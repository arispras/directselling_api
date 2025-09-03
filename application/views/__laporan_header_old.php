
<?php $logo = file_get_contents('./logo_perusahaan.png'); ?>
<?php $logo = base64_encode($logo); ?>

<?php $block = false; ?>

<div>
  <div class="kop-print">
    <!-- <img src="data:image/png;base64,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" alt="image"> -->
    
    <?php if ($block == true) { ?>
    <img src="data:image/png;base64, <?= $logo ?>" alt="image" width="20%" height="auto">
    <?php } ?>

    <!-- <div class="kop-nama">KLINIK ANNAJAH</div>
    <div class="kop-info"> Jl Sukarno Hatta No 12 No 12, Bandung Jawa Barat 14450</div>
    <div class="kop-info">Telp : (021) 6684055</div> -->
		<div class="kop-nama"><?= get_company()['nama'] ?></div>
    <div class="kop-info"> <?= get_company()['alamat'] ?></div>
    <div class="kop-info">Telp : <?= get_company()['telp'] ?></div>
  </div>
  <hr class="kop-print-hr">
</div>
