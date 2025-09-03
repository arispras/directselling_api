<?php


class HrmsAbsensiModel extends CI_Model
{



    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('payroll_absensi');
        $this->db->where('absensi_id', $id);
        $this->db->delete('payroll_lembur');
        return true;
    }


    public function retrieve_all(
        $no_of_records = 10,
        $page_no       = 1,
        $nama   = '',
        $kode = '',
        $inv_kategori_id = array(),
        $pagination    = true
    ) {
        $no_of_records = (int)$no_of_records;
        $page_no       = (int)$page_no;

        $where    = array();
        $group_by = array();

        $where['inv_kategori'] = array('payroll_absensi.inv_kategori_id = inv_kategori.id', 'join', 'left');
        if (!empty($inv_kategori_id)) {
            $where['payroll_absensi.inv_kategori_id'] = array($inv_kategori_id, 'where_in');
        }

        $like = 0;
        if (!empty($nama)) {
            $where['payroll_absensi.nama'] = array($nama, 'like');
            $like = 1;
        }
        if (!empty($kode)) {
            if ($like) {
                $value = array($kode, 'or_like');
            } else {
                $value = array($kode, 'like');
            }
            $where['payroll_absensi.kode'] = $value;
        }
        $orderby = array(
            'payroll_absensi.id' => 'DESC'
        );

        if ($pagination) {
            $data = $this->pager->set('payroll_absensi', $no_of_records, $page_no, $where, $orderby, 'payroll_absensi.*', $group_by);
        } else {
            # cari jumlah semua pengajar
            $no_of_records = $this->db->count_all('payroll_absensi');
            $search_all    = $this->pager->set('payroll_absensi', $no_of_records, $page_no, $where, $orderby, 'payroll_absensi.*', $group_by);
            $data          = $search_all['results'];
        }

        return $data;
    }

    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->from('payroll_absensi a');
        $this->db->select('a.*, b.*, a.id as id, a.karyawan_id as karyawan_id, a.tanggal as tanggal, a.lokasi_id as lokasi_id');
        $this->db->where('a.id', $id);
        // $result = $this->db->get('payroll_absensi', 1);
        $this->db->join('payroll_lembur b', 'b.absensi_id = a.id', 'left');
        $result = $this->db->get();

        return $result->row_array();
    }
    public function retrieve_all_item()
    {
        $this->db->order_by('nama', 'ASC');
        $this->db->get('payroll_absensi');
        $result = $this->db->get('payroll_absensi');
        return $result->result_array();
    }

    public function create(
        $arrdata
    ) {

        $lokasi_id  = (int) $arrdata['lokasi_id'];
        $karyawan_id  = (int) $arrdata['karyawan_id'];
        $tanggal = $arrdata['tanggal'];
        // absensi
        $jenis_absensi_id  = (int) $arrdata['jenis_absensi_id'];
        $masuk    =  $arrdata['masuk'];
        $pulang    =  $arrdata['pulang'];
        $jumlah_jam    =  0;

        $data = array(
            'pulang' => $pulang,
            'masuk' => $masuk,
            'karyawan_id'    => $karyawan_id,
            'lokasi_id'    => $lokasi_id,
            'jenis_absensi_id'    => $jenis_absensi_id,
            // 'premi'=>$premi,
            'tanggal' => $tanggal,
            'jumlah_jam' => 0
        );
        $this->db->insert('payroll_absensi', $data);
        $absensi_id = $this->db->insert_id();

        if (!empty($arrdata['jumlah_jam'])) {
            // lembur
            $mulai    =  $arrdata['mulai'];
            $selesai    =  $arrdata['selesai'];
            $jumlah_jam    =  $arrdata['jumlah_jam'];
            $nilai_lembur    =  $arrdata['nilai_lembur'];
            $tipe_lembur    =  $arrdata['tipe_lembur'];
            $istirahat    =  $arrdata['istirahat'];
            $data = array(
                'absensi_id'=> $absensi_id,
                'lokasi_id'    => $lokasi_id,
                'karyawan_id'    => $karyawan_id,
                'tanggal' => $tanggal,
                'selesai' => $selesai,
                'mulai' => $mulai,
                'nilai_lembur' => $nilai_lembur,
                'jumlah_jam' => $jumlah_jam,
                'istirahat' => $istirahat,
                'tipe_lembur' => $tipe_lembur
            );
            $this->db->insert('payroll_lembur', $data);
        }

        return $absensi_id;
    }

    public function update(
        $id,
        $arrdata
    ) {


        $id = (int)$id;

        $lokasi_id  = (int) $arrdata['lokasi_id'];
        $tanggal = $arrdata['tanggal'];
        $karyawan_id  = (int) $arrdata['karyawan_id'];
        
        // absensi
        $masuk    =  $arrdata['masuk'];
        $jenis_absensi_id  = (int) $arrdata['jenis_absensi_id'];
        $pulang    =  $arrdata['pulang'];
        $jumlah_jam    =  0;
        $data = array(
            'pulang' => $pulang,
            'masuk' => $masuk,
            'karyawan_id'    => $karyawan_id,
            'lokasi_id'    => $lokasi_id,
            'jenis_absensi_id'    => $jenis_absensi_id,
            'tanggal' => $tanggal,
            'jumlah_jam' => 0
        );
        $this->db->where('id', $id);
        $this->db->update('payroll_absensi', $data);
        
        $this->db->where('absensi_id', $id);
		$lembur = $this->db->delete('payroll_lembur');

        if (!empty($arrdata['jumlah_jam'])) {
            // lembur
            $mulai    =  $arrdata['mulai'];
            $selesai    =  $arrdata['selesai'];
            $jumlah_jam    =  $arrdata['jumlah_jam'];
            $nilai_lembur    =  $arrdata['nilai_lembur'];
            $tipe_lembur    =  $arrdata['tipe_lembur'];
            $istirahat    =  $arrdata['istirahat'];
            $data = array(
                'absensi_id'=> $id,
                'lokasi_id'    => $lokasi_id,
                'karyawan_id'    => $karyawan_id,
                'tanggal' => $tanggal,
                'selesai' => $selesai,
                'mulai' => $mulai,
                'nilai_lembur' => $nilai_lembur,
                'jumlah_jam' => $jumlah_jam,
                'istirahat' => $istirahat,
                'tipe_lembur' => $tipe_lembur
            );
            $this->db->insert('payroll_lembur', $data);
        }

        return true;
    }
}
