<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

class EstSpk extends BD_Controller
{
	public $user_id;
	public $theCredential;

	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('GbmOrganisasiModel');
		$this->load->model('EstSpkModel');
		$this->load->model('AccJurnalModel');
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
			c.nama_supplier as supplier
		FROM `est_spk_ht` a 
		left join gbm_organisasi b on a.lokasi_id=b.id
		left join gbm_supplier c on a.supplier_id=c.id 
		";
		$search = array('a.no_spk', 'a.tanggal', 'b.nama', 'c.nama_supplier');
		$where  = null;

		$isWhere = null;
		// $isWhere = 'artikel.deleted_at IS NULL';

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}

	function index_get($id = '')
	{
		$retrieve = $this->EstSpkModel->retrieve_by_id($id);
		// $retrieve['detail'] = $this->EstSpkModel->retrieve_detail($id);
		$detail = $this->EstSpkModel->retrieve_detail($id);
		$result = [];
		foreach ($detail as $row) {
			$row['bapp'] = $this->EstSpkModel->retrieve_detail_bapp($row['id']);
			$result[] = $row;
		}
		$retrieve['detail'] = $result;
		// $retrieve['detail_bapp'] = $this->EstSpkModel->retrieve_detail_bapp($id);
		// $retrieve['detail_log'] = $this->EstSpkModel->retrieve_detail_item($id);


		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAll_get()
	{
		$retrieve = $this->EstSpkModel->retrieve_all();
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAllPosted_get()
	{
		$retrieve = $this->EstSpkModel->retrieve_all_posted();
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getDetail_get($id = '')
	{
		$retrieve = $this->EstSpkModel->retrieve_detail($id);
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
		//$input['no_spat'] = $this->getLastNumber('pks_pengolahan_ht', 'no_spat', 'SPAT');
		// var_dump($input);

		$res = $this->EstSpkModel->create($input);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'est_spk', 'action' => 'new', 'entity_id' => $res);
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
		$data['diubah_oleh'] = $this->user_id;
		$res = $this->EstSpkModel->update($id, $data);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'est_spk', 'action' => 'edit', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	public function index_delete($id)
	{

		$res = $this->EstSpkModel->delete($id);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id' => $id)), 'entity' => 'est_spk', 'action' => 'delete', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function posting_post($segment_3 = null)
	{
		$id = (int) $segment_3;
		$retrieve_header = $this->EstSpkModel->retrieve_by_id($id);
		if (empty($retrieve_header)) {

			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
			return;
		} else {
		}
		$res = $this->EstSpkModel->posting($id, null);
		if (!empty($res)) {
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function closing_post($segment_3 = '')
	{
		$input = $this->post();
		$id = (int)$segment_3;

		$input['is_close'] = 1;
		$input['diubah_tanggal'] = date('Y-m-d H:i:s');
		$input['diubah_oleh'] = $this->user_id;

		// $retrieve = $this->PrcPpModel->closing($id['id'], $input);
		$this->db->query("UPDATE est_spk_ht  SET `is_close`='" . $input['is_close'] . "', `diubah_tanggal`='" . $input['diubah_tanggal'] . "', `diubah_oleh`=" . $input['diubah_oleh'] . " WHERE id=" . $id);

		$this->set_response(array("status" => "OK", "data" => true), REST_Controller::HTTP_OK);
	}
	function hitungPremi_post()
	{
		$input = $this->post();
		$this->hitung_premi($input);
	}
	function hitung_premi($input)
	{
		$resGaji = $this->db->query("select * from payroll_karyawan_gaji where karyawan_id=" . $input['karyawan_id']['id'] . " ")->row_array();
		$upahharian = ($resGaji['gapok'] / 25);
		$res = array(
			'rp_hk' => $upahharian,
			'premi' => 0,

		);

		if (!empty($res)) {
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function getPrice_post()
	{
		$input=$this->post();
		$q = "SELECT b.harga,c.kode AS kode_afdeling,d.kode AS kode_blok 
		from est_spk_ht a
		inner join est_spk_dt b on a.id=b.est_spk_id 
		inner join gbm_organisasi c on b.blok_id=c.id 
		inner join gbm_organisasi d on c.id=d.parent_id 
		where a.id=" . $input['spk_id'] . "
		and b.kegiatan_id=" . $input['kegiatan_id'] . " 
		and d.id=" . $input['blok_id'] . "";

		if  (!$input['spk_id'] || !$input['blok_id'] || !$input['kegiatan_id']){
			$res = array(
				'harga' => 0
	
			);
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
			return;
		}
		// $this->set_response(array("status" => "OK", "data" => var_dump($q)), REST_Controller::HTTP_CREATED);
		// return;
		$resSpk = $this->db->query($q)->row_array();
		$harga = 0;
		if ($resSpk) {
			$harga = ($resSpk['harga']);
		}

		$res = array(
			'harga' => $harga

		);

		if (!empty($res)) {
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
		d.tanggal AS tanggal,
		d.tgl_mulai as tgl_mulai,
		d.tgl_akhir AS tgl_akhir,
		d.estimasi AS estimasi,
		d.lokasi_id AS lokasi_id,
		d.supplier_id AS supplier_id,
		b.nama AS afdeling,
		c.nama AS kegiatan,
		d.no_spk AS no_spk,
		e.kode AS satuan,
		f.nama AS lokasi,
		g.nama_supplier as nama_supplier
		FROM est_spk_dt a 
		LEFT JOIN gbm_organisasi b ON a.blok_id=b.id
		LEFT JOIN acc_kegiatan c ON a.kegiatan_id=c.id
		LEFT JOIN est_spk_ht d ON a.est_spk_id=d.id
		LEFT JOIN gbm_uom e ON c.uom_id=e.id
		LEFT JOIN gbm_organisasi f ON d.lokasi_id=f.id
		LEFT JOIN gbm_supplier g ON d.supplier_id=g.id

		where d.tanggal between  '" . $tanggal_awal . "' and  '" . $tanggal_akhir . "'	
		";

		// if (!empty($no_po)) {
		// 	$queryData = $queryData."and h.no_po LIKE '%".$no_po."%' ";
		// }

		$filter_lokasi = "Semua";
		if ($lokasi_id) {
			$queryData = $queryData . " and d.lokasi_id=" . $lokasi_id . "";
			$res = $this->db->query("select * from gbm_organisasi where id=" . $lokasi_id . "")->row_array();
			$filter_lokasi = $res['nama'];
		}
		$filter_supplier = "Semua";
		if ($supplier_id) {
			$queryData = $queryData . " and d.supplier_id=" . $supplier_id . "";
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

		$html = $this->load->view('Est_Spk_Laporan', $data, true);

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

	function print_slip_get($segment_3 = '')
	{
		error_reporting(0);

		$id = (int)$segment_3;
		$data = [];

		$queryHeader = "SELECT 
		a.*,
		c.nama AS lokasi,
		d.nama_supplier AS supplier,
		a.estimasi as stimasi,
		a.tgl_mulai as mulai,
		a.tgl_akhir as akhir,
		a.id AS id,b.*
		FROM est_spk_ht a 
		LEFT join  est_spk_dt b ON a.id=b.est_spk_id
		LEFT JOIN gbm_organisasi c ON a.lokasi_id=c.id
		LEFT JOIN gbm_supplier d ON a.supplier_id=d.id
		WHERE a.id=" . $id . "
		";
		$dataHeader = $this->db->query($queryHeader)->row_array();

		$queryDetail = "SELECT 
		a.*,
		b.nama AS blok,
		c.nama AS kegiatan,
		a.id AS id,e.kode AS satuan
		FROM est_spk_dt a 
		LEFT JOIN gbm_organisasi b ON a.blok_id=b.id
		LEFT JOIN acc_kegiatan c ON a.kegiatan_id=c.id
		LEFT JOIN est_spk_ht d ON a.est_spk_id=d.id
		LEFT JOIN gbm_uom e ON c.uom_id=e.id
		WHERE d.id=" . $id . "
		";
		$dataDetail = $this->db->query($queryDetail)->result_array();

		$data['header'] = 	$dataHeader;
		$data['detail'] = 	$dataDetail;
		// $data['detail_item'] = 	$dataDetailItem;

		$html = $this->load->view('EstSpk_print', $data, true);

		$filename = 'report_' . time();
		$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		// echo $html;
	}
}
