<!DOCTYPEhtml>
<html>
	<head>
	
		<?php require '__laporan_style.php' ?>
	</head>
	<body>
		
		<?php require '__laporan_header.php' ?>

		<div center>
			<h1 class="title">BA SPK</h1>
			<p><?= $header['no_transaksi'] ?></p>
		</div>
		<br>

		
		<div class="d-flex flex-between">
			<table class="" style="width:50%">
				
				<tr>
					<td>Lokasi</td>
					<th>:</th>
					<td><?= $header['lokasi'] ?></td>
				</tr>
				<tr>
					<td>No SPK</td>
					<th>:</th>
					<td><?= $header['no_spk'] ?></td>
				</tr>
				<tr>
					<td>Tanggal</td>
					<th>:</th>
					<td><?= tgl_indo($header['tanggal']) ?></td>
				</tr>
				<tr>
					<td>Kontraktor</td>
					<th>:</th>
					<td><?= $header['supplier'] ?></td>
				</tr>
			</table>
			<table style="float:right; width:30%">
				<tr>
					<td>Sub Total</td>
					<th>:</th>
					<td><?= number_format($header['subtotal']) ?></td>
				</tr>
				<tr>
					<td>PPH</td>
					<th>:</th>
					<td><?= number_format($header['subtotal']*$header['pph_persen']/100) ?></td>
				</tr>
				<tr>
					<td>Total</td>
					<th>:</th>
					<td><?= number_format($header['total']) ?></td>
				</tr>
				<tr>
					<td>Periode Mulai</td>
					<th>:</th>
					<td><?= $header['mulai'] ?></td>
				</tr>
				<tr>
					<td>Periode Akhir</td>
					<th>:</th>
					<td><?= $header['akhir'] ?></td>
				</tr>
			</table>
			<br>
		</div>

		<div class="d-flex flex-between">
			
		</div>
		<p>Detail :</p>
		

		<table class="table-bg border">
			<thead>
				<tr>
					<th>No</th>
					<th>Blok</th>
					<th>Kegiatan</th>
					<th>Satuan</th>
					<th>HK</th>
					<th>Volume</th>
					<th>Nilai</th>
					<th>Harga Satuan</th>
					<th>Keterangan</th>
				</tr>
			</thead>

			<tbody>
				<?php $no= 0 ?>
				<?php foreach ($detail as $key=>$val) { ?> 
				<?php $no= $no+1 ?>
				<tr>
					
					<td center width="2%"><?= $no ?></td>
					<td center width="10%"><?= $val['blok'] ?></td>
					<td center width="10%"><?= $val['kegiatan'] ?></td>
					<td center width="10%"><?= $val['satuan_volume'] ?></td>
					<td center width="10%"><?= number_format($val['hk']) ?></td>
					<td center width="10%"><?= number_format($val['volume']) ?></td>
					<td center width="10%"><?= number_format($val['total']) ?></td>
					<td center width="10%"><?= number_format($val['harga']) ?></td>
					<td center width="10%"><?= $val['keterangan'] ?></td>
					 
				</tr>
				<?php } ?>
				
			</tbody>
			<tfoot></tfoot>
		</table>

		





		<pre><?php //print_r($headeran) ?></pre>

	</body>
</html>


				
