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
use phpDocumentor\Reflection\Types\Nullable;
use Restserver\Libraries\REST_Controller;

class HrmsKaryawangaji extends BD_Controller // Rest_Controller
{
	public $user_id;
	public $theCredential; 
	public $data = array();
	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('HrmsKaryawanGajiModel');
		$this->load->model('HrmsPeriodeGajiModel');
		$this->load->model('M_DatatablesModel');
		$this->load->library('pdfgenerator');
		$this->load->model('GbmOrganisasiModel');
		$this->load->model('AccJurnalModel');
		$this->load->model('AccJurnalUpahModel');
		$this->load->helper("antech_helper");
		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id = $this->user_data->id;
		// $this->user_id = 40;
	}

	public function list_post()
	{
		$post = $this->post();

		$query  = "SELECT 
		b.*,
		c.nama as tipe_karyawan,
		d.nama as jabatan,e.nama as lokasi,
		a.is_catu,
		a.gapok,
		a.dibuat_tanggal AS tanggal_dibuat,
		a.diubah_tanggal AS tanggal_diubah,
		f.user_full_name AS dibuat,
		g.user_full_name AS diubah   FROM  payroll_karyawan_gaji a 
		inner join karyawan b on a.karyawan_id=b.id
		left join payroll_tipe_karyawan  c on b.tipe_karyawan_id=c.id 
		left join payroll_jabatan d on b.jabatan_id=d.id
		left join gbm_organisasi e on b.lokasi_tugas_id =e.id
		LEFT JOIN fwk_users f ON a.dibuat_oleh = f.id
		LEFT JOIN fwk_users g ON a.diubah_oleh = g.id";
		$search = array('b.nama', 'b.nip', 'c.nama', 'd.nama', 'e.nama');
		$where  = null;

		// $isWhere = null;

		$isWhere = " b.lokasi_tugas_id in
		(select location_id from fwk_users_location where user_id=" . $this->user_id . ")";



		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	function index_get($segment_3 = '')
	{
		$id = $segment_3;
		$retrieve = $this->HrmsKaryawanGajiModel->retrieve($id);

		$retrieve['detail'] = $this->HrmsKaryawanGajiModel->retrieve_detail($id);


		if (!empty($retrieve)) {
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
	function getAll_get()
	{

		$retrieve = $this->HrmsKaryawanGajiModel->retrieve_all_invkaryawan();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function index_post()
	{

		$data = $this->post();
		$data['dibuat_oleh']=$this->user_id;
		$data['diubah_oleh']=$this->user_id;
		$retrieve =  $this->HrmsKaryawanGajiModel->create($data);
		if (!empty($retrieve)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'hrms_karyawan_gaji', 'action' => 'new', 'entity_id' => $retrieve);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $this->post()['no_transaksi']), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function index_put($segment_3 = '')
	{


		$id = (int)$segment_3;

		$karyawan = $this->HrmsKaryawanGajiModel->retrieve($id);
		if (empty($karyawan)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
		// var_dump($this->put());
		// var_dump($karyawan);
		$data_post = $this->put();
		$data_post['diubah_oleh']=$this->user_id;

		$res =   $this->HrmsKaryawanGajiModel->update($karyawan['karyawan_id'], $data_post);
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'hrms_karyawan_gaji', 'action' => 'edit', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function index_delete($segment_3 = '')
	{

		$id = (int)$segment_3;
		$karyawan = $this->HrmsKaryawanGajiModel->retrieve($id);
		if (empty($karyawan)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$res =  $this->HrmsKaryawanGajiModel->delete($karyawan['karyawan_id']);
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id' => $id)), 'entity' => 'hrms_karyawan_gaji', 'action' => 'delete', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getGajiPerHari_get($karyawan_id)
	{
		$resGaji = $this->db->query("select * from payroll_karyawan_gaji where karyawan_id=" . $karyawan_id . " ")->row_array();
		$upahharian = ($resGaji['gapok'] / 25);
		$res = array(
			'rp_hk' => $upahharian,

		);

		if (!empty($res)) {
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function view_proses_get($tahun)
	{
		$hasil = array();
		for ($i = 1; $i < 13; $i++) {
			$bulan = sprintf("%02s", $i);
			$query = "select count(*)as jum from payroll_gaji_tr_hd
                 where tahun_bulan=" . $tahun . $bulan . "";
			$jum =  $this->db->query($query)->row_array();
			$hasil[] = array("bulan" => $bulan, "jumlah" => $jum['jum']);
		}
		$this->set_response(array("status" => "OK", "data" => $hasil), REST_Controller::HTTP_OK);
	}
	public function start_posting_payroll_post()
	{
		$id = $this->post('id');
		$data = $this->post();
		$periodeGaji = $this->HrmsPeriodeGajiModel->retrieve($id);
		if (empty($periodeGaji)) {
			$this->set_response(array("status" => "NOT OK", "data" => 'null'), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		// CEK PERIODE SDH ADA ATAU SDH CLOSE berdasarkan tgl akhir(tgl posting jurnal) proses periode payroll//
		$chk = cek_periode($periodeGaji['tgl_akhir'], $periodeGaji['lokasi_id']);
		if ($chk['status'] == false) {
			$this->set_response(array("status" => "NOT OK", "data" => $chk['message']), REST_Controller::HTTP_OK);
			return;
		}
		//==============//

		$lokasi = $this->GbmOrganisasiModel->retrieve($periodeGaji['lokasi_id']);
		if ($lokasi['tipe'] == 'ESTATE') {

			$hasil = $this->start_posting_payroll_estate($id);
		} else if ($lokasi['tipe'] == 'MILL') {
			$hasil = $this->start_posting_payroll_mill($id);
		} else {
		}

		if (($hasil['jum']) > 0) {

			$this->set_response(array("status" => "OK", "data" => 'Proses berhasil.' . $hasil['jum'] . " data diproses"), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data yang diproses"), REST_Controller::HTTP_OK);
		}
	}
	public function start_unposting_payroll_post()
	{
		$id = $this->post('id');
		$data = $this->post();
		$periodeGaji = $this->HrmsPeriodeGajiModel->retrieve($id);
		if (empty($periodeGaji)) {
			$this->set_response(array("status" => "NOT OK", "data" => 'null'), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		// CEK PERIODE SDH ADA ATAU SDH CLOSE berdasarkan tgl akhir(tgl posting jurnal) proses periode payroll//
		$chk = cek_periode($periodeGaji['tgl_akhir'], $periodeGaji['lokasi_id']);
		if ($chk['status'] == false) {
			$this->set_response(array("status" => "NOT OK", "data" => $chk['message']), REST_Controller::HTTP_OK);
			return;
		}
		//==============//

		$lokasi = $this->GbmOrganisasiModel->retrieve($periodeGaji['lokasi_id']);
		if ($lokasi['tipe'] == 'ESTATE') {

			/* Hapus Jurnal Jika sdh prh diposting */
			$this->AccJurnalModel->delete_by_ref_id_and_modul($periodeGaji['id'], 'ALK_GAJI_ESTATE');
			$this->AccJurnalModel->delete_by_ref_id_and_modul($periodeGaji['id'], 'ALK_GAJI');
			$this->AccJurnalModel->delete_by_ref_id_and_modul($periodeGaji['id'], 'ALK_GAJI_DIFF');
			$this->AccJurnalUpahModel->delete_by_ref_id_and_modul($periodeGaji['id'], 'ALK_GAJI');
			$this->AccJurnalUpahModel->delete_by_ref_id_and_modul($periodeGaji['id'], 'ALK_GAJI_ESTATE');
			$this->AccJurnalUpahModel->delete_by_ref_id_and_modul($periodeGaji['id'], 'ALK_GAJI_DIFF');
		} else if ($lokasi['tipe'] == 'MILL') {
			$this->AccJurnalModel->delete_by_ref_id_and_modul($periodeGaji['id'], 'ALK_GAJI_PKS');
		} else {
		}

		$this->db->where('id', $id);
		$this->db->update('payroll_periode_gaji', array('is_posting'    => '0', "tgl_posting" => date('Y-m-d H:i:s')));

		$this->set_response(array("status" => "OK", "data" => 'Proses unposting berhasil.'), REST_Controller::HTTP_CREATED);
	}
	public function start_posting_payroll_jamsostek_post()
	{
		$id = $this->post('id');
		$data = $this->post();
		$periodeGaji = $this->HrmsPeriodeGajiModel->retrieve($id);
		if (empty($periodeGaji)) {
			$this->set_response(array("status" => "NOT OK", "data" => 'null'), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		// CEK PERIODE SDH ADA ATAU SDH CLOSE berdasarkan tgl akhir(tgl posting jurnal) proses periode payroll//
		$chk = cek_periode($periodeGaji['tgl_akhir'], $periodeGaji['lokasi_id']);
		if ($chk['status'] == false) {
			$this->set_response(array("status" => "NOT OK", "data" => $chk['message']), REST_Controller::HTTP_OK);
			return;
		}
		//==============//
		$lokasi = $this->GbmOrganisasiModel->retrieve($periodeGaji['lokasi_id']);
		if ($lokasi['tipe'] == 'ESTATE') {

			$hasil = $this->start_posting_payroll_jamsostek_estate($id);
		} else if ($lokasi['tipe'] == 'MILL') {
			$hasil = $this->start_posting_payroll_jamsostek_mill($id);
		} else {
		}

		if (($hasil['jum']) > 0) {

			$this->set_response(array("status" => "OK", "data" => 'Proses berhasil.' . $hasil['jum'] . " data diproses"), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data yang diproses"), REST_Controller::HTTP_OK);
		}
	}
	public function start_unposting_payroll_jamsostek_post()
	{
		$id = $this->post('id');
		$data = $this->post();
		$periodeGaji = $this->HrmsPeriodeGajiModel->retrieve($id);
		if (empty($periodeGaji)) {
			$this->set_response(array("status" => "NOT OK", "data" => 'null'), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		// CEK PERIODE SDH ADA ATAU SDH CLOSE berdasarkan tgl akhir(tgl posting jurnal) proses periode payroll//
		$chk = cek_periode($periodeGaji['tgl_akhir'], $periodeGaji['lokasi_id']);
		if ($chk['status'] == false) {
			$this->set_response(array("status" => "NOT OK", "data" => $chk['message']), REST_Controller::HTTP_OK);
			return;
		}
		//==============//
		$lokasi = $this->GbmOrganisasiModel->retrieve($periodeGaji['lokasi_id']);
		if ($lokasi['tipe'] == 'ESTATE') {
			/* dELETE JURNAL JAMSOSTEK YG SDH DIPOSTING */
			$this->AccJurnalModel->delete_by_ref_id_and_modul($periodeGaji['id'], 'ALK_GAJI_BPJS_ESTATE');
			$this->AccJurnalModel->delete_by_ref_id_and_modul($periodeGaji['id'], 'ALK_GAJI_JAMSOSTEK_ESTATE');
			/* dELETE JURNAL UPAH JAMSOSTEK YG SDH DIPOSTING */
			$this->AccJurnalUpahModel->delete_by_ref_id_and_modul($periodeGaji['id'], 'ALK_GAJI_BPJS_ESTATE');
			$this->AccJurnalUpahModel->delete_by_ref_id_and_modul($periodeGaji['id'], 'ALK_GAJI_JAMSOSTEK_ESTATE');
		} else if ($lokasi['tipe'] == 'MILL') {
			$this->AccJurnalModel->delete_by_ref_id_and_modul($periodeGaji['id'], 'ALK_GAJI_JAMSOSTEK_MILL');
		} else {
		}

		$this->db->where('id', $id);
		$this->db->update('payroll_periode_gaji', array('is_posting_jamsostek'    => '0', "tgl_posting_jamsostek" => date('Y-m-d H:i:s')));


		$this->set_response(array("status" => "OK", "data" => 'Proses unposting berhasil.'), REST_Controller::HTTP_CREATED);
	}
	public function start_posting_payroll_catu_beras_post()
	{
		$id = $this->post('id');
		$data = $this->post();
		$periodeGaji = $this->HrmsPeriodeGajiModel->retrieve($id);
		if (empty($periodeGaji)) {
			$this->set_response(array("status" => "NOT OK", "data" => 'null'), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		// CEK PERIODE SDH ADA ATAU SDH CLOSE berdasarkan tgl akhir(tgl posting jurnal) proses periode payroll//
		$chk = cek_periode($periodeGaji['tgl_akhir'], $periodeGaji['lokasi_id']);
		if ($chk['status'] == false) {
			$this->set_response(array("status" => "NOT OK", "data" => $chk['message']), REST_Controller::HTTP_OK);
			return;
		}
		//==============//
		$lokasi = $this->GbmOrganisasiModel->retrieve($periodeGaji['lokasi_id']);
		if ($lokasi['tipe'] == 'ESTATE') {
			$hasil = $this->start_posting_payroll_catu_beras_estate($id);
		} else if ($lokasi['tipe'] == 'MILL') {
			$hasil = $this->start_posting_payroll_catu_beras_mill($id);
		} else {
		}

		if (($hasil['jum']) > 0) {

			$this->set_response(array("status" => "OK", "data" => 'Proses berhasil.' . $hasil['jum'] . " data diproses"), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data yang diproses"), REST_Controller::HTTP_OK);
		}
	}
	public function start_unposting_payroll_catu_beras_post()
	{
		$id = $this->post('id');
		$data = $this->post();
		$periodeGaji = $this->HrmsPeriodeGajiModel->retrieve($id);
		if (empty($periodeGaji)) {
			$this->set_response(array("status" => "NOT OK", "data" => 'null'), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		// CEK PERIODE SDH ADA ATAU SDH CLOSE berdasarkan tgl akhir(tgl posting jurnal) proses periode payroll//
		$chk = cek_periode($periodeGaji['tgl_akhir'], $periodeGaji['lokasi_id']);
		if ($chk['status'] == false) {
			$this->set_response(array("status" => "NOT OK", "data" => $chk['message']), REST_Controller::HTTP_OK);
			return;
		}
		//==============//
		$lokasi = $this->GbmOrganisasiModel->retrieve($periodeGaji['lokasi_id']);
		if ($lokasi['tipe'] == 'ESTATE') {
			$this->AccJurnalModel->delete_by_ref_id_and_modul($periodeGaji['id'], 'ALK_GAJI_CATU_BERAS_ESTATE');
		} else if ($lokasi['tipe'] == 'MILL') {
			$this->AccJurnalModel->delete_by_ref_id_and_modul($periodeGaji['id'], 'ALK_GAJI_CATU_BERAS_MILL');
		} else {
		}

		$this->db->where('id', $id);
		$this->db->update('payroll_periode_gaji', array('is_posting_catu_beras'    => '0', "tgl_posting_catu_beras" => date('Y-m-d H:i:s')));
		
		$this->set_response(array("status" => "OK", "data" => 'Proses unposting berhasil.'), REST_Controller::HTTP_CREATED);
	}
	public function start_posting_payroll_bpjs_kesehatan_post()
	{
		$id = $this->post('id');
		$data = $this->post();
		$periodeGaji = $this->HrmsPeriodeGajiModel->retrieve($id);
		if (empty($periodeGaji)) {
			$this->set_response(array("status" => "NOT OK", "data" => 'null'), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		// CEK PERIODE SDH ADA ATAU SDH CLOSE berdasarkan tgl akhir(tgl posting jurnal) proses periode payroll//
		$chk = cek_periode($periodeGaji['tgl_akhir'], $periodeGaji['lokasi_id']);
		if ($chk['status'] == false) {
			$this->set_response(array("status" => "NOT OK", "data" => $chk['message']), REST_Controller::HTTP_OK);
			return;
		}
		//==============//
		$lokasi = $this->GbmOrganisasiModel->retrieve($periodeGaji['lokasi_id']);
		if ($lokasi['tipe'] == 'ESTATE') {

			$hasil = $this->start_posting_payroll_bpjs_kesehatan_estate($id);
		} else if ($lokasi['tipe'] == 'MILL') {
			$hasil = $this->start_posting_payroll_bpjs_kesehatan_mill($id);
		} else {
		}

		if (($hasil['jum']) > 0) {

			$this->set_response(array("status" => "OK", "data" => 'Proses berhasil.' . $hasil['jum'] . " data diproses"), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data yang diproses"), REST_Controller::HTTP_OK);
		}
	}
	public function start_unposting_payroll_bpjs_kesehatan_post()
	{
		$id = $this->post('id');
		$data = $this->post();
		$periodeGaji = $this->HrmsPeriodeGajiModel->retrieve($id);
		if (empty($periodeGaji)) {
			$this->set_response(array("status" => "NOT OK", "data" => 'null'), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		// CEK PERIODE SDH ADA ATAU SDH CLOSE berdasarkan tgl akhir(tgl posting jurnal) proses periode payroll//
		$chk = cek_periode($periodeGaji['tgl_akhir'], $periodeGaji['lokasi_id']);
		if ($chk['status'] == false) {
			$this->set_response(array("status" => "NOT OK", "data" => $chk['message']), REST_Controller::HTTP_OK);
			return;
		}
		//==============//
		$lokasi = $this->GbmOrganisasiModel->retrieve($periodeGaji['lokasi_id']);
		if ($lokasi['tipe'] == 'ESTATE') {

			/* dELETE JURNAL JAMSOSTEK YG SDH DIPOSTING */
			$this->AccJurnalModel->delete_by_ref_id_and_modul($periodeGaji['id'], 'ALK_GAJI_BPJS_KES');
			$this->AccJurnalModel->delete_by_ref_id_and_modul($periodeGaji['id'], 'ALK_GAJI_BPJS_KES_ESTATE');
			/* dELETE JURNAL UPAH JAMSOSTEK YG SDH DIPOSTING */
			$this->AccJurnalUpahModel->delete_by_ref_id_and_modul($periodeGaji['id'], 'ALK_GAJI_BPJS_KES');
			$this->AccJurnalUpahModel->delete_by_ref_id_and_modul($periodeGaji['id'], 'ALK_GAJI_BPJS_KES_ESTATE');
		} else if ($lokasi['tipe'] == 'MILL') {
			$this->AccJurnalModel->delete_by_ref_id_and_modul($periodeGaji['id'], 'ALK_GAJI_BPJS_KES_MILL');
		} else {
		}

		$this->db->where('id', $id);
		$this->db->update('payroll_periode_gaji', array('is_posting_bpjs_kesehatan'    => '0', "tgl_posting_bpjs_kesehatan" => date('Y-m-d H:i:s')));
		$this->set_response(array("status" => "OK", "data" => 'Proses unposting berhasil.'), REST_Controller::HTTP_CREATED);
	}
	public function start_proses_payroll_post()
	{
		$id = $this->post('id');
		$data = $this->post();
		$periodeGaji = $this->HrmsPeriodeGajiModel->retrieve($id);
		if (empty($periodeGaji)) {
			$this->set_response(array("status" => "NOT OK", "data" => 'null'), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		if ($periodeGaji['is_posting']=='1' || $periodeGaji['is_posting_jamsostek']=='1' ||$periodeGaji['is_posting_bpjs_kesehatan']=='1' ||$periodeGaji['is_posting_catu_beras']=='1' ){
			$this->set_response(array("status" => "NOT OK", "data" => 'Periode Payroll sudah diposting'), REST_Controller::HTTP_OK);
			return;
		}
		$lokasi = $this->GbmOrganisasiModel->retrieve($periodeGaji['lokasi_id']);
		if ($lokasi['tipe'] == 'ESTATE') {
			$hasil = $this->start_proses_payroll_estate($id);
		} else if ($lokasi['tipe'] == 'MILL' || $lokasi['tipe'] == 'RO' || $lokasi['tipe'] == 'HO') {
			$hasil = $this->start_proses_payroll_mill($id);
		} else {
		}

		if (($hasil['jum']) > 0) {

			$this->set_response(array("status" => "OK", "data" => 'Proses berhasil.' . $hasil['jum'] . " data diproses"), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data yang diproses"), REST_Controller::HTTP_OK);
		}
	}

	public function start_proses_payroll_mill($id)
	{
		$id = $id;
		$periodeGaji = $this->HrmsPeriodeGajiModel->retrieve($id);
		if (empty($periodeGaji)) {
			$this->set_response(array("status" => "NOT OK", "data" => 'null'), REST_Controller::HTTP_NOT_FOUND);
			return;
		}


		// Hapus dulu gaji dt tr jika sudah ada pada bulan tsb.
		$q02 = "delete from payroll_gaji_tr_dt where id_hd in(select id from payroll_gaji_tr_hd
                 where periode_gaji_id=" . $periodeGaji['id'] . ")";
		$this->db->query($q02);
		$q01 = "delete from payroll_gaji_tr_hd
                 where periode_gaji_id=" . $periodeGaji['id'] . "";
		$this->db->query($q01);

		$catuKg = array();
		$catuRp = array();
		$qCatu = "SELECT * FROM  hrms_catuberas_setting ";
		$resCatu = $this->db->query($qCatu)->result_array();
		foreach ($resCatu as $c) {
			$catuKg[$c['status_karyawan']] = $c['jumlah_kg'];
			$catuRp[$c['status_karyawan']] = $c['jumlah_rupiah'];
		}

		/* hitung jumlah libur */
		$qLibur = "SELECT count(*)as jum_libur  FROM  hrms_libur where
		tanggal >='" . $periodeGaji['tgl_awal'] . "' and tanggal <='" . $periodeGaji['tgl_akhir'] . "' ";
		$libur = $this->db->query($qLibur)->row_array();
		$jum_libur = $libur['jum_libur'];

		/* hitung jumlah hari dlm 1 periode */
		$datetime1 = new DateTime($periodeGaji['tgl_awal']);
		$datetime2 = new DateTime($periodeGaji['tgl_akhir']);
		$interval = $datetime1->diff($datetime2);
		$jumlah_hari_sebulan = ($interval->days) + 1;

		/* hitung jumlah hari masuk 1 periode */
		$hari_masuk_efektif = $jumlah_hari_sebulan - $jum_libur;
		$hari_masuk_standar_perbulan = 25;
		// $this->set_response(array("status" => "OK", "data" => 'Proses berhasil.' . $hari_masuk_efektif . " data diproses"), REST_Controller::HTTP_OK);
		// $hasil['jum']=$jumlah_hari_sebulan;
		// return $hasil;exit();
		// Query utk nagambil master karyawan dan  gaji pokoknya 
		$q0 = "SELECT a.*,b.status_pajak,b.is_jht,b.is_jp,b.is_jks,b.sub_bagian_id,b.tipe_karyawan_id FROM  payroll_karyawan_gaji a inner join karyawan b on a.karyawan_id=b.id
		where b.status_id=1 and b.lokasi_tugas_id=" . $periodeGaji['lokasi_id'] . "";
		$arrhd = $this->db->query($q0)->result_array();

		$no = 0;
		$strNo = '';

		foreach ($arrhd as $hd) {
			$gaji_per_hari_standar_perbulan = $hd['gapok'] / $hari_masuk_standar_perbulan;
			$gaji_per_hari_efektif = $hd['gapok'] / $hari_masuk_efektif;
			$jum_hari_dibayar = 0;
			$jum_jam_lembur = 0;
			$jum_hari_hadir = 0;
			$jum_hari_catu = 0;
			$lembur = 0;
			$premi = 0;
			/* ngambil data absensi per periode */
			$qAbsensi = "SELECT * FROM payroll_absensi a inner join hrms_jenis_absensi b on a.jenis_absensi_id=b.id 
			 where a.karyawan_id= " . $hd['karyawan_id'] . " 
			 and tanggal >='" . $periodeGaji['tgl_awal'] . "' and tanggal <='" . $periodeGaji['tgl_akhir'] . "'
			 and b.tipe ='DIBAYAR';";
			$resAbsensi = $this->db->query($qAbsensi)->result_array();

			foreach ($resAbsensi as $absensiKaryawan) {
				$jum_hari_dibayar++;
				// $premi = $premi + $absensiKaryawan['premi'];
				if ($absensiKaryawan['kode'] == 'H') { // Jumlah Hadir 
					$jum_hari_hadir++;
					if ($hd['tanggal_efektif_catu'] <= $absensiKaryawan['tanggal']) {
						$jum_hari_catu++;
					}
				}
			}


			$hk_potongan_hari =	$hari_masuk_efektif - $jum_hari_dibayar;
			// $hk_potongan_gaji =	$hk_potongan_hari * $gaji_per_hari_efektif;
			$hk_potongan_gaji =	$hk_potongan_hari * $gaji_per_hari_standar_perbulan; // utk potongan upah harian= gapok/25
			$qLembur = "SELECT * FROM  payroll_lembur where karyawan_id= " . $hd['karyawan_id'] . " 
			 and tanggal >='" . $periodeGaji['tgl_awal'] . "' and tanggal <='" . $periodeGaji['tgl_akhir'] . "';";
			$resLembur = $this->db->query($qLembur)->result_array();
			foreach ($resLembur as $lemburKaryawan) {
				$jum_jam_lembur = $jum_jam_lembur + $lemburKaryawan['jumlah_jam'];
				$lembur = $lembur + $lemburKaryawan['nilai_lembur'];
			}
			$pph = 0;
			// $jht = $hd['gapok'] * (2 / 100);
			// $jkn = $hd['gapok'] * (1 / 100);
			// $jp = $hd['gapok'] * (1 / 100);
			// $jht_perusahaan = $hd['gapok'] * (3.7 / 100);
			// $jp_perusahaan = $hd['gapok'] * (2 / 100);
			// $jkn_perusahaan = $hd['gapok'] * (2 / 100);
			// $jkn_perusahaan = $hd['gapok'] * (4 / 100);


			if ($hd['is_jht'] == 1) {
				$jht = $hd['gapok_bpjs'] * (2 / 100);
				$jht_perusahaan = $hd['gapok_bpjs'] * (3.7 / 100);
				$jkk_perusahaan = $hd['gapok_bpjs'] * (0.89 / 100);
			} else {
				$jht = 0;
				$jht_perusahaan = 0;
				$jkk_perusahaan = 0;
			}
			if ($hd['is_jks'] == 1) {
				$jkn = $hd['gapok_bpjs_kes'] * (1 / 100);
				$jkn_perusahaan = $hd['gapok_bpjs_kes'] * (4 / 100);
			} else {
				$jkn = 0;
				$jkn_perusahaan = 0;
			}
			if ($hd['is_jp'] == 1) {
				$jp = $hd['gapok_bpjs'] * (1 / 100);
				$jp_perusahaan = $hd['gapok_bpjs'] * (2 / 100);
				$jkm_perusahaan = $hd['gapok_bpjs'] * (0.3 / 100);
			} else {
				$jp = 0;
				$jp_perusahaan = 0;
				$jkm_perusahaan = 0;
			}
			// $jkk_perusahaan = $hd['gapok_bpjs'] * (0.89 / 100);
			// $jkm_perusahaan = $hd['gapok_bpjs'] * (0.3 / 100);

			if (isset($catuKg[$hd['status_pajak']])) {
				// $jumlah_kg_catu = $jum_hari_dibayar * $catuKg[$hd['status_pajak']];
				// $nilai_rp_catu = $jum_hari_dibayar * $catuKg[$hd['status_pajak']] * $catuRp[$hd['status_pajak']];
				$jumlah_kg_catu = $jum_hari_catu * $catuKg[$hd['status_pajak']];
				$nilai_rp_catu = $jum_hari_catu * $catuKg[$hd['status_pajak']] * $catuRp[$hd['status_pajak']];
			}
			if ($hd['is_catu'] == 0) {
				$jumlah_kg_catu = 0;
				$nilai_rp_catu = 0;
			}
			if ($jum_hari_dibayar > 0) {


				$datahd = array(
					'lokasi_id'    => $periodeGaji['lokasi_id'],
					'divisi_id'    => $hd['sub_bagian_id'],
					'tipe_karyawan_id'    => $hd['tipe_karyawan_id'],
					'karyawan_id'    => $hd['karyawan_id'],
					'tanggal' => date('Y-m-d'),
					'tahun_bulan' => '',
					'ket' => "Gaji " . $periodeGaji['nama'],
					'periode_gaji_id' =>  $periodeGaji['id'],
					'jumlah_hari_masuk' =>  $jum_hari_dibayar,
					'premi' =>  $premi,
					'jumlah_jam_lembur' =>  $jum_jam_lembur,
					'lembur' =>  $lembur,
					'gapok' =>   $hd['gapok'],
					'pph' => 0,
					'jht' =>  $jht,
					'jp' =>   $jp,
					'jkn' =>  $jkn,
					'jht_perusahaan' =>   $jht_perusahaan,
					'jp_perusahaan' =>   $jp_perusahaan,
					'jkn_perusahaan' =>   $jkn_perusahaan,
					'jkk_perusahaan' =>   $jkk_perusahaan,
					'jkm_perusahaan' =>   $jkm_perusahaan,
					'hk_potongan_gaji' => $hk_potongan_gaji,
					'hk_potongan_hari' => $hk_potongan_hari,
					'jumlah_kg_catu' => $jumlah_kg_catu,
					'nilai_rp_catu' => $nilai_rp_catu,
					'jumlah_hari_catu' => $jum_hari_catu
				);
				$this->db->insert('payroll_gaji_tr_hd', $datahd);
				$id_hd = $this->db->insert_id();
				$tanggal = date('Y-m-d');
				$jumlahPendapatan = ($hd['gapok'] + $lembur + $premi);

				/* cari gaji dari komponen gaji selain gapok detail Seperti tunjangan,potongan dll */
				$q1 = "SELECT b.*,c.jenis,c.nama FROM  payroll_karyawan_gaji a inner join payroll_gaji b on a.karyawan_id=b.karyawan_id
			inner join payroll_tipe_gaji c on b.tipe_gaji=c.id
                 where a.karyawan_id=" . $hd['karyawan_id'] . " order by a.karyawan_id";
				$arrdt = $this->db->query($q1)->result_array();
				/* Gaji komponen */
				$gaji_pendapatan = 0;
				$gaji_potongan = 0;
				foreach ($arrdt as $dt) {
					$nilai_per_hari_efektif = $dt['nilai'] / $hari_masuk_efektif; // cari nilai perhari
					$nilai_dibayar = $jum_hari_hadir *	$nilai_per_hari_efektif;
					if ($dt['jenis'] == 1) { // pendapatan bulanan

						$nilai = $nilai_dibayar;
						$gaji_pendapatan = $gaji_pendapatan + $nilai;
					} elseif ($dt['jenis'] == 2) { // pendapatan harian{
						$nilai = $jum_hari_hadir * $dt['nilai'];
						$gaji_pendapatan = $gaji_pendapatan + $nilai;
					} else if ($dt['jenis'] == 3) { // Pendapatan Fixed
						$nilai = $dt['nilai'];
						$gaji_pendapatan = $gaji_pendapatan + $nilai;
					} else if ($dt['jenis'] == 0) { // Potongan
						$nilai = $dt['nilai'];
						$gaji_potongan = $gaji_potongan + $nilai;
					}

					$datadt = array(
						'id_hd'    => $id_hd,
						'tipe_gaji' => $dt['tipe_gaji'],
						'nilai' => $nilai
					);
					// Insert ke transaksi dt
					$this->db->insert('payroll_gaji_tr_dt', $datadt);
				}
				/* End Gaji komponen */


				/* Gaji transaksi Pendapatan Karyawan*/
				$q1 = "SELECT sum(nilai)as nilai,tipe_gaji_id  FROM  payroll_pendapatan
								where karyawan_id=" . $hd['karyawan_id'] . "
								 and tanggal >='" . $periodeGaji['tgl_awal'] . "' 
								 and tanggal <='" . $periodeGaji['tgl_akhir'] . "'
								 group by  tipe_gaji_id";
				$arrPend = $this->db->query($q1)->result_array();
				foreach ($arrPend as $dt) {
					$gaji_pendapatan = $gaji_pendapatan +  $dt['nilai'];
					$datadt = array(
						'id_hd'    => $id_hd,
						'tipe_gaji' => $dt['tipe_gaji_id'],
						'nilai' => $dt['nilai']
					);
					// Insert ke transaksi dt
					$this->db->insert('payroll_gaji_tr_dt', $datadt);
				}
				/* END Gaji transaksi Pendapatan Karyawan*/

				/* Gaji transaksi Potongn Karyawan*/
				$q1 = "SELECT sum(nilai)as nilai,tipe_gaji_id  FROM  payroll_potongan
								where karyawan_id=" . $hd['karyawan_id'] . "
								 and tanggal >='" . $periodeGaji['tgl_awal'] . "' 
								 and tanggal <='" . $periodeGaji['tgl_akhir'] . "'
								 group by  tipe_gaji_id";
				$arrPot = $this->db->query($q1)->result_array();
				foreach ($arrPot as $dt) {
					$gaji_potongan = $gaji_potongan +  $dt['nilai'];
					$datadt = array(
						'id_hd'    => $id_hd,
						'tipe_gaji' => $dt['tipe_gaji_id'],
						'nilai' => $dt['nilai']
					);
					// Insert ke transaksi dt
					$this->db->insert('payroll_gaji_tr_dt', $datadt);
				}
				/* END Gaji transaksi Potongn Karyawan*/

				$jumlahGaji = $jumlahPendapatan + $gaji_pendapatan - ($jht + $jkn + $jp) - $gaji_potongan;

				switch ($hd['status_pajak']) {
					case 'TK/0':
						$pph = ((($jumlahGaji * 12) - (54000000)) * 0.05) / 12;
						break;
					case 'TK/1':
						$pph = ((($jumlahGaji * 12) - (58500000)) * 0.05) / 12;
						break;
					case 'TK/2':
						$pph = ((($jumlahGaji * 12) - (63000000)) * 0.05) / 12;
						break;
					case 'TK/3':
						$pph = ((($jumlahGaji * 12) - (63000000)) * 0.05) / 12;
						break;
					case 'K/0':
						$pph = ((($jumlahGaji * 12) - (58500000)) * 0.05) / 12;
						break;
					case 'K/1':
						$pph = ((($jumlahGaji * 12) - (63000000)) * 0.05) / 12;
						break;
					case 'K/2':
						$pph = ((($jumlahGaji * 12) - (67500000)) * 0.05) / 12;
						break;
					case 'K/3':
						$pph = ((($jumlahGaji * 12) - (72000000)) * 0.05) / 12;
						break;

					default:
						$pph = 0;
						break;
				}

				if ($pph < 0) {
					$pph = 0;
				}
				$pph = 0; // tidak ada pph kita nol kan saja
				$this->db->where('id', $id_hd);
				$this->db->update('payroll_gaji_tr_hd', array('pph'    => $pph, 'gaji_pendapatan'    => $gaji_pendapatan, 'gaji_potongan'    => $gaji_potongan));
			}
		}

		$this->db->where('id', $periodeGaji['id']);
		$this->db->update('payroll_periode_gaji', array('status'    => '1', "tgl_proses" => date('Y-m-d')));
		$query = "select count(*)as jum from payroll_gaji_tr_hd
                 where periode_gaji_id=" . $periodeGaji['id'] . "";
		$hasil =  $this->db->query($query)->row_array();
		// var_dump($hasil);exit;
		return $hasil;
		// if (($hasil['jum']) > 0) {

		// 	$this->set_response(array("status" => "OK", "data" => 'Proses berhasil.' . $hasil['jum'] . " data diproses"), REST_Controller::HTTP_CREATED);
		// } else {
		// 	$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data yang diproses"), REST_Controller::HTTP_OK);
		// }
	}
	public function start_proses_payroll_mill_new($id)
	{
		$id = $id;
		$periodeGaji = $this->HrmsPeriodeGajiModel->retrieve($id);
		if (empty($periodeGaji)) {
			$this->set_response(array("status" => "NOT OK", "data" => 'null'), REST_Controller::HTTP_NOT_FOUND);
			return;
		}


		// Hapus dulu gaji dt tr jika sudah ada pada bulan tsb.
		$q02 = "delete from payroll_gaji_tr_dt where id_hd in(select id from payroll_gaji_tr_hd
                 where periode_gaji_id=" . $periodeGaji['id'] . ")";
		$this->db->query($q02);
		$q01 = "delete from payroll_gaji_tr_hd
                 where periode_gaji_id=" . $periodeGaji['id'] . "";
		$this->db->query($q01);

		$catuKg = array();
		$catuRp = array();
		$qCatu = "SELECT * FROM  hrms_catuberas_setting ";
		$resCatu = $this->db->query($qCatu)->result_array();
		foreach ($resCatu as $c) {
			$catuKg[$c['status_karyawan']] = $c['jumlah_kg'];
			$catuRp[$c['status_karyawan']] = $c['jumlah_rupiah'];
		}

		/* hitung jumlah libur */
		$qLibur = "SELECT count(*)as jum_libur  FROM  hrms_libur where
		tanggal >='" . $periodeGaji['tgl_awal'] . "' and tanggal <='" . $periodeGaji['tgl_akhir'] . "' ";
		$libur = $this->db->query($qLibur)->row_array();
		$jum_libur = $libur['jum_libur'];

		/* hitung jumlah hari dlm 1 periode */
		$d1 = new DateTime($periodeGaji['tgl_awal']);
		$d2 = new DateTime($periodeGaji['tgl_akhir']);
		$interval = $d1->diff($d2);
		$jumlah_hari_sebulan = ($interval->days) + 1;


		/* hitung jumlah hari masuk 1 periode */
		$hari_masuk_efektif = $jumlah_hari_sebulan - $jum_libur;
		$hari_masuk_standar_perbulan = 25;
		// $this->set_response(array("status" => "OK", "data" => 'Proses berhasil.' . $hari_masuk_efektif . " data diproses"), REST_Controller::HTTP_OK);
		// $hasil['jum']=$jumlah_hari_sebulan;
		// return $hasil;exit();
		// Query utk nagambil master karyawan dan  gaji pokoknya 
		$q0 = "SELECT a.*,b.status_pajak,b.is_jht,b.is_jp,b.is_jks FROM  payroll_karyawan_gaji a inner join karyawan b on a.karyawan_id=b.id
		where b.status_id=1 and b.lokasi_tugas_id=" . $periodeGaji['lokasi_id'] . "";
		$arrhd = $this->db->query($q0)->result_array();

		$no = 0;
		$strNo = '';

		foreach ($arrhd as $hd) {
			$gaji_per_hari_standar_perbulan = $hd['gapok'] / $hari_masuk_standar_perbulan;
			$gaji_per_hari_efektif = $hd['gapok'] / $hari_masuk_efektif;
			$jum_hari_dibayar = 0;
			$jum_jam_lembur = 0;
			$jum_hari_hadir = 0;
			$lembur = 0;
			$premi = 0;
			$jum_hk = 0;

			while ($d1 <= $d2) {
				$hk = 0;
				$jum_absen = 0;
				$hk = 0;
				$premi = 0;
				$lembur = 0;
				$upah = 0;

				$tgl = $d1->format('Y-m-d');
				/* ngambil data absensi per tgl */
				$qAbsensi = "SELECT * FROM payroll_absensi a inner join hrms_jenis_absensi b on a.jenis_absensi_id=b.id 
				where a.karyawan_id= " . $hd['karyawan_id'] . " 
				and tanggal >='" . $d1 . "' 
				and b.tipe ='DIBAYAR';";
				$resAbsensi = $this->db->query($qAbsensi)->result_array();

				foreach ($resAbsensi as $absensiKaryawan) {
					$jum_hari_dibayar++;
					// $premi = $premi + $absensiKaryawan['premi'];
					if ($absensiKaryawan['kode'] == 'H') { // Jumlah Hadir 
						$jum_hari_hadir++;
						$hk = $hk++;
					}
				}
				/* ngambil data bkm workshop per periode */
				$resWorkshop =	$this->db->query("select * from wrk_kegiatan_ht a inner join 
				wrk_kegiatan_dt b on a.id=b.wrk_kegiatan_id  where b.karyawan_id= " . $hd['karyawan_id'] . " 
				and tanggal ='" . $tgl . "'  ")->result_array();
				$premi_workshop = 0;
				$upah_workshop = 0;
				foreach ($resWorkshop as $workshop) {
					$hk = $hk + $workshop['jumlah_hk'];
					$jum_absen++;
					$jum_hari_hadir++;
					$premi_workshop = $premi_workshop + $workshop['premi'];
					$lembur = $lembur + $workshop['premi']; // masukan ke lembur
					$upah_workshop = $upah_workshop + $workshop['rupiah_hk'];
				}
				if ($hk > 1) {
					$hk = 1;
				}
				$jum_hk = $jum_hk + $hk;
				if ($jum_absen > 1) {
					$jum_absen = 1;
				}
			}

			$hk_potongan_hari =	$hari_masuk_efektif - $jum_hk;
			if ($hk_potongan_hari < 0) {
				$hk_potongan_hari = 0;
			}
			// $hk_potongan_gaji =	$hk_potongan_hari * $gaji_per_hari_efektif;
			$hk_potongan_gaji =	$hk_potongan_hari * $gaji_per_hari_standar_perbulan; // utk potongan upah harian= gapok/25
			$qLembur = "SELECT * FROM  payroll_lembur where karyawan_id= " . $hd['karyawan_id'] . " 
			 and tanggal >='" . $periodeGaji['tgl_awal'] . "' and tanggal <='" . $periodeGaji['tgl_akhir'] . "';";
			$resLembur = $this->db->query($qLembur)->result_array();
			foreach ($resLembur as $lemburKaryawan) {
				$jum_jam_lembur = $jum_jam_lembur + $lemburKaryawan['jumlah_jam'];
				$lembur = $lembur + $lemburKaryawan['nilai_lembur'];
			}
			$pph = 0;
			// $jht = $hd['gapok'] * (2 / 100);
			// $jkn = $hd['gapok'] * (1 / 100);
			// $jp = $hd['gapok'] * (1 / 100);
			// $jht_perusahaan = $hd['gapok'] * (3.7 / 100);
			// $jp_perusahaan = $hd['gapok'] * (2 / 100);
			// $jkn_perusahaan = $hd['gapok'] * (2 / 100);
			// $jkn_perusahaan = $hd['gapok'] * (4 / 100);
			if ($hd['is_jht'] == 1) {
				$jht = $hd['gapok'] * (2 / 100);
				$jht_perusahaan = $hd['gapok'] * (3.7 / 100);
			} else {
				$jht = 0;
				$jht_perusahaan = 0;
			}
			if ($hd['is_jks'] == 1) {
				$jkn = $hd['gapok'] * (1 / 100);
				$jkn_perusahaan = $hd['gapok'] * (4 / 100);
			} else {
				$jkn = 0;
				$jkn_perusahaan = 0;
			}
			if ($hd['is_jp'] == 1) {
				$jp = $hd['gapok'] * (1 / 100);
				$jp_perusahaan = $hd['gapok'] * (2 / 100);
			} else {
				$jp = 0;
				$jp_perusahaan = 0;
			}


			$jkk_perusahaan = $hd['gapok'] * (0.89 / 100);
			$jkm_perusahaan = $hd['gapok'] * (0.3 / 100);

			if (isset($catuKg[$hd['status_pajak']])) {
				// $jumlah_kg_catu = $jum_hari_dibayar * $catuKg[$hd['status_pajak']];
				// $nilai_rp_catu = $jum_hari_dibayar * $catuKg[$hd['status_pajak']] * $catuRp[$hd['status_pajak']];
				$jumlah_kg_catu = $jum_hari_hadir * $catuKg[$hd['status_pajak']];
				$nilai_rp_catu = $jum_hari_hadir * $catuKg[$hd['status_pajak']] * $catuRp[$hd['status_pajak']];
			}
			if ($hd['is_catu'] == 0) {
				$jumlah_kg_catu = 0;
				$nilai_rp_catu = 0;
			}
			$datahd = array(
				'karyawan_id'    => $hd['karyawan_id'],
				'tanggal' => date('Y-m-d'),
				'tahun_bulan' => '',
				'ket' => "Gaji " . $periodeGaji['nama'],
				'periode_gaji_id' =>  $periodeGaji['id'],
				'jumlah_hari_masuk' =>  $jum_hari_dibayar,
				'premi' =>  $premi,
				'jumlah_jam_lembur' =>  $jum_jam_lembur,
				'lembur' =>  $lembur,
				'gapok' =>   $hd['gapok'],
				'pph' => 0,
				'jht' =>  $jht,
				'jp' =>   $jp,
				'jkn' =>  $jkn,
				'jht_perusahaan' =>   $jht_perusahaan,
				'jp_perusahaan' =>   $jp_perusahaan,
				'jkn_perusahaan' =>   $jkn_perusahaan,
				'jkk_perusahaan' =>   $jkk_perusahaan,
				'jkm_perusahaan' =>   $jkm_perusahaan,
				'hk_potongan_gaji' => $hk_potongan_gaji,
				'hk_potongan_hari' => $hk_potongan_hari,
				'jumlah_kg_catu' => $jumlah_kg_catu,
				'nilai_rp_catu' => $nilai_rp_catu
			);
			$this->db->insert('payroll_gaji_tr_hd', $datahd);
			$id_hd = $this->db->insert_id();
			$tanggal = date('Y-m-d');
			$jumlahPendapatan = ($hd['gapok'] + $lembur + $premi);

			/* cari gaji dari komponen gaji selain gapok detail Seperti tunjangan,potongan dll */
			$q1 = "SELECT b.*,c.jenis,c.nama FROM  payroll_karyawan_gaji a inner join payroll_gaji b on a.karyawan_id=b.karyawan_id
			inner join payroll_tipe_gaji c on b.tipe_gaji=c.id
                 where a.karyawan_id=" . $hd['karyawan_id'] . " order by a.karyawan_id";
			$arrdt = $this->db->query($q1)->result_array();
			/* Gaji komponen */
			$gaji_pendapatan = 0;
			$gaji_potongan = 0;
			foreach ($arrdt as $dt) {
				$nilai_per_hari_efektif = $dt['nilai'] / $hari_masuk_efektif; // cari nilai perhari
				$nilai_dibayar = $jum_hari_hadir *	$nilai_per_hari_efektif;

				if ($dt['jenis'] == 1) { // pendapatan bulanan

					$nilai = $nilai_dibayar;
					$gaji_pendapatan = $gaji_pendapatan + $nilai;
				} elseif ($dt['jenis'] == 2) { // pendapatan harian{
					$nilai = $jum_hari_hadir * $dt['nilai'];
					$gaji_pendapatan = $gaji_pendapatan + $nilai;
				} else { // Potongan
					$nilai = $dt['nilai'];
					$gaji_potongan = $gaji_potongan + $nilai;
				}
				//echo ( $nilai);

				$datadt = array(
					'id_hd'    => $id_hd,
					'tipe_gaji' => $dt['tipe_gaji'],
					'nilai' => $nilai
				);
				// Insert ke transaksi dt
				$this->db->insert('payroll_gaji_tr_dt', $datadt);
				// if ($dt['jenis'] == 1 || $dt['jenis'] == 2) {
				// 	$jumlahPendapatan = $jumlahPendapatan + $nilai;
				// }
			}
			$jumlahGaji = $jumlahPendapatan + $gaji_pendapatan - ($jht + $jkn + $jp) - $gaji_potongan;

			switch ($hd['status_pajak']) {
				case 'TK/0':
					$pph = ((($jumlahGaji * 12) - (54000000)) * 0.05) / 12;
					break;
				case 'TK/1':
					$pph = ((($jumlahGaji * 12) - (58500000)) * 0.05) / 12;
					break;
				case 'TK/2':
					$pph = ((($jumlahGaji * 12) - (63000000)) * 0.05) / 12;
					break;
				case 'TK/3':
					$pph = ((($jumlahGaji * 12) - (63000000)) * 0.05) / 12;
					break;
				case 'K/0':
					$pph = ((($jumlahGaji * 12) - (58500000)) * 0.05) / 12;
					break;
				case 'K/1':
					$pph = ((($jumlahGaji * 12) - (63000000)) * 0.05) / 12;
					break;
				case 'K/2':
					$pph = ((($jumlahGaji * 12) - (67500000)) * 0.05) / 12;
					break;
				case 'K/3':
					$pph = ((($jumlahGaji * 12) - (72000000)) * 0.05) / 12;
					break;

				default:
					$pph = 0;
					break;
			}

			if ($pph < 0) {
				$pph = 0;
			}
			$pph = 0; // tidak ada pph kita nol kan saja
			$this->db->where('id', $id_hd);
			$this->db->update('payroll_gaji_tr_hd', array('pph'    => $pph, 'gaji_pendapatan'    => $gaji_pendapatan, 'gaji_potongan'    => $gaji_potongan));
		}

		$this->db->where('id', $periodeGaji['id']);
		$this->db->update('payroll_periode_gaji', array('status'    => '1', "tgl_proses" => date('Y-m-d')));
		$query = "select count(*)as jum from payroll_gaji_tr_hd
                 where periode_gaji_id=" . $periodeGaji['id'] . "";
		$hasil =  $this->db->query($query)->row_array();
		// var_dump($hasil);exit;
		return $hasil;
		// if (($hasil['jum']) > 0) {

		// 	$this->set_response(array("status" => "OK", "data" => 'Proses berhasil.' . $hasil['jum'] . " data diproses"), REST_Controller::HTTP_CREATED);
		// } else {
		// 	$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data yang diproses"), REST_Controller::HTTP_OK);
		// }
	}
	public function start_proses_payroll_estate()
	{
		$id = $this->post('id');
		$afdeling_id = $this->post('afdeling_id');
		$periodeGaji = $this->HrmsPeriodeGajiModel->retrieve($id);
		if (empty($periodeGaji)) {
			$this->set_response(array("status" => "NOT OK", "data" => 'null'), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		// $this->set_response(array("status" => "NOT OK", "data" => $periodeGaji), REST_Controller::HTTP_NOT_FOUND);

		// return;
		// Hapus dulu gaji dt tr jika sudah ada pada bulan tsb.
		if ($afdeling_id) {
			$q02 = "delete from payroll_gaji_tr_dt where id_hd in(select payroll_gaji_tr_hd.id from payroll_gaji_tr_hd
			inner join karyawan on payroll_gaji_tr_hd.karyawan_id=karyawan.id
			where periode_gaji_id=" . $periodeGaji['id'] . " and karyawan.sub_bagian_id=" . $afdeling_id . ")";
			$this->db->query($q02);
			$q01 = "delete payroll_gaji_tr_hd from payroll_gaji_tr_hd 
					inner join karyawan on payroll_gaji_tr_hd.karyawan_id=karyawan.id
                 where periode_gaji_id=" . $periodeGaji['id'] . " and karyawan.sub_bagian_id=" . $afdeling_id . "";
			$this->db->query($q01);
		} else {
			$q02 = "delete from payroll_gaji_tr_dt where id_hd in(select id from payroll_gaji_tr_hd
                 where periode_gaji_id=" . $periodeGaji['id'] . ")";
			$this->db->query($q02);
			$q01 = "delete from payroll_gaji_tr_hd 
					 where periode_gaji_id=" . $periodeGaji['id'] . "";
			$this->db->query($q01);
		}


		$catuKg = array();
		$catuRp = array();
		$qCatu = "SELECT * FROM  hrms_catuberas_setting ";
		$resCatu = $this->db->query($qCatu)->result_array();
		foreach ($resCatu as $c) {
			$catuKg[$c['status_karyawan']] = $c['jumlah_kg'];
			$catuRp[$c['status_karyawan']] = $c['jumlah_rupiah'];
		}

		$arr_hari_libur = array();
		/* ambil hari libur */
		$qHariLibur = "SELECT *  FROM  hrms_libur where
		tanggal >='" . $periodeGaji['tgl_awal'] . "' and tanggal <='" . $periodeGaji['tgl_akhir'] . "' ";
		$res_harilibur = $this->db->query($qHariLibur)->result_array();
		foreach ($res_harilibur as $key => $l) {
			$arr_hari_libur[$l['tanggal']] = $l['tanggal'];
			# code...
		}


		/* hitung jumlah libur */
		$qLibur = "SELECT count(*)as jum_libur  FROM  hrms_libur where
		tanggal >='" . $periodeGaji['tgl_awal'] . "' and tanggal <='" . $periodeGaji['tgl_akhir'] . "' ";
		$libur = $this->db->query($qLibur)->row_array();
		$jum_libur = $libur['jum_libur'];

		/* hitung jumlah hari dlm 1 periode */
		$d1 = new DateTime($periodeGaji['tgl_awal']);
		$d2 = new DateTime($periodeGaji['tgl_akhir']);
		$interval = $d1->diff($d2);
		$jumlah_hari_sebulan = ($interval->days) + 1;

		/* hitung jumlah hari masuk 1 periode */
		$hari_masuk_efektif = $jumlah_hari_sebulan - $jum_libur;
		$hari_masuk_standar_perbulan = 25;
		// Query utk nagambil master karyawan dan  gaji pokoknya 
		$q0 = "SELECT a.*,b.status_pajak,b.is_jht,b.is_jp,b.is_jks,b.sub_bagian_id,b.tipe_karyawan_id FROM  payroll_karyawan_gaji a inner join karyawan b on a.karyawan_id=b.id
		where b.status_id=1
		and tgl_masuk<='" . $periodeGaji['tgl_akhir'] . "'
		and b.lokasi_tugas_id=" . $periodeGaji['lokasi_id'] . "";

		if ($afdeling_id) {
			$q0 = $q0 . " and b.sub_bagian_id =" . $afdeling_id . "";
		}
		$arrhd = $this->db->query($q0)->result_array();

		$no = 0;
		$strNo = '';
		try {
			foreach ($arrhd as $hd) {
				$jum_absen_perkaryawan = 0;
				$jum_hk_perkaryawan = 0;
				$upah = 0;
				$upah_absensi = 0;
				$upah_panen = 0;
				$upah_pemeliharaan = 0;
				$upah_traksi = 0;
				$upah_workshop = 0;
				$upah_umum = 0;
				$premi_umum = 0;
				$premi_panen = 0;
				$premi_pemeliharaan = 0;
				$premi_traksi = 0;
				$premi_workshop = 0;
				$upah_panen_kerani = 0;
				$upah_panen_kerani = 0;
				$upah_panen_mandor = 0;
				$premi_panen_mandor = 0;
				$upah_pemeliharaan_kerani = 0;
				$premi_pemeliharaan_kerani = 0;
				$upah_pemeliharaan_mandor = 0;
				$premi_pemeliharaan_mandor = 0;
				$upah_perkaryawan = 0;
				$premi_perkaryawan = 0;
				$denda_perkaryawan = 0;
				$lembur_perkaryawan = 0;
				$jum_jam_lembur = 0;
				$jum_hari_catu = 0;
				$jum_hari_dapat_premi = 0;
				$no++;

				$d1 = new DateTime($periodeGaji['tgl_awal']);
				$jum_hk = 0;
				$gaji_per_hari_standar_perbulan = $hd['gapok'] / $hari_masuk_standar_perbulan;
				$gaji_per_hari_efektif = $hd['gapok'] / $hari_masuk_efektif;
				$jum_hk_cuti = 0;
				/* tipe_karyawan_id: 1(KHT),2(KHL),3(PKWT),4(NSB),5(STAFF) */
				// if ($hd['tipe_karyawan_id'] == 2) {
				// 	$gaji_per_hari_efektif = $hd['gapok'] / 25;
				// } else {
				// 	$gaji_per_hari_efektif = $hd['gapok'] / $hari_masuk_efektif;
				// }

				while ($d1 <= $d2) {
					$jum_absen = 0;
					$hk = 0;
					$premi = 0;
					$lembur = 0;
					$upah = 0;
					$hari_catu = 0;
					$tgl = $d1->format('Y-m-d');
					$denda = 0;

					/* ngambil data absensi per periode */ // Estate idak pake menu Absensi //
					// $qAbsensi = "SELECT * FROM payroll_absensi a inner join hrms_jenis_absensi b on a.jenis_absensi_id=b.id 
					// 	where a.karyawan_id= " . $hd['karyawan_id'] . " 
					// 	and tanggal ='" . $tgl . "' 
					// 	and b.tipe ='DIBAYAR';";
					// $resAbsensi = $this->db->query($qAbsensi)->result_array();
					// $jum_hari_dibayar = 0;

					// $jum_hari_hadir = 0;
					// $lembur = 0;
					// $premi_absensi = 0;
					// $upah_absensi = 0;
					// foreach ($resAbsensi as $absensiKaryawan) {
					// 	if ($absensiKaryawan['kode'] == 'H') { 
					// 		$hk++;
					// 		$jum_absen++;
					// 		$upah_absensi = $upah_absensi + $gaji_per_hari_efektif;
					// 	}
					// }
					$qLembur = "SELECT * FROM  payroll_lembur where karyawan_id= " . $hd['karyawan_id'] . " 
					and	tanggal ='" . $tgl . "' ;";
					$resLembur = $this->db->query($qLembur)->result_array();
					foreach ($resLembur as $lemburKaryawan) {
						$jum_jam_lembur = $jum_jam_lembur + $lemburKaryawan['jumlah_jam'];
						$lembur = $lembur + $lemburKaryawan['nilai_lembur'];
					}
					/* ngambil data bkm Umum per periode */
					$resUmum =	$this->db->query("SELECT *,b.jumlah_hk AS jumlah_hk,c.kode AS kode_absensi from est_bkm_umum_ht a inner join 
					est_bkm_umum_dt b on a.id=b.bkm_umum_id 
					INNER JOIN hrms_jenis_absensi c ON b.jenis_absensi_id=c.id
					 where b.karyawan_id= " . $hd['karyawan_id'] . " 
					and tanggal ='" . $tgl . "' ")->result_array();
					$premi_umum = 0;
					$upah_umum = 0;
					foreach ($resUmum as $umum) {
						if ($umum['kode_absensi'] != 'H') {
							$jum_hk_cuti++;
						} else {
							if ($hd['tanggal_efektif_catu'] <= $tgl) {
								$hari_catu = $hari_catu + $umum['jumlah_hk'];
							}
						}
						$hk = $hk + $umum['jumlah_hk'];
						$jum_absen++;

						$premi_umum = $premi_umum + $umum['premi'];
						$upah_umum = $upah_umum + $umum['rupiah_hk'];
					}

					/* ngambil data bkm panen per periode */
					$resPanen =	$this->db->query("select * from est_bkm_panen_ht a inner join 
					est_bkm_panen_dt b on a.id=b.bkm_panen_id  where karyawan_id= " . $hd['karyawan_id'] . "  
					and tanggal ='" . $tgl . "' and is_premi_kontanan=0 ")->result_array();
					$premi_panen = 0;
					$upah_panen = 0;
					$denda_panen = 0;
					foreach ($resPanen as $panen) {
						$hk = $hk + $panen['jumlah_hk'];
						if ($hd['tanggal_efektif_catu'] <= $tgl) {
							$hari_catu = $hari_catu + $panen['jumlah_hk'];
						}

						$jum_absen++;

						$upah_panen = $upah_panen + $panen['rp_hk'];
						$premi_panen = $premi_panen + ($panen['premi_panen'] + $panen['premi_brondolan']);
						$denda_panen = $denda_panen + $panen['denda_panen'];
					}

					/* ngambil data bkm pemeliharaan per periode */
					$resPemeliharaan =	$this->db->query("select * from est_bkm_pemeliharaan_ht a inner join 
						est_bkm_pemeliharaan_dt b on a.id=b.bkm_pemeliharaan_id  where karyawan_id= " . $hd['karyawan_id'] . "  
						and tanggal ='" . $tgl . "' and is_premi_kontanan=0 ")->result_array();
					$premi_pemeliharaan = 0;
					$upah_pemeliharaan = 0;
					$denda_pemeliharaan = 0;
					foreach ($resPemeliharaan as $pemeliharaan) {
						$hk = $hk + $pemeliharaan['jumlah_hk'];
						if ($hd['tanggal_efektif_catu'] <= $tgl) {
							$hari_catu = $hari_catu + $pemeliharaan['jumlah_hk'];
						}
						$jum_absen++;

						$premi_pemeliharaan = $premi_pemeliharaan + $pemeliharaan['premi'];
						$upah_pemeliharaan = $upah_pemeliharaan + $pemeliharaan['rupiah_hk'];
						$denda_pemeliharaan = $denda_pemeliharaan + $pemeliharaan['denda_pemeliharaan'];
					}

					/* ngambil data bkm Traksi per periode */
					$resTraksi =	$this->db->query("select * from trk_kegiatan_kendaraan_ht a inner join 
				trk_kegiatan_kendaraan_dt b on a.id=b.trk_kegiatan_kendaraan_id  where b.karyawan_id= " . $hd['karyawan_id'] . " 
				and tanggal ='" . $tgl . "'  ")->result_array();
					$premi_traksi = 0;
					$upah_traksi = 0;
					$denda_traksi = 0;
					foreach ($resTraksi as $traksi) {
						$hk = $hk + $traksi['jumlah_hk'];
						if ($hd['tanggal_efektif_catu'] <= $tgl) {
							$hari_catu = $hari_catu + $traksi['jumlah_hk'];
						}
						$jum_absen++;

						$premi_traksi = $premi_traksi + $traksi['premi'];
						$upah_traksi = $upah_traksi + $traksi['rupiah_hk'];
						$denda_traksi = $denda_traksi + $traksi['denda_traksi'];
					}

					/* ngambil data bkm workshop per periode */
					$resWorkshop =	$this->db->query("select * from wrk_kegiatan_ht a inner join 
				wrk_kegiatan_dt b on a.id=b.wrk_kegiatan_id  where b.karyawan_id= " . $hd['karyawan_id'] . " 
				and tanggal ='" . $tgl . "'  ")->result_array();
					$premi_workshop = 0;
					$upah_workshop = 0;
					foreach ($resWorkshop as $workshop) {
						$hk = $hk + $workshop['jumlah_hk'];
						if ($hd['tanggal_efektif_catu'] <= $tgl) {
							$hari_catu = $hari_catu + $workshop['jumlah_hk'];
						}
						$jum_absen++;

						$premi_workshop = $premi_workshop + $workshop['premi'];
						$upah_workshop = $upah_workshop + $workshop['rupiah_hk'];
					}

					/* ngambil data Mandor dari bkm panen per periode */
					$resPanen =	$this->db->query("select * from est_bkm_panen_ht where mandor_id= " . $hd['karyawan_id'] . " 
		 					and tanggal ='" . $tgl . "' and is_premi_kontanan=0")->result_array();
					$premi_panen_mandor = 0;
					$upah_panen_mandor = 0;
					$denda_panen_mandor = 0;
					foreach ($resPanen as $panen) {
						$hk = $hk + $panen['jumlah_hk_mandor'];
						if ($hd['tanggal_efektif_catu'] <= $tgl) {
							$hari_catu = $hari_catu + $panen['jumlah_hk_mandor'];
						}
						$jum_absen++;

						$upah_panen_mandor = $upah_panen_mandor + $panen['rp_hk_mandor']; //	$gaji_per_hari_efektif; //
						$premi_panen_mandor = $premi_panen_mandor + $panen['premi_mandor'];
						$denda_panen_mandor = $denda_panen_mandor + $panen['denda_mandor'];
					}
					/* ngambil data Krani dari bkm panen per periode */
					$resPanen =	$this->db->query("select * from est_bkm_panen_ht where kerani_id= " . $hd['karyawan_id'] . "  
					and tanggal ='" . $tgl . "'  and is_premi_kontanan=0")->result_array();
					$premi_panen_kerani = 0;
					$upah_panen_kerani = 0;
					$denda_panen_kerani = 0;
					foreach ($resPanen as $panen) {
						$hk = $hk + $panen['jumlah_hk_kerani'];
						if ($hd['tanggal_efektif_catu'] <= $tgl) {
							$hari_catu = $hari_catu + $panen['jumlah_hk_kerani'];
						}
						$jum_absen++;

						$upah_panen_kerani = $upah_panen_kerani + $panen['rp_hk_kerani']; //	$gaji_per_hari_efektif; //
						$premi_panen_kerani = $premi_panen_kerani + $panen['premi_kerani'];
						$denda_panen_kerani = $denda_panen_kerani + $panen['denda_kerani'];
					}

					/* ngambil data Mandor dari bkm Pemeliharaan per periode */
					$resPanen =	$this->db->query("select * from est_bkm_pemeliharaan_ht where mandor_id= " . $hd['karyawan_id'] . " 
		 					and tanggal ='" . $tgl . "' and is_premi_kontanan=0")->result_array();
					$premi_pemeliharaan_mandor = 0;
					$upah_pemeliharaan_mandor = 0;
					$denda_pemeliharaan_mandor = 0;
					foreach ($resPanen as $panen) {
						$hk = $hk + $panen['jumlah_hk_mandor'];
						if ($hd['tanggal_efektif_catu'] <= $tgl) {
							$hari_catu = $hari_catu + $panen['jumlah_hk_mandor'];
						}
						$jum_absen++;

						$upah_pemeliharaan_mandor = $upah_pemeliharaan_mandor + $panen['rp_hk_mandor']; //	$gaji_per_hari_efektif; // 
						$premi_pemeliharaan_mandor = $premi_pemeliharaan_mandor + $panen['premi_mandor'];
						$denda_pemeliharaan_mandor = $denda_pemeliharaan_mandor + $panen['denda_mandor'];
					}
					/* ngambil data Krani dari bkm Pemeliharaan per periode */
					$resPanen =	$this->db->query("select * from est_bkm_pemeliharaan_ht where kerani_id= " . $hd['karyawan_id'] . "  
					and tanggal ='" . $tgl . "' and is_premi_kontanan=0 ")->result_array();
					$premi_pemeliharaan_kerani = 0;
					$upah_pemeliharaan_kerani = 0;
					$denda_pemeliharaan_kerani = 0;
					foreach ($resPanen as $panen) {
						$hk = $hk + $panen['jumlah_hk_kerani'];
						if ($hd['tanggal_efektif_catu'] <= $tgl) {
							$hari_catu = $hari_catu + $panen['jumlah_hk_kerani'];
						}
						$jum_absen++;

						$upah_pemeliharaan_kerani = $upah_pemeliharaan_kerani + $panen['rp_hk_kerani']; //	$gaji_per_hari_efektif; //
						$premi_pemeliharaan_kerani = $premi_pemeliharaan_kerani + $panen['premi_kerani'];
						$denda_pemeliharaan_kerani = $denda_pemeliharaan_kerani + $panen['denda_kerani'];
					}
					if ($hk > 1) {
						$hk = 1;
					}
					if ($hari_catu > 1) {
						$hari_catu = 1;
					}
					$jum_hk = $jum_hk + $hk;
					$jum_hari_catu = $jum_hari_catu + $hari_catu;
					if ($jum_absen > 1) {
						$jum_absen = 1;
					}
					$jum_absen_perkaryawan = $jum_absen_perkaryawan + $jum_hk;
					$upah = $upah_umum + $upah_absensi + $upah_panen + $upah_pemeliharaan + $upah_traksi + $upah_workshop + $upah_panen_mandor + $upah_panen_kerani + $upah_pemeliharaan_mandor + $upah_pemeliharaan_kerani;
					//$premi = $premi_umum + $premi_panen + $premi_pemeliharaan + $premi_traksi + $premi_workshop + $premi_panen_mandor + $premi_panen_kerani + $premi_pemeliharaan_mandor + $premi_pemeliharaan_kerani - $denda_panen;
					$premi = $premi_umum + $premi_panen + $premi_pemeliharaan + $premi_traksi + $premi_workshop + $premi_panen_mandor + $premi_panen_kerani + $premi_pemeliharaan_mandor + $premi_pemeliharaan_kerani;
					$denda = $denda_panen + $denda_pemeliharaan + $denda_panen_kerani + $denda_panen_mandor + $denda_pemeliharaan_kerani + $denda_pemeliharaan_mandor + $denda_traksi;

					// if ($upah > $gaji_per_hari_efektif) {
					// 	$upah = $gaji_per_hari_efektif;
					// }
					$upah_perkaryawan = $upah_perkaryawan + $upah;
					$premi_perkaryawan = $premi_perkaryawan + $premi;
					$denda_perkaryawan = $denda_perkaryawan + $denda;
					$lembur_perkaryawan = $lembur_perkaryawan + $lembur;
					$jum_absen_str = $jum_absen == 0 ? "" : $jum_absen;

					$d1->modify('+1 day');
				}
				$hk_potongan_hari =	$hari_masuk_efektif - $jum_hk; // cari sisa tdk masuk
				if ($hk_potongan_hari < 0) {
					$hk_potongan_hari = 0;
				}
				$hk_potongan_gaji =	$hk_potongan_hari * $gaji_per_hari_standar_perbulan; // utk potongan upah harian= gapok/25

				$pph = 0;
				if ($hd['is_jht'] == 1) {
					$jht = $hd['gapok_bpjs'] * (2 / 100);
					$jht_perusahaan = $hd['gapok_bpjs'] * (3.7 / 100);
					$jkk_perusahaan = $hd['gapok_bpjs'] * (0.89 / 100);
				} else {
					$jht = 0;
					$jht_perusahaan = 0;
					$jkk_perusahaan = 0;
				}
				if ($hd['is_jks'] == 1) {
					$jkn = $hd['gapok_bpjs_kes'] * (1 / 100);
					$jkn_perusahaan = $hd['gapok_bpjs_kes'] * (4 / 100);
				} else {
					$jkn = 0;
					$jkn_perusahaan = 0;
				}
				if ($hd['is_jp'] == 1) {
					$jp = $hd['gapok_bpjs'] * (1 / 100);
					$jp_perusahaan = $hd['gapok_bpjs'] * (2 / 100);
					$jkm_perusahaan = $hd['gapok_bpjs'] * (0.3 / 100);
				} else {
					$jp = 0;
					$jp_perusahaan = 0;
					$jkm_perusahaan = 0;
				}


				if (isset($catuKg[$hd['status_pajak']])) {
					$jumlah_kg_catu = ($jum_hari_catu) * $catuKg[$hd['status_pajak']];
					$nilai_rp_catu = ($jum_hari_catu) * $catuKg[$hd['status_pajak']] * $catuRp[$hd['status_pajak']];
				}
				if ($hd['is_catu'] == 0) {
					$jumlah_kg_catu = 0;
					$nilai_rp_catu = 0;
				}

				/* hapus dulu transaksi yg sdh ada sblmnya*/
				// $q02 = "delete from payroll_gaji_tr_dt where id_hd in(select id from payroll_gaji_tr_hd
				//  where periode_gaji_id=" . $periodeGaji['id'] . " and karyawan_id=" . $hd['karyawan_id'] . " )";
				// $this->db->query($q02);
				// $q01 = "delete from payroll_gaji_tr_hd
				// 		where periode_gaji_id=" . $periodeGaji['id'] . " and karyawan_id=" . $hd['karyawan_id'] . "";
				// $this->db->query($q01);

				if ($jum_hk > 0) {
					$datahd = array(
						'lokasi_id'    => $periodeGaji['lokasi_id'],
						'divisi_id'    => $hd['sub_bagian_id'],
						'tipe_karyawan_id'    => $hd['tipe_karyawan_id'],
						'karyawan_id'    => $hd['karyawan_id'],
						'tanggal' => date('Y-m-d'),
						'tahun_bulan' => '',
						'ket' => "Gaji " . $periodeGaji['nama'],
						'periode_gaji_id' =>  $periodeGaji['id'],
						'jumlah_hari_masuk' =>  $jum_hk,
						'premi' =>  $premi_perkaryawan,
						'denda' =>  $denda_perkaryawan,
						'jumlah_jam_lembur' =>  $jum_jam_lembur,
						'lembur' =>  $lembur,
						'gapok' =>   $hd['gapok'],
						'pph' => 0,
						'jht' =>  $jht,
						'jp' =>   $jp,
						'jkn' =>  $jkn,
						'jht_perusahaan' =>   $jht_perusahaan,
						'jp_perusahaan' =>   $jp_perusahaan,
						'jkn_perusahaan' =>   $jkn_perusahaan,
						'jkk_perusahaan' =>   $jkk_perusahaan,
						'jkm_perusahaan' =>   $jkm_perusahaan,
						'hk_potongan_gaji' => $hk_potongan_gaji,
						'hk_potongan_hari' => $hk_potongan_hari,
						'jumlah_kg_catu' => $jumlah_kg_catu,
						'nilai_rp_catu' => $nilai_rp_catu,
						'jumlah_hari_catu' => $jum_hari_catu,
					);
					$this->db->insert('payroll_gaji_tr_hd', $datahd);
					$id_hd = $this->db->insert_id();
					$tanggal = date('Y-m-d');
					$jumlahPendapatan = ($hd['gapok'] + $lembur + $premi);

					/* cari gaji dari komponen gaji selain gapok detail Seperti tunjangan,potongan dll */
					$q1 = "SELECT b.*,c.jenis,c.nama FROM  payroll_karyawan_gaji a inner join payroll_gaji b on a.karyawan_id=b.karyawan_id
					inner join payroll_tipe_gaji c on b.tipe_gaji=c.id
               	  where a.karyawan_id=" . $hd['karyawan_id'] . " order by a.karyawan_id";
					$arrdt = $this->db->query($q1)->result_array();
					/* Gaji komponen */
					$gaji_pendapatan = 0;
					$gaji_potongan = 0;
					foreach ($arrdt as $dt) {
						$nilai_per_hari_efektif = $dt['nilai'] / $hari_masuk_efektif; // cari nilai perhari
						$nilai_dibayar = $jum_absen_perkaryawan  *	$nilai_per_hari_efektif;

						if ($dt['jenis'] == 1) { // pendapatan bulanan

							$nilai = $nilai_dibayar;
							$gaji_pendapatan = $gaji_pendapatan + $nilai;
						} elseif ($dt['jenis'] == 2) { // pendapatan harian{
							// $nilai = $jum_absen_perkaryawan  * $dt['nilai'];
							$jum_hari_dapat_premi = 0;
							$jum_hari_dapat_premi = $jum_hk - $jum_hk_cuti; //  hitung hari masuk dpt hk dikurang cuti(tdk masuk) 
							$tgl_loop = new DateTime($periodeGaji['tgl_awal']);
							while ($tgl_loop <= $d2) {
								if ($dt['tanggal_efektif'] > $tgl_loop) {
									if (!$arr_hari_libur[$tgl_loop]) {
										$jum_hari_dapat_premi =	$jum_hari_dapat_premi - 1;
									}
								}
								$tgl_loop->modify('+1 day');
							}
							$nilai = $jum_hari_dapat_premi  * $dt['nilai'];
							$gaji_pendapatan = $gaji_pendapatan + $nilai;
						} else if ($dt['jenis'] == 3) { // Pendapatan Fixed
							$nilai = $dt['nilai'];
							$gaji_pendapatan = $gaji_pendapatan + $nilai;
						} else if ($dt['jenis'] == 0) { // Potongan
							$nilai = $dt['nilai'];
							$gaji_potongan = $gaji_potongan + $nilai;
						}
						//echo ( $nilai);

						$datadt = array(
							'id_hd'    => $id_hd,
							'tipe_gaji' => $dt['tipe_gaji'],
							'nilai' => $nilai
						);
						// Insert ke transaksi dt
						$this->db->insert('payroll_gaji_tr_dt', $datadt);
					}
					/* END Gaji transaksi Pendapatan Karyawan*/

					/* Gaji transaksi Pendapatan Karyawan*/
					$q1 = "SELECT sum(nilai)as nilai,tipe_gaji_id  FROM  payroll_pendapatan
					where karyawan_id=" . $hd['karyawan_id'] . "
					and tanggal >='" . $periodeGaji['tgl_awal'] . "' 
					and tanggal <='" . $periodeGaji['tgl_akhir'] . "'
					group by  tipe_gaji_id";
					$arrPend = $this->db->query($q1)->result_array();
					foreach ($arrPend as $dt) {
						$gaji_pendapatan = $gaji_pendapatan +  $dt['nilai'];
						$datadt = array(
							'id_hd'    => $id_hd,
							'tipe_gaji' => $dt['tipe_gaji_id'],
							'nilai' => $dt['nilai']
						);
						// Insert ke transaksi dt
						$this->db->insert('payroll_gaji_tr_dt', $datadt);
					}
					/* END Gaji transaksi Pendapatan Karyawan*/

					/* Gaji transaksi Potongn Karyawan*/
					$q1 = "SELECT sum(nilai)as nilai,tipe_gaji_id  FROM  payroll_potongan
					where karyawan_id=" . $hd['karyawan_id'] . "
					and tanggal >='" . $periodeGaji['tgl_awal'] . "' 
					and tanggal <='" . $periodeGaji['tgl_akhir'] . "'
					group by  tipe_gaji_id";
					$arrPot = $this->db->query($q1)->result_array();
					foreach ($arrPot as $dt) {
						$gaji_potongan = $gaji_potongan +  $dt['nilai'];
						$datadt = array(
							'id_hd'    => $id_hd,
							'tipe_gaji' => $dt['tipe_gaji_id'],
							'nilai' => $dt['nilai']
						);
						// Insert ke transaksi dt
						$this->db->insert('payroll_gaji_tr_dt', $datadt);
					}
					/* END Gaji transaksi Potongn Karyawan*/

					$jumlahGaji = $jumlahPendapatan + $gaji_pendapatan - ($jht + $jkn + $jp) - $gaji_potongan;

					switch ($hd['status_pajak']) {
						case 'TK/0':
							$pph = ((($jumlahGaji * 12) - (54000000)) * 0.05) / 12;
							break;
						case 'TK/1':
							$pph = ((($jumlahGaji * 12) - (58500000)) * 0.05) / 12;
							break;
						case 'TK/2':
							$pph = ((($jumlahGaji * 12) - (63000000)) * 0.05) / 12;
							break;
						case 'TK/3':
							$pph = ((($jumlahGaji * 12) - (63000000)) * 0.05) / 12;
							break;
						case 'K/0':
							$pph = ((($jumlahGaji * 12) - (58500000)) * 0.05) / 12;
							break;
						case 'K/1':
							$pph = ((($jumlahGaji * 12) - (63000000)) * 0.05) / 12;
							break;
						case 'K/2':
							$pph = ((($jumlahGaji * 12) - (67500000)) * 0.05) / 12;
							break;
						case 'K/3':
							$pph = ((($jumlahGaji * 12) - (72000000)) * 0.05) / 12;
							break;

						default:
							$pph = 0;
							break;
					}

					if ($pph < 0) {
						$pph = 0;
					}
					$pph = 0; // tidak ada pph kita nol kan saja
					$this->db->where('id', $id_hd);
					$this->db->update('payroll_gaji_tr_hd', array('pph'    => $pph, 'gaji_pendapatan'    => $gaji_pendapatan, 'gaji_potongan'    => $gaji_potongan));
				}

				$this->db->where('id', $periodeGaji['id']);
				$this->db->update('payroll_periode_gaji', array('status'    => '1', "tgl_proses" => date('Y-m-d H:i:s')));
			}
			if ($afdeling_id) {
				$query = "select count(*)as jum from payroll_gaji_tr_hd a inner join karyawan b
				on a.karyawan_id=b.id
					 where periode_gaji_id=" . $periodeGaji['id'] . " and b.sub_bagian_id=" . $afdeling_id . "";
			} else {
				$query = "select count(*)as jum from payroll_gaji_tr_hd a inner join karyawan b
				on a.karyawan_id=b.id
					 where periode_gaji_id=" . $periodeGaji['id'] . "";
			}

			$hasil =  $this->db->query($query)->row_array();
			// var_dump($hasil);exit;
			return $hasil;
		} catch (\Throwable $th) {
			//throw $th;
			$this->set_response(array("status" => "NOT OK", "data" =>  $th), REST_Controller::HTTP_CREATED);
		}
	}
	function start_posting_payroll_estate($id)
	{
		$periodeGaji = $this->HrmsPeriodeGajiModel->retrieve($id);
		if (empty($periodeGaji)) {
			$this->set_response(array("status" => "NOT OK", "data" => 'null'), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		// $res_akun = $this->db->query("SELECT * from acc_auto_jurnal 
		// where kode='ALOKASI_GAJI_BPJS'")->row_array();
		// if (empty($res_akun)) {
		// 	$this->set_response(array("status" => "NOT OK", "data" => "ALOKASI_GAJI_ESTATE Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
		// 	return;
		// }
		// $akun_bpjs = $res_akun['acc_akun_id']; // BPJS KETENAGA KERJAAN
		// $res_akun = $this->db->query("SELECT * from acc_auto_jurnal 
		// where kode='ALOKASI_GAJI_BPJS_KES'")->row_array();
		// if (empty($res_akun)) {
		// 	$this->set_response(array("status" => "NOT OK", "data" => "ALOKASI_GAJI_ESTATE Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
		// 	return;
		// }
		// $akun_bpjs_kes = $res_akun['acc_akun_id']; // BPJS KESEHATAN

		/* === tabel utk check ==*/
		$this->db->where('id >', '0');
		$this->db->delete('test_tabel1');
		$this->db->where('id >', '0');
		$this->db->delete('test_tabel2');
		$this->db->where('id >', '0');
		$this->db->delete('test_tabel3');
		$this->db->where('id >', '0');
		$this->db->delete('test_tabel4');
		$this->db->where('id >', '0');
		$this->db->delete('test_tabel5');
		/* ==== tabel utk check == */

		$res_akun = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='ALOKASI_GAJI_ESTATE'")->row_array();
		if (empty($res_akun)) {
			$this->set_response(array("status" => "NOT OK", "data" => "ALOKASI_GAJI_ESTATE Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$akun_hutang_gaji = $res_akun['acc_akun_id'];
		$akun_debet = $res_akun['acc_akun_id_debet'];
		$akun_kredit = $res_akun['acc_akun_id_kredit'];
		/* Hapus Jurnal Jika sdh prh diposting */
		$this->AccJurnalModel->delete_by_ref_id_and_modul($periodeGaji['id'], 'ALK_GAJI_ESTATE');
		$this->AccJurnalModel->delete_by_ref_id_and_modul($periodeGaji['id'], 'ALK_GAJI');
		$this->AccJurnalModel->delete_by_ref_id_and_modul($periodeGaji['id'], 'ALK_GAJI_DIFF');
		/* Hapus Jurnal Upah Jika sdh prh diposting */
		$this->AccJurnalUpahModel->delete_by_ref_id_and_modul($periodeGaji['id'], 'ALK_GAJI');
		$this->AccJurnalUpahModel->delete_by_ref_id_and_modul($periodeGaji['id'], 'ALK_GAJI_ESTATE');
		// $this->AccJurnalUpahModel->delete_by_ref_id_and_modul($periodeGaji['id'], 'ALK_GAJI_BPJS_ESTATE');
		$this->AccJurnalUpahModel->delete_by_ref_id_and_modul($periodeGaji['id'], 'ALK_GAJI_DIFF');
		// $this->AccJurnalUpahModel->delete_by_ref_id_and_modul($periodeGaji['id'], 'ALK_GAJI_BPJS');
		// $this->AccJurnalUpahModel->delete_by_ref_id_and_modul($periodeGaji['id'], 'ALK_GAJI_BPJS_KES');

		$pendapatan = $this->db->query("select * from payroll_tipe_gaji where jenis=1 or jenis=2 or jenis=3 order by urut")->result_array();
		$potongan = $this->db->query("select * from payroll_tipe_gaji where jenis=0 order by urut")->result_array();

		$sql = "
		SELECT a.*,c.nip,c.nama,d.nama as jabatan,e.nama as departemen ,f.nama as divisi,c.sub_bagian_id
		FROM payroll_gaji_tr_hd a 
		left join payroll_karyawan_gaji b on a.karyawan_id=b.karyawan_id
        inner join karyawan c on a.karyawan_id=c.id 
		left join payroll_jabatan d on c.jabatan_id=d.id
        left join payroll_department e on c.departemen_id=e.id
		left join gbm_organisasi f on c.sub_bagian_id=f.id
        where a.periode_gaji_id=" . $id . "";
		$sql = $sql . " order by nama;";
		$dataGaji = $this->db->query($sql)->result_array();

		/* Cek biaya per karyawan yg sudah dijurnal di jurnalUpah*/
		$query_cek_jurnal = "SELECT SUM(debet)as nilai,karyawan_id FROM acc_jurnal_upah_dt a 
		inner join acc_jurnal_upah_ht b on a.jurnal_id=b.id 
		WHERE 1=1
		and tanggal between '" . $periodeGaji['tgl_awal'] . "' and '" . $periodeGaji['tgl_akhir'] . "'
		and a.lokasi_id= " . $periodeGaji['lokasi_id'] . "
		and modul not in('ALK_GAJI','ALK_GAJI_ESTATE','ALK_GAJI_BPJS','ALK_GAJI_DIFF','ALK_GAJI_BPJS_KES','ALK_GAJI_BPJS_ESTATE','ALK_GAJI_BPJS_ESTATE_KES','ALK_GAJI_JAMSOSTEK_ESTATE','ALK_GAJI_BPJS_KES_ESTATE','ALK_GAJI_CATU_BERAS_ESTATE')
		and karyawan_id IS NOT null
		group by karyawan_id";
		$res_gaji_jurnal = $this->db->query($query_cek_jurnal)->result_array();
		$gaji_jurnal = array();
		foreach ($res_gaji_jurnal as $key => $gaji) {
			$gaji_jurnal[$gaji['karyawan_id']] = $gaji['nilai'];
			$this->db->insert('test_tabel3', array('karyawan_id' => $gaji['karyawan_id'], 'nama'    => '', "jumlah" => $gaji['nilai']));
		}

		$no = 0;
		$gaji_karyawan = array();
		$totalPendapatan = 0;
		$totalPotongan = 0;
		$totalgajiDiterima = 0;
		// Data HEADER
		$tanggal = $periodeGaji['tgl_akhir'];
		$this->load->library('Autonumber');

		$jum_proses = 0;
		foreach ($dataGaji as $key => $arrGaji) {
			$no++;
			$jumPendapatan = 0;
			$jumPotongan = 0;
			$gajiDiterima = 0;
			// looping pendapatan
			foreach ($pendapatan as $key => $value) {
				$dataPendapatan = $this->db->query("SELECT a.*,b.jenis,b.nama,b.urut FROM payroll_gaji_tr_dt a inner join payroll_tipe_gaji b
                    on a.tipe_gaji=b.id
                    where a.id_hd=" . $arrGaji['id'] . " and a.tipe_gaji=" . $value['id'] . ";")->row_array();
				if (empty($dataPendapatan)) {
				} else {
					$jumPendapatan = $jumPendapatan + $dataPendapatan['nilai'];
				}
			}

			$jumPendapatan = $jumPendapatan + $arrGaji['gapok'] +  $arrGaji['lembur'] + $arrGaji['premi'];
			// looping potongan
			foreach ($potongan as $key => $value) {
				$dataPotongan = $this->db->query("SELECT a.*,b.jenis,b.nama,b.urut FROM payroll_gaji_tr_dt a inner join payroll_tipe_gaji b
                    on a.tipe_gaji=b.id
                    where a.id_hd=" . $arrGaji['id'] . " and a.tipe_gaji=" . $value['id'] . ";")->row_array();
				if (empty($dataPotongan)) {
					$total[$value['id']] = 0;
				} else {
					$jumPotongan = $jumPotongan + $dataPotongan['nilai'];
				}
			}

			$jumPotongan = $jumPotongan + $arrGaji['hk_potongan_gaji'] + $arrGaji['denda'] + $arrGaji['jht'] +  $arrGaji['jp'] + $arrGaji['jkn']; //+ $dataHeader['pph'];
			$gajiDiterima = $jumPendapatan - $jumPotongan; // + $arrGaji['nilai_rp_catu'];
			$this->db->insert('test_tabel1', array('karyawan_id' => $arrGaji['karyawan_id'], 'nama'    => $arrGaji['nama'], "jumlah" => $gajiDiterima));
			$nilai_gaji_jurnal = 0;
			if ($gaji_jurnal[$arrGaji['karyawan_id']]) {
				$nilai_gaji_jurnal = $gaji_jurnal[$arrGaji['karyawan_id']];
			}
			$selisih_gaji_jurnal =	$gajiDiterima - $nilai_gaji_jurnal; // Selisih antara hasil proses payroll dan Gaji pada JurnalUpah
			if ($gajiDiterima < 0) { // JIka gaji lebih kecil dr nol abaikan saja
				$this->db->insert('test_tabel4', array('karyawan_id' => $arrGaji['karyawan_id'], 'nama'    => $arrGaji['nama'], "jumlah" => $gajiDiterima));

				continue;
			}


			$jum_proses++;
			/* Query mencari jurnal karyawan di Jurnal_upah(hasil dr posting transaksi) utk Proporsi */
			$query_cek_jurnal_blok = "SELECT  sum(debet)as nilai, blok_stasiun_id,kegiatan_id ,acc_akun_id,umur_tanam_blok,kendaraan_mesin_id
								 FROM acc_jurnal_upah_dt  a 
								inner join acc_jurnal_upah_ht b on a.jurnal_id=b.id 
								 WHERE karyawan_id=" . $arrGaji['karyawan_id'] . "
										and tanggal between '" . $periodeGaji['tgl_awal'] . "' and '" . $periodeGaji['tgl_akhir'] . "'
										and a.lokasi_id =" . $periodeGaji['lokasi_id'] . "
										and modul not in('ALK_GAJI','ALK_GAJI_ESTATE','ALK_GAJI_BPJS','ALK_GAJI_DIFF','ALK_GAJI_BPJS_KES','ALK_GAJI_BPJS_ESTATE','ALK_GAJI_BPJS_ESTATE_KES','ALK_GAJI_JAMSOSTEK_ESTATE','ALK_GAJI_BPJS_KES_ESTATE','ALK_GAJI_CATU_BERAS_ESTATE')
										group by blok_stasiun_id,kegiatan_id ,acc_akun_id,umur_tanam_blok,kendaraan_mesin_id";
			$res_blok = $this->db->query($query_cek_jurnal_blok)->result_array();
			$jum_blok = count($res_blok);

			if ($jum_blok == 0) {
				$this->db->insert('test_tabel5', array('karyawan_id' => $arrGaji['karyawan_id'], 'nama'    => $arrGaji['nama'], "jumlah" => $gajiDiterima));


				continue;
			}

			/* START JURNAL SELISIH GAJI VS BIAYA dan Proporsi */
			if ($selisih_gaji_jurnal > 10) { //   1000 ke atas saja yg di hitung proporsi  
				$this->db->insert('test_tabel2', array('karyawan_id' => $arrGaji['karyawan_id'], 'nama'    => $arrGaji['nama'], "jumlah" => $selisih_gaji_jurnal));

				// $jum_proses++;
				$no_jurnal = $this->autonumber->jurnal_auto($periodeGaji['lokasi_id'], $tanggal, 'ALK_GAJI_DIFF');
				$dataH = array(
					'no_jurnal' => $no_jurnal,
					'lokasi_id' => $periodeGaji['lokasi_id'],
					'tanggal' => $tanggal,
					'no_ref' => 'ALK_GAJI_DIFF_' . $periodeGaji['nama'],
					'ref_id' => $periodeGaji['id'],
					'tipe_jurnal' => 'AUTO',
					'modul' => 'ALK_GAJI_DIFF',
					'keterangan' => 'ALK_GAJI_DIFF_' . $arrGaji['divisi'] . ' Karyawan: ' . $arrGaji['nama'] . ",",
					'is_posting' => 1,
				);
				$id_header = $this->AccJurnalModel->create_header($dataH);

				$jum_blok = count($res_blok);

				// $nilai_proporsi = $selisih_gaji_jurnal / $jum_blok;
				foreach ($res_blok as $key => $blok) {
					$nilai_per_item = $blok['nilai'];
					$nilai_proporsi = $selisih_gaji_jurnal * ($nilai_per_item /	$nilai_gaji_jurnal);
					$dataDebet = array(
						'lokasi_id' => $periodeGaji['lokasi_id'],
						'jurnal_id' => $id_header,
						'acc_akun_id' => $blok['acc_akun_id'], //akun biaya ,
						'debet' => $nilai_proporsi,
						'kredit' => 0,
						'ket' => 'ALK_GAJI_DIFF Proporsi Selisih gaji: ' . $arrGaji['divisi'] . ' Karyawan: ' . $arrGaji['nama'] . ",",
						'no_referensi' => 'ALK_GAJI_DIFF_' . $periodeGaji['nama'],
						'referensi_id' => NULL,
						'blok_stasiun_id' => $blok['blok_stasiun_id'],
						'kegiatan_id' => $blok['kegiatan_id'], //kegiatan ,
						'kendaraan_mesin_id' =>  $blok['kendaraan_mesin_id'],
						'karyawan_id' => $arrGaji['karyawan_id'], //karyawan,
						'umur_tanam_blok' => $blok['umur_tanam_blok'],
						'divisi_id' => NULL,

					);
					$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataDebet);
				}
				$dataKredit = array(
					'lokasi_id' => $periodeGaji['lokasi_id'],
					'jurnal_id' => $id_header,
					'acc_akun_id' => $akun_hutang_gaji, //$value['acc_akun_id'],
					'debet' => 0,
					'kredit' => $selisih_gaji_jurnal, // Akun Lawan Biaya
					'ket' => 'ALK_GAJI_DIFF Proporsi Selisih gaji: ' . $arrGaji['divisi'] . ' Karyawan: ' . $arrGaji['nama'] . "",
					'no_referensi' => 'ALK_GAJI_DIFF_' . $periodeGaji['nama'],
					'referensi_id' => NULL,
					'blok_stasiun_id' => NULL,
					'kegiatan_id' => NULL, // $value['kegiatan_id'],
					'kendaraan_mesin_id' => NULL,
					'divisi_id' => null,
					'karyawan_id' => null, // $arrGaji['karyawan_id'], //karyawan,

				);
				$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataKredit);
			} else if ($selisih_gaji_jurnal < -10) {  // atau -1000 ke bawah saja yg di hitung proporsi  
				// $jum_proses++;
				$this->db->insert('test_tabel2', array('karyawan_id' => $arrGaji['karyawan_id'], 'nama'    => $arrGaji['nama'], "jumlah" => $selisih_gaji_jurnal));

				$no_jurnal = $this->autonumber->jurnal_auto($periodeGaji['lokasi_id'], $tanggal, 'ALK_GAJI_DIFF');

				$dataH = array(
					'no_jurnal' => $no_jurnal,
					'lokasi_id' => $periodeGaji['lokasi_id'],
					'tanggal' => $tanggal,
					'no_ref' => 'ALK_GAJI_DIFF_' . $periodeGaji['nama'],
					'ref_id' => $periodeGaji['id'],
					'tipe_jurnal' => 'AUTO',
					'modul' => 'ALK_GAJI_DIFF',
					'keterangan' => 'ALK_GAJI_DIFF_' . $arrGaji['divisi'] . ' Karyawan: ' . $arrGaji['nama'] . ",",
					'is_posting' => 1,
				);
				$id_header = $this->AccJurnalModel->create_header($dataH);
				$dataDebet = array(
					'lokasi_id' => $periodeGaji['lokasi_id'],
					'jurnal_id' => $id_header,
					'acc_akun_id' => $akun_hutang_gaji, //akun biaya ,
					'debet' => ($selisih_gaji_jurnal) * -1,
					'kredit' => 0,
					'ket' => 'ALK_GAJI_DIFF Proporsi Selisih gaji:' . $arrGaji['divisi'] . ' Karyawan: ' . $arrGaji['nama'] . "",
					'no_referensi' => 'ALK_GAJI_DIFF_' . $periodeGaji['nama'],
					'referensi_id' => NULL,
					'blok_stasiun_id' => NULL,
					'kegiatan_id' => null, //kegiatan ,
					'kendaraan_mesin_id' => NULL,
					'karyawan_id' => NULL, //karyawan,
					'umur_tanam_blok' => null,
					'divisi_id' => null,

				);
				$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataDebet);
				$jum_blok = count($res_blok);
				// $nilai_proporsi = ($selisih_gaji_jurnal / $jum_blok) * -1;
				foreach ($res_blok as $key => $blok) {
					$nilai_per_item = $blok['nilai'];
					$nilai_proporsi = (($selisih_gaji_jurnal) * -1) * ($nilai_per_item / $nilai_gaji_jurnal);
					// $nilai_proporsi = ($nilai_per_item)
					$dataKredit = array(
						'lokasi_id' => $periodeGaji['lokasi_id'],
						'jurnal_id' => $id_header,
						'acc_akun_id' => $blok['acc_akun_id'], //$value['acc_akun_id'],
						'debet' => 0,
						'kredit' => $nilai_proporsi, // Akun Lawan Biaya
						'ket' => 'ALK_GAJI_DIFF Proporsi Selisih gaji: ' . $arrGaji['divisi'] . ' Karyawan: ' . $arrGaji['nama'] . ",",
						'no_referensi' => 'ALK_GAJI_DIFF_' . $periodeGaji['nama'],
						'referensi_id' => NULL,
						'blok_stasiun_id' => $blok['blok_stasiun_id'],
						'kegiatan_id' => $blok['kegiatan_id'], // $value['kegiatan_id'],
						'kendaraan_mesin_id' =>  $blok['kendaraan_mesin_id'],
						'umur_tanam_blok' => $blok['umur_tanam_blok'],
						'karyawan_id' => $arrGaji['karyawan_id'], //karyawan,
						'divisi_id' =>  $arrGaji['sub_bagian_id'],

					);
					$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataKredit);
				}
			}

			$totalgajiDiterima = $totalgajiDiterima + $gajiDiterima;
		}

		$this->db->where('id', $id);
		$this->db->update('payroll_periode_gaji', array('is_posting'    => '1', "tgl_posting" => date('Y-m-d H:i:s')));

		$hasil['jum'] = $jum_proses; // count($dataGaji);
		// var_dump($hasil);exit;
		return $hasil;
	}
	function start_posting_payroll_estate_plus_jamsostek_bpjs_kes($id)
	{
		$periodeGaji = $this->HrmsPeriodeGajiModel->retrieve($id);
		if (empty($periodeGaji)) {
			$this->set_response(array("status" => "NOT OK", "data" => 'null'), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		// CEK PERIODE SDH ADA ATAU SDH CLOSE berdasarkan tgl akhir(tgl posting jurnal) proses periode payroll//
		$chk = cek_periode($periodeGaji['tgl_akhir'], $periodeGaji['lokasi_id']);
		if ($chk['status'] == false) {
			$this->set_response(array("status" => "NOT OK", "data" => $chk['message']), REST_Controller::HTTP_OK);
			return;
		}
		//==============//
		$res_akun = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='ALOKASI_GAJI_BPJS'")->row_array();
		if (empty($res_akun)) {
			$this->set_response(array("status" => "NOT OK", "data" => "ALOKASI_GAJI_ESTATE Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$akun_bpjs = $res_akun['acc_akun_id']; // BPJS KETENAGA KERJAAN
		$res_akun = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='ALOKASI_GAJI_BPJS_KES'")->row_array();
		if (empty($res_akun)) {
			$this->set_response(array("status" => "NOT OK", "data" => "ALOKASI_GAJI_ESTATE Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$akun_bpjs_kes = $res_akun['acc_akun_id']; // BPJS KESEHATAN

		$res_akun = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='ALOKASI_GAJI_ESTATE'")->row_array();
		if (empty($res_akun)) {
			$this->set_response(array("status" => "NOT OK", "data" => "ALOKASI_GAJI_ESTATE Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$akun_hutang_gaji = $res_akun['acc_akun_id'];
		$akun_debet = $res_akun['acc_akun_id_debet'];
		$akun_kredit = $res_akun['acc_akun_id_kredit'];
		$this->AccJurnalUpahModel->delete_by_ref_id_and_modul($periodeGaji['id'], 'ALK_GAJI');
		$this->AccJurnalUpahModel->delete_by_ref_id_and_modul($periodeGaji['id'], 'ALK_GAJI_ESTATE');
		$this->AccJurnalUpahModel->delete_by_ref_id_and_modul($periodeGaji['id'], 'ALK_GAJI_BPJS_ESTATE');
		$this->AccJurnalUpahModel->delete_by_ref_id_and_modul($periodeGaji['id'], 'ALK_GAJI_DIFF');
		$this->AccJurnalUpahModel->delete_by_ref_id_and_modul($periodeGaji['id'], 'ALK_GAJI_BPJS');
		$this->AccJurnalUpahModel->delete_by_ref_id_and_modul($periodeGaji['id'], 'ALK_GAJI_BPJS_KES');
		$this->AccJurnalModel->delete_by_ref_id_and_modul($periodeGaji['id'], 'ALK_GAJI');
		$pendapatan = $this->db->query("select * from payroll_tipe_gaji where jenis=1 or jenis=2 or jenis=3 order by urut")->result_array();
		$potongan = $this->db->query("select * from payroll_tipe_gaji where jenis=0 order by urut")->result_array();


		$sql = "
		SELECT a.*,c.nip,c.nama,d.nama as jabatan,e.nama as departemen ,f.nama as divisi,c.sub_bagian_id
		FROM payroll_gaji_tr_hd a 
		left join payroll_karyawan_gaji b on a.karyawan_id=b.karyawan_id
        inner join karyawan c on a.karyawan_id=c.id 
		left join payroll_jabatan d on c.jabatan_id=d.id
        left join payroll_department e on c.departemen_id=e.id
		left join gbm_organisasi f on c.sub_bagian_id=f.id
        where a.periode_gaji_id=" . $id . "";
		$sql = $sql . " order by nama;";
		// 	$this->set_response(array("status" => "NOT OK", "data" =>$sql), REST_Controller::HTTP_OK);
		// return;
		$dataGaji = $this->db->query($sql)->result_array();

		/* Cek biaya per karyawan yg sudah dijurnal di jurnalUpah*/
		$query_cek_jurnal = "SELECT SUM(debet)as nilai,karyawan_id FROM acc_jurnal_upah_dt a 
		inner join acc_jurnal_upah_ht b on a.jurnal_id=b.id 
		WHERE 1=1
		and tanggal between '" . $periodeGaji['tgl_awal'] . "' and '" . $periodeGaji['tgl_akhir'] . "'
		and b.lokasi_id= " . $periodeGaji['lokasi_id'] . "
		and modul not in('ALK_GAJI','ALK_GAJI_ESTATE','ALK_GAJI_BPJS','ALK_GAJI_DIFF','ALK_GAJI_BPJS_KES','ALK_GAJI_BPJS_ESTATE','ALK_GAJI_BPJS_ESTATE_KES')
		and karyawan_id IS NOT null
		group by karyawan_id";
		$res_gaji_jurnal = $this->db->query($query_cek_jurnal)->result_array();
		$gaji_jurnal = array();
		foreach ($res_gaji_jurnal as $key => $gaji) {
			$gaji_jurnal[$gaji['karyawan_id']] = $gaji['nilai'];
		}

		$no = 0;
		$gaji_karyawan = array();
		$totalPendapatan = 0;
		$totalPotongan = 0;
		$totalgajiDiterima = 0;
		// Data HEADER
		$tanggal = $periodeGaji['tgl_akhir'];
		$this->load->library('Autonumber');

		$jum_proses = 0;
		foreach ($dataGaji as $key => $arrGaji) {
			$no++;
			$jumPendapatan = 0;
			$jumPotongan = 0;
			$gajiDiterima = 0;
			// looping pendapatan
			foreach ($pendapatan as $key => $value) {
				$dataPendapatan = $this->db->query("SELECT a.*,b.jenis,b.nama,b.urut FROM payroll_gaji_tr_dt a inner join payroll_tipe_gaji b
                    on a.tipe_gaji=b.id
                    where a.id_hd=" . $arrGaji['id'] . " and a.tipe_gaji=" . $value['id'] . ";")->row_array();
				if (empty($dataPendapatan)) {
				} else {
					$jumPendapatan = $jumPendapatan + $dataPendapatan['nilai'];
				}
			}

			$jumPendapatan = $jumPendapatan + $arrGaji['gapok'] +  $arrGaji['lembur'] + $arrGaji['premi'];
			// looping potongan
			foreach ($potongan as $key => $value) {
				$dataPotongan = $this->db->query("SELECT a.*,b.jenis,b.nama,b.urut FROM payroll_gaji_tr_dt a inner join payroll_tipe_gaji b
                    on a.tipe_gaji=b.id
                    where a.id_hd=" . $arrGaji['id'] . " and a.tipe_gaji=" . $value['id'] . ";")->row_array();
				if (empty($dataPotongan)) {
					$total[$value['id']] = 0;
				} else {
					$jumPotongan = $jumPotongan + $dataPotongan['nilai'];
				}
			}

			$jumPotongan = $jumPotongan + $arrGaji['hk_potongan_gaji'] + $arrGaji['denda'] + $arrGaji['jht'] +  $arrGaji['jp'] + $arrGaji['jkn']; //+ $dataHeader['pph'];
			$gajiDiterima = $jumPendapatan - $jumPotongan; // + $arrGaji['nilai_rp_catu'];

			/* Cek biaya per karyawan yg sudah dijurnal */
			// $query_cek_jurnal = "SELECT SUM(debet)as nilai FROM acc_jurnal_upah_dt a 
			// 					inner join acc_jurnal_upah_ht b on a.jurnal_id=b.id WHERE karyawan_id=" . $arrGaji['karyawan_id'] . "
			// 					and tanggal between '" . $periodeGaji['tgl_awal'] . "' and '" . $periodeGaji['tgl_akhir'] . "'
			// 					and b.lokasi_id= " . $periodeGaji['lokasi_id'] . "
			// 					and modul not in('ALK_GAJI_ESTATE','ALK_GAJI_BPJS_ESTATE')";
			// $res_gaji_jurnal = $this->db->query($query_cek_jurnal)->row_array();
			$nilai_gaji_jurnal = 0;
			if ($gaji_jurnal[$arrGaji['karyawan_id']]) {
				$nilai_gaji_jurnal = $gaji_jurnal[$arrGaji['karyawan_id']];
			}

			// if ($res_gaji_jurnal) {
			// 	$nilai_gaji_jurnal = $res_gaji_jurnal['nilai'];
			// }
			$selisih_gaji_jurnal =	$gajiDiterima - $nilai_gaji_jurnal;
			if ($gajiDiterima < 0) {
				continue;
			}

			/* start JURNAL BPJS */
			$jum_proses++;
			$nilai_bpjs = $arrGaji['jkk_perusahaan'] + $arrGaji['jkm_perusahaan'];
			$nilai_bpjs_kes = $arrGaji['jkn_perusahaan'];
			$no_jurnal = $this->autonumber->jurnal_upah_auto($periodeGaji['lokasi_id'], $tanggal, 'ALK_GAJI_BPJS');
			$dataH = array(
				'no_jurnal' => $no_jurnal,
				'lokasi_id' => $periodeGaji['lokasi_id'],
				'tanggal' => $tanggal,
				'no_ref' => 'ALK_GAJI_BPJS_' . $periodeGaji['nama'],
				'ref_id' => $periodeGaji['id'],
				'tipe_jurnal' => 'AUTO',
				'modul' => 'ALK_GAJI_BPJS',
				'keterangan' => 'ALK_GAJI_BPJS Karyawan: ' . $arrGaji['nama'] . ",",
				'is_posting' => 1,
			);
			$no_jurnal = $this->autonumber->jurnal_upah_auto($periodeGaji['lokasi_id'], $tanggal, 'ALK_GAJI_BPJS_KES');
			$dataH_KES = array(
				'no_jurnal' => $no_jurnal,
				'lokasi_id' => $periodeGaji['lokasi_id'],
				'tanggal' => $tanggal,
				'no_ref' => 'ALK_GAJI_BPJS_KESEHATAN_' . $periodeGaji['nama'],
				'ref_id' => $periodeGaji['id'],
				'tipe_jurnal' => 'AUTO',
				'modul' => 'ALK_GAJI_BPJS_KES',
				'keterangan' => 'ALK_GAJI_BPJS_KESEHATAN KARYAWAN: ' . $arrGaji['nama'] . ",",
				'is_posting' => 1,
			);
			$id_header_bpjs = $this->AccJurnalUpahModel->create_header($dataH);
			$id_header_bpjs_kes = $this->AccJurnalUpahModel->create_header($dataH_KES);
			$query_cek_jurnal_blok = "SELECT  sum(debet)as nilai, blok_stasiun_id,kegiatan_id ,acc_akun_id,umur_tanam_blok,kendaraan_mesin_id,tipe FROM acc_jurnal_upah_dt  a 
								inner join acc_jurnal_upah_ht b on a.jurnal_id=b.id  WHERE karyawan_id=" . $arrGaji['karyawan_id'] . "
										and tanggal between '" . $periodeGaji['tgl_awal'] . "' and '" . $periodeGaji['tgl_akhir'] . "'
										and b.lokasi_id =" . $periodeGaji['lokasi_id'] . "
										and no_referensi not in('ALK_GAJI_DIFF', 'ALK_GAJI_ESTATE','ALK_GAJI_MILL','ALK_GAJI_BPJS','ALK_GAJI_BPJS_KES')
										group by blok_stasiun_id,kegiatan_id ,acc_akun_id,umur_tanam_blok,kendaraan_mesin_id,tipe";
			$res_blok = $this->db->query($query_cek_jurnal_blok)->result_array();
			$jum_blok = count($res_blok);
			if ($jum_blok == 0) {
				// 	$this->set_response(array("status" => "NOT OK", "data" =>$arrGaji['karyawan_id']), REST_Controller::HTTP_NOT_FOUND);
				// 	return;
				continue;
			}
			// $nilai_proporsi_bpjs = $nilai_bpjs / $jum_blok;
			foreach ($res_blok as $key => $blok) {
				$nilai_per_item = $blok['nilai'];
				$nilai_proporsi_bpjs = $nilai_bpjs * ($nilai_per_item / $nilai_gaji_jurnal);
				$nilai_proporsi_bpjs_kes = $nilai_bpjs_kes * ($nilai_per_item / $nilai_gaji_jurnal);

				$dataDebetBpjs = array(
					'lokasi_id' => $periodeGaji['lokasi_id'],
					'jurnal_id' => $id_header_bpjs,
					'acc_akun_id' => $blok['acc_akun_id'],
					'debet' => $nilai_proporsi_bpjs,
					'kredit' => 0,
					'ket' => 'Biaya BPJS Gaji Afdeling: ,' . $arrGaji['divisi'] . ' Karyawan: ' . $arrGaji['nama'] . ",",
					'no_referensi' => 'ALK_GAJI_BPJS_' . $periodeGaji['nama'],
					'referensi_id' => NULL,
					'blok_stasiun_id' => $blok['blok_stasiun_id'],
					'kegiatan_id' => $blok['kegiatan_id'],
					'kendaraan_mesin_id' =>  $blok['kendaraan_mesin_id'],
					'karyawan_id' => $arrGaji['karyawan_id'],
					'umur_tanam_blok' => $blok['umur_tanam_blok'],
					'divisi_id' => $arrGaji['sub_bagian_id'],
					'tipe' => 'bpjs'
				);
				$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header_bpjs, $dataDebetBpjs);

				$dataDebetBpjsKes = array(
					'lokasi_id' => $periodeGaji['lokasi_id'],
					'jurnal_id' => $id_header_bpjs_kes,
					'acc_akun_id' => $blok['acc_akun_id'],
					'debet' => $nilai_proporsi_bpjs_kes,
					'kredit' => 0,
					'ket' => 'Biaya BPJS Gaji Afdeling: ,' . $arrGaji['divisi'] . ' Karyawan: ' . $arrGaji['nama'] . ",",
					'no_referensi' => 'ALK_GAJI_BPJS_KES_' . $periodeGaji['nama'],
					'referensi_id' => NULL,
					'blok_stasiun_id' => $blok['blok_stasiun_id'],
					'kegiatan_id' => $blok['kegiatan_id'],
					'kendaraan_mesin_id' =>  $blok['kendaraan_mesin_id'],
					'karyawan_id' => $arrGaji['karyawan_id'],
					'umur_tanam_blok' => $blok['umur_tanam_blok'],
					'divisi_id' => $arrGaji['sub_bagian_id'],
					'tipe' => 'bpjs_kes'
				);
				$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header_bpjs_kes, $dataDebetBpjsKes);
			}
			$dataKreditBpjs = array(
				'lokasi_id' => $periodeGaji['lokasi_id'],
				'jurnal_id' => $id_header_bpjs,
				'acc_akun_id' => $akun_bpjs,
				'debet' => 0,
				'kredit' => $nilai_bpjs,
				'ket' => 'Biaya BPJS Afdeling: ,' . $arrGaji['divisi'] . ' Karyawan: ' . $arrGaji['nama'] . ",",
				'no_referensi' => 'ALK_GAJI_BPJS_' . $periodeGaji['nama'],
				'referensi_id' => NULL,
				'blok_stasiun_id' => NULL,
				'kegiatan_id' => NULL,
				'kendaraan_mesin_id' => NULL,
				'divisi_id' => null,
				'karyawan_id' => NULL,
				'tipe' => 'bpjs'
			);
			$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header_bpjs, $dataKreditBpjs);
			$dataKreditBpjsKes = array(
				'lokasi_id' => $periodeGaji['lokasi_id'],
				'jurnal_id' => $id_header_bpjs_kes,
				'acc_akun_id' => $akun_bpjs_kes,
				'debet' => 0,
				'kredit' => $nilai_bpjs_kes,
				'ket' => 'Biaya BPJS KESEHATAN Afdeling: ,' . $arrGaji['divisi'] . ' Karyawan: ' . $arrGaji['nama'] . ",",
				'no_referensi' => 'ALK_GAJI_BPJS_KES' . $periodeGaji['nama'],
				'referensi_id' => NULL,
				'blok_stasiun_id' => NULL,
				'kegiatan_id' => NULL,
				'kendaraan_mesin_id' => NULL,
				'divisi_id' => null,
				'karyawan_id' => NULL,
				'tipe' => 'bpjs_kes'
			);
			$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header_bpjs_kes, $dataKreditBpjsKes);

			// /* END JURNAL BPJS */


			/* STAR JURNAL SELISIH GAJI VS BIAYA */
			if ($selisih_gaji_jurnal > 1000) { // hanya  1000 ke atas saja 
				// $jum_proses++;
				$no_jurnal = $this->autonumber->jurnal_upah_auto($periodeGaji['lokasi_id'], $tanggal, 'ALK_GAJI_ESTATE');
				$dataH = array(
					'no_jurnal' => $no_jurnal,
					'lokasi_id' => $periodeGaji['lokasi_id'],
					'tanggal' => $tanggal,
					'no_ref' => 'ALK_GAJI_DIFF_' . $periodeGaji['nama'],
					'ref_id' => $periodeGaji['id'],
					'tipe_jurnal' => 'AUTO',
					'modul' => 'ALK_GAJI_DIFF',
					'keterangan' => 'ALK_GAJI_DIFF_' . $arrGaji['divisi'] . ' Karyawan: ' . $arrGaji['nama'] . ",",
					'is_posting' => 1,
				);
				$id_header = $this->AccJurnalUpahModel->create_header($dataH);
				// jika gaji payroll lebih besar dr total gaji biaya harian 
				/* Cek biaya per karyawan yg sudah dijurnal */
				// $query_cek_jurnal_blok = "SELECT distinct blok_stasiun_id,kegiatan_id ,acc_akun_id,umur_tanam_blok,kendaraan_mesin_id FROM acc_jurnal_upah_dt  a 
				// inner join acc_jurnal_upah_ht b on a.jurnal_id=b.id  WHERE karyawan_id=" . $arrGaji['karyawan_id'] . "
				// 							and tanggal between '" . $periodeGaji['tgl_awal'] . "' and '" . $periodeGaji['tgl_akhir'] . "'
				// 							and no_referensi not in( 'ALK_GAJI_ESTATE','ALK_GAJI_ESTATE','ALK_GAJI_BPJS_ESTATE','ALK_GAJI_BPJS_MILL')";
				// $res_blok = $this->db->query($query_cek_jurnal_blok)->result_array();
				if (!$res_blok) {
					continue;
				}
				$jum_blok = count($res_blok);

				// $nilai_proporsi = $selisih_gaji_jurnal / $jum_blok;
				foreach ($res_blok as $key => $blok) {
					$nilai_per_item = $blok['nilai'];
					$nilai_proporsi = $selisih_gaji_jurnal * ($nilai_per_item /	$nilai_gaji_jurnal);
					$dataDebet = array(
						'lokasi_id' => $periodeGaji['lokasi_id'],
						'jurnal_id' => $id_header,
						'acc_akun_id' => $blok['acc_akun_id'], //akun biaya Panen,
						'debet' => $nilai_proporsi,
						'kredit' => 0,
						'ket' => 'ALK_GAJI_DIFF Proporsi Selisih gaji: ' . $arrGaji['divisi'] . ' Karyawan: ' . $arrGaji['nama'] . ",",
						'no_referensi' => 'ALK_GAJI_DIFF_' . $periodeGaji['nama'],
						'referensi_id' => NULL,
						'blok_stasiun_id' => $blok['blok_stasiun_id'],
						'kegiatan_id' => $blok['kegiatan_id'], //kegiatan panen,
						'kendaraan_mesin_id' =>  $blok['kendaraan_mesin_id'],
						'karyawan_id' => $arrGaji['karyawan_id'], //karyawan,
						'umur_tanam_blok' => $blok['umur_tanam_blok'],
						'divisi_id' => NULL,
						'tipe' => $blok['tipe'],
						'hk' => 0
					);
					$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
					$dataKredit = array(
						'lokasi_id' => $periodeGaji['lokasi_id'],
						'jurnal_id' => $id_header,
						'acc_akun_id' => $akun_hutang_gaji, //$value['acc_akun_id'],
						'debet' => 0,
						'kredit' => $nilai_proporsi, // Akun Lawan Biaya
						'ket' => 'ALK_GAJI_DIFF Proporsi Selisih gaji: ' . $arrGaji['divisi'] . ' Karyawan: ' . $arrGaji['nama'] . ",",
						'no_referensi' => 'ALK_GAJI_DIFF_' . $periodeGaji['nama'],
						'referensi_id' => NULL,
						'blok_stasiun_id' => NULL,
						'kegiatan_id' => NULL, // $value['kegiatan_id'],
						'kendaraan_mesin_id' => NULL,
						'divisi_id' => null,
						'karyawan_id' => null, // $arrGaji['karyawan_id'], //karyawan,
						'tipe' => $blok['tipe'],
					);
					$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
				}
				// $dataKredit = array(
				// 	'lokasi_id' => $periodeGaji['lokasi_id'],
				// 	'jurnal_id' => $id_header,
				// 	'acc_akun_id' => $akun_hutang_gaji, //$value['acc_akun_id'],
				// 	'debet' => 0,
				// 	'kredit' => $selisih_gaji_jurnal, // Akun Lawan Biaya
				// 	'ket' => 'Biaya Gaji Proporsi Selisih gaji: ' . $arrGaji['divisi'] . ' Karyawan: ' . $arrGaji['nama'] . ",",
				// 	'no_referensi' => 'ALK_GAJI_DIFF_' . $periodeGaji['nama'],
				// 	'referensi_id' => NULL,
				// 	'blok_stasiun_id' => NULL,
				// 	'kegiatan_id' => NULL, // $value['kegiatan_id'],
				// 	'kendaraan_mesin_id' => NULL,
				// 	'divisi_id' => null,
				// 	'karyawan_id' => null, // $arrGaji['karyawan_id'], //karyawan,
				// 	'tipe' =>'upah',
				// );
				// $id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
			} else if ($selisih_gaji_jurnal < -1000) {
				// $jum_proses++;
				$no_jurnal = $this->autonumber->jurnal_upah_auto($periodeGaji['lokasi_id'], $tanggal, 'ALK_GAJI_ESTATE');

				$dataH = array(
					'no_jurnal' => $no_jurnal,
					'lokasi_id' => $periodeGaji['lokasi_id'],
					'tanggal' => $tanggal,
					'no_ref' => 'ALK_GAJI_DIFF_' . $periodeGaji['nama'],
					'ref_id' => $periodeGaji['id'],
					'tipe_jurnal' => 'AUTO',
					'modul' => 'ALK_GAJI_DIFF',
					'keterangan' => 'ALK_GAJI_DIFF_' . $arrGaji['divisi'] . ' Karyawan: ' . $arrGaji['nama'] . ",",
					'is_posting' => 1,
				);
				$id_header = $this->AccJurnalUpahModel->create_header($dataH);
				/* Cek biaya per karyawan yg sudah dijurnal  */
				// $query_cek_jurnal_blok = "SELECT distinct blok_stasiun_id,kegiatan_id ,acc_akun_id,umur_tanam_blok,kendaraan_mesin_id FROM acc_jurnal_upah_dt  a 
				// 							inner join acc_jurnal_upah_ht b on a.jurnal_id=b.id WHERE karyawan_id=" . $arrGaji['karyawan_id'] . "
				// 							and tanggal between '" . $periodeGaji['tgl_awal'] . "' and '" . $periodeGaji['tgl_akhir'] . "'
				// 							and no_referensi <> 'ALK_GAJI_ESTATE'";
				// $res_blok = $this->db->query($query_cek_jurnal_blok)->result_array();
				$jum_blok = count($res_blok);
				// $nilai_proporsi = ($selisih_gaji_jurnal / $jum_blok) * -1;
				foreach ($res_blok as $key => $blok) {
					$nilai_per_item = $blok['nilai'];
					$nilai_proporsi = (($selisih_gaji_jurnal) * -1) * ($nilai_per_item / $nilai_gaji_jurnal);
					// $nilai_proporsi = ($nilai_per_item)
					$dataKredit = array(
						'lokasi_id' => $periodeGaji['lokasi_id'],
						'jurnal_id' => $id_header,
						'acc_akun_id' => $blok['acc_akun_id'], //$value['acc_akun_id'],
						'debet' => 0,
						'kredit' => $nilai_proporsi, // Akun Lawan Biaya
						'ket' => 'ALK_GAJI_DIFF Proporsi Selisih gaji: ' . $arrGaji['divisi'] . ' Karyawan: ' . $arrGaji['nama'] . ",",
						'no_referensi' => 'ALK_GAJI_DIFF_' . $periodeGaji['nama'],
						'referensi_id' => NULL,
						'blok_stasiun_id' => $blok['blok_stasiun_id'],
						'kegiatan_id' => $blok['kegiatan_id'], // $value['kegiatan_id'],
						'kendaraan_mesin_id' =>  $blok['kendaraan_mesin_id'],
						'umur_tanam_blok' => $blok['umur_tanam_blok'],
						'karyawan_id' => $arrGaji['karyawan_id'], //karyawan,
						'divisi_id' =>  $arrGaji['sub_bagian_id'],
						'tipe' => $blok['tipe'],
						'hk' => 0
					);
					$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
					$dataDebet = array(
						'lokasi_id' => $periodeGaji['lokasi_id'],
						'jurnal_id' => $id_header,
						'acc_akun_id' => $akun_hutang_gaji, //akun biaya Panen,
						'debet' => $nilai_proporsi,
						'kredit' => 0,
						'ket' => 'ALK_GAJI_DIFF Proporsi Selisih gaji:' . $arrGaji['divisi'] . ' Karyawan: ' . $arrGaji['nama'] . ",",
						'no_referensi' => 'ALK_GAJI_DIFF_' . $periodeGaji['nama'],
						'referensi_id' => NULL,
						'blok_stasiun_id' => NULL,
						'kegiatan_id' => null, //kegiatan panen,
						'kendaraan_mesin_id' => NULL,
						'karyawan_id' => NULL, //karyawan,
						'umur_tanam_blok' => null,
						'divisi_id' => null,
						'tipe' => $blok['tipe'],
					);
					$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
				}
				// $dataDebet = array(
				// 	'lokasi_id' => $periodeGaji['lokasi_id'],
				// 	'jurnal_id' => $id_header,
				// 	'acc_akun_id' => $akun_hutang_gaji, //akun biaya Panen,
				// 	'debet' => ($selisih_gaji_jurnal) * -1,
				// 	'kredit' => 0,
				// 	'ket' => 'Biaya Gaji Proporsi Selisih gaji:' . $arrGaji['divisi'] . ' Karyawan: ' . $arrGaji['nama'] . ",",
				// 	'no_referensi' => 'ALK_GAJI_DIFF_' . $periodeGaji['nama'],
				// 	'referensi_id' => NULL,
				// 	'blok_stasiun_id' => NULL,
				// 	'kegiatan_id' => null, //kegiatan panen,
				// 	'kendaraan_mesin_id' => NULL,
				// 	'karyawan_id' => NULL, //karyawan,
				// 	'umur_tanam_blok' => null,
				// 	'divisi_id' => null,
				// 	'tipe' => 'upah',
				// );
				// $id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
			}

			$totalgajiDiterima = $totalgajiDiterima + $gajiDiterima;
		}
		$this->load->library('Autonumber');
		/* Get rekap sudah dijurnal di Jurnal Upah */

		$arr_tipe = array('upah' => 'Upah', 'premi' => "Premi", 'upah_pengawas' => 'Upah Pengawas', 'premi_pengawas' => 'Premi Pengawas', 'bpjs' => 'BPJS', 'bpjs_kes' => 'BPJS_KES');
		foreach ($arr_tipe as $t => $tipe) {
			$sql_jurnal = "	SELECT b.lokasi_id,b.acc_akun_id,b.kegiatan_id,b.blok_stasiun_id,b.kendaraan_mesin_id,
			b.umur_tanam_blok,sum(debet-kredit)AS nilai,sum(hk)AS hk,c.nama,b.tipe
			FROM acc_jurnal_upah_ht a inner join acc_jurnal_upah_dt b 
			on a.id=b.jurnal_id INNER JOIN acc_akun c ON b.acc_akun_id=c.id
			WHERE 1=1 
			AND a.tanggal between '" . $periodeGaji['tgl_awal'] . "' and '" . $periodeGaji['tgl_akhir'] . "'
			AND b.tipe='" . $t . "' AND b.lokasi_id='" . $periodeGaji['lokasi_id'] . "'
			GROUP BY b.lokasi_id,b.acc_akun_id,b.kegiatan_id,b.blok_stasiun_id,
			b.kendaraan_mesin_id,b.umur_tanam_blok,c.nama,b.tipe ;	";

			$res_jurnal = $this->db->query($sql_jurnal)->result_array();
			// $this->set_response(array("status" => "NOT OK", "data" =>var_dump($res_jurnal)), REST_Controller::HTTP_NOT_FOUND);

			if ($res_jurnal) {

				$no_jurnal = $this->autonumber->jurnal_auto($periodeGaji['lokasi_id'], $tanggal, 'ALK_GAJI');
				$dataH = array(
					'no_jurnal' => $no_jurnal,
					'lokasi_id' => $periodeGaji['lokasi_id'],
					'tanggal' => $tanggal,
					'no_ref' => 'ALK_GAJI_' . strtoupper($t) . " " . $periodeGaji['nama'],
					'ref_id' => $periodeGaji['id'],
					'tipe_jurnal' => 'AUTO',
					'modul' => 'ALK_GAJI',
					'keterangan' =>  'ALK_GAJI_' . strtoupper($t) . " " . $periodeGaji['nama'],
					'is_posting' => 1,
				);
				$id_header = $this->AccJurnalModel->create_header($dataH);

				foreach ($res_jurnal as $key => $jurnal) {
					$jum_proses++;
					$str_hk = "";
					if ($jurnal['hk']) {

						if ($jurnal['tipe'] == 'upah') {
							$str_hk = $jurnal['hk'] > 0 ? $jurnal['hk'] . "HK" : "";
						}
					}
					if (($jurnal['nilai']) != 0) {
						$dataJurnalDtl = array(
							'lokasi_id' => $periodeGaji['lokasi_id'],
							'jurnal_id' => $id_header,
							'acc_akun_id' => $jurnal['acc_akun_id'], //akun biaya Panen,
							'debet' => ($jurnal['nilai'] > 0) ? $jurnal['nilai'] : 0,
							'kredit' => ($jurnal['nilai'] < 0) ? ($jurnal['nilai'] * -1) : 0,
							'ket' => 'ALK_GAJI_' . strtoupper($t) . " " . $str_hk,
							'no_referensi' => 'ALK_GAJI_' . strtoupper($t) . " " . $periodeGaji['nama'],
							'referensi_id' => NULL,
							'blok_stasiun_id' => $jurnal['blok_stasiun_id'],
							'kegiatan_id' => $jurnal['kegiatan_id'], //kegiatan panen,
							'kendaraan_mesin_id' => $jurnal['kendaraan_mesin_id'],
							'karyawan_id' => 0, //karyawan,
							'umur_tanam_blok' => $jurnal['umur_tanam_blok'],
							'divisi_id' => null,

						);
						$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataJurnalDtl);
					}
				}
			}
		}
		$this->db->where('id', $id);
		$this->db->update('payroll_periode_gaji', array('is_posting'    => '1', "tgl_posting" => date('Y-m-d H:i:s')));

		$hasil['jum'] = $jum_proses; // count($dataGaji);
		// var_dump($hasil);exit;
		return $hasil;
	}
	function start_posting_payroll_biaya_post()
	{
		// $id = $this->post('id');
		// $periodeGaji = $this->HrmsPeriodeGajiModel->retrieve($id);
		// if (empty($periodeGaji)) {
		// 	$this->set_response(array("status" => "NOT OK", "data" => 'null'), REST_Controller::HTTP_NOT_FOUND);
		// 	return;
		// }
		// $res_akun = $this->db->query("SELECT * from acc_auto_jurnal 
		// where kode='ALOKASI_GAJI_BPJS_ESTATE'")->row_array();
		// if (empty($res_akun)) {
		// 	$this->set_response(array("status" => "NOT OK", "data" => "ALOKASI_GAJI_ESTATE Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
		// 	return;
		// }
		// $akun_bpjs = $res_akun['acc_akun_id'];

		// $res_akun = $this->db->query("SELECT * from acc_auto_jurnal 
		// where kode='ALOKASI_GAJI_ESTATE'")->row_array();
		// if (empty($res_akun)) {
		// 	$this->set_response(array("status" => "NOT OK", "data" => "ALOKASI_GAJI_ESTATE Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
		// 	return;
		// }
		// $tanggal = $periodeGaji['tgl_akhir'];
		// $akun_hutang_gaji = $res_akun['acc_akun_id'];
		// $akun_debet = $res_akun['acc_akun_id_debet'];
		// $akun_kredit = $res_akun['acc_akun_id_kredit'];

		// $jum_proses = 0;
		// $this->load->library('Autonumber');
		// /* Get rekap sudah dijurnal di Jurnal Upah */
		// $this->AccJurnalModel->delete_by_ref_id_and_modul($periodeGaji['id'], 'ALK_GAJI');
		// $arr_tipe = array('upah' => 'Upah', 'premi' => "Premi", 'upah_pengawas' => 'Upah Pengawas', 'premi_pengawas' => 'Premi Pengawas', 'bpjs' => 'BPJS');
		// foreach ($arr_tipe as $t => $tipe) {
		// 	$sql_jurnal = "	SELECT b.lokasi_id,b.acc_akun_id,b.kegiatan_id,b.blok_stasiun_id,b.kendaraan_mesin_id,
		// 	b.umur_tanam_blok,sum(debet-kredit)AS nilai,sum(hk)AS hk,c.nama,b.tipe
		// 	FROM acc_jurnal_upah_ht a inner join acc_jurnal_upah_dt b 
		// 	on a.id=b.jurnal_id INNER JOIN acc_akun c ON b.acc_akun_id=c.id
		// 	WHERE 1=1 
		// 	AND a.tanggal between '" . $periodeGaji['tgl_awal'] . "' and '" . $periodeGaji['tgl_akhir'] . "'
		// 	AND b.tipe='" . $t . "' AND b.lokasi_id='" . $periodeGaji['lokasi_id'] . "'
		// 	GROUP BY b.lokasi_id,b.acc_akun_id,b.kegiatan_id,b.blok_stasiun_id,
		// 	b.kendaraan_mesin_id,b.umur_tanam_blok,c.nama,b.tipe ;	";

		// 	$res_jurnal = $this->db->query($sql_jurnal)->result_array();
		// 	// $this->set_response(array("status" => "NOT OK", "data" =>var_dump($res_jurnal)), REST_Controller::HTTP_NOT_FOUND);

		// 	if ($res_jurnal) {

		// 		$no_jurnal = $this->autonumber->jurnal_auto($periodeGaji['lokasi_id'], $tanggal, 'ALK_GAJI');
		// 		$dataH = array(
		// 			'no_jurnal' => $no_jurnal,
		// 			'lokasi_id' => $periodeGaji['lokasi_id'],
		// 			'tanggal' => $tanggal,
		// 			'no_ref' => 'ALK_GAJI_' . strtoupper($t) . " " . $periodeGaji['nama'],
		// 			'ref_id' => $periodeGaji['id'],
		// 			'tipe_jurnal' => 'AUTO',
		// 			'modul' => 'ALK_GAJI',
		// 			'keterangan' =>  'ALK_GAJI_' . strtoupper($t) . " " . $periodeGaji['nama'],
		// 			'is_posting' => 1,
		// 		);
		// 		$id_header = $this->AccJurnalModel->create_header($dataH);

		// 		foreach ($res_jurnal as $key => $jurnal) {
		// 			$jum_proses++;
		// 			$str_hk = "";
		// 			if ($jurnal['hk']) {

		// 				if ($jurnal['tipe'] == 'upah') {
		// 					$str_hk = $jurnal['hk'] > 0 ? $jurnal['hk'] . "HK" : "";
		// 				}
		// 			}
		// 			$dataJurnalDtl = array(
		// 				'lokasi_id' => $periodeGaji['lokasi_id'],
		// 				'jurnal_id' => $id_header,
		// 				'acc_akun_id' => $jurnal['acc_akun_id'], //akun biaya Panen,
		// 				'debet' => ($jurnal['nilai'] > 0) ? $jurnal['nilai'] : 0,
		// 				'kredit' => ($jurnal['nilai'] < 0) ? ($jurnal['nilai'] * -1) : 0,
		// 				'ket' => 'ALK_GAJI_' . strtoupper($t) . " " . $str_hk,
		// 				'no_referensi' => 'ALK_GAJI_' . strtoupper($t) . " " . $periodeGaji['nama'],
		// 				'referensi_id' => NULL,
		// 				'blok_stasiun_id' => $jurnal['blok_stasiun_id'],
		// 				'kegiatan_id' => $jurnal['kegiatan_id'], //kegiatan panen,
		// 				'kendaraan_mesin_id' => $jurnal['kendaraan_mesin_id'],
		// 				'karyawan_id' => 0, //karyawan,
		// 				'umur_tanam_blok' => $jurnal['umur_tanam_blok'],
		// 				'divisi_id' => null,

		// 			);
		// 			$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataJurnalDtl);
		// 		}
		// 	}
		// }

		// $this->db->update('payroll_periode_gaji', array('is_posting'    => '1', "tgl_posting" => date('Y-m-d H:i:s')));

		// $hasil['jum'] = $jum_proses;
		// $this->set_response(array("status" => "OK", "data" => $hasil['jum']), REST_Controller::HTTP_OK);
		// count($dataGaji);
		// var_dump($hasil);exit;
		// return $hasil;
	}
	function start_posting_payroll_jamsostek_estate($id)
	{
		$periodeGaji = $this->HrmsPeriodeGajiModel->retrieve($id);
		if (empty($periodeGaji)) {
			$this->set_response(array("status" => "NOT OK", "data" => 'null'), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$res_akun = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='ALOKASI_GAJI_JAMSOSTEK_ESTATE'")->row_array();
		if (empty($res_akun)) {
			$this->set_response(array("status" => "NOT OK", "data" => "ALOKASI_GAJI_JAMSOSTEK_ESTATE Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$akun_bpjs = $res_akun['acc_akun_id_kredit']; // BPJS KETENAGA KERJAAN

		$akun_hutang_gaji = $res_akun['acc_akun_id'];
		$akun_debet = $res_akun['acc_akun_id_debet'];
		$akun_kredit = $res_akun['acc_akun_id_kredit'];
		/* dELETE JURNAL JAMSOSTEK YG SDH DIPOSTING */
		$this->AccJurnalModel->delete_by_ref_id_and_modul($periodeGaji['id'], 'ALK_GAJI_BPJS_ESTATE');
		$this->AccJurnalModel->delete_by_ref_id_and_modul($periodeGaji['id'], 'ALK_GAJI_JAMSOSTEK_ESTATE');
		/* dELETE JURNAL UPAH JAMSOSTEK YG SDH DIPOSTING */
		$this->AccJurnalUpahModel->delete_by_ref_id_and_modul($periodeGaji['id'], 'ALK_GAJI_BPJS_ESTATE');
		$this->AccJurnalUpahModel->delete_by_ref_id_and_modul($periodeGaji['id'], 'ALK_GAJI_JAMSOSTEK_ESTATE');

		$pendapatan = $this->db->query("select * from payroll_tipe_gaji where jenis=1 or jenis=2 or jenis=3 order by urut")->result_array();
		$potongan = $this->db->query("select * from payroll_tipe_gaji where jenis=0 order by urut")->result_array();


		$sql = "
		SELECT a.*,c.nip,c.nama,d.nama as jabatan,e.nama as departemen ,f.nama as divisi,c.sub_bagian_id
		FROM payroll_gaji_tr_hd a 
		left join payroll_karyawan_gaji b on a.karyawan_id=b.karyawan_id
        inner join karyawan c on a.karyawan_id=c.id 
		left join payroll_jabatan d on c.jabatan_id=d.id
        left join payroll_department e on c.departemen_id=e.id
		left join gbm_organisasi f on c.sub_bagian_id=f.id
        where a.periode_gaji_id=" . $id . "";
		$sql = $sql . " order by nama;";

		$dataGaji = $this->db->query($sql)->result_array();

		/* Cek biaya per karyawan yg sudah dijurnal di jurnalUpah*/
		$query_cek_jurnal = "SELECT SUM(debet)as nilai,karyawan_id FROM acc_jurnal_upah_dt a 
		inner join acc_jurnal_upah_ht b on a.jurnal_id=b.id 
		WHERE 1=1
		and tanggal between '" . $periodeGaji['tgl_awal'] . "' and '" . $periodeGaji['tgl_akhir'] . "'
		and b.lokasi_id= " . $periodeGaji['lokasi_id'] . "
		and modul not in('ALK_GAJI','ALK_GAJI_ESTATE','ALK_GAJI_BPJS','ALK_GAJI_DIFF','ALK_GAJI_BPJS_KES','ALK_GAJI_BPJS_ESTATE','ALK_GAJI_BPJS_ESTATE_KES','ALK_GAJI_JAMSOSTEK_ESTATE','ALK_GAJI_BPJS_KES_ESTATE','ALK_GAJI_CATU_BERAS_ESTATE')
		and karyawan_id IS NOT null
		group by karyawan_id";
		$res_gaji_jurnal = $this->db->query($query_cek_jurnal)->result_array();
		$gaji_jurnal = array();
		foreach ($res_gaji_jurnal as $key => $gaji) {
			$gaji_jurnal[$gaji['karyawan_id']] = $gaji['nilai'];
		}

		$no = 0;
		$gaji_karyawan = array();
		$totalPendapatan = 0;
		$totalPotongan = 0;
		$totalgajiDiterima = 0;
		// Data HEADER
		$tanggal = $periodeGaji['tgl_akhir'];
		$this->load->library('Autonumber');

		$jum_proses = 0;
		foreach ($dataGaji as $key => $arrGaji) {
			$no++;
			$jumPendapatan = 0;
			$jumPotongan = 0;
			$gajiDiterima = 0;
			// looping pendapatan
			foreach ($pendapatan as $key => $value) {
				$dataPendapatan = $this->db->query("SELECT a.*,b.jenis,b.nama,b.urut FROM payroll_gaji_tr_dt a inner join payroll_tipe_gaji b
                    on a.tipe_gaji=b.id
                    where a.id_hd=" . $arrGaji['id'] . " and a.tipe_gaji=" . $value['id'] . ";")->row_array();
				if (empty($dataPendapatan)) {
				} else {
					$jumPendapatan = $jumPendapatan + $dataPendapatan['nilai'];
				}
			}

			$jumPendapatan = $jumPendapatan + $arrGaji['gapok'] +  $arrGaji['lembur'] + $arrGaji['premi'];
			// looping potongan
			foreach ($potongan as $key => $value) {
				$dataPotongan = $this->db->query("SELECT a.*,b.jenis,b.nama,b.urut FROM payroll_gaji_tr_dt a inner join payroll_tipe_gaji b
                    on a.tipe_gaji=b.id
                    where a.id_hd=" . $arrGaji['id'] . " and a.tipe_gaji=" . $value['id'] . ";")->row_array();
				if (empty($dataPotongan)) {
					$total[$value['id']] = 0;
				} else {
					$jumPotongan = $jumPotongan + $dataPotongan['nilai'];
				}
			}

			$jumPotongan = $jumPotongan + $arrGaji['hk_potongan_gaji'] + $arrGaji['denda'] + $arrGaji['jht'] +  $arrGaji['jp'] + $arrGaji['jkn']; //+ $dataHeader['pph'];
			$gajiDiterima = $jumPendapatan - $jumPotongan; // + $arrGaji['nilai_rp_catu'];

			$nilai_gaji_jurnal = 0;
			if ($gaji_jurnal[$arrGaji['karyawan_id']]) {
				$nilai_gaji_jurnal = $gaji_jurnal[$arrGaji['karyawan_id']];
			}

			$selisih_gaji_jurnal =	$gajiDiterima - $nilai_gaji_jurnal;
			if ($gajiDiterima < 0) {
				continue;
			}

			/* start JURNAL JAMSOSTEK */
			$jum_proses++;
			$nilai_bpjs = $arrGaji['jkk_perusahaan'] + $arrGaji['jkm_perusahaan'];
			$no_jurnal = $this->autonumber->jurnal_upah_auto($periodeGaji['lokasi_id'], $tanggal, 'ALK_GAJI_JAMSOSTEK_ESTATE');
			$dataH = array(
				'no_jurnal' => $no_jurnal,
				'lokasi_id' => $periodeGaji['lokasi_id'],
				'tanggal' => $tanggal,
				'no_ref' => 'ALK_GAJI_JAMSOSTEK_ESTATE_' . $periodeGaji['nama'],
				'ref_id' => $periodeGaji['id'],
				'tipe_jurnal' => 'AUTO',
				'modul' => 'ALK_GAJI_JAMSOSTEK_ESTATE',
				'keterangan' => 'ALK_GAJI_JAMSOSTEK_ESTATE Karyawan: ' . $arrGaji['nama'] . ",",
				'is_posting' => 1,
			);

			$id_header_bpjs = $this->AccJurnalUpahModel->create_header($dataH);

			$query_cek_jurnal_blok = "SELECT  sum(debet)as nilai, blok_stasiun_id,kegiatan_id ,acc_akun_id,umur_tanam_blok,kendaraan_mesin_id,tipe FROM acc_jurnal_upah_dt  a 
								inner join acc_jurnal_upah_ht b on a.jurnal_id=b.id  WHERE karyawan_id=" . $arrGaji['karyawan_id'] . "
										and tanggal between '" . $periodeGaji['tgl_awal'] . "' and '" . $periodeGaji['tgl_akhir'] . "'
										and b.lokasi_id =" . $periodeGaji['lokasi_id'] . "
										and modul not in('ALK_GAJI','ALK_GAJI_ESTATE','ALK_GAJI_BPJS','ALK_GAJI_DIFF','ALK_GAJI_BPJS_KES','ALK_GAJI_BPJS_ESTATE','ALK_GAJI_BPJS_ESTATE_KES','ALK_GAJI_JAMSOSTEK_ESTATE','ALK_GAJI_BPJS_KES_ESTATE','ALK_GAJI_CATU_BERAS_ESTATE')
										group by blok_stasiun_id,kegiatan_id ,acc_akun_id,umur_tanam_blok,kendaraan_mesin_id,tipe";
			$res_blok = $this->db->query($query_cek_jurnal_blok)->result_array();
			$jum_blok = count($res_blok);
			if ($jum_blok == 0) {

				continue;
			}

			foreach ($res_blok as $key => $blok) {
				$nilai_per_item = $blok['nilai'];
				$nilai_proporsi_bpjs = $nilai_bpjs * ($nilai_per_item / $nilai_gaji_jurnal);
				// $nilai_proporsi_bpjs_kes = $nilai_bpjs_kes * ($nilai_per_item / $nilai_gaji_jurnal);

				$dataDebetBpjs = array(
					'lokasi_id' => $periodeGaji['lokasi_id'],
					'jurnal_id' => $id_header_bpjs,
					'acc_akun_id' => $blok['acc_akun_id'],
					'debet' => $nilai_proporsi_bpjs,
					'kredit' => 0,
					'ket' => 'ALK_GAJI_JAMSOSTEK_ESTATE Afdeling: ,' . $arrGaji['divisi'] . ' Karyawan: ' . $arrGaji['nama'] . ",",
					'no_referensi' => 'ALK_GAJI_JAMSOSTEK_ESTATE_' . $periodeGaji['nama'],
					'referensi_id' => NULL,
					'blok_stasiun_id' => $blok['blok_stasiun_id'],
					'kegiatan_id' => $blok['kegiatan_id'],
					'kendaraan_mesin_id' =>  $blok['kendaraan_mesin_id'],
					'karyawan_id' => $arrGaji['karyawan_id'],
					'umur_tanam_blok' => $blok['umur_tanam_blok'],
					'divisi_id' => $arrGaji['sub_bagian_id'],
					'tipe' => 'jamsostek'
				);
				$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header_bpjs, $dataDebetBpjs);
			}
			$dataKreditBpjs = array(
				'lokasi_id' => $periodeGaji['lokasi_id'],
				'jurnal_id' => $id_header_bpjs,
				'acc_akun_id' => $akun_bpjs,
				'debet' => 0,
				'kredit' => $nilai_bpjs,
				'ket' => 'Biaya BPJS Afdeling: ,' . $arrGaji['divisi'] . ' Karyawan: ' . $arrGaji['nama'] . ",",
				'no_referensi' => 'ALK_GAJI_JAMSOSTEK_ESTATE_' . $periodeGaji['nama'],
				'referensi_id' => NULL,
				'blok_stasiun_id' => NULL,
				'kegiatan_id' => NULL,
				'kendaraan_mesin_id' => NULL,
				'divisi_id' => null,
				'karyawan_id' => NULL,
				'tipe' => 'jamsostek'
			);
			$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header_bpjs, $dataKreditBpjs);
			// /* END JURNAL JAMSOSTEK */


		}
		$this->load->library('Autonumber');
		/* Get rekap sudah dijurnal di Jurnal Upah */
		$arr_tipe = array('jamsostek' => 'JAMSOSTEK');
		foreach ($arr_tipe as $t => $tipe) {
			$sql_jurnal = "	SELECT b.lokasi_id,b.acc_akun_id,b.kegiatan_id,b.blok_stasiun_id,b.kendaraan_mesin_id,
			b.umur_tanam_blok,sum(debet-kredit)AS nilai,sum(hk)AS hk,c.nama,b.tipe
			FROM acc_jurnal_upah_ht a inner join acc_jurnal_upah_dt b 
			on a.id=b.jurnal_id INNER JOIN acc_akun c ON b.acc_akun_id=c.id
			WHERE 1=1 
			AND a.tanggal between '" . $periodeGaji['tgl_awal'] . "' and '" . $periodeGaji['tgl_akhir'] . "'
			AND b.tipe='" . $t . "' AND b.lokasi_id='" . $periodeGaji['lokasi_id'] . "'
			GROUP BY b.lokasi_id,b.acc_akun_id,b.kegiatan_id,b.blok_stasiun_id,
			b.kendaraan_mesin_id,b.umur_tanam_blok,c.nama,b.tipe ;	";

			$res_jurnal = $this->db->query($sql_jurnal)->result_array();

			if ($res_jurnal) {
				$no_jurnal = $this->autonumber->jurnal_auto($periodeGaji['lokasi_id'], $tanggal, 'ALK_GAJI_JAMSOSTEK_ESTATE');
				$dataH = array(
					'no_jurnal' => $no_jurnal,
					'lokasi_id' => $periodeGaji['lokasi_id'],
					'tanggal' => $tanggal,
					'no_ref' => 'ALK_GAJI_JAMSOSTEK_ESTATE_' . strtoupper($t) . " " . $periodeGaji['nama'],
					'ref_id' => $periodeGaji['id'],
					'tipe_jurnal' => 'AUTO',
					'modul' => 'ALK_GAJI_JAMSOSTEK_ESTATE',
					'keterangan' =>  'ALK_GAJI_JAMSOSTEK_ESTATE_' . strtoupper($t) . " " . $periodeGaji['nama'],
					'is_posting' => 1,
				);
				$id_header = $this->AccJurnalModel->create_header($dataH);

				foreach ($res_jurnal as $key => $jurnal) {
					$jum_proses++;
					$str_hk = "";
					if ($jurnal['hk']) {

						if ($jurnal['tipe'] == 'upah') {
							$str_hk = $jurnal['hk'] > 0 ? $jurnal['hk'] . "HK" : "";
						}
					}
					if (($jurnal['nilai']) != 0) {
						$dataJurnalDtl = array(
							'lokasi_id' => $periodeGaji['lokasi_id'],
							'jurnal_id' => $id_header,
							'acc_akun_id' => $jurnal['acc_akun_id'], //akun biaya ,
							'debet' => ($jurnal['nilai'] > 0) ? $jurnal['nilai'] : 0,
							'kredit' => ($jurnal['nilai'] < 0) ? ($jurnal['nilai'] * -1) : 0,
							'ket' => 'ALK_GAJI_JAMSOSTEK_ESTATE_' . strtoupper($t) . " " . $str_hk,
							'no_referensi' => 'ALK_GAJI_JAMSOSTEK_ESTATE_' . strtoupper($t) . " " . $periodeGaji['nama'],
							'referensi_id' => NULL,
							'blok_stasiun_id' => $jurnal['blok_stasiun_id'],
							'kegiatan_id' => $jurnal['kegiatan_id'], //kegiatan ,
							'kendaraan_mesin_id' => $jurnal['kendaraan_mesin_id'],
							'karyawan_id' => 0, //karyawan,
							'umur_tanam_blok' => $jurnal['umur_tanam_blok'],
							'divisi_id' => null,

						);
						$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataJurnalDtl);
					}
				}
			}
		}
		$this->db->where('id', $id);
		$this->db->update('payroll_periode_gaji', array('is_posting_jamsostek'    => '1', "tgl_posting_jamsostek" => date('Y-m-d H:i:s')));

		$hasil['jum'] = $jum_proses; // count($dataGaji);
		// var_dump($hasil);exit;
		return $hasil;
	}
	function start_posting_payroll_catu_beras_estate($id)
	{
		$periodeGaji = $this->HrmsPeriodeGajiModel->retrieve($id);
		if (empty($periodeGaji)) {
			$this->set_response(array("status" => "NOT OK", "data" => 'null'), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$res_akun = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='ALOKASI_GAJI_CATU_BERAS_ESTATE'")->row_array();
		if (empty($res_akun)) {
			$this->set_response(array("status" => "NOT OK", "data" => "ALOKASI_GAJI_CATU_BERAS_ESTATE Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$akun_bpjs = $res_akun['acc_akun_id_kredit']; // AKUN CATU BERAS

		$akun_hutang_gaji = $res_akun['acc_akun_id'];
		$akun_debet = $res_akun['acc_akun_id_debet'];
		$akun_kredit = $res_akun['acc_akun_id_kredit'];
		/* dELETE JURNAL JAMSOSTEK YG SDH DIPOSTING */
		$this->AccJurnalModel->delete_by_ref_id_and_modul($periodeGaji['id'], 'ALK_GAJI_CATU_BERAS_ESTATE');

		/* dELETE JURNAL UPAH JAMSOSTEK YG SDH DIPOSTING */
		$this->AccJurnalUpahModel->delete_by_ref_id_and_modul($periodeGaji['id'], 'ALK_GAJI_CATU_BERAS_ESTATE');


		$pendapatan = $this->db->query("select * from payroll_tipe_gaji where jenis=1 or jenis=2 or jenis=3 order by urut")->result_array();
		$potongan = $this->db->query("select * from payroll_tipe_gaji where jenis=0 order by urut")->result_array();


		$sql = "
		SELECT a.*,c.nip,c.nama,d.nama as jabatan,e.nama as departemen ,f.nama as divisi,c.sub_bagian_id
		FROM payroll_gaji_tr_hd a 
		left join payroll_karyawan_gaji b on a.karyawan_id=b.karyawan_id
        inner join karyawan c on a.karyawan_id=c.id 
		left join payroll_jabatan d on c.jabatan_id=d.id
        left join payroll_department e on c.departemen_id=e.id
		left join gbm_organisasi f on c.sub_bagian_id=f.id
        where a.periode_gaji_id=" . $id . "";
		$sql = $sql . " order by nama;";

		$dataGaji = $this->db->query($sql)->result_array();

		/* Cek biaya per karyawan yg sudah dijurnal di jurnalUpah*/
		$query_cek_jurnal = "SELECT SUM(debet)as nilai,karyawan_id FROM acc_jurnal_upah_dt a 
		inner join acc_jurnal_upah_ht b on a.jurnal_id=b.id 
		WHERE 1=1
		and tanggal between '" . $periodeGaji['tgl_awal'] . "' and '" . $periodeGaji['tgl_akhir'] . "'
		and b.lokasi_id= " . $periodeGaji['lokasi_id'] . "
		and modul not in('ALK_GAJI','ALK_GAJI_ESTATE','ALK_GAJI_BPJS','ALK_GAJI_DIFF','ALK_GAJI_BPJS_KES','ALK_GAJI_BPJS_ESTATE','ALK_GAJI_BPJS_ESTATE_KES','ALK_GAJI_JAMSOSTEK_ESTATE','ALK_GAJI_BPJS_KES_ESTATE','ALK_GAJI_CATU_BERAS_ESTATE')
		and karyawan_id IS NOT null
		group by karyawan_id";
		$res_gaji_jurnal = $this->db->query($query_cek_jurnal)->result_array();
		$gaji_jurnal = array();
		foreach ($res_gaji_jurnal as $key => $gaji) {
			$gaji_jurnal[$gaji['karyawan_id']] = $gaji['nilai'];
		}

		$no = 0;
		$gaji_karyawan = array();
		$totalPendapatan = 0;
		$totalPotongan = 0;
		$totalgajiDiterima = 0;
		// Data HEADER
		$tanggal = $periodeGaji['tgl_akhir'];
		$this->load->library('Autonumber');

		$jum_proses = 0;
		foreach ($dataGaji as $key => $arrGaji) {
			$no++;
			$jumPendapatan = 0;
			$jumPotongan = 0;
			$gajiDiterima = 0;
			// looping pendapatan
			foreach ($pendapatan as $key => $value) {
				$dataPendapatan = $this->db->query("SELECT a.*,b.jenis,b.nama,b.urut FROM payroll_gaji_tr_dt a inner join payroll_tipe_gaji b
                    on a.tipe_gaji=b.id
                    where a.id_hd=" . $arrGaji['id'] . " and a.tipe_gaji=" . $value['id'] . ";")->row_array();
				if (empty($dataPendapatan)) {
				} else {
					$jumPendapatan = $jumPendapatan + $dataPendapatan['nilai'];
				}
			}

			$jumPendapatan = $jumPendapatan + $arrGaji['gapok'] +  $arrGaji['lembur'] + $arrGaji['premi'];
			// looping potongan
			foreach ($potongan as $key => $value) {
				$dataPotongan = $this->db->query("SELECT a.*,b.jenis,b.nama,b.urut FROM payroll_gaji_tr_dt a inner join payroll_tipe_gaji b
                    on a.tipe_gaji=b.id
                    where a.id_hd=" . $arrGaji['id'] . " and a.tipe_gaji=" . $value['id'] . ";")->row_array();
				if (empty($dataPotongan)) {
					$total[$value['id']] = 0;
				} else {
					$jumPotongan = $jumPotongan + $dataPotongan['nilai'];
				}
			}

			$jumPotongan = $jumPotongan + $arrGaji['hk_potongan_gaji'] + $arrGaji['denda'] + $arrGaji['jht'] +  $arrGaji['jp'] + $arrGaji['jkn']; //+ $dataHeader['pph'];
			$gajiDiterima = $jumPendapatan - $jumPotongan; // + $arrGaji['nilai_rp_catu'];

			$nilai_gaji_jurnal = 0;
			if ($gaji_jurnal[$arrGaji['karyawan_id']]) {
				$nilai_gaji_jurnal = $gaji_jurnal[$arrGaji['karyawan_id']];
			}

			$selisih_gaji_jurnal =	$gajiDiterima - $nilai_gaji_jurnal;
			if ($gajiDiterima < 0) {
				continue;
			}

			/* start JURNAL catu */
			$jum_proses++;
			$nilai_catu = $arrGaji['nilai_rp_catu']; // 
			if ($nilai_catu <= 0) {
				continue;
			}
			$no_jurnal = $this->autonumber->jurnal_upah_auto($periodeGaji['lokasi_id'], $tanggal, 'ALK_GAJI_CATU_BERAS_ESTATE');
			$dataH = array(
				'no_jurnal' => $no_jurnal,
				'lokasi_id' => $periodeGaji['lokasi_id'],
				'tanggal' => $tanggal,
				'no_ref' => 'ALK_GAJI_CATU_BERAS_ESTATE_' . $periodeGaji['nama'],
				'ref_id' => $periodeGaji['id'],
				'tipe_jurnal' => 'AUTO',
				'modul' => 'ALK_GAJI_CATU_BERAS_ESTATE',
				'keterangan' => 'ALK_GAJI_CATU_BERAS_ESTATE Karyawan: ' . $arrGaji['nama'] . ",",
				'is_posting' => 1,
			);

			$id_header_bpjs = $this->AccJurnalUpahModel->create_header($dataH);

			$query_cek_jurnal_blok = "SELECT  sum(debet)as nilai, blok_stasiun_id,kegiatan_id ,acc_akun_id,umur_tanam_blok,kendaraan_mesin_id,tipe FROM acc_jurnal_upah_dt  a 
								inner join acc_jurnal_upah_ht b on a.jurnal_id=b.id  WHERE karyawan_id=" . $arrGaji['karyawan_id'] . "
										and tanggal between '" . $periodeGaji['tgl_awal'] . "' and '" . $periodeGaji['tgl_akhir'] . "'
										and b.lokasi_id =" . $periodeGaji['lokasi_id'] . "
										and modul not in('ALK_GAJI','ALK_GAJI_ESTATE','ALK_GAJI_BPJS','ALK_GAJI_DIFF','ALK_GAJI_BPJS_KES','ALK_GAJI_BPJS_ESTATE','ALK_GAJI_BPJS_ESTATE_KES','ALK_GAJI_JAMSOSTEK_ESTATE','ALK_GAJI_BPJS_KES_ESTATE','ALK_GAJI_CATU_BERAS_ESTATE')
										group by blok_stasiun_id,kegiatan_id ,acc_akun_id,umur_tanam_blok,kendaraan_mesin_id,tipe";
			$res_blok = $this->db->query($query_cek_jurnal_blok)->result_array();
			$jum_blok = count($res_blok);
			if ($jum_blok == 0) {

				continue;
			}

			foreach ($res_blok as $key => $blok) {
				$nilai_per_item = $blok['nilai'];
				$nilai_proporsi_catu = $nilai_catu * ($nilai_per_item / $nilai_gaji_jurnal);
				// $nilai_proporsi_bpjs_kes = $nilai_bpjs_kes * ($nilai_per_item / $nilai_gaji_jurnal);

				$dataDebetBpjs = array(
					'lokasi_id' => $periodeGaji['lokasi_id'],
					'jurnal_id' => $id_header_bpjs,
					'acc_akun_id' => $blok['acc_akun_id'],
					'debet' => $nilai_proporsi_catu,
					'kredit' => 0,
					'ket' => 'ALK_GAJI_CATU_BERAS_ESTATE Afdeling: ,' . $arrGaji['divisi'] . ' Karyawan: ' . $arrGaji['nama'] . ",",
					'no_referensi' => 'ALK_GAJI_CATU_BERAS_ESTATE_' . $periodeGaji['nama'],
					'referensi_id' => NULL,
					'blok_stasiun_id' => $blok['blok_stasiun_id'],
					'kegiatan_id' => $blok['kegiatan_id'],
					'kendaraan_mesin_id' =>  $blok['kendaraan_mesin_id'],
					'karyawan_id' => $arrGaji['karyawan_id'],
					'umur_tanam_blok' => $blok['umur_tanam_blok'],
					'divisi_id' => $arrGaji['sub_bagian_id'],
					'tipe' => 'catu_beras'
				);
				$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header_bpjs, $dataDebetBpjs);
			}
			$dataKreditBpjs = array(
				'lokasi_id' => $periodeGaji['lokasi_id'],
				'jurnal_id' => $id_header_bpjs,
				'acc_akun_id' => $akun_bpjs,
				'debet' => 0,
				'kredit' => $nilai_catu,
				'ket' => 'ALK_GAJI_CATU_BERAS_ESTATE Afdeling: ,' . $arrGaji['divisi'] . ' Karyawan: ' . $arrGaji['nama'] . ",",
				'no_referensi' => 'ALK_GAJI_CATU_BERAS_ESTATE_' . $periodeGaji['nama'],
				'referensi_id' => NULL,
				'blok_stasiun_id' => NULL,
				'kegiatan_id' => NULL,
				'kendaraan_mesin_id' => NULL,
				'divisi_id' => null,
				'karyawan_id' => NULL,
				'tipe' => 'catu_beras'
			);
			$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header_bpjs, $dataKreditBpjs);
			// /* END JURNAL JAMSOSTEK */


		}
		$this->load->library('Autonumber');
		/* Get rekap sudah dijurnal di Jurnal Upah */
		$arr_tipe = array('catu_beras' => 'Catu Beras');
		foreach ($arr_tipe as $t => $tipe) {
			$sql_jurnal = "	SELECT b.lokasi_id,b.acc_akun_id,b.kegiatan_id,b.blok_stasiun_id,b.kendaraan_mesin_id,
			b.umur_tanam_blok,sum(debet-kredit)AS nilai,sum(hk)AS hk,c.nama,b.tipe
			FROM acc_jurnal_upah_ht a inner join acc_jurnal_upah_dt b 
			on a.id=b.jurnal_id INNER JOIN acc_akun c ON b.acc_akun_id=c.id
			WHERE 1=1 
			AND a.tanggal between '" . $periodeGaji['tgl_awal'] . "' and '" . $periodeGaji['tgl_akhir'] . "'
			AND b.tipe='" . $t . "' AND b.lokasi_id='" . $periodeGaji['lokasi_id'] . "'
			GROUP BY b.lokasi_id,b.acc_akun_id,b.kegiatan_id,b.blok_stasiun_id,
			b.kendaraan_mesin_id,b.umur_tanam_blok,c.nama,b.tipe ;	";

			$res_jurnal = $this->db->query($sql_jurnal)->result_array();

			if ($res_jurnal) {
				$no_jurnal = $this->autonumber->jurnal_auto($periodeGaji['lokasi_id'], $tanggal, 'ALK_GAJI_CATU_BERAS_ESTATE');
				$dataH = array(
					'no_jurnal' => $no_jurnal,
					'lokasi_id' => $periodeGaji['lokasi_id'],
					'tanggal' => $tanggal,
					'no_ref' => 'ALK_GAJI_CATU_BERAS_ESTATE_' . strtoupper($t) . " " . $periodeGaji['nama'],
					'ref_id' => $periodeGaji['id'],
					'tipe_jurnal' => 'AUTO',
					'modul' => 'ALK_GAJI_CATU_BERAS_ESTATE',
					'keterangan' =>  'ALK_GAJI_CATU_BERAS_ESTATE_' . strtoupper($t) . " " . $periodeGaji['nama'],
					'is_posting' => 1,
				);
				$id_header = $this->AccJurnalModel->create_header($dataH);

				foreach ($res_jurnal as $key => $jurnal) {
					$jum_proses++;
					$str_hk = "";
					if ($jurnal['hk']) {

						if ($jurnal['tipe'] == 'upah') {
							$str_hk = $jurnal['hk'] > 0 ? $jurnal['hk'] . "HK" : "";
						}
					}
					if (($jurnal['nilai']) != 0) {
						$dataJurnalDtl = array(
							'lokasi_id' => $periodeGaji['lokasi_id'],
							'jurnal_id' => $id_header,
							'acc_akun_id' => $jurnal['acc_akun_id'], //akun biaya ,
							'debet' => ($jurnal['nilai'] > 0) ? $jurnal['nilai'] : 0,
							'kredit' => ($jurnal['nilai'] < 0) ? ($jurnal['nilai'] * -1) : 0,
							'ket' => 'ALK_GAJI_CATU_BERAS_ESTATE_' . strtoupper($t) . " " . $str_hk,
							'no_referensi' => 'ALK_GAJI_CATU_BERAS_ESTATE_' . strtoupper($t) . " " . $periodeGaji['nama'],
							'referensi_id' => NULL,
							'blok_stasiun_id' => $jurnal['blok_stasiun_id'],
							'kegiatan_id' => $jurnal['kegiatan_id'], //kegiatan ,
							'kendaraan_mesin_id' => $jurnal['kendaraan_mesin_id'],
							'karyawan_id' => 0, //karyawan,
							'umur_tanam_blok' => $jurnal['umur_tanam_blok'],
							'divisi_id' => null,

						);
						$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataJurnalDtl);
					}
				}
			}
		}
		$this->db->where('id', $id);
		$this->db->update('payroll_periode_gaji', array('is_posting_catu_beras'    => '1', "tgl_posting_catu_beras" => date('Y-m-d H:i:s')));

		$hasil['jum'] = $jum_proses; // count($dataGaji);
		// var_dump($hasil);exit;
		return $hasil;
	}
	function start_posting_payroll_jamsostek_mill($id)
	{
		$periodeGaji = $this->HrmsPeriodeGajiModel->retrieve($id);
		if (empty($periodeGaji)) {
			$this->set_response(array("status" => "NOT OK", "data" => 'null'), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$res_akun = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='ALOKASI_GAJI_JAMSOSTEK_MILL'")->row_array();
		if (empty($res_akun)) {
			$this->set_response(array("status" => "NOT OK", "data" => "ALOKASI_GAJI_JAMSOSTEK_MILL Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$akun_bpjs = $res_akun['acc_akun_id'];
		$akun_debet = $res_akun['acc_akun_id_debet'];
		$akun_kredit = $res_akun['acc_akun_id_kredit'];
		$pendapatan = $this->db->query("select * from payroll_tipe_gaji where jenis=1 or jenis=2 or jenis=3 order by urut")->result_array();
		$potongan = $this->db->query("select * from payroll_tipe_gaji where jenis=0 order by urut")->result_array();

		$sql = "
		SELECT a.*,c.nip,c.nama,d.nama as jabatan,e.nama as departemen ,f.nama as divisi,c.sub_bagian_id
		FROM payroll_gaji_tr_hd a 
		left join payroll_karyawan_gaji b on a.karyawan_id=b.karyawan_id
        left join karyawan c on a.karyawan_id=c.id 
		left join payroll_jabatan d on c.jabatan_id=d.id
        left join payroll_department e on c.departemen_id=e.id
		left join gbm_organisasi f on c.sub_bagian_id=f.id
        where a.periode_gaji_id=" . $id . "";
		$sql = $sql . " order by nama;";
		// 	$this->set_response(array("status" => "NOT OK", "data" =>$sql), REST_Controller::HTTP_OK);
		// return;
		$dataGaji = $this->db->query($sql)->result_array();
		$no = 0;

		// Data HEADER
		$tanggal = $periodeGaji['tgl_akhir'];
		$this->load->library('Autonumber');
		$this->AccJurnalModel->delete_by_ref_id_and_modul($periodeGaji['id'], 'ALK_GAJI_JAMSOSTEK_MILL');
		$jum_proses = 0;
		foreach ($dataGaji as $key => $arrGaji) {
			$no++;
			$jumPendapatan = 0;
			$jumPotongan = 0;
			$gajiDiterima = 0;
			// looping pendapatan
			foreach ($pendapatan as $key => $value) {
				$dataPendapatan = $this->db->query("SELECT a.*,b.jenis,b.nama,b.urut FROM payroll_gaji_tr_dt a inner join payroll_tipe_gaji b
                    on a.tipe_gaji=b.id
                    where a.id_hd=" . $arrGaji['id'] . " and a.tipe_gaji=" . $value['id'] . ";")->row_array();
				if (empty($dataPendapatan)) {
				} else {
					$jumPendapatan = $jumPendapatan + $dataPendapatan['nilai'];
				}
			}

			$jumPendapatan = $jumPendapatan + $arrGaji['gapok'] +  $arrGaji['lembur'] + $arrGaji['premi'];
			// looping potongan
			foreach ($potongan as $key => $value) {
				$dataPotongan = $this->db->query("SELECT a.*,b.jenis,b.nama,b.urut FROM payroll_gaji_tr_dt a inner join payroll_tipe_gaji b
                    on a.tipe_gaji=b.id
                    where a.id_hd=" . $arrGaji['id'] . " and a.tipe_gaji=" . $value['id'] . ";")->row_array();
				if (empty($dataPotongan)) {
					$total[$value['id']] = 0;
				} else {
					$jumPotongan = $jumPotongan + $dataPotongan['nilai'];
				}
			}

			$jumPotongan = $jumPotongan + $arrGaji['hk_potongan_gaji'] + $arrGaji['denda'] + $arrGaji['jht'] +  $arrGaji['jp'] + $arrGaji['jkn']; //+ $dataHeader['pph'];
			$gajiDiterima = $jumPendapatan - $jumPotongan + $arrGaji['nilai_rp_catu'];
			if ($gajiDiterima <= 0) {
				continue;
			}
			$nilai_bpjs = $arrGaji['jkk_perusahaan'] + $arrGaji['jkm_perusahaan']; // + $arrGaji['jkn_perusahaan'];
			if ($nilai_bpjs <= 0) {
				continue;
			}
			$no_jurnal = $this->autonumber->jurnal_auto($periodeGaji['lokasi_id'], $tanggal, 'ALK_GAJI_JAMSOSTEK_MILL');
			$dataH = array(
				'no_jurnal' => $no_jurnal,
				'lokasi_id' => $periodeGaji['lokasi_id'],
				'tanggal' => $tanggal,
				'no_ref' => 'ALOKASI_GAJI_JAMSOSTEK_MILL_' . $periodeGaji['nama'],
				'ref_id' => $periodeGaji['id'],
				'tipe_jurnal' => 'AUTO',
				'modul' => 'ALK_GAJI_JAMSOSTEK_MILL',
				'keterangan' => 'ALOKASI_GAJI_JAMSOSTEK_MILL: ' . $arrGaji['divisi'] . ' Karyawan: ' . $arrGaji['nama'] . ",",
				'is_posting' => 1,
			);
			$id_header = $this->AccJurnalModel->create_header($dataH);
			$dataDebet = array(
				'lokasi_id' => $periodeGaji['lokasi_id'],
				'jurnal_id' => $id_header,
				'acc_akun_id' => $akun_debet, //akun biaya,
				'debet' => $nilai_bpjs,
				'kredit' => 0,
				'ket' => 'ALOKASI_GAJI_JAMSOSTEK_MILL: ' . $arrGaji['divisi'] . ' Karyawan: ' . $arrGaji['nama'] . ",",
				'no_referensi' => 'ALOKASI_GAJI_JAMSOSTEK_MILL_' . $periodeGaji['nama'],
				'referensi_id' => NULL,
				'blok_stasiun_id' => NULL,
				'kegiatan_id' => NULL, //kegiatan panen,
				'kendaraan_mesin_id' => NULL,
				'karyawan_id' => $arrGaji['karyawan_id'], //karyawan,
				'umur_tanam_blok' => NULL,
				'divisi_id' => $arrGaji['sub_bagian_id']
			);
			$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataDebet);

			$dataKredit = array(
				'lokasi_id' => $periodeGaji['lokasi_id'],
				'jurnal_id' => $id_header,
				'acc_akun_id' => $akun_kredit, //$value['acc_akun_id'],
				'debet' => 0,
				'kredit' => $nilai_bpjs, // Akun Lawan Biaya
				'ket' => 'ALOKASI_GAJI_JAMSOSTEK_MILL',
				'no_referensi' => 'ALOKASI_GAJI_JAMSOSTEK_MILL_' . $periodeGaji['nama'],
				'referensi_id' => NULL,
				'blok_stasiun_id' => NULL,
				'kegiatan_id' => NULL, // $value['kegiatan_id'],
				'kendaraan_mesin_id' => NULL,
				'divisi_id' => null,
				'karyawan_id' => NULL, // $arrGaji['karyawan_id'], //karyawan,
			);
			$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataKredit);
		}
		$this->db->where('id', $id);
		$this->db->update('payroll_periode_gaji', array('is_posting_jamsostek'    => '1', "tgl_posting_jamsostek" => date('Y-m-d H:i:s')));

		$hasil['jum'] =  count($dataGaji);
		// var_dump($hasil);exit;
		return $hasil;
	}
	function start_posting_payroll_catu_beras_mill($id)
	{
		// ===  MAPPING ID STASIUN VS AKUN //
		// $mapping_stasiun_akun = array('431'=>'6310231 FRUIT RECEPTION & STORAGE','432'=> '6320131 STERILLIZING STATION', '433'=>'6320231 THRESHING STATION','434'=> '6320231 PRESSING STATION','435'=> '6320431 CLARIFICATION STATION','436'=> '6340131 DEPERICARPING STATION','437'=> '6320531 KERNEL RECOVERY STATION', '438'=>'6330131 BOILER STATION','439'=> '6330231 POWER STATION','440'=> '6330331 BOILER WATER TREATMENT PLANT','441'=> '6330331 RAW WATER TREATMENT PLANT','442'=> '6330431 PALM OIL MILL EFFLUENT TREATMENT PLANT','735'=>'KTR_MILL KANTOR MILL','748'=> '6330531 LAB - PKS','747'=>'6330631 WORKSHOP-MILL','739'=>'4110201' Traksi MILL);
		$mapping_stasiun_akun = array('431' => '3410', '432' => '3420', '433' => '3429', '434' => '3438', '435' => '3447', '436' => '3521', '437' => '3456', '438' => '3466', '439' => '3475', '440' => '3484', '441' => '3484', '442' => '3493', '735' => '3546', '748' => '3502', '747' => '3261', '739' => '3268');
		$akun_upah_transit_kendaraan = 3268; //4110201 - Gaji Supir Kendaraan/Alat Berat/Genset/Mesin Air

		$periodeGaji = $this->HrmsPeriodeGajiModel->retrieve($id);
		if (empty($periodeGaji)) {
			$this->set_response(array("status" => "NOT OK", "data" => 'null'), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$res_akun = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='ALOKASI_GAJI_CATU_BERAS_MILL'")->row_array();
		if (empty($res_akun)) {
			$this->set_response(array("status" => "NOT OK", "data" => "ALOKASI_GAJI_CATU_BERAS_MILL Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		// $akun_bpjs = $res_akun['acc_akun_id'];
		$akun_debet = $res_akun['acc_akun_id_debet'];
		$akun_kredit = $res_akun['acc_akun_id_kredit'];
		// $pendapatan = $this->db->query("select * from payroll_tipe_gaji where jenis=1 or jenis=2 order by urut")->result_array();
		// $potongan = $this->db->query("select * from payroll_tipe_gaji where jenis=0 order by urut")->result_array();

		$sql = "
		SELECT a.*,c.nip,c.nama,d.nama as jabatan,e.nama as departemen ,f.nama as divisi,c.sub_bagian_id
		FROM payroll_gaji_tr_hd a 
		left join payroll_karyawan_gaji b on a.karyawan_id=b.karyawan_id
        left join karyawan c on a.karyawan_id=c.id 
		left join payroll_jabatan d on c.jabatan_id=d.id
        left join payroll_department e on c.departemen_id=e.id
		left join gbm_organisasi f on c.sub_bagian_id=f.id
        where a.periode_gaji_id=" . $id . "";
		$sql = $sql . " order by nama;";
		// 	$this->set_response(array("status" => "NOT OK", "data" =>$sql), REST_Controller::HTTP_OK);
		// return;
		$dataGaji = $this->db->query($sql)->result_array();
		$no = 0;

		// Data HEADER
		$tanggal = $periodeGaji['tgl_akhir'];
		$this->load->library('Autonumber');
		$this->AccJurnalModel->delete_by_ref_id_and_modul($periodeGaji['id'], 'ALK_GAJI_CATU_BERAS_MILL');
		$jum_proses = 0;
		foreach ($dataGaji as $key => $arrGaji) {
			$akun_debet = $mapping_stasiun_akun[$arrGaji['sub_bagian_id']];
			if (!$akun_debet) { // jika tidak ada maka masukan ke gaji non staff
				$akun_debet = '3546'; // 3546=id utk akun 7110201 GAJI NON STAFF
			}
			$no++;
			$jumPendapatan = 0;
			$jumPotongan = 0;
			$gajiDiterima = 0;
			// looping pendapatan
			// foreach ($pendapatan as $key => $value) {
			// 	$dataPendapatan = $this->db->query("SELECT a.*,b.jenis,b.nama,b.urut FROM payroll_gaji_tr_dt a inner join payroll_tipe_gaji b
			//         on a.tipe_gaji=b.id
			//         where a.id_hd=" . $arrGaji['id'] . " and a.tipe_gaji=" . $value['id'] . ";")->row_array();
			// 	if (empty($dataPendapatan)) {
			// 	} else {
			// 		$jumPendapatan = $jumPendapatan + $dataPendapatan['nilai'];
			// 	}
			// }

			// $jumPendapatan = $jumPendapatan + $arrGaji['gapok'] +  $arrGaji['lembur'] + $arrGaji['premi'];
			// // looping potongan
			// foreach ($potongan as $key => $value) {
			// 	$dataPotongan = $this->db->query("SELECT a.*,b.jenis,b.nama,b.urut FROM payroll_gaji_tr_dt a inner join payroll_tipe_gaji b
			//         on a.tipe_gaji=b.id
			//         where a.id_hd=" . $arrGaji['id'] . " and a.tipe_gaji=" . $value['id'] . ";")->row_array();
			// 	if (empty($dataPotongan)) {
			// 		$total[$value['id']] = 0;
			// 	} else {
			// 		$jumPotongan = $jumPotongan + $dataPotongan['nilai'];
			// 	}
			// }

			// $jumPotongan = $jumPotongan + $arrGaji['hk_potongan_gaji'] + $arrGaji['denda'] + $arrGaji['jht'] +  $arrGaji['jp'] + $arrGaji['jkn']; //+ $dataHeader['pph'];
			// $gajiDiterima = $jumPendapatan - $jumPotongan + $arrGaji['nilai_rp_catu'];
			// if ($gajiDiterima < 0) {
			// 	continue;
			// }
			// $nilai_bpjs = $arrGaji['jkk_perusahaan'] + $arrGaji['jkm_perusahaan']; // + $arrGaji['jkn_perusahaan'];
			$nilai_catu = $arrGaji['nilai_rp_catu'];
			if ($nilai_catu <= 0) {
				continue;
			}
			$no_jurnal = $this->autonumber->jurnal_auto($periodeGaji['lokasi_id'], $tanggal, 'ALK_GAJI_CATU_BERAS_MILL');
			$dataH = array(
				'no_jurnal' => $no_jurnal,
				'lokasi_id' => $periodeGaji['lokasi_id'],
				'tanggal' => $tanggal,
				'no_ref' => 'ALK_GAJI_CATU_BERAS_MILL_' . $periodeGaji['nama'],
				'ref_id' => $periodeGaji['id'],
				'tipe_jurnal' => 'AUTO',
				'modul' => 'ALK_GAJI_CATU_BERAS_MILL',
				'keterangan' => 'ALK_GAJI_CATU_BERAS_MILL_MILL: ' . $arrGaji['divisi'] . ' Karyawan: ' . $arrGaji['nama'] . ",",
				'is_posting' => 1,
			);
			$id_header = $this->AccJurnalModel->create_header($dataH);
			if ($arrGaji['sub_bagian_id'] == 739) { // JIKA KARYAWAN TRAKSI
				$sql_traksi = "SELECT COUNT(*)AS jum_transaksi, b.karyawan_id,a.kendaraan_id,c.sub_bagian_id,c.nama,d.nama,d.id FROM trk_kegiatan_kendaraan_ht a inner join trk_kegiatan_kendaraan_dt b
				ON a.id=b.trk_kegiatan_kendaraan_id
				INNER JOIN karyawan c ON b.karyawan_id=c.id
				LEFT JOIN gbm_organisasi d ON c.sub_bagian_id=d.id
				WHERE a.lokasi_id=" . $periodeGaji['lokasi_id'] . " AND d.id=739
				and b.karyawan_id=" . $arrGaji['karyawan_id'] . " 
				AND a.tanggal BETWEEN '" . $periodeGaji['tgl_awal'] . "' AND  '" . $periodeGaji['tgl_akhir'] . "'
				 GROUP BY   b.karyawan_id,a.kendaraan_id ,c.sub_bagian_id,c.nama,d.nama,d.id";
				$resTraksi = $this->db->query($sql_traksi)->result_array();
				$countData = count($resTraksi);
				if ($countData == 0) {
					$dataDebet = array(
						'lokasi_id' => $periodeGaji['lokasi_id'],
						'jurnal_id' => $id_header,
						'acc_akun_id' => $akun_debet, //akun,
						'debet' => $nilai_catu,
						'kredit' => 0,
						'ket' => 'ALK_GAJI_CATU_BERAS_MILL: ' . $arrGaji['divisi'] . ' , Karyawan: ' . $arrGaji['nama'] . ",",
						'no_referensi' => 'ALK_GAJI_CATU_BERAS_MILL_' . $periodeGaji['nama'],
						'referensi_id' => NULL,
						'blok_stasiun_id' => null,
						'kegiatan_id' => null, //kegiatan ,
						'kendaraan_mesin_id' => NULL,
						'karyawan_id' => $arrGaji['karyawan_id'], //karyawan,
						'umur_tanam_blok' => null,
						'divisi_id' => $arrGaji['sub_bagian_id']
					);
					$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataDebet);
				} else {
					$jumTrans = 0;
					// Cari Total Jum Transaksi utk proporsi thd Kendaraan
					foreach ($resTraksi as $key => $trk) {
						$jumTrans =	$jumTrans + $trk['jum_transaksi'];
						# code...
					}
					foreach ($resTraksi as $key => $trk) {
						$nilai_proporsi = $trk['jum_transaksi'] / $jumTrans * $nilai_catu;
						$dataDebet = array(
							'lokasi_id' => $periodeGaji['lokasi_id'],
							'jurnal_id' => $id_header,
							'acc_akun_id' => $akun_upah_transit_kendaraan, //akun,
							'debet' => $nilai_proporsi,
							'kredit' => 0,
							'ket' => 'ALK_GAJI_CATU_BERAS_MILL: ' . $arrGaji['divisi'] . ' , Karyawan: ' . $arrGaji['nama'] . ",",
							'no_referensi' => 'ALK_GAJI_CATU_BERAS_MILL_' . $periodeGaji['nama'],
							'referensi_id' => NULL,
							'blok_stasiun_id' => null,
							'kegiatan_id' => null, //kegiatan ,
							'kendaraan_mesin_id' => $trk['kendaraan_id'],
							'karyawan_id' => $arrGaji['karyawan_id'], //karyawan,
							'umur_tanam_blok' => null,
							'divisi_id' => $arrGaji['sub_bagian_id']
						);
						$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataDebet);
					}
				}
			} else {
				$dataDebet = array(
					'lokasi_id' => $periodeGaji['lokasi_id'],
					'jurnal_id' => $id_header,
					'acc_akun_id' => $akun_debet, //akun biaya,
					'debet' => $nilai_catu,
					'kredit' => 0,
					'ket' => 'ALK_GAJI_CATU_BERAS_MILLL: ' . $arrGaji['divisi'] . ' Karyawan: ' . $arrGaji['nama'] . ",",
					'no_referensi' => 'ALK_GAJI_CATU_BERAS_MILL_' . $periodeGaji['nama'],
					'referensi_id' => NULL,
					'blok_stasiun_id' => NULL,
					'kegiatan_id' => NULL, //kegiatan panen,
					'kendaraan_mesin_id' => NULL,
					'karyawan_id' => $arrGaji['karyawan_id'], //karyawan,
					'umur_tanam_blok' => NULL,
					'divisi_id' => $arrGaji['sub_bagian_id']
				);
				$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataDebet);
			}
			$dataKredit = array(
				'lokasi_id' => $periodeGaji['lokasi_id'],
				'jurnal_id' => $id_header,
				'acc_akun_id' => $akun_kredit, //$value['acc_akun_id'],
				'debet' => 0,
				'kredit' => $nilai_catu, // Akun Lawan Biaya
				'ket' => 'ALK_GAJI_CATU_BERAS_MILL',
				'no_referensi' => 'ALK_GAJI_CATU_BERAS_MILL_' . $periodeGaji['nama'],
				'referensi_id' => NULL,
				'blok_stasiun_id' => NULL,
				'kegiatan_id' => NULL, // $value['kegiatan_id'],
				'kendaraan_mesin_id' => NULL,
				'divisi_id' => null,
				'karyawan_id' => NULL, // $arrGaji['karyawan_id'], //karyawan,
			);
			$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataKredit);
		}
		$this->db->where('id', $id);
		$this->db->update('payroll_periode_gaji', array('is_posting_catu_beras'    => '1', "tgl_posting_catu_beras" => date('Y-m-d H:i:s')));

		$hasil['jum'] =  count($dataGaji);
		// var_dump($hasil);exit;
		return $hasil;
	}
	function start_posting_payroll_bpjs_kesehatan_mill($id)
	{
		$periodeGaji = $this->HrmsPeriodeGajiModel->retrieve($id);
		if (empty($periodeGaji)) {
			$this->set_response(array("status" => "NOT OK", "data" => 'null'), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$res_akun = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='ALOKASI_GAJI_BPJS_KES_MILL'")->row_array();
		if (empty($res_akun)) {
			$this->set_response(array("status" => "NOT OK", "data" => "ALOKASI_GAJI_BPJS_KES_MILL Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$akun_bpjs = $res_akun['acc_akun_id'];
		$akun_debet = $res_akun['acc_akun_id_debet'];
		$akun_kredit = $res_akun['acc_akun_id_kredit'];
		$pendapatan = $this->db->query("select * from payroll_tipe_gaji where jenis=1 or jenis=2 or jenis=3 order by urut")->result_array();
		$potongan = $this->db->query("select * from payroll_tipe_gaji where jenis=0 order by urut")->result_array();

		$sql = "
		SELECT a.*,c.nip,c.nama,d.nama as jabatan,e.nama as departemen ,f.nama as divisi,c.sub_bagian_id
		FROM payroll_gaji_tr_hd a 
		left join payroll_karyawan_gaji b on a.karyawan_id=b.karyawan_id
        left join karyawan c on a.karyawan_id=c.id 
		left join payroll_jabatan d on c.jabatan_id=d.id
        left join payroll_department e on c.departemen_id=e.id
		left join gbm_organisasi f on c.sub_bagian_id=f.id
        where a.periode_gaji_id=" . $id . "";
		$sql = $sql . " order by nama;";
		// 	$this->set_response(array("status" => "NOT OK", "data" =>$sql), REST_Controller::HTTP_OK);
		// return;
		$dataGaji = $this->db->query($sql)->result_array();
		$no = 0;

		// Data HEADER
		$tanggal = $periodeGaji['tgl_akhir'];
		$this->load->library('Autonumber');
		$this->AccJurnalModel->delete_by_ref_id_and_modul($periodeGaji['id'], 'ALK_GAJI_BPJS_KES_MILL');
		$jum_proses = 0;
		foreach ($dataGaji as $key => $arrGaji) {
			$no++;
			$jumPendapatan = 0;
			$jumPotongan = 0;
			$gajiDiterima = 0;
			// looping pendapatan
			foreach ($pendapatan as $key => $value) {
				$dataPendapatan = $this->db->query("SELECT a.*,b.jenis,b.nama,b.urut FROM payroll_gaji_tr_dt a inner join payroll_tipe_gaji b
                    on a.tipe_gaji=b.id
                    where a.id_hd=" . $arrGaji['id'] . " and a.tipe_gaji=" . $value['id'] . ";")->row_array();
				if (empty($dataPendapatan)) {
				} else {
					$jumPendapatan = $jumPendapatan + $dataPendapatan['nilai'];
				}
			}

			$jumPendapatan = $jumPendapatan + $arrGaji['gapok'] +  $arrGaji['lembur'] + $arrGaji['premi'];
			// looping potongan
			foreach ($potongan as $key => $value) {
				$dataPotongan = $this->db->query("SELECT a.*,b.jenis,b.nama,b.urut FROM payroll_gaji_tr_dt a inner join payroll_tipe_gaji b
                    on a.tipe_gaji=b.id
                    where a.id_hd=" . $arrGaji['id'] . " and a.tipe_gaji=" . $value['id'] . ";")->row_array();
				if (empty($dataPotongan)) {
					$total[$value['id']] = 0;
				} else {
					$jumPotongan = $jumPotongan + $dataPotongan['nilai'];
				}
			}

			$jumPotongan = $jumPotongan + $arrGaji['hk_potongan_gaji'] + $arrGaji['denda'] + $arrGaji['jht'] +  $arrGaji['jp'] + $arrGaji['jkn']; //+ $dataHeader['pph'];
			$gajiDiterima = $jumPendapatan - $jumPotongan + $arrGaji['nilai_rp_catu'];
			if ($gajiDiterima <= 0) {
				continue;
			}
			$nilai_bpjs =  $arrGaji['jkn_perusahaan'];
			if ($nilai_bpjs <= 0) {
				continue;
			}
			$no_jurnal = $this->autonumber->jurnal_auto($periodeGaji['lokasi_id'], $tanggal, 'ALK_GAJI_BPJS_KES_MILL');
			$dataH = array(
				'no_jurnal' => $no_jurnal,
				'lokasi_id' => $periodeGaji['lokasi_id'],
				'tanggal' => $tanggal,
				'no_ref' => 'ALOKASI_GAJI_BPJS_KES_MILL_' . $periodeGaji['nama'],
				'ref_id' => $periodeGaji['id'],
				'tipe_jurnal' => 'AUTO',
				'modul' => 'ALK_GAJI_BPJS_KES_MILL',
				'keterangan' => 'ALOKASI_GAJI_BPJS_KES_MILL:' . $arrGaji['divisi'] . ' Karyawan: ' . $arrGaji['nama'] . ",",
				'is_posting' => 1,
			);
			$id_header = $this->AccJurnalModel->create_header($dataH);

			$dataDebet = array(
				'lokasi_id' => $periodeGaji['lokasi_id'],
				'jurnal_id' => $id_header,
				'acc_akun_id' => $akun_debet, //akun biaya ,
				'debet' => $nilai_bpjs,
				'kredit' => 0,
				'ket' => 'ALOKASI_GAJI_BPJS_KES_MILL: ' . $arrGaji['divisi'] . ' Karyawan: ' . $arrGaji['nama'] . ",",
				'no_referensi' => 'ALOKASI_GAJI_BPJS_KES_MILL_' . $periodeGaji['nama'],
				'referensi_id' => NULL,
				'blok_stasiun_id' => NULL,
				'kegiatan_id' => NULL, //kegiatan ,
				'kendaraan_mesin_id' => NULL,
				'karyawan_id' => $arrGaji['karyawan_id'], //karyawan,
				'umur_tanam_blok' => NULL,
				'divisi_id' => $arrGaji['sub_bagian_id']
			);
			$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataDebet);

			$dataKredit = array(
				'lokasi_id' => $periodeGaji['lokasi_id'],
				'jurnal_id' => $id_header,
				'acc_akun_id' => $akun_kredit, //$value['acc_akun_id'],
				'debet' => 0,
				'kredit' => $nilai_bpjs, // Akun Lawan Biaya
				'ket' => 'ALOKASI_GAJI_BPJS_KES_MILL',
				'no_referensi' => 'ALOKASI_GAJI_BPJS_KES_MILL_' . $periodeGaji['nama'],
				'referensi_id' => NULL,
				'blok_stasiun_id' => NULL,
				'kegiatan_id' => NULL, // $value['kegiatan_id'],
				'kendaraan_mesin_id' => NULL,
				'divisi_id' => null,
				'karyawan_id' => NULL, // $arrGaji['karyawan_id'], //karyawan,
			);
			$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataKredit);
		}
		$this->db->where('id', $id);
		$this->db->update('payroll_periode_gaji', array('is_posting_bpjs_kesehatan'    => '1', "tgl_posting_bpjs_kesehatan" => date('Y-m-d H:i:s')));

		$hasil['jum'] =  count($dataGaji);
		// var_dump($hasil);exit;
		return $hasil;
	}
	function start_posting_payroll_bpjs_kesehatan_estate($id)
	{
		$periodeGaji = $this->HrmsPeriodeGajiModel->retrieve($id);
		if (empty($periodeGaji)) {
			$this->set_response(array("status" => "NOT OK", "data" => 'null'), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$res_akun = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='ALOKASI_GAJI_BPJS_KES_ESTATE'")->row_array();
		if (empty($res_akun)) {
			$this->set_response(array("status" => "NOT OK", "data" => "ALOKASI_GAJI_BPJS_KES_ESTATE Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$akun_bpjs = $res_akun['acc_akun_id_kredit']; //AKUN BPJS 

		/* dELETE JURNAL JAMSOSTEK YG SDH DIPOSTING */
		$this->AccJurnalModel->delete_by_ref_id_and_modul($periodeGaji['id'], 'ALK_GAJI_BPJS_KES');
		$this->AccJurnalModel->delete_by_ref_id_and_modul($periodeGaji['id'], 'ALK_GAJI_BPJS_KES_ESTATE');
		/* dELETE JURNAL UPAH JAMSOSTEK YG SDH DIPOSTING */
		$this->AccJurnalUpahModel->delete_by_ref_id_and_modul($periodeGaji['id'], 'ALK_GAJI_BPJS_KES');
		$this->AccJurnalUpahModel->delete_by_ref_id_and_modul($periodeGaji['id'], 'ALK_GAJI_BPJS_KES_ESTATE');

		$pendapatan = $this->db->query("select * from payroll_tipe_gaji where jenis=1 or jenis=2 or jenis=3 order by urut")->result_array();
		$potongan = $this->db->query("select * from payroll_tipe_gaji where jenis=0 order by urut")->result_array();


		$sql = "
		SELECT a.*,c.nip,c.nama,d.nama as jabatan,e.nama as departemen ,f.nama as divisi,c.sub_bagian_id
		FROM payroll_gaji_tr_hd a 
		left join payroll_karyawan_gaji b on a.karyawan_id=b.karyawan_id
        inner join karyawan c on a.karyawan_id=c.id 
		left join payroll_jabatan d on c.jabatan_id=d.id
        left join payroll_department e on c.departemen_id=e.id
		left join gbm_organisasi f on c.sub_bagian_id=f.id
        where a.periode_gaji_id=" . $id . "";
		$sql = $sql . " order by nama;";

		$dataGaji = $this->db->query($sql)->result_array();

		/* Cek biaya per karyawan yg sudah dijurnal di jurnalUpah*/
		$query_cek_jurnal = "SELECT SUM(debet)as nilai,karyawan_id FROM acc_jurnal_upah_dt a 
		inner join acc_jurnal_upah_ht b on a.jurnal_id=b.id 
		WHERE 1=1
		and tanggal between '" . $periodeGaji['tgl_awal'] . "' and '" . $periodeGaji['tgl_akhir'] . "'
		and b.lokasi_id= " . $periodeGaji['lokasi_id'] . "
		and modul not in('ALK_GAJI','ALK_GAJI_ESTATE','ALK_GAJI_BPJS','ALK_GAJI_DIFF','ALK_GAJI_BPJS_KES','ALK_GAJI_BPJS_ESTATE','ALK_GAJI_BPJS_ESTATE_KES','ALK_GAJI_JAMSOSTEK_ESTATE','ALK_GAJI_BPJS_KES_ESTATE','ALK_GAJI_CATU_BERAS_ESTATE')
		and karyawan_id IS NOT null
		group by karyawan_id";
		$res_gaji_jurnal = $this->db->query($query_cek_jurnal)->result_array();
		$gaji_jurnal = array();
		foreach ($res_gaji_jurnal as $key => $gaji) {
			$gaji_jurnal[$gaji['karyawan_id']] = $gaji['nilai'];
		}

		$no = 0;
		$gaji_karyawan = array();
		$totalPendapatan = 0;
		$totalPotongan = 0;
		$totalgajiDiterima = 0;
		// Data HEADER
		$tanggal = $periodeGaji['tgl_akhir'];
		$this->load->library('Autonumber');

		$jum_proses = 0;
		foreach ($dataGaji as $key => $arrGaji) {
			$no++;
			$jumPendapatan = 0;
			$jumPotongan = 0;
			$gajiDiterima = 0;
			// looping pendapatan
			foreach ($pendapatan as $key => $value) {
				$dataPendapatan = $this->db->query("SELECT a.*,b.jenis,b.nama,b.urut FROM payroll_gaji_tr_dt a inner join payroll_tipe_gaji b
                    on a.tipe_gaji=b.id
                    where a.id_hd=" . $arrGaji['id'] . " and a.tipe_gaji=" . $value['id'] . ";")->row_array();
				if (empty($dataPendapatan)) {
				} else {
					$jumPendapatan = $jumPendapatan + $dataPendapatan['nilai'];
				}
			}

			$jumPendapatan = $jumPendapatan + $arrGaji['gapok'] +  $arrGaji['lembur'] + $arrGaji['premi'];
			// looping potongan
			foreach ($potongan as $key => $value) {
				$dataPotongan = $this->db->query("SELECT a.*,b.jenis,b.nama,b.urut FROM payroll_gaji_tr_dt a inner join payroll_tipe_gaji b
                    on a.tipe_gaji=b.id
                    where a.id_hd=" . $arrGaji['id'] . " and a.tipe_gaji=" . $value['id'] . ";")->row_array();
				if (empty($dataPotongan)) {
					$total[$value['id']] = 0;
				} else {
					$jumPotongan = $jumPotongan + $dataPotongan['nilai'];
				}
			}

			$jumPotongan = $jumPotongan + $arrGaji['hk_potongan_gaji'] + $arrGaji['denda'] + $arrGaji['jht'] +  $arrGaji['jp'] + $arrGaji['jkn']; //+ $dataHeader['pph'];
			$gajiDiterima = $jumPendapatan - $jumPotongan; // + $arrGaji['nilai_rp_catu'];

			$nilai_gaji_jurnal = 0;
			if ($gaji_jurnal[$arrGaji['karyawan_id']]) {
				$nilai_gaji_jurnal = $gaji_jurnal[$arrGaji['karyawan_id']];
			}

			$selisih_gaji_jurnal =	$gajiDiterima - $nilai_gaji_jurnal;
			if ($gajiDiterima <= 0) {
				continue;
			}

			/* start JURNAL JAMSOSTEK */
			$jum_proses++;
			//$nilai_bpjs = $arrGaji['jkk_perusahaan'] + $arrGaji['jkm_perusahaan'];
			$nilai_bpjs = $arrGaji['jkn_perusahaan'];
			if ($nilai_bpjs <= 0) {
				continue;
			}
			$no_jurnal = $this->autonumber->jurnal_upah_auto($periodeGaji['lokasi_id'], $tanggal, 'ALK_GAJI_BPJS_KES_ESTATE');
			$dataH = array(
				'no_jurnal' => $no_jurnal,
				'lokasi_id' => $periodeGaji['lokasi_id'],
				'tanggal' => $tanggal,
				'no_ref' => 'ALK_GAJI_BPJS_KES_ESTATE_' . $periodeGaji['nama'],
				'ref_id' => $periodeGaji['id'],
				'tipe_jurnal' => 'AUTO',
				'modul' => 'ALK_GAJI_BPJS_KES_ESTATE',
				'keterangan' => 'ALK_GAJI_BPJS_KES_ESTATE Karyawan: ' . $arrGaji['nama'] . ",",
				'is_posting' => 1,
			);

			$id_header_bpjs = $this->AccJurnalUpahModel->create_header($dataH);

			$query_cek_jurnal_blok = "SELECT  sum(debet)as nilai, blok_stasiun_id,kegiatan_id ,acc_akun_id,umur_tanam_blok,kendaraan_mesin_id,tipe FROM acc_jurnal_upah_dt  a 
								inner join acc_jurnal_upah_ht b on a.jurnal_id=b.id  WHERE karyawan_id=" . $arrGaji['karyawan_id'] . "
										and tanggal between '" . $periodeGaji['tgl_awal'] . "' and '" . $periodeGaji['tgl_akhir'] . "'
										and b.lokasi_id =" . $periodeGaji['lokasi_id'] . "
										and modul not in('ALK_GAJI','ALK_GAJI_ESTATE','ALK_GAJI_BPJS','ALK_GAJI_DIFF','ALK_GAJI_BPJS_KES','ALK_GAJI_BPJS_ESTATE','ALK_GAJI_BPJS_ESTATE_KES','ALK_GAJI_JAMSOSTEK_ESTATE','ALK_GAJI_BPJS_KES_ESTATE','ALK_GAJI_CATU_BERAS_ESTATE')
										group by blok_stasiun_id,kegiatan_id ,acc_akun_id,umur_tanam_blok,kendaraan_mesin_id,tipe";
			$res_blok = $this->db->query($query_cek_jurnal_blok)->result_array();
			$jum_blok = count($res_blok);
			if ($jum_blok == 0) {

				continue;
			}

			foreach ($res_blok as $key => $blok) {
				$nilai_per_item = $blok['nilai'];
				$nilai_proporsi_bpjs = $nilai_bpjs * ($nilai_per_item / $nilai_gaji_jurnal);
				$dataDebetBpjs = array(
					'lokasi_id' => $periodeGaji['lokasi_id'],
					'jurnal_id' => $id_header_bpjs,
					'acc_akun_id' => $blok['acc_akun_id'],
					'debet' => $nilai_proporsi_bpjs,
					'kredit' => 0,
					'ket' => 'ALK_GAJI_BPJS_KES_ESTATE Afdeling: ,' . $arrGaji['divisi'] . ' Karyawan: ' . $arrGaji['nama'] . "",
					'no_referensi' => 'ALK_GAJI_BPJS_KES_ESTATE_' . $periodeGaji['nama'],
					'referensi_id' => NULL,
					'blok_stasiun_id' => $blok['blok_stasiun_id'],
					'kegiatan_id' => $blok['kegiatan_id'],
					'kendaraan_mesin_id' =>  $blok['kendaraan_mesin_id'],
					'karyawan_id' => $arrGaji['karyawan_id'],
					'umur_tanam_blok' => $blok['umur_tanam_blok'],
					'divisi_id' => $arrGaji['sub_bagian_id'],
					'tipe' => 'bpjs_kes'
				);
				$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header_bpjs, $dataDebetBpjs);
			}
			$dataKreditBpjs = array(
				'lokasi_id' => $periodeGaji['lokasi_id'],
				'jurnal_id' => $id_header_bpjs,
				'acc_akun_id' => $akun_bpjs,
				'debet' => 0,
				'kredit' => $nilai_bpjs,
				'ket' => 'ALK_GAJI_BPJS_KES_ESTATE Afdeling: ,' . $arrGaji['divisi'] . ' Karyawan: ' . $arrGaji['nama'] . ",",
				'no_referensi' => 'ALK_GAJI_BPJS_KES_ESTATE_' . $periodeGaji['nama'],
				'referensi_id' => NULL,
				'blok_stasiun_id' => NULL,
				'kegiatan_id' => NULL,
				'kendaraan_mesin_id' => NULL,
				'divisi_id' => null,
				'karyawan_id' => NULL,
				'tipe' => 'bpjs_kes'
			);
			$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header_bpjs, $dataKreditBpjs);
			// /* END JURNAL JAMSOSTEK */


		}
		$this->load->library('Autonumber');
		/* Get rekap sudah dijurnal di Jurnal Upah */
		$arr_tipe = array('bpjs_kes' => 'BPJS KESEHATAN');
		foreach ($arr_tipe as $t => $tipe) {
			$sql_jurnal = "	SELECT b.lokasi_id,b.acc_akun_id,b.kegiatan_id,b.blok_stasiun_id,b.kendaraan_mesin_id,
			b.umur_tanam_blok,sum(debet-kredit)AS nilai,sum(hk)AS hk,c.nama,b.tipe
			FROM acc_jurnal_upah_ht a inner join acc_jurnal_upah_dt b 
			on a.id=b.jurnal_id INNER JOIN acc_akun c ON b.acc_akun_id=c.id
			WHERE 1=1 
			AND a.tanggal between '" . $periodeGaji['tgl_awal'] . "' and '" . $periodeGaji['tgl_akhir'] . "'
			AND b.tipe='" . $t . "' AND b.lokasi_id='" . $periodeGaji['lokasi_id'] . "'
			GROUP BY b.lokasi_id,b.acc_akun_id,b.kegiatan_id,b.blok_stasiun_id,
			b.kendaraan_mesin_id,b.umur_tanam_blok,c.nama,b.tipe ;	";

			$res_jurnal = $this->db->query($sql_jurnal)->result_array();

			if ($res_jurnal) {
				$no_jurnal = $this->autonumber->jurnal_auto($periodeGaji['lokasi_id'], $tanggal, 'ALK_GAJI_BPJS_KES_ESTATE');
				$dataH = array(
					'no_jurnal' => $no_jurnal,
					'lokasi_id' => $periodeGaji['lokasi_id'],
					'tanggal' => $tanggal,
					'no_ref' => 'ALK_GAJI_BPJS_KES_ESTATE_' . strtoupper($t) . " " . $periodeGaji['nama'],
					'ref_id' => $periodeGaji['id'],
					'tipe_jurnal' => 'AUTO',
					'modul' => 'ALK_GAJI_BPJS_KES_ESTATE',
					'keterangan' =>  'ALK_GAJI_BPJS_KES_ESTATE_' . strtoupper($t) . " " . $periodeGaji['nama'],
					'is_posting' => 1,
				);
				$id_header = $this->AccJurnalModel->create_header($dataH);

				foreach ($res_jurnal as $key => $jurnal) {
					$jum_proses++;
					$str_hk = "";
					if ($jurnal['hk']) {

						if ($jurnal['tipe'] == 'upah') {
							$str_hk = $jurnal['hk'] > 0 ? $jurnal['hk'] . "HK" : "";
						}
					}
					if (($jurnal['nilai']) != 0) {
						$dataJurnalDtl = array(
							'lokasi_id' => $periodeGaji['lokasi_id'],
							'jurnal_id' => $id_header,
							'acc_akun_id' => $jurnal['acc_akun_id'], //akun biaya ,
							'debet' => ($jurnal['nilai'] > 0) ? $jurnal['nilai'] : 0,
							'kredit' => ($jurnal['nilai'] < 0) ? ($jurnal['nilai'] * -1) : 0,
							'ket' => 'ALK_GAJI_BPJS_KES_ESTATE_' . strtoupper($t) . " " . $str_hk,
							'no_referensi' => 'ALK_GAJI_BPJS_KES_ESTATE_' . strtoupper($t) . " " . $periodeGaji['nama'],
							'referensi_id' => NULL,
							'blok_stasiun_id' => $jurnal['blok_stasiun_id'],
							'kegiatan_id' => $jurnal['kegiatan_id'], //kegiatan ,
							'kendaraan_mesin_id' => $jurnal['kendaraan_mesin_id'],
							'karyawan_id' => 0, //karyawan,
							'umur_tanam_blok' => $jurnal['umur_tanam_blok'],
							'divisi_id' => null,

						);
						$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataJurnalDtl);
					}
				}
			}
		}
		$this->db->where('id', $id);
		$this->db->update('payroll_periode_gaji', array('is_posting_bpjs_kesehatan'    => '1', "tgl_posting_bpjs_kesehatan" => date('Y-m-d H:i:s')));

		$hasil['jum'] = $jum_proses; // count($dataGaji);
		// var_dump($hasil);exit;
		return $hasil;
	}
	function start_posting_payroll_mill($id)
	{

		// ===  MAPPING ID STASIUN VS AKUN //
		// $mapping_stasiun_akun = array('431'=>'6310231 FRUIT RECEPTION & STORAGE','432'=> '6320131 STERILLIZING STATION', '433'=>'6320231 THRESHING STATION','434'=> '6320231 PRESSING STATION','435'=> '6320431 CLARIFICATION STATION','436'=> '6340131 DEPERICARPING STATION','437'=> '6320531 KERNEL RECOVERY STATION', '438'=>'6330131 BOILER STATION','439'=> '6330231 POWER STATION','440'=> '6330331 BOILER WATER TREATMENT PLANT','441'=> '6330331 RAW WATER TREATMENT PLANT','442'=> '6330431 PALM OIL MILL EFFLUENT TREATMENT PLANT','735'=>'KTR_MILL KANTOR MILL','748'=> '6330531 LAB - PKS','747'=>'6330631 WORKSHOP-MILL','739'=>'4110201' Traksi MILL);
		$mapping_stasiun_akun = array('431' => '3410', '432' => '3420', '433' => '3429', '434' => '3438', '435' => '3447', '436' => '3521', '437' => '3456', '438' => '3466', '439' => '3475', '440' => '3484', '441' => '3484', '442' => '3493', '735' => '3546', '748' => '3502', '747' => '3261', '739' => '3268');
		$akun_upah_transit_kendaraan = 3268; //4110201 - Gaji Supir Kendaraan/Alat Berat/Genset/Mesin Air
		$periodeGaji = $this->HrmsPeriodeGajiModel->retrieve($id);
		if (empty($periodeGaji)) {
			$this->set_response(array("status" => "NOT OK", "data" => 'null'), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$res_akun = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='ALOKASI_GAJI_PKS'")->row_array();
		if (empty($res_akun)) {
			$this->set_response(array("status" => "NOT OK", "data" => "ALOKASI_GAJI_PKS Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		// $akun_debet = $res_akun['acc_akun_id_debet'];

		$akun_kredit = $res_akun['acc_akun_id_kredit'];


		$pendapatan = $this->db->query("select * from payroll_tipe_gaji where jenis=1 or jenis=2 or jenis=3 order by urut")->result_array();
		$potongan = $this->db->query("select * from payroll_tipe_gaji where jenis=0 order by urut")->result_array();


		$sql = "
		SELECT a.*,c.nip,c.nama,d.nama as jabatan,e.nama as departemen ,f.nama as divisi,c.sub_bagian_id
		FROM payroll_gaji_tr_hd a 
		inner join payroll_karyawan_gaji b on a.karyawan_id=b.karyawan_id
        inner join karyawan c on a.karyawan_id=c.id 
		inner join payroll_jabatan d on c.jabatan_id=d.id
        inner join payroll_department e on c.departemen_id=e.id
		inner join gbm_organisasi f on c.sub_bagian_id=f.id
        where a.periode_gaji_id=" . $id . "";
		$sql = $sql . " order by nama;";
		$dataGaji = $this->db->query($sql)->result_array();

		$no = 0;
		$gaji_karyawan = array();
		$totalPendapatan = 0;
		$totalPotongan = 0;
		$totalgajiDiterima = 0;
		// Data HEADER
		$tanggal = $periodeGaji['tgl_akhir'];
		$this->load->library('Autonumber');
		$this->AccJurnalModel->delete_by_ref_id_and_modul($periodeGaji['id'], 'ALK_GAJI_PKS');
		$no_jurnal = $this->autonumber->jurnal_auto($periodeGaji['lokasi_id'], $tanggal, 'ALK_GAJI_PKS');

		$dataH = array(
			'no_jurnal' => $no_jurnal,
			'lokasi_id' => $periodeGaji['lokasi_id'],
			'tanggal' => $tanggal,
			'no_ref' => 'ALK_GAJI_PKS_' . $periodeGaji['nama'],
			'ref_id' => $periodeGaji['id'],
			'tipe_jurnal' => 'AUTO',
			'modul' => 'ALK_GAJI_PKS',
			'keterangan' => 'ALK_GAJI_PKS',
			'is_posting' => 1,
		);
		$id_header = $this->AccJurnalModel->create_header($dataH);
		$cek_akun_debet = [];
		foreach ($dataGaji as $key => $arrGaji) {
			$akun_debet = $mapping_stasiun_akun[$arrGaji['sub_bagian_id']];



			if (!$akun_debet) { // jika tidak ada maka masukan ke gaji non staff
				$akun_debet = '3546'; // 3546=id utk akun 7110201 GAJI NON STAFF
			}
			$cek_akun_debet[] = $akun_debet;
			$no++;
			$jumPendapatan = 0;
			$jumPotongan = 0;
			$gajiDiterima = 0;
			// looping pendapatan
			foreach ($pendapatan as $key => $value) {
				$dataPendapatan = $this->db->query("SELECT a.*,b.jenis,b.nama,b.urut FROM payroll_gaji_tr_dt a inner join payroll_tipe_gaji b
                    on a.tipe_gaji=b.id
                    where a.id_hd=" . $arrGaji['id'] . " and a.tipe_gaji=" . $value['id'] . ";")->row_array();
				if (empty($dataPendapatan)) {
				} else {
					$jumPendapatan = $jumPendapatan + $dataPendapatan['nilai'];
				}
			}

			$jumPendapatan = $jumPendapatan + $arrGaji['gapok'] +  $arrGaji['lembur'] + $arrGaji['premi'];
			// looping potongan
			foreach ($potongan as $key => $value) {
				$dataPotongan = $this->db->query("SELECT a.*,b.jenis,b.nama,b.urut FROM payroll_gaji_tr_dt a inner join payroll_tipe_gaji b
                    on a.tipe_gaji=b.id
                    where a.id_hd=" . $arrGaji['id'] . " and a.tipe_gaji=" . $value['id'] . ";")->row_array();
				if (empty($dataPotongan)) {
					$total[$value['id']] = 0;
				} else {
					$jumPotongan = $jumPotongan + $dataPotongan['nilai'];
				}
			}

			$jumPotongan = $jumPotongan + $arrGaji['hk_potongan_gaji'] + $arrGaji['jht'] +  $arrGaji['jp'] + $arrGaji['jkn']; //+ $dataHeader['pph'];
			// $gajiDiterima = $jumPendapatan - $jumPotongan + $arrGaji['nilai_rp_catu'];
			$gajiDiterima = $jumPendapatan - $jumPotongan;
			$totalgajiDiterima = $totalgajiDiterima + $gajiDiterima;
			if ($arrGaji['sub_bagian_id'] == 739) { // JIKA KARYAWAN TRAKSI
				$sql_traksi = "SELECT COUNT(*)AS jum_transaksi, b.karyawan_id,a.kendaraan_id,c.sub_bagian_id,c.nama,d.nama,d.id FROM trk_kegiatan_kendaraan_ht a inner join trk_kegiatan_kendaraan_dt b
				ON a.id=b.trk_kegiatan_kendaraan_id
				INNER JOIN karyawan c ON b.karyawan_id=c.id
				LEFT JOIN gbm_organisasi d ON c.sub_bagian_id=d.id
				WHERE a.lokasi_id=" . $periodeGaji['lokasi_id'] . " AND d.id=739
				and b.karyawan_id=" . $arrGaji['karyawan_id'] . " 
				AND a.tanggal BETWEEN '" . $periodeGaji['tgl_awal'] . "' AND  '" . $periodeGaji['tgl_akhir'] . "'
				 GROUP BY   b.karyawan_id,a.kendaraan_id ,c.sub_bagian_id,c.nama,d.nama,d.id";
				$resTraksi = $this->db->query($sql_traksi)->result_array();
				$countData = count($resTraksi);
				if ($countData == 0) {
					$dataDebet = array(
						'lokasi_id' => $periodeGaji['lokasi_id'],
						'jurnal_id' => $id_header,
						'acc_akun_id' => $akun_debet, //akun,
						'debet' => $gajiDiterima,
						'kredit' => 0,
						'ket' => 'ALK_GAJI_PKS: ' . $arrGaji['divisi'] . ' , Karyawan: ' . $arrGaji['nama'] . ",",
						'no_referensi' => 'ALK_GAJI_PKS_' . $periodeGaji['nama'],
						'referensi_id' => NULL,
						'blok_stasiun_id' => null,
						'kegiatan_id' => null, //kegiatan ,
						'kendaraan_mesin_id' => NULL,
						'karyawan_id' => $arrGaji['karyawan_id'], //karyawan,
						'umur_tanam_blok' => null,
						'divisi_id' => $arrGaji['sub_bagian_id']
					);
					$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataDebet);
				} else {
					$jumTrans = 0;
					// Cari Total Jum Transaksi utk proporsi thd Kendaraan
					foreach ($resTraksi as $key => $trk) {
						$jumTrans =	$jumTrans + $trk['jum_transaksi'];
						# code...
					}
					foreach ($resTraksi as $key => $trk) {
						$nilai_proporsi = $trk['jum_transaksi'] / $jumTrans * $gajiDiterima;
						$dataDebet = array(
							'lokasi_id' => $periodeGaji['lokasi_id'],
							'jurnal_id' => $id_header,
							'acc_akun_id' => $akun_upah_transit_kendaraan, //akun,
							'debet' => $nilai_proporsi,
							'kredit' => 0,
							'ket' => 'ALK_GAJI_PKS: ' . $arrGaji['divisi'] . ' , Karyawan: ' . $arrGaji['nama'] . ",",
							'no_referensi' => 'ALK_GAJI_PKS_' . $periodeGaji['nama'],
							'referensi_id' => NULL,
							'blok_stasiun_id' => null,
							'kegiatan_id' => null, //kegiatan ,
							'kendaraan_mesin_id' => $trk['kendaraan_id'],
							'karyawan_id' => $arrGaji['karyawan_id'], //karyawan,
							'umur_tanam_blok' => null,
							'divisi_id' => $arrGaji['sub_bagian_id']
						);
						$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataDebet);
					}
				}
			} else {
				$dataDebet = array(
					'lokasi_id' => $periodeGaji['lokasi_id'],
					'jurnal_id' => $id_header,
					'acc_akun_id' => $akun_debet, //akun,
					'debet' => $gajiDiterima,
					'kredit' => 0,
					'ket' => 'ALK_GAJI_PKS: ' . $arrGaji['divisi'] . ' , Karyawan: ' . $arrGaji['nama'] . ",",
					'no_referensi' => 'ALK_GAJI_PKS_' . $periodeGaji['nama'],
					'referensi_id' => NULL,
					'blok_stasiun_id' => null,
					'kegiatan_id' => null, //kegiatan ,
					'kendaraan_mesin_id' => NULL,
					'karyawan_id' => $arrGaji['karyawan_id'], //karyawan,
					'umur_tanam_blok' => null,
					'divisi_id' => $arrGaji['sub_bagian_id']
				);
				$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataDebet);
			}
		}
		$dataKredit = array(
			'lokasi_id' => $periodeGaji['lokasi_id'],
			'jurnal_id' => $id_header,
			'acc_akun_id' => $akun_kredit, //$value['acc_akun_id'],
			'debet' => 0,
			'kredit' => $totalgajiDiterima, // Akun Lawan Biaya
			'ket' => 'ALK_GAJI_PKS',
			'no_referensi' => 'ALK_GAJI_PKS_' . $periodeGaji['nama'],
			'referensi_id' => NULL,
			'blok_stasiun_id' => NULL,
			'kegiatan_id' => NULL, // $value['kegiatan_id'],
			'kendaraan_mesin_id' => NULL,
			'divisi_id' => null
		);
		$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataKredit);
		$this->db->where('id', $id);
		$this->db->update('payroll_periode_gaji', array('is_posting'    => '1', "tgl_posting" => date('Y-m-d H:i:s')));

		$hasil['jum'] =  count($dataGaji);
		//  var_dump($cek_akun_debet);exit;
		return $hasil;
	}

	public function proses_update_upah_bkm_post() /// UTK MENGUPATE UPAH KE TRANSAKSI HARIAN:BKM 
	{
		$d1 = $this->post('d1');
		$d2 = $this->post('d2');
		// $this->set_response(array("status" => "OK", "data" => $d2), REST_Controller::HTTP_OK);
		// return;exit();
		if (!$d1) {
			$d1 = '2023-01-01';
		}
		if (!$d2) {
			$d2 = '2023-01-31';
		}
		$arr_count = array();
		$gaji_karyawan = array();
		$sql = "	SELECT * FROM payroll_karyawan_gaji ";
		$res = $this->db->query($sql)->result_array();
		foreach ($res as $key => $value) {
			$gaji_karyawan[$value['karyawan_id']] = $value['gapok'] / 25;
		}

		//BKM PANEN
		$sql = "	SELECT *,b.id as id_dt,a.id as id_hd FROM est_bkm_panen_ht a INNER JOIN est_bkm_panen_dt b ON a.id=b.bkm_panen_id
			WHERE tanggal BETWEEN '" . $d1 . "' AND '" . $d2 . "' and a.is_posting=0 order by a.id";
		$res = $this->db->query($sql)->result_array();
		$arr_count['panen'] = count($res);
		$id_hd = '';
		foreach ($res as $key => $value) {
			if ($id_hd != $value['id_hd']) {
				// update mandor,kerani
				$rp_mandor = ($value['jumlah_hk_mandor']) * ($gaji_karyawan[$value['mandor_id']]);
				$rp_kerani = ($value['jumlah_hk_kerani']) * ($gaji_karyawan[$value['kerani_id']]);
				$this->db->where('id', $value['id_hd']);
				$this->db->update('est_bkm_panen_ht', array('rp_hk_mandor'    => $rp_mandor, 'rp_hk_kerani'    => $rp_kerani));
			}
			$id_hd = $value['id_hd'];
			$rp_hk = ($value['jumlah_hk']) * ($gaji_karyawan[$value['karyawan_id']]);
			$this->db->where('id', $value['id_dt']);
			$this->db->update('est_bkm_panen_dt', array('rp_hk'    => $rp_hk));
		}

		//BKM PEMELIHARAAN
		$sql = "	SELECT *,b.id as id_dt,a.id as id_hd FROM est_bkm_pemeliharaan_ht a INNER JOIN est_bkm_pemeliharaan_dt b ON a.id=b.bkm_pemeliharaan_id
			WHERE tanggal BETWEEN '" . $d1 . "' AND '" . $d2 . "' and a.is_posting=0 order by a.id";
		$res = $this->db->query($sql)->result_array();
		$arr_count['pemeliharaan'] = count($res);
		$id_hd = '';
		foreach ($res as $key => $value) {
			if ($id_hd != $value['id_hd']) {
				// update mandor,kerani
				$rp_mandor = ($value['jumlah_hk_mandor']) * ($gaji_karyawan[$value['mandor_id']]);
				$rp_kerani = ($value['jumlah_hk_kerani']) * ($gaji_karyawan[$value['kerani_id']]);
				$this->db->where('id', $value['id_hd']);
				$this->db->update('est_bkm_pemeliharaan_ht', array('rp_hk_mandor'    => $rp_mandor, 'rp_hk_kerani'    => $rp_kerani));
			}
			$id_hd = $value['id_hd'];
			$rp_hk = ($value['jumlah_hk']) * ($gaji_karyawan[$value['karyawan_id']]);
			$this->db->where('id', $value['id_dt']);
			$this->db->update('est_bkm_pemeliharaan_dt', array('rupiah_hk'    => $rp_hk));
		}

		//BKM UMUM
		$sql = "	SELECT *,b.id as id_dt,a.id as id_hd FROM est_bkm_umum_ht a INNER JOIN est_bkm_umum_dt b ON a.id=b.bkm_umum_id
		WHERE tanggal BETWEEN '" . $d1 . "' AND '" . $d2 . "' and a.is_posting=0  order by a.id";
		$res = $this->db->query($sql)->result_array();
		$arr_count['umum'] = count($res);
		$id_hd = '';
		foreach ($res as $key => $value) {

			$rp_hk = ($value['jumlah_hk']) * ($gaji_karyawan[$value['karyawan_id']]);
			$this->db->where('id', $value['id_dt']);
			$this->db->update('est_bkm_umum_dt', array('rupiah_hk'    => $rp_hk));
		}

		//BKM TRAKSI
		$sql = "	SELECT *,b.id as id_dt,a.id as id_hd FROM trk_kegiatan_kendaraan_ht a INNER JOIN trk_kegiatan_kendaraan_dt b ON a.id=b.trk_kegiatan_kendaraan_id
		WHERE tanggal BETWEEN '" . $d1 . "' AND '" . $d2 . "' and a.is_posting=0 order by a.id";
		$res = $this->db->query($sql)->result_array();
		$arr_count['traksi'] = count($res);
		$id_hd = '';
		foreach ($res as $key => $value) {
			$rp_hk = ($value['jumlah_hk']) * ($gaji_karyawan[$value['karyawan_id']]);
			$this->db->where('id', $value['id_dt']);
			$this->db->update('trk_kegiatan_kendaraan_dt', array('rupiah_hk'    => $rp_hk));
		}

		//BKM WORKSHOP
		$sql = "	SELECT *,b.id as id_dt,a.id as id_hd FROM wrk_kegiatan_ht a INNER JOIN wrk_kegiatan_dt b ON a.id=b.wrk_kegiatan_id
		WHERE tanggal BETWEEN '" . $d1 . "' AND '" . $d2 . "' and a.is_posting=0 order by a.id";
		$res = $this->db->query($sql)->result_array();
		$arr_count['workshop'] = count($res);
		$id_hd = '';
		foreach ($res as $key => $value) {

			$rp_hk = ($value['jumlah_hk']) * ($gaji_karyawan[$value['karyawan_id']]);
			$this->db->where('id', $value['id_dt']);
			$this->db->update('wrk_kegiatan_dt', array('rupiah_hk'    => $rp_hk));
		}
		$this->set_response(array("status" => "OK", "data" => $arr_count), REST_Controller::HTTP_OK);
		return;
	}
	function laporan_gaji_post($id, $jenis = '')
	{
		$html = '';
		$jenis = $this->post('jenis');
		$format_laporan = $this->post('format_laporan');
		$divisi_id = $this->post('divisi_id');
		$status_id = $this->post('status_id');
		// $jenis = $this->get('jenis');
		// $format_laporan = $this->get('format_laporan');

		// var_dump($this->post()); die;

		if ($jenis == '1') {
			$html = $this->print_slip($id, $divisi_id, $status_id);
		} else if ($jenis == '0') {
			$html = $this->print_rekap($id, $divisi_id, $status_id);
		} else if ($jenis == '2') {
			$html = $this->print_catu_beras($id, $divisi_id, $status_id);
		} else if ($jenis == '4') {
			$html = $this->print_rekap_pph_by_tipe_karyawan($id, $divisi_id, $status_id);
		} else  if ($jenis == '3') {
			$html = $this->print_rekap_pph($id, $divisi_id, $status_id);
		} else  if ($jenis == '5') {
			$html = $this->print_rekap_pph_by_unit_kerja($id, $divisi_id, $status_id);
		}
		if (is_null($format_laporan)) {
			$format_laporan = 'view';
		}

		if ($format_laporan == 'xls') {
			// $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			// $spreadsheet = $reader->loadFromString($html);
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
			if ($jenis == '1' || $jenis == '2') {
				$this->pdfgenerator->generate($html, $filename, true, 'A4', 'portrait');
			} else {
				$this->pdfgenerator->generate($html, $filename, true, 'A3', 'landscape');
			}
		}
	}
	function print_rekap($id, $divisi_id, $status_id)
	{


		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');



		// 		$html = $html . '<div class="row">
		// <div class="span12">
		//     <br>
		//     <div class="kop-print">
		// 	<img src=data:image/png;base64,' . base64_encode(file_get_contents(get_logo_config())) . ' alt="image" >
		//         <div class="kop-nama">' . get_pengaturan('nama-company', 'value') . '</div>
		//         <div class="kop-info">Alamat : ' . get_pengaturan('alamat', 'value') . ', Telepon :' . get_pengaturan('telp', 'value') . '</div>
		//     </div>
		//     <hr class="kop-print-hr">
		// </div>
		// </div>';
		$periodeGaji = $this->HrmsPeriodeGajiModel->retrieve($id);
		if (empty($periodeGaji)) {
			$this->set_response(array("status" => "NOT OK", "data" => 'null'), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$countPendapatan = 0;
		$pendapatan = $this->db->query("select * from payroll_tipe_gaji where jenis=1 or jenis=2 or jenis=3 order by urut")->result_array();
		$potongan = $this->db->query("select * from payroll_tipe_gaji where jenis=0 order by urut")->result_array();
		$countPendapatan = count($pendapatan) + 3;
		$countPotongan = count($potongan) + 5;

		// $sql = "SELECT a.*,c.nip,c.nama,d.nama as jabatan,e.nama as departemen ,f.nama as divisi, c.sub_bagian_id,
		// c.tipe_karyawan_id , g.nama as tipe_karyawan
		// FROM payroll_gaji_tr_hd a 
		// left join payroll_karyawan_gaji b on a.karyawan_id=b.karyawan_id
        // inner join karyawan c on a.karyawan_id=c.id 
		// left join payroll_jabatan d on c.jabatan_id=d.id
        // left join payroll_department e on c.departemen_id=e.id
		// left join gbm_organisasi f on c.sub_bagian_id=f.id
		// left join payroll_tipe_karyawan g on g.id=c.tipe_karyawan_id 
        // where a.periode_gaji_id=" . $id . "";

		/*UPDATE: DIVISI/AFDELING NGMBIL DARI TABEL PAYROLL GAJI BUKAN DR TABLE MASTER KARYAWAN */
		$sql = "SELECT a.*,c.nip,c.nama,d.nama as jabatan,e.nama as departemen ,f.nama as divisi,a.divisi_id as sub_bagian_id,
		c.tipe_karyawan_id , g.nama as tipe_karyawan
		FROM payroll_gaji_tr_hd a 
		left join payroll_karyawan_gaji b on a.karyawan_id=b.karyawan_id
        inner join karyawan c on a.karyawan_id=c.id 
		left join payroll_jabatan d on c.jabatan_id=d.id
        left join payroll_department e on c.departemen_id=e.id
		left join gbm_organisasi f on a.divisi_id=f.id
		left join payroll_tipe_karyawan g on g.id=c.tipe_karyawan_id 
        where a.periode_gaji_id=" . $id . "";



		$nama_afd_stasiun = "Semua";
		$res_lokasi = $this->db->query("select * from gbm_organisasi where id=" . $periodeGaji['lokasi_id'] . "")->row_array();
		$nama_lokasi = $res_lokasi['nama'];
		if (($divisi_id)) {
			// $sql = $sql . " and c.sub_bagian_id=" . $divisi_id . "";
			$sql = $sql . " and a.divisi_id=" . $divisi_id . "";
			$res_afd_stasiun = $this->db->query("select * from  gbm_organisasi where id=" . $divisi_id . "")->row_array();
			$nama_afd_stasiun = $res_afd_stasiun['nama'];
		}
		if (($status_id)) {
			$sql = $sql . " and c.tipe_karyawan_id=" . $status_id . "";
		}
		$sql = $sql . " order by f.nama,c.nama;";
		$dataHd = $this->db->query($sql)->result_array();

		$html = get_header_report_v2();
		$html = $html . '
		<div class="row">
		<div class="span12">
			<br>
			<div class="kop-print">
			<div class="kop-nama">KLINIK ANNAJAH</div>
			<div class="kop-info"> Jl Sukarno Hatta No 12 No 12, Bandung Jawa Barat 14450</div>
			<div class="kop-info">Telp : (021) 6684055</div>
		</div>
			<hr style="width:1850px" class="kop-print-hr">
		</div>
		</div>
		<h3 class="title">LAPORAN REKAP PENGGAJIAN  ' . $periodeGaji['nama'] . '</h3>
  <table class="no_border" style="width:30%">
  		
			<tr>
					<td>LOKASI</td>
					<td>:</td>
					<td>' . $nama_lokasi . '</td>
			</tr>
			<tr>	
					<td>PERIODE</td>
					<td>:</td>
					<td>' . $periodeGaji['nama'] . '</td>
			</tr>
			<tr>	
					<td>AFDELING/STATION</td>
					<td>:</td>
					<td>' . $nama_afd_stasiun . '</td>
			</tr>

					
	</table>
	<br>
  ';
		$html = $html . "
         	 
        <table   border='1' width='100%' style='border-collapse: collapse;'>
        <thead>
		<tr>
            <th rowspan=2 >No</th>
            <th rowspan=2>Nama</th>
			<th rowspan=2>NIK</td>
            <th rowspan=2>Jabatan</th>
            <th rowspan=2>Divisi</th>
			<th rowspan=2>Status</th>
			<th rowspan=2>HK</th>
            <th colspan=" . $countPendapatan . "  style='text-align: center'>Pendapatan (Rp)</th>
			<th  rowspan=2 style='text-align: center'>Total Pendapatan (Rp) </th>
			<th  colspan=" . $countPotongan . "  style='text-align: center'>Potongan (Rp) </th>
			<th  rowspan=2 style='text-align: center'>Total Potongan  (Rp)</th>
            <th rowspan=2  style='text-align: center'>Gaji Bersih (Rp)</th>
			<th rowspan=2  style='text-align: center'>Catu Beras (Rp)</th>
			<th rowspan=2  style='text-align: center'>Diterima (Rp)</th>
        </tr>";

		$html = $html . "<tr>";

		$html = $html . "<th style='text-align: center'>" . 'Gaji Pokok' . "</th>";
		foreach ($pendapatan as $key => $value) {
			$html = $html . "<th style='text-align: center'>" . $value['nama'] . "</th>";
		}
		$html = $html . "<th style='text-align: center'>" . 'Premi' . "</th>";
		$html = $html . "<th style='text-align: center'>" . 'Lembur' . "</th>";
		$html = $html . "<th style='text-align: center'>" . 'Denda' . "</th>";
		foreach ($potongan as $key => $value) {
			$html = $html . "<th style='text-align: center'>" . $value['nama'] . "</th>";
		}
		$html = $html . "<th style='text-align: center'>" . 'Potongan HK' . "</th>";
		$html = $html . "<th style='text-align: center'>" . 'JHT' . "</th>";
		$html = $html . "<th style='text-align: center'>" . 'JP' . "</th>";
		$html = $html . "<th style='text-align: center'>" . 'JKN' . "</th>";
		// $html = $html . "<td style='text-align: center'>" . 'PPH21' . "</td>";
		$html = $html . "</tr> 
		</thead>
		";
		// print_r($html);exit;

		$no = 0;
		$total = array();
		$sub_total = array();
		$totalPendapatan = 0;
		$totalPotongan = 0;
		$sub_totalPendapatan = 0;
		$sub_totalPotongan = 0;
		$sub_denda = 0;
		$total_hk = 0;
		$sub_total_hk = 0;
		$total_gapok = 0;
		$sub_total_gapok = 0;
		$sub_total_rp_catu_beras = 0;
		$total_rp_catu_beras = 0;
		$total_denda = 0;
		$sub_total_diterima = 0;
		$total_diterima = 0;
		$countRow = count($dataHd);
		for ($i = 0; $i < $countRow; $i++) {
			$dataHeader = $dataHd[$i];
			$no++;
			$jumPendapatan = 0;
			$jumPotongan = 0;
			$total_hk = $total_hk +  $dataHeader['jumlah_hari_masuk'];
			$total_gapok = $total_gapok +  $dataHeader['gapok'];
			$sub_total_hk = $sub_total_hk +  $dataHeader['jumlah_hari_masuk'];
			$sub_total_gapok = $sub_total_gapok +  $dataHeader['gapok'];
			// looping pendapatan
			$html_pendapatan = '';
			foreach ($pendapatan as $key => $value) {
				$dataPendapatan = $this->db->query("SELECT a.*,b.jenis,b.nama,b.urut FROM payroll_gaji_tr_dt a inner join payroll_tipe_gaji b
                    on a.tipe_gaji=b.id
                    where a.id_hd=" . $dataHeader['id'] . " and a.tipe_gaji=" . $value['id'] . ";")->row_array();
				if (empty($dataPendapatan)) {
					$html_pendapatan =  $html_pendapatan . "<td  style='text-align: right'>" . 0 . "</td>";
					if (empty($total[$value['nama']])) {
						$total[$value['nama']] = 0;
					} else {
						//$total[$value['nama']] = $total[$value['nama']] ;
					}
					if (empty($sub_total[$value['nama']])) {

						$sub_total[$value['nama']] = 0;
					} else {

						//$sub_total[$value['nama']] = $sub_total[$value['nama']] ;
					}
				} else {
					$html_pendapatan = $html_pendapatan . "<td  style='text-align: right'>" . $this->format_number_report($dataPendapatan['nilai']) . "</td>";
					$jumPendapatan = $jumPendapatan + $dataPendapatan['nilai'];
					$totalPendapatan = $totalPendapatan + $dataPendapatan['nilai'];
					if (empty($total[$value['nama']])) {
						$total[$value['nama']] = $dataPendapatan['nilai'];
					} else {
						$total[$value['nama']] = $total[$value['nama']] + $dataPendapatan['nilai'];
					}
					if (empty($sub_total[$value['nama']])) {

						$sub_total[$value['nama']] = $dataPendapatan['nilai'];
					} else {

						$sub_total[$value['nama']] = $sub_total[$value['nama']] + $dataPendapatan['nilai'];
					}
				}
			}

			$jumPendapatan = $jumPendapatan + $dataHeader['gapok'] +  $dataHeader['lembur'] + $dataHeader['premi'];
			$totalPendapatan = $totalPendapatan + $dataHeader['gapok'] + $dataHeader['lembur'] + $dataHeader['premi'];
			$sub_totalPendapatan = $sub_totalPendapatan + $dataHeader['gapok'] + $dataHeader['lembur'] + $dataHeader['premi'];
			$sub_total_rp_catu_beras = $sub_total_rp_catu_beras + $dataHeader['nilai_rp_catu'];
			$total_rp_catu_beras = $total_rp_catu_beras + $dataHeader['nilai_rp_catu'];
			if (empty($total['premi'])) {
				$total['premi'] = $dataHeader['premi'];
			} else {
				$total['premi'] = $total['premi'] +  $dataHeader['premi'];
			}
			if (empty($total['lembur'])) {
				$total['lembur'] = $dataHeader['lembur'];
			} else {
				$total['lembur'] = $total['lembur'] +  $dataHeader['lembur'];
			}
			if (empty($total['jum_pendapatan'])) {

				$total['jum_pendapatan'] = $jumPendapatan;
			} else {

				$total['jum_pendapatan'] = $total['jum_pendapatan'] +  $jumPendapatan;
			}
			if (empty($total['denda'])) {
				$total['denda'] = $dataHeader['denda'];
			} else {
				$total['denda'] = $total['denda'] +  $dataHeader['denda'];
			}

			if (empty($sub_total['premi'])) {

				$sub_total['premi'] = $dataHeader['premi'];
			} else {

				$sub_total['premi'] = $sub_total['premi'] +  $dataHeader['premi'];
			}
			if (empty($sub_total['lembur'])) {

				$sub_total['lembur'] = $dataHeader['lembur'];
			} else {

				$sub_total['lembur'] = $sub_total['lembur'] +  $dataHeader['lembur'];
			}
			if (empty($sub_total['jum_pendapatan'])) {

				$sub_total['jum_pendapatan'] = $jumPendapatan;
			} else {

				$sub_total['jum_pendapatan'] = $sub_total['jum_pendapatan'] +  $jumPendapatan;
			}
			if (empty($sub_total['denda'])) {

				$sub_total['denda'] = $dataHeader['denda'];
			} else {

				$sub_total['denda'] = $sub_total['denda'] +  $dataHeader['denda'];
			}


			// looping potongan
			$html_potongan = '';
			foreach ($potongan as $key => $value) {
				$dataPotongan = $this->db->query("SELECT a.*,b.jenis,b.nama,b.urut FROM payroll_gaji_tr_dt a inner join payroll_tipe_gaji b
                    on a.tipe_gaji=b.id
                    where a.id_hd=" . $dataHeader['id'] . " and a.tipe_gaji=" . $value['id'] . ";")->row_array();
				if (empty($dataPotongan)) {
					$html_potongan = $html_potongan . "<td  style='text-align: right'>" . 0 . "</td>";
					$total[$value['nama']] = 0;
					$sub_total[$value['nama']] = 0;
				} else {
					$html_potongan = $html_potongan . "<td  style='text-align: right'>" . $this->format_number_report($dataPotongan['nilai']) . "</td>";
					$jumPotongan = $jumPotongan + $dataPotongan['nilai'];
					$totalPotongan = $totalPotongan + $dataPotongan['nilai'];
					if (empty($total[$value['nama']])) {
						$total[$value['nama']] = $dataPotongan['nilai'];
					} else {
						$total[$value['nama']] = $total[$value['nama']] + $dataPotongan['nilai'];
					}
					if (empty($sub_total[$value['nama']])) {

						$sub_total[$value['nama']] = $dataPotongan['nilai'];
					} else {

						$sub_total[$value['nama']] = $sub_total[$value['nama']] + $dataPotongan['nilai'];
					}
				}
			}

			$jumPotongan = $jumPotongan + $dataHeader['hk_potongan_gaji'] + $dataHeader['jht'] +  $dataHeader['jp'] + $dataHeader['jkn'] + $dataHeader['denda'];
			$totalPotongan = $totalPotongan + $dataHeader['hk_potongan_gaji'] + $dataHeader['jht'] +  $dataHeader['jp'] + $dataHeader['jkn'] + $dataHeader['denda'];
			$sub_totalPotongan = $sub_totalPotongan + $dataHeader['hk_potongan_gaji'] + $dataHeader['jht'] +  $dataHeader['jp'] + $dataHeader['jkn'] + $dataHeader['denda'];
			if (empty($sub_total['hk_potongan_gaji'])) {
				$sub_total['hk_potongan_gaji'] = $dataHeader['hk_potongan_gaji'];
			} else {
				$sub_total['hk_potongan_gaji'] = $sub_total['hk_potongan_gaji'] +  $dataHeader['hk_potongan_gaji'];
			}
			if (empty($sub_total['jht'])) {
				$sub_total['jht'] = $dataHeader['jht'];
			} else {
				$sub_total['jht'] = $sub_total['jht'] +  $dataHeader['jht'];
			}
			if (empty($sub_total['jp'])) {
				$sub_total['jp'] = $dataHeader['jp'];
			} else {
				$sub_total['jp'] = $sub_total['jp'] +  $dataHeader['jp'];
			}
			if (empty($sub_total['jkn'])) {
				$sub_total['jkn'] = $dataHeader['jkn'];
			} else {
				$sub_total['jkn'] = $sub_total['jkn'] +  $dataHeader['jkn'];
			}
			if (empty($sub_total['jum_potongan'])) {
				$sub_total['jum_potongan'] = $jumPotongan;
			} else {
				$sub_total['jum_potongan'] = $sub_total['jum_potongan'] +  $jumPotongan;
			}

			if (empty($total['hk_potongan_gaji'])) {
				$total['hk_potongan_gaji'] = $dataHeader['hk_potongan_gaji'];
			} else {
				$total['hk_potongan_gaji'] = $total['hk_potongan_gaji'] +  $dataHeader['hk_potongan_gaji'];
			}
			if (empty($total['jht'])) {
				$total['jht'] = $dataHeader['jht'];
			} else {
				$total['jht'] = $total['jht'] +  $dataHeader['jht'];
			}
			if (empty($total['jp'])) {
				$total['jp'] = $dataHeader['jp'];
			} else {
				$total['jp'] = $total['jp'] +  $dataHeader['jp'];
			}
			if (empty($total['jkn'])) {
				$total['jkn'] = $dataHeader['jkn'];
			} else {
				$total['jkn'] = $total['jkn'] +  $dataHeader['jkn'];
			}

			if (empty($total['jum_potongan'])) {
				$total['jum_potongan'] = $jumPotongan;
			} else {
				$total['jum_potongan'] = $total['jum_potongan'] + $jumPotongan;
			}
			// if (empty($total['pph'])) {
			// 	$total['pph'] = $dataHeader['pph'];
			// } else {
			// 	$total['pph'] = $total['pph'] +  $dataHeader['pph'];
			// }
			$html = $html . " <tr><td>" . $no . "</td>
                <td>" . $dataHeader['nama'] . "</td>
				<td>" . $dataHeader['nip'] . "</td>
                 <td>" . $dataHeader['jabatan'] . "</td>
                 <td>" . $dataHeader['divisi'] . "</td>
				 <td>" . $dataHeader['tipe_karyawan'] . "</td>
				 <td style='text-align: right'>" . $dataHeader['jumlah_hari_masuk'] . "</td>";
			$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['gapok']) . "</td>";
			$html = $html . $html_pendapatan;
			$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['premi']) . "</td>";
			$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['lembur']) . "</td>";
			$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($jumPendapatan) . "</td>";
			$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['denda']) . "</td>";
			$html = $html . $html_potongan;
			$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['hk_potongan_gaji']) . "</td>";
			$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['jht']) . "</td>";
			$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['jp']) . "</td>";
			$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['jkn']) . "</td>";
			$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($jumPotongan) . "</td>";
			$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($jumPendapatan - $jumPotongan) . "</td>";
			$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['nilai_rp_catu']) . "</td>";
			$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($jumPendapatan - $jumPotongan + $dataHeader['nilai_rp_catu']) . "</td>";
			$html = $html . "</tr>";
			$idx = $i + 1;
			$sub_bagian_next = $dataHd[$idx]['sub_bagian_id'];
			if (!is_null($sub_bagian_next)) {
				if ($sub_bagian_next != $dataHeader['sub_bagian_id']) {
					$html = $html . "<tr><td colspan='6' style='text-align: left'><b> " . $dataHeader['divisi'] . "</b></td>";
					$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_hk) . "</b></td>";
					$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_gapok) . "</b></td>";
					foreach ($sub_total as $key => $value) {
						$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($value) . "</b></td>";
					}

					$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_totalPendapatan - $sub_totalPotongan) . "</b></td>";
					$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_rp_catu_beras) . "</b></td>";
					$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_totalPendapatan - $sub_totalPotongan + $sub_total_rp_catu_beras) . "</b></td>";
					$html = $html .
						"</tr>";

					$sub_total_hk = 0;
					$sub_total_gapok = 0;
					$sub_total = array();
					$sub_totalPendapatan = 0;
					$sub_totalPotongan = 0;
					$sub_total_rp_catu_beras = 0;
					// $html=$html. var_dump($sub_total);
				}
			} else {
				$html = $html . "<tr><td colspan='6' style='text-align: left'><b> " . $dataHeader['divisi'] . "</b></td>";
				$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_hk) . "</b></td>";
				$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_gapok) . "</b></td>";

				foreach ($sub_total as $key => $value) {
					$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($value) . "</b></td>";
				}

				$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_totalPendapatan - $sub_totalPotongan) . "</b></td>";
				$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_rp_catu_beras) . "</b></td>";
				$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_totalPendapatan - $sub_totalPotongan + $sub_total_rp_catu_beras) . "</b></td>";
				$html = $html .
					"</tr>";

				// $html=$html. var_dump($sub_total);
			}
		}
		// var_dump($total);exit();
		// total summary
		$html = $html . "<tr><td colspan='6' style='text-align: left' ><b>TOTAL</b></td>";
		$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($total_hk) . "</b></td>";
		$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($total_gapok) . "</b></td>";
		foreach ($total as $key => $value) {
			$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($value) . "</b></td>";
		}
		// $html = $html . "<td  style='text-align: right'>" . $this->format_number_report($total['premi']) . "</td>";
		// $html = $html . "<td  style='text-align: right'>" . $this->format_number_report($total['lembur']) . "</td>";

		$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($totalPendapatan - $totalPotongan) . "</b></td>";
		$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($total_rp_catu_beras) . "</b></td>";
		$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($totalPendapatan - $totalPotongan + $total_rp_catu_beras) . "</b></td>";
		$html = $html .
			"</tr>";
		//         <td style='text-align: right'>". $this->format_number_report( $jum)."</td>
		//         </tr>";
		$html = $html . " </table>
            <br>
            <table width='300px' class='no_border'  >
            <tr><td></td><td>&nbsp;</td></tr>
            <tr><td></td><td>&nbsp;</td></tr>
            <tr><td></td><td>&nbsp;</td></tr>
            <tr><td></td><td>&nbsp;</td></tr>
            <tr><td></td><td>&nbsp;</td></tr>
            <tr><td width='400px'>&nbsp; </td><td width='400px'>(  ______________________  )
            </td></tr>
            <tr><td width='400px'>&nbsp; </td><td width='400px'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;HRD
            </td></tr>
            </table>
            ";

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A3', 'landscape');
		//echo $html;
		// $html=$html. var_dump($total);

		return $html;
	}
	function print_rekap_pph($id, $divisi_id, $status_id)
	{


		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		// 		$html = $html . '<div class="row">
		// <div class="span12">
		//     <br>
		//     <div class="kop-print">
		// 	<img src=data:image/png;base64,' . base64_encode(file_get_contents(get_logo_config())) . ' alt="image" >
		//         <div class="kop-nama">' . get_pengaturan('nama-company', 'value') . '</div>
		//         <div class="kop-info">Alamat : ' . get_pengaturan('alamat', 'value') . ', Telepon :' . get_pengaturan('telp', 'value') . '</div>
		//     </div>
		//     <hr class="kop-print-hr">
		// </div>
		// </div>';
		$periodeGaji = $this->HrmsPeriodeGajiModel->retrieve($id);
		if (empty($periodeGaji)) {
			$this->set_response(array("status" => "NOT OK", "data" => 'null'), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$countPendapatan = 0;
		$pendapatan = $this->db->query("select * from payroll_tipe_gaji where jenis=1 or jenis=2 or jenis=3 order by urut")->result_array();
		$potongan = $this->db->query("select * from payroll_tipe_gaji where jenis=0 order by urut")->result_array();
		$countPendapatan = count($pendapatan) + 3;
		$countPotongan = count($potongan) + 5;

		// $sql = "
		// SELECT a.*,c.nip,c.nama,d.nama as jabatan,e.nama as departemen ,f.nama as divisi, c.sub_bagian_id,
		// c.tipe_karyawan_id , g.nama as tipe_karyawan
		// FROM payroll_gaji_tr_hd a 
		//  left join payroll_karyawan_gaji b on a.karyawan_id=b.karyawan_id
        // inner join karyawan c on a.karyawan_id=c.id 
		//  left join payroll_jabatan d on c.jabatan_id=d.id
        //  left join payroll_department e on c.departemen_id=e.id
		// left join gbm_organisasi f on c.sub_bagian_id=f.id
		// left join payroll_tipe_karyawan g on g.id=c.tipe_karyawan_id 
        // where a.periode_gaji_id=" . $id . "";

			/*UPDATE: DIVISI/AFDELING NGMBIL DARI TABEL PAYROLL GAJI BUKAN DR TABLE MASTER KARYAWAN */
			$sql = "
			SELECT a.*,c.nip,c.nama,d.nama as jabatan,e.nama as departemen ,f.nama as divisi,a.divisi_id as sub_bagian_id,
			c.tipe_karyawan_id , g.nama as tipe_karyawan
			FROM payroll_gaji_tr_hd a 
			 left join payroll_karyawan_gaji b on a.karyawan_id=b.karyawan_id
			inner join karyawan c on a.karyawan_id=c.id 
			 left join payroll_jabatan d on c.jabatan_id=d.id
			 left join payroll_department e on c.departemen_id=e.id
			left join gbm_organisasi f on a.divisi_id=f.id
			left join payroll_tipe_karyawan g on g.id=c.tipe_karyawan_id 
			where a.periode_gaji_id=" . $id . "";
		$nama_afd_stasiun = "Semua";
		$res_lokasi = $this->db->query("select * from gbm_organisasi where id=" . $periodeGaji['lokasi_id'] . "")->row_array();
		$nama_lokasi = $res_lokasi['nama'];

		if (($divisi_id)) {
			// $sql = $sql . " and c.sub_bagian_id=" . $divisi_id . "";
			$sql = $sql . " and a.divisi_id=" . $divisi_id . "";
			$res_afd_stasiun = $this->db->query("select * from  gbm_organisasi where id=" . $divisi_id . "")->row_array();
			$nama_afd_stasiun = $res_afd_stasiun['nama'];
		}
		if (($status_id)) {
			$sql = $sql . " and c.tipe_karyawan_id=" . $status_id . "";
		}

		$sql = $sql . " order by f.nama,c.nama;";
		$dataHd = $this->db->query($sql)->result_array();
		$html = get_header_report_v2();
		$html = $html . '
		<div class="row">
		<div class="span12">
			<br>
			<div class="kop-print">
			<div class="kop-nama">KLINIK ANNAJAH</div>
			<div class="kop-info"> Jl Sukarno Hatta No 12 No 12, Bandung Jawa Barat 14450</div>
			<div class="kop-info">Telp : (021) 6684055</div>
		</div>
			<hr style="width:2095px" class="kop-print-hr">
		</div>
		</div>
		<h3 class="title">LAPORAN REKAP PENGGAJIAN (PPH)</h3>
  <table class="no_border" style="width:30%">
  		
			<tr>
					<td>LOKASI</td>
					<td>:</td>
					<td>' . $nama_lokasi . '</td>
			</tr>
			<tr>	
					<td>PERIODE</td>
					<td>:</td>
					<td>' . $periodeGaji['nama'] . '</td>
			</tr>
			<tr>	
					<td>AFDELING/STATION</td>
					<td>:</td>
					<td>' . $nama_afd_stasiun . '</td>
			</tr>

					
	</table>
	<br>
  ';
		$html = $html . "
		 <table   border='1' width='100%' style='border-collapse: collapse;'>
		 <thead>
		 <tr>
            <th rowspan=2 >No</th>
            <th rowspan=2>Nama</th>
			<th rowspan=2>NIK</th>
            <th rowspan=2>Jabatan</th>
            <th rowspan=2>Divisi</th>
			<th rowspan=2>Status</th>
			<th rowspan=2>HK</th>
            <th colspan=" . $countPendapatan . "  style='text-align: center'>Pendapatan (Rp)</th>
			<th  rowspan=2 style='text-align: center'>Total Pendapatan (Rp)</th>
            <th  colspan=" . $countPotongan . "  style='text-align: center'>Potongan (Rp)</th>
			<th  rowspan=2 style='text-align: center'>Total Potongan (Rp)</th>
            <th rowspan=2  style='text-align: center'>Gaji Bersih (Rp)</th>
			<th colspan=3  style='text-align: center'>Penambah (Rp)</th>
			<th rowspan=2>Catu Beras (Rp)</th>
			<th rowspan=2>Jumlah Gaji PPH (Rp)</th>
        </tr>";

		$html = $html . "<tr>";

		$html = $html . "<th style='text-align: center'>" . 'Gaji Pokok' . "</th>";
		foreach ($pendapatan as $key => $value) {
			$html = $html . "<th style='text-align: center'>" . $value['nama'] . "</th>";
		}
		$html = $html . "<th style='text-align: center'>" . 'Premi' . "</th>";
		$html = $html . "<th style='text-align: center'>" . 'Lembur' . "</th>";
		$html = $html . "<th style='text-align: center'>" . 'Denda' . "</th>";
		foreach ($potongan as $key => $value) {
			$html = $html . "<th style='text-align: center'>" . $value['nama'] . "</th>";
		}
		$html = $html . "<th style='text-align: center'>" . 'Potongan HK' . "</th>";
		$html = $html . "<th style='text-align: center'>" . 'JHT' . "</th>";
		$html = $html . "<th style='text-align: center'>" . 'JP' . "</th>";
		$html = $html . "<th style='text-align: center'>" . 'JKN' . "</th>";
		$html = $html . "<th style='text-align: center'>" . 'JKK' . "</th>";
		$html = $html . "<th style='text-align: center'>" . 'JKM' . "</td>";
		$html = $html . "<th style='text-align: center'>" . 'KS' . "</th>";
		// $html = $html . "<td style='text-align: center'>" . 'PPH21' . "</td>";
		$html = $html . " </thead></tr>";
		// print_r($html);exit;


		$no = 0;
		$total = array();
		$sub_total = array();
		$totalPendapatan = 0;
		$totalPotongan = 0;
		$sub_totalPendapatan = 0;
		$sub_totalPotongan = 0;
		$total_hk = 0;
		$sub_total_hk = 0;
		$total_gapok = 0;
		$sub_total_gapok = 0;
		$total_jkk_perusahaan = 0;
		$sub_total_jkk_perusahaan = 0;
		$total_jkn_perusahaan = 0;
		$sub_total_jkn_perusahaan = 0;
		$total_jkm_perusahaan = 0;
		$sub_total_jkm_perusahaan = 0;
		$total_gaji_pph = 0;
		$sub_total_gaji_pph = 0;
		$total_catu_beras = 0;
		$sub_total_catu_beras = 0;
		$countRow = count($dataHd);
		for ($i = 0; $i < $countRow; $i++) {
			$dataHeader = $dataHd[$i];
			$no++;
			$jumPendapatan = 0;
			$jumPotongan = 0;
			$total_hk = $total_hk +  $dataHeader['jumlah_hari_masuk'];
			$total_gapok = $total_gapok +  $dataHeader['gapok'];
			$sub_total_hk = $sub_total_hk +  $dataHeader['jumlah_hari_masuk'];
			$sub_total_gapok = $sub_total_gapok +  $dataHeader['gapok'];
			// looping pendapatan
			$html_pendapatan = '';
			foreach ($pendapatan as $key => $value) {
				$dataPendapatan = $this->db->query("SELECT a.*,b.jenis,b.nama,b.urut FROM payroll_gaji_tr_dt a inner join payroll_tipe_gaji b
                    on a.tipe_gaji=b.id
                    where a.id_hd=" . $dataHeader['id'] . " and a.tipe_gaji=" . $value['id'] . ";")->row_array();
				if (empty($dataPendapatan)) {
					$html_pendapatan =  $html_pendapatan . "<td  style='text-align: right'>" . 0 . "</td>";
					if (empty($total[$value['nama']])) {
						$total[$value['nama']] = 0;
					} else {
						//$total[$value['nama']] = $total[$value['nama']] ;
					}
					if (empty($sub_total[$value['nama']])) {

						$sub_total[$value['nama']] = 0;
					} else {

						//$sub_total[$value['nama']] = $sub_total[$value['nama']] ;
					}
				} else {
					$html_pendapatan = $html_pendapatan . "<td  style='text-align: right'>" . $this->format_number_report($dataPendapatan['nilai']) . "</td>";
					$jumPendapatan = $jumPendapatan + $dataPendapatan['nilai'];
					$totalPendapatan = $totalPendapatan + $dataPendapatan['nilai'];
					if (empty($total[$value['nama']])) {
						$total[$value['nama']] = $dataPendapatan['nilai'];
					} else {
						$total[$value['nama']] = $total[$value['nama']] + $dataPendapatan['nilai'];
					}
					if (empty($sub_total[$value['nama']])) {

						$sub_total[$value['nama']] = $dataPendapatan['nilai'];
					} else {

						$sub_total[$value['nama']] = $sub_total[$value['nama']] + $dataPendapatan['nilai'];
					}
				}
			}

			$jumPendapatan = $jumPendapatan + $dataHeader['gapok'] +  $dataHeader['lembur'] + $dataHeader['premi'];
			$totalPendapatan = $totalPendapatan + $dataHeader['gapok'] + $dataHeader['lembur'] + $dataHeader['premi'];
			$sub_totalPendapatan = $sub_totalPendapatan + $dataHeader['gapok'] + $dataHeader['lembur'] + $dataHeader['premi'];

			if (empty($total['premi'])) {
				$total['premi'] = $dataHeader['premi'];
			} else {
				$total['premi'] = $total['premi'] +  $dataHeader['premi'];
			}
			if (empty($total['lembur'])) {
				$total['lembur'] = $dataHeader['lembur'];
			} else {
				$total['lembur'] = $total['lembur'] +  $dataHeader['lembur'];
			}


			if (empty($sub_total['premi'])) {

				$sub_total['premi'] = $dataHeader['premi'];
			} else {

				$sub_total['premi'] = $sub_total['premi'] +  $dataHeader['premi'];
			}
			if (empty($sub_total['lembur'])) {

				$sub_total['lembur'] = $dataHeader['lembur'];
			} else {

				$sub_total['lembur'] = $sub_total['lembur'] +  $dataHeader['lembur'];
			}
			if (empty($total['jum_pendapatan'])) {

				$total['jum_pendapatan'] = $jumPendapatan;
			} else {

				$total['jum_pendapatan'] = $total['jum_pendapatan'] +  $jumPendapatan;
			}
			if (empty($total['denda'])) {
				$total['denda'] = $dataHeader['denda'];
			} else {
				$total['denda'] = $total['denda'] +  $dataHeader['denda'];
			}
			if (empty($sub_total['jum_pendapatan'])) {

				$sub_total['jum_pendapatan'] = $jumPendapatan;
			} else {

				$sub_total['jum_pendapatan'] = $sub_total['jum_pendapatan'] +  $jumPendapatan;
			}
			if (empty($sub_total['denda'])) {

				$sub_total['denda'] = $dataHeader['denda'];
			} else {

				$sub_total['denda'] = $sub_total['denda'] +  $dataHeader['denda'];
			}

			// looping potongan
			$html_potongan = '';
			foreach ($potongan as $key => $value) {
				$dataPotongan = $this->db->query("SELECT a.*,b.jenis,b.nama,b.urut FROM payroll_gaji_tr_dt a inner join payroll_tipe_gaji b
                    on a.tipe_gaji=b.id
                    where a.id_hd=" . $dataHeader['id'] . " and a.tipe_gaji=" . $value['id'] . ";")->row_array();
				if (empty($dataPotongan)) {
					$html_potongan = $html_potongan . "<td  style='text-align: right'>" . 0 . "</td>";
					$total[$value['nama']] = 0;
					$sub_total[$value['nama']] = 0;
				} else {
					$html_potongan = $html_potongan . "<td  style='text-align: right'>" . $this->format_number_report($dataPotongan['nilai']) . "</td>";
					$jumPotongan = $jumPotongan + $dataPotongan['nilai'];
					$totalPotongan = $totalPotongan + $dataPotongan['nilai'];
					if (empty($total[$value['nama']])) {
						$total[$value['nama']] = $dataPotongan['nilai'];
					} else {
						$total[$value['nama']] = $total[$value['nama']] + $dataPotongan['nilai'];
					}
					if (empty($sub_total[$value['nama']])) {

						$sub_total[$value['nama']] = $dataPotongan['nilai'];
					} else {

						$sub_total[$value['nama']] = $sub_total[$value['nama']] + $dataPotongan['nilai'];
					}
				}
			}


			$jumPotongan = $jumPotongan + $dataHeader['hk_potongan_gaji'] + $dataHeader['jht'] +  $dataHeader['jp'] + $dataHeader['jkn'] + $dataHeader['denda'];
			$jumlah_gaji_pph = $jumPendapatan + $dataHeader['jkk_perusahaan'] + $dataHeader['jkn_perusahaan'] + $dataHeader['jkm_perusahaan'] + $dataHeader['nilai_rp_catu'] - $jumPotongan;
			$totalPotongan = $totalPotongan + $dataHeader['hk_potongan_gaji'] + $dataHeader['jht'] +  $dataHeader['jp'] + $dataHeader['jkn'] + $dataHeader['denda'];
			$sub_totalPotongan = $sub_totalPotongan + $dataHeader['hk_potongan_gaji'] + $dataHeader['jht'] +  $dataHeader['jp'] + $dataHeader['jkn'] + $dataHeader['denda'];
			if (empty($sub_total['hk_potongan_gaji'])) {
				$sub_total['hk_potongan_gaji'] = $dataHeader['hk_potongan_gaji'];
			} else {
				$sub_total['hk_potongan_gaji'] = $sub_total['hk_potongan_gaji'] +  $dataHeader['hk_potongan_gaji'];
			}
			if (empty($sub_total['jht'])) {
				$sub_total['jht'] = $dataHeader['jht'];
			} else {
				$sub_total['jht'] = $sub_total['jht'] +  $dataHeader['jht'];
			}
			if (empty($sub_total['jp'])) {
				$sub_total['jp'] = $dataHeader['jp'];
			} else {
				$sub_total['jp'] = $sub_total['jp'] +  $dataHeader['jp'];
			}
			if (empty($sub_total['jkn'])) {
				$sub_total['jkn'] = $dataHeader['jkn'];
			} else {
				$sub_total['jkn'] = $sub_total['jkn'] +  $dataHeader['jkn'];
			}
			if (empty($sub_total['jum_potongan'])) {
				$sub_total['jum_potongan'] = $jumPotongan;
			} else {
				$sub_total['jum_potongan'] = $sub_total['jum_potongan'] +  $jumPotongan;
			}
			if (empty($total['hk_potongan_gaji'])) {
				$total['hk_potongan_gaji'] = $dataHeader['hk_potongan_gaji'];
			} else {
				$total['hk_potongan_gaji'] = $total['hk_potongan_gaji'] +  $dataHeader['hk_potongan_gaji'];
			}
			if (empty($total['jht'])) {
				$total['jht'] = $dataHeader['jht'];
			} else {
				$total['jht'] = $total['jht'] +  $dataHeader['jht'];
			}
			if (empty($total['jp'])) {
				$total['jp'] = $dataHeader['jp'];
			} else {
				$total['jp'] = $total['jp'] +  $dataHeader['jp'];
			}
			if (empty($total['jkn'])) {
				$total['jkn'] = $dataHeader['jkn'];
			} else {
				$total['jkn'] = $total['jkn'] +  $dataHeader['jkn'];
			}
			if (empty($total['jum_potongan'])) {
				$total['jum_potongan'] = $jumPotongan;
			} else {
				$total['jum_potongan'] = $total['jum_potongan'] + $jumPotongan;
			}
			$total_jkk_perusahaan = $total_jkk_perusahaan + $dataHeader['jkk_perusahaan'];
			$sub_total_jkk_perusahaan = $sub_total_jkk_perusahaan + $dataHeader['jkk_perusahaan'];
			$total_jkn_perusahaan = $total_jkn_perusahaan + $dataHeader['jkn_perusahaan'];
			$sub_total_jkn_perusahaan =  $sub_total_jkn_perusahaan + $dataHeader['jkn_perusahaan'];
			$total_jkm_perusahaan =  $total_jkm_perusahaan + $dataHeader['jkm_perusahaan'];
			$sub_total_jkm_perusahaan =  $sub_total_jkm_perusahaan + $dataHeader['jkm_perusahaan'];
			$total_catu_beras = $total_catu_beras + $dataHeader['nilai_rp_catu'];
			$sub_total_catu_beras = $sub_total_catu_beras + $dataHeader['nilai_rp_catu'];
			$total_gaji_pph = $total_gaji_pph + $jumlah_gaji_pph;
			$sub_total_gaji_pph = $sub_total_gaji_pph + $jumlah_gaji_pph;
			$html = $html . " <tr><td>" . $no . "</td>
						<td>" . $dataHeader['nama'] . "</td>
						<td>" . $dataHeader['nip'] . "</td>
                 <td>" . $dataHeader['jabatan'] . "</td>
                 <td>" . $dataHeader['divisi'] . "</td>
				 <td>" . $dataHeader['tipe_karyawan'] . "</td>
				 <td style='text-align: right'>" . $dataHeader['jumlah_hari_masuk'] . "</td>";
			$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['gapok']) . "</td>";
			$html = $html . $html_pendapatan;
			$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['premi']) . "</td>";
			$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['lembur']) . "</td>";
			$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($jumPendapatan) . "</td>";
			$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['denda']) . "</td>";

			$html = $html . $html_potongan;
			$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['hk_potongan_gaji']) . "</td>";
			$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['jht']) . "</td>";
			$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['jp']) . "</td>";
			$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['jkn']) . "</td>";
			$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($jumPotongan) . "</td>";
			$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($jumPendapatan - $jumPotongan) . "</td>";
			$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['jkk_perusahaan']) . "</td>";
			$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['jkm_perusahaan']) . "</td>";
			$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['jkn_perusahaan']) . "</td>";
			$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['nilai_rp_catu']) . "</td>";
			$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($jumlah_gaji_pph) . "</td>";
			$html = $html . "</tr>";
			$idx = $i + 1;
			$sub_bagian_next = $dataHd[$idx]['sub_bagian_id'];
			if (!is_null($sub_bagian_next)) {
				if ($sub_bagian_next != $dataHeader['sub_bagian_id']) {
					$html = $html . "<tr><td colspan='6' style='text-align: left'><b> " . $dataHeader['divisi'] . "</b></td>";
					$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_hk) . "</b></td>";
					$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_gapok) . "</b></td>";
					foreach ($sub_total as $key => $value) {
						$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($value) . "</b></td>";
					}

					$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_totalPendapatan - $sub_totalPotongan) . "</b></td>";
					$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_jkk_perusahaan) . "</b></td>";
					$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_jkm_perusahaan) . "</b></td>";
					$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_jkn_perusahaan) . "</b></td>";
					$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_catu_beras) . "</b></td>";
					$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_gaji_pph) . "</b></td>";
					$html = $html .	"</tr>";

					$sub_total_hk = 0;
					$sub_total_gapok = 0;
					$sub_total = array();
					$sub_totalPendapatan = 0;
					$sub_totalPotongan = 0;
					$sub_total_jkk_perusahaan = 0;
					$sub_total_jkn_perusahaan = 0;
					$sub_total_jkm_perusahaan = 0;
					$sub_total_gaji_pph = 0;
					$sub_total_catu_beras = 0;
				}
			} else {
				$html = $html . "<tr><td colspan='6' style='text-align: left'><b> " . $dataHeader['divisi'] . "</b></td>";
				$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_hk) . "</b></td>";
				$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_gapok) . "</b></td>";

				foreach ($sub_total as $key => $value) {
					$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($value) . "</b></td>";
				}

				$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_totalPendapatan - $sub_totalPotongan) . "</b></td>";
				$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_jkk_perusahaan) . "</b></td>";
				$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_jkm_perusahaan) . "</b></td>";
				$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_jkn_perusahaan) . "</b></td>";
				$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_catu_beras) . "</b></td>";
				$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_gaji_pph) . "</b></td>";
				$html = $html .	"</tr>";

				// $html=$html. var_dump($sub_total);
			}
		}
		// var_dump($total);exit();
		// total summary
		$html = $html . "<tr><td colspan='6' style='text-align: left' ><b>TOTAL</b></td>";
		$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($total_hk) . "</b></td>";
		$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($total_gapok) . "</b></td>";
		foreach ($total as $key => $value) {
			$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($value) . "</b></td>";
		}
		// $html = $html . "<td  style='text-align: right'>" . $this->format_number_report($total['premi']) . "</td>";
		// $html = $html . "<td  style='text-align: right'>" . $this->format_number_report($total['lembur']) . "</td>";

		$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($totalPendapatan - $totalPotongan) . "</b></td>";
		$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($total_jkk_perusahaan) . "</b></td>";
		$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($total_jkm_perusahaan) . "</b></td>";
		$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($total_jkn_perusahaan) . "</b></td>";
		$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($total_catu_beras) . "</b></td>";
		$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($total_gaji_pph) . "</b></td>";
		$html = $html .
			"</tr>";
		//         <td style='text-align: right'>". $this->format_number_report( $jum)."</td>
		//         </tr>";
		$html = $html . " </table>
            <br>
            <table class='no_border' style='width:20%'>
            <tr><td></td><td>&nbsp;</td></tr>
            <tr><td></td><td>&nbsp;</td></tr>
            <tr><td></td><td>&nbsp;</td></tr>
            <tr><td></td><td>&nbsp;</td></tr>
            <tr><td></td><td>&nbsp;</td></tr>
            <tr><td width='400px'>&nbsp; </td><td width='400px'>(  ______________________  )
            </td></tr>
            <tr><td width='400px'>&nbsp; </td><td width='400px'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;HRD
            </td></tr>
            </table>
            ";

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A3', 'landscape');
		//echo $html;
		// $html=$html. var_dump($total);

		return $html;
	}
	function print_rekap_pph_by_unit_kerja($id, $divisi_id, $status_id)
	{


		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		// 		$html = $html . '<div class="row">
		// <div class="span12">
		//     <br>
		//     <div class="kop-print">
		// 	<img src=data:image/png;base64,' . base64_encode(file_get_contents(get_logo_config())) . ' alt="image" >
		//         <div class="kop-nama">' . get_pengaturan('nama-company', 'value') . '</div>
		//         <div class="kop-info">Alamat : ' . get_pengaturan('alamat', 'value') . ', Telepon :' . get_pengaturan('telp', 'value') . '</div>
		//     </div>
		//     <hr class="kop-print-hr">
		// </div>
		// </div>';
		$periodeGaji = $this->HrmsPeriodeGajiModel->retrieve($id);
		if (empty($periodeGaji)) {
			$this->set_response(array("status" => "NOT OK", "data" => 'null'), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$countPendapatan = 0;
		$pendapatan = $this->db->query("select * from payroll_tipe_gaji where jenis=1 or jenis=2 or jenis=3 order by urut")->result_array();
		$potongan = $this->db->query("select * from payroll_tipe_gaji where jenis=0 order by urut")->result_array();
		$countPendapatan = count($pendapatan) + 3;
		$countPotongan = count($potongan) + 2;

		// $sql = "
		// SELECT a.*,c.nip,c.nama,d.nama as jabatan,e.nama as departemen ,f.nama as divisi, c.sub_bagian_id,
		// c.tipe_karyawan_id , g.nama as tipe_karyawan
		// FROM payroll_gaji_tr_hd a 
		//  left join payroll_karyawan_gaji b on a.karyawan_id=b.karyawan_id
        // inner join karyawan c on a.karyawan_id=c.id 
		//  left join payroll_jabatan d on c.jabatan_id=d.id
        //  left join payroll_department e on c.departemen_id=e.id
		// left join gbm_organisasi f on c.sub_bagian_id=f.id
		// left join payroll_tipe_karyawan g on g.id=c.tipe_karyawan_id 
        // where a.periode_gaji_id=" . $id . "";

			/*UPDATE: DIVISI/AFDELING NGMBIL DARI TABEL PAYROLL GAJI BUKAN DR TABLE MASTER KARYAWAN */
			$sql = "
		SELECT a.*,c.nip,c.nama,d.nama as jabatan,e.nama as departemen ,f.nama as divisi,a.divisi_id as sub_bagian_id,
		c.tipe_karyawan_id , g.nama as tipe_karyawan
		FROM payroll_gaji_tr_hd a 
		 left join payroll_karyawan_gaji b on a.karyawan_id=b.karyawan_id
        inner join karyawan c on a.karyawan_id=c.id 
		 left join payroll_jabatan d on c.jabatan_id=d.id
         left join payroll_department e on c.departemen_id=e.id
		left join gbm_organisasi f on a.divisi_id=f.id
		left join payroll_tipe_karyawan g on g.id=c.tipe_karyawan_id 
        where a.periode_gaji_id=" . $id . "";


		$nama_afd_stasiun = "Semua";
		$res_lokasi = $this->db->query("select * from gbm_organisasi where id=" . $periodeGaji['lokasi_id'] . "")->row_array();
		$nama_lokasi = $res_lokasi['nama'];

		if (($divisi_id)) {
			// $sql = $sql . " and c.sub_bagian_id=" . $divisi_id . "";
			$sql = $sql . " and a.divisi_id=" . $divisi_id . "";
			$res_afd_stasiun = $this->db->query("select * from  gbm_organisasi where id=" . $divisi_id . "")->row_array();
			$nama_afd_stasiun = $res_afd_stasiun['nama'];
		}
		if (($status_id)) {
			$sql = $sql . " and c.tipe_karyawan_id=" . $status_id . "";
		}

		$sql = $sql . " order by f.nama,g.nama;";
		$dataHd = $this->db->query($sql)->result_array();
		$html = get_header_report_v2();
		$html = $html . '
		<div class="row">
		<div class="span12">
			<br>
			<div class="kop-print">
			<div class="kop-nama">KLINIK ANNAJAH</div>
			<div class="kop-info"> Jl Sukarno Hatta No 12 No 12, Bandung Jawa Barat 14450</div>
			<div class="kop-info">Telp : (021) 6684055</div>
		</div>
			<hr style="width:2095px" class="kop-print-hr">
		</div>
		</div>
		<h3 class="title">LAPORAN REKAP PENGGAJIAN PPH BY UNIT KERJA </h3>
  <table class="no_border" style="width:30%">
  		
			<tr>
					<td>LOKASI</td>
					<td>:</td>
					<td>' . $nama_lokasi . '</td>
			</tr>
			<tr>	
					<td>PERIODE</td>
					<td>:</td>
					<td>' . $periodeGaji['nama'] . '</td>
			</tr>
			<tr>	
					<td>AFDELING/STATION</td>
					<td>:</td>
					<td>' . $nama_afd_stasiun . '</td>
			</tr>

					
	</table>
	<br>
  ';
		$html = $html . "
		 <table   border='1' width='100%' style='border-collapse: collapse;'>
		 <thead>
		 <tr>
           
             <th rowspan=2>Divisi</th>
			<th rowspan=2>Type Karyawan</th>
			<th rowspan=2>TK</th>
            <th colspan=" . $countPendapatan . "  style='text-align: center'>Pendapatan (Rp) </th>
            <th colspan=" . $countPotongan . "  style='text-align: center'>Potongan (Rp)</th>
            <th rowspan=2  style='text-align: center'>Total Pendapatan (Rp)</th>
			<th colspan=3  style='text-align: center'>Penambah  (Rp)</th>
			<th colspan=2  style='text-align: center'>Pengurang (Rp) </th>
			<th rowspan=2>Catu Beras (Rp)</th>
			<th rowspan=2>Gaji PPH 21 (Rp)</th>
        </tr>";

		$html = $html . "<tr>";

		$html = $html . "<th style='text-align: center'>" . 'Gaji Pokok' . "</th>";
		foreach ($pendapatan as $key => $value) {
			$html = $html . "<th style='text-align: center'>" . $value['nama'] . "</th>";
		}
		$html = $html . "<th style='text-align: center'>" . 'Premi' . "</th>";
		$html = $html . "<th style='text-align: center'>" . 'Lembur' . "</th>";
		$html = $html . "<th style='text-align: center'>" . 'Denda' . "</th>";
		foreach ($potongan as $key => $value) {
			$html = $html . "<th style='text-align: center'>" . $value['nama'] . "</th>";
		}
		$html = $html . "<th style='text-align: center'>" . 'Potongan HK' . "</th>";

		$html = $html . "<th style='text-align: center'>" . 'JKK(0,89%)' . "</th>";
		$html = $html . "<th style='text-align: center'>" . 'JKM(0,3%)' . "</th>";
		$html = $html . "<th style='text-align: center'>" . 'KS(4%)' . "</th>";
		$html = $html . "<th style='text-align: center'>" . 'JHT(2%)' . "</th>";
		$html = $html . "<th style='text-align: center'>" . 'JP(1%)' . "</td>";

		$html = $html . " </thead></tr>";
		// print_r($html);exit;


		$no = 0;
		$total = array();
		$sub_total = array();
		$totalPendapatan = 0;
		$totalPotongan = 0;
		$sub_totalPendapatan = 0;
		$sub_totalPotongan = 0;
		$total_hk = 0;
		$sub_total_hk = 0;
		$total_tk = 0;
		$sub_total_tk = 0;
		$sub_total_tipe_karyawan_tk = 0;

		$total_gapok = 0;
		$sub_total_gapok = 0;
		$total_jkk_perusahaan = 0;
		$total_jkn_perusahaan = 0;
		$total_jkm_perusahaan = 0;
		$total_jht = 0;
		$total_jp = 0;
		$total_jkn = 0;


		$sub_total_jkk_perusahaan = 0;
		$sub_total_jkn_perusahaan = 0;
		$sub_total_jkm_perusahaan = 0;
		$sub_total_jht = 0;
		$sub_total_jp = 0;
		$sub_total_jkn = 0;

		$total_gaji_pph = 0;
		$sub_total_gaji_pph = 0;
		$total_catu_beras = 0;
		$sub_total_catu_beras = 0;


		$sub_total_tipe_karyawan_hk = 0;
		$sub_total_tipe_karyawan_gapok = 0;
		$sub_total_tipe_karyawan = array();
		$sub_total_tipe_karyawan_Pendapatan = 0;
		$sub_total_tipe_karyawan_Potongan = 0;
		$sub_total_tipe_karyawan_jkk_perusahaan = 0;
		$sub_total_tipe_karyawan_jkn_perusahaan = 0;
		$sub_total_tipe_karyawan_jkm_perusahaan = 0;
		$sub_total_tipe_karyawan_jht = 0;
		$sub_total_tipe_karyawan_jp = 0;
		$sub_total_tipe_karyawan_jkn = 0;

		$sub_total_tipe_karyawan_gaji_pph = 0;
		$sub_total_tipe_karyawan_catu_beras = 0;
		$countRow = count($dataHd);
		for ($i = 0; $i < $countRow; $i++) {
			$dataHeader = $dataHd[$i];
			$no++;
			$jumPendapatan = 0;
			$jumPotongan = 0;
			$total_hk = $total_hk +  $dataHeader['jumlah_hari_masuk'];
			$total_gapok = $total_gapok +  $dataHeader['gapok'];
			$sub_total_hk = $sub_total_hk +  $dataHeader['jumlah_hari_masuk'];
			$total_tk = $total_tk + 1;
			$sub_total_tk = $sub_total_tk + 1;
			$sub_total_tipe_karyawan_tk = $sub_total_tipe_karyawan_tk + 1;
			$sub_total_tipe_karyawan_hk = $sub_total_tipe_karyawan_hk +  $dataHeader['jumlah_hari_masuk'];
			$sub_total_gapok = $sub_total_gapok +  $dataHeader['gapok'];
			$sub_total_tipe_karyawan_gapok = $sub_total_tipe_karyawan_gapok +  $dataHeader['gapok'];
			// looping pendapatan
			$html_pendapatan = '';
			foreach ($pendapatan as $key => $value) {
				$dataPendapatan = $this->db->query("SELECT a.*,b.jenis,b.nama,b.urut FROM payroll_gaji_tr_dt a inner join payroll_tipe_gaji b
                    on a.tipe_gaji=b.id
                    where a.id_hd=" . $dataHeader['id'] . " and a.tipe_gaji=" . $value['id'] . ";")->row_array();
				if (empty($dataPendapatan)) {
					$html_pendapatan =  $html_pendapatan . "<td  style='text-align: right'>" . 0 . "</td>";
					if (empty($total[$value['nama']])) {
						$total[$value['nama']] = 0;
					} else {
					}
					if (empty($sub_total[$value['nama']])) {

						$sub_total[$value['nama']] = 0;
					} else {
					}
					if (empty($sub_total_tipe_karyawan[$value['nama']])) {

						$sub_total_tipe_karyawan[$value['nama']] = 0;
					} else {
					}
				} else {
					$html_pendapatan = $html_pendapatan . "<td  style='text-align: right'>" . $this->format_number_report($dataPendapatan['nilai']) . "</td>";
					$jumPendapatan = $jumPendapatan + $dataPendapatan['nilai'];
					$totalPendapatan = $totalPendapatan + $dataPendapatan['nilai'];
					if (empty($total[$value['nama']])) {
						$total[$value['nama']] = $dataPendapatan['nilai'];
					} else {
						$total[$value['nama']] = $total[$value['nama']] + $dataPendapatan['nilai'];
					}
					if (empty($sub_total[$value['nama']])) {

						$sub_total[$value['nama']] = $dataPendapatan['nilai'];
					} else {

						$sub_total[$value['nama']] = $sub_total[$value['nama']] + $dataPendapatan['nilai'];
					}
					if (empty($sub_total_tipe_karyawan[$value['nama']])) {

						$sub_total_tipe_karyawan[$value['nama']] = $dataPendapatan['nilai'];
					} else {

						$sub_total_tipe_karyawan[$value['nama']] = $sub_total_tipe_karyawan[$value['nama']] + $dataPendapatan['nilai'];
					}
				}
			}

			$jumPendapatan = $jumPendapatan + $dataHeader['gapok'] +  $dataHeader['lembur'] + $dataHeader['premi'];
			$totalPendapatan = $totalPendapatan + $dataHeader['gapok'] + $dataHeader['lembur'] + $dataHeader['premi'];
			$sub_totalPendapatan = $sub_totalPendapatan + $dataHeader['gapok'] + $dataHeader['lembur'] + $dataHeader['premi'];
			$sub_total_tipe_karyawan_Pendapatan = $sub_total_tipe_karyawan_Pendapatan + $dataHeader['gapok'] + $dataHeader['lembur'] + $dataHeader['premi'];
			if (empty($total['premi'])) {
				$total['premi'] = $dataHeader['premi'];
			} else {
				$total['premi'] = $total['premi'] +  $dataHeader['premi'];
			}
			if (empty($total['lembur'])) {
				$total['lembur'] = $dataHeader['lembur'];
			} else {
				$total['lembur'] = $total['lembur'] +  $dataHeader['lembur'];
			}
			// if (empty($total['jum_pendapatan'])) {

			// 	$total['jum_pendapatan'] = $jumPendapatan;
			// } else {

			// 	$total['jum_pendapatan'] = $total['jum_pendapatan'] +  $jumPendapatan;
			// }
			if (empty($total['denda'])) {
				$total['denda'] = $dataHeader['denda'];
			} else {
				$total['denda'] = $total['denda'] +  $dataHeader['denda'];
			}

			if (empty($sub_total['premi'])) {

				$sub_total['premi'] = $dataHeader['premi'];
			} else {

				$sub_total['premi'] = $sub_total['premi'] +  $dataHeader['premi'];
			}
			if (empty($sub_total['lembur'])) {

				$sub_total['lembur'] = $dataHeader['lembur'];
			} else {

				$sub_total['lembur'] = $sub_total['lembur'] +  $dataHeader['lembur'];
			}

			// if (empty($sub_total['jum_pendapatan'])) {

			// 	$sub_total['jum_pendapatan'] = $jumPendapatan;
			// } else {

			// 	$sub_total['jum_pendapatan'] = $sub_total['jum_pendapatan'] +  $jumPendapatan;
			// }
			if (empty($sub_total['denda'])) {

				$sub_total['denda'] = $dataHeader['denda'];
			} else {

				$sub_total['denda'] = $sub_total['denda'] +  $dataHeader['denda'];
			}

			// sub total by tipe karyawan //
			if (empty($sub_total_tipe_karyawan['premi'])) {

				$sub_total_tipe_karyawan['premi'] = $dataHeader['premi'];
			} else {

				$sub_total_tipe_karyawan['premi'] = $sub_total_tipe_karyawan['premi'] +  $dataHeader['premi'];
			}
			if (empty($sub_total_tipe_karyawan['lembur'])) {

				$sub_total_tipe_karyawan['lembur'] = $dataHeader['lembur'];
			} else {

				$sub_total_tipe_karyawan['lembur'] = $sub_total_tipe_karyawan['lembur'] +  $dataHeader['lembur'];
			}

			// if (empty($sub_total_tipe_karyawan['jum_pendapatan'])) {

			// 	$sub_total_tipe_karyawan['jum_pendapatan'] = $jumPendapatan;
			// } else {

			// 	$sub_total_tipe_karyawan['jum_pendapatan'] = $sub_total_tipe_karyawan['jum_pendapatan'] +  $jumPendapatan;
			// }
			if (empty($sub_total_tipe_karyawan['denda'])) {

				$sub_total_tipe_karyawan['denda'] = $dataHeader['denda'];
			} else {

				$sub_total_tipe_karyawan['denda'] = $sub_total_tipe_karyawan['denda'] +  $dataHeader['denda'];
			}

			// looping potongan
			$html_potongan = '';
			foreach ($potongan as $key => $value) {
				$dataPotongan = $this->db->query("SELECT a.*,b.jenis,b.nama,b.urut FROM payroll_gaji_tr_dt a inner join payroll_tipe_gaji b
                    on a.tipe_gaji=b.id
                    where a.id_hd=" . $dataHeader['id'] . " and a.tipe_gaji=" . $value['id'] . ";")->row_array();
				if (empty($dataPotongan)) {
					$html_potongan = $html_potongan . "<td  style='text-align: right'>" . 0 . "</td>";
					$total[$value['nama']] = 0;
					$sub_total[$value['nama']] = 0;
					$sub_total_tipe_karyawan[$value['nama']] = 0;
				} else {
					$html_potongan = $html_potongan . "<td  style='text-align: right'>" . $this->format_number_report($dataPotongan['nilai']) . "</td>";
					$jumPotongan = $jumPotongan + $dataPotongan['nilai'];
					$totalPotongan = $totalPotongan + $dataPotongan['nilai'];
					if (empty($total[$value['nama']])) {
						$total[$value['nama']] = $dataPotongan['nilai'];
					} else {
						$total[$value['nama']] = $total[$value['nama']] + $dataPotongan['nilai'];
					}
					if (empty($sub_total[$value['nama']])) {

						$sub_total[$value['nama']] = $dataPotongan['nilai'];
					} else {

						$sub_total[$value['nama']] = $sub_total[$value['nama']] + $dataPotongan['nilai'];
					}
					if (empty($sub_total_tipe_karyawan[$value['nama']])) {

						$sub_total_tipe_karyawan[$value['nama']] = $dataPotongan['nilai'];
					} else {

						$sub_total_tipe_karyawan[$value['nama']] = $sub_total_tipe_karyawan[$value['nama']] + $dataPotongan['nilai'];
					}
				}
			}


			$jumPotongan = $jumPotongan + $dataHeader['hk_potongan_gaji']  + $dataHeader['denda'];
			$jumlah_gaji_pph = $jumPendapatan + $dataHeader['jkk_perusahaan'] + $dataHeader['jkn_perusahaan'] + $dataHeader['jkm_perusahaan'] + $dataHeader['nilai_rp_catu'] - $jumPotongan;
			$totalPotongan = $totalPotongan + $dataHeader['hk_potongan_gaji'] +  $dataHeader['denda'];
			$sub_totalPotongan = $sub_totalPotongan + $dataHeader['hk_potongan_gaji'] + $dataHeader['denda'];
			$sub_total_tipe_karyawan_Potongan = $sub_total_tipe_karyawan_Potongan + $dataHeader['hk_potongan_gaji']  + $dataHeader['denda'];

			$jumPendapatan = $jumPendapatan  - $jumPotongan;
			$totalPendapatan = $totalPendapatan  - $jumPotongan;
			$sub_totalPendapatan = $sub_totalPendapatan - $jumPotongan;
			$sub_total_tipe_karyawan_Pendapatan = $sub_total_tipe_karyawan_Pendapatan - $jumPotongan;
			if (empty($sub_total['hk_potongan_gaji'])) {
				$sub_total['hk_potongan_gaji'] = $dataHeader['hk_potongan_gaji'];
			} else {
				$sub_total['hk_potongan_gaji'] = $sub_total['hk_potongan_gaji'] +  $dataHeader['hk_potongan_gaji'];
			}

			// if (empty($sub_total['jht'])) {
			// 	$sub_total['jht'] = $dataHeader['jht'];
			// } else {
			// 	$sub_total['jht'] = $sub_total['jht'] +  $dataHeader['jht'];
			// }
			// if (empty($sub_total['jp'])) {
			// 	$sub_total['jp'] = $dataHeader['jp'];
			// } else {
			// 	$sub_total['jp'] = $sub_total['jp'] +  $dataHeader['jp'];
			// }
			// if (empty($sub_total['jkn'])) {
			// 	$sub_total['jkn'] = $dataHeader['jkn'];
			// } else {
			// 	$sub_total['jkn'] = $sub_total['jkn'] +  $dataHeader['jkn'];
			// }
			// if (empty($sub_total['jum_potongan'])) {
			// 	$sub_total['jum_potongan'] = $jumPotongan;
			// } else {
			// 	$sub_total['jum_potongan'] = $sub_total['jum_potongan'] +  $jumPotongan;
			// }
			// Subtotal tipe karyawan
			if (empty($sub_total_tipe_karyawan['hk_potongan_gaji'])) {
				$sub_total_tipe_karyawan['hk_potongan_gaji'] = $dataHeader['hk_potongan_gaji'];
			} else {
				$sub_total_tipe_karyawan['hk_potongan_gaji'] = $sub_total_tipe_karyawan['hk_potongan_gaji'] +  $dataHeader['hk_potongan_gaji'];
			}
			// if (empty($sub_total_tipe_karyawan['jht'])) {
			// 	$sub_total_tipe_karyawan['jht'] = $dataHeader['jht'];
			// } else {
			// 	$sub_total_tipe_karyawan['jht'] = $sub_total_tipe_karyawan['jht'] +  $dataHeader['jht'];
			// }
			// if (empty($sub_total_tipe_karyawan['jp'])) {
			// 	$sub_total_tipe_karyawan['jp'] = $dataHeader['jp'];
			// } else {
			// 	$sub_total_tipe_karyawan['jp'] = $sub_total_tipe_karyawan['jp'] +  $dataHeader['jp'];
			// }
			// if (empty($sub_total_tipe_karyawan['jkn'])) {
			// 	$sub_total_tipe_karyawan['jkn'] = $dataHeader['jkn'];
			// } else {
			// 	$sub_total_tipe_karyawan['jkn'] = $sub_total_tipe_karyawan['jkn'] +  $dataHeader['jkn'];
			// }
			// if (empty($sub_total_tipe_karyawan['jum_potongan'])) {
			// 	$sub_total_tipe_karyawan['jum_potongan'] = $jumPotongan;
			// } else {
			// 	$sub_total_tipe_karyawan['jum_potongan'] = $sub_total_tipe_karyawan['jum_potongan'] +  $jumPotongan;
			// }

			if (empty($total['hk_potongan_gaji'])) {
				$total['hk_potongan_gaji'] = $dataHeader['hk_potongan_gaji'];
			} else {
				$total['hk_potongan_gaji'] = $total['hk_potongan_gaji'] +  $dataHeader['hk_potongan_gaji'];
			}
			// if (empty($total['jht'])) {
			// 	$total['jht'] = $dataHeader['jht'];
			// } else {
			// 	$total['jht'] = $total['jht'] +  $dataHeader['jht'];
			// }
			// if (empty($total['jp'])) {
			// 	$total['jp'] = $dataHeader['jp'];
			// } else {
			// 	$total['jp'] = $total['jp'] +  $dataHeader['jp'];
			// }
			// if (empty($total['jkn'])) {
			// 	$total['jkn'] = $dataHeader['jkn'];
			// } else {
			// 	$total['jkn'] = $total['jkn'] +  $dataHeader['jkn'];
			// }
			// if (empty($total['jum_potongan'])) {
			// 	$total['jum_potongan'] = $jumPotongan;
			// } else {
			// 	$total['jum_potongan'] = $total['jum_potongan'] + $jumPotongan;
			// }

			// $total['jum_pendapatan'] = $total['jum_pendapatan'] -  $jumPotongan;
			// $sub_total['jum_pendapatan'] = $sub_total['jum_pendapatan'] -  $jumPotongan;
			// $sub_total_tipe_karyawan['jum_pendapatan'] = $sub_total_tipe_karyawan['jum_pendapatan'] -  $jumPotongan;

			$total_jkk_perusahaan = $total_jkk_perusahaan + $dataHeader['jkk_perusahaan'];
			$sub_total_jkk_perusahaan = $sub_total_jkk_perusahaan + $dataHeader['jkk_perusahaan'];
			$sub_total_tipe_karyawan_jkk_perusahaan = $sub_total_tipe_karyawan_jkk_perusahaan + $dataHeader['jkk_perusahaan'];

			$total_jkn_perusahaan = $total_jkn_perusahaan + $dataHeader['jkn_perusahaan'];
			$sub_total_jkn_perusahaan =  $sub_total_jkn_perusahaan + $dataHeader['jkn_perusahaan'];
			$sub_total_tipe_karyawan_jkn_perusahaan =  $sub_total_tipe_karyawan_jkn_perusahaan + $dataHeader['jkn_perusahaan'];

			$total_jkm_perusahaan =  $total_jkm_perusahaan + $dataHeader['jkm_perusahaan'];
			$sub_total_tipe_karyawan_jkm_perusahaan =  $sub_total_tipe_karyawan_jkm_perusahaan + $dataHeader['jkm_perusahaan'];
			$sub_total_jkm_perusahaan =  $sub_total_jkm_perusahaan + $dataHeader['jkm_perusahaan'];

			$total_jht = $total_jht + $dataHeader['jht'];
			$sub_total_jht = $sub_total_jht + $dataHeader['jht'];
			$sub_total_tipe_karyawan_jht = $sub_total_tipe_karyawan_jht + $dataHeader['jht'];

			$total_jp = $total_jp + $dataHeader['jp'];
			$sub_total_jp =  $sub_total_jp + $dataHeader['jp'];
			$sub_total_tipe_karyawan_jp =  $sub_total_tipe_karyawan_jp + $dataHeader['jp'];

			$total_jkn =  $total_jkn + $dataHeader['jkn'];
			$sub_total_tipe_karyawan_jkn =  $sub_total_tipe_karyawan_jkn + $dataHeader['jkn'];
			$sub_total_jkn =  $sub_total_jkn + $dataHeader['jkn'];


			$total_catu_beras = $total_catu_beras + $dataHeader['nilai_rp_catu'];
			$sub_total_tipe_karyawan_catu_beras = $sub_total_tipe_karyawan_catu_beras + $dataHeader['nilai_rp_catu'];
			$sub_total_catu_beras = $sub_total_catu_beras + $dataHeader['nilai_rp_catu'];

			$total_gaji_pph = $total_gaji_pph + $jumlah_gaji_pph;
			$sub_total_gaji_pph = $sub_total_gaji_pph + $jumlah_gaji_pph;
			$sub_total_tipe_karyawan_gaji_pph = $sub_total_tipe_karyawan_gaji_pph + $jumlah_gaji_pph;

			// $html = $html . " <tr><td>" . $no . "</td>
			// 			<td>" . $dataHeader['nama'] . "</td>
			// 			<td>" . $dataHeader['nip'] . "</td>
			//      <td>" . $dataHeader['jabatan'] . "</td>
			//      <td>" . $dataHeader['divisi'] . "</td>
			// 	 <td>" . $dataHeader['tipe_karyawan'] . "</td>
			// 	 <td style='text-align: right'>" . $dataHeader['jumlah_hari_masuk'] . "</td>";
			// $html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['gapok']) . "</td>";
			// $html = $html . $html_pendapatan;
			// $html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['premi']) . "</td>";
			// $html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['lembur']) . "</td>";
			// $html = $html . "<td  style='text-align: right'>" . $this->format_number_report($jumPendapatan) . "</td>";
			// $html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['denda']) . "</td>";

			// $html = $html . $html_potongan;
			// $html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['hk_potongan_gaji']) . "</td>";
			// $html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['jht']) . "</td>";
			// $html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['jp']) . "</td>";
			// $html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['jkn']) . "</td>";
			// $html = $html . "<td  style='text-align: right'>" . $this->format_number_report($jumPotongan) . "</td>";
			// $html = $html . "<td  style='text-align: right'>" . $this->format_number_report($jumPendapatan - $jumPotongan) . "</td>";
			// $html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['jkk_perusahaan']) . "</td>";
			// $html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['jkm_perusahaan']) . "</td>";
			// $html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['jkn_perusahaan']) . "</td>";
			// $html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['nilai_rp_catu']) . "</td>";
			// $html = $html . "<td  style='text-align: right'>" . $this->format_number_report($jumlah_gaji_pph) . "</td>";
			// $html = $html . "</tr>";
			$idx = $i + 1;
			$tipe_karyawan_next = $dataHd[$idx]['tipe_karyawan_id'];
			$sub_bagian_next = $dataHd[$idx]['sub_bagian_id'];
			if (!is_null($tipe_karyawan_next)) {
				if ($tipe_karyawan_next != $dataHeader['tipe_karyawan_id']) {
					$html = $html . "<tr><td>" . $dataHeader['divisi'] . "</td><td style='text-align: left'> " . $dataHeader['tipe_karyawan'] . "</td>";
					$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_tk) . "</td>";
					$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_gapok) . "</td>";
					foreach ($sub_total_tipe_karyawan as $key => $value) {
						$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($value) . "</td>";
					}

					$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_Pendapatan) . "</td>";
					$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_jkk_perusahaan) . "</td>";
					$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_jkm_perusahaan) . "</td>";
					$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_jkn_perusahaan) . "</td>";
					$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_jht) . "</td>";
					$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_jp) . "</td>";
					$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_catu_beras) . "</td>";
					$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_gaji_pph) . "</td>";
					$html = $html .	"</tr>";

					$sub_total_tipe_karyawan_hk = 0;
					$sub_total_tipe_karyawan_tk = 0;
					$sub_total_tipe_karyawan_gapok = 0;
					$sub_total_tipe_karyawan = array();
					$sub_total_tipe_karyawan_Pendapatan = 0;
					$sub_total_tipe_karyawan_Potongan = 0;
					$sub_total_tipe_karyawan_jkk_perusahaan = 0;
					$sub_total_tipe_karyawan_jkn_perusahaan = 0;
					$sub_total_tipe_karyawan_jkm_perusahaan = 0;
					$sub_total_tipe_karyawan_jht = 0;
					$sub_total_tipe_karyawan_jp = 0;
					$sub_total_tipe_karyawan_gaji_pph = 0;
					$sub_total_tipe_karyawan_catu_beras = 0;
				} else {
					if (!is_null($sub_bagian_next)) {
						if ($sub_bagian_next != $dataHeader['sub_bagian_id']) {
							$html = $html . "<tr><td>" . $dataHeader['divisi'] . "</td><td style='text-align: left'> " . $dataHeader['tipe_karyawan'] . "</td>";
							$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_tk) . "</td>";
							$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_gapok) . "</td>";
							foreach ($sub_total_tipe_karyawan as $key => $value) {
								$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($value) . "</td>";
							}

							$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_Pendapatan) . "</td>";
							$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_jkk_perusahaan) . "</td>";
							$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_jkm_perusahaan) . "</td>";
							$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_jkn_perusahaan) . "</td>";
							$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_jht) . "</td>";
							$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_jp) . "</td>";
							$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_catu_beras) . "</td>";
							$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_gaji_pph) . "</td>";
							$html = $html .	"</tr>";

							$sub_total_tipe_karyawan_hk = 0;
							$sub_total_tipe_karyawan_tk = 0;
							$sub_total_tipe_karyawan_gapok = 0;
							$sub_total_tipe_karyawan = array();
							$sub_total_tipe_karyawan_Pendapatan = 0;
							$sub_total_tipe_karyawan_Potongan = 0;
							$sub_total_tipe_karyawan_jkk_perusahaan = 0;
							$sub_total_tipe_karyawan_jkn_perusahaan = 0;
							$sub_total_tipe_karyawan_jkm_perusahaan = 0;
							$sub_total_tipe_karyawan_jht = 0;
							$sub_total_tipe_karyawan_jp = 0;
							$sub_total_tipe_karyawan_gaji_pph = 0;
							$sub_total_tipe_karyawan_catu_beras = 0;
						}
					} else {
					}
				}
			} else {
				$html = $html . "<tr><td>" . $dataHeader['divisi'] . "</td><td> " . $dataHeader['tipe_karyawan'] . "</td>";
				$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_tk) . "</td>";
				$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_gapok) . "</td>";

				foreach ($sub_total_tipe_karyawan as $key => $value) {
					$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($value) . "</td>";
				}

				$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_Pendapatan) . "</td>";
				$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_jkk_perusahaan) . "</td>";
				$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_jkm_perusahaan) . "</td>";
				$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_jkn_perusahaan) . "</td>";
				$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_jht) . "</td>";
				$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_jp) . "</td>";
				$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_catu_beras) . "</td>";
				$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_gaji_pph) . "</td>";
				$html = $html .	"</tr>";
			}


			if (!is_null($sub_bagian_next)) {
				if ($sub_bagian_next != $dataHeader['sub_bagian_id']) {
					$html = $html . "<tr><td  colspan='2' style='text-align: left'><b> " . $dataHeader['divisi'] . "</b></td>";
					$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_tk) . "</b></td>";
					$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_gapok) . "</b></td>";
					foreach ($sub_total as $key => $value) {
						$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($value) . "</b></td>";
					}

					$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_totalPendapatan) . "</b></td>";
					$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_jkk_perusahaan) . "</b></td>";
					$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_jkm_perusahaan) . "</b></td>";
					$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_jkn_perusahaan) . "</b></td>";
					$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_jht) . "</b></td>";
					$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_jp) . "</b></td>";
					$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_catu_beras) . "</b></td>";
					$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_gaji_pph) . "</b></td>";
					$html = $html .	"</tr>";

					$sub_total_hk = 0;
					$sub_total_tk = 0;
					$sub_total_gapok = 0;
					$sub_total = array();
					$sub_totalPendapatan = 0;
					$sub_totalPotongan = 0;
					$sub_total_jkk_perusahaan = 0;
					$sub_total_jkn_perusahaan = 0;
					$sub_total_jkm_perusahaan = 0;
					$sub_total_jht = 0;
					$sub_total_jp = 0;
					$sub_total_gaji_pph = 0;
					$sub_total_catu_beras = 0;

					$sub_total_tipe_karyawan_hk = 0;
					$sub_total_tipe_karyawan_gapok = 0;
					$sub_total_tipe_karyawan = array();
					$sub_total_tipe_karyawan_Pendapatan = 0;
					$sub_total_tipe_karyawan_Potongan = 0;
					$sub_total_tipe_karyawan_jkk_perusahaan = 0;
					$sub_total_tipe_karyawan_jkn_perusahaan = 0;
					$sub_total_tipe_karyawan_jkm_perusahaan = 0;
					$sub_total_tipe_karyawan_jht = 0;
					$sub_total_tipe_karyawan_jp = 0;
					$sub_total_tipe_karyawan_gaji_pph = 0;
					$sub_total_tipe_karyawan_catu_beras = 0;
				}
			} else {
				$html = $html . "<tr><td colspan='2'><b> " . $dataHeader['divisi'] . "</b></td>";
				$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_tk) . "</b></td>";
				$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_gapok) . "</b></td>";

				foreach ($sub_total as $key => $value) {
					$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($value) . "</b></td>";
				}

				$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_totalPendapatan) . "</b></td>";
				$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_jkk_perusahaan) . "</b></td>";
				$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_jkm_perusahaan) . "</b></td>";
				$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_jkn_perusahaan) . "</b></td>";
				$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_jht) . "</b></td>";
				$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_jp) . "</b></td>";
				$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_catu_beras) . "</b></td>";
				$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_gaji_pph) . "</b></td>";
				$html = $html .	"</tr>";
			}
		}
		// var_dump($total);exit();
		// total summary
		$html = $html . "<tr><td colspan='2' style='text-align: left' ><b>TOTAL</b></td>";
		$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($total_tk) . "</b></td>";
		$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($total_gapok) . "</b></td>";
		foreach ($total as $key => $value) {
			$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($value) . "</b></td>";
		}
		$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($totalPendapatan) . "</b></td>";
		$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($total_jkk_perusahaan) . "</b></td>";
		$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($total_jkm_perusahaan) . "</b></td>";
		$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($total_jkn_perusahaan) . "</b></td>";
		$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($total_jht) . "</b></td>";
		$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($total_jp) . "</b></td>";

		$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($total_catu_beras) . "</b></td>";
		$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($total_gaji_pph) . "</b></td>";
		$html = $html .
			"</tr>";
		//         <td style='text-align: right'>". $this->format_number_report( $jum)."</td>
		//         </tr>";
		$html = $html . " </table>
            <br>
            <table class='no_border' style='width:20%'>
            <tr><td></td><td>&nbsp;</td></tr>
            <tr><td></td><td>&nbsp;</td></tr>
            <tr><td></td><td>&nbsp;</td></tr>
            <tr><td></td><td>&nbsp;</td></tr>
            <tr><td></td><td>&nbsp;</td></tr>
            <tr><td width='400px'>&nbsp; </td><td width='400px'>(  ______________________  )
            </td></tr>
            <tr><td width='400px'>&nbsp; </td><td width='400px'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;HRD
            </td></tr>
            </table>
            ";

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A3', 'landscape');
		//echo $html;
		// $html=$html. var_dump($total);

		return $html;
	}
	function print_rekap_pph_by_unit_kerja_v2($id, $divisi_id, $status_id)
	{


		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		// 		$html = $html . '<div class="row">
		// <div class="span12">
		//     <br>
		//     <div class="kop-print">
		// 	<img src=data:image/png;base64,' . base64_encode(file_get_contents(get_logo_config())) . ' alt="image" >
		//         <div class="kop-nama">' . get_pengaturan('nama-company', 'value') . '</div>
		//         <div class="kop-info">Alamat : ' . get_pengaturan('alamat', 'value') . ', Telepon :' . get_pengaturan('telp', 'value') . '</div>
		//     </div>
		//     <hr class="kop-print-hr">
		// </div>
		// </div>';
		$periodeGaji = $this->HrmsPeriodeGajiModel->retrieve($id);
		if (empty($periodeGaji)) {
			$this->set_response(array("status" => "NOT OK", "data" => 'null'), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$countPendapatan = 0;
		$pendapatan = $this->db->query("select * from payroll_tipe_gaji where jenis=1 or jenis=2 or jenis=3 order by urut")->result_array();
		$potongan = $this->db->query("select * from payroll_tipe_gaji where jenis=0 order by urut")->result_array();
		$countPendapatan = count($pendapatan) + 3;
		$countPotongan = count($potongan) + 5;

		// $sql = "
		// SELECT a.*,c.nip,c.nama,d.nama as jabatan,e.nama as departemen ,f.nama as divisi, c.sub_bagian_id,
		// c.tipe_karyawan_id , g.nama as tipe_karyawan
		// FROM payroll_gaji_tr_hd a 
		//  left join payroll_karyawan_gaji b on a.karyawan_id=b.karyawan_id
        // inner join karyawan c on a.karyawan_id=c.id 
		//  left join payroll_jabatan d on c.jabatan_id=d.id
        //  left join payroll_department e on c.departemen_id=e.id
		// left join gbm_organisasi f on c.sub_bagian_id=f.id
		// left join payroll_tipe_karyawan g on g.id=c.tipe_karyawan_id 
        // where a.periode_gaji_id=" . $id . "";

		/*UPDATE: DIVISI/AFDELING NGMBIL DARI TABEL PAYROLL GAJI BUKAN DR TABLE MASTER KARYAWAN */
		$sql = "
		SELECT a.*,c.nip,c.nama,d.nama as jabatan,e.nama as departemen ,f.nama as divisi, a.divisi_id as sub_bagian_id,
		c.tipe_karyawan_id , g.nama as tipe_karyawan
		FROM payroll_gaji_tr_hd a 
		 left join payroll_karyawan_gaji b on a.karyawan_id=b.karyawan_id
        inner join karyawan c on a.karyawan_id=c.id 
		 left join payroll_jabatan d on c.jabatan_id=d.id
         left join payroll_department e on c.departemen_id=e.id
		left join gbm_organisasi f on a.divisi_id=f.id
		left join payroll_tipe_karyawan g on g.id=c.tipe_karyawan_id 
        where a.periode_gaji_id=" . $id . "";

		$nama_afd_stasiun = "Semua";
		$res_lokasi = $this->db->query("select * from gbm_organisasi where id=" . $periodeGaji['lokasi_id'] . "")->row_array();
		$nama_lokasi = $res_lokasi['nama'];

		if (($divisi_id)) {
			// $sql = $sql . " and c.sub_bagian_id=" . $divisi_id . "";
			$sql = $sql . " and a.divisi_id=" . $divisi_id . "";
			$res_afd_stasiun = $this->db->query("select * from  gbm_organisasi where id=" . $divisi_id . "")->row_array();
			$nama_afd_stasiun = $res_afd_stasiun['nama'];
		}
		if (($status_id)) {
			$sql = $sql . " and c.tipe_karyawan_id=" . $status_id . "";
		}

		$sql = $sql . " order by f.nama,g.nama;";
		$dataHd = $this->db->query($sql)->result_array();
		$html = get_header_report_v2();
		$html = $html . '
		<div class="row">
		<div class="span12">
			<br>
			<div class="kop-print">
			<div class="kop-nama">KLINIK ANNAJAH</div>
			<div class="kop-info"> Jl Sukarno Hatta No 12 No 12, Bandung Jawa Barat 14450</div>
			<div class="kop-info">Telp : (021) 6684055</div>
		</div>
			<hr style="width:2095px" class="kop-print-hr">
		</div>
		</div>
		<h3 class="title">LAPORAN REKAP PENGGAJIAN(PPH)  ' . $periodeGaji['nama'] . '</h3>
  <table class="no_border" style="width:30%">
  		
			<tr>
					<td>LOKASI</td>
					<td>:</td>
					<td>' . $nama_lokasi . '</td>
			</tr>
			<tr>	
					<td>PERIODE</td>
					<td>:</td>
					<td>' . $periodeGaji['nama'] . '</td>
			</tr>
			<tr>	
					<td>AFDELING/STATION</td>
					<td>:</td>
					<td>' . $nama_afd_stasiun . '</td>
			</tr>

					
	</table>
	<br>
  ';
		$html = $html . "
		 <table   border='1' width='100%' style='border-collapse: collapse;'>
		 <thead>
		 <tr>
           
             <th rowspan=2>Divisi</th>
			<th rowspan=2>Type Karyawan</th>
			<th rowspan=2>TK</th>
            <th colspan=" . $countPendapatan . "  style='text-align: center'>Pendapatan  </th>
			<th  rowspan=2 style='text-align: center'>Total Pendapatan  </th>
            <th  colspan=" . $countPotongan . "  style='text-align: center'>Potongan </th>
			<th  rowspan=2 style='text-align: center'>Total Potongan  </th>
            <th rowspan=2  style='text-align: center'>Gaji Bersih</th>
			<th colspan=3  style='text-align: center'>Penambah  </th>
			<th rowspan=2>Catu Beras</th>
			<th rowspan=2>Jumlah Gaji PPH</th>
        </tr>";

		$html = $html . "<tr>";

		$html = $html . "<th style='text-align: center'>" . 'Gaji Pokok' . "</th>";
		foreach ($pendapatan as $key => $value) {
			$html = $html . "<th style='text-align: center'>" . $value['nama'] . "</th>";
		}
		$html = $html . "<th style='text-align: center'>" . 'Premi' . "</th>";
		$html = $html . "<th style='text-align: center'>" . 'Lembur' . "</th>";
		$html = $html . "<th style='text-align: center'>" . 'Denda' . "</th>";
		foreach ($potongan as $key => $value) {
			$html = $html . "<th style='text-align: center'>" . $value['nama'] . "</th>";
		}
		$html = $html . "<th style='text-align: center'>" . 'Potongan HK' . "</th>";
		$html = $html . "<th style='text-align: center'>" . 'JHT' . "</th>";
		$html = $html . "<th style='text-align: center'>" . 'JP' . "</th>";
		$html = $html . "<th style='text-align: center'>" . 'JKN' . "</th>";
		$html = $html . "<th style='text-align: center'>" . 'JKK' . "</th>";
		$html = $html . "<th style='text-align: center'>" . 'JKM' . "</td>";
		$html = $html . "<th style='text-align: center'>" . 'KS' . "</th>";
		// $html = $html . "<td style='text-align: center'>" . 'PPH21' . "</td>";
		$html = $html . " </thead></tr>";
		// print_r($html);exit;


		$no = 0;
		$total = array();
		$sub_total = array();
		$totalPendapatan = 0;
		$totalPotongan = 0;
		$sub_totalPendapatan = 0;
		$sub_totalPotongan = 0;
		$total_hk = 0;
		$sub_total_hk = 0;
		$total_tk = 0;
		$sub_total_tk = 0;
		$sub_total_tipe_karyawan_tk = 0;

		$total_gapok = 0;
		$sub_total_gapok = 0;
		$total_jkk_perusahaan = 0;
		$sub_total_jkk_perusahaan = 0;
		$total_jkn_perusahaan = 0;
		$sub_total_jkn_perusahaan = 0;
		$total_jkm_perusahaan = 0;
		$sub_total_jkm_perusahaan = 0;
		$total_gaji_pph = 0;
		$sub_total_gaji_pph = 0;
		$total_catu_beras = 0;
		$sub_total_catu_beras = 0;


		$sub_total_tipe_karyawan_hk = 0;
		$sub_total_tipe_karyawan_gapok = 0;
		$sub_total_tipe_karyawan = array();
		$sub_total_tipe_karyawan_Pendapatan = 0;
		$sub_total_tipe_karyawan_Potongan = 0;
		$sub_total_tipe_karyawan_jkk_perusahaan = 0;
		$sub_total_tipe_karyawan_jkn_perusahaan = 0;
		$sub_total_tipe_karyawan_jkm_perusahaan = 0;
		$sub_total_tipe_karyawan_gaji_pph = 0;
		$sub_total_tipe_karyawan_catu_beras = 0;
		$countRow = count($dataHd);
		for ($i = 0; $i < $countRow; $i++) {
			$dataHeader = $dataHd[$i];
			$no++;
			$jumPendapatan = 0;
			$jumPotongan = 0;
			$total_hk = $total_hk +  $dataHeader['jumlah_hari_masuk'];
			$total_gapok = $total_gapok +  $dataHeader['gapok'];
			$sub_total_hk = $sub_total_hk +  $dataHeader['jumlah_hari_masuk'];
			$total_tk = $total_tk + 1;
			$sub_total_tk = $sub_total_tk + 1;
			$sub_total_tipe_karyawan_tk = $sub_total_tipe_karyawan_tk + 1;
			$sub_total_tipe_karyawan_hk = $sub_total_tipe_karyawan_hk +  $dataHeader['jumlah_hari_masuk'];
			$sub_total_gapok = $sub_total_gapok +  $dataHeader['gapok'];
			$sub_total_tipe_karyawan_gapok = $sub_total_tipe_karyawan_gapok +  $dataHeader['gapok'];
			// looping pendapatan
			$html_pendapatan = '';
			foreach ($pendapatan as $key => $value) {
				$dataPendapatan = $this->db->query("SELECT a.*,b.jenis,b.nama,b.urut FROM payroll_gaji_tr_dt a inner join payroll_tipe_gaji b
                    on a.tipe_gaji=b.id
                    where a.id_hd=" . $dataHeader['id'] . " and a.tipe_gaji=" . $value['id'] . ";")->row_array();
				if (empty($dataPendapatan)) {
					$html_pendapatan =  $html_pendapatan . "<td  style='text-align: right'>" . 0 . "</td>";
					if (empty($total[$value['nama']])) {
						$total[$value['nama']] = 0;
					} else {
					}
					if (empty($sub_total[$value['nama']])) {

						$sub_total[$value['nama']] = 0;
					} else {
					}
					if (empty($sub_total_tipe_karyawan[$value['nama']])) {

						$sub_total_tipe_karyawan[$value['nama']] = 0;
					} else {
					}
				} else {
					$html_pendapatan = $html_pendapatan . "<td  style='text-align: right'>" . $this->format_number_report($dataPendapatan['nilai']) . "</td>";
					$jumPendapatan = $jumPendapatan + $dataPendapatan['nilai'];
					$totalPendapatan = $totalPendapatan + $dataPendapatan['nilai'];
					if (empty($total[$value['nama']])) {
						$total[$value['nama']] = $dataPendapatan['nilai'];
					} else {
						$total[$value['nama']] = $total[$value['nama']] + $dataPendapatan['nilai'];
					}
					if (empty($sub_total[$value['nama']])) {

						$sub_total[$value['nama']] = $dataPendapatan['nilai'];
					} else {

						$sub_total[$value['nama']] = $sub_total[$value['nama']] + $dataPendapatan['nilai'];
					}
					if (empty($sub_total_tipe_karyawan[$value['nama']])) {

						$sub_total_tipe_karyawan[$value['nama']] = $dataPendapatan['nilai'];
					} else {

						$sub_total_tipe_karyawan[$value['nama']] = $sub_total_tipe_karyawan[$value['nama']] + $dataPendapatan['nilai'];
					}
				}
			}

			$jumPendapatan = $jumPendapatan + $dataHeader['gapok'] +  $dataHeader['lembur'] + $dataHeader['premi'];
			$totalPendapatan = $totalPendapatan + $dataHeader['gapok'] + $dataHeader['lembur'] + $dataHeader['premi'];
			$sub_totalPendapatan = $sub_totalPendapatan + $dataHeader['gapok'] + $dataHeader['lembur'] + $dataHeader['premi'];
			$sub_total_tipe_karyawan_Pendapatan = $sub_total_tipe_karyawan_Pendapatan + $dataHeader['gapok'] + $dataHeader['lembur'] + $dataHeader['premi'];
			if (empty($total['premi'])) {
				$total['premi'] = $dataHeader['premi'];
			} else {
				$total['premi'] = $total['premi'] +  $dataHeader['premi'];
			}
			if (empty($total['lembur'])) {
				$total['lembur'] = $dataHeader['lembur'];
			} else {
				$total['lembur'] = $total['lembur'] +  $dataHeader['lembur'];
			}
			if (empty($total['jum_pendapatan'])) {

				$total['jum_pendapatan'] = $jumPendapatan;
			} else {

				$total['jum_pendapatan'] = $total['jum_pendapatan'] +  $jumPendapatan;
			}
			if (empty($total['denda'])) {
				$total['denda'] = $dataHeader['denda'];
			} else {
				$total['denda'] = $total['denda'] +  $dataHeader['denda'];
			}

			if (empty($sub_total['premi'])) {

				$sub_total['premi'] = $dataHeader['premi'];
			} else {

				$sub_total['premi'] = $sub_total['premi'] +  $dataHeader['premi'];
			}
			if (empty($sub_total['lembur'])) {

				$sub_total['lembur'] = $dataHeader['lembur'];
			} else {

				$sub_total['lembur'] = $sub_total['lembur'] +  $dataHeader['lembur'];
			}

			if (empty($sub_total['jum_pendapatan'])) {

				$sub_total['jum_pendapatan'] = $jumPendapatan;
			} else {

				$sub_total['jum_pendapatan'] = $sub_total['jum_pendapatan'] +  $jumPendapatan;
			}
			if (empty($sub_total['denda'])) {

				$sub_total['denda'] = $dataHeader['denda'];
			} else {

				$sub_total['denda'] = $sub_total['denda'] +  $dataHeader['denda'];
			}

			// sub total by tipe karyawan //
			if (empty($sub_total_tipe_karyawan['premi'])) {

				$sub_total_tipe_karyawan['premi'] = $dataHeader['premi'];
			} else {

				$sub_total_tipe_karyawan['premi'] = $sub_total_tipe_karyawan['premi'] +  $dataHeader['premi'];
			}
			if (empty($sub_total_tipe_karyawan['lembur'])) {

				$sub_total_tipe_karyawan['lembur'] = $dataHeader['lembur'];
			} else {

				$sub_total_tipe_karyawan['lembur'] = $sub_total_tipe_karyawan['lembur'] +  $dataHeader['lembur'];
			}

			if (empty($sub_total_tipe_karyawan['jum_pendapatan'])) {

				$sub_total_tipe_karyawan['jum_pendapatan'] = $jumPendapatan;
			} else {

				$sub_total_tipe_karyawan['jum_pendapatan'] = $sub_total_tipe_karyawan['jum_pendapatan'] +  $jumPendapatan;
			}
			if (empty($sub_total_tipe_karyawan['denda'])) {

				$sub_total_tipe_karyawan['denda'] = $dataHeader['denda'];
			} else {

				$sub_total_tipe_karyawan['denda'] = $sub_total_tipe_karyawan['denda'] +  $dataHeader['denda'];
			}

			// looping potongan
			$html_potongan = '';
			foreach ($potongan as $key => $value) {
				$dataPotongan = $this->db->query("SELECT a.*,b.jenis,b.nama,b.urut FROM payroll_gaji_tr_dt a inner join payroll_tipe_gaji b
                    on a.tipe_gaji=b.id
                    where a.id_hd=" . $dataHeader['id'] . " and a.tipe_gaji=" . $value['id'] . ";")->row_array();
				if (empty($dataPotongan)) {
					$html_potongan = $html_potongan . "<td  style='text-align: right'>" . 0 . "</td>";
					$total[$value['nama']] = 0;
					$sub_total[$value['nama']] = 0;
					$sub_total_tipe_karyawan[$value['nama']] = 0;
				} else {
					$html_potongan = $html_potongan . "<td  style='text-align: right'>" . $this->format_number_report($dataPotongan['nilai']) . "</td>";
					$jumPotongan = $jumPotongan + $dataPotongan['nilai'];
					$totalPotongan = $totalPotongan + $dataPotongan['nilai'];
					if (empty($total[$value['nama']])) {
						$total[$value['nama']] = $dataPotongan['nilai'];
					} else {
						$total[$value['nama']] = $total[$value['nama']] + $dataPotongan['nilai'];
					}
					if (empty($sub_total[$value['nama']])) {

						$sub_total[$value['nama']] = $dataPotongan['nilai'];
					} else {

						$sub_total[$value['nama']] = $sub_total[$value['nama']] + $dataPotongan['nilai'];
					}
					if (empty($sub_total_tipe_karyawan[$value['nama']])) {

						$sub_total_tipe_karyawan[$value['nama']] = $dataPotongan['nilai'];
					} else {

						$sub_total_tipe_karyawan[$value['nama']] = $sub_total_tipe_karyawan[$value['nama']] + $dataPotongan['nilai'];
					}
				}
			}


			$jumPotongan = $jumPotongan + $dataHeader['hk_potongan_gaji'] + $dataHeader['jht'] +  $dataHeader['jp'] + $dataHeader['jkn'] + $dataHeader['denda'];
			$jumlah_gaji_pph = $jumPendapatan + $dataHeader['jkk_perusahaan'] + $dataHeader['jkn_perusahaan'] + $dataHeader['jkm_perusahaan'] + $dataHeader['nilai_rp_catu'] - $jumPotongan;
			$totalPotongan = $totalPotongan + $dataHeader['hk_potongan_gaji'] + $dataHeader['jht'] +  $dataHeader['jp'] + $dataHeader['jkn'] + $dataHeader['denda'];
			$sub_totalPotongan = $sub_totalPotongan + $dataHeader['hk_potongan_gaji'] + $dataHeader['jht'] +  $dataHeader['jp'] + $dataHeader['jkn'] + $dataHeader['denda'];
			$sub_total_tipe_karyawan_Potongan = $sub_total_tipe_karyawan_Potongan + $dataHeader['hk_potongan_gaji'] + $dataHeader['jht'] +  $dataHeader['jp'] + $dataHeader['jkn'] + $dataHeader['denda'];

			if (empty($sub_total['hk_potongan_gaji'])) {
				$sub_total['hk_potongan_gaji'] = $dataHeader['hk_potongan_gaji'];
			} else {
				$sub_total['hk_potongan_gaji'] = $sub_total['hk_potongan_gaji'] +  $dataHeader['hk_potongan_gaji'];
			}
			if (empty($sub_total['jht'])) {
				$sub_total['jht'] = $dataHeader['jht'];
			} else {
				$sub_total['jht'] = $sub_total['jht'] +  $dataHeader['jht'];
			}
			if (empty($sub_total['jp'])) {
				$sub_total['jp'] = $dataHeader['jp'];
			} else {
				$sub_total['jp'] = $sub_total['jp'] +  $dataHeader['jp'];
			}
			if (empty($sub_total['jkn'])) {
				$sub_total['jkn'] = $dataHeader['jkn'];
			} else {
				$sub_total['jkn'] = $sub_total['jkn'] +  $dataHeader['jkn'];
			}
			if (empty($sub_total['jum_potongan'])) {
				$sub_total['jum_potongan'] = $jumPotongan;
			} else {
				$sub_total['jum_potongan'] = $sub_total['jum_potongan'] +  $jumPotongan;
			}
			// Subtotal tipe karyawan
			if (empty($sub_total_tipe_karyawan['hk_potongan_gaji'])) {
				$sub_total_tipe_karyawan['hk_potongan_gaji'] = $dataHeader['hk_potongan_gaji'];
			} else {
				$sub_total_tipe_karyawan['hk_potongan_gaji'] = $sub_total_tipe_karyawan['hk_potongan_gaji'] +  $dataHeader['hk_potongan_gaji'];
			}
			if (empty($sub_total_tipe_karyawan['jht'])) {
				$sub_total_tipe_karyawan['jht'] = $dataHeader['jht'];
			} else {
				$sub_total_tipe_karyawan['jht'] = $sub_total_tipe_karyawan['jht'] +  $dataHeader['jht'];
			}
			if (empty($sub_total_tipe_karyawan['jp'])) {
				$sub_total_tipe_karyawan['jp'] = $dataHeader['jp'];
			} else {
				$sub_total_tipe_karyawan['jp'] = $sub_total_tipe_karyawan['jp'] +  $dataHeader['jp'];
			}
			if (empty($sub_total_tipe_karyawan['jkn'])) {
				$sub_total_tipe_karyawan['jkn'] = $dataHeader['jkn'];
			} else {
				$sub_total_tipe_karyawan['jkn'] = $sub_total_tipe_karyawan['jkn'] +  $dataHeader['jkn'];
			}
			if (empty($sub_total_tipe_karyawan['jum_potongan'])) {
				$sub_total_tipe_karyawan['jum_potongan'] = $jumPotongan;
			} else {
				$sub_total_tipe_karyawan['jum_potongan'] = $sub_total_tipe_karyawan['jum_potongan'] +  $jumPotongan;
			}

			if (empty($total['hk_potongan_gaji'])) {
				$total['hk_potongan_gaji'] = $dataHeader['hk_potongan_gaji'];
			} else {
				$total['hk_potongan_gaji'] = $total['hk_potongan_gaji'] +  $dataHeader['hk_potongan_gaji'];
			}
			if (empty($total['jht'])) {
				$total['jht'] = $dataHeader['jht'];
			} else {
				$total['jht'] = $total['jht'] +  $dataHeader['jht'];
			}
			if (empty($total['jp'])) {
				$total['jp'] = $dataHeader['jp'];
			} else {
				$total['jp'] = $total['jp'] +  $dataHeader['jp'];
			}
			if (empty($total['jkn'])) {
				$total['jkn'] = $dataHeader['jkn'];
			} else {
				$total['jkn'] = $total['jkn'] +  $dataHeader['jkn'];
			}
			if (empty($total['jum_potongan'])) {
				$total['jum_potongan'] = $jumPotongan;
			} else {
				$total['jum_potongan'] = $total['jum_potongan'] + $jumPotongan;
			}
			$total_jkk_perusahaan = $total_jkk_perusahaan + $dataHeader['jkk_perusahaan'];
			$sub_total_jkk_perusahaan = $sub_total_jkk_perusahaan + $dataHeader['jkk_perusahaan'];
			$sub_total_tipe_karyawan_jkk_perusahaan = $sub_total_tipe_karyawan_jkk_perusahaan + $dataHeader['jkk_perusahaan'];

			$total_jkn_perusahaan = $total_jkn_perusahaan + $dataHeader['jkn_perusahaan'];
			$sub_total_jkn_perusahaan =  $sub_total_jkn_perusahaan + $dataHeader['jkn_perusahaan'];
			$sub_total_tipe_karyawan_jkn_perusahaan =  $sub_total_tipe_karyawan_jkn_perusahaan + $dataHeader['jkn_perusahaan'];

			$total_jkm_perusahaan =  $total_jkm_perusahaan + $dataHeader['jkm_perusahaan'];
			$sub_total_tipe_karyawan_jkm_perusahaan =  $sub_total_tipe_karyawan_jkm_perusahaan + $dataHeader['jkm_perusahaan'];
			$sub_total_jkm_perusahaan =  $sub_total_jkm_perusahaan + $dataHeader['jkm_perusahaan'];

			$total_catu_beras = $total_catu_beras + $dataHeader['nilai_rp_catu'];
			$sub_total_tipe_karyawan_catu_beras = $sub_total_tipe_karyawan_catu_beras + $dataHeader['nilai_rp_catu'];
			$sub_total_catu_beras = $sub_total_catu_beras + $dataHeader['nilai_rp_catu'];

			$total_gaji_pph = $total_gaji_pph + $jumlah_gaji_pph;
			$sub_total_gaji_pph = $sub_total_gaji_pph + $jumlah_gaji_pph;
			$sub_total_tipe_karyawan_gaji_pph = $sub_total_tipe_karyawan_gaji_pph + $jumlah_gaji_pph;

			// $html = $html . " <tr><td>" . $no . "</td>
			// 			<td>" . $dataHeader['nama'] . "</td>
			// 			<td>" . $dataHeader['nip'] . "</td>
			//      <td>" . $dataHeader['jabatan'] . "</td>
			//      <td>" . $dataHeader['divisi'] . "</td>
			// 	 <td>" . $dataHeader['tipe_karyawan'] . "</td>
			// 	 <td style='text-align: right'>" . $dataHeader['jumlah_hari_masuk'] . "</td>";
			// $html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['gapok']) . "</td>";
			// $html = $html . $html_pendapatan;
			// $html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['premi']) . "</td>";
			// $html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['lembur']) . "</td>";
			// $html = $html . "<td  style='text-align: right'>" . $this->format_number_report($jumPendapatan) . "</td>";
			// $html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['denda']) . "</td>";

			// $html = $html . $html_potongan;
			// $html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['hk_potongan_gaji']) . "</td>";
			// $html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['jht']) . "</td>";
			// $html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['jp']) . "</td>";
			// $html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['jkn']) . "</td>";
			// $html = $html . "<td  style='text-align: right'>" . $this->format_number_report($jumPotongan) . "</td>";
			// $html = $html . "<td  style='text-align: right'>" . $this->format_number_report($jumPendapatan - $jumPotongan) . "</td>";
			// $html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['jkk_perusahaan']) . "</td>";
			// $html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['jkm_perusahaan']) . "</td>";
			// $html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['jkn_perusahaan']) . "</td>";
			// $html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['nilai_rp_catu']) . "</td>";
			// $html = $html . "<td  style='text-align: right'>" . $this->format_number_report($jumlah_gaji_pph) . "</td>";
			// $html = $html . "</tr>";
			$idx = $i + 1;
			$tipe_karyawan_next = $dataHd[$idx]['tipe_karyawan_id'];
			$sub_bagian_next = $dataHd[$idx]['sub_bagian_id'];
			if (!is_null($tipe_karyawan_next)) {
				if ($tipe_karyawan_next != $dataHeader['tipe_karyawan_id']) {
					$html = $html . "<tr><td>" . $dataHeader['divisi'] . "</td><td style='text-align: left'> " . $dataHeader['tipe_karyawan'] . "</td>";
					$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_tk) . "</td>";
					$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_gapok) . "</td>";
					foreach ($sub_total_tipe_karyawan as $key => $value) {
						$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($value) . "</td>";
					}

					$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_Pendapatan - $sub_total_tipe_karyawan_Potongan) . "</td>";
					$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_jkk_perusahaan) . "</td>";
					$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_jkm_perusahaan) . "</td>";
					$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_jkn_perusahaan) . "</td>";
					$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_catu_beras) . "</td>";
					$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_gaji_pph) . "</td>";
					$html = $html .	"</tr>";

					$sub_total_tipe_karyawan_hk = 0;
					$sub_total_tipe_karyawan_tk = 0;
					$sub_total_tipe_karyawan_gapok = 0;
					$sub_total_tipe_karyawan = array();
					$sub_total_tipe_karyawan_Pendapatan = 0;
					$sub_total_tipe_karyawan_Potongan = 0;
					$sub_total_tipe_karyawan_jkk_perusahaan = 0;
					$sub_total_tipe_karyawan_jkn_perusahaan = 0;
					$sub_total_tipe_karyawan_jkm_perusahaan = 0;
					$sub_total_tipe_karyawan_gaji_pph = 0;
					$sub_total_tipe_karyawan_catu_beras = 0;
				} else {
					if (!is_null($sub_bagian_next)) {
						if ($sub_bagian_next != $dataHeader['sub_bagian_id']) {
							$html = $html . "<tr><td>" . $dataHeader['divisi'] . "</td><td style='text-align: left'> " . $dataHeader['tipe_karyawan'] . "</td>";
							$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_tk) . "</td>";
							$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_gapok) . "</td>";
							foreach ($sub_total_tipe_karyawan as $key => $value) {
								$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($value) . "</td>";
							}

							$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_Pendapatan - $sub_total_tipe_karyawan_Potongan) . "</td>";
							$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_jkk_perusahaan) . "</td>";
							$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_jkm_perusahaan) . "</td>";
							$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_jkn_perusahaan) . "</td>";
							$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_catu_beras) . "</td>";
							$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_gaji_pph) . "</td>";
							$html = $html .	"</tr>";

							$sub_total_tipe_karyawan_hk = 0;
							$sub_total_tipe_karyawan_tk = 0;
							$sub_total_tipe_karyawan_gapok = 0;
							$sub_total_tipe_karyawan = array();
							$sub_total_tipe_karyawan_Pendapatan = 0;
							$sub_total_tipe_karyawan_Potongan = 0;
							$sub_total_tipe_karyawan_jkk_perusahaan = 0;
							$sub_total_tipe_karyawan_jkn_perusahaan = 0;
							$sub_total_tipe_karyawan_jkm_perusahaan = 0;
							$sub_total_tipe_karyawan_gaji_pph = 0;
							$sub_total_tipe_karyawan_catu_beras = 0;
						}
					} else {
					}
				}
			} else {
				$html = $html . "<tr><td>" . $dataHeader['divisi'] . "</td><td> " . $dataHeader['tipe_karyawan'] . "</td>";
				$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_tk) . "</td>";
				$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_gapok) . "</td>";

				foreach ($sub_total_tipe_karyawan as $key => $value) {
					$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($value) . "</td>";
				}

				$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_Pendapatan - $sub_total_tipe_karyawan_Potongan) . "</td>";
				$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_jkk_perusahaan) . "</td>";
				$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_jkm_perusahaan) . "</td>";
				$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_jkn_perusahaan) . "</td>";
				$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_catu_beras) . "</td>";
				$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($sub_total_tipe_karyawan_gaji_pph) . "</td>";
				$html = $html .	"</tr>";
			}


			if (!is_null($sub_bagian_next)) {
				if ($sub_bagian_next != $dataHeader['sub_bagian_id']) {
					$html = $html . "<tr><td  colspan='2' style='text-align: left'><b> " . $dataHeader['divisi'] . "</b></td>";
					$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_tk) . "</b></td>";
					$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_gapok) . "</b></td>";
					foreach ($sub_total as $key => $value) {
						$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($value) . "</b></td>";
					}

					$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_totalPendapatan - $sub_totalPotongan) . "</b></td>";
					$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_jkk_perusahaan) . "</b></td>";
					$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_jkm_perusahaan) . "</b></td>";
					$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_jkn_perusahaan) . "</b></td>";
					$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_catu_beras) . "</b></td>";
					$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_gaji_pph) . "</b></td>";
					$html = $html .	"</tr>";

					$sub_total_hk = 0;
					$sub_total_tk = 0;
					$sub_total_gapok = 0;
					$sub_total = array();
					$sub_totalPendapatan = 0;
					$sub_totalPotongan = 0;
					$sub_total_jkk_perusahaan = 0;
					$sub_total_jkn_perusahaan = 0;
					$sub_total_jkm_perusahaan = 0;
					$sub_total_gaji_pph = 0;
					$sub_total_catu_beras = 0;

					$sub_total_tipe_karyawan_hk = 0;
					$sub_total_tipe_karyawan_gapok = 0;
					$sub_total_tipe_karyawan = array();
					$sub_total_tipe_karyawan_Pendapatan = 0;
					$sub_total_tipe_karyawan_Potongan = 0;
					$sub_total_tipe_karyawan_jkk_perusahaan = 0;
					$sub_total_tipe_karyawan_jkn_perusahaan = 0;
					$sub_total_tipe_karyawan_jkm_perusahaan = 0;
					$sub_total_tipe_karyawan_gaji_pph = 0;
					$sub_total_tipe_karyawan_catu_beras = 0;
				}
			} else {
				$html = $html . "<tr><td colspan='2'><b> " . $dataHeader['divisi'] . "</b></td>";
				$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_tk) . "</b></td>";
				$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_gapok) . "</b></td>";

				foreach ($sub_total as $key => $value) {
					$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($value) . "</b></td>";
				}

				$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_totalPendapatan - $sub_totalPotongan) . "</b></td>";
				$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_jkk_perusahaan) . "</b></td>";
				$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_jkm_perusahaan) . "</b></td>";
				$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_jkn_perusahaan) . "</b></td>";
				$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_catu_beras) . "</b></td>";
				$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_gaji_pph) . "</b></td>";
				$html = $html .	"</tr>";
			}
		}
		// var_dump($total);exit();
		// total summary
		$html = $html . "<tr><td colspan='2' style='text-align: left' ><b>TOTAL</b></td>";
		$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($total_tk) . "</b></td>";
		$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($total_gapok) . "</b></td>";
		foreach ($total as $key => $value) {
			$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($value) . "</b></td>";
		}
		// $html = $html . "<td  style='text-align: right'>" . $this->format_number_report($total['premi']) . "</td>";
		// $html = $html . "<td  style='text-align: right'>" . $this->format_number_report($total['lembur']) . "</td>";

		$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($totalPendapatan - $totalPotongan) . "</b></td>";
		$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($total_jkk_perusahaan) . "</b></td>";
		$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($total_jkm_perusahaan) . "</b></td>";
		$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($total_jkn_perusahaan) . "</b></td>";
		$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($total_catu_beras) . "</b></td>";
		$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($total_gaji_pph) . "</b></td>";
		$html = $html .
			"</tr>";
		//         <td style='text-align: right'>". $this->format_number_report( $jum)."</td>
		//         </tr>";
		$html = $html . " </table>
            <br>
            <table class='no_border' style='width:20%'>
            <tr><td></td><td>&nbsp;</td></tr>
            <tr><td></td><td>&nbsp;</td></tr>
            <tr><td></td><td>&nbsp;</td></tr>
            <tr><td></td><td>&nbsp;</td></tr>
            <tr><td></td><td>&nbsp;</td></tr>
            <tr><td width='400px'>&nbsp; </td><td width='400px'>(  ______________________  )
            </td></tr>
            <tr><td width='400px'>&nbsp; </td><td width='400px'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;HRD
            </td></tr>
            </table>
            ";

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A3', 'landscape');
		//echo $html;
		// $html=$html. var_dump($total);

		return $html;
	}
	function print_rekap_pph_by_tipe_karyawan($id, $divisi_id, $status_id)
	{


		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');



		// 		$html = $html . '<div class="row">
		// <div class="span12">
		//     <br>
		//     <div class="kop-print">
		// 	<img src=data:image/png;base64,' . base64_encode(file_get_contents(get_logo_config())) . ' alt="image" >
		//         <div class="kop-nama">' . get_pengaturan('nama-company', 'value') . '</div>
		//         <div class="kop-info">Alamat : ' . get_pengaturan('alamat', 'value') . ', Telepon :' . get_pengaturan('telp', 'value') . '</div>
		//     </div>
		//     <hr class="kop-print-hr">
		// </div>
		// </div>';
		$periodeGaji = $this->HrmsPeriodeGajiModel->retrieve($id);
		if (empty($periodeGaji)) {
			$this->set_response(array("status" => "NOT OK", "data" => 'null'), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$countPendapatan = 0;
		$pendapatan = $this->db->query("select * from payroll_tipe_gaji where jenis=1 or jenis=2 or jenis=3 order by urut")->result_array();
		$potongan = $this->db->query("select * from payroll_tipe_gaji where jenis=0 order by urut")->result_array();
		$countPendapatan = count($pendapatan) + 3;
		$countPotongan = count($potongan) + 5;

		// $sql = "
		// SELECT a.*,c.nip,c.nama,d.nama as jabatan,e.nama as departemen ,f.nama as divisi, c.sub_bagian_id,
		// c.tipe_karyawan_id , g.nama as tipe_karyawan
		// FROM payroll_gaji_tr_hd a 
		//  left join payroll_karyawan_gaji b on a.karyawan_id=b.karyawan_id
        // inner join karyawan c on a.karyawan_id=c.id 
		//  left join payroll_jabatan d on c.jabatan_id=d.id
        //  left join payroll_department e on c.departemen_id=e.id
		// left join gbm_organisasi f on c.sub_bagian_id=f.id
		// left join payroll_tipe_karyawan g on g.id=c.tipe_karyawan_id 
        // where a.periode_gaji_id=" . $id . "";
		
				/*UPDATE: DIVISI/AFDELING NGMBIL DARI TABEL PAYROLL GAJI BUKAN DR TABLE MASTER KARYAWAN */
				$sql = "
				SELECT a.*,c.nip,c.nama,d.nama as jabatan,e.nama as departemen ,f.nama as divisi, a.divisi_id as sub_bagian_id,
				c.tipe_karyawan_id , g.nama as tipe_karyawan
				FROM payroll_gaji_tr_hd a 
				 left join payroll_karyawan_gaji b on a.karyawan_id=b.karyawan_id
				inner join karyawan c on a.karyawan_id=c.id 
				 left join payroll_jabatan d on c.jabatan_id=d.id
				 left join payroll_department e on c.departemen_id=e.id
				left join gbm_organisasi f on a.divisi_id=f.id
				left join payroll_tipe_karyawan g on g.id=c.tipe_karyawan_id 
				where a.periode_gaji_id=" . $id . "";
		$nama_afd_stasiun = "Semua";
		$res_lokasi = $this->db->query("select * from gbm_organisasi where id=" . $periodeGaji['lokasi_id'] . "")->row_array();
		$nama_lokasi = $res_lokasi['nama'];

		if (($divisi_id)) {
			// $sql = $sql . " and c.sub_bagian_id=" . $divisi_id . "";
			$sql = $sql . " and a.divisi_id=" . $divisi_id . "";
			$res_afd_stasiun = $this->db->query("select * from  gbm_organisasi where id=" . $divisi_id . "")->row_array();
			$nama_afd_stasiun = $res_afd_stasiun['nama'];
		}
		if (($status_id)) {
			$sql = $sql . " and c.tipe_karyawan_id=" . $status_id . "";
		}
		$sql = $sql . " order by g.nama,c.nama;";
		$dataHd = $this->db->query($sql)->result_array();
		$html = get_header_report_v2();

		$html = $html . '
		<div class="row">
		<div class="span12">
			<br>
			<div class="kop-print">
			<div class="kop-nama">KLINIK ANNAJAH</div>
			<div class="kop-info"> Jl Sukarno Hatta No 12 No 12, Bandung Jawa Barat 14450</div>
			<div class="kop-info">Telp : (021) 6684055</div>
		</div>
			<hr style="width:2055px" class="kop-print-hr">
		</div>
		</div>
		<h3 class="title">LAPORAN REKAP PENGGAJIAN BY KATEGORI</h3>
  <table class="no_border" style="width:30%">
			
			<tr>
					<td>LOKASI</td>
					<td>:</td>
					<td>' . $nama_lokasi . '</td>
			</tr>
			<tr>	
					<td>PERIODE</td>
					<td>:</td>
					<td>' . $periodeGaji['nama'] . '</td>
			</tr>
			<tr>	
					<td>AFDELING/STATION</td>
					<td>:</td>
					<td>' . $nama_afd_stasiun . '</td>
			</tr>

			
	</table>
	<br>
  ';
		$html = $html . "
		 <table   border='1' width='100%' style='border-collapse: collapse;'>
		 <thead>
		 <tr>
            <th rowspan=2 >No</th>
            <th rowspan=2>Nama</th>
			<th rowspan=2>NIK</th>
            <th rowspan=2>Jabatan</th>
            <th rowspan=2>Divisi</th>
			<th rowspan=2>Status</th>
			<th rowspan=2>TK</td>
            <th colspan=" . $countPendapatan . "  style='text-align: center'>Pendapatan (Rp)</th>
            <th  rowspan=2 style='text-align: center'>Total Pendapatan (Rp) </th>
			<th  colspan=" . $countPotongan . "  style='text-align: center'>Potongan (Rp)</th>
            <th  rowspan=2 style='text-align: center'>Total Potongan (Rp) </th>
			<th rowspan=2  style='text-align: center'>Gaji Bersih (Rp)</th>
			<th colspan=3  style='text-align: center'>Penambah (Rp) </th>
			<th rowspan=2>Catu Beras (Rp)</th>
			<th rowspan=2>Jumlah Gaji PPH (Rp)</th>
        </tr>
		";

		$html = $html . "<tr>";

		$html = $html . "<th style='text-align: center'>" . 'Gaji Pokok' . "</td>";
		foreach ($pendapatan as $key => $value) {
			$html = $html . "<th style='text-align: center'>" . $value['nama'] . "</th>";
		}
		$html = $html . "<th style='text-align: center'>" . 'Premi' . "</th>";
		$html = $html . "<th style='text-align: center'>" . 'Lembur' . "</th>";
		$html = $html . "<th style='text-align: center'>" . 'Denda' . "</th>";
		foreach ($potongan as $key => $value) {
			$html = $html . "<th style='text-align: center'>" . $value['nama'] . "</th>";
		}
		$html = $html . "<th style='text-align: center'>" . 'Potongan HK' . "</th>";
		$html = $html . "<th style='text-align: center'>" . 'JHT' . "</th>";
		$html = $html . "<th style='text-align: center'>" . 'JP' . "</th>";
		$html = $html . "<th style='text-align: center'>" . 'JKN' . "</th>";
		$html = $html . "<th style='text-align: center'>" . 'JKK' . "</th>";
		$html = $html . "<th style='text-align: center'>" . 'JKM' . "</th>";
		$html = $html . "<th style='text-align: center'>" . 'KS' . "</th>";
		// $html = $html . "<td style='text-align: center'>" . 'PPH21' . "</td>";
		$html = $html . "</tr></thead>";
		// print_r($html);exit;


		$no = 0;
		$total = array();
		$sub_total = array();
		$totalPendapatan = 0;
		$totalPotongan = 0;
		$sub_totalPendapatan = 0;
		$sub_totalPotongan = 0;
		$total_hk = 0;
		$sub_total_hk = 0;
		$total_gapok = 0;
		$sub_total_gapok = 0;
		$total_jkk_perusahaan = 0;
		$sub_total_jkk_perusahaan = 0;
		$total_jkn_perusahaan = 0;
		$sub_total_jkn_perusahaan = 0;
		$total_jkm_perusahaan = 0;
		$sub_total_jkm_perusahaan = 0;
		$total_gaji_pph = 0;
		$sub_total_gaji_pph = 0;
		$total_catu_beras = 0;
		$sub_total_catu_beras = 0;
		$countRow = count($dataHd);
		for ($i = 0; $i < $countRow; $i++) {
			$dataHeader = $dataHd[$i];
			$no++;
			$jumPendapatan = 0;
			$jumPotongan = 0;
			$total_hk = $total_hk +  $dataHeader['jumlah_hari_masuk'];
			$total_gapok = $total_gapok +  $dataHeader['gapok'];
			$sub_total_hk = $sub_total_hk +  $dataHeader['jumlah_hari_masuk'];
			$sub_total_gapok = $sub_total_gapok +  $dataHeader['gapok'];


			// looping pendapatan
			$html_pendapatan = '';
			foreach ($pendapatan as $key => $value) {
				$dataPendapatan = $this->db->query("SELECT a.*,b.jenis,b.nama,b.urut FROM payroll_gaji_tr_dt a inner join payroll_tipe_gaji b
                    on a.tipe_gaji=b.id
                    where a.id_hd=" . $dataHeader['id'] . " and a.tipe_gaji=" . $value['id'] . ";")->row_array();
				if (empty($dataPendapatan)) {
					$html_pendapatan =  $html_pendapatan . "<td  style='text-align: right'>" . 0 . "</td>";
					if (empty($total[$value['nama']])) {
						$total[$value['nama']] = 0;
					} else {
						//$total[$value['nama']] = $total[$value['nama']] ;
					}
					if (empty($sub_total[$value['nama']])) {

						$sub_total[$value['nama']] = 0;
					} else {

						//$sub_total[$value['nama']] = $sub_total[$value['nama']] ;
					}
				} else {
					$html_pendapatan = $html_pendapatan . "<td  style='text-align: right'>" . $this->format_number_report($dataPendapatan['nilai']) . "</td>";
					$jumPendapatan = $jumPendapatan + $dataPendapatan['nilai'];
					$totalPendapatan = $totalPendapatan + $dataPendapatan['nilai'];
					if (empty($total[$value['nama']])) {
						$total[$value['nama']] = $dataPendapatan['nilai'];
					} else {
						$total[$value['nama']] = $total[$value['nama']] + $dataPendapatan['nilai'];
					}
					if (empty($sub_total[$value['nama']])) {

						$sub_total[$value['nama']] = $dataPendapatan['nilai'];
					} else {

						$sub_total[$value['nama']] = $sub_total[$value['nama']] + $dataPendapatan['nilai'];
					}
				}
			}

			$jumPendapatan = $jumPendapatan + $dataHeader['gapok'] +  $dataHeader['lembur'] + $dataHeader['premi'];
			$totalPendapatan = $totalPendapatan + $dataHeader['gapok'] + $dataHeader['lembur'] + $dataHeader['premi'];
			$sub_totalPendapatan = $sub_totalPendapatan + $dataHeader['gapok'] + $dataHeader['lembur'] + $dataHeader['premi'];

			if (empty($total['premi'])) {
				$total['premi'] = $dataHeader['premi'];
			} else {
				$total['premi'] = $total['premi'] +  $dataHeader['premi'];
			}
			if (empty($total['lembur'])) {
				$total['lembur'] = $dataHeader['lembur'];
			} else {
				$total['lembur'] = $total['lembur'] +  $dataHeader['lembur'];
			}
			if (empty($total['jum_pendapatan'])) {

				$total['jum_pendapatan'] = $jumPendapatan;
			} else {

				$total['jum_pendapatan'] = $total['jum_pendapatan'] +  $jumPendapatan;
			}
			if (empty($total['denda'])) {
				$total['denda'] = $dataHeader['denda'];
			} else {
				$total['denda'] = $total['denda'] +  $dataHeader['denda'];
			}

			if (empty($sub_total['premi'])) {

				$sub_total['premi'] = $dataHeader['premi'];
			} else {

				$sub_total['premi'] = $sub_total['premi'] +  $dataHeader['premi'];
			}
			if (empty($sub_total['lembur'])) {

				$sub_total['lembur'] = $dataHeader['lembur'];
			} else {

				$sub_total['lembur'] = $sub_total['lembur'] +  $dataHeader['lembur'];
			}
			if (empty($sub_total['jum_pendapatan'])) {

				$sub_total['jum_pendapatan'] = $jumPendapatan;
			} else {

				$sub_total['jum_pendapatan'] = $sub_total['jum_pendapatan'] +  $jumPendapatan;
			}
			if (empty($sub_total['denda'])) {

				$sub_total['denda'] = $dataHeader['denda'];
			} else {

				$sub_total['denda'] = $sub_total['denda'] +  $dataHeader['denda'];
			}

			// looping potongan
			$html_potongan = '';
			foreach ($potongan as $key => $value) {
				$dataPotongan = $this->db->query("SELECT a.*,b.jenis,b.nama,b.urut FROM payroll_gaji_tr_dt a inner join payroll_tipe_gaji b
                    on a.tipe_gaji=b.id
                    where a.id_hd=" . $dataHeader['id'] . " and a.tipe_gaji=" . $value['id'] . ";")->row_array();
				if (empty($dataPotongan)) {
					$html_potongan = $html_potongan . "<td  style='text-align: right'>" . 0 . "</td>";
					$total[$value['nama']] = 0;
					$sub_total[$value['nama']] = 0;
				} else {
					$html_potongan = $html_potongan . "<td  style='text-align: right'>" . $this->format_number_report($dataPotongan['nilai']) . "</td>";
					$jumPotongan = $jumPotongan + $dataPotongan['nilai'];
					$totalPotongan = $totalPotongan + $dataPotongan['nilai'];
					if (empty($total[$value['nama']])) {
						$total[$value['nama']] = $dataPotongan['nilai'];
					} else {
						$total[$value['nama']] = $total[$value['nama']] + $dataPotongan['nilai'];
					}
					if (empty($sub_total[$value['nama']])) {

						$sub_total[$value['nama']] = $dataPotongan['nilai'];
					} else {

						$sub_total[$value['nama']] = $sub_total[$value['nama']] + $dataPotongan['nilai'];
					}
				}
			}


			$jumPotongan = $jumPotongan + $dataHeader['hk_potongan_gaji'] + $dataHeader['jht'] +  $dataHeader['jp'] + $dataHeader['jkn'] + $dataHeader['denda'];
			$jumlah_gaji_pph = $jumPendapatan + $dataHeader['jkk_perusahaan'] + $dataHeader['jkn_perusahaan'] + $dataHeader['jkm_perusahaan'] + $dataHeader['nilai_rp_catu'] - $jumPotongan;
			$totalPotongan = $totalPotongan + $dataHeader['hk_potongan_gaji'] + $dataHeader['jht'] +  $dataHeader['jp'] + $dataHeader['jkn'] + $dataHeader['denda'];
			$sub_totalPotongan = $sub_totalPotongan + $dataHeader['hk_potongan_gaji'] + $dataHeader['jht'] +  $dataHeader['jp'] + $dataHeader['jkn'] + $dataHeader['denda'];
			if (empty($sub_total['hk_potongan_gaji'])) {
				$sub_total['hk_potongan_gaji'] = $dataHeader['hk_potongan_gaji'];
			} else {
				$sub_total['hk_potongan_gaji'] = $sub_total['hk_potongan_gaji'] +  $dataHeader['hk_potongan_gaji'];
			}
			if (empty($sub_total['jht'])) {
				$sub_total['jht'] = $dataHeader['jht'];
			} else {
				$sub_total['jht'] = $sub_total['jht'] +  $dataHeader['jht'];
			}
			if (empty($sub_total['jp'])) {
				$sub_total['jp'] = $dataHeader['jp'];
			} else {
				$sub_total['jp'] = $sub_total['jp'] +  $dataHeader['jp'];
			}
			if (empty($sub_total['jkn'])) {
				$sub_total['jkn'] = $dataHeader['jkn'];
			} else {
				$sub_total['jkn'] = $sub_total['jkn'] +  $dataHeader['jkn'];
			}
			if (empty($sub_total['jum_potongan'])) {
				$sub_total['jum_potongan'] = $jumPotongan;
			} else {
				$sub_total['jum_potongan'] = $sub_total['jum_potongan'] +  $jumPotongan;
			}

			if (empty($total['hk_potongan_gaji'])) {
				$total['hk_potongan_gaji'] = $dataHeader['hk_potongan_gaji'];
			} else {
				$total['hk_potongan_gaji'] = $total['hk_potongan_gaji'] +  $dataHeader['hk_potongan_gaji'];
			}
			if (empty($total['jht'])) {
				$total['jht'] = $dataHeader['jht'];
			} else {
				$total['jht'] = $total['jht'] +  $dataHeader['jht'];
			}
			if (empty($total['jp'])) {
				$total['jp'] = $dataHeader['jp'];
			} else {
				$total['jp'] = $total['jp'] +  $dataHeader['jp'];
			}
			if (empty($total['jkn'])) {
				$total['jkn'] = $dataHeader['jkn'];
			} else {
				$total['jkn'] = $total['jkn'] +  $dataHeader['jkn'];
			}
			if (empty($total['jum_potongan'])) {
				$total['jum_potongan'] = $jumPotongan;
			} else {
				$total['jum_potongan'] = $total['jum_potongan'] + $jumPotongan;
			}
			$total_jkk_perusahaan = $total_jkk_perusahaan + $dataHeader['jkk_perusahaan'];
			$sub_total_jkk_perusahaan = $sub_total_jkk_perusahaan + $dataHeader['jkk_perusahaan'];
			$total_jkn_perusahaan = $total_jkn_perusahaan + $dataHeader['jkn_perusahaan'];
			$sub_total_jkn_perusahaan =  $sub_total_jkn_perusahaan + $dataHeader['jkn_perusahaan'];
			$total_jkm_perusahaan =  $total_jkm_perusahaan + $dataHeader['jkm_perusahaan'];
			$sub_total_jkm_perusahaan =  $sub_total_jkm_perusahaan + $dataHeader['jkm_perusahaan'];
			$total_catu_beras = $total_catu_beras + $dataHeader['nilai_rp_catu'];
			$sub_total_catu_beras = $sub_total_catu_beras + $dataHeader['nilai_rp_catu'];
			$total_gaji_pph = $total_gaji_pph + $jumlah_gaji_pph;
			$sub_total_gaji_pph = $sub_total_gaji_pph + $jumlah_gaji_pph;
			$html = $html . " <tr><td>" . $no . "</td>
					<td>" . $dataHeader['nama'] . "</td>
					<td>" . $dataHeader['nip'] . "</td>
                 <td>" . $dataHeader['jabatan'] . "</td>
                 <td>" . $dataHeader['divisi'] . "</td>
				 <td>" . $dataHeader['tipe_karyawan'] . "</td>
				 <td style='text-align: right'>" . $dataHeader['jumlah_hari_masuk'] . "</td>";
			$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['gapok']) . "</td>";
			$html = $html . $html_pendapatan;
			$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['premi']) . "</td>";
			$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['lembur']) . "</td>";
			$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($jumPendapatan) . "</td>";
			$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['denda']) . "</td>";

			$html = $html . $html_potongan;
			$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['hk_potongan_gaji']) . "</td>";
			$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['jht']) . "</td>";
			$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['jp']) . "</td>";
			$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['jkn']) . "</td>";
			$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($jumPotongan) . "</td>";
			$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($jumPendapatan - $jumPotongan) . "</td>";
			$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['jkk_perusahaan']) . "</td>";
			$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['jkm_perusahaan']) . "</td>";
			$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['jkn_perusahaan']) . "</td>";
			$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['nilai_rp_catu']) . "</td>";
			$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($jumlah_gaji_pph) . "</td>";
			$html = $html . "</tr>";
			$idx = $i + 1;
			$sub_bagian_next = $dataHd[$idx]['tipe_karyawan_id']; // 
			if (!is_null($sub_bagian_next)) {
				if ($sub_bagian_next != $dataHeader['tipe_karyawan_id']) {
					$html = $html . "<tr><td colspan='6' style='text-align: left'><b> " . $dataHeader['tipe_karyawan'] . "</b></td>";
					$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_hk) . "</b></td>";
					$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_gapok) . "</b></td>";
					foreach ($sub_total as $key => $value) {
						$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($value) . "</b></td>";
					}

					$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_totalPendapatan - $sub_totalPotongan) . "</b></td>";
					$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_jkk_perusahaan) . "</b></td>";
					$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_jkm_perusahaan) . "</b></td>";
					$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_jkn_perusahaan) . "</b></td>";
					$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_catu_beras) . "</b></td>";
					$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_gaji_pph) . "</b></td>";
					$html = $html .	"</tr>";

					$sub_total_hk = 0;
					$sub_total_gapok = 0;
					$sub_total = array();
					$sub_totalPendapatan = 0;
					$sub_totalPotongan = 0;
					$sub_total_jkk_perusahaan = 0;
					$sub_total_jkn_perusahaan = 0;
					$sub_total_jkm_perusahaan = 0;
					$sub_total_gaji_pph = 0;
					$sub_total_catu_beras = 0;
				}
			} else {
				$html = $html . "<tr><td colspan='6' style='text-align: left'><b> " . $dataHeader['tipe_karyawan'] . "</b></td>";
				$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_hk) . "</b></td>";
				$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_gapok) . "</b></td>";

				foreach ($sub_total as $key => $value) {
					$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($value) . "</b></td>";
				}

				$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_totalPendapatan - $sub_totalPotongan) . "</b></td>";
				$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_jkk_perusahaan) . "</b></td>";
				$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_jkm_perusahaan) . "</b></td>";
				$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_jkn_perusahaan) . "</b></td>";
				$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_catu_beras) . "</b></td>";
				$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($sub_total_gaji_pph) . "</b></td>";
				$html = $html .	"</tr>";

				// $html=$html. var_dump($sub_total);
			}
		}
		// var_dump($total);exit();
		// total summary
		$html = $html . "<tr><td colspan='6' style='text-align: left' ><b>TOTAL</b></td>";
		$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($total_hk) . "</b></td>";
		$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($total_gapok) . "</b></td>";
		foreach ($total as $key => $value) {
			$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($value) . "</b></td>";
		}
		// $html = $html . "<td  style='text-align: right'>" . $this->format_number_report($total['premi']) . "</td>";
		// $html = $html . "<td  style='text-align: right'>" . $this->format_number_report($total['lembur']) . "</td>";

		$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($totalPendapatan - $totalPotongan) . "</b></td>";
		$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($total_jkk_perusahaan) . "</b></td>";
		$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($total_jkm_perusahaan) . "</b></td>";
		$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($total_jkn_perusahaan) . "</b></td>";
		$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($total_catu_beras) . "</b></td>";
		$html = $html . "<td  style='text-align: right'><b>" . $this->format_number_report($total_gaji_pph) . "</b></td>";
		$html = $html .
			"</tr>";
		//         <td style='text-align: right'>". $this->format_number_report( $jum)."</td>
		//         </tr>";
		$html = $html . " </table>
            <br>
            <table class='no_border' style='width:20%' >
            <tr><td></td><td>&nbsp;</td></tr>
            <tr><td></td><td>&nbsp;</td></tr>
            <tr><td></td><td>&nbsp;</td></tr>
            <tr><td></td><td>&nbsp;</td></tr>
            <tr><td></td><td>&nbsp;</td></tr>
            <tr><td width='400px'>&nbsp; </td><td width='400px'>(  ______________________  )
            </td></tr>
            <tr><td width='400px'>&nbsp; </td><td width='400px'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;HRD
            </td></tr>
            </table>
            ";

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A3', 'landscape');
		//echo $html;
		// $html=$html. var_dump($total);

		return $html;
	}
	function print_slip($id, $divisi_id, $status_id)
	{

		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		$html = "<style type='text/css'>
		* {
			font-size: 10px;
			font-family: Arial;
		   }
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
    .page_break { page-break-before: always; }
  </style>
";

		$periodeGaji = $this->HrmsPeriodeGajiModel->retrieve($id);
		if (empty($periodeGaji)) {
			$this->set_response(array("status" => "NOT OK", "data" => 'null'), REST_Controller::HTTP_NOT_FOUND);
			return;
		}

		$pendapatan = $this->db->query("select * from payroll_tipe_gaji where jenis=1 or jenis=2 or jenis=3 order by urut")->result_array();
		$potongan = $this->db->query("select * from payroll_tipe_gaji where jenis=0 order by urut")->result_array();
		// $sql="SELECT a.*,c.nip,c.nama,d.nama as jabatan,e.nama as departemen FROM payroll_gaji_tr_hd a inner join payroll_karyawan_gaji b on a.karyawan_id=b.karyawan_id
		// inner join karyawan c on a.karyawan_id=c.id inner join payroll_jabatan d on c.jabatan_id=d.id
		// inner join payroll_department e on c.departemen_id=e.id
		// where a.periode_gaji_id=" . $id . " order by nama";
	
		// $sql = "
		// SELECT a.*,c.nip,c.nama,d.nama as jabatan,e.nama as departemen ,f.nama as divisi,
		// c.tipe_karyawan_id , g.nama as tipe_karyawan
		// FROM payroll_gaji_tr_hd a 
		//  left join payroll_karyawan_gaji b on a.karyawan_id=b.karyawan_id
        //  inner join karyawan c on a.karyawan_id=c.id 
		//  left join payroll_jabatan d on c.jabatan_id=d.id
        //  left join payroll_department e on c.departemen_id=e.id
		//  left join gbm_organisasi f on c.sub_bagian_id=f.id
		//  left join payroll_tipe_karyawan g on g.id=c.tipe_karyawan_id 
        // where a.periode_gaji_id=" . $id . "";
			
		/*UPDATE: DIVISI/AFDELING NGMBIL DARI TABEL PAYROLL GAJI BUKAN DR TABLE MASTER KARYAWAN */
		$sql = "
		SELECT a.*,c.nip,c.nama,d.nama as jabatan,e.nama as departemen ,f.nama as divisi,
		c.tipe_karyawan_id , g.nama as tipe_karyawan
		FROM payroll_gaji_tr_hd a 
		 left join payroll_karyawan_gaji b on a.karyawan_id=b.karyawan_id
         inner join karyawan c on a.karyawan_id=c.id 
		 left join payroll_jabatan d on c.jabatan_id=d.id
         left join payroll_department e on c.departemen_id=e.id
		 left join gbm_organisasi f on a.divisi_id=f.id
		 left join payroll_tipe_karyawan g on g.id=c.tipe_karyawan_id 
        where a.periode_gaji_id=" . $id . "";
		if (($divisi_id)) {
			// $sql = $sql . " and c.sub_bagian_id=" . $divisi_id . "";
			$sql = $sql . " and a.divisi_id=" . $divisi_id . "";
		}
		if (($status_id)) {
			$sql = $sql . " and c.tipe_karyawan_id =" . $status_id . "";
		}
		$sql = $sql . " order by nama;";

		$dataHd = $this->db->query($sql)->result_array();


		$no = 0;
		$countPendapatan = 0;
		$total = array();
		$totalPendapatan = 0;
		$totalPotongan = 0;
		$countPendapatan = count($pendapatan) + 2;
		$view_data['dataHd'] = $dataHd;
		$countData = count($dataHd);
		$html = $html .  " <table  >";
		$j = 2;
		for ($i = 0; $i < $countData; $i++) {


			$dataHeader = $dataHd[$i];
			// foreach ($dataHd as $key => $dataHeader) {
			$no++;
			$html_slip = '';
			$html_slip = $html_slip .  " <div><h3> Slip Gaji </h3>";
			$html_slip = $html_slip .  "
			<table style='border: 1px solid black;
			border-collapse: collapse;witdh:50%'><tr><td>
			
			<table  >
			<tr><td>Nip</td> <td>:</td> <td>" . $dataHeader['nip'] . "</td></tr>
			<tr><td>Nama</td> <td>:</td> <td>" . $dataHeader['nama'] . "</td></tr>
			<tr><td>Jabatan</td> <td>:</td> <td>" . $dataHeader['jabatan'] . "</td></tr>
			<tr><td>Divisi</td> <td>:</td> <td>" . $dataHeader['divisi'] . "</td></tr>
			<tr><td>Periode gaji</td> <td>:</td> <td>" . $periodeGaji['nama'] . "</td></tr>
			<tr><td>Masuk(Hari)</td> <td>:</td> <td>" . $this->format_number_report($dataHeader['jumlah_hari_masuk'], 1) . "</td></tr>";
			if ($dataHeader['jumlah_jam_lembur'] > 0) {
				$html_slip = $html_slip . "<tr><td>Lembur(Jam)</td> <td>:</td> <td>" . $this->format_number_report($dataHeader['jumlah_jam_lembur'], 1) . "</td></tr>";
			}
			$html_slip = $html_slip . " </table>";

			$jumPendapatan = 0;
			$jumPotongan = 0;
			$dataPendapatan = $this->db->query("SELECT a.*,b.jenis,b.nama,b.urut FROM payroll_gaji_tr_dt a inner join payroll_tipe_gaji b
            on a.tipe_gaji=b.id
            where jenis='1' and a.id_hd=" . $dataHeader['id'] .  " order by urut;")->result_array();
			$html_slip = $html_slip .  " <table> <tr>";
			$html_slip = $html_slip .  " <td width='50%'>";
			$html_slip = $html_slip .  " <b> Pendapatan </b>";
			$html_slip = $html_slip . " <table>";
			$html_slip = $html_slip . "<tr><td width='10%'>" . 'Gaji Pokok' . "</td> <td>:</td> <td width='10%' style='text-align: right'>" . $this->format_number_report($dataHeader['gapok']) . "</td></tr>";
			// $view_data['dataPendapatan'][] = $dataPendapatan;
			foreach ($dataPendapatan as $key => $pendapatan) {
				if (empty($pendapatan)) {
				} else {
					$html_slip = $html_slip . "<tr><td width='10%'>" . $pendapatan['nama'] . "</td> <td>:</td> <td width='10%' style='text-align: right'>" . $this->format_number_report($pendapatan['nilai']) . "</td></tr>";
					$jumPendapatan = $jumPendapatan + $pendapatan['nilai'];
				}
			}

			$html_slip = $html_slip . "<tr><td width='10%'>" . 'Premi' . "</td> <td>:</td> <td width='10%' style='text-align: right'>" . $this->format_number_report($dataHeader['premi'], 0) . "</td></tr>";
			if ($dataHeader['lembur'] > 0) {
				$html_slip = $html_slip . "<tr><td width='10%'>" . 'Lembur' . "</td> <td>:</td> <td width='10%' style='text-align: right'>" . $this->format_number_report($dataHeader['lembur'], 0) . "</td></tr>";
			}
			// if ($dataHeader['jumlah_kg_catu'] > 0) {
			$html_slip = $html_slip . "<tr><td width='10%'>Catu(" . $this->format_number_report($dataHeader['jumlah_kg_catu'], 1) . " Kg)</td> <td>:</td> <td width='10%' style='text-align: right'>" . $this->format_number_report($dataHeader['nilai_rp_catu'], 0) . "</td></tr>";
			// }
			$jumPendapatan = $jumPendapatan + $dataHeader['gapok'] + $dataHeader['premi'] + $dataHeader['lembur'] + $dataHeader['nilai_rp_catu'];
			//$html_slip = $html_slip ."<hr class='solid'>";
			$html_slip = $html_slip . "<tr><td> </td> <td></td> <td td width='10%' style='text-align: right'><hr class='solid'></td></tr>";
			$html_slip = $html_slip . "<tr><td></td> <td></td> <td width='10%' style='text-align: right'><b>" . $this->format_number_report($jumPendapatan, 0) . "</b></td></tr>";
			$html_slip = $html_slip . " </table>";

			$html_slip = $html_slip .  " </td>";
			$html_slip = $html_slip .  " <td width='50%'>";
			$dataPotongan = $this->db->query("SELECT a.*,b.jenis,b.nama,b.urut FROM payroll_gaji_tr_dt a inner join payroll_tipe_gaji b
            on a.tipe_gaji=b.id
            where  jenis='0' and a.id_hd=" . $dataHeader['id'] .  " order by urut;")->result_array();
			// if (!empty($dataPotongan)) {
			$html_slip = $html_slip .  " <b> Potongan </b>";
			$html_slip = $html_slip . " <table>";


			// var_dump($dataPendapatan);exit();
			foreach ($dataPotongan as $key => $potongan) {
				if (empty($potongan)) {
				} else {
					$html_slip = $html_slip . "<tr><td width='10%'>" . $potongan['nama'] . "</td> <td>:</td> <td width='10%' style='text-align: right'>" . $this->format_number_report($potongan['nilai']) . "</td></tr>";
					$jumPotongan = $jumPotongan + $potongan['nilai'];
				}
			}
			if ($dataHeader['denda'] > 0) {
				$html_slip = $html_slip . "<tr><td width='10%'>" . 'Denda' . "</td> <td>:</td> <td width='10%' style='text-align: right'>" . $this->format_number_report($dataHeader['denda']) . "</td></tr>";
			}
			$html_slip = $html_slip . "<tr><td width='10%'>" . 'Pot HK' . "</td> <td>:</td> <td width='10%' style='text-align: right'>" . $this->format_number_report($dataHeader['hk_potongan_gaji'], 0) . "</td></tr>";
			$html_slip = $html_slip . "<tr><td width='10%'>" . 'JHT' . "</td> <td>:</td> <td width='10%' style='text-align: right'>" . $this->format_number_report($dataHeader['jht'], 0) . "</td></tr>";
			$html_slip = $html_slip . "<tr><td width='10%'>" . 'JP' . "</td> <td>:</td> <td width='10%' style='text-align: right'>" . $this->format_number_report($dataHeader['jp'], 0) . "</td></tr>";
			$html_slip = $html_slip . "<tr><td width='10%'>" . 'JKN' . "</td> <td>:</td> <td width='10%' style='text-align: right'>" . $this->format_number_report($dataHeader['jkn'], 0) . "</td></tr>";
			//$html_slip = $html_slip . "<tr><td width='10%'>" . 'PPH21' . "</td> <td>:</td> <td width='300px' style='text-align: right'>" . $this->format_number_report($dataHeader['pph']) . "</td></tr>";

			$jumPotongan = $jumPotongan + $dataHeader['hk_potongan_gaji'] + $dataHeader['jht'] + $dataHeader['jp'] + $dataHeader['jkn'] + $dataHeader['pph'] + $dataHeader['denda'];
			//$html_slip = $html_slip . "<tr><td> </td> <td></td> <td  width='10%' style='text-align: right'>_______________</td></tr>";
			$html_slip = $html_slip . "<tr><td> </td> <td></td> <td td width='10%' style='text-align: right'><hr class='solid'></td></tr>";
			$html_slip = $html_slip . "<tr><td> </td> <td></td> <td width='10%' style='text-align: right'><b>" . $this->format_number_report($jumPotongan, 0) . "</b></td></tr>";
			$html_slip = $html_slip . "</table>";

			$html_slip = $html_slip .  " </td>";
			$html_slip = $html_slip .  " </tr></table>";
			// }
			$html_slip = $html_slip . "<table><tr><td width='10%'><b> Total Diterima:&nbsp;</b></td> <td></td> <td width='10%' style='text-align: right'><b>" . $this->format_number_report(($jumPendapatan - $jumPotongan), 0) . "</b></td></tr>";
			$html_slip = $html_slip . "</table>";


			$html_slip = $html_slip . "
			<br>";

			// <table  >

			// <tr><td></td><td>&nbsp;</td></tr>
			// <tr><td></td><td>&nbsp;</td></tr>
			// <tr><td width='10%'>&nbsp; </td><td width='10%'>(  ________________ )
			// </td></tr>
			// <tr><td width='10%'>&nbsp; </td><td width='10%'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;HRD
			// </td></tr>
			// </table>

			// </td></tr></table>
			// </div>";

			$html_slip = $html_slip . "</td></tr></table>
			</div>";
			if ($j == 2) {
				$html = $html .  " <tr> ";
				$html = $html .  " <td style='width:50%;'> " . $html_slip . "</td>";
			} else if ($j == 0 ||  $i == ($countData - 1)) {
				$html = $html .  " <td style='width:50%;'> " . $html_slip . "</td>";
				$html = $html .  " </tr>";
			} else {
				$html = $html .  " <td style='width:50%;'> " . $html_slip . "</td>";
			}
			$j = $j - 1;
			if ($j == 0) {
				$j = 2;
			}
		}
		$html = $html .  " </table>";
		// echo ($html);
		// exit();
		// print_r($html);exit;
		// $html = $this->load->view('table_report', $data, true);
		// $html = $this->load->view('HrmsKaryawanGaji', $view_data, true);
		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'portrait');

		return $html;
	}

	function print_catu_beras($id, $divisi_id, $status_id)
	{
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');




		$periodeGaji = $this->HrmsPeriodeGajiModel->retrieve($id);
		if (empty($periodeGaji)) {
			$this->set_response(array("status" => "NOT OK", "data" => 'null'), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$nama_afd_stasiun = "Semua";
		$res_lokasi = $this->db->query("select * from gbm_organisasi where id=" . $periodeGaji['lokasi_id'] . "")->row_array();
		$nama_lokasi = $res_lokasi['nama'];
		// $sql = "SELECT a.*,c.nip,c.nama,d.nama as jabatan,e.nama as departemen,f.nama as divisi ,c.status_pajak
		// FROM payroll_gaji_tr_hd a inner join payroll_karyawan_gaji b on a.karyawan_id=b.karyawan_id
        // left join karyawan c on a.karyawan_id=c.id 
		// left join payroll_jabatan d on c.jabatan_id=d.id
        // left join payroll_department e on c.departemen_id=e.id
		// left join gbm_organisasi f on c.sub_bagian_id=f.id
		// left join payroll_tipe_karyawan g on g.id=c.tipe_karyawan_id 
        // where a.periode_gaji_id=" . $id . " and jumlah_kg_catu>0 ";

		/*UPDATE: DIVISI/AFDELING NGMBIL DARI TABEL PAYROLL GAJI BUKAN DR TABLE MASTER KARYAWAN */
		$sql = "SELECT a.*,c.nip,c.nama,d.nama as jabatan,e.nama as departemen,f.nama as divisi ,c.status_pajak
		FROM payroll_gaji_tr_hd a inner join payroll_karyawan_gaji b on a.karyawan_id=b.karyawan_id
        left join karyawan c on a.karyawan_id=c.id 
		left join payroll_jabatan d on c.jabatan_id=d.id
        left join payroll_department e on c.departemen_id=e.id
		left join gbm_organisasi f on a.divisi_id=f.id
		left join payroll_tipe_karyawan g on g.id=c.tipe_karyawan_id 
        where a.periode_gaji_id=" . $id . " and jumlah_kg_catu>0 ";

		if (($divisi_id)) {
			// $sql = $sql . " and c.sub_bagian_id=" . $divisi_id . "";
			$sql = $sql . " and a.divisi_id=" . $divisi_id . "";
			$res_afd_stasiun = $this->db->query("select * from  gbm_organisasi where id=" . $divisi_id . "")->row_array();
			$nama_afd_stasiun = $res_afd_stasiun['nama'];
		}
		if (($status_id)) {
			$sql = $sql . " and c.tipe_karyawan_id=" . $status_id . "";
		}
		$sql = $sql . " order by nama;";
		$dataHd = $this->db->query($sql)->result_array();


		$html = get_header_report_v2();

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
		<h3 class="title">LAPORAN CATU BERAS  ' . $periodeGaji['nama'] . '</h3>
  <table class="no_border" style="width:30%">
			
			<tr>
					<td>LOKASI</td>
					<td>:</td>
					<td>' . $nama_lokasi . '</td>
			</tr>
			<tr>	
					<td>PERIODE</td>
					<td>:</td>
					<td>' . $periodeGaji['nama'] . '</td>
			</tr>
			<tr>	
					<td>AFDELING/STATION</td>
					<td>:</td>
					<td>' . $nama_afd_stasiun . '</td>
			</tr>

			
	</table>
	<br>
  ';
		$html = $html . "
         

		 <table   border='1' width='100%' style='border-collapse: collapse;'>
        <thead>
		 <tr>
            <td rowspan >No</td>
            <td rowspan>Nama</td>
			<td rowspan>NIK</td>
			<td rowspan>Status</td>
            <td rowspan>Jabatan</td>
            <td rowspan>Divisi</td>
			<td rowspan>HK Catu</td>
        	<td rowspan  style='text-align: right'>Kg</td>
			<td rowspan  style='text-align: right'>Rp</td>
        </tr>
		</thead>";
		$filename = 'report_' . time();


		$no = 0;
		$totalRpCatu = 0;
		$totalKgCatu = 0;
		foreach ($dataHd as $key => $dataHeader) {
			$no++;
			$html = $html . " <tr><td>" . $no . "</td>
                <td>" . $dataHeader['nama'] . "</td>
				<td>" . $dataHeader['nip'] . "</td>
				<td>" . $dataHeader['status_pajak'] . "</td>
                 <td>" . $dataHeader['jabatan'] . "</td>
                 <td>" . $dataHeader['divisi'] . "</td>
				 <td>" . $this->format_number_report($dataHeader['jumlah_hari_catu']) . "</td>";
			$nilaiRpCatu =  $dataHeader['nilai_rp_catu'];
			$kgCatu =  $dataHeader['jumlah_kg_catu'];
			$totalRpCatu =	$totalRpCatu +	$nilaiRpCatu;
			$totalKgCatu =	$totalKgCatu +	$kgCatu;
			$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($dataHeader['jumlah_kg_catu'], 2) . "</td>";
			$html = $html . "<td  style='text-align: right'>" . $this->format_number_report($nilaiRpCatu) . "</td>";
			$html = $html . "</tr>";
		}
		$html = $html .
			"
			<tr> <td></td><td></td><td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td style='text-align: right'>" . $this->format_number_report($totalKgCatu) . "</td>
			<td style='text-align: right'>" . $this->format_number_report($totalRpCatu) . "</td></tr>
		</table";

		$html = $html . " </table>
            <br>
            <table class='no_border' width='300px'>
            <tr><td></td><td>&nbsp;</td></tr>
            <tr><td></td><td>&nbsp;</td></tr>
            <tr><td></td><td>&nbsp;</td></tr>
            <tr><td></td><td>&nbsp;</td></tr>
            <tr><td></td><td>&nbsp;</td></tr>
            <tr><td width='400px'>&nbsp; </td><td width='400px'>(  ______________________  )
            </td></tr>
           
            </table>
            ";

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		//echo $html;
		return $html;
	}
	public function getRekapAbsensiKebun_post()
	{
		$data_post = $this->post();
		if ($data_post['tipe'] == 'kehadiran') {
			$this->getRekapAbsensiKebunByKehadiran();
		} else 	if ($data_post['tipe'] == 'absensi') {
			$this->getRekapAbsensiKebunByKehadiran_v2();
		} else {
			$this->getRekapAbsensiKebunByUpah();
		}
	}
	public function getRekapAbsensiKebunByKehadiran()
	{
		$data_post = $this->post();

		$catuKg = array();
		$catuRp = array();
		// $qCatu = "SELECT * FROM  hrms_catuberas_setting ";
		// $resCatu = $this->db->query($qCatu)->result_array();
		// foreach ($resCatu as $c) {
		// 	$catuKg[$c['status_karyawan']] = $c['jumlah_kg'];
		// 	$catuRp[$c['status_karyawan']] = $c['jumlah_rupiah'];
		// }



		/* hitung jumlah hari dlm 1 periode */
		$d1 = new DateTime($data_post['tgl_awal']);
		$d2 = new DateTime($data_post['tgl_akhir']);
		$format_laporan = $data_post['format_laporan'];
		$afdeling_id = $data_post['afdeling_id'];
		$interval = $d1->diff($d2);
		$jumlah_hari = $interval->days;

		/* cek jumlah libur */
		$qLibur = "SELECT * FROM  hrms_libur where
		tanggal >='" . $data_post['tgl_awal'] . "' and tanggal <='" . $data_post['tgl_akhir'] . "' ";
		$hari_libur = array();
		$res_libur = $this->db->query($qLibur)->result_array();
		foreach ($res_libur as $key => $l) {
			$hari_libur[$l['tanggal']] = $l['tipe_libur'];
		}
		/* hitung jumlah hari masuk 1 periode */
		// $hari_masuk_efektif = $jumlah_hari_sebulan - $jum_libur;

		// Query utk nagambil master karyawan dan  gaji pokoknya 
		$html = "";
		$q0 = "SELECT a.*,b.nip,b.nama, c.nama as jabatan,b.status_pajak FROM  payroll_karyawan_gaji a 
		inner join karyawan b on a.karyawan_id=b.id
		left join payroll_jabatan c on b.jabatan_id=c.id
		where b.status_id=1 and b.lokasi_tugas_id=" . $data_post['lokasi_id'] . "";
		if ($afdeling_id) {
			$q0 =	$q0 . " and b.sub_bagian_id=" . $afdeling_id . "";
		}
		$q0 =	$q0 . " order by b.nama";
		$arrhd = $this->db->query($q0)->result_array();
		$no = 0;
		$strNo = '';
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
		$html = $html . "	
		<table  border='1' width='100%' style='border-collapse: collapse;'>
		<thead>
		<tr>
			<td rowspan=2 >No</td>
			<td rowspan=2>NIK</td>
			<td rowspan=2>Nama</td>
			<td rowspan=2>Jabatan</td>
			<td colspan=" . ($jumlah_hari + 1) . "  style='text-align: center'> Periode  </td>
			<td rowspan=2  style='text-align: center'>Jumlah</td>
		</tr>
		";
		$html = $html . "<tr>";
		while ($d1 <= $d2) {
			$ddmm = $d1->format('d-m');
			$dd = $d1->format('d');
			// $html = $html . "<td style='text-align: center'>" . $ddmm . "</td>";
			$is_hari_libur = false;
			if ($hari_libur[$d1->format('Y-m-d')]) {
				$is_hari_libur = true;
			}
			if ($is_hari_libur) {
				$html = $html . "<td style='text-align: center' bgcolor='yellow'>" . $dd . "</td>";
			} else {
				$html = $html . "<td style='text-align: center'>" . $dd . "</td>";
			}

			$d1->modify('+1 day');
		}
		$html = $html . "</tr> </thead>";


		foreach ($arrhd as $hd) {
			$jum_absen_perkaryawan = 0; // --> HK Perkaryawan
			$no++;
			//$actual_link ='http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			$actual_link = "http://$_SERVER[HTTP_HOST]" . "/plantation-api/api/GlobalReport/getAbsensiKebunDetailperKaryawan/" . $data_post['tgl_awal'] . "/" . $data_post['tgl_akhir'] .  "/" . $hd['karyawan_id'] . "";
			$html = $html . "<tr>";
			$html = $html . "<td style='text-align: center'>" . $no . "</td>";
			$html = $html . "<td style='text-align: left'> <a href='" . $actual_link . "" . "' target='_blank'> " . $hd['nip'] . " </a></td>";
			$html = $html . "<td style='text-align: left'> <a href='" . $actual_link . "" . "' target='_blank'> " . $hd['nama'] . " </a></td>";
			$html = $html . "<td style='text-align: left'>" . $hd['jabatan'] .  "</td>";
			$d1 = new DateTime($data_post['tgl_awal']);
			while ($d1 <= $d2) {

				$jum_absen = 0; //--> jum HK

				$tgl = $d1->format('Y-m-d');
				$is_hari_libur = false;
				if ($hari_libur[$tgl]) {
					$is_hari_libur = true;
				}
				/* tipe_karyawan_id: 1(KHT),2(KHL),3(PKWT),4(NSB),5(STAFF) */
				// if ($hd['tipe_karyawan_id'] == 2) {
				// 	$gaji_per_hari_efektif = $hd['gapok'] / 25;
				// } else {
				// 	 $gaji_per_hari_efektif = $hd['gapok'] / $hari_masuk_efektif;
				// }
				$gaji_per_hari_efektif = $hd['gapok'] / 25;

				/* ngambil data absensi per periode */
				$qAbsensi = "SELECT * FROM payroll_absensi a inner join hrms_jenis_absensi b on a.jenis_absensi_id=b.id 
					where a.karyawan_id= " . $hd['karyawan_id'] . " 
					and tanggal ='" . $tgl . "' 
					and b.tipe ='DIBAYAR';";
				$resAbsensi = $this->db->query($qAbsensi)->result_array();
				$jum_hari_dibayar = 0;
				$jum_jam_lembur = 0;
				$jum_hari_hadir = 0;
				$lembur = 0;
				$premi_absensi = 0;
				$upah_absensi = 0;
				foreach ($resAbsensi as $absensiKaryawan) {
					// $jum_hari_dibayar++;
					// $upah_absensi = $upah_absensi + $absensiKaryawan['premi'];
					if ($absensiKaryawan['kode'] == 'H') { // Jumlah Hadir 
						$jum_absen++;
						// $jum_absen_perkaryawan=$jum_absen_perkaryawan+$jum_absen;
						$upah_absensi = $upah_absensi + $gaji_per_hari_efektif;
					}
				}
				// $upah_absensi = $hd['gapok'] * $jum_hari_hadir;
				// $hk_potongan_hari =	$hari_masuk_efektif - $jum_hari_dibayar;
				// $hk_potongan_gaji =	$hk_potongan_hari * $gaji_per_hari_efektif;
				$qLembur = "SELECT * FROM  payroll_lembur where karyawan_id= " . $hd['karyawan_id'] . " 
					and	tanggal ='" . $tgl . "' ;";
				$resLembur = $this->db->query($qLembur)->result_array();
				foreach ($resLembur as $lemburKaryawan) {
					$jum_jam_lembur = $jum_jam_lembur + $lemburKaryawan['jumlah_jam'];
					$lembur = $lembur + $lemburKaryawan['nilai_lembur'];
				}
				/* ngambil data bkm BKM Umum per periode */
				$resBkmUmum =	$this->db->query("select * from est_bkm_umum_ht a inner join 
				est_bkm_umum_dt b on a.id=b.bkm_umum_id  where b.karyawan_id= " . $hd['karyawan_id'] . " 
				and tanggal ='" . $tgl . "'  ")->result_array();
				$premi_umum = 0;
				$upah_umum = 0;
				foreach ($resBkmUmum as $umum) {
					// $jum_absen++;
					$jum_absen = $jum_absen + $umum['jumlah_hk'];
					// $jum_absen_perkaryawan=$jum_absen_perkaryawan+$jum_absen;
					$premi_umum = $premi_umum + $umum['premi'];
					$upah_umum = $upah_umum + $umum['rupiah_hk'];
				}


				/* ngambil data bkm panen per periode */
				$resPanen =	$this->db->query("select * from est_bkm_panen_ht a inner join 
					est_bkm_panen_dt b on a.id=b.bkm_panen_id  where karyawan_id= " . $hd['karyawan_id'] . "  
					and tanggal ='" . $tgl . "' and is_premi_kontanan=0 ")->result_array();
				$premi_panen = 0;
				$upah_panen = 0;
				$denda_panen = 0;
				foreach ($resPanen as $panen) {
					// $jum_absen++;
					$jum_absen = $jum_absen + $panen['jumlah_hk'];
					// $jum_absen_perkaryawan=$jum_absen_perkaryawan+$jum_absen;
					$upah_panen = $upah_panen + $panen['rp_hk'];
					$premi_panen = $premi_panen + $panen['premi_panen'];
					$denda_panen = $denda_panen + $panen['denda_panen'];
				}

				/* ngambil data bkm pemeliharaan per periode */
				$resPemeliharaan =	$this->db->query("select * from est_bkm_pemeliharaan_ht a inner join 
						est_bkm_pemeliharaan_dt b on a.id=b.bkm_pemeliharaan_id  where karyawan_id= " . $hd['karyawan_id'] . "  
						and tanggal ='" . $tgl . "'  ")->result_array();
				$premi_pemeliharaan = 0;
				$upah_pemeliharaan = 0;
				$denda_pemeliharaan = 0;
				foreach ($resPemeliharaan as $pemeliharaan) {
					// $jum_absen++;
					$jum_absen = $jum_absen + $pemeliharaan['jumlah_hk'];
					// $jum_absen_perkaryawan=$jum_absen_perkaryawan+$jum_absen;
					$premi_pemeliharaan = $premi_pemeliharaan + $pemeliharaan['premi'];
					$upah_pemeliharaan = $upah_pemeliharaan + $pemeliharaan['rupiah_hk'];
					$denda_pemeliharaan = $denda_pemeliharaan + $panen['denda_pemeliharaan'];
				}

				/* ngambil data bkm Traksi per periode */
				$resTraksi =	$this->db->query("select * from trk_kegiatan_kendaraan_ht a inner join 
				trk_kegiatan_kendaraan_dt b on a.id=b.trk_kegiatan_kendaraan_id  where b.karyawan_id= " . $hd['karyawan_id'] . " 
				and tanggal ='" . $tgl . "'  ")->result_array();
				$premi_traksi = 0;
				$upah_traksi = 0;
				foreach ($resTraksi as $traksi) {
					// $jum_absen++;
					$jum_absen = $jum_absen + $traksi['jumlah_hk'];
					// $jum_absen_perkaryawan=$jum_absen_perkaryawan+$jum_absen;
					$premi_traksi = $premi_traksi + $traksi['premi'];
					$upah_traksi = $upah_traksi + $traksi['rupiah_hk'];
				}

				/* ngambil data bkm workshop per periode */
				$resWorkshop =	$this->db->query("select * from wrk_kegiatan_ht a inner join 
				wrk_kegiatan_dt b on a.id=b.wrk_kegiatan_id  where b.karyawan_id= " . $hd['karyawan_id'] . " 
				and tanggal ='" . $tgl . "'  ")->result_array();
				$premi_workshop = 0;
				$upah_workshop = 0;
				foreach ($resWorkshop as $workshop) {
					// $jum_absen++;
					$jum_absen = $jum_absen + $workshop['jumlah_hk'];
					// $jum_absen_perkaryawan=$jum_absen_perkaryawan+$jum_absen;
					$premi_workshop = $premi_workshop + $workshop['premi'];
					$upah_workshop = $upah_workshop + $workshop['rupiah_hk'];
				}

				/* ngambil data Mandor dari bkm panen per periode */
				$resPanen =	$this->db->query("select * from est_bkm_panen_ht where mandor_id= " . $hd['karyawan_id'] . " 
		 					and tanggal ='" . $tgl . "' and is_premi_kontanan=0 ")->result_array();
				$premi_panen_mandor = 0;
				$upah_panen_mandor = 0;
				foreach ($resPanen as $panen) {
					// $jum_absen++;
					$jum_absen = $jum_absen + $panen['jumlah_hk_mandor'];
					// $jum_absen_perkaryawan=$jum_absen_perkaryawan+$jum_absen;
					$upah_panen_mandor = $upah_panen_mandor + $panen['rp_hk_mandor'];
					$premi_panen_mandor = $premi_panen_mandor + $panen['premi_mandor'];
				}
				/* ngambil data Krani dari bkm panen per periode */
				$resPanen =	$this->db->query("select * from est_bkm_panen_ht where kerani_id= " . $hd['karyawan_id'] . "  
					and tanggal ='" . $tgl . "' and is_premi_kontanan=0 ")->result_array();
				$premi_panen_kerani = 0;
				$upah_panen_kerani = 0;
				foreach ($resPanen as $panen) {
					// $jum_absen++;
					$jum_absen = $jum_absen + $panen['jumlah_hk_kerani'];
					// $jum_absen_perkaryawan=$jum_absen_perkaryawan+$jum_absen;
					$upah_panen_kerani = $upah_panen_kerani + $panen['rp_hk_kerani'];
					$premi_panen_kerani = $premi_panen_kerani + $panen['premi_kerani'];
				}

				/* ngambil data Mandor dari bkm Pemeliharaan per periode */
				$resPanen =	$this->db->query("select * from est_bkm_pemeliharaan_ht where mandor_id= " . $hd['karyawan_id'] . " 
		 					and tanggal ='" . $tgl . "' ")->result_array();
				$premi_panen_mandor = 0;
				$upah_panen_mandor = 0;
				foreach ($resPanen as $panen) {
					// $jum_absen++;
					$jum_absen = $jum_absen + $panen['jumlah_hk_mandor'];
					// $jum_absen_perkaryawan=$jum_absen_perkaryawan+$jum_absen;
					$upah_panen_mandor = $upah_panen_mandor + $panen['rp_hk_mandor'];
					$premi_panen_mandor = $premi_panen_mandor + $panen['premi_mandor'];
				}
				/* ngambil data Krani dari bkm Pemeliharaan per periode */
				$resPanen =	$this->db->query("select * from est_bkm_pemeliharaan_ht where kerani_id= " . $hd['karyawan_id'] . "  
					and tanggal ='" . $tgl . "'  ")->result_array();
				$premi_panen_kerani = 0;
				$upah_panen_kerani = 0;
				foreach ($resPanen as $panen) {
					// $jum_absen++;
					$jum_absen = $jum_absen + $panen['jumlah_hk_kerani'];
					// $jum_absen_perkaryawan=$jum_absen_perkaryawan+$jum_absen;
					$upah_panen_kerani = $upah_panen_kerani + $panen['rp_hk_kerani'];
					$premi_panen_kerani = $premi_panen_kerani + $panen['premi_kerani'];
				}
				$upah = $upah_absensi + $upah_panen + $upah_pemeliharaan + $upah_traksi + $upah_workshop + $upah_panen_mandor + $upah_panen_kerani;
				$premi = $premi_panen + $premi_pemeliharaan + $premi_traksi + $premi_workshop + $upah_panen_mandor + $upah_panen_kerani;
				$jum_absen_str = $jum_absen == 0 ? "" : $jum_absen;
				//$actual_link ='http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
				$actual_link = "http://$_SERVER[HTTP_HOST]" . "/plantation-api/api/GlobalReport/getAbsensiKebunDetail/" . $tgl . "/" . $hd['karyawan_id'] . "";
				// $html = $html . "<td style='text-align: center'> <a href='" . $actual_link . "" . "' target='_blank'> " . $jum_absen_str . " </a></td>";

				if ($jum_absen > 1) {
					$html = $html . "<td style='text-align: center;' bgcolor='red'> <a href='" . $actual_link . "" . "' target='_blank' > " .  $jum_absen_str . " </a></td>";
				} else {
					if ($is_hari_libur) {
						$html = $html . "<td style='text-align: center;' bgcolor='yellow'> <a href='" . $actual_link . "" . "' target='_blank'> " . $jum_absen_str . " </a></td>";
					} else {
						$html = $html . "<td style='text-align: center;' > <a href='" . $actual_link . "" . "' target='_blank'> " . $jum_absen_str . " </a></td>";
					}
				}
				$d1->modify('+1 day');
				$jum_absen_perkaryawan = $jum_absen_perkaryawan + $jum_absen;
			}

			$html = $html . "<td style='text-align: center'>" . $jum_absen_perkaryawan . " </td>";
			$html = $html . "</tr>";
		}

		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: center'> </td>";
		$html = $html . "<td style='text-align: center'></td>";
		$html = $html . "<td style='text-align: center'></td>";
		$html = $html . "<td style='text-align: center'></td>";
		$d1 = new DateTime($data_post['tgl_awal']);
		while ($d1 <= $d2) {
			$ddmm = $d1->format('d-m');
			$html = $html . "<td style='text-align: center'></td>";
			$d1->modify('+1 day');
		}
		$html = $html . "<td style='text-align: right'> </td>";
		$html = $html . "</tr>";
		$html = $html . "</table>";
		if ($format_laporan == 'xls') {
			echo $html;
		} else {
			$this->set_response(array("status" => "OK", "data" => $html), REST_Controller::HTTP_OK);
		}

		// if (($hasil['jum']) > 0) {

		// 	$this->set_response(array("status" => "OK", "data" => 'Proses berhasil.' . $hasil['jum'] . " data diproses"), REST_Controller::HTTP_CREATED);
		// } else {
		// 	$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data yang diproses"), REST_Controller::HTTP_OK);
		// }
	}
	public function getRekapAbsensiKebunByKehadiran_v2()
	{
		$data_post = $this->post();

		$catuKg = array();
		$catuRp = array();
		// $qCatu = "SELECT * FROM  hrms_catuberas_setting ";
		// $resCatu = $this->db->query($qCatu)->result_array();
		// foreach ($resCatu as $c) {
		// 	$catuKg[$c['status_karyawan']] = $c['jumlah_kg'];
		// 	$catuRp[$c['status_karyawan']] = $c['jumlah_rupiah'];
		// }


		/* hitung jumlah hari dlm 1 periode */
		$d1 = new DateTime($data_post['tgl_awal']);
		$d2 = new DateTime($data_post['tgl_akhir']);
		$format_laporan = $data_post['format_laporan'];
		$afdeling_id = $data_post['afdeling_id'];
		$interval = $d1->diff($d2);
		$jumlah_hari = $interval->days;

		/* cek jumlah libur */
		$qLibur = "SELECT * FROM  hrms_libur where
		tanggal >='" . $data_post['tgl_awal'] . "' and tanggal <='" . $data_post['tgl_akhir'] . "' ";
		$hari_libur = array();
		$res_libur = $this->db->query($qLibur)->result_array();
		foreach ($res_libur as $key => $l) {
			$hari_libur[$l['tanggal']] = $l['tipe_libur'];
		}
		/* hitung jumlah hari masuk 1 periode */
		// $hari_masuk_efektif = $jumlah_hari_sebulan - $jum_libur;

		// Query utk nagambil master karyawan dan  gaji pokoknya 
		$html = "";
		$q0 = "SELECT a.*,b.nip,b.nama, c.nama as jabatan,b.status_pajak FROM  payroll_karyawan_gaji a 
		inner join karyawan b on a.karyawan_id=b.id
		left join payroll_jabatan c on b.jabatan_id=c.id
		where b.status_id=1 and b.lokasi_tugas_id=" . $data_post['lokasi_id'] . "";
		if ($afdeling_id) {
			$q0 =	$q0 . " and b.sub_bagian_id=" . $afdeling_id . "";
		}
		$q0 =	$q0 . " order by b.nama";
		$arrhd = $this->db->query($q0)->result_array();

		$qKodeAbsensi = "SELECT * FROM hrms_jenis_absensi ;";
		$resKodeAbsensi = $this->db->query($qKodeAbsensi)->result_array();
		$no = 0;
		$strNo = '';
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
		$html = $html . "	
		<table   border='1' width='100%' style='border-collapse: collapse;'>
		<thead>
		<tr>
			<td rowspan=2 >No</td>
			<td rowspan=2>NIK</td>
			<td rowspan=2>Nama</td>
			<td rowspan=2>Jabatan</td>
			<td colspan=" . ($jumlah_hari + 1 + count($resKodeAbsensi)) . "  style='text-align: center'> Periode  </td>
		</tr>
		";
		$html = $html . "<tr>";
		while ($d1 <= $d2) {
			$ddmm = $d1->format('d');

			$is_hari_libur = false;
			if ($hari_libur[$d1->format('Y-m-d')]) {
				$is_hari_libur = true;
			}
			if ($is_hari_libur) {
				$html = $html . "<td style='text-align: center' bgcolor='yellow'>" . $ddmm . "</td>";
			} else {
				$html = $html . "<td style='text-align: center'>" . $ddmm . "</td>";
			}
			$d1->modify('+1 day');
		}


		foreach ($resKodeAbsensi as $key => $value) {
			$html = $html . "<td style='text-align: center'>" . $value['kode'] . "</td>";
		}
		$html = $html . "</tr> </thead>";
		$kode_hadir = 'H';
		$kode_mangkir = 'M';

		$kehadiran = array();
		foreach ($arrhd as $hd) {
			foreach ($resKodeAbsensi as $key => $value) {
				$kehadiran[$hd['karyawan_id']][$value['kode']] = 0;
			}

			$jum_absen_perkaryawan = 0; // --> HK Perkaryawan
			$no++;
			//$actual_link ='http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			$actual_link = "http://$_SERVER[HTTP_HOST]" . "/plantation-api/api/GlobalReport/getAbsensiKebunDetailperKaryawan/" . $data_post['tgl_awal'] . "/" . $data_post['tgl_akhir'] .  "/" . $hd['karyawan_id'] . "";
			$html = $html . "<tr>";
			$html = $html . "<td style='text-align: center'>" . $no . "</td>";
			$html = $html . "<td style='text-align: left'> <a href='" . $actual_link . "" . "' target='_blank'> " . $hd['nip'] . " </a></td>";
			$html = $html . "<td style='text-align: left'> <a href='" . $actual_link . "" . "' target='_blank'> " . $hd['nama'] . " </a></td>";
			$html = $html . "<td style='text-align: left'>" . $hd['jabatan'] .  "</td>";
			$d1 = new DateTime($data_post['tgl_awal']);
			while ($d1 <= $d2) {
				$jum_absen = 0; //--> jum HK
				$status_kehadiran = ''; // default tidak masuk
				$tgl = $d1->format('Y-m-d');
				$is_hari_libur = false;
				if ($hari_libur[$tgl]) {
					$is_hari_libur = true;
				}

				/* tipe_karyawan_id: 1(KHT),2(KHL),3(PKWT),4(NSB),5(STAFF) */
				// if ($hd['tipe_karyawan_id'] == 2) {
				// 	$gaji_per_hari_efektif = $hd['gapok'] / 25;
				// } else {
				// 	 $gaji_per_hari_efektif = $hd['gapok'] / $hari_masuk_efektif;
				// }
				$gaji_per_hari_efektif = $hd['gapok'] / 25;

				/* ngambil data absensi per periode */
				$qAbsensi = "SELECT * FROM payroll_absensi a inner join hrms_jenis_absensi b on a.jenis_absensi_id=b.id 
					where a.karyawan_id= " . $hd['karyawan_id'] . " 
					and tanggal ='" . $tgl . "' 
					;";
				$resAbsensi = $this->db->query($qAbsensi)->result_array();
				$jum_hari_dibayar = 0;
				$jum_jam_lembur = 0;
				$jum_hari_hadir = 0;
				$lembur = 0;
				$premi_absensi = 0;
				$upah_absensi = 0;
				foreach ($resAbsensi as $absensiKaryawan) {
					// $jum_hari_dibayar++;
					// $upah_absensi = $upah_absensi + $absensiKaryawan['premi'];
					if ($absensiKaryawan['kode'] == $kode_hadir) { // Jumlah Hadir 
						$jum_absen++;
						// $jum_absen_perkaryawan=$jum_absen_perkaryawan+$jum_absen;
						$upah_absensi = $upah_absensi + $gaji_per_hari_efektif;
					}
					$status_kehadiran = $absensiKaryawan['kode'];
				}
				// $upah_absensi = $hd['gapok'] * $jum_hari_hadir;
				// $hk_potongan_hari =	$hari_masuk_efektif - $jum_hari_dibayar;
				// $hk_potongan_gaji =	$hk_potongan_hari * $gaji_per_hari_efektif;
				// $qLembur = "SELECT * FROM  payroll_lembur where karyawan_id= " . $hd['karyawan_id'] . " 
				// 	and	tanggal ='" . $tgl . "' ;";
				// $resLembur = $this->db->query($qLembur)->result_array();
				// foreach ($resLembur as $lemburKaryawan) {
				// 	$jum_jam_lembur = $jum_jam_lembur + $lemburKaryawan['jumlah_jam'];
				// 	$lembur = $lembur + $lemburKaryawan['nilai_lembur'];
				// }

				/* ngambil data bkm BKM Umum per periode */
				$resBkmUmum =	$this->db->query("select *, b.jumlah_hk as jum_hk,c.kode as kode_absen from est_bkm_umum_ht a inner join 
				est_bkm_umum_dt b on a.id=b.bkm_umum_id 
				inner join hrms_jenis_absensi c on b.jenis_absensi_id=c.id 
				 where b.karyawan_id= " . $hd['karyawan_id'] . " 
				and tanggal ='" . $tgl . "'  ")->result_array();
				$premi_umum = 0;
				$upah_umum = 0;
				foreach ($resBkmUmum as $umum) {
					// $jum_absen++;
					if ($umum['kode_absen'] == $kode_hadir) {
						$jum_absen = $jum_absen + $umum['jum_hk'];
					}
					$status_kehadiran = $umum['kode_absen'];
					// $jum_absen_perkaryawan=$jum_absen_perkaryawan+$jum_absen;
					$premi_umum = $premi_umum + $umum['premi'];
					$upah_umum = $upah_umum + $umum['rupiah_hk'];
				}


				/* ngambil data bkm panen per periode */
				$resPanen =	$this->db->query("select * from est_bkm_panen_ht a inner join 
					est_bkm_panen_dt b on a.id=b.bkm_panen_id  where karyawan_id= " . $hd['karyawan_id'] . "  
					and tanggal ='" . $tgl . "' and is_premi_kontanan=0 ")->result_array();
				$premi_panen = 0;
				$upah_panen = 0;
				$denda_panen = 0;
				foreach ($resPanen as $panen) {
					// $jum_absen++;
					$jum_absen = $jum_absen + $panen['jumlah_hk'];
					// $jum_absen_perkaryawan=$jum_absen_perkaryawan+$jum_absen;
					$upah_panen = $upah_panen + $panen['rp_hk'];
					$premi_panen = $premi_panen + $panen['premi_panen'];
					$denda_panen = $denda_panen + $panen['denda_panen'];
					$status_kehadiran = $kode_hadir;
				}

				/* ngambil data bkm pemeliharaan per periode */
				$resPemeliharaan =	$this->db->query("select * from est_bkm_pemeliharaan_ht a inner join 
						est_bkm_pemeliharaan_dt b on a.id=b.bkm_pemeliharaan_id  where karyawan_id= " . $hd['karyawan_id'] . "  
						and tanggal ='" . $tgl . "'  ")->result_array();
				$premi_pemeliharaan = 0;
				$upah_pemeliharaan = 0;
				$denda_pemeliharaan = 0;
				foreach ($resPemeliharaan as $pemeliharaan) {
					// $jum_absen++;
					$jum_absen = $jum_absen + $pemeliharaan['jumlah_hk'];
					// $jum_absen_perkaryawan=$jum_absen_perkaryawan+$jum_absen;
					$premi_pemeliharaan = $premi_pemeliharaan + $pemeliharaan['premi'];
					$upah_pemeliharaan = $upah_pemeliharaan + $pemeliharaan['rupiah_hk'];
					$status_kehadiran = $kode_hadir;
					$denda_pemeliharaan = $denda_pemeliharaan + $panen['denda_pemeliharaan'];
				}

				/* ngambil data bkm Traksi per periode */
				$resTraksi =	$this->db->query("select * from trk_kegiatan_kendaraan_ht a inner join 
				trk_kegiatan_kendaraan_dt b on a.id=b.trk_kegiatan_kendaraan_id  where b.karyawan_id= " . $hd['karyawan_id'] . " 
				and tanggal ='" . $tgl . "'  ")->result_array();
				$premi_traksi = 0;
				$upah_traksi = 0;
				foreach ($resTraksi as $traksi) {
					// $jum_absen++;
					$jum_absen = $jum_absen + $traksi['jumlah_hk'];
					// $jum_absen_perkaryawan=$jum_absen_perkaryawan+$jum_absen;
					$premi_traksi = $premi_traksi + $traksi['premi'];
					$upah_traksi = $upah_traksi + $traksi['rupiah_hk'];
					$status_kehadiran = $kode_hadir;
				}

				/* ngambil data bkm workshop per periode */
				$resWorkshop =	$this->db->query("select * from wrk_kegiatan_ht a inner join 
				wrk_kegiatan_dt b on a.id=b.wrk_kegiatan_id  where b.karyawan_id= " . $hd['karyawan_id'] . " 
				and tanggal ='" . $tgl . "'  ")->result_array();
				$premi_workshop = 0;
				$upah_workshop = 0;
				foreach ($resWorkshop as $workshop) {
					// $jum_absen++;
					$jum_absen = $jum_absen + $workshop['jumlah_hk'];
					// $jum_absen_perkaryawan=$jum_absen_perkaryawan+$jum_absen;
					$premi_workshop = $premi_workshop + $workshop['premi'];
					$upah_workshop = $upah_workshop + $workshop['rupiah_hk'];
					$status_kehadiran = $kode_hadir;
				}

				/* ngambil data Mandor dari bkm panen per periode */
				$resPanen =	$this->db->query("select * from est_bkm_panen_ht where mandor_id= " . $hd['karyawan_id'] . " 
		 					and tanggal ='" . $tgl . "' and is_premi_kontanan=0 ")->result_array();
				$premi_panen_mandor = 0;
				$upah_panen_mandor = 0;
				foreach ($resPanen as $panen) {
					// $jum_absen++;
					$jum_absen = $jum_absen + $panen['jumlah_hk_mandor'];
					// $jum_absen_perkaryawan=$jum_absen_perkaryawan+$jum_absen;
					$upah_panen_mandor = $upah_panen_mandor + $panen['rp_hk_mandor'];
					$premi_panen_mandor = $premi_panen_mandor + $panen['premi_mandor'];
					$status_kehadiran = $kode_hadir;
				}
				/* ngambil data Krani dari bkm panen per periode */
				$resPanen =	$this->db->query("select * from est_bkm_panen_ht where kerani_id= " . $hd['karyawan_id'] . "  
					and tanggal ='" . $tgl . "' and is_premi_kontanan=0 ")->result_array();
				$premi_panen_kerani = 0;
				$upah_panen_kerani = 0;
				foreach ($resPanen as $panen) {
					// $jum_absen++;
					$jum_absen = $jum_absen + $panen['jumlah_hk_kerani'];
					// $jum_absen_perkaryawan=$jum_absen_perkaryawan+$jum_absen;
					$upah_panen_kerani = $upah_panen_kerani + $panen['rp_hk_kerani'];
					$premi_panen_kerani = $premi_panen_kerani + $panen['premi_kerani'];
					$status_kehadiran = $kode_hadir;
				}

				/* ngambil data Mandor dari bkm Pemeliharaan per periode */
				$resPanen =	$this->db->query("select * from est_bkm_pemeliharaan_ht where mandor_id= " . $hd['karyawan_id'] . " 
		 					and tanggal ='" . $tgl . "' ")->result_array();
				$premi_panen_mandor = 0;
				$upah_panen_mandor = 0;
				foreach ($resPanen as $panen) {
					// $jum_absen++;
					$jum_absen = $jum_absen + $panen['jumlah_hk_mandor'];
					// $jum_absen_perkaryawan=$jum_absen_perkaryawan+$jum_absen;
					$upah_panen_mandor = $upah_panen_mandor + $panen['rp_hk_mandor'];
					$premi_panen_mandor = $premi_panen_mandor + $panen['premi_mandor'];
					$status_kehadiran = $kode_hadir;
				}
				/* ngambil data Krani dari bkm Pemeliharaan per periode */
				$resPanen =	$this->db->query("select * from est_bkm_pemeliharaan_ht where kerani_id= " . $hd['karyawan_id'] . "  
					and tanggal ='" . $tgl . "'  ")->result_array();
				$premi_panen_kerani = 0;
				$upah_panen_kerani = 0;
				foreach ($resPanen as $panen) {
					// $jum_absen++;
					$jum_absen = $jum_absen + $panen['jumlah_hk_kerani'];
					// $jum_absen_perkaryawan=$jum_absen_perkaryawan+$jum_absen;
					$upah_panen_kerani = $upah_panen_kerani + $panen['rp_hk_kerani'];
					$premi_panen_kerani = $premi_panen_kerani + $panen['premi_kerani'];
					$status_kehadiran = $kode_hadir;
				}
				$upah = $upah_absensi + $upah_panen + $upah_pemeliharaan + $upah_traksi + $upah_workshop + $upah_panen_mandor + $upah_panen_kerani;
				$premi = $premi_panen + $premi_pemeliharaan + $premi_traksi + $premi_workshop + $upah_panen_mandor + $upah_panen_kerani;
				$jum_absen_str = $jum_absen == 0 ? "" : $jum_absen;
				if ($jum_absen > 0) {
					$status = $jum_absen;
					$kehadiran[$hd['karyawan_id']][$kode_hadir] = $kehadiran[$hd['karyawan_id']][$kode_hadir] + $jum_absen;
				} else {
					$status = $status_kehadiran;
					if ($status_kehadiran == $kode_hadir) {
						$status = '0';
					} else {
						$kehadiran[$hd['karyawan_id']][$status_kehadiran] = $kehadiran[$hd['karyawan_id']][$status_kehadiran] + 1;
					}
				}

				//$actual_link ='http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
				$actual_link = "http://$_SERVER[HTTP_HOST]" . "/plantation-api/api/GlobalReport/getAbsensiKebunDetail/" . $tgl . "/" . $hd['karyawan_id'] . "";
				if ($jum_absen > 1) {
					$html = $html . "<td style='text-align: center;' bgcolor='red'> <a href='" . $actual_link . "" . "' target='_blank' > " . $status . " </a></td>";
				} else {
					if ($is_hari_libur) {
						$html = $html . "<td style='text-align: center;' bgcolor='yellow'> <a href='" . $actual_link . "" . "' target='_blank'> " . $status . " </a></td>";
					} else {
						$html = $html . "<td style='text-align: center;' > <a href='" . $actual_link . "" . "' target='_blank'> " . $status . " </a></td>";
					}
				}
				$d1->modify('+1 day');
				$jum_absen_perkaryawan = $jum_absen_perkaryawan + $jum_absen;
			}

			foreach ($resKodeAbsensi as $key => $value) {
				$html = $html . "<td style='text-align: center'>" . $kehadiran[$hd['karyawan_id']][$value['kode']] . "</td>";
			}
			$html = $html . "</tr>";
		}

		// $html = $html . "<tr>";
		// $html = $html . "<td style='text-align: center'> </td>";
		// $html = $html . "<td style='text-align: center'></td>";
		// $html = $html . "<td style='text-align: center'></td>";
		// $html = $html . "<td style='text-align: center'></td>";
		// $d1 = new DateTime($data_post['tgl_awal']);
		// while ($d1 <= $d2) {
		// 	$ddmm = $d1->format('d-m');
		// 	$html = $html . "<td style='text-align: center'></td>";
		// 	$d1->modify('+1 day');
		// }
		// $html = $html . "<td style='text-align: right'> </td>";
		// $html = $html . "</tr>";
		$html = $html . "</table>";
		if ($format_laporan == 'xls') {
			echo $html;
		} else {
			$this->set_response(array("status" => "OK", "data" => $html), REST_Controller::HTTP_OK);
		}

		// if (($hasil['jum']) > 0) {

		// 	$this->set_response(array("status" => "OK", "data" => 'Proses berhasil.' . $hasil['jum'] . " data diproses"), REST_Controller::HTTP_CREATED);
		// } else {
		// 	$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data yang diproses"), REST_Controller::HTTP_OK);
		// }
	}
	public function getRekapAbsensiKebunByUpah()
	{
		$data_post = $this->post();

		$catuKg = array();
		$catuRp = array();
		// $qCatu = "SELECT * FROM  hrms_catuberas_setting ";
		// $resCatu = $this->db->query($qCatu)->result_array();
		// foreach ($resCatu as $c) {
		// 	$catuKg[$c['status_karyawan']] = $c['jumlah_kg'];
		// 	$catuRp[$c['status_karyawan']] = $c['jumlah_rupiah'];
		// }

		// /* hitung jumlah libur */
		// $qLibur = "SELECT count(*)as jum_libur  FROM  hrms_libur where
		// tanggal >='" . $periodeGaji['tgl_awal'] . "' and tanggal <='" . $periodeGaji['tgl_akhir'] . "' ";
		// $libur = $this->db->query($qLibur)->row_array();
		// $jum_libur = $libur['jum_libur'];

		/* hitung jumlah hari dlm 1 periode */
		$d1 = new DateTime($data_post['tgl_awal']);
		$d2 = new DateTime($data_post['tgl_akhir']);
		$afdeling_id = $data_post['afdeling_id'];
		$format_laporan = $data_post['format_laporan'];
		$interval = $d1->diff($d2);
		$jumlah_hari = $interval->days;

		/* hitung jumlah hari masuk 1 periode */
		// $hari_masuk_efektif = $jumlah_hari_sebulan - $jum_libur;

		// Query utk nagambil master karyawan dan  gaji pokoknya 
		$html = "";
		$q0 = "SELECT a.*,b.nip,b.nama, c.nama as jabatan,b.status_pajak FROM  payroll_karyawan_gaji a 
		inner join karyawan b on a.karyawan_id=b.id
		left join payroll_jabatan c on b.jabatan_id=c.id
		where b.status_id=1 and b.lokasi_tugas_id=" . $data_post['lokasi_id'] . "";
		if ($afdeling_id) {
			$q0 =	$q0 . " and b.sub_bagian_id=" . $afdeling_id . "";
		}
		$q0 =	$q0 . " order by b.nama";
		$arrhd = $this->db->query($q0)->result_array();
		$no = 0;
		$strNo = '';
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
		$html = $html . "	
		<table   border='1' width='100%' style='border-collapse: collapse;'>
		<thead>
		<tr>
			<td rowspan=3 >No</td>
			<td rowspan=3>NIK</td>
			<td rowspan=3>Nama</td>
			<td rowspan=3>Jabatan</td>
			<td colspan=" . (($jumlah_hari + 1) * 3) . "  style='text-align: right'> Periode  </td>
			
			<td rowspan=3  style='text-align: right'>Jumlah</td>
		</tr>
		";
		$html = $html . "<tr>";
		while ($d1 <= $d2) {
			$ddmm = $d1->format('d-m');
			$dd = $d1->format('d');
			// $html = $html . "<td colspan='2' style='text-align: center'>" . $ddmm . "</td>";
			$html = $html . "<td colspan='3' style='text-align: center'>" . $dd . "</td>";
			$d1->modify('+1 day');
		}
		$html = $html . "</tr>";
		$d1 = new DateTime($data_post['tgl_awal']);
		while ($d1 <= $d2) {
			$ddmm = $d1->format('d-m');
			$html = $html . "<td style='text-align: right'>Upah</td>";
			$html = $html . "<td style='text-align: right'>Premi</td>";
			$html = $html . "<td style='text-align: right'>Denda</td>";
			$d1->modify('+1 day');
		}
		$html = $html . "</tr> </thead>";


		foreach ($arrhd as $hd) {
			$jum_absen_perkaryawan = 0;
			$gaji_per_hari_efektif = $hd['gapok'] / 25;
			$jum_absen_perkaryawan = 0;
			$upah = 0;
			$upah_absensi = 0;
			$upah_panen = 0;
			$upah_pemeliharaan = 0;
			$upah_traksi = 0;
			$upah_workshop = 0;
			$upah_umum = 0;
			$premi = 0;
			$denda = 0;
			$lembur = 0;
			$premi_panen = 0;
			$premi_pemeliharaan = 0;
			$premi_traksi = 0;
			$premi_umum = 0;
			$premi_workshop = 0;
			$upah_panen_kerani = 0;
			$upah_panen_kerani = 0;
			$upah_panen_mandor = 0;
			$premi_panen_mandor = 0;
			$upah_pemeliharaan_kerani = 0;
			$premi_pemeliharaan_kerani = 0;
			$upah_pemeliharaan_mandor = 0;
			$premi_pemeliharaan_mandor = 0;
			$upah_perkaryawan = 0;
			$premi_perkaryawan = 0;
			$denda_perkaryawan = 0;
			$pendapatan = 0;
			$potongan = 0;
			$no++;
			//$actual_link ='http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			$actual_link = "http://$_SERVER[HTTP_HOST]" . "/plantation-api/api/GlobalReport/getAbsensiKebunDetailperKaryawan/" . $data_post['tgl_awal'] . "/" . $data_post['tgl_akhir'] .  "/" . $hd['karyawan_id'] . "";
			$html = $html . "<tr>";
			$html = $html . "<td style='text-align: center'>" . $no . "</td>";
			$html = $html . "<td style='text-align: left'> <a href='" . $actual_link . "" . "' target='_blank'> " . $hd['nip'] . " </a></td>";
			$html = $html . "<td style='text-align: left'> <a href='" . $actual_link . "" . "' target='_blank'> " . $hd['nama'] . " </a></td>";
			$html = $html . "<td style='text-align: left'>" . $hd['jabatan'] .  "</td>";
			$d1 = new DateTime($data_post['tgl_awal']);
			while ($d1 <= $d2) {
				$jum_absen = 0;

				$tgl = $d1->format('Y-m-d');
				/* tipe_karyawan_id: 1(KHT),2(KHL),3(PKWT),4(NSB),5(STAFF) */
				// if ($hd['tipe_karyawan_id'] == 2) {
				// 	$gaji_per_hari_efektif = $hd['gapok'] / 25;
				// } else {
				// 	 $gaji_per_hari_efektif = $hd['gapok'] / $hari_masuk_efektif;
				// }
				$gaji_per_hari_efektif = $hd['gapok'] / 25;

				/* ngambil data absensi per periode */
				$qAbsensi = "SELECT * FROM payroll_absensi a inner join hrms_jenis_absensi b on a.jenis_absensi_id=b.id 
					where a.karyawan_id= " . $hd['karyawan_id'] . " 
					and tanggal ='" . $tgl . "' 
					and b.tipe ='DIBAYAR';";
				$resAbsensi = $this->db->query($qAbsensi)->result_array();
				$jum_hari_dibayar = 0;
				$jum_jam_lembur = 0;
				$jum_hari_hadir = 0;
				$lembur = 0;
				$premi_absensi = 0;
				$upah_absensi = 0;
				foreach ($resAbsensi as $absensiKaryawan) {
					// $jum_hari_dibayar++;
					// $upah_absensi = $upah_absensi + $absensiKaryawan['premi'];
					if ($absensiKaryawan['kode'] == 'H') { // Jumlah Hadir 
						$jum_absen++;
						$jum_absen_perkaryawan++;
						$upah_absensi = $upah_absensi + $gaji_per_hari_efektif;
					}
				}
				// $upah_absensi = $hd['gapok'] * $jum_hari_hadir;
				// $hk_potongan_hari =	$hari_masuk_efektif - $jum_hari_dibayar;
				// $hk_potongan_gaji =	$hk_potongan_hari * $gaji_per_hari_efektif;

				$qLembur = "SELECT * FROM  payroll_lembur where karyawan_id= " . $hd['karyawan_id'] . " 
					and	tanggal ='" . $tgl . "' ;";
				$resLembur = $this->db->query($qLembur)->result_array();
				foreach ($resLembur as $lemburKaryawan) {
					$jum_jam_lembur = $jum_jam_lembur + $lemburKaryawan['jumlah_jam'];
					$lembur = $lembur + $lemburKaryawan['nilai_lembur'];
				}

				/* ngambil data bkm BKM Umum per periode */
				$resBkmUmum =	$this->db->query("select * from est_bkm_umum_ht a inner join 
				est_bkm_umum_dt b on a.id=b.bkm_umum_id  where b.karyawan_id= " . $hd['karyawan_id'] . " 
				and tanggal ='" . $tgl . "'  ")->result_array();
				$premi_umum = 0;
				$upah_umum = 0;
				foreach ($resBkmUmum as $umum) {
					// $jum_absen++;
					$jum_absen = $jum_absen + $umum['jumlah_hk'];
					// $jum_absen_perkaryawan=$jum_absen_perkaryawan+$jum_absen;
					$premi_umum = $premi_umum + $umum['premi'];
					$upah_umum = $upah_umum + $umum['rupiah_hk'];
				}


				/* ngambil data bkm panen per periode */
				$resPanen =	$this->db->query("select * from est_bkm_panen_ht a inner join 
					est_bkm_panen_dt b on a.id=b.bkm_panen_id  where karyawan_id= " . $hd['karyawan_id'] . "  
					and tanggal ='" . $tgl . "' and is_premi_kontanan=0 ")->result_array();
				$premi_panen = 0;
				$upah_panen = 0;
				$denda_panen = 0;
				foreach ($resPanen as $panen) {
					$jum_absen++;
					$jum_absen_perkaryawan++;
					$upah_panen = $upah_panen + $panen['rp_hk'];
					$premi_panen = $premi_panen + ($panen['premi_panen'] + $panen['premi_brondolan']);
					$denda_panen = $denda_panen + $panen['denda_panen'];
				}

				/* ngambil data bkm pemeliharaan per periode */
				$resPemeliharaan =	$this->db->query("select * from est_bkm_pemeliharaan_ht a inner join 
						est_bkm_pemeliharaan_dt b on a.id=b.bkm_pemeliharaan_id  where karyawan_id= " . $hd['karyawan_id'] . "  
						and tanggal ='" . $tgl . "'  ")->result_array();
				$premi_pemeliharaan = 0;
				$upah_pemeliharaan = 0;
				$denda_pemeliharaan = 0;
				foreach ($resPemeliharaan as $pemeliharaan) {
					$jum_absen++;
					$jum_absen_perkaryawan++;
					$premi_pemeliharaan = $premi_pemeliharaan + $pemeliharaan['premi'];
					$upah_pemeliharaan = $upah_pemeliharaan + $pemeliharaan['rupiah_hk'];
					$denda_pemeliharaan = $denda_pemeliharaan + $pemeliharaan['denda_pemeliharaan'];
				}

				/* ngambil data bkm Traksi per periode */
				$resTraksi =	$this->db->query("select * from trk_kegiatan_kendaraan_ht a inner join 
				trk_kegiatan_kendaraan_dt b on a.id=b.trk_kegiatan_kendaraan_id  where b.karyawan_id= " . $hd['karyawan_id'] . " 
				and tanggal ='" . $tgl . "'  ")->result_array();
				$premi_traksi = 0;
				$upah_traksi = 0;
				$denda_traksi = 0;
				foreach ($resTraksi as $traksi) {
					$jum_absen++;
					$jum_absen_perkaryawan++;
					$premi_traksi = $premi_traksi + $traksi['premi'];
					$upah_traksi = $upah_traksi + $traksi['rupiah_hk'];
					$denda_traksi = $denda_traksi + $traksi['denda_traksi'];
				}

				/* ngambil data bkm workshop per periode */
				$resWorkshop =	$this->db->query("select * from wrk_kegiatan_ht a inner join 
				wrk_kegiatan_dt b on a.id=b.wrk_kegiatan_id  where b.karyawan_id= " . $hd['karyawan_id'] . " 
				and tanggal ='" . $tgl . "'  ")->result_array();
				$premi_workshop = 0;
				$upah_workshop = 0;
				foreach ($resWorkshop as $workshop) {
					$jum_absen++;
					$jum_absen_perkaryawan++;
					$premi_workshop = $premi_workshop + $workshop['premi'];
					$upah_workshop = $upah_workshop + $workshop['rupiah_hk'];
				}

				/* ngambil data Mandor dari bkm panen per periode */
				$resPanen =	$this->db->query("select * from est_bkm_panen_ht where mandor_id= " . $hd['karyawan_id'] . " 
		 					and tanggal ='" . $tgl . "' and is_premi_kontanan=0")->result_array();
				$premi_panen_mandor = 0;
				$upah_panen_mandor = 0;
				$denda_panen_mandor = 0;
				foreach ($resPanen as $panen) {
					$jum_absen++;
					$jum_absen_perkaryawan++;
					$upah_panen_mandor = $upah_panen_mandor + $panen['rp_hk_mandor']; //	$gaji_per_hari_efektif; //
					$premi_panen_mandor = $premi_panen_mandor + $panen['premi_mandor'];
					$denda_panen_mandor = $denda_panen_mandor + $panen['denda_mandor'];
				}
				/* ngambil data Krani dari bkm panen per periode */
				$resPanen =	$this->db->query("select * from est_bkm_panen_ht where kerani_id= " . $hd['karyawan_id'] . "  
					and tanggal ='" . $tgl . "' and is_premi_kontanan=0 ")->result_array();
				$premi_panen_kerani = 0;
				$upah_panen_kerani = 0;
				$denda_panen_kerani = 0;
				foreach ($resPanen as $panen) {
					$jum_absen++;
					$jum_absen_perkaryawan++;
					$upah_panen_kerani = $upah_panen_kerani + $panen['rp_hk_kerani']; //	$gaji_per_hari_efektif; //
					$premi_panen_kerani = $premi_panen_kerani + $panen['premi_kerani'];
					$denda_panen_kerani = $denda_panen_kerani + $panen['denda_kerani'];
				}

				/* ngambil data Mandor dari bkm Pemeliharaan per periode */
				$resPanen =	$this->db->query("select * from est_bkm_pemeliharaan_ht where mandor_id= " . $hd['karyawan_id'] . " 
		 					and tanggal ='" . $tgl . "' ")->result_array();
				$premi_pemeliharaan_mandor = 0;
				$upah_pemeliharaan_mandor = 0;
				$denda_pemeliharaan_mandor = 0;
				foreach ($resPanen as $panen) {
					$jum_absen++;
					$jum_absen_perkaryawan++;
					$upah_pemeliharaan_mandor = $upah_pemeliharaan_mandor +  $panen['rp_hk_mandor']; //	$gaji_per_hari_efektif; //
					$premi_pemeliharaan_mandor = $premi_pemeliharaan_mandor + $panen['premi_mandor'];
					$denda_pemeliharaan_mandor = $denda_pemeliharaan_mandor + $panen['denda_mandor'];
				}
				/* ngambil data Krani dari bkm Pemeliharaan per periode */
				$resPanen =	$this->db->query("select * from est_bkm_pemeliharaan_ht where kerani_id= " . $hd['karyawan_id'] . "  
					and tanggal ='" . $tgl . "'  ")->result_array();
				$premi_pemeliharaan_kerani = 0;
				$upah_pemeliharaan_kerani = 0;
				$denda_pemeliharaan_kerani = 0;
				foreach ($resPanen as $panen) {
					$jum_absen++;
					$jum_absen_perkaryawan++;
					$upah_pemeliharaan_kerani = $upah_pemeliharaan_kerani + $panen['rp_hk_kerani']; //	$gaji_per_hari_efektif; 
					$premi_pemeliharaan_kerani = $premi_pemeliharaan_kerani + $panen['premi_kerani'];
					$denda_pemeliharaan_kerani = $denda_pemeliharaan_kerani + $panen['denda_kerani'];
				}

				/* ngambil data Pendapatan dari Transksi Payroll Pendapatan per periode */
				$pendapatan = 0;
				$qPendapatan = "SELECT * FROM  payroll_pendapatan where karyawan_id= " . $hd['karyawan_id'] . " 
					and	tanggal ='" . $tgl . "' ;";
				$resPendapatan = $this->db->query($qPendapatan)->result_array();
				foreach ($resPendapatan as $pendapatanKaryawan) {
					$pendapatan = $pendapatan + $pendapatanKaryawan['nilai'];
				}
				/* ngambil data potongan dari Transksi Payroll Potongan per periode */
				$potongan = 0;
				$qPotongan = "SELECT * FROM  payroll_potongan where karyawan_id= " . $hd['karyawan_id'] . " 
					and	tanggal ='" . $tgl . "' ;";
				$resPotongan = $this->db->query($qPotongan)->result_array();
				foreach ($resPotongan as $potonganKaryawan) {
					$potongan = $potongan + $potonganKaryawan['nilai'];
				}

				$upah = $upah_absensi + $upah_umum + $upah_panen + $upah_pemeliharaan + $upah_traksi + $upah_workshop + $upah_panen_mandor + $upah_panen_kerani + $upah_pemeliharaan_mandor + $upah_pemeliharaan_kerani + $pendapatan;
				$premi = $lembur + $premi_umum + $premi_panen + $premi_pemeliharaan + $premi_traksi + $premi_workshop + $premi_panen_mandor + $premi_panen_kerani + $premi_pemeliharaan_mandor + $premi_pemeliharaan_kerani;
				$upah_perkaryawan = $upah_perkaryawan + $upah;
				$denda = $denda_panen + $denda_pemeliharaan + $denda_panen_mandor + $denda_panen_kerani + $denda_pemeliharaan_mandor + $denda_pemeliharaan_kerani + $potongan + $denda_traksi;
				$denda_perkaryawan = $denda_perkaryawan + $denda;
				$premi_perkaryawan = $premi_perkaryawan + $premi;

				$jum_absen_str = $jum_absen == 0 ? "" : $jum_absen;
				//$actual_link ='http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
				$actual_link = "http://$_SERVER[HTTP_HOST]" . "/plantation-api/api/GlobalReport/getAbsensiKebunDetail/" . $tgl . "/" . $hd['karyawan_id'] . "";
				$html = $html . "<td style='text-align: right'> <a href='" . $actual_link . "" . "' target='_blank'> " . number_format($upah) . " </a></td>";
				$html = $html . "<td style='text-align: right'> <a href='" . $actual_link . "" . "' target='_blank'> " . number_format($premi) . " </a></td>";
				$html = $html . "<td style='text-align: right'> <a href='" . $actual_link . "" . "' target='_blank'> " . number_format($denda) . " </a></td>";
				$d1->modify('+1 day');
			}
			$html = $html . "<td style='text-align: right'>" . number_format($upah_perkaryawan + $premi_perkaryawan - $denda_perkaryawan) . " </td>";
			$html = $html . "</tr>";
		}

		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: center'> </td>";
		$html = $html . "<td style='text-align: center'></td>";
		$html = $html . "<td style='text-align: center'></td>";
		$html = $html . "<td style='text-align: center'></td>";
		$d1 = new DateTime($data_post['tgl_awal']);
		while ($d1 <= $d2) {
			$ddmm = $d1->format('d-m');
			$html = $html . "<td style='text-align: center'></td>";
			$d1->modify('+1 day');
		}
		$html = $html . "<td style='text-align: right'> </td>";
		$html = $html . "</tr>";
		$html = $html . "</table>";

		if ($format_laporan == 'xls') {
			echo $html;
			return;
		} else {
			$this->set_response(array("status" => "OK", "data" => $html), REST_Controller::HTTP_OK);
			return;
		}


		// if (($hasil['jum']) > 0) {

		// 	$this->set_response(array("status" => "OK", "data" => 'Proses berhasil.' . $hasil['jum'] . " data diproses"), REST_Controller::HTTP_CREATED);
		// } else {
		// 	$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data yang diproses"), REST_Controller::HTTP_OK);
		// }
	}
	public function getAbsensiKebunDetail_get($tgl, $karyawan_id)
	{

		$html = "";
		$q0 = "SELECT a.*,b.nip,b.nama, c.nama as jabatan,b.status_pajak FROM  payroll_karyawan_gaji a 
		inner join karyawan b on a.karyawan_id=b.id
		left join payroll_jabatan c on b.jabatan_id=c.id
		where b.id=" . $karyawan_id  . "";
		$hd = $this->db->query($q0)->row_array();
		$no = 0;
		$strNo = '';
		$html = $html . "	
		<table   border='0' width='50%' style='border-collapse: collapse;'>
		<tr>
			<td>Nip</td>
			<td>:</td>
			<td>" . $hd['nip'] . "</td>
		 </tr>
		 <tr>
			<td>Nama</td>
			<td>:</td>
			<td>" . $hd['nama'] . "</td>
	   </tr>
	   <tr>
			<td>Tanggal</td>
			<td>:</td>
			<td>" . $tgl . "</td>
		 </tr>
		 </table>
		";

		$html = $html . "	
		<table   border='1' width='100%' style='border-collapse: collapse;'>
		<thead>
		<tr>
			<td>No.</td>
			<td>Jenis</td>
			<td>No Transaksi</td>
			<td>Tanggal</td>
			<td>HK</td>
			<td>Upah</td>
			<td>Premi</td>
			<td>Jam Lembur</td>
			<td>Rp Lembur</td>
			<td rowspan=2  style='text-align: center'>Jumlah</td>
		</tr></thead>
		";

		$jum_absen_perkaryawan = 0;
		$gaji_per_hari_efektif = $hd['gapok'] / 25;

		/* ngambil data absensi per periode */
		$qAbsensi = "SELECT * FROM payroll_absensi a inner join hrms_jenis_absensi b on a.jenis_absensi_id=b.id 
					where a.karyawan_id= " . $hd['karyawan_id'] . " 
					and tanggal ='" . $tgl . "' 
					and b.tipe ='DIBAYAR';";
		$resAbsensi = $this->db->query($qAbsensi)->result_array();
		$jum_jam_lembur = 0;
		$lembur = 0;
		$upah_absensi = 0;
		$jum_absen = 0;
		foreach ($resAbsensi as $absensiKaryawan) {
			// $jum_hari_dibayar++;
			// $upah_absensi = $upah_absensi + $absensiKaryawan['premi'];
			if ($absensiKaryawan['kode'] == 'H') { // Jumlah Hadir 
				$jum_absen++;
				$jum_absen_perkaryawan++;
				$upah_absensi = $upah_absensi + $gaji_per_hari_efektif;
				$no++;
				$html = $html . "<tr>";
				$html = $html . "<td style='text-align: center'>" . $no . "</td>";
				$html = $html . "<td style='text-align: center'>ABSEN</td>";
				$html = $html . "<td style='text-align: center'></td>";
				$html = $html . "<td style='text-align: center'>" . $absensiKaryawan['tanggal'] . "</td>";
				$html = $html . "<td style='text-align: right'>1</td>";
				$html = $html . "<td style='text-align: right'>" . $gaji_per_hari_efektif .  "</td>";
				$html = $html . "<td style='text-align: right'>0</td>";
				$html = $html . "<td style='text-align: right'>0</td>";
				$html = $html . "<td style='text-align: right'>0</td>";
				$html = $html . "<td style='text-align: right'>" . $gaji_per_hari_efektif .  "</td>";
				$html = $html . "</tr>";
			}
		}
		// $upah_absensi = $hd['gapok'] * $jum_hari_hadir;
		// $hk_potongan_hari =	$hari_masuk_efektif - $jum_hari_dibayar;
		// $hk_potongan_gaji =	$hk_potongan_hari * $gaji_per_hari_efektif;
		$qLembur = "SELECT * FROM  payroll_lembur where karyawan_id= " . $hd['karyawan_id'] . " 
					and	tanggal ='" . $tgl . "' ;";
		$resLembur = $this->db->query($qLembur)->result_array();
		foreach ($resLembur as $lemburKaryawan) {
			$jum_jam_lembur = $jum_jam_lembur + $lemburKaryawan['jumlah_jam'];
			$lembur = $lembur + $lemburKaryawan['nilai_lembur'];
			$no++;
			$html = $html . "<tr>";
			$html = $html . "<td style='text-align: center'>" . $no . "</td>";
			$html = $html . "<td style='text-align: center'>LEMBUR</td>";
			$html = $html . "<td style='text-align: center'></td>";
			$html = $html . "<td style='text-align: center'>" . $lemburKaryawan['tanggal'] . "</td>";
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>" . $lemburKaryawan['jumlah_jam'] .  "</td>";
			$html = $html . "<td style='text-align: right'>" . $lemburKaryawan['nilai_lembur'] .  "</td>";
			$html = $html . "<td style='text-align: right'>" . $lemburKaryawan['nilai_lembur'] .  "</td>";
			$html = $html . "</tr>";
		}

		/* ngambil data bkm umum per periode */
		$resUmum =	$this->db->query("select * from est_bkm_umum_ht a inner join 
		est_bkm_umum_dt b on a.id=b.bkm_umum_id  where b.karyawan_id= " . $hd['karyawan_id'] . " 
				and tanggal ='" . $tgl . "'  ")->result_array();
		$premi_umum = 0;
		$upah_umum = 0;
		foreach ($resUmum as $umum) {
			$jum_absen = $jum_absen + $umum['jumlah_hk'];
			$jum_absen_perkaryawan++;
			$premi_umum = $premi_umum + $umum['premi'];
			$upah_umum = $upah_umum + $umum['rupiah_hk'];
			$no++;
			$html = $html . "<tr>";
			$html = $html . "<td style='text-align: center'>" . $no . "</td>";
			$html = $html . "<td style='text-align: center'>BKN Umum</td>";
			$html = $html . "<td style='text-align: center'>" . $umum['no_transaksi'] .  "</td>";
			$html = $html . "<td style='text-align: center'>" . $umum['tanggal'] . "</td>";
			$html = $html . "<td style='text-align: center'>" . $umum['jumlah_hk'] . "</td>";
			$html = $html . "<td style='text-align: right'>" . $umum['rupiah_hk'] .  "</td>";
			$html = $html . "<td style='text-align: right'>" . $umum['premi'] .  "</td>";
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>" . ($umum['rupiah_hk'] + $umum['premi']) .  "</td>";
			$html = $html . "</tr>";
		}


		/* ngambil data bkm panen per periode */
		$resPanen =	$this->db->query("select * from est_bkm_panen_ht a inner join 
					est_bkm_panen_dt b on a.id=b.bkm_panen_id  where karyawan_id= " . $hd['karyawan_id'] . "  
					and tanggal ='" . $tgl . "'  ")->result_array();
		$premi_panen = 0;
		$upah_panen = 0;
		$denda_panen = 0;
		foreach ($resPanen as $panen) {
			$jum_absen = $jum_absen + $panen['jumlah_hk'];
			$jum_absen_perkaryawan++;
			$upah_panen = $upah_panen + $panen['rp_hk'];
			$premi_panen = $premi_panen + $panen['premi_panen'];
			$denda_panen = $denda_panen + $panen['denda_panen'];
			$no++;
			$html = $html . "<tr>";
			$html = $html . "<td style='text-align: center'>" . $no . "</td>";
			$html = $html . "<td style='text-align: center'>PANEN</td>";
			$html = $html . "<td style='text-align: center'>" . $panen['no_transaksi'] .  "</td>";
			$html = $html . "<td style='text-align: center'>" . $panen['tanggal'] . "</td>";
			$html = $html . "<td style='text-align: center'>" . $panen['jumlah_hk'] . "</td>";
			$html = $html . "<td style='text-align: right'>" . $panen['rp_hk'] .  "</td>";
			$html = $html . "<td style='text-align: right'>" . $panen['premi_panen'] .  "</td>";
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>" . ($panen['rp_hk'] + $panen['premi_panen']) .  "</td>";

			$html = $html . "</tr>";
		}

		/* ngambil data bkm pemeliharaan per periode */
		$resPemeliharaan =	$this->db->query("select * from est_bkm_pemeliharaan_ht a inner join 
						est_bkm_pemeliharaan_dt b on a.id=b.bkm_pemeliharaan_id  where karyawan_id= " . $hd['karyawan_id'] . "  
						and tanggal ='" . $tgl . "'  ")->result_array();
		$premi_pemeliharaan = 0;
		$upah_pemeliharaan = 0;
		$denda_pemeliharaan = 0;
		foreach ($resPemeliharaan as $pemeliharaan) {
			$jum_absen = $jum_absen + $pemeliharaan['jumlah_hk'];
			$jum_absen_perkaryawan++;
			$premi_pemeliharaan = $premi_pemeliharaan + $pemeliharaan['premi'];
			$upah_pemeliharaan = $upah_pemeliharaan + $pemeliharaan['rupiah_hk'];
			$denda_pemeliharaan = $denda_pemeliharaan + $pemeliharaan['denda_pemeliharaan'];
			$no++;
			$html = $html . "<tr>";
			$html = $html . "<td style='text-align: center'>" . $no . "</td>";
			$html = $html . "<td style='text-align: center'>PEMELIHARAAN</td>";
			$html = $html . "<td style='text-align: center'>" . $pemeliharaan['no_transaksi'] .  "</td>";
			$html = $html . "<td style='text-align: center'>" . $pemeliharaan['tanggal'] . "</td>";
			$html = $html . "<td style='text-align: center'>" . $pemeliharaan['jumlah_hk'] . "</td>";
			$html = $html . "<td style='text-align: right'>" . $pemeliharaan['rupiah_hk'] .  "</td>";
			$html = $html . "<td style='text-align: right'>" . $pemeliharaan['premi'] .  "</td>";
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>" . ($pemeliharaan['rupiah_hk'] + $pemeliharaan['premi']) .  "</td>";
			$html = $html . "</tr>";
		}

		/* ngambil data bkm Traksi per periode */
		$resTraksi =	$this->db->query("select * from trk_kegiatan_kendaraan_ht a inner join 
				trk_kegiatan_kendaraan_dt b on a.id=b.trk_kegiatan_kendaraan_id  where b.karyawan_id= " . $hd['karyawan_id'] . " 
				and tanggal ='" . $tgl . "'  ")->result_array();
		$premi_traksi = 0;
		$upah_traksi = 0;
		foreach ($resTraksi as $traksi) {
			$jum_absen = $jum_absen + $traksi['jumlah_hk'];
			$jum_absen_perkaryawan++;
			$premi_traksi = $premi_traksi + $traksi['premi'];
			$upah_traksi = $upah_traksi + $traksi['rupiah_hk'];
			$no++;
			$html = $html . "<tr>";
			$html = $html . "<td style='text-align: center'>" . $no . "</td>";
			$html = $html . "<td style='text-align: center'>TRAKSI</td>";
			$html = $html . "<td style='text-align: center'>" . $traksi['no_transaksi'] .  "</td>";
			$html = $html . "<td style='text-align: center'>" . $traksi['tanggal'] . "</td>";
			$html = $html . "<td style='text-align: center'>" . $traksi['jumlah_hk'] . "</td>";
			$html = $html . "<td style='text-align: right'>" . $traksi['rupiah_hk'] .  "</td>";
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>" . $traksi['premi'] .  "</td>";
			$html = $html . "<td style='text-align: right'>" . ($traksi['rupiah_hk'] + $traksi['premi']) .  "</td>";
			$html = $html . "</tr>";
		}

		/* ngambil data bkm workshop per periode */
		$resWorkshop =	$this->db->query("select * from wrk_kegiatan_ht a inner join 
				wrk_kegiatan_dt b on a.id=b.wrk_kegiatan_id  where b.karyawan_id= " . $hd['karyawan_id'] . " 
				and tanggal ='" . $tgl . "'  ")->result_array();
		$premi_workshop = 0;
		$upah_workshop = 0;
		foreach ($resWorkshop as $workshop) {
			$jum_absen = $jum_absen + $workshop['jumlah_hk'];
			$jum_absen_perkaryawan++;
			$premi_workshop = $premi_workshop + $workshop['premi'];
			$upah_workshop = $upah_workshop + $workshop['rupiah_hk'];
			$no++;
			$html = $html . "<tr>";
			$html = $html . "<td style='text-align: center'>" . $no . "</td>";
			$html = $html . "<td style='text-align: center'>WORKSHOP</td>";
			$html = $html . "<td style='text-align: center'>" . $workshop['no_transaksi'] .  "</td>";
			$html = $html . "<td style='text-align: center'>" . $workshop['tanggal'] . "</td>";
			$html = $html . "<td style='text-align: center'>" . $workshop['jumlah_hk'] . "</td>";
			$html = $html . "<td style='text-align: right'>" . $workshop['rupiah_hk'] .  "</td>";
			$html = $html . "<td style='text-align: right'>" . $workshop['premi'] .  "</td>";
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>" . ($workshop['rupiah_hk'] + $workshop['premi']) .  "</td>";
			$html = $html . "</tr>";
		}

		/* ngambil data Mandor dari bkm panen per periode */
		$resPanen =	$this->db->query("select * from est_bkm_panen_ht where mandor_id= " . $hd['karyawan_id'] . " 
		 					and tanggal ='" . $tgl . "' ")->result_array();
		$premi_panen_mandor = 0;
		$upah_panen_mandor = 0;
		foreach ($resPanen as $panen) {
			$jum_absen = $jum_absen + $panen['jumlah_hk_mandor'];
			$upah_panen_mandor = $upah_panen_mandor + $panen['rp_hk_mandor'];
			$premi_panen_mandor = $premi_panen_mandor + $panen['premi_mandor'];
			$no++;
			$html = $html . "<tr>";
			$html = $html . "<td style='text-align: center'>" . $no . "</td>";
			$html = $html . "<td style='text-align: center'>MANDOR</td>";
			$html = $html . "<td style='text-align: center'>" . $panen['no_transaksi'] .  "</td>";
			$html = $html . "<td style='text-align: center'>" . $panen['tanggal'] . "</td>";
			$html = $html . "<td style='text-align: center'>" . $panen['jumlah_hk_mandor'] . "</td>";
			$html = $html . "<td style='text-align: right'>" . $upah_panen_mandor .  "</td>";
			$html = $html . "<td style='text-align: right'>" . $panen['premi_mandor'] .  "</td>";
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>" . ($upah_panen_mandor + $panen['premi_mandor'])  .  "</td>";
			$html = $html . "</tr>";
		}
		/* ngambil data Krani dari bkm panen per periode */
		$resPanen =	$this->db->query("select * from est_bkm_panen_ht where kerani_id= " . $hd['karyawan_id'] . "  
					and tanggal ='" . $tgl . "'  ")->result_array();
		$premi_panen_kerani = 0;
		$upah_panen_kerani = 0;
		foreach ($resPanen as $panen) {
			$jum_absen = $jum_absen + $panen['jumlah_hk_kerani'];
			$upah_panen_kerani = $upah_panen_kerani + $panen['rp_hk_kerani'];
			$premi_panen_kerani = $premi_panen_kerani + $panen['premi_kerani'];
			$no++;
			$html = $html . "<tr>";
			$html = $html . "<td style='text-align: center'>" . $no . "</td>";
			$html = $html . "<td style='text-align: center'>KERANI</td>";
			$html = $html . "<td style='text-align: center'>" . $panen['no_transaksi'] .  "</td>";
			$html = $html . "<td style='text-align: center'>" . $panen['tanggal'] . "</td>";
			$html = $html . "<td style='text-align: center'>" . $panen['jumlah_hk_kerani'] . "</td>";
			$html = $html . "<td style='text-align: right'>" . $upah_panen_kerani .  "</td>";
			$html = $html . "<td style='text-align: right'>" . $panen['premi_kerani'] .  "</td>";
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>" . ($upah_panen_kerani + $panen['premi_kerani']) .  "</td>";
			$html = $html . "</tr>";
		}

		/* ngambil data Mandor dari bkm Pemeliharaan per periode */
		$resPanen =	$this->db->query("select * from est_bkm_pemeliharaan_ht where mandor_id= " . $hd['karyawan_id'] . " 
		 					and tanggal ='" . $tgl . "' ")->result_array();
		$premi_pemeliharaan_mandor = 0;
		$upah_pemeliharaan_mandor = 0;
		foreach ($resPanen as $panen) {
			$jum_absen = $jum_absen + $panen['jumlah_hk_mandor'];
			$upah_pemeliharaan_mandor = $upah_pemeliharaan_mandor + $panen['rp_hk_mandor']; // $gaji_per_hari_efektif; //
			$premi_pemeliharaan_mandor = $premi_pemeliharaan_mandor + $panen['premi_mandor'];
			$no++;
			$html = $html . "<tr>";
			$html = $html . "<td style='text-align: center'>" . $no . "</td>";
			$html = $html . "<td style='text-align: center'>MANDOR</td>";
			$html = $html . "<td style='text-align: center'>" . $panen['no_transaksi'] .  "</td>";
			$html = $html . "<td style='text-align: center'>" . $panen['tanggal'] . "</td>";
			$html = $html . "<td style='text-align: center'>" . $panen['jumlah_hk_mandor'] . "</td>";
			$html = $html . "<td style='text-align: right'>" . $upah_pemeliharaan_mandor .  "</td>";
			$html = $html . "<td style='text-align: right'>" . $panen['premi_mandor'] .  "</td>";
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>" . ($upah_pemeliharaan_mandor + $panen['premi_mandor'])  .  "</td>";
			$html = $html . "</tr>";
		}
		/* ngambil data Krani dari bkm Pemeliharaan per periode */
		$resPanen =	$this->db->query("select * from est_bkm_pemeliharaan_ht where kerani_id= " . $hd['karyawan_id'] . "  
					and tanggal ='" . $tgl . "'  ")->result_array();
		$premi_pemeliharaan_kerani = 0;
		$upah_pemeliharaan_kerani = 0;
		foreach ($resPanen as $panen) {
			$jum_absen = $jum_absen + $panen['jumlah_hk_kerani'];
			$upah_pemeliharaan_kerani = $upah_pemeliharaan_kerani +  $panen['rp_hk_kerani']; //$gaji_per_hari_efektif; //
			$premi_pemeliharaan_kerani = $premi_pemeliharaan_kerani + $panen['premi_kerani'];
			$no++;
			$html = $html . "<tr>";
			$html = $html . "<td style='text-align: center'>" . $no . "</td>";
			$html = $html . "<td style='text-align: center'>KERANI</td>";
			$html = $html . "<td style='text-align: center'>" . $panen['no_transaksi'] .  "</td>";
			$html = $html . "<td style='text-align: center'>" . $panen['tanggal'] . "</td>";
			$html = $html . "<td style='text-align: center'>" . $panen['jumlah_hk_kerani'] . "</td>";
			$html = $html . "<td style='text-align: right'>" . $upah_pemeliharaan_kerani .  "</td>";
			$html = $html . "<td style='text-align: right'>" . $panen['premi_kerani'] .  "</td>";
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>" . ($upah_pemeliharaan_kerani + $panen['premi_kerani']) .  "</td>";
			$html = $html . "</tr>";
		}
		$upah = $upah_umum + $upah_absensi + $upah_panen + $upah_pemeliharaan + $upah_traksi + $upah_workshop + $upah_panen_mandor + $upah_panen_kerani + $upah_pemeliharaan_mandor + $upah_pemeliharaan_kerani;
		$premi = $premi_umum + $premi_panen + $premi_pemeliharaan + $premi_traksi + $premi_workshop + $premi_panen_mandor + $premi_panen_kerani + $premi_pemeliharaan_mandor + $premi_pemeliharaan_kerani;



		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: center'></td>";
		$html = $html . "<td style='text-align: center'></td>";
		$html = $html . "<td style='text-align: center'></td>";
		$html = $html . "<td style='text-align: center'></td>";
		$html = $html . "<td style='text-align: right'> " . $jum_absen . "</td>";
		$html = $html . "<td style='text-align: right'> " . $upah . "</td>";
		$html = $html . "<td style='text-align: right'> " . $premi . "</td>";
		$html = $html . "<td style='text-align: right'> " . $jum_jam_lembur . "</td>";
		$html = $html . "<td style='text-align: right'> " . $lembur  . "</td>";
		$html = $html . "<td style='text-align: right'> " . ($upah + $premi + $lembur) . "</td>";
		$html = $html . "</tr>";
		$html = $html . "</table>";
		// $this->set_response(array("status" => "OK", "data" => $html), REST_Controller::HTTP_OK);
		// return;

		echo $html;
	}
	public function getAbsensiKebunDetailperKaryawan_get($tgl1, $tgl2, $karyawan_id)
	{

		// echo $karyawan_id;
		// exit();
		$html = "";
		$q0 = "SELECT a.*,b.nip,b.nama, c.nama as jabatan,b.status_pajak FROM  payroll_karyawan_gaji a 
		inner join karyawan b on a.karyawan_id=b.id
		left join payroll_jabatan c on b.jabatan_id=c.id
		where b.id=" . $karyawan_id  . "";
		$hd = $this->db->query($q0)->row_array();
		$no = 0;
		$strNo = '';
		$html = $html . "	
		<table   border='0' width='50%' style='border-collapse: collapse;'>
		<tr>
			<td>Nip</td>
			<td>:</td>
			<td>" . $hd['nip'] . "</td>
		 </tr>
		 <tr>
			<td>Nama</td>
			<td>:</td>
			<td>" . $hd['nama'] . "</td>
	  </tr>
		 </table>
		";

		$html = $html . "	
		<table   border='1' width='100%' style='border-collapse: collapse;'>
		<thead>
		<tr>
			<td>No.</td>
			<td>Jenis</td>
			<td>No Transaksi</td>
			<td>Tanggal</td>
			<td>Upah</td>
			<td>Premi</td>
			<td rowspan=2  style='text-align: center'>Jumlah</td>
		</tr></thead>
		";
		$d1 = new DateTime($tgl1);
		$d2 = new DateTime($tgl2);
		$jum_absen_perkaryawan = 0;
		$gaji_per_hari_efektif = $hd['gapok'] / 25;
		$jum_absen_perkaryawan = 0;
		$upah = 0;
		$upah_absensi = 0;
		$upah_panen = 0;
		$upah_pemeliharaan = 0;
		$upah_traksi = 0;
		$upah_workshop = 0;
		$premi = 0;
		$lembur = 0;
		$premi_panen = 0;
		$premi_pemeliharaan = 0;
		$premi_traksi = 0;
		$premi_workshop = 0;
		$upah_panen_kerani = 0;
		$upah_panen_kerani = 0;
		$upah_panen_mandor = 0;
		$premi_panen_mandor = 0;
		$upah_pemeliharaan_kerani = 0;
		$premi_pemeliharaan_kerani = 0;
		$upah_pemeliharaan_mandor = 0;
		$premi_pemeliharaan_mandor = 0;
		$upah_perkaryawan = 0;
		$premi_perkaryawan = 0;
		$jum_jam_lembur = 0;

		$jum_absen = 0;
		$jum_absen = 0;
		while ($d1 <= $d2) {
			$lembur = 0;
			$upah_absensi = 0;
			$tgl = $d1->format('Y-m-d');
			/* ngambil data absensi per periode */
			$qAbsensi = "SELECT * FROM payroll_absensi a inner join hrms_jenis_absensi b on a.jenis_absensi_id=b.id 
					where a.karyawan_id= " . $hd['karyawan_id'] . " 
					and tanggal ='" . $tgl . "' 
					and b.tipe ='DIBAYAR';";
			$resAbsensi = $this->db->query($qAbsensi)->result_array();

			foreach ($resAbsensi as $absensiKaryawan) {
				// $jum_hari_dibayar++;
				// $upah_absensi = $upah_absensi + $absensiKaryawan['premi'];
				if ($absensiKaryawan['kode'] == 'H') { // Jumlah Hadir 
					$jum_absen++;
					$jum_absen_perkaryawan++;
					$upah_absensi = $upah_absensi + $gaji_per_hari_efektif;
					$no++;
					$html = $html . "<tr>";
					$html = $html . "<td style='text-align: center'>" . $no . "</td>";
					$html = $html . "<td style='text-align: center'>ABSEN</td>";
					$html = $html . "<td style='text-align: center'></td>";
					$html = $html . "<td style='text-align: center'>" . $absensiKaryawan['tanggal'] . "</td>";
					$html = $html . "<td style='text-align: center'>" . $gaji_per_hari_efektif .  "</td>";
					$html = $html . "<td style='text-align: center'>0</td>";
					$html = $html . "<td style='text-align: center'>" . $gaji_per_hari_efektif .  "</td>";
					$html = $html . "</tr>";
				}
			}
			// $upah_absensi = $hd['gapok'] * $jum_hari_hadir;
			// $hk_potongan_hari =	$hari_masuk_efektif - $jum_hari_dibayar;
			// $hk_potongan_gaji =	$hk_potongan_hari * $gaji_per_hari_efektif;
			$qLembur = "SELECT * FROM  payroll_lembur where karyawan_id= " . $hd['karyawan_id'] . " 
					and	tanggal ='" . $tgl . "' ;";
			$resLembur = $this->db->query($qLembur)->result_array();
			foreach ($resLembur as $lemburKaryawan) {
				$jum_jam_lembur = $jum_jam_lembur + $lemburKaryawan['jumlah_jam'];
				$lembur = $lembur + $lemburKaryawan['nilai_lembur'];
				$no++;
				$html = $html . "<tr>";
				$html = $html . "<td style='text-align: center'>" . $no . "</td>";
				$html = $html . "<td style='text-align: center'>LEMBUR</td>";
				$html = $html . "<td style='text-align: center'></td>";
				$html = $html . "<td style='text-align: center'>" . $lemburKaryawan['tanggal'] . "</td>";
				$html = $html . "<td style='text-align: center'>0</td>";
				$html = $html . "<td style='text-align: center'>" . $lemburKaryawan['nilai_lembur'] .  "</td>";
				$html = $html . "<td style='text-align: center'>" . $lemburKaryawan['nilai_lembur'] .  "</td>";
				$html = $html . "</tr>";
			}

			/* ngambil data bkm umum per periode */
			$resUmum =	$this->db->query("select * from est_bkm_umum_ht a inner join 
			est_bkm_umum_dt b on a.id=b.bkm_umum_id where b.karyawan_id= " . $hd['karyawan_id'] . " 
				and tanggal ='" . $tgl . "'  ")->result_array();
			$premi_umum = 0;
			$upah_umum = 0;
			// var_dump($resUmum);exit();
			foreach ($resUmum as $umum) {
				$jum_absen++;
				$jum_absen_perkaryawan++;
				$premi_umum = $premi_umum + $umum['premi'];
				$upah_umum = $upah_umum + $umum['rupiah_hk'];
				$no++;
				$html = $html . "<tr>";
				$html = $html . "<td style='text-align: center'>" . $no . "</td>";
				$html = $html . "<td style='text-align: center'>BKM UMUM</td>";
				$html = $html . "<td style='text-align: center'>" . $umum['no_transaksi'] .  "</td>";
				$html = $html . "<td style='text-align: center'>" . $umum['tanggal'] . "</td>";
				$html = $html . "<td style='text-align: center'>" . $umum['rupiah_hk'] .  "</td>";
				$html = $html . "<td style='text-align: center'>" . $umum['premi'] .  "</td>";
				$html = $html . "<td style='text-align: center'>" . ($umum['rupiah_hk'] + $umum['premi']) .  "</td>";
				$html = $html . "</tr>";
			}

			/* ngambil data bkm panen per periode */
			$resPanen =	$this->db->query("select * from est_bkm_panen_ht a inner join 
					est_bkm_panen_dt b on a.id=b.bkm_panen_id  where karyawan_id= " . $hd['karyawan_id'] . "  
					and tanggal ='" . $tgl . "'  ")->result_array();
			$premi_panen = 0;
			$upah_panen = 0;
			$denda_panen = 0;
			foreach ($resPanen as $panen) {
				$jum_absen++;
				$jum_absen_perkaryawan++;
				$upah_panen = $upah_panen + $panen['rp_hk'];
				$premi_panen = $premi_panen + $panen['premi_panen'];
				$denda_panen = $denda_panen + $panen['denda_panen'];
				$no++;
				$html = $html . "<tr>";
				$html = $html . "<td style='text-align: center'>" . $no . "</td>";
				$html = $html . "<td style='text-align: center'>PANEN</td>";
				$html = $html . "<td style='text-align: center'>" . $panen['no_transaksi'] .  "</td>";
				$html = $html . "<td style='text-align: center'>" . $panen['tanggal'] . "</td>";
				$html = $html . "<td style='text-align: center'>" . $panen['rp_hk'] .  "</td>";
				$html = $html . "<td style='text-align: center'>" . $panen['premi_panen'] .  "</td>";
				$html = $html . "<td style='text-align: center'>" . ($panen['rp_hk'] + $panen['premi_panen']) .  "</td>";
				$html = $html . "</tr>";
			}

			/* ngambil data bkm pemeliharaan per periode */
			$resPemeliharaan =	$this->db->query("select * from est_bkm_pemeliharaan_ht a inner join 
						est_bkm_pemeliharaan_dt b on a.id=b.bkm_pemeliharaan_id  where karyawan_id= " . $hd['karyawan_id'] . "  
						and tanggal ='" . $tgl . "'  ")->result_array();
			$premi_pemeliharaan = 0;
			$upah_pemeliharaan = 0;
			foreach ($resPemeliharaan as $pemeliharaan) {
				$jum_absen++;
				$jum_absen_perkaryawan++;
				$premi_pemeliharaan = $premi_pemeliharaan + $pemeliharaan['premi'];
				$upah_pemeliharaan = $upah_pemeliharaan + $pemeliharaan['rupiah_hk'];
				$no++;
				$html = $html . "<tr>";
				$html = $html . "<td style='text-align: center'>" . $no . "</td>";
				$html = $html . "<td style='text-align: center'>PEMELIHARAAN</td>";
				$html = $html . "<td style='text-align: center'>" . $pemeliharaan['no_transaksi'] .  "</td>";
				$html = $html . "<td style='text-align: center'>" . $pemeliharaan['tanggal'] . "</td>";
				$html = $html . "<td style='text-align: center'>" . $pemeliharaan['rupiah_hk'] .  "</td>";
				$html = $html . "<td style='text-align: center'>" . $pemeliharaan['premi'] .  "</td>";
				$html = $html . "<td style='text-align: center'>" . ($pemeliharaan['rupiah_hk'] + $pemeliharaan['premi']) .  "</td>";
				$html = $html . "</tr>";
			}

			/* ngambil data bkm Traksi per periode */
			$resTraksi =	$this->db->query("select * from trk_kegiatan_kendaraan_ht a inner join 
				trk_kegiatan_kendaraan_dt b on a.id=b.trk_kegiatan_kendaraan_id  where b.karyawan_id= " . $hd['karyawan_id'] . " 
				and tanggal ='" . $tgl . "'  ")->result_array();
			$premi_traksi = 0;
			$upah_traksi = 0;
			foreach ($resTraksi as $traksi) {
				$jum_absen++;
				$jum_absen_perkaryawan++;
				$premi_traksi = $premi_traksi + $traksi['premi'];
				$upah_traksi = $upah_traksi + $traksi['rupiah_hk'];
				$no++;
				$html = $html . "<tr>";
				$html = $html . "<td style='text-align: center'>" . $no . "</td>";
				$html = $html . "<td style='text-align: center'>TRAKSI</td>";
				$html = $html . "<td style='text-align: center'>" . $traksi['no_transaksi'] .  "</td>";
				$html = $html . "<td style='text-align: center'>" . $traksi['tanggal'] . "</td>";
				$html = $html . "<td style='text-align: center'>" . $traksi['rupiah_hk'] .  "</td>";
				$html = $html . "<td style='text-align: center'>" . $traksi['premi'] .  "</td>";
				$html = $html . "<td style='text-align: center'>" . ($traksi['rupiah_hk'] + $traksi['premi']) .  "</td>";
				$html = $html . "</tr>";
			}

			/* ngambil data bkm workshop per periode */
			$resWorkshop =	$this->db->query("select * from wrk_kegiatan_ht a inner join 
				wrk_kegiatan_dt b on a.id=b.wrk_kegiatan_id  where b.karyawan_id= " . $hd['karyawan_id'] . " 
				and tanggal ='" . $tgl . "'  ")->result_array();
			$premi_workshop = 0;
			$upah_workshop = 0;
			foreach ($resWorkshop as $workshop) {
				$jum_absen++;
				$jum_absen_perkaryawan++;
				$premi_workshop = $premi_workshop + $workshop['premi'];
				$upah_workshop = $upah_workshop + $workshop['rupiah_hk'];
				$no++;
				$html = $html . "<tr>";
				$html = $html . "<td style='text-align: center'>" . $no . "</td>";
				$html = $html . "<td style='text-align: center'>WORKSHOP</td>";
				$html = $html . "<td style='text-align: center'>" . $workshop['no_transaksi'] .  "</td>";
				$html = $html . "<td style='text-align: center'>" . $workshop['tanggal'] . "</td>";
				$html = $html . "<td style='text-align: center'>" . $workshop['rupiah_hk'] .  "</td>";
				$html = $html . "<td style='text-align: center'>" . $workshop['premi'] .  "</td>";
				$html = $html . "<td style='text-align: center'>" . ($workshop['rupiah_hk'] + $workshop['premi']) .  "</td>";
				$html = $html . "</tr>";
			}

			/* ngambil data Mandor dari bkm panen per periode */
			$resPanen =	$this->db->query("select * from est_bkm_panen_ht where mandor_id= " . $hd['karyawan_id'] . " 
		 					and tanggal ='" . $tgl . "' ")->result_array();
			$premi_panen_mandor = 0;
			$upah_panen_mandor = 0;
			foreach ($resPanen as $panen) {
				$jum_absen++;
				$upah_panen_mandor = $upah_panen_mandor + $gaji_per_hari_efektif; // $panen['rp_hk_mandor'];
				$premi_panen_mandor = $premi_panen_mandor + $panen['premi_mandor'];
				$no++;
				$html = $html . "<tr>";
				$html = $html . "<td style='text-align: center'>" . $no . "</td>";
				$html = $html . "<td style='text-align: center'>MANDOR</td>";
				$html = $html . "<td style='text-align: center'>" . $panen['no_transaksi'] .  "</td>";
				$html = $html . "<td style='text-align: center'>" . $panen['tanggal'] . "</td>";
				$html = $html . "<td style='text-align: center'>" . $gaji_per_hari_efektif .  "</td>";
				$html = $html . "<td style='text-align: center'>" . $panen['premi_mandor'] .  "</td>";
				$html = $html . "<td style='text-align: center'>" . ($gaji_per_hari_efektif + $panen['premi_mandor'])  .  "</td>";
				$html = $html . "</tr>";
			}
			/* ngambil data Krani dari bkm panen per periode */
			$resPanen =	$this->db->query("select * from est_bkm_panen_ht where kerani_id= " . $hd['karyawan_id'] . "  
					and tanggal ='" . $tgl . "'  ")->result_array();
			$premi_panen_kerani = 0;
			$upah_panen_kerani = 0;
			foreach ($resPanen as $panen) {
				$jum_absen++;
				$upah_panen_kerani = $upah_panen_kerani + $gaji_per_hari_efektif; //$panen['rp_hk_kerani'];
				$premi_panen_kerani = $premi_panen_kerani + $panen['premi_kerani'];
				$no++;
				$html = $html . "<tr>";
				$html = $html . "<td style='text-align: center'>" . $no . "</td>";
				$html = $html . "<td style='text-align: center'>KERANI</td>";
				$html = $html . "<td style='text-align: center'>" . $panen['no_transaksi'] .  "</td>";
				$html = $html . "<td style='text-align: center'>" . $panen['tanggal'] . "</td>";
				$html = $html . "<td style='text-align: center'>" . $gaji_per_hari_efektif .  "</td>";
				$html = $html . "<td style='text-align: center'>" . $panen['premi_kerani'] .  "</td>";
				$html = $html . "<td style='text-align: center'>" . ($gaji_per_hari_efektif + $panen['premi_kerani']) .  "</td>";
				$html = $html . "</tr>";
			}

			/* ngambil data Mandor dari bkm Pemeliharaan per periode */
			$resPanen =	$this->db->query("select * from est_bkm_pemeliharaan_ht where mandor_id= " . $hd['karyawan_id'] . " 
		 					and tanggal ='" . $tgl . "' ")->result_array();
			$upah_pemeliharaan_mandor = 0;
			$premi_pemeliharaan_mandor = 0;
			foreach ($resPanen as $panen) {
				$jum_absen++;
				$upah_pemeliharaan_mandor = $upah_pemeliharaan_mandor + $gaji_per_hari_efektif; //$panen['rp_hk_mandor'];
				$premi_pemeliharaan_mandor = $premi_pemeliharaan_mandor + $panen['premi_mandor'];
				$no++;
				$html = $html . "<tr>";
				$html = $html . "<td style='text-align: center'>" . $no . "</td>";
				$html = $html . "<td style='text-align: center'>MANDOR</td>";
				$html = $html . "<td style='text-align: center'>" . $panen['no_transaksi'] .  "</td>";
				$html = $html . "<td style='text-align: center'>" . $panen['tanggal'] . "</td>";
				$html = $html . "<td style='text-align: center'>" . $gaji_per_hari_efektif .  "</td>";
				$html = $html . "<td style='text-align: center'>" . $panen['premi_mandor'] .  "</td>";
				$html = $html . "<td style='text-align: center'>" . ($gaji_per_hari_efektif + $panen['premi_mandor'])  .  "</td>";
				$html = $html . "</tr>";
			}
			/* ngambil data Krani dari bkm Pemeliharaan per periode */
			$resPanen =	$this->db->query("select * from est_bkm_pemeliharaan_ht where kerani_id= " . $hd['karyawan_id'] . "  
					and tanggal ='" . $tgl . "'  ")->result_array();
			$premi_pemeliharaan_kerani = 0;
			$upah_pemeliharaan_kerani = 0;
			foreach ($resPanen as $panen) {
				$jum_absen++;
				$upah_pemeliharaan_kerani = $upah_pemeliharaan_kerani + $gaji_per_hari_efektif; //$panen['rp_hk_kerani'];
				$premi_pemeliharaan_kerani = $premi_pemeliharaan_kerani + $panen['premi_kerani'];
				$no++;
				$html = $html . "<tr>";
				$html = $html . "<td style='text-align: center'>" . $no . "</td>";
				$html = $html . "<td style='text-align: center'>KERANI</td>";
				$html = $html . "<td style='text-align: center'>" . $panen['no_transaksi'] .  "</td>";
				$html = $html . "<td style='text-align: center'>" . $panen['tanggal'] . "</td>";
				$html = $html . "<td style='text-align: center'>" . $gaji_per_hari_efektif .  "</td>";
				$html = $html . "<td style='text-align: center'>" . $panen['premi_kerani'] .  "</td>";
				$html = $html . "<td style='text-align: center'>" . ($gaji_per_hari_efektif + $panen['premi_kerani']) .  "</td>";
				$html = $html . "</tr>";
			}
			$upah = $upah + ($upah_umum + $upah_absensi + $upah_panen + $upah_pemeliharaan + $upah_traksi + $upah_workshop + $upah_panen_mandor + $upah_panen_kerani + $upah_pemeliharaan_mandor + $upah_pemeliharaan_kerani);
			$premi = $premi + ($premi_umum + $lembur + $premi_panen + $premi_pemeliharaan + $premi_traksi + $premi_workshop + $premi_panen_mandor + $premi_panen_kerani + $premi_pemeliharaan_mandor + $premi_pemeliharaan_kerani);
			$jum_absen_str = $jum_absen == 0 ? "" : $jum_absen;
			$d1->modify('+1 day');
		}

		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: center'></td>";
		$html = $html . "<td style='text-align: center'></td>";
		$html = $html . "<td style='text-align: center'></td>";
		$html = $html . "<td style='text-align: center'></td>";
		$html = $html . "<td style='text-align: center'>" . 	$upah . "</td>";
		$html = $html . "<td style='text-align: center'>" . 	$premi . "</td>";
		$html = $html . "<td style='text-align: center'> " . 	($upah + $premi) . "</td>";
		$html = $html . "</tr>";
		$html = $html . "</table>";
		// $this->set_response(array("status" => "OK", "data" => $html), REST_Controller::HTTP_OK);
		// return;

		echo $html;
	}
	function format_number_report($angka, $digit = 0)
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
				return '-';
			}

			return number_format($angka, $digit);
		}
	}
}
