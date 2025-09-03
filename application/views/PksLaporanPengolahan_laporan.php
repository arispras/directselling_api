<!DOCTYPEhtml>
<html>
	<head>
	
	<?php 
			if ($tipe_laporan=='pdf') {
					require '__laporan_style_pdf.php';
				echo $html='
				<style>
				* body{
					font-size: 9px ;
				}
				</style>';
			}
			else {
				require '_laporan_style_fix.php';
			}	
		?>
	</head>
	<body>

		<h3 class="title">Laporan Pengolahan</h3>
		<br>
		
		<div class="d-flex flex-between">
			<table class="no_border" style="width:30%">
				<tr>
					<td style="width:30%">Lokasi</td>
					<th>:</th>
					<td><?= $input['lokasi_nama'] ?></td>
				</tr>
				<tr>
					<td>Priode</td>
					<th>:</th>
					<td><?= tgl_indo($input['tgl_mulai']) ?> s/d <?= tgl_indo($input['tgl_akhir']) ?></td>
				</tr>
			</table>

			<!-- <table class="border" style="width:30%"></table> -->
		</div>
		
		<br><br>


		<?php foreach ($pengolahan as $key=>$val) { ?>
		
		<div class="mt-1" style="background-color:rgba(240,240,240); padding:10px">
			
			<div class="d-flex flex-between mt-1" style="padding:1%;">
				<table class="no_border" style="width:40%">
					<tr>
						<th left>No Transaksi</th>
						<th left>:</th>
						<th><?= $val['no_transaksi'] ?></th>

						<th left>Total Jam Proses</th>
						<th >:</th>
						<th><?= $val['total_jam_proses'] ?> Jam</th>
					</tr>
					<tr>
						<th left >Tanggal</th>
						<th left>:</th>
						<th><?= tgl_indo($val['tanggal']) ?></th>

						<th left style="width:30%">Total Jumlah Rebusan</th>
						<th center>:</th>
						<th><?= $val['total_jumlah_rebusan'] ?> </th>
					</tr>

					<tr>
						<th left>Tbs Olah</th>
						<th center>:</th>
						<th><?= number_format($val['tbs_olah']) ?></th>
					</tr>
				</table>
			</div>
			

			<div style="width:100%; padding:1%">
				<b>Shift :</b>
				<table class="table-bg border mt-1" style="background-color:white">
					<thead>
						<tr>
							<th width="5%">No</th>
							<th>Shift</th>
							<th>Jam Masuk</th>
							<th>Jam Selesai</th>
							<th>Mandor</th>
							<th>Asisten</th>
							<th>Jam Proses (Jam)</th>
							<th>Jumlah Rebusan</th>
						</tr>
					</thead>
					<tbody>
						<?php $no1 = 0; ?>
						<?php foreach ($val['detail'] as $_key=>$_val) { ?>
						<?php $no1 += 1; ?>
						<tr>
							<td><?= $no1 ?></td>
							<td><?= $_val['shift'] ?></td>
							<td center><?= $_val['jam_masuk'] ?></td>
							<td center><?= $_val['jam_selesai'] ?></td>
							<td><?= $_val['mandor'] ?></td>
							<td><?= $_val['asisten'] ?></td>
							<td center><?= $_val['jam_proses'] ?></td>
							<td center><?= $_val['jumlah_rebusan'] ?></td>
						</tr>
						<?php } ?>
					</tbody>
					<tfoot></tfoot>
				</table>
			</div>

			<div style="width:100%; padding:1%">
				<b>Mesin :</b>
				<table class="table-bg border mt-1" style="background-color:white">
					<thead>
						<tr>
							<th width="5%">No</th>
							<th>Mesin</th>
							<th>Jam Masuk</th>
							<th>Jam Selesai</th>
							<th>Jumlah Jam</th>
							<th>Ket.</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($val['mesin'] as $_key=>$_val) { ?>
						<?php $no1 += 1; ?>
						<tr>
							<td><?= $no1 ?></td>
							<td><?= $_val['mesin'] ?></td>
							<td center><?= $_val['jam_masuk'] ?></td>
							<td center><?= $_val['jam_selesai'] ?></td>
							<td center><?= $_val['jumlah_jam'] ?></td>
							<td><?= $_val['keterangan'] ?></td>
						</tr>
						<?php } ?>
					</tbody>
					<tfoot></tfoot>
				</table>
			</div>
			
		</div>

		
		<?php } ?>





		<pre><?php //print_r($pengolahan) ?></pre>

	</body>
</html>
