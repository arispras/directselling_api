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

class WrkKegiatanMill extends BD_Controller
{
	public $user_id;
	public $theCredential;

	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('GbmOrganisasiModel');
		$this->load->model('WrkKegiatanMillModel');
		$this->load->model('AccJurnalModel');
		$this->load->model('M_DatatablesModel');
		$this->load->library('pdfgenerator');
		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id = $this->user_data->id;
	}
	public function list_post()
	{
		$post = $this->post();
		$param=$post['parameter'];
		
		$query  = "SELECT a.*,b.nama as lokasi,e.nama as stasiun,e.kode as kode_stasiun ,f.nama as workshop,
		g.nama as mesin,g.kode as kode_mesin
		FROM `wrk_kegiatan_mill_ht` a 
		inner join gbm_organisasi b on a.lokasi_id=b.id
		left join gbm_organisasi e on a.stasiun_id=e.id 
		left join gbm_organisasi f on a.workshop_id=f.id 
		left join gbm_organisasi g on a.mesin_id=g.id 
		";
		$search = array('a.no_transaksi', 'a.tanggal', 'b.nama', 'e.nama','f.nama','e.kode','g.nama','g.kode');
		$where  = null;

		$isWhere=" 1=1";
		if ($param['tgl_mulai'] && $param['tgl_mulai']){
			$isWhere=" tanggal between '".$param['tgl_mulai']."' and '".$param['tgl_akhir']."'";			
		}
		if ($param['lokasi_id']){
			$isWhere =$isWhere. " and lokasi_id =".$param['lokasi_id']."";
		}else{
			$isWhere = $isWhere. " and  lokasi_id in
			(select location_id from fwk_users_location where user_id=" . $this->user_id . ")";
		}

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}

	function index_get($id = '')
	{
		$retrieve = $this->WrkKegiatanMillModel->retrieve_by_id($id);
		$retrieve['detail'] = $this->WrkKegiatanMillModel->retrieve_detail($id);
		$retrieve['detail_item'] = $this->WrkKegiatanMillModel->retrieve_detail_item($id);
		// $retrieve['detail_log'] = $this->WrkKegiatanMillModel->retrieve_detail_item($id);


		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function getDetail_get($id = '')
	{
		$retrieve = $this->WrkKegiatanMillModel->retrieve_detail($id);
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
		$input['no_transaksi'] = $this->autonumber->wrk_kegiatan_mill($input['lokasi_id']['id'], $input['tanggal']);

		//$input['no_spat'] = $this->getLastNumber('pks_pengolahan_ht', 'no_spat', 'SPAT');
		// var_dump($input);

		$res = $this->WrkKegiatanMillModel->create($input);
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

		$res = $this->WrkKegiatanMillModel->update($id, $data);
		if (!empty($res)) {
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	public function index_delete($id)
	{

		$res = $this->WrkKegiatanMillModel->delete($id);
		if (!empty($res)) {
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function posting_post2($segment_3 = null)
	{
		$id = (int) $segment_3;
		$retrieve_header = $this->WrkKegiatanMillModel->retrieve_by_id($id);
		$retrieve_detail = $this->db->query("SELECT a.*,b.*,c.nip,c.nama as nama_karyawan, d.kode as kode_kendaraan,d.nama as nama_kendaraan,d.no_kendaraan  FROM wrk_kegiatan_mill_ht a inner join wrk_kegiatan_mill_dt b on a.id=b.trk_kegiatan_kendaraan_id inner join karyawan c on b.karyawan_id=c.id
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
			'kendaraan_mesin_id' =>NULL// $retrieve_header['kendaraan_id']
		);
		$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataKredit);
		$res = $this->WrkKegiatanMillModel->posting($id, null);
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
	
		$retrieve_header = $this->WrkKegiatanMillModel->retrieve_by_id($id);
		$retrieve_detail = $this->db->query("SELECT a.*,b.*,c.nip,c.nama as nama_karyawan, d.kode as kode_kendaraan,d.nama as nama_kendaraan,d.no_kendaraan FROM wrk_kegiatan_mill_ht a inner join wrk_kegiatan_mill_dt b on a.id=b.wrk_kegiatan_mill_id inner join karyawan c on b.karyawan_id=c.id inner join trk_kendaraan d on a.kendaraan_mesin_id =d.id where b.wrk_kegiatan_mill_id=" . $id . "")->result_array();

		// Hapus jurnal jika sdh ada berdasarkan no refernsi dan MOdul  //
		$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'TRAKSI');
		$total_pendapatan = 0;


		// Data HEADER
		$this->load->library('Autonumber');
		$no_jurnal = $this->autonumber->jurnal_auto($retrieve_header['lokasi_id'], $retrieve_header['tanggal'], 'WRK');

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
		$id_header = $this->AccJurnalModel->create_header($dataH);
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
					'ket' => 'Biaya Upah Perbaikan Kendaraan: ' . $value['nama_kendaraan'] . ' , Karyawan: ' . $value['nama_karyawan'] . "",
					'no_referensi' => $retrieve_header['no_transaksi'],
					'referensi_id' => NULL,
					'blok_stasiun_id' => NULL,
					'kegiatan_id' => NULL, //kegiatan panen,
					'kendaraan_mesin_id' =>  $retrieve_header['kendaraan_mesin_id'],
					'karyawan_id' => $value['karyawan_id'], //karyawan,
				);
				$total_pendapatan =	$total_pendapatan + ($value['rupiah_hk'] + $value['premi']);
				$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataDebet);
				// Data KREDIT
				$dataKredit = array(
					'lokasi_id' => $retrieve_header['lokasi_id'],
					'jurnal_id' => $id_header,
					'acc_akun_id' =>  $akun_kredit_transit_upah, //$value['acc_akun_id'], // Akun transit
					'debet' => 0,
					'kredit' => $upah, // Akun Lawan Biaya
					'ket' => 'Biaya Upah Perbaikan Kendaraan: ' . $value['nama_kendaraan'] . ' , Karyawan: ' . $value['nama_karyawan'] . "",
					'no_referensi' => $retrieve_header['no_transaksi'],
					'referensi_id' => NULL,
					'blok_stasiun_id' => NULL,
					'kegiatan_id' => NULL, // $value['kegiatan_id'],
					'kendaraan_mesin_id' =>NULL// $retrieve_header['kendaraan_id']
				);
				$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataKredit);
			}
			if ($premi > 0) {
				$dataDebet = array(
					'lokasi_id' => $retrieve_header['lokasi_id'],
					'jurnal_id' => $id_header,
					'acc_akun_id' => $akun_debet_transit_premi,
					'debet' => ($premi),
					'kredit' => 0,
					'ket' => 'Biaya Premi Perbaikan Kendaraan: ' . $value['nama_kendaraan'] . ' , Karyawan: ' . $value['nama_karyawan'] . "",
					'no_referensi' => $retrieve_header['no_transaksi'],
					'referensi_id' => NULL,
					'blok_stasiun_id' => NULL,
					'kegiatan_id' => NULL, //kegiatan panen,
					'kendaraan_mesin_id' =>  $retrieve_header['kendaraan_mesin_id'],
					'karyawan_id' => $value['karyawan_id'], //karyawan,
				);
				$total_pendapatan =	$total_pendapatan + ($value['rupiah_hk'] + $value['premi']);
				$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataDebet);
				// Data KREDIT
				$dataKredit = array(
					'lokasi_id' => $retrieve_header['lokasi_id'],
					'jurnal_id' => $id_header,
					'acc_akun_id' =>  $akun_kredit_transit_premi, //$value['acc_akun_id'], // Akun transit
					'debet' => 0,
					'kredit' => $premi, // Akun Lawan Biaya
					'ket' => 'Biaya Premi Perbaikan Kendaraan: ' . $value['nama_kendaraan'] . ' , Karyawan: ' . $value['nama_karyawan'] . "",
					'no_referensi' => $retrieve_header['no_transaksi'],
					'referensi_id' => NULL,
					'blok_stasiun_id' => NULL,
					'kegiatan_id' => NULL, // $value['kegiatan_id'],
					'kendaraan_mesin_id' =>NULL// $retrieve_header['kendaraan_id']
				);
				$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataKredit);
			}
		}

		$res = $this->WrkKegiatanMillModel->posting($id, null);
		if (!empty($res)) {
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
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
		$d1 = $periodeAkunting['tgl_awal'];
		$d2 = $periodeAkunting['tgl_akhir'];
		$last_date_in_periode = $d2;
		// $res_transit_akun = $this->db->query("SELECT * from acc_auto_jurnal 
		// where kode='TRAKSI_TRANSIT_AKUN'")->row_array();
		// if (empty($res_transit_akun)) {
		// 	$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
		// 	return;
		// }
		$res_workshop_dialokasi_akun = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='WORKSHOP_DIALOKASI_AKUN'")->row_array();
		if (empty($res_workshop_dialokasi_akun)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		// $transit_akun = $res_transit_akun['acc_akun_id'];
		$workshop_dialokasi_akun = $res_workshop_dialokasi_akun['acc_akun_id'];

		$res_traksi_reparasi_akun = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='TRAKSI_TRANSIT_REPARASI'")->row_array();
		if (empty($res_workshop_dialokasi_akun)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		// $transit_akun = $res_transit_akun['acc_akun_id'];
		$traksi_reparasi_akun = $res_traksi_reparasi_akun['acc_akun_id'];
		/* Hapus dulu jurnal alokasi traksi jika sudah ada pada bulan tsb.*/
		$q = "delete from acc_jurnal_dt where jurnal_id in(select id from acc_jurnal_ht
                 where tanggal='" . $last_date_in_periode . "' and  modul='AUTO_JURNAL_WORKSHOP_ALOKASI'
				 and lokasi_id =" . $lokasi_id . ")";
		$this->db->query($q);

		/* Hapus dulu jurnal alokasi traksi jika sudah ada pada bulan tsb.*/
		$q = "delete from acc_jurnal_dt where jurnal_id in(select id from acc_jurnal_ht
                 where tanggal='" . $last_date_in_periode . "' and  modul='AUTO_JURNAL_WORKSHOP_ALOKASI'
				 and lokasi_id =" . $lokasi_id . ")";
		$this->db->query($q);


		$qtransit_account = "SELECT b.kendaraan_mesin_id,d.kode as kode_kendaraan,d.nama as nama_kendaraan,sum(debet-kredit)as nilai FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
		on a.id=b.jurnal_id
		inner join trk_kendaraan d on b.kendaraan_mesin_id=d.id
		where a.lokasi_id =" . $lokasi_id . " and 
		a.tanggal >='" . $d1 . "' and a.tanggal <='" . $d2 . "'
		and b.acc_akun_id in (select id from acc_akun where kode like '4%')
		group by b.kendaraan_mesin_id,d.kode ,d.nama ";
		$res_transit_account = $this->db->query($qtransit_account)->result_array();
		$workshop = array();
		foreach ($res_transit_account as $transit_account) {
			$nilai = $transit_account['nilai'];
			$workshop[$transit_account['kendaraan_mesin_id']] = $nilai;
			$q = "SELECT a.*,b.kode as kode_kendaraan,b.nama as nama_kendaraan,a.lama_perbaikan
			FROM wrk_kegiatan_mill_ht a 
			inner join trk_kendaraan b on a.kendaraan_mesin_id=b.id
			where a.lokasi_id =" . $lokasi_id . " and 
			a.kendaraan_mesin_id=" . $transit_account['kendaraan_mesin_id'] . "
			and a.tanggal >='" . $d1 . "' and a.tanggal <='" . $d2 . "'";
			$res_trk = $this->db->query($q)->result_array();
			$total_jam_perbaikan = 0;
			/* ambil total km/hm dlm periode utk kendaraannya */
			foreach ($res_trk as $trk) {
				$total_jam_perbaikan = $total_jam_perbaikan + $trk['lama_perbaikan'];
			}
			$this->load->library('Autonumber');
			$no_jurnal = $this->autonumber->jurnal_auto($lokasi_id, $last_date_in_periode, 'ALWK');
			$dataH = array(
				'no_jurnal' => $no_jurnal,
				'lokasi_id' => $lokasi_id,
				'tanggal' => $last_date_in_periode,
				'no_ref' => '',
				'ref_id' => null,
				'tipe_jurnal' => 'AUTO',
				'modul' => 'AUTO_JURNAL_WORKSHOP_ALOKASI',
				'keterangan' => 'AUTO_JURNAL_WORKSHOP_ALOKASI:' . $transit_account['nama_kendaraan'],
				'is_posting' => 1,
				'diposting_oleh' => $this->user_id
			);
			$id_header = $this->AccJurnalModel->create_header($dataH);
			$dataKredit = array(
				'lokasi_id' => $lokasi_id,
				'jurnal_id' => $id_header,
				'acc_akun_id' => $workshop_dialokasi_akun, //akun ,
				'debet' => 0,
				'kredit' => ($nilai),
				'ket' => 'AUTO_JURNAL_TRAKSI_ALOKASI:' . $transit_account['nama_kendaraan'],
				'no_referensi' => '',
				'referensi_id' => NULL,
				'blok_stasiun_id' => NULL,
				'kegiatan_id' => NULL,
				'kendaraan_mesin_id' => $transit_account['kendaraan_mesin_id']
			);
			$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataKredit);
			foreach ($res_trk as $trk) {
				$total_jam_perbaikan = $total_jam_perbaikan + $trk['lama_perbaikan'];
				$nilai_alokasi = $trk['lama_perbaikan'] / $total_jam_perbaikan * $nilai;
				$dataDebet = array(
					'lokasi_id' => $lokasi_id,
					'jurnal_id' => $id_header,
					'acc_akun_id' => $trk['acc_akun_id'], //akun ,
					'debet' => $nilai_alokasi,
					'kredit' => 0,
					'ket' => 'AUTO_JURNAL_TRAKSI_ALOKASI:' . $transit_account['nama_kendaraan'] . ', BLOK:' . $trk['blok'] . ', Kegiatan:' . $trk['kegiatan'],
					'no_referensi' => '',
					'referensi_id' => NULL,
					'blok_stasiun_id' => $trk['blok_id'],
					'kegiatan_id' => $trk['kegiatan_id'],
					'kendaraan_mesin_id' => $transit_account['kendaraan_mesin_id']
				);
				$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataDebet);
			}
		}
		$this->set_response(array("status" => "OK", "data" => 'Proses berhasil. ' . count($res_transit_account) . " data diproses"), REST_Controller::HTTP_CREATED);

		// if (($hasil['jum']) > 0) {

		// 	$this->set_response(array("status" => "OK", "data" => 'Proses berhasil.' . $hasil['jum'] . " data diproses"), REST_Controller::HTTP_CREATED);
		// } else {
		// 	$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data yang diproses"), REST_Controller::HTTP_OK);
		// }
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
		c.nama AS kendaraan,
		d.nama as workshop,
		a.id AS id
		FROM wrk_kegiatan_mill_ht a 
		INNER JOIN gbm_organisasi b ON a.lokasi_id=b.id
		INNER JOIN trk_kendaraan c ON a.kendaraan_mesin_id=c.id
		INNER JOIN gbm_organisasi d ON a.workshop_id=d.id
		WHERE a.id=" . $id . "
		";
		$dataHeader = $this->db->query($queryHeader)->row_array();

		$queryDetail = "SELECT 
		a.*,
		b.nama AS karyawan,
		a.id AS id
		FROM wrk_kegiatan_mill_dt a 
		INNER JOIN karyawan b on a.karyawan_id=b.id
		WHERE a.wrk_kegiatan_mill_id=" . $id . "
		";
		$dataDetail = $this->db->query($queryDetail)->result_array();

		$queryDetailItem = "SELECT 
		a.*,
		c.nama AS item,
		d.nama AS uom,
		a.id AS id
		FROM wrk_kegiatan_mill_item a 
		INNER JOIN inv_item c on a.item_id=c.id 
		LEFT JOIN gbm_uom d on c.uom_id=d.id
		WHERE a.wrk_kegiatan_mill_id=" . $id . "
		";
		$dataDetailItem = $this->db->query($queryDetailItem)->result_array();



		// $perminta = $this->InvPermintaanBarangModel->print_slip($id);
		$data['header'] = 	$dataHeader;
		$data['detail'] = 	$dataDetail;
		$data['detail_item'] = 	$dataDetailItem;

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
			'lokasi_id' => 252,
			'tgl_mulai' => '2020-01-01',
			'tgl_akhir' => '2022-12-18',
		];
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
		a.id AS id
		
		FROM wrk_kegiatan_mill_item a
		
		INNER JOIN wrk_kegiatan_mill_ht b ON a.wrk_kegiatan_mill_id=b.id
		INNER JOIN gbm_organisasi c ON b.lokasi_id=c.id
		INNER JOIN inv_item d ON a.item_id=d.id

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

		$html = $this->load->view('wrk_kegiatan_mill_Material_Laporan', $data, true);

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
		a.id AS id
		
		FROM wrk_kegiatan_mill_dt a
		
		INNER JOIN wrk_kegiatan_mill_ht b on a.wrk_kegiatan_mill_id=b.id
		INNER JOIN karyawan c ON a.karyawan_id=c.id

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

		$html = $this->load->view('wrk_kegiatan_mill_Frestasi_Laporan', $data, true);

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
