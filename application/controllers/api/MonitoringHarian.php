<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Restserver\Libraries\REST_Controller;

class MonitoringHarian extends BD_Controller
{
	public $user_id;
	public $theCredential;
	function __construct()
	{
		parent::__construct();
		$this->load->model('MonitoringHarianModel');
		$this->load->model('KaryawanModel');
		$this->load->model('GbmOrganisasiModel');
		$this->load->library('pdfgenerator');
		$this->load->model('M_DatatablesModel');
		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id = $this->user_data->id;
	}

	public function list_post()
	{
		$post = $this->post();

		$query  = "SELECT a.*,b.nama as lokasi,c.nama_customer as customer ,d.nama as nama_item from sls_kontrak a 
		left join gbm_organisasi b on a.lokasi_id=b.id 
		left join gbm_customer c on a.customer_id=c.id 
		left join inv_item d on a.produk_id=d.id";

		$search = array('a.no_spk', 'a.no_ref','c.nama_customer','d.nama','b.nama','a.tanggal');
		$where  = null;

		$isWhere = null;
		// $isWhere = 'artikel.deleted_at IS NULL';

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	function index_get($segment_3 = '')
	{
		$id = $segment_3;
		$retrieve = $this->MonitoringHarianModel->retrieve($id);

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	
	function getAll_get()
	{

		$retrieve = $this->MonitoringHarianModel->retrieve_all();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" =>[]), REST_Controller::HTTP_OK);
		}
	}
	function getAllbyCustomer_get($customer_id)
	{

		$retrieve = $this->MonitoringHarianModel->retrieve_all_by_customer($customer_id);

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" =>[]), REST_Controller::HTTP_OK);
		}
	}
	function create_post()
	{

		$data = $this->post();
		$data['dibuat_oleh'] = $this->user_id;
		$data['diubah_oleh'] = $this->user_id;
		$res =  $this->MonitoringHarianModel->create($data);
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'sls_kontrak', 'action' => 'new', 'entity_id' => $res);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $data['no_spk']), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function update_post($segment_3 = '')
	{


		$id = (int)$segment_3;
		$item = $this->MonitoringHarianModel->retrieve($id);
		if (empty($item)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$data = $this->post();
		$data['diubah_oleh'] = $this->user_id;
		$res =   $this->MonitoringHarianModel->update($item['id'], $data);
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'sls_kontrak', 'action' => 'edit', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function index_delete($segment_3 = '')
	{

		$id = (int)$segment_3;
		$item = $this->MonitoringHarianModel->retrieve($id);
		if (empty($item)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$res =  $this->MonitoringHarianModel->delete($item['id']);
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id'=>$id)), 'entity' => 'sls_kontrak', 'action' => 'delete', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function import($path_file)
	{

		// $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx(); //Excel 2007 or higher
		$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls(); //Excel 2003
		$spreadsheet = $reader->load($path_file);
		$sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
		return $sheetData;
	}

	function laporan_detail_post()
	{
		error_reporting(0);
		
		$data = [];
		if (isset($this->post()['tgl_mulai'])) {
			$input = $this->post();
			$lokasi_id=$this->post('lokasi_id',true);
			$tanggal_awal=$this->post('tgl_mulai',true);
			$tanggal_akhir=$this->post('tgl_akhir',true);
			$format_laporan =  $this->post('format_laporan',true);
		}else {
			$input = [
				'tanggal'=> '2021-12-23',
			];
			$lokasi_id=252;
			$lokasi_id='';
			$tanggal_awal='2022-08-01';
			$tanggal_akhir='2022-12-12';
			$format_laporan =  'view';
		}


		$dataTransaksi = [
			'ESTATE'=> [
				'Bkm Panen'=> 'est_bkm_panen_ht',
				'Bkm Pemeliharaan'=> 'est_bkm_pemeliharaan_ht',
				'Bkm Umum'=> 'est_bkm_umum_ht',
				'SPK'=> 'est_spk_ht',
			],
			'INVENTORY'=> [
				'Penerimaan Po'=> 'inv_penerimaan_po_ht',
				'Penerimaan Tanpa Po'=> 'inv_penerimaan_tanpa_po_ht',
				'Pemakaian Barang'=> 'inv_pemakaian_ht',
				'Pindah Gudang'=> 'inv_pindah_gudang_ht',
				'Penerimaan Pindah gudang'=> 'inv_penerimaan_pindah_gudang_ht',
			],
			'PAYROLL'=> [
				'Absensi'=> 'payroll_absensi',
				'Lembur'=> 'payroll_lembur',
			],
			'TRAKSI'=> [
				'Kegiatan Kendaraan'=> 'trk_kegiatan_kendaraan_ht',
				'Workshop'=> 'wrk_kegiatan_ht',
			],
			'ACCOUNTING'=> [
				'Kas & Bank'=> 'acc_kasbank_ht',
			],
		];
		
		$org = $this->GbmOrganisasiModel->retrieve_all_bytipe("UNIT");

		$dataField=[];
		foreach ($dataTransaksi as $key=>$val) {
			foreach ($val as $key2=>$val2) {
				
				if (!empty($lokasi_id)) {
					$dataField[$key][$key2] = $this->db->query("SELECT 
						IFNULL(b.nama, '-') AS lokasi, 
						COUNT(a.id) AS jumlah, 
						IF( STRCMP(MAX(a.tanggal), '0000-00-00') = 0, '-', MAX(a.tanggal)) AS tanggal_terakhir
						FROM ".$val2." a 
						LEFT JOIN gbm_organisasi b ON a.lokasi_id=b.id 
						WHERE a.lokasi_id='".$lokasi_id."' AND a.tanggal BETWEEN  '". $tanggal_awal ."' AND  '". $tanggal_akhir ."'	
						ORDER BY a.tanggal DESC")->result_array();
				}else {
					foreach ($org as $key3=>$val3) {
						$dataFieldParse = $this->db->query("SELECT
							IFNULL(b.nama, '-') AS lokasi,  
							COUNT(a.id) AS jumlah, 
							IF( STRCMP(MAX(a.tanggal), '0000-00-00') = 0, '-', MAX(a.tanggal)) AS tanggal_terakhir
							FROM ".$val2." a 
							LEFT JOIN gbm_organisasi b ON a.lokasi_id=b.id 
							WHERE a.lokasi_id='".$val3['id']."' AND a.tanggal BETWEEN  '". $tanggal_awal ."' AND  '". $tanggal_akhir ."'	
							ORDER BY a.tanggal DESC")->row_array();
						$dataFieldParse['lokasi'] = $val3['nama'];
						$dataField[$key][$key2][] = $dataFieldParse;
					}
				}
			}
		}

		if (!empty($lokasi_id)) {
			$dataLokasi = $this->db->query("SELECT * FROM gbm_organisasi WHERE id=".$lokasi_id)->row_array();
		}else {
			$dataLokasi = [];
			$dataLokasi['nama'] = 'SEMUA';
		}

		

		// echo '<pre>';
		// echo print_r($dataField).'</pre>';
		// exit; die;

		
		$data['dataField'] = 	$dataField;
		$data['dataLokasi'] = 	$dataLokasi;
		$data['filter_tgl_awal'] = 	$tanggal_awal;
		$data['filter_tgl_akhir'] = $tanggal_akhir;

		$html = $this->load->view('Monitoring_Harian_Laporan', $data, true);

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


	function laporan_detail_non_posting_post()
	{
		error_reporting(0);
		
		$data = [];
		if (isset($this->post()['tgl_mulai'])) {
			$input = $this->post();
			$lokasi_id=$this->post('lokasi_id',true);
			$transaksi=$this->post('transaksi_id',true);
			$tanggal_awal=$this->post('tgl_mulai',true);
			$tanggal_akhir=$this->post('tgl_akhir',true);
			$format_laporan =  $this->post('format_laporan',true);
		}else {
			$input = [
				'tanggal'=> '2021-12-23',
			];
			$lokasi_id=252;
			$lokasi_id='';
			$transaksi= [ 'id'=>'est_bkm_panen_ht', 'text'=>'BKM PANEN' ];
			$tanggal_awal='2022-08-01';
			$tanggal_akhir='2022-12-12';
			$format_laporan =  'view';
		}


		$dataTransaksi = [
			'ESTATE'=> [
				'Bkm Panen'=> 'est_bkm_panen_ht',
				'Bkm Pemeliharaan'=> 'est_bkm_pemeliharaan_ht',
				'Bkm Umum'=> 'est_bkm_umum_ht',
				'SPK'=> 'est_spk_ht',
			],
			'INVENTORY'=> [
				'Penerimaan Po'=> 'inv_penerimaan_po_ht',
				'Penerimaan Tanpa Po'=> 'inv_penerimaan_tanpa_po_ht',
				'Pemakaian Barang'=> 'inv_pemakaian_ht',
				'Pindah Gudang'=> 'inv_pindah_gudang_ht',
				'Penerimaan Pindah gudang'=> 'inv_penerimaan_pindah_gudang_ht',
			],
			'TRAKSI'=> [
				'Kegiatan Kendaraan'=> 'trk_kegiatan_kendaraan_ht',
				'Workshop'=> 'wrk_kegiatan_ht',
			],
			'ACCOUNTING'=> [
				'Kas & Bank'=> 'acc_kasbank_ht',
			],
		];
		
		$org = $this->GbmOrganisasiModel->retrieve_all_bytipe("UNIT");

		$dataField=[];
		foreach ($dataTransaksi as $key=>$val) {
			foreach ($val as $key2=>$val2) {
				
				if (!empty($lokasi_id)) {
					if (empty($transaksi)) {
						if ($key2 == 'SPK') {
							$dataField[$key][$key2][0] = $this->db->query("SELECT 
								IFNULL(a.no_spk, '-') AS no_transaksi,
								a.tanggal,
								IFNULL(b.nama, '-') AS lokasi 
								FROM ".$val2." a 
								LEFT JOIN gbm_organisasi b ON a.lokasi_id=b.id 
								WHERE a.is_posting=0 AND a.lokasi_id='".$lokasi_id."' AND a.tanggal BETWEEN  '". $tanggal_awal ."' AND  '". $tanggal_akhir ."'
								ORDER BY a.tanggal ASC")->result_array();
						}else {
							$dataField[$key][$key2][0] = $this->db->query("SELECT 
								IFNULL(a.no_transaksi, '-') AS no_transaksi,
								a.tanggal,
								IFNULL(b.nama, '-') AS lokasi 
								FROM ".$val2." a 
								LEFT JOIN gbm_organisasi b ON a.lokasi_id=b.id 
								WHERE a.is_posting=0 AND a.lokasi_id='".$lokasi_id."' AND a.tanggal BETWEEN  '". $tanggal_awal ."' AND  '". $tanggal_akhir ."'
								ORDER BY a.tanggal ASC")->result_array();
						}
					}
				}else {
					if (empty($transaksi)) {
						foreach ($org as $key3=>$val3) {
							if ($key2 == 'SPK') {
								$dataFieldParse = $this->db->query("SELECT
									IFNULL(a.no_spk, '-') AS no_transaksi,
									a.tanggal,
									IFNULL(b.nama, '-') AS lokasi  
									FROM ".$val2." a 
									LEFT JOIN gbm_organisasi b ON a.lokasi_id=b.id 
									WHERE a.is_posting=0 AND a.lokasi_id='".$val3['id']."' AND a.tanggal BETWEEN  '". $tanggal_awal ."' AND  '". $tanggal_akhir ."'	
									ORDER BY a.tanggal ASC")->result_array();
							}else {
								$dataFieldParse = $this->db->query("SELECT
									IFNULL(a.no_transaksi, '-') AS no_transaksi,
									a.tanggal,
									IFNULL(b.nama, '-') AS lokasi  
									FROM ".$val2." a 
									LEFT JOIN gbm_organisasi b ON a.lokasi_id=b.id 
									WHERE a.is_posting=0 AND a.lokasi_id='".$val3['id']."' AND a.tanggal BETWEEN  '". $tanggal_awal ."' AND  '". $tanggal_akhir ."'	
									ORDER BY a.tanggal ASC")->result_array();
							}
							$dataFieldParse[]['lokasi'] = $val3['nama'];
							$dataField[$key][$key2][] = $dataFieldParse;
						}
					}
				}
			}
		}

		if (!empty($transaksi)) {
			$menu = $transaksi['text'];

			if (!empty($lokasi_id)) {
				if ($transaksi['text'] == 'SPK') {
					$dataField['x'][$menu][0] = $this->db->query("SELECT 
						IFNULL(a.no_spk, '-') AS no_transaksi,
						a.tanggal,
						IFNULL(b.nama, '-') AS lokasi 
						FROM ".$transaksi['id']." a 
						LEFT JOIN gbm_organisasi b ON a.lokasi_id=b.id 
						WHERE a.is_posting=0 AND a.lokasi_id='".$lokasi_id."' AND a.tanggal BETWEEN  '". $tanggal_awal ."' AND  '". $tanggal_akhir ."'
						ORDER BY a.tanggal ASC")->result_array();
				}else {
					$dataField['x'][$menu][0] = $this->db->query("SELECT 
						IFNULL(a.no_transaksi, '-') AS no_transaksi,
						a.tanggal,
						IFNULL(b.nama, '-') AS lokasi 
						FROM ".$transaksi['id']." a 
						LEFT JOIN gbm_organisasi b ON a.lokasi_id=b.id 
						WHERE a.is_posting=0 AND a.lokasi_id='".$lokasi_id."' AND a.tanggal BETWEEN  '". $tanggal_awal ."' AND  '". $tanggal_akhir ."'
						ORDER BY a.tanggal ASC")->result_array();
				}
			}else {
				foreach ($org as $key3=>$val3) {
					if ($transaksi['text'] == 'SPK') {
						$dataFieldParse = $this->db->query("SELECT
							IFNULL(a.no_spk, '-') AS no_transaksi,
							a.tanggal,
							IFNULL(b.nama, '-') AS lokasi  
							FROM ".$transaksi['id']." a 
							LEFT JOIN gbm_organisasi b ON a.lokasi_id=b.id 
							WHERE a.is_posting=0 AND a.lokasi_id='".$val3['id']."' AND a.tanggal BETWEEN  '". $tanggal_awal ."' AND  '". $tanggal_akhir ."'	
							ORDER BY a.tanggal ASC")->result_array();
					}else {
						$dataFieldParse = $this->db->query("SELECT
							IFNULL(a.no_transaksi, '-') AS no_transaksi,
							a.tanggal,
							IFNULL(b.nama, '-') AS lokasi  
							FROM ".$transaksi['id']." a 
							LEFT JOIN gbm_organisasi b ON a.lokasi_id=b.id 
							WHERE a.is_posting=0 AND a.lokasi_id='".$val3['id']."' AND a.tanggal BETWEEN  '". $tanggal_awal ."' AND  '". $tanggal_akhir ."'	
							ORDER BY a.tanggal ASC")->result_array();
					}
					$dataFieldParse[]['lokasi'] = $val3['nama'];
					$dataField[$key][$menu][] = $dataFieldParse;
				}
			}

		}


		

		$data['header']['transaksi'] = $transaksi['text'];



		if (!empty($lokasi_id)) {
			$dataLokasi = $this->db->query("SELECT * FROM gbm_organisasi WHERE id=".$lokasi_id)->row_array();
		}else {
			$dataLokasi = [];
			$dataLokasi['nama'] = 'SEMUA';
		}

		

		// echo '<pre>';
		// echo print_r($dataField).'</pre>';
		// exit; die;

		
		$data['dataField'] = 	$dataField;
		$data['dataLokasi'] = 	$dataLokasi;
		$data['filter_tgl_awal'] = 	$tanggal_awal;
		$data['filter_tgl_akhir'] = $tanggal_akhir;

		$html = $this->load->view('Monitoring_Harian_Non_Posting_Laporan', $data, true);

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
