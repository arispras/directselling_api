<?php

defined('BASEPATH') or exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;
use \Firebase\JWT\JWT;

class UserAccess extends BD_Controller
{
	public $data = array();
	function __construct()
	{
		// Construct the parent class
		date_default_timezone_set("Asia/Jakarta");
		parent::__construct();
		$this->load->model('M_main');
		$this->load->model('AuthModel');
		$this->load->model('KaryawanModel');
		$this->load->model('MenuModel');
		$this->load->model('UserAccessModel');
		$this->load->model('NotificationModel');
		$this->load->helper("antech_helper");
		 $this->auth();
	}





	function getMenuAccess_post($user_id)
	{
		// $user_id = $this->post('user_id');
		//$this->data = [];

		$sql = "SELECT a.* ,
		CASE
			 WHEN b.id IS NULL THEN 0
			ELSE 1
		END  as selected,ifnull(b.new_,0)as new_,ifnull(b.edit_,0)as edit_,ifnull(b.delete_,0)as delete_,ifnull(b.print_,0)as print_,
		ifnull(b.posting_,0)as posting_
		  FROM fwk_menu a left join ( select * from fwk_users_acces where user_id=" . $user_id . ")  b on a.id =b.menu_id
		  where parent_id=0 order by sort_order";

		$res =	$this->db->query($sql)->result_array();
		foreach ($res as $node) {
			if ($node['url'] != '') $node['url'] = 'Appl/' . $node['url'] . '.php';
			$this->data[] = $node;
			$this->getTree($node['id'], $user_id);
		}

		$itemsByReference = array();
		// Build array of item references:
		foreach ($this->data as $key => &$item) {
			$itemsByReference[$item['id']] = &$item;
			// Children array:
			$itemsByReference[$item['id']]['children'] = array();
			// Empty data class (so that json_encode adds "data: {}" ) 
			// $itemsByReference[$item['id']]['data'] = new StdClass();
			$itemsByReference[$item['id']]['a_attr'] = new StdClass();
		}

		// Set items as children of the relevant parent item.
		foreach ($this->data as $key => &$item)
			//echo "<pre>";print_R($itemsByReference[$item['parent_id']]);
			if ($item['parent_id'] && isset($itemsByReference[$item['parent_id']])) {
				$itemsByReference[$item['parent_id']]['children'][] = &$item;
			}

		// Remove items that were added to parents elsewhere:
		foreach ($this->data as $key => &$item) {
			if (empty($item['children'])) {

				//$item['a_attr']->href = 'https://www.google.co.id';
				$item['a_attr']->href = $item['url'];
			}
			if ($item['parent_id'] && isset($itemsByReference[$item['parent_id']])) {
				unset($this->data[$key]);
			}

			//echo "<pre>".$key;print_R($data);die;
		}
		$menu = array();
		// $menu=$this->data;
		foreach ($this->data  as $key => &$item) {
			//var_dump('key:'.$key);
			$menu[] = $this->data[$key];
		}



		$this->set_response(array("data" => $menu), REST_Controller::HTTP_OK);
	}
	function getTree($parentId = null, $user_id)
	{

		$sql = "SELECT a.* ,
		CASE
			 WHEN b.id IS NULL THEN 0
			ELSE 1
		END  as selected,ifnull(b.new_,0)as new_,ifnull(b.edit_,0)as edit_,ifnull(b.delete_,0)as delete_,ifnull(b.print_,0)as print_,
		ifnull(b.posting_,0)as posting_
		  FROM fwk_menu a left join ( select * from fwk_users_acces where user_id=" . $user_id . ")  b on a.id =b.menu_id
		  where a.parent_id=" . $parentId . " order by sort_order";


		$res =	$this->db->query($sql)->result_array();

		foreach ($res as $node) {
			if ($node['url'] != '') $node['url'] = 'Appl/' . $node['url'] . '.php';
			$this->data[] = $node;
			$this->getTree($node['id'], $user_id);
		}
	}

