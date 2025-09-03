<?php

class AccAngkutInvoiceModel extends CI_Model
{


	public function create(
		$input = null
	) {
		$data = array(
			'lokasi_id' => $input['lokasi_id']['id'],
			'rekap_id' => $input['rekap_id']['id'],
			'supplier_id' => $input['supplier_id']['id'],
			'no_invoice' => $input['no_invoice'],
			'tanggal' => $input['tanggal'],
			'tanggal_tempo' => $input['tanggal_tempo'],
			// 'item_id' => $input['item_id'],
			// 'periode_kt_dari' => $input['periode_kt_dari'],
			// 'periode_kt_sd' => $input['periode_kt_sd'],
			'total_berat_terima' => $input['total_berat_terima'],
			'sub_total' => $input['sub_total'],
			'total_tagihan' => $input['total_tagihan'],
			'potongan' => $input['potongan'],
			'ppn' => $input['ppn'],
			'pph' => $input['pph'],
			'harga_susut_per_kg' => $input['harga_susut_per_kg'],
			'toleransi' => $input['toleransi'],
			'harga_satuan' => $input['harga_satuan'],
			'dibuat_tanggal' => date('Y-m-d H:i:s'),
			'dibuat_oleh' =>  $input['dibuat_oleh'],
			'keterangan' =>  $input['keterangan']
			// 'diubah_tanggal' => date('Y-m-d H:i:s'),
			// 'diubah_oleh' =>  $input['diubah_oleh']
		);
		$this->db->insert('acc_angkut_invoice_ht', $data);
		$id = $this->db->insert_id();
		$details = $input['detail'];
		foreach ($details as $key => $value) {
			$this->db->insert("acc_angkut_invoice_dt", array(
				'acc_angkut_invoice_id ' => $id,
				'sjpp_id' => $value['sjpp_id'],
				'pekerjaan' => $value['pekerjaan'],
				'uom' => $value['uom'],
				'qty' => $value['qty'],
				'harga' => $value['harga']
			));
		}
		return $id;
	}
	public function update(
		$id,
		$input
	) {
		$data = array(
			'lokasi_id' => $input['lokasi_id']['id'],
			'rekap_id' => $input['rekap_id']['id'],
			'supplier_id' => $input['supplier_id']['id'],
			'no_invoice' => $input['no_invoice'],
			'tanggal' => $input['tanggal'],
			'tanggal_tempo' => $input['tanggal_tempo'],
			// 'item_id' => $input['item_id'],
			// 'periode_kt_dari' => $input['periode_kt_dari'],
			// 'periode_kt_sd' => $input['periode_kt_sd'],
			'total_berat_terima' => $input['total_berat_terima'],
			'sub_total' => $input['sub_total'],
			'total_tagihan' => $input['total_tagihan'],
			'ppn' => $input['ppn'],
			'pph' => $input['pph'],
			'potongan' => $input['potongan'],
			'harga_susut_per_kg' => $input['harga_susut_per_kg'],
			'toleransi' => $input['toleransi'],
			'harga_satuan' => $input['harga_satuan'],
			// 'dibuat_tanggal' => date('Y-m-d H:i:s'),
			// 'dibuat_oleh' =>  $input['dibuat_oleh'],
			'diubah_tanggal' => date('Y-m-d H:i:s'),
			'diubah_oleh' =>  $input['diubah_oleh'],
			'keterangan' =>  $input['keterangan']
		);
		$this->db->where('id', $id);
		$this->db->update('acc_angkut_invoice_ht', $data);

		// hapus  detail
		$this->db->where('acc_angkut_invoice_id', $id);
		$this->db->delete('acc_angkut_invoice_dt');

		$details = $input['detail'];
		foreach ($details as $key => $value) {
			$this->db->insert("acc_angkut_invoice_dt", array(
				'acc_angkut_invoice_id ' => $id,
				'sjpp_id' => $value['sjpp_id'],
				'pekerjaan' => $value['pekerjaan'],
				'uom' => $value['uom'],
				'qty' => $value['qty'],
				'harga' => $value['harga']
			));
		}

		// $details = $input['details'];
		// foreach ($details as $key => $value) {
		// 	$this->db->insert("acc_angkut_invoice_dt", array(
		// 		'rekap_id' => $id,
		// 		'dt_lokasi_id' => $value['dt_lokasi_id']['id'],
		// 		'no_kartu_timbang' => $value['no_kartu_timbang'],
		// 		'dt_tanggal' => $value['dt_tanggal'],
		// 		// 'bruto' => $value['bruto'],
		// 		// 'tara' => $value['tara'],
		// 		// 'netto' => $value['netto'],
		// 		'bruto_cust' => $value['bruto_cust'],
		// 		'tara_cust' => $value['tara_cust'],
		// 		'netto_cust' => $value['netto_cust'],
		// 	));
		// }

		return $id;
	}


