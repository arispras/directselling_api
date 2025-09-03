<!DOCTYPEhtml>
	<html>

	<head>

		<?php require '__laporan_style.php' ?>
		<h1 left class="title">KLINIK ANNAJAH </h1>
	</head>


	<body>
		<br>
		<h1 class="title">BAPP ANGKUTAN CPO/PK</h1>
		<h4 class="title"><?= $hd['no_rekap'] ?></h4>
		<br><br><br><br><br><br><br><br><br><br>
		<table>
			<tr>
				<td center>
					<img src="data:image/png;base64,<?= base64_encode(file_get_contents(base_url('logo_perusahaan.png'))) ?>" height="140" width="600px">
				</td>
			</tr>


		</table>

		<br><br><br><br><br><br>


		<h5 center>DPA MILL &nbsp;<?= date('Y', strtotime($hd['tanggal'])) ?></h5>
	</body>

	</html>
