<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

class PrcRekap extends BD_Controller
{
	public $user_id;
	public $theCredential;

	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('GbmOrganisasiModel');
		$this->load->model('PrcRekapModel');
		$this->load->model('M_DatatablesModel');
		$this->load->library('pdfgenerator');
		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id = $this->user_data->id;
	}
	public function list_post()
	{
		$post = $this->post();
		$param = $post['parameter'];
		$query  = "SELECT 
		a.*,
		b.nama AS lokasi,
		d.no_spk AS spk,e.nama_supplier
		FROM prc_rekap_ht a 
		INNER JOIN gbm_organisasi b ON a.lokasi_id=b.id
		left JOIN prc_kontrak d ON a.spk_id=d.id
		left join gbm_supplier e on a.supplier_id=e.id
		";
		$search = array('no_rekap','e.nama_supplier','a.tanggal');
		$where  = null;
		$isWhere = " 1=1";

		if ($param['tgl_mulai'] && $param['tgl_mulai']) {
			$isWhere = " a.tanggal between '" . $param['tgl_mulai'] . "' and '" . $param['tgl_akhir'] . "'";
		}
		if ($param['lokasi_id']){
			$isWhere =$isWhere. " and a.lokasi_id =".$param['lokasi_id']."";
	
		}else{
			$isWhere = $isWhere. " and  a.lokasi_id in
			(select location_id from fwk_users_location where user_id=" . $this->user_id . ")";
		}

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}

	public function listRekapforInvoice_post($customer_id)
	{
		$post = $this->post();

		$query  = "SELECT 
		a.*,
		b.nama AS lokasi,
		d.no_spk AS spk,d.ppn
		FROM prc_rekap_ht a 
		INNER JOIN gbm_organisasi b ON a.lokasi_id=b.id
		INNER JOIN Prc_kontrak d ON a.spk_id=d.id";
		$search = array('no_rekap','d.no_spk');
		// $where  = null;
		$where  = array(
			'd.customer_id'=>$customer_id
		);

		$isWhere = null;
		// $isWhere = 'artikel.deleted_at IS NULL';

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}

	function index_get($id = '')
	{
		$retrieve = $this->PrcRekapModel->retrieve_by_id($id);
		$retrieve['detail'] = $this->PrcRekapModel->retrieve_detail($id);


		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function getAll_get()
	{
		$retrieve = $this->db->query("select a.*,b.no_spk from prc_rekap_ht a
		inner join prc_kontrak b
		on a.spk_id=b.id
		")->result_array();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAllbySupplierId_get($supp_id = '')
	{
		$retrieve = $this->db->query("select a.*,b.no_spk from prc_rekap_ht a inner join prc_kontrak b
		on a.spk_id=b.id
		where a.supplier_id=". $supp_id ."")->result_array();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getRekapPerTanggalBelumInvoice_get($id = '')
	{
		$query="select d.tanggal,sum(berat_terima)as berat_terima, c.harga from prc_rekap_ht a inner join prc_kontrak b
		on a.spk_id=b.id inner join prc_rekap_dt c
		on a.id=c.rekap_id inner join pks_timbangan d
		on c.pks_timbangan_id=d.id
		where a.id=". $id ." and a.id not in(select rekap_id from  acc_tbs_invoice_ht) 
        group by d.tanggal,c.harga
        order by d.tanggal";
		$retrieve = $this->db->query($query)->result_array();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => []), REST_Controller::HTTP_OK);
		}
	}
	function index_post()
	{
		$input = $this->post();
		$input['diubah_oleh'] = $this->user_id;
		$input['dibuat_oleh'] = $this->user_id;
		$this->load->library('Autonumber');
		$input['no_rekap']=$this->autonumber->prc_rekap($input['lokasi_id']['id'], $input['tanggal'],$input['supplier_id']['id']);
		//$input['no_spat'] = $this->getLastNumber('pks_pengolahan_ht', 'no_spat', 'SPAT');
		// var_dump($input);
		$res = $this->PrcRekapModel->create($input);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'prc_rekap', 'action' => 'new', 'entity_id' => $res);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $input['no_rekap']), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function index_put($segment_3 = null)
	{
		$id = (int)$segment_3;
		$data = $this->put();
		$data['dibuat_oleh'] = $this->user_id;

		$res = $this->PrcRekapModel->update($id, $data);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'prc_rekap', 'action' => 'edit', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	public function index_delete($id)
	{

		$res = $this->PrcRekapModel->delete($id);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id'=>$id)), 'entity' => 'prc_rekap', 'action' => 'delete', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function print_slip_get($id = '',$tipe = 'pdf')
	{
		// ini_set('display_errors', 1);
		$id = (int)$id;
		$data = [];

		$hd = $this->PrcRekapModel->print_slip_header($id);
		$data['hd'] = $hd;
		// $data['hd']['terbilang']=terbilang( $hd['total_tagihan']);
		$dt = $this->PrcRekapModel->print_slip_detail($id);
		$data['dt'] = $dt;

		// var_dump($hd);exit();
		$html = $this->load->view('PrcSlipRekap', $data, true);
		//  echo $html;exit();
		if ($tipe=='pdf'){
		$filename = 'report_PrcSlipRekap_' . time();
		$this->pdfgenerator->generate($html, $filename, true, 'A3', 'landscape');
		}else if ($tipe=='excel'){
			echo $html;
		}else if ($tipe=='html'){
			echo $html;
		}

		// echo $html;
	}
	function print_cover_get($id = '')
	{
		// ini_set('display_errors', 1);
		$id = (int)$id;
		$data = [];

		$hd = $this->PrcRekapModel->print_slip_header($id);
		$data['hd'] = $hd;
		// $data['hd']['terbilang']=terbilang( $hd['total_tagihan']);
		$dt = $this->PrcRekapModel->print_slip_detail($id);
		$data['dt'] = $dt;

		// var_dump($hd);exit();
		$html = $this->load->view('PrcCoverRekap', $data, true);
		//  echo $html;exit();
			$filename = 'report_PrcCoverRekap_' . time();
		$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
	

		// echo $html;
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
}
