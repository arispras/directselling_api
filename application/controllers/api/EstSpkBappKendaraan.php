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

class EstSpkBappKendaraan extends BD_Controller
{
	public $user_id;
	public $theCredential;

	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('EstBappSpkKendaraanModel');
		$this->load->model('M_DatatablesModel');
		$this->load->model('AccJurnalModel');
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
		a.*,b.no_spk as no_spk,
		c.nama as lokasi ,
		d.nama as kendaraan,
		e.nama_supplier,
		f.user_full_name AS dibuat,
		g.user_full_name AS diubah,
		h.user_full_name AS diposting 
		FROM est_bapp_spk_kendaraan_ht a 
		INNER JOIN est_spk_kendaraan b on a.spk_kendaraan_id=b.id
		INNER JOIN gbm_organisasi c on a.lokasi_id=c.id
		INNER JOIN trk_kendaraan d on b.kendaraan_id=d.id
		inner join gbm_supplier e on b.kontraktor_id=e.id
		LEFT JOIN fwk_users f ON a.dibuat_oleh = f.id
		LEFT JOIN fwk_users g ON a.diubah_oleh = g.id
		LEFT JOIN fwk_users h ON a.diposting_oleh = h.id 
		";
		$search = array('a.tanggal', 'a.no_bapp', 'b.no_spk', 'c.nama', 'd.nama', 'e.nama_supplier');
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
		if (!empty($param['status_id']) ) {
			if ($param['status_id']=='N'){
				$isWhere =$isWhere .  "  and a.is_posting=0";
			}else{
				$isWhere =$isWhere .  "  and a.is_posting=1";
			}
		}

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}

	function index_get($id = '')
	{
		$retrieve = $this->EstBappSpkKendaraanModel->retrieve_by_id($id);
		$retrieve['detail'] = $this->EstBappSpkKendaraanModel->retrieve_detail($id);
		$retrieve['detail_opt'] = $this->EstBappSpkKendaraanModel->retrieve_detail_opt($id);


		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function getAll_get()
	{

		$retrieve = $this->EstBappSpkKendaraanModel->retrieve_all();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_OK);
		}
	}

	function getAllDetail_get($id = '')
	{
		$retrieve = $this->EstBappSpkKendaraanModel->retrieve_all_detail($id);

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
		$input['diubah_oleh'] = $this->user_id;

		//$input['no_spat'] = $this->getLastNumber('pks_pengolahan_ht', 'no_spat', 'SPAT');
		// var_dump($input);
		$res = $this->EstBappSpkKendaraanModel->create($input);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'est_bapp_spk_kendaraan', 'action' => 'new', 'entity_id' => $res);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $input['no_bapp']), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function index_put($segment_3 = null)
	{
		$id = (int)$segment_3;
		$data = $this->put();
		$data['diubah_oleh'] = $this->user_id;
		if (!$data['details']) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$res = $this->EstBappSpkKendaraanModel->update($id, $data);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'est_bapp_spk_kendaraan', 'action' => 'edit', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	public function index_delete($id)
	{

		$res = $this->EstBappSpkKendaraanModel->delete($id);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id' => $id)), 'entity' => 'est_bapp_spk_kendaraan', 'action' => 'delete', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function posting_post($segment_3 = null)
	{
		$id = (int)$segment_3;
		$data = $this->post();
		// $retrieve_header = $this->EstBappSpkKendaraanModel->retrieve_by_id($id);
		$retrieve_header = $this->db->query("SELECT a.*,b.kendaraan_id, c.acc_akun_id AS akun_kontraktor_id ,
		d.nama AS nama_kendaraan,b.kontraktor_id
		FROM est_bapp_spk_kendaraan_ht a INNER JOIN est_spk_kendaraan b ON a.spk_kendaraan_id=b.id 
		INNER JOIN gbm_supplier c ON b.kontraktor_id=c.id 
		INNER JOIN trk_kendaraan d ON b.kendaraan_id =d.id
		where a.id= " . $id . "")->row_array();

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

		$akun_kontraktor_id = $retrieve_header['akun_kontraktor_id'];
		// $res_akun = $this->db->query("SELECT * from acc_auto_jurnal 
		// where kode='BAPP_SPK_KENDARAAN'")->row_array();
		// if (empty($res_akun)) {
		// 	$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
		// 	return;
		// }
		// $akun_transit_kendaraan_id = $res_akun['acc_akun_id'];

		$res_akun = $this->db->query("SELECT * FROM acc_auto_jurnal
		where kode='PPH_PEMBELIAN'  ")->row_array();
		if (empty($res_akun)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Setting Jurnal"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$akun_pph_id = $res_akun['acc_akun_id'];
		$retrieve_inter_akun = $this->db->query("SELECT * FROM acc_inter_unit
		where tipe='INTER' ")->result_array();
		$akun_inter = array();
		foreach ($retrieve_inter_akun as $key => $akun) {
			$akun_inter[$akun['lokasi_id']][$akun['lokasi_id_2']] = $akun['acc_akun_id'];
		}
		/*  INSERT KE TABEL INVOICE */
		// $dataHd = array(
		// 	'lokasi_id' => $retrieve_header['lokasi_id'],
		// 	'supplier_id' => $retrieve_header['kontraktor_id'],
		// 	'akun_supplier_id' =>$akun_kontraktor_id,
		// 	'no_invoice' => $retrieve_header['no_bapp'],
		// 	'nilai_invoice' =>$retrieve_header['nilai_invoice'],
		// 	'no_invoice_supplier' =>$retrieve_header['no_bapp'],
		// 	'no_faktur_pajak' => '',
		// 	'tanggal' => $retrieve_header['tanggal'],
		// 	'tanggal_tempo' => $retrieve_header['tanggal_tempo'],
		// 	'tanggal_terima' => $retrieve_header['tanggal'],
		// 	'deskripsi' => "BA SPK KENDARAAN",
		// 	'jenis_invoice' => 'SPK',
		// 	'ref_id' => $retrieve_header['id'],
		// 	'is_posting' => 1
		// );
		// $this->db->insert('acc_ap_invoice_ht', $dataHd);
		// $ins_id  = $this->db->insert_id();
		// $this->db->insert(
		// 	"acc_ap_invoice_dt",
		// 	array(
		// 		'invoice_id' => $ins_id,
		// 		'lokasi_id' => $retrieve_header['lokasi_id'],
		// 		'acc_akun_id' => null,
		// 		'debet' => $retrieve_header['nilai_invoice'],
		// 		'kredit' => 0,
		// 		'ket' => 'Pembelian TBS',

		// 	)
		// );

		// $this->db->insert(
		// 	"acc_ap_invoice_dt",
		// 	array(
		// 		'invoice_id' => $ins_id,
		// 		'lokasi_id' => $retrieve_ap['lokasi_id'],
		// 		'acc_akun_id' => $akun_ppn,
		// 		'debet' => $nilai_ppn,
		// 		'kredit' => 0,
		// 		'ket' => 'PPN Pembelian TBS',
		// 	)
		// );
		// $this->db->insert(
		// 	"acc_ap_invoice_dt",
		// 	array(
		// 		'invoice_id' => $ins_id,
		// 		'lokasi_id' => $retrieve_ap['lokasi_id'],
		// 		'acc_akun_id' => $akun_pph,
		// 		'debet' => 0,
		// 		'kredit' => $nilai_pph,
		// 		'ket' => 'PPH Pembelian TBS',
		// 	)
		// );
		$this->load->library('Autonumber');

		$no_jurnal = $this->autonumber->jurnal_auto($retrieve_header['lokasi_id'], $retrieve_header['tanggal'], 'BASPKKD');
		$dataH = array(
			'no_jurnal' => $no_jurnal,
			'lokasi_id' => $retrieve_header['lokasi_id'],
			'tanggal' => $retrieve_header['tanggal'],
			'no_ref' => $retrieve_header['no_bapp'],
			'ref_id' => $retrieve_header['id'],
			'tipe_jurnal' => 'AUTO',
			'modul' => 'SPK_BAPP_KENDARAAN',
			'keterangan' => 'SPK_BAPP_KENDARAAN',
			'is_posting' => 1
		);
		$id_header = $this->AccJurnalModel->create_header($dataH);
		// Biaya  Debet
		// $dataDr = array(
		// 	'lokasi_id' => $retrieve_header['lokasi_id'],
		// 	'jurnal_id' => $id_header,
		// 	'acc_akun_id' => $akun_transit_kendaraan_id, //akun ,
		// 	'debet' => $retrieve_header['subtotal'],
		// 	'kredit' => 0,
		// 	'ket' => 'BA SPK Kendaraan:' . $retrieve_header['nama_kendaraan'],
		// 	'no_referensi' => $retrieve_header['no_bapp'],
		// 	'referensi_id' => NULL,
		// 	'kegiatan_id' => NULL,
		// 	'kendaraan_mesin_id' => $retrieve_header['kendaraan_id'],
		// 	'item_id' => NULL, //item,
		// 	'umur_tanam_blok' => NULL,
		// 	'blok_stasiun_id' => NULL,
		// );
		// $id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataDr);

		/* ALOKASI BIAYA KENDARAN  */
		$retrieve_detail = $this->db->query("SELECT a.jumlah,a.keterangan, b.kode AS kode_blok,b.nama AS nama_blok,
		  c.kode AS kode_kegiatan,c.nama AS nama_kegiatan,c.acc_akun_id,d.tahuntanam,d.statusblok as umur_tanam_blok,
		  a.blok_id,a.kegiatan_id,f.parent_id as lokasi_blok_id
			FROM est_bapp_spk_kendaraan_dt a 
			INNER JOIN gbm_organisasi b  ON a.blok_id=b.id
			INNER JOIN acc_kegiatan c ON a.kegiatan_id=c.id
			INNER JOIN gbm_blok d ON b.id=d.organisasi_id
			INNER JOIN gbm_organisasi e ON b.parent_id=e.id
			INNER JOIN  gbm_organisasi f ON e.parent_id=f.id

			where a.est_bapp_spk_kendaraan_id= " . $id . "")->result_array();
		foreach ($retrieve_detail as $key => $dt) {

			if ($retrieve_header['lokasi_id'] == $dt['lokasi_blok_id']) {
				$dataDr = array(
					'lokasi_id' => $retrieve_header['lokasi_id'],
					'jurnal_id' => $id_header,
					'acc_akun_id' => $dt['acc_akun_id'], //akun ,
					'debet' => $dt['jumlah'],
					'kredit' => 0,
					'ket' => 'BA SPK Kendaraan:' . $retrieve_header['nama_kendaraan'] . '. Blok:' . $dt['nama_blok'] . '. Kegiatan:' . $dt['nama_kegiatan'] . '. Ket:' . $dt['keterangan'],
					'no_referensi' => $retrieve_header['no_bapp'],
					'referensi_id' => NULL,
					'kegiatan_id' =>  $dt['kegiatan_id'], //akun ,,
					'kendaraan_mesin_id' => NULL,
					'item_id' => NULL, //item,
					'umur_tanam_blok' => $dt['umur_tanam_blok'],
					'blok_stasiun_id' => $dt['blok_id'],
				);
				$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataDr);
			} else {
				$lokasi_blok_id = $dt['lokasi_blok_id'];
				$inter_akun_id = null;
				if ($akun_inter[$retrieve_header['lokasi_id']][$lokasi_blok_id]) {
					$inter_akun_id = $akun_inter[$retrieve_header['lokasi_id']][$lokasi_blok_id];
				}
				if ($akun_inter[$lokasi_blok_id][$retrieve_header['lokasi_id']]) {
					$inter_akun_id = $akun_inter[$lokasi_blok_id][$retrieve_header['lokasi_id']];
				}

				// LOkasi Asal //
				$dataDr = array(
					'lokasi_id' => $retrieve_header['lokasi_id'],
					'jurnal_id' => $id_header,
					'acc_akun_id' => $inter_akun_id, //akun ,
					'debet' => $dt['jumlah'],
					'kredit' => 0,
					'ket' => 'BA SPK Kendaraan:' . $retrieve_header['nama_kendaraan'] . '. Blok:' . $dt['nama_blok'] . '. Kegiatan:' . $dt['nama_kegiatan'] . '. Ket:' . $dt['keterangan'],
					'no_referensi' => $retrieve_header['no_bapp'],
					'referensi_id' => NULL,
					'kegiatan_id' => NULL, //akun ,,
					'kendaraan_mesin_id' => NULL,
					'item_id' => NULL, //item,
					'umur_tanam_blok' => NULL,
					'blok_stasiun_id' => NULL,
				);
				$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataDr);
				// Lokasi Asal ///

				//== Lokasi Blok ==//
				$dataDr = array(
					'lokasi_id' => $dt['lokasi_blok_id'],
					'jurnal_id' => $id_header,
					'acc_akun_id' => $dt['acc_akun_id'], //akun ,
					'debet' => $dt['jumlah'],
					'kredit' => 0,
					'ket' => 'BA SPK Kendaraan:' . $retrieve_header['nama_kendaraan'] . '. Blok:' . $dt['nama_blok'] . '. Kegiatan:' . $dt['nama_kegiatan'] . '. Ket:' . $dt['keterangan'],
					'no_referensi' => $retrieve_header['no_bapp'],
					'referensi_id' => NULL,
					'kegiatan_id' =>  $dt['kegiatan_id'], //akun ,,
					'kendaraan_mesin_id' => NULL,
					'item_id' => NULL, //item,
					'umur_tanam_blok' => $dt['umur_tanam_blok'],
					'blok_stasiun_id' => $dt['blok_id'],
				);
				$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataDr);
				$dataCr = array(
					'lokasi_id' =>  $dt['lokasi_blok_id'],
					'jurnal_id' => $id_header,
					'acc_akun_id' => $inter_akun_id, //akun ,
					'debet' => 0,
					'kredit' =>  $dt['jumlah'],
					'ket' => 'BA SPK Kendaraan:' . $retrieve_header['nama_kendaraan'] . '. Blok:' . $dt['nama_blok'] . '. Kegiatan:' . $dt['nama_kegiatan'] . '. Ket:' . $dt['keterangan'],
					'no_referensi' => $retrieve_header['no_bapp'],
					'referensi_id' => NULL,
					'kegiatan_id' => NULL, //akun ,,
					'kendaraan_mesin_id' => NULL,
					'item_id' => NULL, //item,
					'umur_tanam_blok' => NULL,
					'blok_stasiun_id' => NULL,
				);
				$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataCr);
				// == Lokasi Blok ==//

			}
		}
		/* END ALOKASI BIAYA KENDARAN  */

		/* ALOKASI BIAYA OPERATOR  */	
		$retrieve_opt = $this->db->query("SELECT a.jumlah_opt,a.ket, b.kode AS kode_afdeling,
		b.nama AS nama_afdeling,  c.kode AS kode_kegiatan,c.nama AS nama_kegiatan,
		c.acc_akun_id,a.afdeling_id,a.kegiatan_id,d.tahuntanam,d.statusblok as umur_tanam_blok
		FROM est_bapp_spk_kendaraan_opt a 
		LEFT JOIN gbm_organisasi b  ON a.afdeling_id=b.id
		INNER JOIN acc_kegiatan c ON a.kegiatan_id=c.id
		Left JOIN gbm_blok d ON b.id=d.organisasi_id	
		where a.est_bapp_spk_kendaraan_id= " . $id . "")->result_array();
		foreach ($retrieve_opt as $key => $opt) {
			$dataDr = array(
				'lokasi_id' => $retrieve_header['lokasi_id'],
				'jurnal_id' => $id_header,
				'acc_akun_id' => $opt['acc_akun_id'], //akun ,
				'debet' => $opt['jumlah_opt'],
				'kredit' => 0,
				'ket' => 'Biaya Operator BA SPK Kendaraan:' . $retrieve_header['nama_kendaraan'] . '. Afd:' . $opt['nama_afdeling'] . '. Kegiatan:' . $opt['nama_kegiatan'] . '. Ket:' . $opt['ket'],
				'no_referensi' => $retrieve_header['no_bapp'],
				'referensi_id' => NULL,
				'kegiatan_id' => $opt['kegiatan_id'],
				'kendaraan_mesin_id' => NULL,
				'item_id' => NULL, //item,
				'umur_tanam_blok' => $opt['umur_tanam_blok'] ,
				'blok_stasiun_id' => $opt['afdeling_id'] /// afdeling_id sebenarnya blok salah field,
				// 'divisi_id' => $opt['afdeling_id']
				
			);
			$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataDr);
		}
		/* END ALOKASI BIAYA OPERATOR  */

		$dataCr = array(
			'lokasi_id' => $retrieve_header['lokasi_id'],
			'jurnal_id' => $id_header,
			'acc_akun_id' => ($akun_kontraktor_id), //akun 
			'debet' => 0,
			'kredit' => $retrieve_header['nilai_invoice'],
			'ket' => 'BA SPK Kendaraan:' . $retrieve_header['nama_kendaraan'],
			'no_referensi' => $retrieve_header['no_bapp'],
			'referensi_id' => NULL,
			'blok_stasiun_id' => NULL,
			'kegiatan_id' => NULL, // ,
			'kendaraan_mesin_id' => NULL,

		);
		$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataCr);

		if ($retrieve_header['pph_persen'] > 0) {
			$pph_nilai = ($retrieve_header['subtotal'] * $retrieve_header['pph_persen'] / 100);
			$dataCr = array(
				'lokasi_id' => $retrieve_header['lokasi_id'],
				'jurnal_id' => $id_header,
				'acc_akun_id' => $akun_pph_id, //akun ,
				'debet' => 0,
				'kredit' => $pph_nilai,
				'ket' => 'PPH BA SPK Kendaraan:' . $retrieve_header['nama_kendaraan'],
				'no_referensi' => $retrieve_header['no_bapp'],
				'referensi_id' => NULL,
				'kegiatan_id' => NULL,
				'kendaraan_mesin_id' => NULL,
				'item_id' => NULL, //item,
				'umur_tanam_blok' => NULL,
				'blok_stasiun_id' => NULL,
			);
			$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataCr);
		}

		$data['diposting_oleh'] = $this->user_id;
		$data['tanggal'] =	$retrieve_header['tanggal'];
		$res = $this->EstBappSpkKendaraanModel->posting($id, $data);
		if (!empty($res)) {
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAllOutstandingBayar_get()
	{

		$res = $this->db->query("SELECT a.*,a.id as invoice_id, c.nama as lokasi,
		d.nama_supplier as nama_kontraktor,d.acc_akun_id as akun_supplier_id,
		(a.nilai_invoice-a.nilai_dibayar) as sisa,	b.no_spk
		 FROM est_bapp_spk_kendaraan_ht a inner JOIN  est_spk_kendaraan b ON a.spk_kendaraan_id=b.id
		 INNER join gbm_organisasi c on b.lokasi_id=c.id
		inner join gbm_supplier d on b.kontraktor_id=d.id 
		where a.nilai_invoice-a.nilai_dibayar >0 and a.is_posting=1")->result_array();
		if (!empty($res)) {
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function print_slip_get($segment_3 = '')
	{
		error_reporting(0);

		$id = (int)$segment_3;
		$data = [];

		$queryHeader = "SELECT a.no_bapp AS bapp,
		a.tanggal AS tanggal,
		a.jml_opt as jml_opt,
		a.deskripsi AS des,
		b.nama AS lokasi,
		c.no_spk AS no_spk,
		d.nama_supplier AS kontraktor,
		e.nama as nama_kendaraan 
		FROM est_bapp_spk_kendaraan_ht a
		INNER JOIN gbm_organisasi b ON a.lokasi_id=b.id
		INNER JOIN est_spk_kendaraan c ON a.spk_kendaraan_id=c.id
		INNER JOIN gbm_supplier d ON c.kontraktor_id=d.id
		INNER JOIN trk_kendaraan e on c.kendaraan_id=e.id 
		WHERE a.id=" . $id . "
		";
		$dataHeader = $this->db->query($queryHeader)->row_array();

		$queryDetail = "SELECT a.*,b.nama AS blok, c.nama AS kegiatan ,d.kode as uom
		FROM est_bapp_spk_kendaraan_dt a
		INNER JOIN gbm_organisasi b ON a.blok_id=b.id
		INNER JOIN acc_kegiatan c ON a.kegiatan_id=c.id
		INNER JOIN gbm_uom d on a.uom_id=d.id
		WHERE a.est_bapp_spk_kendaraan_id=" . $id . "
		";
		$dataDetail = $this->db->query($queryDetail)->result_array();

		$queryDetailOpt = "SELECT a.*,b.nama AS afdeling, c.nama AS kegiatan FROM est_bapp_spk_kendaraan_opt a
		left JOIN gbm_organisasi b ON a.afdeling_id=b.id
		INNER JOIN acc_kegiatan c ON a.kegiatan_id=c.id
		WHERE a.est_bapp_spk_kendaraan_id=" . $id . "
		";
		$dataDetailOpt = $this->db->query($queryDetailOpt)->result_array();

		// $perminta = $this->InvPermintaanBarangModel->print_slip($id);
		$data['header'] = 	$dataHeader;
		$data['detail'] = 	$dataDetail;
		$data['detail_opt'] = 	$dataDetailOpt;

		$html = $this->load->view('Est_SlipBappSpkKendaraan', $data, true);

		$filename = 'report_' . time();
		$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		// echo $html;
	}
	function print_slip_rekap_get($segment_3 = '')
	{
		error_reporting(0);

		$id = (int)$segment_3;
		$data = [];

		$queryHeader = "SELECT a.no_bapp AS bapp,
		a.tanggal AS tanggal,
		a.deskripsi AS des,
		b.nama AS lokasi,
		c.no_spk AS no_spk,
		d.nama_supplier AS kontraktor,
		e.nama as nama_kendaraan
		 FROM est_bapp_spk_kendaraan_ht a
		INNER JOIN gbm_organisasi b ON a.lokasi_id=b.id
		INNER JOIN est_spk_kendaraan c ON a.spk_kendaraan_id=c.id
		INNER JOIN gbm_supplier d ON c.kontraktor_id=d.id
		INNER JOIN trk_kendaraan e on c.kendaraan_id=e.id 
		WHERE a.id=" . $id . "
		";
		$dataHeader = $this->db->query($queryHeader)->row_array();

		$queryDetail = "SELECT a.tanggal_operasi,sum(qty)as qty,sum(jumlah)as jumlah ,d.kode as uom
		FROM est_bapp_spk_kendaraan_dt a
		INNER JOIN gbm_organisasi b ON a.blok_id=b.id
		INNER JOIN acc_kegiatan c ON a.kegiatan_id=c.id
		INNER JOIN gbm_uom d on a.uom_id=d.id
		WHERE a.est_bapp_spk_kendaraan_id=" . $id . "
		group by a.tanggal_operasi,d.kode
		order by a.tanggal_operasi
		";
		$dataDetail = $this->db->query($queryDetail)->result_array();

		$queryDetailOpt = "SELECT a.tanggal_opt ,b.nama AS afdeling, c.nama AS kegiatan, 
		sum(jumlah_opt)as jumlah_opt
		FROM est_bapp_spk_kendaraan_opt a
		left JOIN gbm_organisasi b ON a.afdeling_id=b.id
		INNER JOIN acc_kegiatan c ON a.kegiatan_id=c.id
		WHERE a.est_bapp_spk_kendaraan_id=" . $id . "
		group by a.tanggal_opt,b.nama,c.nama 
		order by a.tanggal_opt
		";
		$dataDetailOpt = $this->db->query($queryDetailOpt)->result_array();

		$data['header'] = 	$dataHeader;
		$data['detail'] = 	$dataDetail;
		$data['detail_opt'] = 	$dataDetailOpt;

		$html = $this->load->view('Est_SlipBappSpkRekapKendaraan', $data, true);

		$filename = 'report_' . time();
		$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		// echo $html;
	}
	function print_slip_invoice_get($segment_3 = '')
	{
		error_reporting(0);

		$id = (int)$segment_3;
		$data = [];

		$queryHeader = "SELECT a.no_bapp AS bapp,
		a.id as id,
		a.tanggal AS tanggal,
		a.deskripsi AS des,
		a.nilai_invoice AS nilai_invoice,
		a.pph_persen AS pph_persen,
		a.subtotal AS subtotal,
		a.jml_opt as jml_opt,
		b.nama AS lokasi,
		c.no_spk AS no_spk,
		d.nama_supplier AS kontraktor,
		e.nama AS nm_kendaraan,
		e.kode AS kd_kendaraan 
		FROM est_bapp_spk_kendaraan_ht a
		INNER JOIN gbm_organisasi b ON a.lokasi_id=b.id
		INNER JOIN est_spk_kendaraan c ON a.spk_kendaraan_id=c.id
		INNER JOIN gbm_supplier d ON c.kontraktor_id=d.id
		INNER JOIN trk_kendaraan e ON c.kendaraan_id=e.id
		WHERE a.id=" . $id . "
		";
		$dataHeader = $this->db->query($queryHeader)->row_array();

		$queryDetail = "SELECT a.*,b.nama AS blok, c.nama AS kegiatan,d.kode as uom
		 FROM est_bapp_spk_kendaraan_dt a
		INNER JOIN gbm_organisasi b ON a.blok_id=b.id
		INNER JOIN acc_kegiatan c ON a.kegiatan_id=c.id
		INNER JOIN gbm_uom d on a.uom_id=d.id
		WHERE a.est_bapp_spk_kendaraan_id=" . $id . "
		";
		$dataDetail = $this->db->query($queryDetail)->result_array();

		$queryDetailOpt = "SELECT a.*,b.nama AS afdeling, c.nama AS kegiatan FROM est_bapp_spk_kendaraan_opt a
		left JOIN gbm_organisasi b ON a.afdeling_id=b.id
		INNER JOIN acc_kegiatan c ON a.kegiatan_id=c.id
		WHERE a.est_bapp_spk_kendaraan_id=" . $id . "
		";
		$dataDetailOpt = $this->db->query($queryDetailOpt)->result_array();

		$data['header'] = 	$dataHeader;
		$data['detail'] = 	$dataDetail;
		$data['detail_opt'] = 	$dataDetailOpt;

		$html = $this->load->view('Est_SlipBappSpkInvoiceKendaraan', $data, true);

		$filename = 'report_' . time();
		$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		// echo $html;
	}
	function laporan_detail_post()
	{

		// $id = (int)$segment_3;
		$data = [];
		$format_laporan = $this->post('format_laporan', true);
		$input = [
			'no_po' => '01/PO/DPA',
			// 'lokasi_id'=> 252,
			'tgl_mulai' => '2020-01-01',
			'tgl_akhir' => '2022-12-12',
		];
		$lokasi_id = $this->post('lokasi_id', true);
		$supplier_id = $this->post('supplier_id', true);
		$tanggal_awal = $this->post('tgl_mulai', true);
		$tanggal_akhir = $this->post('tgl_akhir', true);

		// $lokasi_id=$input['lokasi_id'];
		// $tanggal_awal=$input['tgl_mulai'];
		// $tanggal_akhir=$input['tgl_akhir'];

		$queryData = "SELECT 
		a.*,
		b.no_bapp,
		b.tanggal,
		c.nama AS blok,
		d.nama AS lokasi,
		e.nama AS kegiatan,
		f.no_spk AS no_spk,
		f.tanggal_mulai AS mulai,
		f.tanggal_akhir AS akhir,
		g.nama_supplier AS supplier,
		h.kode as uom
		FROM est_bapp_spk_kendaraan_dt a 
		INNER JOIN est_bapp_spk_kendaraan_ht b ON a.est_bapp_spk_kendaraan_id=b.id
		INNER JOIN gbm_organisasi c ON a.blok_id=c.id
		INNER JOIN gbm_organisasi d ON b.lokasi_id=d.id
		LEFT JOIN acc_kegiatan e ON a.kegiatan_id=e.id
		LEFT JOIN est_spk_kendaraan f ON b.spk_kendaraan_id=f.id
		INNER JOIN gbm_supplier g ON f.kontraktor_id=g.id
		INNER JOIN gbm_uom h ON a.uom_id=h.id
		where b.tanggal between  '" . $tanggal_awal . "' and  '" . $tanggal_akhir . "'	
		";

		// if (!empty($no_po)) {
		// 	$queryData = $queryData."and h.no_po LIKE '%".$no_po."%' ";
		// }

		$filter_lokasi = "Semua";
		if ($lokasi_id) {
			$queryData = $queryData . " and b.lokasi_id=" . $lokasi_id . "";
			$res = $this->db->query("select * from gbm_organisasi where id=" . $lokasi_id . "")->row_array();
			$filter_lokasi = $res['nama'];
		}

		$filter_supplier = "Semua";
		if ($supplier_id) {
			$queryData = $queryData . " and f.kontraktor_id=" . $supplier_id . "";
			$res = $this->db->query("select * from gbm_supplier where id=" . $supplier_id . "")->row_array();
			$filter_supplier = $res['nama_supplier'];
		}

		$dataBA = $this->db->query($queryData)->result_array();

		$data['ba'] = 	$dataBA;
		$data['filter_tgl_awal'] = 	$tanggal_awal;
		$data['filter_tgl_akhir'] = $tanggal_akhir;
		$data['filter_supplier'] = $filter_supplier;
		$data['filter_lokasi'] = $filter_lokasi;
		$data['format_laporan'] = $format_laporan;

		$html = $this->load->view('Est_SpkBappKendaraan_Laporan', $data, true);

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
}
