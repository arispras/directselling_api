<?php

class PrcSyaratBayarModel extends CI_Model
{



    public function retrieve_all(
        $no_of_records = 10,
        $page_no       = 1
    ) {
        $no_of_records = (int)$no_of_records;
        $page_no       = (int)$page_no;

        $where = array();

        $data = $this->pager->set('prc_syarat_bayar', $no_of_records, $page_no, $where);

        return $data;
    }

   
    public function retrieve_all_kategori()
    {
        // $this->db->where('aktif' , 1);
        $this->db->order_by('id', 'ASC');
        $result = $this->db->get('prc_syarat_bayar');
        return $result->result_array();
    }

   
    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('prc_syarat_bayar');
        return true;
    }


    public function update($id, $input)
    {
        $id = (int)$id;

        // $input['diubah_tanggal'] = date('Y-m-d H:i:s');
        // $input['tanki_id'] = $input['tanki_id']['id'];
        // $input['simbol'] = $input['simbol']['id'];
        // $input['lokasi_id'] = $input['lokasi_id']['id'];
        $input['jenis'] = $input['jenis_id']['id'];
        unset($input['jenis_id']);
        // $input['karyawan_id'] = $input['karyawan_id']['id'];

        $this->db->where('id', $id);
        $this->db->update('prc_syarat_bayar', $input);
        return true;
    }

   
    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('prc_syarat_bayar', 1);
        return $result->row_array();
    }

    
    public function create($input)
    {
        // $input['diubah_tanggal'] = date('Y-m-d H:i:s');
        // $input['tanki_id'] = $input['tanki_id']['id'];
        // $input['lokasi_id'] = $input['lokasi_id']['id'];
        $input['jenis'] = $input['jenis_id']['id'];
        unset($input['jenis_id']);
        // $input['karyawan_id'] = $input['karyawan_id']['id'];

        $this->db->insert('prc_syarat_bayar', $input);
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
		FROM prc_syarat_bayar a 
        inner join pks_tanki b on a.tanki_id = b.id
        inner join inv_item c on a.produk_id = c.id
	    -- inner join inv_item c on b.item=c.id
	    -- inner join inv_gudang d on a.gudang_id=d.id
		
        where 1=1 and a.id=" . $id . ";";
		$data = $this->db->query($query)->row_array();
		return $data;
	}
}
