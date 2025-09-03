<?php

class EstBkmPanenModel extends CI_Model
{



	public function create(
		$input = null
	) {
		$data = array(
			'lokasi_id' => $input['lokasi_id']['id'],
			'rayon_afdeling_id' => $input['afdeling_id']['id'],
			'tanggal' => $input['tanggal'],
			'no_transaksi' => $input['no_transaksi'],
			'mandor_id' =>  $input['mandor_id']['id'],
			'kerani_id' =>  $input['kerani_id']['id'],
			'asisten_id' =>  $input['asisten_id']['id'],
			'mandor_id' =>  $input['mandor_id']['id'],
			'hasil_kerja_mandor' =>  $input['mandor_hasil_kerja'],
			'jumlah_hk_mandor' =>  $input['mandor_jumlah_hk'],
			'rp_hk_mandor' =>  $input['mandor_rupiah_hk'],
			'ket_mandor' =>  $input['ket_mandor'],
			'ket_kerani' =>  $input['ket_kerani'],
			'premi_mandor' =>  $input['mandor_premi'],
			'denda_mandor' =>  $input['mandor_denda'],
			'hasil_kerja_kerani' =>  $input['kerani_hasil_kerja'],
			'jumlah_hk_kerani' =>  $input['kerani_jumlah_hk'],
			'rp_hk_kerani' =>  $input['kerani_rupiah_hk'],
			'premi_kerani' =>  $input['kerani_premi'],
			'premi_kerani' =>  $input['kerani_premi'],
			'denda_kerani' =>  $input['kerani_denda'],
			'dibuat_oleh' =>  $input['dibuat_oleh'],
			'dibuat_tanggal' =>  date("Y-m-d H:i:s"),
			'diubah_oleh' =>  $input['diubah_oleh'],
			'diubah_tanggal' =>  date("Y-m-d H:i:s"),
			'is_premi_kontanan' => $input['is_premi_kontanan']	,
			'is_asistensi' => $input['is_asistensi'],
			'is_asistensi_unit' => $input['is_asistensi_unit']		
			

		);
		$this->db->insert('est_bkm_panen_ht', $data);
		$id = $this->db->insert_id();
		$total_premi=0;
		$total_upah=0;
		$jum_karyawan=0;
		$details = $input['details'];
		foreach ($details as $key => $value) {
			$total_premi=	$total_premi+$value['premi_brondolan']+$value['premi_panen'];
			$total_upah=	$total_upah+$value['rp_hk'];
			$jum_karyawan++;
		 	$this->db->insert("est_bkm_panen_dt", array(
				'bkm_panen_id' => $id,
				'blok_id' => $value['blok_id']['id'],
				'karyawan_id' => $value['karyawan_id']['id'],
				'bjr' => $value['bjr'],
				'jumlah_hk' => $value['jumlah_hk'],
				'hasil_kerja_jjg' => $value['hasil_kerja_jjg'],
				'hasil_kerja_brondolan' => $value['hasil_kerja_brondolan'],
				'hasil_kerja_luas' => $value['hasil_kerja_luas'],
				'hasil_kerja_kg' => $value['hasil_kerja_kg'],
				'premi_brondolan' => $value['premi_brondolan'],
				'rp_hk' => $value['rp_hk'],
				'premi_basis' => $value['premi_basis'],
				'premi_lebih_basis' => $value['premi_lebih_basis'],
				'premi_panen' => $value['premi_panen'],
				'denda_panen' => $value['denda_panen'],
				'denda_basis' => $value['denda_basis'],
				'basis_jjg' => $value['basis_jjg'],
				'total_pendapatan' => $value['total_pendapatan'],	
				'potongan' => $value['potongan'],
				'keterangan_potongan' => $value['keterangan_potongan'],
				'ket' => $value['ket'],
					
			));
			$detail_id = $this->db->insert_id();
			$details_denda =$value['details_denda'];
			foreach ($details_denda as $key_denda => $value_denda) {
				$this->db->insert("est_bkm_panen_denda", array(
					'bkm_panen_dt_id' => $detail_id,
					'kode_denda_panen_id' => $value_denda['denda_id']['id'],
					'qty' => $value_denda['qty'],
					'nilai' => $value_denda['nilai'],
					'jumlah_nilai_denda' => $value_denda['qty']*$value_denda['nilai']
					));
			
			}
		}

		/* BAGIAN MULAI UTK HITUNG PREMI DAN UPAH MANDOR/KRANI */
		/*
		$q0 = "SELECT a.*,b.status_pajak FROM  payroll_karyawan_gaji a inner join karyawan b on a.karyawan_id=b.id
		where a.karyawan_id=".  $input['mandor_id']['id'] . "";
		$mandor = $this->db->query($q0)->row_array();
		$upah_mandor= $mandor['gapok']/25 ;
		$q0 = "SELECT a.*,b.status_pajak FROM  payroll_karyawan_gaji a inner join karyawan b on a.karyawan_id=b.id
		where a.karyawan_id=".  $input['kerani_id']['id'] . "";
		$kerani = $this->db->query($q0)->row_array();
		$upah_kerani= $kerani['gapok']/25 ;

		$persen_premi_mandor=1.5;
		$persen_premi_kerani=1.25;
		$jumlah_karyawan_mandor=10;
		$jumlah_karyawan_kerani=10;
		$premi_kerani=0;
		$premi_mandor=0;

		if ($jum_karyawan<	$jumlah_karyawan_mandor){
			$premi_mandor=($persen_premi_mandor/100 )*	($total_premi/$jumlah_karyawan_mandor);
		}else{
			$premi_mandor=$persen_premi_mandor/100 *	($total_premi/$jum_karyawan);
		}
		if ($jum_karyawan<	$jumlah_karyawan_kerani){
			$premi_kerani=($persen_premi_kerani/100 )*	($total_premi/$jumlah_karyawan_kerani);
		}else{
			$premi_kerani=$persen_premi_kerani/100 *	($total_premi/$jum_karyawan);
		}

		$data_mandor_kerani=array('rp_hk_mandor'=>$upah_mandor,'rp_hk_kerani'=>$upah_kerani,'premi_mandor'=>$premi_mandor,'premi_kerani'=>$premi_kerani);
		$this->db->where('id', $id);
		$this->db->update('est_bkm_panen_ht', $data_mandor_kerani);
		*/
		/* BAGIAN END UTK HITUNG PREMI DAN UPAH MANDOR/KRANI */

	
		return $id;
	}
	public function update(
		$id,
		$input
	) {
		$data = array(
			'lokasi_id' => $input['lokasi_id']['id'],
			'rayon_afdeling_id' => $input['afdeling_id']['id'],
			'tanggal' => $input['tanggal'],
			'no_transaksi' => $input['no_transaksi'],
			'mandor_id' =>  $input['mandor_id']['id'],
			'kerani_id' =>  $input['kerani_id']['id'],
			'asisten_id' =>  $input['asisten_id']['id'],
			'mandor_id' =>  $input['mandor_id']['id'],
			'hasil_kerja_mandor' =>  $input['mandor_hasil_kerja'],
			'jumlah_hk_mandor' =>  $input['mandor_jumlah_hk'],
			'rp_hk_mandor' =>  $input['mandor_rupiah_hk'],
			'premi_mandor' =>  $input['mandor_premi'],
			'denda_mandor' =>  $input['mandor_denda'],
			'ket_mandor' =>  $input['ket_mandor'],
			'ket_kerani' =>  $input['ket_kerani'],
			'hasil_kerja_kerani' =>  $input['kerani_hasil_kerja'],
			'jumlah_hk_kerani' =>  $input['kerani_jumlah_hk'],
			'rp_hk_kerani' =>  $input['kerani_rupiah_hk'],
			'premi_kerani' =>  $input['kerani_premi'],
			'denda_kerani' =>  $input['kerani_denda'],
			'diubah_oleh' =>  $input['diubah_oleh'],
			'diubah_tanggal' =>  date("Y-m-d H:i:s"),
			'is_premi_kontanan' => $input['is_premi_kontanan']	,
			'is_asistensi' => $input['is_asistensi'],
			'is_asistensi_unit' => $input['is_asistensi_unit']		
		);
		$this->db->where('id', $id);
		$this->db->update('est_bkm_panen_ht', $data);

		$dt=$this->retrieve_detail($id);
		foreach ($dt as $key => $value) {
			// hapus denda berdasarkan id dtl //
			$this->db->where('bkm_panen_dt_id', $value['id']);
			$this->db->delete('est_bkm_panen_denda');
		}

		// Hapus  detail //
		$this->db->where('bkm_panen_id', $id);
		$this->db->delete('est_bkm_panen_dt');
		$total_premi=0;
		$total_upah=0;
		$jum_karyawan=0;
		$details = $input['details'];
		foreach ($details as $key => $value) {
			$total_premi=	$total_premi+$value['premi_brondolan']+$value['premi_panen'];
			$total_upah=	$total_upah+$value['rp_hk'];
			$jum_karyawan++;
			$this->db->insert("est_bkm_panen_dt", array(
				'bkm_panen_id' => $id,
				'blok_id' => $value['blok_id']['id'],
				'karyawan_id' => $value['karyawan_id']['id'],
				'bjr' => $value['bjr'],
				'jumlah_hk' => $value['jumlah_hk'],
				'hasil_kerja_jjg' => $value['hasil_kerja_jjg'],
				'hasil_kerja_brondolan' => $value['hasil_kerja_brondolan'],
				'hasil_kerja_luas' => $value['hasil_kerja_luas'],
				'hasil_kerja_kg' => $value['hasil_kerja_kg'],
				'premi_brondolan' => $value['premi_brondolan'],
				'rp_hk' => $value['rp_hk'],
				'premi_basis' => $value['premi_basis'],
				'premi_lebih_basis' => $value['premi_lebih_basis'],
				'premi_panen' => $value['premi_panen'],
				'denda_panen' => $value['denda_panen'],
				'denda_basis' => $value['denda_basis'],
				'basis_jjg' => $value['basis_jjg'],
				'total_pendapatan' => $value['total_pendapatan'],
				'potongan' => $value['potongan'],
				'keterangan_potongan' => $value['keterangan_potongan'],
				'ket' => $value['ket']		
			));
			$detail_id = $this->db->insert_id();
			$details_denda =$value['details_denda'];
			foreach ($details_denda as $key_denda => $value_denda) {
				$this->db->insert("est_bkm_panen_denda", array(
					'bkm_panen_dt_id' => $detail_id,
					'kode_denda_panen_id' => $value_denda['denda_id']['id'],
					'qty' => $value_denda['qty'],
					'nilai' => $value_denda['nilai'],
					'jumlah_nilai_denda' => $value_denda['qty']*$value_denda['nilai']
					));
			
			}
		}
			/* BAGIAN MULAI UTK HITUNG PREMI DAN UPAH MANDOR/KRANI */
				/*
			$q0 = "SELECT a.*,b.status_pajak FROM  payroll_karyawan_gaji a inner join karyawan b on a.karyawan_id=b.id
			where a.karyawan_id=".  $input['mandor_id']['id'] . "";
			$mandor = $this->db->query($q0)->row_array();
			$upah_mandor= $mandor['gapok']/25 ;
			$q0 = "SELECT a.*,b.status_pajak FROM  payroll_karyawan_gaji a inner join karyawan b on a.karyawan_id=b.id
			where a.karyawan_id=".  $input['kerani_id']['id'] . "";
			$kerani = $this->db->query($q0)->row_array();
			$upah_kerani= $kerani['gapok']/25 ;
	
			$persen_premi_mandor=1.5;
			$persen_premi_kerani=1.25;
			$jumlah_karyawan_mandor=10;
			$jumlah_karyawan_kerani=10;
			$premi_kerani=0;
			$premi_mandor=0;
	
			if ($jum_karyawan<	$jumlah_karyawan_mandor){
				$premi_mandor=($persen_premi_mandor/100 )*	($total_premi/$jumlah_karyawan_mandor);
			}else{
				$premi_mandor=$persen_premi_mandor/100 *	($total_premi/$jum_karyawan);
			}
			if ($jum_karyawan<	$jumlah_karyawan_kerani){
				$premi_kerani=($persen_premi_kerani/100 )*	($total_premi/$jumlah_karyawan_kerani);
			}else{
				$premi_kerani=$persen_premi_kerani/100 *	($total_premi/$jum_karyawan);
			}
	
			$data_mandor_kerani=array('rp_hk_mandor'=>$upah_mandor,'rp_hk_kerani'=>$upah_kerani,'premi_mandor'=>$premi_mandor,'premi_kerani'=>$premi_kerani);
			$this->db->where('id', $id);
			$this->db->update('est_bkm_panen_ht', $data_mandor_kerani);
			 */
			/* BAGIAN END UTK HITUNG PREMI DAN UPAH MANDOR/KRANI */
	

		return $id;
	}


