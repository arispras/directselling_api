<?php

class ColLHIModel extends CI_Model
{

	public function retrieve_all(
		$no_of_records = 10,
		$page_no       = 1
	) {
		$no_of_records = (int)$no_of_records;
		$page_no       = (int)$page_no;

		$where = array();

		$data = $this->pager->set('col_lhi_ht', $no_of_records, $page_no, $where);

		return $data;
	}


	public function retrieve_all_kategori()
	{
		// $this->db->where('aktif' , 1);
		$this->db->order_by('id', 'ASC');
		$this->db->from('col_lhi_ht a');
		$this->db->select('a.*, b.nama_customer');
		$this->db->join('gbm_customer b', 'a.customer_id = b.id');
		$result = $this->db->get();
		return $result->result_array();
	}

	public function retrieve_all_by_customer($supp_id)
	{
		$this->db->where('customer_id', $supp_id);
		$this->db->order_by('id', 'ASC');
		$this->db->from('col_lhi_ht a');
		$this->db->select('a.*, b.nama_customer');
		$this->db->join('gbm_customer b', 'a.customer_id = b.id');
		$result = $this->db->get();
		return $result->result_array();
	}
	public function retrieve_all_so_release_by_customer($supp_id)
	{
		$this->db->where('customer_id', $supp_id);
		// $this->db->where('status' , 'RELEASE');
		$this->db->order_by('id', 'ASC');
		$this->db->from('col_lhi_ht a');
		$this->db->select('a.*, b.nama_customer');
		$this->db->join('gbm_customer b', 'a.customer_id = b.id');
		$result = $this->db->get();
		return $result->result_array();
	}

	public function delete($id)
	{
		$id = (int)$id;
		$this->db->where('lhi_id', $id);
		$this->db->delete('col_lhi_dt');
		$this->db->where('id', $id);
		$this->db->delete('col_lhi_ht');
		return true;
	}


	public function create($input)
	{

		$ht['lokasi_id'] = $input['lokasi_id']['id'];
		$ht['collector_id'] = $input['collector_id']['id'];
		$ht['no_lhi'] = $input['no_lhi'];
		$ht['keterangan'] = $input['catatan'];
		$ht['tanggal'] = $input['tanggal'];
		$ht['tanggal_mulai'] = $input['tanggal_mulai'];
		$ht['tanggal_akhir'] = $input['tanggal_akhir'];
		$ht['is_bayar'] = 0;
		$ht['dibuat_tanggal'] = date('Y-m-d H:i:s');
		$ht['dibuat_oleh'] = $input['dibuat_oleh'];


		$this->db->insert('col_lhi_ht', $ht);
		$id = $this->db->insert_id();

		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("col_lhi_dt", array(
				'lhi_id' => $id,
				'kuitansi_id' => $value['kuitansi_id'],
				'nilai_angsuran' => $value['nilai_angsuran'],
				'sisa_angsuran' => $value['sisa_angsuran'],
				'lhi_lama' => $value['lhi_lama'],
				'ket' => $value['ket'],
			));
		}

