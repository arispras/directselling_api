<!DOCTYPEhtml>
	<html>

	<head>

		<?php require '__laporan_style.php' ?>
		<style>
			* {
				font-size: 10px !important;
			}

			.table-bg th,
			.table-bg td {
				font-size: 0.9em !important;
			}
		</style>
	</head>

	<body>


		<div center>
			<!-- <img src="data:image/png;base64,<?= base64_encode(file_get_contents(base_url('logo_perusahaan.png'))) ?>" width="15%"> -->
		</div>

		<div>
			<h1 center class="title">SALES ORDER</h1>
			<hr>
			
			<table>
				<tr>
					<td width="15%">No SO</td>
					<td width="2%">:</td>
					<td width="33%"><strong> <?= $header['no_so'] ?></strong></td>
					<td width="15%">Tanggal SO</td>
					<td width="2%">:</td>
					<td width="33%"><strong> <?= tgl_indo($header['tanggal_so']) ?></strong></td>
				</tr>	
			</table>
			
			<table>
				<tr>
					
					<td width="15%">Customer</td>
					<td width="2%">:</td>
					<td width="33%"><strong> <?= $header['nama_customer'] ?></strong></td>
					<td width="15%">Tenor</td>
					<td width="2%">:</td>
					<td width="33%"><strong> <?= ($header['tenor']) ?></strong></td>
				</tr>		
			</table>
	
			<table>	
				<tr>
					<td width="15%">Sales</td>
					<td width="2%">:</td>
					<td width="33%"><strong> <?= $header['sales'] ?></strong></td>
					<td width="15%">Surveyor</td>
					<td width="2%">:</td>
					<td width="33%"><strong> <?= ($header['surveyor']) ?></strong></td>
				</tr>	
					
			</table>
			
			<table>	
				<tr>
					<td width="15%">Sales Supervisor</td>
					<td width="2%">:</td>
					<td width="33%"><strong> <?= $header['sales_supervisor'] ?></strong></td>
					<td width="15%">Demo Booker</td>
					<td width="2%">:</td>
					<td width="33%"><strong> <?= ($header['demo_booker']) ?></strong></td>
				</tr>	
					
			</table>
		
			
			<!-- <div id="pageCounter" class="page">
				<page size="A4"></page>
				<div id="pageNumbers" class="page">
					<div class="page-number"></div>

				</div>
			</div> -->
			<br>


			<table class="table-bg border">
				<thead>
					<tr>
						<th>No</th>
						<th>Kode Barang</th>
						<th>Nama Barang</th>
						<th>Uom</th>
						<th>Qty</th>
						<th>Harga</th>
						<th>Diskon</th>
						<th>DP</th>
						<th>Jumlah</th>
						<th>Piutang</th>
						<th>Angsuran</th>
						<!-- <th>Keterangan</th> -->
					</tr>
				</thead>
				<tbody>
					<?php 
					$no = 0;
					$total_piutang = 0;
					$total_angsuran = 0;	
					$total_diskon = 0;	
					$total_dp = 0;	
					$total_sub_total = 0;
					
					foreach ($detail as $key => $val) { 
						$no = $no + 1;
						$total_piutang += $val['nilai_piutang'];
						$total_angsuran += $val['nilai_angsuran'];
						$total_diskon += $val['diskon'];
						$total_dp += $val['dp'];
						$total_sub_total += $val['total'];
						?>
						<tr>
							<td center width="2%"><?= $no ?></td>
							<td width="10%"><?= $val['kode_barang'] ?></td>
							<td width="10%"><?= $val['nama_barang'] ?></td>
							<td width="5%"><?= $val['uom'] ?></td>
							<td center width="7%"><?= $val['qty'] ?></td>
							<td center width="8%"><?= number_format( $val['harga'],0 )?></td>							
							<td center width="8%"><?=number_format($val['diskon'],0) ?></td>						
							<td center width="8%"><?= number_format($val['dp'],0) ?></td>
							<td center width="10%"><?= number_format($val['total'],0) ?></td>
							<td center width="10%"><?= number_format($val['nilai_piutang'],0) ?></td>
							<td center width="10%"><?= number_format($val['nilai_angsuran'],0) ?></td>
							<!-- <td width="10%"><?= $val['ket'] ?></td> -->
						</tr>

					<?php } ?>
				</tbody>
				<tfoot>
					<tr>
						<td colspan="6" center>Total</td>
						<td center><?= number_format($total_diskon,0) ?></td>
						<td center><?= number_format($total_dp,0) ?></td>
						<td center><?= number_format($total_sub_total,0) ?></td>
						<td center><?= number_format($total_piutang,0) ?></td>
						<td center><?= number_format($total_angsuran,0) ?></td>
					</tr>
				</tfoot>
			</table>
		
			<br><br>

			<table>
				<tr>
					<td center>
						<p>Dibuat Oleh</p>
						<br><br><br><br>
						<div>
							<div></div>
							<div><?= date_format(date_create($header['dibuat_tanggal']), 'd M Y') ?></div>
						</div>
					</td>
					<td center>
						<p>Disetujui Oleh</p>
						<br><br><br><br>
						<div>
							<div></div>
							<div></div>
						</div>
				</tr>
			</table>
			<br>
		

	</body>

	</html>