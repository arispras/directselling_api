<!DOCTYPEhtml>
	<html>

	<head>
		
		<?php require '__laporan_style.php' ?>
	</head>

	<body>



		<h1 class="title">BERITA ACARA PENYELESAIAN PEKERJAAN</h1>
		<br><br>
		<!-- <h4 class="title"><?= $hd['no_invoice'] ?></h4> -->
		<table>
           
            <tr>
                <td width="20%">Kontraktor</td>
                <td>:</td>
                <td width="50%"><?= $hd['nama_supplier'] ?></td>
				
                <td width="20%">BAPP</td>
                <td>:</td>
                <td width="50%"><?= $hd['no_rekap'] ?> </td>
            </tr>
            </tr>
            <tr>
                <td width="20%">No Perjanjian Kerja</td>
                <td>:</td>
                <td><?= $hd['no_spk'] ?></td>
				<td >Tgl BAPP</td>
                <td>:</td>
                <td><?= $hd['tanggal_rekap'] ?> </td>
            </tr>
			<tr>
                <td width="20%">Jenis Pekerjaan</td>
                <td>:</td>
                <td>Pembelian TBS Luar</td>
				<td >Lokasi</td>
                <td>:</td>
                <td><?= strtoupper(get_company()['nama']) ?></td>
            </tr>
        </table>
	
		<table class="table-bg border">
			<thead>
				<tr>
					<th rowspan="2">No</th>
					<th rowspan="2">Jenis Pekerjaan</th>
					<th colspan="2">Hasil Kerja</th>
					<th rowspan="2">Harga Satuan</th>
					<th rowspan="2">Jumlah</th>

				</tr>
				<tr>
					<th >Sat</th>
					<th>Jumlah</th>
				</tr>
			</thead>

			<tbody>
				<?php $no = 0 ?>
				<?php $jumlah = 0 ?>
				
				<?php foreach ($dt as $key => $val) { ?>

					<?php
					$no = $no + 1;
					$jumlah =$jumlah + ($val['qty']*$val['harga']);

					?>
					<tr>

						<td center width="2%"><?= $no ?></td>
						<td center width="25%"><?= $val['pekerjaan']  ?></td>
						<td center width="5%"><?= $val['uom'] ?></td>
						<td right width="10%"><?= number_format($val['qty']) ?></td>
						<td right width="10%"><?= number_format($val['harga']) ?></td>
						<td right width="10%"><?= number_format($val['harga']*$val['qty'] ) ?></td>

					</tr>
				<?php } ?>
				<tr><td colspan="4"></td><td>Jumlah Tagihan(Kotor)</td>	<td right width="10%"><?= number_format($hd['sub_total']) ?></td></tr>
				<tr><td colspan="4"></td><td>PPN</td>	<td right width="10%"><?= number_format(floor($hd['ppn']/100*$hd['sub_total'])) ?></td></tr>
				<tr><td colspan="4"></td><td>Potongan PPh 22</td>	<td style="color:red" right width="10%"><?= "(". number_format(floor($hd['pph']/100*$hd['sub_total'])).")" ?></td></tr>
				<tr><td colspan="4"></td><td>Jumlah Tagihan Bersih</td>	<td right width="10%"><?= number_format($hd['total_tagihan']) ?></td></tr>
				
				
			</tbody>

			<tfoot>
				
			
			</tfoot>
		</table>
		<table><tr><td>Wakil perusahaan dan kontraktor secara bersama-sama telah melakukan pemeriksaan dan penilaian BAPP ini terhadap hasil pekerjaan di lapangan, serta kedua-duanya saling menyetujui dengan se-pengetahuan atasan masing-masing perusahaan serta tanpa ada unsur paksaan.</td></tr></table>
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
					<p>Diminta Oleh</p>
					<br><br><br><br>
					<div>
						<div>&nbsp;</div>
						<div><?= $hd['nama_supplier'] ?></div>
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
						<div>Eduward Sianturi</div>
						<div>Mill Manager</div>
					</div>
				</td>
				<td center>
					<p>Disetujui Oleh</p>
					<br><br><br><br>
					<div>
						<div>Arief Prasetiyono</div>
						<div>Direktur Operasional</div>
					</div>
				</td>
			</tr>
		</table>

		<pre><?php //print_r($hdan) 
				?></pre>

	</body>

	</html>
