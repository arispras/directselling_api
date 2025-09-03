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
							<h1 style="font-size:15px">Biaya</h1>
						</tr>
					</table>
				</td>
				<td width="25%" style="padding:0 20px">
					<table class="table-bg border">
						<tr>
							<td width="65%" center> Tanggal <br> <?= tgl_indo($hd['tanggal']) ?> </td>

						</tr>

						<tr>
							<td center>No Resep <br>
								<h1><?= $hd['no_transaksi'] ?></h1>
							</td>
						</tr>

					</table>
				</td>
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
						<!-- style="border:0px" -->
						<tr>
							<td style="border:0px" width="30%">Pasien </td>
							<td style="border:0px; padding:0;" width="1">:</td>
							<td style="border:0px; "><?= $hd['nama_pasien'] ?></td>
						</tr>
						<tr>
							<td style="border:0px;"> Dokter</td>
							<td style="border:0px; padding:0;">:</td>
							<td style="border:0px;  "><?= $hd['nama_dokter'] ?></td>
						</tr>
					</table>
				</td>


				<td>
					<table>
						<!-- style="border:0px" -->
						<tr>
							<td style="border:0px" width="31%">No Rawat Jalan </td>
							<td style="border:0px; padding:0;" width="2%">:</td>
							<td style="border:0px"><?= $hd['no_rawat_jalan'] ?></td>
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
					<th rowspan="0">Biaya</th>
					<th rowspan="0">Keterangan</th>
					<th rowspan="0">Jumlah</th>
					<!-- <th rowspan="0">Jumlah</th> -->

				</tr>
			</thead>
			<tbody>

				<tr>
					<td center width="2%">1</td>
					<td center width="30%"><?= $hd['kode_biaya'] . '-' . $hd['nama_biaya'] ?></td>
					<td center width="30%"><?= $hd['keterangan'] ?></td>
					<td center width="10%"><?= number_format($hd['harga']) ?></td>
				</tr>


				<!-- <tr>

					<td colspan="7" center width="2%">Total</td>

					<td center width="10%"><?= number_format($total) ?></td>

				</tr> -->

			</tbody>
		</table>
		<br>
		<br>
		<br>

		<table>
			<tr>

				<td center>
					<div style="margin-right:40px;">
						<p>Kasir</p>
						<br><br><br><br><br>
						<div>
							<div style="margin-right:10px;">( &nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)</div>
							<div></div>
						</div>
					</div>

				</td>


			</tr>
		</table>




		<pre><?php //print_r($hdan) 
				?></pre>

	</body>

	</html>