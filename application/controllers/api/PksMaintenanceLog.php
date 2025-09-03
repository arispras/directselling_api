<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

class PksMaintenanceLog extends BD_Controller
{
	public $user_id;
	public $theCredential;

	function __construct()
	{
		parent::__construct();
		$this->load->model('PksMaintenanceLogModel');
		$this->load->model('M_DatatablesModel');
		$this->auth();
		$this->theCredential = $this->user_data;
		$this->user_id = $this->user_data->id;
	}

	// endpoint/list :POST
	public function list_post()
	{
		$post = $this->post();
		$query  = "SELECT 
		a.*,
		b.nama AS nama_mesin,
        c.keterangan as keterangan
		FROM pks_maintenance_log a 
		INNER JOIN gbm_organisasi b ON a.mesin_id=b.id
        INNER JOIN pks_jenis_maintenance c ON a.jenis_maintenance_id=c.id
		";

		$search = array('b.nama', 'c.keterangan', 'ket');
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
		$retrieve = $this->PksMaintenanceLogModel->retrieve($id);
		$retrieve['detail'] = $this->PksMaintenanceLogModel->retrieve_detail($id);

		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	//  endpoint/getall :GET
	function getAll_get()
	{
		$retrieve = $this->PksMaintenanceLogModel->retrieve_all_kategori();
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
		$input['dibuat_oleh'] = $this->user_id;
		$input['diubah_oleh'] = $this->user_id;
		$res = $this->PksMaintenanceLogModel->create($input);
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->post()), 'entity' => 'pks_maintenance_log', 'action' => 'new', 'entity_id' => $res);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $input['no_transaksi']), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	// endpoint/ :PUT
	function index_put($segment_3 = '')
	{
		$id = (int)$segment_3;
		$res = $this->PksMaintenanceLogModel->retrieve($id);
		if (empty($res)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
		$input = $this->put();
		$input['diubah_oleh'] = $this->user_id;
		$res = $this->PksMaintenanceLogModel->update($res['id'], $input);
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode($this->put()), 'entity' => 'pks_maintenance_log', 'action' => 'edit', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}

	// endpoint/ :DELETE
	function index_delete($segment_3 = '')
	{
		$id = (int)$segment_3;
		$gudang = $this->PksMaintenanceLogModel->retrieve($id);
		if (empty($gudang)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
		$res =  $this->PksMaintenanceLogModel->delete($gudang['id']);
		// $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		if (!empty($res)) {
			/* start audit trail */
			$audit = array('user_id' => $this->user_id, 'desc' => json_encode(array('id' => $id)), 'entity' => 'pks_maintenance_log', 'action' => 'delete', 'entity_id' => $id);
			$this->db->insert('fwk_user_audit', $audit);
			/* end audit trail */
			$this->set_response(array("status" => "OK", "data" => $res), REST_Controller::HTTP_CREATED);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
	}
	function laporan_status_service_rutin_post()
	{
		$data_result = array();
		$data_result = [];
		$sql = "SELECT a.*,b.kode AS kode_mesin,b.nama AS nama_mesin,c.kode AS kode_maintenance,c.keterangan AS nama_maintenance
				 from pks_maintenance_mesin a inner join gbm_organisasi b ON a.mesin_id=b.id
				INNER JOIN pks_jenis_maintenance c ON a.jenis_mesin_id=c.id
				ORDER BY b.nama";
		$res = $this->db->query($sql)->result_array();
		foreach ($res as $key => $m) {
			$sql = "SELECT * FROM pks_maintenance_log 
					WHERE mesin_id=" . $m['mesin_id'] . " 
					ORDER BY tanggal desc LIMIT 1";
			$res_maintenance = $this->db->query($sql)->row_array();
			$last_service_kmhm = 0;
			$last_kmhm = 0;
			$kmhm_rutin_service = 0;
			if ($res_maintenance) {
				$last_kmhm = $res_maintenance['hm_km'];
			}
			$sql = "SELECT * FROM pks_maintenance_log 
			WHERE mesin_id=" . $m['mesin_id'] . " AND jenis_maintenance_id=" . $m['jenis_mesin_id'] . "
			ORDER BY tanggal desc LIMIT 1";
			$res_maintenance_rutin = $this->db->query($sql)->row_array();
			if ($res_maintenance_rutin) {
				$last_service_kmhm = $res_maintenance_rutin['hm_km'];
			}

			$kmhm_rutin_service = 0;
			$mesin_log = array();
			if ($last_kmhm == 0) { // Jika blm prnh di input di log
				$mesin_log = array("mesin" => $m, 'status' => 'OK', 'last_hmkm' => 0, 'last_service_hmkm' => 0, 'kmhm_lewat_service' => 0,'kmhm_rutin_service' => $m['hm_km_maintenance']);
			} else {
				if ($last_service_kmhm == 0) { // Jika blm prnh di service
					$kmhm_rutin_service = $m['hm_km'] + $m['hm_km_maintenance']; // start km ditambah kmhm rutin maintenace
				} else {
					$kmhm_rutin_service = $m['hm_km_maintenance']; // hmkm rutin maintenace
				}
				$selisih_hmkm = $last_kmhm -( $last_service_kmhm+$kmhm_rutin_service);
				if ($selisih_hmkm >= 0) {
					$mesin_log = array("mesin" => $m, 'status' => 'NOT OK', 'last_hmkm' => $last_kmhm, 'last_service_hmkm' => $last_service_kmhm, 'kmhm_lewat_service' => $selisih_hmkm,'kmhm_rutin_service' => $m['hm_km_maintenance']);
					
				} else {
					$mesin_log = array("mesin" => $m, 'status' => 'OK', 'last_hmkm' => $last_kmhm, 'last_service_hmkm' => $last_service_kmhm, 'kmhm_lewat_service' => $selisih_hmkm,'kmhm_rutin_service' => $m['hm_km_maintenance']);
				}
			}
			$data_result[]=$mesin_log;
			
		}
		$data['data_result']=$data_result;
		$html = $this->load->view('PksMaintenanceStatusRutin_laporan', $data, true);

		$filename = 'report_' . time();
		
		 echo $html;
		//var_dump($data_result);
	}
	function laporan_detail_maintenance_post()
	{
		$t1=$this->post('tgl_mulai');
		$t2=$this->post('tgl_akhir');
		$data_result = array();
		$data_result = [];
		$sql = "SELECT a.*,b.kode AS kode_mesin,b.nama AS nama_mesin,c.kode AS kode_maintenance,c.keterangan AS nama_maintenance
		from pks_maintenance_log a inner join gbm_organisasi b ON a.mesin_id=b.id
	   INNER JOIN pks_jenis_maintenance c ON a.jenis_maintenance_id=c.id
	   where a.tanggal between '".$t1."' and '".$t2."'
	   ORDER BY a.mesin_id,a.tanggal;";
		$res = $this->db->query($sql)->result_array();
		
		$data['data_result']=$res;
		$html = $this->load->view('PksMaintenanceDetail_laporan', $data, true);

		$filename = 'report_' . time();
		
		 echo $html;
		//var_dump($data_result);
	}
}
