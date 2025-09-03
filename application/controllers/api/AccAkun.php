<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

class AccAkun extends BD_Controller //Rest_Controller
{
	public $user_id;
	public $theCredential;
	public $data = array();
	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('AccAkunModel');
		$this->load->model('M_DatatablesModel');
		$this->load->library('pdfgenerator');
		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id = $this->user_data->id;
	}

	public function list_post()
	{
		$post = $this->post();

		$query  = "SELECT a.*,
		f.user_full_name AS dibuat,
		g.user_full_name AS diubah from acc_akun a
		LEFT JOIN fwk_users f ON a.dibuat_oleh = f.id
		LEFT JOIN fwk_users g ON a.diubah_oleh = g.id ";
		$search = array('kode', 'nama');
		$where  = null;

		$isWhere = null;
		// $isWhere = 'artikel.deleted_at IS NULL';

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	function index_get($segment_3 = '')
	{
		$id = $segment_3;
		$retrieve = $this->AccAkunModel->retrievebyId($id);
		$sql = "select  a.*,b.nama as lokasi from acc_akun_dt a inner join gbm_organisasi b on a.lokasi_id=b.id where acc_akun_id=" . $id . ";";
		$retrieve['detail'] =	$this->db->query($sql)->result_array();


		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAll_get()
	{

		$retrieve = $this->AccAkunModel->retrieve_all_akun();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAllByLokasiId_get($lokasi_id)
	{

		$retrieve = $this->AccAkunModel->retrieve_all_akun_by_lokasi_id($lokasi_id);

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAllDetail_get()
	{

		$retrieve = $this->AccAkunModel->retrieve_all_akun_detail();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAllKasbank_get()
	{

		$retrieve = $this->AccAkunModel->retrieve_all_akun_kasbank();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAllSupplier_get()
	{

		$retrieve = $this->AccAkunModel->retrieve_all_akun_supplier();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAllKasbankByAccess_get()
	{
		$user_id = $this->user_id;
		$retrieve = $this->AccAkunModel->retrieve_all_akun_kasbank_by_acces($user_id);

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAllKasbankByLokasiId_get($lokasi_id)
	{

		$retrieve = $this->AccAkunModel->retrieve_all_akun_kasbank_by_lokasi_id($lokasi_id);

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
		$retrieve =  $this->AccAkunModel->create($input);
		
		/* start audit trail */
		$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'acc_akun', 'action' => 'new', 'entity_id' => $retrieve);
		$this->db->insert('fwk_user_audit', $audit);
		/* end audit trail */
		
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
	}

	function index_put($segment_3 = '')
	{


		$id = (int)$segment_3;
		$akun = $this->AccAkunModel->retrieve(array('id' => $id));
		if (empty($akun)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
		$input = $this->put();
		$input['diubah_oleh'] = $this->user_id;
		$retrieve =   $this->AccAkunModel->update($akun['id'], $input);
		/* start audit trail */
		$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'acc_akun', 'action' => 'edit', 'entity_id' => $akun['id']);
		$this->db->insert('fwk_user_audit', $audit);
		/* end audit trail */
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
	}

	function index_delete($segment_3 = '')
	{
		
		$id = (int)$segment_3;
		$akun = $this->AccAkunModel->retrieve(array('id' => $id));
		if (empty($akun)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$retrieve =  $this->AccAkunModel->delete($akun['id']);
		/* start audit trail */
		$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->delete()), 'entity' => 'acc_akun', 'action' => 'delete', 'entity_id' => $akun['id']);
		$this->db->insert('fwk_user_audit', $audit);
		/* end audit trail */
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
	}

	function detail($segment_3 = '')
	{
	}


	function export_all_get($segment_3 = '')
	{
		$this->load->helper("terbilang");
		$id = (int)$segment_3;
		$data = [];

		$Data = $this->db->query("SELECT
			* 
			FROM 
			acc_akun
			ORDER BY
			kode
			")->result_array();
		$data['data'] = $Data;

		$html = $this->load->view('AccAkun_export_all', $data, true);

		$filename = 'export_AccAkun_' . time();
		$this->pdfgenerator->generate($html, $filename, true, 'A4', 'portrait');

		echo $html;
	}

	function import_akun_dt_get()
	{

		$r_akun = $this->db->query("select * from acc_akun where is_transaksi_akun=1")->result_array();
		foreach ($r_akun as $key => 	$a) {
			$r_lokasi = $this->db->query("select * from gbm_organisasi where tipe in('MILL','ESTATE','HO','RO')")->result_array();
			foreach ($r_lokasi as $key => 	$l) {
				$data = array("lokasi_id" => $l['id'], "acc_akun_id" => $a['id']);
				$this->db->insert('acc_akun_dt', $data);
			}
		}
	}
}
