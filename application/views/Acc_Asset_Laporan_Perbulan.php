<!DOCTYPEhtml>
	<html>

	<head>

		<?php require '__laporan_style.php' ?>
	</head>

	<body>

		<?php require '__laporan_header.php' ?>

		<!-- <pre><?php print_r($po) ?></pre> -->

		<h1 class="title">LAPORAN ASSET</h1>
		<br>

		<div class="d-flex flex-between">
			<table class="" style="width:30%">
				<!-- <tr>
					<td>Lokasi</td>
					<th>:</th>
					<td><?= $filter_lokasi ?></td>
				</tr>
				<tr>
					<td>Gudang</td>
					<th>:</th>
					<td><?= $filter_gudang ?></td>
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
					<th style="width:2%" rowspan="2">no</th>
					<th rowspan="2">kode</th>
					<th rowspan="2">nama</th>
					<th colspan="2">Purchase</th>
					<th colspan="2">Depreciation &nbsp;<?= $nama_periode ?></th>
					<th colspan="3">Accumulate Depreciation </th>
					<th rowspan="2">Book Value</th>
				</tr>
				<tr>

					<th>Date</th>
					<th>Price</th>
					<th>Voucher No.</th>
					<th>Amount</th>
					<th>Jan-&nbsp;<?= $nama_periode ?></th>
					<th>Prev Year</th>
					<th>YTD</th>
				</tr>

			</thead>


			<tbody>
				<?php
				$no = 0;
				$TotBulanIni = 0;
				$TotTahunIni = 0;
				$TotTahunLalu = 0;
				$TotNilaiAkhir = 0;
				$TotHargaBeli = 0;
				$TotYTD =0;
				foreach ($asset as $key => $tipe) {
					$assetDetail = $tipe['detail'];
					$subTotBulanIni = 0;
					$subTotTahunIni = 0;
					$subTotNilaiAkhir = 0;
					$subTotTahunLalu = 0;
					$subTotHargaBeli = 0;
					$subTotYTD = 0;
					echo "<tr><td colspan='17'>" . $tipe['nama'] . "</td></tr>";
					foreach ($assetDetail as $key => $val) {
						$no = $no + 1;
						$ytd=($val['nilai_susut_tahun_ini'] + $val['nilai_susut_tahun_lalu']);
						$nilaiAkhir = $val['harga_beli'] - $ytd;
						$subTotHargaBeli = $subTotHargaBeli + $val['harga_beli'];
						$subTotBulanIni = $subTotBulanIni + $val['nilai_susut_bln_ini'];
						$subTotTahunIni = $subTotTahunIni + $val['nilai_susut_tahun_ini'];
						$subTotTahunLalu = $subTotTahunLalu + $val['nilai_susut_tahun_lalu'];
						$subTotNilaiAkhir = $subTotNilaiAkhir + $nilaiAkhir;
						$subTotYTD = $subTotYTD + $ytd;
						$TotHargaBeli = $TotHargaBeli + $val['harga_beli'];
						$TotBulanIni = $TotBulanIni + $val['nilai_susut_bln_ini'];
						$TotTahunIni = $TotTahunIni + $val['nilai_susut_tahun_ini'];
						$TotTahunLalu = $TotTahunLalu + $val['nilai_susut_tahun_lalu'];
						$TotNilaiAkhir = $TotNilaiAkhir + $nilaiAkhir;
						$TotYTD = $TotYTD + $ytd;
						
						$actual_link = "http://$_SERVER[HTTP_HOST]" . "/plantationlive-api/api/GlobalReport/acc_asset_detail?id=" . $val['id'] . "";
				?>
						<tr>
							<td><?= $no ?></td>
							<td left><?= '<a href="' . $actual_link  . '" target="_blank"> '. $val['kode'].''  ?></td>
							<td left><?= $val['nama'] ?></td>
							<td right><?= tgl_indo($val['tgl_beli']) ?></td>
							<td right><?= number_format($val['harga_beli'], 2) ?></td>
							<td left><?= $val['no_jurnal_bulan_ini'] ?></td>
							<td right><?= number_format($val['nilai_susut_bln_ini'], 2) ?></td>
							<td right><?= number_format($val['nilai_susut_tahun_ini'], 2) ?></td>
							<td right><?= number_format($val['nilai_susut_tahun_lalu'], 2) ?></td>
							<td right><?= number_format($ytd, 2) ?></td>
							<td right><?= number_format($nilaiAkhir, 2) ?></td>

						</tr>
					<?php } ?>
					<tr>
						<td colspan="4">Sub Total&nbsp;<?= $tipe['nama'] ?></td>
						<td right><?= number_format($subTotHargaBeli, 2) ?></td>
						<td center></td>
						<td right><?= number_format($subTotBulanIni, 2) ?></td>		
						<td right><?= number_format($subTotTahunIni, 2) ?></td>
						<td right><?= number_format($subTotTahunlalu, 2) ?></td>
						<td right><?= number_format($subTotYTD, 2) ?></td>
						<td right><?= number_format($subTotNilaiAkhir, 2) ?></td>
					</tr>
					<tr>
						<td colspan="9">&nbsp;</td>
					</tr>
				<?php } ?>
				<tr>
					<td colspan="4"><b>Total</b></td>\				
					<td right><b><?= number_format($TotHargaBeli, 2) ?></b></td>
					<td right><b></td>
					<td right><b><?= number_format($TotBulanIni, 2) ?></b></td>					
					<td right><b><?= number_format($TotTahunIni, 2) ?></b></td>
					<td right><b><?= number_format($TotTahunlalu, 2) ?></b></td>
					<td right><b><?= number_format($TotYTD, 2) ?></b></td>
					<td right><b><?= number_format($TotNilaiAkhir, 2) ?></b></td>
				</tr>

			</tbody>

			<tfoot>

			</tfoot>
		</table>






		<pre><?php //print_r($headeran) 
				?></pre>

	</body>

	</html>