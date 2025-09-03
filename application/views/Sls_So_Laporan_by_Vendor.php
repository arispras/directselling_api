<!DOCTYPEhtml>
<html>
	<head>
	<title>A.09 Sales Order by Customer</title>
	<link rel="icon" type="image/png" href="<?= base_url('logo_antech.png') ?>" />
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
	<?php 
			if ($format_laporan=='view') {
				require '__laporan_header.php';
			}
			elseif ($format_laporan=='pdf') {
				require '__laporan_header.php';
			}
			else{	echo'-';
			}
		?>

		
		<h3 class="title">A.09 Sales Order by Customer</h3>
		<br>
		
		<div class="d-flex flex-between">
			<table class="no_border"  style="width:30%">
				<tr>
					<td style="width:25%">Periode Tanggal</td>
					<th>:</th>
					<td><?= tgl_indo($filter_tgl_awal) .' s/d '. tgl_indo($filter_tgl_akhir) ?></td>
				</tr>
			
			</table>	
		</div>
		<br>

		<!-- table 1 -->
		<table class="table-bg border">				
				<thead>
					<tr>
						<th>No</th>
						<th >No Order</th>
						<th>Request No</th>
						<th>Date</th>
						<th>Lokasi</th>
						<th>Kode</th>
						<th>Nama</th>
						<th>Qty</th>
						<th>Total Value(RP)</th>
					</tr>

				</thead>


		
			<tbody>
				<?php 
				$ttl=0;
				$ttl_qty=0;
				?>
				<?php foreach ($so as $key=>$val) { ?> 

				
				<tr>
					<td bold colspan=9><?= $val['nama_customer'] ?></td>
				</tr>

				<?php $dt=$val['detail'] ;?>

				<?php 
				$no= 0 ;
				$jml_ttl=0;
				$jml_qty=0;
				?>

				<?php foreach ($dt as $key=>$res) { ?> 
				
				<?php 
				$no++;
				$jml_ttl=$jml_ttl+$res['total_nilai_penjualan'];
				$jml_qty=$jml_qty+$res['qty_order'];
				?>
				<tr>
					<td center width="2%"><?= $no ?></td>
					<td left width="10%"><?= $res['no_so'] ?></td>
					<td left width="8%"><?= $res['no_pp'] ?></td>
					<td center width="5%"><?= tgl_indo_normal($res['tanggal'])?></td>
					<td center width="13%"><?= $res['gudang'] ?></td>
					<!-- <td center width="17%"><?= $res['nama_customer'] ?></td> -->
					<td center width="5%"><?= $res['kode_item'] ?></td>
					<td left width="17%"><?= $res['nama_item'] ?></td>
					<td right width="5%"><?= number_format($res['qty_order']) ?></td>
					<td right width="8%"><?= number_format($res['total_nilai_penjualan'])?></td>
					
				</tr>
				<?php } ?>
				<tr>
					<td colspan='7' right >TOTAL <?= $res['nama_customer'] ?></td>
					<td right><?= number_format($jml_qty) ?></td>
					<td right><?= number_format($jml_ttl) ?></td>
				</tr>
				<?php 
				$ttl=$ttl+$jml_ttl;
				$ttl_qty=$ttl_qty+$jml_qty;
				?>
				<?php } ?>
				
				<tr>
					<td colspan='7' right >TOTAL </td>
					<td right><?= number_format($ttl_qty) ?></td>
					<td right><?= number_format($ttl) ?></td>
				</tr>
			</tbody>
		</table>
		<!-- tutup table -->
			<br>
					<br><hr>
		
			
		<pre><?php //print_r($headeran) ?></pre>

	</body>
</html>


				
