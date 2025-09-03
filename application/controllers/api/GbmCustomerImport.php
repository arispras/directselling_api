<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Restserver\Libraries\REST_Controller;

class GbmCustomerImport extends REST_Controller
{
    public $user_id;
	public $theCredential;
	function __construct()
    {
        parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('GbmCustomerModel');
		$this->load->model('KaryawanModel');
		$this->load->model('M_DatatablesModel');
		$this->load->library('image_lib');
		$this->load->library('upload');
		// $this->auth();
		// $this->theCredential = $this->user_data;
		// $this->user_id=$this->user_data->id;
		
    }

    function index_get($segment_3 = '')
    {
		$id = $segment_3;
		$retrieve = $this->GbmCustomerModel->retrieve($id);
		
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
     }
	 function getAll_get()
	 {
		
		 $retrieve = $this->GbmCustomerModel->retrieve_all();
				 
		 if (!empty($retrieve)) {
			 $this->set_response( $retrieve, REST_Controller::HTTP_OK);
		 } else {
			 $this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		 }
	  }
    
	
}
