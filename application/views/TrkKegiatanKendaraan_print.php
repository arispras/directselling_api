<!DOCTYPEhtml>
<html>
	<head>
	
		<?php require '__laporan_style.php' ?>
	</head>
	<body>
		
		<?php require '__laporan_header.php' ?>

		<div center>
			<h1 class="title">Kegiatan Kendaraan</h1>
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
					<td>Traksi</td>
					<th>:</th>
					<td><?= $header['traksi'] ?></td>
				</tr>
				<tr>
					<td>Status</td>
					<th>:</th>
					<td><?= $header['status_kendaraan'] ?></td>
				</tr>
				<tr>
					<td>Tanggal</td>
					<th>:</th>
					<td><?= $header['tanggal'] ?></td>
				</tr>
				<tr>
					<td>Kendaraan/AB/Mesin</td>
					<th>:</th>
					<td><?= $header['kendaraan'] ?></td>
				</tr>
				<!-- <tr>
					<td>Tipe</td>
					<th>:</th>
					<td><?= $header['tipe'] ?></td>
				</tr> -->
			</table>
			<table style="float:right; width:40%">
				<tr>
					<td>Mandor</td>
					<th>:</th>
					<td><?= $header['mandor'] ?></td>
				</tr>
				<!-- <tr>
					<td>Kerani</td>
					<th>:</th>
					<td><?= $header['kerani'] ?></td>
				</tr>
				<tr>
					<td>Asisten</td>
					<th>:</th>
					<td><?= $header['asisten'] ?></td>
				</tr> -->
			</table>
			<br>
		</div>

		<div class="d-flex flex-between">
			
		</div>
		<p>Detail:</p>
		

		<table class="table-bg border">
			<thead>
				<tr>
					<th>No</th>
					<th>Karyawan</th>
					<!-- <th>Hasil Kerja</th> -->
					<th>Jumlah HK</th>
					<th>Rupiah HK</th>
					<th>Premi</th>
					<th>Denda</th>
					<th>Keterangan</th>
				</tr>
			</thead>

			<tbody>
				<?php $no= 0 ?>
				<?php foreach ($detail as $key=>$val) { ?> 
				<?php $no= $no+1 ?>
				<tr>
					
					<td center width="2%"><?= $no ?></td>
					<td center width="10%"><?= $val['karyawan'] ?></td>
					<!-- <td center width="10%"><?= $val['hasil_kerja'] ?></td> -->
					<td center width="10%"><?= $val['jumlah_hk'] ?></td>
					<td center width="10%"><?= $val['rupiah_hk'] ?></td>
					<td center width="10%"><?= $val['premi'] ?></td>
					<td center width="10%"><?= $val['denda_traksi'] ?></td>
					<td center width="10%"><?= $val['ket_denda'] ?></td>
					 
				</tr>
				<?php } ?>
				
			</tbody>
			<tfoot></tfoot>
		</table>

		<br>


		<table class="table-bg border">
			<thead>
				<tr>
					<th rowspan="2">No</th>
					<th rowspan="2">Kegiatan/Akun</th>
					<th rowspan="2">Blok</th>
					<th colspan="3">KM/HM</th>
					<th rowspan="2">Volume</th>
					<th rowspan="2">Keterangan</th>
				</tr>
				<tr>
					<th>Mulai</th>
					<th>Akhir</th>
					<th>Jumlah</th>
				</tr>
			</thead>

			<tbody>
				<?php $no= 0 ?>
				<?php foreach ($detail_log as $key=>$val) { ?> 
				<?php $no= $no+1 ?>
				<tr>
					
					<td center width="2%"><?= $no ?></td>
					<td center width="20%"><?= $val['kode_kegiatan'].'-'.$val['kegiatan'].'/'.$val['kode_akun'].'-'.$val['nama_akun'] ?></td>
					<td center width="7%"><?= $val['blok'] ?></td>
					<td center width="7%"><?= $val['km_hm_mulai'] ?></td>
					<td center width="7%"><?= $val['km_hm_akhir'] ?></td>
					<td center width="5%"><?= $val['km_hm_jumlah'] ?></td>
					<td center width="5%"><?= $val['volume'] ?></td>
					<td center width="10%"><?= $val['ket'] ?></td>
					 
				</tr>
				<?php } ?>
				
			</tbody>
			<tfoot></tfoot>
		</table>





		<pre><?php //print_r($headeran) ?></pre>

	</body>
</html>


				
