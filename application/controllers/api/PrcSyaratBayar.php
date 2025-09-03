<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
use Restserver\Libraries\REST_Controller;

class PrcSyaratBayar extends BD_Controller
{
	public $user_id;
	public $theCredential;

	function __construct()
	{
		parent::__construct();
		$this->load->model('PrcSyaratBayarModel');
		$this->load->model('M_DatatablesModel');
		$this->load->library('pdfgenerator');
		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id = $this->user_data->id;
	}
	
	public function list_post()
	{
		$post = $this->post();
		
		$query  = "SELECT * from prc_syarat_bayar";
		$search = array('kode','jenis','ket');
		$where  = null;
		
		$isWhere = null;
		// $isWhere = 'artikel.deleted_at IS NULL';
		
		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	function index_get($segment_3 = '')
	{
		$id = $segment_3;
		$retrieve = $this->PrcSyaratBayarModel->retrieve($id);
		
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAll_get()
	{
		
		$retrieve = $this->PrcSyaratBayarModel->retrieve_all_kategori();
		
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function index_post()
	{
		$input = $this->post();

		$retrieve=  $this->PrcSyaratBayarModel->create($input);
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		
	}
	
	function index_put($segment_3 = '')
	{
		$input = $this->put();
		
		$id = (int)$segment_3;
		$kategori = $this->PrcSyaratBayarModel->retrieve( $id);
		if (empty($kategori)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);	
		}
		
		$retrieve=   $this->PrcSyaratBayarModel->update($kategori['id'], $input );
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		
	}
	
	function index_delete($segment_3 = '')
	{
		
		$id = (int)$segment_3;
		$kategori = $this->PrcSyaratBayarModel->retrieve($id);
		if (empty($kategori)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
			
		}
		
		$retrieve=  $this->PrcSyaratBayarModel->delete($kategori['id']);
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		
		
	}
	
	
	
	
	
	
	function print_slip_get($segment_3 = '')
	{
		
		$id = (int)$segment_3;
		$data = [];
		
		$PrcSyaratBayar = $this->PrcSyaratBayarModel->print_slip( $id );
		$data['PrcSyaratBayar'] = $PrcSyaratBayar;

		$html = $this->load->view('PrcSyaratBayar_print', $data, true);
		
		$filename = 'report_' . time();
		$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
	}
}
