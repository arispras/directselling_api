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

class PksTimbangan extends BD_Controller// REST_Controller
{
	public $user_id;
	public $theCredential;
	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('PksTimbanganModel');
		$this->load->model('KaryawanModel');
		$this->load->model('M_DatatablesModel');
		$this->load->library('image_lib');
		$this->load->library('upload');
		$this->load->library('pdfgenerator');
		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id = $this->user_data->id;
	}

	public function list_post()
	{
		$post = $this->post();
		$param=$post['parameter'];

		$query  = "select a.*, d.nama as afdeling,f.nama as estate, b.nama as mill,c.nama_supplier from pks_timbangan a left join gbm_organisasi b on a.mill_id=b.id 
		left join gbm_supplier c on a.supplier_id=c.id
			left join gbm_organisasi d on a.rayon_id=d.id 
			left join gbm_organisasi e on d.parent_id=e.id
			left join gbm_organisasi f on e.parent_id=f.id ";
		$search = array('a.no_tiket', 'a.no_spat', 'a.tanggal', 'd.nama', 'f.nama', 'c.nama_supplier');
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
		if ($param['supplier_id']){
			$isWhere =$isWhere. " and a.supplier_id =".$param['supplier_id']." ";
		}
		// else{
		// 	$isWhere = $isWhere. " and  a.lokasi_id in
		// 	(select location_id from fwk_users_location where user_id=" . $this->user_id . ")";
		// }

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	function index_get($segment_3 = '')
	{
		$id = $segment_3;
		$retrieve = $this->PksTimbanganModel->retrieve($id);

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAll_get()
	{

		$retrieve = $this->PksTimbanganModel->retrieve_all();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getTimbanganInternalBlmSpb_get()
	{

		$retrieve = $this->PksTimbanganModel->get_timbangan_internal_blm_spb();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => []), REST_Controller::HTTP_OK);
		}
	}
	function getTimbanganExternalBlmRekap_post()
	{

		$supp_id = $this->post('supp_id');
		$tgl_mulai = $this->post('tgl_mulai');
		$tgl_sd = $this->post('tgl_sd');
		$retrieve = $this->PksTimbanganModel->get_timbangan_external_blm_rekap($supp_id, $tgl_mulai, $tgl_sd);

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => []), REST_Controller::HTTP_OK);
		}
	}
	function create_post()
	{

		$data = $this->post();
		$data['diubah_oleh'] = $this->user_id;
		$res =  $this->PksTimbanganModel->create($data);
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'pks_timbangan', 'action' => 'new', 'entity_id' => $res);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $data['no_tiket']), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function update_post($segment_3 = '')
	{


		$id = (int)$segment_3;
		$timbangan = $this->PksTimbanganModel->retrieve($id);
		if (empty($timbangan)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$data = $this->post();
		$data['diubah_oleh'] = $this->user_id;
		if (!$data['uoid']){
			$data['uoid']=$timbangan['uoid'];
		}
		$res =   $this->PksTimbanganModel->update($timbangan['id'], $data);
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'pks_timbangan', 'action' => 'edit', 'entity_id' => $id);
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
		$item = $this->PksTimbanganModel->retrieve($id);
		if (empty($item)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$retrieve =  $this->PksTimbanganModel->delete($item['id']);
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id'=>$id)), 'entity' => 'pks_timbangan', 'action' => 'delete', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function laporan_rincian_penerimaan_tbs_post()
	{

		error_reporting(0);
		$mill_id     = $this->post('mill_id', true);
		$tgl_mulai =  $this->post('tgl_mulai', true);
		$tgl_akhir = $this->post('tgl_akhir', true);
		$tipe = $this->post('tipe', true);
		$tipe_laporan = $this->post('tipe_laporan', true);
		$format_laporan=$tipe_laporan;
		$where = "";
		$retrieveMill = $this->db->query("select * from gbm_organisasi where id=" . $mill_id . "")->row_array();
		if ($tipe != "") {
			$where = "and tipe='" . $tipe . "'";
		} else {
			$where = "";
		}
		$retrieveTimbangan = $this->db->query("select * from pks_timbangan_terima_tbs_vw 
		where mill_id=" . $mill_id . " and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
		" . $where . " order by tanggal")->result_array();

		// var_dump($results);
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');

		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report();
			$html= $html.'
				<style>
				*{
					font-size: 6.2px ;
				}
				th,
				td {
				  padding: 3px 3px;
				  vertical-align: middle;
				}
				</style>';
		} else {
			$html = get_header_report_v2();
		}
		$html = $html . '
		<h3 class="title">LAPORAN RINCIAN PENERIMAAN TBS</h3>
  <table class="no_border" style="width:30%">
			
			<tr>
					<td>Mill</td>
					<td>:</td>
					<td>' . $retrieveMill['nama'] . '</td>
			</tr>
			<tr>	
					<td>Periode</td>
					<td>:</td>
					<td>' . tgl_indo($tgl_mulai) . ' s/d ' . tgl_indo($tgl_akhir) . '</td>
			</tr>
			
	</table>
			<br>';

		$html = $html . ' <table  border="1" width="100%" style="border-collapse: collapse;">
			<thead>
				<tr>
					<th width="4%" rowspan="2">No.</th>
					<th rowspan="2">Supplier/Afdeling</th>
					<th rowspan="2">Tanggal</th>
					<th rowspan="2">No Tiket</th>
					<th rowspan="2">No SPB</th>
					<th rowspan="2">No Kendaraan</th>
					<th rowspan="2">Supir</th>
					<th rowspan="2">Int/Ext</th>
					<th rowspan="2" style="text-align: right;">Jumlah Janjang </th>
					<th rowspan="2"style="text-align: right;">Bruto (Kg) </th>
					<th rowspan="2"style="text-align: right;">Tara (Kg)</th>
					<th rowspan="2"style="text-align: right;">Netto (Kg)</th>
					<th rowspan="2"style="text-align: right;">Pot(Kg) Grading </th>
					<th rowspan="2"style="text-align: right;">Pot(%) Grading </th>
					<th rowspan="2"style="text-align: right;">Pot(Kg) </th>
					<th rowspan="2"style="text-align: right;">Pot(%) </th>
					<th rowspan="2"style="text-align: right;">Diterima PKS(Kg) </th>
					<th style="text-align: center;" colspan="11" >Grading </th>
				</tr>
				<tr>
					<th  style="text-align: right;">Janjang Grading </th>
					<th  style="text-align: right;">Buah Mentah </th>
					<th  style="text-align: right;">Tandan Kosong </th>
					<th  style="text-align: right;">Lewat Matang </th>
					<th  style="text-align: right;">Tangkai Panjang </th>
					<th  style="text-align: right;">Partenocerpy </th>
					<th  style="text-align: right;">Buah Kecil </th>
					<th  style="text-align: right;">Restan </th>
					<th  style="text-align: right;">Buah Batu </th>
					<th  style="text-align: right;">Kotoran Sampah </th>
					<th  style="text-align: right;">Brondolan </th>

				</tr>
			</thead>
			<tbody>';


		$no = 0;
		$berat_bersih = 0;
		$berat_potongan = 0;
		$berat_terima = 0;
		$jumlah_janjang = 0;
		$berat_isi = 0;
		$berat_kosong = 0;
		$berat_potongan_real = 0;
		foreach ($retrieveTimbangan as $key => $m) {
			$berat_potongan_real = $berat_potongan_real + $m['berat_potongan_real'];
			$no++;
			$jumlah_janjang = $jumlah_janjang + $m['jumlah_janjang'];
			$berat_isi = $berat_isi + $m['berat_isi'];
			$berat_kosong = $berat_kosong + $m['berat_kosong'];
			$berat_bersih = $berat_bersih + $m['berat_bersih'];
			$berat_potongan = $berat_potongan + $m['berat_potongan'];
			$berat_terima = $berat_terima + $m['berat_terima'];
			$supp = "";
			if ($m['nama_supplier'] == null) {
				$supp =  $m['nama_rayon'];
			} else {
				$supp = $m['nama_supplier'];
			}
			$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative;">	' . ($no) . '</td>
						<td>' . $supp . ' </td>
						<td>' . tgl_indo_normal($m['tanggal']) . ' </td>
						<td>
						' . $m['no_tiket'] . ' 
						
						</td>
						<td>
						' . $m['no_spat'] . ' 
						
						</td>
						<td>
							' . $m['no_plat'] . ' 
						</td>
						<td>
							' . $m['nama_supir'] . ' 
						</td>
						<td>
							' . $m['tipe'] . ' 
						</td>
						<td style="text-align: right;">' . $this->format_number_report($m['jumlah_janjang']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['berat_isi']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['berat_kosong']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['berat_bersih']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['berat_potongan_real']) . ' 
						<td style="text-align: right;">' . $this->format_number_report(($m['berat_potongan_real'] / $m['berat_bersih'] * 100), 2) . '  
						<td style="text-align: right;">' . $this->format_number_report($m['berat_potongan']) . '
						<td style="text-align: right;">' . $this->format_number_report($m['berat_potongan_persen'], 2) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['berat_terima']) . ' 

						
						<td style="text-align: right;">' . $this->format_number_report($m['grading_janjang']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['grading_mentah']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['grading_jangkos']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['grading_lewat_matang']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['grading_tangkai_panjang']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['grading_buah_partenocerpy']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['grading_buah_kecil']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['grading_restan']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['grading_buah_batu']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['grading_air_sampah']) . '
						<td style="text-align: right;">' . $this->format_number_report($m['grading_brondolan']) . '

							
						</td>';

			$html = $html . '</tr>';
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
					
						<td style="text-align: right;">
						' . $this->format_number_report($jumlah_janjang) . ' 
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($berat_isi) . ' 
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($berat_kosong) . ' 
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($berat_bersih) . ' 
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($berat_potongan_real) . ' 
						</td>
						<td style="text-align: right;">
						
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($berat_potongan) . ' 
						</td>
						<td style="text-align: right;">
						
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($berat_terima) . ' 
						</td>
						<td colspan="11">
						
						</td>
						
						</tr>
								</tbody>
							</table>
						';
		if ($tipe_laporan == 'excel') {
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
		}
		else if($tipe_laporan == 'pdf'){
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		} 
		else {
			echo $html;
		}
	}
	function get_path_file($img = '', $size = '')
	{
		if (empty($size)) {
			return  $_SERVER['DOCUMENT_ROOT'] . "/"  . 'hrms' . '/userfiles/files/' . $img;
		} else {
			$pisah = explode('.', $img);
			$ext = end($pisah);
			$nama_file = $pisah[0];

			return  $_SERVER['DOCUMENT_ROOT'] . "/"  . 'hrms' . '/userfiles/files/' . $nama_file . '_' . $size . '.' . $ext;
		}
	}
	function laporan_harian_penerimaan_tbs_post()
	{
		error_reporting(0);

		$mill_id     = $this->post('mill_id', true);
		$periode =  $this->post('periode', true);
		$tipe = $this->post('tipe', true);
		$tipe_laporan = $this->post('tipe_laporan', true);
		$format_laporan=$tipe_laporan;
		if ($tipe != "") {
			$where = "and tipe='" . $tipe . "'";
		} else {
			$where = "";
		}

		$date = new DateTime($periode . '-01');
		$date->modify('last day of this month');
		$last_day_this_month = $date->format('Y-m-d');
		(int)$jumhari = date('d', strtotime($last_day_this_month));
		$tgl_mulai = $periode . '-01';
		$tgl_akhir = $periode . '-' . sprintf("%02d", $jumhari);

		$retrieveMill = $this->db->query("select * from gbm_organisasi where id=" . $mill_id . "")->result_array();

		// var_dump($results);
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');

		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report_v2();
			$html= $html.'
				<style>
				*{
					font-size: 5px ;
				}
				th,
				td {
				  padding: 3px 3px;
				  vertical-align: middle;
				}
				</style>';
		} else {
			$html = get_header_report_v2();
		}
		$html = $html . '
		<h3 class="title">LAPORAN PENERIMAAN TBS HARIAN</h3>
  <table class="no_border" style="width:30%">
			
			<tr>
					<td>Mill</td>
					<td>:</td>
					<td>' . $retrieveMill[0]['nama'] . '</td>
			</tr>
			<tr>	
					<td>Periode</td>
					<td>:</td>
					<td>' . tgl_indo($periode) . '</td>
			</tr>
			
	</table>
			<br>
		';

		$html = $html . "

<table   border='0.3' width='100%' style='border-collapse: collapse;'>
<thead>
<tr>
	<td rowspan=2 >No</td>
	<td rowspan=2>Supplier/Kebun</td>
	<td colspan=" . $jumhari . "  style='text-align: center'> " . tgl_indo($periode) . "  </td>
	<td rowspan=2  style='text-align: center'>TOTAL(Kg)</td>
</tr>
";

		$html = $html . "<tr>";
		for ($i = 1; $i < ($jumhari + 1); $i++) {
			$html = $html . "<td style='text-align: center'>" . $i . "</td>";
		}
		$html = $html . "</tr> </thead>";
		$nourut = 0;
		$grandtotal = 0;
		$totalPerHari = array();
		$totalPerHari = [];
		
		// retrive data rayon  === 
		//NOTE:(RAYON = AFDELING karena ada perubahan rayon ke Afdeling di pertengan implemntasi)
		$qry = "SELECT DISTINCT rayon_id,nama_rayon,nama_estate FROM pks_timbangan_terima_tbs_vw WHERE (rayon_id IS NOT NULL AND rayon_id <>0)
		and mill_id=" . $mill_id . " and tanggal>='" . $tgl_mulai . "' 
		and tanggal<='" . $tgl_akhir . "' " . $where;
		$qry=$qry." order by nama_rayon ;";
		$retrieveDivisi = $this->db->query($qry)->result_array();

		for ($i = 1; $i < ($jumhari + 1); $i++) {
			$totalPerHari[] = 0;
		}

		foreach ($retrieveDivisi as $key => $d) {
			$html = $html . "<tr>";
			$totalkg = 0;
			$nourut = $nourut + 1;
			$html = $html . "<td style='text-align: center'>" . $nourut . "</td>";
			$html = $html . "<td style='text-align: left'>" . $d['nama_rayon'] . "</td>";
			for ($i = 1; $i < ($jumhari + 1); $i++) {
				$tgl = $periode  . '-' . sprintf("%02d", $i);
				$retrieveKgDiv = $this->db->query("SELECT SUM(berat_bersih)as beratkg FROM pks_timbangan_terima_tbs_vw WHERE (rayon_id =" . $d['rayon_id'] . ")
				and mill_id=" . $mill_id . " and tanggal='" . $tgl . "'")->row_array();
				$beratkg = $retrieveKgDiv['beratkg'] ? $retrieveKgDiv['beratkg'] : 0;
				$index = "idx" . $i;
				// $jum=0;
				if (array_key_exists(($i-1), $totalPerHari)) {
					$totalPerHari[$i - 1] = $totalPerHari[$i - 1] + $beratkg;
				} else {
					$totalPerHari[] = $beratkg;
				}

				$totalkg = $totalkg + $beratkg;
				$grandtotal = $grandtotal + $beratkg;
				$html = $html . "<td style='text-align: right'>" . $this->format_number_report($beratkg) . " </td>";
			}
			$html = $html . "<td style='text-align: right'>" . $this->format_number_report($totalkg) . " </td>";
			$html = $html . "</tr>";
		}



		// retrive data SUpplier  
		$qry = "SELECT DISTINCT supplier_id,nama_supplier FROM pks_timbangan_terima_tbs_vw WHERE (supplier_id IS NOT NULL AND supplier_id <>0)
		and mill_id=" . $mill_id . " and tanggal>='" . $tgl_mulai . "' 
		and tanggal<='" . $tgl_akhir . "'  " . $where;
		$qry=$qry." order by nama_supplier ;";
		
		$retrieveSupplier = $this->db->query($qry)->result_array();

		foreach ($retrieveSupplier as $key => $s) {
			$html = $html . "<tr>";
			$totalkg = 0;
			$nourut = $nourut + 1;
			$html = $html . "<td style='text-align: center'>" . $nourut . "</td>";
			$html = $html . "<td style='text-align: left'>" . $s['nama_supplier'] . "</td>";
			for ($i = 1; $i < ($jumhari + 1); $i++) {

				$tgl = $periode  . '-' . sprintf("%02d", $i);
				$retrieveKgSupp = $this->db->query("SELECT SUM(berat_bersih)as beratkg FROM pks_timbangan_terima_tbs_vw WHERE (supplier_id =" . $s['supplier_id'] . ")
				and mill_id=" . $mill_id . " and tanggal='" . $tgl . "'")->row_array();
				$beratkg = $retrieveKgSupp['beratkg'] ? $retrieveKgSupp['beratkg'] : 0;
				$totalPerHari[$i - 1] = ($totalPerHari[$i - 1] ? $totalPerHari[$i - 1] : 0) + $beratkg;
				$totalkg = $totalkg + $beratkg;
				$grandtotal = $grandtotal + $beratkg;
				$html = $html . "<td style='text-align: right'>" . $this->format_number_report($beratkg) . " </td>";
			}
			$html = $html . "<td style='text-align: right'>" . $this->format_number_report($totalkg) . " </td>";
			$html = $html . "</tr>";
		}


		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: center'> </td>";
		$html = $html . "<td style='text-align: center'></td>";
		for ($i = 1; $i < ($jumhari + 1); $i++) {

			$html = $html . "<td style='text-align: right'>" . $this->format_number_report($totalPerHari[$i - 1]) . " </td>";
		}
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($grandtotal) . " </td>";
		$html = $html . "</tr>";
		$html = $html . "</table>";

		if ($tipe_laporan == 'excel') {
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
		}
		else if($tipe_laporan == 'pdf'){
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		} 
		else {
			echo $html;
		}
	}
	function laporan_bulanan_penerimaan_tbs_post()
	{

		error_reporting(0);
		$tipe_laporan     = $this->post('tipe_laporan', true);
		$mill_id     = $this->post('mill_id', true);
		$tahun =  $this->post('tahun', true);
		$tipe =  $this->post('tipe', true);

		if ($tipe != "") {
			$where = "and tipe='" . $tipe . "'";
		} else {
			$where = "";
		}

		$tgl_mulai = $tahun . '-01-01';
		$tgl_akhir = $tahun . '-12-31';
	
		$retrieveMill = $this->db->query("select * from gbm_organisasi where id=" . $mill_id . "")->row_array();

		// var_dump($results);
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');

		if ($tipe_laporan == 'pdf') {
			$html = get_header_pdf_report();
			$html= $html.'
				<style>
				*{
					font-size: 7px ;
				}
				th,
				td {
				  padding: 3px 3px;
				  vertical-align: middle;
				}
				</style>';
		} else {
			$html = get_header_report_v2();
		}

		$html = $html . '
		<h3 class="title">LAPORAN PENERIMAAN TBS BULANAN</h3>
  <table class="no_border" style="width:20%">
			
			<tr>
					<td>Mill</td>
					<td>:</td>
					<td>' . $retrieveMill['nama'] . '</td>
			</tr>
			<tr>	
					<td>Tahun</td>
					<td>:</td>
					<td>' . $tahun  . '</td>
			</tr>
			
	</table>
			<br>';

		$html = $html . "

<table   border='1' width='100%' style='border-collapse: collapse;'>
<thead>
<tr>
	<td rowspan=2 >No</td>
	<td rowspan=2 style='text-align: center'>Supplier/Kebun</td>
	<td colspan=12  style='text-align: center'> " . $tahun . "  </td>
	<td rowspan=2  style='text-align: center'>TOTAL(Kg)</td>
</tr>
";

		$html = $html . "<tr>";
		for ($i = 1; $i < (12 + 1); $i++) {
			$html = $html . "<td style='text-align: center'>" . $i . "</td>";
		}
		$html = $html . "</tr> </thead>";
		$nourut = 0;
		$grandtotal = 0;
		$totalPerBulan = array();

		// retrive data DIVISI
		// NOTE : RAYON=AFDELING filed blm diubah krn ada perubahan DItengah implentasi  
		$qry = "SELECT DISTINCT rayon_id,nama_rayon,nama_estate FROM pks_timbangan_terima_tbs_vw WHERE (rayon_id IS NOT NULL AND rayon_id <>0)
		and mill_id=" . $mill_id . " and tanggal>='" . $tgl_mulai . "' 
		and tanggal<='" . $tgl_akhir . "' " . $where;
		$qry=$qry." order by nama_rayon ;";
		$retrieveDivisi = $this->db->query($qry)->result_array();

		for ($i = 1; $i < (12 + 1); $i++) {
			$totalPerBulan[] = 0;
		}

		foreach ($retrieveDivisi as $key => $d) {
			$html = $html . "<tr>";
			$totalkg = 0;
			$nourut = $nourut + 1;
			$html = $html . "<td style='text-align: center'>" . $nourut . "</td>";
			$html = $html . "<td style='text-align: left'>" . $d['nama_rayon'] . "</td>";
			for ($i = 1; $i < (12 + 1); $i++) {
				$yymm = $tahun  . '-' . sprintf("%02d", $i);
				$retrieveKgDiv = $this->db->query("SELECT SUM(berat_bersih)as beratkg FROM pks_timbangan_terima_tbs_vw WHERE (rayon_id =" . $d['rayon_id'] . ")
				and mill_id=" . $mill_id . " and DATE_FORMAT(tanggal, '%Y-%m')='" . $yymm . "'")->row_array();
				$beratkg = $retrieveKgDiv['beratkg'] ? $retrieveKgDiv['beratkg'] : 0;

				if (array_key_exists($i, $totalPerBulan)) {
					$totalPerBulan[$i - 1] = $totalPerBulan[$i - 1] + $beratkg;
				} else {
					$totalPerBulan[] = $beratkg;
				}

				$totalkg = $totalkg + $beratkg;
				$grandtotal = $grandtotal + $beratkg;
				$html = $html . "<td style='text-align: right'>" . $this->format_number_report($beratkg) . " </td>";
			}
			$html = $html . "<td style='text-align: right'>" . $this->format_number_report($totalkg) . " </td>";
			$html = $html . "</tr>";
		}

		// retrive data SUpplier  
		$qry = "SELECT DISTINCT supplier_id,nama_supplier FROM pks_timbangan_terima_tbs_vw WHERE (supplier_id IS NOT NULL AND supplier_id <>0)
		and mill_id=" . $mill_id . " and tanggal>='" . $tgl_mulai . "' 
		and tanggal<='" . $tgl_akhir . "' " . $where;
		$qry=$qry." order by nama_supplier ;";
		$retrieveSupplier = $this->db->query($qry)->result_array();

		foreach ($retrieveSupplier as $key => $s) {
			$html = $html . "<tr>";
			$totalkg = 0;
			$nourut = $nourut + 1;
			$html = $html . "<td style='text-align: center'>" . $nourut . "</td>";
			$html = $html . "<td style='text-align: left'>" . $s['nama_supplier'] . "</td>";
			for ($i = 1; $i < (12 + 1); $i++) {

				$yymm = $tahun  . '-' . sprintf("%02d", $i);
				$retrieveKgSupp = $this->db->query("SELECT SUM(berat_bersih)as beratkg FROM pks_timbangan_terima_tbs_vw WHERE (supplier_id =" . $s['supplier_id'] . ")
				and mill_id=" . $mill_id . " and DATE_FORMAT(tanggal, '%Y-%m')='" . $yymm . "'")->row_array();
				$beratkg = $retrieveKgSupp['beratkg'] ? $retrieveKgSupp['beratkg'] : 0;
				$totalPerBulan[$i - 1] = ($totalPerBulan[$i - 1] ? $totalPerBulan[$i - 1] : 0) + $beratkg;
				$totalkg = $totalkg + $beratkg;
				$grandtotal = $grandtotal + $beratkg;
				$html = $html . "<td style='text-align: right'>" . $this->format_number_report($beratkg) . " </td>";
			}
			$html = $html . "<td style='text-align: right'>" . $this->format_number_report($totalkg) . " </td>";
			$html = $html . "</tr>";
		}


		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: center'> </td>";
		$html = $html . "<td style='text-align: center'></td>";
		for ($i = 1; $i < (12 + 1); $i++) {

			$html = $html . "<td style='text-align: right'>" . $this->format_number_report($totalPerBulan[$i - 1]) . " </td>";
		}
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($grandtotal) . " </td>";
		$html = $html . "</tr>";
		$html = $html . "</table>";
			if ($tipe_laporan == 'excel') {
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
		} 
		else if($tipe_laporan == 'pdf'){
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
		else {
			echo $html;
		}
	}


	function laporan_rincian_penerimaan_tbs_int_post()
	{
		// error_reporting(0);
		$tipe_laporan = $this->post('tipe_laporan', true);

		$tgl_mulai =  $this->post('tgl_mulai', true);
		$tgl_akhir = $this->post('tgl_akhir', true);

		$qry = "SELECT d.kode as kode_estate, b.id,b.kode,b.nama,SUM(berat_terima)AS jum_kg,COUNT(no_tiket)AS jum_rit FROM pks_timbangan a 
		LEFT JOIN gbm_organisasi b ON a.rayon_id=b.id
		LEFT join gbm_organisasi c ON b.parent_id=c.id
		LEFT join gbm_organisasi d ON c.parent_id=d.id
		WHERE (DATE(a.tanggal) >= '" . $tgl_mulai . "' and DATE(a.tanggal) <= '" . $tgl_akhir . "') and  a.tipe='INT'
		GROUP BY  b.id,b.kode,b.nama,kode_estate
		ORDER BY b.nama";

		$retrieveTimbangan = $this->db->query($qry)->result_array();


		// var_dump($results);
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');

		if ($tipe_laporan == 'pdf') {
			$html = get_header_pdf_report();
			$html= $html.'
				<style>
				*{
					font-size: 9px ;
				}
				th,
				td {
				  padding: 3px 3px;
				  vertical-align: middle;
				}
				</style>';
		} else {
			$html = get_header_report_v2();
		}
		$html = $html . '
<h3 class="title">LAPORAN PENERIMAAN TBS INTERNAL</h3>
  <table class="no_border" style="width:30%">
			
			
			<tr>	
					<td>Periode</td>
					<td>:</td>
					<td>' . tgl_indo($tgl_mulai) . ' s/d ' . tgl_indo($tgl_akhir) . '</td>
			</tr>
			
	</table>
			<br>';

		$html = $html . ' <table  border="1" width="100%" style="border-collapse: collapse;"  >
			<thead>
				<tr>
					<th colspan="4">Tonase Timbangan Mill</th>
				</tr>
				<tr>
					<th>Estate</th>
					<th>Afd</th>
					<th>Kg</th>
					<th>Rit</th>
				</tr>
			 </thead>
			<tbody>';

		for ($i = 0; $i < count($retrieveTimbangan); $i++) {
			$timbangan[$retrieveTimbangan[$i]['kode_estate']][] = $retrieveTimbangan[$i];
		}

		// echo'<pre>'; print_r($timbangan); die;

		$total_grand_kg = 0;
		$total_grand_rit = 0;
		foreach ($timbangan as $key => $val) {
			$total_kg = 0;
			$total_rit = 0;
			for ($i = 0; $i < count($val); $i++) {
				$html = $html . '<tr class=":: arc-content">';
				if ($val[$i]['kode_estate'] !== $val[$i - 1]['kode_estate']) {
					$html = $html . '<td style="position:relative;" rowspan="' . count($val) . '">' . ($val[$i]['kode_estate']) . '</td>';
				}
				$html = $html . '<td style="position:relative;">' . $val[$i]['nama'] . '</td>';
				$html = $html . '<td style="position:relative; text-align: right;">' . $this->format_number_report($val[$i]['jum_kg']) . '</td>';
				$html = $html . '<td style="position:relative; text-align: right;">' . $this->format_number_report($val[$i]['jum_rit']) . '</td>';
				$html = $html . '</tr>';
				$total_kg += $val[$i]['jum_kg'];
				$total_rit += $val[$i]['jum_rit'];
			}
			$html = $html . '<tr class=":: arc-content">
				<td colspan="2"><b>TOTAL ' . $key . '</b></td>
				<td style="text-align: right;"><b>' . $this->format_number_report($total_kg) . '</b></td>
				<td style="text-align: right;"><b>' . $this->format_number_report($total_rit) . '</b></td>
			</tr>';
			$total_grand_kg += $total_kg;
			$total_grand_rit += $total_rit;
		}
		$html = $html . '<tr class=":: arc-content">
			<td colspan="2"><b>GRAND TOTAL</b></td>
			<td style="text-align: right;"><b>' . $this->format_number_report($total_grand_kg) . '</b></td>
			<td style="text-align: right;"><b>' . $this->format_number_report($total_grand_rit) . '</b></td>
		</tr>';

		if ($tipe_laporan == 'excel') {
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
		} 
		else if($tipe_laporan == 'pdf'){
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}else {
			echo $html;
		}
	}



	function laporan_rincian_penerimaan_tbs_ext_post()
	{
		error_reporting(0);
		$tipe_laporan = $this->post('tipe_laporan', true);

		$mill_id     = $this->post('mill_id', true);
		$supplier_id     = $this->post('supplier_id', true);
		$tgl_mulai =  $this->post('tgl_mulai', true);
		$tgl_akhir = $this->post('tgl_akhir', true);

		$retrieveMill = $this->db->query("select * from gbm_organisasi where id=" . $mill_id . "")->row_array();
		$qry = "";
		if ($supplier_id) {
			$retrieveSupplier = $this->db->query("select * from gbm_supplier where id=" . $supplier_id . "")->row_array();
			$nama_supplier = $retrieveSupplier['nama_supplier'];
			$qry = "select * from pks_timbangan_terima_tbs_vw 
		 where mill_id=" . $mill_id . " and tanggal>='" . $tgl_mulai . "' 
		 and tanggal<='" . $tgl_akhir . "' and supplier_id=" . $supplier_id . " and tipe='EXT'
		  order by tanggal,nama_supplier";
		} else {
			$nama_supplier = "Semua";
			$qry = "select * from pks_timbangan_terima_tbs_vw 
		 where mill_id=" . $mill_id . " and tanggal>='" . $tgl_mulai . "' 
		 and tanggal<='" . $tgl_akhir . "' and tipe='EXT'
		  order by tanggal,nama_supplier";
		}
		$retrieveTimbangan = $this->db->query($qry)->result_array();


		// var_dump($results);
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');

		if ($tipe_laporan == 'pdf') {
			$html = get_header_pdf_report();
			$html= $html.'
				<style>
				*{
					font-size: 6px ;
				}
				th,
				td {
				  padding: 3px 3px;
				  vertical-align: middle;
				}
				</style>';
		} else {
			$html = get_header_report_v2();
		}
		$html = $html . '
		<h3 class="title">LAPORAN PENERIMAAN TBS EXTERNAL</h3>
  <table class="no_border" style="width:30%">
			
			<tr>
					<td>Mill</td>
					<td>:</td>
					<td>' . $retrieveMill['nama'] . '</td>
			</tr>
			<tr>	
					<td>Supplier</td>
					<td>:</td>
					<td>' . $nama_supplier .  '</td>
			</tr>
			<tr>	
					<td>Periode</td>
					<td>:</td>
					<td>' . tgl_indo($tgl_mulai) . ' s/d ' . tgl_indo($tgl_akhir) . '</td>
			</tr>
			
	</table>
			<br>';

		$html = $html . ' <table  border="1" width="100%" style="border-collapse: collapse;"  >
			<thead>
				<tr>
					<th width="4%" rowspan=2>No.</th>
					<th rowspan=2>Supplier</th>
					<th rowspan=2>Tanggal</th>
					<th rowspan=2>No Tiket</th>
					<th rowspan=2>No SPB</th>
					<th rowspan=2>No Kendaraan</th>
					<th rowspan=2>Supir</th>
					<th rowspan=2>Jumlah Janjang</th>
					<th rowspan=2>Harga (Rp)</th>
					<th rowspan=2 style="text-align: right;">Bruto(Kg)</th>
					<th rowspan=2 style="text-align: right;">Tara(Kg)</th>
					<th rowspan=2 style="text-align: right;">Netto(Kg)</th>
					<th rowspan=2 style="text-align: right;">Pot(Kg) Grading</th>
					<th rowspan=2 style="text-align: right;">Pot(%) Grading </th>
					<th rowspan=2 style="text-align: right;">Pot(Kg) </th>
					
					<th rowspan=2 style="text-align: right;">Pot(%) </th>
					<th rowspan=2 style="text-align: right;">Diterima PKS (Kg)</th>
					<th rowspan=2 style="text-align: right;">Total Nilai Terima(Rp) </th>
					<th colspan=11 >Grading </th>
				</tr>
				<tr>
					<th  style="text-align: right;">Janjang Grading </th>
					<th  style="text-align: right;">Buah Mentah </th>
					<th  style="text-align: right;">Tandan Kosong </th>
					<th  style="text-align: right;">Lewat Matang </th>
					<th  style="text-align: right;">Tangkai Panjang </th>
					<th  style="text-align: right;">Partenocerpy </th>
					<th  style="text-align: right;">Buah Kecil </th>
					<th  style="text-align: right;">Restan </th>
					<th  style="text-align: right;">Buah Batu </th>
					<th  style="text-align: right;">Kotoran Sampah </th>
					<th  style="text-align: right;">Brondolan </th>

				</tr>
			 </thead>
			<tbody>';


		$no = 0;
		$berat_bersih = 0;
		$berat_potongan = 0;
		$berat_potongan_real = 0;
		$berat_terima = 0;
		$berat_isi = 0;
		$berat_kosong = 0;
		$jumlah_janjang = 0;
		$jumtotal_terima = 0;


		foreach ($retrieveTimbangan as $key => $m) {
			$no++;
			$jumlah_janjang = $jumlah_janjang + $m['jumlah_janjang'];
			$berat_isi = $berat_isi + $m['berat_isi'];
			$berat_kosong = $berat_kosong + $m['berat_kosong'];
			$berat_bersih = $berat_bersih + $m['berat_bersih'];
			$berat_potongan = $berat_potongan + $m['berat_potongan'];
			$berat_potongan_real = $berat_potongan_real + $m['berat_potongan_real'];
			$berat_terima = $berat_terima + $m['berat_terima'];
			$persen_potongan = ($m['berat_potongan_real'] / $m['berat_bersih']) * 100;
			$supp = "";
			if ($m['nama_supplier'] == null) {
				$supp = $m['nama_estate'] . "-" . $m['nama_rayon'];
			} else {
				$supp = $m['nama_supplier'];
			}
			$harga = 0;
			$retrieveHarga = array();
			if ($supplier_id) {
				$retrieveHarga = $this->db->query(
					"select * from pks_harga_tbs where
				tanggal_efektif<= '" . $m['tanggal']  . "' and supplier_id =" . $supplier_id . "
				order by tanggal_efektif desc limit 1 "
				)->row_array();
			} else {
				$retrieveHarga = $this->db->query(
					"select * from pks_harga_tbs where
				tanggal_efektif<= '" . $m['tanggal']  . "' 
				order by tanggal_efektif desc limit 1 "
				)->row_array();
			}


			if ($retrieveHarga) {
				$harga = $retrieveHarga['harga'] ? $retrieveHarga['harga'] : 0;
			}
			$total_diterima = $harga * $m['berat_terima'];
			$jumtotal_terima = $jumtotal_terima + $total_diterima;
			$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative;">
							' . ($no) . '

						</td>
						<td>
						' . $supp . ' 
							
						</td>
						<td>
						' . tgl_indo_normal($m['tanggal']) . ' 
						
						</td>
						<td>
						' . $m['no_tiket'] . ' 
						
						</td>
						<td>
						' . $m['no_spat'] . ' 
						
						</td>
						<td>
							' . $m['no_plat'] . ' 
						</td>
						<td>
							' . $m['nama_supir'] . ' 
						</td>
						<td style="text-align: right;">' . $this->format_number_report($m['jumlah_janjang']) . ' 
						<td>
							' . $this->format_number_report($harga) . ' 
						</td>
						<td style="text-align: right;">' . $this->format_number_report($m['berat_isi']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['berat_kosong']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['berat_bersih']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['berat_potongan_real']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($persen_potongan, 2) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['berat_potongan']) . ' 
						
						<td style="text-align: right;">' . $this->format_number_report($m['berat_potongan_persen'], 2) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['berat_terima']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($total_diterima) . ' 
						
						<td style="text-align: right;">' . $this->format_number_report($m['grading_janjang']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['grading_mentah']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['grading_jangkos']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['grading_lewat_matang']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['grading_tangkai_panjang']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['grading_buah_partenocerpy']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['grading_buah_kecil']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['grading_restan']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['grading_buah_batu']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['grading_air_sampah']) . '
						<td style="text-align: right;">' . $this->format_number_report($m['grading_brondolan']) . '
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
						<td style="text-align: right;">
						' . $this->format_number_report($jumlah_janjang) . ' 
						</td>
						<td>
							&nbsp;
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($berat_isi) . ' 
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($berat_kosong) . ' 
						</td>
						
						<td style="text-align: right;">
						' . $this->format_number_report($berat_bersih) . ' 
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($berat_potongan_real) . ' 
						</td>
						<td style="text-align: right;">
						&nbsp;
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($berat_potongan) . ' 
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($berat_terima) . ' 
						</td>
						<td style="text-align: right;">
						
						</td>
						<td style="text-align: right;">
						' . $this->format_number_report($jumtotal_terima) . ' 
						</td>
						<td colspan=11 >
							&nbsp;
						</td>
						</tr>
								</tbody>
							</table>
						';
	
		if ($tipe_laporan == 'excel') {
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
		} else if($tipe_laporan == 'pdf'){
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}else {
			echo $html;
		}
	}


	public function laporan_rekap_penerimaan_tbs_post()
	{
		error_reporting(0);
		date_default_timezone_set('Asia/Makassar');

		$data = [];
		// if (isset($this->post()['tanggal'])) {
		// 	$input = $this->post();
		// 	$input['lokasi'] = $input['lokasi_id'];
		// }else {
		// 	$input = [
		// 		'lokasi'=> 260,
		// 		'tanggal'=> '2022-01-26',
		// 	];
		// }
		$tipe_laporan = $this->post('tipe_laporan', true);
		$input = $this->post();
		$fk = [
			'inti' => 'SBNE',
			'plasma' => 'SBME',
		];

		$parseTanggal = explode('-', $input['tanggal']);
		$parseTanggalAkhir = explode('-', $input['tgl_akhir']);
		$input['tgl_mulai'] = $parseTanggal[0] . '-' . $parseTanggal[1] . '-01';
		$input['tgl_akhir'] = $input['tanggal'];
		$input['tgl_mulai_1'] = $parseTanggal[0] . '-01-01';

		// $gbm_organisasi = $this->GbmOrganisasiModel->retrieve($input['lokasi']);
		// $input['lokasi_nama'] = $gbm_organisasi['nama'];


		$query = $this->db->query("
			SELECT 
				a.* 
			FROM 
				pks_timbangan a
			INNER JOIN 
				inv_item b ON a.item_id = b.id
			INNER JOIN
				gbm_organisasi c ON a.mill_id = c.id
			WHERE 
				b.kode='TBS'
		");
		$data['tbs'] = $query->result_array();


		$rayon = $this->db->query("SELECT 
				*
			FROM 
				gbm_organisasi a 
			WHERE 
				a.tipe='AFDELING' and nama not like '%umum%'
		")->result_array();

		$result = [];
		$row = [];
		foreach ($rayon as $key => $val) {

			$val['fk'] = substr($val['kode'], 0, 4);

			if ($val['fk'] == $fk['inti']) {
				$result['inti'][$val['kode']] = $val;
			} else if ($val['fk'] == $fk['plasma']) {
				$result['plasma'][$val['kode']] = $val;
			}
		}
		$data['rayon'] = $result;


		foreach ($result as $key => $val) {
			foreach ($val as $_key => $_val) {
				$val[$_key]['total_tbs'] = $this->PksTimbanganModel->retrieve_where(
					'pks_timbangan a',
					['DATE(a.tanggal)' => $input['tanggal'], 'b.id' => $_val['id'], 'c.kode' => 'TBS',],
					['gbm_organisasi b' => 'a.rayon_id = b.id', 'inv_item c' => 'a.item_id = c.id',],
					'SUM(a.berat_bersih) as berat_bersih'
				)['berat_bersih'];
			}
			$result[$key] = $val;
		}

		$data['rayon'] = $result;






		// $data['rayon'] = $this->db->query("SELECT
		// 		-- SUM(a.berat_bersih) AS berat_bersih
		// 		c.kode
		// 	FROM
		// 		pks_timbangan a
		// 	INNER JOIN inv_item b ON a.item_id = b.id
		// 	INNER JOIN gbm_organisasi c ON a.rayon_id = c.id
		// 	WHERE
		// 		b.nama = 'TBS'
		// 	GROUP BY
		// 		c.kode
		// ")->result_array();

		$inti = $this->db->query("SELECT
				SUM(a.berat_bersih) AS berat_bersih
			FROM
				pks_timbangan a
			INNER JOIN inv_item b ON a.item_id = b.id
			INNER JOIN gbm_organisasi c ON a.rayon_id = c.id
			WHERE
				b.nama = 'TBS'
				&&
				DATE(a.tanggal) >= '" . $input['tgl_mulai'] . "'
				&&
				DATE(a.tanggal) <= '" . $input['tgl_akhir'] . "'
				&&
				c.kode LIKE '" . $fk['inti'] . "%'
		")->row_array()['berat_bersih'];
		$data['inti']['sdhi'] = $inti;

		$inti = $this->db->query("SELECT
				SUM(a.berat_bersih) AS berat_bersih
			FROM
				pks_timbangan a
			INNER JOIN inv_item b ON a.item_id = b.id
			INNER JOIN gbm_organisasi c ON a.rayon_id = c.id
			WHERE
				b.nama = 'TBS'
				&&
				DATE(a.tanggal) >= '" . $input['tgl_mulai_1'] . "'
				&&
				DATE(a.tanggal) <= '" . $input['tgl_akhir'] . "'
				&&
				c.kode LIKE '" . $fk['inti'] . "%'
		")->row_array()['berat_bersih'];
		$data['inti']['sdbi'] = $inti;




		$plasma = $this->db->query("SELECT
				SUM(a.berat_bersih) AS berat_bersih
			FROM
				pks_timbangan a
			INNER JOIN inv_item b ON a.item_id = b.id
			INNER JOIN gbm_organisasi c ON a.rayon_id = c.id
			WHERE
				b.nama = 'TBS'
				&&
				DATE(a.tanggal) >= '" . $input['tgl_mulai'] . "'
				&&
				DATE(a.tanggal) <= '" . $input['tgl_akhir'] . "'
				&&
				c.kode LIKE '" . $fk['plasma'] . "%'
		")->row_array()['berat_bersih'];
		$data['plasma']['sdhi'] = $plasma;
		$plasma = $this->db->query("SELECT
				SUM(a.berat_bersih) AS berat_bersih
			FROM
				pks_timbangan a
			INNER JOIN inv_item b ON a.item_id = b.id
			INNER JOIN gbm_organisasi c ON a.rayon_id = c.id
			WHERE
				b.nama = 'TBS'
				&&
				DATE(a.tanggal) >= '" . $input['tgl_mulai'] . "'
				&&
				DATE(a.tanggal) <= '" . $input['tgl_akhir'] . "'
				&&
				c.kode LIKE '" . $fk['plasma'] . "%'
		")->row_array()['berat_bersih'];
		$data['plasma']['sdbi'] = $plasma;




		// $data['supplier'] = $this->db->query("SELECT
		// 		SUM(a.berat_bersih) AS berat_bersih,
		// 		c.nama_supplier
		// 	FROM pks_timbangan a
		// 	INNER JOIN inv_item b ON a.item_id = b.id 
		// 	INNER JOIN gbm_supplier c ON a.supplier_id = c.id 
		// 	WHERE
		// 		b.nama = 'TBS'
		// 		&&
		// 		DATE(a.tanggal) = '" . $input['tanggal'] . "'
		// 	GROUP BY c.nama_supplier
		// ")->result_array();
		$data['supplier'] = $this->db->query("
		SELECT a.nama_supplier,IFNULL(t.berat_bersih,0)as berat_bersih  from gbm_supplier a
		left join(
			SELECT
			SUM(a.berat_bersih) AS berat_bersih,
					c.id
				FROM gbm_supplier c 
				left JOIN pks_timbangan a
				ON a.supplier_id = c.id 
				left JOIN inv_item b ON a.item_id = b.id 
				WHERE
			b.nama = 'TBS'
			&&
			DATE(a.tanggal) = '" . $input['tanggal'] . "'
		GROUP BY c.id)t
	on a.id=t.id where kelompok_id='5'
	order by a.nama_supplier;
")->result_array(); /*kelompok_id=5 : supplier TBS*/

		$data['supplier_hi'] = $this->db->query("SELECT
				SUM(a.berat_bersih) AS berat_bersih
			FROM pks_timbangan a
			INNER JOIN inv_item b ON a.item_id = b.id 
			INNER JOIN gbm_supplier c ON a.supplier_id = c.id 
			WHERE
				b.nama = 'TBS'
				&&
				DATE(a.tanggal) = '" . $input['tanggal'] . "'
		")->row_array()['berat_bersih'];

		$data['supplier_sdhi'] = $this->db->query("SELECT
				SUM(a.berat_bersih) AS berat_bersih
			FROM pks_timbangan a
			INNER JOIN inv_item b ON a.item_id = b.id 
			INNER JOIN gbm_supplier c ON a.supplier_id = c.id 
			WHERE
				b.nama = 'TBS'
				&&
				DATE(a.tanggal) >= '" . $input['tgl_mulai'] . "'
				&&
				DATE(a.tanggal) <= '" . $input['tgl_akhir'] . "'
		")->row_array()['berat_bersih'];

		$data['supplier_sdbi'] = $this->db->query("SELECT
				SUM(a.berat_bersih) AS berat_bersih
			FROM pks_timbangan a
			INNER JOIN inv_item b ON a.item_id = b.id 
			INNER JOIN gbm_supplier c ON a.supplier_id = c.id 
			WHERE
				b.nama = 'TBS'
				&&
				DATE(a.tanggal) >= '" . $input['tgl_mulai_1'] . "'
				&&
				DATE(a.tanggal) <= '" . $input['tgl_akhir'] . "'
		")->row_array()['berat_bersih'];

		$data['supplier_potongan'] = $this->db->query("SELECT
				SUM(a.berat_potongan) AS berat_potongan
			FROM pks_timbangan a
			INNER JOIN inv_item b ON a.item_id = b.id 
			INNER JOIN gbm_supplier c ON a.supplier_id = c.id 
			WHERE
				b.nama = 'TBS'
				&&
				DATE(a.tanggal) = '" . $input['tanggal'] . "'
		")->row_array()['berat_potongan'];

		$data['tbs_hi'] = $this->db->query("SELECT
				SUM(a.berat_bersih) AS berat_bersih
			FROM pks_timbangan a
			INNER JOIN inv_item b ON a.item_id = b.id  
			WHERE
				b.nama = 'TBS'
				&&
				DATE(a.tanggal) = '" . $input['tanggal'] . "'
		")->row_array()['berat_bersih'];

		$data['tbs_sdhi'] = $this->db->query("SELECT
				SUM(a.berat_bersih) AS berat_bersih
			FROM pks_timbangan a
			INNER JOIN inv_item b ON a.item_id = b.id  
			WHERE
				b.nama = 'TBS'
				&&
				DATE(a.tanggal) >= '" . $input['tgl_mulai'] . "'
				&&
				DATE(a.tanggal) <= '" . $input['tgl_akhir'] . "'
		")->row_array()['berat_bersih'];

		$data['tbs_sdbi'] = $this->db->query("SELECT
				SUM(a.berat_bersih) AS berat_bersih
			FROM pks_timbangan a
			INNER JOIN inv_item b ON a.item_id = b.id  
			WHERE
				b.nama = 'TBS'
				&&
				DATE(a.tanggal) >= '" . $input['tgl_mulai_1'] . "'
				&&
				DATE(a.tanggal) <= '" . $input['tgl_akhir'] . "'
		")->row_array()['berat_bersih'];


		// $data['kebun'] = $kebun;
		// $data['sum_tbs'] = $sum_tbs;
		// $data['sum_tbs_sdhi'] = $sum_tbs_sdhi;
		// $data['sum_tbs_sdbi'] = $sum_tbs_sdbi;







		$data['sum_tbs_hi'] = $this->db->query("SELECT
				SUM(a.berat_bersih) AS berat_bersih
			FROM
				pks_timbangan a
			INNER JOIN inv_item b ON a.item_id = b.id
			INNER JOIN gbm_organisasi c ON a.rayon_id = c.id
			WHERE
				b.nama = 'TBS'
				&&
				DATE(a.tanggal) = '" . $input['tanggal'] . "'
		")->row_array()['berat_bersih'];

		$data['sum_tbs_sdhi'] = $this->db->query("SELECT
				SUM(a.berat_bersih) AS berat_bersih
			FROM
				pks_timbangan a
			INNER JOIN inv_item b ON a.item_id = b.id
			INNER JOIN gbm_organisasi c ON a.rayon_id = c.id
			WHERE
				b.nama = 'TBS'
				&&
				DATE(a.tanggal) >= '" . $input['tgl_mulai'] . "'
				&&
				DATE(a.tanggal) <= '" . $input['tgl_akhir'] . "'
		")->row_array()['berat_bersih'];

		$data['sum_tbs_sdbi'] = $this->db->query("SELECT
				SUM(a.berat_bersih) AS berat_bersih
			FROM
				pks_timbangan a
			INNER JOIN inv_item b ON a.item_id = b.id
			INNER JOIN gbm_organisasi c ON a.rayon_id = c.id
			WHERE
				b.nama = 'TBS'
				&&
				DATE(a.tanggal) >= '" . $input['tgl_mulai_1'] . "'
				&&
				DATE(a.tanggal) <= '" . $input['tgl_akhir'] . "'
		")->row_array()['berat_bersih'];


		$data['input'] = $input;
		$data['tipe_laporan'] = $tipe_laporan;

		$html = $this->load->view('PksTimbangan_rekap_penerimaan_tbs_laporan', $data, true);
		
		if ($tipe_laporan == 'excel') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
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
		}
		else if($tipe_laporan == 'pdf'){
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'portrait');
		} 
		else {
			echo $html;
		}
	}
	function format_number_report($angka)
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
			return number_format($angka, 2);
		}
	}
}
