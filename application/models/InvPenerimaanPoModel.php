<?php

class InvPenerimaanPoModel extends CI_Model
{


	public function create(
		$input = null
	) {
		$data = array(
			'lokasi_id' => $input['lokasi_id']['id'],
			'gudang_id' => $input['gudang_id']['id'],
			'supplier_id' => $input['supplier_id']['id'],
			'po_id' => $input['po_id']['id'],
			'tanggal' => $input['tanggal'],
			'catatan' => $input['catatan'],
			'no_transaksi' => $input['no_transaksi'],
			'no_surat_jalan_supplier' => $input['no_surat_jalan_supplier'],
			'dibuat_oleh' => $input['dibuat_oleh'],
			'dibuat_tanggal' => date("Y-m-d H:i:s"),
			'diubah_oleh' => $input['diubah_oleh'],
			'diubah_tanggal' => date("Y-m-d H:i:s"),
		);
		$this->db->insert('inv_penerimaan_po_ht', $data);
		$id = $this->db->insert_id();

		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("inv_penerimaan_po_dt", array(
				'penerimaan_po_hd_id' => $id,
				'item_id' => $value['item_id']['id'],
				'qty' => $value['qty'],
				'harga' => $value['harga'],
				'diskon' => $value['diskon'],
				'ket' => $value['ket'],
				'po_dt_id' => $value['po_dt_id'],
			));
		}


