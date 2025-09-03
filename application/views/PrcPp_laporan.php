<!DOCTYPEhtml>
	<html>

	<head>

		<?php require '__laporan_style.php' ?>
	</head>

	<body>


		<div center>
			<img src="data:image/png;base64,<?= base64_encode(file_get_contents(base_url('logo_perusahaan.png'))) ?>" width="15%">
		</div>

		<div>
			<h1 center class="title">PURCHASE REQUEST</h1>
			<p>No : <?= $header['no_pp'] ?></p>
			<p>Tanggal : <?= tgl_indo($header['tanggal']) ?></p>
			<p>Lokasi : <?= $header['lokasi'] ?></p>
			<!-- <div id="pageCounter" class="page">
				<page size="A4"></page>
				<div id="pageNumbers" class="page">
					<div class="page-number"></div>

				</div>
			</div> -->
			<br>


			<table class="table-bg border">
				<thead>
					<tr>
						<th>No</th>
						<th>Kode Barang</th>
						<th>Nama Barang</th>
						<th>Jumlah</th>
						<th>Satuan</th>
						<th>Stok</th>
						<!-- <th rowspan="2">Required</th> -->
						<th>Keterangan</th>
					</tr>
				</thead>
				<tbody>
					<?php $no = 0 ?>
					<?php foreach ($detail as $key => $val) { ?>
						<?php $no = $no + 1 ?>
						<tr>
							<td center width="2%"><?= $no ?></td>
							<td width="10%"><?= $val['kode_barang'] ?></td>
							<td width="10%"><?= $val['nama_barang'] ?></td>
							<td center width="10%"><?= $val['qty'] ?></td>
							<td width="10%"><?= $val['uom'] ?></td>
							<td center width="10%"><?= $val['stok'] ?></td>
							<td width="10%"><?= $val['ket'] ?></td>
						</tr>
					<?php } ?>
				</tbody>
				<tfoot></tfoot>
			</table>
			<!-- <div>
			<b>Dibuat : <?= $header['user_full_name'] ?></b>
		</div> -->


			<br><br>

			<div>
				<b>Status Persetujuan :</b>
			</div>
			<table class="border">
				<thead>
					<!-- <tr> -->
					<!-- <th>No</th> -->
					<!-- <th>Nama </th> -->
					<!-- <th rowspan="2">Lokasi Tugas</th> -->
					<!-- <th>Keputusan</th> -->
					<!-- <th>Catatan</th> -->
					<!-- </tr> -->
				</thead>
				<tbody>
					<?php $no = 0 ?>
					<?php for ($i = 1; $i < 6; $i++) { ?>
						<?php $no = $no + 1 ?>
						<?php if ($header['status_approve' . $i] != NULL) { ?>
							<tr>
								<td center width="2%"><?= $no ?></td>
								<td>
									(<?= $header['user_approve' . $i] ?>)
									<?= $header['note_approve' . $i] ?>
								</td>
								<!-- <td width="10%"><?= $header['user_approve' . $i] ?></td> -->
								<!-- <td width="10%"><?= $header['last_approve_position'] ?></td> -->
								<!-- <td width="10%"><?= $header['status_approve' . $i] ?></td> -->
								<!-- <td width="10%"><?= $header['note_approve' . $i] ?></td> -->
							</tr>
						<?php } ?>
					<?php } ?>
				</tbody>
				<tfoot></tfoot>
			</table>

			<br><br>

			<table>
				<tr>
					<td center>
						<p>Dibuat Oleh</p>
						<br><br><br><br>
						<div>
							<div><?= $header['user_full_name'] ?></div>
							<div><?= date_format(date_create($header['dibuat_tanggal']), 'd M Y') ?></div>
						</div>
					</td>
					<?php for ($i = 1; $i < 6; $i++) { ?>
						<?php if ($header['status_approve' . $i] != null) { ?>
							<?php if ($header['status_approve' . $i] == 'APPROVED') { ?>
								<td center>
									<p>Disetujui Oleh</p>
									<br><br><br><br>
									<div>
										<div><?= $header['user_approve' . $i] ?></div>
										<div><?= date_format(date_create($header['tgl_approve' . $i]), 'd M Y') ?></div>
									</div>
								</td>
							<?php } else { ?>
								<td center>
									<p>Ditolak Oleh</p>
									<br><br><br><br>
									<div>
										<div><?= $header['user_approve' . $i] ?></div>
										<div><?= date_format(date_create($header['tgl_approve' . $i]), 'd M Y') ?></div>
									</div>
								</td>
							<?php } ?>
						<?php } ?>
					<?php } ?>
					
				</tr>
			</table>
			<br>
			<p center bold>*THIS PURCHASE REQUEST IS APPROVED BY SYSTEM, SIGNATURE IS NOT REQUIRED*</p>





			<pre>
<?php //print_r($headeran) 
?></pre>

	</body>

	</html>
