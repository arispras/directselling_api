<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
use Restserver\Libraries\REST_Controller;

class HrmsPermintaanSdm extends BD_Controller
{
	public $user_id;
	public $theCredential;

    function __construct()
    {
        parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('HrmsPermintaanSdmModel');
		$this->load->model('M_DatatablesModel');
		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id = $this->user_data->id;
    }

    public function list_post($alokasiKaryawan)
	{
		$post = $this->post();

		$query  = "SELECT a.*,a.id as id, j.nama as nama_jabatan,d.nama as nama_departemen,
		p.nama as nama_posisi,
		b.user_full_name AS dibuat,
		c.user_full_name AS diubah  
		from hrms_permintaan_sdm a
		left join payroll_jabatan j on a.jabatan_id=j.id
		left join hrms_posisi p on a.posisi_id=p.id
		left join payroll_department d on a.departement_id=d.id
		LEFT JOIN fwk_users b ON a.dibuat_oleh = b.id
		LEFT JOIN fwk_users c ON a.diubah_oleh = c.id ";
		$search = array( 'a.no_transaksi','a.tanggal','j.nama','p.nama','d.nama');
		$where  = null;
	   
		$isWhere = null;
		$where  = array('a.alokasi_karyawan' => $alokasiKaryawan);

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		if (count($data['data']) > 0) {
			for ($i = 0; $i < (count($data['data'])); $i++) {
				$pos = $data['data'][$i];
				$querySkill = "SELECT   b.nama as nama_skill FROM hrms_permintaan_sdm_skill a INNER JOIN hrms_skill b 
				ON a.skill_id=b.id 	where a.permintaan_sdm_id=" . $pos['id'] . "";
				$sk = $this->db->query($querySkill)->result_array();
				$data['data'][$i]['skill'] = $sk;
			}
		}
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
    function index_get($segment_3 = '')
    {
		$id = $segment_3;
		$retrieve = $this->HrmsPermintaanSdmModel->retrieve($id);
		$retrieveSkill = $this->HrmsPermintaanSdmModel->retrieve_all_skill($id);
		
		$retrieve['skill']=$retrieveSkill;
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
     }
	 function getAll_get()
	 {
		
		 $retrieve = $this->HrmsPermintaanSdmModel->retrieve_all();
				 
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
		$retrieve=  $this->HrmsPermintaanSdmModel->create($input);
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
        $gudang = $this->HrmsPermintaanSdmModel->retrieve($id);
        if (empty($gudang)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);

        }
		$input = $this->put();
		$input['diubah_oleh']=$this->user_id;
         $res=   $this->HrmsPermintaanSdmModel->update($gudang['id'], $input);
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
        $gudang = $this->HrmsPermintaanSdmModel->retrieve( $id);
        if (empty($gudang)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);

        }

         $res=  $this->HrmsPermintaanSdmModel->delete($gudang['id']);
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
