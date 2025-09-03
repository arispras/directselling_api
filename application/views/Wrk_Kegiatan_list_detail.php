<!DOCTYPEhtml>
<html>
	<head>
	
		<?php require '__laporan_style.php' ?>
	</head>
	<body>
		
		<?php require '__laporan_header.php' ?>

		<!-- <pre><?php print_r($po) ?></pre> -->

		<h1 class="title">LIST LOG WORKSHOP DETAIL</h1>
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
						<td>Workshop</td>
						<td>: </td>
						<td><?= $val['workshop'] ?></td>

						<td>Catatan</td>
						<th>:</th>
						<td><?= $val['alasan'] ?></td>

						
				</tr>
				
				<tr>
					<td>Nomor Transaksi</td>
					<td>: </td>
					<td><?= $val['no_transaksi'] ?></td>
					
					<td >Status Posting</td>
					<td>: </td>
					<td><?= $val['is_posting']==1?'Y':'N' ?></td>
					
					
				</tr>
				

			</table>
		</div>
		<br>
		Detail Transaksi :
		<br>
		<br>
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
					<td center width="10%"><?= $res['karyawan'] ?></td>
					<td center width="10%"><?= $res['jumlah_hk'] ?></td>
					<td center width="10%"><?= $res['rupiah_hk'] ?></td>
					<td center width="10%"><?= $res['premi'] ?></td>
					 
				</tr>
				<?php } ?>
				
			</tbody>
			</table>
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
			<?php $dtKegiatan=$val['dtl'] ;?>
			<tbody>
				<?php $no= 0 ?>
				<?php foreach ($dtKegiatan as $key=>$val) { ?> 
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
					<br><hr><br>
		<?php } ?>
			
		<pre><?php //print_r($headeran) ?></pre>

	</body>
</html>


				
