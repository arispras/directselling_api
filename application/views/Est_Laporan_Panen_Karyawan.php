<!DOCTYPEhtml>
<html>
	<head>
	<?php	function format_number_report($angka,$fmt_laporan)
	{
		$format_laporan     =$fmt_laporan;// $this->post('format_laporan', true);
		// if ($fmt_laporan ) {
		// 	$format_laporan     = $this->post('format_laporan', true);
		// }else{
		// 	return number_format($angka);
		// }
		if ($format_laporan == 'xls') {
			return $angka;
		} else {
			if ($angka == 0) {
				return '';
			}
			return number_format($angka);
		}
	}
	?>
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
		<style>
			* body{
				font-size: 8px ;
			}
			
		</style>
	</head>
	<body>
		
		<?php require '__laporan_header.php' ?>

		<h3 class="title">LAPORAN PANEN PERKARYAWAN</h3>
		<br>
		
		<div class="d-flex flex-between">
		<table class="no_border" style="width:30%">
				<tr>
						<td style="width:0%">Lokasi</td>
						<th>: </th>
						<td style="width:60%"><?= $filter_lokasi ?></td>
				</tr>
			
				<tr>
					<td>Tanggal</td>
					<th>: </th>
					<td><?= tgl_indo($filter_tgl_awal) .' s/d '. tgl_indo($filter_tgl_akhir) ?></td>
				</tr>
				
				

			</table>
		</div>
		

		<table class="table-bg border">
			<thead>
				<tr>
					<th rowspan=2>No</th>
					<th rowspan=2>Tanggal</th>
					<th rowspan=2>Karyawan</th>
					<th rowspan=2>Mandor</th>
					<th rowspan=2>Blok</th>
					<th rowspan=2>Tahun Tanam</th>
					<th rowspan=2>BJR</th>
					<th rowspan=2>HK </th>
					<th rowspan=2>HK(Rp)</th>
					<th colspan=2>Hasil</th>
					<th colspan=2>Premi Basis </th>		
					<th colspan=2>Premi Lebih Basis</th>
					<th rowspan=2>Premi Panen</th>					
					<th colspan=2>Premi Brondolan</th>		
					<th rowspan=2>Jumlah Premi(Rp)</th>
					<th rowspan=2>Denda Panen(Rp)</th>
				</tr>
				<tr>
					<th>JJG</th>
					<th>HA</th>
					
					<th>JJG</th>
					<th>RP</th>

					<th>JJG</th>
					<th>RP</th>

					<th>KG</th>
					<th>RP</th>
				</tr>

			</thead>
