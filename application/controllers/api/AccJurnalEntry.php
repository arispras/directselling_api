<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

class AccJurnalEntry extends BD_Controller //Rest_Controller
{
	public $user_id;
	public $theCredential;
	public $data = array();
	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('GbmOrganisasiModel');
		$this->load->model('AccJurnalEntryModel');
		$this->load->model('AccJurnalModel');
		$this->load->model('M_DatatablesModel');
		$this->load->library('pdfgenerator');
		$this->load->helper("antech_helper");
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

		$query  = "SELECT 
		a.*,
		b.nama as lokasi,
		c.user_full_name AS dibuat,
		d.user_full_name AS diubah,
		e.user_full_name AS diposting FROM acc_jurnal_entry_ht a 
		inner join gbm_organisasi b on a.lokasi_id=b.id
		LEFT JOIN fwk_users c ON a.dibuat_oleh = c.id
		LEFT JOIN fwk_users d ON a.diubah_oleh = d.id
		LEFT JOIN fwk_users e ON a.diposting_oleh = e.id   
		";
		$search = array('a.no_jurnal', 'a.no_referensi', 'a.tanggal', 'b.nama', 'a.keterangan');
		$where  = null;

		// $isWhere = "a.tipe_jurnal='UMUM'";
		// $isWhere = " a.tipe_jurnal='UMUM' and a.lokasi_id in
		// (select location_id from fwk_users_location where user_id=" . $this->user_id . ")";
		$isWhere = " 1=1";
		if ($param['tgl_mulai'] && $param['tgl_mulai']) {
			$isWhere = " a.tanggal between '" . $param['tgl_mulai'] . "' and '" . $param['tgl_akhir'] . "'";
		}
		if ($param['lokasi_id']) {
			$isWhere = $isWhere . " and a.lokasi_id =" . $param['lokasi_id'] . "";
		} else {
			$isWhere = $isWhere . " and  a.lokasi_id in
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
		for ($i=0; $i < count($data['data']) ; $i++) { 
			$d=$data['data'][$i];
			$dt = $this->db->query("select a.*, b.kode as kode_akun,b.nama as nama_akun, 
			c.kode as kode_blok, c.nama as nama_blok,d.kode as kode_kendaraan,d.nama as nama_kendaraan,
			e.kode as kode_kegiatan,e.nama as nama_kegiatan
			 from acc_jurnal_entry_dt a left join
			acc_akun b on a.acc_akun_id=b.id 
			left join gbm_organisasi c on a.blok_stasiun_id =c.id
			left join trk_kendaraan d on a.kendaraan_mesin_id =d.id
			left join acc_kegiatan e on a.kegiatan_id =e.id
			where a.jurnal_id=" . $d['id'] . " order by a.id")->result_array();
			$data['data'][$i]['dt'] = $dt;
		}
		$this->set_response($data, REST_Controller::HTTP_OK);
	}

	function index_get($id = '')
	{
		$retrieve = array();
		$retrieve = $this->AccJurnalEntryModel->retrieve_by_id($id);
		$retrieve_detail = $this->AccJurnalEntryModel->retrieve_detail($id);

		$retrieve['detail'] = $retrieve_detail;

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function getDetail_get($id = '')
	{
		$retrieve = $this->AccJurnalEntryModel->retrieve_detail($id);
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
		$input['no_jurnal'] = $this->autonumber->acc_jurnal_entry($input['lokasi_id']['id'], $input['tanggal']);

		$res = $this->AccJurnalEntryModel->create($input);
		if (!empty($res)) {
			$this->set_response(array("status" => "OK",  "data" => $input['no_jurnal']), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Gagal Simpan"), REST_Controller::HTTP_OK);
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


		$res = $this->AccJurnalEntryModel->update($id, $data);
		if (!empty($res)) {
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Gagal Simpan"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	public function index_delete($id)
	{
		$retrieve_header = $this->AccJurnalEntryModel->retrieve_by_id($id);
		if (empty($retrieve_header)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$res = $this->AccJurnalEntryModel->delete($id);
		if (!empty($res)) {
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Gagal Simpan"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function posting_post($segment_3 = null)
	{
		$id = (int)$segment_3;

		$retrieve_header = $this->AccJurnalEntryModel->retrieve_by_id($id);

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
		$retrieve_detail = $this->AccJurnalEntryModel->retrieve_detail($id);
		$retrieve_inter_akun = $this->db->query("SELECT * FROM acc_inter_unit
		 where tipe='INTER' ")->result_array();
		$akun_inter = array();
		foreach ($retrieve_inter_akun as $key => $akun) {
			$akun_inter[$akun['lokasi_id']][$akun['lokasi_id_2']] = $akun['acc_akun_id'];
		}

		// Hapus jurnal jika sdh ada berdasarkan no refernsi dan MOdul  //
		$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'JURNAL');
		// Data HEADER
		$this->load->library('Autonumber');
		$no_jurnal = $this->autonumber->jurnal_auto($retrieve_header['lokasi_id'], $retrieve_header['tanggal'], 'JM');
		$dataH = array(
			'no_jurnal' => $no_jurnal,
			'lokasi_id' => $retrieve_header['lokasi_id'],
			'tanggal' => $retrieve_header['tanggal'],
			'no_ref' => $retrieve_header['no_jurnal'],
			'ref_id' => $retrieve_header['id'],
			'tipe_jurnal' => $retrieve_header['tipe_jurnal'],
			'modul' => 'JURNAL',
			'keterangan' => 'JURNAL: ' . $retrieve_header['keterangan'],
			'is_posting' => 1,
			'diposting_oleh' => $this->user_id
		);
		$id_header = $this->AccJurnalModel->create_header($dataH);


		/* Jurnal Akun Inter unit lawan kas bank pada lokasinya JURNAL  jika beda lokasi */
		$first_row_location_id = null;
		$urut = 0;
		foreach ($retrieve_detail as $key => $value) {
			$urut = $urut + 1;
			/* cek beda lokasi baris pertama  vs dt utk menjurnal inter akunting */
			if ($urut == 1) {
				$first_row_location_id = $value['lokasi_id'];
				$dataDt = array(
					'lokasi_id' =>  $value['lokasi_id'], // Lokasi 
					'jurnal_id' => $id_header,
					'acc_akun_id' => $value['acc_akun_id'],
					'debet' => $value['debet'],
					'kredit' => $value['kredit'],
					'ket' => 'JURNAL:' . $value['ket'],
					'no_referensi' => $retrieve_header['no_jurnal'],
					'referensi_id' => NULL,
					'blok_stasiun_id' => $value['blok_stasiun_id'],
					'kegiatan_id' => $value['kegiatan_id'], //kegiatan 
					'divisi_id' => $value['divisi_id'],
					'kendaraan_mesin_id' =>  $value['kendaraan_mesin_id']
				);
				$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataDt);
			} else {
				if ($value['lokasi_id'] != $first_row_location_id) {
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
					if ($akun_inter[$first_row_location_id][$value['lokasi_id']]) {
						$inter_akun_id = $akun_inter[$first_row_location_id][$value['lokasi_id']];
					}
					if ($akun_inter[$value['lokasi_id']][$first_row_location_id]) {
						$inter_akun_id = $akun_inter[$value['lokasi_id']][$first_row_location_id];
					}
					$dataDt = array(
						'lokasi_id' =>  $first_row_location_id, // Lokasi 
						'jurnal_id' => $id_header,
						'acc_akun_id' => $inter_akun_id, // inter unit akun lokasi Dt 
						'debet' => $dr_dt,
						'kredit' => $cr_dt,
						'ket' => 'INTER UNIT JURNAL:' . $value['ket'],
						'no_referensi' => $retrieve_header['no_jurnal'],
						'referensi_id' => NULL,
						'blok_stasiun_id' => NULL,
						'kegiatan_id' => NULL, //kegiatan ,
						'kendaraan_mesin_id' => NULL
					);
					$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataDt);
				}
			}
		}

		/* Jurnal akun2 berikutnya */
		$urut = 0;
		foreach ($retrieve_detail as $key => $value) {
			$urut = $urut + 1;
			/* cek apakah baris pertama, jika ya maka lewati karena sdh dibuat jurnal di atas  */
			if ($urut == 1) {
			} else {

				$dataDt = array(
					'lokasi_id' => $value['lokasi_id'],
					'jurnal_id' => $id_header,
					'acc_akun_id' => ($value['acc_akun_id']), //akun 
					'debet' => ($value['debet']),
					'kredit' => ($value['kredit']),
					'ket' => 'JURNAL:' . $value['ket'],
					'no_referensi' => $retrieve_header['no_jurnal'],
					'referensi_id' => NULL,
					'blok_stasiun_id' => $value['blok_stasiun_id'],
					'kegiatan_id' => $value['kegiatan_id'], //kegiatan 
					'divisi_id' => $value['divisi_id'],
					'kendaraan_mesin_id' =>  $value['kendaraan_mesin_id']
				);
				$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataDt);
				//*  jika beda lokasi baris pertama vs dt , kalo beda dibuat jurnal inter unit //	
				if ($value['lokasi_id'] != $first_row_location_id) {
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
					if ($akun_inter[$first_row_location_id][$value['lokasi_id']]) {
						$inter_akun_id = $akun_inter[$first_row_location_id][$value['lokasi_id']];
					}
					if ($akun_inter[$value['lokasi_id']][$first_row_location_id]) {
						$inter_akun_id = $akun_inter[$value['lokasi_id']][$first_row_location_id];
					}
					$dataDt = array(
						'lokasi_id' => $value['lokasi_id'],
						'jurnal_id' => $id_header,
						'acc_akun_id' => $inter_akun_id, // inter unit akun lokasi Dt 
						'debet' => $dr_dt,
						'kredit' => $cr_dt,
						'ket' => 'INTER UNIT JURNAL:' . $value['ket'],
						'no_referensi' => $retrieve_header['no_jurnal'],
						'referensi_id' => NULL,
						'blok_stasiun_id' => NULL,
						'kegiatan_id' => NULL, //kegiatan ,
						'kendaraan_mesin_id' => NULL
					);
					$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataDt);
				}
			}
		}

		$data['diposting_oleh'] = $this->user_id;
		$res = $this->AccJurnalEntryModel->posting($id, $data);
		if (!empty($res)) {
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Gagal Simpan"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function get_path_file($img = '', $size = '')
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

		$hd = $this->db->query("select a.*
		from acc_jurnal_entry_ht a  where a.id=" . $id)->row_array();
		$data['hd'] = $hd;

		$dt = $this->db->query("select a.*, b.kode as kode_akun,b.nama as nama_akun, 
		c.kode as kode_blok, c.nama as nama_blok,d.kode as kode_kendaraan,d.nama as nama_kendaraan,
		e.kode as kode_kegiatan,e.nama as nama_kegiatan
		 from acc_jurnal_entry_dt a left join
		acc_akun b on a.acc_akun_id=b.id 
		left join gbm_organisasi c on a.blok_stasiun_id =c.id
		left join trk_kendaraan d on a.kendaraan_mesin_id =d.id
		left join acc_kegiatan e on a.kegiatan_id =e.id
		where a.jurnal_id=" . $id . " order by a.id")->result_array();
		$data['dt'] = $dt;

		$html = $this->load->view('AccSlipJurnal', $data, true);

		$filename = 'AccSlipJurnal_' . time();
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
	function laporan_neraca_saldo_post()
	{

		$lokasi_id     = $this->post('lokasi_id', true);
		$tanggal_mulai =  $this->post('tgl_mulai', true);
		$tanggal_akhir =  $this->post('tgl_akhir', true);

		// $lokasi_id     =263;
		// $tanggal_mulai =  '2022-01-01';
		// $tanggal_akhir =  '2022-05-01';

		$lokasi   = $this->db->query("select * from gbm_organisasi where id=" . $lokasi_id . "")->row_array();
		$judulLokasi = $lokasi['nama'];
		$queryAkun = "select * from acc_akun
       			where aktif=1 and is_transaksi_akun=1 order by kode ";

		$res = array();
		$akuns   = $this->db->query($queryAkun)->result_array();
		foreach ($akuns as $key => $akun) {
			$querySaldoAwal = "SELECT b.lokasi_id,b.acc_akun_id,sum(debet-kredit)as saldo FROM acc_jurnal_entry_ht a inner join acc_jurnal_entry_dt b 
			on a.id=b.jurnal_id
			where b.acc_akun_id=" . $akun['id'] . " and  b.lokasi_id=" . $lokasi_id . " and a.tanggal < '" . $tanggal_mulai . "'
			 group by b.lokasi_id,b.acc_akun_id ;";

			$awal   = $this->db->query($querySaldoAwal)->row_array();
			$akun['saldo_awal'] = (!empty($awal)) ? $awal['saldo'] : 0;

			$query1 = "SELECT b.lokasi_id,b.acc_akun_id,sum(debet)as debet,sum(kredit)as kredit FROM acc_jurnal_entry_ht a inner join acc_jurnal_entry_dt b 
			on a.id=b.jurnal_id
			where b.acc_akun_id=" . $akun['id'] . " and  b.lokasi_id=" . $lokasi_id . "
			 and a.tanggal >= '" . $tanggal_mulai . "'  and a.tanggal <= '" . $tanggal_akhir . "'
			 group by b.lokasi_id,b.acc_akun_id ;";

			$resD  = $this->db->query($query1)->row_array();
			$akun['debet'] = (!empty($resD['debet'])) ? $resD['debet'] : 0;
			$akun['kredit'] = (!empty($resD['kredit'])) ? $resD['kredit'] : 0;
			$res[] = $akun;
		}

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

		$html = $html . '<div class="row">
  <div class="span12">
	  <br>
	  <div class="kop-print">
		 	  </div>
	  <hr class="kop-print-hr">
  </div>
  </div>
  <h2>Laporan Neraca Saldo</h2>
  <h3>Periode  : ' . $tanggal_mulai . ' s/d ' . $tanggal_akhir . '</h3>
  <h3>Lokasi  : ' . $judulLokasi . '</h3>';

		$html = $html . ' <table  >
			<thead>
				<tr>
					<th rowspan="2" width="4%">No.</th>
					<th rowspan="2">Kode</th>
					<th rowspan="2">Nama</th>
					<th colspan="2" style="text-align: center;">Saldo awal</th>
					<th colspan ="2" style="text-align: center;">Transaksi</th>
					<th colspan="2" style="text-align: center;">Saldo Akhir</th>			
				</tr>
				<tr>
				
					<th  style="text-align: right;">Dr</th>
					<th  style="text-align: right;">Cr</th>
					<th  style="text-align: right;">Dr</th>
					<th  style="text-align: right;">Cr</th>
					<th  style="text-align: right;">Dr</th>
					<th  style="text-align: right;">Cr</th>		
				</tr>
			</thead>
			<tbody>';


		$no = 0;
		$jumlah = 0;



		foreach ($res as $key => $m) {
			$no++;
			$jumlah = $m['saldo_awal'] + $m['debet'] - $m['kredit'];
			$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative;">
							' . ($no) . '

						</td>
						<td>
						' . $m['kode'] . ' 
						
						</td>
						<td>
						' . $m['nama'] . ' 
						
						</td>
											
						<td style="text-align: right;">' . number_format($m['saldo_awal'] > 0 ? $m['saldo_awal'] : 0) . ' 
						<td style="text-align: right;">' . number_format($m['saldo_awal'] < 0 ? $m['saldo_awal'] * -1 : 0) . ' 
						<td style="text-align: right;">' . number_format($m['debet']) . ' 
						<td style="text-align: right;">' . number_format($m['kredit']) . ' 
						<td style="text-align: right;">' . number_format($jumlah > 0 ? $jumlah : 0) . ' 
						<td style="text-align: right;">' . number_format($jumlah < 0 ? $jumlah * -1 : 0) . ' 
						</td>';

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


						<td style="text-align: right;">
							
						</td>

						<td>
							&nbsp;
						</td>
						<td>
							&nbsp;
						</td>
						<td style="text-align: right;">
							
						</td>
						<td style="text-align: right;">
							
						</td>
						<td style="text-align: right;">
							
						</td>
						<td style="text-align: right;">
							
						</td>

						</tr>
								</tbody>
							</table>
						';
		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		echo $html;
	}

	function laporan_buku_besar_post()
	{
		$lokasi_id = $this->post('lokasi_id', true);
		$akun_id = $this->post('akun_id', true);
		$tanggal_mulai =  $this->post('tgl_mulai', true);
		$tanggal_akhir = $this->post('tgl_akhir', true);

		$querySaldoAwal = " SELECT b.lokasi_id,b.acc_akun_id,sum(debet-kredit)as saldo FROM acc_jurnal_entry_ht a inner join acc_jurnal_entry_dt b 
		on a.id=b.jurnal_id
        where b.acc_akun_id=" . $akun_id . " and  b.lokasi_id=" . $lokasi_id . " and a.tanggal < '" . $tanggal_mulai . "'
         group by b.lokasi_id,b.acc_akun_id ;";


		$saldoAwal    = $this->db->query($querySaldoAwal)->row_array();
		$data['saldo_awal'] = (!empty($saldoAwal)) ? $saldoAwal['saldo'] : 0;
		//    print_r($querySaldoAwal);exit();

		$queryTransaksi   = "SELECT a.tanggal,a.no_jurnal,b.no_referensi, b.lokasi_id,b.acc_akun_id,b.debet,b.kredit,b.ket  FROM acc_jurnal_entry_ht a inner join acc_jurnal_entry_dt b 
		on a.id=b.jurnal_id
         where  b.acc_akun_id=" . $akun_id . " and  b.lokasi_id=" . $lokasi_id . "
          and a.tanggal >= '" . $tanggal_mulai . "'   and a.tanggal <= '" . $tanggal_akhir . "'
          order by a.tanggal  ;
          ";


		$results  = $this->db->query($queryTransaksi)->result_array();
		$data['transaksi'] = $results;

		$akun = $this->db->query("select * from acc_akun where id=" . $akun_id . "")->row_array();
		$lokasi = $this->db->query("select * from gbm_organisasi where id=" . $lokasi_id . ";")->row_array();

		//$this->set_response(array("status" => "OK", "data" => $data), REST_Controller::HTTP_OK);
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

		$html = $html . '<div class="row">
  <div class="span12">
	  <br>
	  <div class="kop-print">
		 </div>
	  <hr class="kop-print-hr">
  </div>
  </div>
  <h2>Laporan Buku Besar</h2>
  <h3>Periode  : ' . $tanggal_mulai . ' s/d ' . $tanggal_akhir . '</h3>
  <h3>Lokasi : ' . $lokasi['nama'] . '</h3>
  <h3>' . $akun['kode'] . ' - ' . $akun['nama'] . '</h3>';

		$html = $html . ' <table  >
			<thead>
			<tr>
                    <th width="4%" rowspan="2">No.</th>
                    <th rowspan="2">Keterangan</th>
                    <th rowspan="2">No Jurnal</th>
					<th rowspan="2">No ref</th>
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

			$no++;
			$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative;">
							' . ($no) . '

						</td>
						<td>
						' . $m['ket'] . ' 
						
						</td>
						<td>
						' . $m['no_jurnal'] . ' 
						
						</td>
						<td>
						' . $m['no_referensi'] . ' 
						
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
		echo $html;
	}
	function laporan_jurnal_post()
	{
		$lokasi_id = $this->post('lokasi_id', true);
		$akun_dari = $this->post('akun_dari', true);
		$akun_sampai = $this->post('akun_sampai', true);
		$tanggal_mulai =  $this->post('tgl_mulai', true);
		$tanggal_akhir = $this->post('tgl_akhir', true);
		$ket = $this->post('ket', true);
		$no_jurnal = $this->post('no_jurnal', true);
		$no_ref = $this->post('no_ref', true);
		// $lokasi_id = 263;
		// $akun_dari = 1557;
		// $akun_sampai = 2512;
		// $tanggal_mulai = '2022-01-01';
		// $tanggal_akhir = '2022-11-01';
		// $ket = '';
		// $no_jurnal = '';
		// $no_ref = '';
		$akun_1 = $this->db->query("select * from acc_akun where id=" . $akun_dari . "")->row_array();
		$akun_2 = $this->db->query("select * from acc_akun where id=" . $akun_sampai . "")->row_array();


		$queryTransaksi   = "SELECT a.tanggal,a.no_jurnal,a.no_ref, b.lokasi_id,b.acc_akun_id,b.debet,b.kredit,
		b.ket ,c.kode as kode_akun,c.nama as nama_akun
		FROM acc_jurnal_entry_ht a inner join acc_jurnal_entry_dt b 
		on a.id=b.jurnal_id inner join acc_akun c on b.acc_akun_id=c.id
         where  (c.kode between '" . $akun_1['kode']  . "' and '" . $akun_2['kode'] . "') 
		 and b.lokasi_id=" . $lokasi_id . "
          and a.tanggal >= '" . $tanggal_mulai . "'   and a.tanggal <= '" . $tanggal_akhir . "'
        ";

		if ($ket && $ket != '') {
			$queryTransaksi = 	$queryTransaksi . " and b.ket like '%" . $ket . "%'";
		}
		if ($no_jurnal  && $no_jurnal != '') {
			$queryTransaksi = 	$queryTransaksi . " and a.no_jurnal like '%" . $no_jurnal . "%'";
		}
		if ($no_ref  && $no_ref != '') {
			$queryTransaksi = 	$queryTransaksi . " and a.no_ref like '%" . $no_ref . "%'";
		}
		$queryTransaksi = 	$queryTransaksi . " order by a.tanggal,a.no_jurnal";

		$results  = $this->db->query($queryTransaksi)->result_array();
		$data['transaksi'] = $results;

		$lokasi = $this->db->query("select * from gbm_organisasi where id=" . $lokasi_id . ";")->row_array();

		//$this->set_response(array("status" => "OK", "data" => $data), REST_Controller::HTTP_OK);
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

		$html = $html . '<div class="row">
  <div class="span12">
	  <br>
	  <div class="kop-print">
		 </div>
	  <hr class="kop-print-hr">
  </div>
  </div>
  <h2>Laporan Jurnal</h2>
  <h3>Periode : ' . $tanggal_mulai . ' s/d ' . $tanggal_akhir . '</h3>
  <h3>Gudang  : ' . $lokasi['nama'] . '</h3>
  <h3>Akun    :' . $akun_1['kode'] . ' s/d ' . $akun_2['kode'] . '</h3>';

		$html = $html . ' <table  >
			<thead>
			<tr>
                    <th width="4%" rowspan="2">No.</th>
                    <th >Keterangan</th>
					<th >Akun</th>
                    <th >No Jurnal</th>
					<th >No ref</th>
                    <th >Tgl</th>
                    <th  style="text-align: right;">Dr</th>
					<th  style="text-align: right;">Cr</th>                
                </tr>				
			</thead>
			<tbody>	';
		$tdebet = 0;
		$tkredit = 0;
		$no = 0;
		foreach ($data['transaksi'] as $key => $m) {
			$tdebet = $tdebet + $m['debet'];
			$tkredit = $tkredit + $m['kredit'];
			$no++;
			$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative;">
							' . ($no) . '

						</td>
						<td>
						' . $m['kode_akun'] . '-' . $m['nama_akun'] . '
						
						</td>
						<td>
						' . $m['ket'] . ' 
						
						</td>
						<td>
						' . $m['no_jurnal'] . ' 
						
						</td>
						<td>
						' . $m['no_ref'] . ' 
						
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
                    
                </tr>
				</tbody>
				</table>
						';
		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		echo $html;
	}
}
