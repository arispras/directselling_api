<!DOCTYPEhtml>
<html>
	<head>
	
		<?php require '__laporan_style.php' ?>
	</head>
	<body>
		
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
					<th rowspan="2">tbs masuk</th>
					<th rowspan="2">tbs tersedia</th>
					<th colspan="2">tbs olah</th>
					<th rowspan="2">sisa (kg)</th>
					<th colspan="2">jam olah</th>
					<th colspan="2">downtime</th>
					<th colspan="8">cpo</th>
					<th colspan="8">kernel</th>
				</tr>
				<tr>
					<th>hi</th>
					<th>sdhi</th>
					<th>hi</th>
					<th>sdhi</th>
					<th>hi</th>
					<th>sdhi</th>
					
					<th>kg hi</th>
					<th>kg sdhi</th>
					<th>oer hi</th>
					<th>oer sdhi</th>
					<th>ffa hi</th>
					<th>ffa sdhi</th>
					<th>dirt hi</th>
					<th>dirt sdhi</th>

					<th>kg hi</th>
					<th>kg sdhi</th>
					<th>kadar air hi</th>
					<th>kadar air sdhi</th>
					<th>dirt hi</th>
					<th>dirt sdhi</th>
					<th>ffa hi</th>
					<th>ffa sdhi</th>
				</tr>
			</thead>

			<tbody>
				<?php for ($i=1; $i<count($produksi); $i++) { ?>

				<?php
					$produksi[$i]['lokasi'] = $input['lokasi_nama'];

					$produksi[$i]['tbs_masuk'] = 0;
					foreach ($produksi[$i]['timbangan_masuk'] as $row) {
						$produksi[$i]['tbs_masuk'] += $row['berat_bersih'];
					}

					$produksi[$i]['tbs_olah_hi'] = 0;
					foreach ($produksi[$i]['pengolahan'] as $row) {
						$produksi[$i]['tbs_olah_hi'] += $row['tbs_olah'];
						$produksi[$i]['tbs_olah_sdhi'] = $produksi[$i]['tbs_olah_hi'];
					}
					
					$produksi[$i]['tbs_olah_sdhi'] = $produksi[$i]['tbs_olah_hi'] + $produksi[$i-1]['tbs_olah_hi'];
					$produksi[$i]['tbs_tersedia'] = $produksi[$i]['tbs_masuk'] + $produksi[$i-1]['sisa'];
					$produksi[$i]['sisa'] = $produksi[$i]['tbs_tersedia'] - $produksi[$i]['tbs_olah_hi'];

					$produksi[$i]['jam_olah_hi'] = '00:00';
					$produksi[$i]['jam_olah_sdhi'] = '00:00';
					$produksi[$i]['kernel_kadarAir_hi'] = 0;
					$produksi[$i]['kernel_kadarAir_sdhi'] = 0;

					$produksi[$i]['cpo_kg_hi'] = 0;
					$produksi[$i]['cpo_kg_sdhi'] = 0;
					$produksi[$i]['cpo_ffa_hi'] = 0;
					$produksi[$i]['cpo_ffa_sdhi'] = 0;
					$produksi[$i]['cpo_dirt_hi'] = 0;
					$produksi[$i]['cpo_dirt_sdhi'] = 0;

					$produksi[$i]['kernel_ffa_hi'] = 0;
					$produksi[$i]['kernel_ffa_sdhi'] = 0;
					$produksi[$i]['kernel_dirt_hi'] = 0;
					$produksi[$i]['kernel_dirt_sdhi'] = 0;

					foreach ($produksi[$i]['pengolahan'] as $row) {
						$produksi[$i]['jam_olah_hi'] = selisih_waktu($row['jam_masuk'], $row['jam_selesai']);
						$produksi[$i]['jam_olah_sdhi'] = $produksi[$i]['jam_olah_hi'];

						$produksi[$i]['cpo_kg_hi'] += $row['netto_kirim'];
						$produksi[$i]['cpo_kg_sdhi'] = $produksi[$i]['cpo_kg_hi'];
						$produksi[$i]['cpo_ffa_hi'] += $row['ffa'];
						$produksi[$i]['cpo_ffa_sdhi'] = $produksi[$i]['cpo_ffa_hi'];
						$produksi[$i]['cpo_dirt_hi'] += $row['ffa'];
						$produksi[$i]['cpo_dirt_sdhi'] = $produksi[$i]['cpo_dirt_hi'];

						$produksi[$i]['kernel_ffa_hi'] += $row['kernel_ffa'];
						$produksi[$i]['kernel_ffa_sdhi'] = $produksi[$i]['kernel_ffa_hi'];
						$produksi[$i]['kernel_dirt_hi'] += $row['kernel_dirt'];
						$produksi[$i]['kernel_dirt_sdhi'] = $produksi[$i]['kernel_dirt_hi'];
					}

					?>

				<tr>
					<td><?= $produksi[$i]['lokasi'] ?></td>
					<td><?= $produksi[$i]['tanggal'] ?></td>
					<!-- <td></td> -->
					<td><?= $produksi[$i]['tbs_masuk'] ?></td>
					<td><?= $produksi[$i]['tbs_tersedia'] ?></td>
					<td><?= $produksi[$i]['tbs_olah_hi'] ?></td>
					<td><?= $produksi[$i]['tbs_olah_sdhi'] ?></td>
					<td><?= $produksi[$i]['sisa'] ?></td>
					<td><?= $produksi[$i]['jam_olah_hi'] ?></td>
					<td><?= $produksi[$i]['jam_olah_sdhi'] ?></td>
					<td></td>
					<td></td>
					<td><?= $produksi[$i]['cpo_kg_hi'] ?></td>
					<td><?= $produksi[$i]['cpo_kg_sdhi'] ?></td>
					<td></td>
					<td></td>
					<td><?= $produksi[$i]['cpo_ffa_hi'] ?></td>
					<td><?= $produksi[$i]['cpo_ffa_sdhi'] ?></td>
					<td><?= $produksi[$i]['cpo_dirt_hi'] ?></td>
					<td><?= $produksi[$i]['cpo_dirt_sdhi'] ?></td>
					
					
					<td></td>
					<td></td>
					<td><?= $produksi[$i]['kernel_kadarAir_hi'] ?></td>
					<td><?= $produksi[$i]['kernel_kadarAir_sdhi'] ?></td>
					<td><?= $produksi[$i]['kernel_dirt_hi'] ?></td>
					<td><?= $produksi[$i]['kernel_dirt_sdhi'] ?></td>
					<td><?= $produksi[$i]['kernel_ffa_hi'] ?></td>
					<td><?= $produksi[$i]['kernel_ffa_sdhi'] ?></td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot></tfoot>

		</table>


		<pre><?php //print_r($produksi) ?></pre>

	</body>
</html>