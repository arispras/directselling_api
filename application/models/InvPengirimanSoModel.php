<?php

class InvPengirimanSoModel extends CI_Model
{


	public function create(
		$input = null
	) {
		$data = array(
			'lokasi_id' => $input['lokasi_id']['id'],
			'gudang_id' => $input['gudang_id']['id'],
			'customer_id' => $input['customer_id']['id'],
			'so_id' => $input['so_id']['id'],
			'tanggal' => $input['tanggal'],
			'catatan' => $input['catatan'],
			'no_transaksi' => $input['no_transaksi'],
			'no_ref' => $input['no_ref'],
			'dibuat_oleh' => $input['dibuat_oleh'],
			'dibuat_tanggal' => date("Y-m-d H:i:s"),
			'diubah_oleh' => $input['diubah_oleh'],
			'diubah_tanggal' => date("Y-m-d H:i:s"),
		);
		$this->db->insert('inv_pengiriman_so_ht', $data);
		$id = $this->db->insert_id();

		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("inv_pengiriman_so_dt", array(
				'pengiriman_so_id' => $id,
				'item_id' => $value['item_id']['id'],
				'qty' => $value['qty'],
				'harga' => $value['harga'],
				'diskon' => $value['diskon'],
				'ket' => $value['ket'],
				'so_dt_id' => $value['so_dt_id'],
			));
		}


