<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
use Restserver\Libraries\REST_Controller;

class PksLaporanPengolahan extends BD_Controller
{
	public $user_id;
	public $theCredential;

	function __construct()
	{
		parent::__construct();

		$this->load->model('PksLaporanPengolahanModel');
		$this->load->model('GbmOrganisasiModel');
		$this->load->model('M_DatatablesModel');
		$this->load->library('pdfgenerator');
		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id = $this->user_data->id;
	}




	public function laporan_pengolahan_post()
	{
		error_reporting(0);
		
		$data = [];
		if (isset($this->post()['lokasi_id'])) {
			$input = $this->post();
		}else {
			$input = [
				'lokasi_id' => 260,
				'tgl_mulai' => '2020-01-01',
				'tgl_akhir' => '2022-12-31',
			];
		}
		$tipe_laporan = $this->post('tipe_laporan', true);

		$gbm_organisasi = $this->GbmOrganisasiModel->retrieve($input['lokasi_id']);
		$input['lokasi_nama'] = $gbm_organisasi['nama'];

		
		$data['input'] = $input;
		$data['tipe_laporan'] = $tipe_laporan;
		
		// $pengolahan = $this->PksLaporanPengolahanModel->laporanPengolahan( $input );
		$pengolahan = $this->db->query("SELECT * FROM pks_pengolahan_ht a WHERE mill_id=".$input['lokasi_id']." & DATE(a.tanggal) >= '".$input['tgl_mulai']."' & DATE(a.tanggal) <= '".$input['tgl_akhir']."'")->result_array();

		$result = [];
		foreach ($pengolahan as $key=>$val) {
			$row = $val;
			
			$detail = $this->db->query("SELECT 
			a.*, 
			b.nama AS shift,
			c.nama AS mandor,
			d.nama AS asisten,
			a.id AS id
			FROM pks_pengolahan_dt a 
			INNER JOIN pks_shift b ON a.shift_id=b.id 
			LEFT JOIN karyawan c ON a.mandor_id=c.id
			LEFT JOIN karyawan d ON a.asisten_id=d.id
			WHERE a.pengolahan_id=".$val['id'])->result_array();
			$row['detail'] = $detail;

			$mesin = $this->db->query("SELECT 
			a.*,
			b.nama AS stasiun,
			c.nama AS mesin,
			a.id AS id
			FROM pks_pengolahan_mesin a
			LEFT JOIN gbm_organisasi b ON a.station_id=.b.id
			LEFT JOIN gbm_organisasi c ON a.mesin_id=c.id
			WHERE a.pengolahan_id=".$val['id'])->result_array();
			$row['mesin'] = $mesin;

			$result[] = $row;
		}

		// var_dump($pengolahan); die;


		$data['pengolahan'] = $result;
		$data['tipe_laporan'] = $tipe_laporan;
	

		$html = $this->load->view('PksLaporanPengolahan_laporan', $data, true);
		if ($tipe_laporan == 'excel') {
			// $objWriter->save('php://output');
			echo $html;
		} else if($tipe_laporan == 'pdf'){
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		} else {
			echo $html;
		}
	}












	public function laporan_lembur_post()
	{
		error_reporting(0);
		
		$data = [];
		if (isset($this->post()['karyawan_id'])) {
			$input = $this->post();
		}else {
			$input = [
				'karyawan_id' => 170,
				'tgl_mulai' => '2021-05-05',
				'tgl_akhir' => '2022-01-23',
			];
		}

		// $input['tgl_mulai'] = $input['tahun'].'-'.$input['bulan'].'-01';
		// $input['tgl_akhir'] = $input['tahun'].'-'.$input['bulan'].'-31';

		$data['input'] = $input;

		$lembur = $this->PksLaporanPengolahanModel->laporanLembur( $input );
		$data['karyawan'] = $this->PksLaporanPengolahanModel->laporanGetKaryawan( $input['karyawan_id'] );
		// $karyawan_id = array_count_values(array_column($lembur, 'karyawan_id'));

		// $lembur_jenis = $this->PksLaporanPengolahanModel->laporanLemburJenis( $input );
		// $data['lembur_kode'] = array_count_values(array_column($lembur_jenis, 'kode'));

		// $data['lembur_jenis'] = $lembur_jenis;

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


		// foreach ($karyawan_id as $key=>$val) {
		// 	foreach ($data['lembur_kode'] as $__key=>$__val) {
		// 		$row[$key][$__key] = 0;
		// 	}
		// 	foreach ($lembur as $_key=>$_val) {
		// 		if ($key == $_val['karyawan_id']) {
		// 			$row[$key]['nip'] = $_val['nip'];
		// 			$row[$key]['nama'] = $_val['nama'];
		// 			$row[$key][$_val['kode']] += 1;
		// 			$tgl = explode('-', $_val['tanggal']);
		// 			$row[$key][sprintf('%01d',$tgl[2])] = $_val;		
		// 			$result = $row;
		// 		}
		// 	}
		// }
		$lembur = $result;

		$data['lembur'] = $lembur;
		$data['date_loop'] = $date_loopline;

		$this->load->view('PksLaporanPengolahan_laporan', $data);
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
				'lokasi_id' => 256,
				'bulan' => '01',
				'tahun' => '2022',
			];
		}

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
		
		$lembur = $this->PksLaporanPengolahanModel->laporanLemburBulanan( $input );
		$karyawan_id = array_count_values(array_column($lembur, 'karyawan_id'));

		// $lembur_jenis = $this->PksLaporanPengolahanModel->laporanLemburJenis( $input );
		// $data['lembur_kode'] = array_count_values(array_column($lembur_jenis, 'kode'));
		// $data['lembur_jenis'] = $lembur_jenis;

		$result = [];
		foreach ($karyawan_id as $key=>$val) {
			
			// foreach ($data['lembur_kode'] as $__key=>$__val) {
			// 	$row[$key][$__key] = 0;
			// }

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

		$this->load->view('PksLaporanPengolahan_bulanan_laporan', $data);
	}







	

	

}
