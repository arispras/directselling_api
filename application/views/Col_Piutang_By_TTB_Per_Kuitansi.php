<!DOCTYPEhtml>
	<html>

	<head>
		<title>PIUTANG DETAIL</title>
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

		<h3 class="title">PUTANG PER KUITANSI BY Tanggal TTB</h3>
		<br>

		<div class="d-flex flex-between">
			<table class="no_border" style="width:30%">
				<tr>
					<td>Lokasi</td>
					<td>:</td>
					<td><?= $filter_lokasi ?></td>
				</tr>

				<tr>
					<td>Periode Tanggal TTB</td>
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
					<th style="width:12%">Collector</th>
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
				$jum_nilai_angsuran = 0;
				$jum_sisa = 0;
				$total_nilai_angsuran = 0;
				$total_dibayar = 0;
				$total_sisa = 0;
				$jum_nilai_angsuran = 0;

				?>


				<?php foreach ($data as $key => $res) { ?>
					<?php
					$kuitansi = $res['kuitansi'];
					$no = 0;
					$jum_dibayar = 0;
					$jum_nilai_angsuran = 0;
					$jum_sisa = 0;
					?>
					<tr>

						<!-- <td center > <?= $no ?> </td> -->
						<td left colspan="9"><strong><?= $res['collector'] ?></strong></td>
						
					</tr>

					<?php foreach ($kuitansi as $k => $v) {
							$no = $no + 1;
						$sisa += ($v['nilai_angsuran'] - ($v['dibayar'] ? $v['dibayar'] : 0));
						$jum_dibayar += ($v['dibayar'] ? $v['dibayar'] : 0);
						$jum_nilai_angsuran += ($v['nilai_angsuran']);						
						$jum_sisa += $sisa;
						$total_dibayar += ($v['dibayar'] ? $v['dibayar'] : 0);
						$total_nilai_angsuran += ($v['nilai_angsuran']);						
						$total_sisa += $sisa;

					?>
						<tr>
							<td> <?= $no ?> </td>
							<td><?= $v['collector'] ?></td>
							<td left><?= $v['nama_customer'] ?></td>
							<td center><?= $v['no_kuitansi'] ?></td>
							<td center><?= tgl_indo($v['tanggal_tempo']) ?></td>
							<td center><?= $v['angsuran_ke'] ?></td>
							<td right><?= format_number_report($v['nilai_angsuran'], $format_laporan) ?></td>
							<td right><?= format_number_report($v['dibayar'], $format_laporan) ?></td>
							<td right><?= format_number_report(($v['nilai_angsuran'] - ($v['dibayar'] ? $v['dibayar'] : 0)), $format_laporan) ?></td>
						</tr>
					<?php } ?>
					<td colspan=6><strong>SUB TOTAL</strong></td>
					<td right><strong><?= format_number_report($jum_nilai_angsuran, $format_laporan) ?></strong></td>
					<td right><strong><?= format_number_report($jum_dibayar, $format_laporan) ?></strong></td>
					<td right><strong><?= format_number_report($jum_sisa, $format_laporan) ?></strong></td>

				<?php } ?>

				<tr>
					<td colspan=6><strong>TOTAL</strong></td>
					<td right><strong><?= format_number_report($total_nilai_angsuran, $format_laporan) ?></strong></td>
					<td right><strong><?= format_number_report($total_dibayar, $format_laporan) ?></strong></td>
					<td right><strong><?= format_number_report($total_sisa, $format_laporan) ?></strong></td>
				</tr>

			</tbody>
			<tfoot></tfoot>
		</table>


	</body>

	</html>