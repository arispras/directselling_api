<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

class EstBkmUmum extends BD_Controller
{
	public $user_id;
	public $theCredential;
	public $data = array();
	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('GbmOrganisasiModel');
		$this->load->model('EstBkmUmumModel');
		$this->load->model('AccJurnalModel');
		$this->load->model('AccJurnalUpahModel');
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
		c.nama as rayon_afdeling,
		d.user_full_name AS dibuat,
		e.user_full_name AS diubah,
		f.user_full_name AS diposting 
		FROM est_bkm_umum_ht a 
		left join gbm_organisasi b on a.lokasi_id=b.id
		left join gbm_organisasi c on a.rayon_afdeling_id=c.id
		LEFT JOIN fwk_users d ON a.dibuat_oleh = d.id
		LEFT JOIN fwk_users e ON a.diubah_oleh = e.id
		LEFT JOIN fwk_users f ON a.diposting_oleh = f.id
		";
		$search = array('a.no_transaksi', 'a.tanggal', 'b.nama', 'c.nama', 'a.keterangan');
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
		$isWhere = $isWhere .  " and  a.rayon_afdeling_id in
		(select afdeling_id from fwk_users_afdeling where user_id=" . $this->user_id . ")";

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
	function listBkmUmum_post()
	{
		$format_laporan =  $this->post('format_laporan', true);

		// $id = (int)$segment_3;
		$data = [];

		$input = [
			'no_po' => '01/PO/DPA',
			'lokasi_id' => 252,
			'tgl_mulai' => '2022-01-01',
			'tgl_akhir' => '2022-12-12',
		];

		$lokasi_id = $this->post('lokasi_id', true);
		$tanggal_awal = $this->post('tgl_mulai', true);
		$tanggal_akhir = $this->post('tgl_akhir', true);
		$status_id = $this->post('status_id', true);

		// $lokasi_id = $input['lokasi_id'];
		// $tanggal_awal = $input['tgl_mulai'];
		// $tanggal_akhir = $input['tgl_akhir'];

		$queryBkm = "SELECT a.*,
		b.nama as lokasi,
		c.nama as rayon_afdeling 
		FROM `est_bkm_umum_ht` a 
		left join gbm_organisasi b on a.lokasi_id=b.id
		left join gbm_organisasi c on a.rayon_afdeling_id=c.id
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
		$dataBkm = $this->db->query($queryBkm)->result_array();
		// var_dump($dataBkm);exit();
		$data['bkm'] = 	$dataBkm;
		$data['filter_lokasi'] = 	$filter_lokasi;
		$data['filter_tgl_awal'] = 	$tanggal_awal;
		$data['filter_tgl_akhir'] = $tanggal_akhir;

		$html = $this->load->view('Est_bkm_umum_list', $data, true);

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		echo $html;
	}
	function listBkmUmumDetail_post()
	{
		$format_laporan =  $this->post('format_laporan', true);

		$data = [];

		// $input = [
		// 	'lokasi_id' => 252,
		// 	'tgl_mulai' => '2022-07-01',
		// 	'tgl_akhir' => '2022-09-30',
		// ];

		$lokasi_id = $this->post('lokasi_id', true);
		$tanggal_awal = $this->post('tgl_mulai', true);
		$tanggal_akhir = $this->post('tgl_akhir', true);
		$status_id = $this->post('status_id', true);

		// $lokasi_id = $input['lokasi_id'];
		// $tanggal_awal = $input['tgl_mulai'];
		// $tanggal_akhir = $input['tgl_akhir'];

		$queryhead = "SELECT a.*,
		b.nama as lokasi,
		c.nama as rayon_afdeling
		FROM est_bkm_umum_ht a
		LEFT JOIN gbm_organisasi b ON a.lokasi_id=b.id
		LEFT JOIN gbm_organisasi c ON a.rayon_afdeling_id=c.id
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
			$querydetail = "SELECT a.*,
			b.nama as karyawan,
			a.id as id,
			c.nama AS kegiatan,
			d.keterangan AS absensi,
			d.kode AS kode
			FROM est_bkm_umum_dt a 
			LEFT JOIN karyawan b ON a.karyawan_id=b.id
			LEFT JOIN acc_kegiatan c ON a.kegiatan_id=c.id
			LEFT JOIN hrms_jenis_absensi d ON a.jenis_absensi_id=d.id
			WHERE a.bkm_umum_id=" . $hd['id'] . "";
			$dataDtl = $this->db->query($querydetail)->result_array();
			// var_dump($dataDtl)	
			$hd['detail'] = $dataDtl;
			$result[] = $hd;
		}

		$data['umum'] = 	$result;
		// var_dump($result)	;exit();
		$data['filter_lokasi'] = 	$filter_lokasi;
		$data['filter_tgl_awal'] = 	$tanggal_awal;
		$data['filter_tgl_akhir'] = $tanggal_akhir;

