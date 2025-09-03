<?php


class SlsKontrakModel extends CI_Model
{

	public function delete($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$this->db->delete('sls_kontrak');
		return true;
	}

	public function retrieve($id)
	{
		$id = (int)$id;
		$this->db->select('sls_kontrak.*,sls_kontrak.id as id,inv_item.nama as nama_item');
		$this->db->from('sls_kontrak');
		$this->db->join('inv_item', 'sls_kontrak.produk_id=inv_item.id');
		$this->db->where('sls_kontrak.id', $id);
		$result = $this->db->get();
		return $result->row_array();
	}
	public function retrieve_all()
	{
		$query  = "select a.*,b.nama as nama_lokasi, e.nama as mill,c.nama_customer as customer ,d.nama as nama_item from sls_kontrak a 
		left join gbm_organisasi b on a.lokasi_id=b.id 
		left join gbm_customer c on a.customer_id=c.id 
		left join inv_item d on a.produk_id=d.id
		left join gbm_organisasi e on a.mill_id=e.id 
		 ";

		return $this->db->query($query)->result_array();;
	}
	public function retrieve_all_belum_ba_angkut()
	{
		$query  = "select a.*,b.nama as nama_lokasi, e.nama as mill,c.nama_customer as customer ,d.nama as nama_item,
				a.jumlah, IFNULL( T.jumlah_terima,0) from sls_kontrak a 
				left join gbm_organisasi b on a.lokasi_id=b.id 
				left join gbm_customer c on a.customer_id=c.id 
				left join inv_item d on a.produk_id=d.id
				left join gbm_organisasi e on a.mill_id=e.id 
				left JOIN (SELECT SUM(total_berat_terima)AS jumlah_terima,sls_kontrak_id FROM prc_rekap_angkut_hd GROUP BY sls_kontrak_id )T
				ON a.id=T.sls_kontrak_id
				WHERE a.jumlah - IFNULL(T.jumlah_terima,0)>0
				order by a.no_spk; ";

				$resKontrak = $this->db->query($query)->result_array();	
				return $resKontrak;
		// $query  = "select a.*,b.nama as nama_lokasi, e.nama as mill,c.nama_customer as customer ,d.nama as nama_item,a.jumlah from sls_kontrak a 
		// left join gbm_organisasi b on a.lokasi_id=b.id 
		// left join gbm_customer c on a.customer_id=c.id 
		// left join inv_item d on a.produk_id=d.id
		// left join gbm_organisasi e on a.mill_id=e.id 
		//  ";
		// $resKontrak = $this->db->query($query)->result_array();
		// $arrResult = array();
		// foreach ($resKontrak as $key => $k) {
		// 	$queryrekap  = "SELECT SUM(total_berat_terima)AS jumlah_terima FROM prc_rekap_angkut_hd
		// 	 WHERE sls_kontrak_id=" . $k['id'] . "
		//  ";
		// 	$resPekap = $this->db->query($queryrekap)->row_array();
		// 	$jumlahRekap = $resPekap['jumlah_terima'] ? $resPekap['jumlah_terima'] : 0;
		// 	$jumlahKontrak = $k['jumlah'] ? $k['jumlah'] : 0;
		// 	$selisih = $jumlahKontrak - $jumlahRekap;
		// 	if ($selisih > 0) {
		// 		$arrResult[] = $k;
		// 	}
		// }

		// return $arrResult;
	}
	public function retrieve_all_by_customer($customer_id)
	{
		$query  = "select a.*,b.nama as nama_lokasi, e.nama as mill,c.nama_customer as customer ,d.nama as nama_item from sls_kontrak a 
		left join gbm_organisasi b on a.lokasi_id=b.id 
		left join gbm_customer c on a.customer_id=c.id 
		left join inv_item d on a.produk_id=d.id
		left join gbm_organisasi e on a.mill_id=e.id 
		where a.customer_id=" . $customer_id . "
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
		$this->db->update('sls_kontrak', $data);
		return true;
	}


