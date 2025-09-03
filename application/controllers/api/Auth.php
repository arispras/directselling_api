<?php

defined('BASEPATH') or exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;
use \Firebase\JWT\JWT;

class Auth extends BD_Controller
{
	public $data = array();
	public $user_id;
	public $theCredential;
	function __construct()
	{
		// Construct the parent class
		// error_reporting(0);
		date_default_timezone_set("Asia/Jakarta");
		parent::__construct();
		// Configure limits on our controller methods
		// Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
		// $this->methods['users_get']['limit'] = 500; // 500 requests per hour per user/key
		// $this->methods['users_post']['limit'] = 100; // 100 requests per hour per user/key
		// $this->methods['users_delete']['limit'] = 50; // 50 requests per hour per user/key
		$this->load->model('M_main');
		$this->load->model('AuthModel');
		$this->load->model('KaryawanModel');
		$this->load->model('MenuModel');
		$this->load->model('UserAccessModel');
		$this->load->model('NotificationModel');
		$this->load->helper("antech_helper");
		// $this->auth();

	}



	public function login_post()
	{
		$u = $this->post('username'); //Username Posted
		$p = $this->post('password'); //Pasword Posted
		// $q = array('username' => $u); //For where query condition
		$kunci = $this->config->item('thekey');
		// $invalidLogin = ['status' => 'Invalid Login']; //Respon if login invalid
		// $val = $this->M_main->get_user($q)->row(); //Model to get single data row from database base on username
		$res = $this->AuthModel->Login($u, $p);
		//  var_dump($res);
		if ($res['status'] == 'OK') {
			$data = array();
			$token['id'] = $res['data']['id'];
			$token['username'] = $u;
			$date = new DateTime();
			$token['iat'] = $date->getTimestamp();
			// $token['exp'] = $date->getTimestamp() + 60 * 60 * 5; //To here is to generate token
			$token['exp'] = $date->getTimestamp() + 60 * 60 * 1000; //To here is to generate token
			// $token['type'] = 'karyawan';
			// $token['uniqid'] = $res['data']['id'];	
			$login_id = $this->AuthModel->create_log($res['data']['id']);
			$token['login_id'] = $login_id; // id login_log (bukan uer_id)
			$data['token'] = JWT::encode($token, $kunci);
			$data['data'] = $res['data'];
			$data['status'] = "OK";
			// $role=$this->KaryawanModel->retrieve_role( $res['data']['uniqid']);
			// $data['role'] =$role;
			$data['nama_company'] = get_pengaturan('nama-company', 'value');
			$data['logo_company'] = get_pengaturan('logo-company', 'value'); //get_logo_config();
			$data['alamat'] = get_pengaturan('alamat', 'value');
			$data['telp'] = get_pengaturan('telp', 'value');
			$data['whatsapp'] = get_pengaturan('whatsapp', 'value');
			$menu = $this->UserAccessModel->retrieveMenuByUserId($res['data']['id']);
			$data['menu'] = $menu;
			$this->set_response($data, REST_Controller::HTTP_OK); //This is the respon if success

			// }
			// elseif($res['status']=='NOT_AKTIF') {
			// 	$respon=array("status"=>"NOT_AKTIF","data"=>null,"message"=>"User Not Aktif ");
			// 	$this->response($respon, REST_Controller::HTTP_OK);
		} elseif ($res['status'] == 'NOT_FOUND') {
			$respon = array("status" => "NOT_FOUND", "data" => null, "message" => "Periksa kembali email/password anda ");
			$this->response($respon, REST_Controller::HTTP_OK);
		} elseif ($res['status'] == 'NOT_AKTIF') {
			$respon = array("status" => "NOT_AKTIF", "data" => null, "message" => "User Tidak aktif ");
			$this->response($respon, REST_Controller::HTTP_OK);
		}
	}

