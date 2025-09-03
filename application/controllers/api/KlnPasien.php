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
class KlnPasien extends BD_Controller
{
	public $user_id;
	public $theCredential;
	public $data = array();
	function __construct()
	{
		error_reporting(0);
		date_default_timezone_set("Asia/Jakarta");
		parent::__construct();
		$this->load->model('KlnPasienModel');
		$this->load->model('M_DatatablesModel');
		$this->load->model('LoginModel');
		$this->load->library('pdfgenerator');
		$this->load->helper(array('url', 'antech_helper', 'form', 'text',  'security', 'file', 'number', 'date', 'download'));
		$this->load->library('image_lib');
		$this->load->library('upload');
		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id = $this->user_data->id;
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


	public function list_post($status_id)
	{
		$post = $this->post();

		$query  = "SELECT a.*,g.nama as lokasi,TIMESTAMPDIFF(YEAR, tgl_lahir, CURDATE()) AS umur
		 FROM kln_pasien a
		left join gbm_organisasi g on a.lokasi_id=g.id
		";
		$search = array('a.nip', 'a.nama', 'a.jenis_kelamin',  'a.no_hp', 'a.alamat', 'a.tgl_lahir', 'g.nama');
		$where  = null;
		$isWhere = "a.lokasi_id in
		(select location_id from fwk_users_location where user_id=" . $this->user_id . ")";

		// $where  = array('a.status_id' => $status_id);

		 $data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	public	function index_get($segment_3 = '')
	{
		$pasien_id = $segment_3;
		$retrieve = $this->KlnPasienModel->retrieve($pasien_id, null, null);
		// $retrieve['role_id'] = $this->KlnPasienModel->retrieve_role($pasien_id);
		// $retrieve['login'] = $this->LoginModel->retrieve(null, null, null, null, $pasien_id, null, null, null);

		// $retrieve['riwayat_keluarga'] = $this->db->query("select * from payroll_riwayat_keluarga where pasien_id=" . $pasien_id . "")->result_array();
	

		if (!empty($retrieve)) {
			$interval = date_diff(date_create(), date_create($retrieve['tgl_lahir']));
			// echo $interval->format("You are  %Y Year, %M Months, %d Days, %H Hours, %i Minutes, %s Seconds Old");
			$retrieve['umur']=$interval->format("%Y Tahun, %M Bulan, %d Hari");

			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => null), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	public	function index_delete($segment_3 = '')
	{

		$id = $segment_3;
		$pasien_id       = (int)$segment_3;
		$retrieve_Pasien = $this->KlnPasienModel->retrieve($pasien_id);
		if (empty($retrieve_Pasien)) {
			$this->set_response(array("status" => "NOT OK", "data" => "NOT FOUND Karyawan"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		
		$res = $this->KlnPasienModel->delete($id);
		if (($res)) {
					/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id' => $id)), 'entity' => 'hrms_karyawan', 'action' => 'delete', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => null), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function index_post()
	{
		// var_dump($this->post());
		// exit();
		if ($this->post('nama')==null || empty($this->post('nama'))){
			$this->set_response(array("status" => "NOT OK", "data" => "Nama Kosong"), REST_Controller::HTTP_NOT_ACCEPTABLE);
			return;
		}
		if ($this->post('nip')==null || empty($this->post('nip'))){
			$this->set_response(array("status" => "NOT OK", "data" => "NIK Kosong"), REST_Controller::HTTP_NOT_ACCEPTABLE);
			return;
		}

		$config['upload_path']   = $this->get_path_image();
		$config['allowed_types'] = 'jpg|jpeg|png';
		$config['max_size']      = '0';
		$config['max_width']     = '0';
		$config['max_height']    = '0';
		$config['file_name']     = 'Pasien-' . url_title($this->post('nama', TRUE), '-', true);
		$this->upload->initialize($config);

		if (!empty($_FILES['userfile']['tmp_name']) and !$this->upload->do_upload()) {
			$data['error_upload'] = '<span class="text-error">' . $this->upload->display_errors() . '</span>';
			$error_upload = true;
		} else {
			$data['error_upload'] = '';
			$error_upload = false;
		}

		$this->load->library('Autonumber');
		$PasienId = $this->autonumber->pasien();

		$lokasi_id      = $this->post('lokasi_id', TRUE);
		// $nip           = $this->post('nip', TRUE);
		$nip           = $PasienId;
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
		$status_kawin = $this->post('status_pernikahan', TRUE);
		$golongan_darah = $this->post('golongan_darah', TRUE);
		$telp   = $this->post('telp', TRUE);
		$email   = $this->post('email', TRUE);
		$no_hp   = $this->post('no_hp', TRUE);
		$no_ktp   = $this->post('no_ktp', TRUE);
		$alergi   = $this->post('alergi', TRUE);
		$catatan   = $this->post('catatan', TRUE);
		$penanggung_jawab   = $this->post('penanggung_jawab', TRUE);
		
		$tgl_daftar   = $this->post('tgl_daftar', TRUE);
		$tgl_non_aktif   = $this->post('tgl_non_aktif', TRUE);
		$agama   = $this->post('agama', TRUE);
		
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
		$pasien_id = $this->KlnPasienModel->create(
			$lokasi_id,
			$nip,
			$nama,
			$jenis_kelamin,
			$tempat_lahir,
			$tgl_lahir,
			$alamat,
			$foto,
			'1',//$status_id,
			$status_kawin,
			$golongan_darah,		
			$telp,
			$email,
			$no_hp,
			$no_ktp,
			$tgl_daftar,
			$tgl_non_aktif,
			$agama,
			$alergi,
			$catatan,
			$penanggung_jawab,
			$this->user_id

		);

		# simpan role
		// $role      = json_decode($this->post('role_id', TRUE));
		// $this->KlnPasienModel->delete_role($pasien_id);
		// foreach ($role  as $post_role) {
		// 	if (!empty($post_role)) {
		// 		$this->KlnPasienModel->insert_role($pasien_id, $post_role);
		// 	}
		// }
		// var_dump($this->post('details', TRUE));
		// return;
		

		// # simpan data login
		// $this->LoginModel->create(
		// 	$username,
		// 	$password,
		// 	null,
		// 	$pasien_id,
		// 	$is_admin
		// );
		if (!empty($pasien_id)) {
			/* start audit trail */
		$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'hrms_karyawan', 'action' => 'new', 'entity_id' => null);
		$this->db->insert('fwk_user_audit', $audit);
		/* end audit trail */	
			$this->set_response(array("status" => "OK", "data" => $nip), REST_Controller::HTTP_CREATED);
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

		if ($this->put('nama')==null || empty($this->put('nama'))){
			$this->set_response(array("status" => "NOT OK", "data" => "Nama Kosong"), REST_Controller::HTTP_NOT_ACCEPTABLE);
			return;
		}
		if ($this->put('nip')==null || empty($this->put('nip'))){
			$this->set_response(array("status" => "NOT OK", "data" => "NIK Kosong"), REST_Controller::HTTP_NOT_ACCEPTABLE);
			return;
		}
		$pasien_id       = (int)$segment_3;
		$retrieve_Pasien = $this->KlnPasienModel->retrieve($pasien_id, null);
		if (empty($retrieve_Pasien)) {
			$this->set_response(array("status" => "NOT OK", "data" => "NOT FOUND Karyawan"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$retrieve_login = $this->LoginModel->retrieve(null, null, null, null, $retrieve_Pasien['id']);
		//$retrieve_Pasien['is_admin'] = $retrieve_login['is_admin'];
		$lokasi_id      = $this->put('lokasi_id', TRUE);
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
		$telp   = $this->put('telp', TRUE);
		$email   = $this->put('email', TRUE);
		$no_hp   = $this->put('no_hp', TRUE);
		$no_ktp   = $this->put('no_ktp', TRUE);
		$penanggung_jawab   = $this->put('penanggung_jawab', TRUE);
		$catatan   = $this->put('catatan', TRUE);
		$tgl_daftar   = $this->put('tgl_daftar', TRUE);
		$tgl_non_aktif   = $this->put('tgl_non_aktif', TRUE);
		$alergi = $this->put('alergi', TRUE);
		$agama = $this->put('agama', TRUE);
		
		# update Karyawan
		$this->KlnPasienModel->update(
			$retrieve_Pasien['id'],
			$lokasi_id,
			$nip,
			$nama,
			$jenis_kelamin,
			$tempat_lahir,
			$tgl_lahir,
			$alamat,
			$retrieve_Pasien['foto'],
			$status_id,
			$status_kawin,
			$golongan_darah,
			$telp,
			$email,
			$no_hp,
			$no_ktp,
			$tgl_daftar,
			$tgl_non_aktif,
			$agama,
			$alergi,
			$catatan,
			$penanggung_jawab,
			$this->user_id
		);

		# simpan role
		// $role      = ($this->put('role_id', TRUE));
		// $this->KlnPasienModel->delete_role($pasien_id);
		// foreach ($role  as $post_role) {
		// 	if (!empty($post_role)) {
		// 		$this->KlnPasienModel->insert_role($pasien_id, $post_role);
		// 	}
		// }
		# update login
		// $this->LoginModel->update(
		// 	$retrieve_login['id'],
		// 	$retrieve_login['username'],
		// 	null,
		// 	$pasien_id,
		// 	$is_admin,
		// 	null
		// );

		// if ($retrieve_Pasien['status_id'] == 0 && $status == 1) {
		// 	kirim_email_approve_Karyawan($pasien_id);
		// }

		/* start audit trail */
		$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'hrms_karyawan', 'action' => 'edit', 'entity_id' => $pasien_id );
		$this->db->insert('fwk_user_audit', $audit);
		/* end audit trail */	
		$this->set_response(array("status" => "OK", "data" => $pasien_id), REST_Controller::HTTP_CREATED);
	}

	function simpan_riwayat_keluarga_post($pasien_id)
	{
		$karyawandetail      = json_decode($this->post('details', TRUE));
		// var_dump($karyawandetail);
		$this->db->where('pasien_id', $pasien_id);
		$this->db->delete('payroll_riwayat_keluarga');
		$keluarga = $karyawandetail->keluargaItems;

		foreach ($keluarga as $key => $value) {
			$this->db->insert("payroll_riwayat_keluarga", array(
				'pasien_id' => $pasien_id,
				'nama' => $value->nama_keluarga,
				'tempat_lahir' => $value->tempat_lahir_keluarga,
				'tgl_lahir' => $value->tanggal_lahir_keluarga,
				'status' => $value->status_keluarga
			));
		}
		$this->set_response(array("status" => "OK", "data" => $pasien_id), REST_Controller::HTTP_CREATED);
	}
	function simpan_riwayat_pendidikan_post($pasien_id)
	{
		$karyawandetail      = json_decode($this->post('details', TRUE));
		// var_dump($karyawandetail);

		$this->db->where('pasien_id', $pasien_id);
		$this->db->delete('payroll_riwayat_pendidikan');
		$pendidikan = $karyawandetail->pendidikanItems;
		foreach ($pendidikan as $key => $value) {
			$this->db->insert("payroll_riwayat_pendidikan", array(
				'pasien_id' => $pasien_id,
				'nama_sekolah' => $value->nama_sekolah,
				'lokasi' => $value->lokasi_sekolah,
				'jurusan' => $value->jurusan,
				'no_ijazah' => $value->no_ijazah,
				'tgl_ijazah' => $value->tgl_ijazah,
			));
		}
		$this->set_response(array("status" => "OK", "data" => $pasien_id), REST_Controller::HTTP_CREATED);
	}
	function simpan_riwayat_bahasa_post($pasien_id)
	{
		$karyawandetail      = json_decode($this->post('details', TRUE));
		// var_dump($karyawandetail);
		$this->db->where('pasien_id', $pasien_id);
		$this->db->delete('payroll_riwayat_bahasa');
		$bahasa = $karyawandetail->bahasaItems;

		foreach ($bahasa as $key => $value) {
			$this->db->insert("payroll_riwayat_bahasa", array(
				'pasien_id' => $pasien_id,
				'bahasa' => $value->bahasa,
				'kemampuan_bicara' => $value->kemampuan_bicara

			));
		}
		$this->set_response(array("status" => "OK", "data" => $pasien_id), REST_Controller::HTTP_CREATED);
	}
	function simpan_riwayat_jabatan_post($pasien_id)
	{
		$karyawandetail      = json_decode($this->post('details', TRUE));
		//  var_dump($karyawandetail);
		$this->db->where('pasien_id', $pasien_id);
		$this->db->delete('payroll_riwayat_jabatan');
		$jabatan = $karyawandetail->jabatanItems;
		foreach ($jabatan as $key => $value) {
			$this->db->insert("payroll_riwayat_jabatan", array(
				'lokasi_tugas_id' => $value->lokasi_tugas_id->id,
				'sub_bagian_id' => $value->sub_bagian_id->id,
				'id_tipe_karyawan' => $value->id_tipe_karyawan->id,
				'pasien_id' => $pasien_id,
				'jabatan_id' => $value->riwayat_jabatan->id,
				'tmt' => $value->tmt_jabatan,
				'selesai_tugas' => $value->selesai_tugas_jabatan,
				'status' => $value->status_jabatan
			));
		}
		$this->set_response(array("status" => "OK", "data" => $pasien_id), REST_Controller::HTTP_CREATED);
	}
	function simpan_riwayat_pangkat_post($pasien_id)
	{
		$karyawandetail      = json_decode($this->post('details', TRUE));
		$this->db->where('pasien_id', $pasien_id);
		$this->db->delete('payroll_riwayat_kepangkatan');
		$pangkat = $karyawandetail->pangkatItems;
		foreach ($pangkat as $key => $value) {
			$this->db->insert("payroll_riwayat_kepangkatan", array(
				'pasien_id' => $pasien_id,
				'pangkat_id' => $value->riwayat_pangkat->id,
				'golongan_id' => $value->riwayat_golongan->id,
				'tmt' => $value->tmt_pangkat,
				'status' => $value->status_pangkat
			));
		}
		$this->set_response(array("status" => "OK", "data" => $pasien_id), REST_Controller::HTTP_CREATED);
	}
	function simpan_riwayat_hukuman_post($pasien_id)
	{
		$karyawandetail      = json_decode($this->post('details', TRUE));

		$this->db->where('pasien_id', $pasien_id);
		$this->db->delete('payroll_riwayat_hukuman');
		$hukuman = $karyawandetail->hukumanItems;
		foreach ($hukuman as $key => $value) {
			$this->db->insert("payroll_riwayat_hukuman", array(
				'pasien_id' => $pasien_id,
				'jenis_hukuman' => $value->jenis_hukuman,
				'no_sk' => $value->nosk_hukuman,
				'no_pemulihan' => $value->nosk_pemulihan,
				'tgl_sk' => $value->tglsk_hukuman,
				'tgl_pemulihan' => $value->tglsk_pemulihan,
			));
		}
		$this->set_response(array("status" => "OK", "data" => $pasien_id), REST_Controller::HTTP_CREATED);
	}
	function simpan_riwayat_penghargaan_post($pasien_id)
	{
		$karyawandetail      = json_decode($this->post('details', TRUE));

		$this->db->where('pasien_id', $pasien_id);
		$this->db->delete('payroll_riwayat_penghargaan');
		$penghargaan = $karyawandetail->penghargaanItems;
		foreach ($penghargaan as $key => $value) {
			$this->db->insert("payroll_riwayat_penghargaan", array(
				'pasien_id' => $pasien_id,
				'nama_penghargaan' => $value->nama_penghargaan,
				'tahun' => $value->tahun_penghargaan,
				'instansi' => $value->instansi_penghargaan,
			));
		}
		$this->set_response(array("status" => "OK", "data" => $pasien_id), REST_Controller::HTTP_CREATED);
	}
	function simpan_riwayat_pelatihan_post($pasien_id)
	{
		$karyawandetail      = json_decode($this->post('details', TRUE));

		$this->db->where('pasien_id', $pasien_id);
		$this->db->delete('payroll_riwayat_pelatihan');
		$pelatihan = $karyawandetail->pelatihanItems;
		foreach ($pelatihan as $key => $value) {
			$this->db->insert("payroll_riwayat_pelatihan", array(
				'pasien_id' => $pasien_id,
				'nama_pelatihan' => $value->nama_pelatihan,
				'status' => $value->status
			));
		}
		$this->set_response(array("status" => "OK", "data" => $pasien_id), REST_Controller::HTTP_CREATED);
	}
	function simpan_riwayat_pengalaman_post($pasien_id)
	{
		$karyawandetail      = json_decode($this->post('details', TRUE));

		$this->db->where('pasien_id', $pasien_id);
		$this->db->delete('payroll_riwayat_pengalaman');
		$pengalaman = $karyawandetail->pengalamanItems;
		foreach ($pengalaman as $key => $value) {
			$this->db->insert("payroll_riwayat_pengalaman", array(
				'pasien_id' => $pasien_id,
				'perusahaan' => $value->perusahaan,
				'mulai' => $value->mulai,
				'akhir' => $value->akhir,
				'status' => $value->status,
			));
		}
		$this->set_response(array("status" => "OK", "data" => $pasien_id), REST_Controller::HTTP_CREATED);
	}
	function simpan_riwayat_keahlian_post($pasien_id)
	{
		$karyawandetail      = json_decode($this->post('details', TRUE));

		$this->db->where('pasien_id', $pasien_id);
		$this->db->delete('payroll_riwayat_keahlian');
		$keahlian = $karyawandetail->keahlianItems;
		foreach ($keahlian as $key => $value) {
			$this->db->insert("payroll_riwayat_keahlian", array(
				'pasien_id' => $pasien_id,
				'nama_keahlian' => $value->nama_keahlian,
				'status' => $value->status
			));
		}
		$this->set_response(array("status" => "OK", "data" => $pasien_id), REST_Controller::HTTP_CREATED);
	}

	function getAll_get()
	{

		$retrieve = $this->KlnPasienModel->retrieve_all_pasien();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => []), REST_Controller::HTTP_OK);
		}
	}
	function getAllAktif_get()
	{

		$retrieve = $this->KlnPasienModel->retrieve_all_Karyawan_aktif();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => []), REST_Controller::HTTP_OK);
		}
	}
	function getAllAktifEstate_get()
	{

		$retrieve = $this->KlnPasienModel->retrieve_all_Karyawan_aktif_estate();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => []), REST_Controller::HTTP_OK);
		}
	}
	function getByLokasiTugas_get($segment_3 = '')
	{

		$org_id = $segment_3;
		$retrieve = $this->KlnPasienModel->retrieve_by_lokasi_tugas($org_id);

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
		if (!($org_id && $periode)){
			$this->set_response(array("status" => "NOT OK", "data" => []), REST_Controller::HTTP_OK);
			return;
		}

		
		$q = "SELECT a.*,b.nama as sub_bagian_nama FROM karyawan a LEFT JOIN gbm_organisasi b ON a.sub_bagian_id=b.id
		WHERE (tgl_non_aktif IS NULL OR left(tgl_non_aktif,7) >='" . substr($periode, 0, 7) . "' OR tgl_non_aktif='0000-00-00')
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

		$pasien_id = $segment_3;
		$retrieve = $this->KlnPasienModel->retrieve($pasien_id, null, null);
		// $retrieve['jabatan'] = $this->db->query("select * from payroll_jabatan where id=" . $retrieve['jabatan_id'] . "")->row_array();
		// $retrieve['lokasi_tugas'] = $this->db->query("select * from gbm_organisasi where id=" . $retrieve['lokasi_tugas_id'] . "")->row_array();
		// $retrieve['sub_bagian'] = $this->db->query("select * from gbm_organisasi where id=" . $retrieve['sub_bagian_id'] . "")->row_array();
		// $retrieve['departemen'] = $this->db->query("select * from payroll_department where id=" . $retrieve['departemen_id'] . "")->row_array();
		// $retrieve['pangkat'] = $this->db->query("select * from payroll_pangkat where id=" . $retrieve['pangkat_id'] . "")->row_array();
		// $retrieve['golongan'] = $this->db->query("select * from payroll_golongan where id=" . $retrieve['golongan_id'] . "")->row_array();
		// $retrieve['tipe_karyawan'] = $this->db->query("select * from payroll_tipe_karyawan where id=" . $retrieve['tipe_pasien_id'] . "")->row_array();

		// $retrieve['riwayat_keluarga'] = $this->db->query("select * from payroll_riwayat_keluarga where pasien_id=" . $pasien_id . "")->result_array();
		// $retrieve['riwayat_bahasa'] = $this->db->query("select * from payroll_riwayat_bahasa where pasien_id=" . $pasien_id . "")->result_array();
		// $retrieve['riwayat_jabatan'] = $this->db->query("select a.*,b.nama as jabatan, c.nama as lokasi_tugas, cc.nama as sub_bagian, ccc.nama as tipe_karyawan from payroll_riwayat_jabatan a inner join payroll_jabatan b on a.jabatan_id=b.id left join gbm_organisasi c on a.lokasi_tugas_id=c.id left join gbm_organisasi cc on a.sub_bagian_id=cc.id left join payroll_tipe_karyawan ccc on a.id_tipe_karyawan=ccc.id where pasien_id=" . $pasien_id . "")->result_array();
		// $retrieve['riwayat_pangkat'] = $this->db->query("select a.*,b.nama as pangkat,c.nama as golongan from payroll_riwayat_kepangkatan a inner join payroll_pangkat b on a.pangkat_id=b.id
		//  inner join payroll_golongan c on a.golongan_id=c.id  where pasien_id=" . $pasien_id . "")->result_array();
		// $retrieve['riwayat_penghargaan'] = $this->db->query("select * from payroll_riwayat_penghargaan where pasien_id=" . $pasien_id . "")->result_array();
		// $retrieve['riwayat_hukuman'] = $this->db->query("select * from payroll_riwayat_hukuman where pasien_id=" . $pasien_id . "")->result_array();
		// $retrieve['riwayat_pendidikan'] = $this->db->query("select * from payroll_riwayat_pendidikan where pasien_id=" . $pasien_id . "")->result_array();
		// $retrieve['riwayat_keahlian'] = $this->db->query("select * from payroll_riwayat_keahlian where pasien_id=" . $pasien_id . "")->result_array();
		// $retrieve['riwayat_pengalaman'] = $this->db->query("select * from payroll_riwayat_pengalaman where pasien_id=" . $pasien_id . "")->result_array();
		// $retrieve['riwayat_pelatihan'] = $this->db->query("select * from payroll_riwayat_pelatihan where pasien_id=" . $pasien_id . "")->result_array();

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
         <h2>Pasien</h2>
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
            <td>No KTP</td>
            <td width='10px'>:</td>
            <td>" . $retrieve['no_ktp'] . "</td>
        </tr>
		<tr>
            <td>Alergi</td>
            <td width='10px'>:</td>
            <td>" . $retrieve['alergi'] . "</td>
        </tr>
		<tr>
            <td>Penanggung Jawab</td>
            <td width='10px'>:</td>
            <td>" . $retrieve['penanggung_jawab'] . "</td>
        </tr>
		
		<tr>
            <td>Catatan</td>
            <td width='10px'>:</td>
            <td>" . $retrieve['catatan'] . "</td>
        </tr>
		
		
        </table>
        ";

		// var_dump($html);exit();
		$html = $html . "
		<h3>Riwayat</h3>
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
				$where = $where . " and (a.tgl_non_aktif IS NULL or a.tgl_non_aktif='0000-00-00') ";
				$judul_status ='Aktif';
			} else {
				$where = $where . " and a.tgl_non_aktif IS NOT NULL and a.tgl_non_aktif!='0000-00-00' ";
				$judul_status ='Non Aktif';
			}
		}
		$retrieveKaryawan = $this->db->query("select a.*,g.nama as lokasi_tugas,h.nama as sub_bagian, b.nama as departemen,c.nama as jabatan,d.nama as pangkat,e.nama as golongan,f.nama as tipe_karyawan
		from karyawan a left join payroll_department b on a.departemen_id=b.id 
	   left join payroll_jabatan c on a.jabatan_id=c.id 
	   left join payroll_pangkat d on a.pangkat_id=d.id 
	   left join payroll_golongan e on a.golongan_id=e.id 
	   left join payroll_tipe_karyawan f on a.tipe_pasien_id=f.id
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
  <h3 class="title">LAPORAN PRODUKSI PANEN</h3>
  <table class="no_border" style="width:30%">
			
			<tr>
					<td>Lokasi</td>
					<td>:</td>
					<td>' . $judul_lokasi . '</td>
			</tr>
			<tr>
					<td>Sub Bagian</td>
					<td>:</td>
					<td>' . $judul_sub_bagian . '</td>
			</tr>
			<tr>	
					<td>Status</td>
					<td>:</td>
					<td>' . $judul_status  . '</td>
			</tr>
			
	</table>
			<br>
  ';

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
							' . $m['tempat_lahir'] . ', ' . tgl_indo_normal($m['tgl_lahir']) . ' 
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
							' . tgl_indo_normal($m['tgl_daftar']) . ' 
						</td>
						<td>
							' . tgl_indo_normal($m['tgl_non_aktif']) . ' 
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
		$pasien_id       = (int)$segment_4;
		$retrieve_Pasien = $this->KlnPasienModel->retrieve($pasien_id);
		if (empty($retrieve_Pasien)) {
			exit('Data Karyawan tidak ditemukan');
		}

		# jika sebagai Karyawan, hanya profilnya dia yang bisa diupdate
		// if (is_Karyawan() and get_sess_data('user', 'id') != $retrieve_Pasien['id']) {
		// 	exit('Akses ditolak');
		// }

		$retrieve_login = $this->LoginModel->retrieve(null, null, null, null, $retrieve_Pasien['id']);
		$retrieve_role = $this->KlnPasienModel->retrieve_role($retrieve_Pasien['id']);
		$retrieve_Pasien['is_admin'] = $retrieve_login['is_admin'];

		$data['status_id']    = $status_id;
		$data['pasien_id']  = $pasien_id;
		$data['Karyawan']     = $retrieve_Pasien;
		$data['role']     = array_column($retrieve_role, 'role');


		// var_dump(array_column( $retrieve_role, 'role'));exit;

		if ($this->form_validation->run('Karyawan/role') == TRUE) {
			$role      = $this->post('role', TRUE);
			$this->KlnPasienModel->delete_role($retrieve_Pasien['id']);
			foreach ($role  as $post_role) {

				if (!empty($post_role)) {

					$this->KlnPasienModel->insert_role($retrieve_Pasien['id'], $post_role);
				}
			}


			// $this->session->set_flashdata('edit', get_alert('success', 'Role Karyawan berhasil diperbaharui.'));
			// redirect('Karyawan/edit_role/' . $status_id . '/' . $pasien_id);
		}

		$this->twig->display('edit-Karyawan-role.html', $data);
	}

