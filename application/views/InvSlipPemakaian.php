<!DOCTYPEhtml>
	<html>

	<head>

		<?php require '__laporan_style.php' ?>
	</head>

	<body>

		<?php require '__laporan_header.php' ?>

		<h1 class="title">FORM PEMAKAIAN BARANG</h1>
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

				<?php if (!empty($header['lokasi_afd'])) { ?>
				<tr>
					<td>Afdeling/Station</td>
					<th>:</th>
					<td><?= $header['lokasi_afd'] ?></td>
				</tr>
				<?php }else { ?>
				<tr>
					<td>Traksi</td>
					<th>:</th>
					<td><?= $header['lokasi_traksi'] ?></td>
				</tr>
				<?php } ?>
				
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
					<th >Kegiatan/Alokasi</th>
					<th >Blok/Mesin</th>
					<th >Kendaraan/AB/Mesin</th>
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
						<td center width="20%"><?= $val['nama_barang'] ?></td>
						<td center width="5%"><?= $val['qty'] ?></td>
						<td center width="5%"><?= $val['uom'] ?></td>
						<td center width="20%"><?= $val['nama_kegiatan'] ?></td>
						<td center width="20%"><?= $val['blok'] ?></td>
						<td center width="25%"><?= $val['nama_kendaraan'] ?> <?= $val['kode_kendaraan'] ?></td>
						<td center width="15%"><?= $val['ket'] ?></td>


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
