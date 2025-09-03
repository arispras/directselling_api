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
							<h1 style="font-size:15px"  >AR VOUCHER</h1>
						</tr>
					</table>
				</td>
				<td width="30%" style="padding:0 20px">
					<table class="table-bg border">
						<tr>
							<td width="65%" center > Tanggal <br> <?= tgl_indo($hd['tanggal']) ?> </td>
							
						</tr>
						
						<tr>
							<td center >No Invoice <br> <h1><?= $hd['no_invoice'] ?></h1> </td>
						</tr>
						<tr>
							<td center >Customer <br> <h1><?= $hd['nama_customer'] ?></h1>  </td>
						</tr>
					</table>
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
						<tr >
							<td style="border:0px" width="38%">No Ref </td>
							<td style="border:0px; padding:0;" width="1">:</td>
							<td style="border:0px; " ><?= $hd['no_ref'] ?></td>
						</tr>
						<tr>
							<!-- <td style="border:0px;" >  No Referensi</td>
							<td style="border:0px; padding:0;" >:</td>
							<td style="border:0px;  " ><?= $hd['no_referensi'] ?></td> -->
						</tr>
					</table>
				</td>
				
				
				<td > 
					<table  >
					<!-- style="border:0px" -->
						<tr >
							<td style="border:0px" width="31%">Deskripsi </td>
							<td style="border:0px; padding:0;" width="2%">:</td>
							<td style="border:0px"><?= $hd['deskripsi'] ?></td>
						</tr>
					</table>
				</td>
			</tr>
			
			
		</table>
<br>




		<table class="table-bg border">
			<thead>
				<tr>
					<th rowspan="2">No</th>
					<th rowspan="2">Akun</th>
					<th rowspan="2">Keterangan</th>
					<th rowspan="2">Debet</th>
					<th rowspan="2">Kredit</th>

				</tr>

			</thead>

			<tbody>
				<?php $no = 0 ?>
				<?php $tot_dr = 0 ?>
				<?php $tot_cr = 0 ?>
				<tr>


				</tr>
				<?php foreach ($dt as $key => $val) { ?>

					<?php
					$no = $no + 1;
					$tot_cr = $tot_cr + $val['kredit'];
					$tot_dr = $tot_dr + $val['debet'];
					?>
					<tr>

						<td center width="2%"><?= $no ?></td>
						<td center width="10%"><?= $val['kode_akun'] . '-' . $val['nama_akun'] ?></td>
						<td center width="10%"><?= $val['ket'] ?></td>
						<td center width="10%"><?= number_format($val['debet']) ?></td>
						<td center width="10%"><?= number_format($val['kredit']) ?></td>

					</tr>
				<?php } ?>
				<tr>
					<?php
					$tot_dr = $tot_dr + $hd['nilai_invoice'];


					?>
					<td center width="2%"><?= $no + 1 ?></td>
					<td center width="10%"><?= $hd['kode_akun'] . '-' . $hd['nama_akun'] ?></td>
					<td center width="10%"><?= $hd['deskripsi'] ?></td>					
					<td center width="10%"><?= number_format($hd['nilai_invoice']) ?></td>
					<td center width="10%">0</td>

				</tr>
				<tr>
					
					<td colspan="3" center width="2%">Total</td>
					
					<td center width="10%"><?= number_format($tot_dr) ?></td>
					<td center width="10%"><?= number_format($tot_cr) ?></td>

				</tr>


			</tbody>

			<tfoot>
			
			</tfoot>
		</table>
		<br>
		<br>
		<br><br>
		<table >
			<tr>
				
				<td  right>
				<div style="margin-right:40px;">
				<p>Telah Dibukukan Oleh</p>
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
