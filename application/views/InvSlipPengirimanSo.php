<!DOCTYPEhtml>
	<html>

	<head>

		<?php require '__laporan_style.php' ?>
		<style>
			td,th{
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
		//  require '__laporan_header.php' 
		?>
		<table>
			<tr>
				<td center><img src="data:image/png;base64,<?= base64_encode(file_get_contents(('./logo_perusahaan.png'))) ?>" height="80px" width="180px"> </td>
			</tr>

		</table>
		<h1 style="font-size: 18px;" class="title">SURAT JALAN</h1>
		<br>

		<div class="d-flex flex-between">
			<table class="" style="width:70%">

				<!-- <tr>
					<td style="width:30%">Gudang</td>
					<th>:</th>
					<td><?= $header['gudang'] ?></td>
				</tr> -->
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
					<td style="width:30%">No. Sales Order</td>
					<th>:</th>
					<td><?= $header['no_so'] ?></td>
				</tr>
				<tr>
					<td>No PO Customer</td>
					<th>:</th>
					<td><?= $header['no_ref'] ?></td>
				</tr>
				<!-- <tr>
					<td>Status Transaksi</td>
					<th>:</th>
					<td><?= $header['is_posting'] == 1 ? 'Posting' : 'Belum Posting' ?></td>
				</tr> -->

			</table>
		</div>


		<div style="padding:15px 0;font-size: 13px;">
			Kepada Yth, <br> <?= $header['nama_customer'] ?>, kami kirimkan barang dengan detail sebagai berikut:
		</div>

		<!-- <div class="d-flex flex-between">
			<table class="" style="width:30%">
				<tr>
					<td style="width:30%">No. PO</td>
					<th>:</th>
					<td><?= $header['no_po'] ?></td>
				</tr>
				<tr>
					<td>Surat Jalan</td>
					<th>:</th>
					<td><?= $header['no_surat_jalan_supplier'] ?></td>
				</tr>
			
			</table>	
		</div> -->

		<br>


		<!-- <div class="d-flex flex-between">
			<table class="" style="width:30%">
			</table>
		</div>
		<p>Dengan detail sebagai Berikut :</p> -->


		<table class="table-bg border" style="font-size: 13px;">
			<thead>
				<tr>
					<th rowspan="1">No</th>
					<th rowspan="1">Kode Barang</th>
					<th rowspan="1">Nama Barang</th>
					<th rowspan="1">Satuan</th>
					<th rowspan="1">Kuantitas</th>
				</tr>

			</thead>

			<tbody>
				<?php $no = 0 ?>
				<?php foreach ($detail as $key => $val) { ?>

					<?php $no = $no + 1 ?>
					<tr>

						<td center width="2%"><?= $no ?></td>
						<td left width="10%"><?= $val['kode_barang'] ?></td>
						<td cenleftter width="10%"><?= $val['nama_barang'] ?></td>
						<td left width="10%"><?= $val['uom'] ?></td>
						<td right width="10%"><?= $val['qty'] ?></td>



					</tr>
				<?php } ?>

			</tbody>

			<tfoot></tfoot>
		</table>


		<br><br>
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


		<!-- <div class="d-flex flex-between">
			<table class="" style="width:30%">
				<tr>
					<td style="width:30%">Dibuat</td>
					<td>[ ........................ ]</td>
					<th>:</th>
					<td></td>
				</tr>
				<tr>
					<td>Diperiksa</td>
					<td>[ ........................ ]</td>
					<th>:</th>
					<td></td>
				</tr>
				<tr>
					<td>Diposting</td>
					<td>[ ........................ ]</td>
					<th>:</th>
					<td></td>
				</tr>
				<tr>
					<td>Mengetahui</td>
					<td>[ ........................ ]</td>
					<th>:</th>
					<td></td>
				</tr>
			</table>	
		</div> -->






		<pre><?php //print_r($headeran) 
				?></pre>

	</body>

	</html>