	public function save_post()
	{
		$user_id = $this->post('user_id');
		$menus = $this->post('menus', true);
		$this->UserAccessModel->deleteByUserId($user_id);
		if ($menus) {
			foreach ($menus as $menu) {
				$this->UserAccessModel->create($user_id, $menu['id'], $menu['new_'], $menu['edit_'], $menu['delete_'], $menu['print_'],$menu['posting_']);
			}
		}
		$this->set_response(array("status" => "OK", "message" => "Save Successfully "), REST_Controller::HTTP_CREATED);
	}
	function getLocationAccess_post($user_id)
	{
		$sql = "select  a.user_id,a.location_id,b.nama from fwk_users_location a inner join gbm_organisasi b on a.location_id=b.id  where user_id=" . $user_id . ";";

		$retrieve =	$this->db->query($sql)->result_array();
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
	}
	function getAfdelingAccess_post($user_id)
	{
		$sql = "select  a.user_id,a.afdeling_id,b.nama from fwk_users_afdeling a inner join gbm_organisasi b on a.afdeling_id=b.id  where user_id=" . $user_id . ";";

		$retrieve =	$this->db->query($sql)->result_array();
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
	}
	function getKasbankAccess_post($user_id)
	{
		$sql = "select  a.user_id,a.acc_akun_id,b.nama from fwk_users_kasbank a inner join acc_akun b on a.acc_akun_id=b.id  where user_id=" . $user_id . ";";
		$retrieve =	$this->db->query($sql)->result_array();
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
	}
	function getPostingAccess_post($user_id)
	{
		$sql = "select  a.user_id,a.posting_id,b.posting_code as kode,b.posting_name as nama from fwk_users_posting a inner join fwk_posting b on a.posting_id=b.id  where user_id=" . $user_id . ";";

		$retrieve =	$this->db->query($sql)->result_array();
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
	}
	function getPostingAll_get()
	{
		$sql = "select  * from fwk_posting";
		$retrieve =	$this->db->query($sql)->result_array();
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
	}
	function updateLocation_post($user_id)
	{
		//$user_id=$this->post('user_id');
		$locations = $this->post('lokasi_id');
		$this->UserAccessModel->deleteLocationByUserId($user_id);
		foreach ($locations as $location) {
			$this->UserAccessModel->createLocation($user_id, $location);
		}
		$this->set_response(array("status" => "OK", "message" => "Save Successfully "), REST_Controller::HTTP_CREATED);
	}
	function updateAfdeling_post($user_id)
	{
		//$user_id=$this->post('user_id');
		$locations = $this->post('lokasi_id');
		$this->UserAccessModel->deleteAfdelingByUserId($user_id);
		foreach ($locations as $location) {
			$this->UserAccessModel->createAfdeling($user_id, $location);
		}
		$this->set_response(array("status" => "OK", "message" => "Save Successfully "), REST_Controller::HTTP_CREATED);
	}
	function updateKasbank_post($user_id)
	{
		//$user_id=$this->post('user_id');
		$akuns = $this->post('acc_akun_id');
		$this->UserAccessModel->deleteKasbankByUserId($user_id);
		foreach ($akuns as $akun) {
			$this->UserAccessModel->createKasbank($user_id, $akun);
		}
		$this->set_response(array("status" => "OK", "message" => "Save Successfully "), REST_Controller::HTTP_CREATED);
	}
	function updatePosting_post($user_id)
	{
		//$user_id=$this->post('user_id');
		$locations = $this->post('posting_id');
		$this->UserAccessModel->deletePostingByUserId($user_id);
		foreach ($locations as $location) {
			$this->UserAccessModel->createPosting($user_id, $location);
		}
		$this->set_response(array("status" => "OK", "message" => "Save Successfully "), REST_Controller::HTTP_CREATED);
	}
}
