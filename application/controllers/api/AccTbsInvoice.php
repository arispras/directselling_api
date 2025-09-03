<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

class AccTbsInvoice extends BD_Controller //Rest_Controller
{
	public $user_id;
	public $theCredential;
	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('GbmOrganisasiModel');
		$this->load->model('AccTbsInvoiceModel');
		$this->load->model('AccJurnalModel');
		$this->load->model('AccApInvoiceModel');
		$this->load->model('M_DatatablesModel');
		$this->load->library('pdfgenerator');
		$this->load->helper("terbilang");
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
		b.nama AS lokasi,
		e.no_spk AS spk,d.nama_supplier,
		f.user_full_name AS dibuat,
		g.user_full_name AS diubah,
		h.user_full_name AS diposting 
		FROM acc_tbs_invoice_ht a 
		INNER JOIN gbm_organisasi b ON a.lokasi_id=b.id
		left JOIN prc_rekap_ht c ON a.rekap_id=c.id
		left join gbm_supplier d on a.supplier_id=d.id
		left join prc_kontrak e on c.spk_id=e.id
		LEFT JOIN fwk_users f ON a.dibuat_oleh = f.id
		LEFT JOIN fwk_users g ON a.diubah_oleh = g.id
		LEFT JOIN fwk_users h ON a.diposting_oleh = h.id 
		";
		$search = array('no_invoice', 'd.nama_supplier', 'a.tanggal', 'e.no_spk');
		$where  = null;//
		$isWhere = " 1=1";

