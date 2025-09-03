<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
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

class PksTimbanganKirim extends BD_Controller
{
	public $user_id;
	public $theCredential;

	function __construct()
	{
		parent::__construct();
		$this->load->model('PksTimbanganKirimModel');
		$this->load->model('M_DatatablesModel');
		$this->load->library('pdfgenerator');
		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id = $this->user_data->id;
	}

	public function list_post()
	{
		$post = $this->post();
		$param=$post['parameter'];

		//$query  = "SELECT a.*,nama_customer from pks_timbangan_kirim a inner join gbm_customer b on a.customer_id=b.id ";
		$query="SELECT a.*,nama_customer,c.nama_supplier AS nama_transportir,d.no_transaksi AS no_ip, e.no_spk AS no_kontrak 
		from pks_timbangan_kirim a inner join gbm_customer b on a.customer_id=b.id
		LEFT JOIN gbm_supplier c ON a.transportir_id=c.id LEFT JOIN sls_intruksi_kirim d ON a.instruksi_id=d.id
		LEFT JOIN sls_kontrak e ON d.spk_id=e.id ";
		$search = array('no_tiket', 'b.nama_customer','nama_supir','no_kendaraan','c.nama_supplier','d.no_transaksi','e.no_spk');
		$where  = null;

		$isWhere = null;
		// $isWhere = 'artikel.deleted_at IS NULL';
		$isWhere=" 1=1";
		if ($param['tgl_mulai'] && $param['tgl_mulai']){
			$isWhere=" a.tanggal between '".$param['tgl_mulai']."' and '".$param['tgl_akhir']."'";			
		}
		if ($param['lokasi_id']){
			$isWhere =$isWhere. " and a.mill_id =".$param['lokasi_id']." ";
		}
		if ($param['customer_id']){
			$isWhere =$isWhere. " and a.customer_id =".$param['customer_id']." ";
		}

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	public function list_ip_post($id)
	{
		$post = $this->post();

		$query  = "SELECT a.*,nama_customer from pks_timbangan_kirim a 
		inner join gbm_customer b on a.customer_id=b.id 
		inner join sls_intruksi_kirim c on a.instruksi_id=c.id ";
		$search = array('no_tiket');
		$where  = array(
				'a.instruksi_id'=>$id
			);

		$isWhere = null;
		// $isWhere = 'artikel.deleted_at IS NULL';

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	function index_get($segment_3 = '')
	{
		$id = $segment_3;

		$retrieve = $this->PksTimbanganKirimModel->retrieve($id);
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAll_get()
	{

		$retrieve = $this->PksTimbanganKirimModel->retrieve_all_kategori();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function index_post()
	{
		$input = $this->post();
		$input['diubah_oleh'] = $this->user_id;

		$res =  $this->PksTimbanganKirimModel->create($input);
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'pks_timbangan_kirim', 'action' => 'new', 'entity_id' => $res);
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

		$id = (int)$segment_3;
		$timbangan = $this->PksTimbanganKirimModel->retrieve($id);
		if (empty($kategori)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$input['diubah_tanggal'] = date('Y-m-d H:i:s');
		$input['mill_id'] = $input['mill_id']['id'];
		$input['item_id'] = $input['item_id']['id'];
		$input['tangki_id'] = $input['tangki_id']['id'];
		$input['customer_id'] = $input['customer_id']['id'];
		$input['transportir_id'] = $input['transportir_id']['id'];
		$input['instruksi_id'] = $input['instruksi_id']['id'];    
		$input['tipe'] = '';
		$res =   $this->PksTimbanganKirimModel->update($timbangan['id'], $input);
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'pks_timbangan_kirim', 'action' => 'edit', 'entity_id' => $id);
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
		$kategori = $this->PksTimbanganKirimModel->retrieve($id);
		if (empty($kategori)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$res =  $this->PksTimbanganKirimModel->delete($kategori['id']);
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id'=>$id)), 'entity' => 'pks_timbangan_kirim', 'action' => 'delete', 'entity_id' => $id);
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

		$PksTimbanganKirim = $this->PksTimbanganKirimModel->print_slip($id);
		$data['PksTimbanganKirim'] = $PksTimbanganKirim;

		$html = $this->load->view('PksTimbanganKirim_print', $data, true);

		$filename = 'report_' . time();
		$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
	}
	function laporan_rincian_kirim_post()
	{
		error_reporting(0);
		$format_laporan     = $this->post('format_laporan', true);
		$tipe_laporan     = $this->post('tipe_laporan', true);
		$mill_id     = $this->post('mill_id', true);
		$customer_id     = $this->post('customer_id', true);
		$transportir_id     = $this->post('transportir_id', true);
		$produk_id     = $this->post('produk_id', true);
		$spk_id     = $this->post('spk_id', true);
		$tgl_mulai =  $this->post('tgl_mulai', true);
		$tgl_akhir = $this->post('tgl_akhir', true);

		$retrieveMill = $this->db->query("select * from gbm_organisasi where id=" . $mill_id . "")->row_array();
		$nama_customer = "";
		$qry = "";
		$where="";
		if ($customer_id) {
			$where=$where."  and a.customer_id=" . $customer_id . "";
			$retrieveCustomer = $this->db->query("select * from gbm_customer where id=" . $customer_id . "")->row_array();
			$nama_customer = $retrieveCustomer['nama_customer'];
		} else {
			$nama_customer = "Semua";
			
		}
		if ($transportir_id) {
			$where=$where."  and a.transportir_id=" . $transportir_id . "";
			$retrieveTranportir = $this->db->query("select * from gbm_supplier where id=" . $transportir_id . "")->row_array();
			$nama_transportir = $retrieveTranportir['nama_supplier'];
		} else {
			$nama_transportir = "Semua";
			
		}
		if ($produk_id) {
			$where=$where."  and a.item_id=" . $produk_id . "";
			$retrieveProduk = $this->db->query("select * from inv_item where id=" . $produk_id . "")->row_array();
			$nama_produk = $retrieveProduk['nama'];
		} else {
			$nama_produk = "Semua";
			
		}
		
		$qry = "SELECT a.*,b.nama_customer,c.nama as nama_produk,d.nama_supplier,f.no_spk
			 from pks_timbangan_kirim a 
			inner join gbm_customer b on a.customer_id=b.id
			inner join inv_item c on a.item_id=c.id
			left join gbm_supplier d on a.transportir_id=d.id
			LEFT JOIN sls_intruksi_kirim e ON a.instruksi_id=e.id
			LEFT JOIN sls_kontrak f ON e.spk_id=f.id 
		 	where a.mill_id=" . $mill_id . " 
			and a.tanggal>='" . $tgl_mulai . "' and a.tanggal<='" . $tgl_akhir . "'" . $where." ";
		if ($spk_id) {
			$retrieveNoSpk = $this->db->query("select * from sls_kontrak where id=" . $spk_id . "")->row_array();
			$spk_no = $retrieveNoSpk['no_spk'];
			$qry=$qry."  and f.id=" . $spk_id . "";
		} else {
			$spk_no = "Semua";
		}
		  $qry=$qry." order by a.tanggal";
		//   echo $qry;exit();
		$retrieveTimbangan = $this->db->query($qry)->result_array();

		
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');

		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report();
		} else {
			$html = get_header_report_v2();
		}
	

		$html = $html . '
		<div class="row">
  	<div class="span12">
	  <br>
	<div left class="kop-print">
	  <div class="kop-nama">KLINIK ANNAJAH</div>
	  <div class="kop-info"> Jl Sukarno Hatta No 12 No 12, Bandung Jawa Barat 14450</div>
	  <div class="kop-info">Telp : (021) 6684055</div>
	</div>
	  <hr class="kop-print-hr">
  	</div>
  	</div>
		<h3 class="title">Laporan Detail Pengiriman Produk</h3>
  <table class="no_border" style="width:35%">
			
			<tr>
					<td>Mill</td>
					<td>:</td>
					<td>' . $retrieveMill['nama'] . '</td>

					<td>Kontrak</td>
					<td>:</td>
					<td>' .$spk_no . '</td>
			</tr>
			<tr>
					<td>Customer</td>
					<td>:</td>
					<td>' .$nama_customer . '</td>

					<td>Transportir</td>
					<td>:</td>
					<td>' .$nama_transportir . '</td>
			</tr>
			<tr>	
					<td>Periode</td>
					<td>:</td>
					<td>' . tgl_indo($tgl_mulai) . ' - ' . tgl_indo($tgl_akhir) . '</td>

					<td>Produk</td>
					<td>:</td>
					<td>' .$nama_produk . '</td>
			</tr>
			
	</table>
			<br>';

		$html = $html . ' <table   border="1" width="100%" style="border-collapse: collapse;" >
			<thead>
				<tr>
				<th width="2%">No.</th>
				<th>Customer</th>
				<th>Transportir</th>
				<th>Produk</th>
				<th width="7%">Tanggal</th>
				<th>No Tiket</th>
				<th>No Kontrak</th>
				<th>No Kendaraan</th>
				<th>Supir</th>
				<th>Jam Timbang Masuk</th>
				<th>Jam Timbang Keluar</th>
				<th style="text-align: center;">Bruto (Kg) </th>
				<th style="text-align: center;">Tara (Kg)</th>		
				<th style="text-align: center;">Netto (Kg)</th>
				
				</tr>
			</thead>
			<tbody>';


		$no = 0;
		$jum_netto_kirim = 0;
		$jum_bruto_kirim = 0;
		$jum_tara_kirim = 0;


		foreach ($retrieveTimbangan as $key => $m) {
			$no++;
			$jum_netto_kirim = $jum_netto_kirim + $m['netto_kirim'];
			$jum_bruto_kirim = $jum_bruto_kirim + $m['bruto_kirim'];
			$jum_tara_kirim = $jum_tara_kirim + $m['tara_kirim'];
			$cust = "";
			if ($m['nama_customer'] == null) {
				$cust = "-";
			} else {
				$cust = $m['nama_customer'];
			}
			if ($m['nama_supplier'] == null) {
				$transp = "-";
			} else {
				$transp = $m['nama_supplier'];
			}
			if ($m['no_spk'] == null) {
				$no_spk = "-";
			} else {
				$no_spk = $m['no_spk'];
			}
			$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative;">
							' . ($no) . '

						</td>
						<td>
						' . $cust . ' 
							
						</td>
						<td>
						' . $transp . ' 
							
						</td>
						<td style="text-align: center;">
						' . $m['nama_produk'] . ' 
							
						</td>
						<td style="text-align: center;">
						' . tgl_indo_normal($m['tanggal']) . ' 
						
						</td>
						
						<td>
						' . $m['no_tiket'] . ' 
						
						</td>
						<td>
						' . $no_spk . ' 
						
						</td>
						<td>
							' . $m['no_kendaraan'] . ' 
						</td>
						<td>
							' . $m['nama_supir'] . ' 
						</td>
						<td style="text-align: center;">
							' . $m['jam_masuk'] . ' 
						</td>
						<td style="text-align: center;">
							' . $m['jam_keluar'] . ' 
						</td>
						<td style="text-align: right;">' . $this->format_number_report($m['bruto_kirim'],0) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['tara_kirim'],0) . ' 				
						<td style="text-align: right;">' . $this->format_number_report($m['netto_kirim'],0) . ' 
							
						</td>';

			$html = $html . '
						
						
					</tr>';
		}

		$html = $html . ' 	
						<tr class=":: arc-content">
						<td style="position:relative;">
							&nbsp;

						</td>
						<td>
							&nbsp;
						</td>
						<td>
							&nbsp;
						</td>
						<td>
							&nbsp;
						</td>
						<td>
							&nbsp;
						</td>
						<td>
							&nbsp;
						</td>
						<td>
							&nbsp;
						</td>
						<td>
							&nbsp;
						</td>
						<td>
							&nbsp;
						</td>
						<td>
							&nbsp;
						</td>
						<td>
							&nbsp;
						</td>
						
						<td style="text-align: right;">
						' . $this->format_number_report($jum_bruto_kirim,0) . ' 
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($jum_tara_kirim,0) . ' 
						</td>
						
						<td style="text-align: right;">
						' . $this->format_number_report($jum_netto_kirim,0) . ' 
						</td>

						
						</tr>
								</tbody>
							</table>
						';
		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		if ($format_laporan == 'xls' || $format_laporan == 'excel') {
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
			$this->pdfgenerator->generate($html, $filename, true, 'A3', 'landscape');
		}
	}
	function laporan_rincian_kirim_customer_post()
	{
		error_reporting(0);
		$format_laporan     = $this->post('format_laporan', true);
		$customer_id     = $this->post('customer_id', true);
		$transportir_id     = $this->post('transportir_id', true);
		$spk_id     = $this->post('spk_id', true);
		$tgl_mulai =  $this->post('tgl_mulai', true);
		$tgl_akhir = $this->post('tgl_akhir', true);
		$dibuat_oleh = $this->post('dibuat_oleh', true);
		$disetujui_oleh = $this->post('disetujui_oleh', true);
		// var_dump( $this->post());exit();
		$no_spk   = '';

		$where='';
		
		

		$qry = "select * from pks_timbangan_kirim_sj_vw 
		where 1=1 and tanggal_timbang>='" . $tgl_mulai . "' 
		and tanggal_timbang<='" . $tgl_akhir . "'";

		if ($spk_id != null && $spk_id != '' && $spk_id != 'null') {
			$retrieveSpk   = $this->db->query("select * from sls_kontrak where id=" . $spk_id . "")->row_array();
			$no_spk = $retrieveSpk['no_spk'];
			$qry = $qry." and spk_id=" . $spk_id . "";
		}	else{
			$no_spk = "Semua";

		}
		
		if ($customer_id) {
			$qry=$qry."  and customer_id=" . $customer_id . "";
			$retrieveCustomer = $this->db->query("select * from gbm_customer where id=" . $customer_id . "")->row_array();
			$nama_customer = $retrieveCustomer['nama_customer'];
		} else {
			$nama_customer = "Semua";
			
		}
		if ($transportir_id) {
			$qry=$qry."  and transportir_id=" . $transportir_id . "";
			$retrieveTranportir = $this->db->query("select * from gbm_supplier where id=" . $transportir_id . "")->row_array();
			$nama_transportir = $retrieveTranportir['nama_supplier'];
		} else {
			$nama_transportir = "Semua";
			
		}
		$qry=$qry." order by tanggal_timbang";
		$retrieveTimbangan = $this->db->query($qry)->result_array();

		
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');

		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report_v2();
		} else {
			$html = get_header_report_v2();
		}


		$html = $html . '
	<div class="row">
  	<div class="span12">
	  <br>
	<div left class="kop-print">
	  <div class="kop-nama">KLINIK ANNAJAH</div>
	  <div class="kop-info"> Jl Sukarno Hatta No 12 No 12, Bandung Jawa Barat 14450</div>
	  <div class="kop-info">Telp : (021) 6684055</div>
	</div>
	  <hr class="kop-print-hr">
  	</div>
  	</div>
  <h3 class="title">LAPORAN REKAP PENGIRIMAN PRODUK</h3>
  <table class="no_border" style="width:30%">
			
			<tr>
					<td>Customer</td>
					<td>:</td>
					<td>' .  $nama_customer . '</td>
			</tr>
			<tr>
					<td>Transportir</td>
					<td>:</td>
					<td>' .  $nama_transportir . '</td>
			</tr>
			<tr>
					<td>NO SPK</td>
					<td>:</td>
					<td>' .  $no_spk . '</td>
			</tr>
			<tr>	
					<td>Periode</td>
					<td>:</td>
					<td>' . tgl_indo($tgl_mulai) . ' s/d ' . tgl_indo($tgl_akhir) . '</td>
			</tr>
			
	</table>
	<br><br>
';
if ($format_laporan == 'pdf') {
$html = $html . ' <table  border="1" width="100%" style="border-collapse: collapse;table-layout:fixed;" >';
}else{
	$html = $html . ' <table  border="1" width="100%" style="border-collapse: collapse;" >';

}
		$html = $html . ' 
			<thead>
				<tr>
					<th width="2%" rowspan="3">No.</th>
					<th width="6%" rowspan="3">Customer</th>
					<th width="6%" rowspan="3">Transportir</th>
					<th width="6%" rowspan="3">No Tiket</th>
					<th width="7%" rowspan="3">NoKontrak</th>
					<th width="5%" rowspan="3">NoKendaraan</th>
					<th width="5%" rowspan="3">Supir</th>
					<th width="5%" rowspan="3">Tanggal Kirim</th>
					<th colspan="6">Pengiriman</th>
					<th width="5%" rowspan="3">Tanggal terima</th>
					<th colspan="4">Penerimaan</th>
					<th colspan="5" style="text-align: center;" >Variance</th>				
				</tr>
				<tr>
					<th style="text-align: center;" colspan="3">Kualitas</th>
					<th colspan="3">Tonase</th>
					<th style="text-align: center;">Kualitas</th>
					<th colspan="3">Tonase</th>
					<th>Kualitas</th>
					<th colspan="3">Tonase</th>
					<th width="5%" rowspan="2">% Selisih Netto</th>
	  			</tr>
				<tr>
					<th style="text-align: right;">FFA(%)</th>
					<th style="text-align: right;">MJ(%)</th>
					<th style="text-align: right;">Mois(%)</th>

					<th width="4%" style="text-align: right;">Gross(Kg)</th>
					<th width="4%" style="text-align: right;">Tare(Kg)</th>
					<th width="4%" style="text-align: right;">Netto(Kg)</th>
					<th style="text-align: right;">FFA(%)</th>

					<th width="4%" style="text-align: right;">Gross(Kg)</th>
					<th width="4%" style="text-align: right;">Tare(Kg)</th>
					<th width="4%" style="text-align: right;">Netto(Kg)</th>
					<th style="text-align: right;">FFA(%)</th>

					<th width="4%" style="text-align: right;">Gross(Kg)</th>
					<th width="4%" style="text-align: right;">Tare(Kg)</th>
					<th width="4%" style="text-align: right;">Netto(Kg)</th>
				</tr>
			</thead>
			<tbody>';


		$no = 0;
		$jum_netto_kirim = 0;
		$jum_bruto_kirim = 0;
		$jum_tara_kirim = 0;
		$jum_netto_customer = 0;
		$jum_bruto_customer = 0;
		$jum_tara_customer = 0;
		$jum_netto_variance = 0;
		$jum_bruto_variance = 0;
		$jum_tara_variance = 0;
		$jum_ffa = 0;
		$jum_dobi = 0;
		$jum_moist = 0;
		$jum_ffa_customer = 0;
		$jum_ffa_variance = 0;
		$variance_ffa = 0;
		$variance_tara = 0;
		$variance_bruto = 0;
		$variance_netto = 0;
		$jum_persen_var_netto = 0;

		foreach ($retrieveTimbangan as $key => $m) {
			$no++;
			$jum_netto_kirim = $jum_netto_kirim + $m['netto_kirim'];
			$jum_bruto_kirim = $jum_bruto_kirim + $m['bruto_kirim'];
			$jum_tara_kirim = $jum_tara_kirim + $m['tara_kirim'];
			$jum_netto_customer = $jum_netto_customer + $m['netto_customer'];
			$jum_bruto_customer = $jum_bruto_customer + $m['bruto_customer'];
			$jum_tara_customer = $jum_tara_customer + $m['tara_customer'];
			$variance_ffa = $m['ffa_customer']-$m['ffa']  ;
			$variance_tara =$m['tara_customer']-$m['tara_kirim'] ;
			$variance_bruto = $m['bruto_customer']-$m['bruto_kirim'] ;
			$variance_netto = $m['netto_customer']-$m['netto_kirim'] ;
			$jum_ffa = $jum_ffa + $m['ffa'];
			$jum_dobi = $jum_dobi + $m['dobi'];
			$jum_moist = $jum_moist + $m['moisture'];
			$jum_ffa_customer = $jum_ffa_customer + $m['ffa_customer'];
			$jum_ffa_variance = $jum_ffa_variance + $variance_ffa;
			$jum_netto_variance = $jum_netto_variance + $variance_netto;
			$jum_bruto_variance = $jum_bruto_variance +	$variance_bruto;
			$jum_tara_variance = $jum_tara_variance +	$variance_tara;
			if ($m['netto_kirim'] > 0) {
				$persen_var_netto = $variance_netto / $m['netto_kirim'] * 100;
			} else {
				$persen_var_netto = 0;
			}
			$jum_persen_var_netto = $jum_persen_var_netto + $persen_var_netto;
			$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative;">' . ($no) . '</td>
						<td >' . $m['nama_customer'] . ' </td>	
						<td >' . $m['nama_transportir'] . ' </td>				
						<td >' . $m['no_tiket'] . ' </td>				
						<td >' . $m['no_spk'] . ' </td>					
						<td>' . $m['no_kendaraan'] . '	</td> 				
						<td>' . $m['nama_supir'] . ' </td>					
						<td style="text-left: center;">' . tgl_indo_normal($m['tanggal_timbang']) . ' </td>										
						<td style="text-align: right;">' . $this->format_number_report($m['ffa'], 2) . ' </td>		
						<td style="text-align: right;">' . $this->format_number_report($m['dobi'], 2) . ' </td>		
						<td style="text-align: right;">' . $this->format_number_report($m['moisture'], 2) . ' </td>		
						<td style="text-align: right;">' . $this->format_number_report($m['bruto_kirim'], 0) . ' </td>		
						<td style="text-align: right;">' . $this->format_number_report($m['tara_kirim'], 0) . ' </td>		
						<td style="text-align: right;">' . $this->format_number_report($m['netto_kirim'], 0) . ' </td>		
						<td style="text-left: center;">' . tgl_indo_normal($m['tanggal_terima']) . ' </td>	
						<td style="text-align: right;">' . $this->format_number_report($m['ffa_customer'], 2) . ' </td>		
						<td style="text-align: right;">' . $this->format_number_report($m['bruto_customer'], 0) . ' </td>		
						<td style="text-align: right;">' . $this->format_number_report($m['tara_customer'], 0) . ' </td>		
						<td style="text-align: right;">' . $this->format_number_report($m['netto_customer'], 0) . ' 	</td>		
						<td style="text-align: right;">' . $this->format_number_report($variance_ffa, 2) . ' </td>		
						<td style="text-align: right;">' . $this->format_number_report($variance_bruto, 0) . ' </td>		
						<td style="text-align: right;">' . $this->format_number_report($variance_tara, 0) . ' </td>		
						<td style="text-align: right;">' . $this->format_number_report($variance_netto, 0) . ' 	</td>
						<td style="text-align: right;">' . $this->format_number_report($persen_var_netto, 2) . '% 	</td>
						';
			$html = $html . '					
					</tr>';
		}

		$html = $html . ' 	
						<tr class=":: arc-content">
						<td style="position:relative;">&nbsp;</td>				
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td style="text-align: right;">' . $this->format_number_report(($jum_ffa / $no), 2) . '</td>					
						<td style="text-align: right;">	' . $this->format_number_report(($jum_dobi / $no), 2) . ' </td>		
						<td style="text-align: right;">	' . $this->format_number_report(($jum_moist / $no), 2) . '	</td>						

						<td  style="text-align: right;">' . $this->format_number_report($jum_bruto_kirim,0) . '</td>					
						<td style="text-align: right;">	' . $this->format_number_report($jum_tara_kirim, 0) . ' </td>		
						<td style="text-align: right;">	' . $this->format_number_report($jum_netto_kirim, 0) . '	</td>						
						<td>&nbsp;</td>
						<td style="text-align: right;">' . $this->format_number_report(($jum_ffa_customer / $no), 2) . '</td>					
						<td style="text-align: right;">	' . $this->format_number_report($jum_bruto_customer, 0) . ' </td>		
						<td style="text-align: right;">	' . $this->format_number_report($jum_tara_customer, 0) . '	</td>	
						<td style="text-align: right;">' . $this->format_number_report($jum_netto_customer, 0) . '</td>					
						<td style="text-align: right;">	' . $this->format_number_report(($jum_ffa_variance / $no), 2) . ' </td>		
						<td style="text-align: right;">	' . $this->format_number_report($jum_bruto_variance, 0) . '	</td>	
						<td style="text-align: right;">' . $this->format_number_report($jum_tara_variance, 0) . '</td>					
						<td style="text-align: right;">	' . $this->format_number_report($jum_netto_variance, 0) . ' </td>		
						<td style="text-align: right;">	' . $this->format_number_report(($jum_persen_var_netto / $no), 2) . '%	</td>	
						</tr>
					</tbody>
				</table>
						';
			$tglHariIni=tgl_indo(date('Y-m-d'));			
			$html=$html."<br>
			<br>
			<br>
	
			
			<table class='no_border' style='margin-top: 35px; page-break-inside: avoid !important' width='50%' >
			<tr>
			 <td width='30%' center style='text-align: center;'></td>
			 <td width='35%' center style='text-align: center;' ></td>
			 <td width='35%' center style='text-align: center;''> Jakarta,". $tglHariIni. "</td>
		    </tr>
			 <tr>
			 	<td width='30%' center style='text-align: center;'></td>
				<td width='35%' center style='text-align: center;' >Prepared by,</td>
				<td width='35%' center style='text-align: center;''>Acknowledged by,</td>
			</tr>
				<tr>
					<td height='100px'></td>	
					<td > </td>
				
					<td ></td>
				</tr>
				
				<tr>
					<td center></td>
					<td center style='text-align: center;'>". $dibuat_oleh."</td>
					<td center style='text-align: center;'>". $disetujui_oleh."</td>
	
				</tr>	
							
			</table>";			




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
			$this->pdfgenerator->generate($html, $filename, true, 'A2', 'landscape');
		}
	}

	function format_number_report($angka,$decimal=2)
	{
		$format_laporan     = $this->post('format_laporan', true);
		$tipe_laporan= $this->post('tipe_laporan', true);
		if($tipe_laporan ){
			$format_laporan=$tipe_laporan;
		}
		// if ($fmt_laporan ) {
		// 	$format_laporan     = $this->post('format_laporan', true);
		// }else{
		// 	return $this->format_number_report($angka);
		// }
		if ($format_laporan == 'xls' || $format_laporan == 'excel') {
			return $angka;
		} else {
			if ($angka == 0) {
				return 0;
			}
			return number_format($angka, $decimal);
		}
	}
}
