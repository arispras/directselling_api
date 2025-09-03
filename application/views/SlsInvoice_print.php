<!DOCTYPEhtml>
    <html>

    <head>
    
        <?php require '__laporan_style.php' ?>
    </head>

    <body>

        <?php require '__laporan_header.php' ?>

        <br>
		<br>
        <h3>INVOICE</h3>

        <table>
            <tr>
                <td width='150px'>Tanggal</td>
                <td width='10px'>:</td>
                <td><?= $Invo['tanggal'] ?></td>
            </tr>
            <tr>
                <td>Customer</td>
                <td>:</td>
                <td><?= $Invo['nama_customer'] ?></td>
            </tr>
            <tr>
                <td>No Invoice</td>
                <td>:</td>
                <td><?= $Invo['no_invoice'] ?></td>
            </tr>
        </table>

        <br><br>


        <table class="tab-expand border">
            <tr>
                <!-- <th style="width:10%">No.</th> -->
                <th style="width:5%">No </th>
                <th>Nama Produk</th>
                <th>Kuantitas</th>
                <th style="width:10%">Harga Satuan</th>
                <th style="width:15%">Sub total</th>
            </tr>
            <tr style="height: 150px; text-align:right;">
                <td><?= $Invo['id'] ?></td>
                <td>CPO</td>
                <td><?= $Invo['jumlah'] ?></td>
                <td><?= $Invo['harga_satuan'] ?></td>
                <td><?= $Invo['sub_total'] ?></td>

            </tr>
            <tr>
                <td  colspan="4">Sub Total</td>
                <td style="text-align:right;"><?= $Invo['sub_total'] ?></td>
            </tr>
            <tr>
                <td colspan="4">Potongan Harga</td>
                <td style="text-align:right;"><?= $Invo['disc'] ?></td>
            </tr>
            <tr>
                <td colspan="4">Uang Muka</td>
                <td style="text-align:right;"><?= $Invo['uang_muka'] ?></td>
            </tr>
            <tr>
                <td colspan="4">Dasar Pengenaan Pajak (DPP)</td>
                <td style="text-align:right;"><?= $Invo['dpp'] ?></td>
            </tr>
            <tr>
                <td colspan="4">PPN 10%</td>
                <td style="text-align:right;"><?= $Invo['ppn'] ?></td>
            </tr>
            <tr style="text-align:right;">
                <td colspan="4">Total Tagihan</td>
                <td style="text-align:right;"><?= $Invo['grand_total'] ?></td>
            </tr>


        </table>




        <br><br><br><br>



        <table class="tab-expand">
            <tr>
                <th>Yang Menerima</th>
                <th>Yang Menyerahkan</th>
            </tr>
            <tr>
                <td style="height:90px"></td>
                <td style="height:90px"></td>
            </tr>
            <tr>
                <td>
                    <center>(..............................)</center>
                </td>
                <td>
                    <center>(..............................)</center>
                </td>
            </tr>
        </table>


    </body>

    </html>
