<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

class SlsRekap extends BD_Controller
{
	public $user_id;
	public $theCredential;

	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('GbmOrganisasiModel');
		$this->load->model('SlsRekapModel');
		$this->load->library('pdfgenerator');
		$this->load->model('M_DatatablesModel');
		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id = $this->user_data->id;
	}
	public function list_post()
	{
		$post = $this->post();

		$query  = "SELECT 
		a.*,
		b.nama AS lokasi,
		d.no_spk AS spk,e.nama_customer
		FROM sls_rekap_hd a 
		INNER JOIN gbm_organisasi b ON a.lokasi_id=b.id
		INNER JOIN sls_kontrak d ON a.spk_id=d.id
		INNER JOIN gbm_customer e ON a.customer_id=e.id
		";
		$search = array('no_rekap');
		$where  = null;

		$isWhere = null;
		// $isWhere = 'artikel.deleted_at IS NULL';

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
	FROM sls_rekap_hd a 
	INNER JOIN gbm_organisasi b ON a.lokasi_id=b.id
	INNER JOIN sls_kontrak d ON a.spk_id=d.id

		";
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
		$retrieve = $this->SlsRekapModel->retrieve_by_id($id);
		$retrieve['detail'] = $this->SlsRekapModel->retrieve_detail($id);


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
		$input['no_rekap']=$this->autonumber->sls_rekap($input['lokasi_id']['id'], $input['tanggal']);
		//$input['no_spat'] = $this->getLastNumber('pks_pengolahan_ht', 'no_spat', 'SPAT');
		// var_dump($input);
		$res = $this->SlsRekapModel->create($input);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'sls_rekap', 'action' => 'new', 'entity_id' => $res);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" =>$input['no_rekap']), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function index_put($segment_3 = null)
	{
		$id = (int)$segment_3;
		$data = $this->put();
		$data['diubah_oleh'] = $this->user_id;

		$res = $this->SlsRekapModel->update($id, $data);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'sls_rekap', 'action' => 'edit', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	public function index_delete($id)
	{

		$res = $this->SlsRekapModel->delete($id);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id'=>$id)), 'entity' => 'sls_rekap', 'action' => 'delete', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
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


	function laporan_detail_post()
	{
		error_reporting(0);
		
		$data = [];
		if (isset($this->post()['tgl_mulai'])) {
			$input = $this->post();
			$lokasi_id=$this->post('lokasi_id',true);
			$supplier_id=$this->post('supplier_id',true);
			$tanggal_awal=$this->post('tgl_mulai',true);
			$tanggal_akhir=$this->post('tgl_akhir',true);
			$format_laporan =  $this->post('format_laporan',true);
		}else {
			$input = [
				'tanggal'=> '2021-12-23',
			];
			$lokasi_id=263;
			$tanggal_awal='2020-01-01';
			$tanggal_akhir='2022-12-12';
			$format_laporan =  'view';
		}

		$queryPo = "SELECT 
		a.*,
		b.*,
		c.nama AS lokasi,
		d.nama_customer AS customer,
		e.no_spk AS spk,
		f.nama AS item,
		g.no_surat AS no_surat,
		a.id AS id
		
		FROM sls_rekap_dt a
		LEFT JOIN sls_rekap_hd b ON a.rekap_id=b.id
		LEFT JOIN gbm_organisasi c ON b.lokasi_id=c.id
		LEFT JOIN gbm_customer d ON b.customer_id=d.id
		LEFT JOIN est_spk_ht e ON b.spk_id=e.id
		LEFT JOIN inv_item f ON b.item_id=f.id
		LEFT JOIN pks_sjpp g ON a.sjpp_id=g.id

		where b.tanggal between  '". $tanggal_awal ."' and  '". $tanggal_akhir ."'	
		";
		$filter_supplier="Semua";
		if ($lokasi_id){
			$queryPo=$queryPo." and b.lokasi_id=".$lokasi_id ."";
			$res=$this->db->query("select * from gbm_organisasi where id=".$lokasi_id."")->row_array();
			$filter_lokasi=$res['nama'];
		}

		$dataPo = $this->db->query($queryPo)->result_array();
		
		$data['po'] = 	$dataPo;
		$data['filter_lokasi'] = 	$filter_lokasi;
		$data['filter_tgl_awal'] = 	$tanggal_awal;
		$data['filter_tgl_akhir'] = $tanggal_akhir;

		$html = $this->load->view('Sls_Rekap_Laporan', $data, true);

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


	function print_slip_get($segment_3 = '')
	{
		$id = (int)$segment_3;
		$data = [];

		$hd = $this->SlsRekapModel->print_slip_header($id);
		$data['hd'] = $hd;
		$dt = $this->SlsRekapModel->print_slip_detail($id);
		$data['dt'] = $dt;

		$html = $this->load->view('SlsSlipRekap', $data, true);

		$filename = 'report_SlsSlipRekap_' . time();
		$this->pdfgenerator->generate($html, $filename, true, 'A3', 'landscape');
		// echo $html;
	}
}
