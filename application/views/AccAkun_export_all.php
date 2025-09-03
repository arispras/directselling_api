<!DOCTYPEhtml>
	<html>

	<head>
		

		<?php require '__laporan_style.php' ?>
	</head>

	<body>

		<!-- <div>
			<h1 center>KLINIK ANNAJAH</h1>
		</div> -->
		<?php require '__laporan_header.php' ?>

		<div center>
			<h3 style="margin:0;">Master Data Akun</h3>
			<p style="margin:0;"></p>
		</div>
		<br><br>


		<table class="table-bg border">
			<thead>
				<tr>
					<th style="width:5%">No.</th>
					<th style="width:15%">Kode Akun</th>
					<th>Nama Akun</th>
				</tr>
			</thead>
			<tbody>
				<?php $no = 0; ?>
				<?php foreach ($data as $row) { ?>
					<?php $no = $no + 1; ?>
					<tr>
						<td><?= $no ?></td>
						<td right> <?= $row['kode'] ?></td>
						<td><?= $row['nama'] ?></td>

					</tr>
					<!-- <?php if ($row['is_transaksi_akun']) { ?>
						<tr>
							<td ><?= $no ?></td>
							<td right> <?= $row['kode'] ?></td>
							<td><?= $row['nama'] ?></td>

						</tr>
					<?php } else { ?>
						<tr>
							<td ><?= $no ?></td>
							<td right><b> <?= $row['kode'] ?></b></td>
							<td><b><?= $row['nama'] ?></b></td>
						</tr>
					<?php } ?> -->

				<?php } ?>
			</tbody>
		</table>


	</body>

	</html>
