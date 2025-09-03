<!DOCTYPEhtml>
	<html>

	<head>

		<?php require '__laporan_style.php' ?>
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

		<table style="padding:25px 0;">
			<tr>
				<td width="43%" style="padding:0 20px">
					<h1 left style="font-size:20px"><?= strtoupper(get_company()['nama']) ?></h1>
					<!-- <table>
						<tr>
							<td>Jl Sukarno Hatta No 12 No 12, Bandung Jawa Barat 14450</td>
						</tr>
						<tr>
							<td>Telp +6221 668 4055 Ext. 334 (Hendy)</td>
						</tr>
					</table> -->
					<p style="margin:0;"></p>
					<p style="margin:0;"></p>
				</td>
				<td style="padding:0 20px">
					<h1 left style="font-size:20px">SALES ORDER DETAIL</h1>
					<table>
						<tr>
							<td style="width:80px">Tanggal</td>
							<td style="width:4%">:</td>
							<td><?= tgl_indo($header['tanggal']) ?></td>
						</tr>
						<tr>
							<td>Nomor</td>
							<td>:</td>
							<td><?= $header['no_so'] ?></td>
						</tr>
						<tr>
							<td>Jenis</td>
							<td>:</td>
							<td><?= $header['jenis_penjualan'] ?></td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td style="padding:0 20px">
					<div>
						<div bold>Customer:</d>
							<!-- <hr> -->
							<p><?= $header['nama_customer'] ?></p>
							<p><?= $header['alamat_customer'] ?></p>
						</div>
				</td>
				<!-- <td  style="padding:0 20px">
					<div>
						<div bold>Alamat Pengiriman</div>
						<hr>
						<p><?= $header['nama_franco'] ?></p>
						<p><?= $header['alamat_franco'] ?></p>
					</div>
				</td> -->
			</tr>
		</table>



		<table class="table-bg border">
			<thead>
				<tr>
					<th rowspan="2">No</th>
					<th colspan="3">Produk</th>
					<th colspan="4">Bahan</th>
					<th rowspan="2">Supplier</th>
					<th rowspan="2">Penjahit</th>
					<th colspan="10">Biaya Per Satuan</th>
					<th rowspan="2">Qty Order Produk</th>
					<th rowspan="2">Total qty Bahan</th>
					<th colspan="8">Biaya Total</th>


				</tr>
				<tr>
					<th>Item</th>
					<th>Size</th>
					<th>Spesifikasi</th>
					<th>Nama Bahan</th>
					<th>Jenis</th>
					<th>Motif</th>
					<th>Spesifikasi</th>
					<th>Pembagi</th>
					<th>Qty Bahan</th>
					<th>@Harga Beli Bahan</th>
					<th>@Harga Jual Bahan</th>
					<th>@Ongkos Jahit </th>
					<th>@Ongkos Jual Jahit </th>
					<th>@Biaya Lain </th>
					<th>@Biaya Produksi </th>
					<th>@Harga Jual Produk </th>
					<th>@Profit</th>
					<th>Total Beli Bahan</th>
					<th>Total Jual Bahan</th>
					<th>Total Ongkos Jahit</th>
					<th>Total Ongkos Jual Jahit</th>
					<th>Total Biaya Lain</th>
					<th>Total Biaya Produksi</th>
					<th>Total Penjualan</th>
					<th>Total Profit</th>

				</tr>
			</thead>
			<tbody>
				<?php
				$no = 0;
				$sum_total_beli_bahan = 0;
				$sum_total_jual_bahan = 0;
				$sum_total_ongkos_jahit = 0;
				$sum_total_ongkos_jahit_jual = 0;
				$sum_total_biaya_lain = 0;
				$sum_total_biaya_produksi = 0;
				$sum_total_penjualan = 0;
				$sum_total_profit = 0;
				?>
				<?php foreach ($detail as $key => $val) { ?>
					<?php
					$no = $no + 1;
					$harga_produksi_satuan = (($val['harga_beli_bahan'] * $val['qty_bahan']) / $val['pembagi']) + $val['biaya_jahit'] + $val['biaya_lain'];
					$profit_satuan = ($val['harga_jual'] - $harga_produksi_satuan);
					// $sum_total_beli_bahan = $sum_total_beli_bahan + ($harga_produksi_satuan * $val['qty_order']);
					$sum_total_beli_bahan = $sum_total_beli_bahan + ((($val['harga_beli_bahan']* $val['qty_bahan']) * $val['qty_order'])/$val['pembagi']);
					$sum_total_jual_bahan = $sum_total_jual_bahan + ((($val['harga_jual_bahan']* $val['qty_bahan']) * $val['qty_order'])/$val['pembagi']);
					$sum_total_ongkos_jahit = $sum_total_ongkos_jahit + ($val['biaya_jahit'] * $val['qty_order']);
					$sum_total_ongkos_jahit_jual = $sum_total_ongkos_jahit_jual + ($val['biaya_jahit_jual'] * $val['qty_order']);
					$sum_total_biaya_lain = $sum_total_biaya_lain + ($val['biaya_lain'] * $val['qty_order']);
					$sum_total_biaya_produksi = $sum_total_biaya_produksi + ($val['total_nilai_hpp']);
					$sum_total_penjualan = $sum_total_penjualan + ($val['total_nilai_penjualan']);
					$sum_total_profit = $sum_total_profit + ($val['total_nilai_profit']);

					?>
					<tr>
						<td center width="2%"><?= $no ?></td>
						<td left> <?= $val['nama_barang'] ?></td>
						<td center><?= $val['size'] ?></td>
						<td center><?= $val['spesifikasi'] ?></td>
						<td center><?= $val['nama_bahan'] ?></td>
						<td center><?= $val['jenis'] ?></td>
						<td center><?= $val['motif'] ?></td>
						<td center><?= $val['spesifikasi_bahan'] ?></td>
						<td center><?= $val['nama_supplier'] ?></td>
						<td center><?= $val['nama_supplier_jahit'] ?></td>
						<td center><?= number_format($val['pembagi'], 0) ?></td>
						<td center><?= number_format($val['qty_bahan'], 2) ?></td>
						<td center><?= number_format($val['harga_beli_bahan']) ?></td>
						<td right> <?= number_format($val['harga_jual_bahan']) ?></td>
						<td right> <?= number_format($val['biaya_jahit']) ?></td>
						<td right> <?= number_format($val['biaya_jahit_jual']) ?></td>
						<td center><?= number_format($val['biaya_lain']) ?></td>
						<td right> <?= number_format($harga_produksi_satuan) ?></td>
						<td right> <?= number_format($val['harga_jual']) ?></td>
						<td right> <?= number_format($profit_satuan) ?></td>
						<td right> <?= number_format($val['qty_order']) ?></td>
						<td right> <?= number_format($val['total_qty_bahan'], 2) ?></td>
						<td right> <?= number_format((($val['harga_beli_bahan']* $val['qty_bahan'])* $val['qty_order'])/$val['pembagi']) ?></td>
						<td right> <?= number_format((($val['harga_jual_bahan']* $val['qty_bahan']) * $val['qty_order'])/$val['pembagi']) ?></td>
						<td right> <?= number_format($val['biaya_jahit'] * $val['qty_order']) ?></td>
						<td right> <?= number_format($val['biaya_jahit_jual'] * $val['qty_order']) ?></td>
						<td right> <?= number_format($val['biaya_lain'] * $val['qty_order']) ?></td>
						<td right> <?= number_format($val['total_nilai_hpp']) ?></td>
						<td right> <?= number_format($val['total_nilai_penjualan']) ?></td>
						<td center><?= number_format($val['total_nilai_profit']) ?></td>

					</tr>
				<?php } ?>
			</tbody>
			<tfoot>
				<tr>
				
					<td left colspan="22"> TOTAL</td>
				
					<td right> <b><?= number_format($sum_total_beli_bahan) ?></b></td>
					<td right><b> <?= number_format($sum_total_jual_bahan) ?></b></td>
					<td right><b> <?= number_format($sum_total_ongkos_jahit) ?></b></td>
					<td right><b> <?= number_format($sum_total_ongkos_jahit_jual) ?></b></td>
					<td right><b> <?= number_format($sum_total_biaya_lain) ?></b></td>
					<td right><b> <?= number_format($sum_total_biaya_produksi) ?></b></td>
					<td right><b> <?= number_format($sum_total_penjualan) ?></b></td>
					<td right><b><?= number_format($sum_total_profit) ?></b></td>

				</tr>

			</tfoot>
		</table>

		<br>
		<!-- 
		<table class="table-bg border">
			<tbody>

				<tr>
					<td right>Sub Total</td>
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
					<td right bold>Grand Total</td>
					<td right bold>Rp. <?= number_format($header['grand_total']) ?></td>
				</tr>
			</tbody>
			<tfoot></tfoot>
		</table>


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
			
			</tr>
		</table> -->



	</body>

	</html>