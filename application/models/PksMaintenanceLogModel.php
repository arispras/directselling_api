<?php


class PksMaintenanceLogModel extends CI_Model
{

    public function retrieve_all(
        $no_of_records = 10,
        $page_no       = 1
    ) {
        $no_of_records = (int)$no_of_records;
        $page_no       = (int)$page_no;

        $where = array();

        $data = $this->pager->set('pks_maintenance_log', $no_of_records, $page_no, $where);

        return $data;
    }


    //  Method untuk mendapatkan semua data pks_maintenance_log tanpa pagging

    public function retrieve_all_kategori()
    {
        $this->db->order_by('mesin_id','tanggal', 'ASC');
        $result = $this->db->get('pks_maintenance_log');
        return $result->result_array();
    }


    //   Method untuk menghapus record pks_maintenance_log

    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('pks_maintenance_log');
        return true;
    }


    public function update($id, $input)
    {
        $id = (int)$id;

        $data = array(

            'mesin_id' => $input['mesin_id']['id'],
            'jenis_maintenance_id' => $input['jenis_maintenance_id']['id'],
            'tanggal ' => $input['tanggal'],
            'ket ' => $input['ket'],
			'hm_km ' => $input['hm_km'],
            'diubah_oleh'=> $input['diubah_oleh'],
            'diubah_tanggal'=> date('Y-m-d H:i:s'),
        );
        $this->db->where('id', $id);
        $this->db->update('pks_maintenance_log', $data);

        // hapus  detail
		$this->db->where('maintenance_log_id', $id);
		$this->db->delete('pks_maintenance_log_dt');

        $details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("pks_maintenance_log_dt", array(
				'maintenance_log_id' => $id,
                'sparepart_id'=> $value['sparepart_id']['id'],
                'qty'=> $value['qty']
			));
		}

        return true;
    }


    //  Method untuk mengambil satu record pks_maintenance_log

    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('pks_maintenance_log', 1);
        return $result->row_array();
    }
    public function retrieve_detail($hdid)
	{
		$this->db->select('*');
		$this->db->from('pks_maintenance_log_dt');
		$this->db->where('maintenance_log_id', $hdid);
		$res = $this->db->get();
		return $res->result_array();
	}


    //  Method untuk membuat data pks_maintenance_log

    public function create($input)
    {
        $data = array(
            'mesin_id' => $input['mesin_id']['id'],
            'jenis_maintenance_id' => $input['jenis_maintenance_id']['id'],
            'tanggal' => $input['tanggal'],
            'ket' => $input['ket'],
			'hm_km ' => $input['hm_km'],
            'dibuat_oleh'=> $input['dibuat_oleh'],
            'dibuat_tanggal'=> date('Y-m-d H:i:s'),
            'diubah_oleh'=> $input['diubah_oleh'],
            'diubah_tanggal'=> date('Y-m-d H:i:s'),
        );
        $this->db->insert('pks_maintenance_log', $data);
        $id = $this->db->insert_id();

        $details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("pks_maintenance_log_dt", array(
				'maintenance_log_id' => $id,
                'sparepart_id'=> $value['sparepart_id']['id'],
                'qty'=> $value['qty']
			));
		}

        return $id;
    }
}