<br>
			<tbody>
			<?php $no= 0 ;
				$jumlah_hk=0;
				$rp_hk=0;
				$premi_brondolan=0;
				$premi_basis=0;
				$premi_lebih_basis=0;
				$premi_panen=0;
				$hasil_kerja_jjg=0;
				$hasil_kerja_brondolan=0;
				$hasil_kerja_luas=0;
				$denda_panen=0;
				$basis_jjg=0;
				$lebih_basis_jjg=0;$total_basis_jjg=0;
				?>
				
				<?php foreach ($bkm as $key=>$val) { ?> 

				<?php 
				$no= $no+1 ;
				$jumlah_hk=$jumlah_hk+$val['jumlah_hk'];
				$rp_hk=$rp_hk+$val['rp_hk'];
				$premi_brondolan=$premi_brondolan+$val['premi_brondolan'];
				$premi_basis=$premi_basis+$val['premi_basis'];
				$basis_jjg=$basis_jjg+$val['basis_jjg'];
				$premi_lebih_basis=$premi_lebih_basis+$val['premi_lebih_basis'];
				$premi_panen=$premi_panen+$val['premi_panen'];
				$denda_panen=$denda_panen+$val['denda_panen'];
				$hasil_kerja_jjg=$hasil_kerja_jjg+$val['hasil_kerja_jjg'];
				$hasil_kerja_brondolan=$hasil_kerja_brondolan+$val['hasil_kerja_brondolan'];
				$hasil_kerja_luas=$hasil_kerja_luas+$val['hasil_kerja_luas'];

				$lebih_basis_jjg=$val['hasil_kerja_jjg']-$val['basis_jjg'];
				$total_basis_jjg=$total_basis_jjg+($lebih_basis_jjg<=0? 0 :$lebih_basis_jjg);
				
				?>
				
				<tr>
					
				<td center width="2%"><?= $no ?></td>
					<td center width="6%"><?= tgl_indo_normal($val['tanggal'])  ?></td>
					<td left width="15%"><?= $val['nama_karyawan'] ?></td>
					<td left width="10%"><?= $val['nama_mandor'] ?></td>
					<td center width="5%"><?= $val['nama_blok'] ?></td>
					<td center width="5%"><?= $val['tahuntanam'] ?></td>
					<td center width="3%"><?= $val['bjr'] ?></td>
					
					<td center width="3%"><?= $val['jumlah_hk'] ?></td>
					<td right width="4%"><?= format_number_report($val['rp_hk'],$format_laporan) ?></td>

					<td right width="4%"><?= format_number_report($val['hasil_kerja_jjg'],$format_laporan) ?></td>
					<td right width="3%"><?= $val['hasil_kerja_luas'] ?></td>
					
					<td right width="3%"><?= format_number_report($val['basis_jjg'],$format_laporan) ?></td>
					<td right width="5%"><?= format_number_report($val['premi_basis'],$format_laporan) ?></td>
					
					<td right width="3%"><?= ($lebih_basis_jjg)<=0? 0 :format_number_report($lebih_basis_jjg,$format_laporan) ?></td>
					<td right width="6%"><?= format_number_report($val['premi_lebih_basis'],$format_laporan) ?></td>

					<td right width="8%"><?= format_number_report($val['premi_panen'],$format_laporan) ?></td>

					<td right width="3%"><?= format_number_report($val['hasil_kerja_brondolan'],$format_laporan) ?></td>
					<td right width="6%"><?= format_number_report($val['premi_brondolan'],$format_laporan) ?></td>
					
					<td right width="10%"><?= format_number_report(($val['premi_panen']+$val['premi_brondolan']),$format_laporan) ?></td>
					<td right width="8%"><?= format_number_report($val['denda_panen'],$format_laporan) ?></td>
					
					 
				</tr>
				<?php } ?>
				<tr>
					
					<td colspan='7' center >JUMLAH</td>
					
					<td center width="3%"><?=  format_number_report($jumlah_hk,$format_number_report) ?></td>
					<td center width="7%"><?=  format_number_report($rp_hk,$format_number_report) ?></td>

					<td center width="4%"><?=  format_number_report($hasil_kerja_jjg,$format_number_report)?></td>
					<td center width="3%"><?=  format_number_report($hasil_kerja_luas,$format_number_report) ?></td>

					<td center width="3%"><?=  format_number_report($basis_jjg,$format_number_report) ?></td>
					<td center width="5%"><?=  format_number_report($premi_basis,$format_number_report) ?></td>
					
					<td center width="3%"><?=  format_number_report($total_basis_jjg,$format_number_report)?></td>
					<td center width="6%"><?=  format_number_report($premi_lebih_basis,$format_number_report) ?></td>

					<td center width="8%"><?=  format_number_report($premi_panen,$format_number_report) ?></td>
					
					<td center width="3%"><?=  format_number_report($hasil_kerja_brondolan,$format_number_report) ?></td>
					<td center width="6%"><?=  format_number_report($premi_brondolan,$format_number_report) ?></td>
					
			
					<td center width="10%"><?=  format_number_report(($premi_brondolan+$premi_panen),$format_number_report) ?></td>
					<td center width="10%"><?=  format_number_report($denda_panen,$format_number_report) ?></td>
					
					
					 
				</tr>
			</tbody>
			
			<tfoot></tfoot>
		</table>
		





		<pre><?php //print_r($headeran) ?></pre>

	</body>
</html>


				
