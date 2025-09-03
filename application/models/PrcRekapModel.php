<?php

class PrcRekapModel extends CI_Model
{


	public function create(
		$input = null
	) {
		$data = array(
			'lokasi_id' => $input['lokasi_id']['id'],
			'spk_id' => $input['spk_id']['id'],
			'supplier_id' => $input['supplier_id']['id'],
			'no_rekap' => $input['no_rekap'],
			'tanggal' => $input['tanggal'],
			'item_id' => $input['item_id'],
			'periode_kt_dari' => $input['periode_kt_dari'],
			'periode_kt_sd' => $input['periode_kt_sd'],
			'total_berat_terima' => $input['total_berat_terima'],
			'sub_total' => $input['sub_total'],
			'harga_satuan' =>0,
			'dibuat_oleh'=> $input['dibuat_oleh'],
			'dibuat_tanggal'=> date("Y-m-d H:i:s"),
			'diubah_oleh'=> $input['diubah_oleh'],
			'diubah_tanggal'=> date("Y-m-d H:i:s"),
		);
		$this->db->insert('prc_rekap_ht', $data);
		$id = $this->db->insert_id();
		$details = $input['detail'];
		foreach ($details as $key => $value) {
			$this->db->insert("prc_rekap_dt", array(
				'rekap_id' => $id,
				'pks_timbangan_id' => $value['id'],
				'harga' => $value['harga']
			));
			$this->db->where('id', $value['id']);
			$this->db->update("pks_timbangan", array(
				'berat_potongan' => $value['berat_potongan'],
				'berat_potongan_persen' => $value['berat_potongan_persen'],
				'berat_terima' => $value['berat_terima']
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
			'supplier_id' => $input['supplier_id']['id'],
			'no_rekap' => $input['no_rekap'],
			'tanggal' => $input['tanggal'],
			'item_id' => $input['item_id'],
			'periode_kt_dari' => $input['periode_kt_dari'],
			'periode_kt_sd' => $input['periode_kt_sd'],
			'total_berat_terima' => $input['total_berat_terima'],
			'sub_total' => $input['sub_total'],
			'harga_satuan' => 0,
			'diubah_oleh'=> $input['diubah_oleh'],
			'diubah_tanggal'=> date("Y-m-d H:i:s"),
		);
		$this->db->where('id', $id);
		$this->db->update('prc_rekap_ht', $data);

		// hapus  detail
		$this->db->where('rekap_id', $id);
		$this->db->delete('prc_rekap_dt');

		$details = $input['detail'];
		foreach ($details as $key => $value) {
			$this->db->insert("prc_rekap_dt", array(
				'rekap_id' => $id,
				'pks_timbangan_id' => $value['id'],
				'harga' => $value['harga']
			));
			$this->db->where('id', $value['id']);
			$this->db->update("pks_timbangan", array(
				'berat_potongan' => $value['berat_potongan'],
				'berat_potongan_persen' => $value['berat_potongan_persen'],
				'berat_terima' => $value['berat_terima']
			));
		}

		// $details = $input['details'];
		// foreach ($details as $key => $value) {
		// 	$this->db->insert("prc_rekap_dt", array(
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
		$this->db->delete('prc_rekap_dt');
		$this->db->where('id', $id);
		$this->db->delete('prc_rekap_ht');
		return true;
	}

	public function retrieve_by_id($id)
	{
		$this->db->select('a.*,c.nama as nama_produk');
		$this->db->from('prc_rekap_ht a');
		$this->db->join('prc_kontrak b','a.spk_id=b.id','left');
		$this->db->join('inv_item c','b.produk_id=c.id','left');
		$this->db->where('a.id', $id);
		$res = $this->db->get();
		return $res->row_array();
	}

	public function retrieve_detail($hdid)
	{
		$query="select b.*, a.harga from prc_rekap_dt a inner join pks_timbangan b
		on a.pks_timbangan_id=b.id where a.rekap_id=". $hdid ."";
		$res=$this->db->query($query)->result_array();
		return $res;
	}

	public function print_slip_header(
		$id = null
	) {
		$query    = "SELECT a.*,b.kode_supplier,b.nama_supplier,
				c.no_spk,c.tanggal as tanggal_spk
				FROM prc_rekap_ht a inner join gbm_supplier b
			   on a.supplier_id=b.id  inner join prc_kontrak c on a.spk_id=c.id
			where 1=1 and a.id=" . $id . ";";
		$data     = $this->db->query($query)->row_array();
		return $data;
	}
	public function print_slip_detail(
		$id = null
	) {
		$query    = "SELECT b.*,c.* FROM prc_rekap_ht a inner join
		 prc_rekap_dt b on a.id=b.rekap_id inner join pks_timbangan c
		 on b.pks_timbangan_id=c.id
			where 1=1 and b.rekap_id=" . $id . ";";
		$data     = $this->db->query($query)->result_array();
		return $data;
	}

	public function print_slip_old(
		$id = null
	) {
		$query    = "SELECT a.*,b.qty,c.kode,c.nama,c.satuan
		FROM prc_rekap_ht a inner join prc_rekap_dt b
	   on a.id=b.rekap_id
	   inner join inv_blok c on b.blok=c.id
	   inner join inv_gudang d on a.divisi_id=d.id
			where 1=1 and a.id=" . $id . ";";
		$data     = $this->db->query($query)->result_array();
		return $data;
	}
}
