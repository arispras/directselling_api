<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Restserver\Libraries\REST_Controller;

class SlsIntruksiImport extends REST_Controller
{
	public $user_id;
	public $theCredential;
	function __construct()
	{
		parent::__construct();
		$this->load->model('SlsIntruksiModel');
		$this->load->model('KaryawanModel');
		$this->load->model('M_DatatablesModel');
		// $this->auth();
		// $this->theCredential = $this->user_data;
		// $this->user_id = $this->user_data->id;
	}

	
	function index_get($segment_3 = '')
	{
		$id = $segment_3;
		$retrieve = $this->SlsIntruksiModel->retrieve($id);
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function getAll_get()
	{
		$query  = "select a.* ,b.nama as mill, c.nama as milll, d.nama as produk,
		e.no_spk AS no_spk,f.nama_customer as customer from sls_intruksi_kirim a 
		left join gbm_organisasi b on a.sales_lokasi_id = b.id 
		left join gbm_organisasi c on a.kepada_lokasi_id = c.id 
		left join inv_item d on a.produk_id = d.id
        LEFT JOIN sls_kontrak e ON a.spk_id = e.id
		left join gbm_customer f on e.customer_id=f.id 
		order by a.tanggal Desc";
		$retrieve = $this->db->query($query)->result_array();
		if (!empty($retrieve)) {
			$this->set_response($retrieve, REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function create_post()
	{

		$data = $this->post();
		$data['diubah_oleh'] = $this->user_id;
		$retrieve =  $this->SlsIntruksiModel->create($data);
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
	}

	function update_post($segment_3 = '')
	{


		$id = (int)$segment_3;
		$item = $this->SlsIntruksiModel->retrieve($id);
		if (empty($item)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$data = $this->post();
		$data['diubah_oleh'] = $this->user_id;
		$retrieve =   $this->SlsIntruksiModel->update($item['id'], $data);
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
	}

	function index_delete($segment_3 = '')
	{

		$id = (int)$segment_3;
		$item = $this->SlsIntruksiModel->retrieve($id);
		if (empty($item)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$retrieve =  $this->SlsIntruksiModel->delete($item['id']);
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
}
