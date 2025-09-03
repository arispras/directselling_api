<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
use Restserver\Libraries\REST_Controller;

class PksProduksi extends Rest_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('PksProduksiModel');
		$this->load->model('GbmOrganisasiModel');
		$this->load->model('M_DatatablesModel');
		$this->load->library('pdfgenerator');
	}
	
	// public function list_post()
	// {
	// 	$post = $this->post();
	// 	$query  = "SELECT * from pks_produksi ";
	// 	$search = array('no_surat');
	// 	$where  = null;
	// 	$isWhere = null;
	// 	// $isWhere = 'artikel.deleted_at IS NULL';	
	// 	$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
	// 	$this->set_response($data, REST_Controller::HTTP_OK);
	// }

	// function index_get($segment_3 = '')
	// {
	// 	$id = $segment_3;
	// 	$retrieve = $this->PksProduksiModel->retrieve($id);	
	// 	if (!empty($retrieve)) {
	// 		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
	// 	} else {
	// 		$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
	// 	}
	// }

	// function getAll_get()
	// {	
	// 	$retrieve = $this->PksProduksiModel->retrieve_all_kategori();
	// 	if (!empty($retrieve)) {
	// 		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
	// 	} else {
	// 		$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
	// 	}
	// }

	// function index_post()
	// {
	// 	$input = $this->post();
	// 	$input['no_surat'] = $this->getLastNumber('pks_produksi', 'no_surat', 'SR');
	// 	$retrieve=  $this->PksProduksiModel->create($input);
	// 	$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
	// }
	
	// function index_put($segment_3 = '')
	// {
	// 	$input = $this->put();
	// 	$id = (int)$segment_3;
	// 	$kategori = $this->PksProduksiModel->retrieve( $id);
	// 	if (empty($kategori)) {
	// 		$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);	
	// 	}
	// 	$retrieve=   $this->PksProduksiModel->update($kategori['id'], $input );
	// 	$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
	// }
	
	// function index_delete($segment_3 = '')
	// {
	// 	$id = (int)$segment_3;
	// 	$kategori = $this->PksProduksiModel->retrieve($id);
	// 	if (empty($kategori)) {
	// 		$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
	// 	}
	// 	$retrieve=  $this->PksProduksiModel->delete($kategori['id']);
	// 	$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
	// }
	
	
	
	// function getLastNumber($table_name = '', $field = '', $prefix = '')
	// {
	// 	$lastnumber = $this->db->query("select  max(" . $field . ")as last from " . $table_name . "")->row_array();
	// 	// var_dump($lastnumber);exit();
	// 	if (!empty($lastnumber['last'])) {
	// 		$str = (substr($lastnumber['last'], -6));
	// 		$snumber = (int)$str + 1;
	// 	} else {
	// 		$snumber = 1;
	// 	}
	// 	$strnumber = sprintf("%06s", $snumber);
	// 	return  $prefix . $strnumber;
	// 	// $index = 11;
	// 	// $prefix = 'B';
	// 	// echo sprintf("%s%011s", $prefix, $index);
	// }

	
	
	// function print_slip_get($segment_3 = '')
	// {
	// 	$id = (int)$segment_3;
	// 	$data = [];
	// 	$PksProduksi = $this->PksProduksiModel->print_slip( $id );
	// 	$data['PksProduksi'] = $PksProduksi;
	// 	$html = $this->load->view('PksProduksi_print', $data, true);
	// 	$filename = 'report_' . time();
	// 	$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
	// }


	// function laporan_rekap_pengiriman_post()
	// {
	// 	error_reporting(0);
	// 	$data = [];
	// 	$input = $this->post();
	// 	$data['PksProduksi'] = $this->PksProduksiModel->laporanRekapPengiriman( $input );
	// 	$data['count'] = $this->PksProduksiModel->laporanRekapPengirimanCount( $input );
	// 	$data['input'] = $input;
	// 	$html = $this->load->view('PksProduksi_laporan', $data, true);
	// 	// $filename = 'report_' . time();
	// 	// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
	// 	echo $html;
	// }




	function laporan_produksi_post()
	{
		error_reporting(0);
		
		$data = [];
		if (isset($this->post()['lokasi_id'])) {
			$input = $this->post();
			$input['lokasi'] = $input['lokasi_id'];
		}else {
			$input = [
				'lokasi'=> 260,
				'tanggal'=> '2022-01-26',
			];
		}


		$gbm_organisasi = $this->GbmOrganisasiModel->retrieve($input['lokasi']);
		$input['lokasi_nama'] = $gbm_organisasi['nama'];
		$data['input'] = $input;

		// $produksi = $this->PksProduksiModel->laporanProduksi( $input );
		
		$produksi = [];
		
		$result = [];
		foreach ($produksi as $key=>$val) {
			
			$result[] = $val;
		}
		
		$data['produksi'] = $result;
		$data['sounding'] = $this->PksProduksiModel->retrieve_result_where(
			'pks_tanki a',
			[
				'a.mill_id'=>$input['lokasi'],
				'd.nama'=>'CPO',
				'b.tanggal'=>$input['tanggal'],
				'c.tanggal'=>$input['tanggal']
			],
			[
				'pks_sounding b'=> 'b.tanki_id = a.id',
				'pks_lab c'=> 'c.tanki_id = a.id',
				'inv_item d'=> 'a.produk_id = d.id'
			],
			"a.*, b.*, c.*"
		);

		$data['lab_pengolahan'] = $this->PksProduksiModel->retrieve_where(
			'pks_lab_pengolahan a',
			[
				'a.tanggal'=>$input['tanggal']
			],
			null,
			"a.*"
		);
		
		// $data['sounding'] = $this->db->query("SELECT * FROM pks_lab")->result_array();

		$this->load->view('PksProduksi_laporan', $data);
	}



	

	function laporan_produksi_harian_post()
	{
		error_reporting(0);
		
		$data = [];
		if (isset($this->post()['lokasi_id'])) {
			$input = $this->post();
			$input['lokasi'] = $input['lokasi_id'];
		}else {
			$input = [
				'lokasi'=> 256,
				'tgl_mulai'=> '2021-12-5',
				'tgl_akhir'=> '2021-12-20',
			];
		}

		// print_r($input); die;

		// $parseTanggal = explode('-', $input['tanggal']);
		// $parseTanggalAkhir = explode('-', $input['tgl_akhir']);

		// $input['tgl_mulai'] = $parseTanggal[0].'-'.$parseTanggal[1].'-1';
		// $input['tgl_akhir'] = $input['tanggal'];

		$gbm_organisasi = $this->GbmOrganisasiModel->retrieve($input['lokasi']);
		$input['lokasi_nama'] = $gbm_organisasi['nama'];
		$data['input'] = $input;

		$produksi = $this->PksProduksiModel->laporanProduksiHarian( $input );

		
		$result = [];
		// $tgl_mulai = $parseTanggalMulai[2] + 2;
		// $tgl_akhir = $parseTanggalAkhir[2] + 2;
		// for ($i=1; $i < $count; $i++) {
		// 	$result[$i] = [];
		// 	$result[$i]['lokasi'] = $input['lokasi_nama'];
		// 	$result[$i]['tanggal'] = $parseTanggal[0].'-'.$parseTanggal[1].'-'.$i;
		// }
		
		foreach ($produksi as $key=>$val) {

			// $parse_date = explode('-', $val['tanggal'])[2];
			// $parse_date = sprintf('%01d', $parse_date);

			$result[] = $val;
		}
		

		$data['produksi'] = $result;
		
		

		$this->load->view('PksProduksi_harian_laporan', $data);
	}


	function laporan_produksi_harian_m1_get()
	{
		error_reporting(0);
		
		$data = [];
		if (isset($this->post()['tanggal'])) {
			$input = $this->post();
		}else {
			$input = [
				'lokasi'=> 256,
				'tanggal'=> '2021-12-23',
			];
		}
		$parseTanggal = explode('-', $input['tanggal']);

		$input['tgl_mulai'] = $parseTanggal[0].'-'.$parseTanggal[1].'-1';
		$input['tgl_akhir'] = $input['tanggal'];

		$gbm_organisasi = $this->GbmOrganisasiModel->retrieve($input['lokasi']);
		$input['lokasi_nama'] = $gbm_organisasi['nama'];
		$data['input'] = $input;

		$produksi = $this->PksProduksiModel->laporanProduksiHarianM1( $input );

		
		$result = [];
		for ($i=1; $i<24; $i++) {
			$result[$i] = [];
			$result[$i]['tanggal'] = $parseTanggal[0].'-'.$parseTanggal[1].'-'.$i;
		}
		
		foreach ($produksi as $key=>$val) {
			
			$val['sounding'] = $this->PksProduksiModel->retrieve_where('pks_sounding',[
				'mill_id'=>$val['lokasi_id'],
			]);
			$val['timbangan_kirim'] = $this->PksProduksiModel->retrieve_result_where(
				'pks_timbangan_kirim a',
				[
					'a.mill_id'=>$val['lokasi_id'],
					'a.tanggal'=>$val['tanggal'],
					'b.nama'=>'TBS',
				],
				[
					'inv_item b'=>'a.item_id = b.id'
				]
			);
			$val['timbangan_masuk'] = $this->PksProduksiModel->retrieve_result_where(
				'pks_timbangan a',
				[
					'a.mill_id'=>$val['lokasi_id'],
					'a.tanggal'=>$val['tanggal'],
					'b.nama'=>'TBS',
				],
				[
					'inv_item b'=>'a.item_id = b.id'
				]
			);
			$val['pengolahan'] = $this->PksProduksiModel->retrieve_result_where(
				'pks_pengolahan_item a',
				[
					// 'mill_id'=>$val['lokasi_id'],
					'b.tanggal'=>$val['tanggal'],
					'c.nama'=>'TBS',
				],
				[
					'pks_pengolahan_ht b'=>'a.pengolahan_id = b.id',
					'inv_item c'=>'a.item_id = c.id',
				]
			);

			$parse_date = explode('-', $val['tanggal'])[2];
			$parse_date = sprintf('%01d', $parse_date);

			$result[$parse_date] = $val;
		}

		// $exp_tanggal = explode($input['tanggal']);
		// for ($i=0; $i<23; $i++) {
		// 	// $data = $parseTanggal[0].'-'.$parseTanggal[1].'-'.$i;
		// 	$xresult[$i] = [];
		// 	$xresult[$i]['tanggal'] = $parseTanggal[0].'-'.$parseTanggal[1].'-'.$i;
		// }


		// $result = array_merge($result, $xresult);
		

		$data['produksi'] = $result;
		
		

		$this->load->view('PksProduksi_harian_m1_laporan', $data);
	}

	function laporan_produksi_bulanan_post()
	{
		error_reporting(0);
		
		$data = [];
		if (isset($this->post()['lokasi_id'])) {
			$input = $this->post();
		}else {
			$input = [
				'lokasi_id' => 256,
				'tgl_mulai' => '2021-12-5',
				'tgl_akhir' => '2022-01-23',
			];
		}
		$input['tgl_mulai'] = $input['tahun_mulai'].'-'.sprintf('%02d',$input['bulan_mulai']).'-01';
		$input['tgl_akhir'] = $input['tahun_akhir'].'-'.sprintf('%02d',$input['bulan_akhir']).'-31';

		$gbm_organisasi = $this->GbmOrganisasiModel->retrieve($input['lokasi_id']);
		$input['lokasi_nama'] = $gbm_organisasi['nama'];

		$data['input'] = $input;

		$data['produksi'] = $this->PksProduksiModel->laporanProduksiBulanan( $input );
		$data['count'] = $this->PksProduksiModel->laporanProduksiBulananCount( $input );

		$data['bulan'] = [ "", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

		$this->load->view('PksProduksi_bulanan_laporan', $data);
	}

}
