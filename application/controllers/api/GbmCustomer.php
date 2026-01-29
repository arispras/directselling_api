<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Restserver\Libraries\REST_Controller;

class GbmCustomer extends BD_Controller
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
		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id = $this->user_data->id;
	}

	public function list_post()
	{
		$post = $this->post();

		// $query  = "select a.*,b.nama_kelompok from gbm_customer a left join gbm_customer_kelompok b on a.kelompok_id=b.id";
		$query  = "SELECT a.*,d.nama as lokasi,e.nama as provinsi,f.nama as kabupaten,
		g.nama as kecamatan,h.nama as kelurahan,
		b.user_full_name AS dibuat,
		c.user_full_name AS diubah 
		from gbm_customer a
		LEFT JOIN fwk_users b ON a.dibuat_oleh = b.id
		LEFT JOIN fwk_users c ON a.diubah_oleh = c.id
		LEFT JOIN gbm_organisasi d ON a.lokasi_id = d.id
		LEFT JOIN gbm_provinsi e ON a.provinsi_id = e.id
		LEFT JOIN gbm_kabupaten f ON a.kabupaten_id = f.id
		LEFT JOIN gbm_kecamatan g ON a.kecamatan_id = g.id	
		LEFT JOIN gbm_kelurahan h ON a.kelurahan_id = h.id
		";
		// $search = array('a.kode_customer', 'a.nama_customer','b.nama_kelompok');
		$search = array('kode_customer', 'nama_customer', 'no_telpon','a.alamat', 'd.nama', 'e.nama', 'f.nama', 'g.nama', 'h.nama');
		$where  = null;

		$isWhere = null;
		// $isWhere = 'artikel.deleted_at IS NULL';

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	function index_get($segment_3 = '')
	{
		$id = $segment_3;
		$retrieve = $this->GbmCustomerModel->retrieve($id);
		$retrieve['detail'] = $this->GbmCustomerModel->retrieve_detail($id);
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function alamat_get($cust_id = '')
	{
		$id = $cust_id;

		$retrieve = $this->GbmCustomerModel->retrieve_detail($id);
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
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function create_post()
	{

		$data = $this->post();
		$data['dibuat_oleh'] = $this->user_id;
		$this->load->library('Autonumber');
		$data['kode_customer'] = $this->autonumber->gbm_customer();
		$retrieve =  $this->GbmCustomerModel->create($data);
		$this->set_response(array("status" => "OK", "data" => (array("id" => $retrieve, "nama" => $data['nama_customer'], "kode" => $data['kode_customer']))), REST_Controller::HTTP_OK);
	}

	function update_post($segment_3 = '')
	{


		$id = (int)$segment_3;
		$item = $this->GbmCustomerModel->retrieve($id);
		if (empty($item)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$data = $this->post();
		$data['diubah_oleh'] = $this->user_id;
		$retrieve =   $this->GbmCustomerModel->update($item['id'], $data);
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
	}

	function index_delete($segment_3 = '')
	{

		$id = (int)$segment_3;
		$item = $this->GbmCustomerModel->retrieve($id);
		if (empty($item)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$retrieve =  $this->GbmCustomerModel->delete($item['id']);
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
	}


	function get_path_file($img = '', $size = '')
	{
		if (empty($size)) {
			return  $_SERVER['DOCUMENT_ROOT'] . "/"  . 'hrms' . '/userfiles/files/' . $img;
		} else {
			$pisah = explode('.', $img);
			$ext = end($pisah);
			$nama_file = $pisah[0];

			return  $_SERVER['DOCUMENT_ROOT'] . "/"  . 'hrms' . '/userfiles/files/' . $nama_file . '_' . $size . '.' . $ext;
		}
	}
	function import_post()
	{

		$config['upload_path']   = $this->get_path_file();
		$config['allowed_types'] = 'Xls|xls';
		// $config['max_size']      = '0';
		// $config['max_width']     = '0';
		// $config['max_height']    = '0';
		$config['overwrite'] = true;
		$config['file_name']     = 'import_siswa.xls';
		$this->upload->initialize($config);


		if ($this->upload->do_upload()) {
			$upload_data = $this->upload->data();
			$filename = $upload_data['file_name'];
			$excel = array();
			$excel = $this->import($config['upload_path'] . '/' . $filename);
			//   print_r(count( $excel));
			if (count($excel) > 0) {
				for ($i = 2; $i < (count($excel) + 1); $i++) {
					$data = $excel[$i];
					if (!empty($data['A'])) {

						$nip          =  $data['A'];
						$tanggal         =  $data['B'];
						$mulai =  $data['C'];
						$selesai  =  $data['D'];
						$nama_lokasi   =  $data['E'];
						$nilai_lembur      =  $data['G'];
						$jumlah_jam      = $data['F'];
						$lembur_perjam = $data['H'];
						$result = $this->db->query("select * from karyawan where nip='" . $nip . "'");
						$karyawan = $result->row_array();
						$result = $this->db->query("select * from payroll_lokasi where nama='" . $nama_lokasi . "'");
						$lokasi = $result->row_array();
						if (empty($karyawan)) {
						} else {

							$karyawan_id      = $karyawan['id'];
							$lokasi_id      = $lokasi['id'];
							$data_save = array(
								'selesai' => $selesai,
								'mulai' => $mulai,
								'karyawan_id'    => $karyawan_id,
								'lokasi_id'    => $lokasi_id,
								'nilai_lembur' => $nilai_lembur,
								'lembur_perjam' => $lembur_perjam,
								'tanggal' => $tanggal,
								'jumlah_jam' => $jumlah_jam
							);

							# simpan data absen
							$id = $this->GbmCustomerModel->create(
								$data_save
							);
						}
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
