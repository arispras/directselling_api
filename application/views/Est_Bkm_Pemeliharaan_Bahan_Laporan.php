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
				font-size: 10px ;
			}
			
		</style>
	</head>
	<body>
		
		<?php require '__laporan_header.php' ?>

		<!-- <pre><?php print_r($po) ?></pre> -->

		<h3 class="title">LAPORAN PEMAKAIAN BAHAN</h3>
		<br>
		
		<div class="d-flex flex-between">
			<table class="no_border" style="width:30%">
				<tr>
					<td>Lokasi</td>
					<th>:</th>
					<td><?= $filter_lokasi ?></td>
				</tr>
				<!-- <tr>
					<td>Gudang</td>
					<th>:</th>
					<td><?= $filter_gudang ?></td>
				</tr> -->
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
					<th style="width:2%">No.</th>
					<th style="width:7%">No Transaksi</th>
					<th style="width:7%">Tanggal</th>
					<th style="width:10%">Kegiatan</th>
					<th style="width:4%">Blok</th>
					<th style="width:10%">Mandor</th>
					<!-- <th style="width:10%">Kerani</th> -->
					<th style="width:10%">Item</th>
					<th style="width:7%">Uom</th>
					<th style="width:7%">Qty</th>
					
					<th style="width:7%">Keterangan</th>
					<th style="width:3%">Status Posting</th>
				</tr>

			</thead>

			
			<tbody>
			<?php $no= 0 ?>
			<?php foreach ($po as $key=>$val) { ?> 
				
				<?php $no= $no+1 ?>
				<tr>
					<td><?= $no ?></td>
					<td left><?= $val['no_transaksi'] ?></td>
					<td center><?= tgl_indo_normal($val['tanggal']) ?></td>
					<td left><?= $val['kegiatan'] ?></td>
					<td left><?= $val['blok'] ?></td>
					<td left><?= $val['mandor'] ?></td>
					<!-- <td left><?= $val['kerani'] ?></td> -->
					<td left><?= $val['item'] ?></td>
					<td center><?= $val['uom'] ?></td>
					<td right><?= $val['qty'] ?></td>					
					<td left><?= $val['ket'] ?></td>
					<td center><?= ($val['is_posting']==1)?'Y':'N' ?></td>

				</tr>
				<?php } ?>
			
				
			</tbody>
			
			<tfoot></tfoot>
		</table>
		





		<pre><?php //print_r($headeran) ?></pre>

	</body>
</html>


				
