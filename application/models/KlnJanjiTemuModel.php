<?php


class KlnJanjiTemuModel extends CI_Model
{

	public function delete($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$this->db->delete('kln_janji_temu');
		return true;
	}

	public function retrieve($id)
	{
		$id = (int)$id;
		$this->db->select('kln_janji_temu.*,kln_janji_temu.id as id,kln_pasien.nama as nama_pasien');
		$this->db->from('kln_janji_temu');
		$this->db->join('kln_pasien', 'kln_janji_temu.pasien_id=kln_pasien.id');
		$this->db->where('kln_janji_temu.id', $id);
		$result = $this->db->get();
		return $result->row_array();
	}
	public function retrieve_all()
	{
		$query  = "SELECT a.*,
		b.nama as lokasi,
		c.nama as nama_pasien,c.nip as id_Pasien
		from kln_janji_temu a 
				left join gbm_organisasi b on a.lokasi_id=b.id 
				left join kln_pasien c on a.pasien_id=c.id 
			 ";

		return $this->db->query($query)->result_array();;
	}
	
	public function retrieve_all_by_customer($pasien_id)
	{
		$query  = "select a.*,b.nama as nama_lokasi, e.nama as lokasi,c.nama as nama_asien  from kln_janji_temu a 
		left join gbm_organisasi b on a.lokasi_id=b.id 
		left join kln_pasien c on a.pasien_id=c.id 
		where a.pasien_id=" . $pasien_id . "
		 ";

		return $this->db->query($query)->result_array();;
	}

	public function posting($id, $input)
	{
		$id = (int)$id;
		$data['is_posting'] = 1;
		$data['diposting_tanggal'] = date('Y-m-d H:i:s');
		$data['diposting_oleh'] = $input['diposting_oleh'];
		$this->db->where('id', $id);
		$this->db->update('kln_janji_temu', $data);
		return true;
	}


	public function create(
		$arrdata
	) {

		$no_transaksi  = $arrdata['no_transaksi'];	
		$tanggal    =  $arrdata['tanggal'];
		$lokasi_id  = (int) $arrdata['lokasi_id'];
		$poli_id    =  $arrdata['poli_id'];
		$pasien_id    =  $arrdata['pasien_id'];
		$berat_badan    =  $arrdata['berat_badan'];
		$tinggi_badan    =  $arrdata['tinggi_badan'];
		$tekanan_darah    =  $arrdata['tekanan_darah'];
		$catatan    =  $arrdata['catatan'];
		$dokter_id    =  $arrdata['dokter_id'];
		$perawat_id    =  $arrdata['perawat_id'];
		$biaya_daftar    =  $arrdata['biaya_daftar'];
		$biaya_dokter    =  $arrdata['biaya_dokter'];
		$gejala    =  $arrdata['gejala'];
		$asuransi_id    =  $arrdata['asuransi_id'];
		
		$diubah_oleh    =  $arrdata['diubah_oleh'];
		$diubah_tanggal    =  date('Y-m-d H:i:s');
		$dibuat_oleh    =  $arrdata['dibuat_oleh'];
		$dibuat_tanggal    =  date('Y-m-d H:i:s');

		$data = array(
			'lokasi_id'    => $lokasi_id,
			'no_transaksi' => $no_transaksi,
			'tekanan_darah' => $tekanan_darah,
			'dokter_id' => $dokter_id,
			'pasien_id' => $pasien_id,
			'perawat_id' => $perawat_id,
			'berat_badan' => $berat_badan,
			'tanggal' => $tanggal,
			'tinggi_badan' => $tinggi_badan,
			'catatan' => $catatan,
			'biaya_daftar' => $biaya_daftar,
			'biaya_dokter' => $biaya_dokter,
			'gejala' => $gejala,
			'poli_id' => $poli_id,
			'asuransi_id' => $asuransi_id,
			'diubah_oleh' => $diubah_oleh,
			'diubah_tanggal' => $diubah_tanggal,
			'dibuat_oleh' => $dibuat_oleh,
			'dibuat_tanggal' => $dibuat_tanggal,
		);
		$this->db->insert('kln_janji_temu', $data);
		return $this->db->insert_id();
	}

	public function update(
		$id,
		$arrdata
	) {
		$id = (int)$id;
		$no_transaksi  = $arrdata['no_transaksi'];	
		$tanggal    =  $arrdata['tanggal'];
		$lokasi_id  = (int) $arrdata['lokasi_id'];
		$poli_id    =  $arrdata['poli_id'];
		$pasien_id    =  $arrdata['pasien_id'];
		$berat_badan    =  $arrdata['berat_badan'];
		$tinggi_badan    =  $arrdata['tinggi_badan'];
		$tekanan_darah    =  $arrdata['tekanan_darah'];
		$catatan    =  $arrdata['catatan'];
		$dokter_id    =  $arrdata['dokter_id'];
		$perawat_id    =  $arrdata['perawat_id'];
		$biaya_daftar    =  $arrdata['biaya_daftar'];
		$biaya_dokter    =  $arrdata['biaya_dokter'];
		$gejala    =  $arrdata['gejala'];
		$asuransi_id    =  $arrdata['asuransi_id'];
		
		$diubah_oleh    =  $arrdata['diubah_oleh'];
		$diubah_tanggal    =  date('Y-m-d H:i:s');

		$data = array(
			'lokasi_id'    => $lokasi_id,
			'no_transaksi' => $no_transaksi,
			'tekanan_darah' => $tekanan_darah,
			'dokter_id' => $dokter_id,
			'pasien_id' => $pasien_id,
			'perawat_id' => $perawat_id,
			'berat_badan' => $berat_badan,
			'tanggal' => $tanggal,
			'tinggi_badan' => $tinggi_badan,
			'catatan' => $catatan,
			'biaya_daftar' => $biaya_daftar,
			'biaya_dokter' => $biaya_dokter,
			'gejala' => $gejala,
			'poli_id' => $poli_id,
			'asuransi_id' => $asuransi_id,
			'diubah_oleh' => $diubah_oleh,
			
			'diubah_oleh' => $diubah_oleh,
			'diubah_tanggal' => $diubah_tanggal,
		);
		$this->db->where('id', $id);
		$this->db->update('kln_janji_temu', $data);
		return true;
	}
}
