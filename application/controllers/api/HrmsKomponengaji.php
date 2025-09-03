<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
use Restserver\Libraries\REST_Controller;

class HrmsKomponengaji extends BD_Controller
{
	public $user_id;
	public $theCredential;

    function __construct()
    {
        parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('HrmsKomponengajiModel');
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
		from payroll_tipe_gaji a
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
		$retrieve = $this->HrmsKomponengajiModel->retrieve($id);
		
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
     }
	 function getAll_get()
	 {
		
		 $retrieve = $this->HrmsKomponengajiModel->retrieve_all_komponengaji();
				 
		 if (!empty($retrieve)) {
			 $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		 } else {
			 $this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		 }
	  }
	  function getAllPotongan_get()
	  {
		 
		  $retrieve = $this->HrmsKomponengajiModel->retrieve_all_potongan();
				  
		  if (!empty($retrieve)) {
			  $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		  } else {
			  $this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		  }
	   }
	   function getAllPendapatan_get()
	   {
		  
		   $retrieve = $this->HrmsKomponengajiModel->retrieve_all_pendapatan();
				   
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
		$retrieve=  $this->HrmsKomponengajiModel->create($input);
		if (!empty($retrieve)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'hrms_komponen_gaji', 'action' => 'new', 'entity_id' => $retrieve);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => true), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
		

	}

    function index_put($segment_3 = '')
    {
      

        $id = (int)$segment_3;
        $komponenGaji = $this->HrmsKomponengajiModel->retrieve($id);
        if (empty($komponenGaji)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);

        }
		$input = $this->put();
		$input['diubah_oleh']=$this->user_id;
         $res=   $this->HrmsKomponengajiModel->update($komponenGaji['id'], $input);
			// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);

			if (!empty($res)) {
				/* start audit trail */
				$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'hrms_komponen_gaji', 'action' => 'edit', 'entity_id' => $id);
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
        $komponenGaji = $this->HrmsKomponengajiModel->retrieve( $id);
        if (empty($komponenGaji)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);

        }

         $res=  $this->HrmsKomponengajiModel->delete($komponenGaji['id']);
			// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
			if (!empty($res)) {
				/* start audit trail */
				$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id'=>$id)), 'entity' => 'hrms_komponen_gaji', 'action' => 'delete', 'entity_id' => $id);
				$this->db->insert('fwk_user_audit', $audit);
				/* end audit trail */
				$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
			} else {
				$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
			}

    }
}
