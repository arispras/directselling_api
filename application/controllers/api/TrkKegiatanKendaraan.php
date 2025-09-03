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

class TrkKegiatanKendaraan extends BD_Controller //
{
	public $user_id;
	public $theCredential;

	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('GbmOrganisasiModel');
		$this->load->model('TrkKegiatanKendaraanModel');
		$this->load->model('AccJurnalModel');
		$this->load->model('AccJurnalUpahModel');
		$this->load->model('AccPeriodeAkuntingModel');
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

		$query  = "	SELECT 
		a.*,
		b.nama as lokasi,
		c.nama as traksi,
		d.nama as mandor,
		e.nama as kendaraan,
		e.kode as kode_kendaraan,
		f.user_full_name AS dibuat,
		g.user_full_name AS diubah,
		DATE_FORMAT(a.diposting_tanggal,'%d/%m/%Y') AS dipost_tgl,
		h.user_full_name AS diposting		
		FROM trk_kegiatan_kendaraan_ht a 
		inner join gbm_organisasi b on a.lokasi_id=b.id
		inner join gbm_organisasi c on a.traksi_id=c.id 
		left join karyawan d on a.mandor_id =d.id
		left join trk_kendaraan e on a.kendaraan_id=e.id
		LEFT JOIN fwk_users f ON a.dibuat_oleh = f.id
		LEFT JOIN fwk_users g ON a.diubah_oleh = g.id
		LEFT JOIN fwk_users h ON a.diposting_oleh = h.id
		";
		$search = array('a.no_transaksi', 'a.tanggal', 'b.nama', 'c.nama', 'e.nama', 'e.kode');
		$where  = null;
		$isWhere = " 1=1 ";
		if ($param['tgl_mulai'] && $param['tgl_mulai']) {
			$isWhere = " tanggal between '" . $param['tgl_mulai'] . "' and '" . $param['tgl_akhir'] . "'";
		}

		if ($param['lokasi_id']) {
			$isWhere = $isWhere . " and lokasi_id =" . $param['lokasi_id'] . "";
		} else {
			$isWhere = $isWhere . " and  lokasi_id in
			(select location_id from fwk_users_location where user_id=" . $this->user_id . ")";
		}
		if (!empty($param['kendaraan_id'])) {
			$isWhere = $isWhere .  "  and a.kendaraan_id=" . $param['kendaraan_id'] . "";
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

	function listKegKendaraan_post()
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
		c.nama as traksi,
		d.nama as mandor,
		e.nama as kendaraan,
		e.kode as kode_kendaraan 
		FROM `trk_kegiatan_kendaraan_ht` a 
		inner join gbm_organisasi b on a.lokasi_id=b.id
		inner join gbm_organisasi c on a.traksi_id=c.id 
		left join karyawan d on a.mandor_id =d.id
		left join trk_kendaraan e on a.kendaraan_id=e.id 

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

		$html = $this->load->view('Trk_kegiatan_log_list', $data, true);

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		echo $html;
	}