	public function delete($id)
	{
		$this->db->where('bkm_panen_id', $id);
		$this->db->delete('est_bkm_panen_dt');
		$this->db->where('id', $id);
		$this->db->delete('est_bkm_panen_ht');
		return true;
	}
	public function posting($id,	$input) 
	{
		$id = (int)$id;
		$data['is_posting'] = 1;
		$data['diposting_tanggal'] = date('Y-m-d H:i:s');
		$data['diposting_oleh'] = $input['diposting_oleh'];// $input['user_posting]
		$this->db->where('id', $id);
		$this->db->update('est_bkm_panen_ht', $data);
		

		return true;
	}
	public function retrieve_by_id($id)
	{
		$this->db->select('*');
		$this->db->from('est_bkm_panen_ht');
		$this->db->where('id', $id);
		$res = $this->db->get();
		return $res->row_array();
	}

	public function retrieve_detail($hdid)
	{
		$this->db->select('*');
		$this->db->from('est_bkm_panen_dt');
		$this->db->where('bkm_panen_id', $hdid);
		$res = $this->db->get();
		return $res->result_array();
	}

	public function retrieve_detail_denda($dtid)
	{
		$this->db->select('*');
		$this->db->from('est_bkm_panen_denda');
		$this->db->where('bkm_panen_dt_id', $dtid);
		$res = $this->db->get();
		return $res->result_array();
	}


	public function print_slip(
		$id = null
	) {
		$query    = "Select a.*,b.qty,c.kode,c.nama,c.satuan
		FROM est_bkm_panen_ht a inner join est_bkm_panen_dt b
	   on a.id=b.bkm_panen_id
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
			'rayon_afdeling_id' => $input['afdeling_id']['id'],
			'tanggal' => $input['tanggal'],
			'no_transaksi' => $input['no_transaksi'],
			'mandor_id' =>  $input['mandor_id']['id'],
			'kerani_id' =>  $input['kerani_id']['id'],
			'asisten_id' =>  $input['asisten_id']['id'],
			'mandor_id' =>  $input['mandor_id']['id'],

		);
		$this->db->insert('est_bkm_panen_ht', $data);
		$id = $this->db->insert_id();

		$details = $input['details'];
		foreach ($details as $key => $value) {
		 	$this->db->insert("est_bkm_panen_dt", array(
				'bkm_panen_id' => $id,
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
				$this->db->insert("est_bkm_panen_dt", array(
					'bkm_panen_id' => $id,
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
