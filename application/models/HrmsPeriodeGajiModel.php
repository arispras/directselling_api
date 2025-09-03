<?php

 
class HrmsPeriodeGajiModel extends CI_Model
{
 
   
    
    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('payroll_periode_gaji');
        return true;
    }


  
    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('payroll_periode_gaji', 1);
        return $result->row_array();
    }
    public function retrieve_all_item()
    {
        
		$this->db->order_by('nama', 'ASC');
        $this->db->get('payroll_periode_gaji');
        $result = $this->db->get('payroll_periode_gaji');
        return $result->result_array();
    }
	public function retrieve_by_lokasi_id($lokasi_id)
    {

        $this->db->where('lokasi_id', $lokasi_id);
		$this->db->order_by('nama', 'ASC');
        $result = $this->db->get('payroll_periode_gaji');
        return $result->result_array();
    }

    public function create($input) 
    {
        $data = array(
            
            'tgl_awal' => $input['tgl_awal'],
            'tgl_akhir' => $input['tgl_akhir'],
            'nama' => $input['nama'],
            'status' => $input['status'],
            'lokasi_id' => $input['lokasi_id']['id'],
            'is_close' => $input['is_close'],
        );
       
        $this->db->insert('payroll_periode_gaji', $data);
        return $this->db->insert_id();
    }

    public function update($id, $input) 
    {


         $id=(int)$id;
        
		 $data = array(
            
            'tgl_awal' => $input['tgl_awal'],
            'tgl_akhir' => $input['tgl_akhir'],
            'nama' => $input['nama'],
            'status' => $input['status'],
            'lokasi_id' => $input['lokasi_id']['id'],
            'is_close' => $input['is_close'],
        );
         $this->db->where('id', $id);
         $this->db->update('payroll_periode_gaji', $data);
         return true;
     }


}
