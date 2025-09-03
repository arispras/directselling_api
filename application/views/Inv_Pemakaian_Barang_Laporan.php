<!DOCTYPEhtml>
<html>
	<head>
	
	<?php 
			if ($format_laporan=='view') {
				require '_laporan_style_fix.php';
			}
			else{
				if ($format_laporan=='pdf') {
					require '__laporan_style_pdf.php';
				}	
			}
		?>
	</head>
	<body>
		
		<?php require '__laporan_header.php' ?>


		<h3 class="title">LAPORAN PEMAKAIAN BARANG</h3>
		<br>
		
		<div class="d-flex flex-between">
			<table class="no_border" style="width:50%">
				<tr>
					<td>Lokasi</td>
					<th>:</th>
					<td  style="width:45%"><?= $filter_lokasi ?></td>

					<td>Kendaraan</td>
					<th>:</th>
					<td><?= $filter_traksi ?></td>
				</tr>
					
				<tr>
					<td>Gudang</td>
					<th>:</th>
					<td><?= $filter_gudang ?></td>
					
					<td>Kegiatan</td>
					<th>:</th>
					<td><?= $filter_kegiatan ?></td>
				</tr>
				<tr>
					<td>Blok/Mesin</td>
					<th>:</th>
					<td><?= $filter_blok ?></td>

					<td>Item</td>
					<th>:</th>
					<td><?= $filter_item ?></td>
				</tr>
				<tr>
					

					<td>Periode Tanggal</td>
					<th>:</th>
					<td><?= tgl_indo($filter_tgl_awal) .' s/d '. tgl_indo($filter_tgl_akhir) ?></td>

					<td>Keterangan</td>
					<th>:</th>
					<td><?= $filter_ket ?></td>
				</tr>
			
			</table>	
		</div>

		<div class="d-flex flex-between">
			<table class="" style="width:30%">
				
				
			</table>
		</div>
		<br>
		

		<table class="table-bg border">
			<thead>
				<tr>
					<th style="width:2%">no</th>
					<th style="width:10%" >No Transaksi</th>
					<th style="width:7%">Tanggal</th>
					<th style="width:7%">Kode Barang</th>
					<th style="width:15%" >Nama Barang</th>
					<th style="width:5%" >Qty</th>
					<th style="width:3%">Uom</th>
					<!-- <th >No Transaksi Permintaan</th> -->
					<!-- <th >Tanggal Permintaan</th> -->
					<th style="width:10%">Kegiatan</th>
					<th style="width:5%">Blok/Mesin</th>
					<th >Kendaraan</th>
					<th >Keterangan</th>
				</tr>

			</thead>

			
			<tbody>
			<?php $no= 0 ?>
			<?php foreach ($po as $key=>$val) { ?> 
				
				<?php $no= $no+1 ?>
				<tr>
					<td><?= $no ?></td>
					<td left><?= $val['no_transaksi'] ?></td>
					<td center><?= tgl_indo_normal($val['tanggal']) ?></td>
					<td center><?= $val['kode'] ?></td>
					<td left><?= $val['item'] ?></td>
					<td center><?= $val['qty'] ?></td>
					<td center><?= $val['uom'] ?></td>
					<!-- <td center></td> -->
					<!-- <td center></td> -->
					<td left><?= $val['kegiatan'] ?></td>
					<td center><?= $val['blok_mesin'] ?></td>
					<td left><?= $val['kendaraan'] ?> <?= $val['kode_kendaraan'] ?></td>
					<td left><?= $val['keterangan'] ?></td>

				</tr>
				<?php } ?>
			
				
			</tbody>
			
			<tfoot></tfoot>
		</table>
		




	</body>
</html>


				
