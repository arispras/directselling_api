<?php

class PksPengolahanModel extends CI_Model
{


	public function print_spat(
		$divisi_id = null,
		$blok_id = null,
		$tanggal_mulai = null,
		$tanggal_akhir = null

	) {
		$querySaldoAwal = "SELECT a.divisi_id, b.blok,
        sum(case a.jenis when  '0' then b.qty else b.qty*-1 end) as stok FROM pks_pengolahan_ht a inner join pks_pengolahan_dt b
        on a.id=b.pengolahan_id
        inner join inv_blok c on b.blok=c.id
        inner join inv_gudang d on a.divisi_id=d.id
        where b.blok=" . $blok_id . " and  a.divisi_id=" . $divisi_id . " and a.tanggal < '" . $tanggal_mulai . "'
         group by  a.divisi_id,b.blok ;";


		$saldoAwal    = $this->db->query($querySaldoAwal)->row_array();
		$data['saldo_awal'] = (!empty($saldoAwal)) ? $saldoAwal['stok'] : 0;
		//    print_r($querySaldoAwal);exit();

		$queryTransaksi   = "SELECT a.divisi_id,d.nama as gudang,a.tanggal,a.no_spat,b.keterangan, a.jenis,b.blok,c.kode, c.nama,c.satuan,
        b.qty FROM pks_pengolahan_ht a inner join pks_pengolahan_dt b
         on a.id=b.pengolahan_id
         inner join inv_blok c on b.blok=c.id
         inner join inv_gudang d on a.divisi_id=d.id
         where b.blok=" . $blok_id . " and  a.divisi_id=" . $divisi_id . "
          and a.tanggal >= '" . $tanggal_mulai . "'   and a.tanggal <= '" . $tanggal_akhir . "'
          order by a.tanggal,a.id  ;
          ";


		$data['transaksi']     = $this->db->query($queryTransaksi)->result_array();
		// print_r($data);exit();
		return $data;
	}
	public function print_rekap_stok(
		$divisi_id = null,
		$kategori_id = null,
		$tanggal_mulai = null,
		$tanggal_akhir = null

	) {
		$res = array();
		$queryblok = "select a.*, b.nama as nama_kategori
       from inv_blok a inner join inv_kategori b on a.inv_kategori_id=b.id
       where 1=1 ";
		if (!empty($kategori_id)) {
			if (($kategori_id != 'all')) {
				$queryblok = $queryblok . " and b.id=" . $kategori_id . "";
			}
		}

		$bloks   = $this->db->query($queryblok)->result_array();
		foreach ($bloks as $key => $blok) {
			$querySaldoAwal = "SELECT a.divisi_id,d.nama as gudang,b.blok,c.kode, c.nama,c.satuan,
           sum(case a.jenis when  '0' then b.qty else b.qty*-1 end) as stok FROM pks_pengolahan_ht a inner join pks_pengolahan_dt b
           on a.id=b.pengolahan_id
           inner join inv_blok c on b.blok=c.id
           inner join inv_gudang d on a.divisi_id=d.id
           where b.blok=" . $blok['id'] . " and  a.tanggal < '" . $tanggal_mulai . "'
           and a.divisi_id=" . $divisi_id . "
            group by  a.divisi_id,b.blok";

			$awal   = $this->db->query($querySaldoAwal)->row_array();
			$blok['saldo_awal'] = (!empty($awal)) ? $awal['stok'] : 0;
			// print_r($querySaldoAwal);

			$queryMasuk = "SELECT a.divisi_id,b.blok,
            sum(b.qty) as stok FROM pks_pengolahan_ht a inner join pks_pengolahan_dt b
            on a.id=b.pengolahan_id
            inner join inv_blok c on b.blok=c.id
            inner join inv_gudang d on a.divisi_id=d.id
            where b.blok=" . $blok['id'] . " and  a.tanggal >= '" . $tanggal_mulai . "'
            and  a.tanggal <= '" . $tanggal_akhir . "' and  a.jenis=0
            and a.divisi_id=" . $divisi_id . "
             group by  a.divisi_id,b.blok";

			$masuk   = $this->db->query($queryMasuk)->row_array();
			$blok['masuk'] = (!empty($masuk['stok'])) ? $masuk['stok'] : 0;
			//  print_r($queryMasuk);
			$queryKeluar = "SELECT a.divisi_id,b.blok,
             sum( b.qty ) as stok FROM pks_pengolahan_ht a inner join pks_pengolahan_dt b
             on a.id=b.pengolahan_id
             inner join inv_blok c on b.blok=c.id
             inner join inv_gudang d on a.divisi_id=d.id
             where b.blok=" . $blok['id'] . " and  a.tanggal >= '" . $tanggal_mulai . "'
            and  a.tanggal <= '" . $tanggal_akhir . "'  and  a.jenis=1
            and a.divisi_id=" . $divisi_id . "
              group by  a.divisi_id,b.blok";
			$keluar   = $this->db->query($queryKeluar)->row_array();
			$blok['keluar'] = (!empty($keluar['stok'])) ? $keluar['stok'] : 0;
			//   print_r($queryKeluar);
			$res[] = $blok;
		}

		$data['bloks']     =   $res;
		//  print_r($blok);exit();
		return $data;
	}

	
	public function create(
		$input = null
	) {
		$data = array(
			'mill_id' => $input['mill_id']['id'],
			'tanggal' => $input['tanggal'],
			'no_transaksi' => $input['no_transaksi'],
			'total_jam_proses' => $input['total_jam_proses'],
			'total_jumlah_rebusan' => $input['total_jumlah_rebusan'],
			'tbs_olah' => $input['tbs_olah'],
			'dibuat_oleh'=> $input['dibuat_oleh'],
			'dibuat_tanggal'=> date('Y-m-d H:i:s'),
		);
		$this->db->insert('pks_pengolahan_ht', $data);
		$id = $this->db->insert_id();
		
		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("pks_pengolahan_dt", array(
				'pengolahan_id' => $id,
				'shift_id' => $value['shift_id']['id'],
				'jam_masuk' => $value['jam_masuk'],
				'jam_selesai' => $value['jam_selesai'],
				'mandor_id' => $value['mandor_id']['id'],
				'asisten_id' => $value['asisten_id']['id'],
				'jam_proses' => $value['jam_proses'],
				'jumlah_rebusan' => $value['jumlah_rebusan'],
			));
		}

