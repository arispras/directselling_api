<!DOCTYPEhtml>
<html>
	<head>
		
		<?php require '__laporan_style.php' ?>
	</head>
	<body>
		
		<?php require '__laporan_header.php' ?>

		<div center>
			<h1 class="title">FORM BKM UMUM</h1>
			<p></p>
		</div>
		
		<div class="d-flex flex-between">
		<table class="" style="width:60%">
				<tr>
					<td  style="width:14%">Lokasi</td>
					<td >: </td>
					<td style="width:25%"><?= $header['lokasi'] ?></td>

					<td>Tanggal</td>
					<td>: </td>
					<td><?= tgl_indo($header['tanggal']) ?></td>

				</tr >
				<tr>
					<td >Rayon/Afd</td>
					<td>: </td>
					<td ><?= $header['rayon'] ?></td>

					<td >Status Posting</td>
					<td>: </td>
					<td><?= $header['is_posting']==1?'Y':'N' ?></td>
				
				</tr>
				<tr>
					<td >No Transaksi</td>
					<td >: </td>
					<td ><?= $header['no_transaksi'] ?></td>

					<td>Keterangan</td>
					<td>: </td>
					<td><?= $header['keterangan'] ?></td>
				</tr >
				<tr>
					
				</tr>
				<tr>
					
				</tr>
			</table>
		</div>
		<p>Detail:</p>
		

		<table class="table-bg border">
			<thead>
				<tr>
					<th>No</th>
					<th >Karyawan</th>
					<th >Kegiatan</th>
					<th >Blok</th>
					<th >Kendaraan</th>
					<th >Absensi</th>
					<th >HK</th>
					<th >Rupiah HK</th>
					<th>Premi</th>
				</tr>

			</thead>

			<tbody>
				<?php 
				
				$no= 0;
				$premi=0;
				$jumlah_hk=0;
				$rupiah_hk=0;

				?>
				<?php foreach ($detail as $key=>$val) { ?> 
				
				<?php 
				
				$no= $no+1;
				$jumlah_hk=$jumlah_hk+$val['jumlah_hk'];
				$rupiah_hk=$rupiah_hk+$val['rupiah_hk'];
				$premi=$premi+$val['premi'];
				
				?>
			
				<tr>
					
					<td center width="2%"><?= $no ?></td>
					<td center width="12%"><?= $val['karyawan'] ?></td>
					<td center width="9%"><?= $val['kegiatan'] ?></td>
					<td center width="9%"><?= $val['nama_blok'] ?></td>
					<td center width="9%"><?= $val['nama_kendaraan'] ?> - <?= $val['kode_kendaraan'] ?></td>
					<td center width="7%">(<?= $val['kode'] ?>) <?= $val['absensi'] ?></td>
					<td center width="7%"><?= $val['jumlah_hk'] ?></td>
					<td center width="7%"><?= number_format($val['rupiah_hk'], 2, '.', '') ?></td>
					<td center width="7%"><?= number_format($val['premi'], 2, '.', '') ?></td>
					 
				</tr>
				<?php } ?>
				<tr>
					<td colspan='6' center >JUMLAH</td>
					<td center width="7%"><?= $jumlah_hk ?></td>
					<td center width="7%"><?= number_format($rupiah_hk, 2, '.', '') ?></td>
					<td center width="7%"><?= number_format($premi, 2, '.', '') ?></td>
				</tr>

			</tbody>
			
			<tfoot></tfoot>
		</table>
		





		<pre><?php //print_r($headeran) ?></pre>

	</body>
</html>


				
