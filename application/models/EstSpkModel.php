<?php

class EstSpkModel extends CI_Model
{


	
	public function create(
		$input = null
	) {
		$data = array(
			'lokasi_id' => $input['lokasi_id']['id'],
			'supplier_id' => $input['supplier_id']['id'],
			'tanggal' => $input['tanggal'],
			'tgl_mulai' => $input['tgl_mulai'],
			'tgl_akhir' => $input['tgl_akhir'],
			'no_spk' => $input['no_spk'],
			'estimasi' => $input['estimasi'],

			'dibuat_oleh'=> $input['dibuat_oleh'],
			'dibuat_tanggal'=> date('Y-m-d H:i:s'),
		);
		$this->db->insert('est_spk_ht', $data);
		$id = $this->db->insert_id();
		
		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("est_spk_dt", array(
				'est_spk_id' => $id,
				'blok_id' => $value['blok_id']['id'],
				'kegiatan_id' => $value['kegiatan_id']['id'],
				'hk' => $value['hk'],
				'volume' => $value['volume'],
				'total' => $value['total'],
				'harga' => $value['harga'],
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
			'supplier_id' => $input['supplier_id']['id'],
			'tanggal' => $input['tanggal'],
			'tgl_mulai' => $input['tgl_mulai'],
			'tgl_akhir' => $input['tgl_akhir'],
			'no_spk' => $input['no_spk'],
			'estimasi' => $input['estimasi'],

			'diubah_oleh'=> $input['diubah_oleh'],
			'diubah_tanggal'=> date('Y-m-d H:i:s'),
		);
		$this->db->where('id', $id);
		$this->db->update('est_spk_ht', $data);

		// hapus  detail
		$this->db->where('est_spk_id', $id);
		$this->db->delete('est_spk_dt');

		// $this->db->where('est_spk_id', $id);
		// $this->db->delete('trk_kegiatan_kendaraan_log');


		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("est_spk_dt", array(
				'est_spk_id' => $id,
				'blok_id' => $value['blok_id']['id'],
				'kegiatan_id' => $value['kegiatan_id']['id'],
				'hk' => $value['hk'],
				'volume' => $value['volume'],
				'total' => $value['total'],
				'harga' => $value['harga'],
			));
		}

		// $details = $input['details_kegiatan'];
		// foreach ($details as $key => $value) {
		// 	$this->db->insert("trk_kegiatan_kendaraan_log", array(
		// 		'est_spk_id' => $id,
		// 		'blok_id' => $value['blok_id']['id'],
		// 		'acc_kegiatan_id' => $value['kegiatan_id']['id'],
		// 		'km_hm_mulai' => $value['km_hm_mulai'],
		// 		'km_hm_akhir' => $value['km_hm_akhir'],
		// 		'km_hm_jumlah' => $value['km_hm_jumlah'],
		// 		'volume' => $value['volume'],
		// 	));
		// }

		return $id;
	}


	public function delete($id)
	{
		$this->db->where('est_spk_id', $id);
		$this->db->delete('est_spk_dt');
		// $this->db->where('est_spk_id', $id);
		// $this->db->delete('trk_kegiatan_kendaraan_log');
		$this->db->where('id', $id);
		$this->db->delete('est_spk_ht');
		return true;
	}
	public function posting($id,	$input) 
	{
		$id = (int)$id;
		$data['is_posting'] = 1;
		$data['diposting_tanggal'] = date('Y-m-d H:i:s');
		$data['diposting_oleh'] = 1;// $input['user_posting]
		$this->db->where('id', $id);
		$this->db->update('est_spk_ht', $data);
		

		return true;
	}
	public function retrieve_all()
	{
		$this->db->select('a.*, b.nama AS lokasi, c.nama_supplier AS supplier');
		$this->db->from('est_spk_ht a');
		$this->db->join('gbm_organisasi b', 'a.lokasi_id = b.id');
		$this->db->join('gbm_supplier c', 'a.supplier_id = c.id');
		$res = $this->db->get();
		return $res->result_array();
	}
	public function retrieve_all_posted()
	{
		$this->db->select('a.*, b.nama AS lokasi, c.nama_supplier AS supplier');
		$this->db->from('est_spk_ht a');
		$this->db->join('gbm_organisasi b', 'a.lokasi_id = b.id');
		$this->db->join('gbm_supplier c', 'a.supplier_id = c.id');
		// $this->db->where('a.is_posting', 1);
		$this->db->where('a.is_close', 0);
		$res = $this->db->get();
		return $res->result_array();
	}
	public function retrieve_by_id($id)
	{
		$this->db->select('a.*, b.nama AS lokasi, c.nama_supplier AS supplier');
		$this->db->from('est_spk_ht a');
		$this->db->join('gbm_organisasi b', 'a.lokasi_id = b.id');
		$this->db->join('gbm_supplier c', 'a.supplier_id = c.id');
		$this->db->where('a.id', $id);
		$res = $this->db->get();
		return $res->row_array();
	}

	public function retrieve_detail($hdid)
	{
		$this->db->select('a.*, b.nama AS blok, c.nama AS kegiatan,d.kode as satuan');
		$this->db->from('est_spk_dt a');
		$this->db->join('gbm_organisasi b', 'a.blok_id = b.id');
		$this->db->join('acc_kegiatan c', 'a.kegiatan_id = c.id');
		$this->db->join('gbm_uom d', 'c.uom_id = d.id',"left");
		$this->db->where('a.est_spk_id', $hdid);
		$res = $this->db->get();
		return $res->result_array();
	}
	
	public function retrieve_detail_bapp($dtid)
	{
		$this->db->select('*');
		$this->db->from('est_spk_bapp');
		$this->db->where('spk_dt_id', $dtid);
		$res = $this->db->get();
		return $res->result_array();
	}


	public function print_slip(
		$id = null
	) {
		$query    = "Select a.*,b.qty,c.kode,c.nama,c.satuan
		FROM est_spk_ht a inner join est_spk_dt b
	   on a.id=b.est_spk_id
	   inner join inv_blok c on b.blok=c.id
	   inner join inv_gudang d on a.divisi_id=d.id
			where 1=1 and a.id=" . $id . ";";
		$data     = $this->db->query($query)->result_array();
		return $data;
	}
}
