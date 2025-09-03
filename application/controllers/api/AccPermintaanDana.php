<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

class AccPermintaanDana extends  BD_Controller //
{
	public $user_id;
	public $theCredential;
	public $data = array();
	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('AccPermintaanDanaModel');
		$this->load->model('M_DatatablesModel');
		$this->load->model('AccJurnalModel');
		$this->load->library('pdfgenerator');
		$this->load->helper(array('url', 'antech_helper', 'form', 'text',  'security', 'file', 'number', 'date', 'download'));
		$this->load->library('image_lib');
		$this->load->library('upload');
		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id = $this->user_data->id;
	}

	// endpoint/list :POST
	public function list_post()
	{
		$post = $this->post();
		$query  = "SELECT a.*,
		b.nama as lokasi,
		a.id AS id,
		c.user_full_name AS dibuat,
		d.user_full_name AS diubah,
		e.user_full_name AS diposting
		FROM acc_permintaan_dana a 
		LEFT JOIN gbm_organisasi b ON a.lokasi_id=b.id
		LEFT JOIN fwk_users c ON a.dibuat_oleh = c.id
		LEFT JOIN fwk_users d ON a.diubah_oleh = d.id
		LEFT JOIN fwk_users e ON a.diposting_oleh = e.id 
		";

		$search = array('b.nama', 'a.no_transaksi', 'a.tanggal', 'a.nilai', 'a.keterangan');
		$where  = null;
		$isWhere = null;
		// $isWhere = 'artikel.deleted_at IS NULL';
		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}

	// endpoint/ :GET
	function index_get($segment_3 = '')
	{
		$id = $segment_3;
		$retrieve = $this->AccPermintaanDanaModel->retrieve($id);
		$retrieve['file_info']         = get_file_info($this->get_path_file($retrieve['upload_file']));
		$retrieve['file_info']['mime'] = get_mime_by_extension($this->get_path_file($retrieve['upload_file']));

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	//  endpoint/getall :GET
	function getAll_get()
	{
		$retrieve = $this->AccPermintaanDanaModel->retrieve_all();
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	//  endpoint/getall :GET
	function getAllByUnit_get($lokasi_id)
	{
		$retrieve = $this->AccPermintaanDanaModel->retrieve_all_by_unit($lokasi_id);
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "OK", "data" => []), REST_Controller::HTTP_OK);
		}
	}

	// endpoint/ :POST
	function index_post()
	{
		$input = $this->post();
		$input['dibuat_oleh'] = $this->user_id;
		$this->load->library('Autonumber');
		$input['no_transaksi'] = $this->autonumber->acc_permintaan_dana($input['lokasi_id'], $input['tanggal']);
		$retrieve = $this->AccPermintaanDanaModel->create($input);
		$this->set_response(array("status" => "OK", "data" => 	$input['no_transaksi']), REST_Controller::HTTP_OK);
	}

	// endpoint/ :PUT
	function index_put($segment_3 = '')
	{
		$id = (int)$segment_3;
		$res = $this->AccPermintaanDanaModel->retrieve($id);
		if (empty($res)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
		$data = $this->put();
		$data['diubah_oleh'] = $this->user_id;
		$data['dibuat_oleh'] = $this->user_id;
		$retrieve = $this->AccPermintaanDanaModel->update($res['id'], $data);
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
	}

	// endpoint/ :DELETE
	function index_delete($segment_3 = '')
	{
		$id = (int)$segment_3;
		$res = $this->AccPermintaanDanaModel->retrieve($id);
		if (empty($res)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
		$retrieve =  $this->AccPermintaanDanaModel->delete($res['id']);
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
	}
	function upload_post($segment_3 = '')
	{
		$id = (int)$segment_3;

		$permintaan_dana = $this->AccPermintaanDanaModel->retrieve($id);

		// $this->set_response(['status' => 'OK', 'debug'=>$this->post()], REST_Controller::HTTP_CREATED);

		if (empty($permintaan_dana)) {
			$this->set_response([
				'status' => false,
				'message' => 'Data Tidak ditemukan',
			], REST_Controller::HTTP_NOT_FOUND);
			return;
		}

		$config['upload_path']   = $_SERVER['DOCUMENT_ROOT'] . "/" . "plantation" . "/userfiles/files";
		$config['allowed_types'] = 'doc|zip|rar|txt|docx|xls|xlsx|pdf|tar|gz|jpg|jpeg|JPG|JPEG|png|ppt|pptx';
		$config['max_size']      = '0';
		$config['max_width']     = '0';
		$config['max_height']    = '0';
		$config['file_name']     = url_title('PDO_' . $permintaan_dana['no_transaksi'] . '_' . time(), '_', TRUE);
		$this->upload->initialize($config);
		$error_upload = array();

		if ($this->upload->do_upload()) {
			$upload_data = $this->upload->data();
			$error_upload = $this->upload->display_errors();
		} else {
			$upload_data['file_name'] = $permintaan_dana['upload_file'];
			$error_upload = $this->upload->display_errors();
		}
		$file = $upload_data['file_name'];
		$input = $this->post();
		$permintaan_dana_update = $this->AccPermintaanDanaModel->save_upload($permintaan_dana, $file);

		if ($permintaan_dana_update) {
			$message = [
				'status' => "OK",
				'id' => $permintaan_dana['id'],
				'error' => ($_FILES),
				'er' => $this->upload->display_errors(),
				'upload_data' => $this->upload->data()
			];
			$this->set_response(
				$message,
				REST_Controller::HTTP_CREATED
			);
		} else {
			$this->set_response([
				'status' => 'NOT OK',
				'message' => 'Gagal update',
				'error' => ($_FILES),
				'er' => $this->upload->display_errors(),
				'upload_data' => $this->upload->data()
			], REST_Controller::HTTP_NOT_FOUND);
		}
	}
	public function download_get($id)
	{
		$permintaan_dana = $this->AccPermintaanDanaModel->retrieve($id);
		if (!empty($permintaan_dana['upload_file'])) {
			$target_file = $this->get_path_file($permintaan_dana['upload_file']);
			if (!is_file($target_file)) {
				show_error("Maaf file tidak ditemukan." . $target_file);
			}

			$data_file = file_get_contents($target_file); // Read the file's contents
			$name_file = $permintaan_dana['upload_file'];

			force_download($name_file, $data_file);
		}
	}
	function get_path_file($file = '')
	{
		//  return './'.USERFILES.'/files/'.$file;
		return	$_SERVER['DOCUMENT_ROOT'] . "/" . 'hcis_folder' . "/userfiles/files/" . $file;
	}
	function posting_post($segment_3 = null)
	{
		$id = (int)$segment_3;
		$retrieve_header = $this->AccPermintaanDanaModel->retrieve($id);
		if (empty($retrieve_header)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data Untuk diposting"), REST_Controller::HTTP_NOT_FOUND);
			return;
		} else {
			if ($retrieve_header['is_posting'] == 1) {
				$this->set_response(array("status" => "NOT OK", "data" => "Data Sudah diposting"), REST_Controller::HTTP_NOT_FOUND);
				return;
			}
		}


		$data = $this->post();
		$data['diposting_oleh'] = $this->user_id;
		$res = $this->AccPermintaanDanaModel->posting($id, $data);
		if (!empty($res)) {
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function getAkunUangMuka_get()
	{

		$retrieve = $this->db->query("select b.*
		from acc_auto_jurnal a
		inner join acc_akun b on a.acc_akun_id =b.id
		where a.kode='UANG_MUKA'")->result_array();

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function print_slip_get($segment_3 = '')
	{


		$id = (int)$segment_3;
		$data = [];

		$hd = $this->db->query("select a.*,d.nama as lokasi
		from acc_permintaan_dana a 
	     INNER JOIN gbm_organisasi d on a.lokasi_id=d.id
		where a.id=" . $id)->row_array();
		$data['hd'] = $hd;

		$html = $this->load->view('AccSlipPermintaanDana', $data, true);

		$filename = 'AccSlipPDO_' . time();
		$this->pdfgenerator->generate($html, $filename, true, 'A4', 'portrait');
		echo $html;
	}


	function laporan_post()
	{
		error_reporting(0);

		$data = [];
		if (isset($this->post()['lokasi_id'])) {
			$input = $this->post();
			$input['lokasi'] = $input['lokasi_id'];
		} else {
			$input = [
				'lokasi_id' => 252,
				'tgl_mulai' => '2000-01-01',
				'tgl_akhir' => '2022-05-11',
			];
		}


		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');

		$query = $this->db->query("SELECT
		a.*,
		b.nama AS acc_akun,
		c.nama AS acc_akun_permintaan_dana,
		a.id AS id, d.no_transaksi as no_realisasi,d.tanggal as tanggal_realisasi
		FROM acc_permintaan_dana a
		LEFT JOIN acc_akun b ON a.acc_akun_id=b.id 
		LEFT JOIN acc_akun c ON a.acc_akun_permintaan_dana_id=c.id 
		LEFT JOIN acc_permintaan_dana_realisasi d ON a.id=d.acc_permintaan_dana_id 
		WHERE
		a.lokasi_id = " . $input['lokasi_id'] . "
		&&
		DATE(a.tanggal) >= '" . $input['tgl_mulai'] . "'
		&&
		DATE(a.tanggal) <= '" . $input['tgl_akhir'] . "'
		");

		$input['lokasi'] = $this->db->query("SELECT * FROM gbm_organisasi WHERE id=" . $input['lokasi_id'])->row_array()['nama'];

		$data['data'] = $query->result_array();
		$data['input'] = $input;

		$html = $this->load->view('AccPermintaanDana_laporan', $data, true);

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		echo $html;
	}
	function laporan_permintaan_dana_realisasi_post()
	{
		$format_laporan =  $this->post('format_laporan', true);
		$lokasi_id     = $this->post('lokasi_id', true);
		$permintaan_dana_id =  $this->post('permintaan_dana_id', true);
		// $permintaan_dana_id =  11;
		if (is_null($format_laporan)) {
			$format_laporan = 'view';
		}
		$permintaan_dana   = $this->db->query("select * from acc_permintaan_dana where id=" . $permintaan_dana_id . "")->row_array();
		$kas_bank   = $this->db->query("
		select a.tanggal,a.no_transaksi,c.kode as kode_akun,c.nama as nama_akun,b.debet,b.kredit,b.ket 
		from acc_kasbank_ht a inner join 
		acc_kasbank_dt b on a.id=b.jurnal_id
		inner join acc_akun c on b.acc_akun_id=c.id
		 where a.permintaan_id=" . $permintaan_dana_id . "
		 order by a.tanggal")->result_array();

		
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report();
		} else {
			$html = get_header_report();
		}
		$html = $html . '<div class="row">
  <div class="span12">
	  <br>
	  <div class="kop-print">
		 	  </div>
	  <hr class="kop-print-hr">
  </div>
  </div>
  <h2>Laporan Realisasi Permintaan Dana</h2>';

  $html = $html . ' <table  border="1" width="50%" style="border-collapse: collapse;" >
			
				<tr>
					<td>No Permintaan Dana </td><td width="2%">:</td><td>' . $permintaan_dana['no_transaksi'] . '</td>
				</tr>	
				<tr>
					<td>Tanggal </td><td width="2%">:</td><td>' . tgl_indo( $permintaan_dana['tanggal']) . '</td>
				</tr>	
				<tr>
					<td>Keterangan </td><td width="2%">:</td><td>' . $permintaan_dana['keterangan'] . '</td>
				</tr>	
				<tr>
					<td>Nilai </td><td>:</td width="2%"><td>' . number_format( $permintaan_dana['nilai']) . '</td>
				</tr>	
				</table>
				<br>
				<br>'
				;
		
		$html = $html . ' <table  border="1" width="100%" style="border-collapse: collapse;" >
			<thead>
				<tr>
					<th  width="4%">No.</th>
					<th >No Transaksi</th>
					<th >Tanggal</th>
					<th >Kode Akun</th>
					<th >Nama Akun</th>
					<th >Keterangan</th>

					<th style="text-align: center;">Dr</th>
					<th  style="text-align: center;">Cr</th>			
				</tr>
				
			</thead>
			<tbody>';


		$no = 0;
		$jumlah = 0;
		$dr = 0;
		$cr = 0;

		foreach ($kas_bank as $key => $m) {
			//$actual_link = "http://$_SERVER[HTTP_HOST]" . "/plantation-api/api/GlobalReport/acc_kasbank_laporan_saldo_rinci/" . $tanggal_mulai . "/" . $tanggal_akhir .  "/" . $m['id'] .  "/" . $lokasi_id . "";

			$no++;
			$jumlah =$jumlah+( $m['debet'] - $m['kredit']);
			$dr =$dr+( $m['debet']);
			$cr =$cr+( $m['kredit']);
		
			$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative;">
							' . ($no) . '

						</td>
						<td> ' . $m['no_transaksi'] . '
						<td> ' .  tgl_indo($m['tanggal']) . '
						<td> ' . $m['kode_akun'] . '
						</td>
						<td> ' . $m['nama_akun'] . ' 
						</td>
						<td> ' . $m['ket'] . ' 
						</td>		
						<td style="text-align: right;">' . number_format($m['debet']) . '</td> 
						<td style="text-align: right;">' . number_format($m['kredit']) . ' </td>
						
						';

			$html = $html . '
									
					</tr>';
		}

		$html = $html . ' 	
						<tr class=":: arc-content">
						<td colspan="6">
							&nbsp;
						</td>						
						<td style="text-align: right;" ><b>' . number_format($dr) . '</b></td>
						<td style="text-align: right;" ><b>' . number_format($cr) . '</b></td>
						
						</tr>
						</tbody>
					</table>
						';
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
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}

	function laporan_permintaan_dana_post()
	{
		$format_laporan =  $this->post('format_laporan', true);
		$lokasi_id = $this->post('lokasi_id', true);
		$tanggal_mulai =  $this->post('tgl_mulai', true);
		$tanggal_akhir = $this->post('tgl_akhir', true);
		if (is_null($format_laporan)) {
			$format_laporan = 'view';
		}

		// $format_laporan =  'view';
		// $lokasi_id = null;
		// $tanggal_mulai =  '2021-01-01';
		// $tanggal_akhir ='2023-01-01';

		$queryTransaksi   = "SELECT a.*,b.nama as lokasi  FROM acc_permintaan_dana a inner join gbm_organisasi b 
		on a.lokasi_id=b.id
         where  a.tanggal >= '" . $tanggal_mulai . "'   and a.tanggal <= '" . $tanggal_akhir . "'";
		$nama_lokasi = "Semua";
		if ($lokasi_id) {
			
				$queryTransaksi = $queryTransaksi . " and a.lokasi_id=" . $lokasi_id . "";
				$lokasi = $this->db->query("select * from gbm_organisasi where id=" . $lokasi_id . ";")->row_array();

				$nama_lokasi = $lokasi['nama'];
			
		}
		$queryTransaksi = $queryTransaksi . " order by a.tanggal  ; ";


		$results  = $this->db->query($queryTransaksi)->result_array();
		$data['transaksi'] = $results;

		// $this->set_response(array("status" => "OK", "data" => $data), REST_Controller::HTTP_OK);
		// return;
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report();
		} else {
			$html = get_header_report_V2();
		}

		$html = $html . '<div class="row">
  <div class="span12">
	  <br>
	<div class="kop-print">
    <div class="kop-nama">KLINIK ANNAJAH</div>
    <div class="kop-info"> Jl Sukarno Hatta No 12 No 12, Bandung Jawa Barat 14450</div>
    <div class="kop-info">Telp : (021) 6684055</div>
  </div>
	  <hr class="kop-print-hr">
  </div>
  </div>
  <h3 class="title">LAPORAN PERMINTAAN DANA</h3>
  <table class="no_border" style="width:30%">
			
			<tr>
					<td>Lokasi</td>
					<td>:</td>
					<td>' . $nama_lokasi . '</td>
			</tr>
			<tr>	
					<td>Periode</td>
					<td>:</td>
					<td>' . $tanggal_mulai . ' s/d ' . $tanggal_akhir . '</td>
			</tr>

			
	</table>
	<br>
  ';

		$html = $html . '<table  border="1" width="100%" style="border-collapse: collapse;" >
			<thead>
			<tr>
                    <th width="4%" rowspan="2">No.</th>
                    <th>Lokasi</th>
                    <th >No Transaksi</th>
					<th >Tanggal</th>
                    <th >Keterangan</th>
                    <th  style="text-align: center;">Nilai </th>
                   
                </tr>
						
			</thead>
			<tbody>	';


		$total = 0;
		$no = 0;
		foreach ($data['transaksi'] as $key => $m) {
			$total = $total + $m['nilai'];

			$no++;
			$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative;">
							' . ($no) . '

						</td>
						<td>
						' . $m['lokasi'] . ' 
						
						</td>
						<td>
						' . $m['no_transaksi'] . ' 
						
						</td>
						<td>
						' . $m['tanggal'] . ' 
						
						</td>
						<td>
							' . $m['keterangan'] . ' 
						</td>
						<td style="text-align: right;">' . number_format($m['nilai']) . '</td>
						</tr>';
		}

		$html = $html . ' 	
			<tr class=":: arc-content">
                    <td style="position:relative;">
                        &nbsp;

                    </td>
                    <td>
                        &nbsp;
                    </td>
                    <td>
                        &nbsp;
                    </td>
                    <td>
                        &nbsp;
                    </td>
					<td>
					&nbsp;
					</td>
                   
                    <td style="text-align: right;">
                        ' . number_format($total) . '

                    </td>
                   
                </tr>
				</tbody>
				</table>
						';
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
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}
}
