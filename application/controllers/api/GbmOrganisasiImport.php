<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
use Restserver\Libraries\REST_Controller;

class GbmOrganisasiImport extends Rest_Controller
{
	public $data=array();
	function __construct()
    {
        parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('GbmOrganisasiModel');
	
    }

	
	public function getmenuAllChild_get(){
		
		$menu = $this->GbmOrganisasiModel->retrieve_all_child();
		$this->set_response($menu, REST_Controller::HTTP_OK);

	}
	public function getAllByTipe_get($segment_3 = ''){
		$tipe=$segment_3;
		$menu = $this->GbmOrganisasiModel->retrieve_all_bytipe($tipe);
		$this->set_response($menu, REST_Controller::HTTP_OK);

	}
	public function getAllAdmUnit_get(){
	
		$menu = $this->GbmOrganisasiModel->getAllAdmUnit();
		$this->set_response($menu, REST_Controller::HTTP_OK);

	}
	public function getmenuAllParent_get(){
		
		$menu = $this->GbmOrganisasiModel->retrieve_all_parent();
		$this->set_response($menu, REST_Controller::HTTP_OK);

	}

	
   	public	function getById_get($segment_3 = '')
	{

		$id = $segment_3;
		$retrieve = $this->GbmOrganisasiModel->retrieve($id);


		if (!empty($retrieve)) {
			
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => null), REST_Controller::HTTP_NOT_FOUND);
		}
	}
    

       
}
