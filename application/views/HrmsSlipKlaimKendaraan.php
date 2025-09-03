<!DOCTYPEhtml>
<html>
	<head>
	
		<?php require '__laporan_style.php' ?>
	</head>
	<body>
		
		<?php require '__laporan_header.php' ?>

		<h1 class="title">FORM KLAIM KENDARAAN</h1>
		<br>
		
		<div class="d-flex flex-between">
			<table class="" style="width:40%">
				<tr>
						<td>Lokasi</td>
						<th>:</th>
						<td><?= $header['lokasi'] ?></td>
					</tr>
				<tr>
					<td style="width:30%">karyawan</td>
					<th>:</th>
					<td><?= $header['nama_karyawan'] ?></td>
				</tr>
				<tr>
					<td style="width:30%">No Transaksi</td>
					<th>:</th>
					<td><?= $header['no_transaksi'] ?></td>
				</tr>
				
				<tr>
					<td>Tanggal</td>
					<th>:</th>
					<td><?= tgl_indo($header['tanggal']) ?></td>
				</tr>
			</table>	
		</div>

		<div class="d-flex flex-between">
			<table class="" style="width:30%">
				
				
			</table>
		</div>
		<br>
		<p>Detail :</p>
		

		<table class="table-bg border">
			<thead>
				<tr>
					<th >No</th>
					<th >Keterangan</th>
					<th >Jumlah(Rp)</th>
				</tr>

			</thead>

			<tbody>
				<?php $no= 0;$jumlah=0; ?>
			<?php foreach ($detail as $key=>$val) { ?> 
				
				<?php 
				$no= $no+1 ;
				$jumlah=$jumlah+$val['nilai'] ;
				?>
				<tr>
					
					<td center width="2%"><?= $no ?></td>
					<td center width="10%"><?= $val['ket'] ?></td>
					<td center width="10%"><?= $val['nilai'] ?></td>
	

				
					 
				</tr>
				<?php } ?>
				<tr>
					
					<td center colspan="2"></td>
					<td center width="10%"><?= $jumlah ?></td>
	

				
					 
				</tr>
				
			</tbody>
			
			<tfoot></tfoot>
		</table>
		

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




		<pre><?php //print_r($headeran) ?></pre>

	</body>
</html>


				
