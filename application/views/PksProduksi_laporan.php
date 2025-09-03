<!DOCTYPEhtml>
<html>
	<head>
	
		<?php require '__laporan_style.php' ?>
	</head>
	<body>
	<?php require '__laporan_header.php' ?>
		<!-- <h3 class="title">Laporan Harian Produksi MILL</h3>
		<br> -->
		
		<!-- <div class="d-flex flex-between">
			<table class="" style="width:30%">
				<tr>
					<td style="width:30%">Lokasi</td>
					<th>:</th>
					<td><?= $input['lokasi_nama'] ?></td>
				</tr>
				<tr>
					<td>Tanggal</td>
					<th>:</th>
					<td><?= $input['tanggal'] ?></td>
				</tr>
			</table>
		</div> -->
		
		<br><br>

		<div style="border: 2px solid black; padding:20px;">

			<div style="border: 2px solid black" center>
				<h1 bold>LAPORAN HARIAN PRODUKSI MILL</h1>
				<!-- <h1 bold>KLINIK ANNAJAH</h1> -->
			</div>
			
			<div style="padding:2% 5%;">
				<table class="" style="width:30%">
					<tr>
						<td style="width:30%">Lokasi</td>
						<th>:</th>
						<td><?= $input['lokasi_nama'] ?></td>
					</tr>
					<tr>
						<td>Tanggal</td>
						<th>:</th>
						<td><?= tgl_indo($input['tanggal']) ?></td>
					</tr>
				</table>			

				<h3>1. TBS DITERIMA</h3>
				<table class="table-bg" border-none>
					<thead>
						<td border-none></td>
						<th></th>
						<th>INTI</th>
						<th>PLASMA</th>
						<th>P3 Ex. PT</th>
						<th>P3 Ex. Person</th>
						<th>TOTAL</th>
						<td border-none></td>
					</thead>
					<tbody>
						<tr>
							<td style="width:3%" border-none>a.</td>
							<td>Hari ini</td>
							<td right>85,860</td>
							<td right>8,250</td>
							<td right>343,670</td>
							<td>-</td>
							<td right>437,780</td>
							<td style="width:5%" border-none>kg</td>
						</tr>
						<tr>
							<td border-none>b.</td>
							<td>s/d Hari ini</td>
							<td right>85,860</td>
							<td right>8,250</td>
							<td right>343,670</td>
							<td>-</td>
							<td right>437,780</td>
							<td border-none>kg</td>
						</tr>
						<tr>
							<td border-none>c.</td>
							<td>s/d Bulan ini</td>
							<td right>85,860</td>
							<td right>8,250</td>
							<td right>343,670</td>
							<td>-</td>
							<td right>437,780</td>
							<td border-none>kg</td>
						</tr>
					</tbody>
				</table>
			</div>
			
			
			
			<div class="d-flex flex-between">

				<div style="width:100%; padding:0 5%;">
					
					<!-- TBS OLAH -->
					<div class="box">
						<h3>2. TBS OLAH</h3>
						<table>
							<tr>
								<td style="width:5%">a.</td>
								<td>hari ini</td>
								<th right>446,780</th>
								<td style="width:5%">kg</td>
							</tr>
							<tr>
								<td>b.</td>
								<td>s/d Hari ini</td>
								<th right>10,080,990</th>
								<td>kg</td>
							</tr>
							<tr>
								<td>c.</td>
								<td>s/d Bulan ini</td>
								<th right>10,080,990</th>
								<td>kg</td>
							</tr>
							<tr>
								<td>d.</td>
								<td>Restan</td>
								<th right>32,000</th>
								<td>kg</td>
							</tr>
						</table>
					</div>

					<!-- HASIL PENGOLAHAN -->
					<div class="box">
						<h3>3. HASIL PENGOLAHAN</h3>
						<table>
							<tr>
								<td>a.</td>
								<td>Minyak sawit hari ini</td>
								<th right>446,780</th>
								<td style="width:5%">kg</td>
							</tr>
							<tr>
								<td></td>
								<td>Minyak sawit s/d hari ini</td>
								<th right>446,780</th>
								<td>kg</td>
							</tr>
							<tr>
								<td></td>
								<td>Minyak sawit s/d bulan ini</td>
								<th right>446,780</th>
								<td>kg</td>
							</tr>
							<tr>
								<td>b.</td>
								<td>Rendemen hari ini</td>
								<th right>446,780</th>
								<td>kg</td>
							</tr>
							<tr>
								<td></td>
								<td>Rendemen s/d hari ini</td>
								<th right>446,780</th>
								<td>kg</td>
							</tr>
							<tr>
								<td></td>
								<td>Rendemen s/d bulan ini</td>
								<th right>446,780</th>
								<td>kg</td>
							</tr>
							<tr>
								<td>c.</td>
								<td>FFA</td>
								<th right><?= $lab_pengolahan['cpo_ffa'] ?></th>
								<td>%</td>
							</tr>
							<tr>
								<td>d.</td>
								<td>Kadar Air</td>
								<th right><?= $lab_pengolahan['cpo_moisture'] ?></th>
								<td>%</td>
							</tr>
							<tr>
								<td>e.</td>
								<td>Kadar Kotoran</td>
								<th right><?= $lab_pengolahan['cpo_dirt'] ?></th>
								<td>%</td>
							</tr>
						</table>
					</div>

					<!-- PERSEDIAAN MINYAK SAWIT -->
					<div class="box">
						<h3>4. PERSEDIAAN MINYAK SAWIT</h3>
						<?php foreach ($sounding as $key=>$val) { ?>
						<?php $no += 1; ?>
						<?php $jumlah_persediaan_ms += (int) $val['hasil_total']; ?>
						<table style="margin-bottom:10px;">
							<tr>
								<td style="width:5%;">a.</td>
								<td>Tanki No.<?= $no ?></td>
								<td>Kap. <?= $val['kapasitas'] ?> T</td>
								<th right><?= number_format($val['hasil_total']) ?></th>
								<td style="width:5%;">kg</td>
							</tr>
							<tr>
								<td></td>
								<td colspan="2">FFA</td>
								<th right><?= $val['ffa'] ?></th>
								<td>%</td>
							</tr>
							<tr>
								<td></td>
								<td colspan="2">Kadar air</td>
								<th right><?= $val['kadar_air'] ?></th>
								<td>%</td>
							</tr>
							<tr>
								<td></td>
								<td colspan="2">kadar Kotoran</td>
								<th right><?= $val['dirt'] ?></th>
								<td>%</td>
							</tr>
						</table>
						<?php } ?>
						<table style="margin-bottom:10px;">
							<tr>
								<td style="width:5%;"></td>
								<td><b>JUMLAH PERSEDIAAN MS</b></td>
								<th right><?= number_format($jumlah_persediaan_ms) ?></th>
								<td style="width:5%;">kg</td>
							</tr>
						</table>
					</div>

					<!-- PERSEDIAAN INTI SAWIT -->
					<div class="box">
						<h3>5. PERSEDIAAN INTI SAWIT</h3>
						<table>
							<tr>
								<td style="width:5%;">a.</td>
								<td>FFA</td>
								<th right><?= $lab_pengolahan['kernel_ffa'] ?></th>
								<td style="width:5%">%</td>
							</tr>
							<tr>
								<td>b.</td>
								<td>Kadar Air</td>
								<th right><?= $lab_pengolahan['kernel_ffa'] ?></th>
								<td>%</td>
							</tr>
							<tr>
								<td>c.</td>
								<td>Kadar Kotoran</td>
								<th right><?= $lab_pengolahan['kernel_ffa'] ?></th>
								<td>%</td>
							</tr>
						</table>
					</div>

					<!-- JAM EFEKTIF PENGOLAHAN -->
					<div class="box">
						<h3>6. JAM EFEKTIF PENGOLAHAN</h3>
						<table>
							<tr>
								<td style="width:5%">a.</td>
								<td>Hari ini</td>
								<th right>446,780</th>
								<td style="width:5%">kg</td>
							</tr>
							<tr>
								<td>b.</td>
								<td>s/d Hari Ini</td>
								<th right>446,780</th>
								<td>kg</td>
							</tr>
							<tr>
								<td>c.</td>
								<td>s/d Bulan Ini</td>
								<th right>446,780</th>
								<td>kg</td>
							</tr>
						</table>
					</div>

					<!-- KAPASITAS OLAH -->
					<div class="box">
						<h3>7. KAPASITAS OLAH</h3>
						<table>
							<tr>
								<td style="width:5%">a.</td>
								<td>Hari ini</td>
								<th right>446,780</th>
								<td style="width:5%">kg</td>
							</tr>
							<tr>
								<td>b.</td>
								<td>s/d Hari Ini</td>
								<th right>446,780</th>
								<td>kg</td>
							</tr>
							<tr>
								<td>c.</td>
								<td>s/d Bulan Ini</td>
								<th right>446,780</th>
								<td>kg</td>
							</tr>
						</table>
					</div>

				</div>


				<div style="width:100%; padding:0 5%;">
					<!-- INTI MINYAK PRODUKSI -->
					<div class="box">
						<h3>8. INTI MINYAK PRODUKSI</h3>
						<table>
							<tr>
								<td style="width:5%">a.</td>
								<td>Inti sawit hari ini</td>
								<th right>446,780</th>
								<td style="width:5%">kg</td>
							</tr>
							<tr>
								<td></td>
								<td>Inti sawit s/d hari ini</td>
								<th right>446,780</th>
								<td>kg</td>
							</tr>
							<tr>
								<td></td>
								<td>Inti sawit s/d Bulan ini</td>
								<th right>446,780</th>
								<td>kg</td>
							</tr>
							<tr>
								<td>b.</td>
								<td>Rendemen hari ini</td>
								<th right>446,780</th>
								<td>kg</td>
							</tr>
							<tr>
								<td></td>
								<td>Rendemen s/d hari ini</td>
								<th right>446,780</th>
								<td>kg</td>
							</tr>
							<tr>
								<td></td>
								<td>Rendemen s/d Bulan ini</td>
								<th right>446,780</th>
								<td>kg</td>
							</tr>
							<tr>
								<td>c.</td>
								<td>FFA hari ini</td>
								<th right>446,780</th>
								<td>kg</td>
							</tr>
							<tr>
								<td>d.</td>
								<td>Kadar air hari ini</td>
								<th right>446,780</th>
								<td>kg</td>
							</tr>
							<tr>
								<td>e.</td>
								<td>Kadar kotoran hari ini</td>
								<th right>446,780</th>
								<td>kg</td>
							</tr>
						</table>
					</div>

					<!-- PENGIRIMAN MINYAK SAWIT -->
					<div class="box">
						<h3>9. PENGIRIMAN MINYAK SAWIT</h3>
						<table>
							<tr>
								<td style="width:5%">a.</td>
								<td>Hari ini</td>
								<th right>446,780</th>
								<td style="width:5%">kg</td>
							</tr>
							<tr>
								<td>b.</td>
								<td>s/d Hari ini</td>
								<th right>446,780</th>
								<td>kg</td>
							</tr>
							<tr>
								<td>c.</td>
								<td>Inti sawit s/d Bulan ini</td>
								<th right>446,780</th>
								<td>kg</td>
							</tr>
						</table>
					</div>

					<!-- PENGIRIMAN INTI SAWIT -->
					<div class="box">
						<h3>9. PENGIRIMAN INTI SAWIT</h3>
						<table>
							<tr>
								<td style="width:5%">a.</td>
								<td>Hari ini</td>
								<th right>446,780</th>
								<td style="width:5%">kg</td>
							</tr>
							<tr>
								<td>b.</td>
								<td>s/d Hari ini</td>
								<th right>446,780</th>
								<td>kg</td>
							</tr>
							<tr>
								<td>c.</td>
								<td>Inti sawit s/d Bulan ini</td>
								<th right>446,780</th>
								<td>kg</td>
							</tr>
						</table>
					</div>

					<!-- LOSSES -->
					<div class="box">
						<h3>11. LOSSES</h3>
						<div class="box">
							<h3>a. OIL LOSSES</h3>
							<table>
								<tr>
									<td style="width:5%"></td>
									<td>Press</td>
									<th right><?= $lab_pengolahan['cpo_los_press'] ?></th>
									<td style="width:5%">%</td>
								</tr>
								<tr>
									<td></td>
									<td>Nut</td>
									<th right><?= $lab_pengolahan['cpo_los_nut'] ?></th>
									<td>%</td>
								</tr>
								<tr>
									<td></td>
									<td>E Bunch</td>
									<th right><?= $lab_pengolahan['cpo_los_e_bunch'] ?></th>
									<td>%</td>
								</tr>
								<tr>
									<td></td>
									<td>Final Effluent</td>
									<th right><?= $lab_pengolahan['cpo_los_effluent'] ?></th>
									<td>%</td>
								</tr>
								<tr>
									<td></td>
									<td>Fruit Loss</td>
									<th right><?= $lab_pengolahan['cpo_los_fruit'] ?></th>
									<td>%</td>
								</tr>
								<?php $total_cpo = $lab_pengolahan['cpo_los_press'] + $lab_pengolahan['cpo_los_nut'] + $lab_pengolahan['cpo_los_e_bunch'] + $lab_pengolahan['cpo_los_effluent'] + $lab_pengolahan['cpo_los_fruit'] ?>
								<tr>
									<td></td>
									<td right><b>TOTAL</b></td>
									<th right><?= $total_cpo ?></th>
									<td>%</td>
								</tr>
							</table>
						</div>

						<div class="box">
							<h3>b. KERNEL LOSSES</h3>
							<table>
								<tr>
									<td style="width:5%"></td>
									<td>Fruit Loss</td>
									<th right><?= $lab_pengolahan['kernel_los_fruit'] ?></th>
									<td style="width:5%">kg</td>
								</tr>
								<tr>
									<td></td>
									<td>Fibre Cyclone</td>
									<th right><?= $lab_pengolahan['kernel_los_fiber_cyclone'] ?></th>
									<td>%</td>
								</tr>
								<tr>
									<td></td>
									<td>LTDS 1</td>
									<th right><?= $lab_pengolahan['kernel_los_ltds1'] ?></th>
									<td>%</td>
								</tr>
								<tr>
									<td></td>
									<td>LTDS 2</td>
									<th right><?= $lab_pengolahan['kernel_los_ltds2'] ?></th>
									<td>%</td>
								</tr>
								<tr>
									<td></td>
									<td>Claybath</td>
									<th right><?= $lab_pengolahan['kernel_los_claybath'] ?></th>
									<td>%</td>
								</tr>
								<?php $total_kernel = $lab_pengolahan['kernel_los_fruit'] + $lab_pengolahan['kernel_los_fiber_cyclone'] + $lab_pengolahan['kernel_los_ltds1'] + $lab_pengolahan['kernel_los_ltds2'] + $lab_pengolahan['kernel_los_claybath'] ?>
								<tr>
									<td></td>
									<td right><b>TOTAL</b></td>
									<th right><?= $total_kernel ?></th>
									<td>%</td>
								</tr>
							</table>
						</div>
					</div>

					<!-- LOSSES -->
					<div class="box">
						<h3>12. KETERANGAN</h3>
					</div>

					<div class="box">
						<table style="margin-top:25%;">
							<tr>
								<th>Asst Lab.</th>
								<th>KTU</th>
								<th>Mill Manager</th>
							</tr>
						</table>
					</div>


				</div>


			</div>
		</div>
		
		


		<pre><?php //print_r($lab_pengolahan) ?></pre>

	</body>
</html>
