<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Restserver\Libraries\REST_Controller;

class SlsKontrakImport extends REST_Controller
{
	public $user_id;
	public $theCredential;
	function __construct()
	{
		parent::__construct();
		$this->load->model('SlsKontrakModel');
		$this->load->model('KaryawanModel');
		$this->load->model('M_DatatablesModel');
		// $this->auth();
		// $this->theCredential = $this->user_data;
		// $this->user_id = $this->user_data->id;
	}

	public function list_post()
	{
		$post = $this->post();

		$query  = "select a.*,b.nama as mill,c.nama_customer as customer ,d.nama as nama_item from sls_kontrak a 
		left join gbm_organisasi b on a.mill_id=b.id 
		left join gbm_customer c on a.customer_id=c.id 
		left join inv_item d on a.produk_id=d.id";
		$search = array('a.no_spk', 'a.no_ref');
		$where  = null;

		$isWhere = null;
		// $isWhere = 'artikel.deleted_at IS NULL';

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
		$query  = "select a.*,b.nama as mill,c.nama_customer as customer ,d.nama as nama_item from sls_kontrak a 
		left join gbm_organisasi b on a.mill_id=b.id 
		left join gbm_customer c on a.customer_id=c.id 
		left join inv_item d on a.produk_id=d.id
		order by a.tanggal Desc";
		$retrieve = $this->db->query($query)->result_array();

		if (!empty($retrieve)) {
			$this->set_response($retrieve, REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	
}
