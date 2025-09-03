<!DOCTYPEhtml>
<html>
	<head>
		
		<?php require '__laporan_style.php' ?>

	</head>
	<body>
		
		<?php require '__laporan_header.php' ?>

		<br>
		<div center>
			<h1>LAPORAN MONITORING INSTRUKSI PENGIRIMAN</h1>
		</div>
		<br>
		
		<div class="d-flex flex-between">
			<table class="" style="width:30%">
				<tr>
						<td>CUSTOMER</td>
						<th>:</th>
						<td><?=$filter_customer ?></td>
				</tr>
				<tr>
					<td>Mulai Tanggal</td>
					<th>:</th>
					<td><?=  tgl_indo($filter_tgl_awal) ?></td>
				</tr>
				<tr>
					<td>S/d Tanggal</td>
					<th>:</th>
					<td><?=  tgl_indo($filter_tgl_akhir) ?></td>
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
					<th style="width:10%">Customer</th>
					<th style="width:10%">No Spk</th>
					<th style="width:10%">No IP</th>
					<th style="width:10%">Tanggal IP</th>
					
					<th style="width:10%">Periode Kirim Awal</th>
					<th style="width:10%">Periode Kirim Akhir</th>
					<th style="width:5%">Produk</th>
					<th style="width:7%">Jumlah DO</th>
					<th style="width:7%">Jumlah H.Ini</th>
					<th style="width:7%">Jumlah S/d H.Ini</th>
					<th style="width:7%">Sisa</th>
					<th style="width:5%">Status</th>
					
				</tr>

			</thead>

			<tbody>
			<?php $no= 0 ?>
			<?php foreach ($ip as $key=>$val) { ?> 
				
				<?php $no= $no+1 ?>
				<tr>
					<td><?= $no ?></td>
					<td left><?= $val['nama_customer'] ?></td>
					<td left><?= $val['no_spk'] ?></td>
					<td left><?= $val['no_transaksi'] ?></td>
					<td center><?= tgl_indo_normal($val['tanggal']) ?></td>
					
					<td center><?= tgl_indo_normal( $val['periode_kirim_awal']) ?></td>
					<td center><?= tgl_indo_normal($val['periode_kirim_akhir']) ?></td>
					<td center><?= $val['produk'] ?></td>
					<td right><?=  number_format( $val['jumlah'],2) ?></td>
					<td right><?=  number_format( $val['jum_hari_ini'],2) ?></td>
					<td right><?=  number_format( $val['jum_sd_hari_ini'],2) ?></td>
					<td right><?=  number_format( $val['sisa'],2) ?></td>
					<td center><?= $val['status'] ?></td>
				</tr>
				<?php } ?>				
			</tbody>
			
			<tfoot></tfoot>
		</table>
		





		<pre><?php //print_r($headeran) ?></pre>

	</body>
</html>


				
