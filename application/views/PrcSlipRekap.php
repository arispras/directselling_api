<!DOCTYPEhtml>
	<html>

	<head>
	
		<?php require '__laporan_style.php' ?>
		<style>
			* {
				font-size: 10px ;
			}
			thead {display: table-row-group;}
			pagebreak: { avoid: ['tr', 'td'] }
		</style>
	</head>

	<body>



		<h1 class="title">Rekap Penerimaan TBS</h1>
		<h4 class="title"><?= $hd['no_rekap'] ?></h4>
		<table>
            <tr>
                <td width='150px'>Periode</td>
                <td width='10px'>:</td>
                <td><?= tgl_indo($hd['periode_kt_dari']) ?> s/d <?= tgl_indo($hd['periode_kt_sd']) ?></td>
            </tr>
            <tr>
                <td>Supplier</td>
                <td>:</td>
                <td><?= $hd['nama_supplier'] ?></td>
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
					<th rowspan="2">No Polisi</th>
					<th rowspan="2">Driver</th>
					<th rowspan="2">No SPB</th>
					<th rowspan="2">No Tiket</th>
					<th rowspan="2">No SPK</th>
					<th colspan="3">Timbangan</th>
					<th rowspan="2">Sortasi</th>
					<th colspan="1">Netto After</th>
					<th rowspan="2">Harga</th>
					<th rowspan="2">Total</th>

				</tr>
				<tr>
					<th>Bruto</th>
					<th>Tarra</th>
					<th>Netto</th>
					<th>Sortasi</th>
				</tr>
			</thead>

			<tbody>
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
					// if ($no>210){
					// 	continue;
					// }
					?>
					<tr>

						<td center width="2%"><?= $no ?></td>
						<td center width="5%"><?= tgl_indo_normal($val['tanggal'])  ?></td>
						<td center width="5%"><?= $val['no_plat'] ?></td>
						<td center width="5%"><?= $val['nama_supir'] ?></td>
						<td center width="5%"><?= $val['no_spat'] ?></td>
						<td center width="6%"><?= $val['no_tiket'] ?></td>
						<td center width="5%"><?= $hd['no_spk'] ?></td>
						<td center width="3%"><?= number_format($val['berat_isi']) ?></td>
						<td center width="3%"><?= number_format($val['berat_kosong']) ?></td>
						<td center width="3%"><?= number_format($val['berat_bersih']) ?></td>
						<td center width="5%"><?= number_format($val['berat_potongan']) ?></td>
						<td center width="5%"><?= number_format($val['berat_terima']) ?></td>
						<td center width="5%"><?= number_format($val['harga']) ?></td>
						<td center width="5%"><?= number_format($val['berat_terima'] * $val['harga']) ?></td>

					</tr>
				<?php } ?>
				<tr>
					<td center colspan="9">Jumlah</td>
					
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
