<?php


class PksMaintenanceMesinModel extends CI_Model
{

    public function retrieve_all(
        $no_of_records = 10,
        $page_no       = 1
    ) {
        $no_of_records = (int)$no_of_records;
        $page_no       = (int)$page_no;

        $where = array();

        $data = $this->pager->set('pks_maintenance_mesin', $no_of_records, $page_no, $where);

        return $data;
    }


    //  Method untuk mendapatkan semua data pks_maintenance_mesin tanpa pagging

    public function retrieve_all_kategori()
    {
        $this->db->order_by('mesin_id','hm_km', 'ASC');
        $result = $this->db->get('pks_maintenance_mesin');
        return $result->result_array();
    }


    //   Method untuk menghapus record pks_maintenance_mesin

    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('pks_maintenance_mesin');
        return true;
    }


    public function update($id, $input)
    {
        $id = (int)$id;

        $data = array(

            'mesin_id' => $input['mesin_id']['id'],
            'jenis_mesin_id' => $input['jenis_mesin_id']['id'],
            'hm_km ' => $input['hm_km'],
            'hm_km_maintenance ' => $input['hm_km_maintenance'],
            'ket ' => $input['ket'],
        );
        $this->db->where('id', $id);
        $this->db->update('pks_maintenance_mesin', $data);
        return true;
    }


    //  Method untuk mengambil satu record pks_maintenance_mesin

    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('pks_maintenance_mesin', 1);
        return $result->row_array();
    }


    //  Method untuk membuat data pks_maintenance_mesin

    public function create($input)
    {
        $data = array(
            'mesin_id' => $input['mesin_id']['id'],
            'jenis_mesin_id' => $input['jenis_mesin_id']['id'],
            'hm_km' => $input['hm_km'],
            'hm_km_maintenance ' => $input['hm_km_maintenance'],
            'ket' => $input['ket'],

        );
        $this->db->insert('pks_maintenance_mesin', $data);
        return $this->db->insert_id();
    }
}
