<!DOCTYPEhtml>
	<html>

	<head>
	
		<?php require '__laporan_style.php' ?>
	</head>

	<body>



		<h1 class="title">Rekap Pengangkutan Barang</h1>
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
					<th rowspan="2">No Kendaraan</th>
					<th rowspan="2">Nama Supir</th>
					<th rowspan="2">Pekerjaan</th>
					<th colspan="3">Timbangan</th>
					<th rowspan="2">Rupiah/Kg</th>
					<th rowspan="2">Rupiah</th>

				</tr>
				<tr>
					
					<th>Bruto</th>
					<th>Tarra</th>
					<th>Netto</th>
				</tr>
			</thead>

			<tbody></tbody>
				<?php $no = 0 ?>
				<?php $netto_total = 0;  ?>
				<?php $bruto_total = 0 ?>
				<?php $tara_total = 0 ?>
				<?php $total = 0 ?>

				<?php foreach ($dt as $key => $val) { ?>

					<?php
					$no = $no + 1;
					$net=0;
					$tara=0;
					$bruto=0;
					$jum=0;
					if ($hd['sumber_timbangan']=='int'){
						$tara =  ($val['tara_kirim']);
						$bruto =  ($val['bruto_kirim']);
						$net =  ($val['netto_kirim']);
						
						
					}else{
						$tara =  ($val['tara_customer']);
						$bruto =  ($val['bruto_customer']);
						$net=   ($val['netto_customer']);
						
					}
					$netto_total = $netto_total + ($net);
					$bruto_total = $bruto_total + ($bruto);
					$tara_total = $tara_total + ($tara);
					$jum=$net*$val['harga'];
					$total = $total + $jum;

					?>
					<tr>

						<td center width="2%"><?= $no ?></td>
						<td center width="7%"><?= tgl_indo($val['tanggal_timbang'])  ?></td>
						<td center width="5%"><?= $val['no_kendaraan'] ?></td>
						<td left width="5%"><?= ($val['nama_supir']) ?></td>
						<td center width="5%">Angkut &nbsp;<?= $hd['nama_item'] ?>&nbsp; DPA Mill</td>
						<td center width="7%"><?= number_format($bruto) ?></td>
						<td center width="7%"><?= number_format($tara) ?></td>
	 					<td center width="5%"><?= number_format($net) ?></td>
						<td center width="5%"><?= number_format($val['harga']) ?></td>
						<td center width="7%"><?= number_format($jum) ?></td>

					</tr>
				<?php } ?>
				<tr>
					<th center colspan="5">Jumlah</th>
					
					<td center ><?= number_format($bruto_total) ?></td>
					<td center ><?= number_format($tara_total) ?></td>
					<td center ><?= number_format($netto_total) ?></td>
					<td center ></td>
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
