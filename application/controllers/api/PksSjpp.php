<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

class PksSjpp extends BD_Controller
{
	public $user_id;
	public $theCredential;

	function __construct()
	{
		parent::__construct();
		$this->load->model('PksSjppModel');
		$this->load->model('PrcKontrakAngkutModel');
		$this->load->model('M_DatatablesModel');
		$this->load->library('pdfgenerator');
		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id=$this->user_data->id;
	}

	public function list_post()
	{
		$post = $this->post();
		$param = $post['parameter'];
		$query  = "select * from pks_timbangan_kirim_sj_vw ";
		//$query  = "select * from pks_sjpp";
		 $search = array('no_kendaraan','nama_supir','no_surat_jalan','no_tiket','nama_customer','no_spk','tanggal_surat_jalan','tanggal_terima','no_instruksi');
		//$search = array('tanggal');
		$where  = null;

		$isWhere = " 1=1";

		if ($param['tgl_mulai'] && $param['tgl_mulai']) {
			$isWhere = " tanggal_timbang between '" . $param['tgl_mulai'] . "' and '" . $param['tgl_akhir'] . "'";
		}
		if ($param['customer_id']){
			$isWhere = $isWhere. " and  customer_id =".$param['customer_id']."";
	
		}
		if ($param['kontrak_id']){
			$isWhere = $isWhere. " and  spk_id =".$param['kontrak_id']."";
	
		}
		if ($param['instruksi_id']){
			$isWhere = $isWhere. " and  instruksi_id =".$param['instruksi_id']."";
	
		}
		if (!empty($param['status_id'])) {
			if ($param['status_id'] == 'N') {
				$isWhere = $isWhere .  "  and tanggal_terima == '0000-00-00' or tanggal_terima is null";
			} else {
				$isWhere = $isWhere .  "  and tanggal_terima != '0000-00-00' and tanggal_terima is not null";
			}
		}
		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	function index_get($segment_3 = '')
	{
		$id = $segment_3;
		$retrieve = $this->PksSjppModel->retrieve($id);

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getSJCustomer_get($segment_3 = '')
	{
		$id = $segment_3;
		$retrieve = $this->PksSjppModel->retrieveSJCustomer($id);

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	
	function getAll_get()
	{

		$retrieve = $this->PksSjppModel->retrieveAll();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => []), REST_Controller::HTTP_OK);
		}
	}
	
	function getRekapKirim_get($spk_id = '',$periode_kt_dari,$periode_kt_sd)
	{

		
		// $retrieve = $this->PksSjppModel->retrieveRekapKirim($spk_id);
		$retrieve = $this->PksSjppModel->retrieveRekapKirimBySpkPeriode($spk_id,$periode_kt_dari,$periode_kt_sd);

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => []), REST_Controller::HTTP_OK);
		}
	}
	function getRekapAngkut_get($spk_id=null, $periode_kt_dari,$periode_kt_sd,$sls_kontrak_id)
	{
		$retrieve_spk = $this->PrcKontrakAngkutModel->retrieve($spk_id);
		$harga=$retrieve_spk['harga_satuan'];
		$transportir_id = $retrieve_spk['supplier_id'];
		$retrieve = $this->PksSjppModel->retrieveRekapTransportirByPeriode($transportir_id,$periode_kt_dari,$periode_kt_sd,$retrieve_spk['produk_id'],$sls_kontrak_id);
	
		if (!empty($retrieve)) {
			foreach ($retrieve as $key => $value) {
				$retrieve[$key]['harga']=$harga;
				$retrieve[$key]['total']=$harga* $retrieve[$key]['netto_customer'];
			}
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => []), REST_Controller::HTTP_OK);
		}
	}
	function getRekapAngkutInternal_get($spk_id=null, $periode_kt_dari,$periode_kt_sd,$sls_kontrak_id)
	{
		$retrieve_spk = $this->PrcKontrakAngkutModel->retrieve($spk_id);
		$harga=$retrieve_spk['harga_satuan'];
		$transportir_id = $retrieve_spk['supplier_id'];
		$retrieve = $this->PksSjppModel->retrieveRekapInternalTransportirByPeriode($transportir_id,$periode_kt_dari,$periode_kt_sd,$retrieve_spk['produk_id'],$sls_kontrak_id);
		
		if (!empty($retrieve)) {
			foreach ($retrieve as $key => $value) {
				$retrieve[$key]['harga']=$harga;
				$retrieve[$key]['total']=$harga* $retrieve[$key]['netto_kirim'];
			}
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => []), REST_Controller::HTTP_OK);
		}
	}
	function getRekapById_get($segment_3 = '')
	{
		$id = $segment_3;
		$retrieve = $this->PksSjppModel->retrieveRekapById($id);
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => []), REST_Controller::HTTP_OK);
		}
	}


	function getAllbyIdSpk_get()
	{

		$retrieve = $this->PksSjppModel->retrieve_byIdSpk();

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
		$input['no_surat'] = $this->getLastNumber('pks_sjpp', 'no_surat', 'SR');

		$res =  $this->PksSjppModel->create($input);
		// $this->set_response(array("status" => "OK", "data" => $input['no_surat'] ), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'pks_sjpp', 'action' => 'new', 'entity_id' => $res);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $input['no_surat']), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function update_sjpp_customer_post($id)
	{
		$input = $this->post();
		$retrieve =  $this->PksSjppModel->update_sjpp_customer($id,$input);
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
	}

	function index_put($segment_3 = '')
	{
		$input = $this->put();
		$input['diubah_oleh']=$this->user_id;

		$id = (int)$segment_3;
		$kategori = $this->PksSjppModel->retrieve($id);
		if (empty($kategori)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
		$res =   $this->PksSjppModel->update($kategori['id'], $input);
		// $this->set_response(array("status" => "OK", "data" => 55), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'pks_sjpp', 'action' => 'edit', 'entity_id' => $id);
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
		$kategori = $this->PksSjppModel->retrieve($id);
		if (empty($kategori)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$res =  $this->PksSjppModel->delete($kategori['id']);
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id'=>$id)), 'entity' => 'pks_sjpp', 'action' => 'delete', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
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

		$PksSjpp = $this->PksSjppModel->print_slip($id);
		$data['data'] = $PksSjpp;


		// echo'<pre>'; print_r($PksSjpp); die;
		//$PksSjpp['kode_barang'] = "PK";

		if ($PksSjpp['kode_barang'] == "CPO") {
			$html = $this->load->view('PksSjpp_cpo_print', $data, true);
		}else if ($PksSjpp['kode_barang'] == "PK") {
			$html = $this->load->view('PksSjpp_kernel_print', $data, true);
		}else {}

		$filename = 'report_' . time();
		$this->pdfgenerator->generate($html, $filename, true, 'A4', 'portrait');
	}


	function laporan_rekap_pengiriman_get()
	{
		error_reporting(0);

		$data = [];
		$input = $this->post();

		$data['PksSjpp'] = $this->PksSjppModel->laporanRekapPengiriman($input);
		// $data['count'] = $this->PksSjppModel->laporanRekapPengirimanCount( $input );
		$data['input'] = $input;

		$html = $this->load->view('PksSjpp_laporan', $data, true);

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');

		echo $html;
	}


	
}
