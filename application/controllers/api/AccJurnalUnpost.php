<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

class AccJurnalUnpost extends Rest_Controller
{
	public $user_id;
	public $theCredential;
	public $data = array();

	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('GbmOrganisasiModel');
		$this->load->model('AccJurnalModel');
		$this->load->model('AccJurnalUpahModel');
		$this->load->model('M_DatatablesModel');
		$this->load->library('pdfgenerator');
		$this->load->helper("antech_helper");
		// $this->auth();
		// $this->theCredential = $this->user_data;
		// $this->user_id = $this->user_data->id;
	}


	function unpost_post()
	{
		$notransaksi = $this->post('no_transaksi');
		$modul =  $this->post('modul');
		$lokasi_id = $this->post('lokasi_id');
		$retrieve = array();
		switch ($modul) {
			case "JURNAL":
				$retrieve = $this->jurnal_entry($notransaksi, $lokasi_id);
				break;
			case "KASBANK":
				$retrieve = $this->kasbank($notransaksi, $lokasi_id);
				break;
			case "AP_INVOICE": // TYPO
				$retrieve = $this->ap_invoice($notransaksi, $lokasi_id);
				break;
			case "AR_INVOICE": // TYPO
				$retrieve = $this->ar_invoice($notransaksi, $lokasi_id);
				break;
			case "SALES_INVOICE":
				$retrieve = $this->sales_invoice($notransaksi, $lokasi_id);
				break;
			case "UANG_MUKA":
				$retrieve = $this->uangmuka($notransaksi, $lokasi_id);
				break;
			case "UANG_MUKA_REALISASI":
				$retrieve = $this->uangmuka_realisasi($notransaksi, $lokasi_id);
				break;
			case "TBS_INVOICE":
				$retrieve = $this->tbs_invoice($notransaksi, $lokasi_id);
				break;
			case "ANGKUT_INVOICE":
				$retrieve = $this->angkut_invoice($notransaksi, $lokasi_id);
				break;
			case "PANEN":
				$retrieve = $this->panen($notransaksi, $lokasi_id);
				break;
			case "PEMELIHARAAN":
				$retrieve = $this->pemeliharaan($notransaksi, $lokasi_id);
				break;
			case "EST_ABSENSI_UMUM":
				$retrieve = $this->est_absensi_umum($notransaksi, $lokasi_id);
				break;
			case "TRAKSI":
				$retrieve = $this->traksi($notransaksi, $lokasi_id);
				break;
			case "WORKSHOP":
				$retrieve = $this->workshop($notransaksi, $lokasi_id);
				break;
			case "INVENTORY":
				$retrieve = $this->kasbank($notransaksi, $lokasi_id);
				break;
			case "SPK":
				$retrieve = $this->spk($notransaksi, $lokasi_id);
				break;
			case "SPK_BAPP":
				$retrieve = $this->spk_bapp($notransaksi, $lokasi_id);
				break;
			case "SPK_KENDARAAN":
				$retrieve = $this->spk_kendaraan($notransaksi, $lokasi_id);
				break;
			case "SPK_BAPP_KENDARAAN":
				$retrieve = $this->spk_bapp_kendaraan($notransaksi, $lokasi_id);
				break;
			case "INV_PENERIMAAN_TANPA_PO":
				$retrieve = $this->inv_penerimaan_tanpa_po($notransaksi, $lokasi_id);
				break;
			case "INV_PENERIMAAN_PO":
				$retrieve = $this->inv_penerimaan_po($notransaksi, $lokasi_id);
				break;
			case "INV_PENGIRIMAN_SO":
				$retrieve = $this->inv_pengiriman_so($notransaksi, $lokasi_id);
				break;
			case "INV_PERMINTAAN_BARANG":
				$retrieve = $this->inv_permintaan_barang($notransaksi, $lokasi_id);
				break;
			case "INV_PERMINTAAN_BARANG_MUTASI":
				$retrieve = $this->inv_permintaan_barang_mutasi($notransaksi, $lokasi_id);
				break;
			case "INV_PEMAKAIAN":
				$retrieve = $this->inv_pemakaian($notransaksi, $lokasi_id);
				break;
			case "INV_PINDAH_GUDANG":
				$retrieve = $this->inv_pindah_gudang($notransaksi, $lokasi_id);
				break;
			case "INV_PENERIMAAN_PINDAH_GUDANG":
				$retrieve = $this->inv_penerimaan_pindah_gudang($notransaksi, $lokasi_id);
				break;
			case "INV_RETUR_PEMAKAIAN":
				$retrieve = $this->inv_retur_pemakaian($notransaksi, $lokasi_id);
				break;
			case "INV_ADJUSMENT":
				$retrieve = $this->inv_adjustment($notransaksi, $lokasi_id);
				break;
			case "SO_SALES_ORDER":
				$retrieve = $this->so_sales_order($notransaksi, $lokasi_id);
				break;
			case "PO_PURCHASE_ORDER":
				$retrieve = $this->po_purchase_order($notransaksi, $lokasi_id);
				break;
			default:
				// $this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
				// return;
		}

		$this->set_response(array("status" => $retrieve['status'],  "data" => $retrieve['data']), REST_Controller::HTTP_OK);

		// if (!empty($retrieve)) {
		// 	$this->set_response(array("status" => "OK", "res" => $retrieve, "data" => 'Unpost Berhasil'), REST_Controller::HTTP_OK);
		// } else {
		// 	$this->set_response(array("status" => "NOT OK", "res" => $retrieve, "data" => "Tidak ada Data"), REST_Controller::HTTP_OK);
		// }
	}
	function kasbank($no_transaksi, $lokasi_id)
	{
		$retrieve_header = $this->db->query(" select * from acc_kasbank_ht where no_transaksi='" . $no_transaksi . "' and is_posting=1 and lokasi_id=" . $lokasi_id . "")->row_array();
		if (empty($retrieve_header)) {
			return array("status" => "NOT OK", "data" => "Tidak ada Data Transaksi");
		} else {
			$chk = cek_periode($retrieve_header['tanggal'], $retrieve_header['lokasi_id']);
			if ($chk['status'] == false) {
				return array("status" => "NOT OK", "data" => $chk['message']);
			}
		}
		/* Hapus jurnal */
		$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'KASBANK');
		/* Buka Posting */
		$data = array(
			'is_posting' => 0,
		);
		$this->db->where('id', $retrieve_header['id']);
		$this->db->update('acc_kasbank_ht', $data);
		return array("status" => "OK", "data" => "Unposting Berhasil");
	}
	function jurnal_entry($no_transaksi, $lokasi_id)
	{
		$retrieve_header = $this->db->query(" select * from acc_jurnal_entry_ht where no_jurnal='" . $no_transaksi . "' and is_posting=1 and lokasi_id=" . $lokasi_id . "")->row_array();

		if (empty($retrieve_header)) {
			return array("status" => "NOT OK", "data" => "Tidak ada Data Transaksi");
		} else {
			$chk = cek_periode($retrieve_header['tanggal'], $retrieve_header['lokasi_id']);
			if ($chk['status'] == false) {
				return array("status" => "NOT OK", "data" => $chk['message']);
			}
		}
		/* Hapus jurnal */
		$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'JURNAL');
		/* Buka Posting */
		$data = array(
			'is_posting' => 0
		);
		$this->db->where('id', $retrieve_header['id']);
		$this->db->update('acc_jurnal_entry_ht', $data);
		return array("status" => "OK", "data" => "Unposting Berhasil");
	}
	function ap_invoice($no_transaksi, $lokasi_id)
	{
		$retrieve_header = $this->db->query(" select  * from acc_ap_invoice_ht where no_invoice='" . $no_transaksi . "' and is_posting=1 and lokasi_id=" . $lokasi_id . "")->row_array();
		if (empty($retrieve_header)) {
			return array("status" => "NOT OK", "data" => "Tidak ada Data Transaksi");
		} else {
			$chk = cek_periode($retrieve_header['tanggal'], $retrieve_header['lokasi_id']);
			if ($chk['status'] == false) {
				return array("status" => "NOT OK", "data" => $chk['message']);
			}
		}
		/* Hapus jurnal */
		$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'AP_INVOICE');
		/* Buka Posting */
		$data = array(
			'is_posting' => 0,
		);
		$this->db->where('id', $retrieve_header['id']);
		$this->db->update('acc_ap_invoice_ht', $data);
		return array("status" => "OK", "data" => "Unposting Berhasil");
	}
	function ar_invoice($no_transaksi, $lokasi_id)
	{
		$retrieve_header = $this->db->query(" select  * from acc_ar_invoice_ht where no_invoice='" . $no_transaksi . "' and is_posting=1 and lokasi_id=" . $lokasi_id . "")->row_array();
		if (empty($retrieve_header)) {
			return array("status" => "NOT OK", "data" => "Tidak ada Data Transaksi");
		} else {
			$chk = cek_periode($retrieve_header['tanggal'], $retrieve_header['lokasi_id']);
			if ($chk['status'] == false) {
				return array("status" => "NOT OK", "data" => $chk['message']);
			}
		}
		/* Hapus jurnal */
		$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'AR_INVOICE');
		/* Buka Posting */
		$data = array(
			'is_posting' => 0,
		);
		$this->db->where('id', $retrieve_header['id']);
		$this->db->update('acc_ar_invoice_ht', $data);
		return array("status" => "OK", "data" => "Unposting Berhasil");
	}
	function sales_invoice($no_transaksi, $lokasi_id)
	{
		$retrieve_header = $this->db->query(" select  * from  acc_sales_invoice where no_invoice='" . $no_transaksi . "' and is_posting=1 and lokasi_id=" . $lokasi_id . "")->row_array();;
		if (empty($retrieve_header)) {
			return array("status" => "NOT OK", "data" => "Tidak ada Data Transaksi");
		} else {
			$chk = cek_periode($retrieve_header['tanggal'], $retrieve_header['lokasi_id']);
			if ($chk['status'] == false) {
				return array("status" => "NOT OK", "data" => $chk['message']);
			}
		}
		/* Hapus jurnal */
		$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'SALES_INVOICE');
		/* Buka Posting */
		$data = array(
			'is_posting' => 0,
		);
		$this->db->where('id', $retrieve_header['id']);
		$this->db->update('acc_sales_invoice', $data);
		return array("status" => "OK", "data" => "Unposting Berhasil");
	}

	function tbs_invoice($no_transaksi, $lokasi_id)
	{
		$retrieve_header = $this->db->query(" select  * from acc_tbs_invoice_ht where no_invoice='" . $no_transaksi . "' and is_posting=1  and lokasi_id=" . $lokasi_id . "")->row_array();;
		if (empty($retrieve_header)) {
			return array("status" => "NOT OK", "data" => "Tidak ada Data Transaksi");
		} else {
			$chk = cek_periode($retrieve_header['tanggal'], $retrieve_header['lokasi_id']);
			if ($chk['status'] == false) {
				return array("status" => "NOT OK", "data" => $chk['message']);
			}
		}
		$this->set_response(array("status" => "NOT OK", "data" => $retrieve_header), REST_Controller::HTTP_OK);

		// 
		/* cari invoice di Ap Invoce Kemudian Hapus */
		// $retrieve_inv = $this->db->query(" select  * from  acc_ap_invoice_ht where jenis_invoice ='PEMBELIAN TBS' and  ref_id=" . $retrieve_header['id'] . "")->row_array();

		// $this->db->where('invoice_id', $retrieve_inv['id']);
		// $this->db->delete('acc_ap_invoice_dt');
		// $this->db->where('id', $retrieve_inv['id']);
		// $this->db->delete('acc_ap_invoice_ht');

		/* Hapus jurnal */
		$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'INVOICE_TBS');

		/* Buka posting */
		$data = array(
			'is_posting' => 0,
		);
		$this->db->where('id', $retrieve_header['id']);
		$this->db->update('acc_tbs_invoice_ht', $data);
		return array("status" => "OK", "data" => "Unposting Berhasil");
	}
	function angkut_invoice($no_transaksi, $lokasi_id)
	{
		$retrieve_header = $this->db->query(" select  * from acc_angkut_invoice_ht where no_invoice='" . $no_transaksi . "' and is_posting=1 and lokasi_id=" . $lokasi_id . "")->row_array();;
		if (empty($retrieve_header)) {
			return array("status" => "NOT OK", "data" => "Tidak ada Data Transaksi");
		} else {
			$chk = cek_periode($retrieve_header['tanggal'], $retrieve_header['lokasi_id']);
			if ($chk['status'] == false) {
				return array("status" => "NOT OK", "data" => $chk['message']);
			}
		}
		$this->set_response(array("status" => "NOT OK", "data" => $retrieve_header), REST_Controller::HTTP_OK);

		// 
		/* cari invoice di Ap Invoce Kemudian Hapus */
		$retrieve_inv = $this->db->query(" select  * from  acc_ap_invoice_ht where jenis_invoice ='ANGKUT CPO' and  ref_id=" . $retrieve_header['id'] . "")->row_array();

		$this->db->where('invoice_id', $retrieve_inv['id']);
		$this->db->delete('acc_ap_invoice_dt');
		$this->db->where('id', $retrieve_inv['id']);
		$this->db->delete('acc_ap_invoice_ht');

		/* Hapus jurnal */
		$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'INVOICE_ANGKUT');
		$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_inv['id'], 'INVOICE_ANGKUT');
		/* Buka posting */
		$data = array(
			'is_posting' => 0,
		);
		$this->db->where('id', $retrieve_header['id']);
		$this->db->update('acc_angkut_invoice_ht', $data);
		return array("status" => "OK", "data" => "Unposting Berhasil");
	}

	function uangmuka($no_transaksi, $lokasi_id)
	{
		$retrieve_header = $this->db->query(" select  * from acc_uang_muka where no_transaksi='" . $no_transaksi . "' and is_posting=1 and lokasi_id=" . $lokasi_id . "")->row_array();;
		if (empty($retrieve_header)) {
			return array("status" => "NOT OK", "data" => "Tidak ada Data Transaksi");
		} else {
			$chk = cek_periode($retrieve_header['tanggal'], $retrieve_header['lokasi_id']);
			if ($chk['status'] == false) {
				return array("status" => "NOT OK", "data" => $chk['message']);
			}
		}
		/* Hapus jurnal */
		$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'UANG_MUKA');
		/* Buka posting */
		$data = array(
			'is_posting' => 0,
		);
		$this->db->where('id', $retrieve_header['id']);
		$this->db->update('acc_uang_muka', $data);
		return array("status" => "OK", "data" => "Unposting Berhasil");
	}
	function uangmuka_realisasi($no_transaksi, $lokasi_id)
	{
		$retrieve_header = $this->db->query(" select  * from acc_uang_muka_realisasi where no_transaksi='" . $no_transaksi . "' and is_posting=1 and lokasi_id=" . $lokasi_id . "")->row_array();;
		if (empty($retrieve_header)) {
			return array("status" => "NOT OK", "data" => "Tidak ada Data Transaksi");
		} else {
			$chk = cek_periode($retrieve_header['tanggal'], $retrieve_header['lokasi_id']);
			if ($chk['status'] == false) {
				return array("status" => "NOT OK", "data" => $chk['message']);
			}
		}
		/* Hapus jurnal */
		$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'REALISASI_UANG_MUKA');
		/* Buka Posting */
		$data = array(
			'is_posting' => 0,
		);
		$this->db->where('id', $retrieve_header['id']);
		$this->db->update('acc_uang_muka_realisasi', $data);
		return array("status" => "OK", "data" => "Unposting Berhasil");
	}
	function panen($no_transaksi, $lokasi_id)
	{
		$retrieve_header = $this->db->query(" select  * from est_bkm_panen_ht where no_transaksi='" . $no_transaksi . "' and is_posting=1 and lokasi_id=" . $lokasi_id . "")->row_array();;
		if (empty($retrieve_header)) {
			return array("status" => "NOT OK", "data" => "Tidak ada Data Transaksi");
		} else {
			$chk = cek_periode($retrieve_header['tanggal'], $retrieve_header['lokasi_id']);
			if ($chk['status'] == false) {
				return array("status" => "NOT OK", "data" => $chk['message']);
			}
		}
		/* Hapus jurnal */
		$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'PANEN');
		$this->AccJurnalUpahModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'PANEN');
		/* Buka Posting */
		$data = array(
			'is_posting' => 0,
		);
		$this->db->where('id', $retrieve_header['id']);
		$this->db->update('est_bkm_panen_ht', $data);
		return array("status" => "OK", "data" => "Unposting Berhasil");
	}
	function pemeliharaan($no_transaksi, $lokasi_id)
	{
		$retrieve_header = $this->db->query(" select  * from est_bkm_pemeliharaan_ht where no_transaksi='" . $no_transaksi . "' and is_posting=1 and lokasi_id=" . $lokasi_id . "")->row_array();;
		if (empty($retrieve_header)) {
			return array("status" => "NOT OK", "data" => "Tidak ada Data Transaksi");
		} else {
			$chk = cek_periode($retrieve_header['tanggal'], $retrieve_header['lokasi_id']);
			if ($chk['status'] == false) {
				return array("status" => "NOT OK", "data" => $chk['message']);
			}
		}
		$gudang_id = null;
		$retrieve_gudang = $this->db->query("SELECT * from gbm_organisasi 
		where afdeling_id=" . $retrieve_header['rayon_afdeling_id']  . " and tipe='GUDANG_VIRTUAL'")->row_array();
		if (empty($retrieve_gudang)) {
			// $this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Gudang Virtual"), REST_Controller::HTTP_NOT_FOUND);
			// return;
		} else {
			$gudang_id = $retrieve_gudang['id'];
		}
		$data_transaksi = $this->db->query("SELECT a.id,a.no_transaksi,a.tanggal,b.item_id,b.qty,b.total,b.blok_id
		FROM est_bkm_pemeliharaan_ht a inner join est_bkm_pemeliharaan_item b on a.id=b.bkm_pemeliharaan_id  
		inner join gbm_organisasi c on b.blok_id=c.id   
	    where b.bkm_pemeliharaan_id=" . $retrieve_header['id'] . ";")->result_array();


		/* balikan stok */
		foreach ($data_transaksi as $key => $value) {
			if (!empty($retrieve_gudang)) {
				$item_dt = $this->db->query("select * from inv_item_dt  where gudang_id=" . $gudang_id . "
			  and item_id=" . $value['item_id'] . "")->row_array();
				if ($item_dt) {
					$this->db->where('item_id', $value['item_id']);
					$this->db->where('gudang_id', $gudang_id);
					$this->db->update("inv_item_dt", array(
						'item_id' => $value['item_id'],
						'gudang_id' => $gudang_id,
						'qty' => $item_dt['qty'] + $value['qty'],
						'nilai' => $item_dt['nilai'] + ($value['nilai'] * $value['qty'])
					));
				}
			}
		}
		// hapus  transaksi harian
		$this->db->where('ref_id', $retrieve_header['id']);
		$this->db->where('tipe', 'PEMAKAIAN_BKM');
		$this->db->delete('inv_transaksi_harian');
		/* Hapus jurnal */
		$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'INV_PEMAKAIAN_BARANG_BKM');
		$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'PEMELIHARAAN');
		$this->AccJurnalUpahModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'PEMELIHARAAN');

		/* Buka Posting */
		$data = array(
			'is_posting' => 0,
		);
		$this->db->where('id', $retrieve_header['id']);
		$this->db->update('est_bkm_pemeliharaan_ht', $data);
		return array("status" => "OK", "data" => "Unposting Berhasil");
	}
	function est_absensi_umum($no_transaksi, $lokasi_id)
	{
		$retrieve_header = $this->db->query(" select  * from est_bkm_umum_ht where no_transaksi='" . $no_transaksi . "' and is_posting=1 and lokasi_id=" . $lokasi_id . "")->row_array();;
		if (empty($retrieve_header)) {
			return array("status" => "NOT OK", "data" => "Tidak ada Data Transaksi");
		} else {
			$chk = cek_periode($retrieve_header['tanggal'], $retrieve_header['lokasi_id']);
			if ($chk['status'] == false) {
				return array("status" => "NOT OK", "data" => $chk['message']);
			}
		}

		/* Hapus jurnal */
		$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'BKM_UMUM');
		$this->AccJurnalUpahModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'BKM_UMUM');
		/* Buka Posting */
		$data = array(
			'is_posting' => 0,
		);
		$this->db->where('id', $retrieve_header['id']);
		$this->db->update('est_bkm_umum_ht', $data);
		return array("status" => "OK", "data" => "Unposting Berhasil");
	}
	function spk($no_transaksi, $lokasi_id)
	{
		$retrieve_header = $this->db->query(" select  * from est_spk_ht where no_spk='" . $no_transaksi . "' and is_posting=1 and lokasi_id=" . $lokasi_id . "")->row_array();;
		if (empty($retrieve_header)) {
			return array("status" => "NOT OK", "data" => "Tidak ada Data Transaksi");
		} else {
			$chk = cek_periode($retrieve_header['tanggal'], $retrieve_header['lokasi_id']);
			if ($chk['status'] == false) {
				return array("status" => "NOT OK", "data" => $chk['message']);
			}
		}
		/* Hapus jurnal */
		$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'SPK');
		/* Buka Posting */
		$data = array(
			'is_posting' => 0,
		);
		$this->db->where('id', $retrieve_header['id']);
		$this->db->update('est_spk_ht', $data);
		return array("status" => "OK", "data" => "Unposting Berhasil");
	}
	function spk_kendaraan($no_transaksi, $lokasi_id)
	{
		$retrieve_header = $this->db->query(" select  * from est_spk_kendaraan where no_spk='" . $no_transaksi . "' and is_posting=1 and lokasi_id=" . $lokasi_id . "")->row_array();;
		if (empty($retrieve_header)) {
			return array("status" => "NOT OK", "data" => "Tidak ada Data Transaksi");
		} else {
			$chk = cek_periode($retrieve_header['tanggal'], $retrieve_header['lokasi_id']);
			if ($chk['status'] == false) {
				return array("status" => "NOT OK", "data" => $chk['message']);
			}
		}
		/* Hapus jurnal */
		$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'SPK_KENDARAAN');
		/* Buka Posting */
		$data = array(
			'is_posting' => 0,
		);
		$this->db->where('id', $retrieve_header['id']);
		$this->db->update('est_spk_kendaraan', $data);
		return array("status" => "OK", "data" => "Unposting Berhasil");
	}
	function spk_bapp_old($no_transaksi, $lokasi_id)
	{
		$retrieve_header = $this->db->query(" select  * from est_spk_bapp where no_transaksi='" . $no_transaksi . "' and is_posting=1 and lokasi_id=" . $lokasi_id . "")->row_array();;
		if (empty($retrieve_header)) {
			return array("status" => "NOT OK", "data" => "Tidak ada Data Transaksi");
		} else {
			$chk = cek_periode($retrieve_header['tanggal'], $retrieve_header['lokasi_id']);
			if ($chk['status'] == false) {
				return array("status" => "NOT OK", "data" => $chk['message']);
			}
		}
		/* Hapus jurnal */
		$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'SPK_BAPP');
		/* Buka Posting */
		$data = array(
			'is_posting' => 0,
		);
		$this->db->where('id', $retrieve_header['id']);
		$this->db->update('est_spk_bapp', $data);
		return array("status" => "OK", "data" => "Unposting Berhasil");
	}
	function spk_bapp($no_transaksi, $lokasi_id)
	{
		$retrieve_header = $this->db->query(" select  * from est_spk_ba_ht where no_transaksi='" . $no_transaksi . "' and is_posting=1 and lokasi_id=" . $lokasi_id . "")->row_array();;
		if (empty($retrieve_header)) {
			return array("status" => "NOT OK", "data" => "Tidak ada Data Transaksi");
		} else {
			$chk = cek_periode($retrieve_header['tanggal'], $retrieve_header['lokasi_id']);
			if ($chk['status'] == false) {
				return array("status" => "NOT OK", "data" => $chk['message']);
			}
		}
		/* Hapus jurnal */
		$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'BA_SPK');
		/* Buka Posting */
		$data = array(
			'is_posting' => 0,
		);
		$this->db->where('id', $retrieve_header['id']);
		$this->db->update('est_spk_ba_ht', $data);
		return array("status" => "OK", "data" => "Unposting Berhasil");
	}
	function spk_bapp_kendaraan($no_transaksi, $lokasi_id)
	{
		$retrieve_header = $this->db->query(" select  * from est_bapp_spk_kendaraan_ht where no_bapp='" . $no_transaksi . "' and is_posting=1 and lokasi_id=" . $lokasi_id . "")->row_array();;
		if (empty($retrieve_header)) {
			return array("status" => "NOT OK", "data" => "Tidak ada Data Transaksi");
		} else {
			$chk = cek_periode($retrieve_header['tanggal'], $retrieve_header['lokasi_id']);
			if ($chk['status'] == false) {
				return array("status" => "NOT OK", "data" => $chk['message']);
			}
		}
		/* Hapus jurnal */
		$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'SPK_BAPP_KENDARAAN');
		/* Buka Posting */
		$data = array(
			'is_posting' => 0,
		);
		$this->db->where('id', $retrieve_header['id']);
		$this->db->update('est_bapp_spk_kendaraan_ht', $data);
		return array("status" => "OK", "data" => "Unposting Berhasil");
	}
	function traksi($no_transaksi, $lokasi_id)
	{
		$retrieve_header = $this->db->query(" select  * from trk_kegiatan_kendaraan_ht where no_transaksi='" . $no_transaksi . "' and is_posting=1 and lokasi_id=" . $lokasi_id . "")->row_array();;
		if (empty($retrieve_header)) {
			return array("status" => "NOT OK", "data" => "Tidak ada Data Transaksi");
		} else {
			$chk = cek_periode($retrieve_header['tanggal'], $retrieve_header['lokasi_id']);
			if ($chk['status'] == false) {
				return array("status" => "NOT OK", "data" => $chk['message']);
			}
		}
		/* Hapus jurnal */
		$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'TRAKSI');
		$this->AccJurnalUpahModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'TRAKSI');
		/* Buka Posting */
		$data = array(
			'is_posting' => 0,
		);
		$this->db->where('id', $retrieve_header['id']);
		$this->db->update('trk_kegiatan_kendaraan_ht', $data);
		return array("status" => "OK", "data" => "Unposting Berhasil");
	}
	function workshop($no_transaksi, $lokasi_id)
	{
		$retrieve_header = $this->db->query(" select  * from wrk_kegiatan_ht where no_transaksi='" . $no_transaksi . "' and is_posting=1 and lokasi_id=" . $lokasi_id . "")->row_array();;
		if (empty($retrieve_header)) {
			return array("status" => "NOT OK", "data" => "Tidak ada Data Transaksi");
		} else {
			$chk = cek_periode($retrieve_header['tanggal'], $retrieve_header['lokasi_id']);
			if ($chk['status'] == false) {
				return array("status" => "NOT OK", "data" => $chk['message']);
			}
		}
		/* Hapus jurnal */
		$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'WORKSHOP');
		$this->AccJurnalUpahModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'WORKSHOP');
		/* Buka Posting */
		$data = array(
			'is_posting' => 0,
		);
		$this->db->where('id', $retrieve_header['id']);
		$this->db->update('wrk_kegiatan_ht', $data);
		return array("status" => "OK", "data" => "Unposting Berhasil");
	}
	function inv_permintaan_barang($no_transaksi, $lokasi_id)
	{
		$retrieve_header = $this->db->query(" select  * from inv_permintaan_ht where no_transaksi='" . $no_transaksi . "' and is_posting=1 and lokasi_id=" . $lokasi_id . "")->row_array();;
		if (empty($retrieve_header)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}

		/* Buka Posting */
		$data = array(
			'is_posting' => 0,
		);
		$this->db->where('id', $retrieve_header['id']);
		$this->db->update('inv_permintaan_ht', $data);
		return $retrieve_header;;
	}
	function inv_permintaan_barang_mutasi($no_transaksi, $lokasi_id)
	{
		$retrieve_header = $this->db->query(" select  * from inv_permintaan_pindah_gudang_ht where no_transaksi='" . $no_transaksi . "' and is_posting=1 and lokasi_id=" . $lokasi_id . "")->row_array();;
		if (empty($retrieve_header)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}

		/* Buka Posting */
		$data = array(
			'is_posting' => 0,
		);
		$this->db->where('id', $retrieve_header['id']);
		$this->db->update('inv_permintaan_pindah_gudang_ht', $data);
		return $retrieve_header;;
	}
	function so_sales_order($no_transaksi, $lokasi_id)
	{
		$retrieve_header = $this->db->query(" select  * from sls_so_ht where no_so='" . $no_transaksi . "' and status='RELEASE' ")->row_array();;
		if (empty($retrieve_header)) {
			return array("status" => "NOT OK", "data" => "Tidak ada Data Transaksi");
		}

		/* Buka Posting */
		$data = array(
			'is_posting'=>0,
			'status' => 'CREATED',
			'last_approve_position' => Null,
			'last_approve_user' => Null,
			'proses_approval' => 0
		);
		$this->db->where('id', $retrieve_header['id']);
		$this->db->update('sls_so_ht', $data);
		return array("status" => "OK", "data" => "Unposting Berhasil");
	}
	function po_purchase_order($no_transaksi, $lokasi_id)
	{
		$retrieve_header = $this->db->query(" select  * from prc_po_ht where no_po='" . $no_transaksi . "' and status='RELEASE' ")->row_array();;
		if (empty($retrieve_header)) {
			return array("status" => "NOT OK", "data" => "Tidak ada Data Transaksi");
			
		}

		/* Buka Posting */

		$data = array(
			'is_posting'=>0,
			'status' => 'CREATED',
			'last_approve_position' => Null,
			'last_approve_user' => Null,
			'proses_approval' => 0
		);
		$this->db->where('id', $retrieve_header['id']);
		$this->db->update('prc_po_ht', $data);
		return array("status" => "OK", "data" => "Unposting Berhasil");
	}
	function inv_pemakaian($no_transaksi, $lokasi_id)
	{
		$retrieve_header = $this->db->query(" select  * from inv_pemakaian_ht where no_transaksi='" . $no_transaksi . "' and is_posting=1 and lokasi_id=" . $lokasi_id . "")->row_array();;
		if (empty($retrieve_header)) {
			return array("status" => "NOT OK", "data" => "Tidak ada Data Transaksi");
		} else {
			$chk = cek_periode($retrieve_header['tanggal'], $retrieve_header['lokasi_id']);
			if ($chk['status'] == false) {
				return array("status" => "NOT OK", "data" => $chk['message']);
			}
		}
		$gudang_id = $retrieve_header['gudang_id'];

		$data_transaksi = $this->db->query("select * from inv_transaksi_harian 
		 where ref_id=" . $retrieve_header['id'] . " and tipe='PEMAKAIAN' ;")->result_array();

		/* balikan stok */
		foreach ($data_transaksi as $key => $value) {
			$item_dt = $this->db->query("select * from inv_item_dt  where gudang_id=" . $gudang_id . "
			  and item_id=" . $value['item_id'] . "")->row_array();
			if ($item_dt) {
				$this->db->where('item_id', $value['item_id']);
				$this->db->where('gudang_id', $gudang_id);
				$this->db->update("inv_item_dt", array(
					'item_id' => $value['item_id'],
					'gudang_id' => $gudang_id,
					'qty' => $item_dt['qty'] + $value['qty_keluar'],
					'nilai' => $item_dt['nilai'] + ($value['nilai_keluar'])
				));
			}
		}
		// hapus  transaksi harian
		$this->db->where('ref_id', $retrieve_header['id']);
		$this->db->where('tipe', 'PEMAKAIAN');
		$this->db->delete('inv_transaksi_harian');
		/* Hapus jurnal */
		$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'INV_PEMAKAIAN_BARANG');
		/* Buka Posting */
		$data = array(
			'is_posting' => 0,
		);
		$this->db->where('id', $retrieve_header['id']);
		$this->db->update('inv_pemakaian_ht', $data);
		return array("status" => "OK", "data" => "Unposting Berhasil");
	}
	function inv_pindah_gudang($no_transaksi, $lokasi_id)
	{
		$retrieve_header = $this->db->query(" select  * from inv_pindah_gudang_ht where no_transaksi='" . $no_transaksi . "' and is_posting=1 and lokasi_id=" . $lokasi_id . "")->row_array();;
		if (empty($retrieve_header)) {
			return array("status" => "NOT OK", "data" => "Tidak ada Data Transaksi");
		} else {
			$chk = cek_periode($retrieve_header['tanggal'], $retrieve_header['lokasi_id']);
			if ($chk['status'] == false) {
				return array("status" => "NOT OK", "data" => $chk['message']);
			}
		}
		$gudang_id = $retrieve_header['dari_gudang_id'];

		$data_transaksi = $this->db->query("select * from inv_transaksi_harian 
		 where ref_id=" . $retrieve_header['id'] . " and tipe='PINDAH_GUDANG' ;")->result_array();

		/* balikan stok */
		foreach ($data_transaksi as $key => $value) {
			$item_dt = $this->db->query("select * from inv_item_dt  where gudang_id=" . $gudang_id . "
			  and item_id=" . $value['item_id'] . "")->row_array();
			if ($item_dt) {
				$this->db->where('item_id', $value['item_id']);
				$this->db->where('gudang_id', $gudang_id);
				$this->db->update("inv_item_dt", array(
					'item_id' => $value['item_id'],
					'gudang_id' => $gudang_id,
					'qty' => $item_dt['qty'] + $value['qty_keluar'],
					'nilai' => $item_dt['nilai'] + ($value['nilai_keluar'])
				));
			}
		}
		// hapus  transaksi harian
		$this->db->where('ref_id', $retrieve_header['id']);
		$this->db->where('tipe', 'PINDAH_GUDANG');
		$this->db->delete('inv_transaksi_harian');
		/* Hapus jurnal */
		$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'INV_PINDAH_GUDANG');
		/* Buka Posting */
		$data = array(
			'is_posting' => 0,
		);
		$this->db->where('id', $retrieve_header['id']);
		$this->db->update('inv_pindah_gudang_ht', $data);
		return array("status" => "OK", "data" => "Unposting Berhasil");
	}
	function inv_penerimaan_po($no_transaksi, $lokasi_id)
	{
		$retrieve_header = $this->db->query(" select  * from inv_penerimaan_po_ht where no_transaksi='" . $no_transaksi . "' and is_posting=1 and lokasi_id=" . $lokasi_id . "")->row_array();
		if (empty($retrieve_header)) {
			return array("status" => "NOT OK", "data" => "Tidak ada Data Transaksi");
		} else {
			$chk = cek_periode($retrieve_header['tanggal'], $retrieve_header['lokasi_id']);
			if ($chk['status'] == false) {
				return array("status" => "NOT OK", "data" => $chk['message']);
			}
		}
		$gudang_id = $retrieve_header['gudang_id'];

		$data_transaksi = $this->db->query("select * from inv_transaksi_harian 
		 where ref_id=" . $retrieve_header['id'] . " and tipe='PENERIMAAN_PO' ;")->result_array();

		/* balikan stok */
		foreach ($data_transaksi as $key => $value) {
			$item_dt = $this->db->query("select * from inv_item_dt  where gudang_id=" . $gudang_id . "
			  and item_id=" . $value['item_id'] . "")->row_array();
			if ($item_dt) {
				$this->db->where('item_id', $value['item_id']);
				$this->db->where('gudang_id', $gudang_id);
				$this->db->update("inv_item_dt", array(
					'item_id' => $value['item_id'],
					'gudang_id' => $gudang_id,
					'qty' => $item_dt['qty'] - $value['qty_masuk'],
					'nilai' => $item_dt['nilai'] - ($value['nilai_masuk'])
				));
			}
		}
		// hapus  transaksi harian
		$this->db->where('ref_id', $retrieve_header['id']);
		$this->db->where('tipe', 'PENERIMAAN_PO');
		$this->db->delete('inv_transaksi_harian');
		/* Hapus jurnal */
		$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'INV_PENERIMAAN_PO');
		/* Buka Posting */
		$data = array(
			'is_posting' => 0,
		);
		$this->db->where('id', $retrieve_header['id']);
		$this->db->update('inv_penerimaan_po_ht', $data);
		return array("status" => "OK", "data" => "Unposting Berhasil");
	}
	function inv_pengiriman_so($no_transaksi, $lokasi_id)
	{
		$retrieve_header = $this->db->query(" select  * from inv_pengiriman_so_ht where no_transaksi='" . $no_transaksi . "' and is_posting=1 and lokasi_id=" . $lokasi_id . "")->row_array();
		if (empty($retrieve_header)) {
			return array("status" => "NOT OK", "data" => "Tidak ada Data Transaksi");
		} else {
			$chk = cek_periode($retrieve_header['tanggal'], $retrieve_header['lokasi_id']);
			if ($chk['status'] == false) {
				return array("status" => "NOT OK", "data" => $chk['message']);
			}
		}
		$gudang_id = $retrieve_header['gudang_id'];

		$data_transaksi = $this->db->query("select * from inv_transaksi_harian 
		 where ref_id=" . $retrieve_header['id'] . " and tipe='PENGIRIMAN_SO' ;")->result_array();

		/* balikan stok */
		foreach ($data_transaksi as $key => $value) {
			$item_dt = $this->db->query("select * from inv_item_dt  where gudang_id=" . $gudang_id . "
			  and item_id=" . $value['item_id'] . "")->row_array();
			if ($item_dt) {
				$this->db->where('item_id', $value['item_id']);
				$this->db->where('gudang_id', $gudang_id);
				$this->db->update("inv_item_dt", array(
					'item_id' => $value['item_id'],
					'gudang_id' => $gudang_id,
					'qty' => $item_dt['qty'] + $value['qty_keluar'],
					'nilai' => $item_dt['nilai'] + ($value['nilai_keluar'])
				));
			}
		}
		// hapus  transaksi harian
		$this->db->where('ref_id', $retrieve_header['id']);
		$this->db->where('tipe', 'PENGIRIMAN_SO');
		$this->db->delete('inv_transaksi_harian');
		/* Hapus jurnal */
		$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'INV_PENGIRIMAN_SO');
		/* Buka Posting */
		$data = array(
			'is_posting' => 0,
		);
		$this->db->where('id', $retrieve_header['id']);
		$this->db->update('inv_pengiriman_so_ht', $data);
		return array("status" => "OK", "data" => "Unposting Berhasil");
	}
	function inv_penerimaan_tanpa_po($no_transaksi, $lokasi_id)
	{
		$retrieve_header = $this->db->query(" select  * from inv_penerimaan_tanpa_po_ht where no_transaksi='" . $no_transaksi . "' and is_posting=1 and lokasi_id=" . $lokasi_id . "")->row_array();;
		if (empty($retrieve_header)) {
			return array("status" => "NOT OK", "data" => "Tidak ada Data Transaksi");
		} else {
			$chk = cek_periode($retrieve_header['tanggal'], $retrieve_header['lokasi_id']);
			if ($chk['status'] == false) {
				return array("status" => "NOT OK", "data" => $chk['message']);
			}
		}
		$gudang_id = $retrieve_header['gudang_id'];

		$data_transaksi = $this->db->query("select * from inv_transaksi_harian 
		 where ref_id=" . $retrieve_header['id'] . " and tipe='PENERIMAAN_TANPA_PO' ;")->result_array();

		/* balikan stok */
		foreach ($data_transaksi as $key => $value) {
			$item_dt = $this->db->query("select * from inv_item_dt  where gudang_id=" . $gudang_id . "
			  and item_id=" . $value['item_id'] . "")->row_array();
			if ($item_dt) {
				$this->db->where('item_id', $value['item_id']);
				$this->db->where('gudang_id', $gudang_id);
				$this->db->update("inv_item_dt", array(
					'item_id' => $value['item_id'],
					'gudang_id' => $gudang_id,
					'qty' => $item_dt['qty'] - $value['qty_masuk'],
					'nilai' => $item_dt['nilai'] - ($value['nilai_masuk'])
				));
			}
		}
		// hapus  transaksi harian
		$this->db->where('ref_id', $retrieve_header['id']);
		$this->db->where('tipe', 'PENERIMAAN_TANPA_PO');
		$this->db->delete('inv_transaksi_harian');
		/* Hapus jurnal */
		$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'INV_PENERIMAAN_TANPA_PO');
		/* Buka Posting */
		$data = array(
			'is_posting' => 0,
		);
		$this->db->where('id', $retrieve_header['id']);
		$this->db->update('inv_penerimaan_tanpa_po_ht', $data);
		return array("status" => "OK", "data" => "Unposting Berhasil");
	}
	function inv_penerimaan_pindah_gudang($no_transaksi, $lokasi_id)
	{
		$retrieve_header = $this->db->query(" select  * from inv_penerimaan_pindah_gudang_ht where no_transaksi='" . $no_transaksi . "' and is_posting=1 and lokasi_id=" . $lokasi_id . "")->row_array();;
		if (empty($retrieve_header)) {
			return array("status" => "NOT OK", "data" => "Tidak ada Data Transaksi");
		} else {
			$chk = cek_periode($retrieve_header['tanggal'], $retrieve_header['lokasi_id']);
			if ($chk['status'] == false) {
				return array("status" => "NOT OK", "data" => $chk['message']);
			}
		}
		$gudang_id = $retrieve_header['ke_gudang_id'];

		$data_transaksi = $this->db->query("select * from inv_transaksi_harian 
		 where ref_id=" . $retrieve_header['id'] . " and tipe='PENERIMAAN_PINDAH_GUDANG' ;")->result_array();

		/* balikan stok */
		foreach ($data_transaksi as $key => $value) {
			$item_dt = $this->db->query("select * from inv_item_dt  where gudang_id=" . $gudang_id . "
			  and item_id=" . $value['item_id'] . "")->row_array();
			if ($item_dt) {
				$this->db->where('item_id', $value['item_id']);
				$this->db->where('gudang_id', $gudang_id);
				$this->db->update("inv_item_dt", array(
					'item_id' => $value['item_id'],
					'gudang_id' => $gudang_id,
					'qty' => $item_dt['qty'] - $value['qty_masuk'],
					'nilai' => $item_dt['nilai'] - ($value['nilai_masuk'])
				));
			}
		}
		// hapus  transaksi harian
		$this->db->where('ref_id', $retrieve_header['id']);
		$this->db->where('tipe', 'PENERIMAAN_PINDAH_GUDANG');
		$this->db->delete('inv_transaksi_harian');
		/* Hapus jurnal */
		$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'INV_PENERIMAAN_PINDAH_GUDANG');
		/* Buka Posting */
		$data = array(
			'is_posting' => 0,
		);
		$this->db->where('id', $retrieve_header['id']);
		$this->db->update('inv_penerimaan_pindah_gudang_ht', $data);
		return array("status" => "OK", "data" => "Unposting Berhasil");
	}
	function inv_retur_pemakaian($no_transaksi, $lokasi_id)
	{
		$retrieve_header = $this->db->query(" select  * from inv_retur_pemakaian_ht where no_transaksi='" . $no_transaksi . "' and is_posting=1 and lokasi_id=" . $lokasi_id . "")->row_array();;
		if (empty($retrieve_header)) {
			return array("status" => "NOT OK", "data" => "Tidak ada Data Transaksi");
		} else {
			$chk = cek_periode($retrieve_header['tanggal'], $retrieve_header['lokasi_id']);
			if ($chk['status'] == false) {
				return array("status" => "NOT OK", "data" => $chk['message']);
			}
		}
		$gudang_id = $retrieve_header['gudang_id'];

		$data_transaksi = $this->db->query("select * from inv_transaksi_harian 
		 where ref_id=" . $retrieve_header['id'] . " and tipe='RETUR_PEMAKAIAN' ;")->result_array();

		/* balikan stok */
		foreach ($data_transaksi as $key => $value) {
			$item_dt = $this->db->query("select * from inv_item_dt  where gudang_id=" . $gudang_id . "
			  and item_id=" . $value['item_id'] . "")->row_array();
			if ($item_dt) {
				$this->db->where('item_id', $value['item_id']);
				$this->db->where('gudang_id', $gudang_id);
				$this->db->update("inv_item_dt", array(
					'item_id' => $value['item_id'],
					'gudang_id' => $gudang_id,
					'qty' => $item_dt['qty'] - $value['qty_masuk'],
					'nilai' => $item_dt['nilai'] - ($value['nilai_masuk'])
				));
			}
		}
		// hapus  transaksi harian
		$this->db->where('ref_id', $retrieve_header['id']);
		$this->db->where('tipe', 'RETUR_PEMAKAIAN');
		$this->db->delete('inv_transaksi_harian');
		/* Hapus jurnal */
		$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'INV_RETUR_PEMAKAIAN_BARANG');
		/* Buka Posting */
		$data = array(
			'is_posting' => 0,
		);
		$this->db->where('id', $retrieve_header['id']);
		$this->db->update('inv_retur_pemakaian_ht', $data);
		return array("status" => "OK", "data" => "Unposting Berhasil");
	}
	function inv_adjustment($no_transaksi, $lokasi_id)
	{
		$retrieve_header = $this->db->query(" select  * from inv_adj_ht where no_transaksi='" . $no_transaksi . "' and is_posting=1 and lokasi_id=" . $lokasi_id . "")->row_array();;
		if (empty($retrieve_header)) {
			return array("status" => "NOT OK", "data" => "Tidak ada Data Transaksi");
		} else {
			$chk = cek_periode($retrieve_header['tanggal'], $retrieve_header['lokasi_id']);
			if ($chk['status'] == false) {
				return array("status" => "NOT OK", "data" => $chk['message']);
			}
		}
		$gudang_id = $retrieve_header['gudang_id'];

		$data_transaksi = $this->db->query("select * from inv_transaksi_harian 
		 where ref_id=" . $retrieve_header['id'] . " and tipe='ADJUSTMENT' ;")->result_array();

		/* balikan stok */
		foreach ($data_transaksi as $key => $value) {
			$item_dt = $this->db->query("select * from inv_item_dt  where gudang_id=" . $gudang_id . "
			  and item_id=" . $value['item_id'] . "")->row_array();
			if ($item_dt) {
				$this->db->where('item_id', $value['item_id']);
				$this->db->where('gudang_id', $gudang_id);
				$this->db->update("inv_item_dt", array(
					'item_id' => $value['item_id'],
					'gudang_id' => $gudang_id,
					'qty' => $item_dt['qty'] - $value['qty_masuk'],
					'nilai' => $item_dt['nilai'] - ($value['nilai_masuk'])
				));
			}
		}
		// hapus  transaksi harian
		$this->db->where('ref_id', $retrieve_header['id']);
		$this->db->where('tipe', 'ADJUSTMENT');
		$this->db->delete('inv_transaksi_harian');
		/* Hapus jurnal */
		$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'ADJUSTMENT');
		/* Buka Posting */
		$data = array(
			'is_posting' => 0,
		);
		$this->db->where('id', $retrieve_header['id']);
		$this->db->update('inv_adj_ht', $data);
		return array("status" => "OK", "data" => "Unposting Berhasil");
	}
}
