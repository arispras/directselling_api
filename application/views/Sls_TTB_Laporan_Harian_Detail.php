<!DOCTYPEhtml>
	<html>

	<head>
		<title>HARIAN DETAIL</title>
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

		<h3 class="title">RINCIAN LAPORAN HARIAN </h3>
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
					<th style="width:12%">Customer</th>
					<th style="width:9%">No So</th>
					<th style="width:6%">Tgl SO</th>
					<th style="width:9%">No TTB</th>
					<th style="width:6%">Tgl TTB</th>
					<th style="width:5%">Kode Item</th>
					<th style="width:15%">Nama Item</th>
					<th style="width:5%">PDV</th>
					<th style="width:5%">PDL</th>
					<th style="width:5%">Actual</th>
					<th style="width:5%">Batal</th>
					<th style="width:5%">Jumlah</th>

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

				<?php foreach ($sales as $key => $s) { ?>
					<?php
					$data = $dataBySales[$s['id']];
					$no = 0;
					$jum_pdv = 0;
					$jum_pdl = 0;
					$jum_aktual = 0;
					$jum_batal = 0;
					$jum_total = 0;

					?>
					<tr>
						<td colspan='12'><?= $s['nama'] ?></td>
					</tr>
					<?php foreach ($data as $key => $res) { ?>
						<tr>
							<?php
							$no = $no + 1;
							$jum_pdv += $res['qty_pdv'];
							$jum_pdl += $res['qty_pdl'];
							$jum_aktual += $res['qty_actual'];
							$jum_batal += $res['qty_batal'];
							$total = $res['qty_pdv'] + $res['qty_pdl'] + $res['qty_actual']+ $res['qty_batal'];
							$jum_total += $total;

							$tot_pdv += $res['qty_pdv'];
							$tot_pdl += $res['qty_pdl'];
							$tot_aktual += $res['qty_actual'];
							$tot_batal += $res['qty_batal'];
							$tot_total += $total;

							?>

							<td center> <?= $no ?> </td>
							<td left><?= $res['nama_customer'] ?></td>
							<td center><?= $res['no_so'] ?></td>
							<td center><?= tgl_indo($res['tanggal_so']) ?></td>
							<td left> <?= $res['no_ttb'] ?></td>
							<td center><?= tgl_indo($res['tanggal_ttb']) ?></td>
							<td center><?= $res['kode_item'] ?></td>
							<td left><?= $res['nama_item'] ?></td>
							<td right><?= format_number_report($res['qty_pdv'], $format_laporan) ?></td>
							<td right><?= format_number_report($res['qty_pdl'], $format_laporan) ?></td>
							<td right><?= format_number_report($res['qty_actual'], $format_laporan) ?></td>
							<td right><?= format_number_report($res['qty_batal'], $format_laporan) ?></td>
							<td right><?= format_number_report($total, $format_laporan) ?></td>

						</tr>
					<?php } ?>
					<tr>
						<td colspan=8><strong>Total <?= $s['nama'] ?></strong></td>
						<td right><strong><?= format_number_report($jum_pdv, $format_laporan) ?></strong></td>
						<td right><strong><?= format_number_report($jum_pdl, $format_laporan) ?></strong></td>
						<td right><strong><?= format_number_report($jum_aktual, $format_laporan) ?></strong></td>
						<td right><strong><?= format_number_report($jum_batal, $format_laporan) ?></strong></td>
						<td right><strong><?= format_number_report($jum_total, $format_laporan) ?></strong></td>

					</tr>
				<?php } ?>


				<tr>
					<td colspan=8><strong>Total</strong> </td>
					<td right><strong><?= format_number_report($tot_pdv, $format_laporan) ?></strong></td>
					<td right><strong><?= format_number_report($tot_pdl, $format_laporan) ?></strong></td>
					<td right><strong><?= format_number_report($tot_aktual, $format_laporan) ?></strong></td>
					<td right><strong><?= format_number_report($tot_batal, $format_laporan) ?></strong></td>
					<td right><strong><?= format_number_report($tot_total, $format_laporan) ?></strong></td>

				</tr>
				


			</tbody>
			
		</table>

		<br>
		<br>
		<hr>

		<h3 >SALES SPV </h3>
		<table class="table-bg border">
			<thead>
				<tr>
					<th style="width:2%">No</th>
					<th style="width:12%">Customer</th>
					<th style="width:9%">No So</th>
					<th style="width:6%">Tgl SO</th>
					<th style="width:9%">No TTB</th>
					<th style="width:6%">Tgl TTB</th>
					<th style="width:5%">Kode Item</th>
					<th style="width:15%">Nama Item</th>
					<th style="width:5%">PDV</th>
					<th style="width:5%">PDL</th>
					<th style="width:5%">Actual</th>
					<th style="width:5%">Batal</th>
					<th style="width:5%">Jumlah</th>

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

				<?php foreach ($sales_supervisor as $key => $s) { ?>
					<?php
					$data = $dataBySalesSupervisor[$s['id']];
					$no = 0;
					$jum_pdv = 0;
					$jum_pdl = 0;
					$jum_aktual = 0;
					$jum_batal = 0;
					$jum_total = 0;

					?>
					<tr>
						<td colspan='12'><?= $s['nama'] ?></td>
					</tr>
					<?php foreach ($data as $key => $res) { ?>
						<tr>
							<?php
							$no = $no + 1;
							$jum_pdv += $res['qty_pdv'];
							$jum_pdl += $res['qty_pdl'];
							$jum_aktual += $res['qty_actual'];
							$jum_batal += $res['qty_batal'];
							$total = $res['qty_pdv'] + $res['qty_pdl'] + $res['qty_actual'] + $res['qty_batal'];
							$jum_total += $total;

							$tot_pdv += $res['qty_pdv'];
							$tot_pdl += $res['qty_pdl'];
							$tot_aktual += $res['qty_actual'];
							$tot_batal += $res['qty_batal'];
							$tot_total += $total;

							?>

							<td center> <?= $no ?> </td>
							<td left><?= $res['nama_customer'] ?></td>
							<td center><?= $res['no_so'] ?></td>
							<td center><?= tgl_indo($res['tanggal_so']) ?></td>
							<td left> <?= $res['no_ttb'] ?></td>
							<td center><?= tgl_indo($res['tanggal_ttb']) ?></td>
							<td center><?= $res['kode_item'] ?></td>
							<td left><?= $res['nama_item'] ?></td>
							<td right><?= format_number_report($res['qty_pdv'], $format_laporan) ?></td>
							<td right><?= format_number_report($res['qty_pdl'], $format_laporan) ?></td>
							<td right><?= format_number_report($res['qty_actual'], $format_laporan) ?></td>
							<td right><?= format_number_report($res['qty_batal'], $format_laporan) ?></td>
							<td right><?= format_number_report($total, $format_laporan) ?></td>

						</tr>
					<?php } ?>
					<tr>
						<td colspan=8><strong>Total <?= $s['nama'] ?></strong></td>
						<td right><strong><?= format_number_report($jum_pdv, $format_laporan) ?></strong></td>
						<td right><strong><?= format_number_report($jum_pdl, $format_laporan) ?></strong></td>
						<td right><strong><?= format_number_report($jum_aktual, $format_laporan) ?></strong></td>
						<td right><strong><?= format_number_report($jum_batal, $format_laporan) ?></strong></td>
						<td right><strong><?= format_number_report($jum_total, $format_laporan) ?></strong></td>

					</tr>
				<?php } ?>

				<tr>
					<td colspan=8><strong>Total</strong> </td>
					<td right><strong><?= format_number_report($tot_pdv, $format_laporan) ?></strong></td>
					<td right><strong><?= format_number_report($tot_pdl, $format_laporan) ?></strong></td>
					<td right><strong><?= format_number_report($tot_aktual, $format_laporan) ?></strong></td>
					<td right><strong><?= format_number_report($tot_batal, $format_laporan) ?></strong></td>
					<td right><strong><?= format_number_report($tot_total, $format_laporan) ?></strong></td>

				</tr>
			

			</tbody>
			<tfoot></tfoot>
		</table>



	</body>

	</html>