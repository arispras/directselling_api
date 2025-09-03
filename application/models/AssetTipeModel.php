<?php


class AssetTipeModel extends CI_Model
{

    public function retrieve_all(
        $no_of_records = 10,
        $page_no       = 1
    ) {
        $no_of_records = (int)$no_of_records;
        $page_no       = (int)$page_no;

        $where = array();

        $data = $this->pager->set('asset_tipe', $no_of_records, $page_no, $where);

        return $data;
    }

   
    public function retrieve_all_tipe()
    {
        // $this->db->where('aktif' , 1);
        $this->db->order_by('nama', 'ASC');
        $result = $this->db->get('asset_tipe');
        return $result->result_array();
    }


    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('asset_tipe');
        return true;
    }


    public function update($id, $nama)
    {
        $id = (int)$id;


        $data = array(
            'nama' => $nama,

        );
        $this->db->where('id', $id);
        $this->db->update('asset_tipe', $data);
        return true;
    }

 
    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('asset_tipe', 1);
        return $result->row_array();
    }

 
    public function create($nama)
    {
        $data = array(
            'nama' => $nama
        );
        $this->db->insert('asset_tipe', $data);
        return $this->db->insert_id();
    }
}
