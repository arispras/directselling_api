<?php

class AccArInvoiceModel extends CI_Model
{

	public function create(
		$input = null
	) {
		$supp=$this->db->query("select * from gbm_customer where id=".$input['customer_id']['id']."")->row_array();
		$akun_supp_id=$supp['acc_akun_id'];
		$data = array(
			'lokasi_id' => $input['lokasi_id']['id'],
			'customer_id' => $input['customer_id']['id'],
			'akun_customer_id' => $akun_supp_id,
			'no_invoice' => $input['no_invoice'],
			'nilai_invoice' => $input['nilai_invoice'],
			'no_ref' => $input['no_ref'],
			'no_faktur_pajak' => $input['no_faktur_pajak'],
			'tanggal' => $input['tanggal'],
			'tanggal_tempo' => $input['tanggal_tempo'],
			'tanggal_terima' => $input['tanggal_terima'],
			'deskripsi' => $input['deskripsi'],
			'no_referensi' => $input['no_referensi'],
			'jenis_invoice' => $input['jenis_invoice']['id'],
			'so_id' => $input['so_id']['id'],
			'dibuat_tanggal' => date('Y-m-d H:i:s'),
			'dibuat_oleh' =>  $input['dibuat_oleh'],
			'termin_id' => $input['termin_id']['id'],
		);
		$this->db->insert('acc_ar_invoice_ht', $data);
		$id = $this->db->insert_id();

		$details = $input['details'];

		foreach ($details as $key => $value) {
			$this->db->insert("acc_ar_invoice_dt", array(
				'invoice_id' => $id,
				'lokasi_id' =>$input['lokasi_id']['id'],
				'acc_akun_id' => $value['acc_akun_id']['id'],
				'debet' => $value['debet'],
				'kredit' => $value['kredit'],
				'ket' => $value['ket'],

			));
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
		$this->db->insert('acc_ar_invoice_ht', $data);
		$id = $this->db->insert_id();
		return $id;
	}
	public function create_detail(
		$invoice_id,
		$input = null

	) {

		$value = $input;
		$this->db->insert("acc_ar_invoice_dt", array(
			'invoice_id' => $invoice_id,
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
	public function delete_by_ref_id_and_modul($ref_id ,$modul)
	{

		
		$this->db->select('*');
		$this->db->from('acc_ar_invoice_ht');
		$this->db->where('ref_id', $ref_id);
		$this->db->where('modul', $modul);
		$jurnal = $this->db->get()->row_array();

		$this->db->where('invoice_id', $jurnal['id']);
		$this->db->delete('acc_ar_invoice_dt');

		$this->db->where('id', $jurnal['id']);
		$this->db->delete('acc_ar_invoice_ht');
	}
	public function update(
		$id,
		$input
	) {
		$supp=$this->db->query("select * from gbm_customer where id=".$input['customer_id']['id']."")->row_array();
		$akun_supp_id=$supp['acc_akun_id'];
		$data = array(
			'lokasi_id' => $input['lokasi_id']['id'],
			'customer_id' => $input['customer_id']['id'],
			'akun_customer_id' => $akun_supp_id,
			'no_invoice' => $input['no_invoice'],
			'nilai_invoice' => $input['nilai_invoice'],
			'no_ref' => $input['no_ref'],
			'no_faktur_pajak' => $input['no_faktur_pajak'],
			'tanggal' => $input['tanggal'],
			'tanggal_tempo' => $input['tanggal_tempo'],
			'tanggal_terima' => $input['tanggal_terima'],
			'deskripsi' => $input['deskripsi'],
			'no_referensi' => $input['no_referensi'],
			'jenis_invoice' => $input['jenis_invoice']['id'],
			'so_id' => $input['so_id']['id'],
			'diubah_tanggal' => date('Y-m-d H:i:s'),
			'diubah_oleh' =>  $input['diubah_oleh'],
			'termin_id' => $input['termin_id']['id'],
		);
		$this->db->where('id', $id);
		$this->db->update('acc_ar_invoice_ht', $data);

		// Hapus  detail //
		$this->db->where('invoice_id', $id);
		$this->db->delete('acc_ar_invoice_dt');

		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("acc_ar_invoice_dt", array(
				'invoice_id' => $id,
				'lokasi_id' =>$input['lokasi_id']['id'],
				'acc_akun_id' => $value['acc_akun_id']['id'],
				'debet' => $value['debet'],
				'kredit' => $value['kredit'],
				'ket' => $value['ket'],

			));
			$detail_id = $this->db->insert_id();
		}
		return $id;
	}


	public function update_faktur(
		$id,
		$input
	) {
		// $supp=$this->db->query("select * from gbm_customer where id=".$input['customer_id']['id']."")->row_array();
		// $akun_supp_id=$supp['acc_akun_id'];
		$data = array(
			'no_faktur_pajak' => $input['no_faktur_pajak'],
		);
		$this->db->where('id', $id);
		$this->db->update('acc_ar_invoice_ht', $data);
		
		return $id;
	}


	public function delete($id)
	{
		$this->db->where('invoice_id', $id);
		$this->db->delete('acc_ar_invoice_dt');
		$this->db->where('id', $id);
		$this->db->delete('acc_ar_invoice_ht');
		return true;
	}
	public function posting($id,	$input)
	{
		$id = (int)$id;
		$data['is_posting'] = 1;
		$data['diposting_tanggal'] = date('Y-m-d H:i:s');
		$data['diposting_oleh'] =  $input['diposting_oleh'];
		$this->db->where('id', $id);
		$this->db->update('acc_ar_invoice_ht', $data);


		return true;
	}
	public function retrieve_by_id($id)
	{
		$this->db->select('*');
		$this->db->from('acc_ar_invoice_ht');
		$this->db->where('id', $id);
		$res = $this->db->get();
		return $res->row_array();
	}

	public function retrieve_detail($hdid)
	{
		$this->db->select('*');
		$this->db->from('acc_ar_invoice_dt');
		$this->db->where('invoice_id', $hdid);
		$res = $this->db->get();
		return $res->result_array();
	}

	function getNomorJurnal($lokasi_id = null, $tanggal = null,  $prefix = '')
	{
		$lastnumber = $this->db->query("select  max(no_transaksi)as last from acc_ar_invoice_ht ")->row_array();
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
		FROM acc_ar_invoice_ht a inner join acc_ar_invoice_dt b
	   on a.id=b.invoice_id
	   inner join inv_blok c on b.blok=c.id
	   inner join inv_gudang d on a.divisi_id=d.id
			where 1=1 and a.id=" . $id . ";";
		$data     = $this->db->query($query)->result_array();
		return $data;
	}
	
}
