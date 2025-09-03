<!DOCTYPEhtml>
	<html>

	<head>
		<title>Sales Order Detail</title>
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

		<h3 class="title">A.02 Sales Order (DETAIL)</h3>
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
					<th style="width:2%">no</th>
					<th style="width:9%">Order No</th>
					<th style="width:6%">Request No</th>
					<th style="width:12%">Customer</th>
					<th style="width:5%">Item Code</th>
					<th style="width:15%">Item Name</th>
					<th style="width:5%">Qty</th>
					<th style="width:5%">Price(RP)</th>
					<th style="width:5%">Amount(RP)</th>
					<th style="width:5%">Status</th>
				</tr>

			</thead>
			<tbody>
				<?php $no = 0;
				$sum = 0;
				$j_qty = 0; ?>
				<?php foreach ($so as $key => $val) { ?>

					<?php $dt = $val['detail']; ?>

					<?php foreach ($dt as $key => $res) { ?>
						<tr>
							<?php
							$no = $no + 1;
							$sum = $sum + ($res['qty_order']*$res['harga_jual']);
							$j_qty = $j_qty + $res['qty_order'];
							?>
							<!-- <td center rowspan=<?php echo count($dt); ?>> <?= $no ?> </td>
							<td left rowspan=<?php echo count($dt); ?>> <?= $val['no_so'] ?></td> -->
							<td center> <?= $no ?> </td>
							<td left> <?= $val['no_so'] ?></td>
							<td center><?= $res['no_pp'] ?></td>
							<td left><?= $res['nama_customer'] ?></td>
							<td center><?= $res['kode_item'] ?></td>
							<td left><?= $res['nama_item'] ?></td>
							<td right><?= format_number_report($res['qty_order'],$format_laporan) ?></td>
							<td right><?= format_number_report($res['harga_jual'],$format_laporan) ?></td>
							<td right><?= format_number_report($res['total_nilai_penjualan'],$format_laporan) ?></td>
							<td left><?= $res['status'] ?></td>
						</tr>
					<?php } ?>

				<?php } ?>
				<tr>
					<td colspan=6></td>
					<td right><?= format_number_report($j_qty,$format_laporan) ?></td>
					<td right></td>
					<td right><?= format_number_report($sum,$format_laporan) ?></td>
				</tr>

			</tbody>
			<tfoot></tfoot>
		</table>






		<pre><?php //print_r($headeran) 
				?></pre>

	</body>

	</html>