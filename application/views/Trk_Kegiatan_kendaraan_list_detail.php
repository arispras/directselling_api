<!DOCTYPEhtml>
<html>
	<head>
	
		<?php require '__laporan_style.php' ?>
	</head>
	<body>
		
		<?php require '__laporan_header.php' ?>

		<!-- <pre><?php print_r($po) ?></pre> -->

		<h1 class="title">LIST LOG KENDARAAN/AB/MESIN DETAIL</h1>
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
						<td>Traksi</td>
						<td>: </td>
						<td><?= $val['traksi'] ?></td>

						<td>Mandor</td>
						<td>: </td>
						<td><?= $val['mandor'] ?></td>

						
				</tr>
				
				<tr>
					<td>Nomor Transaksi</td>
					<td>: </td>
					<td><?= $val['no_transaksi'] ?></td>
					
					<td>Status Kendaraan</td>
					<td>: </td>
					<td><?= $val['status_kendaraan'] ?></td>
					
					
				</tr>
				<tr>
					<td>Kendaraan </td>
					<td>: </td>
					<td><?= $val['kendaraan'] ?></td>
					
					
					<td >Status Posting</td>
					<td>: </td>
					<td><?= $val['is_posting']==1?'Y':'N' ?></td>
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
					<th>Karyawan</th>
					<!-- <th>Hasil Kerja</th> -->
					<th>Jumlah HK</th>
					<th>Rupiah HK</th>
					<th>Premi</th>
					<th>Denda</th>
					<th>Keterangan</th>
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
					<!-- <td center width="10%"><?= $res['hasil_kerja'] ?></td> -->
					<td center width="10%"><?= $res['jumlah_hk'] ?></td>
					<td center width="10%"><?= $res['rupiah_hk'] ?></td>
					<td center width="10%"><?= $res['premi'] ?></td>
					<td center width="10%"><?= $res['denda_traksi'] ?></td>
					<td center width="10%"><?= $res['ket_denda'] ?></td>
					 
				</tr>
				<?php } ?>
				
			</tbody>
			</table>
			<br>
			<table class="table-bg border">
			<thead>
				<tr>
					<th rowspan="2">No</th>
					<th rowspan="2">Kegiatan</th>
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
			<?php $dtKegiatan=$val['dtl'] ;?>
			<tbody>
				<?php $no= 0 ?>
				<?php foreach ($dtKegiatan as $key=>$val) { ?> 
				<?php $no= $no+1 ?>
				<tr>
					
					<td center width="2%"><?= $no ?></td>
					<td center width="10%"><?= $val['kegiatan'] ?></td>
					<td center width="10%"><?= $val['blok'] ?></td>
					<td center width="10%"><?= $val['km_hm_mulai'] ?></td>
					<td center width="10%"><?= $val['km_hm_akhir'] ?></td>
					<td center width="10%"><?= $val['km_hm_jumlah'] ?></td>
					<td center width="10%"><?= $val['volume'] ?></td>
					<td center width="10%"><?= $val['ket'] ?></td>
					 
				</tr>
				<?php } ?>
				
			</tbody>
			<tfoot></tfoot>
		</table>
					<br><hr>
		<?php } ?>
			
		<pre><?php //print_r($headeran) ?></pre>

	</body>
</html>


				
