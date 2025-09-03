<?php

class EstInspeksiModel extends CI_Model
{
	public function getInspeksi($date = null)
	{

		// if ($siswaid === null && $date === null) {
		if ($date === null) {
			// return $this->db->get('presensi')->result_array();
			return $this->db->get('est_inspeksi')->result_array();
		} else {
			// return $this->db->get('est_inspeksi')->result_array();
			// exit;
			$query = $this->db->query(
				"select * from est_inspeksi 
				where tanggal = '" . $date . "' "
			);
			return $query->result_array();
		}
	}



	public function Create(
		$data_inspeksi

	) {

		$data = array(
			'tipe'     => $data_inspeksi['tipe'],
			'tanggal' =>   $data_inspeksi['tanggal'],
			'jam'  => $data_inspeksi['jam'],
			'status'     => $data_inspeksi['status'],
			'catatan'         => $data_inspeksi['catatan'],
			'file'        => $data_inspeksi['file'],
			'nama_lokasi'   => $data_inspeksi['nama_lokasi'],
			'lat'          => $data_inspeksi['lat'],
			'lng'     => $data_inspeksi['lng']

		);
		$this->db->insert('est_inspeksi', $data);
		return $this->db->insert_id();
	}
	public function UpdateStatus(
		$data_inspeksi

	) {
		$data = array(
			'status'     => $data_inspeksi['status'],
			'catatan'         => $data_inspeksi['catatan']
		);
		$this->db->where('id', $data_inspeksi['id']);
		$this->db->update('est_inspeksi', $data);
		return true;
	}
}
