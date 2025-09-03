<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
use Restserver\Libraries\REST_Controller;

class EstDendaPanen extends BD_Controller
{
	public $user_id;
	public $theCredential;

    function __construct()
    {
        parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('EstDendaPanenModel');
		$this->load->model('M_DatatablesModel');
		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id=$this->user_data->id;
    }

	public function list_post()
	{
		$post = $this->post();

		$query  = "SELECT 
		a.*,
		b.kode as kode_denda_panen,
		b.nama as nama_kode_denda_panen,
		c.nama as lokasi,
		d.user_full_name AS dibuat,
		e.user_full_name AS diubah from est_denda_panen a 
		left join est_kode_denda_panen b on a.kode_denda_panen_id=b.id 
		left join gbm_organisasi c on a.lokasi_id=c.id
		LEFT JOIN fwk_users d ON a.dibuat_oleh = d.id
		LEFT JOIN fwk_users e ON a.diubah_oleh = e.id";
		$search = array( 'a.tanggal_efektif','b.nama','b.kode','c.nama');
		$where  = null;
	   
		$isWhere = null;
		// $isWhere = 'artikel.deleted_at IS NULL';

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
    function index_get($segment_3 = '')
    {
		$id = $segment_3;
		$retrieve = $this->EstDendaPanenModel->retrieve($id);
		
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
     }
	 function getByKodeDendaId_get($segment_3 = '')
    {
		$id = $segment_3;
		$retrieve = $this->EstDendaPanenModel->retrieveByKodeDendaId($id);
		
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
     }
	 function getDendaPanenByTanggal_get($lokasi_id ,$kode_denda_panen_id,$tanggal)
	 {

		 $retrieve = $this->db->query(
			"select * from est_denda_panen where
			tanggal_efektif<= '" . $tanggal  . "' and lokasi_id =" . $lokasi_id . "
			and kode_denda_panen_id=".$kode_denda_panen_id ."
			order by tanggal_efektif desc limit 1 "
		)->row_array();
		 
		 if (!empty($retrieve)) {
			 $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		 } else {
			 $this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		 }
	  }
	 function getAll_get()
	 {
		
		 $retrieve = $this->EstDendaPanenModel->retrieve_all();
				 
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
		$res=  $this->EstDendaPanenModel->create($input);
			// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
			if (!empty($res)) {
				/* start audit trail */
				$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'est_denda_panen', 'action' => 'new', 'entity_id' => $res);
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
        $item = $this->EstDendaPanenModel->retrieve( $id);
        if (empty($item)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);

        }
		$input = $this->put();
		$input['diubah_oleh'] = $this->user_id;
         $res=   $this->EstDendaPanenModel->update($item['id'], $input);
			// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
			if (!empty($res)) {
				/* start audit trail */
				$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'est_denda_panen', 'action' => 'edit', 'entity_id' => $id);
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
        $item = $this->EstDendaPanenModel->retrieve ($id);
        if (empty($item)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);

        }

         $res=  $this->EstDendaPanenModel->delete($item['id']);
			// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
			if (!empty($res)) {
				/* start audit trail */
				$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id'=>$id)), 'entity' => 'est_denda_panen', 'action' => 'delete', 'entity_id' => $id);
				$this->db->insert('fwk_user_audit', $audit);
				/* end audit trail */
				$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
			} else {
				$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
			}

    }

    private function formatData($val)
    {


        if (!empty($val['inv_kategori_id'])) {
            $inv_kategori = $this->invkategori_model->retrieve($val['inv_kategori_id']);
            $val['inv_kategori'] = $inv_kategori;
        }

        return $val;
    }

    
   

   
   
}