		return $id;
	}
	public function update($id, $input)
	{

		/* HANYA KETERANGAN YANG BISA DIUBAH SETELAH LHI DIBUAT */

		// $ht['lokasi_id'] = $input['lokasi_id']['id'];
		// $ht['collector_id'] = $input['collector_id']['id'];
		// $ht['no_lhi'] = $input['no_lhi'];
		$ht['keterangan'] = $input['catatan'];
		$ht['is_bayar'] = 0;
		// $ht['tanggal'] = $input['tanggal'];
		// $ht['tanggal_mulai'] = $input['tanggal_mulai'];
		// $ht['tanggal_akhir'] = $input['tanggal_akhir'];

		$ht['diubah_tanggal'] = date('Y-m-d H:i:s');
		$ht['diubah_oleh'] = $input['diubah_oleh'];

		$this->db->where('id', $id);
		$this->db->update('col_lhi_ht', $ht);

		// hapus  detail
		$this->db->where('lhi_id', $id);
		$this->db->delete('col_lhi_dt');

		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("col_lhi_dt", array(
				'lhi_id' => $id,
				'kuitansi_id' => $value['kuitansi_id'],
				'nilai_angsuran' => $value['nilai_angsuran'],
				'sisa_angsuran' => $value['sisa_angsuran'],
				/* HANYA BBRP FILED YANG BISA DIUBAH SETELAH LHI DIBUAT */

				// 'dibayar' => $value['dibayar'],
				// 'sisa_akhir' => $value['sisa_akhir'],
				// 'tanggal_janji' => $value['tanggal_janji'],
				'lhi_lama' => $value['lhi_lama'],
				'ket' => $value['ket'],
			));
		}
		return true;
	}


	public function updateLhiBayar($id, $input)
	{

		/* HANYA KETERANGAN YANG BISA DIUBAH SETELAH LHI DIBUAT */

		// $ht['lokasi_id'] = $input['lokasi_id']['id'];
		// $ht['collector_id'] = $input['collector_id']['id'];
		// $ht['no_lhi'] = $input['no_lhi'];
		$ht['keterangan'] = $input['catatan'];
		$ht['is_bayar'] = 1;
		// $ht['tanggal'] = $input['tanggal'];
		// $ht['tanggal_mulai'] = $input['tanggal_mulai'];
		// $ht['tanggal_akhir'] = $input['tanggal_akhir'];

		$ht['diubah_tanggal'] = date('Y-m-d H:i:s');
		$ht['diubah_oleh'] = $input['diubah_oleh'];

		$this->db->where('id', $id);
		$this->db->update('col_lhi_ht', $ht);

		// hapus  detail
		$this->db->where('lhi_id', $id);
		$this->db->delete('col_lhi_dt');

		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("col_lhi_dt", array(
				'lhi_id' => $id,
				'kuitansi_id' => $value['kuitansi_id'],
				'nilai_angsuran' => $value['nilai_angsuran'],
				'sisa_angsuran' => $value['sisa_angsuran'],
				'dibayar' => $value['dibayar'],
				'sisa_akhir' => $value['sisa_akhir'],
				'tanggal_janji' => $value['tanggal_janji'],
				'lhi_lama' => $value['lhi_lama'],
				'ket' => $value['ket'],
			));
			if ($value['tanggal_janji'] != null || $value['tanggal_janji'] != '') {
				// update kuitansi
				$kui['tanggal_janji'] = $value['tanggal_janji'];
				$this->db->where('id', $value['kuitansi_id']);
				$this->db->update('col_kuitansi_ht', $kui);
			}

		}
		return true;
	}

	public function retrieve($id)
	{
		$id = (int)$id;



		$this->db->select('a.*, b.nama as nama_collector,');
		$this->db->from('col_lhi_ht a');
		$this->db->join('karyawan b', 'a.collector_id = b.id', "left");
		$this->db->where('a.id', $id);
		$result = $this->db->get();
		return $result->row_array();
	}

	public function retrieve_detail($hdid)
	{
		// $this->db->select('est_spat_dt.*,gbm_organisasi.kode as kode_blok,gbm_organisasi.nama as nama_blok');
		$this->db->select('a.*, b.no_kuitansi,b.angsuran_ke, b.tanggal_tempo,a.tanggal_janji as tanggal_janji ,c.nama_customer');
		$this->db->from('col_lhi_dt a');
		$this->db->join('col_kuitansi_ht b', 'a.kuitansi_id = b.id', "left");
		$this->db->join('gbm_customer c', 'b.customer_id = c.id', "left");
		$this->db->where('a.lhi_id', $hdid);
		$res = $this->db->get();
		return $res->result_array();
	}



	public function reject($id, $input)
	{
		$id = (int)$id;
		$status = $input['status'];
		$is_finish = $input['is_finish'];
		$field_next_approve = '';
		$field_status_approve = '';
		$field_note_approve = '';
		$field_tanggal_approve = '';
		$last_approve_position = '';

		if ($status == '') {
			$field_next_approve = 'user_approve1';
			$field_status_approve = '';
			$field_note_approve = '';
			$field_tanggal_approve = '';
			$last_approve_position = 'PO1';
		} else if ($status == 'PO1') {
			$field_next_approve = 'user_approve2';
			$field_status_approve = 'status_approve1';
			$field_note_approve = 'note_approve1';
			$field_tanggal_approve = 'tgl_approve1';
			$last_approve_position = 'PO2';
		} else if ($status == 'PO2') {
			$field_next_approve = 'user_approve3';
			$field_status_approve = 'status_approve2';
			$field_note_approve = 'note_approve2';
			$field_tanggal_approve = 'tgl_approve2';
			$last_approve_position = 'PO3';
		} else if ($status == 'PO3') {
			$field_next_approve = 'user_approve4';
			$field_status_approve = 'status_approve3';
			$field_note_approve = 'note_approve3';
			$field_tanggal_approve = 'tgl_approve3';
			$last_approve_position = 'PO4';
		} else if ($status == 'PO4') {
			$field_next_approve = 'user_approve5';
			$field_status_approve = 'status_approve4';
			$field_note_approve = 'note_approve4';
			$field_tanggal_approve = 'tgl_approve4';
			$last_approve_position = 'PO5';
		} else if ($status == 'PO5') {
			$field_next_approve = 'user_approve6';
			$field_status_approve = 'status_approve5';
			$field_note_approve = 'note_approve5';
			$field_tanggal_approve = 'tgl_approve5';
			$last_approve_position = 'PO6';
		}

		$ht['proses_approval'] = 1;


		if ($status != '') {
			$ht[$field_status_approve] = 'REJECTED';
			$ht[$field_note_approve] = $input['note_approve'];
			$ht[$field_tanggal_approve] = date('Y-m-d H:i:s');
			$ht['status'] = 'REJECTED';
			$ht['last_approve_position'] = NULL;
			$ht['last_approve_user'] = NULL;
		}

		$ht['diubah_tanggal'] = date('Y-m-d H:i:s');
		$ht['diubah_oleh'] = $input['diubah_oleh'];
		$this->db->where('id', $id);
		$this->db->update('col_lhi_ht', $ht);

		return true;
	}
	public function posting($id,	$input)
	{
		$id = (int)$id;
		$data['status'] = 'RELEASE';
		$data['last_approve_position'] = NULL;
		$data['last_approve_user'] = NULL;
		$data['is_posting'] = 1;
		$data['diposting_tanggal'] = date('Y-m-d H:i:s');
		$data['diposting_oleh'] =  $input['diubah_oleh'];
		$data['diubah_tanggal'] = date('Y-m-d H:i:s');
		$data['diubah_oleh'] = $input['diubah_oleh'];
		$this->db->where('id', $id);
		$this->db->update('col_lhi_ht', $data);


		return true;
	}
	public function retrieve_so_dtl_blm_terima2($so_id)
	{
		$sql = "Select f.nama as lokasi, a.tanggal,a.no_ttb,a.catatan,b.id, b.item_id,c.kode as kode_item,c.nama as nama_item,d.kode as uom, b.qty,IFNULL(e.qty_terkirim, 0)as qty_sudah_terima,
		b.qty-IFNULL(e.qty_terkirim, 0)as qty_belum_terima ,  b.harga,b.diskon 
			from col_lhi_ht a INNER JOIN col_lhi_dt b ON a.id=b.lhi_id
			INNER JOIN inv_item c on b.item_id=c.id 
			INNER join gbm_uom d on c.uom_id=d.id
			LEFT join (
			select so_dt_id,sum(qty)as qty_terkirim from inv_pengiriman_so_dt group by so_dt_id)e 
			on b.id=e.so_dt_id
			left join gbm_organisasi f on a.lokasi_id=f.id
			where a.status='RELEASE'
			and a.id=" . $so_id . "
			and b.qty-IFNULL(e.qty_terkirim, 0)>0
			order by a.tanggal,a.no_ttb";
		$result =	$this->db->query($sql)->result_array();
		return $result;
	}

	public function retrieve_so_dtl_blm_terkirm($so_id)
	{
		$sql = "Select f.nama as lokasi, a.tanggal,a.no_ttb,a.catatan,b.id, b.item_id,c.kode as kode_item,c.nama as nama_item,d.kode as uom, b.qty,IFNULL(e.qty_terkirim, 0)as qty_sudah_terima,
		b.qty-IFNULL(e.qty_terkirim, 0)as qty_belum_terima ,  b.harga,b.diskon 
			from col_lhi_ht a INNER JOIN col_lhi_dt b ON a.id=b.lhi_id
			INNER JOIN inv_item c on b.item_id=c.id 
			INNER join gbm_uom d on c.uom_id=d.id
			LEFT join (
			select so_dt_id,sum(qty)as qty_terkirim from inv_pengiriman_so_dt group by so_dt_id)e 
			on b.id=e.so_dt_id
			left join gbm_organisasi f on a.lokasi_id=f.id
			where 1=1
			and a.id=" . $so_id . "
			and b.qty-IFNULL(e.qty_terkirim, 0)>0
			order by a.tanggal,a.no_ttb";
		$result =	$this->db->query($sql)->result_array();
		return $result;
	}
	public function retrieve_so_dtl_sdh_terkirim($so_id)
	{
		$sql = "Select f.nama as lokasi, a.tanggal,a.no_ttb,a.catatan,b.id, b.item_id,c.kode as kode_item,c.nama as nama_item,d.kode as uom, 
		b.qty,IFNULL(e.qty_terkirim, 0)as qty_sudah_terima,b.qty-IFNULL(e.qty_terkirim, 0)as qty_belum_terima, b.harga ,b.diskon 
			from col_lhi_ht a INNER JOIN col_lhi_dt b ON a.id=b.lhi_id
			INNER JOIN inv_item c on b.item_id=c.id 
			INNER join gbm_uom d on c.uom_id=d.id
			LEFT join (
			select so_dt_id,sum(qty)as qty_terkirim from inv_pengiriman_so_dt group by so_dt_id)e 
			on b.id=e.so_dt_id
			left join gbm_organisasi f on a.lokasi_id=f.id
			where a.status='RELEASE' and a.id=" . $so_id . "
			and IFNULL(e.qty_terkirim, 0)>0
			order by a.tanggal,a.no_ttb";
		$result =	$this->db->query($sql)->result_array();
		return $result;
	}
	public function retrieve_so_dtl($so_id)
	{
		$sql = "Select f.nama as lokasi, a.tanggal,a.no_ttb,a.catatan,b.id, b.item_id,c.kode as kode_item,c.nama as nama_item,d.kode as uom, 
		b.qty,IFNULL(e.qty_terkirim, 0)as qty_sudah_terima,b.qty-IFNULL(e.qty_terkirim, 0)as qty_belum_terima, b.harga ,b.diskon 
			from col_lhi_ht a INNER JOIN col_lhi_dt b ON a.id=b.lhi_id
			INNER JOIN inv_item c on b.item_id=c.id 
			INNER join gbm_uom d on c.uom_id=d.id
			LEFT join (
			select so_dt_id,sum(qty)as qty_terkirim from inv_pengiriman_so_dt group by so_dt_id)e 
			on b.id=e.so_dt_id
			left join gbm_organisasi f on a.lokasi_id=f.id
			where a.id=" . $so_id . "
			order by a.tanggal,a.no_ttb";
		$result =	$this->db->query($sql)->result_array();
		return $result;
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
		FROM col_lhi_ht a 
        inner join pks_tanki b on a.tanki_id = b.id
        inner join inv_item c on a.produk_id = c.id
	    -- inner join inv_item c on b.item=c.id
	    -- inner join inv_gudang d on a.gudang_id=d.id
		
        where 1=1 and a.id=" . $id . ";";
		$data = $this->db->query($query)->row_array();
		return $data;
	}
	public function retrieve_pembayaran_by_so($so_id)
	{
		$this->db->where('lhi_id', $so_id);
		$this->db->order_by('id', 'ASC');
		$this->db->from('sls_so_pembayaran ');
		$this->db->select('*');

		$result = $this->db->get();
		return $result->result_array();
	}
	public function retrieve_pembayaran_by_id($id)
	{
		$this->db->where('id', $id);
		$this->db->order_by('id', 'ASC');
		$this->db->from('sls_so_pembayaran ');
		$this->db->select('*');

		$result = $this->db->get();
		return $result->row_array();
	}

	public function update_pembayaran(
		$id,
		$arrdata
	) {


		$id = (int)$id;
		$nilai = $arrdata['nilai'];
		$tanggal    =  $arrdata['tanggal'];
		$keterangan    =  $arrdata['keterangan'];
		$jenis_invoice  =  $arrdata['jenis_invoice']['id'];
		$tipe_pembayaran  =  $arrdata['tipe_pembayaran']['id'];
		$so_id = $arrdata['so_id'];
		$data = array(
			'lhi_id' => $so_id,
			'nilai' => $nilai,
			'tanggal' => $tanggal,
			'keterangan' => $keterangan,
			'jenis_invoice'    => $jenis_invoice,
			'tipe_pembayaran'    => $tipe_pembayaran,
		);

		$this->db->where('id', $id);
		$this->db->update('sls_so_pembayaran', $data);
		return true;
	}
	public function create_pembayaran(
		$arrdata
	) {

		$so_id = $arrdata['so_id'];
		$nilai = $arrdata['nilai'];
		$tanggal    =  $arrdata['tanggal'];
		$keterangan    =  $arrdata['keterangan'];
		$jenis_invoice  =  $arrdata['jenis_invoice']['id'];
		$tipe_pembayaran  =  $arrdata['tipe_pembayaran']['id'];
		$data = array(
			'lhi_id' => $so_id,
			'nilai' => $nilai,
			'tanggal' => $tanggal,
			'keterangan' => $keterangan,
			'jenis_invoice'    => $jenis_invoice,
			'tipe_pembayaran'    => $tipe_pembayaran,
		);

		$this->db->insert('sls_so_pembayaran', $data);
		return true;
	}
	public function delete_pembayaran($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$this->db->delete('sls_so_pembayaran');
		return true;
	}

	public function retrieve_invoice_by_so($so_id)
	{
		$this->db->where('lhi_id', $so_id);
		$this->db->order_by('id', 'ASC');
		$this->db->from('sls_so_invoice ');
		$this->db->select('*');

		$result = $this->db->get();
		return $result->result_array();
	}
	public function retrieve_invoice_by_id($id)
	{
		$this->db->where('id', $id);
		$this->db->order_by('id', 'ASC');
		$this->db->from('sls_so_invoice ');
		$this->db->select('*');

		$result = $this->db->get();
		return $result->row_array();
	}

	public function update_invoice(
		$id,
		$arrdata
	) {


		$id = (int)$id;
		$so_id = $arrdata['so_id'];
		$nilai_invoice = $arrdata['nilai_invoice'];
		$dp_persen = $arrdata['dp_persen'];
		$tanggal    =  $arrdata['tanggal'];
		$tanggal_tempo    =  $arrdata['tanggal_tempo'];
		$keterangan    =  $arrdata['keterangan'];
		$jenis_invoice  =  $arrdata['jenis_invoice']['id'];
		$no_invoice  =  $arrdata['no_invoice'];
		$data = array(
			'lhi_id' => $so_id,
			'nilai_invoice' => $nilai_invoice,
			'dp_persen' => $dp_persen,
			'tanggal' => $tanggal,
			'tanggal_tempo' => $tanggal_tempo,
			'keterangan' => $keterangan,
			'no_invoice' => $no_invoice,
			'jenis_invoice'    => $jenis_invoice,

		);

		$this->db->where('id', $id);
		$this->db->update('sls_so_invoice', $data);
		return true;
	}
	public function create_invoice(
		$arrdata
	) {

		$so_id = $arrdata['so_id'];
		$nilai_invoice = $arrdata['nilai_invoice'];
		$dp_persen = $arrdata['dp_persen'];
		$tanggal    =  $arrdata['tanggal'];
		$tanggal_tempo    =  $arrdata['tanggal_tempo'];
		$keterangan    =  $arrdata['keterangan'];
		$jenis_invoice  =  $arrdata['jenis_invoice']['id'];
		$no_invoice  =  $arrdata['no_invoice'];
		$data = array(
			'lhi_id' => $so_id,
			'nilai_invoice' => $nilai_invoice,
			'dp_persen' => $dp_persen,
			'tanggal' => $tanggal,
			'tanggal_tempo' => $tanggal_tempo,
			'keterangan' => $keterangan,
			'jenis_invoice'    => $jenis_invoice,
			'no_invoice' => $no_invoice

		);

		$this->db->insert('sls_so_invoice', $data);
		return true;
	}
	public function delete_invoice($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$this->db->delete('sls_so_invoice');
		return true;
	}
}
