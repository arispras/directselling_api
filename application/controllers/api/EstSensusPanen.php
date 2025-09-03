<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

class EstSensusPanen extends BD_Controller
{
	public $user_id;
	public $theCredential;

	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('GbmOrganisasiModel');
		$this->load->model('EstSensusPanenModel');
		$this->load->model('M_DatatablesModel');
		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id=$this->user_data->id;
	}
	public function list_post()
	{
		$post = $this->post();
		$param = $post['parameter'];

		$query  = "SELECT a.*,
		b.nama as lokasi,
		c.nama AS afdeling FROM est_sensus_panen_ht a 
		inner join  gbm_organisasi b on a.lokasi_id=b.id
		inner join  gbm_organisasi c on a.afdeling_id=c.id";
		$search = array( 'c.nama', 'b.nama','a.bulan','a.tahun');
		$where  = null;

		$isWhere = " 1=1";
		if ($param['afdeling_id']) {
			$isWhere = $isWhere . " and a.afdeling_id =" . $param['afdeling_id'] . "";
		}
		// if ($param['tgl_mulai'] && $param['tgl_mulai']) {
		// 	$isWhere = " a.tanggal between '" . $param['tgl_mulai'] . "' and '" . $param['tgl_akhir'] . "'";
		// }
		

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	
	function index_get($id = '')
	{
		$retrieve = $this->EstSensusPanenModel->retrieve_by_id($id);
		$retrieve['detail'] = $this->EstSensusPanenModel->retrieve_detail($id);


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
		//$input['no_rekap_panen'] = $this->getLastNumber('est_rekap_panen_ht', 'no_rekap_panen', 'rekap_panen');
		// var_dump($input);
		$res = $this->EstSensusPanenModel->create($input);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'est_rekap_panen', 'action' => 'new', 'entity_id' => $res);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function index_put($segment_3 = null)
	{
		$id = (int)$segment_3;
		$data = $this->put();
		$data['diubah_oleh']=$this->user_id;
		$res = $this->EstSensusPanenModel->update($id, $data);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'est_rekap_panen', 'action' => 'edit', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	
	public function index_delete($id)
	{

		$res = $this->EstSensusPanenModel->delete($id);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id'=>$id)), 'entity' => 'est_rekap_panen', 'action' => 'delete', 'entity_id' => $id);
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
		

		$queryHeader = "SELECT a.*,
		b.nama as lokasi,
		c.nama AS afdeling FROM est_sensus_panen_ht a 
		inner join  gbm_organisasi b on a.lokasi_id=b.id
		inner join  gbm_organisasi c on a.afdeling_id=c.id   
		WHERE a.id=" . $id . "";
		$dataHeader = $this->db->query($queryHeader)->row_array();

		$queryDetail = "SELECT a.*, b.nama as blok, b.kode as kode
		FROM est_sensus_panen_dt a
		INNER JOIN gbm_organisasi b ON a.blok_id=b.id 
		WHERE a.sensus_panen_id=" . $id . "";
		$dataDetail = $this->db->query($queryDetail)->result_array();

		$data['header'] = 	$dataHeader;
		$data['detail'] = 	$dataDetail;

		// $user = $this->user_id;
		// if ($user) {
		// 	$retrieveProduk = $this->db->query("select * from fwk_users where id=" . $user . "")->row_array();
		// }
		// $data['dibuka']  = $retrieveProduk;

		$html = $this->load->view('EstSlipSensusPanen', $data, true);

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		echo $html;
	}
	
	

}
