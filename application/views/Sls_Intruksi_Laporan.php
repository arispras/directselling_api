<!DOCTYPEhtml>
	<html>

	<head>

		<?php require '__laporan_style.php' ?>

	</head>

	<body>

		<?php require '__laporan_header.php' ?>

		<br>
		<div center>
			<h1>LAPORAN INSTRUKSI PENGIRIMAN</h1>
		</div>
		<br>

		<div class="d-flex flex-between">
			<table class="" style="width:30%">
				<tr>
					<td>CUSTOMER</td>
					<th>:</th>
					<td><?= $filter_customer ?></td>
				</tr>

				<tr>
					<td>Periode Tanggal</td>
					<th>:</th>
					<td><?= tgl_indo($filter_tgl_awal) . ' s/d ' . tgl_indo($filter_tgl_akhir) ?></td>
				</tr>

			</table>
		</div>
		<hr>

		<div class="d-flex flex-between">
			<table class="" style="width:30%">


			</table>
		</div>
		<br>

		<?php $no = 0 ?>
		<?php foreach ($ip as $key => $val) { ?>

			<?php $no = $no + 1 ?>
			<table class="table-bg border">
				<thead>
					<tr>
						<!-- <th style="width:2%">No.</th> -->
						<th style="width:10%">Customer</th>
						<th style="width:10%">No Spk</th>
						<th style="width:10%">No IP</th>
						<th style="width:10%">Tanggal IP</th>
						<!-- <th style="width:10%">Alamat Pengiriman</th> -->
						<th style="width:10%">Periode Kirim Awal</th>
						<th style="width:10%">Periode Kirim Akhir</th>
						<th style="width:5%">Produk</th>
						<th style="width:7%">Jumlah DO</th>
						<th style="width:7%">Terkirim </th>
						<th style="width:7%">Sisa </th>

					</tr>

				</thead>

				<tbody>

					<tr>
						<!-- <td><?= $no ?></td> -->
						<td left><?= $val['nama_customer'] ?></td>
						<td left><?= $val['no_spk'] ?></td>
						<td left><?= $val['no_transaksi'] ?></td>
						<td center><?= $val['tanggal'] ?></td>
						<td center><?= $val['periode_kirim_awal'] ?></td>
						<td center><?= $val['periode_kirim_akhir'] ?></td>
						<td center><?= $val['produk'] ?></td>
						<td right><?= number_format($val['jumlah'], 2) ?></td>
						<td right><?= number_format($val['jum_kirim'], 2) ?></td>
						<td right><?= number_format($val['sisa'], 2) ?></td>
						
					</tr>

				</tbody>

				<tfoot></tfoot>
			</table>
			
			<h4>Detail Pengiriman:</h4>
			<table class="table-bg border" style="width:50%">
				<thead>
					<tr>
						<th style="width:2%">No.</th>
						<th style="width:10%">No Tiket</th>
						<th style="width:7%">Tanggal</th>
						<th style="width:10%">Netto</th>


					</tr>

				</thead>

				<tbody>
					<?php
					$timbangan = $val['dt'];
					$nom = 0;
					$jum = 0;
					?>
					<?php foreach ($timbangan as $key2 => $tb) { ?>

						<?php $nom = $nom + 1;
						$jum = $jum + $tb['netto_kirim']; ?>
						<tr>

							<td><?= $nom; ?></td>
							<td left><?= $tb['no_tiket'] ?></td>
							<td left><?= $tb['tanggal'] ?></td>
							<td right><?= number_format($tb['netto_kirim'], 2) ?></td>


						</tr>
					<?php } ?>
					<tr>

						<td colspan="3"></td>
						
						<td right><?= number_format($jum, 2) ?></td>


					</tr>
				</tbody>

				<tfoot></tfoot>
			</table>
			<br>
			<br>
			<hr>
		<?php } ?>





		<pre><?php //print_r($headeran) 
				?></pre>

	</body>

	</html>
