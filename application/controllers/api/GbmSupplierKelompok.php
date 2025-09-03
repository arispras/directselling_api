<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Restserver\Libraries\REST_Controller;

class GbmSupplierKelompok extends Rest_Controller
{
    function __construct()
    {
        parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('GbmSupplierKelompokModel');
		$this->load->model('KaryawanModel');
		$this->load->model('M_DatatablesModel');
		$this->load->library('image_lib');
		$this->load->library('upload');
		
    }

	public function list_post()
	{
		$post = $this->post();

		$query  = "select * from gbm_supplier_kelompok ";
		$search = array('nama_kelompok');
		$where  = null;
	   
		$isWhere = null;
		// $isWhere = 'artikel.deleted_at IS NULL';

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
    function index_get($segment_3 = '')
    {
		$id = $segment_3;
		$retrieve = $this->GbmSupplierKelompokModel->retrieve($id);
		
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
     }
	 function getAll_get()
	 {
		
		 $retrieve = $this->GbmSupplierKelompokModel->retrieve_all();
				 
		 if (!empty($retrieve)) {
			 $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		 } else {
			 $this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		 }
	  }
    function create_post()
    {
		

		$retrieve=  $this->GbmSupplierKelompokModel->create($this->post());
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);

	}

    function update_post($segment_3 = '')
    {
      

        $id = (int)$segment_3;
        $item = $this->GbmSupplierKelompokModel->retrieve( $id);
        if (empty($item)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);

        }

         $retrieve=   $this->GbmSupplierKelompokModel->update($item['id'], $this->post());
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);

          

      
    }

    function index_delete($segment_3 = '')
    {
      
		$id = (int)$segment_3;
        $item = $this->GbmSupplierKelompokModel->retrieve ($id);
        if (empty($item)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);

        }

         $retrieve=  $this->GbmSupplierKelompokModel->delete($item['id']);
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

		$config['upload_path']   =$this->get_path_file();
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
			$excel = $this->import($config['upload_path'].'/' . $filename);
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
						$lembur_perjam=$data['H'];
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
								'lembur_perjam'=>$lembur_perjam,
								'tanggal' => $tanggal,
								'jumlah_jam'=>$jumlah_jam
							);

							# simpan data absen
							$id = $this->GbmSupplierKelompokModel->create(
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
