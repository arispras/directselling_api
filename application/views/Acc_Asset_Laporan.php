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
					<th style="width:2%">no</th>
					<th>lokasi</th>
					<th>kode</th>
					<th>nama</th>
					<th>tanggal beli</th>
					<th>asset tipe</th>
					<th>harga beli</th>
					<th>tanggal mulai pakai</th>
					<th>nilai asset</th>
					<th>nilai residu</th>
					<th>posisi asset</th>
					<th>status</th>
					<th>lama penyusutan(bulan)</th>
					<th>metode penyusutan</th>
					<th>keterangan</th>
					<th>jumlah penyusutan</th>
					<th>Nilai Asset Sekarang</th>
					<th>Rincian Penyusutan</th>
				</tr>

			</thead>


			<tbody>
				<?php
				$no = 0;
				$TotNilaiAsset = 0;
				$TotNilaiPenyusutan = 0;
				$TotNilaiAkhir = 0;

				foreach ($asset as $key => $tipe) {
					$assetDetail = $tipe['detail'];
					$subTotNilaiAsset = 0;
					$subTotNilaiPenyusutan = 0;
					$subTotNilaiAkhir = 0;
					echo "<tr><td colspan='17'>" . $tipe['nama'] . "</td></tr>";
					foreach ($assetDetail as $key => $val) {
						$no = $no + 1;
						$subTotNilaiAsset = $subTotNilaiAsset + $val['nilai_asset'];
						$subTotNilaiPenyusutan = $subTotNilaiPenyusutan + $val['nilai_susut'];
						$subTotNilaiAkhir = $subTotNilaiAkhir + ($val['nilai_asset'] - $val['nilai_susut']);
						$TotNilaiAsset = $TotNilaiAsset + $val['nilai_asset'];
						$TotNilaiPenyusutan = $TotNilaiPenyusutan + $val['nilai_susut'];
						$TotNilaiAkhir = $TotNilaiAkhir + ($val['nilai_asset'] - $val['nilai_susut']);
						$actual_link = "http://$_SERVER[HTTP_HOST]" . "/plantationlive-api/api/GlobalReport/acc_asset_detail?id=" . $val['id'] . "";
				?>
						<tr>
							<td><?= $no ?></td>
							<td center><?= $val['lokasi'] ?></td>
							<td center><?= $val['kode'] ?></td>
							<td center><?= $val['nama'] ?></td>
							<td center><?= tgl_indo($val['tgl_beli']) ?></td>
							<td center><?= $val['asset_tipe'] ?></td>
							<td center><?= number_format($val['harga_beli'], 2) ?></td>
							<td center><?= tgl_indo($val['tgl_mulai_pakai']) ?></td>
							<td center><?= number_format($val['nilai_asset'], 2) ?></td>
							<td center><?= number_format($val['nilai_residu'], 2) ?></td>
							<td center><?= $val['posisi_asset'] ?></td>
							<td center><?= $val['status'] ?></td>
							<td center><?= $val['lama_bulan_penyusutan'] ?></td>
							<td center><?= $val['metode_penyusutan'] ?></td>
							<td center><?= $val['ket'] ?></td>
							<td center><?= number_format($val['nilai_susut'], 2) ?></td>
							<td center><?= number_format(($val['nilai_asset'] - $val['nilai_susut']), 2) ?></td>
							<td center><?= '<a href="' . $actual_link  . '" target="_blank"> Detail'  ?></td>
						</tr>
					<?php } ?>
					<tr>
						<td colspan="8">Sub Total&nbsp;<?= $tipe['nama'] ?></td>
						<td center><?= number_format($subTotNilaiAsset, 2) ?></td>
						<td center></td>
						<td center></td>
						<td center></td>
						<td center></td>
						<td center></td>
						<td center></td>
						<td center><?= number_format($subTotNilaiPenyusutan, 2) ?></td>
						<td center><?= number_format($subTotNilaiAkhir, 2) ?></td>
						<td center></td>
					</tr>
					<tr><td colspan="18">&nbsp;</td></tr>
				<?php } ?>
				<tr>
					<td colspan="8"><b>Total</b></td>
					<td center><b><?= number_format($subTotNilaiAsset, 2) ?></b></td>
					<td center></td>
					<td center></td>
					<td center></td>
					<td center></td>
					<td center></td>
					<td center></td>
					<td center><b><?= number_format($subTotNilaiPenyusutan, 2) ?></b></td>
					<td center><b><?= number_format($subTotNilaiAkhir, 2) ?></b></td>
					<td center></td>
				</tr>

			</tbody>

			<tfoot>

			</tfoot>
		</table>






		<pre><?php //print_r($headeran) 
				?></pre>

	</body>

	</html>