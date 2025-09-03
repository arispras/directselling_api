<?php

class InvPenerimaanPindahGudangModel extends CI_Model
{


	public function create(
		$input = null
	) {
		$data = array(
			'lokasi_id' => $input['lokasi_id']['id'],
			'tipe' => $input['tipe'],
			'dari_gudang_id' => $input['dari_gudang_id']['id'],
			'gudang_id' => $input['gudang_id']['id'],
			'inv_pindah_gudang_id' => $input['inv_pindah_gudang_id']['id'],
			'tanggal' => $input['tanggal'],
			'no_transaksi' => $input['no_transaksi'],
			'catatan' => $input['catatan'],
			'dibuat_oleh' => $input['dibuat_oleh'],
			'dibuat_tanggal' => date("Y-m-d H:i:s"),
			'diubah_oleh' => $input['diubah_oleh'],
			'diubah_tanggal' => date("Y-m-d H:i:s"),
		);
		$this->db->insert('inv_penerimaan_pindah_gudang_ht', $data);
		$id = $this->db->insert_id();

		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("inv_penerimaan_pindah_gudang_dt", array(
				'inv_penerimaan_pindah_gudang_id' => $id,
				'item_id' => $value['item_id']['id'],
				'qty' => $value['qty'],
				// 'traksi_id' => $value['traksi_id']['id'],
				// 'blok_id' => $value['blok_id']['id'],
				// 'kegiatan_id' => $value['kegiatan_id']['id'],
				'ket' => $value['ket'],
			));
		}


