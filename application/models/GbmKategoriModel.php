<?php

class GbmKategoriModel extends CI_Model
{

	public function delete_foto($id)
	{
		$this->db->where('id', $id);
		$this->db->update('kategori', array('foto' => null));
		return true;
	}

	public function delete($id)
	{
		$id = (int)$id;
		$this->db->where('id', $id);
		$this->db->delete('kategori');
		return true;
	}


	public function retrieve($id = null)
	{

		$id = (int)$id;
		$this->db->where('id', $id);

		$result = $this->db->get('kategori', 1);
		return $result->row_array();
	}



	public function retrieve_all()
	{

		// $this->db->order_by('nama', 'ASC');
		$this->db->select('*');
		$this->db->from('kategori ');
		$result = $this->db->get();
		return $result->result_array();
	}

	public function update(
		$id,
		$input
	) {
		$id        = (int)$id;

		$data = array(
			'nama'          => $input['nama']	
		);
		$this->db->where('id', $id);
		$this->db->update('kategori', $data);
		return true;
	}

	public function create(
		$input	
	) {
		$data = array(
			'nama'          => $input['nama']		
		);
		$this->db->insert('kategori', $data);
		return $this->db->insert_id();
	}
	
}
