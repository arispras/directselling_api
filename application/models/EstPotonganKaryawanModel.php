<?php


class EstPotonganKaryawanModel extends CI_Model
{


    //  Method untuk mendapatkan semua data est_potongan_karyawan tanpa pagging

    public function retrieve_all_kategori()
    {
        $this->db->order_by('tanggal', 'ASC');
        $result = $this->db->get('est_potongan_karyawan');
        return $result->result_array();
    }


    //   Method untuk menghapus record est_potongan_karyawan

    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('est_potongan_karyawan');
        return true;
    }


    public function update($id, $input)
    {
        $id = (int)$id;

        $data = array(
            'lokasi_id' => $input['lokasi_id']['id'],
            'karyawan_id' => $input['karyawan_id']['id'],
            'kegiatan_id' => $input['kegiatan_id']['id'],
            'tanggal' => $input['tanggal'],       
			'nilai_potongan' => $input['nilai_potongan'],
			'diubah_oleh' =>  $input['diubah_oleh'],
			'diubah_tanggal' =>  date("Y-m-d H:i:s"),
        );
        $this->db->where('id', $id);
        $this->db->update('est_potongan_karyawan', $data);
        return true;
    }


    //  Method untuk mengambil satu record est_potongan_karyawan

    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('est_potongan_karyawan', 1);
        return $result->row_array();
    }


    //  Method untuk membuat data est_potongan_karyawan

    public function create($input)
    {
        $data = array(
            'lokasi_id' => $input['lokasi_id']['id'],
            'karyawan_id' => $input['karyawan_id']['id'],
            'kegiatan_id' => $input['kegiatan_id']['id'],
            'tanggal' => $input['tanggal'],       
			'nilai_potongan' => $input['nilai_potongan'],
            'dibuat_oleh' =>  $input['dibuat_oleh'],
			'dibuat_tanggal' =>  date("Y-m-d H:i:s"),
			'diubah_oleh' =>  $input['diubah_oleh'],
			'diubah_tanggal' =>  date("Y-m-d H:i:s"),
        );
        $this->db->insert('est_potongan_karyawan', $data);
        return $this->db->insert_id();
    }
}
