<!DOCTYPEhtml>
<html>
	<head>
		
		<?php require '__laporan_style.php' ?>
	</head>
	<body>
		
		<?php require '__laporan_header.php' ?>

		<div center>
			<h1 class="title">FORM SPAT</h1>
			<p></p>
		</div>
		
		<div class="d-flex flex-between">
		<table class="" style="width:40%;line-height: 20px;">
				<tr>
					<td  style="width:20%">Lokasi</td>
					<td >: </td>
					<td><?= $header['lokasi'] ?></td>
				</tr>
				<tr>
					<td >Afdeling</td>
					<td >: </td>
					<td><?= $header['afdeling'] ?></td>
				</tr>
				<tr>
					<td >No SPAT</td>
					<td >: </td>
					<td><?= $header['no_spat'] ?></td>
				</tr>
				<tr>
					<td>Tanggal</td>
					<td>: </td>
					<td><?= tgl_indo($header['tanggal']) ?></td>
				</tr>
				<tr>
					<td>Keterangan</td>
					<td>: </td>
					<td><?= $header['keterangan'] ?></td>
				</tr>

				<td >Double Handling</td>
					<td>: </td>
					<td><?= $val['is_double_handling']==1?'Y':'N' ?></td>
				
				
			
			</table>
		</div>
		<!-- <p>Dibuka : <?= $dibuka['user_full_name'] ?></p> -->
		<p>Detail:</p>
		

		<table class="table-bg border">
			<thead>
				<tr>
					<th>No</th>
					<th >Blok</th>
					<th >JJG</th>
					<th >KG</th>
					<th >BJR</th>
				</tr>

			</thead>

			<tbody>
				<?php 
				
				$no= 0;

				?>
				<?php foreach ($detail as $key=>$val) { ?> 
				
				<?php 
				$no= $no+1;
				
				?>
			
				<tr>
					
					<td center width="2%"><?= $no ?></td>
					<td center width="12%"><?= $val['blok'] ?></td>
					<td center width="7%"><?= number_format($val['jum_janjang'], 2, '.', '') ?></td>
					<td center width="7%"><?= number_format($val['kg_kebun'], 2, '.', '') ?></td>
					<td center width="7%"><?= number_format($val['bjr_kebun'], 2, '.', '') ?></td>
					 
				</tr>
				<?php } ?>
				

			</tbody>
			
			<tfoot></tfoot>
		</table>
		

		<pre><?php //print_r($headeran) ?></pre>

	</body>
</html>


				
