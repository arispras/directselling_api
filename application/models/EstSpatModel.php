<?php

class EstSpatModel extends CI_Model
{


	// public function print_spat(
	// 	$divisi_id = null,
	// 	$blok_id = null,
	// 	$tanggal_mulai = null,
	// 	$tanggal_akhir = null

	// ) {
	// 	$querySaldoAwal = "  SELECT a.divisi_id,b.blok,
    //     sum(case a.jenis when  '0' then b.qty else b.qty*-1 end) as stok FROM est_spat_ht a inner join est_spat_dt b
    //     on a.id=b.spat_id
    //     inner join inv_blok c on b.blok=c.id
    //     inner join inv_gudang d on a.divisi_id=d.id
    //     where b.blok=" . $blok_id . " and  a.divisi_id=" . $divisi_id . " and a.tanggal < '" . $tanggal_mulai . "'
    //      group by  a.divisi_id,b.blok ;";


	// 	$saldoAwal    = $this->db->query($querySaldoAwal)->row_array();
	// 	$data['saldo_awal'] = (!empty($saldoAwal)) ? $saldoAwal['stok'] : 0;
	// 	//    print_r($querySaldoAwal);exit();

	// 	$queryTransaksi   = "SELECT a.divisi_id,d.nama as gudang,a.tanggal,a.no_spat,b.keterangan, a.jenis,b.blok,c.kode, c.nama,c.satuan,
    //     b.qty FROM est_spat_ht a inner join est_spat_dt b
    //      on a.id=b.spat_id
    //      inner join inv_blok c on b.blok=c.id
    //      inner join inv_gudang d on a.divisi_id=d.id
    //      where b.blok=" . $blok_id . " and  a.divisi_id=" . $divisi_id . "
    //       and a.tanggal >= '" . $tanggal_mulai . "'   and a.tanggal <= '" . $tanggal_akhir . "'
    //       order by a.tanggal,a.id  ;
    //       ";


	// 	$data['transaksi']     = $this->db->query($queryTransaksi)->result_array();
	// 	// print_r($data);exit();
	// 	return $data;
	// }
	// public function print_rekap_stok(
	// 	$divisi_id = null,
	// 	$kategori_id = null,
	// 	$tanggal_mulai = null,
	// 	$tanggal_akhir = null

	// ) {
	// 	$res = array();
	// 	$queryblok = "select a.*, b.nama as nama_kategori
    //    from inv_blok a inner join inv_kategori b on a.inv_kategori_id=b.id
    //    where 1=1 ";
	// 	if (!empty($kategori_id)) {
	// 		if (($kategori_id != 'all')) {
	// 			$queryblok = $queryblok . " and b.id=" . $kategori_id . "";
	// 		}
	// 	}

	// 	$bloks   = $this->db->query($queryblok)->result_array();
	// 	foreach ($bloks as $key => $blok) {
	// 		$querySaldoAwal = "SELECT a.divisi_id,d.nama as gudang,b.blok,c.kode, c.nama,c.satuan,
    //        sum(case a.jenis when  '0' then b.qty else b.qty*-1 end) as stok FROM est_spat_ht a inner join est_spat_dt b
    //        on a.id=b.spat_id
    //        inner join inv_blok c on b.blok=c.id
    //        inner join inv_gudang d on a.divisi_id=d.id
    //        where b.blok=" . $blok['id'] . " and  a.tanggal < '" . $tanggal_mulai . "'
    //        and a.divisi_id=" . $divisi_id . "
    //         group by  a.divisi_id,b.blok";

	// 		$awal   = $this->db->query($querySaldoAwal)->row_array();
	// 		$blok['saldo_awal'] = (!empty($awal)) ? $awal['stok'] : 0;
	// 		// print_r($querySaldoAwal);

