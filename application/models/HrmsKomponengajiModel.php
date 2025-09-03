<?php

class HrmsKomponengajiModel extends CI_Model
{


    public function retrieve_all_komponengaji()
    {
        // $this->db->where('aktif' , 1);
        $this->db->order_by('nama', 'ASC');
        $result = $this->db->get('payroll_tipe_gaji');
        return $result->result_array();
    }
	public function retrieve_all_potongan()
    {
         $this->db->where('jenis' , 0);
        $this->db->order_by('nama', 'ASC');
        $result = $this->db->get('payroll_tipe_gaji');
        return $result->result_array();
    }
	public function retrieve_all_pendapatan()
    {
    	//  $this->db->where('jenis' , 1);
        $this->db->order_by('nama', 'ASC');
        $result = $this->db->get('payroll_tipe_gaji');
        return $result->result_array();
    }
    
    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('payroll_tipe_gaji');
        return true;
    }


    public function update($id, $input)
    {
        $id = (int)$id;


        $data = array(
            'nama' => $input['nama'],
			'jenis' => $input['jenis'],
			'urut' => $input['urut'],
			'diubah_oleh' =>  $input['diubah_oleh'],
			'diubah_tanggal' =>  date("Y-m-d H:i:s"),

        );
        $this->db->where('id', $id);
        $this->db->update('payroll_tipe_gaji', $data);
        return true;
    }


    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('payroll_tipe_gaji', 1);
        return $result->row_array();
    }

   
    public function create($input)
    {
		$data = array(
            'nama' => $input['nama'],
			'jenis' => $input['jenis'],
			'urut' => $input['urut'],
            'dibuat_oleh' =>  $input['dibuat_oleh'],
			'dibuat_tanggal' =>  date("Y-m-d H:i:s"),
			'diubah_oleh' =>  $input['diubah_oleh'],
			'diubah_tanggal' =>  date("Y-m-d H:i:s"),

        );
        $this->db->insert('payroll_tipe_gaji', $data);
        return $this->db->insert_id();
    }
}
