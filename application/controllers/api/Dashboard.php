<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Floor;
use Restserver\Libraries\REST_Controller;

class Dashboard extends Rest_Controller
{
	function __construct()
	{
		parent::__construct();

		$this->load->model('M_DatatablesModel');
	}

	public function list_post()
	{
		$post = $this->post();

		$query  = "SELECT * from dashboard_setting ";
		$search = array('kode', 'nama');
		$where  = null;

		$isWhere = null;
		// $isWhere = 'artikel.deleted_at IS NULL';

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	public function getDashboardSetting_get()
	{

		$res = $this->db->query('SELECT * from dashboard_setting')->result_array();
		$this->set_response($res, REST_Controller::HTTP_OK);
	}
	public function updateDashboardSetting_post()
	{
		$id = $this->post('id');
		$is_show = $this->post('is_show');
		$this->db->where('id', $id);
		$this->db->update('dashboard_setting', array(
			'is_show' => $is_show,
		));
	}
	public function tagihan_customer_get()
	{
		
		$retrieve = $this->db->query("SELECT c.nama_customer,no_so,tanggal , grand_total as nilai_so,
		IFNULL (b.bayar,0)AS bayar,grand_total-IFNULL(b.bayar,0)as sisa FROM sls_so_ht a
				inner join gbm_customer c ON a.customer_id=c.id  LEFT JOIN (SELECT so_hd_id, SUM(nilai)AS bayar FROM sls_so_pembayaran
				GROUP BY so_hd_id)b ON a.id=b.so_hd_id
				WHERE  grand_total-IFNULL(b.bayar,0)>0
				")->result_array();
		$this->set_response($retrieve, REST_Controller::HTTP_OK);
	}
	public function total_sales_get($param)
	{
		$t = date("Y-m");
		if ($param) {
			if ($param == 'bulan_ini') {
				$t = date("Y-m");
			} else if ($param == 'bulan_lalu') {
				$t = date('Y-m', strtotime("first day of -1 month"));
			} else {
				$t = $param;
				$t1 = $param . "-01";
				$t2 =  $param . "-01";;
				$d1 = new DateTime($t1);
				$tanggal1 = new DateTime($t1);
				$d2 = new DateTime($t2);
			}
		}
		
		$retrieve = $this->db->query("SELECT sum(grand_total)as tot
		FROM sls_so_ht 
		where DATE_FORMAT(tanggal,'%Y-%m') = '" . $t . "'")->row_array();
		$total_nilai_so = $retrieve['tot'];

		$retrieve = $this->db->query("SELECT SUM(nilai)AS tot FROM sls_so_pembayaran a inner JOIN sls_so_ht b 
			ON a.so_hd_id=b.id
		where DATE_FORMAT(a.tanggal,'%Y-%m') = '" . $t . "'")->row_array();
		$total_nilai_bayar = $retrieve['tot'];


		$data = array('nilai_so' => $total_nilai_so, 'nilai_bayar' => $total_nilai_bayar);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	public function sales_perbulan_get($param)
	{

		$t = date("Y-m");
		if ($param) {
			if ($param == 'bulan_ini') {
				$t = date("Y-m");
				$d1 = new DateTime();
				$tanggal1 = new DateTime();
				$d2 = new DateTime();
				$tanggal1 = $d1;
			} else if ($param == 'bulan_lalu') {
				$t = date('Y-m', strtotime("first day of -1 month"));
				$t1 = date('Y-m-d', strtotime("first day of -1 month"));
				$t2 = date('Y-m-d', strtotime("first day of -1 month"));
				$d1 = new DateTime($t1);
				$tanggal1 = new DateTime($t1);
				$d2 = new DateTime($t2);
			} else {
				$t = $param;
				$t1 = $param . "-01";
				$t2 =  $param . "-01";;
				$d1 = new DateTime($t1);
				$tanggal1 = new DateTime($t1);
				$d2 = new DateTime($t2);
			}
		}


		$retrieve = $this->db->query("SELECT c.nama_customer as name, grand_total as value FROM sls_so_ht a
				inner join gbm_customer c ON a.customer_id=c.id  
		  WHERE  DATE_FORMAT(a.tanggal ,'%Y-%m') ='" . $t . "'
		  GROUP BY c.nama_customer
		  ")->result_array();

		$this->set_response($retrieve, REST_Controller::HTTP_OK);
	}
	public function pks_stok_tangki_get()
	{

		$retrieve_tangki_cpo1 = $this->db->query('select a.tanki_id,c.kode_tanki,c.nama_tanki,c.kapasitas, (hasil_total/1000) as stok,a.tanggal from pks_sounding a INNER join  (
			SELECT  tanki_id,max(tanggal)as tanggal   FROM `pks_sounding`
			group by tanki_id)b on a.tanki_id=b.tanki_id and a.tanggal=b.tanggal
			inner join pks_tanki c on a.tanki_id=c.id
		where a.tanki_id=18 ')->row_array();
		$retrieve_tangki_cpo2 = $this->db->query('select a.tanki_id,c.kode_tanki,c.nama_tanki,c.kapasitas, (hasil_total/1000) as stok,a.tanggal from pks_sounding a INNER join  (
			SELECT  tanki_id,max(tanggal)as tanggal   FROM `pks_sounding`
			group by tanki_id)b on a.tanki_id=b.tanki_id and a.tanggal=b.tanggal
			inner join pks_tanki c on a.tanki_id=c.id
		where a.tanki_id=19 ')->row_array();

		$retrieve_tangki_cpo3 = $this->db->query('select a.tanki_id,c.kode_tanki,c.nama_tanki,c.kapasitas, (hasil_total/1000) as stok,a.tanggal from pks_sounding a INNER join  (
			SELECT  tanki_id,max(tanggal)as tanggal   FROM `pks_sounding`
			group by tanki_id)b on a.tanki_id=b.tanki_id and a.tanggal=b.tanggal
			inner join pks_tanki c on a.tanki_id=c.id
		where a.tanki_id=22 ')->row_array();
		$retrieve_tangki_kernel1 = $this->db->query('select a.tanki_id,c.kode_tanki,c.nama_tanki,c.kapasitas, (hasil_sounding/1000) as stok,a.tanggal
		 from pks_sounding_kernel a INNER join 
		( SELECT tanki_id,max(tanggal)as tanggal FROM `pks_sounding_kernel` group by tanki_id)b
		 on a.tanki_id=b.tanki_id and a.tanggal=b.tanggal inner join pks_tanki c on a.tanki_id=c.id
		where a.tanki_id=23 ')->row_array();
		// $data["tangki_cpo_1"]=$retrieve_tangki_cpo1;
		// $data["tangki_cpo_2"]=$retrieve_tangki_cpo2;
		// $data["tangki_cpo_3"]=$retrieve_tangki_cpo3;
		// $data["tangki_kernel_1"]=$retrieve_tangki_kernel1;
		$data[] = $retrieve_tangki_cpo1;
		$data[] = $retrieve_tangki_cpo2;
		$data[] = $retrieve_tangki_cpo3;
		$data[] = $retrieve_tangki_kernel1;
		$this->set_response($data, REST_Controller::HTTP_OK);;
	}
	public function pks_pemerimaan_tbs_harian_get($param)
	{
		$t = date("Y-m");
		if ($param) {
			if ($param == 'bulan_ini') {
				$t = date("Y-m");
			} else if ($param == 'bulan_lalu') {
				$t = date('Y-m', strtotime("first day of -1 month"));
			} else {
				$t = $param;
				$t1 = $param . "-01";
				$t2 =  $param . "-01";;
				$d1 = new DateTime($t1);
				$tanggal1 = new DateTime($t1);
				$d2 = new DateTime($t2);
			}
		}
		$retrieve = $this->db->query("SELECT SUM(berat_bersih)as beratkg,DATE_FORMAT(tanggal,'%d')as tgl 
		FROM pks_timbangan_terima_tbs_vw
		where DATE_FORMAT(tanggal,'%Y-%m') = '" . $t . "'
		and nama_produk='TBS' group by tanggal 
				")->result_array();
		// $retrieve = $this->db->query("SELECT SUM(berat_bersih)as beratkg,DATE_FORMAT(tanggal,'%d')as tgl
		// FROM pks_timbangan_terima_tbs_vw
		// where DATE_FORMAT(tanggal,'%Y-%m') = '2022-01'  group by tanggal 
		// 		")->result_array();
		$data = $retrieve;
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	public function pks_pemerimaan_tbs_harian_by_supp_get($param)
	{
		$t = date("Y-m");
		if ($param) {
			if ($param == 'bulan_ini') {
				$t = date("Y-m");
				$d1 = new DateTime();
				$d2 = new DateTime();
				$tanggal1 = $d1;
			} else if ($param == 'bulan_lalu') {
				$t = date('Y-m', strtotime("first day of -1 month"));
			} else {
				$t = $param;
				$t1 = $param . "-01";
				$t2 =  $param . "-01";;
				$d1 = new DateTime($t1);
				$tanggal1 = new DateTime($t1);
				$d2 = new DateTime($t2);
			}
		}


		$res = array();
		$d2->modify('last day of this month');
		$interval = $d1->diff($d2);
		$jumlah_hari = $interval->days;
		$retrieve = $this->db->query("SELECT IF(ISNULL(kode_estate), 'PH3',kode_estate ) kode, SUM(berat_bersih)as beratkg,DATE_FORMAT(tanggal,'%d')as tanggal 
		FROM pks_timbangan_terima_tbs_vw
		where DATE_FORMAT(tanggal,'%Y-%m') = '" . $t . "'
		and nama_produk='TBS' group by tanggal ,kode_estate
				")->result_array();

		$series1 = [];
		$array_res1 = array();
		$array_res1 = [];
		$series2 = [];
		$array_res2 = array();
		$array_res2 = [];
		$series3 = [];
		$array_res3 = array();
		$array_res3 = [];
		foreach ($retrieve as $key => $value) {
			if ($value['kode'] == 'SBNE') {
				$array_res1[$value['tanggal']] = $value['beratkg'];
			} elseif ($value['kode'] == 'SBME') {
				$array_res2[$value['tanggal']] = $value['beratkg'];
			} elseif ($value['kode'] == 'PH3') {
				$array_res3[$value['tanggal']] = $value['beratkg'];
			}
		}

		$d1->modify('first day of this month');
		while ($d1 <= $d2) {
			$dd = $d1->format('d');
			if (array_key_exists($dd, $array_res1)) {
				$data1 = array("name" => $dd, "value" => $array_res1[$dd]);
			} else {
				$data1 = array("name" => $dd, "value" => 0);
			}
			if (array_key_exists($dd, $array_res2)) {
				$data2 = array("name" => $dd, "value" => $array_res2[$dd]);
			} else {
				$data2 = array("name" => $dd, "value" => 0);
			}
			if (array_key_exists($dd, $array_res3)) {
				$data3 = array("name" => $dd, "value" => $array_res3[$dd]);
			} else {
				$data3 = array("name" => $dd, "value" => 0);
			}
			$series1[] = $data1;
			$series2[] = $data2;
			$series3[] = $data3;
			$d1->modify('+1 day');
		}
		$res = array();

		$res[] = array("name" => 'SBNE', "series" => $series1);
		$res[] = array("name" => 'SBME', "series" => $series2);
		$res[] = array("name" => 'PH3', "series" => $series3);


		$data = $res;
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	public function kln_pasien_rawat_get($param)
	{
		$t = date("Y-m");
		if ($param) {
			if ($param == 'bulan_ini') {
				$t = date("Y-m");
				$d1 = new DateTime();
				$d2 = new DateTime();
				$tanggal1 = $d1;
			} else if ($param == 'bulan_lalu') {
				$t = date('Y-m', strtotime("first day of -1 month"));
			} else {
				$t = $param;
				$t1 = $param . "-01";
				$t2 =  $param . "-01";;
				$d1 = new DateTime($t1);
				$tanggal1 = new DateTime($t1);
				$d2 = new DateTime($t2);
			}
		}


		$res = array();
		$d2->modify('last day of this month');
		$interval = $d1->diff($d2);
		$jumlah_hari = $interval->days;
		$retrieve1 = $this->db->query("SELECT COUNT(id)AS jumlah,DATE_FORMAT(tanggal,'%d')as tanggal  FROM kln_rawat_jalan
		where DATE_FORMAT(tanggal,'%Y-%m') = '" . $t . "'
		group by tanggal")->result_array();


		$retrieve2 = $this->db->query("SELECT COUNT(id)AS jumlah,DATE_FORMAT(tanggal,'%d')as tanggal  FROM kln_rawat_inap
		where DATE_FORMAT(tanggal,'%Y-%m') = '" . $t . "'
		group by tanggal")->result_array();

		$series1 = [];
		$array_res1 = array();
		$array_res1 = [];
		$series2 = [];
		$array_res2 = array();
		$array_res2 = [];
		$series3 = [];
		$array_res3 = array();
		$array_res3 = [];
		foreach ($retrieve1 as $key => $value) {

			$array_res1[$value['tanggal']] = $value['jumlah'];
		}
		foreach ($retrieve2 as $key => $value) {

			$array_res2[$value['tanggal']] = $value['jumlah'];
		}

		$d1->modify('first day of this month');
		while ($d1 <= $d2) {
			$dd = $d1->format('d');
			if (array_key_exists($dd, $array_res1)) {
				$data1 = array("name" => $dd, "value" => $array_res1[$dd]);
			} else {
				$data1 = array("name" => $dd, "value" => 0);
			}
			if (array_key_exists($dd, $array_res2)) {
				$data2 = array("name" => $dd, "value" => $array_res2[$dd]);
			} else {
				$data2 = array("name" => $dd, "value" => 0);
			}

			$series1[] = $data1;
			$series2[] = $data2;

			$d1->modify('+1 day');
		}
		$res = array();

		$res[] = array("name" => 'RAWAT_JALAN', "series" => $series1);
		$res[] = array("name" => 'RAWAT_INAP', "series" => $series2);

		$data = $res;
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	public function pks_pengiriman_cpo_harian_get($param)
	{
		$t = date("Y-m");
		if ($param) {
			if ($param == 'bulan_ini') {
				$t = date("Y-m");
			} else if ($param == 'bulan_lalu') {
				$t = date('Y-m', strtotime("first day of -1 month"));
			} else {
				$t = $param;
				$t1 = $param . "-01";
				$t2 =  $param . "-01";;
				$d1 = new DateTime($t1);
				$tanggal1 = new DateTime($t1);
				$d2 = new DateTime($t2);
			}
		}
		$retrieve = $this->db->query("SELECT SUM(netto_kirim)as beratkg,DATE_FORMAT(tanggal,'%d')as tgl 
		FROM  pks_timbangan_kirim_vw 
		where DATE_FORMAT(tanggal,'%Y-%m') = '" . $t . "'
		and nama_produk='CPO' group by tanggal 
				")->result_array();
		// $retrieve = $this->db->query("SELECT SUM(berat_bersih)as beratkg,DATE_FORMAT(tanggal,'%d')as tgl
		// FROM pks_timbangan_terima_tbs_vw
		// where DATE_FORMAT(tanggal,'%Y-%m') = '2022-01'  group by tanggal 
		// 		")->result_array();
		$data = $retrieve;
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	public function kln_jumlah_pasien_dokter_perawat_get()
	{
		$retrieve = $this->db->query("SELECT count(*)as jum
		FROM karyawan 
		where ( tgl_keluar IS NULL or tgl_keluar='0000-00-00')
		and is_dokter=1	")->row_array();
		$jumlah_dokter = $retrieve['jum'];

		$retrieve = $this->db->query("SELECT count(*)as jum
		FROM karyawan 
		where ( tgl_keluar IS NULL or tgl_keluar='0000-00-00')
		and is_perawat=1	")->row_array();
		$jumlah_perawat = $retrieve['jum'];

		$retrieve = $this->db->query("SELECT count(*)as jum
		FROM kln_pasien")->row_array();
		$jumlah_pasien = $retrieve['jum'];

		$data = array('jumlah_dokter' => $jumlah_dokter, 'jumlah_perawat' => $jumlah_perawat, 'jumlah_pasien' => $jumlah_pasien);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}

	public function hrms_jumlah_karyawan_get()
	{
		$retrieve = $this->db->query("SELECT count(*)as value,b.kode as name 
		FROM karyawan a inner join gbm_organisasi b on a.lokasi_tugas_id=b.id 
		where ( a.tgl_keluar IS NULL or a.tgl_keluar='0000-00-00')
		GROUP by b.kode 
				")->result_array();
		$data = $retrieve;
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	public function estate_panen_afdeling_harian_get($param)
	{
		//$t = '2022-09';
		$t = date("Y-m");
		if ($param) {
			if ($param == 'bulan_ini') {
				$t = date("Y-m");
				$d1 = new DateTime();
				$tanggal1 = new DateTime();
				$d2 = new DateTime();
				$tanggal1 = $d1;
			} else if ($param == 'bulan_lalu') {
				$t = date('Y-m', strtotime("first day of -1 month"));
				$t1 = date('Y-m-d', strtotime("first day of -1 month"));
				$t2 = date('Y-m-d', strtotime("first day of -1 month"));
				$d1 = new DateTime($t1);
				$tanggal1 = new DateTime($t1);
				$d2 = new DateTime($t2);
			} else {
				$t = $param;
				$t1 = $param . "-01";
				$t2 =  $param . "-01";;
				$d1 = new DateTime($t1);
				$tanggal1 = new DateTime($t1);
				$d2 = new DateTime($t2);
			}
		}


		$retrieveNamaAfd = $this->db->query("SELECT d.id,d.nama as nama_afdeling
		FROM est_bkm_panen_ht a INNER JOIN est_bkm_panen_dt b
		ON a.id=b.bkm_panen_id INNER JOIN gbm_organisasi c ON b.blok_id=c.id
		INNER JOIN gbm_organisasi d ON c.parent_id=d.id
		WHERE DATE_FORMAT(tanggal,'%Y-%m') ='" . $t . "'
		GROUP BY d.nama,d.id
		ORDER BY d.nama
		")->result_array();
		// $d1 = new DateTime();
		// $d2 = new DateTime();
		$res = array();

		$d2->modify('last day of this month');
		$interval = $d1->diff($d2);
		$jumlah_hari = $interval->days;
		foreach ($retrieveNamaAfd as $key => $nama_afd) {
			$retrieve = $this->db->query("SELECT d.id,d.kode,d.nama as nama_afdeling ,DATE_FORMAT(tanggal,'%d')as tanggal,SUM(b.hasil_kerja_jjg)AS jjg,SUM(b.hasil_kerja_kg)as kg,SUM(b.hasil_kerja_brondolan)AS brondolan  FROM est_bkm_panen_ht a INNER JOIN est_bkm_panen_dt b
			ON a.id=b.bkm_panen_id INNER JOIN gbm_organisasi c ON b.blok_id=c.id
			INNER JOIN gbm_organisasi d ON c.parent_id=d.id
			WHERE DATE_FORMAT(tanggal,'%Y-%m') ='" . $t . "'
			and d.id=" . $nama_afd['id'] . "
			GROUP BY d.id,d.kode,d.nama,DATE_FORMAT(tanggal,'%d')
			ORDER BY DATE_FORMAT(tanggal,'%d')
			")->result_array();
			$name =  $nama_afd['nama_afdeling'];
			$series = [];
			$array_res = array();
			$array_res = [];
			foreach ($retrieve as $key => $value) {
				$array_res[$value['tanggal']] = $value['jjg'];
			}
			// $d1 = $tanggal1;

			if ($param) {
				if ($param == 'bulan_ini') {
					$t1 = date('Y-m-d');
				} else if ($param == 'bulan_lalu') {

					$t1 = date('Y-m-d', strtotime("first day of -1 month"));
				} else {
					$t1 = $param . "-01";
				}
			}
			$d1 = new DateTime($t1);
			$d1->modify('first day of this month');
			while ($d1 <= $d2) {
				$dd = $d1->format('d');
				if (array_key_exists($dd, $array_res)) {
					$data = array("name" => $dd, "value" => $array_res[$dd]);
				} else {
					$data = array("name" => $dd, "value" => 0);
				}
				$series[] = $data;
				$d1->modify('+1 day');
			}

			// foreach ($retrieve as $key => $value) {


			// 	$data= array("name"=> $value['tanggal'],"value"=> $value['jjg']);
			// 	$series[]=$data;
			// }
			$res[] = array("name" => $name, "series" => $series, "tgl1" => $tanggal1);
		}

		$data = $res;
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	public function estate_panen_perbulan_get($param)
	{

		$t = date("Y-m");
		if ($param) {
			if ($param == 'bulan_ini') {
				$t = date("Y-m");
				$d1 = new DateTime();
				$tanggal1 = new DateTime();
				$d2 = new DateTime();
				$tanggal1 = $d1;
			} else if ($param == 'bulan_lalu') {
				$t = date('Y-m', strtotime("first day of -1 month"));
				$t1 = date('Y-m-d', strtotime("first day of -1 month"));
				$t2 = date('Y-m-d', strtotime("first day of -1 month"));
				$d1 = new DateTime($t1);
				$tanggal1 = new DateTime($t1);
				$d2 = new DateTime($t2);
			} else {
				$t = $param;
				$t1 = $param . "-01";
				$t2 =  $param . "-01";;
				$d1 = new DateTime($t1);
				$tanggal1 = new DateTime($t1);
				$d2 = new DateTime($t2);
			}
		}

		$res = array();
		$retrieve = $this->db->query("SELECT d.nama as name ,SUM(b.hasil_kerja_kg)AS value FROM est_bkm_panen_ht a INNER JOIN est_bkm_panen_dt b
		  ON a.id=b.bkm_panen_id INNER JOIN gbm_organisasi c ON b.blok_id=c.id
		  INNER JOIN gbm_organisasi d ON c.parent_id=d.id
		  WHERE DATE_FORMAT(tanggal,'%Y-%m') ='" . $t . "'
		  GROUP BY d.nama
		  ORDER BY  d.nama
		  ")->result_array();
		foreach ($retrieve as $key => $value) {
			$name = substr($value['name'], 9, (strlen($value['name']) - 9));
			$val = ($value['value']) / 1000;
			$res[] = array('name' => $name, 'value' => round($val, 1));
		}

		$this->set_response($res, REST_Controller::HTTP_OK);
	}
	public function estate_hk_panen_perbulan_get($param)
	{
		$t = date("Y-m");
		if ($param) {
			if ($param == 'bulan_ini') {
				$t = date("Y-m");
				$d1 = new DateTime();
				$tanggal1 = new DateTime();
				$d2 = new DateTime();
				$tanggal1 = $d1;
			} else if ($param == 'bulan_lalu') {
				$t = date('Y-m', strtotime("first day of -1 month"));
				$t1 = date('Y-m-d', strtotime("first day of -1 month"));
				$t2 = date('Y-m-d', strtotime("first day of -1 month"));
				$d1 = new DateTime($t1);
				$tanggal1 = new DateTime($t1);
				$d2 = new DateTime($t2);
			} else {
				$t = $param;
				$t1 = $param . "-01";
				$t2 =  $param . "-01";;
				$d1 = new DateTime($t1);
				$tanggal1 = new DateTime($t1);
				$d2 = new DateTime($t2);
			}
		}


		$retrieve = $this->db->query("SELECT d.nama as name ,SUM(CAST(b.jumlah_hk AS DECIMAL(10,1)))AS value FROM est_bkm_panen_ht a INNER JOIN est_bkm_panen_dt b
		  ON a.id=b.bkm_panen_id INNER JOIN gbm_organisasi c ON b.blok_id=c.id
		  INNER JOIN gbm_organisasi d ON c.parent_id=d.id
		  WHERE DATE_FORMAT(tanggal,'%Y-%m') ='" . $t . "'
		  GROUP BY d.nama
		  ORDER BY  d.nama
		  ")->result_array();

		$this->set_response($retrieve, REST_Controller::HTTP_OK);
	}
	public function estate_hk_pemeliharaan_perbulan_get($param)
	{
		$t = date("Y-m");
		if ($param) {
			if ($param == 'bulan_ini') {
				$t = date("Y-m");
				$d1 = new DateTime();
				$tanggal1 = new DateTime();
				$d2 = new DateTime();
				$tanggal1 = $d1;
			} else if ($param == 'bulan_lalu') {
				$t = date('Y-m', strtotime("first day of -1 month"));
				$t1 = date('Y-m-d', strtotime("first day of -1 month"));
				$t2 = date('Y-m-d', strtotime("first day of -1 month"));
				$d1 = new DateTime($t1);
				$tanggal1 = new DateTime($t1);
				$d2 = new DateTime($t2);
			} else {
				$t = $param;
				$t1 = $param . "-01";
				$t2 =  $param . "-01";;
				$d1 = new DateTime($t1);
				$tanggal1 = new DateTime($t1);
				$d2 = new DateTime($t2);
			}
		}

		$retrieve = $this->db->query("SELECT d.nama as name ,SUM(CAST(b.jumlah_hk AS DECIMAL(10,1)))AS value FROM est_bkm_pemeliharaan_ht a INNER JOIN est_bkm_pemeliharaan_dt b
		  ON a.id=b.bkm_pemeliharaan_id INNER JOIN gbm_organisasi c ON b.blok_id=c.id
		  INNER JOIN gbm_organisasi d ON c.parent_id=d.id
		  WHERE DATE_FORMAT(tanggal,'%Y-%m') ='" . $t . "'
		  GROUP BY d.nama
		  ORDER BY  d.nama
		  ")->result_array();

		$this->set_response($retrieve, REST_Controller::HTTP_OK);
	}
	public function estate_hk_all_afdeling_perbulan_get($param)
	{
		$t = date("Y-m");
		if ($param) {
			if ($param == 'bulan_ini') {
				$t = date("Y-m");
				$d1 = new DateTime();
				$tanggal1 = new DateTime();
				$d2 = new DateTime();
				$tanggal1 = $d1;
			} else if ($param == 'bulan_lalu') {
				$t = date('Y-m', strtotime("first day of -1 month"));
				$t1 = date('Y-m-d', strtotime("first day of -1 month"));
				$t2 = date('Y-m-d', strtotime("first day of -1 month"));
				$d1 = new DateTime($t1);
				$tanggal1 = new DateTime($t1);
				$d2 = new DateTime($t2);
			} else {
				$t = $param;
				$t1 = $param . "-01";
				$t2 =  $param . "-01";;
				$d1 = new DateTime($t1);
				$tanggal1 = new DateTime($t1);
				$d2 = new DateTime($t2);
			}
		}
		$res = array();
		$retrieveAfdeling = $this->db->query("SELECT * from gbm_organisasi where tipe='AFDELING'
		and nama like 'AFDELING%'")->result_array();
		foreach ($retrieveAfdeling  as $key => $afd) {
			# code...

			$retrievePanen = $this->db->query("SELECT d.nama as name ,SUM(CAST(b.jumlah_hk AS DECIMAL(10,1)))AS value FROM est_bkm_panen_ht a INNER JOIN est_bkm_panen_dt b
		ON a.id=b.bkm_panen_id INNER JOIN gbm_organisasi c ON b.blok_id=c.id
		INNER JOIN gbm_organisasi d ON c.parent_id=d.id
		WHERE DATE_FORMAT(tanggal,'%Y-%m') ='" . $t . "'
		and d.id=" . $afd['id'] . "
		GROUP BY d.nama
		ORDER BY  d.nama
		")->row_array();
			$retrievePemeliharaan = $this->db->query("SELECT d.nama as name ,SUM(CAST(b.jumlah_hk AS DECIMAL(10,1)))AS value FROM est_bkm_pemeliharaan_ht a INNER JOIN est_bkm_pemeliharaan_dt b
		  ON a.id=b.bkm_pemeliharaan_id INNER JOIN gbm_organisasi c ON b.blok_id=c.id
		  INNER JOIN gbm_organisasi d ON c.parent_id=d.id
		  WHERE DATE_FORMAT(tanggal,'%Y-%m') ='" . $t . "'
		  and d.id=" . $afd['id'] . "
		  GROUP BY d.nama
		  ORDER BY  d.nama
		  ")->row_array();

			$name = substr($afd['nama'], 9, (strlen($afd['nama']) - 9));
			$series = array();
			$series[] = array("name" => "panen", "value" => (int)$retrievePanen['value']);
			$series[] = array("name" => "pemeliharaan", "value" => (int)$retrievePemeliharaan['value']);
			$res[] = array("name" => $name, "series" => $series);
		}

		$this->set_response($res, REST_Controller::HTTP_OK);
	}
	public function trk_pemakaian_solar_perbulan_get($param)
	{

		$t = date("Y-m");
		if ($param) {
			if ($param == 'bulan_ini') {
				$t = date("Y-m");
				$d1 = new DateTime();
				$tanggal1 = new DateTime();
				$d2 = new DateTime();
				$tanggal1 = $d1;
			} else if ($param == 'bulan_lalu') {
				$t = date('Y-m', strtotime("first day of -1 month"));
				$t1 = date('Y-m-d', strtotime("first day of -1 month"));
				$t2 = date('Y-m-d', strtotime("first day of -1 month"));
				$d1 = new DateTime($t1);
				$tanggal1 = new DateTime($t1);
				$d2 = new DateTime($t2);
			} else {
				$t = $param;
				$t1 = $param . "-01";
				$t2 =  $param . "-01";;
				$d1 = new DateTime($t1);
				$tanggal1 = new DateTime($t1);
				$d2 = new DateTime($t2);
			}
		}


		$retrieve = $this->db->query("SELECT concat(c.kode,c.nama)as name,SUM(b.qty)AS value FROM inv_pemakaian_ht a INNER JOIN inv_pemakaian_dt b
		  ON a.id=b.inv_pemakaian_id INNER JOIN trk_kendaraan c ON b.traksi_id=c.id
		  WHERE b.item_id=9 AND DATE_FORMAT(a.tanggal ,'%Y-%m') ='" . $t . "'
		  GROUP BY concat(c.kode,c.nama)
		  ORDER BY SUM(b.qty) DESC 
			LIMIT 20	  
		  ")->result_array();

		$this->set_response($retrieve, REST_Controller::HTTP_OK);
	}
	public function sls_top10_penjualan_by_item_get($param)
	{

		$t = date("Y-m");
		if ($param) {
			if ($param == 'bulan_ini') {
				$t = date("Y-m");
				$d1 = new DateTime();
				$tanggal1 = new DateTime();
				$d2 = new DateTime();
				$tanggal1 = $d1;
			} else if ($param == 'bulan_lalu') {
				$t = date('Y-m', strtotime("first day of -1 month"));
				$t1 = date('Y-m-d', strtotime("first day of -1 month"));
				$t2 = date('Y-m-d', strtotime("first day of -1 month"));
				$d1 = new DateTime($t1);
				$tanggal1 = new DateTime($t1);
				$d2 = new DateTime($t2);
			} else {
				$t = $param;
				$t1 = $param . "-01";
				$t2 =  $param . "-01";;
				$d1 = new DateTime($t1);
				$tanggal1 = new DateTime($t1);
				$d2 = new DateTime($t2);
			}
		}


		$retrieve = $this->db->query("SELECT
		c.kode as kode_barang,
		c.nama as name,SUM(b.qty * b.harga)AS value
		FROM  sls_so_ht a INNER JOIN  sls_so_dt b
		ON a.id=b.so_hd_id 
		LEFT join inv_item c on b.item_id=c.id 
		LEFT join gbm_uom d on c.uom_id=d.id 
		 WHERE DATE_FORMAT(a.tanggal ,'%Y-%m') ='" . $t . "'
		GROUP BY 	c.kode,c.nama		
		  ORDER BY SUM(b.qty * b.harga) DESC 
			LIMIT 10	  
		  ")->result_array();

		$this->set_response($retrieve, REST_Controller::HTTP_OK);
	}
	public function prc_top10_pembelian_by_item_get($param)
	{

		$t = date("Y-m");
		if ($param) {
			if ($param == 'bulan_ini') {
				$t = date("Y-m");
				$d1 = new DateTime();
				$tanggal1 = new DateTime();
				$d2 = new DateTime();
				$tanggal1 = $d1;
			} else if ($param == 'bulan_lalu') {
				$t = date('Y-m', strtotime("first day of -1 month"));
				$t1 = date('Y-m-d', strtotime("first day of -1 month"));
				$t2 = date('Y-m-d', strtotime("first day of -1 month"));
				$d1 = new DateTime($t1);
				$tanggal1 = new DateTime($t1);
				$d2 = new DateTime($t2);
			} else {
				$t = $param;
				$t1 = $param . "-01";
				$t2 =  $param . "-01";;
				$d1 = new DateTime($t1);
				$tanggal1 = new DateTime($t1);
				$d2 = new DateTime($t2);
			}
		}


		$retrieve = $this->db->query("SELECT
		c.kode as kode_barang,
		c.nama as  name,SUM(b.qty * b.harga)AS value
		FROM  prc_po_ht a INNER JOIN  prc_po_dt b
		ON a.id=b.po_hd_id 
		LEFT join inv_item c on b.item_id=c.id 
		LEFT join gbm_uom d on c.uom_id=d.id 
		 WHERE DATE_FORMAT(a.tanggal ,'%Y-%m') ='" . $t . "'
		GROUP BY 	c.kode,c.nama		
		  ORDER BY SUM(b.qty * b.harga) DESC 
			LIMIT 10	  
		  ")->result_array();

		$this->set_response($retrieve, REST_Controller::HTTP_OK);
	}
	// public function sls_top10_penjualan_by_item_get($param)
	// {

	// 	$t = date("Y-m");
	// 	if ($param) {
	// 		if ($param == 'bulan_ini') {
	// 			$t = date("Y-m");
	// 			$d1 = new DateTime();
	// 			$tanggal1 = new DateTime();
	// 			$d2 = new DateTime();
	// 			$tanggal1 = $d1;
	// 		} else if ($param == 'bulan_lalu') {
	// 			$t = date('Y-m', strtotime("first day of -1 month"));
	// 			$t1 = date('Y-m-d', strtotime("first day of -1 month"));
	// 			$t2 = date('Y-m-d', strtotime("first day of -1 month"));
	// 			$d1 = new DateTime($t1);
	// 			$tanggal1 = new DateTime($t1);
	// 			$d2 = new DateTime($t2);
	// 		} else {
	// 			$t = $param;
	// 			$t1 = $param . "-01";
	// 			$t2 =  $param . "-01";;
	// 			$d1 = new DateTime($t1);
	// 			$tanggal1 = new DateTime($t1);
	// 			$d2 = new DateTime($t2);
	// 		}
	// 	}


	// 	$retrieve = $this->db->query("SELECT
	// 	c.kode as kode_barang,
	// 	c.nama as name,SUM(b.qty * b.harga)AS value
	// 	FROM  sls_so_ht a INNER JOIN  sls_so_dt b
	// 	ON a.id=b.so_hd_id 
	// 	LEFT join inv_item c on b.item_id=c.id 
	// 	LEFT join gbm_uom d on c.uom_id=d.id 
	// 	 WHERE DATE_FORMAT(a.tanggal ,'%Y-%m') ='" . $t . "'
	// 	GROUP BY 	c.kode,c.nama		
	// 	  ORDER BY SUM(b.qty * b.harga) DESC 
	// 		LIMIT 10	  
	// 	  ")->result_array();

	// 	$this->set_response($retrieve, REST_Controller::HTTP_OK);
	// }
	public function acc_pembayaran_by_tipe_get($param)
	{

		$t = date("Y-m");
		if ($param) {
			if ($param == 'bulan_ini') {
				$t = date("Y-m");
				$d1 = new DateTime();
				$tanggal1 = new DateTime();
				$d2 = new DateTime();
				$tanggal1 = $d1;
			} else if ($param == 'bulan_lalu') {
				$t = date('Y-m', strtotime("first day of -1 month"));
				$t1 = date('Y-m-d', strtotime("first day of -1 month"));
				$t2 = date('Y-m-d', strtotime("first day of -1 month"));
				$d1 = new DateTime($t1);
				$tanggal1 = new DateTime($t1);
				$d2 = new DateTime($t2);
			} else {
				$t = $param;
				$t1 = $param . "-01";
				$t2 =  $param . "-01";;
				$d1 = new DateTime($t1);
				$tanggal1 = new DateTime($t1);
				$d2 = new DateTime($t2);
			}
		}
		$result = array();

		$retrieveTipe = $this->db->query("SELECT * from kln_tipe_bayar ")->result_array();
		foreach ($retrieveTipe as $key => $value) {
			$totalByTpe = 0;
			$retrieve = $this->db->query("
				SELECT SUM(b.jumlah)AS jumlah FROM kln_invoice_rawat_jalan_ht a
				inner join kln_invoice_rawat_jalan_bayar b
				INNER JOIN kln_tipe_bayar c ON b.tipe_bayar_id=c.id
				ON a.id=b.invoice_id
				WHERE DATE_FORMAT(a.tanggal ,'%Y-%m') ='" . $t . "'
				and b.tipe_bayar_id=" . $value['id'] . "	  
				")->row_array();
			if ($retrieve) {
				$jumlah = $retrieve['jumlah'] ? $retrieve['jumlah'] : 0;
			} else {
				$jumlah = 0;
			}
			$totalByTpe = $jumlah;

			$retrieve = $this->db->query("
				SELECT SUM(b.jumlah)AS jumlah FROM kln_invoice_rawat_inap_ht a
				inner join kln_invoice_rawat_inap_bayar b
				INNER JOIN kln_tipe_bayar c ON b.tipe_bayar_id=c.id
				ON a.id=b.invoice_id
				WHERE DATE_FORMAT(a.tanggal ,'%Y-%m') ='" . $t . "'
				and b.tipe_bayar_id=" . $value['id'] . "	  
				")->row_array();
			if ($retrieve) {
				$jumlah = $retrieve['jumlah'] ? $retrieve['jumlah'] : 0;
			} else {
				$jumlah = 0;
			}
			$totalByTpe = $totalByTpe + $jumlah;
			if ($totalByTpe > 0) {
				$result[] = array('name' => $value['nama'], 'value' => $totalByTpe);
			}
		}

		$this->set_response($result, REST_Controller::HTTP_OK);
	}
	public function pks_tbs_olah_get($param)
	{
		//$t = '2022-09';
		$t = date("Y-m");
		if ($param) {
			if ($param == 'bulan_ini') {
				$t = date("Y-m");
				$d1 = new DateTime();
				$d2 = new DateTime();
				$tanggal1 = $d1;
			} else if ($param == 'bulan_lalu') {
				$t = date('Y-m', strtotime("first day of -1 month"));
				$t1 = date('Y-m-d', strtotime("first day of -1 month"));
				$t2 = date('Y-m-d', strtotime("first day of -1 month"));
				$d1 = new DateTime($t1);
				$d2 = new DateTime($t2);
				$tanggal1 = $d1;
			} else {
				$t = $param;
				$t1 = $param . "-01";
				$t2 =  $param . "-01";;
				$d1 = new DateTime($t1);
				$tanggal1 = new DateTime($t1);
				$d2 = new DateTime($t2);
			}
		}



		$res = array();
		// $d1 = new DateTime();
		// $d2 = new DateTime();
		$d2->modify('last day of this month');
		$interval = $d1->diff($d2);
		$jumlah_hari = $interval->days;

		$retrieve = $this->db->query("SELECT DATE_FORMAT(tanggal,'%d')as tanggal,dua_hi,tiga_hi,delapan_hi from pks_lhp
			WHERE DATE_FORMAT(tanggal,'%Y-%m') ='" . $t . "'
			ORDER BY DATE_FORMAT(tanggal,'%d')
			")->result_array();
		$series1 = [];
		$array_res1 = array();
		$array_res1 = [];
		$series2 = [];
		$array_res2 = array();
		$array_res2 = [];
		$series3 = [];
		$array_res3 = array();
		$array_res3 = [];
		foreach ($retrieve as $key => $value) {
			$array_res1[$value['tanggal']] = $value['dua_hi'];
			$array_res2[$value['tanggal']] = $value['tiga_hi'];
			$array_res3[$value['tanggal']] = $value['delapan_hi'];
		}

		$d1->modify('first day of this month');
		while ($d1 <= $d2) {
			$dd = $d1->format('d');
			if (array_key_exists($dd, $array_res1)) {
				$data1 = array("name" => $dd, "value" => $array_res1[$dd] / 1000);
			} else {
				$data1 = array("name" => $dd, "value" => 0);
			}
			if (array_key_exists($dd, $array_res2)) {
				$data2 = array("name" => $dd, "value" => $array_res2[$dd] / 1000);
			} else {
				$data2 = array("name" => $dd, "value" => 0);
			}
			if (array_key_exists($dd, $array_res3)) {
				$data3 = array("name" => $dd, "value" => $array_res3[$dd] / 1000);
			} else {
				$data3 = array("name" => $dd, "value" => 0);
			}
			$series1[] = $data1;
			$series2[] = $data2;
			$series3[] = $data3;
			$d1->modify('+1 day');
		}
		$res = array();
		// $res['mulai'] = $d1;
		// $res['sd'] = $d2;
		$res[] = array("name" => 'TBS OLAH', "series" => $series1);
		$res[] = array("name" => 'MINYAK SAWIT', "series" => $series2);
		$res[] = array("name" => 'INTI SAWIT', "series" => $series3);


		$data = $res;
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	public function estate_panen_afdeling_harianOLD_get()
	{
		$t = '2022-09';
		$retrieveTgl = $this->db->query("SELECT DATE_FORMAT(tanggal,'%d')as tanggal
		FROM est_bkm_panen_ht a INNER JOIN est_bkm_panen_dt b
		ON a.id=b.bkm_panen_id INNER JOIN gbm_organisasi c ON b.blok_id=c.id
		INNER JOIN gbm_organisasi d ON c.parent_id=d.id
		WHERE DATE_FORMAT(tanggal,'%Y-%m') ='" . $t . "'
		GROUP BY DATE_FORMAT(tanggal,'%d')
		ORDER BY tanggal
		")->result_array();

		$retrieveNamaAfd = $this->db->query("SELECT d.nama as nama_afdeling
		FROM est_bkm_panen_ht a INNER JOIN est_bkm_panen_dt b
		ON a.id=b.bkm_panen_id INNER JOIN gbm_organisasi c ON b.blok_id=c.id
		INNER JOIN gbm_organisasi d ON c.parent_id=d.id
		WHERE DATE_FORMAT(tanggal,'%Y-%m') ='" . $t . "'
		GROUP BY d.nama
		ORDER BY d.nama
		")->result_array();

		$retrieve = $this->db->query("SELECT d.id,d.kode,d.nama as nama_afdeling ,DATE_FORMAT(tanggal,'%d')as tanggal,SUM(b.hasil_kerja_jjg)AS jjg,SUM(b.hasil_kerja_kg)as kg,SUM(b.hasil_kerja_brondolan)AS brondolan  FROM est_bkm_panen_ht a INNER JOIN est_bkm_panen_dt b
		ON a.id=b.bkm_panen_id INNER JOIN gbm_organisasi c ON b.blok_id=c.id
		INNER JOIN gbm_organisasi d ON c.parent_id=d.id
		WHERE DATE_FORMAT(tanggal,'%Y-%m') ='" . $t . "'
		GROUP BY d.id,d.kode,d.nama,DATE_FORMAT(tanggal,'%d')
		ORDER BY tanggal, d.kode
		")->result_array();

		$res_afd = array();
		$res_dt = array();
		$res_detail = array();
		$res = array();
		foreach ($retrieve as $key => $value) {
			$res_afd[$value['nama_afdeling']][$value['tanggal']] = $value['jjg'];
		}

		foreach ($retrieveTgl as $key => $tgl) {
			foreach ($retrieve as $key => $value) {
				$jjg = 0;
				if ($res_afd[$value['nama_afdeling']][$tgl['tanggal']]) {
					$jjg = $res_afd[$value['nama_afdeling']][$tgl['tanggal']];
				}
				$res_dt[$value['nama_afdeling']][$tgl['tanggal']] = $jjg;
			}
		}

		foreach ($retrieveNamaAfd as $key => $nama_afd) {
			foreach ($res_dt[$nama_afd['nama_afdeling']] as $key => $value) {
				$res_detail[$nama_afd['nama_afdeling']][] = $value;
			}
		}

		$res_tgl = array();
		foreach ($retrieveTgl as $key => $tgl) {
			$res_tgl[] = $tgl['tanggal'];
		}
		$res['detail'] = $res_detail;
		$res['tanggal'] = $res_tgl;
		$data = $res;
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	public function estate_curah_hujan_harian_get($param)
	{
		//$t = '2022-09';
		$t = date("Y-m");
		if ($param) {
			if ($param == 'bulan_ini') {
				$t = date("Y-m");
				$d1 = new DateTime();
				$tanggal1 = new DateTime();
				$d2 = new DateTime();
				$tanggal1 = $d1;
			} else if ($param == 'bulan_lalu') {
				$t = date('Y-m', strtotime("first day of -1 month"));
				$t1 = date('Y-m-d', strtotime("first day of -1 month"));
				$t2 = date('Y-m-d', strtotime("first day of -1 month"));
				$d1 = new DateTime($t1);
				$tanggal1 = new DateTime($t1);
				$d2 = new DateTime($t2);
			} else {
				$t = $param;
				$t1 = $param . "-01";
				$t2 =  $param . "-01";;
				$d1 = new DateTime($t1);
				$tanggal1 = new DateTime($t1);
				$d2 = new DateTime($t2);
			}
		}


		$retrieveNamaAfd = $this->db->query("SELECT d.id,d.nama as nama_afdeling
		FROM est_curah_hujan a INNER JOIN 
		gbm_organisasi d ON a.afdeling_id=d.id
		WHERE DATE_FORMAT(tanggal,'%Y-%m') ='" . $t . "'
		GROUP BY d.nama,d.id
		ORDER BY d.nama
		")->result_array();
		// $d1 = new DateTime();
		// $d2 = new DateTime();
		$res = array();

		$d2->modify('last day of this month');
		$interval = $d1->diff($d2);
		$jumlah_hari = $interval->days;
		foreach ($retrieveNamaAfd as $key => $nama_afd) {
			$retrieve = $this->db->query("SELECT d.id,d.nama as nama_afdeling,DATE_FORMAT(tanggal,'%d')as tanggal,AVG((pagi+sore+malam)/3)AS nilai 
			FROM est_curah_hujan a INNER JOIN 
 			gbm_organisasi d ON a.afdeling_id=d.id
			WHERE DATE_FORMAT(tanggal,'%Y-%m') ='" . $t . "'
			and d.id=" . $nama_afd['id'] . "
			GROUP BY d.id,d.kode,d.nama,DATE_FORMAT(tanggal,'%d')
			ORDER BY DATE_FORMAT(tanggal,'%d')
			")->result_array();
			$name =  $nama_afd['nama_afdeling'];
			$series = [];
			$array_res = array();
			$array_res = [];
			foreach ($retrieve as $key => $value) {
				$array_res[$value['tanggal']] = round($value['nilai'], 2);
			}
			// $d1 = $tanggal1;

			if ($param) {
				if ($param == 'bulan_ini') {
					$t1 = date('Y-m-d');
				} else if ($param == 'bulan_lalu') {

					$t1 = date('Y-m-d', strtotime("first day of -1 month"));
				} else {
					$t1 = $param . "-01";
				}
			}
			$d1 = new DateTime($t1);
			$d1->modify('first day of this month');
			while ($d1 <= $d2) {
				$dd = $d1->format('d');
				if (array_key_exists($dd, $array_res)) {
					$data = array("name" => $dd, "value" => $array_res[$dd]);
				} else {
					$data = array("name" => $dd, "value" => 0);
				}
				$series[] = $data;
				$d1->modify('+1 day');
			}

			// foreach ($retrieve as $key => $value) {


			// 	$data= array("name"=> $value['tanggal'],"value"=> $value['jjg']);
			// 	$series[]=$data;
			// }
			$res[] = array("name" => $name, "series" => $series, "tgl1" => $tanggal1);
		}

		$data = $res;
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	public function siswa_get($siswa_id) {}
	public function pengajar_get() {}
	public function admin_get() {}
}
