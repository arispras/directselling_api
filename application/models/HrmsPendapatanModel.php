<?php


class HrmsPendapatanModel extends CI_Model
{



	public function delete($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$this->db->delete('payroll_pendapatan');
		return true;
	}



	public function retrieve($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$result = $this->db->get('payroll_pendapatan', 1);
		return $result->row_array();
	}
	public function retrieve_all_item()
	{
		$this->db->order_by('nama', 'ASC');
		$this->db->get('payroll_pendapatan');
		$result = $this->db->get('payroll_pendapatan');
		return $result->result_array();
	}

	public function create(
		$arrdata
	) {

		$tanggal = $arrdata['tanggal'];
		$karyawan_id  = (int) $arrdata['karyawan_id'];
		$lokasi_id  = (int) $arrdata['lokasi_id'];
		$nilai_pendapatan    =  $arrdata['nilai_pendapatan'];
		$tipe_gaji_id    =  $arrdata['tipe_gaji_id'];
		$keterangan    =  $arrdata['keterangan'];
		$data = array(
			'karyawan_id'    => $karyawan_id,
			'lokasi_id'    => $lokasi_id,
			'nilai' => $nilai_pendapatan,
			'tanggal' => $tanggal,
			'keterangan' => $keterangan,
			'tipe_gaji_id' => $tipe_gaji_id
		);
		$this->db->insert('payroll_pendapatan', $data);
		return $this->db->insert_id();
	}

	public function update(
		$id,
		$arrdata
	) {


		$id = (int)$id;

		$tanggal = $arrdata['tanggal'];
		$karyawan_id  = (int) $arrdata['karyawan_id'];
		$lokasi_id  = (int) $arrdata['lokasi_id'];
		$nilai_pendapatan    =  $arrdata['nilai_pendapatan'];
		$tipe_gaji_id    =  $arrdata['tipe_gaji_id'];
		$keterangan    =  $arrdata['keterangan'];
		$data = array(
			'karyawan_id'    => $karyawan_id,
			'lokasi_id'    => $lokasi_id,
			'nilai' => $nilai_pendapatan,
			'tanggal' => $tanggal,
			'keterangan' => $keterangan,
			'tipe_gaji_id' => $tipe_gaji_id
		);
		$this->db->where('id', $id);
		$this->db->update('payroll_pendapatan', $data);
		return true;
	}
}
