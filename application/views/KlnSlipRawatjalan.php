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
							<h1 style="font-size:15px"  >Rawat Jalan</h1>
						</tr>
					</table>
				</td>
				<td  width="30%" style="padding:0 20px" hidden>
					
				</td>
			</tr>
		</table>

<br>
<br>
<br>		
<br>


		<table class="table-bg border" >
			<tr>
				<td width="52%">
				<table  >
					<!-- style="border:0px" -->
						<tr>
							<td style="border:0px;" >  No Rawat Jalan</td>
							<td style="border:0px; padding:0;" >:</td>
							<td style="border:0px;  " ><?= $hd['no_transaksi'] ?></td>
						</tr>
						<tr >
							<td style="border:0px" width="38%">Tanggal </td>
							<td style="border:0px; padding:0;" width="1">:</td>
							<td style="border:0px; " ><?= tgl_indo($hd['tanggal']) ?></td>
						</tr>
						<tr>
							<td style="border:0px;" > Pasien</td>
							<td style="border:0px; padding:0;" >:</td>
							<td style="border:0px;  " ><?= $hd['nama_pasien'] ?></td>
						</tr>
						<tr>
							<td style="border:0px;" > Jenis Kelamin</td>
							<td style="border:0px; padding:0;" >:</td>
							<td style="border:0px;  " ><?= $hd['jenis_kelamin'] ?></td>
						</tr>
						<tr>
							<td style="border:0px;" > Tgl lahir</td>
							<td style="border:0px; padding:0;" >:</td>
							<td style="border:0px;  " ><?= tgl_indo($hd['tgl_lahir']) ?> </td>
						</tr>
						<tr>
							<td style="border:0px;" >  Alamat </td>
							<td style="border:0px; padding:0;" >:</td>
							<td style="border:0px;  " ><?= $hd['alamat'] ?></td>
						</tr>
						
					</table>
				</td>
				
				
				<td > 
					<table  >
					<!-- style="border:0px" -->
					<tr>
							<td style="border:0px;" >  Dokter</td>
							<td style="border:0px; padding:0;" >:</td>
							<td style="border:0px;  " ><?= $hd['nama_dokter'] ?></td>
						</tr>
						<tr >
							<td style="border:0px" width="31%">Tinggi badan </td>
							<td style="border:0px; padding:0;" width="2%">:</td>
							<td style="border:0px"> <?= ($hd['tinggi_badan']) ?></td>
						</tr>
						<tr >
							<td style="border:0px" width="31%">Berat Badan </td>
							<td style="border:0px; padding:0;" width="2%">:</td>
							<td style="border:0px"> <?= ($hd['berat_badan']) ?></td>
						</tr>
						<tr >
							<td style="border:0px" width="31%">Tekanan Darah </td>
							<td style="border:0px; padding:0;" width="2%">:</td>
							<td style="border:0px"> <?= ($hd['tekanan_darah']) ?></td>
						</tr>
						<tr >
							<td style="border:0px" width="31%">Gejala </td>
							<td style="border:0px; padding:0;" width="2%">:</td>
							<td style="border:0px"> <?= $hd['gejala'] ?></td>
						</tr>
						<tr>
							<td style="border:0px;" >  Catatan</td>
							<td style="border:0px; padding:0;" >:</td>
							<td style="border:0px;  " ><?= $hd['catatan'] ?></td>
						</tr>
						
					</table>
				</td>
			</tr>
			
			
		</table>
<br>




		<!-- <table class="table-bg border">
			<thead>
			</thead>
			<tbody>

					<tr>
						<td >Jumlah</td>
						<td center>:</td>
						<td ><?= number_format($hd['jumlah'],2) ?></td>

						<td >FFA</td>
						<td >:</td>
						<td ><?= number_format($hd['ffa'],2) ?></td>

						<td >MI</td>
						<td >:</td>
						<td ><?= number_format($hd['mi'],2) ?></td>

					</tr>

					<tr>

						<td  width="22%"> Harga Satuan</td>
						<td center width="2%">:</td>
						<td  width="22%"><?= number_format($hd['harga_satuan'],2) ?></td>

						<td  width="10%">Impurities </td>
						<td center width="2%">:</td>
						<td  width="10%"> <?= number_format($hd['impurities'],2) ?></td>

						<td  width="10%">Dobi </td>
						<td center width="2%">:</td>
						<td  width="10%"> <?= number_format($hd['dobi'],2) ?></td>

					</tr>

					<tr>
						<td >Subtotal</td>
						<td center>:</td>
						<td ><?= number_format($hd['sub_total'],2) ?></td>
						
						<td >Moisture</td>
						<td >:</td>
						<td ><?= number_format($hd['moisture'],2) ?></td>

						<td >Grading</td>
						<td >:</td>
						<td ><?= number_format($hd['grading'],2) ?></td>

					</tr>
					<tr>
						<td >Total</td>
						<td center>:</td>
						<td ><?= number_format($hd['total'],2) ?></td>
						
						<td >Toleransi</td>
						<td >:</td>
						<td > <?= number_format($hd['toleransi'],2) ?></td>

						<td colspan=3></td>

					</tr>


			</tbody>

			<tfoot>
			
			</tfoot>
		</table> -->
		<br>
		<br>
		<br><br>
		<table >
			<tr>
				
				<td  right>
				<div style="margin-right:50px;">
				<p style="margin-right:20px;">Diketahui Oleh</p>
					<br><br><br><br><br>
					<div>
						<div style="margin-right:0px;">( &nbsp; &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)</div>
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