	function logout_post()
	{
		$this->auth();
		$theCredential = $this->user_data;

		$time_minus = strtotime("-2 minutes", time());
		$this->AuthModel->update_last_activity($theCredential->login_id, $time_minus);
		$this->set_response(array("status" => "OK"), REST_Controller::HTTP_OK);
	}
	function get_admin_list_get()
	{
		$username = $this->get('username');
		$res = $this->AuthModel->retrieve_admin_list($username);
		$this->set_response($res, REST_Controller::HTTP_OK); //This is the respon if success



	}
	function create_admin_list_post()
	{
		$username = $this->post('username');
		$karyawanid = $this->post('karyawanid');
		$res = $this->AuthModel->create_admin_list($username, $karyawanid);
		$this->set_response(array("status" => "OK"), REST_Controller::HTTP_OK); //This is the respon if success



	}
	function  get_user_exp_post()
	{
		$this->auth();
		$theCredential = $this->user_data;
		$this->set_response($theCredential, REST_Controller::HTTP_OK);
	}

	public function getLocationAccess_get()
	{
		$this->auth();
		//$this->theCredential = $this->user_data;
		$user_id = $this->user_data->id;
		$sql = " select * from gbm_organisasi where id in
			(select location_id from fwk_users_location where user_id=" . $user_id . ")
		  and tipe in ('ESTATE', 'MILL', 'HO', 'GO', 'RO') ";
		$data =	$this->db->query($sql)->result_array();
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	public function getPostingAccess_get()
	{
		$this->auth();
		//$this->theCredential = $this->user_data;
		$user_id = $this->user_data->id;
		$sql = " select * from fwk_posting where id in
			(select poting_id from fwk_users_posting where user_id=" . $user_id . ") ";
		$data =	$this->db->query($sql)->result_array();
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	function getMenuAccess_post()
	{
		$this->auth();
		//$this->theCredential = $this->user_data;
		$user_id = $this->user_data->id;

		$sql = "SELECT a.* ,
		CASE
			 WHEN b.id IS NULL THEN 0
			ELSE 1
		END  as selected,ifnull(b.new_,0)as new_,ifnull(b.edit_,0)as edit_,ifnull(b.delete_,0)as delete_,ifnull(b.print_,0)as print_
		  FROM fwk_menu a inner join ( select * from fwk_users_acces where user_id=" . $user_id . ")  b on a.id =b.menu_id
		  where parent_id=0 order by sort_order";

		$res =	$this->db->query($sql)->result_array();
		foreach ($res as $node) {
			if ($node['url'] != '') $node['url'] =  $node['url'];
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
	function getMenuAccessOld_post($user_id)
	{

		$sql = "SELECT a.* ,
		CASE
			 WHEN b.id IS NULL THEN 0
			ELSE 1
		END  as selected,ifnull(b.new_,0)as new_,ifnull(b.edit_,0)as edit_,ifnull(b.delete_,0)as delete_,ifnull(b.print_,0)as print_
		  FROM fwk_menu a inner join ( select * from fwk_users_acces where user_id=" . $user_id . ")  b on a.id =b.menu_id
		  where parent_id=0 order by sort_order";

		$res =	$this->db->query($sql)->result_array();
		foreach ($res as $node) {
			if ($node['url'] != '') $node['url'] =  $node['url'];
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
		END  as selected,ifnull(b.new_,0)as new_,ifnull(b.edit_,0)as edit_,ifnull(b.delete_,0)as delete_,ifnull(b.print_,0)as print_
		  FROM fwk_menu a inner join ( select * from fwk_users_acces where user_id=" . $user_id . ")  b on a.id =b.menu_id
		  where a.parent_id=" . $parentId . " order by sort_order";


		$res =	$this->db->query($sql)->result_array();

		foreach ($res as $node) {
			if ($node['url'] != '') $node['url'] = $node['url'];
			$this->data[] = $node;
			$this->getTree($node['id'], $user_id);
		}
	}

	public function simpan_menu_access()
	{
		$user_id = $this->post('user_id');
		$menus = $this->post('menus');
		$this->UserAccessModel->deleteByUserId($user_id);
		foreach ($menus as $menu) {
			$this->UserAccessModel->create($user_id, $menu['menu_id'], $menu['new_'], $menu['edit_'], $menu['delete'], $menu['print_']);
		}
		$this->set_response(array("status" => "OK", "message" => "Save Successfully "), REST_Controller::HTTP_CREATED);
	}
	public function getMenuButton_post($user_id, $menu_name)
	{
		$res =	$this->UserAccessModel->retrieveByUserIdMenuName($user_id, $menu_name);

		/* update activity open menu */
		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id = $this->user_data->id;
		$res_menu = $this->MenuModel->retrieve_by_name($menu_name);
		$activity = array('user_id' => $this->user_id, 'activity_type' => 'open_menu', 'note' => $res_menu['text']);
		$this->db->insert('fwk_user_activity', $activity);
		/* end update activity open menu */

		$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_OK);
	}
	public function getMenuByUrl_post()
	{
		$url = $this->post('url', true);
		$url = substr($url, 1);
		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id = $this->user_data->id;
		$res =	$this->UserAccessModel->retrieveByUserIdMenuUrl($this->user_id, $url);
		if ($res) {
			$res['menu_url'] = '/' . $res['url'];
		}
		$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_OK);
	}
	public function loginTest_post()
	{
		$u = $this->post('username'); //Username Posted
		$p = sha1($this->post('password')); //Pasword Posted
		$q = array('username' => $u); //For where query condition
		$kunci = $this->config->item('thekey');
		$invalidLogin = ['status' => 'Invalid Login']; //Respon if login invalid
		$val = $this->M_main->get_user($q)->row(); //Model to get single data row from database base on username
		if ($this->M_main->get_user($q)->num_rows() == 0) {
			$this->response($invalidLogin, REST_Controller::HTTP_NOT_FOUND);
		}

		$match = $val->password;   //Get password for user from database
		// var_dump($match);
		if ($p == $match) {  //Condition if password matched
			$token['id'] = $val->id;  //From here
			$token['username'] = $u;
			$date = new DateTime();
			$token['iat'] = $date->getTimestamp();
			$token['exp'] = $date->getTimestamp() + 60 * 60 * 5; //To here is to generate token
			$output['token'] = JWT::encode($token, $kunci); //This is the output token
			$this->set_response($output, REST_Controller::HTTP_OK); //This is the respon if success
		} else {
			$this->set_response($invalidLogin, REST_Controller::HTTP_NOT_FOUND); //This is the respon if failed
		}
	}



	public function ganti_password_post()
	{
		$id = $this->post('id');
		$oldpass = $this->post('oldpass');
		$newpass = $this->post('newpass');

		$get_login = $this->AuthModel->retrieve($id, null, md5($oldpass));
		// var_dump($get_login);
		if (!empty($get_login)) {
			$update_login = $this->AuthModel->update_password($get_login['id'], ($newpass));
			if ($update_login) {
				$this->set_response(array("status" => 'ok', "message" => 'Password has changed'), REST_Controller::HTTP_CREATED);
			} else {
				$this->set_response(array("status" => 'not ok', "message" => 'Error internal server'), REST_Controller::HTTP_NOT_FOUND);
			}
		} else {
			$this->set_response(array("status" => 'not ok', "message" => 'Wrong Password'), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	public function cek_aktif_user_get()
	{
	}
	public function update_fcm_token_post()
	{
		$this->auth();
		$this->user_id = $this->user_data->id;
		$token=$this->post('token');
		$res = $this->AuthModel->update_fcm_token($this->user_id, $token);
		$this->set_response(array("status" => "OK"), REST_Controller::HTTP_OK);
	}

	function logout()
	{
		# update last activity
		$time_minus = strtotime("-2 minutes", time());
		// $this->LoginModel->update_last_activity(get_sess_data('login', 'log_id'), $time_minus);
	}
}
