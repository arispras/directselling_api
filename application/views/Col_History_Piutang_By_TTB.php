<!DOCTYPEhtml>
	<html>

	<head>
		<title>history PIUTANG</title>
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

		<h3 class="title">HISTORY PIUTANG</h3>
		<br>

		<div class="d-flex flex-between">
			<table class="no_border" style="width:30%">
				<tr>
					<td>Lokasi</td>
					<td>:</td>
					<td><?= $filter_lokasi ?></td>
				</tr>
				<tr>
					<td> No TTB</td>
					<td>:</td>
					<td><?= $header['no_ttb'] ?></td>
				</tr>

				<tr>
					<td> Tanggal TTB</td>
					<td>:</td>
					<td><?= tgl_indo($header['tanggal'])  ?></td>
				</tr>
				<tr>
					<td> Customer</td>
					<td>:</td>
					<td><?= $header['nama_customer'] ?></td>
				</tr>

			</table>
		</div>
		<br>
		<?php
		$sisa_piutang_ttb = $header['sub_total'] - $header['total_dp'] - $rekap['nilai_dibayar'] - $rekap['nilai_tb'];
		$pendapatan_lain = $rekap['sisa_piutang'] - $sisa_piutang_ttb;
		$jum_pendapatan_lain += $pendapatan_lain;

		?>

		<table class="table-bg border">
			<thead>
				<tr>
					<th style="width:2%">No</th>
					<th style="width:9%">Keterangan</th>
					<th style="width:9%">Nomor</th>
					<th style="width:12%">Tanggal</th>
					<th style="width:12%">Nilai</th>
					<th style="width:12%"> Saldo Piutang</th>


				</tr>

			</thead>
			<tbody>
				<?php
				$no = 0;
				$jum_dibayar = 0;
				$jum_qty = 0;
				$jum_dp = 0;
				$jum_sub_total = 0;

				$jum_nilai_ttb = 0;
				$jum_nilai_tb = 0;
				$jum_nilai_angsuran = 0;
				$jum_pendapatan_lain = 0;
				$jum_total_dp = 0;
				$jum_sisa_piutang = 0;

				?>
				<?php foreach ($data as $key => $res) { ?>
					<tr>
						<?php
						$no = $no + 1;

						if ($res['tipe'] == 'ttb') {
							$nilai_rp = $res['nilai'];
						} else if ($res['tipe'] == 'tarik_barang') {
							$nilai_rp = $res['nilai'] * -1;
						} else if ($res['tipe'] == 'lhi') {
							$nilai_rp = $res['nilai'] * -1;
						} else if ($res['tipe'] == 'dp') {
							$nilai_rp = $res['nilai'] * -1;
						}
						$saldo_piutang += $nilai_rp;
						?>
						<td center> <?= $no ?> </td>
						<td left><?= $res['keterangan'] ?></td>
						<td left><?= $res['nomor'] ?></td>
						<td center><?= tgl_indo($res['tanggal']) ?></td>
						<td right><?= format_number_report($nilai_rp, $format_laporan) ?></td>
						<td right><?= format_number_report($saldo_piutang, $format_laporan) ?></td>

					</tr>
				<?php } ?>
				<?php if ($pendapatan_lain<>0) { ?>
				<tr>
						<?php
						$no = $no + 1;

						
						$nilai_rp = $pendapatan_lain;
						
						$saldo_piutang += $nilai_rp;
						?>
						<td center> <?= $no ?> </td>
						<td left><?= 'Pendapatan Lain' ?></td>
						<td left></td>
						<td center></td>
						<td right><?= format_number_report($nilai_rp, $format_laporan) ?></td>
						<td right><?= format_number_report($saldo_piutang, $format_laporan) ?></td>

					</tr>
				<?php } ?>



			</tbody>
			<tfoot></tfoot>
		</table>
		<br>
		<hr>



		<table style="width:50%">
			<thead>
				<tr>
				<tr>

					<th style="width:12%">Nilai TTB</th>
					<th style="width:12%">Angs.I (DP)</th>
					<th style="width:12%">Dibayar</th>
					<th style="width:12%">Nilai Tarik Barang</th>
					<th style="width:12%">Pendapatan Lain</th>
					<th style="width:12%">Sisa Piutang</th>

				</tr>
			</thead>

			<td right><?= format_number_report($header['sub_total'], $format_laporan) ?></td>
			<td right><?= format_number_report($header['total_dp'], $format_laporan) ?></td>
			<td right><?= format_number_report($rekap['nilai_dibayar'], $format_laporan) ?></td>
			<td right><?= format_number_report($rekap['nilai_tb'], $format_laporan) ?></td>
			<td right><?= format_number_report($pendapatan_lain, $format_laporan) ?></td>
			<td right><?= format_number_report($rekap['sisa_piutang'], $format_laporan) ?></td>
			</tr>


		</table>



	</body>

	</html>