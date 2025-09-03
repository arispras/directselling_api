<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

class SlsInvoice extends BD_Controller
{
	// public $user_id;
	// public $theCredential;
	function __construct()
	{
		parent::__construct();
		$this->load->model('SlsInvoiceModel');
		$this->load->model('KaryawanModel');
		$this->load->model('M_DatatablesModel');
		$this->load->helper("antech_helper");
		// $this->auth();
		// $this->theCredential = $this->user_data;
		// $this->user_id = $this->user_data->id;
	}

	public function list_post()
	{
		$post = $this->post();

		$query  = "SELECT a.*,c.no_spk, d.nama_customer from sls_invoice a inner join sls_rekap_hd b on a.sls_rekap_id=b.id
		inner join sls_kontrak c on b.spk_id=c.id
		inner join gbm_customer d on c.customer_id=d.id";
		$search = array('no_invoice','a.tanggal','d.nama_customer','c.no_spk');
		$where  = null;

		$isWhere = null;
		// $isWhere = 'artikel.deleted_at IS NULL';

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	function index_get($segment_3 = '')
	{
		$id = $segment_3;
		$retrieve = $this->SlsInvoiceModel->retrieve($id);

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAll_get()
	{

		$retrieve = $this->SlsInvoiceModel->retrieve_all();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function create_post()
	{

		$data = $this->post();
		// $data['diubah_oleh'] = $this->user_id;
		$res =  $this->SlsInvoiceModel->create($data);
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'sls_invoice', 'action' => 'new', 'entity_id' => $res);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $data['no_invoice']), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function update_post($segment_3 = '')
	{


		$id = (int)$segment_3;
		$item = $this->SlsInvoiceModel->retrieve($id);
		if (empty($item)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$data = $this->post();
		// $data['diubah_oleh'] = $this->user_id;
		$res =   $this->SlsInvoiceModel->update($item['id'], $data);
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'sls_invoice', 'action' => 'edit', 'entity_id' => $id);
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
		$item = $this->SlsInvoiceModel->retrieve($id);
		if (empty($item)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$res =  $this->SlsInvoiceModel->delete($item['id']);
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id'=>$id)), 'entity' => 'sls_invoice', 'action' => 'delete', 'entity_id' => $id);
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

	function print_slip_get($segment_3 = '')
	{

		$id = (int)$segment_3;
		$data = [];

		$Invo = $this->SlsInvoiceModel->print_slip($id);
		$data['Invo'] = $Invo;

		$html = $this->load->view('SlsInvoice_print', $data, true);

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		echo $html;
	}
	function acc_print_slip_get($segment_3 = '')
	{

		$id = (int)$segment_3;
		$data = [];

		$queryHeader = "SELECT a.*, c.nama as item, d.nama_customer as customer,b.periode_kirim_awal as kirim_awal, b.periode_kirim_akhir as kirim_akhir FROM acc_sales_invoice a 
		INNER JOIN sls_kontrak b ON a.sls_kontrak_id=b.id
		INNER JOIN inv_item c ON b.produk_id=c.id
		INNER JOIN gbm_customer d ON a.customer_id=d.id";
		$dataHeader = $this->db->query($queryHeader)->row_array();

	
		$data['header'] = 	$dataHeader;

		$html = $this->load->view('AccSlip_slsInvoice', $data, true);

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		echo $html;
	}
}
