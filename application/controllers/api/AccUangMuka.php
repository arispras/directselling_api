<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

class AccUangMuka extends  BD_Controller //Rest_Controller
{
	public $user_id;
	public $theCredential;
	public $data = array();
	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('AccUangMukaModel');
		$this->load->model('M_DatatablesModel');
		$this->load->model('AccJurnalModel');
		$this->load->library('pdfgenerator');
		$this->load->helper("antech_helper");
		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id = $this->user_data->id;
	}

	// endpoint/list :POST
	public function list_post()
	{
		$post = $this->post();
		$query  = "SELECT a.*, b.nama as lokasi, a.id AS id FROM acc_uang_muka a 
		LEFT JOIN gbm_organisasi b ON a.lokasi_id=b.id
		";

		$search = array('b.nama', 'a.no_transaksi', 'a.tanggal', 'a.nilai', 'a.keterangan');
		$where  = null;
		$isWhere = null;
		// $isWhere = 'artikel.deleted_at IS NULL';
		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}

	// endpoint/ :GET
	function index_get($segment_3 = '')
	{
		$id = $segment_3;
		$retrieve = $this->AccUangMukaModel->retrieve($id);
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	//  endpoint/getall :GET
	function getAll_get()
	{
		$retrieve = $this->AccUangMukaModel->retrieve_all_kategori();
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	// endpoint/ :POST
	function index_post()
	{
		$input = $this->post();
		$input['dibuat_oleh'] = $this->user_id;
		$this->load->library('Autonumber');
		$input['no_transaksi'] = $this->autonumber->acc_uangmuka($input['lokasi_id'], $input['tanggal']);
		$retrieve = $this->AccUangMukaModel->create($input);
		$this->set_response(array("status" => "OK", "data" => 	$input['no_transaksi']), REST_Controller::HTTP_OK);
	}

	// endpoint/ :PUT
	function index_put($segment_3 = '')
	{
		$id = (int)$segment_3;
		$res = $this->AccUangMukaModel->retrieve($id);
		if (empty($res)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
		$data = $this->put();
		$data['diubah_oleh'] = $this->user_id;
		$data['dibuat_oleh'] = $this->user_id;
		$retrieve = $this->AccUangMukaModel->update($res['id'], $data);
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
	}

	// endpoint/ :DELETE
	function index_delete($segment_3 = '')
	{
		$id = (int)$segment_3;
		$res = $this->AccUangMukaModel->retrieve($id);
		if (empty($res)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
		$retrieve =  $this->AccUangMukaModel->delete($res['id']);
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
	}

	function posting_post($segment_3 = null)
	{
		$id = (int)$segment_3;
		$retrieve_header = $this->AccUangMukaModel->retrieve($id);

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

		$nilai_um =	$retrieve_header['nilai'];
		// Hapus jurnal jika sdh ada berdasarkan no refernsi dan MOdul  //
		$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'UANG_MUKA');
		// Data HEADER
		$this->load->library('Autonumber');
		$no_jurnal = $this->autonumber->jurnal_auto($retrieve_header['lokasi_id'], $retrieve_header['tanggal'], 'UM');
		$dataH = array(
			'no_jurnal' => $no_jurnal,
			'lokasi_id' => $retrieve_header['lokasi_id'],
			'tanggal' => $retrieve_header['tanggal'],
			'no_ref' => $retrieve_header['no_transaksi'],
			'ref_id' => $retrieve_header['id'],
			'tipe_jurnal' => 'AUTO',
			'modul' => 'UANG_MUKA',
			'keterangan' => 'UANG_MUKA: ' . $retrieve_header['keterangan'],
			'is_posting' => 1,
			'diposting_oleh' => $this->user_id
		);
		$id_header = $this->AccJurnalModel->create_header($dataH);

		// Data Kas Bank
		$dataJurnal = array(
			'lokasi_id' => $retrieve_header['lokasi_id'],
			'jurnal_id' => $id_header,
			'acc_akun_id' => $retrieve_header['acc_akun_kasbank_id'], //akun biaya pemeliharaan,
			'debet' => 0,
			'kredit' => $nilai_um,
			'ket' => $retrieve_header['keterangan'],
			'no_referensi' => $retrieve_header['no_transaksi'],
			'referensi_id' => NULL,
			'blok_stasiun_id' => NULL,
			'kegiatan_id' => NULL,
			'kendaraan_mesin_id' => NULL
		);
		$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataJurnal);
		// Data UM
		$dataJurnal = array(
			'lokasi_id' => $retrieve_header['lokasi_id'],
			'jurnal_id' => $id_header,
			'acc_akun_id' => $retrieve_header['acc_akun_id'], //Akun Um
			'debet' =>  $nilai_um,
			'kredit' => 0, // Akun Lawan Biaya
			'ket' => $retrieve_header['keterangan'],
			'no_referensi' => $retrieve_header['no_transaksi'],
			'referensi_id' => NULL,
			'blok_stasiun_id' => NULL,
			'kegiatan_id' => NULL, // $value['kegiatan_id'],
			'kendaraan_mesin_id' => NULL
		);
		$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataJurnal);

		$data = $this->post();
		$data['diposting_oleh'] = $this->user_id;
		$res = $this->AccUangMukaModel->posting($id, $data);
		if (!empty($res)) {
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAkunUangMuka_get()
	{

		$retrieve = $this->db->query("select b.*
		from acc_auto_jurnal a
		inner join acc_akun b on a.acc_akun_id =b.id
		where a.kode='UANG_MUKA'")->result_array();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function print_slip_get($segment_3 = '')
	{


		$id = (int)$segment_3;
		$data = [];

		$hd = $this->db->query("select a.*,d.nama as lokasi, b.kode as kode_akun_kasbank,b.nama as nama_akun_kasbank,
		c.kode as kode_akun_uangmuka,c.nama as nama_akun_uangmuka
		from acc_uang_muka a inner join
	   acc_akun b on a.acc_akun_kasbank_id=b.id
	   inner join acc_akun c on a.acc_akun_id=c.id
	   INNER JOIN gbm_organisasi d on a.lokasi_id=d.id
		where a.id=" . $id)->row_array();
		$data['hd'] = $hd;

		$html = $this->load->view('AccSlipUangMuka', $data, true);

		$filename = 'AccSlipUM_' . time();
		$this->pdfgenerator->generate($html, $filename, true, 'A4', 'portrait');
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
				'lokasi_id' => 252,
				'tgl_mulai' => '2000-01-01',
				'tgl_akhir' => '2022-05-11',
			];
		}


		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');

		$query = $this->db->query("SELECT
		a.*,
		b.nama AS acc_akun,
		c.nama AS acc_akun_kasbank,
		a.id AS id, d.no_transaksi as no_realisasi,d.tanggal as tanggal_realisasi
		FROM acc_uang_muka a
		LEFT JOIN acc_akun b ON a.acc_akun_id=b.id 
		LEFT JOIN acc_akun c ON a.acc_akun_kasbank_id=c.id 
		LEFT JOIN acc_uang_muka_realisasi d ON a.id=d.acc_uang_muka_id 
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

		$html = $this->load->view('AccUangMuka_laporan', $data, true);

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		echo $html;
	}
}
