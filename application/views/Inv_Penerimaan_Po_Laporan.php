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

		<h3 class="title">LAPORAN PENERIMAAN PO</h3>
		<br>
		
		<div class="d-flex flex-between">
			<table class="no_border" style="width:30%">
				<tr>
						<td>Lokasi</td>
						<th>:</th>
						<td><?= $filter_lokasi ?></td>
				</tr>
				<tr>
						<td>supplier</td>
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
					<th style="width:2%">no</th>
					<th style="width:10%">Supplier</th>
					<th style="width:10%" >No Transaksi</th>
					<th >Tanggal</th>
					<th >Kode Barang</th>
					<th style="width:19%" >Nama Barang</th>
					<!-- <th style="width:5%" >Mata Uang</th> -->
					<th style="width:5%" >Qty</th>
					<th >Satuan</th>
					<th style="width:10%" >No PO</th>
					<th style="width:10%" >Tanggal PO</th>
					<th style="width:10%" >No Surat Jalan</th>
					<!-- <th style="width:7%" >Tanggal Penerimaan</th> -->
				</tr>

			</thead>

			
			<tbody>
			<?php $no= 0 ?>
			<?php foreach ($po as $key=>$val) { ?> 
				
				<?php $no= $no+1 ?>
				<tr>
					<td><?= $no ?></td>
					<td left><?= $val['nama_supplier'] ?></td>
					<td left><?= $val['no_transaksi'] ?></td>
					<td center><?= tgl_indo_normal($val['tanggal']) ?></td>
					<td center><?= $val['kode'] ?></td>
					<td left><?= $val['item'] ?></td>
					<!-- <td center><?= $val['mata_uang'] ?></td> -->
					<td center><?= $val['qty'] ?></td>
					<td center><?= $val['uom'] ?></td>
					<td left><?= $val['no_po'] ?></td>
					<td center><?= tgl_indo_normal($val['tanggal_po']) ?></td>
					<td left><?= $val['no_surat_jalan_supplier'] ?></td>
					<!-- <td>23/01/2022</td> -->
				</tr>
				<?php } ?>
			
				
			</tbody>
			
			<tfoot></tfoot>
		</table>
		





		<pre><?php //print_r($headeran) ?></pre>

	</body>
</html>


				
