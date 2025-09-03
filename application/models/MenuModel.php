<?php


class MenuModel extends CI_Model
{
   
    public function retrieve_all($parent_id = null, $array_where = array()) {
        $this->db->where('parent_id', $parent_id);

        foreach ($array_where as $key => $value) {
            $this->db->where($key, $value);
        }

        $this->db->order_by('sort_order', 'ASC');
        $result = $this->db->get('fwk_menu');
        return $result->result_array();
    }

    public function retrieve_all_child()
    {
        $this->db->where('parent_id !=', '0');
      
        $this->db->order_by('sort_order', 'ASC');
        $result = $this->db->get('fwk_menu');
        return $result->result_array();
    }
	public function retrieve_all_parent()
    {
        $this->db->where('is_child =', 0);
        $this->db->order_by('id', 'ASC');
        $result = $this->db->get('fwk_menu');
        return $result->result_array();
    }

    
    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        
        $result = $this->db->get('fwk_menu', '1');
        return $result->row_array();
    }
	public function retrieve_by_name($nama)
    {
      

        $this->db->where('name', $nama);
        $result = $this->db->get('fwk_menu', '1');
        return $result->row_array();
    }

   
    public function create(
        $name,
       	$text,
		$is_child,
		$parent_id = 0,
		$url,
		$icon,
		$sort_order

    ) {
		if (!is_null($parent_id)) {
            $parent_id = (int)$parent_id;
        }
		if (!is_null($sort_order)) {
            $sort_order = (int)$sort_order;
        }else{
			$this->db->select('MAX(sort_order) AS sort_order');
			$query = $this->db->get('fwk_menu');
			$row   = $query->row_array();
			if (empty($row['sort_order'])) {
				$row['sort_order'] = 1;
			} else {
				$row['sort_order'] = $row['sort_order'] + 1;
			}
			$sort_order=$row['sort_order'];

		}
       
        $data = array(
            'name'      => $name,
            'parent_id' => $parent_id,
            'sort_order'    => $sort_order,
			'is_child'=> $is_child,
			'text'=> $text,
			'url'=> $url,
			'icon'=> $icon,

        );
        $this->db->insert('fwk_menu', $data);
        return $this->db->insert_id();
    }

   
    public function update(
        $id,
        $name,
       	$text,
		$is_child,
		$parent_id = 0,
		$url,
		$icon,
		$sort_order
    ) {
        $id     = (int)$id;
        $sort_order = (int)$sort_order;
       
        if (!is_null($parent_id)) {
            $parent_id = (int)$parent_id;
        }

		$data = array(
            'name'      => $name,
            'parent_id' => $parent_id,
            'sort_order'    => $sort_order,
			'is_child'=> $is_child,
			'text'=> $text,
			'url'=> $url,
			'icon'=> $icon

        );
        $this->db->where('id', $id);
        $this->db->update('fwk_menu', $data);
        return true;
    }

 
    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('fwk_menu');
        return true;
    }

    
}
