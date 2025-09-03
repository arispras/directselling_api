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

class KaryawanMobile extends REST_Controller
{
	public $user_id;
	public $theCredential;
	public $data = array();
	function __construct()
	{
		error_reporting(0);
		date_default_timezone_set("Asia/Jakarta");
		parent::__construct();
		$this->load->model('KaryawanModel');
		$this->load->model('M_DatatablesModel');
		$this->load->model('LoginModel');
		$this->load->library('pdfgenerator');
		$this->load->helper(array('url', 'antech_helper', 'form', 'text',  'security', 'file', 'number', 'date', 'download'));
		$this->load->library('image_lib');
		$this->load->library('upload');
		// $this->auth();
		// $this->theCredential = $this->user_data;
		// $this->user_id = $this->user_data->id;
	}
	function simpan_face_post()
	{

		$this->db->insert("karyawan_face", array(
			'karyawan_id' => $this->post('karyawan_id'),
			'location' => $this->post('location'),
			'embeddings' => $this->post('embeddings'),
			'distance' => $this->post('distance'),

		));

		$this->set_response(array("status" => "OK", "data" => "Berhasil disimpan"), REST_Controller::HTTP_CREATED);
	}
	function cari_karyawan_by_embeddings_post()
	{
		$emb =	$this->post('emb');
		$emb_data = json_decode($emb, true);

		// $pair = array("nama"=>"Unknown","distance"=> -5);
		$pair_nama = "Tidak Terdaftar";
		$pair_distance = -5;
		$pair_karyawan_id = 0;
		$pair_nip = '';
		$res = $this->db->query("select b.nip,b.nama,a.* from karyawan_face a inner join karyawan b on a.karyawan_id=b.id ")->result_array();
		foreach ($res as $key => $karyawan) {

			$nama = $karyawan['nama'];
			$karyawan_id = $karyawan['karyawan_id'];
			$nip = $karyawan['nip'];
			$knownEmb = json_decode($karyawan['embeddings'], true);
			$distance = 0;
			for ($i = 0; $i <  count($emb_data); $i++) {
				$diff = $emb_data[$i] - $knownEmb[$i];
				$distance += $diff * $diff;
			}
			$distance = sqrt($distance);
			if ($pair_distance == -5 || $distance < $pair_distance) {
				$pair_distance = $distance;
				$pair_nama = $nama;
				$pair_karyawan_id = $karyawan_id;
				$pair_nip = $nip;
			}
		}
		$result = array('karyawan_id' => $pair_karyawan_id, 'nama' => $pair_nama, 'nip' => $pair_nip, 'distance' => $pair_distance);
		if ($pair_distance > 1) {
			$this->set_response(array("status" => "NOT OK", "data" => $result), REST_Controller::HTTP_OK);
			return;
		} else {
			$this->db->insert("payroll_absensi_scan", array(
				'karyawan_id' =>$pair_karyawan_id,
				'tanggal' => date("Y-m-d"),
				'type' => 'IN',
				'status' =>'0',
				'time'=>date('H:i:s')
	
			));
			$id = $this->db->insert_id();
			$result['id_absensi']=$id;
			$this->set_response(array("status" => "OK", "data" => $result), REST_Controller::HTTP_OK);
			return;
		}
	}
	function create_img_thumb($source_path = '', $marker = '_thumb', $width = '90', $height = '90')
	{
		$config['image_library']  = 'gd2';
		$config['source_image']   = $source_path;
		$config['create_thumb']   = TRUE;
		$config['maintain_ratio'] = TRUE;
		$config['width']          = $width;
		$config['height']         = $height;
		$config['thumb_marker']   = $marker;

		$this->image_lib->initialize($config);
		$this->image_lib->resize();
		$this->image_lib->clear();
		unset($config);

		return true;
	}
	function get_path_image($img = '', $size = '')
	{
		if (empty($size)) {
			return  $_SERVER['DOCUMENT_ROOT'] . "/"  . 'hcis_folder' . '/userfiles/images/' . $img;
		} else {
			$pisah = explode('.', $img);
			$ext = end($pisah);
			$nama_file = $pisah[0];

			return  $_SERVER['DOCUMENT_ROOT'] . "/"  . 'hcis_folder' . '/userfiles/images/' . $nama_file . '_' . $size . '.' . $ext;
		}
	}

	function SaldoCuti_post()
	{
		$format_laporan =  $this->post('format_laporan', true);

		// $id = (int)$segment_3;
		$data = [];

		$input = [
			'lokasi_id' => 252,
		];

		$lokasi_id = $this->post('lokasi_id', true);

		// $lokasi_id = $input['lokasi_id'];

		$queryCuti = "SELECT a.nip,a.nama,c.nama as lokasi, IFNULL( SUM(b.jumlah),0) as saldo from karyawan a 
		LEFT JOIN hrms_cuti_saldo b on a.id=b.karyawan_id
		LEFT JOIN gbm_organisasi c on a.lokasi_tugas_id=c.id
		WHERE a.lokasi_tugas_id = '" . $lokasi_id . "'
		GROUP by a.nip, a.nama,c.nama
		";

		$res = $this->db->query("select * from gbm_organisasi where id=" . $lokasi_id . "")->row_array();
		$filter_lokasi = $res['nama'];

		$dataBkm = $this->db->query($queryCuti)->result_array();
		$data['bkm'] = 	$dataBkm;
		$data['filter_lokasi'] = 	$filter_lokasi;

		$html = $this->load->view('hrms_cuti_saldo_lap', $data, true);

		$filename = 'report_' . time();
		$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		echo $html;
	}

