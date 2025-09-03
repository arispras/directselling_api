<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;
use Restserver\Libraries\REST_Controller;

class HrmsLaporanLembur extends Rest_Controller
{
	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('HrmsLaporanLemburModel');
		$this->load->model('GbmOrganisasiModel');
		$this->load->model('M_DatatablesModel');
		$this->load->library('pdfgenerator');
		$this->load->library('pdfgenerator');
		
	
	}



	public function laporan_lembur_post()
	{
		error_reporting(0);
		
		$data = [];
		if (isset($this->post()['karyawan_id'])) {
			$input = $this->post();
		}else {
			$input = [
				'lokasi_id' => 260,
				'karyawan_id' => 223,
				'tgl_mulai' => '2021-05-05',
				'tgl_akhir' => '2022-01-23',
				'format_laporan'=> 'view',
			];
		}

		$format_laporan = $input['format_laporan'];

		
		$gbm_organisasi = $this->GbmOrganisasiModel->retrieve($input['lokasi_id']);
		$input['lokasi_nama'] = $gbm_organisasi['nama'];
		
		$data['input'] = $input;
		

		$lembur = $this->HrmsLaporanLemburModel->laporanLembur( $input );
		$data['karyawan'] = $this->HrmsLaporanLemburModel->laporanGetKaryawan( $input['karyawan_id'] );

		$result = [];
		foreach ($lembur as $key=>$val) {
			$row = $val;

			$result[] = $row;
		}
		$result_total = [];
		foreach ($lembur as $key=>$val) {
			$result_total[$val['kode']] += 1;
		}
		$data['lembur_total'] = $result_total;

		$lembur = $result;

		$data['lembur'] = $lembur;
		$data['date_loop'] = $date_loopline;
		$data['format_laporan'] = $format_laporan;

		$html = $this->load->view('HrmsLaporanLembur_laporan', $data, true);

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



	function laporan_lembur_bulanan_post()
	{
		error_reporting(0);
		
		$data = [];
		if (isset($this->post()['lokasi_id'])) {
			$input = $this->post();
			$input['lokasi'] = $input['lokasi_id'];
		}else {
			$input = [
				'lokasi_id' => 260,
				'bulan' => '08',
				'tahun' => '2022',
				'format_laporan'=> 'view',
			];
		}

		$format_laporan = $input['format_laporan'];

		$input['tgl_mulai'] = $input['tahun'].'-'.$input['bulan'].'-01';
		$input['tgl_akhir'] = $input['tahun'].'-'.$input['bulan'].'-31';

		$gbm_organisasi = $this->GbmOrganisasiModel->retrieve($input['lokasi_id']);
		$input['lokasi_nama'] = $gbm_organisasi['nama'];
		
		$data['input'] = $input;

		$data['bulan'] = [ "", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
		
		$date_loop = cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y')) + 1;
		$date_loopline = [];
		for ($i=1; $i<$date_loop; $i++) {
			$date_loopline[$i] = [];
		}
		
		$lembur = $this->HrmsLaporanLemburModel->laporanLemburBulanan( $input );
		$karyawan_id = array_count_values(array_column($lembur, 'karyawan_id'));

		$result = [];
		foreach ($karyawan_id as $key=>$val) {

			foreach ($lembur as $_key=>$_val) {
				if ($key == $_val['karyawan_id']) {

					$row[$key]['nip'] = $_val['nip'];
					$row[$key]['nama'] = $_val['nama'];
					$row[$key]['sub_bagian'] = $_val['sub_bagian'];

					// $row[$key][$_val['kode']] += 1;
					
					$tgl = explode('-', $_val['tanggal']);
					$row[$key][sprintf('%01d',$tgl[2])] = $_val;
					
					$result = $row;
				}
			}
		}
		$lembur = $result;

		$data['lembur'] = $lembur;
		$data['date_loop'] = $date_loopline;
		$data['format_laporan'] = $format_laporan;

		$html = $this->load->view('HrmsLaporanLembur_bulanan_laporan', $data, true);

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
