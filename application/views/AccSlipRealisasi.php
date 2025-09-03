<!DOCTYPEhtml>
	<html>

	<head>

		<?php require '__laporan_style.php' ?>
	</head>

	<body>


		<h1 class="title">VOUCHER REALISASI</h1>
		<br>

		<div class="">
			<table class="" style="width:50%">
				<tr>
					<td>Lokasi</td>
					<th>:</th>
					<td><?= $hd['lokasi'] ?></td>
				</tr>
				<tr>
					<td>Tipe</td>
					<th>:</th>
					<td><?= $hd['tipe'] ?></td>
				</tr>
				<tr>
					<td>No Transaksi</td>
					<th>:</th>
					<td><?= $hd['no_transaksi'] ?></td>
				</tr>

				<tr>
					<td>Tanggal</td>
					<th>:</th>
					<td><?= tgl_indo($hd['tanggal']) ?></td>
				</tr>
				<tr>
					<td>No uang Muka</td>
					<th>:</th>
					<td><?= $hd['no_uang_muka'] ?></td>
				</tr>

			</table>
		</div>
		<table class="table-bg border">
			<thead>
				<tr>
					<th rowspan="2">No</th>
					<th rowspan="2">Akun</th>
					<th rowspan="2">Keterangan</th>
					<th rowspan="2">Debet</th>
					<th rowspan="2">Kredit</th>

				</tr>

			</thead>

			<tbody>
				<?php $no = 0 ?>
				<?php $tot_dr = 0 ?>
				<?php $tot_cr = 0 ?>
				<tr>


				</tr>
				<?php foreach ($dt as $key => $val) { ?>

					<?php
					$no = $no + 1;
					$tot_cr = $tot_cr + $val['kredit'];
					$tot_dr = $tot_dr + $val['debet'];

					?>
					<tr>

						<td center width="2%"><?= $no ?></td>
						<td center width="10%"><?= $val['kode_akun'] . '-' . $val['nama_akun'] ?></td>
						<td center width="10%"><?= $val['ket'] ?></td>
						<td center width="10%"><?= number_format($val['debet']) ?></td>
						<td center width="10%"><?= number_format($val['kredit']) ?></td>

					</tr>
				<?php } ?>
				<tr>
					<?php
					$no = $no + 1;
					$tot_cr = $tot_cr + $hd['nilai_uang_muka'];

					?>
					<td center width="2%"><?= $no  ?></td>
					<td center width="10%"><?= $hd['kode_akun_uangmuka'] . '-' . $hd['nama_akun_uangmuka'] ?></td>
					<td center width="10%">Uang Muka</td>
					<td center width="10%">0</td>
					<td center width="10%"><?= number_format($hd['nilai_uang_muka']) ?></td>

				</tr>
				<tr>
					<?php
					$selisih = $hd['nilai_uang_muka'] - $hd['nilai_realisasi'];
					if ($selisih > 0) {
						$tot_dr = $tot_dr + $selisih;
					}
					if ($selisih < 0) {
						$tot_cr = $tot_cr + ($selisih * -1);
					}
					?>

					<td center width="2%"><?= $no + 1 ?></td>
					<td center width="10%"><?= $hd['kode_akun_kasbank'] . '-' . $hd['nama_akun_kasbank'] ?></td>
					<td center width="10%">KAS BANK</td>
					<td center width="10%"><?= ($selisih > 0) ? number_format($selisih) : 0 ?></td>
					<td center width="10%"><?= ($selisih < 0) ? number_format($selisih * -1):0 ?></td>

				</tr>
				<tr>

					<td colspan="3" center width="2%">Total</td>
					<td center width="10%"><?= number_format($tot_dr) ?></td>
					<td center width="10%"><?= number_format($tot_cr) ?></td>

				</tr>


			</tbody>

			<tfoot>

			</tfoot>
		</table>
		<table>
			<tr>

				<td center>
					<p>Dibuat Oleh</p>
					<br><br><br><br>
					<div>
						<div>( &nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)</div>
						<div></div>
					</div>
				</td>

				<td center>
					<p>Diketahui Oleh</p>
					<br><br><br><br>
					<div>
						<div>( &nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)</div>
						<div></div>
					</div>
				</td>
				<td center>
					<p>Disetujui Oleh</p>
					<br><br><br><br>
					<div>
						<div>( &nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</div>
						<div></div>
					</div>
				</td>
			</tr>
		</table>




		<pre><?php //print_r($hdan) 
				?></pre>

	</body>

	</html>
