<!DOCTYPEhtml>
	<html>

	<head>

		<?php require '__laporan_style.php' ?>
	</head>

	<body>

		<?php require '__laporan_header.php' ?>

		<h1 class="title">FORM ADJUSMENT BARANG</h1>
		<br>
		<!-- <?= $status=($header['is_posting']==1?'Posting':'Belum Posting') ?> -->
		<div class="d-flex flex-between">
			<table class="" style="width:50%">
				<tr>
					<td>Lokasi</td>
					<th>:</th>
					<td><?= $header['lokasi'] ?></td>
				</tr>
				<tr>
					<td style="width:25%">Gudang</td>
					<th>:</th>
					<td><?= $header['gudang'] ?></td>
				</tr>
				<tr>
					<td>Nomor Dokumen</td>
					<th>:</th>
					<td><?= $header['no_transaksi'] ?></td>
				</tr>

				
				<tr>
					<td>Tanggal</td>
					<th>:</th>
					<td><?= tgl_indo($header['tanggal']) ?></td>
				</tr>
				<tr>
					<td>Status Transaksi</td>
					<th>:</th>
					<td><?= $status ?></td>
				</tr>
			</table>
		</div>

<br>
<br>
		<table class="table-bg border">
			<thead>
				<tr>
					<th >No</th>
					<th >Kode Barang</th>
					<th >Nama Barang</th>
					<th >QTY</th>
					<th >UOM</th>
					<th >Harga</th>
					<th >Total</th>
					<th >Keterangan</th>
				</tr>

			</thead>

			<tbody>
				<?php $no = 0 ?>
				<?php foreach ($detail as $key => $val) { ?>

					<?php $no = $no + 1 ?>
					<tr>

						<td center width="2%"><?= $no ?></td>
						<td center width="10%"><?= $val['kode_barang'] ?></td>
						<td center width="25%"><?= $val['nama_barang'] ?></td>
						<td center width="5%"><?= $val['qty'] ?></td>
						<td center width="5%"><?= $val['uom'] ?></td>
						<td center width="15%"><?= $val['harga'] ?></td>
						<td center width="15%"><?= $val['total'] ?></td>
						<td center width="20%"><?= $val['ket'] ?></td>


					</tr>
				<?php } ?>

			</tbody>

			<tfoot></tfoot>
		</table>
		<br>
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





		<pre><?php //print_r($headeran) 
				?></pre>

	</body>

	</html>
