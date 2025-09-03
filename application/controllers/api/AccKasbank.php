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

class AccKasbank extends BD_Controller //Rest_Controller
{
	public $user_id;
	public $theCredential;
	public $data = array();
	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('GbmOrganisasiModel');
		$this->load->model('AccKasbankModel');
		$this->load->model('AccJurnalModel');
		$this->load->model('M_DatatablesModel');
		$this->load->library('pdfgenerator');
		$this->load->helper(array('url', 'antech_helper', 'form', 'text',  'security', 'file', 'number', 'date', 'download'));
		$this->load->library('image_lib');
		$this->load->library('upload');
		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id = $this->user_data->id;
	}
	public function list_post()
	{
		$post = $this->post();
		$param = $post['parameter'];

		// $query  = " SELECT a.*,b.nama as lokasi,c.no_transaksi as no_permintaan  FROM `acc_kasbank_ht` a 
		// inner join gbm_organisasi b on a.lokasi_id=b.id
		// left join acc_permintaan_dana c on a.permintaan_id=c.id
		// ";
		// $search = array('a.no_transaksi', 'a.no_referensi','c.no_transaksi', 'a.tanggal', 'b.nama', 'a.keterangan');
		$query  = " SELECT * from  acc_kasbank_hd_vw
		";
		$search = array('no_transaksi', 'no_referensi', 'no_permintaan', 'tanggal', 'lokasi', 'keterangan', 'nama_kasbank');

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
		if ($param['akun_id']) {
			$isWhere = $isWhere . " and  akun_kasbank_id =" . $param['akun_id'] . "";
		} else {
			$isWhere = $isWhere . " and  akun_kasbank_id in
			(select acc_akun_id from fwk_users_kasbank where user_id=" . $this->user_id . ")";
		}
		if (!empty($param['status_id'])) {
			if ($param['status_id'] == 'N') {
				$isWhere = $isWhere .  "  and is_posting=0";
			} else {
				$isWhere = $isWhere .  "  and is_posting=1";
			}
		}
		if (!empty($param['tipe_id'])) {
			if ($param['tipe_id'] == 'in') {
				$isWhere = $isWhere .  "  and tipe_jurnal='in'";
			} else {
				$isWhere = $isWhere .  "  and tipe_jurnal='out'";
			}
		}
		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		for ($i = 0; $i < count($data['data']); $i++) {
			$d = $data['data'][$i];
			$dt = $this->db->query("select a.*, b.kode as kode_akun,b.nama as nama_akun from acc_kasbank_dt a left join
			acc_akun b on a.acc_akun_id=b.id where a.jurnal_id=" . $d['id'] . " order by a.id")->result_array();
			$data['data'][$i]['dt'] = $dt;
		}
		$this->set_response($data, REST_Controller::HTTP_OK);
	}

	function index_get($id = '')
	{
		$retrieve = array();
		$retrieve = $this->AccKasbankModel->retrieve_by_id($id);
		$retrieve_detail = $this->AccKasbankModel->retrieve_detail($id);


		$retrieve['detail'] = $retrieve_detail;
		if (!empty($retrieve)) {
			$retrieve['file_info']         = get_file_info($this->get_path_file($retrieve['upload_file']));
			$retrieve['file_info']['mime'] = get_mime_by_extension($this->get_path_file($retrieve['upload_file']));

			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
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

	function index_post()
	{
		$input = $this->post();
		$input['dibuat_oleh'] = $this->user_id;

		/* START VALIDASI BLOK TIDAK DIISI JIKA AKUN->Kelompok Biaya=PNN,PMK,PML*/
		$details = $input['details'];
		foreach ($details as $key => $value) {
			$blok_id = $value['blok_id']['id'];
			$kegiatan_id = $value['kegiatan_id']['id'];
			$traksi_id =  $value['traksi_id']['id'];
			$acc_akun_id = $value['acc_akun_id']['id'];
			if (is_null($blok_id) || empty($blok_id)) {
				$res_akun = $this->db->query("	SELECT b.kode AS kode_akun,b.nama AS nama_akun FROM  acc_akun b WHERE b.kelompok_biaya 
						IN('PNN','PMK','PML') 
						AND b.id=" . $acc_akun_id . "")->row_array();

				if ($res_akun) {
					$msg = "Akun:" . $res_akun['nama_akun'] . " harus diiisi Blok";
					$this->set_response(array("status" => "NOT OK", "data" => $msg), REST_Controller::HTTP_OK);
					return;
				}
			}
			if (is_null($traksi_id) || empty($traksi_id)) {
				$res_akun = $this->db->query("	SELECT b.kode AS kode_akun,b.nama AS nama_akun FROM  acc_akun b WHERE b.kelompok_biaya 
						IN('TRK') 
						AND b.id=" . $acc_akun_id . "")->row_array();

				if ($res_akun) {
					$msg = "Akun:" . $res_akun['nama_akun'] . " harus diiisi Kendaraan/Alat Berat/Mesin";
					$this->set_response(array("status" => "NOT OK", "data" => $msg), REST_Controller::HTTP_OK);
					return;
				}
			}
		}
		/* END VALIDASI BLOK TIDAK DIISI */


		$this->load->library('Autonumber');
		$input['no_transaksi'] = $this->autonumber->acc_kasbank($input['lokasi_id']['id'], $input['tanggal']);

		$res = $this->AccKasbankModel->create($input);
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
		$data['dibuat_oleh'] = $this->user_id;

		/* START VALIDASI BLOK TIDAK DIISI JIKA AKUN->Kelompok Biaya=PNN,PMK,PML*/
		$details = $data['details'];
		foreach ($details as $key => $value) {
			$blok_id = $value['blok_id']['id'];
			$kegiatan_id = $value['kegiatan_id']['id'];
			$traksi_id =  $value['traksi_id']['id'];
			$acc_akun_id = $value['acc_akun_id']['id'];
			if (is_null($blok_id) || empty($blok_id)) {
				$res_akun = $this->db->query("	SELECT b.kode AS kode_akun,b.nama AS nama_akun FROM  acc_akun b WHERE b.kelompok_biaya 
					IN('PNN','PMK','PML') 
					AND b.id=" . $acc_akun_id . "")->row_array();

				if ($res_akun) {
					$msg = "Akun:" . $res_akun['nama_akun'] . " harus diiisi Blok";
					$this->set_response(array("status" => "NOT OK", "data" => $msg), REST_Controller::HTTP_OK);
					return;
				}
			}
			if (is_null($traksi_id) || empty($traksi_id)) {
				$res_akun = $this->db->query("	SELECT b.kode AS kode_akun,b.nama AS nama_akun FROM  acc_akun b WHERE b.kelompok_biaya 
					IN('TRK') 
					AND b.id=" . $acc_akun_id . "")->row_array();

				if ($res_akun) {
					$msg = "Akun:" . $res_akun['nama_akun'] . " harus diiisi Kendaraan/Alat Berat/Mesin";
					$this->set_response(array("status" => "NOT OK", "data" => $msg), REST_Controller::HTTP_OK);
					return;
				}
			}
		}
		/* END VALIDASI BLOK TIDAK DIISI */

		$res = $this->AccKasbankModel->update($id, $data);
		if (!empty($res)) {
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	public function index_delete($id)
	{

		$res = $this->AccKasbankModel->delete($id);
		if (!empty($res)) {
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function upload_post($segment_3 = '')
	{
		$id = (int)$segment_3;

		$kasbank = $this->AccKasbankModel->retrieve_by_id($id);

		// $this->set_response(['status' => 'OK', 'debug'=>$this->post()], REST_Controller::HTTP_CREATED);

		if (empty($kasbank)) {
			$this->set_response([
				'status' => false,
				'message' => 'Data Tidak ditemukan',
			], REST_Controller::HTTP_NOT_FOUND);
			return;
		}

		$config['upload_path']   = $_SERVER['DOCUMENT_ROOT'] . "/" . "plantation" . "/userfiles/files";
		$config['allowed_types'] = 'doc|zip|rar|txt|docx|xls|xlsx|pdf|tar|gz|jpg|jpeg|JPG|JPEG|png|ppt|pptx';
		$config['max_size']      = '0';
		$config['max_width']     = '0';
		$config['max_height']    = '0';
		$config['file_name']     = url_title('Bon_' . $kasbank['no_transaksi'] . '_' . time(), '_', TRUE);
		$this->upload->initialize($config);
		$error_upload = array();

		if ($this->upload->do_upload()) {
			$upload_data = $this->upload->data();
			$error_upload = $this->upload->display_errors();
		} else {
			$upload_data['file_name'] = $kasbank['upload_file'];
			$error_upload = $this->upload->display_errors();
		}
		$file = $upload_data['file_name'];
		$input = $this->post();
		$kasbank_update = $this->AccKasbankModel->save_upload($kasbank, $file);

		if ($kasbank_update) {
			$message = [
				'status' => "OK",
				'id' => $kasbank['id'],
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
		$kasbank = $this->AccKasbankModel->retrieve_by_id($id);
		if (!empty($kasbank['upload_file'])) {
			$target_file = $this->get_path_file($kasbank['upload_file']);
			if (!is_file($target_file)) {
				show_error("Maaf file tidak ditemukan." . $target_file);
			}

			$data_file = file_get_contents($target_file); // Read the file's contents
			$name_file = $kasbank['upload_file'];

			force_download($name_file, $data_file);
		}
	}
	function get_path_file($file = '')
	{
		//  return './'.USERFILES.'/files/'.$file;
		return	$_SERVER['DOCUMENT_ROOT'] . "/" . 'hcis_folder' . "/userfiles/files/" . $file;
	}
	function posting_post($segment_3 = null)
	{
		$id = (int)$segment_3;
		$data = $this->post();
		$retrieve_header = $this->AccKasbankModel->retrieve_by_id($id);
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


		$retrieve_detail = $this->AccKasbankModel->retrieve_detail($id);
		$retrieve_inter_akun = $this->db->query("SELECT * FROM acc_inter_unit
		 where tipe='INTER' ")->result_array();
		$akun_inter = array();
		foreach ($retrieve_inter_akun as $key => $akun) {
			$akun_inter[$akun['lokasi_id']][$akun['lokasi_id_2']] = $akun['acc_akun_id'];
		}

		// Hapus jurnal jika sdh ada berdasarkan no refernsi dan MOdul  //
		$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'KASBANK');
		// Data HEADER
		$this->load->library('Autonumber');
		$no_jurnal = $this->autonumber->jurnal_auto($retrieve_header['lokasi_id'], $retrieve_header['tanggal'], 'KB');
		$dataH = array(
			'no_jurnal' => $no_jurnal,
			'lokasi_id' => $retrieve_header['lokasi_id'],
			'tanggal' => $retrieve_header['tanggal'],
			'no_ref' => $retrieve_header['no_transaksi'],
			'ref_id' => $retrieve_header['id'],
			'tipe_jurnal' => 'AUTO',
			'modul' => 'KASBANK',
			'keterangan' => 'KASBANK: ' . $retrieve_header['keterangan'],
			'is_posting' => 1,
			'diposting_oleh' => $this->user_id
		);
		$id_header = $this->AccJurnalModel->create_header($dataH);
		/* Jurnal Akun Kasbank */
		$dataDebetKredit = array(
			'lokasi_id' => $retrieve_header['lokasi_id'],
			'jurnal_id' => $id_header,
			'acc_akun_id' => $retrieve_header['akun_kasbank_id'], //akun ,
			'debet' => ($retrieve_header['tipe_jurnal'] == 'in') ? $retrieve_header['nilai'] : 0,
			'kredit' => ($retrieve_header['tipe_jurnal'] != 'in') ? $retrieve_header['nilai'] : 0,
			'ket' => $retrieve_header['keterangan'],
			'no_referensi' => $retrieve_header['no_transaksi'],
			'referensi_id' => NULL,
			'blok_stasiun_id' => NULL,
			'kegiatan_id' => NULL,
			'kendaraan_mesin_id' => NULL
		);
		$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataDebetKredit);
		/* Jurnal Akun Inter unit lawan kas bank pada lokasinya kasbank  jika beda lokasi */
		foreach ($retrieve_detail as $key => $value) {
			/* cek beda lokasi hd vs dt utk menjurnal inter akunting */
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
					'ket' => 'INTER UNIT AKUN KASBANK:' . $value['ket'],
					'no_referensi' => $retrieve_header['no_transaksi'],
					'referensi_id' => NULL,
					'blok_stasiun_id' => NULL,
					'kegiatan_id' => NULL, //kegiatan ,
					'kendaraan_mesin_id' => NULL
				);
				$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataDt);
			}
		}

		/* Jurnal biaya2(Lawan Kas bank) */
		foreach ($retrieve_detail as $key => $value) {
			$dataDt = array(
				'lokasi_id' => $value['lokasi_id'],
				'jurnal_id' => $id_header,
				'acc_akun_id' => ($value['acc_akun_id']), //akun 
				'debet' => ($value['debet']),
				'kredit' => ($value['kredit']),
				'ket' => 'KASBANK:' . $value['ket'],
				'no_referensi' => $retrieve_header['no_transaksi'],
				'referensi_id' => NULL,
				'blok_stasiun_id' => $value['blok_stasiun_id'],
				'kegiatan_id' => $value['kegiatan_id'], //kegiatan 
				'divisi_id' => $value['divisi_id'],
				'kendaraan_mesin_id' =>  $value['kendaraan_mesin_id']
			);
			$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataDt);
			//*  jika beda lokasi hd vs dt , kalo beda dibuat jurnal inter unit //	
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
					'ket' => 'KASBANK:' . $value['ket'],
					'no_referensi' => $retrieve_header['no_transaksi'],
					'referensi_id' => NULL,
					'blok_stasiun_id' => NULL,
					'kegiatan_id' => NULL, //kegiatan ,
					'kendaraan_mesin_id' => NULL
				);
				$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataDt);
			}
		}

		$data['diposting_oleh'] = $this->user_id;
		$res = $this->AccKasbankModel->posting($id, $data);
		if (!empty($res)) {
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function get_path_file2($img = '', $size = '')
	{
		if (empty($size)) {
			return  $_SERVER['DOCUMENT_ROOT'] . "/"  . 'hcis_folder' . '/userfiles/files/' . $img;
		} else {
			$pisah = explode('.', $img);
			$ext = end($pisah);
			$nama_file = $pisah[0];

			return  $_SERVER['DOCUMENT_ROOT'] . "/"  . 'hcis_folder' . '/userfiles/files/' . $nama_file . '_' . $size . '.' . $ext;
		}
	}

	function import_detail_test_post()
	{
		$config['upload_path']   = $this->get_path_file();
		$config['allowed_types'] = 'Xls|xls';
		// $config['max_size']      = '0';
		// $config['max_width']     = '0';
		// $config['max_height']    = '0';
		$config['overwrite'] = true;
		$config['file_name']     = 'import_jurnal.xls';
		$this->upload->initialize($config);
		if ($this->upload->do_upload()) {
			$upload_data = $this->upload->data();
			$filename = $upload_data['file_name'];
			$this->set_response(array("status" => "OK", "data" =>	$upload_data), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" =>	$this->upload->display_errors()), REST_Controller::HTTP_OK);
		}
	}
	function import_detail_post()
	{

		$config['upload_path']   = $this->get_path_file2();
		$config['allowed_types'] = 'Xls|xls';
		// $config['max_size']      = '0';
		// $config['max_width']     = '0';
		// $config['max_height']    = '0';
		$config['overwrite'] = true;
		$config['file_name']     = 'import_jurnal.xls';
		$this->upload->initialize($config);

		if ($this->upload->do_upload()) {
			$upload_data = $this->upload->data();
			$filename = $upload_data['file_name'];

			$excel = array();
			$excel = $this->import($config['upload_path'] . '/' . $filename);
			$arrDetail = array();
			$arrDetail = [];
			if (count($excel) > 0) {
				for ($i = 2; $i < (count($excel) + 1); $i++) {
					$data = $excel[$i];
					if (!empty($data['A'])) {
						$kode_lokasi          =  $data['A'];
						$ket = $data['B'];
						$kode_akun         =  $data['C'];
						$debet =  $data['D'];
						$kredit  =  $data['E'];

						$lokasi = $this->db->query("select * from gbm_organisasi where kode='" . $kode_lokasi . "'")->row_array();
						$lokasi_id = $lokasi['id'];
						$akun = $this->db->query("select * from acc_akun  where kode='" . $kode_akun . "'")->row_array();
						$akun_id = $akun['id'];
						$arrDetail[] = array(
							"acc_akun_id" => $akun_id,
							"lokasi_id" => $lokasi_id,
							"ket" => $ket,
							"debet" => $debet,
							"kredit" => $kredit,
						);
					}
				}
			}
			// var_dump($excel);
			//  exit();
			// $this->set_response([
			// 	'status' => 'OK',
			// 	'message' => 'Data berhasil diimport',
			// ], REST_Controller::HTTP_CREATED);
			$this->set_response(array("status" => "OK", "data" => $arrDetail), REST_Controller::HTTP_OK);
		} else {
			if (!empty($_FILES['userfile']['tmp_name'])) {
				$this->set_response([
					'status' => 'NOT OK',
					'message' => 'Gagal import',
					'data' => $this->upload->display_errors(),
				], REST_Controller::HTTP_OK);
			}
		}
	}
	function import($path_file)
	{
		// $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx(); //Excel 2007 or higher
		$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls(); //Excel 2003
		$spreadsheet = $reader->load($path_file);
		$sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
		return $sheetData;
	}
	function print_slip_get($segment_3 = '')
	{

		$id = (int)$segment_3;
		$data = [];

		$hd = $this->db->query("select a.*, b.kode as kode_akun,b.nama as nama_akun from acc_kasbank_ht a left join
		acc_akun b on a.akun_kasbank_id=b.id where a.id=" . $id)->row_array();
		$data['hd'] = $hd;

		$dt = $this->db->query("select a.*, b.kode as kode_akun,b.nama as nama_akun from acc_kasbank_dt a left join
		acc_akun b on a.acc_akun_id=b.id where a.jurnal_id=" . $id . "
		order by debet desc ")->result_array();
		$data['dt'] = $dt;

		$html = $this->load->view('AccSlipKasbank', $data, true);

		$filename = 'AccSlipKasbank_' . time();
		$this->pdfgenerator->generate($html, $filename, true, 'A4', 'portrait');
		echo $html;
	}
	function print_slip_ttd_get($segment_3 = '')
	{

		$id = (int)$segment_3;
		$data = [];

		$hd = $this->db->query("select a.*, b.kode as kode_akun,b.nama as nama_akun from acc_kasbank_ht a left join
		acc_akun b on a.akun_kasbank_id=b.id where a.id=" . $id)->row_array();
		$data['hd'] = $hd;

		$dt = $this->db->query("select a.*, b.kode as kode_akun,b.nama as nama_akun,
		c.kode as kode_blok, c.nama as nama_blok,d.kode as kode_kendaraan,d.nama as nama_kendaraan,
		e.kode as kode_kegiatan,e.nama as nama_kegiatan 
		from acc_kasbank_dt a left join
		acc_akun b on a.acc_akun_id=b.id 
		left join gbm_organisasi c on a.blok_stasiun_id =c.id
		left join trk_kendaraan d on a.kendaraan_mesin_id =d.id
		left join acc_kegiatan e on a.kegiatan_id =e.id
		where a.jurnal_id=" . $id . "
		")->result_array();
		$data['dt'] = $dt;

		$lokasi = $this->db->query("select * from gbm_organisasi where id=" . $hd['lokasi_id'])->row_array();
		$nama_ttd1 = '';
		$nama_ttd2 = '';
		$nama_ttd3 = '';
		$jabatan1 = '';
		$jabatan2 = '';
		$jabatan3 = '';
		if ($lokasi['tipe'] == 'ESTATE') {
			$nama_ttd1 = 'Andi Wahyudi';
			$nama_ttd2 = 'Zulpan Harahap';
			if ($hd['tanggal'] >= '2023-06-01') { // permintaan user 21 juni 2023
				$nama_ttd3 = 'Herwin';
			} else {
				$nama_ttd3 = '';
			}

			$jabatan1 = 'Kasie';
			$jabatan2 = 'KTU';
			$jabatan3 = 'ESTATE MANAGER';
		} else if ($lokasi['tipe'] == 'MILL') {
			$nama_ttd1 = '';
			$nama_ttd2 = 'Kasuda Annas S';
			$nama_ttd3 = 'Eduward Sianturi';
			$jabatan1 = 'Admin';
			$jabatan2 = 'KTU';
			$jabatan3 = 'MILL MANAGER';
		}
		$data['nama_ttd1'] = $nama_ttd1;
		$data['nama_ttd2'] = $nama_ttd2;
		$data['nama_ttd3'] = $nama_ttd3;
		$data['jabatan1'] = $jabatan1;
		$data['jabatan2'] = $jabatan2;
		$data['jabatan3'] = $jabatan3;

		$html = $this->load->view('AccSlipKasbankTTD_V2', $data, true);

		$filename = 'AccSlipKasbank_' . time();
		$this->pdfgenerator->generate($html, $filename, true, 'A4', 'portrait');
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

	function laporan_saldo_post()
	{
		$format_laporan =  $this->post('format_laporan', true);
		$lokasi_id     = $this->post('lokasi_id', true);
		$tanggal_mulai =  $this->post('tgl_mulai', true);
		$tanggal_akhir =  $this->post('tgl_akhir', true);

		if (is_null($format_laporan)) {
			$format_laporan = 'view';
		}
		// $lokasi_id     =263;
		// $tanggal_mulai =  '2022-01-01';
		// $tanggal_akhir =  '2022-05-01';
		$judulLokasi = 'Semua';
		if ($lokasi_id) {
			$lokasi   = $this->db->query("select * from gbm_organisasi where id=" . $lokasi_id . "")->row_array();
			$judulLokasi = $lokasi['nama'];
			$queryAkun = "select a.* from acc_akun a
				inner join acc_akun_dt b on a.id=b.acc_akun_id 
       			where a.is_kasbank_akun=1 and b.lokasi_id="  . $lokasi_id . " ";
		} else {
			$queryAkun = "select * from acc_akun
			where is_kasbank_akun=1 ";
		}


		$res = array();
		$akuns   = $this->db->query($queryAkun)->result_array();
		foreach ($akuns as $key => $akun) {
			if ($lokasi_id) {
				$querySaldoAwal = "SELECT b.lokasi_id,b.acc_akun_id,sum(debet-kredit)as saldo FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id
				where b.acc_akun_id=" . $akun['id'] . " and  b.lokasi_id=" . $lokasi_id . " and a.tanggal < '" . $tanggal_mulai . "'
				group by b.lokasi_id,b.acc_akun_id ;";
			} else {
				$querySaldoAwal = "SELECT b.acc_akun_id,sum(debet-kredit)as saldo FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id
				where b.acc_akun_id=" . $akun['id'] . "  and a.tanggal < '" . $tanggal_mulai . "'
				 group by b.acc_akun_id ;";
			}

			$awal   = $this->db->query($querySaldoAwal)->row_array();
			$akun['saldo_awal'] = (!empty($awal)) ? $awal['saldo'] : 0;
			if ($lokasi_id) {
				$query1 = "SELECT b.lokasi_id,b.acc_akun_id,sum(debet)as debet,sum(kredit)as kredit FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
				on a.id=b.jurnal_id
				where b.acc_akun_id=" . $akun['id'] . " and  b.lokasi_id=" . $lokasi_id . "
				and a.tanggal >= '" . $tanggal_mulai . "'  and a.tanggal <= '" . $tanggal_akhir . "'
				group by b.lokasi_id,b.acc_akun_id ;";
			} else {
				$query1 = "SELECT b.acc_akun_id,sum(debet)as debet,sum(kredit)as kredit FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
					on a.id=b.jurnal_id
					where b.acc_akun_id=" . $akun['id'] . " 
					and a.tanggal >= '" . $tanggal_mulai . "'  and a.tanggal <= '" . $tanggal_akhir . "'
					group by b.acc_akun_id ;";
			}

			$resD  = $this->db->query($query1)->row_array();
			$akun['debet'] = (!empty($resD['debet'])) ? $resD['debet'] : 0;
			$akun['kredit'] = (!empty($resD['kredit'])) ? $resD['kredit'] : 0;
			$res[] = $akun;
		}
$v_link = "http://$_SERVER[HTTP_HOST]" . "/klinik_api/api/GlobalReport/acc_kasbank_laporan_saldo_rinci";
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report();
		} else {
			$html = get_header_report_v2();
		}
$html=$html.'<script type="text/javascript">
		function showHideRow(id,e_link) {
			$("#" + "btn_" + id).toggleClass("fa-caret-right fa-caret-down");
			if ($("#" + "hidden_row_" + id).hasClass("hasOpen")) {
				console.log("has open");

			} else {
				console.log("new open");
			}

			if ($("#" + "hidden_row_" + id).hasClass("hidden_row")) {
				$("#" + "hidden_row_" + id).removeClass("hidden_row");
				console.log("open");
				if ($("#" + "hidden_row_" + id).hasClass("hasOpen")) {

				} else {
					$("#" + "hidden_row_" + id).addClass("hasOpen");
					$.ajax({
						type:"GET",
						dataType: "html",
						data:{},
						url: e_link,
						success:function(data)
						{
							
							$("#" + "content_row_" + id).html(data);
							
						}
					});
				}
			} else {
				$("#" + "hidden_row_" + id).addClass("hidden_row");
				console.log("close");

			}
		}
</script>';

		$html = $html . '<div class="row">
  <div class="span12">
	  <br>
	<div class="kop-print">
	  <div class="kop-nama">PT. ANNAJAH TECHNOLOGY INDONESIA</div>
	  <div class="kop-info"> Komplek Vila Sehati B12 , Tapos - Depok</div>
	  <div class="kop-info">Telp : 081387373939</div>
	</div>
	  <hr class="kop-print-hr">
  </div>
  </div>
  <h3 class="title">LAPORAN SALDO KAS BANK</h3>
  <table class="no_border" style="width:30%">
			
			<tr>
					<td>Lokasi</td>
					<td>:</td>
					<td>' . $judulLokasi . '</td>
			</tr>
			<tr>	
					<td>Periode</td>
					<td>:</td>
					<td>' . $tanggal_mulai . ' s/d ' . $tanggal_akhir . '</td>
			</tr>
			
	</table>
			<br>
 ';

		$html = $html . ' <table  border="1" width="100%" style="border-collapse: collapse;" id="table_detail">
			<thead>
				<tr>
					<th rowspan="2" width="4%">No.</th>
					<th rowspan="2">Kode</th>
					<th rowspan="2">Nama</th>
					<th rowspan="2" style="text-align: center;">Saldo awal</th>
					<th colspan ="2" style="text-align: center;">Transaksi</th>
					<th rowspan="2" style="text-align: center;">Saldo Akhir</th>			
				</tr>
				<tr>
				

					<th  style="text-align: right;">Dr</th>
					<th  style="text-align: right;">Cr</th>
						
				</tr>
			</thead>
			<tbody>';


		$no = 0;
		$jumlah = 0;
		$total_jumlah = 0;


		foreach ($res as $key => $m) {
			$actual_link = "http://$_SERVER[HTTP_HOST]" . "/klinik_api/api/GlobalReport/acc_kasbank_laporan_saldo_rinci/" . $tanggal_mulai . "/" . $tanggal_akhir .  "/" . $m['id'] .  "/" . $lokasi_id . "";

			$no++;
			$jumlah = $m['saldo_awal'] + $m['debet'] - $m['kredit'];
			$total_jumlah = $total_jumlah + $jumlah;
			$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative;">
							' . ($no) . '

						</td>
						<td left width="10%" onclick="showHideRow('.$m['id'] .',\''.$actual_link  .'\');"><i class="fas fa-caret-right add-btn" id="btn_'. $m['id'] .'"></i>'. $m['kode'] .'</td>
						</td>
						<td><a href="' . $actual_link  . '" target="_blank"> ' . $m['nama'] . ' </a>
						</td>
											
						<td style="text-align: right;">' . number_format($m['saldo_awal']) . '</td> 
						<td style="text-align: right;">' . number_format($m['debet']) . '</td> 
						<td style="text-align: right;">' . number_format($m['kredit']) . ' </td>
						<td style="text-align: right;">' . number_format($jumlah) . ' </td>
						</tr>';

			$html = $html . '
			<tr id=hidden_row_' .$m['id'] .' class="hidden_row">
		
							<td colspan=7 >
								<div id=content_row_' .$m['id'] .' width="50%">
								</div>
								</td>	
					</tr>';
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


						<td style="text-align: right;">
							
						</td>

						<td>
							&nbsp;
						</td>
						<td>
							&nbsp;
						</td>
						<td style="text-align: right;" ><b>' . number_format($total_jumlah) . '</b>
						</td>
						</tr>
						</tbody>
					</table>
						';
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

	function laporan_saldo_rinci_post()
	{
		$tipe_laporan =  $this->post('tipe_laporan', true);
		if ($tipe_laporan == 'v1') {
			$this->laporan_saldo_rinci_v1();
		} else if ($tipe_laporan == 'v2') {
			$this->laporan_saldo_rinci_v2();
		} else {
			$this->laporan_saldo_rinci_v1();
		}
	}

	function laporan_saldo_rinci_v1()
	{
		$format_laporan =  $this->post('format_laporan', true);
		$lokasi_id = $this->post('lokasi_id', true);
		$akun_id = $this->post('akun_id', true);
		$tanggal_mulai =  $this->post('tgl_mulai', true);
		$tanggal_akhir = $this->post('tgl_akhir', true);
		if (is_null($format_laporan)) {
			$format_laporan = 'view';
		}
		$querySaldoAwal = " SELECT b.lokasi_id,b.acc_akun_id,sum(debet-kredit)as saldo FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
		on a.id=b.jurnal_id
        where b.acc_akun_id=" . $akun_id . " and  b.lokasi_id=" . $lokasi_id . " and a.tanggal < '" . $tanggal_mulai . "'
         group by b.lokasi_id,b.acc_akun_id ;";


		$saldoAwal    = $this->db->query($querySaldoAwal)->row_array();
		$data['saldo_awal'] = (!empty($saldoAwal)) ? $saldoAwal['saldo'] : 0;
		//    print_r($querySaldoAwal);exit();

		$queryTransaksi   = "SELECT a.tanggal,a.no_jurnal,b.no_referensi, b.lokasi_id,b.acc_akun_id,b.debet,b.kredit,b.ket,c.no_referensi AS no_ref_kasbank
		  FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
		on a.id=b.jurnal_id left join acc_kasbank_ht c on a.ref_id=c.id
         where  b.acc_akun_id=" . $akun_id . " and  b.lokasi_id=" . $lokasi_id . "
          and a.tanggal >= '" . $tanggal_mulai . "'   and a.tanggal <= '" . $tanggal_akhir . "'
		  and a.modul='KASBANK' 
          order by a.tanggal,b.no_referensi  ;
          ";


		$results  = $this->db->query($queryTransaksi)->result_array();
		$data['transaksi'] = $results;

		$akun = $this->db->query("select * from acc_akun where id=" . $akun_id . "")->row_array();
		$lokasi = $this->db->query("select * from gbm_organisasi where id=" . $lokasi_id . ";")->row_array();

		//$this->set_response(array("status" => "OK", "data" => $data), REST_Controller::HTTP_OK);
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report();
		} else {
			$html = get_header_report_v2();
		}

		$html = $html . '<div class="row">
  <div class="span12">
	  <br>
	<div class="kop-print">
	  <div class="kop-nama">PT. ANNAJAH TECHNOLOGY INDONESIA</div>
	  <div class="kop-info"> Komplek Vila Sehati B12 , Tapos - Depok</div>
	  <div class="kop-info">Telp : 081387373939</div>
	</div>
	  <hr class="kop-print-hr">
  </div>
  </div>
  <h3 class="title">LAPORAN KAS BANK - DETAIL</h3>
  <table class="no_border" style="width:30%">
			
			<tr>
					<td>Lokasi</td>
					<td>:</td>
					<td>' . $lokasi['nama'] . '</td>
			</tr>
			<tr>	
					<td>Periode</td>
					<td>:</td>
					<td>' . $tanggal_mulai . ' s/d ' . $tanggal_akhir . '</td>
			</tr>
			<tr>	
					<td>Akun</td>
					<td>:</td>
					<td>' . $akun['kode'] . ' - ' . $akun['nama'] . '</td>
			</tr>
			
	</table>
			<br>';

		$html = $html . '<table  border="1" width="100%" style="border-collapse: collapse;" >
			<thead>
			<tr>
                    <th width="4%" rowspan="2">No.</th>
                    <th rowspan="2">Keterangan</th>
                    <th rowspan="2">No Jurnal</th>
					<th rowspan="2">No Ref</th>
					<th rowspan="2">No Ref KasBank</th>
		            <th rowspan="2">Tgl</th>
                    <th colspan="2" style="text-align: center;">Transaksi</th>
                    <th colspan="2" style="text-align: center;">Saldo </th>
                   
                </tr>
				<tr>

                    <th style="text-align: center;">Dr</th>
                    <th style="text-align: center;">Cr</th>
					<th style="text-align: center;">Dr</th>
                    <th style="text-align: center;">Cr</th>
					
                   

                </tr>
		
			</thead>
			<tbody>		

			 <tr class=":: arc-content">
                    <td style="position:relative;">
                    </td>
                    <td>
                        Saldo awal
                    </td>
                    <td>
                    </td>
					<td>
                    </td>
					<td>
                    </td>
                    <td>

                    </td>
                    <td>

                    </td>
                    <td>

                    </td>

                    <td style="text-align: right;">

                        ' . number_format(($data['saldo_awal'] > 0) ? $data['saldo_awal'] : 0) . '

                    </td>
					<td style="text-align: right;">

					' . number_format(($data['saldo_awal'] < 0) ? $data['saldo_awal'] * -1 : 0) . '

				</td>

                </tr>';


		$total_saldo = $data['saldo_awal'];
		$tdebet = 0;
		$tkredit = 0;
		$no = 0;
		foreach ($data['transaksi'] as $key => $m) {
			$tdebet = $tdebet + $m['debet'];
			$tkredit = $tkredit + $m['kredit'];
			$total_saldo = $total_saldo + $m['debet'] - $m['kredit'];
			$actual_link = "http://$_SERVER[HTTP_HOST]" . "/klinik_api/api/GlobalReport/acc_laporan_jurnal?no_jurnal=" . $m['no_jurnal'] . "";
			$no++;
			$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative;">
							' . ($no) . '

						</td>
						<td>
						' . $m['ket'] . ' 
						
						</td>
						<td><a href="' . $actual_link  . '" target="_blank"> ' . $m['no_jurnal'] . ' </a>
						<td>
						' . $m['no_referensi'] . ' 
						
						</td>
						<td>
						' . $m['no_ref_kasbank'] . ' 
						
						</td>
						<td>
							' . $m['tanggal'] . ' 
						</td>';

			if ($m['debet'] > 0) {

				$html = $html . '<td style="text-align: right;">' . number_format($m['debet']) . '</td>';
			} else {
				$html = $html . '<td style="text-align: right;">-</td>';
			}
			if ($m['kredit'] > 0) {

				$html = $html . '<td style="text-align: right;">' . number_format($m['kredit']) . '</td>';
			} else {
				$html = $html . '<td style="text-align: right;">-</td>';
			}


			$html = $html . '<td style="text-align: right;">' . number_format($total_saldo > 0 ? $total_saldo : 0) . '</td>';
			$html = $html . '<td style="text-align: right;">' . number_format($total_saldo < 0 ? $total_saldo * -1 : 0) . '</td>';

			$html = $html . '											
					</tr>';
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
                       ' . number_format($tdebet) . '

                    </td>
                    <td style="text-align: right;">
                        ' . number_format($tkredit) . '

                    </td>
                    <td>
                        &nbsp;
                    </td>
					<td>
                        &nbsp;
                    </td>
                </tr>
				</tbody>
				</table>
						';
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
	function laporan_saldo_rinci_v2()
	{
		$format_laporan =  $this->post('format_laporan', true);
		$lokasi_id = $this->post('lokasi_id', true);
		$akun_id = $this->post('akun_id', true);
		$tanggal_mulai =  $this->post('tgl_mulai', true);
		$tanggal_akhir = $this->post('tgl_akhir', true);
		if (is_null($format_laporan)) {
			$format_laporan = 'view';
		}
		$querySaldoAwal = " SELECT b.lokasi_id,b.acc_akun_id,sum(debet-kredit)as saldo FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
		on a.id=b.jurnal_id
        where b.acc_akun_id=" . $akun_id . " and  b.lokasi_id=" . $lokasi_id . " and a.tanggal < '" . $tanggal_mulai . "'
         group by b.lokasi_id,b.acc_akun_id ;";


		$saldoAwal    = $this->db->query($querySaldoAwal)->row_array();
		$data['saldo_awal'] = (!empty($saldoAwal)) ? $saldoAwal['saldo'] : 0;
		//    print_r($querySaldoAwal);exit();

		$queryTransaksi   = "SELECT *  FROM acc_kasbank_ht  
         where  akun_kasbank_id=" . $akun_id . " and  lokasi_id=" . $lokasi_id . "
          and tanggal >= '" . $tanggal_mulai . "'   and tanggal <= '" . $tanggal_akhir . "'
          order by tanggal,no_transaksi  ;
          ";

		// echo $queryTransaksi;exit();
		$results  = $this->db->query($queryTransaksi)->result_array();
		$data['transaksi'] = $results;

		$akun = $this->db->query("select * from acc_akun where id=" . $akun_id . "")->row_array();
		$lokasi = $this->db->query("select * from gbm_organisasi where id=" . $lokasi_id . ";")->row_array();

		//$this->set_response(array("status" => "OK", "data" => $data), REST_Controller::HTTP_OK);
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report();
		} else {
			$html = get_header_report_v2();
		}

		$html = $html . '<div class="row">
  <div class="span12">
	  <br>
	<div class="kop-print">
	  <div class="kop-nama">PT. ANNAJAH TECHNOLOGY INDONESIA</div>
	  <div class="kop-info"> Komplek Vila Sehati B12 , Tapos - Depok</div>
	  <div class="kop-info">Telp : 081387373939</div>
	</div>
	  <hr class="kop-print-hr">
  </div>
  </div>
  <h3 class="title">LAPORAN KAS BANK - DETAIL</h3>
  <table class="no_border" style="width:30%">
			
			<tr>
					<td>Lokasi</td>
					<td>:</td>
					<td>' . $lokasi['nama'] . '</td>
			</tr>
			<tr>	
					<td>Periode</td>
					<td>:</td>
					<td>' . $tanggal_mulai . ' s/d ' . $tanggal_akhir . '</td>
			</tr>
			<tr>	
					<td>Akun</td>
					<td>:</td>
					<td>' . $akun['kode'] . ' - ' . $akun['nama'] . '</td>
			</tr>
			
	</table>
			<br>';

		$html = $html . '<table  border="1" width="100%" style="border-collapse: collapse;" >
			<thead>
			<tr>
                    <th width="4%" rowspan="2">No.</th>
                    <th rowspan="2">Keterangan</th>
                    <th rowspan="2">No Transaksi</th>
					<th rowspan="2">No ref</th>
                    <th rowspan="2">Tgl</th>
					<th rowspan="2">Posting</th>
                    <th colspan="2" style="text-align: center;">Transaksi</th>
                    <th colspan="2" style="text-align: center;">Saldo </th>
                   
                </tr>
				<tr>

                    <th style="text-align: center;">Dr</th>
                    <th style="text-align: center;">Cr</th>
					<th style="text-align: center;">Dr</th>
                    <th style="text-align: center;">Cr</th>
					
                   

                </tr>
		
			</thead>
			<tbody>		

			 <tr class=":: arc-content">
                    <td style="position:relative;">
                    </td>
                    <td>
                        Saldo awal
                    </td>
                    <td>
                    </td>
					<td>
                    </td>
                    <td>

                    </td>
                    <td>
                    </td>
                    <td>
                    </td>
					<td>
                    </td>
                    <td style="text-align: right;">

                        ' . number_format(($data['saldo_awal'] > 0) ? $data['saldo_awal'] : 0) . '

                    </td>
					<td style="text-align: right;">

					' . number_format(($data['saldo_awal'] < 0) ? $data['saldo_awal'] * -1 : 0) . '

				</td>

                </tr>';


		$total_saldo = $data['saldo_awal'];
		$tdebet = 0;
		$tkredit = 0;
		$no = 0;
		foreach ($data['transaksi'] as $key => $m) {
			if ($m['tipe_jurnal'] == 'in') {
				$tdebet = $tdebet + $m['nilai'];
				$dr = $m['nilai'];
				$cr = 0;
			} else {
				$tkredit = $tkredit + $m['nilai'];
				$cr = $m['nilai'];
				$dr = 0;
			}
			$status = $m['is_posting'] == 1 ? 'Y' : 'N';
			$total_saldo = $total_saldo + $dr - $cr;
			$actual_link = "http://$_SERVER[HTTP_HOST]" . "/klinik_api/api/GlobalReport/acc_laporan_jurnal?no_jurnal=" . $m['no_jurnal'] . "";
			$tdebet = $tdebet + $m['debet'];
			$no++;
			$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative;">
							' . ($no) . '

						</td>
						<td>
						' . $m['keterangan'] . ' 
						
						</td>
						<td><a href="' . $actual_link  . '" target="_blank"> ' . $m['no_transaksi'] . ' </a>
						<td>
						' . $m['no_referensi'] . ' 
						
						</td>
						<td>
						' . $m['tanggal'] . ' 
						
						</td>
						<td>
							' . $status . ' 
						</td>';


			if ($dr > 0) {

				$html = $html . '<td style="text-align: right;">' . number_format($dr) . '</td>';
			} else {
				$html = $html . '<td style="text-align: right;">-</td>';
			}
			if ($cr > 0) {

				$html = $html . '<td style="text-align: right;">' . number_format($cr) . '</td>';
			} else {
				$html = $html . '<td style="text-align: right;">-</td>';
			}


			$html = $html . '<td style="text-align: right;">' . number_format($total_saldo > 0 ? $total_saldo : 0) . '</td>';
			$html = $html . '<td style="text-align: right;">' . number_format($total_saldo < 0 ? $total_saldo * -1 : 0) . '</td>';

			$html = $html . '											
					</tr>';
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
                       ' . number_format($tdebet) . '

                    </td>
                    <td style="text-align: right;">
                        ' . number_format($tkredit) . '

                    </td>
                    <td>
                        &nbsp;
                    </td>
					<td>
                        &nbsp;
                    </td>
                </tr>
				</tbody>
				</table>
						';
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
	function laporan_transaksi_kasbank_post()
	{
		$format_laporan =  $this->post('format_laporan', true);
		$lokasi_id     = $this->post('lokasi_id', true);
		$tanggal_mulai =  $this->post('tgl_mulai', true);
		$tanggal_akhir =  $this->post('tgl_akhir', true);

		if (is_null($format_laporan)) {
			$format_laporan = 'view';
		}
		// $lokasi_id     =263;
		// $tanggal_mulai =  '2022-01-01';
		// $tanggal_akhir =  '2022-05-01';
		$judulLokasi = 'Semua';
		$query = "SELECT a.*,e.nama AS lokasi, c.kode AS kode_akun_kasbank,c.nama AS nama_akun_kasbank,d.kode AS kode_akun,d.nama AS nama_akun,b.debet,b.kredit,b.ket FROM acc_kasbank_ht a INNER JOIN acc_kasbank_dt b
		ON a.id=b.jurnal_id INNER JOIN acc_akun c ON a.akun_kasbank_id=c.id
		INNER JOIN acc_akun d ON b.acc_akun_id=d.id
		INNER JOIN gbm_organisasi e ON a.lokasi_id=e.id
		where a.tanggal between '" . $tanggal_mulai . "' and '" . $tanggal_akhir . "'";
		if ($lokasi_id) {
			$lokasi   = $this->db->query("select * from gbm_organisasi where id=" . $lokasi_id . "")->row_array();
			$judulLokasi = $lokasi['nama'];
			$query = $query . " and a.lokasi_id=" . $lokasi_id . "";
		} else {
		}

		$query = $query . " order by a.tanggal,a.no_transaksi ";

		$res = array();
		$res_kasbank   = $this->db->query($query)->result_array();


		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report();
		} else {
			$html = get_header_report_v2();
		}
		$html = $html . '<div class="row">
  <div class="span12">
	  <br>
	<div class="kop-print">
    <div class="kop-nama">PT. ANNAJAH TECHNOLOGY INDONESIA</div>
    <div class="kop-info"> Komplek Vila Sehati B12 , Tapos - Depok</div>
    <div class="kop-info">Telp : 081387373939</div>
  </div>
	  <hr class="kop-print-hr">
  </div>
  </div>
  <h3 class="title">LAPORAN TRANSAKSI KAS BANK</h3>
  <table class="no_border" style="width:30%">
			
			<tr>
					<td>Lokasi</td>
					<td>:</td>
					<td>' . $judulLokasi . '</td>
			</tr>
			<tr>	
					<td>Periode</td>
					<td>:</td>
					<td>' . $tanggal_mulai . ' s/d ' . $tanggal_akhir . '</td>
			</tr>

			
	</table>
			<br>';

		$html = $html . ' <table  border="1" width="100%" style="border-collapse: collapse;" >
			<thead>
				<tr>
					<th width="4%">No.</th>
					<th >Akun Kas Bank</th>
					<th>Tipe</th>
					<th>Posting</th>
					<th>Tanggal</th>
					<th>No Transaksi</th>
					<th style="text-align: right;">Nilai</th>
					<th>Akun Transaksi</th>
					<th>Keterangan</th>
					<th  style="text-align: right;">Debet</th>
					<th  style="text-align: right;">Kredit</th>
								
				</tr>
				
			</thead>
			<tbody>';


		$no = 0;
		$jumlah_dr = 0;
		$jumlah_cr = 0;


		foreach ($res_kasbank as $key => $m) {
			$actual_link = "http://$_SERVER[HTTP_HOST]" . "/klinik_api/api/GlobalReport/acc_kasbank_laporan_saldo_rinci/" . $tanggal_mulai . "/" . $tanggal_akhir .  "/" . $m['id'] .  "/" . $lokasi_id . "";

			$no++;
			$jumlah_dr = $jumlah_dr + $m['debet'];
			$jumlah_cr = $jumlah_cr + $m['kredit'];
			$tipe = '';
			$status = '';
			if ($m['tipe_jurnal'] == 'in') {
				$tipe = 'Penerimaan';
			} else {
				$tipe = 'Pembayaran';
			}
			if ($m['is_posting'] == 1) {
				$status = 'Y';
			} else {
				$status = 'N';
			}
			$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative;">
							' . ($no) . '

						</td>
						<td> ' . $m['kode_akun_kasbank'] . '-' . $m['nama_akun_kasbank'] . '	</td>
						<td>' . $tipe . ' </td>
						<td>' . $status . ' </td>
						<td>' .  tgl_indo($m['tanggal']) . ' </td>	
						<td>' . $m['no_transaksi'] . ' </td>	
						<td style="text-align: right;">' . number_format($m['nilai']) . ' </td>	
						<td> ' . $m['kode_akun'] . '-' . $m['nama_akun'] . '	</td>
						<td>' . $m['ket'] . ' </td>					
						<td style="text-align: right;">' . number_format($m['debet']) . '</td> 
						<td style="text-align: right;">' . number_format($m['kredit']) . '</td> 
						
						';

			$html = $html . '
									
					</tr>';
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


						<td style="text-align: right;">
							
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
						
						<td style="text-align: right;" ><b>' . number_format($jumlah_dr) . '</b>
						<td style="text-align: right;" ><b>' . number_format($jumlah_cr) . '</b>
						</td>
						</tr>
						</tbody>
					</table>
						';
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
