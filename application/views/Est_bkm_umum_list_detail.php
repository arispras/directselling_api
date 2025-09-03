<!DOCTYPEhtml>
<html>
	<head>
	
		<?php require '__laporan_style.php' ?>
	</head>
	<body>
		
		<?php require '__laporan_header.php' ?>

		<!-- <pre><?php print_r($po) ?></pre> -->

		<h1 class="title">LIST BKM UMUM DETAIL</h1>
		<br>
		
		<div class="d-flex flex-between">
			<table class="" style="width:30%">
				<tr>
					<td>Lokasi</td>
					<th>:</th>
					<td><?= $filter_lokasi ?></td>
				</tr>
				<tr>
					<td>Periode Tanggal</td>
					<th>:</th>
					<td><?= tgl_indo($filter_tgl_awal) .' s/d '. tgl_indo($filter_tgl_akhir) ?></td>
				</tr>
			
			</table>	
		</div>
		<br>
		<?php $no= 0 ?>
				<?php foreach ($umum as $key=>$val) { ?> 
				
				<?php $no= $no+1 ?>
		
		<div class="d-flex flex-between">
		<table class="" style="width:50%">
				<tr>
						<td >Lokasi</td>
						<td>: </td>
						<td ><?= $val['lokasi'] ?></td>
						
						<td>Tanggal</td>
						<td>: </td>
						<td ><?= tgl_indo($val['tanggal']) ?></td>
						
						
				</tr>
				<tr>
						<td>Rayon/Afd</td>
						<td>: </td>
						<td><?= $val['rayon_afdeling'] ?></td>

						<td >Status Posting</td>
						<td>: </td>
						<td><?= $val['is_posting']==1?'Y':'N' ?></td>
				</tr>
				
				<tr>
					<td>Nomor Transaksi</td>
					<td>: </td>
					<td><?= $val['no_transaksi'] ?></td>
					
					<td>Keterangan</td>
					<td>: </td>
					<td><?= $val['keterangan'] ?></td>
				</tr>

			</table>
		</div>
		Detail Transaksi :
		<br>
		<br>
			<table class="table-bg border">				
				<thead>
					<tr>
						<th>No</th>
						<th >Karyawan</th>
						<th >Kegiatan</th>
						<th >Absensi</th>
						<th >HK</th>
						<th >Rupiah HK</th>
						<th>Premi</th>
					</tr>

				</thead>
			

		<?php $dt=$val['detail'] ;?>
			<tbody>
				<?php 
				$no= 0 ;
				
				?>
				<?php foreach ($dt as $key=>$res) { ?> 
				
				<?php 
				$no++;
				?>
				<tr>
					
					<td center width="2%"><?= $no ?></td>
					<td center width="12%"><?= $res['karyawan'] ?></td>
					<td center width="9%"><?= $res['kegiatan'] ?></td>
					<td center width="7%">(<?= $res['kode'] ?>) <?= $res['absensi'] ?></td>
					<td center width="7%"><?= $res['jumlah_hk'] ?></td>
					<td center width="7%"><?= $res['rupiah_hk'] ?></td>
					<td center width="7%"><?= $res['premi'] ?></td>
					 
				</tr>
				<?php } ?>
				
			</tbody>
			</table>
					<br><hr>
		<?php } ?>
			
		<pre><?php //print_r($headeran) ?></pre>

	</body>
</html>


				
