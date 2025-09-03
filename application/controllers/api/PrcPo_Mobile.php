<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
require 'vendor/autoload.php';

// use Dompdf\Adapter\CPDF;
// use Dompdf\Dompdf;
// use Dompdf\Exception;
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

class PrcPo_Mobile extends REST_Controller
{
	public $user_id;
	public $theCredential;

	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('PrcPoModel');
		$this->load->model('InvItemModel');
		$this->load->model('M_DatatablesModel');
		$this->load->library('pdfgenerator');
		$this->load->library('email');
		// if (!$this->auth()) {
		// 	$this->set_response(array("status" => "OK", "data" => []), REST_Controller::HTTP_OK);
		// 	return;
		// }
		// $this->user_id = 150;

		// $this->auth();
		// $this->theCredential = $this->user_data;
		// $this->user_id = $this->user_data->id;
	}

	public function list_post()
	{
		$post = $this->post();

		$query  = "SELECT a.*, b.nama AS lokasi,c.nama_supplier as nama_supplier,d.no_quotation,d.no_referensi from prc_po_ht a 
		left join gbm_organisasi b on a.lokasi_id=b.id
		left join gbm_supplier c on a.supplier_id=c.id
		left join prc_quotation d on a.quotation_id=d.id";
		$search = array('no_po', 'a.tanggal', 'a.catatan', 'c.nama_supplier', 'd.no_quotation', 'd.no_referensi');
		$where  = null;

		$isWhere = null;
		// $isWhere = 'artikel.deleted_at IS NULL';

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);

		if (count($data['data']) > 0) {
			for ($i = 0; $i < (count($data['data'])); $i++) {
				$po = $data['data'][$i];
				$queryPP = "SELECT DISTINCT  c.id AS pp_id, c.no_pp FROM prc_po_dt a INNER JOIN prc_pp_dt b 
				ON a.pp_dt_id=b.id INNER JOIN prc_pp_ht c ON b.pp_hd_id=c.id
				where a.po_hd_id=" . $po['id'] . "";
				$pp = $this->db->query($queryPP)->result_array();
				$data['data'][$i]['pp_detail'] = $pp;
			}
		}

		// var_dump($data['data']);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	public function list_revisi_post()
	{
		$post = $this->post();

		$query  = "SELECT a.*, b.nama AS lokasi,c.nama_supplier as nama_supplier,d.no_quotation,d.no_referensi from prc_po_ht a 
		left join gbm_organisasi b on a.lokasi_id=b.id
		left join gbm_supplier c on a.supplier_id=c.id
		left join prc_quotation d on a.quotation_id=d.id";
		$search = array('no_po', 'a.tanggal', 'a.catatan', 'c.nama_supplier', 'd.no_quotation', 'd.no_referensi');
		$where  = null;

		$isWhere = null;
		// $isWhere = " a.status ='RELEASE' ";
		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);

		if (count($data['data']) > 0) {
			for ($i = 0; $i < (count($data['data'])); $i++) {
				$po = $data['data'][$i];
				$queryPP = "SELECT DISTINCT  c.id AS pp_id, c.no_pp FROM prc_po_dt a INNER JOIN prc_pp_dt b 
				ON a.pp_dt_id=b.id INNER JOIN prc_pp_ht c ON b.pp_hd_id=c.id
				where a.po_hd_id=" . $po['id'] . "";
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

		
		$query  = "SELECT a.*, b.nama AS lokasi,d.nama_supplier as nama_supplier,e.no_quotation,e.no_referensi from prc_po_ht a left join gbm_organisasi b on a.lokasi_id=b.id 
				inner join fwk_users c on a.last_approve_user=c.employee_id
				inner join gbm_supplier d on a.supplier_id=d.id
				left join prc_quotation e on a.quotation_id=e.id
		where a.proses_approval=1 and a.status not in ('REJECTED','RELEASE') ";

		$data =$this->db->query($query)->result_array();
		if (count($data['data']) > 0) {
			for ($i = 0; $i < (count($data['data'])); $i++) {
				$po = $data['data'][$i];
				$queryPP = "SELECT DISTINCT  c.id AS pp_id, c.no_pp FROM prc_po_dt a INNER JOIN prc_pp_dt b 
				ON a.pp_dt_id=b.id INNER JOIN prc_pp_ht c ON b.pp_hd_id=c.id
				where a.po_hd_id=" . $po['id'] . "";
				$pp = $this->db->query($queryPP)->result_array();
				$data['data'][$i]['pp_detail'] = $pp;
			}
		}
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	public function countByUserApprove_post()
	{

		if ($this->user_id) {
			$query  = "SELECT COUNT(*)as jumlah from prc_po_ht a left join gbm_organisasi b on a.lokasi_id=b.id 
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
		$retrieve = $this->PrcPoModel->retrieve($id);
		$retrieve['detail'] = $this->PrcPoModel->retrieve_detail($id);

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAll_get()
	{

		$retrieve = $this->PrcPoModel->retrieve_all_kategori();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function getAllBySupplier_get($supp_id)
	{

		$retrieve = $this->PrcPoModel->retrieve_all_by_supplier($supp_id);

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => []), REST_Controller::HTTP_OK);
		}
	}
	function getAllPOReleaseBySupplier_get($supp_id)
	{

		$retrieve = $this->PrcPoModel->retrieve_all_po_release_by_supplier($supp_id);

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
		$input['no_po'] = $this->autonumber->purchase_order($input['lokasi_pp_id']['id'], $input['tanggal'], $input['supplier_id']['id']);

		$res =  $this->PrcPoModel->create($input);
		// $this->set_response(array("status" => "OK", "data" => $input['no_po']), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'prc_po', 'action' => 'new', 'entity_id' => $res);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $input['no_po']), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function index_put($segment_3 = '')
	{
		$input = $this->put();
		$input['diubah_oleh'] = $this->user_id;
		$id = (int)$segment_3;
		$kategori = $this->PrcPoModel->retrieve($id);
		if (empty($kategori)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$res =   $this->PrcPoModel->update($kategori['id'], $input);
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'prc_po', 'action' => 'edit', 'entity_id' => $id);
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
		$po = $this->PrcPoModel->retrieve($id);
		if (empty($po)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
		$res =   $this->PrcPoModel->revisi($po['id'], $input);
		if (!empty($res)) {
			/* Kembalikan ke status terkahir */
			if ($po['status_approve5']) { // ($status == 'PO5') {
				$ht['status'] = 'PO4';
				$ht['status_approve5'] = NULL;
				$ht['tgl_approve5'] = NULL;
				$ht['note_approve5'] = NULL;
				$ht['last_approve_position'] = 'PO5';
				$ht['last_approve_user'] = $po['user_approve5'];
			} else if ($po['status_approve4']) { //($status == 'PO4') {
				$ht['status'] = 'PO3';
				$ht['status_approve4'] = NULL;
				$ht['tgl_approve4'] = NULL;
				$ht['note_approve4'] = NULL;
				$ht['last_approve_position'] = 'PO4';
				$ht['last_approve_user'] = $po['user_approve4'];
			} else if ($po['status_approve3']) { //($status == 'PO3') {
				$ht['status'] = 'PO2';
				$ht['status_approve3'] = NULL;
				$ht['tgl_approve3'] = NULL;
				$ht['note_approve3'] = NULL;
				$ht['last_approve_position'] = 'PO3';
				$ht['last_approve_user'] = $po['user_approve3'];
			} else if ($po['status_approve2']) { //($status == 'PO2') {
				$ht['status'] = 'PO1';
				$ht['status_approve2'] = NULL;
				$ht['tgl_approve2'] = NULL;
				$ht['note_approve2'] = NULL;
				$ht['last_approve_position'] = 'PO2';
				$ht['last_approve_user'] = $po['user_approve2'];
			} else if ($po['status_approve1']) { //($status == 'PO1') {
				$ht['status'] = '';
				$ht['status_approve1'] = NULL;
				$ht['tgl_approve1'] = NULL;
				$ht['note_approve1'] = NULL;
				$ht['last_approve_position'] = 'PO1';
				$ht['last_approve_user'] = $po['user_approve1'];
			}
			$karyawan_id = $ht['last_approve_user'];
			$ht['proses_approval'] = 1;
			$this->db->where('id', $id);
			$this->db->update('prc_po_ht', $ht);

			$karyawan = $this->db->query("select email,nama from karyawan where id=" . $karyawan_id)->row_array();
			if ($karyawan['email'] || $karyawan['email'] != '') {
				$email_subject = 'Info Notifikasi Approval Revisi PO';
				$email_body    = 'Hallo, Bpk/Ibu. ' . $karyawan['nama'] . '! <br>
					<br>
					No PO : ' .	$po['no_po'] . ' direvisi dan diajukan ulang kepada Anda.<br> 
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
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'prc_po', 'action' => 'edit', 'entity_id' => $id);
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
		$this->db->query("UPDATE prc_po_ht  SET `status`='" . $input['status'] . "', `diubah_tanggal`='" . $input['diubah_tanggal'] . "', `diubah_oleh`=" . $input['diubah_oleh'] . " WHERE id=" . $id);

		// $this->set_response(array("status" => "OK", "data" => true), REST_Controller::HTTP_OK);
	}

	function index_delete($segment_3 = '')
	{

		$id = (int)$segment_3;
		$kategori = $this->PrcPoModel->retrieve($id);
		if (empty($kategori)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$res =  $this->PrcPoModel->delete($kategori['id']);
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id' => $id)), 'entity' => 'prc_po', 'action' => 'delete', 'entity_id' => $id);
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
		$po = $this->PrcPoModel->retrieve($id);
		if (empty($po)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}

		$retrieve =   $this->PrcPoModel->approval($po['id'], $input);
		if ($retrieve) {
			if ($input['karyawan_id']['id']) {
				$karyawan = $this->db->query("select email,nama from karyawan where id=" . $input['karyawan_id']['id'])->row_array();
				if ($karyawan['email'] || $karyawan['email'] != '') {
					$email_subject = 'Info Notifikasi Approval PO';
					$email_body    = 'Hallo, Bpk/Ibu. ' . $karyawan['nama'] . '! <br>
					<br>
					No PO : ' .	$po['no_po'] . ' diajukan kepada Anda.<br> 
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
			} else {
			}

			$po = $this->PrcPoModel->retrieve($id);
			if ($po['status'] == 'RELEASE') {
				$query_email = "SELECT a.new_, a.user_id,b.user_name,d.nama,d.email  FROM fwk_users_acces a INNER JOIN fwk_users b ON a.user_id=b.id
				INNER JOIN fwk_menu c ON a.menu_id=c.id
				INNER JOIN karyawan d ON  b.employee_id=d.id
				WHERE NAME='prc_po' and a.new_=1 ";
				$res_email = $this->db->query($query_email)->result_array();
				if ($res_email) {
					foreach ($res_email as $key => $value) {
						if ($value['email']) {
							$email_subject = 'Info Notifikasi Approval PO';
							$email_body    = 'Hallo, Bpk/Ibu. ' . $value['nama'] . '! <br>
					<br>
					No PO : ' .	$po['no_po'] . ' Sudah Di-Release.<br> 
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
		$po = $this->PrcPoModel->retrieve($id);
		if (empty($pp)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$retrieve =   $this->PrcPoModel->reject($po['id'], $input);
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
	}

	function posting_post($segment_3 = null)
	{
		$id = (int)$segment_3;
		$data = $this->post();

		$res = $this->PrcPoModel->posting($id, $data);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id' => $id)), 'entity' => 'prc_po', 'action' => 'posting', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAllDetail_get($po_id)
	{
		$retrieve = array();
		$dtl = $this->PrcPoModel->retrieve_po_dtl($po_id);
		$retrieve['dtl'] = $dtl;

		$retrieve['AKUN_PPN'] = $this->db->query("select * from acc_auto_jurnal where kode='PPN_MASUKAN'")->row_array()['acc_akun_id'];
		$retrieve['AKUN_PENERIMAAN_BARANG_PO'] = $this->db->query("select * from acc_auto_jurnal where kode='PENERIMAAN_BARANG_PO'")->row_array()['acc_akun_id'];
		$retrieve['AKUN_DISKON'] = $this->db->query("select * from acc_auto_jurnal where kode='DISKON_PEMBELIAN'")->row_array()['acc_akun_id'];
		$retrieve['AKUN_PPH'] = $this->db->query("select * from acc_auto_jurnal where kode='PPH_PEMBELIAN'")->row_array()['acc_akun_id'];
		$retrieve['AKUN_PPBKB'] = $this->db->query("select * from acc_auto_jurnal where kode='PPBKB_PEMBELIAN'")->row_array()['acc_akun_id'];
		$retrieve['AKUN_BIAYA_KIRIM'] = $this->db->query("select * from acc_auto_jurnal where kode='BIAYA_KIRIM_PO'")->row_array()['acc_akun_id'];
		$retrieve['AKUN_BIAYA_LAIN'] = $this->db->query("select * from acc_auto_jurnal where kode='BIAYA_LAIN_PO'")->row_array()['acc_akun_id'];
		$ret_po_ht = $this->db->query("select * from prc_po_ht where id= " . $po_id)->row_array();
		$retrieve['PO_HT'] =	$ret_po_ht;
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAllDetailBlmTerima_get($po_id)
	{
		$retrieve = $this->PrcPoModel->retrieve_po_dtl_blm_terima($po_id);

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAllDetailSdhTerima_get($po_id)
	{
		$retrieve = array();
		$dtl = $this->PrcPoModel->retrieve_po_dtl_sdh_terima($po_id);
		$retrieve['dtl'] = $dtl;
		$retrieve['AKUN_PPN'] = $this->db->query("select * from acc_auto_jurnal where kode='PPN_MASUKAN'")->row_array()['acc_akun_id'];
		$retrieve['AKUN_PENERIMAAN_BARANG_PO'] = $this->db->query("select * from acc_auto_jurnal where kode='PENERIMAAN_BARANG_PO'")->row_array()['acc_akun_id'];
		$retrieve['AKUN_DISKON'] = $this->db->query("select * from acc_auto_jurnal where kode='DISKON_PEMBELIAN'")->row_array()['acc_akun_id'];
		$retrieve['AKUN_PPH'] = $this->db->query("select * from acc_auto_jurnal where kode='PPH_PEMBELIAN'")->row_array()['acc_akun_id'];
		$retrieve['AKUN_PPBKB'] = $this->db->query("select * from acc_auto_jurnal where kode='PPBKB_PEMBELIAN'")->row_array()['acc_akun_id'];
		$retrieve['AKUN_BIAYA_KIRIM'] = $this->db->query("select * from acc_auto_jurnal where kode='BIAYA_KIRIM_PO'")->row_array()['acc_akun_id'];
		$retrieve['AKUN_BIAYA_LAIN'] = $this->db->query("select * from acc_auto_jurnal where kode='BIAYA_LAIN_PO'")->row_array()['acc_akun_id'];

		$ret_po_ht = $this->db->query("select * from prc_po_ht where id= " . $po_id)->row_array();
		$retrieve['PO_HT'] =	$ret_po_ht;
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}


	function print_slip_po_get($segment_3 = '')
	{

		$id = (int)$segment_3;
		$data = [];

		$queryHeader = "SELECT a.*,
		-- d.nama as gudang,
		f.nama_supplier as nama_supplier,
		f.alamat as alamat_supplier,
		f.no_telpon as no_telepon_supplier,
		f.nama_bank as nama_bank,
		f.no_rekening as no_rekening,
		f.atas_nama as atas_nama,
		f.contact_person as contact_person_supplier,
		f.no_hp as no_hp_supplier,

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
		FROM prc_po_ht a 
		-- INNER JOIN gbm_organisasi d ON a.gudang_id=d.id
		INNER JOIN gbm_organisasi e ON a.lokasi_id=e.id
		INNER JOIN gbm_supplier f ON a.supplier_id=f.id
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
		FROM prc_po_dt a 
		INNER JOIN prc_pp_dt c on a.pp_dt_id=c.id
		inner join prc_pp_ht d on c.pp_hd_id=d.id
		LEFT join inv_item b on a.item_id=b.id 
		LEFT join gbm_uom f on b.uom_id=f.id 
		WHERE  a.po_hd_id = " . $id . "";
		$dataDetail = $this->db->query($queryDetail)->result_array();

		foreach ($dataDetail as $key => $value) {
			$stok = $this->InvItemModel->cek_stok_lokasi_get($value['lokasi_pp_id'], $value['item_id'], $dataHeader['tanggal']);
			$dataDetail[$key]['stok'] = $stok;
		}

		$queryUser = "SELECT a.*, b.nama as peminta FROM fwk_users a LEFT JOIN karyawan b ON a.employee_id=b.id WHERE a.id=" . $dataHeader['dibuat_oleh'];
		$dataUser = $this->db->query($queryUser)->row_array();

		// var_dump($dataUser); die;


		// $perminta = $this->InvPermintaanBarangModel->print_slip($id);
		$data['header'] = 	$dataHeader;
		$data['detail'] = 	$dataDetail;
		$data['user'] = $dataUser;


		$data['database'] = $this->db;

		$html = $this->load->view('PrcPo_laporan', $data, true);

		$filename = 'report_prcpo_' . time();
		$this->pdfgenerator->generate($html, $filename, true, 'A4', 'portrait');
		// echo $html;
	}
	function print_slip_cek_harga_get($segment_3 = '')
	{

		$id = (int)$segment_3;
		$data = [];

		$queryHeader = "SELECT a.*,
		-- d.nama as gudang,
		f.nama_supplier as nama_supplier,
		f.alamat as alamat_supplier,
		f.no_telpon as no_telepon_supplier,
		f.nama_bank as nama_bank,
		f.no_rekening as no_rekening,
		f.atas_nama as atas_nama,
		f.contact_person as contact_person_supplier,
		f.no_hp as no_hp_supplier,

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
		FROM prc_po_ht a 
		-- INNER JOIN gbm_organisasi d ON a.gudang_id=d.id
		INNER JOIN gbm_organisasi e ON a.lokasi_id=e.id
		INNER JOIN gbm_supplier f ON a.supplier_id=f.id
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
		FROM prc_po_dt a 
		INNER JOIN prc_pp_dt c on a.pp_dt_id=c.id
		inner join prc_pp_ht d on c.pp_hd_id=d.id
		LEFT join inv_item b on a.item_id=b.id 
		LEFT join gbm_uom f on b.uom_id=f.id 
		WHERE  a.po_hd_id = " . $id . "";
		$dataDetail = $this->db->query($queryDetail)->result_array();

		foreach ($dataDetail as $key => $value) {
			$stok = $this->InvItemModel->cek_stok_lokasi_get($value['lokasi_pp_id'], $value['item_id'], $dataHeader['tanggal']);
			$dataDetail[$key]['stok'] = $stok;

			$queryLastPO = "select a.no_po,a.tanggal,c.nama_supplier ,b.harga from prc_po_ht a 
			inner join prc_po_dt b on a.id=b.po_hd_id 
			inner join gbm_supplier c on a.supplier_id=c.id
			where b.item_id='" . $value['item_id'] . "'
			and a.tanggal <'" . ($dataHeader['tanggal']) . "'
			order by a.tanggal desc limit 1 ";
			$last_po = $this->db->query($queryLastPO)->row_array();
			if ($last_po) {
				$dataDetail[$key]['last_no_po'] = $last_po['no_po'];
				$dataDetail[$key]['last_harga_po'] = $last_po['harga'];
				$dataDetail[$key]['last_tanggal_po'] = $last_po['tanggal'];
				$dataDetail[$key]['last_supplier'] = $last_po['nama_supplier'];
			} else {
				$dataDetail[$key]['last_no_po'] = '';
				$dataDetail[$key]['last_harga_po'] = 0;
				$dataDetail[$key]['last_tanggal_po'] = '';
				$dataDetail[$key]['last_supplier'] = '';
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

		$html = $this->load->view('PrcPo_laporan_cek_harga', $data, true);

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
		-- d.nama as gudang,
		f.nama_supplier as nama_supplier,
		f.alamat as alamat_supplier,
		f.no_telpon as no_telepon_supplier,
		f.nama_bank as nama_bank,
		f.no_rekening as no_rekening,
		f.atas_nama as atas_nama,
		f.contact_person as contact_person_supplier,
		f.no_hp as no_hp_supplier,

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

		e.nama as lokasi
		FROM prc_po_ht a 
		-- INNER JOIN gbm_organisasi d ON a.gudang_id=d.id
		INNER JOIN gbm_organisasi e ON a.lokasi_id=e.id
		INNER JOIN gbm_supplier f ON a.supplier_id=f.id
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
		f.nama as uom,
		d.lokasi_id as lokasi_pp_id
		FROM prc_po_dt a 
		INNER JOIN prc_pp_dt c on a.pp_dt_id=c.id
		inner join prc_pp_ht d on c.pp_hd_id=d.id
		LEFT join inv_item b on a.item_id=b.id 
		LEFT join gbm_uom f on b.uom_id=f.id 
		WHERE  a.po_hd_id = " . $id . "";
		$dataDetail = $this->db->query($queryDetail)->result_array();

		$queryUser = "SELECT a.*, b.nama as peminta FROM fwk_users a LEFT JOIN karyawan b ON a.employee_id=b.id WHERE a.id=" . $dataHeader['dibuat_oleh'];
		$dataUser = $this->db->query($queryUser)->row_array();

		// var_dump($dataUser); die;


		// $perminta = $this->InvPermintaanBarangModel->print_slip($id);
		$data['header'] = 	$dataHeader;
		$data['detail'] = 	$dataDetail;
		$data['user'] = $dataUser;


		$data['database'] = $this->db;

		$html = $this->load->view('Prc_slip_po_v2', $data, true);

		$filename = 'report_prcpo_' . time();
		$this->pdfgenerator->generate($html, $filename, true, 'A4', 'portrait');
		// echo $html;
	}

	function laporan_po_detail_post()
	{
		$jenis_laporan =  $this->post('jenis_laporan', true);
		if ($jenis_laporan == 'rekap') {
			$this->laporan_po_rekap();
		} else {
			$this->laporan_po_detail();
		}
	}
	function laporan_po_detail()
	{
		error_reporting(0);

		$data = [];
		if (isset($this->post()['tgl_mulai'])) {
			$input = $this->post();
			$lokasi_id = $this->post('lokasi_id', true);
			$supplier_id = $this->post('supplier_id', true);
			$tanggal_awal = $this->post('tgl_mulai', true);
			$tanggal_akhir = $this->post('tgl_akhir', true);
			$format_laporan =  $this->post('format_laporan', true);
		} else {
			$input = [
				'tanggal' => '2021-12-23',
			];
			$lokasi_id = 263;
			$supplier_id = 4;
			$tanggal_awal = '2020-01-01';
			$tanggal_akhir = '2022-12-12';
			$format_laporan =  'xls';
		}

		$queryPo = "SELECT a.*,
		b.no_po as nopo,
		b.tanggal as tanggal,
		c.nama as item,
		d.nama as uom,
		c.kode as kode,
		e.nama_supplier as sup,
		f.kode as mata_uang,
        h.tanggal as tanggal_pp,
		a.item_id,
		b.id as po_id,
		a.id as po_dt_id,
		-- i.no_transaksi as no_penerimaan,
		-- i.tanggal as tanggal_penerimaan,
		-- i.no_surat_jalan_supplier,
		h.no_pp
		
		FROM prc_po_dt a
		
		INNER JOIN prc_po_ht b on a.po_hd_id=b.id
		INNER JOIN inv_item c on a.item_id=c.id
		INNER JOIN gbm_supplier e on b.supplier_id=e.id
		LEFT JOIN gbm_uom d on c.uom_id=d.id
		LEFT JOIN acc_mata_uang f on b.mata_uang_id=f.id
        LEFT JOIN prc_pp_dt g on a.pp_dt_id=g.id
        LEFT JOIN prc_pp_ht h on g.pp_hd_id=h.id
		-- LEFT JOIN inv_penerimaan_po_ht i on i.po_id=b.id
		where b.tanggal between  '" . $tanggal_awal . "' and  '" . $tanggal_akhir . "'	
		";
		$filter_supplier = "Semua";
		$filter_lokasi = "Semua";
		if ($lokasi_id) {
			$queryPo = $queryPo . " and b.lokasi_id=" . $lokasi_id . "";
			$res = $this->db->query("select * from gbm_organisasi where id=" . $lokasi_id . "")->row_array();
			$filter_lokasi = $res['nama'];
		}
		if ($supplier_id) {
			$queryPo = $queryPo . " and b.supplier_id=" . $supplier_id . "";
			$res = $this->db->query("select * from gbm_supplier where id=" . $supplier_id . "")->row_array();
			$filter_supplier = $res['nama_supplier'];
		}
		$dataPo = $this->db->query($queryPo)->result_array();
		foreach ($dataPo  as $key => $po) {
			$no_penerimaan = '';
			$tgl_penerimaan = '';
			$no_surat_jalan = '';
			$qty_terima = 0;
			$res_penerimaan = $this->db->query(" select a.*,b.qty from inv_penerimaan_po_ht a inner join inv_penerimaan_po_dt b
			on a.id=b.penerimaan_po_hd_id where b.item_id=" . $po['item_id'] . " and b.po_dt_id=" . $po['po_dt_id'] . " ")->result_array();
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
						$no_surat_jalan = $penerimaan['no_surat_jalan_supplier'];
					} else {
						$no_surat_jalan = $no_surat_jalan . ', ' . $penerimaan['no_surat_jalan_supplier'];
					}
				}
			}
			$dataPo[$key]['no_penerimaan'] = $no_penerimaan;
			$dataPo[$key]['tanggal_penerimaan'] = $tgl_penerimaan;
			$dataPo[$key]['no_surat_jalan_supplier'] = $no_surat_jalan;
			$dataPo[$key]['qty_terima'] = $qty_terima;
		}

		$data['po'] = 	$dataPo;
		$data['filter_supplier'] = 	$filter_supplier;
		$data['filter_lokasi'] = 	$filter_lokasi;
		$data['filter_tgl_awal'] = 	$tanggal_awal;
		$data['filter_tgl_akhir'] = $tanggal_akhir;

		$html = $this->load->view('Prc_Po_Laporan', $data, true);

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
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}
	function laporan_po_rekap()
	{
		error_reporting(0);

		$data = [];
		if (isset($this->post()['tgl_mulai'])) {
			$input = $this->post();
			$lokasi_id = $this->post('lokasi_id', true);
			$supplier_id = $this->post('supplier_id', true);
			$tanggal_awal = $this->post('tgl_mulai', true);
			$tanggal_akhir = $this->post('tgl_akhir', true);
			$format_laporan =  $this->post('format_laporan', true);
		} else {
			$input = [
				'tanggal' => '2021-12-23',
			];
			$lokasi_id = 263;
			$supplier_id = 4;
			$tanggal_awal = '2020-01-01';
			$tanggal_akhir = '2022-12-12';
			$format_laporan =  'xls';
		}

		$queryPo = "	SELECT a.*,
		b.nama_supplier as sup,
		c.kode as mata_uang	,
		d.ket AS syarat_bayar	
		from prc_po_ht a 
		INNER JOIN gbm_supplier b on a.supplier_id=b.id
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
		if ($supplier_id) {
			$queryPo = $queryPo . " and a.supplier_id=" . $supplier_id . "";
			$res = $this->db->query("select * from gbm_supplier where id=" . $supplier_id . "")->row_array();
			$filter_supplier = $res['nama_supplier'];
		}
		$dataPo = $this->db->query($queryPo)->result_array();
		foreach ($dataPo  as $key => $po) {
			$no_penerimaan = '';
			$tgl_penerimaan = '';
			$no_surat_jalan = '';
			$qty_terima = 0;
			$res_penerimaan = $this->db->query(" 	SELECT a.* FROM inv_penerimaan_po_ht a INNER JOIN prc_po_ht b ON a.po_id=b.id
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
						$no_surat_jalan = $penerimaan['no_surat_jalan_supplier'];
					} else {
						$no_surat_jalan = $no_surat_jalan . ', ' . $penerimaan['no_surat_jalan_supplier'];
					}
				}
			}
			$dataPo[$key]['no_penerimaan'] = $no_penerimaan;
			$dataPo[$key]['tanggal_penerimaan'] = $tgl_penerimaan;
			$dataPo[$key]['no_surat_jalan_supplier'] = $no_surat_jalan;
		}

		$data['po'] = 	$dataPo;
		$data['filter_supplier'] = 	$filter_supplier;
		$data['filter_lokasi'] = 	$filter_lokasi;
		$data['filter_tgl_awal'] = 	$tanggal_awal;
		$data['filter_tgl_akhir'] = $tanggal_akhir;

		$html = $this->load->view('Prc_Po_Laporan_Rekap', $data, true);

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
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}
}
