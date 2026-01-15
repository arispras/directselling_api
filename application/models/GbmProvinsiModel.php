<?php

class GbmProvinsiModel extends CI_Model
{

	public function delete_foto($id)
	{
		$this->db->where('id', $id);
		$this->db->update('gbm_provinsi', array('foto' => null));
		return true;
	}

	public function delete($id)
	{
		$id = (int)$id;
		$this->db->where('id', $id);
		$this->db->delete('gbm_provinsi');
		return true;
	}


	public function retrieve($id = null)
	{

		$id = (int)$id;
		$this->db->where('id', $id);

		$result = $this->db->get('gbm_provinsi', 1);
		return $result->row_array();
	}



	public function retrieve_all()
	{

		// $this->db->order_by('nama', 'ASC');
		$this->db->select('*');
		$this->db->from('gbm_provinsi');
		$result = $this->db->get();
		return $result->result_array();
	}

	public function update(
		$id,
		$input
	) {
		$id        = (int)$id;

		$data = array(
			'nama'          => $input['nama'],
			'provinsi_id'=>$input['provinsi_id']		
		);
		$this->db->where('id', $id);
		$this->db->update('gbm_provinsi', $data);
		return true;
	}

	public function create(
		$input	
	) {
		$data = array(
			'nama'          => $input['nama'],
			'provinsi_id'=>$input['provinsi_id']				
		);
		$this->db->insert('gbm_provinsi', $data);
		return $this->db->insert_id();
	}
	
}
