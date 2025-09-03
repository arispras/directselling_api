<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
use Restserver\Libraries\REST_Controller;

class Menu extends Rest_Controller
{
	public $data=array();
	function __construct()
    {
        parent::__construct();
		$this->load->model('MenuModel');
	
    }

	function index_get()
	{
		$sql = "SELECT * from `fwk_menu` where parent_id=0  order by id";
		$res =	$this->db->query($sql)->result_array();

		foreach ($res as $node) {
			// if ($node['url'] != '') $node['url'] = 'Appl/' . $node['url'] . '.php';
			$this->data[] = $node;
			$this->getTree($node['id'],'');
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
		$menu=array();
		// $menu=$this->data;
		foreach ($this->data  as $key => &$item) {
			//var_dump('key:'.$key);
			$menu[]=$this->data[$key];

		}



		$this->set_response(array("data"=>$menu), REST_Controller::HTTP_OK); 
	}
		function getTree($parentId = null)
	{
	

		$sql = "SELECT *  FROM `fwk_menu` WHERE parent_id=" . $parentId . "   order by sort_order,id";
		
		$res =	$this->db->query($sql)->result_array();
		
		foreach ($res as $node) {
			// if ($node['url'] != '') $node['url'] = 'Appl/' . $node['url'] . '.php';
			$this->data[] = $node;
			$this->getTree($node['id']);
		}
	}

	public function getmenuAllChild_get(){
		
		$menu = $this->MenuModel->retrieve_all_child();
		$this->set_response($menu, REST_Controller::HTTP_OK);

	}
	public function getmenuAllParent_get(){
		
		$menu = $this->MenuModel->retrieve_all_parent();
		$this->set_response($menu, REST_Controller::HTTP_OK);

	}

	public function menu_all_hirarki_get(){
		$menu_hirarki=[];
		$order = 0;
		$menu_parent = $this->MenuModel->retrieve_all(null);
		foreach ($menu_parent as $m){
            $order++;
			$k_parent=$m;
			$menu = $this->MenuModel->retrieve_all($m['id']);
			if (count($menu)>0){
				$kl=[];
				foreach ($menu as $k){
				  $kl[]=$k;
				}
				$k_parent['children']=$kl;
				$k_parent['expanded']= true;
			}		
				
				$menu_hirarki[]=$k_parent;
           
        }
		$this->set_response(array('status'=>'OK',"data"=>$menu_hirarki), REST_Controller::HTTP_OK);
	}
	private function menu_hirarki(&$str_menu = "", $parent_id = null, $order = 0){
        $menu = $this->MenuModel->retrieve_all($parent_id);
        if(count($menu) > 0){
            if(is_null($parent_id)){
                $str_menu .= '<ol class="sortable" id="menu">';
            }else{
                $str_menu .= '<ol>';
            }
        }

        foreach ($menu as $m){
            $order++;
            $str_menu .= '<li id="list_'.$m['id'].'">
            <div>
                <span class="disclose" id="menu"><span>
                </span></span>
                <span class="pull-right">';
                if (empty($m['parent_id'])) {
            $str_menu .= '<a href="'.site_url('menu/add/'.$m['id']).'" title="Tambah Baru"><i class="icon icon-plus"></i>Tambah</a>';
                }
            $str_menu .= '
                    <a href="'.site_url('menu/edit/'.$m['id']).'" title="Edit"><i class="icon icon-edit"></i>Edit</a>
                </span>';
                if ($m['aktif'] == 1) {
                    $str_menu .= '<b>'.$m['nama'].'</b>';
                } else {
                    $str_menu .= '<b class="text-muted">'.$m['nama'].'</b>';
                }
            $str_menu .= '</div>';

                $this->menu_hirarki($str_menu, $m['id'], $order);
            $str_menu .= '</li>';
        }

        if(count($menu) > 0){
            $str_menu .= '</ol>';
        }
    }
   	public	function getById_get($segment_3 = '')
	{

		$id = $segment_3;
		$retrieve = $this->MenuModel->retrieve($id);


		if (!empty($retrieve)) {
			
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => null), REST_Controller::HTTP_NOT_FOUND);
		}
	}
    

    function index_post()
    {
       
            $name = $this->post('name', TRUE);
			$text = $this->post('text', TRUE);
			$url = $this->post('url', TRUE);
			$is_child = $this->post('is_child', TRUE);
            $parent_id = $this->post('parent_id', TRUE);
			$icon = $this->post('icon', TRUE);
			$sort_order = $this->post('sort_order', TRUE);
			

            // print_r($this->input->post()); die;
          $res=  $this->MenuModel->create($name,$text,$is_child, $parent_id,$url,$icon,$sort_order);
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
             }



    function index_put($segment_3 = '')
    {
        $id = (int)$segment_3;

        $menu = $this->MenuModel->retrieve($id, true);
        if (empty($menu)) {
			$this->set_response(array("status" => "NOT OK", "data" => "menu tidak ada"), REST_Controller::HTTP_NOT_FOUND);
			return;
        }
        
		$name = $this->put('name', TRUE);
		$text = $this->put('text', TRUE);
		$url = $this->put('url', TRUE);
		$is_child = $this->put('is_child', TRUE);
		$parent_id = $this->put('parent_id', TRUE);
		$icon = $this->put('icon', TRUE);
		$sort_order = $this->put('sort_order', TRUE);
		

		// print_r($this->input->post()); die;
	   $res=  $this->MenuModel->update($id,$name,$text,$is_child, $parent_id,$url,$icon,$sort_order);
	   $this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
 
       
    }

	public	function index_delete($segment_3 = '')
	{

		$id = $segment_3;
		$id       = (int)$segment_3;
		$retrieve_mapel = $this->MenuModel->retrieve($id);
		if (empty($retrieve_mapel)) {
			$this->set_response(array("status" => "NOT OK", "data" => "NOT FOUND Menu"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
	
		$res = $this->MenuModel->delete($id);
		if (($res)) {
		
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => null), REST_Controller::HTTP_NOT_FOUND);
		}
	}

    
}
