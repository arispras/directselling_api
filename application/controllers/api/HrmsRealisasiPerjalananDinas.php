<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;
use Restserver\Libraries\REST_Controller;

class HrmsRealisasiPerjalananDinas extends BD_Controller
{
	public $user_id;
	public $theCredential;

	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('HrmsRealisasiPerjalananDinasModel');
		$this->load->model('M_DatatablesModel');
		$this->load->library('pdfgenerator');
		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id = $this->user_data->id;
	}
	public function list_post()
	{
		$post = $this->post();

		$query  = "SELECT 
					a.*,
					b.nama as lokasi,
                    c.nama as dari_lokasi,
                    d.nama as ke_lokasi,
                    e.nama as nama_karyawan
					FROM hrms_realisasi_perjalanan_dinas_ht a 
					LEFT JOIN gbm_organisasi b ON a.lokasi_id=b.id
                    LEFT JOIN gbm_organisasi c ON a.dari_lokasi_id=c.id
                    LEFT JOIN gbm_organisasi d ON a.ke_lokasi_id=d.id
                    LEFT JOIN karyawan e ON a.karyawan_id=e.id
		";
		$search = array('a.tanggal', 'b.nama',);
		$where  = null;

		$isWhere = null;
		// $isWhere = 'artikel.deleted_at IS NULL';

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}

	function index_get($id = '')
	{
		$retrieve = $this->HrmsRealisasiPerjalananDinasModel->retrieve_by_id($id);
		$retrieve['detail'] = $this->HrmsRealisasiPerjalananDinasModel->retrieve_detail($id);


		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function getAll_get()
	{

		$retrieve = $this->HrmsRealisasiPerjalananDinasModel->retrieve_all();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_OK);
		}
	}

	function getAllDetail_get($id = '')
	{
		$retrieve = $this->HrmsRealisasiPerjalananDinasModel->retrieve_all_detail($id);

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

		$this->load->library('Autonumber');
		$input['no_transaksi'] = $this->autonumber->hrms_realisasi_perjalanan_dinas($input['lokasi_id']['id'], $input['tanggal']);

		//$input['no_spat'] = $this->getLastNumber('pks_pengolahan_ht', 'no_spat', 'SPAT');
		// var_dump($input);
		$res = $this->HrmsRealisasiPerjalananDinasModel->create($input);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'hrms_realisasi_perjalanan_dinas', 'action' => 'new', 'entity_id' => $res);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $input['no_transaksi']), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function index_put($segment_3 = null)
	{
		$id = (int)$segment_3;
		$data = $this->put();
		$data['diubah_oleh'] = $this->user_id;

		$res = $this->HrmsRealisasiPerjalananDinasModel->update($id, $data);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'hrms_realisasi_perjalanan_dinas', 'action' => 'edit', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	public function index_delete($id)
	{

		$res = $this->HrmsRealisasiPerjalananDinasModel->delete($id);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id'=>$id)), 'entity' => 'hrms_realisasi_perjalanan_dinas', 'action' => 'delete', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function posting_post($segment_3 = null)
	{
		$id = (int)$segment_3;
		$data = $this->post();
		$retrieve_header = $this->HrmsRealisasiPerjalananDinasModel->retrieve_by_id($id);
		$data['diposting_oleh'] = $this->user_id;
		$data['tanggal'] =	$retrieve_header['tanggal'];
		$res = $this->HrmsRealisasiPerjalananDinasModel->posting($id, $data);
		if (!empty($res)) {
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function print_slip_get($segment_3 = '')
	{

		$id = (int)$segment_3;
		$data = [];

		$queryHeader = "SELECT a.*,
		b.nama AS lokasi,
		c.nama AS nama_karyawan,
		d.nama AS dari_lokasi,
		e.nama AS ke_lokasi 
		FROM hrms_realisasi_perjalanan_dinas_ht a
		INNER	JOIN gbm_organisasi b ON a.lokasi_id=b.id
		INNER	JOIN karyawan c ON a.karyawan_id=c.id
		INNER	JOIN gbm_organisasi d ON a.dari_lokasi_id=d.id
		INNER	JOIN gbm_organisasi e ON a.ke_lokasi_id=e.id  WHERE a.id=" . $id . "";
		$dataHeader = $this->db->query($queryHeader)->row_array();

		$queryDetail = "SELECT a.*,
		b.nama AS komponen 
		FROM hrms_realisasi_perjalanan_dinas_dt a
		INNER	JOIN hrms_komponen_perjalanan b ON a.komponen_perjalanan_dinas_id=b.id WHERE  a.realisasi_perjalanan_dinas_id = " . $id . "";
		$dataDetail = $this->db->query($queryDetail)->result_array();



		// $perminta = $this->HrmsRealisasiPerjalananDinasModel->print_slip($id);
		$data['header'] = 	$dataHeader;
		$data['detail'] = 	$dataDetail;

		$html = $this->load->view('HrmsSlipRealisasiPerjalananDinas', $data, true);

		$filename = 'report_' . time();
		$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		echo $html;
	}

}
