<?php


class SlsIntruksiModel extends CI_Model
{

	public function delete($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$this->db->delete('sls_intruksi_kirim');
		return true;
	}

	public function retrieve($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$result = $this->db->get('sls_intruksi_kirim', 1);
		return $result->row_array();
	}
	public function retrieve_all()
	{
		$query  = "select a.* , b.nama as mill, c.nama as milll, d.nama as produk,e.customer_id AS cust, f.*, f.id as id_customer, e.no_spk, a.id AS id from sls_intruksi_kirim a 
		left join gbm_organisasi b on a.sales_lokasi_id = b.id 
		left join gbm_organisasi c on a.kepada_lokasi_id = c.id 
		left join inv_item d on a.produk_id = d.id
    LEFT JOIN sls_kontrak e ON a.spk_id = e.id 
		LEFT JOIN gbm_customer f ON e.customer_id = f.id";

		return $this->db->query($query)->result_array();;
	}
	public function retrieve_all_by_kontrak($kontrak_id)
	{
		$query  = "select a.* , b.nama as mill, c.nama as milll, d.nama as produk,e.customer_id AS cust, f.*, f.id as id_customer, e.no_spk, a.id AS id
		 from sls_intruksi_kirim a 
		left join gbm_organisasi b on a.sales_lokasi_id = b.id 
		left join gbm_organisasi c on a.kepada_lokasi_id = c.id 
		left join inv_item d on a.produk_id = d.id
    LEFT JOIN sls_kontrak e ON a.spk_id = e.id 
		LEFT JOIN gbm_customer f ON e.customer_id = f.id
		where a.spk_id=". $kontrak_id ."";

		return $this->db->query($query)->result_array();;
	}
	public function create(
		$arrdata
	) {



		$sales_lokasi_id    =  $arrdata['sales_lokasi_id'];
		$tanggal    =  $arrdata['tanggal'];
		$kepada_lokasi_id  = (int) $arrdata['kepada_lokasi_id'];
		$spk_id    = (int) $arrdata['spk_id'];
		$alamat_pengiriman    =  $arrdata['alamat_pengiriman'];
		$no_transaksi    =  $arrdata['no_transaksi'];
		$pic    =  $arrdata['pic'];
		// $produk_id    =  $arrdata['produk_id'];
		$jumlah    =  $arrdata['jumlah'];
		$periode_kirim_awal    =  $arrdata['periode_kirim_awal'];
		$periode_kirim_akhir    =  $arrdata['periode_kirim_akhir'];
		$keterangan    =  $arrdata['keterangan'];

		$dibuat_oleh    =  $arrdata['dibuat_oleh'];
		$dibuat_tanggal    =  date('Y-m-d H:i:s');
		$diubah_oleh    =  $arrdata['diubah_oleh'];
		$diubah_tanggal    =  date('Y-m-d H:i:s');

		$data = array(
			'kepada_lokasi_id'    => $kepada_lokasi_id,
			'sales_lokasi_id' => $sales_lokasi_id,
			// 'produk_id' => $produk_id,
			'spk_id' => $spk_id,
			'alamat_pengiriman' => $alamat_pengiriman,
			'tanggal' => $tanggal,
			'no_transaksi' => $no_transaksi,
			'pic' => $pic,
			'jumlah' => $jumlah,
			'periode_kirim_awal' => $periode_kirim_awal,
			'periode_kirim_akhir' => $periode_kirim_akhir,
			'keterangan' => $keterangan,
			'dibuat_oleh' => $dibuat_oleh,
			'dibuat_tanggal' => $dibuat_tanggal,
			'diubah_oleh' => $diubah_oleh,
			'diubah_tanggal' => $diubah_tanggal,
		);
		$this->db->insert('sls_intruksi_kirim', $data);
		return $this->db->insert_id();
	}

	public function update(
		$id,
		$arrdata
	) {
		$id = (int)$id;

		$sales_lokasi_id    =  $arrdata['sales_lokasi_id'];
		$kepada_lokasi_id  =  $arrdata['kepada_lokasi_id'];
		$spk_id    =  $arrdata['spk_id'];
		$no_transaksi    =  $arrdata['no_transaksi'];
		$tanggal    =  $arrdata['tanggal'];
		$alamat_pengiriman    =  $arrdata['alamat_pengiriman'];
		$keterangan    =  $arrdata['keterangan'];
		$pic    =  $arrdata['pic'];
		// $produk_id    =  $arrdata['produk_id'];
		$periode_kirim_awal    =  $arrdata['periode_kirim_awal'];
		$periode_kirim_akhir    =  $arrdata['periode_kirim_akhir'];
		$jumlah    =  $arrdata['jumlah'];


		$diubah_oleh    =  $arrdata['diubah_oleh'];
		$diubah_tanggal    =  date('Y-m-d H:i:s');

		$data = array(
			'sales_lokasi_id' => $sales_lokasi_id,
			'kepada_lokasi_id'    => $kepada_lokasi_id,
			'spk_id' => $spk_id,
			'no_transaksi' => $no_transaksi,
			'tanggal' => $tanggal,
			'alamat_pengiriman' => $alamat_pengiriman,
			'keterangan' => $keterangan,
			'pic' => $pic,
			// 'produk_id' => $produk_id,
			'jumlah' => $jumlah,
			'periode_kirim_awal' => $periode_kirim_awal,
			'periode_kirim_akhir' => $periode_kirim_akhir,



			'diubah_oleh' => $diubah_oleh,
			'diubah_tanggal' => $diubah_tanggal,
		);
		$this->db->where('id', $id);
		$this->db->update('sls_intruksi_kirim', $data);
		return true;
	}
}
