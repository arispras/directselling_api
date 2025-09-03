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

class GlobalReport extends Rest_Controller
{
	public $user_id;
	public $theCredential;
	public $data = array();
	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('GbmOrganisasiModel');
		$this->load->model('AccJurnalModel');
		$this->load->model('InvItemModel');

		$this->load->model('M_DatatablesModel');
		$this->load->library('pdfgenerator');
		// $this->auth();
		// $this->theCredential = $this->user_data;
		// $this->user_id = $this->user_data->id;
	}


	function acc_laporan_buku_besar_get($tanggal_mulai, $tanggal_akhir, $akun_id, $lokasi_id = null)
	{
		$format_laporan = 'view';
		// $format_laporan =  $this->post('format_laporan', true);
		// $lokasi_id = $this->post('lokasi_id', true);
		// $akun_id = $this->post('akun_id', true);
		// $tanggal_mulai =  $this->post('tgl_mulai', true);
		// $tanggal_akhir = $this->post('tgl_akhir', true);
		$carilokasi = "";
		if ($lokasi_id) {
			$carilokasi = " and  b.lokasi_id=" . $lokasi_id . "";
		}
		$querySaldoAwal = " SELECT b.lokasi_id,b.acc_akun_id,sum(debet-kredit)as saldo FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
		on a.id=b.jurnal_id
        where b.acc_akun_id=" . $akun_id . "  " . $carilokasi . " and a.tanggal < '" . $tanggal_mulai . "'
         group by b.lokasi_id,b.acc_akun_id ;";


		$saldoAwal    = $this->db->query($querySaldoAwal)->row_array();
		$data['saldo_awal'] = (!empty($saldoAwal)) ? $saldoAwal['saldo'] : 0;
		//    print_r($querySaldoAwal);exit();

		$queryTransaksi   = "SELECT 
		a.tanggal,a.no_jurnal,b.no_referensi, 
		b.lokasi_id,b.acc_akun_id,b.debet,b.kredit,b.ket ,
		c.kode as kode_blok,c.nama as nama_blok,d.kode as kode_afdeling,d.nama as nama_afdeling,
		e.kode as kode_kendaraan,e.nama as nama_kendaraan,f.kode as kode_traksi,f.nama as nama_traksi,
		b.umur_tanam_blok,g.tahuntanam
		 FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
		on a.id=b.jurnal_id
		left join gbm_organisasi c on b.blok_stasiun_id =c.id
		left join gbm_organisasi d on c.parent_id =d.id
		left join trk_kendaraan e on b.kendaraan_mesin_id =e.id
		left join gbm_organisasi f on f.id=e.traksi_id
		left join gbm_blok g on c.id=g.organisasi_id
         where  b.acc_akun_id=" . $akun_id . "   " . $carilokasi . "
          and a.tanggal >= '" . $tanggal_mulai . "'   and a.tanggal <= '" . $tanggal_akhir . "'
          order by a.tanggal  ;
          ";


		$results  = $this->db->query($queryTransaksi)->result_array();
		$data['transaksi'] = $results;

		$akun = $this->db->query("select * from acc_akun where id=" . $akun_id . "")->row_array();
		if ($lokasi_id) {
			$lokasi = $this->db->query("select * from gbm_organisasi where id=" . $lokasi_id . ";")->row_array();
			$nama_lokasi = $lokasi['nama'];
		} else {
			$nama_lokasi = 'semua';
		}
		//$this->set_response(array("status" => "OK", "data" => $data), REST_Controller::HTTP_OK);
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'view') {
			$html = get_header_report_v2();
		} else {
			$html = get_header_pdf_report();
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
  <h3 class="title">LAPORAN BUKU BESAR</h3>
  <table class="no_border" style="width:30%">
			
			<tr>
					<td>Lokasi</td>
					<td>:</td>
					<td>' . $nama_lokasi . '</td>
			</tr>
			<tr>	
					<td>Periode</td>
					<td>:</td>
					<td>' . $tanggal_mulai . ' s/d ' . $tanggal_akhir . '</td>
			</tr>
			<tr>	
					<td>Periode</td>
					<td>:</td>
					<td>' . $akun['kode'] . ' - ' . $akun['nama'] . '</td>
			</tr>

			
	</table>
			<br>';

		$html = $html . ' <table  border="1" width="100%" style="border-collapse: collapse;" >
  <thead>
  <tr>
		  <th width="4%" rowspan="2">No.</th>
		  <th rowspan="2">Keterangan</th>
		  <th rowspan="2">No Jurnal</th>
		  <th rowspan="2">No ref</th>
		  <th rowspan="2">Tgl</th>
		  <th rowspan="2">Blok</th>
		  <th rowspan="2">Afd</th>
		  <th rowspan="2">Tahun Tanam</th>
		  <th rowspan="2">Kendaraan</th>
		  <th colspan="2" style="text-align: center;">Transaksi</th>
		  <th colspan="2" style="text-align: center;">Saldo </th>
		 
	  </tr>
	  <tr>

		  <th style="text-align: center;">Dr</th>
		  <th style="text-align: center;">Cr</th>
		  <th style="text-align: center;">Dr</th>
		  <th style="text-align: center;">Cr</th>
		  
		 

	  </tr>

  </thead>
  <tbody>		

   <tr class=":: arc-content">
		  <td style="position:relative;">
		  </td>
		  <td>
			  Saldo awal
		  </td>
		  <td>
		  </td>
		  <td>
		  </td>
		  <td>

		  </td>
		  <td>

		  </td>
		  <td>
		  </td>
		  <td>
		  </td>
		  <td>
		  </td>
		  <td>
		  </td>
		  <td>
		  </td>
		  <td style="text-align: right;">

			  ' . number_format(($data['saldo_awal'] > 0) ? $data['saldo_awal'] : 0) . '

		  </td>
		  <td style="text-align: right;">

		  ' . number_format(($data['saldo_awal'] < 0) ? $data['saldo_awal'] * -1 : 0) . '

	  </td>

	  </tr>';


		$total_saldo = $data['saldo_awal'];
		$tdebet = 0;
		$tkredit = 0;
		$no = 0;
		foreach ($data['transaksi'] as $key => $m) {

			$actual_link = "http://$_SERVER[HTTP_HOST]" . "/plantation-api/api/GlobalReport/acc_laporan_jurnal?no_jurnal=" . $m['no_jurnal'] . "";

			$tdebet = $tdebet + $m['debet'];

			$tkredit = $tkredit + $m['kredit'];
			$total_saldo = $total_saldo + $m['debet'] - $m['kredit'];

			$no++;
			$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative;">
							' . ($no) . '

						</td>
						<td>
						' . $m['ket'] . ' 
						
						</td>
						<td><a href="' . $actual_link  . '" target="_blank"> ' . $m['no_jurnal'] . ' </a>
						<td>
						' . $m['no_referensi'] . ' 
						
						</td>
						<td>
							' . $m['tanggal'] . ' 
						</td>
						<td>
							' . ($m['kode_blok']) . ' - ' . ($m['nama_blok']) . ' 
						</td>
						<td>
						' . ($m['nama_afdeling']) . ' 
						</td>
						<td>
						' . ($m['tahuntanam']) . ' - ' . ($m['umur_tanam_blok']) . ' 
						</td>
						<td>
							' . $m['kode_kendaraan'] . ' - ' . $m['nama_kendaraan'] . ' 
						</td>';
			if ($m['debet'] > 0) {

				$html = $html . '<td style="text-align: right;">' . number_format($m['debet']) . '</td>';
			} else {
				$html = $html . '<td style="text-align: right;">-</td>';
			}
			if ($m['kredit'] > 0) {

				$html = $html . '<td style="text-align: right;">' . number_format($m['kredit']) . '</td>';
			} else {
				$html = $html . '<td style="text-align: right;">-</td>';
			}


			$html = $html . '<td style="text-align: right;">' . number_format($total_saldo > 0 ? $total_saldo : 0) . '</td>';
			$html = $html . '<td style="text-align: right;">' . number_format($total_saldo < 0 ? $total_saldo * -1 : 0) . '</td>';

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
				<td style="text-align: right;">
				   ' . number_format($tdebet) . '

				</td>
				<td style="text-align: right;">
					' . number_format($tkredit) . '

				</td>
				<td>
					&nbsp;
				</td>
				<td>
					&nbsp;
				</td>
			</tr>
			</tbody>
			</table>
					';
		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
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
	function acc_laporan_jurnal_get()
	{
		$format_laporan =  'view';
		$no_jurnal = $this->get('no_jurnal');
		$queryTransaksi   = "SELECT 
		a.tanggal,a.no_jurnal,a.no_ref, b.lokasi_id,b.acc_akun_id,b.debet,b.kredit,
		b.ket ,c.kode as kode_akun,c.nama as nama_akun,
		d.kode as kode_blok,d.nama as nama_blok,e.kode as kode_afdeling,e.nama as nama_afdeling,
		f.kode as kode_kendaraan,f.nama as nama_kendaraan,g.kode as kode_traksi,g.nama as nama_traksi,
		b.umur_tanam_blok,h.tahuntanam,i.kode as kode_kegiatan,i.nama as nama_kegiatan
		FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
		on a.id=b.jurnal_id inner join acc_akun c on b.acc_akun_id=c.id
		left join gbm_organisasi d on b.blok_stasiun_id =d.id
		left join gbm_organisasi e on d.parent_id =e.id
		left join trk_kendaraan f on b.kendaraan_mesin_id =f.id
		left join gbm_organisasi g on g.id=f.traksi_id
		left join gbm_blok h on d.id=h.organisasi_id
		left join acc_kegiatan i on b.kegiatan_id=i.id
         where   a.no_jurnal = '" . $no_jurnal . "'
        ";


		$queryTransaksi = 	$queryTransaksi . " order by a.tanggal,a.no_jurnal,b.id";

		$results  = $this->db->query($queryTransaksi)->result_array();
		$data['transaksi'] = $results;


		//$this->set_response(array("status" => "OK", "data" => $data), REST_Controller::HTTP_OK);
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'view') {
			$html = get_header_report();
		} else {
			$html = get_header_pdf_report();
		}
		$html = $html . '
  <h2>Jurnal</h2>';

		$html = $html . ' <table  border="1" width="100%" style="border-collapse: collapse;" >
			<thead>
			<tr>
                    <th width="4%">No.</th>
                    <th >Akun</th>
					<th >Kegiatan</th>
					<th >Keterangan</th>
                    <th >No Jurnal</th>
					<th >No ref</th>
                    <th >Tgl</th>
					<th rowspan="2">Blok</th>
					<th rowspan="2">AFd</th>
					<th width="8%" rowspan="2">Tahun Tanam </th>
					<th rowspan="2">Kendaraan</th>
                    <th  style="text-align: right;">Dr</th>
					<th  style="text-align: right;">Cr</th>                
                </tr>				
			</thead>
			<tbody>	';
		$tdebet = 0;
		$tkredit = 0;
		$no = 0;
		foreach ($data['transaksi'] as $key => $m) {
			$tdebet = $tdebet + $m['debet'];
			$tkredit = $tkredit + $m['kredit'];
			$no++;
			$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative;">
							' . ($no) . '

						</td>
						<td>
						' . $m['kode_akun'] . ' - ' . $m['nama_akun'] . '
						
						</td>
						<td>
						' . $m['kode_kegiatan'] . ' - ' . $m['nama_kegiatan'] . '
						
						</td>
						<td>
						' . $m['ket'] . ' 
						
						</td>
						<td>
						' . $m['no_jurnal'] . ' 
						
						</td>
						<td>
						' . $m['no_ref'] . ' 
						
						</td>
						<td>
							' . $m['tanggal'] . ' 
						</td>
						<td>
							' . ($m['kode_blok']) . ' - ' . ($m['nama_blok']) . ' 
						</td>
						<td>
						' . ($m['nama_afdeling']) . ' 
						</td>
						<td>
						' . ($m['tahuntanam']) . ' - ' . ($m['umur_tanam_blok']) . ' 
						</td>
						<td>
							' . $m['kode_kendaraan'] . ' - ' . $m['nama_kendaraan'] . ' 
						</td>';

			if ($m['debet'] > 0) {

				$html = $html . '<td style="text-align: right;">' . number_format($m['debet']) . '</td>';
			} else {
				$html = $html . '<td style="text-align: right;">-</td>';
			}
			if ($m['kredit'] > 0) {

				$html = $html . '<td style="text-align: right;">' . number_format($m['kredit']) . '</td>';
			} else {
				$html = $html . '<td style="text-align: right;">-</td>';
			}

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
                       ' . number_format($tdebet) . '

                    </td>
                    <td style="text-align: right;">
                        ' . number_format($tkredit) . '

                    </td>
                    
                </tr>
				</tbody>
				</table>
						';
		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
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

	function acc_kasbank_laporan_saldo_rinci_get($tanggal_mulai, $tanggal_akhir, $akun_id, $lokasi_id = null)
	{
		$format_laporan = 'view';
		// $format_laporan =  $this->post('format_laporan', true);
		// $lokasi_id = $this->post('lokasi_id', true);
		// $akun_id = $this->post('akun_id', true);
		// $tanggal_mulai =  $this->post('tgl_mulai', true);
		// $tanggal_akhir = $this->post('tgl_akhir', true);
		$carilokasi = "";
		if ($lokasi_id) {
			$carilokasi = " and  b.lokasi_id=" . $lokasi_id . "";
		}
		$querySaldoAwal = " SELECT b.lokasi_id,b.acc_akun_id,sum(debet-kredit)as saldo FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
		on a.id=b.jurnal_id
        where b.acc_akun_id=" . $akun_id . " " . $carilokasi . "  and a.tanggal < '" . $tanggal_mulai . "'
         group by b.lokasi_id,b.acc_akun_id ;";


		$saldoAwal    = $this->db->query($querySaldoAwal)->row_array();
		$data['saldo_awal'] = (!empty($saldoAwal)) ? $saldoAwal['saldo'] : 0;
		//    print_r($querySaldoAwal);exit();

		$queryTransaksi   = "SELECT a.tanggal,a.no_jurnal,b.no_referensi, b.lokasi_id,b.acc_akun_id,b.debet,b.kredit,b.ket  FROM acc_jurnal_ht a inner join acc_jurnal_dt b 
		on a.id=b.jurnal_id
         where  b.acc_akun_id=" . $akun_id . " " . $carilokasi . "
          and a.tanggal >= '" . $tanggal_mulai . "'   and a.tanggal <= '" . $tanggal_akhir . "'
          order by a.tanggal  ;
          ";


		$results  = $this->db->query($queryTransaksi)->result_array();
		$data['transaksi'] = $results;

		$akun = $this->db->query("select * from acc_akun where id=" . $akun_id . "")->row_array();
		if ($lokasi_id) {
			$lokasi = $this->db->query("select * from gbm_organisasi where id=" . $lokasi_id . ";")->row_array();
			$nama_lokasi = $lokasi['nama'];
		} else {
			$nama_lokasi = 'semua';
		}
		//$this->set_response(array("status" => "OK", "data" => $data), REST_Controller::HTTP_OK);
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		$html ="";
		// if ($format_laporan == 'pdf') {
		// 	$html = get_header_pdf_report();
		// } else {
		// 	$html = get_header_report_v2();
		// }

		$html = $html . '<div class="row">
  <div class="span12">
	  <br>
	  <div class="kop-print">
		 </div>
	  <hr class="kop-print-hr">
  </div>
  </div>
  <h3 class="title">LAPORAN SALDO KAS BANK - DETAIL</h3>
  <table class="no_border" style="width:30%">
			
			<tr>
					<td>Lokasi</td>
					<td>:</td>
					<td>' . $nama_lokasi . '</td>
			</tr>
			<tr>	
					<td>Periode</td>
					<td>:</td>
					<td>' . $tanggal_mulai . ' s/d ' . $tanggal_akhir . '</td>
			</tr>
			<tr>	
					<td>Akun</td>
					<td>:</td>
					<td>' . $akun['kode'] . ' - ' . $akun['nama'] . '</td>
			</tr>
			
	</table>
	<br>
  ';

		$html = $html . '<table  border="1" width="100%" style="border-collapse: collapse;" >
			
			<tr>
                    <td width="4%" rowspan="2">No.</td>
                    <td rowspan="2">Keterangan</td>
                    <td rowspan="2">No Jurnal</td>
					<td rowspan="2">No ref</td>
                    <td rowspan="2">Tgl</td>
                    <td colspan="2" style="text-align: center;">Transaksi</td>
                    <td colspan="2" style="text-align: center;">Saldo </td>
                   
                </tr>
				<tr>

                    <td style="text-align: center;">Dr</td>
                    <td style="text-align: center;">Cr</td>
					<td style="text-align: center;">Dr</td>
                    <td style="text-align: center;">Cr</td>
					
                   

                </tr>
		
	
			<tbody>		

			 <tr class=":: arc-content">
                    <td style="position:relative;">
                    </td>
                    <td>
                        Saldo awal
                    </td>
                    <td>
                    </td>
					<td>
                    </td>
                    <td>

                    </td>
                    <td>

                    </td>
                    <td>

                    </td>

                    <td style="text-align: right;">

                        ' . number_format(($data['saldo_awal'] > 0) ? $data['saldo_awal'] : 0) . '

                    </td>
					<td style="text-align: right;">

					' . number_format(($data['saldo_awal'] < 0) ? $data['saldo_awal'] * -1 : 0) . '

				</td>

                </tr>';


		$total_saldo = $data['saldo_awal'];
		$tdebet = 0;
		$tkredit = 0;
		$no = 0;
		foreach ($data['transaksi'] as $key => $m) {


			$tdebet = $tdebet + $m['debet'];

			$tkredit = $tkredit + $m['kredit'];
			$total_saldo = $total_saldo + $m['debet'] - $m['kredit'];
			$actual_link = "http://$_SERVER[HTTP_HOST]" . "/plantation-api/api/GlobalReport/acc_laporan_jurnal?no_jurnal=" . $m['no_jurnal'] . "";

			$no++;
			$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative;">
							' . ($no) . '

						</td>
						<td>
						' . $m['ket'] . ' 
						
						</td>
						<td><a href="' . $actual_link  . '" target="_blank"> ' . $m['no_jurnal'] . ' </a>
						<td>
						' . $m['no_referensi'] . ' 
						
						</td>
						<td>
							' . $m['tanggal'] . ' 
						</td>';

			if ($m['debet'] > 0) {

				$html = $html . '<td style="text-align: right;">' . number_format($m['debet']) . '</td>';
			} else {
				$html = $html . '<td style="text-align: right;">-</td>';
			}
			if ($m['kredit'] > 0) {

				$html = $html . '<td style="text-align: right;">' . number_format($m['kredit']) . '</td>';
			} else {
				$html = $html . '<td style="text-align: right;">-</td>';
			}


			$html = $html . '<td style="text-align: right;">' . number_format($total_saldo > 0 ? $total_saldo : 0) . '</td>';
			$html = $html . '<td style="text-align: right;">' . number_format($total_saldo < 0 ? $total_saldo * -1 : 0) . '</td>';

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
                    <td style="text-align: right;">
                       ' . number_format($tdebet) . '

                    </td>
                    <td style="text-align: right;">
                        ' . number_format($tkredit) . '

                    </td>
                    <td>
                        &nbsp;
                    </td>
					<td>
                        &nbsp;
                    </td>
                </tr>
				</tbody>
				</table>
						';
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
	function prc_pp_slip_get($segment_3 = '')
	{

		$id = (int)$segment_3;
		$data = [];

		$queryHeader = "SELECT a.*,
		-- d.nama as gudang,
		f.nama as user_approve1,
		ff.nama as user_approve2,
		fff.nama as user_approve3,
		ffff.nama as user_approve4,
		fffff.nama as user_approve5,
		g.user_full_name as user_full_name,
		e.nama as lokasi
		FROM prc_pp_ht a 
		-- INNER JOIN gbm_organisasi d ON a.gudang_id=d.id
		INNER JOIN gbm_organisasi e ON a.lokasi_id=e.id
		LEFT JOIN karyawan f ON a.user_approve1=f.id
		LEFT JOIN karyawan ff ON a.user_approve2=ff.id
		LEFT JOIN karyawan fff ON a.user_approve3=fff.id
		LEFT JOIN karyawan ffff ON a.user_approve4=ffff.id
		LEFT JOIN karyawan fffff ON a.user_approve5=fffff.id
		LEFT JOIN fwk_users g ON a.dibuat_oleh=g.id
		WHERE a.id=" . $id . "";
		$dataHeader = $this->db->query($queryHeader)->row_array();

		$queryDetail = "SELECT a.*,
		b.kode as kode_barang,
		b.nama as nama_barang,
		f.nama as uom
		FROM prc_pp_dt a 
		inner join inv_item b on a.item_id=b.id 
		left join gbm_uom f on b.uom_id=f.id 
		WHERE  a.pp_hd_id = " . $id . "";
		$dataDetail = $this->db->query($queryDetail)->result_array();

		foreach ($dataDetail as $key => $value) {
			$stok = $this->InvItemModel->cek_stok_lokasi_get($dataHeader['lokasi_id'], $value['item_id'], $dataHeader['tanggal']);
			$dataDetail[$key]['stok'] = $stok;
		}


		// $perminta = $this->InvPermintaanBarangModel->print_slip($id);
		$data['header'] = 	$dataHeader;
		$data['detail'] = 	$dataDetail;


		$data['database'] = $this->db;

		$html = $this->load->view('PrcPp_laporan', $data, true);

		$filename = 'report_' . time();
		$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		// echo $html;
	}
	public function getAbsensiKebunDetailperKaryawan_get($tgl1, $tgl2, $karyawan_id)
	{

		// echo $karyawan_id;
		// exit();
		$html = "";
		$q0 = "SELECT a.*,b.nip,b.nama, c.nama as jabatan,b.status_pajak FROM  payroll_karyawan_gaji a 
		inner join karyawan b on a.karyawan_id=b.id
		left join payroll_jabatan c on b.jabatan_id=c.id
		where b.id=" . $karyawan_id  . "";
		$hd = $this->db->query($q0)->row_array();
		$no = 0;
		$strNo = '';
		$html = $html . "	
		<table   border='0' width='50%' style='border-collapse: collapse;'>
		<tr>
			<td>Nip</td>
			<td>:</td>
			<td>" . $hd['nip'] . "</td>
		 </tr>
		 <tr>
			<td>Nama</td>
			<td>:</td>
			<td>" . $hd['nama'] . "</td>
	  </tr>
		 </table>
		";

		$html = $html . "	
		<table   border='1' width='100%' style='border-collapse: collapse;'>
		<thead>
		<tr>
			<td style='text-align: center;'>No.</td>
			<td style='text-align: center;'>Jenis</td>
			<td style='text-align: center;'>No Transaksi</td>
			<td style='text-align: center;'>Tanggal</td>
			<td style='text-align: center;'>Hk</td>
			<td style='text-align: center;'>Upah</td>
			<td style='text-align: center;'>Premi</td>
			<td style='text-align: center;'>Denda</td>
			<td rowspan=2  style='text-align: center'>Jumlah</td>
		</tr></thead>
		";
		$d1 = new DateTime($tgl1);
		$d2 = new DateTime($tgl2);
		$jum_absen_perkaryawan = 0;
		$gaji_per_hari_efektif = $hd['gapok'] / 25;
		$jum_absen_perkaryawan = 0;
		$upah = 0;
		$upah_absensi = 0;
		$upah_panen = 0;
		$upah_pemeliharaan = 0;
		$upah_traksi = 0;
		$upah_workshop = 0;
		$premi = 0;
		$denda = 0;
		$lembur = 0;
		$premi_panen = 0;
		$premi_pemeliharaan = 0;
		$premi_traksi = 0;
		$premi_workshop = 0;
		$upah_panen_kerani = 0;
		$upah_panen_kerani = 0;
		$upah_panen_mandor = 0;
		$premi_panen_mandor = 0;
		$upah_pemeliharaan_kerani = 0;
		$premi_pemeliharaan_kerani = 0;
		$upah_pemeliharaan_mandor = 0;
		$premi_pemeliharaan_mandor = 0;
		$upah_perkaryawan = 0;
		$premi_perkaryawan = 0;
		$potongan = 0;
		$jum_jam_lembur = 0;

		$jum_absen = 0;
		$jum_absen = 0;
		$hk = 0;

		while ($d1 <= $d2) {
			$lembur = 0;
			$upah_absensi = 0;
			$tgl = $d1->format('Y-m-d');
			/* ngambil data absensi per periode */
			$qAbsensi = "SELECT * FROM payroll_absensi a inner join hrms_jenis_absensi b on a.jenis_absensi_id=b.id 
					where a.karyawan_id= " . $hd['karyawan_id'] . " 
					and tanggal ='" . $tgl . "' 
					and b.tipe ='DIBAYAR';";
			$resAbsensi = $this->db->query($qAbsensi)->result_array();

			foreach ($resAbsensi as $absensiKaryawan) {
				// $jum_hari_dibayar++;
				// $upah_absensi = $upah_absensi + $absensiKaryawan['premi'];
				if ($absensiKaryawan['kode'] == 'H') {
					// Jumlah Hadir 
					$hk = $hk++;;
					$jum_absen++;
					$jum_absen_perkaryawan++;
					$upah_absensi = $upah_absensi + $gaji_per_hari_efektif;
					$no++;
					$html = $html . "<tr>";
					$html = $html . "<td style='text-align: center'>" . $no . "</td>";
					$html = $html . "<td style='text-align: center'>ABSEN</td>";
					$html = $html . "<td style='text-align: center'></td>";
					$html = $html . "<td style='text-align: center'>" . tgl_indo_normal($absensiKaryawan['tanggal']) . "</td>";
					$html = $html . "<td style='text-align: center'>" . number_format($hk, 2) . "</td>";
					$html = $html . "<td style='text-align: center'>" . number_format($gaji_per_hari_efektif, 2) .  "</td>";
					$html = $html . "<td style='text-align: center'>0</td>";
					$html = $html . "<td style='text-align: center'>0</td>";
					$html = $html . "<td style='text-align: center'>" . number_format($gaji_per_hari_efektif, 2) .  "</td>";
					$html = $html . "</tr>";
				}
			}
			// $upah_absensi = $hd['gapok'] * $jum_hari_hadir;
			// $hk_potongan_hari =	$hari_masuk_efektif - $jum_hari_dibayar;
			// $hk_potongan_gaji =	$hk_potongan_hari * $gaji_per_hari_efektif;
			$qLembur = "SELECT * FROM  payroll_lembur where karyawan_id= " . $hd['karyawan_id'] . " 
					and	tanggal ='" . $tgl . "' ;";
			$resLembur = $this->db->query($qLembur)->result_array();
			foreach ($resLembur as $lemburKaryawan) {
				$jum_jam_lembur = $jum_jam_lembur + $lemburKaryawan['jumlah_jam'];
				$lembur = $lembur + $lemburKaryawan['nilai_lembur'];
				$no++;
				$html = $html . "<tr>";
				$html = $html . "<td style='text-align: center'>" . $no . "</td>";
				$html = $html . "<td style='text-align: center'>LEMBUR</td>";
				$html = $html . "<td style='text-align: center'></td>";
				$html = $html . "<td style='text-align: center'>" . tgl_indo_normal($lemburKaryawan['tanggal']) . "</td>";
				$html = $html . "<td style='text-align: center'>0</td>";
				$html = $html . "<td style='text-align: center'>" . number_format($lemburKaryawan['nilai_lembur'], 2) .  "</td>";
				$html = $html . "<td style='text-align: center'>0</td>";
				$html = $html . "<td style='text-align: center'>" . number_format($lemburKaryawan['nilai_lembur'], 2) .  "</td>";
				$html = $html . "</tr>";
			}

			/* Potongan */
			$qPotongan = "SELECT * FROM  payroll_potongan where karyawan_id= " . $hd['karyawan_id'] . " 
					and	tanggal ='" . $tgl . "' ;";
			$resPotongan = $this->db->query($qPotongan)->result_array();
			foreach ($resPotongan as $potonganKaryawan) {
				$potongan = $potongan + $potonganKaryawan['nilai'];
				$no++;
				$html = $html . "<tr>";
				$html = $html . "<td style='text-align: center'>" . $no . "</td>";
				$html = $html . "<td style='text-align: center'>Potongan</td>";
				$html = $html . "<td style='text-align: center'></td>";
				$html = $html . "<td style='text-align: center'>" . tgl_indo_normal($potonganKaryawan['tanggal']) . "</td>";
				$html = $html . "<td style='text-align: center'>0</td>";
				$html = $html . "<td style='text-align: center'>0</td>";
				$html = $html . "<td style='text-align: center'>0</td>";
				$html = $html . "<td style='text-align: center'>" . number_format($potonganKaryawan['nilai'], 2) .  "</td>";
				$html = $html . "<td style='text-align: center'>" . number_format($potonganKaryawan['nilai'] * -1, 2) .  "</td>";
				$html = $html . "</tr>";
			}

			/* ngambil data bkm umum per periode */
			$resUmum =	$this->db->query("SELECT *,b.jumlah_hk AS jumlah_hk,c.kode AS kode_absen from est_bkm_umum_ht a inner join 
			est_bkm_umum_dt b on a.id=b.bkm_umum_id 
			inner join hrms_jenis_absensi c ON b.jenis_absensi_id=c.id where b.karyawan_id= " . $hd['karyawan_id'] . " 
				and tanggal ='" . $tgl . "'  ")->result_array();
			$premi_umum = 0;
			$upah_umum = 0;
			// var_dump($resUmum);exit();
			foreach ($resUmum as $umum) {
				$hk = $hk + $umum['jumlah_hk'];
				$jum_absen++;
				$jum_absen_perkaryawan++;
				$premi_umum = $premi_umum + $umum['premi'];
				$upah_umum = $upah_umum + $umum['rupiah_hk'];
				$no++;
				$html = $html . "<tr>";
				$html = $html . "<td style='text-align: center'>" . $no . "</td>";
				$html = $html . "<td style='text-align: center'>BKM UMUM</td>";
				$html = $html . "<td style='text-align: center'>" . $umum['no_transaksi'] .  "</td>";
				$html = $html . "<td style='text-align: center'>" . tgl_indo_normal($umum['tanggal']) . "</td>";
				$html = $html . "<td style='text-align: center'>" . number_format($umum['jumlah_hk'], 2) . " - " . $umum['kode_absen'] . "</td>";
				$html = $html . "<td style='text-align: center'>" . number_format($umum['rupiah_hk'], 2) .  "</td>";
				$html = $html . "<td style='text-align: center'>" . number_format($umum['premi'], 2) .  "</td>";
				$html = $html . "<td style='text-align: center'>0</td>";
				$html = $html . "<td style='text-align: center'>" . number_format(($umum['rupiah_hk'] + $umum['premi']), 2) .  "</td>";
				$html = $html . "</tr>";
			}

			/* ngambil data bkm panen per periode */
			$resPanen =	$this->db->query("select * from est_bkm_panen_ht a inner join 
					est_bkm_panen_dt b on a.id=b.bkm_panen_id  where karyawan_id= " . $hd['karyawan_id'] . "  
					and tanggal ='" . $tgl . "' and is_premi_kontanan=0 ")->result_array();
			$premi_panen = 0;
			$upah_panen = 0;
			$denda_panen = 0;
			foreach ($resPanen as $panen) {
				$jum_absen++;
				$jum_absen_perkaryawan++;
				$hk = $hk + $panen['jumlah_hk'];
				$upah_panen = $upah_panen + $panen['rp_hk'];
				$pm_panen = (($panen['premi_panen'] + $panen['premi_brondolan']));
				$premi_panen = $premi_panen + (($panen['premi_panen'] + $panen['premi_brondolan']));
				$denda_panen = $denda_panen + $panen['denda_panen'];
				$no++;
				$html = $html . "<tr>";
				$html = $html . "<td style='text-align: center'>" . $no . "</td>";
				$html = $html . "<td style='text-align: center'>PANEN</td>";
				$html = $html . "<td style='text-align: center'>" . $panen['no_transaksi'] .  "</td>";
				$html = $html . "<td style='text-align: center'>" . tgl_indo_normal($panen['tanggal']) . "</td>";

				$html = $html . "<td style='text-align: center'>" . number_format($panen['jumlah_hk'], 2) .  "</td>";
				$html = $html . "<td style='text-align: center'>" . number_format($panen['rp_hk'], 2) .  "</td>";
				$html = $html . "<td style='text-align: center'>" . number_format($pm_panen, 2) .  "</td>";
				$html = $html . "<td style='text-align: center'>" . number_format($panen['denda_panen'], 2) .  "</td>";
				$html = $html . "<td style='text-align: center'>" . number_format((($panen['rp_hk'] + $pm_panen) - $panen['denda_panen']), 2) .  "</td>";
				$html = $html . "</tr>";
			}

			/* ngambil data bkm pemeliharaan per periode */
			$resPemeliharaan =	$this->db->query("select * from est_bkm_pemeliharaan_ht a inner join 
						est_bkm_pemeliharaan_dt b on a.id=b.bkm_pemeliharaan_id  where karyawan_id= " . $hd['karyawan_id'] . "  
						and tanggal ='" . $tgl . "'  ")->result_array();
			$premi_pemeliharaan = 0;
			$upah_pemeliharaan = 0;
			$denda_pemeliharaan = 0;
			foreach ($resPemeliharaan as $pemeliharaan) {
				$jum_absen++;
				$jum_absen_perkaryawan++;
				$hk = $hk + $pemeliharaan['jumlah_hk'];
				$premi_pemeliharaan = $premi_pemeliharaan + $pemeliharaan['premi'];
				$upah_pemeliharaan = $upah_pemeliharaan + $pemeliharaan['rupiah_hk'];
				$denda_pemeliharaan = $denda_pemeliharaan + $pemeliharaan['denda_pemeliharaan'];
				$no++;
				$html = $html . "<tr>";
				$html = $html . "<td style='text-align: center'>" . $no . "</td>";
				$html = $html . "<td style='text-align: center'>PEMELIHARAAN</td>";
				$html = $html . "<td style='text-align: center'>" . $pemeliharaan['no_transaksi'] .  "</td>";
				$html = $html . "<td style='text-align: center'>" . tgl_indo_normal($pemeliharaan['tanggal']) . "</td>";
				$html = $html . "<td style='text-align: center'>" . number_format($pemeliharaan['jumlah_hk'], 2) . "</td>";
				$html = $html . "<td style='text-align: center'>" . number_format($pemeliharaan['rupiah_hk'], 2) .  "</td>";
				$html = $html . "<td style='text-align: center'>" . number_format($pemeliharaan['premi'], 2) .  "</td>";
				$html = $html . "<td style='text-align: center'>" . number_format($pemeliharaan['denda_pemeliharaan'], 2) .  "</td>";
				$html = $html . "<td style='text-align: center'>" . number_format(($pemeliharaan['rupiah_hk'] + $pemeliharaan['premi'] - $pemeliharaan['denda_pemeliharaan']), 2) .  "</td>";
				$html = $html . "</tr>";
			}

			/* ngambil data bkm Traksi per periode */
			$resTraksi =	$this->db->query("select * from trk_kegiatan_kendaraan_ht a inner join 
				trk_kegiatan_kendaraan_dt b on a.id=b.trk_kegiatan_kendaraan_id  where b.karyawan_id= " . $hd['karyawan_id'] . " 
				and tanggal ='" . $tgl . "'  ")->result_array();
			$premi_traksi = 0;
			$upah_traksi = 0;
			$denda_traksi = 0;
			foreach ($resTraksi as $traksi) {
				$jum_absen++;
				$jum_absen_perkaryawan++;
				$hk = $hk + $traksi['jumlah_hk'];
				$premi_traksi = $premi_traksi + $traksi['premi'];
				$upah_traksi = $upah_traksi + $traksi['rupiah_hk'];
				$denda_traksi = $denda_traksi + $traksi['denda_traksi'];
				$no++;
				$html = $html . "<tr>";
				$html = $html . "<td style='text-align: center'>" . $no . "</td>";
				$html = $html . "<td style='text-align: center'>TRAKSI</td>";
				$html = $html . "<td style='text-align: center'>" . $traksi['no_transaksi'] .  "</td>";
				$html = $html . "<td style='text-align: center'>" . tgl_indo_normal($traksi['tanggal']) . "</td>";
				$html = $html . "<td style='text-align: center'>" . number_format($traksi['jumlah_hk'], 2) . "</td>";
				$html = $html . "<td style='text-align: center'>" . number_format($traksi['rupiah_hk'], 2) .  "</td>";
				$html = $html . "<td style='text-align: center'>" . number_format($traksi['premi'], 2) .  "</td>";
				$html = $html . "<td style='text-align: center'>" . number_format($traksi['denda_traksi'], 2) .  "</td>";
				$html = $html . "<td style='text-align: center'>" . number_format(($traksi['rupiah_hk'] + $traksi['premi'])-$traksi['denda_traksi'], 2) .  "</td>";
				$html = $html . "</tr>";
			}

			/* ngambil data bkm workshop per periode */
			$resWorkshop =	$this->db->query("select * from wrk_kegiatan_ht a inner join 
				wrk_kegiatan_dt b on a.id=b.wrk_kegiatan_id  where b.karyawan_id= " . $hd['karyawan_id'] . " 
				and tanggal ='" . $tgl . "'  ")->result_array();
			$premi_workshop = 0;
			$upah_workshop = 0;
			foreach ($resWorkshop as $workshop) {
				$jum_absen++;
				$jum_absen_perkaryawan++;
				$hk = $hk + $workshop['jumlah_hk'];
				$premi_workshop = $premi_workshop + $workshop['premi'];
				$upah_workshop = $upah_workshop + $workshop['rupiah_hk'];
				$no++;
				$html = $html . "<tr>";
				$html = $html . "<td style='text-align: center'>" . $no . "</td>";
				$html = $html . "<td style='text-align: center'>WORKSHOP</td>";
				$html = $html . "<td style='text-align: center'>" . $workshop['no_transaksi'] .  "</td>";
				$html = $html . "<td style='text-align: center'>" . tgl_indo_normal($workshop['tanggal']) . "</td>";
				$html = $html . "<td style='text-align: center'>" . number_format($workshop['jumlah_hk'], 2) .  "</td>";
				$html = $html . "<td style='text-align: center'>" . number_format($workshop['rupiah_hk'], 2) .  "</td>";
				$html = $html . "<td style='text-align: center'>" . number_format($workshop['premi'], 2) .  "</td>";
				$html = $html . "<td style='text-align: center'>0</td>";
				$html = $html . "<td style='text-align: center'>" . number_format(($workshop['rupiah_hk'] + $workshop['premi']), 2) .  "</td>";
				$html = $html . "</tr>";
			}

			/* ngambil data Mandor dari bkm panen per periode */
			$resPanen =	$this->db->query("select * from est_bkm_panen_ht where mandor_id= " . $hd['karyawan_id'] . " 
		 					and tanggal ='" . $tgl . "' and is_premi_kontanan=0")->result_array();
			$premi_panen_mandor = 0;
			$upah_panen_mandor = 0;
			$denda_panen_mandor = 0;
			foreach ($resPanen as $panen) {
				$jum_absen++;
				$hk = $hk + $panen['jumlah_hk_mandor'];
				$upah_panen_mandor = $upah_panen_mandor +  $panen['rp_hk_mandor']; //$gaji_per_hari_efektif; //
				$premi_panen_mandor = $premi_panen_mandor + $panen['premi_mandor'];
				$denda_panen_mandor = $denda_panen_mandor + $panen['denda_mandor'];
				$no++;
				$html = $html . "<tr>";
				$html = $html . "<td style='text-align: center'>" . $no . "</td>";
				$html = $html . "<td style='text-align: center'>MANDOR</td>";
				$html = $html . "<td style='text-align: center'>" . $panen['no_transaksi'] .  "</td>";
				$html = $html . "<td style='text-align: center'>" . tgl_indo_normal($panen['tanggal']) . "</td>";
				$html = $html . "<td style='text-align: center'>" . number_format($panen['jumlah_hk_mandor'], 2) .  "</td>";
				$html = $html . "<td style='text-align: center'>" . number_format($panen['rp_hk_mandor'], 2) .  "</td>";
				$html = $html . "<td style='text-align: center'>" . number_format($panen['premi_mandor'], 2) .  "</td>";
				$html = $html . "<td style='text-align: center'>" . number_format($panen['denda_mandor'], 2) .  "</td>";
				$html = $html . "<td style='text-align: center'>" . number_format((($panen['rp_hk_mandor'] + $panen['premi_mandor']) - $panen['denda_mandor']), 2)  .  "</td>";
				$html = $html . "</tr>";
			}
			/* ngambil data Krani dari bkm panen per periode */
			$resPanen =	$this->db->query("select * from est_bkm_panen_ht where kerani_id= " . $hd['karyawan_id'] . "  
					and tanggal ='" . $tgl . "' and is_premi_kontanan=0 ")->result_array();
			$premi_panen_kerani = 0;
			$upah_panen_kerani = 0;
			$denda_panen_kerani = 0;
			foreach ($resPanen as $panen) {
				$jum_absen++;
				$hk = $hk + $panen['jumlah_hk_kerani'];
				$upah_panen_kerani = $upah_panen_kerani + $panen['rp_hk_kerani']; //$gaji_per_hari_efektif; //
				$premi_panen_kerani = $premi_panen_kerani + $panen['premi_kerani'];
				$denda_panen_kerani = $denda_panen_kerani + $panen['denda_kerani'];
				$no++;
				$html = $html . "<tr>";
				$html = $html . "<td style='text-align: center'>" . $no . "</td>";
				$html = $html . "<td style='text-align: center'>KERANI</td>";
				$html = $html . "<td style='text-align: center'>" . $panen['no_transaksi'] .  "</td>";
				$html = $html . "<td style='text-align: center'>" . tgl_indo_normal($panen['tanggal']) . "</td>";
				$html = $html . "<td style='text-align: center'>" . number_format($panen['jumlah_hk_kerani'], 2) .  "</td>";
				$html = $html . "<td style='text-align: center'>" . number_format($panen['rp_hk_kerani'], 2) .  "</td>";
				$html = $html . "<td style='text-align: center'>" . number_format($panen['premi_kerani'], 2) .  "</td>";
				$html = $html . "<td style='text-align: center'>" . number_format($panen['denda_kerani'], 2) .  "</td>";
				$html = $html . "<td style='text-align: center'>" . number_format((($panen['rp_hk_kerani'] + $panen['premi_kerani']) - $panen['denda_kerani']), 2) .  "</td>";
				$html = $html . "</tr>";
			}

			/* ngambil data Mandor dari bkm Pemeliharaan per periode */
			$resPanen =	$this->db->query("select * from est_bkm_pemeliharaan_ht where mandor_id= " . $hd['karyawan_id'] . " 
		 					and tanggal ='" . $tgl . "' ")->result_array();
			$upah_pemeliharaan_mandor = 0;
			$premi_pemeliharaan_mandor = 0;
			$denda_pemeliharaan_mandor = 0;
			foreach ($resPanen as $panen) {
				$jum_absen++;
				$hk = $hk + $panen['jumlah_hk_mandor'];
				$upah_pemeliharaan_mandor = $upah_pemeliharaan_mandor + $panen['rp_hk_mandor']; //$gaji_per_hari_efektif; //
				$premi_pemeliharaan_mandor = $premi_pemeliharaan_mandor + $panen['premi_mandor'];
				$denda_pemeliharaan_mandor = $denda_pemeliharaan_mandor + $panen['denda_mandor'];
				$no++;
				$html = $html . "<tr>";
				$html = $html . "<td style='text-align: center'>" . $no . "</td>";
				$html = $html . "<td style='text-align: center'>MANDOR</td>";
				$html = $html . "<td style='text-align: center'>" . $panen['no_transaksi'] .  "</td>";
				$html = $html . "<td style='text-align: center'>" . tgl_indo_normal($panen['tanggal']) . "</td>";
				$html = $html . "<td style='text-align: center'>" . number_format($panen['jumlah_hk_mandor'], 2) .  "</td>";
				$html = $html . "<td style='text-align: center'>" . number_format($panen['rp_hk_mandor'], 2) .  "</td>";
				$html = $html . "<td style='text-align: center'>" . number_format($panen['premi_mandor'], 2) .  "</td>";
				$html = $html . "<td style='text-align: center'>" . number_format($panen['denda_mandor'], 2) .  "</td>";
				$html = $html . "<td style='text-align: center'>" . number_format((($panen['rp_hk_mandor'] + $panen['premi_mandor']) - $panen['denda_mandor']), 2)  .  "</td>";
				$html = $html . "</tr>";
			}
			/* ngambil data Krani dari bkm Pemeliharaan per periode */
			$resPanen =	$this->db->query("select * from est_bkm_pemeliharaan_ht where kerani_id= " . $hd['karyawan_id'] . "  
					and tanggal ='" . $tgl . "'  ")->result_array();
			$premi_pemeliharaan_kerani = 0;
			$upah_pemeliharaan_kerani = 0;
			$denda_pemeliharaan_kerani = 0;
			foreach ($resPanen as $panen) {
				$jum_absen++;
				$hk = $hk + $panen['jumlah_hk_kerani'];
				$upah_pemeliharaan_kerani = $upah_pemeliharaan_kerani + $panen['rp_hk_kerani']; //$gaji_per_hari_efektif; //
				$premi_pemeliharaan_kerani = $premi_pemeliharaan_kerani + $panen['premi_kerani'];
				$denda_pemeliharaan_kerani = $denda_pemeliharaan_kerani + $panen['denda_kerani'];
				$no++;
				$html = $html . "<tr>";
				$html = $html . "<td style='text-align: center'>" . $no . "</td>";
				$html = $html . "<td style='text-align: center'>KERANI</td>";
				$html = $html . "<td style='text-align: center'>" . $panen['no_transaksi'] .  "</td>";
				$html = $html . "<td style='text-align: center'>" . tgl_indo_normal($panen['tanggal']) . "</td>";
				$html = $html . "<td style='text-align: center'>" . number_format($panen['jumlah_hk_kerani'], 2) .  "</td>";
				$html = $html . "<td style='text-align: center'>" . number_format($panen['rp_hk_kerani'], 2) .  "</td>";
				$html = $html . "<td style='text-align: center'>" . number_format($panen['premi_kerani'], 2) .  "</td>";
				$html = $html . "<td style='text-align: center'>" . number_format($panen['denda_kerani'], 2) .  "</td>";
				$html = $html . "<td style='text-align: center'>" . number_format((($panen['rp_hk_kerani'] + $panen['premi_kerani']) - $panen['denda_kerani']), 2) .  "</td>";
				$html = $html . "</tr>";
			}
			$upah = $upah + ($upah_umum + $upah_absensi + $upah_panen + $upah_pemeliharaan + $upah_traksi + $upah_workshop + $upah_panen_mandor + $upah_panen_kerani + $upah_pemeliharaan_mandor + $upah_pemeliharaan_kerani);
			$premi = $premi + ($premi_umum + $lembur + $premi_panen + $premi_pemeliharaan + $premi_traksi + $premi_workshop + $premi_panen_mandor + $premi_panen_kerani + $premi_pemeliharaan_mandor + $premi_pemeliharaan_kerani);
			$denda = $denda + $denda_panen + $denda_pemeliharaan + $denda_panen_mandor + $denda_panen_kerani + $denda_pemeliharaan_mandor + $denda_pemeliharaan_kerani+$potongan+$denda_traksi;
			$jum_absen_str = $jum_absen == 0 ? "" : $jum_absen;
			$d1->modify('+1 day');
		}

		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: center'></td>";
		$html = $html . "<td style='text-align: center'></td>";
		$html = $html . "<td style='text-align: center'></td>";
		$html = $html . "<td style='text-align: center'></td>";
		$html = $html . "<td style='text-align: center'>" . 	number_format($hk, 2) . "</td>";
		$html = $html . "<td style='text-align: center'>" . 	number_format($upah, 2) . "</td>";
		$html = $html . "<td style='text-align: center'>" . 	number_format($premi, 2) . "</td>";
		$html = $html . "<td style='text-align: center'>" . 	number_format($denda, 2) . "</td>";
		$html = $html . "<td style='text-align: center'> " . 	number_format((($upah + $premi) - $denda), 2) . "</td>";
		$html = $html . "</tr>";
		$html = $html . "</table>";
		// $this->set_response(array("status" => "OK", "data" => $html), REST_Controller::HTTP_OK);
		// return;

		echo $html;
	}
	public function getAbsensiKebunDetail_get($tgl, $karyawan_id)
	{

		$html = "";
		$q0 = "SELECT a.*,b.nip,b.nama, c.nama as jabatan,b.status_pajak FROM  payroll_karyawan_gaji a 
		inner join karyawan b on a.karyawan_id=b.id
		left join payroll_jabatan c on b.jabatan_id=c.id
		where b.id=" . $karyawan_id  . "";
		$hd = $this->db->query($q0)->row_array();
		$no = 0;
		$strNo = '';
		$html = $html . "	
		<table   border='0' width='50%' style='border-collapse: collapse;'>
		<tr>
			<td>Nip</td>
			<td>:</td>
			<td>" . $hd['nip'] . "</td>
		 </tr>
		 <tr>
			<td>Nama</td>
			<td>:</td>
			<td>" . $hd['nama'] . "</td>
	   </tr>
	   <tr>
			<td>Tanggal</td>
			<td>:</td>
			<td>" . $tgl . "</td>
		 </tr>
		 </table>
		";

		$html = $html . "	
		<table   border='1' width='100%' style='border-collapse: collapse;'>
		<thead>
		<tr>
			<td>No.</td>
			<td>Jenis</td>
			<td>No Transaksi</td>
			<td>Tanggal</td>
			<td>HK</td>
			<td>Upah</td>
			<td>Premi</td>
			<td>Denda</td>
			<td>Jam Lembur</td>
			<td>Rp Lembur</td>
			<td rowspan=2  style='text-align: center'>Jumlah</td>
		</tr></thead>
		";

		$jum_absen_perkaryawan = 0;
		$gaji_per_hari_efektif = $hd['gapok'] / 25;

		/* ngambil data absensi per periode */
		$qAbsensi = "SELECT * FROM payroll_absensi a inner join hrms_jenis_absensi b on a.jenis_absensi_id=b.id 
					where a.karyawan_id= " . $hd['karyawan_id'] . " 
					and tanggal ='" . $tgl . "' 
					and b.tipe ='DIBAYAR';";
		$resAbsensi = $this->db->query($qAbsensi)->result_array();
		$jum_jam_lembur = 0;
		$lembur = 0;
		$upah_absensi = 0;
		$jum_absen = 0;
		$pendapatan = 0;
		$potongan = 0;
		foreach ($resAbsensi as $absensiKaryawan) {
			// $jum_hari_dibayar++;
			// $upah_absensi = $upah_absensi + $absensiKaryawan['premi'];
			if ($absensiKaryawan['kode'] == 'H') { // Jumlah Hadir 
				$jum_absen++;
				$jum_absen_perkaryawan++;
				$upah_absensi = $upah_absensi + $gaji_per_hari_efektif;
				$no++;
				$html = $html . "<tr>";
				$html = $html . "<td style='text-align: center'>" . $no . "</td>";
				$html = $html . "<td style='text-align: center'>ABSEN</td>";
				$html = $html . "<td style='text-align: center'></td>";
				$html = $html . "<td style='text-align: center'>" . $absensiKaryawan['tanggal'] . "</td>";
				$html = $html . "<td style='text-align: right'>1</td>";
				$html = $html . "<td style='text-align: right'>" . $gaji_per_hari_efektif .  "</td>";
				$html = $html . "<td style='text-align: right'>0</td>";
				$html = $html . "<td style='text-align: right'>0</td>";
				$html = $html . "<td style='text-align: right'>0</td>";
				$html = $html . "<td style='text-align: right'>0</td>";
				$html = $html . "<td style='text-align: right'>" . $gaji_per_hari_efektif .  "</td>";
				$html = $html . "</tr>";
			}
		}
		// $upah_absensi = $hd['gapok'] * $jum_hari_hadir;
		// $hk_potongan_hari =	$hari_masuk_efektif - $jum_hari_dibayar;
		// $hk_potongan_gaji =	$hk_potongan_hari * $gaji_per_hari_efektif;
		$qLembur = "SELECT * FROM  payroll_lembur where karyawan_id= " . $hd['karyawan_id'] . " 
					and	tanggal ='" . $tgl . "' ;";
		$resLembur = $this->db->query($qLembur)->result_array();
		foreach ($resLembur as $lemburKaryawan) {
			$jum_jam_lembur = $jum_jam_lembur + $lemburKaryawan['jumlah_jam'];
			$lembur = $lembur + $lemburKaryawan['nilai_lembur'];
			$no++;
			$html = $html . "<tr>";
			$html = $html . "<td style='text-align: center'>" . $no . "</td>";
			$html = $html . "<td style='text-align: center'>LEMBUR</td>";
			$html = $html . "<td style='text-align: center'></td>";
			$html = $html . "<td style='text-align: center'>" . $lemburKaryawan['tanggal'] . "</td>";
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>" . $lemburKaryawan['jumlah_jam'] .  "</td>";
			$html = $html . "<td style='text-align: right'>" . $lemburKaryawan['nilai_lembur'] .  "</td>";
			$html = $html . "<td style='text-align: right'>" . $lemburKaryawan['nilai_lembur'] .  "</td>";
			$html = $html . "</tr>";
		}

	
		/* ngambil data bkm umum per periode */
		$resUmum =	$this->db->query("SELECT *,b.jumlah_hk AS jumlah_hk,c.kode AS kode_absen from est_bkm_umum_ht a inner join 
		est_bkm_umum_dt b on a.id=b.bkm_umum_id 
		inner join hrms_jenis_absensi c ON b.jenis_absensi_id=c.id  where b.karyawan_id= " . $hd['karyawan_id'] . " 
				and tanggal ='" . $tgl . "'  ")->result_array();
		$premi_umum = 0;
		$upah_umum = 0;
		foreach ($resUmum as $umum) {
			$jum_absen = $jum_absen + $umum['jumlah_hk'];
			$jum_absen_perkaryawan++;
			$premi_umum = $premi_umum + $umum['premi'];
			$upah_umum = $upah_umum + $umum['rupiah_hk'];
			$no++;
			$html = $html . "<tr>";
			$html = $html . "<td style='text-align: center'>" . $no . "</td>";
			$html = $html . "<td style='text-align: center'>BKN Umum</td>";
			$html = $html . "<td style='text-align: center'>" . $umum['no_transaksi'] .  "</td>";
			$html = $html . "<td style='text-align: center'>" . $umum['tanggal'] . "</td>";
			$html = $html . "<td style='text-align: center'>" . $umum['jumlah_hk'] . " - " . $umum['kode_absen'] . "</td>";
			$html = $html . "<td style='text-align: right'>" . $umum['rupiah_hk'] .  "</td>";
			$html = $html . "<td style='text-align: right'>" . $umum['premi'] .  "</td>";
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>" . ($umum['rupiah_hk'] + $umum['premi']) .  "</td>";
			$html = $html . "</tr>";
		}


		/* ngambil data bkm panen per periode */
		$resPanen =	$this->db->query("select * from est_bkm_panen_ht a inner join 
					est_bkm_panen_dt b on a.id=b.bkm_panen_id  where karyawan_id= " . $hd['karyawan_id'] . "  
					and tanggal ='" . $tgl . "' and is_premi_kontanan=0 ")->result_array();
		$premi_panen = 0;
		$upah_panen = 0;
		$denda_panen = 0;
		foreach ($resPanen as $panen) {
			$jum_absen = $jum_absen + $panen['jumlah_hk'];
			$jum_absen_perkaryawan++;
			$upah_panen = $upah_panen + $panen['rp_hk'];
			$premi_panen = $premi_panen + ($panen['premi_panen'] + $panen['premi_brondolan']);
			$denda_panen = $denda_panen + $panen['denda_panen'];
			$no++;
			$html = $html . "<tr>";
			$html = $html . "<td style='text-align: center'>" . $no . "</td>";
			$html = $html . "<td style='text-align: center'>PANEN</td>";
			$html = $html . "<td style='text-align: center'>" . $panen['no_transaksi'] .  "</td>";
			$html = $html . "<td style='text-align: center'>" . $panen['tanggal'] . "</td>";
			$html = $html . "<td style='text-align: center'>" . $panen['jumlah_hk'] . "</td>";
			$html = $html . "<td style='text-align: right'>" . $panen['rp_hk'] .  "</td>";
			$html = $html . "<td style='text-align: right'>" . ($panen['premi_panen'] + $panen['premi_brondolan']) .  "</td>";
			$html = $html . "<td style='text-align: right'>" . $panen['denda_panen'] .  "</td>";
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>" . (($panen['rp_hk'] + $panen['premi_panen'] + $panen['premi_brondolan']) - $panen['denda_panen']) .  "</td>";

			$html = $html . "</tr>";
		}

		/* ngambil data bkm pemeliharaan per periode */
		$resPemeliharaan =	$this->db->query("select * from est_bkm_pemeliharaan_ht a inner join 
						est_bkm_pemeliharaan_dt b on a.id=b.bkm_pemeliharaan_id  where karyawan_id= " . $hd['karyawan_id'] . "  
						and tanggal ='" . $tgl . "'  ")->result_array();
		$premi_pemeliharaan = 0;
		$upah_pemeliharaan = 0;
		$denda_pemeliharaan = 0;
		foreach ($resPemeliharaan as $pemeliharaan) {
			$jum_absen = $jum_absen + $pemeliharaan['jumlah_hk'];
			$jum_absen_perkaryawan++;
			$premi_pemeliharaan = $premi_pemeliharaan + $pemeliharaan['premi'];
			$upah_pemeliharaan = $upah_pemeliharaan + $pemeliharaan['rupiah_hk'];
			$denda_pemeliharaan = $denda_pemeliharaan + $pemeliharaan['denda_pemeliharaan'];
			$no++;
			$jum = ($pemeliharaan['rupiah_hk'] + $pemeliharaan['premi']) - ($pemeliharaan['denda_pemeliharaan']);
			$html = $html . "<tr>";
			$html = $html . "<td style='text-align: center'>" . $no . "</td>";
			$html = $html . "<td style='text-align: center'>PEMELIHARAAN</td>";
			$html = $html . "<td style='text-align: center'>" . $pemeliharaan['no_transaksi'] .  "</td>";
			$html = $html . "<td style='text-align: center'>" . $pemeliharaan['tanggal'] . "</td>";
			$html = $html . "<td style='text-align: center'>" . $pemeliharaan['jumlah_hk'] . "</td>";
			$html = $html . "<td style='text-align: right'>" . $pemeliharaan['rupiah_hk'] .  "</td>";
			$html = $html . "<td style='text-align: right'>" . $pemeliharaan['premi'] .  "</td>";
			$html = $html . "<td style='text-align: right'>" . $pemeliharaan['denda_pemeliharaan'] .  "</td>";
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>" . $jum .  "</td>";
			// $html = $html . "<td style='text-align: right'>" . ($pemeliharaan['rupiah_hk'] + $pemeliharaan['premi']) .  "</td>";
			$html = $html . "</tr>";
		}

		/* ngambil data bkm Traksi per periode */
		$resTraksi =	$this->db->query("select * from trk_kegiatan_kendaraan_ht a inner join 
				trk_kegiatan_kendaraan_dt b on a.id=b.trk_kegiatan_kendaraan_id  where b.karyawan_id= " . $hd['karyawan_id'] . " 
				and tanggal ='" . $tgl . "'  ")->result_array();
		$premi_traksi = 0;
		$upah_traksi = 0;
		$denda_traksi = 0;
		foreach ($resTraksi as $traksi) {
			$jum_absen = $jum_absen + $traksi['jumlah_hk'];
			$jum_absen_perkaryawan++;
			$premi_traksi = $premi_traksi + $traksi['premi'];
			$upah_traksi = $upah_traksi + $traksi['rupiah_hk'];
			$denda_traksi = $denda_traksi + $traksi['denda_traksi'];
			$jum = ($traksi['rupiah_hk'] + $traksi['premi']) - ($traksi['denda_traksi']);
			$no++;
			$html = $html . "<tr>";
			$html = $html . "<td style='text-align: center'>" . $no . "</td>";
			$html = $html . "<td style='text-align: center'>TRAKSI</td>";
			$html = $html . "<td style='text-align: center'>" . $traksi['no_transaksi'] .  "</td>";
			$html = $html . "<td style='text-align: center'>" . $traksi['tanggal'] . "</td>";
			$html = $html . "<td style='text-align: center'>" . $traksi['jumlah_hk'] . "</td>";
			$html = $html . "<td style='text-align: right'>" . $traksi['rupiah_hk'] .  "</td>";
			$html = $html . "<td style='text-align: right'>" . $traksi['premi'] .  "</td>";
			$html = $html . "<td style='text-align: right'>" . $traksi['denda_traksi'] .  "</td>";
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>" . ($jum) .  "</td>";
			$html = $html . "</tr>";
		}

		/* ngambil data bkm workshop per periode */
		$resWorkshop =	$this->db->query("select * from wrk_kegiatan_ht a inner join 
				wrk_kegiatan_dt b on a.id=b.wrk_kegiatan_id  where b.karyawan_id= " . $hd['karyawan_id'] . " 
				and tanggal ='" . $tgl . "'  ")->result_array();
		$premi_workshop = 0;
		$upah_workshop = 0;
		foreach ($resWorkshop as $workshop) {
			$jum_absen = $jum_absen + $workshop['jumlah_hk'];
			$jum_absen_perkaryawan++;
			$premi_workshop = $premi_workshop + $workshop['premi'];
			$upah_workshop = $upah_workshop + $workshop['rupiah_hk'];
			$no++;
			$html = $html . "<tr>";
			$html = $html . "<td style='text-align: center'>" . $no . "</td>";
			$html = $html . "<td style='text-align: center'>WORKSHOP</td>";
			$html = $html . "<td style='text-align: center'>" . $workshop['no_transaksi'] .  "</td>";
			$html = $html . "<td style='text-align: center'>" . $workshop['tanggal'] . "</td>";
			$html = $html . "<td style='text-align: center'>" . $workshop['jumlah_hk'] . "</td>";
			$html = $html . "<td style='text-align: right'>" . $workshop['rupiah_hk'] .  "</td>";
			$html = $html . "<td style='text-align: right'>" . $workshop['premi'] .  "</td>";
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>" . ($workshop['rupiah_hk'] + $workshop['premi']) .  "</td>";
			$html = $html . "</tr>";
		}

		/* ngambil data Mandor dari bkm panen per periode */
		$resPanen =	$this->db->query("select * from est_bkm_panen_ht where mandor_id= " . $hd['karyawan_id'] . " 
		 					and tanggal ='" . $tgl . "' and is_premi_kontanan=0 ")->result_array();
		$premi_panen_mandor = 0;
		$upah_panen_mandor = 0;
		$denda_panen_mandor = 0;
		foreach ($resPanen as $panen) {
			$jum_absen = $jum_absen + $panen['jumlah_hk_mandor'];
			$upah_panen_mandor = $upah_panen_mandor + $panen['rp_hk_mandor'];
			$premi_panen_mandor = $premi_panen_mandor + $panen['premi_mandor'];
			$denda_panen_mandor = $denda_panen_mandor + $panen['denda_mandor'];
			$no++;
			$html = $html . "<tr>";
			$html = $html . "<td style='text-align: center'>" . $no . "</td>";
			$html = $html . "<td style='text-align: center'>MANDOR</td>";
			$html = $html . "<td style='text-align: center'>" . $panen['no_transaksi'] .  "</td>";
			$html = $html . "<td style='text-align: center'>" . $panen['tanggal'] . "</td>";
			$html = $html . "<td style='text-align: center'>" . $panen['jumlah_hk_mandor'] . "</td>";
			$html = $html . "<td style='text-align: right'>" . $upah_panen_mandor .  "</td>";
			$html = $html . "<td style='text-align: right'>" . $panen['premi_mandor'] .  "</td>";
			$html = $html . "<td style='text-align: right'>" . $panen['denda_mandor'] .  "</td>";
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>" . (($upah_panen_mandor + $panen['premi_mandor']) - $panen['denda_mandor'])  .  "</td>";
			$html = $html . "</tr>";
		}
		/* ngambil data Krani dari bkm panen per periode */
		$resPanen =	$this->db->query("select * from est_bkm_panen_ht where kerani_id= " . $hd['karyawan_id'] . "  
					and tanggal ='" . $tgl . "' and is_premi_kontanan=0 ")->result_array();
		$premi_panen_kerani = 0;
		$upah_panen_kerani = 0;
		$denda_panen_kerani = 0;
		foreach ($resPanen as $panen) {
			$jum_absen = $jum_absen + $panen['jumlah_hk_kerani'];
			$upah_panen_kerani = $upah_panen_kerani + $panen['rp_hk_kerani'];
			$premi_panen_kerani = $premi_panen_kerani + $panen['premi_kerani'];
			$denda_panen_kerani = $denda_panen_kerani + $panen['denda_kerani'];
			$no++;
			$html = $html . "<tr>";
			$html = $html . "<td style='text-align: center'>" . $no . "</td>";
			$html = $html . "<td style='text-align: center'>KERANI</td>";
			$html = $html . "<td style='text-align: center'>" . $panen['no_transaksi'] .  "</td>";
			$html = $html . "<td style='text-align: center'>" . $panen['tanggal'] . "</td>";
			$html = $html . "<td style='text-align: center'>" . $panen['jumlah_hk_kerani'] . "</td>";
			$html = $html . "<td style='text-align: right'>" . $upah_panen_kerani .  "</td>";
			$html = $html . "<td style='text-align: right'>" . $panen['premi_kerani'] .  "</td>";
			$html = $html . "<td style='text-align: right'>" . $panen['denda_kerani'] .  "</td>";
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>" . (($upah_panen_kerani + $panen['premi_kerani']) - $panen['denda_kerani']) .  "</td>";
			$html = $html . "</tr>";
		}

		/* ngambil data Mandor dari bkm Pemeliharaan per periode */
		$resPanen =	$this->db->query("select * from est_bkm_pemeliharaan_ht where mandor_id= " . $hd['karyawan_id'] . " 
		 					and tanggal ='" . $tgl . "' ")->result_array();
		$premi_pemeliharaan_mandor = 0;
		$upah_pemeliharaan_mandor = 0;
		$denda_pemeliharaan_mandor = 0;
		foreach ($resPanen as $panen) {
			$jum_absen = $jum_absen + $panen['jumlah_hk_mandor'];
			$upah_pemeliharaan_mandor = $upah_pemeliharaan_mandor + $panen['rp_hk_mandor']; // $gaji_per_hari_efektif; //
			$premi_pemeliharaan_mandor = $premi_pemeliharaan_mandor + $panen['premi_mandor'];
			$denda_pemeliharaan_mandor = $denda_pemeliharaan_mandor + $panen['denda_mandor'];
			$no++;
			$html = $html . "<tr>";
			$html = $html . "<td style='text-align: center'>" . $no . "</td>";
			$html = $html . "<td style='text-align: center'>MANDOR</td>";
			$html = $html . "<td style='text-align: center'>" . $panen['no_transaksi'] .  "</td>";
			$html = $html . "<td style='text-align: center'>" . $panen['tanggal'] . "</td>";
			$html = $html . "<td style='text-align: center'>" . $panen['jumlah_hk_mandor'] . "</td>";
			$html = $html . "<td style='text-align: right'>" . $upah_pemeliharaan_mandor .  "</td>";
			$html = $html . "<td style='text-align: right'>" . $panen['premi_mandor'] .  "</td>";
			$html = $html . "<td style='text-align: right'>" . $panen['denda_mandor'] .  "</td>";
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>" . (($upah_pemeliharaan_mandor + $panen['premi_mandor']) - $panen['denda_mandor'])  .  "</td>";
			$html = $html . "</tr>";
		}
		/* ngambil data Krani dari bkm Pemeliharaan per periode */
		$resPanen =	$this->db->query("select * from est_bkm_pemeliharaan_ht where kerani_id= " . $hd['karyawan_id'] . "  
					and tanggal ='" . $tgl . "'  ")->result_array();
		$premi_pemeliharaan_kerani = 0;
		$upah_pemeliharaan_kerani = 0;
		$denda_pemeliharaan_kerani = 0;
		foreach ($resPanen as $panen) {
			$jum_absen = $jum_absen + $panen['jumlah_hk_kerani'];
			$upah_pemeliharaan_kerani = $upah_pemeliharaan_kerani +  $panen['rp_hk_kerani']; //$gaji_per_hari_efektif; //
			$premi_pemeliharaan_kerani = $premi_pemeliharaan_kerani + $panen['premi_kerani'];
			$denda_pemeliharaan_kerani = $denda_pemeliharaan_kerani + $panen['denda_kerani'];
			$no++;
			$html = $html . "<tr>";
			$html = $html . "<td style='text-align: center'>" . $no . "</td>";
			$html = $html . "<td style='text-align: center'>KERANI</td>";
			$html = $html . "<td style='text-align: center'>" . $panen['no_transaksi'] .  "</td>";
			$html = $html . "<td style='text-align: center'>" . $panen['tanggal'] . "</td>";
			$html = $html . "<td style='text-align: center'>" . $panen['jumlah_hk_kerani'] . "</td>";
			$html = $html . "<td style='text-align: right'>" . $upah_pemeliharaan_kerani .  "</td>";
			$html = $html . "<td style='text-align: right'>" . $panen['premi_kerani'] .  "</td>";
			$html = $html . "<td style='text-align: right'>" . $panen['denda_kerani'] .  "</td>";
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>" . (($upah_pemeliharaan_kerani + $panen['premi_kerani']) - $panen['denda_kerani']) .  "</td>";
			$html = $html . "</tr>";
		}

		$qPendapatan = "SELECT * FROM  payroll_pendapatan where karyawan_id= " . $hd['karyawan_id'] . " 
		and	tanggal ='" . $tgl . "' ;";
		$resPendapatan = $this->db->query($qPendapatan)->result_array();
		foreach ($resPendapatan as $pendapatanKaryawan) {
			$pendapatan = $pendapatan + $pendapatanKaryawan['nilai'];
			$no++;
			$html = $html . "<tr>";
			$html = $html . "<td style='text-align: center'>" . $no . "</td>";
			$html = $html . "<td style='text-align: center'>Pendapatan</td>";
			$html = $html . "<td style='text-align: center'></td>";
			$html = $html . "<td style='text-align: center'>" . $pendapatanKaryawan['tanggal'] . "</td>";
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>" . $pendapatanKaryawan['nilai'] .  "</td>";
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>0</td>"; 
			$html = $html . "<td style='text-align: right'>" . $pendapatanKaryawan['nilai'] .  "</td>";
			$html = $html . "</tr>";
		}
		$qPotongan = "SELECT * FROM  payroll_potongan where karyawan_id= " . $hd['karyawan_id'] . " 
		and	tanggal ='" . $tgl . "' ;";
		$resPotongan = $this->db->query($qPotongan)->result_array();
		foreach ($resPotongan as $potonganKaryawan) {
			$potongan = $potongan + $potonganKaryawan['nilai'];
			$no++;
			$html = $html . "<tr>";
			$html = $html . "<td style='text-align: center'>" . $no . "</td>";
			$html = $html . "<td style='text-align: center'>Potongan</td>";
			$html = $html . "<td style='text-align: center'></td>";
			$html = $html . "<td style='text-align: center'>" . $potonganKaryawan['tanggal'] . "</td>";
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>" . ($potonganKaryawan['nilai'] ).  "</td>";
			
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>0</td>";
			$html = $html . "<td style='text-align: right'>" . ($potonganKaryawan['nilai'])* -1 .  "</td>";
			$html = $html . "</tr>";
		}

		$upah = $upah_umum + $upah_absensi + $upah_panen + $upah_pemeliharaan + $upah_traksi + $upah_workshop + $upah_panen_mandor + $upah_panen_kerani + $upah_pemeliharaan_mandor + $upah_pemeliharaan_kerani+$pendapatan;
		$premi = $premi_umum + $premi_panen + $premi_pemeliharaan + $premi_traksi + $premi_workshop + $premi_panen_mandor + $premi_panen_kerani + $premi_pemeliharaan_mandor + $premi_pemeliharaan_kerani;
		$denda = $denda_panen + $denda_pemeliharaan + $denda_panen_mandor + $denda_panen_kerani + $denda_pemeliharaan_mandor + $denda_pemeliharaan_kerani+$potongan+$denda_traksi;


		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: center'></td>";
		$html = $html . "<td style='text-align: center'></td>";
		$html = $html . "<td style='text-align: center'></td>";
		$html = $html . "<td style='text-align: center'></td>";
		$html = $html . "<td style='text-align: right'> " . $jum_absen . "</td>";
		$html = $html . "<td style='text-align: right'> " . $upah . "</td>";
		$html = $html . "<td style='text-align: right'> " . $premi . "</td>";
		$html = $html . "<td style='text-align: right'> " . $denda . "</td>";
		$html = $html . "<td style='text-align: right'> " . $jum_jam_lembur . "</td>";
		$html = $html . "<td style='text-align: right'> " . $lembur  . "</td>";
		$html = $html . "<td style='text-align: right'> " . (($upah + $premi + $lembur) - $denda) . "</td>";
		$html = $html . "</tr>";
		$html = $html . "</table>";
		// $this->set_response(array("status" => "OK", "data" => $html), REST_Controller::HTTP_OK);
		// return;

		echo $html;
	}


	function trk_cost_detail_get($kendaraan_id, $tgl_mulai, $tgl_akhir)
	{

		error_reporting(0);
		// $traksi_id     = $this->post('traksi_id', true);
		// $tgl_mulai =  $this->post('tgl_mulai', true);
		// $tgl_akhir = $this->post('tgl_akhir', true);
		//$kendaraan_id = $this->post('kendaraan_id', true);
		$format_laporan     = 'view';

		// $retrieveTraksi = $this->db->query("select * from gbm_organisasi where id=" . $traksi_id . "")->row_array();
		$retrieveKendaraan = $this->db->query("select * from trk_kendaraan where id=" . $kendaraan_id . "")->row_array();

		$retrieveCost = $this->db->query("SELECT kode_akun,nama_akun,tanggal,ket,no_jurnal,no_ref,kode_kendaraan,nama_kendaraan,nama_traksi,
				(debet-kredit)AS nilai_biaya
				FROM trk_cost_kendaraan_vw
					where
			 kendaraan_mesin_id='" . $kendaraan_id . "' and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
			 order by kode_akun,tanggal,no_ref")->result_array();

		// var_dump($results);
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report();
		} else {
			$html = get_header_report();
		}


		$html = $html . '
<h2>Laporan Rincian Biaya Kendaraan/AB/Mesin</h2>
<h3>Kendaraan/AB/Mesin:' . $retrieveKendaraan['nama'] . ' - ' . $retrieveKendaraan['kode'] . ' </h3>
<h4>' . $tgl_mulai . ' - ' . $tgl_akhir . ' </h4>';

		$html = $html . ' <table  >
			<thead>
				<tr>
				<th width="4%">No.</th>	
				<th>Akun</th>		
				<th>Tanggal</th>
				<th>Keterangan</th>
				<th>No Jurnal</th>
				<th>No Ref</th>
				<th style="text-align: right;">Nilai Biaya </th>
	
				</tr>
			</thead>
			<tbody>';


		$no = 0;
		$bahan = 0;
		$lainnya = 0;
		$upah = 0;
		$total = 0;
		$sub_total = 0;

		// foreach ($retrieveCost as $key => $m) {
		for ($i = 0; $i < count($retrieveCost); $i++) {
			$m = $retrieveCost[$i];
			$no++;
			// $upah = $upah + $m['upah'];
			// $bahan = $bahan + $m['bahan'];
			// $lainnya = $lainnya + $m['lainnya'];
			$total = $total + $m['nilai_biaya'];
			$sub_total = $sub_total + $m['nilai_biaya'];
			$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative;">	' . ($no) . '</td>
						<td>' . $m['kode_akun'] . ' - ' . $m['nama_akun'] . '  </td>
						<td>' . $m['tanggal'] . ' </td>
						<td>' . $m['ket'] . ' </td>
						<td>' . $m['no_jurnal'] . ' </td>
						<td>' . $m['no_ref'] . ' </td>
						<td style="text-align: right;">' . number_format($m['nilai_biaya']) . ' 
													
						</td>';

			$html = $html . '</tr>';
			if ($i < (count($retrieveCost) - 1)) {
				if (($i + 1) <= (count($retrieveCost) - 1)) {
					if ($m['kode_akun'] != $retrieveCost[($i + 1)]['kode_akun']) {
						$html = $html . ' 	
						<tr class=":: arc-content">
						<td colspan=6 style="text-align: right;"><b>SUB TOTAL  ' . $m['kode_akun'] . ' - ' . $m['nama_akun'] . '</b>
						</td>
						
						<td style="text-align: right;"><b>' . number_format($sub_total) . ' </b>	
						</td>		
						</tr>
						';
						$sub_total = 0;
						$no = 0;
					}
				}
			} else {
				$html = $html . ' 	
						<tr class=":: arc-content">
						<td colspan=6 style="text-align: right;"><b>SUB TOTAL  ' .  $m['kode_akun'] . ' - ' . $m['nama_akun'] . '</b>
						</td>
						<td style="text-align: right;"><b>' . number_format($sub_total) . ' </b>	
						</td>		
						</tr>
						';
				$sub_total = 0;
				$no = 0;
			}
		}

		$html = $html . ' 	
						<tr class=":: arc-content">
						<td colspan=6 style="text-align: right;">
						<td style="text-align: right;"><b>
						' . number_format($total) . ' </b>
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
	function trk_pemakaian_inventory_get($kendaraan_id, $tgl_mulai, $tgl_akhir)
	{

		error_reporting(0);

		// $traksi_id     = $this->post('traksi_id', true);
		// $tgl_mulai =  $this->post('tgl_mulai', true);
		// $tgl_akhir = $this->post('tgl_akhir', true);
		// $kendaraan_id = $this->post('kendaraan_id', true);
		$format_laporan     = 'view';

		// $retrieveEstate = $this->db->query("select * from gbm_organisasi where id=" . $traksi_id . "")->row_array();
		$nama_kendaraan = "Semua";
		$q = "select a.*,b.kode as kode_item,b.nama as nama_item,c.kode as uom,
		d.kode AS kode_kendaraan,d.nama AS nama_kendaraan, e.kode AS kode_traksi,
		e.nama AS nama_traksi,
		f.kode AS kode_kegiatan,f.nama AS nama_kegiatan,
		g.nama AS nama_kategori
		 FROM inv_transaksi_harian a 
		INNER JOIN inv_item b ON a.item_id=b.id
		INNER JOIN gbm_uom c ON b.uom_id=c.id
		INNER JOIN trk_kendaraan d ON a.kendaraan_id=d.id
		INNER JOIN gbm_organisasi e ON d.traksi_id=e.id
		INNER JOIN acc_kegiatan f ON a.kegiatan_id=f.id
		INNER JOIN inv_kategori g ON b.inv_kategori_id=g.id
		where  a.tanggal>='" . $tgl_mulai . "' and a.tanggal<='" . $tgl_akhir . "'";
		if ($kendaraan_id) {
			$retrieveKendaraan = $this->db->query("select * from trk_kendaraan where id=" . $kendaraan_id . "")->row_array();
			$nama_kendaraan = $retrieveKendaraan['nama'];
			$q = $q . " and d.id=" . $kendaraan_id . "";
		}
		$q = $q . "  ORDER BY g.nama,a.tanggal ;";

		$retrieveCost = $this->db->query($q)->result_array();
		// var_dump($retrieveCost);return;
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report();
		} else {
			$html = get_header_report();
		}


		$html = $html . '
		<h2>Laporan Pemakaian BBM Dan Suku Cadang Traksi</h2>
		<h3>Kendaraan/AB/Mesin:' . $nama_kendaraan . ' </h3>
		<h4>' . $tgl_mulai . ' - ' . $tgl_akhir . ' </h4>';

		$html = $html . ' <table>
			<thead>
				<tr>
				<th width="4%">No.</th>	
				<th>Kendaraan/AB/Mesin</th>	
				<th>Tanggal</th>
				<th>No Transaki</th>
				<th>Alokasi/kegiatan</th>
				<th>Item </th>
				<th>uom</th>
				<th style="text-align: right;">Qty </th>
				<th style="text-align: right;">Harga </th>
				<th style="text-align: right;">Nilai </th>				
				</tr>
			</thead>
			<tbody>';

		$no = 0;
		$sub_total = 0;
		$total = 0;
		for ($i = 0; $i < count($retrieveCost); $i++) {
			// foreach ($retrieveCost as $key => $m) {
			$m = $retrieveCost[$i];
			$no++;
			$sub_total = $sub_total + $m['nilai_keluar'];
			$total = $total + $m['nilai_keluar'];
			$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative;">	' . ($no) . '</td>
						<td>' . $m['nama_kendaraan'] . ' - ' . $m['kode_kendaraan'] . ' </td>
						<td>' . $m['tanggal'] . ' </td>
						<td>' . $m['no_transaksi'] . ' </td>
						<td>' . $m['nama_kegiatan'] . ' </td>
						<td>' . $m['kode_item'] . ' - ' . $m['nama_item'] . ' </td>
						<td>' . $m['uom'] . ' </td>						
						<td style="text-align: right;">' . number_format($m['qty_keluar']) . ' 
						<td style="text-align: right;">' . number_format($m['nilai_keluar'] / $m['qty_keluar']) . ' 
						<td style="text-align: right;">' . number_format($m['nilai_keluar']) . ' 																			
						</td>';
			$html = $html . '</tr>';

			if ($i < (count($retrieveCost) - 1)) {
				if (($i + 1) <= (count($retrieveCost) - 1)) {
					if ($m['nama_kategori'] != $retrieveCost[($i + 1)]['nama_kategori']) {
						$html = $html . ' 	
						<tr class=":: arc-content">
						<td colspan=9 style="text-align: right;"><b>SUB TOTAL  ' . $m['nama_kategori'] . '</b>
						</td>
						
						<td style="text-align: right;"><b>' . number_format($sub_total) . ' </b>	
						</td>		
						</tr>
						';
						$sub_total = 0;
						$no = 0;
					}
				}
			} else {
				$html = $html . ' 	
						<tr class=":: arc-content">
						<td colspan=9 style="text-align: right;"><b>SUB TOTAL  ' . $m['nama_kategori'] . '</b>
						</td>
						<td style="text-align: right;"><b>' . number_format($sub_total) . ' </b>	
						</td>		
						</tr>
						';
				$sub_total = 0;
				$no = 0;
			}
		}

		$html = $html . ' 	
						<tr class=":: arc-content">
						<td colspan=9 style="text-align: right;"><b>TOTAL</b>
						</td>
						<td style="text-align: right;"><b>' . number_format($total) . ' </b>				
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
	function trk_kegiatan_detail_get($kendaraan_id, $tanggal_awal, $tanggal_akhir)
	{
		// $id = (int)$segment_3;
		$data = [];

		$input = [
			'no_po' => '01/PO/DPA',
			'lokasi_id' => 252,
			'tgl_mulai' => '2020-01-01',
			'tgl_akhir' => '2022-04-18',
		];

		$format_laporan = 'view';
		// $lokasi_id = $this->post('lokasi_id', true);
		// $traksi_id = $this->post('traksi_id', true);
		// $kendaraan_id = $this->post('kendaraan_id', true);
		// $tanggal_awal = $this->post('tgl_mulai', true);
		// $tanggal_akhir = $this->post('tgl_akhir', true);

		// $lokasi_id=$input['lokasi_id'];
		// $tanggal_awal=$input['tgl_mulai'];
		// $tanggal_akhir=$input['tgl_akhir'];

		$queryPo = "SELECT
		b.*,
		a.status_kendaraan,
		a.tanggal AS tanggal,
		a.no_transaksi AS no_transaksi,
		c.nama AS blok,
		d.nama AS kegiatan,
		e.nama as nama_kendaraan,
		e.kode as kode_kendaraan,
		f.kode as kode_traksi,
		f.nama as nama_traksi,
		b.id AS id
		
		FROM trk_kegiatan_kendaraan_ht a
		left JOIN trk_kegiatan_kendaraan_log b ON b.trk_kegiatan_kendaraan_id=a.id
		left JOIN gbm_organisasi c ON b.blok_id=c.id
		left JOIN acc_kegiatan d ON b.acc_kegiatan_id=d.id
		left JOIN trk_kendaraan e on a.kendaraan_id=e.id
		left JOIN gbm_organisasi f on a.traksi_id=f.id
		where a.tanggal between  '" . $tanggal_awal . "' and  '" . $tanggal_akhir . "'	
		";



		// $filter_lokasi = "Semua";
		// if ($lokasi_id) {
		// 	$queryPo = $queryPo . " and b.lokasi_id=" . $lokasi_id . "";
		// 	$res = $this->db->query("select * from gbm_organisasi where id=" . $lokasi_id . "")->row_array();
		// 	$filter_lokasi = $res['nama'];
		// }
		$filter_traksi = "Semua";
		// if ($traksi_id) {
		// 	$queryPo = $queryPo . " and a.traksi_id=" . $traksi_id . "";
		// 	$res = $this->db->query("select * from gbm_organisasi where id=" . $traksi_id . "")->row_array();
		// 	$filter_traksi = $res['nama'];
		// }
		$filter_kendaraan = "Semua";
		if ($kendaraan_id) {
			$queryPo = $queryPo . " and a.kendaraan_id=" . $kendaraan_id . "";
			$res = $this->db->query("select * from trk_kendaraan where id=" . $kendaraan_id . "")->row_array();
			$filter_kendaraan = $res['nama'];
		}

		$queryPo = $queryPo . " order by 	a.tanggal,a.kendaraan_id";
		$dataPo = $this->db->query($queryPo)->result_array();

		$data['po'] = 	$dataPo;
		$data['filter_kendaraan'] = 	$filter_kendaraan;
		$data['filter_lokasi'] = 	$filter_traksi;
		$data['filter_tgl_awal'] = 	$tanggal_awal;
		$data['filter_tgl_akhir'] = $tanggal_akhir;

		$html = $this->load->view('Trk_KegiatanKendaraan_Kegiatan_Laporan', $data, true);

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		// echo $html;
		if ($format_laporan == 'xls') {
			// $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			// $spreadsheet = $reader->loadFromString($html);
			// // $reader->setSheetIndex(1);
			// //$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
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
	// -----------------------TEST VIEW-----------------------------------//
	function print_coba_get($segment_3 = '181')
	{

		$id = (int)$segment_3;
		$data = [];

		$queryHeader = "SELECT a.*,
		b.nama as lokasi_afd,
		f.nama as lokasi_traksi,
		d.nama as gudang,
		c.nama as karyawan,
		e.nama as lokasi
		FROM inv_pemakaian_ht a 
		left JOIN gbm_organisasi b ON a.lokasi_afd_id=b.id
		left JOIN gbm_organisasi d ON a.gudang_id=d.id
		left JOIN gbm_organisasi e ON a.lokasi_id=e.id
		left JOIN gbm_organisasi f ON a.lokasi_traksi_id=f.id
		left JOIN karyawan c on a.karyawan_id=c.id  WHERE a.id=" . $id . "";
		$dataHeader = $this->db->query($queryHeader)->row_array();

		$queryDetail = "SELECT a.*,
		b.kode as kode_barang,
		b.nama as nama_barang,
		f.nama as uom,
		c.kode as blok,
		d.nama as nama_kendaraan,
		e.nama as nama_kegiatan,
		d.kode as kode_kendaraan 
		FROM inv_pemakaian_dt a 
		inner join inv_item b on a.item_id=b.id 
		left join gbm_organisasi c on a.blok_id=c.id 
		left join trk_kendaraan d on a.traksi_id=d.id 
		left join acc_kegiatan e on a.kegiatan_id=e.id 
		left join gbm_uom f on b.uom_id=f.id WHERE  a.inv_pemakaian_id = " . $id . "";
		$dataDetail = $this->db->query($queryDetail)->result_array();



		// $perminta = $this->InvPermintaanBarangModel->print_slip($id);
		$data['header'] = 	$dataHeader;
		$data['detail'] = 	$dataDetail;

		$html = $this->load->view('Cobastyle', $data, true);

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		echo $html;
	}
	// -----------------------TEST VIEW-----------------------------------//
}
