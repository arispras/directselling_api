<!DOCTYPEhtml>
<html>
	<head>
		
		<?php require '__laporan_style.php' ?>
	</head>
	<body>
		
		<?php require '__laporan_header.php' ?>

		<!-- <pre><?php print_r($po) ?></pre> -->

		<h1 class="title">LAPORAN PERMINTAAN BARANG</h1>
		<br>
		
		<div class="d-flex flex-between">
			<table class="" style="width:30%">
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
				<tr>
					<td>Gudang</td>
					<th>:</th>
					<td><?= $filter_gudang ?></td>
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
					<!-- <th style="width:10%">Supplier</th> -->
					<th style="width:10%" >No Transaksi</th>
					<th >Tanggal</th>
					<th >Kode Barang</th>
					<th style="width:19%" >Nama Barang</th>
					<!-- <th style="width:5%" >Mata Uang</th> -->
					<th style="width:5%" >Qty</th>
					<th >Satuan</th>
					<!-- <th style="width:10%" >No PO</th>
					<th style="width:10%" >Tanggal PO</th>
					<th style="width:10%" >No Surat Jalan</th> -->
					<!-- <th style="width:7%" >Tanggal Penerimaan</th> -->
				</tr>

			</thead>

			
			<tbody>
			<?php $no= 0 ?>
			<?php foreach ($po as $key=>$val) { ?> 
				
				<?php $no= $no+1 ?>
				<tr>
					<td><?= $no ?></td>
					<!-- <td center><?= $val['nama_supplier'] ?></td> -->
					<td center><?= $val['no_transaksi'] ?></td>
					<td center><?= $val['tanggal'] ?></td>
					<td center><?= $val['kode'] ?></td>
					<td center><?= $val['item'] ?></td>
					<!-- <td center><?= $val['mata_uang'] ?></td> -->
					<td center><?= $val['qty'] ?></td>
					<td center><?= $val['uom'] ?></td>
					<!-- <td center><?= $val['no_po'] ?></td>
					<td center><?= $val['tanggal_po'] ?></td>
					<td center><?= $val['no_surat_jalan_supplier'] ?></td> -->
					<!-- <td>23/01/2022</td> -->
				</tr>
				<?php } ?>
			
				
			</tbody>
			
			<tfoot></tfoot>
		</table>
		





		<pre><?php //print_r($headeran) ?></pre>

	</body>
</html>


				
