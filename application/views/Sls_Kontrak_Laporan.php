<!DOCTYPEhtml>
<html>
	<head>
		
		<?php require '__laporan_style.php' ?>

	</head>
	<body>
		
		<?php require '__laporan_header.php' ?>

		<br>
		<div center>
			<h1>LAPORAN SALES KONTRAK</h1>
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
					<th style="width:2%">No.</th>
					<th style="width:10%">No Spk</th>
					<th style="width:10%">No Ref</th>
					<th style="width:10%">Tanggal</th>
					<th style="width:10%">Lokasi</th>
					<th style="width:10%">Customer</th>
					<th style="width:10%">Periode Kirim Awal</th>
					<th style="width:10%">Periode Kirim Akhir</th>
					<th style="width:10%">Alamat Pengiriman</th>
					<th style="width:10%">Alamat Penagihan</th>
					<th style="width:10%">Pic</th>
					<th style="width:10%">Produk</th>
					<th style="width:10%">Jumlah</th>
					<th style="width:10%">Harga Satuan</th>
					<th style="width:10%">Sub Total</th>
					<th style="width:10%">PPN</th>
					<th style="width:10%">PPH</th>
					<th style="width:10%">TOTAL</th>
					
					<th style="width:10%">FFA</th>
					<th style="width:10%">MI</th>
					<th style="width:10%">Impurities</th>
					<th style="width:10%">Dobi</th>
					<th style="width:10%">Moisture</th>
					<th style="width:10%">Grading</th>
					<th style="width:10%">Toleransi</th>
					<th style="width:10%">Keterangan</th>
					<th style="width:10%">No Rekap Angkut</th>
					<th style="width:10%">Qty Rekap Angkut</th>
					<th style="width:10%">Sisa</th>
					
				</tr>

			</thead>

			<tbody>
			<?php $no= 0 ?>
			<?php foreach ($po as $key=>$val) { ?> 
				
				<?php $no= $no+1 ?>
				<tr>
					<td><?= $no ?></td>
					<td left><?= $val['no_spk'] ?></td>
					<td left><?= $val['no_ref'] ?></td>
					<td center><?= $val['tanggal'] ?></td>
					<td left><?= $val['lokasi'] ?></td>
					<td left><?= $val['customer'] ?></td>
					<td center><?= $val['priode_kirim_awal'] ?></td>
					<td center><?= $val['priode_kirim_akhir'] ?></td>
					<td left><?= $val['alamat_pengiriman'] ?></td>
					<td left><?= $val['alamat_penagihan'] ?></td>
					<td left><?= $val['pic'] ?></td>
					<td left><?= $val['produk'] ?></td>
					<td right><?= number_format($val['jumlah'],2) ?></td>
					<td right><?= number_format($val['harga_satuan'],2) ?></td>
					<td right><?= number_format($val['sub_total'],2) ?></td>
					<td right><?= number_format($val['ppn'],2) ?></td>
					<td right><?= number_format($val['pph'],2) ?></td>
					<td right><?= number_format($val['total'],2) ?></td>
					
					<td right><?= number_format($val['ffa'],2)?></td>
					<td right><?= number_format($val['mi'],2) ?></td>
					<td right><?= number_format($val['impurities'],2) ?></td>
					<td right><?= number_format($val['dobi'],2) ?></td>
					<td right><?= number_format($val['moisture'],2) ?></td>
					<td right><?= number_format($val['grading'],2) ?></td>
					<td right><?= number_format($val['toleransi'],2) ?></td>
					<td center><?= $val['keterangan'] ?></td>
					<td center><?= $val['ket_rekap_angkut'] ?></td>
					<td center><?= number_format($val['jum_qty_terima'],2) ?></td>
					<td center><?= number_format($val['jumlah']-$val['jum_qty_terima'],2) ?></td>
				</tr>
				<?php } ?>
			
				
			</tbody>
			
			<tfoot></tfoot>
		</table>
		





		<pre><?php //print_r($headeran) ?></pre>

	</body>
</html>


				