	// 		$queryMasuk = "SELECT a.divisi_id,b.blok,
    //         sum(b.qty) as stok FROM est_spat_ht a inner join est_spat_dt b
    //         on a.id=b.spat_id
    //         inner join inv_blok c on b.blok=c.id
    //         inner join inv_gudang d on a.divisi_id=d.id
    //         where b.blok=" . $blok['id'] . " and  a.tanggal >= '" . $tanggal_mulai . "'
    //         and  a.tanggal <= '" . $tanggal_akhir . "' and  a.jenis=0
    //         and a.divisi_id=" . $divisi_id . "
    //          group by  a.divisi_id,b.blok";

	// 		$masuk   = $this->db->query($queryMasuk)->row_array();
	// 		$blok['masuk'] = (!empty($masuk['stok'])) ? $masuk['stok'] : 0;
	// 		//  print_r($queryMasuk);
	// 		$queryKeluar = "SELECT a.divisi_id,b.blok,
    //          sum( b.qty ) as stok FROM est_spat_ht a inner join est_spat_dt b
    //          on a.id=b.spat_id
    //          inner join inv_blok c on b.blok=c.id
    //          inner join inv_gudang d on a.divisi_id=d.id
    //          where b.blok=" . $blok['id'] . " and  a.tanggal >= '" . $tanggal_mulai . "'
    //         and  a.tanggal <= '" . $tanggal_akhir . "'  and  a.jenis=1
    //         and a.divisi_id=" . $divisi_id . "
    //           group by  a.divisi_id,b.blok";
	// 		$keluar   = $this->db->query($queryKeluar)->row_array();
	// 		$blok['keluar'] = (!empty($keluar['stok'])) ? $keluar['stok'] : 0;
	// 		//   print_r($queryKeluar);
	// 		$res[] = $blok;
	// 	}

	// 	$data['bloks']     =   $res;
	// 	//  print_r($blok);exit();
	// 	return $data;
	// }

	
	public function create(
		$input = null
	) {
		$data = array(
			'divisi_id' => $input['divisi_id']['id'],
			'rayon_id' => $input['rayon_id']['id'],
			//'jenis' => $input['jenis'],
			'tanggal' => $input['tanggal'],
			'no_spat' => $input['no_spat'],
			'total_jjg' => $input['total_jjg'],
			'total_brondolan' => $input['total_brondolan'],
			'total_kg_kebun' => $input['total_kg'],
			'keterangan' => $input['keterangan'],
			'keterangan' => $input['keterangan'],
			'is_double_handling' => $input['is_double_handling'],
			'dibuat_oleh'=> $input['dibuat_oleh'],
			'dibuat_tanggal'=> date('Y-m-d H:i:s'),
		);
		$this->db->insert('est_spat_ht', $data);
		$id = $this->db->insert_id();
		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("est_spat_dt", array(
				'spat_id' => $id,
				'blok_id' => $value['blok']['id'],
				'jum_janjang' => $value['jumlah_janjang'],
				'jum_brondolan' => $value['jumlah_brondolan'],
				'jum_mentah' => 0,
				'jum_matang' => 0,
				'jum_lwt_matang' => 0,
				'jum_busuk' => 0,
				'bjr_kebun' => $value['bjr'],
				'kg_kebun' => $value['jumlah_kg'],
				'bjr_pabrik' => 0
			));
		}


