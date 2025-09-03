<!DOCTYPEhtml>
<html>
	<head>
	
		<?php require '__laporan_style.php' ?>
	</head>
	<body>
		
		<?php require '__laporan_header.php' ?>

		<!-- <pre><?php print_r($po) ?></pre> -->

		<h1 class="title">REKAP HK & PREMI MANDOR KERANI</h1>
		<br>
		
		<div class="d-flex flex-between">
			<table class="" style="width:30%">
				
				<tr>
					<td>Periode Tanggal</td>
					<th>:</th>
					<td><?= tgl_indo($filter_tgl_awal) .' s/d '. tgl_indo($filter_tgl_akhir) ?></td>
				</tr>
				<tr>
					<td>Estate</td>
					<th>:</th>
					<td><?= $filter_estate ?></td>
				</tr>
				<tr>
					<td>Afdeling</td>
					<th>:</th>
					<td><?= $filter_afdeling ?></td>
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
					<th rowspan=2 style="width:2%">no</th>
					<th rowspan=2 style="width:5%">Tanggal</th>

					<th colspan=3>mandor panen</th>
					<th colspan=3>mandor pemeliharaan</th>
					<th colspan=3>kerani panen</th>
					<th colspan=3>kerani pemeliharaan</th>
					<tr>
					<th style="width:5%" >HK PNN</th>
					<th style="width:5%" >HK RP</th>
					<th style="width:5%" >PREMI</th>
					
					<th style="width:5%" >HK PML</th>
					<th style="width:5%" >HK RP</th>
					<th style="width:5%" >PREMI</th>
					
					<th style="width:5%" >HK PNN</th>
					<th style="width:5%" >HK RP</th>
					<th style="width:5%" >PREMI</th>
					

					<th style="width:5%" >HK PML</th>
					<th style="width:5%" >HK RP</th>
					<th style="width:5%" >PREMI</th>

					</tr>
				</tr>

			</thead>

			
			<tbody>
			<?php $no= 0; 
			$jml_hk_m_pnn=0;
			$jml_rp_hk_m_pnn=0;
			$jml_premi_m_pnn=0;

			$jml_hk_m_pml=0;
			$jml_rp_hk_m_pml=0;
			$jml_premi_m_pml=0;

			$jml_hk_k_pnn=0;
			$jml_rp_hk_k_pnn=0;
			$jml_premi_k_pnn=0;

			$jml_hk_k_pml=0;
			$jml_rp_hk_k_pml=0;
			$jml_premi_k_pml=0;
			?>
			<?php foreach ($bkm as $key=>$val) { ?> 
				
				<?php $no= $no+1;
				$jml_hk_m_pnn=$jml_hk_m_pnn+$val['hk_m_panen'];
				$jml_rp_hk_m_pnn=$jml_rp_hk_m_pnn+$val['rp_m_panen'];
				$jml_premi_m_pnn=$jml_premi_m_pnn+$val['premi_m_panen'];

				$jml_hk_m_pml=$jml_hk_m_pml+$val['hk_m_pml'];
				$jml_rp_hk_m_pml=$jml_rp_hk_m_pml+$val['rp_m_pml'];
				$jml_premi_m_pml=$jml_premi_m_pml+$val['premi_m_pml'];

				$jml_hk_k_pnn=$jml_hk_k_pnn+$val['hk_k_panen'];
				$jml_rp_hk_k_pnn=$jml_rp_hk_k_pnn+$val['rp_k_panen'];
				$jml_premi_k_pnn=$jml_premi_k_pnn+$val['premi_k_panen'];

				$jml_hk_k_pml=$jml_hk_k_pml+$val['hk_k_pml'];
				$jml_rp_hk_k_pml=$jml_rp_hk_k_pml+$val['rp_k_pml'];
				$jml_premi_k_pml=$jml_premi_k_pml+$val['premi_k_pml'];

				?>
				<tr>
					<td><?= $no ?></td>
					<td center><?= $val['tanggal'] ?></td>
					<td center><?= number_format($val['hk_m_panen'],2)?></td>
					<td center><?= number_format($val['rp_m_panen'],2)?></td>
					<td center><?= number_format($val['premi_m_panen'],2)?></td>

					<td center><?= number_format($val['hk_m_pml'],2)?></td>
					<td center><?= number_format($val['rp_m_pml'],2)?></td>
					<td center><?= number_format($val['premi_m_pml'],2)?></td>

					<td center><?= number_format($val['hk_k_panen'],2)?></td>
					<td center><?= number_format($val['rp_k_panen'],2)?></td>
					<td center><?= number_format($val['premi_k_panen'],2)?></td>
					
					<td center><?= number_format($val['hk_k_pml'],2)?></td>
					<td center><?= number_format($val['rp_k_pml'],2)?></td>
					<td center><?= number_format($val['premi_k_pml'],2)?></td>

				</tr>
				<?php } ?>
				<tr>
					<td colspan='2' center >JUMLAH</td>
					<td center><?= number_format($jml_hk_m_pnn,2)?></td>
					<td center><?= number_format($jml_rp_hk_m_pnn,2)?></td>
					<td center><?= number_format($jml_premi_m_pnn,2)?></td>

					<td center><?= number_format($jml_hk_m_pml,2)?></td>
					<td center><?= number_format($jml_rp_hk_m_pml,2)?></td>
					<td center><?= number_format($jml_premi_m_pml,2)?></td>
					

					<td center><?= number_format($jml_hk_k_pnn,2)?></td>
					<td center><?= number_format($jml_rp_hk_k_pnn,2)?></td>
					<td center><?= number_format($jml_premi_k_pnn,2)?></td>
					
					<td center><?= number_format($jml_hk_k_pml,2)?></td>
					<td center><?= number_format($jml_rp_hk_k_pml,2)?></td>
					<td center><?= number_format($jml_premi_k_pml,2)?></td>
				</tr>			
			</tbody>
			
			<tfoot></tfoot>
		</table>
		





		<pre><?php //print_r($headeran) ?></pre>

	</body>
</html>


				
