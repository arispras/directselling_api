<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
use Restserver\Libraries\REST_Controller;

class HrmsJenisAbsensi extends BD_Controller
{
	public $user_id;
	public $theCredential;

	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('HrmsJenisAbsensiModel');
		$this->load->model('M_DatatablesModel');
		$this->load->library('pdfgenerator');
		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id = $this->user_data->id;
	}
	
	public function list_post()
	{
		$post = $this->post();
		
		$query  = "SELECT a.*,
		d.user_full_name AS dibuat,
		e.user_full_name AS diubah  
		FROM hrms_jenis_absensi a
		LEFT JOIN fwk_users d ON a.dibuat_oleh = d.id
		LEFT JOIN fwk_users e ON a.diubah_oleh = e.id ";
		$search = array('kode', 'keterangan', 'tipe', 'jumlah_hk');
		$where  = null;
		
		$isWhere = null;
		// $isWhere = 'artikel.deleted_at IS NULL';
		
		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	function index_get($segment_3 = '')
	{
		$id = $segment_3;
		$retrieve = $this->HrmsJenisAbsensiModel->retrieve($id);
		
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAll_get()
	{
		
		$retrieve = $this->HrmsJenisAbsensiModel->retrieve_all_kategori();
		
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

		$retrieve=  $this->HrmsJenisAbsensiModel->create($input);
		if (!empty($retrieve)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'hrms_jenis_absensi', 'action' => 'new', 'entity_id' => $retrieve);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $input['no_transaksi']), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
		
	}
	
	function index_put($segment_3 = '')
	{
		$input = $this->put();
		$input['diubah_oleh']=$this->user_id;
		
		$id = (int)$segment_3;
		$kategori = $this->HrmsJenisAbsensiModel->retrieve( $id);
		if (empty($kategori)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);	
		}
		
		$res=   $this->HrmsJenisAbsensiModel->update($kategori['id'], $input );
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'hrms_jenis_absensi', 'action' => 'edit', 'entity_id' => $id);
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
		$kategori = $this->HrmsJenisAbsensiModel->retrieve($id);
		if (empty($kategori)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
			
		}
		
		$res=  $this->HrmsJenisAbsensiModel->delete($kategori['id']);
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id'=>$id)), 'entity' => 'inv_pemakaian_barang', 'action' => 'delete', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
		
	}
	
	
	
	
	
	
	function print_slip_get($segment_3 = '')
	{
		
		$id = (int)$segment_3;
		$data = [];
		
		$HrmsJenisAbsensi = $this->HrmsJenisAbsensiModel->print_slip( $id );
		$data['HrmsJenisAbsensi'] = $HrmsJenisAbsensi;

		$html = $this->load->view('HrmsJenisAbsensi_print', $data, true);
		
		$filename = 'report_' . time();
		$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
	}
}
