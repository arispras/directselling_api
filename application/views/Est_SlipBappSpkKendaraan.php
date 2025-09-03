<!DOCTYPEhtml>
<html>
	<head>
	
		<?php require '__laporan_style.php' ?>
	</head>
	<body>
		
		<?php require '__laporan_header.php' ?>

		<div center>
			<h1 class="title">BAPP SPK KENDARAAN</h1>
	
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
					<td>No BAPP</td>
					<th>:</th>
					<td><?= $header['bapp'] ?></td>
				</tr>
				<tr>
					<td>No SPK</td>
					<th>:</th>
					<td><?= $header['no_spk'] ?></td>
				</tr>
				<tr>
					<td>Kendaraan</td>
					<th>:</th>
					<td><?= $header['nama_kendaraan'] ?></td>
				</tr>
			</table>
			<table style="float:right; width:30%">
				<tr>
					<td>Kontraktor</td>
					<th>:</th>
					<td><?= $header['kontraktor'] ?></td>
				</tr>
				<tr>
					<td>Tanggal</td>
					<th>:</th>
					<td><?= tgl_indo($header['tanggal']) ?></td>
				</tr>
				
				<tr>
					<td>Deskripsi</td>
					<th>:</th>
					<td><?= $header['des'] ?></td>
				</tr>
				
			</table>
			<br>
		</div>

		<div class="d-flex flex-between">
			
		</div>
		<p>Dengan detail sebagai Berikut :</p>
		

		<table class="table-bg border">
			<thead>
				<tr>
					<th>No</th>
					<th>Tanggal</th>
					<th>Blok</th>
					<th>Kegitan</th>
					<!-- <th>Awal HM</th>
					<th>Akhir HM</th>
					<th>Jml HM</th> -->
					<th>Satuan</th> 
					<th>Jml</th> 
					<th>Harga</th>
					<th>Jumlah</th>
					<th>keterangan</th>

				
				</tr>
			</thead>

			<tbody>
				<?php 
					$no= 0;
					$j_hm_km=0;
					$total_jumlah=0 ;
				?>
				<?php foreach ($detail as $key=>$val) { ?> 
				<?php 
					// $no= $no+1 ;
					// $j_hm_km= $j_hm_km+$val['jml_hm_km'] ;
					$j_hm_km= $j_hm_km+$val['qty'] ;
					$total_jumlah= $total_jumlah+$val['jumlah'] ;
				?>
				<tr>
					
					<td center width="2%"><?= $no ?></td>
					<td center width="8%"><?= tgl_indo($val['tanggal_operasi']) ?></td>			
					<td center width="10%"><?= $val['blok'] ?></td>
					<td center width="15%"><?= $val['kegiatan'] ?></td>
					<!-- <td center width="4%"><?= number_format($val['hm_km_awal']) ?></td>
					<td center width="4%"><?= number_format($val['hm_km_akhir']) ?></td>
					<td center width="4%"><?= number_format($val['jml_hm_km']) ?></td> -->
					<td center width="4%"><?= ($val['uom']) ?></td>
					<td center width="4%"><?= number_format($val['qty']) ?></td>
					<td center width="4%"><?= number_format($val['harga_satuan']) ?></td>
					<td center width="4%"><?= number_format($val['jumlah']) ?></td>
					<td center width="10%"><?= $val['keterangan'] ?></td>
					 
				</tr>
				<?php } 
				?>
				
			</tbody>
			<tfoot>
				<tr>
					<td center colspan="6"></td>
					<td center ><?= number_format($j_hm_km) ?></td>
					<td center ></td>
					<td center ><?= number_format($total_jumlah) ?></td>
					<td center ></td>
					
				</tr>
			</tfoot>
		</table>
<br>
		<table class="table-bg border">
			<?php if ($header['jml_opt'] !=0) { ?>
			<thead>
				<tr>
					<th>No</th>
					<th>Tanggal</th>
					<th>Afdeling</th>
					<th>Kegitan</th>
					<th>Jumlah</th>
					<th>keterangan</th>
				</tr>
			</thead>
			<tbody>

				<?php 
					$jumlah_opt=0 ;
					$no= 0;
				?>
				<?php foreach ($detail_opt as $key=>$value) { ?> 
				<?php $no= $no+1;
				$jumlah_opt= $jumlah_opt+$value['jumlah_opt'] ;
				?>
				<tr>
					
					<td center width="2%"><?= $no ?></td>
					<td center width="10%"><?= $value['tanggal_opt'] ?></td>
					<td center width="10%"><?= $value['afdeling'] ?></td>
					<td center width="15%"><?= $value['kegiatan'] ?></td>
					<td center width="7%"><?= number_format($value['jumlah_opt']) ?></td>
					<td center width="10%"><?= $value['ket'] ?></td>
					 
				</tr>
				<?php } ?>

			<?php } ?>	

			</tbody>
			<tfoot>
				<tr>
					<td center colspan="4"></td>
					<td center ><?= number_format($jumlah_opt) ?></td>
					<td center ></td>
					
				</tr>
			</tfoot>
			</table>

		





		<pre><?php //print_r($headeran) ?></pre>

	</body>
</html>


				
