<?php


class GbmOrganisasiModel extends CI_Model
{

	public function retrieve_all($parent_id = null, $array_where = array())
	{
		$this->db->where('parent_id', $parent_id);

		foreach ($array_where as $key => $value) {
			$this->db->where($key, $value);
		}

		$this->db->order_by('sort_order', 'ASC');
		$result = $this->db->get('gbm_organisasi');
		return $result->result_array();
	}

	public function retrieve_all_bytipe($tipe)
	{
		
		$this->db->select('a.*,b.nama as nama_parent,b.kode as kode_parent');
		$this->db->from('gbm_organisasi a');
		$this->db->join('gbm_organisasi b', 'a.parent_id=b.id');
		if ($tipe == 'UNIT') {
			$this->db->where_in('a.tipe', ['ESTATE', 'MILL', 'HO', 'RO']);
		} else if ($tipe == 'AFDELING_STASIUN') {
			$this->db->where_in('a.tipe', ['AFDELING', 'STASIUN']);
		} else if ($tipe == 'AFDELING') {
			$this->db->where_in('a.tipe', ['AFDELING']);
		} else if ($tipe == 'AFDELING_STASIUN_WORKSHOP') {
			$this->db->where_in('a.tipe', ['AFDELING', 'STASIUN','WORKSHOP']);
		} else if ($tipe == 'SUBBAGIAN') {
			$this->db->where_in('a.tipe', ['AFDELING', 'STASIUN', 'DIVISI','TRAKSI','UMUM','WORKSHOP']);
		} else if ($tipe == 'BLOK_MESIN') {
			$this->db->where_in('a.tipe', ['BLOK', 'MESIN']);
		} else if ($tipe == 'GUDANG') {
			$this->db->where_in('a.tipe', ['GUDANG']);
		} else if ($tipe == 'ALL_GUDANG') {
			$this->db->where_in('a.tipe', ['GUDANG', 'GUDANG_VIRTUAL']);
		} else if ($tipe == 'TRAKSI') {
			$this->db->where_in('a.tipe', ['TRAKSI']);
		} else {
			$this->db->where('a.tipe', $tipe);
		}
		$this->db->order_by('a.nama', 'ASC');
		$result = $this->db->get();
		return $result->result_array();
	}
	public function retrieve_blok_by_afdeling($parent_id)
	{

		$this->db->where('parent_id', $parent_id);
		$this->db->where_in('tipe', ['BLOK', 'MESIN']);

		$this->db->order_by('nama', 'ASC');
		$result = $this->db->get('gbm_organisasi');
		return $result->result_array();
	}


	public function retrieve_mesin_by_stasiun($parent_id)
	{

		$this->db->where('parent_id', $parent_id);
		$this->db->where_in('tipe', ['MESIN', 'BLOK']);

		$this->db->order_by('nama', 'ASC');
		$result = $this->db->get('gbm_organisasi');
		return $result->result_array();
	}
	public function retrieve_blok_by_rayon($parent_id)
	{
		$this->db->select("a.*");

		$this->db->from("gbm_organisasi a");
		$this->db->where("a.tipe", "BLOK");

		$this->db->join("gbm_organisasi b", "b.id = a.parent_id");
		$this->db->where("b.tipe", "AFDELING");
		$this->db->where("b.parent_id", $parent_id);

		$result = $this->db->get();
		return $result->result_array();
	}
	public function retrieve_blok_by_estate($parent_id)
	{
		$this->db->select(" a.*,b.nama as nama_parent,b.kode as kode_parent");
		$this->db->from("gbm_organisasi a");
		$this->db->join("gbm_organisasi b", "b.id = a.parent_id");
		$this->db->join("gbm_organisasi c", "c.id = b.parent_id");
		$this->db->where("c.parent_id", $parent_id);

		$result = $this->db->get();
		return $result->result_array();
	}
	public function retrieve_mesin_by_mill($parent_id)
	{
		$this->db->select("a.*,b.nama as nama_parent,b.kode as kode_parent");
		$this->db->from("gbm_organisasi a");
		$this->db->join("gbm_organisasi b", "b.id = a.parent_id");
		$this->db->where("b.parent_id", $parent_id);
		
		$result = $this->db->get();
		return $result->result_array();
	}

