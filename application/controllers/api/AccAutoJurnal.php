<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
use Restserver\Libraries\REST_Controller;

class AccAutoJurnal extends BD_Controller
{
    function __construct()
    {
        parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('AccAutoJurnalModel');
		$this->load->model('M_DatatablesModel');
		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id = $this->user_data->id;
		
    }

	public function list_post()
	{
		$post = $this->post();

		$query  = "SELECT 
		a.id AS id,
		a.kode AS kode,
		a.ket AS ket,
		b.nama AS akun,
		c.nama AS akun_debet,
		d.nama AS akun_kredit,
		f.user_full_name AS dibuat,
		g.user_full_name AS diubah,
		a.dibuat_tanggal,
		a.diubah_tanggal 
		FROM acc_auto_jurnal a 
		LEFT JOIN acc_akun b ON a.acc_akun_id=b.id
		LEFT JOIN acc_akun c ON a.acc_akun_id_debet=c.id
		LEFT JOIN acc_akun d ON a.acc_akun_id_kredit=d.id
		LEFT JOIN fwk_users f ON a.dibuat_oleh = f.id
		LEFT JOIN fwk_users g ON a.diubah_oleh = g.id "	;
		$search = array('b.nama', 'a.kode', 'a.ket','c.nama','d.nama',);
		$where  = null;
	   
		$isWhere = null;
		// $isWhere = 'artikel.deleted_at IS NULL';

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
    function index_get($segment_3 = '')
    {
		$id = $segment_3;
		$retrieve = $this->AccAutoJurnalModel->retrievebyId($id);
		
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
     }
	 function getAll_get()
	 {
		
		 $retrieve = $this->AccAutoJurnalModel->retrieve_all_akun();
				 
		 if (!empty($retrieve)) {
			 $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		 } else {
			 $this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		 }
	  }
	  function getAllDetail_get()
	 {
		
		 $retrieve = $this->AccAutoJurnalModel->retrieve_all_akun_detail();
				 
		 if (!empty($retrieve)) {
			 $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		 } else {
			 $this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		 }
	  }
	  function getAllKasbank_get()
	  {
		 
		  $retrieve = $this->AccAutoJurnalModel->retrieve_all_akun_kasbank();
				  
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
		$retrieve=  $this->AccAutoJurnalModel->create($input);
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);

	}

    function index_put($segment_3 = '')
    {
        # yang bisa edit akun adalah pengajar / admin
        // if (!is_pengajar() AND !is_admin()) {
        //     redirect('akun/index');
        // }

        $id = (int)$segment_3;
        $akun = $this->AccAutoJurnalModel->retrieve(array('id' => $id));
        if (empty($akun)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);

        }
		$input = $this->put();
		$input['diubah_oleh'] = $this->user_id;
         $retrieve=   $this->AccAutoJurnalModel->update($akun['id'], $input);
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);

          

      
    }

    function index_delete($segment_3 = '')
    {
        # yang bisa edit akun adalah pengajar / admin
        // if (!is_pengajar() AND !is_admin()) {
        //     redirect('akun/index');
        // }

		$id = (int)$segment_3;
        $akun = $this->AccAutoJurnalModel->retrieve(array('id' => $id));
        if (empty($akun)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);

        }

         $retrieve=  $this->AccAutoJurnalModel->delete($akun['id']);
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);


    }

    function detail($segment_3 = '')
    {
       
    }
}
