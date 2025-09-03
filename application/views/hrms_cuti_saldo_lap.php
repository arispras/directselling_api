<!DOCTYPEhtml>
<html>
	<head>
	
		<?php require '__laporan_style.php' ?>
	</head>
	<body>
		
		<?php require '__laporan_header.php' ?>

		<!-- <pre><?php print_r($po) ?></pre> -->

		<h1 class="title">LAPORAN SALDO CUTI KARYAWAN</h1>
		<br>
		
		<div class="d-flex flex-between">
			<table class="" style="width:30%">
				<tr>
					<td>Lokasi</td>
					<th>:</th>
					<td><?= $filter_lokasi ?></td>
				</tr>
				<tr>
					
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
					<th style="width:1%">no</th>
					<th style="width:10%">Lokasi</th>
					<th style="width:10%">NIP</th>
					<th style="width:10%">Nama Karyawan</th>
					<th style="width:10%">Jumlah</th>
				</tr>

			</thead>

			
			<tbody>
			<?php $no= 0 ?>
			<?php foreach ($bkm as $key=>$val) { ?> 
				
				<?php $no= $no+1 ?>
				<tr>
					<td align="center"><?= $no ?></td>
					<td left><?= $val['lokasi'] ?></td>
					<td left><?= $val['nip'] ?></td>
					<td left><?= $val['nama'] ?></td>
					<td center><?= $val['saldo'] ?></td>

				</tr>
				<?php } ?>
			
				
			</tbody>
			
			<tfoot></tfoot>
		</table>
		





		<pre><?php //print_r($headeran) ?></pre>

	</body>
</html>


				
