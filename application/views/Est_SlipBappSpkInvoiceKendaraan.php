<!DOCTYPEhtml>
<html>
	<head>
	
		<?php require '__laporan_style.php' ?>
		<style>
			* { font-size:14px !important; }
			.table-bg th, .table-bg td { font-size: 0.9em !important; }
		</style>
	</head>
	<body>
		
		<?php require '__laporan_header.php' ?>

		<div center>
			<h1 class="title"> <u>INVOICE</u> </h1>
			<p bold><?= $header['nm_kendaraan'] ?> (<?= $header['kd_kendaraan'] ?>) <?= $header['bapp'] ?> </p>
	
		</div>
		<br>

		
		<div class="d-flex flex-between">
			<table class="" style="width:50%">
				
				<tr>
					<td>Kepada Yth.</td>
				</tr>
				<tr><td>Pimpinan</td></tr>
				<tr><td>KLINIK ANNAJAH</td></tr>
				<tr><td>Di Tempat</td></tr>
				
			</table>
			
				</tr>
				
			</table>
			<br>
		</div>

		<div class="d-flex flex-between">
			
		</div>
		<p>Dengan Hormat,</p>
		<p>Dengan ini kami mengajukan biaya sewa <?= $header['nm_kendaraan'] ?> (<?= $header['kd_kendaraan'] ?>) KLINIK ANNAJAH sebagai berikut :</p>
		

		<table class="table-bg border">
			<thead>
				<tr>
					<th>No</th>
					<th>Blok</th>
					<th>Kegitan</th>
					<!-- <th>Awal HM</th>
					<th>Akhir HM</th>
					<th>Jml HM</th> -->
					<th>Satuan</th>
					<th>Qty</th>
					
					<th>Harga</th>
					<th>Jumlah</th>
					<th>Ket</th>

				
				</tr>
			</thead>

			<tbody>
				<?php $no= 0 ?>
				<?php foreach ($detail as $key=>$val) { ?> 
				<?php $no= $no+1 ?>
				<tr>
					
					<td center width="2%"><?= $no ?></td>
					<td center width="10%"><?= $val['blok'] ?></td>
					<td center width="15%"><?= $val['kegiatan'] ?></td>
					
					<!-- <td center width="4%"><?= number_format($val['hm_km_awal']) ?></td>
					<td center width="4%"><?= number_format($val['hm_km_akhir']) ?></td>
					<td center width="4%"><?= number_format($val['jml_hm_km']) ?></td> -->
					<td center width="7%"><?= ($val['uom']) ?></td>
					<td center width="7%"><?= number_format($val['qty']) ?></td>
					<td center width="7%"><?= number_format($val['harga_satuan']) ?></td>
					<td center width="7%"><?= number_format($val['jumlah']) ?></td>
					<td center width="10%"><?= $val['keterangan'] ?></td>
					 
				</tr>
				<?php } ?>
			</tbody>
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
				<?php $no= 0 ?>
				<?php foreach ($detail_opt as $key=>$value) { ?> 
				<?php $no= $no+1 ?>
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

				<?php
					$sub_total = $header['subtotal'];
					$pph = $header['pph_persen'];

					$pph_total = ($pph / 100) * $sub_total;
				?>	
			
				<tr>
					<td colspan='5' right>Sub Total</td>
					<td colspan='1' right>Rp. <?= number_format($header['subtotal']) ?></td>
				</tr>
				<?php if ($header['pph_persen']!=0) { ?>
				<tr>
					<td  colspan='5' right>PPH (<?= $header['pph_persen'] ?>%)</td>
					<td  colspan='1' right>Rp. <?=  number_format($pph_total)  ?></td>
				</tr>
				<?php } ?>

				<tr>
					<td  colspan='5' right>Jumlah Opt </td>
					<td  colspan='1' right>Rp. <?=  number_format($header['jml_opt'])  ?></td>
				</tr>
				<tr>
					<td  colspan='5' right bold>Grand Total</td>
					<td  colspan='1' right bold>Rp. <?= number_format($header['nilai_invoice']) ?></td>
				</tr>
			</tbody>
			</table>
			
<!-- ---------------------------------------------------------------------------------------------- -->

		
			<br>
		


		<pre><?php //print_r($headeran) ?></pre>

	</body>
</html>


				
