<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
use Restserver\Libraries\REST_Controller;

class HrmsApprovallSettingPengajuanCuti extends Rest_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('HrmsApprovallSettingPengajuanCutiModel');
		$this->load->model('M_DatatablesModel');
		$this->load->library('pdfgenerator');
		
	}
	
	public function list_post()
	{
		$post = $this->post();
		
		$query  = "SELECT 
		a.kode, 
		a.id AS id, 
		b.nama AS lokasi, 
		c.nama AS karyawan, 
		a.is_finish 
		from hrms_approvall_setting_pengajuan_cuti a 
		left join gbm_organisasi b ON a.lokasi_id=b.id 
		left join karyawan c ON a.karyawan_id=c.id
		";
		$search = array('a.kode','b.nama','c.nama');
		$where  = null;
		
		$isWhere = null;
		// $isWhere = 'artikel.deleted_at IS NULL';
		
		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	function index_get($segment_3 = '')
	{
		$id = $segment_3;
		$retrieve = $this->HrmsApprovallSettingPengajuanCutiModel->retrieve($id);
		
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getByLokasiAndKode_get($lokasi_id ,$kode)
	{

		$retrieve = $this->HrmsApprovallSettingPengajuanCutiModel->retrieve_by_lokasi_and_kode($lokasi_id,$kode);
		
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getByLokasiKodeKaryawan_get($lokasi_id ,$kode,$karyawan_id)
	{

		$retrieve = $this->HrmsApprovallSettingPengajuanCutiModel->retrieve_by_lokasi_kode_karyawan($lokasi_id,$kode,$karyawan_id);
		
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getKaryawanByLokasiAndKode_get($lokasi_id ,$kode)
	{
		$retrieve = $this->db->query("SELECT a.*,b.nama as lokasi 
		FROM karyawan a inner join gbm_organisasi b on a.lokasi_tugas_id=b.id
		 WHERE a.id in( SELECT karyawan_id from hrms_approvall_setting_pengajuan_cuti WHERE lokasi_id=".$lokasi_id." and kode='".$kode."')")->result_array();
		
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "No Data", 'x'=>$lokasi_id), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAll_get()
	{
		
		$retrieve = $this->HrmsApprovallSettingPengajuanCutiModel->retrieve_all_kategori();
		
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function index_post()
	{
		$input = $this->post();

		$retrieve=  $this->HrmsApprovallSettingPengajuanCutiModel->create($input);
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		
	}
	
	function index_put($segment_3 = '')
	{
		$input = $this->put();
		
		$id = (int)$segment_3;
		$kategori = $this->HrmsApprovallSettingPengajuanCutiModel->retrieve( $id);
		if (empty($kategori)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);	
		}
		
		$retrieve=   $this->HrmsApprovallSettingPengajuanCutiModel->update($kategori['id'], $input );
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		
	}
	
	function index_delete($segment_3 = '')
	{
		
		$id = (int)$segment_3;
		$kategori = $this->HrmsApprovallSettingPengajuanCutiModel->retrieve($id);
		if (empty($kategori)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
			
		}
		
		$retrieve=  $this->HrmsApprovallSettingPengajuanCutiModel->delete($kategori['id']);
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		
		
	}
	
	
	
	
	
	
	function print_slip_get($segment_3 = '')
	{
		
		$id = (int)$segment_3;
		$data = [];
		
		$PrcApprovallSetting = $this->HrmsApprovallSettingPengajuanCutiModel->print_slip( $id );
		$data['PrcApprovallSetting'] = $PrcApprovallSetting;

		$html = $this->load->view('PrcApprovallSetting_print', $data, true);
		
		$filename = 'report_' . time();
		$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
	}
}
