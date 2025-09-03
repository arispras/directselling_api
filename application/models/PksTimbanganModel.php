<?php


class PksTimbanganModel extends CI_Model
{

	public function delete($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$this->db->delete('pks_timbangan');
		return true;
	}

	public function get_timbangan_internal_blm_spb()
	{
		$query  = "select a.*,b.nama as mill,c.kode as kode_rayon,c.nama as nama_rayon,
		d.kode as kode_estate,d.nama as nama_estate 
		from pks_timbangan a 
		left join gbm_organisasi b on a.mill_id=b.id  
		left join gbm_organisasi c on a.rayon_id=c.id
		left join gbm_organisasi d on c.parent_id=d.id
		where a.tipe='INT' and a.id not in( select pks_timbangan_id from est_spat_ht)
		and a.tanggal>'2023-04-30' order by a.no_tiket; 
		 ";

		return $this->db->query($query)->result_array();;
	}
	public function get_timbangan_external_blm_rekap($supp_id,$tgl_mulai,$tgl_sd)
	{
		$query  = "select a.*,b.nama as mill,c.kode_supplier as kode_supplier,c.nama_supplier as nama_supplier
		from pks_timbangan a 
		left join gbm_organisasi b on a.mill_id=b.id  
		left join gbm_supplier c on a.supplier_id=c.id
		where a.tipe='EXT' and a.supplier_id=" . $supp_id . "
		and tanggal between '".$tgl_mulai ."' and '".$tgl_sd."'
		and a.id not in( select pks_timbangan_id from prc_rekap_dt)
		order by a.tanggal,a.no_tiket; ";

		$retrieveTimbangan = $this->db->query($query)->result_array();
		$result=array();
		foreach ($retrieveTimbangan as $key => $m) {
			$retrieveHarga = $this->db->query(
				"select * from pks_harga_tbs where
				tanggal_efektif<= '" . $m['tanggal']  . "' and supplier_id =" . $supp_id . "
				order by tanggal_efektif desc limit 1 "
			)->row_array();
			$harga=0;
			if ($retrieveHarga) {
				$harga = $retrieveHarga['harga'] ? $retrieveHarga['harga'] : 0;
			}
			$m['harga']=$harga;
			$result[]=$m;
		}
		return $result;
	}


	public function retrieve($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$result = $this->db->get('pks_timbangan', 1);
		return $result->row_array();
	}
	public function retrieveByUoid($uoid)
	{
		$this->db->where('uoid', $uoid);
		$result = $this->db->get('pks_timbangan', 1);
		return $result->row_array();
	}
	public function retrieve_all()
	{
		$query  = "select a.*,b.nama as mill,c.nama_supplier from pks_timbangan a left join gbm_organisasi b on a.mill_id=b.id left join gbm_supplier c on a.supplier_id=c.id ";

		return $this->db->query($query)->result_array();;
	}

