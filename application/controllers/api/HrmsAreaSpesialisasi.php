<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
use Restserver\Libraries\REST_Controller;

class HrmsAreaSpesialisasi extends BD_Controller
{
	public $user_id;
	public $theCredential;

    function __construct()
    {
        parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('HrmsAreaspesialisasiModel');
		$this->load->model('M_DatatablesModel');
		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id = $this->user_data->id;
    }

    public function list_post()
	{
		$post = $this->post();

		$query  = "SELECT a.*,
		b.user_full_name AS dibuat,
		c.user_full_name AS diubah  
		from hrms_area_spesialisasi a
		LEFT JOIN fwk_users b ON a.dibuat_oleh = b.id
		LEFT JOIN fwk_users c ON a.diubah_oleh = c.id ";
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
		$retrieve = $this->HrmsAreaspesialisasiModel->retrieve($id);
		
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
     }
	 function getAll_get()
	 {
		
		 $retrieve = $this->HrmsAreaspesialisasiModel->retrieve_all();
				 
		 if (!empty($retrieve)) {
			 $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		 } else {
			 $this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		 }
	  }
    function index_post()
    {
		
		$input = $this->post();
		$input['dibuat_oleh']=$this->user_id;
		$input['diubah_oleh']=$this->user_id;
		$retrieve=  $this->HrmsAreaspesialisasiModel->create($input);
		if (!empty($retrieve)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'hrms_golongan', 'action' => 'new', 'entity_id' => $retrieve,'key_text'=>$input['nama']);
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
        $gudang = $this->HrmsAreaspesialisasiModel->retrieve($id);
        if (empty($gudang)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);

        }
		$input = $this->put();
		$input['diubah_oleh']=$this->user_id;
         $res=   $this->HrmsAreaspesialisasiModel->update($gudang['id'], $input);
			// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
			if (!empty($res)) {
				/* start audit trail */
				$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'hrms_golongan', 'action' => 'edit', 'entity_id' => $id,'key_text'=>$input['nama']);
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
        $gudang = $this->HrmsAreaspesialisasiModel->retrieve( $id);
        if (empty($gudang)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);

        }

         $res=  $this->HrmsAreaspesialisasiModel->delete($gudang['id']);
			// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
			if (!empty($res)) {
				/* start audit trail */
				$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id'=>$id)), 'entity' => 'hrms_golongan', 'action' => 'delete', 'entity_id' => $id,'key_text'=>$gudang['nama']);
				$this->db->insert('fwk_user_audit', $audit);
				/* end audit trail */
				$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
			} else {
				$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
			}

    }
}
