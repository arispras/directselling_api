<!DOCTYPEhtml>
    <html>

    <head>
    
    <style>
			* {
				font-size: 12px ;
			}

			.table-bg th,
			.table-bg td {
				font-size: 1em !important;
			}

			/* ROOT */
			body {
				background-color: white;
				font-family:  Arial, sans-serif;
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
                font-size: 15px ;
			}
			h4.title {
				text-align: center;
				font-size: 11px
				
			}
			p.tgl {
				text-align: center;
				font-size: 5px;
				margin-bottom: 0px;
			}
			hr.top {
				border: none;
				border-bottom: 2px solid #333;
				margin-bottom: 10px;
				margin-top: 10px;
			}

			/* -------------TABLE--------------------------- */
			
			table {
				width: 100%;
			}
			table.border {
				border: 0.5px solid rgba(0,0,0,0.4);
			}
			.table-bg th,
			.table-bg td {
				font-size: 0.8em;
			}
			.table-bg th {
				color: black;	
				/* background: linear-gradient(to right, #9dc9fa, #9dc9fa); */
				background: rgba(50,50,50,0.1);
				text-align: center !important;
				font-weight: bolder;
				text-transform: uppercase;
			}
            .tab-expand td {
            border-bottom: 1px solid #cecfd5;
            border-right: 1px solid #cecfd5;
              }
	        .tab-expand td:first-child {
	    	border-left: 1px solid #cecfd5;
	          }

			.table-bg th,
			.table-bg td {
				border: 0.5px solid rgba(0,0,0,0.4);
				padding: 5px 8px;
			}
			.tab-expand th,
			.tab-expand td {
				padding: 5px 10px;
				width: auto;
			}
			.tab-expand.border,
			.tab-expand.border th,
			.tab-expand.border td {
				border: 0.5px solid rgba(0, 0, 0, 0.5);
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


			/* KOP PRINT */
			.kop-print {
				width: 1000px;
				margin: auto;
			}
			.kop-print img {
				float: left;
				height: 60px;
				margin-right: 20px;
			}
			.kop-print .kop-info {
				font-size: 15px;
			}
			.kop-print .kop-nama {
				font-size: 25px;
				font-weight: bold;
				line-height: 35px;
			}
			.kop-print-hr {
				border-color: rgba(0,0,0,0,1);
				margin-bottom: 0px;
			}



		
			.mt-1 { margin-bottom: 1.5%; }
			.box { margin-bottom: 5%; }
			.box h3 { margin-bottom:0; }
			.box table td { padding: 5px 0;}
			[border-none] { border: 0px solid red !important; }
			[c-border] { border: 1px solid rgba(0,0,0,0.4); }
			.c-table {
				margin-bottom: 20px;
			}
			.c-table td {
				padding-bottom: 5px;
			}
			.c-table.head td {
				padding-right: 25px;
			}
			.clearfix::after {
				content: "";
				clear: both;
				display: table;
			}
			.m-0 { margin:0; }
			.w-100 { width:100%; }

    div.atas{
        margin-bottom:200px;
    }
    div.jarak{
        line-height: 1.6;
    }
		</style>
    </head>

    <body>

    <div center>
            <h2 style="margin-top:40;font-size:15px;"> <u> PERFORMA INVOICE</u> </h2>
            
            <h3 style="margin:2;"><?= $Invo['no_performa'] ?></h3>
        </div>
        <br>
        <table class="table-bg">
			<tr>
				<td bold style="width:50%; border:0px solid black; line-height: 1.5;">
					<div>Kepada Yth, <br> 
                    <div><?= $Invo['nama_customer'] ?></div>   
                    <div><?= $Invo['alamat'] ?></div>
					<div><?= $Invo['no_npwp'] ?></div>
				</td>
				<td style="width:20%; border:0px solid black"></td>
				<td bold style="width:40%; border:0px solid black">
					<div right>Tanggal Invoice : <?= tgl_indo($Invo['tanggal']) ?></div>
					<br><br><br><br>
				</td>
			</tr>
		</table>

        <br>


        <table class="tab-expand border">
            <tr>
                <!-- <th style="width:10%">No.</th> -->
                <th style="width:5%">No </th>
                <th>Nama Produk</th>
                <th style="width:15%">Kuantitas <br> (Kg)</th>
                <th style="width:15%">Harga Satuan <br> (Rp)</th>
                <th style="width:19%">Jumlah <br> (Rp)</th>
            </tr>
            <tr style="height: 150px; text-align:center;">
                <td > <div class="atas">1.</div></td>

                <td left style="height: 230px">

                <div ><?= $Invo['deskripsi'] ?></div> 
				<div style="margin-bottom:146px; line-height: 1.5;"><?= $Invo['no_referensi'] ?></div>
                <div bol style="margin-bottom:20px;">Jatuh Tempo : SEGERA</div>
            
                
                </td>


                <td>
                <div class="atas"> <?= number_format($Invo['qty']) ?></div>
                </td>
                <td>
                <div class="atas">Rp. <?= number_format($Invo['harga_satuan']) ?></div> 
                </td>
                <td>
                <div class="atas">Rp. <?= number_format($Invo['jumlah']) ?></div> 
                </td>
                
                
			</tr>
            
            </tr>
            <tr>
                <td  colspan="4">Sub Total</td>
                
                <td right>
                    Rp. <?= number_format($Invo['jumlah']) ?></td>
            </tr>
            <tr>
                <td colspan="4">Potongan Harga</td>
                <td right>Rp. <?= number_format($Invo['diskon']) ?></td>
            </tr>
            <tr>
                <td colspan="4">Uang Muka</td>
                <td right>Rp. <?= number_format($Invo['uang_muka']) ?></td>
            </tr>
            <tr>
                <td colspan="4">Dasar Pengenaan Pajak (DPP)</td>
                <td right>Rp. <?= number_format($Invo['jumlah']-($Invo['uang_muka']+$Invo['diskon'])) ?></td>
            </tr>
            <tr>
                <td colspan="4">PPN <?= $Invo['ppn'] ?>%</td>
                <td right>Rp. <?= number_format($Invo['ppn']/100*($Invo['jumlah']-($Invo['uang_muka']+$Invo['diskon']) )) ?></td>
            </tr>
            <tr>
                <td colspan="4">Total Tagihan</td>
                <td right>Rp. <?= number_format($Invo['grand_total']) ?></td>
            </tr>

            
            
            <tr>
                <td style="height: 60px" colspan="5">Terbilang : <i><?= terbilang($Invo['grand_total']) ?></i></td>
            </tr>


        </table>
<br><br><br>
        <table>
            <!-- <tr>
                <td width='30%'>Pembayaran dilakukan dengan transfer</td>
                <td width='5px'>:</td>
                <td></td>
                <td center width='30%' >PT DINAMIKAPRIMA ARTHA</td>
            </tr>
            <tr>
                <td>BNI</td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>Cabang ROA Malaka</td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>A/C</td>
                <td>:</td>
                <td>55777779989</td>
            </tr>
            <tr>
                <td>Atas Nama</td>
                <td>:</td>
                <td>PT DINAMIKAPRIMA ARTHA</td>
              
            </tr>
            <tr>
                <td>.</td>
                <td></td>
                <td></td>
				<td></td>
            </tr>
			<tr>
				<td>.</td>
                <td></td>
                <td></td>
				<td></td>
			</tr>
			<tr>
				<td></td>
                <td></td>
                <td></td>
                <td center><?= $Invo['user_ttd'] ?></td>
			</tr> -->
            
        </table>
        
        <br><br><br>
        


<!-- 
        <table class="tab-expand">
            <tr>
                <th>Yang Menerima</th>
                <th>Yang Menyerahkan</th>
            </tr>
            <tr>
                <td style="height:90px"></td>
                <td style="height:90px"></td>
            </tr>
            <tr>
                <td>
                    <center>(..............................)</center>
                </td>
                <td>
                    <center>(..............................)</center>
                </td>
            </tr>
        </table> -->


    </body>

    </html>
