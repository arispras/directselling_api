<?php

class AccTbsInvoiceModel extends CI_Model
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
			'sub_total' => round($input['sub_total']),
			'ppn' => $input['ppn'],
			'pph' => $input['pph'],
			'total_tagihan' => round($input['total_tagihan']),
			'harga_satuan' =>0,
			'dibuat_tanggal' => date('Y-m-d H:i:s'),
			'dibuat_oleh' =>  $input['dibuat_oleh'],
			// 'diubah_tanggal' => date('Y-m-d H:i:s'),
			// 'diubah_oleh' =>  $input['diubah_oleh']
		);
		$this->db->insert('acc_tbs_invoice_ht', $data);
		$id = $this->db->insert_id();
		$details = $input['detail'];
		foreach ($details as $key => $value) {
			$this->db->insert("acc_tbs_invoice_dt", array(
				'acc_tbs_invoice_id ' => $id,
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
			'sub_total' => round($input['sub_total']),
			'total_tagihan' =>round($input['total_tagihan']),
			'ppn' => $input['ppn'],
			'pph' => $input['pph'],
			'harga_satuan' =>0,
			// 'dibuat_tanggal' => date('Y-m-d H:i:s'),
			// 'dibuat_oleh' =>  $input['dibuat_oleh'],
			'diubah_tanggal' => date('Y-m-d H:i:s'),
			'diubah_oleh' =>  $input['diubah_oleh']
		);
		$this->db->where('id', $id);
		$this->db->update('acc_tbs_invoice_ht', $data);

		// hapus  detail
		$this->db->where('acc_tbs_invoice_id', $id);
		$this->db->delete('acc_tbs_invoice_dt');

		$details = $input['detail'];
		foreach ($details as $key => $value) {
			$this->db->insert("acc_tbs_invoice_dt", array(
				'acc_tbs_invoice_id ' => $id,
				'pekerjaan' => $value['pekerjaan'],
				'uom' => $value['uom'],
				'qty' => $value['qty'],
				'harga' => $value['harga']
			));
		}

		// $details = $input['details'];
		// foreach ($details as $key => $value) {
		// 	$this->db->insert("acc_tbs_invoice_dt", array(
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

		$this->db->where('acc_tbs_invoice_id', $id);
		$this->db->delete('acc_tbs_invoice_dt');
		$this->db->where('id', $id);
		$this->db->delete('acc_tbs_invoice_ht');
		return true;
	}

	public function retrieve_by_id($id)
	{
		$this->db->select('a.*');
		$this->db->from('acc_tbs_invoice_ht a');
		$this->db->join('prc_rekap_ht b','a.rekap_id=b.id','left');
		$this->db->where('a.id', $id);
		$res = $this->db->get();
		return $res->row_array();
	}

	public function retrieve_detail($hdid)
	{
		$query="select * from acc_tbs_invoice_dt where acc_tbs_invoice_id=". $hdid ."";
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
		$this->db->update('acc_tbs_invoice_ht', $data);
		return true;
	}

	public function print_slip_invoice_header(
		$id = null
	) {
		$query    = "SELECT a.*,b.kode_supplier,b.nama_supplier,b.alamat,b.no_telpon,b.nama_bank,
		b.no_rekening,b.atas_nama,b.npwp,b.alamat_npwp,b.contact_person,b.no_hp,b.cabang_bank, c.no_rekap,c.periode_kt_dari,c.periode_kt_sd,c.tanggal as tanggal_rekap,
				d.no_spk,d.tanggal as tanggal_spk
				FROM acc_tbs_invoice_ht a left join gbm_supplier b
			   on a.supplier_id=b.id left join prc_rekap_ht c 
			   on a.rekap_id=c.id left join prc_kontrak d on c.spk_id=d.id
			where 1=1 and a.id=" . $id . ";";
		$data     = $this->db->query($query)->row_array();
		return $data;
	}
	public function print_slip_invoice_detail(
		$id = null
	) {
		$query    = "SELECT b.* FROM acc_tbs_invoice_ht a inner join
		 acc_tbs_invoice_dt b on a.id=b.acc_tbs_invoice_id 
			where 1=1 and b.acc_tbs_invoice_id=" . $id . ";";
		$data     = $this->db->query($query)->result_array();
		return $data;
	}
}
