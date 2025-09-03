<!DOCTYPEhtml>
<html>
	<head>
	
		<?php require '__laporan_style.php' ?>
	</head>
	<body>
		
		<?php require '__laporan_header.php' ?>

		<!-- <pre><?php print_r($po) ?></pre> -->

		<h1 class="title">LIST BKM PANEN</h1>
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
					<th style="width:7%">Lokasi</th>
					<th style="width:7%" >Afdeling</th>
					<th style="width:7%" >No Transaksi</th>
					<th style="width:7%">Tanggal</th>
					<th style="width:10%" >Mandor</th>
					<th style="width:10%">Kerani</th>
					<th style="width:7%">Posting</th>
				</tr>

			</thead>

			
			<tbody>
			<?php $no= 0 ?>
			<?php foreach ($bkm as $key=>$val) { ?> 
				
				<?php $no= $no+1 ?>
				<tr>
					<td><?= $no ?></td>
					<td center><?= $val['lokasi'] ?></td>
					<td center><?= $val['rayon_afdeling'] ?></td>
					<td center><?= $val['no_transaksi'] ?></td>
					<td center><?= $val['tanggal'] ?></td>
					<td center><?= $val['mandor'] ?></td>
					<td center><?= $val['kerani'] ?></td>
					<td center><?= $val['is_posting']==1?'Y':'N' ?></td>

				</tr>
				<?php } ?>
			
				
			</tbody>
			
			<tfoot></tfoot>
		</table>
		





		<pre><?php //print_r($headeran) ?></pre>

	</body>
</html>


				
