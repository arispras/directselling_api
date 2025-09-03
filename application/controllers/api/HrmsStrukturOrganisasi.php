<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

class HrmsStrukturOrganisasi extends BD_Controller //Rest_Controller//
{
	public $user_id;
	public $theCredential;
	public $data = array();
	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('HrmsStrukturOrganisasiModel');
		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id = $this->user_data->id;
	}

	function index_get()
	{
		$sql = "SELECT * from `hrms_struktur_organisasi` where parent_id is null  order by id";
		$res =	$this->db->query($sql)->result_array();

		foreach ($res as $node) {
			// if ($node['url'] != '') $node['url'] = 'Appl/' . $node['url'] . '.php';
			$this->data[] = $node;
			$this->getTree($node['id'], '');
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
				//$item['a_attr']->href = $item['url'];
			}
			if ($item['parent_id'] && isset($itemsByReference[$item['parent_id']])) {
				unset($this->data[$key]);
			}

			//echo "<pre>".$key;print_R($data);die;
		}
		$org = array();
		// $org=$this->data;
		foreach ($this->data  as $key => &$item) {
			//var_dump('key:'.$key);
			$org[] = $this->data[$key];
		}



		$this->set_response(array("data" => $org), REST_Controller::HTTP_OK);
	}
	function getTree($parentId = null)
	{


		//$sql = "SELECT *  FROM `hrms_struktur_organisasi` WHERE parent_id=" . $parentId . "   order by sort_order,id";
		$sql = "SELECT *  FROM `hrms_struktur_organisasi` WHERE parent_id=" . $parentId . "   order by kode";

		$res =	$this->db->query($sql)->result_array();

		foreach ($res as $node) {
			// if ($node['url'] != '') $node['url'] = 'Appl/' . $node['url'] . '.php';
			$this->data[] = $node;
			$this->getTree($node['id']);
		}
	}

	function getOrgChart_get()
	{
		$sql = "SELECT a.*,b.foto,b.nama as nama_karyawan,b.nip,c.nama as nama_jabatan,d.nama as nama_dept from `hrms_struktur_organisasi` a 
		inner join karyawan b on a.karyawan_id=b.id 
		left join payroll_jabatan c on b.jabatan_id =c.id
		left join payroll_department d on b.departemen_id =d.id
		 where a.parent_id is null  order by a.id";
		$res =	$this->db->query($sql)->result_array();

		foreach ($res as $node) {
			$d=array(
				"nodeId" =>$node['id'],
				"parentNodeId" => $node['parent_id'],
				"width" => 342,
				"height" => 146,
				"borderWidth" => 1,
				"borderRadius" => 5,
				"borderColor" => array(
				  "red" => 15,
				  "green" => 140,
				  "blue" => 121,
				  "alpha" => 1
				),
				"backgroundColor" =>array (
				  "red" => 51,
				  "green" => 182,
				  "blue" => 208,
				  "alpha" => 1
				),
				"nodeImage"  =>array(
				  "url" =>$this->get_path_image($node['foto']),//"https://raw.githubusercontent.com/bumbeishvili/Assets/master/Projects/D3/Organization%20Chart/cto.jpg",
				  "width" => 100,
				  "height" =>100,
				  "centerTopDistance" => 0,
				  "centerLeftDistance" => 0,
				  "cornerShape" => "CIRCLE",
				  "shadow" => false,
				  "borderWidth" => 0,
				  "borderColor" => array(
					"red" => 19,
					"green" => 123,
					"blue" => 128,
					"alpha" => 1
				  )
				  ),
				"nodeIcon" => array(
				  "icon" => "https://to.ly/1yZnX",
				  "size" => 30
				),
				"template" => "<div>\n                  <div style=\"margin-left:70px;\n                              margin-top:10px;\n                              font-size:20px;\n                              font-weight:bold;\n                         \">".$node['nama_karyawan']." </div>\n                 <div style=\"margin-left:70px;\n                              margin-top:3px;\n                              font-size:16px;\n                         \">".$node['nama_jabatan']."  </div>\n\n                 <div style=\"margin-left:70px;\n                              margin-top:3px;\n                              font-size:14px;\n                         \">".$node['nama_dept']." </div>\n\n                 <div style=\"margin-left:196px;\n                             margin-top:15px;\n                             font-size:13px;\n                             position:absolute;\n                             bottom:5px;\n                            \">\n                      <div>".$node['nip']."</div>\n                      <div style=\"margin-top:5px\">Corporate</div>\n                 </div>\n              </div>",
				"connectorLineColor" => array(
				  "red" => 220,
				  "green" => 189,
				  "blue" => 207,
				  "alpha" => 1
				),
				"connectorLineWidth" => 5,
				"dashArray" => "",
				"expanded" => false,
				"directSubordinates" => 4,
				"totalSubordinates" => 1515
			);
			// if ($node['url'] != '') $node['url'] = 'Appl/' . $node['url'] . '.php';
			$this->data[] =$d;// $node;
			$this->getTreeOrgChart($node['id'], '');
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
				//$item['a_attr']->href = $item['url'];
			}
			if ($item['parent_id'] && isset($itemsByReference[$item['parent_id']])) {
				unset($this->data[$key]);
			}

			//echo "<pre>".$key;print_R($data);die;
		}
		$org = array();
		// $org=$this->data;
		foreach ($this->data  as $key => &$item) {
			//var_dump('key:'.$key);
			$org[] = $this->data[$key];
		}



		$this->set_response(array("data" => $org), REST_Controller::HTTP_OK);
	}
	function getTreeOrgChart($parentId = null)
	{


		$sql = "SELECT a.*,b.foto,b.nama as nama_karyawan,b.nip,c.nama as nama_jabatan,d.nama as nama_dept from `hrms_struktur_organisasi` a 
		inner join karyawan b on a.karyawan_id=b.id 
		left join payroll_jabatan c on b.jabatan_id =c.id
		left join payroll_department d on b.departemen_id =d.id
		 where a.parent_id=" . $parentId . "  order by a.id";
		//$sql = "SELECT *  FROM `hrms_struktur_organisasi` WHERE parent_id=" . $parentId . "   order by kode";

		$res =	$this->db->query($sql)->result_array();

		foreach ($res as $node) {
			$d=array(
				"nodeId" =>$node['id'],
				"parentNodeId" => $node['parent_id'],
				"width" => 342,
				"height" => 146,
				"borderWidth" => 1,
				"borderRadius" => 5,
				"borderColor" => array(
				  "red" => 15,
				  "green" => 140,
				  "blue" => 121,
				  "alpha" => 1
				),
				"backgroundColor" =>array (
				  "red" => 51,
				  "green" => 182,
				  "blue" => 208,
				  "alpha" => 1
				),
				"nodeImage"  =>array(
				  "url" =>$this->get_path_image($node['foto']),//"https://raw.githubusercontent.com/bumbeishvili/Assets/master/Projects/D3/Organization%20Chart/cto.jpg",
				  "width" => 100,
				  "height" =>100,
				  "centerTopDistance" => 0,
				  "centerLeftDistance" => 0,
				  "cornerShape" => "CIRCLE",
				  "shadow" => false,
				  "borderWidth" => 0,
				  "borderColor" => array(
					"red" => 19,
					"green" => 123,
					"blue" => 128,
					"alpha" => 1
				  )
				  ),
				"nodeIcon" => array(
				  "icon" => "https://to.ly/1yZnX",
				  "size" => 30
				),
				"template" => "<div>\n                  <div style=\"margin-left:70px;\n                              margin-top:10px;\n                              font-size:20px;\n                              font-weight:bold;\n                         \">".$node['nama_karyawan']." </div>\n                 <div style=\"margin-left:70px;\n                              margin-top:3px;\n                              font-size:16px;\n                         \">".$node['nama_jabatan']."  </div>\n\n                 <div style=\"margin-left:70px;\n                              margin-top:3px;\n                              font-size:14px;\n                         \">".$node['nama_dept']." </div>\n\n                 <div style=\"margin-left:196px;\n                             margin-top:15px;\n                             font-size:13px;\n                             position:absolute;\n                             bottom:5px;\n                            \">\n                      <div>".$node['nip']."</div>\n                      <div style=\"margin-top:5px\">Corporate</div>\n                 </div>\n              </div>",
				"connectorLineColor" => array(
				  "red" => 220,
				  "green" => 189,
				  "blue" => 207,
				  "alpha" => 1
				),
				"connectorLineWidth" => 5,
				"dashArray" => "",
				"expanded" => false,
				"directSubordinates" => 4,
				"totalSubordinates" => 1515
			);
			$this->data[] =$d;//$node;
			$this->getTreeOrgChart($node['id']);
		}
	}

	
	public function menu_all_hirarki_get()
	{
		$org_hirarki = [];
		$order = 0;
		$org_parent = $this->HrmsStrukturOrganisasiModel->retrieve_all(null);
		foreach ($org_parent as $m) {
			$order++;
			$k_parent = $m;
			$org = $this->HrmsStrukturOrganisasiModel->retrieve_all($m['id']);
			if (count($org) > 0) {
				$kl = [];
				foreach ($org as $k) {
					$kl[] = $k;
				}
				$k_parent['children'] = $kl;
				$k_parent['expanded'] = true;
			}

			$org_hirarki[] = $k_parent;
		}
		$this->set_response(array('status' => 'OK', "data" => $org_hirarki), REST_Controller::HTTP_OK);
	}
	
	public	function getById_get($segment_3 = '')
	{

		$id = $segment_3;
		$retrieve = $this->HrmsStrukturOrganisasiModel->retrieve($id);


		if (!empty($retrieve)) {

			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => null), REST_Controller::HTTP_NOT_FOUND);
		}
	}


	function index_post()
	{

		$name = $this->post('kode', TRUE);
		$text = $this->post('nama', TRUE);
		$tipe = $this->post('tipe', TRUE);
		$is_child = $this->post('is_child', TRUE);
		$parent_id = $this->post('parent_id', TRUE);
		$karyawan_id = $this->post('karyawan_id', TRUE);
		$sort_order = $this->post('sort_order', TRUE);
		$res =  $this->HrmsStrukturOrganisasiModel->create($name, $text, $is_child, $parent_id, $tipe, $sort_order, $karyawan_id);
		$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
	}



	function index_put($segment_3 = '')
	{
		$id = (int)$segment_3;

		$org = $this->HrmsStrukturOrganisasiModel->retrieve($id, true);
		if (empty($org)) {
			$this->set_response(array("status" => "NOT OK", "data" => "menu tidak ada"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}

		$name = $this->put('kode', TRUE);
		$text = $this->put('nama', TRUE);
		$tipe = $this->put('tipe', TRUE);
		$is_child = $this->put('is_child', TRUE);
		$parent_id = $this->put('parent_id', TRUE);
		$karyawan_id = $this->put('karyawan_id', TRUE);
		$sort_order = $this->put('sort_order', TRUE);


		// print_r($this->input->post()); die;
		$res =  $this->HrmsStrukturOrganisasiModel->update($id, $name, $text, $is_child, $parent_id, $tipe, $sort_order, $karyawan_id);
		$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
	}

	public	function index_delete($segment_3 = '')
	{

		$id = $segment_3;
		$id       = (int)$segment_3;
		$retrieve_mapel = $this->HrmsStrukturOrganisasiModel->retrieve($id);
		if (empty($retrieve_mapel)) {
			$this->set_response(array("status" => "NOT OK", "data" => "NOT FOUND Menu"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}

		$res = $this->HrmsStrukturOrganisasiModel->delete($id);
		if (($res)) {

			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => null), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function import_mobile_get()
	{
		$retrieve = $this->db->query(" select * from hrms_struktur_organisasi where tipe in('estate','afdeling','blok') ")->result_array();
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => []), REST_Controller::HTTP_OK);
		}
	}
	function get_path_image($img = '', $size = '')
	{
		if (empty($size)) {
			return   'https://klinik.antech-indonesia.com/plantation/userfiles/images/' . $img;
		} else {
			$pisah = explode('.', $img);
			$ext = end($pisah);
			$nama_file = $pisah[0];

			return  'https://klinik.antech-indonesia.com/plantation//userfiles/images/' . $nama_file . '_' . $size . '.' . $ext;
		}
	}
}