		if ($param['tgl_mulai'] && $param['tgl_mulai']) {
			$isWhere = " a.tanggal between '" . $param['tgl_mulai'] . "' and '" . $param['tgl_akhir'] . "'";
		}
		if ($param['lokasi_id']){
			$isWhere =$isWhere. " and a.lokasi_id =".$param['lokasi_id']."";
	
		}else{
			$isWhere = $isWhere. " and  a.lokasi_id in
			(select location_id from fwk_users_location where user_id=" . $this->user_id . ")";
		}
		if (!empty($param['status_id']) ) {
			if ($param['status_id']=='N'){
				$isWhere =$isWhere .  "  and is_posting=0";
			}else{
				$isWhere =$isWhere .  "  and is_posting=1";
			}
		}
		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}

	public function listRekapforInvoice_post($customer_id)
	{
		$post = $this->post();

		$query  = "SELECT 
		a.*,
		b.nama AS lokasi,
		d.no_spk AS spk,d.ppn
		FROM prc_rekap_ht a 
		INNER JOIN gbm_organisasi b ON a.lokasi_id=b.id
		INNER JOIN Prc_kontrak d ON a.spk_id=d.id";
		$search = array('no_rekap', 'd.no_spk');
		// $where  = null;
		$where  = array(
			'd.customer_id' => $customer_id
		);

		$isWhere = null;
		// $isWhere = 'artikel.deleted_at IS NULL';

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}

	function index_get($id = '')
	{
		$retrieve = $this->AccTbsInvoiceModel->retrieve_by_id($id);
		$retrieve['detail'] = $this->AccTbsInvoiceModel->retrieve_detail($id);


		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function getAllbySupplierId_get($supp_id = '')
	{
		$retrieve = $this->db->query("select a.*,b.no_spk from prc_rekap_ht a inner join prc_kontrak b
		on a.spk_id=b.id
		where a.supplier_id=" . $supp_id . "")->result_array();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getRekapPerTanggalBelumInvoice_get($id = '')
	{
		$query = "select d.tanggal,sum(berat_terima)as berat_terima, c.harga from prc_rekap_ht a inner join prc_kontrak b
		on a.spk_id=b.id inner join prc_rekap_dt c
		on a.id=c.rekap_id inner join pks_timbangan d
		on c.pks_timbangan_id=d.id
		where a.id=" . $id . " and a.id not in(select rekap_id from  acc_tbs_invoice_ht) 
        group by d.tanggal
        order by d.tanggal";
		$retrieve = $this->db->query($query)->result_array();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAllOutstandingBayar_get()
	{

		$res = $this->db->query("SELECT a.*,a.id as invoice_id, b.nama as lokasi,
		c.nama_supplier as nama_kontraktor,c.acc_akun_id as akun_supplier_id,
		(a.total_tagihan-a.nilai_dibayar) as sisa,d.no_rekap,e.no_spk
		 FROM acc_tbs_invoice_ht a 
		 INNER join gbm_organisasi b on a.lokasi_id=b.id
		inner join gbm_supplier c on a.supplier_id=c.id 
		INNER JOIN prc_rekap_ht d ON a.rekap_id=d.id
		INNER JOIN prc_kontrak e ON d.spk_id=e.id
		where a.total_tagihan-a.nilai_dibayar >0
		and a.is_posting=1")->result_array();
		if (!empty($res)) {
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function index_post()
	{
		$data = $this->post();
		$data['diubah_oleh'] = $this->user_id;
		$data['dibuat_oleh'] = $this->user_id;
		$this->load->library('Autonumber');
		$data['no_invoice'] = $this->autonumber->acc_tbs_invoice($data['lokasi_id']['id'], $data['tanggal'],$data['supplier_id']['id']);

		$res = $this->AccTbsInvoiceModel->create($data);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'acc_invoice_tbs', 'action' => 'new', 'entity_id' => $res);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $data['no_invoice']), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function index_put($segment_3 = null)
	{
		$id = (int)$segment_3;
		$data = $this->put();
		$data['diubah_oleh'] = $this->user_id;
		$data['dibuat_oleh'] = $this->user_id;
		$res = $this->AccTbsInvoiceModel->update($id, $data);
		if (!empty($res)) {
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	public function index_delete($id)
	{

		$res = $this->AccTbsInvoiceModel->delete($id);
		if (!empty($res)) {
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}



	function posting_post($segment_3 = null)
	{
		$id = (int)$segment_3;
		$retrieve_ap = $this->AccTbsInvoiceModel->retrieve_by_id($id);
		
		if (empty($retrieve_ap)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Untuk diposting"), REST_Controller::HTTP_OK);
			return;
		} else {
			if ($retrieve_ap['is_posting'] == 1) {
				$this->set_response(array("status" => "NOT OK", "data" => "Data Sudah diposting"), REST_Controller::HTTP_OK);
				return;
			}
		}

		// CEK PERIODE SDH ADA ATAU SDH CLOSE//
		$chk = cek_periode($retrieve_ap['tanggal'], $retrieve_ap['lokasi_id']);
		if ($chk['status'] == false) {
			$this->set_response(array("status" => "NOT OK", "data" => $chk['message']), REST_Controller::HTTP_OK);
			return;
		}
		//==============//

		// $nilai_tagihan = $retrieve_ap['total_tagihan'];
		$nilai_sub_total =round( $retrieve_ap['sub_total']);
		$nilai_ppn =floor($retrieve_ap['ppn'] / 100 * $retrieve_ap['sub_total']);
		$nilai_pph = floor($retrieve_ap['pph'] / 100 * $retrieve_ap['sub_total']);
		$total_tagihan=($nilai_sub_total+$nilai_ppn)-$nilai_pph;
		$retrieve_akun_pembelian_tbs = $this->db->query("SELECT * FROM acc_auto_jurnal
		where kode='TBS_INVOICE'  ")->row_array();
		$akun_pembelian_tbs = $retrieve_akun_pembelian_tbs['acc_akun_id'];
		$retrieve_ppn = $this->db->query("SELECT * FROM acc_auto_jurnal
		 where kode='PPN_MASUKAN'  ")->row_array();
		$akun_ppn = $retrieve_ppn['acc_akun_id'];
		$retrieve_pph = $this->db->query("SELECT * FROM acc_auto_jurnal
		 where kode='PPH_PEMBELIAN'  ")->row_array();
		$akun_pph = $retrieve_pph['acc_akun_id'];
		$supp = $this->db->query("select * from gbm_supplier where id=" . $retrieve_ap['supplier_id'] . "")->row_array();
		$akun_supp_id = $supp['acc_akun_id'];
		// $dataHd = array(
		// 	'lokasi_id' => $retrieve_ap['lokasi_id'],
		// 	'supplier_id' => $retrieve_ap['supplier_id'],
		// 	'akun_supplier_id' =>$akun_supp_id,
		// 	'no_invoice' => $retrieve_ap['no_invoice'],
		// 	'nilai_invoice' => $nilai_tagihan,
		// 	'no_invoice_supplier' => $retrieve_ap['no_invoice'],
		// 	'no_faktur_pajak' => '',
		// 	'tanggal' => $retrieve_ap['tanggal'],
		// 	'tanggal_tempo' => $retrieve_ap['tanggal_tempo'],
		// 	'tanggal_terima' => $retrieve_ap['tanggal'],
		// 	'deskripsi' => "Pembelian TBS",
		// 	'jenis_invoice' => 'PEMBELIAN TBS',
		// 	'ref_id' => $retrieve_ap['id'],
		// 	'is_posting' => 1
		// );
		// $this->db->insert('acc_tbs_invoice_ht', $dataHd);
		// $ins_id  = $this->db->insert_id();
		// $this->db->insert(
		// 	"acc_ap_invoice_dt",
		// 	array(
		// 		'invoice_id' => $ins_id,
		// 		'lokasi_id' => $retrieve_ap['lokasi_id'],
		// 		'acc_akun_id' => $akun_pembelian_tbs,
		// 		'debet' => $nilai_sub_total,
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

		$data = $this->post();
		/* JURNAL GL */
		// $retrieve_header = $this->AccApInvoiceModel->retrieve_by_id($ins_id);
		// $retrieve_detail = $this->AccApInvoiceModel->retrieve_detail($ins_id);
		/*Hapus jurnal jika sdh ada berdasarkan no refernsi dan MOdul   */
		$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_ap['id'], 'INVOICE_TBS');
		// Data HEADER
		$this->load->library('Autonumber');
		$no_jurnal = $this->autonumber->jurnal_auto($retrieve_ap['lokasi_id'], $retrieve_ap['tanggal'], 'AP-TBS');
		$dataH = array(
			'no_jurnal' => $no_jurnal,
			'lokasi_id' => $retrieve_ap['lokasi_id'],
			'tanggal' => $retrieve_ap['tanggal'],
			'no_ref' => $retrieve_ap['no_invoice'],
			'ref_id' => $retrieve_ap['id'],
			'tipe_jurnal' => 'AUTO',
			'modul' => 'INVOICE_TBS',
			'keterangan' => 'INVOICE TBS',
			'is_posting' => 1,
			'diposting_oleh' => $this->user_id
		);
		$id_header = $this->AccJurnalModel->create_header($dataH);

		// KREDIT HUTANG SUPPLIER
		$dataKredit = array(
			'lokasi_id' => $retrieve_ap['lokasi_id'],
			'jurnal_id' => $id_header,
			'acc_akun_id' => $akun_supp_id, //akun ,
			'debet' => 0,
			'kredit' =>$total_tagihan,// ($retrieve_ap['total_tagihan']),
			'ket' => 'INVOICE TBS',
			'no_referensi' => $retrieve_ap['no_invoice'],
			'referensi_id' => NULL,
			'blok_stasiun_id' => NULL,
			'kegiatan_id' => NULL,
			'kendaraan_mesin_id' => NULL
		);
		$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataKredit);

		// PEMBELIAN TBS //  
		$dataDt = array(
			'lokasi_id' => $retrieve_ap['lokasi_id'],
			'jurnal_id' => $id_header,
			'acc_akun_id' => $akun_pembelian_tbs, //akun 
			'debet' => $nilai_sub_total,
			'kredit' => 0,
			'ket' => 'INVOICE TBS',
			'no_referensi' => $retrieve_ap['no_invoice'],
			'referensi_id' => NULL,
			'blok_stasiun_id' => NULL,
			'kegiatan_id' => NULL, //kegiatan ,
			'kendaraan_mesin_id' => NULL
		);
		$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataDt);

		// PPN //  
		$dataDt = array(
			'lokasi_id' => $retrieve_ap['lokasi_id'],
			'jurnal_id' => $id_header,
			'acc_akun_id' => $akun_ppn, //akun 
			'debet' => $nilai_ppn,
			'kredit' => 0,
			'ket' => 'PPN Pembelian TBS',
			'no_referensi' => $retrieve_ap['no_invoice'],
			'referensi_id' => NULL,
			'blok_stasiun_id' => NULL,
			'kegiatan_id' => NULL, //kegiatan ,
			'kendaraan_mesin_id' => NULL
		);
		$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataDt);

		// PPH //  
		$dataDt = array(
			'lokasi_id' => $retrieve_ap['lokasi_id'],
			'jurnal_id' => $id_header,
			'acc_akun_id' => $akun_pph, //akun 
			'debet' => 0,
			'kredit' => $nilai_pph,
			'ket' => 'PPH Pembelian TBS',
			'no_referensi' => $retrieve_ap['no_invoice'],
			'referensi_id' => NULL,
			'blok_stasiun_id' => NULL,
			'kegiatan_id' => NULL, //kegiatan ,
			'kendaraan_mesin_id' => NULL
		);
		$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataDt);

		$data['diposting_oleh'] = $this->user_id;
		$res = $this->AccTbsInvoiceModel->posting($id, $data);
		if (!empty($res)) {
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function laporan_tbs_invoice_post()
	{
		$versi_laporan = $this->post('versi_laporan', true);
		if ($versi_laporan == 'v1') {
			$this->laporan_tbs_invoice_detail();
		} else if ($versi_laporan == 'v2') {
			$this->laporan_tbs_invoice_umur_hutang();
		}
	}
	function laporan_tbs_invoice_detail()
	{
		error_reporting(0);

		$data = [];
		$lokasi_id = $this->post('lokasi_id', true);
		$supplier_id = $this->post('supplier_id', true);
		$tanggal_tempo = $this->post('tgl_mulai', true);
		$status = $this->post('status', true);
		$format_laporan = $this->post('format_laporan', true);

		$query = "select a.*,b.nama_supplier,IFNULL( c.dibayar, 0)as dibayar,a.total_tagihan-(IFNULL( c.dibayar, 0))as sisa   from acc_tbs_invoice_ht a INNER join gbm_supplier b 
		on a.supplier_id=b.id left join (select ref_id, sum(nilai)as dibayar from acc_kasbank_ht group by ref_id)c
		on a.id=c.ref_id
		where a.tanggal_tempo <=  '" . $tanggal_tempo . "' 	
		";
		$filter_supplier = "Semua";
		$filter_lokasi = "Semua";
		if ($lokasi_id) {
			$query = $query . " and a.lokasi_id=" . $lokasi_id . "";
			$res = $this->db->query("select * from gbm_organisasi where id=" . $lokasi_id . "")->row_array();
			$filter_lokasi = $res['nama'];
		}
		if ($supplier_id) {
			$query = $query . " and a.supplier_id=" . $supplier_id . "";
			$res = $this->db->query("select * from gbm_supplier where id=" . $supplier_id . "")->row_array();
			$filter_supplier = $res['nama_supplier'];
		}
		if ($status == '0') {
			$filter_status = 'SEMUA';
		} else if ($status == '1') {
			$query = $query . " and a.total_tagihan-(IFNULL( c.dibayar, 0))<=0";
			$filter_status = 'LUNAS';
		} else if ($status == '2') {
			$query = $query . " and a.total_tagihan-(IFNULL( c.dibayar, 0))>0";
			$filter_status = 'BELUM LUNAS';
		}
		$data = $this->db->query($query)->result_array();

		$data['ap'] = 	$data;
		$data['filter_supplier'] = 	$filter_supplier;
		$data['filter_lokasi'] = 	$filter_lokasi;
		$data['filter_tgl_tempo'] = 	$tanggal_tempo;
		$data['filter_status'] = $filter_status;

		$html = $this->load->view('Acc_Tbs_Invoice_Laporan', $data, true);

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

	function laporan_tbs_invoice_umur_hutang()
	{
		error_reporting(0);

		$data = [];
		$lokasi_id = $this->post('lokasi_id', true);
		$supplier_id = $this->post('supplier_id', true);
		$tanggal_tempo = $this->post('tgl_mulai', true);
		$status = $this->post('status', true);
		$format_laporan = $this->post('format_laporan', true);
		$umur_hutang = array('0-30'=>array(0,30),'31-60'=>array(31,60),	'61-90'=>array(61,90),'91-120'=>array(91,120),'121 - 150'=>array(121,150),'151-180'=>array(151,180),'181 - 210'=>array(181,210),'211 - 240'=>array(211,240),'241 - 270'=>array(241,270),'>271'=>array(271,10000));

		$query = "Select distinct a.supplier_id,b.nama_supplier  from acc_tbs_invoice_ht a INNER join gbm_supplier b 
		on a.supplier_id=b.id left join (select invoice_id, sum(debet)as dibayar from acc_kasbank_dt group by invoice_id)c
		on a.id=c.invoice_id
		where a.tanggal_tempo <=  '" . $tanggal_tempo . "' 	
		";
		
		$filter_supplier = "Semua";
		$filter_lokasi = "Semua";
		if ($lokasi_id) {
			$query = $query . " and a.lokasi_id=" . $lokasi_id . "";
			$res = $this->db->query("select * from gbm_organisasi where id=" . $lokasi_id . "")->row_array();
			$filter_lokasi = $res['nama'];
		}
		if ($supplier_id) {
			$query = $query . " and a.supplier_id=" . $supplier_id . "";
			$res = $this->db->query("select * from gbm_supplier where id=" . $supplier_id . "")->row_array();
			$filter_supplier = $res['nama_supplier'];
		}
		if ($status == '0') {
			$filter_status = 'SEMUA';
		} else if ($status == '1') {
			$query = $query . " and a.total_tagihan-(IFNULL( c.dibayar, 0))<=0";
			$filter_status = 'LUNAS';
		} else if ($status == '2') {
			$query = $query . " and a.total_tagihan-(IFNULL( c.dibayar, 0))>0";
			$filter_status = 'BELUM LUNAS';
		}
		$data_supplier = $this->db->query($query)->result_array();

		foreach ($data_supplier as $key_supp => $supp) {
			foreach ($umur_hutang as $um => $umur) {
				$query = "select sum(a.total_tagihan-(IFNULL( c.dibayar, 0)))as sisa  
				 from acc_tbs_invoice_ht a INNER join gbm_supplier b 
				on a.supplier_id=b.id left join (select invoice_id, sum(debet)as dibayar from acc_kasbank_dt group by invoice_id)c
				on a.id=c.invoice_id
				where a.tanggal_tempo <=  '" . $tanggal_tempo . "' 	and a.supplier_id=" . $supp['supplier_id'] . "";
				if ($lokasi_id) {
					$query = $query . " and a.lokasi_id=" . $lokasi_id . "";
				}
				if ($status == '0') {					
				} else if ($status == '1') {
					$query = $query . " and a.total_tagihan-(IFNULL( c.dibayar, 0))<=0";
				} else if ($status == '2') {
					$query = $query . " and a.total_tagihan-(IFNULL( c.dibayar, 0))>0";
				}
				$query = $query . " and  (DATEDIFF('" . $tanggal_tempo . "',a.tanggal_tempo ) between ".$umur[0]." and ".$umur[1].")"; 
				//  echo ($query);exit();
				$res = $this->db->query($query)->row_array();
				$sisa=0;
				if ($res){
					$sisa=$res['sisa']?$res['sisa']:0;
				}
				$data_supplier[$key_supp][$um]=$sisa;
			}
		}
// var_dump($data_supplier);
// exit();
		$data['ap'] = 	$data_supplier;
		$data['umur_hutang'] = 	$umur_hutang;
		$data['filter_supplier'] = 	$filter_supplier;
		$data['filter_lokasi'] = 	$filter_lokasi;
		$data['filter_tgl_tempo'] = 	$tanggal_tempo;
		$data['filter_status'] = $filter_status;

		$html = $this->load->view('Acc_Tbs_Invoice_Laporan_Umur_Hutang', $data, true);

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
	function print_slip_invoice_get($segment_3 = '')
	{

		$id = (int)$segment_3;
		$data = [];

		$hd = $this->AccTbsInvoiceModel->print_slip_invoice_header($id);
		$data['hd'] = $hd;
		$data['hd']['terbilang'] = terbilang($hd['total_tagihan']);
		$dt = $this->AccTbsInvoiceModel->print_slip_invoice_detail($id);
		$data['dt'] = $dt;

		$html = $this->load->view('AccSlipTbsInvoice', $data, true);

		$filename = 'report_TbsInvoice_' . time();
		$this->pdfgenerator->generate($html, $filename, true, 'A4', 'portrait');
		echo $html;
	}
	function print_slip_ba_get($segment_3 = '')
	{
		$id = (int)$segment_3;
		$data = [];

		$hd = $this->AccTbsInvoiceModel->print_slip_invoice_header($id);
		$data['hd'] = $hd;
		$data['hd']['terbilang'] = terbilang($hd['total_tagihan']);
		$dt = $this->AccTbsInvoiceModel->print_slip_invoice_detail($id);
		$data['dt'] = $dt;

		$html = $this->load->view('AccSlipTbsBA', $data, true);

		$filename = 'report_TbsBA_' . time();
		$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
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
}
