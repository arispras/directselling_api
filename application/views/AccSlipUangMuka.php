<!DOCTYPEhtml>
	<html>

	<head>
		
		<?php require '__laporan_style.php' ?>
	</head>

	<body>



		<h1 class="title">VOUCHER UANG MUKA</h1>
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
					<td>Payment</td>
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
		
					<tr>

						<td center width="2%">1</td>
						<td center width="10%"><?= $hd['kode_akun_uangmuka'] . '-' . $hd['nama_akun_uangmuka'] ?></td>
						<td center width="10%"><?= $hd['keterangan'] ?></td>
						<td center width="10%"><?= number_format($hd['nilai']) ?></td>
						<td center width="10%">-</td>

					</tr>
				<tr>
					
					<td center width="2%">2</td>
					<td center width="10%"><?= $hd['kode_akun_kasbank'] . '-' . $hd['nama_akun_kasbank'] ?></td>
					<td center width="10%">KAS BANK</td>
					<td center width="10%">-</td>
					<td center width="10%"><?= number_format($hd['nilai']) ?></td>

				</tr>
				<tr>

					<td colspan="3" center width="2%">Total</td>

					<td center width="10%"><?= number_format($hd['nilai']) ?></td>
					<td center width="10%"><?= number_format($hd['nilai']) ?></td>

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
