<!DOCTYPEhtml>
	<html>

	<head>
		<title>LHI Detail</title>
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

		<h3 class="title">LHI DETAIL</h3>
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


		<table class="table-bg border">
			<thead>
				<tr>
					<th style="width:2%">No</th>
					<th style="width:9%">No TTB</th>
					<th style="width:9%">Tanggal</th>
					<th style="width:12%">Customer</th>
					<th style="width:6%">No Kuitansi</th>
					<th style="width:6%">Tgl Tempo</th>
					<th style="width:6%">Angs Ke</th>
					<th style="width:5%">Nilai Angsuran</th>
					<th style="width:5%">Dibayar</th>
					<th style="width:5%">Sisa</th>
				</tr>

			</thead>
			<tbody>
				<?php
				$no = 0;
				$jum_dibayar = 0;
				$jum_qty = 0;
				$jum_dp = 0;
				$jum_total = 0;
				$jum_nilai_ttb = 0;
				$jum_nilai_angsuran = 0;

				?>


				<?php foreach ($data as $key => $res) { ?>
					<?php
					$kuitansi = $res['kuitansi'];
					$no = $no + 1;
					$jum_dibayar += $res['dibayar'];
					$jum_nilai_ttb += $res['nilai_ttb'];
					?>
					<tr>

						<td center> <?= $no ?> </td>
						<td left><?= $res['no_ttb'] ?></td>
						<td center><?= tgl_indo($res['tanggal_ttb']) ?></td>
						<td left><?= $res['nama_customer'] ?></td>
						<!-- <td right><?= format_number_report($res['nilai_ttb'], $format_laporan) ?></td>
						<td right><?= format_number_report($res['dibayar'], $format_laporan) ?></td>
						<td right><?= format_number_report($res['nilai_ttb'] - $res['dibayar'], $format_laporan) ?></td> -->

					</tr>

					<?php foreach ($kuitansi as $k => $v) { ?>
						<tr>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td center><?= $v['no_kuitansi'] ?></td>
							<td center><?= tgl_indo($v['tanggal_tempo']) ?></td>
							<td center><?= $v['angsuran_ke'] ?></td>
							<td right><?= format_number_report($v['nilai_angsuran'], $format_laporan) ?></td>
							<td right><?= format_number_report($v['dibayar'], $format_laporan) ?></td>
							<td right><?= format_number_report(($v['nilai_angsuran'] - $v['dibayar']), $format_laporan) ?></td>
						</tr>
					<?php } ?>
					<td colspan=7>Sub TOTAL</td>
					<td right><?= format_number_report($res['nilai_ttb'], $format_laporan) ?></td>
					<td right><?= format_number_report($res['dibayar'], $format_laporan) ?></td>
					<td right><?= format_number_report($res['nilai_ttb'] - $res['dibayar'], $format_laporan) ?></td>

				<?php } ?>

				<tr>
					<td colspan=7>TOTAL</td>
					<td right><strong><?= format_number_report($jum_nilai_ttb, $format_laporan) ?></strong></td>
					<td right><strong><?= format_number_report($jum_dibayar, $format_laporan) ?></strong></td>
					<td right><strong><?= format_number_report($jum_nilai_ttb - $jum_dibayar, $format_laporan) ?></strong></td>
				</tr>

			</tbody>
			<tfoot></tfoot>
		</table>


	</body>

	</html>