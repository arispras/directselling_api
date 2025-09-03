<?php

 
class AccPeriodeAkuntingModel extends CI_Model
{
 
   
    
    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('acc_periode_akunting');
        return true;
    }


  
    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('acc_periode_akunting', 1);
        return $result->row_array();
    }
    public function retrieve_all_item()
    {
        
		$this->db->order_by('nama', 'ASC');
        $this->db->get('acc_periode_akunting');
        $result = $this->db->get('acc_periode_akunting');
        return $result->result_array();
    }
	public function retrieve_by_lokasi_id($lokasi_id)
    {

        $this->db->where('lokasi_id', $lokasi_id);
		$this->db->order_by('nama', 'ASC');
        $result = $this->db->get('acc_periode_akunting');
        return $result->result_array();
    }

    public function create(
       $arrdata
    ) {

        $tgl_awal = $arrdata['tgl_awal'];
        $tgl_akhir    =  $arrdata['tgl_akhir'];
        $nama  =  $arrdata['nama'];
		$status    = '0';//default Open  // $arrdata['status'];
		$lokasi_id    =  $arrdata['lokasi_id'];

        $diubah_oleh    =  $arrdata['diubah_oleh'];
		$diubah_tanggal    =  date('Y-m-d H:i:s');
		$dibuat_oleh    =  $arrdata['dibuat_oleh'];
		$dibuat_tanggal    =  date('Y-m-d H:i:s');
        $data = array(
            'tgl_awal' => $tgl_awal,
            'tgl_akhir' => $tgl_akhir,
            'nama'    => $nama,
            'status'    => $status,
			'lokasi_id'    => $lokasi_id,

            'diubah_oleh' => $diubah_oleh,
			'diubah_tanggal' => $diubah_tanggal,
			'dibuat_oleh' => $dibuat_oleh,
			'dibuat_tanggal' => $dibuat_tanggal,
             );
        $this->db->insert('acc_periode_akunting', $data);
        return $this->db->insert_id();
    }

    public function update($id,
        $arrdata
     ) {


         $id=(int)$id;
        
		 $tgl_awal = $arrdata['tgl_awal'];
		 $tgl_akhir    =  $arrdata['tgl_akhir'];
		 $nama  =  $arrdata['nama'];
		 $status    =  $arrdata['status'];
		 $lokasi_id    =  $arrdata['lokasi_id'];

         $diubah_oleh    =  $arrdata['diubah_oleh'];
		 $diubah_tanggal    =  date('Y-m-d H:i:s');
        $data = array(
            'tgl_awal' => $tgl_awal,
            'tgl_akhir' => $tgl_akhir,
            'nama'    => $nama,
            'status'    => $status,
			'lokasi_id'    => $lokasi_id,

            'diubah_tanggal'    => $diubah_tanggal,
            'diubah_oleh'    => $diubah_oleh,
            
             );
         $this->db->where('id', $id);
         $this->db->update('acc_periode_akunting', $data);
         return true;
     }


}
