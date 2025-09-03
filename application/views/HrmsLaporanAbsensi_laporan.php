<!DOCTYPEhtml>
<html>
	<head>
	
		<?php 
			if ($format_laporan=='view') {
				require '_laporan_style_fix.php';
			}
			else{
				if ($format_laporan=='pdf') {
					require '__laporan_style_pdf.php';
				}	
			}
		?>
	</head>
	<body>

		<?php require '__laporan_header.php' ?>

		<h3 class="title">Laporan Absensi PerKaryawan</h3>
		<br>
		
		<div class="d-flex flex-between">
			<table class="no_border" style="width:30%">
				<tr>
					<td style="width:30%">Lokasi</td>
					<th>:</th>
					<td><?= $input['lokasi_nama'] ?></td>
				</tr>
				<tr>
					<td style="width:30%">Karyawan</td>
					<th>:</th>
					<td><?= $karyawan['nama'] ?></td>
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



<?php
function selisih_waktu($data1, $data2)
{
	$data1 = explode(':', $data1);
	$data2 = explode(':', $data2);
	$calc1 = sprintf('%02d', abs($data1[0] - $data2[0]));
	$calc2 = sprintf('%02d', abs($data1[1] - $data2[1]));
	return $calc1.':'.$calc2;
}
?>


		
		<table class="table-bg border">
			<thead>
				<tr>
					<th rowspan="">No</th>
					<th rowspan="">Tanggal</th>
					<th rowspan="">Masuk</th>
					<th rowspan="">Pulang</th>
					<th rowspan="">Ket.</th>

					<!-- <th colspan="<?= count($date_loop) ?>"><?= $bulan[sprintf('%01d',$input['bulan'])] ?> <?= $input['tahun'] ?></th>
					<th colspan="<?= count($absensi_kode) ?>">Total</th> -->
				</tr>
				<!-- <tr>
					
					<?php foreach($date_loop as $key=>$val) { ?>
					<th><?= $key ?></th>
					<?php } ?>

					

				</tr> -->
			</thead>

			<tbody>
				<?php foreach ($absensi as $key=>$val) { ?>
				<?php $no += 1 ?>
				<tr>
					
					<td width="5%"><?= $no ?></td>
					<td><?= tgl_indo($val['tanggal']) ?></td>
					<td center width="10%"><?= $val['masuk'] ?></td>
					<td center width="10%"><?= $val['pulang'] ?></td>
					<td>(<?= $val['kode'] ?>) <?= $val['keterangan'] ?></td>
					
					<!-- <?php $absensi_ = $val;?>
					<?php foreach($date_loop as $key=>$val) { ?>
					<td><?= $absensi_[$key]['kode'] ?></td>
					<?php } ?> -->
					
					
				</tr>
				<?php } ?>
			</tbody>
			
			<tfoot></tfoot>
		</table>
		
		<br><br>
		
		<table class="table-bg border">
			<?php foreach($absensi_kode as $key=>$val) { ?>
				<tr>
					<td width="10%"><?= $key ?></td>
					<td><?= $absensi_total[$key] ?></td>
				</tr>
			<?php } ?>
		</table>





		<pre><?php //print_r($karyawan) ?></pre>

	</body>
</html>