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
				}	
			}
		?>
		<style>
			* body{
				font-size: 11px ;
			}
			
		</style>
	</head>
	<body>
		
		<?php require '__laporan_header.php' ?>

		
		<h3 class="title">LAPORAN RINCIAN BAPP KEBUN</h3>
		<br>
		
		<div>
			<table class="no_border" style="width:30%">
				<tr>
					<td>Lokasi</td>
					<th>:</th>
					<td><?= $filter_lokasi ?></td>
				</tr>
				<tr>
					<td>Kontraktor</td>
					<th>:</th>
					<td><?= $filter_supplier ?></td>
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
		

		<table class="table-bg border">
			<thead>
				<tr>
					<th style="width:2%">No</th>
					
					<th style="width:12%">No BA SPK</th>
					<th style="width:6%" >Tanggal</th>
					<th style="width:12%">Kontraktor</th>
					<th style="width:12%" >No SPK </th>
					<th style="width:5%">Blok</th>
					<th  style="width:10%">Kegiatan</th>
					<th  style="width:10%">Satuan</th>
					<th style="width:5%">HK</th>
					<th style="width:5%">Volume</th>
					<th style="width:7%">Harga</th>
					<th style="width:7%">Jumlah</th>
					<th  style="width:10%">Keterangan</th>
				</tr>

			</thead>

			
			<tbody>
			<?php $no= 0 ?>
			<?php foreach ($ba as $key=>$val) { ?> 
				
				<?php $no= $no+1 ?>
				<tr>
					<td center><?= $no ?></td>
					<td left><?= $val['no_transaksi'] ?></td>
					<td center><?= tgl_indo_normal($val['tanggal'])  ?></td>
					<td left><?= $val['supplier'] ?></td>
					<td left><?= $val['no_spk'] ?></td>
					<td center><?= $val['blok'] ?></td>
					<td left><?= $val['kegiatan'] ?></td>
					<td center><?= $val['satuan_volume'] ?></td>
					<td center><?= number_format($val['hk'])?></td>
					<td right><?= number_format($val['volume'])?></td>
					<td right><?= number_format($val['harga'])?></td>
					<td right><?= number_format($val['total'])?></td>
					<td left><?= $val['keterangan'] ?></td>
				</tr>
				<?php } ?>
			
				
			</tbody>
			
			<tfoot></tfoot>
		</table>
		





		<pre><?php //print_r($headeran) ?></pre>

	</body>
</html>


				
