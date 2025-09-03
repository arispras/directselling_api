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

class InvPermintaanBarang extends BD_Controller
{
	public $user_id;
	public $theCredential;

	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('InvPermintaanBarangModel');
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
					b.nama as lokasi_afd,
					d.nama as gudang,
					c.nama as karyawan,
					e.nama as lokasi,
					f.nama as lokasi_traksi
					FROM inv_permintaan_ht a 
					LEFT JOIN gbm_organisasi b ON a.lokasi_afd_id=b.id
					LEFT JOIN gbm_organisasi d ON a.gudang_id=d.id
					LEFT JOIN gbm_organisasi e ON a.lokasi_id=e.id
					LEFT JOIN karyawan c on a.karyawan_id=c.id  
					LEFT JOIN gbm_organisasi f ON a.lokasi_traksi_id=f.id
		";
		$search = array('a.tanggal', 'b.nama', 'c.nama');
		$where  = null;

		$isWhere = null;
		// $isWhere = 'artikel.deleted_at IS NULL';

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}

	function index_get($id = '')
	{
		$retrieve = $this->InvPermintaanBarangModel->retrieve_by_id($id);
		$retrieve['detail'] = $this->InvPermintaanBarangModel->retrieve_detail($id);


		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}


	function getAll_get()
	{

		$retrieve = $this->InvPermintaanBarangModel->retrieve_all();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_OK);
		}
	}
	function getAllBelumPemakaian_get($lokasi_id)
	{

		$retrieve = $this->db->query(
			" select a.*,b.id as inv_pemakaian_id from inv_permintaan_ht a left join inv_pemakaian_ht b
			on a.id=b.inv_permintaan_id
			 where a.lokasi_id =" . $lokasi_id . " and a.is_posting=1 and 
			 b.id is null"
		)->result_array();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_OK);
		}
	}
	function getTraksi_get()
	{

		$retrieve = $this->InvPermintaanBarangModel->retrieve_traksi();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAllDetail_get($id = '')
	{
		$retrieve = $this->InvPermintaanBarangModel->retrieve_all_detail($id);

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
		$input['no_transaksi'] = $this->autonumber->inv_permintaan_barang($input['lokasi_id']['id'], $input['tanggal']);

		//$input['no_spat'] = $this->getLastNumber('pks_pengolahan_ht', 'no_spat', 'SPAT');
		// var_dump($input);
		$res = $this->InvPermintaanBarangModel->create($input);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'inv_permintaan_barang', 'action' => 'new', 'entity_id' => $res);
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

		$res = $this->InvPermintaanBarangModel->update($id, $data);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'inv_permintaan_barang', 'action' => 'edit', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	public function index_delete($id)
	{

		$res = $this->InvPermintaanBarangModel->delete($id);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id'=>$id)), 'entity' => 'inv_permintaan_barang', 'action' => 'delete', 'entity_id' => $id);
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
		$retrieve_header = $this->InvPermintaanBarangModel->retrieve_by_id($id);
		$data['diposting_oleh'] = $this->user_id;
		$data['tanggal'] =	$retrieve_header['tanggal'];
		$res = $this->InvPermintaanBarangModel->posting($id, $data);
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
		b.nama as lokasi_afd,
		b.nama as lokasi_traksi,
		d.nama as gudang,
		c.nama as karyawan,
		e.nama as lokasi
		FROM inv_permintaan_ht a 
		LEFT JOIN gbm_organisasi b ON a.lokasi_afd_id=b.id
		LEFT JOIN gbm_organisasi bb ON a.lokasi_traksi_id=bb.id
		LEFT JOIN gbm_organisasi d ON a.gudang_id=d.id
		LEFT JOIN gbm_organisasi e ON a.lokasi_id=e.id
		LEFT JOIN karyawan c on a.karyawan_id=c.id  WHERE a.id=" . $id . "";
		$dataHeader = $this->db->query($queryHeader)->row_array();

		$queryDetail = "SELECT a.*,
		b.kode as kode_barang,
		b.nama as nama_barang,
		f.nama as uom,
		c.kode as blok,
		d.nama as nama_kendaraan,
		e.nama as nama_kegiatan 
		FROM inv_permintaan_dt a 
		inner join inv_item b on a.item_id=b.id 
		left join gbm_organisasi c on a.blok_id=c.id 
		left join trk_kendaraan d on a.traksi_id=d.id 
		left join acc_kegiatan e on a.kegiatan_id=e.id 
		left join gbm_uom f on b.uom_id=f.id WHERE  a.inv_permintaan_id = " . $id . "";
		$dataDetail = $this->db->query($queryDetail)->result_array();



		// $perminta = $this->InvPermintaanBarangModel->print_slip($id);
		$data['header'] = 	$dataHeader;
		$data['detail'] = 	$dataDetail;

		$html = $this->load->view('InvSlipPermintaan', $data, true);

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		echo $html;
	}


	function laporan_po_detail_post()
	{
		$format_laporan =  $this->post('format_laporan', true);
		// $id = (int)$segment_3;
		$data = [];

		$input = [
			'no_po' => '01/PO/DPA',
			'lokasi_id' => 252,
			'gudang_id' => 740,
			'tgl_mulai' => '2020-01-01',
			'tgl_akhir' => '2022-04-18',
		];

		$lokasi_id = $this->post('lokasi_id', true);
		$gudang_id = $this->post('gudang_id', true);
		$tanggal_awal = $this->post('tgl_mulai', true);
		$tanggal_akhir = $this->post('tgl_akhir', true);

		// $lokasi_id=$input['lokasi_id'];
		// $gudang_id=$input['gudang_id'];
		// $tanggal_awal=$input['tgl_mulai'];
		// $tanggal_akhir=$input['tgl_akhir'];

		$queryPo = "SELECT
		b.*,
		a.qty as qty,
		b.no_transaksi,
		b.tanggal as tanggal,
		d.nama as uom,
		c.nama as item,
		c.kode as kode,
		-- e.nama as gudang,
		a.id AS id
		
		FROM inv_permintaan_dt a
		
		INNER JOIN inv_permintaan_ht b on a.inv_permintaan_id=b.id
		INNER JOIN inv_item c on a.item_id=c.id
		INNER JOIN gbm_organisasi e on b.gudang_id=e.id
		LEFT JOIN gbm_uom d on c.uom_id=d.id
		-- INNER JOIN gbm_supplier e on b.supplier_id=e.id
        
        -- LEFT JOIN inv_permintaan_ht h on b.inv_permintaan_id=h.id
        -- LEFT JOIN prc_po_ht h on b.po_id=h.id
		-- LEFT JOIN prc_po_dt g on h.po_hd_id=g.id

		where b.tanggal between  '" . $tanggal_awal . "' and  '" . $tanggal_akhir . "'	
		";

		// if (!empty($no_po)) {
		// 	$queryPo = $queryPo."and h.no_po LIKE '%".$no_po."%' ";
		// }

		$filter_lokasi = "Semua";
		if ($lokasi_id) {
			$queryPo = $queryPo . " and b.lokasi_id=" . $lokasi_id . "";
			$res = $this->db->query("select * from gbm_organisasi where id=" . $lokasi_id . "")->row_array();
			$filter_lokasi = $res['nama'];
		}

		$filter_gudang = "Semua";
		if ($gudang_id) {
			$queryPo = $queryPo . " and b.gudang_id=" . $gudang_id . "";
			$res = $this->db->query("select * from gbm_organisasi where id=" . $gudang_id . "")->row_array();
			$filter_gudang = $res['nama'];
		}

		$dataPo = $this->db->query($queryPo)->result_array();

		$data['po'] = 	$dataPo;
		// $data['filter_supplier'] = 	$filter_supplier;
		$data['filter_gudang'] = 	$filter_gudang;
		$data['filter_lokasi'] = 	$filter_lokasi;
		$data['filter_tgl_awal'] = 	$tanggal_awal;
		$data['filter_tgl_akhir'] = $tanggal_akhir;

		$html = $this->load->view('Inv_Permintaan_Barang_Laporan', $data, true);

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
