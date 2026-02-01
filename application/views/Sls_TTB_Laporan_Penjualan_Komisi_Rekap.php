<!DOCTYPEhtml>
	<html>

	<head>
		<title>REKAP KOMISI PENJUALAN</title>
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

		<h3 class="title">REKAP KOMISI PENJUALAN </h3>
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
					<th style="width:9%">Qty</th>
					<th style="width:9%">Bonus</th>
					<th style="width:6%">Komisi</th>
					<th style="width:9%">JumlahBonus</th>
					<th style="width:6%">JumlahKomisi</th>
					<th style="width:6%">Total</th>

				</tr>

			</thead>
			<tbody>
				<?php
				$no = 0;
				$jum_qty = 0;
				$jum_komisi = 0;
				$jum_bonus = 0;
				$jum_total = 0;
				$tot_qty = 0;
				$tot_bonus = 0;
				$tot_komisi = 0;
				$tot_total = 0;

				?>

				<?php foreach ($dataBySales as $key => $res) { ?>

					<?php
					$no = $no + 1;
					$jum_qty += $res['qty'];
					$jum_komisi += $res['jumlah_komisi'];
					$jum_bonus += $res['jumlah_bonus'];
					$jum_total += $res['jumlah_komisi_bonus'];



					?>
					<tr>
						<td center> <?= $no ?> </td>
						<td left><?= $res['sales'] ?></td>
						<td right><?= format_number_report($res['qty'], $format_laporan) ?></td>
						<td right><?= format_number_report($res['bonus'], $format_laporan) ?></td>
						<td right><?= format_number_report($res['komisi'], $format_laporan) ?></td>
						<td right><?= format_number_report($res['jumlah_bonus'], $format_laporan) ?></td>
						<td right><?= format_number_report($res['jumlah_komisi'], $format_laporan) ?></td>
						<td right><?= format_number_report($res['jumlah_komisi_bonus'], $format_laporan) ?></td>

					</tr>
				<?php } ?>
				<tr>
					<td colspan=2><strong>Total</strong></td>
					<td right><strong><?= format_number_report($jum_qty, $format_laporan) ?></strong></td>
					<td><strong></strong></td>
					<td><strong></strong></td>
					<td right><strong><?= format_number_report($jum_bonus, $format_laporan) ?></strong></td>
					<td right><strong><?= format_number_report($jum_komisi, $format_laporan) ?></strong></td>
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
					<th style="width:12%">Sales SV</th>
					<th style="width:9%">Qty</th>
					<th style="width:9%">Bonus</th>
					<th style="width:6%">Komisi</th>
					<th style="width:9%">JumlahBonus</th>
					<th style="width:6%">JumlahKomisi</th>
					<th style="width:6%">Total</th>

				</tr>

			</thead>
			<tbody>
				<?php
				$no = 0;
				$jum_qty = 0;
				$jum_komisi = 0;
				$jum_bonus = 0;
				$jum_total = 0;
				$tot_qty = 0;
				$tot_bonus = 0;
				$tot_komisi = 0;
				$tot_total = 0;

				?>

				<?php foreach ($dataBySalesSupervisor as $key => $res) { ?>

					<?php
					$no = $no + 1;
					$jum_qty += $res['qty'];
					$jum_komisi += $res['jumlah_komisi'];
					$jum_bonus += $res['jumlah_bonus'];
					$jum_total += $res['jumlah_komisi_bonus'];



					?>
					<tr>
						<td center> <?= $no ?> </td>
						<td left><?= $res['sales_supervisor'] ?></td>
						<td right><?= format_number_report($res['qty'], $format_laporan) ?></td>
						<td right><?= format_number_report($res['bonus'], $format_laporan) ?></td>
						<td right><?= format_number_report($res['komisi'], $format_laporan) ?></td>
						<td right><?= format_number_report($res['jumlah_bonus'], $format_laporan) ?></td>
						<td right><?= format_number_report($res['jumlah_komisi'], $format_laporan) ?></td>
						<td right><?= format_number_report($res['jumlah_komisi_bonus'], $format_laporan) ?></td>

					</tr>
				<?php } ?>
				<tr>
					<td colspan=2><strong>Total</strong></td>
					<td right><strong><?= format_number_report($jum_qty, $format_laporan) ?></strong></td>
					<td><strong></strong></td>
					<td><strong></strong></td>
					<td right><strong><?= format_number_report($jum_bonus, $format_laporan) ?></strong></td>
					<td right><strong><?= format_number_report($jum_komisi, $format_laporan) ?></strong></td>
					<td right><strong><?= format_number_report($jum_total, $format_laporan) ?></strong></td>

				</tr>

			</tbody>
			<tfoot></tfoot>
		</table>



	</body>

	</html>