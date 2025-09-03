<?php


class KlnJenisBiayaModel extends CI_Model
{

    public function retrieve_all(
        $no_of_records = 10,
        $page_no       = 1
    ) {
        $no_of_records = (int)$no_of_records;
        $page_no       = (int)$page_no;

        $where = array();

        $data = $this->pager->set('kln_jenis_biaya', $no_of_records, $page_no, $where);

        return $data;
    }


    //  Method untuk mendapatkan semua data kln_jenis_biaya tanpa pagging

    public function retrieve_all_kegiatan()
    {
		$this->db->select('kln_jenis_biaya.*,gbm_uom.kode as uom');
		$this->db->from('kln_jenis_biaya');
		$this->db->join('gbm_uom', 'kln_jenis_biaya.uom_id = gbm_uom.id', 'left');
		$this->db->order_by('kln_jenis_biaya.nama', 'ASC');
        $result = $this->db->get();
        return $result->result_array();
    }
	public function retrieve_all_kegiatan_by_tipe($tipe)
    {
		$this->db->select('kln_jenis_biaya.*,gbm_uom.kode as uom');
		$this->db->from('kln_jenis_biaya');
		$this->db->join('gbm_uom', 'kln_jenis_biaya.uom_id = gbm_uom.id', 'left');
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
		$this->db->order_by('kln_jenis_biaya.nama', 'ASC');
        $result = $this->db->get();
        return $result->result_array();
    }

    //   Method untuk menghapus record kln_jenis_biaya

    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('kln_jenis_biaya');
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
            'kategori_id' => $input['kategori_id']['id'],
			'tipe_biaya' => $input['tipe_biaya_id']['id'],
			'harga' => $input['harga'],
			'diskon' => $input['diskon'],
			'diubah_oleh'=> $input['diubah_oleh'],
			'diubah_tanggal'=> date("Y-m-d H:i:s"),
        );
        $this->db->where('id', $id);
        $this->db->update('kln_jenis_biaya', $data);
        return true;
    }


    //  Method untuk mengambil satu record kln_jenis_biaya

    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('kln_jenis_biaya', 1);
        return $result->row_array();
    }


    //  Method untuk membuat data kln_jenis_biaya

    public function create($input)
    {
        $data = array(
            // 'tanggal_efektif' => $input['tanggal_efektif'],
            'nama' => $input['nama'],
            'kode' => $input['kode'],
			'uom_id' => $input['uom_id']['id'],
            'acc_akun_id' => $input['acc_akun_id']['id'],
            'kategori_id' => $input['kategori_id']['id'],
			'tipe_biaya' => $input['tipe_biaya_id']['id'],
			'harga' => $input['harga'],
			'diskon' => $input['diskon'],
            'dibuat_oleh'=> $input['dibuat_oleh'],
			'dibuat_tanggal'=> date("Y-m-d H:i:s"),
			'diubah_oleh'=> $input['diubah_oleh'],
			'diubah_tanggal'=> date("Y-m-d H:i:s"),
        );
        $this->db->insert('kln_jenis_biaya', $data);
        return $this->db->insert_id();
    }
}
