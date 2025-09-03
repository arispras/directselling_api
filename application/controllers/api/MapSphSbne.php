<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

class MapSphSbne extends REST_Controller
{
	public $user_id;
	public $theCredential;

	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		// $this->load->model('EstCurahHujanModel');
		$this->load->model('M_DatatablesModel');
		// $this->auth();
		// $this->theCredential = $this->user_data;
		// $this->user_id=$this->user_data->id;
	}

	// endpoint/list :POST
	public function list_post()
	{
		$post = $this->post();
		$query  = "SELECT a.*,b.nama as lokasi,c.nama as afdeling FROM est_curah_hujan a
					INNER JOIN gbm_organisasi b on a.lokasi_id=b.id
					INNER JOIN gbm_organisasi c on a.afdeling_id=c.id 
		";

		$search = array('b.nama', 'c.nama', 'a.pagi', 'a.sore', 'a.malam', 'a.tanggal');
		$where  = null;
		$isWhere = null;
		// $isWhere = 'artikel.deleted_at IS NULL';
		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}

	// endpoint/ :GET
	function show_get()
	{
		$this->load->view('map_sph_sbne', null, false);
	}

	function retrieve_data_get()
	{
		$results = [];

		$Query = "select b.kode,b.nama,a.* from gbm_blok a inner join gbm_organisasi b on a.organisasi_id=b.id	
		order by b.kode";


		$rows = $this->db->query($Query)->result_array();
		foreach ($rows as $key => $row) {
			if ($row['luasareaproduktif'] == 0 || $row['jumlahpokok'] == 0) {
				$sph = 0;
			} else {
				$sph = $row['jumlahpokok'] / $row['luasareaproduktif'];
			}
			$results[] = array(
	
				'kodeblok' => $row['kode'],
				'jumlahpokok' => $row['jumlahpokok'],
				'statusblok' => $row['statusblok'],
				'tahuntanam' => $row['tahuntanam'],
				'sph' =>	number_format($sph, 0)
	
			);
		}
		
		$this->set_response($results, REST_Controller::HTTP_OK);
	}

	// endpoint/ :POST
	function index_post()
	{
		$input = $this->post();
	}


	function show_detail_get()
	{



		$blok =  $this->get('blok', true);
		$periode =  $this->get('periode', true);
		$format_laporan = 'view';

		$Query = "select b.kode,b.nama,a.*,c.bibit from gbm_blok 
		a inner join gbm_organisasi b on a.organisasi_id=b.id
		left join est_bibit c on a.jenisbibit=c.id
		where b.kode= '" . $blok . "'	
		";


		$row = $this->db->query($Query)->row_array();

		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');

		// if ($format_laporan == 'pdf') {
		// 	$html = get_header_pdf_report();
		// } else {
		// 	$html = get_header_report_v2();
		// }
		$html = "

		<html>
		<head>
		<style>
		body {font-family: sans-serif;
		    font-size: 10pt;
		}

		.garis{ 
			border-left: none;
			border-right: none;
		}

		td { vertical-align: top; 
			border-left: 0.6mm solid #000000;
		    border-right: 0.6mm solid #000000;
			align: center;
		}

		table thead td { background-color: #EEEEEE;
		    text-align: center;
		    border: 0.6mm solid #000000;
		}

		td.lastrow {
		    // background-color: #000000;
		    border: 0mm none #000000;
		    border-bottom: 0.6mm solid #000000;
		    border-left: 0.6mm solid #000000;
			border-right: 0.6mm solid #000000;
		}

		td.lastrow2 {
		    // background-color: #000000;
		    border: 0mm none #FFFFFF;
		    border-bottom: 0.6mm solid #000000;
		    
		}

		.hide-text {
		    overflow: hidden;
		    text-indent: 100%;
		    white-space: nowrap;
		}

		 
		</style>
		</head>
		<body>
		 
		<!--mpdf
		<htmlpagefooter name='myfooter'>
		<div style='border-top: 1px solid #000000; font-size: 9pt; text-align: center; padding-top: 3mm; '>
		Page {PAGENO} of {nb}
		</div>
		</htmlpagefooter>
		 
		<sethtmlpageheader name='myheader' value='on' show-this-page='1' />
		<sethtmlpagefooter name='myfooter' value='on' />
		mpdf-->
		 
		<div style='text-align:center; background-color:Steelblue; font-size: 16pt;'><font color=Lightgray><strong>" . $_GET['blok'] . "</strong></color></div>

		<table class='items' width='100%' style='font-size: 9pt; border-collapse: collapse;' cellpadding='8'>

		<tbody>
		</tbody>
		</table>

		<table class='items' width='100%' style='font-size: 9pt; border-collapse: collapse;' cellpadding='8'>
		<thead>
		<tr>
		</tr>
		</thead>
		<tbody>";
		$intiplasma=($row['intiplasma']=='I')?'INTI':'PLASMA';
		$html .= "
				<tr>
					<td>Blok</td><td>:</td><td>" . $row['kode'] . "</td>
				</tr>
				<tr>
					<td>status</td><td>:</td><td>" . $row['statusblok'] . "</td>
				</tr>
				<tr>
					<td>Jumlah Pokok</td><td>:</td><td>" . $row['jumlahpokok'] . "</td>
				</tr>
				<tr>
					<td>Tahun Tanam</td><td>:</td><td>" . $row['tahuntanam'] . "</td>
				</tr>
				<tr>
					<td>Luas Produktif</td><td>:</td><td>" . $row['luasareaproduktif'] . "</td>
				</tr>
				<tr>
					<td>Inti/Plasma</td><td>:</td><td>" . $intiplasma . "</td>
				</tr>
				<tr>
					<td>Topografi</td><td>:</td><td>" . $row['topografi'] . "</td>
				</tr>
				<tr>
					<td>Bibit</td><td>:</td><td>" . $row['bibit'] . "</td>
				</tr>
				<tr>
					<td>SPH</td><td>:</td><td>" . number_format($row['jumlahpokok'] / $row['luasareaproduktif']) . "</td>
				</tr>
				";
		$html .= "
		<tr>
		<td class='lastrow'></td>
		<td class='lastrow'></td>
		<td class='lastrow'></td>
		</tr>
		</tbody>
		</table>";

		$html .= "</body>
		</html>
		</table>
		";

		
		echo $html;
	}
}