	public function create(
		$arrdata
	) {

		$mill_id  = (!$arrdata['mill_id'] || $arrdata['mill_id'] == "") ? null : $arrdata['mill_id'];
		$estate_id  = (!isset($arrdata['estate_id']) || empty($arrdata['estate_id']) || !$arrdata['estate_id'] || $arrdata['estate_id'] == "") ? null : $arrdata['estate_id'];
		$rayon_id    = (!isset($arrdata['rayon_id']) || empty($arrdata['rayon_id']) || !$arrdata['rayon_id'] || $arrdata['rayon_id'] == "") ? null : $arrdata['rayon_id'];
		// $divisi_id    = ( !$arrdata['divisi_id']||$arrdata['divisi_id']=="" )?null:$arrdata['divisi_id'];
		$tipe    =  $arrdata['tipe'];
		$item_id    =  (!$arrdata['item_id'] || $arrdata['item_id'] == "") ? null : $arrdata['item_id'];
		$no_tiket    =  $arrdata['no_tiket'];
		$no_spat    =  $arrdata['no_spat'];
		$tanggal    =  $arrdata['tanggal'];
		$berat_bersih    =  $arrdata['berat_bersih'];
		$berat_kosong    =  $arrdata['berat_kosong'];
		$berat_isi    =  $arrdata['berat_isi'];
		$berat_potongan    =  $arrdata['berat_potongan'];
		$berat_potongan_persen    =  $arrdata['berat_potongan_persen'];
		$berat_terima    =  $arrdata['berat_terima'];
		$jumlah_item    =  $arrdata['jumlah_item'];
		$jumlah_berondolan    =  $arrdata['jumlah_berondolan'];
		$supplier_id    =  (!isset($arrdata['supplier_id']) || !$arrdata['supplier_id'] || $arrdata['supplier_id'] == "") ? null : $arrdata['supplier_id'];
		$transportir_id    =  (empty($arrdata['transportir_id']) || !($arrdata['transportir_id']) || $arrdata['transportir_id'] == "") ? null : $arrdata['transportir_id'];
		$no_plat    =  $arrdata['no_plat'];
		$nama_supir    =  $arrdata['nama_supir'];
		$jam_masuk    =  $arrdata['jam_masuk'];
		$jam_keluar    =  $arrdata['jam_keluar'];
		$uoid    = empty($arrdata['uoid']) ? null : $arrdata['uoid'];
		$diubah_oleh    =  $arrdata['diubah_oleh'];
		$diubah_tanggal    =  date('Y-m-d H:i:s');
		$blok    =  $arrdata['blok'];
		$keterangan    =   $arrdata['keterangan'];


		$data = array(
			'mill_id'    => $mill_id,
			// 'divisi_id'    => $divisi_id,
			'estate_id' => $estate_id,
			'rayon_id' => $rayon_id,
			'blok' => $blok,
			'keterangan' => $keterangan,
			'tipe' => $tipe,
			'item_id' => $item_id,
			'no_tiket' => $no_tiket,
			'no_spat' => $no_spat,
			'tanggal' => $tanggal,
			'berat_bersih' => $berat_bersih,
			'berat_kosong' => $berat_kosong,
			'berat_isi' => $berat_isi,
			'berat_potongan' => $berat_potongan,
			'berat_potongan_persen' => $berat_potongan_persen,
			'berat_terima' => $berat_terima,
			'jumlah_item' => $jumlah_item,
			'jumlah_berondolan' => $jumlah_berondolan,
			'supplier_id' => $supplier_id,
			'transportir_id' => $transportir_id,
			'no_plat' => $no_plat,
			'nama_supir' => $nama_supir,
			'jam_masuk' => $jam_masuk,
			'jam_keluar' => $jam_keluar,
			'uoid' => $uoid,
			'diubah_oleh' => $diubah_oleh,
			'diubah_tanggal' => $diubah_tanggal,
		);
		$this->db->insert('pks_timbangan', $data);
		return $this->db->insert_id();
	}

