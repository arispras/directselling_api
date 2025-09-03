<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

class EstProduksipanen extends BD_Controller
{
	public $user_id;
	public $theCredential;

	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('GbmOrganisasiModel');
		$this->load->model('EstProduksiPanenModel');
		$this->load->model('M_DatatablesModel');
		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id = $this->user_data->id;
		$this->load->library('pdfgenerator');
	}
	public function list_post()
	{
		$post = $this->post();
		$param = $post['parameter'];

		$query  = "SELECT a.*,b.nama as nama_divisi FROM est_produksi_panen_ht a 
		inner join  gbm_organisasi b on a.divisi_id=b.id";
		$search = array('a.tanggal', 'b.nama');
		$where  = null;

		$isWhere = " 1=1";
		if ($param['tgl_mulai'] && $param['tgl_mulai']) {
			$isWhere = " a.tanggal between '" . $param['tgl_mulai'] . "' and '" . $param['tgl_akhir'] . "'";
		}
		if ($param['divisi_id']) {
			$isWhere = $isWhere . " and a.divisi_id =" . $param['divisi_id'] . "";
		}

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		if (count($data['data']) > 0) {
			for ($i = 0; $i < (count($data['data'])); $i++) {
				$h = $data['data'][$i];
				$ket = "";
				if ($h) {
					$queryDetail = "SELECT  sum(a.jum_ha)AS jum_ha,sum(a.jum_hk)AS jum_hk,sum(a.jum_jjg)AS jum_jjg ,sum(a.jum_kg) AS jum_kg,
					sum(a.jum_jjg_kirim)AS jum_jjg_kirim ,sum(a.jum_kg_pks)AS jum_kg_pks,sum(a.jum_kg_pks)AS jum_kg_pks,
					sum(a.jjg_afkir)AS jjg_afkir,sum(a.kg_afkir)AS kg_afkir,sum(a.jjg_restan)AS jjg_restan,sum(a.kg_restan)AS kg_restan
					FROM est_produksi_panen_dt a
					WHERE a.produksi_panen_id=" . $h['id'] . "";
					 $d = $this->db->query($queryDetail)->result_array();
					
					foreach ($d as $key => $dt) {
						$ket = $ket .
						' ha:' .  number_format($dt['jum_ha']?$dt['jum_ha']:0, 2);
						$ket = $ket .
						' ~ hk:' .  number_format($dt['jum_hk']?$dt['jum_hk']:0, 2);
						$ket = $ket .
						' ~ jjg:' .  number_format($dt['jum_jjg']?$dt['jum_jjg']:0, 2);
						$ket = $ket .
						' ~ kg:' .  number_format($dt['jum_kg']?$dt['jum_ha']:0, 2);
						$ket = $ket .
						' ~ jjg_kirim:' .  number_format($dt['jum_jjg_kirim']?$dt['jum_jjg_kirim']:0, 2);
						$ket = $ket .
						' ~ kg_pks:' .  number_format($dt['jum_kg_pks']?$dt['jum_kg_pks']:0, 2);
						$ket = $ket .
						' ~ jjg_afkir:' .  number_format($dt['jjg_afkir']?$dt['jjg_afkir']:0, 2);
						$ket = $ket .
						' ~ jjg_restan:' .  number_format($dt['jjg_restan']?$dt['jjg_restan']:0, 2);
						$ket = $ket .
						' ~ kg_restan:' .  number_format($dt['kg_restan']?$dt['kg_restan']:0, 2);
					// 	$ket = $ket .
					// 		' ha:' .  number_format($dt['jum_ha'], 2) . ' hk:' . number_format($dt['jum_hk'], 2) . ' jjg:' . number_format($dt['jum_jjg'], 2)
					// 		. ' kg:' . number_format($dt['jum_kg'], 2) . ' jjg_kirim:' . number_format($dt['jum_jjg_kirim'], 2) . ' kg_pks:' . number_format($dt['jum_kg_pk'], 2)
					// 		. ' afkir:' . number_format($dt['jjg_afkir'], 2) . ' kg_afkir:' . number_format($dt['kg_afkir'], 2) . ' restan:' . number_format($dt['jjg_restan'], 2)
					// 		. ' kg restan:' . number_format($dt['kg_restan'], 2);
					 }
					
				}
				$data['data'][$i]['keterangan'] = $ket;
			}
		}
		$this->set_response($data, REST_Controller::HTTP_OK);
	}

	function index_get($id = '')
	{
		$retrieve = $this->EstProduksiPanenModel->retrieve_by_id($id);
		$retrieve['detail'] = $this->EstProduksiPanenModel->retrieve_detail($id);


		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}


	function index_post()
	{
		$input = $this->post();
		$input['dibuat_oleh'] = $this->user_id;
		//$input['no_rekap_panen'] = $this->getLastNumber('est_produksi_panen_ht', 'no_rekap_panen', 'rekap_panen');
		// var_dump($input);
		$res = $this->EstProduksiPanenModel->create($input);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'est_produksi_panen', 'action' => 'new', 'entity_id' => $res);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	function index_put($segment_3 = null)
	{
		$id = (int)$segment_3;
		$data = $this->put();
		$data['diubah_oleh'] = $this->user_id;
		$res = $this->EstProduksiPanenModel->update($id, $data);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'est_produksi_panen', 'action' => 'edit', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	public function index_delete($id)
	{

		$res = $this->EstProduksiPanenModel->delete($id);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id' => $id)), 'entity' => 'est_produksi_panen', 'action' => 'delete', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}


	function laporan_detail_post()
	{
		error_reporting(0);
		$estate_id     = $this->post('estate_id', true);
		$afdeling_id     = $this->post('afdeling_id', true);
		$periode =  $this->post('periode', true);

		$format_laporan     = $this->post('format_laporan', true);
		$date = new DateTime($periode . '-01');
		$date->modify('last day of this month');
		$last_day_this_month = $date->format('Y-m-d');
		(int)$jumhari = date('d', strtotime($last_day_this_month));
		$tgl_mulai = $periode . '-01';
		$tgl_akhir = $periode . '-' . sprintf("%02d", $jumhari);
		$nama_afdeling = "Semua";
		$retrieveEstate = $this->db->query("select * from gbm_organisasi where id=" . $estate_id . "")->row_array();
		if ($afdeling_id) {
			$retrieveAfdeling = $this->db->query("select * from gbm_organisasi where id=" . $afdeling_id . "")->row_array();
			$nama_afdeling = $retrieveAfdeling['nama'];
		}

		// var_dump($results);
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'pdf') {
			$html = get_header_pdf_report();
		} else {
			$html = get_header_report_v2();
		}

		$html = $html . '
	<div class="row">
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
  <h3 class="title">LAPORAN PRODUKSI PANEN</h3>
  <table class="no_border" style="width:30%">
			
			<tr>
					<td>Estate</td>
					<td>:</td>
					<td>' .  $retrieveEstate['nama'] . '</td>
			</tr>
			<tr>
					<td>Afdeling</td>
					<td>:</td>
					<td>' .  $nama_afdeling . '</td>
			</tr>
			<tr>	
					<td>Periode</td>
					<td>:</td>
					<td>' . $tgl_mulai . ' s/d ' . $tgl_akhir . '</td>
			</tr>
			
	</table>
			<br>';

		$html = $html . "

<table   border='1' width='100%' style='border-collapse: collapse;'>
<thead>
<tr>
	<th rowspan=3 >No</th>
	<th rowspan=3>Afdeling</th>
	<th rowspan=3>Blok</th>
	<th rowspan=3>Inti/Plasma</th>
	<th rowspan=3>Luas</th>
	<th rowspan=3>SPH</th>
	<th colspan=" . ($jumhari * 10) . "  style='text-align: center'> " . $periode . "  </th>
	<th colspan=10 rowspan=2  style='text-align: center'>TOTAL</th>
</tr>
";

		$html = $html . "<tr>";
		for ($i = 1; $i < ($jumhari + 1); $i++) {
			$html = $html . "<th style='text-align: center' colspan=10>" . $i . "</td>";
		}

		$html = $html . "</tr> ";
		$html = $html . "<tr>";
		for ($i = 1; $i < ($jumhari + 1); $i++) {
			$html = $html . "<th style='text-align: center'>Ha</th>";
			$html = $html . "<th style='text-align: center'>Hk</th>";
			$html = $html . "<th style='text-align: center'>Jjg</th>";
			$html = $html . "<th style='text-align: center'>Kg</th>";
			$html = $html . "<th style='text-align: center'>Jjg Kirim</th>";
			$html = $html . "<th style='text-align: center'>Kg PKS</th>";

			$html = $html . "<th style='text-align: center'>Jjg Afkir</th>";
			$html = $html . "<th style='text-align: center'>Kg Afkir</th>";
			$html = $html . "<th style='text-align: center'>Jjg Restan</th>";
			$html = $html . "<th style='text-align: center'>Kg Restan</th>";
		}
		$html = $html . "<th style='text-align: center'>Ha</th>";
		$html = $html . "<th style='text-align: center'>Hk</th>";
		$html = $html . "<th style='text-align: center'>Jjg</th>";
		$html = $html . "<th style='text-align: center'>Kg</th>";
		$html = $html . "<th style='text-align: center'>Jjg Kirim</th>";
		$html = $html . "<th style='text-align: center'>Kg PKS</th>";

		$html = $html . "<th style='text-align: center'>Jjg Afkir</th>";
		$html = $html . "<th style='text-align: center'>Kg Afkir</th>";
		$html = $html . "<th style='text-align: center'>Jjg Restan</th>";
		$html = $html . "<th style='text-align: center'>Kg Restan</th>";


		$html = $html . "</tr> </thead>";
		$nourut = 0;
		$grandtotal_jjg = 0;
		$grandtotal_kg = 0;
		$grandtotal_luas = 0;
		$grandtotal_hk = 0;
		$grandtotal_jjg_kirim = 0;

		$grandtotal_jjg_afkir = 0;
		$grandtotal_jjg_restan = 0;
		$grandtotal_kg_afkir = 0;
		$grandtotal_kg_restan = 0;

		$grandtotal_kg_pks = 0;

		$totalJjgPerHari = array();
		$totalJjgPerHari = [];
		$totalKgPerHari = array();
		$totalKgPerHari = [];
		$totalLuasPerHari = array();
		$totalLuasPerHari = [];
		$totalHkPerHari = array();
		$totalHkPerHari = [];
		$totalJjgKirimPerHari = array();
		$totalJjgKirimPerHari = [];

		$totalJjgAfkirPerHari = array();
		$totalJjgAfkirPerHari = [];
		$totalJjgRestanPerHari = array();
		$totalJjgRestanPerHari = [];

		$totalKgAfkirPerHari = array();
		$totalKgAfkirPerHari = [];

		$totalKgRestanPerHari = array();
		$totalKgRestanPerHari = [];

		$totalKgPKSPerHari = array();
		$totalKgPKSPerHari = [];

		if ($afdeling_id) {
			$qry = "SELECT DISTINCT b.blok_id,d.nama AS nama_afdeling,c.kode AS kode_blok,c.nama AS nama_blok,e.tahuntanam,e.jumlahpokok,e.intiplasma,e.luasareaproduktif,
			(e.jumlahpokok/e.luasareaproduktif) AS sph
			FROM est_produksi_panen_ht a 
			INNER JOIN est_produksi_panen_dt b ON a.id=b.produksi_panen_id
			INNER JOIN gbm_organisasi c ON b.blok_id=c.id
			INNER JOIN gbm_organisasi d ON a.divisi_id=d.id
			INNER JOIN gbm_blok e ON b.blok_id=e.organisasi_id
			INNER JOIN gbm_organisasi f ON  d.parent_id=f.id
			WHERE f.parent_id=" . $estate_id . " and a.divisi_id=" . $afdeling_id . "  and tanggal>='" . $tgl_mulai . "' 
			and tanggal<='" . $tgl_akhir . "' order by d.nama,c.nama ";
		} else {
			$qry = "SELECT DISTINCT b.blok_id,d.nama AS nama_afdeling,c.kode AS kode_blok,c.nama AS nama_blok,e.tahuntanam,e.jumlahpokok,e.intiplasma,e.luasareaproduktif,
			(e.jumlahpokok/e.luasareaproduktif) AS sph
			FROM est_produksi_panen_ht a 
			INNER JOIN est_produksi_panen_dt b ON a.id=b.produksi_panen_id
			INNER JOIN gbm_organisasi c ON b.blok_id=c.id
			INNER JOIN gbm_organisasi d ON a.divisi_id=d.id
			INNER JOIN gbm_blok e ON b.blok_id=e.organisasi_id
			INNER JOIN gbm_organisasi f ON  d.parent_id=f.id
			WHERE f.parent_id=" . $estate_id . " and tanggal>='" . $tgl_mulai . "' 
			and tanggal<='" . $tgl_akhir . "' order by d.nama,c.nama ";
		}

		// $qry = "SELECT DISTINCT blok_id,kode_blok,nama_blok,nama_afdeling,intiplasma,luasareaproduktif FROM est_bkm_panen_vw WHERE id_estate=" . $estate_id . " and tanggal>='" . $tgl_mulai . "' 
		// and tanggal<='" . $tgl_akhir . "' order by nama_afdeling,nama_blok ";
		$retrieveBlok = $this->db->query($qry)->result_array();

		for ($i = 1; $i < ($jumhari + 1); $i++) {
			$totalJjgPerHari[] = 0;
			$totalKgPerHari[] = 0;
			$totalLuasPerHari[] = 0;
			$totalHkPerHari[] = 0;
			$totalJjgKirimPerHari[] = 0;

			$totalJjgAfkirPerHari[] = 0;
			$totalJjgRestanPerHari[] = 0;
			$totalKgAfkirPerHari[] = 0;
			$totalKgRestanPerHari[] = 0;

			$totalKgPKSPerHari[] = 0;
		}

		foreach ($retrieveBlok as $key => $d) {
			$html = $html . "<tr>";
			$totalkg = 0;
			$nourut = $nourut + 1;
			$html = $html . "<td style='text-align: center'>" . $nourut . "</td>";
			$html = $html . "<td style='text-align: center'>" . $d['nama_afdeling'] . "</td>";
			$html = $html . "<td style='text-align: center'>" . $d['nama_blok'] . "</td>";
			$html = $html . "<td style='text-align: center'>" . $d['intiplasma'] . "</td>";
			$html = $html . "<td style='text-align: center'>" . $d['luasareaproduktif'] . "</td>";
			$html = $html . "<td style='text-align: center'>" . number_format($d['sph']) . "</td>";
			$total_hasil_kerja_jjg = 0;
			$total_hasil_kerja_kg = 0;
			$total_hasil_kerja_luas = 0;
			$total_jumlah_hk = 0;
			$total_jjg_kirim = 0;

			$total_jjg_afkir = 0;
			$total_jjg_restan = 0;
			$total_kg_afkir = 0;
			$total_kg_restan = 0;

			$total_kg_pks = 0;

			for ($i = 1; $i < ($jumhari + 1); $i++) {
				$tgl = $periode  . '-' . sprintf("%02d", $i);
				$retrievePanen = $this->db->query("SELECT SUM( b.jum_ha) AS jum_ha,sum(b.jum_hk) AS jum_hk,sum(b.jum_jjg)AS jum_jjg,sum(b.jum_kg)AS jum_kg,sum(b.jum_jjg_kirim)AS jum_jjg_kirim,SUM(b.jjg_afkir)AS jjg_afkir,SUM(b.jjg_restan) AS jjg_restan,SUM(b.kg_afkir) AS kg_afkir,sum(b.kg_restan) AS kg_restan,sum(b.jum_kg_pks)AS jum_kg_pks
				FROM est_produksi_panen_ht a 
			   INNER JOIN est_produksi_panen_dt b ON a.id=b.produksi_panen_id
			   INNER JOIN gbm_organisasi c ON b.blok_id=c.id
			   INNER JOIN gbm_organisasi d ON a.divisi_id=d.id
			   INNER JOIN gbm_blok e ON b.blok_id=e.organisasi_id
			   INNER JOIN gbm_organisasi f ON  d.parent_id=f.id
			    WHERE (b.blok_id =" . $d['blok_id'] . ") and
				 tanggal='" . $tgl . "'")->row_array();
				$hasil_kerja_jjg = $retrievePanen['jum_jjg'] ? $retrievePanen['jum_jjg'] : 0;
				$hasil_kerja_kg = $retrievePanen['jum_kg'] ? $retrievePanen['jum_kg'] : 0;
				$hasil_kerja_luas = $retrievePanen['jum_ha'] ? $retrievePanen['jum_ha'] : 0;
				$jumlah_hk = $retrievePanen['jum_hk'] ? $retrievePanen['jum_hk'] : 0;
				$jumlah_jjg_kirim = $retrievePanen['jum_jjg_kirim'] ? $retrievePanen['jum_jjg_kirim'] : 0;
				$jumlah_jjg_afkir = $retrievePanen['jjg_afkir'] ? $retrievePanen['jjg_afkir'] : 0;
				$jumlah_jjg_restan = $retrievePanen['jjg_restan'] ? $retrievePanen['jjg_restan'] : 0;
				$jumlah_kg_afkir = $retrievePanen['kg_afkir'] ? $retrievePanen['kg_afkir'] : 0;
				$jumlah_kg_restan = $retrievePanen['kg_restan'] ? $retrievePanen['kg_restan'] : 0;
				$jumlah_kg_pks = $retrievePanen['jum_kg_pks'] ? $retrievePanen['jum_kg_pks'] : 0;
				$index = "idx" . $i;
				// $jum=0;
				if (array_key_exists(($i - 1), $totalJjgPerHari)) {
					$totalJjgPerHari[$i - 1] = $totalJjgPerHari[$i - 1] + $hasil_kerja_jjg;
				} else {
					$totalJjgPerHari[] = $hasil_kerja_jjg;
				}
				if (array_key_exists(($i - 1), $totalKgPerHari)) {
					$totalKgPerHari[$i - 1] = $totalKgPerHari[$i - 1] + $hasil_kerja_kg;
				} else {
					$totalKgPerHari[] = $hasil_kerja_kg;
				}
				if (array_key_exists(($i - 1), $totalLuasPerHari)) {
					$totalLuasPerHari[$i - 1] = $totalLuasPerHari[$i - 1] + $hasil_kerja_luas;
				} else {
					$totalLuasPerHari[] = $hasil_kerja_luas;
				}
				if (array_key_exists(($i - 1), $totalHkPerHari)) {
					$totalHkPerHari[$i - 1] = $totalHkPerHari[$i - 1] + $jumlah_hk;
				} else {
					$totalHkPerHari[] = $jumlah_hk;
				}
				if (array_key_exists(($i - 1), $totalJjgKirimPerHari)) {
					$totalJjgKirimPerHari[$i - 1] = $totalJjgKirimPerHari[$i - 1] + $jumlah_jjg_kirim;
				} else {
					$totalJjgKirimPerHari[] = $jumlah_jjg_kirim;
				}
				// -----

				if (array_key_exists(($i - 1), $totalJjgAfkirPerHari)) {
					$totalJjgAfkirPerHari[$i - 1] = $totalJjgAfkirPerHari[$i - 1] + $jumlah_jjg_afkir;
				} else {
					$totalJjgAfkirPerHari[] = $jumlah_jjg_afkir;
				}
				if (array_key_exists(($i - 1), $totalJjgRestanPerHari)) {
					$totalJjgRestanPerHari[$i - 1] = $totalJjgRestanPerHari[$i - 1] + $jumlah_jjg_restan;
				} else {
					$totalJjgRestanPerHari[] = $jumlah_jjg_restan;
				}
				if (array_key_exists(($i - 1), $totalKgAfkirPerHari)) {
					$totalKgAfkirPerHari[$i - 1] = $totalKgAfkirPerHari[$i - 1] + $jumlah_kg_afkir;
				} else {
					$totalKgAfkirPerHari[] = $jumlah_kg_afkir;
				}
				if (array_key_exists(($i - 1), $totalKgRestanPerHari)) {
					$totalKgRestanPerHari[$i - 1] = $totalKgRestanPerHari[$i - 1] + $jumlah_kg_restan;
				} else {
					$totalKgRestanPerHari[] = $jumlah_kg_restan;
				}
				// ------

				if (array_key_exists(($i - 1), $totalKgPKSPerHari)) {
					$totalKgPKSPerHari[$i - 1] = $totalKgPKSPerHari[$i - 1] + $jumlah_kg_pks;
				} else {
					$totalKgPKSPerHari[] = $jumlah_kg_pks;
				}


				$total_hasil_kerja_jjg = $total_hasil_kerja_jjg + $hasil_kerja_jjg;
				$total_hasil_kerja_kg = $total_hasil_kerja_kg + $hasil_kerja_kg;
				$total_hasil_kerja_luas = $total_hasil_kerja_luas + $hasil_kerja_luas;
				$total_jumlah_hk = $total_jumlah_hk + $jumlah_hk;
				$total_jjg_kirim = $total_jjg_kirim + $jumlah_jjg_kirim;
				$total_kg_pks = $total_kg_pks + $jumlah_kg_pks;

				$total_jjg_afkir = $total_jjg_afkir + $jumlah_jjg_afkir;
				$total_kg_afkir = $total_kg_afkir + $jumlah_kg_afkir;
				$total_jjg_restan = $total_jjg_restan + $jumlah_jjg_restan;
				$total_kg_restan = $total_kg_restan + $jumlah_kg_restan;



				$grandtotal_jjg = $grandtotal_jjg + $hasil_kerja_jjg;
				$grandtotal_kg = $grandtotal_kg + $hasil_kerja_kg;
				$grandtotal_luas = $grandtotal_luas + $hasil_kerja_luas;
				$grandtotal_hk = $grandtotal_hk + $jumlah_hk;
				$grandtotal_jjg_kirim = $grandtotal_jjg_kirim + $jumlah_jjg_kirim;

				$grandtotal_jjg_afkir = $grandtotal_jjg_afkir + $jumlah_jjg_afkir;
				$grandtotal_jjg_restan = $grandtotal_jjg_restan + $jumlah_jjg_restan;
				$grandtotal_kg_afkir = $grandtotal_kg_afkir + $jumlah_kg_afkir;
				$grandtotal_kg_restan = $grandtotal_kg_restan + $jumlah_kg_restan;

				$grandtotal_kg_pks = $grandtotal_kg_pks + $jumlah_kg_pks;
				$html = $html . "<td style='text-align: right'>" . number_format($hasil_kerja_luas, 2) . " </td>";
				$html = $html . "<td style='text-align: right'>" . number_format($jumlah_hk, 2) . " </td>";
				$html = $html . "<td style='text-align: right'>" . number_format($hasil_kerja_jjg) . " </td>";
				$html = $html . "<td style='text-align: right'>" . number_format($hasil_kerja_kg, 2) . " </td>";
				$html = $html . "<td style='text-align: right'>" . number_format($jumlah_jjg_kirim) . " </td>";
				$html = $html . "<td style='text-align: right'>" . number_format($jumlah_kg_pks, 2) . " </td>";

				$html = $html . "<td style='text-align: right'>" . number_format($jumlah_jjg_afkir) . " </td>";
				$html = $html . "<td style='text-align: right'>" . number_format($jumlah_kg_afkir, 2) . " </td>";
				$html = $html . "<td style='text-align: right'>" . number_format($jumlah_jjg_restan) . " </td>";
				$html = $html . "<td style='text-align: right'>" . number_format($jumlah_kg_restan, 2) . " </td>";
			}
			$html = $html . "<td style='text-align: right'>" . number_format($total_hasil_kerja_luas, 2) . " </td>";
			$html = $html . "<td style='text-align: right'>" . number_format($total_jumlah_hk, 2) . " </td>";
			$html = $html . "<td style='text-align: right'>" . number_format($total_hasil_kerja_jjg) . " </td>";
			$html = $html . "<td style='text-align: right'>" . number_format($total_hasil_kerja_kg, 2) . " </td>";
			$html = $html . "<td style='text-align: right'>" . number_format($total_jjg_kirim) . " </td>";
			$html = $html . "<td style='text-align: right'>" . number_format($total_kg_pks, 2) . " </td>";

			$html = $html . "<td style='text-align: right'>" . number_format($total_jjg_afkir) . " </td>";
			$html = $html . "<td style='text-align: right'>" . number_format($total_kg_afkir, 2) . " </td>";
			$html = $html . "<td style='text-align: right'>" . number_format($total_jjg_restan) . " </td>";
			$html = $html . "<td style='text-align: right'>" . number_format($total_kg_restan, 2) . " </td>";


			$html = $html . "</tr>";
		}




		$html = $html . "<tr>";
		$html = $html . "<td style='text-align: center'> </td>";
		$html = $html . "<td style='text-align: center'></td>";
		$html = $html . "<td style='text-align: center'></td>";
		$html = $html . "<td style='text-align: center'></td>";
		$html = $html . "<td style='text-align: center'></td>";
		$html = $html . "<td style='text-align: center'></td>";

		for ($i = 1; $i < ($jumhari + 1); $i++) {
			$html = $html . "<td style='text-align: right'>" . number_format($totalLuasPerHari[$i - 1], 2) . " </td>";
			$html = $html . "<td style='text-align: right'>" . number_format($totalHkPerHari[$i - 1], 2) . " </td>";
			$html = $html . "<td style='text-align: right'>" . number_format($totalJjgPerHari[$i - 1]) . " </td>";
			$html = $html . "<td style='text-align: right'>" . number_format($totalKgPerHari[$i - 1], 2) . " </td>";
			$html = $html . "<td style='text-align: right'>" . number_format($totalJjgKirimPerHari[$i - 1]) . " </td>";
			$html = $html . "<td style='text-align: right'>" . number_format($totalKgPKSPerHari[$i - 1], 1) . " </td>";

			$html = $html . "<td style='text-align: right'>" . number_format($totalJjgAfkirPerHari[$i - 1]) . " </td>";
			$html = $html . "<td style='text-align: right'>" . number_format($totalKgAfkirPerHari[$i - 1], 2) . " </td>";
			$html = $html . "<td style='text-align: right'>" . number_format($totalJjgRestanPerHari[$i - 1]) . " </td>";
			$html = $html . "<td style='text-align: right'>" . number_format($totalKgRestanPerHari[$i - 1], 2) . " </td>";
		}
		$html = $html . "<td style='text-align: right'><b>" . number_format($grandtotal_luas, 2) . " </b></td>";
		$html = $html . "<td style='text-align: right'><b>" . number_format($grandtotal_hk, 2) . "</b> </td>";
		$html = $html . "<td style='text-align: right'><b>" . number_format($grandtotal_jjg) . "</b> </td>";
		$html = $html . "<td style='text-align: right'><b>" . number_format($grandtotal_kg, 2) . " </b></td>";
		$html = $html . "<td style='text-align: right'><b>" . number_format($grandtotal_jjg_kirim) . "</b> </td>";
		$html = $html . "<td style='text-align: right'><b>" . number_format($grandtotal_kg_pks, 2) . " </b></td>";

		$html = $html . "<td style='text-align: right'><b>" . number_format($grandtotal_jjg_afkir) . "</b> </td>";
		$html = $html . "<td style='text-align: right'><b>" . number_format($grandtotal_kg_afkir, 2) . "</b> </td>";
		$html = $html . "<td style='text-align: right'><b>" . number_format($grandtotal_jjg_restan) . "</b> </td>";
		$html = $html . "<td style='text-align: right'><b>" . number_format($grandtotal_kg_restan, 2) . "</b> </td>";


		$html = $html . "</tr>";
		$html = $html . "</table>";


		if ($format_laporan == 'xls') {
			// $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html;
			// $spreadsheet = $reader->loadFromString($html);
			// // $reader->setSheetIndex(1);
			// //$spreadhseet = $reader->loadFromString($secondHtmlString, $spreadsheet);
			// $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
			// header("Pragma: public");
			// header("Expires: 0");
			// header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			// header("Content-Type: application/force-download");
			// header("Content-Type: application/octet-stream");
			// header("Content-Type: application/download");
			// header("Content-Disposition: attachment;filename=test.xlsx");
			// header("Content-Transfer-Encoding: binary ");

			// ob_end_clean();
			// ob_start();
			// $objWriter->save('php://output');
			echo $html;
		} else if ($format_laporan == 'view') {
			echo $html;
		} else {
			$filename = 'report_' . time();
			$this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		}
	}
	function laporan_detail2_post()
	{

		error_reporting(0);
		$format_laporan =  $this->post('tipe_laporan', true);
		$judulLokasi = 'Semua';
		$estate_id     = $this->post('estate_id', true);
		$afdeling_id     = $this->post('afdeling_id', true);
		$tgl_mulai =  $this->post('tgl_mulai', true);
		$tgl_akhir = $this->post('tgl_akhir', true);

		$retrieveEstate = $this->db->query("select * from gbm_organisasi where id=" . $estate_id . "")->row_array();

		$retrievePanen = $this->db->query("select * from est_produksi_panen_vw 
		where id_estate=" . $estate_id . " and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
		 order by tanggal")->result_array();

		// var_dump($results);
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		if ($format_laporan == 'view') {
			$html = get_header_report_v2();
		} else {
			$html = get_header_pdf_report();
		}
		$html = $html . '
<h2>Laporan Panen vs WB</h2>
<h3>Estate:' . $retrieveEstate['nama'] . ' </h3>
<h4>' . $tgl_mulai . ' - ' . $tgl_akhir . ' </h4>';

		$html = $html . ' <table  >
			<thead>
				<tr>
				<th width="4%">No.</th>			
				<th>Tanggal</th>
				<th>Afdeling</th>
				<th>Blok</th>
				<th>No Tiket Pabrik</th>
				<th style="text-align: right;">Janjang </th>
				<th style="text-align: right;">Brondolan(Kg) </th>
				<th style="text-align: right;">BJR Kebun</th>
				<th style="text-align: right;">Kg Kebun </th>
				<th style="text-align: right;">BJR Pabrik</th>
				<th style="text-align: right;">Kg Pabrik</th>
				<th style="text-align: right;">% </th>
				</tr>
			</thead>
			<tbody>';


		$no = 0;
		$total_janjang = 0;
		$total_brondolan = 0;
		$total_kg_kebun = 0;
		$total_kg_pabrik = 0;
		$avg_persen = 0;

		$total = 0;

		foreach ($retrievePanen as $key => $m) {
			$no++;
			$total_janjang = $total_janjang + $m['jum_janjang'];
			$total_brondolan = $total_brondolan + $m['jum_brondolan'];
			$total_kg_kebun = $total_kg_kebun + $m['kg_kebun'];
			$total_kg_pabrik = $total_kg_pabrik + $m['kg_pabrik'];
			$persen = ($m['kg_pabrik'] / $m['kg_kebun']) * 100;
			if ($avg_persen == 0) {
				$avg_persen = $persen;
			} else {
				$avg_persen = ($avg_persen +	$persen) / 2;
			}

			$html = $html . ' 	<tr class=":: arc-content">
						<td style="position:relative;">	' . ($no) . '</td>
						<td>' . $m['tanggal'] . ' </td>
						<td>
						' . $m['nama_afdeling'] . ' 
						
						</td>
						<td>
						' . $m['nama_blok'] . ' 
						
						</td>
						
						<td>
							' . $m['no_tiket'] . ' 
						</td>
						
						<td style="text-align: right;">' . number_format($m['jum_janjang']) . ' 
						<td style="text-align: right;">' . number_format($m['jum_brondolan']) . ' 
						<td style="text-align: right;">' . number_format($m['bjr_kebun']) . ' 
						<td style="text-align: right;">' . number_format($m['kg_kebun']) . ' 
						<td style="text-align: right;">' . number_format($m['bjr_pabrik']) . '
						<td style="text-align: right;">' . number_format($m['kg_pabrik']) . '  
						<td style="text-align: right;">' . number_format($persen) . ' 
										
						</td>';

			$html = $html . '</tr>';
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
						' . number_format($total_janjang) . ' 
						</td>
						<td style="text-align: right;">
						' . number_format($total_brondolan) . ' 
						</td>
						<td style="text-align: right;">
						
						</td>
						<td style="text-align: right;">
						' . number_format($total_kg_kebun) . ' 
						</td>
						<td style="text-align: right;">
						
						</td>
						<td style="text-align: right;">
						' . number_format($total_kg_pabrik) . ' 
						</td>
						<td style="text-align: right;">
						' . number_format($avg_persen) . ' 
						</td>
												
						</tr>
								</tbody>
							</table>
						';
		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		echo $html;
	}
	function get_logo_url($size = 'small')
	{
		return base_url('assets/images/logo-' . strtolower($size) . '.png');
	}

	/**
	 * Method untuk mendapatkan logo yang diatur
	 * @return string
	 */
	function get_logo_config()
	{
		$config = get_pengaturan('logo-company', 'value');
		if (empty($config)) {
			return get_logo_url('medium');
		} else {
			return get_url_image($config);
		}
	}


	function getLastNumber($table_name = '', $field = '', $prefix = '')
	{
		$lastnumber = $this->db->query("select  max(" . $field . ")as last from " . $table_name . "")->row_array();
		// var_dump($lastnumber);exit();
		if (!empty($lastnumber['last'])) {
			$str = (substr($lastnumber['last'], -6));
			$snumber = (int)$str + 1;
		} else {
			$snumber = 1;
		}
		$strnumber = sprintf("%06s", $snumber);
		return  $prefix . $strnumber;
		// $index = 11;
		// $prefix = 'B';
		// echo sprintf("%s%011s", $prefix, $index);


	}
	function print_slip_get($segment_3 = '')
	{

		$id = (int)$segment_3;
		$data = [];
		

		$queryHeader = "SELECT a.*,b.nama as afdeling ,b.kode  as kode
		FROM est_produksi_panen_ht a
		INNER JOIN gbm_organisasi b ON a.divisi_id=b.id   
		WHERE a.id=" . $id . "";
		$dataHeader = $this->db->query($queryHeader)->row_array();

		$queryDetail = "SELECT a.*, b.nama as blok, b.kode as kode
		FROM est_produksi_panen_dt a
		INNER JOIN gbm_organisasi b ON a.blok_id=b.id  
		WHERE a.produksi_panen_id=" . $id . "";
		$dataDetail = $this->db->query($queryDetail)->result_array();

		$data['header'] = 	$dataHeader;
		$data['detail'] = 	$dataDetail;

		// $user = $this->user_id;
		// if ($user) {
		// 	$retrieveProduk = $this->db->query("select * from fwk_users where id=" . $user . "")->row_array();
		// }
		// $data['dibuka']  = $retrieveProduk;

		$html = $this->load->view('EstSliPorduksiPanen', $data, true);

		// $filename = 'report_' . time();
		// $this->pdfgenerator->generate($html, $filename, true, 'A4', 'landscape');
		echo $html;
	}
}
