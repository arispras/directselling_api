<!DOCTYPEhtml>
	<html>

	<head>
		
		<?php require '__laporan_style.php' ?>
		<style>
			.border-top-none { border-top:0px solid black !important; }
			.border-left-none { border-left:0px solid black !important; }
			.border-right-none { border-right:0px solid black !important; }
			.border-bottom-none { border-bottom:0px solid black !important; }
		</style>
	</head>

	<body>

		<pre><?php //print_r($po) ?></pre>
		<pre><?php //print_r($podt) ?></pre>


		<h1 center style="font-size:150%"><?= strtoupper(get_company()['nama']) ?></h1>

		<div center>
			<h2 style="margin:0">INVOICE</h2>
			<p style="margin:0"><?= $hd['no_invoice'] ?></p>
		</div>

		<!-- header -->
		<table class="table-bg">
			<tr>
				<td style="width:50%; border:0px solid black">
					<div>Kepada Yth, <br> PT. PALM MAS ASRI JL. RAYA PLUIT PERMAI (MEGA MALL) NO. 21 - 23 PLUIT, PENJARINGAN</div>
					<div>Jawa Barat</div>
					<div>02.296.424.1-046.000</div>
				</td>
				<td style="width:20%; border:0px solid black"></td>
				<td style="width:40%; border:0px solid black">
					<div>Tanggal Invoice : <?= tgl_indo($hd['tanggal']) ?></div>
					<br><br><br><br>
				</td>
			</tr>
		</table>

		<br>

		<!-- table -->
		<table class="table-bg">
			<tr>
				<th>No</th>
				<th>Nama Produk</th>
				<th>Kuantitas (Kg)</th>
				<th>Harga Satuan (Rp)</th>
				<th>Jumlah (Rp)</th>
			</tr>
			<?php foreach ($sodt as $key => $val) { ?>
			<?php
			$no = $no + 1;
			// $tot_cr = $tot_cr + $val['kredit'];
			// $tot_dr = $tot_dr + $val['debet'];
			?>
			<tr>
				<td><?= $no ?></td>
				<td><?= $val['item_nama'] . ' (' . $val['nama_uom'] . ') <br> ' . $val['no_so'] ?></td>
				<td center><?= $val['qty'] ?></td>
				<td right>Rp. <?= number_format($val['harga']) ?></td>
				<td right>Rp. <?= number_format($val['total']) ?></td>
			</tr>
			<?php } ?>
			<tr>
				<td></td>
				<td style="height:10%">Jatuh Temso: SEGERA</td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
			<tr>
				<td colspan="3">Sub Total</td>
				<td colspan="2" right>Rp. <?= number_format($so['sub_total']) ?></td>
			</tr>
			<tr>
				<td colspan="3">sotongan Harga (Discount)</td>
				<td colspan="2" right>Rp. <?= number_format($so['diskon']) ?></td>
			</tr>
			<tr>
				<td colspan="3">Uang Muka</td>
				<td colspan="2" right>Rp. <?= number_format($so['uang_muka']) ?></td>
			</tr>
			<tr>
				<td colspan="3">Dasar Pengenaan Pajak (DPP)</td>
				<td colspan="2" right>Rp. <?= number_format($so['sub_total'] - $so['diskon']) ?></td>
			</tr>
			<?php $ppn_total = ($so['ppn'] / 100) * $so['sub_total']; ?>
			<tr>
				<td colspan="3">PPN 11%</td>
				<td colspan="2" right>Rp. <?= number_format($ppn_total) ?></td>
			</tr>
			<tr>
				<td colspan="3">Total Tagihan</td>
				<td colspan="2" right>Rp. <?= number_format($so['grand_total']) ?></td>
			</tr>
			<tr>
				<td colspan="5">
					<br>
					<div>Terbilang</div>
					<div><i>&nbsp;&nbsp;&nbsp;&nbsp;<?= terbilang($so['grand_total']) ?></i></div>
					<br>
				</td>
			</tr>
		</table>

		<br>

		<!-- footer -->
		<table class="table-bg">
			<!-- <tr>
				<td style="border:0px solid black !important">
					<div>Pembayaran dilakukang dengan transfer ke:</div>
					<div>nama_bank</div>
					<div>nama_cabang_bank</div>
					<div>A/C : 08</div>
					<div>Atas Nama : 08</div>
				</td>
				<td style="border:0px solid black !important">
					<div>PT. DINAMIKA PRIMA ARTHA</div>
					<br><br><br>
					<div>penerima</div>
				</td>
			</tr> -->
		</table>



		<pre><?php //print_r($hdan) 
				?></pre>

	</body>

	</html>
