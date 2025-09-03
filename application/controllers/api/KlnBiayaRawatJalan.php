<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

class KlnBiayaRawatJalan extends  BD_Controller
{
	public $user_id;
	public $theCredential;

	function __construct()
	{
		parent::__construct();
		$this->load->model('KlnBiayaRawatJalanModel');
		$this->load->model('M_DatatablesModel');
		$this->load->model('AccJurnalModel');
		$this->load->model('InvItemModel');
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
		b.nama AS nama_biaya	
		from kln_biaya_rawat_jalan a
		left join kln_jenis_biaya b on a.biaya_id=b.id";
		$search = array('a.tanggal',   'a.keterangan', 'b.nama');
		$where  = null;

		$isWhere = null;
		$isWhere = " 1=1 ";
		// if ($param['tgl_mulai'] && $param['tgl_mulai']) {
		// 	$isWhere = " a.tanggal between '" . $param['tgl_mulai'] . "' and '" . $param['tgl_akhir'] . "'";
		// }

		if (!empty($param['rawat_jalan_id'])) {

			$isWhere = $isWhere .  "  and a.rawat_jalan_id=" . $param['rawat_jalan_id'] . "";
		}

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	function index_get($segment_3 = '')
	{
		$id = $segment_3;
		$retrieve = $this->KlnBiayaRawatJalanModel->retrieve($id);
		// $retrieve['detail'] = $this->KlnBiayaRawatJalanModel->retrieve_detail($id);

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAll_get()
	{

		$retrieve = $this->KlnBiayaRawatJalanModel->retrieve_all_kategori();

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

		$this->load->library('Autonumber');
		// $input['no_transaksi'] = $this->autonumber->inv_adj($input['lokasi_id']['id'], $input['tanggal']);

		$res =  $this->KlnBiayaRawatJalanModel->create($input);
		// $this->set_response(array("status" => "OK", "data" => $input['no_transaksi']), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'inv_adj', 'action' => 'new', 'entity_id' => $res);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $input['no_transaksi']), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function index_put($segment_3 = '')
	{
		$input = $this->put();
		$input['diubah_oleh'] = $this->user_id;

		$id = (int)$segment_3;
		$kategori = $this->KlnBiayaRawatJalanModel->retrieve($id);
		if (empty($kategori)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$res =   $this->KlnBiayaRawatJalanModel->update($kategori['id'], $input);
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'inv_adj', 'action' => 'edit', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $input['no_transaksi']), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function index_delete($segment_3 = '')
	{

		$id = (int)$segment_3;
		$kategori = $this->KlnBiayaRawatJalanModel->retrieve($id);
		if (empty($kategori)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$res =  $this->KlnBiayaRawatJalanModel->delete($kategori['id']);
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id' => $id)), 'entity' => 'inv_adj', 'action' => 'delete', 'entity_id' => $id);
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

		$retrieve_header = $this->KlnBiayaRawatJalanModel->retrieve($id);
		// CEK PERIODE SDH ADA ATAU SDH CLOSE//
		$chk = cek_periode($retrieve_header['tanggal'], $retrieve_header['lokasi_id']);
		if ($chk['status'] == false) {
			$this->set_response(array("status" => "NOT OK", "data" => $chk['message']), REST_Controller::HTTP_OK);
			return;
		}
		//==============//

		if (empty($retrieve_header)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Untuk diposting"), REST_Controller::HTTP_OK);
			return;
		} else {
			if ($retrieve_header['is_posting'] == 1) {
				$this->set_response(array("status" => "NOT OK", "data" => "Data Sudah diposting"), REST_Controller::HTTP_OK);
				return;
			}
		}
		$retrieve_detail = $this->KlnBiayaRawatJalanModel->retrieve_detail($id);

		/* cek stok */
		$ada_stok_minus = false;
		$ada_kegiatan_kosong = false;
		$result_stok = array();
		foreach ($retrieve_detail as $key => $value) {
			if ($value['qty'] < 0) {
				$stok = $this->InvItemModel->getStok($value['item_id'], $retrieve_header['gudang_id']);
				$cek = $stok - ($value['qty'] * -1);
				if ($cek < 0) {
					$ada_stok_minus = true;
					$item = array('kode' => $value['kode_barang'], 'nama' => $value['nama_barang'], 'stok' => $cek);
					$result_stok[] = $item;
				}
			}
		}
		if ($ada_stok_minus) {
			$this->set_response(array("status" => "NOT OK", "data" => $result_stok), REST_Controller::HTTP_OK);
			return;
		}

		$retrieve_akun = $this->db->query("SELECT * FROM acc_auto_jurnal
		 where kode='INV_ADJUSTMENT_STOK'")->row_array();
		if (empty($retrieve_akun)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Setting Auto Jurnal belum ada"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$akun_kredit =	$retrieve_akun['acc_akun_id_kredit'];
		$akun_debet =	$retrieve_akun['acc_akun_id_debet'];
		// $this->set_response(array("status" => "NOT OK", "data" => $retrieve_akun), REST_Controller::HTTP_NOT_FOUND);
		// return;
		$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'INV_ADJ_STOK');
		// Data HEADER
		$this->load->library('Autonumber');
		$no_jurnal = $this->autonumber->jurnal_auto($retrieve_header['lokasi_id'], $retrieve_header['tanggal'], 'KlnBiayaRawatJalan');

		$dataH = array(
			'no_jurnal' => $no_jurnal,
			'lokasi_id' => $retrieve_header['lokasi_id'],
			'tanggal' => $retrieve_header['tanggal'],
			'no_ref' => $retrieve_header['no_transaksi'],
			'ref_id' => $retrieve_header['id'],
			'tipe_jurnal' => 'AUTO',
			'modul' => 'INV_ADJ_STOK',
			'keterangan' => 'INV_ADJ_STOK',
			'is_posting' => 1,
		);
		$id_header = $this->AccJurnalModel->create_header($dataH);


		$total_nilai = 0;
		foreach ($retrieve_detail as $key => $value) {
			$nilai = (($value['harga'] * $value['qty']));
			$total_nilai = $total_nilai + $nilai;
			if ($value['qty'] > 0) {

				$dataDebet = array(
					'lokasi_id' => $retrieve_header['lokasi_id'],
					'jurnal_id' => $id_header,
					'acc_akun_id' => ($value['acc_akun_id']), //akun 
					'debet' => $nilai,
					'kredit' => 0,
					'ket' => 'Adjustment Stok :' . $value['nama_barang'] . ', qty:' . $value['qty'] . ' ' . $value['uom'],
					'no_referensi' => $retrieve_header['no_transaksi'],
					'referensi_id' => NULL,
					'blok_stasiun_id' => NULL,
					'kegiatan_id' => NULL, //kegiatan ,
					'kendaraan_mesin_id' => NULL
				);

				$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataDebet);
				$dataKredit = array(
					'lokasi_id' => $retrieve_header['lokasi_id'],
					'jurnal_id' => $id_header,
					'acc_akun_id' => $akun_kredit, //akun ,
					'debet' => 0,
					'kredit' => $nilai,
					'ket' => 'Adjustment Stok :' . $value['nama_barang'] . ', qty:' . $value['qty'] . ' ' . $value['uom'],
					'no_referensi' => $retrieve_header['no_transaksi'],
					'referensi_id' => NULL,
					'blok_stasiun_id' => NULL,
					'kegiatan_id' => NULL,
					'kendaraan_mesin_id' => NULL
				);
				$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataKredit);
			} else {

				$dataDebet = array(
					'lokasi_id' => $retrieve_header['lokasi_id'],
					'jurnal_id' => $id_header,
					'acc_akun_id' => $akun_debet, //akun 
					'debet' => ($nilai * -1),
					'kredit' => 0,
					'ket' => 'Adjustment Stok :' . $value['nama_barang'] . ', qty:' . $value['qty'] . ' ' . $value['uom'],
					'no_referensi' => $retrieve_header['no_transaksi'],
					'referensi_id' => NULL,
					'blok_stasiun_id' => NULL,
					'kegiatan_id' => NULL, //kegiatan ,
					'kendaraan_mesin_id' => NULL
				);

				$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataDebet);
				$dataKredit = array(
					'lokasi_id' => $retrieve_header['lokasi_id'],
					'jurnal_id' => $id_header,
					'acc_akun_id' => $value['acc_akun_id'], //akun ,
					'debet' => 0,
					'kredit' => ($nilai * -1),
					'ket' => 'Adjustment Stok :' . $value['nama_barang'] . ', qty:' . $value['qty'] . ' ' . $value['uom'],
					'no_referensi' => $retrieve_header['no_transaksi'],
					'referensi_id' => NULL,
					'blok_stasiun_id' => NULL,
					'kegiatan_id' => NULL,
					'kendaraan_mesin_id' => NULL
				);
				$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataKredit);
			}
		}


		$data['diposting_oleh'] = $this->user_id;
		$data['tanggal'] =	$retrieve_header['tanggal'];
		$res = $this->KlnBiayaRawatJalanModel->posting($id, $data);
		if (!empty($res)) {
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}




	function print_slip_get($segment_3 = '')
	{

		$id = (int)$segment_3;
		$data = [];

		$queryHeader = "SELECT a.*,b.no_transaksi AS no_rawat_jalan,c.nama AS nama_pasien,d.nama AS nama_dokter ,k.kode AS kode_biaya,k.nama AS nama_biaya
		FROM kln_biaya_rawat_jalan a 
		INNER JOIN kln_jenis_biaya k ON a.biaya_id=k.id
		left JOIN kln_rawat_jalan b ON a.rawat_jalan_id=b.id 
		left JOIN kln_pasien c ON b.pasien_id =c.id 
		left JOIN karyawan d ON b.dokter_id=d.id
				WHERE a.id=" . $id . "";
			// echo $queryHeader;
			// exit();
		$dataHeader = $this->db->query($queryHeader)->row_array();

	


		$data['hd'] = 	$dataHeader;


		$html = $this->load->view('KlnSlipBiaya', $data, true);

		$filename = 'report_' . time();
		$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		// echo $html;
	}
}
