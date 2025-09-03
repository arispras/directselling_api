<!DOCTYPEhtml>
	<html>

	<head>
	
		<?php require '__laporan_style.php' ?>
	</head>

	<body>



		<h1 class="title">Rekap Pengiriman</h1>
		<h4 class="title"><?= $hd['no_rekap'] ?></h4>
		<table>
            <tr>
                <td width='150px'>Periode</td>
                <td width='10px'>:</td>
                <td><?= tgl_indo($hd['periode_kt_dari']) ?> s/d <?= tgl_indo($hd['periode_kt_sd']) ?></td>
            </tr>
            <tr>
                <td>Customer</td>
                <td>:</td>
                <td><?= $hd['nama_customer'] ?></td>
            </tr>
            <!-- <tr>
                <td>Periode Penagihan</td>
                <td>:</td>
                <td><?= $hd['no_invoice'] ?></td>
            </tr> -->
        </table>

        <br><br>


		<table class="table-bg border">
			<thead>
				<tr>
					<th rowspan="2">No</th>
					<th rowspan="2">Tanggal</th>
					<th rowspan="2">No SPK</th>
					<th colspan="2">Periode KT dari</th>
					<th rowspan="2">Item</th>
					<th colspan="3">Timbangan</th>
					<th rowspan="2">Harga</th>
					<th rowspan="2">Total</th>

				</tr>
				<tr>
					<th>Dari</th>
					<th>S/d</th>
					<th>Bruto</th>
					<th>Tarra</th>
					<th>Netto</th>
				</tr>
			</thead>

			<tbody></tbody>
				<?php $no = 0 ?>
				<?php $netto = 0 ?>
				<?php $potongan = 0 ?>
				<?php $terima = 0 ?>
				<?php $total = 0 ?>

				<?php foreach ($dt as $key => $val) { ?>

					<?php
					$no = $no + 1;
					$netto = $netto + ($val['berat_bersih']);
					$potongan = $potongan + ($val['berat_potongan']);
					$terima = $terima + ($val['berat_terima']);
					$total = $total + ($val['berat_terima'] * $val['harga']);

					?>
					<tr>

						<td center width="2%"><?= $no ?></td>
						<td center width="7%"><?= $val['tanggal']  ?></td>
						<td center width="5%"><?= $hd['no_spk'] ?></td>
						<td center width="5%"><?= tgl_indo($hd['periode_kt_dari']) ?></td>
						<td center width="5%"><?= tgl_indo($hd['periode_kt_sd']) ?></td>
						<td center width="5%"><?= $hd['nama_item'] ?></td>
						<td center width="7%"><?= number_format($val['berat_isi']) ?></td>
						<td center width="7%"><?= number_format($val['berat_kosong']) ?></td>
	 					<td center width="5%"><?= number_format($val['berat_bersih']) ?></td>
						<td center width="5%"><?= number_format($val['harga']) ?></td>
						<td center width="7%"><?= number_format($val['berat_terima'] * $val['harga']) ?></td>

					</tr>
				<?php } ?>
				<tr>
					<th center colspan="6">Jumlah</th>
					
					<td center ><?= number_format($netto) ?></td>
					<td center ><?= number_format($potongan) ?></td>
					<td center ><?= number_format($terima) ?></td>
					<td center></td>
					<td center ><?= number_format($total) ?></td>
				</tr>

			</tbody>

			<tfoot>


			</tfoot>
		</table>
		<!-- <table><tr><td>Terbilang:#<?= $hd['terbilang'] ?>&nbsp; Rupiah#</td></tr></table> -->
		<table class="table-bg border">
			<tr>
				
				<td center>
					<p>Dibuat Oleh</p>
					<br><br><br><br>
					<div>
						<div>Yearni Rosa Uli Sihombing</div>
						<div>Adm FFB Purchase</div>
					</div>
				</td>
				
				<td center>
					<p>Diperiksa Oleh</p>
					<br><br><br><br>
					<div>
						<div>Andry</div>
						<div>Asst. FFB Purchase</div>
					</div>
				</td>
				<td center>
					<p>Diperiksa Oleh</p>
					<br><br><br><br>
					<div>
						<div>Kasuda Annas S</div>
						<div>KTU Mill</div>
					</div>
				</td>
				
				<td center>
					<p>Diketahui Oleh</p>
					<br><br><br><br>
					<div>
						<div>&nbsp;</div>
						<div><?= $hd['nama_supplier'] ?></div>
					</div>
				</td>
				<td center>
					<p>Disetujui Oleh</p>
					<br><br><br><br>
					<div>
						<div>Eduward Sianturi</div>
						<div>Mill Manager</div>
					</div>
				</td>
				
			</tr>
		</table>

		<pre><?php //print_r($hdan) 
				?></pre>

	</body>

	</html>
