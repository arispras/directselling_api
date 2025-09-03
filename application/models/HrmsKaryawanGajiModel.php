<?php

class HrmsKaryawanGajiModel extends CI_Model
{
   
 
    public function retrieve_all_karyawan()
    {
        // $this->db->where('aktif' , 1);
        $this->db->order_by('nama', 'ASC');
        $result = $this->db->get('payroll_karyawan_gaji');
        return $result->result_array();
    }

 
    public function create($data)
    {
        $this->db->insert("payroll_karyawan_gaji", array(
            // 'lokasi_id'=> $data['lokasi_id']['id'],
            'karyawan_id'=> $data['karyawan_id']['id'],
			'gapok'=> $data['gapok'],
            'gapok_bpjs'=> $data['gapok_bpjs'],
            'gapok_bpjs_kes'=> $data['gapok_bpjs_kes'],
			'pot_bpjs_jht'=> $data['pot_bpjs_jht'],
            'pot_bpjs_jp'=> $data['pot_bpjs_jp'],
			'pot_bpjs_kes'=> $data['pot_bpjs_kes'],
			'is_catu'=> $data['is_catu'],
			'tanggal_efektif_catu'=> $data['tanggal_efektif_catu'],
            'dibuat_oleh' => $data['dibuat_oleh'],
			'dibuat_tanggal' => date("Y-m-d H:i:s"),
			'diubah_oleh' => $data['diubah_oleh'],
			'diubah_tanggal' => date("Y-m-d H:i:s"),
            
            
        ));

        // $id= $this->db->insert_id();
		$details=$data['invoiceItems'];
		foreach ($details as $key => $value) {
			$this->db->insert("payroll_gaji", array(
         
				'karyawan_id'=>$data['karyawan_id']['id'],
				'tipe_gaji'=> $value['komponenGaji']['id'],
				'nilai'=> $value['nilai'],
				'tanggal_efektif'=> $value['tanggal_efektif']
			));
		}

		return $data['karyawan_id']['id'];

    }
    public function delete($id)
    {
        $id = (int)$id;
		$this->db->where('karyawan_id', $id);
        $this->db->delete('payroll_gaji');

        $this->db->where('karyawan_id', $id);
        $this->db->delete('payroll_karyawan_gaji');
			
		
        return true;
    }


    public function update($id, $data_post)
    {
        $id = (int)$id;


        $data = array(
           
            // 'lokasi_id'=> $data_post['lokasi_id']['id'],
            'karyawan_id'=> $data_post['karyawan_id']['id'],
			'gapok'=> $data_post['gapok'],
            'gapok_bpjs'=> $data_post['gapok_bpjs'],
            'gapok_bpjs_kes'=> $data_post['gapok_bpjs_kes'],
			'pot_bpjs_jht'=> $data_post['pot_bpjs_jht'],
            'pot_bpjs_jp'=> $data_post['pot_bpjs_jp'],
			'pot_bpjs_kes'=> $data_post['pot_bpjs_kes'],
			'is_catu'=> $data_post['is_catu'],
			'tanggal_efektif_catu'=> $data_post['tanggal_efektif_catu'],
            'diubah_oleh' => $data_post['diubah_oleh'],
			'diubah_tanggal' => date("Y-m-d H:i:s"),
        );
        $this->db->where('karyawan_id', $id);
        $this->db->update('payroll_karyawan_gaji', $data);

		$this->db->where('karyawan_id', $id);
        $this->db->delete('payroll_gaji');

		$details=$data_post['invoiceItems'];
		foreach ($details as $key => $value) {
			$this->db->insert("payroll_gaji", array(
         
				'karyawan_id'=>$id,
				'tipe_gaji'=> $value['komponenGaji']['id'],
				'nilai'=> $value['nilai'],
				'tanggal_efektif'=> $value['tanggal_efektif']
			));
		}

        return true;
    }

  
    public function retrieve($id)
    {
        $id = (int)$id;
		$this->db->select('payroll_karyawan_gaji.*,karyawan.lokasi_tugas_id as lokasi_id' );
		$this->db->from('payroll_karyawan_gaji' );
		$this->db->join('karyawan', 'payroll_karyawan_gaji.karyawan_id=karyawan.id');
        $this->db->where('payroll_karyawan_gaji.karyawan_id', $id);
        $result = $this->db->get();
        return $result->row_array();
    }
	public function retrieve_detail($hdid){
		$this->db->select('payroll_gaji.*,payroll_tipe_gaji.nama as nama_tipe_gaji');
		$this->db->from('payroll_gaji');
		$this->db->join('payroll_tipe_gaji', 'payroll_gaji.tipe_gaji = payroll_tipe_gaji.id');
		$this->db->where('karyawan_id', $hdid);
		$res=$this->db->get();
		return $res->result_array();
	}


	
}
