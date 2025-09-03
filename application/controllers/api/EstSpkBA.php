<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

class EstSpkBA extends BD_Controller
{
	public $user_id;
	public $theCredential;

	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('GbmOrganisasiModel');
		$this->load->model('EstSpkBAModel');
		$this->load->model('AccJurnalModel');
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

		$query  = "SELECT 
		a.*,
		b.nama as lokasi,
		a.no_transaksi AS no_transaksi,
		d.no_spk AS no_spk,
		c.nama_supplier as supplier,
		e.user_full_name AS dibuat,
		f.user_full_name AS diubah,
		g.user_full_name AS diposting 
		FROM est_spk_ba_ht a 
		inner join est_spk_ht d on a.spk_id=d.id
		left join gbm_organisasi b on a.lokasi_id=b.id
		left join gbm_supplier c on a.supplier_id=c.id 
		LEFT JOIN fwk_users e ON a.dibuat_oleh = e.id
		LEFT JOIN fwk_users f ON a.diubah_oleh = f.id
		LEFT JOIN fwk_users g ON a.diposting_oleh = g.id 
		";
		$search = array('a.no_transaksi', 'a.tanggal', 'b.nama', 'c.nama_supplier', 'd.no_spk');
		$where  = null;

		$isWhere = " 1=1 ";
		if ($param['tgl_mulai'] && $param['tgl_mulai']) {
			$isWhere = " a.tanggal between '" . $param['tgl_mulai'] . "' and '" . $param['tgl_akhir'] . "'";
		}

