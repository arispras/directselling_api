<?php

class InvPemakaianBarangModel extends CI_Model
{


	public function create(
		$input = null
	) {
		$data = array(
			'inv_permintaan_id' => $input['inv_permintaan_id']['id'],
			'lokasi_id' => $input['lokasi_id']['id'],
			'gudang_id' => $input['gudang_id']['id'],
			'karyawan_id' => $input['karyawan_id']['id'],
			'lokasi_afd_id' => $input['lokasi_afd_id']['id'],
			'lokasi_traksi_id' => $input['lokasi_traksi_id']['id'],
			'tipe' => $input['tipe']['id'],
			'tanggal' => $input['tanggal'],
			'no_transaksi' => $input['no_transaksi'],
			'catatan' => $input['catatan'],
			'dibuat_oleh' => $input['dibuat_oleh'],
			'dibuat_tanggal' => date("Y-m-d H:i:s"),
			'diubah_oleh' => $input['dibuat_oleh'],
			'diubah_tanggal' => date("Y-m-d H:i:s"),
		);
		$this->db->insert('inv_pemakaian_ht', $data);
		$id = $this->db->insert_id();

		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("inv_pemakaian_dt", array(
				'inv_pemakaian_id' => $id,
				'item_id' => $value['item_id']['id'],
				// 'uom_id' => $value['uom_id']['id'],
				'qty' => $value['qty'],
				'traksi_id' => $value['traksi_id']['id'],
				'blok_id' => $value['blok_id']['id'],
				'kegiatan_id' => $value['kegiatan_id']['id'],
				'ket' => $value['ket'],
			));
		}


