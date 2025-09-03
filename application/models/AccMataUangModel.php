<?php


class AccMataUangModel extends CI_Model
{

    public function retrieve_all(
        $no_of_records = 10,
        $page_no       = 1
    ) {
        $no_of_records = (int)$no_of_records;
        $page_no       = (int)$page_no;

        $where = array();

        $data = $this->pager->set('acc_mata_uang', $no_of_records, $page_no, $where);

        return $data;
    }


    //  Method untuk mendapatkan semua data acc_mata_uang tanpa pagging

    public function retrieve_all_kategori()
    {
        $this->db->order_by('nama', 'ASC');
        $result = $this->db->get('acc_mata_uang');
        return $result->result_array();
    }


    //   Method untuk menghapus record acc_mata_uang

    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('acc_mata_uang');
        return true;
    }


    public function update($id, $input)
    {
        $id = (int)$id;

        $data = array(
            // 'tanggal_efektif' => $input['tanggal_efektif'],
            'nama' => $input['nama'],
            'kode' => $input['kode'],
            // 'acc_akun_id' => $input['acc_akun_id']['id'],
        );
        $this->db->where('id', $id);
        $this->db->update('acc_mata_uang', $data);
        return true;
    }


    //  Method untuk mengambil satu record acc_mata_uang

    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('acc_mata_uang', 1);
        return $result->row_array();
    }


    //  Method untuk membuat data acc_mata_uang

    public function create($input)
    {
        $data = array(
            // 'tanggal_efektif' => $input['tanggal_efektif'],
            'nama' => $input['nama'],
            'kode' => $input['kode'],
            // 'acc_akun_id' => $input['acc_akun_id']['id'],

        );
        $this->db->insert('acc_mata_uang', $data);
        return $this->db->insert_id();
    }
}
