<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Restserver\Libraries\REST_Controller;

class SlsIntruksi extends BD_Controller
{
	public $user_id;
	public $theCredential;
	function __construct()
	{
		parent::__construct();
		$this->load->model('SlsIntruksiModel');
		$this->load->model('KaryawanModel');
		$this->load->library('pdfgenerator');
		$this->load->model('M_DatatablesModel');
		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id = $this->user_data->id;
	}

	public function list_post()
	{
		$post = $this->post();

		$query  = "SELECT a.* ,
				b.nama as mill,
				c.nama as milll,
				h.customer_id,
				i.nama_customer as cust,
				e.no_spk  AS kontrak,
				f.produk_id,
				g.nama as nama_produk
				from sls_intruksi_kirim a 
				INNER JOIN gbm_organisasi b on a.sales_lokasi_id = b.id 
				INNER JOIN gbm_organisasi c on a.kepada_lokasi_id = c.id 
				INNER JOIN sls_kontrak e ON a.spk_id = e.id
				INNER JOIN sls_kontrak f on a.spk_id =f.id
				INNER JOIN inv_item g on f.produk_id = g.id
				INNER JOIN sls_kontrak h on a.spk_id =h.id
				INNER JOIN gbm_customer i on h.customer_id =i.id";
		$search = array('e.no_spk', 'a.no_transaksi','f.produk_id','c.nama','a.tanggal');
		$where  = null;

		$isWhere = null;
		// $isWhere = 'artikel.deleted_at IS NULL';

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	function index_get($segment_3 = '')
	{
		$id = $segment_3;
		$retrieve = $this->SlsIntruksiModel->retrieve($id);
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function getAll_get()
	{
		$retrieve = $this->SlsIntruksiModel->retrieve_all();
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAllByKontrak_get($kontrak_id)
	{
		$retrieve = $this->SlsIntruksiModel->retrieve_all_by_kontrak($kontrak_id);
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
		$res =  $this->SlsIntruksiModel->create($data);
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'sls_intruksi', 'action' => 'new', 'entity_id' => $res);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $input['no_transaksi']), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function update_post($segment_3 = '')
	{


		$id = (int)$segment_3;
		$item = $this->SlsIntruksiModel->retrieve($id);
		if (empty($item)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$data = $this->post();
		$data['diubah_oleh'] = $this->user_id;
		$res =   $this->SlsIntruksiModel->update($item['id'], $data);
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'sls_intruksi', 'action' => 'edit', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function index_delete($segment_3 = '')
	{

		$id = (int)$segment_3;
		$item = $this->SlsIntruksiModel->retrieve($id);
		if (empty($item)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$res =  $this->SlsIntruksiModel->delete($item['id']);
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id'=>$id)), 'entity' => 'sls_intruksi', 'action' => 'delete', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function import($path_file)
	{

		// $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx(); //Excel 2007 or higher
		$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls(); //Excel 2003
		$spreadsheet = $reader->load($path_file);
		$sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
		return $sheetData;
	}


	function laporan_detail_post()
	{
		error_reporting(0);
		
		$data = [];
		if (isset($this->post()['tgl_mulai'])) {
			$input = $this->post();
			// $lokasi_id=$this->post('lokasi_id',true);
			$no_ip=$this->post('no_ip',true);
			$no_kontrak=$this->post('no_kontrak',true);
			$customer_id=$this->post('customer_id',true);
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

		$query = " SELECT 
		a.*,
		c.nama_customer AS nama_customer,
		b.no_spk AS no_spk,
		d.nama AS produk,
		a.id AS id	
		FROM sls_intruksi_kirim a
		LEFT JOIN sls_kontrak b ON a.spk_id=b.id
		LEFT JOIN gbm_customer c ON b.customer_id=c.id
		LEFT JOIN inv_item d ON b.produk_id=d.id
		where a.tanggal between  '". $tanggal_awal ."' and  '". $tanggal_akhir ."'	
		";
		$filter_customer="Semua";
		if ($customer_id){
			$query=$query." and b.customer_id=".$customer_id ."";
			$res=$this->db->query("select * from gbm_customer where id=".$customer_id."")->row_array();
			$filter_customer=$res['nama_customer'];
		}
		if ($no_ip  ){
			if (trim($no_ip)!=''){
				$query=$query." and a.no_transaksi like '%".$no_ip ."%'";
				
			}
		}
		if ($no_kontrak  ){
			if (trim($no_kontrak)!=''){
				$query=$query." and b.no_spk like '%".$no_kontrak ."%'";
				
			}
		}
		$data = $this->db->query($query)->result_array();
		$res=array();
		foreach ($data as $key => $value) {
			$query=" SELECT * FROM pks_timbangan_kirim where instruksi_id=".$value['id']." order by tanggal;";
			$dt=$this->db->query($query)->result_array();
			$jum=0;
			foreach ($dt as $key2 => $dtl) {
				$jum=$jum+$dtl['netto_kirim'];
			}
			$value['jum_kirim']=$jum;
			$value['sisa']=$value['jumlah']-$jum;
			$value['dt']=$dt;
			$res[]=$value;
			
		}
		
		$data['ip'] = 	$res;

		$data['filter_customer'] = 	$filter_customer;
		$data['filter_tgl_awal'] = 	$tanggal_awal;
		$data['filter_tgl_akhir'] = $tanggal_akhir;

		$html = $this->load->view('Sls_Intruksi_Laporan', $data, true);

	
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
	function laporan_monitoring_post()
	{
		error_reporting(0);
		
		$data = [];
		if (isset($this->post()['tgl_akhir'])) {
			$input = $this->post();
			// $lokasi_id=$this->post('lokasi_id',true);
			// $no_ip=$this->post('no_ip',true);
			// $no_kontrak=$this->post('no_kontrak',true);
			$customer_id=$this->post('customer_id',true);
			$tanggal_awal=$this->post('tgl_mulai',true);
			$tanggal_akhir=$this->post('tgl_akhir',true);
			$format_laporan =  $this->post('format_laporan',true);
			$status =  $this->post('status',true);
		}else {
			$input = [
				'tanggal'=> '2021-12-23',
			];
			$lokasi_id=263;
			$tanggal_awal='2020-01-01';
			$tanggal_akhir='2022-12-12';
			$format_laporan =  'view';
		}

		$query = " SELECT 
		a.*,
		c.nama_customer AS nama_customer,
		b.no_spk AS no_spk,
		d.nama AS produk,
		a.id AS id	
		FROM sls_intruksi_kirim a
		LEFT JOIN sls_kontrak b ON a.spk_id=b.id
		LEFT JOIN gbm_customer c ON b.customer_id=c.id
		LEFT JOIN inv_item d ON b.produk_id=d.id
		
		";
		$filter_customer="Semua";
		if ($customer_id){
			$query=$query." and b.customer_id=".$customer_id ."";
			$res=$this->db->query("select * from gbm_customer where id=".$customer_id."")->row_array();
			$filter_customer=$res['nama_customer'];
		}
		// if ($no_ip  ){
		// 	if (trim($no_ip)!=''){
		// 		$query=$query." and a.no_transaksi like '%".$no_ip ."%'";
				
		// 	}
		// }
		// if ($no_kontrak  ){
		// 	if (trim($no_kontrak)!=''){
		// 		$query=$query." and b.no_spk like '%".$no_kontrak ."%'";
				
		// 	}
		// }
		$data = $this->db->query($query)->result_array();
		$res=array();
		foreach ($data as $key => $value) {
			$query1=" SELECT sum(netto_kirim)as jumlah FROM pks_timbangan_kirim 
			where instruksi_id=".$value['id']." and  tanggal='".$tanggal_akhir ."';";
			$res1=$this->db->query($query1)->row_array();
			$jum_hari_ini=0;
			if ($res1){
				$jum_hari_ini=$res1['jumlah'];
			}
			$query2=" SELECT sum(netto_kirim)as jumlah FROM pks_timbangan_kirim 
			where instruksi_id=".$value['id']." and (tanggal>='".$tanggal_awal ."' and tanggal<='".$tanggal_akhir ."');";
			$res2=$this->db->query($query2)->row_array();
			$jum_sd_hari_ini=0;
			if ($res2){
				$jum_sd_hari_ini=$res2['jumlah'];
			}
			
		
			$value['jum_sd_hari_ini']=$jum_sd_hari_ini;
			$value['jum_hari_ini']=$jum_hari_ini;
			$sisa=$value['jumlah']-$jum_sd_hari_ini;
			$value['sisa']=$sisa;
			$value['status']=($sisa>0)?'on Progress':'Close';
			if ($status=='all'){
				$res[]=$value;

			}elseif ($status=='close'){
				if ($sisa<=0){
					$res[]=$value;
				}
			}elseif ($status=='on_progres'){
				if ($sisa>0){
					$res[]=$value;
				}
			}
		}
		
		$data['ip'] = 	$res;

		$data['filter_customer'] = 	$filter_customer;
		$data['filter_tgl_awal'] = 	$tanggal_awal;
		$data['filter_tgl_akhir'] = $tanggal_akhir;

		$html = $this->load->view('Sls_Intruksi_LaporanMonitoring', $data, true);

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
}
