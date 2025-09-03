<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
use Restserver\Libraries\REST_Controller;

class PrcPoTtd extends BD_Controller
{
	public $user_id;
	public $theCredential;

    function __construct()
    {
        parent::__construct();
		$this->load->model('PrcPoTtdModel');
		$this->load->model('M_DatatablesModel');
		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id = $this->user_data->id;
    }

	public function list_post()
	{
		$post = $this->post();
		$query  = "SELECT * FROM prc_po_ttd ";

		$search = array('nama','tipe');
		$where  = null;
	   
		$isWhere = null;
		// $isWhere = 'artikel.deleted_at IS NULL';

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
    function index_get($segment_3 = '')
    {
		$id = $segment_3;
		$retrieve = $this->PrcPoTtdModel->retrievebyId($id);
		
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => []), REST_Controller::HTTP_OK);
		}
     }
	 function getAll_get()
	 {
		
		 $retrieve = $this->PrcPoTtdModel->retrieve_all();
				 
		 if (!empty($retrieve)) {
			 $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		 } else {
			 $this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		 }
	  }
	  function getAllbyType_get($segment_3 = '')
	  {
		 
		$tipe=$segment_3;
		  $retrieve = $this->PrcPoTtdModel->retrieve_all_by_tipe($tipe);
				  
		  if (!empty($retrieve)) {
			  $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		  } else {
			  $this->set_response(array("status" => "NOT OK", "data" => []), REST_Controller::HTTP_OK);
		  }
	   }
	  function getAllDetail_get()
	 {
		
		 $retrieve = $this->PrcPoTtdModel->retrieve_all_akun_detail();
				 
		 if (!empty($retrieve)) {
			 $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		 } else {
			 $this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		 }
	  }
    function index_post()
    {
			$input = $this->post();

		$res=  $this->PrcPoTtdModel->create($this->post());
			// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
			if (!empty($res)) {
				/* start audit trail */
				$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'prc_po_ttd', 'action' => 'new', 'entity_id' => $res);
				$this->db->insert('fwk_user_audit', $audit);
				/* end audit trail */
				$this->set_response(array("status" => "OK", "data" => $input['no_transaksi']), REST_Controller::HTTP_CREATED);
			} else {
				$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
			}
	}

    function index_put($segment_3 = '')
    {
        # yang bisa edit akun adalah pengajar / admin
        // if (!is_pengajar() AND !is_admin()) {
        //     redirect('akun/index');
        // }

        $id = (int)$segment_3;
        $akun = $this->PrcPoTtdModel->retrieve(array('id' => $id));
        if (empty($akun)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);

        }

         $res=   $this->PrcPoTtdModel->update($akun['id'], $this->put());
			// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
			if (!empty($res)) {
				/* start audit trail */
				$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'prc_po_ttd', 'action' => 'edit', 'entity_id' => $id);
				$this->db->insert('fwk_user_audit', $audit);
				/* end audit trail */
				$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
			} else {
				$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
			}
          

      
    }

    function index_delete($segment_3 = '')
    {
        # yang bisa edit akun adalah pengajar / admin
        // if (!is_pengajar() AND !is_admin()) {
        //     redirect('akun/index');
        // }

		$id = (int)$segment_3;
        $akun = $this->PrcPoTtdModel->retrieve(array('id' => $id));
        if (empty($akun)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);

        }

      $res=  $this->PrcPoTtdModel->delete($akun['id']);
			// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
			if (!empty($res)) {
				/* start audit trail */
				$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id'=>$id)), 'entity' => 'prc_po_ttd', 'action' => 'delete', 'entity_id' => $id);
				$this->db->insert('fwk_user_audit', $audit);
				/* end audit trail */
				$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
			} else {
				$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
			}

    }

    function detail($segment_3 = '')
    {
       
    }
}
