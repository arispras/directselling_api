<!DOCTYPEhtml>
<html>
	<head>
	
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
	</head>
	<body>

		<?php require '__laporan_header.php' ?>

		<h3 class="title">Laporan Lembur</h3>
		<br>
		
		<div class="d-flex flex-between">
			<table class="no_border" style="width:30%">
				<tr>
					<td style="width:30%">Lokasi</td>
					<th>:</th>
					<td><?= $input['lokasi_nama'] ?></td>
				</tr>
				<tr>
					<td style="width:30%">Karyawan</td>
					<th>:</th>
					<td><?= $karyawan['nama'] ?></td>
				</tr>
				<tr>
					<td>Priode</td>
					<th>:</th>
					<td><?= tgl_indo($input['tgl_mulai']) ?> s/d <?= tgl_indo($input['tgl_akhir']) ?></td>
				</tr>
			</table>

			<!-- <table class="border" style="width:30%"></table> -->
		</div>



		
		<br><br>



<?php
function selisih_waktu($data1, $data2)
{
	$data1 = explode(':', $data1);
	$data2 = explode(':', $data2);
	$calc1 = sprintf('%02d', abs($data1[0] - $data2[0]));
	$calc2 = sprintf('%02d', abs($data1[1] - $data2[1]));
	return $calc1.':'.$calc2;
}
?>


		
		<table class="table-bg border">
			<thead>
				<tr>
					<th center>No</th>
					<th>Tanggal</th>
					<th>Tipe</th>
					<th>Jam</th>
					<th>Jam Istirahat</th>
					<th>Nilai (RP)</th>

					<!-- <th colspan="<?= count($date_loop) ?>"><?= $bulan[sprintf('%01d',$input['bulan'])] ?> <?= $input['tahun'] ?></th>
					<th colspan="<?= count($lembur_kode) ?>">Total</th> -->
				</tr>
				<!-- <tr>
					
					<?php foreach($date_loop as $key=>$val) { ?>
					<th><?= $key ?></th>
					<?php } ?>

					

				</tr> -->
			</thead>

			<tbody>
				<?php foreach ($lembur as $key=>$val) { ?>
				<?php
					$no += 1;
					$total_jam += $val['jumlah_jam'];
					$total_jam_istirahat += $val['istirahat'];
					$total_nilai += $val['nilai_lembur'];
					?>
				<tr>
					
					<td center width="5%"><?= $no ?></td>
					<td center><?= tgl_indo_normal($val['tanggal']) ?></td>
					<td center width="20%"><?= $val['tipe_lembur'] ?></td>
					<td right width="10%"><?= $val['jumlah_jam'] ?></td>
					<td right width="10%"><?= $val['istirahat'] ?></td>
					<td right width="10%"><?= number_format($val['nilai_lembur']) ?></td>
					 
				</tr>
				<?php } ?>
				<tr>
					<th colspan="3">TOTAL</th>
					<td right><?= $total_jam ?></td>
					<td right><?= $total_jam_istirahat ?></td>
					<td right><?= number_format($total_nilai) ?></td>
				</tr>
			</tbody>
			
			<tfoot></tfoot>
		</table>
		





		<pre><?php //print_r($lembur) ?></pre>

	</body>
</html>