	public function create(
		$arrdata
	) {


		$no_spk  = $arrdata['no_spk'];
		$no_ref    =  $arrdata['no_ref'];
		$tanggal    =  $arrdata['tanggal'];
		$lokasi_id  = (int) $arrdata['lokasi_id'];
		$customer_id    =  $arrdata['customer_id'];
		$alamat_pengiriman    =  $arrdata['alamat_pengiriman'];
		$alamat_penagihan    =  $arrdata['alamat_penagihan'];
		$pic    =  $arrdata['pic'];
		$produk_id    =  $arrdata['produk_id'];
		$jumlah    =  $arrdata['jumlah'];
		$harga_satuan    =  $arrdata['harga_satuan'];
		$sub_total    =  $arrdata['sub_total'];
		$ppn    =  $arrdata['ppn'];
		$pph    =  $arrdata['pph'];
		$total    =  $arrdata['total'];
		$periode_kirim_awal    =  $arrdata['periode_kirim_awal'];
		$periode_kirim_akhir    =  $arrdata['periode_kirim_akhir'];
		$ffa    =  $arrdata['ffa'];
		$mi    =  $arrdata['mi'];
		$impurities    =  $arrdata['impurities'];
		$dobi    =  $arrdata['dobi'];
		$moisture    =  $arrdata['moisture'];
		$grading    =  $arrdata['grading'];
		$toleransi    =  $arrdata['toleransi'];
		$keterangan    =  $arrdata['keterangan'];
		$diubah_oleh    =  $arrdata['diubah_oleh'];
		$diubah_tanggal    =  date('Y-m-d H:i:s');
		$dibuat_oleh    =  $arrdata['dibuat_oleh'];
		$dibuat_tanggal    =  date('Y-m-d H:i:s');

		$data = array(
			'lokasi_id'    => $lokasi_id,
			'no_spk' => $no_spk,
			'no_ref' => $no_ref,
			'produk_id' => $produk_id,
			'customer_id' => $customer_id,
			'alamat_pengiriman' => $alamat_pengiriman,
			'tanggal' => $tanggal,
			'alamat_penagihan' => $alamat_penagihan,
			'pic' => $pic,
			'jumlah' => $jumlah,
			'harga_satuan' => $harga_satuan,
			'sub_total' => $sub_total,
			'ppn' => $ppn,
			'pph' => $pph,
			'total' => $total,
			'periode_kirim_awal' => $periode_kirim_awal,
			'periode_kirim_akhir' => $periode_kirim_akhir,
			'ffa' => $ffa,
			'mi' => $mi,
			'impurities' => $impurities,
			'dobi' => $dobi,
			'moisture' => $moisture,
			'grading' => $grading,
			'toleransi' => $toleransi,
			'keterangan' => $keterangan,
			'diubah_oleh' => $diubah_oleh,
			'diubah_tanggal' => $diubah_tanggal,
			'dibuat_oleh' => $dibuat_oleh,
			'dibuat_tanggal' => $dibuat_tanggal,
		);
		$this->db->insert('sls_kontrak', $data);
		return $this->db->insert_id();
	}

	public function update(
		$id,
		$arrdata
	) {
		$id = (int)$id;
		$no_spk  =  $arrdata['no_spk'];
		$no_ref    =  $arrdata['no_ref'];
		$tanggal    =  $arrdata['tanggal'];
		$lokasi_id  = (int) $arrdata['lokasi_id'];
		$customer_id    =  $arrdata['customer_id'];
		$alamat_pengiriman    =  $arrdata['alamat_pengiriman'];
		$alamat_penagihan    =  $arrdata['alamat_penagihan'];
		$pic    =  $arrdata['pic'];
		$produk_id    =  $arrdata['produk_id'];
		$jumlah    =  $arrdata['jumlah'];
		$harga_satuan    =  $arrdata['harga_satuan'];
		$sub_total    =  $arrdata['sub_total'];
		$ppn    =  $arrdata['ppn'];
		$pph    =  $arrdata['pph'];
		$total    =  $arrdata['total'];
		$periode_kirim_awal    =  $arrdata['periode_kirim_awal'];
		$periode_kirim_akhir    =  $arrdata['periode_kirim_akhir'];
		$ffa    =  $arrdata['ffa'];
		$mi    =  $arrdata['mi'];
		$impurities    =  $arrdata['impurities'];
		$dobi    =  $arrdata['dobi'];
		$moisture    =  $arrdata['moisture'];
		$grading    =  $arrdata['grading'];
		$toleransi    =  $arrdata['toleransi'];
		$keterangan    =  $arrdata['keterangan'];
		$diubah_oleh    =  $arrdata['diubah_oleh'];
		$diubah_tanggal    =  date('Y-m-d H:i:s');

		$data = array(
			'lokasi_id'    => $lokasi_id,
			'no_spk' => $no_spk,
			'no_ref' => $no_ref,
			'produk_id' => $produk_id,
			'customer_id' => $customer_id,
			'alamat_pengiriman' => $alamat_pengiriman,
			'tanggal' => $tanggal,
			'alamat_penagihan' => $alamat_penagihan,
			'pic' => $pic,
			'jumlah' => $jumlah,
			'harga_satuan' => $harga_satuan,
			'sub_total' => $sub_total,
			'ppn' => $ppn,
			'pph' => $pph,
			'total' => $total,
			'periode_kirim_awal' => $periode_kirim_awal,
			'periode_kirim_akhir' => $periode_kirim_akhir,
			'ffa' => $ffa,
			'mi' => $mi,
			'impurities' => $impurities,
			'dobi' => $dobi,
			'moisture' => $moisture,
			'grading' => $grading,
			'toleransi' => $toleransi,
			'keterangan' => $keterangan,
			'diubah_oleh' => $diubah_oleh,
			'diubah_tanggal' => $diubah_tanggal,
		);
		$this->db->where('id', $id);
		$this->db->update('sls_kontrak', $data);
		return true;
	}
}
