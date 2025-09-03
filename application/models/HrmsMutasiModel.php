<?php


class HrmsMutasiModel extends CI_Model
{

    public function retrieve_all(
        $no_of_records = 10,
        $page_no       = 1
    ) {
        $no_of_records = (int)$no_of_records;
        $page_no       = (int)$page_no;

        $where = array();

        $data = $this->pager->set('hrms_mutasi', $no_of_records, $page_no, $where);

        return $data;
    }


    //  Method untuk mendapatkan semua data hrms_mutasi tanpa pagging

    public function retrieve_all_kategori()
    {
        $this->db->order_by('no_transaksi', 'ASC');
        $result = $this->db->get('hrms_mutasi');
        return $result->result_array();
    }


    //   Method untuk menghapus record hrms_mutasi

    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('hrms_mutasi');
        return true;
    }


    public function update($id, $input)
    {
        $id = (int)$id;

        $data = array(
            
            'karyawan_id' => $input['karyawan_id']['id'],
            'no_transaksi' => $input['no_transaksi'],
            'tgl' => $input['tgl'],

            'jabatan_id' => $input['jabatan_id']['id'],
            'lokasi_tugas_id' => $input['lokasi_tugas_id']['id'],
            'pangkat_id' => $input['pangkat_id']['id'],
            'departemen_id' => $input['departemen_id']['id'],
            'golongan_id' => $input['golongan_id']['id'],
            'tipe_karyawan_id' => $input['tipe_karyawan_id']['id'],
            'sub_bagian_id' => $input['sub_bagian_id']['id'],

            'jabatan_lama_id' => $input['jabatan_lama_id']['id'],
            'lokasi_tugas_lama_id' => $input['lokasi_tugas_lama_id']['id'],
            'pangkat_lama_id' => $input['pangkat_lama_id']['id'],
            'departemen_lama_id' => $input['departemen_lama_id']['id'],
            'golongan_lama_id' => $input['golongan_lama_id']['id'],
            'tipe_karyawan_lama_id' => $input['tipe_karyawan_lama_id']['id'],
            'sub_bagian_lama_id' => $input['sub_bagian_lama_id']['id'],
            'status' => $input['status'],
			'diubah_oleh' =>  $input['diubah_oleh'],
			'diubah_tanggal' =>  date("Y-m-d H:i:s"),
        );
        $this->db->where('id', $id);
        $this->db->update('hrms_mutasi', $data);
        return true;
    }


    //  Method untuk mengambil satu record hrms_mutasi

    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('hrms_mutasi', 1);
        return $result->row_array();
    }


    //  Method untuk membuat data hrms_mutasi

    public function create($input)
    {
        $data = array(
            
            'karyawan_id' => $input['karyawan_id']['id'],
            'no_transaksi' => $input['no_transaksi'],
            'tgl' => $input['tgl'],

            'jabatan_id' => $input['jabatan_id']['id'],
            'lokasi_tugas_id' => $input['lokasi_tugas_id']['id'],
            'pangkat_id' => $input['pangkat_id']['id'],
            'departemen_id' => $input['departemen_id']['id'],
            'golongan_id' => $input['golongan_id']['id'],
            'tipe_karyawan_id' => $input['tipe_karyawan_id']['id'],
            'sub_bagian_id' => $input['sub_bagian_id']['id'],

            'jabatan_lama_id' => $input['jabatan_lama_id']['id'],
            'lokasi_tugas_lama_id' => $input['lokasi_tugas_lama_id']['id'],
            'pangkat_lama_id' => $input['pangkat_lama_id']['id'],
            'departemen_lama_id' => $input['departemen_lama_id']['id'],
            'golongan_lama_id' => $input['golongan_lama_id']['id'],
            'tipe_karyawan_lama_id' => $input['tipe_karyawan_lama_id']['id'],
            'sub_bagian_lama_id' => $input['sub_bagian_lama_id']['id'],
            'status' => $input['status'],
            'dibuat_oleh' =>  $input['dibuat_oleh'],
			'dibuat_tanggal' =>  date("Y-m-d H:i:s"),
			'diubah_oleh' =>  $input['diubah_oleh'],
			'diubah_tanggal' =>  date("Y-m-d H:i:s"),

        );
        $this->db->insert('hrms_mutasi', $data);
        return $this->db->insert_id();
    }
}
