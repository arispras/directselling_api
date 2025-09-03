<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

class AccAngkutInvoice extends BD_Controller //Rest_Controller
{
	public $user_id;
	public $theCredential;
	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('GbmOrganisasiModel');
		$this->load->model('AccAngkutInvoiceModel');
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
		FROM acc_angkut_invoice_ht a 
		INNER JOIN gbm_organisasi b ON a.lokasi_id=b.id
		left JOIN prc_rekap_angkut_hd c ON a.rekap_id=c.id
		left join gbm_supplier d on a.supplier_id=d.id
		left join prc_kontrak_angkut e on c.spk_id=e.id
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
		$retrieve = $this->AccAngkutInvoiceModel->retrieve_by_id($id);
		$retrieve['detail'] = $this->AccAngkutInvoiceModel->retrieve_detail($id);


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
		where a.id=" . $id . " and a.id not in(select rekap_id from  acc_angkut_invoice_ht) 
        group by d.tanggal
        order by d.tanggal";
		$retrieve = $this->db->query($query)->result_array();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function index_post()
	{
		$data = $this->post();
		$this->load->library('Autonumber');
		$data['no_invoice'] = $this->autonumber->acc_angkut_invoice($data['lokasi_id']['id'], $data['tanggal'],$data['supplier_id']['id']);

		$data['diubah_oleh']= $this->user_id;
		$data['dibuat_oleh']= $this->user_id;
		$res = $this->AccAngkutInvoiceModel->create($data);
		if (!empty($res)) {
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
		$data['diubah_oleh']= $this->user_id;
		$data['dibuat_oleh']= $this->user_id;
		$res = $this->AccAngkutInvoiceModel->update($id, $data);
		if (!empty($res)) {
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	public function index_delete($id)
	{

		$res = $this->AccAngkutInvoiceModel->delete($id);
		if (!empty($res)) {
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}



	function posting_post($segment_3 = null)
	{
		$id = (int)$segment_3;
		$retrieve_ap = $this->AccAngkutInvoiceModel->retrieve_by_id($id);
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
		$nilai_tagihan = $retrieve_ap['total_tagihan'];
		$nilai_sub_total = $retrieve_ap['sub_total'];
		$nilai_potongan = $retrieve_ap['potongan'];
		$nilai_ppn = $retrieve_ap['ppn'] / 100 *( $retrieve_ap['sub_total']-$retrieve_ap['potongan']);
		$nilai_pph = $retrieve_ap['pph'] / 100 * ($retrieve_ap['sub_total']-$retrieve_ap['potongan']);
		$retrieve_akun_angkut_cpo = $this->db->query("SELECT * FROM acc_auto_jurnal
		where kode='ANGKUT_CPO_INVOICE'  ")->row_array();
		$akun_angkut_cpo = $retrieve_akun_angkut_cpo['acc_akun_id'];
		$retrieve_ppn = $this->db->query("SELECT * FROM acc_auto_jurnal
		 where kode='PPN_MASUKAN'  ")->row_array();
		$akun_ppn = $retrieve_ppn['acc_akun_id'];
		$retrieve_pph = $this->db->query("SELECT * FROM acc_auto_jurnal
		 where kode='PPH_PEMBELIAN'  ")->row_array();
		$akun_pph = $retrieve_pph['acc_akun_id'];
		$supp=$this->db->query("select * from gbm_supplier where id=". $retrieve_ap['supplier_id']."")->row_array();
		$akun_supp_id=$supp['acc_akun_id'];
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
		// 	'deskripsi' => "Angkut CPO",
		// 	'jenis_invoice' => 'ANGKUT CPO',
		// 	'ref_id' => $retrieve_ap['id'],
		// 	'is_posting' => 1
		// );
		// $this->db->insert('acc_ap_invoice_ht', $dataHd);
		// $ins_id  = $this->db->insert_id();
		// $this->db->insert(
		// 	"acc_ap_invoice_dt",
		// 	array(
		// 		'invoice_id' => $ins_id,
		// 		'lokasi_id' => $retrieve_ap['lokasi_id'],
		// 		'acc_akun_id' => $akun_pembelian_tbs,
		// 		'debet' => $nilai_sub_total-$nilai_potongan,
		// 		'kredit' => 0,
		// 		'ket' => 'ANGKUT CPO',

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
		// 		'ket' => 'PPN Angkut CPO',
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
		// 		'ket' => 'PPH Angkut CPO',
		// 	)
		// );

		$data = $this->post();
		
		// $retrieve_header = $this->AccApInvoiceModel->retrieve_by_id($ins_id);
		// $retrieve_detail = $this->AccApInvoiceModel->retrieve_detail($ins_id);

		/* JURNAL GL */
		/*Hapus jurnal jika sdh ada berdasarkan no refernsi dan MOdul   */
		$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_ap['id'], 'INVOICE_ANGKUT');
		// Data HEADER
		$this->load->library('Autonumber');
		$no_jurnal = $this->autonumber->jurnal_auto($retrieve_ap['lokasi_id'], $retrieve_ap['tanggal'], 'AP-AKT');
		// $dataH = array(
		// 	'no_jurnal' => $no_jurnal,
		// 	'lokasi_id' => $retrieve_header['lokasi_id'],
		// 	'tanggal' => $retrieve_header['tanggal'],
		// 	'no_ref' => $retrieve_header['no_invoice'],
		// 	'ref_id' => $retrieve_header['id'],
		// 	'tipe_jurnal' => 'AUTO',
		// 	'modul' => 'INVOICE_ANGKUT',
		// 	'keterangan' => 'AP INVOICE ANGKUT CPO',
		// 	'is_posting' => 1,
		// 	'diposting_oleh' => $this->user_id
		// );
		// $id_header = $this->AccJurnalModel->create_header($dataH);
		// // // Data DEBET
		// $dataKredit = array(
		// 	'lokasi_id' => $retrieve_header['lokasi_id'],
		// 	'jurnal_id' => $id_header,
		// 	'acc_akun_id' => $retrieve_header['akun_supplier_id'], //akun ,
		// 	'debet' => 0,
		// 	'kredit' => ($retrieve_header['nilai_invoice']),
		// 	'ket' => 'AP INVOICE ANGKUT CPO',
		// 	'no_referensi' => $retrieve_header['no_invoice'],
		// 	'referensi_id' => NULL,
		// 	'blok_stasiun_id' => NULL,
		// 	'kegiatan_id' => NULL,
		// 	'kendaraan_mesin_id' => NULL
		// );
		// $id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataKredit);
		// foreach ($retrieve_detail as $key => $value) {
		// 	$dataDt = array(
		// 		'lokasi_id' => $retrieve_header['lokasi_id'],
		// 		'jurnal_id' => $id_header,
		// 		'acc_akun_id' => ($value['acc_akun_id']), //akun 
		// 		'debet' => ($value['debet']),
		// 		'kredit' => ($value['kredit']),
		// 		'ket' => 'AP INVOICE:' . $value['ket'],
		// 		'no_referensi' => $retrieve_header['no_invoice'],
		// 		'referensi_id' => NULL,
		// 		'blok_stasiun_id' => NULL,
		// 		'kegiatan_id' => NULL, //kegiatan ,
		// 		'kendaraan_mesin_id' => NULL
		// 	);

		// 	$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataDt);
		// }
		$dataH = array(
			'no_jurnal' => $no_jurnal,
			'lokasi_id' => $retrieve_ap['lokasi_id'],
			'tanggal' => $retrieve_ap['tanggal'],
			'no_ref' => $retrieve_ap['no_invoice'],
			'ref_id' => $retrieve_ap['id'],
			'tipe_jurnal' => 'AUTO',
			'modul' => 'INVOICE_ANGKUT',
			'keterangan' => 'INVOICE ANGKUT CPO',
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
			'kredit' =>$nilai_tagihan,// ($retrieve_ap['total_tagihan']),
			'ket' => 'INVOICE ANGKUT CPO/PK',
			'no_referensi' => $retrieve_ap['no_invoice'],
			'referensi_id' => NULL,
			'blok_stasiun_id' => NULL,
			'kegiatan_id' => NULL,
			'kendaraan_mesin_id' => NULL
		);
		$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataKredit);

		// ANGKUT //  
		$dataDt = array(
			'lokasi_id' => $retrieve_ap['lokasi_id'],
			'jurnal_id' => $id_header,
			'acc_akun_id' => $akun_angkut_cpo, //akun 
			'debet' => $nilai_sub_total-$nilai_potongan ,
			'kredit' => 0,
			'ket' => 'INVOICE ANGKUT CPO/PK',
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
			'ket' => 'PPN Angkut CPO/PK',
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
			'ket' => 'PPH Angkut CPO/PK',
			'no_referensi' => $retrieve_ap['no_invoice'],
			'referensi_id' => NULL,
			'blok_stasiun_id' => NULL,
			'kegiatan_id' => NULL, //kegiatan ,
			'kendaraan_mesin_id' => NULL
		);
		$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataDt);

		$data['diposting_oleh'] = $this->user_id;
		$res = $this->AccAngkutInvoiceModel->posting($id, $data);
		if (!empty($res)) {
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAllOutstandingBayar_get()
	{

		$res = $this->db->query("SELECT a.*,a.id as invoice_id, b.nama as lokasi,
		c.nama_supplier as nama_kontraktor,c.acc_akun_id as akun_supplier_id,
		(a.total_tagihan-a.nilai_dibayar) as sisa,d.no_rekap,e.no_spk
		 FROM acc_angkut_invoice_ht a 
		 INNER join gbm_organisasi b on a.lokasi_id=b.id
		inner join gbm_supplier c on a.supplier_id=c.id 
		INNER JOIN prc_rekap_angkut_hd d ON a.rekap_id=d.id
		INNER JOIN prc_kontrak_angkut e ON d.spk_id=e.id
		where a.total_tagihan-a.nilai_dibayar>0
		and a.is_posting=1")->result_array();
		if (!empty($res)) {
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function print_slip_invoice_get($segment_3 = '')
	{

		$id = (int)$segment_3;
		$data = [];

		$hd = $this->AccAngkutInvoiceModel->print_slip_invoice_header($id);
		$data['hd'] = $hd;
		$data['hd']['terbilang']=terbilang( $hd['total_tagihan']);
		// $dt = $this->AccAngkutInvoiceModel->print_slip_invoice_detail($id);
		$dt = $this->AccAngkutInvoiceModel->print_slip_invoice_detail_sum($id);
		$data['dt'] = $dt;

		$html = $this->load->view('AccSlipAngkutCPOInvoice', $data, true);

		$filename = 'report_TbsInvoice_' . time();
		$this->pdfgenerator->generate($html, $filename, true, 'A4', 'portrait');
		echo $html;
	}
	function print_slip_ba_get($segment_3 = '')
	{
		$id = (int)$segment_3;
		$data = [];

		$hd = $this->AccAngkutInvoiceModel->print_slip_invoice_header($id);
		$data['hd'] = $hd;
		$data['hd']['terbilang']=terbilang( $hd['total_tagihan']);
		$dt = $this->AccAngkutInvoiceModel->print_slip_invoice_detail($id);
		$data['dt'] = $dt;

		$html = $this->load->view('AccSlipAngkutCPOBA', $data, true);

		$filename = 'report_TbsBA_' . time();
		$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		echo $html;
	}
	function print_slip_ba_susut_get($segment_3 = '')
	{
		$id = (int)$segment_3;
		$data = [];

		$hd = $this->AccAngkutInvoiceModel->print_slip_invoice_header($id);
		$data['hd'] = $hd;
		$data['hd']['terbilang']=terbilang( $hd['total_tagihan']);
		$dt = $this->AccAngkutInvoiceModel->print_slip_invoice_detail($id);
		$data['dt'] = $dt;

		$html = $this->load->view('AccSlipAngkutCPOBASusut', $data, true);

		$filename = 'report_TbsBA_' . time();
		$this->pdfgenerator->generate($html, $filename, true, 'A2', 'landscape');
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
