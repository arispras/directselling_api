<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';


class UserLog extends REST_Controller
{

	public function __construct()
	{
		date_default_timezone_set('Asia/Jakarta');
		parent::__construct();
		$this->load->model('UserLogModel');
	}
	
	public function UserLog_get()
	{ //Request Method select ALL
		$login_id = $this->get('login_id');
		$userLog = $this->UserLogModel->getUserLog($login_id);


		if ($userLog) {
			$this->set_response(
				$userLog,
				REST_Controller::HTTP_OK
			);
		} else {
			$this->set_response([
				'status' => false,
				'message' => 'Log Tidak Tersedia',
			], REST_Controller::HTTP_NOT_FOUND);
		}
	}
	
	public function UserLog_post()
	{
		$data = $this->post();
		$absen = $this->UserLogModel->Simpan($data);
		$message = array();
		if ($absen) {
			$message = [
				'id' => $absen, 
				'message' => 'OK'
			];
		} else {
			$message = [
				'id' => null,
				'message' => 'NOT OK'
			];
		}
		$this->set_response($message, REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
	}
	public function UserLogLogin_post()
	{
		$data = $this->post();
		$data['last_activity']='0';
		$data['lasttime']=date('Y-m-d H:i:s');
		$userLog = $this->UserLogModel->Simpan($data);
		$message = array();
		if ($userLog) {
			$message = [
				'id' => $userLog, 
				'message' => 'OK'
			];
		} else {
			$message = [
				'id' => null,
				'message' => 'NOT OK'
			];
		}
		$this->set_response($message, REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
	}
	public function UserLogLogout_post()
	{
		$data = $this->post();
		$data['last_activity']='1';
		$data['lasttime']=date('Y-m-d H:i:s');
	
		$userLog = $this->UserLogModel->Simpan($data);
		$message = array();
		if ($userLog) {
			$message = [
				'id' => $userLog, 
				'message' => 'OK'
			];
		} else {
			$message = [
				'id' => null,
				'message' => 'NOT OK'
			];
		}
		$this->set_response($message, REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
	}
	public function UploadFoto_post()
	{
		$postData = $this->post();
		// echo   $_SERVER['DOCUMENT_ROOT'] . "/Demo/userfiles/absensi";
		$config = array(
			'upload_path' => $_SERVER['DOCUMENT_ROOT'] . "/".$_SERVER["HTTP_NAMAPATH"]."/userfiles/absensiQRCode",	            //path for upload
			'allowed_types' => "gif|jpg|png|jpeg",   //restrict extension
			// 'max_size' => '100',
			// 'max_width' => '1024',
			// 'max_height' => '768',
			//'file_name' => 'logo_' . date('ymdhis')
		);
		$this->load->library('upload', $config);

		if ($this->upload->do_upload('file')) {
			$data = array('upload_data' => $this->upload->data());
			//$path = $config['upload_path'] . '/' . $data['upload_data']['orig_name'];
			// Write query to store image details of login user { }
			$returndata = array('status' => 1, 'data' => 'OK', 'message' => 'image uploaded successfully');
			$this->set_response($returndata, 200);
		} else {
			$error = array('error' => $this->upload->display_errors());
			$returndata = array('status' => 0, 'data' => $error, 'message' => 'image upload failed');
			$this->set_response($returndata, 200);
		}
	}
}
