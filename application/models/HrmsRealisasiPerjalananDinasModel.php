<?php

class HrmsRealisasiPerjalananDinasModel extends CI_Model
{


	public function create(
		$input = null
	) {
		$data = array(
			'lokasi_id' => $input['lokasi_id']['id'],
			'karyawan_id' => $input['karyawan_id']['id'],
			'dari_lokasi_id' => $input['dari_lokasi_id']['id'],
			'ke_lokasi_id' => $input['ke_lokasi_id']['id'],
			'tanggal' => $input['tanggal'],
			'no_transaksi' => $input['no_transaksi'],
			'perjalanan_dinas_id' => $input['perjalanan_dinas_id']['id'],
			'catatan' => $input['catatan'],
			'dibuat_oleh'=> $input['dibuat_oleh'],
			'dibuat_tanggal'=> date("Y-m-d H:i:s"),
			'diubah_oleh'=> $input['diubah_oleh'],
			'diubah_tanggal'=> date("Y-m-d H:i:s"),
		);
		$this->db->insert('hrms_realisasi_perjalanan_dinas_ht', $data);
		$id = $this->db->insert_id();

		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("hrms_realisasi_perjalanan_dinas_dt", array(
				'realisasi_perjalanan_dinas_id' => $id,
				'komponen_perjalanan_dinas_id' => $value['komponen_perjalanan_dinas_id']['id'],
				'nilai' => $value['nilai'],
				'ket' => $value['ket'],
			));
		}
		return $id;
	}
	public function update($id,	$input) 
	{
		$id = (int)$id;
		$data['lokasi_id'] = $input['lokasi_id']['id'];
		$data['karyawan_id'] = $input['karyawan_id']['id'];
		$data['dari_lokasi_id'] = $input['dari_lokasi_id']['id'];
		$data['ke_lokasi_id'] = $input['ke_lokasi_id']['id'];
		$data['no_transaksi'] = $input['no_transaksi'];
		$data['perjalanan_dinas_id'] = $input['perjalanan_dinas_id']['id'];
		$data['catatan'] = $input['catatan'];
		$data['tanggal'] = $input['tanggal'];
		$data['diubah_oleh'] = $input['diubah_oleh'];
		$data['diubah_tanggal'] = date("Y-m-d H:i:s");

		$this->db->where('id', $id);
		$this->db->update('hrms_realisasi_perjalanan_dinas_ht', $data);

		// hapus  detail
		$this->db->where('realisasi_perjalanan_dinas_id', $id);
		$this->db->delete('hrms_realisasi_perjalanan_dinas_dt');

		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("hrms_realisasi_perjalanan_dinas_dt", array(
				'realisasi_perjalanan_dinas_id' => $id,
				'komponen_perjalanan_dinas_id' => $value['komponen_perjalanan_dinas_id']['id'],
				'nilai' => $value['nilai'],
				'ket' => $value['ket'],
			));
		}

		return true;
	}

	public function posting($id,	$input) 
	{
		$id = (int)$id;
		$data['is_posting'] = 1;
		$data['diposting_tanggal'] = date('Y-m-d H:i:s');
		$data['diposting_oleh'] = $input['diposting_oleh'];
		$this->db->where('id', $id);
		$this->db->update('hrms_realisasi_perjalanan_dinas_ht', $data);
		

		return true;
	}
	public function delete($id)
	{
		$this->db->where('realisasi_perjalanan_dinas_id', $id);
		$this->db->delete('hrms_realisasi_perjalanan_dinas_dt');
		$this->db->where('id', $id);
		$this->db->delete('hrms_realisasi_perjalanan_dinas_ht');
		return true;
	}

	public function retrieve_all()
    {
        $this->db->order_by('tanggal', 'ASC');
        $result = $this->db->get('hrms_realisasi_perjalanan_dinas_ht');
        return $result->result_array();
    }
	
	public function retrieve_all_detail($id)
	{
		$this->db->from('hrms_realisasi_perjalanan_dinas_dt');
		$this->db->where('realisasi_perjalanan_dinas_id', $id);

		return $this->db->get()->result_array();;
	}


	public function retrieve_by_id($id)
	{
		$this->db->select('*');
		$this->db->from('hrms_realisasi_perjalanan_dinas_ht');
		$this->db->where('id', $id);
		$res = $this->db->get();
		return $res->row_array();
	}

	public function retrieve_detail($hdid)
	{
		$this->db->select('*');
		$this->db->from('hrms_realisasi_perjalanan_dinas_dt');
		$this->db->where('realisasi_perjalanan_dinas_id', $hdid);
		$res = $this->db->get();
		return $res->result_array();
	}

	public function print_slip($id)
	{
		$id = (int)$id;

		$query = "SELECT a.*,
		b.nama as lokasi_afd,
		b.nama as lokasi_traksi,
		d.nama as gudang,
		c.nama as karyawan,
		e.nama as lokasi
		FROM hrms_realisasi_perjalanan_dinas_ht a 
		LEFT JOIN gbm_organisasi b ON a.dari_lokasi_id=b.id
		LEFT JOIN gbm_organisasi bb ON a.ke_lokasi_id=bb.id
		LEFT JOIN gbm_organisasi d ON a.gudang_id=d.id
		LEFT JOIN gbm_organisasi e ON a.lokasi_id=e.id
		LEFT JOIN karyawan c on a.karyawan_id=c.id  WHERE a.id=" . $id . "";
		$data = $this->db->query($query)->row_array($id);
		return $data;
	}
}



