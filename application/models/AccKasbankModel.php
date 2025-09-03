<?php

class AccKasbankModel extends CI_Model
{

	public function create(
		$input = null
	) {
		$data = array(
			'lokasi_id' => $input['lokasi_id']['id'],
			'permintaan_id' => $input['permintaan_id']['id'],
			'akun_kasbank_id' => $input['akun_kasbank_id']['id'],
			'no_transaksi' => $input['no_transaksi'],
			'no_referensi' => $input['no_referensi'],
			'ref_id' => $input['ref_id'],
			'tanggal' => $input['tanggal'],
			'keterangan' => $input['keterangan'],
			'tipe_jurnal' => $input['tipe_jurnal']['id'],
			'tipe_bayar' => $input['tipe_bayar']['id'],
			'sumber_dokumen' => $input['sumber_dokumen']['id'],
			'nilai' => $input['nilai'],
			'dibuat_tanggal' => date('Y-m-d H:i:s'),
			'dibuat_oleh' =>  $input['dibuat_oleh'],
			// 'diubah_tanggal' => date('Y-m-d H:i:s'),
			// 'diubah_oleh' =>  $input['diubah_oleh']
		);
		$this->db->insert('acc_kasbank_ht', $data);
		$id = $this->db->insert_id();

		$details = $input['details'];

		foreach ($details as $key => $value) {
			$this->db->insert("acc_kasbank_dt", array(
				'jurnal_id' => $id,
				'lokasi_id' => $value['lokasi_id']['id'],
				'acc_akun_id' => $value['acc_akun_id']['id'],
				'debet' => $value['debet'],
				'kredit' => $value['kredit'],
				'ket' => $value['ket'],
				'blok_stasiun_id' => $value['blok_id']['id'],
				'kegiatan_id' => $value['kegiatan_id']['id'],
				'kendaraan_mesin_id' => $value['traksi_id']['id'],
				'invoice_id' => $value['invoice_id']

			));

			if ($input['sumber_dokumen']['id'] == 'INVOICE_AP') {
				if ($value['invoice_id']) {
					$this->db->query("update acc_ap_invoice_ht set nilai_dibayar=nilai_dibayar+" . $value['debet'] . "
						where id=" .  $value['invoice_id']);
				}
			}
			if ($input['sumber_dokumen']['id'] == 'BAPP_SEWA_KENDARAAN') {
				if ($value['invoice_id']) {
					$this->db->query("update est_bapp_spk_kendaraan_ht set nilai_dibayar=nilai_dibayar+" . $value['debet'] . "
						where id=" .  $value['invoice_id']);
				}
			}
			if ($input['sumber_dokumen']['id'] == 'BAPP_SPK_KEBUN') {
				if ($value['invoice_id']) {
					$this->db->query("update est_spk_ba_ht set nilai_dibayar=nilai_dibayar+" . $value['debet'] . "
						where id=" .  $value['invoice_id']);
				}
			}
			if ($input['sumber_dokumen']['id'] == 'INVOICE_TBS') {
				if ($value['invoice_id']) {
					$this->db->query("update acc_tbs_invoice_ht set nilai_dibayar=nilai_dibayar+" . $value['debet'] . "
						where id=" .  $value['invoice_id']);
				}
			}
			if ($input['sumber_dokumen']['id'] == 'INVOICE_ANGKUT_CPO') {
				if ($value['invoice_id']) {
					$this->db->query("update acc_angkut_invoice_ht set nilai_dibayar=nilai_dibayar+" . $value['debet'] . "
						where id=" .  $value['invoice_id']);
				}
			}
			if ($input['sumber_dokumen']['id'] == 'INVOICE_AR') {
				if ($value['invoice_id']) {
					$this->db->query("update acc_sales_invoice set nilai_dibayar=nilai_dibayar+" . $value['kredit'] . "
					where id=" . $value['invoice_id']);
				}
			}
		}

		return $id;
	}
	public function create_header(
		$input = []
	) {
		$data = array(
			'lokasi_id' => $input['lokasi_id'],
			'no_transaksi' => $this->getNomorJurnal(null, null, null),
			'tanggal' => $input['tanggal'],
			'no_ref' => $input['no_ref'],
			'ref_id' => $input['ref_id'],
			'tipe_jurnal' => $input['tipe_jurnal'],
			'modul' => $input['modul'],
			'keterangan' => $input['keterangan']
		);
		$this->db->insert('acc_kasbank_ht', $data);
		$id = $this->db->insert_id();
		return $id;
	}
	public function create_detail(
		$jurnal_id,
		$input = null

	) {

		$value = $input;
		$this->db->insert("acc_kasbank_dt", array(
			'jurnal_id' => $jurnal_id,
			'acc_akun_id' => $value['acc_akun_id'],
			'debet' => $value['debet'],
			'kredit' => $value['kredit'],
			'ket' => $value['ket'],
			'no_referensi' => $value['no_referensi'],
			'referensi_id' => $value['id'],
			'blok_stasiun_id' => $value['blok_stasiun_id'],
			'kegiatan_id' => $value['kegiatan_id'],
			'kendaraan_mesin_id' => $value['kendaraan_mesin_id']
		));
		$id = $this->db->insert_id();
		return $id;
	}
	public function delete_by_ref_id_and_modul($ref_id, $modul)
	{


		$this->db->select('*');
		$this->db->from('acc_kasbank_ht');
		$this->db->where('ref_id', $ref_id);
		$this->db->where('modul', $modul);
		$jurnal = $this->db->get()->row_array();

		$this->db->where('jurnal_id', $jurnal['id']);
		$this->db->delete('acc_kasbank_dt');

		$this->db->where('id', $jurnal['id']);
		$this->db->delete('acc_kasbank_ht');
	}
	public function update(
		$id,
		$input
	) {
		// balikin dulu nilai dibayar invoice
		if ($input['sumber_dokumen']['id'] == 'INVOICE_AP') {
			$last_data = $this->retrieve_detail($id);
			foreach ($last_data as $key => $value) {
				if ($value['invoice_id']) {
					$this->db->query("update acc_ap_invoice_ht set nilai_dibayar=nilai_dibayar-" . $value['debet'] . "
					where id=" . $value['invoice_id']);
				}
			}
		}
		if ($input['sumber_dokumen']['id'] == 'BAPP_SEWA_KENDARAAN') {
			$last_data = $this->retrieve_detail($id);
			foreach ($last_data as $key => $value) {
				if ($value['invoice_id']) {
					$this->db->query("update est_bapp_spk_kendaraan_ht set nilai_dibayar=nilai_dibayar-" . $value['debet'] . "
					where id=" . $value['invoice_id']);
				}
			}
		}
		if ($input['sumber_dokumen']['id'] == 'BAPP_SPK_KEBUN') {
			$last_data = $this->retrieve_detail($id);
			foreach ($last_data as $key => $value) {
				if ($value['invoice_id']) {
					$this->db->query("update est_spk_ba_ht set nilai_dibayar=nilai_dibayar-" . $value['debet'] . "
					where id=" . $value['invoice_id']);
				}
			}
		}
		if ($input['sumber_dokumen']['id'] == 'INVOICE_TBS') {
			$last_data = $this->retrieve_detail($id);
			foreach ($last_data as $key => $value) {
				if ($value['invoice_id']) {
					$this->db->query("update acc_tbs_invoice_ht set nilai_dibayar=nilai_dibayar-" . $value['debet'] . "
					where id=" . $value['invoice_id']);
				}
			}
		}
		if ($input['sumber_dokumen']['id'] == 'INVOICE_ANGKUT_CPO') {
			$last_data = $this->retrieve_detail($id);
			foreach ($last_data as $key => $value) {
				if ($value['invoice_id']) {
					$this->db->query("update acc_angkut_invoice_ht set nilai_dibayar=nilai_dibayar-" . $value['debet'] . "
					where id=" . $value['invoice_id']);
				}
			}
		}
		if ($input['sumber_dokumen']['id'] == 'INVOICE_AR') {
			$last_data = $this->retrieve_detail($id);
			foreach ($last_data as $key => $value) {
				if ($value['invoice_id']) {
					$this->db->query("update acc_sales_invoice set nilai_dibayar=nilai_dibayar-" . $value['kredit'] . "
					where id=" . $value['invoice_id']);
				}
			}
		}
		
		$data = array(
			'lokasi_id' => $input['lokasi_id']['id'],
			'permintaan_id' => $input['permintaan_id']['id'],
			'akun_kasbank_id' => $input['akun_kasbank_id']['id'],
			'no_transaksi' => $input['no_transaksi'],
			'no_referensi' => $input['no_referensi'],
			'ref_id' => $input['ref_id'],
			'tanggal' => $input['tanggal'],
			'keterangan' => $input['keterangan'],
			'tipe_jurnal' => $input['tipe_jurnal']['id'],
			'tipe_bayar' => $input['tipe_bayar']['id'],
			'sumber_dokumen' => $input['sumber_dokumen']['id'],
			'nilai' => $input['nilai'],
			// 'dibuat_tanggal' => date('Y-m-d H:i:s'),
			// 'dibuat_oleh' =>  $input['dibuat_oleh'],
			'diubah_tanggal' => date('Y-m-d H:i:s'),
			'diubah_oleh' =>  $input['diubah_oleh']

		);
		$this->db->where('id', $id);
		$this->db->update('acc_kasbank_ht', $data);

		// Hapus  detail //
		$this->db->where('jurnal_id', $id);
		$this->db->delete('acc_kasbank_dt');

		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("acc_kasbank_dt", array(
				'jurnal_id' => $id,
				'lokasi_id' => $value['lokasi_id']['id'],
				'acc_akun_id' => $value['acc_akun_id']['id'],
				'debet' => $value['debet'],
				'kredit' => $value['kredit'],
				'ket' => $value['ket'],
				'blok_stasiun_id' => $value['blok_id']['id'],
				'kegiatan_id' => $value['kegiatan_id']['id'],
				'kendaraan_mesin_id' => $value['traksi_id']['id'],
				'invoice_id' => $value['invoice_id']

			));
			$detail_id = $this->db->insert_id();


			if ($input['sumber_dokumen']['id'] == 'INVOICE_AP') {
				if ($value['invoice_id']) {
					$this->db->query("update acc_ap_invoice_ht set nilai_dibayar=nilai_dibayar+" . $value['debet'] . "
					where id=" .  $value['invoice_id']);
				}
			}
			if ($input['sumber_dokumen']['id'] == 'BAPP_SEWA_KENDARAAN') {
				if ($value['invoice_id']) {
					$this->db->query("update est_bapp_spk_kendaraan_ht set nilai_dibayar=nilai_dibayar+" . $value['debet'] . "
						where id=" .  $value['invoice_id']);
				}
			}
			if ($input['sumber_dokumen']['id'] == 'BAPP_SPK_KEBUN') {
				if ($value['invoice_id']) {
					$this->db->query("update est_spk_ba_ht set nilai_dibayar=nilai_dibayar+" . $value['debet'] . "
						where id=" .  $value['invoice_id']);
				}
			}
			if ($input['sumber_dokumen']['id'] == 'INVOICE_TBS') {
				if ($value['invoice_id']) {
					$this->db->query("update acc_tbs_invoice_ht set nilai_dibayar=nilai_dibayar+" . $value['debet'] . "
						where id=" .  $value['invoice_id']);
				}
			}
			if ($input['sumber_dokumen']['id'] == 'INVOICE_ANGKUT_CPO') {
				if ($value['invoice_id']) {
					$this->db->query("update acc_angkut_invoice_ht set nilai_dibayar=nilai_dibayar+" . $value['debet'] . "
						where id=" .  $value['invoice_id']);
				}
			}
			if ($input['sumber_dokumen']['id'] == 'INVOICE_AR') {
				if ($value['invoice_id']) {
					$this->db->query("update acc_sales_invoice set nilai_dibayar=nilai_dibayar+" . $value['kredit'] . "
					where id=" . $value['invoice_id']);
				}
			}
		}

		return $id;
	}


	public function delete($id)
	{
		$last_data = $this->retrieve_by_id($id);
		if ($last_data['sumber_dokumen'] == 'INVOICE_AP') {
			$last_data = $this->retrieve_detail($id);
			foreach ($last_data as $key => $value) {
				if ($value['invoice_id']) {
					$this->db->query("update acc_ap_invoice_ht set nilai_dibayar=nilai_dibayar-" . $value['debet'] . "
					where id=" . $value['invoice_id']);
				}
			}
		}
		if ($last_data['sumber_dokumen'] == 'INVOICE_TBS') {
			$last_data = $this->retrieve_detail($id);
			foreach ($last_data as $key => $value) {
				if ($value['invoice_id']) {
					$this->db->query("update acc_tbs_invoice_ht set nilai_dibayar=nilai_dibayar-" . $value['debet'] . "
					where id=" . $value['invoice_id']);
				}
			}
		}
		if ($last_data['sumber_dokumen'] == 'INVOICE_ANGKUT_CPO') {
			$last_data = $this->retrieve_detail($id);
			foreach ($last_data as $key => $value) {
				if ($value['invoice_id']) {
					$this->db->query("update acc_angkut_invoice_ht set nilai_dibayar=nilai_dibayar-" . $value['debet'] . "
					where id=" . $value['invoice_id']);
				}
			}
		}
		if ($last_data['sumber_dokumen'] == 'BAPP_SEWA_KENDARAAN') {
			$last_data = $this->retrieve_detail($id);
			foreach ($last_data as $key => $value) {
				if ($value['invoice_id']) {
					$this->db->query("update est_bapp_spk_kendaraan_ht set nilai_dibayar=nilai_dibayar-" . $value['debet'] . "
					where id=" . $value['invoice_id']);
				}
			}
		}
		if ($last_data['sumber_dokumen'] == 'BAPP_SPK_KEBUN') {
			$last_data = $this->retrieve_detail($id);
			foreach ($last_data as $key => $value) {
				if ($value['invoice_id']) {
					$this->db->query("update est_spk_ba_ht set nilai_dibayar=nilai_dibayar-" . $value['debet'] . "
					where id=" . $value['invoice_id']);
				}
			}
		}
		if ($last_data['sumber_dokumen'] == 'INVOICE_AR') {
			$last_data = $this->retrieve_detail($id);
			foreach ($last_data as $key => $value) {
				if ($value['invoice_id']) {
					$this->db->query("update acc_sales_invoice set nilai_dibayar=nilai_dibayar-" . $value['kredit'] . "
					where id=" . $value['invoice_id']);
				}
			}
		}
		// if ($last_data['sumber_dokumen'] == 'INVOICE_AR') {
		// 	//balikin dulu dibayar invoice AR
		// 	$this->db->query("update acc_sales_invoice set nilai_dibayar=nilai_dibayar-" . $last_data['nilai'] . "
		// 	where id=" . $last_data['ref_id']);
		// }
		$this->db->where('jurnal_id', $id);
		$this->db->delete('acc_kasbank_dt');
		$this->db->where('id', $id);
		$this->db->delete('acc_kasbank_ht');
		return true;
	}
	public function posting($id,	$input)
	{
		$id = (int)$id;
		$data['is_posting'] = 1;
		$data['diposting_tanggal'] = date('Y-m-d H:i:s');
		$data['diposting_oleh'] =  $input['diposting_oleh'];
		$this->db->where('id', $id);
		$this->db->update('acc_kasbank_ht', $data);


		return true;
	}
	public function retrieve_by_id($id)
	{
		$this->db->select('*');
		$this->db->from('acc_kasbank_ht');
		$this->db->where('id', $id);
		$res = $this->db->get();
		return $res->row_array();
	}

	public function retrieve_detail($hdid)
	{
		$this->db->select('acc_kasbank_dt.*,gbm_organisasi.parent_id as divisi_id');
		$this->db->from('acc_kasbank_dt');
		$this->db->join('gbm_organisasi', 'acc_kasbank_dt.blok_stasiun_id=gbm_organisasi.id', 'LEFT');
		$this->db->where('acc_kasbank_dt.jurnal_id', $hdid);
		$res = $this->db->get();
		return $res->result_array();
	}

	function getNomorJurnal($lokasi_id = null, $tanggal = null,  $prefix = '')
	{
		$lastnumber = $this->db->query("select  max(no_transaksi)as last from acc_kasbank_ht ")->row_array();
		// var_dump($lastnumber);exit();
		if (!empty($lastnumber['last'])) {
			$str = (substr($lastnumber['last'], -6));
			$snumber = (int)$str + 1;
		} else {
			$snumber = 1;
		}
		$strnumber = sprintf("%06s", $snumber);
		return  $prefix . $strnumber;
		// $index = 11;
		// $prefix = 'B';
		// echo sprintf("%s%011s", $prefix, $index);


	}
	public function print_slip(
		$id = null
	) {
		$query    = "Select a.*,b.qty,c.kode,c.nama,c.satuan
		FROM acc_kasbank_ht a inner join acc_kasbank_dt b
	   on a.id=b.jurnal_id
	   inner join inv_blok c on b.blok=c.id
	   inner join inv_gudang d on a.divisi_id=d.id
			where 1=1 and a.id=" . $id . ";";
		$data     = $this->db->query($query)->result_array();
		return $data;
	}
	public function save_upload($inv, $file)
	{
		$id = (int) $inv['id'];

		$data['upload_file'] = $file;

		$this->db->where('id', $id);
		$this->db->update('acc_kasbank_ht', $data);

		return true;
	}
}
