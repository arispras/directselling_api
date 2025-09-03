<?php


class GbmSupplierKelompokModel extends CI_Model
{



	public function delete($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$this->db->delete('gbm_supplier_kelompok');
		return true;
	}



	public function retrieve($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$result = $this->db->get('gbm_supplier_kelompok', 1);
		return $result->row_array();
	}
	public function retrieve_all()
	{
		$this->db->order_by('nama_kelompok', 'ASC');
		$this->db->get('gbm_supplier_kelompok');
		$result = $this->db->get('gbm_supplier_kelompok');
		return $result->result_array();
	}

	public function create(
		$arrdata
	) {


		// $kode_supplier  =  $arrdata['kode_supplier'];
		$tipe_supplier  =  $arrdata['tipe_supplier'];
		$nama_kelompok    =  $arrdata['nama_kelompok'];
		
		$data = array(
		
		
			'tipe_supplier'  =>  $tipe_supplier,
			'nama_kelompok' => $nama_kelompok
		);
		$this->db->insert('gbm_supplier_kelompok', $data);
		return $this->db->insert_id();
	}

	public function update(
		$id,
		$arrdata
	) {


		$kelompok_id  = (int) $arrdata['kelompok_id'];
		$tipe_supplier  =  $arrdata['tipe_supplier'];
		$nama_kelompok    =  $arrdata['nama_kelompok'];


		$data = array(	
			'tipe_supplier'  =>  $tipe_supplier,
			'nama_kelompok' => $nama_kelompok
		);
		$this->db->where('id', $id);
		$this->db->update('gbm_supplier_kelompok', $data);
		return true;
	}
}
