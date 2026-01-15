<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

class GbmProvinsi extends BD_Controller
{
	public $user_id;
	public $theCredential;
	public $data = array();
	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('M_DatatablesModel');
		$this->load->model('GbmProvinsiModel');
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id = $this->user_data->id;
	}
	
	public function list_mobile_post($page_no, $limit)
	{
		$post = $this->post();
		$param = $post['parameter'];

		$query  =" SELECT a.*,b.nama as provinsi 
		from GbmProvinsi a left join provinsi b 
		on a.provinsi_id=b.id
		order by a.nama
		LIMIT " . $limit . " OFFSET " . $page_no . "
		";
		$data = $this->db->query($query)->result_array();
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	
	function getAll_get($id = '')
	{
		$retrieve = array();
		$retrieve = $this->GbmProvinsiModel->retrieve_all();
		
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	
	function index_get($id = '')
	{
		$retrieve = array();
		$retrieve = $this->GbmProvinsiModel->retrieve_by_id($id);
		$retrieve_detail = $this->GbmProvinsiModel->retrieve_detail($id);
		
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	
	function index_post()
	{
		$input = $this->post();
		$input['dibuat_oleh'] = $this->user_id;
		$input['diubah_oleh'] = $this->user_id;

		
		$res = $this->GbmProvinsiModel->create($input);
		if (!empty($res)) {
			$this->set_response(array("status" => "OK", "data" =>$res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function index_put($segment_3 = null)
	{
		$id = (int)$segment_3;
		$data = $this->put();
		$data['diubah_oleh'] = $this->user_id;

		$res = $this->GbmProvinsiModel->update($id, $data);
		if (!empty($res)) {
				$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	public function index_delete($id)
	{
		$res = $this->GbmProvinsiModel->delete($id);
		if (!empty($res)) {
				$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	
	public function UploadFoto_post()
	{
		// $postData = $this->post();
		// $returndata = array('status' => 0, 'data' => $postData, 'message' => 'image upload failed');
		// 	$this->set_response($returndata, 200);
		// 	return;
		// echo   $_SERVER['DOCUMENT_ROOT'] . "/Demo/userfiles/absensi";
		$config = array(
			'upload_path' => $_SERVER['DOCUMENT_ROOT'] . "/"  . 'plantation' . '/userfiles/inspeksi',	            //path for upload
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

	function get_path_image($img = '', $size = '')
	{
		if (empty($size)) {
			return  $_SERVER['DOCUMENT_ROOT'] . "/"  .  'directsales'  . '/userfiles/inspeksi/' . $img;
		} else {
			$pisah = explode('.', $img);
			$ext = end($pisah);
			$nama_file = $pisah[0];

			return  $_SERVER['DOCUMENT_ROOT'] . "/"  .  'directsales'  . '/userfiles/inspeksi/' . $nama_file . '_' . $size . '.' . $ext;
		}
	}
	
}
