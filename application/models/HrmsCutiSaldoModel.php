<?php


class HrmsCutiSaldoModel extends CI_Model
{

	public function retrieve_all(
		$no_of_records = 10,
		$page_no       = 1
	) {
		$no_of_records = (int)$no_of_records;
		$page_no       = (int)$page_no;

		$where = array();

		$data = $this->pager->set('hrms_cuti_saldo', $no_of_records, $page_no, $where);

		return $data;
	}


	//  Method untuk mendapatkan semua data hrms_cuti_saldo tanpa pagging

	public function retrieve_all_kategori()
	{
		$this->db->order_by('status_karyawan', 'ASC');
		$result = $this->db->get('hrms_cuti_saldo');
		return $result->result_array();
	}


	//   Method untuk menghapus record hrms_cuti_saldo

	public function delete($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$this->db->delete('hrms_cuti_saldo');
		return true;
	}


	public function update($id, $input)
	{
		$id = (int)$id;

		$data = array(
			'lokasi_id' => $input['lokasi_id']['id'],
			'karyawan_id' => $input['karyawan_id']['id'],
			'tanggal' => $input['tanggal'],
			'jumlah' => $input['jumlah'],
			'ket' => $input['ket'],
		);
		$this->db->where('id', $id);
		$this->db->update('hrms_cuti_saldo', $data);
		return true;
	}


	//  Method untuk mengambil satu record hrms_cuti_saldo

	public function retrieve($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$result = $this->db->get('hrms_cuti_saldo', 1);
		return $result->row_array();
	}


	//  Method untuk membuat data hrms_cuti_saldo

	public function create($input)
	{
		$data = array(
			'lokasi_id' => $input['lokasi_id']['id'],
			'karyawan_id' => $input['karyawan_id']['id'],
			'tanggal' => $input['tanggal'],
			'jumlah' => $input['jumlah'],
			'ket' => $input['ket'],

		);
		$this->db->insert('hrms_cuti_saldo', $data);
		return $this->db->insert_id();
	}

	public function get_sisa_cuti($karyawan_id)
	{
		$karyawan_id = (int)$karyawan_id;
		$result = $this->db->query("select sum(jumlah)as sisa_cuti from hrms_cuti_saldo where karyawan_id=" . $karyawan_id . "")->row_array();
		$sisa_cuti = 0;
		if ($result) {
			$sisa_cuti = $result['sisa_cuti'];
		}
		return $sisa_cuti;
	}
}
