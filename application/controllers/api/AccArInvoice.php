<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

class AccArInvoice extends BD_Controller //Rest_Controller
{
	public $user_id;
	public $theCredential;
	public $data = array();
	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('GbmOrganisasiModel');
		$this->load->model('AccArInvoiceModel');
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
		$param = $post['parameter'];
		$query  = "SELECT a.*,
		b.nama as lokasi,
		c.nama_customer,
		c.tipe_pajak,d.no_so,
		e.user_full_name AS dibuat,
		f.user_full_name AS diubah,
		g.user_full_name AS diposting FROM acc_ar_invoice_ht a 
		inner join gbm_organisasi b on a.lokasi_id=b.id
		inner join gbm_customer c on a.customer_id=c.id 
		left join sls_so_ht d on a.so_id=d.id
		LEFT JOIN fwk_users e ON a.dibuat_oleh = e.id
		LEFT JOIN fwk_users f ON a.diubah_oleh = f.id
		LEFT JOIN fwk_users g ON a.diposting_oleh = g.id  ";
		$search = array('a.no_invoice', 'a.no_ref', 'a.tanggal', 'b.nama', 'c.nama_customer', 'a.deskripsi', 'd.no_so');
		// $search ='';
		$where  = null;

		$isWhere = " 1=1 ";
		if ($param['tgl_mulai'] && $param['tgl_mulai']) {
			$isWhere = " a.tanggal between '" . $param['tgl_mulai'] . "' and '" . $param['tgl_akhir'] . "'";
		}

