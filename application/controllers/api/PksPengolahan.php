<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

class PksPengolahan extends BD_Controller
{
	public $user_id;
	public $theCredential;

	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('GbmOrganisasiModel');
		$this->load->model('PksPengolahanModel');
		$this->load->model('M_DatatablesModel');
		$this->load->library('pdfgenerator');
		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id=$this->user_data->id;
	}
	public function list_post()
	{
		$post = $this->post();

		$query  = "SELECT 
			a.*,
			b.nama AS nama_mill
		FROM pks_pengolahan_ht a 
		INNER JOIN gbm_organisasi b ON a.mill_id=b.id
		";
		$search = array('a.no_transaksi','b.nama');
		$where  = null;

		$isWhere = null;
		// $isWhere = 'artikel.deleted_at IS NULL';

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	
	function index_get($id = '')
	{
		$retrieve = $this->PksPengolahanModel->retrieve_by_id($id);
		$retrieve['detail'] = $this->PksPengolahanModel->retrieve_detail($id);
		$retrieve['detail_mesin'] = $this->PksPengolahanModel->retrieve_detail_mesin($id);
		$retrieve['detail_item'] = $this->PksPengolahanModel->retrieve_detail_item($id);


		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function getDetail_get($id='')
	{	
		$retrieve = $this->PksPengolahanModel->retrieve_detail($id);
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function index_post()
	{
		$input = $this->post();
		//$input['no_spat'] = $this->getLastNumber('pks_pengolahan_ht', 'no_spat', 'SPAT');
		// var_dump($input);
		$input['dibuat_oleh']=$this->user_id;
		$this->load->library('Autonumber');
		$input['no_transaksi']=$this->autonumber->pks_pengolahan($input['mill_id']['id'],$input['tanggal'],$input['supplier_id']['id']);
		$res = $this->PksPengolahanModel->create($input);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'pks_pengolahan', 'action' => 'new', 'entity_id' => $res);
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
		$data['dibuat_oleh']=$this->user_id;
		$data['diubah_oleh']=$this->user_id;
		$res = $this->PksPengolahanModel->update($id, $data);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'pks_pengolahan', 'action' => 'edit', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	public function index_delete($id)
	{

		$res = $this->PksPengolahanModel->delete($id);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id'=>$id)), 'entity' => 'pks_pengolahan', 'action' => 'delete', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}




	function get_logo_url($size = 'small')
	{
		return base_url('assets/images/logo-' . strtolower($size) . '.png');
	}

	/**
	 * Method untuk mendapatkan logo yang diatur
	 * @return string
	 */
	function get_logo_config()
	{
		$config = get_pengaturan('logo-company', 'value');
		if (empty($config)) {
			return get_logo_url('medium');
		} else {
			return get_url_image($config);
		}
	}


	function getLastNumber($table_name = '', $field = '', $prefix = '')
	{
		$lastnumber = $this->db->query("select  max(" . $field . ")as last from " . $table_name . "")->row_array();
		// var_dump($lastnumber);exit();
		if (!empty($lastnumber['last'])) {
			$str = (substr($lastnumber['last'], -6));
			$snumber = (int)$str + 1;
		} else {
			$snumber = 1;
		}
		$strnumber = sprintf("%06s", $snumber);
		return  $prefix . $strnumber;
		// $index = 11;
		// $prefix = 'B';
		// echo sprintf("%s%011s", $prefix, $index);
	}


	
	function print_slip_get($segment_3 = '')
	{
		error_reporting(0);

		$id = (int)$segment_3;
		$data = [];

		$queryHeader = "SELECT 
		a.*,
		b.nama AS mill,
		a.id AS id
		FROM pks_pengolahan_ht a 
		LEFT JOIN gbm_organisasi b ON a.mill_id=b.id
		WHERE a.id=" . $id . "";
		$dataHeader = $this->db->query($queryHeader)->row_array();

		$dataDetail = $this->db->query("SELECT
		a.*,
		b.nama AS shift,
		c.nama AS mandor,
		d.nama AS asisten,
		a.id AS id
		FROM pks_pengolahan_dt a
		LEFT JOIN pks_shift b ON a.shift_id=b.id
		LEFT JOIN karyawan c ON a.mandor_id=c.id
		LEFT JOIN karyawan d ON a.asisten_id=d.id
		WHERE a.pengolahan_id=".$id."
		")->result_array();

		$dataDetailMesin = $this->db->query("SELECT
		a.*,
		b.nama AS stasiun,
		c.nama AS mesin,
		a.id AS id
		FROM pks_pengolahan_mesin a
		LEFT JOIN gbm_organisasi b ON a.station_id=.b.id
		LEFT JOIN gbm_organisasi c ON a.mesin_id=c.id
		WHERE a.pengolahan_id=".$id."
		")->result_array();


		// $perminta = $this->InvPermintaanBarangModel->print_slip($id);
		$data['header'] = 	$dataHeader;
		$data['detail'] = 	$dataDetail;
		$data['mesin'] = 	$dataDetailMesin;

		$html = $this->load->view('PksPengolahan_laporan', $data, true);

		$filename = 'report_' . time();
		$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		// echo $html;
	}
	

}
