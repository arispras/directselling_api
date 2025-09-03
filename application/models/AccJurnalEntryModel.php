<?php

class AccJurnalEntryModel extends CI_Model
{

	public function create(
		$input = null
	) {
		$data = array(
			'lokasi_id' => $input['lokasi_id']['id'],
			'no_jurnal' => $input['no_jurnal'],
			'no_referensi' => $input['no_referensi'],
			'tanggal' => $input['tanggal'],
			'keterangan' => $input['keterangan'],
			'tipe_jurnal' => $input['tipe_jurnal']['id'],
			'dibuat_tanggal' => date('Y-m-d H:i:s'),
			'dibuat_oleh' =>  $input['dibuat_oleh'],
			// 'diubah_tanggal' => date('Y-m-d H:i:s'),
			// 'diubah_oleh' =>  $input['diubah_oleh']
		);
		$this->db->insert('acc_jurnal_entry_ht', $data);
		$id = $this->db->insert_id();

		$details = $input['details'];

		foreach ($details as $key => $value) {
			$this->db->insert("acc_jurnal_entry_dt", array(
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
			'keterangan' => $input['keterangan']
		);
		$this->db->insert('acc_jurnal_entry_ht', $data);
		$id = $this->db->insert_id();
		return $id;
	}
	public function create_detail(
		$jurnal_id,
		$input = null

	) {

		$value = $input;
		$this->db->insert("acc_jurnal_entry_dt", array(
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
			'kendaraan_mesin_id' => $value['kendaraan_mesin_id']
		));
		$id = $this->db->insert_id();
		return $id;
	}
	public function delete_by_ref_id_and_modul($ref_id, $modul)
	{


		$this->db->select('*');
		$this->db->from('acc_jurnal_entry_ht');
		$this->db->where('ref_id', $ref_id);
		$this->db->where('modul', $modul);
		$jurnal = $this->db->get()->row_array();

		$this->db->where('jurnal_id', $jurnal['id']);
		$this->db->delete('acc_jurnal_entry_dt');

		$this->db->where('id', $jurnal['id']);
		$this->db->delete('acc_jurnal_entry_ht');
	}
	public function update(
		$id,
		$input
	) {
		$data = array(
			'lokasi_id' => $input['lokasi_id']['id'],
			'no_jurnal' => $input['no_jurnal'],
			'no_referensi' => $input['no_referensi'],
			'tanggal' => $input['tanggal'],
			'keterangan' => $input['keterangan'],
			'tipe_jurnal' => $input['tipe_jurnal']['id'],
			'diubah_tanggal' => date('Y-m-d H:i:s'),
			'diubah_oleh' =>  $input['diubah_oleh']
		);
		$this->db->where('id', $id);
		$this->db->update('acc_jurnal_entry_ht', $data);

		// Hapus  detail //
		$this->db->where('jurnal_id', $id);
		$this->db->delete('acc_jurnal_entry_dt');

		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("acc_jurnal_entry_dt", array(
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
		$this->db->delete('acc_jurnal_entry_dt');
		$this->db->where('id', $id);
		$this->db->delete('acc_jurnal_entry_ht');
		return true;
	}
	public function posting($id,	$input)
	{
		$id = (int)$id;
		$data['is_posting'] = 1;
		$data['diposting_tanggal'] = date('Y-m-d H:i:s');
		$data['diposting_oleh'] =  $input['diposting_oleh'];
		$this->db->where('id', $id);
		$this->db->update('acc_jurnal_entry_ht', $data);


		return true;
	}
	public function retrieve_by_id($id)
	{
		$this->db->select('*');
		$this->db->from('acc_jurnal_entry_ht');
		$this->db->where('id', $id);
		$res = $this->db->get();
		return $res->row_array();
	}

	public function retrieve_detail($hdid)
	{
		$this->db->select('acc_jurnal_entry_dt.*,gbm_organisasi.parent_id as divisi_id');
		$this->db->from('acc_jurnal_entry_dt');
		$this->db->join('gbm_organisasi', 'acc_jurnal_entry_dt.blok_stasiun_id=gbm_organisasi.id', 'LEFT');
		$this->db->where('acc_jurnal_entry_dt.jurnal_id', $hdid);
		$this->db->order_by('id', 'ASC');
		$res = $this->db->get();
		return $res->result_array();
	}

	function getNomorJurnal($lokasi_id = null, $tanggal = null,  $prefix = '')
	{
		$lastnumber = $this->db->query("select  max(no_jurnal)as last from acc_jurnal_entry_ht ")->row_array();
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
		FROM acc_jurnal_entry_ht a inner join acc_jurnal_entry_dt b
	   on a.id=b.jurnal_id
	   inner join inv_blok c on b.blok=c.id
	   inner join inv_gudang d on a.divisi_id=d.id
			where 1=1 and a.id=" . $id . ";";
		$data     = $this->db->query($query)->result_array();
		return $data;
	}
}
