<!DOCTYPEhtml>
<html>
	<head>
		<?php 
			if ($tipe_laporan=='pdf') {
					require '__laporan_style_pdf.php';
				echo $html='
				<style>
				* body{
					font-size: 8px ;
				}
				</style>';
			}
			else {
				require '_laporan_style_fix.php';
			}	
		?>
		<?php require '_laporan_style_fix.php'; ?>
	</head>
	<body>
	<?php require '__laporan_header.php' ?>

		<h3 class="title">Rekap LHP</h3>
		<br>
		
		<div class="d-flex flex-between">
			<table class="no_border" style="width:30%">
				<tr>
					<td style="width:30%">Lokasi</td>
					<th>:</th>
					<td><?= $input['lokasi_nama'] ?></td>
				</tr>
				<tr>
					<td>Tanggal</td>
					<th>:</th>
					<td><?= tgl_indo($input['tgl_mulai']) ?> - <?= tgl_indo($input['tgl_akhir']) ?></td>
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


		
		<table border="0.45px" class="table-bg border">
			
			<thead>
				<tr>
					<th rowspan="2">tanggal</th>
					<!-- <th rowspan="2">shift</th> -->
					<th rowspan="2">sisa tbs kemarin (Kg)</th>
					<th rowspan="2">tbs masuk (kg)</th>
					<th rowspan="2">tbs diolah (kg)</th>
					<th rowspan="2">sisa (kg)</th>
					<!-- <th colspan="2">jam olah</th> -->
					<!-- <th colspan="2">downtime</th> -->
					<th colspan="6">cpo</th>
					<th colspan="6">kernel</th>
				</tr>
				<tr>
					<th>CPO (kg)</th>
					<th>OER (%)</th>
					<th>FFA (kg)</td>
					<th>DIRT (kg)</th>
					<th>MOIST (kg)</th>
					<th>Loses (%)</th>
					
					<th>KERNEL (kg)</th>
					<th>OER (%)</th>
					<th>FFA (kg)</th>
					<th>DIRT (kg)</th>
					<th>MOIST (kg)</th>
					<th>Loses (%)</th>
				</tr>
			</thead>

			<tbody>

				<?php foreach ($produksi as $key=>$val) { ?>
					
				<tr>
					<td center><?= tgl_indo_normal($val['tanggal']) ?></td>
					<!-- <td></td> -->
					<td right><?= ($val['tbs_kemarin'] == null) ? 0: number_format($val['tbs_kemarin']) ?></td>
					<td right><?= ($val['satu_total_hi'] == null) ? 0: number_format($val['satu_total_hi']) ?></td>
					<td right><?= ($val['dua_hi'] == null) ? 0: number_format($val['dua_hi']) ?></td>
					<td right><?= ($val['dua_restan'] == null) ? 0: number_format($val['dua_restan']) ?></td>
					<td right><?= ($val['tiga_hi'] == null) ? 0: number_format($val['tiga_hi']) ?></td>
					<td right><?= ($val['tiga_rendemen_hi']== null) ? 0:number_format($val['tiga_rendemen_hi'],2) ?></td>
					<td right><?= ($val['tiga_ffa'] == null) ? 0:$val['tiga_ffa'] ?></td>
					<td right><?= ($val['tiga_kadar_kotoran'] == null) ? 0:$val['tiga_kadar_kotoran'] ?></td>
					<td right><?= ($val['tiga_kadar_air'] == null) ? 0:$val['tiga_kadar_air'] ?></td>
					<td right><?= ($val['sebelas_oil_total'] == null) ? 0: $val['sebelas_oil_total'] ?></td>
					<td right><?= ($val['delapan_hi'] == null) ? 0: number_format($val['delapan_hi']) ?></td>
					<td right><?= ($val['delapan_rendemen_hi'] == null) ? 0:number_format($val['delapan_rendemen_hi'],2) ?></td>
					<td right><?= ($val['delapan_ffa_hi'] == null) ? 0:$val['delapan_ffa_hi'] ?></td>
					<td right><?= ($val['delapan_kadar_kotoran_hi'] == null) ? 0:$val['delapan_kadar_kotoran_hi'] ?></td>
					<td right><?= ($val['delapan_kadar_air_hi'] == null) ? 0:$val['delapan_kadar_air_hi'] ?></td>
					<td right><?= ($val['sebelas_kernel_total'] == null) ? 0:$val['sebelas_kernel_total'] ?></td>

				</tr>
				<?php } ?>
			</tbody>

			<tfoot></tfoot>

		</table>


		<pre><?php //print_r($input) ?></pre>

	</body>
</html>