		$details = $input['details_mesin'];
		foreach ($details as $key => $value) {
			$mesin = $this->GbmOrganisasiModel->retrieve($value['mesin_id']['id']);
			// $value['stasiun'] = $this->GbmOrganisasiModel->retrieve($mesin['parent_id']);
			$this->db->insert("pks_pengolahan_mesin", array(
				'pengolahan_id' => $id,
				'mesin_id' => $value['mesin_id']['id'],
				'jam_masuk' => $value['jam_masuk'],
				'jam_selesai' => $value['jam_selesai'],
				'jumlah_jam' => $value['jumlah_jam'],
				// 'station_id' => $value['stasiun']['id'],
				'keterangan' => $value['keterangan'],
			));
		}

		$data = array(
			'mill_id' => $input['mill_id']['id'],
			'no_transaksi' => $input['no_transaksi'],
			'tanggal' => $input['tanggal'],
			'pengolahan_id'=> $id,
	
			'cpo_moisture' => $input['cpo_moisture'],
			'cpo_dobi' => $input['cpo_dobi'],
			'cpo_ffa' => $input['cpo_ffa'],
			'cpo_dirt' => $input['cpo_dirt'],
			
			'kernel_moisture' => $input['kernel_moisture'],
			'kernel_dobi' => $input['kernel_dobi'],
			'kernel_ffa' => $input['kernel_ffa'],
			'kernel_dirt' => $input['kernel_dirt'],
			
			'cpo_los_fruit' => $input['cpo_los_fruit'],
			'cpo_los_press' => $input['cpo_los_press'],
			'cpo_los_nut' => $input['cpo_los_nut'],
			'cpo_los_e_bunch' => $input['cpo_los_e_bunch'],
			'cpo_los_effluent' => $input['cpo_los_effluent'],
			
			'kernel_los_fruit' => $input['kernel_los_fruit'],
			'kernel_los_fiber_cyclone' => $input['kernel_los_fiber_cyclone'],
			'kernel_los_ltds1' => $input['kernel_los_ltds1'],
			'kernel_los_ltds2' => $input['kernel_los_ltds2'],
			'kernel_los_claybath' => $input['kernel_los_claybath'],

			'dibuat_oleh'=> $input['dibuat_oleh'],
			'dibuat_tanggal'=> date('Y-m-d H:i:s'),
		);
		$this->db->insert('pks_lab_pengolahan', $data);

