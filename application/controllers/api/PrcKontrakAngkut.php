<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Restserver\Libraries\REST_Controller;

class PrcKontrakAngkut extends BD_Controller
{
	public $user_id;
	public $theCredential;
	function __construct()
	{
		parent::__construct();
		$this->load->model('PrcKontrakAngkutModel');
		$this->load->model('KaryawanModel');
		$this->load->library('pdfgenerator');
		$this->load->model('M_DatatablesModel');
		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id = $this->user_data->id;
	}

	public function list_post()
	{
		$post = $this->post();

		$query  = "SELECT a.*,b.nama as lokasi,c.nama_supplier as supplier ,d.nama as nama_item from prc_kontrak_angkut a 
		left join gbm_organisasi b on a.lokasi_id=b.id 
		left join gbm_supplier c on a.supplier_id=c.id 
		left join inv_item d on a.produk_id=d.id";

		$search = array('a.no_spk', 'a.no_ref','c.nama_supplier','d.nama','b.nama','a.tanggal');
		$where  = null;

		$isWhere = null;
		// $isWhere = 'artikel.deleted_at IS NULL';

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	function index_get($segment_3 = '')
	{
		$id = $segment_3;
		$retrieve = $this->PrcKontrakAngkutModel->retrieve($id);

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	
	function getAll_get()
	{

		$retrieve = $this->PrcKontrakAngkutModel->retrieve_all();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" =>[]), REST_Controller::HTTP_OK);
		}
	}
	function getAllbyCustomer_get($supplier_id)
	{

		$retrieve = $this->PrcKontrakAngkutModel->retrieve_all_by_customer($supplier_id);

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
		$res =  $this->PrcKontrakAngkutModel->create($data);
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'prc_kontrak_angkut', 'action' => 'new', 'entity_id' => $res);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $data['no_transaksi']), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function update_post($segment_3 = '')
	{


		$id = (int)$segment_3;
		$item = $this->PrcKontrakAngkutModel->retrieve($id);
		if (empty($item)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$data = $this->post();
		$data['diubah_oleh'] = $this->user_id;
		$retrieve =   $this->PrcKontrakAngkutModel->update($item['id'], $data);
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
	}

	function index_delete($segment_3 = '')
	{

		$id = (int)$segment_3;
		$item = $this->PrcKontrakAngkutModel->retrieve($id);
		if (empty($item)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$retrieve =  $this->PrcKontrakAngkutModel->delete($item['id']);
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
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
			$supplier_id=$this->post('supplier_id',true);
			$tanggal_awal=$this->post('tgl_mulai',true);
			$tanggal_akhir=$this->post('tgl_akhir',true);
			$format_laporan =  $this->post('format_laporan',true);
		}else {
			$input = [
				'tanggal'=> '2021-12-23',
			];
			$lokasi_id=263;
			$tanggal_awal='2020-01-01';
			$tanggal_akhir='2022-12-12';
			$format_laporan =  'view';
		}

		$queryPo = "SELECT 
		a.*,
		b.nama AS lokasi,
		c.nama_customer AS customer,
		d.nama AS produk,
		a.id AS id
		
		FROM prc_kontrak_angkut a
		LEFT JOIN gbm_organisasi b ON a.lokasi_id=b.id
		LEFT JOIN gbm_supplier c ON a.supplier_id=c.id
		LEFT JOIN inv_item d ON a.produk_id=d.id

		where a.tanggal between  '". $tanggal_awal ."' and  '". $tanggal_akhir ."'	
		";
		$filter_supplier="Semua";
		if ($lokasi_id){
			$queryPo=$queryPo." and a.lokasi_id=".$lokasi_id ."";
			$res=$this->db->query("select * from gbm_organisasi where id=".$lokasi_id."")->row_array();
			$filter_lokasi=$res['nama'];
		}

		$dataPo = $this->db->query($queryPo)->result_array();
		
		$data['po'] = 	$dataPo;
		$data['filter_lokasi'] = 	$filter_lokasi;
		$data['filter_tgl_awal'] = 	$tanggal_awal;
		$data['filter_tgl_akhir'] = $tanggal_akhir;

		$html = $this->load->view('Sls_Kontrak_Laporan', $data, true);

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
