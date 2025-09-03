<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

class EstSpat extends BD_Controller
{
	public $user_id;
	public $theCredential;

	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('GbmOrganisasiModel');
		$this->load->model('EstSpatModel');
		$this->load->model('M_DatatablesModel');
		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id = $this->user_data->id;
	}
	public function list_post()
	{
		$post = $this->post();
		$param = $post['parameter'];

		$query  = "SELECT a.*,b.nama as nama_rayon, d.nama as divisi, c.no_tiket,c.berat_bersih FROM est_spat_ht a 
		inner join  gbm_organisasi b on a.rayon_id=b.id
		inner join  gbm_organisasi d on a.divisi_id=d.id
		left join pks_timbangan c on a.pks_timbangan_id=c.id";
		$search = array('a.no_spat', 'a.tanggal', 'b.nama', 'c.no_tiket','d.nama');
		$where  = null;

		$isWhere = null;

		$isWhere = " 1=1";
		if ($param['tgl_mulai'] && $param['tgl_mulai']) {
			$isWhere = " a.tanggal between '" . $param['tgl_mulai'] . "' and '" . $param['tgl_akhir'] . "'";
		}
		// if ($param['lokasi_id']){
		// 	$isWhere =$isWhere. " and a.mill_id =".$param['lokasi_id']." ";
		// }
		if ($param['divisi_id']) {
			$isWhere = $isWhere . " and a.divisi_id =" . $param['divisi_id'] . " ";
		}
		if ($param['status_id']) {
			if ($param['status_id'] == 'Y') {
				$isWhere = $isWhere . " and c.id is not NUll  ";
			} else if ($param['status_id'] == 'N') {
				$isWhere = $isWhere . " and c.id is NUll  ";
			}
		}

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}

	function index_get($id = '')
	{
		$retrieve = $this->EstSpatModel->retrieve_by_id($id);
		$retrieve['detail'] = $this->EstSpatModel->retrieve_detail($id);


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
		//$input['no_spat'] = $this->getLastNumber('est_spat_ht', 'no_spat', 'SPAT');
		// var_dump($input);
		$res = $this->EstSpatModel->create($input);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'est_spat', 'action' => 'new', 'entity_id' => $res);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function index_put($segment_3 = null)
	{
		$id = (int)$segment_3;
		$retrieve = $this->EstSpatModel->retrieve_by_id($id);
		if ($retrieve) {
			if ($retrieve['pks_timbangan_id'] && $retrieve['pks_timbangan_id'] != 0) {
				$this->set_response(array("status" => "NOT OK", "data" => "Sudah divalidasi Timbangan PKS"), REST_Controller::HTTP_OK);
				return;
			}
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$data = $this->put();
		$data['diubah_oleh'] = $this->user_id;
		$res = $this->EstSpatModel->update($id, $data);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'est_spat', 'action' => 'edit', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function validasiTimbangan_put($segment_3 = null)
	{
		$id = (int)$segment_3;
		$data = $this->put();
		$retrieve = $this->EstSpatModel->retrieve_by_id($id);
		$data['total_jjg'] = $retrieve['total_jjg'];
		$res = $this->EstSpatModel->validasiTimbangan($id, $data);
		if (!empty($res)) {
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	public function index_delete($id)
	{
		$retrieve = $this->EstSpatModel->retrieve_by_id($id);
		if ($retrieve) {
			if ($retrieve['pks_timbangan_id'] && $retrieve['pks_timbangan_id'] != 0) {
				$this->set_response(array("status" => "NOT OK", "data" => "Sudah divalidasi Timbangan PKS"), REST_Controller::HTTP_OK);
				return;
			}
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
			return;
		}
		$res = $this->EstSpatModel->delete($id);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id' => $id)), 'entity' => 'est_spat', 'action' => 'delete', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	public function clearValidasiTimbangan_post($id)
	{

		$data['diubah_oleh'] = $this->user_id;
		$res = $this->EstSpatModel->clearValidasiTimbangan($id, $data);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id' => $id)), 'entity' => 'est_spat', 'action' => 'delete', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function laporan_spat_detail_post()
	{

		error_reporting(0);
		$estate_id     = $this->post('estate_id', true);
		$tgl_mulai =  $this->post('tgl_mulai', true);
		$tgl_akhir = $this->post('tgl_akhir', true);
		$format_laporan = $this->post('format_laporan', true);
		$retrieveEstate = $this->db->query("select * from gbm_organisasi where id=" . $estate_id . "")->row_array();
		$retrieveAfdeling = $this->db->query("SELECT a.* from gbm_organisasi a 
		inner join gbm_organisasi b	 on a.parent_id=b.id
		 inner join gbm_organisasi c on b.parent_id=c.id
		  where c.id=" . $estate_id . "
		  and a.tipe='AFDELING'
		  order by a.nama")->result_array();

		// var_dump($results);
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
	<h3 class="title">LAPORAN SURAT PENGANTAR ANGKUT TBS</h3>
	<table class="no_border" style="width:30%">
			  
			  <tr>
					  <td>Estate</td>
					  <td>:</td>
					  <td>' .  $retrieveEstate['nama'] . '</td>
			  </tr>
			  <tr>	
					  <td>Periode</td>
					  <td>:</td>
					  <td>' . tgl_indo($tgl_mulai) . ' s/d ' . tgl_indo($tgl_akhir) . '</td>
			  </tr>
			  
	  </table>
			  <br>';

		$html = $html . ' <table  >
			<thead>
				<tr>
				<th width="4%">No.</th>			
				<th>Tanggal</th>
				<th>Afdeling</th>
				<th>Blok</th>
				<th>No Tiket Pabrik</th>
				<th style="text-align: center;">Janjang </th>
				<th style="text-align: center;">Brondolan(Kg) </th>
				<th style="text-align: center;">BJR Kebun</th>
				<th style="text-align: center;">Kg Kebun </th>
				<th style="text-align: center;">BJR Pabrik</th>
				<th style="text-align: center;">Kg Pabrik</th>
				<th style="text-align: center;">% </th>
				</tr>
			</thead>
			<tbody>';
		$no = 0;
		$total_janjang = 0;
		$total_brondolan = 0;
		$total_kg_kebun = 0;
		$total_kg_pabrik = 0;
		$avg_persen = 0;

		$total = 0;
		foreach ($retrieveAfdeling as $key => $afd) {


			$sql = "	SELECT a.*,b.*,c.kode AS kode_blok,c.nama AS nama_blok,d.nama 	AS nama_afdeling, 
					d.kode AS kode_afdeling,d.id as afdeling_id,f.nama AS nama_estate,f.id AS estate_id,
					g.no_tiket ,g.no_plat,g.nama_supir
					 FROM est_spat_ht a 
					INNER JOIN est_spat_dt b ON a.id=b.spat_id 
					Inner JOIN gbm_organisasi c ON b.blok_id=c.id
					INNER JOIN gbm_organisasi d ON c.parent_id=d.id
					INNER JOIN gbm_organisasi e ON d.parent_id=e.id
					INNER JOIN gbm_organisasi f ON e.parent_id=f.id
					LEFT JOIN pks_timbangan g ON a.pks_timbangan_id=g.id
					where f.id=" . $estate_id . " 
					and a.tanggal>='" . $tgl_mulai . "' and a.tanggal<='" . $tgl_akhir . "'
					and d.id=" . $afd['id'] . "
					order by a.tanggal";
			$retrievePanen = $this->db->query($sql)->result_array();

			$no = 0;
			$sub_total_janjang = 0;
			$sub_total_brondolan = 0;
			$sub_total_kg_kebun = 0;
			$sub_total_kg_pabrik = 0;
			$avg_persen_afd = 0;

			foreach ($retrievePanen as $key => $m) {
				$no++;
				$total_janjang = $total_janjang + $m['jum_janjang'];
				$total_brondolan = $total_brondolan + $m['jum_brondolan'];
				$total_kg_kebun = $total_kg_kebun + $m['kg_kebun'];
				$total_kg_pabrik = $total_kg_pabrik + $m['kg_pabrik'];
				$sub_total_janjang = $sub_total_janjang + $m['jum_janjang'];
				$sub_total_brondolan = $sub_total_brondolan + $m['jum_brondolan'];
				$sub_total_kg_kebun = $sub_total_kg_kebun + $m['kg_kebun'];
				$sub_total_kg_pabrik = $sub_total_kg_pabrik + $m['kg_pabrik'];
				$persen = ($m['kg_pabrik'] / $m['kg_kebun']) * 100;
				if ($avg_persen_afd == 0) {
					$avg_persen_afd = $persen;
				} else {
					$avg_persen_afd = ($avg_persen_afd +	$persen) / 2;
				}
				if ($avg_persen == 0) {
					$avg_persen = $persen;
				} else {
					$avg_persen = ($avg_persen +	$persen) / 2;
				}

				$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative; text-align: center;">	' . ($no) . '</td>
						<td>' . tgl_indo_normal($m['tanggal']) . ' </td>
						<td>
						' . $m['nama_afdeling'] . ' 
						
						</td>
						<td>
						' . $m['nama_blok'] . ' 
						
						</td>
						
						<td>
							' . $m['no_tiket'] . ' 
						</td>
						
						<td style="text-align: right;">' . number_format($m['jum_janjang']) . ' 
						<td style="text-align: right;">' . number_format($m['jum_brondolan']) . ' 
						<td style="text-align: right;">' . number_format($m['bjr_kebun']) . ' 
						<td style="text-align: right;">' . number_format($m['kg_kebun']) . ' 
						<td style="text-align: right;">' . number_format($m['bjr_pabrik']) . '
						<td style="text-align: right;">' . number_format($m['kg_pabrik']) . '  
						<td style="text-align: right;">' . number_format($persen) . ' 									
						</td></tr>';
			}
			// SUB TOTAL //
			if ($sub_total_janjang > 0) {
				$html = $html . ' 	
					<tr class=":: arc-content">
					<td style="position:relative;" colspan="5">
					<b> JUMLAH	&nbsp;' . $afd['nama'] . ' </b>

					</td>
					<td style="text-align: right;">
					' . number_format($sub_total_janjang) . ' 
					</td>
					<td style="text-align: right;">
					' . number_format($sub_total_brondolan) . ' 
					</td>
					<td style="text-align: right;">
					
					</td>
					<td style="text-align: right;">
					' . number_format($sub_total_kg_kebun) . ' 
					</td>
					<td style="text-align: right;">
					
					</td>
					<td style="text-align: right;">
					' . number_format($sub_total_kg_pabrik) . ' 
					</td>
					<td style="text-align: right;">
					' . number_format($avg_persen_afd) . ' 
					</td>
											
					</tr>
					';
			}
		}
		$html = $html . ' 	
						<tr class=":: arc-content">
						<td style="position:relative;" colspan="5">
						<b> TOTAL	</b>
						</td>
						<td style="text-align: right;">
						' . number_format($total_janjang) . ' 
						</td>
						<td style="text-align: right;">
						' . number_format($total_brondolan) . ' 
						</td>
						<td style="text-align: right;">
						
						</td>
						<td style="text-align: right;">
						' . number_format($total_kg_kebun) . ' 
						</td>
						<td style="text-align: right;">
						
						</td>
						<td style="text-align: right;">
						' . number_format($total_kg_pabrik) . ' 
						</td>
						<td style="text-align: right;">
						' . number_format($avg_persen) . ' 
						</td>
												
						</tr>
								</tbody>
							</table>
						';
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
	function print_slip_get($segment_3 = '')
	{

		$id = (int)$segment_3;
		$data = [];

		$queryHeader = "SELECT a.*,
		b.nama as afdeling,
		d.nama as lokasi, c.no_tiket,
		c.berat_bersih FROM est_spat_ht a 
		inner join  gbm_organisasi b on a.rayon_id=b.id
		inner join  gbm_organisasi d on a.divisi_id=d.id
		left join pks_timbangan c on a.pks_timbangan_id=c.id   
		WHERE a.id=" . $id . "";
		$dataHeader = $this->db->query($queryHeader)->row_array();

		$queryDetail = "SELECT a.*, b.nama as blok, b.kode as kode
		FROM est_spat_dt a
		INNER JOIN gbm_organisasi b ON a.blok_id=b.id 
		WHERE a.spat_id=" . $id . "";
		$dataDetail = $this->db->query($queryDetail)->result_array();

		$data['header'] = 	$dataHeader;
		$data['detail'] = 	$dataDetail;

		// $user = $this->user_id;
		// if ($user) {
		// 	$retrieveProduk = $this->db->query("select * from fwk_users where id=" . $user . "")->row_array();
		// }
		// $data['dibuka']  = $retrieveProduk;

		$html = $this->load->view('EstSlipSpat', $data, true);
		echo $html;
	}

	function print_slip_validasi_get($id = '')
	{

		// $queryHeader = "SELECT a.*,
		// b.nama as lokasi,
		// c.nama AS afdeling FROM est_sensus_panen_ht a 
		// inner join  gbm_organisasi b on a.lokasi_id=b.id
		// inner join  gbm_organisasi c on a.afdeling_id=c.id   
		// WHERE a.id=" . $id . "";
		// $dataHeader = $this->db->query($queryHeader)->row_array();

		// $queryDetail = "SELECT a.*, b.nama as blok, b.kode as kode
		// FROM est_sensus_panen_dt a
		// INNER JOIN gbm_organisasi b ON a.blok_id=b.id 
		// WHERE a.sensus_panen_id=" . $id . "";
		// $dataDetail = $this->db->query($queryDetail)->result_array();
		$hd = $this->EstSpatModel->retrieve_by_id($id);
		$dt = $this->EstSpatModel->retrieve_detail($id);

		$data['header'] = 	$hd;
		$data['detail'] = 	$dt;


		$html = $this->load->view('EstSlipSpatValidasiTimbangan', $data, true);

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		echo $html;
	}
}