		if ($param['lokasi_id']) {
			$isWhere = $isWhere . " and a.lokasi_id =" . $param['lokasi_id'] . "";
		} else {
			$isWhere = $isWhere . " and  a.lokasi_id in
			(select location_id from fwk_users_location where user_id=" . $this->user_id . ")";
		}

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}

	function index_get($id = '')
	{
		$retrieve = $this->EstSpkBAModel->retrieve_by_id($id);
		// $retrieve['detail'] = $this->EstSpkBAModel->retrieve_detail($id);
		$detail = $this->EstSpkBAModel->retrieve_detail($id);
		$result = [];
		foreach ($detail as $row) {
			$row['bapp'] = $this->EstSpkBAModel->retrieve_detail_bapp($row['id']);
			$result[] = $row;
		}
		$retrieve['detail'] = $result;
		// $retrieve['detail_bapp'] = $this->EstSpkBAModel->retrieve_detail_bapp($id);
		// $retrieve['detail_log'] = $this->EstSpkBAModel->retrieve_detail_item($id);


		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAll_get()
	{
		$retrieve = $this->EstSpkBAModel->retrieve_all();
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getDetail_get($id = '')
	{
		$retrieve = $this->EstSpkBAModel->retrieve_detail($id);
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function index_post()
	{
		$input = $this->post();
		if (!$input) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		if (!$input['details']) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}

		$input['dibuat_oleh'] = $this->user_id;
		//$input['no_spat'] = $this->getLastNumber('pks_pengolahan_ht', 'no_spat', 'SPAT');
		// var_dump($input);

		$res = $this->EstSpkBAModel->create($input);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'est_spk_ba', 'action' => 'new', 'entity_id' => $res);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function index_put($segment_3 = null)
	{
		$id = (int)$segment_3;
		$data = $this->put();
		if (!$data) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		if (!$data['details']) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$data['diubah_oleh'] = $this->user_id;
		$res = $this->EstSpkBAModel->update($id, $data);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'est_spk_ba', 'action' => 'edit', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	public function index_delete($id)
	{

		$res = $this->EstSpkBAModel->delete($id);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id' => $id)), 'entity' => 'est_spk_ba', 'action' => 'delete', 'entity_id' => $id);
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
		$retrieve_header = $this->EstSpkBAModel->retrieve_by_id($id);

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
		$retrieve_supp = $this->db->query("select * from gbm_supplier where id=" . $retrieve_header['supplier_id'] . "")->row_array();
		$acc_akun_supplier = $retrieve_supp['acc_akun_id'];
		$res_akun = $this->db->query("SELECT * FROM acc_auto_jurnal
		where kode='PPH_PEMBELIAN'  ")->row_array();
		if (empty($res_akun)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$akun_pph_id = $res_akun['acc_akun_id'];

		$retrieve_detail = $this->db->query("SELECT a.*,g.no_spk,g.tanggal AS tanggal_spk, b.hk,b.volume,b.total AS total_detail,b.harga,c.nama_supplier as nama_supplier, d.kode as kode_blok,d.nama as nama_blok,
		e.kode as kode_kegiatan,e.nama as nama_kegiatan,e.acc_akun_id, b.kegiatan_id,f.statusblok as umur_tanam_blok,f.tahuntanam,b.blok_id,b.kegiatan_id
		FROM est_spk_ba_ht a 
		inner join est_spk_ba_dt b on a.id=b.est_spk_ba_id 
		inner join gbm_supplier c on a.supplier_id=c.id 
		inner join gbm_organisasi d on b.blok_id=d.id 
		inner join gbm_blok f on b.blok_id=f.organisasi_id
		inner join acc_kegiatan e on b.kegiatan_id =e.id 
		LEFT JOIN est_spk_ht g ON a.spk_id=g.id
		where a.id=" . $id . " ")->result_array();
		// $this->set_response(array("status" => "NOT OK", "data" => 	$retrieve_detail), REST_Controller::HTTP_NOT_FOUND);
		// return;
		// Hapus jurnal jika sdh ada berdasarkan no refernsi dan MOdul  //
		$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'BA_SPK');

		// Data HEADER
		$this->load->library('Autonumber');
		$no_jurnal = $this->autonumber->jurnal_auto($retrieve_header['lokasi_id'], $retrieve_header['tanggal'], 'BASPK');
		$dataH = array(
			'no_jurnal' => $no_jurnal,
			'lokasi_id' => $retrieve_header['lokasi_id'],
			'tanggal' => $retrieve_header['tanggal'],
			'no_ref' => $retrieve_header['no_transaksi'],
			'ref_id' => $retrieve_header['id'],
			'tipe_jurnal' => 'AUTO',
			'modul' => 'BA_SPK',
			'keterangan' => 'BA SPK',
			'is_posting' => 1
		);
		$id_header = $this->AccJurnalModel->create_header($dataH);
		// $total_pendapatan = 0;
		foreach ($retrieve_detail as $key => $value) {
			// Data DEBET
			$dataDebet = array(
				'lokasi_id' => $retrieve_header['lokasi_id'],
				'jurnal_id' => $id_header,
				'acc_akun_id' => $value['acc_akun_id'], //akun biaya pemeliharaan,
				'debet' => $value['total_detail'],
				'kredit' => 0,
				'ket' => 'BA SPK No.:' . $value['no_transaksi'] . ' NO SPK:' . $value['no_spk'] . ' Blok: ' . $value['nama_blok'] . ' , Supplier: ' . $value['nama_Supplier'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
				'no_referensi' => $retrieve_header['no_transaksi'],
				'referensi_id' => NULL,
				'blok_stasiun_id' => $value['blok_id'],
				'kegiatan_id' => $value['kegiatan_id'], //kegiatan pemeliharaan,
				'kendaraan_mesin_id' => NULL,
				'karyawan_id' => NULL, //karyawan,
				'umur_tanam_blok' => $value['umur_tanam_blok'],
			);
			$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataDebet);
		}

		// Data KREDIT
		$dataKredit = array(
			'jurnal_id' => $id_header,
			'lokasi_id' => $retrieve_header['lokasi_id'],
			'acc_akun_id' => $acc_akun_supplier,
			'debet' => 0,
			'kredit' => $retrieve_header['total'], // Akun Lawan Biaya
			'ket' => 'BA SPK No.:' . $value['no_transaksi'] . ' NO SPK:' . $value['no_spk'],
			'no_referensi' => $retrieve_header['no_transaksi'],
			'referensi_id' => NULL,
			'blok_stasiun_id' => NULL,
			'kegiatan_id' => NULL, // $value['kegiatan_id'],
			'kendaraan_mesin_id' => NULL
		);
		$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataKredit);

		if ($retrieve_header['pph_persen'] > 0) {
			$pph_nilai = ($retrieve_header['subtotal'] * $retrieve_header['pph_persen'] / 100);
			$dataCr = array(
				'lokasi_id' => $retrieve_header['lokasi_id'],
				'jurnal_id' => $id_header,
				'acc_akun_id' => $akun_pph_id, //akun ,
				'debet' => 0,
				'kredit' => $pph_nilai,
				'ket' => 'PPH BA SPK ESTATE',
				'no_referensi' => $retrieve_header['no_transaksi'] . ' NO SPK:' . $value['no_spk'],
				'referensi_id' => NULL,
				'kegiatan_id' => NULL,
				'kendaraan_mesin_id' => NULL,
				'item_id' => NULL, //item,
				'umur_tanam_blok' => NULL,
				'blok_stasiun_id' => NULL,
			);
			$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataCr);
		}

		$input['diposting_oleh'] = $this->user_id;
		$res = $this->EstSpkBAModel->posting($id, $input);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id' => $id)), 'entity' => 'est_spk_ba', 'action' => 'posting', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAllOutstandingBayar_get()
	{

		$res = $this->db->query("SELECT a.*,a.id as invoice_id, c.nama as lokasi,
		d.nama_supplier as nama_kontraktor,d.acc_akun_id as akun_supplier_id,
		(a.total-a.nilai_dibayar) as sisa,	b.no_spk
		 FROM est_spk_ba_ht a inner JOIN  est_spk_ht b ON a.spk_id=b.id
		 INNER join gbm_organisasi c on b.lokasi_id=c.id
		inner join gbm_supplier d on b.supplier_id=d.id 
		WHERE a.total-a.nilai_dibayar >0 and a.is_posting=1")->result_array();
		if (!empty($res)) {
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
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


	function laporan_detail_post()
	{

		// $id = (int)$segment_3;
		$data = [];
		$format_laporan = $this->post('format_laporan', true);
		$input = [
			'no_po' => '01/PO/DPA',
			'lokasi_id' => 252,
			'tgl_mulai' => '2020-01-01',
			'tgl_akhir' => '2022-04-18',
		];

		$lokasi_id = $this->post('lokasi_id', true);
		$supplier_id = $this->post('supplier_id', true);
		$tanggal_awal = $this->post('tgl_mulai', true);
		$tanggal_akhir = $this->post('tgl_akhir', true);

		// $lokasi_id=$input['lokasi_id'];
		// $tanggal_awal=$input['tgl_mulai'];
		// $tanggal_akhir=$input['tgl_akhir'];

		// if ($format_laporan == 'pdf') {
		// 	$html = get_header_pdf_report();
		// }
		$queryData = "SELECT
		a.*,
		b.no_transaksi,
		b.tanggal,
		c.nama AS blok,
		d.nama AS kegiatan,
		e.nama AS lokasi,
		f.nama_supplier AS supplier, 
		g.no_spk,
		a.id AS id
		FROM est_spk_ba_dt a		
		INNER JOIN est_spk_ba_ht b on a.est_spk_ba_id=b.id
		INNER JOIN gbm_organisasi c on a.blok_id=c.id
		LEFT JOIN acc_kegiatan d on a.kegiatan_id=d.id
		LEFT JOIN gbm_organisasi e on b.lokasi_id=e.id
		LEFT JOIN gbm_supplier f on b.supplier_id=f.id
		INNER JOIN est_spk_ht g ON b.spk_id=g.id
		where b.tanggal between  '" . $tanggal_awal . "' and  '" . $tanggal_akhir . "'	
		";


		$filter_lokasi = "Semua";
		if ($lokasi_id) {
			$queryData = $queryData . " and b.lokasi_id=" . $lokasi_id . "";
			$res = $this->db->query("select * from gbm_organisasi where id=" . $lokasi_id . "")->row_array();
			$filter_lokasi = $res['nama'];
		}
		$filter_supplier = "Semua";
		if ($supplier_id) {
			$queryData = $queryData . " and b.supplier_id=" . $supplier_id . "";
			$res = $this->db->query("select * from gbm_supplier where id=" . $supplier_id . "")->row_array();
			$filter_supplier = $res['nama_supplier'];
		}


		$dataBA = $this->db->query($queryData)->result_array();

		$data['ba'] = 	$dataBA;
		$data['filter_supplier'] = 	$filter_supplier;
		$data['filter_lokasi'] = 	$filter_lokasi;
		$data['filter_tgl_awal'] = 	$tanggal_awal;
		$data['filter_tgl_akhir'] = $tanggal_akhir;
		$data['format_laporan'] = $format_laporan;

		$html = $this->load->view('Est_SpkBapp_Laporan', $data, true);

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		// echo $html;

		if ($format_laporan == 'view') {
			echo $html;
		} else if ($format_laporan == 'xls') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}

	function print_slip_get($segment_3 = '')
	{
		error_reporting(0);

		$id = (int)$segment_3;
		$data = [];

		$queryHeader = "SELECT 
		a.*,
		a.no_transaksi as no_ba,
		b.nama AS lokasi,
		c.nama_supplier AS supplier,
		a.id AS id,
		d.no_spk as no_spk,
		d.tgl_mulai AS mulai,
		d.tgl_akhir AS akhir,
		d.estimasi AS stimasi
		FROM est_spk_ba_ht a 
		LEFT JOIN gbm_organisasi b ON a.lokasi_id=b.id
		LEFT JOIN gbm_supplier c ON a.supplier_id=c.id
		LEFT JOIN est_spk_ht d ON a.spk_id = d.id
		WHERE a.id=" . $id . "
		";
		$dataHeader = $this->db->query($queryHeader)->row_array();

		$queryDetail = "		SELECT 
		a.*,
		b.nama AS blok,c.nama AS afdeling,
		d.nama AS kegiatan,e.kode AS satuan,
		a.id AS id
		FROM est_spk_ba_dt a 
		LEFT JOIN gbm_organisasi b ON a.blok_id=b.id
		LEFT JOIN gbm_organisasi c ON c.id=b.parent_id
		LEFT JOIN acc_kegiatan d ON a.kegiatan_id=d.id
		LEFT JOIN gbm_uom e ON d.uom_id=e.id
		WHERE a.est_spk_ba_id=" . $id . "
		";
		$dataDetail = $this->db->query($queryDetail)->result_array();


		// $perminta = $this->InvPermintaanBarangModel->print_slip($id);
		$data['header'] = 	$dataHeader;
		$data['detail'] = 	$dataDetail;
		// $data['detail_item'] = 	$dataDetailItem;

		$html = $this->load->view('EstSpkBA_print', $data, true);

		$filename = 'report_' . time();
		$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		// echo $html;
	}
}
