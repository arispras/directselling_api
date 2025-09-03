<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

class AccMataUang extends Rest_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('AccMataUangModel');
		$this->load->model('M_DatatablesModel');
	}

	// endpoint/list :POST
	public function list_post()
	{
		$post = $this->post();
		$query  = "SELECT 
			a.*,
			-- b.kode as kode_akun,
			-- b.nama as nama_akun 
			FROM acc_mata_uang a 
			-- left join acc_akun b on a.acc_akun_id=b.id
		";

		$search = array('a.nama','a.kode');
		$where  = null;
		$isWhere = null;
		// $isWhere = 'artikel.deleted_at IS NULL';
		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}

	// endpoint/ :GET
	function index_get($segment_3 = '')
	{
		$id = $segment_3;
		$retrieve = $this->AccMataUangModel->retrieve($id);
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	//  endpoint/getall :GET
	function getAll_get()
	{
		$retrieve = $this->AccMataUangModel->retrieve_all_kategori();
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	// endpoint/ :POST
	function index_post()
	{
		$input = $this->post();
		$retrieve = $this->AccMataUangModel->create($input);
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
	}

	// endpoint/ :PUT
	function index_put($segment_3 = '')
	{
		$id = (int)$segment_3;
		$gudang = $this->AccMataUangModel->retrieve($id);
		if (empty($gudang)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
		$input = $this->put();
		$retrieve = $this->AccMataUangModel->update($gudang['id'], $input);
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
	}

	// endpoint/ :DELETE
	function index_delete($segment_3 = '')
	{
		$id = (int)$segment_3;
		$gudang = $this->AccMataUangModel->retrieve($id);
		if (empty($gudang)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
		$retrieve =  $this->AccMataUangModel->delete($gudang['id']);
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
	}
}
