<?php

class EstBkmUmumModel extends CI_Model
{



	public function create(
		$input = null
	) {
		$data = array(
			'lokasi_id' => $input['lokasi_id']['id'],
			'rayon_afdeling_id' => $input['rayon_afdeling_id']['id'],
			'tanggal' => $input['tanggal'],
			'keterangan' => $input['keterangan'],
			'no_transaksi' => $input['no_transaksi'],
			'no_ref' => $input['no_ref'],
			'dibuat_oleh' =>  $input['dibuat_oleh'],
			'dibuat_tanggal' =>  date("Y-m-d H:i:s"),
			'diubah_oleh' =>  $input['diubah_oleh'],
			'diubah_tanggal' =>  date("Y-m-d H:i:s"),
		);
		$this->db->insert('est_bkm_umum_ht', $data);
		$id = $this->db->insert_id();
		$total_premi=0;
		$total_upah=0;
		$jum_karyawan=0;
		$details = $input['details'];
		foreach ($details as $key => $value) {
			// $total_premi=	$total_premi+$value['premi_brondolan']+$value['premi_panen'];
			// $total_upah=	$total_upah+$value['rp_hk'];
			$jum_karyawan++;
		 	$this->db->insert("est_bkm_umum_dt", array(
				'bkm_umum_id' => $id,
				'karyawan_id' => $value['karyawan_id']['id'],
				'kegiatan_id' => $value['kegiatan_id']['id'],
				'kendaraan_id' => $value['kendaraan_id']['id'],
				'jenis_absensi_id' => $value['jenis_absensi_id']['id'],
				'jumlah_hk' => $value['jumlah_hk'],
				'rupiah_hk' => $value['rupiah_hk'],
				'premi' => $value['premi'],
				'ket' => $value['ket'],
				'blok_id' => $value['blok_id']['id'],
			));
			$detail_id = $this->db->insert_id();
		}
		return $id;
	}
	public function update(
		$id,
		$input
	) {
		$data = array(
			'lokasi_id' => $input['lokasi_id']['id'],
			'rayon_afdeling_id' => $input['rayon_afdeling_id']['id'],
			'tanggal' => $input['tanggal'],
			'no_transaksi' => $input['no_transaksi'],
			'keterangan' => $input['keterangan'],
			'no_ref' => $input['no_ref'],
			'dibuat_oleh' =>  $input['dibuat_oleh'],
			'dibuat_tanggal' =>  date("Y-m-d H:i:s"),
			'diubah_oleh' =>  $input['diubah_oleh'],
			'diubah_tanggal' =>  date("Y-m-d H:i:s"),
		);
		$this->db->where('id', $id);
		$this->db->update('est_bkm_umum_ht', $data);

		$dt=$this->retrieve_detail($id);
		// foreach ($dt as $key => $value) {
		// 	// hapus denda berdasarkan id dtl //
		// 	// $this->db->where('bkm_umum_dt_id', $value['id']);
		// 	// $this->db->delete('est_bkm_umum_denda');
		// }

		// Hapus  detail //
		$this->db->where('bkm_umum_id', $id);
		$this->db->delete('est_bkm_umum_dt');
		$total_premi=0;
		$total_upah=0;
		$jum_karyawan=0;
		$details = $input['details'];
		foreach ($details as $key => $value) {
			// $total_premi=	$total_premi+$value['premi_brondolan']+$value['premi_panen'];
			// $total_upah=	$total_upah+$value['rp_hk'];
			$jum_karyawan++;
			$this->db->insert("est_bkm_umum_dt", array(
				'bkm_umum_id' => $id,
				'karyawan_id' => $value['karyawan_id']['id'],
				'kegiatan_id' => $value['kegiatan_id']['id'],
				'kendaraan_id' => $value['kendaraan_id']['id'],
				'jenis_absensi_id' => $value['jenis_absensi_id']['id'],
				'jumlah_hk' => $value['jumlah_hk'],
				'rupiah_hk' => $value['rupiah_hk'],
				'premi' => $value['premi'],
				'ket' => $value['ket'],
				'blok_id' => $value['blok_id']['id'],
			));
			$detail_id = $this->db->insert_id();
		}
		return $id;
	}


	public function delete($id)
	{
		$this->db->where('bkm_umum_id', $id);
		$this->db->delete('est_bkm_umum_dt');
		$this->db->where('id', $id);
		$this->db->delete('est_bkm_umum_ht');
		return true;
	}
	public function posting($id,	$input) 
	{
		$id = (int)$id;
		$data['is_posting'] = 1;
		$data['diposting_tanggal'] = date('Y-m-d H:i:s');
		$data['diposting_oleh'] = $input['diposting_oleh'];// $input['user_posting]
		$this->db->where('id', $id);
		$this->db->update('est_bkm_umum_ht', $data);
		

		return true;
	}
	public function retrieve_by_id($id)
	{
		$this->db->select('*');
		$this->db->from('est_bkm_umum_ht');
		$this->db->where('id', $id);
		$res = $this->db->get();
		return $res->row_array();
	}

	public function retrieve_detail($hdid)
	{
		$this->db->select('*');
		$this->db->from('est_bkm_umum_dt');
		$this->db->where('bkm_umum_id', $hdid);
		$res = $this->db->get();
		return $res->result_array();
	}

