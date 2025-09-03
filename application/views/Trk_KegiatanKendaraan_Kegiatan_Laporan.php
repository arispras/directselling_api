<!DOCTYPEhtml>
<html>
	<head>
	
	<?php 
			if ($format_laporan=='view') {
				require '_laporan_style_fix.php';
			}
			else{
				if ($format_laporan=='pdf') {
					require '__laporan_style_pdf.php';
					echo $html='
				<style>
				* body{
					font-size: 11px ;
				}
				
				.table-bg th,
				.table-bg td {
				border: 0.3px solid rgba(0, 0, 0, 0.4);
				padding: 5px 8px;
				}
				</style>';
				}	
			}
		?>
	</head>
	<body>
		
		<?php require '__laporan_header.php' ?>

		<!-- <pre><?php print_r($po) ?></pre> -->

		<h3 class="title">LAPORAN DETAIL KEGIATAN KENDARAAN/AB/MESIN </h3>
		<br>
		
		<div class="d-flex flex-between">
			<table class="no_border" style="width:30%">
				<tr>
					<td>Lokasi</td>
					<th>:</th>
					<td><?= $filter_lokasi ?></td>
				</tr>
				<tr>
					<td>Kendaraan</td>
					<th>:</th>
					<td><?= $filter_kendaraan ?></td>
				</tr>
				
				<tr>
					<td>Periode Tanggal</td>
					<th>:</th>
					<td><?= tgl_indo($filter_tgl_awal) .' s/d '. tgl_indo($filter_tgl_akhir) ?></td>
				</tr>
			
			</table>	
		</div>

		<div class="d-flex flex-between">
			<table class="" style="width:30%">
				
				
			</table>
		</div>
		<br>
		

		<table border=1 class="table-bg border">
			<thead>
				<tr>
					<th rowspan="2" style="width:2%">no</th>
					<!-- <th style="width:10%">Supplier</th> -->
					<th rowspan="2" style="width:10%" >No Transaksi</th>
					<th rowspan="2">Tanggal</th>
					<th rowspan="2">Kendaraan</th>
					<th rowspan="2">Status</th>
					<th rowspan="2">Kegiatan</th>
					<th rowspan="2" >Blok</th>
					<th colspan="3">KM HM</th>
					<th rowspan="2">Volume</th>
					
					<th rowspan="2">Keterangan</th>
				</tr>
				<tr>
					<th >Mulai</th>
					<th >Akhir</th>
					<th >Jumlah</th>
				</tr>
			</thead>

			
			<tbody>
			<?php 
			$no= 0;
			$jum_km=0;
			$jum_volume=0;
			?>
			<?php foreach ($po as $key=>$val) { ?> 
				
				<?php
				 $no= $no+1;
				 $jum_km= $jum_km+$val['km_hm_jumlah'];
				 $jum_volume= $jum_volume+$val['volume'];
				 ?>
				<tr>
					<td center><?= $no ?></td>
					<td left><?= $val['no_transaksi'] ?></td>
					<td center><?= tgl_indo_normal($val['tanggal']) ?></td>
					<td left><?= $val['nama_kendaraan'].' - '.$val['kode_kendaraan'] ?></td>
										
					 <?php
					 if($val['status_kendaraan']=='BREAKDOWN'){  ?>
						<td center style="color: red;"><?= $val["status_kendaraan"] ?></td>
						<?php } ?>
					<?php
					 if($val['status_kendaraan']!='BREAKDOWN'){  ?>
						<td center ><?= $val["status_kendaraan"] ?></td>
						<?php } ?>	
					
					<td left><?= $val['kegiatan'] ?></td>
					<td center><?= $val['blok'] ?></td>
					<td right><?= $val['km_hm_mulai'] ?></td>
					<td right><?= $val['km_hm_akhir'] ?></td>
					<td right><?= number_format($val['km_hm_jumlah']) ?></td>
					<td right><?= number_format($val['volume']) ?></td>
					
					<td left><?= $val['ket'] ?></td>
				</tr>
				<?php } ?>
				<tr>
					<td colspan=9>Jumlah</td>
					
					<td right><?= number_format($jum_km) ?></td>
					<td right><?= number_format($jum_volume) ?></td>
					
					<td center></td>
				</tr>
			
				
			</tbody>
			
			<tfoot></tfoot>
		</table>
		





		<pre><?php //print_r($headeran) ?></pre>

	</body>
</html>


				
