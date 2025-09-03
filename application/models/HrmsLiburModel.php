<?php


class HrmsLiburModel extends CI_Model
{

    //  Method untuk mendapatkan semua data hrms_libur tanpa pagging

    


    //   Method untuk menghapus record hrms_libur

    public function delete($id)
    {
        $id = (int)$id;
        $this->db->where('hrms_libur_id', $id);
		$this->db->delete('hrms_libur_dt');
        $this->db->where('id', $id);
        $this->db->delete('hrms_libur');

        return true;
    }

    //  Method untuk membuat data hrms_libur

    public function create($input)
    {
        $data = array(
            'tanggal' => $input['tanggal'],
            'tipe_libur' => $input['tipe_libur']['id'],
            'dibuat_oleh'=> $input['dibuat_oleh'],
			'dibuat_tanggal'=> date("Y-m-d H:i:s"),
			'diubah_oleh'=> $input['diubah_oleh'],
			'diubah_tanggal'=> date("Y-m-d H:i:s"),

        );
        $this->db->insert('hrms_libur', $data);
        $id = $this->db->insert_id();

        $details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("hrms_libur_dt", array(
				'hrms_libur_id' => $id,
				'lokasi_id' => $value['lokasi_id']['id'],
			));
		}
        return $id;
    }

    public function update($id, $input)
    {
        $id = (int)$id;

        $data = array(
            'tanggal' => $input['tanggal'],
            'tipe_libur' => $input['tipe_libur']['id'],
			'diubah_oleh'=> $input['diubah_oleh'],
			'diubah_tanggal'=> date("Y-m-d H:i:s"),
            
        );
        $this->db->where('id', $id);
        $this->db->update('hrms_libur', $data);

        // hapus  detail
		$this->db->where('hrms_libur_id', $id);
		$this->db->delete('hrms_libur_dt');

        $details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("hrms_libur_dt", array(
				'hrms_libur_id' => $id,
				'lokasi_id' => $value['lokasi_id']['id'],
			));
		}

        return true;
    }

    public function retrieve_all()
    {
        $this->db->order_by('tipe_libur', 'ASC');
        $result = $this->db->get('hrms_libur');
        return $result->result_array();
    }

    public function retrieve_all_detail($id)
	{
		$this->db->from('hrms_libur_dt');
		$this->db->where('hrms_libur_id', $id);

		return $this->db->get()->result_array();;
	}


	public function retrieve_by_id($id)
	{
		$this->db->select('*');
		$this->db->from('hrms_libur');
		$this->db->where('id', $id);
		$res = $this->db->get();
		return $res->row_array();
	}


    public function retrieve_detail($hdid)
	{
		$this->db->select('*');
		$this->db->from('hrms_libur_dt');
		$this->db->where('hrms_libur_id', $hdid);
		$res = $this->db->get();
		return $res->result_array();
	}
}
