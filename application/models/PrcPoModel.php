<?php

class PrcPoModel extends CI_Model
{

	public function retrieve_all(
		$no_of_records = 10,
		$page_no       = 1
	) {
		$no_of_records = (int)$no_of_records;
		$page_no       = (int)$page_no;

		$where = array();

		$data = $this->pager->set('prc_po_ht', $no_of_records, $page_no, $where);

		return $data;
	}


	public function retrieve_all_kategori()
	{
		// $this->db->where('aktif' , 1);
		$this->db->order_by('id', 'ASC');
		$this->db->from('prc_po_ht a');
		$this->db->select('a.*, b.nama_supplier');
		$this->db->join('gbm_supplier b', 'a.supplier_id = b.id');
		$result = $this->db->get();
		return $result->result_array();
	}

	public function retrieve_all_by_supplier($supp_id)
	{
		 $this->db->where('supplier_id' , $supp_id);
		$this->db->order_by('id', 'ASC');
		$this->db->from('prc_po_ht a');
		$this->db->select('a.*, b.nama_supplier');
		$this->db->join('gbm_supplier b', 'a.supplier_id = b.id');
		$result = $this->db->get();
		return $result->result_array();
	}
	public function retrieve_all_po_release_by_supplier($supp_id)
	{
		$this->db->where('supplier_id' , $supp_id);
		$this->db->where('status' , 'RELEASE');
		$this->db->order_by('id', 'ASC');
		$this->db->from('prc_po_ht a');
		$this->db->select('a.*, b.nama_supplier');
		$this->db->join('gbm_supplier b', 'a.supplier_id = b.id');
		$result = $this->db->get();
		return $result->result_array();
	}

	public function delete($id)
	{
		$id = (int)$id;
		$this->db->where('po_hd_id', $id);
		$this->db->delete('prc_po_dt');
		$this->db->where('id', $id);
		$this->db->delete('prc_po_ht');
		return true;
	}


	public function update($id, $input)
	{
		$id = (int)$id;	
		$ht['lokasi_id'] = $input['lokasi_id']['id'];
		$ht['lokasi_pp_id'] = $input['lokasi_pp_id']['id'];
		$ht['supplier_id'] = $input['supplier_id']['id'];
		$ht['syarat_bayar_id'] = $input['syarat_bayar_id']['id'];
		$ht['franco_id'] = $input['franco_id']['id'];
		$ht['mata_uang_id'] = $input['mata_uang_id']['id'];
		$ht['quotation_id'] = $input['quotation_id'];
		// $ht['ttd_peminta'] = $input['ttd_peminta']['id'];
		// $ht['ttd_penyetuju'] = $input['ttd_penyetuju']['id'];
		$ht['status_stok'] = $input['status_stok']['id'];
		$ht['no_po'] = $input['no_po'];
		$ht['catatan'] = $input['catatan'];
		$ht['ket_indent'] = $input['ket_indent'];
		$ht['info_pengiriman'] = $input['info_pengiriman'];
		$ht['tempo_bayar'] = $input['tempo_bayar'];
		$ht['sub_total'] = $input['sub_total'];
		$ht['disc'] = $input['disc'];
		$ht['diskon'] = $input['diskon'];
		$ht['ppn'] = $input['ppn'];
		$ht['pph'] = $input['pph'];
		$ht['ppbkb'] = $input['ppbkb'];
		$ht['biaya_kirim'] = $input['biaya_kirim'];
		$ht['grand_total'] = $input['grand_total'];
		$ht['biaya_lain'] = $input['biaya_lain'];
		$ht['pph_nilai'] = $input['pph_nilai'];
		$ht['tanggal'] = $input['tanggal'];
		$ht['diubah_oleh'] = $input['diubah_oleh'];
		$input['diubah_tanggal'] = date('Y-m-d H:i:s');
		$this->db->where('id', $id);
		$this->db->update('prc_po_ht', $ht);

		// hapus  detail
		$this->db->where('po_hd_id', $id);
		$this->db->delete('prc_po_dt');

		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("prc_po_dt", array(
				'po_hd_id' => $id,
				'pp_dt_id' => $value['pp_dt_id'],
				'item_id' => $value['item']['id'],
				'qty' => $value['qty'],
				'diskon' => $value['diskon'],
				'harga' => $value['harga'],
				'total' => $value['total'],
				'ket' => $value['ket'],
			));
		}

