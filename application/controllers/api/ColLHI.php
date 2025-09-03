<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
require 'vendor/autoload.php';

use Dompdf\Adapter\CPDF;
use Dompdf\Dompdf;
use Dompdf\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;
use Dompdf\Positioner\NullPositioner;
use Restserver\Libraries\REST_Controller;

class ColLHI extends BD_Controller
{
	public $user_id;
	public $theCredential;

	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('ColLHIModel');
		$this->load->model('InvItemModel');
		$this->load->model('M_DatatablesModel');
		$this->load->library('pdfgenerator');
		$this->load->library('email');
		$this->load->library('image_lib');
		$this->load->library('upload');
		$this->load->model('PrcQuotationModel');

		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id = $this->user_data->id;
	}

	public function list_post()
	{
		$post = $this->post();
		$param = $post['parameter'];

		$query  = "SELECT a.*,b.nama AS lokasi,c.nama as nama_collector from col_lhi_ht a 
		left join gbm_organisasi b on a.lokasi_id=b.id
		left join karyawan c on a.collector_id=c.id";
		$search = array('no_lhi', 'a.tanggal',  'c.nama');
		$where  = null;



		$isWhere = " 1=1 ";
		if ($param['tgl_mulai'] && $param['tgl_mulai']) {
			$isWhere = " a.tanggal between '" . $param['tgl_mulai'] . "' and '" . $param['tgl_akhir'] . "'";
		}
		// if (!empty($param['customer_id'])) {
		// 	$isWhere = $isWhere .  "  and a.customer_id=" . $param['customer_id'] . "";
		// }

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		// if (count($data['data']) > 0) {
		// 	for ($i = 0; $i < (count($data['data'])); $i++) {
		// 		$so = $data['data'][$i];
		// 		$queryBayar = "SELECT sum(nilai)as bayar from sls_so_pembayaran 
		// 		where so_hd_id=" . $so['id'] . "";
		// 		$bayar = $this->db->query($queryBayar)->row_array();
		// 		$data['data'][$i]['bayar'] = $bayar ? $bayar['bayar'] : 0;
		// 	}
		// }


		// var_dump($data['data']);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	public function list_pembayaran_post($so_id)
	{
		$post = $this->post();
		$param = $post['parameter'];

		$query  = "SELECT * from sls_so_pembayaran";
		$search = array('tanggal', 'keterangan', 'tipe_pembayaran', 'jenis_invoice');
		$where  = null;


		$isWhere = " so_hd_id= " . $so_id;

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);

		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	public function list_invoice_post($so_id)
	{
		$post = $this->post();
		$param = $post['parameter'];

		$query  = "SELECT * from sls_so_invoice";
		$search = array('tanggal', 'no_invoice');
		$where  = null;


		$isWhere = " so_hd_id= " . $so_id;

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);

		$this->set_response($data, REST_Controller::HTTP_OK);
	}

	public function list_revisi_post()
	{
		$post = $this->post();

		$query  = "SELECT a.*, b.nama AS lokasi,c.nama_customer as nama_customer,d.no_quotation,d.no_referensi from sls_ttb_ht a 
		left join gbm_organisasi b on a.lokasi_id=b.id
		left join gbm_customer c on a.customer_id=c.id
		-- LEFT JOIN inv_pengiriman_so_ht e ON a.id=e.so_id
		left join prc_quotation d on a.quotation_id=d.id";
		$search = array('no_ttb', 'a.tanggal', 'a.catatan', 'c.nama_customer', 'd.no_quotation', 'd.no_referensi');
		$where  = null;

		$isWhere = null;
		// $isWhere = " e.so_id is null ";
		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);

		if (count($data['data']) > 0) {
			for ($i = 0; $i < (count($data['data'])); $i++) {
				$so = $data['data'][$i];
				$queryPP = "SELECT DISTINCT  c.id AS pp_id, c.no_pp FROM sls_so_dt a INNER JOIN prc_pp_dt b 
				ON a.pp_dt_id=b.id INNER JOIN prc_pp_ht c ON b.pp_hd_id=c.id
				where a.so_hd_id=" . $so['id'] . "";
				$pp = $this->db->query($queryPP)->result_array();
				$data['data'][$i]['pp_detail'] = $pp;
			}
		}

		// var_dump($data['data']);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	public function listByUserApprove_post()
	{
		$post = $this->post();

		$query  = "SELECT a.*, b.nama AS lokasi,d.nama_customer as nama_customer,e.no_quotation,e.no_referensi 
		from sls_ttb_ht a left join gbm_organisasi b on a.lokasi_id=b.id 
				inner join fwk_users c on a.last_approve_user=c.employee_id
				inner join gbm_customer d on a.customer_id=d.id
				left join prc_quotation e on a.quotation_id=e.id";
		$search = array('no_ttb', 'a.tanggal', 'a.catatan', 'd.nama_customer', 'e.no_quotation', 'e.no_referensi');

		$where  = null;

		$isWhere = null;
		$isWhere = "a.proses_approval=1 and a.status not in ('REJECTED','RELEASE') and c.id=" . $this->user_id;

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		if (count($data['data']) > 0) {
			for ($i = 0; $i < (count($data['data'])); $i++) {
				$so = $data['data'][$i];
				$queryPP = "SELECT DISTINCT  c.id AS pp_id, c.no_pp FROM sls_so_dt a INNER JOIN prc_pp_dt b 
				ON a.pp_dt_id=b.id INNER JOIN prc_pp_ht c ON b.pp_hd_id=c.id
				where a.so_hd_id=" . $so['id'] . "";
				$pp = $this->db->query($queryPP)->result_array();
				$data['data'][$i]['pp_detail'] = $pp;
			}
		}
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	public function listByUserApproveMobile_post()
	{
		$post = $this->post();

		$query  = "SELECT a.*, b.nama AS lokasi,d.nama_customer as nama_customer,e.no_quotation,e.no_referensi from sls_ttb_ht a left join gbm_organisasi b on a.lokasi_id=b.id 
				inner join fwk_users c on a.last_approve_user=c.employee_id
				inner join gbm_customer d on a.customer_id=d.id
				left join prc_quotation e on a.quotation_id=e.id
		where a.proses_approval=1 and a.status not in ('REJECTED','RELEASE') and c.id=" . $this->user_id . "";

		$data = $this->db->query($query)->result_array();
		if (count($data['data']) > 0) {
			for ($i = 0; $i < (count($data['data'])); $i++) {
				$so = $data['data'][$i];
				$queryPP = "SELECT DISTINCT  c.id AS pp_id, c.no_pp FROM sls_so_dt a INNER JOIN prc_pp_dt b 
				ON a.pp_dt_id=b.id INNER JOIN prc_pp_ht c ON b.pp_hd_id=c.id
				where a.so_hd_id=" . $so['id'] . "";
				$pp = $this->db->query($queryPP)->result_array();
				$data['data'][$i]['pp_detail'] = $pp;
			}
		}
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	public function countByUserApprove_post()
	{

		if ($this->user_id) {
			$query  = "SELECT COUNT(*)as jumlah from sls_ttb_ht a left join gbm_organisasi b on a.lokasi_id=b.id 
				inner join fwk_users c on a.last_approve_user=c.employee_id
				where a.proses_approval=1 and a.status not in ('REJECTED','RELEASE') and c.id=" . $this->user_id;

			$retrieve =	$this->db->query($query)->row_array();
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
			return;
		} else {
			$this->set_response(array("status" => "OK", "data" => []), REST_Controller::HTTP_OK);
			return;
		}
	}
	function index_get($segment_3 = '')
	{
		$id = $segment_3;
		$retrieve = $this->ColLHIModel->retrieve($id);
		$retrieve['detail'] = $this->ColLHIModel->retrieve_detail($id);

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function pembayaran_by_so_get($segment_3 = '')
	{
		$id = $segment_3;
		$retrieve = $this->ColLHIModel->retrieve_pembayaran_by_so($id);

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function invoice_by_so_get($segment_3 = '')
	{
		$id = $segment_3;
		$retrieve = $this->ColLHIModel->retrieve_invoice_by_so($id);

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function pembayaran_by_id_get($segment_3 = '')
	{
		$id = $segment_3;
		$retrieve = $this->ColLHIModel->retrieve_pembayaran_by_id($id);

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function invoice_by_id_get($segment_3 = '')
	{
		$id = $segment_3;
		$retrieve = $this->ColLHIModel->retrieve_pembayaran_by_id($id);

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getPOMobile_post($segment_3 = '')
	{
		$id = $segment_3;
		$retrieve = $this->ColLHIModel->retrieve($id);
		$retrieve['detail'] = $this->ColLHIModel->retrieve_detail($id);

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAll_get()
	{

		$retrieve = $this->ColLHIModel->retrieve_all_kategori();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function getAllByCustomer_get($supp_id)
	{

		$retrieve = $this->ColLHIModel->retrieve_all_by_customer($supp_id);

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => []), REST_Controller::HTTP_OK);
		}
	}
	function getAllSOReleaseByCustomer_get($supp_id)
	{

		$retrieve = $this->ColLHIModel->retrieve_all_so_release_by_customer($supp_id);

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => []), REST_Controller::HTTP_OK);
		}
	}
	function getLHIKuitansiBlmLunas_post()
	{
		$collector_id = $this->post('collector_id');
		$tanggal_mulai =  $this->post('tanggal_mulai', true);
		$tanggal_akhir =  $this->post('tanggal_akhir', true);
		$lokasi_id =  $this->post('lokasi_id', true);

		$sql = "SELECT a.*,b.kode_customer,b.nama_customer,nilai_kuitansi as sisa_angsuran  FROM col_kuitansi a INNER JOIN gbm_customer b 
		ON a.customer_id=b.id where  lokasi_id=" . $lokasi_id . " and ((
		tanggal_tempo between '" . $tanggal_mulai . "' and '" . $tanggal_akhir . "')or 
		(tanggal_janji between '" . $tanggal_mulai . "' and '" . $tanggal_akhir . "'))
		and collector_id=" . $collector_id . "
		order by a.no_kuitansi";
		$retrieve =	$this->db->query($sql)->result_array();

		if (!empty($retrieve)) {
			// Cek PO msh ada outstanding atau Tidak //

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
		//$input['no_ttb'] = $this->autonumber->Sales_order($input['lokasi_pp_id']['id'], $input['tanggal'], $input['customer_id']['id']);
		// $input['no_ttb'] = $this->autonumber->Sales_order($input['lokasi_id']['id'], $input['tanggal'], $input['lokasi_pp_id']['id']);
		$input['no_lhi'] = $this->autonumber->lhi($input['lokasi_id']['id'], $input['tanggal']);
		$res =  $this->ColLHIModel->create($input);
		// $this->set_response(array("status" => "OK", "data" => $input['no_ttb']), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'sls_so', 'action' => 'new', 'entity_id' => $res, 'key_text' => $input['no_ttb']);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $input['no_lhi']), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function simpan_pembayaran_post()
	{
		$input = $this->post();
		$input['diubah_oleh'] = $this->user_id;
		$input['dibuat_oleh'] = $this->user_id;
		$this->load->library('Autonumber');
		$res =  $this->ColLHIModel->create_pembayaran($input);
		// $this->set_response(array("status" => "OK", "data" => $input['no_ttb']), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'sls_so_pembayaran', 'action' => 'new', 'entity_id' => $res, 'key_text' => $res['id']);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $input['no_ttb']), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function simpan_invoice_post()
	{
		$input = $this->post();
		$input['diubah_oleh'] = $this->user_id;
		$input['dibuat_oleh'] = $this->user_id;
		$so = $this->db->query("select * from sls_ttb_ht where id=" . $input['so_id'] . "")->row_array();
		$so_id = $so['id'];
		$customer_id = $so['customer_id'];
		$this->load->library('Autonumber');
		$input['no_invoice'] = $this->autonumber->sales_order_invoice($input['tanggal'], $customer_id);

		$res =  $this->ColLHIModel->create_invoice($input);
		// $this->set_response(array("status" => "OK", "data" => $input['no_ttb']), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'sls_so_invoice', 'action' => 'new', 'entity_id' => $res, 'key_text' => $res['id']);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $input['no_invoice']), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function update_pembayaran_put($segment_3 = '')
	{
		$input = $this->put();
		$input['diubah_oleh'] = $this->user_id;
		$id = (int)$segment_3;
		$so = $this->ColLHIModel->retrieve_pembayaran_by_id($id);
		if (empty($so)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$res =   $this->ColLHIModel->update_pembayaran($so['id'], $input);
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'sls_so_pembayaran', 'action' => 'edit', 'entity_id' => $id, 'key_text' => $so['id']);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function update_invoice_put($segment_3 = '')
	{
		$input = $this->put();
		$input['diubah_oleh'] = $this->user_id;
		$id = (int)$segment_3;
		$so = $this->ColLHIModel->retrieve_invoice_by_id($id);
		if (empty($so)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$res =   $this->ColLHIModel->update_invoice($so['id'], $input);
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'sls_so_invoice', 'action' => 'edit', 'entity_id' => $id, 'key_text' => $so['id']);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function index_put($segment_3 = '')
	{
		$input = $this->put();
		$input['diubah_oleh'] = $this->user_id;
		$id = (int)$segment_3;
		$so = $this->ColLHIModel->retrieve($id);
		if (empty($so)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$res =   $this->ColLHIModel->update($so['id'], $input);
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'sls_so', 'action' => 'edit', 'entity_id' => $id, 'key_text' => $so['no_ttb']);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function revisi_put($segment_3 = '')
	{
		$input = $this->put();
		$input['diubah_oleh'] = $this->user_id;
		$id = (int)$segment_3;
		if (!$input['details']) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$so = $this->ColLHIModel->retrieve($id);

		if (!empty($so)) {
			$penerimaan = $this->db->query("SELECT b.so_id,a.* FROM sls_ttb_ht a
			LEFT JOIN inv_pengiriman_so_ht b ON b.so_id=a.id
			WHERE b.so_id=" . $so['id'] . "")->row_array();
			if (empty($penerimaan)) {
				$this->set_response(array("status" => "OK", "data" => "OK"), REST_Controller::HTTP_CREATED);
			} else {
				$this->set_response(array("status" => "INV_PO", "data" => "Sudah Ada Penerimaan"), REST_Controller::HTTP_CREATED);
				return;
			}
			$this->set_response(array("status" => "OK", "data" => $so), REST_Controller::HTTP_CREATED);
		}

		$res =   $this->ColLHIModel->revisi($so['id'], $input);
		if (!empty($res)) {
			if ($so['is_revisi'] != 1) { // JIka Posisi blm di revisi maka kembalikan ke posisi terakhir 
				/* Kembalikan ke status terkahir */
				if ($so['status_approve5']) { // ($status == 'PO5') {
					$ht['status'] = 'PO4';
					$ht['status_approve5'] = NULL;
					$ht['tgl_approve5'] = NULL;
					$ht['note_approve5'] = NULL;
					$ht['last_approve_position'] = 'PO5';
					$ht['last_approve_user'] = $so['user_approve5'];
					$karyawan_id = $ht['last_approve_user'];
					$ht['proses_approval'] = 1;
				} else if ($so['status_approve4']) { //($status == 'PO4') {
					$ht['status'] = 'PO3';
					$ht['status_approve4'] = NULL;
					$ht['tgl_approve4'] = NULL;
					$ht['note_approve4'] = NULL;
					$ht['last_approve_position'] = 'PO4';
					$ht['last_approve_user'] = $so['user_approve4'];
					$karyawan_id = $ht['last_approve_user'];
					$ht['proses_approval'] = 1;
				} else if ($so['status_approve3']) { //($status == 'PO3') {
					$ht['status'] = 'PO2';
					$ht['status_approve3'] = NULL;
					$ht['tgl_approve3'] = NULL;
					$ht['note_approve3'] = NULL;
					$ht['last_approve_position'] = 'PO3';
					$ht['last_approve_user'] = $so['user_approve3'];
					$karyawan_id = $ht['last_approve_user'];
					$ht['proses_approval'] = 1;
				} else if ($so['status_approve2']) { //($status == 'PO2') {
					$ht['status'] = 'PO1';
					$ht['status_approve2'] = NULL;
					$ht['tgl_approve2'] = NULL;
					$ht['note_approve2'] = NULL;
					$ht['last_approve_position'] = 'PO2';
					$ht['last_approve_user'] = $so['user_approve2'];
					$karyawan_id = $ht['last_approve_user'];
					$ht['proses_approval'] = 1;
				} else if ($so['status_approve1']) { //($status == 'PO1') {
					$ht['status'] = '';
					$ht['status_approve1'] = NULL;
					$ht['tgl_approve1'] = NULL;
					$ht['note_approve1'] = NULL;
					$ht['last_approve_position'] = 'PO1';
					$ht['last_approve_user'] = $so['user_approve1'];
					$karyawan_id = $ht['last_approve_user'];
					$ht['proses_approval'] = 1;
				} else {
					$ht['status'] = '';
					$ht['status_approve1'] = NULL;
					$ht['tgl_approve1'] = NULL;
					$ht['note_approve1'] = NULL;
					$ht['last_approve_position'] = '';
					$ht['last_approve_user'] = NULL;
					$ht['proses_approval'] = 0;
				}
			}
			$ht['is_revisi'] = 1;
			$this->db->where('id', $id);
			$this->db->update('sls_ttb_ht', $ht);
			if ($so['is_revisi'] != 1) {
				if ($karyawan_id) {
					$karyawan = $this->db->query("select email,nama from karyawan where id=" . $karyawan_id)->row_array();
					if ($karyawan['email'] || $karyawan['email'] != '') {
						$email_subject = 'Info Notifikasi Approval Revisi PO';
						$email_body    = 'Hallo, Bpk/Ibu. ' . $karyawan['nama'] . '! <br>
					<br>
					No PO : ' .	$so['no_ttb'] . ' direvisi dan diajukan ulang kepada Anda.<br> 
					Silakan melakukan approve pada Aplikasi Antech Sistem melalui   <a href="http://erp.dpaplant.com" target="_blank">AnTech Plantation - DPA</a>. 
					<br> <br> Salam 
					 <br> (Antech Sistem)';
						$this->email->set_newline("\r\n");
						$this->email->to($karyawan['email']);
						$this->email->from("support@antech-indonesia.com");
						$this->email->subject($email_subject);
						$this->email->message($email_body);
						if (!$this->email->send()) {
							//show_error($this->email->print_debugger());
						} else {
							//echo "email sent";
						}
					}
				}
			}
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'sls_so', 'action' => 'revisi', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function closing_post($segment_3 = '')
	{
		$input = $this->post();
		$id = (int)$segment_3;

		$input['status'] = "CLOSED";
		$input['diubah_tanggal'] = date('Y-m-d H:i:s');
		$input['diubah_oleh'] = $this->user_id;

		// $retrieve = $this->PrcPpModel->closing($id['id'], $input);
		$this->db->query("UPDATE sls_ttb_ht  SET `status`='" . $input['status'] . "', `diubah_tanggal`='" . $input['diubah_tanggal'] . "', `diubah_oleh`=" . $input['diubah_oleh'] . " WHERE id=" . $id);

		// $this->set_response(array("status" => "OK", "data" => true), REST_Controller::HTTP_OK);
	}

	function index_delete($segment_3 = '')
	{

		$id = (int)$segment_3;
		$so = $this->ColLHIModel->retrieve($id);
		if (empty($so)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$res =  $this->ColLHIModel->delete($so['id']);
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id' => $id)), 'entity' => 'sls_so', 'action' => 'delete', 'entity_id' => $id, 'key_text' => $so['no_ttb']);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function hapus_pembayaran_delete($segment_3 = '')
	{

		$id = (int)$segment_3;
		$so = $this->ColLHIModel->retrieve_pembayaran_by_id($id);
		if (empty($so)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$res =  $this->ColLHIModel->delete_pembayaran($so['id']);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id' => $id)), 'entity' => 'sls_so_pembayaran', 'action' => 'delete', 'entity_id' => $id, 'key_text' => $so['id']);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function hapus_invoice_delete($segment_3 = '')
	{

		$id = (int)$segment_3;
		$so = $this->ColLHIModel->retrieve_invoice_by_id($id);
		if (empty($so)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$res =  $this->ColLHIModel->delete_invoice($so['id']);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id' => $id)), 'entity' => 'sls_so_invoice', 'action' => 'delete', 'entity_id' => $id, 'key_text' => $so['id']);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function approval_post($segment_3 = '')
	{
		$input = $this->post();
		$input['diubah_oleh'] = $this->user_id;
		$id = (int)$segment_3;
		$so = $this->ColLHIModel->retrieve($id);
		if (empty($so)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}

		$retrieve =   $this->ColLHIModel->approval($so['id'], $input);
		if ($retrieve) {
			if ($input['karyawan_id']['id']) {
				$karyawan = $this->db->query("select email,nama from karyawan where id=" . $input['karyawan_id']['id'])->row_array();
				if ($karyawan['email'] || $karyawan['email'] != '') {
					$email_subject = 'Info Notifikasi Approval PO';
					$email_body    = 'Hallo, Bpk/Ibu. ' . $karyawan['nama'] . '! <br>
					<br>
					No PO : ' .	$so['no_ttb'] . ' diajukan kepada Anda.<br> 
					Silakan melakukan approve pada Aplikasi Antech Sistem melalui   <a href="http://erp.dpaplant.com" target="_blank">AnTech Plantation - DPA</a>. 
					<br> <br> Salam 
					 <br> (Antech Sistem)';
					$this->email->set_newline("\r\n");
					$this->email->to($karyawan['email']);
					$this->email->from("support@antech-indonesia.com");
					$this->email->subject($email_subject);
					$this->email->message($email_body);
					if (!$this->email->send()) {
						//show_error($this->email->print_debugger());
					} else {
						//echo "email sent";
					}
				}
				$usr = $this->db->query("select * from fwk_users where employee_id=" . $input['karyawan_id']['id'] . "")->row_array();
				if ($usr['fcm_token']) {
					$title = "Approval PO";
					$message = "Hallo, Bpk/Ibu. " . $karyawan['nama'] . "!. No PO : " .	$so['no_ttb'] . " diajukan kepada Anda.";
					$this->sendNotification($usr['fcm_token'], $title, $message, '');
				}
			} else {
			}

			$so = $this->ColLHIModel->retrieve($id);
			if ($so['status'] == 'RELEASE') {
				$query_email = "SELECT a.new_, a.user_id,b.user_name,d.nama,d.email  FROM fwk_users_acces a INNER JOIN fwk_users b ON a.user_id=b.id
				INNER JOIN fwk_menu c ON a.menu_id=c.id
				INNER JOIN karyawan d ON  b.employee_id=d.id
				WHERE NAME='sls_so' and a.new_=1 ";
				$res_email = $this->db->query($query_email)->result_array();
				if ($res_email) {
					foreach ($res_email as $key => $value) {
						if ($value['email']) {
							$email_subject = 'Info Notifikasi Approval PO';
							$email_body    = 'Hallo, Bpk/Ibu. ' . $value['nama'] . '! <br>
					<br>
					No PO : ' .	$so['no_ttb'] . ' Sudah Di-Release.<br> 
					Silakan melakukan pemeriksaan pada Aplikasi Antech Sistem melalui   <a href="http://erp.dpaplant.com" target="_blank">AnTech Plantation - DPA</a>. 
					<br> <br> Salam 
					 <br> (Antech Sistem)';
							$this->email->set_newline("\r\n");
							$this->email->to($value['email']);
							$this->email->from("support@antech-indonesia.com");
							$this->email->subject($email_subject);
							$this->email->message($email_body);
							if (!$this->email->send()) {
								//show_error($this->email->print_debugger());
							} else {
								//echo "email sent";
							}
						}
					}
				}
			}

			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => []), REST_Controller::HTTP_OK);
		}
	}
	function reject_post($segment_3 = '')
	{
		$input = $this->post();
		$input['diubah_oleh'] = $this->user_id;
		$id = (int)$segment_3;
		$so = $this->ColLHIModel->retrieve($id);
		if (empty($pp)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$retrieve =   $this->ColLHIModel->reject($so['id'], $input);
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
	}

	function posting_post($segment_3 = null)
	{
		$id = (int)$segment_3;
		$data = $this->post();
		$data['diubah_oleh'] = $this->user_id;
		$ttb = $this->ColLHIModel->retrieve($id);
		if (empty($ttb)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
		$tenor = $ttb['tenor'];
		$no_ttb = $ttb['no_ttb'];
		$tgl_ttb = $ttb['tanggal'];
		$collector_id = $ttb['collector_id'];
		$customer_id = $ttb['customer_id'];
		$nilai_angsuran = $ttb['total_nilai_angsuran'];

		$this->db->where('ttb_id', $id);
		$this->db->delete('col_kuitansi');
		for ($i = 0; $i < $tenor; $i++) {
			$date = new DateTime($tgl_ttb);
			$date->modify('+1 month');
			$tanggal_tempo = $date->format('Y-m-d');
			$angsuran_ke = $i + 1;
			$no_kuitansi = $no_ttb . "." . sprintf("%02s", $angsuran_ke);



			$this->db->insert("col_kuitansi", array(
				'ttb_id' => $id,
				'no_kuitansi' => $no_kuitansi,
				'angsuran_ke' => $angsuran_ke,
				'nilai_kuitansi' => $nilai_angsuran,
				'nilai_kuitansi_ori' => $nilai_angsuran,
				'collector_id' => $collector_id,
				'customer_id' => $customer_id,
				'tanggal_tempo' => $tanggal_tempo,
				'keterangan' => '',

			));
		}

		$res = $this->ColLHIModel->posting($id, $data);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id' => $id)), 'entity' => 'sls_so', 'action' => 'posting', 'entity_id' => $id, 'key_text' => $so['no_ttb']);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAllDetail_get($so_id)
	{
		$retrieve = array();
		$dtl = $this->ColLHIModel->retrieve_so_dtl($so_id);
		$retrieve['dtl'] = $dtl;
		$retrieve['AKUN_PPN'] = $this->db->query("select * from acc_auto_jurnal where kode='PPN_KELUARAN'")->row_array()['acc_akun_id'];
		$retrieve['AKUN_PENGIRIMAN_BARANG_SO'] = $this->db->query("select * from acc_auto_jurnal where kode='PENGIRIMAN_BARANG_SO'")->row_array()['acc_akun_id'];
		$retrieve['AKUN_DISKON'] = $this->db->query("select * from acc_auto_jurnal where kode='DISKON_PENJUALAN'")->row_array()['acc_akun_id'];
		$retrieve['AKUN_PPH'] = $this->db->query("select * from acc_auto_jurnal where kode='PPH_PENJUALAN'")->row_array()['acc_akun_id'];
		$retrieve['AKUN_PPBKB'] = $this->db->query("select * from acc_auto_jurnal where kode='PPBKB_PENJUALAN'")->row_array()['acc_akun_id'];
		$retrieve['AKUN_BIAYA_KIRIM'] = $this->db->query("select * from acc_auto_jurnal where kode='BIAYA_KIRIM_SO'")->row_array()['acc_akun_id'];
		$retrieve['AKUN_BIAYA_LAIN'] = $this->db->query("select * from acc_auto_jurnal where kode='BIAYA_LAIN_SO'")->row_array()['acc_akun_id'];

		// $retrieve['AKUN_PPN'] = $this->db->query("select * from acc_auto_jurnal where kode='PPN_MASUKAN'")->row_array()['acc_akun_id'];
		// $retrieve['AKUN_PENGIRIMAN_BARANG_SO'] = $this->db->query("select * from acc_auto_jurnal where kode='PENERIMAAN_BARANG_PO'")->row_array()['acc_akun_id'];
		// $retrieve['AKUN_DISKON'] = $this->db->query("select * from acc_auto_jurnal where kode='DISKON_PEMBELIAN'")->row_array()['acc_akun_id'];
		// $retrieve['AKUN_PPH'] = $this->db->query("select * from acc_auto_jurnal where kode='PPH_PEMBELIAN'")->row_array()['acc_akun_id'];
		// $retrieve['AKUN_PPBKB'] = $this->db->query("select * from acc_auto_jurnal where kode='PPBKB_PEMBELIAN'")->row_array()['acc_akun_id'];
		// $retrieve['AKUN_BIAYA_KIRIM'] = $this->db->query("select * from acc_auto_jurnal where kode='BIAYA_KIRIM_PO'")->row_array()['acc_akun_id'];
		// $retrieve['AKUN_BIAYA_LAIN'] = $this->db->query("select * from acc_auto_jurnal where kode='BIAYA_LAIN_PO'")->row_array()['acc_akun_id'];
		$ret_so_ht = $this->db->query("select * from sls_ttb_ht where id= " . $so_id)->row_array();
		$retrieve['SO_HT'] =	$ret_so_ht;
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAllDetailBlmTerkirim_get($so_id)
	{
		$retrieve = $this->ColLHIModel->retrieve_so_dtl_blm_terkirim($so_id);

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAllDetailSdhTerkirim_get($so_id)
	{
		$retrieve = array();
		$dtl = $this->ColLHIModel->retrieve_so_dtl_sdh_terkirim($so_id);
		$retrieve['dtl'] = $dtl;
		$retrieve['AKUN_PPN'] = $this->db->query("select * from acc_auto_jurnal where kode='PPN_KELUARAN'")->row_array()['acc_akun_id'];
		$retrieve['AKUN_PENGIRIMAN_BARANG_SO'] = $this->db->query("select * from acc_auto_jurnal where kode='AKUN_PENGIRIMAN_BARANG_SO'")->row_array()['acc_akun_id'];
		$retrieve['AKUN_DISKON'] = $this->db->query("select * from acc_auto_jurnal where kode='DISKON_PENJUALAN'")->row_array()['acc_akun_id'];
		$retrieve['AKUN_PPH'] = $this->db->query("select * from acc_auto_jurnal where kode='PPH_PENJUALAN'")->row_array()['acc_akun_id'];
		$retrieve['AKUN_PPBKB'] = $this->db->query("select * from acc_auto_jurnal where kode='PPBKB_PENJUALAN'")->row_array()['acc_akun_id'];
		$retrieve['AKUN_BIAYA_KIRIM'] = $this->db->query("select * from acc_auto_jurnal where kode='BIAYA_KIRIM_SO'")->row_array()['acc_akun_id'];
		$retrieve['AKUN_BIAYA_LAIN'] = $this->db->query("select * from acc_auto_jurnal where kode='BIAYA_LAIN_SO'")->row_array()['acc_akun_id'];

		$ret_so_ht = $this->db->query("select * from sls_ttb_ht where id= " . $so_id)->row_array();
		$retrieve['SO_HT'] =	$ret_so_ht;
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}


	function print_slip_so_get($segment_3 = '')
	{

		$id = (int)$segment_3;
		$data = [];

		$queryHeader = "SELECT a.*,
		f.nama_customer,
		a.alamat_pengiriman as alamat_customer,
		a.telp_pengiriman as no_telepon_customer,
		a.contact_pengiriman as contact_person_customer,
		g.jenis as jenis_bayar,
		g.ket as ket_bayar
		FROM sls_ttb_ht a 
		left JOIN gbm_organisasi e ON a.lokasi_id=e.id
		left JOIN gbm_customer f ON a.customer_id=f.id
		left JOIN prc_syarat_bayar g ON a.syarat_bayar_id=g.id
		WHERE a.id=" . $id . "";

		$dataHeader = $this->db->query($queryHeader)->row_array();

		$queryDetail = "SELECT a.*,
		b.kode as kode_barang,
		b.nama as nama_barang,
		f.nama as uom
		FROM sls_so_dt a 
		LEFT join inv_item b on a.item_id=b.id 
		LEFT join gbm_uom f on b.uom_id=f.id 
		WHERE  a.so_hd_id = " . $id . "";
		$dataDetail = $this->db->query($queryDetail)->result_array();

		// foreach ($dataDetail as $key => $value) {
		// 	$stok = $this->InvItemModel->cek_stok_lokasi_get($value['lokasi_pp_id'], $value['item_id'], $dataHeader['tanggal']);
		// 	$dataDetail[$key]['stok'] = $stok;
		// }

		// $queryUser = "SELECT a.*, b.nama as peminta FROM fwk_users a LEFT JOIN karyawan b ON a.employee_id=b.id WHERE a.id=" . $dataHeader['dibuat_oleh'];
		// $dataUser = $this->db->query($queryUser)->row_array();

		// var_dump($dataUser); die;


		// $perminta = $this->InvPermintaanBarangModel->print_slip($id);
		$data['header'] = 	$dataHeader;
		$data['detail'] = 	$dataDetail;
		// $data['user'] = $dataUser;


		$data['database'] = $this->db;

		$html = $this->load->view('SlsTTB_laporan', $data, true);

		$filename = 'report_so_' . time();
		$this->pdfgenerator->generate($html, $filename, true, 'A4', 'portrait');
		// echo $html;
	}
	function print_slip_invoice_get($segment_3 = '')
	{

		$id = (int)$segment_3;
		$data = [];
		// $queryInv = "SELECT * from sls_so_invoice
		// WHERE id=" . $id . "";
		// $dataInv = $this->db->query($queryInv)->row_array();


		$queryHeader = "SELECT a.*,
		f.nama_customer,
		a.alamat_pengiriman as alamat_customer,
		a.telp_pengiriman as no_telepon_customer,
		a.contact_pengiriman as contact_person_customer,
		g.jenis as jenis_bayar,
		g.ket as ket_bayar
		FROM sls_ttb_ht a 
		INNER JOIN gbm_organisasi e ON a.lokasi_id=e.id
		INNER JOIN gbm_customer f ON a.customer_id=f.id
		INNER JOIN prc_syarat_bayar g ON a.syarat_bayar_id=g.id
		WHERE a.id=" . $id . "";
		$dataHeader = $this->db->query($queryHeader)->row_array();

		$queryDetail = "	SELECT a.*,
		b.kode as kode_barang,
		b.nama as nama_barang,
		c.nama as uom
		FROM sls_so_dt a 
		LEFT join inv_item b on a.item_id=b.id 
		LEFT join gbm_uom c on b.uom_id=c.id 

		WHERE  a.so_hd_id = " . $id . "";
		$dataDetail = $this->db->query($queryDetail)->result_array();

		$this->load->helper("terbilangv2");

		$terbilang = terbilang($dataHeader['grand_total']);
		$data['header'] = 	$dataHeader;
		$data['detail'] = 	$dataDetail;
		$data['invoice'] = [];
		$data['terbilang'] = 	$terbilang;


		$data['database'] = $this->db;

		$html = $this->load->view('SlsTTBSlipInvoice', $data, true);

		$filename = 'report_invoice_' . time();
		$this->pdfgenerator->generate($html, $filename, true, 'A4', 'portrait');
		// echo $html;
	}
	function print_slip_html_get($segment_3 = '')
	{

		$id = (int)$segment_3;
		$data = [];

		$queryHeader = "SELECT a.*,

		f.nama_customer as nama_customer,
		f.alamat as alamat_customer,
		f.no_telpon as no_telepon_customer,
		f.nama_bank as nama_bank,
		f.no_rekening as no_rekening,
		f.atas_nama as atas_nama,
		f.contact_person as contact_person_customer,
		f.no_hp as no_hp_customer,

		g.jenis as jenis_bayar,
		g.ket as ket_bayar,

		h.nama as nama_franco,
		h.alamat as alamat_franco,
		h.contact as contact_franco,
		h.telp as telp_franco,

		i.kode as mata_uang_kode,
		i.simbol as mata_uang_simbol,
		i.nama as mata_uang_nama,

		z.nama as user_approve1,
		x.nama as user_approve_jabatan1,
		zz.nama as user_approve2,
		xx.nama as user_approve_jabatan2,
		zzz.nama as user_approve3,
		xxx.nama as user_approve_jabatan3,
		zzzz.nama as user_approve4,
		xxxx.nama as user_approve_jabatan4,
		zzzzz.nama as user_approve5,
		xxxxx.nama as user_approve_jabatan5,

		e.nama as lokasi
		FROM sls_ttb_ht a 
		INNER JOIN gbm_organisasi e ON a.lokasi_id=e.id
		INNER JOIN gbm_customer f ON a.customer_id=f.id
		INNER JOIN prc_syarat_bayar g ON a.syarat_bayar_id=g.id
		INNER JOIN prc_franco h ON a.franco_id=h.id
		LEFT JOIN karyawan z ON a.user_approve1=z.id
		LEFT JOIN payroll_jabatan x ON z.jabatan_id=x.id
		LEFT JOIN karyawan zz ON a.user_approve2=zz.id
		LEFT JOIN payroll_jabatan xx ON zz.jabatan_id=xx.id
		LEFT JOIN karyawan zzz ON a.user_approve3=zzz.id
		LEFT JOIN payroll_jabatan xxx ON zzz.jabatan_id=xxx.id
		LEFT JOIN karyawan zzzz ON a.user_approve4=zzzz.id
		LEFT JOIN payroll_jabatan xxxx ON zzzz.jabatan_id=xxxx.id
		LEFT JOIN karyawan zzzzz ON a.user_approve5=zzzzz.id
		LEFT JOIN payroll_jabatan xxxxx ON zzzzz.jabatan_id=xxxxx.id
		LEFT JOIN acc_mata_uang i ON a.mata_uang_id=i.id
		LEFT JOIN karyawan j ON a.dibuat_oleh=j.id
		WHERE a.id=" . $id . "";
		$dataHeader = $this->db->query($queryHeader)->row_array();

		$queryDetail = "SELECT a.*,
		b.kode as kode_barang,
		b.nama as nama_barang,
		f.nama as uom,
		d.lokasi_id as lokasi_pp_id
		FROM sls_so_dt a 
		INNER JOIN prc_pp_dt c on a.pp_dt_id=c.id
		inner join prc_pp_ht d on c.pp_hd_id=d.id
		LEFT join inv_item b on a.item_id=b.id 
		LEFT join gbm_uom f on b.uom_id=f.id 
		WHERE  a.so_hd_id = " . $id . "";
		$dataDetail = $this->db->query($queryDetail)->result_array();

		foreach ($dataDetail as $key => $value) {
			$stok = $this->InvItemModel->cek_stok_lokasi_get($value['lokasi_pp_id'], $value['item_id'], $dataHeader['tanggal']);
			$dataDetail[$key]['stok'] = $stok;
		}

		$queryUser = "SELECT a.*, b.nama as peminta FROM fwk_users a LEFT JOIN karyawan b ON a.employee_id=b.id WHERE a.id=" . $dataHeader['dibuat_oleh'];
		$dataUser = $this->db->query($queryUser)->row_array();

		$data['header'] = 	$dataHeader;
		$data['detail'] = 	$dataDetail;
		$data['user'] = $dataUser;


		$data['database'] = $this->db;

		$html = $this->load->view('SlsTTB_laporan', $data, true);

		echo $html;
	}
	function print_slip_cek_harga_get($segment_3 = '')
	{

		$id = (int)$segment_3;
		$data = [];

		$queryHeader = "SELECT a.*,
		-- d.nama as gudang,
		f.nama_customer as nama_customer,
		f.alamat as alamat_customer,
		f.no_telpon as no_telepon_customer,
		f.nama_bank as nama_bank,
		f.no_rekening as no_rekening,
		f.atas_nama as atas_nama,
		f.contact_person as contact_person_customer,
		f.no_hp as no_hp_customer,

		g.jenis as jenis_bayar,
		g.ket as ket_bayar,

		h.nama as nama_franco,
		h.alamat as alamat_franco,
		h.contact as contact_franco,
		h.telp as telp_franco,

		i.kode as mata_uang_kode,
		i.simbol as mata_uang_simbol,
		i.nama as mata_uang_nama,

		z.nama as user_approve1,
		x.nama as user_approve_jabatan1,
		zz.nama as user_approve2,
		xx.nama as user_approve_jabatan2,
		zzz.nama as user_approve3,
		xxx.nama as user_approve_jabatan3,
		zzzz.nama as user_approve4,
		xxxx.nama as user_approve_jabatan4,
		zzzzz.nama as user_approve5,
		xxxxx.nama as user_approve_jabatan5,

		e.nama as lokasi
		FROM sls_ttb_ht a 
		-- INNER JOIN gbm_organisasi d ON a.gudang_id=d.id
		INNER JOIN gbm_organisasi e ON a.lokasi_id=e.id
		INNER JOIN gbm_customer f ON a.customer_id=f.id
		INNER JOIN prc_syarat_bayar g ON a.syarat_bayar_id=g.id
		INNER JOIN prc_franco h ON a.franco_id=h.id

		LEFT JOIN karyawan z ON a.user_approve1=z.id
		LEFT JOIN payroll_jabatan x ON z.jabatan_id=x.id
		LEFT JOIN karyawan zz ON a.user_approve2=zz.id
		LEFT JOIN payroll_jabatan xx ON zz.jabatan_id=xx.id
		LEFT JOIN karyawan zzz ON a.user_approve3=zzz.id
		LEFT JOIN payroll_jabatan xxx ON zzz.jabatan_id=xxx.id
		LEFT JOIN karyawan zzzz ON a.user_approve4=zzzz.id
		LEFT JOIN payroll_jabatan xxxx ON zzzz.jabatan_id=xxxx.id
		LEFT JOIN karyawan zzzzz ON a.user_approve5=zzzzz.id
		LEFT JOIN payroll_jabatan xxxxx ON zzzzz.jabatan_id=xxxxx.id
		
		LEFT JOIN acc_mata_uang i ON a.mata_uang_id=i.id
		LEFT JOIN karyawan j ON a.dibuat_oleh=j.id
		WHERE a.id=" . $id . "";
		$dataHeader = $this->db->query($queryHeader)->row_array();

		$queryDetail = "SELECT a.*,
		b.kode as kode_barang,
		b.nama as nama_barang,
		f.nama as uom,
		d.lokasi_id as lokasi_pp_id
		FROM sls_so_dt a 
		INNER JOIN prc_pp_dt c on a.pp_dt_id=c.id
		inner join prc_pp_ht d on c.pp_hd_id=d.id
		LEFT join inv_item b on a.item_id=b.id 
		LEFT join gbm_uom f on b.uom_id=f.id 
		WHERE  a.so_hd_id = " . $id . "";
		$dataDetail = $this->db->query($queryDetail)->result_array();

		foreach ($dataDetail as $key => $value) {
			$stok = $this->InvItemModel->cek_stok_lokasi_get($value['lokasi_pp_id'], $value['item_id'], $dataHeader['tanggal']);
			$dataDetail[$key]['stok'] = $stok;

			$queryLastPO = "select a.no_ttb,a.tanggal,c.nama_customer ,b.harga from sls_ttb_ht a 
			inner join sls_so_dt b on a.id=b.so_hd_id 
			inner join gbm_customer c on a.customer_id=c.id
			where b.item_id='" . $value['item_id'] . "'
			and a.tanggal <'" . ($dataHeader['tanggal']) . "'
			order by a.tanggal desc limit 1 ";
			$last_po = $this->db->query($queryLastPO)->row_array();
			if ($last_po) {
				$dataDetail[$key]['last_no_ttb'] = $last_po['no_ttb'];
				$dataDetail[$key]['last_harga_po'] = $last_po['harga'];
				$dataDetail[$key]['last_tanggal_po'] = $last_po['tanggal'];
				$dataDetail[$key]['last_customer'] = $last_po['nama_customer'];
			} else {
				$dataDetail[$key]['last_no_ttb'] = '';
				$dataDetail[$key]['last_harga_po'] = 0;
				$dataDetail[$key]['last_tanggal_po'] = '';
				$dataDetail[$key]['last_customer'] = '';
			}
		}

		$queryUser = "SELECT a.*, b.nama as peminta FROM fwk_users a LEFT JOIN karyawan b ON a.employee_id=b.id WHERE a.id=" . $dataHeader['dibuat_oleh'];
		$dataUser = $this->db->query($queryUser)->row_array();

		// var_dump($dataUser); die;


		// $perminta = $this->InvPermintaanBarangModel->print_slip($id);
		$data['header'] = 	$dataHeader;
		$data['detail'] = 	$dataDetail;
		$data['user'] = $dataUser;


		$data['database'] = $this->db;

		$html = $this->load->view('SlsTTB_laporan_cek_harga', $data, true);

		$filename = 'report_prcpo_' . time();
		$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		// echo $html;
	}
	function print_slip_get($segment_3 = '')
	// 490
	{
		$this->load->helper("terbilangv2");
		$id = (int)$segment_3;
		$data = [];

		$queryHeader = "SELECT a.*,
		f.nama_customer,
		a.alamat_pengiriman as alamat_customer,
		a.telp_pengiriman as no_telepon_customer,
		a.contact_pengiriman as contact_person_customer,
		g.jenis as jenis_bayar,
		g.ket as ket_bayar
		FROM sls_ttb_ht a 
		INNER JOIN gbm_organisasi e ON a.lokasi_id=e.id
		INNER JOIN gbm_customer f ON a.customer_id=f.id
		INNER JOIN prc_syarat_bayar g ON a.syarat_bayar_id=g.id
		WHERE a.id=" . $id . "";
		$dataHeader = $this->db->query($queryHeader)->row_array();

		$queryDetail = "SELECT a.*,
		b.kode as kode_barang,
		b.nama as nama_barang,
		f.nama as uom
		FROM sls_so_dt a 
		LEFT join inv_item b on a.item_id=b.id 
		LEFT join gbm_uom f on b.uom_id=f.id 
		WHERE  a.so_hd_id = " . $id . "";


		$dataDetail = $this->db->query($queryDetail)->result_array();


		$data['header'] = 	$dataHeader;
		$data['detail'] = 	$dataDetail;



		$data['database'] = $this->db;

		// echo (base64_encode(file_get_contents(('./logo_perusahaan.png'))));
		//exit();
		$html = $this->load->view('Sls_slip_so_v3', $data, true);
		$dompdf = new DOMPDF;
		$dompdf->loadHtml($html);
		$dompdf->setPaper('A4', 'portrait');
		$dompdf->render();
		$filename = 'report_' . time();
		$x          = 545;
		$y          = 30;
		$text       = "{PAGE_NUM} / {PAGE_COUNT}";
		$font       = null; // $dompdf->getFontMetrics()->get_font('Helvetica', 'normal');
		$size       = 8;
		$color      = array(0, 0, 0);
		$word_space = 0.0;
		$char_space = 0.0;
		$angle      = 0.0;

		$dompdf->getCanvas()->page_text(
			$x,
			$y,
			$text,
			$font,
			$size,
			$color,
			$word_space,
			$char_space,
			$angle
		);
		$dompdf->stream($filename . ".pdf", array("Attachment" => 0));
		// $html;exit();
		// $filename = 'report_prcpo_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'portrait');
	}
	function print_slip_ttd_get($segment_3 = '')
	// 490
	{
		$this->load->helper("terbilangv2");
		$id = (int)$segment_3;
		$data = [];

		$queryHeader = "SELECT a.*,
		f.nama_customer as nama_customer,
		f.alamat as alamat_customer,
		f.no_telpon as no_telepon_customer,
		f.nama_bank as nama_bank,
		f.no_rekening as no_rekening,
		f.atas_nama as atas_nama,
		f.contact_person as contact_person_customer,
		f.no_hp as no_hp_customer,

		g.jenis as jenis_bayar,
		g.ket as ket_bayar,

		h.nama as nama_franco,
		h.alamat as alamat_franco,
		h.contact as contact_franco,
		h.telp as telp_franco,

		i.kode as mata_uang_kode,
		i.simbol as mata_uang_simbol,
		i.nama as mata_uang_nama,
		j.user_full_name AS dibuat,
		k.no_quotation AS qoutation,

		z.nama as user_approve1,
		x.nama as user_approve_jabatan1,
		zz.nama as user_approve2,
		xx.nama as user_approve_jabatan2,
		zzz.nama as user_approve3,
		xxx.nama as user_approve_jabatan3,
		zzzz.nama as user_approve4,
		xxxx.nama as user_approve_jabatan4,
		zzzzz.nama as user_approve5,
		xxxxx.nama as user_approve_jabatan5,
		d.no_pp AS no_pp,
		e.nama as lokasi
		FROM sls_ttb_ht a 
		INNER JOIN sls_so_dt b ON a.id=b.so_hd_id
		INNER JOIN prc_pp_dt c ON b.pp_dt_id=c.id
		INNER JOIN prc_pp_ht d ON c.pp_hd_id=d.id

		INNER JOIN gbm_organisasi e ON a.lokasi_id=e.id
		INNER JOIN gbm_customer f ON a.customer_id=f.id
		INNER JOIN prc_syarat_bayar g ON a.syarat_bayar_id=g.id
		INNER JOIN prc_franco h ON a.franco_id=h.id
		LEFT JOIN acc_mata_uang i ON a.mata_uang_id=i.id
		LEFT JOIN  fwk_users j ON a.dibuat_oleh=j.id
		LEFT JOIN prc_quotation k ON a.quotation_id

		LEFT JOIN karyawan z ON a.user_approve1=z.id
		LEFT JOIN payroll_jabatan x ON z.jabatan_id=x.id
		LEFT JOIN karyawan zz ON a.user_approve2=zz.id
		LEFT JOIN payroll_jabatan xx ON zz.jabatan_id=xx.id
		LEFT JOIN karyawan zzz ON a.user_approve3=zzz.id
		LEFT JOIN payroll_jabatan xxx ON zzz.jabatan_id=xxx.id
		LEFT JOIN karyawan zzzz ON a.user_approve4=zzzz.id
		LEFT JOIN payroll_jabatan xxxx ON zzzz.jabatan_id=xxxx.id
		LEFT JOIN karyawan zzzzz ON a.user_approve5=zzzzz.id
		LEFT JOIN payroll_jabatan xxxxx ON zzzzz.jabatan_id=xxxxx.id
		
		
		WHERE a.id=" . $id . "";
		$dataHeader = $this->db->query($queryHeader)->row_array();

		$queryDetail = "SELECT a.*,
		b.kode as kode_barang,
		b.nama as nama_barang,
		b.no_plat AS no_plat,
		f.nama as uom,
		d.lokasi_id as lokasi_pp_id
		FROM sls_so_dt a 
		INNER JOIN prc_pp_dt c on a.pp_dt_id=c.id
		inner join prc_pp_ht d on c.pp_hd_id=d.id
		LEFT join inv_item b on a.item_id=b.id 
		LEFT join gbm_uom f on b.uom_id=f.id 
		WHERE  a.so_hd_id = " . $id . "";




		$dataDetail = $this->db->query($queryDetail)->result_array();

		$queryUser = "SELECT a.*, b.nama as peminta FROM fwk_users a LEFT JOIN karyawan b ON a.employee_id=b.id WHERE a.id=" . $dataHeader['dibuat_oleh'];
		$dataUser = $this->db->query($queryUser)->row_array();

		// var_dump($dataUser); die;


		// $perminta = $this->InvPermintaanBarangModel->print_slip($id);
		$data['header'] = 	$dataHeader;
		$data['detail'] = 	$dataDetail;
		$data['user'] = $dataUser;
		//echo ((curl_get_content(('./logo_perusahaan.png'))));exit();


		$data['database'] = $this->db;

		// echo (base64_encode(file_get_contents(('./logo_perusahaan.png'))));
		//exit();
		$html = $this->load->view('Prc_slip_so_v3_kbv_ttd', $data, true);
		// $html;exit();
		// $filename = 'report_prcpo_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'portrait');
		$dompdf = new DOMPDF;
		$dompdf->loadHtml($html);
		$dompdf->setPaper('A4', 'portrait');
		$dompdf->render();
		$filename = 'report_' . time();
		$x          = 545;
		$y          = 30;
		$text       = "{PAGE_NUM} / {PAGE_COUNT}";
		$font       = null; // $dompdf->getFontMetrics()->get_font('Helvetica', 'normal');
		$size       = 8;
		$color      = array(0, 0, 0);
		$word_space = 0.0;
		$char_space = 0.0;
		$angle      = 0.0;

		$dompdf->getCanvas()->page_text(
			$x,
			$y,
			$text,
			$font,
			$size,
			$color,
			$word_space,
			$char_space,
			$angle
		);
		$dompdf->stream($filename . ".pdf", array("Attachment" => 0));
	}

	function laporan_Detail_So_post()
	{
		/* A.02 Sales ORDER (DETAIL) */
		$format_laporan =  $this->post('format_laporan', true);

		// $id = (int)$segment_3;
		$data = [];

		$input = [
			// 'lokasi_id' => 252,
			'periode' => '2022-08',
			// 'tgl_mulai' => '2022-09-01',
			// 'tgl_akhir' => '2022-09-01',
			'format_laporan' => 'view',
		];

		// var_dump($this->post());
		// exit();
		// $lokasi_id = $this->post('lokasi_id', true);
		$periode =  $this->post('periode', true);
		// $tanggal_awal = $this->post('tgl_mulai', true);
		// $tanggal_akhir = $this->post('tgl_akhir', true);

		// $lokasi_id = $input['lokasi_id'];
		// $periode = $input['periode'];
		// $tanggal_awal = $input['tgl_mulai'];
		// $tanggal_akhir = $input['tgl_akhir'];
		// $format_laporan = $input['format_laporan'];

		$date = new DateTime($periode . '-01');
		$date->modify('last day of this month');
		$last_day_this_month = $date->format('Y-m-d');
		(int)$jumhari = date('d', strtotime($last_day_this_month));
		$tgl_mulai = $periode . '-01';
		$tgl_akhir = $periode . '-' . sprintf("%02d", $jumhari);

		$queryhead = "SELECT 
		c.no_ttb AS no_ttb, 
		c.id as id FROM sls_so_dt a
		INNER JOIN sls_ttb_ht c ON a.so_hd_id=c.id
		INNER JOIN gbm_customer b ON c.customer_id=b.id
		where c.tanggal between  '" . $tgl_mulai . "' and  '" . $tgl_akhir . "'	
		and c.status='RELEASE'
		GROUP BY c.id,c.no_ttb 
		";

		$ressult = array();

		$dataBkm = $this->db->query($queryhead)->result_array();

		foreach ($dataBkm as $key => $hd) {
			$querydetail = "SELECT a.*,
			b.no_ttb as no_ttb,
			b.id AS id_so,
			d.no_pp as no_pp,
			b.tanggal as tanggal,
			e.nama_customer as nama_customer,
			f.kode as kode_item,
			f.nama as nama_item,
			g.nama AS gudang,
			b.id as id,
			b.status
			FROM sls_so_dt a
			INNER JOIN sls_ttb_ht b ON a.so_hd_id=b.id
			LEFT JOIN prc_pp_dt c ON a.pp_dt_id=c.id
			LEFT JOIN prc_pp_ht d ON c.pp_hd_id=d.id
			LEFT JOIN gbm_customer e ON b.customer_id=e.id
			LEFT JOIN inv_item f ON a.item_id=f.id
			LEFT JOIN gbm_organisasi g ON b.lokasi_id=g.id 
			WHERE b.tanggal between  '" . $tgl_mulai . "' and  '" . $tgl_akhir . "'
			AND b.no_ttb='" . $hd['no_ttb'] . "'
			";
			$dataDtl = $this->db->query($querydetail)->result_array();
			// var_dump($dataDtl)	;exit;
			$hd['detail'] = $dataDtl;
			$result[] = $hd;
		}

		$data['so'] = 	$result;

		// $data['filter_lokasi'] = 	$filter_lokasi;
		$data['filter_tgl_awal'] = 	$tgl_mulai;
		$data['filter_tgl_akhir'] = $tgl_akhir;
		$data['format_laporan'] = $format_laporan;

		$html = $this->load->view('Sls_So_Laporan_Detail', $data, true);

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		// echo $html;
		if ($format_laporan == 'xls') {
			echo $html;
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
			$dompdf = new DOMPDF;
			$dompdf->loadHtml($html);
			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			$filename = 'report_' . time();
			$x          = 400;
			$y          = 570;
			$text       = "{PAGE_NUM} of {PAGE_COUNT}";
			$font       = null; // $dompdf->getFontMetrics()->get_font('Helvetica', 'normal');
			$size       = 10;
			$color      = array(0, 0, 0);
			$word_space = 0.0;
			$char_space = 0.0;
			$angle      = 0.0;

			$dompdf->getCanvas()->page_text(
				$x,
				$y,
				$text,
				$font,
				$size,
				$color,
				$word_space,
				$char_space,
				$angle
			);
			$dompdf->stream($filename . ".pdf", array("Attachment" => 0));
		}
	}

	function laporanso_by_vendor_post()
	{

		/* A.09 Sales Order by Vendor */
		$format_laporan =  $this->post('format_laporan', true);

		// $id = (int)$segment_3;
		$data = [];

		$input = [
			'lokasi_id' => 252,
			'periode' => '2022-08',
			'tgl_mulai' => '2023-08-01',
			'tgl_akhir' => '2023-08-02',
			'format_laporan' => 'view',
		];

		// $lokasi_id = $this->post('lokasi_id', true);
		$periode =  $this->post('periode', true);
		// $tanggal_awal = $this->post('tgl_mulai', true);
		// $tanggal_akhir = $this->post('tgl_akhir', true);
		// $status_id = $this->post('status_id', true);

		// $lokasi_id = $input['lokasi_id'];
		// $periode = $input['periode'];
		// $tgl_mulai = $input['tgl_mulai'];
		// $tgl_akhir = $input['tgl_akhir'];
		// $format_laporan = $input['format_laporan'];



		$date = new DateTime($periode . '-01');
		$date->modify('last day of this month');
		$last_day_this_month = $date->format('Y-m-d');
		(int)$jumhari = date('d', strtotime($last_day_this_month));
		$tgl_mulai = $periode . '-01';
		$tgl_akhir = $periode . '-' . sprintf("%02d", $jumhari);

		$queryhead = "SELECT b.nama_customer as nama_customer, c.customer_id FROM sls_so_dt a
		INNER JOIN sls_ttb_ht c ON a.so_hd_id=c.id
		INNER JOIN gbm_customer b ON c.customer_id=b.id
		where c.tanggal between  '" . $tgl_mulai . "' and  '" . $tgl_akhir . "'	
		and c.status  in ('RELEASE')
		GROUP BY b.nama_customer, c.customer_id
		";

		$ressult = array();
		$dataBkm = $this->db->query($queryhead)->result_array();

		foreach ($dataBkm as $key => $hd) {
			$querydetail = "SELECT a.*,
			b.no_ttb as no_ttb,
			d.no_pp as no_pp,
			b.tanggal as tanggal,
			b.tgl_approve1 AS aprrove1,
			b.tgl_approve2 AS aprrove2,
			b.tgl_approve3 AS aprrove3,
			e.nama_customer as nama_customer,
			f.kode as kode_item,
			f.nama as nama_item,
			g.nama AS gudang
			FROM sls_so_dt a
			INNER JOIN sls_ttb_ht b ON a.so_hd_id=b.id
			LEFT JOIN prc_pp_dt c ON a.pp_dt_id=c.id
			LEFT JOIN prc_pp_ht d ON c.pp_hd_id=d.id
			LEFT JOIN gbm_customer e ON b.customer_id=e.id
			LEFT JOIN inv_item f ON a.item_id=f.id
			LEFT JOIN gbm_organisasi g ON b.lokasi_id=g.id 
			WHERE b.customer_id=" . $hd['customer_id'] . "
			AND b.tanggal between  '" . $tgl_mulai . "' and  '" . $tgl_akhir . "'
			and b.status in ('RELEASE')
			";
			$dataDtl = $this->db->query($querydetail)->result_array();
			// var_dump($dataDtl)	
			$hd['detail'] = $dataDtl;
			$result[] = $hd;
		}

		$data['so'] = 	$result;
		// var_dump($result)	;exit();
		// $data['filter_lokasi'] = 	$filter_lokasi;
		$data['filter_tgl_awal'] = 	$tgl_mulai;
		$data['filter_tgl_akhir'] = $tgl_akhir;
		$data['format_laporan'] = $format_laporan;

		$html = $this->load->view('Sls_So_Laporan_by_Vendor', $data, true);

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		// echo $html;

		if ($format_laporan == 'xls') {
			echo $html;
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
			$dompdf = new DOMPDF;
			$dompdf->loadHtml($html);
			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			$filename = 'report_' . time();
			$x          = 400;
			$y          = 570;
			$text       = "{PAGE_NUM} of {PAGE_COUNT}";
			$font       = null; // $dompdf->getFontMetrics()->get_font('Helvetica', 'normal');
			$size       = 10;
			$color      = array(0, 0, 0);
			$word_space = 0.0;
			$char_space = 0.0;
			$angle      = 0.0;

			$dompdf->getCanvas()->page_text(
				$x,
				$y,
				$text,
				$font,
				$size,
				$color,
				$word_space,
				$char_space,
				$angle
			);
			$dompdf->stream($filename . ".pdf", array("Attachment" => 0));
		}
	}

	function laporanso_by_vendor_unpaid_post()
	{
		/* A.04 Sales Order Unpaid */


		$format_laporan =  $this->post('format_laporan', true);

		// $id = (int)$segment_3;
		$data = [];

		$input = [
			'lokasi_id' => 252,
			'periode' => '2022-08',
			'tgl_mulai' => '2023-11-01',
			'tgl_akhir' => '2023-12-31',
			'format_laporan' => 'view',
		];

		// $lokasi_id = $this->post('lokasi_id', true);
		// $periode =  $this->post('periode', true);
		$tgl_mulai = $this->post('tgl_mulai', true);
		$tgl_akhir = $this->post('tgl_akhir', true);
		// $status_id = $this->post('status_id', true);

		// $lokasi_id = $input['lokasi_id'];
		// $periode = $input['periode'];
		// $tgl_mulai = $input['tgl_mulai'];
		// $tgl_akhir = $input['tgl_akhir'];
		// $format_laporan = $input['format_laporan'];

		// $date = new DateTime($periode . '-01');
		// $date->modify('last day of this month');
		// $last_day_this_month = $date->format('Y-m-d');
		// (int)$jumhari = date('d', strtotime($last_day_this_month));
		// $tgl_mulai = $periode . '-01';
		// $tgl_akhir = $periode . '-' . sprintf("%02d", $jumhari);

		$queryhead = "SELECT b.nama_customer as nama_customer, c.customer_id FROM sls_so_dt a
		INNER JOIN sls_ttb_ht c ON a.so_hd_id=c.id
		INNER JOIN gbm_customer b ON c.customer_id=b.id
		where c.tanggal between  '" . $tgl_mulai . "' and  '" . $tgl_akhir . "'	
		and c.status  in ('RELEASE')
		GROUP BY b.nama_customer, c.customer_id
		";

		$ressult = array();
		$dataBkm = $this->db->query($queryhead)->result_array();

		foreach ($dataBkm as $key => $hd) {
			$querydetail = "SELECT a.*,b.nama_customer AS nama_customer 
			FROM sls_ttb_ht a
			LEFT JOIN gbm_customer b ON a.customer_id=b.id
			WHERE a.customer_id=" . $hd['customer_id'] . "
			AND a.tanggal between  '" . $tgl_mulai . "' and  '" . $tgl_akhir . "'
			and a.status  in ('RELEASE')
			";
			$dataDtl = $this->db->query($querydetail)->result_array();
			// var_dump($dataDtl)	
			$hd['detail'] = $dataDtl;
			$result[] = $hd;
		}

		$data['so'] = 	$result;
		// var_dump($result)	;exit();
		// $data['filter_lokasi'] = 	$filter_lokasi;
		$data['filter_tgl_awal'] = 	$tgl_mulai;
		$data['filter_tgl_akhir'] = $tgl_akhir;
		$data['format_laporan'] = $format_laporan;

		$html = $this->load->view('Sls_So_Laporan_by_Vendor_Unpaid', $data, true);

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		// echo $html;

		if ($format_laporan == 'xls') {
			echo $html;
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
			$dompdf = new DOMPDF;
			$dompdf->loadHtml($html);
			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			$filename = 'report_' . time();
			$x          = 400;
			$y          = 570;
			$text       = "{PAGE_NUM} of {PAGE_COUNT}";
			$font       = null; // $dompdf->getFontMetrics()->get_font('Helvetica', 'normal');
			$size       = 10;
			$color      = array(0, 0, 0);
			$word_space = 0.0;
			$char_space = 0.0;
			$angle      = 0.0;

			$dompdf->getCanvas()->page_text(
				$x,
				$y,
				$text,
				$font,
				$size,
				$color,
				$word_space,
				$char_space,
				$angle
			);
			$dompdf->stream($filename . ".pdf", array("Attachment" => 0));
		}
	}

	function laporanso_by_approval_post()
	{

		/*=== A.01 Sales Order per-Period === */

		$format_laporan =  $this->post('format_laporan', true);

		// $id = (int)$segment_3;
		$data = [];

		$input = [
			'lokasi_id' => 252,
			'periode' => '2022-08',
			'tgl_mulai' => '2023-11-01',
			'tgl_akhir' => '2023-12-31',
			'format_laporan' => 'view',
		];

		// $lokasi_id = $this->post('lokasi_id', true);
		$periode =  $this->post('periode', true);
		// $tanggal_awal = $this->post('tgl_mulai', true);
		// $tanggal_akhir = $this->post('tgl_akhir', true);
		// $status_id = $this->post('status_id', true);

		// $lokasi_id = $input['lokasi_id'];
		// $periode = $input['periode'];
		// $tgl_mulai = $input['tgl_mulai'];
		// $tgl_akhir = $input['tgl_akhir'];
		// $format_laporan = $input['format_laporan'];



		$date = new DateTime($periode . '-01');
		$date->modify('last day of this month');
		$last_day_this_month = $date->format('Y-m-d');
		(int)$jumhari = date('d', strtotime($last_day_this_month));
		$tgl_mulai = $periode . '-01';
		$tgl_akhir = $periode . '-' . sprintf("%02d", $jumhari);

		$queryhead = "SELECT DISTINCT c.status AS statuss FROM sls_so_dt a
		INNER JOIN sls_ttb_ht c ON a.so_hd_id=c.id
		where c.tanggal between  '" . $tgl_mulai . "' and  '" . $tgl_akhir . "'	
		and c.status not in ('REJECTED')
		GROUP BY c.status, c.id
		";

		$ressult = array();
		$dataSt = $this->db->query($queryhead)->result_array();

		foreach ($dataSt as $key => $hd) {
			$querydetail = "SELECT a.*, b.nama AS lokasi, c.nama_customer as nama_customer FROM sls_ttb_ht a
			LEFT JOIN gbm_organisasi b ON a.lokasi_id=b.id
			LEFT JOIN gbm_customer c ON a.customer_id=c.id
			WHERE a.status='" . $hd['statuss'] . "'
			AND a.tanggal between  '" . $tgl_mulai . "' and  '" . $tgl_akhir . "'
			and a.status not in ('REJECTED')
			";
			$dataDtl = $this->db->query($querydetail)->result_array();
			// var_dump($dataDtl)	
			$hd['detail'] = $dataDtl;
			$result[] = $hd;
		}

		$data['so'] = 	$result;
		// var_dump($result)	;exit();
		// $data['filter_lokasi'] = 	$filter_lokasi;
		$data['filter_tgl_awal'] = 	$tgl_mulai;
		$data['filter_tgl_akhir'] = $tgl_akhir;
		$data['format_laporan'] = $format_laporan;

		$html = $this->load->view('Sls_So_Laporan_by_Approval', $data, true);

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		// echo $html;

		if ($format_laporan == 'xls') {
			echo $html;
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
			$dompdf = new DOMPDF;
			$dompdf->loadHtml($html);
			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			$filename = 'report_' . time();
			$x          = 400;
			$y          = 570;
			$text       = "{PAGE_NUM} of {PAGE_COUNT}";
			$font       = null; // $dompdf->getFontMetrics()->get_font('Helvetica', 'normal');
			$size       = 10;
			$color      = array(0, 0, 0);
			$word_space = 0.0;
			$char_space = 0.0;
			$angle      = 0.0;

			$dompdf->getCanvas()->page_text(
				$x,
				$y,
				$text,
				$font,
				$size,
				$color,
				$word_space,
				$char_space,
				$angle
			);
			$dompdf->stream($filename . ".pdf", array("Attachment" => 0));
		}
	}
	function laporanso_by_cancelled_post()
	{

		/*=== A.07 Sales Order Cancelled === */

		$format_laporan =  $this->post('format_laporan', true);

		// $id = (int)$segment_3;
		$data = [];

		$input = [
			'lokasi_id' => 252,
			'periode' => '2022-08',
			'tgl_mulai' => '2023-11-01',
			'tgl_akhir' => '2023-12-31',
			'format_laporan' => 'view',
		];

		// $lokasi_id = $this->post('lokasi_id', true);
		$periode =  $this->post('periode', true);
		// $tanggal_awal = $this->post('tgl_mulai', true);
		// $tanggal_akhir = $this->post('tgl_akhir', true);
		// $status_id = $this->post('status_id', true);

		// $lokasi_id = $input['lokasi_id'];
		// $periode = $input['periode'];
		// $tgl_mulai = $input['tgl_mulai'];
		// $tgl_akhir = $input['tgl_akhir'];
		// $format_laporan = $input['format_laporan'];



		$date = new DateTime($periode . '-01');
		$date->modify('last day of this month');
		$last_day_this_month = $date->format('Y-m-d');
		(int)$jumhari = date('d', strtotime($last_day_this_month));
		$tgl_mulai = $periode . '-01';
		$tgl_akhir = $periode . '-' . sprintf("%02d", $jumhari);

		$queryhead = "SELECT DISTINCT c.status AS statuss FROM sls_so_dt a
		INNER JOIN sls_ttb_ht c ON a.so_hd_id=c.id
		where c.tanggal between  '" . $tgl_mulai . "' and  '" . $tgl_akhir . "'	
		and c.status  in ('REJECTED')
		GROUP BY c.status, c.id
		";

		$ressult = array();
		$dataSt = $this->db->query($queryhead)->result_array();

		foreach ($dataSt as $key => $hd) {
			$querydetail = "SELECT a.*, b.nama AS lokasi, c.nama_customer as nama_customer FROM sls_ttb_ht a
			LEFT JOIN gbm_organisasi b ON a.lokasi_id=b.id
			LEFT JOIN gbm_customer c ON a.customer_id=c.id
			WHERE a.status='" . $hd['statuss'] . "'
			AND a.tanggal between  '" . $tgl_mulai . "' and  '" . $tgl_akhir . "'
			and a.status  in ('REJECTED')
			";
			$dataDtl = $this->db->query($querydetail)->result_array();
			// var_dump($dataDtl)	
			$hd['detail'] = $dataDtl;
			$result[] = $hd;
		}

		$data['so'] = 	$result;
		// var_dump($result)	;exit();
		// $data['filter_lokasi'] = 	$filter_lokasi;
		$data['filter_tgl_awal'] = 	$tgl_mulai;
		$data['filter_tgl_akhir'] = $tgl_akhir;
		$data['format_laporan'] = $format_laporan;

		$html = $this->load->view('Sls_So_Laporan_by_Cancelled', $data, true);

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		// echo $html;

		if ($format_laporan == 'xls') {
			echo $html;
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
			$dompdf = new DOMPDF;
			$dompdf->loadHtml($html);
			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			$filename = 'report_' . time();
			$x          = 400;
			$y          = 570;
			$text       = "{PAGE_NUM} of {PAGE_COUNT}";
			$font       = null; // $dompdf->getFontMetrics()->get_font('Helvetica', 'normal');
			$size       = 10;
			$color      = array(0, 0, 0);
			$word_space = 0.0;
			$char_space = 0.0;
			$angle      = 0.0;

			$dompdf->getCanvas()->page_text(
				$x,
				$y,
				$text,
				$font,
				$size,
				$color,
				$word_space,
				$char_space,
				$angle
			);
			$dompdf->stream($filename . ".pdf", array("Attachment" => 0));
		}
	}
	function laporanso_by_header_post()
	{
		/* A.05 Sales Order per-Period (Detail) */
		$format_laporan =  $this->post('format_laporan', true);

		// $id = (int)$segment_3;
		$data = [];

		$input = [
			'lokasi_id' => 252,
			'periode' => '2022-08',
			'tgl_mulai' => '2023-11-01',
			'tgl_akhir' => '2023-11-31',
			'format_laporan' => 'view',
		];

		// $lokasi_id = $this->post('lokasi_id', true);
		$periode =  $this->post('periode', true);
		// $tanggal_awal = $this->post('tgl_mulai', true);
		// $tanggal_akhir = $this->post('tgl_akhir', true);
		// $status_id = $this->post('status_id', true);

		// $lokasi_id = $input['lokasi_id'];
		// $periode = $input['periode'];
		// $tgl_mulai = $input['tgl_mulai'];
		// $tgl_akhir = $input['tgl_akhir'];
		// $format_laporan = $input['format_laporan'];

		$date = new DateTime($periode . '-01');
		$date->modify('last day of this month');
		$last_day_this_month = $date->format('Y-m-d');
		(int)$jumhari = date('d', strtotime($last_day_this_month));
		$tgl_mulai = $periode . '-01';
		$tgl_akhir = $periode . '-' . sprintf("%02d", $jumhari);

		$queryhead = "SELECT c.no_ttb AS no_ttb, c.id AS id  FROM sls_so_dt a
		INNER JOIN sls_ttb_ht c ON a.so_hd_id=c.id
		where c.tanggal between  '" . $tgl_mulai . "' and  '" . $tgl_akhir . "'	
		and c.status='RELEASE'
		GROUP BY c.no_ttb, c.id
		";

		$ressult = array();
		$dataPo = $this->db->query($queryhead)->result_array();

		foreach ($dataPo as $key => $hd) {
			$querydetail = "SELECT a.*,
			b.no_ttb as no_ttb,
			d.no_pp as no_pp,
			b.tanggal as tanggal,
			b.tgl_approve1 AS aprrove1,
			b.tgl_approve2 AS aprrove2,
			b.tgl_approve3 AS aprrove3,
			e.nama_customer as nama_customer,
			f.kode as kode_item,
			f.nama as nama_item,
			g.nama AS gudang,
			b.status
			FROM sls_so_dt a
			INNER JOIN sls_ttb_ht b ON a.so_hd_id=b.id
			LEFT JOIN prc_pp_dt c ON a.pp_dt_id=c.id
			LEFT JOIN prc_pp_ht d ON c.pp_hd_id=d.id
			LEFT JOIN gbm_customer e ON b.customer_id=e.id
			LEFT JOIN inv_item f ON a.item_id=f.id
			LEFT JOIN gbm_organisasi g ON b.lokasi_id=g.id 
			WHERE b.id=" . $hd['id'] . "
			AND b.tanggal between  '" . $tgl_mulai . "' and  '" . $tgl_akhir . "'
			and b.status='RELEASE'
			";
			$dataDtl = $this->db->query($querydetail)->result_array();
			// var_dump($dataDtl)	
			$hd['detail'] = $dataDtl;
			$result[] = $hd;
		}

		$data['so'] = 	$result;
		// var_dump($result)	;exit();
		// $data['filter_lokasi'] = 	$filter_lokasi;
		$data['filter_tgl_awal'] = 	$tgl_mulai;
		$data['filter_tgl_akhir'] = $tgl_akhir;
		$data['format_laporan'] = $format_laporan;

		$html = $this->load->view('Sls_So_Laporan_by_header', $data, true);

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		// echo $html;

		if ($format_laporan == 'xls') {
			echo $html;
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
			$dompdf = new DOMPDF;
			$dompdf->loadHtml($html);
			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			$filename = 'report_' . time();
			$x          = 400;
			$y          = 570;
			$text       = "{PAGE_NUM} of {PAGE_COUNT}";
			$font       = null; // $dompdf->getFontMetrics()->get_font('Helvetica', 'normal');
			$size       = 10;
			$color      = array(0, 0, 0);
			$word_space = 0.0;
			$char_space = 0.0;
			$angle      = 0.0;

			$dompdf->getCanvas()->page_text(
				$x,
				$y,
				$text,
				$font,
				$size,
				$color,
				$word_space,
				$char_space,
				$angle
			);
			$dompdf->stream($filename . ".pdf", array("Attachment" => 0));
		}
	}

	function laporansoBy_vendor_kategori_post()
	{
		/* A.03 Sales Order by Category */
		$format_laporan =  $this->post('format_laporan', true);

		// $id = (int)$segment_3;
		$data = [];

		$input = [
			'lokasi_id' => 252,
			'periode' => '2022-08',
			'tgl_mulai' => '2022-08-01',
			'tgl_akhir' => '2022-08-02',
			'format_laporan' => 'view',
		];

		// $lokasi_id = $this->post('lokasi_id', true);
		$periode =  $this->post('periode', true);
		// $tanggal_awal = $this->post('tgl_mulai', true);
		// $tanggal_akhir = $this->post('tgl_akhir', true);
		// $status_id = $this->post('status_id', true);

		// $lokasi_id = $input['lokasi_id'];
		// $periode = $input['periode'];
		// $tgl_mulai = $input['tgl_mulai'];
		// $tgl_akhir = $input['tgl_akhir'];
		// $format_laporan = $input['format_laporan'];

		$date = new DateTime($periode . '-01');
		$date->modify('last day of this month');
		$last_day_this_month = $date->format('Y-m-d');
		(int)$jumhari = date('d', strtotime($last_day_this_month));
		$tgl_mulai = $periode . '-01';
		$tgl_akhir = $periode . '-' . sprintf("%02d", $jumhari);

		$queryKategori = "SELECT 
		c.inv_kategori_id,
		d.nama AS kategori
		FROM sls_so_dt a
		INNER JOIN sls_ttb_ht b ON a.so_hd_id=b.id
		INNER JOIN inv_item c ON a.item_id=c.id
		INNER JOIN inv_kategori d ON c.inv_kategori_id=d.id
		where b.tanggal between  '" . $tgl_mulai . "' and  '" . $tgl_akhir . "'	
		and b.status  in ('RELEASE')
		GROUP BY c.inv_kategori_id,d.nama
		";
		$dataKat = $this->db->query($queryKategori)->result_array();

		foreach ($dataKat as $key => $kt) {
			$querySup = "SELECT 
		b.nama_customer as nama_customer,
		c.customer_id
		FROM sls_so_dt a
		INNER JOIN sls_ttb_ht c ON a.so_hd_id=c.id
		INNER JOIN gbm_customer b ON c.customer_id=b.id
		INNER JOIN inv_item d ON a.item_id=d.id
		INNER JOIN inv_kategori e ON d.inv_kategori_id=e.id
		where c.tanggal between  '" . $tgl_mulai . "' and  '" . $tgl_akhir . "'
		and d.inv_kategori_id=" . $kt['inv_kategori_id'] . "
		and c.status  in ('RELEASE')
		GROUP BY b.nama_customer,c.customer_id
		";
			$dataSupp = $this->db->query($querySup)->result_array();
			$res_supp = [];
			foreach ($dataSupp as $key => $hd) {
				$querydetail = "SELECT a.*,
				b.no_ttb as no_ttb,
				d.no_pp as no_pp,
				b.tanggal as tanggal,
				b.tgl_approve1 AS aprrove1,
				b.tgl_approve2 AS aprrove2,
				b.tgl_approve3 AS aprrove3,
				e.nama_customer as nama_customer,
				f.kode as kode_item,
				f.nama as nama_item,
				g.nama AS gudang,
				h.nama AS kategori
				FROM sls_so_dt a
				INNER JOIN sls_ttb_ht b ON a.so_hd_id=b.id
				LEFT JOIN prc_pp_dt c ON a.pp_dt_id=c.id
				LEFT JOIN prc_pp_ht d ON c.pp_hd_id=d.id
				LEFT JOIN gbm_customer e ON b.customer_id=e.id
				LEFT JOIN inv_item f ON a.item_id=f.id
				LEFT JOIN gbm_organisasi g ON b.lokasi_id=g.id 
				LEFT JOIN inv_kategori h ON f.inv_kategori_id=h.id
				WHERE b.customer_id=" . $hd['customer_id'] . "
				and f.inv_kategori_id=" . $kt['inv_kategori_id'] . "
				AND b.tanggal between  '" . $tgl_mulai . "' and  '" . $tgl_akhir . "'
				and b.status  in ('RELEASE')
				";
				$dataDtl = $this->db->query($querydetail)->result_array();
				// var_dump($dataDtl)	
				$hd['it'] = $dataDtl;
				$res_supp[] = $hd;
			}
			$kt['supp'] = $res_supp;
			$result[] = $kt;
		}
		$data['so'] = 	$result;
		// print_r($result);exit();
		// $data['filter_lokasi'] = 	$filter_lokasi;
		$data['filter_tgl_awal'] = 	$tgl_mulai;
		$data['filter_tgl_akhir'] = $tgl_akhir;
		$data['format_laporan'] = $format_laporan;

		$html = $this->load->view('Sls_So_Laporan_by_Kat_Vendor', $data, true);

		// echo $html;

		if ($format_laporan == 'xls') {
			echo $html;
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
			$dompdf = new DOMPDF;
			$dompdf->loadHtml($html);
			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			$filename = 'report_' . time();
			$x          = 400;
			$y          = 570;
			$text       = "{PAGE_NUM} of {PAGE_COUNT}";
			$font       = null; // $dompdf->getFontMetrics()->get_font('Helvetica', 'normal');
			$size       = 10;
			$color      = array(0, 0, 0);
			$word_space = 0.0;
			$char_space = 0.0;
			$angle      = 0.0;

			$dompdf->getCanvas()->page_text(
				$x,
				$y,
				$text,
				$font,
				$size,
				$color,
				$word_space,
				$char_space,
				$angle
			);
			$dompdf->stream($filename . ".pdf", array("Attachment" => 0));
		}
	}


	function laporanSummary_get()
	{
		$format_laporan =  $this->post('format_laporan', true);
		$data = [];
		$input = [
			'periode' => '2022-08',
			'tgl_mulai' => '2023-08-01',
			'tgl_akhir' => '2023-08-02',
			'tahun' => '2022',
			'format_laporan' => 'view',
		];
		// $periode =  $this->post('periode', true);

		// $tgl_mulai = $input['tgl_mulai'];
		// $tgl_akhir = $input['tgl_akhir'];
		$tahun = $input['tahun'];
		$format_laporan = $input['format_laporan'];

		$tgl_mulai = $tahun . '-01-01';
		$tgl_akhir = $tahun . '-12-31';

		$queryhead = "SELECT DISTINCT
		c.inv_kategori_id,
		d.nama AS kategori
		FROM sls_so_dt a
		INNER JOIN sls_ttb_ht b ON a.so_hd_id=b.id
		INNER JOIN inv_item c ON a.item_id=c.id
		INNER JOIN inv_kategori d ON c.inv_kategori_id=d.id
		WHERE (c.inv_kategori_id IS NOT NULL AND c.inv_kategori_id <>0)
		and b.tanggal between  '" . $tgl_mulai . "' and  '" . $tgl_akhir . "'	
		and b.status  in ('RELEASE')
		";
		$dataPo = $this->db->query($queryhead)->result_array();

		for ($i = 1; $i < (12 + 1); $i++) {
			$totalPerBulan[] = 0;
		}

		foreach ($dataPo as $key => $hd) {
			$totalrp = 0;
			for ($i = 1; $i < (12 + 1); $i++) {
				$yymm = $tahun  . '-' . sprintf("%02d", $i);

				$querydetail = "SELECT 
			SUM(a.total) AS jml_kat_rp
			FROM sls_so_dt a
			INNER JOIN sls_ttb_ht b ON a.so_hd_id=b.id
			LEFT JOIN prc_pp_dt c ON a.pp_dt_id=c.id
			LEFT JOIN prc_pp_ht d ON c.pp_hd_id=d.id
			LEFT JOIN gbm_customer e ON b.customer_id=e.id
			LEFT JOIN inv_item f ON a.item_id=f.id
			LEFT JOIN gbm_organisasi g ON b.lokasi_id=g.id 
			LEFT JOIN inv_kategori h ON f.inv_kategori_id=h.id
			WHERE DATE_FORMAT(b.tanggal, '%Y-%m')='" . $yymm . "'
			AND f.inv_kategori_id=" . $hd['inv_kategori_id'] . "
			and b.status not in ('REJECTED')
			";
				$dataDtl = $this->db->query($querydetail)->row_array();
			}
			$hd['detail'] = $dataDtl;
			$result[] = $hd;
		}

		$data['so'] = 	$result;
		// var_dump($result)	;exit();
		$data['tahun'] = 	$tahun;
		$data['filter_tgl_awal'] = 	$tgl_mulai;
		$data['filter_tgl_akhir'] = $tgl_akhir;
		$data['format_laporan'] = $format_laporan;

		$html = $this->load->view('Sls_So_Laporan_Summary_Kat', $data, true);

		echo $html;
	}

	function laporan_so_detail_post()
	{
		$jenis_laporan =  $this->post('jenis_laporan', true);
		if ($jenis_laporan == 'rekap') {
			$this->laporan_so_rekap();
		} else {
			$this->laporan_so_detail();
		}
	}
	function laporan_so_detail()
	{
		error_reporting(0);

		$data = [];
		if (isset($this->post()['tgl_mulai'])) {
			$input = $this->post();
			$lokasi_id = $this->post('lokasi_id', true);
			$customer_id = $this->post('customer_id', true);
			$tanggal_awal = $this->post('tgl_mulai', true);
			$tanggal_akhir = $this->post('tgl_akhir', true);
			$format_laporan =  $this->post('format_laporan', true);
		} else {
			$input = [
				'tanggal' => '2021-12-23',
			];
			$lokasi_id = 263;
			$customer_id = 4;
			$tanggal_awal = '2020-01-01';
			$tanggal_akhir = '2022-12-12';
			$format_laporan =  'view';
		}

		$queryPo = "SELECT a.*,
		b.no_ttb as noso,
		b.tanggal as tanggal,
		c.nama as item,
		d.nama as uom,
		c.kode as kode,
		e.nama_customer as sup,
		f.kode as mata_uang,
        h.tanggal as tanggal_pp,
		a.item_id,
		b.id as po_id,
		a.id as so_dt_id,
		b.status,
		-- i.no_transaksi as no_penerimaan,
		-- i.tanggal as tanggal_penerimaan,
		-- i.no_ref,
		h.no_pp
		
		FROM sls_so_dt a
		
		INNER JOIN sls_ttb_ht b on a.so_hd_id=b.id
		INNER JOIN inv_item c on a.item_id=c.id
		INNER JOIN gbm_customer e on b.customer_id=e.id
		LEFT JOIN gbm_uom d on c.uom_id=d.id
		LEFT JOIN acc_mata_uang f on b.mata_uang_id=f.id
        LEFT JOIN prc_pp_dt g on a.pp_dt_id=g.id
        LEFT JOIN prc_pp_ht h on g.pp_hd_id=h.id
		-- LEFT JOIN inv_pengiriman_so_ht i on i.po_id=b.id
		where b.tanggal between  '" . $tanggal_awal . "' and  '" . $tanggal_akhir . "'	
		";
		$filter_supplier = "Semua";
		$filter_lokasi = "Semua";
		if ($lokasi_id) {
			$queryPo = $queryPo . " and b.lokasi_id=" . $lokasi_id . "";
			$res = $this->db->query("select * from gbm_organisasi where id=" . $lokasi_id . "")->row_array();
			$filter_lokasi = $res['nama'];
		}
		if ($customer_id) {
			$queryPo = $queryPo . " and b.customer_id=" . $customer_id . "";
			$res = $this->db->query("select * from gbm_customer where id=" . $customer_id . "")->row_array();
			$filter_supplier = $res['nama_customer'];
		}
		$dataPo = $this->db->query($queryPo)->result_array();
		foreach ($dataPo  as $key => $po) {
			$no_penerimaan = '';
			$tgl_penerimaan = '';
			$no_surat_jalan = '';
			$qty_terima = 0;
			$res_penerimaan = $this->db->query(" select a.*,b.qty from inv_pengiriman_so_ht a inner join inv_pengiriman_so_dt b
			on a.id=b.pengiriman_so_id where b.item_id=" . $po['item_id'] . " and b.so_dt_id=" . $po['so_dt_id'] . " ")->result_array();
			if ($res_penerimaan) {
				foreach ($res_penerimaan as $key2 => $penerimaan) {
					$qty_terima = $qty_terima + $penerimaan['qty'];
					if ($no_penerimaan == '') {
						$no_penerimaan = $penerimaan['no_transaksi'];
					} else {
						$no_penerimaan = $no_penerimaan . ', ' . $penerimaan['no_transaksi'];
					}
					if ($tgl_penerimaan == '') {
						$tgl_penerimaan = $penerimaan['tanggal'];
					} else {
						$tgl_penerimaan = $tgl_penerimaan . ', ' . $penerimaan['tanggal'];
					}
					if ($no_surat_jalan == '') {
						$no_surat_jalan = $penerimaan['no_ref'];
					} else {
						$no_surat_jalan = $no_surat_jalan . ', ' . $penerimaan['no_ref'];
					}
				}
			}
			$dataPo[$key]['no_penerimaan'] = $no_penerimaan;
			$dataPo[$key]['tanggal_penerimaan'] = $tgl_penerimaan;
			$dataPo[$key]['no_ref'] = $no_surat_jalan;
			$dataPo[$key]['qty_terima'] = $qty_terima;
		}

		$data['so'] = 	$dataPo;
		$data['filter_supplier'] = 	$filter_supplier;
		$data['filter_lokasi'] = 	$filter_lokasi;
		$data['filter_tgl_awal'] = 	$tanggal_awal;
		$data['filter_tgl_akhir'] = $tanggal_akhir;
		$data['format_laporan'] = $format_laporan;

		$html = $this->load->view('Sls_So_Laporan', $data, true);

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		// echo $html;
		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
			$dompdf = new DOMPDF;
			$dompdf->loadHtml($html);
			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			$filename = 'report_' . time();
			$x          = 400;
			$y          = 570;
			$text       = "{PAGE_NUM} of {PAGE_COUNT}";
			$font       = null; // $dompdf->getFontMetrics()->get_font('Helvetica', 'normal');
			$size       = 10;
			$color      = array(0, 0, 0);
			$word_space = 0.0;
			$char_space = 0.0;
			$angle      = 0.0;

			$dompdf->getCanvas()->page_text(
				$x,
				$y,
				$text,
				$font,
				$size,
				$color,
				$word_space,
				$char_space,
				$angle
			);
			$dompdf->stream($filename . ".pdf", array("Attachment" => 0));
		}
	}
	function laporan_so_rekap()
	{
		error_reporting(0);

		$data = [];
		if (isset($this->post()['tgl_mulai'])) {
			$input = $this->post();
			$lokasi_id = $this->post('lokasi_id', true);
			$customer_id = $this->post('customer_id', true);
			$tanggal_awal = $this->post('tgl_mulai', true);
			$tanggal_akhir = $this->post('tgl_akhir', true);
			$format_laporan =  $this->post('format_laporan', true);
		} else {
			$input = [
				'tanggal' => '2021-12-23',
			];
			$lokasi_id = 263;
			$customer_id = 4;
			$tanggal_awal = '2020-01-01';
			$tanggal_akhir = '2022-12-12';
			$format_laporan =  'xls';
		}

		$queryPo = "	SELECT a.*,
		b.nama_customer as sup,
		c.kode as mata_uang	,
		d.ket AS syarat_bayar	
		from sls_ttb_ht a 
		INNER JOIN gbm_customer b on a.customer_id=b.id
		inner JOIN acc_mata_uang c on a.mata_uang_id=c.id
		INNER JOIN prc_syarat_bayar d ON a.syarat_bayar_id=d.id
      	where a.tanggal between  '" . $tanggal_awal . "' and  '" . $tanggal_akhir . "'	
		";
		$filter_supplier = "Semua";
		$filter_lokasi = "Semua";
		if ($lokasi_id) {
			$queryPo = $queryPo . " and a.lokasi_id=" . $lokasi_id . "";
			$res = $this->db->query("select * from gbm_organisasi where id=" . $lokasi_id . "")->row_array();
			$filter_lokasi = $res['nama'];
		}
		if ($customer_id) {
			$queryPo = $queryPo . " and a.customer_id=" . $customer_id . "";
			$res = $this->db->query("select * from gbm_customer where id=" . $customer_id . "")->row_array();
			$filter_supplier = $res['nama_customer'];
		}
		$dataPo = $this->db->query($queryPo)->result_array();
		foreach ($dataPo  as $key => $po) {
			$no_penerimaan = '';
			$tgl_penerimaan = '';
			$no_surat_jalan = '';
			$qty_terima = 0;
			$res_penerimaan = $this->db->query(" 	SELECT a.* FROM inv_pengiriman_so_ht a INNER JOIN sls_ttb_ht b ON a.po_id=b.id
			where a.po_id=" . $po['id'] . " ")->result_array();
			if ($res_penerimaan) {
				foreach ($res_penerimaan as $key2 => $penerimaan) {

					if ($no_penerimaan == '') {
						$no_penerimaan = $penerimaan['no_transaksi'];
					} else {
						$no_penerimaan = $no_penerimaan . ', ' . $penerimaan['no_transaksi'];
					}
					if ($tgl_penerimaan == '') {
						$tgl_penerimaan = $penerimaan['tanggal'];
					} else {
						$tgl_penerimaan = $tgl_penerimaan . ', ' . $penerimaan['tanggal'];
					}
					if ($no_surat_jalan == '') {
						$no_surat_jalan = $penerimaan['no_ref'];
					} else {
						$no_surat_jalan = $no_surat_jalan . ', ' . $penerimaan['no_ref'];
					}
				}
			}
			$dataPo[$key]['no_penerimaan'] = $no_penerimaan;
			$dataPo[$key]['tanggal_penerimaan'] = $tgl_penerimaan;
			$dataPo[$key]['no_ref'] = $no_surat_jalan;
		}

		$data['so'] = 	$dataPo;
		$data['filter_supplier'] = 	$filter_supplier;
		$data['filter_lokasi'] = 	$filter_lokasi;
		$data['filter_tgl_awal'] = 	$tanggal_awal;
		$data['filter_tgl_akhir'] = $tanggal_akhir;
		$data['format_laporan'] = $format_laporan;

		$html = $this->load->view('Sls_So_Laporan_Rekap', $data, true);

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		// echo $html;
		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
			$dompdf = new DOMPDF;
			$dompdf->loadHtml($html);
			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			$filename = 'report_' . time();
			$x          = 400;
			$y          = 570;
			$text       = "{PAGE_NUM} of {PAGE_COUNT}";
			$font       = null; // $dompdf->getFontMetrics()->get_font('Helvetica', 'normal');
			$size       = 10;
			$color      = array(0, 0, 0);
			$word_space = 0.0;
			$char_space = 0.0;
			$angle      = 0.0;

			$dompdf->getCanvas()->page_text(
				$x,
				$y,
				$text,
				$font,
				$size,
				$color,
				$word_space,
				$char_space,
				$angle
			);
			$dompdf->stream($filename . ".pdf", array("Attachment" => 0));
		}
	}

	function laporanSummaryLokasi_post()
	{

		/* A.06 Sales Order Summary */

		$input = [
			'tahun' => '2023',
			'tipe_laporan' => '',
		];

		// $tahun = $input['tahun'];
		// $format_laporan = $input['format_laporan'];

		$format_laporan     = $this->post('format_laporan', true);
		// $format_laporan=	$tipe_laporan;
		$tahun =  $this->post('tahun', true);

		$tgl_mulai = $tahun . '-01-01';
		$tgl_akhir = $tahun . '-12-31';


		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report_v2("A.06 Sales Order Summary");
			$html = $html . '
		
				<style>
				*{
					font-size: 10px ;
				}
				th,
				td {
				  padding: 3px 3px;
				  vertical-align: middle;
				}
				</style>
				';
			$html = $html . ' 
			<div>
			<div class="kop-print">
			<div style=" padding-right: 10px; "><img src="data:image/png;base64,' . get_company()['logo'] . '"></div>
			<div class="kop-nama">' . get_company()['nama'] . '</div>
			<div class="kop-info"> ' . get_company()['alamat'] . '</div>
			<div class="kop-info">Telp : ' . get_company()['telp'] . '</div>
			</div>
			<hr class="kop-print-hr">
		 	</div>';
		} else {
			$html = get_header_report_v2("A.06 Sales Order Summary");
			$html = $html . ' 
			<div>
			<div class="kop-print">
			<div style=" padding-right: 200px; "><img src="data:image/png;base64,' . get_company()['logo'] . '"></div>
			<div class="kop-nama">' . get_company()['nama'] . '</div>
			<div class="kop-info"> ' . get_company()['alamat'] . '</div>
			<div class="kop-info">Telp : ' . get_company()['telp'] . '</div>
		  </div>
			<hr class="kop-print-hr">
		  </div>';
		}


		$html = $html . '
		<h3 class="title">A.06 Sales Order Summary</h3>
  		<table class="no_border" style="width:30%">
			
			<tr>	
					<td>Periode Tahun</td>
					<td>:</td>
					<td>' . tgl_indo($tgl_mulai) . ' - ' . tgl_indo($tgl_akhir) . ' </td>
			</tr>
			
		</table>
			<br>';

		$html = $html . "

		<table   border='1' width='100%' style='border-collapse: collapse;'>
		<thead>
		<tr>
			<th rowspan=2 >No</th>
			<th rowspan=2 style='text-align: center'>Kategori Item</th>
			<th colspan=12  style='text-align: center'> " . $tahun . "  </th>
			<th rowspan=2  style='text-align: center'>TOTAL(Rp)</th>
		</tr>
		";


		$html = $html . "<tr>";
		for ($i = 1; $i < (12 + 1); $i++) {
			$html = $html . "<th style='text-align: center'>" . convert_month($i) . "</th>";
		}
		$html = $html . "</tr> </thead>";
		$nourut = 0;
		$grandtotal = 0;
		$totalPerBulan = array();

		for ($i = 1; $i < (12 + 1); $i++) {
			$totalPerBulan[] = 0;
		}

		// retrive data Kategori  
		$qry = "SELECT DISTINCT c.nama AS lokasi,
		b.lokasi_pp_id AS lokasi_pp_id
		FROM sls_so_dt a
		INNER JOIN sls_ttb_ht b ON a.so_hd_id=b.id
		INNER JOIN gbm_organisasi c ON b.lokasi_pp_id=c.id
		WHERE (b.lokasi_pp_id IS NOT NULL AND b.lokasi_pp_id <>0) 
		and tanggal>='" . $tgl_mulai . "' 
		and tanggal<='" . $tgl_akhir . "' 
		and b.status  in ('RELEASE')";

		// $qry=$qry." order by nama_customer ;";
		$retrieveLokasipp = $this->db->query($qry)->result_array();

		foreach ($retrieveLokasipp as $key => $s) {
			$html = $html . "<tr>";
			$totalRp = 0;
			$nourut = $nourut + 1;
			$html = $html . "<td style='text-align: center'>" . $nourut . "</td>";
			$html = $html . "<td style='text-align: left'>" . $s['lokasi'] . "</td>";
			for ($i = 1; $i < (12 + 1); $i++) {

				$yymm = $tahun  . '-' . sprintf("%02d", $i);

				$retrieveRpPO = $this->db->query("SELECT 
				SUM(a.total) AS jml_kat_rp
				FROM sls_so_dt a
				INNER JOIN sls_ttb_ht b ON a.so_hd_id=b.id
				INNER JOIN gbm_organisasi c ON b.lokasi_pp_id=c.id
				WHERE DATE_FORMAT(b.tanggal, '%Y-%m')='" . $yymm . "'
				AND b.lokasi_pp_id=" . $s['lokasi_pp_id'] . "
				and b.status  in ('RELEASE')
				")->row_array();

				$jmlRpKt = $retrieveRpPO['jml_kat_rp'] ? $retrieveRpPO['jml_kat_rp'] : 0;

				$totalPerBulan[$i - 1] = ($totalPerBulan[$i - 1] ? $totalPerBulan[$i - 1] : 0) + $jmlRpKt;

				$totalRp = $totalRp + $jmlRpKt;

				$grandtotal = $grandtotal + $jmlRpKt;

				$html = $html . "<td style='text-align: right'>" . $this->format_number_report($jmlRpKt) . " </td>";
			}
			$html = $html . "<td style='text-align: right'>" . $this->format_number_report($totalRp) . " </td>";
			$html = $html . "</tr>";
		}
		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: center'> </td>";
		$html = $html . "<td style='text-align: center'></td>";
		for ($i = 1; $i < (12 + 1); $i++) {

			$html = $html . "<td style='text-align: right'>" . $this->format_number_report($totalPerBulan[$i - 1]) . " </td>";
		}
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($grandtotal) . " </td>";
		$html = $html . "</tr>";
		$html = $html . "</table>";

		if ($format_laporan == 'xls') {
			echo $html;
		} else if ($format_laporan == 'pdf') {
			$filename = 'report_' . time();
			// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
			$dompdf = new DOMPDF;
			$dompdf->loadHtml($html);
			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			$filename = 'report_' . time();
			$x          = 400;
			$y          = 570;
			$text       = "{PAGE_NUM} of {PAGE_COUNT}";
			$font       = null; // $dompdf->getFontMetrics()->get_font('Helvetica', 'normal');
			$size       = 10;
			$color      = array(0, 0, 0);
			$word_space = 0.0;
			$char_space = 0.0;
			$angle      = 0.0;

			$dompdf->getCanvas()->page_text(
				$x,
				$y,
				$text,
				$font,
				$size,
				$color,
				$word_space,
				$char_space,
				$angle
			);
			$dompdf->stream($filename . ".pdf", array("Attachment" => 0));
		} else {
			echo $html;
		}
	}
	function laporanSummaryKategori_post()
	{

		/* A.08 Sales Order Summary */

		$input = [
			'tahun' => '2022',
			'tipe_laporan' => '',
		];

		// $tahun = $input['tahun'];
		// $format_laporan = $input['format_laporan'];

		$format_laporan     = $this->post('format_laporan', true);
		// $format_laporan=	$tipe_laporan;
		$tahun =  $this->post('tahun', true);

		$tgl_mulai = $tahun . '-01-01';
		$tgl_akhir = $tahun . '-12-31';


		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report_v2("A.08 Sales Order Summary");
			$html = $html . '
		
				<style>
				*{
					font-size: 10px ;
				}
				th,
				td {
				  padding: 3px 3px;
				  vertical-align: middle;
				}
				</style>
				
				';
			$html = $html . ' 
			<div>
			<div class="kop-print">
			<div style=" padding-right: 10px; "><img src="data:image/png;base64,' . get_company()['logo'] . '"></div>
			<div class="kop-nama">' . get_company()['nama'] . '</div>
			<div class="kop-info"> ' . get_company()['alamat'] . '</div>
			<div class="kop-info">Telp : ' . get_company()['telp'] . '</div>
			</div>
			<hr class="kop-print-hr">
		 	</div>';
		} else {
			$html = get_header_report_v2("A.08 Sales Order Summary");
			$html = $html . ' 
			<div>
			<div class="kop-print">
			<div style=" padding-right: 200px; "><img src="data:image/png;base64,' . get_company()['logo'] . '"></div>
			<div class="kop-nama">' . get_company()['nama'] . '</div>
			<div class="kop-info"> ' . get_company()['alamat'] . '</div>
			<div class="kop-info">Telp : ' . get_company()['telp'] . '</div>
		  </div>
			<hr class="kop-print-hr">
		  </div>';
		}


		$html = $html . '
	
	</div>
		<h3 class="title">A.08 Sales Order Summary</h3>
  		<table class="no_border" style="width:30%">
			
			<tr>	
					<td>Periode Tahun</td>
					<td>:</td>
					<td>' . tgl_indo($tgl_mulai) . ' - ' . tgl_indo($tgl_akhir) . ' </td>
			</tr>
			
		</table>
			<br>';

		$html = $html . "

		<table   border='1' width='100%' style='border-collapse: collapse;'>
		<thead>
		<tr>
			<th rowspan=2 >No</th>
			<th rowspan=2 style='text-align: center'>Kategori Item</th>
			<th colspan=12  style='text-align: center'> " . $tahun . "  </th>
			<th rowspan=2  style='text-align: center'>TOTAL(Rp)</th>
		</tr>
		";


		$html = $html . "<tr>";
		for ($i = 1; $i < (12 + 1); $i++) {
			$html = $html . "<th style='text-align: center'>" . convert_month($i) . "</th>";
		}
		$html = $html . "</tr> </thead>";
		$nourut = 0;
		$grandtotal = 0;
		$totalPerBulan = array();

		for ($i = 1; $i < (12 + 1); $i++) {
			$totalPerBulan[] = 0;
		}

		// retrive data Kategori  
		$qry = "SELECT DISTINCT
		c.inv_kategori_id,
		d.nama AS kategori
		FROM sls_so_dt a
		INNER JOIN sls_ttb_ht b ON a.so_hd_id=b.id
		INNER JOIN inv_item c ON a.item_id=c.id
		INNER JOIN inv_kategori d ON c.inv_kategori_id=d.id
		WHERE (c.inv_kategori_id IS NOT NULL AND c.inv_kategori_id <>0) 
		and tanggal>='" . $tgl_mulai . "' 
		and tanggal<='" . $tgl_akhir . "'
		and b.status  in ('RELEASE') ";
		// $qry=$qry." order by nama_customer ;";
		$retrieveSupplier = $this->db->query($qry)->result_array();

		foreach ($retrieveSupplier as $key => $s) {
			$html = $html . "<tr>";
			$totalRp = 0;
			$nourut = $nourut + 1;
			$html = $html . "<td style='text-align: center'>" . $nourut . "</td>";
			$html = $html . "<td style='text-align: left'>" . $s['kategori'] . "</td>";
			for ($i = 1; $i < (12 + 1); $i++) {

				$yymm = $tahun  . '-' . sprintf("%02d", $i);

				$retrieveKgSupp = $this->db->query("SELECT 
				SUM(a.total) AS jml_kat_rp
				FROM sls_so_dt a
				INNER JOIN sls_ttb_ht b ON a.so_hd_id=b.id
				LEFT JOIN prc_pp_dt c ON a.pp_dt_id=c.id
				LEFT JOIN prc_pp_ht d ON c.pp_hd_id=d.id
				LEFT JOIN gbm_customer e ON b.customer_id=e.id
				LEFT JOIN inv_item f ON a.item_id=f.id
				LEFT JOIN gbm_organisasi g ON b.lokasi_id=g.id 
				LEFT JOIN inv_kategori h ON f.inv_kategori_id=h.id
				WHERE DATE_FORMAT(b.tanggal, '%Y-%m')='" . $yymm . "'
				AND f.inv_kategori_id=" . $s['inv_kategori_id'] . "
				and b.status  in ('RELEASE')
				")->row_array();

				$jmlRpKt = $retrieveKgSupp['jml_kat_rp'] ? $retrieveKgSupp['jml_kat_rp'] : 0;

				$totalPerBulan[$i - 1] = ($totalPerBulan[$i - 1] ? $totalPerBulan[$i - 1] : 0) + $jmlRpKt;

				$totalRp = $totalRp + $jmlRpKt;

				$grandtotal = $grandtotal + $jmlRpKt;

				$html = $html . "<td style='text-align: right'>" . $this->format_number_report($jmlRpKt) . " </td>";
			}
			$html = $html . "<td style='text-align: right'>" . $this->format_number_report($totalRp) . " </td>";
			$html = $html . "</tr>";
		}
		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: center'> </td>";
		$html = $html . "<td style='text-align: center'></td>";
		for ($i = 1; $i < (12 + 1); $i++) {

			$html = $html . "<td style='text-align: right'>" . $this->format_number_report($totalPerBulan[$i - 1]) . " </td>";
		}
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($grandtotal) . " </td>";
		$html = $html . "</tr>";
		$html = $html . "</table>";

		if ($format_laporan == 'xls') {
			echo $html;
		} else if ($format_laporan == 'pdf') {
			$filename = 'report_' . time();
			// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
			$dompdf = new DOMPDF;
			$dompdf->loadHtml($html);
			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			$filename = 'report_' . time();
			$x          = 400;
			$y          = 570;
			$text       = "{PAGE_NUM} of {PAGE_COUNT}";
			$font       = null; // $dompdf->getFontMetrics()->get_font('Helvetica', 'normal');
			$size       = 10;
			$color      = array(0, 0, 0);
			$word_space = 0.0;
			$char_space = 0.0;
			$angle      = 0.0;

			$dompdf->getCanvas()->page_text(
				$x,
				$y,
				$text,
				$font,
				$size,
				$color,
				$word_space,
				$char_space,
				$angle
			);
			$dompdf->stream($filename . ".pdf", array("Attachment" => 0));
		} else {
			echo $html;
		}
	}
	function format_number_report($angka)
	{
		$format_laporan     = $this->post('format_laporan', true);
		$tipe_laporan = $this->post('tipe_laporan', true);
		if ($tipe_laporan) {
			$format_laporan = $tipe_laporan;
		}
		// if ($fmt_laporan ) {
		// 	$format_laporan     = $this->post('format_laporan', true);
		// }else{
		// 	return $this->format_number_report($angka);
		// }
		if ($format_laporan == 'xls' || $format_laporan == 'excel') {
			return $angka;
		} else {
			if ($angka == 0) {
				return 0;
			}
			return number_format($angka, 2);
		}
	}

	function laporanTopTen_post()
	{
		error_reporting(0);

		$data = [];
		if (isset($this->post()['bulan'])) {
			$mm = $this->post('bulan', true);
			$yyyy = $this->post('tahun', true);

			$format_laporan =  $this->post('format_laporan', true);
		} else {
			$mm = 12;
			$yyyy = 2023;
			$format_laporan =  'view';
		}



		$resultPO = [];
		$yyyymm = $yyyy .  sprintf("%02s", $mm);
		$bln = $mm;
		$thn = $yyyy;
		$bulan_tahun = [];
		for ($i = 6; $i > 0; $i--) {
			$yyyymm = $thn . sprintf("%02s", $bln);
			$bulan_tahun[$yyyymm] = convert_year_month($yyyymm);
			$queryPo = "	SELECT 
				a.customer_id,
				b.nama_customer as nama_customer,
				sum(grand_total)as amount
				from sls_ttb_ht a 
				INNER JOIN gbm_customer b on a.customer_id=b.id
				INNER JOIN acc_mata_uang c on a.mata_uang_id=c.id
				INNER JOIN prc_syarat_bayar d ON a.syarat_bayar_id=d.id
				where DATE_FORMAT(a.tanggal, '%Y%m')='" . $yyyymm . "' 
				and a.status  in ('RELEASE')
				group by a.customer_id,b.nama_customer order  by sum(grand_total) DESC
				limit 10
				";
			$resPo = $this->db->query($queryPo)->result_array();
			foreach ($resPo as $key => $po) {
				$resultPO[$yyyymm][] = $po;
			}
			$bln = $bln - 1;
			if ($bln == 0) {
				$bln = 12;
				$thn = $thn - 1;
			}
		}


		$data['so'] = 	$resultPO;
		$data['bulan_tahun'] = array_reverse($bulan_tahun, true);
		$data['format_laporan'] = $format_laporan;
		// echo (json_encode($data));
		// exit;
		//var_dump( array_reverse($bulan_tahun,true));exit();
		$html = $this->load->view('Sls_So_Laporan_top10', $data, true);

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		// echo $html;
		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
			$dompdf = new DOMPDF;
			$dompdf->loadHtml($html);
			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			$filename = 'report_' . time();
			$x          = 400;
			$y          = 570;
			$text       = "{PAGE_NUM} of {PAGE_COUNT}";
			$font       = null; // $dompdf->getFontMetrics()->get_font('Helvetica', 'normal');
			$size       = 10;
			$color      = array(0, 0, 0);
			$word_space = 0.0;
			$char_space = 0.0;
			$angle      = 0.0;

			$dompdf->getCanvas()->page_text(
				$x,
				$y,
				$text,
				$font,
				$size,
				$color,
				$word_space,
				$char_space,
				$angle
			);
			$dompdf->stream($filename . ".pdf", array("Attachment" => 0));
		}
	}
	public function sendNotification($token, $title, $message, $payload)
	{
		// $token = $token;
		// $message = "Test notification message";
		$this->load->library('Fcm');
		$payload = array("click_action" => "FLUTTER_NOTIFICATION_CLICK");
		$this->fcm->setTitle($title);
		$this->fcm->setMessage($message);

		/**
		 * set to true if the notificaton is used to invoke a function
		 * in the background
		 */
		$this->fcm->setIsBackground(false);

		/**
		 * payload is userd to send additional data in the notification
		 * This is purticularly useful for invoking functions in background
		 * -----------------------------------------------------------------
		 * set payload as null if no custom data is passing in the notification
		 */
		//$payload = array('notification' => '');
		$this->fcm->setPayload($payload);

		/**
		 * Send images in the notification
		 */
		$this->fcm->setImage(base_url('logo_antech.png'));

		/**
		 * Get the compiled notification data as an array
		 */
		$json = $this->fcm->getPush();

		$p = $this->fcm->send($token, $json);

		// print_r($p);
	}
	function get_path_file($file = '')
	{
		return '/plantation/userfiles/files/' . $file;
		//return	$_SERVER['SERVER_NAME'] . "/" . 'hcis_folder' . "/userfiles/files/" . $file;
	}
	function test_file_get_content_post()
	{
		ini_set('display_errors', 1);
		//ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);
		$curl = curl_init('http://localhost/plantationlive-api/logo_perusahaan.png');
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);


		$html = curl_exec($curl);
		//html var will contain the html code 

		if (curl_error($curl)) {
			die(curl_error($curl));
		}
		// Check for HTTP Codes (if you want)
		$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		curl_close($curl);
		var_dump($html);
		return;

		ini_set('max_execution_time', 300);
		$d = file_get_contents(('./logo_perusahaan.png'));
		var_dump($d);
	}
}
