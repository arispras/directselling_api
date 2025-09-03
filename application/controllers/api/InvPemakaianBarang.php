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

class InvPemakaianBarang extends BD_Controller
{
	public $user_id;
	public $theCredential;

	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('InvPemakaianBarangModel');
		$this->load->model('InvItemModel');
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
		b.nama as lokasi_afd,
		d.nama as gudang,
		c.nama as karyawan,
		e.nama as lokasi,
		f.nama as lokasi_traksi,
		g.user_full_name AS dibuat,
		h.user_full_name AS diubah,
		i.user_full_name AS diposting
		FROM inv_pemakaian_ht a 
		LEFT JOIN gbm_organisasi b ON a.lokasi_afd_id=b.id
		INNER JOIN gbm_organisasi d ON a.gudang_id=d.id
		INNER JOIN gbm_organisasi e ON a.lokasi_id=e.id
		INNER JOIN karyawan c on a.karyawan_id=c.id 
		LEFT JOIN gbm_organisasi f ON a.lokasi_traksi_id=f.id
		LEFT JOIN fwk_users g ON a.dibuat_oleh = g.id
		LEFT JOIN fwk_users h ON a.diubah_oleh = h.id
		LEFT JOIN fwk_users i ON a.diposting_oleh = i.id
		";
		$search = array('a.tanggal', 'b.nama', 'c.nama', 'a.no_transaksi');
		$where  = null;

		// $isWhere = " a.lokasi_id in
		// (select location_id from fwk_users_location where user_id=" . $this->user_id . ")";
		$isWhere = " 1=1";
		if ($param['tgl_mulai'] && $param['tgl_mulai']) {
			$isWhere = " a.tanggal between '" . $param['tgl_mulai'] . "' and '" . $param['tgl_akhir'] . "'";
		}
		if ($param['lokasi_id']) {
			$isWhere = $isWhere . " and a.lokasi_id =" . $param['lokasi_id'] . "";
		} else {
			$isWhere = $isWhere . " and  a.lokasi_id in
			(select location_id from fwk_users_location where user_id=" . $this->user_id . ")";
		}
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

