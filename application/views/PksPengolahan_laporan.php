<!DOCTYPEhtml>
	<html>

	<head>
	
		<?php require '__laporan_style.php' ?>
	</head>

	<body>

		<?php require '_laporan_header.php' ?>

		<div>
			<h1 center class="title">Pengolahan</h1>
			<p center><?= $header['no_transaksi'] ?></p>
		</div>
		<br>

		<div class="d-flex flex-between">
			<div style="width:100%">
				<p><b>Mill :</b> <?= $header['mill'] ?></p>
				<p><b>Tanggal :</b> <?= tgl_indo($header['tanggal']) ?></p>
				<p><b>Total Jam Proses :</b> <?= $header['total_jam_proses'] ?></p>
				<p><b>Total Jumlah Rebusan :</b> <?= $header['total_jumlah_rebusan'] ?></p>
				<p><b>TBS Olah :</b> <?= $header['tbs_olah'] ?></p>	
			</div>
		</div>
		<br>


		<b>Details :</b>
		<table class="table-bg border">
			<thead>
				<tr>
					<th>No</th>
					<th>Shift</th>
					<th>Jam Masuk</th>
					<th>Jam Selesai</th>
					<th>Mandor</th>
					<th>Asisten</th>
					<th>Jam Proses</th>
					<th>Jumlah Rebusan</th>
				</tr>
			</thead>
			<tbody>
				<?php $no = 0 ?>
				<?php foreach ($detail as $key => $val) { ?>
					<?php $no = $no + 1 ?>
					<tr>
						<td center width="2%"><?= $no ?></td>
						<td width="10%"><?= $val['shift'] ?></td>
						<td center width="10%"><?= $val['jam_masuk'] ?></td>
						<td center width="10%"><?= $val['jam_selesai'] ?></td>
						<td width="10%"><?= $val['mandor'] ?></td>
						<td width="10%"><?= $val['asisten'] ?></td>
						<td center width="10%"><?= $val['jam_proses'] ?></td>
						<td center width="10%"><?= $val['jumlah_rebusan'] ?></td>
					</tr>
				<?php } ?>
			</tbody>
			<tfoot></tfoot>
		</table>
		<br>

		

		<b>Details Mesin :</b>
		<table class="table-bg border">
			<thead>
				<tr>
					<th>No</th>
					<th>Mesin</th>
					<th>Jam Masuk</th>
					<th>Jam Selesai</th>
					<th>Jumlah Jam</th>
					<th>Keterangan</th>
				</tr>
			</thead>
			<tbody>
				<?php $no = 0 ?>
				<?php foreach ($mesin as $key => $val) { ?>
					<?php $no = $no + 1 ?>
					<tr>
						<td center width="2%"><?= $no ?></td>
						<td width="10%"><?= $val['mesin'] ?></td>
						<td center width="10%"><?= $val['jam_masuk'] ?></td>
						<td center width="10%"><?= $val['jam_selesai'] ?></td>
						<td center width="10%"><?= $val['jumlah_jam'] ?></td>
						<td center width="10%"><?= $val['keterangan'] ?></td>
					</tr>
				<?php } ?>
			</tbody>
			<tfoot></tfoot>
		</table>




		<pre><?php //print_r($detail) ?></pre>

	</body>

	</html>
