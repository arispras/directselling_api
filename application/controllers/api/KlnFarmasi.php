<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

class KlnFarmasi extends  BD_Controller
{
	public $user_id;
	public $theCredential;

	function __construct()
	{
		parent::__construct();
		$this->load->model('KlnFarmasiModel');
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
		b.nama AS nama_pasien,
		c.nama AS nama_dokter
		from kln_farmasi_ht a
		left join kln_pasien b on a.pasien_id=b.id
		LEFT JOIN karyawan c ON a.dokter_id = c.id
		";
		$search = array('a.tanggal',  'a.no_transaksi', 'a.catatan','b.nama');
		$where  = null;

		$isWhere = null;
		$isWhere = " 1=1 ";
		// if ($param['tgl_mulai'] && $param['tgl_mulai']) {
		// 	$isWhere = " a.tanggal between '" . $param['tgl_mulai'] . "' and '" . $param['tgl_akhir'] . "'";
		// }

		// if (!empty($param['rawat_inap_id'])) {

		// 	$isWhere = $isWhere .  "  and a.rawat_inap_id=" . $param['rawat_inap_id'] . "";
		// }

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	function index_get($segment_3 = '')
	{
		$id = $segment_3;
		$retrieve = $this->KlnFarmasiModel->retrieve($id);
		$retrieve['detail'] = $this->KlnFarmasiModel->retrieve_detail($id);

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAll_get()
	{

		$retrieve = $this->KlnFarmasiModel->retrieve_all_kategori();

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
		$input['no_transaksi'] = $this->autonumber->rawat_jalan_obat($input['tanggal']);

		$res =  $this->KlnFarmasiModel->create($input);
		// $this->set_response(array("status" => "OK", "data" => $input['no_transaksi']), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'KlnFarmasi', 'action' => 'new', 'entity_id' => $res);
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
		$kategori = $this->KlnFarmasiModel->retrieve($id);
		if (empty($kategori)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$res =   $this->KlnFarmasiModel->update($kategori['id'], $input);
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'KlnFarmasi', 'action' => 'edit', 'entity_id' => $id);
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
		$kategori = $this->KlnFarmasiModel->retrieve($id);
		if (empty($kategori)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$res =  $this->KlnFarmasiModel->delete($kategori['id']);
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id' => $id)), 'entity' => 'KlnFarmasi', 'action' => 'delete', 'entity_id' => $id);
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

		$retrieve_header = $this->KlnFarmasiModel->retrieve($id);
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
		$retrieve_detail = $this->KlnFarmasiModel->retrieve_detail($id);

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
		 where kode='KlnFarmasiUSTMENT_STOK'")->row_array();
		if (empty($retrieve_akun)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Setting Auto Jurnal belum ada"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$akun_kredit =	$retrieve_akun['acc_akun_id_kredit'];
		$akun_debet =	$retrieve_akun['acc_akun_id_debet'];
		// $this->set_response(array("status" => "NOT OK", "data" => $retrieve_akun), REST_Controller::HTTP_NOT_FOUND);
		// return;
		$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'KlnFarmasi_STOK');
		// Data HEADER
		$this->load->library('Autonumber');
		$no_jurnal = $this->autonumber->jurnal_auto($retrieve_header['lokasi_id'], $retrieve_header['tanggal'], 'KlnFarmasi');

		$dataH = array(
			'no_jurnal' => $no_jurnal,
			'lokasi_id' => $retrieve_header['lokasi_id'],
			'tanggal' => $retrieve_header['tanggal'],
			'no_ref' => $retrieve_header['no_transaksi'],
			'ref_id' => $retrieve_header['id'],
			'tipe_jurnal' => 'AUTO',
			'modul' => 'KlnFarmasi_STOK',
			'keterangan' => 'KlnFarmasi_STOK',
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
		$res = $this->KlnFarmasiModel->posting($id, $data);
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

		$queryHeader = "SELECT a.*,c.nama AS nama_pasien,d.nama AS nama_dokter
		 FROM kln_farmasi_ht a 
		left JOIN kln_pasien c ON a.pasien_id =c.id  
		left JOIN karyawan d ON a.dokter_id=d.id 
		WHERE a.id=" . $id . "";
		$dataHeader = $this->db->query($queryHeader)->row_array();
		// echo($queryHeader);exit();

		$queryDetail = "SELECT a.*,
		b.kode as kode_barang,
		b.nama as nama_barang,
		f.nama as uom
		FROM kln_farmasi_dt a 
		inner join inv_item b on a.item_id=b.id 
		left join gbm_uom f on b.uom_id=f.id 
		WHERE  a.kln_farmasi_id = " . $id . "";
		$dataDetail = $this->db->query($queryDetail)->result_array();

		// $perminta = $this->InvPermintaanBarangModel->print_slip($id);
		$data['hd'] = 	$dataHeader;
		$data['dt'] = 	$dataDetail;

		$html = $this->load->view('KlnSlipFarmasi', $data, true);

		$filename = 'report_' . time();
		$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		// echo $html;
	}




	function laporan_detail_post()
	{
		$format_laporan =  $this->post('format_laporan', true);

		// $id = (int)$segment_3;
		$data = [];

		$input = [
			'no_po' => '01/PO/DPA',
			'lokasi_id' => 252,
			'gudang_id' => 740,
			'tgl_mulai' => '2020-01-01',
			'tgl_akhir' => '2022-12-12',
		];

		$lokasi_id = $this->post('lokasi_id', true);
		$gudang_id = $this->post('gudang_id', true);
		$tanggal_awal = $this->post('tgl_mulai', true);
		$tanggal_akhir = $this->post('tgl_akhir', true);


		$queryPemakaianObat = "SELECT a.*,c.kode as kode_obat,c.nama as nama_obat,d.kode as uom, b.dosis,b.instruksi,b.qty,b.harga,b.diskon,
		g.nama AS nama_pasien,f.nama AS nama_dokter
		FROM kln_farmasi a 
		INNER JOIN kln_obat_rawat_inap_dt b 
		ON a.id=b.kln_obat_rawat_inap_id 
		INNER JOIN kln_rawat_inap r ON a.rawat_inap_id=r.id
		left JOIN inv_item c ON b.item_id=c.id
		left JOIN gbm_uom d ON c.uom_id=d.id
		left JOIN inv_kategori e ON c.inv_kategori_id=e.id 
		left JOIN karyawan f ON r.dokter_id=f.id
		left JOIN kln_pasien g ON r.pasien_id = g.id     
		where a.tanggal between  '" . $tanggal_awal . "' and  '" . $tanggal_akhir . "'	
		";

		// if (!empty($no_po)) {
		// 	$queryPemakaianObat = $queryPemakaianObat."and h.no_po LIKE '%".$no_po."%' ";
		// }

		// $filter_lokasi = "Semua";
		// if ($lokasi_id) {
		// 	$queryPemakaianObat = $queryPemakaianObat . " and b.lokasi_id=" . $lokasi_id . "";
		// 	$res = $this->db->query("select * from gbm_organisasi where id=" . $lokasi_id . "")->row_array();
		// 	$filter_lokasi = $res['nama'];
		// }
		// $filter_gudang = "Semua";
		// if ($gudang_id) {
		// 	$queryPemakaianObat = $queryPemakaianObat . " and b.gudang_id=" . $gudang_id . "";
		// 	$res = $this->db->query("select * from gbm_organisasi where id=" . $gudang_id . "")->row_array();
		// 	$filter_gudang = $res['nama'];
		// }

		$dataPemakaianObat = $this->db->query($queryPemakaianObat)->result_array();

		$data['pemakaianObat'] = 	$dataPemakaianObat;
		// $data['filter_gudang'] = 	$filter_gudang;
		// $data['filter_lokasi'] = 	$filter_lokasi;
		$data['filter_tgl_awal'] = 	$tanggal_awal;
		$data['filter_tgl_akhir'] = $tanggal_akhir;
		$data['format_laporan'] = $format_laporan;
		$html = $this->load->view('KlnPemakaianObatRawatInapDetail', $data, true);

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
}
