<!DOCTYPEhtml>
<html>
	<head>
	
		<?php require '__laporan_style.php' ?>
	</head>
	<body>
		
		<?php require '__laporan_header.php' ?>

		<h1 class="title">Workshop</h1>
		<br>

		
		<div class="d-flex flex-between">
			<table class="" style="width:50%">
				<tr>
					<td>Lokasi</td>
					<th>:</th>
					<td><?= $header['lokasi'] ?></td>
				</tr>
				<tr>
					<td>Workshop</td>
					<th>:</th>
					<td><?= $header['workshop'] ?></td>
				</tr>
				<!-- <tr>
					<td>Kendaraan/AB/Mesin</td>
					<th>:</th>
					<td>( <?= $header['kode_kendaraan'] ?> ) <?= $header['kendaraan'] ?></td>
				</tr> -->
				<tr>
					<td>Nomor Transaksi</td>
					<th>:</th>
					<td><?= $header['no_transaksi'] ?></td>
				</tr>
				<tr>
					<td>Tanggal</td>
					<th>:</th>
					<td><?= $header['tanggal'] ?></td>
				</tr>
				<tr>
					<td>Catatan</td>
					<th>:</th>
					<td><?= $header['alasan'] ?></td>
				</tr>
				
			</table>
			<!-- <table style="float:right; width:30%">
				<tr>
					<td>KM HM Mulai</td>
					<th>:</th>
					<td><?= $header['km_hm_mulai'] ?> Km</td>
				</tr>
				<tr>
					<td>KM HM Akhir</td>
					<th>:</th>
					<td><?= $header['km_hm_akhir'] ?> Km</td>
				</tr>
				<tr>
					<td>Lama Perbaikan</td>
					<th>:</th>
					<td><?= $header['lama_perbaikan'] ?> Jam</td>
				</tr>
				<tr>
					<td>Kerusakan</td>
					<th>:</th>
					<td><?= $header['kerusakan'] ?></td>
				</tr>
				<tr>
					<td>Alasan</td>
					<th>:</th>
					<td><?= $header['alasan'] ?></td>
				</tr>
			</table> -->
			<br>
		</div>

		<div class="d-flex flex-between">
			
		</div>
		<p>Detail :</p>
		

		<table class="table-bg border">
			<thead>
				<tr>
					<th>No</th>
					<th>Karyawan</th>
					<th>Jumlah Hk</th>
					<th>Rupiah Hk</th>
					<th>Premi</th>
				</tr>
			</thead>

			<tbody>
				<?php $no= 0 ?>
				<?php foreach ($detail as $key=>$val) { ?> 
				<?php $no= $no+1 ?>
				<tr>
					
					<td center width="2%"><?= $no ?></td>
					<td center width="10%"><?= $val['karyawan'] ?></td>
					<td center width="10%"><?= $val['jumlah_hk'] ?></td>
					<td center width="10%"><?= $val['rupiah_hk'] ?></td>
					<td center width="10%"><?= $val['premi'] ?></td>
					 
				</tr>
				<?php } ?>
				
			</tbody>
			<tfoot></tfoot>
		</table>


		<br>

		<!-- <table class="table-bg border">
			<thead>
				<tr>
					<th>No</th>
					<th>Blok</th>
					<th>Kegiatan</th>
					<th>Jumlah Jam</th>
					<th>Total</th>
					<th>Keterangan</th>

				</tr>
			</thead>

			<tbody>
				<?php $no= 0 ?>
				<?php foreach ($detail_kegiatan as $key=>$val) { ?> 
				<?php $no= $no+1 ?>
				<tr>
					
					<td center width="2%"><?= $no ?></td>
					<td center width="15%"><?= $val['blok'] ?></td>
					<td center width="20%"><?= $val['kegiatan'] ?></td>
					<td center width="10%"><?= $val['jumlah_jam'] ?></td>
					<td center width="10%"><?= $val['total'] ?></td>
					<td center width="20%"><?= $val['keterangan'] ?></td>
					 
				</tr>
				<?php } ?>
				
			</tbody>
			<tfoot></tfoot>
		</table> -->

		<br>

		<table class="table-bg border">
			<thead>
				<tr>
					<th>No</th>
					<th>Kegiatan</th>
					<th>Kendaraan</th>
					<th>Blok</th>
					<th>Jam</th>
					<th>Keterangan</th>
				</tr>
			</thead>

			<tbody>
				<?php $no= 0 ?>
				<?php foreach ($detail_log as $key=>$val) { ?> 
				<?php $no= $no+1 ?>
				<tr>
					
					<td center width="2%"><?= $no ?></td>
					<td center width="20%"><?= $val['nama_kegiatan'].' - '.$val['kode_kegiatan'] ?></td>
					<td center width="20%"><?= $val['nama_kendaraan'].' - '.$val['kode_kendaraan'] ?></td>
					<td center width="10%"><?= $val['nama_blok'].' - '.$val['kode_blok'] ?></td>
					<td center width="10%"><?= $val['jumlah_jam'] ?></td>
					<td center width="20%"><?= $val['ket'] ?></td>
					 
				</tr>
				<?php } ?>
				
			</tbody>
			<tfoot></tfoot>
		</table>
		





		<pre><?php //print_r($headeran) ?></pre>

	</body>
</html>


				
