<!DOCTYPEhtml>
	<html>

	<head>
		
		<style>
			* {
				font-size: 10px ;
			}
			/* ROOT */
			body {
				background-color: white;
				font-family: Helvetica, arial, sans-serif;
			}
			body * {
				font-size: 1vw;
				border-spacing: 0;
			}
			h1.title {
				margin-bottom: 0px;
				line-height: 30px;
				text-align: center;
			}
			h3.title {
				margin-bottom: 0px;
				line-height: 30px;
			}
			h4.title {
				text-align: center;
				font-size: 11px
				
			}

			/* -------------TABLE--------------------------- */
			.table-bg th,
			.table-bg td {
				font-size: 1em !important;
			}
			table {
				width: 100%;
			}
			table.border {
				/* border: 0.5px solid rgba(0,0,0,0.4); */
			}
			.table-bg th,
			.table-bg td {
				font-size: 0.8em;
			}
			.table-bg th {
				color: black;	
				/* background: rgba(50,50,50,0.1); */
				text-align: center;
				font-weight: bolder;
				/* text-transform: uppercase; */
			}
			.table-bg th,
			.table-bg td {
				border-top: 0.5px solid rgba(0,0,0,0.4);
				padding: 6px 8px;
			}
			.no_table td{
				padding: 3px 2px;
			}

			@page { margin: 110px 35px; }
    		.header { position: fixed; top: -80px; left: 0px; right: 0px; height: 50px; }
			.ket {
			border-collapse: collapse;
			width: 100%;
			}
			.ket_td {  
			/* border: 1px solid #ddd; */
			text-align: left;
			padding: 2px 2px;
			}

						/* HELPER */
			[d], [d] * { border: 1px solid red; }
			[dd] { border: 1px solid blue; }
			[center] { text-align: center !important; }
			[left] { text-align: left !important; }
			[right] { text-align: right !important; }
			[bold] { font-weight: bold !important; }
			.d-flex { display:flex; }
			.flex-between { justify-content: space-between; }
			.flex-nowrap { flex-wrap: nowrap; }
			.flex-wrap { flex-wrap: wrap; }
			.pos-fixed { position: fixed; top:0; width:100%; }


		</style>
	</head>

	<body>

		<?php
		//$context = stream_context_create(array('https' => array('header'=>'Connection: close\r\n')));
		//$imgLogo=file_get_contents(('./logo_perusahaan.png'),false,$context);
		// $curl = curl_init('http://localhost/plantationlive-api/logo_perusahaan.png');
		// curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);


		// $html = curl_exec($curl);

		// if (curl_error($curl)) {
		// 	die(curl_error($curl));
		// }
		// $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		// curl_close($curl);

		$sub_total = $header['biaya_kirim']+$header['sub_total'] - $header['diskon'];
		$grand_total = $header['grand_total'];
		$ppn = $header['ppn'];
		$pph = $header['pph'];
		$ppbkb = $header['ppbkb'];
		$ppn_total = 0;
		$pph_total = 0;
		$ppbkb_total = 0;

		$ppn_total = ($ppn / 100) * $sub_total;
		$pph_total = ($pph / 100) * $sub_total;

		$pbbkb_total = ($ppbkb / 100) * $sub_total;
		?>

		<table class="header" border="0">
			<tr>
				<td width="12%" style="padding:0 0px">
					<table>
						<tr>
					
							<!-- <td center>
								<img src="data:image/png;base64,
								<?=  base64_encode($html) ?>" 
							height="80px" width="80px">
						   </td> -->
						   <td center>
								<img src="data:image/png;base64,
							  <?=  base64_encode(file_get_contents('./logo_perusahaan.png')) ?>" 
							height="80px" width="80px">
						   </td>
						</tr>
						
					</table>
				</td>
				<td width="25%" style="padding:0 0px">
					
					<table>
						<tr>
							<td bold style="font-size:13px"><?= strtoupper(get_company()['nama']) ?></td>
						</tr>
						<tr>
							<td>Gd. Cyber Lt.11, Jl. Kuningan Barat No. 8</td>
						</tr>
						<tr>
							<td>Mampang Prapatan - Jakarta Selatan</td>
						</tr>
						<tr>
							<td>Telp. (021) 5269588</td>
						</tr>
						<tr>
							<td>&nbsp;</td>
						</tr>
						
					</table>
				</td>
				<td width="23%" style="padding:0 10px">
					
					<table>
						<tr>
							<td>&nbsp;</td>
						</tr>
					</table>
				</td>
				<td width="33%" style="padding:0 0px">
					<table >
						<tr>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<td left bold width="13%">PO No</td>
							<td  width="4%">:</td>
							<td left width="70%"><?= ($header['revisi_ke']>0)?($header['no_po'].'/rev.'. $header['revisi_ke']): $header['no_po'] ?></td>
						</tr>
						
						<tr>
							<td left bold style="padding:3px 0px">PR No <br> </td>
							<td>:</td>
							<td left><?= $header['no_pp'] ?></td>
						</tr>
						<tr>
							<td left bold>Lokasi <br> </td>
							<td>:</td>
							<td left><?= $header['lokasi'] ?></td>
						</tr>
						<tr>
							<td left bold>Tanggal <br> </td>
							<td>:</td>
							<td left><?= tgl_indo($header['tanggal']) ?></td>
						</tr>
						
						<!-- <tr>
							<td left bold>#Revisi <br> </td>
							<td>:</td>
							<td left><?= ($header['revisi_ke']==0)?'-':$header['revisi_ke'] ?></td>
						</tr> -->
					</table>
				</td>
			</tr>
			<table>
				<tr>
					<td><h1 center style="font-size:16px"  >PURCHASE ORDER</h1></td>
				</tr>
			</table>
		</table>
		
		<table border="0">
			<tr>
				<td width="30%" style="padding:0 0px">
					<table>
						<tr>
							<td bold >Supplier :</td>
						</tr>
						<tr>
							<td left bold> <i><?= $header['nama_supplier'] ?></i> </td>
						</tr>
						<tr>
							<td left><?= $header['alamat_supplier'] ?></td>
						</tr>
						<tr>
							<td left>Phone : <?= $header['no_hp_supplier'] ?></td>
						</tr>
						<tr>
							<td left>PIC : <?= $header['contact_person_supplier'] ?></td>
						</tr>
					</table>
				</td>
				<td width="28%" style="padding:0 10px">
					
					<table>
						<tr>
							<td>&nbsp;</td>
						</tr>
					</table>
				</td>
				<td width="33%" style="padding:0 0px">
					<table>
						<tr>
							<td bold >Procurement Office :</td>
						</tr>
						<tr>
							<td left bold> <i>PT. Annajah Technology Indonesia</i> </td>
						</tr>
						<tr>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<td>&nbsp;</td>
						</tr>
						
					</table>
				</td>
			</tr>
		</table>
		<br><br>

		<table class="table-bg border">
			<thead>
				<tr>
					<th >No</th>
					<th left>Kode Item</th>
					<th left>Nama Item Deskripsi</th>
					<th left>No Part</th>
					<!-- <th left> Catatan</th> -->
					<th>Qty</th>
					<th>UOM</th>
					<th right>Harga</th>
					<th right>Total</th>
				</tr>
			</thead>
			<tbody>
				<?php $no = 0 ?>
				<?php foreach ($detail as $key => $val) { ?>
					<?php $no = $no + 1;
					?>

					<tr>
						<td center width="2%"><?= $no ?></td>
						<td left width="5%"><?= $val['kode_barang'] ?> </td>
						<td left width="15%"><?= $val['nama_barang'] ?> <br> <i><?= $val['ket'] ?></i> </td>
						<td width="7%"><?= $val['no_plat'] ?></td>
						<!-- <td left width="12%"><?= $val['ket'] ?></td> -->
						<td center width="3%"><?= number_format($val['qty'],2) ?></td>
						<td center width="3%"><?= $val['uom'] ?></td>
						<td right width="6%"> <?= number_format($val['harga']) ?></td>
						<td right width="7%"> <?= number_format($val['total']) ?></td>
					</tr>
				<?php } ?>
				<tr>
					<td colspan=8> </td>
				</tr>
			</tbody>
		</table>

		<table class="no_table">
			<tbody>
				<tr>
					<td left style="width:60%" rowspan=8 >
						<p left bold>Catatan :</p>
						<p left>  <?= $header['catatan'] ?> </p>
					</td>
					<td right bold style="width:23%">Sub Total</td>
					<td right  style="width:17%">Rp. <?= number_format($header['sub_total']) ?></td>
					</tr>
					<tr>
						<td right bold>Biaya Kirim</td>
						<td right>Rp. <?= number_format($header['biaya_kirim']) ?></td>
					</tr>
					<tr>
						<td right bold>Disc</td>
						<td right>Rp. <?= number_format($header['diskon']) ?></td>
					</tr>
				
			
					<tr>
						<td right bold>PPBKB (<?= $header['ppbkb'] ?>%)</td>
						<td right>Rp. <?= number_format($pbbkb_total) ?></td>
					</tr>
				
				
					<tr>
						<td right bold>PPN (<?= $header['ppn'] ?>%)</td>
						<td right>Rp. <?= number_format($ppn_total) ?></td>
					</tr>
			
					<tr>
						<td right bold>PPH (<?= $header['pph'] ?>%)</td>
						<td right>Rp. <?= number_format($pph_total) ?></td>
					</tr>
			
					<tr>
						<td right bold>Other Cost</td>
						<td right>Rp. <?= number_format($header['biaya_lain']) ?></td>
					</tr>
			
				
			
				<tr>
					<td right bold>Grand Total</td>
					<td right bold>Rp. <?= number_format($header['grand_total']) ?></td>
				</tr>
				<tr> 
					<td right colspan="3">Terbilang : <i><?= terbilang($header['grand_total']) ?> Rupiah</i></td>
				</tr>
			</tbody>
			<tfoot></tfoot>
		</table>

		<!-- <p>Availability Of Goods : <?= $header['status_stok'] ?></p>
		
		<?php if ($header['ket_indent'] != null) { ?>
		<p>Additional Info : <?= $header['ket_indent'] ?> </p>
		<?php } ?> -->

