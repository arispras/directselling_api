<?php


class HrmsCatuBerasModel extends CI_Model
{

    public function retrieve_all(
        $no_of_records = 10,
        $page_no       = 1
    ) {
        $no_of_records = (int)$no_of_records;
        $page_no       = (int)$page_no;

        $where = array();

        $data = $this->pager->set('hrms_catuberas_setting', $no_of_records, $page_no, $where);

        return $data;
    }


    //  Method untuk mendapatkan semua data hrms_catuberas_setting tanpa pagging

    public function retrieve_all_kategori()
    {
        $this->db->order_by('status_karyawan', 'ASC');
        $result = $this->db->get('hrms_catuberas_setting');
        return $result->result_array();
    }


    //   Method untuk menghapus record hrms_catuberas_setting

    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('hrms_catuberas_setting');
        return true;
    }


    public function update($id, $input)
    {
        $id = (int)$id;

        $data = array(
            
            'status_karyawan' => $input['status_karyawan']['id'],
            'jumlah_kg' => $input['jumlah_kg'],
            'jumlah_rupiah' => $input['jumlah_rupiah'],
			'diubah_oleh' =>  $input['diubah_oleh'],
			'diubah_tanggal' =>  date("Y-m-d H:i:s"),
        );
        $this->db->where('id', $id);
        $this->db->update('hrms_catuberas_setting', $data);
        return true;
    }


    //  Method untuk mengambil satu record hrms_catuberas_setting

    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('hrms_catuberas_setting', 1);
        return $result->row_array();
    }


    //  Method untuk membuat data hrms_catuberas_setting

    public function create($input)
    {
        $data = array(
            
            'status_karyawan' => $input['status_karyawan']['id'],
            'jumlah_kg' => $input['jumlah_kg'],
            'jumlah_rupiah' => $input['jumlah_rupiah'],
            'dibuat_oleh' =>  $input['dibuat_oleh'],
			'dibuat_tanggal' =>  date("Y-m-d H:i:s"),
			'diubah_oleh' =>  $input['diubah_oleh'],
			'diubah_tanggal' =>  date("Y-m-d H:i:s"),

        );
        $this->db->insert('hrms_catuberas_setting', $data);
        return $this->db->insert_id();
    }
}
