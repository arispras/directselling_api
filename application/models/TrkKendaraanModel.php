<?php


class TrkKendaraanModel extends CI_Model
{

    public function retrieve_all(
        $no_of_records = 10,
        $page_no       = 1
    ) {
        $no_of_records = (int)$no_of_records;
        $page_no       = (int)$page_no;

        $where = array();

        $data = $this->pager->set('trk_kendaraan', $no_of_records, $page_no, $where);

        return $data;
    }


    //  Method untuk mendapatkan semua data trk_kendaraan tanpa pagging

    public function retrieve_all_kategori()
    {
		$this->db->select('trk_kendaraan.*,gbm_organisasi.nama as traksi,gbm_organisasi.kode as kode_traksi');
		$this->db->from('trk_kendaraan');
		$this->db->join('gbm_organisasi','trk_kendaraan.traksi_id=gbm_organisasi.id');
        $this->db->order_by('nama', 'ASC');
        $result = $this->db->get()->result_array();
        return $result;
    }


    //   Method untuk menghapus record trk_kendaraan

    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('trk_kendaraan');
        return true;
    }

    public function retrieve_kendaraan_by_lokasi($parent_id)
	{
		$this->db->select(" a.*,b.nama as traksi, b.parent_id AS parent_id ");
		$this->db->from("trk_kendaraan a");
		$this->db->join("gbm_organisasi b", "b.id=a.traksi_id");
		$this->db->where("b.parent_id", $parent_id);

		$result = $this->db->get();
		return $result->result_array();
	}

    public function update($id, $input)
    {
        $id = (int)$id;

        $data = array(
            // 'tanggal_efektif' => $input['tanggal_efektif'],
            'nama' => $input['nama'],
            'kode' => $input['kode'],
            'traksi_id' => $input['traksi_id']['id'],
            'jenis_id' => $input['jenis_id']['id'],
            'no_kendaraan' => $input['no_kendaraan'],
            'no_mesin' => $input['no_mesin'],
            'no_rangka' => $input['no_rangka'],
            'tahun_perolehan' => $input['tahun_perolehan'],
            'berat_kosong' => $input['berat_kosong'],
            'kepemilikan' => $input['kepemilikan']['id'],
            'nama_pemilik' => $input['nama_pemilik'],
            'is_nonaktif' => $input['is_nonaktif'],
			'diubah_oleh'=> $input['diubah_oleh'],
			'diubah_tanggal'=> date("Y-m-d H:i:s"),
            
        );
        $this->db->where('id', $id);
        $this->db->update('trk_kendaraan', $data);
        return true;
    }


    //  Method untuk mengambil satu record trk_kendaraan

    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('trk_kendaraan', 1);
        return $result->row_array();
    }


    //  Method untuk membuat data trk_kendaraan

    public function create($input)
    {
        $data = array(
            // 'tanggal_efektif' => $input['tanggal_efektif'],
            'nama' => $input['nama'],
            'kode' => $input['kode'],
            'traksi_id' => $input['traksi_id']['id'],
            'jenis_id' => $input['jenis_id']['id'],
            'no_kendaraan' => $input['no_kendaraan'],
            'no_mesin' => $input['no_mesin'],
            'no_rangka' => $input['no_rangka'],
            'tahun_perolehan' => $input['tahun_perolehan'],
            'berat_kosong' => $input['berat_kosong'],
            'kepemilikan' => $input['kepemilikan']['id'],
            'nama_pemilik' => $input['nama_pemilik'],
            'is_nonaktif' => $input['is_nonaktif'],
            'dibuat_oleh'=> $input['dibuat_oleh'],
			'dibuat_tanggal'=> date("Y-m-d H:i:s"),
			'diubah_oleh'=> $input['diubah_oleh'],
			'diubah_tanggal'=> date("Y-m-d H:i:s"),

        );
        $this->db->insert('trk_kendaraan', $data);
        return $this->db->insert_id();
    }
}
