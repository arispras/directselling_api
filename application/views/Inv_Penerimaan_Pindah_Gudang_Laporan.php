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
	</head>
	<body>
		
		<?php require '__laporan_header.php' ?>

		<!-- <pre><?php print_r($po) ?></pre> -->

		<h3 class="title">LAPORAN PENERIMAAN PINDAH GUDANG</h3>
		<br>
		
		<div class="d-flex flex-between">
			<table class="no_border" style="width:30%">
				<tr>
					<td>Lokasi</td>
					<th>:</th>
					<td><?= $filter_lokasi ?></td>
				</tr>
				<!-- <tr>
					<td>supplier</td>
					<th>:</th>
					<td><?= $filter_supplier ?></td>
				</tr> -->
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
					<th style="width:2%">no</th>
					<th style="width:10%" >No Transaksi</th>
					<th style="width:15%" >Dari Gudang</th>
					<th style="width:15%" >Ke Gudang</th>
					<th >Tanggal</th>
					<th >Kode Barang</th>
					<th style="width:19%" >Nama Barang</th>
					<th style="width:5%" >Qty</th>
					<th >Satuan</th>
				</tr>

			</thead>

			
			<tbody>
			<?php $no= 0 ?>
			<?php foreach ($po as $key=>$val) { ?> 
				
				<?php $no= $no+1 ?>
				<tr>
					<td center><?= $no ?></td>
					<td left><?= $val['no_transaksi'] ?></td>
					<td left><?= $val['dari_gudang'] ?></td>
					<td left><?= $val['ke_gudang'] ?></td>
					<td center><?= tgl_indo_normal($val['tanggal']) ?></td>
					<td center><?= $val['kode'] ?></td>
					<td left><?= $val['item'] ?></td>
					<td right><?= $val['qty'] ?></td>
					<td center><?= $val['uom'] ?></td>
				</tr>
				<?php } ?>
			
				
			</tbody>
			
			<tfoot></tfoot>
		</table>
		


	</body>
</html>


				
