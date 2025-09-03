<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


use Restserver\Libraries\REST_Controller;

class EstSpkKendaraan extends BD_Controller
{
	public $user_id;
	public $theCredential;

	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('EstSpkKendaraanModel');
		$this->load->model('M_DatatablesModel');
		$this->auth();
		$this->load->library('pdfgenerator');
		$this->theCredential = $this->user_data;
		$this->user_id=$this->user_data->id;
	}

	// endpoint/list :POST
	public function list_post()
	{
		$post = $this->post();
		$param = $post['parameter'];
		
		$query  = "SELECT a.*,
		b.nama AS lokasi,
		c.nama_supplier AS supplier,
		d.nama AS kendaraan FROM est_spk_kendaraan a
		INNER JOIN gbm_organisasi b ON a.lokasi_id=b.id
		INNER JOIN gbm_supplier c ON a.kontraktor_id=c.id
		INNER JOIN trk_kendaraan d ON a.kendaraan_id=d.id 
		";

		$search = array('b.nama','c.nama_supplier','d.nama','a.tanggal','a.no_spk');
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

	// endpoint/ :GET
	function index_get($segment_3 = '')
	{
		$id = $segment_3;
		$retrieve = $this->EstSpkKendaraanModel->retrieve($id);
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	//  endpoint/getall :GET
	function getAll_get()
	{
		$retrieve = $this->EstSpkKendaraanModel->retrieve_all();
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAllbyEstate_get($estate_id)
	{
		$retrieve = $this->EstSpkKendaraanModel->retrieve_all_by_estate($estate_id);
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => []), REST_Controller::HTTP_OK);
		}
	}
	// endpoint/ :POST
	function index_post()
	{
		$input = $this->post();
		$input['dibuat_oleh']=$this->user_id;
		$res = $this->EstSpkKendaraanModel->create($input);
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'est_spk_kendaraan', 'action' => 'new', 'entity_id' => $res);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $input['no_spk']), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	// endpoint/ :PUT
	function index_put($segment_3 = '')
	{
		$id = (int)$segment_3;
		$gudang = $this->EstSpkKendaraanModel->retrieve($id);
		if (empty($gudang)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
		$input = $this->put();
		$input['diubah_oleh']=$this->user_id;
		$res = $this->EstSpkKendaraanModel->update($gudang['id'], $input);
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'est_spk_kendaraan', 'action' => 'edit', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	// endpoint/ :DELETE
	function index_delete($segment_3 = '')
	{
		$id = (int)$segment_3;
		$gudang = $this->EstSpkKendaraanModel->retrieve($id);
		if (empty($gudang)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
		$res =  $this->EstSpkKendaraanModel->delete($gudang['id']);
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id'=>$id)), 'entity' => 'est_spk_kendaraan', 'action' => 'delete', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function laporan_detail_post()
	{

		// $id = (int)$segment_3;
		$data = [];
		$format_laporan = $this->post('format_laporan', true);
		$input = [
			'lokasi_id' => 252,
			'tgl_mulai' => '2020-01-01',
			'tgl_akhir' => '2022-04-18',
		];

		$lokasi_id = $this->post('lokasi_id', true);
		$tanggal_awal = $this->post('tgl_mulai', true);
		$tanggal_akhir = $this->post('tgl_akhir', true);
		$supplier_id = $this->post('supplier_id', true);

		// $lokasi_id=$input['lokasi_id'];
		// $tanggal_awal=$input['tgl_mulai'];
		// $tanggal_akhir=$input['tgl_akhir'];

		$queryData = "SELECT 
		a.*,
		b.nama AS lokasi,
		c.nama_supplier AS supplier
		FROM est_spk_kendaraan a 
		LEFT JOIN gbm_organisasi b ON a.lokasi_id=b.id
		LEFT JOIN gbm_supplier c ON a.kontraktor_id=c.id

		where a.tanggal between  '" . $tanggal_awal . "' and  '" . $tanggal_akhir . "'	
		";

		// if (!empty($no_po)) {
		// 	$queryData = $queryData."and h.no_po LIKE '%".$no_po."%' ";
		// }

		$filter_lokasi = "Semua";
		if ($lokasi_id) {
			$queryData = $queryData . " and a.lokasi_id=" . $lokasi_id . "";
			$res = $this->db->query("select * from gbm_organisasi where id=" . $lokasi_id . "")->row_array();
			$filter_lokasi = $res['nama'];
		}
		$filter_supplier = "Semua";
		if ($supplier_id) {
			$queryData = $queryData . " and a.kontraktor_id=" . $supplier_id . "";
			$res = $this->db->query("select * from gbm_supplier where id=" . $supplier_id . "")->row_array();
			$filter_supplier = $res['nama_supplier'];
		}


		$dataPo = $this->db->query($queryData)->result_array();

		$data['po'] = 	$dataPo;
		$data['filter_supplier'] = 	$filter_supplier;
		$data['filter_lokasi'] = 	$filter_lokasi;
		$data['filter_tgl_awal'] = 	$tanggal_awal;
		$data['filter_tgl_akhir'] = $tanggal_akhir;
		$data['format_laporan'] = $format_laporan;

		$html = $this->load->view('Est_SpkKendaraan_Laporan', $data, true);

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		// echo $html;

		if ($format_laporan == 'view') {
			echo $html;
		} else if ($format_laporan == 'xls') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}

}