		$html = $this->load->view('Est_bkm_umum_list_detail', $data, true);

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		echo $html;
	}

	function index_get($id = '')
	{
		$retrieve = array();
		$retrieve = $this->EstBkmUmumModel->retrieve_by_id($id);
		$retrieve['detail'] = $this->EstBkmUmumModel->retrieve_detail($id);

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function getDetail_get($id = '')
	{
		$retrieve = $this->EstBkmUmumModel->retrieve_detail($id);
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
		$details = $input['details'];
		foreach ($details as $key => $value) {
			$kegiatan_id = $value['kegiatan_id']['id'];
			$blok_id = $value['blok_id']['id'];
			$kendaraan_id = $value['kendaraan_id']['id'];
			if ($kegiatan_id) {
				if (is_null($blok_id) || empty($blok_id)) {
					$res_akun = $this->db->query("	SELECT a.kode AS kode_kegiatan,a.nama AS nama_kegiatan,b.kode AS kode_akun,b.nama AS nama_akun FROM acc_kegiatan a 
								INNER JOIN acc_akun b ON a.acc_akun_id=b.id WHERE b.kelompok_biaya 
								IN('PNN','PMK','PML') 
								AND a.id=" . $kegiatan_id . "")->row_array();

					if ($res_akun) {
						$msg = "Kegiatan:" . $res_akun['nama_kegiatan'] . " harus diisi Blok ";
						$this->set_response(array("status" => "NOT OK", "data" => $msg), REST_Controller::HTTP_OK);
						return;
					}
				}
				if (is_null($kendaraan_id) || empty($kendaraan_id)) {
					$res_akun = $this->db->query("	SELECT a.kode AS kode_kegiatan,a.nama AS nama_kegiatan,b.kode AS kode_akun,b.nama AS nama_akun FROM acc_kegiatan a 
							INNER JOIN acc_akun b ON a.acc_akun_id=b.id WHERE b.kelompok_biaya 
							IN('TRK') 
							AND a.id=" . $kegiatan_id . "")->row_array();

					if ($res_akun) {
						$msg = "Kegiatan:" . $res_akun['nama_kegiatan'] . " harus diiisi Kendaraan.(Diinput di Modul Traksi)";
						$this->set_response(array("status" => "NOT OK", "data" => $msg), REST_Controller::HTTP_OK);
						return;
					}
				}
			}
		}
		/* END VALIDASI BLOK TIDAK DIISI */

		$input['dibuat_oleh'] = $this->user_id;
		$input['diubah_oleh'] = $this->user_id;

		$this->load->library('Autonumber');
		$input['no_transaksi'] = $this->autonumber->est_bkm_umum($input['lokasi_id']['id'], $input['tanggal']);


		$res = $this->EstBkmUmumModel->create($input);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'est_bkm_umum', 'action' => 'new', 'entity_id' => $res);
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

		/* START VALIDASI BLOK TIDAK DIISI JIKA AKUN->Kelompok Biaya=PNN,PMK,PML*/
		$details = $data['details'];
		foreach ($details as $key => $value) {
			$kegiatan_id = $value['kegiatan_id']['id'];
			$blok_id = $value['blok_id']['id'];
			$kendaraan_id = $value['kendaraan_id']['id'];
			if ($kegiatan_id) {
				if (is_null($blok_id) || empty($blok_id)) {
					$res_akun = $this->db->query("	SELECT a.kode AS kode_kegiatan,a.nama AS nama_kegiatan,b.kode AS kode_akun,b.nama AS nama_akun FROM acc_kegiatan a 
										INNER JOIN acc_akun b ON a.acc_akun_id=b.id WHERE b.kelompok_biaya 
										IN('PNN','PMK','PML') 
										AND a.id=" . $kegiatan_id . "")->row_array();

					if ($res_akun) {
						$msg = "Kegiatan:" . $res_akun['nama_kegiatan'] . " harus diisi Blok";
						$this->set_response(array("status" => "NOT OK", "data" => $msg), REST_Controller::HTTP_OK);
						return;
					}
				}
				if (is_null($kendaraan_id) || empty($kendaraan_id)) {
					$res_akun = $this->db->query("	SELECT a.kode AS kode_kegiatan,a.nama AS nama_kegiatan,b.kode AS kode_akun,b.nama AS nama_akun FROM acc_kegiatan a 
							INNER JOIN acc_akun b ON a.acc_akun_id=b.id WHERE b.kelompok_biaya 
							IN('TRK') 
							AND a.id=" . $kegiatan_id . "")->row_array();

					if ($res_akun) {
						$msg = "Kegiatan:" . $res_akun['nama_kegiatan'] . " harus diiisi Kendaraan.(Diinput di Modul Traksi)";
						$this->set_response(array("status" => "NOT OK", "data" => $msg), REST_Controller::HTTP_OK);
						return;
					}
				}
			}
		}
		/* END VALIDASI BLOK TIDAK DIISI */

		$res = $this->EstBkmUmumModel->update($id, $data);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'est_bkm_umum', 'action' => 'edit', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	public function index_delete($id)
	{
		$res = $this->EstBkmUmumModel->delete($id);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id' => $id)), 'entity' => 'est_bkm_umum', 'action' => 'delete', 'entity_id' => $id);
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
		where kode='ABSEN_UMUM_UPAH'")->row_array();
		if (empty($res_akun_transit_upah)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$res_akun_transit_premi = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='ABSEN_UMUM_PREMI'")->row_array();
		if (empty($res_akun_transit_premi)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}

		$akun_debet_transit_premi = $res_akun_transit_premi['acc_akun_id_debet'];
		$akun_kredit_transit_premi = $res_akun_transit_premi['acc_akun_id_kredit'];
		$akun_debet_transit_upah = $res_akun_transit_upah['acc_akun_id_debet'];
		$akun_kredit_transit_upah = $res_akun_transit_upah['acc_akun_id_kredit'];

		$retrieve_header = $this->EstBkmUmumModel->retrieve_by_id($id);
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

		$retrieve_detail = $this->db->query("
		SELECT a.*,b.*,c.nip,c.nama as nama_karyawan,d.acc_akun_id,
		g.statusblok as umur_tanam_blok,g.tahuntanam,
		f.kode as kode_blok,f.nama as nama_blok,b.blok_id as blok_id
		FROM est_bkm_umum_ht a 
		inner join est_bkm_umum_dt b on a.id=b.bkm_umum_id 
		inner join karyawan c on b.karyawan_id=c.id 
		LEFT JOIN acc_kegiatan d ON b.kegiatan_id=d.id
		LEFT JOIN acc_akun e ON d.acc_akun_id=e.id
		LEFT JOIN gbm_organisasi f ON b.blok_id=f.id
		LEFT JOIN gbm_blok g ON f.id=g.organisasi_id
		WHERE b.bkm_umum_id=" . $id . "")->result_array();

		// Hapus jurnal jika sdh ada berdasarkan no refernsi dan MOdul  //
		$this->AccJurnalUpahModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'BKM-UMUM');
		$total_pendapatan = 0;


		// Data HEADER
		$this->load->library('Autonumber');
		$no_jurnal = $this->autonumber->jurnal_auto($retrieve_header['lokasi_id'], $retrieve_header['tanggal'], 'BKM-UM');
		$id_header = 0;
		$dataH = array(
			'lokasi_id' => $retrieve_header['lokasi_id'],
			'no_jurnal' => $no_jurnal,
			'lokasi_id' => $retrieve_header['lokasi_id'],
			'tanggal' => $retrieve_header['tanggal'],
			'no_ref' => $retrieve_header['no_transaksi'],
			'ref_id' => $retrieve_header['id'],
			'tipe_jurnal' => 'AUTO',
			'modul' => 'BKM_UMUM',
			'keterangan' => 'BKM_UMUM',
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
					'acc_akun_id' => ($value['acc_akun_id']) ? $value['acc_akun_id'] : $akun_debet_transit_upah,
					'debet' => ($upah),
					'kredit' => 0,
					'ket' => 'BKM_UMUM:Upah Karyawan: ' . $value['nama_karyawan'] . "",
					'no_referensi' => $retrieve_header['no_transaksi'],
					'referensi_id' => NULL,
					'blok_stasiun_id' => $value['blok_id'],
					'umur_tanam_blok' => $value['umur_tanam_blok'],
					'divisi_id' => $value['rayon_afdeling_id'],
					'kegiatan_id' => $value['kegiatan_id'],
					'kendaraan_mesin_id' =>  $value['kendaraan_id'],
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
					'ket' => 'BKM_UMUM:Upah Karyawan: ' . $value['nama_karyawan'] . "",
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
					'acc_akun_id' => ($value['acc_akun_id']) ? $value['acc_akun_id'] : $akun_debet_transit_premi,
					'debet' => ($premi),
					'kredit' => 0,
					'ket' => 'BKM_UMUM:Premi Karyawan: ' . $value['nama_karyawan'] . "",
					'no_referensi' => $retrieve_header['no_transaksi'],
					'referensi_id' => NULL,
					'blok_stasiun_id' => $value['blok_id'],
					'umur_tanam_blok' => $value['umur_tanam_blok'],
					'divisi_id' => $value['rayon_afdeling_id'],
					'kegiatan_id' => $value['kegiatan_id'],
					'kendaraan_mesin_id' =>  $value['kendaraan_id'],
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
					'ket' => 'BKM_UMUM Premi Karyawan: ' . $value['nama_karyawan'] . "",
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
		$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'BKM_UMUM');
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
			$no_jurnal = $this->autonumber->jurnal_auto($retrieve_header['lokasi_id'], $retrieve_header['tanggal'], 'BKM-UM');

			$dataH = array(
				'no_jurnal' => $no_jurnal,
				'lokasi_id' => $retrieve_header['lokasi_id'],
				'tanggal' => $retrieve_header['tanggal'],
				'no_ref' => $retrieve_header['no_transaksi'],
				'ref_id' => $retrieve_header['id'],
				'tipe_jurnal' => 'AUTO',
				'modul' => 'BKM_UMUM',
				'keterangan' => 'TRANSAKSI BKM_UMUM',
				'is_posting' => 1,
			);
			$id_header2 = $this->AccJurnalModel->create_header($dataH);

			foreach ($resJurnalTemp as $key => $JurnalTemp) {
				$ket = 'Bkm Umum';
				// if ($JurnalTemp['blok']) {
				// 	$ket = 'Panen (Upah/premi) Blok: ' . $JurnalTemp['blok'];
				// } else {
				// 	$ket = 'Panen (Upah/premi)';
				// }
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
					'umur_tanam_blok' => $JurnalTemp['umur_tanam_blok'],
					'karyawan_id' => ($JurnalTemp['debet'] > 0) ? 0 : NULL,

				);
				$id_dtl = $this->AccJurnalModel->create_detail($id_header2, $dataDebetKredit);
			}
		}
		$input['diposting_oleh'] = $this->user_id;
		$res = $this->EstBkmUmumModel->posting($id, $input);
		if (!empty($res)) {
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function posting_batch_post($lokasi_id = null, $t1 = null, $t2 = null)
	{

		$res_akun_transit_upah = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='ABSEN_UMUM_UPAH'")->row_array();
		if (empty($res_akun_transit_upah)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$res_akun_transit_premi = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='ABSEN_UMUM_PREMI'")->row_array();
		if (empty($res_akun_transit_premi)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}

		$akun_debet_transit_premi = $res_akun_transit_premi['acc_akun_id_debet'];
		$akun_kredit_transit_premi = $res_akun_transit_premi['acc_akun_id_kredit'];
		$akun_debet_transit_upah = $res_akun_transit_upah['acc_akun_id_debet'];
		$akun_kredit_transit_upah = $res_akun_transit_upah['acc_akun_id_kredit'];

		$res_transaksi_umum = $this->db->query("SELECT * from est_bkm_umum_ht where  
		tanggal between '" . $t1 . "' and '" . $t2 . "' and is_posting=0 
		and lokasi_id=" . $lokasi_id . " order by tanggal")->result_array();
		$id = null;
		$jum = 0;
		$id_header = 0;
		foreach ($res_transaksi_umum  as $key => $retrieve_header) {
			$jum++;
			$id = $retrieve_header['id'];
			// $retrieve_header = $this->EstBkmUmumModel->retrieve_by_id($id);
			$retrieve_detail = $this->db->query("SELECT a.*,b.*,c.nip,c.nama as nama_karyawan,d.acc_akun_id
			FROM est_bkm_umum_ht a 
			inner join est_bkm_umum_dt b on a.id=b.bkm_umum_id 
			inner join karyawan c on b.karyawan_id=c.id 
			LEFT JOIN acc_kegiatan d ON b.kegiatan_id=d.id
			LEFT JOIN acc_akun e ON d.acc_akun_id=e.id
			WHERE b.bkm_umum_id=" . $id . "")->result_array();

			// Hapus jurnal jika sdh ada berdasarkan no refernsi dan MOdul  //
			$this->AccJurnalUpahModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'BKM_UMUM');
			$total_pendapatan = 0;


			// Data HEADER
			$this->load->library('Autonumber');
			$no_jurnal = $this->autonumber->jurnal_upah_auto($retrieve_header['lokasi_id'], $retrieve_header['tanggal'], 'BKM-UM');

			$dataH = array(
				'lokasi_id' => $retrieve_header['lokasi_id'],
				'no_jurnal' => $no_jurnal,
				'lokasi_id' => $retrieve_header['lokasi_id'],
				'tanggal' => $retrieve_header['tanggal'],
				'no_ref' => $retrieve_header['no_transaksi'],
				'ref_id' => $retrieve_header['id'],
				'tipe_jurnal' => 'AUTO',
				'modul' => 'BKM_UMUM',
				'keterangan' => 'BKM_UMUM',
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
						'acc_akun_id' => ($value['acc_akun_id']) ? $value['acc_akun_id'] : $akun_debet_transit_upah,
						'debet' => ($upah),
						'kredit' => 0,
						'ket' => 'BKM_UMUM:Upah Karyawan: ' . $value['nama_karyawan'] . "",
						'no_referensi' => $retrieve_header['no_transaksi'],
						'referensi_id' => NULL,
						'blok_stasiun_id' => NULL,
						'divisi_id' => $value['rayon_afdeling_id'],
						'kegiatan_id' => $value['kegiatan_id'], //kegiatan panen,
						'kendaraan_mesin_id' => NULL,
						'karyawan_id' => $value['karyawan_id'],
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
						'ket' => 'BKM_UMUM:Upah Karyawan: ' . $value['nama_karyawan'] . "",
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
						'acc_akun_id' => ($value['acc_akun_id']) ? $value['acc_akun_id'] : $akun_debet_transit_premi,
						'debet' => ($premi),
						'kredit' => 0,
						'ket' => 'BKM_UMUM:Premi Karyawan: ' . $value['nama_karyawan'] . "",
						'no_referensi' => $retrieve_header['no_transaksi'],
						'referensi_id' => NULL,
						'blok_stasiun_id' => NULL,
						'divisi_id' => $value['rayon_afdeling_id'],
						'kegiatan_id' => $value['kegiatan_id'],
						'kendaraan_mesin_id' =>  NULL,
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
						'ket' => 'BKM_UMUM Premi Karyawan: ' . $value['nama_karyawan'] . "",
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

			$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'BKM_UMUM');
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
				$no_jurnal = $this->autonumber->jurnal_auto($retrieve_header['lokasi_id'], $retrieve_header['tanggal'], 'BKM-UM');

				$dataH = array(
					'no_jurnal' => $no_jurnal,
					'lokasi_id' => $retrieve_header['lokasi_id'],
					'tanggal' => $retrieve_header['tanggal'],
					'no_ref' => $retrieve_header['no_transaksi'],
					'ref_id' => $retrieve_header['id'],
					'tipe_jurnal' => 'AUTO',
					'modul' => 'BKM_UMUM',
					'keterangan' => 'TRANSAKSI BKM_UMUM',
					'is_posting' => 1,
				);
				$id_header2 = $this->AccJurnalModel->create_header($dataH);

				foreach ($resJurnalTemp as $key => $JurnalTemp) {
					$ket = 'BKM Umum';
					// if ($JurnalTemp['blok']) {
					// 	$ket = 'Panen (Upah/premi) Blok: ' . $JurnalTemp['blok'];
					// } else {
					// 	$ket = 'Panen (Upah/premi)';
					// }
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

			$res = $this->EstBkmUmumModel->posting($id, null);
			// if (!empty($res)) {
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
	function hitungPremiMandorKerani_post()
	{
		$input = $this->post();
		$this->hitung_premi_mandor_kerani($input);
	}
	function hitung_premi($input)
	{

		// $details_denda = $input['details_denda'];
		// $total_denda = 0;
		// foreach ($details_denda as $key_denda => $value_denda) {
		// 	$total_denda += ($value_denda['qty'] * $value_denda['nilai']);
		// }

		// $jjg = (float)$input['hasil_kerja_jjg'];
		// $resBjr = $this->db->query("select bjr from est_bjr where blok_id=" . $input['blok_id']['id'] . " ")->row_array();
		// $stdBJR = $resBjr['bjr'];
		// $hasilkerjakg = ($jjg * $stdBJR);


		// $resBasisBlok = $this->db->query("select * from est_premi_basis_panen where blok_id='" . $input['blok_id']['id'] . "'
		//  and tanggal_efektif <='" . ($input['tanggal']) . "'
		//  order by tanggal_efektif desc limit 1 ")->row_array();
		// $premi = 0;
		// $premibrondolan = $input['hasil_kerja_brondolan'] * $resBasisBlok['premi_brondolan'];
		// if ($jjg >= $resBasisBlok['basis_jjg']) {
		// 	$premi = (float) $resBasisBlok['premi_basis'];
		// } else {
		// 	$premi = 0;
		// }

		// Premi Lebih Basis
		//Lebih basis 1
		// $premlb = 0;
		// $sisa1 = $jjg - $resBasisBlok['basis_jjg'];
		// if ($sisa1 > $resBasisBlok['lebih_basis1']) {
		// 	$premlb += $sisa1 * $resBasisBlok['premi_lebih_basis1'];
		// } else {
		// 	$premlb += 0;
		// }
		// $this->set_response(array("status" => "OK", "data" =>$premlb), REST_Controller::HTTP_CREATED);
		// return;
		//Lebih basis 2
		// $sisa2 = $sisa1 - $resBasisBlok['lebih_basis1'];
		// if ($sisa2 > $resBasisBlok['lebih_basis2']) {
		// 	$premlb += $sisa2 * $resBasisBlok['premi_lebih_basis2'];
		// } else {
		// 	$premlb += 0;
		// }

		//Lebih basis 3
		// $sisa3 = $sisa2 - $resBasisBlok['lebih_basis2'];
		// if ($sisa3 > $resBasisBlok['lebih_basis3']) {
		// 	$premlb += $sisa3 * $resBasisBlok['premi_lebih_basis3'];
		// } else {
		// 	$premlb += 0;
		// }

		$resGaji = $this->db->query("select * from payroll_karyawan_gaji where karyawan_id=" . $input['karyawan_id']['id'] . " ")->row_array();
		$upahharian = ($resGaji['gapok'] / 25);
		// $basis = $resBasisBlok['basis_jjg'];
		// $upahpenalty = 0;
		// if ($input['hasil_kerja_jjg'] <= $basis) {
		// 	$capaibasis = ($basis - $input['hasil_kerja_jjg']) / $basis;
		// 	$upahpenalty = $upahharian * $capaibasis;
		// }

		$res = array();
		// $totalpendapatan = (($upahharian + $premibrondolan + $premi + $premlb) - ($upahpenalty + $total_denda));
		// $jumlah_hk = 0;
		// if ($upahharian > 0) {
		// 	$jumlah_hk = ($upahharian - $upahpenalty) / $upahharian;
		// }

		$res = array(
			'rupiah_hk' => $upahharian,
			// 'arr_premi_basis_panen' => $resBasisBlok,
			// 'basis_jjg' => (float) $resBasisBlok['basis_jjg'],
			// 'hasil_kerja_kg' => $hasilkerjakg,
			// 'premi_brondolan' => $premibrondolan,
			// 'premi_panen' => $premi + $premlb,
			// 'denda_basis' => $upahpenalty,
			// 'denda_panen' => $total_denda,
			// 'bjr' => $stdBJR,
			// 'jumlah_hk' => $jumlah_hk,
			// 'total_pendapatan' => $totalpendapatan,
			// 'rp_hk' => $upahharian,
			// 'upah_premi_lebih_basis' => $premlb,
			// 'rpkgkontanan' => 0,
			// 'kontanan' => 0,
			// 'premi_basis' => $premi
		);

		if (!empty($res)) {
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function hitung_premi_mandor_kerani($input)
	{
		$lokasi_id = $input['lokasi_id']['id'];
		$details = $input['details'];
		$total_premi = 0;
		$total_upah = 0;
		$jum_karyawan = 0;
		$jumlah_nilai_denda = 0;
		foreach ($details as $key => $value) {
			$total_premi =	$total_premi + $value['premi_brondolan'] + $value['premi_panen'];
			$total_upah =	$total_upah + $value['rp_hk'];
			$jum_karyawan++;

			$details_denda = $value['details_denda'];
			foreach ($details_denda as $key_denda => $value_denda) {
				$jumlah_nilai_denda = $jumlah_nilai_denda + ($value_denda['qty'] * $value_denda['nilai']);
			}
		}

		/* BAGIAN MULAI UTK HITUNG PREMI DAN UPAH MANDOR/KRANI */
		$q0 = "SELECT a.*,b.status_pajak FROM  payroll_karyawan_gaji a inner join karyawan b on a.karyawan_id=b.id
		where a.karyawan_id=" .  $input['mandor_id']['id'] . "";
		$mandor = $this->db->query($q0)->row_array();
		$upah_mandor = $mandor['gapok'] / 25;
		$q0 = "SELECT a.*,b.status_pajak FROM  payroll_karyawan_gaji a inner join karyawan b on a.karyawan_id=b.id
		where a.karyawan_id=" .  $input['kerani_id']['id'] . "";
		$kerani = $this->db->query($q0)->row_array();
		$upah_kerani = $kerani['gapok'] / 25;
		$persen_premi_mandor = 0;
		$persen_premi_kerani = 0;
		$jumlah_karyawan_mandor = 10;
		$jumlah_karyawan_kerani = 10;
		$q0 = "SELECT * FROM  est_param_premi_mandor_kerani 
		where lokasi_id=" .  $lokasi_id . " and jabatan_id='MANDOR'";
		$p_mandor = $this->db->query($q0)->row_array();
		if ($p_mandor) {
			$jumlah_karyawan_mandor = $p_mandor['jumlah_karyawan'];
			$persen_premi_mandor = $p_mandor['persen_premi'];
		}
		$q0 = "SELECT * FROM  est_param_premi_mandor_kerani 
		where lokasi_id=" .  $lokasi_id . " and jabatan_id='KERANI'";
		$p_kerani = $this->db->query($q0)->row_array();
		if ($p_kerani) {
			$jumlah_karyawan_kerani = $p_kerani['jumlah_karyawan'];
			$persen_premi_kerani = $p_kerani['persen_premi'];
		}
		// $persen_premi_mandor = 1.5;
		// $persen_premi_kerani = 1.25;
		// $jumlah_karyawan_mandor = 10;
		// $jumlah_karyawan_kerani = 10;
		$premi_kerani = 0;
		$premi_mandor = 0;
		$total_premi_dan_denda = $total_premi - $jumlah_nilai_denda;
		if ($jum_karyawan <	$jumlah_karyawan_mandor) {
			$premi_mandor = ($persen_premi_mandor / 100) *	($total_premi_dan_denda / $jumlah_karyawan_mandor);
		} else {
			$premi_mandor = $persen_premi_mandor / 100 *	($total_premi_dan_denda / $jum_karyawan);
		}
		if ($jum_karyawan <	$jumlah_karyawan_kerani) {
			$premi_kerani = ($persen_premi_kerani / 100) *	($total_premi_dan_denda / $jumlah_karyawan_kerani);
		} else {
			$premi_kerani = $persen_premi_kerani / 100 *	($total_premi_dan_denda / $jum_karyawan);
		}

		$data_mandor_kerani = array('rp_hk_mandor' => $upah_mandor, 'rp_hk_kerani' => $upah_kerani, 'premi_mandor' => $premi_mandor, 'premi_kerani' => $premi_kerani);
		if (!empty($data_mandor_kerani)) {
			$this->set_response(array("status" => "OK", "data" => $data_mandor_kerani), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function laporan_panen_detail_post()
	{

		error_reporting(0);
		$estate_id     = $this->post('estate_id', true);
		$tgl_mulai =  $this->post('tgl_mulai', true);
		$tgl_akhir = $this->post('tgl_akhir', true);

		$retrieveEstate = $this->db->query("select * from gbm_organisasi where id=" . $estate_id . "")->row_array();

		$retrievePanen = $this->db->query("select * from est_bkm_umum_vw 
		where id_estate=" . $estate_id . " and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
		 order by tanggal")->result_array();

		// var_dump($results);
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		$html = "<style type='text/css'>
		h3.title {
			margin-bottom: 0px;
			line-height: 30px;
		}
		hr.top {
			border: none;
			border-bottom: 2px solid #333;
			margin-bottom: 10px;
			margin-top: 10px;
		}
		.kop-print {
		  width: 700px;
		  margin: auto;
	  }
  
	  .kop-print img {
		  float: left;
		  height: 60px;
		  margin-right: 20px;
	  }
  
	  .kop-print .kop-info {
		  font-size: 15px;
	  }
  
	  .kop-print .kop-nama {
		  font-size: 25px;
		  font-weight: bold;
		  line-height: 35px;
	  }
  
	  .kop-print-hr {
		  border-width: 2px;
		  border-color: black;
		  margin-bottom: 0px;
	  }
	  table {
		border-collapse: separate;
		border-spacing: 0;
		color: #4a4a4d;
		font: 14px/1.4 'Helvetica Neue', Helvetica, Arial, sans-serif;
	  }
	  th,
	  td {
		padding: 10px 15px;
		vertical-align: middle;
	  }
	  thead {
		background: #395870;
		background: linear-gradient(#49708f, #293f50);
		color: #fff;
		font-size: 11px;
		text-transform: uppercase;
	  }
	  th:first-child {
		border-top-left-radius: 5px;
		text-align: left;
	  }
	  th:last-child {
		border-top-right-radius: 5px;
	  }
	  tbody tr:nth-child(even) {
		background: #f0f0f2;
	  }
	  td {
		border-bottom: 1px solid #cecfd5;
		border-right: 1px solid #cecfd5;
	  }
	  td:first-child {
		border-left: 1px solid #cecfd5;
	  }
	  .book-title {
		color: #395870;
		display: block;
	  }
	  .text-offset {
		color: #7c7c80;
		font-size: 12px;
	  }
	  .item-stock,
	  .item-qty {
		text-align: center;
	  }
	  .item-price {
		text-align: right;
	  }
	  .item-multiple {
		display: block;
	  }
	  tfoot {
		text-align: right;
	  }
	  tfoot tr:last-child {
		background: #f0f0f2;
		color: #395870;
		font-weight: bold;
	  }
	  tfoot tr:last-child td:first-child {
		border-bottom-left-radius: 5px;
	  }
	  tfoot tr:last-child td:last-child {
		border-bottom-right-radius: 5px;
	  }
	  
  
	</style>
  ";

		// 		$html = $html . '<div class="row">
		//   <div class="span12">
		// 	  <br>
		// 	  <div class="kop-print">
		// 		  <img src=data:image/png;base64,' . base64_encode(file_get_contents(get_logo_config())) . ' alt="image" >
		// 		  <div class="kop-nama">' . get_pengaturan('nama-company', 'value') . '</div>
		// 		  <div class="kop-info">Alamat : ' . get_pengaturan('alamat', 'value') . ', Telepon :' . get_pengaturan('telp', 'value') . '</div>
		// 	  </div>
		// 	  <hr class="kop-print-hr">
		//   </div>
		//   </div>
		//   <h2>Laporan Lembur</h2>
		//   <h3>estate:' . $retrieveEstate[0]['nama'] . ' </h3>
		//   <h4>' . $tgl_mulai . ' - ' . $tgl_akhir . ' </h4>';
		$html = $html . '
<h2>Laporan Rincian Panen</h2>
<h3>estate:' . $retrieveEstate['nama'] . ' </h3>
<h4>' . $tgl_mulai . ' - ' . $tgl_akhir . ' </h4>';

		$html = $html . ' <table  >
			<thead>
				<tr>
				<th width="4%">No.</th>			
				<th>Tanggal</th>
				<th>Afdeling</th>
				<th>Blok</th>
				<th>Tahun Tanam</th>
				<th>Inti/Plasma</th>
				<th style="text-align: right;">Janjang </th>
				<th style="text-align: right;">Hasil Kerja(Kg) </th>
				<th style="text-align: right;">Upah(Rp) </th>
				<th style="text-align: right;">Premi Basis </th>
				<th style="text-align: right;">Premi(Rp) </th>
				<th style="text-align: right;">Jumlah Hk</th>
				<th style="text-align: right;">Denda </th>
				<th style="text-align: right;">Total Biaya(Rp) </th>
				
				</tr>
			</thead>
			<tbody>';


		$no = 0;
		$janjang = 0;
		$hasil_kerja_kg = 0;
		$upah = 0;
		$premi_basis = 0;
		$premi_rp = 0;
		$jumlah_hk = 0;
		$denda = 0;
		$total = 0;

		foreach ($retrievePanen as $key => $m) {
			$no++;
			$janjang = $janjang + $m['hasil_kerja_jjg'];
			$hasil_kerja_kg = $hasil_kerja_kg + $m['hasil_kerja_kg'];
			$upah = $upah + $m['rp_hk'];
			$premi_basis = $premi_basis + $m['premi_basis'];
			$jumlah_hk = $jumlah_hk + $m['jumlah_hk'];
			$premi_rp = $premi_rp + $m['premi_panen'];
			$denda = $denda + $m['denda_panen'];
			$jumlah_rp = ($m['rp_hk'] + $m['premi_panen']);
			$total = $total + $jumlah_rp;

			$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative;">	' . ($no) . '</td>
						<td>' . $m['tanggal'] . ' </td>
						<td>
						' . $m['nama_afdeling'] . ' 
						
						</td>
						<td>
						' . $m['nama_blok'] . ' 
						
						</td>
						<td>
							' . $m['tahuntanam'] . ' 
						</td>
						<td>
							' . $m['intiplasma'] . ' 
						</td>
						
						<td style="text-align: right;">' . number_format($m['hasil_kerja_jjg']) . ' 
						<td style="text-align: right;">' . number_format($m['hasil_kerja_kg']) . ' 
						<td style="text-align: right;">' . number_format($m['rp_hk']) . ' 
						<td style="text-align: right;">' . number_format($m['premi_basis']) . ' 
						<td style="text-align: right;">' . number_format($m['premi_panen']) . '
						<td style="text-align: right;">' . number_format($m['jumlah_hk']) . '  
						<td style="text-align: right;">' . number_format($m['denda_panen']) . ' 
						<td style="text-align: right;">' . number_format($jumlah_rp) . ' 
							
						</td>';

			$html = $html . '</tr>';
		}

		$html = $html . ' 	
						<tr class=":: arc-content">
						<td style="position:relative;">
							&nbsp;

						</td>
						<td>
							&nbsp;
						</td>
						<td>
							&nbsp;
						</td>
						<td>
							&nbsp;
						</td>
						<td>
							&nbsp;
						</td>
						<td>
							&nbsp;
						</td>
					
					
						<td style="text-align: right;">
						' . number_format($janjang) . ' 
						</td>
						<td style="text-align: right;">
						' . number_format($hasil_kerja_kg) . ' 
						</td>
						<td style="text-align: right;">
						' . number_format($upah) . ' 
						</td>
						<td style="text-align: right;">
						' . number_format($premi_basis) . ' 
						</td>
						<td style="text-align: right;">
						' . number_format($premi_rp) . ' 
						</td>
						<td style="text-align: right;">
						' . number_format($jumlah_hk) . ' 
						</td>
						<td style="text-align: right;">
						' . number_format($denda) . ' 
						</td>
						<td style="text-align: right;">
						' . number_format($total) . ' 
						</td>

						
						</tr>
								</tbody>
							</table>
						';
		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		echo $html;
	}
	function laporan_panen_perkaryawan_post()
	{

		error_reporting(0);
		$estate_id     = $this->post('estate_id', true);
		$tgl_mulai =  $this->post('tgl_mulai', true);
		$tgl_akhir = $this->post('tgl_akhir', true);

		$retrieveEstate = $this->db->query("select * from gbm_organisasi where id=" . $estate_id . "")->row_array();

		$retrievePanen = $this->db->query("select * from est_bkm_umum_vw 
		where id_estate=" . $estate_id . " and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
		 order by tanggal")->result_array();

		// var_dump($results);
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		$html = "<style type='text/css'>
		h3.title {
			margin-bottom: 0px;
			line-height: 30px;
		}
		hr.top {
			border: none;
			border-bottom: 2px solid #333;
			margin-bottom: 10px;
			margin-top: 10px;
		}
		.kop-print {
		  width: 700px;
		  margin: auto;
	  }
  
	  .kop-print img {
		  float: left;
		  height: 60px;
		  margin-right: 20px;
	  }
  
	  .kop-print .kop-info {
		  font-size: 15px;
	  }
  
	  .kop-print .kop-nama {
		  font-size: 25px;
		  font-weight: bold;
		  line-height: 35px;
	  }
  
	  .kop-print-hr {
		  border-width: 2px;
		  border-color: black;
		  margin-bottom: 0px;
	  }
	  table {
		border-collapse: separate;
		border-spacing: 0;
		color: #4a4a4d;
		font: 14px/1.4 'Helvetica Neue', Helvetica, Arial, sans-serif;
	  }
	  th,
	  td {
		padding: 10px 15px;
		vertical-align: middle;
	  }
	  thead {
		background: #395870;
		background: linear-gradient(#49708f, #293f50);
		color: #fff;
		font-size: 11px;
		text-transform: uppercase;
	  }
	  th:first-child {
		border-top-left-radius: 5px;
		text-align: left;
	  }
	  th:last-child {
		border-top-right-radius: 5px;
	  }
	  tbody tr:nth-child(even) {
		background: #f0f0f2;
	  }
	  td {
		border-bottom: 1px solid #cecfd5;
		border-right: 1px solid #cecfd5;
	  }
	  td:first-child {
		border-left: 1px solid #cecfd5;
	  }
	  .book-title {
		color: #395870;
		display: block;
	  }
	  .text-offset {
		color: #7c7c80;
		font-size: 12px;
	  }
	  .item-stock,
	  .item-qty {
		text-align: center;
	  }
	  .item-price {
		text-align: right;
	  }
	  .item-multiple {
		display: block;
	  }
	  tfoot {
		text-align: right;
	  }
	  tfoot tr:last-child {
		background: #f0f0f2;
		color: #395870;
		font-weight: bold;
	  }
	  tfoot tr:last-child td:first-child {
		border-bottom-left-radius: 5px;
	  }
	  tfoot tr:last-child td:last-child {
		border-bottom-right-radius: 5px;
	  }
	  
  
	</style>
  ";

		// 		$html = $html . '<div class="row">
		//   <div class="span12">
		// 	  <br>
		// 	  <div class="kop-print">
		// 		  <img src=data:image/png;base64,' . base64_encode(file_get_contents(get_logo_config())) . ' alt="image" >
		// 		  <div class="kop-nama">' . get_pengaturan('nama-company', 'value') . '</div>
		// 		  <div class="kop-info">Alamat : ' . get_pengaturan('alamat', 'value') . ', Telepon :' . get_pengaturan('telp', 'value') . '</div>
		// 	  </div>
		// 	  <hr class="kop-print-hr">
		//   </div>
		//   </div>
		//   <h2>Laporan Lembur</h2>
		//   <h3>estate:' . $retrieveEstate[0]['nama'] . ' </h3>
		//   <h4>' . $tgl_mulai . ' - ' . $tgl_akhir . ' </h4>';
		$html = $html . '
<h2>Laporan Rincian Panen</h2>
<h3>Estate:' . $retrieveEstate['nama'] . ' </h3>
<h4>' . $tgl_mulai . ' - ' . $tgl_akhir . ' </h4>';
		$html = $html . ' <table  >
			<thead>
				<tr>
				<th width="4%">No.</th>			
				<th>No Transaksi</th>
				<th>Tanggal</th>
				<th>Afdeling</th>
				<th>Blok</th>
				<th>Pemanen</th>
				<th>Mandor</th>
				<th style="text-align: right;">Janjang </th>
				<th style="text-align: right;">Hasil Kerja(Kg) </th>
				<th style="text-align: right;">Upah(Rp) </th>
				<th style="text-align: right;">Premi Basis </th>
				<th style="text-align: right;">Premi(Rp) </th>
				<th style="text-align: right;">Jumlah Hk</th>
				<th style="text-align: right;">Denda </th>
				<th style="text-align: right;">Total Biaya(Rp) </th>
				
				</tr>
			</thead>
			<tbody>';


		$no = 0;
		$janjang = 0;
		$hasil_kerja_kg = 0;
		$upah = 0;
		$premi_basis = 0;
		$premi_rp = 0;
		$jumlah_hk = 0;
		$denda = 0;
		$total = 0;

		foreach ($retrievePanen as $key => $m) {
			$no++;
			$janjang = $janjang + $m['hasil_kerja_jjg'];
			$hasil_kerja_kg = $hasil_kerja_kg + $m['hasil_kerja_kg'];
			$upah = $upah + $m['rp_hk'];
			$premi_basis = $premi_basis + $m['premi_basis'];
			$jumlah_hk = $jumlah_hk + $m['jumlah_hk'];
			$premi_rp = $premi_rp + $m['premi_panen'];
			$denda = $denda + $m['denda_panen'];
			$jumlah_rp = ($m['rp_hk'] + $m['premi_panen']);
			$total = $total + $jumlah_rp;

			$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative;">	' . ($no) . '</td>
						<td>' . $m['no_transaksi'] . ' </td>
						<td>' . $m['tanggal'] . ' </td>
						<td>
						' . $m['nama_afdeling'] . ' 
						
						</td>
						<td>
						' . $m['nama_blok'] . ' 
						
						</td>
						<td>
							' . $m['nama_karyawan'] . '(' . $m['nip_karyawan'] . ') 
						</td>
						<td>
						' . $m['nama_mandor'] . '(' . $m['nip_mandor'] . ') 
						</td>
						
						<td style="text-align: right;">' . number_format($m['hasil_kerja_jjg']) . ' 
						<td style="text-align: right;">' . number_format($m['hasil_kerja_kg']) . ' 
						<td style="text-align: right;">' . number_format($m['rp_hk']) . ' 
						<td style="text-align: right;">' . number_format($m['premi_basis']) . ' 
						<td style="text-align: right;">' . number_format($m['premi_panen']) . '
						<td style="text-align: right;">' . number_format($m['jumlah_hk']) . '  
						<td style="text-align: right;">' . number_format($m['denda_panen']) . ' 
						<td style="text-align: right;">' . number_format($jumlah_rp) . ' 
							
						</td>';

			$html = $html . '</tr>';
		}

		$html = $html . ' 	
						<tr class=":: arc-content">
						<td style="position:relative;">
							&nbsp;

						</td>
						<td>
							&nbsp;
						</td>
						
						<td>
							&nbsp;
						</td>
						<td>
							&nbsp;
						</td>
						<td>
							&nbsp;
						</td>
						<td>
							&nbsp;
						</td>
						<td>
						&nbsp;
						</td>	
						<td style="text-align: right;">
						' . number_format($janjang) . ' 
						</td>
						<td style="text-align: right;">
						' . number_format($hasil_kerja_kg) . ' 
						</td>
						<td style="text-align: right;">
						' . number_format($upah) . ' 
						</td>
						<td style="text-align: right;">
						' . number_format($premi_basis) . ' 
						</td>
						<td style="text-align: right;">
						' . number_format($premi_rp) . ' 
						</td>
						<td style="text-align: right;">
						' . number_format($jumlah_hk) . ' 
						</td>
						<td style="text-align: right;">
						' . number_format($denda) . ' 
						</td>
						<td style="text-align: right;">
						' . number_format($total) . ' 
						</td>

						
						</tr>
								</tbody>
							</table>
						';
		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		echo $html;
	}
	function laporan_panen_perbulan_post()
	{
		error_reporting(0);

		$estate_id     = $this->post('estate_id', true);
		$periode =  $this->post('periode', true);


		$date = new DateTime($periode . '-01');
		$date->modify('last day of this month');
		$last_day_this_month = $date->format('Y-m-d');
		(int)$jumhari = date('d', strtotime($last_day_this_month));
		$tgl_mulai = $periode . '-01';
		$tgl_akhir = $periode . '-' . sprintf("%02d", $jumhari);

		$retrieveEstate = $this->db->query("select * from gbm_organisasi where id=" . $estate_id . "")->row_array();

		// var_dump($results);
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		$html = "<style type='text/css'>
		h3.title {
			margin-bottom: 0px;
			line-height: 30px;
		}
		hr.top {
			border: none;
			border-bottom: 2px solid #333;
			margin-bottom: 10px;
			margin-top: 10px;
		}
		.kop-print {
		  width: 700px;
		  margin: auto;
	  }
  
	  .kop-print img {
		  float: left;
		  height: 60px;
		  margin-right: 20px;
	  }
  
	  .kop-print .kop-info {
		  font-size: 15px;
	  }
  
	  .kop-print .kop-nama {
		  font-size: 25px;
		  font-weight: bold;
		  line-height: 35px;
	  }
  
	  .kop-print-hr {
		  border-width: 2px;
		  border-color: black;
		  margin-bottom: 0px;
	  }
	  table {
		border-collapse: separate;
		border-spacing: 0;
		color: #4a4a4d;
		font: 14px/1.4 'Helvetica Neue', Helvetica, Arial, sans-serif;
	  }
	  th,
	  td {
		padding: 10px 15px;
		vertical-align: middle;
	  }
	  thead {
		background: #395870;
		background: linear-gradient(#49708f, #293f50);
		color: #fff;
		font-size: 11px;
		text-transform: uppercase;
	  }
	  th:first-child {
		border-top-left-radius: 5px;
		text-align: left;
	  }
	  th:last-child {
		border-top-right-radius: 5px;
	  }
	  tbody tr:nth-child(even) {
		background: #f0f0f2;
	  }
	  td {
		border-bottom: 1px solid #cecfd5;
		border-right: 1px solid #cecfd5;
	  }
	  td:first-child {
		border-left: 1px solid #cecfd5;
	  }
	  .book-title {
		color: #395870;
		display: block;
	  }
	  .text-offset {
		color: #7c7c80;
		font-size: 12px;
	  }
	  .item-stock,
	  .item-qty {
		text-align: center;
	  }
	  .item-price {
		text-align: right;
	  }
	  .item-multiple {
		display: block;
	  }
	  tfoot {
		text-align: right;
	  }
	  tfoot tr:last-child {
		background: #f0f0f2;
		color: #395870;
		font-weight: bold;
	  }
	  tfoot tr:last-child td:first-child {
		border-bottom-left-radius: 5px;
	  }
	  tfoot tr:last-child td:last-child {
		border-bottom-right-radius: 5px;
	  }
	  
  
	</style>
  ";

		// 		$html = $html . '<div class="row">
		//   <div class="span12">
		// 	  <br>
		// 	  <div class="kop-print">
		// 		  <img src=data:image/png;base64,' . base64_encode(file_get_contents(get_logo_config())) . ' alt="image" >
		// 		  <div class="kop-nama">' . get_pengaturan('nama-company', 'value') . '</div>
		// 		  <div class="kop-info">Alamat : ' . get_pengaturan('alamat', 'value') . ', Telepon :' . get_pengaturan('telp', 'value') . '</div>
		// 	  </div>
		// 	  <hr class="kop-print-hr">
		//   </div>
		//   </div>

		$html = $html . '<div class="row">
<div class="span12">
	<br>

</div>
</div>
<h2>Laporan Panen Per Bulan</h2>
<h3>estate:' . $retrieveEstate['nama'] . ' </h3>
<h4>' . $tgl_mulai . ' - ' . $tgl_akhir . ' </h4>';

		$html = $html . "

<table   border='1' width='100%' style='border-collapse: collapse;'>
<thead>
<tr>
	<td rowspan=3 >No</td>
	<td rowspan=3>Afdeling</td>
	<td rowspan=3>Blok</td>
	<td rowspan=3>Inti/Plasma</td>
	<td rowspan=3>Luas</td>
	<td colspan=" . ($jumhari * 4) . "  style='text-align: center'> " . $periode . "  </td>
	<td colspan=4 rowspan=2  style='text-align: center'>TOTAL</td>
</tr>
";

		$html = $html . "<tr>";
		for ($i = 1; $i < ($jumhari + 1); $i++) {
			$html = $html . "<td style='text-align: center' colspan=4>" . $i . "</td>";
		}

		$html = $html . "</tr> ";
		$html = $html . "<tr>";
		for ($i = 1; $i < ($jumhari + 1); $i++) {
			$html = $html . "<td style='text-align: center'>Jjg</td>";
			$html = $html . "<td style='text-align: center'>Kg</td>";
			$html = $html . "<td style='text-align: center'>Luas</td>";
			$html = $html . "<td style='text-align: center'>Hk</td>";
		}
		$html = $html . "<td style='text-align: center'>Jjg</td>";
		$html = $html . "<td style='text-align: center'>Kg</td>";
		$html = $html . "<td style='text-align: center'>Luas</td>";
		$html = $html . "<td style='text-align: center'>Hk</td>";
		$html = $html . "</tr> </thead>";
		$nourut = 0;
		$grandtotal = 0;
		$totalPerHari = array();
		$totalPerHari = [];
		// retrive data rayon  
		$qry = "SELECT DISTINCT blok_id,kode_blok,nama_blok,nama_afdeling,intiplasma,luasareaproduktif FROM est_bkm_umum_vw WHERE id_estate=" . $estate_id . " and tanggal>='" . $tgl_mulai . "' 
		and tanggal<='" . $tgl_akhir . "' ";
		$retrieveBlok = $this->db->query($qry)->result_array();

		for ($i = 1; $i < ($jumhari + 1); $i++) {
			$totalPerHari[] = 0;
		}

		foreach ($retrieveBlok as $key => $d) {
			$html = $html . "<tr>";
			$totalkg = 0;
			$nourut = $nourut + 1;
			$html = $html . "<td style='text-align: center'>" . $nourut . "</td>";
			$html = $html . "<td style='text-align: center'>" . $d['nama_afdeling'] . "</td>";
			$html = $html . "<td style='text-align: center'>" . $d['nama_blok'] . "</td>";
			$html = $html . "<td style='text-align: center'>" . $d['intiplasma'] . "</td>";
			$html = $html . "<td style='text-align: center'>" . $d['luasareaproduktif'] . "</td>";
			$total_hasil_kerja_jjg = 0;
			$total_hasil_kerja_kg = 0;
			$total_hasil_kerja_luas = 0;
			$total_jumlah_hk = 0;
			for ($i = 1; $i < ($jumhari + 1); $i++) {
				$tgl = $periode  . '-' . sprintf("%02d", $i);
				$retrievePanen = $this->db->query("SELECT SUM(hasil_kerja_jjg)as hasil_kerja_jjg,
				SUM(hasil_kerja_kg)as hasil_kerja_kg,SUM(hasil_kerja_luas)as hasil_kerja_luas,
				SUM(jumlah_hk)as jumlah_hk
				 FROM est_bkm_umum_vw WHERE (blok_id =" . $d['blok_id'] . ")
				and id_estate=" . $estate_id . " and tanggal='" . $tgl . "'")->row_array();
				$hasil_kerja_jjg = $retrievePanen['hasil_kerja_jjg'] ? $retrievePanen['hasil_kerja_jjg'] : 0;
				$hasil_kerja_kg = $retrievePanen['hasil_kerja_kg'] ? $retrievePanen['hasil_kerja_kg'] : 0;
				$hasil_kerja_luas = $retrievePanen['hasil_kerja_luas'] ? $retrievePanen['hasil_kerja_luas'] : 0;
				$jumlah_hk = $retrievePanen['jumlah_hk'] ? $retrievePanen['jumlah_hk'] : 0;
				$index = "idx" . $i;
				// $jum=0;
				if (array_key_exists($i, $totalPerHari)) {
					$totalPerHari[$i - 1] = $totalPerHari[$i - 1] + $hasil_kerja_jjg;
				} else {
					$totalPerHari[] = $hasil_kerja_jjg;
				}

				$total_hasil_kerja_jjg = $total_hasil_kerja_jjg + $hasil_kerja_jjg;
				$total_hasil_kerja_kg = $total_hasil_kerja_kg + $hasil_kerja_kg;
				$total_hasil_kerja_luas = $total_hasil_kerja_luas + $hasil_kerja_luas;
				$total_jumlah_hk = $total_jumlah_hk + $jumlah_hk;
				$grandtotal = $grandtotal + $hasil_kerja_jjg;
				$html = $html . "<td style='text-align: center'>" . $hasil_kerja_jjg . " </td>";
				$html = $html . "<td style='text-align: center'>" . $hasil_kerja_kg . " </td>";
				$html = $html . "<td style='text-align: center'>" . $hasil_kerja_luas . " </td>";
				$html = $html . "<td style='text-align: center'>" . $jumlah_hk . " </td>";
			}
			$html = $html . "<td style='text-align: center'>" . $total_hasil_kerja_jjg . " </td>";
			$html = $html . "<td style='text-align: center'>" . $total_hasil_kerja_kg . " </td>";
			$html = $html . "<td style='text-align: center'>" . $total_hasil_kerja_luas . " </td>";
			$html = $html . "<td style='text-align: center'>" . $total_jumlah_hk . " </td>";
			$html = $html . "</tr>";
		}



		// // retrive data SUpplier  
		// $qry = "SELECT DISTINCT supplier_id,nama_supplier FROM pks_timbangan_terima_tbs_vw WHERE (supplier_id IS NOT NULL AND supplier_id <>0)
		// and estate_id=" . $estate_id . " and tanggal>='" . $tgl_mulai . "' 
		// and tanggal<='" . $tgl_akhir . "'  " . $where;
		// $retrieveSupplier = $this->db->query($qry)->result_array();

		// foreach ($retrieveSupplier as $key => $s) {
		// 	$html = $html . "<tr>";
		// 	$totalkg = 0;
		// 	$nourut = $nourut + 1;
		// 	$html = $html . "<td style='text-align: center'>" . $nourut . "</td>";
		// 	$html = $html . "<td style='text-align: center'>" . $s['nama_supplier'] . "</td>";
		// 	for ($i = 1; $i < ($jumhari + 1); $i++) {

		// 		$tgl = $periode  . '-' . sprintf("%02d", $i);
		// 		$retrieveKgSupp = $this->db->query("SELECT SUM(berat_bersih)as beratkg FROM pks_timbangan_terima_tbs_vw WHERE (supplier_id =" . $s['supplier_id'] . ")
		// 		and estate_id=" . $estate_id . " and tanggal='" . $tgl . "'")->row_array();
		// 		$beratkg = $retrieveKgSupp['beratkg'] ? $retrieveKgSupp['beratkg'] : 0;
		// 		$totalPerHari[$i - 1] = ($totalPerHari[$i - 1] ? $totalPerHari[$i - 1] : 0) + $beratkg;
		// 		$totalkg = $totalkg + $beratkg;
		// 		$grandtotal = $grandtotal + $beratkg;
		// 		$html = $html . "<td style='text-align: center'>" . number_format($beratkg) . " </td>";
		// 	}
		// 	$html = $html . "<td style='text-align: center'>" . number_format($totalkg) . " </td>";
		// 	$html = $html . "</tr>";
		// }


		// $html = $html . "<tr>";
		// $html = $html . "<td style='text-align: center'> </td>";
		// $html = $html . "<td style='text-align: center'></td>";
		// for ($i = 1; $i < ($jumhari + 1); $i++) {

		// 	$html = $html . "<td style='text-align: center'>" . number_format($totalPerHari[$i - 1]) . " </td>";
		// }
		// $html = $html . "<td style='text-align: right'>" . number_format($grandtotal) . " </td>";
		// $html = $html . "</tr>";
		$html = $html . "</table>";



		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
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
	function laporan_umum_post()
	{
		$format_laporan =  $this->post('format_laporan', true);

		// $id = (int)$segment_3;
		$data = [];

		$input = [
			'lokasi_id' => 252,
			'afdeling_id' => 743,
			'tgl_mulai' => '2020-01-01',
			'tgl_akhir' => '2022-08-08',
			'format_laporan' => 'view',
		];

		$lokasi_id = $this->post('lokasi_id', true);
		$afdeling_id = $this->post('afdeling_id', true);
		$tanggal_awal = $this->post('tgl_mulai', true);
		$tanggal_akhir = $this->post('tgl_akhir', true);

		// $lokasi_id = $input['lokasi_id'];
		// $afdeling_id = $input['afdeling_id'];
		// $tanggal_awal = $input['tgl_mulai'];
		// $tanggal_akhir = $input['tgl_akhir'];
		// $format_laporan = $input['format_laporan'];

		$queryPo = "SELECT a.*,
		b.no_transaksi AS no_transaksi,
		b.tanggal AS tanggal,
		c.nama AS lokasi,
		d.nama AS afdeling,
		e.nama AS kegiatan,
		f.nama AS nama_karyawan, 
		g.kode AS kode_absen,
		g.keterangan AS ket_absen,
		h.kode as blok, i.kode as kode_kendaraan,
		i.nama as nama_kendaraan
		 FROM est_bkm_umum_dt a
		LEFT JOIN est_bkm_umum_ht b ON a.bkm_umum_id=b.id
		LEFT JOIN gbm_organisasi c ON b.lokasi_id=c.id
		LEFT JOIN gbm_organisasi d ON b.rayon_afdeling_id=d.id
		LEFT JOIN acc_kegiatan e ON a.kegiatan_id=e.id
		LEFT JOIN karyawan f ON a.karyawan_id=f.id
		LEFT JOIN hrms_jenis_absensi g ON a.jenis_absensi_id=g.id
		LEFT JOIN gbm_organisasi h on a.blok_id=h.id
		LEFT JOIN trk_kendaraan i on a.kendaraan_id=i.id
		where b.tanggal between  '" . $tanggal_awal . "' and  '" . $tanggal_akhir . "'	
		";

		$filter_lokasi = "Semua";
		if ($lokasi_id) {
			$queryPo = $queryPo . " and b.lokasi_id=" . $lokasi_id . "";
			$res = $this->db->query("select * from gbm_organisasi where id=" . $lokasi_id . "")->row_array();
			$filter_lokasi = $res['nama'];
		}


		$filter_afdeling = "Semua";
		if ($afdeling_id) {
			$queryPo = $queryPo . " and b.rayon_afdeling_id=" . $afdeling_id . "";
			$res = $this->db->query("select * from gbm_organisasi where id=" . $afdeling_id . "")->row_array();
			$filter_afdeling = $res['nama'];
		}

		$dataPo = $this->db->query($queryPo)->result_array();

		$data['po'] = 	$dataPo;

		$data['filter_lokasi'] = 	$filter_lokasi;
		$data['filter_afdeling'] = 	$filter_afdeling;
		$data['filter_tgl_awal'] = 	$tanggal_awal;
		$data['filter_tgl_akhir'] = $tanggal_akhir;
		$data['format_laporan'] = $format_laporan;

		$html = $this->load->view('Est_Bkm_Umum_Laporan', $data, true);

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
	function print_slip_get($segment_3 = '')
	{

		$id = (int)$segment_3;
		$data = [];

		$queryHeader = "SELECT a.*,
		b.nama as lokasi,
		c.nama as rayon
		FROM est_bkm_umum_ht a
		LEFT JOIN gbm_organisasi b ON a.lokasi_id=b.id
		LEFT JOIN gbm_organisasi c ON a.rayon_afdeling_id=c.id
		WHERE a.id=" . $id . "";
		$dataHeader = $this->db->query($queryHeader)->row_array();

		$queryDetail = "SELECT a.*,
		b.nama as karyawan,
		a.id as id,
		c.nama AS kegiatan,
		d.keterangan AS absensi,
		d.kode AS kode,
		e.nama as nama_blok,
		e.kode as kode_blok,
		f.nama AS nama_kendaraan,
		f.kode AS kode_kendaraan
		FROM est_bkm_umum_dt a 
		LEFT JOIN karyawan b ON a.karyawan_id=b.id
		LEFT JOIN acc_kegiatan c ON a.kegiatan_id=c.id
		LEFT JOIN hrms_jenis_absensi d ON a.jenis_absensi_id=d.id
		LEFT JOIN gbm_organisasi e ON a.blok_id=e.id
		LEFT JOIN trk_kendaraan f ON a.kendaraan_id=f.id
		WHERE a.bkm_umum_id=" . $id . "";
		$dataDetail = $this->db->query($queryDetail)->result_array();

		$data['header'] = 	$dataHeader;
		$data['detail'] = 	$dataDetail;

		$html = $this->load->view('EstSlipBkmUmum', $data, true);

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		echo $html;
	}
	function format_number_report($angka)
	{
		$format_laporan     = $this->post('format_laporan', true);
		// if ($fmt_laporan ) {
		// 	$format_laporan     = $this->post('format_laporan', true);
		// }else{
		// 	return number_format($angka);
		// }
		if ($format_laporan == 'xls') {
			return $angka;
		} else {
			if ($angka == 0) {
				return '';
			}
			return number_format($angka);
		}
	}
}
