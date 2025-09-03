<!DOCTYPEhtml>
<html>
	<head>
		
		<?php require '__laporan_style.php' ?>

	</head>
	<body>
		
		<?php require '__laporan_header.php' ?>

		<br>
		<div center>
			<h1>LAPORAN REKAP PENGIRIMAN</h1>
		</div>
		<br>
		
		<div class="d-flex flex-between">
			<table class="" style="width:30%">
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
					<th style="width:10%">No Rekap</th>
					<th style="width:10%">Tanggal</th>
					<th style="width:10%">Customer</th>
					<th style="width:10%">SPK</th>
					<th style="width:10%">Priode KT Dari</th>
					<th style="width:10%">Priode KT SD</th>
					<th style="width:10%">Item</th>
					<th style="width:10%">ADJ Berat Terima</th>
					<th style="width:10%">Total Berat Tagihan</th>
					<th style="width:10%">Harga Satuan</th>
					<th style="width:10%">Total Tagihan</th>
					<th style="width:10%">Sub Total</th>
					<th style="width:10%">No Surat Jalan</th>
					
				</tr>

			</thead>

			<tbody>
			<?php $no= 0 ?>
			<?php foreach ($po as $key=>$val) { ?> 
				
				<?php $no= $no+1 ?>
				<tr>
					<td><?= $no ?></td>
					<td center><?= $val['no_rekap'] ?></td>
					<td center><?= $val['tanggal'] ?></td>
					<td center><?= $val['customer'] ?></td>
					<td center><?= $val['spk'] ?></td>
					<td center><?= $val['periode_kt_dari'] ?></td>
					<td center><?= $val['periode_kt_sd'] ?></td>
					<td center><?= $val['item'] ?></td>
					<td center><?= $val['adj_berat_terima'] ?></td>
					<td center><?= $val['total_berat_tagihan'] ?></td>
					<td center><?= $val['harga_satuan'] ?></td>
					<td center><?= $val['total_tagihan'] ?></td>
					<td center><?= $val['sub_total'] ?></td>
					<td center><?= $val['no_surat'] ?></td>
				</tr>
				<?php } ?>
			
				
			</tbody>
			
			<tfoot></tfoot>
		</table>
		





		<pre><?php //print_r($headeran) ?></pre>

	</body>
</html>


				
