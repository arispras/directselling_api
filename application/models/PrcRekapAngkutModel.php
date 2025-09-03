<?php

class PrcRekapAngkutModel extends CI_Model
{


	public function create(
		$input = null
	) {
		$data = array(
			'lokasi_id' => $input['lokasi_id']['id'],
			'spk_id' => $input['spk_id']['id'],
			'sls_kontrak_id' => $input['sls_kontrak_id']['id'],
			'supplier_id' => $input['supplier_id']['id'],
			'no_rekap' => $input['no_rekap'],
			'tanggal' => $input['tanggal'],
			'item_id' => $input['item_id'],
			'harga_satuan' => $input['harga_satuan'],
			'total_berat_terima' => $input['total_berat_terima'],
			'adj_berat_terima' => $input['adj_berat_terima'],
			'total_berat_tagihan' => $input['total_berat_tagihan'],
			'periode_kt_dari' => $input['periode_kt_dari'],
			'periode_kt_sd' => $input['periode_kt_sd'],
			'sub_total' => $input['sub_total'],
			'harga_susut_per_kg' => $input['harga_susut_per_kg'],
			'dibuat_oleh' =>  $input['dibuat_oleh'],
			'dibuat_tanggal' =>  date("Y-m-d H:i:s"),
			'diubah_oleh' =>  $input['diubah_oleh'],
			'diubah_tanggal' =>  date("Y-m-d H:i:s"),
			'sumber_timbangan' =>  $input['sumber_timbangan']
		);
		$this->db->insert('prc_rekap_angkut_hd', $data);
		$id = $this->db->insert_id();

		$details = $input['suratjalan'];
		foreach ($details as $key => $value) {

			$this->db->insert("prc_rekap_angkut_dt", array(
				'rekap_id' => $id,
				'sjpp_id' => $value['id'],
				'harga' =>  $value['harga']

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
			'sls_kontrak_id' => $input['sls_kontrak_id']['id'],
			'supplier_id' => $input['supplier_id']['id'],
			'no_rekap' => $input['no_rekap'],
			'tanggal' => $input['tanggal'],
			'item_id' => $input['item_id'],
			'harga_satuan' => $input['harga_satuan'],
			'total_berat_terima' => $input['total_berat_terima'],
			'adj_berat_terima' => $input['adj_berat_terima'],
			'total_berat_tagihan' => $input['total_berat_tagihan'],
			'periode_kt_dari' => $input['periode_kt_dari'],
			'periode_kt_sd' => $input['periode_kt_sd'],
			'sub_total' => $input['sub_total'],
			'harga_susut_per_kg' => $input['harga_susut_per_kg'],
			'dibuat_oleh' =>  $input['dibuat_oleh'],
			'dibuat_tanggal' =>  date("Y-m-d H:i:s"),
			'diubah_oleh' =>  $input['diubah_oleh'],
			'diubah_tanggal' =>  date("Y-m-d H:i:s"),
			'sumber_timbangan' =>  $input['sumber_timbangan']
		);
		$this->db->where('id', $id);
		$this->db->update('prc_rekap_angkut_hd', $data);

		// hapus  detail
		$this->db->where('rekap_id', $id);
		$this->db->delete('prc_rekap_angkut_dt');

		$details = $input['suratjalan'];
		foreach ($details as $key => $value) {

			$this->db->insert("prc_rekap_angkut_dt", array(
				'rekap_id' => $id,
				'sjpp_id' => $value['id'],
				'harga' =>  $value['harga']

			));
		}


		return $id;
	}


	public function delete($id)
	{

		$this->db->where('rekap_id', $id);
		$this->db->delete('prc_rekap_angkut_dt');
		$this->db->where('id', $id);
		$this->db->delete('prc_rekap_angkut_hd');
		return true;
	}

	public function retrieve_by_id($id)
	{
		$this->db->select('a.*,c.nama as nama_produk,b.no_spk');
		$this->db->from('prc_rekap_angkut_hd a');
		$this->db->join('prc_kontrak_angkut b','a.spk_id=b.id','left');
		$this->db->join('inv_item c','b.produk_id=c.id','left');
		$this->db->where('a.id', $id);
		$res = $this->db->get();
		return $res->row_array();
		// $this->db->select('*');
		// $this->db->from('prc_rekap_angkut_hd');
		// $this->db->where('id', $id);
		// $res = $this->db->get();
		// return $res->row_array();
	}

	public function retrieve_detail($hdid)
	{
		$this->db->select('pks_sjpp.*,pks_sjpp.id as sjpp_id, prc_rekap_angkut_dt.id as id,prc_rekap_angkut_dt.harga,pks_timbangan_kirim.*,pks_timbangan_kirim.tanggal as tanggal');
		$this->db->from('prc_rekap_angkut_dt ');
		 $this->db->join('pks_sjpp', 'prc_rekap_angkut_dt.sjpp_id = pks_sjpp.id');
		 $this->db->join('pks_timbangan_kirim', 'pks_sjpp.pks_timbangan_kirim_id = pks_timbangan_kirim.id');
		$this->db->where('rekap_id', $hdid);
		$res = $this->db->get();
		return $res->result_array();
	}


	public function print_slip(
		$id = null
	) {
		$query    = "SELECT a.*,b.qty,c.kode,c.nama,c.satuan
		FROM prc_rekap_angkut_hd a inner join prc_rekap_angkut_dt b
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
			b.kode_supplier,b.nama_supplier,
			c.no_spk,
			c.tanggal as tanggal_spk,
			d.nama as nama_item,a.sumber_timbangan
			FROM prc_rekap_angkut_hd a 
			left join gbm_supplier b on a.supplier_id=b.id 
			left join prc_kontrak_angkut c on a.spk_id=c.id
			left join inv_item d on a.item_id=d.id
			where 1=1 and a.id=" . $id . ";";
		$data     = $this->db->query($query)->row_array();
		return $data;
	}

	public function print_slip_detail(
		$id = null
	) {
		$query    = "SELECT b.harga,c.*		FROM prc_rekap_angkut_hd a 
		left join prc_rekap_angkut_dt b on a.id=b.rekap_id 
		left join pks_timbangan_kirim_sj_vw c on b.sjpp_id=c.id
		where 1=1 and b.rekap_id=" . $id . ";";
		$data     = $this->db->query($query)->result_array();
		return $data;
	}
}
