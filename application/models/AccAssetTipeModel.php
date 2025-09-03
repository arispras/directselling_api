<?php


class AccAssetTipeModel extends CI_Model
{



	public function delete($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$this->db->delete('acc_asset_tipe');
		return true;
	}



	public function retrieve($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$result = $this->db->get('acc_asset_tipe', 1);
		return $result->row_array();
	}
	public function retrieve_all()
	{
		$this->db->order_by('nama', 'ASC');
		$this->db->get('acc_asset_tipe');
		$result = $this->db->get('acc_asset_tipe');
		return $result->result_array();
	}

	public function create(
		$arrdata
	) {

		$parent_id = $arrdata['parent_id']['id'];
		$nama = $arrdata['nama'];
		$data = array(
			'parent_id' => $parent_id,
			'nama' => $nama,
		);
		$this->db->insert('acc_asset_tipe', $data);
		return $this->db->insert_id();
	}

	public function update(
		$id,
		$arrdata
	) {


		$id = (int)$id;
		$parent_id = $arrdata['parent_id']['id'];
		$nama = $arrdata['nama'];
		$data = array(
			'parent_id' => $parent_id,
			'nama' => $nama,
		);
		$this->db->where('id', $id);
		$this->db->update('acc_asset_tipe', $data);
		return true;
	}

	public function laporan_area()
	{
		

		$query = "SELECT * FROM acc_asset_tipe_organisasi_vw";
		$data = $this->db->query($query)->result_array();
		return $data;
	}

}
