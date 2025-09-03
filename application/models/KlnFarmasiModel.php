<?php

class KlnFarmasiModel extends CI_Model
{


	public function retrieve_all()
	{
		// $this->db->where('aktif' , 1);
		$this->db->order_by('id', 'ASC');
		$result = $this->db->get('kln_farmasi_ht');
		return $result->result_array();
	}


	public function delete($id)
	{
		$id = (int)$id;
		$this->db->where('kln_farmasi_id', $id);
		$this->db->delete('kln_farmasi_dt');
		$this->db->where('id', $id);
		$this->db->delete('kln_farmasi_ht');
		return true;
	}


	public function update($id, $input)
	{
		$id = (int)$id;
		$ht['pasien_id'] = $input['pasien_id']['id'];
		$ht['dokter_id'] = $input['dokter_id']['id'];
		$ht['catatan'] = $input['catatan'];
		$ht['no_transaksi'] = $input['no_transaksi'];
		$ht['tanggal'] = $input['tanggal'];

		$ht['diubah_oleh'] = $input['diubah_oleh'];
		$ht['diubah_tanggal'] = date("Y-m-d H:i:s");


		$this->db->where('id', $id);
		$this->db->update('kln_farmasi_ht', $ht);

		// hapus  detail
		$this->db->where('kln_farmasi_id', $id);
		$this->db->delete('kln_farmasi_dt');

		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("kln_farmasi_dt", array(
				'kln_farmasi_id' => $id,
				'item_id' => $value['item_id']['id'],
				'dosis' => $value['dosis'],
				'instruksi' => $value['instruksi'],
				'qty' => $value['qty'],
				'harga' => $value['harga'],
			));
		}

		return true;
	}

	public function posting($id,	$input)
	{
		$id = (int)$id;
		$data['is_posting'] = 1;
		$data['diposting_tanggal'] = date('Y-m-d H:i:s');
		$data['diposting_oleh'] = $input['diposting_oleh'];
		$this->db->where('id', $id);
		$this->db->update('kln_farmasi', $data);	

		return true;
	}
	public function retrieve($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$result = $this->db->get('kln_farmasi_ht', 1);
		return $result->row_array();
	}

	public function retrieve_detail($hdid)
	{
	
		$this->db->select('a.*,b.nama as nama_item');
		$this->db->from('kln_farmasi_dt a');
		$this->db->join('inv_item b', 'a.item_id = b.id', 'Left');
		$this->db->where('a.kln_farmasi_id', $hdid);
		$res = $this->db->get();
		return $res->result_array();
	}


	public function create($input)
	{
		$ht['pasien_id'] = $input['pasien_id']['id'];
		$ht['dokter_id'] = $input['dokter_id']['id'];
		$ht['catatan'] = $input['catatan'];
		$ht['no_transaksi'] = $input['no_transaksi'];
		$ht['tanggal'] = $input['tanggal'];
		$ht['dibuat_oleh'] = $input['dibuat_oleh'];
		$ht['dibuat_tanggal'] = date("Y-m-d H:i:s");
		$ht['diubah_oleh'] = $input['diubah_oleh'];
		$ht['diubah_tanggal'] = date("Y-m-d H:i:s");

		$this->db->insert('kln_farmasi_ht', $ht);
		$id = $this->db->insert_id();

		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("kln_farmasi_dt", array(
				'kln_farmasi_id' => $id,
				'item_id' => $value['item_id']['id'],
				'dosis' => $value['dosis'],
				'instruksi' => $value['instruksi'],
				'qty' => $value['qty'],
				'harga' => $value['harga'],
				
			));
		}

		return $id;
	}


	public function print_slip(
		$id = null
	) {
		$query = "SELECT 
            c.nama AS nama_produk,
            c.kode AS kode_produk,
            c.*,
            b.*,
            a.*
            -- c.kode,
            -- c.nama,
            -- c.satuan
		FROM kln_farmasi a 
        inner join pks_tanki b on a.tanki_id = b.id
        inner join kln_jenis_diagnosa c on a.produk_id = c.id
	    -- inner join kln_jenis_diagnosa c on b.jenis_diagnosa=c.id
	    -- inner join inv_gudang d on a.rawat_inap_id=d.id
		
        where 1=1 and a.id=" . $id . ";";
		$data = $this->db->query($query)->row_array();
		return $data;
	}
}
