<?php


class InvKategoriModel extends CI_Model
{

    public function retrieve_all(
        $no_of_records = 10,
        $page_no       = 1
    ) {
        $no_of_records = (int)$no_of_records;
        $page_no       = (int)$page_no;

        $where = array();

        $data = $this->pager->set('inv_kategori', $no_of_records, $page_no, $where);

        return $data;
    }


    //  Method untuk mendapatkan semua data inv_kategori tanpa pagging

    public function retrieve_all_kategori()
    {
        $this->db->order_by('nama', 'ASC');
        $result = $this->db->get('inv_kategori');
        return $result->result_array();
    }


    //   Method untuk menghapus record inv_kategori

    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('inv_kategori');
        return true;
    }


    public function update($id, $input)
    {
        $id = (int)$id;

        $data = array(
            // 'tanggal_efektif' => $input['tanggal_efektif'],
            'nama' => $input['nama'],
            'kode' => $input['kode'],
            'acc_akun_id' => $input['acc_akun_id']['id'],
        );
        $this->db->where('id', $id);
        $this->db->update('inv_kategori', $data);
        return true;
    }


    //  Method untuk mengambil satu record inv_kategori

    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('inv_kategori', 1);
        return $result->row_array();
    }


    //  Method untuk membuat data inv_kategori

    public function create($input)
    {
        $data = array(
            // 'tanggal_efektif' => $input['tanggal_efektif'],
            'nama' => $input['nama'],
            'kode' => $input['kode'],
            'acc_akun_id' => $input['acc_akun_id']['id'],

        );
        $this->db->insert('inv_kategori', $data);
        return $this->db->insert_id();
    }
}
