<!DOCTYPEhtml>
	<html>

	<head>

		<?php require '__laporan_style.php' ?>
		<style>
			* {
				font-size: 10px !important;
			}

			.table-bg th,
			.table-bg td {
				font-size: 0.9em !important;
			}
		</style>
	</head>

	<body>


		<div center>
			<!-- <img src="data:image/png;base64,<?= base64_encode(file_get_contents(base_url('logo_perusahaan.png'))) ?>" width="15%"> -->
		</div>

		<div>
			<h1 center class="title">SALES ORDER</h1>
			<p>Lokasi : <strong><?= $header['lokasi'] ?></strong></p>
			<p>No : <strong> <?= $header['no_so'] ?></strong></p>
			<p>Tanggal :<strong> <?= tgl_indo($header['tanggal']) ?></strong></p>
			<p>Sales :<strong> <?= ($header['nama_sales']) ?></strong></p>
			<p>Surveyor :<strong> <?= ($header['nama_surveyor']) ?></strong></p>
			<p>Customer :<strong> <?= ($header['nama_customer']) ?></strong></p>
			
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
						<th>Uom</th>
						<th>Qty</th>
						<th>Harga</th>
						<th>Diskon</th>
						<th>DP</th>
						<th>Jumlah</th>
						<th>Piutang</th>
						<th>Angsuran</th>
						<!-- <th>Keterangan</th> -->
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
							<td width="5%"><?= $val['uom'] ?></td>
							<td center width="7%"><?= $val['qty'] ?></td>
							<td center width="8%"><?= number_format( $val['harga'],0 )?></td>							
							<td center width="8%"><?=number_format($val['diskon'],0) ?></td>						
							<td center width="8%"><?= number_format($val['dp'],0) ?></td>
							<td center width="10%"><?= number_format($val['total'],0) ?></td>
							<td center width="10%"><?= number_format($val['nilai_piutang'],0) ?></td>
							<td center width="10%"><?= number_format($val['nilai_angsuran'],0) ?></td>
							<!-- <td width="10%"><?= $val['ket'] ?></td> -->
						</tr>
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
							<div></div>
							<div><?= date_format(date_create($header['dibuat_tanggal']), 'd M Y') ?></div>
						</div>
					</td>
					<td center>
						<p>Disetujui Oleh</p>
						<br><br><br><br>
						<div>
							<div></div>
							<div></div>
						</div>
				</tr>
			</table>
			<br>
		

	</body>

	</html>