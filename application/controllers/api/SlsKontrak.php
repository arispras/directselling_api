<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Restserver\Libraries\REST_Controller;

class SlsKontrak extends BD_Controller
{
	public $user_id;
	public $theCredential;
	function __construct()
	{
		parent::__construct();
		$this->load->model('SlsKontrakModel');
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
		$param = $post['parameter'];

		$query  = "SELECT a.*,
		b.nama as lokasi,
		c.nama_customer as customer,
		d.nama as nama_item,
		e.user_full_name AS dibuat,
		f.user_full_name AS diubah,
		g.user_full_name AS dipost     
		from sls_kontrak a 
				left join gbm_organisasi b on a.lokasi_id=b.id 
				left join gbm_customer c on a.customer_id=c.id 
				left join inv_item d on a.produk_id=d.id
				LEFT JOIN fwk_users e ON a.dibuat_oleh = e.id
				LEFT JOIN fwk_users f ON a.diubah_oleh = f.id
				LEFT JOIN fwk_users g ON a.diposting_oleh = g.id";

		$search = array('a.no_spk', 'a.no_ref','c.nama_customer','d.nama','b.nama','a.tanggal');
		$where  = null;
		// $isWhere = null;
		// $isWhere = 'artikel.deleted_at IS NULL';
		$isWhere = " 1=1 ";
		if ($param['tgl_mulai'] && $param['tgl_mulai']) {
			$isWhere = " a.tanggal between '" . $param['tgl_mulai'] . "' and '" . $param['tgl_akhir'] . "'";
		}
		if (!empty($param['produk_id'])) {
			$isWhere = $isWhere .  "  and a.produk_id=" . $param['produk_id'] . "";
		}
		if (!empty($param['status_id'])) {
			if ($param['status_id'] == 'N') {
				$isWhere = $isWhere .  "  and a.is_posting=0";
			} else {
				$isWhere = $isWhere .  "  and a.is_posting=1";
			}
		}

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	function index_get($segment_3 = '')
	{
		$id = $segment_3;
		$retrieve = $this->SlsKontrakModel->retrieve($id);

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	
	function getAll_get()
	{

		$retrieve = $this->SlsKontrakModel->retrieve_all();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" =>[]), REST_Controller::HTTP_OK);
		}
	}
	function getAllBelumBAAngkut_get()
	{

		$retrieve = $this->SlsKontrakModel->retrieve_all_belum_ba_angkut();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" =>[]), REST_Controller::HTTP_OK);
		}
	}
	function getAllbyCustomer_get($customer_id)
	{

		$retrieve = $this->SlsKontrakModel->retrieve_all_by_customer($customer_id);

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
		$res =  $this->SlsKontrakModel->create($data);
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
		$item = $this->SlsKontrakModel->retrieve($id);
		if (empty($item)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$data = $this->post();
		$data['diubah_oleh'] = $this->user_id;
		$res =   $this->SlsKontrakModel->update($item['id'], $data);
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
		$item = $this->SlsKontrakModel->retrieve($id);
		if (empty($item)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$res =  $this->SlsKontrakModel->delete($item['id']);
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
	function posting_post($segment_3 = null)
	{
		$id = (int) $segment_3;
		$retrieve_header = $this->SlsKontrakModel->retrieve($id);
		if (empty($retrieve_header)) {

			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
			return;
		} else {
		}
		$input['diposting_oleh'] = $this->user_id;
		$res = $this->SlsKontrakModel->posting($id, $input);
		if (!empty($res)) {
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

	function print_slip_get($segment_3 = '')
	{

		$id = (int)$segment_3;
		$data = [];

		$hd = $this->db->query("SELECT a.*,
		b.nama as lokasi,
		c.nama_customer as customer,
		d.nama as nama_item,
		e.user_full_name AS dibuat,
		f.user_full_name AS diubah,
		g.user_full_name AS dipost     
		from sls_kontrak a 
		left join gbm_organisasi b on a.lokasi_id=b.id 
		left join gbm_customer c on a.customer_id=c.id 
		left join inv_item d on a.produk_id=d.id
		LEFT JOIN fwk_users e ON a.dibuat_oleh = e.id
		LEFT JOIN fwk_users f ON a.diubah_oleh = f.id
		LEFT JOIN fwk_users g ON a.diposting_oleh = g.id where a.id=" . $id)->row_array();
		$data['hd'] = $hd;

		// $data['dt'] = $dt;

		$html = $this->load->view('SlsSlipKontrak', $data, true);

		$filename = 'AccSlipApInvoice_' . time();
		$this->pdfgenerator->generate($html, $filename, true, 'A4', 'portrait');
		echo $html;
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
		a.id AS id_kontrak
		
		FROM sls_kontrak a
		LEFT JOIN gbm_organisasi b ON a.lokasi_id=b.id
		LEFT JOIN gbm_customer c ON a.customer_id=c.id
		LEFT JOIN inv_item d ON a.produk_id=d.id

		where a.tanggal between  '". $tanggal_awal ."' and  '". $tanggal_akhir ."'	
		";
		$filter_lokasi="Semua";
		if ($lokasi_id){
			$queryPo=$queryPo." and a.lokasi_id=".$lokasi_id ."";
			$res=$this->db->query("select * from gbm_organisasi where id=".$lokasi_id."")->row_array();
			$filter_lokasi=$res['nama'];
		}

		$dataPo = $this->db->query($queryPo)->result_array();
		foreach ($dataPo as $key => $h) {
			$q="SELECT no_rekap,tanggal,total_berat_terima FROM prc_rekap_angkut_hd where sls_kontrak_id=". $h['id_kontrak']."";
			$dataDtl = $this->db->query($q)->result_array();
			$ket_rekap_angkut='';
			$jum_qty_terima=0;
			foreach ($dataDtl as $key2 => $d) {
				$ket_rekap_angkut=$ket_rekap_angkut." ~ NoBA:".$d['no_rekap']." qty:".number_format($d['total_berat_terima'],2);
				$jum_qty_terima=$jum_qty_terima+$d['total_berat_terima'];
			}	

			$dataPo[$key]['ket_rekap_angkut']=$ket_rekap_angkut;
			$dataPo[$key]['jum_qty_terima']=$jum_qty_terima;

		}
		
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
