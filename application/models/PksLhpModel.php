<?php


class PksLhpModel extends CI_Model
{

    public function retrieve_all(
        $no_of_records = 10,
        $page_no       = 1
    ) {
        $no_of_records = (int)$no_of_records;
        $page_no       = (int)$page_no;

        $where = array();

        $data = $this->pager->set('pks_lhp', $no_of_records, $page_no, $where);

        return $data;
    }

    /**
     * Method untuk mendapatkan semua data pks_lhp tanpa pagging
     *
     * @return array
     * @author Almazari <almazary@gmail.com>
     */
    public function retrieve_all_jabatan()
    {
        $this->db->order_by('nama', 'ASC');
        $result = $this->db->get('pks_lhp');
        return $result->result_array();
    }

    /**
     * Method untuk menghapus record pks_lhp
     *
     * @param  integer $id
     * @return boolean true jika berhasil
     * @author Almazari <almazary@gmail.com>
     */
    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('pks_lhp');
        return true;
    }


    public function update($id, $input)
    {
        $id = (int)$id;
        
        $data = $input;
        $this->db->where('id', $id);
        $this->db->update('pks_lhp', $data);
        return true;
    }

    /**
     * Method untuk mengambil satu record pks_lhp
     *
     * @param  integer $id
     * @return array
     * @author Almazari <almazary@gmail.com>
     */
    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('pks_lhp', 1);
        return $result->row_array();
    }

    /**
     * Method untuk membuat data pks_lhp
     *
     * @param  string       $nama
     * @param  null|string  $info
     * @return integer      last insert id
     * @author Almazari <almazary@gmail.com>
     */
    public function create( $input )
    {
        $data = $input;
        $this->db->insert('pks_lhp', $data);
        return $this->db->insert_id();
    }
}
