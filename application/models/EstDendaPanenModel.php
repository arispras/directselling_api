<?php

 
class EstDendaPanenModel extends CI_Model
{
 
   
    
    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('est_denda_panen');
        return true;
    }


   
    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('est_denda_panen', 1);
        return $result->row_array();
    }
	public function retrieveByKodeDendaId($id)
    {
        $id = (int)$id;

        $this->db->where('kode_denda_panen_id', $id);
        $result = $this->db->get('est_denda_panen', 1);
        return $result->row_array();
    }
    public function retrieve_all()
    {
        $this->db->order_by('nama', 'ASC');
        $this->db->get('est_denda_panen');
        $result = $this->db->get('est_denda_panen');
        return $result->result_array();
    }

    public function create(
       $input
    ) {

       

        $data = array(
			'lokasi_id' => $input['lokasi_id'],
			'tanggal_efektif' => $input['tanggal_efektif'],
			'nilai' => $input['nilai'],
			'tipe' => $input['tipe'],
			'kode_denda_panen_id' => $input['kode_denda_panen_id'],
            'dibuat_oleh' =>  $input['dibuat_oleh'],
			'dibuat_tanggal' =>  date("Y-m-d H:i:s"),
			'diubah_oleh' =>  $input['diubah_oleh'],
			'diubah_tanggal' =>  date("Y-m-d H:i:s"),

		);
        $this->db->insert('est_denda_panen', $data);
        return $this->db->insert_id();
    }

    public function update($id,
        $input
     ) {


         $id=(int)$id;
		 
 
		 $data = array(
			'lokasi_id' => $input['lokasi_id'],
			'tanggal_efektif' => $input['tanggal_efektif'],
			'nilai' => $input['nilai'],
			'tipe' => $input['tipe'],
			'kode_denda_panen_id' => $input['kode_denda_panen_id'],
			'diubah_oleh' =>  $input['diubah_oleh'],
			'diubah_tanggal' =>  date("Y-m-d H:i:s"),

		);
         $this->db->where('id', $id);
         $this->db->update('est_denda_panen', $data);
         return true;
     }


}
