<?php

class HrmsLaporanAbsensiModel extends CI_Model
{



    public function retrieve_all(
        $no_of_records = 10,
        $page_no       = 1
    ) {
        $no_of_records = (int)$no_of_records;
        $page_no       = (int)$page_no;

        $where = array();

        $data = $this->pager->set('pks_sjpp', $no_of_records, $page_no, $where);

        return $data;
    }

   
    public function retrieve_all_kategori()
    {
        // $this->db->where('aktif' , 1);
        $this->db->order_by('id', 'ASC');
        $result = $this->db->get('pks_sjpp');
        return $result->result_array();
    }

   
    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('pks_sjpp');
        return true;
    }


    public function update($id, $input)
    {
        $id = (int)$id;

        $input['diubah_tanggal'] = date('Y-m-d H:i:s');
        $input['mill_id'] = $input['mill_id']['id'];
        $input['tanki_id'] = $input['tanki_id']['id'];
        $input['produk_id'] = $input['produk_id']['id'];
        $input['intruksi_id'] = $input['intruksi_id']['id'];
        $input['kontrak_id'] = $input['kontrak_id']['id'];
        $input['transport_id'] = $input['transport_id']['id'];
        
        $this->db->where('id', $id);
        $this->db->update('pks_sjpp', $input);
        return true;
    }

   
    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('pks_sjpp', 1);
        return $result->row_array();
    }

    
    public function create($input)
    {
        $input['diubah_tanggal'] = date('Y-m-d H:i:s');
        $input['mill_id'] = $input['mill_id']['id'];
        $input['tanki_id'] = $input['tanki_id']['id'];
        $input['produk_id'] = $input['produk_id']['id'];
        $input['intruksi_id'] = $input['intruksi_id']['id'];
        $input['kontrak_id'] = $input['kontrak_id']['id'];
        $input['transport_id'] = $input['transport_id']['id'];

        $this->db->insert('pks_sjpp', $input);
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
		FROM pks_sjpp a 
        inner join pks_tanki b on a.tanki_id = b.id
        inner join inv_item c on a.produk_id = c.id
	    -- inner join inv_item c on b.item=c.id
	    -- inner join inv_gudang d on a.gudang_id=d.id
		
        where 1=1 and a.id=" . $id . ";";
		$data = $this->db->query($query)->row_array();
		return $data;
	}

    public function laporanRekapPengiriman( $input='' )
    {
        $this->db->select("
            c.nama as nama_produk,

            b.no_spk as no_kontrak,
            b.dobi as dobi_kirim,
            b.mi as mi_kirim,
            b.moisture as moist_kirim,

            a.nama_pelanggan as customer,

            a.no_polisi as no_polisi,
            a.nama_pengemudi as nama_supir,
            a.nama_pengemudi,
            a.tanggal as tanggal_kirim,
            a.tanggal_customer as tanggal_terima,
            
            a.ffa as ffa_kirim,
            a.dirt as dirt_kirim,
            a.tara_kirim as tara_kirim,
            a.tara_kirim as tare_kirim,
            a.bruto_kirim as gross_kirim,
            a.bruto_kirim as bruto_kirim,
            a.netto_kirim as netto_kirim,
            
            a.ffa_customer as ffa_terima,
            a.dirt_customer as dirt_terima,
            a.tara_customer as tara_terima,
            a.tara_customer as tare_terima,
            a.bruto_customer as gross_terima,
            a.bruto_customer as bruto_terima,
            a.netto_customer as netto_terima
        ");
        $this->db->from("pks_sjpp a");
        $this->db->join("sls_kontrak b", "a.kontrak_id = b.id");
        $this->db->join("inv_item c", "a.produk_id = c.id");
        
        $this->db->where("b.id", $input['kontrak_id']);
        $this->db->where("DATE(a.tanggal) >= ", $input['tgl_mulai']);
        $this->db->where("DATE(a.tanggal) <= ", $input['tgl_akhir']);

		$data = $this->db->get()->result();
        return $data;
    }
    public function laporanRekapPengirimanCount( $input='' )
    {
        $this->db->from("pks_sjpp a");
        $this->db->join("sls_kontrak b", "a.kontrak_id = b.id");
        $this->db->join("inv_item c", "a.produk_id = c.id");
        $this->db->select_avg("a.ffa");
        $this->db->select_avg("b.mi");
        $this->db->select_avg("b.moisture");
        $this->db->select_avg("a.dirt");
        $this->db->select_sum("b.dobi");
        $this->db->select_sum("a.bruto_kirim");
        $this->db->select_sum("a.tara_kirim");
        $this->db->select_sum("a.netto_kirim");
        $this->db->select_avg("a.ffa_customer");
        $this->db->select_sum("a.bruto_customer");
        $this->db->select_sum("a.tara_customer");
        $this->db->select_sum("a.netto_customer");
        
        $this->db->where("b.id", $input['kontrak_id']);
        $this->db->where("DATE(a.tanggal) >= ", $input['tgl_mulai']);
        $this->db->where("DATE(a.tanggal) <= ", $input['tgl_akhir']);

        $data = $this->db->get()->row();
        return $data;
    }




    public function retrieve_where( $table, $opt ) {
        $this->db->from($table);
        $this->db->where($opt);
        return $this->db->get()->row_array();
    }
    public function retrieve_result_where( $table, $opt, $join='' ) {
        $this->db->from($table);
        $this->db->where($opt);
        if (is_array($join)) {
            foreach ($join as $key=>$val) {
                $this->db->join($key, $val);
            }
        }
        return $this->db->get()->result_array();
    }




    public function laporanGetKaryawan( $karyawan_id )
    {
        $this->db->select("*");
        $this->db->from("karyawan a");
        $this->db->where("id", $karyawan_id);
        $data = $this->db->get()->row_array();
        return $data;
    }

    public function laporanAbsensi( $input='' )
    {
        $this->db->select("
            a.*,
            b.*,
            c.*,
            a.id AS id,
            a.tanggal AS tanggal,
        ");
        $this->db->from("payroll_absensi a");
        $this->db->join("karyawan b", "a.karyawan_id = b.id");
        $this->db->join("hrms_jenis_absensi c", "a.jenis_absensi_id = c.id");

        $this->db->where("karyawan_id", $input['karyawan_id']);
        $this->db->where("lokasi_id", $input['lokasi_id']);

        $this->db->where("DATE(a.tanggal) >= ", $input['tgl_mulai']);
        $this->db->where("DATE(a.tanggal) <= ", $input['tgl_akhir']);

        $this->db->order_by("tanggal", "asc");

		$data = $this->db->get()->result_array();
        return $data;
    }


    public function laporanAbsensiBulanan( $input='' )
    {
        $this->db->select("
            a.*,
            b.*,
            c.*,
            a.id AS id,
            a.tanggal AS tanggal,
        ");
        $this->db->from("payroll_absensi a");
        $this->db->join("karyawan b", "a.karyawan_id = b.id");
        $this->db->join("hrms_jenis_absensi c", "a.jenis_absensi_id = c.id");

        $this->db->where("lokasi_id", $input['lokasi_id']);

        $this->db->where("DATE(a.tanggal) >= ", $input['tgl_mulai']);
        $this->db->where("DATE(a.tanggal) <= ", $input['tgl_akhir']);

        $this->db->order_by("tanggal", "asc");

		$data = $this->db->get()->result_array();
        return $data;
    }


    public function laporanAbsensiJenis( $input='' )
    {
        $this->db->select("
            *
        ");
        $this->db->from("hrms_jenis_absensi");

        $data = $this->db->get()->result_array();
        return $data;
    }








    public function laporanProduksiHarian( $input='' )
    {
        $this->db->select("
            a.*,
            b.*,
            a.id AS id,
            b.nama AS lokasi,
        ");
        $this->db->from("pks_produksi_harian a");
        $this->db->join("gbm_organisasi b", "a.mill_id = b.id");

        
        $this->db->where("b.id", $input['lokasi']);
        $this->db->where("DATE(a.tanggal) >= ", $input['tgl_mulai']);
        $this->db->where("DATE(a.tanggal) <= ", $input['tgl_akhir']);

        $this->db->order_by("tanggal", "asc");

		$data = $this->db->get()->result_array();
        return $data;
    }

    public function laporanProduksiHarianM1( $input='' )
    {
        $this->db->select("
            a.*,
            b.*,
            a.id AS id,
            b.nama AS lokasi,
        ");
        $this->db->from("pks_produksi a");
        $this->db->join("gbm_organisasi b", "a.lokasi_id = b.id");

        
        $this->db->where("b.id", $input['lokasi']);
        $this->db->where("DATE(a.tanggal) >= ", $input['tgl_mulai']);
        $this->db->where("DATE(a.tanggal) <= ", $input['tgl_akhir']);

		$data = $this->db->get()->result_array();
        return $data;
    }

    public function laporanProduksiBulanan( $input='' )
    {
        $this->db->select("
            a.*,
            b.*,

            SUM(a.tbs_olah) AS tbs_olah,
            SUM(a.cpo_kg) AS cpo_kg,
            ROUND( SUM(a.tbs_olah) / SUM(a.cpo_kg), 2) AS cpo_oer,
            SUM(a.cpo_ffa) AS cpo_ffa,
            SUM(a.cpo_dirt) AS cpo_dirt,
            SUM(a.kernel_kg) AS kernel_kg,
            SUM(a.kernel_moisture) AS kernel_moisture,
            SUM(a.kernel_dirt) AS kernel_dirt,
            SUM(a.kernel_ffa) AS kernel_ffa,

            a.id AS id,
            b.nama as lokasi
        ");
        $this->db->from("pks_produksi_harian a");
        $this->db->join("gbm_organisasi b", "a.mill_id = b.id");
        
        $this->db->where("DATE(a.tanggal) >= ", $input['tgl_mulai']);
        $this->db->where("DATE(a.tanggal) <= ", $input['tgl_akhir']);

        $this->db->group_by("DATE_FORMAT(a.tanggal, '%Y%m')");
        
        // $tgl_mulai = $input['tgl_mulai'];
        // $tgl_akhir = $input['tgl_akhir'];
        // $query = $this->db->query("SELECT *,
        //         -- a.*,
        //         -- b.*,
        //         -- a.id AS id,
        //         -- b.nama as lokasi
        //         sum(a.tbs_olah) AS tbs_olah
        //     FROM 
        //         pks_produksi_harian a
        //     INNER JOIN 
        //         gbm_organisasi b ON a.mill_id = b.id
        //     -- WHERE
        //         -- DATE(a.tanggal) >= $tgl_mulai
        //         -- AND
        //         -- DATE(a.tanggal) <= $tgl_akhir
        //     GROUP BY 
        //         DATE_FORMAT(tanggal, '%Y%m')
        //     ");
        // $this->db->query("SELECT avg(berat_bersih)AS bb, DATE_FORMAT(tanggal,'%Y%m') FROM pks_timbangan GROUP BY DATE_FORMAT(tanggal,'%Y%m')");

		$data = $this->db->get()->result_array();
		// $data = $query->result_array();
        return $data;
    }
    public function laporanProduksiBulananCount( $input='' )
    {
        $this->db->from("pks_produksi_harian a");
        
        $this->db->select("
            SUM(a.tbs_olah) AS tbs_olah,
            SUM(a.cpo_kg) AS cpo_kg,
            ROUND( SUM(a.tbs_olah) / SUM(a.cpo_kg), 2) AS cpo_oer,
            SUM(a.cpo_ffa) AS cpo_ffa,
            SUM(a.cpo_dirt) AS cpo_dirt,
            SUM(a.kernel_kg) AS kernel_kg,
            SUM(a.kernel_moisture) AS kernel_moisture,
            SUM(a.kernel_dirt) AS kernel_dirt,
            SUM(a.kernel_ffa) AS kernel_ffa,
        ");
        // $this->db->select_sum("a.cpo_kg");
        // $this->db->select_sum("a.cpo_dirt");
        // $this->db->select_avg("a.cpo_ffa");
        // $this->db->select_sum("a.kernel_kg");
        // $this->db->select_sum("a.kernel_moisture");
        // $this->db->select_sum("a.kernel_dirt");
        // $this->db->select_avg("a.kernel_ffa");
        
        // $this->db->where("b.id", $input['kontrak_id']);
        $this->db->where("DATE(a.tanggal) >= ", $input['tgl_mulai']);
        $this->db->where("DATE(a.tanggal) <= ", $input['tgl_akhir']);

        $data = $this->db->get()->row_array();
        return $data;
    }

}