	public function retrieve_mesinblok_by_millestate($parent_id)
	{	
		if ($parent_id==260) {
		$this->db->select("a.*,b.nama as nama_parent,b.kode as kode_parent,b.nama as nama_parent");
		$this->db->from("gbm_organisasi a");
		$this->db->join("gbm_organisasi b", "b.id = a.parent_id");
		$this->db->where("b.parent_id", $parent_id);
		$result = $this->db->get();
		return $result->result_array();
		}

		elseif ($parent_id==265||$parent_id==252) {
		$this->db->select(" a.*,b.nama as nama_parent,b.kode as kode_parent,b.nama as nama_parent");
		$this->db->from("gbm_organisasi a");
		$this->db->join("gbm_organisasi b", "b.id = a.parent_id");
		$this->db->join("gbm_organisasi c", "c.id = b.parent_id");
		$this->db->where("c.parent_id", $parent_id);
		$result = $this->db->get();
		return $result->result_array();
		}
		
	}


	public function retrieve_sub_by_Id($parent_id)
	{

		$lokasi = $this->db->query("SELECT * FROM gbm_organisasi WHERE id=" . $parent_id)->row_array();
		// if ($lokasi['tipe'] == 'ESTATE') {
		// 	$this->db->select("a.*");

		// 	$this->db->from("gbm_organisasi a");
		// 	$this->db->where("a.tipe", "AFDELING");

		// 	$this->db->join("gbm_organisasi b", "b.id = a.parent_id");
		// 	$this->db->where("b.tipe", "RAYON");
		// 	$this->db->where("b.parent_id", $parent_id);

		// 	$result = $this->db->get()->result_array();
		// } else {
		// 	$this->db->from('gbm_organisasi');
		// 	$this->db->where('parent_id', $parent_id);

		// 	$this->db->order_by('nama', 'ASC');
		// 	$result = $this->db->get()->result_array();
		// }

		// return $result;
		$res = $this->db->query("select * from gbm_organisasi where id=" . $parent_id . "")->row_array();
		if ($res['tipe'] == "ESTATE") {
			$resr_data = $this->db->query("select c.* from gbm_organisasi a inner join gbm_organisasi b on a.id=b.parent_id
		inner join gbm_organisasi c on b.id=c.parent_id
		 where a.id=" . $parent_id . " and c.tipe in('AFDELING','UMUM')
		 ")->result_array();
		} else if ($res['tipe'] == "MILL") {
			$resr_data = $this->db->query("select b.* from gbm_organisasi a inner join gbm_organisasi b on a.id=b.parent_id
		where a.id=" . $parent_id . "
		and b.tipe in('STASIUN','UMUM') ")->result_array();
		} else if ($res['tipe'] == "HO") {
			$resr_data = $this->db->query("select b.* from gbm_organisasi a inner join gbm_organisasi b on a.id=b.parent_id
		where a.id=" . $parent_id . "
		and b.tipe in('DIVISI','UMUM','DEPARTEMEN') ")->result_array();
		} else if ($res['tipe'] == "RO") {
			$resr_data = $this->db->query("select b.* from gbm_organisasi a inner join gbm_organisasi b on a.id=b.parent_id
		where a.id=" . $parent_id . "
		and b.tipe in('DIVISI','UMUM','DEPARTEMEN') ")->result_array();
		}

		if ($res['tipe'] == "ESTATE" || $res['tipe'] == "MILL") {
			$resr_workshop = $this->db->query("select b.* from gbm_organisasi a inner join gbm_organisasi b on a.id=b.parent_id
			where a.id=" . $parent_id . "
			and b.tipe in('WORKSHOP') ")->row_array();
			if ($resr_workshop) {
				$resr_data[] =	$resr_workshop;
			}
			$resr_traksi = $this->db->query("select b.* from gbm_organisasi a inner join gbm_organisasi b on a.id=b.parent_id
			where a.id=" . $parent_id . "
			and b.tipe in('TRAKSI') ")->row_array();
			if ($resr_traksi) {
				$resr_data[] =	$resr_traksi;
			}
		}
		return $resr_data;
	}
	public function retrieve_afdeling_by_estate($parent_id)
	{
		$this->db->select("a.*");

		$this->db->from("gbm_organisasi a");
		$this->db->where("a.tipe", "AFDELING");

		$this->db->join("gbm_organisasi b", "b.id = a.parent_id");
		$this->db->where("b.tipe", "RAYON");
		$this->db->where("b.parent_id", $parent_id);

		$result = $this->db->get();
		return $result->result_array();
	}
	public function retrieve_afdeling_by_estate_and_user($parent_id,$user_id)
	{
		$res=array();

		/* Cari Afdeling */
		$sql1 = " select a.* from gbm_organisasi a inner join 
		gbm_organisasi b on a.parent_id=b.id
		where b.parent_id=". $parent_id ." and a.id in
			(select afdeling_id from fwk_users_afdeling where user_id=" . $user_id . ")
			and a.tipe in('AFDELING' ,'STASIUN' ,'TRAKSI' ,'WORKSHOP','DIVISI','UMUM')
		";

		
		$res1=$this->db->query($sql1)->result_array();
		foreach ($res1 as $key => $value) {
			$res[]= $value;
		}

		/* Cari Stasiun,Traksi,Workshop */
		$sql2 = " select a.* from gbm_organisasi a inner join 
		gbm_organisasi b on a.parent_id=b.id
		where b.id=". $parent_id ." and a.id in
			(select afdeling_id from fwk_users_afdeling where user_id=" . $user_id . ")
			and a.tipe in('AFDELING' ,'STASIUN' ,'TRAKSI' ,'WORKSHOP','DIVISI')
		";
		$res1=$this->db->query($sql2)->result_array();
		foreach ($res1 as $key => $value) {
			$res[]= $value;
		}
		return	$res;
	
	}
	public function retrieve_gudang_central_and_virtual_by_unit($parent_id)
	{
		$gudang = array();
		$this->db->select("*");
		$this->db->from("gbm_organisasi  ");
		$this->db->where_in('tipe', ['GUDANG']);
		$this->db->where("parent_id", $parent_id);
		$result1 = $this->db->get()->result_array();

		foreach ($result1 as $key => $value) {
			$gudang[] = $value;
			$this->db->select("*");
			$this->db->from("gbm_organisasi  ");
			$this->db->where_in('tipe', ['GUDANG_VIRTUAL']);
			$this->db->where("parent_id", $value['id']);
			$result2 = $this->db->get()->result_array();
			foreach ($result2 as $key2 => $value2) {
				$gudang[] = $value2;
			}
		}

		return $gudang;
	}
	public function retrieve_all_gudang_central_and_virtual()
	{
		$gudang = array();
		$this->db->select("*");
		$this->db->from("gbm_organisasi  ");
		$this->db->where_in('tipe', ['GUDANG']);
		$result1 = $this->db->get()->result_array();

		foreach ($result1 as $key => $value) {
			$gudang[] = $value;
			$this->db->select("*");
			$this->db->from("gbm_organisasi  ");
			$this->db->where_in('tipe', ['GUDANG_VIRTUAL']);
			$this->db->where("parent_id", $value['id']);
			$result2 = $this->db->get()->result_array();
			foreach ($result2 as $key2 => $value2) {
				$gudang[] = $value2;
			}
		}

		return $gudang;
	}
	public function retrieve_gudang_by_unit($parent_id)
	{
		$this->db->select("*");

		$this->db->from("gbm_organisasi  ");
		// $this->db->where("a.tipe", "GUDANG");

		// $this->db->join("gbm_organisasi b", "a.id = b.parent_id");
		$this->db->where_in('tipe', ['GUDANG']);
		$this->db->where("parent_id", $parent_id);

		$result = $this->db->get();
		return $result->result_array();
	}

	public function retrieve_traksi_by_unit($parent_id)
	{
		$this->db->select("*");

		$this->db->from("gbm_organisasi");

		$this->db->where_in('tipe', ['TRAKSI']);
		$this->db->where("parent_id", $parent_id);

		$result = $this->db->get();
		return $result->result_array();
	}
	public function retrieve_workshop_by_unit($parent_id)
	{
		$this->db->select("*");

		$this->db->from("gbm_organisasi");

		$this->db->where_in('tipe', ['WORKSHOP']);
		$this->db->where("parent_id", $parent_id);

		$result = $this->db->get();
		return $result->result_array();
	}

	public function retrieve_afdst_by_unit($parent_id)
	{

		$res = $this->db->query("select * from gbm_organisasi where id=" . $parent_id . "")->row_array();
		if ($res['tipe'] == "ESTATE") {
			$resr_data = $this->db->query("select c.* from gbm_organisasi a inner join gbm_organisasi b on a.id=b.parent_id
		inner join gbm_organisasi c on b.id=c.parent_id
		 where a.id=" . $parent_id . " and c.tipe in('AFDELING','UMUM')
		 ")->result_array();
		} else if ($res['tipe'] == "MILL") {
			$resr_data = $this->db->query("select b.* from gbm_organisasi a inner join gbm_organisasi b on a.id=b.parent_id
		where a.id=" . $parent_id . "
		and b.tipe in('STASIUN','UMUM') ")->result_array();
		} else if ($res['tipe'] == "HO") {
			$resr_data = $this->db->query("select b.* from gbm_organisasi a inner join gbm_organisasi b on a.id=b.parent_id
		where a.id=" . $parent_id . "
		and b.tipe in('DIVISI','UMUM','DEPARTEMEN') ")->result_array();
		} else if ($res['tipe'] == "RO") {
			$resr_data = $this->db->query("select b.* from gbm_organisasi a inner join gbm_organisasi b on a.id=b.parent_id
		where a.id=" . $parent_id . "
		and b.tipe in('DIVISI','UMUM','DEPARTEMEN') ")->result_array();
		}

		if ($res['tipe'] == "ESTATE" || $res['tipe'] == "MILL") {
			$resr_workshop = $this->db->query("select b.* from gbm_organisasi a inner join gbm_organisasi b on a.id=b.parent_id
			where a.id=" . $parent_id . "
			and b.tipe in('WORKSHOP') ")->row_array();
			if ($resr_workshop) {
				$resr_data[] =	$resr_workshop;
			}
		}
		return $resr_data;
	}
	public function retrieve_all_divisi()
	{
		$resr_data = $this->db->query("select * from gbm_organisasi
			where tipe in('DIVISI','UMUM','DEPARTEMEN','AFDELING','STATION','WORKSHOP','TRAKSI') ")->result_array();
			return $resr_data;
	}
	public function getAllAdmUnit()
	{
		$this->db->where_in('tipe', ['ESTATE', 'MILL', 'HO', 'GO', 'RO']);

		$this->db->order_by('nama', 'ASC');
		$result = $this->db->get('gbm_organisasi');
		return $result->result_array();
	}
	public function getAllAdmUnitByAccess($user_id)
	{
		$sql = " select * from gbm_organisasi where id in
			(select location_id from fwk_users_location where user_id=" . $user_id . ")
		  and tipe in ('ESTATE', 'MILL', 'HO', 'GO', 'RO') ";
		return	$this->db->query($sql)->result_array();
	}
	public function retrieve_all_child()
	{
		$this->db->where('parent_id !=', '0');

		$this->db->order_by('sort_order', 'ASC');
		$result = $this->db->get('gbm_organisasi');
		return $result->result_array();
	}
	public function retrieve_child_gudang($gudang_id)
	{
		$this->db->where('parent_id', $gudang_id);
		$this->db->where('tipe', 'GUDANG_VIRTUAL');
		$this->db->order_by('kode', 'ASC');
		$result = $this->db->get('gbm_organisasi');
		return $result->result_array();
	}
	public function retrieve_all_parent()
	{
		$this->db->where('is_child =', 0);
		$this->db->order_by('id', 'ASC');
		$result = $this->db->get('gbm_organisasi');
		return $result->result_array();
	}


	public function retrieve($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);

		$result = $this->db->get('gbm_organisasi', '1');
		return $result->row_array();
	}
	public function retrieve_by_name($nama)
	{


		$this->db->where('nama', $nama);
		$result = $this->db->get('menu', '1');
		return $result->row_array();
	}


	public function create(
		$name,
		$text,
		$is_child,
		$parent_id = 0,
		$tipe,
		$sort_order,
		$afdeling_id

	) {
		if (!is_null($parent_id)) {
			$parent_id = (int)$parent_id;
		}
		if (!is_null($sort_order)) {
			$sort_order = (int)$sort_order;
		} else {
			$this->db->select('MAX(sort_order) AS sort_order');
			$query = $this->db->get('gbm_organisasi');
			$row   = $query->row_array();
			if (empty($row['sort_order'])) {
				$row['sort_order'] = 1;
			} else {
				$row['sort_order'] = $row['sort_order'] + 1;
			}
			$sort_order = $row['sort_order'];
		}

		$data = array(
			'kode'      => $name,
			'parent_id' => $parent_id,
			'sort_order'    => $sort_order,
			'is_child' => $is_child,
			'nama' => $text,
			'tipe' => $tipe,
			'afdeling_id' => $afdeling_id

		);
		$this->db->insert('gbm_organisasi', $data);
		return $this->db->insert_id();
	}


	public function update(
		$id,
		$name,
		$text,
		$is_child,
		$parent_id = 0,
		$tipe,
		$sort_order,
		$afdeling_id
	) {
		$id     = (int)$id;
		$sort_order = (int)$sort_order;

		if (!is_null($parent_id)) {
			$parent_id = (int)$parent_id;
		}

		$data = array(
			'kode'      => $name,
			'parent_id' => $parent_id,
			'sort_order'    => $sort_order,
			'is_child' => $is_child,
			'nama' => $text,
			'tipe' => $tipe,
			'afdeling_id' => $afdeling_id

		);
		$this->db->where('id', $id);
		$this->db->update('gbm_organisasi', $data);
		return true;
	}


	public function delete($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$this->db->delete('gbm_organisasi');
		return true;
	}
}
