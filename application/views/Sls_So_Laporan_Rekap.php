<!DOCTYPEhtml>
	<html>

	<head>
	<title>Sales Order Status Summary</title>
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
			<h3 class='title'>C.02 STATUS Sales Order SUMMARY</h1>
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
					<th style="width:2%">No</th>
					<th style="width:10%">Customer</th>
					<th style="width:10%">No SO</th>
					<th style="width:5%">Tanggal</th>
					<th style="width:3%">Mata Uang</th>
					<th style="width:7%">Note</th>
					<th style="width:10%">Syarat Bayar</th>
					<th style="width:3%">Tempo Bayar</th>
					<!-- <th style="width:5%">Tgl Tempo</th> -->
					<th style="width:8%">Nilai SO (Rp)</th>
					<th style="width:5%">Tanggal Pengiriman</th>
					<th style="width:7%">No Pengiriman</th>
					<th style="width:7%">No Ref</th>
					<th style="width:7%">Status</th>


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
					$total = $total + $val['grand_total'];

					?>
					<tr>
						<td center><?= $no ?></td>
						<td left><?= $val['sup'] ?></td>
						<td left><?= $val['no_so'] ?></td>
						<td center><?= tgl_indo_normal($val['tanggal']) ?></td>
						<td center><?= $val['mata_uang'] ?></td>
						<td left><?= $val['catatan'] ?></td>
						<td left><?= $val['syarat_bayar'] ?></td>
						<td center><?= $val['tempo_bayar'] ?>&nbsp; Hari</td>
						<!-- <td center><?= $val['tgl_tempo_bayar'] ?></td> -->
						<td right><?= number_format($val['grand_total']) ?></td>
						<td center><?= tgl_indo_normal($val['tanggal_penerimaan']) ?></td>
						<td><?= $val['no_penerimaan'] ?></td>
						<td><?= $val['no_surat_jalan_customer'] ?></td>
						<td><?= $val['status'] ?></td>
						<!-- <td>23/01/2022</td> -->
					</tr>
				<?php } ?>
				<tr>
					<td center></td>
					<td center></td>
					<td center></td>
					<td center></td>
					<td center></td>
					<td center></td>
								
					<td right></td>
					<td></td>
					<td right><?= number_format($total) ?></td>
					
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
