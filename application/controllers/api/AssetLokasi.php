<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
use Restserver\Libraries\REST_Controller;

class AssetLokasi extends Rest_Controller
{
    function __construct()
    {
        parent::__construct();
		$this->load->model('AssetLokasiModel');
		$this->load->model('M_DatatablesModel');
		
    }

    public function list_post()
	{
		$post = $this->post();

		$query  = "SELECT * from asset_lokasi ";
		$search = array( 'nama');
		$where  = null;
	   
		$isWhere = null;
		// $isWhere = 'artikel.deleted_at IS NULL';

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
    function index_get($segment_3 = '')
    {
		$id = $segment_3;
		$retrieve = $this->AssetLokasiModel->retrieve($id);
		
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
     }
	 function getAll_get()
	 {
		
		 $retrieve = $this->AssetLokasiModel->retrieve_all_lokasi();
				 
		 if (!empty($retrieve)) {
			 $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		 } else {
			 $this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		 }
	  }
    function index_post()
    {
		

		$retrieve=  $this->AssetLokasiModel->create($this->post('nama'));
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);

	}

    function index_put($segment_3 = '')
    {
      

        $id = (int)$segment_3;
        $gudang = $this->AssetLokasiModel->retrieve($id);
        if (empty($gudang)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);

        }

         $retrieve=   $this->AssetLokasiModel->update($gudang['id'], $this->put('nama'));
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);

          

      
    }

    function index_delete($segment_3 = '')
    {
      
		$id = (int)$segment_3;
        $gudang = $this->AssetLokasiModel->retrieve( $id);
        if (empty($gudang)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);

        }

         $retrieve=  $this->AssetLokasiModel->delete($gudang['id']);
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);


    }
}
