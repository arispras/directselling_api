<!DOCTYPEhtml>
<html>
	<head>
	
		<?php require '__laporan_style.php' ?>
	</head>
	<body>
		
		<?php require '__laporan_header.php' ?>

		<!-- <pre><?php print_r($po) ?></pre> -->

		<h1 class="title">REKAP PEMELIHARAAN HK & PREMI MANDOR KERANI</h1>
		<br>
		
		<div class="d-flex flex-between">
			<table class="" style="width:44%">
				
				<tr>
					<td>Periode Tanggal</td>
					<th>:</th>
					<td><?= tgl_indo($filter_tgl_awal) .' s/d '. tgl_indo($filter_tgl_akhir) ?></td>
				</tr>
				<tr>
					<td>Lokasi</td>
					<th>:</th>
					<td><?=($filter_lokasi) ?></td>
				</tr>
				<tr>
					<td>Afdeling</td>
					<th>:</th>
					<td><?=($filter_afdeling) ?></td>
				</tr>
			
			</table>	
		</div>

		<div class="d-flex flex-between">
			<table class="" style="width:30%">
				
				
			</table>
		</div>
		<br>
		

		<table class="table-bg border">
			<thead>
					
				<tr>
					<th  center style="width:2%">no</th>
					<th style="width:15%" >Uraian Pekerjaan</th>
					<th style="width:10%">Tenaga Kerja</th>
					<th style="width:10%" >Upah HK</th>
					<th style="width:10%" >PREMI</th>
				</tr>
				

				
				
			</thead>

			<tbody>
			<?php $no= 0
			?>
			<?php foreach ($bkm as $key=>$val) { ?> 
				
				<?php $no= $no+1

				?>
				<tr>
					<td><?= $no ?></td>
					<td center><?= $val['ket'] ?></td>
					<td center><?= number_format($val['hk'],2)?></td>
					<td center><?= number_format($val['rp_hk'],2)?></td>
					<td center><?= number_format($val['premi'],2)?></td>

				</tr>
				<?php } ?>
			</tbody>
			
			<tfoot></tfoot>
		</table>
		





		<pre><?php //print_r($headeran) ?></pre>

	</body>
</html>


				
