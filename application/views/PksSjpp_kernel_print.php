<!DOCTYPEhtml>
<html>
<head>
    
    <?php require '__laporan_style.php' ?>
    <style>
        body { font-size:12px; }
    </style>
</head>
<body>


    <div style="border:1px solid black; padding:10px">


        <table>
            <tr>
                <td left>
                    <small>PT. PALM MAS ASRI</sma>
                </td>
                <td right>
                    <?= tgl_indo($data['tanggal']) ?>
                </td>
            </tr>
        </table>

        
        <div center>
            <h1 class="m-0">SURAT PENGANTAR KERNEL</h1>
            <div>No. : <?= $data['no_surat'] ?></div>
        </div>
        
        <div>
            <p>
                <b>Kepada Yth :</b><br>
                <?= $data['nama_customer'] ?>
            </p>
            <p>
                <b>Ditempat : </b><br>
                Apabila kernel yang kami kirim telah diterima dalam keadaan baik dan cukup,
                agar copy surat pengantar ini dikembalikan kepada kami setelah di tanda tangani.
            </p>
        </div>
        <br>



        <table class="c-table" style="border:1px solid black; border-bottom:0px solid black; margin-bottom:0;">
            <tr>
                <th left width='150px'>Nama Sopir</th>
                <td width='5%'>:</td>
                <td><?= $data['nama_supir'] ?></td>
            </tr>
            <tr>
                <th left>No. KTP/SIM</th>
                <td>:</td>
                <td><?= $data['no_ktp_sim'] ?></td>
            </tr>
            <tr>
                <th left>No. Kendaraan</th>
                <td>:</td>
                <td><?= $data['no_kendaraan'] ?></td>
            </tr>
            <tr><td colspan="3" style="border-bottom:1px solid black"></td></tr>
            <tr>
                <th left width='150px'>No. DO</th>
                <td width='5%'>:</td>
                <td><?= $data['no_do'] ?></td>
            </tr>
            <tr>
                <th left>No. IP</th>
                <td>:</td>
                <td><?= $data['no_ip'] ?></td>
            </tr>
            <tr>
                <th left>No. Karcis</th>
                <td>:</td>
                <td><?= $data['no_karcis'] ?></td>
            </tr>
            <tr>
                <th left>No. Segel</th>
                <td>:</td>
                <td>
                    <?php $no_segel = explode(',', $data['no_segel']); ?>
                    <div style="width:100%;">
                        <?php foreach($no_segel as $key=>$val) { ?>
                        <?php $no+=1; ?>
                        <div style="display:inline; padding:5px; padding-right:30%; text-align:">
                            <?=$no?>. <?= $val ?>
                        </div>
                        <?php } ?>
                    </div>
                </td>
            </tr>
        </table>
        <table class="c-table" style="border:0px solid black; border-top:0px solid black;">
            <tr>
                <th style="border:1px solid black">Berat (kg)</th>
                <th style="border:1px solid black">Dikirim (kg)</th>
                <th style="border:1px solid black">Diterima (kg)</th>
            </tr>
            <tr>
                <td style="border:1px solid black">Bruto</td>
                <td style="border:1px solid black"><?= number_format($data['bruto_kirim']) ?></td>
                <td style="border:1px solid black"></td>
            </tr>
            <tr>
                <td style="border:1px solid black">Tarra</td>
                <td style="border:1px solid black"><?= number_format($data['tara_kirim']) ?></td>
                <td style="border:1px solid black"></td>
            </tr>
            <tr>
                <td style="border:1px solid black">Netto</td>
                <td style="border:1px solid black"><?= number_format($data['netto_kirim']) ?></td>
                <td style="border:1px solid black"></td>
            </tr>
            <tr>
                <td style="border:1px solid black">FFA</td>
                <td style="border:1px solid black"><?= $data['ffa'] ?></td>
                <td style="border:1px solid black"></td>
            </tr>
            <tr>
                <td style="border:1px solid black">Kadar Air</td>
                <td style="border:1px solid black"><?= $data['moisture'] ?></td>
                <td style="border:1px solid black"></td>
            </tr>
            <tr>
                <td style="border:1px solid black">Kadar Kotoran</td>
                <td style="border:1px solid black"><?= $data['dirt'] ?></td>
                <td style="border:1px solid black"></td>
            </tr>
        </table>

        <table class="tab-expand">
            <tr>
                <th>MM /KTU /Asst.</th>
                <th>Supir</th>
                <th>Penerima</th>
            </tr>
            <tr>
                <td style="height:20px"></td>
                <td style="height:20px"></td>
                <td style="height:20px"></td>
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

        
        <br><br>
        <hr>

        <small>
            <b>Catatan : </b> <br>
            <div style="font-size:11px">Apabila ada kelainan atau perbedaan yang mencolok terhadap hasil timbangan. harap hubungi kami secepatnya</div>
        </small>




        <!-- <table class="c-table head">
            <tr>
                <td>
                    <div>Jln. Pluit Permai Ruko No. 21-23 Pluit Village</div>
                    <div>Jakarta 14440-INDONESIA</div>
                    <div>Telp.6211 668 4055 (Hunting)</div>
                    <div>Fax.6211 668 4055</div>
                </td>
                <td>
                    <table class="c-table">
                        <tr>
                            <th left>No.</th>
                            <td width="5%">:</td>
                            <td>123/PMA/I/2020</td>
                        </tr>
                        <tr>
                            <th left>Tanggal</th>
                            <td>:</td>
                            
                        </tr>
                    </table>
                </td>
                <td width="5%">
                    <h4>Kepada Yth, Bapak/Ibu/PT</h4>
                    <div>PT. Energi Unggul Persada</div>
                </td>
            </tr>
        </table>
         -->
        <!-- <div class="clearfix c-table">
            <table style="width:50%; float:left;">
                <tr>
                    <th left width='150px'>Nama Barang</th>
                    <td width='5%'>:</td>
                    <td></td>
                </tr>
                <tr>
                    <th left>No. Kontrak</th>
                    <td>:</td>
                    <td></td>
                </tr>
                <tr>
                    <th left>No. IP</th>
                    <td>:</td>
                    <td></td>
                </tr>
                <tr>
                    <th left>No. Mobil</th>
                    <td>:</td>
                    <td></td>
                </tr>
                <tr>
                    <th left>Bruto Kirim</th>
                    <td>:</td>
                    <td></td>
                    <th>Kg</th>
                </tr>
                <tr>
                    <th left>Tara Kirim</th>
                    <td>:</td>
                    <td></td>
                    <th>Kg</th>
                </tr>
                <tr>
                    <th left>Netto Kirim</th>
                    <td>:</td>
                    <td></td>
                    <th>Kg</th>
                </tr>
            </table>
            <div style="width:50%; float:left;">
                <table>
                    <tr>
                        <th left width='150px'>Segel No</th>
                        <td width='10px'>:</td>
                        <td>
                            <ul type="1">
                                <li>0001</li>
                                <li>0002</li>
                                <li>0003</li>
                            </ul>
                        </td>
                    </tr>
                </table>
                <table style="border:1px solid black !important">
                    <tr>
                        <th left width='150px'>FFA</th>
                        <td width='10px'>:</td>
                        <td></td>
                    </tr>
                    <tr>
                        <th left>MOISTURE</th>
                        <td>:</td>
                        <td></td>
                    </tr>
                    <tr>
                        <th left>DIRT</th>
                        <td>:</td>
                        <td></td>
                    </tr>
                    <tr>
                        <th left>DOBI</th>
                        <td>:</td>
                        <td></td>
                    </tr>
                </table>
            </div>
        </div>
 -->
        <!-- <table class="tab-expand">
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
                <td><center>(..............................)</center></td>
                <td><center>(..............................)</center></td>
            </tr>
        </table> -->



    </div>

</body>
</html>