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
					echo $html='
				<style>
				* body{
					font-size: 11px ;
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
	</head>
	<body>
		
		<?php require '__laporan_header.php' ?>

		<!-- <pre><?php print_r($po) ?></pre> -->

		<h3 class="title">LAPORAN RINCIAN MEKANIK WORKSHOP</h3>
		<br>
		
		<div class="d-flex flex-between">
			<table class="no_border" style="width:30%">
				<tr>
					<td>Lokasi</td>
					<th>:</th>
					<td><?= $filter_lokasi ?></td>
				</tr>
				<!-- <tr>
					<td>supplier</td>
					<th>:</th>
					<td><?= $filter_supplier ?></td>
				</tr> -->
				<!-- <tr>
					<td>Gudang</td>
					<th>:</th>
					<td><?= $filter_gudang ?></td>
				</tr> -->
				<tr>
					<td>Periode Tanggal</td>
					<th>:</th>
					<td><?= tgl_indo($filter_tgl_awal) .' s/d '. tgl_indo($filter_tgl_akhir) ?></td>
				</tr>
			
			</table>	
		</div>

		<br><br>
		

		<table class="table-bg border">
			<thead>
				<tr>
					<th style="width:2%">no</th>
					<th style="width:10%" >No Transaksi</th>
					<th>Tanggal</th>
					<th>Kendaraan</th>
					<th>Karyawan</th>
					<!-- <th>Hasil Kerja</th> -->
					<th>Jumlah HK</th>
					<th>Rupiah HK</th>
					<th>premi</th>
				</tr>

			</thead>

			
			<tbody>
			<?php $no= 0 ?>
			<?php foreach ($po as $key=>$val) { ?> 
				
				<?php $no= $no+1 ?>
				<tr>
					<td center><?= $no ?></td>
					<td left><?= $val['no_transaksi'] ?></td>
					<td center><?= tgl_indo_normal($val['tanggal']) ?></td>
					<td left><?= '(' . $val['kode_kendaraan'] . ') ' . $val['nama_kendaraan'] ?></td>
					<td left><?= $val['karyawan'] ?></td>
					<!-- <td center><?= $val['hasil_kerja'] ?></td> -->
					<td right><?= $val['jumlah_hk'] ?></td>
					<td right><?= $val['rupiah_hk'] ?></td>
					<td right><?= $val['premi'] ?></td>
				</tr>
				<?php } ?>
			
				
			</tbody>
			
			<tfoot></tfoot>
		</table>

	</body>
</html>


				
