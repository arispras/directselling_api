<?php

class InvAdjModel extends CI_Model
{



	public function retrieve_all(
		$no_of_records = 10,
		$page_no       = 1
	) {
		$no_of_records = (int)$no_of_records;
		$page_no       = (int)$page_no;

		$where = array();

		$data = $this->pager->set('inv_adj_ht', $no_of_records, $page_no, $where);

		return $data;
	}


	public function retrieve_all_kategori()
	{
		// $this->db->where('aktif' , 1);
		$this->db->order_by('id', 'ASC');
		$result = $this->db->get('inv_adj_ht');
		return $result->result_array();
	}


	public function delete($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$this->db->delete('inv_adj_ht');
		return true;
	}


	public function update($id, $input)
	{
		$id = (int)$id;

		$input['dibuat_tanggal'] = date('Y-m-d H:i:s');

		$ht['lokasi_id'] = $input['lokasi_id']['id'];
		$ht['gudang_id'] = $input['gudang_id']['id'];
		// $ht['supplier_id'] = $input['supplier_id']['id'];
		// $ht['no_pp'] = $input['no_pp'];
		$ht['no_ref'] = $input['no_ref'];
		$ht['no_transaksi'] = $input['no_transaksi'];
		$ht['catatan'] = $input['catatan'];
		$ht['tanggal'] = $input['tanggal'];
		$ht['diubah_oleh'] = $input['diubah_oleh'];
		$ht['diubah_tanggal'] = date("Y-m-d H:i:s");

		$this->db->where('id', $id);
		$this->db->update('inv_adj_ht', $ht);

		// hapus  detail
		$this->db->where('adj_hd_id', $id);
		$this->db->delete('inv_adj_dt');

		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("inv_adj_dt", array(
				'adj_hd_id' => $id,
				'item_id' => $value['item']['id'],
				'qty' => $value['qty'],
				'harga' => $value['harga'],
				'total' => $value['total'],
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
		$this->db->update('inv_adj_ht', $data);
		$data_transaksi = $this->db->query("select * from inv_adj_ht a
		inner join inv_adj_dt b on a.id=b.adj_hd_id  
		 where a.id=" . $id . ";")->result_array();

		// hapus  transaksi harian
		$this->db->where('ref_id', $id);
		$this->db->where('tipe', 'ADJUSTMENT');
		$this->db->delete('inv_transaksi_harian');

		foreach ($data_transaksi as $key => $value) {
			/* cari stok dan value akhir */
			$stok_akhir = $this->db->query("SELECT  SUM(qty_masuk-qty_keluar)AS qty,SUM(nilai_masuk-nilai_keluar)AS nilai from inv_transaksi_harian
				where item_id=" . $value['item_id'] . " and gudang_id=" . $value['gudang_id'] . " ")->row_array();
			$stok_akhir_qty = 	$stok_akhir['qty'] = (!empty($stok_akhir['qty'])) ? $stok_akhir['qty'] : 0;
			$stok_akhir_nilai = 	$stok_akhir['nilai'] = (!empty($stok_akhir['nilai'])) ? $stok_akhir['nilai'] : 0;
			$item_dt = $this->db->query("select * from inv_item_dt  where gudang_id=" . $value['gudang_id'] . "
			and item_id=" . $value['item_id'] . "")->row_array();

			/* item nambah */
			if ($value['qty'] > 0) {

				if ($item_dt) {
					$this->db->where('item_id', $value['item_id']);
					$this->db->where('gudang_id', $value['gudang_id']);
					$this->db->update("inv_item_dt", array(
						'item_id' => $value['item_id'],
						'gudang_id' => $value['gudang_id'],
						'qty' => $stok_akhir_qty + $value['qty'], // $item_dt['qty'] + $value['qty'],
						'nilai' => $stok_akhir_nilai + ($value['qty'] * $value['harga']) //  $item_dt['nilai'] + ($value['qty'] * $value['harga'])
					));
				} else {
					$this->db->insert("inv_item_dt", array(
						'item_id' => $value['item_id'],
						'gudang_id' => $value['gudang_id'],
						'qty' => $stok_akhir_qty + $value['qty'], // $item_dt['qty'] + $value['qty'],
						'nilai' => $stok_akhir_nilai + ($value['qty'] * $value['harga']) //  $item_dt['nilai'] + ($value['qty'] * $value['harga'])
					));
				}
				$this->db->insert("inv_transaksi_harian", array(
					'ref_id' => $id,
					'item_id' => $value['item_id'],
					'gudang_id' => $value['gudang_id'],
					'no_transaksi' => $value['no_transaksi'],
					'tipe ' => 'ADJUSTMENT',
					'tanggal' => $value['tanggal'],
					'tanggal_proses' => date('Y-m-d H:i:s'),
					'qty_masuk' => $value['qty'],
					'qty_keluar' => 0,
					'nilai_masuk' => $value['qty'] * $value['harga'],
					'nilai_keluar' => 0,
				));
			} else { /* item kurang */
				if ($item_dt) {
					$this->db->where('item_id', $value['item_id']);
					$this->db->where('gudang_id', $value['gudang_id']);
					$this->db->update("inv_item_dt", array(
						'item_id' => $value['item_id'],
						'gudang_id' => $value['gudang_id'],
						'qty' => $stok_akhir_qty + $value['qty'],  // $value['qty'] = minus 
						'nilai' => $stok_akhir_nilai + ($value['qty'] * $value['harga']) //  $item_dt['nilai'] + ($value['qty'] * $value['harga'])
					));
				} else {
					$this->db->insert("inv_item_dt", array(
						'item_id' => $value['item_id'],
						'gudang_id' => $value['gudang_id'],
						'qty' => $stok_akhir_qty + $value['qty'], // $value['qty'] = minus 
						'nilai' => $stok_akhir_nilai + ($value['qty'] * $value['harga']) //  $item_dt['nilai'] + ($value['qty'] * $value['harga'])
					));
				}
				$this->db->insert("inv_transaksi_harian", array(
					'ref_id' => $id,
					'item_id' => $value['item_id'],
					'gudang_id' => $value['gudang_id'],
					'no_transaksi' => $value['no_transaksi'],
					'tipe ' => 'ADJUSTMENT',
					'tanggal' => $value['tanggal'],
					'tanggal_proses' => date('Y-m-d H:i:s'),
					'qty_masuk' => 0,
					'qty_keluar' => $value['qty'] * -1,
					'nilai_masuk' => 0,
					'nilai_keluar' => ($value['qty'] * $value['harga']) * -1,
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
			// 			'nilai_masuk' => ($value['qty'] * $value['harga']),
			// 			'harga_hpp' => ($item_hpp['nilai'] + ($value['qty'] * $value['harga'])) / ($item_hpp['qty'] + $value['qty']),
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
		}

		return true;
	}
	public function retrieve($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$result = $this->db->get('inv_adj_ht', 1);
		return $result->row_array();
	}

	public function retrieve_detail($hdid)
	{
		// 	$this->db->select('*');
		// $this->db->from('inv_adj_dt');
		// $this->db->where('adj_hd_id', $hdid);
		$this->db->select('a.*,b.kode as kode_barang,b.nama as nama_barang,c.acc_akun_id,d.kode as uom');
		$this->db->from('inv_adj_dt a');
		$this->db->join('inv_item b', 'a.item_id = b.id', 'Left');
		$this->db->join('inv_kategori c', 'b.inv_kategori_id = c.id', 'Left');
		$this->db->join('gbm_uom d', 'b.uom_id = d.id', 'Left');
		$this->db->where('a.adj_hd_id', $hdid);
		$res = $this->db->get();
		return $res->result_array();
	}


	public function create($input)
	{
		$input['dibuat_tanggal'] = date('Y-m-d H:i:s');

		$ht['lokasi_id'] = $input['lokasi_id']['id'];
		$ht['gudang_id'] = $input['gudang_id']['id'];
		// $ht['supplier_id'] = $input['supplier_id']['id'];
		// $ht['no_pp'] = $input['no_pp'];
		$ht['catatan'] = $input['catatan'];
		$ht['no_ref'] = $input['no_ref'];
		$ht['no_transaksi'] = $input['no_transaksi'];
		$ht['tanggal'] = $input['tanggal'];
		$ht['dibuat_oleh'] = $input['dibuat_oleh'];
		$ht['dibuat_tanggal'] = date("Y-m-d H:i:s");
		$ht['diubah_oleh'] = $input['diubah_oleh'];
		$ht['diubah_tanggal'] = date("Y-m-d H:i:s");

		$this->db->insert('inv_adj_ht', $ht);
		$id = $this->db->insert_id();

		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("inv_adj_dt", array(
				'adj_hd_id' => $id,
				'item_id' => $value['item']['id'],
				'qty' => $value['qty'],
				'harga' => $value['harga'],
				'total' => $value['total'],
				'ket' => $value['ket'],
			));
		}

		return $id;
	}


	public function print_slip(
		$id = null
	) {
		$query = "SELECT 
            c.nama AS nama_produk,
            c.kode AS kode_produk,
            c.*,
            b.*,
            a.*
            -- c.kode,
            -- c.nama,
            -- c.satuan
		FROM inv_adj_ht a 
        inner join pks_tanki b on a.tanki_id = b.id
        inner join inv_item c on a.produk_id = c.id
	    -- inner join inv_item c on b.item=c.id
	    -- inner join inv_gudang d on a.gudang_id=d.id
		
        where 1=1 and a.id=" . $id . ";";
		$data = $this->db->query($query)->row_array();
		return $data;
	}
}
