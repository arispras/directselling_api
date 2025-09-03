<?php


class KlnRadiologiPasienModel extends CI_Model
{

	public function delete($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$this->db->delete('kln_radiologi_pasien');
		return true;
	}

	public function retrieve($id)
	{
		$id = (int)$id;
		$this->db->select('kln_radiologi_pasien.*,kln_radiologi_pasien.id as id,kln_pasien.nama as nama_pasien,kln_radiologi.nama as nama_radiologi');
		$this->db->from('kln_radiologi_pasien');
		$this->db->join('kln_pasien', 'kln_radiologi_pasien.pasien_id=kln_pasien.id');
		$this->db->join('kln_radiologi', 'kln_radiologi_pasien.radiologi_id=kln_radiologi.id');
		$this->db->where('kln_radiologi_pasien.id', $id);
		$result = $this->db->get();
		return $result->row_array();
	}
	public function retrieve_all()
	{
		$query  = "SELECT a.*,
		b.nama as lokasi,
		c.nama as nama_pasien,c.nip as id_Pasien,
		d.nama as nama_radiologi
		from kln_radiologi_pasien a 
				left join gbm_organisasi b on a.lokasi_id=b.id 
				left join kln_pasien c on a.pasien_id=c.id 
				left join kln_radiologi d on a.radiologi_id=d.id 
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
		$this->db->update('kln_radiologi_pasien', $data);
		return true;
	}


	public function create(
		$arrdata
	) {

		$no_transaksi  = $arrdata['no_transaksi'];	
		$tanggal    =  $arrdata['tanggal'];
		$lokasi_id  = (int) $arrdata['lokasi_id'];
		$radiologi_id    =  $arrdata['radiologi_id'];
		$pasien_id    =  $arrdata['pasien_id'];
		$keterangan    =  $arrdata['keterangan'];
		$dokter_id    =  $arrdata['dokter_id'];
		$harga    =  $arrdata['harga'];
		
		$diubah_oleh    =  $arrdata['diubah_oleh'];
		$diubah_tanggal    =  date('Y-m-d H:i:s');
		$dibuat_oleh    =  $arrdata['dibuat_oleh'];
		$dibuat_tanggal    =  date('Y-m-d H:i:s');

		$data = array(
			'lokasi_id'    => $lokasi_id,
			'no_transaksi' => $no_transaksi,
			'dokter_id' => $dokter_id,
			'pasien_id' => $pasien_id,
			'tanggal' => $tanggal,
			'keterangan' => $keterangan,
			'harga' => $harga,
			'radiologi_id' => $radiologi_id,
			'diubah_oleh' => $diubah_oleh,
			'diubah_tanggal' => $diubah_tanggal,
			'dibuat_oleh' => $dibuat_oleh,
			'dibuat_tanggal' => $dibuat_tanggal,
		);
		$this->db->insert('kln_radiologi_pasien', $data);
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
		$radiologi_id    =  $arrdata['radiologi_id'];
		$pasien_id    =  $arrdata['pasien_id'];
		$keterangan    =  $arrdata['keterangan'];
		$dokter_id    =  $arrdata['dokter_id'];
		$harga    =  $arrdata['harga'];
		
		$diubah_oleh    =  $arrdata['diubah_oleh'];
		$diubah_tanggal    =  date('Y-m-d H:i:s');

		$data = array(
			'lokasi_id'    => $lokasi_id,
			'no_transaksi' => $no_transaksi,
			'dokter_id' => $dokter_id,
			'pasien_id' => $pasien_id,
			'tanggal' => $tanggal,
			'keterangan' => $keterangan,
			'harga' => $harga,
			'radiologi_id' => $radiologi_id,			
			'diubah_oleh' => $diubah_oleh,
			'diubah_tanggal' => $diubah_tanggal,
		);
		$this->db->where('id', $id);
		$this->db->update('kln_radiologi_pasien', $data);
		return true;
	}
}
