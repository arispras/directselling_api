<?php

class PksTimbanganKirimModel extends CI_Model
{



	public function retrieve_all_kategori()
	{
		// $this->db->where('aktif' , 1);
		$this->db->order_by('id', 'ASC');
		$result = $this->db->get('pks_timbangan_kirim');
		return $result->result_array();
	}


	public function delete($id)
	{
		$id = (int)$id;
		$res = $this->db->query("select * from pks_sjpp where pks_timbangan_kirim_id=" . $id . "")->row_array();
		$this->db->where('id', $res['id']);
		$this->db->delete('pks_sjpp');
		$this->db->where('id', $id);
		$this->db->delete('pks_timbangan_kirim');
		return true;
	}


	public function update($id, $arrdata)
	{
		$id = (int)$id;

		$mill_id  =  $arrdata['mill_id'];
		$sjpp_id  =  $arrdata['sjpp_id'];
		$tipe    =  $arrdata['tipe'];
		$item_id    = empty($arrdata['item_id']) ? null :  $arrdata['item_id'];
		$no_tiket    =  $arrdata['no_tiket'];
		$no_ref    =  $arrdata['no_referensi'];
		$no_kontrak    =  $arrdata['no_kontrak_timbangan'];
		$no_do    =  $arrdata['no_do_timbangan'];
		$tanggal    =  $arrdata['tanggal'];

		$tara_kirim     =  $arrdata['tara_kirim'];
		$bruto_kirim     =  $arrdata['bruto_kirim'];
		$netto_kirim     =  $arrdata['netto_kirim'];
		$customer_id    =  empty($arrdata['customer_id']) ? null :  $arrdata['customer_id'];
		$transportir_id    =  empty($arrdata['transportir_id']) ? null : $arrdata['transportir_id'];
		$instruksi_id    = !isset($arrdata['instruksi_id']) || empty($arrdata['instruksi_id'])  ? null : $arrdata['instruksi_id'];
		$tangki_id    =   empty($arrdata['tangki_id']) ? null : $arrdata['tangki_id'];
		$no_kendaraan     =  $arrdata['no_kendaraan'];
		$nama_supir    =  $arrdata['nama_supir'];
		$no_ktp_sim    =  $arrdata['no_ktp_sim'];
		$no_surat    =  $arrdata['no_surat'];
		$jam_masuk    =  $arrdata['jam_masuk'];
		$jam_keluar    =  $arrdata['jam_keluar'];

		$suhu     =  $arrdata['suhu'];
		$keterangan     =  $arrdata['keterangan'];
		$jumlah_segel    =  $arrdata['jumlah_segel'];
		$no_segel     =  $arrdata['no_segel'];
		$ffa     =  $arrdata['ffa'];
		$moisture     =  $arrdata['moisture'];
		$dirt     =  $arrdata['dirt'];
		$dobi    =  $arrdata['dobi'];
		$uoid    = empty($arrdata['uoid']) ? null : $arrdata['uoid'];
		$segel_1    =  $arrdata['segel_1'];
		$segel_2    =  $arrdata['segel_2'];
		$segel_3    =  $arrdata['segel_3'];
		$diubah_oleh    = empty($arrdata['diubah_oleh']) ? null : $arrdata['diubah_oleh'];
		$diubah_tanggal    =  date('Y-m-d H:i:s');

		$data = array(
			'mill_id'    => $mill_id,
			'tipe' => $tipe,
			'item_id' => $item_id,
			'no_tiket' => $no_tiket,
			'no_referensi' => $no_ref,
			'no_kontrak_timbangan' => $no_kontrak,
			'no_do_timbangan' => $no_do,
			'tanggal' => $tanggal,
			'tara_kirim' => $tara_kirim,
			'bruto_kirim' => $bruto_kirim,
			'netto_kirim' => $netto_kirim,
			'customer_id' => $customer_id,
			'transportir_id' => $transportir_id,
			'tangki_id' => $tangki_id,
			'instruksi_id' => $instruksi_id,
			'no_kendaraan' => $no_kendaraan,
			'nama_supir' => $nama_supir,
			'jam_masuk' => $jam_masuk,
			'jam_keluar' => $jam_keluar,
			'suhu' => $suhu,
			'keterangan' => $keterangan,
			'jumlah_segel' => $jumlah_segel,
			'no_segel' => $no_segel,
			'ffa' => $ffa,
			'moisture' => $moisture,
			'dirt' => $dirt,
			'dobi' => $dobi,
			//'uoid' => $uoid,
			'segel_1' => $segel_1,
			'segel_2' => $segel_2,
			'segel_3' => $segel_3,
			'diubah_oleh' => $diubah_oleh,
			'diubah_tanggal' => $diubah_tanggal,
		);


		$this->db->where('id', $id);
		$this->db->update('pks_timbangan_kirim', $data);


		$dataSjpp = array(
			'mill_id' =>	$mill_id,
			'intruksi_id' =>	$instruksi_id,
			'tanggal' =>	$tanggal,
			'no_surat' => $no_surat, //	
			'no_ktp_sim' => $no_ktp_sim, //
			'pks_timbangan_kirim_id' =>	$id
		);

		$res = $this->db->query("select * from pks_sjpp where pks_timbangan_kirim_id=" . $id . "")->row_array();
		if (empty($res)) {
			$this->db->insert('pks_sjpp', $dataSjpp);
		} else {
			$this->db->where('id', $res['id']);
			$this->db->update('pks_sjpp', $dataSjpp);
		}

		return true;
	}


