<!DOCTYPEhtml>
<html>
	<head>
	
		<?php require '__laporan_style.php' ?>
	</head>
	<body>
		
		<?php require '__laporan_header.php' ?>

		<h1 class="title">FORM PERJALANAN DINAS</h1>
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
					<td style="width:30%">Dari</td>
					<th>:</th>
					<td><?= $header['dari_lokasi'] ?></td>
				</tr>
				<tr>
					<td style="width:30%">Ke</td>
					<th>:</th>
					<td><?= $header['ke_lokasi'] ?></td>
				</tr>
				<tr>
					<td>Tanggal</td>
					<th>:</th>
					<td><?= tgl_indo($header['tanggal']) ?></td>
				</tr>
				<tr>
					<td>Tanggal Jalan</td>
					<th>:</th>
					<td><?= tgl_indo($header['tgl_mulai']) ?></td>
				</tr>
				<tr>
					<td>Tanggal Kembali</td>
					<th>:</th>
					<td><?= tgl_indo($header['tgl_kembali']) ?></td>
				</tr>
			</table>	
		</div>

		<div class="d-flex flex-between">
			<table class="" style="width:30%">
				
				
			</table>
		</div>
		<br>
		<p>Dengan detail sebagai Berikut :</p>
		

		<table class="table-bg border">
			<thead>
				<tr>
					<th >No</th>
					<th >Komponen</th>
					<th>Harga</th>
					<th>Qty</th>
					<th >Nilai</th>
					<th >Keterangan Tanggal</th>
				</tr>

			</thead>

			<tbody>
				<?php 
				$no= 0;
				$ttl_nilai=0;
				?>
			<?php foreach ($detail as $key=>$val) { ?> 
				
				<?php 
				$no= $no+1;
				$ttl_nilai= $ttl_nilai + $val['nilai'];
				?>
				<tr>
					
					<td center width="2%"><?= $no ?></td>
					<td center width="15%"><?= $val['komponen'] ?></td>
					<td center width="7%"><?= number_format($val['harga']) ?></td>
					<td center width="7%"><?= number_format($val['qty']) ?></td>
					<td right width="7%"><?= number_format($val['nilai']) ?></td>
					<td center width="15%"><?= $val['ket'] ?></td>

				
					 
				</tr>
				<?php } ?>
				<td right colspan=4> Total </td>
				<td right><?= number_format($ttl_nilai) ?> </td>
				<td></td>
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


				
