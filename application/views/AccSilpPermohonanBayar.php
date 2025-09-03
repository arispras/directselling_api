<!DOCTYPEhtml>
    <html>

    <head>
        
    
<style>
			* {
				font-size: 11px ;
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
			
    div.atas{
        margin-bottom:50px;
    }
    div.jarak{
        line-height: 1.6;
    }
	.div1 {
  	width: 15px;
  	height: 12px;
  	border: 0.5px solid rgba(0,0,0,0.7);
  	box-sizing: border-box;
	margin-right:7px;
	margin-bottom:8px;
	line-height: 2;
	}	
	.div2 {
  	box-sizing: border-box;
	margin-right:7px;
	margin-bottom:8px;
	}
	.kanan {
	border: 0.5px ;
	width: 350px;
	height:140px;
	/* border-style: solid; */
	float:right;
	/* margin-left:30px; */
	
	/* margin-bottom:10px; */
	}		
	.kiri {
	
	border: 0.5px ;
	width: 345px;
	height:140px;
	/* border-style: solid; */
	/* margin-left:350px; */
	/* margin-bottom:10px; */
	}
	
	.rp{
		align:left;
	}
	
		</style>

    </head>

    <body>
	<div right>
        <table   style="line-height: 2;">
			<tr>
				<td right>Nomor Voucher</td>
				<td style="width:5px" right>:</td>
				<td style="width:85px" right>................................</td>
			</tr>
			<tr>
				
				<td right>Tanggal Voucher</td>
				<td right>:</td>
				<td right>................................</td>
			</tr>
		</table>
        </div>
        <div center>
            <h2 style="font-size:19px;">PERMOHONAN PEMBAYARAN </h2>
            
            
        </div>
        <br>
        
		<div>
		<div class="kanan">
			<table   style="line-height: 2;">
				<tr>
					
					<td style="Width:25px">Divisi</td>
					<td style="Width:5px" >:</td>
					<td style="Width:160px"><?= $hd['divisi'] ?></td>
				</tr>
				<tr>
					
					<td>Periode</td>
					<td>:</td>
					<td > <?= $hd['periode'] ?></td>
				</tr>
				<tr>
					<td>Keperluan</td>
					<td>:</td>
					<td ><?= $hd['ket'] ?></td>
				</tr>
				
			</table>
				
		</div>
				<div class="kiri">
				<table   style="line-height: 2;">
			<tr>
				<td style="Width:55px">Nomor</td>
				<td style="Width:5px">:</td>
				<td style="Width:175px"><?= $hd['no_transaksi'] ?></td>
			</tr>
			<tr>
				<td>Tanggal</td>
				<td>:</td>
				<td><?= tgl_indo($hd['tanggal']) ?></td>
			</tr>
			<tr>
				<td>Vendor</td>
				<td>:</td>
				<td><?= $hd['supplier'] ?></td>
				
				<!-- <td>Keperluan</td>
				<td>:</td>
				<td><?= $hd['ket'] ?></td> -->
			</tr>
			<tr>
				<td>No Referensi</td>
				<td>:</td>
				<td><?= $hd['no_referensi'] ?></td>
			</tr>
			<tr>
				<td>Diminta Oleh</td>
				<td>:</td>
				<td><?= $hd['diminta_oleh'] ?></td>
			</tr>

			
		</table>
				</div>
		</div>
		
       


        <table class="tab-expand border">
            <tr>
                <!-- <th style="width:10%">No.</th> -->
                <th style="width:2%">No </th>
                <th>Keterangan</th>
                <th style="width:10%">Kuantitas </th>
                <th style="width:17%">Harga Satuan </th>
                <th style="width:19%">Total Harga </th>
            </tr>
            
			<?php $no= 0 ?>
			<?php foreach ($dt as $key=>$val) { ?> 
				
				<?php $no= $no+1 ?>
			
			<tr style=" text-align:center;">
                
			
				<td> <div class="atas"><?= $no ?></div></td>

                <td left style="height: 130px">

                <div  class="atas" ><?= $val['keterangan'] ?> </div> 
               
                </td>
                
				<td>
                <div right class="atas"> <?= number_format($val['qty']) ?></div>
                </td>
                <td>
                <div rowspan="2" right class="atas">Rp. <?= number_format($val['harga']) ?></div> 
                </td>
                <td>
                <div right class="atas">Rp. <?= number_format($val['qty']*$val['harga']) ?></div> 
                </td>
                
                
			</tr>
			
            <?php } ?>
			
			
            <tr>
				<td colspan="3" bold rowspan="6"style="line-height: 1.7;" > 
				<div style="margin-left:41px;">Pembayaran disetorkan ke Rekening :</div>
				<div style="margin-left:41px;">Nama Bank &nbsp; &nbsp;&nbsp;&nbsp;: <?= $hd['nama_bank'] ?></div>
				<div style="margin-left:41px;">No Rekening&nbsp;&nbsp;&nbsp;&nbsp;: <?= $hd['no_rek'] ?></div>
				<div style="margin-left:41px;">Atas Nama &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <?= $hd['atas_nama'] ?></div>
				</td>
                <td  >Sub Total</td>
                <td right>
					<div style=" display: inline-block; "> Rp.</div> 
					<div style="float:right;"><?= number_format($hd['subtotal']) ?></div>	
				</td>
            </tr>
            <tr>
                <td colspan="0">Potongan</td>
                <td right>
					<div style=" display: inline-block; "> Rp.</div> 
			 		<div style="float:right;"><?= number_format($hd['diskon']) ?></div>	
				</td>
            </tr>
            <!-- <tr>
                <td colspan="">Uang Muka</td>
                <td right>Rp. <?= number_format($Invo['uang_muka']) ?></td>
            </tr> -->
            <tr>
                <td colspan="">DPP</td>
                <td right>
					<div style=" display: inline-block; "> Rp.</div>
					<div style="float:right;"><?= number_format($hd['dpp']) ?></div>	
				</td>
            </tr>
            <tr>
                <td colspan="">PPN (<?= $hd['ppn'] ?>%)</td>
                <td right>
					<div style=" display: inline-block; "> Rp.</div>	
					<div style="float:right;"><?= number_format(floor($hd['ppn']/100*$hd['dpp'])) ?></div>	
				</td>
            </tr>
			<tr>
                <td colspan="">PPH 23 (<?= $hd['pph'] ?>%) </td>
                <td right>
					<div style=" display: inline-block; "> Rp.</div>
				 	<div style="float:right;"><?= number_format(floor($hd['pph']/100*$hd['dpp'])) ?></div>	 
				</td>
            </tr>
            <tr>
                <td colspan="">Total</td>
                <td right> 
					<div style=" display: inline-block; "> Rp.</div>  
					<div style="float:right;"><?= number_format(floor($hd['total'])) ?></div>  
				</td>
            </tr>
            <tr>
                <td colspan="5">Terbilang : <i><?= terbilang($hd['total']) ?> Rupiah</i></td>
            </tr>
        </table>
		<br>
		<table class="table-bg border">
			<tr>
			<td width="33%" center >Dibuat Oleh</td>
			<td width="34%" center>Deketahui Oleh</td>
			<td width="33%" center>Disetujui Oleh</td>
			</tr>
			<tr>
				<td height="40"></td>
				<td></td>
				<td></td>
			</tr>
			<tr>
				<td center>D'Elaisa Agriputri P.</td>
				<td center>M. Feizal Deradjat</td>
				<td center>Mulyanto / Kurniawan</td>
			</tr>			
		</table>
		<br><br><br>
		<table class="table-bg border">
			<tr>
			<td width="33%" center >Maker</td>
			<td width="34%" center>Approval</td>
			<td width="33%" center>Releaser</td>
			</tr>
			<tr>
				<td height="30"></td>
				<td></td>
				<td></td>
			</tr>

			
		</table>
				<br>
		<div>
		KELENGKAPAN DOKUMEN :<br>
			<table  style="margin-top:6px; font-size:9px;">
				<tr>
					<td width="10px"> 
						<div class="div1"> </div> 
					</td>
					<td left> <div class="div2">ASLI INVOICE BERMATERAI / KWITANSI BERMATERAI</div> </td>
				</tr>
				
				<tr>
					<td style="margin-top:10px;">
						<div class="div1"></div> 
					</td>
					<td left > <div class="div2">FAKTUR PAJAK / SURAT PERNYATAAN NON PKP</div> </td>
				</tr>
				<tr>
					<td style="margin-top:10px;">
						<div class="div1"></div> 
					</td>
					<td left > <div class="div2">ASLI SURAT JALAN / BAPP</div> </td>
				</tr>
				<tr>
					<td style="margin-top:10px;">
						<div class="div1"></div> 
					</td>
					<td left > <div class="div2"> PO / SPK</div></td>
				</tr>
			</table>
			
			 
			
		</div>

        


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
