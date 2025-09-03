<?php

class HrmsApprovallSettingPengajuanCutiModel extends CI_Model
{



   
    public function retrieve_all_kategori()
    {
        // $this->db->where('aktif' , 1);
        $this->db->order_by('id', 'ASC');
        $result = $this->db->get('hrms_approvall_setting_pengajuan_cuti');
        return $result->result_array();
    }
	public function retrieve_by_lokasi_and_kode($lokasi_id,$kode)
    {
        $this->db->select('*' );
		$this->db->from('hrms_approvall_setting_pengajuan_cuti' );
		$this->db->where('lokasi_id' ,$lokasi_id);
		$this->db->where('kode' ,$kode);
         $result = $this->db->get();
        return $result->row_array();
    }
	public function retrieve_by_lokasi_kode_karyawan($lokasi_id,$kode,$karyawan_id)
    {
        $this->db->select('*' );
		$this->db->from('hrms_approvall_setting_pengajuan_cuti' );
		$this->db->where('lokasi_id' ,$lokasi_id);
		$this->db->where('kode' ,$kode);
		$this->db->where('karyawan_id' ,$karyawan_id);
         $result = $this->db->get();
        return $result->row_array();
    }


   
    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('hrms_approvall_setting_pengajuan_cuti');
        return true;
    }


    public function update($id, $input)
    {
        $id = (int)$id;

        $input['lokasi_id'] = $input['lokasi_id']['id'];
        $input['kode'] = $input['kode_id']['id'];
        unset($input['kode_id']);
        $input['karyawan_id'] = $input['karyawan_id']['id'];

        $this->db->where('id', $id);
        $this->db->update('hrms_approvall_setting_pengajuan_cuti', $input);
        return true;
    }

   
    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('hrms_approvall_setting_pengajuan_cuti', 1);
        return $result->row_array();
    }

    
    public function create($input)
    {

        $input['lokasi_id'] = $input['lokasi_id']['id'];
        $input['kode'] = $input['kode_id']['id'];
        unset($input['kode_id']);
        $input['karyawan_id'] = $input['karyawan_id']['id'];
        $this->db->insert('hrms_approvall_setting_pengajuan_cuti', $input);
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
		FROM hrms_approvall_setting_pengajuan_cuti a 
        inner join pks_tanki b on a.tanki_id = b.id
        inner join inv_item c on a.produk_id = c.id
	    -- inner join inv_item c on b.item=c.id
	    -- inner join inv_gudang d on a.gudang_id=d.id
		
        where 1=1 and a.id=" . $id . ";";
		$data = $this->db->query($query)->row_array();
		return $data;
	}
}