		// $details_item = $input['details_item'];
		// foreach ($details_item as $key => $value) {
		// 	// $item = $this->InvItemModel->retrieve($value['item']['id']);
		// 	$this->db->insert("pks_pengolahan_item", array(
		// 		'pengolahan_id' => $id,
		// 		'issue_id' => 0,
		// 		'item_id' => $value['item']['id'],
		// 		'qty' => $value['qty'],
		// 		'no_issue' => $value['no_issue'],
		// 		'harga' => $value['harga'],
		// 	));
		// }


		return $id;
	}
	public function update(
		$id,
		$input
	) {
		$data = array(
			'mill_id' => $input['mill_id']['id'],
			'tanggal' => $input['tanggal'],
			'no_transaksi' => $input['no_transaksi'],
			'total_jam_proses' => $input['total_jam_proses'],
			'total_jumlah_rebusan' => $input['total_jumlah_rebusan'],
			'tbs_olah' => $input['tbs_olah'],
			'diubah_oleh'=> $input['diubah_oleh'],
			'diubah_tanggal'=> date('Y-m-d H:i:s'),
		);
		$this->db->where('id', $id);
		$this->db->update('pks_pengolahan_ht', $data);

		// hapus  detail
		$this->db->where('pengolahan_id', $id);
		$this->db->delete('pks_pengolahan_dt');

		$this->db->where('pengolahan_id', $id);
		$this->db->delete('pks_pengolahan_mesin');

		$this->db->where('pengolahan_id', $id);
		$this->db->delete('pks_lab_pengolahan');


		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("pks_pengolahan_dt", array(
				'pengolahan_id' => $id,
				'shift_id' => $value['shift_id']['id'],
				'jam_masuk' => $value['jam_masuk'],
				'jam_selesai' => $value['jam_selesai'],
				'mandor_id' => $value['mandor_id']['id'],
				'asisten_id' => $value['asisten_id']['id'],
				'jam_proses' => $value['jam_proses'],
				'jumlah_rebusan' => $value['jumlah_rebusan'],
			));
		}

		$details = $input['details_mesin'];
		foreach ($details as $key => $value) {
			$mesin = $this->GbmOrganisasiModel->retrieve($value['mesin_id']['id']);
			// $value['stasiun'] = $this->GbmOrganisasiModel->retrieve($mesin['parent_id']);
			$this->db->insert("pks_pengolahan_mesin", array(
				'pengolahan_id' => $id,
				'mesin_id' => $value['mesin_id']['id'],
				'jam_masuk' => $value['jam_masuk'],
				'jam_selesai' => $value['jam_selesai'],
				'jumlah_jam' => $value['jumlah_jam'],
				// 'station_id' => $value['stasiun']['id'],
				'keterangan' => $value['keterangan'],
			));
		}

		$data = array(
			'mill_id' => $input['mill_id']['id'],
			'no_transaksi' => $input['no_transaksi'],
			'tanggal' => $input['tanggal'],
			'pengolahan_id'=> $id,
	 
			'cpo_moisture' => $input['cpo_moisture'],
			'cpo_dobi' => $input['cpo_dobi'],
			'cpo_ffa' => $input['cpo_ffa'],
			'cpo_dirt' => $input['cpo_dirt'],
			
			'kernel_moisture' => $input['kernel_moisture'],
			'kernel_dobi' => $input['kernel_dobi'],
			'kernel_ffa' => $input['kernel_ffa'],
			'kernel_dirt' => $input['kernel_dirt'],
			
			'cpo_los_fruit' => $input['cpo_los_fruit'],
			'cpo_los_press' => $input['cpo_los_press'],
			'cpo_los_nut' => $input['cpo_los_nut'],
			'cpo_los_e_bunch' => $input['cpo_los_e_bunch'],
			'cpo_los_effluent' => $input['cpo_los_effluent'],
			
			'kernel_los_fruit' => $input['kernel_los_fruit'],
			'kernel_los_fiber_cyclone' => $input['kernel_los_fiber_cyclone'],
			'kernel_los_ltds1' => $input['kernel_los_ltds1'],
			'kernel_los_ltds2' => $input['kernel_los_ltds2'],
			'kernel_los_claybath' => $input['kernel_los_claybath'],

			'dibuat_oleh'=> $input['diubah_oleh'],
			'dibuat_tanggal'=> date('Y-m-d H:i:s'),
		);
		$this->db->insert('pks_lab_pengolahan', $data);
		
		// $details_item = $input['details_item'];
		// foreach ($details_item as $key => $value) {
		// 	// $item = $this->InvItemModel->retrieve($value['item']['id']);
		// 	$this->db->insert("pks_pengolahan_item", array(
		// 		'pengolahan_id' => $id,
		// 		'issue_id' => 0,
		// 		'item_id' => $value['item']['id'],
		// 		'qty' => $value['qty'],
		// 		'no_issue' => $value['no_issue'],
		// 		'harga' => $value['harga'],
		// 	));
		// }

		return $id;
	}


	public function delete($id)
	{
		$this->db->where('pengolahan_id', $id);
		$this->db->delete('pks_pengolahan_dt');
		$this->db->where('pengolahan_id', $id);
		$this->db->delete('pks_pengolahan_mesin');
		$this->db->where('pengolahan_id', $id);
		$this->db->delete('pks_pengolahan_item');
		$this->db->where('pengolahan_id', $id);
		$this->db->delete('pks_lab_pengolahan');
		$this->db->where('id', $id);
		$this->db->delete('pks_pengolahan_ht');
		return true;
	}

	public function retrieve_by_id($id)
	{
		$this->db->select('a.*, b.*, a.id AS id, a.tanggal as tanggal,a.no_transaksi as no_transaksi,a.mill_id as mill_id');
		$this->db->from('pks_pengolahan_ht a');
		$this->db->join('pks_lab_pengolahan b', 'a.id=b.pengolahan_id', 'left');
		$this->db->where('a.id', $id);
		$res = $this->db->get();
		return $res->row_array();
	}

	public function retrieve_detail($hdid)
	{
		$this->db->select('*');
		$this->db->from('pks_pengolahan_dt');
		// $this->db->join('pks_shift', 'pks_pengolahan_dt.shift_id = shift.id');
		$this->db->where('pengolahan_id', $hdid);
		$res = $this->db->get();
		return $res->result_array();
	}
	public function retrieve_detail_mesin($hdid)
	{
		$this->db->select('*');
		$this->db->from('pks_pengolahan_mesin');
		// $this->db->join('pks_shift', 'pks_pengolahan_dt.shift_id = shift.id');
		$this->db->where('pengolahan_id', $hdid);
		$res = $this->db->get();
		return $res->result_array();
	}
	public function retrieve_detail_item($hdid)
	{
		$this->db->select('*');
		$this->db->from('pks_pengolahan_item');
		// $this->db->join('pks_shift', 'pks_pengolahan_dt.shift_id = shift.id');
		$this->db->where('pengolahan_id', $hdid);
		$res = $this->db->get();
		return $res->result_array();
	}


	public function print_slip(
		$id = null
	) {
		$query    = "Select a.*,b.qty,c.kode,c.nama,c.satuan
		FROM pks_pengolahan_ht a inner join pks_pengolahan_dt b
	   on a.id=b.pengolahan_id
	   inner join inv_blok c on b.blok=c.id
	   inner join inv_gudang d on a.divisi_id=d.id
			where 1=1 and a.id=" . $id . ";";
		$data     = $this->db->query($query)->result_array();
		return $data;
	}
}
