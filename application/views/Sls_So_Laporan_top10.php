<!DOCTYPEhtml>
	<html>

	<head>
	<title>C.01 Top 10 Customers in Last 6 Months</title>
	<link rel="icon" type="image/png" href="<?= base_url('logo_antech.png') ?>" />

		<?php
		if ($format_laporan == 'view') {
			require '_laporan_style_fix.php';
		} else {
			if ($format_laporan == 'pdf') {
				require '__laporan_style_pdf.php';
				echo $html = '
			<style>
			* body{
				font-size: 10px ;
			}
			
			.table-bg th,
			.table-bg td {
			border: 0.3px solid rgba(0, 0, 0, 0.4);
			padding: 5px 8px;
			}
			</style>';
			}
		}
		?>
		<style>
			thead {
				display: table-row-group;
			}

			pagebreak: {
				avoid: ['tr', 'td']
			}
		</style>
	</head>


	<body>

		<?php require '__laporan_header.php' ?>

		<br>
		<div center>
			<h3 class='title'>C.01 Top 10 Customers in Last 6 Months</h1>
		</div>
		<br>

		<div class="d-flex flex-between">
			<table class="no_border" style="width:30%">
				<!-- <tr>
					<td>Lokasi</td>
					<th>:</th>
					<td><?= $filter_lokasi ?></td>
				</tr>
				<tr>
					<td>supplier</td>
					<th>:</th>
					<td><?= $filter_customer ?></td>
				</tr>
				<tr>
					<td>Periode Tanggal</td>
					<th>:</th>
					<td><?= tgl_indo($filter_tgl_awal) . ' s/d ' . tgl_indo($filter_tgl_akhir) ?></td>
				</tr> -->

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
					<?php
					echo "<th> No </th>";
					foreach (($bulan_tahun) as $key => $yyyymm) {
						echo "<th>" . $bulan_tahun[$key] . " </th>";
					}
					?>
				</tr>


			</thead>

			<tbody>
				<?php
				for ($i = 0; $i < 10; $i++) {
					echo "<tr>";
					$no=$i + 1;
					echo "<td>" .$no. "</td>";
					foreach (($bulan_tahun) as $key => $yyyymm) {
						// $dat=$so[$key][$i]['nama_customer']
						// ?$so[$key][$i]['nama_customer']. " <strong>(" . number_format($so[$key][$i]['amount']).")</strong>":" ";
						
						if ($so[$key][$i]['nama_customer']== null) {
							echo "<td> - </td>";
						}
						else{
							echo "<td>" . $so[$key][$i]['nama_customer']. " <strong> Rp(" . number_format($so[$key][$i]['amount']).")</strong> </td>";
						}
						
					}
					echo "</tr>";
				}

				?>




			</tbody>

			<tfoot></tfoot>
		</table>


	</body>

	</html>