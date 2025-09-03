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
							<td center><img src="data:image/png;base64,<?=  base64_encode(file_get_contents(base_url('logo_perusahaan.png'))) ?>" height="90px" width="90px"> </td>
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
							<h1 style="font-size:15px"  >PERMINTAAN DANA</h1>
						</tr>
					</table>
				</td>
				<td width="25%" style="padding:0 20px">
					<table class="table-bg border">
						<tr>
							<td width="65%" center > Tanggal <br> <?= tgl_indo($hd['tanggal']) ?> </td>
							
						</tr>
						
						<tr>
							<td center >No Transaksi <br> <h1><?= $hd['no_transaksi'] ?></h1> </td>
						</tr>
						
					</table>
				</td>
			</tr>
		</table>

<br>
<br>
<br>		
<br>


		<!-- <h1 class="title">PERMINTAAN DANA</h1>
		<br> -->

		<div class="">
			<table class="table-bg border" style="width:90%">
				<tr>
						<td style="width:25%">Lokasi</td>
						<td style="width:2%">:</th>
						<td><?= $hd['lokasi'] ?></td>
					</tr>
				
				<tr>
					<td style="width:25%">Keterangan</td>
					<td style="width:2%">:</th>
					<td><?= $hd['keterangan'] ?></td>
				</tr>
				
				<tr>
					<td style="width:25%">Nilai</td>
					<td style="width:2%">:</th>
					<td><?= number_format($hd['nilai'],2) ?></td>
				</tr>
				

			</table>
		</div>




		<br>
		<br>

		<table>
			<tr>
				
				<td center>
					<p>Dibuat Oleh</p>
					<br><br><br><br>
					<div>
						<div>( &nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)</div>
						<div></div>
					</div>
				</td>
				
				<td center>
					<p>Diketahui Oleh</p>
					<br><br><br><br>
					<div>
						<div>( &nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)</div>
						<div></div>
					</div>
				</td>
				<td center>
					<p>Disetujui Oleh</p>
					<br><br><br><br>
					<div>
						<div>( &nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</div>
						<div></div>
					</div>
				</td>
			</tr>
		</table>




		<pre><?php //print_r($hdan) 
				?></pre>

	</body>

	</html>
