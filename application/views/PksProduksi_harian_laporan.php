<!DOCTYPEhtml>
<html>
	<head>
	
		<?php require '__laporan_style.php' ?>
	</head>
	<body>

		<h3 class="title">Laporan Produksi Bulanan</h3>
		<br>
		
		<div class="d-flex flex-between">
			<table class="" style="width:30%">
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


		
		<table class="table-bg border">
			
			<thead>
				<tr>
					<th rowspan="2">lokasi</th>
					<th rowspan="2">tanggal</th>
					<!-- <th rowspan="2">shift</th> -->
					<th rowspan="2">sisa tbs kemarin</th>
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
				<?php
					if ( $val['cpo_produksi'] ==null ||$val['tbs_olah']==null ){
						$cpo_oer = 0;
					}else{
						if ( $val['cpo_produksi'] ==0 ||$val['tbs_olah']==0 ){
							$cpo_oer = 0;
						}else{
							$cpo_oer = $val['cpo_produksi'] / $val['tbs_olah'] * 100;
						}
						
					}
					if ( $val['kernel_produksi'] ==null ||$val['tbs_olah']==null ){
						$kernel_oer = 0;
						
					}else{
						if ( $val['kernel_produksi'] ==0 ||$val['tbs_olah']==0 ){
							$kernel_oer = 0;
						}else{
							$kernel_oer = $val['kernel_produksi'] / $val['tbs_olah'] * 100;
						}
					}
					
					$cpo_loses = $val['cpo_los_press'] +$val['cpo_los_nut'] + $val['cpo_los_e_bunch'] + $val['cpo_los_effluent'] + $val['cpo_los_fruit'];
					$kernel_loses = $val['kernel_los_fruit'] + $val['kernel_los_fiber_cyclone'] + $val['kernel_los_ltds1'] + $val['kernel_los_ltds2'] + $val['kernel_los_claybath'];
					
					?>

				<tr>
					<td><?= $val['lokasi'] ?></td>
					<td><?= $val['tanggal_produksi'] ?></td>
					<!-- <td></td> -->
					<td><?= ($val['tbs_kemarin'] == null) ? 0:$val['tbs_kemarin'] ?></td>
					<td><?= ($val['tbs_masuk'] == null) ? 0:$val['tbs_masuk'] ?></td>
					<td><?= ($val['tbs_olah'] == null) ? 0:$val['tbs_olah'] ?></td>
					<td><?= ($val['tbs_sisa'] == null) ? 0:$val['tbs_sisa'] ?></td>
					<td><?= ($val['cpo_produksi'] == null) ? 0:$val['cpo_produksi'] ?></td>
					<td><?= ($cpo_oer == null) ? 0:number_format($cpo_oer,2) ?></td>
					<td><?= ($val['cpo_ffa'] == null) ? 0:$val['cpo_ffa'] ?></td>
					<td><?= ($val['cpo_dirt'] == null) ? 0:$val['cpo_dirt'] ?></td>
					<td><?= ($val['cpo_moisture'] == null) ? 0:$val['cpo_moisture'] ?></td>
					<td><?= ($cpo_loses == null) ? 0: $cpo_loses ?></td>
					<td><?= ($val['kernel_produksi'] == null) ? 0:$val['kernel_produksi'] ?></td>
					<td><?= ($kernel_oer == null) ? 0:number_format($kernel_oer,2) ?></td>
					<td><?= ($val['kernel_ffa'] == null) ? 0:$val['kernel_ffa'] ?></td>
					<td><?= ($val['kernel_dirt'] == null) ? 0:$val['kernel_dirt'] ?></td>
					<td><?= ($val['kernel_moisture'] == null) ? 0:$val['kernel_moisture'] ?></td>
					<td><?= ($kernel_loses == null) ? 0:$kernel_loses ?></td>

				</tr>
				<?php } ?>
			</tbody>

			<tfoot></tfoot>

		</table>


		<pre><?php //print_r($input) ?></pre>

	</body>
</html>
