<!DOCTYPEhtml>
	<html>

	<head>
		<title>HARIAN REKAP</title>
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

		<!-- <pre><?php print_r($so) ?></pre> -->

		<h3 class="title">REKAP LAPORAN HARIAN </h3>
		<br>

		<div class="d-flex flex-between">
			<table class="no_border" style="width:30%">
				<tr>
					<td>Lokasi</td>
					<td>:</td>
					<td><?= $filter_lokasi ?></td>
				</tr>

				<tr>
					<td>Periode Tanggal</td>
					<td>:</td>
					<td><?= tgl_indo($filter_tgl_awal) . ' s/d ' . tgl_indo($filter_tgl_akhir) ?></td>
				</tr>

			</table>
		</div>
		<br>

		<h3>SALES </h3>
		<table class="table-bg border">
			<thead>
				<tr>
					<th style="width:2%">No</th>
					<th style="width:12%">Sales</th>
					<th style="width:9%">PDV</th>
					<th style="width:6%">PDL</th>
					<th style="width:9%">AKTUAL</th>
					<th style="width:9%">BATAL</th>
					<th style="width:6%">JUMLAH</th>

				</tr>

			</thead>
			<tbody>
				<?php
				$no = 0;
				$jum_pdv = 0;
				$jum_pdl = 0;
				$jum_batal = 0;
				$jum_aktual = 0;
				$jum_total = 0;
				$tot_pdv = 0;
				$tot_pdl = 0;
				$tot_batal = 0;
				$tot_aktual = 0;
				$tot_total = 0;

				?>

				<?php foreach ($dataBySales as $key => $res) { ?>

					<?php
					$no = $no + 1;
					$jum_pdv += $res['pdv'];
					$jum_pdl += $res['pdl'];
					$jum_batal += $res['batal'];
					$jum_aktual += $res['actual'];
					$total = $res['pdv'] + $res['pdl'] + $res['actual'] + $res['batal'];
					$jum_total += $total;

					$tot_pdv += $res['pdv'];
					$tot_pdl += $res['pdl'];
					$tot_batal += $res['batal'];
					$tot_aktual += $res['actual'];
					$tot_total += $total;

					?>
					<tr>
						<td center> <?= $no ?> </td>
						<td left><?= $res['sales'] ?></td>
						<td right><?= format_number_report($res['pdv'], $format_laporan) ?></td>
						<td right><?= format_number_report($res['pdl'], $format_laporan) ?></td>
						<td right><?= format_number_report($res['actual'], $format_laporan) ?></td>
						<td right><?= format_number_report($res['batal'], $format_laporan) ?></td>
						<td right><?= format_number_report($total, $format_laporan) ?></td>

					</tr>
				<?php } ?>
				<tr>
					<td colspan=2><strong>Total</strong></td>
					<td right><strong><?= format_number_report($jum_pdv, $format_laporan) ?></strong></td>
					<td right><strong><?= format_number_report($jum_pdl, $format_laporan) ?></strong></td>
					<td right><strong><?= format_number_report($jum_aktual, $format_laporan) ?></strong></td>
					<td right><strong><?= format_number_report($jum_batal, $format_laporan) ?></strong></td>
					<td right><strong><?= format_number_report($jum_total, $format_laporan) ?></strong></td>

				</tr>

			</tbody>
			<tfoot></tfoot>
		</table>

		<br>
		<br>
		<hr>

		<h3>SALES SPV </h3>
		<table class="table-bg border">
			<thead>
				<tr>
					<th style="width:2%">No</th>
					<th style="width:12%">Sales SPV</th>
					<th style="width:9%">PDV</th>
					<th style="width:6%">PDL</th>
					<th style="width:9%">AKTUAL</th>
					<th style="width:9%">BATAL</th>
					<th style="width:6%">JUMLAH</th>

				</tr>

			</thead>
			<tbody>
				<?php
				$no = 0;
				$jum_pdv = 0;
				$jum_pdl = 0;
				$jum_aktual = 0;
				$jum_batal = 0;
				$jum_total = 0;
				$tot_pdv = 0;
				$tot_pdl = 0;
				$tot_aktual = 0;
				$tot_batal = 0;
				$tot_total = 0;

				?>


				<?php foreach ($dataBySalesSupervisor as $key => $res) { ?>

					<?php
					$no = $no + 1;
					$jum_pdv += $res['pdv'];
					$jum_pdl += $res['pdl'];
					$jum_aktual += $res['actual'];
					$jum_batal += $res['batal'];
					$total = $res['pdv'] + $res['pdl'] + $res['actual'] + $res['batal'];
					$jum_total += $total;

					$tot_pdv += $res['pdv'];
					$tot_pdl += $res['pdl'];
					$tot_aktual += $res['actual'];
					$tot_batal += $res['batal'];
					$tot_total += $total;

					?>
					<tr>
						<td center> <?= $no ?> </td>
						<td left><?= $res['sales_supervisor'] ?></td>
						<td right><?= format_number_report($res['pdv'], $format_laporan) ?></td>
						<td right><?= format_number_report($res['pdl'], $format_laporan) ?></td>
						<td right><?= format_number_report($res['actual'], $format_laporan) ?></td>
						<td right><?= format_number_report($res['batal'], $format_laporan) ?></td>
						<td right><?= format_number_report($total, $format_laporan) ?></td>

					</tr>
				<?php } ?>
				<tr>
					<td colspan=2><strong>Total</strong></td>
					<td right><strong><?= format_number_report($jum_pdv, $format_laporan) ?></strong></td>
					<td right><strong><?= format_number_report($jum_pdl, $format_laporan) ?></strong></td>
					<td right><strong><?= format_number_report($jum_aktual, $format_laporan) ?></strong></td>
					<td right><strong><?= format_number_report($jum_batal, $format_laporan) ?></strong></td>
					<td right><strong><?= format_number_report($jum_total, $format_laporan) ?></strong></td>

				</tr>





			</tbody>

		</table>



	</body>

	</html>