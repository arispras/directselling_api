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
			<h1 center class="title">LAPORAN HARIAN PENAGIHAN</h1>
			<p>Lokasi : <strong><?= $header['lokasi'] ?></strong></p>
			<p>No : <strong> <?= $header['no_lhi'] ?></strong></p>
			<p>Tanggal :<strong> <?= tgl_indo($header['tanggal']) ?></strong></p>
			<p>Collector :<strong> <?= ($header['collector']) ?></strong></p>
			
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
						<th>NoKuitansi</th>
						<th>Customer</th>
						<th>AngsKe</th>
						<th>Tgl Tempo</th>
						<th>Sisa Angsuran</th>
						<th>Dibayar</th>
						<th>Tanggal Janji</th>					
						<th>Catatan</th>
					</tr>
				</thead>
				<tbody>
					<?php $no = 0 ?>
					<?php foreach ($detail as $key => $val) { ?>
						<?php $no = $no + 1 ?>
						<tr>
							<td center width="2%"><?= $no ?></td>
							<td width="10%"><?= $val['no_kuitansi'] ?></td>
							<td width="10%"><?= $val['nama_customer'] ?></td>
							<td width="7%"><?= $val['angsuran_ke'] ?></td>
							<td width="11%"><?=  tgl_indo($val['tanggal_tempo']) ?></td>
							<td center width="8%"><?= number_format( $val['sisa_angsuran'],0 )?></td>							
							<td center width="12%"></td>						
							<td center width="12%"></td>
							
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