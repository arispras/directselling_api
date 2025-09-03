<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require 'vendor/autoload.php';
require_once 'vendor/dompdf/dompdf_config.inc.php';

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
use Restserver\Libraries\REST_Controller;


class PrcPp extends BD_Controller //Rest_Controller
{
	public $user_id;
	public $theCredential;
	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('PrcPpModel');
		$this->load->model('InvItemModel');
		$this->load->model('M_DatatablesModel');
		$this->load->library('email');
		$this->load->helper(array('url', 'antech_helper', 'form', 'text',  'security', 'file', 'number', 'date', 'download'));
		$this->load->library('image_lib');
		$this->load->library('upload');
		$this->load->library('pdfgenerator');
		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id = $this->user_data->id;
	}

	public function list_post()
	{
		$post = $this->post();

		$query  = "SELECT a.*, b.nama AS lokasi from prc_pp_ht a left join gbm_organisasi b on a.lokasi_id=b.id";
		$search = array('no_pp', 'tanggal', 'catatan');
		$where  = null;

		$isWhere = null;
		// $isWhere = 'artikel.deleted_at IS NULL';

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	public function listByUserEntry_post()
	{
		$post = $this->post();

		$query  = "SELECT a.*, b.nama AS lokasi from prc_pp_ht a left join gbm_organisasi b on a.lokasi_id=b.id ";
		$search = array('no_pp', 'tanggal', 'catatan');
		$where  = null;

		$isWhere = null;
		$isWhere = " a.dibuat_oleh=" . $this->user_id;

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	public function listByUserApprove_post()
	{
		$post = $this->post();

		$query  = "SELECT a.*, b.nama AS lokasi from prc_pp_ht a left join gbm_organisasi b on a.lokasi_id=b.id 
				inner join fwk_users c on a.last_approve_user=c.employee_id";
		$search = array('no_pp', 'tanggal', 'catatan');
		$where  = null;

		$isWhere = null;
		$isWhere = "a.proses_approval=1 and a.status not in ('REJECTED','READY_PO','CLOSED') and c.id=" . $this->user_id;

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	public function listByUserApproveMobile_post()
	{
		$post = $this->post();

		$query  = "SELECT a.*, b.nama AS lokasi from prc_pp_ht a left join gbm_organisasi b on a.lokasi_id=b.id 
				inner join fwk_users c on a.last_approve_user=c.employee_id
				where a.proses_approval=1 and a.status not in ('REJECTED','READY_PO','CLOSED') and c.id=" . $this->user_id;

		$data =$this->db->query($query)->result_array();
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	public function countByUserApprove_post()
	{

		if ($this->user_id) {
			$query  = "SELECT COUNT(*)as jumlah from prc_pp_ht a left join gbm_organisasi b on a.lokasi_id=b.id 
				inner join fwk_users c on a.last_approve_user=c.employee_id
				where a.proses_approval=1 and a.status not in ('REJECTED','READY_PO','CLOSED') and c.id=" . $this->user_id;

			$retrieve =	$this->db->query($query)->row_array();
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
			return;
		} else {
			$this->set_response(array("status" => "OK", "data" => []), REST_Controller::HTTP_OK);
			return;
		}
	}
	public function countPPReadyPO_post()
	{

		if ($this->user_id) {
			$queryaccess_po = "SELECT a.new_, a.user_id,b.user_name,d.nama,d.email  FROM fwk_users_acces a INNER JOIN fwk_users b ON a.user_id=b.id
			INNER JOIN fwk_menu c ON a.menu_id=c.id
			INNER JOIN karyawan d ON  b.employee_id=d.id
			WHERE NAME='prc_po' and a.new_=1 and a.user_id=" . $this->user_id . "";
			$res_user = $this->db->query($queryaccess_po)->result_array();
			if (!$res_user) {
				$this->set_response(array("status" => "OK", "data" => array('jumlah' => 0)), REST_Controller::HTTP_OK);
				return;
			}

			$query  = "SELECT  COUNT(*)as jumlah  FROM prc_pp_ht a INNER JOIN prc_pp_dt b ON a.id=b.pp_hd_id left JOIN prc_po_dt c
			ON b.id=c.pp_dt_id 
			WHERE c.pp_dt_id IS NULL AND a.`status`='READY_PO' ";

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
		$retrieve = $this->PrcPpModel->retrieve($id);
		$retrieve['pembuat'] = $this->db->query("select id,nip,nama from karyawan where id 
		in(select employee_id from fwk_users where id=" . $retrieve['dibuat_oleh'] . ")")->row_array();
		$retrieve['detail'] = $this->PrcPpModel->retrieve_detail($id);
		foreach ($retrieve['detail'] as $key => $value) {
			$stok = $this->InvItemModel->cek_stok_lokasi_get($retrieve['lokasi_id'], $value['item_id'], $retrieve['tanggal']);
			$retrieve['detail'][$key]['stok'] = $stok;
		}

		if (!empty($retrieve)) {
			$retrieve['file_info']         = get_file_info($this->get_path_file($retrieve['upload_file']));
			$retrieve['file_info']['mime'] = get_mime_by_extension($this->get_path_file($retrieve['upload_file']));

			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function getAllDetail_get()
	{
		$retrieve = $this->PrcPpModel->retrieve_all_detail();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function getAllDetailByStatus_get()
	{
		$retrieve = $this->PrcPpModel->retrieve_all_detail_by_status();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function getAllDetailLokasiByStatus_get($lokasi_id)
	{
		$retrieve = $this->PrcPpModel->retrieve_all_detail_lokasi_by_status($lokasi_id);

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAll_get()
	{

		$retrieve = $this->PrcPpModel->retrieve_all_kategori();

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
		$this->load->library('Autonumber');
		$input['no_pp'] = $this->autonumber->purchase_request($input['lokasi_id']['id'], $input['tanggal']);
		$res =  $this->PrcPpModel->create($input);
		// $this->set_response(array("status" => "OK", "data" => $input['no_pp']), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'prc_pp', 'action' => 'new', 'entity_id' => $res);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $input['no_pp']), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function index_put($segment_3 = '')
	{
		$input = $this->put();
		$input['diubah_oleh'] = $this->user_id;
		$id = (int)$segment_3;
		$pp = $this->PrcPpModel->retrieve($id);
		if (empty($pp)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$res =   $this->PrcPpModel->update($pp['id'], $input);
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'prc_pp', 'action' => 'edit', 'entity_id' => $id);
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
		$kategori = $this->PrcPpModel->retrieve($id);
		if (empty($kategori)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$res =  $this->PrcPpModel->delete($kategori['id']);
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id'=>$id)), 'entity' => 'prc_pp', 'action' => 'delete', 'entity_id' => $id);
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
		$this->db->query("UPDATE prc_pp_ht  SET `status`='" . $input['status'] . "', `diubah_tanggal`='" . $input['diubah_tanggal'] . "', `diubah_oleh`=" . $input['diubah_oleh'] . " WHERE id=" . $id);

		$this->set_response(array("status" => "OK", "data" => true), REST_Controller::HTTP_OK);
	}


	function approval_post($segment_3 = '')
	{
		$input = $this->post();
		$input['diubah_oleh'] = $this->user_id;
		$id = (int)$segment_3;
		$pp = $this->PrcPpModel->retrieve($id);
		if (empty($pp)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}

		$retrieve =   $this->PrcPpModel->approval($pp['id'], $input);
		if ($retrieve) {
			if ($input['karyawan_id']['id']) {
				$karyawan = $this->db->query("select email,nama from karyawan where id=" . $input['karyawan_id']['id'])->row_array();
				if ($karyawan['email'] || $karyawan['email'] != '') {
					$email_subject = 'Info Notifikasi Approval PP';
					$email_body    = 'Hallo, Bpk/Ibu. ' . $karyawan['nama'] . '! <br>
					<br>
					No PP : ' .	$pp['no_pp'] . ' diajukan kepada Anda.<br> 
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
				
				$usr=$this->db->query("select * from fwk_users where employee_id=".$input['karyawan_id']['id']."" )->row_array();
				if ($usr['fcm_token']){
					$title="Approval PP";
					$message="Hallo, Bpk/Ibu. " . $karyawan['nama'] . "!. <br> PP No. : " .	$pp['no_pp'] . " diajukan kepada Anda.";
					$this->sendNotification($usr['fcm_token'],$title,$message ,'');
					$this->kirimNotifikasiAndroid($usr['fcm_token'],$title,$message ,array("no_pp"=>$pp['no_pp']));

				}
				
			}
			$pp = $this->PrcPpModel->retrieve($id);
			if ($pp['status'] == 'READY_PO') {

				$query_email = "SELECT a.new_, a.user_id,b.user_name,d.nama,d.email  FROM fwk_users_acces a INNER JOIN fwk_users b ON a.user_id=b.id
			INNER JOIN fwk_menu c ON a.menu_id=c.id
			INNER JOIN karyawan d ON  b.employee_id=d.id
			WHERE NAME='prc_po' and a.new_=1 ";
				$res_email = $this->db->query($query_email)->result_array();
				if ($res_email) {
					foreach ($res_email as $key => $value) {
						if ($value['email']) {
							$email_subject = 'Info Notifikasi Approval PP';
							$email_body    = 'Hallo, Bpk/Ibu. ' . $value['nama'] . '! <br>
					<br>
					No PP : ' .	$pp['no_pp'] . ' Sudah siap dibuat PO.<br> 
					Silakan melakukan ceate PO pada Aplikasi Antech Sistem melalui   <a href="http://erp.dpaplant.com" target="_blank">AnTech Plantation - DPA</a>. 
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
		$pp = $this->PrcPpModel->retrieve($id);
		if (empty($pp)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$retrieve =   $this->PrcPpModel->reject($pp['id'], $input);
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
	}

	function autonumber_get($lokasi, $tanggal, $supplier_id)
	{

		$this->load->library('Autonumber');
		$no = $this->autonumber->purchase_order($lokasi, $tanggal, $supplier_id);
		$this->set_response(array("status" => "OK", "data" =>	$no), REST_Controller::HTTP_OK);
	}

	function upload_post($segment_3='')
	{
		$id = (int)$segment_3;
		
		$prc_pp = $this->PrcPpModel->retrieve_by_id($id);

		// $this->set_response(['status' => 'OK', 'debug'=>$this->post()], REST_Controller::HTTP_CREATED);
		
		if (empty($prc_pp)) {
			$this->set_response([
				'status' => false,
				'message' => 'Quotation Tidak ditemukan',
			], REST_Controller::HTTP_NOT_FOUND);
			return;
		}

		$config['upload_path']   = $_SERVER['DOCUMENT_ROOT'] . "/" . "plantation" . "/userfiles/files";
		$config['allowed_types'] = 'doc|zip|rar|txt|docx|xls|xlsx|pdf|tar|gz|jpg|jpeg|JPG|JPEG|png|ppt|pptx';
		$config['max_size']      = '0';
		$config['max_width']     = '0';
		$config['max_height']    = '0';
		$config['file_name']     = url_title('PP_' . $prc_pp['no_pp'] . '_' . time(), '_', TRUE);
		$this->upload->initialize($config);
		$error_upload = array();

		if ($this->upload->do_upload()) {
			$upload_data = $this->upload->data();
			$error_upload = $this->upload->display_errors();
		} else {
			$upload_data['file_name'] = $prc_pp['upload_file'];
			$error_upload = $this->upload->display_errors();
		}
		$file = $upload_data['file_name'];
		$input = $this->post();
		$prc_pp_update = $this->PrcPpModel->save_upload($prc_pp, $file);
		
		if ($prc_pp_update) {
			$message = [
				'status' => "OK",
				'id' => $prc_pp['id'],
				'error' => ($_FILES),
				'er' => $this->upload->display_errors(),
				'upload_data' => $this->upload->data()
			];
			$this->set_response(
				$message,
				REST_Controller::HTTP_CREATED
			);
		} else {
			$this->set_response([
				'status' => 'NOT OK',
				'message' => 'Gagal update',
				'error' => ($_FILES),
				'er' => $this->upload->display_errors(),
				'upload_data' => $this->upload->data()
			], REST_Controller::HTTP_NOT_FOUND);
		}
	}

	public function download_get($id)
	{
		$Prcpp = $this->PrcPpModel->retrieve_by_id($id);
		if (!empty($Prcpp['upload_file'])) {
			$target_file = $this->get_path_file($Prcpp['upload_file']);
			if (!is_file($target_file)) {
				show_error("Maaf file tidak ditemukan." . $target_file);
			}

			$data_file = file_get_contents($target_file); // Read the file's contents
			$name_file = $Prcpp['upload_file'];

			force_download($name_file, $data_file);
		}
	}
	function get_path_file($file = '')
	{
		//  return './'.USERFILES.'/files/'.$file;
		return	$_SERVER['DOCUMENT_ROOT'] . "/" . 'hcis_folder' . "/userfiles/files/" . $file;
	}

	function print_slip_get($segment_3 = '')
	{

		$id = (int)$segment_3;
		$data = [];

		$queryHeader = "SELECT a.*,
		-- d.nama as gudang,
		f.nama as user_approve1,
		ff.nama as user_approve2,
		fff.nama as user_approve3,
		ffff.nama as user_approve4,
		fffff.nama as user_approve5,
		g.user_full_name as user_full_name,
		e.nama as lokasi
		FROM prc_pp_ht a 
		-- INNER JOIN gbm_organisasi d ON a.gudang_id=d.id
		INNER JOIN gbm_organisasi e ON a.lokasi_id=e.id
		LEFT JOIN karyawan f ON a.user_approve1=f.id
		LEFT JOIN karyawan ff ON a.user_approve2=ff.id
		LEFT JOIN karyawan fff ON a.user_approve3=fff.id
		LEFT JOIN karyawan ffff ON a.user_approve4=ffff.id
		LEFT JOIN karyawan fffff ON a.user_approve5=fffff.id
		LEFT JOIN fwk_users g ON a.dibuat_oleh=g.id
		WHERE a.id=" . $id . "";
		$dataHeader = $this->db->query($queryHeader)->row_array();

		$queryDetail = "SELECT a.*,
		b.kode as kode_barang,
		b.nama as nama_barang,
		f.nama as uom
		FROM prc_pp_dt a 
		inner join inv_item b on a.item_id=b.id 
		left join gbm_uom f on b.uom_id=f.id 
		WHERE  a.pp_hd_id = " . $id . "";
		$dataDetail = $this->db->query($queryDetail)->result_array();

		foreach ($dataDetail as $key => $value) {
			$stok = $this->InvItemModel->cek_stok_lokasi_get($dataHeader['lokasi_id'], $value['item_id'], $dataHeader['tanggal']);
			$dataDetail[$key]['stok'] = $stok;
		}


		// $perminta = $this->InvPermintaanBarangModel->print_slip($id);
		$data['header'] = 	$dataHeader;
		$data['detail'] = 	$dataDetail;


		$data['database'] = $this->db;

		$html = $this->load->view('PrcPp_laporan', $data, true);
		$dompdf = new DOMPDF;
		$dompdf->loadHtml($html);
		$dompdf->setPaper('A4', 'landscape');
		$dompdf->render();
		$filename = 'report_' . time();
			$x          = 750;
			$y          = 50;
			$text       = "{PAGE_NUM} of {PAGE_COUNT}";
			$font       = null;// $dompdf->getFontMetrics()->get_font('Helvetica', 'normal');
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
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		// echo $html;
	}
	function print_slip_html_get($segment_3 = '')
	{

		$id = (int)$segment_3;
		$data = [];

		$queryHeader = "SELECT a.*,
		-- d.nama as gudang,
		f.nama as user_approve1,
		ff.nama as user_approve2,
		fff.nama as user_approve3,
		ffff.nama as user_approve4,
		fffff.nama as user_approve5,
		g.user_full_name as user_full_name,
		e.nama as lokasi
		FROM prc_pp_ht a 
		-- INNER JOIN gbm_organisasi d ON a.gudang_id=d.id
		INNER JOIN gbm_organisasi e ON a.lokasi_id=e.id
		LEFT JOIN karyawan f ON a.user_approve1=f.id
		LEFT JOIN karyawan ff ON a.user_approve2=ff.id
		LEFT JOIN karyawan fff ON a.user_approve3=fff.id
		LEFT JOIN karyawan ffff ON a.user_approve4=ffff.id
		LEFT JOIN karyawan fffff ON a.user_approve5=fffff.id
		LEFT JOIN fwk_users g ON a.dibuat_oleh=g.id
		WHERE a.id=" . $id . "";
		$dataHeader = $this->db->query($queryHeader)->row_array();

		$queryDetail = "SELECT a.*,
		b.kode as kode_barang,
		b.nama as nama_barang,
		f.nama as uom
		FROM prc_pp_dt a 
		inner join inv_item b on a.item_id=b.id 
		left join gbm_uom f on b.uom_id=f.id 
		WHERE  a.pp_hd_id = " . $id . "";
		$dataDetail = $this->db->query($queryDetail)->result_array();

		foreach ($dataDetail as $key => $value) {
			$stok = $this->InvItemModel->cek_stok_lokasi_get($dataHeader['lokasi_id'], $value['item_id'], $dataHeader['tanggal']);
			$dataDetail[$key]['stok'] = $stok;
		}
		$data['header'] = 	$dataHeader;
		$data['detail'] = 	$dataDetail;


		$data['database'] = $this->db;

		$html = $this->load->view('PrcPp_laporan', $data, true);
		echo $html;
		
	}
	function laporan_pp_status_post()
	{

		error_reporting(0);
		if (isset($this->post()['tgl_mulai'])) {
			$input = $this->post();
			$lokasi_id     = $this->post('lokasi_id', true);
			$status_pp_id     = $this->post('status_pp_id', true);
			$pp_id     = $this->post('pp_id', true);
			$item_id     = $this->post('item_id', true);
			$tgl_mulai =  $this->post('tgl_mulai', true);
			$tgl_akhir = $this->post('tgl_akhir', true);
			$format_laporan =  $this->post('format_laporan', true);
		} else {
			$input = [
				'tanggal' => '2021-12-23',
			];
			$lokasi_id = 263;
			$pp_id = 1030;
			$item_id = 1020;
			$tgl_mulai = '2020-01-01';
			$tgl_akhir = '2022-12-12';
			$format_laporan =  'xls';
		}
		// $lokasi_id     = $this->post('lokasi_id', true);
		// $pp_id     = $this->post('pp_id', true);
		// $item_id     = $this->post('item_id', true);
		// $tgl_mulai =  $this->post('tgl_mulai', true);
		// $tgl_akhir = $this->post('tgl_akhir', true);
		// $format_laporan =  $this->post('format_laporan',true);

		$no_pp = "";
		$qry = "";
		$where = "";
		if ($pp_id) {
			$where = $where . "  and id=" . $pp_id . "";
			$retrievePp = $this->db->query("select * from prc_pp_ht where id=" . $pp_id . "")->row_array();
			$no_pp = $retrievePp['no_pp'];
		} else {
			$no_pp = "Semua";
		}
		if ($status_pp_id) {
			if ($status_pp_id == 'CREATED') {
				$where = $where . "  and status='CREATED'";
				$status_pp = 'CREATED';
			} elseif ($status_pp_id == 'REJECTED') {
				$where = $where . "  and status='REJECTED'";
				$status_pp = 'REJECTED';
			} elseif ($status_pp_id == 'CLOSED') {
				$where = $where . "  and status='CLOSED'";
				$status_pp = 'CLOSED';
			} else	if ($status_pp_id == 'READY_PO') {
				$where = $where . "  and status='READY_PO'";
				$status_pp = 'READY_PO';
			} else	if ($status_pp_id == 'READY_PO_WITHOUT') {
				$where = $where . "  and status='READY_PO' and no_po IS NULL";
				$status_pp = 'READY_PO (Belum dibuat PO)';
			} else {
				$where = $where . "  and status not in ('CREATED','READY_PO','REJECTED')";
				$status_pp = 'PROSES PERSETUJUAN';
			}
		} else {
			$status_pp = 'Semua';
		}
		if ($item_id) {
			$where = $where . "  and item_id=" . $item_id . "";
			$retrieveitem = $this->db->query("select * from inv_item where id=" . $item_id . "")->row_array();
			$nama_item = $retrieveitem['nama'];
		} else {
			$nama_item = "Semua";
		}
		$lokasi = "";
		if ($lokasi_id) {
			$where = $where . "  and lokasi_id=" . $lokasi_id . "";
			$retrieveitem = $this->db->query("select * from gbm_organisasi where id=" . $lokasi_id . "")->row_array();
			$lokasi = $retrieveitem['nama'];
		} else {
			$lokasi = "Semua";
		}
		$qry = "select * from prc_pp_status_vw
		 where tanggal>='" . $tgl_mulai . "' 
		 and tanggal<='" . $tgl_akhir . "'" . $where . "
		  order by tanggal";
		$retrievePp = $this->db->query($qry)->result_array();

		// var_dump($results);
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report();
		} else {
			$html = get_header_report_v2();
		}


		$html = $html . '
		<div class="row">
  	<div class="span12">
	  <br>
	<div class="kop-print">
	  <div class="kop-nama">KLINIK ANNAJAH</div>
	  <div class="kop-info"> Jl Sukarno Hatta No 12 No 12, Bandung Jawa Barat 14450</div>
	  <div class="kop-info">Telp : (021) 6684055</div>
	</div>
	  <hr class="kop-print-hr">
  	</div>
  	</div>
  <h3 class="title">LAPORAN STATUS PERMINTAAN PEMBELIAN</h3>
  <table class="no_border" style="width:30%">
			
			<tr>
					<td>Lokasi</td>
					<td>:</td>
					<td>' .  $lokasi . '</td>
			</tr>
			<tr>
					<td>Status</td>
					<td>:</td>
					<td>' .  $status_pp . '</td>
			</tr>
			<tr>
					<td>No PP</td>
					<td>:</td>
					<td>' .  $no_pp . '</td>
			</tr>
			<tr>
					<td>Item</td>
					<td>:</td>
					<td>' .  $nama_item . '</td>
			</tr>
			<tr>	
					<td>Periode</td>
					<td>:</td>
					<td>' . tgl_indo($tgl_mulai) . ' s/d ' . tgl_indo($tgl_akhir) . '</td>
			</tr>
			
	</table>
			<br>';

		$html = $html . ' <table border="1" width="100%" style="border-collapse: collapse;" >
			<thead>
				<tr>
				<th width="4%">No.</th>
				<th>Lokasi</th>
				<th>No PP</th>
				<th>Tanggal</th>
				<th>Status</th>
				<th>Kode Item</th>
				<th>Nama Item</th>
				<th>Satuan</th>
				<th>Qty</th>
				<th>No PO</th>
				<th>Tgl PO</th>
				<th>No Penerimaan</th>
				<th>Tgl Penerimaan</th>
				<th>No Surat Jalan Supp</th>
				
				</tr>
			</thead>
			<tbody>';


		$no = 0;

		foreach ($retrievePp as $key => $m) {
			$actual_link = "http://$_SERVER[HTTP_HOST]" . "/plantation-api/api/GlobalReport/prc_pp_slip/" . $m['id'] . "";
			$no++;
			$status = $m['status'];
			if ($m['last_approve_position'] != '' && $m['status'] != 'REJECTED' && $m['status'] != 'READY_PO') {
				$status = $m['last_approve_position'];
			}
			$no_penerimaan = '';
			$tgl_penerimaan = '';
			$no_surat_jalan = '';
			if ($m['po_id']) {
				$res_penerimaan = $this->db->query(" select a.* from inv_penerimaan_po_ht a inner join inv_penerimaan_po_dt b
			on a.id=b.penerimaan_po_hd_id where b.item_id=" . $m['item_id'] . " and b.po_dt_id=" . $m['po_dt_id'] . " ")->result_array();

				foreach ($res_penerimaan as $key => $penerimaan) {
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
			$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative; text-align: center;">
							' . ($no) . '

						</td>
						<td>
						' . $m['nama_lokasi'] . ' 
						
						</td>
						<td><a href="' . $actual_link  . '" target="_blank"> ' . $m['no_pp'] . ' </a>
						
						
						<td style="text-align: center;">
						' . tgl_indo_normal($m['tanggal']) . ' 
						
						</td>
						<td>
						' . $status . ' 
						
						</td>
						<td style="text-align: center;">
						' . $m['kode_item'] . ' 
							
						</td>
						<td>
						' . $m['nama_item'] . ' 
							
						</td>
						
						<td style="text-align: center;">
						' . $m['uom'] . ' 
						
						</td>
						<td style="text-align: right;">' . number_format($m['qty']) . ' 
						<td>
							' . $m['no_po'] . ' 
						</td>
						<td style="text-align: center;">
							' . tgl_indo_normal($m['tanggal_po']) . ' 
						</td>
						<td >
							' . $no_penerimaan . ' 
						</td>
						<td style="text-align: center;">
							' . $tgl_penerimaan . ' 
						</td>
						<td>
							' . $no_surat_jalan . ' 
						</td>
					';
			$html = $html . '
									
					</tr>';
		}

		$html = $html . ' 	
						<tr class=":: arc-content">
						<td style="position:relative;">
							&nbsp;

						</td>
						
						<td>
							&nbsp;
						</td>
						<td>
							&nbsp;
						</td>
						<td>
							&nbsp;
						</td>
						<td>
							&nbsp;
						</td>
						<td>
							&nbsp;
						</td>
						<td>
							&nbsp;
						</td>
						<td>
							&nbsp;
						</td>
						<td>
							&nbsp;
						</td>
						<td>
							&nbsp;
						</td>
						<td>
							&nbsp;
						</td>
						<td>
							&nbsp;
						</td>
						<td>
							&nbsp;
						</td>
									
						</tr>
					</tbody>
					</table>
						';
		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
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
			$this->pdfgenerator->generate($html, $filename, true, 'A3', 'landscape');
		}
	}

	public function kirimNotifikasiAndroid($token, $title,$body, $data_send)
	{
		// $token = $token;
		// $message = "Test notification message";
		$this->load->library('Fcm');
		$this->fcm->kirimNotif($token,$title,$body,$data_send);
		// print_r($p);
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
}