<br><br>
		<table class="ket">
			<tr>
				<td width="40%" bold class="ket_td">Informasi Pembayaran:</td>
				<td width="20%"class="ket_td"></td>
				<td width="40%" bold class="ket_td">Informasi Pengiriman:</td>
			</tr>
			<tr>
				<td class="ket_td">Metode Pembayaran: Transfer</td>
				<td class="ket_td"></td>
				<td class="ket_td">Serah Terima Barang: <?= $header['nama_franco'] ?></td>
			</tr>
			<tr>
				<td class="ket_td">Termin Pembayaran :  <?= $header['jenis_bayar'] ?> <?= $header['ket_bayar'] ?></td>
				<td class="ket_td"></td>
				<td class="ket_td">PIC: <?= $header['contact_franco'] ?></td>
			</tr>
			<tr>
				<td class="ket_td">&nbsp;</td>
				<td class="ket_td">&nbsp;</td>
				<td class="ket_td">No Tlp: <?= $header['telp_franco'] ?></td>
			</tr>
			<tr>
				<td bold class="ket_td">&nbsp;</td>
				<td class="ket_td">&nbsp;</td>
				<td class="ket_td">Informasi : <?= $header['info_pengiriman'] ?></td>
			</tr>
			<tr>
				<td bold class="ket_td">&nbsp;</td>
				<td class="ket_td">&nbsp;</td>
				<td class="ket_td">Alamat : <?= $header['alamat_franco'] ?></td>
			</tr>
			<tr>
				<td bold class="ket_td">Informasi Rekening Bank Penerima Pembayaran:</td>
				<td class="ket_td">&nbsp;</td>
				<td class="ket_td">&nbsp;</td>
			</tr>
			<tr>
				<td class="ket_td">Nama Bank: <?= $header['nama_bank'] ?></td>
				<td class="ket_td">&nbsp;</td>
				<td class="ket_td">&nbsp;</td>
			</tr>
			<tr>
				<td class="ket_td">No Rekening : <?= $header['no_rekening'] ?></td>
				<td class="ket_td">&nbsp;</td>
				<td class="ket_td">&nbsp;</td>
			</tr>
			<tr>
				<td class="ket_td">Atas Nama : <?= $header['atas_nama'] ?></td>
				<td class="ket_td">&nbsp;</td>
				<td bold class="ket_td">Syarat Penagihan: </td>
			</tr>
			<tr>
				<td class="ket_td">&nbsp;</td>
				<td class="ket_td">&nbsp;</td>
				<td class="ket_td"> Wajib melampirkan kwitansi, invoice, faktur pajak 
(jika ada pungutan PPN), surat jalan/delivery order, 
tanda terima barang dengan tercantum nomor ORDER 
ini</td>
			</tr>
		</table>
<br>
		<table>
			<tr>
				<td>
					<!-- <p>Syarat Pembayaran</p>
					<p><?= $header['ket_bayar'] ?></p> -->

					
				</td>
				<!-- <td left>
					<p left bold>Disetujui :</p>
					<br><br><br><br><br>
					
					<p>Ricko Yudhi Permana</p>
				</td> -->
				<td left>
					<p left bold>Disetujui :</p>
					<br><br><br><br><br>
					
					<p>( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)</p>
				</td>
				<td left>
					<p bold>&nbsp;</p>
					<br><br><br><br><br>
					
					<p>( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)</p>
				</td>
				<td center>
					<p bold>Konfirmasi Supplier</p>
					<br><br><br><br><br>
					
					<p><?= $header['nama_supplier'] ?></p>
				</td>
				
			</tr>
		</table>
		
		


		<pre><?php //print_r($headeran) 
				?></pre>

	</body>

	</html>
