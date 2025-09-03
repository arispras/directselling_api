<!DOCTYPEhtml>
	<html>

	<head>
	<title>Laporan Rawat Jalan</title>
	<link rel="icon" type="image/png" href="<?= base_url('logo_antech.png') ?>" />
		<?php
		if ($format_laporan == 'view') {
			require '_laporan_style_fix.php';
		} else {
			if ($format_laporan == 'pdf') {
				require '__laporan_style_pdf.php';
			}
		}
		?>
	</head>

	<body>

		<?php require '__laporan_header.php' ?>

		<!-- <pre><?php print_r($po) ?></pre> -->

		<h3 class="title">LAPORAN RAWAT JALAN</h3>
		<br>

		<div class="d-flex flex-between">
			<table class="no_border" style="width:30%">
				<!-- <tr>
					<td>Lokasi</td>
					<th>:</th>
					<td><?= $filter_lokasi ?></td>
				</tr>
				<tr>
					<td>supplier</td>
					<th>:</th>
					<td><?= $filter_supplier ?></td>
				</tr>
				<tr>
					<td>Gudang</td>
					<th>:</th>
					<td><?= $filter_gudang ?></td>
				</tr> -->
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
					<th style="width:10%">NoRJ</th>
					<th>Tanggal</th>
					<th>Poli</th>
					<th style="width:10%">Pasien</th>
					<th>Dokter</th>
					<th >Catatan</th>
				</tr>

			</thead>


			<tbody>
				<?php $no = 0 ?>
				<?php foreach ($rawatJalan as $key => $val) { ?>

					<?php $no = $no + 1 ?>
					<tr>
						<td><?= $no ?></td>

						<td left><?= $val['no_transaksi'] ?></td>
						<td center><?= tgl_indo_normal($val['tanggal']) ?></td>
						<td center><?= $val['nama_poli'] ?></td>
						<td center><?= $val['nama_pasien'] ?></td>
						<td center><?= $val['nama_dokter'] ?></td>
						<td left><?= $val['catatan'] ?></td>
						

					</tr>
				<?php } ?>


			</tbody>

			<tfoot></tfoot>
		</table>






		<pre><?php //print_r($headeran) 
				?></pre>

	</body>

	</html>