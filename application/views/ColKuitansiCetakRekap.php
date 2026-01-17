<!DOCTYPEhtml>
	<html>

	<head>
		<title>Rekap Kuitansi</title>
		<?php function format_number_report($angka, $fmt_laporan)
		{
			$format_laporan     = $fmt_laporan;
			if ($format_laporan == 'xls') {
				return $angka;
			} else {
				if ($angka == 0) {
					return '';
				}
				return number_format($angka);
			}
		}
		?>
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
		<style>
			* body {
				font-size: 11px;
			}
		</style>
	</head>

	<body>


		<?php require '__laporan_header.php' ?>

		<h3 class="title">Rekap Kuitansi</h3>
		<br>

		<div class="d-flex flex-between">
			<table class="no_border" style="width:30%">

				<tr>
					<td>Periode Tanggal</td>
					<td>:</td>
					<td><?= tgl_indo($filter_tgl_awal) . ' s/d ' . tgl_indo($filter_tgl_akhir) ?></td>
				</tr>

			</table>
		</div>
		<br>


		<table class="table-bg border">
			<thead>
				<tr>
					<th style="width:2%">No</th>
					<th style="width:9%">No Kuitansi</th>
					<th style="width:6%">Tanggal Tempo</th>
					<th style="width:5%">Collector</th>
					<th style="width:12%">Customer</th>
					<th style="width:15%">AngsuranKe</th>
					<th style="width:5%">Nilai Angsuran</th>

				</tr>

			</thead>
			<tbody>
				<?php $no = 0;
				$sum = 0;
				$j_qty = 0; ?>
				<?php foreach ($kuitansi as $key => $k) { ?>


					<tr>
						<?php
						$no = $no + 1;
						$sum = $sum + $k['nilai_angsuran'];
						?>
						<td center> <?= $no ?> </td>
						<td left> <?= $k['no_kuitansi'] ?></td>
						<td center><?= tgl_indo($k['tanggal_tempo']) ?></td>
						<td left><?= $k['collector'] ?></td>
						<td center><?= $k['nama_customer'] ?></td>
						<td center><?= $k['angsuran_ke'] ?></td>
						<td right><?= format_number_report($k['nilai_angsuran'], $format_laporan) ?></td>
			
					</tr>


				<?php } ?>
				<tr>
					<td colspan=6></td>
					<td right><?= format_number_report($sum, $format_laporan) ?></td>
					
				</tr>

			</tbody>
			<tfoot></tfoot>
		</table>






		<pre><?php //print_r($headeran) 
				?></pre>

	</body>

	</html>