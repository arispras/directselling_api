<!DOCTYPEhtml>
	<html>

	<head>
		
		<?php require '__laporan_style.php' ?>
	</head>

	<body>



		<h1 class="title">INVOICE</h1>
		<h4 class="title"><?= $hd['no_invoice'] ?></h4>
		<br>

		
		<table class="table-bg border">
			<thead>
				<tr>
					<th rowspan="2">No</th>
					<th rowspan="2">Pekerjaan</th>
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
						<td center width="10%"><?= $hd['keterangan']  ?></td>
						 <!-- <td center width="10%">Angkut CPO/Kernel</td> -->
						<td center width="10%">Kg</td>
						<td right width="10%"><?= number_format($val['qty']) ?></td>
						<td right width="10%"><?= number_format($val['harga']) ?></td>
						<td right width="10%"><?= number_format($val['harga']*$val['qty'] ) ?></td>

					</tr>
				<?php } ?>
				<tr><td colspan="4">No SPK:<?= $hd['no_spk'] ?></td><td >Jumlah</td>	<td right width="10%"><?= number_format($hd['sub_total']) ?></td></tr>
				<tr><td colspan="4"><?= $hd['no_spk'] ?></td><td>Potongan</td>	<td right width="10%"><?= number_format($hd['potongan']) ?></td></tr>
				<tr><td colspan="4">BAPB Tanggal:<?= tgl_indo($hd['tanggal_rekap']) ?></td><td>PPN</td>	<td right width="10%"><?= number_format($hd['ppn']/100*($hd['sub_total']-$hd['potongan'])) ?></td></tr>
				<tr><td  colspan="4"></td><td>PPH</td>	<td right width="10%"><?= number_format($hd['pph']/100*($hd['sub_total']-$hd['potongan'])) ?></td></tr>
				<tr><td  colspan="4"></td><td>Jumlah Tagihan</td>	<td right width="10%"><?= number_format($hd['total_tagihan']) ?></td></tr>
				<tr><td  colspan="4"></td><td>grand Total</td>	<td right width="10%"><?= number_format($hd['total_tagihan']) ?></td></tr>
				
			</tbody>

			<tfoot>
				
			
			</tfoot>
		</table>
		<table class="table-bg border"><tr><td>Terbilang:#<?= $hd['terbilang'] ?>&nbsp; Rupiah#</td></tr></table>
		<table class="table-bg border">
			<tr>				
				<td center>
					<p>Keterangan Mohon ditransfer Ke</p>
					<table>
						<tr>
							<td width="10%">Nama Bank</td>
							<td width="20%"><?= $hd['nama_bank'] ?></td>
						</tr>
						<tr>
							<td>Cabang</td>
							<td><?= $hd['cabang_bank'] ?></td>
						</tr>
						<tr>
							<td>No Rekening</td>
							<td><?= $hd['no_rekening'] ?></td>
						</tr>
						<tr>
							<td>Atas Nama</td>
							<td><?= $hd['atas_nama'] ?></td>
						</tr>
					</table>
				</td>
				<td center>
					<p>DPA MILL, <?= tgl_indo($hd['tanggal']) ?></p>
					<br><br><br><br><br><br><br>
					<div>
						<div><?= $hd['nama_supplier'] ?></div>
						<div></div>
					</div>
				</td>
				<!-- <td center>
					<p>Diketahui Oleh</p>
					<br><br><br><br>
					<div>
						<div>( &nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)</div>
						<div></div>
					</div>
				</td> -->
			</tr>
		</table>






		<pre><?php //print_r($hdan) 
				?></pre>

	</body>

	</html>
