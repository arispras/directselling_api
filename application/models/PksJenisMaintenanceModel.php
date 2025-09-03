<?php


class PksJenisMaintenanceModel extends CI_Model
{

    public function retrieve_all(
        $no_of_records = 10,
        $page_no       = 1
    ) {
        $no_of_records = (int)$no_of_records;
        $page_no       = (int)$page_no;

        $where = array();

        $data = $this->pager->set('pks_jenis_maintenance', $no_of_records, $page_no, $where);

        return $data;
    }


    //  Method untuk mendapatkan semua data pks_jenis_maintenance tanpa pagging

    public function retrieve_all_kategori()
    {
        $this->db->order_by('kode', 'ASC');
        $result = $this->db->get('pks_jenis_maintenance');
        return $result->result_array();
    }


    //   Method untuk menghapus record pks_jenis_maintenance

    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('pks_jenis_maintenance');
        return true;
    }


    public function update($id, $input)
    {
        $id = (int)$id;

        $data = array(
            'kode' => $input['kode'],
            'keterangan' => $input['keterangan'],
        );
        $this->db->where('id', $id);
        $this->db->update('pks_jenis_maintenance', $data);
        return true;
    }


    //  Method untuk mengambil satu record pks_jenis_maintenance

    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('pks_jenis_maintenance', 1);
        return $result->row_array();
    }


    //  Method untuk membuat data pks_jenis_maintenance

    public function create($input)
    {
        $data = array(
            'kode' => $input['kode'],
            'keterangan' => $input['keterangan'],

        );
        $this->db->insert('pks_jenis_maintenance', $data);
        return $this->db->insert_id();
    }
}
