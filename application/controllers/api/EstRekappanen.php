<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

class EstRekappanen extends BD_Controller
{
	public $user_id;
	public $theCredential;

	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Jakarta");
		$this->load->model('GbmOrganisasiModel');
		$this->load->model('EstRekapPanenModel');
		$this->load->model('M_DatatablesModel');
		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id=$this->user_data->id;
	}
	public function list_post()
	{
		$post = $this->post();

		$query  = "SELECT a.*,b.nama as nama_divisi FROM est_rekap_panen_ht a 
		inner join  gbm_organisasi b on a.divisi_id=b.id";
		$search = array( 'a.tanggal', 'b.nama');
		$where  = null;

		$isWhere = null;
		// $isWhere = 'artikel.deleted_at IS NULL';

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
	
	function index_get($id = '')
	{
		$retrieve = $this->EstRekapPanenModel->retrieve_by_id($id);
		$retrieve['detail'] = $this->EstRekapPanenModel->retrieve_detail($id);


		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}


	function index_post()
	{
		$input = $this->post();
		$input['dibuat_oleh']=$this->user_id;
		//$input['no_rekap_panen'] = $this->getLastNumber('est_rekap_panen_ht', 'no_rekap_panen', 'rekap_panen');
		// var_dump($input);
		$res = $this->EstRekapPanenModel->create($input);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'est_rekap_panen', 'action' => 'new', 'entity_id' => $res);
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
		$data['diubah_oleh']=$this->user_id;
		$res = $this->EstRekapPanenModel->update($id, $data);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'est_rekap_panen', 'action' => 'edit', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	
	public function index_delete($id)
	{

		$res = $this->EstRekapPanenModel->delete($id);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id'=>$id)), 'entity' => 'est_rekap_panen', 'action' => 'delete', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}



	function laporan_rekap_panen_detail_post()
	{

		error_reporting(0);
		$estate_id     = $this->post('estate_id', true);
		$tgl_mulai =  $this->post('tgl_mulai', true);
		$tgl_akhir = $this->post('tgl_akhir', true);

		$retrieveEstate = $this->db->query("select * from gbm_organisasi where id=" . $estate_id . "")->row_array();

		$retrievePanen = $this->db->query("select * from est_rekap_panen_vw 
		where id_estate=" . $estate_id . " and tanggal>='" . $tgl_mulai . "' and tanggal<='" . $tgl_akhir . "'
		 order by tanggal")->result_array();

		// var_dump($results);
		$this->load->helper("terbilang");
		$this->load->helper("antech_helper");
		$this->load->library('pdfgenerator');
		$html = "<style type='text/css'>
		h3.title {
			margin-bottom: 0px;
			line-height: 30px;
		}
		hr.top {
			border: none;
			border-bottom: 2px solid #333;
			margin-bottom: 10px;
			margin-top: 10px;
		}
		.kop-print {
		  width: 700px;
		  margin: auto;
	  }
  
	  .kop-print img {
		  float: left;
		  height: 60px;
		  margin-right: 20px;
	  }
  
	  .kop-print .kop-info {
		  font-size: 15px;
	  }
  
	  .kop-print .kop-nama {
		  font-size: 25px;
		  font-weight: bold;
		  line-height: 35px;
	  }
  
	  .kop-print-hr {
		  border-width: 2px;
		  border-color: black;
		  margin-bottom: 0px;
	  }
	  table {
		border-collapse: separate;
		border-spacing: 0;
		color: #4a4a4d;
		font: 14px/1.4 'Helvetica Neue', Helvetica, Arial, sans-serif;
	  }
	  th,
	  td {
		padding: 10px 15px;
		vertical-align: middle;
	  }
	  thead {
		background: #395870;
		background: linear-gradient(#49708f, #293f50);
		color: #fff;
		font-size: 11px;
		text-transform: uppercase;
	  }
	  th:first-child {
		border-top-left-radius: 5px;
		text-align: left;
	  }
	  th:last-child {
		border-top-right-radius: 5px;
	  }
	  tbody tr:nth-child(even) {
		background: #f0f0f2;
	  }
	  td {
		border-bottom: 1px solid #cecfd5;
		border-right: 1px solid #cecfd5;
	  }
	  td:first-child {
		border-left: 1px solid #cecfd5;
	  }
	  .book-title {
		color: #395870;
		display: block;
	  }
	  .text-offset {
		color: #7c7c80;
		font-size: 12px;
	  }
	  .item-stock,
	  .item-qty {
		text-align: center;
	  }
	  .item-price {
		text-align: right;
	  }
	  .item-multiple {
		display: block;
	  }
	  tfoot {
		text-align: right;
	  }
	  tfoot tr:last-child {
		background: #f0f0f2;
		color: #395870;
		font-weight: bold;
	  }
	  tfoot tr:last-child td:first-child {
		border-bottom-left-radius: 5px;
	  }
	  tfoot tr:last-child td:last-child {
		border-bottom-right-radius: 5px;
	  }
	  
  
	</style>
  ";

		// 		$html = $html . '<div class="row">
		//   <div class="span12">
		// 	  <br>
		// 	  <div class="kop-print">
		// 		  <img src=data:image/png;base64,' . base64_encode(file_get_contents(get_logo_config())) . ' alt="image" >
		// 		  <div class="kop-nama">' . get_pengaturan('nama-company', 'value') . '</div>
		// 		  <div class="kop-info">Alamat : ' . get_pengaturan('alamat', 'value') . ', Telepon :' . get_pengaturan('telp', 'value') . '</div>
		// 	  </div>
		// 	  <hr class="kop-print-hr">
		//   </div>
		//   </div>
		//   <h2>Laporan Lembur</h2>
		//   <h3>estate:' . $retrieveEstate[0]['nama'] . ' </h3>
		//   <h4>' . $tgl_mulai . ' - ' . $tgl_akhir . ' </h4>';
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
			$total_brondolan =$total_brondolan+$m['jum_brondolan'];
			$total_kg_kebun = $total_kg_kebun + $m['kg_kebun'];
			$total_kg_pabrik = $total_kg_pabrik + $m['kg_pabrik'];
			$persen=($m['kg_pabrik']/$m['kg_kebun'])*100;
			if ($avg_persen==0){
				$avg_persen=$persen;
			}else{
				$avg_persen=($avg_persen+	$persen)/2;
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
	

}
