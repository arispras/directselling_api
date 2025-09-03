<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

class PksTimbanganKirimExport extends Rest_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('PksTimbanganKirimModel');
		$this->load->model('PksSjppModel');
		$this->load->model('M_DatatablesModel');
	}


	function create_post()
	{
		// try {
			$dataTimbanganKirim = $this->post();
			$dataTimbanganKirim['diubah_oleh'] = 1;
			$timbangan = $this->PksTimbanganKirimModel->retrieveByUoid($dataTimbanganKirim['uoid']);
			if (empty($timbangan)) {
				$res=  $this->PksTimbanganKirimModel->create_from_import($dataTimbanganKirim);
				// $dataSjpp = array(
				// 	'mill_id' =>	$dataTimbanganKirim['mill_id'],
				// 	'intruksi_id' =>	$dataTimbanganKirim['instruksi_id'],
				// 	'tanggal' =>	$dataTimbanganKirim['tanggal'],
				// 	'no_surat' =>$dataTimbanganKirim['no_tiket'],//	$dataTimbanganKirim['no_referensi'],no_tiket
				// 	'pks_timbangan_kirim_id' =>	$res	
				// );			
				//  $retSjpp =  $this->PksSjppModel->create_from_import($dataSjpp);				  
			  }else{
				$res=   $this->PksTimbanganKirimModel->update_from_import($timbangan['id'], $dataTimbanganKirim);
			  }
					// save ke tabel surat jalan
			
			$this->set_response(	'OK', REST_Controller::HTTP_OK);
		// } catch (\Throwable $th) {

		// 	$this->set_response(var_dump($th), REST_Controller::HTTP_OK);
		// }
	}

	function index_put($segment_3 = '')
	{
		$input = $this->put();

		$id = (int)$segment_3;
		$kategori = $this->PksTimbanganKirimModel->retrieve($id);
		if (empty($kategori)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$retrieve =   $this->PksTimbanganKirimModel->update($kategori['id'], $input);
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
	}

	function index_delete($segment_3 = '')
	{

		$id = (int)$segment_3;
		$kategori = $this->PksTimbanganKirimModel->retrieve($id);
		if (empty($kategori)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$retrieve =  $this->PksTimbanganKirimModel->delete($kategori['id']);
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
	}
}
