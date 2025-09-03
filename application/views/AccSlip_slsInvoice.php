<!DOCTYPEhtml>
    <html>
    <head>
		
        <?php require '__laporan_style.php' ?>
	</head>

    <body>
        
        <?php require '__laporan_header.php' ?>

        <br>
		<u><h1 class="no_doc">INVOICE</h1></u>
        <h1 align="center" ><?= $header['no_invoice'] ?></h1>
        <br>

        <div class="d-flex flex-between">
		<table class="" style="width:100%">	
				<tr>
                    <tr>
                        <td style="width:77%">Kepada Yth.</td>
                        <td align="left" style="width:9%">Tanggal Invoice </td>
                        <td align="center">:</td>
                        <td><?= tgl_indo($header['tanggal']) ?></td>
                    </tr>		
                    <tr>
                        <td><?= $header['customer'] ?></td>
                        <td align="left">Periode Kirim</td>
                        <td align="center">:</td>
                        <td align="left"><?= $header['kirim_awal'] ?> - <?= $header['kirim_akhir'] ?></td>
                    </tr>
                    <tr>
                        <td>JL. RAYA PLUIT PERMAI (MEGA MALL) NO.21-23</td>
                    </tr>
                   
                    <tr>
                        <td>PLUIT PENJARINGAN</td>
                    </tr>
                    <tr>
                        <td>Jawa Barat</td>
                    </tr>
				</tr>
			</table>
		</div>
        <br><br>

        <table class="table-bg border">
			<thead>
				<tr>
					<th>No</th>
					<th>Nama Produk</th>
					<th>Kuantitas (Kg)</th>
					<th>Harga Satuan (Rp)</th>
					<th>Jumlah (Rp)</th>
				</tr>

			</thead>

			<tbody>
			
				<tr class="tab1">
					
					<td  width="2%"><p>1</p></td>
					<td  width="20%"><p><?= $header['item'] ?></p> Jatuh tempo : </td>
					<td  width="7%"><p><?= $header['qty'] ?></p></td>
					<td  width="7%"><p>Rp.<?= $header['harga_satuan'] ?></p></td>
					<td  width="9%"><p>Rp.<?= $header['jumlah'] ?></p></td>
					 
				</tr>
                <tr>
                    <td colspan="4">Sub Total</td>
                    <td>Rp.<?= $header['jumlah'] ?></td>
                </tr>
                <tr>
                   <td colspan="4">Potongan Harga (Discount)</td>
                   <td>Rp.<?= $header['diskon'] ?></td>
                </tr>
				<tr>
                   <td colspan="4">Uang Muka</td>
                   <td>Rp.<?= $header['uang_muka'] ?></td>
                </tr>
                <tr>
                   <td colspan="4">Dasar Pengenaan Pajak (DPP)</td>
                   <td>Rp.40.000</td>
                </tr>
                <tr>
                   <td colspan="4">PPN</td>
                   <td><?= $header['ppn'] ?> %</td>
                </tr>
                <tr>
                   <td colspan="4">Total Tagihan</td>
                   <td>Rp.<?= $header['grand_total'] ?></td>
                </tr>
                <tr class="tab2">
                   <td colspan="5"><p>Terbilang : Dua Puluh Ribu Rupiah</p></td>
                </tr>
			</tbody>
			
			<tfoot></tfoot>
		</table>
        <br><br>
        <div class="d-flex flex-between">
		<table class="" style="width:100%">	
				<tr>
                    <tr>
                        <td>Pembayaran dilakukan dengan transfer ke </td>
                        <td> : </td>
                        <td style="width:20%" align="center"><?= strtoupper(get_company()['nama']) ?></td>
                    </tr>		
                    <tr>
                        <td>BNI</td>
                    </tr>
                    <tr>
                        <td>Cabang ROA</td>
                    </tr>   
                    <tr>
                        <td>A/C</td>
                       
                        <td style="width:60%"> : 55777779989</td>
                    </tr>
                    <tr>
                        <td>Atas Nama</td>
                       
                        <td style="width:60%"> :<?= strtoupper(get_company()['nama']) ?></td>
                        <td align="center">SUBIANTO</td>
                    </tr>

				</tr>
			</table>
		</div>

        <br><br><br>


    </body>

</html>
