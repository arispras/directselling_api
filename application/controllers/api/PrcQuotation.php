<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

class PrcQuotation extends BD_Controller
{
	public $user_id;
	public $theCredential;

	public function __construct()

	{
		parent::__construct();
		// ini_set('post_max_size', '99500M');
		// ini_set('upload_max_size', '100000M');
		// ini_set('memory_limit', '128M');
		// ini_set('max_execution_time', '5000');
		$this->load->model('PrcQuotationModel');
		$this->load->model('M_DatatablesModel');
		// $this->load->model('ConfigModel');
		$this->load->helper(array('url', 'antech_helper', 'form', 'text',  'security', 'file', 'number', 'date', 'download'));
		$this->load->library('image_lib');
		$this->load->library('upload');
		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id = $this->user_data->id;
	}

	public function list_post()
	{

		$post = $this->post();
		$query  = "SELECT * FROM prc_quotation ";

		$search = array('no_quotation', 'no_referensi');
		$where  = null;

		$isWhere = null;
		// $isWhere = 'artikel.deleted_at IS NULL';

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	function getAll_get()
	{

		$retrieve = $this->PrcQuotationModel->retrieve_all();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => []), REST_Controller::HTTP_OK);
		}
	}
	function url_title($str, $separator = '-', $lowercase = FALSE)
	{
		if ($separator == 'dash') {
			$separator = '-';
		} else if ($separator == 'underscore') {
			$separator = '_';
		}

		$q_separator = preg_quote($separator);

		$trans = array(
			'&.+?;'                 => '',
			'[^a-z0-9 _-]'          => '',
			'\s+'                   => $separator,
			'(' . $q_separator . ')+'   => $separator
		);

		$str = strip_tags($str);

		foreach ($trans as $key => $val) {
			$str = preg_replace("#" . $key . "#i", $val, $str);
		}

		if ($lowercase === TRUE) {
			$str = strtolower($str);
		}

		return trim($str, $separator);
	}

	function get_path_image($img = '', $size = '')
	{
		if (empty($size)) {
			return  $_SERVER['DOCUMENT_ROOT'] . "/"  . 'hcis_folder' . '/userfiles/images/' . $img;
		} else {
			$pisah = explode('.', $img);
			$ext = end($pisah);
			$nama_file = $pisah[0];

			return  $_SERVER['DOCUMENT_ROOT'] . "/"  . 'hcis_folder' . '/userfiles/images/' . $nama_file . '_' . $size . '.' . $ext;
		}
	}
	function index_post()
	{

		$config['upload_path']   = $_SERVER['DOCUMENT_ROOT'] . "/" . 'hcis_folder' . "/userfiles/files/";
		//$config['allowed_types'] = 'doc|zip|rar|txt|docx|xls|xlsx|pdf|tar|gz|jpg|jpeg|JPG|JPEG|png|ppt|pptx';
		$config['max_size']      = '0';
		$config['max_width']     = '0';
		$config['max_height']    = '0';
		$config['detect_mime']          = false;
		$config['file_name']     = url_title('quotation_' . $this->post('no_quotation', TRUE) . '_' . time(), '_', TRUE);
		$this->upload->initialize($config);
		$error_upload = array();
		$this->upload->set_allowed_types('*');
		//move_uploaded_file($_FILES['userfile']['tmp_name'],  $_SERVER['DOCUMENT_ROOT'] . "/" . 'hcis_folder' . "/userfiles/test.jpg");
		if ($this->upload->do_upload()) {
			$error_upload = $this->upload->display_errors();
			$upload_data = $this->upload->data();
			$file        = $upload_data['file_name'];
			$upload_data = array('upload_data' => $this->upload->data());
			// var_dump($upload_data);
		} else {
			$error_upload = $this->upload->display_errors();
			$upload_data = null;
		}

		$input = $this->post();
		$input['dibuat_oleh'] = $this->user_id;
		$input['diubah_oleh'] = $this->user_id;
		$input['upload_file'] = $file;
		// var_dump($input);exit();
		if ($upload_data != null) {
			$idnew = $this->PrcQuotationModel->create($input, $upload_data);

			$message = array();
			if ($idnew) {
				$message = [
					'status' => "OK",
					'id' =>  $idnew,
					'error' => ($_FILES),
					'er' => $this->upload->display_errors(),
					'upload_data' => $this->upload->data()
				];
			}
		} else {
			$message = [
				'status' => "NOT OK",
				'id' =>  '',
				'error' => ($_FILES),
				'er' => $this->upload->display_errors(),
				'upload_data' => $this->upload->data()
			];
		}
		// $this->set_response($message, REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
		if (!empty($idnew)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'prc_quotation', 'action' => 'new', 'entity_id' => $idnew);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $this->post()['no_transaksi']), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

	}
	function detail_get($id)
	{
		$prc_quotation =   $this->PrcQuotationModel->retrieve($id);

		$prc_quotation['file_info']         = get_file_info($this->get_path_file($prc_quotation['upload_file']));
		$prc_quotation['file_info']['mime'] = get_mime_by_extension($this->get_path_file($prc_quotation['upload_file']));


		if ($prc_quotation) {

			$this->set_response(
				array("status" => "OK", "data" => $prc_quotation),
				REST_Controller::HTTP_OK
			);
		} else {
			$this->set_response([
				'status' => "Not Ok",
				'message' => 'Not Found',
			], REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function get_path_file($file = '')
	{
		//  return './'.USERFILES.'/files/'.$file;
		return	$_SERVER['DOCUMENT_ROOT'] . "/" . 'hcis_folder' . "/userfiles/files/" . $file;
	}
	public function download_get($id)
	{
		$prc_quotation = $this->PrcQuotationModel->retrieve($id);
		if (!empty($prc_quotation['upload_file'])) {
			$target_file = $this->get_path_file($prc_quotation['upload_file']);
			if (!is_file($target_file)) {
				show_error("Maaf file tidak ditemukan." . $target_file);
			}

			$data_file = file_get_contents($target_file); // Read the file's contents
			$name_file = $prc_quotation['upload_file'];

			force_download($name_file, $data_file);
		}
	}


	function update_post($segment_3 = null)
	{

		$id = (int)$segment_3;

		$prc_quotation = $this->PrcQuotationModel->retrieve($id);


		if (empty($prc_quotation)) {
			$this->set_response([
				'status' => false,
				'message' => 'Quotation Tidak ditemukan',
			], REST_Controller::HTTP_NOT_FOUND);
			return;
		}

		$config['upload_path']   = $_SERVER['DOCUMENT_ROOT'] . "/" . "plantation" . "/userfiles/files";
		$config['allowed_types'] = 'doc|zip|rar|txt|docx|xls|xlsx|pdf|tar|gz|jpg|jpeg|JPG|JPEG|png|ppt|pptx';
		$config['max_size']      = '0';
		$config['max_width']     = '0';
		$config['max_height']    = '0';
		$config['file_name']     = url_title('quotation_' . $this->input->post('no_quotation', TRUE) . '_' . time(), '_', TRUE);
		$this->upload->initialize($config);
		$error_upload = array();
		// $er=$this->upload->do_upload();
		// $this->set_response(
		// 	$config['upload_path'],
		// 	REST_Controller::HTTP_CREATED
		// );
		// return;
		if ($this->upload->do_upload()) {
			$upload_data = $this->upload->data();
			$error_upload = $this->upload->display_errors();
		} else {
			$upload_data['file_name'] = $prc_quotation['upload_file'];
			$error_upload = $this->upload->display_errors();
		}
		$file = $upload_data['file_name'];
		$input = $this->post();
		$input['diubah_oleh'] = $this->user_id;
		// $input['upload_file']=$file ;
		$prc_quotation_update = $this->PrcQuotationModel->update($prc_quotation['id'], $input, $file);
		// if ($prc_quotation_update) {
		// 	$message = [
		// 		'status' => "OK",
		// 		'id' => $prc_quotation['id'],
		// 		'error'=>var_dump($error_upload)
		// 	];
		// 	$this->set_response(
		// 		var_dump($error_upload),
		// 		REST_Controller::HTTP_CREATED
		// 	);
		// } else {
		// 	$this->set_response([
		// 		var_dump($error_upload)
		// 	], REST_Controller::HTTP_NOT_FOUND);
		// }

		if ($prc_quotation_update) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'prc_quotation', 'action' => 'edit', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$message = [
				'status' => "OK",
				'id' => $prc_quotation['id'],
				'error' => ($_FILES),
				'er' => $this->upload->display_errors(),
				'upload_data' => $this->upload->data()
			];
			$this->set_response(
				$message,
				REST_Controller::HTTP_CREATED
			);
		} else {
			$this->set_response([
				'status' => 'NOT OK',
				'message' => 'Gagal update',
				'error' => ($_FILES),
				'er' => $this->upload->display_errors(),
				'upload_data' => $this->upload->data()
			], REST_Controller::HTTP_NOT_FOUND);
		}
	}


	function index_delete($id = null)
	{


		$prc_quotation = $this->PrcQuotationModel->retrieve($id);
		$this->PrcQuotationModel->delete($prc_quotation['id']);

		/* start audit trail */
		$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id'=>$id)), 'entity' => 'prc_quotation', 'action' => 'delete', 'entity_id' => $id);
		$this->db->insert('fwk_user_audit', $audit);
		/* end audit trail */

		$message = [
			'status' => "OK",
			'id' => $prc_quotation['id']
		];
		$this->set_response(
			$message,
			REST_Controller::HTTP_CREATED
		);
	}
}
