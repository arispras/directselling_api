<!DOCTYPEhtml>
<html>
	<head>
	
		<?php require '__laporan_style.php' ?>
	</head>
	<body>

		<?php require '__laporan_header.php' ?>
		

		<h3 class="title">Laporan Uang Muka Realisasi</h3>
		<br>
		
		<div class="d-flex flex-between">
			<table class="" style="width:30%">
				<tr>
					<td style="width:30%">Lokasi</td>
					<th>:</th>
					<td><?= $input['lokasi'] ?></td>
				</tr>
				<tr>
					<td>Tanggal Mulai</td>
					<th>:</th>
					<td><?= tgl_indo($input['tgl_mulai']) ?></td>
				</tr>
				<tr>
					<td>Tanggal Akhir</td>
					<th>:</th>
					<td><?= tgl_indo($input['tgl_akhir']) ?></td>
				</tr>
			</table>

			<!-- <table class="border" style="width:30%"></table> -->
		</div>

		
		<br><br>

		
		<table class="table-bg border">
			
			<thead>
				<tr>
					<th>No</th>
					<th>Tanggal</th>
					<th>No Transaksi</th>
					<th>Akun Uang Muka</th>
					<th>Akun Kasbank</th>
					<th>Akun Realisasi</th>
					<th>nilai Uang Muka</th>
					<th>nilai Realisasi</th>
					<th>keterangan</th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($data as $key=>$val) { ?>
				<?php $no += 1 ?>
				<tr>
					
					<td><?= $no ?></td>
					<td><?= $val['tanggal'] ?></td>
					<td><?= $val['no_transaksi'] ?></td>
					<td><?= $val['acc_akun_uang_muka'] ?></td>
					<td><?= $val['acc_akun_kasbank'] ?></td>
					<td><?= $val['acc_akun_realisasi'] ?></td>
					<td><?=number_format( $val['nilai_uang_muka']) ?></td>
					<td><?= number_format($val['nilai_realisasi']) ?></td>
					<td><?= $val['keterangan'] ?></td>

				</tr>
				<?php } ?>
			</tbody>

			<tfoot></tfoot>

		</table>


		<pre><?php //print_r($absensi) ?></pre>

	</body>
</html>
