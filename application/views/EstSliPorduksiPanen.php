<!DOCTYPEhtml>
<html>
	<head>
		
		<?php require '__laporan_style.php' ?>
	</head>
	<body>
		
		<?php require '__laporan_header.php' ?>

		<div center>
			<h1 class="title">FORM PRODUKSI PANEN</h1>
			<p></p>
		</div>
		
		<div class="d-flex flex-between">
		<table class="" style="width:30%;line-height: 20px;">
				<tr>
					<td  style="width:14%">Lokasi</td>
					<td >: </td>
					<td><?= $header['afdeling'] ?></td>
				</tr>
				<tr></tr>
				<tr>
					<td>Tanggal</td>
					<td>: </td>
					<td><?= tgl_indo($header['tanggal']) ?></td>
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
					<th >HA</th>
					<th >HK</th>
					<th >JJG PNN</th>
					<th >KG PNN</th>
					<th>JJG Kirim</th>
					<th>KG PKS</th>
					<th>JJG AFKIR</th>
					<th>KG AFKIR</th>
					<th >JJG RESTAN</th>
					<th >KG RESTAN</th>
				</tr>

			</thead>

			<tbody>
				<?php 
				
				$no= 0;
				$ttl_ha=0;
				$ttl_hk=0;
				$ttl_jjg=0;
				$ttl_kg=0;
				$ttl_jjg_kirim=0;
				$ttl_kg_pks=0;
				$ttl_jjg_afkir=0;
				$ttl_kg_afkir=0;
				$ttl_jjg_res=0;
				$ttl_kg_res=0;

				?>
				<?php foreach ($detail as $key=>$val) { ?> 
				
				<?php 
				
				$no= $no+1;
				$ttl_ha=$ttl_ha+$val['jum_ha'];
				$ttl_hk=$ttl_hk+$val['jum_hk'];
				$ttl_jjg=$ttl_jjg+$val['jum_jjg'];
				$ttl_kg=$ttl_kg+$val['jum_kg'];
				$ttl_jjg_kirim=$ttl_jjg_kirim+$val['jum_jjg_kirim'];
				$ttl_kg_pks=$ttl_kg_pks+$val['jum_kg_pks'];
				$ttl_jjg_afkir=$ttl_jjg_afkir+$val['jjg_afkir'];
				$ttl_kg_afkir=$ttl_kg_afkir+$val['kg_afkir'];
				$ttl_jjg_res=$ttl_jjg_res+$val['jjg_restan'];
				$ttl_kg_res=$ttl_kg_res+$val['kg_restan'];
				
				?>
			
				<tr>
					
					<td center width="2%"><?= $no ?></td>
					<td center width="12%"><?= $val['blok'] ?></td>
					<td center width="7%"><?= number_format($val['jum_ha'], 2, '.', '') ?></td>
					<td center width="7%"><?= number_format($val['jum_hk'], 2, '.', '') ?></td>
					<td center width="7%"><?= number_format($val['jum_jjg'], 2, '.', '') ?></td>
					<td center width="7%"><?= number_format($val['jum_kg'], 2, '.', '') ?></td>
					<td center width="7%"><?= number_format($val['jum_jjg_kirim'], 2, '.', '') ?></td>
					<td center width="7%"><?= number_format($val['jum_kg_pks'], 2, '.', '') ?></td>
					<td center width="7%"><?= number_format($val['jjg_afkir'], 2, '.', '') ?></td>
					<td center width="7%"><?= number_format($val['kg_afkir'], 2, '.', '') ?></td>
					<td center width="7%"><?= number_format($val['jjg_restan'], 2, '.', '') ?></td>
					<td center width="7%"><?= number_format($val['kg_restan'], 2, '.', '') ?></td>
					 
				</tr>
				<?php } ?>
				<tr>
					<td colspan='2' center >JUMLAH</td>
					<td center width="7%"><?= number_format($ttl_ha, 2, '.', '') ?></td>
					<td center width="7%"><?= number_format($ttl_hk, 2, '.', '') ?></td>
					<td center width="7%"><?= number_format($ttl_jjg, 2, '.', '') ?></td>
					<td center width="7%"><?= number_format($ttl_kg, 2, '.', '') ?></td>
					<td center width="7%"><?= number_format($ttl_jjg_kirim, 2, '.', '') ?></td>
					<td center width="7%"><?= number_format($ttl_kg_pks, 2, '.', '') ?></td>
					<td center width="7%"><?= number_format($ttl_jjg_afkir, 2, '.', '') ?></td>
					<td center width="7%"><?= number_format($ttl_kg_afkir, 2, '.', '') ?></td>
					<td center width="7%"><?= number_format($ttl_jjg_res, 2, '.', '') ?></td>
					<td center width="7%"><?= number_format($ttl_kg_res, 2, '.', '') ?></td>
				</tr>

			</tbody>
			
			<tfoot></tfoot>
		</table>
		





		<pre><?php //print_r($headeran) ?></pre>

	</body>
</html>


				
