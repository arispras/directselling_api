<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

class InvItem extends BD_Controller
{
	public $user_id;
	public $theCredential;

	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('InvItemModel');
		$this->load->model('M_DatatablesModel');
		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id = $this->user_data->id;
	}

	public function list_post()
	{
		$post = $this->post();

		$query  = "select a.*,b.nama as nama_kategori,c.nama as uom from inv_item a left join inv_kategori b on a.inv_kategori_id=b.id
		left join gbm_uom c on a.uom_id=c.id";
		$search = array('a.kode', 'a.nama', 'b.nama', 'c.nama', 'a.jenis_item');
		$where  = null;

		$isWhere = null;
		// $isWhere = 'artikel.deleted_at IS NULL';

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	function index_get($segment_3 = '')
	{
		$id = $segment_3;
		$retrieve = $this->InvItemModel->retrieve($id);

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAll_get()
	{

		$retrieve = $this->InvItemModel->retrieve_all_item();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAllProduk_get()
	{

		$retrieve = $this->InvItemModel->retrieve_all_item_produk();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAllBahanBkm_get()
	{

		$retrieve = $this->InvItemModel->retrieve_all_item_bahan_bkm();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAllSukuCadang_get()
	{

		$retrieve = $this->InvItemModel->retrieve_all_item_suku_cadang();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function index_post()
	{
		if (is_array($this->post("nama"))) {
			$this->set_response(array("status" => "NOT OK", "data" => "Nama Item Sudah Ada"), REST_Controller::HTTP_OK);
			return;
		}
		$cek = $this->db->query("select * from inv_item where kode='" . $this->post("kode") . "'")->row_array();
		if ($cek) {
			$this->set_response(array("status" => "NOT OK", "data" => "Kode Item Sudah Ada"), REST_Controller::HTTP_OK);
			return;
		}
		$cek = $this->db->query("select * from inv_item where nama='" . $this->post("nama") . "'")->row_array();
		if ($cek) {
			$this->set_response(array("status" => "NOT OK", "data" => "Nama Item Sudah Ada"), REST_Controller::HTTP_OK);
			return;
		}
		$res =  $this->InvItemModel->create($this->post());
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'inv_item', 'action' => 'new', 'entity_id' => $res);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $this->post('kode')), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function index_put($segment_3 = '')
	{


		$id = (int)$segment_3;
		$item = $this->InvItemModel->retrieve($id);
		if (empty($item)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
		if (strtolower($this->put("kode")) != strtolower($item['kode'])) {
			$cek = $this->db->query("select * from inv_item where kode='" . $this->put("kode") . "'")->row_array();
			if ($cek) {
				$this->set_response(array("status" => "NOT OK", "data" => "Kode Item Sudah Ada"), REST_Controller::HTTP_OK);
				return;
			}
		}
		if (strtolower($this->put("nama")) != strtolower($item['nama'])) {
			$cek = $this->db->query("select * from inv_item where nama='" . $this->put("nama") . "'")->row_array();
			if ($cek) {
				$this->set_response(array("status" => "NOT OK", "data" => "Nama Item Sudah Ada"), REST_Controller::HTTP_OK);
				return;
			}
		}
		$res =   $this->InvItemModel->update($item['id'], $this->put());
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'inv_item', 'action' => 'edit', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function index_delete($segment_3 = '')
	{

		$id = (int)$segment_3;
		$item = $this->InvItemModel->retrieve($id);
		if (empty($item)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$res =  $this->InvItemModel->delete($item['id']);
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id' => $id)), 'entity' => 'inv_item', 'action' => 'delete', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}


	private function formatData($val)
	{


		if (!empty($val['inv_kategori_id'])) {
			$inv_kategori = $this->invkategori_model->retrieve($val['inv_kategori_id']);
			$val['inv_kategori'] = $inv_kategori;
		}

		return $val;
	}
}
