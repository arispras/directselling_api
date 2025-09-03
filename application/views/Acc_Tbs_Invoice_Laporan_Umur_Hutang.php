<!DOCTYPEhtml>
	<html>

	<head>

		<?php require '__laporan_style.php' ?>
	</head>

	<body>

		<?php require '__laporan_header.php' ?>

		<h1 class="title">LAPORAN HUTANG SUPPLIER TBS</h1>
		<br>

		<div class="d-flex flex-between">
			<table class="" style="width:30%">
				<tr>
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
					<td>Tanggal Tempo</td>
					<th>:</th>
					<td><?= $filter_tgl_tempo  ?></td>
				</tr>
				<tr>
					<td>Status</td>
					<th>:</th>
					<td><?= $filter_status  ?></td>
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
					<th rowspan="2" style="width:2%">No</th>
					<th rowspan="2" style="width:10%">Supplier</th>
					<th colspan=<?= count($umur_hutang) ?>>Umur</th>
					</tr>
				<tr>
					
				
					<?php foreach ($umur_hutang as $key => $val) { ?>
						<th><?= ($key) ?></th>
					<?php } ?>


				</tr>


			</thead>

			<tbody>
				<?php $no = 0 ?>
				<?php foreach ($ap as $key => $val) { ?>

					<?php $no = $no + 1 ?>

					<tr>
						<td><?= $no ?></td>
						<td center><?= $val['nama_supplier'] ?></td>
						<?php foreach ($umur_hutang as $umur => $val_umur) { ?>

							<td center> <?= number_format($val[$umur]) ?></td>
						<?php } ?>

					</tr>
				<?php } ?>


			</tbody>

			<tfoot></tfoot>
		</table>






		<pre><?php //print_r($headeran) 
				?></pre>

	</body>

	</html>