	function index_get($id = '')
	{
		$retrieve = $this->InvPemakaianBarangModel->retrieve_by_id($id);
		$retrieve['detail'] = $this->InvPemakaianBarangModel->retrieve_detail($id);


		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function getAllBelumPemakaian_get($lokasi_id)
	{

		$retrieve = $this->db->query(
			" select a.*,b.id as inv_pemakaian_id from inv_pemakaian_ht a left join inv_retur_pemakaian_ht b
			on a.id=b.inv_pemakaian_id
			 where a.lokasi_id =" . $lokasi_id . " and a.is_posting=1 and 
			 b.id is null"
		)->result_array();

		// $retrieve;
		// $pemakaian = $this->db->query("select a.*, sum(b.qty) as qty from inv_pemakaian_ht a left join inv_pemakaian_dt b on a.id=b.inv_pemakaian_id group by a.id")->result_array();

		// $result=[];
		// foreach ($pemakaian as $x) {
		// 	$retur = $this->db->query("select a.*, sum(b.qty) as qty from inv_retur_pemakaian_ht a left join inv_retur_pemakaian_dt b on a.id=b.inv_retur_pemakaian_id where a.inv_pemakaian_id=".$x['id']." group by a.id")->row_array();
		// 	$x['qty'] = (int)$x['qty'];
		// 	$x['qty_retur'] = (int)$retur['qty'];
		// 	if ($x['qty_retur'] == null) {
		// 		$x['qty_retur'] = 0;
		// 	}
		// 	if ($x['qty_retur'] >= $x['qty']) {
		// 		$x['match'] = true;
		// 	}else {
		// 		$result[] = $x;
		// 	}
		// }

		// $retrieve = $result;


		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}


	function getAll_get()
	{

		$retrieve = $this->InvPemakaianBarangModel->retrieve_all();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getTraksi_get()
	{

		$retrieve = $this->InvPemakaianBarangModel->retrieve_traksi();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAllDetail_get($id = '')
	{
		$retrieve = $this->InvPemakaianBarangModel->retrieve_all_detail($id);

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}


	function index_post()
	{
		$input = $this->post();

		/* START VALIDASI BLOK TIDAK DIISI JIKA AKUN->Kelompok Biaya=PNN,PMK,PML*/
		$details = $input['details'];
		foreach ($details as $key => $value) {
			$blok_id = $value['blok_id']['id'];
			$kegiatan_id = $value['kegiatan_id']['id'];
			$traksi_id =  $value['traksi_id']['id'];

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
			if (is_null($traksi_id) || empty($traksi_id)) {
				$res_akun = $this->db->query("	SELECT a.kode AS kode_kegiatan,a.nama AS nama_kegiatan,b.kode AS kode_akun,b.nama AS nama_akun FROM acc_kegiatan a 
					INNER JOIN acc_akun b ON a.acc_akun_id=b.id WHERE b.kelompok_biaya 
					IN('TRK') 
					AND a.id=" . $kegiatan_id . "")->row_array();

				if ($res_akun) {
					$msg = "Kegiatan:" . $res_akun['nama_kegiatan'] . " harus diiisi Kendaraan/Alat Berat/Mesin";
					$this->set_response(array("status" => "NOT OK", "data" => $msg), REST_Controller::HTTP_OK);
					return;
				}
			}
		}
		/* END VALIDASI BLOK TIDAK DIISI */

		$input['dibuat_oleh'] = $this->user_id;
		$input['diubah_oleh'] = $this->user_id;

		$this->load->library('Autonumber');
		$input['no_transaksi'] = $this->autonumber->inv_pemakaian_barang($input['lokasi_id']['id'], $input['tanggal']);


		$res = $this->InvPemakaianBarangModel->create($input);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'inv_pemakaian_barang', 'action' => 'new', 'entity_id' => $res);
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
			$traksi_id =  $value['traksi_id']['id'];

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
			if (is_null($traksi_id) || empty($traksi_id)) {
				$res_akun = $this->db->query("	SELECT a.kode AS kode_kegiatan,a.nama AS nama_kegiatan,b.kode AS kode_akun,b.nama AS nama_akun FROM acc_kegiatan a 
					INNER JOIN acc_akun b ON a.acc_akun_id=b.id WHERE b.kelompok_biaya 
					IN('TRK') 
					AND a.id=" . $kegiatan_id . "")->row_array();

				if ($res_akun) {
					$msg = "Kegiatan:" . $res_akun['nama_kegiatan'] . " harus diiisi Kendaraan/Alat Berat/Mesin";
					$this->set_response(array("status" => "NOT OK", "data" => $msg), REST_Controller::HTTP_OK);
					return;
				}
			}
		}
		/* END VALIDASI BLOK TIDAK DIISI */

		$res = $this->InvPemakaianBarangModel->update($id, $data);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'inv_pemakaian_barang', 'action' => 'edit', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	public function index_delete($id)
	{

		$res = $this->InvPemakaianBarangModel->delete($id);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id' => $id)), 'entity' => 'inv_pemakaian_barang', 'action' => 'delete', 'entity_id' => $id);
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
		

		$retrieve_header = $this->InvPemakaianBarangModel->retrieve_by_id($id);
		$retrieve_detail = $this->InvPemakaianBarangModel->retrieve_detail($id);
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

		/* cek stok */
		$ada_stok_minus = false;
		$ada_kegiatan_kosong = false;
		$result_stok = array();
		foreach ($retrieve_detail as $key => $value) {
			$stok = $this->InvItemModel->getStok($value['item_id'], $retrieve_header['gudang_id']);
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
		/* cek Kegiatan */
		$ada_kegiatan_kosong = false;
		$result_kegiatan = array();
		foreach ($retrieve_detail as $key => $value) {
			if (!($value['acc_akun_biaya_id']) || is_null($value['acc_akun_biaya_id'])) {
				$ada_kegiatan_kosong = true;
				$kegiatan = array('kode' => $value['kode_barang'], 'nama' => $value['nama_barang'], 'stok' => 'Kegiatan Kosong');
				$result_kegiatan[] = $kegiatan;
			}
		}
		if ($ada_kegiatan_kosong) {
			$this->set_response(array("status" => "NOT OK", "data" => $result_kegiatan, "message" => null), REST_Controller::HTTP_OK);
			return;
		}

		/* START VALIDASI BLOK TIDAK DIISI JIKA AKUN->Kelompok Biaya=PNN,PMK,PML*/
	
		foreach ($retrieve_detail as $key => $value) {
			$blok_id = $value['blok_id'];
			$kegiatan_id = $value['kegiatan_id'];
			$traksi_id =  $value['traksi_id'];

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
			if (is_null($traksi_id) || empty($traksi_id)) {
				$res_akun = $this->db->query("	SELECT a.kode AS kode_kegiatan,a.nama AS nama_kegiatan,b.kode AS kode_akun,b.nama AS nama_akun FROM acc_kegiatan a 
					INNER JOIN acc_akun b ON a.acc_akun_id=b.id WHERE b.kelompok_biaya 
					IN('TRK') 
					AND a.id=" . $kegiatan_id . "")->row_array();

				if ($res_akun) {
					$msg = "Kegiatan:" . $res_akun['nama_kegiatan'] . " harus diiisi Kendaraan/Alat Berat/Mesin";
					$this->set_response(array("status" => "NOT OK", "data" => $msg), REST_Controller::HTTP_OK);
					return;
				}
			}
		}
		/* END VALIDASI BLOK TIDAK DIISI */


		$lokasi_traksi = null;
		$inter_akun_id = null;

		if ($retrieve_header['tipe'] == 'TRAKSI') {

			/* cek Inter unit Akun */
			$retrieve_inter_akun = $this->db->query("SELECT * FROM acc_inter_unit
			where tipe='INTER' ")->result_array();
			$akun_inter = array();
			foreach ($retrieve_inter_akun as $key => $akun) {
				$akun_inter[$akun['lokasi_id']][$akun['lokasi_id_2']] = $akun['acc_akun_id'];
			}
			$retrieve_traksi = $this->db->query("SELECT * FROM gbm_organisasi
			where id=" . $retrieve_header["lokasi_traksi_id"])->row_array();
			$lokasi_traksi = $retrieve_traksi['parent_id']; // dapatkan parentnya utk dpt lokasinya
			$lokasi1 = $retrieve_header["lokasi_id"];
			$lokasi2 = $lokasi_traksi;
			if ($akun_inter[$lokasi1][$lokasi2]) {
				$inter_akun_id = $akun_inter[$lokasi1][$lokasi2];
			}
			if ($akun_inter[$lokasi2][$lokasi1]) {
				$inter_akun_id = $akun_inter[$lokasi2][$lokasi1];
			}
		}

		$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'INV_PEMAKAIAN_BARANG');
		// Data HEADER
		$this->load->library('Autonumber');
		$no_jurnal = $this->autonumber->jurnal_auto($retrieve_header['lokasi_id'], $retrieve_header['tanggal'], 'INVPM');

		$dataH = array(
			'no_jurnal' => $no_jurnal,
			'lokasi_id' => $retrieve_header['lokasi_id'],
			'tanggal' => $retrieve_header['tanggal'],
			'no_ref' => $retrieve_header['no_transaksi'],
			'ref_id' => $retrieve_header['id'],
			'tipe_jurnal' => 'AUTO',
			'modul' => 'INV_PEMAKAIAN_BARANG',
			'keterangan' => 'INV_PEMAKAIAN_BARANG',
			'is_posting' => 1
		);
		$id_header = $this->AccJurnalModel->create_header($dataH);

		$nilai = 0;
		foreach ($retrieve_detail as $key => $value) {
			$item_dt = $this->db->query("select * from inv_item_dt  where gudang_id=" . $retrieve_header['gudang_id'] . "
			  and item_id=" . $value['item_id'] . "")->row_array();
			$avg_price = 0;
			if ($item_dt) {
				$avg_price = $item_dt['nilai'] / $item_dt['qty'];
			}
			$nilai =  ($avg_price * $value['qty']);
			$keterangan = '';
			if (is_null($value['nama_blok']) == false) {
				$keterangan = 'Pemakaian Item:' . $value['nama_barang'] . ', qty:' . $value['qty'] . ' ' . $value['uom'] . ',Blok/Mesin:' . $value['nama_blok'];
			} else if (is_null($value['nama_kendaraan']) == false) {
				$keterangan =	'Pemakaian Item:' . $value['nama_barang'] . ', qty:' . $value['qty'] . ' ' . $value['uom'] . ',Unit:' . $value['nama_kendaraan'];
			} else {
				$keterangan = 'Pemakaian Item:' . $value['nama_barang'] . ', qty:' . $value['qty'] . ' ' . $value['uom'];
			}
			if (!$inter_akun_id || is_null($inter_akun_id)) {
				$dataCr = array(
					'lokasi_id' => $retrieve_header['lokasi_id'],
					'jurnal_id' => $id_header,
					'acc_akun_id' => ($value['acc_akun_id']), //akun 
					'debet' => 0,
					'kredit' => $nilai,
					'ket' => $keterangan,
					'no_referensi' => $retrieve_header['no_transaksi'],
					'referensi_id' => NULL,
					'blok_stasiun_id' => NULL,
					'kegiatan_id' => NULL, //kegiatan ,
					'item_id' => $value['item_id'], //karyawan,

				);

				$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataCr);
				$dataDr = array(
					'lokasi_id' => $retrieve_header['lokasi_id'],
					'jurnal_id' => $id_header,
					'acc_akun_id' => $value['acc_akun_biaya_id'], //akun ,
					'debet' =>  $nilai,
					'kredit' => 0,
					'ket' => $keterangan,
					'no_referensi' => $retrieve_header['no_transaksi'],
					'referensi_id' => NULL,
					'item_id' => $value['item_id'], //item,
					'umur_tanam_blok' => $value['umur_tanam_blok'],
					'blok_stasiun_id' => $value['blok_id'],
					'kegiatan_id' => $value['kegiatan_id'],
					'kendaraan_mesin_id' => $value['traksi_id'],
					'divisi_id' => $retrieve_header['lokasi_afd_id'],
				);
				$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataDr);
			} else {
				$dataCr = array(
					'lokasi_id' => $retrieve_header['lokasi_id'],
					'jurnal_id' => $id_header,
					'acc_akun_id' => ($value['acc_akun_id']), //akun 
					'debet' => 0,
					'kredit' => $nilai,
					'ket' => $keterangan,
					'no_referensi' => $retrieve_header['no_transaksi'],
					'referensi_id' => NULL,
					'blok_stasiun_id' => NULL,
					'kegiatan_id' => NULL, //kegiatan ,
					'item_id' => $value['item_id'], //item,
				);
				// $this->set_response(array("status" => "NOT OK", "data" =>$dataCr), REST_Controller::HTTP_OK);
				// return;
				$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataCr);
				$dataDr = array(
					'lokasi_id' => $retrieve_header['lokasi_id'],
					'jurnal_id' => $id_header,
					'acc_akun_id' => $inter_akun_id, //akun ,
					'debet' =>  $nilai,
					'kredit' => 0,
					'ket' => $keterangan,
					'no_referensi' => $retrieve_header['no_transaksi'],
					'referensi_id' => NULL,
					'item_id' => $value['item_id'], //item,
					'umur_tanam_blok' => $value['umur_tanam_blok'],
					'blok_stasiun_id' => $value['blok_id'],
					'kegiatan_id' => $value['kegiatan_id'],
					'kendaraan_mesin_id' => $value['traksi_id']
				);
				$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataDr);

				$dataDr = array(
					'lokasi_id' => $lokasi_traksi,
					'jurnal_id' => $id_header,
					'acc_akun_id' => ($value['acc_akun_biaya_id']), //akun ,
					'debet' =>  $nilai,
					'kredit' => 0,
					'ket' => $keterangan,
					'no_referensi' => $retrieve_header['no_transaksi'],
					'referensi_id' => NULL,
					'item_id' => $value['item_id'], //item,
					'umur_tanam_blok' => $value['umur_tanam_blok'],
					'blok_stasiun_id' => $value['blok_id'],
					'kegiatan_id' => $value['kegiatan_id'],
					'kendaraan_mesin_id' => $value['traksi_id'],
					'item_id' => $value['item_id'], //karyawan,
					'divisi_id' => $retrieve_header['lokasi_afd_id']
				);
				$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataDr);
				$dataCr = array(
					'lokasi_id' => $lokasi_traksi,
					'jurnal_id' => $id_header,
					'acc_akun_id' => $inter_akun_id, //akun 
					'debet' => 0,
					'kredit' => $nilai,
					'ket' => $keterangan,
					'no_referensi' => $retrieve_header['no_transaksi'],
					'referensi_id' => NULL,
					'blok_stasiun_id' => NULL,
					'kegiatan_id' => NULL, //kegiatan ,
					'item_id' => $value['item_id'], //karyawan,
				);

				$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataCr);
			}
		}
		$data['diposting_oleh'] = $this->user_id;
		$data['tanggal'] =	$retrieve_header['tanggal'];
		$res = $this->InvPemakaianBarangModel->posting($id, $data);

		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id' => $id)), 'entity' => 'inv_pemakaian_barang', 'action' => 'posting', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function print_slip_get($segment_3 = '')
	{

		$id = (int)$segment_3;
		$data = [];

		$queryHeader = "SELECT a.*,
		b.nama as lokasi_afd,
		f.nama as lokasi_traksi,
		d.nama as gudang,
		c.nama as karyawan,
		e.nama as lokasi
		FROM inv_pemakaian_ht a 
		left JOIN gbm_organisasi b ON a.lokasi_afd_id=b.id
		left JOIN gbm_organisasi d ON a.gudang_id=d.id
		left JOIN gbm_organisasi e ON a.lokasi_id=e.id
		left JOIN gbm_organisasi f ON a.lokasi_traksi_id=f.id
		left JOIN karyawan c on a.karyawan_id=c.id  WHERE a.id=" . $id . "";
		$dataHeader = $this->db->query($queryHeader)->row_array();

		$queryDetail = "SELECT a.*,
		b.kode as kode_barang,
		b.nama as nama_barang,
		f.nama as uom,
		c.kode as blok,
		d.nama as nama_kendaraan,
		e.nama as nama_kegiatan,
		d.kode as kode_kendaraan 
		FROM inv_pemakaian_dt a 
		inner join inv_item b on a.item_id=b.id 
		left join gbm_organisasi c on a.blok_id=c.id 
		left join trk_kendaraan d on a.traksi_id=d.id 
		left join acc_kegiatan e on a.kegiatan_id=e.id 
		left join gbm_uom f on b.uom_id=f.id WHERE  a.inv_pemakaian_id = " . $id . "";
		$dataDetail = $this->db->query($queryDetail)->result_array();



		// $perminta = $this->InvPermintaanBarangModel->print_slip($id);
		$data['header'] = 	$dataHeader;
		$data['detail'] = 	$dataDetail;

		$html = $this->load->view('InvSlipPemakaian', $data, true);

		$filename = 'report_' . time();
		$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		echo $html;
	}


	function laporan_pemakaian_detail_post()
	{
		$format_laporan =  $this->post('format_laporan', true);

		$data = [];

		$input = [
			'lokasi_id' => 252,
			'gudang_id' => 740,
			'item_id'   => 9,
			'kegiatan_id'   => 61,
			// 'blok_id'   => 577,
			// 'traksi_id'   => 56,
			// 'ket'   => 	'RENTAL',
			'tgl_mulai' => '2022-09-01',
			'tgl_akhir' => '2022-09-31',
		];

		$lokasi_id = $this->post('lokasi_id', true);
		$gudang_id = $this->post('gudang_id', true);
		$tanggal_awal = $this->post('tgl_mulai', true);
		$tanggal_akhir = $this->post('tgl_akhir', true);
		$item_id = $this->post('item_id', true);
		$kegiatan_id = $this->post('kegiatan_id', true);
		$blok_id = $this->post('blok_id', true);
		$traksi_id = $this->post('traksi_id', true);
		$ket = $this->post('ket', true);

		// $lokasi_id = $input['lokasi_id'];
		// $gudang_id = $input['gudang_id'];
		// $item_id = $input['item_id'];
		// $kegiatan_id = $input['kegiatan_id'];
		// $blok_id = $input['blok_id'];
		// $traksi_id = $input['traksi_id'];
		// $ket = $input['ket'];
		// $tanggal_awal = $input['tgl_mulai'];
		// $tanggal_akhir = $input['tgl_akhir'];

		$queryPemakaian = "SELECT
		a.qty as qty,
		b.no_transaksi,
		b.tanggal as tanggal,
		a.ket,
		d.nama as uom,
		c.nama as item,
		c.kode as kode,
		e.nama as gudang,
		h.no_transaksi as no_transaksi_permintaan,
		h.tanggal as tanggal_permintaan,
		a.id AS id,
		f.nama as blok_mesin, 
		g.nama as kegiatan,
		i.nama as kendaraan,
		i.kode as kode_kendaraan,
		a.ket as keterangan
		
		FROM inv_pemakaian_dt a
		
		INNER JOIN inv_pemakaian_ht b on a.inv_pemakaian_id=b.id
		INNER JOIN inv_item c on a.item_id=c.id
		INNER JOIN gbm_organisasi e on b.gudang_id=e.id
		LEFT JOIN gbm_uom d on c.uom_id=d.id
		left join gbm_organisasi f on a.blok_id=f.id 
		LEFT JOIN acc_kegiatan g on a.kegiatan_id=g.id
		LEFT JOIN trk_kendaraan i on a.traksi_id=i.id
    	LEFT JOIN inv_permintaan_ht h on b.inv_permintaan_id=h.id
        
		where b.tanggal between  '" . $tanggal_awal . "' and  '" . $tanggal_akhir . "'	
		";

		$filter_lokasi = "Semua";
		if ($lokasi_id) {
			$queryPemakaian = $queryPemakaian . " and b.lokasi_id=" . $lokasi_id . "";
			$res = $this->db->query("select * from gbm_organisasi where id=" . $lokasi_id . "")->row_array();
			$filter_lokasi = $res['nama'];
		}
		$filter_gudang = "Semua";
		if ($gudang_id) {
			$queryPemakaian = $queryPemakaian . " and b.gudang_id=" . $gudang_id . "";
			$res = $this->db->query("select * from gbm_organisasi where id=" . $gudang_id . "")->row_array();
			$filter_gudang = $res['nama'];
		}

		$filter_item = "Semua";
		if ($item_id) {
			$queryPemakaian = $queryPemakaian . " and a.item_id=" . $item_id . "";
			$res = $this->db->query("select * from inv_item where id=" . $item_id . "")->row_array();
			$filter_item = $res['nama'];
		}
		$filter_kegiatan = "Semua";
		if ($kegiatan_id) {
			$queryPemakaian = $queryPemakaian . " and a.kegiatan_id=" . $kegiatan_id . "";
			$res = $this->db->query("select * from acc_kegiatan where id=" . $kegiatan_id . "")->row_array();
			$filter_kegiatan = $res['nama'];
		}
		$filter_blok = "Semua";
		if ($blok_id) {
			$queryPemakaian = $queryPemakaian . " and a.blok_id=" . $blok_id . "";
			$res = $this->db->query("select * from gbm_organisasi where id=" . $blok_id . "")->row_array();
			$filter_blok = $res['nama'];
		}
		$filter_traksi = "Semua";
		if ($traksi_id) {
			$queryPemakaian = $queryPemakaian . " and a.traksi_id=" . $traksi_id . "";
			$res = $this->db->query("select * from trk_kendaraan where id=" . $traksi_id . "")->row_array();
			$filter_traksi = $res['nama'];
		}

		$filter_ket = "Semua";
		if ($ket) {
			$queryPemakaian = $queryPemakaian . " and a.ket='" . $ket . "'";
			$res = $this->db->query("select * from inv_pemakaian_dt where ket='" . $ket . "'")->row_array();
			$filter_ket = $res['ket'];
		}

		$dataPo = $this->db->query($queryPemakaian)->result_array();

		$data['po'] = 	$dataPo;
		$data['filter_gudang'] = 	$filter_gudang;
		$data['filter_lokasi'] = 	$filter_lokasi;
		$data['filter_blok'] = 		$filter_blok;
		$data['filter_traksi'] = 	$filter_traksi;
		$data['filter_kegiatan'] = 	$filter_kegiatan;
		$data['filter_item'] = 		$filter_item;
		$data['filter_ket'] = 		$filter_ket;
		$data['filter_tgl_awal'] = 	$tanggal_awal;
		$data['filter_tgl_akhir'] = $tanggal_akhir;
		$data['format_laporan'] = $format_laporan;


		$html = $this->load->view('Inv_Pemakaian_Barang_Laporan', $data, true);

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		// echo $html;

		if ($format_laporan == 'xls') {
			// $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			// $spreadsheet = $reader->loadFromString($html);
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
	public function laporan_pemakaian_by_tanggal_post()
	{
		error_reporting(0);
		$format_laporan =  $this->post('format_laporan', true);

		$data = [];


		$lokasi_id = $this->post('lokasi_id', true);
		$gudang_id = $this->post('gudang_id', true);
		$tgl_mulai = $this->post('tgl_mulai', true);
		$tgl_akhir = $this->post('tgl_akhir', true);
		$kategori_item_id = $this->post('kategori_item_id', true);



		$queryPemakaian = "SELECT
		sum(a.qty) as qty,
		b.tanggal as tanggal,
		d.nama as uom,
		c.nama as nama_item,
		c.kode as kode_item,
		a.item_id,
		h.nama as kategori
		FROM inv_pemakaian_dt a
		INNER JOIN inv_pemakaian_ht b on a.inv_pemakaian_id=b.id
		INNER JOIN inv_item c on a.item_id=c.id
		INNER JOIN gbm_organisasi e on b.gudang_id=e.id
		LEFT JOIN gbm_uom d on c.uom_id=d.id
		INNER JOIN inv_kategori h on c.inv_kategori_id=h.id
		where b.tanggal between  '" . $tgl_mulai . "' and  '" . $tgl_akhir . "'	
		 ";

		$filter_lokasi = "Semua";
		if ($lokasi_id) {
			$queryPemakaian = $queryPemakaian . " and b.lokasi_id=" . $lokasi_id . "";
			$res = $this->db->query("select * from gbm_organisasi where id=" . $lokasi_id . "")->row_array();
			$filter_lokasi = $res['nama'];
		}
		$filter_gudang = "Semua";
		if ($gudang_id) {
			$queryPemakaian = $queryPemakaian . " and b.gudang_id=" . $gudang_id . "";
			$res = $this->db->query("select * from gbm_organisasi where id=" . $gudang_id . "")->row_array();
			$filter_gudang = $res['nama'];
		}

		$filter_kategori_item = "Semua";
		if ($kategori_item_id) {
			$queryPemakaian = $queryPemakaian . " and c.inv_kategori_id=" . $kategori_item_id . "";
			$res = $this->db->query("select * from inv_kategori where id=" . $kategori_item_id . "")->row_array();
			$filter_kategori_item = $res['nama'];
		}

		$queryPemakaian = $queryPemakaian . " group by b.tanggal,d.nama,c.nama,c.kode ,a.item_id,h.nama ";
		$queryPemakaian = $queryPemakaian . " order by c.nama";
		$arr_item = array();
		$arr_pmk = array();
		$res_pmk = $this->db->query($queryPemakaian)->result_array();
		foreach ($res_pmk as $key => $value) {
			$item = array("kode_item" => $value['kode_item'], "nama_item" => $value['nama_item'], "uom" => $value['uom']);
			$arr_item[$value['item_id']] = $item;
			$arr_pmk[$value['item_id']][$value['tanggal']] = $value['qty'];
		}

		$d1 = new DateTime($tgl_mulai);
		$d2 = new DateTime($tgl_akhir);
		$interval = $d1->diff($d2);
		$jumlah_hari = $interval->days;

		// var_dump($arr_item);exit();
		// $q0 = "SELECT a.*,b.nama AS jenis FROM inv_item a INNER JOIN inv_kategori b
		// ON a.inv_kategori_id=b.id 
		// where a.inv_kategori_id=" . $kategori_item_id . "";


		// $q0 =	$q0 . " order by a.nama";
		// $arrhd = $this->db->query($q0)->result_array();
		$no = 0;
		$strNo = '';

		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report();
		} else {
			$html = get_header_report_v2();
		}
		$html = $html . '<div class="row">
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
		<h3 class="title">LAPORAN PEMAKAIAN </h3>
		<table class="no_border" style="width:30%">
				  <tr>
						  <td>Periode</td>
						  <td>:</td>
						  <td>'. tgl_indo($tgl_mulai) . ' - ' . tgl_indo($tgl_akhir) .  '</td>
				  </tr>
				  <tr>
						  <td>Gudang</td>
						  <td>:</td>
						  <td>' . $filter_gudang . '</td>
				  </tr>
				  <tr>	
						  <td>Kategori</td>
						  <td>:</td>
						  <td>' . $filter_kategori_item  .  '</td>
				  </tr>
				  
		  </table>
				  <br>';
		$html = $html . "	
		<table width='100%' style='border-collapse: collapse;'>
		<thead>
		<tr>
			<th rowspan=2 >No</th>
			<th rowspan=2>Nama</th>
			<th rowspan=2>Kode</th>
			<th rowspan=2>Uom</th>
			<th colspan=" . (($jumlah_hari + 1) * 1) . "  style='text-align: center'> Tanggal  </th>
			
			<th rowspan=2  style='text-align: right'>Jumlah</th>
		</tr>
		";
		$html = $html . "<tr>";
		while ($d1 <= $d2) {
			$ddmm = $d1->format('d-m');
			$dd = $d1->format('d');
			// $html = $html . "<td colspan='2' style='text-align: center'>" . $ddmm . "</td>";
			$html = $html . "<th colspan='1' style='text-align: center'>" . $dd . "</th>";
			$d1->modify('+1 day');
		}
		$html = $html . "</tr>";

		$html = $html . "</thead>";
		$qtyPerHari = array();
		$qtyPerHari = [];
		$nilaiPerHari = array();
		$nilaiPerHari = [];
		$totalNilai = 0;
		$totalQty = 0;
		foreach ($arr_item as $key => $item) {

			$no++;
			// $actual_link = "http://$_SERVER[HTTP_HOST]" . "/plantation-api/api/GlobalReport/trk_pemakaian_inventory/" . $hd['id'] . "/" . $tgl_mulai . "/" . $tgl_akhir .   "";
			$html = $html . "<tr>";
			$html = $html . "<td style='text-align: center'>" . $no . "</td>";
			// $html = $html . "<td style='text-align: left'> <a href='" . $actual_link . "" . "' target='_blank'> " . $hd['kode'] . " </a></td>";
			$html = $html . "<td style='text-align: left'>  " . $item['nama_item'] . " </td>";
			$html = $html . "<td style='text-align: center'> " . $item['kode_item'] . "</td>";
			$html = $html . "<td style='text-align: center'> " . $item['uom'] . "</td>";

			$d1 = new DateTime($tgl_mulai);
			$jum_qty = 0;
			$jum_nilai = 0;
			while ($d1 <= $d2) {
				$tgl = $d1->format('Y-m-d');

				// $qTraksi = "SELECT sum(km_hm_jumlah)as jumlah_km_hm,
				// 	a.status_kendaraan	
				// FROM trk_kegiatan_kendaraan_ht a
				// left JOIN trk_kegiatan_kendaraan_log b ON b.trk_kegiatan_kendaraan_id=a.id
				// left JOIN gbm_organisasi c ON b.blok_id=c.id
				// left JOIN acc_kegiatan d ON b.acc_kegiatan_id=d.id
				// left JOIN trk_kendaraan e on a.kendaraan_id=e.id
				// left JOIN gbm_organisasi f on a.traksi_id=f.id
				// where a.tanggal =  '" . $tgl . "'	
				// and a.kendaraan_id=" . $hd['id'] . "
				// group by a.status_kendaraan";
				// $resTraksi = $this->db->query($qTraksi)->row_array();
				// $jum_qty = $jum_qty + $resTraksi['jumlah_km_hm'];
				// $totalQty = $totalQty + $resTraksi['jumlah_km_hm'];
				// if ($resTraksi['status_kendaraan']=='BREAKDOWN'){
				// 	$html = $html . "<td style='text-align: right;color:red'>" . ($resTraksi['status_kendaraan']) . " </td>";

				// }else{
				// 	$html = $html . "<td style='text-align: right'>" . ($resTraksi['status_kendaraan']) . " </td>";

				// }
				$qty = 0;
				if ($arr_pmk[$key][$tgl]) {
					// echo ($arr_pmk[$key][$tgl] .'<br>');
					$qty = $arr_pmk[$key][$tgl];
				}
				$jum_qty = $jum_qty + $qty;
				$html = $html . "<td style='text-align: right'> " . number_format($qty) . " </td>";
				$yymmdd = $d1->format('Ymd');

				if (array_key_exists($yymmdd, $qtyPerHari)) {
					$qtyPerHari[$yymmdd] = $qtyPerHari[$yymmdd] + $qty;
				} else {
					$qtyPerHari[$yymmdd] = $qty;
				}
				$d1->modify('+1 day');
			}
			$html = $html . "<td style='text-align: right'>" . number_format($jum_qty) . " </td>";
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
}
