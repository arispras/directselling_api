<!DOCTYPEhtml>
	<html>

	<head>
		
		<style>
			* {
				font-size: 9px ;
			}

			.table-bg th,
			.table-bg td {
				font-size: 1em !important;
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
				border: 1px solid rgba(0, 0, 0, 0.2);
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



			/* EXTRA */
			.no_doc{
				text-align:center;
				margin-bottom:0px;
			}
			.tab1 td{
				height:250px;
			}
			.tab2 td{
				height:60px;
			}
			.tab1 p{
				margin-bottom:200px;
				width:80%;
				font-size: 1em
			}
			.brand .image {
				width: 60px;
			}
			.brand div {
				width: 70%;
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

			.pagenum:before {
		/* content: counter(page); */
		content: counter(page) ' of 'counter(pages);
	}

	page {
		background: white;
		display: block;
		margin: 0 auto;
		margin-bottom: 0.5cm;
		box-shadow: 0 0 0.5cm rgba(0, 0, 0, 0.5);
	}

	page[size="A4"] {
		width: 21cm;
		min-height: 29.7cm;
	}

	page[size="A4"][layout="portrait"] {
		width: 29.7cm;
		height: 21cm;
	}

	page[size="A3"] {
		width: 29.7cm;
		height: 42cm;
	}

	page[size="A3"][layout="portrait"] {
		width: 42cm;
		height: 29.7cm;
	}

	page[size="A5"] {
		width: 14.8cm;
		height: 21cm;
	}

	page[size="A5"][layout="portrait"] {
		width: 21cm;
		height: 14.8cm;
	}

	#pageCounter {
		counter-reset: pageTotal;
	}

	#pageCounter page {
		counter-increment: pageTotal;
	}

	#pageNumbers {
		counter-reset: currentPage;

	}

	#pageNumbers div:before {
		counter-increment: currentPage;
		content: counter(currentPage) " of ";
	}

	#pageNumbers div:after {
		content: counter(pageTotal);
	}

	.page-number {
		font-size: 20px;
	}

	.page-number:after {
		counter-increment: page;
	}

	

	@page {
		counter-increment: page;

		@bottom-right {
			padding-right: 20px;
			/* content: "Page " counter(page); */
			content: counter(page) ' of 'counter(pages);
		}

	}
		</style>
	</head>

	<body>

		<?php

		$sub_total = $header['sub_total'] - $header['diskon'];
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

		<table style="padding:10px 0;">
			<tr>
				<td width="13%" style="padding:0 0px">
					<table>
						<tr>
							<td center><img src="data:image/png;base64,<?=  base64_encode(file_get_contents(('./logo_perusahaan.png'))) ?>" height="90px" width="90px"> </td>
						</tr>
						
					</table>
				</td>
				<td width="20%" style="padding:0 0px">
					<h1 left><?= strtoupper(get_company()['nama']) ?></h1>
					<table>
						<tr>
							<td>Gd. Cyber, Lantai.11, Jl. Kuningan Barat No. 8 </td>
						</tr>
						<tr>
							<td>Mampang Prapatan</td>
						</tr>
						<tr>
							<td>Jakarta Selatan</td>
						</tr>
						<tr>
							<td>Tel. 021 5269588</td>
						</tr>
					</table>
				</td>
				<td width="27%" style="padding:0 10px">
					
					<table>
						<tr>
							<h1 style="font-size:15px"  >PURCHASE ORDER</h1>
						</tr>
					</table>
				</td>
				<td width="25%" style="padding:0 20px">
					<table class="table-bg border">
						<tr>
							<td width="65%" center > Date <br> <?= tgl_indo($header['tanggal']) ?> </td>
							<td center> Page
								<p>
									<div id="pageCounter" class="page"> 
										<page size="A4"></page>
										<div id="pageNumbers" class="page">
											<div class="page-number"></div>
										</div>
									</div>
								</p> 
								
							</td>
						</tr>
						
						<tr>
							<td center colspan="2">PO Number <br> <h1><?= $header['no_po'] ?></h1> </td>
						</tr>
						<tr>
							<td colspan="2">Prepared by : <?= $header['dibuat'] ?></td>
						</tr>
					</table>
				</td>
			</tr>
		</table>

		<table class="table-bg border" >
			<tr>
				<td width="52%">
				<table  >
					<!-- style="border:0px" -->
						<tr >
							<td style="border:0px" width="30%">Supplier Name </td>
							<td style="border:0px; padding:0;" width="1">:</td>
							<td style="border:0px; " ><?= $header['nama_supplier'] ?></td>
						</tr>
						<tr>
							<td style="border:0px;" >  Supplier Address</td>
							<td style="border:0px; padding:0;" >:</td>
							<td style="border:0px;  " ><?= $header['alamat_supplier'] ?></td>
						</tr>
						<tr>
							<td style="border:0px" >Supplier PIC </td>
							<td style="border:0px; padding:0;" >:</td>
							<td style="border:0px; "><?= $header['contact_person_supplier'] ?></td>
						</tr>
						<tr>
							<td style="border:0px">Phone Number</td>
							<td style="border:0px; padding:0;">:</td>
							<td style="border:0px; "><?= $header['no_telepon_supplier'] ?></td>
						</tr>
					</table>
				</td>
				
				
				<td > 
					<table  >
					<!-- style="border:0px" -->
						<tr >
							<td style="border:0px" width="31%">Franco </td>
							<td style="border:0px; padding:0;" width="2%">:</td>
							<td style="border:0px"><?= $header['nama_franco'] ?></td>
						</tr>
						<tr>
							<td style="border:0px">Franco Address</td>
							<td style="border:0px; padding:0;">:</td>
							<td style="border:0px"><?= $header['alamat_franco'] ?></td>
						</tr>
						<tr>
							<td style="border:0px">PIC </td>
							<td style="border:0px; padding:0;">:</td>
							<td style="border:0px"><?= $header['contact_franco'] ?></td>
						</tr>
						<tr>
							<td style="border:0px">Phone Number</td>
							<td style="border:0px; padding:0;">:</td>
							<td style="border:0px"><?= $header['telp_franco'] ?></td>
						</tr>
						<!-- <tr>
							<td style="border:0px">Catatan </td>
							<td style="border:0px; padding:0;">:</td>
							<td style="border:0px"><?= $header['catatan'] ?></td>
						</tr> -->
					</table>
				</td>
			</tr>
			
			<tr>
				<td center colspan=""> Supplier Quotation / Contract Ref <h1><?= $header['qoutation'] ?></h1></td>
				<td center>Terms of Payment<h1><?= $header['ket_bayar'] ?></h1></td>
			</tr>
		</table>

		<br><br>

		<table class="table-bg border">
			<thead>
				<tr>
					<th >No</th>
					<th >Item Code</th>
					<th>Item Description</th>
					<th >Goods Specs / Remarks</th>
					<th >Qty</th>
					<th >UOM</th>
					<th >Unit Price</th>
					<th >Amount</th>
				</tr>
			</thead>
			<tbody>
				<?php $no = 0 ?>
				<?php foreach ($detail as $key => $val) { ?>
					<?php $no = $no + 1;
					

					?>

					<tr>
						<td center width="2%"><?= $no ?></td>
						<td left width="7%"><?= $val['kode_barang'] ?> </td>
						<td width="17%"><?= $val['nama_barang'] ?></td>
						<td center width="12%"><?= $val['ket'] ?></td>
						<td center width="3%"><?= number_format($val['qty']) ?></td>
						<td center width="3%"><?= $val['uom'] ?></td>
						<td right width="7%"> <?= number_format($val['harga']) ?></td>
						<td right width="7%"> <?= number_format($val['total']) ?></td>
					</tr>
				<?php } ?>
			</tbody>
			<tfoot></tfoot>
		</table>

		<br>

		<table class="table-bg border">
			<tbody>

				<tr>
					<td right>Sub Total</td>
					<td right>Rp. <?= number_format($header['sub_total']) ?></td>
				</tr>
				<?php if ($header['diskon'] != 0) { ?>
					<tr>
						<td right>Disc</td>
						<td right>Rp. <?= number_format($header['diskon']) ?></td>
					</tr>
				<?php } ?>
				<?php if ($header['ppbkb'] != 0) { ?>
					<tr>
						<td right>PPBKB (<?= $header['ppbkb'] ?>%)</td>
						<td right>Rp. <?= number_format($pbbkb_total) ?></td>
					</tr>
				<?php } ?>
				<?php if ($header['ppn'] != 0) { ?>
					<tr>
						<td right>PPN (<?= $header['ppn'] ?>%)</td>
						<td right>Rp. <?= number_format($ppn_total) ?></td>
					</tr>
				<?php } ?>
				<?php if ($header['pph'] != 0) { ?>
					<tr>
						<td right>PPH (<?= $header['pph'] ?>%)</td>
						<td right>Rp. <?= number_format($pph_total) ?></td>
					</tr>
				<?php } ?>
				<?php if ($header['biaya_lain'] != 0) { ?>
					<tr>
						<td right>Other Cost</td>
						<td right>Rp. <?= number_format($header['biaya_lain']) ?></td>
					</tr>
				<?php } ?>
				<?php if ($header['biaya_kirim'] != 0) { ?>
				<tr>
					<td right>Biaya Kirim</td>
					<td right style="width:30%">Rp. <?= number_format($header['biaya_kirim']) ?></td>
				</tr>
				<?php } ?>
				<tr>
					<td right bold>Grand Total</td>
					<td right bold>Rp. <?= number_format($header['grand_total']) ?></td>
				</tr>
				<tr> 
					<td colspan="2">In Words : <i><?= CurencyLang::toEnglish($header['grand_total']) ?> Rupiah</i></td>
				</tr>
			</tbody>
			<tfoot></tfoot>
		</table>
		<p>Availability Of Goods : <?= $header['status_stok'] ?></p>
		<?php if ($header['ket_indent'] != null) { ?>
		<p>additional info : <?= $header['ket_indent'] ?> </p>
		<?php } ?>


		<p></p>
	<table class="table-bg border">
		<tr>
			<td>
					<p bold>Syarat Penagihan :</p>
		<ol>
			<li>Dokumen :
			<ul>
					<li>Invoice</li>
					<li>Kwitansi Bermaterai Cukup (jika tidak ada kwitansi, invoice perlu bermaterai)</li>
					<li>Faktur Pajak (jika ada)</li>
					<li>Surat Pernyataan Non-PKP (untuk perorangan/pengusaha badan yang tidak dikenakan PPN)</li>
					<li>Lampirkan Tanda Terima/Berita Acara/Surat Jalan dari Barang/Jasa</li>
				</ul>
			</li>
			
				
			<li>Jatuh tempo dihitung sejak dokumen tagihan diterima dengan lengkap dan benar</li>
			<li>Supplier menjamin keabsahan barang yang dijual serta membebaskan pihak pembeli 	
			dari segala tuntutan<br> hukum terkait dengan keabsahan barang tersebut</li>
			<li>Nomor Rekening Harus Dicantumkan Di Invoice</li>
		</ol>
			</td>
		</tr>
		
	</table>
