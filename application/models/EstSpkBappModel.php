<?php

class EstSpkBappModel extends CI_Model
{


	
	public function create(
		$input = null
	) {
		$data = array(
			'spk_dt_id' => $input['spk_dt_id'],
			'real_hk' => $input['real_hk'],
			'tanggal' => $input['tanggal'],
			'keterangan' => $input['keterangan'],
			'real_volume' => $input['real_volume'],
			'real_harga' => $input['real_harga'],
			
		);
		$this->db->insert('est_spk_bapp', $data);
		$id = $this->db->insert_id();
		$ret = array(
			'spk_dt_id' => $input['spk_dt_id'],
			'real_hk' => $input['real_hk'],
			'tanggal' => $input['tanggal'],
			'keterangan' => $input['keterangan'],
			'real_volume' => $input['real_volume'],
			'real_harga' => $input['real_harga'],
			'id'=> $id
		);
		return $ret;
	}
	public function update(
		$id,
		$input
	) {
		$data = array(
			'spk_dt_id' => $input['spk_dt_id'],
			'real_hk' => $input['real_hk'],
			'tanggal' => $input['tanggal'],
			'keterangan' => $input['keterangan'],
			'real_volume' => $input['real_volume'],
			'real_harga' => $input['real_harga'],
			
		);
		$this->db->where('id', $id);
		$this->db->update('est_spk_bapp', $data);

		$ret = array(
			'spk_dt_id' => $input['spk_dt_id'],
			'real_hk' => $input['real_hk'],
			'tanggal' => $input['tanggal'],
			'keterangan' => $input['keterangan'],
			'real_volume' => $input['real_volume'],
			'real_harga' => $input['real_harga'],
			'id'=> $id
		);
		return $ret;
	}


	public function delete($id)
	{
	
		$this->db->where('id', $id);
		$this->db->delete('est_spk_bapp');
		return true;
	}
	public function posting($id,	$input) 
	{
		$id = (int)$id;
		$data['is_posting'] = 1;
		$data['diposting_tanggal'] = date('Y-m-d H:i:s');
		$data['diposting_oleh'] = $input['diposting_oleh'];
		$this->db->where('id', $id);
		$this->db->update('est_spk_bapp', $data);
		

		return true;
	}
	public function retrieve_by_id($id)
	{
		$this->db->select('*');
		$this->db->from('est_spk_bapp');
		$this->db->where('id', $id);
		$res = $this->db->get();
		return $res->row_array();
	}
	public function retrieve_by_spkdt_id($id)
	{
		$this->db->select('a.*, b.nama AS lokasi, c.nama_supplier AS supplier');
		$this->db->from('est_spk_ht a');
		$this->db->join('gbm_organisasi b', 'a.lokasi_id = b.id');
		$this->db->join('gbm_supplier c', 'a.supplier_id = c.id');
		$this->db->where('a.id', $id);
		$res = $this->db->get();
		return $res->row_array();
	}

	public function retrieve_detail($hdid)
	{
		$this->db->select('a.*, b.nama AS blok, c.nama AS kegiatan');
		$this->db->from('est_spk_dt a');
		$this->db->join('gbm_organisasi b', 'a.blok_id = b.id');
		$this->db->join('acc_kegiatan c', 'a.kegiatan_id = c.id');
		$this->db->where('a.est_spk_id', $hdid);
		$res = $this->db->get();
		return $res->result_array();
	}
	
	public function retrieve_detail_bapp($dtid)
	{
		$this->db->select('*');
		$this->db->from('est_spk_bapp');
		$this->db->where('spk_dt_id', $dtid);
		$res = $this->db->get();
		return $res->result_array();
	}


	public function print_slip(
		$id = null
	) {
		$query    = "Select a.*,b.qty,c.kode,c.nama,c.satuan
		FROM est_spk_ht a inner join est_spk_dt b
	   on a.id=b.est_spk_id
	   inner join inv_blok c on b.blok=c.id
	   inner join inv_gudang d on a.divisi_id=d.id
			where 1=1 and a.id=" . $id . ";";
		$data     = $this->db->query($query)->result_array();
		return $data;
	}
}
