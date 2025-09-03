<!DOCTYPEhtml>
	<html>

	<head>
		<title>Laporan Invoice</title>
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
	</head>

	<body>

		<?php require '__laporan_header.php' ?>

		<!-- <pre><?php print_r($po) ?></pre> -->

		<h3 class="title">LAPORAN INVOICE JALAN JALAN</h3>
		<br>

		<div class="d-flex flex-between">
			<table class="no_border" style="width:30%">
				<!-- <tr>
					<td>Lokasi</td>
					<th>:</th>
					<td><?= $filter_lokasi ?></td>
				</tr>
				<tr>
					<td>supplier</td>
					<th>:</th>
					<td><?= $filter_supplier ?></td>
				</tr>
				<tr>
					<td>Gudang</td>
					<th>:</th>
					<td><?= $filter_gudang ?></td>
				</tr> -->
				<tr>
					<td>Periode Tanggal</td>
					<th>:</th>
					<td><?= tgl_indo($filter_tgl_awal) . ' s/d ' . tgl_indo($filter_tgl_akhir) ?></td>
				</tr>

			</table>
		</div>

		<div class="d-flex flex-between">
			<table class="" style="width:30%">


			</table>
		</div>
		<br>


		<table class="table-bg border">
			<thead>
				<tr>
					<!-- <th style="width:2%">No</th> -->
					<th style="width:10%">NoTransaksi</th>
					<th>Tanggal</th>
					<th style="width:10%">Poli</th>
					<th>Pasien</th>
					<th>NoRef</th>
					<th style="width:19%">Jenis Biaya</th>
					<th style="width:7%">Jumlah</th>
				</tr>

			</thead>


			<tbody>
				<?php $no = 0;
				$total = 0;
				?>
				<?php foreach ($invoice as $key => $val) { ?>
					<?php
					$no = $no + 1;
					$dt = $val['dt'];
					$countRow = Count($dt);
					$subtotal = 0;
					$numb = 0;
					?>
					<?php foreach ($dt as $key2 => $d) { ?>
						<?php
						$numb++;
						$subtotal = $subtotal + $d['harga'];
						$total = $total + $d['harga'];

						?>
						<?php
						if ($numb == 1) {
						?>
							<tr>
								<!-- <td><?= $no ?></td> -->
								<td rowspan=<?= $countRow ?> left><?= $d['no_transaksi'] ?></td>
								<td rowspan=<?= $countRow ?> center><?= tgl_indo_normal($d['tanggal']) ?></td>
								<td rowspan=<?= $countRow ?> center><?= $d['nama_poli'] ?></td>
								<td rowspan=<?= $countRow ?> center><?= $d['nama_pasien'] ?></td>
								<td rowspan=<?= $countRow ?> left><?= $d['no_rj'] ?></td>
								<td center><?= $d['nama_biaya'] ?></td>
								<td right><?= number_format($d['harga'], 2) ?></td>


							</tr>
						<?php
						} else {
						?>
							<tr>
								<td center><?= $d['nama_biaya'] ?></td>
								<td right><?= number_format($d['harga'], 2) ?></td>


							</tr>

						<?php
						}
						?>

					<?php } ?>
					<tr>
						<td colspan="6" left><strong> SubTotal</strong></td>
						<td right><strong><?= number_format($subtotal,2) ?></strong></td>
					</tr>
				<?php } ?>


			</tbody>

			<tfoot>
				<tr>
					<td colspan="6" left><strong> TOTAL</strong></td>
					<td right><strong><?= number_format($total,2) ?></strong></td>


				</tr>
			</tfoot>
		</table>






		<pre><?php //print_r($headeran) 
				?></pre>

	</body>

	</html>