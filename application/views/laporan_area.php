<!DOCTYPEhtml>
<html>
	<head>
	
		<?php require '__laporan_style.php' ?>
	</head>
	<body>

		<h3 class="title">Laporan Area Blok</h3>
		<br>
		
		<!-- <div class="d-flex flex-between">
			<table class="" style="width:30%">
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
			</table> -->

			<!-- <table class="border" style="width:30%"></table> -->
		</div>



		
		<br><br>





		
		<table class="table-bg border">
			
			<thead>
				<tr>
					<th rowspan="3">No</th>
					<th rowspan="3">Kode Blok</th>
					<th rowspan="3">Nama Blok</th>
					<th rowspan="3">Estate</th>
					<th rowspan="3">Rayon</th>
					<th rowspan="3">Afdeling</th>
					<th rowspan="3">Tahun Tanam</th>
					<th rowspan="3">Luas</th>
					<th rowspan="3">Jumlah Pokok</th>
					<th rowspan="3">SPH</th>

				</tr>
				
				
			</thead>

			<tbody>
				
				<?php foreach ($lap_area as $key=>$v) { ?>
				
				<tr>
					
					<td><?= $key ?></td>
					<td><?= $v['kode_blok'] ?></td>
					<td><?= $v['nama_blok'] ?></td>
					<td><?= $v['nama_estate'] ?></td>
					<td><?= $v['nama_rayon'] ?></td>
					<td><?= $v['nama_afdeling'] ?></td>
					<td><?= $v['tahuntanam'] ?></td>
					<td><?= $v['luasareaproduktif'] ?></td>
					<td><?= $v['jumlahpokok'] ?></td>
					<td><?= number_format ($v['sph'],1) ?></td>
					
					
				
					
				</tr>
				<?php } ?>
			</tbody>

			<tfoot></tfoot>

		</table>


		<pre><?php //print_r($lembur) ?></pre>

	</body>
</html>
