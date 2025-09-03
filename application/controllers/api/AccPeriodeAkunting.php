<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

class AccPeriodeAkunting extends BD_Controller
{
	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('AccPeriodeAkuntingModel');
		$this->load->model('GbmOrganisasiModel');
		$this->load->model('M_DatatablesModel');
		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id = $this->user_data->id;
	}

	public function list_post()
	{
		$post = $this->post();
		$param = $post['parameter'];
		$query  = "SELECT a.*,
		b.kode as kode_organisasi,
		b.nama as nama_organisasi,
		c.user_full_name AS dibuat,
		d.user_full_name AS diubah
		from acc_periode_akunting a 
		inner join gbm_organisasi b on a.lokasi_id=b.id
		LEFT JOIN fwk_users c ON a.dibuat_oleh = c.id
		LEFT JOIN fwk_users d ON a.diubah_oleh = d.id";
		$search = array('a.nama', 'b.nama');
		$where  = null;

		$isWhere = null;
		$isWhere = " 1=1";


		if ($param['lokasi_id']) {
			$isWhere = $isWhere . " and a.lokasi_id =" . $param['lokasi_id'] . "";
		} else {
			$isWhere = $isWhere . " and  a.lokasi_id in
			(select location_id from fwk_users_location where user_id=" . $this->user_id . ")";
		}

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	function index_get($segment_3 = '')
	{
		$id = $segment_3;
		$retrieve = $this->AccPeriodeAkuntingModel->retrieve($id);

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAll_get()
	{

		$retrieve = $this->AccPeriodeAkuntingModel->retrieve_all_item();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function get_by_lokasi_id_get($org_id)
	{

		$retrieve = $this->AccPeriodeAkuntingModel->retrieve_by_lokasi_id($org_id);

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function create_post()
	{

		$data = $this->post();
		$data['dibuat_oleh'] = $this->user_id;
		$data['diubah_oleh'] = $this->user_id;
		$tgl_awal = $data['tgl_awal'];
		$tgl_akhir    =  $data['tgl_akhir'];
		$nama  =  $data['nama'];
		$status    =  $data['status'];
		$lokasi_id    =  $data['lokasi_id'];
		$p =  $this->db->query(" select * from acc_periode_akunting 
		where lokasi_id=". $lokasi_id."
		and( tgl_awal='". $tgl_awal ."' or tgl_akhir='". $tgl_akhir ."')
		" )->row_array();

		if ($p){
			$this->set_response(array("status" => "NOT OK", "data" => "Data sudah ada"), REST_Controller::HTTP_OK);
			return;

		}

		$retrieve =  $this->AccPeriodeAkuntingModel->create($data);
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
	}

	function update_post($segment_3 = '')
	{


		$id = (int)$segment_3;
		$item = $this->AccPeriodeAkuntingModel->retrieve($id);
		if (empty($item)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
		$data = $this->post();
		$data['diubah_oleh'] = $this->user_id;
		$retrieve =   $this->AccPeriodeAkuntingModel->update($item['id'], $data);
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
	}

	function index_delete($segment_3 = '')
	{

		$id = (int)$segment_3;
		$item = $this->AccPeriodeAkuntingModel->retrieve($id);
		if (empty($item)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$retrieve =  $this->AccPeriodeAkuntingModel->delete($item['id']);
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
	}

	function check_closing_post($id_periode)
	{
		$id = $id_periode;
		$retAccPeriodeAkunting = $this->AccPeriodeAkuntingModel->retrieve($id);
		$is_valid = true;
		$nama_periode = $retAccPeriodeAkunting['nama'];
		$array_hasil = array();
		if (empty($retAccPeriodeAkunting)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$tgl_awal = $retAccPeriodeAkunting['tgl_awal'];
		$tgl_akhir = $retAccPeriodeAkunting['tgl_akhir'];
		$lokasi = $this->GbmOrganisasiModel->retrieve($retAccPeriodeAkunting['lokasi_id']);
		$nama_lokasi = $lokasi['nama'];
		/*  CEK POSTING */
		if ($lokasi['tipe'] == 'ESTATE') {
			$res_transaksi = $this->db->query("select count(*)as jumlah from est_bkm_panen_ht where tanggal between '" . $tgl_awal . "' and '" . $tgl_akhir . "' and is_posting=0 and lokasi_id=" . $retAccPeriodeAkunting['lokasi_id'] . "")->row_array();
			if ($res_transaksi['jumlah'] == 0) {

				$array_hasil[] = array("item" => "Posting BKM Panen", "nilai" => $res_transaksi['jumlah'], "is_valid" => true);
			} else {
				$array_hasil[] = array("item" => "Posting BKM Panen", "nilai" => $res_transaksi['jumlah'], "is_valid" => false);
				$is_valid = false;
			}
			$res_transaksi = $this->db->query("select count(*)as jumlah from est_bkm_pemeliharaan_ht where tanggal between '" . $tgl_awal . "' and '" . $tgl_akhir . "' and is_posting=0 and lokasi_id=" . $retAccPeriodeAkunting['lokasi_id'] . "")->row_array();
			if ($res_transaksi['jumlah'] == 0) {

				$array_hasil[] = array("item" => "Posting BKM Pemeliharaan", "nilai" => $res_transaksi['jumlah'], "is_valid" => true);
			} else {
				$array_hasil[] = array("item" => "Posting BKM Pemeliharaan", "nilai" => $res_transaksi['jumlah'], "is_valid" => false);
				$is_valid = false;
			}
			$res_transaksi = $this->db->query("select count(*)as jumlah from est_bkm_umum_ht where tanggal between '" . $tgl_awal . "' and '" . $tgl_akhir . "' and is_posting=0 and lokasi_id=" . $retAccPeriodeAkunting['lokasi_id'] . "")->row_array();
			if ($res_transaksi['jumlah'] == 0) {

				$array_hasil[] = array("item" => "Posting BKM Umum", "nilai" => $res_transaksi['jumlah'], "is_valid" => true);
			} else {
				$array_hasil[] = array("item" => "Posting BKM Umum", "nilai" => $res_transaksi['jumlah'], "is_valid" => false);
				$is_valid = false;
			}
			$res_transaksi = $this->db->query("select count(*)as jumlah from est_spk_ba_ht where tanggal between '" . $tgl_awal . "' and '" . $tgl_akhir . "' and is_posting=0 and lokasi_id=" . $retAccPeriodeAkunting['lokasi_id'] . "")->row_array();
			if ($res_transaksi['jumlah'] == 0) {

				$array_hasil[] = array("item" => "Posting BAPP Kebun", "nilai" => $res_transaksi['jumlah'], "is_valid" => true);
			} else {
				$array_hasil[] = array("item" => "Posting BAPP Kebun", "nilai" => $res_transaksi['jumlah'], "is_valid" => false);
				$is_valid = false;
			}
			$res_transaksi = $this->db->query("select count(*)as jumlah from est_bapp_spk_kendaraan_ht where tanggal between '" . $tgl_awal . "' and '" . $tgl_akhir . "' and is_posting=0 and lokasi_id=" . $retAccPeriodeAkunting['lokasi_id'] . "")->row_array();
			if ($res_transaksi['jumlah'] == 0) {

				$array_hasil[] = array("item" => "Posting BAPP Kendaraan", "nilai" => $res_transaksi['jumlah'], "is_valid" => true);
			} else {
				$array_hasil[] = array("item" => "Posting BAPP Kendaraan", "nilai" => $res_transaksi['jumlah'], "is_valid" => false);
				$is_valid = false;
			}
		} else if ($lokasi['tipe'] == 'MILL') {
			$res_transaksi = $this->db->query("select count(*)as jumlah from est_bapp_spk_kendaraan_ht where tanggal between '" . $tgl_awal . "' and '" . $tgl_akhir . "' and is_posting=0 and lokasi_id=" . $retAccPeriodeAkunting['lokasi_id'] . "")->row_array();
			if ($res_transaksi['jumlah'] == 0) {

				$array_hasil[] = array("item" => "Posting BAPP Kendaraan", "nilai" => $res_transaksi['jumlah'], "is_valid" => true);
			} else {
				$array_hasil[] = array("item" => "Posting BAPP Kendaraan", "nilai" => $res_transaksi['jumlah'], "is_valid" => false);
				$is_valid = false;
			}
			
			$res_transaksi = $this->db->query("select count(*)as jumlah from acc_angkut_invoice_ht where tanggal between '" . $tgl_awal . "' and '" . $tgl_akhir . "' and is_posting=0 and lokasi_id=" . $retAccPeriodeAkunting['lokasi_id'] . "")->row_array();
			if ($res_transaksi['jumlah'] == 0) {

				$array_hasil[] = array("item" => "Posting Invoice Angkut", "nilai" => $res_transaksi['jumlah'], "is_valid" => true);
			} else {
				$array_hasil[] = array("item" => "Posting Invoice Angkut", "nilai" => $res_transaksi['jumlah'], "is_valid" => false);
				$is_valid = false;
			}

			$res_transaksi = $this->db->query("select count(*)as jumlah from acc_tbs_invoice_ht where tanggal between '" . $tgl_awal . "' and '" . $tgl_akhir . "' and is_posting=0 and lokasi_id=" . $retAccPeriodeAkunting['lokasi_id'] . "")->row_array();
			if ($res_transaksi['jumlah'] == 0) {

				$array_hasil[] = array("item" => "Posting Invoice Pembelian TBS", "nilai" => $res_transaksi['jumlah'], "is_valid" => true);
			} else {
				$array_hasil[] = array("item" => "Posting Invoice Pembelian TBS", "nilai" => $res_transaksi['jumlah'], "is_valid" => false);
				$is_valid = false;
			}
		} else if ($lokasi['tipe'] == 'HO') {
			$res_transaksi = $this->db->query("select count(*)as jumlah from acc_sales_invoice where tanggal between '" . $tgl_awal . "' and '" . $tgl_akhir . "' and is_posting=0 and lokasi_id=" . $retAccPeriodeAkunting['lokasi_id'] . "")->row_array();
			if ($res_transaksi['jumlah'] == 0) {

				$array_hasil[] = array("item" => "Posting Invoice Sales", "nilai" => $res_transaksi['jumlah'], "is_valid" => true);
			} else {
				$array_hasil[] = array("item" => "Posting Invoice Sales", "nilai" => $res_transaksi['jumlah'], "is_valid" => false);
				$is_valid = false;
			}
		}

		if ($lokasi['tipe'] == 'ESTATE' || $lokasi['tipe'] == 'MILL') {
			$res_transaksi = $this->db->query("select count(*)as jumlah from wrk_kegiatan_ht where tanggal between '" . $tgl_awal . "' and '" . $tgl_akhir . "' and is_posting=0 and lokasi_id=" . $retAccPeriodeAkunting['lokasi_id'] . "")->row_array();
			if ($res_transaksi['jumlah'] == 0) {

				$array_hasil[] = array("item" => "Posting Workshop", "nilai" => $res_transaksi['jumlah'], "is_valid" => true);
			} else {
				$array_hasil[] = array("item" => "Posting Workshop", "nilai" => $res_transaksi['jumlah'], "is_valid" => false);
				$is_valid = false;
			}

			$res_transaksi = $this->db->query("select count(*)as jumlah from trk_kegiatan_kendaraan_ht where tanggal between '" . $tgl_awal . "' and '" . $tgl_akhir . "' and is_posting=0 and lokasi_id=" . $retAccPeriodeAkunting['lokasi_id'] . "")->row_array();
			if ($res_transaksi['jumlah'] == 0) {

				$array_hasil[] = array("item" => "Posting Traksi", "nilai" => $res_transaksi['jumlah'], "is_valid" => true);
			} else {
				$is_valid = false;
				$array_hasil[] = array("item" => "Posting Traksi", "nilai" => $res_transaksi['jumlah'], "is_valid" => false);
			}
		}

		
		$res_transaksi = $this->db->query("select count(*)as jumlah from inv_pemakaian_ht where tanggal between '" . $tgl_awal . "' and '" . $tgl_akhir . "' and is_posting=0 and lokasi_id=" . $retAccPeriodeAkunting['lokasi_id'] . "")->row_array();
		if ($res_transaksi['jumlah'] == 0) {

			$array_hasil[] = array("item" => "Posting Inventory Pemakaian", "nilai" => $res_transaksi['jumlah'], "is_valid" => true);
		} else {
			$array_hasil[] = array("item" => "Posting Inventory Pemakaian", "nilai" => $res_transaksi['jumlah'], "is_valid" => false);
			$is_valid = false;
		}
		$res_transaksi = $this->db->query("select count(*)as jumlah from inv_penerimaan_po_ht where tanggal between '" . $tgl_awal . "' and '" . $tgl_akhir . "' and is_posting=0 and lokasi_id=" . $retAccPeriodeAkunting['lokasi_id'] . "")->row_array();
		if ($res_transaksi['jumlah'] == 0) {

			$array_hasil[] = array("item" => "Posting Inventory Penerimaan PO", "nilai" => $res_transaksi['jumlah'], "is_valid" => true);
		} else {
			$array_hasil[] = array("item" => "Posting Inventory Penerimaan PO", "nilai" => $res_transaksi['jumlah'], "is_valid" => false);
			$is_valid = false;
		}
		$res_transaksi = $this->db->query("select count(*)as jumlah from inv_penerimaan_tanpa_po_ht where tanggal between '" . $tgl_awal . "' and '" . $tgl_akhir . "' and is_posting=0 and lokasi_id=" . $retAccPeriodeAkunting['lokasi_id'] . "")->row_array();
		if ($res_transaksi['jumlah'] == 0) {

			$array_hasil[] = array("item" => "Posting Inventory Penerimaan tanpa PO", "nilai" => $res_transaksi['jumlah'], "is_valid" => true);
		} else {
			$array_hasil[] = array("item" => "Posting Inventory Penerimaan tanpa PO", "nilai" => $res_transaksi['jumlah'], "is_valid" => false);
			$is_valid = false;
		}
		$res_transaksi = $this->db->query("select count(*)as jumlah from inv_pindah_gudang_ht where tanggal between '" . $tgl_awal . "' and '" . $tgl_akhir . "' and is_posting=0 and lokasi_id=" . $retAccPeriodeAkunting['lokasi_id'] . "")->row_array();
		if ($res_transaksi['jumlah'] == 0) {

			$array_hasil[] = array("item" => "Posting Inventory Pindah Gudang", "nilai" => $res_transaksi['jumlah'], "is_valid" => true);
		} else {
			$array_hasil[] = array("item" => "Posting Inventory Pindah Gudang", "nilai" => $res_transaksi['jumlah'], "is_valid" => false);
			$is_valid = false;
		}


		$res_transaksi = $this->db->query("select count(*)as jumlah from inv_penerimaan_pindah_gudang_ht where tanggal between '" . $tgl_awal . "' and '" . $tgl_akhir . "' and is_posting=0 and lokasi_id=" . $retAccPeriodeAkunting['lokasi_id'] . "")->row_array();
		if ($res_transaksi['jumlah'] == 0) {

			$array_hasil[] = array("item" => "Posting Inventory Penerimaan Pindah Gudang", "nilai" => $res_transaksi['jumlah'], "is_valid" => true);
		} else {
			$array_hasil[] = array("item" => "Posting Inventory Penerimaan Pindah Gudang", "nilai" => $res_transaksi['jumlah'], "is_valid" => false);
			$is_valid = false;
		}
		$res_transaksi = $this->db->query("select count(*)as jumlah from inv_adj_ht where tanggal between '" . $tgl_awal . "' and '" . $tgl_akhir . "' and is_posting=0 and lokasi_id=" . $retAccPeriodeAkunting['lokasi_id'] . "")->row_array();
		if ($res_transaksi['jumlah'] == 0) {

			$array_hasil[] = array("item" => "Posting Inventory Adjustment", "nilai" => $res_transaksi['jumlah'], "is_valid" => true);
		} else {
			$array_hasil[] = array("item" => "Posting Inventory Adjustment", "nilai" => $res_transaksi['jumlah'], "is_valid" => false);
			$is_valid = false;
		}

		$res_transaksi = $this->db->query("select count(*)as jumlah from acc_jurnal_entry_ht where tanggal between '" . $tgl_awal . "' and '" . $tgl_akhir . "' and is_posting=0 and lokasi_id=" . $retAccPeriodeAkunting['lokasi_id'] . "")->row_array();
		if ($res_transaksi['jumlah'] == 0) {

			$array_hasil[] = array("item" => "Posting Jurnal Memorial", "nilai" => $res_transaksi['jumlah'], "is_valid" => true);
		} else {
			$array_hasil[] = array("item" => "Posting Jurnal Memorial", "nilai" => $res_transaksi['jumlah'], "is_valid" => false);
			$is_valid = false;
		}
		$res_transaksi = $this->db->query("select count(*)as jumlah from acc_kasbank_ht where tanggal between '" . $tgl_awal . "' and '" . $tgl_akhir . "' and is_posting=0 and lokasi_id=" . $retAccPeriodeAkunting['lokasi_id'] . "")->row_array();
		if ($res_transaksi['jumlah'] == 0) {

			$array_hasil[] = array("item" => "Posting Kasbank", "nilai" => $res_transaksi['jumlah'], "is_valid" => true);
		} else {
			$array_hasil[] = array("item" => "Posting Kasbank", "nilai" => $res_transaksi['jumlah'], "is_valid" => false);
			$is_valid = false;
		}

		$res_transaksi = $this->db->query("select count(*)as jumlah from acc_ap_invoice_ht where tanggal between '" . $tgl_awal . "' and '" . $tgl_akhir . "' and is_posting=0 and lokasi_id=" . $retAccPeriodeAkunting['lokasi_id'] . "")->row_array();
		if ($res_transaksi['jumlah'] == 0) {

			$array_hasil[] = array("item" => "Posting Invoice AP", "nilai" => $res_transaksi['jumlah'], "is_valid" => true);
		} else {
			$is_valid = false;
			$array_hasil[] = array("item" => "Posting Invoice AP", "nilai" => $res_transaksi['jumlah'], "is_valid" => false);
		}
		/* end  CEK POSTING  */

		/* == CEK POSTING PAYROLL== */
		$res_payroll_period = $this->db->query("select * from payroll_periode_gaji where tgl_akhir between '" . $tgl_awal . "' and '" . $tgl_akhir . "'  and lokasi_id=" . $retAccPeriodeAkunting['lokasi_id'] . "")->row_array();
		if ($res_payroll_period) {
			if ($res_payroll_period['is_posting'] == '0') {
				$is_valid = false;
				$array_hasil[] = array("item" => "Posting Payroll", "nilai" => 1, "is_valid" => false);
			} else {
				$array_hasil[] = array("item" => "Posting Payroll", "nilai" => 0, "is_valid" => true);
			}
		}
		/* == END CEK POSTING PAYROLL== */


		/* === CEK RUNNING AKUN ====== */
		// '41101'  ; -- bengkel
		// '41102'  ; -- kendaraan 
		// '41103'  ; -- Waduk 
		// '41104'  ; -- water treatment
		// '41105'  ; -- Pengobatan
		// '41106'  ;  --  perumahan
		$res_transaksi = $this->db->query("Select SUM(debet-kredit)as nilai from acc_jurnal_ht a inner join acc_jurnal_dt b on a.id=b.jurnal_id 
		INNER JOIN acc_akun c ON b.acc_akun_id=c.id
		 where a.tanggal between '" . $tgl_awal . "' and '" . $tgl_akhir . "' 
		 AND c.kode LIKE '41101%'
		and b.lokasi_id=" . $retAccPeriodeAkunting['lokasi_id'] . "")->row_array();
		if ($res_transaksi['nilai'] == 0) {

			$array_hasil[] = array("item" => " Transit Bengkel Sudah Nol", "nilai" => $res_transaksi['nilai'], "is_valid" => true);
		} else {
			$is_valid = false;
			$array_hasil[] = array("item" => "Transit Bengkel Belum Nol", "nilai" => $res_transaksi['nilai'], "is_valid" => false);
		}

		$res_transaksi = $this->db->query("Select SUM(debet-kredit)as nilai from acc_jurnal_ht a inner join acc_jurnal_dt b on a.id=b.jurnal_id 
		INNER JOIN acc_akun c ON b.acc_akun_id=c.id
		 where a.tanggal between '" . $tgl_awal . "' and '" . $tgl_akhir . "' 
		 AND c.kode LIKE '41102%'
		and b.lokasi_id=" . $retAccPeriodeAkunting['lokasi_id'] . "")->row_array();
		if ($res_transaksi['nilai'] == 0) {

			$array_hasil[] = array("item" => " Transit Kendaraan Sudah Nol", "nilai" => $res_transaksi['nilai'], "is_valid" => true);
		} else {
			$is_valid = false;
			$array_hasil[] = array("item" => "Transit Kendaraan Belum Nol", "nilai" => $res_transaksi['nilai'], "is_valid" => false);
		}

		$res_transaksi = $this->db->query("Select SUM(debet-kredit)as nilai from acc_jurnal_ht a inner join acc_jurnal_dt b on a.id=b.jurnal_id 
		INNER JOIN acc_akun c ON b.acc_akun_id=c.id
		 where a.tanggal between '" . $tgl_awal . "' and '" . $tgl_akhir . "' 
		 AND c.kode LIKE '41103%'
		and b.lokasi_id=" . $retAccPeriodeAkunting['lokasi_id'] . "")->row_array();
		if ($res_transaksi['nilai'] == 0) {

			$array_hasil[] = array("item" => " Transit Waduk Sudah Nol", "nilai" => $res_transaksi['nilai'], "is_valid" => true);
		} else {
			$is_valid = false;
			$array_hasil[] = array("item" => "Transit Waduk Belum Nol", "nilai" => $res_transaksi['nilai'], "is_valid" => false);
		}

		$res_transaksi = $this->db->query("Select SUM(debet-kredit)as nilai from acc_jurnal_ht a inner join acc_jurnal_dt b on a.id=b.jurnal_id 
		INNER JOIN acc_akun c ON b.acc_akun_id=c.id
		 where a.tanggal between '" . $tgl_awal . "' and '" . $tgl_akhir . "' 
		 AND c.kode LIKE '41104%'
		and b.lokasi_id=" . $retAccPeriodeAkunting['lokasi_id'] . "")->row_array();
		if ($res_transaksi['nilai'] == 0) {

			$array_hasil[] = array("item" => " Transit Watertretment Sudah Nol", "nilai" => $res_transaksi['nilai'], "is_valid" => true);
		} else {
			$is_valid = false;
			$array_hasil[] = array("item" => "Transit Watertretment Belum Nol", "nilai" => $res_transaksi['nilai'], "is_valid" => false);
		}

		$res_transaksi = $this->db->query("Select SUM(debet-kredit)as nilai from acc_jurnal_ht a inner join acc_jurnal_dt b on a.id=b.jurnal_id 
		INNER JOIN acc_akun c ON b.acc_akun_id=c.id
		 where a.tanggal between '" . $tgl_awal . "' and '" . $tgl_akhir . "' 
		 AND c.kode LIKE '41105%'
		and b.lokasi_id=" . $retAccPeriodeAkunting['lokasi_id'] . "")->row_array();
		if ($res_transaksi['nilai'] == 0) {

			$array_hasil[] = array("item" => " Transit Pengobatan Sudah Nol", "nilai" => $res_transaksi['nilai'], "is_valid" => true);
		} else {
			$is_valid = false;
			$array_hasil[] = array("item" => "Transit Pengobatan Belum Nol", "nilai" => $res_transaksi['nilai'], "is_valid" => false);
		}

		$res_transaksi = $this->db->query("Select SUM(debet-kredit)as nilai from acc_jurnal_ht a inner join acc_jurnal_dt b on a.id=b.jurnal_id 
		INNER JOIN acc_akun c ON b.acc_akun_id=c.id
		 where a.tanggal between '" . $tgl_awal . "' and '" . $tgl_akhir . "' 
		 AND c.kode LIKE '41106%'
		and b.lokasi_id=" . $retAccPeriodeAkunting['lokasi_id'] . "")->row_array();
		if ($res_transaksi['nilai'] == 0) {

			$array_hasil[] = array("item" => " Transit Perumahan Sudah Nol", "nilai" => $res_transaksi['nilai'], "is_valid" => true);
		} else {
			$is_valid = false;
			$array_hasil[] = array("item" => "Transit Perumahan Belum Nol", "nilai" => $res_transaksi['nilai'], "is_valid" => false);
		}
		/* === END RUNNING AKUN ====*/

		$ret_akhir = array("nama_periode" => $nama_periode, "nama_lokasi" =>$nama_lokasi, "is_valid" => $is_valid, "data" => $array_hasil);
		$this->set_response(array("status" => "OK", "data" => $ret_akhir), REST_Controller::HTTP_OK);
	}
}
