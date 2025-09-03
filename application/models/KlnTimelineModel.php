<?php

class KlnTimelineModel extends CI_Model
{
	

	public function retrieve_all()
	{
		// $this->db->where('aktif' , 1);
		$this->db->order_by('id', 'ASC');
		$result = $this->db->get('kln_timeline');
		return $result->result_array();
	}


	public function delete($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$this->db->delete('kln_timeline');
		return true;
	}


	public function update($id, $input)
	{
		$id = (int)$id;

		$input['dibuat_tanggal'] = date('Y-m-d H:i:s');

		$ht['pasien_id'] = $input['pasien_id'];
		
		$ht['deskripsi'] = $input['deskripsi'];
		$ht['judul'] = $input['judul'];
		
		$ht['tanggal'] = $input['tanggal'];
		$ht['dibuat_oleh'] = $input['dibuat_oleh'];
		$ht['dibuat_tanggal'] = date("Y-m-d H:i:s");
		$ht['diubah_oleh'] = $input['diubah_oleh'];
		$ht['diubah_tanggal'] = date("Y-m-d H:i:s");

		$this->db->where('id', $id);
		$this->db->update('kln_timeline', $ht);

		// hapus  detail
		// $this->db->where('diagnosa_id', $id);
		// $this->db->delete('kln_timeline_dt');

		// $details = $input['details'];
		// foreach ($details as $key => $value) {
		// 	$this->db->insert("kln_timeline_dt", array(
		// 		'diagnosa_id' => $id,
		// 		'jenis_diagnosa_id' => $value['jenis_diagnosa']['id'],
		// 		'deskripsi' => $value['deskripsi'],
		// 		'rekomendasi' => $value['rekomendasi']
		// 	));
		// }

		return true;
	}

	public function posting($id,	$input)
	{
		$id = (int)$id;
		$data['is_posting'] = 1;
		$data['diposting_tanggal'] = date('Y-m-d H:i:s');
		$data['diposting_oleh'] = $input['diposting_oleh'];
		$this->db->where('id', $id);
		$this->db->update('kln_timeline', $data);	

		return true;
	}
	public function retrieve($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$result = $this->db->get('kln_timeline', 1);
		return $result->row_array();
	}

	public function retrieve_detail($hdid)
	{
	
		$this->db->select('a.*,b.nama as nama_jenis_diagnosa');
		$this->db->from('kln_timeline_dt a');
		$this->db->join('kln_jenis_diagnosa b', 'a.jenis_diagnosa_id = b.id', 'Left');
		$this->db->where('a.diagnosa_id', $hdid);
		$res = $this->db->get();
		return $res->result_array();
	}


	public function create($input)
	{
		
		$ht['pasien_id'] = $input['pasien_id'];	
		$ht['deskripsi'] = $input['deskripsi'];
		$ht['judul'] = $input['judul'];
		$ht['tanggal'] = $input['tanggal'];
		$ht['dibuat_oleh'] = $input['dibuat_oleh'];
		$ht['dibuat_tanggal'] = date("Y-m-d H:i:s");
		$ht['diubah_oleh'] = $input['diubah_oleh'];
		$ht['diubah_tanggal'] = date("Y-m-d H:i:s");

		$this->db->insert('kln_timeline', $ht);
		$id = $this->db->insert_id();

		// $details = $input['details'];
		// foreach ($details as $key => $value) {
		// 	$this->db->insert("kln_timeline_dt", array(
		// 		'diagnosa_id' => $id,
		// 		'jenis_diagnosa_id' => $value['jenis_diagnosa']['id'],
		// 		'deskripsi' => $value['deskripsi'],
		// 		'rekomendasi' => $value['rekomendasi']
		// 	));
		// }

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
		FROM kln_timeline a 
        inner join pks_tanki b on a.tanki_id = b.id
        inner join kln_jenis_diagnosa c on a.produk_id = c.id
	    -- inner join kln_jenis_diagnosa c on b.jenis_diagnosa=c.id
	    -- inner join inv_gudang d on a.rawat_jalan_id=d.id
		
        where 1=1 and a.id=" . $id . ";";
		$data = $this->db->query($query)->row_array();
		return $data;
	}
}
