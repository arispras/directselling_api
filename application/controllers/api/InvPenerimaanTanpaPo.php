<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
use Restserver\Libraries\REST_Controller;

class InvPenerimaanTanpaPo extends  BD_Controller
{
	public $user_id;
	public $theCredential;

	function __construct()
	{
		parent::__construct();
		$this->load->model('InvPenerimaanTanpaPoModel');
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
		$param=$post['parameter'];
		
		$query  = "SELECT a.*,
			b.nama AS lokasi, 
			c.nama AS gudang, 
			d.nama_supplier AS supplier,
			e.user_full_name AS dibuat,
			f.user_full_name AS diubah,
			g.user_full_name AS diposting
		   from inv_penerimaan_tanpa_po_ht a
		   left join gbm_organisasi b on a.lokasi_id=b.id
		   left join gbm_organisasi c on a.gudang_id=c.id
		   left join gbm_supplier d on a.supplier_id=d.id
		   LEFT JOIN fwk_users e ON a.dibuat_oleh = e.id
		   LEFT JOIN fwk_users f ON a.diubah_oleh = f.id
		   LEFT JOIN fwk_users g ON a.diposting_oleh = g.id
		";
		$search = array('tanggal','no_ref','no_transaksi','catatan','d.nama_supplier');
		$where  = null;

		// $isWhere = " a.lokasi_id in
		// (select location_id from fwk_users_location where user_id=" . $this->user_id . ")";
		$isWhere=" 1=1";
		if ($param['tgl_mulai'] && $param['tgl_mulai']){
			$isWhere=" a.tanggal between '".$param['tgl_mulai']."' and '".$param['tgl_akhir']."'";			
		}
		if ($param['lokasi_id']){
			$isWhere =$isWhere. " and a.lokasi_id =".$param['lokasi_id']."";
		}else{
			$isWhere = $isWhere. " and  a.lokasi_id in
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
	function index_get($segment_3 = '')
	{
		$id = $segment_3;
		$retrieve = $this->InvPenerimaanTanpaPoModel->retrieve($id);
		$retrieve['detail'] = $this->InvPenerimaanTanpaPoModel->retrieve_detail($id);
		
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAll_get()
	{
		
		$retrieve = $this->InvPenerimaanTanpaPoModel->retrieve_all_kategori();
		
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
		$input['no_transaksi']=$this->autonumber->inv_penerimaan_tanpa_po($input['lokasi_id']['id'], $input['tanggal']);
		
		$retrieve=  $this->InvPenerimaanTanpaPoModel->create($input);
		// $this->set_response(array("status" => "OK", "data" => $input['no_transaksi']), REST_Controller::HTTP_OK);
		if (!empty($retrieve)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'inv_penerimaan_tanpa_po', 'action' => 'new', 'entity_id' => $retrieve);
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
		$kategori = $this->InvPenerimaanTanpaPoModel->retrieve( $id);
		if (empty($kategori)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);	
		}
		
		$retrieve=   $this->InvPenerimaanTanpaPoModel->update($kategori['id'], $input );
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($retrieve)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'inv_penerimaan_tanpa_po', 'action' => 'edit', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	
	function index_delete($segment_3 = '')
	{
		
		$id = (int)$segment_3;
		$kategori = $this->InvPenerimaanTanpaPoModel->retrieve($id);
		if (empty($kategori)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
			
		}
		
		$retrieve=  $this->InvPenerimaanTanpaPoModel->delete($kategori['id']);
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		
		if (!empty($retrieve)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id'=>$id)), 'entity' => 'inv_penerimaan_tanpa_po', 'action' => 'delete', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	
	function posting_post($segment_3 = null)
	{
		$id = (int)$segment_3;
		$data = $this->post();
		$retrieve_akun_kredit = $this->db->query("SELECT * FROM acc_auto_jurnal
		 where kode='PENERIMAAN_BARANG_PO'")->row_array();
		$akun_kredit=	$retrieve_akun_kredit['acc_akun_id']; 
		$retrieve_header = $this->InvPenerimaanTanpaPoModel->retrieve($id);
		$retrieve_detail = $this->InvPenerimaanTanpaPoModel->retrieve_detail($id);
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
		
		$this->AccJurnalModel->delete_by_ref_id_and_modul($retrieve_header['id'], 'INV_PENERIMAAN_TANPA_PO');
		// Data HEADER
		$this->load->library('Autonumber');
		$no_jurnal=$this->autonumber->jurnal_auto($retrieve_header['lokasi_id'],$retrieve_header['tanggal'],'INVPO');

		$dataH = array(
			'no_jurnal'=>$no_jurnal,
			'lokasi_id' => $retrieve_header['lokasi_id'],
			'tanggal' => $retrieve_header['tanggal'],
			'no_ref' => $retrieve_header['no_transaksi'],
			'ref_id' => $retrieve_header['id'],
			'tipe_jurnal' => 'AUTO',
			'modul' => 'INV_PENERIMAAN_TANPA_PO',
			'keterangan' => 'INV_PENERIMAAN_TANPA_PO',
			'is_posting' => 1,
		);
		$id_header = $this->AccJurnalModel->create_header($dataH);
		// $ppn_nilai=($retrieve_header['ppn']/100) *  ($retrieve_header['harga_satuan'] * $retrieve_header['qty']);
		// $pph_nilai=($retrieve_header['pph']/100) *  ($retrieve_header['harga_satuan'] * $retrieve_header['qty']);
		// // Data DEBET
		
		$total_nilai=0;
		foreach ($retrieve_detail as $key => $value) {
			$nilai=(($value['harga']* $value['qty']));
			$total_nilai=$total_nilai+$nilai;
			$dataDt = array(
				'lokasi_id' => $retrieve_header['lokasi_id'],
				'jurnal_id' => $id_header,
				'acc_akun_id' => ($value['acc_akun_id']), //akun 
				'debet' =>$nilai,
				'kredit' => 0,
				'ket' => 'Penerimaan Tanpa PO Item:'.$value['nama_barang'].', qty:'.$value['qty'].' '.$value['uom'],
				'no_referensi' => $retrieve_header['no_transaksi'],
				'referensi_id' => NULL,
				'blok_stasiun_id' => NULL,
				'kegiatan_id' => NULL, //kegiatan ,
				'kendaraan_mesin_id' => NULL
			);
			
			$id_dtl = $this->AccJurnalModel->create_detail($id_header, $dataDt);
		}
		$dataKredit = array(
			'lokasi_id' => $retrieve_header['lokasi_id'],
			'jurnal_id' => $id_header,
			'acc_akun_id' => $akun_kredit, //akun ,
			'debet' =>0 ,
			'kredit' => $total_nilai,
			'ket' => 'INV_PENERIMAAN_TANPA_PO',
			'no_referensi' => $retrieve_header['no_transaksi'],
			'referensi_id' => NULL,
			'blok_stasiun_id' => NULL,
			'kegiatan_id' => NULL,
			'kendaraan_mesin_id' => NULL
		);
		$id_dtl = $this->AccJurnalModel->create_detail($id_header, 	$dataKredit);
		$data['diposting_oleh'] = $this->user_id;
		$data['tanggal'] =	$retrieve_header['tanggal'];
		$res = $this->InvPenerimaanTanpaPoModel->posting($id, $data);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id'=>$id)), 'entity' => 'inv_penerimaan_tanpa_po', 'action' => 'posting', 'entity_id' => $id);
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
		d.nama as gudang,
		e.nama as lokasi,f.nama_supplier
		FROM inv_penerimaan_tanpa_po_ht a 
		INNER JOIN gbm_organisasi d ON a.gudang_id=d.id
		INNER JOIN gbm_organisasi e ON a.lokasi_id=e.id
		left join gbm_supplier f on a.supplier_id=f.id
		WHERE a.id=" . $id . "";
		$dataHeader = $this->db->query($queryHeader)->row_array();

		$queryDetail = "SELECT a.*,
		b.kode as kode_barang,
		b.nama as nama_barang,
		f.nama as uom
		FROM inv_penerimaan_tanpa_po_dt a 
		inner join inv_item b on a.item_id=b.id 
		left join gbm_uom f on b.uom_id=f.id 
		WHERE  a.penerimaan_tanpa_po_id = " . $id . "";
		$dataDetail = $this->db->query($queryDetail)->result_array();

		// $perminta = $this->InvPermintaanBarangModel->print_slip($id);
		$data['header'] = 	$dataHeader;
		$data['detail'] = 	$dataDetail;

		$html = $this->load->view('InvSlipPenerimaanTanpaPo', $data, true);

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		echo $html;
	}




	function laporan_po_detail_post()
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

		$lokasi_id=$this->post('lokasi_id',true);
		$gudang_id=$this->post('gudang_id',true);
		$tanggal_awal=$this->post('tgl_mulai',true);
		$tanggal_akhir=$this->post('tgl_akhir',true);

		// $lokasi_id = $input['lokasi_id'];
		// $gudang_id = $input['gudang_id'];
		// $tanggal_awal = $input['tgl_mulai'];
		// $tanggal_akhir = $input['tgl_akhir'];

		$queryPo = "SELECT
		a.qty as qty,
		b.no_transaksi,
		b.tanggal as tanggal,
		d.nama as uom,
		c.nama as item,
		c.kode as kode,
		e.nama as gudang,
		a.id AS id
		
		FROM inv_penerimaan_tanpa_po_dt a
		
		LEFT JOIN inv_penerimaan_tanpa_po_ht b on a.penerimaan_tanpa_po_id=b.id
		LEFT JOIN inv_item c on a.item_id=c.id
		LEFT JOIN gbm_organisasi e on b.gudang_id=e.id
		LEFT JOIN gbm_uom d on c.uom_id=d.id
        
		where b.tanggal between  '" . $tanggal_awal . "' and  '" . $tanggal_akhir . "'	
		";

		// if (!empty($no_po)) {
		// 	$queryPo = $queryPo."and h.no_po LIKE '%".$no_po."%' ";
		// }

		$filter_lokasi = "Semua";
		if ($lokasi_id) {
			$queryPo = $queryPo . " and b.lokasi_id=" . $lokasi_id . "";
			$res = $this->db->query("select * from gbm_organisasi where id=" . $lokasi_id . "")->row_array();
			$filter_lokasi = $res['nama'];
		}
		$filter_gudang = "Semua";
		if ($gudang_id) {
			$queryPo = $queryPo . " and b.gudang_id=" . $gudang_id . "";
			$res = $this->db->query("select * from gbm_organisasi where id=" . $gudang_id . "")->row_array();
			$filter_gudang = $res['nama'];
		}

		$dataPo = $this->db->query($queryPo)->result_array();

		$data['po'] = 	$dataPo;
		$data['filter_gudang'] = 	$filter_gudang;
		$data['filter_lokasi'] = 	$filter_lokasi;
		$data['filter_tgl_awal'] = 	$tanggal_awal;
		$data['filter_tgl_akhir'] = $tanggal_akhir;
		$data['format_laporan'] = $format_laporan;

		$html = $this->load->view('Inv_Penerimaan_Tanpa_Po_Laporan', $data, true);

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
