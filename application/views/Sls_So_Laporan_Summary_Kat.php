<!DOCTYPEhtml>
<html>
	<head>
	<title>Sales Order Summary</title>
	<link rel="icon" type="image/png" href="<?= base_url('logo_antech.png') ?>" />
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
				font-size: 11px ;
			}
			
		</style>
	</head>
	<body>
	<?php 
			if ($format_laporan=='view') {
				require '__laporan_header.php';
			}
			elseif ($format_laporan=='pdf') {
				require '__laporan_header.php';
			}
			else{	echo'-';
			}
		?>

		
		<h3 class="title">Sales Order SUMMARY KATEGORI</h3>
		<br>
		<!-- <pre><?php print_r($so); ?></pre> -->
		<div class="d-flex flex-between">
			<table class="no_border"  style="width:30%">
				<tr>
					<td style="width:25%">Periode Tanggal</td>
					<th>:</th>
					<td><?= tgl_indo($filter_tgl_awal) .' s/d '. tgl_indo($filter_tgl_akhir) ?></td>
				</tr>
			
			</table>	
		</div>
		<br>

		<!-- table 1 -->
		<table class="table-bg border">				
				<thead>
					<tr>
					<th rowspan=2 >No</th>
					<th rowspan=2 >Kategori</th>
					<th colspan=12 > <?= $tahun ?>  </th>
					</tr>

					<tr>
					<?php for ($i=1; $i < (12 + 1) ; $i++) { ?>
							<th width="6%"> <?php echo (convert_month($i)); ?> </th> 
						<?php 	} ?>
					</tr>

				</thead>


		
			<tbody>
				<?php $no=0; ?>
				<?php foreach ($so as $key=>$val) { ?> 
				<?php $dt=$val['detail'] ;?>
				<?php $no++ ?>
				<tr>
					<td center width="2%"><?= $no ?></td>
					<td colspan=12><?= $val['kategori'] ?></td>

				<?php foreach ($dt as $key=>$value) { ?> 
					<td><?= $value['jml_kat_rp'] ?></td>
				</tr>

				<?php } ?>
				<?php } ?>

			</tbody>
		</table>
		<!-- tutup table -->
			<br>
					<br><hr>
		
			
		<pre><?php //print_r($headeran) ?></pre>

	</body>
</html>


				
