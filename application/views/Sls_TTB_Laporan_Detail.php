<!DOCTYPEhtml>
	<html>

	<head>
		<title>TTB  Detail</title>
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

		<h3 class="title">TTB DETAIL</h3>
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
					<th style="width:9%">NoTTB</th>
					<th style="width:9%">Tanggal</th>
					<th style="width:6%">No SO</th>
					<th style="width:12%">Customer</th>
					<th style="width:5%">Kode Item</th>
					<th style="width:15%">Nama Item</th>
					<th style="width:5%">Qty</th>
					<th style="width:5%">Harga</th>
					<th style="width:5%">Diskon</th>
					<th style="width:5%">Total</th>
					<th style="width:5%">Dp</th>
					<th style="width:5%">Nilai Piutang</th>
					<th style="width:5%">@angsuran</th>
				</tr>

			</thead>
			<tbody>
				<?php 
				$no = 0;
				$jum_diskon = 0;
				$jum_qty = 0;
				$jum_dp = 0;
				$jum_total= 0;
				$jum_nilai_piutang = 0;
				$jum_nilai_angsuran = 0;
				
				
				?>


					<?php foreach ($data as $key => $res) { ?>
						<tr>
							<?php
							$no = $no + 1;
							$jum_diskon += $res['diskon'];
							$jum_dp += $res['dp'];
							$jum_total += $res['total'];	
							$jum_nilai_piutang += $res['nilai_piutang'];
							$jum_nilai_angsuran += $res['nilai_angsuran'];
							$jum_qty += $res['qty'];
							?>
							<!-- <td center rowspan=<?php echo count($dt); ?>> <?= $no ?> </td>
							<td left rowspan=<?php echo count($dt); ?>> <?= $val['no_so'] ?></td> -->
							<td center> <?= $no ?> </td>
							<td left> <?= $res['no_ttb'] ?></td>
							<td center><?=  tgl_indo($res['tanggal']) ?></td>
							<td center><?= $res['no_so'] ?></td>
							<td left><?= $res['nama_customer'] ?></td>
							<td center><?= $res['kode_item'] ?></td>
							<td left><?= $res['nama_item'] ?></td>
							<td right><?= format_number_report($res['qty'],$format_laporan) ?></td>
							<td right><?= format_number_report($res['harga'],$format_laporan) ?></td>
							<td right><?= format_number_report($res['diskon'],$format_laporan) ?></td>
							<td right><?= format_number_report($res['total'],$format_laporan) ?></td>
							<td right><?= format_number_report($res['dp'],$format_laporan) ?></td>
							<td right><?= format_number_report($res['nilai_piutang'],$format_laporan) ?></td>
							<td right><?= format_number_report($res['nilai_angsuran'],$format_laporan) ?></td>
							
						</tr>
					<?php } ?>

	
				<tr>
					<td colspan=7></td>
					<td right><?= format_number_report($jum_qty,$format_laporan) ?></td>
					<td right></td>
					<td right><?= format_number_report($jum_diskon,$format_laporan) ?></td>
					
					<td right><?= format_number_report($jum_dp,$format_laporan) ?></td>
					<td right><?= format_number_report($jum_total,$format_laporan) ?></td>
					<td right><?= format_number_report($jum_nilai_piutang,$format_laporan) ?></td>
					<td right><?= format_number_report($jum_nilai_angsuran,$format_laporan) ?></td>
				</tr>

			</tbody>
			<tfoot></tfoot>
		</table>






		<pre><?php //print_r($headeran) 
				?></pre>

	</body>

	</html>