<?php


class EstParamPremiMandorKeraniModel extends CI_Model
{


    //  Method untuk mendapatkan semua data est_param_premi_mandor_kerani tanpa pagging

    public function retrieve_all_kategori()
    {
        $this->db->order_by('tanggal', 'ASC');
        $result = $this->db->get('est_param_premi_mandor_kerani');
        return $result->result_array();
    }


    //   Method untuk menghapus record est_param_premi_mandor_kerani

    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('est_param_premi_mandor_kerani');
        return true;
    }


    public function update($id, $input)
    {
        $id = (int)$id;

        $data = array(
            'lokasi_id' => $input['lokasi_id']['id'],
            'jabatan_id' => $input['jabatan_id']['id'], 
			'persen_premi' => $input['persen_premi'],
			'jumlah_karyawan' => $input['jumlah_karyawan'],
			'diubah_oleh' =>  $input['diubah_oleh'],
			'diubah_tanggal' =>  date("Y-m-d H:i:s"),
        );
        $this->db->where('id', $id);
        $this->db->update('est_param_premi_mandor_kerani', $data);
        return true;
    }


    //  Method untuk mengambil satu record est_param_premi_mandor_kerani

    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('est_param_premi_mandor_kerani', 1);
        return $result->row_array();
    }


    //  Method untuk membuat data est_param_premi_mandor_kerani

    public function create($input)
    {
        $data = array(
            'lokasi_id' => $input['lokasi_id']['id'],
            'jabatan_id' => $input['jabatan_id']['id'],     
			'persen_premi' => $input['persen_premi'],
			'jumlah_karyawan' => $input['jumlah_karyawan'],
            'dibuat_oleh' =>  $input['dibuat_oleh'],
			'dibuat_tanggal' =>  date("Y-m-d H:i:s"),
			'diubah_oleh' =>  $input['diubah_oleh'],
			'diubah_tanggal' =>  date("Y-m-d H:i:s"),
        );
        $this->db->insert('est_param_premi_mandor_kerani', $data);
        return $this->db->insert_id();
    }
}
