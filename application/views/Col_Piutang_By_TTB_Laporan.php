<!DOCTYPEhtml>
	<html>

	<head>
		<title>LHI  Rakap</title>
	<?php	function format_number_report($angka,$fmt_laporan)
	{
		$format_laporan     =$fmt_laporan;
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

		<h3 class="title">Piutang REKAP</h3>
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
					<th style="width:12%">Tanggal</th>
					<th style="width:12%">Customer</th>
					<th style="width:12%">Nilai</th>
					<th style="width:12%">Dibayar</th>
					<th style="width:12%">Sisa</th>

				</tr>

			</thead>
			<tbody>
				<?php 
				$no = 0;
				$jum_dibayar = 0;
				$jum_qty = 0;
				$jum_dp = 0;
				$jum_total= 0;
				$jum_nilai_ttb = 0;
				$jum_nilai_angsuran = 0;
				
				
				?>


					<?php foreach ($data as $key => $res) { ?>
						<tr>
							<?php
							$no = $no + 1;
							$jum_dibayar += $res['dibayar'];
							$jum_nilai_ttb += $res['nilai_ttb'];
							?>
							<td center> <?= $no ?> </td>
							<td left><?= $res['no_ttb'] ?></td>
							<td center><?=  tgl_indo($res['tanggal_ttb']) ?></td>
							<td left><?= $res['nama_customer'] ?></td>
							<td right><?= format_number_report($res['nilai_ttb'],$format_laporan) ?></td>
							<td right><?= format_number_report($res['dibayar'],$format_laporan) ?></td>
							<td right><?= format_number_report($res['nilai_ttb'] - $res['dibayar'],$format_laporan) ?></td>
							
								
						</tr>
					<?php } ?>

	
				<tr>
					<td colspan="4"></td>
					<td right><b><?= format_number_report($jum_nilai_ttb,$format_laporan) ?></b></td>
					<td right><b><?= format_number_report($jum_dibayar,$format_laporan) ?></b></td>	
					<td right><b><?= format_number_report($jum_nilai_ttb - $jum_dibayar,$format_laporan) ?></b></td>	
					
				</tr>

			</tbody>
			<tfoot></tfoot>
		</table>






		<pre><?php //print_r($headeran) 
				?></pre>

	</body>

	</html>