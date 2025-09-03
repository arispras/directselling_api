<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

class EstBkmPemeliharaan extends BD_Controller
{
	public $user_id;
	public $theCredential;

	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('GbmOrganisasiModel');
		$this->load->model('EstBkmPemeliharaanModel');
		$this->load->model('InvItemModel');
		$this->load->model('AccJurnalModel');
		$this->load->model('AccJurnalUpahModel');
		$this->load->model('M_DatatablesModel');
		$this->load->model('AccKegiatanModel');
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
		d.nama as mandor,
		e.nama as kerani,
		f.nama as asisten,
		g.user_full_name AS dibuat,
		h.user_full_name AS diubah,
		i.user_full_name AS diposting 
		FROM est_bkm_pemeliharaan_ht a 
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

	function listBkmPemeliharaan_post()
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
					c.nama as rayon_afdeling,
					d.nama as mandor,
					e.nama as kerani, 
					f.nama as asisten FROM est_bkm_pemeliharaan_ht a 
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

		$dataBkm = $this->db->query($queryBkm)->result_array();
		// var_dump($dataBkm);exit();
		$data['bkm'] = 	$dataBkm;
		$data['filter_lokasi'] = 	$filter_lokasi;
		$data['filter_tgl_awal'] = 	$tanggal_awal;
		$data['filter_tgl_akhir'] = $tanggal_akhir;

