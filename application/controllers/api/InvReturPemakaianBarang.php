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

class InvReturPemakaianBarang extends BD_Controller
{
	public $user_id;
	public $theCredential;

	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('InvReturPemakaianBarangModel');
		$this->load->model('InvItemModel');
		$this->load->model('AccJurnalModel');
		$this->load->model('M_DatatablesModel');
		$this->load->library('pdfgenerator');
		$this->load->helper("antech_helper");
		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id = $this->user_data->id;
	}
	public function list_post()
	{
		$post = $this->post();

		$query  = "		SELECT 
		a.*,
		b.nama as lokasi_afd,
		d.nama as gudang,
		c.nama as karyawan,
		e.nama as lokasi,
		f.nama as lokasi_traksi,
		g.user_full_name AS dibuat,
		h.user_full_name AS diubah,
		i.user_full_name AS diposting
		FROM inv_retur_pemakaian_ht a 
		LEFT JOIN gbm_organisasi b ON a.lokasi_afd_id=b.id
		INNER JOIN gbm_organisasi d ON a.gudang_id=d.id
		INNER JOIN gbm_organisasi e ON a.lokasi_id=e.id
		INNER JOIN karyawan c on a.karyawan_id=c.id 
		LEFT JOIN gbm_organisasi f ON a.lokasi_traksi_id=f.id
		LEFT JOIN fwk_users g ON a.dibuat_oleh = g.id
		LEFT JOIN fwk_users h ON a.diubah_oleh = h.id
		LEFT JOIN fwk_users i ON a.diposting_oleh = i.id
		
		";
		$search = array('a.tanggal', 'b.nama', 'c.nama', 'a.no_transaksi');
		$where  = null;

		$isWhere = " a.lokasi_id in
		(select location_id from fwk_users_location where user_id=" . $this->user_id . ")";

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}

	function index_get($id = '')
	{
		$retrieve = $this->InvReturPemakaianBarangModel->retrieve_by_id($id);
		$retrieve['detail'] = $this->InvReturPemakaianBarangModel->retrieve_detail($id);


		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function getReturByPemakaianPosting_get($id = '')
	{
		$retrieve = $this->InvReturPemakaianBarangModel->retrieve_by_pemakaian_posting($id);
		// $retrieve['detail'] = $this->InvReturPemakaianBarangModel->retrieve_detail($id);

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}


	function getAll_get()
	{

		$retrieve = $this->InvReturPemakaianBarangModel->retrieve_all();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getTraksi_get()
	{

		$retrieve = $this->InvReturPemakaianBarangModel->retrieve_traksi();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAllDetail_get($id = '')
	{
		$retrieve = $this->InvReturPemakaianBarangModel->retrieve_all_detail($id);

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
		$input['no_transaksi'] = $this->autonumber->inv_retur_pemakaian_barang($input['lokasi_id']['id'], $input['tanggal']);

		//$input['no_spat'] = $this->getLastNumber('pks_pengolahan_ht', 'no_spat', 'SPAT');
		// var_dump($input);
		$res = $this->InvReturPemakaianBarangModel->create($input);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'inv_pemakaian_barang', 'action' => 'new', 'entity_id' => $res);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $input['no_transaksi']), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => $input), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function index_put($segment_3 = null)
	{
		$id = (int)$segment_3;
		$data = $this->put();
		$data['diubah_oleh'] = $this->user_id;

		$res = $this->InvReturPemakaianBarangModel->update($id, $data);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'inv_pemakaian_barang', 'action' => 'edit', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	public function index_delete($id)
	{

		$res = $this->InvReturPemakaianBarangModel->delete($id);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id'=>$id)), 'entity' => 'inv_pemakaian_barang', 'action' => 'delete', 'entity_id' => $id);
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

		$retrieve_header = $this->db->query(" select * from inv_retur_pemakaian_ht where id=".$id ." ")->row_array();
		if (empty($retrieve_header)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Untuk diposting"), REST_Controller::HTTP_OK);
			return;
		} else {
			if ($retrieve_header['is_posting'] == 1) {
				$this->set_response(array("status" => "NOT OK", "data" => "Data Sudah diposting"), REST_Controller::HTTP_OK);
				return;
			}
		}
		// CEK PERIODE SDH ADA ATAU SDH CLOSE//
		$chk = cek_periode($retrieve_header['tanggal'], $retrieve_header['lokasi_id']);
		if ($chk['status'] == false) {
			$this->set_response(array("status" => "NOT OK", "data" => $chk['message']), REST_Controller::HTTP_OK);
			return;
		}
		//==============//
		
		$retrieve_detail = $this->InvReturPemakaianBarangModel->retrieve_detail($id);


		$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'INV_RETUR_PEMAKAIAN_BARANG');
		// Data HEADER
		$this->load->library('Autonumber');
		$no_jurnal = $this->autonumber->jurnal_auto($retrieve_header['lokasi_id'], $retrieve_header['tanggal'], 'INV-RPM');

		$dataH = array(
			'no_jurnal' => $no_jurnal,
			'lokasi_id' => $retrieve_header['lokasi_id'],
			'tanggal' => $retrieve_header['tanggal'],
			'no_ref' => $retrieve_header['no_transaksi'],
			'ref_id' => $retrieve_header['id'],
			'tipe_jurnal' => 'AUTO',
			'modul' => 'INV_RETUR_PEMAKAIAN_BARANG',
			'keterangan' => 'INV_RETUR_PEMAKAIAN_BARANG',
			'is_posting' => 1
		);
		$id_header = $this->AccJurnalModel->create_header($dataH);

		$nilai = 0;
		foreach ($retrieve_detail as $key => $value) {
		
			$inv_tr = $this->db->query("select * from inv_transaksi_harian  where 1=1 and tipe='PEMAKAIAN' and ref_id=" . $retrieve_header['inv_pemakaian_id'] . "
			and item_id=" . $value['item_id'] . "")->row_array();
			//   $item_dt = $this->db->query("select * from inv_item_dt  where gudang_id=" . $value['gudang_id'] . "
			// 	and item_id=" . $value['item_id'] . "")->row_array();
			$avg_price = $inv_tr['nilai_keluar'] / $inv_tr['qty_keluar'];
			$nilai =  ($avg_price * $value['qty']);
			$keterangan = '';
			if (is_null($value['nama_blok']) == false) {
				$keterangan = 'Retur Pemakaian Item:' . $value['nama_barang'] . ', qty:' . $value['qty'] . ' ' . $value['uom'] . ',Blok/Mesin:' . $value['nama_blok'];
			} else if (is_null($value['nama_kendaraan']) == false) {
				$keterangan =	'Retur Pemakaian Item:' . $value['nama_barang'] . ', qty:' . $value['qty'] . ' ' . $value['uom'] . ',Unit:' . $value['nama_kendaraan'];
			} else {
				$keterangan = 'Retur Pemakaian Item:' . $value['nama_barang'] . ', qty:' . $value['qty'] . ' ' . $value['uom'];
			}
			$dataDr = array(
				'lokasi_id' => $retrieve_header['lokasi_id'],
				'jurnal_id' => $id_header,
				'acc_akun_id' => ($value['acc_akun_id']), //akun 
				'debet' => $nilai,
				'kredit' => 0,
				'ket' => $keterangan,
				'no_referensi' => $retrieve_header['no_transaksi'],
				'referensi_id' => NULL,
				'blok_stasiun_id' => NULL,
				'kegiatan_id' => NULL, //kegiatan ,
				'item_id' => $value['item_id'], //karyawan,
			);

			$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataDr);
			$dataCr = array(
				'lokasi_id' => $retrieve_header['lokasi_id'],
				'jurnal_id' => $id_header,
				'acc_akun_id' => $value['acc_akun_biaya_id'], //akun ,
				'debet' =>  0,
				'kredit' => $nilai,
				'ket' => $keterangan,
				'no_referensi' => $retrieve_header['no_transaksi'],
				'referensi_id' => NULL,
				'item_id' => $value['item_id'], //item,
				'umur_tanam_blok' => $value['umur_tanam_blok'],
				'blok_stasiun_id' => $value['blok_id'],
				'kegiatan_id' => $value['kegiatan_id'],
				'kendaraan_mesin_id' => $value['traksi_id']
			);
			$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataCr);
		}
		$data['diposting_oleh'] = $this->user_id;
		$data['tanggal'] =	$retrieve_header['tanggal'];
		$res = $this->InvReturPemakaianBarangModel->posting($id, $data);

		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id'=>$id)), 'entity' => 'inv_pemakaian_barang', 'action' => 'posting', 'entity_id' => $id);
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
		b.nama as lokasi_afd,
		d.nama as gudang,
		c.nama as karyawan,
		e.nama as lokasi
		FROM inv_retur_pemakaian_ht a 
		left JOIN gbm_organisasi b ON a.lokasi_afd_id=b.id
		left JOIN gbm_organisasi d ON a.gudang_id=d.id
		left JOIN gbm_organisasi e ON a.lokasi_id=e.id
		left JOIN karyawan c on a.karyawan_id=c.id  WHERE a.id=" . $id . "";
		$dataHeader = $this->db->query($queryHeader)->row_array();

		$queryDetail = "SELECT a.*,
		b.kode as kode_barang,
		b.nama as nama_barang,
		f.nama as uom,
		c.kode as blok,
		d.nama as nama_kendaraan,
		e.nama as nama_kegiatan 
		FROM inv_retur_pemakaian_dt a 
		inner join inv_item b on a.item_id=b.id 
		left join gbm_organisasi c on a.blok_id=c.id 
		left join trk_kendaraan d on a.traksi_id=d.id 
		left join acc_kegiatan e on a.kegiatan_id=e.id 
		left join gbm_uom f on b.uom_id=f.id WHERE  a.inv_retur_pemakaian_id = " . $id . "";
		$dataDetail = $this->db->query($queryDetail)->result_array();



		// $perminta = $this->InvPermintaanBarangModel->print_slip($id);
		$data['header'] = 	$dataHeader;
		$data['detail'] = 	$dataDetail;

		$html = $this->load->view('InvSlipPemakaian', $data, true);

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
			'tgl_akhir' => '2022-12-12',
		];

		$lokasi_id = $this->post('lokasi_id', true);
		// $gudang_id=$this->post('gudang_id',true);
		$tanggal_awal = $this->post('tgl_mulai', true);
		$tanggal_akhir = $this->post('tgl_akhir', true);

		// $lokasi_id = $input['lokasi_id'];
		// $gudang_id = $input['gudang_id'];
		// $tanggal_awal = $input['tgl_mulai'];
		// $tanggal_akhir = $input['tgl_akhir'];

		$queryPo = "SELECT
		a.qty as qty,
		b.no_transaksi,
		b.tanggal as tanggal,
		c.nama AS nama_afdeling,
		cc.nama AS nama_traksi,
		d.nama as gudang,
		f.nama as item,
		f.kode as kode,
		g.nama as uom,
		h.no_transaksi as no_transaksi_pemakaian,
		a.id AS id
		
		FROM inv_retur_pemakaian_dt a
		
		INNER JOIN inv_retur_pemakaian_ht b on a.inv_retur_pemakaian_id=b.id
		LEFT JOIN gbm_organisasi c ON b.lokasi_afd_id=c.id
		LEFT JOIN gbm_organisasi cc ON b.lokasi_traksi_id=cc.id
		LEFT JOIN gbm_organisasi d on b.gudang_id=d.id
		LEFT JOIN karyawan e ON b.karyawan_id=d.id
		INNER JOIN inv_item f on a.item_id=f.id
		LEFT JOIN gbm_uom g on f.uom_id=g.id
		
    LEFT JOIN inv_pemakaian_ht h on b.inv_pemakaian_id=h.id
        
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
		// $filter_gudang = "Semua";
		// if ($gudang_id) {
		// 	$queryPo = $queryPo . " and b.gudang_id=" . $gudang_id . "";
		// 	$res = $this->db->query("select * from gbm_organisasi where id=" . $gudang_id . "")->row_array();
		// 	$filter_gudang = $res['nama'];
		// }

		$dataPo = $this->db->query($queryPo)->result_array();

		$data['po'] = 	$dataPo;
		$data['filter_lokasi'] = 	$filter_lokasi;
		// $data['filter_gudang'] = 	$filter_gudang;
		$data['filter_tgl_awal'] = 	$tanggal_awal;
		$data['filter_tgl_akhir'] = $tanggal_akhir;

		$html = $this->load->view('Inv_Retur_Pemakaian_Barang_Laporan', $data, true);

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
