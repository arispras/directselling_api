<?php


class PksHargaTbsModel extends CI_Model
{

    public function retrieve_all(
        $no_of_records = 10,
        $page_no       = 1
    ) {
        $no_of_records = (int)$no_of_records;
        $page_no       = (int)$page_no;

        $where = array();

        $data = $this->pager->set('pks_harga_tbs', $no_of_records, $page_no, $where);

        return $data;
    }


    //  Method untuk mendapatkan semua data pks_harga_tbs tanpa pagging

    public function retrieve_all_kategori()
    {
        $this->db->order_by('supplier_id', 'ASC');
        $result = $this->db->get('pks_harga_tbs');
        return $result->result_array();
    }


    //   Method untuk menghapus record pks_harga_tbs

    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('pks_harga_tbs');
        return true;
    }


    public function update($id, $input)
    {
        $id = (int)$id;

        $data = array(
            'tanggal_efektif' => $input['tanggal_efektif'],
            'supplier_id' => $input['supplier_id']['id'],
            'harga' => $input['harga'],
			'diubah_oleh'=> $input['diubah_oleh'],
			'diubah_tanggal'=> date("Y-m-d H:i:s"),
        );
        $this->db->where('id', $id);
        $this->db->update('pks_harga_tbs', $data);
        return true;
    }


    //  Method untuk mengambil satu record pks_harga_tbs

    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('pks_harga_tbs', 1);
        return $result->row_array();
    }


    //  Method untuk membuat data pks_harga_tbs

    public function create($input)
    {
        $data = array(
            'tanggal_efektif' => $input['tanggal_efektif'],
            'supplier_id' => $input['supplier_id']['id'],
            'harga' => $input['harga'],
            'dibuat_oleh'=> $input['dibuat_oleh'],
			'dibuat_tanggal'=> date("Y-m-d H:i:s"),
			'diubah_oleh'=> $input['diubah_oleh'],
			'diubah_tanggal'=> date("Y-m-d H:i:s"),

        );
        $this->db->insert('pks_harga_tbs', $data);
        return $this->db->insert_id();
    }
}
