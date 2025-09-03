<!DOCTYPEhtml>
	<html>

	<head>
		<title>A.07 Purchase Order Cancelled</title>
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
		<?php
		if ($format_laporan == 'view') {
			require '__laporan_header.php';
		} elseif ($format_laporan == 'pdf') {
			require '__laporan_header.php';
		} else {
			echo '-';
		}
		?>


		<h3 class="title">A.07 Purchase Order Cancelled</h3>
		<br>

		<div class="d-flex flex-between">
			<table class="no_border" style="width:30%">
				<tr>
					<td style="width:25%">Periode Tanggal</td>
					<th>:</th>
					<td><?= tgl_indo($filter_tgl_awal) . ' s/d ' . tgl_indo($filter_tgl_akhir) ?></td>
				</tr>

			</table>
		</div>
		<br>

		<!-- table 1 -->
		<table class="table-bg border">
			<thead>
				<tr>
					<th>No</th>
					<th>No Order</th>
					<!-- <th>Request No</th> -->

					<th>Created Date</th>
					<th>Description</th>
					<th>Vendor</th>
					<th>Subtotal</th>
					<th>PPN</th>
					<th>Others</th>
					<th>Total Value (RP)</th>
				</tr>
			</thead>

			<tbody>
				<?php
				$ttl_grand = 0;
				$ttl_subtotal = 0;
				$ttl_ppn = 0;
				$ttl_other = 0;
				?>
				<?php foreach ($po as $key => $val) { ?>


					<tr>
						<td bold colspan=9> Status :
							<?php
							if ($val['statuss'] == null) {
								echo 'Menunggu Approval ';
							} else {
								echo $val['statuss'];
							}
							?>
						</td>
					</tr>

					<?php $dt = $val['detail']; ?>

					<?php
					$no = 0;
					$jml_grand = 0;
					$jml_subtotal = 0;
					$jml_ppn = 0;
					$jml_other = 0;
					?>

					<?php foreach ($dt as $key => $res) { ?>

						<?php
						$no++;
						$jml_grand = $jml_grand + $res['grand_total'];
						$jml_subtotal = $jml_subtotal + $res['sub_total'];
						$jml_ppn = $jml_ppn + (($res['ppn'] / 100) * $res['sub_total']);
						$jml_other = $jml_other + $res['biaya_lain'];
						?>
						<tr>
							<td center width="2%"><?= $no ?></td>
							<td left width="10%"><?= $res['no_po'] ?></td>
							<!-- <td left width="8%"><?= $res['no_pp'] ?></td> -->
							<td center width="5%"><?php
													if ($val['statuss'] == 'RELEASE') {
														echo date_format(date_create($res['tgl_approve1']), 'd-m-Y');
													} elseif ($val['statuss'] == 'REJECTED') {
														echo date_format(date_create($res['dibuat_tanggal']), 'd-m-Y');
													} elseif ($val['statuss'] == 'CREATED') {
														echo date_format(date_create($res['dibuat_tanggal']), 'd-m-Y');
													} else {
														echo date_format(date_create($res['dibuat_tanggal']), 'd-m-Y');
													}
													?>
							</td>

							<td left width="15%"><?= $res['catatan'] ?></td>
							<td center width="12%"><?= $res['nama_supplier'] ?></td>
							<td right width="5%"><?= number_format($res['sub_total'], 2) ?></td>
							<td right width="5%"><?= number_format(($res['ppn'] / 100) * $res['sub_total'], 2) ?></td>
							<td right width="5%"><?= number_format($res['biaya_lain'], 2) ?></td>
							<td right width="8%"><?= number_format($res['grand_total'], 2) ?></td>

						</tr>
					<?php } ?>
					<!-- <tr>
						<td colspan='5' right>Sub Total </td>
						<td right><?= number_format($jml_subtotal, 2) ?></td>
						<td right><?= number_format($jml_ppn, 2) ?></td>
						<td right><?= number_format($jml_other, 2) ?></td>
						<td right><?= number_format($jml_grand, 2) ?></td>
					</tr> -->
					<?php

					$ttl_grand = $jml_grand + $ttl_grand;
					$ttl_subtotal = $jml_subtotal + $ttl_subtotal;
					$ttl_ppn = $jml_ppn + $ttl_ppn;
					$ttl_other = $jml_other + $ttl_other;
					?>
				<?php } ?>

				<tr>
					<td colspan='5' right>TOTAL </td>
					<td right><?= number_format($ttl_subtotal, 2) ?></td>
					<td right><?= number_format($ttl_ppn, 2) ?></td>
					<td right><?= number_format($ttl_other, 2) ?></td>
					<td right><?= number_format($ttl_grand, 2) ?></td>
				</tr>
			</tbody>
		</table>
		<!-- tutup table -->
		<br>
		<br>
		<hr>


		<pre><?php //print_r($headeran) 
				?></pre>

	</body>

	</html>