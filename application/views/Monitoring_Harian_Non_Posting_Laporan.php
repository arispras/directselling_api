<!DOCTYPEhtml>
<html>
	<head>
		
		<?php require '__laporan_style.php' ?>

	</head>
	<body>
		
		<?php require '__laporan_header.php' ?>

		<br>
		<div center>
			<h1>LAPORAN MONITORING HARIAN</h1>
		</div>
		<br>
		
		<div class="d-flex flex-between">
			<table class="" style="width:30%">
				<tr>
						<td>Lokasi</td>
						<th>:</th>
						<td><?= $dataLokasi['nama'] ?></td>
				</tr>
				<tr>
						<td>Transaksi</td>
						<th>:</th>
						<td><?= (!empty($header['transaksi'])) ? $header['transaksi'] : 'semua' ?></td>
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
					<th>Lokasi</th>
					<th colspan="1">Transaksi</th>
					<th>No Transaksi</th>
					<th>Tanggal</th>
					
				</tr>

			</thead>

			<tbody>
			<?php $no=0; ?>
			<?php $modul=[]; ?> 
			<?php foreach ($dataField as $key=>$val) { ?> 
				
				<?php foreach ($val as $key2=>$val2) { ?>
					<?php $modul[$key] += count($val2) ?>
					<?php } ?>
					
				<?php foreach ($val as $key2=>$val2) { ?>
					<?php foreach ($val2 as $key3=>$val3) { ?>
						<?php foreach ($val3 as $key4=>$val4) { ?>
						<?php $no= $no+1 ?>
						
						<?php if (!empty($val4['no_transaksi'])){ ?>
					<tr>
						<td><?= $no ?></td>
						<td><?= $val4['lokasi'] ?></td>
						<td left><?= $key2 ?></td>
						<td center><?= $val4['no_transaksi'] ?></td>
						<td><?= tgl_indo($val4['tanggal']) ?></td>
					</tr>
							<?php } ?>
						
						<?php } ?>
					<?php } ?>
				<?php } ?>


			<?php } ?>
			
				
			</tbody>
			
			<tfoot></tfoot>
		</table>
		





		<pre><?php //print_r($dataField) ?></pre>

	</body>
</html>


				
