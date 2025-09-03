<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

class PksTankiImport extends Rest_Controller
{
	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('GbmOrganisasiModel');
		$this->load->model('PksTankiModel');
		$this->load->model('M_DatatablesModel');
	}
	
	function index_get($id = '')
	{
		$retrieve = $this->PksTankiModel->retrieve_by_id($id);
		$retrieve['detail'] = $this->PksTankiModel->retrieve_detail($id);


		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}


	function getAll_get()
	{
		
		$retrieve = $this->PksTankiModel->retrieve_all();
				 
		if (!empty($retrieve)) {
			$this->set_response( $retrieve, REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAllDetail_get($id='')
	{
		$retrieve = $this->PksTankiModel->retrieve_all_detail($id);
				 
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}


	
}
