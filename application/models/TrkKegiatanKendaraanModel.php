<?php

class TrkKegiatanKendaraanModel extends CI_Model
{	
	public function create(
		$input = null
	) {
		$data = array(
			'lokasi_id' => $input['lokasi_id']['id'],
			'traksi_id' => $input['traksi_id']['id'],
			'tanggal' => $input['tanggal'],
			'no_transaksi' => $input['no_transaksi'],
			'kendaraan_id' =>  $input['kendaraan_id']['id'],
			'status_kendaraan' =>  $input['status_kendaraan']['id'],
			'mandor_id' =>  $input['mandor_id']['id'],
			'is_asistensi' =>  $input['is_asistensi'],
			'dibuat_oleh'=> $input['dibuat_oleh'],
			'dibuat_tanggal'=> date("Y-m-d H:i:s"),
			'diubah_oleh'=> $input['diubah_oleh'],
			'diubah_tanggal'=> date("Y-m-d H:i:s"),
			
		);
		$this->db->insert('trk_kegiatan_kendaraan_ht', $data);
		$id = $this->db->insert_id();
		
		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("trk_kegiatan_kendaraan_dt", array(
				'trk_kegiatan_kendaraan_id' => $id,
				'karyawan_id' => $value['karyawan_id']['id'],
				// 'hasil_kerja' => $value['hasil_kerja'],
				'rupiah_hk' => $value['rupiah_hk'],
				'jumlah_hk' => $value['jumlah_hk'],
				'premi' => $value['premi'],
				'denda_traksi' => $value['denda_traksi'],
				'ket_denda' => $value['ket_denda'],
			));
		}

		$details = $input['details_kegiatan'];
		foreach ($details as $key => $value) {

			$this->db->insert("trk_kegiatan_kendaraan_log", array(
				'trk_kegiatan_kendaraan_id' => $id,
				'blok_id' => $value['blok_id']['id'],
				'acc_kegiatan_id' => $value['kegiatan_id']['id'],
				'km_hm_mulai' => $value['km_hm_mulai'],
				'km_hm_akhir' => $value['km_hm_akhir'],
				'km_hm_jumlah' => $value['km_hm_jumlah'],
				'volume' => $value['volume'],
				'ket' => $value['ket'],
			
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
			'traksi_id' => $input['traksi_id']['id'],
			'tanggal' => $input['tanggal'],
			'no_transaksi' => $input['no_transaksi'],
			'kendaraan_id' =>  $input['kendaraan_id']['id'],
			'status_kendaraan' =>  $input['status_kendaraan']['id'],
			'mandor_id' =>  $input['mandor_id']['id'],
			'is_asistensi' =>  $input['is_asistensi'],
			'diubah_oleh'=> $input['diubah_oleh'],
			'diubah_tanggal'=> date("Y-m-d H:i:s"),
			
		);
		$this->db->where('id', $id);
		$this->db->update('trk_kegiatan_kendaraan_ht', $data);

		// hapus  detail
		$this->db->where('trk_kegiatan_kendaraan_id', $id);
		$this->db->delete('trk_kegiatan_kendaraan_dt');

		$this->db->where('trk_kegiatan_kendaraan_id', $id);
		$this->db->delete('trk_kegiatan_kendaraan_log');


		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("trk_kegiatan_kendaraan_dt", array(
				'trk_kegiatan_kendaraan_id' => $id,
				'karyawan_id' => $value['karyawan_id']['id'],
				// 'hasil_kerja' => $value['hasil_kerja'],
				'rupiah_hk' => $value['rupiah_hk'],
				'jumlah_hk' => $value['jumlah_hk'],
				'premi' => $value['premi'],
				'denda_traksi' => $value['denda_traksi'],
				'ket_denda' => $value['ket_denda'],
			));
		}

		$details = $input['details_kegiatan'];
		foreach ($details as $key => $value) {

			$this->db->insert("trk_kegiatan_kendaraan_log", array(
				'trk_kegiatan_kendaraan_id' => $id,
				'blok_id' => $value['blok_id']['id'],
				'acc_kegiatan_id' => $value['kegiatan_id']['id'],
				'km_hm_mulai' => $value['km_hm_mulai'],
				'km_hm_akhir' => $value['km_hm_akhir'],
				'km_hm_jumlah' => $value['km_hm_jumlah'],
				'volume' => $value['volume'],
				'ket' => $value['ket'],
			
			));
		}

		return $id;
	}


	public function delete($id)
	{
		$this->db->where('trk_kegiatan_kendaraan_id', $id);
		$this->db->delete('trk_kegiatan_kendaraan_dt');
		$this->db->where('trk_kegiatan_kendaraan_id', $id);
		$this->db->delete('trk_kegiatan_kendaraan_log');
		$this->db->where('id', $id);
		$this->db->delete('trk_kegiatan_kendaraan_ht');
		return true;
	}
	public function posting($id,	$input) 
	{
		$id = (int)$id;
		$data['is_posting'] = 1;
		$data['diposting_tanggal'] = date('Y-m-d H:i:s');
		$data['diposting_oleh'] = $input['diposting_oleh'];// $input['user_posting]
		$this->db->where('id', $id);
		$this->db->update('trk_kegiatan_kendaraan_ht', $data);
		

		return true;
	}
	public function retrieve_by_id($id)
	{
		$this->db->select('*');
		$this->db->from('trk_kegiatan_kendaraan_ht');
		$this->db->where('id', $id);
		$res = $this->db->get();
		return $res->row_array();
	}

	public function retrieve_detail($hdid)
	{
		$this->db->select('*');
		$this->db->from('trk_kegiatan_kendaraan_dt');
		$this->db->where('trk_kegiatan_kendaraan_id', $hdid);
		$res = $this->db->get();
		return $res->result_array();
	}
	
	public function retrieve_detail_item($hdid)
	{
		$this->db->select('*');
		$this->db->from('trk_kegiatan_kendaraan_log');
		$this->db->where('trk_kegiatan_kendaraan_id', $hdid);
		$res = $this->db->get();
		return $res->result_array();
	}


	public function print_slip(
		$id = null
	) {
		$query    = "Select a.*,b.qty,c.kode,c.nama,c.satuan
		FROM trk_kegiatan_kendaraan_ht a inner join trk_kegiatan_kendaraan_dt b
	   on a.id=b.trk_kegiatan_kendaraan_id
	   inner join inv_blok c on b.blok=c.id
	   inner join inv_gudang d on a.divisi_id=d.id
			where 1=1 and a.id=" . $id . ";";
		$data     = $this->db->query($query)->result_array();
		return $data;
	}
}