		return $id;
	}
	public function update($id,	$input)
	{
		$id = (int)$id;
		$data['lokasi_id'] = $input['lokasi_id']['id'];
		$data['gudang_id'] = $input['gudang_id']['id'];
		$data['supplier_id'] = $input['supplier_id']['id'];
		$data['po_id'] = $input['po_id']['id'];
		$data['catatan'] = $input['catatan'];
		$data['no_transaksi'] = $input['no_transaksi'];
		$data['no_surat_jalan_supplier'] = $input['no_surat_jalan_supplier'];
		$data['tanggal'] = $input['tanggal'];
		$data['diubah_oleh'] = $input['diubah_oleh'];
		$data['diubah_tanggal'] = date("Y-m-d H:i:s");

		$this->db->where('id', $id);
		$this->db->update('inv_penerimaan_po_ht', $data);

		// hapus  detail
		$this->db->where('penerimaan_po_hd_id', $id);
		$this->db->delete('inv_penerimaan_po_dt');

		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("inv_penerimaan_po_dt", array(
				'penerimaan_po_hd_id' => $id,
				'item_id' => $value['item_id']['id'],
				'qty' => $value['qty'],
				'harga' => $value['harga'],
				'diskon' => $value['diskon'],
				'ket' => $value['ket'],
				'po_dt_id' => $value['po_dt_id'],
			));
		}

		return true;
	}

	public function save_upload($inv, $file)
	{
		$id = (int) $inv['id'];
		// $data['lokasi_id'] = $inv['lokasi_id'];
		// $data['gudang_id'] = $inv['gudang_id'];
		// $data['supplier_id'] = $inv['supplier_id'];
		// $data['po_id'] = $inv['po_id'];
		// $data['catatan'] = $inv['catatan'];
		// $data['no_transaksi'] = $inv['no_transaksi'];
		// $data['no_surat_jalan_supplier'] = $inv['no_surat_jalan_supplier'];
		// $data['tanggal'] = $inv['tanggal'];
		$data['upload_file'] = $file;

		$this->db->where('id', $id);
		$this->db->update('inv_penerimaan_po_ht', $data);

		return true;
	}



	public function posting($id,	$input)
	{
		$id = (int)$id;
		$data['is_posting'] = 1;
		$data['diposting_tanggal'] = date('Y-m-d H:i:s');
		$data['diposting_oleh'] = $input['diposting_oleh'];
		$this->db->where('id', $id);
		$this->db->update('inv_penerimaan_po_ht', $data);
		$data_transaksi = $this->db->query("select * from inv_penerimaan_po_ht a
		inner join inv_penerimaan_po_dt b on a.id=b.penerimaan_po_hd_id 
		 where a.id=" . $id . ";")->result_array();

		// hapus  transaksi harian
		$this->db->where('ref_id', $id);
		$this->db->where('tipe', 'PENERIMAAN_PO');
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

			$nilai = 0;
			if ($input['is_solar'] == true) {
				// Cari qty di PO Detail nya ///	
				$po_dt =	$this->db->query("select * from prc_po_dt where id=" . $value['po_dt_id'])->row_array();
				$qty_po_dt = $po_dt['qty'];
				$ppbkb_nilai_proporsi =  ($value['qty'] / $qty_po_dt) * $input['ppbkb_nilai'];
				$biaya_kirim_proporsi = ($value['qty'] / $qty_po_dt) * $input['biaya_kirim'];
				$nilai = (($value['harga'] * $value['qty']) - $value['diskon']) + $ppbkb_nilai_proporsi +	$biaya_kirim_proporsi;
			} else {
				$nilai = ($value['qty'] * $value['harga']) - $value['diskon'];
			}
			if ($item_dt) {
				$this->db->where('item_id', $value['item_id']);
				$this->db->where('gudang_id', $value['gudang_id']);
				$this->db->update("inv_item_dt", array(
					'item_id' => $value['item_id'],
					'gudang_id' => $value['gudang_id'],
					'qty' => $stok_akhir_qty + $value['qty'],
					'nilai' => $stok_akhir_nilai + $nilai
				));
			} else {
				$this->db->insert("inv_item_dt", array(
					'item_id' => $value['item_id'],
					'gudang_id' => $value['gudang_id'],
					'qty' => $stok_akhir_qty + $value['qty'],
					'nilai' => $stok_akhir_nilai + $nilai
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
			$this->db->insert("inv_transaksi_harian", array(
				'ref_id' => $id,
				'item_id' => $value['item_id'],
				'gudang_id' => $value['gudang_id'],
				'no_transaksi' => $value['no_transaksi'],
				'tipe' => 'PENERIMAAN_PO',
				'tanggal' => $value['tanggal'],
				'tanggal_proses' => date('Y-m-d H:i:s'),
				'qty_masuk' => $value['qty'],
				'qty_keluar' => 0,
				'nilai_masuk' => $nilai,
				'nilai_keluar' => 0,

			));
		}

		return true;
	}


	public function delete($id)
	{
		$this->db->where('penerimaan_po_hd_id', $id);
		$this->db->delete('inv_penerimaan_po_dt');
		$this->db->where('id', $id);
		$this->db->delete('inv_penerimaan_po_ht');
		return true;
	}

	public function retrieve_all()
	{
		$this->db->order_by('tanggal', 'ASC');
		$result = $this->db->get('inv_penerimaan_po_ht');
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
		$this->db->from('inv_penerimaan_po_dt');
		$this->db->where('penerimaan_po_hd_id', $id);

		return $this->db->get()->result_array();;
	}


	public function retrieve_by_id($id)
	{
		$this->db->select('*');
		$this->db->from('inv_penerimaan_po_ht');
		$this->db->where('id', $id);
		$res = $this->db->get();
		return $res->row_array();
	}

	public function retrieve_detail($hdid)
	{
		$this->db->select('a.*,b.kode as kode_barang,b.nama as nama_barang,c.acc_akun_id,d.kode as uom,b.tipe_produk');
		$this->db->from('inv_penerimaan_po_dt a');
		$this->db->join('inv_item b', 'a.item_id = b.id', 'Left');
		$this->db->join('inv_kategori c', 'b.inv_kategori_id = c.id', 'Left');
		$this->db->join('gbm_uom d', 'b.uom_id = d.id', 'Left');
		$this->db->where('a.penerimaan_po_hd_id', $hdid);
		$res = $this->db->get();
		return $res->result_array();
	}
}
