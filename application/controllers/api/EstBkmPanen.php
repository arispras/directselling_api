<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

class EstBkmPanen extends BD_Controller
{
	public $user_id;
	public $theCredential;
	public $data = array();
	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('GbmOrganisasiModel');
		$this->load->model('EstBkmPanenModel');
		$this->load->model('AccJurnalModel');
		$this->load->model('AccJurnalUpahModel');
		$this->load->model('M_DatatablesModel');
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id = $this->user_data->id;
	}
	public function list_post()
	{
		$post = $this->post();
		$param = $post['parameter'];

		$query  = "	SELECT a.*,
		b.nama as lokasi,
		c.nama as rayon_afdeling,
		d.nama as mandor,
		e.nama as kerani, 
		f.nama as asisten,
		g.user_full_name AS dibuat,
		h.user_full_name AS diubah,
		i.user_full_name AS diposting 
		FROM est_bkm_panen_ht a 
		inner join gbm_organisasi b on a.lokasi_id=b.id
		inner join gbm_organisasi c on a.rayon_afdeling_id=c.id 
		left join karyawan d on a.mandor_id =d.id
		left join karyawan e on a.kerani_id=e.id 
		left join karyawan f on a.asisten_id=f.id
		LEFT JOIN fwk_users g ON a.dibuat_oleh = g.id
		LEFT JOIN fwk_users h ON a.diubah_oleh = h.id
		LEFT JOIN fwk_users i ON a.diposting_oleh = i.id
		";
		$search = array('a.no_transaksi', 'a.tanggal', 'b.nama', 'c.nama', 'd.nama', 'e.nama');
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
	public function list_mobile_post($page_no, $limit)
	{
		$post = $this->post();
		$param = $post['parameter'];

		$query  = "SELECT a.*,b.nama as lokasi,c.nama as rayon_afdeling,d.nama as mandor,e.nama as kerani, f.nama as asisten FROM `est_bkm_panen_ht` a inner join gbm_organisasi b on a.lokasi_id=b.id
		inner join gbm_organisasi c on a.rayon_afdeling_id=c.id left join karyawan d on a.mandor_id =d.id
		left join karyawan e on a.kerani_id=e.id left join karyawan f on a.asisten_id=f.id
		order by tanggal DESC
		LIMIT " . $limit . " OFFSET " . $page_no . "";
		// $search = array('a.no_transaksi', 'a.tanggal', 'b.nama', 'c.nama', 'd.nama', 'e.nama');
		// $where  = null;

		// $isWhere = " 1=1";

		// if ($param['tgl_mulai'] && $param['tgl_mulai']) {
		// 	$isWhere = " tanggal between '" . $param['tgl_mulai'] . "' and '" . $param['tgl_akhir'] . "'";
		// }
		// if ($param['lokasi_id']) {
		// 	$isWhere = $isWhere . " and lokasi_id =" . $param['lokasi_id'] . "";
		// } else {
		// 	$isWhere = $isWhere . " and  lokasi_id in
		// 	(select location_id from fwk_users_location where user_id=" . $this->user_id . ")";
		// }
		// $isWhere = $isWhere .  " and  a.rayon_afdeling_id in
		// (select afdeling_id from fwk_users_afdeling where user_id=" . $this->user_id . ")";

		// if (!empty($param['status_id'])) {
		// 	if ($param['status_id'] == 'N') {
		// 		$isWhere = $isWhere .  "  and a.is_posting=0";
		// 	} else {
		// 		$isWhere = $isWhere .  "  and a.is_posting=1";
		// 	}
		// }
		// $data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$data = $this->db->query($query)->result_array();
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	function listBkmPanen_post()
	{
		$format_laporan =  $this->post('format_laporan', true);

		// $id = (int)$segment_3;
		$data = [];

		$input = [
			'no_po' => '01/PO/DPA',
			'lokasi_id' => 265,
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
		c.nama as rayon_afdeling,
		d.nama as mandor,
		e.nama as kerani,
		f.nama as asisten 
		FROM est_bkm_panen_ht a 
		inner join gbm_organisasi b on a.lokasi_id=b.id
		inner join gbm_organisasi c on a.rayon_afdeling_id=c.id 
		left join karyawan d on a.mandor_id =d.id
		left join karyawan e on a.kerani_id=e.id 
		left join karyawan f on a.asisten_id=f.id
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
		// var_dump($this->post());exit();
		$dataBkm = $this->db->query($queryBkm)->result_array();
		// var_dump($dataBkm);exit();
		$data['bkm'] = 	$dataBkm;
		$data['filter_lokasi'] = 	$filter_lokasi;
		$data['filter_tgl_awal'] = 	$tanggal_awal;
		$data['filter_tgl_akhir'] = $tanggal_akhir;

		$html = $this->load->view('Est_bkm_panen_list', $data, true);

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		echo $html;
	}

	function listBkmPanenDetail_post()
	{
		$format_laporan =  $this->post('format_laporan', true);

		$data = [];

		$input = [
			'no_po' => '01/PO/DPA',
			'lokasi_id' => 265,
			'tgl_mulai' => '2022-07-01',
			'tgl_akhir' => '2022-07-30',

		];

		$lokasi_id = $this->post('lokasi_id', true);
		$tanggal_awal = $this->post('tgl_mulai', true);
		$tanggal_akhir = $this->post('tgl_akhir', true);
		$status_id = $this->post('status_id', true);

		// $lokasi_id = $input['lokasi_id'];
		// $tanggal_awal = $input['tgl_mulai'];
		// $tanggal_akhir = $input['tgl_akhir'];

		$queryhead = "SELECT a.*,
		b.nama as lokasi,
		c.nama as rayon_afdeling,
		d.nama as mandor,
		e.nama as kerani,
		f.nama as asisten 
		FROM est_bkm_panen_ht a 
		inner join gbm_organisasi b on a.lokasi_id=b.id
		inner join gbm_organisasi c on a.rayon_afdeling_id=c.id 
		left join karyawan d on a.mandor_id =d.id
		left join karyawan e on a.kerani_id=e.id 
		left join karyawan f on a.asisten_id=f.id
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
							c.nama as blok
							FROM est_bkm_panen_dt a 
							INNER JOIN karyawan b ON a.karyawan_id=b.id
							INNER JOIN gbm_organisasi c ON a.blok_id=c.id WHERE a.bkm_panen_id=" . $hd['id'] . "";
			$dataDtl = $this->db->query($querydetail)->result_array();
			// var_dump($dataDtl)	
			$hd['detail'] = $dataDtl;
			$result[] = $hd;
		}

		$data['bkm'] = 	$result;
		// var_dump($result)	;exit();
		$data['filter_lokasi'] = 	$filter_lokasi;
		$data['filter_tgl_awal'] = 	$tanggal_awal;
		$data['filter_tgl_akhir'] = $tanggal_akhir;

		$html = $this->load->view('Est_bkm_panen_list_detail', $data, true);

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		echo $html;
	}

	function index_get($id = '')
	{
		$retrieve = array();
		$retrieve = $this->EstBkmPanenModel->retrieve_by_id($id);
		$retrieve_detail = $this->EstBkmPanenModel->retrieve_detail($id);
		$detail = array();
		foreach ($retrieve_detail as $key => $value) {
			$dtl = array();
			$retrieve_denda = $this->EstBkmPanenModel->retrieve_detail_denda($value['id']);
			$dtl = $value;
			$dtl['denda'] = array();
			foreach ($retrieve_denda as $key1 => $value_denda) {
				$dtl['denda'][] = $value_denda;
			}
			$detail[] = $dtl;
		}

		$retrieve['detail'] = $detail;

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function getDetail_get($id = '')
	{
		$retrieve = $this->EstBkmPanenModel->retrieve_detail($id);
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function get_detail_mobile_get($id = '')
	{

		$id = (int)$id;
		$data = [];

		$queryHeader = "SELECT a.*,
		b.nama as lokasi,
		c.nama as rayon,
		d.nama as mandor,
		e.nama as kerani,
		f.nama as asisten,
		premi_mandor,denda_mandor,premi_kerani,denda_kerani
		 FROM est_bkm_panen_ht a
		INNER JOIN gbm_organisasi b ON a.lokasi_id=b.id
		INNER JOIN gbm_organisasi c ON a.rayon_afdeling_id=c.id
		LEFT JOIN karyawan d ON a.mandor_id=d.id
		LEFT JOIN karyawan e ON a.kerani_id=e.id 
		LEFT JOIN karyawan f ON a.asisten_id=f.id WHERE a.id=" . $id . "";
		$dataHeader = $this->db->query($queryHeader)->row_array();

		$queryDetail = "SELECT a.*,
		b.nama as karyawan,
        c.nama as blok
		FROM est_bkm_panen_dt a 
		INNER JOIN karyawan b ON a.karyawan_id=b.id
        INNER JOIN gbm_organisasi c ON a.blok_id=c.id WHERE a.bkm_panen_id=" . $id . "";
		$dataDetail = $this->db->query($queryDetail)->result_array();

		$data['header'] = 	$dataHeader;
		$data['detail'] = 	$dataDetail;

		if (!empty($data)) {
			$this->set_response(array("status" => "OK", "data" => $data), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function index_post()
	{
		$input = $this->post();
		$input['dibuat_oleh'] = $this->user_id;
		$input['diubah_oleh'] = $this->user_id;

		$this->load->library('Autonumber');
		$input['no_transaksi'] = $this->autonumber->est_bkm_panen($input['lokasi_id']['id'], $input['tanggal']);

		//$input['no_spat'] = $this->getLastNumber('pks_pengolahan_ht', 'no_spat', 'SPAT');
		// var_dump($input);

		$res = $this->EstBkmPanenModel->create($input);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'est_bkm_panen', 'action' => 'new', 'entity_id' => $res);
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

		$res = $this->EstBkmPanenModel->update($id, $data);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'est_bkm_panen', 'action' => 'edit', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	public function index_delete($id)
	{
		$res = $this->EstBkmPanenModel->delete($id);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id' => $id)), 'entity' => 'est_bkm_panen', 'action' => 'delete', 'entity_id' => $id);
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

		/* -- GET SETTING AKUN --*/
		$res_akun_panen_upah = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='PANEN_KEBUN_UPAH'")->row_array();
		if (empty($res_akun_panen_upah)) {
			$this->set_response(array("status" => "NOT OK", "data" => "PANEN_KEBUN_UPAH Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$res_akun_panen_premi = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='PANEN_KEBUN_PREMI'")->row_array();
		if (empty($res_akun_panen_premi)) {
			$this->set_response(array("status" => "NOT OK", "data" => "PANEN_KEBUN_PREMI Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$res_akun_panen_upah_pengawas = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='PANEN_KEBUN_UPAH_PENGAWAS'")->row_array();
		if (empty($res_akun_panen_upah_pengawas)) {
			$this->set_response(array("status" => "NOT OK", "data" => "PANEN_KEBUN_UPAH_PENGAWAS Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$res_akun_panen_premi_pengawas = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='PANEN_KEBUN_PREMI_PENGAWAS'")->row_array();
		if (empty($res_akun_panen_premi_pengawas)) {
			$this->set_response(array("status" => "NOT OK", "data" => "PANEN_KEBUN_PREMI_PENGAWAS Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$akun_debet_pemanen_premi = $res_akun_panen_premi['acc_akun_id_debet'];
		$akun_kredit_pemanen_premi = $res_akun_panen_premi['acc_akun_id_kredit'];
		$akun_debet_pemanen_upah = $res_akun_panen_upah['acc_akun_id_debet'];
		$akun_kredit_pemanen_upah = $res_akun_panen_upah['acc_akun_id_kredit'];
		$akun_debet_pengawas_premi = $res_akun_panen_premi_pengawas['acc_akun_id_debet'];
		$akun_kredit_pengawas_premi = $res_akun_panen_premi_pengawas['acc_akun_id_kredit'];
		$akun_debet_pengawas_upah = $res_akun_panen_upah_pengawas['acc_akun_id_debet'];
		$akun_kredit_pengawas_upah = $res_akun_panen_upah_pengawas['acc_akun_id_kredit'];

		$retrieve_inter_akun = $this->db->query("SELECT * FROM acc_inter_unit
		where tipe='INTER' ")->result_array();
		$akun_inter = array();
		foreach ($retrieve_inter_akun as $key => $akun) {
			$akun_inter[$akun['lokasi_id']][$akun['lokasi_id_2']] = $akun['acc_akun_id'];
		}
		$kegiatan_panen = 1; // sementara hardcode 
		$kegiatan_pengawas_panen = 6; // sementara hardcode 
		/* END GET SETTING AKUN */
		$retrieve_header = $this->EstBkmPanenModel->retrieve_by_id($id);
		if (empty($retrieve_header)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Untuk diposting"), REST_Controller::HTTP_OK);
			return;
		} else {
			if ($retrieve_header['is_posting'] == 1) {
				$this->set_response(array("status" => "NOT OK", "data" => "Data sudah diposting"), REST_Controller::HTTP_OK);
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
		$lokasi_tugas_id_mandor = null;
		$lokasi_tugas_id_kerani = null;
		if ($retrieve_header['mandor_id']) {
			$res_mandor = $this->db->query("select * from karyawan where id=" . $retrieve_header['mandor_id'])->row_array();
			$lokasi_tugas_id_mandor = $res_mandor['lokasi_tugas_id'];
		}
		if ($retrieve_header['kerani_id']) {
			$res_kerani = $this->db->query("select * from karyawan where id=" . $retrieve_header['kerani_id'])->row_array();
			$lokasi_tugas_id_kerani = $res_kerani['lokasi_tugas_id'];
		}
		$sql = "SELECT a.*,b.*,c.nip,c.nama as nama_karyawan, d.kode as kode_blok,d.nama as nama_blok, 
		e.kode as kode_kegiatan,e.nama as nama_kegiatan ,f.statusblok as umur_tanam_blok,f.tahuntanam,c.lokasi_tugas_id
		FROM est_bkm_panen_ht a 
		inner join est_bkm_panen_dt b on a.id=b.bkm_panen_id 
		inner join karyawan c on b.karyawan_id=c.id 
		left join gbm_organisasi d on b.blok_id=d.id 
		left join gbm_blok f on b.blok_id=f.organisasi_id
		left join acc_kegiatan e on b.acc_kegiatan_id =e.id 
		where b.bkm_panen_id=" . $id . "";
		$retrieve_detail = $this->db->query($sql)->result_array();


		// 		// Hapus jurnal jika sdh ada berdasarkan no refernsi dan MOdul  //
		$this->AccJurnalUpahModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'PANEN');
		$total_pendapatan = 0;
		$id_header = 0;
		/* JIka bukan premi kontanan maka jurnal*/
		if (!$retrieve_header['is_premi_kontanan'] || $retrieve_header['is_premi_kontanan'] == '0') {
			// Data HEADER
			$this->load->library('Autonumber');
			$no_jurnal = $this->autonumber->jurnal_upah_auto($retrieve_header['lokasi_id'], $retrieve_header['tanggal'], 'PNN');

			$dataH = array(
				'no_jurnal' => $no_jurnal,
				'lokasi_id' => $retrieve_header['lokasi_id'],
				'tanggal' => $retrieve_header['tanggal'],
				'no_ref' => $retrieve_header['no_transaksi'],
				'ref_id' => $retrieve_header['id'],
				'tipe_jurnal' => 'AUTO',
				'modul' => 'PANEN',
				'keterangan' => 'TRANSAKSI PANEN',
				'is_posting' => 1,
			);
			$id_header = $this->AccJurnalUpahModel->create_header($dataH);
			$total_pendapatan = 0;
			$upah_mandor_proporsi = 0;
			$premi_mandor_proporsi = 0;
			$upah_kerani_proporsi = 0;
			$premi_kerani_proporsi = 0;
			if ($retrieve_header['rp_hk_mandor'] > 0) {
				$upah_mandor = $retrieve_header['rp_hk_mandor'] - $retrieve_header['denda_mandor'];
				$premi_mandor = $retrieve_header['premi_mandor'];
			} else {
				$upah_mandor = $retrieve_header['rp_hk_mandor'];
				$premi_mandor = $retrieve_header['premi_mandor'] - $retrieve_header['denda_mandor'];
			}

			if ($retrieve_header['rp_hk_kerani'] > 0) {
				$upah_kerani = $retrieve_header['rp_hk_kerani'] - $retrieve_header['denda_kerani'];
				$premi_kerani = $retrieve_header['premi_kerani'];
			} else {
				$upah_kerani = $retrieve_header['rp_hk_kerani'];
				$premi_kerani = $retrieve_header['premi_kerani'] - $retrieve_header['denda_kerani'];
			}


			$jumlah_pemanen = count($retrieve_detail);
			if ($upah_mandor > 0) {
				$upah_mandor_proporsi = $upah_mandor / $jumlah_pemanen;
			}
			if ($premi_mandor > 0) {
				$premi_mandor_proporsi = $premi_mandor / $jumlah_pemanen;
			}
			if ($upah_kerani > 0) {
				$upah_kerani_proporsi = $upah_kerani / $jumlah_pemanen;
			}
			if ($premi_kerani > 0) {
				$premi_kerani_proporsi = $premi_kerani / $jumlah_pemanen;
			}
			if ($jumlah_pemanen <= 0) { // JIka tdk ada anggotanya maka alokasikan ke umum
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
				/* MANDOR */
				if ($retrieve_header['mandor_id']) {
					$inter_akun_id = null;
					if ($akun_inter[$retrieve_header['lokasi_id']][$lokasi_tugas_id_mandor]) {
						$inter_akun_id = $akun_inter[$retrieve_header['lokasi_id']][$lokasi_tugas_id_mandor];
					}
					if ($akun_inter[$lokasi_tugas_id_mandor][$retrieve_header['lokasi_id']]) {
						$inter_akun_id = $akun_inter[$lokasi_tugas_id_mandor][$retrieve_header['lokasi_id']];
					}
					if ($upah_mandor > 0) {
						if ($lokasi_tugas_id_mandor == $retrieve_header['lokasi_id']) { // JIKA lokasi karyawan dan lokasi bekerja sama
							$dataDebet = array(
								'lokasi_id' => $retrieve_header['lokasi_id'],
								'jurnal_id' => $id_header,
								'acc_akun_id' => $akun_debet_transit_upah, //akun biaya Panen,
								'debet' => $upah_mandor,
								'kredit' => 0,
								'ket' => 'Biaya Pengawas(Mandor) Panen ',
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL,
								'kegiatan_id' => NULL, //kegiatan panen,
								'kendaraan_mesin_id' => NULL,
								'karyawan_id' => $retrieve_header['mandor_id'], //karyawan,
								'umur_tanam_blok' => NULL,
								'tipe' => 'upah_pengawas',
								'hk' => 0
							);

							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
							// Data KREDIT
							$dataKredit = array(
								'lokasi_id' => $retrieve_header['lokasi_id'],
								'jurnal_id' => $id_header,
								'acc_akun_id' => $akun_kredit_transit_upah, //$value['acc_akun_id'],
								'debet' => 0,
								'kredit' => $upah_mandor, // Akun Lawan Biaya
								'ket' => 'Biaya Pengawas(Mandor) Panen Upah',
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL,
								'kegiatan_id' => NULL, // $value['kegiatan_id'],
								'kendaraan_mesin_id' => NULL,
								'tipe' => 'upah_pengawas',
								'hk' => 0
							);
							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
						} else { // Jika lokasi bekerja berbeda

							$dataDebet = array(
								'lokasi_id' => $retrieve_header['lokasi_id'],
								'jurnal_id' => $id_header,
								'acc_akun_id' => $akun_debet_transit_upah, //akun biaya Panen,
								'debet' => $upah_mandor,
								'kredit' => 0,
								'ket' => 'Biaya Pengawas(Mandor) Panen ',
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL,
								'kegiatan_id' => NULL, //kegiatan panen,
								'kendaraan_mesin_id' => NULL,
								'karyawan_id' => $retrieve_header['mandor_id'], //karyawan,
								'umur_tanam_blok' => NULL,
								'tipe' => 'upah_pengawas',
								'hk' => 0
							);

							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
							// Data KREDIT
							$dataKredit = array(
								'lokasi_id' => $retrieve_header['lokasi_id'],
								'jurnal_id' => $id_header,
								'acc_akun_id' => $inter_akun_id, //$value['acc_akun_id'],
								'debet' => 0,
								'kredit' => $upah_mandor, // Akun Lawan Biaya
								'ket' => 'Biaya Pengawas(Mandor) Panen Upah',
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL,
								'kegiatan_id' => NULL, // $value['kegiatan_id'],
								'kendaraan_mesin_id' => NULL,
								'tipe' => 'upah_pengawas',
								'hk' => 0
							);
							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);

							$dataDebet = array(
								'lokasi_id' => $lokasi_tugas_id_mandor,
								'jurnal_id' => $id_header,
								'acc_akun_id' => $inter_akun_id, //akun biaya Panen,
								'debet' => $upah_mandor,
								'kredit' => 0,
								'ket' => 'Biaya Pengawas(Mandor) Panen ',
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL,
								'kegiatan_id' => NULL, //kegiatan panen,
								'kendaraan_mesin_id' => NULL,
								'karyawan_id' => $retrieve_header['mandor_id'], //karyawan,
								'umur_tanam_blok' => NULL,
								'tipe' => 'upah_pengawas',
								'hk' => 0
							);

							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
							// Data KREDIT
							$dataKredit = array(
								'lokasi_id' => $lokasi_tugas_id_mandor,
								'jurnal_id' => $id_header,
								'acc_akun_id' => $akun_kredit_transit_upah, //$value['acc_akun_id'],
								'debet' => 0,
								'kredit' => $upah_mandor, // Akun Lawan Biaya
								'ket' => 'Biaya Pengawas(Mandor) Panen Upah',
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL,
								'kegiatan_id' => NULL, // $value['kegiatan_id'],
								'kendaraan_mesin_id' => NULL,
								'tipe' => 'upah_pengawas',
								'hk' => 0
							);
							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
						}
					}
					if ($premi_mandor > 0) {
						if ($lokasi_tugas_id_mandor == $retrieve_header['lokasi_id']) { // JIKA lokasi karyawan dan lokasi bekerja sama
							$dataDebet = array(
								'lokasi_id' => $retrieve_header['lokasi_id'],
								'jurnal_id' => $id_header,
								'acc_akun_id' => $akun_debet_transit_premi, //akun biaya Panen,
								'debet' => $premi_mandor,
								'kredit' => 0,
								'ket' => 'Biaya Pengawas(Mandor) Panen Premi',
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL,
								'kegiatan_id' => NULL, //kegiatan panen,
								'kendaraan_mesin_id' => NULL,
								'karyawan_id' => $retrieve_header['mandor_id'], //karyawan,
								'umur_tanam_blok' => NULL,
								'tipe' => 'premi_pengawas',
								'hk' => 0
							);

							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
							// Data KREDIT
							$dataKredit = array(
								'lokasi_id' => $retrieve_header['lokasi_id'],
								'jurnal_id' => $id_header,
								'acc_akun_id' => $akun_kredit_transit_premi, //$value['acc_akun_id'],
								'debet' => 0,
								'kredit' => $premi_mandor, // Akun Lawan Biaya
								'ket' => 'Biaya Pengawas(Mandor) Panen Premi ',
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL,
								'kegiatan_id' => NULL, // $value['kegiatan_id'],
								'kendaraan_mesin_id' => NULL,
								'tipe' => 'premi_pengawas',
								'hk' => 0
							);
							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
						} else { // JIKA lokasi karyawan dan lokasi bekerja berbeda
							$dataDebet = array(
								'lokasi_id' => $retrieve_header['lokasi_id'],
								'jurnal_id' => $id_header,
								'acc_akun_id' => $akun_debet_transit_premi, //akun biaya Panen,
								'debet' => $premi_mandor,
								'kredit' => 0,
								'ket' => 'Biaya Pengawas(Mandor) Panen Premi',
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL,
								'kegiatan_id' => NULL, //kegiatan panen,
								'kendaraan_mesin_id' => NULL,
								'karyawan_id' => $retrieve_header['mandor_id'], //karyawan,
								'umur_tanam_blok' => NULL,
								'tipe' => 'premi_pengawas',
								'hk' => 0
							);

							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
							// Data KREDIT
							$dataKredit = array(
								'lokasi_id' => $retrieve_header['lokasi_id'],
								'jurnal_id' => $id_header,
								'acc_akun_id' => $inter_akun_id, //$value['acc_akun_id'],
								'debet' => 0,
								'kredit' => $premi_mandor, // Akun Lawan Biaya
								'ket' => 'Biaya Pengawas(Mandor) Panen Premi ',
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL,
								'kegiatan_id' => NULL, // $value['kegiatan_id'],
								'kendaraan_mesin_id' => NULL,
								'tipe' => 'premi_pengawas',
								'hk' => 0
							);
							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);

							$dataDebet = array(
								'lokasi_id' => $lokasi_tugas_id_mandor,
								'jurnal_id' => $id_header,
								'acc_akun_id' => $inter_akun_id, //akun biaya Panen,
								'debet' => $premi_mandor,
								'kredit' => 0,
								'ket' => 'Biaya Pengawas(Mandor) Panen Premi',
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL,
								'kegiatan_id' => NULL, //kegiatan panen,
								'kendaraan_mesin_id' => NULL,
								'karyawan_id' => $retrieve_header['mandor_id'], //karyawan,
								'umur_tanam_blok' => NULL,
								'tipe' => 'premi_pengawas',
								'hk' => 0
							);

							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
							// Data KREDIT
							$dataKredit = array(
								'lokasi_id' => $lokasi_tugas_id_mandor,
								'jurnal_id' => $id_header,
								'acc_akun_id' => $akun_kredit_transit_premi, //$value['acc_akun_id'],
								'debet' => 0,
								'kredit' => $premi_mandor, // Akun Lawan Biaya
								'ket' => 'Biaya Pengawas(Mandor) Panen Premi ',
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL,
								'kegiatan_id' => NULL, // $value['kegiatan_id'],
								'kendaraan_mesin_id' => NULL,
								'tipe' => 'premi_pengawas',
								'hk' => 0
							);
							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
						}
					}
				}
				/* end MANDOR */

				/* KERANI */
				if ($retrieve_header['kerani_id']) {
					$inter_akun_id = null;
					if ($akun_inter[$retrieve_header['lokasi_id']][$lokasi_tugas_id_kerani]) {
						$inter_akun_id = $akun_inter[$retrieve_header['lokasi_id']][$lokasi_tugas_id_kerani];
					}
					if ($akun_inter[$lokasi_tugas_id_kerani][$retrieve_header['lokasi_id']]) {
						$inter_akun_id = $akun_inter[$lokasi_tugas_id_kerani][$retrieve_header['lokasi_id']];
					}
					if ($upah_kerani > 0) {
						if ($lokasi_tugas_id_kerani == $retrieve_header['lokasi_id']) { // JIKA lokasi karyawan dan lokasi bekerja sama
							$dataDebet = array(
								'lokasi_id' => $retrieve_header['lokasi_id'],
								'jurnal_id' => $id_header,
								'acc_akun_id' => $akun_debet_transit_upah, //akun biaya Panen,
								'debet' => $upah_kerani,
								'kredit' => 0,
								'ket' => 'Biaya Pengawas(Kerani) Panen',
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL,
								'kegiatan_id' => NULL, //kegiatan panen,
								'kendaraan_mesin_id' => NULL,
								'karyawan_id' => $retrieve_header['kerani_id'], //karyawan,
								'umur_tanam_blok' => NULL,
								'tipe' => 'upah_pengawas',
								'hk' => 0
							);
							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
							// Data KREDIT
							$dataKredit = array(
								'lokasi_id' => $retrieve_header['lokasi_id'],
								'jurnal_id' => $id_header,
								'acc_akun_id' => $akun_kredit_transit_upah, //$value['acc_akun_id'],
								'debet' => 0,
								'kredit' => $upah_kerani, // Akun Lawan Biaya
								'ket' => 'Biaya Pengawas(Kerani) Panen Upah',
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL,
								'kegiatan_id' => NULL, // $value['kegiatan_id'],
								'kendaraan_mesin_id' => NULL,
								'tipe' => 'upah_pengawas',
								'hk' => 0
							);
							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
						} else { // Jika lokasi kerja berbeda
							$dataDebet = array(
								'lokasi_id' => $retrieve_header['lokasi_id'],
								'jurnal_id' => $id_header,
								'acc_akun_id' => $akun_debet_transit_upah, //akun biaya Panen,
								'debet' => $upah_kerani,
								'kredit' => 0,
								'ket' => 'Biaya Pengawas(Kerani) Panen',
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL,
								'kegiatan_id' => NULL, //kegiatan panen,
								'kendaraan_mesin_id' => NULL,
								'karyawan_id' => $retrieve_header['kerani_id'], //karyawan,
								'umur_tanam_blok' => NULL,
								'tipe' => 'upah_pengawas',
								'hk' => 0
							);
							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
							// Data KREDIT
							$dataKredit = array(
								'lokasi_id' => $retrieve_header['lokasi_id'],
								'jurnal_id' => $id_header,
								'acc_akun_id' => $inter_akun_id, //$value['acc_akun_id'],
								'debet' => 0,
								'kredit' => $upah_kerani, // Akun Lawan Biaya
								'ket' => 'Biaya Pengawas(Kerani) Panen Upah',
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL,
								'kegiatan_id' => NULL, // $value['kegiatan_id'],
								'kendaraan_mesin_id' => NULL,
								'tipe' => 'upah_pengawas',
								'hk' => 0
							);
							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);

							$dataDebet = array(
								'lokasi_id' => $lokasi_tugas_id_kerani,
								'jurnal_id' => $id_header,
								'acc_akun_id' => $inter_akun_id, //akun biaya Panen,
								'debet' => $upah_kerani,
								'kredit' => 0,
								'ket' => 'Biaya Pengawas(Kerani) Panen',
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL,
								'kegiatan_id' => NULL, //kegiatan panen,
								'kendaraan_mesin_id' => NULL,
								'karyawan_id' => $retrieve_header['kerani_id'], //karyawan,
								'umur_tanam_blok' => NULL,
								'tipe' => 'upah_pengawas',
								'hk' => 0
							);
							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
							// Data KREDIT
							$dataKredit = array(
								'lokasi_id' => $lokasi_tugas_id_kerani,
								'jurnal_id' => $id_header,
								'acc_akun_id' => $akun_kredit_transit_upah, //$value['acc_akun_id'],
								'debet' => 0,
								'kredit' => $upah_kerani, // Akun Lawan Biaya
								'ket' => 'Biaya Pengawas(Kerani) Panen Upah',
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL,
								'kegiatan_id' => NULL, // $value['kegiatan_id'],
								'kendaraan_mesin_id' => NULL,
								'tipe' => 'upah_pengawas',
								'hk' => 0
							);
							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
						}
					}
					if ($premi_kerani > 0) {
						if ($lokasi_tugas_id_kerani == $retrieve_header['lokasi_id']) { // JIKA lokasi karyawan dan lokasi bekerja sama
							$dataDebet = array(
								'lokasi_id' => $retrieve_header['lokasi_id'],
								'jurnal_id' => $id_header,
								'acc_akun_id' => $akun_debet_transit_upah, //akun biaya Panen,
								'debet' => $premi_kerani,
								'kredit' => 0,
								'ket' => 'Biaya Pengawas(Kerani) Panen Premi ',
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL,
								'kegiatan_id' => NULL, //kegiatan ,
								'kendaraan_mesin_id' => NULL,
								'karyawan_id' => $retrieve_header['kerani_id'], //karyawan,
								'umur_tanam_blok' => NULL,
								'tipe' => 'premi_pengawas',
								'hk' => 0
							);

							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
							// Data KREDIT
							$dataKredit = array(
								'lokasi_id' => $retrieve_header['lokasi_id'],
								'jurnal_id' => $id_header,
								'acc_akun_id' => $akun_kredit_transit_upah, //$value['acc_akun_id'],
								'debet' => 0,
								'kredit' => $premi_kerani, // Akun Lawan Biaya
								'ket' => 'Biaya Pengawas(Kerani) Panen Premi ',
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL,
								'kegiatan_id' => NULL, // $value['kegiatan_id'],
								'kendaraan_mesin_id' => NULL,
								'tipe' => 'premi_pengawas',
								'hk' => 0
							);
							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
						} else { // JIKA LOKASI BERBEDA
							$dataDebet = array(
								'lokasi_id' => $retrieve_header['lokasi_id'],
								'jurnal_id' => $id_header,
								'acc_akun_id' => $akun_debet_transit_upah, //akun biaya Panen,
								'debet' => $premi_kerani,
								'kredit' => 0,
								'ket' => 'Biaya Pengawas(Kerani) Panen Premi ',
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL,
								'kegiatan_id' => NULL, //kegiatan ,
								'kendaraan_mesin_id' => NULL,
								'karyawan_id' => $retrieve_header['kerani_id'], //karyawan,
								'umur_tanam_blok' => NULL,
								'tipe' => 'premi_pengawas',
								'hk' => 0
							);

							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
							// Data KREDIT
							$dataKredit = array(
								'lokasi_id' => $retrieve_header['lokasi_id'],
								'jurnal_id' => $id_header,
								'acc_akun_id' => $inter_akun_id, //$value['acc_akun_id'],
								'debet' => 0,
								'kredit' => $premi_kerani, // Akun Lawan Biaya
								'ket' => 'Biaya Pengawas(Kerani) Panen Premi ',
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL,
								'kegiatan_id' => NULL, // $value['kegiatan_id'],
								'kendaraan_mesin_id' => NULL,
								'tipe' => 'premi_pengawas',
								'hk' => 0
							);
							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);

							$dataDebet = array(
								'lokasi_id' => $lokasi_tugas_id_kerani,
								'jurnal_id' => $id_header,
								'acc_akun_id' => $inter_akun_id, //akun biaya Panen,
								'debet' => $premi_kerani,
								'kredit' => 0,
								'ket' => 'Biaya Pengawas(Kerani) Panen Premi ',
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL,
								'kegiatan_id' => NULL, //kegiatan ,
								'kendaraan_mesin_id' => NULL,
								'karyawan_id' => $retrieve_header['kerani_id'], //karyawan,
								'umur_tanam_blok' => NULL,
								'tipe' => 'premi_pengawas',
								'hk' => 0
							);

							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
							// Data KREDIT
							$dataKredit = array(
								'lokasi_id' => $lokasi_tugas_id_kerani,
								'jurnal_id' => $id_header,
								'acc_akun_id' => $akun_kredit_transit_upah, //$value['acc_akun_id'],
								'debet' => 0,
								'kredit' => $premi_kerani, // Akun Lawan Biaya
								'ket' => 'Biaya Pengawas(Kerani) Panen Premi ',
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL,
								'kegiatan_id' => NULL, // $value['kegiatan_id'],
								'kendaraan_mesin_id' => NULL,
								'tipe' => 'premi_pengawas',
								'hk' => 0
							);
							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
						}
					}
				}
				/* eND KERANI */
			} else {
				foreach ($retrieve_detail as $key => $value) {
					$inter_akun_id = null;
					if ($akun_inter[$retrieve_header['lokasi_id']][$value['lokasi_tugas_id']]) {
						$inter_akun_id = $akun_inter[$retrieve_header['lokasi_id']][$value['lokasi_tugas_id']];
					}
					if ($akun_inter[$value['lokasi_tugas_id']][$retrieve_header['lokasi_id']]) {
						$inter_akun_id = $akun_inter[$value['lokasi_tugas_id']][$retrieve_header['lokasi_id']];
					}

					if ($value['rp_hk'] > 0) {
						$upah = $value['rp_hk'] - $value['denda_panen'];
						$premi = ($value['premi_panen'] + $value['premi_brondolan']); // nilai premi 

					} else {
						$upah = $value['rp_hk'];
						$premi = ($value['premi_panen'] + $value['premi_brondolan']) - $value['denda_panen']; // nilai premi 	
					}
					/* PEMANEN */
					if ($upah > 0) {
						if ($value['lokasi_tugas_id'] == $retrieve_header['lokasi_id']) { // JIKA lokasi karyawan dan lokasi bekerja sama
							$dataDebet = array(
								'lokasi_id' => $retrieve_header['lokasi_id'],
								'jurnal_id' => $id_header,
								'acc_akun_id' => $akun_debet_pemanen_upah, //akun biaya Panen,
								'debet' => $upah,
								'kredit' => 0,
								'ket' => 'Biaya Panen Upah Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ",",
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => $value['blok_id'],
								'kegiatan_id' => $kegiatan_panen, //kegiatan panen,
								'kendaraan_mesin_id' => NULL,
								'karyawan_id' => $value['karyawan_id'], //karyawan,
								'umur_tanam_blok' => $value['umur_tanam_blok'],
								'tipe' => 'upah',
								'hk' => $value['jumlah_hk']
							);
							$total_pendapatan =	$total_pendapatan + $value['total_pendapatan'];
							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
							// Data KREDIT
							$dataKredit = array(
								'lokasi_id' => $retrieve_header['lokasi_id'],
								'jurnal_id' => $id_header,
								'acc_akun_id' => $akun_kredit_pemanen_upah, //$value['acc_akun_id'],
								'debet' => 0,
								'kredit' => $upah, // Akun Lawan Biaya
								'ket' => 'Biaya Panen Upah Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ",",
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL,
								'kegiatan_id' => NULL, // $value['kegiatan_id'],
								'kendaraan_mesin_id' => NULL,
								'tipe' => 'upah',
								'hk' => 0
							);
							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
						} else { // JIKA LOKASI BERBEDA
							$dataDebet = array(
								'lokasi_id' => $retrieve_header['lokasi_id'],
								'jurnal_id' => $id_header,
								'acc_akun_id' => $akun_debet_pemanen_upah, //akun biaya Panen,
								'debet' => $upah,
								'kredit' => 0,
								'ket' => 'Biaya Panen Upah Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ",",
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => $value['blok_id'],
								'kegiatan_id' => $kegiatan_panen, //kegiatan panen,
								'kendaraan_mesin_id' => NULL,
								'karyawan_id' => $value['karyawan_id'], //karyawan,
								'umur_tanam_blok' => $value['umur_tanam_blok'],
								'tipe' => 'upah',
								'hk' => $value['jumlah_hk']
							);
							$total_pendapatan =	$total_pendapatan + $value['total_pendapatan'];
							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
							// Data KREDIT
							$dataKredit = array(
								'lokasi_id' => $retrieve_header['lokasi_id'],
								'jurnal_id' => $id_header,
								'acc_akun_id' => $inter_akun_id, //$value['acc_akun_id'],
								'debet' => 0,
								'kredit' => $upah, // Akun Lawan Biaya
								'ket' => 'Biaya Panen Upah Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ",",
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL,
								'kegiatan_id' => NULL, // $value['kegiatan_id'],
								'kendaraan_mesin_id' => NULL,
								'tipe' => 'upah',
								'hk' => 0
							);
							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);

							$dataDebet = array(
								'lokasi_id' => $value['lokasi_tugas_id'],
								'jurnal_id' => $id_header,
								'acc_akun_id' => $inter_akun_id, //akun biaya Panen,
								'debet' => $upah,
								'kredit' => 0,
								'ket' => 'Biaya Panen Upah Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ",",
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL, // $value['blok_id'],
								'kegiatan_id' => $kegiatan_panen, //kegiatan panen,
								'kendaraan_mesin_id' => NULL,
								'karyawan_id' => NULL, // $value['karyawan_id'], //karyawan,
								'umur_tanam_blok' => NULL, // $value['umur_tanam_blok'],
								'tipe' => 'upah',
								'hk' => $value['jumlah_hk']
							);
							// $total_pendapatan =	$total_pendapatan + $value['total_pendapatan'];
							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
							// Data KREDIT
							$dataKredit = array(
								'lokasi_id' => $value['lokasi_tugas_id'],
								'jurnal_id' => $id_header,
								'acc_akun_id' => $akun_kredit_pemanen_upah, //$value['acc_akun_id'],
								'debet' => 0,
								'kredit' => $upah, // Akun Lawan Biaya
								'ket' => 'Biaya Panen Upah Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ",",
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
					}
					if ($premi > 0) {
						if ($value['lokasi_tugas_id'] == $retrieve_header['lokasi_id']) { // JIKA lokasi karyawan dan lokasi bekerja sama
							$dataDebet = array(
								'lokasi_id' => $retrieve_header['lokasi_id'],
								'jurnal_id' => $id_header,
								'acc_akun_id' => $akun_debet_pemanen_premi, //akun biaya Panen,
								'debet' => $premi,
								'kredit' => 0,
								'ket' => 'Biaya Panen Premi Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ",",
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => $value['blok_id'],
								'kegiatan_id' => $kegiatan_panen, //kegiatan panen,
								'kendaraan_mesin_id' => NULL,
								'karyawan_id' => $value['karyawan_id'], //karyawan,
								'umur_tanam_blok' => $value['umur_tanam_blok'],
								'tipe' => 'premi',
								'hk' => 0

							);
							$total_pendapatan =	$total_pendapatan + $value['total_pendapatan'];
							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
							// Data KREDIT
							$dataKredit = array(
								'lokasi_id' => $retrieve_header['lokasi_id'],
								'jurnal_id' => $id_header,
								'acc_akun_id' => $akun_kredit_pemanen_premi, //$value['acc_akun_id'],
								'debet' => 0,
								'kredit' => $premi, // Akun Lawan Biaya
								'ket' => 'Biaya Panen Premi Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ",",
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL,
								'kegiatan_id' => NULL, // $value['kegiatan_id'],
								'kendaraan_mesin_id' => NULL,
								'tipe' => 'premi',
								'hk' => 0
							);
							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
						} else { // JIka Lokasi kerja berbeda
							$dataDebet = array(
								'lokasi_id' => $retrieve_header['lokasi_id'],
								'jurnal_id' => $id_header,
								'acc_akun_id' => $akun_debet_pemanen_premi, //akun biaya Panen,
								'debet' => $premi,
								'kredit' => 0,
								'ket' => 'Biaya Panen Premi Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ",",
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => $value['blok_id'],
								'kegiatan_id' => $kegiatan_panen, //kegiatan panen,
								'kendaraan_mesin_id' => NULL,
								'karyawan_id' => $value['karyawan_id'], //karyawan,
								'umur_tanam_blok' => $value['umur_tanam_blok'],
								'tipe' => 'premi',
								'hk' => 0

							);
							$total_pendapatan =	$total_pendapatan + $value['total_pendapatan'];
							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
							// Data KREDIT
							$dataKredit = array(
								'lokasi_id' => $retrieve_header['lokasi_id'],
								'jurnal_id' => $id_header,
								'acc_akun_id' => $inter_akun_id, //$value['acc_akun_id'],
								'debet' => 0,
								'kredit' => $premi, // Akun Lawan Biaya
								'ket' => 'Biaya Panen Premi Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ",",
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL,
								'kegiatan_id' => NULL, // $value['kegiatan_id'],
								'kendaraan_mesin_id' => NULL,
								'tipe' => 'premi',
								'hk' => 0
							);
							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);

							$dataDebet = array(
								'lokasi_id' => $value['lokasi_tugas_id'],
								'jurnal_id' => $id_header,
								'acc_akun_id' => $inter_akun_id, //akun biaya Panen,
								'debet' => $premi,
								'kredit' => 0,
								'ket' => 'Biaya Panen Premi Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ",",
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL, // $value['blok_id'],
								'kegiatan_id' => NULL, // $kegiatan_panen, //kegiatan panen,
								'kendaraan_mesin_id' => NULL,
								'karyawan_id' => NULL, // $value['karyawan_id'], //karyawan,
								'umur_tanam_blok' => NULL, // $value['umur_tanam_blok'],
								'tipe' => 'premi',
								'hk' => 0

							);
							// $total_pendapatan =	$total_pendapatan + $value['total_pendapatan'];
							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
							// Data KREDIT
							$dataKredit = array(
								'lokasi_id' => $value['lokasi_tugas_id'],
								'jurnal_id' => $id_header,
								'acc_akun_id' => $akun_kredit_pemanen_premi, //$value['acc_akun_id'],
								'debet' => 0,
								'kredit' => $premi, // Akun Lawan Biaya
								'ket' => 'Biaya Panen Premi Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ",",
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
					/* END PEMANEN */

					/* MANDOR */
					if ($value['mandor_id']) {
						$inter_akun_id = null;
						if ($akun_inter[$retrieve_header['lokasi_id']][$lokasi_tugas_id_mandor]) {
							$inter_akun_id = $akun_inter[$retrieve_header['lokasi_id']][$lokasi_tugas_id_mandor];
						}
						if ($akun_inter[$lokasi_tugas_id_mandor][$retrieve_header['lokasi_id']]) {
							$inter_akun_id = $akun_inter[$lokasi_tugas_id_mandor][$retrieve_header['lokasi_id']];
						}
						if ($upah_mandor_proporsi > 0) {
							if ($lokasi_tugas_id_mandor == $retrieve_header['lokasi_id']) { // JIKA lokasi karyawan dan lokasi bekerja sama
								$dataDebet = array(
									'lokasi_id' => $retrieve_header['lokasi_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $akun_debet_pengawas_upah, //akun biaya Panen,
									'debet' => $upah_mandor_proporsi,
									'kredit' => 0,
									'ket' => 'Biaya Pengawas(Mandor) Panen Upah Blok: ' . $value['nama_blok'],
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => $value['blok_id'],
									'kegiatan_id' => $kegiatan_pengawas_panen, //kegiatan panen,
									'kendaraan_mesin_id' => NULL,
									'karyawan_id' => $value['mandor_id'], //karyawan,
									'umur_tanam_blok' => $value['umur_tanam_blok'],
									'tipe' => 'upah_pengawas',
									'hk' => 0
								);
								$total_pendapatan =	$total_pendapatan + $value['total_pendapatan'];
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
								// Data KREDIT
								$dataKredit = array(
									'lokasi_id' => $retrieve_header['lokasi_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $akun_kredit_pengawas_upah, //$value['acc_akun_id'],
									'debet' => 0,
									'kredit' => $upah_mandor_proporsi, // Akun Lawan Biaya
									'ket' => 'Biaya Pengawas(Mandor) Panen Upah Blok: ' . $value['nama_blok'],
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL,
									'kegiatan_id' => NULL, // $value['kegiatan_id'],
									'kendaraan_mesin_id' => NULL,
									'tipe' => 'upah_pengawas',
									'hk' => 0
								);
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
							} else { // JIKA LOKASI BERBEDA
								$dataDebet = array(
									'lokasi_id' => $retrieve_header['lokasi_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $akun_debet_pengawas_upah, //akun biaya Panen,
									'debet' => $upah_mandor_proporsi,
									'kredit' => 0,
									'ket' => 'Biaya Pengawas(Mandor) Panen Upah Blok: ' . $value['nama_blok'],
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => $value['blok_id'],
									'kegiatan_id' => $kegiatan_pengawas_panen, //kegiatan panen,
									'kendaraan_mesin_id' => NULL,
									'karyawan_id' => $value['mandor_id'], //karyawan,
									'umur_tanam_blok' => $value['umur_tanam_blok'],
									'tipe' => 'upah_pengawas',
									'hk' => 0
								);
								$total_pendapatan =	$total_pendapatan + $value['total_pendapatan'];
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
								// Data KREDIT
								$dataKredit = array(
									'lokasi_id' => $retrieve_header['lokasi_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $inter_akun_id, //$value['acc_akun_id'],
									'debet' => 0,
									'kredit' => $upah_mandor_proporsi, // Akun Lawan Biaya
									'ket' => 'Biaya Pengawas(Mandor) Panen Upah Blok: ' . $value['nama_blok'],
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL,
									'kegiatan_id' => NULL, // $value['kegiatan_id'],
									'kendaraan_mesin_id' => NULL,
									'tipe' => 'upah_pengawas',
									'hk' => 0
								);
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);

								$dataDebet = array(
									'lokasi_id' => $lokasi_tugas_id_mandor,
									'jurnal_id' => $id_header,
									'acc_akun_id' => $inter_akun_id, //akun biaya Panen,
									'debet' => $upah_mandor_proporsi,
									'kredit' => 0,
									'ket' => 'Biaya Pengawas(Mandor) Panen Upah Blok: ' . $value['nama_blok'],
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL, // $value['blok_id'],
									'kegiatan_id' => NULL, // $kegiatan_panen, //kegiatan panen,
									'kendaraan_mesin_id' => NULL,
									'karyawan_id' => NULL, // $value['mandor_id'], //karyawan,
									'umur_tanam_blok' => NULL, // $value['umur_tanam_blok'],
									'tipe' => 'upah_pengawas',
									'hk' => 0
								);
								// $total_pendapatan =	$total_pendapatan + $value['total_pendapatan'];
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
								// Data KREDIT
								$dataKredit = array(
									'lokasi_id' =>  $lokasi_tugas_id_mandor,
									'jurnal_id' => $id_header,
									'acc_akun_id' => $akun_kredit_pengawas_upah, //$value['acc_akun_id'],
									'debet' => 0,
									'kredit' => $upah_mandor_proporsi, // Akun Lawan Biaya
									'ket' => 'Biaya Pengawas(Mandor) Panen Upah Blok: ' . $value['nama_blok'],
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL,
									'kegiatan_id' => NULL, // $value['kegiatan_id'],
									'kendaraan_mesin_id' => NULL,
									'tipe' => 'upah_pengawas',
									'hk' => 0
								);
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
							}
						}

						if ($premi_mandor_proporsi > 0) {
							if ($lokasi_tugas_id_mandor == $retrieve_header['lokasi_id']) { // JIKA lokasi karyawan dan lokasi bekerja sama
								$dataDebet = array(
									'lokasi_id' => $retrieve_header['lokasi_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $akun_debet_pengawas_premi, //akun biaya Panen,
									'debet' => $premi_mandor_proporsi,
									'kredit' => 0,
									'ket' => 'Biaya Pengawas(Mandor) Panen Premi Blok: ' . $value['nama_blok'],
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => $value['blok_id'],
									'kegiatan_id' => $kegiatan_panen, //kegiatan panen,
									'kendaraan_mesin_id' => NULL,
									'karyawan_id' => $value['mandor_id'], //karyawan,
									'umur_tanam_blok' => $value['umur_tanam_blok'],
									'tipe' => 'premi_pengawas',
									'hk' => 0
								);
								$total_pendapatan =	$total_pendapatan + $value['total_pendapatan'];
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
								// Data KREDIT
								$dataKredit = array(
									'lokasi_id' => $retrieve_header['lokasi_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $akun_kredit_pengawas_premi, //$value['acc_akun_id'],
									'debet' => 0,
									'kredit' => $premi_mandor_proporsi, // Akun Lawan Biaya
									'ket' => 'Biaya Pengawas(Mandor) Panen Premi Blok: ' . $value['nama_blok'],
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL,
									'kegiatan_id' => NULL, // $value['kegiatan_id'],
									'kendaraan_mesin_id' => NULL,
									'karyawan_id' => NULL, //karyawan,
									'tipe' => 'premi_pengawas',
									'hk' => 0
								);
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
							} else { // jika beda lokasi
								$dataDebet = array(
									'lokasi_id' => $retrieve_header['lokasi_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $akun_debet_pengawas_premi, //akun biaya Panen,
									'debet' => $premi_mandor_proporsi,
									'kredit' => 0,
									'ket' => 'Biaya Pengawas(Mandor) Panen Premi Blok: ' . $value['nama_blok'],
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => $value['blok_id'],
									'kegiatan_id' => $kegiatan_panen, //kegiatan panen,
									'kendaraan_mesin_id' => NULL,
									'karyawan_id' => $value['mandor_id'], //karyawan,
									'umur_tanam_blok' => $value['umur_tanam_blok'],
									'tipe' => 'premi_pengawas',
									'hk' => 0
								);
								$total_pendapatan =	$total_pendapatan + $value['total_pendapatan'];
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
								// Data KREDIT
								$dataKredit = array(
									'lokasi_id' => $retrieve_header['lokasi_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $inter_akun_id, //$value['acc_akun_id'],
									'debet' => 0,
									'kredit' => $premi_mandor_proporsi, // Akun Lawan Biaya
									'ket' => 'Biaya Pengawas(Mandor) Panen Premi Blok: ' . $value['nama_blok'],
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL,
									'kegiatan_id' => NULL, // $value['kegiatan_id'],
									'kendaraan_mesin_id' => NULL,
									'karyawan_id' => NULL, //karyawan,
									'tipe' => 'premi_pengawas',
									'hk' => 0
								);
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);

								$dataDebet = array(
									'lokasi_id' => $lokasi_tugas_id_mandor,
									'jurnal_id' => $id_header,
									'acc_akun_id' => $inter_akun_id, //akun biaya Panen,
									'debet' => $premi_mandor_proporsi,
									'kredit' => 0,
									'ket' => 'Biaya Pengawas(Mandor) Panen Premi Blok: ' . $value['nama_blok'],
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL, // $value['blok_id'],
									'kegiatan_id' => NULL, // $kegiatan_panen, //kegiatan panen,
									'kendaraan_mesin_id' => NULL,
									'karyawan_id' => NULL, // $value['mandor_id'], //karyawan,
									'umur_tanam_blok' => NULL, // $value['umur_tanam_blok'],
									'tipe' => 'premi_pengawas',
									'hk' => 0
								);
								// $total_pendapatan =	$total_pendapatan + $value['total_pendapatan'];
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
								// Data KREDIT
								$dataKredit = array(
									'lokasi_id' => $lokasi_tugas_id_mandor,
									'jurnal_id' => $id_header,
									'acc_akun_id' => $akun_kredit_pengawas_premi, //$value['acc_akun_id'],
									'debet' => 0,
									'kredit' => $premi_mandor_proporsi, // Akun Lawan Biaya
									'ket' => 'Biaya Pengawas(Mandor) Panen Premi Blok: ' . $value['nama_blok'],
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL,
									'kegiatan_id' => NULL, // $value['kegiatan_id'],
									'kendaraan_mesin_id' => NULL,
									'karyawan_id' => NULL, //karyawan,
									'tipe' => 'premi_pengawas',
									'hk' => 0
								);
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
							}
						}
					}
					/* end MANDOR */

					/* KERANI */
					if ($value['kerani_id']) {
						$inter_akun_id = null;
						if ($akun_inter[$retrieve_header['lokasi_id']][$lokasi_tugas_id_kerani]) {
							$inter_akun_id = $akun_inter[$retrieve_header['lokasi_id']][$lokasi_tugas_id_kerani];
						}
						if ($akun_inter[$lokasi_tugas_id_kerani][$retrieve_header['lokasi_id']]) {
							$inter_akun_id = $akun_inter[$lokasi_tugas_id_kerani][$retrieve_header['lokasi_id']];
						}
						if ($upah_kerani_proporsi > 0) {
							if ($lokasi_tugas_id_kerani == $retrieve_header['lokasi_id']) { // JIKA lokasi karyawan dan lokasi bekerja sama
								$dataDebet = array(
									'lokasi_id' => $retrieve_header['lokasi_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $akun_debet_pengawas_upah, //akun biaya Panen,
									'debet' => $upah_kerani_proporsi,
									'kredit' => 0,
									'ket' => 'Biaya Pengawas(Krani) Panen Upah Blok: ' . $value['nama_blok'],
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => $value['blok_id'],
									'kegiatan_id' => $kegiatan_pengawas_panen, //kegiatan panen,
									'kendaraan_mesin_id' => NULL,
									'karyawan_id' => $value['kerani_id'], //karyawan,
									'umur_tanam_blok' => $value['umur_tanam_blok'],
									'tipe' => 'upah_pengawas',
									'hk' => 0
								);
								$total_pendapatan =	$total_pendapatan + $value['total_pendapatan'];
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
								// Data KREDIT
								$dataKredit = array(
									'lokasi_id' => $retrieve_header['lokasi_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $akun_kredit_pengawas_upah, //$value['acc_akun_id'],
									'debet' => 0,
									'kredit' => $upah_kerani_proporsi, // Akun Lawan Biaya
									'ket' => 'Biaya Pengawas(Krani) Panen Upah Blok: ' . $value['nama_blok'],
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL,
									'kegiatan_id' => NULL, // $value['kegiatan_id'],
									'kendaraan_mesin_id' => NULL,
									'tipe' => 'upah_pengawas',
									'hk' => 0
								);
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
							} else { // JIKA beda lokasi
								$dataDebet = array(
									'lokasi_id' => $retrieve_header['lokasi_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $akun_debet_pengawas_upah, //akun biaya Panen,
									'debet' => $upah_kerani_proporsi,
									'kredit' => 0,
									'ket' => 'Biaya Pengawas(Krani) Panen Upah Blok: ' . $value['nama_blok'],
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => $value['blok_id'],
									'kegiatan_id' => $kegiatan_pengawas_panen, //kegiatan panen,
									'kendaraan_mesin_id' => NULL,
									'karyawan_id' => $value['kerani_id'], //karyawan,
									'umur_tanam_blok' => $value['umur_tanam_blok'],
									'tipe' => 'upah_pengawas',
									'hk' => 0
								);
								$total_pendapatan =	$total_pendapatan + $value['total_pendapatan'];
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
								// Data KREDIT
								$dataKredit = array(
									'lokasi_id' => $retrieve_header['lokasi_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $inter_akun_id, //$value['acc_akun_id'],
									'debet' => 0,
									'kredit' => $upah_kerani_proporsi, // Akun Lawan Biaya
									'ket' => 'Biaya Pengawas(Krani) Panen Upah Blok: ' . $value['nama_blok'],
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL,
									'kegiatan_id' => NULL, // $value['kegiatan_id'],
									'kendaraan_mesin_id' => NULL,
									'tipe' => 'upah_pengawas',
									'hk' => 0
								);
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);

								$dataDebet = array(
									'lokasi_id' => $lokasi_tugas_id_kerani,
									'jurnal_id' => $id_header,
									'acc_akun_id' => $inter_akun_id, //akun biaya Panen,
									'debet' => $upah_kerani_proporsi,
									'kredit' => 0,
									'ket' => 'Biaya Pengawas(Krani) Panen Upah Blok: ' . $value['nama_blok'],
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL, // $value['blok_id'],
									'kegiatan_id' => NULL, // $kegiatan_panen, //kegiatan panen,
									'kendaraan_mesin_id' => NULL,
									'karyawan_id' => NULL, // $value['kerani_id'], //karyawan,
									'umur_tanam_blok' => NULL, // $value['umur_tanam_blok'],
									'tipe' => 'upah_pengawas',
									'hk' => 0
								);
								// $total_pendapatan =	$total_pendapatan + $value['total_pendapatan'];
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
								// Data KREDIT
								$dataKredit = array(
									'lokasi_id' =>  $lokasi_tugas_id_kerani,
									'jurnal_id' => $id_header,
									'acc_akun_id' => $akun_kredit_pengawas_upah, //$value['acc_akun_id'],
									'debet' => 0,
									'kredit' => $upah_kerani_proporsi, // Akun Lawan Biaya
									'ket' => 'Biaya Pengawas(Krani) Panen Upah Blok: ' . $value['nama_blok'],
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL,
									'kegiatan_id' => NULL, // $value['kegiatan_id'],
									'kendaraan_mesin_id' => NULL,
									'tipe' => 'upah_pengawas',
									'hk' => 0
								);
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
							}
						}
						if ($premi_kerani_proporsi > 0) {
							if ($lokasi_tugas_id_kerani == $retrieve_header['lokasi_id']) { // JIKA lokasi karyawan dan lokasi bekerja sama
								$dataDebet = array(
									'lokasi_id' => $retrieve_header['lokasi_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $akun_debet_pengawas_premi, //akun biaya Panen,
									'debet' => $premi_kerani_proporsi,
									'kredit' => 0,
									'ket' => 'Biaya Pengawas(Krani) Panen Premi Blok: ' . $value['nama_blok'],
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => $value['blok_id'],
									'kegiatan_id' => $kegiatan_panen, //kegiatan panen,
									'kendaraan_mesin_id' => NULL,
									'karyawan_id' => $value['kerani_id'], //karyawan,
									'umur_tanam_blok' => $value['umur_tanam_blok'],
									'tipe' => 'premi_pengawas',
									'hk' => 0
								);
								$total_pendapatan =	$total_pendapatan + $value['total_pendapatan'];
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
								// Data KREDIT
								$dataKredit = array(
									'lokasi_id' => $retrieve_header['lokasi_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $akun_kredit_pengawas_premi, //$value['acc_akun_id'],
									'debet' => 0,
									'kredit' => $premi_kerani_proporsi, // Akun Lawan Biaya
									'ket' => 'Biaya Pengawas(Krani) Panen Premi Blok: ' . $value['nama_blok'],
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL,
									'kegiatan_id' => NULL, // $value['kegiatan_id'],
									'kendaraan_mesin_id' => NULL,
									'tipe' => 'premi_pengawas',
									'hk' => 0

								);
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
							} else { // JIka lokasi berbeda
								$dataDebet = array(
									'lokasi_id' => $retrieve_header['lokasi_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $akun_debet_pengawas_premi, //akun biaya Panen,
									'debet' => $premi_kerani_proporsi,
									'kredit' => 0,
									'ket' => 'Biaya Pengawas(Krani) Panen Premi Blok: ' . $value['nama_blok'],
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => $value['blok_id'],
									'kegiatan_id' => $kegiatan_panen, //kegiatan panen,
									'kendaraan_mesin_id' => NULL,
									'karyawan_id' => $value['kerani_id'], //karyawan,
									'umur_tanam_blok' => $value['umur_tanam_blok'],
									'tipe' => 'premi_pengawas',
									'hk' => 0
								);
								$total_pendapatan =	$total_pendapatan + $value['total_pendapatan'];
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
								// Data KREDIT
								$dataKredit = array(
									'lokasi_id' => $retrieve_header['lokasi_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $inter_akun_id, //$value['acc_akun_id'],
									'debet' => 0,
									'kredit' => $premi_kerani_proporsi, // Akun Lawan Biaya
									'ket' => 'Biaya Pengawas(Krani) Panen Premi Blok: ' . $value['nama_blok'],
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL,
									'kegiatan_id' => NULL, // $value['kegiatan_id'],
									'kendaraan_mesin_id' => NULL,
									'tipe' => 'premi_pengawas',
									'hk' => 0

								);
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);

								$dataDebet = array(
									'lokasi_id' => $lokasi_tugas_id_kerani,
									'jurnal_id' => $id_header,
									'acc_akun_id' => $inter_akun_id, //akun biaya Panen,
									'debet' => $premi_kerani_proporsi,
									'kredit' => 0,
									'ket' => 'Biaya Pengawas(Krani) Panen Premi Blok: ' . $value['nama_blok'],
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL, // $value['blok_id'],
									'kegiatan_id' => NULL, // $kegiatan_panen, //kegiatan panen,
									'kendaraan_mesin_id' => NULL,
									'karyawan_id' => NULL, // $value['kerani_id'], //karyawan,
									'umur_tanam_blok' => NULL, // $value['umur_tanam_blok'],
									'tipe' => 'premi_pengawas',
									'hk' => 0
								);
								// $total_pendapatan =	$total_pendapatan + $value['total_pendapatan'];
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
								// Data KREDIT
								$dataKredit = array(
									'lokasi_id' => $lokasi_tugas_id_kerani,
									'jurnal_id' => $id_header,
									'acc_akun_id' => $akun_kredit_pengawas_premi, //$value['acc_akun_id'],
									'debet' => 0,
									'kredit' => $premi_kerani_proporsi, // Akun Lawan Biaya
									'ket' => 'Biaya Pengawas(Krani) Panen Premi Blok: ' . $value['nama_blok'],
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL,
									'kegiatan_id' => NULL, // $value['kegiatan_id'],
									'kendaraan_mesin_id' => NULL,
									'tipe' => 'premi_pengawas',
									'hk' => 0

								);
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
							}
						}
					}
					/* eND KERANI */
				}
			}

			$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'PANEN');
			$qjurnalTemp = "SELECT b.lokasi_id,a.id,SUM(debet)AS debet ,SUM(kredit)AS kredit ,sum(hk)AS hk,acc_akun_id,blok_stasiun_id,kegiatan_id,
				umur_tanam_blok ,kendaraan_mesin_id,c.nama AS blok,d.nama AS kendaraan
				FROM acc_jurnal_upah_ht a INNER JOIN acc_jurnal_upah_dt b
				ON a.id=b.jurnal_id
				LEFT JOIN gbm_organisasi c ON b.blok_stasiun_id=c.id
				LEFT JOIN trk_kendaraan d ON b.kendaraan_mesin_id=d.id
				WHERE a.id=" . $id_header . "
				GROUP by b.lokasi_id, acc_akun_id,blok_stasiun_id,kegiatan_id,umur_tanam_blok,a.id,kendaraan_mesin_id,c.nama,d.nama
				ORDER BY debet desc";
			$resJurnalTemp = $this->db->query($qjurnalTemp)->result_array();
			if ($resJurnalTemp) {
				$no_jurnal = $this->autonumber->jurnal_auto($retrieve_header['lokasi_id'], $retrieve_header['tanggal'], 'PNN');

				$dataH = array(
					'no_jurnal' => $no_jurnal,
					'lokasi_id' => $retrieve_header['lokasi_id'],
					'tanggal' => $retrieve_header['tanggal'],
					'no_ref' => $retrieve_header['no_transaksi'],
					'ref_id' => $retrieve_header['id'],
					'tipe_jurnal' => 'AUTO',
					'modul' => 'PANEN',
					'keterangan' => 'TRANSAKSI PANEN',
					'is_posting' => 1,
				);
				$id_header2 = $this->AccJurnalModel->create_header($dataH);

				foreach ($resJurnalTemp as $key => $JurnalTemp) {
					$ket = '';
					if ($JurnalTemp['blok']) {
						$ket = 'Panen (Upah/premi) Blok: ' . $JurnalTemp['blok'];
					} else {
						$ket = 'Panen (Upah/premi)';
					}
					if ($JurnalTemp['hk'] > 0) {
						$ket = $ket . ' ' . $JurnalTemp['hk'] . 'Hk';
					}
					$dataDebetKredit = array(
						'lokasi_id' => $JurnalTemp['lokasi_id'], // $retrieve_header['lokasi_id'],
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
		}
		$input['diposting_oleh'] = $this->user_id;
		$res = $this->EstBkmPanenModel->posting($id, $input);
		if (!empty($res)) {
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function posting_batch_post($lokasi_id = null, $t1 = null, $t2 = null)
	{

		/* -- GET SETTING AKUN --*/
		$res_akun_panen_upah = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='PANEN_KEBUN_UPAH'")->row_array();
		if (empty($res_akun_panen_upah)) {
			$this->set_response(array("status" => "NOT OK", "data" => "PANEN_KEBUN_UPAH Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$res_akun_panen_premi = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='PANEN_KEBUN_PREMI'")->row_array();
		if (empty($res_akun_panen_premi)) {
			$this->set_response(array("status" => "NOT OK", "data" => "PANEN_KEBUN_PREMI Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$res_akun_panen_upah_pengawas = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='PANEN_KEBUN_UPAH_PENGAWAS'")->row_array();
		if (empty($res_akun_panen_upah_pengawas)) {
			$this->set_response(array("status" => "NOT OK", "data" => "PANEN_KEBUN_UPAH_PENGAWAS Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$res_akun_panen_premi_pengawas = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='PANEN_KEBUN_PREMI_PENGAWAS'")->row_array();
		if (empty($res_akun_panen_premi_pengawas)) {
			$this->set_response(array("status" => "NOT OK", "data" => "PANEN_KEBUN_PREMI_PENGAWAS Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$akun_debet_pemanen_premi = $res_akun_panen_premi['acc_akun_id_debet'];
		$akun_kredit_pemanen_premi = $res_akun_panen_premi['acc_akun_id_kredit'];
		$akun_debet_pemanen_upah = $res_akun_panen_upah['acc_akun_id_debet'];
		$akun_kredit_pemanen_upah = $res_akun_panen_upah['acc_akun_id_kredit'];
		$akun_debet_pengawas_premi = $res_akun_panen_premi_pengawas['acc_akun_id_debet'];
		$akun_kredit_pengawas_premi = $res_akun_panen_premi_pengawas['acc_akun_id_kredit'];
		$akun_debet_pengawas_upah = $res_akun_panen_upah_pengawas['acc_akun_id_debet'];
		$akun_kredit_pengawas_upah = $res_akun_panen_upah_pengawas['acc_akun_id_kredit'];
		$retrieve_inter_akun = $this->db->query("SELECT * FROM acc_inter_unit
		where tipe='INTER' ")->result_array();
		$akun_inter = array();
		foreach ($retrieve_inter_akun as $key => $akun) {
			$akun_inter[$akun['lokasi_id']][$akun['lokasi_id_2']] = $akun['acc_akun_id'];
		}
		$kegiatan_panen = 1; // POTONG BUAH -- sementara hardcode 
		$kegiatan_pengawas_panen = 6; // sementara hardcode 
		/* END GET SETTING AKUN */
		// $retrieve_header = $this->EstBkmPanenModel->retrieve_by_id($id);
		// if (empty($retrieve_header)) {
		// 	$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Untuk diposting"), REST_Controller::HTTP_NOT_FOUND);
		// 	return;
		// } else {
		// 	if ($retrieve_header['is_posting'] == 1) {
		// 		$this->set_response(array("status" => "NOT OK", "data" => "Data sudah diposting"), REST_Controller::HTTP_NOT_FOUND);
		// 		return;
		// 	}
		// }
		$res_transaksi_panen = $this->db->query("SELECT * from est_bkm_panen_ht where  
		tanggal between '" . $t1 . "' and '" . $t2 . "' and is_posting=0
		and lokasi_id=" . $lokasi_id . " order by tanggal")->result_array();
		$id = null;
		$jum = 0;
		foreach ($res_transaksi_panen  as $key => $retrieve_header) {
			$id = (int) $retrieve_header['id'];
			$lokasi_tugas_id_mandor = null;
			$lokasi_tugas_id_kerani = null;
			if ($retrieve_header['mandor_id']) {
				$res_mandor = $this->db->query("select * from karyawan where id=" . $retrieve_header['mandor_id'])->row_array();
				$lokasi_tugas_id_mandor = $res_mandor['lokasi_tugas_id'];
			}
			if ($retrieve_header['kerani_id']) {
				$res_kerani = $this->db->query("select * from karyawan where id=" . $retrieve_header['kerani_id'])->row_array();
				$lokasi_tugas_id_kerani = $res_kerani['lokasi_tugas_id'];
			}
			$jum++;
			$sql = "SELECT a.*,b.*,c.nip,c.nama as nama_karyawan, d.kode as kode_blok,d.nama as nama_blok, 
			e.kode as kode_kegiatan,e.nama as nama_kegiatan ,f.statusblok as umur_tanam_blok,f.tahuntanam,c.lokasi_tugas_id
			FROM est_bkm_panen_ht a 
			inner join est_bkm_panen_dt b on a.id=b.bkm_panen_id 
			inner join karyawan c on b.karyawan_id=c.id 
			left join gbm_organisasi d on b.blok_id=d.id 
			left join gbm_blok f on b.blok_id=f.organisasi_id
			left join acc_kegiatan e on b.acc_kegiatan_id =e.id 
			where b.bkm_panen_id=" . $id . "";
			$retrieve_detail = $this->db->query($sql)->result_array();

			// 		// Hapus jurnal jika sdh ada berdasarkan no refernsi dan MOdul  //
			$this->AccJurnalUpahModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'PANEN');
			$total_pendapatan = 0;
			$id_header = 0;
			/* JIka bukan premi kontanan maka jurnal*/
			if (!$retrieve_header['is_premi_kontanan'] || $retrieve_header['is_premi_kontanan'] == '0') {
				// $this->set_response(array("status" => "NOT OK", "data" =>$retrieve_header ), REST_Controller::HTTP_NOT_FOUND);
				// return;
				// Data HEADER
				$this->load->library('Autonumber');
				$no_jurnal = $this->autonumber->jurnal_upah_auto($retrieve_header['lokasi_id'], $retrieve_header['tanggal'], 'PNN');

				$dataH = array(
					'no_jurnal' => $no_jurnal,
					'lokasi_id' => $retrieve_header['lokasi_id'],
					'tanggal' => $retrieve_header['tanggal'],
					'no_ref' => $retrieve_header['no_transaksi'],
					'ref_id' => $retrieve_header['id'],
					'tipe_jurnal' => 'AUTO',
					'modul' => 'PANEN',
					'keterangan' => 'TRANSAKSI PANEN',
					'is_posting' => 1,
				);
				$id_header = $this->AccJurnalUpahModel->create_header($dataH);
				$total_pendapatan = 0;
				$upah_mandor_proporsi = 0;
				$premi_mandor_proporsi = 0;
				$upah_kerani_proporsi = 0;
				$premi_kerani_proporsi = 0;
				if ($retrieve_header['rp_hk_mandor'] > 0) {
					$upah_mandor = $retrieve_header['rp_hk_mandor'] - $retrieve_header['denda_mandor'];
					$premi_mandor = $retrieve_header['premi_mandor'];
				} else {
					$upah_mandor = $retrieve_header['rp_hk_mandor'];
					$premi_mandor = $retrieve_header['premi_mandor'] - $retrieve_header['denda_mandor'];
				}

				if ($retrieve_header['rp_hk_kerani'] > 0) {
					$upah_kerani = $retrieve_header['rp_hk_kerani'] - $retrieve_header['denda_kerani'];
					$premi_kerani = $retrieve_header['premi_kerani'];
				} else {
					$upah_kerani = $retrieve_header['rp_hk_kerani'];
					$premi_kerani = $retrieve_header['premi_kerani'] - $retrieve_header['denda_kerani'];
				}


				$jumlah_pemanen = count($retrieve_detail);
				if ($upah_mandor > 0) {
					$upah_mandor_proporsi = $upah_mandor / $jumlah_pemanen;
				}
				if ($premi_mandor > 0) {
					$premi_mandor_proporsi = $premi_mandor / $jumlah_pemanen;
				}
				if ($upah_kerani > 0) {
					$upah_kerani_proporsi = $upah_kerani / $jumlah_pemanen;
				}
				if ($premi_kerani > 0) {
					$premi_kerani_proporsi = $premi_kerani / $jumlah_pemanen;
				}
				if ($jumlah_pemanen <= 0) { // JIka tdk ada anggotanya maka alokasikan ke umum
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
					/* MANDOR */
					if ($retrieve_header['mandor_id']) {
						$inter_akun_id = null;
						if ($akun_inter[$retrieve_header['lokasi_id']][$lokasi_tugas_id_mandor]) {
							$inter_akun_id = $akun_inter[$retrieve_header['lokasi_id']][$lokasi_tugas_id_mandor];
						}
						if ($akun_inter[$lokasi_tugas_id_mandor][$retrieve_header['lokasi_id']]) {
							$inter_akun_id = $akun_inter[$lokasi_tugas_id_mandor][$retrieve_header['lokasi_id']];
						}
						if ($upah_mandor > 0) {
							if ($lokasi_tugas_id_mandor == $retrieve_header['lokasi_id']) { // JIKA lokasi karyawan dan lokasi bekerja sama
								$dataDebet = array(
									'lokasi_id' => $retrieve_header['lokasi_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $akun_debet_transit_upah, //akun biaya Panen,
									'debet' => $upah_mandor,
									'kredit' => 0,
									'ket' => 'Biaya Pengawas(Mandor) Panen ',
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL,
									'kegiatan_id' => NULL, //kegiatan panen,
									'kendaraan_mesin_id' => NULL,
									'karyawan_id' => $retrieve_header['mandor_id'], //karyawan,
									'umur_tanam_blok' => NULL,
									'tipe' => 'upah_pengawas',
									'hk' => 0
								);

								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
								// Data KREDIT
								$dataKredit = array(
									'lokasi_id' => $retrieve_header['lokasi_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $akun_kredit_transit_upah, //$value['acc_akun_id'],
									'debet' => 0,
									'kredit' => $upah_mandor, // Akun Lawan Biaya
									'ket' => 'Biaya Pengawas(Mandor) Panen Upah',
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL,
									'kegiatan_id' => NULL, // $value['kegiatan_id'],
									'kendaraan_mesin_id' => NULL,
									'tipe' => 'upah_pengawas',
									'hk' => 0
								);
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
							} else { // Jika lokasi bekerja berbeda

								$dataDebet = array(
									'lokasi_id' => $retrieve_header['lokasi_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $akun_debet_transit_upah, //akun biaya Panen,
									'debet' => $upah_mandor,
									'kredit' => 0,
									'ket' => 'Biaya Pengawas(Mandor) Panen ',
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL,
									'kegiatan_id' => NULL, //kegiatan panen,
									'kendaraan_mesin_id' => NULL,
									'karyawan_id' => $retrieve_header['mandor_id'], //karyawan,
									'umur_tanam_blok' => NULL,
									'tipe' => 'upah_pengawas',
									'hk' => 0
								);

								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
								// Data KREDIT
								$dataKredit = array(
									'lokasi_id' => $retrieve_header['lokasi_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $inter_akun_id, //$value['acc_akun_id'],
									'debet' => 0,
									'kredit' => $upah_mandor, // Akun Lawan Biaya
									'ket' => 'Biaya Pengawas(Mandor) Panen Upah',
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL,
									'kegiatan_id' => NULL, // $value['kegiatan_id'],
									'kendaraan_mesin_id' => NULL,
									'tipe' => 'upah_pengawas',
									'hk' => 0
								);
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);

								$dataDebet = array(
									'lokasi_id' => $lokasi_tugas_id_mandor,
									'jurnal_id' => $id_header,
									'acc_akun_id' => $inter_akun_id, //akun biaya Panen,
									'debet' => $upah_mandor,
									'kredit' => 0,
									'ket' => 'Biaya Pengawas(Mandor) Panen ',
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL,
									'kegiatan_id' => NULL, //kegiatan panen,
									'kendaraan_mesin_id' => NULL,
									'karyawan_id' => $retrieve_header['mandor_id'], //karyawan,
									'umur_tanam_blok' => NULL,
									'tipe' => 'upah_pengawas',
									'hk' => 0
								);

								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
								// Data KREDIT
								$dataKredit = array(
									'lokasi_id' => $lokasi_tugas_id_mandor,
									'jurnal_id' => $id_header,
									'acc_akun_id' => $akun_kredit_transit_upah, //$value['acc_akun_id'],
									'debet' => 0,
									'kredit' => $upah_mandor, // Akun Lawan Biaya
									'ket' => 'Biaya Pengawas(Mandor) Panen Upah',
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL,
									'kegiatan_id' => NULL, // $value['kegiatan_id'],
									'kendaraan_mesin_id' => NULL,
									'tipe' => 'upah_pengawas',
									'hk' => 0
								);
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
							}
						}
						if ($premi_mandor > 0) {
							if ($lokasi_tugas_id_mandor == $retrieve_header['lokasi_id']) { // JIKA lokasi karyawan dan lokasi bekerja sama
								$dataDebet = array(
									'lokasi_id' => $retrieve_header['lokasi_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $akun_debet_transit_premi, //akun biaya Panen,
									'debet' => $premi_mandor,
									'kredit' => 0,
									'ket' => 'Biaya Pengawas(Mandor) Panen Premi',
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL,
									'kegiatan_id' => NULL, //kegiatan panen,
									'kendaraan_mesin_id' => NULL,
									'karyawan_id' => $retrieve_header['mandor_id'], //karyawan,
									'umur_tanam_blok' => NULL,
									'tipe' => 'premi_pengawas',
									'hk' => 0
								);

								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
								// Data KREDIT
								$dataKredit = array(
									'lokasi_id' => $retrieve_header['lokasi_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $akun_kredit_transit_premi, //$value['acc_akun_id'],
									'debet' => 0,
									'kredit' => $premi_mandor, // Akun Lawan Biaya
									'ket' => 'Biaya Pengawas(Mandor) Panen Premi ',
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL,
									'kegiatan_id' => NULL, // $value['kegiatan_id'],
									'kendaraan_mesin_id' => NULL,
									'tipe' => 'premi_pengawas',
									'hk' => 0
								);
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
							} else { // JIKA lokasi karyawan dan lokasi bekerja berbeda
								$dataDebet = array(
									'lokasi_id' => $retrieve_header['lokasi_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $akun_debet_transit_premi, //akun biaya Panen,
									'debet' => $premi_mandor,
									'kredit' => 0,
									'ket' => 'Biaya Pengawas(Mandor) Panen Premi',
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL,
									'kegiatan_id' => NULL, //kegiatan panen,
									'kendaraan_mesin_id' => NULL,
									'karyawan_id' => $retrieve_header['mandor_id'], //karyawan,
									'umur_tanam_blok' => NULL,
									'tipe' => 'premi_pengawas',
									'hk' => 0
								);

								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
								// Data KREDIT
								$dataKredit = array(
									'lokasi_id' => $retrieve_header['lokasi_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $inter_akun_id, //$value['acc_akun_id'],
									'debet' => 0,
									'kredit' => $premi_mandor, // Akun Lawan Biaya
									'ket' => 'Biaya Pengawas(Mandor) Panen Premi ',
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL,
									'kegiatan_id' => NULL, // $value['kegiatan_id'],
									'kendaraan_mesin_id' => NULL,
									'tipe' => 'premi_pengawas',
									'hk' => 0
								);
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);

								$dataDebet = array(
									'lokasi_id' => $lokasi_tugas_id_mandor,
									'jurnal_id' => $id_header,
									'acc_akun_id' => $inter_akun_id, //akun biaya Panen,
									'debet' => $premi_mandor,
									'kredit' => 0,
									'ket' => 'Biaya Pengawas(Mandor) Panen Premi',
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL,
									'kegiatan_id' => NULL, //kegiatan panen,
									'kendaraan_mesin_id' => NULL,
									'karyawan_id' => $retrieve_header['mandor_id'], //karyawan,
									'umur_tanam_blok' => NULL,
									'tipe' => 'premi_pengawas',
									'hk' => 0
								);

								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
								// Data KREDIT
								$dataKredit = array(
									'lokasi_id' => $lokasi_tugas_id_mandor,
									'jurnal_id' => $id_header,
									'acc_akun_id' => $akun_kredit_transit_premi, //$value['acc_akun_id'],
									'debet' => 0,
									'kredit' => $premi_mandor, // Akun Lawan Biaya
									'ket' => 'Biaya Pengawas(Mandor) Panen Premi ',
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL,
									'kegiatan_id' => NULL, // $value['kegiatan_id'],
									'kendaraan_mesin_id' => NULL,
									'tipe' => 'premi_pengawas',
									'hk' => 0
								);
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
							}
						}
					}
					/* end MANDOR */

					/* KERANI */
					if ($retrieve_header['kerani_id']) {
						$inter_akun_id = null;
						if ($akun_inter[$retrieve_header['lokasi_id']][$lokasi_tugas_id_kerani]) {
							$inter_akun_id = $akun_inter[$retrieve_header['lokasi_id']][$lokasi_tugas_id_kerani];
						}
						if ($akun_inter[$lokasi_tugas_id_kerani][$retrieve_header['lokasi_id']]) {
							$inter_akun_id = $akun_inter[$lokasi_tugas_id_kerani][$retrieve_header['lokasi_id']];
						}
						if ($upah_kerani > 0) {
							if ($lokasi_tugas_id_kerani == $retrieve_header['lokasi_id']) { // JIKA lokasi karyawan dan lokasi bekerja sama

								$dataDebet = array(
									'lokasi_id' => $retrieve_header['lokasi_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $akun_debet_transit_upah, //akun biaya Panen,
									'debet' => $upah_kerani,
									'kredit' => 0,
									'ket' => 'Biaya Pengawas(Kerani) Panen',
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL,
									'kegiatan_id' => NULL, //kegiatan panen,
									'kendaraan_mesin_id' => NULL,
									'karyawan_id' => $retrieve_header['kerani_id'], //karyawan,
									'umur_tanam_blok' => NULL,
									'tipe' => 'upah_pengawas',
									'hk' => 0
								);
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
								// Data KREDIT
								$dataKredit = array(
									'lokasi_id' => $retrieve_header['lokasi_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $akun_kredit_transit_upah, //$value['acc_akun_id'],
									'debet' => 0,
									'kredit' => $upah_kerani, // Akun Lawan Biaya
									'ket' => 'Biaya Pengawas(Kerani) Panen Upah',
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL,
									'kegiatan_id' => NULL, // $value['kegiatan_id'],
									'kendaraan_mesin_id' => NULL,
									'tipe' => 'upah_pengawas',
									'hk' => 0
								);
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
							} else { // Jika lokasi kerja berbeda
								$dataDebet = array(
									'lokasi_id' => $retrieve_header['lokasi_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $akun_debet_transit_upah, //akun biaya Panen,
									'debet' => $upah_kerani,
									'kredit' => 0,
									'ket' => 'Biaya Pengawas(Kerani) Panen',
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL,
									'kegiatan_id' => NULL, //kegiatan panen,
									'kendaraan_mesin_id' => NULL,
									'karyawan_id' => $retrieve_header['kerani_id'], //karyawan,
									'umur_tanam_blok' => NULL,
									'tipe' => 'upah_pengawas',
									'hk' => 0
								);
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
								// Data KREDIT
								$dataKredit = array(
									'lokasi_id' => $retrieve_header['lokasi_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $inter_akun_id, //$value['acc_akun_id'],
									'debet' => 0,
									'kredit' => $upah_kerani, // Akun Lawan Biaya
									'ket' => 'Biaya Pengawas(Kerani) Panen Upah',
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL,
									'kegiatan_id' => NULL, // $value['kegiatan_id'],
									'kendaraan_mesin_id' => NULL,
									'tipe' => 'upah_pengawas',
									'hk' => 0
								);
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);

								$dataDebet = array(
									'lokasi_id' => $lokasi_tugas_id_kerani,
									'jurnal_id' => $id_header,
									'acc_akun_id' => $inter_akun_id, //akun biaya Panen,
									'debet' => $upah_kerani,
									'kredit' => 0,
									'ket' => 'Biaya Pengawas(Kerani) Panen',
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL,
									'kegiatan_id' => NULL, //kegiatan panen,
									'kendaraan_mesin_id' => NULL,
									'karyawan_id' => $retrieve_header['kerani_id'], //karyawan,
									'umur_tanam_blok' => NULL,
									'tipe' => 'upah_pengawas',
									'hk' => 0
								);
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
								// Data KREDIT
								$dataKredit = array(
									'lokasi_id' => $lokasi_tugas_id_kerani,
									'jurnal_id' => $id_header,
									'acc_akun_id' => $akun_kredit_transit_upah, //$value['acc_akun_id'],
									'debet' => 0,
									'kredit' => $upah_kerani, // Akun Lawan Biaya
									'ket' => 'Biaya Pengawas(Kerani) Panen Upah',
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL,
									'kegiatan_id' => NULL, // $value['kegiatan_id'],
									'kendaraan_mesin_id' => NULL,
									'tipe' => 'upah_pengawas',
									'hk' => 0
								);
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
							}
						}
						if ($premi_kerani > 0) {
							if ($lokasi_tugas_id_kerani == $retrieve_header['lokasi_id']) { // JIKA lokasi karyawan dan lokasi bekerja sama

								$dataDebet = array(
									'lokasi_id' => $retrieve_header['lokasi_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $akun_debet_transit_upah, //akun biaya Panen,
									'debet' => $premi_kerani,
									'kredit' => 0,
									'ket' => 'Biaya Pengawas(Kerani) Panen Premi ',
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL,
									'kegiatan_id' => NULL, //kegiatan ,
									'kendaraan_mesin_id' => NULL,
									'karyawan_id' => $retrieve_header['kerani_id'], //karyawan,
									'umur_tanam_blok' => NULL,
									'tipe' => 'premi_pengawas',
									'hk' => 0
								);

								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
								// Data KREDIT
								$dataKredit = array(
									'lokasi_id' => $retrieve_header['lokasi_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $akun_kredit_transit_upah, //$value['acc_akun_id'],
									'debet' => 0,
									'kredit' => $premi_kerani, // Akun Lawan Biaya
									'ket' => 'Biaya Pengawas(Kerani) Panen Premi ',
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL,
									'kegiatan_id' => NULL, // $value['kegiatan_id'],
									'kendaraan_mesin_id' => NULL,
									'tipe' => 'premi_pengawas',
									'hk' => 0
								);
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
							} else { // JIKA LOKASI BERBEDA
								$dataDebet = array(
									'lokasi_id' => $retrieve_header['lokasi_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $akun_debet_transit_upah, //akun biaya Panen,
									'debet' => $premi_kerani,
									'kredit' => 0,
									'ket' => 'Biaya Pengawas(Kerani) Panen Premi ',
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL,
									'kegiatan_id' => NULL, //kegiatan ,
									'kendaraan_mesin_id' => NULL,
									'karyawan_id' => $retrieve_header['kerani_id'], //karyawan,
									'umur_tanam_blok' => NULL,
									'tipe' => 'premi_pengawas',
									'hk' => 0
								);

								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
								// Data KREDIT
								$dataKredit = array(
									'lokasi_id' => $retrieve_header['lokasi_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $inter_akun_id, //$value['acc_akun_id'],
									'debet' => 0,
									'kredit' => $premi_kerani, // Akun Lawan Biaya
									'ket' => 'Biaya Pengawas(Kerani) Panen Premi ',
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL,
									'kegiatan_id' => NULL, // $value['kegiatan_id'],
									'kendaraan_mesin_id' => NULL,
									'tipe' => 'premi_pengawas',
									'hk' => 0
								);
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);

								$dataDebet = array(
									'lokasi_id' => $lokasi_tugas_id_kerani,
									'jurnal_id' => $id_header,
									'acc_akun_id' => $inter_akun_id, //akun biaya Panen,
									'debet' => $premi_kerani,
									'kredit' => 0,
									'ket' => 'Biaya Pengawas(Kerani) Panen Premi ',
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL,
									'kegiatan_id' => NULL, //kegiatan ,
									'kendaraan_mesin_id' => NULL,
									'karyawan_id' => $retrieve_header['kerani_id'], //karyawan,
									'umur_tanam_blok' => NULL,
									'tipe' => 'premi_pengawas',
									'hk' => 0
								);

								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
								// Data KREDIT
								$dataKredit = array(
									'lokasi_id' => $lokasi_tugas_id_kerani,
									'jurnal_id' => $id_header,
									'acc_akun_id' => $akun_kredit_transit_upah, //$value['acc_akun_id'],
									'debet' => 0,
									'kredit' => $premi_kerani, // Akun Lawan Biaya
									'ket' => 'Biaya Pengawas(Kerani) Panen Premi ',
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL,
									'kegiatan_id' => NULL, // $value['kegiatan_id'],
									'kendaraan_mesin_id' => NULL,
									'tipe' => 'premi_pengawas',
									'hk' => 0
								);
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
							}
						}
					}

					/* eND KERANI */
				} else {
					foreach ($retrieve_detail as $key => $value) {
						$inter_akun_id = null;
						if ($akun_inter[$retrieve_header['lokasi_id']][$value['lokasi_tugas_id']]) {
							$inter_akun_id = $akun_inter[$retrieve_header['lokasi_id']][$value['lokasi_tugas_id']];
						}
						if ($akun_inter[$value['lokasi_tugas_id']][$retrieve_header['lokasi_id']]) {
							$inter_akun_id = $akun_inter[$value['lokasi_tugas_id']][$retrieve_header['lokasi_id']];
						}
						if ($value['rp_hk'] > 0) {
							$upah = $value['rp_hk'] - $value['denda_panen'];
							$premi = ($value['premi_panen'] + $value['premi_brondolan']); // nilai premi 

						} else {
							$upah = $value['rp_hk'];
							$premi = ($value['premi_panen'] + $value['premi_brondolan']) - $value['denda_panen']; // nilai premi 	
						}
						/* PEMANEN */
						if ($upah > 0) {
							if ($value['lokasi_tugas_id'] == $retrieve_header['lokasi_id']) { // JIKA lokasi karyawan dan lokasi bekerja sama	
								$dataDebet = array(
									'lokasi_id' => $retrieve_header['lokasi_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $akun_debet_pemanen_upah, //akun biaya Panen,
									'debet' => $upah,
									'kredit' => 0,
									'ket' => 'Biaya Panen Upah Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ",",
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => $value['blok_id'],
									'kegiatan_id' => $kegiatan_panen, //kegiatan panen,
									'kendaraan_mesin_id' => NULL,
									'karyawan_id' => $value['karyawan_id'], //karyawan,
									'umur_tanam_blok' => $value['umur_tanam_blok'],
									'tipe' => 'upah',
									'hk' => $value['jumlah_hk']
								);
								$total_pendapatan =	$total_pendapatan + $value['total_pendapatan'];
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
								// Data KREDIT
								$dataKredit = array(
									'lokasi_id' => $retrieve_header['lokasi_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $akun_kredit_pemanen_upah, //$value['acc_akun_id'],
									'debet' => 0,
									'kredit' => $upah, // Akun Lawan Biaya
									'ket' => 'Biaya Panen Upah Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ",",
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL,
									'kegiatan_id' => NULL, // $value['kegiatan_id'],
									'kendaraan_mesin_id' => NULL,
									'tipe' => 'upah',
									'hk' => 0
								);
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
							} else { // JIKA LOKASI BERBEDA
								$dataDebet = array(
									'lokasi_id' => $retrieve_header['lokasi_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $akun_debet_pemanen_upah, //akun biaya Panen,
									'debet' => $upah,
									'kredit' => 0,
									'ket' => 'Biaya Panen Upah Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ",",
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => $value['blok_id'],
									'kegiatan_id' => $kegiatan_panen, //kegiatan panen,
									'kendaraan_mesin_id' => NULL,
									'karyawan_id' => $value['karyawan_id'], //karyawan,
									'umur_tanam_blok' => $value['umur_tanam_blok'],
									'tipe' => 'upah',
									'hk' => $value['jumlah_hk']
								);
								$total_pendapatan =	$total_pendapatan + $value['total_pendapatan'];
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
								// Data KREDIT
								$dataKredit = array(
									'lokasi_id' => $retrieve_header['lokasi_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $inter_akun_id, //$value['acc_akun_id'],
									'debet' => 0,
									'kredit' => $upah, // Akun Lawan Biaya
									'ket' => 'Biaya Panen Upah Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ",",
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL,
									'kegiatan_id' => NULL, // $value['kegiatan_id'],
									'kendaraan_mesin_id' => NULL,
									'tipe' => 'upah',
									'hk' => 0
								);
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);

								$dataDebet = array(
									'lokasi_id' => $value['lokasi_tugas_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $inter_akun_id, //akun biaya Panen,
									'debet' => $upah,
									'kredit' => 0,
									'ket' => 'Biaya Panen Upah Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ",",
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL, // $value['blok_id'],
									'kegiatan_id' => $kegiatan_panen, //kegiatan panen,
									'kendaraan_mesin_id' => NULL,
									'karyawan_id' => NULL, // $value['karyawan_id'], //karyawan,
									'umur_tanam_blok' => NULL, // $value['umur_tanam_blok'],
									'tipe' => 'upah',
									'hk' => $value['jumlah_hk']
								);
								// $total_pendapatan =	$total_pendapatan + $value['total_pendapatan'];
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
								// Data KREDIT
								$dataKredit = array(
									'lokasi_id' => $value['lokasi_tugas_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $akun_kredit_pemanen_upah, //$value['acc_akun_id'],
									'debet' => 0,
									'kredit' => $upah, // Akun Lawan Biaya
									'ket' => 'Biaya Panen Upah Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ",",
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
						}
						if ($premi > 0) {
							if ($value['lokasi_tugas_id'] == $retrieve_header['lokasi_id']) { // JIKA lokasi karyawan dan lokasi bekerja sama
								$dataDebet = array(
									'lokasi_id' => $retrieve_header['lokasi_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $akun_debet_pemanen_premi, //akun biaya Panen,
									'debet' => $premi,
									'kredit' => 0,
									'ket' => 'Biaya Panen Premi Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ",",
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => $value['blok_id'],
									'kegiatan_id' => $kegiatan_panen, //kegiatan panen,
									'kendaraan_mesin_id' => NULL,
									'karyawan_id' => $value['karyawan_id'], //karyawan,
									'umur_tanam_blok' => $value['umur_tanam_blok'],
									'tipe' => 'premi',
									'hk' => 0

								);
								$total_pendapatan =	$total_pendapatan + $value['total_pendapatan'];
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
								// Data KREDIT
								$dataKredit = array(
									'lokasi_id' => $retrieve_header['lokasi_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $akun_kredit_pemanen_premi, //$value['acc_akun_id'],
									'debet' => 0,
									'kredit' => $premi, // Akun Lawan Biaya
									'ket' => 'Biaya Panen Premi Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ",",
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL,
									'kegiatan_id' => NULL, // $value['kegiatan_id'],
									'kendaraan_mesin_id' => NULL,
									'tipe' => 'premi',
									'hk' => 0
								);
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
							} else { // JIka Lokasi kerja berbeda
								$dataDebet = array(
									'lokasi_id' => $retrieve_header['lokasi_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $akun_debet_pemanen_premi, //akun biaya Panen,
									'debet' => $premi,
									'kredit' => 0,
									'ket' => 'Biaya Panen Premi Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ",",
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => $value['blok_id'],
									'kegiatan_id' => $kegiatan_panen, //kegiatan panen,
									'kendaraan_mesin_id' => NULL,
									'karyawan_id' => $value['karyawan_id'], //karyawan,
									'umur_tanam_blok' => $value['umur_tanam_blok'],
									'tipe' => 'premi',
									'hk' => 0

								);
								$total_pendapatan =	$total_pendapatan + $value['total_pendapatan'];
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
								// Data KREDIT
								$dataKredit = array(
									'lokasi_id' => $retrieve_header['lokasi_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $inter_akun_id, //$value['acc_akun_id'],
									'debet' => 0,
									'kredit' => $premi, // Akun Lawan Biaya
									'ket' => 'Biaya Panen Premi Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ",",
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL,
									'kegiatan_id' => NULL, // $value['kegiatan_id'],
									'kendaraan_mesin_id' => NULL,
									'tipe' => 'premi',
									'hk' => 0
								);
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);

								$dataDebet = array(
									'lokasi_id' => $value['lokasi_tugas_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $inter_akun_id, //akun biaya Panen,
									'debet' => $premi,
									'kredit' => 0,
									'ket' => 'Biaya Panen Premi Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ",",
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL, // $value['blok_id'],
									'kegiatan_id' => NULL, // $kegiatan_panen, //kegiatan panen,
									'kendaraan_mesin_id' => NULL,
									'karyawan_id' => NULL, // $value['karyawan_id'], //karyawan,
									'umur_tanam_blok' => NULL, // $value['umur_tanam_blok'],
									'tipe' => 'premi',
									'hk' => 0

								);
								// $total_pendapatan =	$total_pendapatan + $value['total_pendapatan'];
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
								// Data KREDIT
								$dataKredit = array(
									'lokasi_id' => $value['lokasi_tugas_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $akun_kredit_pemanen_premi, //$value['acc_akun_id'],
									'debet' => 0,
									'kredit' => $premi, // Akun Lawan Biaya
									'ket' => 'Biaya Panen Premi Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ",",
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
						/* END PEMANEN */

						/* MANDOR */
						if ($value['mandor_id']) {
							$inter_akun_id = null;
							if ($akun_inter[$retrieve_header['lokasi_id']][$lokasi_tugas_id_mandor]) {
								$inter_akun_id = $akun_inter[$retrieve_header['lokasi_id']][$lokasi_tugas_id_mandor];
							}
							if ($akun_inter[$lokasi_tugas_id_mandor][$retrieve_header['lokasi_id']]) {
								$inter_akun_id = $akun_inter[$lokasi_tugas_id_mandor][$retrieve_header['lokasi_id']];
							}
							if ($upah_mandor_proporsi > 0) {
								if ($lokasi_tugas_id_mandor == $retrieve_header['lokasi_id']) { // JIKA lokasi karyawan dan lokasi bekerja sama
									$dataDebet = array(
										'lokasi_id' => $retrieve_header['lokasi_id'],
										'jurnal_id' => $id_header,
										'acc_akun_id' => $akun_debet_pengawas_upah, //akun biaya Panen,
										'debet' => $upah_mandor_proporsi,
										'kredit' => 0,
										'ket' => 'Biaya Pengawas(Mandor) Panen Upah Blok: ' . $value['nama_blok'],
										'no_referensi' => $retrieve_header['no_transaksi'],
										'referensi_id' => NULL,
										'blok_stasiun_id' => $value['blok_id'],
										'kegiatan_id' => $kegiatan_pengawas_panen, //kegiatan panen,
										'kendaraan_mesin_id' => NULL,
										'karyawan_id' => $value['mandor_id'], //karyawan,
										'umur_tanam_blok' => $value['umur_tanam_blok'],
										'tipe' => 'upah_pengawas',
										'hk' => 0
									);
									$total_pendapatan =	$total_pendapatan + $value['total_pendapatan'];
									$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
									// Data KREDIT
									$dataKredit = array(
										'lokasi_id' => $retrieve_header['lokasi_id'],
										'jurnal_id' => $id_header,
										'acc_akun_id' => $akun_kredit_pengawas_upah, //$value['acc_akun_id'],
										'debet' => 0,
										'kredit' => $upah_mandor_proporsi, // Akun Lawan Biaya
										'ket' => 'Biaya Pengawas(Mandor) Panen Upah Blok: ' . $value['nama_blok'],
										'no_referensi' => $retrieve_header['no_transaksi'],
										'referensi_id' => NULL,
										'blok_stasiun_id' => NULL,
										'kegiatan_id' => NULL, // $value['kegiatan_id'],
										'kendaraan_mesin_id' => NULL,
										'tipe' => 'upah_pengawas',
										'hk' => 0
									);
									$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
								} else { // JIKA LOKASI BERBEDA
									$dataDebet = array(
										'lokasi_id' => $retrieve_header['lokasi_id'],
										'jurnal_id' => $id_header,
										'acc_akun_id' => $akun_debet_pengawas_upah, //akun biaya Panen,
										'debet' => $upah_mandor_proporsi,
										'kredit' => 0,
										'ket' => 'Biaya Pengawas(Mandor) Panen Upah Blok: ' . $value['nama_blok'],
										'no_referensi' => $retrieve_header['no_transaksi'],
										'referensi_id' => NULL,
										'blok_stasiun_id' => $value['blok_id'],
										'kegiatan_id' => $kegiatan_pengawas_panen, //kegiatan panen,
										'kendaraan_mesin_id' => NULL,
										'karyawan_id' => $value['mandor_id'], //karyawan,
										'umur_tanam_blok' => $value['umur_tanam_blok'],
										'tipe' => 'upah_pengawas',
										'hk' => 0
									);
									$total_pendapatan =	$total_pendapatan + $value['total_pendapatan'];
									$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
									// Data KREDIT
									$dataKredit = array(
										'lokasi_id' => $retrieve_header['lokasi_id'],
										'jurnal_id' => $id_header,
										'acc_akun_id' => $inter_akun_id, //$value['acc_akun_id'],
										'debet' => 0,
										'kredit' => $upah_mandor_proporsi, // Akun Lawan Biaya
										'ket' => 'Biaya Pengawas(Mandor) Panen Upah Blok: ' . $value['nama_blok'],
										'no_referensi' => $retrieve_header['no_transaksi'],
										'referensi_id' => NULL,
										'blok_stasiun_id' => NULL,
										'kegiatan_id' => NULL, // $value['kegiatan_id'],
										'kendaraan_mesin_id' => NULL,
										'tipe' => 'upah_pengawas',
										'hk' => 0
									);
									$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);

									$dataDebet = array(
										'lokasi_id' => $lokasi_tugas_id_mandor,
										'jurnal_id' => $id_header,
										'acc_akun_id' => $inter_akun_id, //akun biaya Panen,
										'debet' => $upah_mandor_proporsi,
										'kredit' => 0,
										'ket' => 'Biaya Pengawas(Mandor) Panen Upah Blok: ' . $value['nama_blok'],
										'no_referensi' => $retrieve_header['no_transaksi'],
										'referensi_id' => NULL,
										'blok_stasiun_id' => NULL, // $value['blok_id'],
										'kegiatan_id' => NULL, // $kegiatan_panen, //kegiatan panen,
										'kendaraan_mesin_id' => NULL,
										'karyawan_id' => NULL, // $value['mandor_id'], //karyawan,
										'umur_tanam_blok' => NULL, // $value['umur_tanam_blok'],
										'tipe' => 'upah_pengawas',
										'hk' => 0
									);
									// $total_pendapatan =	$total_pendapatan + $value['total_pendapatan'];
									$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
									// Data KREDIT
									$dataKredit = array(
										'lokasi_id' =>  $lokasi_tugas_id_mandor,
										'jurnal_id' => $id_header,
										'acc_akun_id' => $akun_kredit_pengawas_upah, //$value['acc_akun_id'],
										'debet' => 0,
										'kredit' => $upah_mandor_proporsi, // Akun Lawan Biaya
										'ket' => 'Biaya Pengawas(Mandor) Panen Upah Blok: ' . $value['nama_blok'],
										'no_referensi' => $retrieve_header['no_transaksi'],
										'referensi_id' => NULL,
										'blok_stasiun_id' => NULL,
										'kegiatan_id' => NULL, // $value['kegiatan_id'],
										'kendaraan_mesin_id' => NULL,
										'tipe' => 'upah_pengawas',
										'hk' => 0
									);
									$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
								}
							}

							if ($premi_mandor_proporsi > 0) {
								if ($lokasi_tugas_id_mandor == $retrieve_header['lokasi_id']) { // JIKA lokasi karyawan dan lokasi bekerja sama
									$dataDebet = array(
										'lokasi_id' => $retrieve_header['lokasi_id'],
										'jurnal_id' => $id_header,
										'acc_akun_id' => $akun_debet_pengawas_premi, //akun biaya Panen,
										'debet' => $premi_mandor_proporsi,
										'kredit' => 0,
										'ket' => 'Biaya Pengawas(Mandor) Panen Premi Blok: ' . $value['nama_blok'],
										'no_referensi' => $retrieve_header['no_transaksi'],
										'referensi_id' => NULL,
										'blok_stasiun_id' => $value['blok_id'],
										'kegiatan_id' => $kegiatan_panen, //kegiatan panen,
										'kendaraan_mesin_id' => NULL,
										'karyawan_id' => $value['mandor_id'], //karyawan,
										'umur_tanam_blok' => $value['umur_tanam_blok'],
										'tipe' => 'premi_pengawas',
										'hk' => 0
									);
									$total_pendapatan =	$total_pendapatan + $value['total_pendapatan'];
									$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
									// Data KREDIT
									$dataKredit = array(
										'lokasi_id' => $retrieve_header['lokasi_id'],
										'jurnal_id' => $id_header,
										'acc_akun_id' => $akun_kredit_pengawas_premi, //$value['acc_akun_id'],
										'debet' => 0,
										'kredit' => $premi_mandor_proporsi, // Akun Lawan Biaya
										'ket' => 'Biaya Pengawas(Mandor) Panen Premi Blok: ' . $value['nama_blok'],
										'no_referensi' => $retrieve_header['no_transaksi'],
										'referensi_id' => NULL,
										'blok_stasiun_id' => NULL,
										'kegiatan_id' => NULL, // $value['kegiatan_id'],
										'kendaraan_mesin_id' => NULL,
										'tipe' => 'premi_pengawas',
										'hk' => 0
									);
									$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
								} else { // jika beda lokasi
									$dataDebet = array(
										'lokasi_id' => $retrieve_header['lokasi_id'],
										'jurnal_id' => $id_header,
										'acc_akun_id' => $akun_debet_pengawas_premi, //akun biaya Panen,
										'debet' => $premi_mandor_proporsi,
										'kredit' => 0,
										'ket' => 'Biaya Pengawas(Mandor) Panen Premi Blok: ' . $value['nama_blok'],
										'no_referensi' => $retrieve_header['no_transaksi'],
										'referensi_id' => NULL,
										'blok_stasiun_id' => $value['blok_id'],
										'kegiatan_id' => $kegiatan_panen, //kegiatan panen,
										'kendaraan_mesin_id' => NULL,
										'karyawan_id' => $value['mandor_id'], //karyawan,
										'umur_tanam_blok' => $value['umur_tanam_blok'],
										'tipe' => 'premi_pengawas',
										'hk' => 0
									);
									$total_pendapatan =	$total_pendapatan + $value['total_pendapatan'];
									$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
									// Data KREDIT
									$dataKredit = array(
										'lokasi_id' => $retrieve_header['lokasi_id'],
										'jurnal_id' => $id_header,
										'acc_akun_id' => $inter_akun_id, //$value['acc_akun_id'],
										'debet' => 0,
										'kredit' => $premi_mandor_proporsi, // Akun Lawan Biaya
										'ket' => 'Biaya Pengawas(Mandor) Panen Premi Blok: ' . $value['nama_blok'],
										'no_referensi' => $retrieve_header['no_transaksi'],
										'referensi_id' => NULL,
										'blok_stasiun_id' => NULL,
										'kegiatan_id' => NULL, // $value['kegiatan_id'],
										'kendaraan_mesin_id' => NULL,
										'karyawan_id' => NULL, //karyawan,
										'tipe' => 'premi_pengawas',
										'hk' => 0
									);
									$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);

									$dataDebet = array(
										'lokasi_id' => $lokasi_tugas_id_mandor,
										'jurnal_id' => $id_header,
										'acc_akun_id' => $inter_akun_id, //akun biaya Panen,
										'debet' => $premi_mandor_proporsi,
										'kredit' => 0,
										'ket' => 'Biaya Pengawas(Mandor) Panen Premi Blok: ' . $value['nama_blok'],
										'no_referensi' => $retrieve_header['no_transaksi'],
										'referensi_id' => NULL,
										'blok_stasiun_id' => NULL, // $value['blok_id'],
										'kegiatan_id' => NULL, // $kegiatan_panen, //kegiatan panen,
										'kendaraan_mesin_id' => NULL,
										'karyawan_id' => NULL, // $value['mandor_id'], //karyawan,
										'umur_tanam_blok' => NULL, // $value['umur_tanam_blok'],
										'tipe' => 'premi_pengawas',
										'hk' => 0
									);
									// $total_pendapatan =	$total_pendapatan + $value['total_pendapatan'];
									$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
									// Data KREDIT
									$dataKredit = array(
										'lokasi_id' => $lokasi_tugas_id_mandor,
										'jurnal_id' => $id_header,
										'acc_akun_id' => $akun_kredit_pengawas_premi, //$value['acc_akun_id'],
										'debet' => 0,
										'kredit' => $premi_mandor_proporsi, // Akun Lawan Biaya
										'ket' => '8.Biaya Pengawas(Mandor) Panen Premi Blok: ' . $value['nama_blok'],
										'no_referensi' => $retrieve_header['no_transaksi'],
										'referensi_id' => NULL,
										'blok_stasiun_id' => NULL,
										'kegiatan_id' => NULL, // $value['kegiatan_id'],
										'kendaraan_mesin_id' => NULL,
										'karyawan_id' => NULL, //karyawan,
										'tipe' => 'premi_pengawas',
										'hk' => 0
									);
									$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
								}
							}
						}

						/* end MANDOR */

						/* KERANI */
						if ($value['kerani_id']) {
							$inter_akun_id = null;
							if ($akun_inter[$retrieve_header['lokasi_id']][$lokasi_tugas_id_kerani]) {
								$inter_akun_id = $akun_inter[$retrieve_header['lokasi_id']][$lokasi_tugas_id_kerani];
							}
							if ($akun_inter[$lokasi_tugas_id_kerani][$retrieve_header['lokasi_id']]) {
								$inter_akun_id = $akun_inter[$lokasi_tugas_id_kerani][$retrieve_header['lokasi_id']];
							}
							if ($upah_kerani_proporsi > 0) {
								if ($lokasi_tugas_id_kerani == $retrieve_header['lokasi_id']) { // JIKA lokasi karyawan dan lokasi bekerja sama
									$dataDebet = array(
										'lokasi_id' => $retrieve_header['lokasi_id'],
										'jurnal_id' => $id_header,
										'acc_akun_id' => $akun_debet_pengawas_upah, //akun biaya Panen,
										'debet' => $upah_kerani_proporsi,
										'kredit' => 0,
										'ket' => 'Biaya Pengawas(Krani) Panen Upah Blok: ' . $value['nama_blok'],
										'no_referensi' => $retrieve_header['no_transaksi'],
										'referensi_id' => NULL,
										'blok_stasiun_id' => $value['blok_id'],
										'kegiatan_id' => $kegiatan_pengawas_panen, //kegiatan panen,
										'kendaraan_mesin_id' => NULL,
										'karyawan_id' => $value['kerani_id'], //karyawan,
										'umur_tanam_blok' => $value['umur_tanam_blok'],
										'tipe' => 'upah_pengawas',
										'hk' => 0
									);
									$total_pendapatan =	$total_pendapatan + $value['total_pendapatan'];
									$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
									// Data KREDIT
									$dataKredit = array(
										'lokasi_id' => $retrieve_header['lokasi_id'],
										'jurnal_id' => $id_header,
										'acc_akun_id' => $akun_kredit_pengawas_upah, //$value['acc_akun_id'],
										'debet' => 0,
										'kredit' => $upah_kerani_proporsi, // Akun Lawan Biaya
										'ket' => 'Biaya Pengawas(Krani) Panen Upah Blok: ' . $value['nama_blok'],
										'no_referensi' => $retrieve_header['no_transaksi'],
										'referensi_id' => NULL,
										'blok_stasiun_id' => NULL,
										'kegiatan_id' => NULL, // $value['kegiatan_id'],
										'kendaraan_mesin_id' => NULL,
										'tipe' => 'upah_pengawas',
										'hk' => 0
									);
									$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
								} else { // JIKA beda lokasi
									$dataDebet = array(
										'lokasi_id' => $retrieve_header['lokasi_id'],
										'jurnal_id' => $id_header,
										'acc_akun_id' => $akun_debet_pengawas_upah, //akun biaya Panen,
										'debet' => $upah_kerani_proporsi,
										'kredit' => 0,
										'ket' => 'Biaya Pengawas(Krani) Panen Upah Blok: ' . $value['nama_blok'],
										'no_referensi' => $retrieve_header['no_transaksi'],
										'referensi_id' => NULL,
										'blok_stasiun_id' => $value['blok_id'],
										'kegiatan_id' => $kegiatan_pengawas_panen, //kegiatan panen,
										'kendaraan_mesin_id' => NULL,
										'karyawan_id' => $value['kerani_id'], //karyawan,
										'umur_tanam_blok' => $value['umur_tanam_blok'],
										'tipe' => 'upah_pengawas',
										'hk' => 0
									);
									$total_pendapatan =	$total_pendapatan + $value['total_pendapatan'];
									$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
									// Data KREDIT
									$dataKredit = array(
										'lokasi_id' => $retrieve_header['lokasi_id'],
										'jurnal_id' => $id_header,
										'acc_akun_id' => $inter_akun_id, //$value['acc_akun_id'],
										'debet' => 0,
										'kredit' => $upah_kerani_proporsi, // Akun Lawan Biaya
										'ket' => 'Biaya Pengawas(Krani) Panen Upah Blok: ' . $value['nama_blok'],
										'no_referensi' => $retrieve_header['no_transaksi'],
										'referensi_id' => NULL,
										'blok_stasiun_id' => NULL,
										'kegiatan_id' => NULL, // $value['kegiatan_id'],
										'kendaraan_mesin_id' => NULL,
										'tipe' => 'upah_pengawas',
										'hk' => 0
									);
									$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);

									$dataDebet = array(
										'lokasi_id' => $lokasi_tugas_id_kerani,
										'jurnal_id' => $id_header,
										'acc_akun_id' => $inter_akun_id, //akun biaya Panen,
										'debet' => $upah_kerani_proporsi,
										'kredit' => 0,
										'ket' => 'Biaya Pengawas(Krani) Panen Upah Blok: ' . $value['nama_blok'],
										'no_referensi' => $retrieve_header['no_transaksi'],
										'referensi_id' => NULL,
										'blok_stasiun_id' => NULL, // $value['blok_id'],
										'kegiatan_id' => NULL, // $kegiatan_panen, //kegiatan panen,
										'kendaraan_mesin_id' => NULL,
										'karyawan_id' => NULL, // $value['kerani_id'], //karyawan,
										'umur_tanam_blok' => NULL, // $value['umur_tanam_blok'],
										'tipe' => 'upah_pengawas',
										'hk' => 0
									);
									// $total_pendapatan =	$total_pendapatan + $value['total_pendapatan'];
									$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
									// Data KREDIT
									$dataKredit = array(
										'lokasi_id' =>  $lokasi_tugas_id_kerani,
										'jurnal_id' => $id_header,
										'acc_akun_id' => $akun_kredit_pengawas_upah, //$value['acc_akun_id'],
										'debet' => 0,
										'kredit' => $upah_kerani_proporsi, // Akun Lawan Biaya
										'ket' => 'Biaya Pengawas(Krani) Panen Upah Blok: ' . $value['nama_blok'],
										'no_referensi' => $retrieve_header['no_transaksi'],
										'referensi_id' => NULL,
										'blok_stasiun_id' => NULL,
										'kegiatan_id' => NULL, // $value['kegiatan_id'],
										'kendaraan_mesin_id' => NULL,
										'tipe' => 'upah_pengawas',
										'hk' => 0
									);
									$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
								}
							}
							if ($premi_kerani_proporsi > 0) {
								if ($lokasi_tugas_id_kerani == $retrieve_header['lokasi_id']) { // JIKA lokasi karyawan dan lokasi bekerja sama
									$dataDebet = array(
										'lokasi_id' => $retrieve_header['lokasi_id'],
										'jurnal_id' => $id_header,
										'acc_akun_id' => $akun_debet_pengawas_premi, //akun biaya Panen,
										'debet' => $premi_kerani_proporsi,
										'kredit' => 0,
										'ket' => 'Biaya Pengawas(Krani) Panen Premi Blok: ' . $value['nama_blok'],
										'no_referensi' => $retrieve_header['no_transaksi'],
										'referensi_id' => NULL,
										'blok_stasiun_id' => $value['blok_id'],
										'kegiatan_id' => $kegiatan_panen, //kegiatan panen,
										'kendaraan_mesin_id' => NULL,
										'karyawan_id' => $value['kerani_id'], //karyawan,
										'umur_tanam_blok' => $value['umur_tanam_blok'],
										'tipe' => 'premi_pengawas',
										'hk' => 0
									);
									$total_pendapatan =	$total_pendapatan + $value['total_pendapatan'];
									$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
									// Data KREDIT
									$dataKredit = array(
										'lokasi_id' => $retrieve_header['lokasi_id'],
										'jurnal_id' => $id_header,
										'acc_akun_id' => $akun_kredit_pengawas_premi, //$value['acc_akun_id'],
										'debet' => 0,
										'kredit' => $premi_kerani_proporsi, // Akun Lawan Biaya
										'ket' => 'Biaya Pengawas(Krani) Panen Premi Blok: ' . $value['nama_blok'],
										'no_referensi' => $retrieve_header['no_transaksi'],
										'referensi_id' => NULL,
										'blok_stasiun_id' => NULL,
										'kegiatan_id' => NULL, // $value['kegiatan_id'],
										'kendaraan_mesin_id' => NULL,
										'tipe' => 'premi_pengawas',
										'hk' => 0
									);
									$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
								} else { // JIka lokasi berbeda
									$dataDebet = array(
										'lokasi_id' => $retrieve_header['lokasi_id'],
										'jurnal_id' => $id_header,
										'acc_akun_id' => $akun_debet_pengawas_premi, //akun biaya Panen,
										'debet' => $premi_kerani_proporsi,
										'kredit' => 0,
										'ket' => 'Biaya Pengawas(Krani) Panen Premi Blok: ' . $value['nama_blok'],
										'no_referensi' => $retrieve_header['no_transaksi'],
										'referensi_id' => NULL,
										'blok_stasiun_id' => $value['blok_id'],
										'kegiatan_id' => $kegiatan_panen, //kegiatan panen,
										'kendaraan_mesin_id' => NULL,
										'karyawan_id' => $value['kerani_id'], //karyawan,
										'umur_tanam_blok' => $value['umur_tanam_blok'],
										'tipe' => 'premi_pengawas',
										'hk' => 0
									);
									$total_pendapatan =	$total_pendapatan + $value['total_pendapatan'];
									$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
									// Data KREDIT
									$dataKredit = array(
										'lokasi_id' => $retrieve_header['lokasi_id'],
										'jurnal_id' => $id_header,
										'acc_akun_id' => $inter_akun_id, //$value['acc_akun_id'],
										'debet' => 0,
										'kredit' => $premi_kerani_proporsi, // Akun Lawan Biaya
										'ket' => 'Biaya Pengawas(Krani) Panen Premi Blok: ' . $value['nama_blok'],
										'no_referensi' => $retrieve_header['no_transaksi'],
										'referensi_id' => NULL,
										'blok_stasiun_id' => NULL,
										'kegiatan_id' => NULL, // $value['kegiatan_id'],
										'kendaraan_mesin_id' => NULL,
										'tipe' => 'premi_pengawas',
										'hk' => 0

									);
									$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);

									$dataDebet = array(
										'lokasi_id' => $lokasi_tugas_id_kerani,
										'jurnal_id' => $id_header,
										'acc_akun_id' => $inter_akun_id, //akun biaya Panen,
										'debet' => $premi_kerani_proporsi,
										'kredit' => 0,
										'ket' => 'Biaya Pengawas(Krani) Panen Premi Blok: ' . $value['nama_blok'],
										'no_referensi' => $retrieve_header['no_transaksi'],
										'referensi_id' => NULL,
										'blok_stasiun_id' => NULL, // $value['blok_id'],
										'kegiatan_id' => NULL, // $kegiatan_panen, //kegiatan panen,
										'kendaraan_mesin_id' => NULL,
										'karyawan_id' => NULL, // $value['kerani_id'], //karyawan,
										'umur_tanam_blok' => NULL, // $value['umur_tanam_blok'],
										'tipe' => 'premi_pengawas',
										'hk' => 0
									);
									// $total_pendapatan =	$total_pendapatan + $value['total_pendapatan'];
									$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
									// Data KREDIT
									$dataKredit = array(
										'lokasi_id' => $lokasi_tugas_id_kerani,
										'jurnal_id' => $id_header,
										'acc_akun_id' => $akun_kredit_pengawas_premi, //$value['acc_akun_id'],
										'debet' => 0,
										'kredit' => $premi_kerani_proporsi, // Akun Lawan Biaya
										'ket' => 'Biaya Pengawas(Krani) Panen Premi Blok: ' . $value['nama_blok'],
										'no_referensi' => $retrieve_header['no_transaksi'],
										'referensi_id' => NULL,
										'blok_stasiun_id' => NULL,
										'kegiatan_id' => NULL, // $value['kegiatan_id'],
										'kendaraan_mesin_id' => NULL,
										'tipe' => 'premi_pengawas',
										'hk' => 0

									);
									$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
								}
							}
						}

						/* eND KERANI */
					}
				}
				$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'PANEN');
				$qjurnalTemp = "SELECT b.lokasi_id,a.id,SUM(debet)AS debet ,SUM(kredit)AS kredit ,sum(hk)AS hk,acc_akun_id,blok_stasiun_id,kegiatan_id,
				umur_tanam_blok ,kendaraan_mesin_id,c.nama AS blok,d.nama AS kendaraan
				FROM acc_jurnal_upah_ht a INNER JOIN acc_jurnal_upah_dt b
				ON a.id=b.jurnal_id
				LEFT JOIN gbm_organisasi c ON b.blok_stasiun_id=c.id
				LEFT JOIN trk_kendaraan d ON b.kendaraan_mesin_id=d.id
				WHERE a.id=" . $id_header . "
				GROUP by b.lokasi_id,acc_akun_id,blok_stasiun_id,kegiatan_id,umur_tanam_blok,a.id,kendaraan_mesin_id,c.nama,d.nama
				ORDER BY debet desc";
				$resJurnalTemp = $this->db->query($qjurnalTemp)->result_array();
				if ($resJurnalTemp) {
					$no_jurnal = $this->autonumber->jurnal_auto($retrieve_header['lokasi_id'], $retrieve_header['tanggal'], 'PNN');

					$dataH = array(
						'no_jurnal' => $no_jurnal,
						'lokasi_id' => $retrieve_header['lokasi_id'],
						'tanggal' => $retrieve_header['tanggal'],
						'no_ref' => $retrieve_header['no_transaksi'],
						'ref_id' => $retrieve_header['id'],
						'tipe_jurnal' => 'AUTO',
						'modul' => 'PANEN',
						'keterangan' => 'TRANSAKSI PANEN',
						'is_posting' => 1,
					);
					$id_header2 = $this->AccJurnalModel->create_header($dataH);

					foreach ($resJurnalTemp as $key => $JurnalTemp) {
						$ket = '';
						if ($JurnalTemp['blok']) {
							$ket = 'Panen (Upah/premi) Blok: ' . $JurnalTemp['blok'];
						} else {
							$ket = 'Panen (Upah/premi)';
						}
						if ($JurnalTemp['hk'] > 0) {
							$ket = $ket . ' ' . $JurnalTemp['hk'] . 'Hk';
						}
						$dataDebetKredit = array(
							'lokasi_id' => $JurnalTemp['lokasi_id'], // $retrieve_header['lokasi_id'],
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
			}


			$res = $this->EstBkmPanenModel->posting($id, null);
			if (!empty($res)) {
				//$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
			} else {
				//$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
			}
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

		try {

			if (!$input['blok_id']['id']) {
				$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_OK);
				return;
			}
			if (!$input['karyawan_id']['id']) {
				$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_OK);
				return;
			}
			$details_denda = $input['details_denda'];
			$total_denda = 0;
			foreach ($details_denda as $key_denda => $value_denda) {
				$total_denda += ($value_denda['qty'] * $value_denda['nilai']);
			}

			$jjg = (float)$input['hasil_kerja_jjg'];
			$potongan = (float)$input['potongan'];
			$resBjr = $this->db->query("select bjr from est_bjr where blok_id=" . $input['blok_id']['id'] . " ")->row_array();
			$stdBJR = $resBjr['bjr'];
			$hasilkerjakg = ($jjg * $stdBJR);


			$resBasisBlok = $this->db->query("select * from est_premi_basis_panen where blok_id='" . $input['blok_id']['id'] . "'
		 and tanggal_efektif <='" . ($input['tanggal']) . "'
		 order by tanggal_efektif desc limit 1 ")->row_array();
			$premi = 0;
			$hari = date("w", strtotime($input['tanggal']));
			$premibrondolan = $input['hasil_kerja_brondolan'] * $resBasisBlok['premi_brondolan'];
			$basis_jjg = 0;
			if ($hari == 5) {
				$basis_jjg = $resBasisBlok['basis_jjg_jumat'];
			} else {
				$basis_jjg = $resBasisBlok['basis_jjg'];
			}
			if (!$basis_jjg) {
				$basis_jjg = 0;
			}
			if ($jjg >= $basis_jjg) {
				$premi = (float) $resBasisBlok['premi_basis'];
			} else {
				$premi = 0;
			}
			// Premi Lebih Basis
			//Lebih basis 1
			$premlb = 0;
			$sisa1 = $jjg - $basis_jjg;
			if ($sisa1 > $resBasisBlok['lebih_basis1']) {
				$premlb += $sisa1 * $resBasisBlok['premi_lebih_basis1'];
			} else {
				$premlb += 0;
			}
			// $this->set_response(array("status" => "OK", "data" =>$premlb), REST_Controller::HTTP_CREATED);
			// return;
			//Lebih basis 2
			$sisa2 = $sisa1 - $resBasisBlok['lebih_basis1'];
			if ($sisa2 > $resBasisBlok['lebih_basis2']) {
				$premlb += $sisa2 * $resBasisBlok['premi_lebih_basis2'];
			} else {
				$premlb += 0;
			}

			//Lebih basis 3
			$sisa3 = $sisa2 - $resBasisBlok['lebih_basis2'];
			if ($sisa3 > $resBasisBlok['lebih_basis3']) {
				$premlb += $sisa3 * $resBasisBlok['premi_lebih_basis3'];
			} else {
				$premlb += 0;
			}

			$resGaji = $this->db->query("select * from payroll_karyawan_gaji where karyawan_id=" . $input['karyawan_id']['id'] . " ")->row_array();
			$upahharian = ($resGaji['gapok'] / 25);
			// $basis = $resBasisBlok['basis_jjg'];
			$upahpenalty = 0;
			/* Utk potongan HK
		if ($input['hasil_kerja_jjg'] <= $basis) {
			$capaibasis = ($basis - $input['hasil_kerja_jjg']) / $basis;
			$upahpenalty = $upahharian * $capaibasis;
		}
		*/

			// $dendarp = 0;
			// $dendajjg = 0;
			// $denda = array(
			// 	'jjg' => 0,
			// 	'rp' => 0
			// );

			// //Denda Panen
			// if (is_array($input['denda'])) {
			// 	foreach ($input['denda'] as $kode => $val) {
			// 		if (isset($optDenda[$kode])) {
			// 			if ($optDenda[$kode]['jenis'] == 'JANJANG') {
			// 				$denda['jjg'] += $val * $optDenda[$kode]['nilai'];
			// 			} elseif ($optDenda[$kode]['jenis'] == 'RUPIAH') {
			// 				$denda['rp'] += $val * $optDenda[$kode]['nilai'];
			// 			}
			// 		}
			// 	}
			// }
			// $dendajjg = $denda['jjg'];
			// $dendarp = $denda['rp'];
			$jumlah_hk = $input['jumlah_hk'];
			$upahharian = $jumlah_hk * $upahharian;
			$res = array();
			$totalpendapatan = (($upahharian + $premibrondolan + $premi + $premlb) - ($upahpenalty + $total_denda + $potongan));
			/* Utk Proporsi HK 
		$jumlah_hk = 0;
		if ($upahharian > 0) {
			$jumlah_hk = ($upahharian - $upahpenalty) / $upahharian;
		}
		*/


			$res = array(
				'arr_premi_basis_panen' => $resBasisBlok,
				'basis_jjg' => $basis_jjg,
				'hasil_kerja_kg' => $hasilkerjakg,
				'premi_brondolan' => $premibrondolan,
				'premi_panen' => $premi + $premlb,
				'denda_basis' => $upahpenalty,
				'denda_panen' => $total_denda,
				'bjr' => $stdBJR,
				'jumlah_hk' => $jumlah_hk,
				// 'dendarp' => $dendarp,
				// 'dendajjg' => $dendajjg,
				'total_pendapatan' => $totalpendapatan,
				//'hari' => $jenisPremi,
				'rp_hk' => $upahharian,
				'upah_premi_lebih_basis' => $premlb,
				'rpkgkontanan' => 0,
				'kontanan' => 0,
				'hari' => $hari,
				'premi_basis' => $premi

			);

			if (!empty($res)) {
				$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
			} else {
				$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_OK);
			}
		} catch (\Throwable $th) {
			//throw $th;
			$this->set_response(array("status" => "NOT OK", "data" =>  $th), REST_Controller::HTTP_OK);
		}
	}

	function hitung_premi_mandor_kerani($input)
	{
		try {

			$lokasi_id = $input['lokasi_id']['id'];
			$details = $input['details'];
			$total_premi = 0;
			$total_upah = 0;
			$jum_karyawan = 0;
			$jumlah_nilai_denda = 0;
			$upah_mandor = 0;
			$upah_kerani = 0;
			$premi_mandor = 0;
			$premi_kerani = 0;
			foreach ($details as $key => $value) {
				$total_premi =	$total_premi + $value['premi_brondolan'] + $value['premi_panen'];
				$total_upah =	$total_upah + $value['rp_hk'];
				$jum_karyawan++;

				$details_denda = $value['details_denda'];
				foreach ($details_denda as $key_denda => $value_denda) {
					$jumlah_nilai_denda = $jumlah_nilai_denda + ($value_denda['qty'] * $value_denda['nilai']);
				}
			}
			$persen_premi_mandor = 0;
			$persen_premi_kerani = 0;
			$jumlah_karyawan_mandor = 10;
			$jumlah_karyawan_kerani = 10;
			/* BAGIAN MULAI UTK HITUNG PREMI DAN UPAH MANDOR/KRANI */
			if ($input['mandor_id']['id']) {
				$q0 = "SELECT a.*,b.status_pajak FROM  payroll_karyawan_gaji a inner join karyawan b on a.karyawan_id=b.id
				where a.karyawan_id=" .  $input['mandor_id']['id'] . "";
				$mandor = $this->db->query($q0)->row_array();
				$upah_mandor = $mandor['gapok'] / 25;

				$q0 = "SELECT * FROM  est_param_premi_mandor_kerani 
				where lokasi_id=" .  $lokasi_id . " and jabatan_id='MANDOR'";
				$p_mandor = $this->db->query($q0)->row_array();
				if ($p_mandor) {
					$jumlah_karyawan_mandor = $p_mandor['jumlah_karyawan'];
					$persen_premi_mandor = $p_mandor['persen_premi'];
				}
				$premi_mandor = 0;
				$total_premi_dan_denda = $total_premi - $jumlah_nilai_denda;
				if ($jum_karyawan <	$jumlah_karyawan_mandor) {
					$premi_mandor = ($persen_premi_mandor) *	($total_premi_dan_denda / $jumlah_karyawan_mandor);
				} else {
					$premi_mandor = $persen_premi_mandor  *	($total_premi_dan_denda / $jum_karyawan);
				}
			}
			if ($input['kerani_id']['id']) {
				$q0 = "SELECT a.*,b.status_pajak FROM  payroll_karyawan_gaji a inner join karyawan b on a.karyawan_id=b.id
				where a.karyawan_id=" .  $input['kerani_id']['id'] . "";
				$kerani = $this->db->query($q0)->row_array();
				$upah_kerani = $kerani['gapok'] / 25;

				$q0 = "SELECT * FROM  est_param_premi_mandor_kerani 
				where lokasi_id=" .  $lokasi_id . " and jabatan_id='KERANI'";
				$p_kerani = $this->db->query($q0)->row_array();
				if ($p_kerani) {
					$jumlah_karyawan_kerani = $p_kerani['jumlah_karyawan'];
					$persen_premi_kerani = $p_kerani['persen_premi'];
				}

				$premi_kerani = 0;

				if ($jum_karyawan <	$jumlah_karyawan_kerani) {
					$premi_kerani = ($persen_premi_kerani) *	($total_premi_dan_denda / $jumlah_karyawan_kerani);
				} else {
					$premi_kerani = $persen_premi_kerani  *	($total_premi_dan_denda / $jum_karyawan);
				}
			}


			$data_mandor_kerani = array('rp_hk_mandor' => $upah_mandor, 'rp_hk_kerani' => $upah_kerani, 'premi_mandor' => $premi_mandor, 'premi_kerani' => $premi_kerani);
			if (!empty($data_mandor_kerani)) {
				$this->set_response(array("status" => "OK", "data" => $data_mandor_kerani), REST_Controller::HTTP_CREATED);
			} else {
				$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_OK);
			}
		} catch (\Throwable $th) {
			//throw $th;
			$this->set_response(array("status" => "NOT OK", "data" =>  $th), REST_Controller::HTTP_OK);
		}
	}

	function laporan_panen_detail_post()
	{

		error_reporting(0);

		$estate_id     = $this->post('estate_id', true);
		$tgl_mulai =  $this->post('tgl_mulai', true);
		$tgl_akhir = $this->post('tgl_akhir', true);
		$format_laporan     = $this->post('format_laporan', true);
		$retrieveEstate = $this->db->query("select * from gbm_organisasi where id=" . $estate_id . "")->row_array();

		$retrievePanen = $this->db->query("select * from est_bkm_panen_vw 
		where id_estate=" . $estate_id . " and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
		 order by tanggal,nama_afdeling,nama_blok ")->result_array();

		// var_dump($results);
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
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
  <h3 class="title">LAPORAN RINCIAN PANEN</h3>
  <table class="no_border" style="width:30%">
			
			<tr>
					<td>Estate</td>
					<td>:</td>
					<td>' .  $retrieveEstate['nama'] . '</td>
			</tr>
			<tr>	
					<td>Periode</td>
					<td>:</td>
					<td>' . $tgl_mulai . ' s/d ' . $tgl_akhir . '</td>
			</tr>
			
	</table>
			<br>';

		$html = $html . ' <table border="1" width="100%" style="border-collapse: collapse;" >
			<thead>
				<tr>
				<th style="text-align: center;" width="4%">No.</th>			
				<th>Tanggal</th>
				<th>Afdeling</th>
				<th>Blok</th>
				<th>Tahun Tanam</th>
				<th>Inti/Plasma</th>
				<th style="text-align: center;">Janjang </th>
				<th style="text-align: center;">Hasil Kerja(Kg) </th>
				<th style="text-align: center;">Upah(Rp) </th>
				<th style="text-align: center;">Premi(Rp) </th>
				<th style="text-align: center;">Jumlah Hk</th>
				<th style="text-align: center;">Denda </th>
				<th style="text-align: center;">Total Biaya(Rp) </th>
				
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
						<td style="position:relative; text-align: center;">	' . ($no) . '</td>
						<td style="text-align: center;">' . tgl_indo_normal($m['tanggal']) . ' </td>
						<td>
						' . $m['nama_afdeling'] . ' 
						
						</td>
						<td style="text-align: center;">
						' . $m['nama_blok'] . ' 
						
						</td>
						<td style="text-align: center;">
							' . $m['tahuntanam'] . ' 
						</td>
						<td style="text-align: center;">
							' . $m['intiplasma'] . ' 
						</td>
						
						<td style="text-align: right;">' . $this->format_number_report($m['hasil_kerja_jjg']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['hasil_kerja_kg']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['rp_hk']) . ' 
					
						<td style="text-align: right;">' . $this->format_number_report($m['premi_panen']) . '
						<td style="text-align: right;">' . $this->format_number_report($m['jumlah_hk']) . '  
						<td style="text-align: right;">' . $this->format_number_report($m['denda_panen']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($jumlah_rp) . ' 
							
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
						' . $this->format_number_report($janjang) . ' 
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($hasil_kerja_kg) . ' 
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($upah) . ' 
						</td>
						
						<td style="text-align: right;">
						' . $this->format_number_report($premi_rp) . ' 
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($jumlah_hk) . ' 
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($denda) . ' 
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($total) . ' 
						</td>

						
						</tr>
								</tbody>
							</table>
						';
		if ($format_laporan == 'xls') {
			// $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			// $spreadsheet = $reader->loadFromString($html);
			// // $reader->setSheetIndex(1);
			// //$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
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
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}
	function laporan_panen_perkaryawan()
	{

		error_reporting(0);
		$estate_id     = $this->post('estate_id', true);
		$tgl_mulai =  $this->post('tgl_mulai', true);
		$tgl_akhir = $this->post('tgl_akhir', true);
		$format_laporan     = $this->post('format_laporan', true);
		$retrieveEstate = $this->db->query("select * from gbm_organisasi where id=" . $estate_id . "")->row_array();

		$retrievePanen = $this->db->query("select * from est_bkm_panen_vw 
		where id_estate=" . $estate_id . " and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
		 order by tanggal,nama_afdeling,nama_blok")->result_array();

		// var_dump($results);
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
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
  <h3 class="title">LAPORAN PANEN PERKARYAWAN</h3>
  <table class="no_border" style="width:30%">
			
			<tr>
					<td>Estate</td>
					<td>:</td>
					<td>' .  $retrieveEstate['nama'] . '</td>
			</tr>
			<tr>	
					<td>Periode</td>
					<td>:</td>
					<td>' . tgl_indo($tgl_mulai) . ' s/d ' . tgl_indo($tgl_akhir) . '</td>
			</tr>
			
	</table>
			<br>';
		$html = $html . ' <table  >
			<thead>
				<tr>
				<th style="text-align: center;" width="4%">No.</th>			
				<th>No Transaksi</th>
				<th>Tanggal</th>
				<th>Afdeling</th>
				<th>Blok</th>
				<th>Pemanen</th>
				<th>Mandor</th>
				<th style="text-align: center;">Janjang </th>
				<th style="text-align: center;">Hasil Kerja(Kg) </th>
				<th style="text-align: center;">Upah(Rp) </th>
				<th style="text-align: center;">Premi(Rp) </th>
				<th style="text-align: center;">Jumlah Hk</th>
				<th style="text-align: center;">Denda(Rp) </th>
				<th style="text-align: center;">Total Biaya(Rp) </th>
				
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
						<td style="position:relative; "text-align: center;"">	' . ($no) . '</td>
						<td>' . $m['no_transaksi'] . ' </td>
						<td style="text-align: center;">' . tgl_indo_normal($m['tanggal']) . ' </td>
						<td>
						' . $m['nama_afdeling'] . ' 
						
						</td>
						<td style="text-align: center;">
						' . $m['nama_blok'] . ' 
						
						</td>
						<td>
							' . $m['nama_karyawan'] . '(' . $m['nip_karyawan'] . ') 
						</td>
						<td>
						' . $m['nama_mandor'] . '(' . $m['nip_mandor'] . ') 
						</td>
						
						<td style="text-align: right;">' . $this->format_number_report($m['hasil_kerja_jjg']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['hasil_kerja_kg']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['rp_hk']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['premi_panen']) . '
						<td style="text-align: right;">' . $this->format_number_report($m['jumlah_hk']) . '  
						<td style="text-align: right;">' . $this->format_number_report($m['denda_panen']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($jumlah_rp) . ' 
							
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
						' . $this->format_number_report($janjang) . ' 
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($hasil_kerja_kg) . ' 
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($upah) . ' 
						</td>
						
						<td style="text-align: right;">
						' . $this->format_number_report($premi_rp) . ' 
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($jumlah_hk) . ' 
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($denda) . ' 
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($total) . ' 
						</td>

						
						</tr>
								</tbody>
							</table>
						';
		if ($format_laporan == 'xls') {
			// $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			// $spreadsheet = $reader->loadFromString($html);
			// // $reader->setSheetIndex(1);
			// //$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
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
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}
	function laporan_panen_perbulan_post()
	{
		error_reporting(0);

		$estate_id     = $this->post('estate_id', true);
		$periode =  $this->post('periode', true);

		$format_laporan     = $this->post('format_laporan', true);
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
  <h3 class="title">LAPORAN RINCIAN PANEN PERBULAN</h3>
  <table class="no_border" style="width:30%">
			
			<tr>
					<td>Estate</td>
					<td>:</td>
					<td>' .  $retrieveEstate['nama'] . '</td>
			</tr>
			<tr>	
					<td>Periode</td>
					<td>:</td>
					<td>' . $tgl_mulai . ' s/d ' . $tgl_akhir . '</td>
			</tr>
			
	</table>
			<br>';

		$html = $html . "

<table   border='1' width='100%' style='border-collapse: collapse;'>
<thead>
<tr>
	<th rowspan=3 >No</th>
	<th rowspan=3>Afdeling</th>
	<th rowspan=3>Blok</th>
	<th rowspan=3>Inti/Plasma</th>
	<th rowspan=3>Luas</th>
	<th colspan=" . ($jumhari * 4) . "  style='text-align: center'> " . $periode . "  </th>
	<th colspan=4 rowspan=2  style='text-align: center'>TOTAL</th>
</tr>
";

		$html = $html . "<tr>";
		for ($i = 1; $i < ($jumhari + 1); $i++) {
			$html = $html . "<th style='text-align: center' colspan=4>" . $i . "</td>";
		}

		$html = $html . "</tr> ";
		$html = $html . "<tr>";
		for ($i = 1; $i < ($jumhari + 1); $i++) {
			$html = $html . "<th style='text-align: center'>Jjg</th>";
			$html = $html . "<th style='text-align: center'>Kg</th>";
			$html = $html . "<th style='text-align: center'>Luas</th>";
			$html = $html . "<th style='text-align: center'>Hk</th>";
		}
		$html = $html . "<th style='text-align: center'>Jjg</th>";
		$html = $html . "<th style='text-align: center'>Kg</th>";
		$html = $html . "<th style='text-align: center'>Luas</th>";
		$html = $html . "<th style='text-align: center'>Hk</th>";
		$html = $html . "</tr> </thead>";
		$nourut = 0;
		$grandtotal_jjg = 0;
		$grandtotal_kg = 0;
		$grandtotal_luas = 0;
		$grandtotal_hk = 0;
		$totalJjgPerHari = array();
		$totalJjgPerHari = [];
		$totalKgPerHari = array();
		$totalKgPerHari = [];
		$totalLuasPerHari = array();
		$totalLuasPerHari = [];
		$totalHkPerHari = array();
		$totalHkPerHari = [];

		// retrive data rayon  
		$qry = "SELECT DISTINCT blok_id,kode_blok,nama_blok,nama_afdeling,intiplasma,luasareaproduktif FROM est_bkm_panen_vw WHERE id_estate=" . $estate_id . " and tanggal>='" . $tgl_mulai . "' 
		and tanggal<='" . $tgl_akhir . "' order by nama_afdeling,nama_blok ";
		$retrieveBlok = $this->db->query($qry)->result_array();

		for ($i = 1; $i < ($jumhari + 1); $i++) {
			$totalJjgPerHari[] = 0;
			$totalKgPerHari[] = 0;
			$totalLuasPerHari[] = 0;
			$totalHkPerHari[] = 0;
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
				 FROM est_bkm_panen_vw WHERE (blok_id =" . $d['blok_id'] . ")
				and id_estate=" . $estate_id . " and tanggal='" . $tgl . "'")->row_array();
				$hasil_kerja_jjg = $retrievePanen['hasil_kerja_jjg'] ? $retrievePanen['hasil_kerja_jjg'] : 0;
				$hasil_kerja_kg = $retrievePanen['hasil_kerja_kg'] ? $retrievePanen['hasil_kerja_kg'] : 0;
				$hasil_kerja_luas = $retrievePanen['hasil_kerja_luas'] ? $retrievePanen['hasil_kerja_luas'] : 0;
				$jumlah_hk = $retrievePanen['jumlah_hk'] ? $retrievePanen['jumlah_hk'] : 0;
				$index = "idx" . $i;
				// $jum=0;
				if (array_key_exists($i, $totalJjgPerHari)) {
					$totalJjgPerHari[$i - 1] = $totalJjgPerHari[$i - 1] + $hasil_kerja_jjg;
				} else {
					$totalJjgPerHari[] = $hasil_kerja_jjg;
				}
				if (array_key_exists($i, $totalKgPerHari)) {
					$totalKgPerHari[$i - 1] = $totalKgPerHari[$i - 1] + $hasil_kerja_kg;
				} else {
					$totalKgPerHari[] = $hasil_kerja_kg;
				}
				if (array_key_exists($i, $totalLuasPerHari)) {
					$totalLuasPerHari[$i - 1] = $totalLuasPerHari[$i - 1] + $hasil_kerja_luas;
				} else {
					$totalLuasPerHari[] = $hasil_kerja_luas;
				}
				if (array_key_exists($i, $totalHkPerHari)) {
					$totalHkPerHari[$i - 1] = $totalHkPerHari[$i - 1] + $jumlah_hk;
				} else {
					$totalHkPerHari[] = $jumlah_hk;
				}

				$total_hasil_kerja_jjg = $total_hasil_kerja_jjg + $hasil_kerja_jjg;
				$total_hasil_kerja_kg = $total_hasil_kerja_kg + $hasil_kerja_kg;
				$total_hasil_kerja_luas = $total_hasil_kerja_luas + $hasil_kerja_luas;
				$total_jumlah_hk = $total_jumlah_hk + $jumlah_hk;
				$grandtotal_jjg = $grandtotal_jjg + $hasil_kerja_jjg;
				$grandtotal_kg = $grandtotal_kg + $hasil_kerja_kg;
				$grandtotal_luas = $grandtotal_luas + $hasil_kerja_luas;
				$grandtotal_hk = $grandtotal_hk + $jumlah_hk;
				$html = $html . "<td style='text-align: center'>" . number_format($hasil_kerja_jjg) . " </td>";
				$html = $html . "<td style='text-align: center'>" . number_format($hasil_kerja_kg, 2) . " </td>";
				$html = $html . "<td style='text-align: center'>" . number_format($hasil_kerja_luas, 2) . " </td>";
				$html = $html . "<td style='text-align: center'>" . number_format($jumlah_hk, 2) . " </td>";
			}
			$html = $html . "<td style='text-align: center'>" . number_format($total_hasil_kerja_jjg) . " </td>";
			$html = $html . "<td style='text-align: center'>" . number_format($total_hasil_kerja_kg, 2) . " </td>";
			$html = $html . "<td style='text-align: center'>" . number_format($total_hasil_kerja_luas, 2) . " </td>";
			$html = $html . "<td style='text-align: center'>" . number_format($total_jumlah_hk, 2) . " </td>";
			$html = $html . "</tr>";
		}




		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: center'> </td>";
		$html = $html . "<td style='text-align: center'></td>";
		$html = $html . "<td style='text-align: center'></td>";
		$html = $html . "<td style='text-align: center'></td>";
		$html = $html . "<td style='text-align: center'></td>";

		for ($i = 1; $i < ($jumhari + 1); $i++) {

			$html = $html . "<td style='text-align: center'>" . number_format($totalJjgPerHari[$i - 1]) . " </td>";
			$html = $html . "<td style='text-align: center'>" . number_format($totalKgPerHari[$i - 1], 2) . " </td>";
			$html = $html . "<td style='text-align: center'>" . number_format($totalLuasPerHari[$i - 1], 2) . " </td>";
			$html = $html . "<td style='text-align: center'>" . number_format($totalHkPerHari[$i - 1], 2) . " </td>";
		}
		$html = $html . "<td style='text-align: right'>" . number_format($grandtotal_jjg) . " </td>";
		$html = $html . "<td style='text-align: right'>" . number_format($grandtotal_kg, 2) . " </td>";
		$html = $html . "<td style='text-align: right'>" . number_format($grandtotal_luas, 2) . " </td>";
		$html = $html . "<td style='text-align: right'>" . number_format($grandtotal_hk, 2) . " </td>";
		$html = $html . "</tr>";
		$html = $html . "</table>";


		if ($format_laporan == 'xls') {
			// $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			// $spreadsheet = $reader->loadFromString($html);
			// // $reader->setSheetIndex(1);
			// //$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
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
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}
	function laporan_panen_sensus_post()
	{
		error_reporting(0);

		$estate_id     = $this->post('estate_id', true);
		$bulan_mulai =  $this->post('bulan_awal', true);
		$bulan_akhir =  $this->post('bulan_akhir', true);
		$tahun =  $this->post('tahun', true);

		$format_laporan     = $this->post('format_laporan', true);
		$b1=(int)$bulan_mulai;
		$b2=(int)$bulan_akhir;
		$jum_bulan=$b2-$b1;

		// echo $b1;echo $b2;exit();
		// $date = new DateTime($periode . '-01');
		// $date->modify('last day of this month');
		// $last_day_this_month = $date->format('Y-m-d');
		// (int)$jumhari = date('d', strtotime($last_day_this_month));
		// $tgl_mulai = $periode . '-01';
		// $tgl_akhir = $periode . '-' . sprintf("%02d", $jumhari);

		$retrieveEstate = $this->db->query("select * from gbm_organisasi where id=" . $estate_id . "")->row_array();

		// var_dump($results);
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
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
  <h3 class="title">LAPORAN PANEN VS SENSUS</h3>
  <table class="no_border" style="width:30%">
			
			<tr>
					<td>Estate</td>
					<td>:</td>
					<td>' .  $retrieveEstate['nama'] . '</td>
			</tr>
			<tr>	
					<td>Periode</td>
					<td>:</td>
					<td>' . $bulan_mulai . ' s/d ' . $bulan_akhir . '  '. $tahun . '</td>
			</tr>
			
	</table>
			<br>';

		$html = $html . "

<table   border='1' width='100%' style='border-collapse: collapse;'>
<thead>
<tr>
	<th rowspan=4 >No</th>
	<th rowspan=4>Afdeling</th>
	<th rowspan=4>Blok</th>
	<th rowspan=4>Inti/Plasma</th>
	<th rowspan=4>Luas</th>
	<th colspan=" . (($jum_bulan +2) * 4) . "  style='text-align: center'> " . $tahun . "  </th>
	
</tr>";


		$html = $html . "<tr>";
		for ($i = $b1; $i < ($b2 + 1); $i++) {
			$html = $html . "<th style='text-align: center' colspan=4>" . $i . "</td>";
		}
		$html = $html . "<th colspan=4  style='text-align: center'>TOTAL</th>";	
		$html = $html . "</tr> ";

		$html = $html . "<tr>";
		for ($i = $b1; $i < ($b2 + 2); $i++) {
			$html = $html . "<th style='text-align: center' colspan=2>Panen</td>";
			$html = $html . "<th style='text-align: center' colspan=2>Sensus</td>";
		}
		$html = $html . "</tr> ";

		$html = $html . "<tr>";
		for ($i = $b1; $i < ($b2 + 2); $i++) {
			$html = $html . "<th style='text-align: center'>Jjg</th>";
			$html = $html . "<th style='text-align: center'>Kg</th>";
			$html = $html . "<th style='text-align: center'>Jjg</th>";
			$html = $html . "<th style='text-align: center'>Kg</th>";
		}
		$html = $html . "</tr> 
		</thead>";

		// $html = $html . "</table> ";
		// echo $html;exit();

		$qry = "SELECT * from gbm_blok_organisasi_vw where id_estate=" . $estate_id . " order by nama_blok ";
		$retrieveBlok = $this->db->query($qry)->result_array();
		
		$nourut = 0;
		$grandtotal_jjg = 0;
		$grandtotal_kg = 0;
		$$grandtotal_jjg_sensus = 0;
		$$grandtotal_kg_sensus = 0;
		$totalJjgPerBulan = array();
		$totalJjgPerBulan = [];
		$totalKgPerBulan = array();
		$totalKgPerBulan = [];
		$totalJjgSensusPerBulan = array();
		$totalJjgSensusPerBulan = [];
		$totalKgSensusPerBulan = array();
		$totalKgSensusPerBulan = [];


		
		for ($i = 1; $i < ($jum_bulan + 1); $i++) {
			$totalJjgPerBulan[] = 0;
			$totalKgPerBulan[] = 0;
			$totalJjgSensusPerBulan[] = 0;
			$totalKgSensusPerBulan[] = 0;
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
			$total_jjg = 0;
			$total_kg = 0;
			$total_jjg_sensus= 0;
			$total_kg_sensus = 0;
			
			for ($i = $b1; $i < ($b2 + 1); $i++) {
				$bln=sprintf("%02d", $i);
				$periode=$tahun ."-". sprintf("%02d", $i);
				// 
				$queryPanen="	SELECT SUM(jum_jjg_kirim)AS jjg,sum(jum_kg_pks)AS kg,blok_id 
				FROM est_produksi_panen_ht a 
				INNER JOIN est_produksi_panen_dt b ON a.id=b.produksi_panen_id 
				WHERE 
				DATE_FORMAT(a.tanggal, '%Y-%m')='".$periode."'
				AND blok_id =" . $d['organisasi_id'] . ";";
				
				$retrievePanen = $this->db->query($queryPanen)->row_array();
				
			
				$jjg = $retrievePanen['jjg'] ? $retrievePanen['jjg'] : 0;
				$kg = $retrievePanen['kg'] ? $retrievePanen['kg'] : 0;

				$retrieveSensusPanen = $this->db->query("		SELECT SUM(jjg)AS jjg,SUM(kg)AS kg,blok_id FROM est_sensus_panen_ht a 
				INNER JOIN est_sensus_panen_dt b ON a.id=b.sensus_panen_id
				WHERE  tahun='". $tahun."' AND bulan='".$bln."'
				AND blok_id =" . $d['organisasi_id'] . ";")->row_array();
				$jjg_sensus = $retrieveSensusPanen['jjg'] ? $retrieveSensusPanen['jjg'] : 0;
				$kg_sensus = $retrieveSensusPanen['kg'] ? $retrieveSensusPanen['kg'] : 0;
				
				$index = "idx" . $i;
				// $jum=0;
				if (array_key_exists($i, $totalJjgPerBulan)) {
					$totalJjgPerBulan[$i - 1] = $totalJjgPerBulan[$i - 1] + $jjg;
				} else {
					$totalJjgPerBulan[$i - 1] = $jjg;
				}
				if (array_key_exists($i, $totalKgPerBulan)) {
					$totalKgPerBulan[$i - 1] = $totalKgPerBulan[$i - 1] + $kg;
				} else {
					$totalKgPerBulan[$i - 1] = $kg;
				}
				if (array_key_exists($i, $totalJjgSensusPerBulan)) {
					$totalJjgSensusPerBulan[$i - 1] = $totalJjgSensusPerBulan[$i - 1] + $jjg_sensus;
				} else {
					$totalJjgSensusPerBulan[$i - 1] = $jjg_sensus;
				}
				if (array_key_exists($i, $totalKgSensusPerBulan)) {
					$totalKgSensusPerBulan[$i - 1] = $totalKgSensusPerBulan[$i - 1] + $kg_sensus;
				} else {
					$totalKgSensusPerBulan[$i - 1] = $kg_sensus;
				}

				$total_jjg = $total_jjg + $jjg;
				$total_kg = $total_kg + $kg;
				$total_kg_sensus = $total_kg_sensus + $kg_sensus;
				$total_jjg_sensus = $total_jjg_sensus + $jjg_sensus;
				$grandtotal_jjg = $grandtotal_jjg + $jjg;
				$grandtotal_kg = $grandtotal_kg + $kg;
				$grandtotal_jjg_sensus = $grandtotal_jjg_sensus + $jjg_sensus;
				$grandtotal_kg_sensus = $grandtotal_kg_sensus + $kg_sensus;
				$html = $html . "<td style='text-align: center'>" . number_format($jjg) . " </td>";
				$html = $html . "<td style='text-align: center'>" . number_format($kg, 2) . " </td>";
				$html = $html . "<td style='text-align: center'>" . number_format($jjg_sensus, 2) . " </td>";
				$html = $html . "<td style='text-align: center'>" . number_format($kg_sensus, 2) . " </td>";
			}
			$html = $html . "<td style='text-align: center'>" . number_format($total_jjg) . " </td>";
			$html = $html . "<td style='text-align: center'>" . number_format($total_kg, 2) . " </td>";
			$html = $html . "<td style='text-align: center'>" . number_format($total_jjg_sensus, 2) . " </td>";
			$html = $html . "<td style='text-align: center'>" . number_format($total_kg_sensus, 2) . " </td>";
			$html = $html . "</tr>";
		}




		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: center'> </td>";
		$html = $html . "<td style='text-align: center'></td>";
		$html = $html . "<td style='text-align: center'></td>";
		$html = $html . "<td style='text-align: center'></td>";
		$html = $html . "<td style='text-align: center'></td>";

		for ($i = $b1; $i < ($b2 + 1); $i++) {

			$html = $html . "<td style='text-align: center'>" . number_format($totalJjgPerBulan[$i - 1]) . " </td>";
			$html = $html . "<td style='text-align: center'>" . number_format($totalKgPerBulan[$i - 1], 2) . " </td>";
			$html = $html . "<td style='text-align: center'>" . number_format($totalJjgSensusPerBulan[$i - 1], 2) . " </td>";
			$html = $html . "<td style='text-align: center'>" . number_format($totalKgSensusPerBulan[$i - 1], 2) . " </td>";
		}
		$html = $html . "<td style='text-align: right'>" . number_format($grandtotal_jjg) . " </td>";
		$html = $html . "<td style='text-align: right'>" . number_format($grandtotal_kg, 2) . " </td>";
		$html = $html . "<td style='text-align: right'>" . number_format($grandtotal_jjg_sensus, 2) . " </td>";
		$html = $html . "<td style='text-align: right'>" . number_format($grandtotal_kg_sensus, 2) . " </td>";
		$html = $html . "</tr>";
		$html = $html . "</table>";


		if ($format_laporan == 'xls') {
			// $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			// $spreadsheet = $reader->loadFromString($html);
			// // $reader->setSheetIndex(1);
			// //$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
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
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}

	function laporan_denda_panen_perbulan_post()
	{
		error_reporting(0);

		$estate_id     = $this->post('estate_id', true);
		$periode =  $this->post('periode', true);

		$format_laporan     = $this->post('format_laporan', true);
		$date = new DateTime($periode . '-01');
		$date->modify('last day of this month');
		$last_day_this_month = $date->format('Y-m-d');
		(int)$jumhari = date('d', strtotime($last_day_this_month));
		$tgl_mulai = $periode . '-01';
		$tgl_akhir = $periode . '-' . sprintf("%02d", $jumhari);

		$retrieveEstate = $this->db->query("select * from gbm_organisasi where id=" . $estate_id . "")->row_array();
		$sql_denda_panen = "SELECT a.tanggal,b.karyawan_id,d.nip,d.nama,e.nama AS jabatan,
		f.nama AS afdeling,g.id as id_kode_denda, g.kode,SUM(c.qty)AS qty,
		SUM(jumlah_nilai_denda)AS nilai FROM est_bkm_panen_ht a 
		INNER JOIN est_bkm_panen_dt b ON a.id=b.bkm_panen_id
		INNER JOIN est_bkm_panen_denda c ON b.id=c.bkm_panen_dt_id
		INNER JOIN karyawan d ON b.karyawan_id=d.id
		LEFT JOIN payroll_jabatan e ON d.jabatan_id=e.id
		LEFT JOIN gbm_organisasi f ON d.sub_bagian_id=f.id
		INNER JOIN est_kode_denda_panen g ON c.kode_denda_panen_id=g.id
		WHERE a.lokasi_id=" . $estate_id . " and a.tanggal>='" . $tgl_mulai . "' 
		and a.tanggal<='" . $tgl_akhir . "'
		GROUP BY  a.tanggal,b.karyawan_id,d.nip,d.nama,e.nama ,f.nama,g.id,g.kode 
		ORDER BY g.kode ";

		$arr_kode_denda = array();
		$arr_denda_panen = array();
		$arr_karyawan = array();
		$arr_total_by_karyawan = array();
		$arr_total_by_jumlah_karyawan = array();
		$res_denda_panen = $this->db->query($sql_denda_panen)->result_array();
		// var_dump ($res_denda_panen);exit();
		foreach ($res_denda_panen as $key => $value) {
			$arr_kode_denda[$value['id_kode_denda']] = $value['kode'];
			$karyawan = array("id" => $value['karyawan_id'], "nip" => $value['nip'], "nama" => $value['nama'], "jabatan" => $value['jabatan'], "afdeling" => $value['afdeling']);
			$arr_karyawan[$value['karyawan_id']] = $karyawan;
			$arr_denda_panen[$value['karyawan_id']][$value['id_kode_denda']][$value['tanggal']] = $value['nilai'];
			if ($arr_total_by_karyawan[$value['karyawan_id']][$value['id_kode_denda']]) {
				$tot = $arr_total_by_karyawan[$value['karyawan_id']][$value['id_kode_denda']];
				$arr_total_by_karyawan[$value['karyawan_id']][$value['id_kode_denda']] = $tot + $value['nilai'];
			} else {
				$arr_total_by_karyawan[$value['karyawan_id']][$value['id_kode_denda']] = $value['nilai'];
			}
			if ($arr_total_by_jumlah_karyawan[$value['karyawan_id']]) {
				$tot_jum = $arr_total_by_jumlah_karyawan[$value['karyawan_id']];
				$arr_total_by_jumlah_karyawan[$value['karyawan_id']] = $tot_jum + $value['nilai'];
			} else {
				$arr_total_by_jumlah_karyawan[$value['karyawan_id']] = $value['nilai'];
			}
		}


		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report();
		} else {
			$html = get_header_report_v2();
		}

		// $res_kode_denda=$this->db->query("select * from est_kode_denda_panen oerder by kode")->result_array();
		$jum_col = count($arr_kode_denda);
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
  <h3 class="title">LAPORAN DENDA PERBULAN</h3>
  <table class="no_border" style="width:30%">
			
			<tr>
					<td>Estate</td>
					<td>:</td>
					<td>' .  $retrieveEstate['nama'] . '</td>
			</tr>
			<tr>	
					<td>Periode</td>
					<td>:</td>
					<td>' . $tgl_mulai . ' s/d ' . $tgl_akhir . '</td>
			</tr>
			
	</table>
			<br>';

		$html = $html . "

<table   border='1' width='100%' style='border-collapse: collapse;'>
<thead>
<tr>
	<th rowspan=3 >No</th>
	<th rowspan=3>NIK</th>
	<th rowspan=3>Nama</th>
	<th rowspan=3>Jabatan</th>
	<th rowspan=3>Afdeling</th>
	<th colspan=" . ($jumhari * count($arr_kode_denda)) . "  style='text-align: center'> " . $periode . "  </th>
	<th rowspan=2 colspan=" . (count($arr_kode_denda) + 1) . "   style='text-align: center'>TOTAL</th>
	
</tr>
";

		$html = $html . "<tr>";
		for ($i = 1; $i < ($jumhari + 1); $i++) {
			$html = $html . "<th  style='text-align: center' colspan=" . (count($arr_kode_denda)) . ">" . $i . "</th> ";
		}

		$html = $html . "</tr> ";
		$html = $html . "<tr>";
		for ($i = 1; $i < ($jumhari + 1); $i++) {
			foreach ($arr_kode_denda as $key => $value) {
				$html = $html . "<th style='text-align: center'>" . $value . "</th>";
			}
		}
		foreach ($arr_kode_denda as $key => $value) {
			$html = $html . "<th  style='text-align: center'>" . $value . "</th>";
		}
		$html = $html . "<th colspan=2 style='text-align: center'>Jumlah</th> 
						";
		$html = $html . "</tr> 
		</thead>";
		// $html = $html . " </table>";

		// echo $html;exit();

		$nourut = 0;



		foreach ($arr_karyawan as $key => $k) {
			$html = $html . "<tr>";
			$nourut = $nourut + 1;
			$html = $html . "<td style='text-align: center'>" . $nourut . "</td>";
			$html = $html . "<td style='text-align: center'>" . $k['nip'] . "</td>";
			$html = $html . "<td style='text-align: center'>" . $k['nama'] . "</td>";
			$html = $html . "<td style='text-align: center'>" . $k['jabatan'] . "</td>";
			$html = $html . "<td style='text-align: center'>" . $k['afdeling'] . "</td>";

			for ($i = 1; $i < ($jumhari + 1); $i++) {
				$tgl = $periode  . '-' . sprintf("%02d", $i);

				foreach ($arr_kode_denda as $key_kode => $d) {
					$nilai = 0;
					if ($arr_denda_panen[$k['id']][$key_kode][$tgl]) {
						$nilai = $arr_denda_panen[$k['id']][$key_kode][$tgl];
					}

					$html = $html . "<td style='text-align: center'>" . number_format($nilai) . " </td>";
				}
			}
			foreach ($arr_kode_denda as $key_kode => $d) {
				$jum = 0;
				if ($arr_total_by_karyawan[$k['id']][$key_kode]) {
					$jum = $arr_total_by_karyawan[$k['id']][$key_kode];
				}
				$html = $html . "<td style='text-align: center'>" . number_format($jum) . " </td>";
			}
			$tot_jumlah = 0;
			if ($arr_total_by_karyawan[$k['id']]) {
				$tot_jumlah = $arr_total_by_jumlah_karyawan[$k['id']];
			}
			$html = $html . "<td style='text-align: center'>" . number_format($tot_jumlah) . " </td>";
			$html = $html . "</tr>";
		}

		$html = $html . "</table>";


		if ($format_laporan == 'xls') {

			echo $html;
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
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
	function laporan_panenkaryawan_post()
	{
		$tipe_laporan =  $this->post('tipe_laporan', true);
		if ($tipe_laporan == 'v1') {
			$this->laporan_panen_karyawan_v1();
		} else if ($tipe_laporan == 'v2') {
			$this->laporan_panen_perkaryawan();
		} else {
			$this->laporan_panen_karyawan_v1();
		}
	}


	function laporan_panen_karyawan_v1()
	{

		$format_laporan =  $this->post('format_laporan', true);

		// $id = (int)$segment_3;
		$data = [];

		$input = [
			'estate_id' => 265,
			'tgl_mulai' => '2022-07-05',
			'tgl_akhir' => '2022-07-05',
		];

		$estate_id = $this->post('estate_id', true);
		$tanggal_awal = $this->post('tgl_mulai', true);
		$tanggal_akhir = $this->post('tgl_akhir', true);

		// $estate_id = $input['estate_id'];
		// $tanggal_awal = $input['tgl_mulai'];
		// $tanggal_akhir = $input['tgl_akhir'];
		if (is_null($format_laporan)) {
			$format_laporan = 'view';
		}
		$queryBkm = "SELECT * from est_bkm_panen_vw 
		WHERE tanggal between  '" . $tanggal_awal . "' and  '" . $tanggal_akhir . "'	
		";

		$filter_lokasi = "Semua";
		if ($estate_id) {
			$queryBkm = $queryBkm . " and id_estate=" . $estate_id . "";
			$res = $this->db->query("select * from gbm_organisasi where id=" . $estate_id . "")->row_array();
			$filter_lokasi = $res['nama'];
		}
		$dataBkm = $this->db->query($queryBkm)->result_array();
		// var_dump($dataBkm);exit();
		$data['bkm'] = 	$dataBkm;
		$data['filter_lokasi'] = 	$filter_lokasi;
		$data['filter_tgl_awal'] = 	$tanggal_awal;
		$data['filter_tgl_akhir'] = $tanggal_akhir;
		$data['format_laporan'] =$format_laporan ;

		$html = $this->load->view('Est_Laporan_Panen_Karyawan', $data, true);

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		// echo $html;

		if ($format_laporan == 'xls') {
			echo $html;
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
		c.nama as rayon,
		d.nama as mandor,
		e.nama as kerani,
		f.nama as asisten,
		premi_mandor,denda_mandor,premi_kerani,denda_kerani
		 FROM est_bkm_panen_ht a
		INNER JOIN gbm_organisasi b ON a.lokasi_id=b.id
		INNER JOIN gbm_organisasi c ON a.rayon_afdeling_id=c.id
		LEFT JOIN karyawan d ON a.mandor_id=d.id
		LEFT JOIN karyawan e ON a.kerani_id=e.id 
		LEFT JOIN karyawan f ON a.asisten_id=f.id WHERE a.id=" . $id . "";
		$dataHeader = $this->db->query($queryHeader)->row_array();

		$queryDetail = "SELECT a.*,
		b.nama as karyawan,
        c.nama as blok
		FROM est_bkm_panen_dt a 
		INNER JOIN karyawan b ON a.karyawan_id=b.id
        INNER JOIN gbm_organisasi c ON a.blok_id=c.id WHERE a.bkm_panen_id=" . $id . "";
		$dataDetail = $this->db->query($queryDetail)->result_array();

		$data['header'] = 	$dataHeader;
		$data['detail'] = 	$dataDetail;

		$html = $this->load->view('EstSlipBkmPanen', $data, true);

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
	function rekap_mandor_kerani_post()
	{
		$format_laporan =  $this->post('format_laporan', true);
		$lokasi_id = $this->post('estate_id', true);
		$afdeling_id = $this->post('afdeling_id', true);
		$tanggal_awal = $this->post('tgl_mulai', true);
		$tanggal_akhir = $this->post('tgl_akhir', true);

		$dataHasil = [];
		// $input = [
		// 	'afdeling_id' => 271,
		// 	'tgl_mulai' => '2022-01-01',
		// 	'tgl_akhir' => '2023-05-31',
		// 	// 'format_laporan' => 'view',
		// ];
		// $afdeling_id =null;// $input['afdeling_id'];
		// $tanggal_awal = $input['tgl_mulai'];
		// $tanggal_akhir = $input['tgl_akhir'];
		// $format_laporan = $input['format_laporan'];
		$res = $this->db->query("select * from gbm_organisasi where id=" . $lokasi_id . "")->row_array();
			$filter_estate = $res['nama'];
			$filter_afdeling = 'Semua';
		
		/* UTK MENCARI SEMUA TANGGAL YG ADA DI TRASAKSI PADA PERIODE BERSANGKUTAN */
			if ($afdeling_id) {
			$qTgl = "SELECT DISTINCT tanggal FROM (
				SELECT DISTINCT tanggal FROM est_bkm_panen_ht  WHERE tanggal between '" . $tanggal_awal . "' and  '" . $tanggal_akhir . "'  and rayon_afdeling_id=" . $afdeling_id . "
				UNION 
				SELECT DISTINCT tanggal FROM est_bkm_pemeliharaan_ht WHERE tanggal between '" . $tanggal_awal . "' and  '" . $tanggal_akhir . "' and rayon_afdeling_id=" . $afdeling_id . ")T
				order by tanggal;";
			
			$res = $this->db->query("select * from gbm_organisasi where id=" . $afdeling_id . "")->row_array();
			$filter_afdeling = $res['nama'];
		}else{
			$qTgl = "SELECT DISTINCT tanggal FROM (
				SELECT DISTINCT tanggal FROM est_bkm_panen_ht WHERE tanggal between '" . $tanggal_awal . "' and  '" . $tanggal_akhir . "' and lokasi_id=" . $lokasi_id . "
				UNION 
				SELECT DISTINCT tanggal FROM est_bkm_pemeliharaan_ht WHERE tanggal between '" . $tanggal_awal . "' and  '" . $tanggal_akhir . "' and lokasi_id=" . $lokasi_id . ")T
				order by tanggal;";
			

		}	
		$resTgl = $this->db->query($qTgl)->result_array();
		/* END UTK MENCARI SEMUA TANGGAL YG ADA DI TRASAKSI PADA PERIODE BERSANGKUTAN */

				// var_dump($resTgl);return;
		
		/* LOOPING TANGGAL */			
		foreach ($resTgl as $key => $tgl) {
			$hk_k_panen = 0;
			$rp_k_panen = 0;
			$hk_m_panen = 0;
			$rp_m_panen = 0;
			$premi_m_panen = 0;
			$premi_k_panen = 0;

			$hk_k_pml = 0;
			$rp_k_pml = 0;
			$hk_m_pml = 0;
			$rp_m_pml = 0;
			$premi_m_pml = 0;
			$premi_k_pml = 0;

			// PANEN //
			$queryPanen = "SELECT 
		SUM(a.jumlah_hk_kerani) AS hk_k,
		SUM(a.rp_hk_kerani) AS rp_k,
		SUM(a.jumlah_hk_mandor) AS hk_m,
		SUM(a.rp_hk_mandor) AS rp_m,
		SUM(a.premi_mandor) AS premi_m,
		SUM(a.premi_kerani) AS premi_k
		FROM est_bkm_panen_ht a 
		where a.tanggal = '" . $tgl['tanggal'] . "' 
		";
			if ($afdeling_id) {
				$queryPanen = $queryPanen . " and a.rayon_afdeling_id=" . $afdeling_id . "";
			}else{
				$queryPanen = $queryPanen . " and lokasi_id=" . $lokasi_id . "";
			}

			$resPanen = $this->db->query($queryPanen)->row_array();
			if ($resPanen) {
				$hk_k_panen = $resPanen['hk_k'];
				$rp_k_panen = $resPanen['rp_k'];
				$hk_m_panen = $resPanen['hk_m'];
				$rp_m_panen = $resPanen['rp_m'];
				$premi_m_panen = $resPanen['premi_m'];
				$premi_k_panen = $resPanen['premi_k'];
			}

		// PML //
		$queryPml = "SELECT 
		SUM(a.jumlah_hk_kerani) AS hk_k,
		SUM(a.rp_hk_kerani) AS rp_k,
		SUM(a.jumlah_hk_mandor) AS hk_m,
		SUM(a.rp_hk_mandor) AS rp_m,
		SUM(a.premi_mandor) AS premi_m,
		SUM(a.premi_kerani) AS premi_k
		FROM est_bkm_pemeliharaan_ht a 
		where a.tanggal = '" . $tgl['tanggal']  . "'
		";
			if ($afdeling_id) {
				$queryPml = $queryPml . " and a.rayon_afdeling_id=" . $afdeling_id . "";
			}else{
				$queryPml = $queryPml . " and lokasi_id=" . $lokasi_id . "";
			}

			$resPml = $this->db->query($queryPml)->row_array();
			if ($resPanen) {
				$hk_k_pml = $resPml['hk_k'];
				$rp_k_pml = $resPml['rp_k'];
				$hk_m_pml = $resPml['hk_m'];
				$rp_m_pml = $resPml['rp_m'];
				$premi_m_pml = $resPml['premi_m'];
				$premi_k_pml = $resPml['premi_k'];
			}
			$hasil = array(
				'tanggal' => $tgl['tanggal'],
				'hk_k_panen' => $hk_k_panen, 'rp_k_panen' => $rp_k_panen, 'hk_m_panen' => $hk_m_panen, 'rp_m_panen' => $rp_m_panen, 'premi_m_panen' => $premi_m_panen, 'premi_k_panen' => $premi_k_panen,
				'hk_k_pml' => $hk_k_pml, 'rp_k_pml' => $rp_k_pml, 'hk_m_pml' => $hk_m_pml, 'rp_m_pml' => $rp_m_pml, 'premi_m_pml' => $premi_m_pml, 'premi_k_pml' => $premi_k_pml
			);

			// MASUKAN KE ARRAY//
			$dataHasil[] = $hasil;
		}

	
		$data['bkm'] = 	$dataHasil;
		$data['filter_estate'] = 	$filter_estate;
		$data['filter_afdeling'] = 	$filter_afdeling;
		$data['filter_tgl_awal'] = 	$tanggal_awal;
		$data['filter_tgl_akhir'] = $tanggal_akhir;

		$html = $this->load->view('Est_Bkm_rekap_mandor_kerani', $data, true);

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		echo $html;
	}
}
