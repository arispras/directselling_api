<!DOCTYPEhtml>
	<html>

	<head>
	
		<?php require '__laporan_style.php' ?>
		<style>
			* { box-sizing:border-box; }
			table { width:100%; }
			
			[container-box] table td:nth-child(1) { width:25px; text-align:center; }
			[container-box] table td:nth-child(4) { width:50px; text-align:left; }

			[container-box] { padding:10px; width:45%; margin:auto; }
			
			[panel] {
				display: flex;
				justify-content: flex-between;
				flex-wrap: wrap;
			}
			@media print {
				[panel] {
					display: block;
				}
				[container-box] { padding:10px; width:100%; }
      }

			[box-value] { 
				border: 1px solid grey; 
				text-align: right; 
				width: 20%; 
				padding-left: 5px !important;
				padding-right: 5px !important;
			}
		</style>
	</head>

	<body>

		<div>
			<h1 center class="title">LAPORAN HARIAN PRODUKSI MILL</h1>
			<h1 center>PT. DINAMIMKAPRIMA ARTHA</h1>
		</div>
		<br>




		<div class>
			<div style="width:94%; margin:auto">
				<p>Hari / Tanggal: <b><?= tgl_indo($data['tanggal']) ?></b></p>
				<div>
					<h4>1. TBS DITERIMA</h4>
					<table class="table-bg">
						<tr>
							<th></th>
							<th>inti</th>
							<th>plasma</th>
							<th>p3 ex pt</th>
							<th>p3 ex person</th>
							<th>total</th>
						</tr>
						<tr>
							<td width="15%">Hari ini</td>
							<td box-value><?= number_format($data['satu_inti_hi']) ?></td>
							<td box-value><?= number_format($data['satu_plasma_hi']) ?></td>
							<td box-value><?= number_format($data['satu_p3expt_hi']) ?></td>
							<td box-value><?= number_format($data['satu_p3experson_hi']) ?></td>
							<td box-value><?= number_format($data['satu_total_hi']) ?></td>
						</tr>
						<tr>
							<td>s/d Hari ini</td>
							<td box-value><?= number_format($data['satu_inti_sdhi']) ?></td>
							<td box-value><?= number_format($data['satu_plasma_sdhi']) ?></td>
							<td box-value><?= number_format($data['satu_p3expt_sdhi']) ?></td>
							<td box-value><?= number_format($data['satu_p3experson_sdhi']) ?></td>
							<td box-value><?= number_format($data['satu_total_sdhi']) ?></td>
						</tr>
						<tr>
							<td>s/d Bulan ini</td>
							<td box-value><?= number_format($data['satu_inti_sdbi']) ?></td>
							<td box-value><?= number_format($data['satu_plasma_sdbi']) ?></td>
							<td box-value><?= number_format($data['satu_p3expt_sdbi']) ?></td>
							<td box-value><?= number_format($data['satu_p3experson_sdbi']) ?></td>
							<td box-value><?= number_format($data['satu_total_sdbi']) ?></td>
						</tr>
					</table>
				</div>
			</div>
		</div>
		
		
		<div panel>
			<div container-box>
				<div class="box">
					<h4>2. TBS DIOLAH</h4>
					<table>
						<tr>
							<td>a.</td>
							<td>Hari ini</td>
							<td box-value><?= number_format($data['dua_hi']) ?></td>
							<td width="5%">kg</td>
						</tr>
						<tr>
							<td>b.</td>
							<td>s/d Hari ini</td>
							<td box-value><?= number_format($data['dua_sdhi']) ?></td>
							<td>kg</td>
						</tr>
						<tr>
							<td>c.</td>
							<td>s/d Bulan ini</td>
							<td box-value><?= number_format($data['dua_sdbi']) ?></td>
							<td>kg</td>
						</tr>
						<tr>
							<td>d.</td>
							<td><b>Restan</b></td>
							<td box-value><?= number_format($data['dua_restan']) ?></td>
							<td>kg</td>
						</tr>
					</table>
				</div>
				<div class="box">
					<h4>3. HASIL PENGOLAHAN</h4>
					<table>
						<tr>
							<td width="5%">a.</td>
							<td>Minyak Sawit Hari ini</td>
							<td box-value><?= number_format($data['tiga_hi']) ?></td>
							<td width="5%">kg</td>
						</tr>
						<tr>
							<td></td>
							<td>Minyak Sawit s/d Hari ini</td>
							<td box-value><?= number_format($data['tiga_sdhi']) ?></td>
							<td>kg</td>
						</tr>
						<tr>
							<td></td>
							<td>Minyak Sawit s/d Bulan ini</td>
							<td box-value><?= number_format($data['tiga_sdbi']) ?></td>
							<td>kg</td>
						</tr>
						<tr>
							<td width="5%">b.</td>
							<td>Rendemen Hari ini</td>
							<td box-value><?= number_format($data['tiga_rendemen_hi'],2) ?></td>
							<td>%</td>
						</tr>
						<tr>
							<td></td>
							<td>Rendemen s/d Hari ini</td>
							<td box-value><?= number_format($data['tiga_rendemen_sdhi'],2) ?></td>
							<td>%</td>
						</tr>
						<tr>
							<td></td>
							<td>Rendemen s/d Bulan ini</td>
							<td box-value><?= number_format($data['tiga_rendemen_sdbi'],2) ?></td>
							<td>%</td>
						</tr>
						<tr>
							<td>c.</td>
							<td>FFA</td>
							<td box-value><?= number_format($data['tiga_ffa'],2) ?></td>
							<td>%</td>
						</tr>
						<tr>
							<td>d.</td>
							<td>Kadar Air</td>
							<td box-value><?= number_format($data['tiga_kadar_air'],2) ?></td>
							<td>%</td>
						</tr>
						<tr>
							<td>e.</td>
							<td>Kadar Kotoran</td>
							<td box-value><?= number_format($data['tiga_kadar_kotoran'],2) ?></td>
							<td>%</td>
						</tr>
					</table>
				</div>
				<div class="box">
					<h4>4. PERSEDIAAN MINYAK SAWIT</h4>
					<table>
						<tr>
							<td>a.</td>
							<td>Tanki No.1 kap.500 T</td>
							<td box-value><?= number_format($data['empat_tank1_kg']) ?></td>
							<td width="5%">kg</td>
						</tr>
						<tr>
							<td></td>
							<td>FFA</td>
							<td box-value><?= number_format($data['empat_tank1_ffa'],2) ?></td>
							<td>%</td>
						</tr>
						<tr>
							<td></td>
							<td>Kadar Air</td>
							<td box-value><?= number_format($data['empat_tank1_kadar_air'],2) ?></td>
							<td>%</td>
						</tr>
						<tr>
							<td></td>
							<td>Kadar kotoran</td>
							<td box-value><?= number_format($data['empat_tank1_kadar_kotoran'],2) ?></td>
							<td>%</td>
						</tr>
						<tr>
							<td></td>
							<td>DOBI</td>
							<td box-value><?= number_format($data['empat_tank1_dobi'],2) ?></td>
							<td></td>
						</tr>
					</table>
					<table>
						<tr>
							<td>b.</td>
							<td>Tanki No.2 kap.2000 T</td>
							<td box-value><?= number_format($data['empat_tank2_kg']) ?></td>
							<td width="5%">kg</td>
						</tr>
						<tr>
							<td></td>
							<td>FFA</td>
							<td box-value><?= number_format($data['empat_tank2_ffa'],2) ?></td>
							<td>%</td>
						</tr>
						<tr>
							<td></td>
							<td>Kadar Air</td>
							<td box-value><?= number_format($data['empat_tank2_kadar_air'],2) ?></td>
							<td>%</td>
						</tr>
						<tr>
							<td></td>
							<td>Kadar kotoran</td>
							<td box-value><?= number_format($data['empat_tank2_kadar_kotoran'],2) ?></td>
							<td>%</td>
						</tr>
						<tr>
							<td></td>
							<td>DOBI</td>
							<td box-value><?= number_format($data['empat_tank2_dobi'],2) ?></td>
							<td></td>
						</tr>
					</table>
					<table>
						<tr>
							<td>c.</td>
							<td>Tanki No.3 kap.2000 T</td>
							<td box-value><?= number_format($data['empat_tank3_kg']) ?></td>
							<td width="5%">kg</td>
						</tr>
						<tr>
							<td></td>
							<td>FFA</td>
							<td box-value><?= number_format($data['empat_tank3_ffa'],2) ?></td>
							<td>%</td>
						</tr>
						<tr>
							<td></td>
							<td>Kadar Air</td>
							<td box-value><?= number_format($data['empat_tank3_kadar_air'],2) ?></td>
							<td>%</td>
						</tr>
						<tr>
							<td></td>
							<td>Kadar kotoran</td>
							<td box-value><?= number_format($data['empat_tank3_kadar_kotoran'],2) ?></td>
							<td>%</td>
						</tr>
						<tr>
							<td></td>
							<td>DOBI</td>
							<td box-value><?= number_format($data['empat_tank3_dobi'],2) ?></td>
							<td></td>
						</tr>
					</table>
					<table>
						<tr>
							<td></td>
							<td><b>JUMLAH PERSEDIAAN MS</b></td>
							<td box-value><?= number_format($data['empat_total']) ?></td>
							<td width="5%">kg</td>
						</tr>
					</table>
				</div>
				<div class="box">
					<h4>5. PERSEDIAAN INTI SAWIT</h4>
					<table>
						<tr>
							<td></td>
							<td></td>
							<td box-value><?= number_format($data['lima_kg']) ?></td>
							<td width="5%">kg</td>
						</tr>
						<tr>
							<td>a.</td>
							<td>FFA</td>
							<td box-value><?= number_format($data['lima_ffa'],2) ?></td>
							<td width="5%">%</td>
						</tr>
						<tr>
							<td>b.</td>
							<td>Kadar Air</td>
							<td box-value><?= number_format($data['lima_kadar_air'],2) ?></td>
							<td>%</td>
						</tr>
						<tr>
							<td>c.</td>
							<td>Kadar kotoran</td>
							<td box-value><?= number_format($data['lima_kadar_kotoran'],2) ?></td>
							<td>%</td>
						</tr>
					</table>
				</div>
				<div class="box">
					<h4>6. JAM AKTIF PENGOLAHAN</h4>
					<table>
						<tr>
							<td>a.</td>
							<td>Hari ini</td>
							<td box-value><?= number_format($data['enam_hi']) ?></td>
							<td width="5%">jam</td>
						</tr>
						<tr>
							<td>b.</td>
							<td>s/d Hari ini</td>
							<td box-value><?= number_format($data['enam_sdhi']) ?></td>
							<td>jam</td>
						</tr>
						<tr>
							<td>c.</td>
							<td>s/d Bulan ini</td>
							<td box-value><?= number_format($data['enam_sdbi']) ?></td>
							<td>jam</td>
						</tr>
					</table>
				</div>
				<div class="box">
					<h4>7. KAPASITAS OLAH</h4>
					<table>
						<tr>
							<td>a.</td>
							<td>Hari ini</td>
							<td box-value><?= number_format($data['tujuh_hi']) ?></td>
							<td width="5%">ton/jam</td>
						</tr>
						<tr>
							<td>b.</td>
							<td>s/d Hari ini</td>
							<td box-value><?= number_format($data['tujuh_sdhi']) ?></td>
							<td>ton/jam</td>
						</tr>
						<tr>
							<td>c.</td>
							<td>s/d Bulan ini</td>
							<td box-value><?= number_format($data['tujuh_sdbi']) ?></td>
							<td>ton/jam</td>
						</tr>
					</table>
				</div>

			</div>
			<div container-box>
				<div class="box">
					<h4>8. INTI SAWIT PRODUKSI</h4>
					<table>
						<tr>
							<td width="5%">a.</td>
							<td>Inti Sawit Hari ini</td>
							<td box-value><?= number_format($data['delapan_hi']) ?></td>
							<td width="5%">kg</td>
						</tr>
						<tr>
							<td></td>
							<td>Inti Sawit s/d Hari ini</td>
							<td box-value><?= number_format($data['delapan_sdhi']) ?></td>
							<td>kg</td>
						</tr>
						<tr>
							<td></td>
							<td>Inti Sawit s/d Bulan ini</td>
							<td box-value><?= number_format($data['delapan_sdbi']) ?></td>
							<td>kg</td>
						</tr>
						<tr>
							<td width="5%">b.</td>
							<td>Rendemen Hari ini</td>
							<td box-value><?= number_format($data['delapan_rendemen_hi'],2) ?></td>
							<td>%</td>
						</tr>
						<tr>
							<td></td>
							<td>Rendemen s/d Hari ini</td>
							<td box-value><?= number_format($data['delapan_rendemen_sdhi'],2) ?></td>
							<td>%</td>
						</tr>
						<tr>
							<td></td>
							<td>Rendemen s/d Bulan ini</td>
							<td box-value><?= number_format($data['delapan_rendemen_sdbi'],2) ?></td>
							<td>%</td>
						</tr>
						<tr>
							<td>c.</td>
							<td>FFA Hari ini</td>
							<td box-value><?= number_format($data['delapan_ffa_hi'],2) ?></td>
							<td>%</td>
						</tr>
						<tr>
							<td>d.</td>
							<td>Kadar Air Hari ini</td>
							<td box-value><?= number_format($data['delapan_kadar_air_hi'],2) ?></td>
							<td>%</td>
						</tr>
						<tr>
							<td>e.</td>
							<td>Kadar Kotoran Hari ini</td>
							<td box-value><?= number_format($data['delapan_kadar_kotoran_hi'],2) ?></td>
							<td>%</td>
						</tr>
					</table>
				</div>
				<div class="box">
					<h4>9. PENGIRIMAN MINYAK SAWIT</h4>
					<table>
						<tr>
							<td>a.</td>
							<td>Hari ini</td>
							<td box-value><?= number_format($data['sembilan_hi']) ?></td>
							<td width="5%">kg</td>
						</tr>
						<tr>
							<td>b.</td>
							<td>s/d Hari ini</td>
							<td box-value><?= number_format($data['sembilan_sdhi']) ?></td>
							<td>kg</td>
						</tr>
						<tr>
							<td>c.</td>
							<td>s/d Bulan ini</td>
							<td box-value><?= number_format($data['sembilan_sdbi']) ?></td>
							<td>kg</td>
						</tr>
					</table>
				</div>
				<div class="box">
					<h4>10. PENGIRIMAN INTI SAWIT</h4>
					<table>
						<tr>
							<td>a.</td>
							<td>Hari ini</td>
							<td box-value><?= number_format($data['sepuluh_hi']) ?></td>
							<td width="5%">kg</td>
						</tr>
						<tr>
							<td>b.</td>
							<td>s/d Hari ini</td>
							<td box-value><?= number_format($data['sepuluh_sdhi']) ?></td>
							<td>kg</td>
						</tr>
						<tr>
							<td>c.</td>
							<td>s/d Bulan ini</td>
							<td box-value><?= number_format($data['sepuluh_sdbi']) ?></td>
							<td>kg</td>
						</tr>
					</table>
				</div>
				<div class="box">
					<h4>11. LOSSES</h4>
					<div>
						<h5>A. OIL LOSES</h5>
						<table>
							<tr>
								<td>.</td>
								<td>PRESS</td>
								<td box-value><?= number_format($data['sebelas_oil_press'],2) ?></td>
								<td width="5%">%</td>
							</tr>
							<tr>
								<td>.</td>
								<td>NUT</td>
								<td box-value><?= number_format($data['sebelas_oil_nut'],2) ?></td>
								<td>%</td>
							</tr>
							<tr>
								<td>.</td>
								<td>E Bunch</td>
								<td box-value><?= number_format($data['sebelas_oil_e_bunch'],2) ?></td>
								<td>%</td>
							</tr>
							<tr>
								<td>.</td>
								<td>Final Effluent</td>
								<td box-value><?= number_format($data['sebelas_oil_final_effluent'],2) ?></td>
								<td>%</td>
							</tr>
							<tr>
								<td>.</td>
								<td>Fruit Loss</td>
								<td box-value><?= number_format($data['sebelas_oil_fruit_loss'],2) ?></td>
								<td>%</td>
							</tr>
							<tr>
								<td>.</td>
								<td>Total</td>
								<td box-value><?= number_format($data['sebelas_oil_total'],2) ?></td>
								<td>%</td>
							</tr>
						</table>
					</div>
					<div>
						<h5>B. KERNEL LOSES</h5>
						<table>
							<tr>
								<td>.</td>
								<td>Fruit Loss</td>
								<td box-value><?= number_format($data['sebelas_kernel_fruit_loss'],2) ?></td>
								<td width="5%">%</td>
							</tr>
							<tr>
								<td>.</td>
								<td>Fibre Cyclone</td>
								<td box-value><?= number_format($data['sebelas_kernel_fibre_cyclone'],2) ?></td>
								<td>%</td>
							</tr>
							<tr>
								<td>.</td>
								<td>LTDS 1</td>
								<td box-value><?= number_format($data['sebelas_kernel_ltds_1'],2) ?></td>
								<td>%</td>
							</tr>
							<tr>
								<td>.</td>
								<td>LTDS 2</td>
								<td box-value><?= number_format($data['sebelas_kernel_ltds_2'],2) ?></td>
								<td>%</td>
							</tr>
							<tr>
								<td>.</td>
								<td>Claybath</td>
								<td box-value><?= number_format($data['sebelas_kernel_claybath'],2) ?></td>
								<td>%</td>
							</tr>
							<tr>
								<td>.</td>
								<td>Total</td>
								<td box-value><?= number_format($data['sebelas_kernel_total'],2) ?></td>
								<td>%</td>
							</tr>
						</table>
					</div>
				</div>
				<div class="box">
					<h3>12. KETERANGAN</h3>
					<p><?= $data['duabelas_ket'] ?></p>
				</div>

				<br><br><br>

				<table>
					<tr>
						<th>Asst Lab.</th>
						<th>KTU</th>
						<th>Mill Manager</th>
					</tr>
				</table>

			</div>
		</div>









		<!-- <div class="d-flex flex-between">
			<div style="width:100%">
				<p><b>Mill :</b> <?= $header['mill'] ?></p>
				<p><b>Tanggal :</b> <?= tgl_indo($header['tanggal']) ?></p>
				<p><b>Total Jam Proses :</b> <?= $header['total_jam_proses'] ?></p>
				<p><b>Total Jumlah Rebusan :</b> <?= $header['total_jumlah_rebusan'] ?></p>
				<p><b>TBS Olah :</b> <?= $header['tbs_olah'] ?></p>	
			</div>
		</div>
		<br>


		<b>Details :</b>
		<table class="table-bg border">
			<thead>
				<tr>
					<th>No</th>
					<th>Shift</th>
					<th>Jam Masuk</th>
					<th>Jam Selesai</th>
					<th>Mandor</th>
					<th>Asisten</th>
					<th>Jam Proses</th>
					<th>Jumlah Rebusan</th>
				</tr>
			</thead>
			<tbody>
				<?php $no = 0 ?>
				<?php foreach ($detail as $key => $val) { ?>
					<?php $no = $no + 1 ?>
					<tr>
						<td center width="5%"><?= $no ?></td>
						<td width="10%"><?= $val['shift'] ?></td>
						<td center width="10%"><?= $val['jam_masuk'] ?></td>
						<td center width="10%"><?= $val['jam_selesai'] ?></td>
						<td width="10%"><?= $val['mandor'] ?></td>
						<td width="10%"><?= $val['asisten'] ?></td>
						<td center width="10%"><?= $val['jam_proses'] ?></td>
						<td center width="10%"><?= $val['jumlah_rebusan'] ?></td>
					</tr>
				<?php } ?>
			</tbody>
			<tfoot></tfoot>
		</table>
		<br>

		

		<b>Details Mesin :</b>
		<table class="table-bg border">
			<thead>
				<tr>
					<th>No</th>
					<th>Mesin</th>
					<th>Jam Masuk</th>
					<th>Jam Selesai</th>
					<th>Jumlah Jam</th>
					<th>Keterangan</th>
				</tr>
			</thead>
			<tbody>
				<?php $no = 0 ?>
				<?php foreach ($mesin as $key => $val) { ?>
					<?php $no = $no + 1 ?>
					<tr>
						<td center width="5%"><?= $no ?></td>
						<td width="10%"><?= $val['mesin'] ?></td>
						<td center width="10%"><?= $val['jam_masuk'] ?></td>
						<td center width="10%"><?= $val['jam_selesai'] ?></td>
						<td center width="10%"><?= $val['jumlah_jam'] ?></td>
						<td center width="10%"><?= $val['keterangan'] ?></td>
					</tr>
				<?php } ?>
			</tbody>
			<tfoot></tfoot>
		</table> -->




		<pre><?php //print_r($detail) ?></pre>

	</body>

	</html>
