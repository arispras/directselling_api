<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

class MapPemupukanSbme extends REST_Controller
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

	function retrieve_tahun_bulan_get()
	{
		$res = [];
		$this->set_response($res, REST_Controller::HTTP_OK);
	}
	function show_get()
	{
		$this->load->view('map_pemupukan_sbme', null, false);
	}

	function retrieve_data_get()
	{
		$periode =  $this->get('periode', true);
		if (!$periode) {
			$periode = date('Y-m-d');
		} else {
			if ($periode == "") {
				$periode = date('Y-m-d');
			}
		}

		$blok_results = [];
		$sProd = "select kode_blok,sum(hasil_kerja_kg) as kg,sum(hasil_kerja_jjg) as jjg,
        sum(hasil_kerja_luas) as ha,sum(jumlah_hk) as jmlhk from est_bkm_panen_vw 
		where left(tanggal,7) = left('" . $periode . "',7)  group by kode_blok ";
		$kg = array();
		$jjg = array();

		$res = $this->db->query($sProd)->result_array();
		foreach ($res as $key => $rProd) {
			$kg[$rProd['kode_blok']] = $rProd['kg'];
			$jjg[$rProd['kode_blok']] = $rProd['jjg'];
		}


		$Query = "select b.kode,b.nama,a.* from gbm_blok a inner join gbm_organisasi b on a.organisasi_id=b.id	
		order by b.kode";
		$rows = $this->db->query($Query)->result_array();
		foreach ($rows as $key => $row) {
			if ($row['luasareaproduktif'] == 0 || $row['jumlahpokok'] == 0) {
				$sph = 0;
			} else {
				$sph = $row['jumlahpokok'] / $row['luasareaproduktif'];
			}
			if (isset($kg[$row['kode']])) {
				$jumkg = $kg[$row['kode']];
			} else {
				$jumkg = 0;
			}

			if (isset($jjg[$row['kode']])) {
				$jumjjg = $jjg[$row['kode']];
			} else {
				$jumjjg = 0;
			}
			$blok_results[] = array(
				'kodeblok' => $row['kode'],
				'jumlahpokok' => $row['jumlahpokok'],
				'statusblok' => $row['statusblok'],
				'tahuntanam' => $row['tahuntanam'],
				'sph' =>	number_format($sph, 0),
				'kg' =>$jumkg,// number_format($jumkg, 0),
				'jjg' => number_format($jumjjg, 0),
				'yph' => number_format($jumkg / $row['luasareaproduktif'], 0)

			);
		}

		$this->set_response($blok_results, REST_Controller::HTTP_OK);
	}

	// endpoint/ :POST
	function index_post()
	{
		$input = $this->post();
	}


	function show_detail_get()
	{

		$periode =  $this->get('periode', true);
		if (!$periode) {
			$periode = date('Y-m-d');
		} else {
			if ($periode == "") {
				$periode = date('Y-m-d');
			}
		}


		$blok =  $this->get('blok', true);
		$format_laporan = 'view';

		$Query = "select b.kode,b.nama,a.*,c.bibit from gbm_blok 
		a inner join gbm_organisasi b on a.organisasi_id=b.id
		left join est_bibit c on a.jenisbibit=c.id
		where b.kode= '" . $blok . "'	
		";


		$row = $this->db->query($Query)->row_array();

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
		$areaproduktif = $row['luasareaproduktif'];
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

		$optBulan = array();
		$optBulan['01'] = 'Januari';
		$optBulan['02'] = 'Februari';
		$optBulan['03'] = 'Maret';
		$optBulan['04'] = 'April';
		$optBulan['05'] = 'Mei';
		$optBulan['06'] = 'Juni';
		$optBulan['07'] = 'Juli';
		$optBulan['08'] = 'Agustus';
		$optBulan['09'] = 'September';
		$optBulan['10'] = 'Oktober';
		$optBulan['11'] = 'November';
		$optBulan['12'] = 'Desember';
		$tahun = substr($periode, 0, 4);
		$sProd = "select kode_blok,sum(hasil_kerja_kg) as kg,sum(hasil_kerja_jjg) as jjg,
        sum(hasil_kerja_luas) as ha,sum(jumlah_hk) as jmlhk,substr(tanggal,6,2) as bln from est_bkm_panen_vw 
		 where   kode_blok = '" . $blok . "' and (left(tanggal,7) between '" . $tahun . "-01' and '" . $periode . "') 
		 group by  kode_blok,substr(tanggal,6,2) order by substr(tanggal,6,2)";
		//echo $sProd ;
		$maxBulan = 0;
		@$dtProdKgSi = 0;
		@$dtProdJjgSi = 0;
		@$dtProdJmhkSi = 0;
		@$harealisasi = 0;
		$res = $this->db->query($sProd)->result_array();
		foreach ($res  as $key => $rProd) {
			$html .= "<br><div style='text-align:center; background-color:Steelblue; font-size: 16pt;'><font color=Lightgray><strong>" . $optBulan[$rProd['bln']] . " " . substr($periode, 0, 4) . "</strong></color></div>";

			$html .= "<table class='items' width='100%' style='font-size: 9pt; border-collapse: collapse;' cellpadding='8'>
				<thead>	<tr><td>Produksi(Kg)</td><td>Produksi(jjg)</td><td>Jumlah HK</td> <td>YPH</td>	</tr>	</thead>
				<tbody>
				
				<tr><td>" . number_format($rProd['kg'], 2) . "</td><td>" . number_format($rProd['jjg']) . "</td><td>" . number_format($rProd['jmlhk']) . "</td> <td>" . number_format(($rProd['kg'] / $areaproduktif), 2) . "</td>	</tr>	
				";

			$dtProdKg[$rProd['bln']] = $rProd['kg'];
			$dtProdJjg[$rProd['bln']] = $rProd['jjg'];
			$dtProdJmhk[$rProd['bln']] = $rProd['jmlhk'];
			$dtProdYPH[$rProd['bln']] = $rProd['kg'] / $areaproduktif;
			@$dtProdKgSi += $rProd['kg'];
			@$dtProdJjgSi += $rProd['jjg'];
			@$dtProdJmhkSi += $rProd['jmlhk'];
			@$harealisasi[$rProd['bln']] += $rProd['ha'];

			$maxBulan = $rProd['bln'];
			$html .= "
			<tr>
			<td class='lastrow'></td>
			<td class='lastrow'></td>
			<td class='lastrow'></td>
			<td class='lastrow'></td>
			</tr>
			</tbody>
			</table>";
		}

		$Bi = $optBulan[$maxBulan] . " " . substr($periode, 0, 4);
		$html .= "<br><div style='text-align:center; background-color:Steelblue; font-size: 16pt;'><font color=Lightgray><strong>S/d " . $Bi . "</strong></color></div>";

		$html .= "<table class='items' width='100%' style='font-size: 9pt; border-collapse: collapse;' cellpadding='8'>
				<thead>	<tr><td>Produksi(Kg)</td><td>Produksi(jjg)</td><td>Jumlah HK</td>	</tr>	</thead>
				<tbody>
				
				<tr><td>" . number_format(@$dtProdKgSi) . "</td><td>" . number_format(@$dtProdJjgSi) . "</td><td>" . number_format(@$dtProdJmhkSi) . "</td>	</tr>	
				";
		$html .= "
			<tr>
			<td class='lastrow'></td>
			<td class='lastrow'></td>
			<td class='lastrow'></td>
			</tr>
			</tbody>
			</table>";
		echo $html;
	}
}
