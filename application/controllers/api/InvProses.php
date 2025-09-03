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

class InvProses extends Rest_Controller
{
	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('InvItemModel');
		$this->load->model('GbmOrganisasiModel');
		$this->load->model('M_DatatablesModel');
		$this->load->library('pdfgenerator');
	}

	public function proses_hitung_hpp_post($gudang_id)
	{
		$queryItem = "select a.*, b.nama as nama_kategori,c.kode as uom
		from inv_item a left join inv_kategori b on a.inv_kategori_id=b.id
		left join gbm_uom c on a.uom_id=c.id
		where a.id=9 ";

		$items   = $this->db->query($queryItem)->result_array();
		foreach ($items as $key => $item) {

			$queryTransaksi   = "SELECT a.gudang_id,d.nama as gudang,a.tanggal,a.no_transaksi as no_bukti,a.tipe as ket,c.kode, c.nama,e.kode as satuan,
			a.qty_masuk,a.qty_keluar,a.nilai_masuk,a.nilai_keluar,f.nama as nama_kegiatan,g.kode as kode_blok,g.nama as nama_blok,h.nama as nama_kendaraan,i.nama as nama_afdeling
			FROM inv_transaksi_harian a 
			inner join inv_item c on a.item_id=c.id
			inner join  gbm_organisasi d on a.gudang_id=d.id
			left join gbm_uom e on c.uom_id=e.id
			left join acc_kegiatan f on a.kegiatan_id=f.id
			left join gbm_organisasi g on a.blok_stasiun_id=g.id
			left join trk_kendaraan h on a.kendaraan_id=h.id
			left join gbm_organisasi i on g.parent_id=i.id
			where a.item_id=" . $item['id'] . " and  a.gudang_id=" . $gudang_id . "			
			order by a.tanggal,a.tanggal_proses  ; ";
			$res_transaksi   = $this->db->query($queryTransaksi)->result_array();
			$jummasuk = 0;
			$jumkeluar = 0;
			$nilaimasuk = 0;
			$nilaikeluar = 0;
			$total_nilai_saldo = 0;
			$total_saldo = 0;
			$no = 0;
			foreach ($res_transaksi as $key => $m) {
				$jummasuk = $jummasuk + $m['qty_masuk'];
				$nilaimasuk = $nilaimasuk + $m['nilai_masuk'];
				$jumkeluar = $jumkeluar + $m['qty_keluar'];
				$nilaikeluar = $nilaikeluar + $m['nilai_keluar'];
				$total_saldo = $total_saldo + $m['qty_masuk'] - $m['qty_keluar'];
				$total_nilai_saldo = $total_nilai_saldo + $m['nilai_masuk'] - $m['nilai_keluar'];

				$queryHpp="select * from inv_item_hpp  where gudang_id=" . $gudang_id . "
				and item_id=" . $item['id'] . " and tanggal<='" . $m['tanggal'] . "'
				order by tanggal desc LIMIT 1";
				$item_hpp = $this->db->query($queryHpp)->row_array();
				
				if ($item_hpp) {
					if ($item_hpp['tanggal'] != $m['tanggal']) {
						$this->db->insert("inv_item_hpp", array(
							'item_id' => $item['id'],
							'gudang_id' => $gudang_id,
							'qty' => 0,
							'nilai' =>  0,
							'qty_masuk' => 0,
							'nilai_masuk' => 0,
							'harga_hpp' => $total_nilai_saldo / $total_saldo,
							'tanggal' => $m['tanggal'],
							'tanggal_proses' => date('Y-m-d H:i:s')
						));
					} else {
						$this->db->where('item_id', $item['id']);
						$this->db->where('gudang_id', $gudang_id);
						$this->db->where('tanggal', $m['tanggal']);
						$this->db->update("inv_item_hpp", array(
							'item_id' => $item['id'],
							'gudang_id' => $gudang_id,
							'qty' => 0,
							'nilai' => 0,
							'qty_masuk' => 0,
							'nilai_masuk' => 0,
							'harga_hpp' => $total_nilai_saldo / $total_saldo,
							'tanggal' => $m['tanggal'],
							'tanggal_proses' => date('Y-m-d H:i:s')

						));
					}
				} else {
					$this->db->insert("inv_item_hpp", array(
						'item_id' => $item['id'],
						'gudang_id' => $gudang_id,
						'qty' => 0,
						'nilai' => 0,
						'qty_masuk' => 0,
						'nilai_masuk' => 0,
						'harga_hpp' => $total_nilai_saldo / $total_saldo,
						'tanggal' => $m['tanggal'],
						'tanggal_proses' => date('Y-m-d H:i:s')
					));
				}
			}
			$item_dt = $this->db->query("select * from inv_item_dt  where gudang_id=" . $gudang_id . "
			and item_id=" . $item['id'] . "")->row_array();

			if ($item_dt) {
				$this->db->where('item_id', $item['id']);
				$this->db->where('gudang_id', $gudang_id);
				$this->db->update("inv_item_dt", array(
					'item_id' => $item['id'],
					'gudang_id' => $gudang_id,
					'qty' => $total_saldo,
					'nilai' => ($total_nilai_saldo)
				));
			} else {
				$this->db->insert("inv_item_dt", array(
					'item_id' => $item['id'],
					'gudang_id' => $gudang_id,
					'qty' => $total_saldo,
					'nilai' => ($total_nilai_saldo)
				));
			}
		}
		$this->set_response(array("status" => "OK", "data" => $res_transaksi), REST_Controller::HTTP_CREATED);
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
		$html = $html = get_header_report();

		$html = $html . '<div class="row">
  <div class="span12">
	  <br>
	  <div class="kop-print">
		 	  </div>
	  <hr class="kop-print-hr">
  </div>
  </div>
  <h2>Laporan Rekap Stok</h2>
  <h3>Periode  : ' . $tanggal_mulai . ' - ' . $tanggal_akhir . '</h3>
  <h3>Gudang  : ' . $judulGudang . '</h3>
  <h3>Kategori :' . $judulKategori . '</h3>';

		$html = $html .  ' <table  border="1" width="100%" style="border-collapse: collapse;" >
			<thead>
				<tr>
				<th width="4%">No.</th>
				<th>Kode</th>
				<th>Nama</th>
				<th>Kategori</th>
				<th>Satuan </th>
				<th style="text-align: right;">Stok awal</th>
				<th style="text-align: right;">Masuk</th>
				<th style="text-align: right;">Keluar</th>
				<th style="text-align: right;">Stok Akhir</th>
				<th style="text-align: right;">Min Stok</th>
				<th style="text-align: right;">Selisih Min Stok</th>
				
				</tr>
			</thead>
			<tbody>';


		$no = 0;
		$jumlah = 0;



		foreach ($res as $key => $m) {
			$no++;
			$jumlah = $m['saldo_awal'] + $m['masuk'] - $m['keluar'];
			$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative;">
							' . ($no) . '

						</td>
						<td>
						' . $m['kode'] . ' 
						
						</td>
						<td>
						' . $m['nama'] . ' 
						
						</td>
						<td>
							' . $m['nama_kategori'] . ' 
						</td>
						
						<td>
						' . $m['uom'] . ' 
							
						</td>
					
						<td style="text-align: right;">' . number_format($m['saldo_awal']) . ' 
						<td style="text-align: right;">' . number_format($m['masuk']) . ' 
						<td style="text-align: right;">' . number_format($m['keluar']) . ' 
						<td style="text-align: right;">' . number_format($jumlah) . ' 
						<td style="text-align: right;">' . number_format($m['min_stok']) . ' 	
						<td style="text-align: right;">' . number_format($jumlah - $m['min_stok']) . ' 	
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
		$html = 	$html = $html = get_header_report();

		$html = $html . '<div class="row">
  <div class="span12">
	  <br>
	  <div class="kop-print">
		 </div>
	  <hr class="kop-print-hr">
  </div>
  </div>
  <h2>Laporan Kartu Stok</h2>
  <h3>Periode  : ' . $tanggal_mulai . ' - ' . $tanggal_akhir . '</h3>
  <h3>Gudang   : ' . $gudang['nama'] . '</h3>
  <h3>Item     : ' . $item['kode'] . ' - ' . $item['nama'] . '</h3>';

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

                        ' . number_format($data['saldo_awal']) . '

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
						<td style="position:relative;">
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
						<td>
							' . $m['tgl'] . ' 
						</td>';

			if ($m['qty_masuk'] > 0) {

				$html = $html . '<td style="text-align: right;">' . number_format($m['qty_masuk']) . '</td>';
			} else {
				$html = $html . '<td style="text-align: right;">-</td>';
			}
			if ($m['qty_keluar'] > 0) {

				$html = $html . '<td style="text-align: right;">' . number_format($m['qty_keluar']) . '</td>';
			} else {
				$html = $html . '<td style="text-align: right;">-</td>';
			}


			$html = $html . '<td style="text-align: right;">' . number_format($total_saldo) . '</td>';

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
                       ' . number_format($jummasuk) . '

                    </td>
                    <td style="text-align: right;">
                        ' . number_format($jumkeluar) . '

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

			/* cari harga */
			$query_harga = "select * from inv_item_hpp where tanggal <='" . $tanggal_akhir . "' and item_id=" . $item['id'] . " and  gudang_id=" . $gudang_id . " order by tanggal desc limit 1";
			$res_harga   = $this->db->query($query_harga)->row_array();
			$item['harga'] = (!empty($res_harga['harga_hpp'])) ? $res_harga['harga_hpp'] : 0;

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
		$html = $html = get_header_report();

		$html = $html . '<div class="row">
  <div class="span12">
	  <br>
	  <div class="kop-print">
		 	  </div>
	  <hr class="kop-print-hr">
  </div>
  </div>
  <h2>Laporan Rekap Stok</h2>
  <h3>Periode  : ' . $tanggal_mulai . ' - ' . $tanggal_akhir . '</h3>
  <h3>Gudang  : ' . $judulGudang . '</h3>
  <h3>Kategori :' . $judulKategori . '</h3>';

		$html = $html .  ' <table  border="1" width="100%" style="border-collapse: collapse;" >
			<thead>
				<tr>
				<th width="4%">No.</th>
				<th>Kode</th>
				<th>Nama</th>
				<th>Kategori</th>
				<th>Satuan </th>
				<th style="text-align: right;">Stok awal</th>
				<th style="text-align: right;">Masuk</th>
				<th style="text-align: right;">Keluar</th>
				<th style="text-align: right;">Stok Akhir</th>
				<th style="text-align: right;">Harga </th>
				<th style="text-align: right;">Nilai Stok</th>
				
				</tr>
			</thead>
			<tbody>';


		$no = 0;
		$jumlah = 0;
		$total_nilai = 0;


		foreach ($res as $key => $m) {
			$no++;
			$jumlah = $m['saldo_awal'] + $m['masuk'] - $m['keluar'];
			$nilai = $jumlah * $m['harga'];
			$total_nilai = $total_nilai + $nilai;
			$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative;">
							' . ($no) . '

						</td>
						<td>
						' . $m['kode'] . ' 
						
						</td>
						<td>
						' . $m['nama'] . ' 
						
						</td>
						<td>
							' . $m['nama_kategori'] . ' 
						</td>
						
						<td>
						' . $m['uom'] . ' 
							
						</td>
					
						<td style="text-align: right;">' . number_format($m['saldo_awal']) . ' 
						<td style="text-align: right;">' . number_format($m['masuk']) . ' 
						<td style="text-align: right;">' . number_format($m['keluar']) . ' 
						<td style="text-align: right;">' . number_format($jumlah) . ' 
						<td style="text-align: right;">' . number_format($m['harga']) . ' 	
						<td style="text-align: right;">' . number_format($nilai) . ' 	
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
						
						<td style="text-align: right;">' . number_format($total_nilai) . ' 

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
        sum(qty_masuk-qty_keluar) as stok FROM inv_transaksi_harian a
        inner join inv_item c on a.item_id=c.id
        inner join gbm_organisasi d on a.gudang_id=d.id
		left join gbm_uom e on c.uom_id=e.id
        where a.item_id=" . $item_id . " and  a.gudang_id=" . $gudang_id . " and a.tanggal < '" . $tanggal_mulai . "'
         group by  a.gudang_id,a.item_id ;";


		$saldoAwal    = $this->db->query($querySaldoAwal)->row_array();
		$data['saldo_awal'] = (!empty($saldoAwal)) ? $saldoAwal['stok'] : 0;


		/* cari harga */
		$query_harga = "select * from inv_item_hpp where tanggal <='" . $tanggal_mulai . "' and item_id=" . $item_id . " and  gudang_id=" . $gudang_id . " order by tanggal desc limit 1";
		$res_harga   = $this->db->query($query_harga)->row_array();
		$data['nilai_awal'] = (!empty($res_harga['harga_hpp'])) ? ($res_harga['harga_hpp'] * $data['saldo_awal']) : 0;


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
		$html = 	$html = $html = get_header_report();

		$html = $html . '<div class="row">
  <div class="span12">
	  <br>
	  <div class="kop-print">
		 </div>
	  <hr class="kop-print-hr">
  </div>
  </div>
  <h2>Laporan Kartu Stok</h2>
  <h3>Periode  : ' . $tanggal_mulai . ' - ' . $tanggal_akhir . '</h3>
  <h3>Gudang   : ' . $gudang['nama'] . '</h3>
  <h3>Item     : ' . $item['kode'] . ' - ' . $item['nama'] . '</h3>';

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

                        ' . number_format($data['saldo_awal']) . '

                    </td>
					<td style="text-align: right;">

					' . number_format($data['nilai_awal']) . '

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
						<td style="position:relative;">
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
						<td>
							' . $m['tgl'] . ' 
						</td>';

			if ($m['qty_masuk'] > 0) {

				$html = $html . '<td style="text-align: right;">' . number_format($m['qty_masuk']) . '</td>';
				$html = $html . '<td style="text-align: right;">' . number_format($m['nilai_masuk']) . '</td>';
			} else {
				$html = $html . '<td style="text-align: right;">-</td>';
				$html = $html . '<td style="text-align: right;">-</td>';
			}
			if ($m['qty_keluar'] > 0) {

				$html = $html . '<td style="text-align: right;">' . number_format($m['qty_keluar']) . '</td>';
				$html = $html . '<td style="text-align: right;">' . number_format($m['nilai_keluar']) . '</td>';
			} else {
				$html = $html . '<td style="text-align: right;">-</td>';
				$html = $html . '<td style="text-align: right;">-</td>';
			}


			$html = $html . '<td style="text-align: right;">' . number_format($total_saldo) . '</td>';
			$html = $html . '<td style="text-align: right;">' . number_format($total_nilai_saldo) . '</td>';
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
                       ' . number_format($jummasuk) . '

                    </td>
					<td style="text-align: right;">
					' . number_format($nilaimasuk) . '

				 	</td>
                    <td style="text-align: right;">
                        ' . number_format($jumkeluar) . '

                    </td>
					<td style="text-align: right;">
					' . number_format($nilaikeluar) . '

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
}
