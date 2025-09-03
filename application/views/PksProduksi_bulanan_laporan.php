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
					<td>Priode</td>
					<th>:</th>
					<td><?= $input['bulan_mulai']?>-<?= $input['tahun_mulai'] ?> s/d <?= $input['bulan_akhir']?>-<?= $input['tahun_akhir'] ?></td>
				</tr>
			</table>

			<!-- <table class="border" style="width:30%"></table> -->
		</div>



		
		<br><br>



		
		<table class="table-bg border">
			
			<thead>
				<tr>
					<th rowspan="2">lokasi</th>
					<th rowspan="2">Bulan</th>
					<th rowspan="2">anggaran tbs (kg)</th>
					<th rowspan="2">tbs olah (kg)</th>
					<th colspan="8">cpo</th>
					<th colspan="8">kernel</th>
				</tr>
				<tr>
					<th>anggaran kg</th>
					<th>aktual kg</th>
					<th>anggaran oer</th>
					<th>aktual oer</th>
					<th>anggaran ffa</th>
					<th>aktual ffa</th>
					<th>anggaran dirt</th>
					<th>aktual dirt</th>

					<th>anggaran kg</th>
					<th>aktual kg</th>
					<th>anggaran moist</th>
					<th>aktual moist</th>
					<th>anggaran dirt</th>
					<th>aktual dirt</th>
					<th>anggaran ffa</th>
					<th>aktual ffa</th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($produksi as $row) { ?>
				<?php $row_bulan = sprintf('%01d', explode('-', $row['tanggal'])[1]); ?>
				<tr>
					<td><?= $row['lokasi'] ?></td>
					<td center><?= $bulan[$row_bulan]; ?></td>
					<td center></td>
					<td center><?= $row['tbs_olah'] ?></td>
					<td center></td>
					<td center><?= $row['cpo_kg'] ?></td>
					<td center></td>
					<td center><?= $row['cpo_oer'] ?></td>
					<td center></td>
					<td center><?= $row['cpo_ffa'] ?></td>
					<td center></td>
					<td center><?= $row['cpo_dirt'] ?></td>
					<td center></td>
					<td center><?= $row['kernel_kg'] ?></td>
					<td center></td>
					<td center><?= $row['kernel_moisture'] ?></td>
					<td center></td>
					<td center><?= $row['kernel_dirt'] ?></td>
					<td center></td>
					<td center><?= $row['kernel_ffa'] ?></td>
				</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<th colspan="3"></th>
					<th><?= $count['tbs_olah'] ?></th>
					<th></th>
					<th><?= $count['cpo_kg'] ?></th>
					<th></th>
					<th><?= $count['cpo_oer'] ?></th>
					<th></th>
					<th><?= $count['cpo_ffa'] ?></th>
					<th></th>
					<th><?= $count['cpo_dirt'] ?></th>
					<th></th>
					<th><?= $count['kernel_kg'] ?></th>
					<th></th>
					<th><?= $count['kernel_moisture'] ?></th>
					<th></th>
					<th><?= $count['kernel_dirt'] ?></th>
					<th></th>
					<th><?= $count['kernel_ffa'] ?></th>
				</tr>
			</tfoot>

		</table>



		<pre><?php //print_r($produksi); ?></pre>

	</body>
</html>