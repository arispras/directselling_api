<?php


class GbmCustomerModel extends CI_Model
{



	public function delete($id)
	{
		$id = (int)$id;
		$this->db->where('customer_id', $id);
		$this->db->delete('gbm_customer_alamat');
		$this->db->where('id', $id);
		$this->db->delete('gbm_customer');
		return true;
	}



	public function retrieve($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$result = $this->db->get('gbm_customer', 1);
		return $result->row_array();
	}
	public function retrieve_detail($cust_id)
	{
		// $this->db->select('est_spat_dt.*,gbm_organisasi.kode as kode_blok,gbm_organisasi.nama as nama_blok');
		$this->db->select('*');
		$this->db->from('gbm_customer_alamat');
		$this->db->where('customer_id', $cust_id);
		$res = $this->db->get();
		return $res->result_array();
	}

	public function retrieve_all()
	{
		$this->db->order_by('nama_customer', 'ASC');
		$this->db->get('gbm_customer');
		$result = $this->db->get('gbm_customer');
		return $result->result_array();
	}

	public function create(
		$arrdata
	) {
	
		$kode_customer  =  $arrdata['kode_customer'];
		$nama_customer    =  $arrdata['nama_customer'];
		$no_ktp    =  $arrdata['no_ktp'];
		$koordinat    =  $arrdata['koordinat'];
		$alamat    =  $arrdata['alamat'];
		$no_telpon    =  $arrdata['no_telpon'];
		$provinsi_id    =  $arrdata['provinsi_id']['id'];
		$kabupaten_id    =  $arrdata['kabupaten_id']['id'];
		$kecamatan_id    =  $arrdata['kecamatan_id']['id'];
		$kelurahan_id    =  $arrdata['kelurahan_id']['id'];
		$lokasi_id    =  $arrdata['lokasi_id']['id'];
		$dibuat_oleh    =  $arrdata['dibuat_oleh'];
		$dibuat_tanggal    =  date('Y-m-d H:i:s');


		$data = array(
			'lokasi_id' => $lokasi_id,
			'kode_customer'    => $kode_customer,		
			'nama_customer' => $nama_customer,
			'alamat' => $alamat,
			'no_telpon' => $no_telpon,
			'no_ktp' => $no_ktp,
			'provinsi_id'    => $provinsi_id,		
			'kabupaten_id' => $kabupaten_id,
			'kecamatan_id' => $kecamatan_id,
			'kelurahan_id' => $kelurahan_id,
			'koordinat' => $koordinat,
			'dibuat_oleh' => $dibuat_oleh,
			'dibuat_tanggal' => $dibuat_tanggal

		);
		$this->db->insert('gbm_customer', $data);
		$id = $this->db->insert_id();

		$details = $arrdata['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("gbm_customer_alamat", array(
				'customer_id' => $id,
				'contact' => $value['contact'],
				'alamat' => $value['alamat'],
				'nama' => $value['nama'],
				'telp' => $value['telp'],
				'koordinat' => $value['koordinat']
			));
		}

		return $id;
		

	}

	public function update(
		$id,
		$arrdata
	) {

		$kode_customer  =  $arrdata['kode_customer'];
		$nama_customer    =  $arrdata['nama_customer'];
		$no_ktp    =  $arrdata['no_ktp'];
		$koordinat    =  $arrdata['koordinat'];
		$alamat    =  $arrdata['alamat'];
		$no_telpon    =  $arrdata['no_telpon'];
		$provinsi_id    =  $arrdata['provinsi_id']['id'];
		$kabupaten_id    =  $arrdata['kabupaten_id']['id'];
		$kecamatan_id    =  $arrdata['kecamatan_id']['id'];
		$kelurahan_id    =  $arrdata['kelurahan_id']['id'];
		$lokasi_id    =  $arrdata['lokasi_id']['id'];
		$diubah_oleh    =  $arrdata['diubah_oleh'];
		$diubah_tanggal    =  date('Y-m-d H:i:s');

		$data = array(		
			'lokasi_id' => $lokasi_id,
			'kode_customer'    => $kode_customer,		
			'nama_customer' => $nama_customer,
			'alamat' => $alamat,
			'no_telpon' => $no_telpon,
			'no_ktp' => $no_ktp,
			'provinsi_id'    => $provinsi_id,		
			'kabupaten_id' => $kabupaten_id,
			'kecamatan_id' => $kecamatan_id,
			'kelurahan_id' => $kelurahan_id,
			'koordinat' => $koordinat,
			'diubah_oleh' => $diubah_oleh,
			'diubah_tanggal' => $diubah_tanggal
		);
		$this->db->where('id', $id);
		$this->db->update('gbm_customer', $data);

		$this->db->where('customer_id', $id);
		$this->db->delete('gbm_customer_alamat');

		$details = $arrdata['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("gbm_customer_alamat", array(
				'customer_id' => $id,
				'contact' => $value['contact'],
				'alamat' => $value['alamat'],
				'nama' => $value['nama'],
				'telp' => $value['telp'],
				'koordinat' => $value['koordinat']
			));
		}

		return true;
	}
}
