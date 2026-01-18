<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
require 'vendor/autoload.php';

use Dompdf\Adapter\CPDF;
use Dompdf\Dompdf;
use Dompdf\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;
use Dompdf\Positioner\NullPositioner;
use Restserver\Libraries\REST_Controller;

class ColKuitansi extends BD_Controller
{
	public $user_id;
	public $theCredential;

	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('ColKuitansiModel');
		$this->load->model('M_DatatablesModel');
		$this->load->library('pdfgenerator');
		$this->load->library('email');
		$this->load->library('image_lib');
		$this->load->library('upload');

		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id = $this->user_data->id;
	}

	public function list_post()
	{
		$post = $this->post();
		$param = $post['parameter'];

		$query = "SELECT a.*,d.nama as lokasi, b.kode_customer,b.nama_customer,c.nama as collector ,ifnull(kuitansi.dibayar,0)AS dibayar,
		(nilai_angsuran - ifnull(kuitansi.dibayar,0))AS nilai_sisa_angsuran
		FROM col_kuitansi_ht a 
		INNER join gbm_organisasi d on a.lokasi_id=d.id		 
		INNER JOIN gbm_customer b 	ON a.customer_id=b.id 
		LEFT join karyawan c on a.collector_id=c.id
		LEFT join (SELECT b.kuitansi_id,sum(b.dibayar)AS dibayar 
		FROM col_lhi_ht a INNER JOIN col_lhi_dt b ON a.id=b.lhi_id
		GROUP BY b.kuitansi_id) kuitansi 
		ON a.id=kuitansi.kuitansi_id ";

		$search = array('a.no_kuitansi', 'a.tanggal_tempo', 'b.nama_customer', 'c.nama', 'd.nama');
		$where  = null;

		$isWhere = " 1=1 ";
		if ($param['tgl_mulai'] && $param['tgl_mulai']) {
			$isWhere = " a.tanggal_tempo between '" . $param['tgl_mulai'] . "' and '" . $param['tgl_akhir'] . "'";
		}
		if (!empty($param['customer_id'])) {
			$isWhere = $isWhere .  "  and a.customer_id=" . $param['customer_id'] . "";
		}
		if ($param['lokasi_id']) {
			$isWhere = $isWhere . " and a.lokasi_id =" . $param['lokasi_id'] . "";
		} else {
			$isWhere = $isWhere . " and  a.lokasi_id in
			(select location_id from fwk_users_location where user_id=" . $this->user_id . ")";
		}

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);

		$this->set_response($data, REST_Controller::HTTP_OK);
	}

	function index_get($segment_3 = '')
	{
		$id = $segment_3;
		// $retrieve = $this->ColKuitansiModel->retrieve($id);
		$query = "SELECT a.*,d.nama as lokasi, b.kode_customer,b.nama_customer,c.nama as collector ,ifnull(kuitansi.dibayar,0)AS dibayar,
		(nilai_angsuran - ifnull(kuitansi.dibayar,0))AS nilai_sisa_angsuran
		FROM col_kuitansi_ht a 
		INNER join gbm_organisasi d on a.lokasi_id=d.id		 
		INNER JOIN gbm_customer b 	ON a.customer_id=b.id 
		LEFT join karyawan c on a.collector_id=c.id
		LEFT join (SELECT b.kuitansi_id,sum(b.dibayar)AS dibayar 
		FROM col_lhi_ht a INNER JOIN col_lhi_dt b ON a.id=b.lhi_id
		GROUP BY b.kuitansi_id) kuitansi 
		ON a.id=kuitansi.kuitansi_id where a.id=" . $id . ";";

		$retrieve = $this->db->query($query)->row_array();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function getAll_get()
	{

		$retrieve = $this->ColKuitansiModel->retrieve_all_kategori();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}


	function getLHIKuitansiBlmLunas_post()
	{
		$collector_id = $this->post('collector_id');
		$tanggal_mulai =  $this->post('tanggal_mulai', true);
		$tanggal_akhir =  $this->post('tanggal_akhir', true);
		$lokasi_id =  $this->post('lokasi_id', true);

		$sql = "SELECT a.*,b.kode_customer,b.nama_customer,nilai_angsuran  ,ifnull(kuitansi.dibayar,0)AS dibayar,
		(nilai_angsuran - ifnull(kuitansi.dibayar,0))AS sisa_akhir
		 FROM col_kuitansi_ht a INNER JOIN gbm_customer b 
		ON a.customer_id=b.id 
				 left join (SELECT b.kuitansi_id,sum(b.dibayar)AS dibayar 
				FROM col_lhi_ht a INNER JOIN col_lhi_dt b ON a.id=b.lhi_id
				GROUP BY b.kuitansi_id) kuitansi ON a.id=kuitansi.kuitansi_id
				WHERE  1=1 
				and a.lokasi_id=" . $lokasi_id . " and ((
		tanggal_tempo between '" . $tanggal_mulai . "' and '" . $tanggal_akhir . "')or 
		(tanggal_janji between '" . $tanggal_mulai . "' and '" . $tanggal_akhir . "'))
		and collector_id=" . $collector_id . " and (nilai_angsuran > ifnull(kuitansi.dibayar,0))
		and a.angsuran_ke>1
		order by a.no_kuitansi";
		$retrieve =	$this->db->query($sql)->result_array();

		if (!empty($retrieve)) {

			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => []), REST_Controller::HTTP_OK);
		}
	}
	function index_post()
	{
		$input = $this->post();
		$input['diubah_oleh'] = $this->user_id;
		$input['dibuat_oleh'] = $this->user_id;
		$this->load->library('Autonumber');
		$input['no_lhi'] = $this->autonumber->lhi($input['lokasi_id']['id'], $input['tanggal']);
		$res =  $this->ColKuitansiModel->create($input);
		// $this->set_response(array("status" => "OK", "data" => $input['no_lhi']), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'sls_so', 'action' => 'new', 'entity_id' => $res, 'key_text' => $input['no_ttb']);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $input['no_lhi']), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function updateCollectorTanggalJanji_put($segment_3 = '')
	{
		$input = $this->put();
		$input['diubah_oleh'] = $this->user_id;
		$id = (int)$segment_3;
		$kuitansi = $this->ColKuitansiModel->retrieve($id);
		if (empty($kuitansi)) {
			$this->set_response(array("status" => "NOT OK", "data" => []), REST_Controller::HTTP_OK);
		}

		$res =   $this->ColKuitansiModel->updateCollectorTanggalJanji($kuitansi['id'], $input);
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'kuitansi_ht', 'action' => 'edit', 'entity_id' => $id, 'key_text' => $so['id']);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function update_invoice_put($segment_3 = '')
	{
		$input = $this->put();
		$input['diubah_oleh'] = $this->user_id;
		$id = (int)$segment_3;
		$so = $this->ColKuitansiModel->retrieve_invoice_by_id($id);
		if (empty($so)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$res =   $this->ColKuitansiModel->update_invoice($so['id'], $input);
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'sls_so_invoice', 'action' => 'edit', 'entity_id' => $id, 'key_text' => $so['id']);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function index_put($segment_3 = '')
	{
		$input = $this->put();
		$input['diubah_oleh'] = $this->user_id;
		$id = (int)$segment_3;
		$so = $this->ColKuitansiModel->retrieve($id);
		if (empty($so)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$res =   $this->ColKuitansiModel->update($so['id'], $input);
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'sls_so', 'action' => 'edit', 'entity_id' => $id, 'key_text' => $so['no_ttb']);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function updateLhiBayar_put($segment_3 = '')
	{
		$input = $this->put();
		$input['diubah_oleh'] = $this->user_id;
		$id = (int)$segment_3;
		$so = $this->ColKuitansiModel->retrieve($id);
		if (empty($so)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$res =   $this->ColKuitansiModel->updateLhiBayar($so['id'], $input);
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'lhi_bayar', 'action' => 'edit', 'entity_id' => $id, 'key_text' => $so['no_ttb']);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}



	function index_delete($segment_3 = '')
	{

		$id = (int)$segment_3;
		$so = $this->ColKuitansiModel->retrieve($id);
		if (empty($so)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$res =  $this->ColKuitansiModel->delete($so['id']);
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id' => $id)), 'entity' => 'sls_so', 'action' => 'delete', 'entity_id' => $id, 'key_text' => $so['no_ttb']);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}



	function print_slip_kuitansi_get($segment_3 = '')
	{

		$id = (int)$segment_3;
		$data = [];

		$queryHeader = "SELECT a.*,
		f.nama_customer,
		a.alamat_pengiriman as alamat_customer,
		a.telp_pengiriman as no_telepon_customer,
		a.contact_pengiriman as contact_person_customer,
		g.jenis as jenis_bayar,
		g.ket as ket_bayar
		FROM sls_ttb_ht a 
		left JOIN gbm_organisasi e ON a.lokasi_id=e.id
		left JOIN gbm_customer f ON a.customer_id=f.id
		left JOIN prc_syarat_bayar g ON a.syarat_bayar_id=g.id
		WHERE a.id=" . $id . "";

		$dataHeader = $this->db->query($queryHeader)->row_array();

		$queryDetail = "SELECT a.*,
		b.kode as kode_barang,
		b.nama as nama_barang,
		f.nama as uom
		FROM sls_so_dt a 
		LEFT join inv_item b on a.item_id=b.id 
		LEFT join gbm_uom f on b.uom_id=f.id 
		WHERE  a.so_hd_id = " . $id . "";
		$dataDetail = $this->db->query($queryDetail)->result_array();

		// foreach ($dataDetail as $key => $value) {
		// 	$stok = $this->InvItemModel->cek_stok_lokasi_get($value['lokasi_pp_id'], $value['item_id'], $dataHeader['tanggal']);
		// 	$dataDetail[$key]['stok'] = $stok;
		// }

		// $queryUser = "SELECT a.*, b.nama as peminta FROM fwk_users a LEFT JOIN karyawan b ON a.employee_id=b.id WHERE a.id=" . $dataHeader['dibuat_oleh'];
		// $dataUser = $this->db->query($queryUser)->row_array();

		// var_dump($dataUser); die;


		// $perminta = $this->InvPermintaanBarangModel->print_slip($id);
		$data['header'] = 	$dataHeader;
		$data['detail'] = 	$dataDetail;
		// $data['user'] = $dataUser;


		$data['database'] = $this->db;

		$html = $this->load->view('SlsTTB_laporan', $data, true);

		$filename = 'report_so_' . time();
		$this->pdfgenerator->generate($html, $filename, true, 'A4', 'portrait');
		// echo $html;
	}

	function print_slip_get($segment_3 = '')
	// 490
	{
		$this->load->helper("terbilangv2");
		$id = (int)$segment_3;
		$data = [];

		$queryHeader = "SELECT a.*,
		f.nama_customer,
		a.alamat_pengiriman as alamat_customer,
		a.telp_pengiriman as no_telepon_customer,
		a.contact_pengiriman as contact_person_customer,
		g.jenis as jenis_bayar,
		g.ket as ket_bayar
		FROM sls_ttb_ht a 
		INNER JOIN gbm_organisasi e ON a.lokasi_id=e.id
		INNER JOIN gbm_customer f ON a.customer_id=f.id
		INNER JOIN prc_syarat_bayar g ON a.syarat_bayar_id=g.id
		WHERE a.id=" . $id . "";
		$dataHeader = $this->db->query($queryHeader)->row_array();

		$queryDetail = "SELECT a.*,
		b.kode as kode_barang,
		b.nama as nama_barang,
		f.nama as uom
		FROM sls_so_dt a 
		LEFT join inv_item b on a.item_id=b.id 
		LEFT join gbm_uom f on b.uom_id=f.id 
		WHERE  a.so_hd_id = " . $id . "";


		$dataDetail = $this->db->query($queryDetail)->result_array();


		$data['header'] = 	$dataHeader;
		$data['detail'] = 	$dataDetail;



		$data['database'] = $this->db;

		// echo (base64_encode(file_get_contents(('./logo_perusahaan.png'))));
		//exit();
		$html = $this->load->view('Sls_slip_so_v3', $data, true);
		$dompdf = new DOMPDF;
		$dompdf->loadHtml($html);
		$dompdf->setPaper('A4', 'portrait');
		$dompdf->render();
		$filename = 'report_' . time();
		$x          = 545;
		$y          = 30;
		$text       = "{PAGE_NUM} / {PAGE_COUNT}";
		$font       = null; // $dompdf->getFontMetrics()->get_font('Helvetica', 'normal');
		$size       = 8;
		$color      = array(0, 0, 0);
		$word_space = 0.0;
		$char_space = 0.0;
		$angle      = 0.0;

		$dompdf->getCanvas()->page_text(
			$x,
			$y,
			$text,
			$font,
			$size,
			$color,
			$word_space,
			$char_space,
			$angle
		);
		$dompdf->stream($filename . ".pdf", array("Attachment" => 0));
		// $html;exit();
		// $filename = 'report_prcpo_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'portrait');
	}

	// ================= TERBILANG =================
	function terbilang($angka)
	{
		$angka = abs($angka);
		$baca = ["", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas"];
		if ($angka < 12) return " " . $baca[$angka];
		elseif ($angka < 20) return terbilang($angka - 10) . " Belas";
		elseif ($angka < 100) return terbilang($angka / 10) . " Puluh" . terbilang($angka % 10);
		elseif ($angka < 1000) return terbilang($angka / 100) . " Ratus" . terbilang($angka % 100);
		elseif ($angka < 1000000) return terbilang($angka / 1000) . " Ribu" . terbilang($angka % 1000);
	}
	function getLaporanCetakKuitansi3_post()
{
   
	
	$format_laporan = $this->post('format_laporan', true);
    $tanggal_awal = $this->post('tgl_mulai', true);
    $tanggal_akhir = $this->post('tgl_akhir', true);
    $lokasi_id = $this->post('lokasi_id');

    $query = "SELECT * from col_kuitansi_vw 
              where tanggal_tempo between '" . $tanggal_awal . "' and '" . $tanggal_akhir . "'    
              and lokasi_id=" . $lokasi_id . " 
              order by tanggal_tempo, no_kuitansi";

    $kuitansi = $this->db->query($query)->result_array();

    $data = [
        'no_kuitansi' => 'KWT-2026-001',
        'terima_dari' => 'PT MAJU JAYA ABADI',
        'untuk'       => 'Pembayaran Jasa Maintenance',
        'nilai'       => 1500000,
        'tanggal'     => date('d-m-Y'),
        'petugas'     => 'ADMIN'
    ];


    // ================= HTML =================
    $html = '
<style>
@page {
    size: A4 portrait;
    margin: 5mm;
}

body {
    font-family: "Times New Roman", serif;
    font-size: 10px;
    margin: 0;
    padding: 0;
    width: 210mm; /* Lebar A4 */
    height: 297mm; /* Tinggi A4 */
}

* {
    box-sizing: border-box;
}

/* Container menggunakan table untuk kompatibilitas DOMPDF */
.table-container {
    width: 100%;
    border-collapse: collapse;
}

.kuitansi-row {
    page-break-inside: avoid;
}

.kuitansi-cell {
    width: 50%;
    height: 62mm;
    padding: 0;
    vertical-align: top;
    page-break-inside: avoid;
}

.kuitansi {
    border: 1px solid #000;
    padding: 3mm;
    height: 100%;
    position: relative;
    margin: 0 2mm;
    page-break-inside: avoid;
}

.header {
    text-align: center;
    font-weight: bold;
    font-size: 12px;
    margin-bottom: 2mm;
    line-height: 1.2;
}

.isi {
    line-height: 1.4;
    margin-bottom: 2mm;
    min-height: 20mm;
}

.nominal {
    font-size: 12px;
    font-weight: bold;
    margin: 3mm 0;
    text-align: center;
}

.footer {
    position: absolute;
    bottom: 3mm;
    left: 3mm;
    right: 3mm;
}

.footer-content {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
}

.footer-left {
    text-align: left;
    width: 40%;
}

.footer-right {
    text-align: right;
    width: 50%;
}

/* Clear float untuk baris baru */
.clearfix::after {
    content: "";
    clear: both;
    display: table;
}

/* Untuk mencegah page break */
.no-break {
    page-break-inside: avoid;
}

/* Media print */
@media print {
    body {
        margin: 0 !important;
        padding: 0 !important;
        width: 210mm !important;
        height: 297mm !important;
    }
    
    .kuitansi {
        border: 1px solid #000 !important;
        page-break-inside: avoid !important;
    }
}
</style>';

    $html .= '<table class="table-container">';
    
    // Baris pertama (kuitansi 1 & 2)
    $html .= '<tr class="kuitansi-row">';
    
    for ($i = 1; $i <= 2; $i++) {
        $html .= '
        <td class="kuitansi-cell">
            <div class="kuitansi">
                <div class="header">KUITANSI</div>
                
                <div class="isi">
                    Sudah terima dari <b>' . $data['terima_dari'] . '</b><br>
                    Uang sejumlah <b>' . $terbilang . '</b><br>
                    Untuk pembayaran <b>' . $data['untuk'] . '</b>
                </div>
                
                <div class="nominal">
                    Rp ' . number_format($data['nilai'], 0, ',', '.') . '
                </div>
                
                <div class="footer">
                    <div class="footer-content">
                        <div class="footer-left">
                            No: KWT-2026-00' . $i . '
                        </div>
                        <div class="footer-right">
                            Jakarta, ' . date('d-m-Y') . '<br><br>
                            <u>ADMIN</u>
                        </div>
                    </div>
                </div>
            </div>
        </td>';
    }
    
    $html .= '</tr>';
    
    // Baris kedua (kuitansi 3 & 4)
    $html .= '<tr class="kuitansi-row">';
    
    for ($i = 3; $i <= 4; $i++) {
        $html .= '
        <td class="kuitansi-cell">
            <div class="kuitansi">
                <div class="header">KUITANSI</div>
                
                <div class="isi">
                    Sudah terima dari <b>' . $data['terima_dari'] . '</b><br>
                    Uang sejumlah <b>' . $terbilang . '</b><br>
                    Untuk pembayaran <b>' . $data['untuk'] . '</b>
                </div>
                
                <div class="nominal">
                    Rp ' . number_format($data['nilai'], 0, ',', '.') . '
                </div>
                
                <div class="footer">
                    <div class="footer-content">
                        <div class="footer-left">
                            No: KWT-2026-00' . $i . '
                        </div>
                        <div class="footer-right">
                            Jakarta, ' . date('d-m-Y') . '<br><br>
                            <u>ADMIN</u>
                        </div>
                    </div>
                </div>
            </div>
        </td>';
    }
    
    $html .= '</tr>';
    $html .= '</table>';

    // ================= GENERATE PDF =================
    $dompdf = new Dompdf();
    
    // Atur options DOMPDF
    $options = $dompdf->getOptions();
    $options->set(array(
        'isHtml5ParserEnabled' => true,
        'isRemoteEnabled' => true,
        'isPhpEnabled' => true,
        'defaultPaperSize' => 'A4',
        'defaultPaperOrientation' => 'portrait',
        'dpi' => 150,
        'isFontSubsettingEnabled' => true
    ));
    $dompdf->setOptions($options);
    
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->loadHtml($html);
    
    try {
        $dompdf->render();
        
        // Output
        if ($format_laporan == 'view' || $format_laporan == 'pdf') {
            $dompdf->stream("kuitansi_1_halaman.pdf", array("Attachment" => 0));
        } else {
            echo $html;
        }
    } catch (Exception $e) {
        // Fallback: versi sederhana jika masih error
        // echo $this->generateSimpleKuitansi($data, $terbilang);
    }
    
    exit();
}

// Fungsi fallback versi sederhana
private function generateSimpleKuitansi($data, $terbilang)
{
    $html = '<style>
    body { font-family: "Times New Roman"; font-size: 10px; }
    .kuitansi { 
        border: 1px solid #000; 
        padding: 5mm; 
        margin-bottom: 5mm;
        width: 90mm;
        height: 62mm;
        float: left;
        margin-right: 5mm;
    }
    .header { text-align: center; font-weight: bold; font-size: 12px; }
    .clear { clear: both; }
    </style>';
    
    for ($i = 1; $i <= 4; $i++) {
        $html .= '
        <div class="kuitansi">
            <div class="header">KUITANSI</div>
            <div>Sudah terima dari <b>' . $data['terima_dari'] . '</b></div>
            <div>Uang sejumlah <b>' . $terbilang . '</b></div>
            <div>Untuk pembayaran <b>' . $data['untuk'] . '</b></div>
            <div style="text-align: center; font-weight: bold; margin: 5mm 0;">
                Rp ' . number_format($data['nilai'], 0, ',', '.') . '
            </div>
            <div style="display: flex; justify-content: space-between;">
                <div>No: KWT-2026-00' . $i . '</div>
                <div>Jakarta, ' . date('d-m-Y') . '<br><u>ADMIN</u></div>
            </div>
        </div>';
        
        if ($i == 2) {
            $html .= '<div class="clear"></div>';
        }
    }
    
    return $html;
}
	function getLaporanCetakKuitansi_post()
	{

		$format_laporan =  $this->post('format_laporan', true);

		// $id = (int)$segment_3;
		$data = [];

		$input = [
			// 'lokasi_id' => 252,
			'periode' => '2022-08',
			// 'tgl_mulai' => '2022-09-01',
			// 'tgl_akhir' => '2022-09-01',
			'format_laporan' => 'view',
		];


		$tanggal_awal = $this->post('tgl_mulai', true);
		$tanggal_akhir = $this->post('tgl_akhir', true);

		$lokasi_id = $this->post('lokasi_id');

		$query = " SELECT *
		from col_kuitansi_vw where tanggal_tempo between  '" . $tanggal_awal . "' and  '" . $tanggal_akhir . "'	
		and lokasi_id=" . $lokasi_id .	" order by tanggal_tempo,no_kuitansi";
		// 	

		$kuitansi = $this->db->query($query)->result_array();


		// var_dump($kuitansi);exit();
		$data['kuitansi'] = 	$kuitansi;

		// $data['filter_lokasi'] = 	$filter_lokasi;
		$data['filter_tgl_awal'] = 	$tanggal_awal;
		$data['filter_tgl_akhir'] = $tanggal_akhir;
		$data['format_laporan'] = $format_laporan;



		// ================= DATA =================
		// $data = [
		// 	'no_kuitansi' => 'KWT-2026-001',
		// 	'terima_dari' => 'PT MAJU JAYA ABADI',
		// 	'untuk'       => 'Pembayaran Jasa Maintenance',
		// 	'nilai'       => 1500000,
		// 	'tanggal'     => date('d-m-Y'),
		// 	'petugas'     => 'ADMIN'
		// ];


		// $terbilang = trim(terbilang($data['nilai'])) . " Rupiah";
    
		// ================= HTML =================
		$html = '
		<style>
		@page {
			size: A4 portrait;
			margin: 8mm;
		}

		body {
			font-family: "Times New Roman", serif;
			font-size: 11px;
		}

		* {
			box-sizing: border-box; /* KUNCI */
		}

		.kuitansi {
			height: 60mm;                 /* FIX */
			border: 1px solid #000;
			padding: 4mm;
		}

		.cut-line {
			border-top: 1px dashed #000;
			margin: 1mm 0;
		}

		.header {
			text-align: center;
			font-weight: bold;
			font-size: 13px;
			margin-bottom: 2mm;
		}

		.isi {
			line-height: 1.4;
		}

		.nominal {
			font-size: 13px;
			font-weight: bold;
			margin-top: 2mm;
		}

		.footer {
			margin-top: 4mm;
			display: table;
			width: 100%;
		}

		.footer .left {
			display: table-cell;
			width: 60%;
		}

		.footer .right {
			display: table-cell;
			text-align: right;
		}
		</style>';
		$counter = 0;
		foreach ($kuitansi as $key => $k) {
			
			for ($i=1; $i <= 2; $i++) { 
				$counter++;
			$terbilag=terbilang($k['nilai_angsuran']);
			$tgl_tempo=tgl_indo($k['tanggal_tempo']);
			$html .= "
			<div class='kuitansi'>
				<div class='header'>KUITANSI</div>

				<div class='isi'>
					Sudah terima dari: <b>{$k['nama_customer']}</b><br>
					Uang sejumlah: <b>{$terbilag} Rupiah</b><br>
					Untuk pembayaran: <b>{$k['keterangan']}</b>
					
				</div>

				<div class='nominal'>
					Rp " . number_format($k['nilai_angsuran'], 0, ',', '.') . "
				</div>

				<div class='footer'>
					<div class='left'>
						No: {$k['no_kuitansi']}
						counter: {$counter}
					</div>
					<div class='right'>
						Jakarta, {$tgl_tempo}<br><br>
						<u>ADMIN</u>
				</div>
				</div>

			</div>";
				

					// ===== GARIS POTONG (kecuali terakhir)
					if ($counter % 4 != 0 || $counter < 4) {
						$html .= "<div class='cut-line'></div>";
					}
			}
				
		}

		

		// exit();


		// $html = $this->load->view('ColKuitansiCetakRekap', $data, true);


		if ($format_laporan == 'xls') {
			echo $html;
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			// ================= GENERATE PDF =================
		$dompdf = new Dompdf();
		$dompdf->setPaper('A4', 'portrait');
		$dompdf->loadHtml($html);
		$dompdf->render();
		$dompdf->stream("kuitansi_{$filename}.pdf", ["Attachment" => false]);
		// exit
			// $dompdf = new DOMPDF;
			// $dompdf->loadHtml($html);
			// $dompdf->setPaper('A4', 'portrait');
			// $dompdf->render();
			// $filename = 'report_' . time();
			// $x          = 400;
			// $y          = 570;
			// $text       = "{PAGE_NUM} of {PAGE_COUNT}";
			// $font       = null; 
			// $size       = 10;
			// $color      = array(0, 0, 0);
			// $word_space = 0.0;
			// $char_space = 0.0;
			// $angle      = 0.0;

			// $dompdf->getCanvas()->page_text(
			// 	$x,
			// 	$y,
			// 	$text,
			// 	$font,
			// 	$size,
			// 	$color,
			// 	$word_space,
			// 	$char_space,
			// 	$angle
			// );
			// $dompdf->stream($filename . ".pdf", array("Attachment" => 0));
		}
	}
	function getLaporanRekapKuitansi_post()
	{

		$format_laporan =  $this->post('format_laporan', true);

		// $id = (int)$segment_3;
		$data = [];

		$input = [
			// 'lokasi_id' => 252,
			'periode' => '2022-08',
			// 'tgl_mulai' => '2022-09-01',
			// 'tgl_akhir' => '2022-09-01',
			'format_laporan' => 'view',
		];


		$tanggal_awal = $this->post('tgl_mulai', true);
		$tanggal_akhir = $this->post('tgl_akhir', true);

		$lokasi_id = $this->post('lokasi_id');

		$query = " SELECT *
		from col_kuitansi_vw where tanggal_tempo between  '" . $tanggal_awal . "' and  '" . $tanggal_akhir . "'	
		and lokasi_id=" . $lokasi_id .	" order by tanggal_tempo,no_kuitansi";
		// 	

		$kuitansi = $this->db->query($query)->result_array();


		// var_dump($kuitansi);exit();
		$data['kuitansi'] = 	$kuitansi;

		// $data['filter_lokasi'] = 	$filter_lokasi;
		$data['filter_tgl_awal'] = 	$tanggal_awal;
		$data['filter_tgl_akhir'] = $tanggal_akhir;
		$data['format_laporan'] = $format_laporan;

		$html = $this->load->view('ColKuitansiCetakRekap', $data, true);


		if ($format_laporan == 'xls') {
			echo $html;
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
			$dompdf = new DOMPDF;
			$dompdf->loadHtml($html);
			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			$filename = 'report_' . time();
			$x          = 400;
			$y          = 570;
			$text       = "{PAGE_NUM} of {PAGE_COUNT}";
			$font       = null; // $dompdf->getFontMetrics()->get_font('Helvetica', 'normal');
			$size       = 10;
			$color      = array(0, 0, 0);
			$word_space = 0.0;
			$char_space = 0.0;
			$angle      = 0.0;

			$dompdf->getCanvas()->page_text(
				$x,
				$y,
				$text,
				$font,
				$size,
				$color,
				$word_space,
				$char_space,
				$angle
			);
			$dompdf->stream($filename . ".pdf", array("Attachment" => 0));
		}
	}

	function laporanso_by_vendor_post()
	{

		/* A.09 Sales Order by Vendor */
		$format_laporan =  $this->post('format_laporan', true);

		// $id = (int)$segment_3;
		$data = [];

		$input = [
			'lokasi_id' => 252,
			'periode' => '2022-08',
			'tgl_mulai' => '2023-08-01',
			'tgl_akhir' => '2023-08-02',
			'format_laporan' => 'view',
		];

		// $lokasi_id = $this->post('lokasi_id', true);
		$periode =  $this->post('periode', true);
		// $tanggal_awal = $this->post('tgl_mulai', true);
		// $tanggal_akhir = $this->post('tgl_akhir', true);
		// $status_id = $this->post('status_id', true);

		// $lokasi_id = $input['lokasi_id'];
		// $periode = $input['periode'];
		// $tgl_mulai = $input['tgl_mulai'];
		// $tgl_akhir = $input['tgl_akhir'];
		// $format_laporan = $input['format_laporan'];



		$date = new DateTime($periode . '-01');
		$date->modify('last day of this month');
		$last_day_this_month = $date->format('Y-m-d');
		(int)$jumhari = date('d', strtotime($last_day_this_month));
		$tgl_mulai = $periode . '-01';
		$tgl_akhir = $periode . '-' . sprintf("%02d", $jumhari);

		$queryhead = "SELECT b.nama_customer as nama_customer, c.customer_id FROM sls_so_dt a
		INNER JOIN sls_ttb_ht c ON a.so_hd_id=c.id
		INNER JOIN gbm_customer b ON c.customer_id=b.id
		where c.tanggal between  '" . $tgl_mulai . "' and  '" . $tgl_akhir . "'	
		and c.status  in ('RELEASE')
		GROUP BY b.nama_customer, c.customer_id
		";

		$ressult = array();
		$dataBkm = $this->db->query($queryhead)->result_array();

		foreach ($dataBkm as $key => $hd) {
			$querydetail = "SELECT a.*,
			b.no_ttb as no_ttb,
			d.no_pp as no_pp,
			b.tanggal as tanggal,
			b.tgl_approve1 AS aprrove1,
			b.tgl_approve2 AS aprrove2,
			b.tgl_approve3 AS aprrove3,
			e.nama_customer as nama_customer,
			f.kode as kode_item,
			f.nama as nama_item,
			g.nama AS gudang
			FROM sls_so_dt a
			INNER JOIN sls_ttb_ht b ON a.so_hd_id=b.id
			LEFT JOIN prc_pp_dt c ON a.pp_dt_id=c.id
			LEFT JOIN prc_pp_ht d ON c.pp_hd_id=d.id
			LEFT JOIN gbm_customer e ON b.customer_id=e.id
			LEFT JOIN inv_item f ON a.item_id=f.id
			LEFT JOIN gbm_organisasi g ON b.lokasi_id=g.id 
			WHERE b.customer_id=" . $hd['customer_id'] . "
			AND b.tanggal between  '" . $tgl_mulai . "' and  '" . $tgl_akhir . "'
			and b.status in ('RELEASE')
			";
			$dataDtl = $this->db->query($querydetail)->result_array();
			// var_dump($dataDtl)	
			$hd['detail'] = $dataDtl;
			$result[] = $hd;
		}

		$data['so'] = 	$result;
		// var_dump($result)	;exit();
		// $data['filter_lokasi'] = 	$filter_lokasi;
		$data['filter_tgl_awal'] = 	$tgl_mulai;
		$data['filter_tgl_akhir'] = $tgl_akhir;
		$data['format_laporan'] = $format_laporan;

		$html = $this->load->view('Sls_So_Laporan_by_Vendor', $data, true);

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		// echo $html;

		if ($format_laporan == 'xls') {
			echo $html;
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
			$dompdf = new DOMPDF;
			$dompdf->loadHtml($html);
			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			$filename = 'report_' . time();
			$x          = 400;
			$y          = 570;
			$text       = "{PAGE_NUM} of {PAGE_COUNT}";
			$font       = null; // $dompdf->getFontMetrics()->get_font('Helvetica', 'normal');
			$size       = 10;
			$color      = array(0, 0, 0);
			$word_space = 0.0;
			$char_space = 0.0;
			$angle      = 0.0;

			$dompdf->getCanvas()->page_text(
				$x,
				$y,
				$text,
				$font,
				$size,
				$color,
				$word_space,
				$char_space,
				$angle
			);
			$dompdf->stream($filename . ".pdf", array("Attachment" => 0));
		}
	}

	function laporanso_by_vendor_unpaid_post()
	{
		/* A.04 Sales Order Unpaid */


		$format_laporan =  $this->post('format_laporan', true);

		// $id = (int)$segment_3;
		$data = [];

		$input = [
			'lokasi_id' => 252,
			'periode' => '2022-08',
			'tgl_mulai' => '2023-11-01',
			'tgl_akhir' => '2023-12-31',
			'format_laporan' => 'view',
		];

		// $lokasi_id = $this->post('lokasi_id', true);
		// $periode =  $this->post('periode', true);
		$tgl_mulai = $this->post('tgl_mulai', true);
		$tgl_akhir = $this->post('tgl_akhir', true);
		// $status_id = $this->post('status_id', true);

		// $lokasi_id = $input['lokasi_id'];
		// $periode = $input['periode'];
		// $tgl_mulai = $input['tgl_mulai'];
		// $tgl_akhir = $input['tgl_akhir'];
		// $format_laporan = $input['format_laporan'];

		// $date = new DateTime($periode . '-01');
		// $date->modify('last day of this month');
		// $last_day_this_month = $date->format('Y-m-d');
		// (int)$jumhari = date('d', strtotime($last_day_this_month));
		// $tgl_mulai = $periode . '-01';
		// $tgl_akhir = $periode . '-' . sprintf("%02d", $jumhari);

		$queryhead = "SELECT b.nama_customer as nama_customer, c.customer_id FROM sls_so_dt a
		INNER JOIN sls_ttb_ht c ON a.so_hd_id=c.id
		INNER JOIN gbm_customer b ON c.customer_id=b.id
		where c.tanggal between  '" . $tgl_mulai . "' and  '" . $tgl_akhir . "'	
		and c.status  in ('RELEASE')
		GROUP BY b.nama_customer, c.customer_id
		";

		$ressult = array();
		$dataBkm = $this->db->query($queryhead)->result_array();

		foreach ($dataBkm as $key => $hd) {
			$querydetail = "SELECT a.*,b.nama_customer AS nama_customer 
			FROM sls_ttb_ht a
			LEFT JOIN gbm_customer b ON a.customer_id=b.id
			WHERE a.customer_id=" . $hd['customer_id'] . "
			AND a.tanggal between  '" . $tgl_mulai . "' and  '" . $tgl_akhir . "'
			and a.status  in ('RELEASE')
			";
			$dataDtl = $this->db->query($querydetail)->result_array();
			// var_dump($dataDtl)	
			$hd['detail'] = $dataDtl;
			$result[] = $hd;
		}

		$data['so'] = 	$result;
		// var_dump($result)	;exit();
		// $data['filter_lokasi'] = 	$filter_lokasi;
		$data['filter_tgl_awal'] = 	$tgl_mulai;
		$data['filter_tgl_akhir'] = $tgl_akhir;
		$data['format_laporan'] = $format_laporan;

		$html = $this->load->view('Sls_So_Laporan_by_Vendor_Unpaid', $data, true);

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		// echo $html;

		if ($format_laporan == 'xls') {
			echo $html;
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
			$dompdf = new DOMPDF;
			$dompdf->loadHtml($html);
			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			$filename = 'report_' . time();
			$x          = 400;
			$y          = 570;
			$text       = "{PAGE_NUM} of {PAGE_COUNT}";
			$font       = null; // $dompdf->getFontMetrics()->get_font('Helvetica', 'normal');
			$size       = 10;
			$color      = array(0, 0, 0);
			$word_space = 0.0;
			$char_space = 0.0;
			$angle      = 0.0;

			$dompdf->getCanvas()->page_text(
				$x,
				$y,
				$text,
				$font,
				$size,
				$color,
				$word_space,
				$char_space,
				$angle
			);
			$dompdf->stream($filename . ".pdf", array("Attachment" => 0));
		}
	}

	function laporanso_by_approval_post()
	{

		/*=== A.01 Sales Order per-Period === */

		$format_laporan =  $this->post('format_laporan', true);

		// $id = (int)$segment_3;
		$data = [];

		$input = [
			'lokasi_id' => 252,
			'periode' => '2022-08',
			'tgl_mulai' => '2023-11-01',
			'tgl_akhir' => '2023-12-31',
			'format_laporan' => 'view',
		];

		// $lokasi_id = $this->post('lokasi_id', true);
		$periode =  $this->post('periode', true);
		// $tanggal_awal = $this->post('tgl_mulai', true);
		// $tanggal_akhir = $this->post('tgl_akhir', true);
		// $status_id = $this->post('status_id', true);

		// $lokasi_id = $input['lokasi_id'];
		// $periode = $input['periode'];
		// $tgl_mulai = $input['tgl_mulai'];
		// $tgl_akhir = $input['tgl_akhir'];
		// $format_laporan = $input['format_laporan'];



		$date = new DateTime($periode . '-01');
		$date->modify('last day of this month');
		$last_day_this_month = $date->format('Y-m-d');
		(int)$jumhari = date('d', strtotime($last_day_this_month));
		$tgl_mulai = $periode . '-01';
		$tgl_akhir = $periode . '-' . sprintf("%02d", $jumhari);

		$queryhead = "SELECT DISTINCT c.status AS statuss FROM sls_so_dt a
		INNER JOIN sls_ttb_ht c ON a.so_hd_id=c.id
		where c.tanggal between  '" . $tgl_mulai . "' and  '" . $tgl_akhir . "'	
		and c.status not in ('REJECTED')
		GROUP BY c.status, c.id
		";

		$ressult = array();
		$dataSt = $this->db->query($queryhead)->result_array();

		foreach ($dataSt as $key => $hd) {
			$querydetail = "SELECT a.*, b.nama AS lokasi, c.nama_customer as nama_customer FROM sls_ttb_ht a
			LEFT JOIN gbm_organisasi b ON a.lokasi_id=b.id
			LEFT JOIN gbm_customer c ON a.customer_id=c.id
			WHERE a.status='" . $hd['statuss'] . "'
			AND a.tanggal between  '" . $tgl_mulai . "' and  '" . $tgl_akhir . "'
			and a.status not in ('REJECTED')
			";
			$dataDtl = $this->db->query($querydetail)->result_array();
			// var_dump($dataDtl)	
			$hd['detail'] = $dataDtl;
			$result[] = $hd;
		}

		$data['so'] = 	$result;
		// var_dump($result)	;exit();
		// $data['filter_lokasi'] = 	$filter_lokasi;
		$data['filter_tgl_awal'] = 	$tgl_mulai;
		$data['filter_tgl_akhir'] = $tgl_akhir;
		$data['format_laporan'] = $format_laporan;

		$html = $this->load->view('Sls_So_Laporan_by_Approval', $data, true);

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		// echo $html;

		if ($format_laporan == 'xls') {
			echo $html;
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
			$dompdf = new DOMPDF;
			$dompdf->loadHtml($html);
			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			$filename = 'report_' . time();
			$x          = 400;
			$y          = 570;
			$text       = "{PAGE_NUM} of {PAGE_COUNT}";
			$font       = null; // $dompdf->getFontMetrics()->get_font('Helvetica', 'normal');
			$size       = 10;
			$color      = array(0, 0, 0);
			$word_space = 0.0;
			$char_space = 0.0;
			$angle      = 0.0;

			$dompdf->getCanvas()->page_text(
				$x,
				$y,
				$text,
				$font,
				$size,
				$color,
				$word_space,
				$char_space,
				$angle
			);
			$dompdf->stream($filename . ".pdf", array("Attachment" => 0));
		}
	}
	function laporanso_by_cancelled_post()
	{

		/*=== A.07 Sales Order Cancelled === */

		$format_laporan =  $this->post('format_laporan', true);

		// $id = (int)$segment_3;
		$data = [];

		$input = [
			'lokasi_id' => 252,
			'periode' => '2022-08',
			'tgl_mulai' => '2023-11-01',
			'tgl_akhir' => '2023-12-31',
			'format_laporan' => 'view',
		];

		// $lokasi_id = $this->post('lokasi_id', true);
		$periode =  $this->post('periode', true);
		// $tanggal_awal = $this->post('tgl_mulai', true);
		// $tanggal_akhir = $this->post('tgl_akhir', true);
		// $status_id = $this->post('status_id', true);

		// $lokasi_id = $input['lokasi_id'];
		// $periode = $input['periode'];
		// $tgl_mulai = $input['tgl_mulai'];
		// $tgl_akhir = $input['tgl_akhir'];
		// $format_laporan = $input['format_laporan'];



		$date = new DateTime($periode . '-01');
		$date->modify('last day of this month');
		$last_day_this_month = $date->format('Y-m-d');
		(int)$jumhari = date('d', strtotime($last_day_this_month));
		$tgl_mulai = $periode . '-01';
		$tgl_akhir = $periode . '-' . sprintf("%02d", $jumhari);

		$queryhead = "SELECT DISTINCT c.status AS statuss FROM sls_so_dt a
		INNER JOIN sls_ttb_ht c ON a.so_hd_id=c.id
		where c.tanggal between  '" . $tgl_mulai . "' and  '" . $tgl_akhir . "'	
		and c.status  in ('REJECTED')
		GROUP BY c.status, c.id
		";

		$ressult = array();
		$dataSt = $this->db->query($queryhead)->result_array();

		foreach ($dataSt as $key => $hd) {
			$querydetail = "SELECT a.*, b.nama AS lokasi, c.nama_customer as nama_customer FROM sls_ttb_ht a
			LEFT JOIN gbm_organisasi b ON a.lokasi_id=b.id
			LEFT JOIN gbm_customer c ON a.customer_id=c.id
			WHERE a.status='" . $hd['statuss'] . "'
			AND a.tanggal between  '" . $tgl_mulai . "' and  '" . $tgl_akhir . "'
			and a.status  in ('REJECTED')
			";
			$dataDtl = $this->db->query($querydetail)->result_array();
			// var_dump($dataDtl)	
			$hd['detail'] = $dataDtl;
			$result[] = $hd;
		}

		$data['so'] = 	$result;
		// var_dump($result)	;exit();
		// $data['filter_lokasi'] = 	$filter_lokasi;
		$data['filter_tgl_awal'] = 	$tgl_mulai;
		$data['filter_tgl_akhir'] = $tgl_akhir;
		$data['format_laporan'] = $format_laporan;

		$html = $this->load->view('Sls_So_Laporan_by_Cancelled', $data, true);

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		// echo $html;

		if ($format_laporan == 'xls') {
			echo $html;
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
			$dompdf = new DOMPDF;
			$dompdf->loadHtml($html);
			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			$filename = 'report_' . time();
			$x          = 400;
			$y          = 570;
			$text       = "{PAGE_NUM} of {PAGE_COUNT}";
			$font       = null; // $dompdf->getFontMetrics()->get_font('Helvetica', 'normal');
			$size       = 10;
			$color      = array(0, 0, 0);
			$word_space = 0.0;
			$char_space = 0.0;
			$angle      = 0.0;

			$dompdf->getCanvas()->page_text(
				$x,
				$y,
				$text,
				$font,
				$size,
				$color,
				$word_space,
				$char_space,
				$angle
			);
			$dompdf->stream($filename . ".pdf", array("Attachment" => 0));
		}
	}
	function laporanso_by_header_post()
	{
		/* A.05 Sales Order per-Period (Detail) */
		$format_laporan =  $this->post('format_laporan', true);

		// $id = (int)$segment_3;
		$data = [];

		$input = [
			'lokasi_id' => 252,
			'periode' => '2022-08',
			'tgl_mulai' => '2023-11-01',
			'tgl_akhir' => '2023-11-31',
			'format_laporan' => 'view',
		];

		// $lokasi_id = $this->post('lokasi_id', true);
		$periode =  $this->post('periode', true);
		// $tanggal_awal = $this->post('tgl_mulai', true);
		// $tanggal_akhir = $this->post('tgl_akhir', true);
		// $status_id = $this->post('status_id', true);

		// $lokasi_id = $input['lokasi_id'];
		// $periode = $input['periode'];
		// $tgl_mulai = $input['tgl_mulai'];
		// $tgl_akhir = $input['tgl_akhir'];
		// $format_laporan = $input['format_laporan'];

		$date = new DateTime($periode . '-01');
		$date->modify('last day of this month');
		$last_day_this_month = $date->format('Y-m-d');
		(int)$jumhari = date('d', strtotime($last_day_this_month));
		$tgl_mulai = $periode . '-01';
		$tgl_akhir = $periode . '-' . sprintf("%02d", $jumhari);

		$queryhead = "SELECT c.no_ttb AS no_ttb, c.id AS id  FROM sls_so_dt a
		INNER JOIN sls_ttb_ht c ON a.so_hd_id=c.id
		where c.tanggal between  '" . $tgl_mulai . "' and  '" . $tgl_akhir . "'	
		and c.status='RELEASE'
		GROUP BY c.no_ttb, c.id
		";

		$ressult = array();
		$dataPo = $this->db->query($queryhead)->result_array();

		foreach ($dataPo as $key => $hd) {
			$querydetail = "SELECT a.*,
			b.no_ttb as no_ttb,
			d.no_pp as no_pp,
			b.tanggal as tanggal,
			b.tgl_approve1 AS aprrove1,
			b.tgl_approve2 AS aprrove2,
			b.tgl_approve3 AS aprrove3,
			e.nama_customer as nama_customer,
			f.kode as kode_item,
			f.nama as nama_item,
			g.nama AS gudang,
			b.status
			FROM sls_so_dt a
			INNER JOIN sls_ttb_ht b ON a.so_hd_id=b.id
			LEFT JOIN prc_pp_dt c ON a.pp_dt_id=c.id
			LEFT JOIN prc_pp_ht d ON c.pp_hd_id=d.id
			LEFT JOIN gbm_customer e ON b.customer_id=e.id
			LEFT JOIN inv_item f ON a.item_id=f.id
			LEFT JOIN gbm_organisasi g ON b.lokasi_id=g.id 
			WHERE b.id=" . $hd['id'] . "
			AND b.tanggal between  '" . $tgl_mulai . "' and  '" . $tgl_akhir . "'
			and b.status='RELEASE'
			";
			$dataDtl = $this->db->query($querydetail)->result_array();
			// var_dump($dataDtl)	
			$hd['detail'] = $dataDtl;
			$result[] = $hd;
		}

		$data['so'] = 	$result;
		// var_dump($result)	;exit();
		// $data['filter_lokasi'] = 	$filter_lokasi;
		$data['filter_tgl_awal'] = 	$tgl_mulai;
		$data['filter_tgl_akhir'] = $tgl_akhir;
		$data['format_laporan'] = $format_laporan;

		$html = $this->load->view('Sls_So_Laporan_by_header', $data, true);

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		// echo $html;

		if ($format_laporan == 'xls') {
			echo $html;
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
			$dompdf = new DOMPDF;
			$dompdf->loadHtml($html);
			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			$filename = 'report_' . time();
			$x          = 400;
			$y          = 570;
			$text       = "{PAGE_NUM} of {PAGE_COUNT}";
			$font       = null; // $dompdf->getFontMetrics()->get_font('Helvetica', 'normal');
			$size       = 10;
			$color      = array(0, 0, 0);
			$word_space = 0.0;
			$char_space = 0.0;
			$angle      = 0.0;

			$dompdf->getCanvas()->page_text(
				$x,
				$y,
				$text,
				$font,
				$size,
				$color,
				$word_space,
				$char_space,
				$angle
			);
			$dompdf->stream($filename . ".pdf", array("Attachment" => 0));
		}
	}

	function laporansoBy_vendor_kategori_post()
	{
		/* A.03 Sales Order by Category */
		$format_laporan =  $this->post('format_laporan', true);

		// $id = (int)$segment_3;
		$data = [];

		$input = [
			'lokasi_id' => 252,
			'periode' => '2022-08',
			'tgl_mulai' => '2022-08-01',
			'tgl_akhir' => '2022-08-02',
			'format_laporan' => 'view',
		];

		// $lokasi_id = $this->post('lokasi_id', true);
		$periode =  $this->post('periode', true);
		// $tanggal_awal = $this->post('tgl_mulai', true);
		// $tanggal_akhir = $this->post('tgl_akhir', true);
		// $status_id = $this->post('status_id', true);

		// $lokasi_id = $input['lokasi_id'];
		// $periode = $input['periode'];
		// $tgl_mulai = $input['tgl_mulai'];
		// $tgl_akhir = $input['tgl_akhir'];
		// $format_laporan = $input['format_laporan'];

		$date = new DateTime($periode . '-01');
		$date->modify('last day of this month');
		$last_day_this_month = $date->format('Y-m-d');
		(int)$jumhari = date('d', strtotime($last_day_this_month));
		$tgl_mulai = $periode . '-01';
		$tgl_akhir = $periode . '-' . sprintf("%02d", $jumhari);

		$queryKategori = "SELECT 
		c.inv_kategori_id,
		d.nama AS kategori
		FROM sls_so_dt a
		INNER JOIN sls_ttb_ht b ON a.so_hd_id=b.id
		INNER JOIN inv_item c ON a.item_id=c.id
		INNER JOIN inv_kategori d ON c.inv_kategori_id=d.id
		where b.tanggal between  '" . $tgl_mulai . "' and  '" . $tgl_akhir . "'	
		and b.status  in ('RELEASE')
		GROUP BY c.inv_kategori_id,d.nama
		";
		$dataKat = $this->db->query($queryKategori)->result_array();

		foreach ($dataKat as $key => $kt) {
			$querySup = "SELECT 
		b.nama_customer as nama_customer,
		c.customer_id
		FROM sls_so_dt a
		INNER JOIN sls_ttb_ht c ON a.so_hd_id=c.id
		INNER JOIN gbm_customer b ON c.customer_id=b.id
		INNER JOIN inv_item d ON a.item_id=d.id
		INNER JOIN inv_kategori e ON d.inv_kategori_id=e.id
		where c.tanggal between  '" . $tgl_mulai . "' and  '" . $tgl_akhir . "'
		and d.inv_kategori_id=" . $kt['inv_kategori_id'] . "
		and c.status  in ('RELEASE')
		GROUP BY b.nama_customer,c.customer_id
		";
			$dataSupp = $this->db->query($querySup)->result_array();
			$res_supp = [];
			foreach ($dataSupp as $key => $hd) {
				$querydetail = "SELECT a.*,
				b.no_ttb as no_ttb,
				d.no_pp as no_pp,
				b.tanggal as tanggal,
				b.tgl_approve1 AS aprrove1,
				b.tgl_approve2 AS aprrove2,
				b.tgl_approve3 AS aprrove3,
				e.nama_customer as nama_customer,
				f.kode as kode_item,
				f.nama as nama_item,
				g.nama AS gudang,
				h.nama AS kategori
				FROM sls_so_dt a
				INNER JOIN sls_ttb_ht b ON a.so_hd_id=b.id
				LEFT JOIN prc_pp_dt c ON a.pp_dt_id=c.id
				LEFT JOIN prc_pp_ht d ON c.pp_hd_id=d.id
				LEFT JOIN gbm_customer e ON b.customer_id=e.id
				LEFT JOIN inv_item f ON a.item_id=f.id
				LEFT JOIN gbm_organisasi g ON b.lokasi_id=g.id 
				LEFT JOIN inv_kategori h ON f.inv_kategori_id=h.id
				WHERE b.customer_id=" . $hd['customer_id'] . "
				and f.inv_kategori_id=" . $kt['inv_kategori_id'] . "
				AND b.tanggal between  '" . $tgl_mulai . "' and  '" . $tgl_akhir . "'
				and b.status  in ('RELEASE')
				";
				$dataDtl = $this->db->query($querydetail)->result_array();
				// var_dump($dataDtl)	
				$hd['it'] = $dataDtl;
				$res_supp[] = $hd;
			}
			$kt['supp'] = $res_supp;
			$result[] = $kt;
		}
		$data['so'] = 	$result;
		// print_r($result);exit();
		// $data['filter_lokasi'] = 	$filter_lokasi;
		$data['filter_tgl_awal'] = 	$tgl_mulai;
		$data['filter_tgl_akhir'] = $tgl_akhir;
		$data['format_laporan'] = $format_laporan;

		$html = $this->load->view('Sls_So_Laporan_by_Kat_Vendor', $data, true);

		// echo $html;

		if ($format_laporan == 'xls') {
			echo $html;
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
			$dompdf = new DOMPDF;
			$dompdf->loadHtml($html);
			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			$filename = 'report_' . time();
			$x          = 400;
			$y          = 570;
			$text       = "{PAGE_NUM} of {PAGE_COUNT}";
			$font       = null; // $dompdf->getFontMetrics()->get_font('Helvetica', 'normal');
			$size       = 10;
			$color      = array(0, 0, 0);
			$word_space = 0.0;
			$char_space = 0.0;
			$angle      = 0.0;

			$dompdf->getCanvas()->page_text(
				$x,
				$y,
				$text,
				$font,
				$size,
				$color,
				$word_space,
				$char_space,
				$angle
			);
			$dompdf->stream($filename . ".pdf", array("Attachment" => 0));
		}
	}


	function laporanSummary_get()
	{
		$format_laporan =  $this->post('format_laporan', true);
		$data = [];
		$input = [
			'periode' => '2022-08',
			'tgl_mulai' => '2023-08-01',
			'tgl_akhir' => '2023-08-02',
			'tahun' => '2022',
			'format_laporan' => 'view',
		];
		// $periode =  $this->post('periode', true);

		// $tgl_mulai = $input['tgl_mulai'];
		// $tgl_akhir = $input['tgl_akhir'];
		$tahun = $input['tahun'];
		$format_laporan = $input['format_laporan'];

		$tgl_mulai = $tahun . '-01-01';
		$tgl_akhir = $tahun . '-12-31';

		$queryhead = "SELECT DISTINCT
		c.inv_kategori_id,
		d.nama AS kategori
		FROM sls_so_dt a
		INNER JOIN sls_ttb_ht b ON a.so_hd_id=b.id
		INNER JOIN inv_item c ON a.item_id=c.id
		INNER JOIN inv_kategori d ON c.inv_kategori_id=d.id
		WHERE (c.inv_kategori_id IS NOT NULL AND c.inv_kategori_id <>0)
		and b.tanggal between  '" . $tgl_mulai . "' and  '" . $tgl_akhir . "'	
		and b.status  in ('RELEASE')
		";
		$dataPo = $this->db->query($queryhead)->result_array();

		for ($i = 1; $i < (12 + 1); $i++) {
			$totalPerBulan[] = 0;
		}

		foreach ($dataPo as $key => $hd) {
			$totalrp = 0;
			for ($i = 1; $i < (12 + 1); $i++) {
				$yymm = $tahun  . '-' . sprintf("%02d", $i);

				$querydetail = "SELECT 
			SUM(a.total) AS jml_kat_rp
			FROM sls_so_dt a
			INNER JOIN sls_ttb_ht b ON a.so_hd_id=b.id
			LEFT JOIN prc_pp_dt c ON a.pp_dt_id=c.id
			LEFT JOIN prc_pp_ht d ON c.pp_hd_id=d.id
			LEFT JOIN gbm_customer e ON b.customer_id=e.id
			LEFT JOIN inv_item f ON a.item_id=f.id
			LEFT JOIN gbm_organisasi g ON b.lokasi_id=g.id 
			LEFT JOIN inv_kategori h ON f.inv_kategori_id=h.id
			WHERE DATE_FORMAT(b.tanggal, '%Y-%m')='" . $yymm . "'
			AND f.inv_kategori_id=" . $hd['inv_kategori_id'] . "
			and b.status not in ('REJECTED')
			";
				$dataDtl = $this->db->query($querydetail)->row_array();
			}
			$hd['detail'] = $dataDtl;
			$result[] = $hd;
		}

		$data['so'] = 	$result;
		// var_dump($result)	;exit();
		$data['tahun'] = 	$tahun;
		$data['filter_tgl_awal'] = 	$tgl_mulai;
		$data['filter_tgl_akhir'] = $tgl_akhir;
		$data['format_laporan'] = $format_laporan;

		$html = $this->load->view('Sls_So_Laporan_Summary_Kat', $data, true);

		echo $html;
	}

	function laporan_so_detail_post()
	{
		$jenis_laporan =  $this->post('jenis_laporan', true);
		if ($jenis_laporan == 'rekap') {
			$this->laporan_so_rekap();
		} else {
			$this->laporan_so_detail();
		}
	}
	function laporan_so_detail()
	{
		error_reporting(0);

		$data = [];
		if (isset($this->post()['tgl_mulai'])) {
			$input = $this->post();
			$lokasi_id = $this->post('lokasi_id', true);
			$customer_id = $this->post('customer_id', true);
			$tanggal_awal = $this->post('tgl_mulai', true);
			$tanggal_akhir = $this->post('tgl_akhir', true);
			$format_laporan =  $this->post('format_laporan', true);
		} else {
			$input = [
				'tanggal' => '2021-12-23',
			];
			$lokasi_id = 263;
			$customer_id = 4;
			$tanggal_awal = '2020-01-01';
			$tanggal_akhir = '2022-12-12';
			$format_laporan =  'view';
		}

		$queryPo = "SELECT a.*,
		b.no_ttb as noso,
		b.tanggal as tanggal,
		c.nama as item,
		d.nama as uom,
		c.kode as kode,
		e.nama_customer as sup,
		f.kode as mata_uang,
        h.tanggal as tanggal_pp,
		a.item_id,
		b.id as po_id,
		a.id as so_dt_id,
		b.status,
		-- i.no_transaksi as no_penerimaan,
		-- i.tanggal as tanggal_penerimaan,
		-- i.no_ref,
		h.no_pp
		
		FROM sls_so_dt a
		
		INNER JOIN sls_ttb_ht b on a.so_hd_id=b.id
		INNER JOIN inv_item c on a.item_id=c.id
		INNER JOIN gbm_customer e on b.customer_id=e.id
		LEFT JOIN gbm_uom d on c.uom_id=d.id
		LEFT JOIN acc_mata_uang f on b.mata_uang_id=f.id
        LEFT JOIN prc_pp_dt g on a.pp_dt_id=g.id
        LEFT JOIN prc_pp_ht h on g.pp_hd_id=h.id
		-- LEFT JOIN inv_pengiriman_so_ht i on i.po_id=b.id
		where b.tanggal between  '" . $tanggal_awal . "' and  '" . $tanggal_akhir . "'	
		";
		$filter_supplier = "Semua";
		$filter_lokasi = "Semua";
		if ($lokasi_id) {
			$queryPo = $queryPo . " and b.lokasi_id=" . $lokasi_id . "";
			$res = $this->db->query("select * from gbm_organisasi where id=" . $lokasi_id . "")->row_array();
			$filter_lokasi = $res['nama'];
		}
		if ($customer_id) {
			$queryPo = $queryPo . " and b.customer_id=" . $customer_id . "";
			$res = $this->db->query("select * from gbm_customer where id=" . $customer_id . "")->row_array();
			$filter_supplier = $res['nama_customer'];
		}
		$dataPo = $this->db->query($queryPo)->result_array();
		foreach ($dataPo  as $key => $po) {
			$no_penerimaan = '';
			$tgl_penerimaan = '';
			$no_surat_jalan = '';
			$qty_terima = 0;
			$res_penerimaan = $this->db->query(" select a.*,b.qty from inv_pengiriman_so_ht a inner join inv_pengiriman_so_dt b
			on a.id=b.pengiriman_so_id where b.item_id=" . $po['item_id'] . " and b.so_dt_id=" . $po['so_dt_id'] . " ")->result_array();
			if ($res_penerimaan) {
				foreach ($res_penerimaan as $key2 => $penerimaan) {
					$qty_terima = $qty_terima + $penerimaan['qty'];
					if ($no_penerimaan == '') {
						$no_penerimaan = $penerimaan['no_transaksi'];
					} else {
						$no_penerimaan = $no_penerimaan . ', ' . $penerimaan['no_transaksi'];
					}
					if ($tgl_penerimaan == '') {
						$tgl_penerimaan = $penerimaan['tanggal'];
					} else {
						$tgl_penerimaan = $tgl_penerimaan . ', ' . $penerimaan['tanggal'];
					}
					if ($no_surat_jalan == '') {
						$no_surat_jalan = $penerimaan['no_ref'];
					} else {
						$no_surat_jalan = $no_surat_jalan . ', ' . $penerimaan['no_ref'];
					}
				}
			}
			$dataPo[$key]['no_penerimaan'] = $no_penerimaan;
			$dataPo[$key]['tanggal_penerimaan'] = $tgl_penerimaan;
			$dataPo[$key]['no_ref'] = $no_surat_jalan;
			$dataPo[$key]['qty_terima'] = $qty_terima;
		}

		$data['so'] = 	$dataPo;
		$data['filter_supplier'] = 	$filter_supplier;
		$data['filter_lokasi'] = 	$filter_lokasi;
		$data['filter_tgl_awal'] = 	$tanggal_awal;
		$data['filter_tgl_akhir'] = $tanggal_akhir;
		$data['format_laporan'] = $format_laporan;

		$html = $this->load->view('Sls_So_Laporan', $data, true);

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		// echo $html;
		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
			$dompdf = new DOMPDF;
			$dompdf->loadHtml($html);
			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			$filename = 'report_' . time();
			$x          = 400;
			$y          = 570;
			$text       = "{PAGE_NUM} of {PAGE_COUNT}";
			$font       = null; // $dompdf->getFontMetrics()->get_font('Helvetica', 'normal');
			$size       = 10;
			$color      = array(0, 0, 0);
			$word_space = 0.0;
			$char_space = 0.0;
			$angle      = 0.0;

			$dompdf->getCanvas()->page_text(
				$x,
				$y,
				$text,
				$font,
				$size,
				$color,
				$word_space,
				$char_space,
				$angle
			);
			$dompdf->stream($filename . ".pdf", array("Attachment" => 0));
		}
	}
	function laporan_so_rekap()
	{
		error_reporting(0);

		$data = [];
		if (isset($this->post()['tgl_mulai'])) {
			$input = $this->post();
			$lokasi_id = $this->post('lokasi_id', true);
			$customer_id = $this->post('customer_id', true);
			$tanggal_awal = $this->post('tgl_mulai', true);
			$tanggal_akhir = $this->post('tgl_akhir', true);
			$format_laporan =  $this->post('format_laporan', true);
		} else {
			$input = [
				'tanggal' => '2021-12-23',
			];
			$lokasi_id = 263;
			$customer_id = 4;
			$tanggal_awal = '2020-01-01';
			$tanggal_akhir = '2022-12-12';
			$format_laporan =  'xls';
		}

		$queryPo = "	SELECT a.*,
		b.nama_customer as sup,
		c.kode as mata_uang	,
		d.ket AS syarat_bayar	
		from sls_ttb_ht a 
		INNER JOIN gbm_customer b on a.customer_id=b.id
		inner JOIN acc_mata_uang c on a.mata_uang_id=c.id
		INNER JOIN prc_syarat_bayar d ON a.syarat_bayar_id=d.id
      	where a.tanggal between  '" . $tanggal_awal . "' and  '" . $tanggal_akhir . "'	
		";
		$filter_supplier = "Semua";
		$filter_lokasi = "Semua";
		if ($lokasi_id) {
			$queryPo = $queryPo . " and a.lokasi_id=" . $lokasi_id . "";
			$res = $this->db->query("select * from gbm_organisasi where id=" . $lokasi_id . "")->row_array();
			$filter_lokasi = $res['nama'];
		}
		if ($customer_id) {
			$queryPo = $queryPo . " and a.customer_id=" . $customer_id . "";
			$res = $this->db->query("select * from gbm_customer where id=" . $customer_id . "")->row_array();
			$filter_supplier = $res['nama_customer'];
		}
		$dataPo = $this->db->query($queryPo)->result_array();
		foreach ($dataPo  as $key => $po) {
			$no_penerimaan = '';
			$tgl_penerimaan = '';
			$no_surat_jalan = '';
			$qty_terima = 0;
			$res_penerimaan = $this->db->query(" 	SELECT a.* FROM inv_pengiriman_so_ht a INNER JOIN sls_ttb_ht b ON a.po_id=b.id
			where a.po_id=" . $po['id'] . " ")->result_array();
			if ($res_penerimaan) {
				foreach ($res_penerimaan as $key2 => $penerimaan) {

					if ($no_penerimaan == '') {
						$no_penerimaan = $penerimaan['no_transaksi'];
					} else {
						$no_penerimaan = $no_penerimaan . ', ' . $penerimaan['no_transaksi'];
					}
					if ($tgl_penerimaan == '') {
						$tgl_penerimaan = $penerimaan['tanggal'];
					} else {
						$tgl_penerimaan = $tgl_penerimaan . ', ' . $penerimaan['tanggal'];
					}
					if ($no_surat_jalan == '') {
						$no_surat_jalan = $penerimaan['no_ref'];
					} else {
						$no_surat_jalan = $no_surat_jalan . ', ' . $penerimaan['no_ref'];
					}
				}
			}
			$dataPo[$key]['no_penerimaan'] = $no_penerimaan;
			$dataPo[$key]['tanggal_penerimaan'] = $tgl_penerimaan;
			$dataPo[$key]['no_ref'] = $no_surat_jalan;
		}

		$data['so'] = 	$dataPo;
		$data['filter_supplier'] = 	$filter_supplier;
		$data['filter_lokasi'] = 	$filter_lokasi;
		$data['filter_tgl_awal'] = 	$tanggal_awal;
		$data['filter_tgl_akhir'] = $tanggal_akhir;
		$data['format_laporan'] = $format_laporan;

		$html = $this->load->view('Sls_So_Laporan_Rekap', $data, true);

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		// echo $html;
		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
			$dompdf = new DOMPDF;
			$dompdf->loadHtml($html);
			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			$filename = 'report_' . time();
			$x          = 400;
			$y          = 570;
			$text       = "{PAGE_NUM} of {PAGE_COUNT}";
			$font       = null; // $dompdf->getFontMetrics()->get_font('Helvetica', 'normal');
			$size       = 10;
			$color      = array(0, 0, 0);
			$word_space = 0.0;
			$char_space = 0.0;
			$angle      = 0.0;

			$dompdf->getCanvas()->page_text(
				$x,
				$y,
				$text,
				$font,
				$size,
				$color,
				$word_space,
				$char_space,
				$angle
			);
			$dompdf->stream($filename . ".pdf", array("Attachment" => 0));
		}
	}

	function laporanSummaryLokasi_post()
	{

		/* A.06 Sales Order Summary */

		$input = [
			'tahun' => '2023',
			'tipe_laporan' => '',
		];

		// $tahun = $input['tahun'];
		// $format_laporan = $input['format_laporan'];

		$format_laporan     = $this->post('format_laporan', true);
		// $format_laporan=	$tipe_laporan;
		$tahun =  $this->post('tahun', true);

		$tgl_mulai = $tahun . '-01-01';
		$tgl_akhir = $tahun . '-12-31';


		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report_v2("A.06 Sales Order Summary");
			$html = $html . '
		
				<style>
				*{
					font-size: 10px ;
				}
				th,
				td {
				  padding: 3px 3px;
				  vertical-align: middle;
				}
				</style>
				';
			$html = $html . ' 
			<div>
			<div class="kop-print">
			<div style=" padding-right: 10px; "><img src="data:image/png;base64,' . get_company()['logo'] . '"></div>
			<div class="kop-nama">' . get_company()['nama'] . '</div>
			<div class="kop-info"> ' . get_company()['alamat'] . '</div>
			<div class="kop-info">Telp : ' . get_company()['telp'] . '</div>
			</div>
			<hr class="kop-print-hr">
		 	</div>';
		} else {
			$html = get_header_report_v2("A.06 Sales Order Summary");
			$html = $html . ' 
			<div>
			<div class="kop-print">
			<div style=" padding-right: 200px; "><img src="data:image/png;base64,' . get_company()['logo'] . '"></div>
			<div class="kop-nama">' . get_company()['nama'] . '</div>
			<div class="kop-info"> ' . get_company()['alamat'] . '</div>
			<div class="kop-info">Telp : ' . get_company()['telp'] . '</div>
		  </div>
			<hr class="kop-print-hr">
		  </div>';
		}


		$html = $html . '
		<h3 class="title">A.06 Sales Order Summary</h3>
  		<table class="no_border" style="width:30%">
			
			<tr>	
					<td>Periode Tahun</td>
					<td>:</td>
					<td>' . tgl_indo($tgl_mulai) . ' - ' . tgl_indo($tgl_akhir) . ' </td>
			</tr>
			
		</table>
			<br>';

		$html = $html . "

		<table   border='1' width='100%' style='border-collapse: collapse;'>
		<thead>
		<tr>
			<th rowspan=2 >No</th>
			<th rowspan=2 style='text-align: center'>Kategori Item</th>
			<th colspan=12  style='text-align: center'> " . $tahun . "  </th>
			<th rowspan=2  style='text-align: center'>TOTAL(Rp)</th>
		</tr>
		";


		$html = $html . "<tr>";
		for ($i = 1; $i < (12 + 1); $i++) {
			$html = $html . "<th style='text-align: center'>" . convert_month($i) . "</th>";
		}
		$html = $html . "</tr> </thead>";
		$nourut = 0;
		$grandtotal = 0;
		$totalPerBulan = array();

		for ($i = 1; $i < (12 + 1); $i++) {
			$totalPerBulan[] = 0;
		}

		// retrive data Kategori  
		$qry = "SELECT DISTINCT c.nama AS lokasi,
		b.lokasi_pp_id AS lokasi_pp_id
		FROM sls_so_dt a
		INNER JOIN sls_ttb_ht b ON a.so_hd_id=b.id
		INNER JOIN gbm_organisasi c ON b.lokasi_pp_id=c.id
		WHERE (b.lokasi_pp_id IS NOT NULL AND b.lokasi_pp_id <>0) 
		and tanggal>='" . $tgl_mulai . "' 
		and tanggal<='" . $tgl_akhir . "' 
		and b.status  in ('RELEASE')";

		// $qry=$qry." order by nama_customer ;";
		$retrieveLokasipp = $this->db->query($qry)->result_array();

		foreach ($retrieveLokasipp as $key => $s) {
			$html = $html . "<tr>";
			$totalRp = 0;
			$nourut = $nourut + 1;
			$html = $html . "<td style='text-align: center'>" . $nourut . "</td>";
			$html = $html . "<td style='text-align: left'>" . $s['lokasi'] . "</td>";
			for ($i = 1; $i < (12 + 1); $i++) {

				$yymm = $tahun  . '-' . sprintf("%02d", $i);

				$retrieveRpPO = $this->db->query("SELECT 
				SUM(a.total) AS jml_kat_rp
				FROM sls_so_dt a
				INNER JOIN sls_ttb_ht b ON a.so_hd_id=b.id
				INNER JOIN gbm_organisasi c ON b.lokasi_pp_id=c.id
				WHERE DATE_FORMAT(b.tanggal, '%Y-%m')='" . $yymm . "'
				AND b.lokasi_pp_id=" . $s['lokasi_pp_id'] . "
				and b.status  in ('RELEASE')
				")->row_array();

				$jmlRpKt = $retrieveRpPO['jml_kat_rp'] ? $retrieveRpPO['jml_kat_rp'] : 0;

				$totalPerBulan[$i - 1] = ($totalPerBulan[$i - 1] ? $totalPerBulan[$i - 1] : 0) + $jmlRpKt;

				$totalRp = $totalRp + $jmlRpKt;

				$grandtotal = $grandtotal + $jmlRpKt;

				$html = $html . "<td style='text-align: right'>" . $this->format_number_report($jmlRpKt) . " </td>";
			}
			$html = $html . "<td style='text-align: right'>" . $this->format_number_report($totalRp) . " </td>";
			$html = $html . "</tr>";
		}
		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: center'> </td>";
		$html = $html . "<td style='text-align: center'></td>";
		for ($i = 1; $i < (12 + 1); $i++) {

			$html = $html . "<td style='text-align: right'>" . $this->format_number_report($totalPerBulan[$i - 1]) . " </td>";
		}
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($grandtotal) . " </td>";
		$html = $html . "</tr>";
		$html = $html . "</table>";

		if ($format_laporan == 'xls') {
			echo $html;
		} else if ($format_laporan == 'pdf') {
			$filename = 'report_' . time();
			// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
			$dompdf = new DOMPDF;
			$dompdf->loadHtml($html);
			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			$filename = 'report_' . time();
			$x          = 400;
			$y          = 570;
			$text       = "{PAGE_NUM} of {PAGE_COUNT}";
			$font       = null; // $dompdf->getFontMetrics()->get_font('Helvetica', 'normal');
			$size       = 10;
			$color      = array(0, 0, 0);
			$word_space = 0.0;
			$char_space = 0.0;
			$angle      = 0.0;

			$dompdf->getCanvas()->page_text(
				$x,
				$y,
				$text,
				$font,
				$size,
				$color,
				$word_space,
				$char_space,
				$angle
			);
			$dompdf->stream($filename . ".pdf", array("Attachment" => 0));
		} else {
			echo $html;
		}
	}
	function laporanSummaryKategori_post()
	{

		/* A.08 Sales Order Summary */

		$input = [
			'tahun' => '2022',
			'tipe_laporan' => '',
		];

		// $tahun = $input['tahun'];
		// $format_laporan = $input['format_laporan'];

		$format_laporan     = $this->post('format_laporan', true);
		// $format_laporan=	$tipe_laporan;
		$tahun =  $this->post('tahun', true);

		$tgl_mulai = $tahun . '-01-01';
		$tgl_akhir = $tahun . '-12-31';


		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report_v2("A.08 Sales Order Summary");
			$html = $html . '
		
				<style>
				*{
					font-size: 10px ;
				}
				th,
				td {
				  padding: 3px 3px;
				  vertical-align: middle;
				}
				</style>
				
				';
			$html = $html . ' 
			<div>
			<div class="kop-print">
			<div style=" padding-right: 10px; "><img src="data:image/png;base64,' . get_company()['logo'] . '"></div>
			<div class="kop-nama">' . get_company()['nama'] . '</div>
			<div class="kop-info"> ' . get_company()['alamat'] . '</div>
			<div class="kop-info">Telp : ' . get_company()['telp'] . '</div>
			</div>
			<hr class="kop-print-hr">
		 	</div>';
		} else {
			$html = get_header_report_v2("A.08 Sales Order Summary");
			$html = $html . ' 
			<div>
			<div class="kop-print">
			<div style=" padding-right: 200px; "><img src="data:image/png;base64,' . get_company()['logo'] . '"></div>
			<div class="kop-nama">' . get_company()['nama'] . '</div>
			<div class="kop-info"> ' . get_company()['alamat'] . '</div>
			<div class="kop-info">Telp : ' . get_company()['telp'] . '</div>
		  </div>
			<hr class="kop-print-hr">
		  </div>';
		}


		$html = $html . '
	
	</div>
		<h3 class="title">A.08 Sales Order Summary</h3>
  		<table class="no_border" style="width:30%">
			
			<tr>	
					<td>Periode Tahun</td>
					<td>:</td>
					<td>' . tgl_indo($tgl_mulai) . ' - ' . tgl_indo($tgl_akhir) . ' </td>
			</tr>
			
		</table>
			<br>';

		$html = $html . "

		<table   border='1' width='100%' style='border-collapse: collapse;'>
		<thead>
		<tr>
			<th rowspan=2 >No</th>
			<th rowspan=2 style='text-align: center'>Kategori Item</th>
			<th colspan=12  style='text-align: center'> " . $tahun . "  </th>
			<th rowspan=2  style='text-align: center'>TOTAL(Rp)</th>
		</tr>
		";


		$html = $html . "<tr>";
		for ($i = 1; $i < (12 + 1); $i++) {
			$html = $html . "<th style='text-align: center'>" . convert_month($i) . "</th>";
		}
		$html = $html . "</tr> </thead>";
		$nourut = 0;
		$grandtotal = 0;
		$totalPerBulan = array();

		for ($i = 1; $i < (12 + 1); $i++) {
			$totalPerBulan[] = 0;
		}

		// retrive data Kategori  
		$qry = "SELECT DISTINCT
		c.inv_kategori_id,
		d.nama AS kategori
		FROM sls_so_dt a
		INNER JOIN sls_ttb_ht b ON a.so_hd_id=b.id
		INNER JOIN inv_item c ON a.item_id=c.id
		INNER JOIN inv_kategori d ON c.inv_kategori_id=d.id
		WHERE (c.inv_kategori_id IS NOT NULL AND c.inv_kategori_id <>0) 
		and tanggal>='" . $tgl_mulai . "' 
		and tanggal<='" . $tgl_akhir . "'
		and b.status  in ('RELEASE') ";
		// $qry=$qry." order by nama_customer ;";
		$retrieveSupplier = $this->db->query($qry)->result_array();

		foreach ($retrieveSupplier as $key => $s) {
			$html = $html . "<tr>";
			$totalRp = 0;
			$nourut = $nourut + 1;
			$html = $html . "<td style='text-align: center'>" . $nourut . "</td>";
			$html = $html . "<td style='text-align: left'>" . $s['kategori'] . "</td>";
			for ($i = 1; $i < (12 + 1); $i++) {

				$yymm = $tahun  . '-' . sprintf("%02d", $i);

				$retrieveKgSupp = $this->db->query("SELECT 
				SUM(a.total) AS jml_kat_rp
				FROM sls_so_dt a
				INNER JOIN sls_ttb_ht b ON a.so_hd_id=b.id
				LEFT JOIN prc_pp_dt c ON a.pp_dt_id=c.id
				LEFT JOIN prc_pp_ht d ON c.pp_hd_id=d.id
				LEFT JOIN gbm_customer e ON b.customer_id=e.id
				LEFT JOIN inv_item f ON a.item_id=f.id
				LEFT JOIN gbm_organisasi g ON b.lokasi_id=g.id 
				LEFT JOIN inv_kategori h ON f.inv_kategori_id=h.id
				WHERE DATE_FORMAT(b.tanggal, '%Y-%m')='" . $yymm . "'
				AND f.inv_kategori_id=" . $s['inv_kategori_id'] . "
				and b.status  in ('RELEASE')
				")->row_array();

				$jmlRpKt = $retrieveKgSupp['jml_kat_rp'] ? $retrieveKgSupp['jml_kat_rp'] : 0;

				$totalPerBulan[$i - 1] = ($totalPerBulan[$i - 1] ? $totalPerBulan[$i - 1] : 0) + $jmlRpKt;

				$totalRp = $totalRp + $jmlRpKt;

				$grandtotal = $grandtotal + $jmlRpKt;

				$html = $html . "<td style='text-align: right'>" . $this->format_number_report($jmlRpKt) . " </td>";
			}
			$html = $html . "<td style='text-align: right'>" . $this->format_number_report($totalRp) . " </td>";
			$html = $html . "</tr>";
		}
		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: center'> </td>";
		$html = $html . "<td style='text-align: center'></td>";
		for ($i = 1; $i < (12 + 1); $i++) {

			$html = $html . "<td style='text-align: right'>" . $this->format_number_report($totalPerBulan[$i - 1]) . " </td>";
		}
		$html = $html . "<td style='text-align: right'>" . $this->format_number_report($grandtotal) . " </td>";
		$html = $html . "</tr>";
		$html = $html . "</table>";

		if ($format_laporan == 'xls') {
			echo $html;
		} else if ($format_laporan == 'pdf') {
			$filename = 'report_' . time();
			// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
			$dompdf = new DOMPDF;
			$dompdf->loadHtml($html);
			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			$filename = 'report_' . time();
			$x          = 400;
			$y          = 570;
			$text       = "{PAGE_NUM} of {PAGE_COUNT}";
			$font       = null; // $dompdf->getFontMetrics()->get_font('Helvetica', 'normal');
			$size       = 10;
			$color      = array(0, 0, 0);
			$word_space = 0.0;
			$char_space = 0.0;
			$angle      = 0.0;

			$dompdf->getCanvas()->page_text(
				$x,
				$y,
				$text,
				$font,
				$size,
				$color,
				$word_space,
				$char_space,
				$angle
			);
			$dompdf->stream($filename . ".pdf", array("Attachment" => 0));
		} else {
			echo $html;
		}
	}
	function format_number_report($angka)
	{
		$format_laporan     = $this->post('format_laporan', true);
		$tipe_laporan = $this->post('tipe_laporan', true);
		if ($tipe_laporan) {
			$format_laporan = $tipe_laporan;
		}
		// if ($fmt_laporan ) {
		// 	$format_laporan     = $this->post('format_laporan', true);
		// }else{
		// 	return $this->format_number_report($angka);
		// }
		if ($format_laporan == 'xls' || $format_laporan == 'excel') {
			return $angka;
		} else {
			if ($angka == 0) {
				return 0;
			}
			return number_format($angka, 2);
		}
	}

	function laporanTopTen_post()
	{
		error_reporting(0);

		$data = [];
		if (isset($this->post()['bulan'])) {
			$mm = $this->post('bulan', true);
			$yyyy = $this->post('tahun', true);

			$format_laporan =  $this->post('format_laporan', true);
		} else {
			$mm = 12;
			$yyyy = 2023;
			$format_laporan =  'view';
		}



		$resultPO = [];
		$yyyymm = $yyyy .  sprintf("%02s", $mm);
		$bln = $mm;
		$thn = $yyyy;
		$bulan_tahun = [];
		for ($i = 6; $i > 0; $i--) {
			$yyyymm = $thn . sprintf("%02s", $bln);
			$bulan_tahun[$yyyymm] = convert_year_month($yyyymm);
			$queryPo = "	SELECT 
				a.customer_id,
				b.nama_customer as nama_customer,
				sum(grand_total)as amount
				from sls_ttb_ht a 
				INNER JOIN gbm_customer b on a.customer_id=b.id
				INNER JOIN acc_mata_uang c on a.mata_uang_id=c.id
				INNER JOIN prc_syarat_bayar d ON a.syarat_bayar_id=d.id
				where DATE_FORMAT(a.tanggal, '%Y%m')='" . $yyyymm . "' 
				and a.status  in ('RELEASE')
				group by a.customer_id,b.nama_customer order  by sum(grand_total) DESC
				limit 10
				";
			$resPo = $this->db->query($queryPo)->result_array();
			foreach ($resPo as $key => $po) {
				$resultPO[$yyyymm][] = $po;
			}
			$bln = $bln - 1;
			if ($bln == 0) {
				$bln = 12;
				$thn = $thn - 1;
			}
		}


		$data['so'] = 	$resultPO;
		$data['bulan_tahun'] = array_reverse($bulan_tahun, true);
		$data['format_laporan'] = $format_laporan;
		// echo (json_encode($data));
		// exit;
		//var_dump( array_reverse($bulan_tahun,true));exit();
		$html = $this->load->view('Sls_So_Laporan_top10', $data, true);

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		// echo $html;
		if ($format_laporan == 'xls') {
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			$spreadsheet = $reader->loadFromString($html);
			// $reader->setSheetIndex(1);
			//$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			$objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=test.xlsx");
			header("Content-Transfer-Encoding: binary ");

			ob_end_clean();
			ob_start();
			$objWriter->save('php://output');
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
			$dompdf = new DOMPDF;
			$dompdf->loadHtml($html);
			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			$filename = 'report_' . time();
			$x          = 400;
			$y          = 570;
			$text       = "{PAGE_NUM} of {PAGE_COUNT}";
			$font       = null; // $dompdf->getFontMetrics()->get_font('Helvetica', 'normal');
			$size       = 10;
			$color      = array(0, 0, 0);
			$word_space = 0.0;
			$char_space = 0.0;
			$angle      = 0.0;

			$dompdf->getCanvas()->page_text(
				$x,
				$y,
				$text,
				$font,
				$size,
				$color,
				$word_space,
				$char_space,
				$angle
			);
			$dompdf->stream($filename . ".pdf", array("Attachment" => 0));
		}
	}
	public function sendNotification($token, $title, $message, $payload)
	{
		// $token = $token;
		// $message = "Test notification message";
		$this->load->library('Fcm');
		$payload = array("click_action" => "FLUTTER_NOTIFICATION_CLICK");
		$this->fcm->setTitle($title);
		$this->fcm->setMessage($message);

		/**
		 * set to true if the notificaton is used to invoke a function
		 * in the background
		 */
		$this->fcm->setIsBackground(false);

		/**
		 * payload is userd to send additional data in the notification
		 * This is purticularly useful for invoking functions in background
		 * -----------------------------------------------------------------
		 * set payload as null if no custom data is passing in the notification
		 */
		//$payload = array('notification' => '');
		$this->fcm->setPayload($payload);

		/**
		 * Send images in the notification
		 */
		$this->fcm->setImage(base_url('logo_antech.png'));

		/**
		 * Get the compiled notification data as an array
		 */
		$json = $this->fcm->getPush();

		$p = $this->fcm->send($token, $json);

		// print_r($p);
	}
	function get_path_file($file = '')
	{
		return '/plantation/userfiles/files/' . $file;
		//return	$_SERVER['SERVER_NAME'] . "/" . 'hcis_folder' . "/userfiles/files/" . $file;
	}
	function test_file_get_content_post()
	{
		ini_set('display_errors', 1);
		//ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);
		$curl = curl_init('http://localhost/plantationlive-api/logo_perusahaan.png');
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);


		$html = curl_exec($curl);
		//html var will contain the html code 

		if (curl_error($curl)) {
			die(curl_error($curl));
		}
		// Check for HTTP Codes (if you want)
		$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		curl_close($curl);
		var_dump($html);
		return;

		ini_set('max_execution_time', 300);
		$d = file_get_contents(('./logo_perusahaan.png'));
		var_dump($d);
	}
}
