<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
use Restserver\Libraries\REST_Controller;

class HrmsLokasi extends BD_Controller
{
	public $user_id;
	public $theCredential;

    function __construct()
    {
        parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('HrmsLokasiModel');
		$this->load->model('M_DatatablesModel');
		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id = $this->user_data->id;
    }

    public function list_post()
	{
		$post = $this->post();

		$query  = "SELECT * from payroll_lokasi ";
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
		$retrieve = $this->HrmsLokasiModel->retrieve($id);
		
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
     }
	 function getAll_get()
	 {
		
		 $retrieve = $this->HrmsLokasiModel->retrieve_all_hrmsdepartemen();
				 
		 if (!empty($retrieve)) {
			 $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		 } else {
			 $this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		 }
	  }
    function index_post()
    {
		

		$retrieve=  $this->HrmsLokasiModel->create($this->post('nama'));
		if (!empty($retrieve)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'hrms_lokasi', 'action' => 'new', 'entity_id' => $retrieve);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $this->post()['no_transaksi']), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
		

	}

    function index_put($segment_3 = '')
    {
      

        $id = (int)$segment_3;
        $gudang = $this->HrmsLokasiModel->retrieve($id);
        if (empty($gudang)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);

        }

         $res=   $this->HrmsLokasiModel->update($gudang['id'], $this->put('nama'));
			// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
			if (!empty($res)) {
				/* start audit trail */
				$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'hrms_lokasi', 'action' => 'edit', 'entity_id' => $id);
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
        $gudang = $this->HrmsLokasiModel->retrieve( $id);
        if (empty($gudang)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);

        }

         $res=  $this->HrmsLokasiModel->delete($gudang['id']);
			// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
			if (!empty($res)) {
				/* start audit trail */
				$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id'=>$id)), 'entity' => 'hrms_lokasi', 'action' => 'delete', 'entity_id' => $id);
				$this->db->insert('fwk_user_audit', $audit);
				/* end audit trail */
				$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
			} else {
				$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
			}

    }
}
