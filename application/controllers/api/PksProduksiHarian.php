<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

class PksProduksiHarian extends BD_Controller
{
	public $user_id;
	public $theCredential;

	function __construct()
	{
		parent::__construct();
		$this->load->model('PksProduksiHarianModel');
		$this->load->model('M_DatatablesModel');
		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id=$this->user_data->id;
	}

	// endpoint/list :POST
	public function list_post()
	{
		$post = $this->post();
		$query  = "SELECT 
		a.*,
		b.nama AS mill
		FROM pks_produksi_harian a 
		INNER JOIN gbm_organisasi b ON a.mill_id=b.id
		";

		$search = array('no_transaksi');
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
		$retrieve = $this->PksProduksiHarianModel->retrieve($id);
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	//  endpoint/getall :GET
	function getAll_get()
	{
		$retrieve = $this->PksProduksiHarianModel->retrieve_all_kategori();
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	// endpoint/ :POST
	function index_post()
	{
		$input = $this->post();
		$input['dibuat_oleh']=$this->user_id;

		$this->load->library('Autonumber');
		$input['no_transaksi']=$this->autonumber->pks_produksi_harian($input['mill_id']['id'],$input['tanggal'],$input['supplier_id']['id']);

		$retrieve = $this->PksProduksiHarianModel->create($input);
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
	}

	// endpoint/ :PUT
	function index_put($segment_3 = '')
	{
		$id = (int)$segment_3;
		$gudang = $this->PksProduksiHarianModel->retrieve($id);
		if (empty($gudang)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
		$input = $this->put();
		$input['diubah_oleh']=$this->user_id;
		$retrieve = $this->PksProduksiHarianModel->update($gudang['id'], $input);
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
	}

	// endpoint/ :DELETE
	function index_delete($segment_3 = '')
	{
		$id = (int)$segment_3;
		$gudang = $this->PksProduksiHarianModel->retrieve($id);
		if (empty($gudang)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
		$retrieve =  $this->PksProduksiHarianModel->delete($gudang['id']);
		$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
	}
	public function proses_hitung_produksi_post()
	{
		$mill_id = $this->post('mill_id');
		$tgl_awal = $this->post('tgl_awal');
		$tgl_akhir = $this->post('tgl_akhir');


		$tbs_olah = 0;
		$tbs_kemarin = 0;
		$tbs_sisa = 0;
		$tbs_masuk = 0;

		// cari produk id TBS,CPO,KERNEL
		$retProdukTBS = $this->db->query("SELECT * FROM `inv_item` where tipe_produk='TBS'")->row_array();
		$retProdukCPO = $this->db->query("SELECT * FROM `inv_item` where tipe_produk='CPO'")->row_array();
		$retProdukKernel = $this->db->query("SELECT * FROM `inv_item` where tipe_produk='PK'")->row_array();
		$produk_id_tbs = $retProdukTBS['id'];
		$produk_id_cpo = $retProdukCPO['id'];
		$produk_id_kernel = $retProdukKernel['id'];


		// cari tbs olah seblumya utk mencari tbs_sisa/restan
		$retTBSOlahSebelumnya = $this->db->query("SELECT * FROM `pks_produksi_harian` 
		where tanggal <'" . $tgl_awal . "' and mill_id=" . $mill_id . "  
		 order by tanggal DESC limit 1  ")->row_array();
		if (!empty($retTBSOlahSebelumnya)) {

			$tbs_olah = $retTBSOlahSebelumnya['tbs_olah'];
			$tbs_kemarin = $retTBSOlahSebelumnya['tbs_kemarin'];
			$tbs_sisa = $retTBSOlahSebelumnya['tbs_sisa'];
			$tbs_masuk = $retTBSOlahSebelumnya['tbs_masuk'];
		}
		// cari semua produksi harian per periode terpilih 
		$retProduksi = $this->db->query("SELECT * FROM `pks_produksi_harian` 
		where tanggal >='" . $tgl_awal . "' and tanggal <='" . $tgl_akhir . "' and mill_id=" . $mill_id . "  
		 order by tanggal   ")->result_array();


		$no = 0;
		//$tbs_kemarin=$retTBSOlahSebelumnya['tbs_sisa'];
		foreach ($retProduksi as $produksi) {
			$no++;

			$tbs_olah = $produksi['tbs_olah'];
			//$tbs_kemarin = $tbs_sisa;
			//$tbs_sisa=$retTBSOlahSebelumnya['tbs_sisa'];

			// TBS MASUK
			$retTbsTerima = $this->db->query("SELECT sum(berat_terima)as kg FROM `pks_timbangan` 
			where tanggal ='" . $produksi['tanggal'] . "' and mill_id=" . $mill_id . " and item_id=" . $produk_id_tbs . "
		   ")->row_array();
			if (!empty($retTbsTerima)) {
				$tbs_masuk = $retTbsTerima['kg'];
			} else {
				$tbs_masuk = 0;
			}
			$tbs_sisa = $tbs_kemarin + $tbs_masuk - $tbs_olah;
			//$tgl_besok = date("Y-m-d", strtotime("1 day", strtotime($tgl_awal)));

			// CPO KIRIM
			$retCPOKirim = $this->db->query("SELECT sum(netto_kirim)as kg FROM `pks_timbangan_kirim` 
			where tanggal ='" . $produksi['tanggal'] . "' and mill_id=" . $mill_id . " and item_id=" . $produk_id_cpo . "
		   ")->row_array();
			if (!empty($retCPOKirim)) {
				$cpo_kirim = $retCPOKirim['kg'];
			} else {
				$cpo_kirim = 0;
			}
			// cari stok CPO pada sounding tgl terpilih sbg stok awal. 
			$retStokCPOHariIni = $this->db->query("SELECT sum(a.hasil_total) as kg FROM `pks_sounding` a left join pks_tanki b on a.tanki_id=b.id
			where a.tanggal ='" . $produksi['tanggal'] . "' and a.mill_id=" . $mill_id . " and b.produk_id=" . $produk_id_cpo . "
			group by a.tanggal	
		   ")->row_array();
			if (!empty($retStokCPOHariIni)) {
				$cpo_kg_hari_ini = $retStokCPOHariIni['kg'];
			} else {
				$cpo_kg_hari_ini = 0;
			}

			// cari stok CPO pada sounding tgl berikutnya. 
			$retStokCPOBerikutnya = $this->db->query("SELECT a.tanggal, sum(a.hasil_total) as kg FROM `pks_sounding` a left join pks_tanki b on a.tanki_id=b.id
			where a.tanggal >'" . $produksi['tanggal'] . "' and a.mill_id=" . $mill_id . " and b.produk_id=" . $produk_id_cpo . "
			group by a.tanggal
			order by a.tanggal ASC limit 1
		   ")->row_array();
			if (!empty($retStokCPOBerikutnya)) {
				$cpo_kg_berikutnya = $retStokCPOBerikutnya['kg'];
			} else {
				$cpo_kg_berikutnya = 0;
			}

			// Kernel KIRIM
			$retKernelKirim = $this->db->query("SELECT sum(netto_kirim)as kg FROM `pks_timbangan_kirim` 
			where tanggal ='" . $produksi['tanggal'] . "' and mill_id=" . $mill_id . " and item_id=" . $produk_id_kernel . "
		   ")->row_array();
			if (!empty($retKernelKirim)) {
				$kernel_kirim = $retKernelKirim['kg'];
			} else {
				$kernel_kirim = 0;
			}
			// cari stok Kernel pada sounding tgl terpilih sbg stok awal. 
			$retStokKernelHariIni = $this->db->query("SELECT sum(a.hasil_total) as kg FROM `pks_sounding` a left join pks_tanki b on a.tanki_id=b.id
			where a.tanggal ='" . $produksi['tanggal'] . "' and a.mill_id=" . $mill_id . " and b.produk_id=" . $produk_id_kernel . "
			group by a.tanggal
		   ")->row_array();
			if (!empty($retStokKernelHariIni)) {
				$kernel_kg_hari_ini = $retStokKernelHariIni['kg'];
			} else {
				$kernel_kg_hari_ini = 0;
			}
			// cari stok Kernel pada sounding tgl berikutnya. 
			$retStokKernelBerikutnya = $this->db->query("SELECT a.tanggal,sum(a.hasil_total) as kg FROM `pks_sounding` a left join pks_tanki b on a.tanki_id=b.id
			where a.tanggal >'" . $produksi['tanggal'] . "' and a.mill_id=" . $mill_id . " and b.produk_id=" . $produk_id_kernel . "
			group by a.tanggal
			order by a.tanggal  ASC limit 1
		   ")->row_array();

			if (!empty($retStokKernelBerikutnya)) {
				$kernel_kg_berikutnya = $retStokKernelBerikutnya['kg'];
			} else {
				$kernel_kg_berikutnya = 0;
			}
			$id = (int)$produksi['id'];


			$data = array(
				'tbs_kemarin'    => $tbs_kemarin,
				'tbs_sisa' => $tbs_sisa,
				'tbs_masuk' => $tbs_masuk,
				'cpo_kg' => $cpo_kg_berikutnya -  $cpo_kg_hari_ini + $cpo_kirim,
				'cpo_kg_awal' => $cpo_kg_hari_ini,
				'cpo_kg_akhir' => $cpo_kg_berikutnya,
				'cpo_kg_kirim' => $cpo_kirim,
				'kernel_kg' => $kernel_kg_berikutnya -  $kernel_kg_hari_ini + $kernel_kirim,
				'kernel_kg_awal' =>  $kernel_kg_hari_ini,
				'kernel_kg_akhir' => $kernel_kg_berikutnya,
				'kernel_kg_kirim' => $kernel_kirim
			);
			$this->db->where('id', $id);
			$this->db->update('pks_produksi_harian', $data);

			$tbs_kemarin = $tbs_sisa;
		}


		if (count($retProduksi) > 0) {

			$this->set_response(array("status" => "OK", "data" => 'Proses berhasil.' . count($retProduksi) . " data diproses"), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data yang diproses"), REST_Controller::HTTP_OK);
		}
	}
	public function proses_hitung_produksi_perhari_post()
	{
		$mill_id = $this->post('mill_id');
		$tgl = $this->post('tanggal');

		$tbs_olah_hari_ini = 0;
		$tbs_sisa_kemarin = 0;
		$tbs_sisa = 0;
		$tbs_masuk = 0;

		// cari produk id TBS,CPO,KERNEL
		$retProdukTBS = $this->db->query("SELECT * FROM `inv_item` where tipe_produk='TBS'")->row_array();
		$retProdukCPO = $this->db->query("SELECT * FROM `inv_item` where tipe_produk='CPO'")->row_array();
		$retProdukKernel = $this->db->query("SELECT * FROM `inv_item` where tipe_produk='PK'")->row_array();
		$produk_id_tbs = $retProdukTBS['id'];
		$produk_id_cpo = $retProdukCPO['id'];
		$produk_id_kernel = $retProdukKernel['id'];

		$tgl_kemarin = date( 'Y-m-d', strtotime( $tgl . ' -1 day' ) );

		// cari tbs olah seblumya utk mencari tbs_sisa/restan
		$retTBSOlahSebelumnya = $this->db->query("SELECT * FROM `pks_produksi_harian` 
		where tanggal ='" . $tgl_kemarin . "' and mill_id=" . $mill_id . " ")->row_array();
		if (!empty($retTBSOlahSebelumnya)) {

			$tbs_sisa_kemarin = $retTBSOlahSebelumnya['tbs_sisa'];

		}
		// cari produksi harian per periode terpilih 
		$ret_tbs_olah = $this->db->query("SELECT * FROM `pks_pengolahan_ht` 
		where tanggal ='" . $tgl . "' and mill_id=" . $mill_id . "  
		 order by tanggal   ")->row_array();
		$tbs_olah_hari_ini = $ret_tbs_olah['tbs_olah'];


		// TBS MASUK
		$retTbsTerima = $this->db->query("SELECT sum(berat_bersih)as kg FROM `pks_timbangan` 
			where tanggal ='" . $tgl . "' and mill_id=" . $mill_id . " and item_id=" . $produk_id_tbs . "
		   ")->row_array();
		if (!empty($retTbsTerima)) {
			$tbs_masuk = $retTbsTerima['kg'];
		} else {
			$tbs_masuk = 0;
		}
		$tbs_sisa = $tbs_sisa_kemarin + $tbs_masuk - $tbs_olah_hari_ini;
		//$tgl_besok = date("Y-m-d", strtotime("1 day", strtotime($tgl_awal)));

		// CPO KIRIM
		$retCPOKirim = $this->db->query("SELECT sum(netto_kirim)as kg FROM `pks_timbangan_kirim` 
			where tanggal ='" . $tgl . "' and mill_id=" . $mill_id . " and item_id=" . $produk_id_cpo . "
		   ")->row_array();
		if (($retCPOKirim)) {
			$cpo_kirim = $retCPOKirim['kg']?$retCPOKirim['kg']:0;
		} else {
			$cpo_kirim = 0;
		}
		// cari stok CPO pada sounding kemarin sbg stok awal. 
		$retStokCPOKemarin = $this->db->query("SELECT sum(a.hasil_total) as kg FROM `pks_sounding` a left join pks_tanki b on a.tanki_id=b.id
			where a.tanggal ='" . $tgl_kemarin . "' and a.mill_id=" . $mill_id . " and b.produk_id=" . $produk_id_cpo . "
			group by a.tanggal	
		   ")->row_array();
		if (($retStokCPOKemarin)) {
			$cpo_kg_kemarin = $retStokCPOKemarin['kg'];
		} else {
			$cpo_kg_kemarin = 0;
		}

		// cari stok CPO pada sounding tgl berikutnya. 
		$retStokCPOHariIni = $this->db->query("SELECT a.tanggal, sum(a.hasil_total) as kg FROM `pks_sounding` a left join pks_tanki b on a.tanki_id=b.id
			where a.tanggal ='" . $tgl . "' and a.mill_id=" . $mill_id . " and b.produk_id=" . $produk_id_cpo . "
			group by a.tanggal
			order by a.tanggal ASC limit 1
		   ")->row_array();
		if (!empty($retStokCPOHariIni)) {
			$cpo_kg_hari_ini = $retStokCPOHariIni['kg'];
		} else {
			$cpo_kg_hari_ini = 0;
		}

		// Kernel KIRIM
		$retKernelKirim = $this->db->query("SELECT sum(netto_kirim)as kg FROM `pks_timbangan_kirim` 
			where tanggal ='" . $tgl . "' and mill_id=" . $mill_id . " and item_id=" . $produk_id_kernel . "
		   ")->row_array();
		if (!empty($retKernelKirim)) {
			$kernel_kirim = $retKernelKirim['kg']?$retKernelKirim['kg']:0;
		} else {
			$kernel_kirim = 0;
		}
		// cari stok Kernel pada sounding kemarin sbg stok awal. 
		$retStokKernelKemarin = $this->db->query("SELECT sum(a.hasil_sounding) as kg FROM `pks_sounding_kernel` a left join pks_tanki b on a.tanki_id=b.id
			where a.tanggal ='" . $tgl_kemarin . "' and a.mill_id=" . $mill_id . " and b.produk_id=" . $produk_id_kernel . "
			group by a.tanggal
		   ")->row_array();
		if (!empty($retStokKernelKemarin)) {
			$kernel_kg_kemarin = $retStokKernelKemarin['kg'];
		} else {
			$kernel_kg_kemarin = 0;
		}
		// cari stok Kernel pada sounding tgl HARI INI. 
		$retStokKernelHariIni = $this->db->query("SELECT a.tanggal,sum(a.hasil_sounding) as kg FROM `pks_sounding_kernel` a left join pks_tanki b on a.tanki_id=b.id
			where a.tanggal ='" . $tgl . "' and a.mill_id=" . $mill_id . " and b.produk_id=" . $produk_id_kernel . "
			group by a.tanggal
		   ")->row_array();

		if (!empty($retStokKernelHariIni)) {
			$kernel_kg_hari_ini = $retStokKernelHariIni['kg'];
		} else {
			$kernel_kg_hari_ini = 0;
		}
		$data = array(
		
			'mill_id'=>$mill_id,
			'tanggal'=>$tgl,
			'tbs_olah_hari_ini'=>$tbs_olah_hari_ini,
			'tbs_sisa_kemarin'    => $tbs_sisa_kemarin,
			'tbs_sisa' => $tbs_sisa,
			'tbs_masuk' => $tbs_masuk,
			'cpo_kg_olah' => $cpo_kg_hari_ini -  $cpo_kg_kemarin + $cpo_kirim,
			'cpo_kg_awal' => $cpo_kg_kemarin,
			'cpo_kg_akhir' => $cpo_kg_hari_ini,
			'cpo_kg_kirim' => $cpo_kirim,
			'kernel_kg_olah' => $kernel_kg_hari_ini -  $kernel_kg_kemarin + $kernel_kirim,
			'kernel_kg_awal' =>  $kernel_kg_kemarin,
			'kernel_kg_akhir' => $kernel_kg_hari_ini,
			'kernel_kg_kirim' => $kernel_kirim
		);

		$this->set_response(array("status" => "OK", "data" => 	$data), REST_Controller::HTTP_OK);
	}
}
