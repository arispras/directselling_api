<?php


class EstSpkKendaraanModel extends CI_Model
{


    //  Method untuk mendapatkan semua data est_spk_kendaraan tanpa pagging

    public function retrieve_all()
    {
        $this->db->select('est_spk_kendaraan.*,gbm_supplier.kode_supplier,gbm_supplier.nama_supplier');
		$this->db->from('est_spk_kendaraan');
		$this->db->join('gbm_supplier','est_spk_kendaraan.kontraktor_id=gbm_supplier.id');
		$this->db->order_by('est_spk_kendaraan.no_spk', 'ASC');
        $result = $this->db->get();
        return $result->result_array();
    }

	public function retrieve_all_by_estate($estate_id)
    {
        $this->db->select('est_spk_kendaraan.*,gbm_supplier.kode_supplier,gbm_supplier.nama_supplier');
		$this->db->from('est_spk_kendaraan');
		$this->db->join('gbm_supplier','est_spk_kendaraan.kontraktor_id=gbm_supplier.id');
		$this->db->where('est_spk_kendaraan.lokasi_id',$estate_id);
		$this->db->order_by('est_spk_kendaraan.no_spk', 'ASC');
        $result = $this->db->get();
        return $result->result_array();
    }
    //   Method untuk menghapus record est_spk_kendaraan

    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('est_spk_kendaraan');
        return true;
    }


    public function update($id, $input)
    {
        $id = (int)$id;

        $data = array(
            // 'tanggal_efektif' => $input['tanggal_efektif'],
            'lokasi_id' => $input['lokasi_id']['id'],
            'traksi_id' => $input['traksi_id']['id'],
            'no_spk' => $input['no_spk'],
            'tanggal' => $input['tanggal'],
            'kontraktor_id' => $input['kontraktor_id']['id'],
            'kendaraan_id' => $input['kendaraan_id']['id'],
            'tanggal_mulai' => $input['tanggal_mulai'],
            'tanggal_akhir' => $input['tanggal_akhir'],
            'harga_sewa' => $input['harga_sewa'],
            'harga_mob' => $input['harga_mob'],
            'uom_id' => $input['uom_id']['id'],
            'deskripsi' => $input['deskripsi'],
            'diubah_oleh'=> $input['diubah_oleh'],
			'diubah_tanggal'=> date('Y-m-d H:i:s'),
        );
        $this->db->where('id', $id);
        $this->db->update('est_spk_kendaraan', $data);
        return true;
    }


    //  Method untuk mengambil satu record est_spk_kendaraan

    public function retrieve($id)
    {
        $id = (int)$id;

        // $this->db->where('id', $id);
        // $result = $this->db->get('est_spk_kendaraan', 1);
        // return $result->row_array();

		$this->db->select('est_spk_kendaraan.*,trk_kendaraan.kode as kode_kendaraan,trk_kendaraan.nama as nama_kendaraan');
		$this->db->from('est_spk_kendaraan');
		$this->db->join('trk_kendaraan','est_spk_kendaraan.kendaraan_id=trk_kendaraan.id');
		$this->db->where('est_spk_kendaraan.id', $id);
		$result = $this->db->get();
		return $result->row_array();
    }


    //  Method untuk membuat data est_spk_kendaraan

    public function create($input)
    {
        $data = array(
            'lokasi_id' => $input['lokasi_id']['id'],
            'traksi_id' => $input['traksi_id']['id'],
            'no_spk' => $input['no_spk'],
            'tanggal' => $input['tanggal'],
            'kontraktor_id' => $input['kontraktor_id']['id'],
            'kendaraan_id' => $input['kendaraan_id']['id'],
            'tanggal_mulai' => $input['tanggal_mulai'],
            'tanggal_akhir' => $input['tanggal_akhir'],
            'harga_sewa' => $input['harga_sewa'],
            'harga_mob' => $input['harga_mob'],
            'uom_id' => $input['uom_id']['id'],
            'deskripsi' => $input['deskripsi'],
            'dibuat_oleh'=> $input['dibuat_oleh'],
			'dibuat_tanggal'=> date('Y-m-d H:i:s'),
        );
        $this->db->insert('est_spk_kendaraan', $data);
        return $this->db->insert_id();
    }
}
