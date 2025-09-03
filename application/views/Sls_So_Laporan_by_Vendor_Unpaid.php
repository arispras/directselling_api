<!DOCTYPEhtml>
<html>
	<head>
	<title>A.04 Sales Order Unpaid</title>
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

		
		<h3 class="title">A.04 Sales Order Unpaid</h3>
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
						<th>Date</th>
						<th>Desc</th>
						<th>Total Value(RP)</th>
						<th>Outstanding</th>
						<th>Age</th>
					</tr>

				</thead>


		
			<tbody>
				<?php 
				$ttl=0;
				$ttl_qty=0;
				?>
				<?php foreach ($so as $key=>$val) { ?> 

				
				<tr>
					<td bold colspan=7><?= $val['nama_customer'] ?></td>
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
				$jml_ttl=$jml_ttl+$res['grand_total'];
				
				$tgl1= strtotime($res['tanggal']);
				$tgl2= strtotime($filter_tgl_akhir);
				$jarak = $tgl2 - $tgl1;
				$hari = $jarak / 60 / 60 / 24;
				?>
				<tr>
					<td center width="2%"><?= $no ?></td>
					<td left width="10%"><?= $res['no_so'] ?></td>

					<td center width="5%"><?= tgl_indo_normal($res['tanggal'])?></td>

					<td left width="17%"><?= $res['catatan'] ?></td>
					<td right width="8%"><?= number_format($res['grand_total'])?></td>
					<td right width="8%">outstanding</td>
					<td right width="8%"><?php echo $hari ;?></td>
					
				</tr>
				<?php } ?>
				<tr>
					<td colspan='4' right >TOTAL <?= $res['nama_customer'] ?></td>
					<td right><?= number_format($jml_ttl) ?></td>
					<td right>outstanding</td>
					<td right>&nbsp;</td>
				</tr>
				<?php 
				$ttl=$ttl+$jml_ttl;
				$ttl_qty=$ttl_qty+$jml_qty;
				?>
				<?php } ?>
				
				<tr>
					<td colspan='4' right >TOTAL </td>
					<td right><?= number_format($ttl) ?></td>
					<td right>outstanding</td>
					<td right>&nbsp;</td>
				</tr>
			</tbody>
		</table>
		<!-- tutup table -->
			<br>
					<br><hr>
		
			
		<pre><?php //print_r($headeran) ?></pre>

	</body>
</html>


				
