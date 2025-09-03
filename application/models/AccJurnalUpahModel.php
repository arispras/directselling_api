<?php

class AccJurnalUpahModel extends CI_Model
{

	public function create(
		$input = null
	) {
		$data = array(
			'lokasi_id' => $input['lokasi_id']['id'],
			'no_jurnal' => $input['no_jurnal'],
			'tanggal' => $input['tanggal'],
			'keterangan' => $input['keterangan'],
			'tipe_jurnal' => 'UMUM' // $input['tipe_jurnal']['id'],
		);
		$this->db->insert('acc_jurnal_upah_ht', $data);
		$id = $this->db->insert_id();

		$details = $input['details'];

		foreach ($details as $key => $value) {
			$this->db->insert("acc_jurnal_upah_dt", array(
				'jurnal_id' => $id,
				'lokasi_id' => $value['lokasi_id']['id'],
				'acc_akun_id' => $value['acc_akun_id']['id'],
				'debet' => $value['debet'],
				'kredit' => $value['kredit'],
				'ket' => $value['ket'],
				'blok_stasiun_id' => $value['blok_id']['id'],
				'kegiatan_id' => $value['kegiatan_id']['id'],
				'kendaraan_mesin_id' => $value['traksi_id']['id'],

			));
		}


		return $id;
	}
	public function create_header(
		$input = []
	) {
		$data = array(
			'lokasi_id' => $input['lokasi_id'],
			'no_jurnal' => $input['no_jurnal'], //$this->getNomorJurnal(null, null, null),
			'tanggal' => $input['tanggal'],
			'no_ref' => $input['no_ref'],
			'ref_id' => $input['ref_id'],
			'tipe_jurnal' => $input['tipe_jurnal'],
			'modul' => $input['modul'],
			'keterangan' => $input['keterangan'],
			'is_posting' => $input['is_posting'],
			'diposting_tanggal' =>  date('Y-m-d H:i:s'),
			'diposting_oleh' => $input['diposting_oleh']
		);
		$this->db->insert('acc_jurnal_upah_ht', $data);
		$id = $this->db->insert_id();
		return $id;
	}
	public function create_detail(
		$jurnal_id,
		$input = null

	) {

		$value = $input;
		$this->db->insert("acc_jurnal_upah_dt", array(
			'lokasi_id' => $value['lokasi_id'],
			'jurnal_id' => $jurnal_id,
			'acc_akun_id' => $value['acc_akun_id'],
			'debet' => $value['debet'],
			'kredit' => $value['kredit'],
			'ket' => $value['ket'],
			'no_referensi' => $value['no_referensi'],
			'referensi_id' => $value['id'],
			'blok_stasiun_id' => $value['blok_stasiun_id'],
			'kegiatan_id' => $value['kegiatan_id'],
			'kendaraan_mesin_id' => $value['kendaraan_mesin_id'],
			'umur_tanam_blok' => $value['umur_tanam_blok'],
			'karyawan_id' => $value['karyawan_id'],
			'divisi_id' => $value['divisi_id'],
			'item_id' => $value['item_id'],
			'tipe' => $value['tipe'],
			'hk' => $value['hk'],

		));
		$id = $this->db->insert_id();
		return $id;
	}
	public function delete_by_ref_id_and_modul($ref_id, $modul)
	{


		$this->db->select('*');
		$this->db->from('acc_jurnal_upah_ht');
		$this->db->where('ref_id', $ref_id);
		$this->db->where('modul', $modul);
		$jurnal = $this->db->get()->result_array();

		foreach ($jurnal as $key => $j) {
			$this->db->where('jurnal_id', $j['id']);
			$this->db->delete('acc_jurnal_upah_dt');

			$this->db->where('id', $j['id']);
			$this->db->delete('acc_jurnal_upah_ht');
		}
	}
	public function update(
		$id,
		$input
	) {
		$data = array(
			'lokasi_id' => $input['lokasi_id']['id'],
			'no_jurnal' => $input['no_jurnal'],
			'tanggal' => $input['tanggal'],
			'keterangan' => $input['keterangan'],
			'tipe_jurnal' => 'UMUM' // $input['tipe_jurnal']['id'],
		);
		$this->db->where('id', $id);
		$this->db->update('acc_jurnal_upah_ht', $data);

		// Hapus  detail //
		$this->db->where('jurnal_id', $id);
		$this->db->delete('acc_jurnal_upah_dt');

		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("acc_jurnal_upah_dt", array(
				'jurnal_id' => $id,
				'lokasi_id' => $value['lokasi_id']['id'],
				'acc_akun_id' => $value['acc_akun_id']['id'],
				'debet' => $value['debet'],
				'kredit' => $value['kredit'],
				'ket' => $value['ket'],
				'blok_stasiun_id' => $value['blok_id']['id'],
				'kegiatan_id' => $value['kegiatan_id']['id'],
				'kendaraan_mesin_id' => $value['traksi_id']['id'],


			));
			$detail_id = $this->db->insert_id();
		}
		return $id;
	}


	public function delete($id)
	{
		$this->db->where('jurnal_id', $id);
		$this->db->delete('acc_jurnal_upah_dt');
		$this->db->where('id', $id);
		$this->db->delete('acc_jurnal_upah_ht');
		return true;
	}
	public function posting($id,	$input)
	{
		$id = (int)$id;
		$data['is_posting'] = 1;
		$data['diposting_tanggal'] = date('Y-m-d H:i:s');
		$data['diposting_oleh'] = 1; // $input['user_posting]
		$this->db->where('id', $id);
		$this->db->update('acc_jurnal_upah_ht', $data);


		return true;
	}
	public function retrieve_by_id($id)
	{
		$this->db->select('*');
		$this->db->from('acc_jurnal_upah_ht');
		$this->db->where('id', $id);
		$res = $this->db->get();
		return $res->row_array();
	}

	public function retrieve_detail($hdid)
	{
		$this->db->select('*');
		$this->db->from('acc_jurnal_upah_dt');
		$this->db->where('jurnal_id', $hdid);
		$res = $this->db->get();
		return $res->result_array();
	}

	function getNomorJurnal($lokasi_id = null, $tanggal = null,  $prefix = '')
	{
		$lastnumber = $this->db->query("select  max(no_jurnal)as last from acc_jurnal_upah_ht ")->row_array();
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
}
