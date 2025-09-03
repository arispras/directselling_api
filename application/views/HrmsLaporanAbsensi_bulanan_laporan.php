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
				echo $html='
				<style>
				* body{
					font-size: 8px ;
				}
				
				.table-bg th,
				.table-bg td {
				border: 0.3px solid rgba(0, 0, 0, 0.4);
				padding: 5px 8px;
				}
			</style>';
				}	
			}
		?>
		
		
	</head>
	<body>

		<?php require '__laporan_header.php' ?>

		<h3 class="title">Laporan Absensi Bulanan</h3>
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
					<td><?= $bulan[sprintf('%01d',$input['bulan'])] ?> - <?= $input['tahun'] ?></td>
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
					<th rowspan="2">No</th>
					<th rowspan="2">Nik</th>
					<th rowspan="2">Nama</th>

					<th colspan="<?= count($date_loop) ?>"><?= $bulan[sprintf('%01d',$input['bulan'])] ?> <?= $input['tahun'] ?></th>
					<th colspan="<?= count($absensi_kode) ?>">Total</th>
				</tr>
				<tr>
					
					<?php foreach($date_loop as $key=>$val) { ?>
					<th><?= $key ?></th>
					<?php } ?>

					<?php foreach($absensi_kode as $key=>$val) { ?>
					<th><?= $key ?></th>
					<?php } ?>

				</tr>
			</thead>

			<tbody>
				<?php foreach ($absensi as $key=>$val) { ?>
				<?php $no += 1 ?>
				<tr>
					
					<td><?= $no ?></td>
					<td><?= $val['nip'] ?></td>
					<td><?= $val['nama'] ?></td>
					
					<?php $absensi_ = $val;?>
					<?php foreach($date_loop as $key=>$val) { ?>
					<td><?= $absensi_[$key]['kode'] ?></td>
					<?php } ?>
					
					<?php foreach($absensi_kode as $key=>$val) { ?>
					<td><?= $absensi_[$key]; ?></td>
					<?php } ?>

				</tr>
				<?php } ?>
			</tbody>

			<tfoot></tfoot>

		</table>


		<pre><?php //print_r($absensi) ?></pre>

	</body>
</html>