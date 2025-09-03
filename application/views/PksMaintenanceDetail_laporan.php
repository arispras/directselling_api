<!DOCTYPEhtml>
	<html>

	<head>
	
		<?php require '_laporan_style_fix.php' ?>
	</head>

	<body>

		<?php require '_laporan_header.php' ?>

		<div>
			<h1 center class="title">Laporan Maintence Detail</h1>
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
					<th>Tanggal</th>
					<th>Kode Mesin</th>
					<th>Nama Mesin</th>
					<th>Maintence</th>
					<th>HmKm</th>
					<th>Keterangan</th>
					
				</tr>
			</thead>
			<tbody>
				<?php $no = 0 ?>
				<?php foreach ($data_result as $key => $val) { ?>
					<?php $no = $no + 1 ?>
					<tr>
						<td center width="2%"><?= $no ?></td>
						<td width="10%"><?= tgl_indo_normal($val['tanggal']) ?></td>
						<td width="10%"><?= $val['kode_mesin'] ?></td>
						<td width="10%"><?= $val['nama_mesin'] ?></td>
						<td width="10%"><?= $val['nama_maintenance'] ?></td>
						<td right width="10%"><?= $val['hm_km'] ?></td>
						<td width="10%"><?= $val['ket'] ?></td>
						
					</tr>
				<?php } ?>
			</tbody>
			<tfoot></tfoot>
		</table>
		<br>

		

		<pre><?php //print_r($detail) ?></pre>

	</body>

	</html>
