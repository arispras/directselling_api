<?php


class PksMesinLogModel extends CI_Model
{

    public function retrieve_all(
        $no_of_records = 10,
        $page_no       = 1
    ) {
        $no_of_records = (int)$no_of_records;
        $page_no       = (int)$page_no;

        $where = array();

        $data = $this->pager->set('pks_mesin_log', $no_of_records, $page_no, $where);

        return $data;
    }


    //  Method untuk mendapatkan semua data pks_mesin_log tanpa pagging

    public function retrieve_all_kategori()
    {
        $this->db->order_by('simbol', 'ASC');
        $result = $this->db->get('pks_mesin_log');
        return $result->result_array();
    }


    //   Method untuk menghapus record pks_mesin_log

    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('pks_mesin_log');
        return true;
    }


    public function update($id, $input)
    {
        $id = (int)$id;

        $data = array(
            'mesin_id' => $input['mesin_id']['id'],
            'tanggal' => $input['tanggal'],
            'km_hm_awal' => $input['km_hm_awal'],
            'km_hm_akhir' => $input['km_hm_akhir'],
            'ket' => $input['ket'],

            'diubah_oleh'=> $input['diubah_oleh'],
            'diubah_tanggal'=> date('Y-m-d H:i:s'),
        );
        $this->db->where('id', $id);
        $this->db->update('pks_mesin_log', $data);
        return true;
    }


    //  Method untuk mengambil satu record pks_mesin_log

    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('pks_mesin_log', 1);
        return $result->row_array();
    }

    public function retrieve_km($id)
    {
        $id = (int)$id;
        $query = $this->db->query("SELECT * 
            FROM pks_mesin_log
            WHERE
                mesin_id='".$id."'
            ORDER BY tanggal DESC
        ");
        return $query->row_array();
        // $this->db->where('id', $id);
        // $result = $this->db->get('pks_mesin_log', 1);
        // return $result->row_array();
    }


    //  Method untuk membuat data pks_mesin_log

    public function create($input)
    {
        $data = array(
            'mesin_id' => $input['mesin_id']['id'],
            'tanggal' => $input['tanggal'],
            'km_hm_awal' => $input['km_hm_awal'],
            'km_hm_akhir' => $input['km_hm_akhir'],
            'ket' => $input['ket'],

            'dibuat_oleh'=> $input['dibuat_oleh'],
            'dibuat_tanggal'=> date('Y-m-d H:i:s'),
        );
        $this->db->insert('pks_mesin_log', $data);
        return $this->db->insert_id();
    }
}
