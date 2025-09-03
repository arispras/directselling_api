<?php


class KlnTarifDokterModel extends CI_Model
{

	public function retrieve_all(
		$no_of_records = 10,
		$page_no       = 1
	) {
		$no_of_records = (int)$no_of_records;
		$page_no       = (int)$page_no;

		$where = array();

		$data = $this->pager->set('kln_tarif_dokter', $no_of_records, $page_no, $where);

		return $data;
	}


	//  Method untuk mendapatkan semua data kln_tarif_dokter tanpa pagging

	public function retrieve_all_kategori()
	{
		$this->db->order_by('status_karyawan', 'ASC');
		$result = $this->db->get('kln_tarif_dokter');
		return $result->result_array();
	}


	//   Method untuk menghapus record kln_tarif_dokter

	public function delete($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$this->db->delete('kln_tarif_dokter');
		return true;
	}


	public function update($id, $input)
	{
		$id = (int)$id;

		$data = array(
			'lokasi_id' => $input['lokasi_id']['id'],
			'karyawan_id' => $input['karyawan_id']['id'],
			// 'tanggal' => $input['tanggal'],
			'harga_rawat_jalan' => $input['harga_rawat_jalan'],
			'ket' => $input['ket'],
		);
		$this->db->where('id', $id);
		$this->db->update('kln_tarif_dokter', $data);
		return true;
	}


	//  Method untuk mengambil satu record kln_tarif_dokter

	public function retrieve($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$result = $this->db->get('kln_tarif_dokter', 1);
		return $result->row_array();
	}


	//  Method untuk membuat data kln_tarif_dokter

	public function create($input)
	{
		$data = array(
			'lokasi_id' => $input['lokasi_id']['id'],
			'karyawan_id' => $input['karyawan_id']['id'],
			// 'tanggal' => $input['tanggal'],
			'harga_rawat_jalan' => $input['harga_rawat_jalan'],
			'ket' => $input['ket'],

		);
		$this->db->insert('kln_tarif_dokter', $data);
		return $this->db->insert_id();
	}

	
}
