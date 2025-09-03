<?php

class PksVsjtModel extends CI_Model
{



    public function retrieve_all(
        $no_of_records = 10,
        $page_no       = 1
    ) {
        $no_of_records = (int)$no_of_records;
        $page_no       = (int)$page_no;

        $where = array();

        $data = $this->pager->set('pks_vsjt', $no_of_records, $page_no, $where);

        return $data;
    }

   
    public function retrieve_all_kategori()
    {
        // $this->db->where('aktif' , 1);
        $this->db->order_by('id', 'ASC');
        $result = $this->db->get('pks_vsjt');
        return $result->result_array();
    }

   
    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('pks_vsjt');
        return true;
    }


    public function update($id, $input)
    {
        $id = (int)$id;

        $input['diubah_tanggal'] = date('Y-m-d H:i:s');
        $input['sjpp_id'] = $input['sjpp_id']['id'];
        
        $this->db->where('id', $id);
        $this->db->update('pks_vsjt', $input);
        return true;
    }

   
    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('pks_vsjt', 1);
        return $result->row_array();
    }

    
    public function create($input)
    {
        $input['diubah_tanggal'] = date('Y-m-d H:i:s');
        $input['sjpp_id'] = $input['sjpp_id']['id'];

        $this->db->insert('pks_vsjt', $input);
        return $this->db->insert_id();
    }


    public function print_slip(
		$id = null
	) {
		$query = "SELECT 
            c.nama AS nama_produk,
            c.kode AS kode_produk,
            c.*,
            b.*,
            a.*
            -- c.kode,
            -- c.nama,
            -- c.satuan
		FROM pks_vsjt a 
        inner join pks_tanki b on a.tanki_id = b.id
        inner join inv_item c on a.produk_id = c.id
	    -- inner join inv_item c on b.item=c.id
	    -- inner join inv_gudang d on a.gudang_id=d.id
		
        where 1=1 and a.id=" . $id . ";";
		$data = $this->db->query($query)->row_array();
		return $data;
	}
}
