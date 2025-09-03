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
		
		<h3 class="title">Laporan Lembur</h3>
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
					<th rowspan="3">No</th>
					<th rowspan="3">Nik</th>
					<th rowspan="3">Nama</th>
					<th rowspan="3">Sub Bagian</th>

					<th colspan="<?= count($date_loop) * 2 ?>"><?= $bulan[sprintf('%01d',$input['bulan'])] ?> <?= $input['tahun'] ?></th>
					<th colspan="2" rowspan="2">Total</th>
				</tr>
				<tr>
					
					<?php foreach($date_loop as $key=>$val) { ?>
					<th colspan="2"><?= $key ?></th>
					<?php } ?>

				</tr>
				<tr>
					
					<?php foreach($date_loop as $key=>$val) { ?>
						<th>Jam</th>
						<th>RP</th>
					<?php } ?>
						
					<th>Jam</th>
					<th>RP</th>
				
				</tr>
			</thead>

			<tbody>
				<?php foreach ($lembur as $key=>$val) { ?>
				<?php $no += 1 ?>
				<tr>
					
					<td><?= $no ?></td>
					<td><?= $val['nip'] ?></td>
					<td><?= $val['nama'] ?></td>
					<td><?= $val['sub_bagian'] ?></td>
					
					<?php $lembur_ = $val;?>
					<?php $total_jam = 0; ?>
					<?php $total_nilai = 0; ?>
					<?php foreach($date_loop as $key=>$val) { ?>
						<?php $total_jam += $lembur_[$key]['jumlah_jam'] ?>
						<?php $total_nilai += $lembur_[$key]['nilai_lembur'] ?>
					<td center><?= number_format($lembur_[$key]['jumlah_jam'], 2, '.', '') ?></td>
					<td center><?= number_format($lembur_[$key]['nilai_lembur']) ?></td>
					<?php } ?>
					
					<td center><?= number_format($total_jam, 2, '.', '')  ?></td>
					<td center><?= number_format($total_nilai) ?></td>

				</tr>
				<?php } ?>
			</tbody>

			<tfoot></tfoot>

		</table>


		<pre><?php //print_r($lembur) ?></pre>

	</body>
</html>