<?php

class GbmKabupatenModel extends CI_Model
{



	public function delete($id)
	{
		$id = (int)$id;
		$this->db->where('id', $id);
		$this->db->delete('gbm_kabupaten');
		return true;
	}


	public function retrieve_by_id($id = null)
	{

		$id = (int)$id;
		$this->db->where('id', $id);

		$result = $this->db->get('gbm_kabupaten', 1);
		return $result->row_array();
	}

	public function retrieve_all_by_provinsi_id($id = null)
	{

		$id = (int)$id;
		$this->db->where('provinsi_id', $id);

		$result = $this->db->get('gbm_kabupaten');
		return $result->result_array();
	}



	public function retrieve_all()
	{

		// $this->db->order_by('nama', 'ASC');
		$this->db->select('*');
		$this->db->from('gbm_kabupaten ');
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
			'provinsi_id'=>$input['provinsi_id']['id']		
		);
		$this->db->where('id', $id);
		$this->db->update('gbm_kabupaten', $data);
		return true;
	}

	public function create(
		$input	
	) {
		$data = array(
			'nama'          => $input['nama'],
			'provinsi_id'=>$input['provinsi_id']['id']				
		);
		$this->db->insert('gbm_kabupaten', $data);
		return $this->db->insert_id();
	}
	
}
