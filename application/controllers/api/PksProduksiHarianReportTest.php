<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

class PksStokProdukReport extends Rest_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('PksProduksiModel');
		$this->load->model('M_DatatablesModel');
		$this->load->library('pdfgenerator');
	}

	function laporan_stok_produk_post()
	{


		$mill_id     = $this->post('mill_id', true);
		$tgl_mulai =  $this->post('tgl_mulai', true);
		$tgl_akhir = $this->post('tgl_akhir', true);
		$produk_id     = $this->post('produk_id', true);

		$retrieveMill = $this->db->query("select * from gbm_organisasi where id=" . $mill_id . "")->row_array();
		$retrieveProduct = $this->db->query("select * from inv_item where id=" . $produk_id . "")->row_array();


		// var_dump($results);
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		$html = "<style type='text/css'>
		h3.title {
			margin-bottom: 0px;
			line-height: 30px;
		}
		hr.top {
			border: none;
			border-bottom: 2px solid #333;
			margin-bottom: 10px;
			margin-top: 10px;
		}
		.kop-print {
		  width: 700px;
		  margin: auto;
	  }
  
	  .kop-print img {
		  float: left;
		  height: 60px;
		  margin-right: 20px;
	  }
  
	  .kop-print .kop-info {
		  font-size: 15px;
	  }
  
	  .kop-print .kop-nama {
		  font-size: 25px;
		  font-weight: bold;
		  line-height: 35px;
	  }
  
	  .kop-print-hr {
		  border-width: 2px;
		  border-color: black;
		  margin-bottom: 0px;
	  }
	  table {
		border-collapse: separate;
		border-spacing: 0;
		color: #4a4a4d;
		font: 14px/1.4 'Helvetica Neue', Helvetica, Arial, sans-serif;
	  }
	  th,
	  td {
		padding: 10px 15px;
		vertical-align: middle;
	  }
	  thead {
		background: #395870;
		background: linear-gradient(#49708f, #293f50);
		color: #fff;
		font-size: 11px;
		text-transform: uppercase;
	  }
	  th:first-child {
		border-top-left-radius: 5px;
		text-align: left;
	  }
	  th:last-child {
		border-top-right-radius: 5px;
	  }
	  tbody tr:nth-child(even) {
		background: #f0f0f2;
	  }
	  td {
		border-bottom: 1px solid #cecfd5;
		border-right: 1px solid #cecfd5;
	  }
	  td:first-child {
		border-left: 1px solid #cecfd5;
	  }
	  .book-title {
		color: #395870;
		display: block;
	  }
	  .text-offset {
		color: #7c7c80;
		font-size: 12px;
	  }
	  .item-stock,
	  .item-qty {
		text-align: center;
	  }
	  .item-price {
		text-align: right;
	  }
	  .item-multiple {
		display: block;
	  }
	  tfoot {
		text-align: right;
	  }
	  tfoot tr:last-child {
		background: #f0f0f2;
		color: #395870;
		font-weight: bold;
	  }
	  tfoot tr:last-child td:first-child {
		border-bottom-left-radius: 5px;
	  }
	  tfoot tr:last-child td:last-child {
		border-bottom-right-radius: 5px;
	  }
	  
  
	</style>
  ";

		$html = $html . '<div class="row">
<div class="span12">
	<br>
	<div class="kop-print">
		
		<div class="kop-nama">DPA</div>
		<div class="kop-info">Alamat : - </div>
	</div>
	<hr class="kop-print-hr">
</div>
</div>
<h2>Laporan Stok Produk</h2>
<h3>Mill:' . $retrieveMill['nama'] . ' </h3>
<h4>' . $tgl_mulai . ' - ' . $tgl_akhir . ' </h4>
<h4> Produk:' . $retrieveProduct['nama'] . ' </h4>';

		$html = $html . ' <table  >
			<thead>
				<tr>
				<th>No.</th>
				<th>Tanggal</th>
				<th>Stok Awal</th>				
				<th>Hasil Produksi</th>
				<th>Pengiriman</th>
				<th>Stok Akhir</th>
				
				</tr>
			</thead>
			<tbody>';


		$no = 0;
		$stok_awal = 0;
		$hasil_produksi = 0;
		$pengiriman = 0;
		$stok_akhir = 0;

		$tgl_awal = $tgl_mulai;
		do {
			$no = $no + 1;
			$retStokAwal = $this->db->query("SELECT sum(a.volume)as volume FROM `pks_sounding` a left join pks_tanki b on a.tanki_id=b.id
			 where a.tanggal ='" . $tgl_awal . "' and a.mill_id=" . $mill_id . " and b.produk_id=" . $produk_id . "
			  group by a.tanggal
			  order by a.tanggal DESC limit 1  
			")->row_array();

			if ($retStokAwal) {
				$stok_awal = $retStokAwal['volume'];
			} else {
				$stok_awal = 0;
			}
			$retPengiriman = $this->db->query("SELECT SUM(netto_kirim) AS kg  FROM pks_timbangan_kirim 
			 where tanggal ='" . $tgl_awal . "' and mill_id=" . $mill_id . " and item_id=" . $produk_id . "
			")->row_array();

			if ($retPengiriman) {
				$pengiriman = $retPengiriman['kg'] ? $retPengiriman['kg'] : 0;
			} else {
				$pengiriman = 0;
			}

			$tgl_besok = date("Y-m-d", strtotime("1 day", strtotime($tgl_awal)));
			$retStokAkhir = $this->db->query("SELECT sum(a.volume)as volume FROM `pks_sounding` a left join pks_tanki b on a.tanki_id=b.id
			where a.tanggal ='" . $tgl_besok . "' and a.mill_id=" . $mill_id . " and b.produk_id=" . $produk_id . "
			  group by a.tanggal
			 order by a.tanggal DESC limit 1  
		   ")->row_array();

			if ($retStokAkhir) {
				$stok_akhir = $retStokAkhir['volume'];
			} else {
				$stok_akhir = 0;
			}
			$hasil_produksi = $stok_akhir - $stok_awal + $pengiriman;

			$html = $html . ' 	<tr class=":: arc-content">
			 <td style="position:relative;">
				 ' . ($no) . '

			 </td>
			 <td>
			 ' . $tgl_awal . ' 
				 
			 </td>
			
			 <td style="text-align: right;">' . number_format($stok_awal) .  ' </td> 
			 <td style="text-align: right;">' . number_format($hasil_produksi) .  ' </td> 
			 <td style="text-align: right;">' . number_format($pengiriman) .  ' </td> 
			 <td style="text-align: right;">' . number_format($stok_akhir) .  ' </td> 
			 </tr>';;
			$tgl_awal = date("Y-m-d",  strtotime("1 day", strtotime($tgl_awal)));
		} while ($tgl_awal <= $tgl_akhir);
		// $your_date = strtotime("1 day", strtotime("2016-08-24"));
		// $new_date = date("Y-m-d", $your_date);



		$html = $html . ' 	
						
								</tbody>
							</table>
						';
		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		echo $html;
	}


	function laporan_produksi_harian_post()
	{
		error_reporting(0);

		$data = [];
		if (isset($this->post()['tanggal'])) {
			$input = $this->post();
		} else {
			$input = [
				'tanggal' => '2021-12-23',
			];
		}
		$parseTanggal = explode('-', $input['tanggal']);
		$input['tgl_mulai'] = $parseTanggal[0] . '-' . $parseTanggal[1] . '-1';
		$input['tgl_akhir'] = $input['tanggal'];
		$data['input'] = $input;

		$data['produksi'] = $this->PksProduksiModel->laporanProduksiHarian($input);

		$this->load->view('PksProduksi_harian_laporan', $data);
	}

	function laporan_produksi_bulanan_post()
	{
		error_reporting(0);

		$data = [];
		if (isset($this->post()['tanggal'])) {
			$input = $this->post();
		} else {
			$input = [
				'tanggal' => '2021-12-23',
			];
		}
		$parseTanggal = explode('-', $input['tanggal']);
		$input['tgl_mulai'] = $parseTanggal[0] . '-' . $parseTanggal[1] . '-1';
		$input['tgl_akhir'] = $input['tanggal'];
		$data['input'] = $input;

		$data['produksi'] = $this->PksProduksiModel->laporanProduksiBulanan($input);

		$this->load->view('PksProduksi_bulanan_laporan', $data);
	}
}
