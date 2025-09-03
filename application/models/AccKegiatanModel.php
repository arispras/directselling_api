<?php


class AccKegiatanModel extends CI_Model
{

    public function retrieve_all(
        $no_of_records = 10,
        $page_no       = 1
    ) {
        $no_of_records = (int)$no_of_records;
        $page_no       = (int)$page_no;

        $where = array();

        $data = $this->pager->set('acc_kegiatan', $no_of_records, $page_no, $where);

        return $data;
    }


    //  Method untuk mendapatkan semua data acc_kegiatan tanpa pagging

    public function retrieve_all_kegiatan()
    {
		$this->db->select('acc_kegiatan.*,gbm_uom.kode as uom');
		$this->db->from('acc_kegiatan');
		$this->db->join('gbm_uom', 'acc_kegiatan.uom_id = gbm_uom.id', 'left');
		$this->db->order_by('acc_kegiatan.nama', 'ASC');
        $result = $this->db->get();
        return $result->result_array();
    }
	public function retrieve_all_kegiatan_by_tipe($tipe)
    {
		$this->db->select('acc_kegiatan.*,gbm_uom.kode as uom');
		$this->db->from('acc_kegiatan');
		$this->db->join('gbm_uom', 'acc_kegiatan.uom_id = gbm_uom.id', 'left');
		if ($tipe=='BAHAN'){
			$this->db->where('is_bahan', 1);
		}else if ($tipe=='PEMELIHARAAN'){
			$this->db->where('is_pemeliharaan', 1);
		}else if ($tipe=='TRAKSI'){
			$this->db->where('is_traksi', 1);
		}	else if ($tipe=='UMUM'){
			$this->db->where('is_umum', 1);
		}else if ($tipe=='TRAKSI_MILL'){
			$this->db->where('is_traksi_mill', 1);
		}else if ($tipe=='TRAKSI_ALL'){
			$this->db->where('is_traksi', 1);
			$this->db->or_where('is_traksi_mill', 1);
		}		
		$this->db->order_by('acc_kegiatan.nama', 'ASC');
        $result = $this->db->get();
        return $result->result_array();
    }

    //   Method untuk menghapus record acc_kegiatan

    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('acc_kegiatan');
        return true;
    }


    public function update($id, $input)
    {
        $id = (int)$id;

        $data = array(
            // 'tanggal_efektif' => $input['tanggal_efektif'],
            'nama' => $input['nama'],
            'kode' => $input['kode'],
            'acc_akun_id' => $input['acc_akun_id']['id'],
			'uom_id' => $input['uom_id']['id'],
            'kegiatan_kelompok_id' => $input['kegiatan_kelompok_id']['id'],
			'tipe_kegiatan' => $input['tipe_kegiatan_id']['id'],
			'is_pemeliharaan' => $input['is_pemeliharaan'],
			'is_bahan' => $input['is_bahan'],
			'is_traksi' => $input['is_traksi'],
            'is_umum' => $input['is_umum'],
			'is_premi_otomatis' => $input['is_premi_otomatis'],
			'is_traksi_mill' => $input['is_traksi_mill'],
			'basis' => $input['basis'],
			'premi_basis' => $input['premi_basis'],
			'premi_lebih_basis' => $input['premi_lebih_basis'],
			'diubah_oleh'=> $input['diubah_oleh'],
			'diubah_tanggal'=> date("Y-m-d H:i:s"),
        );
        $this->db->where('id', $id);
        $this->db->update('acc_kegiatan', $data);
        return true;
    }


    //  Method untuk mengambil satu record acc_kegiatan

    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('acc_kegiatan', 1);
        return $result->row_array();
    }


    //  Method untuk membuat data acc_kegiatan

    public function create($input)
    {
        $data = array(
            // 'tanggal_efektif' => $input['tanggal_efektif'],
            'nama' => $input['nama'],
            'kode' => $input['kode'],
			'uom_id' => $input['uom_id']['id'],
            'acc_akun_id' => $input['acc_akun_id']['id'],
            'kegiatan_kelompok_id' => $input['kegiatan_kelompok_id']['id'],
			'tipe_kegiatan' => $input['tipe_kegiatan_id']['id'],
			'is_pemeliharaan' => $input['is_pemeliharaan'],
			'is_bahan' => $input['is_bahan'],
			'is_traksi' => $input['is_traksi'],
            'is_umum' => $input['is_umum'],
			'is_premi_otomatis' => $input['is_premi_otomatis'],
			'is_traksi_mill' => $input['is_traksi_mill'],
			'basis' => $input['basis'],
			'premi_basis' => $input['premi_basis'],
			'premi_lebih_basis' => $input['premi_lebih_basis'],
            'dibuat_oleh'=> $input['dibuat_oleh'],
			'dibuat_tanggal'=> date("Y-m-d H:i:s"),
			'diubah_oleh'=> $input['diubah_oleh'],
			'diubah_tanggal'=> date("Y-m-d H:i:s"),
        );
        $this->db->insert('acc_kegiatan', $data);
        return $this->db->insert_id();
    }
}
