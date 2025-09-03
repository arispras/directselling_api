<!DOCTYPEhtml>
<html>
	<head>
	
		<?php require '__laporan_style.php' ?>
	</head>
	<body>
		
		<?php require '__laporan_header.php' ?>

		<!-- <pre><?php print_r($po) ?></pre> -->

		<h1 class="title">LAPORAN RETUR PEMAKAIAN BARANG</h1>
		<br>
		
		<div class="d-flex flex-between">
			<table class="" style="width:60%">
				<tr>
					<td>Lokasi</td>
					<th>:</th>
					<td><?= $filter_lokasi ?></td>
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
					<th style="width:10%" >No Transaksi</th>
					<th style="width:10%" >No Transaksi Pemakaian</th>
					<th >Tanggal</th>
					<th >Lokasi Afdeling/Traksi</th>
					<th >Gudang</th>
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
					<td><?= $no ?></td>
					<td center><?= $val['no_transaksi'] ?></td>
					<td center><?= $val['no_transaksi_pemakaian'] ?></td>
					<td center><?= $val['tanggal'] ?></td>
					<?php if (!is_null($val['nama_afdeling'])) { ?>
					<td center><?= $val['nama_afdeling'] ?></td>
					<?php } ?>
					<?php if (!is_null($val['nama_traksi'])) { ?>
					<td center><?= $val['nama_traksi'] ?></td>
					<?php } ?>
					<td center><?= $val['gudang'] ?></td>
					<td center><?= $val['kode'] ?></td>
					<td center><?= $val['item'] ?></td>
					<td center><?= $val['qty'] ?></td>
					<td center><?= $val['uom'] ?></td>
				</tr>
				<?php } ?>
			
				
			</tbody>
			
			<tfoot></tfoot>
		</table>
		





		<pre><?php //print_r($headeran) ?></pre>

	</body>
</html>


				
