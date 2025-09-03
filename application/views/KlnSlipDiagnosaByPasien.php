<!DOCTYPEhtml>
	<html>


	<head>
		<?php require '_slip_header.php' ?>
	</head>

	<body>




		<table style="padding:10px 0;">
			<tr>
				<td width="13%" style="padding:0 0px">
					<table>
						<tr>
							<td center><img src="data:image/png;base64,<?= base64_encode(file_get_contents(base_url('logo_perusahaan.png'))) ?>" height="90px" width="90px"> </td>
						</tr>

					</table>
				</td>
				<td width="20%" style="padding:0 0px">
					<h1 left><?= strtoupper(get_company()['nama']) ?></h1>
					<table>
						<tr>
							<td>Jl Sukarno Hatta ,</td>
						</tr>
						<tr>
							<td>No 12, Bandung,</td>
						</tr>
						<tr>
							<td>Jawa Barat, 14450,</td>
						</tr>
						<tr>
							<td>Tel. +6221 668 4055</td>
						</tr>
					</table>
				</td>
				<td width="27%" style="padding:0 10px">

					<table>
						<tr>
							<h1 style="font-size:15px">DIAGNOSA</h1>
						</tr>
					</table>
				</td>
				<!-- <td width="25%" style="padding:0 20px">
					<table class="table-bg border">
						<tr>
							<td width="65%" center > Tanggal <br> <?= tgl_indo($hd['tanggal']) ?> </td>
							
						</tr>
						
						<tr>
							<td center >No Resep <br> <h1><?= $hd['no_transaksi'] ?></h1> </td>
						</tr>
						
					</table>
				</td> -->
			</tr>
		</table>

		<br>
		<br>
		<br>
		<br>
		<table class="table-bg border">
			<tr>
				<td width="52%">
					<table>
						<tr>
							<td style="border:0px" width="30%">Pasien </td>
							<td style="border:0px; padding:0;" width="1">:</td>
							<td style="border:0px; "><?= $hd['nama'] ?></td>
						</tr>
						<tr>
							<td style="border:0px;"> Tgl Lahir</td>
							<td style="border:0px; padding:0;">:</td>
							<td style="border:0px;  "><?= tgl_indo($hd['tgl_lahir']) ?></td>
						</tr>
						<tr>
							<td style="border:0px;"> Jenis Kelamin</td>
							<td style="border:0px; padding:0;">:</td>
							<td style="border:0px;  "><?= ($hd['jenis_kelamin']) ?></td>
						</tr>
					</table>
				</td>



			</tr>


		</table>
		<br>
		<table class="table-bg border">
			<thead>
				<tr>
					<th rowspan="0">No</th>
					<th rowspan="0">NoDiagnosa</th>
					<th rowspan="0">Tanggal</th>
					<th rowspan="0">Dokter</th>
					<th rowspan="0">Jenis Diagnosa</th>
					<th rowspan="0">Deskripsi</th>
					<th rowspan="0">Rekomendasi</th>

				</tr>
			</thead>
			<tbody>
				<?php $no = 0 ?>
				<?php $total = 0 ?>



				<?php foreach ($dt as $key => $val) { ?>
					<?php
					$no = $no + 1;
					?>
					<tr>
						<td center width="2%"><?= $no ?></td>
						<td center width="10%"><?= $val['no_transaksi'] ?></td>
						<td center width="10%"><?= $val['tanggal'] ?></td>
						<td center width="10%"><?= $val['nama_dokter'] ?></td>
						<td center width="10%"><?= $val['jenis_diagnosa'] ?></td>
						<td center width="10%"><?= $val['deskripsi'] ?></td>
						<td center width="10%"><?= $val['rekomendasi'] ?></td>
					</tr>
				<?php } ?>



			</tbody>
		</table>
		<br>
		<br>
		<br>




		<pre><?php //print_r($hdan) 
				?></pre>

	</body>

	</html>