		$html = $this->load->view('Est_bkm_pemeliharaan_list', $data, true);

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		echo $html;
	}

	function listBkmPemeliharaanDetail_post()
	{
		$format_laporan =  $this->post('format_laporan', true);

		// $id = (int)$segment_3;
		$data = [];

		$input = [
			'lokasi_id' => 252,
			'tgl_mulai' => '2022-07-01',
			'tgl_akhir' => '2022-09-30',
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
		FROM est_bkm_pemeliharaan_ht a
		INNER JOIN gbm_organisasi b ON a.lokasi_id=b.id
		INNER JOIN gbm_organisasi c ON a.rayon_afdeling_id=c.id
        LEFT JOIN karyawan d ON a.mandor_id=d.id
        LEFT JOIN karyawan e ON a.kerani_id=e.id
        LEFT JOIN karyawan f ON a.asisten_id=f.id
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
			c.nama as kegiatan,
			d.nama as blok
			FROM est_bkm_pemeliharaan_dt a 
			LEFT JOIN karyawan b ON a.karyawan_id=b.id
			LEFT JOIN acc_kegiatan c ON a.acc_kegiatan_id=c.id
			LEFT JOIN gbm_organisasi d ON a.blok_id=d.id
			WHERE a.bkm_pemeliharaan_id=" . $hd['id'] . "";
			$dataDtl = $this->db->query($querydetail)->result_array();
			// var_dump($dataDtl)	
			$querydetailItem = "SELECT a.*,
			b.nama as item,
			c.nama as gudang,
			d.nama as blok,
			f.nama as uom,
			e.nama as kegiatan
			FROM est_bkm_pemeliharaan_item a 
			LEFT JOIN inv_item b ON a.item_id=b.id
			LEFT JOIN gbm_organisasi c ON a.gudang_id=c.id
			LEFT JOIN gbm_organisasi d ON a.blok_id=d.id 
			LEFT JOIN acc_kegiatan e ON a.kegiatan_id=e.id
			LEFT JOIN gbm_uom f ON b.uom_id=f.id
			WHERE a.bkm_pemeliharaan_id=" . $hd['id'] . "";
			$dataDtlItem = $this->db->query($querydetailItem)->result_array();
			// var_dump($dataDtl)	

			$hd['dtl'] = $dataDtlItem;
			$hd['detail'] = $dataDtl;
			$result[] = $hd;
		}


		$data['pml'] = 	$result;
		// var_dump($result)	;exit();
		$data['filter_lokasi'] = 	$filter_lokasi;
		$data['filter_tgl_awal'] = 	$tanggal_awal;
		$data['filter_tgl_akhir'] = $tanggal_akhir;

		$html = $this->load->view('Est_bkm_pemeliharaan_list_detail', $data, true);

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		echo $html;
	}

	function index_get($id = '')
	{
		$retrieve = $this->EstBkmPemeliharaanModel->retrieve_by_id($id);
		$retrieve['detail'] = $this->EstBkmPemeliharaanModel->retrieve_detail($id);
		$retrieve['detail_item'] = $this->EstBkmPemeliharaanModel->retrieve_detail_item($id);


		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function getDetail_get($id = '')
	{
		$retrieve = $this->EstBkmPemeliharaanModel->retrieve_detail($id);
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
			// $traksi_id =  $value['traksi_id']['id'];

			if (is_null($blok_id) || empty($blok_id)) {
				$res_akun = $this->db->query("	SELECT a.kode AS kode_kegiatan,a.nama AS nama_kegiatan,b.kode AS kode_akun,b.nama AS nama_akun FROM acc_kegiatan a 
						INNER JOIN acc_akun b ON a.acc_akun_id=b.id WHERE b.kelompok_biaya 
						IN('PNN','PMK','PML') 
						AND a.id=" . $kegiatan_id . "")->row_array();

				if ($res_akun) {
					$msg = "Kegiatan:" . $res_akun['nama_kegiatan'] . " harus diiisi Blok";
					$this->set_response(array("status" => "NOT OK", "data" => $msg), REST_Controller::HTTP_OK);
					return;
				}
			}
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
		/* END VALIDASI BLOK TIDAK DIISI */


		$this->load->library('Autonumber');
		$input['no_transaksi'] = $this->autonumber->est_bkm_pemeliharaan($input['lokasi_id']['id'], $input['tanggal']);

		//$input['no_spat'] = $this->getLastNumber('pks_pengolahan_ht', 'no_spat', 'SPAT');
		// var_dump($input);

		$res = $this->EstBkmPemeliharaanModel->create($input);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'est_bkm_pemeliharaan', 'action' => 'new', 'entity_id' => $res);
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
			$blok_id = $value['blok_id']['id'];
			$kegiatan_id = $value['kegiatan_id']['id'];
			// $traksi_id =  $value['traksi_id']['id'];

			if (is_null($blok_id) || empty($blok_id)) {
				$res_akun = $this->db->query("	SELECT a.kode AS kode_kegiatan,a.nama AS nama_kegiatan,b.kode AS kode_akun,b.nama AS nama_akun FROM acc_kegiatan a 
								INNER JOIN acc_akun b ON a.acc_akun_id=b.id WHERE b.kelompok_biaya 
								IN('PNN','PMK','PML') 
								AND a.id=" . $kegiatan_id . "")->row_array();

				if ($res_akun) {
					$msg = "Kegiatan:" . $res_akun['nama_kegiatan'] . " harus diiisi Blok";
					$this->set_response(array("status" => "NOT OK", "data" => $msg), REST_Controller::HTTP_OK);
					return;
				}
			}

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
		/* END VALIDASI BLOK TIDAK DIISI */

		$res = $this->EstBkmPemeliharaanModel->update($id, $data);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'est_bkm_pemeliharaan', 'action' => 'edit', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	public function index_delete($id)
	{

		$res = $this->EstBkmPemeliharaanModel->delete($id);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id' => $id)), 'entity' => 'est_bkm_pemeliharaan', 'action' => 'delete', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function posting_post_old($segment_3 = null)
	{
		$id = (int) $segment_3;
		/* GET SETTING AKUN */
		$res_akun_pemeliharaan_upah = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='PEMELIHARAAN_KEBUN_UPAH'")->row_array();
		if (empty($res_akun_pemeliharaan_upah)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$res_akun_pemeliharaan_premi = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='PEMELIHARAAN_KEBUN_PREMI'")->row_array();
		if (empty($res_akun_pemeliharaan_premi)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$res_akun_pemeliharaan_upah_pengawas = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='PEMELIHARAAN_KEBUN_UPAH_PENGAWAS'")->row_array();
		if (empty($res_akun_pemeliharaan_upah_pengawas)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$res_akun_pemeliharaan_premi_pengawas = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='PEMELIHARAAN_KEBUN_PREMI_PENGAWAS'")->row_array();
		if (empty($res_akun_pemeliharaan_premi_pengawas)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		//$akun_debet_premi = $res_akun_pemeliharaan_premi['acc_akun_id_debet'];
		$akun_kredit_premi = $res_akun_pemeliharaan_premi['acc_akun_id'];
		//$akun_debet_upah = $res_akun_pemeliharaan_upah['acc_akun_id_debet'];
		$akun_kredit_upah = $res_akun_pemeliharaan_upah['acc_akun_id'];
		$akun_debet_pengawas_premi = $res_akun_pemeliharaan_premi_pengawas['acc_akun_id_debet'];
		$akun_kredit_pengawas_premi = $res_akun_pemeliharaan_premi_pengawas['acc_akun_id_kredit'];
		$akun_debet_pengawas_upah = $res_akun_pemeliharaan_upah_pengawas['acc_akun_id_debet'];
		$akun_kredit_pengawas_upah = $res_akun_pemeliharaan_upah_pengawas['acc_akun_id_kredit'];

		$retrieve_inter_akun = $this->db->query("SELECT * FROM acc_inter_unit
		where tipe='INTER' ")->result_array();
		$akun_inter = array();
		foreach ($retrieve_inter_akun as $key => $akun) {
			$akun_inter[$akun['lokasi_id']][$akun['lokasi_id_2']] = $akun['acc_akun_id'];
		}
		/* === END GET SETTING AKUN ==*/

		$retrieve_header = $this->EstBkmPemeliharaanModel->retrieve_by_id($id);

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

		$res_mandor = $this->db->query("select * from karyawan where id=" . $retrieve_header['mandor_id'])->row_array();
		$lokasi_tugas_id_mandor = $res_mandor['lokasi_tugas_id'];
		$res_kerani = $this->db->query("select * from karyawan where id=" . $retrieve_header['kerani_id'])->row_array();
		$lokasi_tugas_id_kerani = $res_kerani['lokasi_tugas_id'];

		$data_transaksi = $this->db->query("SELECT a.id,a.no_transaksi,a.tanggal,b.item_id,b.qty,b.total,b.blok_id,b.kegiatan_id,
		d.kode as kode_barang,d.nama as nama_barang,f.statusblok as umur_tanam_blok,f.tahuntanam,
		c.kode as kode_blok,c.nama as nama_blok,g.acc_akun_id ,g.acc_akun_id ,h.acc_akun_id AS acc_akun_biaya_id
		FROM est_bkm_pemeliharaan_ht a inner join est_bkm_pemeliharaan_item b on a.id=b.bkm_pemeliharaan_id  
		left join gbm_organisasi c on b.blok_id=c.id 
		left join gbm_blok f on b.blok_id=f.organisasi_id
		left join inv_item d on b.item_id=d.id
		left join inv_kategori g on d.inv_kategori_id=g.id
		left JOIN acc_kegiatan h ON b.kegiatan_id=h.id
	    where b.bkm_pemeliharaan_id=" . $id . ";")->result_array();

		if ($data_transaksi) {
			/* Cari gudang virtual utk afdeling */
			$gudang_id = null;
			$retrieve_gudang = $this->db->query("SELECT * from gbm_organisasi 
			where afdeling_id=" . $retrieve_header['rayon_afdeling_id']  . " and tipe='GUDANG_VIRTUAL'")->row_array();
			if (empty($retrieve_gudang)) {
				$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Gudang Virtual"), REST_Controller::HTTP_NOT_FOUND);
				return;
			} else {
				$gudang_id = $retrieve_gudang['id'];
			}
		}
		// cek stok
		$ada_stok_minus = false;
		$result_stok = array();
		foreach ($data_transaksi as $key => $value) {
			$stok = $this->InvItemModel->getStok($value['item_id'], $gudang_id);
			$cek = $stok - $value['qty'];
			if ($cek < 0) {
				$ada_stok_minus = true;
				$item = array('kode' => $value['kode_barang'], 'nama' => $value['nama_barang'], 'stok' => $cek);
				$result_stok[] = $item;
			}
		}
		if ($ada_stok_minus) {
			$this->set_response(array("status" => "NOT OK", "data" => $result_stok), REST_Controller::HTTP_OK);
			return;
		}

		// hapus  transaksi harian
		$this->db->where('ref_id', $retrieve_header['id']);
		$this->db->where('tipe', 'PEMAKAIAN_BKM');
		$this->db->delete('inv_transaksi_harian');

		$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'INV_PEMAKAIAN_BARANG_BKM');

		// Data HEADER BIAYA BAHAN/ITEM
		$this->load->library('Autonumber');
		$no_jurnal = $this->autonumber->jurnal_auto($retrieve_header['lokasi_id'], $retrieve_header['tanggal'], 'INVBKM');
		$dataH = array(
			'no_jurnal' => $no_jurnal,
			'lokasi_id' => $retrieve_header['lokasi_id'],
			'tanggal' => $retrieve_header['tanggal'],
			'no_ref' => $retrieve_header['no_transaksi'],
			'ref_id' => $retrieve_header['id'],
			'tipe_jurnal' => 'AUTO',
			'modul' => 'INV_PEMAKAIAN_BARANG_BKM',
			'keterangan' => 'INV_PEMAKAIAN_BARANG_BKM',
			'is_posting' => 1
		);
		$id_header = $this->AccJurnalModel->create_header($dataH);
		foreach ($data_transaksi as $key => $value) {
			$stok_akhir = $this->db->query("SELECT  SUM(qty_masuk-qty_keluar)AS qty,SUM(nilai_masuk-nilai_keluar)AS nilai from inv_transaksi_harian
					where	item_id=" . $value['item_id'] . " and gudang_id=" . $gudang_id . " ")->row_array();
			$stok_akhir_qty = 	$stok_akhir['qty'] = (!empty($stok_akhir['qty'])) ? $stok_akhir['qty'] : 0;
			$stok_akhir_nilai = 	$stok_akhir['nilai'] = (!empty($stok_akhir['nilai'])) ? $stok_akhir['nilai'] : 0;

			$item_dt = $this->db->query("select * from inv_item_dt  where gudang_id=" . $gudang_id . "
			  and item_id=" . $value['item_id'] . "")->row_array();
			$avg_price = 0;
			if ($item_dt) {
				$avg_price =	$stok_akhir_nilai / $stok_akhir_qty; //$item_dt['nilai'] / $item_dt['qty'];
				$this->db->where('item_id', $value['item_id']);
				$this->db->where('gudang_id', $gudang_id);
				$this->db->update("inv_item_dt", array(
					'item_id' => $value['item_id'],
					'gudang_id' => $gudang_id,
					'qty' => $stok_akhir_qty - $value['qty'], //$item_dt['qty'] - $value['qty'],
					'nilai' =>	$stok_akhir_nilai - ($value['qty'] * $avg_price) // $item_dt['nilai'] - ($value['qty'] * $avg_price)
				));
			} else {
				$this->db->insert("inv_item_dt", array(
					'item_id' => $value['item_id'],
					'gudang_id' => $gudang_id,
					'qty' => $stok_akhir_qty - $value['qty'], //$item_dt['qty'] - $value['qty'],
					'nilai' =>	$stok_akhir_nilai - ($value['qty'] * $avg_price) // $item_dt['nilai'] - ($value['qty'] * $avg_price)
				));
			}
			$nilai = ($value['qty'] * $avg_price);
			// Biaya bahan Kredit
			$dataCr = array(
				'lokasi_id' => $retrieve_header['lokasi_id'],
				'jurnal_id' => $id_header,
				'acc_akun_id' => ($value['acc_akun_id']), //akun 
				'debet' => 0,
				'kredit' => $nilai,
				'ket' => 'Pemakaian BKM Item:' . $value['nama_barang'] . ', qty:' . $value['qty'] . ' ' . $value['uom'],
				'no_referensi' => $retrieve_header['no_transaksi'],
				'referensi_id' => NULL,
				'blok_stasiun_id' => NULL,
				'kegiatan_id' => NULL, // ,
				'kendaraan_mesin_id' => NULL,

			);

			$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataCr);
			// Biaya bahan Debet
			$dataDr = array(
				'lokasi_id' => $retrieve_header['lokasi_id'],
				'jurnal_id' => $id_header,
				'acc_akun_id' => $value['acc_akun_biaya_id'], //akun ,
				'debet' =>  $nilai,
				'kredit' => 0,
				'ket' => 'Pemakaian BKM Item:' . $value['nama_barang'] . ', qty:' . $value['qty'] . ' ' . $value['uom'],
				'no_referensi' => $retrieve_header['no_transaksi'],
				'referensi_id' => NULL,
				'kegiatan_id' => $value['kegiatan_id'],
				'kendaraan_mesin_id' => NULL,
				'item_id' => $value['item_id'], //item,
				'umur_tanam_blok' => $value['umur_tanam_blok'],
				'blok_stasiun_id' =>  $value['blok_id'],
			);
			$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataDr);
			$this->db->insert("inv_transaksi_harian", array(
				'ref_id' => $id,
				'item_id' => $value['item_id'],
				'gudang_id' => $gudang_id,
				'no_transaksi' => $value['no_transaksi'],
				'tipe ' => 'PEMAKAIAN_BKM',
				'tanggal' => $value['tanggal'],
				'tanggal_proses' => date('Y-m-d H:i:s'),
				'qty_masuk' => 0,
				'qty_keluar' =>  $value['qty'],
				'nilai_masuk' => 0,
				'nilai_keluar' => $avg_price * $value['qty'],
				'blok_stasiun_id' =>  $value['blok_id'],
				'kendaraan_id' => NULL,
				'kegiatan_id' =>  $value['kegiatan_id'],
			));
		}
		// ==End Pemkaian bahan=== //

		$id_header = 0;
		// Hapus jurnal jika sdh ada berdasarkan no refernsi dan Modul  //
		$this->AccJurnalUpahModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'PEMELIHARAAN');

		/* JIka bukan premi kontanan  maka jurnal*/
		if (!$retrieve_header['is_premi_kontanan'] || $retrieve_header['is_premi_kontanan'] == '0') {
			/* mulai Jurnal Upah */
			$retrieve_detail = $this->db->query("SELECT a.*,b.*,c.nip,c.nama as nama_karyawan, d.kode as kode_blok,d.nama as nama_blok,
			e.kode as kode_kegiatan,e.nama as nama_kegiatan,e.acc_akun_id, b.acc_kegiatan_id,f.statusblok as umur_tanam_blok,
			f.tahuntanam,c.lokasi_tugas_id
			FROM est_bkm_pemeliharaan_ht a 
			inner join est_bkm_pemeliharaan_dt b on a.id=b.bkm_pemeliharaan_id 
			inner join karyawan c on b.karyawan_id=c.id 
			inner join gbm_organisasi d on b.blok_id=d.id 
			inner join gbm_blok f on b.blok_id=f.organisasi_id
			left join acc_kegiatan e on b.acc_kegiatan_id =e.id  
			where b.bkm_pemeliharaan_id=" . $id . "")->result_array();

			$total_pendapatan = 0;
			$upah_mandor_proporsi = 0;
			$premi_mandor_proporsi = 0;
			$upah_kerani_proporsi = 0;
			$premi_kerani_proporsi = 0;
			// $upah_mandor = $retrieve_header['rp_hk_mandor'];
			// $premi_mandor = $retrieve_header['premi_mandor'];
			// $upah_kerani = $retrieve_header['rp_hk_kerani'];
			// $premi_kerani = $retrieve_header['premi_kerani'];
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

			$total_pendapatan = 0;
			// Data HEADER
			$this->load->library('Autonumber');
			$no_jurnal = $this->autonumber->jurnal_upah_auto($retrieve_header['lokasi_id'], $retrieve_header['tanggal'], 'PML');
			$dataH = array(
				'no_jurnal' => $no_jurnal,
				'lokasi_id' => $retrieve_header['lokasi_id'],
				'tanggal' => $retrieve_header['tanggal'],
				'no_ref' => $retrieve_header['no_transaksi'],
				'ref_id' => $retrieve_header['id'],
				'tipe_jurnal' => 'AUTO',
				'modul' => 'PEMELIHARAAN',
				'keterangan' => 'TRANSAKSI PEMELIHARAAN',
				'is_posting' => 1,
			);
			$id_header = $this->AccJurnalUpahModel->create_header($dataH);
			$total_pendapatan = 0;
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
					if ($upah_mandor > 0) {
						$dataDebet = array(
							'lokasi_id' => $retrieve_header['lokasi_id'],
							'jurnal_id' => $id_header,
							'acc_akun_id' => $akun_debet_transit_upah, //akun biaya Panen,
							'debet' => $upah_mandor,
							'kredit' => 0,
							'ket' => 'Biaya Pengawas(Mandor) Pemeliharaan ',
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
							'ket' => 'Biaya Pengawas(Mandor) Pemeliharaan Upah',
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
					if ($premi_mandor > 0) {
						$dataDebet = array(
							'lokasi_id' => $retrieve_header['lokasi_id'],
							'jurnal_id' => $id_header,
							'acc_akun_id' => $akun_debet_transit_premi, //akun biaya Panen,
							'debet' => $premi_mandor,
							'kredit' => 0,
							'ket' => 'Biaya Pengawas(Mandor) Pemeliharaan Premi',
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
							'ket' => 'Biaya Pengawas(Mandor) Pemeliharaan Premi ',
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
				/* end MANDOR */

				/* KERANI */
				if ($retrieve_header['kerani_id']) {
					if ($upah_kerani > 0) {
						$dataDebet = array(
							'lokasi_id' => $retrieve_header['lokasi_id'],
							'jurnal_id' => $id_header,
							'acc_akun_id' => $akun_debet_transit_upah, //akun biaya Panen,
							'debet' => $upah_kerani,
							'kredit' => 0,
							'ket' => 'Biaya Pengawas(Kerani) Pemeliharaan',
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
							'ket' => 'Biaya Pengawas(Kerani) Pemeliharaan Upah',
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
					if ($premi_kerani > 0) {
						$dataDebet = array(
							'lokasi_id' => $retrieve_header['lokasi_id'],
							'jurnal_id' => $id_header,
							'acc_akun_id' => $akun_debet_transit_upah, //akun biaya Panen,
							'debet' => $premi_kerani,
							'kredit' => 0,
							'ket' => 'Biaya Pengawas(Kerani) Pemeliharaan Premi ',
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
							'ket' => 'Biaya Pengawas(Kerani) Pemeliharaan Premi ',
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
				/* eND KERANI */
			} else {
				foreach ($retrieve_detail as $key => $value) {
					$upah = $value['rupiah_hk'];
					$premi = $value['premi'];
					if ($upah > 0) {
						$dataDebet = array(
							'lokasi_id' => $retrieve_header['lokasi_id'],
							'jurnal_id' => $id_header,
							'acc_akun_id' => $value['acc_akun_id'], //akun biaya pemeliharaan,
							'debet' => ($upah),
							'kredit' => 0,
							'ket' => 'Biaya Pemeliharaan upah Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
							'no_referensi' => $retrieve_header['no_transaksi'],
							'referensi_id' => NULL,
							'blok_stasiun_id' => $value['blok_id'],
							'kegiatan_id' => $value['acc_kegiatan_id'], //kegiatan pemeliharaan,
							'kendaraan_mesin_id' => NULL,
							'karyawan_id' => $value['karyawan_id'], //karyawan,
							'umur_tanam_blok' => $value['umur_tanam_blok'],
							'tipe' => 'upah',
							'hk' => $value['jumlah_hk']
						);
						$total_pendapatan =	$total_pendapatan + ($value['rupiah_hk'] + $value['premi']);
						$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
						// Data KREDIT
						$dataKredit = array(
							'lokasi_id' => $retrieve_header['lokasi_id'],
							'jurnal_id' => $id_header,
							'acc_akun_id' => $akun_kredit_upah, //$value['acc_akun_id'],
							'debet' => 0,
							'kredit' => $upah, // Akun Lawan Biaya
							'ket' => 'Biaya Pemeliharaan upah Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
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
							'acc_akun_id' => $value['acc_akun_id'], //akun biaya pemeliharaan,
							'debet' => ($premi),
							'kredit' => 0,
							'ket' => 'Biaya Pemeliharaan Premi Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
							'no_referensi' => $retrieve_header['no_transaksi'],
							'referensi_id' => NULL,
							'blok_stasiun_id' => $value['blok_id'],
							'kegiatan_id' => $value['acc_kegiatan_id'], //kegiatan pemeliharaan,
							'kendaraan_mesin_id' => NULL,
							'karyawan_id' => $value['karyawan_id'], //karyawan,
							'umur_tanam_blok' => $value['umur_tanam_blok'],
							'tipe' => 'premi',
							'hk' => 0
						);
						$total_pendapatan =	$total_pendapatan + ($value['rupiah_hk'] + $value['premi']);
						$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
						// Data KREDIT
						$dataKredit = array(
							'lokasi_id' => $retrieve_header['lokasi_id'],
							'jurnal_id' => $id_header,
							'acc_akun_id' => $akun_kredit_premi, //$value['acc_akun_id'],
							'debet' => 0,
							'kredit' => $premi, // Akun Lawan Biaya
							'ket' => 'Biaya Pemeliharaan Premi Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
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
					/* MANDOR */
					if ($value['mandor_id']) {
						if ($upah_mandor_proporsi > 0) {
							$dataDebet = array(
								'lokasi_id' => $retrieve_header['lokasi_id'],
								'jurnal_id' => $id_header,
								'acc_akun_id' => $akun_debet_pengawas_upah, //akun biaya Panen,
								'debet' => $upah_mandor_proporsi,
								'kredit' => 0,
								'ket' => 'Biaya Pengawas(Mandor) Pemeliharaan Upah Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => $value['blok_id'],
								'kegiatan_id' => $value['acc_kegiatan_id'], //kegiatan panen,
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
								'ket' => 'Biaya Pengawas(Mandor) Pemeliharaan Upah Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
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
						if ($premi_mandor_proporsi > 0) {
							$dataDebet = array(
								'lokasi_id' => $retrieve_header['lokasi_id'],
								'jurnal_id' => $id_header,
								'acc_akun_id' => $akun_debet_pengawas_premi, //akun biaya Panen,
								'debet' => $premi_mandor_proporsi,
								'kredit' => 0,
								'ket' => 'Biaya Pengawas(Mandor) Pemeliharaan Premi Blok: ' . $value['nama_blok'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => $value['blok_id'],
								'kegiatan_id' => $value['acc_kegiatan_id'], //kegiatan panen,
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
								'ket' => 'Biaya Pengawas(Mandor) Pemeliharaan Premi Blok: ' . $value['nama_blok'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
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
					/* end MANDOR */

					/* KERANI */
					if ($value['kerani_id']) {
						if ($upah_kerani_proporsi > 0) {
							$dataDebet = array(
								'lokasi_id' => $retrieve_header['lokasi_id'],
								'jurnal_id' => $id_header,
								'acc_akun_id' => $akun_debet_pengawas_upah, //akun biaya Panen,
								'debet' => $upah_kerani_proporsi,
								'kredit' => 0,
								'ket' => 'Biaya Pengawas(Kerani) Pemeliharaan Upah Blok: ' . $value['nama_blok'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => $value['blok_id'],
								'kegiatan_id' => $value['acc_kegiatan_id'], //kegiatan panen,
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
								'ket' => 'Biaya Pengawas(Kerani) Pemeliharaan Upah Blok: ' . $value['nama_blok'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
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
						if ($premi_kerani_proporsi > 0) {
							$dataDebet = array(
								'lokasi_id' => $retrieve_header['lokasi_id'],
								'jurnal_id' => $id_header,
								'acc_akun_id' => $akun_debet_pengawas_premi, //akun biaya Panen,
								'debet' => $premi_kerani_proporsi,
								'kredit' => 0,
								'ket' => 'Biaya Pengawas(Kerani) Pemeliharaan Premi Blok: ' . $value['nama_blok'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => $value['blok_id'],
								'kegiatan_id' => $value['acc_kegiatan_id'], //kegiatan panen,
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
								'ket' => 'Biaya Pengawas(Kerani) Pemeliharaan Premi Blok: ' . $value['nama_blok'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
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
					/* eND KERANI */
				}
			}
			$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'PEMELIHARAAN');
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
				$no_jurnal = $this->autonumber->jurnal_auto($retrieve_header['lokasi_id'], $retrieve_header['tanggal'], 'PML');

				$dataH = array(
					'no_jurnal' => $no_jurnal,
					'lokasi_id' => $retrieve_header['lokasi_id'],
					'tanggal' => $retrieve_header['tanggal'],
					'no_ref' => $retrieve_header['no_transaksi'],
					'ref_id' => $retrieve_header['id'],
					'tipe_jurnal' => 'AUTO',
					'modul' => 'PEMELIHARAAN',
					'keterangan' => 'TRANSAKSI PEMELIHARAAN',
					'is_posting' => 1,
				);
				$id_header2 = $this->AccJurnalModel->create_header($dataH);

				foreach ($resJurnalTemp as $key => $JurnalTemp) {
					$dataDebetKredit = array(
						'lokasi_id' => $retrieve_header['lokasi_id'],
						'jurnal_id' => $id_header2,
						'acc_akun_id' => $JurnalTemp['acc_akun_id'], //$value['acc_akun_id'],
						'debet' => $JurnalTemp['debet'],
						'kredit' => $JurnalTemp['kredit'], // Akun Lawan Biaya
						'ket' => 'Pemeliharaan Blok: ' . $JurnalTemp['blok'],
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
		$res = $this->EstBkmPemeliharaanModel->posting($id, $input);
		if (!empty($res)) {
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function posting_post($segment_3 = null)
	{
		$id = (int) $segment_3;
		/* GET SETTING AKUN */
		$res_akun_pemeliharaan_upah = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='PEMELIHARAAN_KEBUN_UPAH'")->row_array();
		if (empty($res_akun_pemeliharaan_upah)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$res_akun_pemeliharaan_premi = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='PEMELIHARAAN_KEBUN_PREMI'")->row_array();
		if (empty($res_akun_pemeliharaan_premi)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$res_akun_pemeliharaan_upah_pengawas = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='PEMELIHARAAN_KEBUN_UPAH_PENGAWAS'")->row_array();
		if (empty($res_akun_pemeliharaan_upah_pengawas)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$res_akun_pemeliharaan_premi_pengawas = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='PEMELIHARAAN_KEBUN_PREMI_PENGAWAS'")->row_array();
		if (empty($res_akun_pemeliharaan_premi_pengawas)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		//$akun_debet_premi = $res_akun_pemeliharaan_premi['acc_akun_id_debet'];
		$akun_kredit_premi = $res_akun_pemeliharaan_premi['acc_akun_id'];
		//$akun_debet_upah = $res_akun_pemeliharaan_upah['acc_akun_id_debet'];
		$akun_kredit_upah = $res_akun_pemeliharaan_upah['acc_akun_id'];
		$akun_debet_pengawas_premi = $res_akun_pemeliharaan_premi_pengawas['acc_akun_id_debet'];
		$akun_kredit_pengawas_premi = $res_akun_pemeliharaan_premi_pengawas['acc_akun_id_kredit'];
		$akun_debet_pengawas_upah = $res_akun_pemeliharaan_upah_pengawas['acc_akun_id_debet'];
		$akun_kredit_pengawas_upah = $res_akun_pemeliharaan_upah_pengawas['acc_akun_id_kredit'];

		$retrieve_inter_akun = $this->db->query("SELECT * FROM acc_inter_unit
		where tipe='INTER' ")->result_array();
		$akun_inter = array();
		foreach ($retrieve_inter_akun as $key => $akun) {
			$akun_inter[$akun['lokasi_id']][$akun['lokasi_id_2']] = $akun['acc_akun_id'];
		}
		/* === END GET SETTING AKUN ==*/

		$retrieve_header = $this->EstBkmPemeliharaanModel->retrieve_by_id($id);
		if (empty($retrieve_header)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Untuk diposting"), REST_Controller::HTTP_NOT_FOUND);
			return;
		} else {
			if ($retrieve_header['is_posting'] == 1) {
				$this->set_response(array("status" => "NOT OK", "data" => "Data Sudah diposting"), REST_Controller::HTTP_NOT_FOUND);
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

		$data_transaksi = $this->db->query("SELECT a.id,a.no_transaksi,a.tanggal,b.item_id,b.qty,b.total,b.blok_id,b.kegiatan_id,
		d.kode as kode_barang,d.nama as nama_barang,f.statusblok as umur_tanam_blok,f.tahuntanam,
		c.kode as kode_blok,c.nama as nama_blok,g.acc_akun_id ,g.acc_akun_id ,h.acc_akun_id AS acc_akun_biaya_id
		FROM est_bkm_pemeliharaan_ht a inner join est_bkm_pemeliharaan_item b on a.id=b.bkm_pemeliharaan_id  
		left join gbm_organisasi c on b.blok_id=c.id 
		left join gbm_blok f on b.blok_id=f.organisasi_id
		left join inv_item d on b.item_id=d.id
		left join inv_kategori g on d.inv_kategori_id=g.id
		left JOIN acc_kegiatan h ON b.kegiatan_id=h.id
	    where b.bkm_pemeliharaan_id=" . $id . ";")->result_array();

		if ($data_transaksi) {
			/* Cari gudang virtual utk afdeling */
			$gudang_id = null;
			$retrieve_gudang = $this->db->query("SELECT * from gbm_organisasi 
			where afdeling_id=" . $retrieve_header['rayon_afdeling_id']  . " and tipe='GUDANG_VIRTUAL'")->row_array();
			if (empty($retrieve_gudang)) {
				$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Gudang Virtual"), REST_Controller::HTTP_NOT_FOUND);
				return;
			} else {
				$gudang_id = $retrieve_gudang['id'];
			}
		}
		// cek stok
		$ada_stok_minus = false;
		$result_stok = array();
		foreach ($data_transaksi as $key => $value) {
			$stok = $this->InvItemModel->getStok($value['item_id'], $gudang_id);
			$cek = $stok - $value['qty'];
			if ($cek < 0) {
				$ada_stok_minus = true;
				$item = array('kode' => $value['kode_barang'], 'nama' => $value['nama_barang'], 'stok' => $cek);
				$result_stok[] = $item;
			}
		}
		if ($ada_stok_minus) {
			$this->set_response(array("status" => "NOT OK", "data" => $result_stok), REST_Controller::HTTP_OK);
			return;
		}

		// hapus  transaksi harian
		$this->db->where('ref_id', $retrieve_header['id']);
		$this->db->where('tipe', 'PEMAKAIAN_BKM');
		$this->db->delete('inv_transaksi_harian');

		$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'INV_PEMAKAIAN_BARANG_BKM');

		// Data HEADER BIAYA BAHAN/ITEM
		$this->load->library('Autonumber');
		$no_jurnal = $this->autonumber->jurnal_auto($retrieve_header['lokasi_id'], $retrieve_header['tanggal'], 'INVBKM');
		$dataH = array(
			'no_jurnal' => $no_jurnal,
			'lokasi_id' => $retrieve_header['lokasi_id'],
			'tanggal' => $retrieve_header['tanggal'],
			'no_ref' => $retrieve_header['no_transaksi'],
			'ref_id' => $retrieve_header['id'],
			'tipe_jurnal' => 'AUTO',
			'modul' => 'INV_PEMAKAIAN_BARANG_BKM',
			'keterangan' => 'INV_PEMAKAIAN_BARANG_BKM',
			'is_posting' => 1
		);
		$id_header = $this->AccJurnalModel->create_header($dataH);
		foreach ($data_transaksi as $key => $value) {
			$stok_akhir = $this->db->query("SELECT  SUM(qty_masuk-qty_keluar)AS qty,SUM(nilai_masuk-nilai_keluar)AS nilai from inv_transaksi_harian
					where	item_id=" . $value['item_id'] . " and gudang_id=" . $gudang_id . " ")->row_array();
			$stok_akhir_qty = 	$stok_akhir['qty'] = (!empty($stok_akhir['qty'])) ? $stok_akhir['qty'] : 0;
			$stok_akhir_nilai = 	$stok_akhir['nilai'] = (!empty($stok_akhir['nilai'])) ? $stok_akhir['nilai'] : 0;

			$item_dt = $this->db->query("select * from inv_item_dt  where gudang_id=" . $gudang_id . "
			  and item_id=" . $value['item_id'] . "")->row_array();
			$avg_price = 0;
			if ($item_dt) {
				$avg_price =	$stok_akhir_nilai / $stok_akhir_qty; //$item_dt['nilai'] / $item_dt['qty'];
				$this->db->where('item_id', $value['item_id']);
				$this->db->where('gudang_id', $gudang_id);
				$this->db->update("inv_item_dt", array(
					'item_id' => $value['item_id'],
					'gudang_id' => $gudang_id,
					'qty' => $stok_akhir_qty - $value['qty'], //$item_dt['qty'] - $value['qty'],
					'nilai' =>	$stok_akhir_nilai - ($value['qty'] * $avg_price) // $item_dt['nilai'] - ($value['qty'] * $avg_price)
				));
			} else {
				$this->db->insert("inv_item_dt", array(
					'item_id' => $value['item_id'],
					'gudang_id' => $gudang_id,
					'qty' => $stok_akhir_qty - $value['qty'], //$item_dt['qty'] - $value['qty'],
					'nilai' =>	$stok_akhir_nilai - ($value['qty'] * $avg_price) // $item_dt['nilai'] - ($value['qty'] * $avg_price)
				));
			}
			$nilai = ($value['qty'] * $avg_price);
			// Biaya bahan Kredit
			$dataCr = array(
				'lokasi_id' => $retrieve_header['lokasi_id'],
				'jurnal_id' => $id_header,
				'acc_akun_id' => ($value['acc_akun_id']), //akun 
				'debet' => 0,
				'kredit' => $nilai,
				'ket' => 'Pemakaian BKM Item:' . $value['nama_barang'] . ', qty:' . $value['qty'] . ' ' . $value['uom'],
				'no_referensi' => $retrieve_header['no_transaksi'],
				'referensi_id' => NULL,
				'blok_stasiun_id' => NULL,
				'kegiatan_id' => NULL, // ,
				'kendaraan_mesin_id' => NULL,

			);

			$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataCr);
			// Biaya bahan Debet
			$dataDr = array(
				'lokasi_id' => $retrieve_header['lokasi_id'],
				'jurnal_id' => $id_header,
				'acc_akun_id' => $value['acc_akun_biaya_id'], //akun ,
				'debet' =>  $nilai,
				'kredit' => 0,
				'ket' => 'Pemakaian BKM Item:' . $value['nama_barang'] . ', qty:' . $value['qty'] . ' ' . $value['uom'],
				'no_referensi' => $retrieve_header['no_transaksi'],
				'referensi_id' => NULL,
				'kegiatan_id' => $value['kegiatan_id'],
				'kendaraan_mesin_id' => NULL,
				'item_id' => $value['item_id'], //item,
				'umur_tanam_blok' => $value['umur_tanam_blok'],
				'blok_stasiun_id' =>  $value['blok_id'],
			);
			$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataDr);
			$this->db->insert("inv_transaksi_harian", array(
				'ref_id' => $id,
				'item_id' => $value['item_id'],
				'gudang_id' => $gudang_id,
				'no_transaksi' => $value['no_transaksi'],
				'tipe ' => 'PEMAKAIAN_BKM',
				'tanggal' => $value['tanggal'],
				'tanggal_proses' => date('Y-m-d H:i:s'),
				'qty_masuk' => 0,
				'qty_keluar' =>  $value['qty'],
				'nilai_masuk' => 0,
				'nilai_keluar' => $avg_price * $value['qty'],
				'blok_stasiun_id' =>  $value['blok_id'],
				'kendaraan_id' => NULL,
				'kegiatan_id' =>  $value['kegiatan_id'],
			));
		}
		// ==End Pemkaian bahan=== //

		$id_header = 0;
		// Hapus jurnal jika sdh ada berdasarkan no refernsi dan Modul  //
		$this->AccJurnalUpahModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'PEMELIHARAAN');

		/* JIka bukan premi kontanan  maka jurnal*/
		if (!$retrieve_header['is_premi_kontanan'] || $retrieve_header['is_premi_kontanan'] == '0') {
			/* mulai Jurnal Upah */
			$retrieve_detail = $this->db->query("SELECT a.*,b.*,c.nip,c.nama as nama_karyawan, d.kode as kode_blok,d.nama as nama_blok,
			e.kode as kode_kegiatan,e.nama as nama_kegiatan,e.acc_akun_id, b.acc_kegiatan_id,f.statusblok as umur_tanam_blok,
			f.tahuntanam,c.lokasi_tugas_id
			FROM est_bkm_pemeliharaan_ht a 
			inner join est_bkm_pemeliharaan_dt b on a.id=b.bkm_pemeliharaan_id 
			inner join karyawan c on b.karyawan_id=c.id 
			inner join gbm_organisasi d on b.blok_id=d.id 
			inner join gbm_blok f on b.blok_id=f.organisasi_id
			left join acc_kegiatan e on b.acc_kegiatan_id =e.id  
			where b.bkm_pemeliharaan_id=" . $id . "")->result_array();

			$total_pendapatan = 0;
			$upah_mandor_proporsi = 0;
			$premi_mandor_proporsi = 0;
			$upah_kerani_proporsi = 0;
			$premi_kerani_proporsi = 0;
			// $upah_mandor = $retrieve_header['rp_hk_mandor'];
			// $premi_mandor = $retrieve_header['premi_mandor'];
			// $upah_kerani = $retrieve_header['rp_hk_kerani'];
			// $premi_kerani = $retrieve_header['premi_kerani'];
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

			$total_pendapatan = 0;
			// Data HEADER
			$this->load->library('Autonumber');
			$no_jurnal = $this->autonumber->jurnal_upah_auto($retrieve_header['lokasi_id'], $retrieve_header['tanggal'], 'PML');
			$dataH = array(
				'no_jurnal' => $no_jurnal,
				'lokasi_id' => $retrieve_header['lokasi_id'],
				'tanggal' => $retrieve_header['tanggal'],
				'no_ref' => $retrieve_header['no_transaksi'],
				'ref_id' => $retrieve_header['id'],
				'tipe_jurnal' => 'AUTO',
				'modul' => 'PEMELIHARAAN',
				'keterangan' => 'TRANSAKSI PEMELIHARAAN',
				'is_posting' => 1,
			);
			$id_header = $this->AccJurnalUpahModel->create_header($dataH);
			$total_pendapatan = 0;
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
								'ket' => 'Biaya Pengawas(Mandor) Pemeliharaan ',
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
								'ket' => 'Biaya Pengawas(Mandor) Pemeliharaan Upah',
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL,
								'kegiatan_id' => NULL, // $value['kegiatan_id'],
								'kendaraan_mesin_id' => NULL,
								'tipe' => 'upah_pengawas',
								'hk' => 0
							);
							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
						} else { // JIKA lokasi karyawan dan lokasi bekerja berbeda
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
								'kegiatan_id' => NULL, //kegiatan ,
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
								'ket' => 'Biaya Pengawas(Mandor) Pemeliharaan Premi',
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
								'ket' => 'Biaya Pengawas(Mandor) Pemeliharaan Premi ',
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
								'ket' => 'Biaya Pengawas(Kerani) Pemeliharaan',
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
								'ket' => 'Biaya Pengawas(Kerani) Pemeliharaan Upah',
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL,
								'kegiatan_id' => NULL, // $value['kegiatan_id'],
								'kendaraan_mesin_id' => NULL,
								'tipe' => 'upah_pengawas',
								'hk' => 0
							);
							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
						} else { // JIka beda lokasi
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
								'ket' => 'Biaya Pengawas(Kerani) Pemeliharaan Premi ',
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
								'ket' => 'Biaya Pengawas(Kerani) Pemeliharaan Premi ',
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL,
								'kegiatan_id' => NULL, // $value['kegiatan_id'],
								'kendaraan_mesin_id' => NULL,
								'tipe' => 'premi_pengawas',
								'hk' => 0
							);
							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
						} else { // JIKA  BEDA LOKASI
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

					$upah = $value['rupiah_hk'];
					$premi = $value['premi'];
					/* karyawan upkeep */
					if ($upah > 0) {
						if ($value['lokasi_tugas_id'] == $retrieve_header['lokasi_id']) { // JIKA lokasi karyawan dan lokasi bekerja sama
							$dataDebet = array(
								'lokasi_id' => $retrieve_header['lokasi_id'],
								'jurnal_id' => $id_header,
								'acc_akun_id' => $value['acc_akun_id'], //akun biaya pemeliharaan,
								'debet' => ($upah),
								'kredit' => 0,
								'ket' => 'Biaya Pemeliharaan upah Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => $value['blok_id'],
								'kegiatan_id' => $value['acc_kegiatan_id'], //kegiatan pemeliharaan,
								'kendaraan_mesin_id' => NULL,
								'karyawan_id' => $value['karyawan_id'], //karyawan,
								'umur_tanam_blok' => $value['umur_tanam_blok'],
								'tipe' => 'upah',
								'hk' => $value['jumlah_hk']
							);
							$total_pendapatan =	$total_pendapatan + ($value['rupiah_hk'] + $value['premi']);
							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
							// Data KREDIT
							$dataKredit = array(
								'lokasi_id' => $retrieve_header['lokasi_id'],
								'jurnal_id' => $id_header,
								'acc_akun_id' => $akun_kredit_upah, //$value['acc_akun_id'],
								'debet' => 0,
								'kredit' => $upah, // Akun Lawan Biaya
								'ket' => 'Biaya Pemeliharaan upah Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL,
								'kegiatan_id' => NULL, // $value['kegiatan_id'],
								'kendaraan_mesin_id' => NULL,
								'tipe' => 'upah',
								'hk' => 0
							);
							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
						} else { // JIka beda lokasi
							$dataDebet = array(
								'lokasi_id' => $retrieve_header['lokasi_id'],
								'jurnal_id' => $id_header,
								'acc_akun_id' => $value['acc_akun_id'], //akun biaya pemeliharaan,
								'debet' => ($upah),
								'kredit' => 0,
								'ket' => 'Biaya Pemeliharaan upah Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => $value['blok_id'],
								'kegiatan_id' => $value['acc_kegiatan_id'], //kegiatan pemeliharaan,
								'kendaraan_mesin_id' => NULL,
								'karyawan_id' => $value['karyawan_id'], //karyawan,
								'umur_tanam_blok' => $value['umur_tanam_blok'],
								'tipe' => 'upah',
								'hk' => $value['jumlah_hk']
							);
							$total_pendapatan =	$total_pendapatan + ($value['rupiah_hk'] + $value['premi']);
							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
							// Data KREDIT
							$dataKredit = array(
								'lokasi_id' => $retrieve_header['lokasi_id'],
								'jurnal_id' => $id_header,
								'acc_akun_id' => $inter_akun_id, //$value['acc_akun_id'],
								'debet' => 0,
								'kredit' => $upah, // Akun Lawan Biaya
								'ket' => 'Biaya Pemeliharaan upah Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
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
								'acc_akun_id' => $inter_akun_id, //akun biaya pemeliharaan,
								'debet' => ($upah),
								'kredit' => 0,
								'ket' => 'Biaya Pemeliharaan upah Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL, // $value['blok_id'],
								'kegiatan_id' => NULL, //$value['acc_kegiatan_id'], //kegiatan pemeliharaan,
								'kendaraan_mesin_id' => NULL,
								'karyawan_id' => NULL, // $value['karyawan_id'], //karyawan,
								'umur_tanam_blok' => NULL, // $value['umur_tanam_blok'],
								'tipe' => 'upah',
								'hk' => $value['jumlah_hk']
							);
							// $total_pendapatan =	$total_pendapatan + ($value['rupiah_hk'] + $value['premi']);
							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
							// Data KREDIT
							$dataKredit = array(
								'lokasi_id' => $value['lokasi_tugas_id'],
								'jurnal_id' => $id_header,
								'acc_akun_id' => $akun_kredit_upah, //$value['acc_akun_id'],
								'debet' => 0,
								'kredit' => $upah, // Akun Lawan Biaya
								'ket' => 'Biaya Pemeliharaan upah Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
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
								'acc_akun_id' => $value['acc_akun_id'], //akun biaya pemeliharaan,
								'debet' => ($premi),
								'kredit' => 0,
								'ket' => 'Biaya Pemeliharaan Premi Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => $value['blok_id'],
								'kegiatan_id' => $value['acc_kegiatan_id'], //kegiatan pemeliharaan,
								'kendaraan_mesin_id' => NULL,
								'karyawan_id' => $value['karyawan_id'], //karyawan,
								'umur_tanam_blok' => $value['umur_tanam_blok'],
								'tipe' => 'premi',
								'hk' => 0
							);
							$total_pendapatan =	$total_pendapatan + ($value['rupiah_hk'] + $value['premi']);
							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
							// Data KREDIT
							$dataKredit = array(
								'lokasi_id' => $retrieve_header['lokasi_id'],
								'jurnal_id' => $id_header,
								'acc_akun_id' => $akun_kredit_premi, //$value['acc_akun_id'],
								'debet' => 0,
								'kredit' => $premi, // Akun Lawan Biaya
								'ket' => 'Biaya Pemeliharaan Premi Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL,
								'kegiatan_id' => NULL, // $value['kegiatan_id'],
								'kendaraan_mesin_id' => NULL,
								'tipe' => 'premi',
								'hk' => 0
							);
							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
						} else { // JIKA BEDA LOKASI
							$dataDebet = array(
								'lokasi_id' => $retrieve_header['lokasi_id'],
								'jurnal_id' => $id_header,
								'acc_akun_id' => $value['acc_akun_id'], //akun biaya pemeliharaan,
								'debet' => ($premi),
								'kredit' => 0,
								'ket' => 'Biaya Pemeliharaan Premi Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => $value['blok_id'],
								'kegiatan_id' => $value['acc_kegiatan_id'], //kegiatan pemeliharaan,
								'kendaraan_mesin_id' => NULL,
								'karyawan_id' => $value['karyawan_id'], //karyawan,
								'umur_tanam_blok' => $value['umur_tanam_blok'],
								'tipe' => 'premi',
								'hk' => 0
							);
							$total_pendapatan =	$total_pendapatan + ($value['rupiah_hk'] + $value['premi']);
							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
							// Data KREDIT
							$dataKredit = array(
								'lokasi_id' => $retrieve_header['lokasi_id'],
								'jurnal_id' => $id_header,
								'acc_akun_id' => $inter_akun_id, //$value['acc_akun_id'],
								'debet' => 0,
								'kredit' => $premi, // Akun Lawan Biaya
								'ket' => 'Biaya Pemeliharaan Premi Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
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
								'lokasi_id' =>  $value['lokasi_tugas_id'],
								'jurnal_id' => $id_header,
								'acc_akun_id' => $inter_akun_id, //akun biaya pemeliharaan,
								'debet' => ($premi),
								'kredit' => 0,
								'ket' => 'Biaya Pemeliharaan Premi Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
								'no_referensi' => $retrieve_header['no_transaksi'],
								'referensi_id' => NULL,
								'blok_stasiun_id' => NULL, // $value['blok_id'],
								'kegiatan_id' => NULL, // $value['acc_kegiatan_id'], //kegiatan pemeliharaan,
								'kendaraan_mesin_id' => NULL,
								'karyawan_id' => NULL, // $value['karyawan_id'], //karyawan,
								'umur_tanam_blok' => NULL, //$value['umur_tanam_blok'],
								'tipe' => 'premi',
								'hk' => 0
							);
							$total_pendapatan =	$total_pendapatan + ($value['rupiah_hk'] + $value['premi']);
							$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
							// Data KREDIT
							$dataKredit = array(
								'lokasi_id' => $value['lokasi_tugas_id'],
								'jurnal_id' => $id_header,
								'acc_akun_id' => $akun_kredit_premi, //$value['acc_akun_id'],
								'debet' => 0,
								'kredit' => $premi, // Akun Lawan Biaya
								'ket' => 'Biaya Pemeliharaan Premi Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
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
					/* END KARYAWAN UPKEEP */

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
									'ket' => 'Biaya Pengawas(Mandor) Pemeliharaan Upah Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => $value['blok_id'],
									'kegiatan_id' => $value['acc_kegiatan_id'], //kegiatan panen,
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
									'ket' => 'Biaya Pengawas(Mandor) Pemeliharaan Upah Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
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
									'ket' => 'Biaya Pengawas(Mandor) Pemeliharaan Upah Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => $value['blok_id'],
									'kegiatan_id' => $value['acc_kegiatan_id'], //kegiatan panen,
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
									'ket' => 'Biaya Pengawas(Mandor) Pemeliharaan Upah Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
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
									'ket' => 'Biaya Pengawas(Mandor) Pemeliharaan Upah Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL, // $value['blok_id'],
									'kegiatan_id' => NULL, // $value['acc_kegiatan_id'], //kegiatan panen,
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
									'ket' => 'Biaya Pengawas(Mandor) Pemeliharaan Upah Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
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
									'ket' => 'Biaya Pengawas(Mandor) Pemeliharaan Premi Blok: ' . $value['nama_blok'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => $value['blok_id'],
									'kegiatan_id' => $value['acc_kegiatan_id'], //kegiatan panen,
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
									'ket' => 'Biaya Pengawas(Mandor) Pemeliharaan Premi Blok: ' . $value['nama_blok'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL,
									'kegiatan_id' => NULL, // $value['kegiatan_id'],
									'kendaraan_mesin_id' => NULL,
									'tipe' => 'premi_pengawas',
									'hk' => 0
								);
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
							} else { // JIKA beda lokasi
								$dataDebet = array(
									'lokasi_id' => $retrieve_header['lokasi_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $akun_debet_pengawas_premi, //akun biaya Panen,
									'debet' => $premi_mandor_proporsi,
									'kredit' => 0,
									'ket' => 'Biaya Pengawas(Mandor) Pemeliharaan Premi Blok: ' . $value['nama_blok'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => $value['blok_id'],
									'kegiatan_id' => $value['acc_kegiatan_id'], //kegiatan panen,
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
									'ket' => 'Biaya Pengawas(Mandor) Pemeliharaan Premi Blok: ' . $value['nama_blok'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
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
									'lokasi_id' =>  $lokasi_tugas_id_mandor,
									'jurnal_id' => $id_header,
									'acc_akun_id' => $inter_akun_id, //akun biaya Panen,
									'debet' => $premi_mandor_proporsi,
									'kredit' => 0,
									'ket' => 'Biaya Pengawas(Mandor) Pemeliharaan Premi Blok: ' . $value['nama_blok'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL, // $value['blok_id'],
									'kegiatan_id' => NULL, // $value['acc_kegiatan_id'], //kegiatan panen,
									'kendaraan_mesin_id' => NULL,
									'karyawan_id' => NULL, // $value['mandor_id'], //karyawan,
									'umur_tanam_blok' => NULL, //$value['umur_tanam_blok'],
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
									'ket' => 'Biaya Pengawas(Mandor) Pemeliharaan Premi Blok: ' . $value['nama_blok'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
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
									'ket' => 'Biaya Pengawas(Kerani) Pemeliharaan Upah Blok: ' . $value['nama_blok'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => $value['blok_id'],
									'kegiatan_id' => $value['acc_kegiatan_id'], //kegiatan panen,
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
									'ket' => 'Biaya Pengawas(Kerani) Pemeliharaan Upah Blok: ' . $value['nama_blok'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
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
									'ket' => 'Biaya Pengawas(Kerani) Pemeliharaan Upah Blok: ' . $value['nama_blok'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => $value['blok_id'],
									'kegiatan_id' => $value['acc_kegiatan_id'], //kegiatan panen,
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
									'ket' => 'Biaya Pengawas(Kerani) Pemeliharaan Upah Blok: ' . $value['nama_blok'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
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
									'ket' => 'Biaya Pengawas(Kerani) Pemeliharaan Upah Blok: ' . $value['nama_blok'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL, // $value['blok_id'],
									'kegiatan_id' => NULL, // $value['acc_kegiatan_id'], //kegiatan panen,
									'kendaraan_mesin_id' => NULL,
									'karyawan_id' => NULL, // $value['kerani_id'], //karyawan,
									'umur_tanam_blok' => NULL, //$value['umur_tanam_blok'],
									'tipe' => 'upah_pengawas',
									'hk' => 0
								);
								// $total_pendapatan =	$total_pendapatan + $value['total_pendapatan'];
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
								// Data KREDIT
								$dataKredit = array(
									'lokasi_id' => $lokasi_tugas_id_kerani,
									'jurnal_id' => $id_header,
									'acc_akun_id' => $akun_kredit_pengawas_upah, //$value['acc_akun_id'],
									'debet' => 0,
									'kredit' => $upah_kerani_proporsi, // Akun Lawan Biaya
									'ket' => 'Biaya Pengawas(Kerani) Pemeliharaan Upah Blok: ' . $value['nama_blok'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
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
									'ket' => 'Biaya Pengawas(Kerani) Pemeliharaan Premi Blok: ' . $value['nama_blok'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => $value['blok_id'],
									'kegiatan_id' => $value['acc_kegiatan_id'], //kegiatan panen,
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
									'ket' => 'Biaya Pengawas(Kerani) Pemeliharaan Premi Blok: ' . $value['nama_blok'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL,
									'kegiatan_id' => NULL, // $value['kegiatan_id'],
									'kendaraan_mesin_id' => NULL,
									'tipe' => 'premi_pengawas',
									'hk' => 0
								);
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
							} else { // JIKA lokasi berbeda
								$dataDebet = array(
									'lokasi_id' => $retrieve_header['lokasi_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $akun_debet_pengawas_premi, //akun biaya Panen,
									'debet' => $premi_kerani_proporsi,
									'kredit' => 0,
									'ket' => 'Biaya Pengawas(Kerani) Pemeliharaan Premi Blok: ' . $value['nama_blok'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => $value['blok_id'],
									'kegiatan_id' => $value['acc_kegiatan_id'], //kegiatan panen,
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
									'ket' => 'Biaya Pengawas(Kerani) Pemeliharaan Premi Blok: ' . $value['nama_blok'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
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
									'acc_akun_id' =>  $inter_akun_id, //akun biaya Panen,
									'debet' => $premi_kerani_proporsi,
									'kredit' => 0,
									'ket' => 'Biaya Pengawas(Kerani) Pemeliharaan Premi Blok: ' . $value['nama_blok'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL, // $value['blok_id'],
									'kegiatan_id' => NULL, // $value['acc_kegiatan_id'], //kegiatan panen,
									'kendaraan_mesin_id' => NULL,
									'karyawan_id' => NULL, //$value['kerani_id'], //karyawan,
									'umur_tanam_blok' => NULL, // $value['umur_tanam_blok'],
									'tipe' => 'premi_pengawas',
									'hk' => 0
								);
								// $total_pendapatan =	$total_pendapatan + $value['total_pendapatan'];
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
								// Data KREDIT
								$dataKredit = array(
									'lokasi_id' =>  $lokasi_tugas_id_kerani,
									'jurnal_id' => $id_header,
									'acc_akun_id' => $akun_kredit_pengawas_premi, //$value['acc_akun_id'],
									'debet' => 0,
									'kredit' => $premi_kerani_proporsi, // Akun Lawan Biaya
									'ket' => 'Biaya Pengawas(Kerani) Pemeliharaan Premi Blok: ' . $value['nama_blok'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
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
			$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'PEMELIHARAAN');
			$qjurnalTemp = "SELECT b.lokasi_id, a.id,SUM(debet)AS debet ,SUM(kredit)AS kredit ,sum(hk)AS hk,acc_akun_id,blok_stasiun_id,kegiatan_id,
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
				$no_jurnal = $this->autonumber->jurnal_auto($retrieve_header['lokasi_id'], $retrieve_header['tanggal'], 'PML');

				$dataH = array(
					'no_jurnal' => $no_jurnal,
					'lokasi_id' => $retrieve_header['lokasi_id'],
					'tanggal' => $retrieve_header['tanggal'],
					'no_ref' => $retrieve_header['no_transaksi'],
					'ref_id' => $retrieve_header['id'],
					'tipe_jurnal' => 'AUTO',
					'modul' => 'PEMELIHARAAN',
					'keterangan' => 'TRANSAKSI PEMELIHARAAN',
					'is_posting' => 1,
				);
				$id_header2 = $this->AccJurnalModel->create_header($dataH);

				foreach ($resJurnalTemp as $key => $JurnalTemp) {
					$ket = '';
					if ($JurnalTemp['blok']) {
						$ket = 'Pemeliharaan (Upah/premi) Blok: ' . $JurnalTemp['blok'];
					} else {
						$ket = 'Pemeliharaan (Upah/premi)';
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
		$res = $this->EstBkmPemeliharaanModel->posting($id, $input);
		if (!empty($res)) {
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function posting_batch_post($lokasi_id = null, $t1 = null, $t2 = null)
	{
		/* GET SETTING AKUN */
		$res_akun_pemeliharaan_upah = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='PEMELIHARAAN_KEBUN_UPAH'")->row_array();
		if (empty($res_akun_pemeliharaan_upah)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$res_akun_pemeliharaan_premi = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='PEMELIHARAAN_KEBUN_PREMI'")->row_array();
		if (empty($res_akun_pemeliharaan_premi)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$res_akun_pemeliharaan_upah_pengawas = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='PEMELIHARAAN_KEBUN_UPAH_PENGAWAS'")->row_array();
		if (empty($res_akun_pemeliharaan_upah_pengawas)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$res_akun_pemeliharaan_premi_pengawas = $this->db->query("SELECT * from acc_auto_jurnal 
		where kode='PEMELIHARAAN_KEBUN_PREMI_PENGAWAS'")->row_array();
		if (empty($res_akun_pemeliharaan_premi_pengawas)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		//$akun_debet_premi = $res_akun_pemeliharaan_premi['acc_akun_id_debet'];
		$akun_kredit_premi = $res_akun_pemeliharaan_premi['acc_akun_id'];
		//$akun_debet_upah = $res_akun_pemeliharaan_upah['acc_akun_id_debet'];
		$akun_kredit_upah = $res_akun_pemeliharaan_upah['acc_akun_id'];
		$akun_debet_pengawas_premi = $res_akun_pemeliharaan_premi_pengawas['acc_akun_id_debet'];
		$akun_kredit_pengawas_premi = $res_akun_pemeliharaan_premi_pengawas['acc_akun_id_kredit'];
		$akun_debet_pengawas_upah = $res_akun_pemeliharaan_upah_pengawas['acc_akun_id_debet'];
		$akun_kredit_pengawas_upah = $res_akun_pemeliharaan_upah_pengawas['acc_akun_id_kredit'];


		$retrieve_inter_akun = $this->db->query("SELECT * FROM acc_inter_unit
		where tipe='INTER' ")->result_array();
		$akun_inter = array();
		foreach ($retrieve_inter_akun as $key => $akun) {
			$akun_inter[$akun['lokasi_id']][$akun['lokasi_id_2']] = $akun['acc_akun_id'];
		}
		/* === END GET SETTING AKUN ==*/

		$res_transaksi_pemeliharaan = $this->db->query("SELECT * from est_bkm_pemeliharaan_ht where  
		tanggal between '" . $t1 . "' and '" . $t2 . "' and is_posting=0 
		and lokasi_id=" . $lokasi_id . " order by tanggal")->result_array();
		$id = null;
		$jum = 0;
		foreach ($res_transaksi_pemeliharaan  as $key => $retrieve_header) {
			$jum++;
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
			// $retrieve_header = $this->EstBkmPemeliharaanModel->retrieve_by_id($id);

			/* MATIKAN DULU UTK POSTING ITEM */
			// $data_transaksi = $this->db->query("SELECT a.id,a.no_transaksi,a.tanggal,b.item_id,b.qty,b.total,b.blok_id,b.kegiatan_id,
			// d.kode as kode_barang,d.nama as nama_barang,f.statusblok as umur_tanam_blok,f.tahuntanam,
			// c.kode as kode_blok,c.nama as nama_blok,g.acc_akun_id ,g.acc_akun_id ,h.acc_akun_id AS acc_akun_biaya_id
			// FROM est_bkm_pemeliharaan_ht a inner join est_bkm_pemeliharaan_item b on a.id=b.bkm_pemeliharaan_id  
			// left join gbm_organisasi c on b.blok_id=c.id 
			// left join gbm_blok f on b.blok_id=f.organisasi_id
			// left join inv_item d on b.item_id=d.id
			// left join inv_kategori g on d.inv_kategori_id=g.id
			// left JOIN acc_kegiatan h ON b.kegiatan_id=h.id
			// where b.bkm_pemeliharaan_id=" . $id . ";")->result_array();

			// if ($data_transaksi) {
			// 	/* Cari gudang virtual utk afdeling */
			// 	$gudang_id = null;
			// 	$retrieve_gudang = $this->db->query("SELECT * from gbm_organisasi 
			// where afdeling_id=" . $retrieve_header['rayon_afdeling_id']  . " and tipe='GUDANG_VIRTUAL'")->row_array();
			// 	if (empty($retrieve_gudang)) {
			// 		$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Gudang Virtual"), REST_Controller::HTTP_NOT_FOUND);
			// 		return;
			// 	} else {
			// 		$gudang_id = $retrieve_gudang['id'];
			// 	}
			// }
			// // cek stok
			// $ada_stok_minus = false;
			// $result_stok = array();
			// foreach ($data_transaksi as $key => $value) {
			// 	$stok = $this->InvItemModel->getStok($value['item_id'], $gudang_id);
			// 	$cek = $stok - $value['qty'];
			// 	if ($cek < 0) {
			// 		$ada_stok_minus = true;
			// 		$item = array('kode' => $value['kode_barang'], 'nama' => $value['nama_barang'], 'stok' => $cek);
			// 		$result_stok[] = $item;
			// 	}
			// }
			// if ($ada_stok_minus) {
			// 	$this->set_response(array("status" => "NOT OK", "data" => $result_stok), REST_Controller::HTTP_OK);
			// 	continue;
			// }

			// // hapus  transaksi harian
			// $this->db->where('ref_id', $retrieve_header['id']);
			// $this->db->where('tipe', 'PEMAKAIAN_BKM');
			// $this->db->delete('inv_transaksi_harian');

			// $this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'INV_PEMAKAIAN_BARANG_BKM');

			// // Data HEADER BIAYA BAHAN/ITEM
			// $this->load->library('Autonumber');
			// $no_jurnal = $this->autonumber->jurnal_auto($retrieve_header['lokasi_id'], $retrieve_header['tanggal'], 'INVBKM');
			// $dataH = array(
			// 	'no_jurnal' => $no_jurnal,
			// 	'lokasi_id' => $retrieve_header['lokasi_id'],
			// 	'tanggal' => $retrieve_header['tanggal'],
			// 	'no_ref' => $retrieve_header['no_transaksi'],
			// 	'ref_id' => $retrieve_header['id'],
			// 	'tipe_jurnal' => 'AUTO',
			// 	'modul' => 'INV_PEMAKAIAN_BARANG_BKM',
			// 	'keterangan' => 'INV_PEMAKAIAN_BARANG_BKM',
			// 	'is_posting' => 1
			// );
			// $id_header = $this->AccJurnalModel->create_header($dataH);
			// foreach ($data_transaksi as $key => $value) {
			// 	$stok_akhir = $this->db->query("SELECT  SUM(qty_masuk-qty_keluar)AS qty,SUM(nilai_masuk-nilai_keluar)AS nilai from inv_transaksi_harian
			// 		where	item_id=" . $value['item_id'] . " and gudang_id=" . $gudang_id . " ")->row_array();
			// 	$stok_akhir_qty = 	$stok_akhir['qty'] = (!empty($stok_akhir['qty'])) ? $stok_akhir['qty'] : 0;
			// 	$stok_akhir_nilai = 	$stok_akhir['nilai'] = (!empty($stok_akhir['nilai'])) ? $stok_akhir['nilai'] : 0;

			// 	$item_dt = $this->db->query("select * from inv_item_dt  where gudang_id=" . $gudang_id . "
			//   and item_id=" . $value['item_id'] . "")->row_array();
			// 	$avg_price = 0;
			// 	if ($item_dt) {
			// 		$avg_price =	$stok_akhir_nilai / $stok_akhir_qty; //$item_dt['nilai'] / $item_dt['qty'];
			// 		$this->db->where('item_id', $value['item_id']);
			// 		$this->db->where('gudang_id', $gudang_id);
			// 		$this->db->update("inv_item_dt", array(
			// 			'item_id' => $value['item_id'],
			// 			'gudang_id' => $gudang_id,
			// 			'qty' => $stok_akhir_qty - $value['qty'], //$item_dt['qty'] - $value['qty'],
			// 			'nilai' =>	$stok_akhir_nilai - ($value['qty'] * $avg_price) // $item_dt['nilai'] - ($value['qty'] * $avg_price)
			// 		));
			// 	} else {
			// 		$this->db->insert("inv_item_dt", array(
			// 			'item_id' => $value['item_id'],
			// 			'gudang_id' => $gudang_id,
			// 			'qty' => $stok_akhir_qty - $value['qty'], //$item_dt['qty'] - $value['qty'],
			// 			'nilai' =>	$stok_akhir_nilai - ($value['qty'] * $avg_price) // $item_dt['nilai'] - ($value['qty'] * $avg_price)
			// 		));
			// 	}
			// 	$nilai = ($value['qty'] * $avg_price);
			// 	// Biaya bahan Kredit
			// 	$dataCr = array(
			// 		'lokasi_id' => $retrieve_header['lokasi_id'],
			// 		'jurnal_id' => $id_header,
			// 		'acc_akun_id' => ($value['acc_akun_id']), //akun 
			// 		'debet' => 0,
			// 		'kredit' => $nilai,
			// 		'ket' => 'Pemakaian BKM Item:' . $value['nama_barang'] . ', qty:' . $value['qty'] . ' ' . $value['uom'],
			// 		'no_referensi' => $retrieve_header['no_transaksi'],
			// 		'referensi_id' => NULL,
			// 		'blok_stasiun_id' => NULL,
			// 		'kegiatan_id' => NULL, // ,
			// 		'kendaraan_mesin_id' => NULL,

			// 	);

			// 	$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataCr);
			// 	// Biaya bahan Debet
			// 	$dataDr = array(
			// 		'lokasi_id' => $retrieve_header['lokasi_id'],
			// 		'jurnal_id' => $id_header,
			// 		'acc_akun_id' => $value['acc_akun_biaya_id'], //akun ,
			// 		'debet' =>  $nilai,
			// 		'kredit' => 0,
			// 		'ket' => 'Pemakaian BKM Item:' . $value['nama_barang'] . ', qty:' . $value['qty'] . ' ' . $value['uom'],
			// 		'no_referensi' => $retrieve_header['no_transaksi'],
			// 		'referensi_id' => NULL,
			// 		'kegiatan_id' => $value['kegiatan_id'],
			// 		'kendaraan_mesin_id' => NULL,
			// 		'item_id' => $value['item_id'], //item,
			// 		'umur_tanam_blok' => $value['umur_tanam_blok'],
			// 		'blok_stasiun_id' =>  $value['blok_id'],
			// 	);
			// 	$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataDr);
			// 	$this->db->insert("inv_transaksi_harian", array(
			// 		'ref_id' => $id,
			// 		'item_id' => $value['item_id'],
			// 		'gudang_id' => $gudang_id,
			// 		'no_transaksi' => $value['no_transaksi'],
			// 		'tipe ' => 'PEMAKAIAN_BKM',
			// 		'tanggal' => $value['tanggal'],
			// 		'tanggal_proses' => date('Y-m-d H:i:s'),
			// 		'qty_masuk' => 0,
			// 		'qty_keluar' =>  $value['qty'],
			// 		'nilai_masuk' => 0,
			// 		'nilai_keluar' => $avg_price * $value['qty'],
			// 		'blok_stasiun_id' =>  $value['blok_id'],
			// 		'kendaraan_id' => NULL,
			// 		'kegiatan_id' =>  $value['kegiatan_id'],
			// 	));
			// }
			// // ==End Pemkaian bahan=== //

			// Hapus jurnal jika sdh ada berdasarkan no refernsi dan Modul  //
			$this->AccJurnalUpahModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'PEMELIHARAAN');
			$id_header = 0;
			/* JIka bukan premi kontanan  maka jurnal*/
			if (!$retrieve_header['is_premi_kontanan'] || $retrieve_header['is_premi_kontanan'] == '0') {
				/* mulai Jurnal Upah */
				$retrieve_detail = $this->db->query("SELECT a.*,b.*,c.nip,c.nama as nama_karyawan, d.kode as kode_blok,d.nama as nama_blok,
				e.kode as kode_kegiatan,e.nama as nama_kegiatan,e.acc_akun_id, b.acc_kegiatan_id,
				f.statusblok as umur_tanam_blok,f.tahuntanam,c.lokasi_tugas_id
				FROM est_bkm_pemeliharaan_ht a 
				inner join est_bkm_pemeliharaan_dt b on a.id=b.bkm_pemeliharaan_id 
				inner join karyawan c on b.karyawan_id=c.id 
				inner join gbm_organisasi d on b.blok_id=d.id 
				inner join gbm_blok f on b.blok_id=f.organisasi_id
				left join acc_kegiatan e on b.acc_kegiatan_id =e.id  
				where b.bkm_pemeliharaan_id=" . $id . "")->result_array();

				$total_pendapatan = 0;
				$upah_mandor_proporsi = 0;
				$premi_mandor_proporsi = 0;
				$upah_kerani_proporsi = 0;
				$premi_kerani_proporsi = 0;
				// $upah_mandor = $retrieve_header['rp_hk_mandor'];
				// $premi_mandor = $retrieve_header['premi_mandor'];
				// $upah_kerani = $retrieve_header['rp_hk_kerani'];
				// $premi_kerani = $retrieve_header['premi_kerani'];
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

				$total_pendapatan = 0;
				// Data HEADER
				$this->load->library('Autonumber');
				$no_jurnal = $this->autonumber->jurnal_upah_auto($retrieve_header['lokasi_id'], $retrieve_header['tanggal'], 'PML');
				$dataH = array(
					'no_jurnal' => $no_jurnal,
					'lokasi_id' => $retrieve_header['lokasi_id'],
					'tanggal' => $retrieve_header['tanggal'],
					'no_ref' => $retrieve_header['no_transaksi'],
					'ref_id' => $retrieve_header['id'],
					'tipe_jurnal' => 'AUTO',
					'modul' => 'PEMELIHARAAN',
					'keterangan' => 'TRANSAKSI PEMELIHARAAN',
					'is_posting' => 1,
				);
				$id_header = $this->AccJurnalUpahModel->create_header($dataH);
				$total_pendapatan = 0;
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
									'ket' => 'Biaya Pengawas(Mandor) Pemeliharaan  , Karyawan: ' . $retrieve_header['nama_karyawan'],
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
								$total_pendapatan =	$total_pendapatan + $retrieve_header['total_pendapatan'];
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
								// Data KREDIT
								$dataKredit = array(
									'lokasi_id' => $retrieve_header['lokasi_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $akun_kredit_transit_upah, //$value['acc_akun_id'],
									'debet' => 0,
									'kredit' => $upah_mandor, // Akun Lawan Biaya
									'ket' => 'Biaya Pengawas(Mandor) Pemeliharaan Upah,Karyawan: ' . $retrieve_header['nama_karyawan'] . '',
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL,
									'kegiatan_id' => NULL, // $value['kegiatan_id'],
									'kendaraan_mesin_id' => NULL,
									'tipe' => 'upah_pengawas',
									'hk' => 0
								);
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
							} else { // JIKA lokasi karyawan dan lokasi bekerja berbeda
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
									'kegiatan_id' => NULL, //kegiatan ,
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
									'ket' => 'Biaya Pengawas(Mandor) Pemeliharaan Premi',
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
								$total_pendapatan =	$total_pendapatan + $retrieve_header['total_pendapatan'];
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
								// Data KREDIT
								$dataKredit = array(
									'lokasi_id' => $retrieve_header['lokasi_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $akun_kredit_transit_premi, //$value['acc_akun_id'],
									'debet' => 0,
									'kredit' => $premi_mandor, // Akun Lawan Biaya
									'ket' => 'Biaya Pengawas(Mandor) Pemeliharaan Premi ',
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
									'ket' => 'Biaya Pengawas(Kerani) Pemeliharaan Upah ',
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
								$total_pendapatan =	$total_pendapatan + $retrieve_header['total_pendapatan'];
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
								// Data KREDIT
								$dataKredit = array(
									'lokasi_id' => $retrieve_header['lokasi_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $akun_kredit_transit_upah, //$value['acc_akun_id'],
									'debet' => 0,
									'kredit' => $upah_kerani, // Akun Lawan Biaya
									'ket' => 'Biaya Pengawas(Kerani) Pemeliharaan Upah ',
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL,
									'kegiatan_id' => NULL, // $value['kegiatan_id'],
									'kendaraan_mesin_id' => NULL,
									'tipe' => 'upah_pengawas',
									'hk' => 0
								);
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
							} else { // JIka beda lokasi
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
									'ket' => 'Biaya Pengawas(Kerani) Pemeliharaan Premi ',
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
								$total_pendapatan =	$total_pendapatan + $retrieve_header['total_pendapatan'];
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
								// Data KREDIT
								$dataKredit = array(
									'lokasi_id' => $retrieve_header['lokasi_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $akun_kredit_transit_upah, //$value['acc_akun_id'],
									'debet' => 0,
									'kredit' => $premi_kerani, // Akun Lawan Biaya
									'ket' => 'Biaya Pengawas(Kerani) Pemeliharaan Premi ',
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL,
									'kegiatan_id' => NULL, // $value['kegiatan_id'],
									'kendaraan_mesin_id' => NULL,
									'tipe' => 'premi_pengawas',
									'hk' => 0
								);
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
							} else { // JIKA  BEDA LOKASI
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
				} else {
					foreach ($retrieve_detail as $key => $value) {
						$inter_akun_id = null;
						if ($akun_inter[$retrieve_header['lokasi_id']][$value['lokasi_tugas_id']]) {
							$inter_akun_id = $akun_inter[$retrieve_header['lokasi_id']][$value['lokasi_tugas_id']];
						}
						if ($akun_inter[$value['lokasi_tugas_id']][$retrieve_header['lokasi_id']]) {
							$inter_akun_id = $akun_inter[$value['lokasi_tugas_id']][$retrieve_header['lokasi_id']];
						}
						$upah = $value['rupiah_hk'];
						$premi = $value['premi'];
						/* karyawan upkeep */
						if ($upah > 0) {
							if ($value['lokasi_tugas_id'] == $retrieve_header['lokasi_id']) { // JIKA lokasi karyawan dan lokasi bekerja sama
								$dataDebet = array(
									'lokasi_id' => $retrieve_header['lokasi_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $value['acc_akun_id'], //akun biaya pemeliharaan,
									'debet' => ($upah),
									'kredit' => 0,
									'ket' => 'Biaya Pemeliharaan upah Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => $value['blok_id'],
									'kegiatan_id' => $value['acc_kegiatan_id'], //kegiatan pemeliharaan,
									'kendaraan_mesin_id' => NULL,
									'karyawan_id' => $value['karyawan_id'], //karyawan,
									'umur_tanam_blok' => $value['umur_tanam_blok'],
									'tipe' => 'upah',
									'hk' => $value['jumlah_hk']
								);
								$total_pendapatan =	$total_pendapatan + ($value['rupiah_hk'] + $value['premi']);
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
								// Data KREDIT
								$dataKredit = array(
									'lokasi_id' => $retrieve_header['lokasi_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $akun_kredit_upah, //$value['acc_akun_id'],
									'debet' => 0,
									'kredit' => $upah, // Akun Lawan Biaya
									'ket' => 'Biaya Pemeliharaan upah Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL,
									'kegiatan_id' => NULL, // $value['kegiatan_id'],
									'kendaraan_mesin_id' => NULL,
									'tipe' => 'upah',
									'hk' => 0
								);
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
							} else { // JIka beda lokasi
								$dataDebet = array(
									'lokasi_id' => $retrieve_header['lokasi_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $value['acc_akun_id'], //akun biaya pemeliharaan,
									'debet' => ($upah),
									'kredit' => 0,
									'ket' => 'Biaya Pemeliharaan upah Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => $value['blok_id'],
									'kegiatan_id' => $value['acc_kegiatan_id'], //kegiatan pemeliharaan,
									'kendaraan_mesin_id' => NULL,
									'karyawan_id' => $value['karyawan_id'], //karyawan,
									'umur_tanam_blok' => $value['umur_tanam_blok'],
									'tipe' => 'upah',
									'hk' => $value['jumlah_hk']
								);
								$total_pendapatan =	$total_pendapatan + ($value['rupiah_hk'] + $value['premi']);
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
								// Data KREDIT
								$dataKredit = array(
									'lokasi_id' => $retrieve_header['lokasi_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $inter_akun_id, //$value['acc_akun_id'],
									'debet' => 0,
									'kredit' => $upah, // Akun Lawan Biaya
									'ket' => 'Biaya Pemeliharaan upah Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
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
									'acc_akun_id' => $inter_akun_id, //akun biaya pemeliharaan,
									'debet' => ($upah),
									'kredit' => 0,
									'ket' => 'Biaya Pemeliharaan upah Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL, // $value['blok_id'],
									'kegiatan_id' => NULL, //$value['acc_kegiatan_id'], //kegiatan pemeliharaan,
									'kendaraan_mesin_id' => NULL,
									'karyawan_id' => NULL, // $value['karyawan_id'], //karyawan,
									'umur_tanam_blok' => NULL, // $value['umur_tanam_blok'],
									'tipe' => 'upah',
									'hk' => $value['jumlah_hk']
								);
								// $total_pendapatan =	$total_pendapatan + ($value['rupiah_hk'] + $value['premi']);
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
								// Data KREDIT
								$dataKredit = array(
									'lokasi_id' => $value['lokasi_tugas_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $akun_kredit_upah, //$value['acc_akun_id'],
									'debet' => 0,
									'kredit' => $upah, // Akun Lawan Biaya
									'ket' => 'Biaya Pemeliharaan upah Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
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
									'acc_akun_id' => $value['acc_akun_id'], //akun biaya pemeliharaan,
									'debet' => ($premi),
									'kredit' => 0,
									'ket' => 'Biaya Pemeliharaan Premi Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => $value['blok_id'],
									'kegiatan_id' => $value['acc_kegiatan_id'], //kegiatan pemeliharaan,
									'kendaraan_mesin_id' => NULL,
									'karyawan_id' => $value['karyawan_id'], //karyawan,
									'umur_tanam_blok' => $value['umur_tanam_blok'],
									'tipe' => 'premi',
									'hk' => 0
								);
								$total_pendapatan =	$total_pendapatan + ($value['rupiah_hk'] + $value['premi']);
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
								// Data KREDIT
								$dataKredit = array(
									'lokasi_id' => $retrieve_header['lokasi_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $akun_kredit_premi, //$value['acc_akun_id'],
									'debet' => 0,
									'kredit' => $premi, // Akun Lawan Biaya
									'ket' => 'Biaya Pemeliharaan Premi Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL,
									'kegiatan_id' => NULL, // $value['kegiatan_id'],
									'kendaraan_mesin_id' => NULL,
									'tipe' => 'premi',
									'hk' => 0
								);
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
							} else { // JIKA BEDA LOKASI
								$dataDebet = array(
									'lokasi_id' => $retrieve_header['lokasi_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $value['acc_akun_id'], //akun biaya pemeliharaan,
									'debet' => ($premi),
									'kredit' => 0,
									'ket' => 'Biaya Pemeliharaan Premi Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => $value['blok_id'],
									'kegiatan_id' => $value['acc_kegiatan_id'], //kegiatan pemeliharaan,
									'kendaraan_mesin_id' => NULL,
									'karyawan_id' => $value['karyawan_id'], //karyawan,
									'umur_tanam_blok' => $value['umur_tanam_blok'],
									'tipe' => 'premi',
									'hk' => 0
								);
								$total_pendapatan =	$total_pendapatan + ($value['rupiah_hk'] + $value['premi']);
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
								// Data KREDIT
								$dataKredit = array(
									'lokasi_id' => $retrieve_header['lokasi_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $inter_akun_id, //$value['acc_akun_id'],
									'debet' => 0,
									'kredit' => $premi, // Akun Lawan Biaya
									'ket' => 'Biaya Pemeliharaan Premi Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
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
									'lokasi_id' =>  $value['lokasi_tugas_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $inter_akun_id, //akun biaya pemeliharaan,
									'debet' => ($premi),
									'kredit' => 0,
									'ket' => 'Biaya Pemeliharaan Premi Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
									'no_referensi' => $retrieve_header['no_transaksi'],
									'referensi_id' => NULL,
									'blok_stasiun_id' => NULL, // $value['blok_id'],
									'kegiatan_id' => NULL, // $value['acc_kegiatan_id'], //kegiatan pemeliharaan,
									'kendaraan_mesin_id' => NULL,
									'karyawan_id' => NULL, // $value['karyawan_id'], //karyawan,
									'umur_tanam_blok' => NULL, //$value['umur_tanam_blok'],
									'tipe' => 'premi',
									'hk' => 0
								);
								$total_pendapatan =	$total_pendapatan + ($value['rupiah_hk'] + $value['premi']);
								$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
								// Data KREDIT
								$dataKredit = array(
									'lokasi_id' => $value['lokasi_tugas_id'],
									'jurnal_id' => $id_header,
									'acc_akun_id' => $akun_kredit_premi, //$value['acc_akun_id'],
									'debet' => 0,
									'kredit' => $premi, // Akun Lawan Biaya
									'ket' => 'Biaya Pemeliharaan Premi Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
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
						/* END KARYAWAN UPKEEP */

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
										'ket' => 'Biaya Pengawas(Mandor) Pemeliharaan Upah Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
										'no_referensi' => $retrieve_header['no_transaksi'],
										'referensi_id' => NULL,
										'blok_stasiun_id' => $value['blok_id'],
										'kegiatan_id' => $value['acc_kegiatan_id'], //kegiatan panen,
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
										'ket' => 'Biaya Pengawas(Mandor) Pemeliharaan Upah Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
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
										'ket' => 'Biaya Pengawas(Mandor) Pemeliharaan Upah Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
										'no_referensi' => $retrieve_header['no_transaksi'],
										'referensi_id' => NULL,
										'blok_stasiun_id' => $value['blok_id'],
										'kegiatan_id' => $value['acc_kegiatan_id'], //kegiatan panen,
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
										'ket' => 'Biaya Pengawas(Mandor) Pemeliharaan Upah Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
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
										'ket' => 'Biaya Pengawas(Mandor) Pemeliharaan Upah Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
										'no_referensi' => $retrieve_header['no_transaksi'],
										'referensi_id' => NULL,
										'blok_stasiun_id' => NULL, // $value['blok_id'],
										'kegiatan_id' => NULL, // $value['acc_kegiatan_id'], //kegiatan panen,
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
										'ket' => 'Biaya Pengawas(Mandor) Pemeliharaan Upah Blok: ' . $value['nama_blok'] . ' , Karyawan: ' . $value['nama_karyawan'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
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
										'ket' => 'Biaya Pengawas(Mandor) Pemeliharaan Premi Blok: ' . $value['nama_blok'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
										'no_referensi' => $retrieve_header['no_transaksi'],
										'referensi_id' => NULL,
										'blok_stasiun_id' => $value['blok_id'],
										'kegiatan_id' => $value['acc_kegiatan_id'], //kegiatan panen,
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
										'ket' => 'Biaya Pengawas(Mandor) Pemeliharaan Premi Blok: ' . $value['nama_blok'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
										'no_referensi' => $retrieve_header['no_transaksi'],
										'referensi_id' => NULL,
										'blok_stasiun_id' => NULL,
										'kegiatan_id' => NULL, // $value['kegiatan_id'],
										'kendaraan_mesin_id' => NULL,
										'tipe' => 'premi_pengawas',
										'hk' => 0
									);
									$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
								} else { // JIKA beda lokasi
									$dataDebet = array(
										'lokasi_id' => $retrieve_header['lokasi_id'],
										'jurnal_id' => $id_header,
										'acc_akun_id' => $akun_debet_pengawas_premi, //akun biaya Panen,
										'debet' => $premi_mandor_proporsi,
										'kredit' => 0,
										'ket' => 'Biaya Pengawas(Mandor) Pemeliharaan Premi Blok: ' . $value['nama_blok'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
										'no_referensi' => $retrieve_header['no_transaksi'],
										'referensi_id' => NULL,
										'blok_stasiun_id' => $value['blok_id'],
										'kegiatan_id' => $value['acc_kegiatan_id'], //kegiatan panen,
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
										'ket' => 'Biaya Pengawas(Mandor) Pemeliharaan Premi Blok: ' . $value['nama_blok'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
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
										'lokasi_id' =>  $lokasi_tugas_id_mandor,
										'jurnal_id' => $id_header,
										'acc_akun_id' => $inter_akun_id, //akun biaya Panen,
										'debet' => $premi_mandor_proporsi,
										'kredit' => 0,
										'ket' => 'Biaya Pengawas(Mandor) Pemeliharaan Premi Blok: ' . $value['nama_blok'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
										'no_referensi' => $retrieve_header['no_transaksi'],
										'referensi_id' => NULL,
										'blok_stasiun_id' => NULL, // $value['blok_id'],
										'kegiatan_id' => NULL, // $value['acc_kegiatan_id'], //kegiatan panen,
										'kendaraan_mesin_id' => NULL,
										'karyawan_id' => NULL, // $value['mandor_id'], //karyawan,
										'umur_tanam_blok' => NULL, //$value['umur_tanam_blok'],
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
										'ket' => 'Biaya Pengawas(Mandor) Pemeliharaan Premi Blok: ' . $value['nama_blok'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
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
										'ket' => 'Biaya Pengawas(Kerani) Pemeliharaan Upah Blok: ' . $value['nama_blok'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
										'no_referensi' => $retrieve_header['no_transaksi'],
										'referensi_id' => NULL,
										'blok_stasiun_id' => $value['blok_id'],
										'kegiatan_id' => $value['acc_kegiatan_id'], //kegiatan panen,
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
										'ket' => 'Biaya Pengawas(Kerani) Pemeliharaan Upah Blok: ' . $value['nama_blok'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
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
										'ket' => 'Biaya Pengawas(Kerani) Pemeliharaan Upah Blok: ' . $value['nama_blok'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
										'no_referensi' => $retrieve_header['no_transaksi'],
										'referensi_id' => NULL,
										'blok_stasiun_id' => $value['blok_id'],
										'kegiatan_id' => $value['acc_kegiatan_id'], //kegiatan panen,
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
										'ket' => 'Biaya Pengawas(Kerani) Pemeliharaan Upah Blok: ' . $value['nama_blok'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
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
										'ket' => 'Biaya Pengawas(Kerani) Pemeliharaan Upah Blok: ' . $value['nama_blok'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
										'no_referensi' => $retrieve_header['no_transaksi'],
										'referensi_id' => NULL,
										'blok_stasiun_id' => NULL, // $value['blok_id'],
										'kegiatan_id' => NULL, // $value['acc_kegiatan_id'], //kegiatan panen,
										'kendaraan_mesin_id' => NULL,
										'karyawan_id' => NULL, // $value['kerani_id'], //karyawan,
										'umur_tanam_blok' => NULL, //$value['umur_tanam_blok'],
										'tipe' => 'upah_pengawas',
										'hk' => 0
									);
									// $total_pendapatan =	$total_pendapatan + $value['total_pendapatan'];
									$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
									// Data KREDIT
									$dataKredit = array(
										'lokasi_id' => $lokasi_tugas_id_kerani,
										'jurnal_id' => $id_header,
										'acc_akun_id' => $akun_kredit_pengawas_upah, //$value['acc_akun_id'],
										'debet' => 0,
										'kredit' => $upah_kerani_proporsi, // Akun Lawan Biaya
										'ket' => 'Biaya Pengawas(Kerani) Pemeliharaan Upah Blok: ' . $value['nama_blok'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
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
										'ket' => 'Biaya Pengawas(Kerani) Pemeliharaan Premi Blok: ' . $value['nama_blok'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
										'no_referensi' => $retrieve_header['no_transaksi'],
										'referensi_id' => NULL,
										'blok_stasiun_id' => $value['blok_id'],
										'kegiatan_id' => $value['acc_kegiatan_id'], //kegiatan panen,
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
										'ket' => 'Biaya Pengawas(Kerani) Pemeliharaan Premi Blok: ' . $value['nama_blok'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
										'no_referensi' => $retrieve_header['no_transaksi'],
										'referensi_id' => NULL,
										'blok_stasiun_id' => NULL,
										'kegiatan_id' => NULL, // $value['kegiatan_id'],
										'kendaraan_mesin_id' => NULL,
										'tipe' => 'premi_pengawas',
										'hk' => 0
									);
									$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataKredit);
								} else { // JIKA lokasi berbeda
									$dataDebet = array(
										'lokasi_id' => $retrieve_header['lokasi_id'],
										'jurnal_id' => $id_header,
										'acc_akun_id' => $akun_debet_pengawas_premi, //akun biaya Panen,
										'debet' => $premi_kerani_proporsi,
										'kredit' => 0,
										'ket' => 'Biaya Pengawas(Kerani) Pemeliharaan Premi Blok: ' . $value['nama_blok'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
										'no_referensi' => $retrieve_header['no_transaksi'],
										'referensi_id' => NULL,
										'blok_stasiun_id' => $value['blok_id'],
										'kegiatan_id' => $value['acc_kegiatan_id'], //kegiatan panen,
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
										'ket' => 'Biaya Pengawas(Kerani) Pemeliharaan Premi Blok: ' . $value['nama_blok'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
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
										'acc_akun_id' =>  $inter_akun_id, //akun biaya Panen,
										'debet' => $premi_kerani_proporsi,
										'kredit' => 0,
										'ket' => 'Biaya Pengawas(Kerani) Pemeliharaan Premi Blok: ' . $value['nama_blok'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
										'no_referensi' => $retrieve_header['no_transaksi'],
										'referensi_id' => NULL,
										'blok_stasiun_id' => NULL, // $value['blok_id'],
										'kegiatan_id' => NULL, // $value['acc_kegiatan_id'], //kegiatan panen,
										'kendaraan_mesin_id' => NULL,
										'karyawan_id' => NULL, //$value['kerani_id'], //karyawan,
										'umur_tanam_blok' => NULL, // $value['umur_tanam_blok'],
										'tipe' => 'premi_pengawas',
										'hk' => 0
									);
									// $total_pendapatan =	$total_pendapatan + $value['total_pendapatan'];
									$id_dtl = $this->AccJurnalUpahModel->create_detail($id_header, $dataDebet);
									// Data KREDIT
									$dataKredit = array(
										'lokasi_id' =>  $lokasi_tugas_id_kerani,
										'jurnal_id' => $id_header,
										'acc_akun_id' => $akun_kredit_pengawas_premi, //$value['acc_akun_id'],
										'debet' => 0,
										'kredit' => $premi_kerani_proporsi, // Akun Lawan Biaya
										'ket' => 'Biaya Pengawas(Kerani) Pemeliharaan Premi Blok: ' . $value['nama_blok'] . ', Kegiatan: ' . $value['nama_kegiatan'] . "",
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
				$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'PEMELIHARAAN');
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
					$no_jurnal = $this->autonumber->jurnal_auto($retrieve_header['lokasi_id'], $retrieve_header['tanggal'], 'PML');

					$dataH = array(
						'no_jurnal' => $no_jurnal,
						'lokasi_id' => $retrieve_header['lokasi_id'],
						'tanggal' => $retrieve_header['tanggal'],
						'no_ref' => $retrieve_header['no_transaksi'],
						'ref_id' => $retrieve_header['id'],
						'tipe_jurnal' => 'AUTO',
						'modul' => 'PEMELIHARAAN',
						'keterangan' => 'TRANSAKSI PEMELIHARAAN',
						'is_posting' => 1,
					);
					$id_header2 = $this->AccJurnalModel->create_header($dataH);

					foreach ($resJurnalTemp as $key => $JurnalTemp) {
						$ket = '';
						if ($JurnalTemp['blok']) {
							$ket = 'Pemeliharaan (Upah/premi) Blok: ' . $JurnalTemp['blok'];
						} else {
							$ket = 'Pemeliharaan (Upah/premi)';
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
			$res = $this->EstBkmPemeliharaanModel->posting($id, $input);
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
	function hitung_premi($input)
	{
		$resGaji = $this->db->query("select * from payroll_karyawan_gaji where karyawan_id=" . $input['karyawan_id'] . " ")->row_array();
		$upahharian = ($resGaji['gapok'] / 25);
		$retrieve_kegiatan = $this->AccKegiatanModel->retrieve($input['kegiatan_id']);
		$hasil_kerja = $input['hasil_kerja'];
		$basis = $retrieve_kegiatan['basis'];
		$premi_basis = $retrieve_kegiatan['premi_basis'];
		$premi_lebih_basis = $retrieve_kegiatan['premi_lebih_basis'];
		$hk = 1;
		$hasil_premi_basis = 0;
		$hasil_premi_lebih_basis = 0;
		if ($hasil_kerja < $basis) {
			$hk = $hasil_kerja / $basis;
		} else {
			$hasil_premi_basis = $premi_basis;
			$hasil_premi_lebih_basis = ($hasil_kerja - $basis) *	$premi_lebih_basis;
		}
		$upah = $upahharian * $hk;
		$res = array(
			'hk' => $hk,
			'rp_hk' => $upah,
			'premi' => $hasil_premi_basis + $hasil_premi_lebih_basis,
			'kegiatan' => $retrieve_kegiatan

		);

		if (!empty($res)) {
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function laporan_pemeliharaan_detail_post()
	{

		error_reporting(0);
		$estate_id     = $this->post('estate_id', true);
		$afdeling_id     = $this->post('afdeling_id', true);
		$tgl_mulai =  $this->post('tgl_mulai', true);
		$tgl_akhir = $this->post('tgl_akhir', true);
		$format_laporan     = $this->post('format_laporan', true);
		$retrieveEstate = $this->db->query("select * from gbm_organisasi where id=" . $estate_id . "")->row_array();
		$nama_afdeling = "Semua";
		if ($afdeling_id) {
			$retrieveAfdeling = $this->db->query("select * from gbm_organisasi where id=" . $afdeling_id . "")->row_array();
			$nama_afdeling = $retrieveAfdeling['nama'];
		}
		$qry = "select * from est_bkm_pemeliharaan_vw 
		where id_estate=" . $estate_id . " and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
		";

		if ($afdeling_id) {
			$qry = $qry . " and kode_afdeling= '" . $retrieveAfdeling['kode'] . "'";
		}
		$qry = $qry . " order by tanggal,nama_afdeling,nama_blok";
		$retrievepemeliharaan = $this->db->query($qry)->result_array();

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
  <h3 class="title">LAPORAN RINCIAN PEMELIHARAAN</h3>
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

		$html = $html . ' <table  border="1" width="100%" style="border-collapse: collapse;">
			<thead>
				<tr>
				<th width="4%">No.</th>	
				<th>Tanggal</th>		
				<th>No Transaksi</th>
				<th>Afdeling</th>
				<th>Blok</th>
				<th>Tahun Tanam</th>
				<th>Inti/Plasma</th>
				<th>Kode kegiatan</th>
				<th>Nama Kegiatan</th>
				<th>Satuan</th>
				<th>Karyawan</th>
				<th>ket</th>
				<th style="text-align: center;">Hasil Kerja </th>
				<th style="text-align: center;">Hk </th>
				<th style="text-align: center;">Upah (RP)</th>
				<th style="text-align: center;">Premi (RP)</th>
				<th style="text-align: center;">Denda (RP)</th>
				<th style="text-align: center;">Jumlah (RP) </th>
				
				</tr>
			</thead>
			<tbody>';


		$no = 0;

		$hasil_kerja = 0;
		$jum_upah = 0;
		$jum_premi = 0;
		$jumlah_rp = 0;
		$jumlah_denda = 0;
		$total_hk = 0;


		foreach ($retrievepemeliharaan as $key => $m) {
			$no++;
			$jum_upah = $jum_upah + $m['rupiah_hk'];
			$jum_premi = $jum_premi + $m['premi'];
			$jumlah_denda = $jumlah_denda + $m['denda_pemeliharaan'];
			$jumlah_rp = $jumlah_rp + (($m['rupiah_hk'] + $m['premi']) - $m['denda_pemeliharaan']);
			$total_hk = $total_hk + $m['jumlah_hk'];
			$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative; text-align: center;">	' . ($no) . '</td>
						<td >' . tgl_indo_normal($m['tanggal']) . ' </td>
						<td>' . $m['no_transaksi'] . ' </td>
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
						<td style="text-align: center;">
						' . $m['kode_kegiatan'] . ' 
						</td>
						<td>
						' . $m['nama_kegiatan'] . ' 
						</td>
						<td style="text-align: center;">
						' . $m['uom'] . ' 
						</td>
						<td>
						' . $m['nama_karyawan'] . ' - ' . $m['nip_karyawan'] . ' 
						</td>
						<td>
						' . $m['keterangan'] . ' 
						</td>
						<td style="text-align: right;">' . number_format($m['hasil_kerja'], 2) . ' 
						<td style="text-align: right;">' . number_format($m['jumlah_hk'], 2) . ' 
						<td style="text-align: right;">' . number_format($m['rupiah_hk'], 2) . ' 
						<td style="text-align: right;">' . number_format($m['premi'], 2) . ' 
						<td style="text-align: right;">' . number_format($m['denda_pemeliharaan'], 2) . ' 
						<td style="text-align: right;">' . number_format((($m['rupiah_hk'] + $m['premi']) - $m['denda_pemeliharaan']), 2) . '
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
						
						</td>
						<td style="text-align: right;">
						' . number_format($total_hk) . ' 
						</td>
						<td style="text-align: right;">
						' . number_format($jum_upah) . ' 
						</td>
						<td style="text-align: right;">
						' . number_format($jum_premi) . ' 
						</td>
						<td style="text-align: right;">
						' . number_format($jumlah_denda) . ' 
						</td>
						<td style="text-align: right;">
						' . number_format($jumlah_rp) . ' 
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
	function laporan_pemeliharaan_perbulan_post()
	{
		error_reporting(0);
		// ini_set('display_errors', 1);
		ini_set('max_execution_time', '300');
		try {
			$kegiatan_id     = $this->post('kegiatan_id', true);
			$afdeling_id     = $this->post('afdeling_id', true);
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
			$nama_kegiatan = "Semua";
			if ($kegiatan_id) {
				$retrieveKegiatan = $this->db->query("select a.*,b.kode as uom from acc_kegiatan a left join gbm_uom b on a.uom_id =b.id
		 		where a.id=" . $kegiatan_id . "")->row_array();
				$nama_kegiatan = $retrieveKegiatan['kode'] . '-' . $retrieveKegiatan['nama'] . ' (' . $retrieveKegiatan['uom'] . ')';
			}
			$nama_afdeling = "Semua";
			if ($afdeling_id) {
				$retrieveAfdeling = $this->db->query("select * from gbm_organisasi where id=" . $afdeling_id . "")->row_array();
				$nama_afdeling = $retrieveAfdeling['nama'];
			}
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
  <h3 class="title">LAPORAN PEMELIHARAAN PER BULAN</h3>
  <table class="no_border" style="width:40%">
			
			<tr>
					<td>Estate</td>
					<td>:</td>
					<td>' .  $retrieveEstate['nama'] . '</td>

					<td>Kegiatan</td>
					<td>:</td>
					<td>' .  $nama_kegiatan . '</td>
			</tr>
			<tr>	<td>Afdeling</td>
					<td>:</td>
					<td>' .  $nama_afdeling . '</td>

					<td>Periode</td>
					<td>:</td>
					<td>' . tgl_indo($tgl_mulai) . ' s/d ' . tgl_indo($tgl_akhir) . '</td>
			</tr>
			
	</table>
			<br>';

			$html = $html . "

<table   border='1' width='100%' style='border-collapse: collapse;'>
<thead>
<tr>
	<th rowspan=3 >No</th>
	<th rowspan=3>Kegiatan</th>
	<th rowspan=3>Afdeling</th>
	<th rowspan=3>Blok</th>
	<th rowspan=3 style='height:100px'>I/P</th>
	<th rowspan=3>Luas</th>
	<th colspan=" . ($jumhari * 5) . "  style='text-align: center'> " . $periode . "  </th>
	<th colspan=5 rowspan=2  style='text-align: center'>TOTAL</td>
</tr>
";

			$html = $html . "<tr>";
			for ($i = 1; $i < ($jumhari + 1); $i++) {
				$html = $html . "<th style='text-align: center' colspan=5>" . $i . "</th>";
			}

			$html = $html . "</tr> ";
			$html = $html . "<tr>";
			for ($i = 1; $i < ($jumhari + 1); $i++) {
				$html = $html . "<th style='text-align: center'>Hasil Kerja</td>";
				$html = $html . "<th style='text-align: center'>HK</th>";
				$html = $html . "<th style='text-align: center'>Upah (RP)</td>";
				$html = $html . "<th style='text-align: center'>Premi (RP)</th>";
				$html = $html . "<th style='text-align: center'>Upah+Premi (RP)</th>";
			}
			$html = $html . "<th style='text-align: center'>Hasil Kerja</th>";
			$html = $html . "<th style='text-align: center'>HK</th>";
			$html = $html . "<th style='text-align: center'>Upah (RP)</th>";
			$html = $html . "<th style='text-align: center'>Premi (RP)</th>";
			$html = $html . "<th style='text-align: center'>Upah+Premi (RP)</th>";
			$html = $html . "</tr> </thead>";
			$nourut = 0;
			$grandtotal_hk = 0;
			$grandtotal_upah = 0;
			$grandtotal_premi = 0;
			$grandtotal_hasil_kerja = 0;
			$totalHasilKejaPerHari = array();
			$totalHasilKejaPerHari = [];
			$totalHkPerHari = array();
			$totalHkPerHari = [];
			$totalUpahPerHari = array();
			$totalUpahPerHari = [];
			$totalPremiPerHari = array();
			$totalPremiPerHari = [];
			// retrive data rayon  

			if ($kegiatan_id) {
				$qry = "SELECT DISTINCT acc_kegiatan_id,kode_kegiatan,nama_kegiatan,blok_id,kode_blok,nama_blok,nama_afdeling,intiplasma,luasareaproduktif FROM est_bkm_pemeliharaan_vw WHERE acc_kegiatan_id=" . $kegiatan_id . " and id_estate=" . $estate_id . " and tanggal>='" . $tgl_mulai . "' 
				and tanggal<='" . $tgl_akhir . "' ";
			} else {
				$qry = "SELECT DISTINCT acc_kegiatan_id,kode_kegiatan,nama_kegiatan,blok_id,kode_blok,nama_blok,nama_afdeling,intiplasma,luasareaproduktif FROM est_bkm_pemeliharaan_vw WHERE  id_estate=" . $estate_id . " and tanggal>='" . $tgl_mulai . "' 
				and tanggal<='" . $tgl_akhir . "' ";
				$retrieveBlok = $this->db->query($qry)->result_array();
			}

			if ($afdeling_id) {
				$qry = $qry . " and kode_afdeling= '" . $retrieveAfdeling['kode'] . "'";
			}
			$qry = $qry . " order by nama_kegiatan,kode_kegiatan, nama_afdeling,nama_blok ";
			$retrieveBlok = $this->db->query($qry)->result_array();
			for ($i = 1; $i < ($jumhari + 1); $i++) {
				$totalPerHari[] = 0;
			}

			foreach ($retrieveBlok as $key => $d) {

				//code...

				$html = $html . "<tr>";
				$nourut = $nourut + 1;
				$html = $html . "<td style='text-align: left'>" . $nourut . "</td>";
				$html = $html . "<td style='text-align: left'>" . $d['nama_kegiatan'] . ' - ' . $d['kode_kegiatan'] . "</td>";
				$html = $html . "<td style='text-align: left'>" . $d['nama_afdeling'] . "</td>";
				$html = $html . "<td style='text-align: left'>" . $d['nama_blok'] . "</td>";
				$html = $html . "<td style='text-align: left'>" . $d['intiplasma'] . "</td>";
				$html = $html . "<td style='text-align: center'>" . $d['luasareaproduktif'] . "</td>";
				$total_hasil_kerja = 0;
				$total_rupiah_hk = 0;
				$total_premi = 0;
				$total_jumlah_hk = 0;
				for ($i = 1; $i < ($jumhari + 1); $i++) {
					$tgl = $periode  . '-' . sprintf("%02d", $i);

					$sql = "SELECT SUM(hasil_kerja)as hasil_kerja,
						SUM(rupiah_hk)as rupiah_hk,SUM(premi)as premi,
						SUM(jumlah_hk)as jumlah_hk
						FROM est_bkm_pemeliharaan_vw WHERE (blok_id =" . $d['blok_id'] . ")
						and id_estate=" . $estate_id . " and tanggal='" . $tgl . "'";
					if ($kegiatan_id) {
						$sql = $sql . " and acc_kegiatan_id=" . $d['acc_kegiatan_id'] . "  ";
					}
					if ($afdeling_id) {
						$sql = $sql . " and kode_afdeling= '" . $retrieveAfdeling['kode'] . "'";
					}
					$retrievepemeliharaan = $this->db->query($sql)->row_array();
					$hasil_kerja = $retrievepemeliharaan['hasil_kerja'] ? $retrievepemeliharaan['hasil_kerja'] : 0;
					$rupiah_hk = $retrievepemeliharaan['rupiah_hk'] ? $retrievepemeliharaan['rupiah_hk'] : 0;
					$premi = $retrievepemeliharaan['premi'] ? $retrievepemeliharaan['premi'] : 0;
					$jumlah_hk = $retrievepemeliharaan['jumlah_hk'] ? $retrievepemeliharaan['jumlah_hk'] : 0;
					$index = "idx" . $i;
					// $jum=0;
					if (array_key_exists($i, $totalHasilKejaPerHari)) {
						$totalHasilKejaPerHari[$i - 1] = $totalHasilKejaPerHari[$i - 1] + $hasil_kerja;
					} else {
						$totalHasilKejaPerHari[] = $hasil_kerja;
					}
					if (array_key_exists($i, $totalHkPerHari)) {
						$totalHkPerHari[$i - 1] = $totalHkPerHari[$i - 1] + $jumlah_hk;
					} else {
						$totalHkPerHari[] = $jumlah_hk;
					}
					if (array_key_exists($i, $totalUpahPerHari)) {
						$totalUpahPerHari[$i - 1] = $totalUpahPerHari[$i - 1] + $rupiah_hk;
					} else {
						$totalUpahPerHari[] = $rupiah_hk;
					}
					if (array_key_exists($i, $totalPremiPerHari)) {
						$totalPremiPerHari[$i - 1] = $totalPremiPerHari[$i - 1] + $premi;
					} else {
						$totalPremiPerHari[] = $premi;
					}

					$total_hasil_kerja = $total_hasil_kerja + $hasil_kerja;
					$total_rupiah_hk = $total_rupiah_hk + $rupiah_hk;
					$total_premi = $total_premi + $premi;
					$total_jumlah_hk = $total_jumlah_hk + $jumlah_hk;
					$grandtotal_hk = $grandtotal_hk + $jumlah_hk;
					$grandtotal_upah = $grandtotal_upah + $rupiah_hk;
					$grandtotal_premi = $grandtotal_premi + $premi;
					$grandtotal_hasil_kerja = $grandtotal_hasil_kerja + $hasil_kerja;
					$html = $html . "<td style='text-align: center'>" . number_format($hasil_kerja, 2) . " </td>";
					$html = $html . "<td style='text-align: center'>" . number_format($jumlah_hk, 2) . " </td>";
					$html = $html . "<td style='text-align: center'>" . number_format($rupiah_hk) . " </td>";
					$html = $html . "<td style='text-align: center'>" . number_format($premi) . " </td>";
					$html = $html . "<td style='text-align: center'>" . number_format($premi + $rupiah_hk) . " </td>";
				}
				$html = $html . "<td style='text-align: center'>" . number_format($total_hasil_kerja, 2) . " </td>";
				$html = $html . "<td style='text-align: center'>" . number_format($total_jumlah_hk, 2) . " </td>";
				$html = $html . "<td style='text-align: center'>" . number_format($total_rupiah_hk) . " </td>";
				$html = $html . "<td style='text-align: center'>" . number_format($total_premi) . " </td>";
				$html = $html . "<td style='text-align: center'>" . number_format($total_premi + $total_rupiah_hk) . " </td>";
				$html = $html . "</tr>";
			}

			$html = $html . "<tr>";
			$html = $html . "<td style='text-align: center'> </td>";
			$html = $html . "<td style='text-align: center'> </td>";
			$html = $html . "<td style='text-align: center'></td>";
			$html = $html . "<td style='text-align: center'></td>";
			$html = $html . "<td style='text-align: center'></td>";
			$html = $html . "<td style='text-align: center'></td>";
			for ($i = 1; $i < ($jumhari + 1); $i++) {

				$html = $html . "<td style='text-align: center'>" . number_format($totalHasilKejaPerHari[$i - 1]) . " </td>";
				$html = $html . "<td style='text-align: center'>" . number_format($totalHkPerHari[$i - 1]) . " </td>";
				$html = $html . "<td style='text-align: center'>" . number_format($totalUpahPerHari[$i - 1]) . " </td>";
				$html = $html . "<td style='text-align: center'>" . number_format($totalPremiPerHari[$i - 1]) . " </td>";
				$html = $html . "<td style='text-align: center'>" . number_format(($totalPremiPerHari[$i - 1]) + ($totalUpahPerHari[$i - 1])) . " </td>";
			}
			$html = $html . "<td style='text-align: right'>" . number_format($grandtotal_hasil_kerja) . " </td>";
			$html = $html . "<td style='text-align: right'>" . number_format($grandtotal_hk) . " </td>";
			$html = $html . "<td style='text-align: right'>" . number_format($grandtotal_upah) . " </td>";
			$html = $html . "<td style='text-align: right'>" . number_format($grandtotal_premi) . " </td>";
			$html = $html . "<td style='text-align: right'>" . number_format($grandtotal_premi + $grandtotal_upah) . " </td>";
			$html = $html . "</tr>";
			$html = $html . "</table>";
		} catch (\Throwable $th) {
			var_dump($th);
			exit();
			//throw $th;
		}


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
	function laporan_pemeliharaan_perkaryawan_post()
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
			$html = get_header_report();
		}
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
	function print_slip_get($segment_3 = '')
	{

		$id = (int)$segment_3;
		$data = [];

		$queryHeader = "SELECT a.*, 
		b.nama as lokasi, 
		c.nama as rayon, 
		d.nama as mandor,
        e.nama as kerani,
        f.nama as asisten
		FROM est_bkm_pemeliharaan_ht a
		INNER JOIN gbm_organisasi b ON a.lokasi_id=b.id
		INNER JOIN gbm_organisasi c ON a.rayon_afdeling_id=c.id
        LEFT JOIN karyawan d ON a.mandor_id=d.id
        LEFT JOIN karyawan e ON a.kerani_id=e.id
        LEFT JOIN karyawan f ON a.asisten_id=f.id WHERE a.id=" . $id . " ";
		$dataHeader = $this->db->query($queryHeader)->row_array();

		$queryDetail = "SELECT a.*,
		b.nama as karyawan,
		c.nama as kegiatan,
		d.nama as blok,
		e.kode as kode_akun,e.nama as nama_akun
		FROM est_bkm_pemeliharaan_dt a 
		LEFT JOIN karyawan b ON a.karyawan_id=b.id
		LEFT JOIN acc_kegiatan c ON a.acc_kegiatan_id=c.id
		LEFT JOIN acc_akun e ON c.acc_akun_id=e.id
		LEFT JOIN gbm_organisasi d ON a.blok_id=d.id WHERE a.bkm_pemeliharaan_id=" . $id . " ";

		$queryDetailItem = "SELECT a.*,
		b.nama as item,
		c.nama as gudang,
		d.nama as blok,
		f.nama as uom,
		e.nama as kegiatan
		FROM est_bkm_pemeliharaan_item a 
		LEFT JOIN inv_item b ON a.item_id=b.id
		LEFT JOIN gbm_organisasi c ON a.gudang_id=c.id
		LEFT JOIN gbm_organisasi d ON a.blok_id=d.id 
		LEFT JOIN acc_kegiatan e ON a.kegiatan_id=e.id
		LEFT JOIN gbm_uom f ON b.uom_id=f.id
		WHERE a.bkm_pemeliharaan_id=" . $id . " ";

		$dataDetail = $this->db->query($queryDetail)->result_array();
		$dataDetailItem = $this->db->query($queryDetailItem)->result_array();

		$data['header'] = 	$dataHeader;
		$data['detail'] = 	$dataDetail;
		$data['detailItem'] = 	$dataDetailItem;

		$html = $this->load->view('EstSlipBkmPemeliharaan', $data, true);

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



	function laporan_bahan_post()
	{
		$format_laporan =  $this->post('format_laporan', true);

		// $id = (int)$segment_3;
		$data = [];

		$input = [
			'no_po' => '01/PO/DPA',
			'lokasi_id' => 252,
			// 'gudang_id' => 740,
			'tgl_mulai' => '2020-01-01',
			'tgl_akhir' => '2022-12-12',
			'format_laporan' => 'view',
		];

		$lokasi_id = $this->post('lokasi_id', true);
		$tanggal_awal = $this->post('tgl_mulai', true);
		$tanggal_akhir = $this->post('tgl_akhir', true);

		// $lokasi_id = $input['lokasi_id'];
		// $format_laporan = $input['format_laporan'];
		// $tanggal_awal = $input['tgl_mulai'];
		// $tanggal_akhir = $input['tgl_akhir'];

		$queryPo = "SELECT
		a.*,
		b.*,
		c.nama AS item,
		cc.nama AS kegiatan,
		ccc.nama AS blok,
		d.nama AS lokasi,
		e.nama AS mandor,
		ee.nama AS kerani,
		a.id AS id,
		f.kode as uom
		FROM est_bkm_pemeliharaan_item a
		
		LEFT JOIN est_bkm_pemeliharaan_ht b on a.bkm_pemeliharaan_id=b.id

		LEFT JOIN inv_item c on a.item_id=c.id
		LEFT JOIN acc_kegiatan cc on a.kegiatan_id=cc.id
		LEFT JOIN gbm_organisasi ccc on a.blok_id=ccc.id

		LEFT JOIN gbm_organisasi d on b.lokasi_id=d.id

		LEFT JOIN karyawan e ON b.mandor_id=e.id
		LEFT JOIN karyawan ee ON b.kerani_id=e.id
        LEFT JOIN gbm_uom f ON c.uom_id=f.id
		where b.tanggal between  '" . $tanggal_awal . "' and  '" . $tanggal_akhir . "'	
		";

		$filter_lokasi = "Semua";
		if ($lokasi_id) {
			$queryPo = $queryPo . " and b.lokasi_id=" . $lokasi_id . "";
			$res = $this->db->query("select * from gbm_organisasi where id=" . $lokasi_id . "")->row_array();
			$filter_lokasi = $res['nama'];
		}
		$dataPo = $this->db->query($queryPo)->result_array();

		// $filter_gudang = "Semua";
		// if ($gudang_id) {
		// 	$queryPo = $queryPo . " and b.gudang_id=" . $gudang_id . "";
		// 	$res = $this->db->query("select * from gbm_organisasi where id=" . $gudang_id . "")->row_array();
		// 	$filter_gudang = $res['nama'];
		// }


		$data['po'] = 	$dataPo;
		// $data['filter_gudang'] = 	$filter_gudang;
		$data['filter_lokasi'] = 	$filter_lokasi;
		$data['filter_tgl_awal'] = 	$tanggal_awal;
		$data['filter_tgl_akhir'] = $tanggal_akhir;
		$data['format_laporan'] = $format_laporan;


		$html = $this->load->view('Est_Bkm_Pemeliharaan_Bahan_Laporan', $data, true);

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

	function rekap_mandor_kerani_pemeliharaan_post()
	{

		$data = [];

		$input = [
			'lokasi_id' => 252,
			'tgl_mulai' => '2022-08-01',
			'tgl_akhir' => '2022-08-01',
			'afdeling_id' => 270,

			// 'format_laporan' => 'view',
		];
		$format_laporan =  $this->post('format_laporan', true);
		$lokasi_id = $this->post('lokasi_id', true);
		$afdeling_id = $this->post('afdeling_id', true);
		$tanggal_awal = $this->post('tgl_mulai', true);
		$tanggal_akhir = $this->post('tgl_akhir', true);


		// $lokasi_id = $input['lokasi_id'];
		// $afdeling_id = $input['afdeling_id'];
		// $tanggal_awal = $input['tgl_mulai'];
		// $tanggal_akhir = $input['tgl_akhir'];

		$queryBkm = "SELECT 
		ket_mandor AS ket,
		SUM(jumlah_hk_mandor) AS hk,
		SUM(rp_hk_mandor) AS rp_hk, 
		SUM(premi_mandor) AS premi FROM est_bkm_pemeliharaan_ht
		where tanggal between  '" . $tanggal_awal . "' and  '" . $tanggal_akhir . "'
		and  mandor_id is not null	
		and (jumlah_hk_mandor>0 or rp_hk_mandor>0 or premi_mandor>0)
		";
		$filter_lokasi = "Semua";
		if ($lokasi_id) {
			$queryBkm = $queryBkm . " and lokasi_id=" . $lokasi_id . "";
			$res = $this->db->query("select * from gbm_organisasi where id=" . $lokasi_id . "")->row_array();
			$filter_lokasi = $res['nama'];
		}
		$filter_afdeling = "Semua";
		if ($afdeling_id) {
			$queryBkm = $queryBkm . " and rayon_afdeling_id=" . $afdeling_id . "";
			$res = $this->db->query("select * from gbm_organisasi where id=" . $afdeling_id . "")->row_array();
			$filter_afdeling = $res['nama'];
		}
		$queryBkm = $queryBkm . " GROUP BY ket_mandor
		UNION ALL
		SELECT ket_kerani AS ket, SUM(jumlah_hk_kerani) AS hk,
		SUM(rp_hk_kerani) AS rp_hk, SUM(premi_kerani) AS premi FROM est_bkm_pemeliharaan_ht 
		where tanggal between  '" . $tanggal_awal . "' and  '" . $tanggal_akhir .  "' 
		and kerani_id is not null
		and (jumlah_hk_kerani>0 or rp_hk_kerani>0 or premi_kerani>0)
		";
		if ($lokasi_id) {
			$queryBkm = $queryBkm . " and lokasi_id=" . $lokasi_id . "";
			$res = $this->db->query("select * from gbm_organisasi where id=" . $lokasi_id . "")->row_array();
			$filter_lokasi = $res['nama'];
		}
		$filter_afdeling = "Semua";
		if ($afdeling_id) {
			$queryBkm = $queryBkm . " and rayon_afdeling_id=" . $afdeling_id . "";
			$res = $this->db->query("select * from gbm_organisasi where id=" . $afdeling_id . "")->row_array();
			$filter_afdeling = $res['nama'];
		}

		$queryBkm = $queryBkm . " GROUP BY ket_kerani";
		// var_dump($queryBkm);return;
		$dataBkm = $this->db->query($queryBkm)->result_array();

		// var_dump($dataBkm);return;

		$data['bkm'] = 	$dataBkm;
		$data['filter_lokasi'] = 	$filter_lokasi;
		$data['filter_afdeling'] = 	$filter_afdeling;
		$data['filter_tgl_awal'] = 	$tanggal_awal;
		$data['filter_tgl_akhir'] = $tanggal_akhir;

		$html = $this->load->view('Est_Bkm_rekap_pemeliharaan_mandor_kerani', $data, true);

		// echo var_dum($data['bkm']);
		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		echo $html;

		// if ($format_laporan == 'xls') {
		// 	$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
		// 	$spreadsheet = $reader->loadFromString($html);
		// 	// $reader->setSheetIndex(1);
		// 	//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
		// 	$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
		// 	header("Pragma: public");
		// 	header("Expires: 0");
		// 	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		// 	header("Content-Type: application/force-download");
		// 	header("Content-Type: application/octet-stream");
		// 	header("Content-Type: application/download");
		// 	header("Content-Disposition: attachment;filename=test.xlsx");
		// 	header("Content-Transfer-Encoding: binary ");

		// 	ob_end_clean();
		// 	ob_start();
		// 	$objWriter->save('php://output');
		// } else if ($format_laporan == 'view') {
		// 	echo $html;
		// } else {
		// 	$filename = 'report_' . time();
		// 	$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		// }
	}
}
