<!DOCTYPEhtml>
<html>
	<head>
	
		<?php require '__laporan_style.php' ?>
	</head>
	<body>
		
		<?php require '__laporan_header.php' ?>

		<h1 class="title">FORM PERMINTAAN BARANG</h1>
		<br>
		
		<div class="d-flex flex-between">
			<table class="" style="width:30%">
				<tr>
						<td>Lokasi</td>
						<th>:</th>
						<td><?= $header['lokasi'] ?></td>
					</tr>
				<tr>
					<td style="width:30%">Gudang</td>
					<th>:</th>
					<td><?= $header['gudang'] ?></td>
				</tr>
				<tr>
					<td style="width:30%">Afdeling</td>
					<th>:</th>
					<td><?= $header['afdeling'] ?></td>
				</tr>
				<tr>
					<td style="width:30%">Traksi</td>
					<th>:</th>
					<td><?= $header['traksi'] ?></td>
				</tr>
				<tr>
					<td>Nomor Dokumen</td>
					<th>:</th>
					<td><?= $header['no_transaksi'] ?></td>
				</tr>
				<tr>
					<td>Tanggal</td>
					<th>:</th>
					<td><?= tgl_indo($header['tanggal']) ?></td>
				</tr>
				<tr>
					<td>Status Transaksi</td>
					<th>:</th>
					<td><?= $header['is_posting']==1?'Posting':'Belum Posting' ?></td>
				</tr>
			</table>	
		</div>

		<div class="d-flex flex-between">
			<table class="" style="width:30%">
				
				
			</table>
		</div>
		<p>Dengan detail sebagai Berikut :</p>
		

		<table class="table-bg border">
			<thead>
				<tr>
					<th rowspan="2">No</th>
					<th rowspan="2">Kode Barang</th>
					<th rowspan="2">Nama Barang</th>
					<th rowspan="2">Kuantitas</th>
					<th rowspan="2">Satuan</th>
					<th rowspan="2">Kegiatan</th>
					<th rowspan="2">Kode Blok</th>
					<th rowspan="2">Kendaraan</th>
				</tr>

			</thead>

			<tbody>
				<?php $no= 0 ?>
			<?php foreach ($detail as $key=>$val) { ?> 
				
				<?php $no= $no+1 ?>
				<tr>
					
					<td center width="2%"><?= $no ?></td>
					<td center width="10%"><?= $val['kode_barang'] ?></td>
					<td center width="10%"><?= $val['nama_barang'] ?></td>
					<td center width="10%"><?= $val['qty'] ?></td>
					<td center width="10%"><?= $val['uom'] ?></td>
					<td center width="10%"><?= $val['nama_kegiatan'] ?></td>
					<td center width="10%"><?= $val['blok'] ?></td>
					<td center width="10%"><?= $val['nama_kendaraan'] ?></td>

				
					 
				</tr>
				<?php } ?>
				
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


				
