<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Restserver\Libraries\REST_Controller;
use \Firebase\JWT\JWT;

defined('BASEPATH') or exit('No direct script access allowed');
// require APPPATH . 'libraries/REST_Controller.php';
// require APPPATH . 'libraries/Format.php';


class User extends BD_Controller
{
	public $user_id;
	public $theCredential;
	public function __construct()
	{
		parent::__construct();
		$this->load->model('UserModel');
		$this->load->model('M_DatatablesModel');
		$this->load->library('image_lib');
		$this->load->library('upload');
		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id = $this->user_data->id;
	}
	function create_img_thumb($source_path = '', $marker = '_thumb', $width = '90', $height = '90')
	{
		$config['image_library']  = 'gd2';
		$config['source_image']   = $source_path;
		$config['create_thumb']   = TRUE;
		$config['maintain_ratio'] = TRUE;
		$config['width']          = $width;
		$config['height']         = $height;
		$config['thumb_marker']   = $marker;

		$this->image_lib->initialize($config);
		$this->image_lib->resize();
		$this->image_lib->clear();
		unset($config);

		return true;
	}
	function get_path_image($img = '', $size = '')
	{
		if (empty($size)) {
			return  $_SERVER['DOCUMENT_ROOT'] . "/"  . $_SERVER["HTTP_NAMAPATH"] . '/userfiles//images/' . $img;
		} else {
			$pisah = explode('.', $img);
			$ext = end($pisah);
			$nama_file = $pisah[0];

			return  $_SERVER['DOCUMENT_ROOT'] . "/"  . $_SERVER["HTTP_NAMAPATH"] . '/userfiles/images/' . $nama_file . '_' . $size . '.' . $ext;
		}
	}
	public function list_post($status)
	{
		$post = $this->post();

		$query  = "SELECT a.*,b.nama as employee_name,c.nama AS jabatan,d.nama AS lokasi_tugas,
		e.nama AS sub_bagian,b.foto  from fwk_users a 
		left join karyawan b on a.employee_id=b.id
		LEFT JOIN payroll_jabatan c ON b.jabatan_id=c.id
		LEFT JOIN gbm_organisasi d ON b.lokasi_tugas_id=d.id
			LEFT JOIN gbm_organisasi e ON b.sub_bagian_id=e.id";
		$search = array('a.user_full_name', 'a.user_name', 'a.user_email', 'b.nama','c.nama','d.nama','e.nama');
		//$where  = null;
		$where  = array('a.status' => $status);
		$isWhere = null;


		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	public	function index_get($segment_3 = '')
	{

		$id = $segment_3;
		$retrieve_user = $this->UserModel->retrieve($id);



		if (!empty($retrieve_user)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve_user), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => null), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	public	function index_delete($segment_3 = '')
	{

		$id = $segment_3;
		$user_id       = (int)$segment_3;
		$retrieve_user = $this->UserModel->retrieve($user_id);
		if (empty($retrieve_user)) {
			$this->set_response(array("status" => "NOT OK", "data" => "NOT FOUND User"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		// $retrieve_login = $this->LoginModel->retrieve(null, null, null, null, $retrieve_user['id']);

		$res = $this->UserModel->delete($id);
		if (($res)) {
			// $retrieveLogin = $this->LoginModel->delete($retrieve_login['id']);
			$audit = array('user_id' => $this->user_id, 'desc' => '', 'entity' => 'users', 'action' => 'delete', 'entity_id' => $user_id);
			$this->db->insert('fwk_user_audit', $audit);
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => null), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function index_post()
	{


		$user_name          = $this->post('user_name', TRUE);
		$user_full_name          = $this->post('user_full_name', TRUE);
		$user_email = $this->post('user_email', TRUE);
		$user_password  = $this->post('user_password', TRUE);
		$employee_id   = $this->post('employee_id', TRUE);
		$status      = $this->post('status', TRUE);

		# simpan data user
		$user_id = $this->UserModel->create(
			$user_full_name,
			$user_name,
			$user_password,
			$user_email,
			$status,
			$employee_id
		);

		if (!empty($user_id)) {
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'users', 'action' => 'new', 'entity_id' => $user_id);
			$this->db->insert('fwk_user_audit', $audit);
			$this->set_response(array("status" => "OK", "data" => $user_id), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => null), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	public	function index_put($segment_3 = '')
	{

		$user_id       = (int)$segment_3;
		$retrieve_user = $this->UserModel->retrieve($user_id);
		if (empty($retrieve_user)) {
			$this->set_response(array("status" => "NOT OK", "data" => "NOT FOUND user"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$user_name          = $this->put('user_name', TRUE);
		$user_full_name          = $this->put('user_full_name', TRUE);
		$user_email = $this->put('user_email', TRUE);
		$user_password  = $this->put('user_password', TRUE);
		$employee_id   = $this->put('employee_id', TRUE);
		$status      = $this->put('status', TRUE);

		$this->UserModel->update(
			$user_id,
			$user_full_name,
			$user_name,
			$retrieve_user['user_password'],
			$user_email,
			$status,
			$employee_id
		);
		$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'users', 'action' => 'edit', 'entity_id' => $user_id);
		$this->db->insert('fwk_user_audit', $audit);
		$this->set_response(array("status" => "OK", "data" => $user_id), REST_Controller::HTTP_CREATED);
	}
	public	function update_password_put($segment_3 = '')
	{

		$user_id       = (int)$segment_3;
		$user_password  = $this->put('user_password', TRUE);
		$retrieve_user = $this->UserModel->retrieve($user_id);
		if (empty($retrieve_user)) {
			$this->set_response(array("status" => "NOT OK", "data" => "NOT FOUND user"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}

		$user_password  = $this->put('user_password', TRUE);
		$this->UserModel->update_password(
			$user_id,
			$user_password

		);
		$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'users', 'action' => 'edit', 'entity_id' => $user_id);
		$this->db->insert('fwk_user_audit', $audit);
		$this->set_response(array("status" => "OK", "data" => $user_id), REST_Controller::HTTP_CREATED);
	}

	public	function update_password_login_put()
	{

		$user_id       = $this->user_id;
		$user_password  = $this->put('user_password', TRUE);
		$retrieve_user = $this->UserModel->retrieve($user_id);
		if (empty($retrieve_user)) {
			$this->set_response(array("status" => "NOT OK", "data" => "NOT FOUND user"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}

		$user_password  = $this->put('user_password', TRUE);
		$this->UserModel->update_password(
			$user_id,
			$user_password

		);
		$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'users', 'action' => 'edit', 'entity_id' => $user_id);
		$this->db->insert('fwk_user_audit', $audit);
		$this->set_response(array("status" => "OK", "data" => $user_id), REST_Controller::HTTP_CREATED);
	}


	function update_status_post()
	{


		$post_status_id = $this->post('status_id', TRUE);
		$user_ids      = $this->post('user_id', TRUE);
		foreach ($user_ids as $user_id) {
			$retrieve_user = $this->UserModel->retrieve($user_id);
			if (!empty($retrieve_user)) {
				# update user
				$this->UserModel->update(
					$retrieve_user['id'],
					$retrieve_user['nis'],
					$retrieve_user['nama'],
					$retrieve_user['jenis_kelamin'],
					$retrieve_user['tempat_lahir'],
					$retrieve_user['tgl_lahir'],
					$retrieve_user['agama'],
					$retrieve_user['alamat'],
					$retrieve_user['tahun_masuk'],
					$retrieve_user['foto'],
					$post_status_id
				);

				if ($retrieve_user['status_id'] == 0 && $post_status_id == 1) {
					//	@kirim_email_approve_user($retrieve_user['id']);
				}
			}
		}
		$this->set_response(array("status" => "OK", "data" => []), REST_Controller::HTTP_CREATED);
	}






	function edit_picture_post($segment_3 = '')
	{

		$user_id       = (int)$segment_3;
		$retrieve_user = $this->UserModel->retrieve($user_id);

		if (empty($retrieve_user)) {
			$this->set_response(array("status" => "NOT OK", "data" => "NOT FOUND DATA"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}


		$data['user_id']  = $user_id;
		$data['user']     = $retrieve_user;

		$config['upload_path']   = $this->get_path_image();
		$config['allowed_types'] = 'jpg|jpeg|png';
		$config['max_size']      = '0';
		$config['max_width']     = '0';
		$config['max_height']    = '0';
		$config['file_name']     = 'user-' . url_title($retrieve_user['nama'], '-', true) . '-' . url_title($retrieve_user['nis'], '-', true);
		$this->upload->initialize($config);

		if ($this->upload->do_upload()) {

			if (is_file($this->get_path_image($retrieve_user['foto']))) {
				unlink($this->get_path_image($retrieve_user['foto']));
			}

			if (is_file($this->get_path_image($retrieve_user['foto'], 'medium'))) {
				unlink($this->get_path_image($retrieve_user['foto'], 'medium'));
			}

			if (is_file($this->get_path_image($retrieve_user['foto'], 'small'))) {
				unlink($this->get_path_image($retrieve_user['foto'], 'small'));
			}

			$upload_data = $this->upload->data();

			# create thumb small
			$this->create_img_thumb(
				$this->get_path_image($upload_data['file_name']),
				'_small',
				'50',
				'50'
			);

			# create thumb medium
			$this->create_img_thumb(
				$this->get_path_image($upload_data['file_name']),
				'_medium',
				'150',
				'150'
			);

			# update user
			$this->UserModel->update(
				$user_id,
				$retrieve_user['nis'],
				$retrieve_user['nama'],
				$retrieve_user['jenis_kelamin'],
				$retrieve_user['tempat_lahir'],
				$retrieve_user['tgl_lahir'],
				$retrieve_user['agama'],
				$retrieve_user['alamat'],
				$retrieve_user['tahun_masuk'],
				$upload_data['file_name'],
				$retrieve_user['status_id']
			);
			$this->set_response(array("status" => "OK", "data" => $user_id), REST_Controller::HTTP_CREATED);
		} else {
			if (!empty($_FILES['userfile']['tmp_name'])) {
				$this->set_response(array("status" => "NOT OK", "data" => ""), REST_Controller::HTTP_NO_CONTENT);
			}
		}
	}

	public function getUserProfileLogin_get(){

		$datauser= $this->db->query("SELECT a.user_full_name,b.email,b.nama, IFNULL( SUM(c.jumlah),0) as saldo ,b.jenis_kelamin,b.tgl_lahir,b.foto,b.alamat,b.tempat_lahir,b.jabatan_id,b.email FROM fwk_users a  
		INNER JOIN karyawan b on a.employee_id=b.id INNER JOIN hrms_cuti_saldo c on c.karyawan_id=b.id WHERE a.id=".$this->user_id."" )->row_array();

		$this->set_response(array("status" => "OK", "data" => $datauser), REST_Controller::HTTP_CREATED);

	}

	public function getAll_get()
	{ //Request Method select ALL


		$user = $this->UserModel->retrieve_all_user();


		if ($user) {

			$this->set_response(
				$user,
				REST_Controller::HTTP_OK
			);
		} else {
			$this->set_response([
				'status' => false,
				'message' => 'Err',
			], REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function import_post()
	{

		$config['upload_path']   = $_SERVER['DOCUMENT_ROOT'] . "/" . $_SERVER["HTTP_NAMAPATH"];
		$config['allowed_types'] = 'Xls|xls';
		// $config['max_size']      = '0';
		// $config['max_width']     = '0';
		// $config['max_height']    = '0';
		$config['overwrite'] = true;
		$config['file_name']     = 'import_user.xls';
		$this->upload->initialize($config);


		if ($this->upload->do_upload()) {
			$upload_data = $this->upload->data();
			$filename = $upload_data['file_name'];
			$excel = array();
			$excel = $this->import($_SERVER['DOCUMENT_ROOT'] . "/" . $_SERVER["HTTP_NAMAPATH"] .  '/' . $filename);
			//   print_r(count( $excel));
			if (count($excel) > 0) {
				for ($i = 2; $i < (count($excel) + 1); $i++) {
					$data = $excel[$i];
					if (!empty($data['A'])) {
						$nis          =  $data['A'];
						$nama          =  $data['B'];
						$jenis_kelamin =  $data['C'];
						$tempat_lahir  =  $data['D'];
						$tahun_masuk   =  $data['E'];
						$kelas      =  $data['F'];
						$status_id      = $data['G'];
						$tgl_lahir     =  $data['H'];
						// $bln_lahir     =  $data['I'];
						// $thn_lahir     =  $data['J'];
						$alamat        =  $data['I'];
						$agama         = $data['J'];
						$username      =  $data['K'];
						$password      =  $data['L'];




						# simpan data user
						$user_id = $this->UserModel->create(
							$nis,
							$nama,
							$jenis_kelamin,
							$tempat_lahir,
							$tgl_lahir,
							$agama,
							$alamat,
							$tahun_masuk,
							'',
							$status_id
						);

						
					}
				}
			}
			//  var_dump($excel);
			//  exit();
			$this->set_response([
				'status' => 'OK',
				'message' => 'Data berhasil diimport',
			], REST_Controller::HTTP_CREATED);
		} else {
			if (!empty($_FILES['userfile']['tmp_name'])) {
				$this->set_response([
					'status' => 'NOT OK',
					'message' => 'Gagal import',
				], REST_Controller::HTTP_NOT_FOUND);
			}
		}
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
