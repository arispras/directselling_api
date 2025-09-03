<!DOCTYPEhtml>
<html>
	<head>
		
		<?php require '__laporan_style.php' ?>
	</head>
	<body>
		
		<?php require '__laporan_header.php' ?>

		<div center>
			<h1 class="title">SPAT vs Timbangan PKS</h1>
			<p></p>
		</div>
		
		<div class="d-flex flex-between">
		<table class="" style="width:30%;line-height: 20px;">
				<!-- <tr>
					<td  style="width:14%">Lokasi</td>
					<td >: </td>
					<td><?= $header['lokasi'] ?></td>
				</tr> -->
				<tr>
					<td  style="width:14%">Afdeling</td>
					<td >: </td>
					<td><?= $header['nama_afdeling'] ?></td>
				</tr>
				<tr></tr>
				<tr>
					<td>Tanggal</td>
					<td>: </td>
					<td><?= $header['tanggal'] ?></td>
				</tr>
				<tr>
					<td>No SPB</td>
					<td>: </td>
					<td><?= $header['no_spat'] ?></td>
				</tr>
				<tr>
					<td>No Tiket</td>
					<td>: </td>
					<td><?= $header['no_tiket'] ?></td>
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
					<th >Jjg</th>
					<th >Brondolan</th>
					<th >Kg(kebun)</th>
					<th >Bjr(kebun)</th>
					<th >Kg(Pks)</th>
					<th >Bjr(Pks)</th>
				</tr>

			</thead>

			<tbody>
				<?php 
				
				$no= 0;
				$tot_jjg= 0;$tot_brondolan= 0;$tot_kg_kebun= 0;$tot_kg_pabrik= 0;

				?>
				<?php foreach ($detail as $key=>$val) { ?> 
				
				<?php 
				$no= $no+1;
				$tot_jjg=$tot_jjg+$val['jum_janjang'];
				$tot_brondolan=$tot_brondolan+$val['jum_brondolan'];
				$tot_kg_kebun=$tot_kg_kebun+$val['kg_kebun'];
				$tot_kg_pabrik=$tot_jjg+$val['kg_pabrik'];
				
				?>
			
				<tr>
					
					<td center width="2%"><?= $no ?></td>
					<td center width="12%"><?= $val['nama_blok'] ?></td>
					<td center width="7%"><?= number_format($val['jum_janjang'], 0) ?></td>
					<td center width="7%"><?= number_format($val['jum_brondolan'], 2) ?></td>
					<td center width="7%"><?= number_format($val['kg_kebun'], 2) ?></td>
					<td center width="7%"><?= number_format($val['bjr_kebun'], 2) ?></td>
					<td center width="7%"><?= number_format($val['kg_pabrik'], 2) ?></td>
					<td center width="7%"><?= number_format($val['bjr_pabrik'], 2) ?></td>
				</tr>
				<?php } ?>
				

			</tbody>
			
			<tfoot>
			<tr>
					
					<td center width="2%" colspan="2"><b>JUMLAH</b></td>
					<td center width="7%"><b><?= number_format($tot_jjg, 0) ?></b></td>
					<td center width="7%"><b><?= number_format($tot_brondolan, 2) ?></b></td>
					<td center width="7%"><b><?= number_format($tot_kg_kebun, 2) ?></b></td>
					<td center width="7%"></td>
					<td center width="7%"><b><?= number_format($tot_kg_pabrik, 2) ?></b></td>
					<td center width="7%"></td>
				</tr>
			</tfoot>
		</table>
		

		<pre><?php //print_r($headeran) ?></pre>

	</body>
</html>


				
