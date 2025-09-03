<!DOCTYPEhtml>
<html>
<head>

    <?php require '__laporan_style.php' ?>
</head>
<body>


    <div style="border:1px solid black; padding:10px">
        <table class="c-table head">
            <tr>
                <td>
                    <h3>PT. PALM MAS ASRI</h3>
                    <div>Jln. Pluit Permai Ruko No. 21-23 Pluit Village</div>
                    <div>Jakarta 14440-INDONESIA</div>
                    <div>Telp.6211 668 4055 (Hunting)</div>
                    <div>Fax.6211 668 4055</div>
                </td>
                <td>
                    <!-- <br><br><br> -->
                    <h1>SURAT JALAN</h1>
                    <table class="c-table">
                        <tr>
                            <th left>No.</th>
                            <td width="5%">:</td>
                            <td><?= $data['no_surat'] ?></td>
                        </tr>
                        <tr>
                            <th left>Tanggal</th>
                            <td>:</td>
                            <td><?= tgl_indo($data['tanggal']) ?></td>
                        </tr>
                    </table>
                </td>
                <td width="5%">
                    <h4>Kepada Yth, Bapak/Ibu/PT</h4>
                    <div><?= $data['nama_customer'] ?></div>
                </td>
            </tr>
        </table>
        
        <div class="clearfix c-table">
            <table style="width:50%; float:left;">
                <tr>
                    <th left width='150px'>Nama Barang</th>
                    <td width='5%'>:</td>
                    <td><?= $data['nama_barang'] ?></td>
                </tr>
                <tr>
                    <th left>No. Kontrak</th>
                    <td>:</td>
                    <td><?= $data['no_kontrak'] ?></td>
                </tr>
                <tr>
                    <th left>No. IP</th>
                    <td>:</td>
                    <td><?= $data['no_ip'] ?></td>
                </tr>
                <tr>
                    <th left>No. Mobil</th>
                    <td>:</td>
                    <td><?= $data['no_kendaraan'] ?></td>
                </tr>
                <tr>
                    <th left>Bruto Kirim</th>
                    <td>:</td>
                    <td><?= $data['bruto_kirim'] ?></td>
                    <th>Kg</th>
                </tr>
                <tr>
                    <th left>Tara Kirim</th>
                    <td>:</td>
                    <td><?= $data['tara_kirim'] ?></td>
                    <th>Kg</th>
                </tr>
                <tr>
                    <th left>Netto Kirim</th>
                    <td>:</td>
                    <td><?= $data['netto_kirim'] ?></td>
                    <th>Kg</th>
                </tr>
            </table>
            <div style="width:50%; float:left;">
                <table>
                    <tr>
                        <th left width='150px'>Segel No</th>
                        <td width='10px'>:</td>
                        <td>
                            <?php $no_segel = explode(',', $data['no_segel']); ?>
                            <ul type="1">
                                <?php foreach($no_segel as $key=>$val) { ?>
                                <li><?= $val ?></li>
                                <?php } ?>
                            </ul>
                        </td>
                    </tr>
                </table>
                <table style="border:1px solid black !important">
                    <tr>
                        <th left width='150px'>FFA</th>
                        <td width='10px'>:</td>
                        <td><?= $data['ffa'] ?></td>
                    </tr>
                    <tr>
                        <th left>MOISTURE</th>
                        <td>:</td>
                        <td><?= $data['moisture'] ?></td>
                    </tr>
                    <tr>
                        <th left>DIRT</th>
                        <td>:</td>
                        <td><?= $data['dirt'] ?></td>
                    </tr>
                    <tr>
                        <th left>DOBI</th>
                        <td>:</td>
                        <td><?= $data['dobi'] ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <table class="tab-expand">
            <tr>
                <th>Hormat Kami</th>
                <th>Sopir</th>
                <th>Penerima</th>
            </tr>
            <tr>
                <td style="height:50px"></td>
                <td style="height:50px"></td>
                <td style="height:50px"></td>
            </tr>
            <tr>
                <td><center>(..............................)</center></td>
                <td>
                    <center><?= $data['nama_supir'] ?></center>
                    <center>(..............................)</center>
                </td>
                <td><center>(..............................)</center></td>
            </tr>
        </table>
    </div>

</body>
</html>