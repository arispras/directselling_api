<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';


class Token extends REST_Controller{

    public function __construct(){
        parent::__construct();
        $this->load->model('TokenModel');
    }

    public function index_put(){
	$id = $this->put('id');
	$data = ['fcm_token' => $this->put('fcm_token')];
	
	if($this->TokenModel->updateToken($data,$id) > 0){
	 $this->set_response([
                'status' => True,
                'message' => 'FCM diupdate',
            ], REST_Controller::HTTP_NO_CONTENT);
	}else{
	 $this->set_response([
                'status' => false,
                'message' => 'FCM gagal di update',
            ], REST_Controller::HTTP_NOT_FOUND);
	}
		
	} 
   
}
