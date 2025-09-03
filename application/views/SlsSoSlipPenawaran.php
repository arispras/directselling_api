<!DOCTYPEhtml>
	<html>

	<head>

		<?php
		require '__laporan_style.php';


		?>
		<style>
			* {
				font-size: 13px !important;
			}

			.table-bg th,
			.table-bg td {
				font-size: 0.9em !important;
			}
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


		<table>
			<tr>
				<td center><img src="data:image/png;base64,<?= base64_encode(file_get_contents(('./logo_perusahaan.png'))) ?>" height="80px" width="180px"> </td>
			</tr>

		</table>
		<h1 center style="font-size:20px"><?= strtoupper(get_company()['nama']) ?></h1>


		<table>
			<tr>
				<td>Kepada Yth, </td>
			</tr>
			<tr>
				<td><?= $header['contact_person_customer'] ?></td>
			</tr>
			<tr>
				<td>Di Tempat</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td> Hal : Penawaran Linen Housekeeping </td>
			</tr>
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>&nbsp;&nbsp;&nbsp;&nbsp; Bersama ini kami mengajukan penawaran di lampiran Berikut. Untuk spesifikasi dan ukuran dapat di rubah sesuai kebutuhan hotel saat ini. Silahkan di pelajari kembali dan apabila ada pertanyaan yang harus kami jelaskan langsung, dapat menghubungi kami kembali. Kami siap untuk mempresentasikan produk yang kami produksi</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
			</tr>
		</table>



		<table class="table-bg border">
			<thead>
				<tr>
					<th colspan="7"><?= $header['nama_customer'] ?></th>
				</tr>
				<tr>
					<th>No</th>
					<th>Item</th>
					<th>Size</th>
					<th>Spesifikasi</th>
					<th>Qty</th>
					<th>Harga</th>
					<th>Total</th>
				</tr>
			</thead>
			<tbody>
				<?php $no = 0 ?>
				<?php foreach ($detail as $key => $val) { ?>
					<?php $no = $no + 1 ?>
					<tr>
						<td center width="2%"><?= $no ?></td>
						<td left> <?= $val['nama_barang'] ?></td>
						<td center><?= $val['size'] ?></td>
						<td center><?= $val['spesifikasi'] ?></td>
						<td center width="5%"><?= number_format($val['qty_order']) ?></td>
						<td right width="10%"> <?= number_format($val['harga_jual']) ?></td>
						<td right width="20%"> <?= number_format($val['total_nilai_penjualan']) ?></td>
					</tr>
				<?php } ?>
			</tbody>
			<tfoot></tfoot>
		</table>

		<br>

		<table class="table-bg border">
			<tbody>

				<tr>
					<td right>Total</td>
					<td right>Rp. <?= number_format($header['sub_total']) ?></td>
				</tr>
				<?php if ($header['diskon'] != 0) { ?>
					<tr>
						<td right>Diskon</td>
						<td right>Rp. <?= number_format($header['diskon']) ?></td>
					</tr>
				<?php } ?>
				<?php if ($header['ppbkb'] != 0) { ?>
					<tr>
						<td right>PPBKB (<?= $header['ppbkb'] ?>%)</td>
						<td right>Rp. <?= number_format($pbbkb_total) ?></td>
					</tr>
				<?php } ?>
				<?php if ($header['ppn'] != 0) { ?>
					<tr>
						<td right>PPN (<?= $header['ppn'] ?>%)</td>
						<td right>Rp. <?= number_format($ppn_total) ?></td>
					</tr>
				<?php } ?>
				<?php if ($header['pph'] != 0) { ?>
					<tr>
						<td right>PPH (<?= $header['pph'] ?>%)</td>
						<td right>Rp. <?= number_format($header['pph_nilai']) ?></td>
					</tr>
				<?php } ?>
				<?php if ($header['biaya_lain'] != 0) { ?>
					<tr>
						<td right>Biaya Lainnya</td>
						<td right>Rp. <?= number_format($header['biaya_lain']) ?></td>
					</tr>
				<?php } ?>
				<?php if ($header['biaya_kirim'] != 0) { ?>
					<tr>
						<td right>Biaya Kirim</td>
						<td right style="width:30%">Rp. <?= number_format($header['biaya_kirim']) ?></td>
					</tr>
				<?php } ?>
	
					<tr>
						<td right>Uang Muka(<?= number_format($header['dp_persen']) ?>%)</td>
						<td right style="width:30%">Rp. <?= number_format(($header['dp_persen'])/100*$header['sub_total']) ?></td>
					</tr>
					<tr>
						<td right>Sisa Pembayaran</td>
						<td right style="width:30%">Rp. <?= number_format($header['sub_total']-(($header['dp_persen'])/100*$header['sub_total'])) ?></td>
					</tr>

				<!-- <tr>
					<td right bold>Grand Total</td>
					<td right bold>Rp. <?= number_format($header['grand_total']) ?></td>
				</tr> -->

			</tbody>
			<tfoot></tfoot>
		</table>
		

		<br>

		<table>
			<tr>
				<td>
					<!-- <p>Syarat Pembayaran</p>
					<p><?= $header['ket_bayar'] ?></p>

					<br> -->

					<p>Catatan :</p>
					<p><?= $header['catatan'] ?></p>
				</td>
				<td >
					<p >Bank Mandiri <br>				
					Cabang Matraman Jakarta Timur<br>
					a/n Rudi<br>
					No Rek&nbsp;006&nbsp; 0006&nbsp; 226&nbsp; 553 </p> 
				</td>
			</tr>
		</table>
		<table>
			
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td> &nbsp;&nbsp;&nbsp;&nbsp;Demikian penawaran kami buat semoga dapat di pertimbangkan, kami berharap dapat duduk bersama untuk membicarakan Harga dan kebutuhan Khususnya Linen Housekeeping . Untuk perjanjian dan cara pembayaran akan kami lampirkankan setelah adanya persetujuan untuk pengadaan kebutuhan tersebut. Atas perhatian dan kerjasamanya kami ucapkan Terima Kasih yang sebesar besarnya.</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>Bogor, &nbsp; <?= tgl_indo($header['tanggal']) ?></td>
			</tr>
			<tr>
				<td>Hormat Saya,</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>Rudi <br>Marketing Manager J</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>J Production <br>CV. Jaya Produksi Mandiri - 087873402299</td>
			</tr>
			
		</table>




		<pre><?php //print_r($headeran) 
				?></pre>

	</body>

	</html>