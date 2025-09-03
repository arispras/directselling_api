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

class InvPenerimaanPo extends BD_Controller
{
	public $user_id;
	public $theCredential;

	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('InvPenerimaanPoModel');
		$this->load->model('M_DatatablesModel');
		$this->load->model('AccJurnalModel');
		$this->load->model('PrcPoModel');
		$this->load->helper(array('url', 'antech_helper', 'form', 'text',  'security', 'file', 'number', 'date', 'download'));
		$this->load->library('image_lib');
		$this->load->library('upload');
		$this->load->library('pdfgenerator');
		$this->load->helper("antech_helper");
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
		b.nama as gudang,
		c.nama as lokasi,
		d.no_po as no_po,
		e.nama_supplier as supplier,
		g.user_full_name AS dibuat,
		h.user_full_name AS diubah,
		i.user_full_name AS diposting
		FROM inv_penerimaan_po_ht a 
		INNER JOIN gbm_organisasi b ON a.gudang_id=b.id
		INNER JOIN gbm_organisasi c ON a.lokasi_id=c.id
		INNER JOIN prc_po_ht d ON a.po_id=d.id
		INNER JOIN gbm_supplier e ON d.supplier_id=e.id
		LEFT JOIN fwk_users g ON a.dibuat_oleh = g.id
		LEFT JOIN fwk_users h ON a.diubah_oleh = h.id
		LEFT JOIN fwk_users i ON a.diposting_oleh = i.id
		";
		$search = array('a.tanggal', 'a.no_transaksi', 'd.no_po', 'e.nama_supplier');
		$where  = null;