		return $id;
	}
	public function update($id,	$input)
	{
		$id = (int)$id;
		$data['lokasi_id'] = $input['lokasi_id']['id'];
		$data['gudang_id'] = $input['gudang_id']['id'];
		$data['customer_id'] = $input['customer_id']['id'];
		$data['so_id'] = $input['so_id']['id'];
		$data['catatan'] = $input['catatan'];
		$data['no_transaksi'] = $input['no_transaksi'];
		$data['no_ref'] = $input['no_ref'];
		$data['tanggal'] = $input['tanggal'];
		$data['diubah_oleh'] = $input['diubah_oleh'];
		$data['diubah_tanggal'] = date("Y-m-d H:i:s");

		$this->db->where('id', $id);
		$this->db->update('inv_pengiriman_so_ht', $data);

		// hapus  detail
		$this->db->where('pengiriman_so_id', $id);
		$this->db->delete('inv_pengiriman_so_dt');

		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("inv_pengiriman_so_dt", array(
				'pengiriman_so_id' => $id,
				'item_id' => $value['item_id']['id'],
				'qty' => $value['qty'],
				'harga' => $value['harga'],
				'diskon' => $value['diskon'],
				'ket' => $value['ket'],
				'so_dt_id' => $value['so_dt_id'],
			));
		}

		return true;
	}

	public function save_upload($inv, $file)
	{
		$id = (int) $inv['id'];
		// $data['lokasi_id'] = $inv['lokasi_id'];
		// $data['gudang_id'] = $inv['gudang_id'];
		// $data['customer_id'] = $inv['customer_id'];
		// $data['so_id'] = $inv['so_id'];
		// $data['catatan'] = $inv['catatan'];
		// $data['no_transaksi'] = $inv['no_transaksi'];
		// $data['no_ref'] = $inv['no_ref'];
		// $data['tanggal'] = $inv['tanggal'];
		$data['upload_file'] = $file;

		$this->db->where('id', $id);
		$this->db->update('inv_pengiriman_so_ht', $data);

		return true;
	}



	public function posting($id,	$input)
	{
		$id = (int)$id;
		$data['is_posting'] = 1;
		$data['diposting_tanggal'] = date('Y-m-d H:i:s');
		$data['diposting_oleh'] = $input['diposting_oleh'];
		$this->db->where('id', $id);
		$this->db->update('inv_pengiriman_so_ht', $data);
		$data_transaksi = $this->db->query("select * from inv_pengiriman_so_ht a
		inner join inv_pengiriman_so_dt b on a.id=b.pengiriman_so_id 
		 where a.id=" . $id . ";")->result_array();

		// hapus  transaksi harian
		$this->db->where('ref_id', $id);
		$this->db->where('tipe', 'PENGIRIMAN_SO');
		$this->db->delete('inv_transaksi_harian');

		foreach ($data_transaksi as $key => $value) {


			// $stok_akhir_pertanggal = $this->db->query("SELECT  SUM(qty_masuk-qty_keluar)AS qty,SUM(nilai_masuk-nilai_keluar)AS amount from inv_transaksi_harian
			// where item_id=" . $value['item_id'] . " and gudang_id=" . $value['gudang_id'] . " AND tanggal <='" . $value['tanggal'] . "' ")->row_array();

			/* cari stok dan value akhir */
			$stok_akhir = $this->db->query("SELECT  SUM(qty_masuk-qty_keluar)AS qty,SUM(nilai_masuk-nilai_keluar)AS amount from inv_transaksi_harian
					where item_id=" . $value['item_id'] . " and gudang_id=" . $value['gudang_id'] . " ")->row_array();
			$stok_akhir_qty = 	$stok_akhir['qty'] = (!empty($stok_akhir['qty'])) ? $stok_akhir['qty'] : 0;
			$stok_akhir_nilai = 	$stok_akhir['nilai'] = (!empty($stok_akhir['nilai'])) ? $stok_akhir['nilai'] : 0;

			$item_dt = $this->db->query("select * from inv_item_dt  where gudang_id=" . $value['gudang_id'] . "
			and item_id=" . $value['item_id'] . "")->row_array();

			$avg_price = $stok_akhir_nilai/	$stok_akhir_qty;
			if ($item_dt) {
				// $avg_price = $item_dt['nilai'] / $item_dt['qty'];
				$this->db->where('item_id', $value['item_id']);
				$this->db->where('gudang_id', $value['gudang_id']);
				$this->db->update("inv_item_dt", array(
					'item_id' => $value['item_id'],
					'gudang_id' => $value['gudang_id'],
					'qty' =>  $stok_akhir_qty - $value['qty'],//$item_dt['qty'] - $value['qty'],
					'nilai' =>$stok_akhir_nilai - ($value['qty'] * $avg_price)// $item_dt['nilai'] - ($value['qty'] * $avg_price)
				));
			} else {
				$this->db->insert("inv_item_dt", array(
					'item_id' => $value['item_id'],
					'gudang_id' => $value['gudang_id'],
					'qty' => $stok_akhir_qty - $value['qty'],
					'nilai' => $stok_akhir_nilai - ($value['qty'] * $avg_price)
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
			// 			'qty' => $item_hpp['qty'] + $value['qty'],
			// 			'nilai' =>  $item_hpp['nilai'] + ($value['qty'] * $value['harga']),
			// 			'qty_masuk' => $value['qty'],
			// 			'nilai_masuk' => ($value['qty'] * $value['harga']) - $value['diskon'],
			// 			'harga_hpp' => ($item_hpp['nilai'] + ($value['qty'] * $value['harga']) - $value['diskon']) / ($item_hpp['qty'] + $value['qty']),
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
			// 			'qty' => $item_hpp['qty'] + $value['qty'],
			// 			'nilai' =>  $item_hpp['nilai'] + ($value['qty'] * $value['harga']),
			// 			'qty_masuk' => $value['qty'],
			// 			'nilai_masuk' => ($value['qty'] * $value['harga']),
			// 			'harga_hpp' => ($item_hpp['nilai'] + ($value['qty'] * $value['harga'])) / ($item_hpp['qty'] + $value['qty']),
			// 			'tanggal' => $input['tanggal'],
			// 			'tanggal_proses' => date('Y-m-d H:i:s')

			// 		));
			// 	}
			// } else {
			// 	$this->db->insert("inv_item_hpp", array(
			// 		'item_id' => $value['item_id'],
			// 		'gudang_id' => $value['gudang_id'],
			// 		'qty' => $value['qty'],
			// 		'nilai' => ($value['qty'] * $value['harga']),
			// 		'qty_masuk' => $value['qty'],
			// 		'nilai_masuk' => ($value['qty'] * $value['harga']),
			// 		'harga_hpp' => $value['harga'],
			// 		'tanggal' => $input['tanggal'],
			// 		'tanggal_proses' => date('Y-m-d H:i:s')
			// 	));
			// }
			// $this->db->insert("inv_transaksi_harian", array(
			// 	'ref_id' => $id,
			// 	'item_id' => $value['item_id'],
			// 	'gudang_id' => $value['gudang_id'],
			// 	'no_transaksi' => $value['no_transaksi'],
			// 	'tipe' => 'PENERIMAAN_PO',
			// 	'tanggal' => $value['tanggal'],
			// 	'tanggal_proses' => date('Y-m-d H:i:s'),
			// 	'qty_masuk' => $value['qty'],
			// 	'qty_keluar' => 0,
			// 	'nilai_masuk' => $nilai,
			// 	'nilai_keluar' => 0,

			// ));
			$this->db->insert("inv_transaksi_harian", array(
				'ref_id' => $id,
				'item_id' => $value['item_id'],
				'gudang_id' => $value['gudang_id'],
				'no_transaksi' => $value['no_transaksi'],
				'tipe ' => 'PENGIRIMAN_SO',
				'tanggal' => $value['tanggal'],
				'tanggal_proses' => date('Y-m-d H:i:s'),
				'qty_masuk' => 0,
				'qty_keluar' =>  $value['qty'],
				'nilai_masuk' => 0,
				'nilai_keluar' => $avg_price * $value['qty'],
				'blok_stasiun_id' =>  $value['blok_id'],
				'kendaraan_id' =>  $value['traksi_id'],
				'kegiatan_id' =>  $value['kegiatan_id'],

			));
		}

		return true;
	}


	public function delete($id)
	{
		$this->db->where('pengiriman_so_id', $id);
		$this->db->delete('inv_pengiriman_so_dt');
		$this->db->where('id', $id);
		$this->db->delete('inv_pengiriman_so_ht');
		return true;
	}

	public function retrieve_all()
	{
		$this->db->order_by('tanggal', 'ASC');
		$result = $this->db->get('inv_pengiriman_so_ht');
		return $result->result_array();
	}

	public function retrieve_traksi()
	{
		$this->db->order_by('nama', 'ASC');
		$result = $this->db->get('trk_kendaraan');
		return $result->result_array();
	}

	public function retrieve_all_detail($id)
	{
		$this->db->from('inv_pengiriman_so_dt');
		$this->db->where('pengiriman_so_id', $id);

		return $this->db->get()->result_array();;
	}


	public function retrieve_by_id($id)
	{
		$this->db->select('*');
		$this->db->from('inv_pengiriman_so_ht');
		$this->db->where('id', $id);
		$res = $this->db->get();
		return $res->row_array();
	}

	public function retrieve_detail($hdid)
	{
		$this->db->select('a.*,b.kode as kode_barang,b.nama as nama_barang,c.acc_akun_id,d.kode as uom,b.tipe_produk');
		$this->db->from('inv_pengiriman_so_dt a');
		$this->db->join('inv_item b', 'a.item_id = b.id', 'Left');
		$this->db->join('inv_kategori c', 'b.inv_kategori_id = c.id', 'Left');
		$this->db->join('gbm_uom d', 'b.uom_id = d.id', 'Left');
		$this->db->where('a.pengiriman_so_id', $hdid);
		$res = $this->db->get();
		return $res->result_array();
	}
}
