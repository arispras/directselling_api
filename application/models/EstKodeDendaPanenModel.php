<?php


class EstKodeDendaPanenModel extends CI_Model
{



    public function retrieve_all()
    {
        $this->db->order_by('nama', 'ASC');
        $result = $this->db->get('est_kode_denda_panen');
        return $result->result_array();
    }


    //   Method untuk menghapus record est_kode_denda_panen

    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('est_kode_denda_panen');
        return true;
    }


    public function update($id, $input)
    {
        $id = (int)$id;

        $data = array(
            // 'tanggal_efektif' => $input['tanggal_efektif'],
            'nama' => $input['nama'],
            'kode' => $input['kode'],
			'diubah_oleh' =>  $input['diubah_oleh'],
			'diubah_tanggal' =>  date("Y-m-d H:i:s"),
        );
        $this->db->where('id', $id);
        $this->db->update('est_kode_denda_panen', $data);
        return true;
    }


    //  Method untuk mengambil satu record est_kode_denda_panen

    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('est_kode_denda_panen', 1);
        return $result->row_array();
    }


    //  Method untuk membuat data est_kode_denda_panen

    public function create($input)
    {
        $data = array(
            // 'tanggal_efektif' => $input['tanggal_efektif'],
            'nama' => $input['nama'],
            'kode' => $input['kode'],
            'dibuat_oleh' =>  $input['dibuat_oleh'],
			'dibuat_tanggal' =>  date("Y-m-d H:i:s"),
			'diubah_oleh' =>  $input['diubah_oleh'],
			'diubah_tanggal' =>  date("Y-m-d H:i:s"),

        );
        $this->db->insert('est_kode_denda_panen', $data);
        return $this->db->insert_id();
    }
}