	public function delete($id)
	{

		$this->db->where('acc_angkut_invoice_id', $id);
		$this->db->delete('acc_angkut_invoice_dt');
		$this->db->where('id', $id);
		$this->db->delete('acc_angkut_invoice_ht');
		return true;
	}

	public function retrieve_by_id($id)
	{
		$this->db->select('a.*,b.sumber_timbangan,b.periode_kt_dari as tgl_periode_mulai,b.periode_kt_sd as tgl_periode_sd');
		$this->db->from('acc_angkut_invoice_ht a');
		$this->db->join('prc_rekap_angkut_hd b','a.rekap_id=b.id','left');
		$this->db->where('a.id', $id);
		$res = $this->db->get();
		return $res->row_array();
	}

	public function retrieve_detail($hdid)
	{
		$query="select a.*,b.netto_kirim,b.netto_customer,b.bruto_kirim,b.bruto_customer,
		b.tara_kirim,b.tara_customer ,b.tanggal_timbang,b.tanggal_terima 
		from acc_angkut_invoice_dt a inner join pks_timbangan_kirim_sj_vw b
		on a.sjpp_id=b.id where acc_angkut_invoice_id=". $hdid ."";
		$res=$this->db->query($query)->result_array();
		return $res;
	}
	public function posting($id,	$input)
	{
		$id = (int)$id;
		$data['is_posting'] = 1;
		$data['diposting_tanggal'] = date('Y-m-d H:i:s');
		$data['diposting_oleh'] =  $input['diposting_oleh'];
		$this->db->where('id', $id);
		$this->db->update('acc_angkut_invoice_ht', $data);
		return true;
	}

	public function print_slip_invoice_header(
		$id = null
	) {
		$query    = "SELECT a.*,b.kode_supplier,b.nama_supplier,b.alamat,b.no_telpon,b.nama_bank,
		b.no_rekening,b.atas_nama,b.npwp,b.alamat_npwp,b.contact_person,b.no_hp,b.cabang_bank, c.no_rekap,c.periode_kt_dari,c.periode_kt_sd,c.tanggal as tanggal_rekap,
				d.no_spk,d.tanggal as tanggal_spk,c.periode_kt_dari as periode_mulai,c.periode_kt_sd as periode_sda,c.sumber_timbangan,
				c.item_id,e.nama AS nama_produk
				FROM acc_angkut_invoice_ht a 
				inner join gbm_supplier b   on a.supplier_id=b.id 
				inner join prc_rekap_angkut_hd c   on a.rekap_id=c.id 
				inner join prc_kontrak_angkut d on c.spk_id=d.id
				INNER JOIN inv_item e ON c.item_id=e.id
				where 1=1 and a.id=" . $id . ";";
		$data     = $this->db->query($query)->row_array();
		return $data;
	}
	public function print_slip_invoice_detail(
		$id = null
	) {
		$query    = "SELECT b.*,c.*,b.id as id,b.sjpp_id as sjpp_id FROM acc_angkut_invoice_ht a inner join
		 acc_angkut_invoice_dt b on a.id=b.acc_angkut_invoice_id 
		 inner join pks_timbangan_kirim_sj_vw c on b.sjpp_id=c.id
			where 1=1 and b.acc_angkut_invoice_id=" . $id . ";";
		$data     = $this->db->query($query)->result_array();
		return $data;
	}
	public function print_slip_invoice_detail_sum(
		$id = null
	) {
		$query    = "SELECT SUM(qty)AS qty,MAX(harga)AS harga  FROM acc_angkut_invoice_ht a inner join
		acc_angkut_invoice_dt b on a.id=b.acc_angkut_invoice_id 
		inner join pks_timbangan_kirim_sj_vw c on b.sjpp_id=c.id
		where 1=1 and b.acc_angkut_invoice_id=" . $id . "
		GROUP BY a.id ;";
		$data     = $this->db->query($query)->result_array();
		return $data;
	}
}