		return $id;
	}
	public function update($id,	$input)
	{
		$id = (int)$id;
		$data['lokasi_id'] = $input['lokasi_id']['id'];
		$data['tipe'] = $input['tipe'];
		$data['gudang_id'] = $input['gudang_id']['id'];
		$data['dari_gudang_id'] = $input['dari_gudang_id']['id'];
		$data['inv_pindah_gudang_id'] = $input['inv_pindah_gudang_id']['id'];
		$data['no_transaksi'] = $input['no_transaksi'];
		$data['catatan'] = $input['catatan'];
		$data['tanggal'] = $input['tanggal'];
		$data['diubah_oleh'] = $input['diubah_oleh'];
		$data['diubah_tanggal'] = date("Y-m-d H:i:s");

		$this->db->where('id', $id);
		$this->db->update('inv_penerimaan_pindah_gudang_ht', $data);

		// hapus  detail
		$this->db->where('inv_penerimaan_pindah_gudang_id', $id);
		$this->db->delete('inv_penerimaan_pindah_gudang_dt');

		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("inv_penerimaan_pindah_gudang_dt", array(
				'inv_penerimaan_pindah_gudang_id' => $id,
				'item_id' => $value['item_id']['id'],
				// 'traksi_id' => $value['traksi_id']['id'],
				// 'blok_id' => $value['blok_id']['id'],
				// 'kegiatan_id' => $value['kegiatan_id']['id'],
				'qty' => $value['qty'],
				'ket' => $value['ket'],
			));
		}

		return true;
	}

	public function posting($id,	$input)
	{
		$id = (int)$id;
		
		$data_transaksi = $this->db->query("select * from inv_penerimaan_pindah_gudang_ht a
		inner join inv_penerimaan_pindah_gudang_dt b on a.id=b.inv_penerimaan_pindah_gudang_id 
		 where a.id=" . $id . ";")->result_array();

		// hapus  transaksi harian
		$this->db->where('ref_id', $id);
		$this->db->where('tipe', 'PENERIMAAN_PINDAH_GUDANG');
		$this->db->delete('inv_transaksi_harian');
		foreach ($data_transaksi as $key => $value) {
			$q="Select * from inv_transaksi_harian  where tipe='PINDAH_GUDANG' and ref_id=" . $value['inv_pindah_gudang_id'] . "
			and item_id=" . $value['item_id'] . "";
			// var_dump($q);
			$inv_tr = $this->db->query($q)->row_array();
			$item_dt = $this->db->query("select * from inv_item_dt  where gudang_id=" . $value['gudang_id'] . "
			  and item_id=" . $value['item_id'] . "")->row_array();
			$avg_price = $inv_tr['nilai_keluar'] / $inv_tr['qty_keluar'];

			/* cari stok dan value akhir */
			$stok_akhir = $this->db->query("SELECT  SUM(qty_masuk-qty_keluar)AS qty,SUM(nilai_masuk-nilai_keluar)AS nilai from inv_transaksi_harian
				where item_id=" . $value['item_id'] . " and gudang_id=" . $value['gudang_id'] . " ")->row_array();
			$stok_akhir_qty = 	$stok_akhir['qty'] = (!empty($stok_akhir['qty'])) ? $stok_akhir['qty'] : 0;
			$stok_akhir_nilai = 	$stok_akhir['nilai'] = (!empty($stok_akhir['nilai'])) ? $stok_akhir['nilai'] : 0;

			if ($item_dt) {
				$this->db->where('item_id', $value['item_id']);
				$this->db->where('gudang_id', $value['gudang_id']);
				$this->db->update("inv_item_dt", array(
					'item_id' => $value['item_id'],
					'gudang_id' => $value['gudang_id'],
					'qty' => $stok_akhir_qty + $value['qty'], //$item_dt['qty'] + $value['qty'],
					'nilai' => $stok_akhir_nilai + ($value['qty'] * $avg_price) //$item_dt['nilai'] + ($value['qty'] * $avg_price)
				));
			} else {
				$this->db->insert("inv_item_dt", array(
					'item_id' => $value['item_id'],
					'gudang_id' => $value['gudang_id'],
					'qty' => $stok_akhir_qty + $value['qty'],
					'nilai' => $stok_akhir_nilai + ($value['qty'] * $avg_price)
				));
			}
			// $item_hpp = $this->db->query("select * from inv_item_hpp  where gudang_id=" . $value['gudang_id'] . "
			//   and item_id=" . $value['item_id'] . " and tanggal<='" . $input['tanggal'] . "'
			//   order by tanggal desc LIMIT 1")->row_array();
			// if ($item_hpp) {
			// 	if ($item_hpp['tanggal'] != $input['tanggal']) {
			// 		$this->db->insert("inv_item_hpp", array(
			// 			'item_id' => $value['item_id'],
			// 			'gudang_id' => $value['gudang_id'],
			// 			'qty' => 0,
			// 			'nilai' =>  0,
			// 			'qty_masuk' => 0,
			// 			'nilai_masuk' => 0,
			// 			'harga_hpp' => ($item_hpp['harga_hpp'] + $avg_price) / 2,
			// 			'tanggal' => $input['tanggal'],
			// 			'tanggal_proses' => date('Y-m-d H:i:s')
			// 		));
			// 	} else {
			// 		$this->db->where('item_id', $value['item_id']);
			// 		$this->db->where('gudang_id', $value['gudang_id']);
			// 		$this->db->where('tanggal', $input['tanggal']);
			// 		$this->db->update("inv_item_hpp", array(
			// 			'item_id' => $value['item_id'],
			// 			'gudang_id' => $value['gudang_id'],
			// 			'qty' => 0,
			// 			'nilai' => 0,
			// 			'qty_masuk' => 0,
			// 			'nilai_masuk' => 0,
			// 			'harga_hpp' => ($item_hpp['harga_hpp'] + $avg_price) / 2,
			// 			'tanggal' => $input['tanggal'],
			// 			'tanggal_proses' => date('Y-m-d H:i:s')

			// 		));
			// 	}
			// } else {
			// 	$this->db->insert("inv_item_hpp", array(
			// 		'item_id' => $value['item_id'],
			// 		'gudang_id' => $value['gudang_id'],
			// 		'qty' => 0,
			// 		'nilai' =>  0,
			// 		'qty_masuk' => 0,
			// 		'nilai_masuk' => 0,
			// 		'harga_hpp' => $avg_price,
			// 		'tanggal' => $input['tanggal'],
			// 		'tanggal_proses' => date('Y-m-d H:i:s')
			// 	));
			// }
			$this->db->insert("inv_transaksi_harian", array(
				'ref_id' => $id,
				'item_id' => $value['item_id'],
				'gudang_id' => $value['gudang_id'],
				'no_transaksi' => $value['no_transaksi'],
				'tipe ' => 'PENERIMAAN_PINDAH_GUDANG',
				'tanggal' => $value['tanggal'],
				'tanggal_proses' => date('Y-m-d H:i:s'),
				'qty_masuk' => $value['qty'],
				'qty_keluar' => 0,
				'nilai_masuk' => $avg_price * $value['qty'],
				'nilai_keluar' => 0,

			));
		}
		$data['is_posting'] = 1;
		$data['diposting_tanggal'] = date('Y-m-d H:i:s');
		$data['diposting_oleh'] = $input['diposting_oleh'];
		$this->db->where('id', $id);
		$this->db->update('inv_penerimaan_pindah_gudang_ht', $data);
		return true;
	}
	public function delete($id)
	{
		$this->db->where('inv_penerimaan_pindah_gudang_id', $id);
		$this->db->delete('inv_penerimaan_pindah_gudang_dt');
		$this->db->where('id', $id);
		$this->db->delete('inv_penerimaan_pindah_gudang_ht');
		return true;
	}

	public function retrieve_all()
	{
		$this->db->order_by('tanggal', 'ASC');
		$result = $this->db->get('inv_penerimaan_pindah_gudang_ht');
		return $result->result_array();
	}


	public function retrieve_all_detail($id)
	{
		$this->db->from('inv_penerimaan_pindah_gudang_dt');
		$this->db->where('inv_penerimaan_pindah_gudang_id', $id);

		return $this->db->get()->result_array();;
	}


	public function retrieve_by_id($id)
	{
		$this->db->select('*');
		$this->db->from('inv_penerimaan_pindah_gudang_ht');
		$this->db->where('id', $id);
		$res = $this->db->get();
		return $res->row_array();
	}

	public function retrieve_detail($hdid)
	{
		// $this->db->select('*');
		// $this->db->from('inv_penerimaan_pindah_gudang_dt');
		// $this->db->where('inv_penerimaan_pindah_gudang_id', $hdid);
		// $res = $this->db->get();
		// return $res->result_array();
		$query = "select a.*,e.acc_akun_id, b.kode as kode_barang,b.nama as nama_barang,d.kode as uom from inv_penerimaan_pindah_gudang_dt a
		inner join inv_item b on a.item_id = b.id
		left join gbm_uom d on b.uom_id=d.id 
		left join inv_kategori e on b.inv_kategori_id=e.id
		where a.inv_penerimaan_pindah_gudang_id=" . $hdid . "";
		$res = $this->db->query($query)->result_array();
		return $res;
	}
}
