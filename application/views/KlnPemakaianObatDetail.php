<!DOCTYPEhtml>
	<html>

	<head>
	<title>Laporan Pemakaian Obat</title>
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

		<h3 class="title">LAPORAN PEMAKAIAN OBAT</h3>
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
					<th style="width:2%">No</th>
					<th style="width:10%">NoTransaksi</th>
					<th>Tanggal</th>
					<th style="width:10%">Pasien</th>
					<th>Kode Barang</th>
					<th style="width:19%">Nama Barang</th>
					<th style="width:5%">Qty</th>
					<th>Satuan</th>
					<th style="width:7%">Harga</th>
					<th style="width:7%">Jumlah</th>
				</tr>

			</thead>


			<tbody>
				<?php $no = 0 ?>
				<?php foreach ($pemakaianObat as $key => $val) { ?>

					<?php $no = $no + 1 ?>
					<tr>
						<td><?= $no ?></td>

						<td left><?= $val['no_transaksi'] ?></td>
						<td center><?= tgl_indo_normal($val['tanggal']) ?></td>
						<td center><?= $val['nama_pasien'] ?></td>
						<td center><?= $val['kode_obat'] ?></td>
						<td left><?= $val['nama_obat'] ?></td>
						<td right><?= number_format( $val['qty'],2) ?></td>
						<td center><?= $val['uom'] ?></td>
						<td center><?=number_format( $val['harga'],2) ?></td>
						<td center><?=  number_format($val['harga'] * $val['qty'],2) ?></td>

					</tr>
				<?php } ?>


			</tbody>

			<tfoot></tfoot>
		</table>






		<pre><?php //print_r($headeran) 
				?></pre>

	</body>

	</html>