	public function list_post($status_id)
	{
		$post = $this->post();

		$query  = "SELECT a.*,g.nama as lokasi,h.nama as sub_bagian,b.nama as jabatan,c.nama as departemen,d.nama as pangkat,e.nama as golongan,
		f.nama as tipe_karyawan FROM karyawan a left join payroll_jabatan b on a.jabatan_id =b.id
		left join payroll_department c on a.departemen_id=c.id
		left join payroll_pangkat d on a.pangkat_id=d.id
		left join payroll_golongan e on a.golongan_id=e.id
		left join payroll_tipe_karyawan f on a.tipe_karyawan_id=f.id
		left join gbm_organisasi g on a.lokasi_tugas_id=g.id
		left join gbm_organisasi h on a.sub_bagian_id=h.id
		";
		$search = array('a.nip', 'a.nama', 'a.jenis_kelamin', 'b.nama', 'c.nama', 'd.nama', 'e.nama', 'f.nama', 'g.nama', 'h.nama');
		$where  = null;
		$isWhere = "a.lokasi_tugas_id in
		(select location_id from fwk_users_location where user_id=" . $this->user_id . ")";

		// $where  = array('a.status_id' => $status_id);

		$data = $this->M_DatatablesModel->get_tables_query_Karyawan($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	public	function index_get($segment_3 = '')
	{
		$karyawan_id = $segment_3;
		$retrieve = $this->KaryawanModel->retrieve($karyawan_id, null, null);
		$retrieve['role_id'] = $this->KaryawanModel->retrieve_role($karyawan_id);
		$retrieve['login'] = $this->LoginModel->retrieve(null, null, null, null, $karyawan_id, null, null, null);

		$retrieve['riwayat_keluarga'] = $this->db->query("select * from payroll_riwayat_keluarga where karyawan_id=" . $karyawan_id . "")->result_array();
		$retrieve['riwayat_bahasa'] = $this->db->query("select * from payroll_riwayat_bahasa where karyawan_id=" . $karyawan_id . "")->result_array();
		$retrieve['riwayat_jabatan'] = $this->db->query("select * from payroll_riwayat_jabatan where karyawan_id=" . $karyawan_id . "")->result_array();
		$retrieve['riwayat_pangkat'] = $this->db->query("select * from payroll_riwayat_kepangkatan where karyawan_id=" . $karyawan_id . "")->result_array();
		$retrieve['riwayat_penghargaan'] = $this->db->query("select * from payroll_riwayat_penghargaan where karyawan_id=" . $karyawan_id . "")->result_array();
		$retrieve['riwayat_hukuman'] = $this->db->query("select * from payroll_riwayat_hukuman where karyawan_id=" . $karyawan_id . "")->result_array();
		$retrieve['riwayat_pendidikan'] = $this->db->query("select * from payroll_riwayat_pendidikan where karyawan_id=" . $karyawan_id . "")->result_array();
		$retrieve['riwayat_keahlian'] = $this->db->query("select * from payroll_riwayat_keahlian where karyawan_id=" . $karyawan_id . "")->result_array();
		$retrieve['riwayat_pengalaman'] = $this->db->query("select * from payroll_riwayat_pengalaman where karyawan_id=" . $karyawan_id . "")->result_array();
		$retrieve['riwayat_pelatihan'] = $this->db->query("select * from payroll_riwayat_pelatihan where karyawan_id=" . $karyawan_id . "")->result_array();


		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => null), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	public	function index_delete($segment_3 = '')
	{

		$id = $segment_3;
		$Karyawan_id       = (int)$segment_3;
		$retrieve_Karyawan = $this->KaryawanModel->retrieve($Karyawan_id);
		if (empty($retrieve_Karyawan)) {
			$this->set_response(array("status" => "NOT OK", "data" => "NOT FOUND Karyawan"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$retrieve_login = $this->LoginModel->retrieve(null, null, null, null, $retrieve_Karyawan['id']);
		$this->KaryawanModel->delete_role($Karyawan_id);
		$res = $this->KaryawanModel->delete($id);
		if (($res)) {
			$retrieveLogin = $this->LoginModel->delete($retrieve_login['id']);
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => null), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function index_post()
	{
		// var_dump($this->post());
		// exit();
		$config['upload_path']   = $this->get_path_image();
		$config['allowed_types'] = 'jpg|jpeg|png';
		$config['max_size']      = '0';
		$config['max_width']     = '0';
		$config['max_height']    = '0';
		$config['file_name']     = 'Karyawan-' . url_title($this->post('nama', TRUE), '-', true);
		$this->upload->initialize($config);

		if (!empty($_FILES['userfile']['tmp_name']) and !$this->upload->do_upload()) {
			$data['error_upload'] = '<span class="text-error">' . $this->upload->display_errors() . '</span>';
			$error_upload = true;
		} else {
			$data['error_upload'] = '';
			$error_upload = false;
		}

		$lokasi_tugas_id      = $this->post('lokasi_tugas_id', TRUE);
		$sub_bagian_id      = $this->post('sub_bagian_id', TRUE);
		$nip           = $this->post('nip', TRUE);
		$nama          = $this->post('nama', TRUE);
		$jenis_kelamin = $this->post('jenis_kelamin', TRUE);
		$tempat_lahir  = $this->post('tempat_lahir', TRUE);
		$tgl_lahir     = $this->post('tgl_lahir', TRUE);
		$bln_lahir     = $this->post('bln_lahir', TRUE);
		$thn_lahir     = $this->post('thn_lahir', TRUE);
		$alamat        = $this->post('alamat', TRUE);
		$username      = $this->post('username', TRUE);
		$password      = $this->post('password2', TRUE);
		$is_admin      = $this->post('is_admin', TRUE);
		$status_id      = $this->post('status_id', TRUE);
		$status_kawin = $this->post('status_pernikahan', TRUE);
		$golongan_darah = $this->post('golongan_darah', TRUE);
		$jabatan_id    =  $this->post('id_jabatan', TRUE);
		$departemen_id    = $this->post('id_departemen', TRUE);
		$golongan_id    = $this->post('id_golongan', TRUE);
		$pangkat_id    = $this->post('id_pangkat', TRUE);
		$tipe_karyawan_id   = $this->post('id_tipe_karyawan', TRUE);
		$telp   = $this->post('telp', TRUE);
		$email   = $this->post('email', TRUE);
		$no_hp   = $this->post('no_hp', TRUE);
		$no_npwp   = $this->post('no_npwp', TRUE);
		$no_kk   = $this->post('no_kk', TRUE);
		$no_ktp   = $this->post('no_ktp', TRUE);
		$no_rek_bank   = $this->post('no_rek_bank', TRUE);
		$nama_bank   = $this->post('nama_bank', TRUE);
		$no_bpjs   = $this->post('no_bpjs', TRUE);
		$no_bpjs_ks   = $this->post('no_bpjs_ks', TRUE);
		$tgl_masuk   = $this->post('tgl_masuk', TRUE);
		$tgl_keluar   = $this->post('tgl_keluar', TRUE);
		$tgl_hbs_kontrak   = $this->post('tgl_hbs_kontrak', TRUE);
		$status_pajak   = $this->post('status_pajak', TRUE);
		$agama   = $this->post('agama', TRUE);
		$is_jht   = $this->post('is_jht', TRUE);
		$is_jp   = $this->post('is_jp', TRUE);
		$is_jks   = $this->post('is_jks', TRUE);


		$foto = null;
		if (!empty($_FILES['userfile']['tmp_name'])) {
			$upload_data = $this->upload->data();

			# create thumb small
			$this->create_img_thumb(
				$this->get_path_image($upload_data['file_name']),
				'_small',
				'50',
				'50'
			);

			# create thumb medium
			$this->create_img_thumb(
				$this->get_path_image($upload_data['file_name']),
				'_medium',
				'150',
				'150'
			);

			$foto = $upload_data['file_name'];
		} else {
			$foto = null;
		}

		# simpan data siswa
		$karyawan_id = $this->KaryawanModel->create(
			$lokasi_tugas_id,
			$sub_bagian_id,
			$nip,
			$nama,
			$jenis_kelamin,
			$tempat_lahir,
			$tgl_lahir,
			$alamat,
			$foto,
			$status_id,
			$status_kawin,
			$golongan_darah,
			$jabatan_id,
			$departemen_id,
			$golongan_id,
			$pangkat_id,
			$tipe_karyawan_id,
			$telp,
			$email,
			$no_hp,
			$no_npwp,
			$no_kk,
			$no_ktp,
			$no_rek_bank,
			$nama_bank,
			$no_bpjs,
			$no_bpjs_ks,
			$tgl_masuk,
			$tgl_keluar,
			$tgl_hbs_kontrak,
			$status_pajak,
			$agama,
			$is_jht,
			$is_jp,
			$is_jks,
			$this->user_id

		);

		# simpan role
		// $role      = json_decode($this->post('role_id', TRUE));
		// $this->KaryawanModel->delete_role($karyawan_id);
		// foreach ($role  as $post_role) {
		// 	if (!empty($post_role)) {
		// 		$this->KaryawanModel->insert_role($karyawan_id, $post_role);
		// 	}
		// }
		// var_dump($this->post('details', TRUE));
		// return;
		$karyawandetail      = json_decode($this->post('details', TRUE));
		// var_dump($karyawandetail);
		$this->db->where('karyawan_id', $karyawan_id);
		$this->db->delete('payroll_riwayat_keluarga');
		$keluarga = $karyawandetail->keluargaItems;

		foreach ($keluarga as $key => $value) {
			$this->db->insert("payroll_riwayat_keluarga", array(
				'karyawan_id' => $karyawan_id,
				'nama' => $value->nama_keluarga,
				'tempat_lahir' => $value->tempat_lahir_keluarga,
				'tgl_lahir' => $value->tanggal_lahir_keluarga,
				'status' => $value->status_keluarga
			));
		}
		$this->db->where('karyawan_id', $karyawan_id);
		$this->db->delete('payroll_riwayat_bahasa');
		$bahasa = $karyawandetail->bahasaItems;

		foreach ($bahasa as $key => $value) {
			$this->db->insert("payroll_riwayat_bahasa", array(
				'karyawan_id' => $karyawan_id,
				'bahasa' => $value->bahasa,
				'kemampuan_bicara' => $value->kemampuan_bicara

			));
		}

		$this->db->where('karyawan_id', $karyawan_id);
		$this->db->delete('payroll_riwayat_pendidikan');
		$pendidikan = $karyawandetail->pendidikanItems;
		foreach ($pendidikan as $key => $value) {
			$this->db->insert("payroll_riwayat_pendidikan", array(
				'karyawan_id' => $karyawan_id,
				'nama_sekolah' => $value->nama_sekolah,
				'lokasi' => $value->lokasi_sekolah,
				'jurusan' => $value->jurusan,
				'no_ijazah' => $value->no_ijazah,
				'tgl_ijazah' => $value->tgl_ijazah,
			));
		}
		$this->db->where('karyawan_id', $karyawan_id);
		$this->db->delete('payroll_riwayat_jabatan');
		$jabatan = $karyawandetail->jabatanItems;
		foreach ($jabatan as $key => $value) {
			$this->db->insert("payroll_riwayat_jabatan", array(
				'lokasi_tugas_id' => $value->lokasi_tugas_id->id,
				'sub_bagian_id' => $value->sub_bagian_id->id,
				'id_tipe_karyawan' => $value->id_tipe_karyawan->id,
				'karyawan_id' => $karyawan_id,
				'jabatan_id' => $value->riwayat_jabatan->id,
				'tmt' => $value->tmt_jabatan,
				'selesai_tugas' => $value->selesai_tugas_jabatan,
				'status' => $value->status_jabatan
			));
		}
		$this->db->where('karyawan_id', $karyawan_id);
		$this->db->delete('payroll_riwayat_kepangkatan');
		$pangkat = $karyawandetail->pangkatItems;
		foreach ($pangkat as $key => $value) {
			$this->db->insert("payroll_riwayat_kepangkatan", array(
				'karyawan_id' => $karyawan_id,
				'pangkat_id' => $value->riwayat_pangkat->id,
				'golongan_id' => $value->riwayat_golongan->id,
				'tmt' => $value->tmt_pangkat,
				'status' => $value->status_pangkat
			));
		}

		$this->db->where('karyawan_id', $karyawan_id);
		$this->db->delete('payroll_riwayat_penghargaan');
		$penghargaan = $karyawandetail->penghargaanItems;
		foreach ($penghargaan as $key => $value) {
			$this->db->insert("payroll_riwayat_penghargaan", array(
				'karyawan_id' => $karyawan_id,
				'nama_penghargaan' => $value->nama_penghargaan,
				'tahun' => $value->tahun_penghargaan,
				'instansi' => $value->instansi_penghargaan,
			));
		}
		$this->db->where('karyawan_id', $karyawan_id);
		$this->db->delete('payroll_riwayat_hukuman');
		$hukuman = $karyawandetail->hukumanItems;
		foreach ($hukuman as $key => $value) {
			$this->db->insert("payroll_riwayat_hukuman", array(
				'karyawan_id' => $karyawan_id,
				'jenis_hukuman' => $value->jenis_hukuman,
				'no_sk' => $value->nosk_hukuman,
				'no_pemulihan' => $value->nosk_pemulihan,
				'tgl_sk' => $value->tglsk_hukuman,
				'tgl_pemulihan' => $value->tglsk_pemulihan,
			));
		}


		$this->db->where('karyawan_id', $karyawan_id);
		$this->db->delete('payroll_riwayat_pelatihan');
		$pelatihan = $karyawandetail->pelatihanItems;
		foreach ($pelatihan as $key => $value) {
			$this->db->insert("payroll_riwayat_pelatihan", array(
				'karyawan_id' => $karyawan_id,
				'nama_pelatihan' => $value->nama_pelatihan,
				'status' => $value->status
			));
		}

		$this->db->where('karyawan_id', $karyawan_id);
		$this->db->delete('payroll_riwayat_pengalaman');
		$pengalaman = $karyawandetail->pengalamanItems;
		foreach ($pengalaman as $key => $value) {
			$this->db->insert("payroll_riwayat_pengalaman", array(
				'karyawan_id' => $karyawan_id,
				'perusahaan' => $value->perusahaan,
				'mulai' => $value->mulai,
				'akhir' => $value->akhir,
				'status' => $value->status,
			));
		}

		$this->db->where('karyawan_id', $karyawan_id);
		$this->db->delete('payroll_riwayat_keahlian');
		$keahlian = $karyawandetail->keahlianItems;
		foreach ($keahlian as $key => $value) {
			$this->db->insert("payroll_riwayat_keahlian", array(
				'karyawan_id' => $karyawan_id,
				'nama_keahlian' => $value->nama_keahlian,
				'status' => $value->status
			));
		}


		// # simpan data login
		// $this->LoginModel->create(
		// 	$username,
		// 	$password,
		// 	null,
		// 	$karyawan_id,
		// 	$is_admin
		// );
		if (!empty($karyawan_id)) {
			$this->set_response(array("status" => "OK", "data" => $karyawan_id), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => null), REST_Controller::HTTP_OK);
		}

		// } else {
		// 	$upload_data = $this->upload->data();
		// 	if (!empty($upload_data) and is_file(get_path_image($upload_data['file_name']))) {
		// 		unlink(get_path_image($upload_data['file_name']));
		// 	}
		// }
	}

	public	function index_put($segment_3 = '')
	{

		$Karyawan_id       = (int)$segment_3;
		$retrieve_Karyawan = $this->KaryawanModel->retrieve($Karyawan_id, null);
		if (empty($retrieve_Karyawan)) {
			$this->set_response(array("status" => "NOT OK", "data" => "NOT FOUND Karyawan"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$retrieve_login = $this->LoginModel->retrieve(null, null, null, null, $retrieve_Karyawan['id']);
		//$retrieve_Karyawan['is_admin'] = $retrieve_login['is_admin'];
		$lokasi_tugas_id      = $this->put('lokasi_tugas_id', TRUE);
		$sub_bagian_id      = $this->put('sub_bagian_id', TRUE);
		$nip           = $this->put('nip', TRUE);
		$nama          = $this->put('nama', TRUE);
		$jenis_kelamin = $this->put('jenis_kelamin', TRUE);
		$tempat_lahir  = $this->put('tempat_lahir', TRUE);
		$tgl_lahir     = $this->put('tgl_lahir', TRUE);
		$alamat        = $this->put('alamat', TRUE);
		$is_admin        = $this->put('is_admin', TRUE);
		$status_id        = $this->put('status_id', TRUE);
		$status_kawin = $this->put('status_pernikahan', TRUE);
		$golongan_darah = $this->put('golongan_darah', TRUE);
		$jabatan_id    =  $this->put('id_jabatan', TRUE);
		$departemen_id    = $this->put('id_departemen', TRUE);
		$golongan_id    = $this->put('id_golongan', TRUE);
		$pangkat_id    = $this->put('id_pangkat', TRUE);
		$tipe_karyawan_id   = $this->put('id_tipe_karyawan', TRUE);
		$telp   = $this->put('telp', TRUE);
		$email   = $this->put('email', TRUE);
		$no_hp   = $this->put('no_hp', TRUE);
		$no_npwp   = $this->put('no_npwp', TRUE);
		$no_kk   = $this->put('no_kk', TRUE);
		$no_ktp   = $this->put('no_ktp', TRUE);
		$no_rek_bank   = $this->put('no_rek_bank', TRUE);
		$nama_bank   = $this->put('nama_bank', TRUE);
		$no_bpjs   = $this->put('no_bpjs', TRUE);
		$no_bpjs_ks   = $this->put('no_bpjs_ks', TRUE);
		$tgl_masuk   = $this->put('tgl_masuk', TRUE);
		$tgl_keluar   = $this->put('tgl_keluar', TRUE);
		$tgl_hbs_kontrak   = $this->put('tgl_hbs_kontrak', TRUE);
		$status_pajak = $this->put('status_pajak', TRUE);
		$agama = $this->put('agama', TRUE);
		$is_jht = $this->put('is_jht', TRUE);
		$is_jp = $this->put('is_jp', TRUE);
		$is_jks = $this->put('is_jks', TRUE);
		# update Karyawan
		$this->KaryawanModel->update(
			$retrieve_Karyawan['id'],
			$lokasi_tugas_id,
			$sub_bagian_id,
			$nip,
			$nama,
			$jenis_kelamin,
			$tempat_lahir,
			$tgl_lahir,
			$alamat,
			$retrieve_Karyawan['foto'],
			$status_id,
			$status_kawin,
			$golongan_darah,
			$jabatan_id,
			$departemen_id,
			$golongan_id,
			$pangkat_id,
			$tipe_karyawan_id,
			$telp,
			$email,
			$no_hp,
			$no_npwp,
			$no_kk,
			$no_ktp,
			$no_rek_bank,
			$nama_bank,
			$no_bpjs,
			$no_bpjs_ks,
			$tgl_masuk,
			$tgl_keluar,
			$tgl_hbs_kontrak,
			$status_pajak,
			$agama,
			$is_jht,
			$is_jp,
			$is_jks,
			$this->user_id
		);

		# simpan role
		// $role      = ($this->put('role_id', TRUE));
		// $this->KaryawanModel->delete_role($Karyawan_id);
		// foreach ($role  as $post_role) {
		// 	if (!empty($post_role)) {
		// 		$this->KaryawanModel->insert_role($Karyawan_id, $post_role);
		// 	}
		// }
		# update login
		// $this->LoginModel->update(
		// 	$retrieve_login['id'],
		// 	$retrieve_login['username'],
		// 	null,
		// 	$Karyawan_id,
		// 	$is_admin,
		// 	null
		// );

		// if ($retrieve_Karyawan['status_id'] == 0 && $status == 1) {
		// 	kirim_email_approve_Karyawan($Karyawan_id);
		// }

		$this->set_response(array("status" => "OK", "data" => $Karyawan_id), REST_Controller::HTTP_CREATED);
	}

	function simpan_riwayat_keluarga_post($karyawan_id)
	{
		$karyawandetail      = json_decode($this->post('details', TRUE));
		// var_dump($karyawandetail);
		$this->db->where('karyawan_id', $karyawan_id);
		$this->db->delete('payroll_riwayat_keluarga');
		$keluarga = $karyawandetail->keluargaItems;

		foreach ($keluarga as $key => $value) {
			$this->db->insert("payroll_riwayat_keluarga", array(
				'karyawan_id' => $karyawan_id,
				'nama' => $value->nama_keluarga,
				'tempat_lahir' => $value->tempat_lahir_keluarga,
				'tgl_lahir' => $value->tanggal_lahir_keluarga,
				'status' => $value->status_keluarga
			));
		}
		$this->set_response(array("status" => "OK", "data" => $karyawan_id), REST_Controller::HTTP_CREATED);
	}
	function simpan_riwayat_pendidikan_post($karyawan_id)
	{
		$karyawandetail      = json_decode($this->post('details', TRUE));
		// var_dump($karyawandetail);

		$this->db->where('karyawan_id', $karyawan_id);
		$this->db->delete('payroll_riwayat_pendidikan');
		$pendidikan = $karyawandetail->pendidikanItems;
		foreach ($pendidikan as $key => $value) {
			$this->db->insert("payroll_riwayat_pendidikan", array(
				'karyawan_id' => $karyawan_id,
				'nama_sekolah' => $value->nama_sekolah,
				'lokasi' => $value->lokasi_sekolah,
				'jurusan' => $value->jurusan,
				'no_ijazah' => $value->no_ijazah,
				'tgl_ijazah' => $value->tgl_ijazah,
			));
		}
		$this->set_response(array("status" => "OK", "data" => $karyawan_id), REST_Controller::HTTP_CREATED);
	}
	function simpan_riwayat_bahasa_post($karyawan_id)
	{
		$karyawandetail      = json_decode($this->post('details', TRUE));
		// var_dump($karyawandetail);
		$this->db->where('karyawan_id', $karyawan_id);
		$this->db->delete('payroll_riwayat_bahasa');
		$bahasa = $karyawandetail->bahasaItems;

		foreach ($bahasa as $key => $value) {
			$this->db->insert("payroll_riwayat_bahasa", array(
				'karyawan_id' => $karyawan_id,
				'bahasa' => $value->bahasa,
				'kemampuan_bicara' => $value->kemampuan_bicara

			));
		}
		$this->set_response(array("status" => "OK", "data" => $karyawan_id), REST_Controller::HTTP_CREATED);
	}
	function simpan_riwayat_jabatan_post($karyawan_id)
	{
		$karyawandetail      = json_decode($this->post('details', TRUE));
		//  var_dump($karyawandetail);
		$this->db->where('karyawan_id', $karyawan_id);
		$this->db->delete('payroll_riwayat_jabatan');
		$jabatan = $karyawandetail->jabatanItems;
		foreach ($jabatan as $key => $value) {
			$this->db->insert("payroll_riwayat_jabatan", array(
				'lokasi_tugas_id' => $value->lokasi_tugas_id->id,
				'sub_bagian_id' => $value->sub_bagian_id->id,
				'id_tipe_karyawan' => $value->id_tipe_karyawan->id,
				'karyawan_id' => $karyawan_id,
				'jabatan_id' => $value->riwayat_jabatan->id,
				'tmt' => $value->tmt_jabatan,
				'selesai_tugas' => $value->selesai_tugas_jabatan,
				'status' => $value->status_jabatan
			));
		}
		$this->set_response(array("status" => "OK", "data" => $karyawan_id), REST_Controller::HTTP_CREATED);
	}
	function simpan_riwayat_pangkat_post($karyawan_id)
	{
		$karyawandetail      = json_decode($this->post('details', TRUE));
		$this->db->where('karyawan_id', $karyawan_id);
		$this->db->delete('payroll_riwayat_kepangkatan');
		$pangkat = $karyawandetail->pangkatItems;
		foreach ($pangkat as $key => $value) {
			$this->db->insert("payroll_riwayat_kepangkatan", array(
				'karyawan_id' => $karyawan_id,
				'pangkat_id' => $value->riwayat_pangkat->id,
				'golongan_id' => $value->riwayat_golongan->id,
				'tmt' => $value->tmt_pangkat,
				'status' => $value->status_pangkat
			));
		}
		$this->set_response(array("status" => "OK", "data" => $karyawan_id), REST_Controller::HTTP_CREATED);
	}
	function simpan_riwayat_hukuman_post($karyawan_id)
	{
		$karyawandetail      = json_decode($this->post('details', TRUE));

		$this->db->where('karyawan_id', $karyawan_id);
		$this->db->delete('payroll_riwayat_hukuman');
		$hukuman = $karyawandetail->hukumanItems;
		foreach ($hukuman as $key => $value) {
			$this->db->insert("payroll_riwayat_hukuman", array(
				'karyawan_id' => $karyawan_id,
				'jenis_hukuman' => $value->jenis_hukuman,
				'no_sk' => $value->nosk_hukuman,
				'no_pemulihan' => $value->nosk_pemulihan,
				'tgl_sk' => $value->tglsk_hukuman,
				'tgl_pemulihan' => $value->tglsk_pemulihan,
			));
		}
		$this->set_response(array("status" => "OK", "data" => $karyawan_id), REST_Controller::HTTP_CREATED);
	}
	function simpan_riwayat_penghargaan_post($karyawan_id)
	{
		$karyawandetail      = json_decode($this->post('details', TRUE));

		$this->db->where('karyawan_id', $karyawan_id);
		$this->db->delete('payroll_riwayat_penghargaan');
		$penghargaan = $karyawandetail->penghargaanItems;
		foreach ($penghargaan as $key => $value) {
			$this->db->insert("payroll_riwayat_penghargaan", array(
				'karyawan_id' => $karyawan_id,
				'nama_penghargaan' => $value->nama_penghargaan,
				'tahun' => $value->tahun_penghargaan,
				'instansi' => $value->instansi_penghargaan,
			));
		}
		$this->set_response(array("status" => "OK", "data" => $karyawan_id), REST_Controller::HTTP_CREATED);
	}
	function simpan_riwayat_pelatihan_post($karyawan_id)
	{
		$karyawandetail      = json_decode($this->post('details', TRUE));

		$this->db->where('karyawan_id', $karyawan_id);
		$this->db->delete('payroll_riwayat_pelatihan');
		$pelatihan = $karyawandetail->pelatihanItems;
		foreach ($pelatihan as $key => $value) {
			$this->db->insert("payroll_riwayat_pelatihan", array(
				'karyawan_id' => $karyawan_id,
				'nama_pelatihan' => $value->nama_pelatihan,
				'status' => $value->status
			));
		}
		$this->set_response(array("status" => "OK", "data" => $karyawan_id), REST_Controller::HTTP_CREATED);
	}
	function simpan_riwayat_pengalaman_post($karyawan_id)
	{
		$karyawandetail      = json_decode($this->post('details', TRUE));

		$this->db->where('karyawan_id', $karyawan_id);
		$this->db->delete('payroll_riwayat_pengalaman');
		$pengalaman = $karyawandetail->pengalamanItems;
		foreach ($pengalaman as $key => $value) {
			$this->db->insert("payroll_riwayat_pengalaman", array(
				'karyawan_id' => $karyawan_id,
				'perusahaan' => $value->perusahaan,
				'mulai' => $value->mulai,
				'akhir' => $value->akhir,
				'status' => $value->status,
			));
		}
		$this->set_response(array("status" => "OK", "data" => $karyawan_id), REST_Controller::HTTP_CREATED);
	}
	function simpan_riwayat_keahlian_post($karyawan_id)
	{
		$karyawandetail      = json_decode($this->post('details', TRUE));

		$this->db->where('karyawan_id', $karyawan_id);
		$this->db->delete('payroll_riwayat_keahlian');
		$keahlian = $karyawandetail->keahlianItems;
		foreach ($keahlian as $key => $value) {
			$this->db->insert("payroll_riwayat_keahlian", array(
				'karyawan_id' => $karyawan_id,
				'nama_keahlian' => $value->nama_keahlian,
				'status' => $value->status
			));
		}
		$this->set_response(array("status" => "OK", "data" => $karyawan_id), REST_Controller::HTTP_CREATED);
	}

	function getAll_get()
	{

		$retrieve = $this->KaryawanModel->retrieve_all_Karyawan();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => []), REST_Controller::HTTP_OK);
		}
	}
	function getAllAktif_get()
	{

		$retrieve = $this->KaryawanModel->retrieve_all_Karyawan_aktif();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => []), REST_Controller::HTTP_OK);
		}
	}
	function getAllFaceRegistration_get()
	{

		$this->db->select('a.*, a.id AS id, b.nama AS lokasi_tugas_nama, c.nama AS sub_bagian_nama, d.gapok AS gapok');
		$this->db->from('karyawan a');
		$this->db->join('gbm_organisasi b', 'a.lokasi_tugas_id = b.id', 'left');
		$this->db->join('gbm_organisasi c', 'a.sub_bagian_id = c.id', 'left');
		$this->db->join('payroll_karyawan_gaji d', 'a.id = d.karyawan_id', 'left');
		$this->db->where('b.nama','SAINTEK');
		$retrieve = $this->db->get()->result_array();;

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => []), REST_Controller::HTTP_OK);
		}
	}
	function getAllAktifEstate_get()
	{

		$retrieve = $this->KaryawanModel->retrieve_all_Karyawan_aktif_estate();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => []), REST_Controller::HTTP_OK);
		}
	}
	function getByLokasiTugas_get($segment_3 = '')
	{

		$org_id = $segment_3;
		$retrieve = $this->KaryawanModel->retrieve_by_lokasi_tugas($org_id);

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => []), REST_Controller::HTTP_OK);
		}
	}
	function getAllAktifKaryawanByDivisi_get($segment_3 = '', $segment_4 = '')
	{

		$org_id = $segment_3;
		$periode = $segment_4;
		if (!($org_id && $periode)) {
			$this->set_response(array("status" => "NOT OK", "data" => []), REST_Controller::HTTP_OK);
			return;
		}


		$q = "SELECT a.*,b.nama as sub_bagian_nama FROM karyawan a LEFT JOIN gbm_organisasi b ON a.sub_bagian_id=b.id
		WHERE (tgl_keluar IS NULL OR left(tgl_keluar,7) >='" . substr($periode, 0, 7) . "' OR tgl_keluar='0000-00-00')
		and a.sub_bagian_id=" . $org_id . " order by a.nama";
		$retrieve = $this->db->query($q)->result_array();
		// $this->set_response(array("status" => "OK", "data" => $q), REST_Controller::HTTP_OK);
		// return;
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => []), REST_Controller::HTTP_OK);
		}
	}
	function printDetail_get($segment_3 = '')
	{

		$karyawan_id = $segment_3;
		$retrieve = $this->KaryawanModel->retrieve($karyawan_id, null, null);
		$retrieve['jabatan'] = $this->db->query("select * from payroll_jabatan where id=" . $retrieve['jabatan_id'] . "")->row_array();
		$retrieve['lokasi_tugas'] = $this->db->query("select * from gbm_organisasi where id=" . $retrieve['lokasi_tugas_id'] . "")->row_array();
		$retrieve['sub_bagian'] = $this->db->query("select * from gbm_organisasi where id=" . $retrieve['sub_bagian_id'] . "")->row_array();
		$retrieve['departemen'] = $this->db->query("select * from payroll_department where id=" . $retrieve['departemen_id'] . "")->row_array();
		$retrieve['pangkat'] = $this->db->query("select * from payroll_pangkat where id=" . $retrieve['pangkat_id'] . "")->row_array();
		$retrieve['golongan'] = $this->db->query("select * from payroll_golongan where id=" . $retrieve['golongan_id'] . "")->row_array();
		$retrieve['tipe_karyawan'] = $this->db->query("select * from payroll_tipe_karyawan where id=" . $retrieve['tipe_karyawan_id'] . "")->row_array();

		$retrieve['riwayat_keluarga'] = $this->db->query("select * from payroll_riwayat_keluarga where karyawan_id=" . $karyawan_id . "")->result_array();
		$retrieve['riwayat_bahasa'] = $this->db->query("select * from payroll_riwayat_bahasa where karyawan_id=" . $karyawan_id . "")->result_array();
		$retrieve['riwayat_jabatan'] = $this->db->query("select a.*,b.nama as jabatan, c.nama as lokasi_tugas, cc.nama as sub_bagian, ccc.nama as tipe_karyawan from payroll_riwayat_jabatan a inner join payroll_jabatan b on a.jabatan_id=b.id left join gbm_organisasi c on a.lokasi_tugas_id=c.id left join gbm_organisasi cc on a.sub_bagian_id=cc.id left join payroll_tipe_karyawan ccc on a.id_tipe_karyawan=ccc.id where karyawan_id=" . $karyawan_id . "")->result_array();
		$retrieve['riwayat_pangkat'] = $this->db->query("select a.*,b.nama as pangkat,c.nama as golongan from payroll_riwayat_kepangkatan a inner join payroll_pangkat b on a.pangkat_id=b.id
		 inner join payroll_golongan c on a.golongan_id=c.id  where karyawan_id=" . $karyawan_id . "")->result_array();
		$retrieve['riwayat_penghargaan'] = $this->db->query("select * from payroll_riwayat_penghargaan where karyawan_id=" . $karyawan_id . "")->result_array();
		$retrieve['riwayat_hukuman'] = $this->db->query("select * from payroll_riwayat_hukuman where karyawan_id=" . $karyawan_id . "")->result_array();
		$retrieve['riwayat_pendidikan'] = $this->db->query("select * from payroll_riwayat_pendidikan where karyawan_id=" . $karyawan_id . "")->result_array();
		$retrieve['riwayat_keahlian'] = $this->db->query("select * from payroll_riwayat_keahlian where karyawan_id=" . $karyawan_id . "")->result_array();
		$retrieve['riwayat_pengalaman'] = $this->db->query("select * from payroll_riwayat_pengalaman where karyawan_id=" . $karyawan_id . "")->result_array();
		$retrieve['riwayat_pelatihan'] = $this->db->query("select * from payroll_riwayat_pelatihan where karyawan_id=" . $karyawan_id . "")->result_array();

		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		// var_dump($retrieve);exit();
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

  </style>
";

		// 		$html = $html . '<div class="row">
		// <div class="span12">
		//     <br>
		//     <div class="kop-print">


		//         <div class="kop-nama">' . get_pengaturan('nama-company', 'value') . '</div>
		//         <div class="kop-info">Alamat : ' . get_pengaturan('alamat', 'value') . ', Telepon :' . get_pengaturan('telp', 'value') . '</div>
		//     </div>
		//     <hr class="kop-print-hr">
		// </div>
		// </div>';

		//var_dump(file_get_contents(get_url_image($retrieve['foto'])));exit();
		$html = $html . "
         <h2>Biodata Karyawan</h2>
		 <h3>Profile</h3>";
		if (!empty($retrieve['foto'])) {
			$html = $html . "
		 <img src=data:image/jpg;base64," . base64_encode(file_get_contents(get_url_image($retrieve['foto']))) . "
		 width='200' height='250'>";
		}

		$html = $html . "
		 <table  width='100%' style='border-collapse: collapse;'>
        <tr>
            <td width='100px'>Nip</td>
            <td width='10px'>:</td>
            <td>" . $retrieve['nip'] . "</td>
        </tr>
        <tr>
            <td width='100px'>Nama</td>
            <td width='10px'>:</td>
            <td>" . $retrieve['nama'] . "</td>
        </tr>
        <tr>
            <td>Jenis kelamin</td>
            <td width='10px'>:</td>
            <td>" . $retrieve['jenis_kelamin'] . "</td>
        </tr>
		<tr>
            <td>Tempat,Tgl Lahir</td>
            <td width='10px'>:</td>
            <td>" . $retrieve['tempat_lahir'] . "," . substr($retrieve['tgl_lahir'], 0, 10) . "</td>
        </tr>
		<tr>
            <td>Agama</td>
            <td width='10px'>:</td>
            <td>" . $retrieve['agama'] . "</td>
        </tr>
		<tr>
            <td>Alamat</td>
            <td width='10px'>:</td>
            <td>" . $retrieve['alamat'] . "</td>
        </tr>
		<tr>
            <td>Telp</td>
            <td width='10px'>:</td>
            <td>" . $retrieve['telp'] . "</td>
        </tr>
		<tr>
            <td>No Hp</td>
            <td width='10px'>:</td>
            <td>" . $retrieve['no_hp'] . "</td>
        </tr>
		<tr>
            <td>Email</td>
            <td width='10px'>:</td>
            <td>" . $retrieve['email'] . "</td>
        </tr>
		
		<tr>
            <td>Status</td>
            <td width='10px'>:</td>
            <td>" . $retrieve['status_kawin'] . "</td>
        </tr>
		<tr>
            <td>Gol Darah</td>
            <td width='10px'>:</td>
            <td>" . $retrieve['golongan_darah'] . "</td>
        </tr>
		<tr>
            <td>Status Pajak</td>
            <td width='10px'>:</td>
            <td>" . $retrieve['status_pajak'] . "</td>
        </tr>
		<tr>
            <td>Tipe Karyawan</td>
            <td width='10px'>:</td>
            <td>" . $retrieve['tipe_karyawan']['nama'] . "</td>
        </tr>
		<tr>
            <td>Jabatan</td>
            <td width='10px'>:</td>
            <td>" . $retrieve['jabatan']['nama'] . "</td>
        </tr>
		<tr>
            <td>Lokasi Tugas</td>
            <td width='10px'>:</td>
            <td>" . $retrieve['lokasi_tugas']['nama'] . "</td>
        </tr>
		<tr>
            <td>Sub bagian</td>
            <td width='10px'>:</td>
            <td>" . $retrieve['sub_bagian']['nama'] . "</td>
        </tr>
		<tr>
            <td>Deparment</td>
            <td width='10px'>:</td>
            <td>" . $retrieve['departemen']['nama'] . "</td>
        </tr>
		<tr>
            <td>Pangkat</td>
            <td width='10px'>:</td>
            <td>" . $retrieve['pangkat']['nama'] . "</td>
        </tr>
		<tr>
            <td>Golongan</td>
            <td width='10px'>:</td>
            <td>" . $retrieve['golongan']['nama'] . "</td>
        </tr>
		<tr>
            <td>No BPJS</td>
            <td width='10px'>:</td>
            <td>" . $retrieve['no_bpjs'] . "</td>
        </tr>
		<tr>
            <td>No BPJS</td>
            <td width='10px'>:</td>
            <td>" . $retrieve['no_bpjs_ks'] . "</td>
        </tr>
		<tr>
            <td>No KTP</td>
            <td width='10px'>:</td>
            <td>" . $retrieve['no_ktp'] . "</td>
        </tr>
		<tr>
            <td>No KK</td>
            <td width='10px'>:</td>
            <td>" . $retrieve['no_kk'] . "</td>
        </tr>
		<tr>
            <td>No NPWP</td>
            <td width='10px'>:</td>
            <td>" . $retrieve['no_npwp'] . "</td>
        </tr>
		<tr>
            <td>Nama Bank</td>
            <td width='10px'>:</td>
            <td>" . $retrieve['nama_bank'] . "</td>
        </tr>
		<tr>
            <td>No Rek Bank</td>
            <td width='10px'>:</td>
            <td>" . $retrieve['no_rek_bank'] . "</td>
        </tr>
        </table>
        ";

		// var_dump($html);exit();
		$html = $html . "
		<h3>Keluarga</h3>
		 <table  border='1' width='100%' style='border-collapse: collapse;'>
		<tr>
		<th >Nama</th>
		<th>Status</th>
		<th>Tempat,Tgl Lahir</th>
		</tr>";
		$no = 0;
		foreach ($retrieve['riwayat_keluarga'] as $key => $data) {
			$no++;
			$html = $html . " <tr>
			<td>" . $data['nama'] . "</td>
            <td>" . $data['status'] . "</td>
            <td>" . $data['tempat_lahir'] . "," . substr($data['tgl_lahir'], 0, 10) . "</td>    
            </tr>";
		}

		$html = $html . " 
		</table>
      
        ";

		$html = $html . "
		<h3>Pendidikan</h3>
		 <table  border='1' width='100%' style='border-collapse: collapse;'>
		<tr>
		<th >Institusi</th>
		<th>Lokasi</th>
		<th>Jurusan</th>
		<th>No Ijazah</th>
		<th>Tgl Ijazah</th>
		
		</tr>";
		$no = 0;
		foreach ($retrieve['riwayat_pendidikan'] as $key => $data) {
			$no++;
			$html = $html . " <tr>
			<td>" . $data['nama_sekolah'] . "</td>
            <td>" . $data['lokasi'] . "</td>
            <td>" . $data['jurusan'] . "</td>    
			<td>" . $data['no_ijazah'] . "</td>    
			<td>" . substr($data['tgl_ijazah'], 0, 10)  . "</td>    
            </tr>";
		}

		$html = $html . " 
		</table>
      
        ";

		$html = $html . "
		<h3>Keahlian</h3>
		 <table  border='1' width='100%' style='border-collapse: collapse;'>
		<tr>
		<th >Nama</th>
		<th>Ket</th>
		</tr>";
		$no = 0;
		foreach ($retrieve['riwayat_keahlian'] as $key => $data) {
			$no++;
			$html = $html . " <tr>
			<td>" . $data['nama_keahlian'] . "</td>
            <td>" . $data['status'] . "</td>
            </tr>";
		}

		$html = $html . " 
		</table>
      
        ";

		$html = $html . "
		<h3>Pengalaman</h3>
		 <table  border='1' width='100%' style='border-collapse: collapse;'>
		<tr>
		<th >Perusahaan</th>
		<th>Mulai</th>
		<th>S.d</th>
		<th>Ket</th>
		</tr>";
		$no = 0;
		foreach ($retrieve['riwayat_pengalaman'] as $key => $data) {
			$no++;
			$html = $html . " <tr>
			<td>" . $data['perusahaan'] . "</td>
            <td>" . $data['mulai'] . "</td>
            <td>" . $data['akhir'] . "</td>    
			<td>" . $data['status'] . "</td>    
	
            </tr>";
		}
		$html = $html . " 
		</table>
        ";

		$html = $html . "
		<h3>Pelatihan</h3>
		 <table  border='1' width='100%' style='border-collapse: collapse;'>
		<tr>
		<th >Nama Pelatihan</th>
		<th>Ket</th>
	
		</tr>";
		$no = 0;
		foreach ($retrieve['riwayat_pelatihan'] as $key => $data) {
			$no++;
			$html = $html . " <tr>
			<td>" . $data['nama_pelatihan'] . "</td>
            <td>" . $data['status'] . "</td> 
            </tr>";
		}

		$html = $html . " 
		</table>
      
        ";

		$html = $html . "
		<h3>Bahasa</h3>
		 <table  border='1' width='100%' style='border-collapse: collapse;'>
		<tr>
		<th >Bahasa</th>
		<th>Kemampuan Bicara</th>
		</tr>";
		$no = 0;
		foreach ($retrieve['riwayat_bahasa'] as $key => $data) {
			$no++;
			$html = $html . " <tr>
			<td>" . $data['bahasa'] . "</td>
            <td>" . $data['kemampuan_bicara'] . "</td>
            </tr>";
		}

		$html = $html . " 
		</table>
      
        ";

		$html = $html . "
		<h3>Kepangkatan</h3>
		 <table  border='1' width='100%' style='border-collapse: collapse;'>
		<tr>
		<th >Pangkat</th>
		<th>Golongan</th>
		<th>TMT</th>
		<th>Status</th>
		</tr>";
		$no = 0;
		foreach ($retrieve['riwayat_pangkat'] as $key => $data) {
			$no++;
			$html = $html . " <tr>
			<td>" . $data['pangkat'] . "</td>
			<td>" . $data['golongan'] . "</td>
			<td>" . substr($data['tmt'], 0, 10) . "</td>
            <td>" . $data['status'] . "</td>
            </tr>";
		}

		$html = $html . " 
		</table>
      
        ";

		$html = $html . "
		<h3>Jabatan</h3>
		 <table  border='1' width='100%' style='border-collapse: collapse;'>
		<tr>
		<th>Lokasi Tugas</th>
		<th>Sub Bagian</th>
		<th>Tipe Karyawan</th>
		<th>Jabatan</th>
		<th>Mulai Tugas</th>
		<th>Selesai Tugas</th>
		<th>Status</th>
		</tr>";
		$no = 0;
		foreach ($retrieve['riwayat_jabatan'] as $key => $data) {
			$no++;
			$html = $html . " <tr>
			<td>" . $data['lokasi_tugas'] . "</td>
			<td>" . $data['sub_bagian'] . "</td>
			<td>" . $data['tipe_karyawan'] . "</td>
			<td>" . $data['jabatan'] . "</td>
            <td>" . substr($data['tmt'], 0, 10) . "</td>
            <td>" . substr($data['selesai_tugas'], 0, 10) . "</td>    
			<td>" . $data['status'] . "</td>    
            </tr>";
		}

		$html = $html . " 
		</table>
      
        ";

		$html = $html . "
		<h3>Penghargaan</h3>
		 <table  border='1' width='100%' style='border-collapse: collapse;'>
		<tr>
		<th >Nama Penghargaan</th>
		<th>Tahun</th>
		<th>Instansi</th>
		</tr>";
		$no = 0;
		foreach ($retrieve['riwayat_penghargaan'] as $key => $data) {
			$no++;
			$html = $html . " <tr>
			<td>" . $data['nama_penghargaan'] . "</td>
            <td>" . $data['tahun'] . "</td>
            <td>" . $data['instansi'] . "</td>    
            </tr>";
		}

		$html = $html . " 
		</table>
      
        ";

		$html = $html . "
		<h3>Hukuman</h3>
		 <table  border='1' width='100%' style='border-collapse: collapse;'>
		<tr>
		<td >Jenis Hukuman</td>
		<th>No SK</th>
		<th>Tgl SK</th>
		<th>No Pemulihan</th>
		<th>Tgl Pemulihan</th>
		</tr>";
		$no = 0;
		foreach ($retrieve['riwayat_hukuman'] as $key => $data) {
			$no++;
			$html = $html . " <tr>
			<td>" . $data['jenis_hukuman'] . "</td>
            <td>" . $data['no_sk'] . "</td>
			<td>" . substr($data['tgl_sk'], 0, 10) . "</td>
			<td>" . $data['no_pemulihan'] . "</td>
            <td>" . substr($data['tgl_pemulihan'], 0, 10) . "</td>    
            </tr>";
		}

		$html = $html . " 
		</table>
      
        ";


		// print_r($html);exit;
		// $html = $this->load->view('table_report', $data, true);
		$filename = 'report_' . time();
		$this->pdfgenerator->generate($html, $filename, true, 'A4', 'portrait');
	}
	function laporan_karyawan_post()
	{

		// var_dump( $this->post());exit();
		$lokasi_id     = $this->post('lokasi_id', true);
		$sub_bagian_id     = $this->post('sub_bagian_id', true);
		$format_laporan     = $this->post('format_laporan', true);
		$status_id     = $this->post('status_id', true);
		// $lokasi_id     = 252;
		// $sub_bagian_id     = null;
		// $format_laporan     = 'pdf';
		// $status_id     = null;

		$where = "";
		$judul_lokasi = "Semua";
		$judul_sub_bagian = "Semua";
		$judul_status = "Semua";
		if (!empty($lokasi_id)) {
			$where = " and a.lokasi_tugas_id=" . $lokasi_id . " ";
			$lokasi   = $this->db->query("select * from gbm_organisasi where id=" . $lokasi_id . "")->row_array();
			$judul_lokasi = $lokasi['nama'];
		}
		if (!empty($sub_bagian_id)) {
			$where = $where . " and a.sub_bagian_id=" . $sub_bagian_id . " ";
			$lokasi   = $this->db->query("select * from gbm_organisasi where id=" . $sub_bagian_id . "")->row_array();
			$judul_sub_bagian = $lokasi['nama'];
		}
		if (!empty($status_id)) {
			if ($status_id == 'aktif') {
				$where = $where . " and (a.tgl_keluar IS NULL or a.tgl_keluar='0000-00-00') ";
				$judul_status = 'Aktif';
			} else {
				$where = $where . " and a.tgl_keluar IS NOT NULL and a.tgl_keluar!='0000-00-00' ";
				$judul_status = 'Non Aktif';
			}
		}
		$retrieveKaryawan = $this->db->query("select a.*,g.nama as lokasi_tugas,h.nama as sub_bagian, b.nama as departemen,c.nama as jabatan,d.nama as pangkat,e.nama as golongan,f.nama as tipe_karyawan
		from karyawan a left join payroll_department b on a.departemen_id=b.id 
	   left join payroll_jabatan c on a.jabatan_id=c.id 
	   left join payroll_pangkat d on a.pangkat_id=d.id 
	   left join payroll_golongan e on a.golongan_id=e.id 
	   left join payroll_tipe_karyawan f on a.tipe_karyawan_id=f.id
	   left join gbm_organisasi g on a.lokasi_tugas_id=g.id
	   left join gbm_organisasi h on a.sub_bagian_id=h.id

	   where 1=1 " . $where . "
	   order by a.nama
	  	")->result_array();

		// var_dump($results);
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report();
		} else {
			$html = get_header_report();
		}
		$html = $html . '
  <h2>Laporan Karyawan</h2>
  <h3>Lokasi  : ' . $judul_lokasi . '</h3>
  <h3>Sub Bagian  : ' . $judul_sub_bagian . '</h3>
  <h3>Status  : ' . $judul_status . '</h3>';

		$html = $html . ' <table   border="1" width="100%" style="border-collapse: collapse;">
			<thead>
				<tr>
				<th width="4%">No.</th>
				<th>NIP</th>
				<th>Nama</th>
				<th>Jns Kelamin</th>
				<th>Status</th>
				<th>Gol Darah</th>
				<th>Tempat,tgl lahir</th>
				<th>Alamat</th>
				<th>Telp</th>
				<th>No HP</th>
				<th>Email</th>
				<th>No KTP</th>
				<th>No KK</th>
				<th>No BPJS TK</th>
				<th>No BPJS KS</th>
				<th>No NPWP</th>
				<th>Nama Bank</th>
				<th>No Rek Bank</th>
				<th>Lokasi Tugas</th>
				<th>Sub Bagian</th>
				<th>Departemen</th>
				<th>Jabatan</th>
				<th>Pangkat/Golongan</th>
				<th>Tipe(Status)</th>
				<th>Tgl Masuk</th>
				<th>Tgl Keluar</th>
				<th>Status Pajak</th>
				</tr>
			</thead>
			<tbody>';


		$no = 0;
		$jumlah = 0;

		foreach ($retrieveKaryawan as $key => $m) {
			$no++;

			$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative;">
							' . ($no) . '

						</td>
						<td>
						' . $m['nip'] . ' 
							
						</td>
						<td>
						' . $m['nama'] . ' 
						
						</td>
						<td>
						' . $m['jenis_kelamin'] . ' 
						
						</td>
						<td>
							' . $m['status_kawin'] . ' 
						</td>
						<td>
							' . $m['golongan_darah'] . ' 
						</td>
						<td>
							' . $m['tempat_lahir'] . ', ' . $m['tgl_lahir'] . ' 
						</td>
						<td>
							' . $m['alamat'] . ' 
						</td>
						<td>
							' . $m['telp'] . ' 
						</td>
						<td>
							' . $m['no_hp'] . ' 
						</td>
						<td>
							' . $m['email'] . ' 
						</td>
						<td>
							' . $m['no_ktp'] . ' 
						</td>
						<td>
							' . $m['no_kk'] . ' 
						</td>
						<td>
							' . $m['no_bpjs'] . ' 
						</td>
						<td>
							' . $m['no_bpjs_ks'] . ' 
						</td>
						<td>
							' . $m['no_npwp'] . ' 
						</td>
						<td>
							' . $m['nama_bank'] . ' 
						</td>
						<td>
							' . $m['no_rek_bank'] . ' 
						</td>
						<td>
							' . $m['lokasi_tugas'] . ' 
						</td>
						<td>
							' . $m['sub_bagian'] . ' 
						</td>
						<td>
							' . $m['departemen'] . ' 
						</td>
						<td>
							' . $m['jabatan'] . ' 
						</td>
						<td>
							' . $m['pangkat'] . '/' . $m['golongan'] . ' 
						</td>
						<td>
							' . $m['tipe_karyawan'] . ' 
						</td>
						<td>
							' . $m['tgl_masuk'] . ' 
						</td>
						<td>
							' . $m['tgl_keluar'] . ' 
						</td>
						<td>
							' . $m['status_pajak'] . ' 
						</td>
						
						';

			$html = $html . '
												
					</tr>';
		}
		$html = $html . ' 	
						</tbody>
						</table>
						';
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
			$this->pdfgenerator->generate($html, $filename, true, 'A3', 'landscape');
		}
	}

	function edit_role($segment_3 = '', $segment_4 = '')
	{
		// # siswa tidak diijinkan
		// if (is_siswa()) {
		// 	exit('Akses ditolak');
		// }

		$status_id         = (int)$segment_3;
		$Karyawan_id       = (int)$segment_4;
		$retrieve_Karyawan = $this->KaryawanModel->retrieve($Karyawan_id);
		if (empty($retrieve_Karyawan)) {
			exit('Data Karyawan tidak ditemukan');
		}

		# jika sebagai Karyawan, hanya profilnya dia yang bisa diupdate
		// if (is_Karyawan() and get_sess_data('user', 'id') != $retrieve_Karyawan['id']) {
		// 	exit('Akses ditolak');
		// }

		$retrieve_login = $this->LoginModel->retrieve(null, null, null, null, $retrieve_Karyawan['id']);
		$retrieve_role = $this->KaryawanModel->retrieve_role($retrieve_Karyawan['id']);
		$retrieve_Karyawan['is_admin'] = $retrieve_login['is_admin'];

		$data['status_id']    = $status_id;
		$data['Karyawan_id']  = $Karyawan_id;
		$data['Karyawan']     = $retrieve_Karyawan;
		$data['role']     = array_column($retrieve_role, 'role');


		// var_dump(array_column( $retrieve_role, 'role'));exit;

		if ($this->form_validation->run('Karyawan/role') == TRUE) {
			$role      = $this->post('role', TRUE);
			$this->KaryawanModel->delete_role($retrieve_Karyawan['id']);
			foreach ($role  as $post_role) {

				if (!empty($post_role)) {

					$this->KaryawanModel->insert_role($retrieve_Karyawan['id'], $post_role);
				}
			}


			// $this->session->set_flashdata('edit', get_alert('success', 'Role Karyawan berhasil diperbaharui.'));
			// redirect('Karyawan/edit_role/' . $status_id . '/' . $Karyawan_id);
		}

		$this->twig->display('edit-Karyawan-role.html', $data);
	}

	/**
	 * Meghapus foto Karyawan
	 * @since 1.8
	 */
	function delete_foto($segment_3 = "", $segment_4 = "")
	{

		$Karyawan_id       = (int)$segment_3;
		$retrieve_Karyawan = $this->KaryawanModel->retrieve($Karyawan_id);
		if (empty($retrieve_Karyawan)) {
			show_error('Data Karyawan tidak ditemukan');
		}



		if (is_file(get_path_image($retrieve_Karyawan['foto']))) {
			unlink(get_path_image($retrieve_Karyawan['foto']));
		}

		if (is_file(get_path_image($retrieve_Karyawan['foto'], 'medium'))) {
			unlink(get_path_image($retrieve_Karyawan['foto'], 'medium'));
		}

		if (is_file(get_path_image($retrieve_Karyawan['foto'], 'small'))) {
			unlink(get_path_image($retrieve_Karyawan['foto'], 'small'));
		}

		$this->KaryawanModel->delete_foto($retrieve_Karyawan['id']);

		$uri_back = $segment_4;
		if (!empty($uri_back)) {
			$uri_back = deurl_redirect($uri_back);
		} else {
			$uri_back = site_url('Karyawan');
		}

		redirect($uri_back);
	}

	function edit_picture_post($segment_3 = '')
	{

		$Karyawan_id       = (int)$segment_3;
		$retrieve_Karyawan = $this->KaryawanModel->retrieve($Karyawan_id, null);
		if (empty($retrieve_Karyawan)) {
			$this->set_response(array("status" => "NOT OK", "data" => "NOT FOUND Karyawan"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}

		// $this->set_response(array("status" => "OK", "data" => 	$retrieve_Karyawan), REST_Controller::HTTP_OK);
		// return;

		// $retrieve_login = $this->LoginModel->retrieve(null, null, null, null, $retrieve_Karyawan['id']);
		// $retrieve_Karyawan['is_admin'] = $retrieve_login['is_admin'];

		// $data['status_id']    = $status_id;
		$data['Karyawan_id']  = $Karyawan_id;
		$data['Karyawan']     = $retrieve_Karyawan;

		$config['upload_path']   = $this->get_path_image();
		$config['allowed_types'] = 'jpg|jpeg|png';
		$config['max_size']      = '0';
		$config['max_width']     = '0';
		$config['max_height']    = '0';
		$config['file_name']     = 'Karyawan-' . url_title($retrieve_Karyawan['nama'], '-', true);
		$this->upload->initialize($config);


		if ($this->upload->do_upload()) {



			if (is_file($this->get_path_image($retrieve_Karyawan['foto']))) {
				unlink($this->get_path_image($retrieve_Karyawan['foto']));
			}

			if (is_file($this->get_path_image($retrieve_Karyawan['foto'], 'medium'))) {
				unlink($this->get_path_image($retrieve_Karyawan['foto'], 'medium'));
			}

			if (is_file($this->get_path_image($retrieve_Karyawan['foto'], 'small'))) {
				unlink($this->get_path_image($retrieve_Karyawan['foto'], 'small'));
			}

			$upload_data = $this->upload->data();

			# create thumb small
			$this->create_img_thumb(
				$this->get_path_image($upload_data['file_name']),
				'_small',
				'50',
				'50'
			);

			# create thumb medium
			$this->create_img_thumb(
				$this->get_path_image($upload_data['file_name']),
				'_medium',
				'150',
				'150'
			);

			# update Karyawan
			$this->KaryawanModel->update(
				$Karyawan_id,
				$retrieve_Karyawan['lokasi_tugas_id'],
				$retrieve_Karyawan['sub_bagian_id'],
				$retrieve_Karyawan['nip'],
				$retrieve_Karyawan['nama'],
				$retrieve_Karyawan['jenis_kelamin'],
				$retrieve_Karyawan['tempat_lahir'],
				$retrieve_Karyawan['tgl_lahir'],
				$retrieve_Karyawan['alamat'],
				$upload_data['file_name'],
				$retrieve_Karyawan['status_id'],
				$retrieve_Karyawan['status_kawin'],
				$retrieve_Karyawan['golongan_darah'],
				$retrieve_Karyawan['jabatan_id'],
				$retrieve_Karyawan['departemen_id'],
				$retrieve_Karyawan['golongan_id'],
				$retrieve_Karyawan['pangkat_id'],
				$retrieve_Karyawan['tipe_karyawan_id'],
				$retrieve_Karyawan['telp'],
				$retrieve_Karyawan['email'],
				$retrieve_Karyawan['no_hp'],
				$retrieve_Karyawan['no_npwp'],
				$retrieve_Karyawan['no_kk'],
				$retrieve_Karyawan['no_ktp'],
				$retrieve_Karyawan['nama_bank'],
				$retrieve_Karyawan['no_rek_bank'],
				$retrieve_Karyawan['no_bpjs'],
				$retrieve_Karyawan['no_bpjs_ks'],
				$retrieve_Karyawan['tgl_masuk'],
				$retrieve_Karyawan['tgl_keluar'],
				$retrieve_Karyawan['tgl_hbs_kontrak'],
				$retrieve_Karyawan['status_pajak'],
				$retrieve_Karyawan['agama'],
				$retrieve_Karyawan['is_jht'],
				$retrieve_Karyawan['is_jp'],
				$retrieve_Karyawan['is_jks'],
				$this->user_id


			);

			$this->set_response(array("status" => "OK", "data" => $Karyawan_id), REST_Controller::HTTP_CREATED);
			return;
		} else {
			if (!empty($_FILES['userfile']['tmp_name'])) {
				$this->set_response(array("status" => "NOT OK", "data" => ""), REST_Controller::HTTP_NO_CONTENT);
				return;
				// $data['error_upload'] = '<span class="text-error">' . $this->upload->display_errors() . '</span>';
			} else {
				$this->set_response(array("status" => "NOT OK", "data" => "gagal upload"), REST_Controller::HTTP_NO_CONTENT);
				return;
			}
		}
	}
}
