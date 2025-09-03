<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

class PksTankiFormula3 extends BD_Controller
{
	public $user_id;
	public $theCredential;
	
	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('PksTankiFormula3Model');
		$this->load->model('M_DatatablesModel');
		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id=$this->user_data->id;
	}
	public function list_post()
	{
		$post = $this->post();

		$query  = "SELECT a.*, b.nama_tanki AS tangki 
		FROM pks_tanki_formula3 a INNER JOIN pks_tanki b ON a.tanki_id=b.id
		";
		$search = array('kode_tanki');
		$where  = null;
		$isWhere = null;
		// $isWhere = 'artikel.deleted_at IS NULL';

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}

	function index_get($segment_3 = '')
	{
		$id = $segment_3;
		$retrieve = $this->PksTankiFormula3Model->retrieve($id);
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	//  endpoint/getall :GET
	function getAll_get()
	{
		$retrieve = $this->PksTankiFormula3Model->retrieve_all_kategori();
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
		$res = $this->PksTankiFormula3Model->create($input);
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'pks_tanki_formula_3', 'action' => 'new', 'entity_id' => $res);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $input['no_transaksi']), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	// endpoint/ :PUT
	function index_put($segment_3 = '')
	{
		$id = (int)$segment_3;
		$gudang = $this->PksTankiFormula3Model->retrieve($id);
		if (empty($gudang)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
		$input = $this->put();
		$retrieve = $this->PksTankiFormula3Model->update($gudang['id'], $input);
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
	}

	// endpoint/ :DELETE
	function index_delete($segment_3 = '')
	{
		$id = (int)$segment_3;
		$gudang = $this->PksTankiFormula3Model->retrieve($id);
		if (empty($gudang)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
		$retrieve =  $this->PksTankiFormula3Model->delete($gudang['id']);
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
	}



}