	public function retrieve($id)
	{
		$id = (int)$id;

		// $this->db->where('id', $id);
		// $result = $this->db->get('pks_timbangan_kirim', 1);
		// return $result->row_array();
		$res = $this->db->query("select a.*,b.id as sjpp_id,b.no_surat,b.no_ktp_sim from pks_timbangan_kirim a left join pks_sjpp b
		on a.id=b.pks_timbangan_kirim_id where a.id=" . $id . "")->row_array();
		return $res;
	}
	public function retrieveByUoid($uoid)
	{
		$this->db->where('uoid', $uoid);
		$result = $this->db->get('pks_timbangan_kirim', 1);
		return $result->row_array();
	}

	public function create($arrdata)
	{

		$mill_id  =  $arrdata['mill_id'];
		$tipe    =  $arrdata['tipe'];
		$item_id    =  $arrdata['item_id'];
		$no_tiket    =  $arrdata['no_tiket'];
		$no_ref    =  $arrdata['no_referensi'];
		$no_kontrak    =  $arrdata['no_kontrak_timbangan'];
		$no_do    =  $arrdata['no_do_timbangan'];
		$no_ktp_sim    =  $arrdata['no_ktp_sim'];
		$tanggal    =  $arrdata['tanggal'];

		$tara_kirim     =  $arrdata['tara_kirim'];
		$bruto_kirim     =  $arrdata['bruto_kirim'];
		$netto_kirim     =  $arrdata['netto_kirim'];
		$customer_id    =  $arrdata['customer_id'];
		$transportir_id    =  $arrdata['transportir_id'];
		$instruksi_id    = !isset($arrdata['instruksi_id']) || empty($arrdata['instruksi_id'])  ? null : $arrdata['instruksi_id'];
		$tangki_id    =  $arrdata['tangki_id'];
		$no_kendaraan     =  $arrdata['no_kendaraan'];
		$nama_supir    =  $arrdata['nama_supir'];
		$jam_masuk    =  $arrdata['jam_masuk'];
		$jam_keluar    =  $arrdata['jam_keluar'];

		$suhu     =  $arrdata['suhu'];
		$keterangan     =  $arrdata['keterangan'];
		$jumlah_segel    =  $arrdata['jumlah_segel'];
		$no_segel     =  $arrdata['no_segel'];
		$ffa     =  $arrdata['ffa'];
		$moisture     =  $arrdata['moisture'];
		$dirt     =  $arrdata['dirt'];
		$dobi    =  $arrdata['dobi'];
		$uoid    =  $arrdata['uoid'];
		$segel_1    =  $arrdata['segel_1'];
		$segel_2    =  $arrdata['segel_2'];
		$segel_3    =  $arrdata['segel_3'];
		$no_surat    =  $arrdata['no_surat'];
		$diubah_oleh    =  $arrdata['diubah_oleh'];
		$diubah_tanggal    =  date('Y-m-d H:i:s');

		$data = array(
			'mill_id'    => $mill_id,
			'tipe' => $tipe,
			'item_id' => $item_id,
			'no_tiket' => $no_tiket,
			'no_referensi' => $no_ref,
			'no_kontrak_timbangan' => $no_kontrak,
			'no_do_timbangan' => $no_do,
			'tanggal' => $tanggal,
			'tara_kirim' => $tara_kirim,
			'bruto_kirim' => $bruto_kirim,
			'netto_kirim' => $netto_kirim,
			'customer_id' => $customer_id,
			'transportir_id' => $transportir_id,
			'tangki_id' => $tangki_id,
			'instruksi_id' => $instruksi_id,
			'no_kendaraan' => $no_kendaraan,
			'nama_supir' => $nama_supir,
			'jam_masuk' => $jam_masuk,
			'jam_keluar' => $jam_keluar,
			'suhu' => $suhu,
			'keterangan' => $keterangan,
			'jumlah_segel' => $jumlah_segel,
			'no_segel' => $no_segel,
			'ffa' => $ffa,
			'moisture' => $moisture,
			'dirt' => $dirt,
			'dobi' => $dobi,
			'uoid' => $uoid,
			'segel_1' => $segel_1,
			'segel_2' => $segel_2,
			'segel_3' => $segel_3,
			'diubah_oleh' => $diubah_oleh,
			'diubah_tanggal' => $diubah_tanggal,
		);
		try {

			//$this->db->trans_start(FALSE);
			$this->db->insert('pks_timbangan_kirim', $data);
			$id_timbang = $this->db->insert_id();
			$dataSjpp = array(
				'mill_id' =>	$mill_id,
				'intruksi_id' =>	$instruksi_id,
				'tanggal' =>	$tanggal,
				'no_surat' => $no_surat, //	
				'no_ktp_sim' => $no_ktp_sim, //
				'pks_timbangan_kirim_id' =>	$id_timbang
			);
			$this->db->insert('pks_sjpp', $dataSjpp);
			//$this->db->trans_complete();
			return $id_timbang;

			// $db_error = $this->db->error();
			// if (!empty($db_error)) {

			//   if(	$db_error['message']){
			// 	return $db_error['message']; 

			//   }


			// }

		} catch (Exception $e) {
			//var_dump($e);exit();
			// this will not catch DB related errors. But it will include them, because this is more general. 
			//log_message('error: ',$e->getMessage());
			return var_dump($e);
		}
		//  $insert=   $this->db->insert('pks_timbangan_kirim', $data);
		//  if(!$insert){

		// 	$error = $this->db->error(); // Has keys 'code' and 'message'
		// 	var_dump($error);exit();

		// }else{

		// 	return $this->db->insert_id();	
		// }

	}

	public function create_from_import($arrdata)
	{

		$mill_id  =  $arrdata['mill_id'];
		$tipe    =  $arrdata['tipe'];
		$item_id    =  $arrdata['item_id'];
		$no_tiket    =  $arrdata['no_tiket'];
		$no_ref    =  $arrdata['no_referensi'];
		$no_kontrak    =  $arrdata['no_kontrak_timbangan'];
		$no_do    =  $arrdata['no_do_timbangan'];
		$no_ktp_sim    =  $arrdata['no_ktp_sim'];
		$tanggal    =  $arrdata['tanggal'];

		$tara_kirim     =  $arrdata['tara_kirim'];
		$bruto_kirim     =  $arrdata['bruto_kirim'];
		$netto_kirim     =  $arrdata['netto_kirim'];
		$customer_id    =  $arrdata['customer_id'];
		$transportir_id    =  $arrdata['transportir_id'];
		$instruksi_id    = !isset($arrdata['instruksi_id']) || empty($arrdata['instruksi_id'])  ? null : $arrdata['instruksi_id'];
		$tangki_id    =  $arrdata['tangki_id'];
		$no_kendaraan     =  $arrdata['no_kendaraan'];
		$nama_supir    =  $arrdata['nama_supir'];
		$jam_masuk    =  $arrdata['jam_masuk'];
		$jam_keluar    =  $arrdata['jam_keluar'];

		$suhu     =  $arrdata['suhu'];
		$keterangan     =  $arrdata['keterangan'];
		$jumlah_segel    =  $arrdata['jumlah_segel'];
		$no_segel     =  $arrdata['no_segel'];
		$ffa     =  $arrdata['ffa'];
		$moisture     =  $arrdata['moisture'];
		$dirt     =  $arrdata['dirt'];
		$dobi    =  $arrdata['dobi'];
		$uoid    =  $arrdata['uoid'];
		$segel_1    =  $arrdata['segel_1'];
		$segel_2    =  $arrdata['segel_2'];
		$segel_3    =  $arrdata['segel_3'];
		// $no_surat    =  $arrdata['no_surat'];
		$no_surat    =  $arrdata['no_tiket'];
		$diubah_oleh    =  $arrdata['diubah_oleh'];
		$diubah_tanggal    =  date('Y-m-d H:i:s');

		$data = array(
			'mill_id'    => $mill_id,
			'tipe' => $tipe,
			'item_id' => $item_id,
			'no_tiket' => $no_tiket,
			'no_referensi' => $no_ref,
			'no_kontrak_timbangan' => $no_kontrak,
			'no_do_timbangan' => $no_do,
			'tanggal' => $tanggal,
			'tara_kirim' => $tara_kirim,
			'bruto_kirim' => $bruto_kirim,
			'netto_kirim' => $netto_kirim,
			'customer_id' => $customer_id,
			'transportir_id' => $transportir_id,
			'tangki_id' => $tangki_id,
			'instruksi_id' => $instruksi_id,
			'no_kendaraan' => $no_kendaraan,
			'nama_supir' => $nama_supir,
			'jam_masuk' => $jam_masuk,
			'jam_keluar' => $jam_keluar,
			'suhu' => $suhu,
			'keterangan' => $keterangan,
			'jumlah_segel' => $jumlah_segel,
			'no_segel' => $no_segel,
			'ffa' => $ffa,
			'moisture' => $moisture,
			'dirt' => $dirt,
			'dobi' => $dobi,
			'uoid' => $uoid,
			'segel_1' => $segel_1,
			'segel_2' => $segel_2,
			'segel_3' => $segel_3,
			'diubah_oleh' => $diubah_oleh,
			'diubah_tanggal' => $diubah_tanggal,
		);
		try {

			//$this->db->trans_start(FALSE);
			$this->db->insert('pks_timbangan_kirim', $data);
			$id_timbang = $this->db->insert_id();
			if ($instruksi_id) {
				$dataSjpp = array(
					'mill_id' =>	$mill_id,
					'intruksi_id' =>	$instruksi_id,
					'tanggal' =>	$tanggal,
					'no_surat' => $no_surat, //	
					'no_ktp_sim' => $no_ktp_sim, //
					'pks_timbangan_kirim_id' =>	$id_timbang
				);
				$this->db->insert('pks_sjpp', $dataSjpp);
			}
			//$this->db->trans_complete();
			return $id_timbang;

			// $db_error = $this->db->error();
			// if (!empty($db_error)) {

			//   if(	$db_error['message']){
			// 	return $db_error['message']; 

			//   }


			// }

		} catch (Exception $e) {
			//var_dump($e);exit();
			// this will not catch DB related errors. But it will include them, because this is more general. 
			//log_message('error: ',$e->getMessage());
			return var_dump($e);
		}
		//  $insert=   $this->db->insert('pks_timbangan_kirim', $data);
		//  if(!$insert){

		// 	$error = $this->db->error(); // Has keys 'code' and 'message'
		// 	var_dump($error);exit();

		// }else{

		// 	return $this->db->insert_id();	
		// }

	}
	public function update_from_import($id, $arrdata)
	{
		$id = (int)$id;

		$mill_id  =  $arrdata['mill_id'];
		$sjpp_id  =  $arrdata['sjpp_id'];
		$tipe    =  $arrdata['tipe'];
		$item_id    = empty($arrdata['item_id']) ? null :  $arrdata['item_id'];
		$no_tiket    =  $arrdata['no_tiket'];
		$no_ref    =  $arrdata['no_referensi'];
		$no_kontrak    =  $arrdata['no_kontrak_timbangan'];
		$no_do    =  $arrdata['no_do_timbangan'];
		$tanggal    =  $arrdata['tanggal'];

		$tara_kirim     =  $arrdata['tara_kirim'];
		$bruto_kirim     =  $arrdata['bruto_kirim'];
		$netto_kirim     =  $arrdata['netto_kirim'];
		$customer_id    =  empty($arrdata['customer_id']) ? null :  $arrdata['customer_id'];
		$transportir_id    =  empty($arrdata['transportir_id']) ? null : $arrdata['transportir_id'];
		$instruksi_id    = !isset($arrdata['instruksi_id']) || empty($arrdata['instruksi_id'])  ? null : $arrdata['instruksi_id'];
		$tangki_id    =   empty($arrdata['tangki_id']) ? null : $arrdata['tangki_id'];
		$no_kendaraan     =  $arrdata['no_kendaraan'];
		$nama_supir    =  $arrdata['nama_supir'];
		$no_ktp_sim    =  $arrdata['no_ktp_sim'];

		// $no_surat    =  $arrdata['no_surat'];
		$no_surat    =  $arrdata['no_tiket'];
		$jam_masuk    =  $arrdata['jam_masuk'];
		$jam_keluar    =  $arrdata['jam_keluar'];

		$suhu     =  $arrdata['suhu'];
		$keterangan     =  $arrdata['keterangan'];
		$jumlah_segel    =  $arrdata['jumlah_segel'];
		$no_segel     =  $arrdata['no_segel'];
		$ffa     =  $arrdata['ffa'];
		$moisture     =  $arrdata['moisture'];
		$dirt     =  $arrdata['dirt'];
		$dobi    =  $arrdata['dobi'];
		$uoid    = empty($arrdata['uoid']) ? null : $arrdata['uoid'];
		$segel_1    =  $arrdata['segel_1'];
		$segel_2    =  $arrdata['segel_2'];
		$segel_3    =  $arrdata['segel_3'];
		$diubah_oleh    = empty($arrdata['diubah_oleh']) ? null : $arrdata['diubah_oleh'];
		$diubah_tanggal    =  date('Y-m-d H:i:s');

		$data = array(
			'mill_id'    => $mill_id,
			'tipe' => $tipe,
			'item_id' => $item_id,
			'no_tiket' => $no_tiket,
			'no_referensi' => $no_ref,
			'no_kontrak_timbangan' => $no_kontrak,
			'no_do_timbangan' => $no_do,
			'tanggal' => $tanggal,
			'tara_kirim' => $tara_kirim,
			'bruto_kirim' => $bruto_kirim,
			'netto_kirim' => $netto_kirim,
			'customer_id' => $customer_id,
			'transportir_id' => $transportir_id,
			'tangki_id' => $tangki_id,
			'instruksi_id' => $instruksi_id,
			'no_kendaraan' => $no_kendaraan,
			'nama_supir' => $nama_supir,
			'jam_masuk' => $jam_masuk,
			'jam_keluar' => $jam_keluar,
			'suhu' => $suhu,
			'keterangan' => $keterangan,
			'jumlah_segel' => $jumlah_segel,
			'no_segel' => $no_segel,
			'ffa' => $ffa,
			'moisture' => $moisture,
			'dirt' => $dirt,
			'dobi' => $dobi,
			'uoid' => $uoid,
			'segel_1' => $segel_1,
			'segel_2' => $segel_2,
			'segel_3' => $segel_3,
			'diubah_oleh' => $diubah_oleh,
			'diubah_tanggal' => $diubah_tanggal,
		);


		$this->db->where('id', $id);
		$this->db->update('pks_timbangan_kirim', $data);

		if ($instruksi_id) {
			$dataSjpp = array(
				'mill_id' =>	$mill_id,
				'intruksi_id' =>	$instruksi_id,
				'tanggal' =>	$tanggal,
				'no_surat' => $no_surat, //	
				'no_ktp_sim' => $no_ktp_sim, //
				'pks_timbangan_kirim_id' =>	$id
			);

			$res = $this->db->query("select * from pks_sjpp where pks_timbangan_kirim_id=" . $id . "")->row_array();
			if (empty($res)) {
				$this->db->insert('pks_sjpp', $dataSjpp);
			} else {
				$this->db->where('id', $res['id']);
				$this->db->update('pks_sjpp', $dataSjpp);
			}
		}

		return true;
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
		FROM pks_timbangan_kirim a 
        inner join pks_tanki b on a.tanki_id = b.id
        inner join inv_item c on a.produk_id = c.id
	    -- inner join inv_item c on b.item=c.id
	    -- inner join inv_gudang d on a.gudang_id=d.id
		
        where 1=1 and a.id=" . $id . ";";
		$data = $this->db->query($query)->row_array();
		return $data;
	}
}
