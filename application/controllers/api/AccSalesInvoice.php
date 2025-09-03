<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

class AccSalesInvoice extends BD_Controller
{
	public $user_id;
	public $theCredential;
	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('AccSalesInvoiceModel');
		$this->load->model('KaryawanModel');
		$this->load->model('M_DatatablesModel');
		$this->load->model('AccJurnalModel');
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
		b.no_spk,
		c.nama_customer as customer,
		d.user_full_name AS dibuat,
		e.user_full_name AS diubah,
		f.user_full_name AS diposting from acc_sales_invoice a 
		left join sls_kontrak b on a.sls_kontrak_id=b.id
		left join gbm_customer c on a.customer_id=c.id
		LEFT JOIN fwk_users d ON a.dibuat_oleh = d.id
		LEFT JOIN fwk_users e ON a.diubah_oleh = e.id
		LEFT JOIN fwk_users f ON a.diposting_oleh = f.id ";
		$search = array('no_invoice', 'a.tanggal', 'a.tanggal_tempo', 'a.jenis_invoice', 'c.nama_customer', 'b.no_spk');
		$where  = null;

		$isWhere = " 1=1";
		if ($param['tgl_mulai'] && $param['tgl_mulai']) {
			$isWhere = " a.tanggal between '" . $param['tgl_mulai'] . "' and '" . $param['tgl_akhir'] . "'";
		}
		// $isWhere = 'artikel.deleted_at IS NULL';

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	function index_get($segment_3 = '')
	{
		$id = $segment_3;
		$retrieve = $this->AccSalesInvoiceModel->retrieve($id);

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAll_get()
	{

		$retrieve = $this->AccSalesInvoiceModel->retrieve_all();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAkunDebetSalesInvoice_get()
	{

		$retrieve = $this->db->query("select a.*,b.kode as kode_akun,b.nama as nama_akun
		from acc_auto_jurnal a
		inner join acc_akun b on a.acc_akun_id =b.id
		where a.kode='SALES_INVOICE_DEBET'")->result_array();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAkunKreditSalesInvoice_get()
	{

		$retrieve = $this->db->query("select a.*,b.kode as kode_akun,b.nama as nama_akun
		from acc_auto_jurnal a
		inner join acc_akun b on a.acc_akun_id =b.id
		where a.kode='SALES_INVOICE_KREDIT'")->result_array();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function create_post()
	{
		$data = $this->post();
		$data['diubah_oleh'] = $this->user_id;
		$data['dibuat_oleh'] = $this->user_id;
		$retrieve =  $this->AccSalesInvoiceModel->create($data);
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
	}

	function update_post($segment_3 = '')
	{
		$id = (int)$segment_3;
		$item = $this->AccSalesInvoiceModel->retrieve($id);
		if (empty($item)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$data = $this->post();
		$data['diubah_oleh'] = $this->user_id;
		$retrieve =   $this->AccSalesInvoiceModel->update($item['id'], $data);
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
	}

	function index_delete($segment_3 = '')
	{

		$id = (int)$segment_3;
		$item = $this->AccSalesInvoiceModel->retrieve($id);
		if (empty($item)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$retrieve =  $this->AccSalesInvoiceModel->delete($item['id']);
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
	}

	function posting_post($segment_3 = null)
	{
		$id = (int)$segment_3;
		$data = $this->post();
		$retrieve_header = $this->AccSalesInvoiceModel->retrieve($id);
		

		if (empty($retrieve_header)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Untuk diposting"), REST_Controller::HTTP_NOT_FOUND);
			return;
		} else {
			if ($retrieve_header['is_posting'] == 1) {
				$this->set_response(array("status" => "NOT OK", "data" => "Data Sudah diposting"), REST_Controller::HTTP_NOT_FOUND);
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
		$retrieve_ppn = $this->db->query("SELECT * FROM acc_auto_jurnal
		 where kode='PPN_KELUARAN'  ")->row_array();
		$akun_ppn = $retrieve_ppn['acc_akun_id'];
		// Hapus jurnal jika sdh ada berdasarkan no refernsi dan MOdul  //
		$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'SALES_INVOICE');
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
			'modul' => 'SALES_INVOICE',
			'keterangan' => 'SALES_INVOICE ' . $retrieve_header['deskripsi'],
			'is_posting' => 1,
			'diposting_oleh' => $this->user_id
		);
		$id_header = $this->AccJurnalModel->create_header($dataH);
			// Data DEBET
		$dataDebet = array(
			'lokasi_id' => $retrieve_header['lokasi_id'],
			'jurnal_id' => $id_header,
			'acc_akun_id' => $retrieve_header['acc_akun_id_debet'], //akun biaya pemeliharaan,
			'debet' => ($retrieve_header['grand_total']),
			'kredit' => 0,
			'ket' => 'SALES_INVOICE ' . $retrieve_header['deskripsi'],
			'no_referensi' => $retrieve_header['no_invoice'],
			'referensi_id' => NULL,
			'blok_stasiun_id' => NULL,
			'kegiatan_id' => NULL,
			'kendaraan_mesin_id' => NULL
		);

		$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataDebet);

		// Data KREDIT 1
		$dataKredit = array(
			'lokasi_id' => $retrieve_header['lokasi_id'],
			'jurnal_id' => $id_header,
			'acc_akun_id' => $retrieve_header['acc_akun_id_kredit'], //$value['acc_akun_id'],
			'debet' => 0,
			'kredit' => ($retrieve_header['harga_satuan'] * $retrieve_header['qty'])+ $retrieve_header['premi'] - ($retrieve_header['uang_muka'] + $retrieve_header['diskon']), // Akun Lawan Biaya
			'ket' => 'SALES_INVOICE ' . $retrieve_header['deskripsi'],
			'no_referensi' => $retrieve_header['no_invoice'],
			'referensi_id' => NULL,
			'blok_stasiun_id' => NULL,
			'kegiatan_id' => NULL, // $value['kegiatan_id'],
			'kendaraan_mesin_id' => NULL
		);

		$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataKredit);

		$ppn_nilai = ($retrieve_header['ppn'] / 100) *  (($retrieve_header['harga_satuan'] * $retrieve_header['qty'])+ $retrieve_header['premi'] - ($retrieve_header['uang_muka'] + $retrieve_header['diskon']));
		if ($ppn_nilai > 0) {
			// Data KREDIT 2
			$dataKredit = array(
				'lokasi_id' => $retrieve_header['lokasi_id'],
				'jurnal_id' => $id_header,
				'acc_akun_id' => $akun_ppn, //$value['acc_akun_id'],
				'debet' => 0,
				'kredit' => $ppn_nilai, // Akun Lawan Biaya
				'ket' => 'SALES_INVOICE PPN',
				'no_referensi' => $retrieve_header['no_invoice'],
				'referensi_id' => NULL,
				'blok_stasiun_id' => NULL,
				'kegiatan_id' => NULL, // $value['kegiatan_id'],
				'kendaraan_mesin_id' => NULL
			);
			$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataKredit);
		}
		$data['diposting_oleh'] = $this->user_id;
		$res = $this->AccSalesInvoiceModel->posting($id, $data);
		if (!empty($res)) {
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAllOutstandingInvoice_get()
	{

		$res = $this->db->query("SELECT a.*,b.nama as lokasi,a.id as invoice_id,
			c.nama_customer,c.acc_akun_id as akun_customer_id,(a.grand_total-a.nilai_dibayar) as sisa
			 FROM `acc_sales_invoice` a inner join gbm_organisasi b on a.lokasi_id=b.id
			inner join gbm_customer c on a.customer_id=c.id left join sls_kontrak d on a.sls_kontrak_id=d.id
			where a.grand_total-a.nilai_dibayar >0 
			and a.is_posting=1")->result_array();
		if (!empty($res)) {
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function print_slip_get($segment_3 = '')
	{
		$this->load->helper("terbilang");
		$id = (int)$segment_3;
		$data = [];

		$Invo = $this->AccSalesInvoiceModel->print_slip($id);
		$data['Invo'] = $Invo;

		$html = $this->load->view('AccSlsInvoice_print', $data, true);

		$filename = 'Slip_slsInvoice_' . time();
		$this->pdfgenerator->generate($html, $filename, true, 'A4', 'portrait');
		echo $html;
	}
	function acc_print_slip_get($segment_3 = '')
	{

		$id = (int)$segment_3;
		$data = [];

		$queryHeader = "SELECT a.*, 
		c.nama as item, 
		d.nama_customer as customer,
		b.periode_kirim_awal as kirim_awal, 
		b.periode_kirim_akhir as kirim_akhir,
		d.alamat as alamat FROM acc_sales_invoice a 
		INNER JOIN sls_kontrak b ON a.sls_kontrak_id=b.id
		INNER JOIN inv_item c ON b.produk_id=c.id
		INNER JOIN gbm_customer d ON a.customer_id=d.id WHERE a.id=" . $id . "";
		$dataHeader = $this->db->query($queryHeader)->row_array();

		$data['header'] = 	$dataHeader;

		$html = $this->load->view('AccSlip_slsInvoice', $data, true);

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'portrait');
		echo $html;
	}
	function laporan_ar_detail_post()
	{
		error_reporting(0);

		$data = [];
		$lokasi_id = $this->post('lokasi_id', true);
		$produk_id = $this->post('produk_id', true);
		$customer_id = $this->post('customer_id', true);
		$tanggal_tempo = $this->post('tgl_mulai', true);
		$status = $this->post('status', true);

		$query = "select a.*,b.nama_customer,IFNULL( c.dibayar, 0)as dibayar,a.grand_total -(IFNULL( c.dibayar, 0))as sisa   from acc_sales_invoice a INNER join gbm_customer b 
		on a.customer_id=b.id left join (select ref_id, sum(nilai)as dibayar from acc_kasbank_ht group by ref_id)c
		on a.id=c.ref_id
		left join  sls_kontrak d on a.sls_kontrak_id=d.id 
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
		$filter_produk = "Semua";
		if ($produk_id) {
			$query = $query . " and d.produk_id=" . $produk_id . "";
			$res = $this->db->query("select * from inv_item where id=" . $produk_id . "")->row_array();
			$filter_produk = $res['nama'];
		}
		if ($status == '0') {
			$filter_status = 'SEMUA';
		} else if ($status == '1') {
			$query = $query . " and (a.grand_total-(IFNULL( c.dibayar, 0)))<=0";
			$filter_status = 'LUNAS';
		} else if ($status == '2') {
			$query = $query . " and (a.grand_total-(IFNULL( c.dibayar, 0)))>0";
			$filter_status = 'BELUM LUNAS';
		}
		$data = $this->db->query($query)->result_array();

		$data['ap'] = 	$data;
		$data['filter_customer'] = 	$filter_customer;
		$data['filter_lokasi'] = 	$filter_lokasi;
		$data['filter_tgl_tempo'] = 	$tanggal_tempo;
		$data['filter_status'] = $filter_status;
		$data['filter_produk'] = $filter_produk;

		$html = $this->load->view('Acc_Ar_Laporan', $data, true);

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		echo $html;
	}
}
