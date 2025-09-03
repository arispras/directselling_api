<!DOCTYPEhtml>
	<html>

	<head>

		<?php require '__laporan_style.php' ?>
	</head>

	<body>
		<h1 class="title">REKAPITULASI PERHITUNGAN SUSUT PENGIRIMAN CPO</h1>
		Periode:<?= tgl_indo($hd['periode_mulai']) ?> s/d <?= tgl_indo($hd['periode_sda']) ?>
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
					<th rowspan="2">NO</th>
					<th rowspan="2">NO POLISI</th>
					<th rowspan="2">SUPIR</th>
					<th colspan="2">TANGGAL</th>
					<th colspan="2">GROSS</th>
					<th colspan="2">TARE</th>
					<th colspan="2">NETTO</th>
					<th colspan="7">Perhitungan Potongan Susut</th>

				</tr>
				<tr>
					<th>KIRIM</th>
					<th>TERIMA</th>
					<th>DPA</th>
					<th>CUST</th>
					<th>DPA</th>
					<th>CUST</th>
					<th>DPA</th>
					<th>CUST</th>
					<th>Susut(Kg)</th>
					<th>%Susut</th>
					<th>%Tol</th>
					<th>%Pot</th>
					<th>Rp/Kg</th>
					<th>Pot(Kg)</th>
					<th>Pot(Rp)</th>
				</tr>

			</thead>

			<tbody>
				<?php
				$no = 0;
				$jumlah_rp = 0;
				$jumlah_netto = 0;
				$total_susut = 0;
				$total_potongan=0;
				$potongan_rp=0;
				$total_netto_kirim=0;
				$total_netto_customer=0;
				$total_bruto_kirim=0;
				$total_bruto_customer=0;
				$total_tara_kirim=0;
				$total_tara_customer=0;
				$total_potongan_rp=0;
				$total_selisih=0;
				?>

				<?php foreach ($dt as $key => $val) { ?>

					<?php
					$no = $no + 1;
					$total_netto_customer = $total_netto_customer + ($val['netto_customer']);
					$total_netto_kirim = $total_netto_kirim + ($val['netto_kirim']);
					$total_bruto_customer = $total_bruto_customer + ($val['bruto_customer']);
					$total_bruto_kirim = $total_bruto_kirim + ($val['bruto_kirim']);
					$total_tara_customer = $total_tara_customer + ($val['tara_customer']);
					$total_tara_kirim = $total_tara_kirim + ($val['tara_kirim']);
					$jumlah_rp = $jumlah_rp + ($val['netto_customer'] * $val['harga']);
					$jumlah_netto = $jumlah_netto + ($val['netto_customer']);
					$selisih = $val['netto_customer'] - $val['netto_kirim'];
					$total_selisih=$total_selisih+$selisih;
					$total_susut=$total_susut+$selisih;
					$persen_potongan=0;
					$persen_susut = ($selisih * -1) / $val['netto_kirim'] * 100;
					if ($selisih < 0) {
						
						if ( round($persen_susut,2) > round($hd['toleransi'],2)) {
							$persen_potongan=$persen_susut - $hd['toleransi'];
							$potongan = ($persen_potongan / 100) * $val['netto_kirim'];
							$total_potongan = $total_potongan + $potongan;
							$potongan_rp=$potongan*$hd['harga_susut_per_kg'];
							$total_potongan_rp=$total_potongan_rp+$potongan_rp;
						}else{
							$potongan =0;
							$persen_potongan=0;
							$potongan_rp=0;

						}
					}else{
						$potongan =0;
						$persen_potongan=0;
						$potongan_rp=0;
					}
					?>
					<tr>

						<td center width="2%"><?= $no ?></td>

						<td center width="7%"><?= $val['no_kendaraan']  ?></td>
						<td left width="12%"><?= $val['nama_supir']  ?></td>
						<td center width="7%"><?= tgl_indo_normal($val['tanggal_timbang']) ?></td>
						<td center width="7%"><?= tgl_indo_normal($val['tanggal_terima'])  ?></td>
						<td right width="7%"><?= number_format($val['bruto_kirim'])  ?></td>
						<td right width="7%"><?= number_format($val['bruto_customer'])  ?></td>
						<td right width="7%"><?= number_format($val['tara_kirim'])  ?></td>
						<td right width="7%"><?= number_format($val['tara_customer'])  ?></td>
						<td right width="7%"><?= number_format($val['netto_kirim']) ?></td>
						<td right width="7%"><?= number_format($val['netto_customer']) ?></td>
						<td right width="7%"><?= number_format($selisih) ?></td>
						<td right width="7%"><?= number_format(($persen_susut * -1),2) ?>%</td> 
						<td right width="7%"><?= number_format($hd['toleransi'],2) ?>%</td>
						<td right width="7%"><?= number_format($persen_potongan,2) ?>%</td>
						<td right width="7%"><?= number_format($hd['harga_susut_per_kg']) ?></td>
						<td right width="7%"><?= number_format($potongan) ?></td>
						<td right width="7%"><?= number_format($potongan_rp) ?></td>
					</tr>
				<?php } ?>
				<tr>
					<td colspan="5">Jumlah</td>
					<td right><?= number_format($total_bruto_kirim) ?></td>
					<td right><?= number_format($total_bruto_customer) ?></td>
					<td right><?= number_format($total_tara_kirim) ?></td>
					<td right><?= number_format($total_tara_customer) ?></td>
					<td right><?= number_format($total_netto_kirim) ?></td>
					<td right><?= number_format($total_netto_customer) ?></td>
					<td right><?= number_format($total_selisih) ?></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td right><?= number_format($total_potongan) ?></td>
					<td right><?= number_format($total_potongan_rp) ?></td>
				</tr>
				<!-- <tr><td colspan="4"></td><td>PPN</td>	<td center width="10%"><?= number_format($hd['ppn'] / 100 * $hd['sub_total']) ?></td></tr>
				<tr><td colspan="4"></td><td>Potongan PPH 22</td>	<td center width="10%"><?= number_format($hd['pph'] / 100 * $hd['sub_total']) ?></td></tr>
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
