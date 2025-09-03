<!DOCTYPEhtml>
	<html>

	<head>
	<title> Sales Order Status</title>
	<link rel="icon" type="image/png" href="<?= base_url('logo_antech.png') ?>" />
	<?php 
			if ($format_laporan=='view') {
				require '_laporan_style_fix.php';
			}
			else{
				if ($format_laporan=='pdf') {
					require '__laporan_style_pdf.php';
					echo $html='
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
				}	
			}
		?>

		<style>
			thead {display: table-row-group;}
			pagebreak: { avoid: ['tr', 'td'] }
		</style>
	</head>

	<body>

		<?php require '__laporan_header.php' ?>

		<br>
		<div center>
		<h1>C.02 STATUS Sales Order DETAIL</h1>
		</div>
		<br>

		<div class="d-flex flex-between">
			<table class="no_border" style="width:30%">
				<tr>
					<td>Lokasi</td>
					<th>:</th>
					<td><?= $filter_lokasi ?></td>
				</tr>
				<tr>
					<td>Customer</td>
					<th>:</th>
					<td><?= $filter_customer ?></td>
				</tr>
				<tr>
					<td>Periode Tanggal</td>
					<th>:</th>
					<td><?= tgl_indo($filter_tgl_awal) . ' s/d ' . tgl_indo($filter_tgl_akhir) ?></td>
				</tr>

			</table>
		</div>

		<div class="d-flex flex-between">
			<table class="" style="width:30%">


			</table>
		</div>
		<br>


		<table class="table-bg border">
			<thead>
				<tr>
					<th style="width:1%">No</th>
					<th >Customer</th>
					<th >No SO</th>
					<th style="width:5%">Tanggal</th>
					<th >Kode Barang</th>
					<th >Nama Barang</th>
					<!-- <th style="width:4%">Mata Uang</th> -->
					<th style="width:4%">Jml</th>
					<th style="width:4%">Jml Terima</th>
					<th style="width:4%">Sisa</th>
					<th style="width:4%">Satuan</th>
					<th style="width:5%">Harga (Rp)</th>
					<th style="width:5%">Total (Rp)</th>
					<!-- <th style="width:5%">Tanggal Permintaan</th>
					<th>NoRequest</th> -->
					<th style="width:5%">Tanggal Pengiriman</th>
					<th >No Pengiriman</th>
					<th >No SJ</th>
					<th >Status</th>

				</tr>

			</thead>

			<tbody>
				<?php
				$no = 0;
				$total = 0;
				?>
				<?php foreach ($so as $key => $val) { ?>

					<?php
					$no = $no + 1;
					$total = $total + $val['total_nilai_penjualan'];

					?>
					<tr>
						<td><?= $no ?></td>
						<td left><?= $val['sup'] ?></td>
						<td left><?= $val['noso'] ?></td>
						<td center><?= tgl_indo_normal($val['tanggal']) ?></td>
						<td center><?= $val['kode'] ?></td>
						<td left><?= $val['item'] ?></td>
						<!-- <td center><?= $val['mata_uang'] ?></td> -->
						<td right><?= number_format($val['qty_order']) ?></td>
						<td right><?= number_format($val['qty_terima']) ?></td>
						<td right><?= number_format($val['qty_order'] - $val['qty_terima']) ?></td>
						<td center><?= $val['uom'] ?></td>
						<td right><?= number_format($val['harga_jual']) ?></td>
						<td right><?= number_format($val['total_nilai_penjualan']) ?></td>
						<td center><?= tgl_indo_normal($val['tanggal_penerimaan']) ?></td>
						<td left><?= $val['no_penerimaan'] ?></td>
						<td left><?= $val['no_ref'] ?></td>
						<td left><?= $val['status'] ?></td>
						<!-- <td>23/01/2022</td> -->
					</tr>
				<?php } ?>
				<tr>
					<td center></td>
					<td center></td>
					<td center></td>
					<td center></td>
					<td center></td>
					<td right></td>
					<td right></td>
					<td right></td>
					<td center></td>
					<td right></td>
					<td></td>
					<td right><?= number_format($total) ?></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>

				</tr>


			</tbody>

			<tfoot></tfoot>
		</table>






		<pre><?php //print_r($headeran) 
				?></pre>

	</body>

	</html>
