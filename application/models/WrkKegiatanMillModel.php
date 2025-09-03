<?php

class WrkKegiatanMillModel extends CI_Model
{


	
	public function create(
		$input = null
	) {
		$data = array(
			'lokasi_id' => $input['lokasi_id']['id'],
			'workshop_id' => $input['workshop_id']['id'],
			'tanggal' => $input['tanggal'],
			'tgl_mulai' => $input['tgl_mulai'],
			'tgl_akhir' => $input['tgl_akhir'],
			'no_transaksi' => $input['no_transaksi'],
			'km_hm_mulai' => $input['km_hm_mulai'],
			'km_hm_akhir' => $input['km_hm_akhir'],
			'lama_perbaikan' => $input['lama_perbaikan'],
			'kerusakan' => $input['kerusakan'],
			'alasan' => $input['alasan'],
			'mesin_id' =>  $input['mesin_id']['id'],
			'stasiun_id' =>  $input['stasiun_id']['id'],
			'dibuat_oleh'=> $input['dibuat_oleh'],
			'dibuat_tanggal'=> date("Y-m-d H:i:s"),
			'diubah_oleh'=> $input['diubah_oleh'],
			'diubah_tanggal'=> date("Y-m-d H:i:s"),
			'jam_mulai' => $input['jam_mulai'],
			'jam_akhir' => $input['jam_akhir'],
		);
		$this->db->insert('wrk_kegiatan_mill_ht', $data);
		$id = $this->db->insert_id();
		
		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("wrk_kegiatan_mill_dt", array(
				'wrk_kegiatan_mill_id' => $id,
				'karyawan_id' => $value['karyawan_id']['id'],
				'rupiah_hk' => $value['rupiah_hk'],
				'jumlah_hk' => $value['jumlah_hk'],
				'premi' => $value['premi'],
			));
		}

		$details_item = $input['details_item'];
		foreach ($details_item as $key => $value) {
			$this->db->insert("wrk_kegiatan_mill_item", array(
				'wrk_kegiatan_mill_id' => $id,
				'item_id' => $value['item_id']['id'],
				'qty' => $value['qty'],
				'ket' => $value['ket'],
			));
		}

		/* $details = $input['details_kegiatan'];
		foreach ($details as $key => $value) {
			$this->db->insert("trk_kegiatan_kendaraan_log", array(
				'wrk_kegiatan_mill_id' => $id,
				'blok_id' => $value['blok_id']['id'],
				'acc_kegiatan_id' => $value['kegiatan_id']['id'],
				'km_hm_mulai' => $value['km_hm_mulai'],
				'km_hm_akhir' => $value['km_hm_akhir'],
				'km_hm_jumlah' => $value['km_hm_jumlah'],
				'volume' => $value['volume'],
			));
		} */