		if ($param['lokasi_id']) {
			$isWhere = $isWhere . " and a.lokasi_id =" . $param['lokasi_id'] . "";
		} else {
			$isWhere = $isWhere . " and  a.lokasi_id in
			(select location_id from fwk_users_location where user_id=" . $this->user_id . ")";
		}
		if (!empty($param['status_id']) ) {
			if ($param['status_id']=='N'){
				$isWhere =$isWhere .  "  and a.is_posting=0";
			}else{
				$isWhere =$isWhere .  "  and a.is_posting=1";
			}
		}

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}

	function index_get($id = '')
	{
		$retrieve = array();
		$retrieve = $this->AccArInvoiceModel->retrieve_by_id($id);
		$retrieve_detail = $this->AccArInvoiceModel->retrieve_detail($id);
		//$detail = array();
		// foreach ($retrieve_detail as $key => $value) {
		// 	$dtl = array();
		// 	$retrieve_denda = $this->AccArInvoiceModel->retrieve_detail_denda($value['id']);
		// 	$dtl = $value;
		// 	$dtl['denda'] = array();
		// 	foreach ($retrieve_denda as $key1 => $value_denda) {
		// 		$dtl['denda'][] = $value_denda;
		// 	}
		// 	$detail[] = $dtl;
		// }

		$retrieve['detail'] = $retrieve_detail;

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function getDetail_get($id = '')
	{
		$retrieve = $this->AccArInvoiceModel->retrieve_detail($id);
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
		$input['no_invoice'] = $this->autonumber->acc_ar_invoice($input['lokasi_id']['id'], $input['tanggal']);

		$res = $this->AccArInvoiceModel->create($input);
		if (!empty($res)) {
			$this->set_response(array("status" => "OK", "data" => 	$input['no_invoice']), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function index_put($segment_3 = null)
	{
		$id = (int)$segment_3;
		$data = $this->put();
		$data['diubah_oleh'] = $this->user_id;
		$data['dibuat_oleh'] = $this->user_id;
		$res = $this->AccArInvoiceModel->update($id, $data);
		if (!empty($res)) {
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function faktur_put($segment_3 = null)
	{
		$id = (int)$segment_3;
		$data = $this->put();

		$res = $this->AccArInvoiceModel->update_faktur($id, $data);

		if (!empty($res)) {
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	public function index_delete($id)
	{

		$res = $this->AccArInvoiceModel->delete($id);
		if (!empty($res)) {
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function posting_post($segment_3 = null)
	{
		$id = (int)$segment_3;

		$data = $this->post();

		$retrieve_header = $this->AccArInvoiceModel->retrieve_by_id($id);
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
		$retrieve_detail = $this->AccArInvoiceModel->retrieve_detail($id);
		$retrieve_inter_akun = $this->db->query("SELECT * FROM acc_inter_unit
		 where tipe='INTER' ")->result_array();
		$akun_inter = array();
		foreach ($retrieve_inter_akun as $key => $akun) {
			$akun_inter[$akun['lokasi_id']] = $akun['acc_akun_id'];
		}
		// Hapus jurnal jika sdh ada berdasarkan no refernsi dan MOdul  //
		$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'AR_INVOICE');
		// Data HEADER
		$this->load->library('Autonumber');
		$no_jurnal = $this->autonumber->jurnal_auto($retrieve_header['lokasi_id'], $retrieve_header['tanggal'], 'AR');
		$dataH = array(
			'no_jurnal' => $no_jurnal,
			'lokasi_id' => $retrieve_header['lokasi_id'],
			'tanggal' => $retrieve_header['tanggal'],
			'no_ref' => $retrieve_header['no_invoice'],
			'ref_id' => $retrieve_header['id'],
			'tipe_jurnal' => 'AUTO',
			'modul' => 'AR_INVOICE',
			'keterangan' => 'AR INVOICE:' . $retrieve_header['deskripsi'],
			'is_posting' => 1,
			'diposting_oleh' => $this->user_id
		);
		$id_header = $this->AccJurnalModel->create_header($dataH);
		// $ppn_nilai=($retrieve_header['ppn']/100) *  ($retrieve_header['harga_satuan'] * $retrieve_header['qty']);
		// $pph_nilai=($retrieve_header['pph']/100) *  ($retrieve_header['harga_satuan'] * $retrieve_header['qty']);
		// // Data DEBET
		$dataDebet = array(
			'lokasi_id' => $retrieve_header['lokasi_id'],
			'jurnal_id' => $id_header,
			'acc_akun_id' => $retrieve_header['akun_customer_id'], //akun ,
			'debet' => ($retrieve_header['nilai_invoice']),
			'kredit' => 0,
			'ket' => 'AR INVOICE:' . $retrieve_header['deskripsi'],
			'no_referensi' => $retrieve_header['no_invoice'],
			'referensi_id' => NULL,
			'blok_stasiun_id' => NULL,
			'kegiatan_id' => NULL,
			'kendaraan_mesin_id' => NULL
		);
		$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataDebet);
		foreach ($retrieve_detail as $key => $value) {
			//* cek beda lokasi hd vs dt //
			if ($value['lokasi_id'] == $retrieve_header['lokasi_id']) {
				$dataDt = array(
					'lokasi_id' => $retrieve_header['lokasi_id'],
					'jurnal_id' => $id_header,
					'acc_akun_id' => ($value['acc_akun_id']), //akun 
					'debet' => ($value['debet']),
					'kredit' => ($value['kredit']),
					'ket' => 'AR INVOICE:' . $value['ket'],
					'no_referensi' => $retrieve_header['no_invoice'],
					'referensi_id' => NULL,
					'blok_stasiun_id' => NULL,
					'kegiatan_id' => NULL, //kegiatan pemeliharaan,
					'kendaraan_mesin_id' => NULL
				);
				$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataDt);
			} else {
				$dataDt = array(
					'lokasi_id' => $value['lokasi_id'],
					'jurnal_id' => $id_header,
					'acc_akun_id' => ($value['acc_akun_id']), //akun 
					'debet' => ($value['debet']),
					'kredit' => ($value['kredit']),
					'ket' => 'AP INVOICE:' . $value['ket'],
					'no_referensi' => $retrieve_header['no_invoice'],
					'referensi_id' => NULL,
					'blok_stasiun_id' => NULL,
					'kegiatan_id' => NULL, //kegiatan pemeliharaan,
					'kendaraan_mesin_id' => NULL
				);
				$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataDt);
				// INTER UNIT 
				$dr_dt = 0;
				$cr_dt = 0;
				if ($value['debet'] > 0) {
					$dr_dt = 0;
					$cr_dt = $value['debet'];
				}
				if ($value['kredit'] > 0) {
					$dr_dt = $value['kredit'];
					$cr_dt = 0;
				}
				$dataDt = array(
					'lokasi_id' => $value['lokasi_id'],
					'jurnal_id' => $id_header,
					'acc_akun_id' => $akun_inter[$value['lokasi_id']], // inter unit akun lokasi Dt 
					'debet' => $dr_dt,
					'kredit' => $cr_dt,
					'ket' => 'AR INVOICE:' . $value['ket'],
					'no_referensi' => $retrieve_header['no_invoice'],
					'referensi_id' => NULL,
					'blok_stasiun_id' => NULL,
					'kegiatan_id' => NULL, //kegiatan pemeliharaan,
					'kendaraan_mesin_id' => NULL
				);
				$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataDt);

				$dataDt = array(
					'lokasi_id' => $retrieve_header['lokasi_id'],
					'jurnal_id' => $id_header,
					'acc_akun_id' => $akun_inter[$retrieve_header['lokasi_id']], // inter unit akun lokasi Hd 
					'debet' => $cr_dt, // kebalikan dt
					'kredit' => $dr_dt, // kebalikan dt
					'ket' => 'AR INVOICE:' . $value['ket'],
					'no_referensi' => $retrieve_header['no_invoice'],
					'referensi_id' => NULL,
					'blok_stasiun_id' => NULL,
					'kegiatan_id' => NULL, //kegiatan pemeliharaan,
					'kendaraan_mesin_id' => NULL
				);
				$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataDt);
			}
		}


		$data['diposting_oleh'] = $this->user_id;
		$res = $this->AccArInvoiceModel->posting($id, $data);
		if (!empty($res)) {
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAllOutstandingInvoice_get()
	{

		$res = $this->db->query("SELECT a.*,a.id as invoice_id, b.nama as lokasi,
			c.nama_customer,c.acc_akun_id as akun_customer_id,(a.nilai_invoice-a.nilai_dibayar) as sisa
			 FROM `acc_ar_invoice_ht` a inner join gbm_organisasi b on a.lokasi_id=b.id
			inner join gbm_customer c on a.customer_id=c.id left join sls_so_ht d on a.so_id=d.id
			where a.nilai_invoice-a.nilai_dibayar >0 
			and a.is_posting=1")->result_array();
		if (!empty($res)) {
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function print_slip_get($segment_3 = '')
	{

		$id = (int)$segment_3;
		$data = [];

		$hd = $this->db->query("select a.*,c.nama_customer, b.kode as kode_akun,b.nama as nama_akun from acc_ar_invoice_ht a left join
		acc_akun b on a.akun_customer_id=b.id left join gbm_customer c
		on a.customer_id=c.id where a.id=" . $id)->row_array();
		$data['hd'] = $hd;

		$dt = $this->db->query("select a.*, b.kode as kode_akun,b.nama as nama_akun from acc_ar_invoice_dt a left join
		acc_akun b on a.acc_akun_id=b.id where a.invoice_id=" . $id)->result_array();
		$data['dt'] = $dt;

		$html = $this->load->view('AccSlipArInvoice', $data, true);

		$filename = 'AccSlipApInvoice_' . time();
		$this->pdfgenerator->generate($html, $filename, true, 'A4', 'portrait');
		echo $html;
	}
	function print_slip_2_get($segment_3 = '')
	{

		$id = (int)$segment_3;
		$data = [];

		$hd = $this->db->query("select a.*,c.nama_customer, b.kode as kode_akun,b.nama as nama_akun from acc_ar_invoice_ht a left join
		acc_akun b on a.akun_customer_id=b.id left join gbm_customer c
		on a.customer_id=c.id where a.id=" . $id)->row_array();
		$data['hd'] = $hd;

		$dt = $this->db->query("select a.*, b.kode as kode_akun,b.nama as nama_akun from acc_ar_invoice_dt a left join
		acc_akun b on a.acc_akun_id=b.id where a.invoice_id=" . $id)->result_array();
		$data['dt'] = $dt;

		$so = $this->db->query("SELECT 
		a.*,
		a.id AS id
		FROM 
		sls_so_ht a
		WHERE id=" . $hd['so_id'])->row_array();
		$data['so'] = $so;

		$sodt = $this->db->query("SELECT
		a.*,
		b.nama AS item_nama,
		bb.nama AS nama_uom,
		c.no_so AS no_so,
		a.id AS id
		FROM sls_so_dt a
		LEFT JOIN inv_item b ON a.item_id=b.id
		LEFT JOIN gbm_uom bb ON b.uom_id=bb.id
		LEFT JOIN sls_so_ht c ON a.so_hd_id=c.id
		WHERE a.so_hd_id=" . $so['id'])->result_array();
		$data['sodt'] = $sodt;

		$html = $this->load->view('AccSlipArInvoice2', $data, true);

		$filename = 'AccSlipArInvoice2_' . time();
		$this->pdfgenerator->generate($html, $filename, true, 'A4', 'portrait');
		echo $html;
	}

	function laporan_faktur_pajak_outstanding_get($segment_3 = '')
	{

		$id = (int)$segment_3;
		$data = [];

		$res = $this->db->query("SELECT a.*,b.nama_customer FROM acc_ar_invoice_ht a inner join gbm_customer b on a.customer_id=b.id
		where tipe_pajak='PKP' and (no_faktur_pajak='' or no_faktur_pajak is null)")->result_array();
		$data['ap'] = $res;


		$html = $this->load->view('AccApFakturPajakOutstanding', $data, true);

		// $filename = 'AccApFakturPajakOutstanding_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'portrait');
		echo $html;
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
	function laporan_ar_post()
	{
		$versi_laporan = $this->post('versi_laporan', true);
		if ($versi_laporan == 'v1') {
			$this->laporan_ar_detail();
		} else if ($versi_laporan == 'v2') {
			$this->laporan_ar_umur_hutang();
		}
	}
	function laporan_ar_detail()
	{
		error_reporting(0);

		$data = [];
		$lokasi_id = $this->post('lokasi_id', true);
		$customer_id = $this->post('customer_id', true);
		$tanggal_tempo = $this->post('tgl_mulai', true);
		$status = $this->post('status', true);
		$format_laporan = $this->post('format_laporan', true);

		$query = "select a.*,b.nama_customer,IFNULL( c.dibayar, 0)as dibayar,a.nilai_invoice-(IFNULL( c.dibayar, 0))as sisa   from acc_ar_invoice_ht a INNER join gbm_customer b 
		on a.customer_id=b.id left join (select ref_id, sum(nilai)as dibayar from acc_kasbank_ht group by ref_id)c
		on a.id=c.ref_id
		where a.tanggal_tempo <=  '" . $tanggal_tempo . "' 	
		";
		$filter_customer = "Semua";
		$filter_lokasi = "Semua";
		if ($lokasi_id) {
			$query = $query . " and a.lokasi_id=" . $lokasi_id . "";
			$res = $this->db->query("select * from gbm_organisasi where id=" . $lokasi_id . "")->row_array();
			$filter_lokasi = $res['nama'];
		}
		if ($customer_id) {
			$query = $query . " and a.customer_id=" . $customer_id . "";
			$res = $this->db->query("select * from gbm_customer where id=" . $customer_id . "")->row_array();
			$filter_customer = $res['nama_customer'];
		}
		if ($status == '0') {
			$filter_status = 'SEMUA';
		} else if ($status == '1') {
			$query = $query . " and a.nilai_invoice-(IFNULL( c.dibayar, 0))<=0";
			$filter_status = 'LUNAS';
		} else if ($status == '2') {
			$query = $query . " and a.nilai_invoice-(IFNULL( c.dibayar, 0))>0";
			$filter_status = 'BELUM LUNAS';
		}
		$data = $this->db->query($query)->result_array();

		$data['ap'] = 	$data;
		$data['filter_customer'] = 	$filter_customer;
		$data['filter_lokasi'] = 	$filter_lokasi;
		$data['filter_tgl_tempo'] = 	$tanggal_tempo;
		$data['filter_status'] = $filter_status;

		$html = $this->load->view('Acc_Ar_Laporan', $data, true);

		if ($format_laporan == 'xls') {
			// $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			// $spreadsheet = $reader->loadFromString($html);
			// // $reader->setSheetIndex(1);
			// //$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			// $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			// header("Pragma: public");
			// header("Expires: 0");
			// header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			// header("Content-Type: application/force-download");
			// header("Content-Type: application/octet-stream");
			// header("Content-Type: application/download");
			// header("Content-Disposition: attachment;filename=test.xlsx");
			// header("Content-Transfer-Encoding: binary ");

			// ob_end_clean();
			// ob_start();
			// $objWriter->save('php://output');
			echo $html;
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}

	}

	function laporan_ar_umur_hutang()
	{
		error_reporting(0);

		$data = [];
		$lokasi_id = $this->post('lokasi_id', true);
		$customer_id = $this->post('customer_id', true);
		$tanggal_tempo = $this->post('tgl_mulai', true);
		$status = $this->post('status', true);
		$format_laporan = $this->post('format_laporan', true);
		$umur_hutang = array('0-30'=>array(0,30),'31-60'=>array(31,60),	'61-90'=>array(61,90),'91-120'=>array(91,120),'121 - 150'=>array(121,150),'151-180'=>array(151,180),'181 - 210'=>array(181,210),'211 - 240'=>array(211,240),'241 - 270'=>array(241,270),'>271'=>array(271,10000));

		$query = "Select distinct a.customer_id,b.nama_customer  from acc_ar_invoice_ht a INNER join gbm_customer b 
		on a.customer_id=b.id left join (select invoice_id, sum(debet)as dibayar from acc_kasbank_dt group by invoice_id)c
		on a.id=c.invoice_id
		where a.tanggal_tempo <=  '" . $tanggal_tempo . "' 	
		";
		
		$filter_customer = "Semua";
		$filter_lokasi = "Semua";
		if ($lokasi_id) {
			$query = $query . " and a.lokasi_id=" . $lokasi_id . "";
			$res = $this->db->query("select * from gbm_organisasi where id=" . $lokasi_id . "")->row_array();
			$filter_lokasi = $res['nama'];
		}
		if ($customer_id) {
			$query = $query . " and a.customer_id=" . $customer_id . "";
			$res = $this->db->query("select * from gbm_customer where id=" . $customer_id . "")->row_array();
			$filter_customer = $res['nama_customer'];
		}
		if ($status == '0') {
			$filter_status = 'SEMUA';
		} else if ($status == '1') {
			$query = $query . " and a.nilai_invoice-(IFNULL( c.dibayar, 0))<=0";
			$filter_status = 'LUNAS';
		} else if ($status == '2') {
			$query = $query . " and a.nilai_invoice-(IFNULL( c.dibayar, 0))>0";
			$filter_status = 'BELUM LUNAS';
		}
		$data_customer = $this->db->query($query)->result_array();

		foreach ($data_customer as $key_supp => $supp) {
			foreach ($umur_hutang as $um => $umur) {
				$query = "select sum(a.nilai_invoice-(IFNULL( c.dibayar, 0)))as sisa  
				 from acc_ar_invoice_ht a INNER join gbm_customer b 
				on a.customer_id=b.id left join (select invoice_id, sum(debet)as dibayar from acc_kasbank_dt group by invoice_id)c
				on a.id=c.invoice_id
				where a.tanggal_tempo <=  '" . $tanggal_tempo . "' 	and a.customer_id=" . $supp['customer_id'] . "";
				if ($lokasi_id) {
					$query = $query . " and a.lokasi_id=" . $lokasi_id . "";
				}
				if ($status == '0') {					
				} else if ($status == '1') {
					$query = $query . " and a.nilai_invoice-(IFNULL( c.dibayar, 0))<=0";
				} else if ($status == '2') {
					$query = $query . " and a.nilai_invoice-(IFNULL( c.dibayar, 0))>0";
				}
				$query = $query . " and  (DATEDIFF('" . $tanggal_tempo . "',a.tanggal_tempo ) between ".$umur[0]." and ".$umur[1].")"; 
				//  echo ($query);exit();
				$res = $this->db->query($query)->row_array();
				$sisa=0;
				if ($res){
					$sisa=$res['sisa']?$res['sisa']:0;
				}
				$data_customer[$key_supp][$um]=$sisa;
			}
		}
// var_dump($data_customer);
// exit();
		$data['ap'] = 	$data_customer;
		$data['umur_hutang'] = 	$umur_hutang;
		$data['filter_customer'] = 	$filter_customer;
		$data['filter_lokasi'] = 	$filter_lokasi;
		$data['filter_tgl_tempo'] = 	$tanggal_tempo;
		$data['filter_status'] = $filter_status;

		$html = $this->load->view('Acc_AR_Laporan_Umur_Piutang', $data, true);

		if ($format_laporan == 'xls') {
			// $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			// $spreadsheet = $reader->loadFromString($html);
			// // $reader->setSheetIndex(1);
			// //$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			// $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			// header("Pragma: public");
			// header("Expires: 0");
			// header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			// header("Content-Type: application/force-download");
			// header("Content-Type: application/octet-stream");
			// header("Content-Type: application/download");
			// header("Content-Disposition: attachment;filename=test.xlsx");
			// header("Content-Transfer-Encoding: binary ");

			// ob_end_clean();
			// ob_start();
			// $objWriter->save('php://output');
			echo $html;
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
		
	}
}
