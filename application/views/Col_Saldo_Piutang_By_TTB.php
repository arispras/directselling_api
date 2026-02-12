<!DOCTYPEhtml>
	<html>

	<head>
		<title>SALDO PIUTANG</title>
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

		<h3 class="title">SALDO REKAP PIUTANG</h3>
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
					<th style="width:9%">No TTB</th>
					<th style="width:12%">Tanggal</th>
					<th style="width:12%">Customer</th>
					<th style="width:12%">Nilai TTB</th>
					<th style="width:12%">Angs.I (DP)</th>
					<th style="width:12%">Dibayar</th>
					<th style="width:12%">Nilai Tarik Barang</th>
					<th style="width:12%">Pendapatan Lain</th>
					<th style="width:12%">Sisa Piutang</th>

				</tr>

			</thead>
			<tbody>
				<?php 
				$no = 0;
				$jum_dibayar = 0;
				$jum_qty = 0;
				$jum_dp = 0;
				$jum_sub_total= 0;

				$jum_nilai_ttb = 0;
				$jum_nilai_tb = 0;
				$jum_nilai_angsuran = 0;
				$jum_pendapatan_lain = 0;
				$jum_total_dp=0;
				$jum_sisa_piutang=0;
				
				
				?>


					<?php foreach ($data as $key => $res) { ?>
						<tr>
							<?php
							$no = $no + 1;
							$sisa_piutang_ttb=$res['sub_total']-$res['total_dp']-$res['nilai_dibayar']-$res['nilai_tb'];
							$jum_dibayar += $res['nilai_dibayar'];
							$jum_sub_total += $res['sub_total'];
							$jum_nilai_tb += $res['nilai_tb'];
							$pendapatan_lain=$res['sisa_piutang']-$sisa_piutang_ttb;
							$jum_pendapatan_lain+=$pendapatan_lain;
							$jum_sisa_piutang+=$res['sisa_piutang'];
							$jum_total_dp+=$res['total_dp'];
							?>
							<td center> <?= $no ?> </td>
							<td left><?= $res['no_ttb'] ?></td>
							<td center><?=  tgl_indo($res['tanggal']) ?></td>
							<td left><?= $res['nama_customer'] ?></td>
							<td right><?= format_number_report($res['sub_total'],$format_laporan) ?></td>
							<td right><?= format_number_report($res['total_dp'],$format_laporan) ?></td>							
							<td right><?= format_number_report($res['nilai_dibayar'],$format_laporan) ?></td>
							<td right><?= format_number_report($res['nilai_tb'],$format_laporan) ?></td>
							<td right><?= format_number_report($pendapatan_lain,$format_laporan) ?></td>						
							<td right><?= format_number_report($res['sisa_piutang'],$format_laporan) ?></td>
							
								
						</tr>
					<?php } ?>

	
				<tr>
					<td colspan="4"></td>
					<td right><b><?= format_number_report($jum_sub_total,$format_laporan) ?></b></td>
					<td right><b><?= format_number_report($jum_total_dp,$format_laporan) ?></b></td>	
					<td right><b><?= format_number_report($jum_dibayar,$format_laporan) ?></b></td>	
					
					<td right><b><?= format_number_report($jum_nilai_tb,$format_laporan) ?></b></td>	
					<td right><b><?= format_number_report($jum_pendapatan_lain,$format_laporan) ?></b></td>	
					<td right><b><?= format_number_report($jum_sisa_piutang,$format_laporan) ?></b></td>	
					
				</tr>

			</tbody>
			<tfoot></tfoot>
		</table>






		<pre><?php //print_r($headeran) 
				?></pre>

	</body>

	</html>