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


		// $acc_akun_id  = (int) $arrdata['acc_akun_id'];
		// $tipe_pajak  =  $arrdata['tipe_pajak'];
		$kode_customer  =  $arrdata['kode_customer'];
		// $tipe_customer  =  $arrdata['tipe_customer'];
		$nama_customer    =  $arrdata['nama_customer'];
		// $no_npwp    =  $arrdata['no_npwp'];
		// $alamat_npwp    =  $arrdata['alamat_npwp'];
		// $alamat    =  $arrdata['alamat'];
		$no_telpon    =  $arrdata['no_telpon'];
		// $contact_person    =  $arrdata['contact_person'];
		// $no_hp    =  $arrdata['no_hp'];
		// $tempo_pembayaran =$arrdata['tempo_pembayaran'];
		$dibuat_oleh    =  $arrdata['dibuat_oleh'];
		$dibuat_tanggal    =  date('Y-m-d H:i:s');


		$data = array(
		
			'kode_customer'    => $kode_customer,
			// 'tipe_customer'  =>  $tipe_customer,
			'nama_customer' => $nama_customer,
			// 'no_npwp' => $no_npwp,
			// 'alamat_npwp' => $alamat_npwp,
			// 'alamat' => $alamat,
			'no_telpon' => $no_telpon,
			// 'contact_person' => $contact_person,
			// 'no_hp' => $no_hp,
			// 'tempo_pembayaran' => $tempo_pembayaran,
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

		// $acc_akun_id  = (int) $arrdata['acc_akun_id'];
		// $tipe_pajak  =  $arrdata['tipe_pajak'];
		// $kelompok_id  = (int) $arrdata['kelompok_id'];
		$kode_customer  =$arrdata['kode_customer'];
		// $tipe_customer  =  $arrdata['tipe_customer'];
		$nama_customer    =  $arrdata['nama_customer'];
		// $no_npwp    =  $arrdata['no_npwp'];
		// $alamat_npwp    =  $arrdata['alamat_npwp'];
		// $alamat    =  $arrdata['alamat'];
		$no_telpon    =  $arrdata['no_telpon'];
		// $contact_person    =  $arrdata['contact_person'];
		// $no_hp    =  $arrdata['no_hp'];
		// $tempo_pembayaran =$arrdata['tempo_pembayaran'];
		$diubah_oleh    =  $arrdata['diubah_oleh'];
		$diubah_tanggal    =  date('Y-m-d H:i:s');


		$data = array(
		
			// 'kelompok_id'    => $kelompok_id,
			// 'acc_akun_id'    => $acc_akun_id,
			// 'tipe_pajak'  =>  $tipe_pajak,
			'kode_customer'    => $kode_customer,
			// 'tipe_customer'  =>  $tipe_customer,
			'nama_customer' => $nama_customer,
			// 'no_npwp' => $no_npwp,
			// 'alamat_npwp' => $alamat_npwp,
			// 'alamat' => $alamat,
			'no_telpon' => $no_telpon,
			// 'contact_person' => $contact_person,
			// 'no_hp' => $no_hp,
			// 'tempo_pembayaran' => $tempo_pembayaran,
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
