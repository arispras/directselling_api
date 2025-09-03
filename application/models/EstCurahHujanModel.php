<?php


class EstCurahHujanModel extends CI_Model
{

    public function retrieve_all(
        $no_of_records = 10,
        $page_no       = 1
    ) {
        $no_of_records = (int)$no_of_records;
        $page_no       = (int)$page_no;

        $where = array();

        $data = $this->pager->set('est_curah_hujan', $no_of_records, $page_no, $where);

        return $data;
    }


    //  Method untuk mendapatkan semua data est_curah_hujan tanpa pagging

    public function retrieve_all_kategori()
    {
        $this->db->order_by('tanggal', 'ASC');
        $result = $this->db->get('est_curah_hujan');
        return $result->result_array();
    }


    //   Method untuk menghapus record est_curah_hujan

    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('est_curah_hujan');
        return true;
    }


    public function update($id, $input)
    {
        $id = (int)$id;

        $data = array(
            // 'tanggal_efektif' => $input['tanggal_efektif'],
            'tanggal' => $input['tanggal'],
            'lokasi_id' => $input['lokasi_id']['id'],
            'afdeling_id' => $input['afdeling_id']['id'],
            'pagi' => $input['pagi'],
            'sore' => $input['sore'],
            'malam' => $input['malam'],
            'diubah_oleh'=> $input['diubah_oleh'],
			'diubah_tanggal'=> date('Y-m-d H:i:s'),
        );
        $this->db->where('id', $id);
        $this->db->update('est_curah_hujan', $data);
        return true;
    }


    //  Method untuk mengambil satu record est_curah_hujan

    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('est_curah_hujan', 1);
        return $result->row_array();
    }


    //  Method untuk membuat data est_curah_hujan

    public function create($input)
    {
        $data = array(
            // 'tanggal_efektif' => $input['tanggal_efektif'],
            'tanggal' => $input['tanggal'],
            'lokasi_id' => $input['lokasi_id']['id'],
            'afdeling_id' => $input['afdeling_id']['id'],
            'pagi' => $input['pagi'],
            'sore' => $input['sore'],
            'malam' => $input['malam'],
            'dibuat_oleh'=> $input['dibuat_oleh'],
			'dibuat_tanggal'=> date('Y-m-d H:i:s'),
        );
        $this->db->insert('est_curah_hujan', $data);
        return $this->db->insert_id();
    }
}
