<!DOCTYPEhtml>
	<html>

	<head>
		

		<?php require '__laporan_style.php' ?>
	</head>

	<body>

		<?php require '__laporan_header.php' ?>

		<h1 class="title">LAPORAN OUTSTANDING FAKTUR PAJAK</h1>
		<br>


		<div class="d-flex flex-between">
			<table class="" style="width:30%">


			</table>
		</div>
		<br>


		<table class="table-bg border">
			<thead>
				<tr>
					<th style="width:2%">No</th>
					<th style="width:10%">Supplier</th>
					<th style="width:10%">No Invoice</th>
					<th style="width:10%">No Invoice Supplier</th>
					<th>Tanggal Tempo</th>
					<th>Lama Hari</th>
					<th style="width:10%">Nilai</th>
					<th style="width:10%">Dibayar</th>
					<th style="width:7%">Sisa</th>
					<th style="width:7%">No Faktur Pajak</th>
				</tr>

			</thead>

			<tbody>
				<?php $no = 0 ?>
				<?php foreach ($ap as $key => $val) { ?>

					<?php $no = $no + 1 ?>
					<?php
					$datetime1 = new DateTime($val['tanggal_tempo']);
					$datetime2 = new DateTime($filter_tgl_tempo);
					$interval = $datetime1->diff($datetime2);
					// echo $interval->format('%R%a hari')
					?>
					<tr>
						<td><?= $no ?></td>
						<td center><?= $val['nama_supplier'] ?></td>
						<td center><?= $val['no_invoice'] ?></td>
						<td center><?= $val['no_invoice_supplier'] ?></td>
						<td center><?= $val['tanggal_tempo'] ?></td>
						<td center><?= $interval->format('%a hari') ?></td>
						<td center> <?= number_format($val['nilai_invoice']) ?></td>
						<td center> <?= number_format($val['dibayar']) ?></td>
						<td center> <?= number_format($val['sisa']) ?></td>
						<td center><?= $val['no_faktur_pajak'] ?></td>

					</tr>
				<?php } ?>


			</tbody>

			<tfoot></tfoot>
		</table>






		<pre><?php //print_r($headeran) 
				?></pre>

	</body>

	</html>
