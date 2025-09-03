<?php

class SlsRekapModel extends CI_Model
{


	public function create(
		$input = null
	) {
		$data = array(
			'lokasi_id' => $input['lokasi_id']['id'],
			'spk_id' => $input['spk_id']['id'],
			'customer_id' => $input['customer_id']['id'],
			'no_rekap' => $input['no_rekap'],
			'tanggal' => $input['tanggal'],
			'item_id' => $input['item_id'],
			'total_berat_terima' => $input['total_berat_terima'],
			'periode_kt_dari' => $input['periode_kt_dari'],
			'periode_kt_sd' => $input['periode_kt_sd'],
			'sub_total' => $input['sub_total'],
			'harga_satuan' => $input['harga_satuan'],
			'dibuat_oleh' =>  $input['dibuat_oleh'],
			'dibuat_tanggal' =>  date("Y-m-d H:i:s"),
			'diubah_oleh' =>  $input['diubah_oleh'],
			'diubah_tanggal' =>  date("Y-m-d H:i:s"),
		);
		$this->db->insert('sls_rekap_hd', $data);
		$id = $this->db->insert_id();

		$details = $input['suratjalan'];
		foreach ($details as $key => $value) {

			$this->db->insert("sls_rekap_dt", array(
				'rekap_id' => $id,
				'sjpp_id' => $value

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
			'spk_id' => $input['spk_id']['id'],
			'customer_id' => $input['customer_id']['id'],
			'no_rekap' => $input['no_rekap'],
			'tanggal' => $input['tanggal'],
			'item_id' => $input['item_id'],
			'total_berat_terima' => $input['total_berat_terima'],
			'periode_kt_dari' => $input['periode_kt_dari'],
			'periode_kt_sd' => $input['periode_kt_sd'],
			'sub_total' => $input['sub_total'],
			'harga_satuan' => $input['harga_satuan'],
			'diubah_oleh' =>  $input['diubah_oleh'],
			'diubah_tanggal' =>  date("Y-m-d H:i:s"),
		);
		$this->db->where('id', $id);
		$this->db->update('sls_rekap_hd', $data);

		// hapus  detail
		$this->db->where('rekap_id', $id);
		$this->db->delete('sls_rekap_dt');

		$details = $input['suratjalan'];
		foreach ($details as $key => $value) {

			$this->db->insert("sls_rekap_dt", array(
				'rekap_id' => $id,
				'sjpp_id' => $value

			));
		}

		// $details = $input['details'];
		// foreach ($details as $key => $value) {
		// 	$this->db->insert("sls_rekap_dt", array(
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

		$this->db->where('rekap_id', $id);
		$this->db->delete('sls_rekap_dt');
		$this->db->where('id', $id);
		$this->db->delete('sls_rekap_hd');
		return true;
	}

	public function retrieve_by_id($id)
	{
		$this->db->select('a.*,c.nama as nama_produk');
		$this->db->from('sls_rekap_hd a');
		$this->db->join('sls_kontrak b','a.spk_id=b.id','left');
		$this->db->join('inv_item c','b.produk_id=c.id','left');
		$this->db->where('a.id', $id);
		$res = $this->db->get();
		return $res->row_array();
		// $this->db->select('*');
		// $this->db->from('sls_rekap_hd');
		// $this->db->where('id', $id);
		// $res = $this->db->get();
		// return $res->row_array();
	}

	public function retrieve_detail($hdid)
	{
		$this->db->select('*');
		$this->db->from('sls_rekap_dt');
		// $this->db->join('pks_shift', 'sls_rekap_dt.lokasi_id = shift.id');
		$this->db->where('rekap_id', $hdid);
		$res = $this->db->get();
		return $res->result_array();
	}


	public function print_slip(
		$id = null
	) {
		$query    = "SELECT a.*,b.qty,c.kode,c.nama,c.satuan
		FROM sls_rekap_hd a inner join sls_rekap_dt b
	   on a.id=b.rekap_id
	   inner join inv_blok c on b.blok=c.id
	   inner join inv_gudang d on a.divisi_id=d.id
			where 1=1 and a.id=" . $id . ";";
		$data     = $this->db->query($query)->result_array();
		return $data;
	}

	public function print_slip_header(
		$id = null
	) {
		$query    = "SELECT a.*,
			b.kode_customer,b.nama_customer,
			c.no_spk,c.tanggal as tanggal_spk,
			d.nama as nama_item
			FROM sls_rekap_hd a 
			inner join gbm_customer b on a.customer_id=b.id 
			inner join prc_kontrak c on a.spk_id=c.id
			left join inv_item d on a.item_id=d.id
			where 1=1 and a.id=" . $id . ";";
		$data     = $this->db->query($query)->row_array();
		return $data;
	}

	public function print_slip_detail(
		$id = null
	) {
		$query    = "SELECT b.*, c.* 
		FROM sls_rekap_hd a 
		inner join sls_rekap_dt b on a.id=b.rekap_id 
		inner join pks_sjpp c on b.sjpp_id=c.id
		where 1=1 and b.rekap_id=" . $id . ";";
		$data     = $this->db->query($query)->result_array();
		return $data;
	}

}
