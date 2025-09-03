<!DOCTYPEhtml>
	<html>

	<head>

		<?php require '__laporan_style.php' ?>
	</head>

	<body>

		<?php require '__laporan_header.php' ?>

		<div center>
			<h1 class="title">Rekapitulasi Pembayaran</h1>

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
					<th>Satuan</th>
					<th>Jml Qty</th>
					<th>Jumlah</th>


				</tr>
			</thead>

			<tbody>
				<?php
				$no = 0;
				$j_hm_km = 0;
				$total_jumlah = 0;
				?>
				<?php foreach ($detail as $key => $val) { ?>
					<?php
					$no = $no + 1;
					$j_hm_km = $j_hm_km + $val['qty'];
					$total_jumlah = $total_jumlah + $val['jumlah'];
					?>
					<tr>
						<td center width="2%"><?= $no ?></td>
						<td center width="4%"><?= tgl_indo($val['tanggal_operasi']) ?></td>
						<td center width="4%"><?= ($val['uom']) ?></td>
						<td center width="4%"><?= number_format($val['qty']) ?></td>
						<td center width="4%"><?= number_format($val['jumlah']) ?></td>

					</tr>
				<?php } ?>

			</tbody>
			<tfoot>
				<tr>
					<td right colspan="3">Total </td>
					<td center><?= number_format($j_hm_km) ?></td>
					<td center><?= number_format($total_jumlah) ?></td>
				</tr>
			</tfoot>
		</table>
		<p>Dengan Detail Opt :</p>
		<table class="table-bg border">
			<thead>
				<tr>
					<th>No</th>
					<th>Tanggal</th>
					<th>Jumlah</th>
				</tr>
			</thead>

			<tbody>
				<?php
				$no = 0;
				$j_hm_km = 0;
				$total_jumlah = 0;
				?>
				<?php foreach ($detail_opt as $key => $val) { ?>
					<?php
					$no = $no + 1;
					$total_jumlah = $total_jumlah + $val['jumlah_opt'];
					?>
					<tr>
						<td center width="2%"><?= $no ?></td>
						<td center width="4%"><?= tgl_indo($val['tanggal_opt']) ?></td>
						<td center width="4%"><?= number_format($val['jumlah_opt']) ?></td>

					</tr>
				<?php } ?>

			</tbody>
			<tfoot>
				<tr>
					<td right colspan="2" > Total</td>
					<td center><?= number_format($total_jumlah) ?></td>
				</tr>
			</tfoot>
		</table>







		<pre><?php //print_r($headeran) 
				?></pre>

	</body>

	</html>
