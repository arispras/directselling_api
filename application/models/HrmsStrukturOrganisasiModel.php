<?php


class HrmsStrukturOrganisasiModel extends CI_Model
{

	public function retrieve_all($parent_id = null, $array_where = array())
	{
		$this->db->where('parent_id', $parent_id);

		foreach ($array_where as $key => $value) {
			$this->db->where($key, $value);
		}

		$this->db->order_by('sort_order', 'ASC');
		$result = $this->db->get('hrms_struktur_organisasi');
		return $result->result_array();
	}

	
	public function retrieve_all_parent()
	{
		$this->db->where('is_child =', 0);
		$this->db->order_by('id', 'ASC');
		$result = $this->db->get('hrms_struktur_organisasi');
		return $result->result_array();
	}


	public function retrieve($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);

		$result = $this->db->get('hrms_struktur_organisasi', '1');
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
		$karyawan_id

	) {
		if (!is_null($parent_id)) {
			$parent_id = (int)$parent_id;
		}
		if (!is_null($sort_order)) {
			$sort_order = (int)$sort_order;
		} else {
			$this->db->select('MAX(sort_order) AS sort_order');
			$query = $this->db->get('hrms_struktur_organisasi');
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
			'karyawan_id' => $karyawan_id

		);
		$this->db->insert('hrms_struktur_organisasi', $data);
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
		$karyawan_id
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
			'karyawan_id' => $karyawan_id

		);
		$this->db->where('id', $id);
		$this->db->update('hrms_struktur_organisasi', $data);
		return true;
	}


	public function delete($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$this->db->delete('hrms_struktur_organisasi');
		return true;
	}
}
