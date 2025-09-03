<?php

class PksTankiModel extends CI_Model
{


	public function create(
		$input = null
	) {
		$data = array(
			'mill_id' => $input['mill_id']['id'],
			'produk_id' => $input['produk_id']['id'],
			'kode_tanki' => $input['kode_tanki'],
			'nama_tanki' => $input['nama_tanki'],
			'meja_ukur' => $input['meja_ukur'],
			// 'tinggi_meja_ukur' => $input['tinggi_meja_ukur'],
			'kapasitas' => $input['kapasitas'],
			
			'dibuat_oleh'=> $input['dibuat_oleh'],
			'dibuat_tanggal'=> date('Y-m-d H:i:s'),
		);
		$this->db->insert('pks_tanki', $data);
		$id = $this->db->insert_id();

		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("pks_tanki_dt", array(
				'tanki_id' => $id,
				// 'suhu' => $value['suhu'],
				'tinggi_dari' => $value['tinggi_dari'],
				'tinggi_sd' => $value['tinggi_sd'],
				'volume' => $value['volume'],
			));
		}


		return $id;
	}
	public function update(
		$id,
		$input
	) {
		$data = array(
			'mill_id' => $input['mill_id']['id'],
			'produk_id' => $input['produk_id']['id'],
			'kode_tanki' => $input['kode_tanki'],
			'nama_tanki' => $input['nama_tanki'],
			'meja_ukur' => $input['meja_ukur'],
			// 'tinggi_meja_ukur' => $input['tinggi_meja_ukur'],
			'kapasitas' => $input['kapasitas'],

			'diubah_oleh'=> $input['diubah_oleh'],
			'diubah_tanggal'=> date('Y-m-d H:i:s'),
		);
		$this->db->where('id', $id);
		$this->db->update('pks_tanki', $data);

		// hapus  detail
		$this->db->where('tanki_id', $id);
		$this->db->delete('pks_tanki_dt');


		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("pks_tanki_dt", array(
				'tanki_id' => $id,
				'tinggi_dari' => $value['tinggi_dari'],
				'tinggi_sd' => $value['tinggi_sd'],
				'volume' => $value['volume'],
			));
		}

		return $id;
	}


	public function delete($id)
	{

		$this->db->where('tanki_id', $id);
		$this->db->delete('pks_tanki_dt');
		$this->db->where('id', $id);
		$this->db->delete('pks_tanki');
		return true;
	}

	public function retrieve_all()
	{
		$query  = "SELECT 
			a.*,
			b.nama AS mill,
			c.nama AS produk
		FROM pks_tanki a 
		INNER JOIN gbm_organisasi b ON a.mill_id=b.id
		INNER JOIN inv_item c ON a.produk_id=c.id
		";

		return $this->db->query($query)->result_array();;
	}
	public function retrieve_all_detail($id)
	{
		// $query  = "SELECT * FROM pks_tanki_dt";
		$this->db->from('pks_tanki_dt');
		$this->db->where('tanki_id', $id);

		return $this->db->get()->result_array();;
	}


	public function retrieve_by_id($id)
	{
		$this->db->select('*');
		$this->db->from('pks_tanki');
		$this->db->where('id', $id);
		$res = $this->db->get();
		return $res->row_array();
	}

	public function retrieve_detail($hdid)
	{
		$this->db->select('*');
		$this->db->from('pks_tanki_dt');
		// $this->db->join('pks_shift', 'pks_tanki_dt.mill_id = shift.id');
		$this->db->where('tanki_id', $hdid);
		$res = $this->db->get();
		return $res->result_array();
	}


	public function print_slip(
		$id = null
	) {
		$query    = "SELECT a.*,b.qty,c.kode,c.nama,c.satuan
		FROM pks_tanki a inner join pks_tanki_dt b
	   on a.id=b.tanki_id
	   inner join inv_blok c on b.blok=c.id
	   inner join inv_gudang d on a.divisi_id=d.id
			where 1=1 and a.id=" . $id . ";";
		$data     = $this->db->query($query)->result_array();
		return $data;
	}
}
