<!DOCTYPEhtml>
<html>
	<head>
	
		<?php require '__laporan_style.php' ?>
		<?php 
			if ($tipe_laporan=='pdf') {
				require '__laporan_style.php';
				echo $html='
				<style>
				*{
					font-size: 5 ;
				}
				
				th,
				td {
				padding: 2px 2px;
				}
				</style>';
				}	
		?>
	</head>
	<body>
		<br><br>

		<div style="border: 1px solid black; padding:20px; margin:0% 30%">

			<div style="border: 1px solid black" center>
				<h1 bold>LAPORAN HARIAN PRODUKSI MILL</h1>
				<!-- <h1 bold>KLINIK ANNAJAH</h1> -->
			</div>

			<div style="padding:2% 5%;" center>
				<!-- <div>Hari Selasa, 25 Januari 2002</div> -->
				<div><?= tgl_indo($input['tanggal']) ?></div>
				<div>Jam <?= date('H:i') ?> WITA</div>
			</div>

			<div class="box">
				<table>
					<tr>
						<th left>KEBUN INTI</th>
					</tr>
					<?php foreach($rayon['inti'] as $key=>$val) { ?>
					<?php $total_tbs_inti += $val['total_tbs']; ?>
					<tr>
						<td><?= $val['nama'] ?></td>
						<td right>=</td>
						<td right width="30%"><?= number_format($val['total_tbs']) ?> kg</td>
					</tr>
					<?php } ?>
					<tr>
						<td colspan="3" style="background-color:black; padding:1px;"></td>
					</tr>
					<tr>
						<th left>H.ini</th>
						<td right>=</td>
						<td right><?= number_format($total_tbs_inti) ?> kg</td>
					</tr>
					<tr>
						<td>s/d Hi</td>
						<td right>=</td>
						<td right><?= number_format($inti['sdhi']) ?> kg</td>
					</tr>
					<tr>
						<td>s/d B.ini</td>
						<td right>=</td>
						<td right><?= number_format($inti['sdbi']) ?> kg</td>
					</tr>
				</table>
			</div>


			<div class="box">
				<table>
					<tr>
						<th left>KEBUN PLASMA</th>
					</tr>
					<?php foreach($rayon['plasma'] as $key=>$val) { ?>
					<?php $total_tbs_plasma += $val['total_tbs']; ?>
					<tr>
						<td><?= $val['nama'] ?></td>
						<td right>=</td>
						<td right width="30%"><?= number_format($val['total_tbs']) ?> kg</td>
					</tr>
					<?php } ?>
					<tr>
						<td colspan="3" style="background-color:black; padding:1px;"></td>
					</tr>
					<tr>
						<th left>H.ini</th>
						<td right>=</td>
						<td right><?= number_format($total_tbs_plasma) ?> kg</td>
					</tr>
					<tr>
						<td>s/d Hi</td>
						<td right>=</td>
						<td right><?= number_format($plasma['sdhi']) ?> kg</td>
					</tr>
					<tr>
						<td>s/d B.ini</td>
						<td right>=</td>
						<td right><?= number_format($plasma['sdbi']) ?> kg</td>
					</tr>
				</table>
			</div>


			<div class="box">
				<table>
					<tr>
						<th left>TOTAL DPA</th>
					</tr>
					<tr>
						<th left>H.ini</th>
						<td right >=</td>
						<td right><?= number_format($sum_tbs_hi) ?> kg</td>
					</tr>
					<tr>
						<td>s/d Hi</td>
						<td right>=</td>
						<td right><?= number_format($sum_tbs_sdhi) ?> kg</td>
					</tr>
					<tr>
						<td>s/d B.ini</td>
						<td right>=</td>
						<td right><?= number_format($sum_tbs_sdbi) ?> kg</td>
					</tr>
				</table>
			</div>



			<div class="box">
				<table>
					<tr>
						<th left>PIHAK KE 3 Ex. PT/CV/Kop</th>
					</tr>
					<?php foreach ($supplier as $key=>$val) { ?>
					<tr>
						<td><?= $val['nama_supplier'] ?></td>
						<td right>=</td>
						<td right width="30%"><?= number_format($val['berat_bersih']) ?> kg</td>
					</tr>
					<?php } ?>
					<tr>
						<td colspan="3" style="background-color:black; padding:1px;"></td>
					</tr>
					<tr>
						<th left>H.ini</th>
						<td right>=</td>
						<td right><?= number_format($supplier_hi) ?> kg</td>
					</tr>
					<tr>
						<td>s/d Hi</td>
						<td right>=</td>
						<td right><?= number_format($supplier_sdhi) ?> kg</td>
					</tr>
					<tr>
						<td>s/d B.ini</td>
						<td right>=</td>
						<td right><?= number_format($supplier_sdbi) ?> kg</td>
					</tr>
				</table>
			</div>



			<div class="box">
				<table>
					<tr>
						<th left>Pot Hi</th>
						<td right>=</td>
						<td right width="30%"><?= number_format($supplier_potongan) ?> kg</td>
					</tr>
					<tr>
						<td>%</td>
						<td right>=</td>
						<td right><?= number_format( (($supplier_potongan / $supplier_hi)*100),2 ) ?> %</td>
					</tr>
				</table>
			</div>



			<div class="box">
				<table>
					<tr>
						<th left>TOTAL PENERIMAAN TBS</th>
					</tr>
					<tr>
						<th left>Total H.ini</th>
						<td right>=</td>
						<td right width="30%"><?= number_format($tbs_hi) ?> kg</td>
					</tr>
					<tr>
						<td>Total s/d Hi</td>
						<td right>=</td>
						<td right><?= number_format($tbs_sdhi) ?> kg</td>
					</tr>
					<tr>
						<td>Total s/d B.ini</td>
						<td right>=</td>
						<td right><?= number_format($tbs_sdbi) ?> kg</td>
					</tr>
				</table>
			</div>		


			
		</div>
		
		


		<pre><?php //print_r($rayon) ?></pre>

	</body>
</html>
