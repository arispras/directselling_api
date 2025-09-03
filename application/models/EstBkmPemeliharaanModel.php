<?php

class EstBkmPemeliharaanModel extends CI_Model
{


	
	public function create(
		$input = null
	) {
		$data = array(
			'lokasi_id' => $input['lokasi_id']['id'],
			'rayon_afdeling_id' => $input['afdeling_id']['id'],
			'tanggal' => $input['tanggal'],
			'no_transaksi' => $input['no_transaksi'],
			'mandor_id' =>  $input['mandor_id']['id'],
			'kerani_id' =>  $input['kerani_id']['id'],
			'asisten_id' =>  $input['asisten_id']['id'],
			'mandor_id' =>  $input['mandor_id']['id'],
			'hasil_kerja_mandor' =>  $input['mandor_hasil_kerja'],
			'jumlah_hk_mandor' =>  $input['mandor_jumlah_hk'],
			'rp_hk_mandor' =>  $input['mandor_rupiah_hk'],
			'premi_mandor' =>  $input['mandor_premi'],
			'denda_mandor' =>  $input['mandor_denda'],
			'ket_mandor' =>  $input['ket_mandor'],
			'ket_kerani' =>  $input['ket_kerani'],
			'hasil_kerja_kerani' =>  $input['kerani_hasil_kerja'],
			'jumlah_hk_kerani' =>  $input['kerani_jumlah_hk'],
			'rp_hk_kerani' =>  $input['kerani_rupiah_hk'],
			'premi_kerani' =>  $input['kerani_premi'],
			'denda_kerani' =>  $input['kerani_denda'],
			'is_premi_kontanan' => $input['is_premi_kontanan']	,
			'is_asistensi' => $input['is_asistensi']	,
			'is_asistensi_unit' => $input['is_asistensi_unit'],
			'dibuat_oleh'=> $input['dibuat_oleh'],
			'dibuat_tanggal'=> date('Y-m-d H:i:s'),
		);
		$this->db->insert('est_bkm_pemeliharaan_ht', $data);
		$id = $this->db->insert_id();
		
		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("est_bkm_pemeliharaan_dt", array(
				'bkm_pemeliharaan_id' => $id,
				'blok_id' => $value['blok_id']['id'],
				'acc_kegiatan_id' => $value['kegiatan_id']['id'],
				'karyawan_id' => $value['karyawan_id']['id'],
				'hasil_kerja' => $value['hasil_kerja'],
				'rupiah_hk' => $value['rupiah_hk'],
				'jumlah_hk' => $value['jumlah_hk'],
				'premi' => $value['premi'],
				'keterangan' => $value['keterangan'],
				'denda_pemeliharaan' => $value['denda_pemeliharaan'],
			));
		}

