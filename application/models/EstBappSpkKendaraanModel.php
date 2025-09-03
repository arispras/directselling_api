<?php

class EstBappSpkKendaraanModel extends CI_Model
{


	public function create(
		$input = null
	) {
		$data = array(
			'lokasi_id' => $input['lokasi_id']['id'],
			'spk_kendaraan_id' => $input['spk_kendaraan_id']['id'],
			'tanggal' => $input['tanggal'],
			'tanggal_tempo' => $input['tanggal_tempo'],
			'no_bapp' => $input['no_bapp'],
			'periode_mulai' => $input['periode_mulai'],
			'periode_sd' => $input['periode_sd'],
			'pph_persen' => $input['pph_persen'],
			'nilai_invoice' => $input['nilai_invoice'],
			'subtotal' => $input['subtotal'],
			'jml_opt' => $input['jml_opt'],
			'deskripsi' => $input['deskripsi'],
			'is_asistensi' => $input['is_asistensi_unit'],
			'dibuat_oleh'=> $input['dibuat_oleh'],
			'dibuat_tanggal'=> date("Y-m-d H:i:s"),
			'diubah_oleh'=> $input['diubah_oleh'],
			'diubah_tanggal'=> date("Y-m-d H:i:s"),

		);
		$this->db->insert('est_bapp_spk_kendaraan_ht', $data);
		$id = $this->db->insert_id();

		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("est_bapp_spk_kendaraan_dt", array(
				'est_bapp_spk_kendaraan_id' => $id,
				'tanggal_operasi' => $value['tanggal_operasi'],
				'blok_id' => $value['blok_id']['id'],
				'kegiatan_id' => $value['kegiatan_id']['id'],
				'uom_id' => $value['uom_id']['id'],
				'hm_km_awal' => $value['hm_km_awal'],
				'hm_km_akhir' => $value['hm_km_akhir'],
				'jml_hm_km' => $value['jml_hm_km'],
				'harga_satuan' => $value['harga_satuan'],
				'qty' => $value['qty'],
				'jumlah' => $value['jumlah'],
				'keterangan' => $value['keterangan'],
			));
		}
		$details = $input['details_opt'];
		foreach ($details as $key => $value) {

			$this->db->insert("est_bapp_spk_kendaraan_opt", array(
				'est_bapp_spk_kendaraan_id' => $id,
				'tanggal_opt' => $value['tanggal_opt'],
				'kegiatan_id' => $value['kegiatan_id']['id'],
				'afdeling_id' => $value['afdeling_id']['id'],
				'jumlah_opt' => $value['jumlah_opt'],
				'ket' => $value['ket'],
			));
		}
		return $id;
	}
	public function update($id,	$input) 
	{
		$id = (int)$id;
		$data['lokasi_id'] = $input['lokasi_id']['id'];
		$data['spk_kendaraan_id'] = $input['spk_kendaraan_id']['id'];
		$data['tanggal'] = $input['tanggal'];
		$data['tanggal_tempo'] = $input['tanggal_tempo'];
		$data['no_bapp'] = $input['no_bapp'];
		$data['periode_mulai'] = $input['periode_mulai'];
		$data['periode_sd'] = $input['periode_sd'];
		$data['pph_persen'] = $input['pph_persen'];
		$data['nilai_invoice'] = $input['nilai_invoice'];
		$data['subtotal'] = $input['subtotal'];
		$data['jml_opt'] = $input['jml_opt'];
		$data['deskripsi'] = $input['deskripsi'];
		$data['is_asistensi'] =$input['is_asistensi_unit'];
		$data['diubah_oleh'] = $input['diubah_oleh'];
		$data['diubah_tanggal'] = date("Y-m-d H:i:s");

		$this->db->where('id', $id);
		$this->db->update('est_bapp_spk_kendaraan_ht', $data);

		// hapus  detail
		$this->db->where('est_bapp_spk_kendaraan_id', $id);
		$this->db->delete('est_bapp_spk_kendaraan_dt');

		$this->db->where('est_bapp_spk_kendaraan_id', $id);
		$this->db->delete('est_bapp_spk_kendaraan_opt');

		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("est_bapp_spk_kendaraan_dt", array(
				'est_bapp_spk_kendaraan_id' => $id,
				'tanggal_operasi' => $value['tanggal_operasi'],
				'blok_id' => $value['blok_id']['id'],
				'kegiatan_id' => $value['kegiatan_id']['id'],
				'uom_id' => $value['uom_id']['id'],
				'hm_km_awal' => $value['hm_km_awal'],
				'hm_km_akhir' => $value['hm_km_akhir'],
				'jml_hm_km' => $value['jml_hm_km'],
				'harga_satuan' => $value['harga_satuan'],
				'qty' => $value['qty'],
				'jumlah' => $value['jumlah'],
				'keterangan' => $value['keterangan'],
			));
		}

		$details = $input['details_opt'];
		foreach ($details as $key => $value) {

			$this->db->insert("est_bapp_spk_kendaraan_opt", array(
				'est_bapp_spk_kendaraan_id' => $id,
				'tanggal_opt' => $value['tanggal_opt'],
				'kegiatan_id' => $value['kegiatan_id']['id'],
				'afdeling_id' => $value['afdeling_id']['id'],
				'jumlah_opt' => $value['jumlah_opt'],
				'ket' => $value['ket'],
			
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
		$this->db->update('est_bapp_spk_kendaraan_ht', $data);
		

		return true;
	}
	public function delete($id)
	{
		$this->db->where('est_bapp_spk_kendaraan_id', $id);
		$this->db->delete('est_bapp_spk_kendaraan_dt');
		$this->db->where('est_bapp_spk_kendaraan_id', $id);
		$this->db->delete('est_bapp_spk_kendaraan_opt');
		$this->db->where('id', $id);
		$this->db->delete('est_bapp_spk_kendaraan_ht');
		return true;
	}

	public function retrieve_all()
    {
        $this->db->order_by('tanggal', 'ASC');
        $result = $this->db->get('est_bapp_spk_kendaraan_ht');
        return $result->result_array();
    }
	
	public function retrieve_all_detail($id)
	{
		$this->db->from('est_bapp_spk_kendaraan_dt');
		$this->db->where('est_bapp_spk_kendaraan_id', $id);

		return $this->db->get()->result_array();;
	}


	public function retrieve_by_id($id)
	{
		$this->db->select('*');
		$this->db->from('est_bapp_spk_kendaraan_ht');
		$this->db->where('id', $id);
		$res = $this->db->get();
		return $res->row_array();
	}

	public function retrieve_detail($hdid)
	{
		$this->db->select('*');
		$this->db->from('est_bapp_spk_kendaraan_dt');
		$this->db->where('est_bapp_spk_kendaraan_id', $hdid);
		$res = $this->db->get();
		return $res->result_array();
	}
	public function retrieve_detail_opt($hdid)
	{
		$this->db->select('*');
		$this->db->from('est_bapp_spk_kendaraan_opt');
		$this->db->where('est_bapp_spk_kendaraan_id', $hdid);
		$res = $this->db->get();
		return $res->result_array();
	}

	public function print_slip($id)
	{
		$id = (int)$id;

		$query = "SELECT a.*,
		b.nama as lokasi_afd,
		b.nama as lokasi_traksi,
		d.nama as gudang,
		c.nama as karyawan,
		e.nama as lokasi
		FROM est_bapp_spk_kendaraan_ht a 
		LEFT JOIN gbm_organisasi b ON a.dari_lokasi_id=b.id
		LEFT JOIN gbm_organisasi bb ON a.ke_lokasi_id=bb.id
		LEFT JOIN gbm_organisasi d ON a.gudang_id=d.id
		LEFT JOIN gbm_organisasi e ON a.lokasi_id=e.id
		LEFT JOIN karyawan c on a.karyawan_id=c.id  WHERE a.id=" . $id . "";
		$data = $this->db->query($query)->row_array($id);
		return $data;
	}
}



