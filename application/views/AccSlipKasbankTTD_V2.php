<!DOCTYPEhtml>
	<html>

	
	<head>
	<?php require '_slip_header.php' ?>
	</head>
	<body>




		<table>
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
							<h1 style="font-size:15px"  >KAS/BANK VOUCHER</h1>
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
		<table class="table-bg border" >
			<tr>
				<td width="52%">
				<table  >
					<!-- style="border:0px" -->
						<tr >
							<td style="border:0px" width="30%">Tipe </td>
							<td style="border:0px; padding:0;" width="1">:</td>
							<td style="border:0px; " ><?= ($hd['tipe_jurnal']=='in')?'Penerimaan':'Pembayaran' ?></td>
						</tr>
						<tr>
							<td style="border:0px;" >  No Referensi</td>
							<td style="border:0px; padding:0;" >:</td>
							<td style="border:0px;  " ><?= $hd['no_referensi'] ?></td>
						</tr>
					</table>
				</td>
				
				
				<td > 
					<table  >
					<!-- style="border:0px" -->
						<tr >
							<td style="border:0px" width="31%">Deskripsi </td>
							<td style="border:0px; padding:0;" width="2%">:</td>
							<td style="border:0px"><?= $hd['keterangan'] ?></td>
						</tr>
					</table>
				</td>
			</tr>
			
			
		</table>
<br>
<br>
		<table class="table-bg border">
			<thead>
				<tr>
					<th rowspan="0">No</th>
					<th rowspan="0">Akun</th>
					<th rowspan="0">Keterangan</th>
					<th rowspan="0">Debet</th>
					<th rowspan="0">Kredit</th>
					<th rowspan="0">Detail</th>
				</tr>
			</thead>
			
			<tbody>
				<?php $no = 0 ?>
				<?php $tot_dr = 0 ?>
				<?php $tot_cr = 0 ?>
				
				<?php
				if ($hd['tipe_jurnal']=='in'){  // JIKA PENERIMAAN MAKA POSISI DI ATAS
				?>		
					<tr>					
						<?php						
						if ($hd['tipe_jurnal']=='in'){
							$tot_dr = $tot_dr + $hd['nilai'];
						}else{
							$tot_cr = $tot_cr + $hd['nilai'];
						}
						$no = $no + 1;					
						?>
						<td center width="2%"><?= $no  ?></td>
						<td center width="10%"><?= $hd['kode_akun'] . '-' . $hd['nama_akun'] ?></td>
						<td center width="10%"><?= $hd['keterangan'] ?></td>
						<td center width="10%"><?= ($hd['tipe_jurnal']=='in')?number_format($hd['nilai']):0 ?></td>
						<td center width="10%"><?= ($hd['tipe_jurnal']=='in')?0:number_format($hd['nilai']) ?></td>
					</tr>
				<?php
				 }
				?>	
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
						<td center width="10%">
							<?php
								if ($val['kode_blok']){	
									echo "<p>Blok/Mesin:". ($val['nama_blok']) ."</p>";
								}	
							?>	
							<?php
								if ($val['kode_kendaraan']){	
									echo "<p>Kendaraan:". ($val['kode_kendaraan']).'-'.($val['nama_kendaraan']) ."</p>";
								}	
							?>	
						</td>
						
					</tr>
					<?php } ?>
				<?php
				if ($hd['tipe_jurnal']!='in'){ // JIKA BUKAN PENERIMAAN MAKA POSISI DI BAWAH
				?>		
					<tr>					
						<?php
						
						if ($hd['tipe_jurnal']=='in'){
							$tot_dr = $tot_dr + $hd['nilai'];
						}else{
							$tot_cr = $tot_cr + $hd['nilai'];
						}					
						?>
						<td center width="2%"><?= $no + 1 ?></td>
						<td center width="10%"><?= $hd['kode_akun'] . '-' . $hd['nama_akun'] ?></td>
						<td center width="10%"><?= $hd['keterangan'] ?></td>
						<td center width="10%"><?= ($hd['tipe_jurnal']=='in')?number_format($hd['nilai']):0 ?></td>
						<td center width="10%"><?= ($hd['tipe_jurnal']=='in')?0:number_format($hd['nilai']) ?></td>
						<td ></td>

					</tr>
				<?php
				 }
				?>		
				<tr>

					<td colspan="3" center width="2%">Total</td>

					<td center width="10%"><?= number_format($tot_dr) ?></td>
					<td center width="10%"><?= number_format($tot_cr) ?></td>
					<td ></td>

				</tr>

			</tbody>
			

		</table>
		<br>
		<br>
		<br>

		<table class="table-bg border">
			<tr>
			<td width="33%" center >Dibuat Oleh</td>
			<td width="34%" center>Deketahui Oleh</td>
			<td width="33%" center>Disetujui Oleh</td>
			</tr>
			<tr>
				<td height="40"></td>
				<td></td>
				<td></td>
			</tr>
			<tr>
				<!-- <td center>(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)</td>
				<td center>(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)</td>
				<td center>(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)</td> -->
				<td center><?= $nama_ttd1 ?></td>
				<td center><?= $nama_ttd2 ?></td>
				<td center><?= $nama_ttd3 ?></td>

			</tr>	
			<tr>
				<!-- <td center>(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)</td>
				<td center>(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)</td>
				<td center>(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)</td> -->
				<td center><?= $jabatan1 ?></td>
				<td center><?= $jabatan2 ?></td>
				<td center><?= $jabatan3 ?></td>

			</tr>				
		</table>
		<br><br><br>
		




		<pre><?php //print_r($hdan) 
				?></pre>

	</body>

	</html>
