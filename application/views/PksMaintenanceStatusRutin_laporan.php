<!DOCTYPEhtml>
	<html>

	<head>
	
	<?php require '_laporan_style_fix.php';
		?>
	</head>

	<body>

		<?php require '_laporan_header.php' ?>

		<div>
			<h1 center class="title">Status Maintence Rutin</h1>
			<!-- <p center><?= $header['no_transaksi'] ?></p> -->
		</div>
		<br>

		<!-- <div class="d-flex flex-between">
			<div style="width:100%">
				<p><b>Mill :</b> <?= $header['mill'] ?></p>
				<p><b>Tanggal :</b> <?= tgl_indo($header['tanggal']) ?></p>
				<p><b>Total Jam Proses :</b> <?= $header['total_jam_proses'] ?></p>
				<p><b>Total Jumlah Rebusan :</b> <?= $header['total_jumlah_rebusan'] ?></p>
				<p><b>TBS Olah :</b> <?= $header['tbs_olah'] ?></p>	
			</div>
		</div> -->
		<br>


	
		<table class="table-bg border">
			<thead>
				<tr>
					<th>No</th>
					<th>Kode Mesin</th>
					<th>Nama Mesin</th>
					<th>Maintence Rutin</th>
					<th>Status</th>
					<th>HmKm Terakhir</th>
					<th>HmKm service Terakhir</th>
					<th>HmKm Rutin</th>
					<th>HmKm terlewat</th>
				</tr>
			</thead>
			<tbody>
				<?php $no = 0 ?>
				<?php foreach ($data_result as $key => $val) { ?>
					<?php $no = $no + 1 ?>
					<tr>
						<td center width="2%"><?= $no ?></td>
						<td width="10%"><?= $val['mesin']['kode_mesin'] ?></td>
						<td width="10%"><?= $val['mesin']['nama_mesin'] ?></td>
						<td width="10%"><?= $val['mesin']['nama_maintenance'] ?></td>
						<td center width="10%"><?= $val['status'] ?></td>
						<td right width="10%"><?= $val['last_hmkm'] ?></td>
						<td right width="10%"><?= $val['last_service_hmkm'] ?></td>
						<td right width="10%"><?= $val['kmhm_rutin_service'] ?></td>
						<td right width="10%"><?= $val['kmhm_lewat_service'] ?></td>
					</tr>
				<?php } ?>
			</tbody>
			<tfoot></tfoot>
		</table>
		<br>

		

		<pre><?php //print_r($detail) ?></pre>

	</body>

	</html>