		$details = $input['details_item'];
		foreach ($details as $key => $value) {

			$this->db->insert("est_bkm_pemeliharaan_item", array(
				'bkm_pemeliharaan_id' => $id,
				'blok_id' => $value['blok_id']['id'],
				'kegiatan_id' => $value['kegiatan_id']['id'],
				'item_id' => $value['item_id']['id'],
				'qty' => $value['qty'],
			
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
			'rayon_afdeling_id' => $input['afdeling_id']['id'],
			'tanggal' => $input['tanggal'],
			'no_transaksi' => $input['no_transaksi'],
			'mandor_id' =>  $input['mandor_id']['id'],
			'kerani_id' =>  $input['kerani_id']['id'],
			'asisten_id' =>  $input['asisten_id']['id'],
			'mandor_id' =>  $input['mandor_id']['id'],
			'hasil_kerja_mandor' =>  $input['mandor_hasil_kerja'],
			'jumlah_hk_mandor' =>  $input['mandor_jumlah_hk'],
			'rp_hk_mandor' =>  $input['mandor_rupiah_hk'],
			'premi_mandor' =>  $input['mandor_premi'],
			'denda_mandor' =>  $input['mandor_denda'],
			'ket_mandor' =>  $input['ket_mandor'],
			'ket_kerani' =>  $input['ket_kerani'],
			'hasil_kerja_kerani' =>  $input['kerani_hasil_kerja'],
			'jumlah_hk_kerani' =>  $input['kerani_jumlah_hk'],
			'rp_hk_kerani' =>  $input['kerani_rupiah_hk'],
			'premi_kerani' =>  $input['kerani_premi'],
			'denda_kerani' =>  $input['kerani_denda'],
			'is_premi_kontanan' => $input['is_premi_kontanan']	,
			'is_asistensi' => $input['is_asistensi']	,
			'is_asistensi_unit' => $input['is_asistensi_unit'],
			'diubah_oleh'=> $input['diubah_oleh'],
			'diubah_tanggal'=> date('Y-m-d H:i:s'),
		);
		$this->db->where('id', $id);
		$this->db->update('est_bkm_pemeliharaan_ht', $data);

		// hapus  detail
		$this->db->where('bkm_pemeliharaan_id', $id);
		$this->db->delete('est_bkm_pemeliharaan_dt');

		$this->db->where('bkm_pemeliharaan_id', $id);
		$this->db->delete('est_bkm_pemeliharaan_item');


		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("est_bkm_pemeliharaan_dt", array(
				'bkm_pemeliharaan_id' => $id,
				'blok_id' => $value['blok_id']['id'],
				'acc_kegiatan_id' => $value['kegiatan_id']['id'],
				'karyawan_id' => $value['karyawan_id']['id'],
				'hasil_kerja' => $value['hasil_kerja'],
				'rupiah_hk' => $value['rupiah_hk'],
				'jumlah_hk' => $value['jumlah_hk'],
				'premi' => $value['premi'],
				'keterangan' => $value['keterangan'],
				'denda_pemeliharaan' => $value['denda_pemeliharaan'],
			));
		}

		$details = $input['details_item'];
		foreach ($details as $key => $value) {

			$this->db->insert("est_bkm_pemeliharaan_item", array(
				'bkm_pemeliharaan_id' => $id,
				'blok_id' => $value['blok_id']['id'],
				'kegiatan_id' => $value['kegiatan_id']['id'],
				'item_id' => $value['item_id']['id'],
				'qty' => $value['qty'],
			
			));
		}

		return $id;
	}


	public function delete($id)
	{
		$this->db->where('bkm_pemeliharaan_id', $id);
		$this->db->delete('est_bkm_pemeliharaan_dt');
		$this->db->where('bkm_pemeliharaan_id', $id);
		$this->db->delete('est_bkm_pemeliharaan_item');
		$this->db->where('id', $id);
		$this->db->delete('est_bkm_pemeliharaan_ht');
		return true;
	}

	public function retrieve_by_id($id)
	{
		$this->db->select('*');
		$this->db->from('est_bkm_pemeliharaan_ht');
		$this->db->where('id', $id);
		$res = $this->db->get();
		return $res->row_array();
	}
	public function posting($id,	$input) 
	{
		$id = (int)$id;
		$data['is_posting'] = 1;
		$data['diposting_tanggal'] = date('Y-m-d H:i:s');
		$data['diposting_oleh'] = $input['diposting_oleh'];
		$this->db->where('id', $id);
		$this->db->update('est_bkm_pemeliharaan_ht', $data);
		

		return true;
	}
	public function retrieve_detail($hdid)
	{
		$this->db->select('*');
		$this->db->from('est_bkm_pemeliharaan_dt');
		$this->db->where('bkm_pemeliharaan_id', $hdid);
		$res = $this->db->get();
		return $res->result_array();
	}
	
	public function retrieve_detail_item($hdid)
	{
		$this->db->select('*');
		$this->db->from('est_bkm_pemeliharaan_item');
		$this->db->where('bkm_pemeliharaan_id', $hdid);
		$res = $this->db->get();
		return $res->result_array();
	}


	public function print_slip(
		$id = null
	) {
		$query    = "Select a.*,b.qty,c.kode,c.nama,c.satuan
		FROM est_bkm_pemeliharaan_ht a inner join est_bkm_pemeliharaan_dt b
	   on a.id=b.bkm_pemeliharaan_id
	   inner join inv_blok c on b.blok=c.id
	   inner join inv_gudang d on a.divisi_id=d.id
			where 1=1 and a.id=" . $id . ";";
		$data     = $this->db->query($query)->result_array();
		return $data;
	}
}
