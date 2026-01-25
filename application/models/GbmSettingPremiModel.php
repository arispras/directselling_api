<?php


class GbmSettingPremiModel extends CI_Model
{

    public function retrieve_all(
        $no_of_records = 10,
        $page_no       = 1
    ) {
        $no_of_records = (int)$no_of_records;
        $page_no       = (int)$page_no;

        $where = array();

        $data = $this->pager->set('gbm_setting_premi', $no_of_records, $page_no, $where);

        return $data;
    }


    //  Method untuk mendapatkan semua data gbm_setting_premi tanpa pagging

    public function retrieve_all_kategori()
    {
        $this->db->order_by('jabatan', 'ASC');
        $result = $this->db->get('gbm_setting_premi');
        return $result->result_array();
    }


    //   Method untuk menghapus record gbm_setting_premi

    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('gbm_setting_premi');
        return true;
    }


    public function update($id, $input)
    {
        $id = (int)$id;

        $data = array(
            'lokasi_id' => $input['lokasi_id']['id'],
            'jabatan' => $input['jabatan']['id'],
            'awal' => $input['awal'],
            'akhir' => $input['akhir'],
            'komisi' => $input['komisi'],
            'bonus' => $input['bonus'],
            'bonus_lunas' => $input['bonus_lunas'],
            'diubah_oleh' =>  $input['diubah_oleh'],
            'diubah_tanggal' =>  date("Y-m-d H:i:s"),
        );
        $this->db->where('id', $id);
        $this->db->update('gbm_setting_premi', $data);
        return true;
    }


    //  Method untuk mengambil satu record gbm_setting_premi

    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('gbm_setting_premi', 1);
        return $result->row_array();
    }


    //  Method untuk membuat data gbm_setting_premi

    public function create($input)
    {
        $data = array(
            'lokasi_id' => $input['lokasi_id']['id'],
            'jabatan' => $input['jabatan']['id'],
            'awal' => $input['awal'],
            'akhir' => $input['akhir'],
            'komisi' => $input['komisi'],
            'bonus' => $input['bonus'],
            'bonus_lunas' => $input['bonus_lunas'],
            'dibuat_oleh' =>  $input['dibuat_oleh'],
            'dibuat_tanggal' =>  date("Y-m-d H:i:s"),
            'diubah_oleh' =>  $input['diubah_oleh'],
            'diubah_tanggal' =>  date("Y-m-d H:i:s"),

        );
        $this->db->insert('gbm_setting_premi', $data);
        return $this->db->insert_id();
    }
}
