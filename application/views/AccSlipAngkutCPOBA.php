<!DOCTYPEhtml>
	<html>

	<head>
		
		<?php require '__laporan_style.php' ?>
	</head>

	<body>



		<h1 class="title">REKAPITULASI PEMBAYARAN ANGKUTAN  <?= ($hd['nama_produk']) ?></h1>
		Periode:<?=  tgl_indo($hd['periode_mulai']) ?> s/d <?= tgl_indo($hd['periode_sda']) ?>
		<br><br>
		<!-- <table width="5%">
           
            <tr>
                <td >Periode</td>
                <td>:</td>
                <td ><?= $hd['periode_mulai'] ?></td>
				<td>s/d</td>
                <td ><?= $hd['periode_sd'] ?></td>
                
            </tr>
            
        </table>  -->
			<!-- <table>
           
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
                <td>Angkut CPO</td>
				<td >Lokasi</td>
                <td>:</td>
                <td>PT DINAMIKA PRIMA ARTHA</td>
            </tr>
        </table> -->
	
		<table class="table-bg border">
			<thead>
				<tr>
					<th rowspan="1">No</th>
					<th rowspan="1">Tanggal</th>
					<th rowspan="1">No Kendaraan</th>
					<th rowspan="1">Supir</th>
					<th rowspan="1">Pekerjaan</th>
					<th colspan="1">Bruto</th>
					<th rowspan="1">Tarra</th>
					<th rowspan="1">Netto</th>
					<th rowspan="1">Rupiah/Kg</th>
					<th rowspan="1">Jumlah(Rp)</th>
					<!-- <th rowspan="1">Rp Setelah PPH 2%</th> -->

				</tr>
				
			</thead>

			<tbody>
				<?php $no = 0; $jumlah_rp = 0; $jumlah_netto = 0;
					
				
				
				?>
					
				<?php foreach ($dt as $key => $val) { ?>

					<?php
					$no = $no + 1;
					$netto=0;$bruto=0;$tara=0;
					$tgl;
					if($hd['sumber_timbangan']=='ext'){
						$netto=$val['netto_customer'];
						$bruto=$val['bruto_customer'];
						$tara=$val['tara_customer'];
						$tgl=$val['tanggal_terima'];
					}else{
						$netto=$val['netto_kirim'];
						$bruto=$val['bruto_kirim'];
						$tara=$val['tara_kirim'];
						$tgl=$val['tanggal_timbang'];

					}
					$jumlah_rp =$jumlah_rp + ($netto*$val['harga']);
					$jumlah_netto =$jumlah_netto + ($netto);

					?>
					<tr>

						<td center width="2%"><?= $no ?></td>
						<td left width="10%"><?= tgl_indo($tgl)  ?></td>
						<td left width="10%"><?= $val['no_kendaraan']  ?></td>
						<td left width="10%"><?= $val['nama_supir']  ?></td>
						<!-- <td left width="10%"><?= $val['pekerjaan']  ?></td> -->
						<td left width="10%">Angkut <?= $hd['nama_produk']  ?> Periode: <br><?= tgl_indo($tgl)  ?></td>
						<td right width="10%"><?= number_format($bruto)  ?></td>
						<td right width="10%"><?= number_format($tara)  ?></td>
						<td right width="10%"><?= number_format($netto) ?></td>
						<td right width="10%"><?= number_format($val['harga']) ?></td>
						<td right width="10%"><?= number_format($val['harga']*$netto) ?></td>
						<!-- <td center width="10%"><?= number_format($val['harga']*$val['qty'] ) ?></td> -->

					</tr>
				<?php } ?>
				<tr><td colspan="7">Jumlah</td><td right><?= number_format($jumlah_netto) ?></td>	<td></td><td right><?= number_format($jumlah_rp) ?></td></tr>
				<!-- <tr><td colspan="4"></td><td>PPN</td>	<td center width="10%"><?= number_format($hd['ppn']/100*$hd['sub_total']) ?></td></tr>
				<tr><td colspan="4"></td><td>Potongan PPH 22</td>	<td center width="10%"><?= number_format($hd['pph']/100*$hd['sub_total']) ?></td></tr>
				<tr><td colspan="4"></td><td>Jumlah Tagihan Bersih</td>	<td center width="10%"><?= number_format($hd['total_tagihan']) ?></td></tr>
				 -->
				
			</tbody>

			<tfoot>
				
			
			</tfoot>
		</table>
		<!-- <table><tr><td>Wakil perusahaan dan kontraktor secara bersama-sama telah melakukan pemeriksaan dan penilaian BAPP ini terhadap hasil pekerjaan di lapangan, serta kedua-duanya saling menyetujui dengan se-pengetahuan atasan masing-masing perusahaan serta tanpa ada unsur paksaan.</td></tr></table> -->
		<br><br>
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