	public function update(
		$id,
		$arrdata
	) {

		$id = (int)$id;
		$mill_id  = (!$arrdata['mill_id'] || $arrdata['mill_id'] == "") ? null : $arrdata['mill_id'];
		$estate_id  = (!isset($arrdata['estate_id'])  || empty($arrdata['estate_id']) || !$arrdata['estate_id'] || $arrdata['estate_id'] == "") ? null : $arrdata['estate_id'];
		$rayon_id    = (!isset($arrdata['rayon_id']) || empty($arrdata['rayon_id']) || !$arrdata['rayon_id'] || $arrdata['rayon_id'] == "") ? null : $arrdata['rayon_id'];
		// $divisi_id    = ( !$arrdata['divisi_id']||$arrdata['divisi_id']=="" )?null:$arrdata['divisi_id'];
		$tipe    =  $arrdata['tipe'];
		$item_id    =  (!$arrdata['item_id'] || $arrdata['item_id'] == "") ? null : $arrdata['item_id'];
		$no_tiket    =  $arrdata['no_tiket'];
		$no_spat    =  $arrdata['no_spat'];
		$tanggal    =  $arrdata['tanggal'];
		$berat_bersih    =  $arrdata['berat_bersih'];
		$berat_kosong    =  $arrdata['berat_kosong'];
		$berat_isi    =  $arrdata['berat_isi'];
		$berat_potongan    =  $arrdata['berat_potongan'];
		$berat_potongan_persen    =  $arrdata['berat_potongan_persen'];
		$berat_terima    =  $arrdata['berat_terima'];
		$jumlah_item    =  $arrdata['jumlah_item'];
		$jumlah_berondolan    =  $arrdata['jumlah_berondolan'];
		$supplier_id    =  (!isset($arrdata['supplier_id']) || empty($arrdata['supplier_id']) || !$arrdata['supplier_id'] || $arrdata['supplier_id'] == "") ? null : $arrdata['supplier_id'];
		$transportir_id    =  (empty($arrdata['transportir_id']) || !($arrdata['transportir_id']) || $arrdata['transportir_id'] == "") ? null : $arrdata['transportir_id'];
		$no_plat    =  $arrdata['no_plat'];
		$nama_supir    =  $arrdata['nama_supir'];
		$jam_masuk    =  $arrdata['jam_masuk'];
		$jam_keluar    =  $arrdata['jam_keluar'];
		$uoid    = empty($arrdata['uoid']) ? null : $arrdata['uoid'];
		$diubah_oleh    =  $arrdata['diubah_oleh'];
		$diubah_tanggal    =  date('Y-m-d H:i:s');
		$blok    =  $arrdata['blok'];
		$keterangan    =   $arrdata['keterangan'];


		$data = array(
			'mill_id'    => $mill_id,
			// 'divisi_id'    => $divisi_id,
			'estate_id' => $estate_id,
			'rayon_id' => $rayon_id,
			'blok' => $blok,
			'keterangan' => $keterangan,
			'tipe' => $tipe,
			'item_id' => $item_id,
			'no_tiket' => $no_tiket,
			'no_spat' => $no_spat,
			'tanggal' => $tanggal,
			'berat_bersih' => $berat_bersih,
			'berat_kosong' => $berat_kosong,
			'berat_isi' => $berat_isi,
			'berat_potongan' => $berat_potongan,
			'berat_potongan_persen' => $berat_potongan_persen,
			'berat_terima' => $berat_terima,
			'jumlah_item' => $jumlah_item,
			'jumlah_berondolan' => $jumlah_berondolan,
			'supplier_id' => $supplier_id,
			'transportir_id' => $transportir_id,
			'no_plat' => $no_plat,
			'nama_supir' => $nama_supir,
			'jam_masuk' => $jam_masuk,
			'jam_keluar' => $jam_keluar,
			'uoid' => $uoid,
			'diubah_oleh' => $diubah_oleh,
			'diubah_tanggal' => $diubah_tanggal,
		);
		$this->db->where('id', $id);
		$this->db->update('pks_timbangan', $data);
		return true;
	}


	public function retrieve_where($table, $opt = '', $join = '', $select = '')
	{
		$this->db->from($table);
		if (!empty($opt)) {
			$this->db->where($opt);
		}
		if (!empty($select)) {
			$this->db->select($select);
		}
		if (is_array($join)) {
			foreach ($join as $key => $val) {
				$this->db->join($key, $val);
			}
		}
		return $this->db->get()->row_array();
	}
	public function retrieve_result_where($table, $opt = '', $join = '', $select = '')
	{
		$this->db->from($table);
		if (!empty($opt)) {
			$this->db->where($opt);
		}
		if (!empty($select)) {
			$this->db->select($select);
		}
		if (is_array($join)) {
			foreach ($join as $key => $val) {
				$this->db->join($key, $val);
			}
		}
		return $this->db->get()->result_array();
	}
}
