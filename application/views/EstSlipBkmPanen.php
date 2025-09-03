<!DOCTYPEhtml>
<html>
	<head>
		
		<?php require '__laporan_style.php' ?>
	</head>
	<body>
		
		<?php require '__laporan_header.php' ?>

		<h1 class="title">FORM BKM PANEN</h1>
		<br>
		
		<div class="d-flex flex-between">
		<table class="" style="width:80%">
				<tr>
						<td style="width:14%">Lokasi</td>
						<th>: </th>
						<td style="width:18%"><?= $header['lokasi'] ?></td>

						<td style="width:9%">Mandor</td>
						<td>: </td>
						<td style="width:25%"><?= $header['mandor'] ?></td>
						
						<td style="width:9%">Premi Mandor</td>
						<td>: </td>
						<td><?= number_format($header['premi_mandor']) ?></td>

						<td style="width:8%">HK Mandor</td>
						<td>: </td>
						<td><?= $header['jumlah_hk_mandor'] ?></td>
				</tr>
				<tr>
						<td>Rayon/Afd</td>
						<th>: </th>
						<td><?= $header['rayon'] ?></td>

						<td >Kerani</td>
						<td>: </td>
						<td><?= $header['kerani'] ?></td>
						
						<td>Premi Kerani</td>
						<td>: </td>
						<td><?= number_format($header['premi_kerani']) ?></td>

						<td>HK Kerani</td>
						<td>: </td>
						<td><?= $header['jumlah_hk_kerani'] ?></td>
				</tr>
				
				<tr>
					<td>Nomor Transaksi</td>
					<th>: </th>
					<td><?= $header['no_transaksi'] ?></td>

					<td >Status Posting</td>
					<td>: </td>
					<td><?= $header['is_posting']==1?'Y':'N' ?></td>
					
					<td>Denda Mandor</td>
					<td>: </td>
					<td><?= number_format($header['denda_mandor']) ?></td>
				</tr>
				<tr>
					<td>Tanggal</td>
					<th>: </th>
					<td><?= tgl_indo($header['tanggal']) ?></td>

					<td></td>
					<td></td>
					<td></td>

					<td>Denda Kerani</td>
					<td>: </td>
					<td><?= number_format($header['denda_kerani']) ?></td>
				</tr>
				
				

			</table>
		</div>

		<div class="d-flex flex-between">
			<table class="" style="width:30%">
				
				
			</table>
		</div>
		<p>Keterangan Pekerjaan Mandor :  <?= $header['ket_mandor'] ?></p>
		<p>Keterangan Pekerjaan Kerani :  <?= $header['ket_kerani'] ?></p>
		<p>Detail:</p>
		

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
					<th>Ket Panen</th>
					<th>Denda Panen</th>
					<th>Ket Denda</th>

					
					
					
				</tr>

			</thead>

			<tbody>
				<?php $no= 0 ;
				$jumlah_hk=0;
				$rp_hk=0;
				$premi_brondolan=0;
				$premi_basis=0;
				$premi_lebih_basis=0;
				$premi_panen=0;
				$hasil_kerja_jjg=0;
				$hasil_kerja_brondolan=0;
				$hasil_kerja_luas=0;
				$denda_panen=0;
				?>
				<?php foreach ($detail as $key=>$val) { ?> 
				
				<?php 
				$no= $no+1 ;
				$jumlah_hk=$jumlah_hk+$val['jumlah_hk'];
				$rp_hk=$rp_hk+$val['rp_hk'];
				$premi_brondolan=$premi_brondolan+$val['premi_brondolan'];
				$premi_basis=$premi_basis+$val['premi_basis'];
				$premi_lebih_basis=$premi_lebih_basis+$val['premi_lebih_basis'];
				$premi_panen=$premi_panen+$val['premi_panen'];
				$denda_panen=$denda_panen+$val['denda_panen'];
				$hasil_kerja_jjg=$hasil_kerja_jjg+$val['hasil_kerja_jjg'];
				$hasil_kerja_brondolan=$hasil_kerja_brondolan+$val['hasil_kerja_brondolan'];
				$hasil_kerja_luas=$hasil_kerja_luas+$val['hasil_kerja_luas'];
				
				?>
			
				<tr>
					
				<td center width="2%"><?= $no ?></td>
					<td center width="15%"><?= $val['karyawan'] ?></td>
					<td center width="5%"><?= ($val['blok']) ?></td>
					<td center width="5%"><?= $val['hasil_kerja_luas'] ?></td>
					<td center width="5%"><?= $val['jumlah_hk'] ?></td>
					<td center width="8%"><?= number_format($val['rp_hk']) ?></td>
					<td center width="5%"><?= number_format($val['hasil_kerja_jjg']) ?></td>
					<td center width="5%"><?= number_format($val['hasil_kerja_brondolan']) ?></td>
					<td center width="8%"><?= number_format($val['premi_basis']) ?></td>
					<td center width="8%"><?= number_format($val['premi_lebih_basis']) ?></td>
					<td center width="8%"><?= number_format($val['premi_brondolan']) ?></td>
					<td center width="8%"><?= number_format($val['premi_panen']) ?></td>
					<td center width="10%"><?= number_format($val['premi_panen']+$val['premi_brondolan']) ?></td>
					<td center width="10%"><?= $val['ket'] ?></td> 
					<td center width="8%"><?= number_format($val['denda_panen']) ?></td>
					<td center width="10%"><?= $val['keterangan_potongan'] ?></td>
					

					

					
					 
				</tr>
				<?php } ?>
				<tr>
					
					<td colspan='3' center >JUMLAH</td>
					<td center><?= number_format($hasil_kerja_luas, 2, '.', '') ?></td>
					<td center><?=  number_format($jumlah_hk, 2, '.', '') ?></td>
					<td center><?=  number_format($rp_hk) ?></td>
					<td center><?=  number_format($hasil_kerja_jjg)?></td>
					<td center><?=  number_format($hasil_kerja_brondolan) ?></td>
					
					<td center><?=  number_format($premi_basis) ?></td>
					<td center><?=  number_format($premi_lebih_basis) ?></td>
					<td center><?=  number_format($premi_brondolan) ?></td>
					<td center><?=  number_format($premi_panen) ?></td>
					
					<td center><?=  number_format($premi_brondolan+$premi_panen) ?></td>
					<td></td>
					<td center><?=  number_format($denda_panen) ?></td>
					<td></td>
					
					
					 
				</tr>
			</tbody>
			
			<tfoot></tfoot>
		</table>
		





		<pre><?php //print_r($headeran) ?></pre>

	</body>
</html>


				
