<?php


class PksHargaAngkutModel extends CI_Model
{

    public function retrieve_all(
        $no_of_records = 10,
        $page_no       = 1
    ) {
        $no_of_records = (int)$no_of_records;
        $page_no       = (int)$page_no;

        $where = array();

        $data = $this->pager->set('pks_harga_angkut', $no_of_records, $page_no, $where);

        return $data;
    }


    //  Method untuk mendapatkan semua data pks_harga_angkut tanpa pagging

    public function retrieve_all_kategori()
    {
        $this->db->order_by('supplier_id', 'ASC');
        $result = $this->db->get('pks_harga_angkut');
        return $result->result_array();
    }


    //   Method untuk menghapus record pks_harga_angkut

    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('pks_harga_angkut');
        return true;
    }


    public function update($id, $input)
    {
        $id = (int)$id;

        $data = array(
            'tanggal_efektif' => $input['tanggal_efektif'],
            'supplier_id' => $input['supplier_id']['id'],
            'harga' => $input['harga'],
        );
        $this->db->where('id', $id);
        $this->db->update('pks_harga_angkut', $data);
        return true;
    }


    //  Method untuk mengambil satu record pks_harga_angkut

    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('pks_harga_angkut', 1);
        return $result->row_array();
    }


    //  Method untuk membuat data pks_harga_angkut

    public function create($input)
    {
        $data = array(
            'tanggal_efektif' => $input['tanggal_efektif'],
            'supplier_id' => $input['supplier_id']['id'],
            'harga' => $input['harga'],

        );
        $this->db->insert('pks_harga_angkut', $data);
        return $this->db->insert_id();
    }
}
