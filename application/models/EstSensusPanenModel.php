<?php

class EstSensusPanenModel extends CI_Model
{

	
	public function create(
		$input = null
	) {
		$data = array(
			'lokasi_id' => $input['lokasi_id']['id'],
			'afdeling_id' => $input['afdeling_id']['id'],
			'tahun' => $input['tahun'],
			'bulan' => $input['bulan']['id'],
			'ket' => $input['ket'],
			'dibuat_oleh'=> $input['dibuat_oleh'],
			'dibuat_tanggal'=> date('Y-m-d H:i:s'),
		);
		$this->db->insert('est_sensus_panen_ht', $data);
		$id = $this->db->insert_id();
		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("est_sensus_panen_dt", array(
				'sensus_panen_id' => $id,
				'blok_id' => $value['blok_id'],
				'jjg' => $value['jjg'],
				'kg' => $value['kg'],
				'bjr' => $value['bjr'],
				// 'kg_kebun' => $value['jumlah_kg'],
				
			));
		}
		return $id;
	}

	
	public function update(
		$id,
		$input
	) {
		$data = array(
			'lokasi_id' => $input['lokasi_id']['id'],
			'afdeling_id' => $input['afdeling_id']['id'],
			'tahun' => $input['tahun'],
			'bulan' => $input['bulan']['id'],
			'ket' => $input['ket'],
			'diubah_oleh'=> $input['diubah_oleh'],
			'diubah_tanggal'=> date('Y-m-d H:i:s'),
		);
		$this->db->where('id', $id);
		$this->db->update('est_sensus_panen_ht', $data);

		// hapus  detail
		$this->db->where('sensus_panen_id', $id);
		$this->db->delete('est_sensus_panen_dt');


		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("est_sensus_panen_dt", array(
				'sensus_panen_id' => $id,
				'blok_id' => $value['blok_id']['id'],
				'jjg' => $value['jjg'],
				'kg' => $value['kg'],
				'bjr' => $value['bjr'],
				// 'kg_kebun' => $value['jumlah_kg'],
			));
		}
		return $id;
	}


	public function delete($id)
	{

		$this->db->where('sensus_panen_id', $id);
		$this->db->delete('est_sensus_panen_dt');
		$this->db->where('id', $id);
		$this->db->delete('est_sensus_panen_ht');
		return true;
	}

	public function retrieve_by_id($id)
	{
		$this->db->select('est_sensus_panen_ht.*');
		$this->db->from('est_sensus_panen_ht');
		$this->db->where('est_sensus_panen_ht.id', $id);
		$res = $this->db->get();
		return $res->row_array();
	}

	public function retrieve_detail($hdid)
	{
		$this->db->select('est_sensus_panen_dt.*,gbm_organisasi.kode as kode_blok,gbm_organisasi.nama as nama_blok');
		$this->db->from('est_sensus_panen_dt');
		$this->db->join('gbm_organisasi', 'est_sensus_panen_dt.blok_id = gbm_organisasi.id');
		$this->db->where('sensus_panen_id', $hdid);
		$res = $this->db->get();
		return $res->result_array();
	}


	public function print_slip(
		$id = null
	) {
		$query    = "Select a.*,b.qty,c.kode,c.nama,c.satuan
		FROM est_sensus_panen_ht a inner join est_sensus_panen_dt b
	   on a.id=b.sensus_panen_id
	   inner join inv_blok c on b.blok=c.id
	   inner join inv_gudang d on a.divisi_id=d.id
			where 1=1 and a.id=" . $id . ";";
		$data     = $this->db->query($query)->result_array();
		return $data;
	}
}
