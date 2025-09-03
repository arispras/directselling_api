<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

class AccSalesPerforma extends BD_Controller
{
	// public $user_id;
	// public $theCredential;
	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('AccSalesPerformaModel');
		$this->load->model('KaryawanModel');
		$this->load->model('M_DatatablesModel');
		$this->load->model('AccJurnalModel');
		$this->load->library('pdfgenerator');
		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id = $this->user_data->id;
	}

	public function list_post()
	{
		$post = $this->post();

		$query  = "SELECT a.*,b.no_spk, c.nama_customer as customer from acc_sales_performa a 
		left join sls_kontrak b on a.sls_kontrak_id=b.id
		left join gbm_customer c on a.customer_id=c.id";
		$search = array('no_performa', 'a.tanggal', 'a.tanggal_tempo', 'a.jenis_performa', 'c.nama_customer', 'b.no_spk');
		$where  = null;

		$isWhere = null;
		// $isWhere = 'artikel.deleted_at IS NULL';

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	function index_get($segment_3 = '')
	{
		$id = $segment_3;
		$retrieve = $this->AccSalesPerformaModel->retrieve($id);

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAll_get()
	{

		$retrieve = $this->AccSalesPerformaModel->retrieve_all();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function create_post()
	{

		$data = $this->post();
		// $data['diubah_oleh'] = $this->user_id;
		$retrieve =  $this->AccSalesPerformaModel->create($data);
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
	}

	function update_post($segment_3 = '')
	{


		$id = (int)$segment_3;
		$item = $this->AccSalesPerformaModel->retrieve($id);
		if (empty($item)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$data = $this->post();
		// $data['diubah_oleh'] = $this->user_id;
		$retrieve =   $this->AccSalesPerformaModel->update($item['id'], $data);
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
	}

	function index_delete($segment_3 = '')
	{

		$id = (int)$segment_3;
		$item = $this->AccSalesPerformaModel->retrieve($id);
		if (empty($item)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$retrieve =  $this->AccSalesPerformaModel->delete($item['id']);
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
	}

	function posting_post($segment_3 = null)
	{
		$id = (int)$segment_3;
		$data = $this->post();
		// $retrieve_header = $this->AccSalesPerformaModel->retrieve($id);
		// $retrieve_ppn = $this->db->query("SELECT * FROM acc_auto_jurnal
		//  where kode='PPN_MASUKAN'  ")->row_array();
		// $akun_ppn = $retrieve_ppn['acc_akun_id'];
		// // Hapus jurnal jika sdh ada berdasarkan no refernsi dan MOdul  //
		// $this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'AR_INVOICE');
		// // Data HEADER
		// $this->load->library('Autonumber');
		// $no_jurnal=$this->autonumber->jurnal_auto($retrieve_header['lokasi_id'],$retrieve_header['tanggal'],'AR');
		// $dataH = array(
		// 	'no_jurnal'=>$no_jurnal,
		// 	'lokasi_id' => $retrieve_header['lokasi_id'],
		// 	'tanggal' => $retrieve_header['tanggal'],
		// 	'no_ref' => $retrieve_header['no_performa'],
		// 	'ref_id' => $retrieve_header['id'],
		// 	'tipe_jurnal' => 'AUTO',
		// 	'modul' => 'AR_INVOICE',
		// 	'keterangan' => 'AR INVOICE'
		// );
		// $id_header = $this->AccJurnalModel->create_header($dataH);
		// $ppn_nilai=($retrieve_header['ppn']/100) *  ($retrieve_header['harga_satuan'] * $retrieve_header['qty']) - ($retrieve_header['uang_muka'] + $retrieve_header['diskon']);
		// // Data DEBET
		// $dataDebet = array(
		// 	'lokasi_id' => $retrieve_header['lokasi_id'],
		// 	'jurnal_id' => $id_header,
		// 	'acc_akun_id' => $retrieve_header['acc_akun_id_debet'], //akun biaya pemeliharaan,
		// 	'debet' => ($retrieve_header['grand_total']),
		// 	'kredit' => 0,
		// 	'ket' => 'AR INVOICE ',
		// 	'no_referensi' => $retrieve_header['no_performa'],
		// 	'referensi_id' => NULL,
		// 	'blok_stasiun_id' => NULL,
		// 	'kegiatan_id' => NULL,
		// 	'kendaraan_mesin_id' => NULL
		// );

		// $id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataDebet);

		// // Data KREDIT 1
		// $dataKredit = array(
		// 	'lokasi_id' => $retrieve_header['lokasi_id'],
		// 	'jurnal_id' => $id_header,
		// 	'acc_akun_id' => $retrieve_header['acc_akun_id_kredit'], //$value['acc_akun_id'],
		// 	'debet' => 0,
		// 	'kredit' => ($retrieve_header['harga_satuan'] * $retrieve_header['qty']) - ($retrieve_header['uang_muka'] + $retrieve_header['diskon']), // Akun Lawan Biaya
		// 	'ket' => 'AR_INVOICE',
		// 	'no_referensi' => $retrieve_header['no_performa'],
		// 	'referensi_id' => NULL,
		// 	'blok_stasiun_id' => NULL,
		// 	'kegiatan_id' => NULL, // $value['kegiatan_id'],
		// 	'kendaraan_mesin_id' => NULL
		// );
		// $id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataKredit);
		// // Data KREDIT 2
	
		// $dataKredit = array(
		// 	'lokasi_id' => $retrieve_header['lokasi_id'],
		// 	'jurnal_id' => $id_header,
		// 	'acc_akun_id' =>$akun_ppn, //$value['acc_akun_id'],
		// 	'debet' => 0,
		// 	'kredit' => $ppn_nilai, // Akun Lawan Biaya
		// 	'ket' => 'AR_INVOICE PPN',
		// 	'no_referensi' => $retrieve_header['no_performa'],
		// 	'referensi_id' => NULL,
		// 	'blok_stasiun_id' => NULL,
		// 	'kegiatan_id' => NULL, // $value['kegiatan_id'],
		// 	'kendaraan_mesin_id' => NULL
		// );
		// $id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataKredit);

		$res = $this->AccSalesPerformaModel->posting($id, $data);
		if (!empty($res)) {
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAllOutstandingPerforma_get()
	{

		$res = $this->db->query("SELECT a.*,b.nama as lokasi,
			c.nama_customer,c.acc_akun_id as akun_customer_id,(a.grand_total-a.nilai_dibayar) as sisa
			 FROM `acc_sales_performa` a inner join gbm_organisasi b on a.lokasi_id=b.id
			inner join gbm_customer c on a.customer_id=c.id left join sls_kontrak d on a.sls_kontrak_id=d.id
			where a.grand_total-a.nilai_dibayar >0 ")->result_array();
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

		$Invo = $this->AccSalesPerformaModel->print_slip($id);
		$data['Invo'] = $Invo;

		$html = $this->load->view('AccSlsPerforma_print', $data, true);

		$filename = 'report_SlsPerforma_' . time();
		$this->pdfgenerator->generate($html, $filename, true, 'A4', 'portrait');
		echo $html;
	}
	function laporan_ar_detail_post()
	{
		error_reporting(0);
		
		$data = [];
		$lokasi_id=$this->post('lokasi_id',true);
		$customer_id=$this->post('customer_id',true);
		$tanggal_tempo=$this->post('tgl_mulai',true);
		$status=$this->post('status',true);

		$query = "select a.*,b.nama_customer,IFNULL( c.dibayar, 0)as dibayar,a.grand_total -(IFNULL( c.dibayar, 0))as sisa   from acc_sales_performa a INNER join gbm_customer b 
		on a.customer_id=b.id left join (select ref_id, sum(nilai)as dibayar from acc_kasbank_ht group by ref_id)c
		on a.id=c.ref_id
		where a.tanggal_tempo <=  '". $tanggal_tempo ."' 	
		";
		$filter_customer="Semua";
		$filter_lokasi="Semua";
		if ($lokasi_id){
			$query=$query." and a.lokasi_id=".$lokasi_id ."";
			$res=$this->db->query("select * from gbm_organisasi where id=".$lokasi_id."")->row_array();
			$filter_lokasi=$res['nama'];
		}
		if ($customer_id){
			$query=$query." and a.customer_id=".$customer_id ."";
			$res=$this->db->query("select * from gbm_customer where id=".$customer_id."")->row_array();
			$filter_customer=$res['nama_customer'];
		}
		if ($status=='0'){
			$filter_status='SEMUA';
		}else if ($status=='1'){
			$query=$query." and a.nilai_performa-(IFNULL( c.dibayar, 0))<=0";
			$filter_status='LUNAS';
		}else if ($status=='2'){
			$query=$query." and a.nilai_performa-(IFNULL( c.dibayar, 0))>0";
			$filter_status='BELUM LUNAS';
		}	
		$data = $this->db->query($query)->result_array();
		
		$data['ap'] = 	$data;
		$data['filter_customer'] = 	$filter_customer;
		$data['filter_lokasi'] = 	$filter_lokasi;
		$data['filter_tgl_tempo'] = 	$tanggal_tempo;
		$data['filter_status'] = $filter_status;

		$html = $this->load->view('Acc_Ar_Laporan', $data, true);

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		echo $html;
	}
}
