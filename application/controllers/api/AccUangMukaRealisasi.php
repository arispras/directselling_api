<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

class AccUangMukaRealisasi extends BD_Controller
{
	public $user_id;
	public $theCredential;
	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('AccUangMukaRealisasiModel');
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

		$query  = "SELECT a.*,b.no_transaksi as no_uang_muka,b.tanggal as tanggal_uang_muka
		from acc_uang_muka_realisasi a 
	   inner join acc_uang_muka b on a.acc_uang_muka_id =b.id ";
		$search = array('a.no_transaksi', 'a.tanggal', 'b.tanggal', 'b.no_transaksi', 'a.keterangan');
		$where  = null;

		$isWhere = null;
		// $isWhere = 'artikel.deleted_at IS NULL';

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	function index_get($segment_3 = '')
	{
		$id = $segment_3;
		$retrieve = $this->AccUangMukaRealisasiModel->retrieve($id);
		$retrieve_detail = $this->AccUangMukaRealisasiModel->retrieve_detail($id);
		$retrieve['detail'] = $retrieve_detail;

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getDetail_get($id = '')
	{
		$retrieve = $this->AccKasbankModel->retrieve_detail($id);
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAll_get()
	{

		$retrieve = $this->AccUangMukaRealisasiModel->retrieve_all();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAllUangMukaBlmRealisasi_get()
	{

		$retrieve = $this->db->query("select * from acc_uang_muka
		where is_posting=1 and id not in(select acc_uang_muka_id  from acc_uang_muka_realisasi
		)")->result_array();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => []), REST_Controller::HTTP_OK);
		}
	}
	function getAkunSalesInvoice_get()
	{

		$retrieve = $this->db->query("select a.*,b.kode as kode_akun_debet,b.nama as nama_akun_debet,
		c.kode as kode_akun_kredit,c.nama as nama_akun_kredit
		from acc_auto_jurnal a
		inner join acc_akun b on a.acc_akun_id_debet =b.id
		inner join acc_akun c on a.acc_akun_id_kredit=c.id
		where a.kode='SALES_INVOICE'")->result_array();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function create_post()
	{
		$data = $this->post();
		$data['dibuat_oleh'] = $this->user_id;
		$this->load->library('Autonumber');
		$data['no_transaksi']=$this->autonumber->acc_uangmuka_realisasi($data['lokasi_id']['id'],$data['tanggal']);

		$retrieve =  $this->AccUangMukaRealisasiModel->create($data);
		$this->set_response(array("status" => "OK", "data" => $data['no_transaksi']), REST_Controller::HTTP_OK);
	}

	function update_post($segment_3 = '')
	{

		$id = (int)$segment_3;
		$item = $this->AccUangMukaRealisasiModel->retrieve($id);
		if (empty($item)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$data = $this->post();
		$data['diubah_oleh'] = $this->user_id;
		$retrieve =   $this->AccUangMukaRealisasiModel->update($item['id'], $data);
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
	}

	function index_delete($segment_3 = '')
	{

		$id = (int)$segment_3;
		$item = $this->AccUangMukaRealisasiModel->retrieve($id);
		if (empty($item)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$retrieve =  $this->AccUangMukaRealisasiModel->delete($item['id']);
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
	}

	function posting_post($segment_3 = null)
	{
		$id = (int)$segment_3;
		$data = $this->post();
		$retrieve_header = $this->AccUangMukaRealisasiModel->retrieve($id);
		
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
		
		$retrieve_detail = $this->AccUangMukaRealisasiModel->retrieve_detail($id);
		$retrieve_inter_akun = $this->db->query("SELECT * FROM acc_inter_unit
		 where tipe='INTER' ")->result_array();
		$akun_inter = array();
		foreach ($retrieve_inter_akun as $key => $akun) {
			$akun_inter[$akun['lokasi_id']][$akun['lokasi_id_2']] = $akun['acc_akun_id'];
		}
		$nilai_um =	$retrieve_header['nilai_uang_muka'];
		$nilai_realisasi =	$retrieve_header['nilai_realisasi'];
		$nilai_selisih =	$nilai_um - $nilai_realisasi;
		// Hapus jurnal jika sdh ada berdasarkan no refernsi dan MOdul  //
		$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'REALISASI_UANG_MUKA');
		// Data HEADER
		$this->load->library('Autonumber');
		$no_jurnal = $this->autonumber->jurnal_auto($retrieve_header['lokasi_id'], $retrieve_header['tanggal'], 'RUM');
		$dataH = array(
			'no_jurnal' => $no_jurnal,
			'lokasi_id' => $retrieve_header['lokasi_id'],
			'tanggal' => $retrieve_header['tanggal'],
			'no_ref' => $retrieve_header['no_transaksi'],
			'ref_id' => $retrieve_header['id'],
			'tipe_jurnal' => 'AUTO',
			'modul' => 'REALISASI_UANG_MUKA',
			'keterangan' => ''. $retrieve_header['keterangan'],
			'is_posting' => 1,
			'diposting_oleh' => $this->user_id
		);
		$id_header = $this->AccJurnalModel->create_header($dataH);
		// Data UM
		$dataJurnal = array(
			'lokasi_id' => $retrieve_header['lokasi_id'],
			'jurnal_id' => $id_header,
			'acc_akun_id' => $retrieve_header['acc_akun_uang_muka_id'], //$value['acc_akun_id'],
			'debet' => 0,
			'kredit' => $nilai_um, // Akun Lawan Biaya
			'ket' => ''. $retrieve_header['keterangan'],
			'no_referensi' => $retrieve_header['no_transaksi'],
			'referensi_id' => NULL,
			'blok_stasiun_id' => NULL,
			'kegiatan_id' => NULL, // $value['kegiatan_id'],
			'kendaraan_mesin_id' => NULL
		);
		$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataJurnal);
		if ($nilai_selisih < 0) {
			// Data Kas Bank
			$dataJurnal = array(
				'lokasi_id' => $retrieve_header['lokasi_id'],
				'jurnal_id' => $id_header,
				'acc_akun_id' => $retrieve_header['acc_akun_kasbank_id'], //akun biaya pemeliharaan,
				'debet' => 0,
				'kredit' => ($nilai_selisih * -1),
				'ket' =>''. $retrieve_header['keterangan'],
				'no_referensi' => $retrieve_header['no_transaksi'],
				'referensi_id' => NULL,
				'blok_stasiun_id' => NULL,
				'kegiatan_id' => NULL,
				'kendaraan_mesin_id' => NULL
			);

		} elseif ($nilai_selisih > 0) {
			// Data Kas Bank
			$dataJurnal = array(
				'lokasi_id' => $retrieve_header['lokasi_id'],
				'jurnal_id' => $id_header,
				'acc_akun_id' => $retrieve_header['acc_akun_kasbank_id'], //akun biaya pemeliharaan,
				'debet' => $nilai_selisih,
				'kredit' => 0,
				'ket' => $retrieve_header['keterangan'],
				'no_referensi' => $retrieve_header['no_transaksi'],
				'referensi_id' => NULL,
				'blok_stasiun_id' => NULL,
				'kegiatan_id' => NULL,
				'kendaraan_mesin_id' => NULL
			);
			$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataJurnal);

		} else {
		}
		/* Jurnal Akun Inter unit lawan kasbank&UM pada lokasinya kasbank&UM  jika beda lokasi */
		foreach ($retrieve_detail as $key => $value) {
			/* cek beda lokasi hd vs dt utk menjurnal inter akunting, jika beda buat jurnal inter unit */
			if ($value['lokasi_id'] != $retrieve_header['lokasi_id']) {
				$dr_dt = 0;
				$cr_dt = 0;
				if ($value['debet'] > 0) {
					$cr_dt = 0;
					$dr_dt = $value['debet'];
				}
				if ($value['kredit'] > 0) {
					$cr_dt = $value['kredit'];
					$dr_dt = 0;
				}
				$inter_akun_id = null;
				if ($akun_inter[$retrieve_header['lokasi_id']][$value['lokasi_id']]) {
					$inter_akun_id = $akun_inter[$retrieve_header['lokasi_id']][$value['lokasi_id']];
				}
				if ($akun_inter[$value['lokasi_id']][$retrieve_header['lokasi_id']]) {
					$inter_akun_id = $akun_inter[$value['lokasi_id']][$retrieve_header['lokasi_id']];
				}
				$dataDt = array(
					'lokasi_id' =>  $retrieve_header['lokasi_id'], // Lokasi headernya
					'jurnal_id' => $id_header,
					'acc_akun_id' => $inter_akun_id, // inter unit akun lokasi Dt 
					'debet' => $dr_dt,
					'kredit' => $cr_dt,
					'ket' => 'Realisasi:' . $value['ket'],
					'no_referensi' => $retrieve_header['no_transaksi'],
					'referensi_id' => NULL,
					'blok_stasiun_id' => NULL,
					'kegiatan_id' => NULL, //kegiatan pemeliharaan,
					'kendaraan_mesin_id' => NULL
				);
				$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataDt);
			}
		}

		/* Jurnal biaya2 Realisai(Lawan Kasbank&UM) */
		foreach ($retrieve_detail as $key => $value) {
			$dataDt = array(
				'lokasi_id' => $value['lokasi_id'],
				'jurnal_id' => $id_header,
				'acc_akun_id' => ($value['acc_akun_id']), //akun 
				'debet' => ($value['debet']),
				'kredit' => ($value['kredit']),
				'ket' => 'Realisasi:' . $value['ket'],
				'no_referensi' => $retrieve_header['no_transaksi'],
				'referensi_id' => NULL,
				'blok_stasiun_id' => NULL,
				'kegiatan_id' => NULL, //kegiatan pemeliharaan,
				'kendaraan_mesin_id' => NULL
			);
			$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataDt);
			//*  jika beda lokasi hd vs dt, kalo beda dibuat jurnal inter unit  //	
			if ($value['lokasi_id'] != $retrieve_header['lokasi_id']) {
				// INTER UNIT 
				$dr_dt = 0;
				$cr_dt = 0;
				if ($value['debet'] > 0) {
					$dr_dt = 0;
					$cr_dt = $value['debet']; // dibalik krn lawannya
				}
				if ($value['kredit'] > 0) {
					$dr_dt = $value['kredit']; // dibalik krn lawannya
					$cr_dt = 0;
				}
				$inter_akun_id = null;
				if ($akun_inter[$retrieve_header['lokasi_id']][$value['lokasi_id']]) {
					$inter_akun_id = $akun_inter[$retrieve_header['lokasi_id']][$value['lokasi_id']];
				}
				if ($akun_inter[$value['lokasi_id']][$retrieve_header['lokasi_id']]) {
					$inter_akun_id = $akun_inter[$value['lokasi_id']][$retrieve_header['lokasi_id']];
				}
				$dataDt = array(
					'lokasi_id' => $value['lokasi_id'],
					'jurnal_id' => $id_header,
					'acc_akun_id' => $inter_akun_id, // inter unit akun lokasi Dt 
					'debet' => $dr_dt,
					'kredit' => $cr_dt,
					'ket' => 'Realisasi:' . $value['ket'],
					'no_referensi' => $retrieve_header['no_transaksi'],
					'referensi_id' => NULL,
					'blok_stasiun_id' => NULL,
					'kegiatan_id' => NULL, //kegiatan pemeliharaan,
					'kendaraan_mesin_id' => NULL
				);
				$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataDt);
			}
		}
		$data['diposting_oleh'] = $this->user_id;
		$res = $this->AccUangMukaRealisasiModel->posting($id, $data);
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


		$hd = $this->db->query("select a.*,f.nama as lokasi, b.no_transaksi as no_uang_muka,b.tanggal as tanggal_uang_muka, c.kode as kode_akun_kasbank,c.nama as nama_akun_kasbank,
		d.kode as kode_akun_uangmuka,d.nama as nama_akun_uangmuka
		from acc_uang_muka_realisasi a INNER join
		acc_uang_muka b on a.acc_uang_muka_id=b.id
	   INNER join acc_akun c on a.acc_akun_kasbank_id=c.id
	   INNER join acc_akun d on a.acc_akun_uang_muka_id=d.id
	   INNER JOIN gbm_organisasi f on a.lokasi_id=f.id
		where a.id=" . $id)->row_array();

		$dt = $this->db->query("select a.*, b.kode as kode_akun,b.nama as nama_akun from acc_uang_muka_realisasi_dt a left join
		acc_akun b on a.acc_akun_id=b.id where a.realisasi_id=" . $id)->result_array();
		$data['dt'] = $dt;
		$selisih = $hd['nilai_uang_muka'] - $hd['nilai_realisasi'];
		$tipe = '';
		$hd['selisih'] =	$selisih;
		if ($selisih < 0) {
			$tipe = 'Payment';
		} else if ($selisih > 0) {
			$tipe = 'Receipt';
		} else {
			$tipe = 'Realisasi';
		}
		$hd['tipe'] = $tipe;
		$data['hd'] = $hd;
		$data['dt'] = $dt;
		$html = $this->load->view('AccSlipRealisasi', $data, true);

		$filename = 'report_Realisasi_' . time();
		$this->pdfgenerator->generate($html, $filename, true, 'A4', 'portrait');
		echo $html;
	}
	function laporan_ar_detail_post()
	{
		error_reporting(0);

		$data = [];
		$lokasi_id = $this->post('lokasi_id', true);
		$customer_id = $this->post('customer_id', true);
		$tanggal_tempo = $this->post('tgl_mulai', true);
		$status = $this->post('status', true);

		$query = "select a.*,b.nama_customer,IFNULL( c.dibayar, 0)as dibayar,a.grand_total -(IFNULL( c.dibayar, 0))as sisa   from acc_uang_muka_realisasi a INNER join gbm_customer b 
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

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		echo $html;
	}




	function laporan_post()
	{
		error_reporting(0);

		$data = [];
		if (isset($this->post()['lokasi_id'])) {
			$input = $this->post();
			$input['lokasi'] = $input['lokasi_id'];
		} else {
			$input = [
				'lokasi_id' => 263,
				'tgl_mulai' => '2000-01-01',
				'tgl_akhir' => '2022-05-11',
			];
		}


		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');

		$query = $this->db->query("SELECT
		a.*,
		-- b.nama AS acc_akun,
		c.nama AS acc_akun_kasbank,
		d.nama AS acc_akun_realisasi,
		e.nama AS acc_akun_uang_muka,
		a.id AS id
		FROM acc_uang_muka_realisasi a
		-- LEFT JOIN acc_uang_muka b ON a.acc_uang_muka_id=b.id 
		LEFT JOIN acc_akun c ON a.acc_akun_kasbank_id=c.id 
		LEFT JOIN acc_akun d ON a.acc_akun_realisasi_id=d.id 
		LEFT JOIN acc_akun e ON a.acc_akun_uang_muka_id=e.id 
		WHERE
		a.lokasi_id = " . $input['lokasi_id'] . "
		&&
		DATE(a.tanggal) >= '" . $input['tgl_mulai'] . "'
		&&
		DATE(a.tanggal) <= '" . $input['tgl_akhir'] . "'
		");

		$input['lokasi'] = $this->db->query("SELECT * FROM gbm_organisasi WHERE id=" . $input['lokasi_id'])->row_array()['nama'];

		$data['data'] = $query->result_array();
		$data['input'] = $input;

		$html = $this->load->view('AccUangMukaRealisasi_laporan', $data, true);

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		echo $html;
	}
}
