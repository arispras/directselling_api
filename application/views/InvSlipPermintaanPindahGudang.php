<!DOCTYPEhtml>
<html>
	<head>
	
		<?php require '__laporan_style.php' ?>
	</head>
	<body>
		
		<?php require '_laporan_header.php' ?>

		<h1 class="title">FORM PERMINTAAN MUTASI/PINDAH GUDANG</h1>
		<br>
		
		<div class="d-flex flex-between">
			<table class="" style="width:30%">
				<tr>
						<td>Lokasi</td>
						<th>:</th>
						<td><?= $header['lokasi'] ?></td>
					</tr>
				
				<tr>
					<td>Nomor Dokumen</td>
					<th>:</th>
					<td><?= $header['no_transaksi'] ?></td>
				</tr>
				<tr>
					<td style="width:30%">Dari Gudang</td>
					<th>:</th>
					<td><?= $header['dari_gudang'] ?></td>
				</tr>
				<tr>
					<td style="width:30%">Ke Gudang</td>
					<th>:</th>
					<td><?= $header['ke_gudang'] ?></td>
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
					<th rowspan="2">Keterangan</th>

				</tr>

			</thead>

			<tbody>
				<?php $no= 0 ?>
			<?php foreach ($detail as $key=>$val) { ?> 
				<?php $no= $no+1 ?>
				<tr>
					
					<td center width="2%"><?= $no ?></td>
					<td center width="7%"><?= $val['kode'] ?></td>
					<td center width="15%"><?= $val['item'] ?></td>
					<td center width="5%"><?= $val['qty'] ?></td>
					<td center width="5%"><?= $val['satuan'] ?></td>
					<td center width="10%"><?= $val['ket'] ?></td>

				
					 
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


				
