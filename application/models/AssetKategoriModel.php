<?php


class AssetKategoriModel extends CI_Model
{



    public function retrieve_all(
        $no_of_records = 10,
        $page_no       = 1
    ) {
        $no_of_records = (int)$no_of_records;
        $page_no       = (int)$page_no;

        $where = array();

        $data = $this->pager->set('asset_kategori', $no_of_records, $page_no, $where);

        return $data;
    }

 
    public function retrieve_all_kategori()
    {
        // $this->db->where('aktif' , 1);
        $this->db->order_by('nama', 'ASC');
        $result = $this->db->get('asset_kategori');
        return $result->result_array();
    }

 
    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('asset_kategori');
        return true;
    }


    public function update($id, $nama)
    {
        $id = (int)$id;


        $data = array(
            'nama' => $nama,

        );
        $this->db->where('id', $id);
        $this->db->update('asset_kategori', $data);
        return true;
    }

    /**
     * Method untuk mengambil satu record asset_kategori
     *
     * @param  integer $id
     * @return array
     * @author Almazari <almazary@gmail.com>
     */
    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('asset_kategori', 1);
        return $result->row_array();
    }


    public function create($nama)
    {
        $data = array(
            'nama' => $nama
        );
        $this->db->insert('asset_kategori', $data);
        return $this->db->insert_id();
    }
}
