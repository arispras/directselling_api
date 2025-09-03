<?php

class EstProduksiPanenModel extends CI_Model
{


	public function print_rekap_panen(
		$divisi_id = null,
		$blok_id = null,
		$tanggal_mulai = null,
		$tanggal_akhir = null

	) {
		$querySaldoAwal = "  SELECT a.divisi_id,b.blok,
        sum(case a.jenis when  '0' then b.qty else b.qty*-1 end) as stok FROM est_produksi_panen_ht a inner join est_produksi_panen_dt b
        on a.id=b.produksi_panen_id
        inner join inv_blok c on b.blok=c.id
        inner join inv_gudang d on a.divisi_id=d.id
        where b.blok=" . $blok_id . " and  a.divisi_id=" . $divisi_id . " and a.tanggal < '" . $tanggal_mulai . "'
         group by  a.divisi_id,b.blok ;";


		$saldoAwal    = $this->db->query($querySaldoAwal)->row_array();
		$data['saldo_awal'] = (!empty($saldoAwal)) ? $saldoAwal['stok'] : 0;
		//    print_r($querySaldoAwal);exit();

		$queryTransaksi   = "SELECT a.divisi_id,d.nama as gudang,a.tanggal,a.no_rekap_panen,b.keterangan, a.jenis,b.blok,c.kode, c.nama,c.satuan,
        b.qty FROM est_produksi_panen_ht a inner join est_produksi_panen_dt b
         on a.id=b.produksi_panen_id
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
	
	
	public function create(
		$input = null
	) {
		$data = array(
			'divisi_id' => $input['divisi_id']['id'],
			// 'rayon_id' => $input['rayon_id']['id'],
			//'jenis' => $input['jenis'],
			'tanggal' => $input['tanggal'],
			// 'no_rekap_panen' => $input['no_rekap_panen'],
			// 'total_jjg' => $input['total_jjg'],
			// 'total_brondolan' => $input['total_brondolan'],
			// 'total_kg_kebun' => $input['total_kg'],
			 'keterangan' => $input['keterangan'],
			 'dibuat_oleh'=> $input['dibuat_oleh'],
			'dibuat_tanggal'=> date('Y-m-d H:i:s'),
		);
		$this->db->insert('est_produksi_panen_ht', $data);
		$id = $this->db->insert_id();
		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("est_produksi_panen_dt", array(
				'produksi_panen_id' => $id,
				'blok_id' => $value['blok']['id'],
				'jum_ha' => $value['jum_ha'],
				'jum_hk' => $value['jum_hk'],
				'jum_jjg' => $value['jum_jjg'],
				'jum_kg' => $value['jum_kg'],
				'jum_jjg_kirim' => $value['jum_jjg_kirim'],

				'jjg_afkir' => $value['jjg_afkir'],
				'jjg_restan' => $value['jjg_restan'],
				'kg_afkir' => $value['kg_afkir'],
				'kg_restan' => $value['kg_restan'],

				'jum_kg_pks' => $value['jum_kg_pks']
				
				
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
			// 'rayon_id' => $input['rayon_id']['id'],
			//'jenis' => $input['jenis'],
			'tanggal' => $input['tanggal'],
			// 'no_rekap_panen' => $input['no_rekap_panen'],
			// 'total_jjg' => $input['total_jjg'],
			// 'total_brondolan' => $input['total_brondolan'],
			// 'total_kg_kebun' => $input['total_kg'],
			 'keterangan' => $input['keterangan'],
			 'diubah_oleh'=> $input['diubah_oleh'],
			'diubah_tanggal'=> date('Y-m-d H:i:s'),
		);
		$this->db->where('id', $id);
		$this->db->update('est_produksi_panen_ht', $data);

		// hapus  detail
		$this->db->where('produksi_panen_id', $id);
		$this->db->delete('est_produksi_panen_dt');


		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("est_produksi_panen_dt", array(
				'produksi_panen_id' => $id,
				'blok_id' => $value['blok']['id'],
				'jum_ha' => $value['jum_ha'],
				'jum_hk' => $value['jum_hk'],
				'jum_jjg' => $value['jum_jjg'],
				'jum_kg' => $value['jum_kg'],
				'jum_jjg_kirim' => $value['jum_jjg_kirim'],
				
				'jjg_afkir' => $value['jjg_afkir'],
				'jjg_restan' => $value['jjg_restan'],
				'kg_afkir' => $value['kg_afkir'],
				'kg_restan' => $value['kg_restan'],

				'jum_kg_pks' => $value['jum_kg_pks']
				
			));
		}

		return $id;
	}
	


	public function delete($id)
	{

		$this->db->where('produksi_panen_id', $id);
		$this->db->delete('est_produksi_panen_dt');
		$this->db->where('id', $id);
		$this->db->delete('est_produksi_panen_ht');
		return true;
	}

	public function retrieve_by_id($id)
	{
		$this->db->select('est_produksi_panen_ht.*');
		$this->db->from('est_produksi_panen_ht');
		$this->db->where('est_produksi_panen_ht.id', $id);
		$res = $this->db->get();
		return $res->row_array();
	}

	public function retrieve_detail($hdid)
	{
		$this->db->select('est_produksi_panen_dt.*,gbm_organisasi.kode as kode_blok,gbm_organisasi.nama as nama_blok');
		$this->db->from('est_produksi_panen_dt');
		$this->db->join('gbm_organisasi', 'est_produksi_panen_dt.blok_id = gbm_organisasi.id');
		$this->db->where('produksi_panen_id', $hdid);
		$res = $this->db->get();
		return $res->result_array();
	}


	public function print_slip(
		$id = null
	) {
		$query    = "Select a.*,b.qty,c.kode,c.nama,c.satuan
		FROM est_produksi_panen_ht a inner join est_produksi_panen_dt b
	   on a.id=b.produksi_panen_id
	   inner join inv_blok c on b.blok=c.id
	   inner join inv_gudang d on a.divisi_id=d.id
			where 1=1 and a.id=" . $id . ";";
		$data     = $this->db->query($query)->result_array();
		return $data;
	}
}
