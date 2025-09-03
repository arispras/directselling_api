<?php

 
class AssetModel extends CI_Model
{
 
   
    
    public function delete($id)
    {
        $id = (int)$id;
        $this->db->where('id', $id);
        $this->db->delete('asset');
        return true;
    }


    public function retrieve_all(
        $no_of_records = 10,
        $page_no       = 1,
        $nama   ='',
        $kode= '',
        $inv_kategori_id= array(),
        $pagination    = true
    ) {
        $no_of_records = (int)$no_of_records;
        $page_no       = (int)$page_no;

        $where    = array();
        $group_by = array();

        $where['inv_kategori'] = array('asset.inv_kategori_id = inv_kategori.id', 'join', 'left');
        if (!empty($inv_kategori_id)) {
            $where['asset.inv_kategori_id'] = array($inv_kategori_id, 'where_in');
        }

        $like = 0;
        if (!empty($nama)) {
            $where['asset.nama'] = array($nama, 'like');
            $like = 1;
        }
        if (!empty($kode)) {
            if ($like) {
                $value = array($kode, 'or_like');
            } else {
                $value = array($kode, 'like');
            }
            $where['asset.kode'] = $value;
        }
        $orderby = array(
            'asset.id' => 'DESC'
        );

        if ($pagination) {
            $data = $this->pager->set('asset', $no_of_records, $page_no, $where, $orderby, 'asset.*', $group_by);
        } else {
            # cari jumlah semua pengajar
            $no_of_records = $this->db->count_all('asset');
            $search_all    = $this->pager->set('asset', $no_of_records, $page_no, $where, $orderby, 'asset.*', $group_by);
            $data          = $search_all['results'];
        }

        return $data;
    }
   
    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('asset', 1);
        return $result->row_array();
    }
    public function retrieve_all_asset()
    {
        $this->db->order_by('nama', 'ASC');
        $this->db->get('asset');
        $result = $this->db->get('asset');
        return $result->result_array();
    }

    public function create(
       $arrdata
    ) {

		$kode = $arrdata['kode'];
        $nama    =  $arrdata['nama'];
        $tgl_beli= $arrdata['tgl_beli'];
        $tgl_mulai_pakai= $arrdata['tgl_mulai_pakai'];
        $asset_kategori_id  = (int) $arrdata['asset_kategori_id'];
        $asset_tipe_id    =  (int)$arrdata['asset_tipe_id'];
        $asset_lokasi_id    = (int) $arrdata['asset_lokasi_id'];
        $pengguna_id    =  !empty($arrdata['pengguna_id'])?$arrdata['pengguna_id']:null;
        $harga_beli    =  $arrdata['harga_beli'];
        $nilai_asset    =$arrdata['nilai_asset'];
       
        $diubah_oleh    =  $arrdata['diubah_oleh'];
		$diubah_tanggal    =  date('Y-m-d H:i:s');
		$dibuat_oleh    =  $arrdata['dibuat_oleh'];
		$dibuat_tanggal    =  date('Y-m-d H:i:s');

        $data = array(
            'kode' => $kode,
            'nama' => $nama,
            'tgl_beli'    => $tgl_beli,
            'tgl_mulai_pakai'    => $tgl_mulai_pakai,
            'asset_kategori_id'       => $asset_kategori_id,
            'asset_tipe_id'      => $asset_tipe_id,
            'asset_lokasi_id'      => $asset_lokasi_id,
            'pengguna_id'        => $pengguna_id,
            'harga_beli' =>$harga_beli,
            'nilai_asset'     => $nilai_asset,
           
            'diubah_oleh' => $diubah_oleh,
			'diubah_tanggal' => $diubah_tanggal,
			'dibuat_oleh' => $dibuat_oleh,
			'dibuat_tanggal' => $dibuat_tanggal,
        );
        $this->db->insert('asset', $data);
        return $this->db->insert_id();
    }

    public function update($id,
        $arrdata
     ) {

        $id=(int)$id;
        $kode = $arrdata['kode'];
        $nama    =  $arrdata['nama'];
        $tgl_beli= $arrdata['tgl_beli'];
        $tgl_mulai_pakai= $arrdata['tgl_mulai_pakai'];
        $asset_kategori_id  = (int) $arrdata['asset_kategori_id'];
        $asset_tipe_id    =  (int)$arrdata['asset_tipe_id'];
        $asset_lokasi_id    = (int) $arrdata['asset_lokasi_id'];
		$pengguna_id    =  !empty($arrdata['pengguna_id'])?$arrdata['pengguna_id']:null;
        $harga_beli    =  $arrdata['harga_beli'];
        $nilai_asset    =$arrdata['nilai_asset'];
        
        $diubah_oleh    =  $arrdata['diubah_oleh'];
		$diubah_tanggal    =  date('Y-m-d H:i:s');
        $data = array(
            'kode' => $kode,
            'nama' => $nama,
            'tgl_beli'    => $tgl_beli,
            'tgl_mulai_pakai'    => $tgl_mulai_pakai,
            'asset_kategori_id'       => $asset_kategori_id,
            'asset_tipe_id'      => $asset_tipe_id,
            'asset_lokasi_id'      => $asset_lokasi_id,
            'pengguna_id'        => $pengguna_id,
            'harga_beli' =>$harga_beli,
            'nilai_asset'     => $nilai_asset,
            'diubah_oleh' => $diubah_oleh,
			'diubah_tanggal' => $diubah_tanggal,
        );
         $this->db->where('id', $id);
         $this->db->update('asset', $data);
         return true;

     }


}
