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
use Symfony\Component\Translation\Provider\NullProvider;

class WrkKegiatan extends BD_Controller
{
	public $user_id;
	public $theCredential;

	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('GbmOrganisasiModel');
		$this->load->model('WrkKegiatanModel');
		$this->load->model('AccJurnalModel');
		$this->load->model('AccJurnalUpahModel');
		$this->load->model('M_DatatablesModel');
		$this->load->model('AccPeriodeAkuntingModel');

		$this->load->library('pdfgenerator');
		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id = $this->user_data->id;
	}
	public function list_post()
	{
		$post = $this->post();
		$param = $post['parameter'];

		$query  = "SELECT a.*,b.nama as lokasi,e.nama as kendaraan,e.kode as kode_kendaraan ,f.nama as workshop
		FROM `wrk_kegiatan_ht` a 
		inner join gbm_organisasi b on a.lokasi_id=b.id
		left join trk_kendaraan e on a.kendaraan_mesin_id=e.id 
		left join gbm_organisasi f on a.workshop_id=f.id 
		";
		$search = array('a.no_transaksi', 'a.tanggal', 'b.nama', 'e.nama', 'f.nama', 'e.kode', 'a.alasan');
		$where  = null;

		$isWhere = " 1=1";
		if ($param['tgl_mulai'] && $param['tgl_mulai']) {
			$isWhere = " tanggal between '" . $param['tgl_mulai'] . "' and '" . $param['tgl_akhir'] . "'";
		}
		if ($param['lokasi_id']) {
			$isWhere = $isWhere . " and lokasi_id =" . $param['lokasi_id'] . "";
		} else {
			$isWhere = $isWhere . " and  lokasi_id in
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
	function listWorkshop_post()
	{
		$format_laporan =  $this->post('format_laporan', true);
		// $id = (int)$segment_3;
		$data = [];
		$input = [
			'no_po' => '01/PO/DPA',
			'lokasi_id' => 265,
			'tgl_mulai' => '2022-01-01',
			'tgl_akhir' => '2022-12-12',
			'status_id' => '1',
		];

		$lokasi_id = $this->post('lokasi_id', true);
		$tanggal_awal = $this->post('tgl_mulai', true);
		$tanggal_akhir = $this->post('tgl_akhir', true);
		$status_id = $this->post('status_id', true);

		// $lokasi_id = $input['lokasi_id'];
		// $tanggal_awal = $input['tgl_mulai'];
		// $tanggal_akhir = $input['tgl_akhir'];
		// $status_id = $input['status_id'];

		$queryBkm = "SELECT a.*,
		b.nama as lokasi,
		e.nama as kendaraan,
		e.kode as kode_kendaraan ,
		f.nama as workshop
		FROM `wrk_kegiatan_ht` a 
		inner join gbm_organisasi b on a.lokasi_id=b.id
		left join trk_kendaraan e on a.kendaraan_mesin_id=e.id 
		left join gbm_organisasi f on a.workshop_id=f.id 

		where a.tanggal between  '" . $tanggal_awal . "' and  '" . $tanggal_akhir . "'	
		";

		$filter_lokasi = "Semua";
		if ($lokasi_id) {
			$queryBkm = $queryBkm . " and a.lokasi_id=" . $lokasi_id . "";
			$res = $this->db->query("select * from gbm_organisasi where id=" . $lokasi_id . "")->row_array();
			$filter_lokasi = $res['nama'];
		}
		if (!empty($status_id)) {
			if ($status_id == 'N') {
				$queryBkm = $queryBkm .  "  and a.is_posting=0";
			} else {
				$queryBkm = $queryBkm .  "  and a.is_posting=1";
			}
		}
		// var_dump($this->post());exit();
		$dataBkm = $this->db->query($queryBkm)->result_array();
		// var_dump($dataBkm);exit();
		$data['bkm'] = 	$dataBkm;
		$data['filter_lokasi'] = 	$filter_lokasi;
		$data['filter_tgl_awal'] = 	$tanggal_awal;
		$data['filter_tgl_akhir'] = $tanggal_akhir;

		$html = $this->load->view('Wrk_kegiatan_list', $data, true);

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		echo $html;
	}

	function listWrkKegiatanDetail_post()
	{
		$format_laporan =  $this->post('format_laporan', true);

		$data = [];

		$input = [
			'lokasi_id' => 252,
			'tgl_mulai' => '2022-11-05',
			'tgl_akhir' => '2022-11-05',
			'status_id' => '0',
		];

		$lokasi_id = $this->post('lokasi_id', true);
		$tanggal_awal = $this->post('tgl_mulai', true);
		$tanggal_akhir = $this->post('tgl_akhir', true);
		$status_id = $this->post('status_id', true);

		// $lokasi_id = $input['lokasi_id'];
		// $tanggal_awal = $input['tgl_mulai'];
		// $tanggal_akhir = $input['tgl_akhir'];
		// $status_id = $input['status_id'];

		$queryhead = "SELECT 
		a.*,
		b.nama AS lokasi,
		-- c.nama AS kendaraan,
		-- c.kode AS kode_kendaraan,
		d.nama as workshop,
		a.id AS id
		FROM wrk_kegiatan_ht a 
		INNER JOIN gbm_organisasi b ON a.lokasi_id=b.id
		-- INNER JOIN trk_kendaraan c ON a.kendaraan_mesin_id=c.id
		INNER JOIN gbm_organisasi d ON a.workshop_id=d.id
		where a.tanggal between  '" . $tanggal_awal . "' and  '" . $tanggal_akhir . "'	
		";

		$filter_lokasi = "Semua";
		if ($lokasi_id) {
			$queryhead = $queryhead . " and a.lokasi_id=" . $lokasi_id . "";
			$res = $this->db->query("select * from gbm_organisasi where id=" . $lokasi_id . "")->row_array();
			$filter_lokasi = $res['nama'];
		}
		if (!empty($status_id)) {
			if ($status_id == 'N') {
				$queryhead = $queryhead .  "  and a.is_posting=0";
			} else {
				$queryhead = $queryhead .  "  and a.is_posting=1";
			}
		}

		$ressult = array();
		$dataBkm = $this->db->query($queryhead)->result_array();
		foreach ($dataBkm as $key => $hd) {
			$querydetail = "SELECT 
			a.*,
			b.nama AS karyawan,
			a.id AS id
			FROM wrk_kegiatan_dt a 
			LEFT JOIN karyawan b on a.karyawan_id=b.id
			WHERE a.wrk_kegiatan_id=" . $hd['id'] . "";
			$dataDtl = $this->db->query($querydetail)->result_array();
			// var_dump($dataDtl)
			$querydetailKegiatan = "SELECT 
			a.*,
			c.nama AS nama_kegiatan,
			c.kode AS kode_kegiatan,
			d.nama AS nama_blok,
			d.kode AS kode_blok,
			e.nama AS nama_kendaraan,
			e.kode AS kode_kendaraan,
			a.id AS id
			FROM wrk_kegiatan_log a 
			left JOIN acc_kegiatan c on a.acc_kegiatan_id=c.id 
			LEFT JOIN gbm_organisasi d on a.blok_id=d.id
			LEFT JOIN trk_kendaraan e on a.kendaraan_id=e.id
			WHERE a.wrk_kegiatan_id=" . $hd['id'] . "";
			$queryDtlKegiatan = $this->db->query($querydetailKegiatan)->result_array();
			
			$hd['dtl'] = $queryDtlKegiatan;
			$hd['detail'] = $dataDtl;
			$result[] = $hd;
		}

		$data['umum'] = 	$result;
		// var_dump($result)	;exit();
		$data['filter_lokasi'] = 	$filter_lokasi;
		$data['filter_tgl_awal'] = 	$tanggal_awal;
		$data['filter_tgl_akhir'] = $tanggal_akhir;

		$html = $this->load->view('Wrk_Kegiatan_list_detail', $data, true);

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		echo $html;
	}

	function index_get($id = '')
	{
		$retrieve = $this->WrkKegiatanModel->retrieve_by_id($id);
		$retrieve['detail'] = $this->WrkKegiatanModel->retrieve_detail($id);
		$retrieve['detail_log'] = $this->WrkKegiatanModel->retrieve_log($id);
		$retrieve['detail_item'] = $this->WrkKegiatanModel->retrieve_detail_item($id);
		// $retrieve['detail_log'] = $this->WrkKegiatanModel->retrieve_detail_item($id);


		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function getDetail_get($id = '')
	{
		$retrieve = $this->WrkKegiatanModel->retrieve_detail($id);
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
		$input['no_transaksi'] = $this->autonumber->wrk_kegiatan($input['lokasi_id']['id'], $input['tanggal']);

		//$input['no_spat'] = $this->getLastNumber('pks_pengolahan_ht', 'no_spat', 'SPAT');
		// var_dump($input);

		$res = $this->WrkKegiatanModel->create($input);
		if (!empty($res)) {
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

		$res = $this->WrkKegiatanModel->update($id, $data);
		if (!empty($res)) {
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	public function index_delete($id)
	{

		$res = $this->WrkKegiatanModel->delete($id);
		if (!empty($res)) {
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function posting_post2($segment_3 = null)
	{
		$id = (int) $segment_3;
		$retrieve_header = $this->WrkKegiatanModel->retrieve_by_id($id);
		$retrieve_detail = $this->db->query("SELECT a.*,b.*,c.nip,c.nama as nama_karyawan, d.kode as kode_kendaraan,d.nama as nama_kendaraan,d.no_kendaraan  FROM wrk_kegiatan_ht a inner join wrk_kegiatan_dt b on a.id=b.trk_kegiatan_kendaraan_id inner join karyawan c on b.karyawan_id=c.id
		inner join trk_kendaraan d on a.kendaraan_id=d.id 
		where b.trk_kegiatan_kendaraan_id=" . $id . "")->result_array();

		// Hapus jurnal jika sdh ada berdasarkan no refernsi dan MOdul  //
		$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'TRAKSI');
		$total_pendapatan = 0;

		// Data HEADER
		$dataH = array(
			'lokasi_id' => $retrieve_header['lokasi_id'],
			'tanggal' => $retrieve_header['tanggal'],
			'no_ref' => $retrieve_header['no_transaksi'],
			'ref_id' => $retrieve_header['id'],
			'tipe_jurnal' => 'AUTO',
			'modul' => 'TRAKSI',
			'keterangan' => 'TRANSAKSI TRAKSI'
		);
		$id_header = $this->AccJurnalModel->create_header($dataH);
		$total_pendapatan = 0;
		foreach ($retrieve_detail as $key => $value) {
			// Data DEBET
			$dataDebet = array(
				'jurnal_id' => $id_header,
				'acc_akun_id' => 1, //akun biaya Panen,
				'debet' => ($value['rupiah_hk'] + $value['premi']),
				'kredit' => 0,
				'ket' => 'Biaya Gaji Traksi Kendaraan: ' . $value['nama_kendaraan'] . ' , Karyawan: ' . $value['nama_karyawan'] . ",",
				'no_referensi' => $retrieve_header['no_transaksi'],
				'referensi_id' => NULL,
				'blok_stasiun_id' => NULL,
				'kegiatan_id' => NULL, //kegiatan panen,
				'kendaraan_mesin_id' =>  $retrieve_header['kendaraan_id']
			);
			$total_pendapatan =	$total_pendapatan + ($value['rupiah_hk'] + $value['premi']);
			$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataDebet);
		}

		// Data KREDIT
		$dataKredit = array(
			'jurnal_id' => $id_header,
			'acc_akun_id' => 2, //$value['acc_akun_id'], // Akun transit
			'debet' => 0,
			'kredit' => $total_pendapatan, // Akun Lawan Biaya
			'ket' => 'TRAKSI',
			'no_referensi' => $retrieve_header['no_transaksi'],
			'referensi_id' => NULL,
			'blok_stasiun_id' => NULL,
			'kegiatan_id' => NULL, // $value['kegiatan_id'],
			'kendaraan_mesin_id' => NULL // $retrieve_header['kendaraan_id']
		);
		$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataKredit);
		$res = $this->WrkKegiatanModel->posting($id, null);
		if (!empty($res)) {
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function posting_post($segment_3 = null)
	{
		$id = (int) $segment_3;
		$res_akun_transit_upah = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='WORKSHOP_TRANSIT_UPAH'")->row_array();
		if (empty($res_akun_transit_upah)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$res_akun_transit_premi = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='WORKSHOP_TRANSIT_PREMI'")->row_array();
		if (empty($res_akun_transit_premi)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}

		$akun_debet_transit_premi = $res_akun_transit_premi['acc_akun_id_debet'];
		$akun_kredit_transit_premi = $res_akun_transit_premi['acc_akun_id_kredit'];
		$akun_debet_transit_upah = $res_akun_transit_upah['acc_akun_id_debet'];
		$akun_kredit_transit_upah = $res_akun_transit_upah['acc_akun_id_kredit'];

		$retrieve_header = $this->WrkKegiatanModel->retrieve_by_id($id);
		$q = "SELECT a.*,b.*,c.nip,c.nama as nama_karyawan, d.kode as kode_kendaraan,
		d.nama as nama_kendaraan,d.no_kendaraan
		FROM wrk_kegiatan_ht a inner join wrk_kegiatan_dt b on a.id=b.wrk_kegiatan_id 
	   inner join karyawan c on b.karyawan_id=c.id 
	   left join trk_kendaraan d on a.kendaraan_mesin_id =d.id where b.wrk_kegiatan_id=" . $id . "";
		$retrieve_detail = $this->db->query($q)->result_array();
		$retrieve_org = $this->GbmOrganisasiModel->retrieve($retrieve_header['lokasi_id']);
		// Hapus jurnal jika sdh ada berdasarkan no refernsi dan MOdul  //
		$this->AccJurnalUpahModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'WORKSHOP');
		$total_pendapatan = 0;

		if ($retrieve_org['tipe'] == 'ESTATE') { // JIka bukan ESTATE maka Tidak ada jurnal.Jurnalnya saat poting payroll
			// Data HEADER
			$this->load->library('Autonumber');
			$no_jurnal = $this->autonumber->jurnal_upah_auto($retrieve_header['lokasi_id'], $retrieve_header['tanggal'], 'WRK');

			$dataH = array(
				'lokasi_id' => $retrieve_header['lokasi_id'],
				'no_jurnal' => $no_jurnal,
				'tanggal' => $retrieve_header['tanggal'],
				'no_ref' => $retrieve_header['no_transaksi'],
				'ref_id' => $retrieve_header['id'],
				'tipe_jurnal' => 'AUTO',
				'modul' => 'WORKSHOP',
				'keterangan' => 'TRANSAKSI WORKSHOP',
				'is_posting' => 1,

			);
			$id_header = $this->AccJurnalUpahModel->create_header($dataH);
			$total_pendapatan = 0;

			foreach ($retrieve_detail as $key => $value) {
				$upah = $value['rupiah_hk'];
				$premi = $value['premi']; // nilai premi 
				if ($upah > 0) {
					$dataDebet = array(
						'lokasi_id' => $retrieve_header['lokasi_id'],
						'jurnal_id' => $id_header,
						'acc_akun_id' => $akun_debet_transit_upah,
						'debet' => ($upah),
						'kredit' => 0,
						'ket' => 'Biaya Upah Workshop , Karyawan: ' . $value['nama_karyawan'] . "",
						'no_referensi' => $retrieve_header['no_transaksi'],
						'referensi_id' => NULL,
						'blok_stasiun_id' => NULL,
						'kegiatan_id' => NULL, //,
						'divisi_id' =>  $retrieve_header['workshop_id'], // divisi workshop sbg cost center
						'kendaraan_mesin_id' => NULL,
						'karyawan_id' => $value['karyawan_id'], //karyawan,
						'tipe' => 'upah',
						'hk' => $value['jumlah_hk']
					);
					$total_pendapatan =	$total_pendapatan + ($value['rupiah_hk'] + $value['premi']);
					$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
					// Data KREDIT
					$dataKredit = array(
						'lokasi_id' => $retrieve_header['lokasi_id'],
						'jurnal_id' => $id_header,
						'acc_akun_id' =>  $akun_kredit_transit_upah, //$value['acc_akun_id'], // Akun transit
						'debet' => 0,
						'kredit' => $upah, // Akun Lawan Biaya
						'ket' => 'Biaya Upah Workshop, Karyawan: ' . $value['nama_karyawan'] . "",
						'no_referensi' => $retrieve_header['no_transaksi'],
						'referensi_id' => NULL,
						'blok_stasiun_id' => NULL,
						'kegiatan_id' => NULL, // $value['kegiatan_id'],
						'kendaraan_mesin_id' => NULL,
						'tipe' => 'upah',
						'hk' => 0
					);
					$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
				}
				if ($premi > 0) {
					$dataDebet = array(
						'lokasi_id' => $retrieve_header['lokasi_id'],
						'jurnal_id' => $id_header,
						'acc_akun_id' => $akun_debet_transit_premi,
						'debet' => ($premi),
						'kredit' => 0,
						'ket' => 'Biaya Premi Workshop, Karyawan: ' . $value['nama_karyawan'] . "",
						'no_referensi' => $retrieve_header['no_transaksi'],
						'referensi_id' => NULL,
						'blok_stasiun_id' => NULL,
						'kegiatan_id' => NULL, //kegiatan panen,
						'divisi_id' =>  $retrieve_header['workshop_id'], // divisi workshop sbg cost center
						'kendaraan_mesin_id' =>  $retrieve_header['kendaraan_mesin_id'],
						'karyawan_id' => $value['karyawan_id'], //karyawan,
						'tipe' => 'premi',
						'hk' => 0
					);
					$total_pendapatan =	$total_pendapatan + ($value['rupiah_hk'] + $value['premi']);
					$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
					// Data KREDIT
					$dataKredit = array(
						'lokasi_id' => $retrieve_header['lokasi_id'],
						'jurnal_id' => $id_header,
						'acc_akun_id' =>  $akun_kredit_transit_premi, //$value['acc_akun_id'], // Akun transit
						'debet' => 0,
						'kredit' => $premi, // Akun Lawan Biaya
						'ket' => 'Biaya Premi Workshop, Karyawan: ' . $value['nama_karyawan'] . "",
						'no_referensi' => $retrieve_header['no_transaksi'],
						'referensi_id' => NULL,
						'blok_stasiun_id' => NULL,
						'kegiatan_id' => NULL, // $value['kegiatan_id'],
						'kendaraan_mesin_id' => NULL,
						'tipe' => 'premi',
						'hk' => 0
					);
					$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
				}
			}
		}
		$res = $this->WrkKegiatanModel->posting($id, null);
		if (!empty($res)) {
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function posting_batch_post($lokasi_id = null, $t1 = null, $t2 = null)
	{
		$res_akun_transit_upah = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='WORKSHOP_TRANSIT_UPAH'")->row_array();
		if (empty($res_akun_transit_upah)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$res_akun_transit_premi = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='WORKSHOP_TRANSIT_PREMI'")->row_array();
		if (empty($res_akun_transit_premi)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}

		$akun_debet_transit_premi = $res_akun_transit_premi['acc_akun_id_debet'];
		$akun_kredit_transit_premi = $res_akun_transit_premi['acc_akun_id_kredit'];
		$akun_debet_transit_upah = $res_akun_transit_upah['acc_akun_id_debet'];
		$akun_kredit_transit_upah = $res_akun_transit_upah['acc_akun_id_kredit'];
		$res_transaksi_wrk = $this->db->query("SELECT * from wrk_kegiatan_ht where  
		tanggal between '" . $t1 . "' and '" . $t2 . "' and is_posting=0 
		and lokasi_id=" . $lokasi_id . " order by tanggal")->result_array();
		$id = null;
		$jum = 0;
		foreach ($res_transaksi_wrk  as $key => $retrieve_header) {
			$jum++;
			$id = $retrieve_header['id'];
			// $retrieve_header = $this->WrkKegiatanModel->retrieve_by_id($id);
			$retrieve_detail = $this->db->query("SELECT a.*,b.*,c.nip,c.nama as nama_karyawan, d.kode as kode_kendaraan,d.nama as nama_kendaraan,d.no_kendaraan FROM wrk_kegiatan_ht a inner join wrk_kegiatan_dt b on a.id=b.wrk_kegiatan_id inner join karyawan c on b.karyawan_id=c.id left join trk_kendaraan d on a.kendaraan_mesin_id =d.id where b.wrk_kegiatan_id=" . $id . "")->result_array();
			$retrieve_org = $this->GbmOrganisasiModel->retrieve($retrieve_header['lokasi_id']);
			// Hapus jurnal jika sdh ada berdasarkan no refernsi dan MOdul  //
			$this->AccJurnalUpahModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'WORKSHOP');
			$total_pendapatan = 0;

			if ($retrieve_org['tipe'] == 'ESTATE') { // JIka bukan ESTATE maka Tidak ada jurnal.Jurnalnya saat poting payroll
				// Data HEADER
				$this->load->library('Autonumber');
				$no_jurnal = $this->autonumber->jurnal_upah_auto($retrieve_header['lokasi_id'], $retrieve_header['tanggal'], 'WRK');

				$dataH = array(
					'lokasi_id' => $retrieve_header['lokasi_id'],
					'no_jurnal' => $no_jurnal,
					'lokasi_id' => $retrieve_header['lokasi_id'],
					'tanggal' => $retrieve_header['tanggal'],
					'no_ref' => $retrieve_header['no_transaksi'],
					'ref_id' => $retrieve_header['id'],
					'tipe_jurnal' => 'AUTO',
					'modul' => 'WORKSHOP',
					'keterangan' => 'TRANSAKSI WORKSHOP',
					'is_posting' => 1,

				);
				$id_header = $this->AccJurnalUpahModel->create_header($dataH);
				$total_pendapatan = 0;

				foreach ($retrieve_detail as $key => $value) {
					$upah = $value['rupiah_hk'];
					$premi = $value['premi']; // nilai premi 
					if ($upah > 0) {
						$dataDebet = array(
							'lokasi_id' => $retrieve_header['lokasi_id'],
							'jurnal_id' => $id_header,
							'acc_akun_id' => $akun_debet_transit_upah,
							'debet' => ($upah),
							'kredit' => 0,
							'ket' => 'Biaya Upah Workshop , Karyawan: ' . $value['nama_karyawan'] . "",
							'no_referensi' => $retrieve_header['no_transaksi'],
							'referensi_id' => NULL,
							'blok_stasiun_id' => NULL,
							'kegiatan_id' => NULL, //,
							'divisi_id' =>  $retrieve_header['workshop_id'], // divisi workshop sbg cost center
							'kendaraan_mesin_id' => NULL,
							'karyawan_id' => $value['karyawan_id'], //karyawan,
							'tipe' => 'upah',
							'hk' => $value['jumlah_hk']
						);
						$total_pendapatan =	$total_pendapatan + ($value['rupiah_hk'] + $value['premi']);
						$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
						// Data KREDIT
						$dataKredit = array(
							'lokasi_id' => $retrieve_header['lokasi_id'],
							'jurnal_id' => $id_header,
							'acc_akun_id' =>  $akun_kredit_transit_upah, //$value['acc_akun_id'], // Akun transit
							'debet' => 0,
							'kredit' => $upah, // Akun Lawan Biaya
							'ket' => 'Biaya Upah Workshop, Karyawan: ' . $value['nama_karyawan'] . "",
							'no_referensi' => $retrieve_header['no_transaksi'],
							'referensi_id' => NULL,
							'blok_stasiun_id' => NULL,
							'kegiatan_id' => NULL, // $value['kegiatan_id'],
							'kendaraan_mesin_id' => NULL,
							'tipe' => 'upah',
							'hk' => 0
						);
						$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
					}
					if ($premi > 0) {
						$dataDebet = array(
							'lokasi_id' => $retrieve_header['lokasi_id'],
							'jurnal_id' => $id_header,
							'acc_akun_id' => $akun_debet_transit_premi,
							'debet' => ($premi),
							'kredit' => 0,
							'ket' => 'Biaya Premi Workshop, Karyawan: ' . $value['nama_karyawan'] . "",
							'no_referensi' => $retrieve_header['no_transaksi'],
							'referensi_id' => NULL,
							'blok_stasiun_id' => NULL,
							'kegiatan_id' => NULL, //kegiatan panen,
							'kendaraan_mesin_id' =>  $retrieve_header['kendaraan_mesin_id'],
							'karyawan_id' => $value['karyawan_id'], //karyawan,
							'tipe' => 'premi',
							'hk' => 0
						);
						$total_pendapatan =	$total_pendapatan + ($value['rupiah_hk'] + $value['premi']);
						$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
						// Data KREDIT
						$dataKredit = array(
							'lokasi_id' => $retrieve_header['lokasi_id'],
							'jurnal_id' => $id_header,
							'acc_akun_id' =>  $akun_kredit_transit_premi, //$value['acc_akun_id'], // Akun transit
							'debet' => 0,
							'kredit' => $premi, // Akun Lawan Biaya
							'ket' => 'Biaya Premi Workshop, Karyawan: ' . $value['nama_karyawan'] . "",
							'no_referensi' => $retrieve_header['no_transaksi'],
							'referensi_id' => NULL,
							'blok_stasiun_id' => NULL,
							'kegiatan_id' => NULL, // $value['kegiatan_id'],
							'kendaraan_mesin_id' => NULL,
							'tipe' => 'premi',
							'hk' => 0
						);
						$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
					}
				}
			}
			$res = $this->WrkKegiatanModel->posting($id, null);
			// if (!empty($res)) {
			// 	$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
			// } else {
			// 	$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
			// }
		}
		$this->set_response(array("status" => "OK", "data" => $jum), REST_Controller::HTTP_CREATED);
	}
	public function start_proses_alokasi_post()
	{
		$id = $this->post('id');
		$periodeAkunting = $this->AccPeriodeAkuntingModel->retrieve($id);
		if (empty($periodeAkunting)) {
			$this->set_response(array("status" => "NOT OK", "data" => 'null'), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$lokasi_id = $periodeAkunting['lokasi_id'];
		$lokasi = $this->db->query("SELECT * FROM gbm_organisasi 
								where id=" . $lokasi_id . "")->row_array();
	
		if ($lokasi['tipe'] == 'ESTATE') {
			$this->start_proses_alokasi_estate($id);
		} elseif ($lokasi['tipe'] == 'MILL') {
			$this->start_proses_alokasi_mill($id);
		}
	}
	public function start_proses_alokasi_mill($id)
	{

		// $id = $this->post('id');
		$periodeAkunting = $this->AccPeriodeAkuntingModel->retrieve($id);
		if (empty($periodeAkunting)) {
			$this->set_response(array("status" => "NOT OK", "data" => 'null'), REST_Controller::HTTP_NOT_FOUND);
			return;
		}

		$lokasi_id = $periodeAkunting['lokasi_id'];
		$d1 = $periodeAkunting['tgl_awal'];
		$d2 = $periodeAkunting['tgl_akhir'];
		$last_date_in_periode = $d2;
		$res_transit_akun_traksi = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='TRAKSI_TRANSIT_REPARASI'")->row_array();
		if (empty($res_transit_akun_traksi)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$transit_akun_traksi_reparasi = $res_transit_akun_traksi['acc_akun_id'];
		$res_workshop_dialokasi_akun = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='WORKSHOP_DIALOKASI_AKUN'")->row_array();
		if (empty($res_workshop_dialokasi_akun)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}

		$workshop_dialokasi_akun = $res_workshop_dialokasi_akun['acc_akun_id'];

		/* Hapus dulu jurnal alokasi traksi jika sudah ada pada bulan tsb.*/
		$q = "delete from acc_jurnal_dt where jurnal_id in(select id from acc_jurnal_ht
                 where tanggal='" . $last_date_in_periode . "' and  modul='ALK_WORKSHOP'
				 and lokasi_id =" . $lokasi_id . ")";
		$this->db->query($q);
		$q = "delete from acc_jurnal_ht  
                 where tanggal='" . $last_date_in_periode . "' and  modul='ALK_WORKSHOP'
				 and lokasi_id =" . $lokasi_id . "";
		$this->db->query($q);

		/* cek  total biaya transit ke workshop utk alokasi traksi */
		/* hard code akun transit: ('4110101', '4110102','4110103','4110104','4110105')*/
		$qtransit_account = "SELECT b.lokasi_id,sum(debet-kredit)as nilai 
		FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
		on a.id=b.jurnal_id
		inner join acc_akun c on b.acc_akun_id=c.id
		left join gbm_organisasi d on d.id=b.divisi_id 
		where 1=1 
		and b.lokasi_id =" . $lokasi_id . "
		and a.tanggal >='" . $d1 . "' and a.tanggal <='" . $d2 . "'
		and c.kode in ('4110101', '4110102','4110103','4110104','4110105')
		group by b.lokasi_id ";
		
		$res_transit_account = $this->db->query($qtransit_account)->row_array();
		
		$nilai = 0;
		$total_jam_perbaikan = 0;
		$jumlah_transaksi = 0;
		if ($res_transit_account) {
			/* cek total jam perbaikan traksi */
			$nilai = $res_transit_account['nilai'];

			/* START  perbaikan di kendaran worshop */
			$q1 = " SELECT a.*,b.acc_kegiatan_id,c.kode as kode_kegiatan,c.nama as nama_kegiatan,
			a.lama_perbaikan,b.ket,
			c.acc_akun_id,d.kode as kode_akun,d.nama as nama_akun,b.kendaraan_id,
			b.blok_id, b.jumlah_jam
			FROM wrk_kegiatan_ht a 
			inner join wrk_kegiatan_log b on b.wrk_kegiatan_id=a.id
			inner  join acc_kegiatan c on b.acc_kegiatan_id=c.id
			inner join acc_akun d on c.acc_akun_id=d.id
			where a.lokasi_id =" . $lokasi_id . "  
			and a.tanggal >='" . $d1 . "' and a.tanggal <='" . $d2 . "'
			and a.is_posting=1";

			$res_wrk_alokasi = $this->db->query($q1)->result_array();
			$jumlah_transaksi = $jumlah_transaksi + count($res_wrk_alokasi);
				$total_jam_perbaikan = 0;
			/* ambil total jam dlm periode  */
			foreach ($res_wrk_alokasi as $wrk_alokasi) {
				$total_jam_perbaikan = $total_jam_perbaikan + $wrk_alokasi['jumlah_jam'];
			}



			/* Jika ada transaksi di kegiatan traksi Maka hitung dan jurnal */
			if ($res_wrk_alokasi) {

				$this->load->library('Autonumber');
				$no_jurnal = $this->autonumber->jurnal_auto($lokasi_id, $last_date_in_periode, 'ALWRK');
				$dataH = array(
					'no_jurnal' => $no_jurnal,
					'lokasi_id' => $lokasi_id,
					'tanggal' => $last_date_in_periode,
					'no_ref' => '',
					'ref_id' => null,
					'tipe_jurnal' => 'AUTO',
					'modul' => 'ALK_WORKSHOP',
					'keterangan' => 'ALK_WORKSHOP_' . $periodeAkunting['nama'],
					'is_posting' => 1,
					'diposting_oleh' => $this->user_id
				);
				$id_header = $this->AccJurnalModel->create_header($dataH);
				foreach ($res_wrk_alokasi as $alk) {
								
					$nilai_alokasi = ($alk['jumlah_jam'] / $total_jam_perbaikan) * $nilai;		
						$dataDebet = array(
							'lokasi_id' => $lokasi_id,
							'jurnal_id' => $id_header,
							'acc_akun_id' => $alk['acc_akun_id'], //akun ,
							'debet' => $nilai_alokasi,
							'kredit' => 0,
							'ket' => 'ALK_WORKSHOP:' . $alk['ket'],
							'no_referensi' => 'ALK_WORKSHOP:' . $periodeAkunting['nama'],
							'referensi_id' => NULL,
							'blok_stasiun_id' =>  $alk['blok_id'],
							'kegiatan_id' => $alk['kegiatan_id'],
							'kendaraan_mesin_id' =>  $alk['kendaraan_id'],
							'umur_tanam_blok' => NULL
						);
						$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataDebet);
						$dataKredit = array(
							'lokasi_id' => $lokasi_id,
							'jurnal_id' => $id_header,
							'acc_akun_id' => $workshop_dialokasi_akun, //akun ,
							'debet' => 0,
							'kredit' => ($nilai_alokasi),
							'ket' => 'ALK_WORKSHOP:' .   $alk['ket'],
							'no_referensi' => 'ALK_WORKSHOP:' . $periodeAkunting['nama'],
							'referensi_id' => NULL,
							'blok_stasiun_id' => NULL,
							'kegiatan_id' => NULL,
							'kendaraan_mesin_id' => NULL // $transit_account['kendaraan_mesin_id']
						);
						$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataKredit);
					
				}
			}
		}
		/* END Perbaikan KEndaraan di Workshop */
		$this->db->where('id', $id);
		$this->db->update('acc_periode_akunting', array('is_proses_workshop'    => '1', "tanggal_proses_workshop" => date('Y-m-d H:i:s')));
		$this->set_response(array("status" => "OK", "data" => 'Proses berhasil. ' . $jumlah_transaksi . " data diproses"), REST_Controller::HTTP_CREATED);
	}
	public function start_proses_alokasi_estate($id)
	{

		// $id = $this->post('id');
		$periodeAkunting = $this->AccPeriodeAkuntingModel->retrieve($id);
		if (empty($periodeAkunting)) {
			$this->set_response(array("status" => "NOT OK", "data" => 'null'), REST_Controller::HTTP_NOT_FOUND);
			return;
		}

		$lokasi_id = $periodeAkunting['lokasi_id'];
		$d1 = $periodeAkunting['tgl_awal'];
		$d2 = $periodeAkunting['tgl_akhir'];
		$last_date_in_periode = $d2;
		$res_transit_akun_traksi = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='TRAKSI_TRANSIT_REPARASI'")->row_array();
		if (empty($res_transit_akun_traksi)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$transit_akun_traksi_reparasi = $res_transit_akun_traksi['acc_akun_id'];
		$res_workshop_dialokasi_akun = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='WORKSHOP_DIALOKASI_AKUN'")->row_array();
		if (empty($res_workshop_dialokasi_akun)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}

		$workshop_dialokasi_akun = $res_workshop_dialokasi_akun['acc_akun_id'];

		/* Hapus dulu jurnal alokasi traksi jika sudah ada pada bulan tsb.*/
		$q = "delete from acc_jurnal_dt where jurnal_id in(select id from acc_jurnal_ht
                 where tanggal='" . $last_date_in_periode . "' and  modul='ALK_WORKSHOP'
				 and lokasi_id =" . $lokasi_id . ")";
		$this->db->query($q);
		$q = "delete from acc_jurnal_ht  
                 where tanggal='" . $last_date_in_periode . "' and  modul='ALK_WORKSHOP'
				 and lokasi_id =" . $lokasi_id . "";
		$this->db->query($q);

		/* Get InterUnit Akun /HUb RK */
		$retrieve_inter_akun = $this->db->query("SELECT * FROM acc_inter_unit
			where tipe='INTER' ")->result_array();
		$akun_inter = array();
		foreach ($retrieve_inter_akun as $key => $akun) {
			$akun_inter[$akun['lokasi_id']][$akun['lokasi_id_2']] = $akun['acc_akun_id'];
		}

		/* cek  total biaya transit ke workshop utk alokasi traksi */
		/* hard code akun transit: ('4110101', '4110102','4110103','4110104','4110105')*/
		$qtransit_account = "SELECT b.lokasi_id,sum(debet-kredit)as nilai 
		FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
		on a.id=b.jurnal_id
		inner join acc_akun c on b.acc_akun_id=c.id
		left join gbm_organisasi d on d.id=b.divisi_id 
		where 1=1 
		and b.lokasi_id =" . $lokasi_id . "
		and a.tanggal >='" . $d1 . "' and a.tanggal <='" . $d2 . "'
		and c.kode in ('4110101', '4110102','4110103','4110104','4110105')
		group by b.lokasi_id ";
		
		$res_transit_account = $this->db->query($qtransit_account)->row_array();
		
		$nilai = 0;
		$total_jam_perbaikan = 0;
		$jumlah_transaksi = 0;
		if ($res_transit_account) {
			/* cek total jam perbaikan traksi */
			$nilai = $res_transit_account['nilai'];

			/* START  perbaikan di kendaran worshop */
			$q1 = " SELECT a.*,b.acc_kegiatan_id,c.kode as kode_kegiatan,c.nama as nama_kegiatan,
			a.lama_perbaikan,b.ket,
			c.acc_akun_id,d.kode as kode_akun,d.nama as nama_akun,b.kendaraan_id,
			b.blok_id, b.jumlah_jam,e.tahuntanam,e.statusblok
			FROM wrk_kegiatan_ht a 
			inner join wrk_kegiatan_log b on b.wrk_kegiatan_id=a.id
			inner  join acc_kegiatan c on b.acc_kegiatan_id=c.id
			inner join acc_akun d on c.acc_akun_id=d.id
			left JOIN gbm_blok e ON b.blok_id=e.organisasi_id
			where a.lokasi_id =" . $lokasi_id . "  
			and a.tanggal >='" . $d1 . "' and a.tanggal <='" . $d2 . "'
			and a.is_posting=1";

			$res_wrk_alokasi = $this->db->query($q1)->result_array();
			$jumlah_transaksi = $jumlah_transaksi + count($res_wrk_alokasi);
		// 	$this->set_response(array("status" => "NOT OK", "data" =>  ($q1) ), REST_Controller::HTTP_CREATED);
		//  return;exit();
			$total_jam_perbaikan = 0;
			/* ambil total km/hm dlm periode utk kendaraannya */
			foreach ($res_wrk_alokasi as $wrk_alokasi) {
				$total_jam_perbaikan = $total_jam_perbaikan + $wrk_alokasi['jumlah_jam'];
			}


			/* Jika ada transaksi di kegiatan workshop Maka hitung dan jurnal */
			if ($res_wrk_alokasi) {

				$this->load->library('Autonumber');
				$no_jurnal = $this->autonumber->jurnal_auto($lokasi_id, $last_date_in_periode, 'ALWRK');
				$dataH = array(
					'no_jurnal' => $no_jurnal,
					'lokasi_id' => $lokasi_id,
					'tanggal' => $last_date_in_periode,
					'no_ref' => '',
					'ref_id' => null,
					'tipe_jurnal' => 'AUTO',
					'modul' => 'ALK_WORKSHOP',
					'keterangan' => 'ALK_WORKSHOP_' . $periodeAkunting['nama'],
					'is_posting' => 1,
					'diposting_oleh' => $this->user_id
				);
				$id_header = $this->AccJurnalModel->create_header($dataH);
				foreach ($res_wrk_alokasi as $alk) {
					$lokasi1 = $periodeAkunting["lokasi_id"];
					 $lokasi2 = $alk['lokasi_traksi_id'];
					if ($akun_inter[$lokasi1][$lokasi2]) {
						$inter_akun_id = $akun_inter[$lokasi1][$lokasi2];
					}
					if ($akun_inter[$lokasi2][$lokasi1]) {
						$inter_akun_id = $akun_inter[$lokasi2][$lokasi1];
					}
					$nilai_alokasi = ($alk['jumlah_jam'] / $total_jam_perbaikan) * $nilai;		
						$dataDebet = array(
							'lokasi_id' => $lokasi_id,
							'jurnal_id' => $id_header,
							'acc_akun_id' => $alk['acc_akun_id'], //akun ,
							'debet' => $nilai_alokasi,
							'kredit' => 0,
							'ket' => 'ALK_WORKSHOP:' . $alk['ket'],
							'no_referensi' => 'ALK_WORKSHOP:' . $periodeAkunting['nama'],
							'referensi_id' => NULL,
							'blok_stasiun_id' =>  $alk['blok_id'],
							'kegiatan_id' => $alk['kegiatan_id'],
							'kendaraan_mesin_id' =>  $alk['kendaraan_id'],
							'umur_tanam_blok' =>$alk['statusblok'], 
						);
						$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataDebet);
						$dataKredit = array(
							'lokasi_id' => $lokasi_id,
							'jurnal_id' => $id_header,
							'acc_akun_id' => $workshop_dialokasi_akun, //akun ,
							'debet' => 0,
							'kredit' => ($nilai_alokasi),
							'ket' => 'ALK_WORKSHOP:' .   $alk['ket'],
							'no_referensi' => 'ALK_WORKSHOP:' . $periodeAkunting['nama'],
							'referensi_id' => NULL,
							'blok_stasiun_id' => NULL,
							'kegiatan_id' => NULL,
							'kendaraan_mesin_id' => NULL // $transit_account['kendaraan_mesin_id']
						);
						$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataKredit);
					
				}
			}
		}
		/* END Perbaikan KEndaraan di Workshop */
		$this->db->where('id', $id);
		$this->db->update('acc_periode_akunting', array('is_proses_workshop'    => '1', "tanggal_proses_workshop" => date('Y-m-d H:i:s')));

		$this->set_response(array("status" => "OK", "data" => 'Proses berhasil. ' . $jumlah_transaksi . " data diproses"), REST_Controller::HTTP_CREATED);
	}
	public function start_proses_alokasi_estate_old($id)
	{

		$id = $this->post('id');
		$periodeAkunting = $this->AccPeriodeAkuntingModel->retrieve($id);
		if (empty($periodeAkunting)) {
			$this->set_response(array("status" => "NOT OK", "data" => 'null'), REST_Controller::HTTP_NOT_FOUND);
			return;
		}

		$lokasi_id = $periodeAkunting['lokasi_id'];
		$d1 = $periodeAkunting['tgl_awal'];
		$d2 = $periodeAkunting['tgl_akhir'];
		$last_date_in_periode = $d2;
		$res_transit_akun_traksi = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='TRAKSI_TRANSIT_REPARASI'")->row_array();
		if (empty($res_transit_akun_traksi)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$transit_akun_traksi_reparasi = $res_transit_akun_traksi['acc_akun_id'];
		$res_workshop_dialokasi_akun = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='WORKSHOP_DIALOKASI_AKUN'")->row_array();
		if (empty($res_workshop_dialokasi_akun)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}

		$workshop_dialokasi_akun = $res_workshop_dialokasi_akun['acc_akun_id'];

		/* Hapus dulu jurnal alokasi traksi jika sudah ada pada bulan tsb.*/
		$q = "delete from acc_jurnal_dt where jurnal_id in(select id from acc_jurnal_ht
                 where tanggal='" . $last_date_in_periode . "' and  modul='ALK_WORKSHOP'
				 and lokasi_id =" . $lokasi_id . ")";
		$this->db->query($q);
		$q = "delete from acc_jurnal_ht  
                 where tanggal='" . $last_date_in_periode . "' and  modul='ALK_WORKSHOP'
				 and lokasi_id =" . $lokasi_id . "";
		$this->db->query($q);

		/* Get InterUnit Akun /HUb RK */
		$retrieve_inter_akun = $this->db->query("SELECT * FROM acc_inter_unit
			where tipe='INTER' ")->result_array();
		$akun_inter = array();
		foreach ($retrieve_inter_akun as $key => $akun) {
			$akun_inter[$akun['lokasi_id']][$akun['lokasi_id_2']] = $akun['acc_akun_id'];
		}

		/* cek  total biaya transit ke workshop utk alokasi traksi */
		/* hard code akun transit: ('4110101', '4110102','4110103','4110104','4110105')*/
		$qtransit_account = "SELECT b.lokasi_id,b.divisi_id,d.kode as kode_workshop,d.nama as nama_workshop,sum(debet-kredit)as nilai 
		FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
		on a.id=b.jurnal_id
		inner join acc_akun c on b.acc_akun_id=c.id
		left join gbm_organisasi d on d.id=b.divisi_id 
		where 1=1 
		and b.lokasi_id =" . $lokasi_id . "
		and a.tanggal >='" . $d1 . "' and a.tanggal <='" . $d2 . "'
		and c.kode in ('4110101', '4110102','4110103','4110104','4110105')
		group by b.divisi_id,d.kode ,d.nama,b.lokasi_id ";
		// 	$this->set_response(array("status" => "NOT OK", "data" =>  ($qtransit_account) ), REST_Controller::HTTP_CREATED);
		// return;
		$res_transit_account = $this->db->query($qtransit_account)->row_array();
		$nilai = 0;
		$total_jam_perbaikan = 0;
		$jumlah_transaksi = 0;
		if ($res_transit_account) {
			/* cek total jam perbaikan traksi */
			$nilai = $res_transit_account['nilai'];

			/* START  perbaikan di kendaran worshop */
			$q1 = " SELECT a.*,b.acc_kegiatan_id,c.kode as kode_kegiatan,c.nama as nama_kegiatan,
			a.lama_perbaikan,b.ket,
			c.acc_akun_id,d.kode as kode_akun,d.nama as nama_akun,b.kendaraan_id,
			e.kode as kode_kendaraan,e.nama as nama_kendaraan,jumlah_jam,f.parent_id as lokasi_traksi_id
			FROM wrk_kegiatan_ht a 
			inner join wrk_kegiatan_log b on b.wrk_kegiatan_id=a.id
			inner  join acc_kegiatan c on b.acc_kegiatan_id=c.id
			inner join acc_akun d on c.acc_akun_id=d.id
			inner join trk_kendaraan e on b.kendaraan_id=e.id
			inner join gbm_organisasi f on e.traksi_id=f.id
			where a.lokasi_id =" . $lokasi_id . " and 
			a.workshop_id=" . $res_transit_account['divisi_id'] . "
			and a.tanggal >='" . $d1 . "' and a.tanggal <='" . $d2 . "'";

			$res_wrk_kendaraan = $this->db->query($q1)->result_array();
			$jumlah_transaksi = $jumlah_transaksi + count($res_wrk_kendaraan);
			$total_jam_perbaikan = 0;
			/* ambil total km/hm dlm periode utk kendaraannya */
			foreach ($res_wrk_kendaraan as $trk) {
				$total_jam_perbaikan = $total_jam_perbaikan + $trk['jumlah_jam'];
			}

			$q2 = " SELECT a.*,b.acc_kegiatan_id,c.kode as kode_kegiatan,c.nama as nama_kegiatan,
			a.lama_perbaikan,b.ket,
			c.acc_akun_id,d.kode as kode_akun,d.nama as nama_akun,b.blok_id,e.parent_id as divisi_id,
			e.kode as kode_blok,e.nama as nama_blok,f.kode as kode_afdeling,f.nama as nama_afdeling,
			jumlah_jam,g.parent_id as lokasi_kerja_id,h.statusblok
			FROM wrk_kegiatan_ht a 
			inner join wrk_kegiatan_log b on b.wrk_kegiatan_id=a.id
			inner  join acc_kegiatan c on b.acc_kegiatan_id=c.id
			inner join acc_akun d on c.acc_akun_id=d.id
			inner join gbm_organisasi e on b.blok_id=e.id
			inner join gbm_organisasi f on e.parent_id=f.id
			inner join gbm_organisasi g on f.parent_id=g.id
			inner join gbm_blok h on e.id=h.organisasi_id
			where a.lokasi_id =" . $lokasi_id . " and 
			a.workshop_id=" . $res_transit_account['divisi_id'] . "
			and a.tanggal >='" . $d1 . "' and a.tanggal <='" . $d2 . "'";
			$res_wrk_blok = $this->db->query($q2)->result_array();
			$jumlah_transaksi = $jumlah_transaksi + count($res_wrk_blok);

			/* ambil total km/hm dlm periode utk kendaraannya */
			foreach ($res_wrk_blok as $blk) {
				$total_jam_perbaikan = $total_jam_perbaikan + $blk['jumlah_jam'];
			}

			/* Jika ada transaksi di kegiatan traksi Maka hitung dan jurnal */
			if ($res_wrk_kendaraan) {

				$this->load->library('Autonumber');
				$no_jurnal = $this->autonumber->jurnal_auto($lokasi_id, $last_date_in_periode, 'ALTR');
				$dataH = array(
					'no_jurnal' => $no_jurnal,
					'lokasi_id' => $lokasi_id,
					'tanggal' => $last_date_in_periode,
					'no_ref' => '',
					'ref_id' => null,
					'tipe_jurnal' => 'AUTO',
					'modul' => 'ALK_WORKSHOP',
					'keterangan' => 'ALK_WORKSHOP_' . $periodeAkunting['nama'],
					'is_posting' => 1,
					'diposting_oleh' => $this->user_id
				);
				$id_header = $this->AccJurnalModel->create_header($dataH);
				foreach ($res_wrk_kendaraan as $trk) {
					$lokasi1 = $periodeAkunting["lokasi_id"];
					$lokasi2 = $trk['lokasi_traksi_id'];
					if ($akun_inter[$lokasi1][$lokasi2]) {
						$inter_akun_id = $akun_inter[$lokasi1][$lokasi2];
					}
					if ($akun_inter[$lokasi2][$lokasi1]) {
						$inter_akun_id = $akun_inter[$lokasi2][$lokasi1];
					}
					$nilai_alokasi = ($trk['jumlah_jam'] / $total_jam_perbaikan) * $nilai;
					if ($lokasi1 == $lokasi2) {		// JIKA TIDAK ASISTENSI BLOK				
						$dataDebet = array(
							'lokasi_id' => $lokasi_id,
							'jurnal_id' => $id_header,
							'acc_akun_id' => $trk['acc_akun_id'], //akun ,
							'debet' => $nilai_alokasi,
							'kredit' => 0,
							'ket' => 'ALK_WORKSHOP:' . $trk['nama_kendaraan'] . '-' . $trk['kode_kendaraan'] . ',  Kegiatan:' . $trk['kegiatan'] . ' Ket:' . $trk['ket'],
							'no_referensi' => 'ALK_WORKSHOP:' . $periodeAkunting['nama'],
							'referensi_id' => NULL,
							'blok_stasiun_id' => NULL,
							'kegiatan_id' => $trk['kegiatan_id'],
							'kendaraan_mesin_id' =>  $trk['kendaraan_id'],
							'umur_tanam_blok' => NULL
						);
						$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataDebet);
						$dataKredit = array(
							'lokasi_id' => $lokasi_id,
							'jurnal_id' => $id_header,
							'acc_akun_id' => $workshop_dialokasi_akun, //akun ,
							'debet' => 0,
							'kredit' => ($nilai_alokasi),
							'ket' => 'ALK_WORKSHOP:' . $trk['nama_kendaraan'] . '-' . $trk['kode_kendaraan'] . ', Kegiatan:' . $trk['kegiatan'] . ' Ket:' . $trk['ket'],
							'no_referensi' => 'ALK_WORKSHOP:' . $periodeAkunting['nama'],
							'referensi_id' => NULL,
							'blok_stasiun_id' => NULL,
							'kegiatan_id' => NULL,
							'kendaraan_mesin_id' => NULL // $transit_account['kendaraan_mesin_id']
						);
						$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataKredit);
					} else { // JIKA  ASISTENSI BLOK (KENDARAAN DIPINJAM OLEH LOKASI LAIN)	
						$dataDebet = array(
							'lokasi_id' => $lokasi1,
							'jurnal_id' => $id_header,
							'acc_akun_id' => $inter_akun_id, //akun ,
							'debet' => $nilai_alokasi,
							'kredit' => 0,
							'ket' => 'ALK_WORKSHOP:' . $trk['nama_kendaraan'] . '-' . $trk['kode_kendaraan'] . ',  Kegiatan:' . $trk['kegiatan'] . ' Ket:' . $trk['ket'],
							'no_referensi' => 'ALK_WORKSHOP:' . $periodeAkunting['nama'],
							'referensi_id' => NULL,
							'blok_stasiun_id' => NULL,
							'kegiatan_id' => NULL,
							'kendaraan_mesin_id' => NULL,
							'umur_tanam_blok' => NULL // $transit_account['kendaraan_mesin_id']
						);
						$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataDebet);
						$dataKredit = array(
							'lokasi_id' => $lokasi1,
							'jurnal_id' => $id_header,
							'acc_akun_id' => $workshop_dialokasi_akun, //akun ,
							'debet' => 0,
							'kredit' => ($nilai_alokasi),
							'ket' => 'ALK_WORKSHOP:' . $trk['nama_kendaraan'] . '-' . $trk['kode_kendaraan'] . ',  Kegiatan:' . $trk['kegiatan'] . ' Ket:' . $trk['ket'],
							'no_referensi' => 'ALK_WORKSHOP:' . $periodeAkunting['nama'],
							'referensi_id' => NULL,
							'blok_stasiun_id' => NULL,
							'kegiatan_id' => NULL,
							'kendaraan_mesin_id' => NULL // $transit_account['kendaraan_mesin_id']
						);
						$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataKredit);

						$dataDebet = array(
							'lokasi_id' => $lokasi2,
							'jurnal_id' => $id_header,
							'acc_akun_id' => $trk['acc_akun_id'], //akun ,
							'debet' => $nilai_alokasi,
							'kredit' => 0,
							'ket' => 'ALK_WORKSHOP:' . $trk['nama_kendaraan'] . '-' . $trk['kode_kendaraan'] . ',  Kegiatan:' . $trk['kegiatan'] . ' Ket:' . $trk['ket'],
							'no_referensi' => 'ALK_WORKSHOP:' . $periodeAkunting['nama'],
							'referensi_id' => NULL,
							'blok_stasiun_id' => NULL,
							'kegiatan_id' => $trk['kegiatan_id'],
							'kendaraan_mesin_id' =>  $trk['kendaraan_id'],
							'umur_tanam_blok' => NULL  // $transit_account['kendaraan_mesin_id']
						);
						$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataDebet);
						$dataKredit = array(
							'lokasi_id' => $lokasi2,
							'jurnal_id' => $id_header,
							'acc_akun_id' => $inter_akun_id, //akun ,
							'debet' => 0,
							'kredit' => ($nilai_alokasi),
							'ket' => 'ALK_WORKSHOP:' . $trk['nama_kendaraan'] . '-' . $trk['kode_kendaraan'] . ',  Kegiatan:' . $trk['kegiatan'] . ' Ket:' . $trk['ket'],
							'no_referensi' => 'ALK_WORKSHOP:' . $periodeAkunting['nama'],
							'referensi_id' => NULL,
							'blok_stasiun_id' => NULL,
							'kegiatan_id' => NULL,
							'kendaraan_mesin_id' => NULL // $transit_account['kendaraan_mesin_id']
						);
						$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataKredit);
					}
				}
			}
			/* END Perbaikan KEndaraan di Workshop

			/*START perbaikan melalui kegeiatan per blok/cost center selain kendaraan */
			/* Jika ada transaksi di kegiatan traksi Maka hitung dan jurnal */
			if ($res_wrk_blok) {

				$this->load->library('Autonumber');
				$no_jurnal = $this->autonumber->jurnal_auto($lokasi_id, $last_date_in_periode, 'ALTR');
				$dataH = array(
					'no_jurnal' => $no_jurnal,
					'lokasi_id' => $lokasi_id,
					'tanggal' => $last_date_in_periode,
					'no_ref' => '',
					'ref_id' => null,
					'tipe_jurnal' => 'AUTO',
					'modul' => 'ALK_WORKSHOP',
					'keterangan' => 'ALK_WORKSHOP_' . $periodeAkunting['nama'],
					'is_posting' => 1,
					'diposting_oleh' => $this->user_id
				);
				$id_header = $this->AccJurnalModel->create_header($dataH);
				foreach ($res_wrk_blok as $blk) {
					$lokasi1 = $periodeAkunting["lokasi_id"];
					$lokasi2 = $blk['lokasi_kerja_id'];
					if ($akun_inter[$lokasi1][$lokasi2]) {
						$inter_akun_id = $akun_inter[$lokasi1][$lokasi2];
					}
					if ($akun_inter[$lokasi2][$lokasi1]) {
						$inter_akun_id = $akun_inter[$lokasi2][$lokasi1];
					}
					$nilai_alokasi = ($trk['jumlah_jam'] / $total_jam_perbaikan) * $nilai;
					if ($lokasi1 == $lokasi2) {		// JIKA TIDAK ASISTENSI BLOK				
						$dataDebet = array(
							'lokasi_id' => $lokasi_id,
							'jurnal_id' => $id_header,
							'acc_akun_id' => $blk['acc_akun_id'], //akun ,
							'debet' => $nilai_alokasi,
							'kredit' => 0,
							'ket' => 'ALK_WORKSHOP:  BLOK:' . $blk['blok'] . ', Kegiatan:' . $blk['kegiatan'] . ' Ket:' . $blk['ket'],
							'no_referensi' => 'ALK_WORKSHOP:' . $periodeAkunting['nama'],
							'referensi_id' => NULL,
							'blok_stasiun_id' => $blk['blok_id'],
							'kegiatan_id' => $blk['kegiatan_id'],
							'kendaraan_mesin_id' => NULL,
							'umur_tanam_blok' => $blk['statusblok'] // $transit_account['kendaraan_mesin_id']
						);
						$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataDebet);
						$dataKredit = array(
							'lokasi_id' => $lokasi_id,
							'jurnal_id' => $id_header,
							'acc_akun_id' => $workshop_dialokasi_akun, //akun ,
							'debet' => 0,
							'kredit' => ($nilai_alokasi),
							'ket' => 'ALK_WORKSHOP: BLOK:' . $blk['blok'] . ', Kegiatan:' . $blk['kegiatan'] . ' Ket:' . $blk['ket'],
							'no_referensi' => 'ALK_WORKSHOP:' . $periodeAkunting['nama'],
							'referensi_id' => NULL,
							'blok_stasiun_id' => NULL,
							'kegiatan_id' => NULL,
							'kendaraan_mesin_id' => NULL // $transit_account['kendaraan_mesin_id']
						);
						$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataKredit);
					} else { // JIKA  ASISTENSI BLOK (KENDARAAN DIPINJAM OLEH LOKASI LAIN)	
						$dataDebet = array(
							'lokasi_id' => $lokasi1,
							'jurnal_id' => $id_header,
							'acc_akun_id' => $inter_akun_id, //akun ,
							'debet' => $nilai_alokasi,
							'kredit' => 0,
							'ket' => 'ALK_WORKSHOP:  BLOK:' . $blk['blok'] . ', Kegiatan:' . $blk['kegiatan'] . ' Ket:' . $blk['ket'],
							'no_referensi' => 'ALK_WORKSHOP:' . $periodeAkunting['nama'],
							'referensi_id' => NULL,
							'blok_stasiun_id' => $blk['blok_id'],
							'kegiatan_id' => $blk['kegiatan_id'],
							'kendaraan_mesin_id' => NULL,
							'umur_tanam_blok' => $blk['statusblok']  // $transit_account['kendaraan_mesin_id']
						);
						$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataDebet);
						$dataKredit = array(
							'lokasi_id' => $lokasi1,
							'jurnal_id' => $id_header,
							'acc_akun_id' => $workshop_dialokasi_akun, //akun ,
							'debet' => 0,
							'kredit' => ($nilai_alokasi),
							'ket' => 'ALK_WORKSHOP: BLOK:' . $blk['blok'] . ', Kegiatan:' . $blk['kegiatan'] . ' Ket:' . $blk['ket'],
							'no_referensi' => 'ALK_WORKSHOP:' . $periodeAkunting['nama'],
							'referensi_id' => NULL,
							'blok_stasiun_id' => NULL,
							'kegiatan_id' => NULL,
							'kendaraan_mesin_id' => NULL // $transit_account['kendaraan_mesin_id']
						);
						$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataKredit);

						$dataDebet = array(
							'lokasi_id' => $lokasi2,
							'jurnal_id' => $id_header,
							'acc_akun_id' => $trk['acc_akun_id'], //akun ,
							'debet' => $nilai_alokasi,
							'kredit' => 0,
							'ket' => 'ALK_WORKSHOP: BLOK:' . $blk['blok'] . ', Kegiatan:' . $blk['kegiatan'] . ' Ket:' . $blk['ket'],
							'no_referensi' => 'ALK_WORKSHOP:' . $periodeAkunting['nama'],
							'referensi_id' => NULL,
							'blok_stasiun_id' => $blk['blok_id'],
							'kegiatan_id' => $blk['kegiatan_id'],
							'kendaraan_mesin_id' => NULL,
							'umur_tanam_blok' => $blk['statusblok']  // $transit_account['kendaraan_mesin_id']
						);
						$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataDebet);
						$dataKredit = array(
							'lokasi_id' => $lokasi2,
							'jurnal_id' => $id_header,
							'acc_akun_id' => $inter_akun_id, //akun ,
							'debet' => 0,
							'kredit' => ($nilai_alokasi),
							'ket' => 'ALK_WORKSHOP: BLOK:' . $blk['blok'] . ', Kegiatan:' . $blk['kegiatan'] . ' Ket:' . $blk['ket'],
							'no_referensi' => 'ALK_WORKSHOP:' . $periodeAkunting['nama'],
							'referensi_id' => NULL,
							'blok_stasiun_id' => NULL,
							'kegiatan_id' => NULL,
							'kendaraan_mesin_id' => NULL // $transit_account['kendaraan_mesin_id']
						);
						$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataKredit);
					}
				}
			}

			// $this->load->library('Autonumber');
			// $no_jurnal = $this->autonumber->jurnal_auto($lokasi_id, $last_date_in_periode, 'ALWK');
			// $dataH = array(
			// 	'no_jurnal' => $no_jurnal,
			// 	'lokasi_id' => $lokasi_id,
			// 	'tanggal' => $last_date_in_periode,
			// 	'no_ref' => '',
			// 	'ref_id' => null,
			// 	'tipe_jurnal' => 'AUTO',
			// 	'modul' => 'ALK_WORKSHOP',
			// 	'keterangan' => 'ALK_WORKSHOP:' . $res_transit_account['nama_workshop'],
			// 	'is_posting' => 1,
			// 	'diposting_oleh' => $this->user_id
			// );
			// $id_header = $this->AccJurnalModel->create_header($dataH);
			// $dataKredit = array(
			// 	'lokasi_id' => $lokasi_id,
			// 	'jurnal_id' => $id_header,
			// 	'acc_akun_id' => $workshop_dialokasi_akun, //akun ,
			// 	'debet' => 0,
			// 	'kredit' => ($nilai),
			// 	'ket' => 'ALK_WORKSHOP:' . $res_transit_account['nama_workshop'],
			// 	'no_referensi' => '',
			// 	'referensi_id' => NULL,
			// 	'blok_stasiun_id' => NULL,
			// 	'kegiatan_id' => NULL,
			// 	'kendaraan_mesin_id' => NULL
			// );
			// $id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataKredit);
			// foreach ($res_wrk as $trk) {
			// 	$nilai_alokasi = ($trk['jumlah_jam'] / $total_jam_perbaikan) * $nilai;
			// 	$dataDebet = array(
			// 		'lokasi_id' => $lokasi_id,
			// 		'jurnal_id' => $id_header,
			// 		'acc_akun_id' => $trk['acc_akun_id'], //$transit_akun_traksi_reparasi, //akun ,
			// 		'debet' => $nilai_alokasi,
			// 		'kredit' => 0,
			// 		'ket' => 'ALK_WORKSHOP:' . $res_transit_account['nama_workshop'] . ', Kendaraan:' . $trk['nama_kendaraan'] . '',
			// 		'no_referensi' => '',
			// 		'referensi_id' => NULL,
			// 		'blok_stasiun_id' => NULL,
			// 		'kegiatan_id' => NULL,
			// 		'kendaraan_mesin_id' => $trk['kendaraan_mesin_id']
			// 	);
			// 	$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataDebet);
			// }
			// foreach ($res_wrk as $wrk) {
			// 	$nilai_alokasi = ($wrk['jumlah_jam'] / $total_jam_perbaikan) * $nilai;
			// 	$dataDebet = array(
			// 		'lokasi_id' => $lokasi_id,
			// 		'jurnal_id' => $id_header,
			// 		'acc_akun_id' => $wrk['acc_akun_id'], //akun ,
			// 		'debet' => $nilai_alokasi,
			// 		'kredit' => 0,
			// 		'ket' => 'ALK_WORKSHOP:' . $res_transit_account['nama_workshop'] . ', blok/mesin:' . $wrk['nama_blok'] . ' - ' . $wrk['nama_Afdeling'],
			// 		'no_referensi' => '',
			// 		'referensi_id' => NULL,
			// 		'divisi_id' => $wrk['divisi_id'],
			// 		'blok_stasiun_id' => $wrk['blok_id'],
			// 		'kegiatan_id' => $wrk['kegiatan_id'],
			// 		'kendaraan_mesin_id' => NULL
			// 	);
			// 	$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataDebet);
			// }
		}


		$this->set_response(array("status" => "OK", "data" => 'Proses berhasil. ' . $jumlah_transaksi . " data diproses"), REST_Controller::HTTP_CREATED);
	}
	function hitungPremi_post()
	{
		$input = $this->post();
		$this->hitung_premi($input);
	}
	function hitung_premi($input)
	{
		$resGaji = $this->db->query("select * from payroll_karyawan_gaji where karyawan_id=" . $input['karyawan_id']['id'] . " ")->row_array();
		$upahharian = ($resGaji['gapok'] / 25);
		$res = array(
			'rp_hk' => $upahharian,
			'premi' => 0,

		);

		if (!empty($res)) {
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
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


	function print_slip_get($segment_3 = '')
	{

		$id = (int)$segment_3;
		$data = [];

		$queryHeader = "SELECT 
		a.*,
		b.nama AS lokasi,
		-- c.nama AS kendaraan,
		-- c.kode AS kode_kendaraan,
		d.nama as workshop,
		a.id AS id
		FROM wrk_kegiatan_ht a 
		INNER JOIN gbm_organisasi b ON a.lokasi_id=b.id
		-- INNER JOIN trk_kendaraan c ON a.kendaraan_mesin_id=c.id
		INNER JOIN gbm_organisasi d ON a.workshop_id=d.id
		WHERE a.id=" . $id . "
		";
		$dataHeader = $this->db->query($queryHeader)->row_array();

		$queryDetail = "SELECT 
		a.*,
		b.nama AS karyawan,
		a.id AS id
		FROM wrk_kegiatan_dt a 
		LEFT JOIN karyawan b on a.karyawan_id=b.id
		WHERE a.wrk_kegiatan_id=" . $id . "
		";
		$dataDetail = $this->db->query($queryDetail)->result_array();

		// $queryDetailItem = "SELECT 
		// a.*,
		// c.nama AS item,
		// d.nama AS uom,
		// a.id AS id
		// FROM wrk_kegiatan_item a 
		// INNER JOIN inv_item c on a.item_id=c.id 
		// LEFT JOIN gbm_uom d on c.uom_id=d.id
		// WHERE a.wrk_kegiatan_id=" . $id . "
		// ";
		// $dataDetailItem = $this->db->query($queryDetailItem)->result_array();

		$queryDetailLog = "SELECT 
		a.*,
		c.nama AS nama_kegiatan,
		c.kode AS kode_kegiatan,
		d.nama AS nama_blok,
		d.kode AS kode_blok,
		e.nama AS nama_kendaraan,
		e.kode AS kode_kendaraan,
		a.id AS id
		FROM wrk_kegiatan_log a 
		left JOIN acc_kegiatan c on a.acc_kegiatan_id=c.id 
		LEFT JOIN gbm_organisasi d on a.blok_id=d.id
		LEFT JOIN trk_kendaraan e on a.kendaraan_id=e.id
		WHERE a.wrk_kegiatan_id=" . $id . "
		";
		$dataDetailLog = $this->db->query($queryDetailLog)->result_array();

		// $perminta = $this->InvPermintaanBarangModel->print_slip($id);
		$data['header'] = 	$dataHeader;
		$data['detail'] = 	$dataDetail;
		// $data['detail_item'] = 	$dataDetailItem;
		$data['detail_log'] = 	$dataDetailLog;

		$html = $this->load->view('WrkKegiatan_print', $data, true);

		$filename = 'report_' . time();
		$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		// echo $html;
	}





	function laporan_material_detail_post()
	{
		error_reporting(0);

		// $id = (int)$segment_3;
		$data = [];

		$input = [
			'no_po' => '01/PO/DPA',
			'format_laporan' => 'view',
			'lokasi_id' => 252,
			'tgl_mulai' => '2020-01-01',
			'tgl_akhir' => '2022-12-18',
		];
		// $format_laporan=$input['format_laporan'];
		// $lokasi_id=$input['lokasi_id'];
		// $tanggal_awal=$input['tgl_mulai'];
		// $tanggal_akhir=$input['tgl_akhir'];

		$format_laporan = $this->post('format_laporan', true);
		$lokasi_id = $this->post('lokasi_id', true);
		$tanggal_awal = $this->post('tgl_mulai', true);
		$tanggal_akhir = $this->post('tgl_akhir', true);


		$queryPo = "SELECT
		a.*,
		b.*,
		b.tanggal AS tanggal,
		b.no_transaksi AS no_transaksi,
		c.nama AS lokasi,
		d.nama AS material,
		e.kode AS kode_kendaraan,
		e.nama AS nama_kendaraan,
		a.id AS id
		
		FROM wrk_kegiatan_item a
		
		LEFT JOIN wrk_kegiatan_ht b ON a.wrk_kegiatan_id=b.id
		LEFT JOIN gbm_organisasi c ON b.lokasi_id=c.id
		LEFT JOIN inv_item d ON a.item_id=d.id
		LEFT JOIN trk_kendaraan e ON b.kendaraan_mesin_id=e.id

		where b.tanggal between  '" . $tanggal_awal . "' and  '" . $tanggal_akhir . "'	
		";

		$filter_lokasi = "Semua";
		if ($lokasi_id) {
			$queryPo = $queryPo . " and b.lokasi_id=" . $lokasi_id . "";
			$res = $this->db->query("select * from gbm_organisasi where id=" . $lokasi_id . "")->row_array();
			$filter_lokasi = $res['nama'];
		}

		$dataPo = $this->db->query($queryPo)->result_array();

		$data['po'] = 	$dataPo;
		$data['filter_lokasi'] = 	$filter_lokasi;
		$data['filter_tgl_awal'] = 	$tanggal_awal;
		$data['filter_tgl_akhir'] = $tanggal_akhir;

		$html = $this->load->view('Wrk_Kegiatan_Material_Laporan', $data, true);

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


	function laporan_kegiatan_detail_post()
	{
		error_reporting(0);

		// $id = (int)$segment_3;
		$data = [];

		$input = [
			'no_po' => '01/PO/DPA',
			'format_laporan' => 'view',
			'lokasi_id' => 252,
			'kendaraan_id' => 11,
			'blok_id' => 565,
			'tgl_mulai' => '2020-01-01',
			'tgl_akhir' => '2022-12-18',
		];
		$format_laporan = $input['format_laporan'];
		$lokasi_id = $input['lokasi_id'];
		$kendaraan_id = $input['kendaraan_id'];
		$blok_id = $input['blok_id'];
		$tanggal_awal = $input['tgl_mulai'];
		$tanggal_akhir = $input['tgl_akhir'];

		// $format_laporan = $this->post('format_laporan', true);
		// $lokasi_id = $this->post('lokasi_id', true);
		// $tanggal_awal = $this->post('tgl_mulai', true);
		// $tanggal_akhir = $this->post('tgl_akhir', true);


		$queryPo = "SELECT
		a.*,
		b.*,
		b.tanggal AS tanggal,
		b.no_transaksi AS no_transaksi,
		c.nama AS blok,
		d.nama AS kegiatan,
		e.nama AS nama_kendaraan,
		e.kode AS kode_kendaraan,
		a.id AS id
		
		FROM wrk_kegiatan_log a
		
		LEFT JOIN wrk_kegiatan_ht b ON a.wrk_kegiatan_id=b.id
		LEFT JOIN gbm_organisasi c ON a.blok_id=c.id
		LEFT JOIN acc_kegiatan d ON a.acc_kegiatan_id=d.id
		LEFT JOIN trk_kendaraan e ON a.kendaraan_id=e.id

		where b.tanggal between  '" . $tanggal_awal . "' and  '" . $tanggal_akhir . "'	
		";

		$filter_lokasi = "Semua";
		if ($lokasi_id) {
			$queryPo = $queryPo . " and b.lokasi_id=" . $lokasi_id . "";
			$res = $this->db->query("select * from gbm_organisasi where id=" . $lokasi_id . "")->row_array();
			$filter_lokasi = $res['nama'];
		}

		if ($kendaraan_id) {
			$queryPo = $queryPo . " and a.kendaraan_id=" . $kendaraan_id . "";
		}

		if ($blok_id) {
			$queryPo = $queryPo . " and a.blok_id=" . $blok_id . "";
		}

		$dataPo = $this->db->query($queryPo)->result_array();

		$data['po'] = 	$dataPo;
		$data['filter_lokasi'] = 	$filter_lokasi;
		$data['filter_tgl_awal'] = 	$tanggal_awal;
		$data['filter_tgl_akhir'] = $tanggal_akhir;

		$html = $this->load->view('Wrk_Kegiatan_Kegiatan_Laporan', $data, true);

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


	function laporan_frestasi_detail_post()
	{

		// $id = (int)$segment_3;
		$data = [];

		$input = [
			'no_po' => '01/PO/DPA',
			'lokasi_id' => 252,
			'tgl_mulai' => '2020-01-01',
			'tgl_akhir' => '2022-12-30',
		];

		$format_laporan = $this->post('format_laporan', true);
		$lokasi_id = $this->post('lokasi_id', true);
		$tanggal_awal = $this->post('tgl_mulai', true);
		$tanggal_akhir = $this->post('tgl_akhir', true);

		// $lokasi_id=$input['lokasi_id'];
		// $tanggal_awal=$input['tgl_mulai'];
		// $tanggal_akhir=$input['tgl_akhir'];

		$queryPo = "SELECT
		a.*,
		b.tanggal AS tanggal,
		b.no_transaksi AS no_transaksi,
		c.nama AS karyawan,
		e.kode AS kode_kendaraan,
		e.nama AS nama_kendaraan,
		a.id AS id
		
		FROM wrk_kegiatan_dt a
		
		LEFT JOIN wrk_kegiatan_ht b on a.wrk_kegiatan_id=b.id
		LEFT JOIN karyawan c ON a.karyawan_id=c.id
		LEFT JOIN trk_kendaraan e ON b.kendaraan_mesin_id=e.id

		where b.tanggal between  '" . $tanggal_awal . "' and  '" . $tanggal_akhir . "'	
		";

		$filter_lokasi = "Semua";
		if ($lokasi_id) {
			$queryPo = $queryPo . " and b.lokasi_id=" . $lokasi_id . "";
			$res = $this->db->query("select * from gbm_organisasi where id=" . $lokasi_id . "")->row_array();
			$filter_lokasi = $res['nama'];
		}

		$dataPo = $this->db->query($queryPo)->result_array();

		$data['po'] = 	$dataPo;
		// $data['filter_gudang'] = 	$filter_gudang;
		$data['filter_lokasi'] = 	$filter_lokasi;
		$data['filter_tgl_awal'] = 	$tanggal_awal;
		$data['filter_tgl_akhir'] = $tanggal_akhir;

		$html = $this->load->view('Wrk_Kegiatan_Frestasi_Laporan', $data, true);

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
