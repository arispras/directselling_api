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

class HrmsResume extends BD_Controller
{
	public $user_id;
	public $theCredential;
	public $data = array();
	function __construct()
	{
		error_reporting(0);
		date_default_timezone_set("Asia/Jakarta");
		parent::__construct();
		$this->load->model('HrmsResumeModel');
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


	public function getCountStatus_get()
	{
		$sql = "	SELECT 
       SUM(CASE WHEN status_resume = 'applied' THEN 1 ELSE 0 END) applied,
       SUM(CASE WHEN status_resume = 'screening' THEN 1 ELSE 0 END) screening,
        SUM(CASE WHEN status_resume = 'interview' THEN 1 ELSE 0 END) interview,
          SUM(CASE WHEN status_resume = 'onboarding' THEN 1 ELSE 0 END) onboarding
  		FROM hrms_resume";
		$retrieve=$this->db->query($sql)->row_array();
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
	}
	public function list_post($status)
	{
		$post = $this->post();

		$query  = "SELECT a.*,b.nama as nama_posisi FROM hrms_resume a 
		left join hrms_posisi b on a.posisi_id =b.id";
		$search = array('a.no_resume', 'a.nama', 'a.jenis_kelamin', 'b.nama');
		$where  = null;
		$isWhere = null;

		$where  = array('a.status_resume' => $status);

		$data = $this->M_DatatablesModel->get_tables_query_Karyawan($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	public	function index_get($segment_3 = '')
	{
		$resume_id = $segment_3;
		$retrieve = $this->HrmsResumeModel->retrieve($resume_id, null, null);
		$retrieve['resume_pendidikan'] = $this->db->query("select * from hrms_resume_pendidikan where resume_id=" . $resume_id . "")->result_array();
		$retrieve['resume_pengalaman'] = $this->db->query("select * from hrms_resume_pengalaman where resume_id=" . $resume_id . "")->result_array();
		$retrieve['resume_profesional'] = $this->db->query("select * from hrms_resume_profesional where resume_id=" . $resume_id . "")->result_array();
		$retrieve['resume_pelatihan'] = $this->db->query("select * from hrms_resume_pelatihan where resume_id=" . $resume_id . "")->result_array();
		$retrieve['resume_skill'] = $this->db->query("select * from hrms_resume_skill where resume_id=" . $resume_id . "")->result_array();
		$retrieve['resume_bahasa'] = $this->db->query("select * from hrms_resume_bahasa where resume_id=" . $resume_id . "")->result_array();


		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => null), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	public	function index_delete($segment_3 = '')
	{

		$id = $segment_3;
		$resume_id       = (int)$segment_3;
		$retrieveResume = $this->HrmsResumeModel->retrieve($resume_id);
		if (empty($retrieveResume)) {
			$this->set_response(array("status" => "NOT OK", "data" => "NOT FOUND Karyawan"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$res = $this->HrmsResumeModel->delete($id);
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

		if ($this->post('nama') == null || empty($this->post('nama'))) {
			$this->set_response(array("status" => "NOT OK", "data" => "Nama Kosong"), REST_Controller::HTTP_NOT_ACCEPTABLE);
			return;
		}
		if ($this->post('no_resume') == null || empty($this->post('no_resume'))) {
			$this->set_response(array("status" => "NOT OK", "data" => "No Resume Kosong"), REST_Controller::HTTP_NOT_ACCEPTABLE);
			return;
		}

		$config['upload_path']   = $this->get_path_image();
		$config['allowed_types'] = 'jpg|jpeg|png';
		$config['max_size']      = '0';
		$config['max_width']     = '0';
		$config['max_height']    = '0';
		$config['file_name']     = 'resume-' . url_title($this->post('nama', TRUE), '-', true);
		$this->upload->initialize($config);

		if (!empty($_FILES['userfile']['tmp_name']) and !$this->upload->do_upload()) {
			$data['error_upload'] = '<span class="text-error">' . $this->upload->display_errors() . '</span>';
			$error_upload = true;
		} else {
			$data['error_upload'] = '';
			$error_upload = false;
		}


		$no_resume           = $this->post('no_resume', TRUE);
		$nama          = $this->post('nama', TRUE);
		$posisi_id          = $this->post('posisi_id', TRUE);
		$tgl_terima          = $this->post('tgl_terima', TRUE);
		$jenis_kelamin = $this->post('jenis_kelamin', TRUE);
		$tempat_lahir  = $this->post('tempat_lahir', TRUE);
		$tgl_lahir     = $this->post('tgl_lahir', TRUE);
		$alamat        = $this->post('alamat', TRUE);
		$status_kawin = $this->post('status_pernikahan', TRUE);
		$golongan_darah = $this->post('golongan_darah', TRUE);
		$telp   = $this->post('telp', TRUE);
		$email   = $this->post('email', TRUE);
		$no_hp   = $this->post('no_hp', TRUE);
		$no_kk   = $this->post('no_kk', TRUE);
		$no_ktp   = $this->post('no_ktp', TRUE);
		$status_pajak   = $this->post('status_pajak', TRUE);
		$agama   = $this->post('agama', TRUE);
		$pendidikan_terakhir   = $this->post('pendidikan_terakhir', TRUE);
		$status_resume          = $this->post('status_resume', TRUE);
		$alokasi_karyawan          = $this->post('alokasi_karyawan', TRUE);
		$tgl_status_resume          = $this->post('tgl_status_resume', TRUE);



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


		# simpan data 
		$resume_id = $this->HrmsResumeModel->create(
			$no_resume,
			$nama,
			$jenis_kelamin,
			$tempat_lahir,
			$tgl_lahir,
			$alamat,
			$foto,
			$status_kawin,
			$golongan_darah,
			$posisi_id,
			$telp,
			$email,
			$no_hp,
			$no_kk,
			$no_ktp,
			$tgl_terima,
			$status_pajak,
			$agama,
			$pendidikan_terakhir,
			$status_resume,
			$tgl_status_resume,
			$alokasi_karyawan,
			$this->user_id

		);

		// $this->set_response(array("status" => "NOT OK", "data" => $this->post()), REST_Controller::HTTP_OK);
		// return;
		$resumeDetail = json_decode($this->post('details', TRUE));

		$this->db->where('resume_id', $resume_id);
		$this->db->delete('hrms_resume_pendidikan');
		$pendidikan = $resumeDetail->pendidikanItems;
		foreach ($pendidikan as $key => $value) {
			$this->db->insert("hrms_resume_pendidikan", array(
				'resume_id' => $resume_id,
				'nama_sekolah' => $value->nama_sekolah,
				'lokasi' => $value->lokasi_sekolah,
				'jurusan' => $value->jurusan,
				'no_ijazah' => $value->no_ijazah,
				'tgl_ijazah' => $value->tgl_ijazah,
			));
		}


		$this->db->where('resume_id', $resume_id);
		$this->db->delete('hrms_resume_pelatihan');
		$pelatihan = $resumeDetail->pelatihanItems;
		foreach ($pelatihan as $key => $value) {
			$this->db->insert("hrms_resume_pelatihan", array(
				'resume_id' => $resume_id,
				'nama_pelatihan' => $value->nama_pelatihan,
				'nama_penyelenggara' => $value->nama_penyelenggara,
				// 'biaya' => $value->biaya,
				'tgl_mulai' => $value->tgl_mulai,
				'tgl_akhir' => $value->tgl_akhir,
				'status' => $value->status,
			));
		}

		$this->db->where('resume_id', $resume_id);
		$this->db->delete('hrms_resume_pengalaman');
		$pengalaman = $resumeDetail->pengalamanItems;
		foreach ($pengalaman as $key => $value) {
			$this->db->insert("hrms_resume_pengalaman", array(
				'resume_id' => $resume_id,
				'perusahaan' => $value->perusahaan,
				'mulai' => $value->mulai,
				'akhir' => $value->akhir,
				'posisi' => $value->posisi,
			));
		}

		$this->db->where('resume_id', $resume_id);
		$this->db->delete('hrms_resume_profesional');
		$profesional = $resumeDetail->profesionalItems;
		foreach ($profesional as $key => $value) {
			$this->db->insert("hrms_resume_profesional", array(
				'resume_id' => $resume_id,
				'kualifikasi_id' => $value->resume_kualifikasi->id,
				'area_spesialisasi_id' => $value->resume_area_spesialisasi->id,
				'institusi' => $value->institusi,
				'tahun_lulus' => $value->tahun_lulus,
				'nilai' => $value->nilai,
			));
		}

		$this->db->where('resume_id', $resume_id);
		$this->db->delete('hrms_resume_bahasa');
		$bahasa = $resumeDetail->bahasaItems;

		foreach ($bahasa as $key => $value) {
			$this->db->insert("hrms_resume_bahasa", array(
				'resume_id' => $resume_id,
				'bahasa' => $value->bahasa,
				'kemampuan_bicara' => $value->kemampuan_bicara

			));
		}

		if (!empty($resume_id)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'hrms_resume', 'action' => 'new', 'entity_id' => null);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $resume_id), REST_Controller::HTTP_CREATED);
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

		if ($this->put('nama') == null || empty($this->put('nama'))) {
			$this->set_response(array("status" => "NOT OK", "data" => "Nama Kosong"), REST_Controller::HTTP_NOT_ACCEPTABLE);
			return;
		}
		if ($this->put('no_resume') == null || empty($this->put('no_resume'))) {
			$this->set_response(array("status" => "NOT OK", "data" => "NIK Kosong"), REST_Controller::HTTP_NOT_ACCEPTABLE);
			return;
		}
		$resume_id       = (int)$segment_3;
		$retrieveResume = $this->HrmsResumeModel->retrieve($resume_id, null);
		if (empty($retrieveResume)) {
			$this->set_response(array("status" => "NOT OK", "data" => "NOT FOUND Karyawan"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$no_resume           = $this->put('no_resume', TRUE);
		$nama          = $this->put('nama', TRUE);
		$posisi_id          = $this->put('posisi_id', TRUE);
		$tgl_terima          = $this->put('tgl_terima', TRUE);
		$jenis_kelamin = $this->put('jenis_kelamin', TRUE);
		$tempat_lahir  = $this->put('tempat_lahir', TRUE);
		$tgl_lahir     = $this->put('tgl_lahir', TRUE);
		$alamat        = $this->put('alamat', TRUE);
		$status_kawin = $this->put('status_pernikahan', TRUE);
		$golongan_darah = $this->put('golongan_darah', TRUE);
		$telp   = $this->put('telp', TRUE);
		$email   = $this->put('email', TRUE);
		$no_hp   = $this->put('no_hp', TRUE);
		$no_kk   = $this->put('no_kk', TRUE);
		$no_ktp   = $this->put('no_ktp', TRUE);
		$status_pajak   = $this->put('status_pajak', TRUE);
		$agama   = $this->put('agama', TRUE);
		$pendidikan_terakhir   = $this->put('pendidikan_terakhir', TRUE);
		$status_resume          = $this->put('status_resume', TRUE);
		$tgl_status_resume          = $this->put('tgl_status_resume', TRUE);
		$alokasi_karyawan          = $this->put('alokasi_karyawan', TRUE);
		
		# update 
		$this->HrmsResumeModel->update(
			$retrieveResume['id'],
			$no_resume,
			$nama,
			$jenis_kelamin,
			$tempat_lahir,
			$tgl_lahir,
			$alamat,
			$status_kawin,
			$golongan_darah,
			$posisi_id,
			$telp,
			$email,
			$no_hp,
			$no_kk,
			$no_ktp,
			$tgl_terima,
			$status_pajak,
			$agama,
			$pendidikan_terakhir,
			$status_resume,
			$tgl_status_resume,
			$alokasi_karyawan,
			$this->user_id
		);

		/* start audit trail */
		$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'hrms_resume', 'action' => 'edit', 'entity_id' => $resume_id);
		$this->db->insert('fwk_user_audit', $audit);
		/* end audit trail */
		$this->set_response(array("status" => "OK", "data" => $resume_id), REST_Controller::HTTP_CREATED);
	}

	function simpan_resume_keluarga_post($resume_id)
	{
		$resumeDetail      = json_decode($this->post('details', TRUE));
		// var_dump($resumeDetail);
		$this->db->where('resume_id', $resume_id);
		$this->db->delete('hrms_resume_keluarga');
		$keluarga = $resumeDetail->keluargaItems;

		foreach ($keluarga as $key => $value) {
			$this->db->insert("hrms_resume_keluarga", array(
				'resume_id' => $resume_id,
				'nama' => $value->nama_keluarga,
				'tempat_lahir' => $value->tempat_lahir_keluarga,
				'tgl_lahir' => $value->tanggal_lahir_keluarga,
				'status' => $value->status_keluarga
			));
		}
		$this->set_response(array("status" => "OK", "data" => $resume_id), REST_Controller::HTTP_CREATED);
	}
	function simpan_resume_pendidikan_post($resume_id)
	{
		$resumeDetail      = json_decode($this->post('details', TRUE));
		// var_dump($resumeDetail);

		$this->db->where('resume_id', $resume_id);
		$this->db->delete('hrms_resume_pendidikan');
		$pendidikan = $resumeDetail->pendidikanItems;
		foreach ($pendidikan as $key => $value) {
			$this->db->insert("hrms_resume_pendidikan", array(
				'resume_id' => $resume_id,
				'nama_sekolah' => $value->nama_sekolah,
				'lokasi' => $value->lokasi_sekolah,
				'jurusan' => $value->jurusan,
				'no_ijazah' => $value->no_ijazah,
				'tgl_ijazah' => $value->tgl_ijazah,
			));
		}
		$this->set_response(array("status" => "OK", "data" => $resume_id), REST_Controller::HTTP_CREATED);
	}
	function simpan_resume_bahasa_post($resume_id)
	{
		$resumeDetail      = json_decode($this->post('details', TRUE));
		// var_dump($resumeDetail);
		$this->db->where('resume_id', $resume_id);
		$this->db->delete('hrms_resume_bahasa');
		$bahasa = $resumeDetail->bahasaItems;

		foreach ($bahasa as $key => $value) {
			$this->db->insert("hrms_resume_bahasa", array(
				'resume_id' => $resume_id,
				'bahasa' => $value->bahasa,
				'kemampuan_bicara' => $value->kemampuan_bicara

			));
		}
		$this->set_response(array("status" => "OK", "data" => $resume_id), REST_Controller::HTTP_CREATED);
	}

	function simpan_resume_pelatihan_post($resume_id)
	{
		$resumeDetail      = json_decode($this->post('details', TRUE));

		$this->db->where('resume_id', $resume_id);
		$this->db->delete('hrms_resume_pelatihan');
		$pelatihan = $resumeDetail->pelatihanItems;
		foreach ($pelatihan as $key => $value) {
			$this->db->insert("hrms_resume_pelatihan", array(
				'resume_id' => $resume_id,
				'nama_pelatihan' => $value->nama_pelatihan,
				'nama_penyelenggara' => $value->nama_penyelenggara,
				// 'biaya' => $value->biaya,
				'tgl_mulai' => $value->tgl_mulai,
				'tgl_akhir' => $value->tgl_akhir,
				'status' => $value->status
			));
		}
		$this->set_response(array("status" => "OK", "data" => $resume_id), REST_Controller::HTTP_CREATED);
	}
	function simpan_resume_skill_post($resume_id)
	{
		$resumeDetail      = json_decode($this->post('details', TRUE));

		$this->db->where('resume_id', $resume_id);
		$this->db->delete('hrms_resume_skill');
		$skill = $resumeDetail->skillItems;
		foreach ($skill as $key => $value) {
			$this->db->insert("hrms_resume_skill", array(
				'resume_id' => $resume_id,
				'skill_id' => $value->resume_skill->id,
				'nilai' => $value->nilai,
			));
		}
		$this->set_response(array("status" => "OK", "data" => $resume_id), REST_Controller::HTTP_CREATED);
	}
	function simpan_resume_pengalaman_post($resume_id)
	{
		$resumeDetail      = json_decode($this->post('details', TRUE));

		$this->db->where('resume_id', $resume_id);
		$this->db->delete('hrms_resume_pengalaman');
		$pengalaman = $resumeDetail->pengalamanItems;
		foreach ($pengalaman as $key => $value) {
			$this->db->insert("hrms_resume_pengalaman", array(
				'resume_id' => $resume_id,
				'perusahaan' => $value->perusahaan,
				'mulai' => $value->mulai,
				'akhir' => $value->akhir,
				'posisi' => $value->posisi,
			));
		}
		$this->set_response(array("status" => "OK", "data" => $resume_id), REST_Controller::HTTP_CREATED);
	}
	function simpan_resume_profesional_post($resume_id)
	{
		$resumeDetail      = json_decode($this->post('details', TRUE));

		$this->db->where('resume_id', $resume_id);
		$this->db->delete('hrms_resume_profesional');
		$profesional = $resumeDetail->profesionalItems;
		foreach ($profesional as $key => $value) {
			$this->db->insert("hrms_resume_profesional", array(
				'resume_id' => $resume_id,
				'kualifikasi_id' => $value->resume_kualifikasi->id,
				'area_spesialisasi_id' => $value->resume_area_spesialisasi->id,
				'institusi' => $value->institusi,
				'tahun_lulus' => $value->tahun_lulus,
				'nilai' => $value->nilai,
			));
		}
		$this->set_response(array("status" => "OK", "data" => $resume_id), REST_Controller::HTTP_CREATED);
	}

	function getAll_get()
	{

		$retrieve = $this->HrmsResumeModel->retrieve_all_Karyawan();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => []), REST_Controller::HTTP_OK);
		}
	}
	function getAllAktif_get()
	{

		$retrieve = $this->HrmsResumeModel->retrieve_all_Karyawan_aktif();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => []), REST_Controller::HTTP_OK);
		}
	}
	function getAllAktifEstate_get()
	{

		$retrieve = $this->HrmsResumeModel->retrieve_all_Karyawan_aktif_estate();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => []), REST_Controller::HTTP_OK);
		}
	}
	function getByLokasiTugas_get($segment_3 = '')
	{

		$org_id = $segment_3;
		$retrieve = $this->HrmsResumeModel->retrieve_by_lokasi_tugas($org_id);

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


		$q = "SELECT a.*,b.nama as sub_bagian_nama, c.nama AS jabatan  FROM karyawan a 
		LEFT JOIN gbm_organisasi b ON a.sub_bagian_id=b.id
		LEFT JOIN payroll_jabatan c ON a.jabatan_id=c.id
		WHERE (tgl_keluar IS NULL OR left(tgl_keluar,7) >='" . substr($periode, 0, 7) . "' )
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

		$resume_id = $segment_3;
		$retrieve = $this->HrmsResumeModel->retrieve($resume_id, null, null);


		$retrieve['resume_bahasa'] = $this->db->query("select * from hrms_resume_bahasa where resume_id=" . $resume_id . "")->result_array();
		$retrieve['resume_pendidikan'] = $this->db->query("select * from hrms_resume_pendidikan where resume_id=" . $resume_id . "")->result_array();
		$retrieve['resume_profesional'] = $this->db->query("select a.*,b.nama as nama_kualifikasi,c.nama as nama_area_spesialisasi from hrms_resume_profesional a 
		inner join hrms_kualifikasi b on a.kualifikasi_id=b.id
		inner join hrms_area_spesialisasi c on a.area_spesialisasi_id=c.id
		 where resume_id=" . $resume_id . "")->result_array();
		$retrieve['resume_pengalaman'] = $this->db->query("select * from hrms_resume_pengalaman where resume_id=" . $resume_id . "")->result_array();
		$retrieve['resume_pelatihan'] = $this->db->query("select * from hrms_resume_pelatihan where resume_id=" . $resume_id . "")->result_array();
		$retrieve['resume_skill'] = $this->db->query("select a.*,b.nama as nama_skill from hrms_resume_skill a
		inner join hrms_skill b on a.skill_id=b.id
		where resume_id=" . $resume_id . "")->result_array();

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
         <h2>RESUME</h2>
		 <h3>Profile</h3>";
		if (!empty($retrieve['foto'])) {
			$html = $html . "
		 <img src=data:image/jpg;base64," . base64_encode(file_get_contents(get_url_image($retrieve['foto']))) . "
		 width='200' height='250'>";
		}

		$html = $html . "
		 <table  width='100%' style='border-collapse: collapse;'>
        <tr>
            <td width='100px'>no_resume</td>
            <td width='10px'>:</td>
            <td>" . $retrieve['no_resume'] . "</td>
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
            <td>No KTP</td>
            <td width='10px'>:</td>
            <td>" . $retrieve['no_ktp'] . "</td>
        </tr>
		<tr>
            <td>No KK</td>
            <td width='10px'>:</td>
            <td>" . $retrieve['no_kk'] . "</td>
        </tr>
		
        </table>
        ";


		// $html = $html . "
		// <h3>Keluarga</h3>
		//  <table  border='1' width='100%' style='border-collapse: collapse;'>
		// <tr>
		// <th >Nama</th>
		// <th>Status</th>
		// <th>Tempat,Tgl Lahir</th>
		// </tr>";
		// $no = 0;
		// foreach ($retrieve['resume_keluarga'] as $key => $data) {
		// 	$no++;
		// 	$html = $html . " <tr>
		// 	<td>" . $data['nama'] . "</td>
		//     <td>" . $data['status'] . "</td>
		//     <td>" . $data['tempat_lahir'] . "," . substr($data['tgl_lahir'], 0, 10) . "</td>    
		//     </tr>";
		// }

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
		foreach ($retrieve['resume_pendidikan'] as $key => $data) {
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
		<h3>profesional</h3>
		 <table  border='1' width='100%' style='border-collapse: collapse;'>
		<tr>
		<th>Kualifikasi</th>
		<th>Area Spesialisai</th>
		<th>Institusi</th>
		<th>Grade</th>
		<th>Tahun Lulus</th>
		</tr>";
		$no = 0;
		foreach ($retrieve['resume_profesional'] as $key => $data) {
			$no++;
			$html = $html . " <tr>
			<td>" . $data['nama_kualifikasi'] . "</td>
            <td>" . $data['nama_area_spesialisasi'] . "</td>
			 <td>" . $data['institusi'] . "</td>
			  <td>" . $data['nilai'] . "</td>
			   <td>" . $data['tahun_lulus'] . "</td>
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
		foreach ($retrieve['resume_pengalaman'] as $key => $data) {
			$no++;
			$html = $html . " <tr>
			<td>" . $data['perusahaan'] . "</td>
            <td>" . $data['mulai'] . "</td>
            <td>" . $data['akhir'] . "</td>    
			<td>" . $data['posisi'] . "</td>    
	
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
		foreach ($retrieve['resume_pelatihan'] as $key => $data) {
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
		<h3>SKILL</h3>
		 <table  border='1' width='100%' style='border-collapse: collapse;'>
		<tr>
		<th >Skill</th>
		<th>Grade</th>
	
		</tr>";
		$no = 0;
		foreach ($retrieve['resume_skill'] as $key => $data) {
			$no++;
			$html = $html . " <tr>
			<td>" . $data['nama_skill'] . "</td>
            <td>" . $data['nilai'] . "</td> 
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
		foreach ($retrieve['resume_bahasa'] as $key => $data) {
			$no++;
			$html = $html . " <tr>
			<td>" . $data['bahasa'] . "</td>
            <td>" . $data['kemampuan_bicara'] . "</td>
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
				$where = $where . " and (a.tgl_keluar IS NULL ) ";
				$judul_status = 'Aktif';
			} else {
				$where = $where . " and a.tgl_keluar IS NOT NULL  ";
				$judul_status = 'Non Aktif';
			}
		}
		$retrieveKaryawan = $this->db->query("select a.*,g.nama as lokasi_tugas,h.nama as sub_bagian, b.nama as departemen,c.nama as jabatan,d.nama as pangkat,e.nama as golongan,f.nama as tipe_karyawan
		from karyawan a left join payroll_department b on a.departemen_id=b.id 
	   left join payroll_jabatan c on a.jabatan_id=c.id 
	   left join payroll_pangkat d on a.pangkat_id=d.id 
	   left join payroll_golongan e on a.golongan_id=e.id 
	   left join payroll_tipe_karyawan f on a.tipe_resume_id=f.id
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
	<div class="kop-nama">' . get_company()['nama'] . '</div>
	<div class="kop-info"> ' . get_company()['alamat'] . '</div>
	<div class="kop-info">Telp : ' . get_company()['telp'] . '</div>
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
				<th>no_resume</th>
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
						' . $m['no_resume'] . ' 
							
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
							' . tgl_indo_normal($m['tgl_masuk']) . ' 
						</td>
						<td>
							' . tgl_indo_normal($m['tgl_keluar']) . ' 
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
		$resume_id       = (int)$segment_4;
		$retrieveResume = $this->HrmsResumeModel->retrieve($resume_id);
		if (empty($retrieveResume)) {
			exit('Data Karyawan tidak ditemukan');
		}

		# jika sebagai Karyawan, hanya profilnya dia yang bisa diupdate
		// if (is_Karyawan() and get_sess_data('user', 'id') != $retrieveResume['id']) {
		// 	exit('Akses ditolak');
		// }

		$retrieve_login = $this->LoginModel->retrieve(null, null, null, null, $retrieveResume['id']);
		$retrieve_role = $this->HrmsResumeModel->retrieve_role($retrieveResume['id']);
		$retrieveResume['is_admin'] = $retrieve_login['is_admin'];

		$data['status_id']    = $status_id;
		$data['resume_id']  = $resume_id;
		$data['Karyawan']     = $retrieveResume;
		$data['role']     = array_column($retrieve_role, 'role');


		// var_dump(array_column( $retrieve_role, 'role'));exit;

		if ($this->form_validation->run('Karyawan/role') == TRUE) {
			$role      = $this->post('role', TRUE);
			$this->HrmsResumeModel->delete_role($retrieveResume['id']);
			foreach ($role  as $post_role) {

				if (!empty($post_role)) {

					$this->HrmsResumeModel->insert_role($retrieveResume['id'], $post_role);
				}
			}


			// $this->session->set_flashdata('edit', get_alert('success', 'Role Karyawan berhasil diperbaharui.'));
			// redirect('Karyawan/edit_role/' . $status_id . '/' . $resume_id);
		}

		$this->twig->display('edit-Karyawan-role.html', $data);
	}

	/**
	 * Meghapus foto Karyawan
	 * @since 1.8
	 */
	function delete_foto($segment_3 = "", $segment_4 = "")
	{

		$resume_id       = (int)$segment_3;
		$retrieveResume = $this->HrmsResumeModel->retrieve($resume_id);
		if (empty($retrieveResume)) {
			show_error('Data Karyawan tidak ditemukan');
		}



		if (is_file(get_path_image($retrieveResume['foto']))) {
			unlink(get_path_image($retrieveResume['foto']));
		}

		if (is_file(get_path_image($retrieveResume['foto'], 'medium'))) {
			unlink(get_path_image($retrieveResume['foto'], 'medium'));
		}

		if (is_file(get_path_image($retrieveResume['foto'], 'small'))) {
			unlink(get_path_image($retrieveResume['foto'], 'small'));
		}

		$this->HrmsResumeModel->delete_foto($retrieveResume['id']);

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

		$resume_id       = (int)$segment_3;
		$retrieveResume = $this->HrmsResumeModel->retrieve($resume_id, null);
		if (empty($retrieveResume)) {
			$this->set_response(array("status" => "NOT OK", "data" => "NOT FOUND Karyawan"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}

		// $this->set_response(array("status" => "OK", "data" => 	$retrieveResume), REST_Controller::HTTP_OK);
		// return;

		// $retrieve_login = $this->LoginModel->retrieve(null, null, null, null, $retrieveResume['id']);
		// $retrieveResume['is_admin'] = $retrieve_login['is_admin'];

		// $data['status_id']    = $status_id;
		$data['resume_id']  = $resume_id;
		$data['Karyawan']     = $retrieveResume;

		$config['upload_path']   = $this->get_path_image();
		$config['allowed_types'] = 'jpg|jpeg|png';
		$config['max_size']      = '0';
		$config['max_width']     = '0';
		$config['max_height']    = '0';
		$config['file_name']     = 'Karyawan-' . url_title($retrieveResume['nama'], '-', true);
		$this->upload->initialize($config);


		if ($this->upload->do_upload()) {



			if (is_file($this->get_path_image($retrieveResume['foto']))) {
				unlink($this->get_path_image($retrieveResume['foto']));
			}

			if (is_file($this->get_path_image($retrieveResume['foto'], 'medium'))) {
				unlink($this->get_path_image($retrieveResume['foto'], 'medium'));
			}

			if (is_file($this->get_path_image($retrieveResume['foto'], 'small'))) {
				unlink($this->get_path_image($retrieveResume['foto'], 'small'));
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
			$data = array(
				'diubah_oleh' => $this->user_id,
				'diubah_tanggal' => date("Y-m-d H:i:s"),
				'foto' => $upload_data['file_name']

			);
			$this->db->where('id', $resume_id);
			$this->db->update('hrms_resume', $data);

			$this->set_response(array("status" => "OK", "data" => $resume_id), REST_Controller::HTTP_CREATED);
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


	function get_karyawan_raw_data_get()
	{

		$query = "select a.*,g.nama as lokasi_tugas,h.nama as sub_bagian, b.nama as departemen,c.nama as jabatan,d.nama as pangkat,e.nama as golongan,f.nama as tipe_karyawan
		from karyawan a left join payroll_department b on a.departemen_id=b.id 
	   left join payroll_jabatan c on a.jabatan_id=c.id 
	   left join payroll_pangkat d on a.pangkat_id=d.id 
	   left join payroll_golongan e on a.golongan_id=e.id 
	   left join payroll_tipe_karyawan f on a.tipe_resume_id=f.id
	   left join gbm_organisasi g on a.lokasi_tugas_id=g.id
	   left join gbm_organisasi h on a.sub_bagian_id=h.id
	   where 1=1
	  	";
		$res = $this->db->query($query)->result_array();


		$this->set_response($res, REST_Controller::HTTP_OK);
	}
	function laporanPivot_get()
	{

		$query = "	Select a.id, a.no_resume,a.nama,a.jenis_kelamin,a.tempat_lahir,a.tgl_lahir,a.alamat,a.status_kawin,a.telp,a.no_ktp,
		a.no_npwp,a.tgl_hbs_kontrak,a.tgl_masuk,a.status_pajak,a.status_kerja,
		  g.nama as lokasi_tugas,h.nama as sub_bagian, b.nama as departemen,c.nama as jabatan,d.nama as pangkat,
		  e.nama as golongan,f.nama as tipe_karyawan,agama
	from karyawan a left join payroll_department b on a.departemen_id=b.id 
   left join payroll_jabatan c on a.jabatan_id=c.id 
   left join payroll_pangkat d on a.pangkat_id=d.id 
   left join payroll_golongan e on a.golongan_id=e.id 
   left join payroll_tipe_karyawan f on a.tipe_resume_id=f.id
   left join gbm_organisasi g on a.lokasi_tugas_id=g.id
   left join gbm_organisasi h on a.sub_bagian_id=h.id
   where 1=1;
	  	";
		$res = $this->db->query($query)->result_array();
		$data['karyawan'] = json_encode($res);
		$html = $this->load->view('karyawan_pivot', $data, true);


		echo $html;
	}

	function import_mobile_get()
	{
		$retrieve = $this->db->query(" select * from karyawan where tgl_keluar IS NOT NULL  ")->result_array();
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => []), REST_Controller::HTTP_OK);
		}
	}
}
