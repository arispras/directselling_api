<!DOCTYPEhtml>
<html>
	<head>
	
		<?php require '__laporan_style.php' ?>
	</head>
	<body>

		<pre>
			<?php print_r($PksSjpp) ?>
		</pre>
		
		<div class="d-flex flex-between">
			
			<table class="" style="width:30%">
				<tr>
					<td style="width:30%">No Kontrak</td>
					<th>:</th>
					<td><?= $PksSjpp[0]->no_kontrak ?></td>
				</tr>
				<tr>
					<td>No IP</td>
					<th>:</td>
					<td></td>
				</tr>
				<tr>
					<td>Priode</td>
					<th>:</th>
					<td>tanggal_from sd tanggal_to</td>
				</tr>
				<tr>
					<td>Customer</td>
					<th>:</th>
					<td><?= $PksSjpp[0]->customer ?></td>
				</tr>
				<tr>
					<td>Nama Barang</td>
					<th>:</th>
					<td><?= $PksSjpp[0]->nama_produk ?></td>
				</tr>
			</table>

			<table class="border" style="width:30%">
				<tr>
					<td style="width:50%">Jumlah CPO</td>
					<th>:</th>
					<td right></td>
				</tr>
				<tr>
					<td>Dikirim</td>
					<th>:</td>
					<td right></td>
				</tr>
				<tr>
					<td>Sisa Pengiriman</td>
					<th>:</th>
					<td right></td>
				</tr>
				<tr>
					<td>Diterima</td>
					<th>:</th>
					<td right></td>
				</tr>
				<tr>
					<td>Sisa DO</td>
					<th>:</th>
					<td right></td>
				</tr>
			</table>

		</div>



		
		<br><br>



		
		<table class="table-bg border">
			<thead>
				<tr>
					<!-- <th style="width:10%">No.</th> -->
					<th rowspan="3" style="width:4%">No</th>
					<th rowspan="3" style="width:7%">No Polisi</th>
					<th rowspan="3" style="width:10%">Nama Supir</th>
					<th rowspan="3" style="width:5%">Tanggal kirim</th>
					
					<th colspan="8" style="width:30%">Pengiriman</th>
					
					<th rowspan="3" style="width:5%">Tanggal Terima</th>
					
					<th colspan="4" style="width:10%">Penerimaan</th>
					<th colspan="4" style="width:10%">Variance</th>
				</tr>
				<tr>
					<th colspan="5">Kualitas</th>
					<th colspan="3">Tonase</th>

					<th>Kualitas</th>
					<th colspan="3">Tonase</th>
					
					<th>Kualitas</th>
					<th colspan="3">Tonase</th>
				</tr>
				<tr>
					<th>Ffa</th>
					<th>M.I</th>
					<th>Moist</th>
					<th>dirt</th>
					<th>Dobi</th>
					
					<th>Gross</th>
					<th>Tare</th>
					<th>Netto</th>

					<th>Ffa</th>
					<th>Gross</th>
					<th>Tare</th>
					<th>Netto</th>

					<th>Ffa</th>
					<th>Gross</th>
					<th>Tare</th>
					<th>Netto</th>
				</tr>
			</thead>
			<tbody>
				<?php //for($x=0; $x<107; $x++) { ?>
				<?php
				$ffa_variance=0;
				$gross_variance=0;
				$tare_variance=0;
				$netto_variance=0;
				$gross_count_variance=0;
				$tare_count_variance=0;
				$netto_count_variance=0;

				?>
				<?php foreach ($PksSjpp as $no=>$row) { ?>
				<?php $ffa_variance = $row->ffa_kirim - $row->ffa_terima; ?>
				<?php $gross_variance = $row->gross_kirim - $row->gross_terima; ?>
				<?php $tare_variance = $row->tare_kirim - $row->tare_terima; ?>
				<?php $netto_variance = $row->netto_kirim - $row->netto_terima; ?>
				<?php $gross_count_variance += $row->gross_kirim ?>
				<?php $tare_count_variance += $row->tare_kirim ?>
				<?php $netto_count_variance += $row->netto_kirim ?>


				<tr>
					<td center><?= $no ?></td>
					<td><?= $row->no_polisi ?></td>
					<td><?= $row->nama_supir ?></td>
					<td center><?= tgl_indo($row->tanggal_kirim) ?></td>

					<td right><?= $row->ffa_kirim ?></td>
					<td right><?= $row->mi_kirim ?></td>
					<td right><?= $row->moist_kirim ?></td>
					<td right><?= $row->dirt_kirim ?></td>
					<td right><?= $row->dobi_kirim ?></td>
					
					<td right><?= $row->gross_kirim ?></td>
					<td right><?= $row->tare_kirim ?></td>
					<td right><?= $row->netto_kirim ?></td>
					
					<td center><?= tgl_indo($row->tanggal_terima) ?></td>
					
					<td right><?= $row->ffa_terima ?></td>
					<td right><?= $row->gross_terima ?></td>
					<td right><?= $row->tare_terima ?></td>
					<td right><?= $row->netto_terima ?></td>
					
					<td right><?= $ffa_variance ?></td>
					<td right><?= $gross_variance ?></td>
					<td right><?= $tare_variance ?></td>
					<td right><?= $netto_variance ?></td>
				</tr>
				<!-- <tr>
					<td center><?= $no ?></td>
					<td>KT 8991 RN</td>
					<td>Afriadi</td>
					<td center>13/11/2021</td>

					<td right>3.74</td>
					<td right>0.21</td>
					<td right>0.19</td>
					<td right>0.02</td>
					<td right>3.196</td>
					
					<td right>22,480</td>
					<td right>8,400</td>
					<td right>14,080</td>
					
					<td center>14/11/2021</td>
					
					<td right>3.79</td>
					<td right>22,470</td>
					<td right>8,360</td>
					<td right>14,110</td>
					
					<td right>0.05</td>
					<td right>-10</td>
					<td right>-40</td>
					<td right>30</td>
				</tr> -->
				<?php } ?>
			</tbody>

			<tfoot>

				<th colspan="4">Total</th>
				
				<th right><?= round($count->ffa, 2) ?></th>
				<th right><?= round($count->mi, 2) ?></th>
				<th right><?= round($count->moisture, 2) ?></th>
				<th right><?= round($count->dirt, 2) ?></th>
				<th right><?= $count->dobi ?></th>
				<th right><?= $count->bruto_kirim ?></th>
				<th right><?= $count->tara_kirim ?></th>
				<th right><?= $count->netto_kirim ?></th>
				
				<th></th>
				
				<th right><?= round($count->ffa_customer, 2) ?></th>
				<th right><?= $count->bruto_customer ?></th>
				<th right><?= $count->tara_customer ?></th>
				<th right><?= $count->netto_customer ?></th>

				<th right></th>
				<th right><?= $gross_count_variance ?></th>
				<th right><?= $tare_count_variance ?></th>
				<th right><?= $netto_count_variance ?></th>
			
			</tfoot>

		</table>


	</body>
</html>
