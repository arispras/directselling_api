<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');
// require APPPATH . 'libraries/REST_Controller.php';
// require APPPATH . 'libraries/Format.php';


class UploadFile extends REST_Controller
{

	public function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");

		$this->load->model('M_DatatablesModel');
		$this->load->library('image_lib');
		$this->load->library('upload');
		$this->load->helper(array('url', 'form', 'text',  'security', 'file', 'number', 'date', 'download'));
	}

	function materi_post()
	{

		$config['upload_path']   = 	$_SERVER['DOCUMENT_ROOT'] . "/" . $_SERVER["HTTP_NAMAPATH"] . "/userfiles/files/"; //get_path_file();
		$config['allowed_types'] = 'doc|zip|rar|txt|docx|xls|xlsx|pdf|tar|gz|jpg|jpeg|JPG|JPEG|png|ppt|pptx|mp3|mp4|m4a';
		$config['max_size']      = '0';
		$config['max_width']     = '0';
		$config['max_height']    = '0';
		$config['file_name']     = 'mat' . date('_Y_m_d_H_i_s');
		$this->upload->initialize($config);

		if ($this->upload->do_upload()) {
			$upload_data = $this->upload->data();
			// $check_field_value['file_name'] = $upload_data['file_name'];
			$this->set_response(array("status" => "OK", "filename" => $upload_data['file_name']), REST_Controller::HTTP_OK);
		} else {

			$this->set_response(array("status" => "NOT OK", "filename" => ""), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function tugas_post()
	{

		$config['upload_path']   = 	$_SERVER['DOCUMENT_ROOT'] . "/" . $_SERVER["HTTP_NAMAPATH"] . "/userfiles/files/"; //get_path_file();
		$config['allowed_types'] = 'doc|zip|rar|txt|docx|xls|xlsx|pdf|tar|gz|jpg|jpeg|JPG|JPEG|png|ppt|pptx|mp3|mp4|m4a';
		$config['max_size']      = '0';
		$config['max_width']     = '0';
		$config['max_height']    = '0';
		$config['file_name']     = 'tug' . date('_Y_m_d_H_i_s');
		$this->upload->initialize($config);

		if ($this->upload->do_upload()) {
			$upload_data = $this->upload->data();
			// $check_field_value['file_name'] = $upload_data['file_name'];
			$this->set_response(array("status" => "OK", "filename" => $upload_data['file_name']), REST_Controller::HTTP_OK);
		} else {

			$this->set_response(array("status" => "NOT OK", "filename" => ""), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function pertanyaan_post()
	{

		$config['upload_path']   = 	$_SERVER['DOCUMENT_ROOT'] . "/" . $_SERVER["HTTP_NAMAPATH"] . "/userfiles/files/"; //get_path_file();
		$config['allowed_types'] = 'doc|zip|rar|txt|docx|xls|xlsx|pdf|tar|gz|jpg|jpeg|JPG|JPEG|png|ppt|pptx|mp3|mp4|m4a';
		$config['max_size']      = '0';
		$config['max_width']     = '0';
		$config['max_height']    = '0';
		$config['file_name']     = 'pty' . date('_Y_m_d_H_i_s');
		$this->upload->initialize($config);

		if ($this->upload->do_upload()) {
			$upload_data = $this->upload->data();
			// $check_field_value['file_name'] = $upload_data['file_name'];
			$this->set_response(array("status" => "OK", "filename" => $upload_data['file_name']), REST_Controller::HTTP_OK);
		} else {

			$this->set_response(array("status" => "NOT OK", "filename" => ""), REST_Controller::HTTP_NOT_FOUND);
		}
	}
}
