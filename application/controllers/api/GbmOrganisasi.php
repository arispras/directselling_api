<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

class GbmOrganisasi extends BD_Controller //Rest_Controller
{
	public $user_id;
	public $theCredential;
	public $data = array();
	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('GbmOrganisasiModel');
		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id = $this->user_data->id;
	}

	function index_get()
	{
		$sql = "SELECT * from `gbm_organisasi` where parent_id is null  order by id";
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


		$sql = "SELECT *  FROM `gbm_organisasi` WHERE parent_id=" . $parentId . "   order by sort_order,id";

		$res =	$this->db->query($sql)->result_array();

		foreach ($res as $node) {
			// if ($node['url'] != '') $node['url'] = 'Appl/' . $node['url'] . '.php';
			$this->data[] = $node;
			$this->getTree($node['id']);
		}
	}

	public function getmenuAllChild_get()
	{

		$org = $this->GbmOrganisasiModel->retrieve_all_child();
		$this->set_response($org, REST_Controller::HTTP_OK);
	}
	public function getAllByTipe_get($segment_3 = '')
	{
		$tipe = $segment_3;
		$org = $this->GbmOrganisasiModel->retrieve_all_bytipe($tipe);
		$this->set_response($org, REST_Controller::HTTP_OK);
	}
	public function getBlokByAfdeling_get($segment_3 = '')
	{
		$parent_id = $segment_3;
		$org = $this->GbmOrganisasiModel->retrieve_blok_by_afdeling($parent_id);
		$this->set_response($org, REST_Controller::HTTP_OK);
	}
	public function getMesinByStasiun_get($segment_3 = '')
	{
		$parent_id = $segment_3;
		$org = $this->GbmOrganisasiModel->retrieve_mesin_by_stasiun($parent_id);
		$this->set_response($org, REST_Controller::HTTP_OK);
	}
	public function getSubById_get($segment_3 = '')
	{
		$parent_id = $segment_3;
		$org = $this->GbmOrganisasiModel->retrieve_sub_by_id($parent_id);
		$this->set_response($org, REST_Controller::HTTP_OK);
	}
	public function getAfdelingByEstate_get($segment_3 = '')
	{
		$parent_id = $segment_3;
		$org = $this->GbmOrganisasiModel->retrieve_afdeling_by_estate($parent_id);
		$this->set_response($org, REST_Controller::HTTP_OK);
	}
	public function getAfdelingByEstateAndUser_get($segment_3 = '')
	{
		$parent_id = $segment_3;
		$user_id = $this->user_id;
		$org = $this->GbmOrganisasiModel->retrieve_afdeling_by_estate_and_user($parent_id,$user_id);
		$this->set_response($org, REST_Controller::HTTP_OK);
	}
	public function getGudangByUnit_get($segment_3 = '')
	{
		$parent_id = $segment_3;
		$org = $this->GbmOrganisasiModel->retrieve_gudang_by_unit($parent_id);
		$this->set_response($org, REST_Controller::HTTP_OK);
	}
	public function getGudangCentralAndVirtualByUnit_get($segment_3 = '')
	{
		$parent_id = $segment_3;
		$org = $this->GbmOrganisasiModel->retrieve_gudang_central_and_virtual_by_unit($parent_id);
		$this->set_response($org, REST_Controller::HTTP_OK);
	}
	public function getAllGudangCentralAndVirtual_get()
	{
		$org = $this->GbmOrganisasiModel->retrieve_all_gudang_central_and_virtual();
		$this->set_response($org, REST_Controller::HTTP_OK);
	}
	public function getAfdStByUnit_get($segment_3 = '')
	{
		$parent_id = $segment_3;
		$org = $this->GbmOrganisasiModel->retrieve_afdst_by_unit($parent_id);
		$this->set_response($org, REST_Controller::HTTP_OK);
	}
	public function getAllDivisi_get()
	{
		
		$org = $this->GbmOrganisasiModel->retrieve_all_divisi();
		$this->set_response($org, REST_Controller::HTTP_OK);
	}
	public function getTraksiByUnit_get($segment_3 = '')
	{
		$parent_id = $segment_3;
		$org = $this->GbmOrganisasiModel->retrieve_traksi_by_unit($parent_id);
		$this->set_response($org, REST_Controller::HTTP_OK);
	}
	public function getWorkshopByUnit_get($segment_3 = '')
	{
		$parent_id = $segment_3;
		$org = $this->GbmOrganisasiModel->retrieve_workshop_by_unit($parent_id);
		$this->set_response($org, REST_Controller::HTTP_OK);
	}
	public function getBlokByRayon_get($segment_3 = '')
	{
		$parent_id = $segment_3;
		$org = $this->GbmOrganisasiModel->retrieve_blok_by_rayon($parent_id);
		$this->set_response($org, REST_Controller::HTTP_OK);
	}
	public function getBlokByEstate_get($segment_3 = '')
	{
		$parent_id = $segment_3;
		$org = $this->GbmOrganisasiModel->retrieve_blok_by_estate($parent_id);
		$this->set_response($org, REST_Controller::HTTP_OK);
	}
	public function getMesinByMill_get($segment_3 = '')
	{
		$parent_id = $segment_3;
		$org = $this->GbmOrganisasiModel->retrieve_mesin_by_mill($parent_id);
		$this->set_response($org, REST_Controller::HTTP_OK);
	}
	public function getMesinBlokByMillEstate_get($segment_3 = '')
	{
		$parent_id = $segment_3;
		$org = $this->GbmOrganisasiModel->retrieve_mesinblok_by_millestate($parent_id);
		$this->set_response($org, REST_Controller::HTTP_OK);
	}
	public function getAllChildGudang_get($segment_3 = '')
	{
		$parent_id = $segment_3;
		$org = $this->GbmOrganisasiModel->retrieve_child_gudang($parent_id);
		$this->set_response($org, REST_Controller::HTTP_OK);
	}
	public function getAllAdmUnit_get()
	{

		$org = $this->GbmOrganisasiModel->getAllAdmUnit();
		$this->set_response($org, REST_Controller::HTTP_OK);
	}

	public function getAllAdmUnitByAccess_get()
	{
		$user_id = $this->user_id;
		$org = $this->GbmOrganisasiModel->getAllAdmUnitByAccess($user_id);
		$this->set_response($org, REST_Controller::HTTP_OK);
	}
	public function getmenuAllParent_get()
	{

		$org = $this->GbmOrganisasiModel->retrieve_all_parent();
		$this->set_response($org, REST_Controller::HTTP_OK);
	}

	public function menu_all_hirarki_get()
	{
		$org_hirarki = [];
		$order = 0;
		$org_parent = $this->GbmOrganisasiModel->retrieve_all(null);
		foreach ($org_parent as $m) {
			$order++;
			$k_parent = $m;
			$org = $this->GbmOrganisasiModel->retrieve_all($m['id']);
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
	private function menu_hirarki(&$str_menu = "", $parent_id = null, $order = 0)
	{
		$org = $this->GbmOrganisasiModel->retrieve_all($parent_id);
		if (count($org) > 0) {
			if (is_null($parent_id)) {
				$str_menu .= '<ol class="sortable" id="menu">';
			} else {
				$str_menu .= '<ol>';
			}
		}

		foreach ($org as $m) {
			$order++;
			$str_menu .= '<li id="list_' . $m['id'] . '">
            <div>
                <span class="disclose" id="menu"><span>
                </span></span>
                <span class="pull-right">';
			if (empty($m['parent_id'])) {
				$str_menu .= '<a href="' . site_url('menu/add/' . $m['id']) . '" title="Tambah Baru"><i class="icon icon-plus"></i>Tambah</a>';
			}
			$str_menu .= '
                    <a href="' . site_url('menu/edit/' . $m['id']) . '" title="Edit"><i class="icon icon-edit"></i>Edit</a>
                </span>';
			if ($m['aktif'] == 1) {
				$str_menu .= '<b>' . $m['nama'] . '</b>';
			} else {
				$str_menu .= '<b class="text-muted">' . $m['nama'] . '</b>';
			}
			$str_menu .= '</div>';

			$this->menu_hirarki($str_menu, $m['id'], $order);
			$str_menu .= '</li>';
		}

		if (count($org) > 0) {
			$str_menu .= '</ol>';
		}
	}
	public	function getById_get($segment_3 = '')
	{

		$id = $segment_3;
		$retrieve = $this->GbmOrganisasiModel->retrieve($id);


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
		$afdeling_id = $this->post('afdeling_id', TRUE);
		$sort_order = $this->post('sort_order', TRUE);
		$res =  $this->GbmOrganisasiModel->create($name, $text, $is_child, $parent_id, $tipe, $sort_order, $afdeling_id);
		$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
	}



	function index_put($segment_3 = '')
	{
		$id = (int)$segment_3;

		$org = $this->GbmOrganisasiModel->retrieve($id, true);
		if (empty($org)) {
			$this->set_response(array("status" => "NOT OK", "data" => "menu tidak ada"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}

		$name = $this->put('kode', TRUE);
		$text = $this->put('nama', TRUE);
		$tipe = $this->put('tipe', TRUE);
		$is_child = $this->put('is_child', TRUE);
		$parent_id = $this->put('parent_id', TRUE);
		$afdeling_id = $this->put('afdeling_id', TRUE);
		$sort_order = $this->put('sort_order', TRUE);


		// print_r($this->input->post()); die;
		$res =  $this->GbmOrganisasiModel->update($id, $name, $text, $is_child, $parent_id, $tipe, $sort_order, $afdeling_id);
		$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
	}

	public	function index_delete($segment_3 = '')
	{

		$id = $segment_3;
		$id       = (int)$segment_3;
		$retrieve_mapel = $this->GbmOrganisasiModel->retrieve($id);
		if (empty($retrieve_mapel)) {
			$this->set_response(array("status" => "NOT OK", "data" => "NOT FOUND Menu"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}

		$res = $this->GbmOrganisasiModel->delete($id);
		if (($res)) {

			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => null), REST_Controller::HTTP_NOT_FOUND);
		}
	}
}
