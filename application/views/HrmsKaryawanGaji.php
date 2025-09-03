<!DOCTYPEhtml>
<html>
<head>

    <?php require '__laporan_style.php' ?>
    <style>
        * { xbox-sizing:border-box; font-size:92% !important; }
        .slip-row {
            width:100vw;
            /* content: "";
            display: table;
            clear: left; */
            clear:both;
        }
        .slip-row > div {
            width:24%;
            margin-top:0.5%;
            margin-right:0.5%;
            /* float: left; */
            display: inline-block;
            /* border: 1px solid black; */
            /* margin: 0.1%; */
        }
        table {
            padding:2px;
            border:1px solid grey;
        }
    </style>

</head>
<body>


    <div class="slip-row">
        <?php for ($x=0; $x<50; $x++){ ?>
        <div>
            <table>
                <tr>
                    <td>
                        <h6 style="margin:4px 2px; font-size:120% !important;">Slip Penggajian <?=$x?></h6>
                        <table>
                            <tr>
                                <td>NIP</td>
                                <td>:</td>
                                <td>data</td>
                            </tr>
                            <tr>
                                <td>Nama</td>
                                <td>:</td>
                                <td>data</td>
                            </tr>
                            <tr>
                                <td>Jabatan</td>
                                <td>:</td>
                                <td>data</td>
                            </tr>
                            <tr>
                                <td>Departemen</td>
                                <td>:</td>
                                <td>data</td>
                            </tr>
                            <tr>
                                <td>Priode Gaji</td>
                                <td>:</td>
                                <td>data</td>
                            </tr>
                            <tr>
                                <td>Jumlah Masuk (hari)</td>
                                <td>:</td>
                                <td>data</td>
                            </tr>
                            <tr>
                                <td>Jumlah Lembur (jam)</td>
                                <td>:</td>
                                <td>data</td>
                            </tr>
                        </table>
                    </td>
                    <td>
                        <h6 style="margin:0" center>Pendapatan</h6>
                        <table>
                            <tr>
                                <td>Gaji Pokok</td>
                                <td>:</td>
                                <td>data</td>
                            </tr>
                            <tr>
                                <td>Premi</td>
                                <td>:</td>
                                <td>data</td>
                            </tr>
                            <tr>
                                <td>Lembur</td>
                                <td>:</td>
                                <td>data</td>
                            </tr>
                        </table>
                        <h6 style="margin:0" center>Potongan</h6>
                        <table>
                            <tr>
                                <td>Potongan HK</td>
                                <td>:</td>
                                <td>data</td>
                            </tr>
                            <tr>
                                <td>JHT</td>
                                <td>:</td>
                                <td>data</td>
                            </tr>
                            <tr>
                                <td>JP</td>
                                <td>:</td>
                                <td>data</td>
                            </tr>
                            <tr>
                                <td>JKN</td>
                                <td>:</td>
                                <td>data</td>
                            </tr>
                            <tr>
                                <td>PPH 21</td>
                                <td>:</td>
                                <td>data</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
        <?php } ?>
    </div>

</body>
</html>
