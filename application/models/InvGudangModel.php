<?php

class InvGudangModel extends CI_Model
{



    public function retrieve_all(
        $no_of_records = 10,
        $page_no       = 1
    ) {
        $no_of_records = (int)$no_of_records;
        $page_no       = (int)$page_no;

        $where = array();

        $data = $this->pager->set('inv_gudang', $no_of_records, $page_no, $where);

        return $data;
    }

    /**
     * Method untuk mendapatkan semua data inv_gudang tanpa pagging
     *
     * @return array
     * @author Almazari <almazary@gmail.com>
     */
    public function retrieve_all_invgudang()
    {
        // $this->db->where('aktif' , 1);
        $this->db->order_by('nama', 'ASC');
        $result = $this->db->get('inv_gudang');
        return $result->result_array();
    }

    /**
     * Method untuk menghapus record inv_gudang
     *
     * @param  integer $id
     * @return boolean true jika berhasil
     * @author Almazari <almazary@gmail.com>
     */
    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('inv_gudang');
        return true;
    }


    public function update($id, $nama)
    {
        $id = (int)$id;


        $data = array(
            'nama' => $nama,

        );
        $this->db->where('id', $id);
        $this->db->update('inv_gudang', $data);
        return true;
    }

    /**
     * Method untuk mengambil satu record inv_gudang
     *
     * @param  integer $id
     * @return array
     * @author Almazari <almazary@gmail.com>
     */
    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('inv_gudang', 1);
        return $result->row_array();
    }

    /**
     * Method untuk membuat data inv_gudang
     *
     * @param  string       $nama
     * @param  null|string  $info
     * @return integer      last insert id
     * @author Almazari <almazary@gmail.com>
     */
    public function create($nama)
    {
        $data = array(
            'nama' => $nama
        );
        $this->db->insert('inv_gudang', $data);
        return $this->db->insert_id();
    }
}
