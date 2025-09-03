<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

class PksLhp extends BD_Controller
{
	public $user_id;
	public $theCredential;

	function __construct()
	{
		parent::__construct();
		$this->load->model('PksLhpModel');
		$this->load->model('M_DatatablesModel');
		$this->load->model('GbmOrganisasiModel');
		$this->load->library('pdfgenerator');
		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id = $this->user_data->id;
	}

	// endpoint/list :POST
	public function list_post()
	{
		$post = $this->post();
		$query  = "SELECT * from pks_lhp";
		$search = array('satu_inti_hi');
		$where  = null;
		$isWhere = null;
		// $isWhere = 'artikel.deleted_at IS NULL';
		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}

	// endpoint/ :GET
	function index_get($segment_3 = '')
	{
		$id = $segment_3;
		$retrieve = $this->PksLhpModel->retrieve($id);
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	//  endpoint/getall :GET
	function getAll_get()
	{
		$retrieve = $this->PksLhpModel->retrieve_all_jabatan();
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	// endpoint/ :POST
	function index_post()
	{
		$input = $this->post();
		$res = $this->PksLhpModel->create($input);
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'pks_lhp', 'action' => 'new', 'entity_id' => $res);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $input['no_transaksi']), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	// endpoint/ :PUT
	function index_put($segment_3 = '')
	{
		$id = (int)$segment_3;
		$gudang = $this->PksLhpModel->retrieve($id);
		if (empty($gudang)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
		$input = $this->put();
		$res = $this->PksLhpModel->update($gudang['id'], $input);
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'pks_lhp', 'action' => 'edit', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	// endpoint/ :DELETE
	function index_delete($segment_3 = '')
	{
		$id = (int)$segment_3;
		$lhp = $this->PksLhpModel->retrieve($id);
		if (empty($lhp)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
		$retrieve =  $this->PksLhpModel->delete($lhp['id']);
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($retrieve)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id' => $id)), 'entity' => 'pks_lhp', 'action' => 'delete', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => []), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}


	function print_slip_get($segment_3 = '')
	{
		error_reporting(0);

		$id = (int)$segment_3;
		$data = [];

		$query = $this->db->query("SELECT 
			a.*,
			a.id AS id
			FROM pks_lhp a 
			WHERE a.id=" . $id . "")->row_array();


		// $perminta = $this->InvPermintaanBarangModel->print_slip($id);
		$data['data'] = 	$query;

		$html = $this->load->view('PksLhp_laporan', $data, true);

		$filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		echo $html;
	}



	function laporan_rekap_lhp_post()
	{
		// error_reporting(0);

		$data = [];
		if (isset($this->post()['lokasi_id'])) {
			$input = $this->post();
			$input['lokasi'] = $input['lokasi_id'];
			$tipe_laporan = $this->post('tipe_laporan', true);
		} else {
			$input = [
				'lokasi' => 256,
				'tgl_mulai' => '2021-12-5',
				'tgl_akhir' => '2022-12-20',
			];
		}

		// print_r($input); die;

		// $parseTanggal = explode('-', $input['tanggal']);
		// $parseTanggalAkhir = explode('-', $input['tgl_akhir']);

		// $input['tgl_mulai'] = $parseTanggal[0].'-'.$parseTanggal[1].'-1';
		// $input['tgl_akhir'] = $input['tanggal'];

		$gbm_organisasi = $this->GbmOrganisasiModel->retrieve($input['lokasi']);
		$input['lokasi_nama'] = $gbm_organisasi['nama'];
		$data['input'] = $input;
		$data['tipe_laporan'] = $tipe_laporan;

		// $produksi = $this->PksProduksiModel->laporanProduksiHarian( $input );

		$query = "Select 
		*
		FROM pks_lhp  
		WHERE tanggal < '" . $input['tgl_mulai'] . "' 
		order by tanggal Desc limit 1";
		$produksi_awal = $this->db->query($query)->row_array();
		$tbs_kemarin = 0;
		if (!empty($produksi_awal)) {
			$tbs_kemarin = $produksi_awal['dua_restan'];
		}
		$query = "Select *
		FROM pks_lhp  
		WHERE tanggal between '" . $input['tgl_mulai'] . "' 
		and '" . $input['tgl_akhir'] . "' order by tanggal ASC";
		$produksi = $this->db->query($query)->result_array();
		$prod = array();
		foreach ($produksi as $key => $value) {
			$value['tbs_kemarin'] = $tbs_kemarin;
			$tbs_kemarin = $value['dua_restan'];
			$prod[] = $value;
		}
		$data['produksi'] = $prod;
		$data['tipe_laporan'] = $tipe_laporan;

		$html = $this->load->view('PksLhprekap_laporan', $data, true);
		if ($tipe_laporan == 'excel') {
			// $objWriter->save('php://output');
			echo $html;
		} else if($tipe_laporan == 'pdf'){
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		} else {
			echo $html;
		}
	}

	public function proses_hitung_lhp_perhari_post()
	{
		$mill_id = $this->post('mill_id');
		$tgl = $this->post('tanggal');
		$tgl_awal_bulan = substr($tgl, 0, 7) . "-01";
		$tgl_awal_tahun = substr($tgl, 0, 4) . "-01-01";
		$tbs_olah_hari_ini = 0;
		$tbs_olah_restan = 0;
		$tbs_sisa = 0;
		$tbs_masuk = 0;

		// cari produk id TBS,CPO,KERNEL
		$retProdukTBS = $this->db->query("SELECT * FROM `inv_item` where tipe_produk='TBS'")->row_array();
		$retProdukCPO = $this->db->query("SELECT * FROM `inv_item` where tipe_produk='CPO'")->row_array();
		$retProdukKernel = $this->db->query("SELECT * FROM `inv_item` where tipe_produk='PK'")->row_array();
		$produk_id_tbs = $retProdukTBS['id'];
		$produk_id_cpo = $retProdukCPO['id'];
		$produk_id_kernel = $retProdukKernel['id'];

		$tgl_kemarin = date('Y-m-d', strtotime($tgl . ' -1 day'));

		// cari tbs olah seblumya utk mencari tbs_sisa/restan
		$retTBSOlahSebelumnya = $this->db->query("SELECT * FROM `pks_lhp` 
		where tanggal <'" . $tgl . "' 
		order by tanggal Desc limit 1 ")->row_array();
		if (!empty($retTBSOlahSebelumnya)) {

			$tbs_olah_restan = $retTBSOlahSebelumnya['dua_restan'];
		}
		// cari pengolahan tbs per periode terpilih 
		$ret_tbs_olah = $this->db->query("SELECT * FROM `pks_pengolahan_ht` 
		where tanggal ='" . $tgl . "' and mill_id=" . $mill_id . "  
		 order by tanggal   ")->row_array();
		 if ($ret_tbs_olah){
			$tbs_olah_hari_ini = $ret_tbs_olah['tbs_olah'];
		 }else{
			$tbs_olah_hari_ini = 0;
		 }
		 $ret_tbs_olah = $this->db->query("SELECT sum(tbs_olah)as tbs_olah FROM `pks_pengolahan_ht` 
		 where tanggal between '" . $tgl_awal_bulan . "' and '" . $tgl . "' and mill_id=" . $mill_id . "  
		  order by tanggal   ")->row_array();
		  if ($ret_tbs_olah){
			 $tbs_olah_sd_hari_ini = $ret_tbs_olah['tbs_olah'];
		  }else{
			 $tbs_olah_sd_hari_ini = 0;
		  }
		  $ret_tbs_olah = $this->db->query("SELECT  sum(tbs_olah)as tbs_olah FROM `pks_pengolahan_ht` 
		  where tanggal between '" . $tgl_awal_tahun . "' and '" . $tgl . "' and mill_id=" . $mill_id . "  
		   order by tanggal   ")->row_array();
		   if ($ret_tbs_olah){
			  $tbs_olah_sd_bulan_ini = $ret_tbs_olah['tbs_olah'];
		   }else{
			  $tbs_olah_sd_bulan_ini = 0;
		   }


		// $this->set_response(array("status" => "OK", "data" => 	var_dump($ret_tbs_olah)), REST_Controller::HTTP_OK);
		// return;
		// TBS  INTI
		$retTbsTerimaInti = $this->db->query("Select sum(berat_bersih)as kg FROM  pks_timbangan a INNER JOIN gbm_organisasi b ON a.rayon_id=b.id 
		 WHERE a.tipe='INT' AND b.kode LIKE 'SBNE%'and tanggal ='" . $tgl . "'
		  and a.mill_id=" . $mill_id . " and a.item_id=" . $produk_id_tbs . " ")->row_array();
		if (($retTbsTerimaInti)) {
			$tbs_inti_hari_ini = $retTbsTerimaInti['kg']?$retTbsTerimaInti['kg']:0;
		} else {
			$tbs_inti_hari_ini = 0;
		}

		// TBS Plasma
		$retTbsTerimaPlasma = $this->db->query("Select sum(berat_bersih)as kg FROM  pks_timbangan a INNER JOIN gbm_organisasi b ON a.rayon_id=b.id 
				WHERE a.tipe='INT' AND b.kode LIKE 'SBME%'and tanggal ='" . $tgl . "'
				 and a.mill_id=" . $mill_id . " and a.item_id=" . $produk_id_tbs . "				   ")->row_array();
		if (($retTbsTerimaPlasma)) {
			$tbs_plasma_hari_ini = $retTbsTerimaPlasma['kg']?$retTbsTerimaPlasma['kg']:0;
		} else {
			$tbs_plasma_hari_ini = 0;
		}

		// TBS EXT
		$retTbsTerimaExt = $this->db->query("Select sum(berat_bersih)as kg FROM  pks_timbangan  a
		WHERE a.tipe='EXT' and  tanggal ='" . $tgl . "' and a.mill_id=" . $mill_id . " 
		and a.item_id=" . $produk_id_tbs . " ")->row_array();
		if (($retTbsTerimaExt)) {
			$tbs_ext_hari_ini = $retTbsTerimaExt['kg']?$retTbsTerimaExt['kg']:0;
		} else {
			$tbs_ext_hari_ini = 0;
		}
		$total_tbs_hari_ini = $tbs_inti_hari_ini + $tbs_plasma_hari_ini + $tbs_ext_hari_ini;
		$tbs_sisa = $tbs_olah_restan + $total_tbs_hari_ini - $tbs_olah_hari_ini;

		// TBS  INTI sd hari ini
		$retTbsTerimaInti = $this->db->query("Select sum(berat_bersih)as kg FROM  pks_timbangan a INNER JOIN gbm_organisasi b ON a.rayon_id=b.id 
		 WHERE a.tipe='INT' AND b.kode LIKE 'SBNE%' and tanggal between  '" . $tgl_awal_bulan . "' and '" . $tgl . "'
		  and a.mill_id=" . $mill_id . " and a.item_id=" . $produk_id_tbs . " ")->row_array();
		if (!empty($retTbsTerimaInti)) {
			$tbs_inti_sd_hari_ini = $retTbsTerimaInti['kg']?$retTbsTerimaInti['kg']:0;
		} else {
			$tbs_inti_sd_hari_ini = 0;
		}

		// TBS Plasma sd hari ini
		$retTbsTerimaPlasma = $this->db->query("Select sum(berat_bersih)as kg FROM  pks_timbangan a INNER JOIN gbm_organisasi b ON a.rayon_id=b.id 
				WHERE a.tipe='INT' AND b.kode LIKE 'SBME%'and tanggal between  '" . $tgl_awal_bulan . "' and '" . $tgl . "'
				 and a.mill_id=" . $mill_id . " and a.item_id=" . $produk_id_tbs . "				   ")->row_array();
		if (($retTbsTerimaPlasma)) {
			$tbs_plasma_sd_hari_ini = $retTbsTerimaPlasma['kg']? $retTbsTerimaPlasma['kg']:0;
		} else {
			$tbs_plasma_sd_hari_ini = 0;
		}

		// TBS EXT sd hari ini
		$retTbsTerimaExt = $this->db->query("Select sum(berat_bersih)as kg FROM  pks_timbangan a  
		WHERE a.tipe='EXT' and  tanggal between  '" . $tgl_awal_bulan . "' and '" . $tgl . "' and a.mill_id=" . $mill_id . " 
		and a.item_id=" . $produk_id_tbs . " ")->row_array();
		if (($retTbsTerimaExt)) {
			$tbs_ext_sd_hari_ini = $retTbsTerimaExt['kg']?$retTbsTerimaExt['kg']:0;
		} else {
			$tbs_ext_sd_hari_ini = 0;
		}
		$total_tbs_sd_hari_ini = $tbs_inti_sd_hari_ini + $tbs_plasma_sd_hari_ini + $tbs_ext_sd_hari_ini;

		// TBS  INTI sd bulan ini
		$retTbsTerimaInti = $this->db->query("Select sum(berat_bersih)as kg FROM  pks_timbangan a INNER JOIN gbm_organisasi b ON a.rayon_id=b.id 
				WHERE a.tipe='INT' AND b.kode LIKE 'SBNE%' and tanggal between  '" . $tgl_awal_tahun . "' and '" . $tgl . "'
				 and a.mill_id=" . $mill_id . " and a.item_id=" . $produk_id_tbs . " ")->row_array();
		if (($retTbsTerimaInti)) {
			$tbs_inti_sd_bulan_ini = $retTbsTerimaInti['kg']?$retTbsTerimaInti['kg']:0;
		} else {
			$tbs_inti_sd_bulan_ini = 0;
		}

		// TBS Plasma sd bulan ini
		$retTbsTerimaPlasma = $this->db->query("Select sum(berat_bersih)as kg FROM  pks_timbangan a INNER JOIN gbm_organisasi b ON a.rayon_id=b.id 
					   WHERE a.tipe='INT' AND b.kode LIKE 'SBME%' and tanggal between  '" . $tgl_awal_tahun . "' and '" . $tgl . "'
						and a.mill_id=" . $mill_id . " and a.item_id=" . $produk_id_tbs . "				   ")->row_array();
		if (($retTbsTerimaPlasma)) {
			$tbs_plasma_sd_bulan_ini = $retTbsTerimaPlasma['kg']?$retTbsTerimaPlasma['kg']:0;
		} else {
			$tbs_plasma_sd_bulan_ini = 0;
		}

		// TBS EXT sd bulan ini
		$retTbsTerimaExt = $this->db->query("Select sum(berat_bersih)as kg FROM  pks_timbangan a 
			   WHERE a.tipe='EXT' and  tanggal between  '" . $tgl_awal_tahun . "' and '" . $tgl . "' and a.mill_id=" . $mill_id . " 
			   and a.item_id=" . $produk_id_tbs . " ")->row_array();
		if (($retTbsTerimaExt)) {
			$tbs_ext_sd_bulan_ini = $retTbsTerimaExt['kg']?$retTbsTerimaExt['kg']:0;
		} else {
			$tbs_ext_sd_bulan_ini = 0;
		}
		$total_tbs_sd_bulan_ini = $tbs_inti_sd_bulan_ini + $tbs_plasma_sd_bulan_ini + $tbs_ext_sd_bulan_ini;



		//$tgl_besok = date("Y-m-d", strtotime("1 day", strtotime($tgl_awal)));

		// CPO KIRIM
		$retCPOKirim = $this->db->query("SELECT sum(netto_kirim)as kg FROM `pks_timbangan_kirim` 
			where tanggal ='" . $tgl . "' and mill_id=" . $mill_id . " and item_id=" . $produk_id_cpo . "
		   ")->row_array();
		if (($retCPOKirim)) {
			$cpo_kirim_hari_ini = $retCPOKirim['kg'] ? $retCPOKirim['kg'] : 0;
		} else {
			$cpo_kirim_hari_ini = 0;
		}
		// CPO KIRIM
		$retCPOKirim = $this->db->query("SELECT sum(netto_kirim)as kg FROM `pks_timbangan_kirim` 
			where tanggal between '" . $tgl_awal_bulan . "' and '" . $tgl . "' and mill_id=" . $mill_id . " and item_id=" . $produk_id_cpo . "
		   ")->row_array();
		if (($retCPOKirim)) {
			$cpo_kirim_sd_hari_ini = $retCPOKirim['kg'] ? $retCPOKirim['kg'] : 0;
		} else {
			$cpo_kirim_sd_hari_ini = 0;
		}
		// CPO KIRIM
		$retCPOKirim = $this->db->query("SELECT sum(netto_kirim)as kg FROM `pks_timbangan_kirim` 
			where tanggal between '" . $tgl_awal_tahun . "' and '" . $tgl . "' and mill_id=" . $mill_id . " and item_id=" . $produk_id_cpo . "
		   ")->row_array();
		if (($retCPOKirim)) {
			$cpo_kirim_sd_bulan_ini = $retCPOKirim['kg'] ? $retCPOKirim['kg'] : 0;
		} else {
			$cpo_kirim_sd_bulan_ini = 0;
		}
		// cari stok CPO pada sounding kemarin sbg stok awal. 
		$retStokCPOKemarin = $this->db->query("SELECT sum(a.hasil_total) as kg FROM `pks_sounding` a left join pks_tanki b on a.tanki_id=b.id
			where a.tanggal ='" . $tgl_kemarin . "' and a.mill_id=" . $mill_id . " and b.produk_id=" . $produk_id_cpo . "
			group by a.tanggal	
		   ")->row_array();
		if (($retStokCPOKemarin)) {
			$cpo_kg_kemarin = $retStokCPOKemarin['kg'];
		} else {
			$cpo_kg_kemarin = 0;
		}

		// cari stok CPO pada sounding tgl berikutnya. 
		$retStokCPOHariIni = $this->db->query("SELECT a.tanggal, sum(a.hasil_total) as kg FROM `pks_sounding` a left join pks_tanki b on a.tanki_id=b.id
			where a.tanggal ='" . $tgl . "' and a.mill_id=" . $mill_id . " and b.produk_id=" . $produk_id_cpo . "
			group by a.tanggal
			order by a.tanggal ASC limit 1
		   ")->row_array();
		if (!empty($retStokCPOHariIni)) {
			$cpo_kg_hari_ini = $retStokCPOHariIni['kg'];
		} else {
			$cpo_kg_hari_ini = 0;
		}

		// Kernel KIRIM
		$retKernelKirim = $this->db->query("SELECT sum(netto_kirim)as kg FROM `pks_timbangan_kirim` 
			where tanggal ='" . $tgl . "' and mill_id=" . $mill_id . " and item_id=" . $produk_id_kernel . "
		   ")->row_array();
		if (!empty($retKernelKirim)) {
			$kernel_kirim_hari_ini = $retKernelKirim['kg'] ? $retKernelKirim['kg'] : 0;
		} else {
			$kernel_kirim_hari_ini = 0;
		}
		$retKernelKirim = $this->db->query("SELECT sum(netto_kirim)as kg FROM `pks_timbangan_kirim` 
			where tanggal between '" . $tgl_awal_bulan . "' and '" . $tgl . "' and mill_id=" . $mill_id . " and item_id=" . $produk_id_kernel . "
		   ")->row_array();
		if (!empty($retKernelKirim)) {
			$kernel_kirim_sd_hari_ini = $retKernelKirim['kg'] ? $retKernelKirim['kg'] : 0;
		} else {
			$kernel_kirim_sd_hari_ini = 0;
		}
		$retKernelKirim = $this->db->query("SELECT sum(netto_kirim)as kg FROM `pks_timbangan_kirim` 
			where tanggal between '" . $tgl_awal_tahun . "' and '" . $tgl . "' and mill_id=" . $mill_id . " and item_id=" . $produk_id_kernel . "
		   ")->row_array();
		if (!empty($retKernelKirim)) {
			$kernel_kirim_sd_bulan_ini = $retKernelKirim['kg'] ? $retKernelKirim['kg'] : 0;
		} else {
			$kernel_kirim_sd_bulan_ini = 0;
		}
		// cari stok Kernel pada sounding kemarin sbg stok awal. 
		$retStokKernelKemarin = $this->db->query("SELECT sum(a.hasil_sounding) as kg FROM `pks_sounding_kernel` a left join pks_tanki b on a.tanki_id=b.id
			where a.tanggal ='" . $tgl_kemarin . "' and a.mill_id=" . $mill_id . " and b.produk_id=" . $produk_id_kernel . "
			group by a.tanggal
		   ")->row_array();
		if (!empty($retStokKernelKemarin)) {
			$kernel_kg_kemarin = $retStokKernelKemarin['kg'];
		} else {
			$kernel_kg_kemarin = 0;
		}
		// cari stok Kernel pada sounding tgl HARI INI. 
		$retStokKernelHariIni = $this->db->query("SELECT a.tanggal,sum(a.hasil_sounding) as kg FROM `pks_sounding_kernel` a left join pks_tanki b on a.tanki_id=b.id
			where a.tanggal ='" . $tgl . "' and a.mill_id=" . $mill_id . " and b.produk_id=" . $produk_id_kernel . "
			group by a.tanggal
		   ")->row_array();
		if (!empty($retStokKernelHariIni)) {
			$kernel_kg_hari_ini = $retStokKernelHariIni['kg'];
		} else {
			$kernel_kg_hari_ini = 0;
		}
		
		$data = array(

			'mill_id' => $mill_id,
			'tanggal' => $tgl,
			'tbs_olah_hari_ini' => $tbs_olah_hari_ini,
			'tbs_olah_sd_hari_ini' => $tbs_olah_sd_hari_ini,
			'tbs_olah_sd_bulan_ini' => $tbs_olah_sd_bulan_ini,
			'tbs_sisa_kemarin'    => $tbs_olah_restan,
			'tbs_sisa' => $tbs_sisa,
			'tbs_inti_hari_ini' => $tbs_inti_hari_ini,
			'tbs_plasma_hari_ini' => $tbs_plasma_hari_ini,
			'tbs_ext_hari_ini' => $tbs_ext_hari_ini,
			'total_tbs_hari_ini' => $total_tbs_hari_ini,
			'tbs_inti_sd_hari_ini' => $tbs_inti_sd_hari_ini,
			'tbs_plasma_sd_hari_ini' => $tbs_plasma_sd_hari_ini,
			'tbs_ext_sd_hari_ini' => $tbs_ext_sd_hari_ini,
			'total_tbs_sd_hari_ini' => $total_tbs_sd_hari_ini,
			'tbs_inti_sd_bulan_ini' => $tbs_inti_sd_bulan_ini,
			'tbs_plasma_sd_bulan_ini' => $tbs_plasma_sd_bulan_ini,
			'tbs_ext_sd_bulan_ini' => $tbs_ext_sd_bulan_ini,
			'total_tbs_sd_bulan_ini' => $total_tbs_sd_bulan_ini,
			'cpo_kg_olah' => $cpo_kg_hari_ini -  $cpo_kg_kemarin + $cpo_kirim_hari_ini,
			'cpo_kg_awal' => $cpo_kg_kemarin,
			'cpo_kg_akhir' => $cpo_kg_hari_ini,
			'cpo_kg_kirim_hari_ini' => $cpo_kirim_hari_ini,
			'cpo_kg_kirim_sd_hari_ini' => $cpo_kirim_sd_hari_ini,
			'cpo_kg_kirim_sd_bulan_ini' => $cpo_kirim_sd_bulan_ini,
			'kernel_kg_olah' => $kernel_kg_hari_ini -  $kernel_kg_kemarin + $kernel_kirim_hari_ini,
			'kernel_kg_awal' =>  $kernel_kg_kemarin,
			'kernel_kg_akhir' => $kernel_kg_hari_ini,
			'kernel_kg_kirim_hari_ini' => $kernel_kirim_hari_ini,
			'kernel_kg_kirim_sd_hari_ini' => $kernel_kirim_sd_hari_ini,
			'kernel_kg_kirim_sd_bulan_ini' => $kernel_kirim_sd_bulan_ini
		);

		$this->set_response(array("status" => "OK", "data" => 	$data), REST_Controller::HTTP_OK);
	}
}
