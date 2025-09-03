<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class Validation
{
	function __construct()
	{
	}
	function cek_periode($tanggal)
	{
		$cek_periode = false;
		$CI =   &get_instance();

		$res = $CI->db->query("SELECT * FROM acc_periode_akunting where 
		'" . $tanggal . "' >= tgl_awal and '" . $tanggal . "' <= tgl_akhir
		and status=0")->row_array();
		if ($res) {
			$cek_periode = true;
		}
		return $cek_periode;
		
	}
	function ada_blm_posting_inventory($tanggal, $gudang_id)
	{
		$ada_blm_posting = false;
		$CI =   &get_instance();

		$res = $CI->db->query("SELECT * FROM inv_pemakaian_ht a INNER JOIN 
		inv_pemakaian_dt b ON a.id=b.inv_pemakaian_id
		WHERE gudang_id=" . $gudang_id . " and is_posting=0 and tanggal<'" . $tanggal . "'")->row_array();
		if ($res) {
			$ada_blm_posting = true;
		}
		$res = $CI->db->query("SELECT * FROM inv_penerimaan_tanpa_po_ht a INNER JOIN 
		inv_penerimaan_tanpa_po_dt b ON a.id=b.penerimaan_tanpa_po_id
		WHERE gudang_id=" . $gudang_id . " and is_posting=0  and tanggal<'" . $tanggal . "'")->row_array();
		if ($res) {
			$ada_blm_posting = true;
		}
		$res = $CI->db->query("SELECT * FROM inv_penerimaan_po_ht a INNER JOIN 
		inv_penerimaan_po_dt b ON a.id=b.penerimaan_po_hd_id
		WHERE gudang_id=" . $gudang_id . " and is_posting=0  and tanggal<'" . $tanggal . "'")->row_array();
		if ($res) {
			$ada_blm_posting = true;
		}
		$res = $CI->db->query("SELECT * FROM inv_penerimaan_pindah_gudang_ht a INNER JOIN 
		inv_penerimaan_pindah_gudang_dt b ON a.id=b.inv_penerimaan_pindah_gudang_id
		WHERE gudang_id=" . $gudang_id . " and is_posting=0 and tanggal<'" . $tanggal . "'")->row_array();
		if ($res) {
			$ada_blm_posting = true;
		}
		$res = $CI->db->query("SELECT * FROM inv_pindah_gudang_ht a INNER JOIN 
		inv_pindah_gudang_dt b ON a.id=b.inv_pindah_gudang_id
		WHERE gudang_id=" . $gudang_id . " and is_posting=0  and tanggal<'" . $tanggal . "'")->row_array();
		if ($res) {
			$ada_blm_posting = true;
		}

		$res = $CI->db->query("SELECT * FROM est_bkm_pemeliharaan_ht a INNER JOIN 
		est_bkm_pemeliharaan_item b ON a.id=b.bkm_pemeliharaan_id
		inner join gbm_organisasi c on a.rayon_afdeling_id=c.afdeling_id
		WHERE c.id=" . $gudang_id . " and c.tipe='GUDANG_VIRTUAL' and is_posting=0  
		and tanggal<'" . $tanggal . "'")->row_array();
		if ($res) {
			$ada_blm_posting = true;
		}

		return $ada_blm_posting;
	}

	function ada_sudah_posting_setelahnya_inventory($tanggal, $gudang_id)
	{
		$ada_sudah_posting_setelahnya = false;
		$CI =   &get_instance();

		$res = $CI->db->query("SELECT * FROM inv_pemakaian_ht a INNER JOIN 
		inv_pemakaian_dt b ON a.id=b.inv_pemakaian_id
		WHERE gudang_id=" . $gudang_id . " and is_posting=1 and tanggal>'" . $tanggal . "'")->row_array();
		if ($res) {
			$ada_sudah_posting_setelahnya = true;
		}
		$res = $CI->db->query("SELECT * FROM inv_penerimaan_tanpa_po_ht a INNER JOIN 
		inv_penerimaan_tanpa_po_dt b ON a.id=b.penerimaan_tanpa_po_id
		WHERE gudang_id=" . $gudang_id . " and is_posting=1  and tanggal>'" . $tanggal . "'")->row_array();
		if ($res) {
			$ada_sudah_posting_setelahnya = true;
		}
		$res = $CI->db->query("SELECT * FROM inv_penerimaan_po_ht a INNER JOIN 
		inv_penerimaan_po_dt b ON a.id=b.penerimaan_po_hd_id
		WHERE gudang_id=" . $gudang_id . " and is_posting=1  and tanggal>'" . $tanggal . "'")->row_array();
		if ($res) {
			$ada_sudah_posting_setelahnya = true;
		}
		$res = $CI->db->query("SELECT * FROM inv_penerimaan_pindah_gudang_ht a INNER JOIN 
		inv_penerimaan_pindah_gudang_dt b ON a.id=b.inv_penerimaan_pindah_gudang_id
		WHERE gudang_id=" . $gudang_id . " and is_posting=1 and tanggal>'" . $tanggal . "'")->row_array();
		if ($res) {
			$ada_sudah_posting_setelahnya = true;
		}
		$res = $CI->db->query("SELECT * FROM inv_pindah_gudang_ht a INNER JOIN 
		inv_pindah_gudang_dt b ON a.id=b.inv_pindah_gudang_id
		WHERE gudang_id=" . $gudang_id . " and is_posting=1  and tanggal>'" . $tanggal . "'")->row_array();
		if ($res) {
			$ada_sudah_posting_setelahnya = true;
		}

		$res = $CI->db->query("SELECT * FROM est_bkm_pemeliharaan_ht a INNER JOIN 
		est_bkm_pemeliharaan_item b ON a.id=b.bkm_pemeliharaan_id
		inner join gbm_organisasi c on a.rayon_afdeling_id=c.afdeling_id
		WHERE c.id=" . $gudang_id . " and c.tipe='GUDANG_VIRTUAL' and is_posting=1  
		and tanggal>'" . $tanggal . "'")->row_array();
		if ($res) {
			$ada_sudah_posting_setelahnya = true;
		}

		return $ada_sudah_posting_setelahnya;
	}
}
