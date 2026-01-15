<?php

class GbmKecamatanModel extends CI_Model
{

	

	public function delete($id)
	{
		$id = (int)$id;
		$this->db->where('id', $id);
		$this->db->delete('gbm_kecamatan');
		return true;
	}


	public function retrieve_by_id($id = null)
	{

		$id = (int)$id;
		$this->db->where('id', $id);

		$result = $this->db->get('gbm_kecamatan', 1);
		return $result->row_array();
	}



	public function retrieve_all()
	{

		// $this->db->order_by('nama', 'ASC');
		$this->db->select('*');
		$this->db->from('gbm_kecamatan ');
		$result = $this->db->get();
		return $result->result_array();
	}

	public function update(
		$id,
		$input
	) {
		$id        = (int)$id;

		$data = array(
			'nama'          => $input['nama']	,
			'kabupaten_id'=>$input['kabupaten_id']['id']
		);
		$this->db->where('id', $id);
		$this->db->update('gbm_kecamatan', $data);
		return true;
	}

	public function create(
		$input	
	) {
		$data = array(
			'nama'          => $input['nama']	,
			'kabupaten_id'=>$input['kabupaten_id']['id']	
		);
		$this->db->insert('gbm_kecamatan', $data);
		return $this->db->insert_id();
	}
	
}