			return $id;
	}
	public function update(
		$id,
		$input
	) {
		$data = array(
			'lokasi_id' => $input['lokasi_id']['id'],
			'workshop_id' => $input['workshop_id']['id'],
			'tanggal' => $input['tanggal'],
			'tgl_mulai' => $input['tgl_mulai'],
			'tgl_akhir' => $input['tgl_akhir'],
			'no_transaksi' => $input['no_transaksi'],
			'km_hm_mulai' => $input['km_hm_mulai'],
			'km_hm_akhir' => $input['km_hm_akhir'],
			'lama_perbaikan' => $input['lama_perbaikan'],
			'kerusakan' => $input['kerusakan'],
			'alasan' => $input['alasan'],
			'mesin_id' =>  $input['mesin_id']['id'],
			'stasiun_id' =>  $input['stasiun_id']['id'],
			'diubah_oleh'=> $input['diubah_oleh'],
			'diubah_tanggal'=> date("Y-m-d H:i:s"),
			'jam_mulai' => $input['jam_mulai'],
			'jam_akhir' => $input['jam_akhir'],
		);
		$this->db->where('id', $id);
		$this->db->update('wrk_kegiatan_mill_ht', $data);

		// hapus  detail
		$this->db->where('wrk_kegiatan_mill_id', $id);
		$this->db->delete('wrk_kegiatan_mill_dt');

		// hapus  detail item
		$this->db->where('wrk_kegiatan_mill_id', $id);
		$this->db->delete('wrk_kegiatan_mill_item');

		// $this->db->where('wrk_kegiatan_mill_id', $id);
		// $this->db->delete('trk_kegiatan_kendaraan_log');

		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("wrk_kegiatan_mill_dt", array(
				'wrk_kegiatan_mill_id' => $id,
				'karyawan_id' => $value['karyawan_id']['id'],
				'rupiah_hk' => $value['rupiah_hk'],
				'jumlah_hk' => $value['jumlah_hk'],
				'premi' => $value['premi'],
			));
		}

		$details_item = $input['details_item'];
		foreach ($details_item as $key => $value) {
			$this->db->insert("wrk_kegiatan_mill_item", array(
				'wrk_kegiatan_mill_id' => $id,
				'item_id' => $value['item_id']['id'],
				'qty' => $value['qty'],
				'ket' => $value['ket'],
			));
		}

		// $details = $input['details_kegiatan'];
		// foreach ($details as $key => $value) {
		// 	$this->db->insert("trk_kegiatan_kendaraan_log", array(
		// 		'wrk_kegiatan_mill_id' => $id,
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
		$this->db->where('wrk_kegiatan_mill_id', $id);
		$this->db->delete('wrk_kegiatan_mill_dt');
		$this->db->where('wrk_kegiatan_mill_id', $id);
		$this->db->delete('wrk_kegiatan_mill_item');
		// $this->db->where('wrk_kegiatan_mill_id', $id);
		// $this->db->delete('trk_kegiatan_kendaraan_log');
		$this->db->where('id', $id);
		$this->db->delete('wrk_kegiatan_mill_ht');
		return true;
	}
	public function posting($id,	$input) 
	{
		$id = (int)$id;
		$data['is_posting'] = 1;
		$data['diposting_tanggal'] = date('Y-m-d H:i:s');
		$data['diposting_oleh'] = 1;// $input['user_posting]
		$this->db->where('id', $id);
		$this->db->update('wrk_kegiatan_mill_ht', $data);
		

		return true;
	}
	public function retrieve_by_id($id)
	{
		$this->db->select('*');
		$this->db->from('wrk_kegiatan_mill_ht');
		$this->db->where('id', $id);
		$res = $this->db->get();
		return $res->row_array();
	}

	public function retrieve_detail($hdid)
	{
		$this->db->select('*');
		$this->db->from('wrk_kegiatan_mill_dt');
		$this->db->where('wrk_kegiatan_mill_id', $hdid);
		$res = $this->db->get();
		return $res->result_array();
	}
	
	public function retrieve_detail_item($hdid)
	{
		$this->db->select('*');
		$this->db->from('wrk_kegiatan_mill_item');
		$this->db->where('wrk_kegiatan_mill_id', $hdid);
		$res = $this->db->get();
		return $res->result_array();
	}

	public function retrieve_detail_log($hdid)
	{
		$this->db->select('*');
		$this->db->from('trk_kegiatan_kendaraan_log');
		$this->db->where('wrk_kegiatan_mill_id', $hdid);
		$res = $this->db->get();
		return $res->result_array();
	}


	public function print_slip(
		$id = null
	) {
		$query    = "Select a.*,b.qty,c.kode,c.nama,c.satuan
		FROM wrk_kegiatan_mill_ht a inner join wrk_kegiatan_mill_dt b
	   on a.id=b.wrk_kegiatan_mill_id
	   inner join inv_blok c on b.blok=c.id
	   inner join inv_gudang d on a.divisi_id=d.id
			where 1=1 and a.id=" . $id . ";";
		$data     = $this->db->query($query)->result_array();
		return $data;
	}
}