<br>
		<table>
			<tr>
				<td>
					<!-- <p>Syarat Pembayaran</p>
					<p><?= $header['ket_bayar'] ?></p> -->

					
				</td>
				<td center>
					<p bold>Supplier Confirmation</p>
					<br><br><br><br><br>
					
					<p><?= $header['nama_supplier'] ?></p>
					<p>.</p>
				</td>
				<td center>
					<p bold> 
					<?php  $ket_approval=($header['status']=='REJECTED')?'Rejected By':'Approved By'; ?>		
					<?=  $ket_approval ?>	
					  
					
					</p>
					<br><br><br><br><br>
					<?php for ($i = 1; $i < 6; $i++) { ?>
						<?php if ($header['user_approve' . $i] != null) { ?>
							<?php $pembayaran[] = $header['user_approve' . $i]; ?>
							<?php $user_jabatan[] = $header['user_approve_jabatan' . $i]; ?>
						<?php } ?>
					<?php } ?>
					<p><?= array_reverse($pembayaran)[0] ?></p>
					<p><?= array_reverse($user_jabatan)[0] ?></p>
				</td>
				<p right bold>*THIS PURCHASE ORDER IS APPROVED BY SYSTEM, SIGNATURE IS NOT REQUIRED*</p>
			</tr>
		</table>
		
		


		<pre><?php //print_r($headeran) 
				?></pre>

	</body>

	</html>