		return $id;
	}
	public function update(
		$id,
		$input
	) {
		$data = array(
			'divisi_id' => $input['divisi_id']['id'],
			'rayon_id' => $input['rayon_id']['id'],
			//'jenis' => $input['jenis'],
			'tanggal' => $input['tanggal'],
			'no_spat' => $input['no_spat'],
			'total_jjg' => $input['total_jjg'],
			'total_brondolan' => $input['total_brondolan'],
			'total_kg_kebun' => $input['total_kg'],
			'keterangan' => $input['keterangan'],
			'is_double_handling' => $input['is_double_handling'],

			'diubah_oleh'=> $input['diubah_oleh'],
			'diubah_tanggal'=> date('Y-m-d H:i:s'),
		);
		$this->db->where('id', $id);
		$this->db->update('est_spat_ht', $data);

		// hapus  detail
		$this->db->where('spat_id', $id);
		$this->db->delete('est_spat_dt');


		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("est_spat_dt", array(
				'spat_id' => $id,
				'blok_id' => $value['blok']['id'],
				'jum_janjang' => $value['jumlah_janjang'],
				'jum_brondolan' => $value['jumlah_brondolan'],
				'jum_mentah' => 0,
				'jum_matang' => 0,
				'jum_lwt_matang' => 0,
				'jum_busuk' => 0,
				'bjr_kebun' => $value['bjr'],
				'kg_kebun' => $value['jumlah_kg'],
				'bjr_pabrik' => 0
			));
		}

		return $id;
	}

	public function clearValidasiTimbangan(
		$id,
		$input
	) {
		$data = array(

			'pks_timbangan_id' =>null,
			'total_kg_pabrik'=>0,
			'bjr_pabrik'=>0,
			'diubah_oleh'=> $input['diubah_oleh'],
			'diubah_tanggal'=> date('Y-m-d H:i:s'),
		);
		$this->db->where('id', $id);
		$this->db->update('est_spat_ht', $data);

		//   detail
		$data = array(
			'bjr_pabrik' => 0,
			'kg_pabrik' => 0
		);
		$this->db->where('spat_id', $id);
		$this->db->update('est_spat_dt', $data);


		return $id;
	}
	public function validasiTimbangan(
		$id,
		$input
	) {
		$data = array(
			'pks_timbangan_id' => $input['pks_timbangan_id'],
			'total_kg_pabrik' => $input['berat_bersih'],
			'bjr_pabrik' => $input['berat_bersih']/	$input['total_jjg']				
		);
		$this->db->where('id', $id);
		$this->db->update('est_spat_ht', $data);

		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->where('id', 	$value['id_detail'] );
			$this->db->update("est_spat_dt", array(
				'bjr_pabrik' => $value['bjr_pabrik'],
				'kg_pabrik' => $value['kg_pabrik'],
				
			));
			
		}

		return $id;
	}


	public function delete($id)
	{

		$this->db->where('spat_id', $id);
		$this->db->delete('est_spat_dt');
		$this->db->where('id', $id);
		$this->db->delete('est_spat_ht');
		return true;
	}

	public function retrieve_by_id($id)
	{
		$this->db->select('est_spat_ht.*,pks_timbangan.berat_bersih,pks_timbangan.no_tiket,gbm_organisasi.nama as nama_afdeling');
		$this->db->from('est_spat_ht');
		$this->db->join('pks_timbangan', 'est_spat_ht.pks_timbangan_id = pks_timbangan.id', 'left');
		$this->db->join('gbm_organisasi', 'est_spat_ht.rayon_id = gbm_organisasi.id', 'left');
		$this->db->where('est_spat_ht.id', $id);
		$res = $this->db->get();
		return $res->row_array();
	}

	public function retrieve_detail($hdid)
	{
		$this->db->select('est_spat_dt.*,gbm_organisasi.kode as kode_blok,gbm_organisasi.nama as nama_blok');
		$this->db->from('est_spat_dt');
		$this->db->join('gbm_organisasi', 'est_spat_dt.blok_id = gbm_organisasi.id');
		$this->db->where('spat_id', $hdid);
		$res = $this->db->get();
		return $res->result_array();
	}


	public function print_slip(
		$id = null
	) {
		$query    = "Select a.*,b.qty,c.kode,c.nama,c.satuan
		FROM est_spat_ht a inner join est_spat_dt b
	   on a.id=b.spat_id
	   inner join inv_blok c on b.blok=c.id
	   inner join inv_gudang d on a.divisi_id=d.id
			where 1=1 and a.id=" . $id . ";";
		$data     = $this->db->query($query)->result_array();
		return $data;
	}
}
