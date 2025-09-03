<!DOCTYPEhtml>
<html>
	<head>
	
		<?php require '__laporan_style.php' ?>
		<style>
			* { font-size:13px !important; }
			.table-bg th, .table-bg td { font-size: 0.9em !important; }
		</style>
	</head>
	<body>




<?php


$sub_total = $header['sub_total'] - $header['diskon'];
$grand_total = $header['grand_total'];
$ppn = $header['ppn'];
$ppbkb = $header['ppbkb'];
$ppn_total = 0;
$ppbkb_total = 0;

$ppn_total = ($ppn / 100) * $sub_total;

$pbbkb_total = ($ppbkb / 100) * $sub_total;


?>

		<table style="padding:25px 0;">
			<tr>
				<td width="43%" style="padding:0 20px">
					<h1 left style="font-size:20px"><?= strtoupper(get_company()['nama']) ?></h1>
					<table>
						<tr>
							<td>Jl Sukarno Hatta No 12 No 12, Bandung Jawa Barat 14450</td>
						</tr>
						<tr>
							<td>Telp +6221 668 4055 Ext. 334 (Hendy)</td>
						</tr>
					</table>
					<p style="margin:0;"></p>
					<p style="margin:0;"></p>
				</td>
				<td style="padding:0 20px">
					<h1 left style="font-size:20px">PURCHASE ORDER</h1>
					<table>
						<tr>
							<td style="width:80px">Tanggal</td>
							<td style="width:4%">:</td>
							<td><?= tgl_indo($header['tanggal']) ?></td>
						</tr>
						<tr>
							<td>Nomor</td>
							<td>:</td>
							<td><?= $header['no_po'] ?></td>
						</tr>
						<tr>
							<td>PIC</td>
							<td>:</td>
							<td><?= $user['peminta'] ?></td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td style="padding:0 20px">
					<div>
						<div bold>Vendor</d>
						<hr>
						<p><?= $header['nama_supplier'] ?></p>
						<p><?= $header['alamat_supplier'] ?></p>
					</div>
				</td>
				<td  style="padding:0 20px">
					<div>
						<div bold>Alamat Pengiriman</div>
						<hr>
						<p><?= $header['nama_franco'] ?></p>
						<p><?= $header['alamat_franco'] ?></p>
					</div>
				</td>
			</tr>
		</table>
		
		

		<table class="table-bg border">
			<thead>
				<tr>
					<th>No</th>
					<th >Spesifikasi Barang</th>
					<th>Ket</th>
					<th>Uom</th>
					<!-- <th>Stok</th> -->
					<th>Qty</th>
					<th>Harga (Rp)</th>
					<th>Diskon (Rp)</th>
					<th>Total (Rp)</th>
				</tr>
			</thead>
			<tbody>
				<?php $no= 0 ?>
				<?php foreach ($detail as $key=>$val) { ?>
				<?php $no= $no+1 ?>
				<tr>
					<td center width="2%"><?= $no ?></td>
					<td left width="15%">(<?= $val['kode_barang'] ?>) <?= $val['nama_barang'] ?></td>
					<td center width="15%"><?= $val['ket'] ?></td>
					<td center width="3%"><?= $val['uom'] ?></td>
					<!-- <td center width="5%"><?= number_format($val['stok']) ?></td> -->
					<td center width="5%"><?= number_format($val['qty']) ?></td>
					<td right width="10%"> <?= number_format($val['harga']) ?></td>
					<td right width="7%"> <?= number_format($val['diskon']) ?></td>
					<td right width="20%"> <?= number_format($val['total']) ?></td>
				</tr>
				<?php } ?>
			</tbody>
			<tfoot></tfoot>
		</table>

		<br>

		<table class="table-bg border">
			<tbody>

				<tr>
					<td right>Sub Total</td>
					<td right>Rp. <?= number_format($header['sub_total']) ?></td>
				</tr>
				<?php if ($header['diskon']!=0) { ?>
				<tr>
					<td right>Diskon</td>
					<td right>Rp. <?= number_format($header['diskon']) ?></td>
				</tr>
				<?php } ?>
				<?php if ($header['ppbkb']!=0) { ?>
				<tr>
					<td right>PPBKB (<?= $header['ppbkb'] ?>%)</td>
					<td right>Rp. <?= number_format($pbbkb_total) ?></td>
				</tr>
				<?php } ?>
				<?php if ($header['ppn']!=0) { ?>
				<tr>
					<td right>PPN (<?= $header['ppn'] ?>%)</td>
					<td right>Rp. <?= number_format($ppn_total) ?></td>
				</tr>
				<?php } ?>
				<?php if ($header['pph']!=0) { ?>
				<tr>
					<td right>PPH (<?= $header['pph'] ?>%)</td>
					<td right>Rp. <?= number_format($header['pph_nilai']) ?></td>
				</tr>
				<?php } ?>
				<?php if ($header['biaya_lain']!=0) { ?>
				<tr>
					<td right>Biaya Lainnya</td>
					<td right>Rp. <?= number_format($header['biaya_lain']) ?></td>
				</tr>
				<?php } ?>
				<tr>
					<td right>Biaya Kirim</td>
					<td right style="width:30%">Rp. <?= number_format($header['biaya_kirim']) ?></td>
				</tr>
				<tr>
					<td right bold>Grand Total</td>
					<td right bold>Rp. <?= number_format($header['grand_total']) ?></td>
				</tr>
			</tbody>
			<tfoot></tfoot>
		</table>
		<p>Ketersediaan Barang : <?= $header['status_stok'] ?></p>
		<!-- <p>Keterangan : <?= $header['ket'] ?></p> -->

		<br>

		<table>
			<tr>
				<td>
					<p>Syarat Pembayaran</p>
					<p><?= $header['ket_bayar'] ?></p>
					
					<br>
					
					<p>Catatan :</p>
					<p><?= $header['catatan'] ?></p>
				</td>
				<td center>
					<p bold>Disetujui Oleh</p>
					<br><br><br><br>
					<?php for ($i = 1; $i < 6; $i++) { ?>
						<?php if ($header['user_approve'.$i] != null) { ?>
						<?php $pembayaran[] = $header['user_approve'.$i]; ?>
						<?php $user_jabatan[] = $header['user_approve_jabatan'.$i]; ?>
						<?php } ?>
					<?php } ?>
					<p><?= array_reverse($pembayaran)[0] ?></p>
					<p><?= array_reverse($user_jabatan)[0] ?></p>
				</td>
			</tr>
		</table>


		<pre><?php //print_r($headeran) ?></pre>

	</body>
</html>


				
