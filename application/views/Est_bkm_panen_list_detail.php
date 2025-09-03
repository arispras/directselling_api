<!DOCTYPEhtml>
<html>
	<head>
	
		<?php require '__laporan_style.php' ?>
	</head>
	<body>
		
		<?php require '__laporan_header.php' ?>

		<!-- <pre><?php print_r($po) ?></pre> -->

		<h1 class="title">LIST BKM PANEN DETAIL</h1>
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
				<?php foreach ($bkm as $key=>$val) { ?> 
				
				<?php $no= $no+1 ?>
		
		<div class="d-flex flex-between">
		<table class="" style="width:90%">
				<tr>
						<td style="width:14%">Lokasi</td>
						<th>: </th>
						<td style="width:17%"><?= $val['lokasi'] ?></td>	
						
						<td style="width:9%" >Mandor</td>
						<td>: </td>
						<td style="width:24%"><?= $val['mandor'] ?></td>
						
						<td style="width:9%" >Premi Mandor</td>
						<td>: </td>
						<td style=""><?= number_format($val['premi_mandor']) ?></td>

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

						<td style="" >Premi Kerani</td>
						<td>: </td>
						<td style=""><?= number_format($val['premi_kerani']) ?></td>

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
					<td></td>
					<td></td>

					<td>Denda Kerani</td>
					<td>: </td>
					<td><?= number_format($val['denda_kerani']) ?></td>

				</tr>
				

			</table>
		</div>
		<br>
		Detail Transaksi :
		<br>
		
			<table class="table-bg border">				
				<thead>
					<tr>
					<th>No</th>
					<th>Karyawan</th>
					<th>Blok</th>
					<th>Luas</th>
					<th>HK</th>
					<th>RP HK</th>
					<th>Janjang</th>
					<th>Brondolan</th>			
					<th>Premi Basis</th>	
					<th>Premi Lebih Basis</th>				
					<th>Premi Brondolan</th>				
					<th>Premi Panen</th>
					<th>jumlah Premi</th>
					<th>Denda Panen</th>
					</tr>

				</thead>
			

		<?php $dt=$val['detail'] ;?>
			<tbody>
				<?php 
				$no= 0 ;
				$jumlah_hk=0;
				$rp_hk=0;
				$premi_brondolan=0;
				$premi_basis =0;
				$premi_lebih_basis=0;
				$premi_panen=0;
				$hasil_kerja_jjg =0;
				$hasil_kerja_brondolan=0;
				$hasil_kerja_luas=0;
				$denda_panen=0;
				?>
				<?php foreach ($dt as $key=>$res) { ?> 
				
				<?php 
				$no++;
				$jumlah_hk += $res['jumlah_hk'];
				$rp_hk += $res['rp_hk'];
				$premi_brondolan += $res['premi_brondolan'];
				$premi_basis += $res['premi_basis'];
				$premi_lebih_basis += $res['premi_lebih_basis'];
				$premi_panen += $res['premi_panen'];
				$hasil_kerja_jjg += $res['hasil_kerja_jjg'];
				$hasil_kerja_brondolan += $res['hasil_kerja_brondolan'];
				$hasil_kerja_luas += $res['hasil_kerja_luas'];
				$denda_panen += $res['denda_panen'];
				?>
				<tr>
					
					<td center width="2%"><?= $no ?></td>
					<td center width="15%"><?= $res['karyawan'] ?></td>
					<td center width="5%"><?= ($res['blok']) ?></td>
					<td center width="5%"><?= $res['hasil_kerja_luas'] ?></td>
					<td center width="5%"><?= $res['jumlah_hk'] ?></td>
					<td center width="8%"><?= number_format($res['rp_hk']) ?></td>
					<td center width="5%"><?= number_format($res['hasil_kerja_jjg']) ?></td>
					<td center width="5%"><?= number_format($res['hasil_kerja_brondolan']) ?></td>
					<td center width="8%"><?= number_format($res['premi_basis']) ?></td>
					<td center width="8%"><?= number_format($res['premi_lebih_basis']) ?></td>
					<td center width="8%"><?= number_format($res['premi_brondolan']) ?></td>
					<td center width="8%"><?= number_format($res['premi_panen']) ?></td>
					<td center width="10%"><?= number_format($res['premi_panen']+$res['premi_brondolan']) ?></td>
					<td center width="8%"><?= number_format($res['denda_panen']) ?></td>
					 
				</tr>
				<?php } ?>
				<tr>
					
				<td colspan='3' center >JUMLAH</td>
					<td center width=""><?=  number_format($hasil_kerja_luas) ?></td>
					<td center width=""><?=  number_format($jumlah_hk) ?></td>
					<td center width=""><?=  number_format($rp_hk) ?></td>
					<td center width=""><?=  number_format($hasil_kerja_jjg)?></td>
					<td center width=""><?=  number_format($hasil_kerja_brondolan) ?></td>
					<td center width=""><?=  number_format($premi_basis) ?></td>
					<td center width=""><?=  number_format($premi_lebih_basis) ?></td>
					<td center width=""><?=  number_format($premi_brondolan) ?></td>
					<td center width=""><?=  number_format($premi_panen) ?></td>
					<td center width=""><?=  number_format($premi_brondolan+$premi_panen) ?></td>
					<td center width=""><?=  number_format($denda_panen) ?></td>
					 
				</tr>
			</tbody>
			</table>
					<br><hr>
		<?php } ?>
			
		<pre><?php //print_r($headeran) ?></pre>

	</body>
</html>


				
