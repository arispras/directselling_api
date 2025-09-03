<!DOCTYPEhtml>
	<html>

	<head>
		<title>A.03 Purchase Order by Category</title>
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
				font-size: 10px;
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

		<!-- <pre><?php print_r($po); ?></pre> -->

		<h3 class="title">A.03 Purchase Order by Category</h3>
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
					<th>Request No</th>
					<th>Date</th>
					<th>Lokasi</th>
					<th>kat</th>
					<th>Kode</th>
					<th>Nama</th>
					<th>Qty</th>
					<th>Total Value(RP)</th>
				</tr>

			</thead>


			<tbody>
			<?php
					$g_ttl = 0;
					$g_ttl_qty = 0;	?>	
				<?php foreach ($po as $key => $val) { ?>
					<?php $supp = $val['supp']; ?>

					<tr>
						<td bold colspan=10><?= $val['kategori'] ?></td>
					</tr>
					<?php
					$ttl = 0;
					$ttl_qty = 0;	?>
					<?php foreach ($supp as $key => $value) { ?>
						<?php $dt = $value['it']; ?>

						<tr>
							<td bold colspan=10><?= $value['nama_supplier'] ?></td>
						</tr>

						<?php
						$no = 0;
						$jml_ttl = 0;
						$jml_qty = 0;
						?>
						<?php foreach ($dt as $key => $res) { ?>

							<?php
							$no++;
							$jml_ttl = $jml_ttl + $res['total'];
							$jml_qty = $jml_qty + $res['qty'];
							?>
							<tr>
								<td center width="2%"><?= $no ?></td>
								<td left width="10%"><?= $res['no_po'] ?></td>
								<td left width="8%"><?= $res['no_pp'] ?></td>
								<td center width="5%"><?= tgl_indo_normal($res['tanggal']) ?></td>
								<td center width="13%"><?= $res['gudang'] ?></td>
								<td center width="17%"><?= $res['kategori'] ?></td>
								<td center width="5%"><?= $res['kode_item'] ?></td>
								<td left width="17%"><?= $res['nama_item'] ?></td>
								<td right width="5%"><?= number_format($res['qty']) ?></td>
								<td right width="8%"><?= number_format($res['total']) ?></td>

							</tr>
						<?php } ?>
						<tr>
							<td bold colspan='8' right>TOTAL <?= $res['nama_supplier'] ?></td>
							<td bold right><?= number_format($jml_qty) ?></td>
							<td bold right><?= number_format($jml_ttl) ?></td>
						</tr>
						<?php
						$ttl = $ttl + $jml_ttl;
						$ttl_qty = $ttl_qty + $jml_qty;
						?>
					<?php } ?>
					<tr>
						<td bold colspan='8' right>TOTAL <?= $val['kategori'] ?></td>
						<td bold right><?= number_format($ttl_qty) ?></td>
						<td bold right><?= number_format($ttl) ?></td>
					</tr>
					<?php
						$g_ttl = $g_ttl + $ttl;
						$g_ttl_qty = $g_ttl_qty +$ttl_qty;
						?>

				<?php } ?>

				<tr>
						<td bold colspan='8' right>GRAND TOTAL </td>
						<td bold right><?= number_format($g_ttl_qty) ?></td>
						<td bold right><?= number_format($g_ttl) ?></td>
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