	/**
	 * Meghapus foto Karyawan
	 * @since 1.8
	 */
	function delete_foto($segment_3 = "", $segment_4 = "")
	{

		$pasien_id       = (int)$segment_3;
		$retrieve_Pasien = $this->KlnPasienModel->retrieve($pasien_id);
		if (empty($retrieve_Pasien)) {
			show_error('Data Karyawan tidak ditemukan');
		}



		if (is_file(get_path_image($retrieve_Pasien['foto']))) {
			unlink(get_path_image($retrieve_Pasien['foto']));
		}

		if (is_file(get_path_image($retrieve_Pasien['foto'], 'medium'))) {
			unlink(get_path_image($retrieve_Pasien['foto'], 'medium'));
		}

		if (is_file(get_path_image($retrieve_Pasien['foto'], 'small'))) {
			unlink(get_path_image($retrieve_Pasien['foto'], 'small'));
		}

		$this->KlnPasienModel->delete_foto($retrieve_Pasien['id']);

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

		$pasien_id       = (int)$segment_3;
		$retrieve_Pasien = $this->KlnPasienModel->retrieve($pasien_id, null);
		if (empty($retrieve_Pasien)) {
			$this->set_response(array("status" => "NOT OK", "data" => "NOT FOUND Karyawan"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}

		// $this->set_response(array("status" => "OK", "data" => 	$retrieve_Pasien), REST_Controller::HTTP_OK);
		// return;

		// $retrieve_login = $this->LoginModel->retrieve(null, null, null, null, $retrieve_Pasien['id']);
		// $retrieve_Pasien['is_admin'] = $retrieve_login['is_admin'];

		// $data['status_id']    = $status_id;
		$data['pasien_id']  = $pasien_id;
		$data['Karyawan']     = $retrieve_Pasien;

		$config['upload_path']   = $this->get_path_image();
		$config['allowed_types'] = 'jpg|jpeg|png';
		$config['max_size']      = '0';
		$config['max_width']     = '0';
		$config['max_height']    = '0';
		$config['file_name']     = 'Pasien-' . url_title($retrieve_Pasien['nama'], '-', true);
		$this->upload->initialize($config);


		if ($this->upload->do_upload()) {



			if (is_file($this->get_path_image($retrieve_Pasien['foto']))) {
				unlink($this->get_path_image($retrieve_Pasien['foto']));
			}

			if (is_file($this->get_path_image($retrieve_Pasien['foto'], 'medium'))) {
				unlink($this->get_path_image($retrieve_Pasien['foto'], 'medium'));
			}

			if (is_file($this->get_path_image($retrieve_Pasien['foto'], 'small'))) {
				unlink($this->get_path_image($retrieve_Pasien['foto'], 'small'));
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
			$dataUpdate=array("foto"=>$upload_data['file_name'],"diubah_oleh"=>$this->user_id,"diubah_tanggal"=>date("Y-m-d H:i:s"));
			$this->db->where('id', $pasien_id);
			$this->db->update('kln_pasien', $dataUpdate);
			// $this->KlnPasienModel->update(
			// 	$pasien_id,
			// 	$retrieve_Pasien['lokasi_tugas_id'],
			// 	$retrieve_Pasien['sub_bagian_id'],
			// 	$retrieve_Pasien['nip'],
			// 	$retrieve_Pasien['nama'],
			// 	$retrieve_Pasien['jenis_kelamin'],
			// 	$retrieve_Pasien['tempat_lahir'],
			// 	$retrieve_Pasien['tgl_lahir'],
			// 	$retrieve_Pasien['alamat'],
			// 	$upload_data['file_name'],
			// 	$retrieve_Pasien['status_id'],
			// 	$retrieve_Pasien['status_kawin'],
			// 	$retrieve_Pasien['golongan_darah'],
			// 	$retrieve_Pasien['jabatan_id'],
			// 	$retrieve_Pasien['departemen_id'],
			// 	$retrieve_Pasien['golongan_id'],
			// 	$retrieve_Pasien['pangkat_id'],
			// 	$retrieve_Pasien['tipe_pasien_id'],
			// 	$retrieve_Pasien['telp'],
			// 	$retrieve_Pasien['email'],
			// 	$retrieve_Pasien['no_hp'],
			// 	$retrieve_Pasien['no_npwp'],
			// 	$retrieve_Pasien['no_kk'],
			// 	$retrieve_Pasien['no_ktp'],
			// 	$retrieve_Pasien['nama_bank'],
			// 	$retrieve_Pasien['no_rek_bank'],
			// 	$retrieve_Pasien['no_bpjs'],
			// 	$retrieve_Pasien['no_bpjs_ks'],
			// 	$retrieve_Pasien['tgl_daftar'],
			// 	$retrieve_Pasien['tgl_non_aktif'],
			// 	$retrieve_Pasien['tgl_hbs_kontrak'],
			// 	$retrieve_Pasien['status_pajak'],
			// 	$retrieve_Pasien['agama'],
			// 	$retrieve_Pasien['is_jht'],
			// 	$retrieve_Pasien['is_jp'],
			// 	$retrieve_Pasien['is_jks'],
			// 	$this->user_id


			// );

			$this->set_response(array("status" => "OK", "data" => $pasien_id), REST_Controller::HTTP_CREATED);
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
