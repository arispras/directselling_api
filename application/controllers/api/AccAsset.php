<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;
use Restserver\Libraries\REST_Controller;

class AccAsset extends BD_Controller
{
	public $user_id;
	public $theCredential;

	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('AccAssetModel');
		$this->load->model('AccPeriodeAkuntingModel');
		$this->load->model('AccJurnalModel');
		$this->load->model('M_DatatablesModel');
		$this->load->library('pdfgenerator');
		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id = $this->user_data->id;
	}

	// endpoint/list :POST
	public function list_post()
	{
		$post = $this->post();
		$query  = "SELECT 
		a.*,
		b.nama AS lokasi,
		bb.nama AS posisi_asset,
		c.nama AS akun_penyusutan,
		d.nama AS asset_tipe,
		e.user_full_name AS dibuat,
		f.user_full_name AS diubah
		FROM acc_asset a
		LEFT JOIN gbm_organisasi b ON a.lokasi_id=b.id
		LEFT JOIN gbm_organisasi bb ON a.posisi_asset_id=bb.id
		LEFT JOIN acc_akun c ON a.akun_penyusutan_id=c.id
		LEFT JOIN acc_asset_tipe d ON a.asset_tipe_id=d.id	
		LEFT JOIN fwk_users e ON a.dibuat_oleh = e.id
		LEFT JOIN fwk_users f ON a.diubah_oleh = f.id";

		$search = array('b.nama', 'bb.nama', 'c.nama', 'd.nama', 'a.nama', 'a.kode');
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
		$retrieve = $this->AccAssetModel->retrieve($id);
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	//  endpoint/getall :GET
	function getAll_get()
	{
		$retrieve = $this->AccAssetModel->retrieve_all_kategori();
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	// endpoint/ :POST
	function index_post()
	{
		$data = $this->post();
		$data['dibuat_oleh'] = $this->user_id;
		$data['diubah_oleh'] = $this->user_id;
		$retrieve = $this->AccAssetModel->create($data);
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
	}

	// endpoint/ :PUT
	function index_put($segment_3 = '')
	{
		$id = (int)$segment_3;
		$gudang = $this->AccAssetModel->retrieve($id);
		if (empty($gudang)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
		$data = $this->put();
		$data['diubah_oleh'] = $this->user_id;
		$retrieve = $this->AccAssetModel->update($gudang['id'], $data);
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
	}

	// endpoint/ :DELETE
	function index_delete($segment_3 = '')
	{
		$id = (int)$segment_3;
		$gudang = $this->AccAssetModel->retrieve($id);
		if (empty($gudang)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
		$retrieve =  $this->AccAssetModel->delete($gudang['id']);
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
	}

	public function start_proses_asset_post()
	{
		$id = $this->post('id');
		$periodeAkunting = $this->AccPeriodeAkuntingModel->retrieve($id);
		if (empty($periodeAkunting)) {
			$this->set_response(array("status" => "NOT OK", "data" => 'null'), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		// CEK PERIODE SDH ADA ATAU SDH CLOSE//
		$chk = cek_periode_by_id($periodeAkunting['id']);
		if ($chk['status'] == false) {
			$this->set_response(array("status" => "NOT OK", "data" => $chk['message']), REST_Controller::HTTP_OK);
			return;
		}
		//==============//
		$lokasi_id = $periodeAkunting['lokasi_id'];
		$lokasi = $this->db->query("SELECT * FROM gbm_organisasi 
								where id=" . $lokasi_id . "")->row_array();

		$this->start_proses_asset($id);
		// if ($lokasi['tipe'] == 'ESTATE') {
		// 	$this->start_proses_alokasi_estate($id);
		// } elseif ($lokasi['tipe'] == 'MILL') {
		// 	$this->start_proses_alokasi_mill($id);
		// }
	}
	public function start_proses_asset($id)
	{
		// $id = $this->post('id');
		$periodeAkunting = $this->AccPeriodeAkuntingModel->retrieve($id);
		if (empty($periodeAkunting)) {
			$this->set_response(array("status" => "NOT OK", "data" => 'null'), REST_Controller::HTTP_NOT_FOUND);
			return;
		}

		$lokasi_id = $periodeAkunting['lokasi_id'];
		$d1 = $periodeAkunting['tgl_awal'];
		$d2 = $periodeAkunting['tgl_akhir'];
		$last_date_in_periode = $d2;
		// $res_transit_akun = $this->db->query("SELECT * from acc_auto_jurnal 
		// where kode='TRAKSI_TRANSIT_AKUN'")->row_array();
		// if (empty($res_transit_akun)) {
		// 	$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
		// 	return;
		// }
		// $res_kendaraan_dialokasi_akun = $this->db->query("SELECT * from acc_auto_jurnal 
		// where kode='TRAKSI_DIALOKASI_AKUN'")->row_array();
		// if (empty($res_kendaraan_dialokasi_akun)) {
		// 	$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
		// 	return;
		// }
		// $kendaraan_dialokasi_akun = $res_kendaraan_dialokasi_akun['acc_akun_id'];

		/* Hapus dulu jurnal alokasi traksi jika sudah ada pada bulan tsb.*/
		$q = "delete from acc_jurnal_dt where jurnal_id in(select id from acc_jurnal_ht
                 where tanggal='" . $last_date_in_periode . "' and  modul='DEP_ASSET'
				 and lokasi_id =" . $lokasi_id . ")";
		$this->db->query($q);
		$q = "delete from acc_jurnal_ht  
                 where tanggal='" . $last_date_in_periode . "' and  modul='DEP_ASSET'
				 and lokasi_id =" . $lokasi_id . "";
		$this->db->query($q);

		/* Get InterUnit Akun /HUb RK */
		$retrieve_inter_akun = $this->db->query("SELECT * FROM acc_inter_unit
			where tipe='INTER' ")->result_array();
		$akun_inter = array();
		foreach ($retrieve_inter_akun as $key => $akun) {
			$akun_inter[$akun['lokasi_id']][$akun['lokasi_id_2']] = $akun['acc_akun_id'];
		}

		$qAsset = "SELECT * from acc_asset
		where lokasi_id =" . $lokasi_id . "
		 and status='AKTIF'
		and tgl_mulai_pakai <='" . $d2 . "'";
		$resAsset = $this->db->query($qAsset)->result_array();
		if ($resAsset) {
			$this->load->library('Autonumber');
			$no_jurnal = $this->autonumber->jurnal_auto($lokasi_id, $last_date_in_periode, 'DEP_ASSET');
			$dataH = array(
				'no_jurnal' => $no_jurnal,
				'lokasi_id' => $lokasi_id,
				'tanggal' => $last_date_in_periode,
				'no_ref' => '',
				'ref_id' => null,
				'tipe_jurnal' => 'AUTO',
				'modul' => 'DEP_ASSET',
				'keterangan' => 'DEP_ASSET:' . $periodeAkunting['nama'],
				'is_posting' => 1,
				'diposting_oleh' => $this->user_id
			);
			$id_header = $this->AccJurnalModel->create_header($dataH);
			foreach ($resAsset as $AssetAcc) {
				$asset_id = $AssetAcc['id'];
				$nilaiAsset = $AssetAcc['nilai_asset'];
				$nilaiResidu = $AssetAcc['nilai_residu'];
				$bulanPenyusutan = $AssetAcc['lama_bulan_penyusutan'];
				$akun_biaya_id = $AssetAcc['akun_biaya_id'];
				$akun_akumulasi = $AssetAcc['akun_akumulasi_id'];
				$tgl_mulai_pakai = $AssetAcc['tgl_mulai_pakai'];
				$date1 = new DateTime($tgl_mulai_pakai);
				$date2 = new DateTime($d2);
				$interval = $date1->diff($date2);
				$months = ($interval->y * 12) + $interval->m;
				$nilai = ($nilaiAsset - $nilaiResidu) / $bulanPenyusutan;

				// JIKA MASIH DALAM BULAN PERIODE LAMA BULAN PENYUSUTAN ///
				if ($months <= $bulanPenyusutan) {
					$dataNilaiAsset = array(
						'asset_id' => $asset_id,
						'jurnal_id' => $id_header,
						'nilai_penyusutan' => $nilai,
						'tanggal_proses' =>  $last_date_in_periode,
					);

					$this->db->where('asset_id', $asset_id);
					$this->db->where('tanggal_proses', $last_date_in_periode);
					$this->db->delete('acc_asset_nilai');
					$this->db->insert('acc_asset_nilai', $dataNilaiAsset);

					$dataDebet = array(
						'lokasi_id' => $lokasi_id,
						'jurnal_id' => $id_header,
						'acc_akun_id' => $akun_biaya_id, //akun ,
						'debet' => $nilai,
						'kredit' => 0,
						'ket' => 'DEP_ASSET:' . $AssetAcc['nama'] . '-' . $AssetAcc['kode'],
						'no_referensi' => 'DEP_ASSET:' . $periodeAkunting['nama'],
						'referensi_id' => NULL,
						'blok_stasiun_id' => NULL,
						'kegiatan_id' => NULL,
						'kendaraan_mesin_id' =>  NULL,
						'umur_tanam_blok' => NULL // $AssetAcc['kendaraan_mesin_id']
					);
					$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataDebet);
					$dataKredit = array(
						'lokasi_id' => $lokasi_id,
						'jurnal_id' => $id_header,
						'acc_akun_id' => $akun_akumulasi, //akun ,
						'debet' => 0,
						'kredit' => $nilai,
						'ket' => 'DEP_ASSET:' . $AssetAcc['nama'] . '-' . $AssetAcc['kode'],
						'no_referensi' => 'DEP_ASSET:' . $periodeAkunting['nama'],
						'referensi_id' => NULL,
						'blok_stasiun_id' => NULL,
						'kegiatan_id' => NULL,
						'kendaraan_mesin_id' =>  null
					);
					$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataKredit);
				}
			}
		}

		$this->db->where('id', $id);
		$this->db->update('acc_periode_akunting', array('is_proses_asset'    => '1', "tanggal_proses_asset" => date('Y-m-d H:i:s')));
		$this->set_response(array("status" => "OK", "data" => 'Proses berhasil. ' . count($resAsset) . " data diproses"), REST_Controller::HTTP_CREATED);
	}

	function laporan_asset_detail_post()
	{
		$format_laporan =  $this->post('format_laporan', true);

		$data = [];

		$input = [
			'no_po' => '01/PO/DPA',
			'asset_tipe_id' => 252,
		];

		$asset_tipe_id = $this->post('asset_tipe_id', true);

		$queryAssetTipe = "SELECT distinct b.id,b.nama  FROM  acc_asset a inner join acc_asset_tipe b on a.asset_tipe_id=b.id	 ";
		if ($asset_tipe_id) {
			$queryAssetTipe = $queryAssetTipe . " where b.id=" . $asset_tipe_id . "";
		}
		$queryAssetTipe = $queryAssetTipe . " order by b.nama;";
		// var_dump($queryAssetTipe);
		// exit();
		$dataAssetTipe = $this->db->query($queryAssetTipe)->result_array();
		$ArrAsset = array();
		$ArrAsset = [];
		
		foreach ($dataAssetTipe  as $key => $tipe) {
			$queryAsset = "SELECT
			a.*,n.nilai_susut,
			b.nama as asset_tipe,
			c.nama as lokasi,
			d.nama as akun_penyusutan,
			e.nama as posisi_asset,
			a.id AS id
			FROM acc_asset a
			LEFT JOIN acc_asset_tipe b on a.asset_tipe_id=b.id	
			LEFT JOIN gbm_organisasi c on a.lokasi_id=c.id
			LEFT JOIN acc_akun d on a.akun_penyusutan_id=d.id
			LEFT JOIN gbm_organisasi e on a.posisi_asset_id=e.id
			LEFT JOIN (select sum(nilai_penyusutan)as nilai_susut,asset_id from acc_asset_nilai group by asset_id)n 
			on a.id=n.asset_id where a.asset_tipe_id=" . $tipe['id'] . "";
			$queryAsset = $queryAsset . " order by b.nama,a.nama;";
			$dataAsset = $this->db->query($queryAsset)->result_array();
			$tipe['detail'] = $dataAsset;
			$ArrAsset[] = $tipe;
		}


		$data['asset'] = 	$ArrAsset;

		$html = $this->load->view('Acc_Asset_Laporan', $data, true);

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		// echo $html;
		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}
	function laporan_asset_detail_perbulan_post()
	{
		$bulan =  $this->post('bulan', true);
		$tahun =  $this->post('tahun', true);
		$bulan_number = (int)$bulan;
		$format_laporan =  $this->post('format_laporan', true);
		$periode = $tahun . "-" . $bulan;
		$t =  $tahun . '-' . $bulan . '-' . '01';
		$d1 = new DateTime($t);
		$d1->modify('last day of this month');
		$tanggal_akhir = $d1->format('Y-m-d');
		$d2= new DateTime($t);
		$d2->modify('fist day of this month');
		$tanggal_awal_bulan_ini = $d2->format('Y-m-d');
		

		$tAwal =  $tahun . '-01-01';
		$Awal = new DateTime($tAwal);
		$tanggal_awal_tahun_ini = $Awal->format('Y-m-d');
		$nama_periode = get_indo_bulan($d1->format('m')) . ' ' . $d1->format('Y');

		$asset_tipe_id = $this->post('asset_tipe_id', true);

		$queryAssetTipe = "SELECT distinct b.id,b.nama  FROM  acc_asset a inner join acc_asset_tipe b on a.asset_tipe_id=b.id	 ";
		if ($asset_tipe_id) {
			$queryAssetTipe = $queryAssetTipe . " where b.id=" . $asset_tipe_id . "";
		}
		$queryAssetTipe = $queryAssetTipe . " order by b.nama;";
		
		$dataAssetTipe = $this->db->query($queryAssetTipe)->result_array();
		$ArrAsset = array();
		$ArrAsset = [];
		
		foreach ($dataAssetTipe  as $key => $tipe) {
			$queryAsset = "SELECT
			a.*,ifnull(bln_ini.nilai_susut,0) as nilai_susut_bln_ini,
			 ifnull(tahun_ini.nilai_susut,0) as nilai_susut_tahun_ini,
			 ifnull(tahun_lalu.nilai_susut,0) as nilai_susut_tahun_lalu, 
			 bln_ini.no_jurnal as no_jurnal_bulan_ini,
			b.nama as asset_tipe,
			c.nama as lokasi,
			d.nama as akun_penyusutan,
			e.nama as posisi_asset,
			a.id AS id
			FROM acc_asset a
			LEFT JOIN acc_asset_tipe b on a.asset_tipe_id=b.id	
			LEFT JOIN gbm_organisasi c on a.lokasi_id=c.id
			LEFT JOIN acc_akun d on a.akun_penyusutan_id=d.id
			LEFT JOIN gbm_organisasi e on a.posisi_asset_id=e.id
			LEFT JOIN (select sum(nilai_penyusutan)as nilai_susut,asset_id,b.no_jurnal from acc_asset_nilai a
			left join acc_jurnal_ht b on a.jurnal_id=b.id
			where (tanggal_proses between  '".$tanggal_awal_bulan_ini."' and '".$tanggal_akhir."')
			group by a.asset_id,b.no_jurnal)bln_ini 
			on a.id=bln_ini.asset_id 
			LEFT JOIN (select sum(nilai_penyusutan)as nilai_susut,asset_id from acc_asset_nilai a
			left join acc_jurnal_ht b on a.jurnal_id=b.id
			where (tanggal_proses between  '".$tanggal_awal_tahun_ini."' and '".$tanggal_akhir."')
			group by a.asset_id)tahun_ini 
			on a.id=tahun_ini.asset_id
			LEFT JOIN (select sum(nilai_penyusutan)as nilai_susut,asset_id from acc_asset_nilai a
			left join acc_jurnal_ht b on a.jurnal_id=b.id
			where tanggal_proses <  '".$tanggal_awal_tahun_ini."'
			group by a.asset_id)tahun_lalu
			on a.id=tahun_lalu.asset_id
			where a.asset_tipe_id=" . $tipe['id'] . "";
			$queryAsset = $queryAsset . " order by b.nama,a.nama;";
			// echo($queryAsset);
			//  exit();
			$dataAsset = $this->db->query($queryAsset)->result_array();
			$tipe['detail'] = $dataAsset;
			$ArrAsset[] = $tipe;
		}

		$data['asset'] = 	$ArrAsset;
		$data['nama_periode'] = 	$nama_periode;

		$html = $this->load->view('Acc_Asset_Laporan_Perbulan', $data, true);

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		// echo $html;
		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}
}
