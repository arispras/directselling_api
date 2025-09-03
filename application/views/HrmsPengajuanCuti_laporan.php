<!DOCTYPEhtml>
<html>
	<head>
	
		<?php require '__laporan_style.php' ?>
		<style>
			* { font-size:13px !important; }
			.table-bg th, .table-bg td { font-size: 0.9em !important; }
		</style>
	</head>
	<body>

	
		<br>
		<h1 left style="font-size:20px">PENGAJUAN CUTI(IJIN)</h1>
		<table class="table ">
			<tbody>
				<!-- <tr>
					<td left>Lokasi</td>
					<td right><?= $header['lokasi'] ?></td>
				</tr> -->
				<tr>
					<td left style="width:20% ;">Keterangan Cuti</td>
					<td left style="width:2% ;">:</td>
					<td left ><?= $header['cuti'] ?></td>
				</tr>
				<tr>
					<td left>Tanggal Pengajuan</td>
					<td left >:</td>
					<td left><?= tgl_indo($header['tanggal']) ?></td>
				</tr>
				<tr>
					<td left>Karyawan</td>
					<td left >:</td>
					<td left><b><?= $header['karyawan'] .'('.$header['nip'].')'?></b></td>
				</tr>
				<tr>
					<td left>Lokasi/Divisi</td>
					<td left>:</td>
					<td left><?= $header['lokasi'] .'/'.$header['sub_bagian']?></td>
				</tr>
				<tr>
					<td left>Tanggal Cuti/Izin</td>
					<td left>:</td>
					<td left><?= tgl_indo($header['dari_tanggal']) ?> - <?= tgl_indo($header['sampai_tanggal']) ?></td>
				</tr>
				<tr>
					<td left>Jenis Cuti/Ijin</td>
					<td left>:</td>
					<td left>(<?= $header['jenis_absensi_kode'] ?>) <?= $header['jenis_absensi_ket'] ?></td>
				</tr>
				<tr>
					<td left>Saldo Cuti</td>
					<td left>:</td>
					<td left>(<?= $header['saldo'] ?>) </td>
				</tr>

			</tbody>
			<tfoot></tfoot>
		</table>

		<br>
		<br>
		<br>
		<table>
			<tr>
				
				<td center>
					<p bold>Disetujui Oleh</p>
					<br><br><br><br>
					<?php for ($i = 1; $i < 6; $i++) { ?>
						<?php if ($header['user_approve'.$i] != null) { ?>
						<?php $pembayaran[] = $header['user_approve'.$i]; ?>
						<?php $user_jabatan[] = $header['user_approve_jabatan'.$i]; ?>
						<?php } ?>
					<?php } ?>
					<p><?= array_reverse($pembayaran)[0] ?></p>
					<p><?= array_reverse($user_jabatan)[0] ?></p>
				</td>
			</tr>
		</table>


		<pre><?php //print_r($headeran) ?></pre>

	</body>
</html>


				
