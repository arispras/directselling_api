<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
use Restserver\Libraries\REST_Controller;

class AccInterUnit extends BD_Controller
{
    function __construct()
    {
        parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('AccInterUnitModel');
		$this->load->model('M_DatatablesModel');
		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id = $this->user_data->id;
		
    }

	public function list_post()
	{
		$post = $this->post();

		$query  = "	SELECT 
		a.tipe as tipe,		
		b.nama AS akun,
		c.nama AS lokasi,
		d.nama AS lokasi2,
		a.id AS id,
		f.user_full_name AS dibuat,
		g.user_full_name AS diubah,
		a.dibuat_tanggal,
		a.diubah_tanggal 
		FROM acc_inter_unit a 
		LEFT JOIN acc_akun b ON a.acc_akun_id=b.id
		LEFT JOIN gbm_organisasi c ON a.lokasi_id=c.id
		LEFT JOIN gbm_organisasi d ON a.lokasi_id_2=d.id
		LEFT JOIN fwk_users f ON a.dibuat_oleh = f.id
		LEFT JOIN fwk_users g ON a.diubah_oleh = g.id
			";
		$search = array('a.tipe', 'b.nama', 'c.nama');
		$where  = null;
	   
		$isWhere = null;
		// $isWhere = 'artikel.deleted_at IS NULL';

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
    function index_get($segment_3 = '')
    {
		$id = $segment_3;
		$retrieve = $this->AccInterUnitModel->retrievebyId($id);
		
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
     }
	 function getAll_get()
	 {
		
		 $retrieve = $this->AccInterUnitModel->retrieve_all_akun();
				 
		 if (!empty($retrieve)) {
			 $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		 } else {
			 $this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		 }
	  }
	  function getAllDetail_get()
	 {
		
		 $retrieve = $this->AccInterUnitModel->retrieve_all_akun_detail();
				 
		 if (!empty($retrieve)) {
			 $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		 } else {
			 $this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		 }
	  }
	  function getAllKasbank_get()
	  {
		 
		  $retrieve = $this->AccInterUnitModel->retrieve_all_akun_kasbank();
				  
		  if (!empty($retrieve)) {
			  $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		  } else {
			  $this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		  }
	   }
    function index_post()
    {
		$input = $this->post();
		$input['dibuat_oleh'] = $this->user_id;
		$input['diubah_oleh'] = $this->user_id;
		$retrieve=  $this->AccInterUnitModel->create($input);
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);

	}

    function index_put($segment_3 = '')
    {
        # yang bisa edit akun adalah pengajar / admin
        // if (!is_pengajar() AND !is_admin()) {
        //     redirect('akun/index');
        // }

        $id = (int)$segment_3;
        $akun = $this->AccInterUnitModel->retrieve(array('id' => $id));
        if (empty($akun)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);

        }
		$input = $this->put();
		$input['diubah_oleh'] = $this->user_id;
         $retrieve=   $this->AccInterUnitModel->update($akun['id'], $input);
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);

          

      
    }

    function index_delete($segment_3 = '')
    {
        # yang bisa edit akun adalah pengajar / admin
        // if (!is_pengajar() AND !is_admin()) {
        //     redirect('akun/index');
        // }

		$id = (int)$segment_3;
        $akun = $this->AccInterUnitModel->retrieve(array('id' => $id));
        if (empty($akun)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);

        }

         $retrieve=  $this->AccInterUnitModel->delete($akun['id']);
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);


    }

    function detail($segment_3 = '')
    {
       
    }
}