		// $isWhere = " a.lokasi_id in
		// (select location_id from fwk_users_location where user_id=" . $this->user_id . ")";
		$isWhere = " 1=1";
		if ($param['tgl_mulai'] && $param['tgl_mulai']) {
			$isWhere = " a.tanggal between '" . $param['tgl_mulai'] . "' and '" . $param['tgl_akhir'] . "'";
		}
		if ($param['lokasi_id']) {
			$isWhere = $isWhere . " and a.lokasi_id =" . $param['lokasi_id'] . "";
		} else {
			$isWhere = $isWhere . " and  a.lokasi_id in
			(select location_id from fwk_users_location where user_id=" . $this->user_id . ")";
		}
		if (!empty($param['status_id'])) {
			if ($param['status_id'] == 'N') {
				$isWhere = $isWhere .  "  and a.is_posting=0";
			} else {
				$isWhere = $isWhere .  "  and a.is_posting=1";
			}
		}
		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}

	function index_get($id = '')
	{
		$retrieve = $this->InvPenerimaanPoModel->retrieve_by_id($id);
		$retrieve['detail'] = $this->InvPenerimaanPoModel->retrieve_detail($id);


		if (!empty($retrieve)) {
			$retrieve['file_info']         = get_file_info($this->get_path_file($retrieve['upload_file']));
			$retrieve['file_info']['mime'] = get_mime_by_extension($this->get_path_file($retrieve['upload_file']));

			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}


	function getAll_get()
	{

		$retrieve = $this->InvPenerimaanPoModel->retrieve_all();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getTraksi_get()
	{

		$retrieve = $this->InvPenerimaanPoModel->retrieve_traksi();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAllDetail_get($id = '')
	{
		$retrieve = $this->InvPenerimaanPoModel->retrieve_all_detail($id);

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
		$input['no_transaksi'] = $this->autonumber->inv_penerimaan_po($input['lokasi_id']['id'], $input['tanggal']);

		//$input['no_spat'] = $this->getLastNumber('pks_pengolahan_ht', 'no_spat', 'SPAT');
		// var_dump($input);
		$res = $this->InvPenerimaanPoModel->create($input);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'inv_penerimaan_po', 'action' => 'new', 'entity_id' => $res);
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

		$res = $this->InvPenerimaanPoModel->update($id, $data);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'inv_penerimaan_po', 'action' => 'edit', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function upload_post($segment_3 = '')
	{
		$id = (int)$segment_3;

		$inv_penerimaan_po = $this->InvPenerimaanPoModel->retrieve_by_id($id);

		// $this->set_response(['status' => 'OK', 'debug'=>$this->post()], REST_Controller::HTTP_CREATED);

		if (empty($inv_penerimaan_po)) {
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
		$config['file_name']     = url_title('SJ_' . $inv_penerimaan_po['no_transaksi'] . '_' . time(), '_', TRUE);
		$this->upload->initialize($config);
		$error_upload = array();

		if ($this->upload->do_upload()) {
			$upload_data = $this->upload->data();
			$error_upload = $this->upload->display_errors();
		} else {
			$upload_data['file_name'] = $inv_penerimaan_po['upload_file'];
			$error_upload = $this->upload->display_errors();
		}
		$file = $upload_data['file_name'];
		$input = $this->post();
		$inv_penerimaan_po_update = $this->InvPenerimaanPoModel->save_upload($inv_penerimaan_po, $file);

		if ($inv_penerimaan_po_update) {
			$message = [
				'status' => "OK",
				'id' => $inv_penerimaan_po['id'],
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
		$penerimaan = $this->InvPenerimaanPoModel->retrieve_by_id($id);
		if (!empty($penerimaan['upload_file'])) {
			$target_file = $this->get_path_file($penerimaan['upload_file']);
			if (!is_file($target_file)) {
				show_error("Maaf file tidak ditemukan." . $target_file);
			}

			$data_file = file_get_contents($target_file); // Read the file's contents
			$name_file = $penerimaan['upload_file'];

			force_download($name_file, $data_file);
		}
	}
	function get_path_file($file = '')
	{
		//  return './'.USERFILES.'/files/'.$file;
		return	$_SERVER['DOCUMENT_ROOT'] . "/" . 'hcis_folder' . "/userfiles/files/" . $file;
	}
	public function index_delete($id)
	{

		$res = $this->InvPenerimaanPoModel->delete($id);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id' => $id)), 'entity' => 'inv_penerimaan_po', 'action' => 'delete', 'entity_id' => $id);
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
		d.nama as gudang,
		f.no_po as no_po,
		e.nama as lokasi,g.nama_supplier
		FROM inv_penerimaan_po_ht a 
		INNER JOIN gbm_organisasi d ON a.gudang_id=d.id
		INNER JOIN gbm_organisasi e ON a.lokasi_id=e.id
		left JOIN prc_po_ht f ON a.po_id=f.id
		left join gbm_supplier g on f.supplier_id=g.id
		WHERE a.id=" . $id . "";
		$dataHeader = $this->db->query($queryHeader)->row_array();

		$queryDetail = "SELECT a.*,
		b.kode as kode_barang,
		b.nama as nama_barang,
		f.nama as uom
		FROM inv_penerimaan_po_dt a 
		inner join inv_item b on a.item_id=b.id 
		left join gbm_uom f on b.uom_id=f.id 
		WHERE  a.penerimaan_po_hd_id = " . $id . "";
		$dataDetail = $this->db->query($queryDetail)->result_array();



		// $perminta = $this->InvPermintaanBarangModel->print_slip($id);
		$data['header'] = 	$dataHeader;
		$data['detail'] = 	$dataDetail;

		$html = $this->load->view('InvSlipPenerimaanPo', $data, true);

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		echo $html;
	}

	function posting_post($segment_3 = null)
	{
		$id = (int)$segment_3;
		$data = $this->post();

		$retrieve_akun_kredit = $this->db->query("SELECT * FROM acc_auto_jurnal
		 where kode='PENERIMAAN_BARANG_PO'")->row_array();
		$akun_kredit =	$retrieve_akun_kredit['acc_akun_id'];
		$retrieve_header = $this->InvPenerimaanPoModel->retrieve_by_id($id);
		$retrieve_detail = $this->InvPenerimaanPoModel->retrieve_detail($id);
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
		
		$retrieve_po = $this->PrcPoModel->retrieve($retrieve_header['po_id']);
	
		// Cek apakah Barangnya tipe SOLAR (hanya solar yg biaya kirimnya dibebankan ke persediaan//
		$is_solar = false;
		foreach ($retrieve_detail as $key => $value) {
			if ($value['tipe_produk'] == 'SOLAR') {
				$is_solar = true;
			}
		}

		$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'INV_PENERIMAAN_PO');
		// Data HEADER
		$this->load->library('Autonumber');
		$no_jurnal = $this->autonumber->jurnal_auto($retrieve_header['lokasi_id'], $retrieve_header['tanggal'], 'INVPO');

		$dataH = array(
			'no_jurnal' => $no_jurnal,
			'lokasi_id' => $retrieve_header['lokasi_id'],
			'tanggal' => $retrieve_header['tanggal'],
			'no_ref' => $retrieve_header['no_transaksi'],
			'ref_id' => $retrieve_header['id'],
			'tipe_jurnal' => 'AUTO',
			'modul' => 'INV_PENERIMAAN_PO',
			'keterangan' => 'PENERIMAAN_PO',
			'is_posting' => 1,
		);
		$id_header = $this->AccJurnalModel->create_header($dataH);
		// $ppn_nilai=($retrieve_header['ppn']/100) *  ($retrieve_header['harga_satuan'] * $retrieve_header['qty']);
		// $pph_nilai=($retrieve_header['pph']/100) *  ($retrieve_header['harga_satuan'] * $retrieve_header['qty']);
		// // Data DEBET
		$total_nilai = 0;
		if ($is_solar == false) { // Jika barang tipe_produknya bukan SOLAR maka PPKB dan Ongkir TIDAK dimasukan sbg persediaan //
			foreach ($retrieve_detail as $key => $value) {
				$nilai = (($value['harga'] * $value['qty']) - $value['diskon']);
				$total_nilai =	$total_nilai + $nilai;
				$dataDt = array(
					'lokasi_id' => $retrieve_header['lokasi_id'],
					'jurnal_id' => $id_header,
					'acc_akun_id' => ($value['acc_akun_id']), //akun 
					'debet' => $nilai,
					'kredit' => 0,
					'ket' => 'Penerimaan PO:' . $retrieve_po['no_po'] . ' Item:' . $value['nama_barang'] . ', qty:' . $value['qty'] . ' ' . $value['uom'],
					'no_referensi' => $retrieve_header['no_transaksi'],
					'referensi_id' => NULL,
					'blok_stasiun_id' => NULL,
					'kegiatan_id' => NULL, //kegiatan ,
					'kendaraan_mesin_id' => NULL
				);

				$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataDt);
			}
			$dataKredit = array(
				'lokasi_id' => $retrieve_header['lokasi_id'],
				'jurnal_id' => $id_header,
				'acc_akun_id' => $akun_kredit, //akun ,
				'debet' => 0,
				'kredit' => $total_nilai,
				'ket' => 'INV_PENERIMAAN_PO',
				'no_referensi' => $retrieve_header['no_transaksi'],
				'referensi_id' => NULL,
				'blok_stasiun_id' => NULL,
				'kegiatan_id' => NULL,
				'kendaraan_mesin_id' => NULL
			);
			$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataKredit);
		} else {
			// Jika barang tipe_produknya SOLAR maka PPKB dan Ongkir dimasukan sbg persediaan //
			$ppbkb_persen = $retrieve_po['ppbkb'];
			$ppbkb_nilai = $ppbkb_persen / 100 * $retrieve_po['sub_total'];
			$biaya_kirim = $retrieve_po['biaya_kirim'];
			foreach ($retrieve_detail as $key => $value) {

				// Cari qty di PO Detail nya ///	
				$po_dt =	$this->db->query("select * from prc_po_dt where id=" . $value['po_dt_id'])->row_array();
				$qty_po_dt = $po_dt['qty'];
				$ppbkb_nilai_proporsi =  ($value['qty'] / $qty_po_dt) *$ppbkb_nilai;
				$biaya_kirim_proporsi =($value['qty'] / $qty_po_dt)*$biaya_kirim;


				$nilai = (($value['harga'] * $value['qty']) - $value['diskon'])+$ppbkb_nilai_proporsi+	$biaya_kirim_proporsi;
				$total_nilai =	$total_nilai + $nilai;
				$dataDt = array(
					'lokasi_id' => $retrieve_header['lokasi_id'],
					'jurnal_id' => $id_header,
					'acc_akun_id' => ($value['acc_akun_id']), //akun 
					'debet' => $nilai,
					'kredit' => 0,
					'ket' => 'Penerimaan PO:' . $retrieve_po['no_po'] . ' Item:' . $value['nama_barang'] . ', qty:' . $value['qty'] . ' ' . $value['uom'],
					'no_referensi' => $retrieve_header['no_transaksi'],
					'referensi_id' => NULL,
					'blok_stasiun_id' => NULL,
					'kegiatan_id' => NULL, //kegiatan ,
					'kendaraan_mesin_id' => NULL
				);
				$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataDt);
				// Kredit Penerimaan GX persediaan
				$dataKredit = array(
					'lokasi_id' => $retrieve_header['lokasi_id'],
					'jurnal_id' => $id_header,
					'acc_akun_id' => $akun_kredit, //akun ,
					'debet' => 0,
					'kredit' => (($value['harga'] * $value['qty']) - $value['diskon']),
					'ket' => 'INV_PENERIMAAN_PO (Persediaan)',
					'no_referensi' => $retrieve_header['no_transaksi'],
					'referensi_id' => NULL,
					'blok_stasiun_id' => NULL,
					'kegiatan_id' => NULL,
					'kendaraan_mesin_id' => NULL
				);
				$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataKredit);

				// Kredit Penerimaan GX (PPBKB)
				$dataKredit = array(
					'lokasi_id' => $retrieve_header['lokasi_id'],
					'jurnal_id' => $id_header,
					'acc_akun_id' => $akun_kredit, //akun ,
					'debet' => 0,
					'kredit' => $ppbkb_nilai_proporsi,
					'ket' => 'INV_PENERIMAAN_PO (PPBKB)',
					'no_referensi' => $retrieve_header['no_transaksi'],
					'referensi_id' => NULL,
					'blok_stasiun_id' => NULL,
					'kegiatan_id' => NULL,
					'kendaraan_mesin_id' => NULL
				);
				$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataKredit);
				
				// Kredit Penerimaan GX (Biaya Kirim)
				$dataKredit = array(
					'lokasi_id' => $retrieve_header['lokasi_id'],
					'jurnal_id' => $id_header,
					'acc_akun_id' => $akun_kredit, //akun , dialihkan ke akun persediaan
					'debet' => 0,
					'kredit' =>$biaya_kirim_proporsi,
					'ket' => 'INV_PENERIMAAN_PO (Biaya Kirim)',
					'no_referensi' => $retrieve_header['no_transaksi'],
					'referensi_id' => NULL,
					'blok_stasiun_id' => NULL,
					'kegiatan_id' => NULL,
					'kendaraan_mesin_id' => NULL
				);
				$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataKredit);

			}
		}

		$data['diposting_oleh'] = $this->user_id;
		$data['tanggal'] =	$retrieve_header['tanggal'];
		$data['ppbkb_persen'] =$ppbkb_persen;
		$data['ppbkb_nilai'] =	$ppbkb_nilai ;
		$data['biaya_kirim'] =	$biaya_kirim;
		$data['is_solar'] =	$is_solar;
		$res = $this->InvPenerimaanPoModel->posting($id, $data);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id' => $id)), 'entity' => 'inv_penerimaan_po', 'action' => 'posting', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function laporan_po_detail_post()
	{
		$format_laporan =  $this->post('format_laporan', true);
		// $id = (int)$segment_3;
		$data = [];

		// $input = [
		// 	'no_po'=> '01/PO/DPA',
		// 	'lokasi_id'=> 252,
		// 	'supplier_id'=> 4,
		// 	'tgl_mulai'=> '2020-01-01',
		// 	'tgl_akhir'=> '2022-04-14',
		// ];

		$no_po = $this->post('no_po', true);
		$lokasi_id = $this->post('lokasi_id', true);
		$supplier_id = $this->post('supplier_id', true);
		$tanggal_awal = $this->post('tgl_mulai', true);
		$tanggal_akhir = $this->post('tgl_akhir', true);

		// $no_po=$input['no_po'];
		// $lokasi_id=$input['lokasi_id'];
		// $supplier_id=$input['supplier_id'];
		// $tanggal_awal=$input['tgl_mulai'];
		// $tanggal_akhir=$input['tgl_akhir'];

		$queryPo = "SELECT 
		e.nama_supplier,
		b.no_transaksi,
		b.no_surat_jalan_supplier,
		h.no_po,
		d.nama as uom,
		a.qty as qty,
		c.nama as item,
		c.kode as kode,
		b.tanggal as tanggal,
		b.tanggal as tanggal_po,
		a.id AS id
		
		FROM inv_penerimaan_po_dt a
		
		INNER JOIN inv_penerimaan_po_ht b on a.penerimaan_po_hd_id=b.id
		INNER JOIN inv_item c on a.item_id=c.id
		INNER JOIN gbm_supplier e on b.supplier_id=e.id
		LEFT JOIN gbm_uom d on c.uom_id=d.id
		-- LEFT JOIN acc_mata_uang f on b.mata_uang_id=f.id
        
        LEFT JOIN prc_po_ht h on b.po_id=h.id
		-- LEFT JOIN prc_po_dt g on h.po_hd_id=g.id

		where b.tanggal between  '" . $tanggal_awal . "' and  '" . $tanggal_akhir . "'	
		";

		if (!empty($no_po)) {
			$queryPo = $queryPo . "and h.no_po LIKE '%" . $no_po . "%' ";
		}

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

		$data['po'] = 	$dataPo;
		$data['filter_supplier'] = 	$filter_supplier;
		$data['filter_lokasi'] = 	$filter_lokasi;
		$data['filter_tgl_awal'] = 	$tanggal_awal;
		$data['filter_tgl_akhir'] = $tanggal_akhir;
		$data['format_laporan'] = $format_laporan;

		$html = $this->load->view('Inv_Penerimaan_Po_Laporan', $data, true);

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