		return true;
	}

	public function revisi($id, $input)
	{
		$id = (int)$id;	
	
		$ht['revisi_ke'] =(int) $input['revisi_ke'];
		$ht['lokasi_id'] = $input['lokasi_id']['id'];
		$ht['lokasi_pp_id'] = $input['lokasi_pp_id']['id'];
		$ht['supplier_id'] = $input['supplier_id']['id'];
		$ht['syarat_bayar_id'] = $input['syarat_bayar_id']['id'];
		$ht['franco_id'] = $input['franco_id']['id'];
		$ht['mata_uang_id'] = $input['mata_uang_id']['id'];
		$ht['quotation_id'] = $input['quotation_id'];
		$ht['status_stok'] = $input['status_stok']['id'];
		$ht['no_po'] = $input['no_po'];
		$ht['catatan'] = $input['catatan'];
		$ht['ket_indent'] = $input['ket_indent'];
		$ht['info_pengiriman'] = $input['info_pengiriman'];
		$ht['catatan_revisi'] = $input['catatan_revisi'];
		$ht['tempo_bayar'] = $input['tempo_bayar'];
		// $ht['ket'] = $input['ket'];
		$ht['sub_total'] = $input['sub_total'];
		$ht['disc'] = $input['disc'];
		$ht['diskon'] = $input['diskon'];
		$ht['ppn'] = $input['ppn'];
		$ht['pph'] = $input['pph'];
		$ht['ppbkb'] = $input['ppbkb'];
		$ht['biaya_kirim'] = $input['biaya_kirim'];
		$ht['grand_total'] = $input['grand_total'];
		$ht['biaya_lain'] = $input['biaya_lain'];
		$ht['pph_nilai'] = $input['pph_nilai'];
		$ht['tanggal'] = $input['tanggal'];
		$ht['diubah_oleh'] = $input['diubah_oleh'];
		$input['diubah_tanggal'] = date('Y-m-d H:i:s');
		$this->db->where('id', $id);
		$this->db->update('prc_po_ht', $ht);

		// hapus  detail
		$this->db->where('po_hd_id', $id);
		$this->db->delete('prc_po_dt');

		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("prc_po_dt", array(
				'po_hd_id' => $id,
				'pp_dt_id' => $value['pp_dt_id'],
				'item_id' => $value['item']['id'],
				'qty' => $value['qty'],
				'diskon' => $value['diskon'],
				'harga' => $value['harga'],
				'total' => $value['total'],
				'ket' => $value['ket'],
			));
		}

		return true;
	}


	public function retrieve($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$result = $this->db->get('prc_po_ht', 1);
		return $result->row_array();
	}

	public function retrieve_detail($hdid)
	{
		// $this->db->select('est_spat_dt.*,gbm_organisasi.kode as kode_blok,gbm_organisasi.nama as nama_blok');
		$this->db->select('prc_po_dt.*,prc_pp_ht.no_pp');
		$this->db->from('prc_po_dt');
		$this->db->join('prc_pp_dt', 'prc_po_dt.pp_dt_id = prc_pp_dt.id', 'left');
		$this->db->join('prc_pp_ht', 'prc_pp_dt.pp_hd_id = prc_pp_ht.id', 'left');
		$this->db->where('po_hd_id', $hdid);
		$res = $this->db->get();
		return $res->result_array();
	}


	public function create($input)
	{
		$ht['mata_uang_id'] = $input['mata_uang_id']['id'];
		$ht['quotation_id'] = $input['quotation_id'];
		$ht['status_stok'] = $input['status_stok']['id'];
		// $ht['ttd_peminta'] = $input['ttd_peminta']['id'];
		// $ht['ttd_penyetuju'] = $input['ttd_penyetuju']['id'];
		$ht['lokasi_id'] = $input['lokasi_id']['id'];
		$ht['lokasi_pp_id'] = $input['lokasi_pp_id']['id'];
		$ht['supplier_id'] = $input['supplier_id']['id'];
		$ht['syarat_bayar_id'] = $input['syarat_bayar_id']['id'];
		$ht['franco_id'] = $input['franco_id']['id'];
		$ht['no_po'] = $input['no_po'];
		$ht['catatan'] = $input['catatan'];
		$ht['ket_indent'] = $input['ket_indent'];
		$ht['info_pengiriman'] = $input['info_pengiriman'];
		$ht['tempo_bayar'] = $input['tempo_bayar'];
		// $ht['ket'] = $input['ket'];
		$ht['sub_total'] = $input['sub_total'];
		$ht['disc'] = $input['disc'];
		$ht['diskon'] = $input['diskon'];
		$ht['ppn'] = $input['ppn'];
		$ht['pph'] = $input['pph'];
		$ht['ppbkb'] = $input['ppbkb'];
		$ht['biaya_kirim'] = $input['biaya_kirim'];
		$ht['grand_total'] = $input['grand_total'];
		$ht['tanggal'] = $input['tanggal'];
		$ht['dibuat_tanggal'] = date('Y-m-d H:i:s');
		$ht['dibuat_oleh'] = $input['dibuat_oleh'];
		$ht['biaya_lain'] = $input['biaya_lain'];
		$ht['pph_nilai'] = $input['pph_nilai'];

		$this->db->insert('prc_po_ht', $ht);
		$id = $this->db->insert_id();

		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("prc_po_dt", array(
				'po_hd_id' => $id,
				'pp_dt_id' => $value['pp_dt_id'],
				'item_id' => $value['item']['id'],
				'qty' => $value['qty'],
				'diskon' => $value['diskon'],
				'harga' => $value['harga'],
				'total' => $value['total'],
				'ket' => $value['ket'],
			));
		}

		return $id;
	}
	public function approval($id, $input)
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
			$ht[$field_status_approve] = 'APPROVED';
			$ht[$field_note_approve] = $input['note_approve'];
			$ht[$field_tanggal_approve] = date('Y-m-d H:i:s');
		}
		if ($is_finish == 0) {
			$ht[$field_next_approve] = $input['karyawan_id']['id'];
			$ht['status'] = $status;
			$ht['last_approve_position'] = $last_approve_position;
			$ht['last_approve_user'] = $input['karyawan_id']['id'];
		} else {
			$ht['status'] = 'RELEASE';
			$ht['last_approve_position'] = NULL;
			$ht['last_approve_user'] = NULL;
		}
		$ht['is_revisi']=0; /// DIBUAT NOL LAGI
		$ht['diubah_tanggal'] = date('Y-m-d H:i:s');
		$ht['diubah_oleh'] = $input['diubah_oleh'];
		$this->db->where('id', $id);
		$this->db->update('prc_po_ht', $ht);
		// $details = $input['details'];
		// foreach ($details as $key => $value) {
		// 	$field_qty = '';
		// 	if ($status == '') {
		// 		$field_qty = 'qty_pp';
		// 	} else if ($status == 'PO1') {
		// 		$field_qty = 'qty_PO1';
		// 	} else if ($status == 'PO2') {
		// 		$field_qty = 'qty_PO2';
		// 	} else if ($status == 'PO3') {
		// 		$field_qty = 'qty_PO3';
		// 	} else if ($status == 'PO4') {
		// 		$field_qty = 'qty_PO4';
		// 	} else if ($status == 'PO5') {
		// 		$field_qty = 'qty_PO5';
		// 	}
		// 	$dt = array(
		// 		'qty' => $value['qty'],
		// 		$field_qty => $value['qty']

		// 	);
		// 	$this->db->where('id', $value['id']);
		// 	$this->db->update("prc_pp_dt", $dt);
		// }

		return true;
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
		$this->db->update('prc_po_ht', $ht);

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
		$this->db->update('prc_po_ht', $data);


		return true;
	}

	public function retrieve_po_dtl_blm_terima($po_id)
	{
		$sql = "Select f.nama as lokasi, a.tanggal,a.no_po,a.catatan,b.id, b.item_id,c.kode as kode_item,c.nama as nama_item,d.kode as uom, b.qty,IFNULL(e.qty_terima, 0)as qty_sudah_terima,
		b.qty-IFNULL(e.qty_terima, 0)as qty_belum_terima ,  b.harga,b.diskon 
			from prc_po_ht a INNER JOIN prc_po_dt b ON a.id=b.po_hd_id
			INNER JOIN inv_item c on b.item_id=c.id 
			INNER join gbm_uom d on c.uom_id=d.id
			LEFT join (
			select po_dt_id,sum(qty)as qty_terima from inv_penerimaan_po_dt group by po_dt_id)e 
			on b.id=e.po_dt_id
			left join gbm_organisasi f on a.lokasi_id=f.id
			where a.status='RELEASE' and a.id=".$po_id ."
			and b.qty-IFNULL(e.qty_terima, 0)>0
			order by a.tanggal,a.no_po";
			$result=	$this->db->query($sql)->result_array();
			return $result;
	}
	public function retrieve_po_dtl_sdh_terima($po_id)
	{
		$sql = "Select f.nama as lokasi, a.tanggal,a.no_po,a.catatan,b.id, b.item_id,c.kode as kode_item,c.nama as nama_item,d.kode as uom, 
		b.qty,IFNULL(e.qty_terima, 0)as qty_sudah_terima,b.qty-IFNULL(e.qty_terima, 0)as qty_belum_terima, b.harga ,b.diskon 
			from prc_po_ht a INNER JOIN prc_po_dt b ON a.id=b.po_hd_id
			INNER JOIN inv_item c on b.item_id=c.id 
			INNER join gbm_uom d on c.uom_id=d.id
			LEFT join (
			select po_dt_id,sum(qty)as qty_terima from inv_penerimaan_po_dt group by po_dt_id)e 
			on b.id=e.po_dt_id
			left join gbm_organisasi f on a.lokasi_id=f.id
			where a.status='RELEASE' and a.id=".$po_id ."
			and IFNULL(e.qty_terima, 0)>0
			order by a.tanggal,a.no_po";
			$result=	$this->db->query($sql)->result_array();
			return $result;
	}
	public function retrieve_po_dtl($po_id)
	{
		$sql = "Select f.nama as lokasi, a.tanggal,a.no_po,a.catatan,b.id, b.item_id,c.kode as kode_item,c.nama as nama_item,d.kode as uom, 
		b.qty,IFNULL(e.qty_terima, 0)as qty_sudah_terima,b.qty-IFNULL(e.qty_terima, 0)as qty_belum_terima, b.harga ,b.diskon 
			from prc_po_ht a INNER JOIN prc_po_dt b ON a.id=b.po_hd_id
			INNER JOIN inv_item c on b.item_id=c.id 
			INNER join gbm_uom d on c.uom_id=d.id
			LEFT join (
			select po_dt_id,sum(qty)as qty_terima from inv_penerimaan_po_dt group by po_dt_id)e 
			on b.id=e.po_dt_id
			left join gbm_organisasi f on a.lokasi_id=f.id
			where a.id=".$po_id ."
			order by a.tanggal,a.no_po";
			$result=	$this->db->query($sql)->result_array();
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
		FROM prc_po_ht a 
        inner join pks_tanki b on a.tanki_id = b.id
        inner join inv_item c on a.produk_id = c.id
	    -- inner join inv_item c on b.item=c.id
	    -- inner join inv_gudang d on a.gudang_id=d.id
		
        where 1=1 and a.id=" . $id . ";";
		$data = $this->db->query($query)->row_array();
		return $data;
	}
}
