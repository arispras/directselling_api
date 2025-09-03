<!DOCTYPEhtml>
	<html>

	<head>
	<style type='text/css'>
	/* ROOT */
	body {
		background-color: white;
		font-size: 13px ;
		font-family: Arial;
	}

	body * {
		border-spacing: 0;
	}

	h1.title {
			margin-bottom: 0px;
			margin-left: 10px;
			line-height: 30px;
			text-align: center;
			font-size: 17px ;
	}

	h3.title {
		margin-bottom: 0px;
		line-height: 30px;
	}

	h4.title {
		margin-bottom: 0px;
		text-align: center;
	}

	hr.top {
		border: none;
		border-bottom: 2px solid #333;
		margin-bottom: 10px;
		margin-top: 10px;
	}

	/* TABLE */
	table.no_border td  {
				padding: 5px 5px;
				
		}
	table.no_border tr:nth-child(even) {
		background: none;
	  }
	table {
		width: 100%;
	}

	table.border {
		/* border: 0.5px solid rgba(0, 0, 0, 0.4); */
	}

	.table-bg th,
	.table-bg td {
		font-size: 0.9em;
	}

	.table-bg th {
		color: #fff;
		background: #395870;
		background: linear-gradient(#49708f, #293f50);
		text-align: center !important;
		font-weight: bolder;
		text-transform: uppercase;
	}

	.table-bg th,
	.table-bg td {
		border: 0.5px solid rgba(0, 0, 0, 0.3);
		padding: 7px 8px;
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
	/* ------------------------ */
	
	  /* thead {
		background: #395870;
		background: linear-gradient(#49708f, #293f50);
		color: #fff;
		font-size: 11px;
		text-transform: uppercase;
	  } */

	  

	  th:first-child {
		border-top-left-radius: 10px;
		text-align: left;
		
	  }
	  th:last-child {
		border-top-right-radius: 10px;
	  }
	  tbody tr:nth-child(even) {
		background: #f0f0f2;
	  }
	  .table-bg td {
		border-bottom: 1px solid #cecfd5;
		border-right: 1px solid #cecfd5;
		/* font-size: 12px; */
	  }
	  .table-bg td:first-child {
		border-left: 1px solid #cecfd5;
	  }
	  
	 
/* ------------------------ */
	/* HELPER */
	[d],
	[d] * {
		border: 1px solid red;
	}

	[dd] {
		border: 1px solid blue;
	}

	[center] {
		text-align: center !important;
	}

	[left] {
		text-align: left !important;
	}

	[right] {
		text-align: right !important;
	}

	[bold] {
		font-weight: bold !important;
	}

	.d-flex {
		display: flex;
	}

	.flex-between {
		justify-content: space-between;
	}

	.flex-nowrap {
		flex-wrap: nowrap;
	}

	.flex-wrap {
		flex-wrap: wrap;
	}

	.pos-fixed {
		position: fixed;
		top: 0;
		width: 100%;
	}


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
		border-color: rgba(0, 0, 0, 0, 1);
		margin-bottom: 0px;
	}


</style>


	</head>

	<body>
<div>
<div>
  <div class="kop-print">
  <img src="data:image/png;base64,<?=  base64_encode(file_get_contents(base_url('logo_perusahaan.png'))) ?>" height="90px" width="110px"> 
    <div class="kop-nama">KLINIK ANNAJAH</div>
    <div class="kop-info"> Jl Sukarno Hatta No 12 No 12, Bandung Jawa Barat 14450</div>
    <div class="kop-info">Telp : (021) 6684055</div>
  </div>
  <hr class="top">
</div>
		

		<h1 class="title">FORM PEMAKAIAN BARANG</h3>
		<br>
		<!-- <?= $status=($header['is_posting']==1?'Posting':'Belum Posting') ?> -->
		<div class="d-flex flex-between">
			<table class="no_border" style="width:60%">
			<tr>
					<td style="width:15%" class="no_border" >Lokasi</td>
					<td>:</td>
					<td style="width:20%"><?= $header['lokasi'] ?></td>
				
				
					<td class="no_border" >Gudang</td>
					<td>:</td>
					<td ><?= $header['gudang'] ?></td>
			</tr>
			
					<td>Nomor Dokumen</td>
					<td>:</td>
					<td><?= $header['no_transaksi'] ?></td>
				
				
					<td>Tanggal</td>
					<td>:</td>
					<td><?= tgl_indo($header['tanggal']) ?></td>
				
			</table>
		</div>

<br>
<br>
		<table  class="table-bg border" >
			<thead>
				<tr>
					<th >No</th>
					<th >Kode Barang</th>
					<th >Nama Barang</th>
					<th >QTY</th>
					<th >UOM</th>
					<th >Kegiatan/Alokasi</th>
					<th >Kendaraan/AB/Mesin</th>
					<th >Keterangan</th>
				</tr>

			</thead>

			<tbody>
				<?php $no = 0 ?>
				<?php foreach ($detail as $key => $val) { ?>

					<?php $no = $no + 1 ?>
					<tr>

						<td center width="2%"><?= $no ?></td>
						<td center width="10%"><?= $val['kode_barang'] ?></td>
						<td center width="20%"><?= $val['nama_barang'] ?></td>
						<td center width="5%"><?= $val['qty'] ?></td>
						<td center width="5%"><?= $val['uom'] ?></td>
						<td center width="20%"><?= $val['nama_kegiatan'] ?></td>
						<td center width="25%"><?= $val['nama_kendaraan'] ?> <?= $val['kode_kendaraan'] ?></td>
						<td center width="15%"><?= $val['ket'] ?></td>


					</tr>
				<?php } ?>

			</tbody>

			<tfoot></tfoot>
		</table>
		<br>






		<pre><?php //print_r($headeran) 
				?></pre>
</div>
	</body>

	</html>