		return $id;
	}
	public function update($id,	$input)
	{
		$id = (int)$id;
		$data['inv_permintaan_id'] = $input['inv_permintaan_id']['id'];
		$data['lokasi_id'] = $input['lokasi_id']['id'];
		$data['lokasi_afd_id'] = $input['lokasi_afd_id']['id'];
		$data['lokasi_traksi_id'] = $input['lokasi_traksi_id']['id'];
		$data['gudang_id'] = $input['gudang_id']['id'];
		$data['karyawan_id'] = $input['karyawan_id']['id'];
		$data['tipe'] = $input['tipe']['id'];
		$data['no_transaksi'] = $input['no_transaksi'];
		$data['catatan'] = $input['catatan'];
		$data['tanggal'] = $input['tanggal'];
		$data['diubah_oleh'] = $input['diubah_oleh'];
		$data['diubah_tanggal'] = date("Y-m-d H:i:s");

		$this->db->where('id', $id);
		$this->db->update('inv_pemakaian_ht', $data);

		// hapus  detail
		$this->db->where('inv_pemakaian_id', $id);
		$this->db->delete('inv_pemakaian_dt');

		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("inv_pemakaian_dt", array(
				'inv_pemakaian_id' => $id,
				'item_id' => $value['item_id']['id'],
				// 'uom_id' => $value['uom_id']['id'],
				'traksi_id' => $value['traksi_id']['id'],
				'blok_id' => $value['blok_id']['id'],
				'kegiatan_id' => $value['kegiatan_id']['id'],
				'qty' => $value['qty'],
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
		$this->db->update('inv_pemakaian_ht', $data);
		$data_transaksi = $this->db->query("select * from inv_pemakaian_ht a
		inner join inv_pemakaian_dt b on a.id=b.inv_pemakaian_id  
		 where a.id=" . $id . ";")->result_array();

		// hapus  transaksi harian
		$this->db->where('ref_id', $id);
		$this->db->where('tipe', 'PEMAKAIAN');
		$this->db->delete('inv_transaksi_harian');

		foreach ($data_transaksi as $key => $value) {
			/* cari stok dan value akhir */
			$stok_akhir = $this->db->query("SELECT  SUM(qty_masuk-qty_keluar)AS qty,SUM(nilai_masuk-nilai_keluar)AS nilai from inv_transaksi_harian
					where	item_id=" . $value['item_id'] . " and gudang_id=" . $value['gudang_id'] . " ")->row_array();
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
			$this->db->insert("inv_transaksi_harian", array(
				'ref_id' => $id,
				'item_id' => $value['item_id'],
				'gudang_id' => $value['gudang_id'],
				'no_transaksi' => $value['no_transaksi'],
				'tipe ' => 'PEMAKAIAN',
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
			// $item_hpp = $this->db->query("select * from inv_item_hpp  where gudang_id=" . $value['gudang_id'] . "
			// and item_id=" . $value['item_id'] . " and tanggal<='" . $input['tanggal'] . "'
			// order by tanggal desc LIMIT 1")->row_array();
			// if ($item_hpp) {
			// 	if ($item_hpp['tanggal'] != $input['tanggal']) {
			// 		$this->db->insert("inv_item_hpp", array(
			// 			'item_id' => $value['item_id'],
			// 			'gudang_id' => $value['gudang_id'],
			// 			'qty' => 0,
			// 			'nilai' => 0,
			// 			'qty_masuk' => 0,
			// 			'nilai_masuk' => 0,
			// 			'harga_hpp' =>  $avg_price,
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
			// 			'nilai' =>  0,
			// 			'qty_masuk' => 0,
			// 			'nilai_masuk' => 0,
			// 			'harga_hpp' => ($item_hpp['harga_hpp'] +  $avg_price) / 2,
			// 			'tanggal' => $input['tanggal'],
			// 			'tanggal_proses' => date('Y-m-d H:i:s')

			// 		));
			// 	}
			// } else {
			// 	$this->db->insert("inv_item_hpp", array(
			// 		'item_id' => 0,
			// 		'gudang_id' => 0,
			// 		'qty' => 0,
			// 		'nilai' => 0,
			// 		'qty_masuk' => 0,
			// 		'nilai_masuk' => 0,
			// 		'harga_hpp' => $avg_price,
			// 		'tanggal' => $input['tanggal'],
			// 		'tanggal_proses' => date('Y-m-d H:i:s')
			// 	));
			// }
		}

		return true;
	}
	public function delete($id)
	{
		$this->db->where('inv_pemakaian_id', $id);
		$this->db->delete('inv_pemakaian_dt');
		$this->db->where('id', $id);
		$this->db->delete('inv_pemakaian_ht');
		return true;
	}

	public function retrieve_all()
	{
		$this->db->order_by('tanggal', 'ASC');
		$result = $this->db->get('inv_pemakaian_ht');
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
		$this->db->from('inv_pemakaian_dt');
		$this->db->where('inv_pemakaian_id', $id);

		return $this->db->get()->result_array();;
	}


	public function retrieve_by_id($id)
	{
		$this->db->select('*');
		$this->db->from('inv_pemakaian_ht');
		$this->db->where('id', $id);
		$res = $this->db->get();
		return $res->row_array();
	}

	public function retrieve_detail($hdid)
	{
		$query = "SELECT 
		a.id,
		a.no_transaksi,
		a.tanggal,
		b.item_id,
		b.qty,
		b.blok_id,
		b.kegiatan_id,
		b.traksi_id,
		b.ket,
		d.kode as kode_barang,
		d.nama as nama_barang,
		f.statusblok as umur_tanam_blok,
		f.tahuntanam,
		c.kode as kode_blok,
		c.nama as nama_blok,
		j.kode as kode_divisi,
		j.nama as nama_divisi,
		j.id as divisi_id,
		g.acc_akun_id,
		g.acc_akun_id ,
		h.acc_akun_id AS acc_akun_biaya_id,i.kode AS kode_kendaraan,i.nama AS nama_kendaraan
		FROM inv_pemakaian_ht a inner join inv_pemakaian_dt b on a.id=b.inv_pemakaian_id 
		left join gbm_organisasi c on b.blok_id=c.id 
		left join gbm_blok f on b.blok_id=f.organisasi_id
		inner join inv_item d on b.item_id=d.id
		left join inv_kategori g on d.inv_kategori_id=g.id
		left JOIN acc_kegiatan h ON b.kegiatan_id=h.id
		left JOIN  trk_kendaraan i ON b.traksi_id=i.id
		left join gbm_organisasi j on c.parent_id=j.id 
		where a.id=" . $hdid . "";
		$res = $this->db->query($query)->result_array();
		return $res;
	}
}
