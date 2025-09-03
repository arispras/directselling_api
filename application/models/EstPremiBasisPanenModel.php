<?php


class EstPremiBasisPanenModel extends CI_Model
{


    //  Method untuk mendapatkan semua data est_premi_basis_panen tanpa pagging

    public function retrieve_all_kategori()
    {
        $this->db->order_by('tanggal_efektif', 'ASC');
        $result = $this->db->get('est_premi_basis_panen');
        return $result->result_array();
    }


    //   Method untuk menghapus record est_premi_basis_panen

    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('est_premi_basis_panen');
        return true;
    }


    public function update($id, $input)
    {
        $id = (int)$id;

        $data = array(
            'blok_id' => $input['blok_id']['id'],
            'tanggal_efektif' => $input['tanggal_efektif'],
            // 'bjr_dari' => $input['bjr_dari'],
            // 'bjr_sd' => $input['bjr_sd'],
			'premi_basis' => $input['premi_basis'],
            'basis_jjg' => $input['basis_jjg'],
            'basis_jjg_jumat' => $input['basis_jjg_jumat'],
            'lebih_basis1' => $input['lebih_basis1'],
            'premi_lebih_basis1' => $input['premi_lebih_basis1'],
            'lebih_basis2' => $input['lebih_basis2'],
            'premi_lebih_basis2' => $input['premi_lebih_basis2'],
            'lebih_basis3' => $input['lebih_basis3'],
            'premi_lebih_basis3' => $input['premi_lebih_basis3'],
            'premi_brondolan' => $input['premi_brondolan'],
            'hk_luas_panen' => $input['hk_luas_panen'],
			'diubah_oleh' =>  $input['diubah_oleh'],
			'diubah_tanggal' =>  date("Y-m-d H:i:s"),
        );
        $this->db->where('id', $id);
        $this->db->update('est_premi_basis_panen', $data);
        return true;
    }


    //  Method untuk mengambil satu record est_premi_basis_panen

    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('est_premi_basis_panen', 1);
        return $result->row_array();
    }


    //  Method untuk membuat data est_premi_basis_panen

    public function create($input)
    {
        $data = array(
            
            'blok_id' => $input['blok_id']['id'],
            'tanggal_efektif' => $input['tanggal_efektif'],
           // 'bjr_dari' => $input['bjr_dari'],
            // 'bjr_sd' => $input['bjr_sd'],
			'premi_basis' => $input['premi_basis'],
            'basis_jjg' => $input['basis_jjg'],
            'basis_jjg_jumat' => $input['basis_jjg_jumat'],
            'lebih_basis1' => $input['lebih_basis1'],
            'premi_lebih_basis1' => $input['premi_lebih_basis1'],
            'lebih_basis2' => $input['lebih_basis2'],
            'premi_lebih_basis2' => $input['premi_lebih_basis2'],
            'lebih_basis3' => $input['lebih_basis3'],
            'premi_lebih_basis3' => $input['premi_lebih_basis3'],
            'premi_brondolan' => $input['premi_brondolan'],
            'hk_luas_panen' => $input['hk_luas_panen'],
            'dibuat_oleh' =>  $input['dibuat_oleh'],
			'dibuat_tanggal' =>  date("Y-m-d H:i:s"),
			'diubah_oleh' =>  $input['diubah_oleh'],
			'diubah_tanggal' =>  date("Y-m-d H:i:s"),

        );
        $this->db->insert('est_premi_basis_panen', $data);
        return $this->db->insert_id();
    }
}