	function listTrkKegiatanDetail_post()
	{
		$format_laporan =  $this->post('format_laporan', true);

		$data = [];

		$input = [
			'lokasi_id' => 252,
			'tgl_mulai' => '2022-08-01',
			'tgl_akhir' => '2022-08-01',
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
		c.nama AS kendaraan,
		d.nama AS mandor,
		d.nama AS kerani,
		d.nama AS asisten,
		e.nama AS traksi,
		a.id AS id
		FROM trk_kegiatan_kendaraan_ht a 
		LEFT JOIN gbm_organisasi b ON a.lokasi_id=b.id
		LEFT JOIN trk_kendaraan c ON a.kendaraan_id=c.id
		LEFT JOIN karyawan d ON a.mandor_id=d.id
		LEFT JOIN karyawan dd ON a.kerani_id=dd.id
		LEFT JOIN karyawan ddd ON a.asisten_id=ddd.id
		LEFT JOIN gbm_organisasi e ON a.traksi_id=e.id
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
			FROM trk_kegiatan_kendaraan_dt a 
			LEFT JOIN karyawan b on a.karyawan_id=b.id
			WHERE a.trk_kegiatan_kendaraan_id=" . $hd['id'] . "";
			$dataDtl = $this->db->query($querydetail)->result_array();
			// var_dump($dataDtl)
			$querydetailKegiatan = "SELECT 
			a.*,
			b.nama AS kegiatan,
			c.nama AS blok,
			a.id AS id
			FROM trk_kegiatan_kendaraan_log a 
			LEFT JOIN acc_kegiatan b on a.acc_kegiatan_id=b.id
			LEFT JOIN gbm_organisasi c on a.blok_id=c.id
			WHERE a.trk_kegiatan_kendaraan_id=" . $hd['id'] . "";
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

		$html = $this->load->view('Trk_Kegiatan_kendaraan_list_detail', $data, true);

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		echo $html;
	}

	function index_get($id = '')
	{
		$retrieve = $this->TrkKegiatanKendaraanModel->retrieve_by_id($id);
		$retrieve['detail'] = $this->TrkKegiatanKendaraanModel->retrieve_detail($id);
		$retrieve['detail_log'] = $this->TrkKegiatanKendaraanModel->retrieve_detail_item($id);


		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function getDetail_get($id = '')
	{
		$retrieve = $this->TrkKegiatanKendaraanModel->retrieve_detail($id);
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function index_post()
	{
		$input = $this->post();
		/* START VALIDASI BLOK TIDAK DIISI JIKA AKUN->Kelompok Biaya=PNN,PMK,PML*/
		$details = $input['details_kegiatan'];
		foreach ($details as $key => $value) {
			$blok_id = $value['blok_id']['id'];
			$kegiatan_id = $value['kegiatan_id']['id'];
			// $traksi_id =  $value['traksi_id']['id'];

			if (is_null($blok_id) || empty($blok_id)) {
				$res_akun = $this->db->query("	SELECT a.kode AS kode_kegiatan,a.nama AS nama_kegiatan,b.kode AS kode_akun,b.nama AS nama_akun FROM acc_kegiatan a 
						INNER JOIN acc_akun b ON a.acc_akun_id=b.id WHERE b.kelompok_biaya 
						IN('PNN','PMK','PML') 
						AND a.id=" . $kegiatan_id . "")->row_array();

				if ($res_akun) {
					$msg = "Kegiatan:" . $res_akun['nama_kegiatan'] . " harus diiisi Blok";
					$this->set_response(array("status" => "NOT OK", "data" => $msg), REST_Controller::HTTP_OK);
					return;
				}
			}
			// if (is_null($traksi_id) || empty($traksi_id)) {
			// 	$res_akun = $this->db->query("	SELECT a.kode AS kode_kegiatan,a.nama AS nama_kegiatan,b.kode AS kode_akun,b.nama AS nama_akun FROM acc_kegiatan a 
			// 		INNER JOIN acc_akun b ON a.acc_akun_id=b.id WHERE b.kelompok_biaya 
			// 		IN('TRK') 
			// 		AND a.id=" . $kegiatan_id . "")->row_array();

			// 	if ($res_akun) {
			// 		$msg = "Kegiatan:" . $res_akun['nama_kegiatan'] . " harus diiisi Kendaraan/Alat Berat/Mesin";
			// 		$this->set_response(array("status" => "NOT OK", "data" => $msg), REST_Controller::HTTP_OK);
			// 		return;
			// 	}
			// }
		}
		/* END VALIDASI BLOK TIDAK DIISI */

		$input['dibuat_oleh'] = $this->user_id;
		$input['diubah_oleh'] = $this->user_id;

		$this->load->library('Autonumber');
		$input['no_transaksi'] = $this->autonumber->trk_kegiatan_kendaraan($input['lokasi_id']['id'], $input['tanggal']);

		//$input['no_spat'] = $this->getLastNumber('pks_pengolahan_ht', 'no_spat', 'SPAT');
		// var_dump($input);

		$res = $this->TrkKegiatanKendaraanModel->create($input);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'trk_kegiatan_kendaraan', 'action' => 'new', 'entity_id' => $res);
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
		$details = $data['details_kegiatan'];
		foreach ($details as $key => $value) {
			$blok_id = $value['blok_id']['id'];
			$kegiatan_id = $value['kegiatan_id']['id'];
			// $traksi_id =  $value['traksi_id']['id'];

			if (is_null($blok_id) || empty($blok_id)) {
				$res_akun = $this->db->query("	SELECT a.kode AS kode_kegiatan,a.nama AS nama_kegiatan,b.kode AS kode_akun,b.nama AS nama_akun FROM acc_kegiatan a 
					INNER JOIN acc_akun b ON a.acc_akun_id=b.id WHERE b.kelompok_biaya 
					IN('PNN','PMK','PML') 
					AND a.id=" . $kegiatan_id . "")->row_array();

				if ($res_akun) {
					$msg = "Kegiatan:" . $res_akun['nama_kegiatan'] . " harus diiisi Blok";
					$this->set_response(array("status" => "NOT OK", "data" => $msg), REST_Controller::HTTP_OK);
					return;
				}
			}
			// if (is_null($traksi_id) || empty($traksi_id)) {
			// 	$res_akun = $this->db->query("	SELECT a.kode AS kode_kegiatan,a.nama AS nama_kegiatan,b.kode AS kode_akun,b.nama AS nama_akun FROM acc_kegiatan a 
			// 		INNER JOIN acc_akun b ON a.acc_akun_id=b.id WHERE b.kelompok_biaya 
			// 		IN('TRK') 
			// 		AND a.id=" . $kegiatan_id . "")->row_array();

			// 	if ($res_akun) {
			// 		$msg = "Kegiatan:" . $res_akun['nama_kegiatan'] . " harus diiisi Kendaraan/Alat Berat/Mesin";
			// 		$this->set_response(array("status" => "NOT OK", "data" => $msg), REST_Controller::HTTP_OK);
			// 		return;
			// 	}
			// }
		}
		/* END VALIDASI BLOK TIDAK DIISI */

		$res = $this->TrkKegiatanKendaraanModel->update($id, $data);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'trk_kegiatan_kendaraan', 'action' => 'edit', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	public function index_delete($id)
	{

		$res = $this->TrkKegiatanKendaraanModel->delete($id);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id' => $id)), 'entity' => 'trk_kegiatan_kendaraan', 'action' => 'delete', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function posting_normal_post($segment_3 = null)
	{
		$id = (int) $segment_3;
		$res_akun_transit_upah = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='TRAKSI_TRANSIT_UPAH'")->row_array();
		if (empty($res_akun_transit_upah)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$res_akun_transit_premi = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='TRAKSI_TRANSIT_PREMI'")->row_array();
		if (empty($res_akun_transit_premi)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		// $res_akun_panen_upah_pengawas = $this->db->query("SELECT * from acc_auto_jurnal 
		// where kode='PANEN_KEBUN_UPAH_PENGAWAS'")->row_array();
		// if (empty($res_akun_kredit)) {
		// 	$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
		// 	return;
		// }
		// $res_akun_panen_premi_pengawas = $this->db->query("SELECT * from acc_auto_jurnal 
		// where kode='PANEN_KEBUN_PREMI_PENGAWAS'")->row_array();
		// if (empty($res_akun_kredit)) {
		// 	$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
		// 	return;
		// }
		$akun_debet_transit_premi = $res_akun_transit_premi['acc_akun_id_debet'];
		$akun_kredit_transit_premi = $res_akun_transit_premi['acc_akun_id_kredit'];
		$akun_debet_transit_upah = $res_akun_transit_upah['acc_akun_id_debet'];
		$akun_kredit_transit_upah = $res_akun_transit_upah['acc_akun_id_kredit'];
		// $akun_debet_pengawas_premi = $res_akun_panen_premi_pengawas['acc_akun_id_debet'];
		// $akun_kredit_pengawas_premi = $res_akun_panen_premi_pengawas['acc_akun_id_kredit'];
		// $akun_debet_pengawas_upah = $res_akun_panen_upah_pengawas['acc_akun_id_debet'];
		// $akun_kredit_pengawas_upah = $res_akun_panen_upah_pengawas['acc_akun_id_kredit'];

		$retrieve_header = $this->TrkKegiatanKendaraanModel->retrieve_by_id($id);
		$retrieve_org = $this->GbmOrganisasiModel->retrieve($retrieve_header['lokasi_id']);
		$retrieve_detail = $this->db->query("SELECT a.*, b.*,c.lokasi_tugas_id, c.nip,c.nama as nama_karyawan, d.kode as kode_kendaraan,d.nama as nama_kendaraan,d.no_kendaraan  
		FROM trk_kegiatan_kendaraan_ht a 
		inner join trk_kegiatan_kendaraan_dt b on a.id=b.trk_kegiatan_kendaraan_id 
		inner join karyawan c on b.karyawan_id=c.id
		inner join trk_kendaraan d on a.kendaraan_id=d.id 
		where b.trk_kegiatan_kendaraan_id=" . $id . "")->result_array();

		// Hapus jurnal jika sdh ada berdasarkan no refernsi dan MOdul  //
		$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'TRAKSI');
		$total_pendapatan = 0;
		$retrieve_inter_akun = $this->db->query("SELECT * FROM acc_inter_unit
		where tipe='INTER' ")->result_array();
		$akun_inter = array();
		foreach ($retrieve_inter_akun as $key => $akun) {
			$akun_inter[$akun['lokasi_id']][$akun['lokasi_id_2']] = $akun['acc_akun_id'];
		}
		if ($retrieve_org['tipe'] == 'ESTATE') { // JIka bukan ESTATE maka Tidak ada jurnal.Jurnalnya saat poting payroll
			// Data HEADER
			$this->load->library('Autonumber');
			$no_jurnal = $this->autonumber->jurnal_auto($retrieve_header['lokasi_id'], $retrieve_header['tanggal'], 'TRK');

			$dataH = array(
				'no_jurnal' => $no_jurnal,
				'lokasi_id' => $retrieve_header['lokasi_id'],
				'tanggal' => $retrieve_header['tanggal'],
				'no_ref' => $retrieve_header['no_transaksi'],
				'ref_id' => $retrieve_header['id'],
				'tipe_jurnal' => 'AUTO',
				'modul' => 'TRAKSI',
				'keterangan' => 'TRANSAKSI TRAKSI',
				'is_posting' => 1,

			);
			$id_header = $this->AccJurnalModel->create_header($dataH);
			$total_pendapatan = 0;
			foreach ($retrieve_detail as $key => $value) {
				$upah = $value['rupiah_hk'];
				$premi = $value['premi']; // nilai premi 
				/* jurnal biaya gaji terjadi di LOKASI KARYAWAN TERDAFTAR */
				/* karyawan */
				if ($upah > 0) {
					$dataDebet = array(
						'jurnal_id' => $id_header,
						'lokasi_id' => $value['lokasi_tugas_id'],
						'acc_akun_id' => $akun_debet_transit_upah,
						'debet' => ($upah),
						'kredit' => 0,
						'ket' => 'Biaya Upah Traksi Kendaraan: ' . $value['nama_kendaraan'] . ' , Karyawan: ' . $value['nama_karyawan'] . "",
						'no_referensi' => $retrieve_header['no_transaksi'],
						'referensi_id' => NULL,
						'blok_stasiun_id' => NULL,
						'kegiatan_id' => NULL, //kegiatan panen,
						'kendaraan_mesin_id' =>  $retrieve_header['kendaraan_id'],
						'karyawan_id' => $value['karyawan_id'], //karyawan,
					);
					$total_pendapatan =	$total_pendapatan + ($value['rupiah_hk'] + $value['premi']);
					$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataDebet);
					// Data KREDIT
					$dataKredit = array(
						'jurnal_id' => $id_header,
						'lokasi_id' => $value['lokasi_tugas_id'],
						'acc_akun_id' =>  $akun_kredit_transit_upah, //$value['acc_akun_id'], // Akun transit
						'debet' => 0,
						'kredit' => $upah, // Akun Lawan Biaya
						'ket' => 'Biaya Upah Traksi Kendaraan: ' . $value['nama_kendaraan'] . ' , Karyawan: ' . $value['nama_karyawan'] . "",
						'no_referensi' => $retrieve_header['no_transaksi'],
						'referensi_id' => NULL,
						'blok_stasiun_id' => NULL,
						'kegiatan_id' => NULL, // $value['kegiatan_id'],
						'kendaraan_mesin_id' => NULL //$retrieve_header['kendaraan_id']
					);
					$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataKredit);
				}
				if ($premi > 0) {
					$dataDebet = array(
						'jurnal_id' => $id_header,
						'lokasi_id' => $value['lokasi_tugas_id'],
						'acc_akun_id' => $akun_debet_transit_premi,
						'debet' => ($premi),
						'kredit' => 0,
						'ket' => 'Biaya Premi Traksi Kendaraan: ' . $value['nama_kendaraan'] . ' , Karyawan: ' . $value['nama_karyawan'] . ",",
						'no_referensi' => $retrieve_header['no_transaksi'],
						'referensi_id' => NULL,
						'blok_stasiun_id' => NULL,
						'kegiatan_id' => NULL, //kegiatan panen,
						'kendaraan_mesin_id' =>  $retrieve_header['kendaraan_id'],
						'karyawan_id' => $value['karyawan_id'], //karyawan,
					);
					$total_pendapatan =	$total_pendapatan + ($value['rupiah_hk'] + $value['premi']);
					$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataDebet);
					// Data KREDIT
					$dataKredit = array(
						'lokasi_id' => $value['lokasi_tugas_id'],
						'jurnal_id' => $id_header,
						'acc_akun_id' =>  $akun_kredit_transit_premi, //$value['acc_akun_id'], // Akun transit
						'debet' => 0,
						'kredit' => $premi, // Akun Lawan Biaya
						'ket' => 'Biaya Premi Traksi Kendaraan: ' . $value['nama_kendaraan'] . ' , Karyawan: ' . $value['nama_karyawan'] . ",",
						'no_referensi' => $retrieve_header['no_transaksi'],
						'referensi_id' => NULL,
						'blok_stasiun_id' => NULL,
						'kegiatan_id' => NULL, // $value['kegiatan_id'],
						'kendaraan_mesin_id' => NULL // $retrieve_header['kendaraan_id']
					);
					$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataKredit);
				}
			}
		}
		$res = $this->TrkKegiatanKendaraanModel->posting($id, null);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id' => $id)), 'entity' => 'trk_kegiatan_kendaraan', 'action' => 'posting', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function posting_post($segment_3 = null)
	{
		$id = (int) $segment_3;
		$res_akun_transit_upah = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='TRAKSI_TRANSIT_UPAH'")->row_array();
		if (empty($res_akun_transit_upah)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$res_akun_transit_premi = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='TRAKSI_TRANSIT_PREMI'")->row_array();
		if (empty($res_akun_transit_premi)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		// $res_akun_panen_upah_pengawas = $this->db->query("SELECT * from acc_auto_jurnal 
		// where kode='PANEN_KEBUN_UPAH_PENGAWAS'")->row_array();
		// if (empty($res_akun_kredit)) {
		// 	$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
		// 	return;
		// }
		// $res_akun_panen_premi_pengawas = $this->db->query("SELECT * from acc_auto_jurnal 
		// where kode='PANEN_KEBUN_PREMI_PENGAWAS'")->row_array();
		// if (empty($res_akun_kredit)) {
		// 	$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
		// 	return;
		// }
		$akun_debet_transit_premi = $res_akun_transit_premi['acc_akun_id_debet'];
		$akun_kredit_transit_premi = $res_akun_transit_premi['acc_akun_id_kredit'];
		$akun_debet_transit_upah = $res_akun_transit_upah['acc_akun_id_debet'];
		$akun_kredit_transit_upah = $res_akun_transit_upah['acc_akun_id_kredit'];
		// $akun_debet_pengawas_premi = $res_akun_panen_premi_pengawas['acc_akun_id_debet'];
		// $akun_kredit_pengawas_premi = $res_akun_panen_premi_pengawas['acc_akun_id_kredit'];
		// $akun_debet_pengawas_upah = $res_akun_panen_upah_pengawas['acc_akun_id_debet'];
		// $akun_kredit_pengawas_upah = $res_akun_panen_upah_pengawas['acc_akun_id_kredit'];

		$retrieve_header = $this->TrkKegiatanKendaraanModel->retrieve_by_id($id);
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
		$retrieve_org = $this->GbmOrganisasiModel->retrieve($retrieve_header['lokasi_id']);
		$retrieve_detail = $this->db->query("SELECT a.*, b.*,c.lokasi_tugas_id, c.nip,c.nama as nama_karyawan, d.kode as kode_kendaraan,d.nama as nama_kendaraan,d.no_kendaraan  
		FROM trk_kegiatan_kendaraan_ht a 
		inner join trk_kegiatan_kendaraan_dt b on a.id=b.trk_kegiatan_kendaraan_id 
		inner join karyawan c on b.karyawan_id=c.id
		inner join trk_kendaraan d on a.kendaraan_id=d.id 
		where b.trk_kegiatan_kendaraan_id=" . $id . "")->result_array();

		// Hapus jurnal jika sdh ada berdasarkan no refernsi dan MOdul  //
		$this->AccJurnalUpahModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'TRAKSI');
		$total_pendapatan = 0;
		$retrieve_inter_akun = $this->db->query("SELECT * FROM acc_inter_unit
		where tipe='INTER' ")->result_array();
		$akun_inter = array();
		foreach ($retrieve_inter_akun as $key => $akun) {
			$akun_inter[$akun['lokasi_id']][$akun['lokasi_id_2']] = $akun['acc_akun_id'];
		}
		if ($retrieve_org['tipe'] == 'ESTATE') { // JIka bukan ESTATE maka Tidak ada jurnal.Jurnalnya saat poting payroll
			// Data HEADER
			$this->load->library('Autonumber');
			$no_jurnal = $this->autonumber->jurnal_upah_auto($retrieve_header['lokasi_id'], $retrieve_header['tanggal'], 'TRK');

			$dataH = array(
				'no_jurnal' => $no_jurnal,
				'lokasi_id' => $retrieve_header['lokasi_id'],
				'tanggal' => $retrieve_header['tanggal'],
				'no_ref' => $retrieve_header['no_transaksi'],
				'ref_id' => $retrieve_header['id'],
				'tipe_jurnal' => 'AUTO',
				'modul' => 'TRAKSI',
				'keterangan' => 'TRANSAKSI TRAKSI',
				'is_posting' => 1,

			);
			$id_header = $this->AccJurnalUpahModel->create_header($dataH);
			$total_pendapatan = 0;
			foreach ($retrieve_detail as $key => $value) {
				$upah = $value['rupiah_hk'];
				$premi = $value['premi']; // nilai premi 

				/* JIKA LOKASI KARYAWAN TERDAFTAR SAMA DENGAN LOKASI TRAKSI */
				if ($value['lokasi_tugas_id'] == $retrieve_header['lokasi_id']) {
					/* karyawan */
					if ($upah > 0) {
						$dataDebet = array(
							'jurnal_id' => $id_header,
							'lokasi_id' => $retrieve_header['lokasi_id'],
							'acc_akun_id' => $akun_debet_transit_upah,
							'debet' => ($upah),
							'kredit' => 0,
							'ket' => 'Biaya Upah Traksi Kendaraan: ' . $value['nama_kendaraan'] . ' , Karyawan: ' . $value['nama_karyawan'] . "",
							'no_referensi' => $retrieve_header['no_transaksi'],
							'referensi_id' => NULL,
							'blok_stasiun_id' => NULL,
							'kegiatan_id' => NULL, //kegiatan panen,
							'kendaraan_mesin_id' =>  $retrieve_header['kendaraan_id'],
							'karyawan_id' => $value['karyawan_id'], //karyawan,
							'tipe' => 'upah',
							'hk' => $value['jumlah_hk']
						);
						$total_pendapatan =	$total_pendapatan + ($value['rupiah_hk'] + $value['premi']);
						$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
						// Data KREDIT
						$dataKredit = array(
							'jurnal_id' => $id_header,
							'lokasi_id' => $retrieve_header['lokasi_id'],
							'acc_akun_id' =>  $akun_kredit_transit_upah, //$value['acc_akun_id'], // Akun transit
							'debet' => 0,
							'kredit' => $upah, // Akun Lawan Biaya
							'ket' => 'Biaya Upah Traksi Kendaraan: ' . $value['nama_kendaraan'] . ' , Karyawan: ' . $value['nama_karyawan'] . "",
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
							'jurnal_id' => $id_header,
							'lokasi_id' => $retrieve_header['lokasi_id'],
							'acc_akun_id' => $akun_debet_transit_premi,
							'debet' => ($premi),
							'kredit' => 0,
							'ket' => 'Biaya Premi Traksi Kendaraan: ' . $value['nama_kendaraan'] . ' , Karyawan: ' . $value['nama_karyawan'] . ",",
							'no_referensi' => $retrieve_header['no_transaksi'],
							'referensi_id' => NULL,
							'blok_stasiun_id' => NULL,
							'kegiatan_id' => NULL, //kegiatan panen,
							'kendaraan_mesin_id' =>  $retrieve_header['kendaraan_id'],
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
							'ket' => 'Biaya Premi Traksi Kendaraan: ' . $value['nama_kendaraan'] . ' , Karyawan: ' . $value['nama_karyawan'] . ",",
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
				} else {
					$inter_akun_id = null;
					if ($akun_inter[$retrieve_header['lokasi_id']][$value['lokasi_tugas_id']]) {
						$inter_akun_id = $akun_inter[$retrieve_header['lokasi_id']][$value['lokasi_tugas_id']];
					}
					if ($akun_inter[$value['lokasi_tugas_id']][$retrieve_header['lokasi_id']]) {
						$inter_akun_id = $akun_inter[$value['lokasi_tugas_id']][$retrieve_header['lokasi_id']];
					}
					if ($upah > 0) {
						/* jurnal lokasi traksi */
						$dataDebet = array(
							'jurnal_id' => $id_header,
							'lokasi_id' => $retrieve_header['lokasi_id'],
							'acc_akun_id' => $akun_debet_transit_upah,
							'debet' => ($upah),
							'kredit' => 0,
							'ket' => 'Biaya Upah Traksi Kendaraan: ' . $value['nama_kendaraan'] . ' , Karyawan: ' . $value['nama_karyawan'] . "",
							'no_referensi' => $retrieve_header['no_transaksi'],
							'referensi_id' => NULL,
							'blok_stasiun_id' => NULL,
							'kegiatan_id' => NULL, //kegiatan panen,
							'kendaraan_mesin_id' =>  $retrieve_header['kendaraan_id'],
							'karyawan_id' => $value['karyawan_id'], //karyawan,
							'tipe' => 'upah',
							'hk' => $value['jumlah_hk']
						);
						$total_pendapatan =	$total_pendapatan + ($value['rupiah_hk'] + $value['premi']);
						$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
						// Data KREDIT
						$dataKredit = array(
							'jurnal_id' => $id_header,
							'lokasi_id' => $retrieve_header['lokasi_id'],
							'acc_akun_id' =>  $inter_akun_id, //$value['acc_akun_id'], // Akun transit
							'debet' => 0,
							'kredit' => $upah, // Akun Lawan Biaya
							'ket' => 'Biaya Upah Traksi Kendaraan: ' . $value['nama_kendaraan'] . ' , Karyawan: ' . $value['nama_karyawan'] . "",
							'no_referensi' => $retrieve_header['no_transaksi'],
							'referensi_id' => NULL,
							'blok_stasiun_id' => NULL,
							'kegiatan_id' => NULL, // $value['kegiatan_id'],
							'kendaraan_mesin_id' => NULL,
							'tipe' => 'upah',
							'hk' => 0
						);
						$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);

						/* jurnal lokasi karyawan terrdaftar */
						$dataDebet = array(
							'jurnal_id' => $id_header,
							'lokasi_id' => $value['lokasi_tugas_id'],
							'acc_akun_id' => $inter_akun_id,
							'debet' => ($upah),
							'kredit' => 0,
							'ket' => 'Biaya Upah Traksi Kendaraan: ' . $value['nama_kendaraan'] . ' , Karyawan: ' . $value['nama_karyawan'] . "",
							'no_referensi' => $retrieve_header['no_transaksi'],
							'referensi_id' => NULL,
							'blok_stasiun_id' => NULL,
							'kegiatan_id' => NULL, //kegiatan panen,
							'kendaraan_mesin_id' =>  $retrieve_header['kendaraan_id'],
							'karyawan_id' => $value['karyawan_id'], //karyawan,
							'tipe' => 'upah',
							'hk' => $value['jumlah_hk']
						);
						$total_pendapatan =	$total_pendapatan + ($value['rupiah_hk'] + $value['premi']);
						$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
						// Data KREDIT
						$dataKredit = array(
							'jurnal_id' => $id_header,
							'lokasi_id' => $value['lokasi_tugas_id'],
							'acc_akun_id' =>  $akun_kredit_transit_upah, //$value['acc_akun_id'], // Akun transit
							'debet' => 0,
							'kredit' => $upah, // Akun Lawan Biaya
							'ket' => 'Biaya Upah Traksi Kendaraan: ' . $value['nama_kendaraan'] . ' , Karyawan: ' . $value['nama_karyawan'] . "",
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
						/* juranal lokasi traksi */
						$dataDebet = array(
							'jurnal_id' => $id_header,
							'lokasi_id' => $retrieve_header['lokasi_id'],
							'acc_akun_id' => $akun_debet_transit_premi,
							'debet' => ($premi),
							'kredit' => 0,
							'ket' => 'Biaya Premi Traksi Kendaraan: ' . $value['nama_kendaraan'] . ' , Karyawan: ' . $value['nama_karyawan'] . ",",
							'no_referensi' => $retrieve_header['no_transaksi'],
							'referensi_id' => NULL,
							'blok_stasiun_id' => NULL,
							'kegiatan_id' => NULL, //kegiatan panen,
							'kendaraan_mesin_id' =>  $retrieve_header['kendaraan_id'],
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
							'acc_akun_id' =>  $inter_akun_id, //$value['acc_akun_id'], // Akun transit
							'debet' => 0,
							'kredit' => $premi, // Akun Lawan Biaya
							'ket' => 'Biaya Premi Traksi Kendaraan: ' . $value['nama_kendaraan'] . ' , Karyawan: ' . $value['nama_karyawan'] . ",",
							'no_referensi' => $retrieve_header['no_transaksi'],
							'referensi_id' => NULL,
							'blok_stasiun_id' => NULL,
							'kegiatan_id' => NULL, // $value['kegiatan_id'],
							'kendaraan_mesin_id' => NULL,
							'tipe' => 'premi',
							'hk' => 0
						);
						$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);

						$dataDebet = array(
							'jurnal_id' => $id_header,
							'lokasi_id' => $value['lokasi_tugas_id'],
							'acc_akun_id' => $inter_akun_id,
							'debet' => ($premi),
							'kredit' => 0,
							'ket' => 'Biaya Premi Traksi Kendaraan: ' . $value['nama_kendaraan'] . ' , Karyawan: ' . $value['nama_karyawan'] . ",",
							'no_referensi' => $retrieve_header['no_transaksi'],
							'referensi_id' => NULL,
							'blok_stasiun_id' => NULL,
							'kegiatan_id' => NULL, //kegiatan panen,
							'kendaraan_mesin_id' =>  $retrieve_header['kendaraan_id'],
							'karyawan_id' => $value['karyawan_id'], //karyawan,
							'tipe' => 'premi',
							'hk' => 0
						);
						$total_pendapatan =	$total_pendapatan + ($value['rupiah_hk'] + $value['premi']);
						$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
						// Data KREDIT
						$dataKredit = array(
							'lokasi_id' => $value['lokasi_tugas_id'],
							'jurnal_id' => $id_header,
							'acc_akun_id' =>  $akun_kredit_transit_premi, //$value['acc_akun_id'], // Akun transit
							'debet' => 0,
							'kredit' => $premi, // Akun Lawan Biaya
							'ket' => 'Biaya Premi Traksi Kendaraan: ' . $value['nama_kendaraan'] . ' , Karyawan: ' . $value['nama_karyawan'] . ",",
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
			$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'TRAKSI');
			$qjurnalTemp = "SELECT b.lokasi_id, a.id,SUM(debet)AS debet ,SUM(kredit)AS kredit ,sum(hk)AS hk,acc_akun_id,blok_stasiun_id,kegiatan_id,
				umur_tanam_blok ,kendaraan_mesin_id,c.nama AS blok,d.nama AS kendaraan
				FROM acc_jurnal_upah_ht a INNER JOIN acc_jurnal_upah_dt b
				ON a.id=b.jurnal_id
				LEFT JOIN gbm_organisasi c ON b.blok_stasiun_id=c.id
				LEFT JOIN trk_kendaraan d ON b.kendaraan_mesin_id=d.id
				WHERE a.id=" . $id_header . "
				GROUP by b.lokasi_id,acc_akun_id,blok_stasiun_id,kegiatan_id,umur_tanam_blok,a.id,kendaraan_mesin_id,c.nama,d.nama
				ORDER BY debet desc";
			$resJurnalTemp = $this->db->query($qjurnalTemp)->result_array();
			if ($resJurnalTemp) {
				$no_jurnal = $this->autonumber->jurnal_auto($retrieve_header['lokasi_id'], $retrieve_header['tanggal'], 'TRK');

				$dataH = array(
					'no_jurnal' => $no_jurnal,
					'lokasi_id' => $retrieve_header['lokasi_id'],
					'tanggal' => $retrieve_header['tanggal'],
					'no_ref' => $retrieve_header['no_transaksi'],
					'ref_id' => $retrieve_header['id'],
					'tipe_jurnal' => 'AUTO',
					'modul' => 'TRAKSI',
					'keterangan' => 'TRANSAKSI TRAKSI',
					'is_posting' => 1,
				);
				$id_header2 = $this->AccJurnalModel->create_header($dataH);

				foreach ($resJurnalTemp as $key => $JurnalTemp) {
					$ket = '';
					if ($JurnalTemp['kendaraan']) {
						$ket = 'Traksi (Upah/premi) Kendaraan: ' . $JurnalTemp['kendaraan'];
					} else {
						$ket = 'Traksi (Upah/premi)';
					}
					if ($JurnalTemp['hk'] > 0) {
						$ket = $ket . ' ' . $JurnalTemp['hk'] . 'Hk';
					}
					$dataDebetKredit = array(
						'lokasi_id' => $JurnalTemp['lokasi_id'], //$retrieve_header['lokasi_id'],
						'jurnal_id' => $id_header2,
						'acc_akun_id' => $JurnalTemp['acc_akun_id'], //$value['acc_akun_id'],
						'debet' => $JurnalTemp['debet'],
						'kredit' => $JurnalTemp['kredit'], // Akun Lawan Biaya
						'ket' => $ket,
						'no_referensi' => $retrieve_header['no_transaksi'],
						'referensi_id' => NULL,
						'blok_stasiun_id' => $JurnalTemp['blok_stasiun_id'],
						'kegiatan_id' => $JurnalTemp['kegiatan_id'],  // $value['kegiatan_id'],
						'kendaraan_mesin_id' => $JurnalTemp['kendaraan_mesin_id'],
						'karyawan_id' => ($JurnalTemp['debet'] > 0) ? 0 : NULL,

					);
					$id_dtl = $this->AccJurnalModel->create_detail($id_header2, $dataDebetKredit);
				}
			}
		}
		$input['diposting_oleh'] = $this->user_id;
		$res = $this->TrkKegiatanKendaraanModel->posting($id, $input);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id' => $id)), 'entity' => 'trk_kegiatan_kendaraan', 'action' => 'posting', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function posting_batch_post($lokasi_id = null, $t1 = null, $t2 = null)
	{
		$res_akun_transit_upah = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='TRAKSI_TRANSIT_UPAH'")->row_array();
		if (empty($res_akun_transit_upah)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$res_akun_transit_premi = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='TRAKSI_TRANSIT_PREMI'")->row_array();
		if (empty($res_akun_transit_premi)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}

		$akun_debet_transit_premi = $res_akun_transit_premi['acc_akun_id_debet'];
		$akun_kredit_transit_premi = $res_akun_transit_premi['acc_akun_id_kredit'];
		$akun_debet_transit_upah = $res_akun_transit_upah['acc_akun_id_debet'];
		$akun_kredit_transit_upah = $res_akun_transit_upah['acc_akun_id_kredit'];

		$res_transaksi_kendaraan = $this->db->query("SELECT * from trk_kegiatan_kendaraan_ht where  
		tanggal between '" . $t1 . "' and '" . $t2 . "' and is_posting=0 
		and lokasi_id=" . $lokasi_id . " order by tanggal")->result_array();
		$id = null;
		$jum = 0;
		$id_header = 0;
		foreach ($res_transaksi_kendaraan  as $key => $retrieve_header) {
			$jum++;
			$id = $retrieve_header['id'];
			// $retrieve_header = $this->TrkKegiatanKendaraanModel->retrieve_by_id($id);
			$retrieve_org = $this->GbmOrganisasiModel->retrieve($retrieve_header['lokasi_id']);
			$retrieve_detail = $this->db->query("SELECT a.*, b.*,c.lokasi_tugas_id,c.nip,c.nama as nama_karyawan, d.kode as kode_kendaraan,d.nama as nama_kendaraan,d.no_kendaraan  
			FROM trk_kegiatan_kendaraan_ht a 
			inner join trk_kegiatan_kendaraan_dt b on a.id=b.trk_kegiatan_kendaraan_id 
			inner join karyawan c on b.karyawan_id=c.id
			inner join trk_kendaraan d on a.kendaraan_id=d.id 
			where b.trk_kegiatan_kendaraan_id=" . $id . "")->result_array();

			// Hapus jurnal jika sdh ada berdasarkan no refernsi dan MOdul  //
			$this->AccJurnalUpahModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'TRAKSI');
			$total_pendapatan = 0;
			$retrieve_inter_akun = $this->db->query("SELECT * FROM acc_inter_unit
			where tipe='INTER' ")->result_array();
			$akun_inter = array();
			foreach ($retrieve_inter_akun as $key => $akun) {
				$akun_inter[$akun['lokasi_id']][$akun['lokasi_id_2']] = $akun['acc_akun_id'];
			}
			if ($retrieve_org['tipe'] == 'ESTATE') { // JIka bukan ESTATE maka Tidak ada jurnal.Jurnalnya saat poting payroll
				// Data HEADER
				$this->load->library('Autonumber');
				$no_jurnal = $this->autonumber->jurnal_upah_auto($retrieve_header['lokasi_id'], $retrieve_header['tanggal'], 'TRK');

				$dataH = array(
					'no_jurnal' => $no_jurnal,
					'lokasi_id' => $retrieve_header['lokasi_id'],
					'tanggal' => $retrieve_header['tanggal'],
					'no_ref' => $retrieve_header['no_transaksi'],
					'ref_id' => $retrieve_header['id'],
					'tipe_jurnal' => 'AUTO',
					'modul' => 'TRAKSI',
					'keterangan' => 'TRANSAKSI TRAKSI',
					'is_posting' => 1,

				);
				$id_header = $this->AccJurnalUpahModel->create_header($dataH);
				$total_pendapatan = 0;
				foreach ($retrieve_detail as $key => $value) {
					$upah = $value['rupiah_hk'];
					$premi = $value['premi']; // nilai premi 

					/* JIKA LOKASI KARYAWAN TERDAFTAR SAMA DENGAN LOKASI TRAKSI */
					if ($value['lokasi_tugas_id'] == $retrieve_header['lokasi_id']) {
						/* karyawan */
						if ($upah > 0) {
							$dataDebet = array(
								'jurnal_id' => $id_header,
								'lokasi_id' => $retrieve_header['lokasi_id'],
								'acc_akun_id' => $akun_debet_transit_upah,
								'debet' => ($upah),
								'kredit' => 0,
								'ket' => 'Biaya Upah Traksi Kendaraan: ' . $value['nama_kendaraan'] . ' , Karyawan: ' . $value['nama_karyawan'] . "",
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL,
								'kegiatan_id' => NULL, //kegiatan panen,
								'kendaraan_mesin_id' =>  $retrieve_header['kendaraan_id'],
								'karyawan_id' => $value['karyawan_id'], //karyawan,
								'tipe' => 'upah',
								'hk' => $value['jumlah_hk']
							);
							$total_pendapatan =	$total_pendapatan + ($value['rupiah_hk'] + $value['premi']);
							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
							// Data KREDIT
							$dataKredit = array(
								'jurnal_id' => $id_header,
								'lokasi_id' => $retrieve_header['lokasi_id'],
								'acc_akun_id' =>  $akun_kredit_transit_upah, //$value['acc_akun_id'], // Akun transit
								'debet' => 0,
								'kredit' => $upah, // Akun Lawan Biaya
								'ket' => 'Biaya Upah Traksi Kendaraan: ' . $value['nama_kendaraan'] . ' , Karyawan: ' . $value['nama_karyawan'] . "",
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL,
								'kegiatan_id' => NULL, // $value['kegiatan_id'],
								'kendaraan_mesin_id' => NULL, //$retrieve_header['kendaraan_id']
								'tipe' => 'upah',
								'hk' => 0
							);
							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
						}
						if ($premi > 0) {
							$dataDebet = array(
								'jurnal_id' => $id_header,
								'lokasi_id' => $retrieve_header['lokasi_id'],
								'acc_akun_id' => $akun_debet_transit_premi,
								'debet' => ($premi),
								'kredit' => 0,
								'ket' => 'Biaya Premi Traksi Kendaraan: ' . $value['nama_kendaraan'] . ' , Karyawan: ' . $value['nama_karyawan'] . ",",
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL,
								'kegiatan_id' => NULL, //kegiatan panen,
								'kendaraan_mesin_id' =>  $retrieve_header['kendaraan_id'],
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
								'ket' => 'Biaya Premi Traksi Kendaraan: ' . $value['nama_kendaraan'] . ' , Karyawan: ' . $value['nama_karyawan'] . ",",
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL,
								'kegiatan_id' => NULL, // $value['kegiatan_id'],
								'kendaraan_mesin_id' => NULL, // $retrieve_header['kendaraan_id']
								'tipe' => 'premi',
								'hk' => 0
							);
							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
						}
					} else {
						$inter_akun_id = null;
						if ($akun_inter[$retrieve_header['lokasi_id']][$value['lokasi_tugas_id']]) {
							$inter_akun_id = $akun_inter[$retrieve_header['lokasi_id']][$value['lokasi_tugas_id']];
						}
						if ($akun_inter[$value['lokasi_tugas_id']][$retrieve_header['lokasi_id']]) {
							$inter_akun_id = $akun_inter[$value['lokasi_tugas_id']][$retrieve_header['lokasi_id']];
						}
						if ($upah > 0) {
							/* jurnal lokasi traksi */
							$dataDebet = array(
								'jurnal_id' => $id_header,
								'lokasi_id' => $retrieve_header['lokasi_id'],
								'acc_akun_id' => $akun_debet_transit_upah,
								'debet' => ($upah),
								'kredit' => 0,
								'ket' => 'Biaya Upah Traksi Kendaraan: ' . $value['nama_kendaraan'] . ' , Karyawan: ' . $value['nama_karyawan'] . "",
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL,
								'kegiatan_id' => NULL, //kegiatan panen,
								'kendaraan_mesin_id' =>  $retrieve_header['kendaraan_id'],
								'karyawan_id' => $value['karyawan_id'], //karyawan,
								'tipe' => 'upah',
								'hk' => $value['jumlah_hk']
							);
							$total_pendapatan =	$total_pendapatan + ($value['rupiah_hk'] + $value['premi']);
							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
							// Data KREDIT
							$dataKredit = array(
								'jurnal_id' => $id_header,
								'lokasi_id' => $retrieve_header['lokasi_id'],
								'acc_akun_id' =>  $inter_akun_id, //$value['acc_akun_id'], // Akun transit
								'debet' => 0,
								'kredit' => $upah, // Akun Lawan Biaya
								'ket' => 'Biaya Upah Traksi Kendaraan: ' . $value['nama_kendaraan'] . ' , Karyawan: ' . $value['nama_karyawan'] . "",
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL,
								'kegiatan_id' => NULL, // $value['kegiatan_id'],
								'kendaraan_mesin_id' => NULL, //$retrieve_header['kendaraan_id']
								'tipe' => 'upah',
								'hk' => 0
							);
							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);

							/* jurnal lokasi karyawan terrdaftar */
							$dataDebet = array(
								'jurnal_id' => $id_header,
								'lokasi_id' => $value['lokasi_tugas_id'],
								'acc_akun_id' => $inter_akun_id,
								'debet' => ($upah),
								'kredit' => 0,
								'ket' => 'Biaya Upah Traksi Kendaraan: ' . $value['nama_kendaraan'] . ' , Karyawan: ' . $value['nama_karyawan'] . "",
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL,
								'kegiatan_id' => NULL, //kegiatan panen,
								'kendaraan_mesin_id' =>  $retrieve_header['kendaraan_id'],
								'karyawan_id' => $value['karyawan_id'], //karyawan,
								'tipe' => 'upah',
								'hk' => $value['jumlah_hk']
							);
							$total_pendapatan =	$total_pendapatan + ($value['rupiah_hk'] + $value['premi']);
							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
							// Data KREDIT
							$dataKredit = array(
								'jurnal_id' => $id_header,
								'lokasi_id' => $value['lokasi_tugas_id'],
								'acc_akun_id' =>  $akun_kredit_transit_upah, //$value['acc_akun_id'], // Akun transit
								'debet' => 0,
								'kredit' => $upah, // Akun Lawan Biaya
								'ket' => 'Biaya Upah Traksi Kendaraan: ' . $value['nama_kendaraan'] . ' , Karyawan: ' . $value['nama_karyawan'] . "",
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL,
								'kegiatan_id' => NULL, // $value['kegiatan_id'],
								'kendaraan_mesin_id' => NULL, //$retrieve_header['kendaraan_id']
								'tipe' => 'upah',
								'hk' => 0
							);
							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
						}
						if ($premi > 0) {
							/* juranal lokasi traksi */
							$dataDebet = array(
								'jurnal_id' => $id_header,
								'lokasi_id' => $retrieve_header['lokasi_id'],
								'acc_akun_id' => $akun_debet_transit_premi,
								'debet' => ($premi),
								'kredit' => 0,
								'ket' => 'Biaya Premi Traksi Kendaraan: ' . $value['nama_kendaraan'] . ' , Karyawan: ' . $value['nama_karyawan'] . ",",
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL,
								'kegiatan_id' => NULL, //kegiatan panen,
								'kendaraan_mesin_id' =>  $retrieve_header['kendaraan_id'],
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
								'acc_akun_id' =>  $inter_akun_id, //$value['acc_akun_id'], // Akun transit
								'debet' => 0,
								'kredit' => $premi, // Akun Lawan Biaya
								'ket' => 'Biaya Premi Traksi Kendaraan: ' . $value['nama_kendaraan'] . ' , Karyawan: ' . $value['nama_karyawan'] . ",",
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL,
								'kegiatan_id' => NULL, // $value['kegiatan_id'],
								'kendaraan_mesin_id' => NULL, // $retrieve_header['kendaraan_id']
								'tipe' => 'premi',
								'hk' => 0
							);
							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);

							$dataDebet = array(
								'jurnal_id' => $id_header,
								'lokasi_id' => $value['lokasi_tugas_id'],
								'acc_akun_id' => $inter_akun_id,
								'debet' => ($premi),
								'kredit' => 0,
								'ket' => 'Biaya Premi Traksi Kendaraan: ' . $value['nama_kendaraan'] . ' , Karyawan: ' . $value['nama_karyawan'] . ",",
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL,
								'kegiatan_id' => NULL, //kegiatan panen,
								'kendaraan_mesin_id' =>  $retrieve_header['kendaraan_id'],
								'karyawan_id' => $value['karyawan_id'], //karyawan,
								'tipe' => 'premi',
								'hk' => 0
							);
							$total_pendapatan =	$total_pendapatan + ($value['rupiah_hk'] + $value['premi']);
							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
							// Data KREDIT
							$dataKredit = array(
								'lokasi_id' => $value['lokasi_tugas_id'],
								'jurnal_id' => $id_header,
								'acc_akun_id' =>  $akun_kredit_transit_premi, //$value['acc_akun_id'], // Akun transit
								'debet' => 0,
								'kredit' => $premi, // Akun Lawan Biaya
								'ket' => 'Biaya Premi Traksi Kendaraan: ' . $value['nama_kendaraan'] . ' , Karyawan: ' . $value['nama_karyawan'] . ",",
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL,
								'kegiatan_id' => NULL, // $value['kegiatan_id'],
								'kendaraan_mesin_id' => NULL, // $retrieve_header['kendaraan_id']
								'tipe' => 'premi',
								'hk' => 0
							);
							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
						}
					}
				}
				$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'TRAKSI');
				$qjurnalTemp = "SELECT a.id,SUM(debet)AS debet ,SUM(kredit)AS kredit ,sum(hk)AS hk,acc_akun_id,blok_stasiun_id,kegiatan_id,
				umur_tanam_blok ,kendaraan_mesin_id,c.nama AS blok,d.nama AS kendaraan
				FROM acc_jurnal_upah_ht a INNER JOIN acc_jurnal_upah_dt b
				ON a.id=b.jurnal_id
				LEFT JOIN gbm_organisasi c ON b.blok_stasiun_id=c.id
				LEFT JOIN trk_kendaraan d ON b.kendaraan_mesin_id=d.id
				WHERE a.id=" . $id_header . "
				GROUP by acc_akun_id,blok_stasiun_id,kegiatan_id,umur_tanam_blok,a.id,kendaraan_mesin_id,c.nama,d.nama
				ORDER BY debet desc";
				$resJurnalTemp = $this->db->query($qjurnalTemp)->result_array();
				if ($resJurnalTemp) {
					$no_jurnal = $this->autonumber->jurnal_auto($retrieve_header['lokasi_id'], $retrieve_header['tanggal'], 'TRK');

					$dataH = array(
						'no_jurnal' => $no_jurnal,
						'lokasi_id' => $retrieve_header['lokasi_id'],
						'tanggal' => $retrieve_header['tanggal'],
						'no_ref' => $retrieve_header['no_transaksi'],
						'ref_id' => $retrieve_header['id'],
						'tipe_jurnal' => 'AUTO',
						'modul' => 'TRAKSI',
						'keterangan' => 'TRANSAKSI TRAKSI',
						'is_posting' => 1,
					);
					$id_header2 = $this->AccJurnalModel->create_header($dataH);

					foreach ($resJurnalTemp as $key => $JurnalTemp) {
						$ket = '';
						if ($JurnalTemp['kendaraan']) {
							$ket = 'Traksi (Upah/premi) Kendaraan: ' . $JurnalTemp['kendaraan'];
						} else {
							$ket = 'Traksi (Upah/premi)';
						}
						if ($JurnalTemp['hk'] > 0) {
							$ket = $ket . ' ' . $JurnalTemp['hk'] . 'Hk';
						}
						$dataDebetKredit = array(
							'lokasi_id' => $retrieve_header['lokasi_id'],
							'jurnal_id' => $id_header2,
							'acc_akun_id' => $JurnalTemp['acc_akun_id'], //$value['acc_akun_id'],
							'debet' => $JurnalTemp['debet'],
							'kredit' => $JurnalTemp['kredit'], // Akun Lawan Biaya
							'ket' => $ket,
							'no_referensi' => $retrieve_header['no_transaksi'],
							'referensi_id' => NULL,
							'blok_stasiun_id' => $JurnalTemp['blok_stasiun_id'],
							'kegiatan_id' => $JurnalTemp['kegiatan_id'],  // $value['kegiatan_id'],
							'kendaraan_mesin_id' => $JurnalTemp['kendaraan_mesin_id'],
							'karyawan_id' => ($JurnalTemp['debet'] > 0) ? 0 : NULL,

						);
						$id_dtl = $this->AccJurnalModel->create_detail($id_header2, $dataDebetKredit);
					}
				}
			}

			$res = $this->TrkKegiatanKendaraanModel->posting($id, null);
			// if (!empty($res)) {
			// 	/* start audit trail */
			// 	$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id' => $id)), 'entity' => 'trk_kegiatan_kendaraan', 'action' => 'posting', 'entity_id' => $id);
			// 	$this->db->insert('fwk_user_audit', $audit);
			// 	/* end audit trail */
			// 	$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
			// } else {
			// 	$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
			// }
		}
		$this->set_response(array("status" => "OK", "data" => $jum), REST_Controller::HTTP_CREATED);
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
	public function start_proses_alokasi_post()
	{
		$id = $this->post('id');
		$periodeAkunting = $this->AccPeriodeAkuntingModel->retrieve($id);
		if (empty($periodeAkunting)) {
			$this->set_response(array("status" => "NOT OK", "data" => 'null'), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		// CEK PERIODE SDH ADA ATAU SDH CLOSE//
		$chk = cek_periode_by_id($periodeAkunting['id']);
		if ($chk['status'] == false) {
			$this->set_response(array("status" => "NOT OK", "data" => $chk['message']), REST_Controller::HTTP_OK);
			return;
		}
		//==============//
		$lokasi_id = $periodeAkunting['lokasi_id'];
		$lokasi = $this->db->query("SELECT * FROM gbm_organisasi 
								where id=" . $lokasi_id . "")->row_array();

		if ($lokasi['tipe'] == 'ESTATE') {
			$this->start_proses_alokasi_estate($id);
		} elseif ($lokasi['tipe'] == 'MILL') {
			$this->start_proses_alokasi_mill($id);
		}
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
		// $res_transit_akun = $this->db->query("SELECT * from acc_auto_jurnal 
		// where kode='TRAKSI_TRANSIT_AKUN'")->row_array();
		// if (empty($res_transit_akun)) {
		// 	$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
		// 	return;
		// }
		$res_kendaraan_dialokasi_akun = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='TRAKSI_DIALOKASI_AKUN'")->row_array();
		if (empty($res_kendaraan_dialokasi_akun)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		// $transit_akun = $res_transit_akun['acc_akun_id'];
		$kendaraan_dialokasi_akun = $res_kendaraan_dialokasi_akun['acc_akun_id'];

		/* Hapus dulu jurnal alokasi traksi jika sudah ada pada bulan tsb.*/
		$q = "delete from acc_jurnal_dt where jurnal_id in(select id from acc_jurnal_ht
                 where tanggal='" . $last_date_in_periode . "' and  modul='ALK_TRAKSI'
				 and lokasi_id =" . $lokasi_id . ")";
		$this->db->query($q);
		$q = "delete from acc_jurnal_ht  
                 where tanggal='" . $last_date_in_periode . "' and  modul='ALK_TRAKSI'
				 and lokasi_id =" . $lokasi_id . "";
		$this->db->query($q);

		/* Get InterUnit Akun /HUb RK */
		$retrieve_inter_akun = $this->db->query("SELECT * FROM acc_inter_unit
			where tipe='INTER' ")->result_array();
		$akun_inter = array();
		foreach ($retrieve_inter_akun as $key => $akun) {
			$akun_inter[$akun['lokasi_id']][$akun['lokasi_id_2']] = $akun['acc_akun_id'];
		}

		/* hard code akun transit: ('4110201', '4110202','4110203','4110204','4110205','4110206','4110207')*/
		$qtransit_account = "SELECT b.kendaraan_mesin_id,d.kode as kode_kendaraan,
		d.nama as nama_kendaraan,sum(debet-kredit)as nilai 
		FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
		on a.id=b.jurnal_id
		inner join trk_kendaraan d on b.kendaraan_mesin_id=d.id
		where b.lokasi_id =" . $lokasi_id . " and 
		a.tanggal >='" . $d1 . "' and a.tanggal <='" . $d2 . "'
		and b.acc_akun_id in (select id from acc_akun where kode in('4110201', '4110202','4110203','4110204','4110205','4110206','4110207'))
		group by b.kendaraan_mesin_id,d.kode ,d.nama ";
		$res_transit_account = $this->db->query($qtransit_account)->result_array();
		$traksi = array();
		foreach ($res_transit_account as $transit_account) {
			$nilai = $transit_account['nilai'];
			$traksi[$transit_account['kendaraan_mesin_id']] = $nilai;
			$q = "SELECT a.kendaraan_id,c.kode as kode_kendaraan,c.nama as nama_kendaraan,b.blok_id,e.acc_akun_id,
			b.km_hm_jumlah,g.kode as blok,e.nama as kegiatan,i.parent_id as lokasi_kerja_id ,b.ket,
			j.statusblok,b.acc_kegiatan_id as kegiatan_id
			FROM trk_kegiatan_kendaraan_ht a 
			inner join trk_kegiatan_kendaraan_log b on a.id=b.trk_kegiatan_kendaraan_id 
			inner join trk_kendaraan c on a.kendaraan_id =c.id 
			inner join acc_kegiatan e on b.acc_kegiatan_id=e.id 
			inner join acc_akun f on e.acc_akun_id=f.id 
			left join gbm_organisasi g on b.blok_id=g.id
			left join gbm_organisasi h on g.parent_id=h.id
			left join gbm_organisasi i on h.parent_id=i.id
			left join gbm_blok j on g.id=j.organisasi_id
			where 1=1
			and a.kendaraan_id=" . $transit_account['kendaraan_mesin_id'] . "
			and a.tanggal >='" . $d1 . "' and a.tanggal <='" . $d2 . "'";
			$res_trk = $this->db->query($q)->result_array();

			/* Jika ada transaksi di kegiatan traksi Maka hitung dan jurnal */
			if ($res_trk) {
				$total_km_hm = 0;
				/* ambil total km/hm dlm periode utk kendaraannya */
				foreach ($res_trk as $trk) {
					$total_km_hm = $total_km_hm + $trk['km_hm_jumlah'];
				}
				$this->load->library('Autonumber');
				$no_jurnal = $this->autonumber->jurnal_auto($lokasi_id, $last_date_in_periode, 'ALTR');
				$dataH = array(
					'no_jurnal' => $no_jurnal,
					'lokasi_id' => $lokasi_id,
					'tanggal' => $last_date_in_periode,
					'no_ref' => '',
					'ref_id' => null,
					'tipe_jurnal' => 'AUTO',
					'modul' => 'ALK_TRAKSI',
					'keterangan' => 'ALK_TRAKSI:' . $transit_account['nama_kendaraan'] . '-' . $transit_account['kode_kendaraan'],
					'is_posting' => 1,
					'diposting_oleh' => $this->user_id
				);
				$id_header = $this->AccJurnalModel->create_header($dataH);
				foreach ($res_trk as $trk) {
					$lokasi1 = $periodeAkunting["lokasi_id"];
					$lokasi2 = $trk['lokasi_kerja_id'];
					if ($akun_inter[$lokasi1][$lokasi2]) {
						$inter_akun_id = $akun_inter[$lokasi1][$lokasi2];
					}
					if ($akun_inter[$lokasi2][$lokasi1]) {
						$inter_akun_id = $akun_inter[$lokasi2][$lokasi1];
					}
					$nilai_alokasi = ($trk['km_hm_jumlah'] / $total_km_hm) * $nilai;
					if (!($lokasi2)) { // jIKA BLOK DAN PARENTNYA NULL (BLOK TDK DIISI) 
						$dataDebet = array(
							'lokasi_id' => $lokasi_id,
							'jurnal_id' => $id_header,
							'acc_akun_id' => $trk['acc_akun_id'], //akun ,
							'debet' => $nilai_alokasi,
							'kredit' => 0,
							'ket' => 'ALK_TRAKSI:' . $transit_account['nama_kendaraan'] . '-' . $transit_account['kode_kendaraan'] . ',  Kegiatan:' . $trk['kegiatan'] . ' Ket:' . $trk['ket'],
							'no_referensi' => 'ALK_TRAKSI:' . $periodeAkunting['nama'],
							'referensi_id' => NULL,
							'blok_stasiun_id' => NULL,
							'kegiatan_id' => $trk['kegiatan_id'],
							'kendaraan_mesin_id' =>  $trk['kendaraan_id'],
							'umur_tanam_blok' => NULL // $transit_account['kendaraan_mesin_id']
						);
						$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataDebet);
						$dataKredit = array(
							'lokasi_id' => $lokasi_id,
							'jurnal_id' => $id_header,
							'acc_akun_id' => $kendaraan_dialokasi_akun, //akun ,
							'debet' => 0,
							'kredit' => ($nilai_alokasi),
							'ket' => 'ALK_TRAKSI:' . $transit_account['nama_kendaraan'] . '-' . $transit_account['kode_kendaraan'] . ', Kegiatan:' . $trk['kegiatan'] . ' Ket:' . $trk['ket'],
							'no_referensi' => 'ALK_TRAKSI:' . $periodeAkunting['nama'],
							'referensi_id' => NULL,
							'blok_stasiun_id' => NULL,
							'kegiatan_id' => NULL,
							'kendaraan_mesin_id' =>  $trk['kendaraan_id'] // $transit_account['kendaraan_mesin_id']
						);
						$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataKredit);
					} else {
						if (($lokasi1 == $lokasi2)) {		// JIKA TIDAK ASISTENSI BLOK atau blok dan lokasi kerja = NULL				
							$dataDebet = array(
								'lokasi_id' => $lokasi_id,
								'jurnal_id' => $id_header,
								'acc_akun_id' => $trk['acc_akun_id'], //akun ,
								'debet' => $nilai_alokasi,
								'kredit' => 0,
								'ket' => 'ALK_TRAKSI:' . $transit_account['nama_kendaraan'] . '-' . $transit_account['kode_kendaraan'] . ',  BLOK:' . $trk['blok'] . ', Kegiatan:' . $trk['kegiatan'] . ' Ket:' . $trk['ket'],
								'no_referensi' => 'ALK_TRAKSI:' . $periodeAkunting['nama'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => $trk['blok_id'],
								'kegiatan_id' => $trk['kegiatan_id'],
								'kendaraan_mesin_id' =>  $trk['kendaraan_id'],
								'umur_tanam_blok' => $trk['statusblok'] // $transit_account['kendaraan_mesin_id']
							);
							$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataDebet);
							$dataKredit = array(
								'lokasi_id' => $lokasi_id,
								'jurnal_id' => $id_header,
								'acc_akun_id' => $kendaraan_dialokasi_akun, //akun ,
								'debet' => 0,
								'kredit' => ($nilai_alokasi),
								'ket' => 'ALK_TRAKSI:' . $transit_account['nama_kendaraan'] . '-' . $transit_account['kode_kendaraan'] . ',  BLOK:' . $trk['blok'] . ', Kegiatan:' . $trk['kegiatan'] . ' Ket:' . $trk['ket'],
								'no_referensi' => 'ALK_TRAKSI:' . $periodeAkunting['nama'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL,
								'kegiatan_id' => NULL,
								'kendaraan_mesin_id' =>  $trk['kendaraan_id'], // $transit_account['kendaraan_mesin_id']
							);
							$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataKredit);
						} else { // JIKA  ASISTENSI BLOK (KENDARAAN DIPINJAM OLEH LOKASI LAIN)	
							$dataDebet = array(
								'lokasi_id' => $lokasi1,
								'jurnal_id' => $id_header,
								'acc_akun_id' => $inter_akun_id, //akun ,
								'debet' => $nilai_alokasi,
								'kredit' => 0,
								'ket' => 'ALK_TRAKSI:' . $transit_account['nama_kendaraan'] . '-' . $transit_account['kode_kendaraan'] . ',  BLOK:' . $trk['blok'] . ', Kegiatan:' . $trk['kegiatan'] . ' Ket:' . $trk['ket'],
								'no_referensi' => 'ALK_TRAKSI:' . $periodeAkunting['nama'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => $trk['blok_id'],
								'kegiatan_id' => $trk['kegiatan_id'],
								'kendaraan_mesin_id' =>  $trk['kendaraan_id'],
								'umur_tanam_blok' => $trk['statusblok']  // $transit_account['kendaraan_mesin_id']
							);
							$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataDebet);
							$dataKredit = array(
								'lokasi_id' => $lokasi1,
								'jurnal_id' => $id_header,
								'acc_akun_id' => $kendaraan_dialokasi_akun, //akun ,
								'debet' => 0,
								'kredit' => ($nilai_alokasi),
								'ket' => 'ALK_TRAKSI:' . $transit_account['nama_kendaraan'] . '-' . $transit_account['kode_kendaraan'] . ',  BLOK:' . $trk['blok'] . ', Kegiatan:' . $trk['kegiatan'] . ' Ket:' . $trk['ket'],
								'no_referensi' => 'ALK_TRAKSI:' . $periodeAkunting['nama'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL,
								'kegiatan_id' => NULL,
								'kendaraan_mesin_id' =>  $trk['kendaraan_id'] // $transit_account['kendaraan_mesin_id']
							);
							$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataKredit);

							$dataDebet = array(
								'lokasi_id' => $lokasi2,
								'jurnal_id' => $id_header,
								'acc_akun_id' => $trk['acc_akun_id'], //akun ,
								'debet' => $nilai_alokasi,
								'kredit' => 0,
								'ket' => 'ALK_TRAKSI:' . $transit_account['nama_kendaraan'] . '-' . $transit_account['kode_kendaraan'] . ',  BLOK:' . $trk['blok'] . ', Kegiatan:' . $trk['kegiatan'] . ' Ket:' . $trk['ket'],
								'no_referensi' => 'ALK_TRAKSI:' . $periodeAkunting['nama'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => $trk['blok_id'],
								'kegiatan_id' => $trk['kegiatan_id'],
								'kendaraan_mesin_id' =>  $trk['kendaraan_id'],
								'umur_tanam_blok' => $trk['statusblok']  // $transit_account['kendaraan_mesin_id']
							);
							$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataDebet);
							$dataKredit = array(
								'lokasi_id' => $lokasi2,
								'jurnal_id' => $id_header,
								'acc_akun_id' => $inter_akun_id, //akun ,
								'debet' => 0,
								'kredit' => ($nilai_alokasi),
								'ket' => 'ALK_TRAKSI:' . $transit_account['nama_kendaraan'] . '-' . $transit_account['kode_kendaraan'] . ',  BLOK:' . $trk['blok'] . ', Kegiatan:' . $trk['kegiatan'] . ' Ket:' . $trk['ket'],
								'no_referensi' => 'ALK_TRAKSI:' . $periodeAkunting['nama'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL,
								'kegiatan_id' => NULL,
								'kendaraan_mesin_id' =>  $trk['kendaraan_id'] // $transit_account['kendaraan_mesin_id']
							);
							$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataKredit);
						}
					}
				}
			}
		}
		$this->db->where('id', $id);
		$this->db->update('acc_periode_akunting', array('is_proses_traksi'    => '1', "tanggal_proses_traksi" => date('Y-m-d H:i:s')));
		$this->set_response(array("status" => "OK", "data" => 'Proses berhasil. ' . count($res_transit_account) . " data diproses"), REST_Controller::HTTP_CREATED);
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
		// $res_transit_akun = $this->db->query("SELECT * from acc_auto_jurnal 
		// where kode='TRAKSI_TRANSIT_AKUN'")->row_array();
		// if (empty($res_transit_akun)) {
		// 	$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
		// 	return;
		// }
		$res_kendaraan_dialokasi_akun = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='TRAKSI_DIALOKASI_AKUN'")->row_array();
		if (empty($res_kendaraan_dialokasi_akun)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		// $transit_akun = $res_transit_akun['acc_akun_id'];
		$kendaraan_dialokasi_akun = $res_kendaraan_dialokasi_akun['acc_akun_id'];

		/* Hapus dulu jurnal alokasi traksi jika sudah ada pada bulan tsb.*/
		$q = "delete from acc_jurnal_dt where jurnal_id in(select id from acc_jurnal_ht
                 where tanggal='" . $last_date_in_periode . "' and  modul='ALK_TRAKSI'
				 and lokasi_id =" . $lokasi_id . ")";
		$this->db->query($q);
		$q = "delete from acc_jurnal_ht  
                 where tanggal='" . $last_date_in_periode . "' and  modul='ALK_TRAKSI'
				 and lokasi_id =" . $lokasi_id . "";
		$this->db->query($q);

		/* Get InterUnit Akun /HUb RK */
		$retrieve_inter_akun = $this->db->query("SELECT * FROM acc_inter_unit
			where tipe='INTER' ")->result_array();
		$akun_inter = array();
		foreach ($retrieve_inter_akun as $key => $akun) {
			$akun_inter[$akun['lokasi_id']][$akun['lokasi_id_2']] = $akun['acc_akun_id'];
		}

		/* hard code akun transit: ('4110201', '4110202','4110203','4110204','4110205','4110206','4110207')*/
		$qtransit_account = "SELECT b.kendaraan_mesin_id,d.kode as kode_kendaraan,
		d.nama as nama_kendaraan,sum(debet-kredit)as nilai 
		FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
		on a.id=b.jurnal_id
		inner join trk_kendaraan d on b.kendaraan_mesin_id=d.id
		where b.lokasi_id =" . $lokasi_id . " and 
		a.tanggal >='" . $d1 . "' and a.tanggal <='" . $d2 . "'
		and b.acc_akun_id in (select id from acc_akun where kode in('4110201', '4110202','4110203','4110204','4110205','4110206','4110207'))
		group by b.kendaraan_mesin_id,d.kode ,d.nama ";
		$res_transit_account = $this->db->query($qtransit_account)->result_array();
		$traksi = array();
		foreach ($res_transit_account as $transit_account) {
			$nilai = $transit_account['nilai'];
			$traksi[$transit_account['kendaraan_mesin_id']] = $nilai;
			$q = "SELECT a.kendaraan_id,c.kode as kode_kendaraan,c.nama as nama_kendaraan,b.blok_id,e.acc_akun_id,
			b.km_hm_jumlah,g.nama as mesin,e.nama as kegiatan,i.parent_id as lokasi_kerja_id ,b.ket,b.acc_kegiatan_id as kegiatan_id
			FROM trk_kegiatan_kendaraan_ht a 
			inner join trk_kegiatan_kendaraan_log b on a.id=b.trk_kegiatan_kendaraan_id 
			inner join trk_kendaraan c on a.kendaraan_id =c.id 
			inner join acc_kegiatan e on b.acc_kegiatan_id=e.id 
			inner join acc_akun f on e.acc_akun_id=f.id 
			left join gbm_organisasi g on b.blok_id=g.id
			left join gbm_organisasi h on g.parent_id=h.id
			left join gbm_organisasi i on h.parent_id=i.id
			where 1=1
			and a.kendaraan_id=" . $transit_account['kendaraan_mesin_id'] . "
			and a.tanggal >='" . $d1 . "' and a.tanggal <='" . $d2 . "'";
			$res_trk = $this->db->query($q)->result_array();

			/* Jika ada transaksi di kegiatan traksi Maka hitung dan jurnal */
			if ($res_trk) {
				$total_km_hm = 0;
				/* ambil total km/hm dlm periode utk kendaraannya */
				foreach ($res_trk as $trk) {
					$total_km_hm = $total_km_hm + $trk['km_hm_jumlah'];
				}
				$this->load->library('Autonumber');
				$no_jurnal = $this->autonumber->jurnal_auto($lokasi_id, $last_date_in_periode, 'ALTR');
				$dataH = array(
					'no_jurnal' => $no_jurnal,
					'lokasi_id' => $lokasi_id,
					'tanggal' => $last_date_in_periode,
					'no_ref' => '',
					'ref_id' => null,
					'tipe_jurnal' => 'AUTO',
					'modul' => 'ALK_TRAKSI',
					'keterangan' => 'ALK_TRAKSI:' . $transit_account['nama_kendaraan'] . '-' . $transit_account['kode_kendaraan'],
					'is_posting' => 1,
					'diposting_oleh' => $this->user_id
				);
				$id_header = $this->AccJurnalModel->create_header($dataH);
				foreach ($res_trk as $trk) {
					$lokasi1 = $periodeAkunting["lokasi_id"];
					$lokasi2 = $trk['lokasi_kerja_id'];
					if ($akun_inter[$lokasi1][$lokasi2]) {
						$inter_akun_id = $akun_inter[$lokasi1][$lokasi2];
					}
					if ($akun_inter[$lokasi2][$lokasi1]) {
						$inter_akun_id = $akun_inter[$lokasi2][$lokasi1];
					}
					$nilai_alokasi = ($trk['km_hm_jumlah'] / $total_km_hm) * $nilai;
					if (!($lokasi2)) { // jIKA BLOK DAN PARENTNYA NULL (BLOK TDK DIISI) 
						$dataDebet = array(
							'lokasi_id' => $lokasi_id,
							'jurnal_id' => $id_header,
							'acc_akun_id' => $trk['acc_akun_id'], //akun ,
							'debet' => $nilai_alokasi,
							'kredit' => 0,
							'ket' => 'ALK_TRAKSI:' . $transit_account['nama_kendaraan'] . '-' . $transit_account['kode_kendaraan'] . ',  Kegiatan:' . $trk['kegiatan'] . ' Ket:' . $trk['ket'],
							'no_referensi' => 'ALK_TRAKSI:' . $periodeAkunting['nama'],
							'referensi_id' => NULL,
							'blok_stasiun_id' => NULL,
							'kegiatan_id' => $trk['kegiatan_id'],
							'kendaraan_mesin_id' => $trk['kendaraan_id'],
							'umur_tanam_blok' => NULL // $transit_account['kendaraan_mesin_id']
						);
						$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataDebet);
						$dataKredit = array(
							'lokasi_id' => $lokasi_id,
							'jurnal_id' => $id_header,
							'acc_akun_id' => $kendaraan_dialokasi_akun, //akun ,
							'debet' => 0,
							'kredit' => ($nilai_alokasi),
							'ket' => 'ALK_TRAKSI:' . $transit_account['nama_kendaraan'] . '-' . $transit_account['kode_kendaraan'] . ', Kegiatan:' . $trk['kegiatan'] . ' Ket:' . $trk['ket'],
							'no_referensi' => 'ALK_TRAKSI:' . $periodeAkunting['nama'],
							'referensi_id' => NULL,
							'blok_stasiun_id' => NULL,
							'kegiatan_id' => NULL,
							'kendaraan_mesin_id' => $trk['kendaraan_id'] // $transit_account['kendaraan_mesin_id']
						);
						$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataKredit);
					} else {
						if (($lokasi1 == $lokasi2)) {		// JIKA TIDAK ASISTENSI BLOK atau blok dan lokasi kerja = NULL				
							$dataDebet = array(
								'lokasi_id' => $lokasi_id,
								'jurnal_id' => $id_header,
								'acc_akun_id' => $trk['acc_akun_id'], //akun ,
								'debet' => $nilai_alokasi,
								'kredit' => 0,
								'ket' => 'ALK_TRAKSI:' . $transit_account['nama_kendaraan'] . '-' . $transit_account['kode_kendaraan'] . ',  Mesin:' . $trk['mesin'] . ', Kegiatan:' . $trk['kegiatan'] . ' Ket:' . $trk['ket'],
								'no_referensi' => 'ALK_TRAKSI:' . $periodeAkunting['nama'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => $trk['blok_id'],
								'kegiatan_id' => $trk['kegiatan_id'],
								'kendaraan_mesin_id' => $trk['kendaraan_id'],
								'umur_tanam_blok' => NULL // $transit_account['kendaraan_mesin_id']
							);
							$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataDebet);
							$dataKredit = array(
								'lokasi_id' => $lokasi_id,
								'jurnal_id' => $id_header,
								'acc_akun_id' => $kendaraan_dialokasi_akun, //akun ,
								'debet' => 0,
								'kredit' => ($nilai_alokasi),
								'ket' => 'ALK_TRAKSI:' . $transit_account['nama_kendaraan'] . '-' . $transit_account['kode_kendaraan'] . ',  Mesin:' . $trk['mesin'] . ', Kegiatan:' . $trk['kegiatan'] . ' Ket:' . $trk['ket'],
								'no_referensi' => 'ALK_TRAKSI:' . $periodeAkunting['nama'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL,
								'kegiatan_id' => NULL,
								'kendaraan_mesin_id' => $trk['kendaraan_id'] // $transit_account['kendaraan_mesin_id']
							);
							$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataKredit);
						} else { // JIKA  ASISTENSI BLOK (KENDARAAN DIPINJAM OLEH LOKASI LAIN)	
							$dataDebet = array(
								'lokasi_id' => $lokasi1,
								'jurnal_id' => $id_header,
								'acc_akun_id' => $inter_akun_id, //akun ,
								'debet' => $nilai_alokasi,
								'kredit' => 0,
								'ket' => 'ALK_TRAKSI:' . $transit_account['nama_kendaraan'] . '-' . $transit_account['kode_kendaraan'] . ',  Mesian:' . $trk['mesin'] . ', Kegiatan:' . $trk['kegiatan'] . ' Ket:' . $trk['ket'],
								'no_referensi' => 'ALK_TRAKSI:' . $periodeAkunting['nama'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => $trk['blok_id'],
								'kegiatan_id' => $trk['kegiatan_id'],
								'kendaraan_mesin_id' => $trk['kendaraan_id'],
								'umur_tanam_blok' => NULL  // $transit_account['kendaraan_mesin_id']
							);
							$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataDebet);
							$dataKredit = array(
								'lokasi_id' => $lokasi1,
								'jurnal_id' => $id_header,
								'acc_akun_id' => $kendaraan_dialokasi_akun, //akun ,
								'debet' => 0,
								'kredit' => ($nilai_alokasi),
								'ket' => 'ALK_TRAKSI:' . $transit_account['nama_kendaraan'] . '-' . $transit_account['kode_kendaraan'] . ',  Mesin:' . $trk['mesin'] . ', Kegiatan:' . $trk['kegiatan'] . ' Ket:' . $trk['ket'],
								'no_referensi' => 'ALK_TRAKSI:' . $periodeAkunting['nama'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL,
								'kegiatan_id' => NULL,
								'kendaraan_mesin_id' => $trk['kendaraan_id'] // $transit_account['kendaraan_mesin_id']
							);
							$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataKredit);

							$dataDebet = array(
								'lokasi_id' => $lokasi2,
								'jurnal_id' => $id_header,
								'acc_akun_id' => $trk['acc_akun_id'], //akun ,
								'debet' => $nilai_alokasi,
								'kredit' => 0,
								'ket' => 'ALK_TRAKSI:' . $transit_account['nama_kendaraan'] . '-' . $transit_account['kode_kendaraan'] . ',  Mesin:' . $trk['mesin'] . ', Kegiatan:' . $trk['kegiatan'] . ' Ket:' . $trk['ket'],
								'no_referensi' => 'ALK_TRAKSI:' . $periodeAkunting['nama'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => $trk['blok_id'],
								'kegiatan_id' => $trk['kegiatan_id'],
								'kendaraan_mesin_id' => $trk['kendaraan_id'],
								'umur_tanam_blok' => NULL // $transit_account['kendaraan_mesin_id']
							);
							$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataDebet);
							$dataKredit = array(
								'lokasi_id' => $lokasi2,
								'jurnal_id' => $id_header,
								'acc_akun_id' => $inter_akun_id, //akun ,
								'debet' => 0,
								'kredit' => ($nilai_alokasi),
								'ket' => 'ALK_TRAKSI:' . $transit_account['nama_kendaraan'] . '-' . $transit_account['kode_kendaraan'] . ',  Mesin:' . $trk['mesin'] . ', Kegiatan:' . $trk['kegiatan'] . ' Ket:' . $trk['ket'],
								'no_referensi' => 'ALK_TRAKSI:' . $periodeAkunting['nama'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL,
								'kegiatan_id' => NULL,
								'kendaraan_mesin_id' => $trk['kendaraan_id'] // $transit_account['kendaraan_mesin_id']
							);
							$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataKredit);
						}
					}
				}
			}
		}
		$this->db->where('id', $id);
		$this->db->update('acc_periode_akunting', array('is_proses_traksi'    => '1', "tanggal_proses_traksi" => date('Y-m-d H:i:s')));
		$this->set_response(array("status" => "OK", "data" => 'Proses berhasil. ' . count($res_transit_account) . " data diproses"), REST_Controller::HTTP_CREATED);
	}
	function laporan_kegiatan_detail_post()
	{
		// $id = (int)$segment_3;
		$data = [];

		$input = [
			'no_po' => '01/PO/DPA',
			'lokasi_id' => 252,
			'tgl_mulai' => '2020-01-01',
			'tgl_akhir' => '2022-04-18',
		];

		$format_laporan = $this->post('format_laporan', true);
		$lokasi_id = $this->post('lokasi_id', true);
		$traksi_id = $this->post('traksi_id', true);
		$kendaraan_id = $this->post('kendaraan_id', true);
		$tanggal_awal = $this->post('tgl_mulai', true);
		$tanggal_akhir = $this->post('tgl_akhir', true);

		// $lokasi_id=$input['lokasi_id'];
		// $tanggal_awal=$input['tgl_mulai'];
		// $tanggal_akhir=$input['tgl_akhir'];

		$queryPo = "SELECT
		b.*,
		a.status_kendaraan,
		a.tanggal AS tanggal,
		a.no_transaksi AS no_transaksi,
		c.nama AS blok,
		d.nama AS kegiatan,
		e.nama as nama_kendaraan,
		e.kode as kode_kendaraan,
		f.kode as kode_traksi,
		f.nama as nama_traksi,
		b.id AS id
		
		FROM trk_kegiatan_kendaraan_ht a
		left JOIN trk_kegiatan_kendaraan_log b ON b.trk_kegiatan_kendaraan_id=a.id
		left JOIN gbm_organisasi c ON b.blok_id=c.id
		left JOIN acc_kegiatan d ON b.acc_kegiatan_id=d.id
		left JOIN trk_kendaraan e on a.kendaraan_id=e.id
		left JOIN gbm_organisasi f on a.traksi_id=f.id
		where a.tanggal between  '" . $tanggal_awal . "' and  '" . $tanggal_akhir . "'	
		";



		// $filter_lokasi = "Semua";
		// if ($lokasi_id) {
		// 	$queryPo = $queryPo . " and b.lokasi_id=" . $lokasi_id . "";
		// 	$res = $this->db->query("select * from gbm_organisasi where id=" . $lokasi_id . "")->row_array();
		// 	$filter_lokasi = $res['nama'];
		// }
		$filter_traksi = "Semua";
		if ($traksi_id) {
			$queryPo = $queryPo . " and a.traksi_id=" . $traksi_id . "";
			$res = $this->db->query("select * from gbm_organisasi where id=" . $traksi_id . "")->row_array();
			$filter_traksi = $res['nama'];
		}
		$filter_kendaraan = "Semua";
		if ($kendaraan_id) {
			$queryPo = $queryPo . " and a.kendaraan_id=" . $kendaraan_id . "";
			$res = $this->db->query("select * from trk_kendaraan where id=" . $kendaraan_id . "")->row_array();
			$filter_kendaraan = $res['nama'];
		}

		$queryPo = $queryPo . " order by 	a.tanggal,a.kendaraan_id";
		$dataPo = $this->db->query($queryPo)->result_array();

		$data['po'] = 	$dataPo;
		$data['filter_kendaraan'] = 	$filter_kendaraan;
		$data['filter_lokasi'] = 	$filter_traksi;
		$data['filter_tgl_awal'] = 	$tanggal_awal;
		$data['filter_tgl_akhir'] = $tanggal_akhir;
		$data['format_laporan'] = $format_laporan;

		$html = $this->load->view('Trk_KegiatanKendaraan_Kegiatan_Laporan', $data, true);

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		// echo $html;
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


	function laporan_frestasi_detail_post()
	{

		// $id = (int)$segment_3;
		$data = [];

		$input = [
			'no_po' => '01/PO/DPA',
			'lokasi_id' => 252,
			'tgl_mulai' => '2020-01-01',
			'tgl_akhir' => '2022-04-18',
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
		-- a.qty as qty,
		-- b.no_transaksi,
		b.tanggal AS tanggal,
		b.no_transaksi AS no_transaksi,
		c.nama AS karyawan,
		d.nama as nama_kendaraan,
	    d.kode as kode_kendaraan,
		-- c.kode as kode,
		-- e.nama as gudang,
		a.id AS id
		
		FROM trk_kegiatan_kendaraan_dt a
		
		INNER JOIN trk_kegiatan_kendaraan_ht b on a.trk_kegiatan_kendaraan_id=b.id
		INNER JOIN karyawan c ON a.karyawan_id=c.id
		INNER join trk_kendaraan d on b.kendaraan_id=d.id 
		
        
        -- LEFT JOIN inv_pindah_gudang_ht h on b.inv_pindah_gudang_id=h.id
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

		// $filter_gudang="Semua";
		// if ($gudang_id){
		// 	$queryPo=$queryPo." and b.gudang_id=".$gudang_id ."";
		// 	$res=$this->db->query("select * from gbm_organisasi where id=".$gudang_id."")->row_array();
		// 	$filter_gudang=$res['nama'];
		// }

		$dataPo = $this->db->query($queryPo)->result_array();

		$data['po'] = 	$dataPo;
		// $data['filter_gudang'] = 	$filter_gudang;
		$data['filter_lokasi'] = 	$filter_lokasi;
		$data['filter_tgl_awal'] = 	$tanggal_awal;
		$data['filter_tgl_akhir'] = $tanggal_akhir;
		$data['format_laporan'] = $format_laporan;

		$html = $this->load->view('Trk_KegiatanKendaraan_Frestasi_Laporan', $data, true);

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

	public function laporan_log_by_tanggal_post()
	{
		error_reporting(0);
		$traksi_id     = $this->post('traksi_id', true);
		$tgl_mulai =  $this->post('tgl_mulai', true);
		$tgl_akhir = $this->post('tgl_akhir', true);
		// $blok_id = $this->post('blok_id', true);
		$format_laporan     = $this->post('format_laporan', true);

		$kendaraan_id = $this->post('kendaraan_id', true);

		$retrieveTraksi = $this->db->query("select * from gbm_organisasi where id=" . $traksi_id . "")->row_array();

		$d1 = new DateTime($tgl_mulai);
		$d2 = new DateTime($tgl_akhir);
		$interval = $d1->diff($d2);
		$jumlah_hari = $interval->days;

		$q0 = "SELECT a.*,b.nama AS jenis FROM trk_kendaraan a INNER JOIN trk_jenis_traksi b
		ON a.jenis_id=b.id 
		where traksi_id=" . $traksi_id . "";

		if ($kendaraan_id) {
			$q0 =	$q0 . " and a.id=" . $kendaraan_id . "";
		}
		$q0 =	$q0 . " order by b.nama";
		$arrhd = $this->db->query($q0)->result_array();
		$no = 0;
		$strNo = '';

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
		<h3 class="title">Laporan Rekap Log Kendaran/AB/Mesin By Tanggal</h3>
  <table class="no_border" style="width:30%">
			
			<tr>
					<td>Traksi</td>
					<td>:</td>
					<td>' . $retrieveTraksi['nama'] . '</td>
			</tr>
			<tr>	
					<td>Periode</td>
					<td>:</td>
					<td>' . tgl_indo($tgl_mulai) . ' - ' . tgl_indo($tgl_akhir) . '</td>
			</tr>			
	</table>
	<br>';
		$html = $html . "	
		<table   border='1' width='100%' style='border-collapse: collapse;'>
		<thead>
		<tr>
			<th rowspan=3 >No</th>
			<th rowspan=3>Unit</th>
			<th rowspan=3>Kode</th>
			<th colspan=" . (($jumlah_hari + 1) * 2) . "  style='text-align: center'> Tanggal  </th>
			
			<th rowspan=2  style='text-align: right'>Jumlah</th>
		</tr>
		";
		$html = $html . "<tr>";
		while ($d1 <= $d2) {
			$ddmm = $d1->format('d-m');
			$dd = $d1->format('d');
			// $html = $html . "<td colspan='2' style='text-align: center'>" . $ddmm . "</td>";
			$html = $html . "<th colspan='2' style='text-align: center'>" . $dd . "</th>";
			$d1->modify('+1 day');
		}
		$html = $html . "</tr>";
		$d1 = new DateTime($tgl_mulai);
		while ($d1 <= $d2) {
			$ddmm = $d1->format('d-m');
			$html = $html . "<th style='text-align: center'>Status</th>";
			$html = $html . "<th style='text-align: center'>Km,Hm</th>";
			$d1->modify('+1 day');
		}
		$html = $html . "<th style='text-align: center'>Km,Hm</th>";
		$html = $html . "</tr> </thead>";
		$qtyPerHari = array();
		$qtyPerHari = [];
		$nilaiPerHari = array();
		$nilaiPerHari = [];
		$totalNilai = 0;
		$totalQty = 0;
		foreach ($arrhd as $hd) {
			$no++;
			//$actual_link ='http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			$actual_link = "http://$_SERVER[HTTP_HOST]" . "/plantation-api/api/GlobalReport/trk_pemakaian_inventory/" . $hd['id'] . "/" . $tgl_mulai . "/" . $tgl_akhir .   "";
			$html = $html . "<tr>";
			$html = $html . "<td style='text-align: center'>" . $no . "</td>";
			// $html = $html . "<td style='text-align: left'> <a href='" . $actual_link . "" . "' target='_blank'> " . $hd['nama'] . " </a></td>";
			// $html = $html . "<td style='text-align: left'> <a href='" . $actual_link . "" . "' target='_blank'> " . $hd['kode'] . " </a></td>";
			$html = $html . "<td style='text-align: left'>  " . $hd['nama'] . " </td>";
			$html = $html . "<td style='text-align: left'> " . $hd['kode'] . "</td>";

			$d1 = new DateTime($tgl_mulai);
			$jum_qty = 0;
			$jum_nilai = 0;
			while ($d1 <= $d2) {
				$tgl = $d1->format('Y-m-d');
				$qTraksi = "SELECT sum(km_hm_jumlah)as jumlah_km_hm,
					a.status_kendaraan	
				FROM trk_kegiatan_kendaraan_ht a
				left JOIN trk_kegiatan_kendaraan_log b ON b.trk_kegiatan_kendaraan_id=a.id
				left JOIN gbm_organisasi c ON b.blok_id=c.id
				left JOIN acc_kegiatan d ON b.acc_kegiatan_id=d.id
				left JOIN trk_kendaraan e on a.kendaraan_id=e.id
				left JOIN gbm_organisasi f on a.traksi_id=f.id
				where a.tanggal =  '" . $tgl . "'	
				and a.kendaraan_id=" . $hd['id'] . "
				group by a.status_kendaraan";
				$resTraksi = $this->db->query($qTraksi)->row_array();
				$jum_qty = $jum_qty + $resTraksi['jumlah_km_hm'];
				$totalQty = $totalQty + $resTraksi['jumlah_km_hm'];
				if ($resTraksi['status_kendaraan'] == 'BREAKDOWN') {
					$html = $html . "<td style='text-align: right;color:red'>" . ($resTraksi['status_kendaraan']) . " </td>";
				} else {
					$html = $html . "<td style='text-align: right'>" . ($resTraksi['status_kendaraan']) . " </td>";
				}
				$html = $html . "<td style='text-align: right'> " . number_format($resTraksi['jumlah_km_hm']) . " </td>";
				$yymmdd = $d1->format('Ymd');

				if (array_key_exists($yymmdd, $qtyPerHari)) {
					$qtyPerHari[$yymmdd] = $qtyPerHari[$yymmdd] + $resTraksi['jumlah_km_hm'];
				} else {
					$qtyPerHari[$yymmdd] = $resTraksi['jumlah_km_hm'];
				}
				$d1->modify('+1 day');
			}
			$html = $html . "<td style='text-align: right'>" . number_format($jum_qty) . " </td>";
			$html = $html . "</tr>";
		}

		// $html = $html . "<tr>";
		// $html = $html . "<td style='text-align: center'> </td>";
		// $html = $html . "<td style='text-align: center'></td>";
		// $html = $html . "<td style='text-align: center'></td>";

		// $d1 = new DateTime($tgl_mulai);
		// while ($d1 <= $d2) {
		// 	$yymmdd = $d1->format('Ymd');
		// 	$html = $html . "<td style='text-align: center'></td>";
		// 	$html = $html . "<td style='text-align: right'><b>" . number_format($qtyPerHari[$yymmdd]) . " </b></td>";
		// 	$d1->modify('+1 day');
		// }
		// $html = $html . "<td style='text-align: right'><b>" . number_format($totalQty) . " </b> </td>";
		// $html = $html . "</tr>";
		$html = $html . "</table>";

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

	function print_slip_get($segment_3 = '')
	{
		error_reporting(0);

		$id = (int)$segment_3;
		$data = [];

		$queryHeader = "SELECT 
		a.*,
		b.nama AS lokasi,
		c.nama AS kendaraan,
		d.nama AS mandor,
		d.nama AS kerani,
		d.nama AS asisten,
		e.nama AS traksi,
		a.id AS id
		FROM trk_kegiatan_kendaraan_ht a 
		LEFT JOIN gbm_organisasi b ON a.lokasi_id=b.id
		LEFT JOIN trk_kendaraan c ON a.kendaraan_id=c.id
		LEFT JOIN karyawan d ON a.mandor_id=d.id
		LEFT JOIN karyawan dd ON a.kerani_id=dd.id
		LEFT JOIN karyawan ddd ON a.asisten_id=ddd.id
		LEFT JOIN gbm_organisasi e ON a.traksi_id=e.id
		WHERE a.id=" . $id . "
		";
		$dataHeader = $this->db->query($queryHeader)->row_array();

		$queryDetail = "SELECT 
		a.*,
		b.nama AS karyawan,
		a.id AS id
		FROM trk_kegiatan_kendaraan_dt a 
		LEFT JOIN karyawan b on a.karyawan_id=b.id
		WHERE a.trk_kegiatan_kendaraan_id=" . $id . "
		";
		$dataDetail = $this->db->query($queryDetail)->result_array();

		$queryDetailLog = "SELECT 
		a.*,
		b.kode as kode_kegiatan,
		b.nama AS kegiatan,
		c.nama AS blok,
		a.id AS id,
		d.kode as kode_akun,
		d.nama as nama_akun
		FROM trk_kegiatan_kendaraan_log a 
		LEFT JOIN acc_kegiatan b on a.acc_kegiatan_id=b.id
		LEFT JOIN gbm_organisasi c on a.blok_id=c.id
		LEFT JOIN acc_akun d on b.acc_akun_id=d.id
		WHERE a.trk_kegiatan_kendaraan_id=" . $id . "
		";
		$dataDetailLog = $this->db->query($queryDetailLog)->result_array();

		// $queryDetailItem = "SELECT 
		// a.*,
		// c.nama AS item,
		// d.nama AS uom,
		// a.id AS id
		// FROM trk_kegiatan_kendaraan_item a 
		// INNER JOIN inv_item c on a.item_id=c.id 
		// LEFT JOIN gbm_uom d on c.uom_id=d.id
		// WHERE a.trk_kegiatan_kendaraan_id=".$id."
		// ";
		// $dataDetailItem = $this->db->query($queryDetailItem)->result_array();

		// $perminta = $this->InvPermintaanBarangModel->print_slip($id);
		$data['header'] = 	$dataHeader;
		$data['detail'] = 	$dataDetail;
		$data['detail_log'] = 	$dataDetailLog;
		// $data['detail_item'] = 	$dataDetailItem;

		$html = $this->load->view('TrkKegiatanKendaraan_print', $data, true);

		$filename = 'report_' . time();
		$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		// echo $html;
	}
}
