<!DOCTYPEhtml>
<html>
	<head>
	
		<?php require '__laporan_style.php' ?>
	</head>
	<body>
		
		<?php require '__laporan_header.php' ?>

		<!-- <pre><?php print_r($po) ?></pre> -->

		<h1 class="title">LIST BKM PEMELIHARAAN DETAIL</h1>
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
				<?php foreach ($pml as $key=>$val) { ?> 
				
				<?php $no= $no+1 ?>
		
		<div class="d-flex flex-between">
			<table class="" style="width:90%">
				<tr>
						<td style="width:14%">Lokasi</td>
						<th>: </th>
						<td style="width:18%"><?= $val['lokasi'] ?></td>	
						
						<td  style="width:9%">Mandor</td>
						<td>: </td>
						<td style="width:24%"><?= $val['mandor'] ?></td>

						<td style="width:9%">Premi Mandor</td>
						<td>: </td>
						<td><?= number_format($val['premi_mandor']) ?></td>

						<td style="width:8%" >HK Mandor</td>
						<td>: </td>
						<td style=""><?= $val['jumlah_hk_mandor'] ?></td>
						
				</tr>
				<tr>
						<td>Rayon/Afd</td>
						<th>: </th>
						<td><?= $val['rayon_afdeling'] ?></td>

						<td >Kerani</td>
						<td>: </td>
						<td><?= $val['kerani'] ?></td>

						<td>Premi Kerani</td>
						<td>: </td>
						<td><?= number_format($val['premi_kerani']) ?></td>

						<td style="" >HK Kerani</td>
						<td>: </td>
						<td style=""><?= $val['jumlah_hk_kerani'] ?></td>
						
				</tr>
				
				<tr>
					<td>Nomor Transaksi</td>
					<th>: </th>
					<td><?= $val['no_transaksi'] ?></td>

					<td >Status Posting</td>
					<td>: </td>
					<td><?= $val['is_posting']==1?'Y':'N' ?></td>

					<td>Denda Mandor</td>
					<td>: </td>
					<td><?= number_format($val['denda_mandor']) ?></td>
						
				</tr>
				<tr>
					<td>Tanggal</td>
					<th>: </th>
					<td><?= tgl_indo($val['tanggal']) ?></td>

					<td></td>
					<th></th>
					<td></td>

					<td>Denda Kerani</td>
					<td>: </td>
					<td><?= number_format($val['denda_kerani']) ?></td>

					
				</tr>
				

			</table>
		</div>
		Detail Transaksi :
		<br>
		<br>

		<!-- table 1 -->
		<table class="table-bg border">				
				<thead>
					<tr>
						<th>No</th>
						<th >Karyawan</th>
						<th>Blok</th>
						<th>Kegiatan</th>
						<th>Hasil Kerja</th>
						<th>Hk</th>
						<th>Rp Hk</th>
						<th>Premi</th>
						<th>Denda</th>
						<th>Keterangan</th>
					</tr>

				</thead>
			

		<?php $dt=$val['detail'] ;?>
			<tbody>
				<?php 
				$no= 0 ;
				$jumlah_hk=0;
				$rupiah_hk=0;
				$hasil_kerja=0;
				$premi=0;
				$denda=0;
				
				?>
				<?php foreach ($dt as $key=>$res) { ?> 
				
				<?php 
				$no++;
				$jumlah_hk=$jumlah_hk+$res['jumlah_hk'];
				$rupiah_hk=$rupiah_hk+$res['rupiah_hk'];
				$hasil_kerja=$hasil_kerja+$res['hasil_kerja'];
				$premi=$premi+$res['premi'];
				$denda=$denda+$res['denda_pemeliharaan'];
				

				?>
				<tr>
					<td center width="2%"><?= $no ?></td>
					<td center width="15%"><?= $res['karyawan'] ?></td>
					<td center width="3%"><?= $res['blok'] ?></td>
					<td center width="17%"><?= $res['kegiatan'] ?></td>
					<td center width="4%"><?= $res['hasil_kerja'] ?></td>
					<td center width="2%"><?= $res['jumlah_hk'] ?></td>
					<td center width="5%"><?= $res['rupiah_hk'] ?></td>
					<td center width="5%"><?= number_format($res['premi']) ?></td>		 
					<td center width="7%"><?= number_format($res['denda_pemeliharaan']) ?></td>
					<td center width="15%"><?= $res['keterangan'] ?></td>
				</tr>
				<?php } ?>
				<tr>
					<td colspan='4' center >JUMLAH</td>
					<td center width="10%"><?= $hasil_kerja ?></td>
					<td center width="10%"><?= $jumlah_hk ?></td>
					<td center width="10%"><?= number_format($rupiah_hk) ?></td>
					<td center width="10%"><?= number_format($premi) ?></td>
					<td center width="7%"><?= number_format($denda) ?></td>
					<td></td>
					 
				</tr>
			</tbody>
		</table>
		<!-- tutup table -->
			<br>

			<table class="table-bg border">				
				<thead>
					<tr>
						<th>No</th>
						<th>Kegiatan</th>
						<th>Blok</th>
						<th>Item</th>
						<th>UOM</th>
						<th>QTY</th>
						
						
					</tr>
				</thead>
		

			<?php $dti=$val['dtl'] ;?>
				<tbody>
					<?php 
					$no= 0 ;
					
					?>
					<?php foreach ($dti as $key=>$ress) { ?> 
					
					<?php 
					$no++;
					?>
					<tr>
						<td center width="2%"><?= $no ?></td>
						<td center width="15%"><?= $ress['kegiatan'] ?></td>
						<td center width="7%"><?= $ress['blok'] ?></td>
						<td center width="10%"><?= $ress['item'] ?></td>
						<td center width="10%"><?= $ress['uom'] ?></td>
						<td center width="10%"><?= $ress['qty'] ?></td>
						
						
					</tr>
					<?php } ?>
				</tbody>
			</table>
					<br><hr>
		<?php } ?>
			
		<pre><?php //print_r($headeran) ?></pre>

	</body>
</html>


				