	public function retrieve_detail_denda($dtid)
	{
		$this->db->select('*');
		$this->db->from('est_bkm_umum_denda');
		$this->db->where('bkm_umum_dt_id', $dtid);
		$res = $this->db->get();
		return $res->result_array();
	}


	public function print_slip(
		$id = null
	) {
		$query    = "Select a.*,b.qty,c.kode,c.nama,c.satuan
		FROM est_bkm_umum_ht a inner join est_bkm_umum_dt b
	   on a.id=b.bkm_umum_id
	   inner join inv_blok c on b.blok=c.id
	   inner join inv_gudang d on a.divisi_id=d.id
			where 1=1 and a.id=" . $id . ";";
		$data     = $this->db->query($query)->result_array();
		return $data;
	}
	public function createOld(
		$input = null
	) {
		$data = array(
			'lokasi_id' => $input['lokasi_id']['id'],
			'rayon_afdeling_id' => $input['rayon_afdeling_id']['id'],
			'tanggal' => $input['tanggal'],
			'no_transaksi' => $input['no_transaksi'],
			'mandor_id' =>  $input['mandor_id']['id'],
			'kerani_id' =>  $input['kerani_id']['id'],
			'asisten_id' =>  $input['asisten_id']['id'],
			'mandor_id' =>  $input['mandor_id']['id'],

		);
		$this->db->insert('est_bkm_umum_ht', $data);
		$id = $this->db->insert_id();

		$details = $input['details'];
		foreach ($details as $key => $value) {
		 	$this->db->insert("est_bkm_umum_dt", array(
				'bkm_umum_id' => $id,
				'blok_id' => $value['blok_id']['id'],
				'karyawan_id' => $value['karyawan_id']['id'],
				'hasil_kerja_jjg' => $value['hasil_kerja_jjg'],
				'hasil_kerja_brondolan' => $value['hasil_kerja_brondolan'],
				'hasil_kerja_luas' => $value['hasil_kerja_luas'],
				// 'jumlah_hk' => $value['jumlah_hk'],
				// 'rupiah_hk' => $value['rupiah_hk'],
				// 'basis_jjg' => $value['basis_jjg'],
				// 'premi_basis' => $value['premi_basis'],
				// 'lebih_basis1' => $value['lebih_basis1'],
				// 'premi_lebih_basis1' => $value['premi_lebih_basis1'],
				// 'lebih_basis2' => $value['lebih_basis2'],
				// 'premi_lebih_basis2' => $value['premi_lebih_basis2'],
				// 'total_premi' => $value['total_premi'],
				// 'total_potongan' => $value['total_potongan'],
				// 'total_pendapatan' => $value['total_pendapatan'],
				// 'd_buah_mentah' => $value['d_buah_mentah'],
				// 'd_buah_mentah_rp' => $value['d_buah_mentah_rp'],
				// 'd_tangkai_panjang' => $value['d_tangkai_panjang'],
				// 'd_tangkai_panjang_rp' => $value['d_tangkai_panjang_rp'],
				// 'd_matang_tidak_dipanen' => $value['d_matang_tidak_dipanen'],
				// 'd_matang_tidak_dipanen_rp' => $value['d_matang_tidak_dipanen_rp'],
				// 'd_matang_tinggal' => $value['d_matang_tinggal'],
				// 'd_matang_tinggal_rp' => $value['d_matang_tinggal_rp'],
				// 'd_brondolan_tidak_dikutip' => $value['d_brondolan_tidak_dikutip'],
				// 'd_brondolan_tidak_dikutip_rp' => $value['d_brondolan_tidak_dikutip_rp'],
				// 'd_pelepah_tidak_disusun' => $value['d_pelepah_tidak_disusun'],
				// 'd_pelepah_tidak_disusun_rp' => $value['d_pelepah_tidak_disusun_rp'],
				// 'd_pelepah_sengkleh' => $value['d_pelepah_sengkleh'],
				// 'd_pelepah_sengkleh_rp' => $value['d_pelepah_sengkleh_rp'],
				// 'd_mentah_disembunyikan' => $value['d_mentah_disembunyikan'],
				// 'd_mentah_disembunyikan_rp' => $value['d_mentah_disembunyikan_rp'],
				// 'd_matahari' => $value['d_matahari'],
				// 'd_matahari_rp' => $value['d_matahari_rp'],
				// 'd_buah_tidak_rapi' => $value['d_buah_tidak_rapi'],
				// 'd_buah_tidak_rapi_rp' => $value['d_buah_tidak_rapi_rp']

			));
			$id = $this->db->insert_id();
			$details_denda = $input['details_denda'];
			foreach ($details_denda as $key => $value) {
				$this->db->insert("est_bkm_umum_dt", array(
					'bkm_umum_id' => $id,
					'blok_id' => $value['blok_id']['id'],
					'karyawan_id' => $value['karyawan_id']['id'],
					'hasil_kerja_jjg' => $value['hasil_kerja_jjg'],
					'hasil_kerja_brondolan' => $value['hasil_kerja_brondolan'],
					'hasil_kerja_luas' => $value['hasil_kerja_luas'],

				));
			
			}
		}


		return $id;
	}
}
