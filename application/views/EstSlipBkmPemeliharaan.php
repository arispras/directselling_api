<!DOCTYPEhtml>
<html>
	<head>
	
		<?php require '__laporan_style.php' ?>
	</head>
	<body>
		
		<?php require '__laporan_header.php' ?>

		<h1 class="title">FORM BKM PEMELIHARAAN</h1>
		<br>
		
		<div class="d-flex flex-between">
		<table class="" style="width:90%">
				<tr>
						<td style="width:14%">Lokasi</td>
						<th>: </th>
						<td style="width:18%"><?= $header['lokasi'] ?></td>

						<td style="width:9%">Mandor</td>
						<td>: </td>
						<td style="width:24%"><?= $header['mandor'] ?></td>
						
						<td style="width:9%">Premi Mandor</td>
						<td>: </td>
						<td><?= number_format($header['premi_mandor']) ?></td>
						
						<td style="width:8%" >HK Mandor</td>
						<td>: </td>
						<td style=""><?= $header['jumlah_hk_mandor'] ?></td>
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

						<td style="" >HK Kerani</td>
						<td>: </td>
						<td style=""><?= $header['jumlah_hk_kerani'] ?></td>
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
					<th></th>
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
		<p>Detail :</p>
		

		<table class="table-bg border">
			<thead>
				<tr>
					<th rowspan="2">No</th>
					<th rowspan="2">Karyawan</th>
					<th rowspan="2">Blok</th>
					<th rowspan="2">Kegiatan</th>
					<th rowspan="2">Akun</th>
					<th rowspan="2">Hasil Kerja</th>
					<th rowspan="2">Hk</th>
					<th rowspan="2">Rp Hk</th>
					<th rowspan="2">Premi</th>
					<th rowspan="2">Denda</th>
					<th rowspan="2">Keterangan</th>
				</tr>
			</thead>
			<tbody>
				<?php $no= 0;
				
				$jumlah_hk=0;
				$rupiah_hk=0;
				$hasil_kerja=0;
				$premi=0;
				$denda=0;  ?>
				<?php foreach ($detail as $key=>$val) { ?> 
				<?php 
				$no= $no+1 ;
				$jumlah_hk=$jumlah_hk+$val['jumlah_hk'];
				$rupiah_hk=$rupiah_hk+$val['rupiah_hk'];
				$hasil_kerja=$hasil_kerja+$val['hasil_kerja'];
				$premi=$premi+$val['premi'];
				$denda=$denda+$val['denda_pemeliharaan'];

				?>
				<tr>
					<td center width="2%"><?= $no ?></td>
					<td left width="15%"><?= $val['karyawan'] ?></td>
					<td left width="7%"><?= $val['blok'] ?></td>
					<td left width="17%"><?= $val['kegiatan'] ?></td>
					<td left width="17%"><?= $val['kode_akun'].'-'.$val['nama_akun'] ?></td>
					<td right width="7%"><?= $val['hasil_kerja'] ?></td>
					<td right width="7%"><?= $val['jumlah_hk'] ?></td>
					<td right width="7%"><?= number_format($val['rupiah_hk']) ?></td>
					<td right width="7%"><?= number_format($val['premi']) ?></td>
					<td right width="7%"><?= number_format($val['denda_pemeliharaan']) ?></td>
					<td left width="15%"><?= $val['keterangan'] ?></td>
				</tr>
				<?php } ?>
				<tr>
					
					<td colspan='5' center >JUMLAH</td>
					<td right width="7%"><?= $hasil_kerja ?></td>
					<td right width="7%"><?= $jumlah_hk ?></td>
					<td right width="7%"><?= number_format($rupiah_hk) ?></td>
					<td right width="7%"><?= number_format($premi) ?></td>
					<td right width="7%"><?= number_format($denda) ?></td>
					<td></td>
					
					 
				</tr>
			</tbody>
			<tfoot></tfoot>
		</table>


		<br>


		<table class="table-bg border">
			<thead>
				<tr>
					<th rowspan="2">No</th>
					<!-- <th rowspan="2">Gudang</th> -->
					<th rowspan="2">Kegiatan</th>
					<th rowspan="2">Blok</th>
					<th rowspan="2">Item</th>
					<th rowspan="2">UOM</th>
					<th rowspan="2">Qty</th>
					<!-- <th rowspan="2">Total</th> -->
				</tr>
			</thead>
			<tbody>
				<?php $no= 0 ?>
				<?php foreach ($detailItem as $key=>$val) { ?> 
				<?php $no= $no+1 ?>
				<tr>
					<td center width="2%"><?= $no ?></td>
					<!-- <td center width="15%">]</td> -->
					<td center width="10%"><?= $val['kegiatan'] ?></td>
					<td center width="7%"><?= $val['blok'] ?></td>
					<td center width="10%"><?= $val['item'] ?></td>
					<td center width="10%"><?= $val['uom'] ?></td>
					<td center width="7%"><?= $val['qty'] ?></td>
					<!-- <td center width="5%"></td> -->
				</tr>
				<?php } ?>
			</tbody>
			<tfoot></tfoot>
		</table>
		





		<pre><?php //print_r($detailItem) ?></pre>

	</body>
</html>


				
