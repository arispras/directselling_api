<!DOCTYPEhtml>
<html>
	<head>
	
		<?php require '__laporan_style.php' ?>
	</head>
	<body>
		
		<?php require '__laporan_header.php' ?>

		<!-- <pre><?php print_r($po) ?></pre> -->

		<h1 class="title">LAPORAN MATERIAL WORKSHOP</h1>
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

		<br><br>

		<table class="table-bg border">
			<thead>
				<tr>
					<th style="width:2%">no</th>
					<th style="width:10%" >No Transaksi</th>
					<th>Kendaraan</th>
					<th>Item</th>
					<th>Tanggal</th>
					<th>Qty</th>
					<th>Keterangan</th>
				</tr>
			</thead>

			
			<tbody>
			<?php $no= 0 ?>
			<?php foreach ($po as $key=>$val) { ?> 
				
				<?php $no= $no+1 ?>
				<tr>
					<td><?= $no ?></td>
					<td left><?= $val['no_transaksi'] ?></td>
					<td left><?= '(' . $val['kode_kendaraan'] . ') ' . $val['nama_kendaraan'] ?></td>
					<td left><?= $val['material'] ?></td>
					<td center><?= tgl_indo($val['tanggal']) ?></td>
					<td center><?= $val['qty'] ?></td>
					<td center><?= $val['ket'] ?></td>
				</tr>
				<?php } ?>
			
				
			</tbody>
			
			<tfoot></tfoot>
		</table>

	</body>
</html>


				
