<?php


class PrcKontrakModel extends CI_Model
{

	public function delete($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$this->db->delete('prc_kontrak');
		return true;
	}

	public function retrieve($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$result = $this->db->get('prc_kontrak', 1);
		return $result->row_array();
	}
	public function retrieve_all()
	{
		$query  = "select a.*,b.nama as nama_lokasi, e.nama as mill,c.nama_supplier as supplier ,d.nama as nama_item from prc_kontrak a 
		left join gbm_organisasi b on a.lokasi_id=b.id 
		left join gbm_supplier c on a.supplier_id=c.id 
		left join inv_item d on a.produk_id=d.id
		left join gbm_organisasi e on a.mill_id=e.id 
		 ";

		return $this->db->query($query)->result_array();;
	}
	public function retrieve_all_by_supplier($supplier_id)
	{
		$query  = "select a.*,b.nama as nama_lokasi, e.nama as mill,c.nama_supplier as supplier ,d.nama as nama_item from prc_kontrak a 
		left join gbm_organisasi b on a.lokasi_id=b.id 
		left join gbm_supplier c on a.supplier_id=c.id 
		left join inv_item d on a.produk_id=d.id
		left join gbm_organisasi e on a.mill_id=e.id 
		where a.supplier_id=".$supplier_id."
		 ";

		return $this->db->query($query)->result_array();;
	}


	public function create(
		$arrdata
	) {


		$no_spk  = $arrdata['no_spk'];
		$no_ref    =  $arrdata['no_ref'];
		$tanggal    =  $arrdata['tanggal'];
		$lokasi_id  = (int) $arrdata['lokasi_id'];
		$supplier_id    =  $arrdata['supplier_id'];
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
		// $ffa    =  $arrdata['ffa'];
		// $mi    =  $arrdata['mi'];
		// $impurities    =  $arrdata['impurities'];
		// $dobi    =  $arrdata['dobi'];
		// $moisture    =  $arrdata['moisture'];
		// $grading    =  $arrdata['grading'];
		// $toleransi    =  $arrdata['toleransi'];
		$keterangan    =  $arrdata['keterangan'];
		$dibuat_oleh    =  $arrdata['dibuat_oleh'];
		$dibuat_tanggal    =  date('Y-m-d H:i:s');
		$diubah_oleh    =  $arrdata['diubah_oleh'];
		$diubah_tanggal    =  date('Y-m-d H:i:s');

		$data = array(
			'lokasi_id'    => $lokasi_id,
			'no_spk' => $no_spk,
			'no_ref' => $no_ref,
			'produk_id' => $produk_id,
			'supplier_id' => $supplier_id,
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
			'keterangan' => $keterangan,
			'dibuat_oleh' => $dibuat_oleh,
			'dibuat_tanggal' => $dibuat_tanggal,
			'diubah_oleh' => $diubah_oleh,
			'diubah_tanggal' => $diubah_tanggal,
		);
		$this->db->insert('prc_kontrak', $data);
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
		$supplier_id    =  $arrdata['supplier_id'];
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
		// $ffa    =  $arrdata['ffa'];
		// $mi    =  $arrdata['mi'];
		// $impurities    =  $arrdata['impurities'];
		// $dobi    =  $arrdata['dobi'];
		// $moisture    =  $arrdata['moisture'];
		// $grading    =  $arrdata['grading'];
		// $toleransi    =  $arrdata['toleransi'];
		$keterangan    =  $arrdata['keterangan'];
		$diubah_oleh    =  $arrdata['diubah_oleh'];
		$diubah_tanggal    =  date('Y-m-d H:i:s');

		$data = array(
			'lokasi_id'    => $lokasi_id,
			'no_spk' => $no_spk,
			'no_ref' => $no_ref,
			'produk_id' => $produk_id,
			'supplier_id' => $supplier_id,
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
			// 'ffa' => $ffa,
			// 'mi' => $mi,
			// 'impurities' => $impurities,
			// 'dobi' => $dobi,
			// 'moisture' => $moisture,
			// 'grading' => $grading,
			// 'toleransi' => $toleransi,
			'keterangan' => $keterangan,
			'diubah_oleh' => $diubah_oleh,
			'diubah_tanggal' => $diubah_tanggal,
		);
		$this->db->where('id', $id);
		$this->db->update('prc_kontrak', $data);
		return true;
	}
}
