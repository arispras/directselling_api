<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

class UserAudit extends Rest_Controller
{
	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('M_DatatablesModel');
		$this->load->model('UserAccessModel');
	}

	// endpoint/list :POST
	public function list_post()
	{
		$post = $this->post();
		$query  = "SELECT a.*, b.user_name as username  
		FROM fwk_user_audit a
		INNER JOIN fwk_users b ON a.user_id=b.id";

		$search = array('a.key_text','b.user_name', 'a.entity', 'a.last_modified', 'a.desc', 'a.action');
		$where  = null;
		$isWhere = null;
		// $isWhere = 'artikel.deleted_at IS NULL';
		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	public function delete_audit_all_post()
	{
		$res = $this->UserAccessModel->delete_audit_all();
		if (($res)) {
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => null), REST_Controller::HTTP_NOT_FOUND);
		}
	}
}
