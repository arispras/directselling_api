<?php

class HrmsAbsensiScanModel extends CI_Model
{
	public function getAbsensi($date = null,$karyawan_id=null)
	{

		if ($karyawan_id == null ) {
	
			$query = $this->db->query(
				"select a.*,b.nama,b.nip from payroll_absensi_scan a
				inner join karyawan b on a.karyawan_id=b.id
				where tanggal = '" . $date . "' "
			);
			return $query->result_array();
		} else {
			// return $this->db->get('payroll_absensi_scan')->result_array();
			// exit;
			$query = $this->db->query(
				"select * from payroll_absensi_scan 
				where tanggal = '" . $date . "' and karyawan_id=".$karyawan_id." "
			);
			return $query->result_array();
		}
	}



	public function Create(
		$data_absensi

	) {

		$data = array(
			'type'     => $data_absensi['type'],
			'karyawan_id'     => $data_absensi['karyawan_id'],
			'tanggal' =>   $data_absensi['tanggal'],
			'time_in'  => $data_absensi['time_in'],
			'time_out'  => $data_absensi['time_out'],
			'time'  => $data_absensi['time'],
			'status'     => $data_absensi['status'],
			'remarks'         => $data_absensi['remarks'],
			'file'        => $data_absensi['file'],
			'location'   => $data_absensi['location'],
			'lat'          => $data_absensi['lat'],
			'lng'     => $data_absensi['lng']

		);
		$this->db->insert('payroll_absensi_scan', $data);
		return $this->db->insert_id();
	}
}
