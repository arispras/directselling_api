<!DOCTYPEhtml>
<html>
	<head>
		
		<?php require '__laporan_style.php' ?>
	</head>
	<body>
		
		<?php require '__laporan_header.php' ?>

		<div center>
			<h1 class="title">FORM SENSUS PANEN</h1>
			<p></p>
		</div>
		
		<div class="d-flex flex-between">
		<table class="" style="width:30%;line-height: 20px;">
				<tr>
					<td  style="width:14%">Lokasi</td>
					<td >: </td>
					<td><?= $header['lokasi'] ?></td>
				</tr>
				<tr>
					<td  style="width:14%">Afdeling</td>
					<td >: </td>
					<td><?= $header['afdeling'] ?></td>
				</tr>
				<tr></tr>
				<tr>
					<td>Tahun</td>
					<td>: </td>
					<td><?= $header['tahun'] ?></td>
				</tr>
				<tr>
					<td>Bulan</td>
					<td>: </td>
					<td><?= $header['bulan'] ?></td>
				</tr>
				<tr>
					<td>Keterangan</td>
					<td>: </td>
					<td><?= $header['ket'] ?></td>
				</tr>
				
				
			
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
					<td center width="7%"><?= number_format($val['jjg'], 2, '.', '') ?></td>
					<td center width="7%"><?= number_format($val['kg'], 2, '.', '') ?></td>
					<td center width="7%"><?= number_format($val['bjr'], 2, '.', '') ?></td>
					 
				</tr>
				<?php } ?>
				

			</tbody>
			
			<tfoot></tfoot>
		</table>
		

		<pre><?php //print_r($headeran) ?></pre>

	</body>
</html>


				
