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
					<th colspan="2">Transaksi</th>
					<th>Jumlah</th>
					<th>Tanggal Terakhir</th>
					
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
						<?php $no= $no+1 ?>

					<?php if ($val3['jumlah'] != 0) { ?>
					<tr style="background-color:#ebfcee;">
					<?php }else { ?>
					<tr>
					<?php } ?>

						<td><?= $no ?></td>
						<td><?= $val3['lokasi'] ?></td>

						<?php if ($prev_modul!==$key) { ?>
						<td rowspan="<?= $modul[$key] ?>"><?= $key ?></td>
						<?php } ?>

						<td left><?= $key2 ?></td>
						<td center><?= number_format($val3['jumlah']) ?></td>
						<td><?= tgl_indo($val3['tanggal_terakhir']) ?></td>
					</tr>
					<?php $prev_modul = $key;?>
					<?php } ?>
				<?php } ?>


			<?php } ?>
			
				
			</tbody>
			
			<tfoot></tfoot>
		</table>
		





		<pre><?php //print_r($headeran) ?></pre>

	</body>
</html>


				
