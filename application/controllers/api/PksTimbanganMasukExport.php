<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Restserver\Libraries\REST_Controller;

class PksTimbanganMasukExport extends REST_Controller
{
	public $user_id;
	public $theCredential;
	function __construct()
	{
		parent::__construct();
		$this->load->model('PksTimbanganModel');
		$this->load->model('KaryawanModel');
		$this->load->model('M_DatatablesModel');
		$this->load->library('image_lib');
		$this->load->library('upload');
		// $this->auth();
		// $this->theCredential = $this->user_data;
		// $this->user_id=$this->user_data->id;

	}


	function create_post()
	{
		try {
			$arrdata = $this->post();
			//var_dump($data['mill_id']);exit();
			$arrdata['diubah_oleh'] = 1;
			$timbangan = $this->PksTimbanganModel->retrieveByUoid($arrdata['uoid']);
			$mill_id  = (!$arrdata['mill_id'] || $arrdata['mill_id'] == "") ? null : $arrdata['mill_id'];
			$estate_id  = (!isset($arrdata['estate_id']) || empty($arrdata['estate_id']) || !$arrdata['estate_id'] || $arrdata['estate_id'] == "") ? null : $arrdata['estate_id'];
			$rayon_id    = (!isset($arrdata['rayon_id']) || empty($arrdata['rayon_id']) || !$arrdata['rayon_id'] || $arrdata['rayon_id'] == "") ? null : $arrdata['rayon_id'];
			$tipe    =  $arrdata['tipe'];
			$item_id    =  (!$arrdata['item_id'] || $arrdata['item_id'] == "") ? null : $arrdata['item_id'];
			$no_tiket    =  $arrdata['no_tiket'];
			$no_spat    =  $arrdata['no_spat'];
			$tanggal    =  $arrdata['tanggal'];
			$berat_bersih    =  $arrdata['berat_bersih'];
			$berat_kosong    =  $arrdata['berat_kosong'];
			$berat_isi    =  $arrdata['berat_isi'];
			$berat_potongan    =  $arrdata['berat_potongan'];
			$berat_potongan_real    =  $arrdata['berat_potongan_real'];
			$berat_potongan_persen    =  $arrdata['berat_potongan_persen'];
			$berat_terima    =  $arrdata['berat_terima'];
			$jumlah_item    =  $arrdata['jumlah_item'];
			$jumlah_berondolan    =  $arrdata['jumlah_berondolan'];
			$supplier_id    =  (!isset($arrdata['supplier_id']) || !$arrdata['supplier_id'] || $arrdata['supplier_id'] == "" || empty($arrdata['supplier_id']) ) ? null : $arrdata['supplier_id'];
			$transportir_id    =  (empty($arrdata['transportir_id']) || !($arrdata['transportir_id']) || $arrdata['transportir_id'] == "") ? null : $arrdata['transportir_id'];
			$no_plat    =  $arrdata['no_plat'];
			$nama_supir    =  $arrdata['nama_supir'];
			$jam_masuk    =  $arrdata['jam_masuk'];
			$jam_keluar    =  $arrdata['jam_keluar'];
			$uoid    = empty($arrdata['uoid']) ? null : $arrdata['uoid'];
			$diubah_oleh    =  $arrdata['diubah_oleh'];
			$diubah_tanggal    =  date('Y-m-d H:i:s');
			$blok    =  $arrdata['blok'];
			$keterangan    =   $arrdata['keterangan'];

			$jumlah_janjang    =   $arrdata['jumlah_janjang'];
			$grading_mentah    =   $arrdata['grading_mentah'];
			$grading_lewat_matang    =   $arrdata['grading_lewat_matang'];
			$grading_jangkos    =   $arrdata['grading_jangkos'];
			$grading_tangkai_panjang    =   $arrdata['grading_tangkai_panjang'];
			$grading_brondolan    =   $arrdata['grading_brondolan'];
			$grading_air_sampah    =   $arrdata['grading_air_sampah'];
			$grading_buah_partenocerpy    =   $arrdata['grading_buah_partenocerpy'];
			$grading_buah_batu    =   $arrdata['grading_buah_batu'];
			$grading_restan    =   $arrdata['grading_restan'];
			$grading_buah_kecil    =   $arrdata['grading_buah_kecil'];
			$grading_janjang    =   $arrdata['grading_janjang'];


			$data = array(
				'mill_id'    => $mill_id,
				'estate_id' => $estate_id,
				'rayon_id' => $rayon_id,
				'blok' => $blok,
				'keterangan' => $keterangan,
				'tipe' => $tipe,
				'item_id' => $item_id,
				'no_tiket' => $no_tiket,
				'no_spat' => $no_spat,
				'tanggal' => $tanggal,
				'berat_bersih' => $berat_bersih,
				'berat_kosong' => $berat_kosong,
				'berat_isi' => $berat_isi,
				'berat_potongan' => $berat_potongan,
				'berat_potongan_real' => $berat_potongan_real,
				'berat_potongan_persen' => $berat_potongan_persen,
				'berat_terima' => $berat_terima,
				'jumlah_item' => $jumlah_janjang,// item=janjang
				'jumlah_berondolan' => $jumlah_berondolan,
				'supplier_id' => $supplier_id,
				'transportir_id' => $transportir_id,
				'no_plat' => $no_plat,
				'nama_supir' => $nama_supir,
				'jam_masuk' => $jam_masuk,
				'jam_keluar' => $jam_keluar,
				'jumlah_janjang'  => $jumlah_janjang,
				'grading_mentah'  => $grading_mentah,
				'grading_lewat_matang'  =>   $grading_lewat_matang,
				'grading_jangkos'    =>  $grading_jangkos,
				'grading_tangkai_panjang'   =>  $grading_tangkai_panjang,
				'grading_brondolan'    =>  $grading_brondolan,
				'grading_air_sampah'    =>  $grading_air_sampah,
				'grading_buah_partenocerpy'  =>   $grading_buah_partenocerpy,
				'grading_buah_batu'  => $grading_buah_batu,
				'grading_restan'   =>   $grading_restan,
				'grading_buah_kecil'   =>   $grading_buah_kecil,
				'grading_janjang'   =>  $grading_janjang,

				'uoid' => $uoid,
				'diubah_oleh' => $diubah_oleh,
				'diubah_tanggal' => $diubah_tanggal,
			);
			if (empty($timbangan)) {
				//$res=  $this->PksTimbanganModel->create($data);
			
				$this->db->insert('pks_timbangan', $data);
				
			} else {
				//$res=   $this->PksTimbanganModel->update($timbangan['id'], $data);
				$this->db->where('id',$timbangan['id']);
				$this->db->update('pks_timbangan', $data);

			}
			$this->set_response("OK", REST_Controller::HTTP_OK);
		} catch (\Throwable $th) {

			$this->set_response("NOT OK", REST_Controller::HTTP_OK);
		}
	}

	function update_post($segment_3 = '')
	{


		$id = (int)$segment_3;
		$item = $this->PksTimbanganModel->retrieve($id);
		if (empty($item)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}

		$data = $this->post();
		$data['diubah_oleh'] = $this->user_id;
		$retrieve =   $this->PksTimbanganModel->update($item['id'], $data);
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
	}
}
