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

class InvLaporan extends Rest_Controller
{
	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('InvItemModel');
		$this->load->model('InvKategoriModel');
		$this->load->model('GbmOrganisasiModel');
		$this->load->model('M_DatatablesModel');
		$this->load->library('pdfgenerator');
	}
	public function list_post()
	{
		$post = $this->post();

		$query  = "SELECT a.*,b.nama as nama_gudang FROM inv_masukkeluar_hd a inner join  inv_gudang b on a.gudang_id=b.id";
		$search = array('no_bukti', 'a.tgl', 'b.nama');
		$where  = null;

		$isWhere = null;
		// $isWhere = 'artikel.deleted_at IS NULL';

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	public function cek_stok_lokasi_get($lokasi_id, $item_id, $tanggal)
	{
		$query = "SELECT e.id, a.item_id,
		sum(qty_masuk-qty_keluar) as stok FROM inv_transaksi_harian a
		inner join inv_item c on a.item_id=c.id
		inner join gbm_organisasi d on a.gudang_id=d.id
		inner join gbm_organisasi e on d.parent_id=e.id
		left join gbm_uom f on c.uom_id=f.id
		where e.id=" . $lokasi_id . " and a.item_id=" . $item_id . " and a.tanggal <='" . $tanggal . "'
		group by  e.id,a.item_id";

		$res   = $this->db->query($query)->row_array();
		$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_OK);
	}

	function laporan_rekap_stok_post()
	{
		$format_laporan =  $this->post('format_laporan', true);

		$gudang_id     = $this->post('gudang_id', true);
		$kategori_id    = $this->post('kategori_id', true);
		$tanggal_mulai =  $this->post('tgl_mulai', true);
		$tanggal_akhir =  $this->post('tgl_akhir', true);
		$tampil_stok_nol =  $this->post('tampil_stok_nol', true);
		$judulKategori = 'Semua';
		$kategori = array();
		$judulGudang = 'Semua';
		$gudang = array();
		$gudang   = $this->db->query("select * from gbm_organisasi where id=" . $gudang_id . "")->row_array();
		$judulGudang = $gudang['nama'];
		$queryItem = "select a.*, b.nama as nama_kategori,c.kode as uom
       from inv_item a left join inv_kategori b on a.inv_kategori_id=b.id
	   left join gbm_uom c on a.uom_id=c.id
       where 1=1 ";
		if (!empty($kategori_id)) {
			if (($kategori_id != 'Semua')) {
				$queryItem = $queryItem . " and b.id=" . $kategori_id . "";
				$kategori   = $this->db->query("select * from inv_kategori where id=" . $kategori_id . "")->row_array();
				$judulKategori = $kategori['nama'];
			}
		}
		$res = array();
		$items   = $this->db->query($queryItem)->result_array();
		foreach ($items as $key => $item) {
			$querySaldoAwal = "SELECT a.gudang_id,a.item_id,
			sum(qty_masuk-qty_keluar) as stok FROM inv_transaksi_harian a
			inner join inv_item c on a.item_id=c.id
			inner join gbm_organisasi d on a.gudang_id=d.id
			left join gbm_uom e on c.uom_id=e.id
           where a.item_id=" . $item['id'] . " and  a.tanggal < '" . $tanggal_mulai . "'
           and a.gudang_id=" . $gudang_id . "
            group by  a.gudang_id,a.item_id";

			$awal   = $this->db->query($querySaldoAwal)->row_array();
			$item['saldo_awal'] = (!empty($awal)) ? $awal['stok'] : 0;

			$queryMasuk = "SELECT a.gudang_id,a.item_id,
			sum(qty_masuk) as stok FROM inv_transaksi_harian a
			inner join inv_item c on a.item_id=c.id
			inner join gbm_organisasi d on a.gudang_id=d.id
			left join gbm_uom e on c.uom_id=e.id
            where a.item_id=" . $item['id'] . " and  a.tanggal >= '" . $tanggal_mulai . "'
            and  a.tanggal <= '" . $tanggal_akhir . "' 
            and a.gudang_id=" . $gudang_id . "
             group by  a.gudang_id,a.item_id";

			$masuk   = $this->db->query($queryMasuk)->row_array();
			$item['masuk'] = (!empty($masuk['stok'])) ? $masuk['stok'] : 0;

			$queryKeluar = "SELECT a.gudang_id,a.item_id,
			sum(qty_keluar) as stok FROM inv_transaksi_harian a
			inner join inv_item c on a.item_id=c.id
			inner join gbm_organisasi d on a.gudang_id=d.id
			left join gbm_uom e on c.uom_id=e.id
             where a.item_id=" . $item['id'] . " and  a.tanggal >= '" . $tanggal_mulai . "'
            and  a.tanggal <= '" . $tanggal_akhir . "'  
            and a.gudang_id=" . $gudang_id . "
            group by  a.gudang_id,a.item_id";
			$keluar   = $this->db->query($queryKeluar)->row_array();
			$item['keluar'] = (!empty($keluar['stok'])) ? $keluar['stok'] : 0;
			$stok = $item['saldo_awal'] + $item['masuk'] - $item['keluar'];
			if ($tampil_stok_nol == true) {
				$res[] = $item;
			} else {
				if ($stok > 0) {
					$res[] = $item;
				}
			}
		}

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
	<div class="kop-print">
	  <div class="kop-nama">KLINIK ANNAJAH</div>
	  <div class="kop-info"> Jl Sukarno Hatta No 12 No 12, Bandung Jawa Barat 14450</div>
	  <div class="kop-info">Telp : (021) 6684055</div>
	</div>
	  <hr class="kop-print-hr">
  </div>
  </div>
  <h3 class="title">LAPORAN REKAP STOK</h3>
  <table class="no_border" style="width:30%">
			<tr>
					<td>Periode</td>
					<td>:</td>
					<td>'. tgl_indo($tanggal_mulai) . ' - ' . tgl_indo($tanggal_akhir) .  '</td>
			</tr>
			<tr>
					<td>Gudang</td>
					<td>:</td>
					<td>' . $judulGudang . '</td>
			</tr>
			<tr>	
					<td>Kategori</td>
					<td>:</td>
					<td>' . $judulKategori .  '</td>
			</tr>
			
	</table>
			<br> ';

		$html = $html .  ' <table  border="1" width="100%" style="border-collapse: collapse;" >
			<thead>
				<tr>
				<th width="4%">No.</th>
				<th>Kode</th>
				<th>Nama</th>
				<th>Kategori</th>
				<th>Satuan </th>
				<th >Stok awal</th>
				<th >Masuk</th>
				<th >Keluar</th>
				<th >Stok Akhir</th>
				<th >Min Stok</th>
				<th style="text-align: center;">Selisih Min Stok</th>
				
				</tr>
			</thead>
			<tbody>';


		$no = 0;
		$jumlah = 0;



		foreach ($res as $key => $m) {
			$no++;
			$jumlah = $m['saldo_awal'] + $m['masuk'] - $m['keluar'];
			$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative; text-align: center;">
							' . ($no) . '

						</td>
						<td style="text-align: center;">
						' . $m['kode'] . ' 
						
						</td>
						<td>
						' . $m['nama'] . ' 
						
						</td>
						<td>
							' . $m['nama_kategori'] . ' 
						</td>
						
						<td style="text-align: center;">
						' . $m['uom'] . ' 
							
						</td>
					
						<td style="text-align: right;">' . $this->format_number_report($m['saldo_awal'],2) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['masuk'],2) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['keluar'],2) . ' 
						<td style="text-align: right;">' . $this->format_number_report($jumlah,2) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['min_stok'],2) . ' 	
						<td style="text-align: right;">' . $this->format_number_report(($jumlah - $m['min_stok']),2) . ' 	
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


						<td style="text-align: right;">
							
						</td>

						<td>
							&nbsp;
						</td>
						<td>
							&nbsp;
						</td>
						<td style="text-align: right;">
							
						</td>
						<td style="text-align: right;">
							
						</td>
						<td style="text-align: right;">
							
						</td>
						<td style="text-align: right;">
							
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

	function laporan_kartu_stok_post()
	{
		$jenis_laporan =  $this->post('jenis_laporan', true);
		if ($jenis_laporan == 'by_item') {
			$this->laporan_kartu_stok_by_item();
		} else if ($jenis_laporan == 'by_kategori') {
			$this->laporan_kartu_stok_by_kategori();
		}
	}
	function laporan_kartu_stok_by_item()
	{
		$format_laporan =  $this->post('format_laporan', true);
		$gudang_id = $this->post('gudang_id', true);
		$item_id = $this->post('item_id', true);
		$tanggal_mulai =  $this->post('tgl_mulai', true);
		$tanggal_akhir = $this->post('tgl_akhir', true);

		$querySaldoAwal = "  SELECT a.gudang_id,a.item_id,
        sum(qty_masuk-qty_keluar) as stok FROM inv_transaksi_harian a
        inner join inv_item c on a.item_id=c.id
        inner join gbm_organisasi d on a.gudang_id=d.id
		left join gbm_uom e on c.uom_id=e.id
        where a.item_id=" . $item_id . " and  a.gudang_id=" . $gudang_id . " and a.tanggal < '" . $tanggal_mulai . "'
         group by  a.gudang_id,a.item_id ;";


		$saldoAwal    = $this->db->query($querySaldoAwal)->row_array();
		$data['saldo_awal'] = (!empty($saldoAwal)) ? $saldoAwal['stok'] : 0;
		//    print_r($querySaldoAwal);exit();

		$queryTransaksi   = "SELECT a.gudang_id,d.nama as gudang,a.tanggal as tgl,a.no_transaksi as no_bukti,a.tipe as ket,c.kode, c.nama,e.kode as satuan,
        a.qty_masuk,a.qty_keluar,f.nama as nama_kegiatan,g.kode as kode_blok,g.nama as nama_blok,h.nama as nama_kendaraan,i.nama as nama_afdeling
		 FROM inv_transaksi_harian a 
         inner join inv_item c on a.item_id=c.id
         inner join  gbm_organisasi d on a.gudang_id=d.id
		 left join gbm_uom e on c.uom_id=e.id
		 left join acc_kegiatan f on a.kegiatan_id=f.id
		 left join gbm_organisasi g on a.blok_stasiun_id=g.id
		 left join trk_kendaraan h on a.kendaraan_id=h.id
		 left join gbm_organisasi i on g.parent_id=i.id
         where a.item_id=" . $item_id . " and  a.gudang_id=" . $gudang_id . "
          and a.tanggal >= '" . $tanggal_mulai . "'   and a.tanggal <= '" . $tanggal_akhir . "'
          order by a.tanggal,a.tanggal_proses  ;
          ";


		$results  = $this->db->query($queryTransaksi)->result_array();
		$data['transaksi'] = $results;

		$item = $this->InvItemModel->retrieve($item_id);
		$gudang = $this->GbmOrganisasiModel->retrieve($gudang_id);

		//$this->set_response(array("status" => "OK", "data" => $data), REST_Controller::HTTP_OK);
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
	<div class="kop-print">
	  <div class="kop-nama">KLINIK ANNAJAH</div>
	  <div class="kop-info"> Jl Sukarno Hatta No 12 No 12, Bandung Jawa Barat 14450</div>
	  <div class="kop-info">Telp : (021) 6684055</div>
	</div>
	  <hr class="kop-print-hr">
  </div>
  </div>
  <h3 class="title">LAPORAN KARTU STOK</h3>
  <table class="no_border" style="width:30%">
			<tr>
					<td>Periode</td>
					<td>:</td>
					<td>'. tgl_indo($tanggal_mulai) . ' - ' . tgl_indo($tanggal_akhir) .  '</td>
			</tr>
			<tr>
					<td>Gudang</td>
					<td>:</td>
					<td>' .$gudang['nama'] . '</td>
			</tr>
			<tr>	
					<td>Item</td>
					<td>:</td>
					<td>' . $item['kode'] . ' - ' . $item['nama']  .  '</td>
			</tr>
			
	</table>
			<br> ';

		$html = $html . '  <table  border="1" width="100%" style="border-collapse: collapse;" >
			<thead>
			<tr>
                    <th width="4%" rowspan="2">No.</th>
                    <th rowspan="2">Keterangan</th>                 
					<th rowspan="2">Kegiatan</th>
					<th rowspan="2">Blok/Mesin</th>
					<th rowspan="2">Kendaraan/AB/Mesin</th>
					<th rowspan="2">No Bukti</th>
                    <th rowspan="2">Tgl</th>
                    <th colspan="2" style="text-align: center;">Transaksi</th>
                    <th  rowspan="2" style="text-align: center;">Stok Akhir</th>
                   
                </tr>
				<tr>

                    <th style="text-align: center;">masuk</th>
                    <th style="text-align: center;">keluar</th>
					
                   

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

                    <td style="text-align: right;">

                        ' . $this->format_number_report($data['saldo_awal'],2) . '

                    </td>

                </tr>';


		$total_saldo = $data['saldo_awal'];
		$jummasuk = 0;
		$jumkeluar = 0;
		$no = 0;
		foreach ($data['transaksi'] as $key => $m) {

			//$keterangan='Blok/Stasiun'.$m['nama_blok'].', Kegiatan'.$m['nama_kegiatan'].', Kendaraan/Mesin:'.$m['nama_mesin'];
			$jummasuk = $jummasuk + $m['qty_masuk'];

			$jumkeluar = $jumkeluar + $m['qty_keluar'];
			$total_saldo = $total_saldo + $m['qty_masuk'] - $m['qty_keluar'];

			$no++;
			$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative; text-align: center;">
							' . ($no) . '

						</td>
						<td>
						' . $m['ket'] . ' 
						
						</td>
						<td>
						' . $m['nama_kegiatan'] . ' 
						
						</td>
						<td>
						' . $m['nama_afdeling'] . '-' . $m['nama_blok'] . ' 
						
						</td>
						<td>
						' . $m['nama_kendaraan'] . ' 
						
						</td>
						<td>
						' . $m['no_bukti'] . ' 
						
						</td>
						<td style="text-align: center;">
							' . tgl_indo_normal($m['tgl']) . ' 
						</td>';

			if ($m['qty_masuk'] > 0) {

				$html = $html . '<td style="text-align: right;">' . $this->format_number_report($m['qty_masuk'],2) . '</td>';
			} else {
				$html = $html . '<td style="text-align: right;">-</td>';
			}
			if ($m['qty_keluar'] > 0) {

				$html = $html . '<td style="text-align: right;">' . $this->format_number_report($m['qty_keluar'],2) . '</td>';
			} else {
				$html = $html . '<td style="text-align: right;">-</td>';
			}


			$html = $html . '<td style="text-align: right;">' . $this->format_number_report($total_saldo,2) . '</td>';

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
                       ' . $this->format_number_report($jummasuk,2) . '

                    </td>
                    <td style="text-align: right;">
                        ' . $this->format_number_report($jumkeluar,2) . '

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
	function laporan_kartu_stok_by_kategori()
	{
		$format_laporan =  $this->post('format_laporan', true);
		$gudang_id = $this->post('gudang_id', true);
		$item_id = $this->post('item_id', true);
		$kategori_id = $this->post('kategori_id', true);
		$tanggal_mulai =  $this->post('tgl_mulai', true);
		$tanggal_akhir = $this->post('tgl_akhir', true);
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report();
		} else {
			$html = get_header_report_v2();
		}

		$kategori = $this->InvKategoriModel->retrieve($kategori_id);
		$gudang = $this->GbmOrganisasiModel->retrieve($gudang_id);
		$html = $html . '
		<div class="row">
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
		<h3 class="title">LAPORAN KARTU STOK</h3>
		<table class="no_border" style="width:30%">
				  <tr>
						  <td>Periode</td>
						  <td>:</td>
						  <td>'. tgl_indo($tanggal_mulai) . ' - ' . tgl_indo($tanggal_akhir) .  '</td>
				  </tr>
				  <tr>
						  <td>Gudang</td>
						  <td>:</td>
						  <td>' .$gudang['nama'] . '</td>
				  </tr>
				  <tr>	
						  <td>Kategori</td>
						  <td>:</td>
						  <td>' . $kategori['nama']  .  '</td>
				  </tr>
				  
		  </table>
				  <br> ';


		$queryItem = "  SELECT * from inv_item where inv_kategori_id=" . $kategori_id . " ;";
	
	
		$res_item    = $this->db->query($queryItem)->result_array();
		
		foreach ($res_item  as $key => $item) {
			$item_id = $item['id'];

			$querySaldoAwal = "  SELECT a.gudang_id,a.item_id,
        sum(qty_masuk-qty_keluar) as stok FROM inv_transaksi_harian a
        inner join inv_item c on a.item_id=c.id
        inner join gbm_organisasi d on a.gudang_id=d.id
		left join gbm_uom e on c.uom_id=e.id
        where a.item_id=" . $item_id . " and  a.gudang_id=" . $gudang_id . " and a.tanggal < '" . $tanggal_mulai . "'
         group by  a.gudang_id,a.item_id ;";


			$saldoAwal    = $this->db->query($querySaldoAwal)->row_array();
			$data['saldo_awal'] = (!empty($saldoAwal)) ? $saldoAwal['stok'] : 0;
			//    print_r($querySaldoAwal);exit();

			$queryTransaksi   = "SELECT a.gudang_id,d.nama as gudang,a.tanggal as tgl,a.no_transaksi as no_bukti,a.tipe as ket,c.kode, c.nama,e.kode as satuan,
        a.qty_masuk,a.qty_keluar,f.nama as nama_kegiatan,g.kode as kode_blok,g.nama as nama_blok,h.nama as nama_kendaraan,i.nama as nama_afdeling
		 FROM inv_transaksi_harian a 
         inner join inv_item c on a.item_id=c.id
         inner join  gbm_organisasi d on a.gudang_id=d.id
		 left join gbm_uom e on c.uom_id=e.id
		 left join acc_kegiatan f on a.kegiatan_id=f.id
		 left join gbm_organisasi g on a.blok_stasiun_id=g.id
		 left join trk_kendaraan h on a.kendaraan_id=h.id
		 left join gbm_organisasi i on g.parent_id=i.id
         where a.item_id=" . $item_id . " and  a.gudang_id=" . $gudang_id . "
          and a.tanggal >= '" . $tanggal_mulai . "'   and a.tanggal <= '" . $tanggal_akhir . "'
          order by a.tanggal,a.tanggal_proses  ;
          ";


			$results  = $this->db->query($queryTransaksi)->result_array();

			$data['transaksi'] = $results;


			//$this->set_response(array("status" => "OK", "data" => $data), REST_Controller::HTTP_OK);
			$html = $html . '
			<h3>Kategori     : '. $item['nama'] . ' - ' . $item['nama'] . '</h3>';
			$html = $html . '  <table  border="1" width="100%" style="border-collapse: collapse;" >
			<thead>
			<tr>
                    <th width="4%" rowspan="2">No.</th>
                    <th rowspan="2">Keterangan</th>                 
					<th rowspan="2">Kegiatan</th>
					<th rowspan="2">Blok/Mesin</th>
					<th rowspan="2">Kendaraan/AB/Mesin</th>
					<th rowspan="2">No Bukti</th>
                    <th rowspan="2">Tgl</th>
                    <th colspan="2" style="text-align: center;">Transaksi</th>
                    <th  rowspan="2" style="text-align: center;">Stok Akhir</th>
                   
                </tr>
				<tr>

                    <th style="text-align: center;">masuk</th>
                    <th style="text-align: center;">keluar</th>
					
                   

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

                    <td style="text-align: right;">

                        ' . $this->format_number_report($data['saldo_awal'],2) . '

                    </td>

                </tr>';


			$total_saldo = $data['saldo_awal'];
			$jummasuk = 0;
			$jumkeluar = 0;
			$no = 0;
			foreach ($data['transaksi'] as $key => $m) {

				//$keterangan='Blok/Stasiun'.$m['nama_blok'].', Kegiatan'.$m['nama_kegiatan'].', Kendaraan/Mesin:'.$m['nama_mesin'];
				$jummasuk = $jummasuk + $m['qty_masuk'];

				$jumkeluar = $jumkeluar + $m['qty_keluar'];
				$total_saldo = $total_saldo + $m['qty_masuk'] - $m['qty_keluar'];

				$no++;
				$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative; text-align: center;">
							' . ($no) . '

						</td>
						<td>
						' . $m['ket'] . ' 
						
						</td>
						<td>
						' . $m['nama_kegiatan'] . ' 
						
						</td>
						<td>
						' . $m['nama_afdeling'] . '-' . $m['nama_blok'] . ' 
						
						</td>
						<td>
						' . $m['nama_kendaraan'] . ' 
						
						</td>
						<td>
						' . $m['no_bukti'] . ' 
						
						</td>
						<td style="text-align: center;">
							' . tgl_indo_normal($m['tgl']) . ' 
						</td>';

				if ($m['qty_masuk'] > 0) {

					$html = $html . '<td style="text-align: right;">' . $this->format_number_report($m['qty_masuk'],2) . '</td>';
				} else {
					$html = $html . '<td style="text-align: right;">-</td>';
				}
				if ($m['qty_keluar'] > 0) {

					$html = $html . '<td style="text-align: right;">' . $this->format_number_report($m['qty_keluar'],2) . '</td>';
				} else {
					$html = $html . '<td style="text-align: right;">-</td>';
				}


				$html = $html . '<td style="text-align: right;">' . $this->format_number_report($total_saldo,2) . '</td>';

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
                       ' . $this->format_number_report($jummasuk,2) . '

                    </td>
                    <td style="text-align: right;">
                        ' . $this->format_number_report($jumkeluar,2) . '

                    </td>
                    <td>
                        &nbsp;
                    </td>
                </tr>
				</tbody>
				</table>
				<hr>';
		}
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


	function laporan_pemakaian_harga_post()
	{
		$format_laporan =  $this->post('format_laporan', true);

		$gudang_id     = $this->post('gudang_id', true);
		$kategori_id    = $this->post('kategori_id', true);
		$tanggal_mulai =  $this->post('tgl_mulai', true);
		$tanggal_akhir =  $this->post('tgl_akhir', true);
		$tampil_stok_nol =  $this->post('tampil_stok_nol', true);
		$judulKategori = 'Semua';
		$kategori = array();
		$judulGudang = 'Semua';
		$gudang = array();
		$gudang   = $this->db->query("select * from gbm_organisasi where id=" . $gudang_id . "")->row_array();
		$judulGudang = $gudang['nama'];
		$q = "SELECT a.*,b.kode as kode_item,b.nama as nama_item,c.kode as uom,d.kode AS kode_blok,d.nama AS nama_blok, e.kode AS kode_afdeling,e.nama AS nama_afdeling,
		f.kode AS kode_kegiatan,f.nama AS nama_kegiatan,h.nama as nama_kategori ,c.nama as uom,i.nama as nama_kendaraan 
		 FROM inv_transaksi_harian a 
		INNER JOIN inv_item b ON a.item_id=b.id
		INNER JOIN gbm_uom c ON b.uom_id=c.id
		left JOIN gbm_organisasi d ON a.blok_stasiun_id=d.id
		left JOIN gbm_organisasi e ON d.parent_id=e.id
		left JOIN acc_kegiatan f ON a.kegiatan_id=f.id
		left JOIN gbm_organisasi g ON g.id=a.gudang_id
		left JOIN inv_kategori h on b.inv_kategori_id=h.id
		left JOIN trk_kendaraan i on a.kendaraan_id=i.id
		where a.tipe in('PEMAKAIAN','PEMAKAIAN_BKM') and  a.gudang_id='" . $gudang_id . "' 
			 and a.tanggal>='" . $tanggal_mulai . "' and a.tanggal<='" . $tanggal_akhir . "'";
		if (!empty($kategori_id)) {
			if (($kategori_id != 'Semua')) {
				$q = $q . " and h.id=" . $kategori_id . "";
				$kategori   = $this->db->query("select * from inv_kategori where id=" . $kategori_id . "")->row_array();
				$judulKategori = $kategori['nama'];
			}
		}
		$q = $q . "  ORDER BY a.tanggal ;";


		$res   = $this->db->query($q)->result_array();

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
	<div class="kop-print">
	  <div class="kop-nama">KLINIK ANNAJAH</div>
	  <div class="kop-info"> Jl Sukarno Hatta No 12 No 12, Bandung Jawa Barat 14450</div>
	  <div class="kop-info">Telp : (021) 6684055</div>
	</div>
	  <hr class="kop-print-hr">
  </div>
  </div>
  <h3 class="title">LAPORAN PEMAKAIAN (HARGA)</h3>
  <table class="no_border" style="width:30%">
			<tr>
					<td>Periode</td>
					<td>:</td>
					<td>'. $tanggal_mulai . ' - ' . $tanggal_akhir .  '</td>
			</tr>
			<tr>
					<td>Gudang</td>
					<td>:</td>
					<td>' . $judulGudang . '</td>
			</tr>
			<tr>	
					<td>Kategori</td>
					<td>:</td>
					<td>' . $judulKategori  .  '</td>
			</tr>
			
	</table>
			<br> ';

		$html = $html .  ' <table  border="1" width="100%" style="border-collapse: collapse;" >
			<thead>
				<tr>
				<th width="4%">No.</th>
				<th>Kode</th>
				<th>Nama</th>
				<th>Kategori</th>
				<th>Satuan </th>
				<th style="text-align: center;">qty</th>
				<th style="text-align: center;">Harga</th>
				<th style="text-align: center;">Jumlah</th>
				<th style="text-align: center;">Blok/Stasiun</th>
				<th style="text-align: center;">Kendaraan </th>
				<th style="text-align: center;">Kegiatan</th>
				
				</tr>
			</thead>
			<tbody>';


		$no = 0;
		$jumlah = 0;
		$sub_total = 0;
		$total = 0;


		foreach ($res as $key => $m) {
			$no++;
			$jumlah = $m['qty_keluar'] + $m['nilai_keluar'];
			$sub_total = $sub_total + $jumlah;;
			$total = $total + $jumlah;;
			$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative; text-align: center;">
							' . ($no) . '

						</td>
						<td style="text-align: center;">
						' . $m['kode_item'] . ' 
						
						</td>
						<td>
						' . $m['nama_item'] . ' 
						
						</td>
						<td>
							' . $m['nama_kategori'] . ' 
						</td>
						
						<td style="text-align: center;">
						' . $m['uom'] . ' 
							
						</td>
					
						<td style="text-align: right;">' . $this->format_number_report($m['qty_keluar'],2) . ' </td>
						<td style="text-align: right;">' . $this->format_number_report($m['nilai_keluar'] / $m['qty_keluar']) . ' </td>
						<td style="text-align: right;">' . $this->format_number_report($m['nilai_keluar']) . ' </td>
						<td style="text-align: center;">' . $m['kode_blok'] . '-' . $m['nama_blok'] . ' </td>
						<td style="text-align: left;">' . ($m['nama_kendaraan']) . ' 	</td>
						<td style="text-align: left;">' . ($m['nama_kegiatan']) . ' 	</td>
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
						<td style="text-align: right;">
							
						</td>
												
						<td style="text-align: right;">' . $this->format_number_report($total,2) . ' </td>

						<td style="text-align: right;">
							
						</td>
						<td style="text-align: right;">
							
						</td>
						<td style="text-align: right;">
							
						</td>
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

	function laporan_rekap_stok_harga_post()
	{
		$format_laporan =  $this->post('format_laporan', true);

		$gudang_id     = $this->post('gudang_id', true);
		$kategori_id    = $this->post('kategori_id', true);
		$tanggal_mulai =  $this->post('tgl_mulai', true);
		$tanggal_akhir =  $this->post('tgl_akhir', true);
		$tampil_stok_nol =  $this->post('tampil_stok_nol', true);
		$judulKategori = 'Semua';
		$kategori = array();
		$judulGudang = 'Semua';
		$gudang = array();
		$gudang   = $this->db->query("select * from gbm_organisasi where id=" . $gudang_id . "")->row_array();
		$judulGudang = $gudang['nama'];
		$queryItem = "select a.*, b.nama as nama_kategori,c.kode as uom
       from inv_item a left join inv_kategori b on a.inv_kategori_id=b.id
	   left join gbm_uom c on a.uom_id=c.id
       where 1=1 ";
		if (!empty($kategori_id)) {
			if (($kategori_id != 'Semua')) {
				$queryItem = $queryItem . " and b.id=" . $kategori_id . "";
				$kategori   = $this->db->query("select * from inv_kategori where id=" . $kategori_id . "")->row_array();
				$judulKategori = $kategori['nama'];
			}
		}
		$res = array();
		$items   = $this->db->query($queryItem)->result_array();
		foreach ($items as $key => $item) {
			$querySaldoAwal = "SELECT a.gudang_id,a.item_id,
			sum(qty_masuk-qty_keluar) as stok ,SUM(nilai_masuk-nilai_keluar)AS nilai FROM inv_transaksi_harian a
			inner join inv_item c on a.item_id=c.id
			inner join gbm_organisasi d on a.gudang_id=d.id
			left join gbm_uom e on c.uom_id=e.id
           where a.item_id=" . $item['id'] . " and  a.tanggal < '" . $tanggal_mulai . "'
           and a.gudang_id=" . $gudang_id . "
            group by  a.gudang_id,a.item_id";

			$awal   = $this->db->query($querySaldoAwal)->row_array();
			$item['saldo_awal'] = (!empty($awal)) ? $awal['stok'] : 0;
			$item['nilai_awal'] = (!empty($awal)) ? $awal['nilai'] : 0;

			$queryMasuk = "SELECT a.gudang_id,a.item_id,
			sum(qty_masuk) as stok,SUM(nilai_masuk)AS nilai FROM inv_transaksi_harian a
			inner join inv_item c on a.item_id=c.id
			inner join gbm_organisasi d on a.gudang_id=d.id
			left join gbm_uom e on c.uom_id=e.id
            where a.item_id=" . $item['id'] . " and  a.tanggal >= '" . $tanggal_mulai . "'
            and  a.tanggal <= '" . $tanggal_akhir . "' 
            and a.gudang_id=" . $gudang_id . "
             group by  a.gudang_id,a.item_id";

			$masuk   = $this->db->query($queryMasuk)->row_array();
			$item['masuk'] = (!empty($masuk['stok'])) ? $masuk['stok'] : 0;
			$item['nilai_masuk'] = (!empty($masuk)) ? $masuk['nilai'] : 0;

			$queryKeluar = "SELECT a.gudang_id,a.item_id,
			sum(qty_keluar) as stok,SUM(nilai_keluar)AS nilai FROM inv_transaksi_harian a
			inner join inv_item c on a.item_id=c.id
			inner join gbm_organisasi d on a.gudang_id=d.id
			left join gbm_uom e on c.uom_id=e.id
             where a.item_id=" . $item['id'] . " and  a.tanggal >= '" . $tanggal_mulai . "'
            and  a.tanggal <= '" . $tanggal_akhir . "'  
            and a.gudang_id=" . $gudang_id . "
            group by  a.gudang_id,a.item_id";
			$keluar   = $this->db->query($queryKeluar)->row_array();
			$item['keluar'] = (!empty($keluar['stok'])) ? $keluar['stok'] : 0;
			$item['nilai_keluar'] = (!empty($keluar)) ? $keluar['nilai'] : 0;

			$stok = $item['saldo_awal'] + $item['masuk'] - $item['keluar'];

			/* cari harga */
			//$query_harga = "select * from inv_item_hpp where tanggal <='" . $tanggal_akhir . "' and item_id=" . $item['id'] . " and  gudang_id=" . $gudang_id . " order by tanggal desc limit 1";
			$query_harga = "SELECT  SUM(qty_masuk-qty_keluar)AS qty,SUM(nilai_masuk-nilai_keluar)AS nilai from inv_transaksi_harian
			where item_id=" . $item['id'] . " and gudang_id=" . $gudang_id . "  and tanggal <='" . $tanggal_akhir . "' ";
			$res_harga   = $this->db->query($query_harga)->row_array();
			if (!empty($res_harga['qty'])) {
				if ($res_harga['qty'] <= 0 || $res_harga['nilai'] <= 0) {
					$item['harga'] = 0;
				} else {
					$item['harga'] = ($res_harga['nilai'] / $res_harga['qty']);
				}
			} else {

				$item['harga'] = 0;
			}

			if ($tampil_stok_nol == true) {
				$res[] = $item;
			} else {
				if ($stok > 0) {
					$res[] = $item;
				}
			}
		}

		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report();
			$html='
				<style>
				* body{
					font-size: 12px ;
				}
				
				.table-bg th,
				.table-bg td {
				border: 0.3px solid rgba(0, 0, 0, 0.4);
				padding: 5px 8px;
				}
				</style>';
		} else {
			$html = get_header_report_v2();
		}

		$html = $html . '
		<div class="row">
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
		<h3 class="title">LAPORAN REKAP STOK (HARGA)</h3>
		<table class="no_border" style="width:30%">
				  <tr>
						  <td>Periode</td>
						  <td>:</td>
						  <td>'. tgl_indo($tanggal_mulai) . ' - ' . tgl_indo($tanggal_akhir) .  '</td>
				  </tr>
				  <tr>
						  <td>Gudang</td>
						  <td>:</td>
						  <td>' . $judulGudang  . '</td>
				  </tr>
				  <tr>	
						  <td>Kategori</td>
						  <td>:</td>
						  <td>' . $judulKategori  .  '</td>
				  </tr>
				  
		  </table>
				  <br>';

		$html = $html .  ' <table  border="1" width="100%" style="border-collapse: collapse;" >
			<thead>
				<tr>
				<th width="4%">No.</th>
				<th>Kode</th>
				<th>Nama</th>
				<th>Kategori</th>
				<th>Satuan </th>
				<th style="text-align: center;">Stok awal</th>
				<th style="text-align: center;">Nilai awal</th>
				<th style="text-align: center;">Masuk</th>
				<th style="text-align: center;">Nilai Masuk</th>
				<th style="text-align: center;">Keluar</th>
				<th style="text-align: center;">Nilai Keluar</th>
				<th style="text-align: center;">Stok Akhir</th>
				<th style="text-align: center;">Harga </th>
				<th style="text-align: center;">Nilai Stok</th>
				
				</tr>
			</thead>
			<tbody>';


		$no = 0;
		$jumlah = 0;
		$total_nilai = 0;
		$total_nilai_awal = 0;
		$total_nilai_masuk = 0;
		$total_nilai_keluar = 0;

		foreach ($res as $key => $m) {
			$no++;
			$jumlah = $m['saldo_awal'] + $m['masuk'] - $m['keluar'];
			$nilai = $jumlah * $m['harga'];
			$total_nilai = $total_nilai + $nilai;
			$total_nilai_awal = $total_nilai_awal + $m['nilai_awal'];
			$total_nilai_masuk = $total_nilai_masuk + $m['nilai_masuk'];
			$total_nilai_keluar = $total_nilai_keluar + $m['nilai_keluar'];
			$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative; text-align: center;">
							' . ($no) . '

						</td>
						<td style="text-align: center;">
						' . $m['kode'] . ' 
						
						</td>
						<td>
						' . $m['nama'] . ' 
						
						</td>
						<td>
							' . $m['nama_kategori'] . ' 
						</td>
						
						<td style="text-align: center;">
						' . $m['uom'] . ' 
							
						</td>
					
						<td style="text-align: right;">' . $this->format_number_report($m['saldo_awal'],2) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['nilai_awal']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['masuk'],2) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['nilai_masuk']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['keluar'],2) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['nilai_keluar']) . ' 
						<td style="text-align: right;">' . $this->format_number_report($jumlah,2) . ' 
						<td style="text-align: right;">' . $this->format_number_report($m['harga'],2) . ' 	
						<td style="text-align: right;">' . $this->format_number_report($nilai) . ' 	
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


						<td style="text-align: right;">
							
						</td>

						<td>
							&nbsp;
						</td>
						<td style="text-align: right;">' . $this->format_number_report($total_nilai_awal) . ' </td>
						<td>
							&nbsp;
						</td>
						<td style="text-align: right;">' . $this->format_number_report($total_nilai_masuk) . ' </td>

						<td style="text-align: right;">
							
						</td>
						<td style="text-align: right;">' . $this->format_number_report($total_nilai_keluar) . ' </td>

						<td style="text-align: right;">
							
						</td>
						<td style="text-align: right;">
							
						</td>
						
						<td style="text-align: right;">' . $this->format_number_report($total_nilai) . ' </td>

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

	function laporan_kartu_stok_harga_post()
	{
		$format_laporan =  $this->post('format_laporan', true);
		$gudang_id = $this->post('gudang_id', true);
		$item_id = $this->post('item_id', true);
		$tanggal_mulai =  $this->post('tgl_mulai', true);
		$tanggal_akhir = $this->post('tgl_akhir', true);

		$querySaldoAwal = "  SELECT a.gudang_id,a.item_id,
        sum(qty_masuk-qty_keluar) as qty,sum(nilai_masuk-nilai_keluar)AS nilai FROM inv_transaksi_harian a
        inner join inv_item c on a.item_id=c.id
        inner join gbm_organisasi d on a.gudang_id=d.id
		left join gbm_uom e on c.uom_id=e.id
        where a.item_id=" . $item_id . " and  a.gudang_id=" . $gudang_id . " and a.tanggal < '" . $tanggal_mulai . "'
         group by  a.gudang_id,a.item_id ;";


		$saldoAwal    = $this->db->query($querySaldoAwal)->row_array();
		$data['saldo_awal'] = (!empty($saldoAwal)) ? $saldoAwal['qty'] : 0;
		if (!empty($saldoAwal['qty'])) {
			if ($saldoAwal['qty'] <= 0 || $saldoAwal['nilai'] <= 0) {
				$data['nilai_awal'] = 0;
			} else {
				$data['nilai_awal'] = ($saldoAwal['nilai']);
			}
		} else {

			$data['nilai_awal'] = 0;
		}


		$queryTransaksi   = "SELECT a.gudang_id,d.nama as gudang,a.tanggal as tgl,a.no_transaksi as no_bukti,a.tipe as ket,c.kode, c.nama,e.kode as satuan,
        a.qty_masuk,a.qty_keluar,a.nilai_masuk,a.nilai_keluar,f.nama as nama_kegiatan,g.kode as kode_blok,g.nama as nama_blok,h.nama as nama_kendaraan,i.nama as nama_afdeling
		 FROM inv_transaksi_harian a 
         inner join inv_item c on a.item_id=c.id
         inner join  gbm_organisasi d on a.gudang_id=d.id
		 left join gbm_uom e on c.uom_id=e.id
		 left join acc_kegiatan f on a.kegiatan_id=f.id
		 left join gbm_organisasi g on a.blok_stasiun_id=g.id
		 left join trk_kendaraan h on a.kendaraan_id=h.id
		 left join gbm_organisasi i on g.parent_id=i.id
         where a.item_id=" . $item_id . " and  a.gudang_id=" . $gudang_id . "
          and a.tanggal >= '" . $tanggal_mulai . "'   and a.tanggal <= '" . $tanggal_akhir . "'
          order by a.tanggal,a.tanggal_proses  ;
          ";


		$results  = $this->db->query($queryTransaksi)->result_array();
		$data['transaksi'] = $results;

		$item = $this->InvItemModel->retrieve($item_id);
		$gudang = $this->GbmOrganisasiModel->retrieve($gudang_id);

		//$this->set_response(array("status" => "OK", "data" => $data), REST_Controller::HTTP_OK);
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report();
			$html='
				<style>
				* body{
					font-size: 10px ;
				}
				
				.table-bg th,
				.table-bg td {
				border: 0.3px solid rgba(0, 0, 0, 0.4);
				padding: 5px 8px;
				}
				</style>';
		} else {
			$html = get_header_report_v2();
		}

		$html = $html . '
		<div class="row">
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
		<h3 class="title">LAPORAN KARTU STOK (HARGA)</h3>
		<table class="no_border" style="width:30%">
				  <tr>
						  <td>Periode</td>
						  <td>:</td>
						  <td>'. tgl_indo($tanggal_mulai) . ' - ' . tgl_indo($tanggal_akhir) .  '</td>
				  </tr>
				  <tr>
						  <td>Gudang</td>
						  <td>:</td>
						  <td>' . $gudang['nama']  . '</td>
				  </tr>
				  <tr>	
						  <td>Item</td>
						  <td>:</td>
						  <td>' . $item['kode'] . ' - ' . $item['nama']  .  '</td>
				  </tr>
				  
		  </table>
				  <br>';

		$html = $html . '  <table  border="1" width="100%" style="border-collapse: collapse;" >
			<thead>
			<tr>
                    <th width="4%" rowspan="2">No.</th>
                    <th rowspan="2">Keterangan</th>                 
					<th rowspan="2">Kegiatan</th>
					<th rowspan="2">Blok/Mesin</th>
					<th rowspan="2">Kendaraan/AB/Mesin</th>
					<th rowspan="2">No Bukti</th>
                    <th rowspan="2">Tgl</th>
                    <th colspan="4" style="text-align: center;">Transaksi</th>
                    <th  rowspan="2" style="text-align: center;">Stok Akhir</th>
					<th  rowspan="2" style="text-align: center;">Nilai Akhir</th>
                   
                </tr>
				<tr>

                    <th style="text-align: center;">Qty Masuk</th>
                    <th style="text-align: center;">Nilai Masuk</th>
					<th style="text-align: center;">Qty Keluar</th>
                    <th style="text-align: center;">Nilai Keluar</th>
					
                   

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

                        ' . $this->format_number_report($data['saldo_awal'],2) . '

                    </td>
					<td style="text-align: right;">

					' . $this->format_number_report($data['nilai_awal']) . '

				</td>

                </tr>';


		$total_saldo = $data['saldo_awal'];
		$total_nilai_saldo = $data['nilai_awal'];
		$jummasuk = 0;
		$jumkeluar = 0;
		$nilaimasuk = 0;
		$nilaikeluar = 0;
		$no = 0;
		foreach ($data['transaksi'] as $key => $m) {

			//$keterangan='Blok/Stasiun'.$m['nama_blok'].', Kegiatan'.$m['nama_kegiatan'].', Kendaraan/Mesin:'.$m['nama_mesin'];
			$jummasuk = $jummasuk + $m['qty_masuk'];
			$nilaimasuk = $nilaimasuk + $m['nilai_masuk'];
			$jumkeluar = $jumkeluar + $m['qty_keluar'];
			$nilaikeluar = $nilaikeluar + $m['nilai_keluar'];
			$total_saldo = $total_saldo + $m['qty_masuk'] - $m['qty_keluar'];
			$total_nilai_saldo = $total_nilai_saldo + $m['nilai_masuk'] - $m['nilai_keluar'];
			$no++;
			$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative; text-align: center;">
							' . ($no) . '

						</td>
						<td>
						' . $m['ket'] . ' 
						
						</td>
						<td>
						' . $m['nama_kegiatan'] . ' 
						
						</td>
						<td>
						' . $m['nama_afdeling'] . '-' . $m['nama_blok'] . ' 
						
						</td>
						<td>
						' . $m['nama_kendaraan'] . ' 
						
						</td>
						<td>
						' . $m['no_bukti'] . ' 
						
						</td>
						<td style="text-align: center;">
							' . tgl_indo_normal($m['tgl']) . ' 
						</td>';

			if ($m['qty_masuk'] > 0) {

				$html = $html . '<td style="text-align: right;">' . $this->format_number_report($m['qty_masuk'],2) . '</td>';
				$html = $html . '<td style="text-align: right;">' . $this->format_number_report($m['nilai_masuk']) . '</td>';
			} else {
				$html = $html . '<td style="text-align: right;">-</td>';
				$html = $html . '<td style="text-align: right;">-</td>';
			}
			if ($m['qty_keluar'] > 0) {

				$html = $html . '<td style="text-align: right;">' . $this->format_number_report($m['qty_keluar'],2) . '</td>';
				$html = $html . '<td style="text-align: right;">' . $this->format_number_report($m['nilai_keluar']) . '</td>';
			} else {
				$html = $html . '<td style="text-align: right;">-</td>';
				$html = $html . '<td style="text-align: right;">-</td>';
			}


			$html = $html . '<td style="text-align: right;">' . $this->format_number_report($total_saldo,2) . '</td>';
			$html = $html . '<td style="text-align: right;">' . $this->format_number_report($total_nilai_saldo) . '</td>';
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
                       ' . $this->format_number_report($jummasuk,2) . '

                    </td>
					<td style="text-align: right;">
					' . $this->format_number_report($nilaimasuk) . '

				 	</td>
                    <td style="text-align: right;">
                        ' . $this->format_number_report($jumkeluar,2) . '

                    </td>
					<td style="text-align: right;">
					' . $this->format_number_report($nilaikeluar) . '

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



	function get_logo_url($size = 'small')
	{
		return base_url('assets/images/logo-' . strtolower($size) . '.png');
	}


	function get_logo_config()
	{
		$config = get_pengaturan('logo-company', 'value');
		if (empty($config)) {
			return get_logo_url('medium');
		} else {
			return get_url_image($config);
		}
	}
	function format_number_report($angka, $digit = 0)
	{

		$format_laporan     = $this->post('format_laporan', true);
		// if ($fmt_laporan ) {
		// 	$format_laporan     = $this->post('format_laporan', true);
		// }else{
		// 	return $this->format_number_report($angka);
		// }
		if ($format_laporan == 'xls') {
			return $angka;
		} else {
			if ($angka == 0) {
				return '-';
			}

			return number_format($angka, $digit);
		}
	}
}
