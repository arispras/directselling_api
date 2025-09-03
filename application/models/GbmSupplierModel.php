<?php


class GbmSupplierModel extends CI_Model
{



	public function delete($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$this->db->delete('gbm_supplier');
		return true;
	}



	public function retrieve($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$result = $this->db->get('gbm_supplier', 1);
		return $result->row_array();
	}
	public function retrieve_all()
	{
		$this->db->order_by('nama_supplier', 'ASC');
		$this->db->get('gbm_supplier');
		$result = $this->db->get('gbm_supplier');
		return $result->result_array();
	}

	public function retrieve_tbs()
	{
		$this->db->order_by('nama_supplier', 'ASC');
		$this->db->get('gbm_supplier');
		$this->db->where('kelompok_id',5);
		$this->db->or_where('tipe_supplier','KT');
		$result = $this->db->get('gbm_supplier');
		return $result->result_array();
	}
	public function retrieve_kontraktor()
	{
		$this->db->order_by('nama_supplier', 'ASC');
		$this->db->get('gbm_supplier');
		$this->db->where('kelompok_id',3);
		$result = $this->db->get('gbm_supplier');
		return $result->result_array();
	}

	public function create(
		$arrdata
	) {
		$acc_akun_id  = (int) $arrdata['acc_akun_id'];
		$kelompok_id  = (int) $arrdata['kelompok_id'];
		$kode_supplier  =  $arrdata['kode_supplier'];
		$tipe_supplier  =  $arrdata['tipe_supplier'];
		$tipe_pajak  =  $arrdata['tipe_pajak'];
		$nama_supplier    =  $arrdata['nama_supplier'];
		$npwp    =  $arrdata['npwp'];
		$alamat_npwp    =  $arrdata['alamat_npwp'];
		$nama_bank    =  $arrdata['nama_bank'];
		$cabang_bank    =  $arrdata['cabang_bank'];
		$no_rekening    =  $arrdata['no_rekening'];
		$atas_nama    =  $arrdata['atas_nama'];
		$alamat    =  $arrdata['alamat'];
		$no_telpon    =  $arrdata['no_telpon'];
		$contact_person    =  $arrdata['contact_person'];
		$no_hp    =  $arrdata['no_hp'];
		$tempo_pembayaran    =  $arrdata['tempo_pembayaran'];
		$harga_jahit    =  $arrdata['harga_jahit'];
		$diubah_oleh    =  $arrdata['diubah_oleh'];
		$diubah_tanggal    =  date('Y-m-d H:i:s');

		$data = array(
			'acc_akun_id'    => $acc_akun_id,
			'kelompok_id'    => $kelompok_id,
			'kode_supplier'    => $kode_supplier,
			'tipe_supplier'  =>  $tipe_supplier,
			'tipe_pajak'  =>  $tipe_pajak,
			'nama_supplier' => $nama_supplier,
			'npwp' => $npwp,
			'alamat_npwp' => $alamat_npwp,
			'nama_bank' => $nama_bank,
			'cabang_bank' => $cabang_bank,
			'no_rekening' => $no_rekening,
			'atas_nama' => $atas_nama,
			'alamat' => $alamat,
			'no_telpon' => $no_telpon,
			'contact_person' => $contact_person,
			'no_hp' => $no_hp,
			'tempo_pembayaran' => $tempo_pembayaran,
			'harga_jahit' => $harga_jahit,
			'diubah_oleh' => $diubah_oleh,
			'diubah_tanggal' => $diubah_tanggal
		);
		$this->db->insert('gbm_supplier', $data);
		return $this->db->insert_id();
	}

	public function update(
		$id,
		$arrdata
	) {
		$acc_akun_id  = (int) $arrdata['acc_akun_id'];
		$kelompok_id  = (int) $arrdata['kelompok_id'];
		$kode_supplier  =$arrdata['kode_supplier'];
		$tipe_supplier  =  $arrdata['tipe_supplier'];
		$tipe_pajak  =  $arrdata['tipe_pajak'];
		$nama_supplier    =  $arrdata['nama_supplier'];
		$npwp    =  $arrdata['npwp'];
		$alamat_npwp    =  $arrdata['alamat_npwp'];
		$nama_bank    =  $arrdata['nama_bank'];
		$cabang_bank    =  $arrdata['cabang_bank'];
		$no_rekening    =  $arrdata['no_rekening'];
		$atas_nama    =  $arrdata['atas_nama'];
		$alamat    =  $arrdata['alamat'];
		$no_telpon    =  $arrdata['no_telpon'];
		$contact_person    =  $arrdata['contact_person'];
		$no_hp    =  $arrdata['no_hp'];
		$tempo_pembayaran    =  $arrdata['tempo_pembayaran'];
		$harga_jahit    =  $arrdata['harga_jahit'];
		$diubah_oleh    =  $arrdata['diubah_oleh'];
		$diubah_tanggal    =  date('Y-m-d H:i:s');

		$data = array(
			'acc_akun_id'    => $acc_akun_id,
			'kelompok_id'    => $kelompok_id,
			'kode_supplier'    => $kode_supplier,
			'tipe_supplier'  =>  $tipe_supplier,
			'tipe_pajak'  =>  $tipe_pajak,
			'nama_supplier' => $nama_supplier,
			'npwp' => $npwp,
			'alamat_npwp' => $alamat_npwp,
			'nama_bank' => $nama_bank,
			'cabang_bank' => $cabang_bank,
			'no_rekening' => $no_rekening,
			'atas_nama' => $atas_nama,
			'alamat' => $alamat,
			'no_telpon' => $no_telpon,
			'contact_person' => $contact_person,
			'no_hp' => $no_hp,
			'tempo_pembayaran' => $tempo_pembayaran,
			'harga_jahit' => $harga_jahit,
			'diubah_oleh' => $diubah_oleh,
			'diubah_tanggal' => $diubah_tanggal
		);
		$this->db->where('id', $id);
		$this->db->update('gbm_supplier', $data);
		return true;
	}
}
