<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use LDAP\Result;
use Restserver\Libraries\REST_Controller;

class KlnInvoiceRawatJalan extends  BD_Controller
{
	public $user_id;
	public $theCredential;

	function __construct()
	{
		parent::__construct();
		$this->load->model('KlnInvoiceRawatJalanModel');
		$this->load->model('M_DatatablesModel');
		$this->load->model('AccJurnalModel');
		$this->load->model('InvItemModel');
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


		$query  = "SELECT a.*,
		b.nama AS nama_pasien,
		c.nama AS nama_dokter
		from kln_invoice_rawat_jalan_ht a
		left join kln_pasien b on a.pasien_id=b.id
		LEFT JOIN karyawan c ON a.dokter_id = c.id
		";
		$search = array('a.tanggal',  'a.no_transaksi', 'sub_total');
		$where  = null;

		$isWhere = null;
		$isWhere = " 1=1 ";
		// if ($param['tgl_mulai'] && $param['tgl_mulai']) {
		// 	$isWhere = " a.tanggal between '" . $param['tgl_mulai'] . "' and '" . $param['tgl_akhir'] . "'";
		// }

		if (!empty($param['rawat_jalan_id'])) {

			$isWhere = $isWhere .  "  and a.rawat_jalan_id=" . $param['rawat_jalan_id'] . "";
		}

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	function index_get($segment_3 = '')
	{
		$id = $segment_3;
		$retrieve = $this->KlnInvoiceRawatJalanModel->retrieve($id);
		$retrieve['detail'] = $this->KlnInvoiceRawatJalanModel->retrieve_detail($id);
		$retrieve['pembayaran'] = $this->KlnInvoiceRawatJalanModel->retrieve_detail_pembayaran($id);
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAll_get()
	{

		$retrieve = $this->KlnInvoiceRawatJalanModel->retrieve_all_kategori();

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
		$input['no_transaksi'] = $this->autonumber->rawat_jalan_invoice($input['lokasi_id']['id'], $input['tanggal']);

		$res =  $this->KlnInvoiceRawatJalanModel->create($input);
		// $this->set_response(array("status" => "OK", "data" => $input['no_transaksi']), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'KlnInvoiceRawatJalan', 'action' => 'new', 'entity_id' => $res);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $input['no_transaksi']), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function index_put($segment_3 = '')
	{
		$input = $this->put();
		$input['diubah_oleh'] = $this->user_id;

		$id = (int)$segment_3;
		$kategori = $this->KlnInvoiceRawatJalanModel->retrieve($id);
		if (empty($kategori)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$res =   $this->KlnInvoiceRawatJalanModel->update($kategori['id'], $input);
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'KlnInvoiceRawatJalan', 'action' => 'edit', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $input['no_transaksi']), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function index_delete($segment_3 = '')
	{

		$id = (int)$segment_3;
		$kategori = $this->KlnInvoiceRawatJalanModel->retrieve($id);
		if (empty($kategori)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$res =  $this->KlnInvoiceRawatJalanModel->delete($kategori['id']);
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id' => $id)), 'entity' => 'KlnInvoiceRawatJalan', 'action' => 'delete', 'entity_id' => $id);
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

		$retrieve_header = $this->KlnInvoiceRawatJalanModel->retrieve($id);
		// CEK PERIODE SDH ADA ATAU SDH CLOSE//
		$chk = cek_periode($retrieve_header['tanggal'], $retrieve_header['lokasi_id']);
		if ($chk['status'] == false) {
			$this->set_response(array("status" => "NOT OK", "data" => $chk['message']), REST_Controller::HTTP_OK);
			return;
		}
		//==============//

		if (empty($retrieve_header)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Untuk diposting"), REST_Controller::HTTP_OK);
			return;
		} else {
			if ($retrieve_header['is_posting'] == 1) {
				$this->set_response(array("status" => "NOT OK", "data" => "Data Sudah diposting"), REST_Controller::HTTP_OK);
				return;
			}
		}
		$retrieve_detail = $this->KlnInvoiceRawatJalanModel->retrieve_detail($id);

		/* cek stok */
		$ada_stok_minus = false;
		$ada_kegiatan_kosong = false;
		$result_stok = array();
		foreach ($retrieve_detail as $key => $value) {
			if ($value['qty'] < 0) {
				$stok = $this->InvItemModel->getStok($value['item_id'], $retrieve_header['gudang_id']);
				$cek = $stok - ($value['qty'] * -1);
				if ($cek < 0) {
					$ada_stok_minus = true;
					$item = array('kode' => $value['kode_barang'], 'nama' => $value['nama_barang'], 'stok' => $cek);
					$result_stok[] = $item;
				}
			}
		}
		if ($ada_stok_minus) {
			$this->set_response(array("status" => "NOT OK", "data" => $result_stok), REST_Controller::HTTP_OK);
			return;
		}

		$retrieve_akun = $this->db->query("SELECT * FROM acc_auto_jurnal
		 where kode='KlnInvoiceRawatJalanUSTMENT_STOK'")->row_array();
		if (empty($retrieve_akun)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Setting Auto Jurnal belum ada"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$akun_kredit =	$retrieve_akun['acc_akun_id_kredit'];
		$akun_debet =	$retrieve_akun['acc_akun_id_debet'];
		// $this->set_response(array("status" => "NOT OK", "data" => $retrieve_akun), REST_Controller::HTTP_NOT_FOUND);
		// return;
		$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'KlnInvoiceRawatJalan_STOK');
		// Data HEADER
		$this->load->library('Autonumber');
		$no_jurnal = $this->autonumber->jurnal_auto($retrieve_header['lokasi_id'], $retrieve_header['tanggal'], 'KlnInvoiceRawatJalan');

		$dataH = array(
			'no_jurnal' => $no_jurnal,
			'lokasi_id' => $retrieve_header['lokasi_id'],
			'tanggal' => $retrieve_header['tanggal'],
			'no_ref' => $retrieve_header['no_transaksi'],
			'ref_id' => $retrieve_header['id'],
			'tipe_jurnal' => 'AUTO',
			'modul' => 'KlnInvoiceRawatJalan_STOK',
			'keterangan' => 'KlnInvoiceRawatJalan_STOK',
			'is_posting' => 1,
		);
		$id_header = $this->AccJurnalModel->create_header($dataH);


		$total_nilai = 0;
		foreach ($retrieve_detail as $key => $value) {
			$nilai = (($value['harga'] * $value['qty']));
			$total_nilai = $total_nilai + $nilai;
			if ($value['qty'] > 0) {

				$dataDebet = array(
					'lokasi_id' => $retrieve_header['lokasi_id'],
					'jurnal_id' => $id_header,
					'acc_akun_id' => ($value['acc_akun_id']), //akun 
					'debet' => $nilai,
					'kredit' => 0,
					'ket' => 'Adjustment Stok :' . $value['nama_barang'] . ', qty:' . $value['qty'] . ' ' . $value['uom'],
					'no_referensi' => $retrieve_header['no_transaksi'],
					'referensi_id' => NULL,
					'blok_stasiun_id' => NULL,
					'kegiatan_id' => NULL, //kegiatan ,
					'kendaraan_mesin_id' => NULL
				);

				$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataDebet);
				$dataKredit = array(
					'lokasi_id' => $retrieve_header['lokasi_id'],
					'jurnal_id' => $id_header,
					'acc_akun_id' => $akun_kredit, //akun ,
					'debet' => 0,
					'kredit' => $nilai,
					'ket' => 'Adjustment Stok :' . $value['nama_barang'] . ', qty:' . $value['qty'] . ' ' . $value['uom'],
					'no_referensi' => $retrieve_header['no_transaksi'],
					'referensi_id' => NULL,
					'blok_stasiun_id' => NULL,
					'kegiatan_id' => NULL,
					'kendaraan_mesin_id' => NULL
				);
				$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataKredit);
			} else {

				$dataDebet = array(
					'lokasi_id' => $retrieve_header['lokasi_id'],
					'jurnal_id' => $id_header,
					'acc_akun_id' => $akun_debet, //akun 
					'debet' => ($nilai * -1),
					'kredit' => 0,
					'ket' => 'Adjustment Stok :' . $value['nama_barang'] . ', qty:' . $value['qty'] . ' ' . $value['uom'],
					'no_referensi' => $retrieve_header['no_transaksi'],
					'referensi_id' => NULL,
					'blok_stasiun_id' => NULL,
					'kegiatan_id' => NULL, //kegiatan ,
					'kendaraan_mesin_id' => NULL
				);

				$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataDebet);
				$dataKredit = array(
					'lokasi_id' => $retrieve_header['lokasi_id'],
					'jurnal_id' => $id_header,
					'acc_akun_id' => $value['acc_akun_id'], //akun ,
					'debet' => 0,
					'kredit' => ($nilai * -1),
					'ket' => 'Adjustment Stok :' . $value['nama_barang'] . ', qty:' . $value['qty'] . ' ' . $value['uom'],
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
		$res = $this->KlnInvoiceRawatJalanModel->posting($id, $data);
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

		$queryHeader = "SELECT a.*,b.no_transaksi AS no_rawat_inap,c.nama AS nama_pasien,d.nama AS nama_dokter 
		FROM kln_invoice_rawat_jalan_ht a 
		left JOIN kln_rawat_jalan b ON a.rawat_jalan_id=b.id 
		left JOIN kln_pasien c ON b.pasien_id =c.id 
		left JOIN karyawan d ON b.dokter_id=d.id
		WHERE a.id=" . $id . "";
		$dataHeader = $this->db->query($queryHeader)->row_array();

		$queryDetail = "SELECT a.*,
		b.kode as kode_biaya,
		b.nama as nama_biaya,
		c.nama as uom
		FROM kln_invoice_rawat_jalan_dt a 
		inner join acc_kegiatan b ON a.biaya_id=b.id 
		left join gbm_uom c on b.uom_id=c.id 
		WHERE  a.invoice_id = " . $id . "";
		$dataDetail = $this->db->query($queryDetail)->result_array();

		$queryPembayaran = "	SELECT a.*,b.nama,b.jenis from kln_invoice_rawat_jalan_bayar a 
		INNER JOIN kln_tipe_bayar b ON a.tipe_bayar_id=b.id
		WHERE  a.invoice_id = " . $id . "";
		$dataPembayaran = $this->db->query($queryPembayaran)->result_array();


		$data['hd'] = 	$dataHeader;
		$data['dt'] = 	$dataDetail;
		$data['bayar'] = 	$dataPembayaran;

		$html = $this->load->view('KlnSlipInvoiceRawatInap', $data, true);

		$filename = 'report_' . time();
		$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		// echo $html;
	}




	function laporan_invoice_post()
	{
		$jenis_laporan = $this->post('jenis_laporan', true);
		if ($jenis_laporan == 'detail') {
			$this->laporan_detail();
		} else if ($jenis_laporan == 'by_jenis_biaya') {
			$this->laporan_detail_by_jenis_biaya();
		} else if ($jenis_laporan == 'by_jenis_pembayaran') {
			$this->laporan_detail_by_tipe_bayar();
		} else if ($jenis_laporan == 'detail') {
		} else if ($jenis_laporan == 'detail') {
		}
	}
	function laporan_detail()
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

		// $lokasi_id = $this->post('lokasi_id', true);
		// $gudang_id = $this->post('gudang_id', true);
		$tanggal_awal = $this->post('tgl_mulai', true);
		$tanggal_akhir = $this->post('tgl_akhir', true);

		// $lokasi_id = $input['lokasi_id'];
		// $gudang_id = $input['gudang_id'];
		// $tanggal_awal = $input['tgl_mulai'];
		// $tanggal_akhir = $input['tgl_akhir'];
		$result = array();
		$result = [];
		$queryHd = "SELECT a.*,e.nip AS no_pasien,e.nama AS nama_pasien,
		f.nip AS nip_dokter,f.nama AS nama_dokter,g.nama AS nama_poli from kln_invoice_rawat_jalan_ht a 
		INNER JOIN kln_rawat_jalan d ON a.rawat_jalan_id=d.id
		INNER JOIN kln_pasien e ON d.pasien_id=e.id
		INNER JOIN karyawan f ON d.dokter_id=f.id
		INNER JOIN kln_poli g ON d.poli_id=g.id
		where a.tanggal between  '" . $tanggal_awal . "' and  '" . $tanggal_akhir . "'	
		order by a.no_transaksi";
		$dataHd = $this->db->query($queryHd)->result_array();

		foreach ($dataHd  as $key => $value) {
			$queryDt = "SELECT a.* , c.kode as kode_biaya,c.nama as nama_biaya,b.qty,b.harga,b.ket,d.no_transaksi AS no_rj,e.nip AS no_pasien,e.nama AS nama_pasien,
			f.nip AS nip_dokter,f.nama AS nama_dokter,g.nama AS nama_poli from kln_invoice_rawat_jalan_ht a 
			INNER JOIN kln_invoice_rawat_jalan_dt b ON a.id=b.invoice_id
			INNER JOIN acc_kegiatan c ON b.biaya_id=c.id
			INNER JOIN kln_rawat_jalan d ON a.rawat_jalan_id=d.id
			INNER JOIN kln_pasien e ON d.pasien_id=e.id
			INNER JOIN karyawan f ON d.dokter_id=f.id
			INNER JOIN kln_poli g ON d.poli_id=g.id		
			where a.id=" . $value['id'] . "
			";
			$dataDt = $this->db->query($queryDt)->result_array();
			$ht = array();
			$ht = [];
			$ht = $value;
			$ht['dt'] = $dataDt;
			$result[] = $ht;
		}







		$data['invoice'] = 	$result;
		// $data['filter_gudang'] = 	$filter_gudang;
		// $data['filter_lokasi'] = 	$filter_lokasi;
		$data['format_laporan'] = 	$format_laporan;
		$data['filter_tgl_awal'] = 	$tanggal_awal;
		$data['filter_tgl_akhir'] = $tanggal_akhir;

		$html = $this->load->view('KlnInvoiceRawatJalanDetail', $data, true);

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
	function laporan_detail_by_jenis_biaya()
	{
		$format_laporan =  $this->post('format_laporan', true);

		// $id = (int)$segment_3;
		$data = [];


		$tanggal_awal = $this->post('tgl_mulai', true);
		$tanggal_akhir = $this->post('tgl_akhir', true);

		$result = array();
		$result = [];
		$queryHd = "SELECT Distinct c.id,c.kode as kode_biaya,c.nama as nama_biaya  from kln_invoice_rawat_jalan_ht a 
			INNER JOIN kln_invoice_rawat_jalan_dt b ON a.id=b.invoice_id
			INNER JOIN acc_kegiatan c ON b.biaya_id=c.id
			INNER JOIN kln_rawat_jalan d ON a.rawat_jalan_id=d.id
			INNER JOIN kln_pasien e ON d.pasien_id=e.id
			INNER JOIN karyawan f ON d.dokter_id=f.id
			INNER JOIN kln_poli g ON d.poli_id=g.id	
			where a.tanggal between  '" . $tanggal_awal . "' and  '" . $tanggal_akhir . "'	
			order by c.nama";
		$dataHd = $this->db->query($queryHd)->result_array();

		foreach ($dataHd  as $key => $value) {
			$queryDt = "SELECT a.* , c.kode as kode_biaya,c.nama as nama_biaya,b.qty,b.harga,b.ket,d.no_transaksi AS no_rj,e.nip AS no_pasien,e.nama AS nama_pasien,
			f.nip AS nip_dokter,f.nama AS nama_dokter,g.nama AS nama_poli from kln_invoice_rawat_jalan_ht a 
			INNER JOIN kln_invoice_rawat_jalan_dt b ON a.id=b.invoice_id
			INNER JOIN acc_kegiatan c ON b.biaya_id=c.id
			INNER JOIN kln_rawat_jalan d ON a.rawat_jalan_id=d.id
			INNER JOIN kln_pasien e ON d.pasien_id=e.id
			INNER JOIN karyawan f ON d.dokter_id=f.id
			INNER JOIN kln_poli g ON d.poli_id=g.id		
			where c.id=" . $value['id'] . "
			";
			$dataDt = $this->db->query($queryDt)->result_array();
			$ht = array();
			$ht = [];
			$ht = $value;
			$ht['dt'] = $dataDt;
			$result[] = $ht;
		}







		$data['invoice'] = 	$result;
		// $data['filter_gudang'] = 	$filter_gudang;
		// $data['filter_lokasi'] = 	$filter_lokasi;
		$data['format_laporan'] = 	$format_laporan;
		$data['filter_tgl_awal'] = 	$tanggal_awal;
		$data['filter_tgl_akhir'] = $tanggal_akhir;

		$html = $this->load->view('KlnInvoiceRawatJalanDetailByBiaya', $data, true);

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
	function laporan_detail_by_tipe_bayar()
	{
		$format_laporan =  $this->post('format_laporan', true);

		// $id = (int)$segment_3;
		$data = [];


		$tanggal_awal = $this->post('tgl_mulai', true);
		$tanggal_akhir = $this->post('tgl_akhir', true);

		$result = array();
		$result = [];
		$queryHd = "SELECT distinct c.id,c.nama as jenis_bayar
		from kln_invoice_rawat_jalan_ht a 
		INNER JOIN kln_invoice_rawat_jalan_bayar b ON a.id=b.invoice_id
		INNER JOIN kln_tipe_bayar c ON b.tipe_bayar_id=c.id
		INNER JOIN kln_rawat_jalan d ON a.rawat_jalan_id=d.id
		INNER JOIN kln_pasien e ON d.pasien_id=e.id
		INNER JOIN karyawan f ON d.dokter_id=f.id
		INNER JOIN kln_poli g ON d.poli_id=g.id
			where a.tanggal between  '" . $tanggal_awal . "' and  '" . $tanggal_akhir . "'	
			order by c.nama";
		$dataHd = $this->db->query($queryHd)->result_array();

		foreach ($dataHd  as $key => $value) {
			$queryDt = "SELECT a.*,b.jumlah,b.ket,d.no_transaksi AS no_rj,c.nama AS jenis_bayar, e.nip AS no_pasien,e.nama AS nama_pasien,
				f.nip AS nip_dokter,f.nama AS nama_dokter,g.nama AS nama_poli
				from kln_invoice_rawat_jalan_ht a 
				INNER JOIN kln_invoice_rawat_jalan_bayar b ON a.id=b.invoice_id
				INNER JOIN kln_tipe_bayar c ON b.tipe_bayar_id=c.id
				INNER JOIN kln_rawat_jalan d ON a.rawat_jalan_id=d.id
				INNER JOIN kln_pasien e ON d.pasien_id=e.id
				INNER JOIN karyawan f ON d.dokter_id=f.id
				INNER JOIN kln_poli g ON d.poli_id=g.id
							where c.id=" . $value['id'] . "
			";
			$dataDt = $this->db->query($queryDt)->result_array();
			$ht = array();
			$ht = [];
			$ht = $value;
			$ht['dt'] = $dataDt;
			$result[] = $ht;
		}







		$data['invoice'] = 	$result;
		// $data['filter_gudang'] = 	$filter_gudang;
		// $data['filter_lokasi'] = 	$filter_lokasi;
		$data['format_laporan'] = 	$format_laporan;
		$data['filter_tgl_awal'] = 	$tanggal_awal;
		$data['filter_tgl_akhir'] = $tanggal_akhir;

		$html = $this->load->view('KlnInvoiceRawatJalanDetailByTipeBayar', $data, true);

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
