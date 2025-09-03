<?php

class PrcPpModel extends CI_Model
{



	public function retrieve_all_kategori()
	{
		// $this->db->where('aktif' , 1);
		$this->db->order_by('id', 'ASC');
		$result = $this->db->get('prc_pp_ht');
		return $result->result_array();
	}


	public function delete($id)
	{
		$id = (int)$id;
		$this->db->where('pp_hd_id', $id);
		$this->db->delete('prc_pp_dt');
		$this->db->where('id', $id);
		$this->db->delete('prc_pp_ht');
		return true;
	}

	public function create($input)
	{
		$ht['lokasi_id'] = $input['lokasi_id']['id'];
		$ht['no_pp'] = $input['no_pp'];
		$ht['catatan'] = $input['catatan'];
		$ht['tanggal'] = $input['tanggal'];
		//$ht['diubah_tanggal'] = date('Y-m-d H:i:s');
		$ht['dibuat_tanggal'] = date('Y-m-d H:i:s');
		$ht['dibuat_oleh'] = $input['dibuat_oleh'];

		$this->db->insert('prc_pp_ht', $ht);
		$id = $this->db->insert_id();

		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("prc_pp_dt", array(
				'pp_hd_id' => $id,
				'item_id' => $value['item']['id'],
				'qty' => $value['qty'],
				'qty_pp' => $value['qty'],
				'ket' => $value['ket'],
			));
		}

		return $id;
	}

	public function update($id, $input)
	{
		$id = (int)$id;
		$ht['lokasi_id'] = $input['lokasi_id']['id'];
		$ht['no_pp'] = $input['no_pp'];
		$ht['catatan'] = $input['catatan'];
		$ht['tanggal'] = $input['tanggal'];
		$ht['diubah_tanggal'] = date('Y-m-d H:i:s');
		$ht['diubah_oleh'] = $input['diubah_oleh'];

		$this->db->where('id', $id);
		$this->db->update('prc_pp_ht', $ht);

		// hapus  detail
		$this->db->where('pp_hd_id', $id);
		$this->db->delete('prc_pp_dt');

		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("prc_pp_dt", array(
				'pp_hd_id' => $id,
				'item_id' => $value['item']['id'],
				'qty' => $value['qty'],
				'qty_pp' => $value['qty'],
				'ket' => $value['ket'],
			));
		}

		return true;
	}

	public function retrieve_by_id($id)
	{
		$this->db->select('*');
		$this->db->from('prc_pp_ht');
		$this->db->where('id', $id);
		$res = $this->db->get();
		return $res->row_array();
	}

	public function save_upload($inv, $file)
	{
		$id = (int) $inv['id'];
		$data['upload_file'] = $file;

		$this->db->where('id', $id);
		$this->db->update('prc_pp_ht', $data);

		return true;
	}

	public function closing($id, $input)
	{
		$id = (int)$id;
		
		$ht['status'] = $input['status'];
		$ht['diubah_tanggal'] = date('Y-m-d H:i:s');
		$ht['diubah_oleh'] = $input['diubah_oleh'];
		
		$x=$this->db->query("UPDATE prc_pp_ht  SET `status`='".$ht['status']."', `diubah_tanggal`='".$ht['diubah_tanggal']."', `diubah_oleh`='".$ht['diubah_oleh']."' WHERE id=".$id);

		return $x;
	}


	public function approval($id, $input)
	{
		$id = (int)$id;
		$status = $input['status'];
		$is_ready_po = $input['is_ready_po'];
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
			$last_approve_position = 'PP1';
		} else if ($status == 'PP1') {
			$field_next_approve = 'user_approve2';
			$field_status_approve = 'status_approve1';
			$field_note_approve = 'note_approve1';
			$field_tanggal_approve = 'tgl_approve1';
			$last_approve_position = 'PP2';
		} else if ($status == 'PP2') {
			$field_next_approve = 'user_approve3';
			$field_status_approve = 'status_approve2';
			$field_note_approve = 'note_approve2';
			$field_tanggal_approve = 'tgl_approve2';
			$last_approve_position = 'PP3';
		} else if ($status == 'PP3') {
			$field_next_approve = 'user_approve4';
			$field_status_approve = 'status_approve3';
			$field_note_approve = 'note_approve3';
			$field_tanggal_approve = 'tgl_approve3';
			$last_approve_position = 'PP4';
		} else if ($status == 'PP4') {
			$field_next_approve = 'user_approve5';
			$field_status_approve = 'status_approve4';
			$field_note_approve = 'note_approve4';
			$field_tanggal_approve = 'tgl_approve4';
			$last_approve_position = 'PP5';
		} else if ($status == 'PP5') {
			$field_next_approve = 'user_approve6';
			$field_status_approve = 'status_approve5';
			$field_note_approve = 'note_approve5';
			$field_tanggal_approve = 'tgl_approve5';
			$last_approve_position = 'PP6';
		}

		$ht['proses_approval'] = 1;

		
		if ($status != '') {
			$ht[$field_status_approve] = 'APPROVED';
			$ht[$field_note_approve] = $input['note_approve'];
			$ht[$field_tanggal_approve] = date('Y-m-d H:i:s');
		}
		if ($is_ready_po == 0) {
			$ht[$field_next_approve] = $input['karyawan_id']['id'];
			$ht['status'] = $status;
			$ht['last_approve_position'] = $last_approve_position;
			$ht['last_approve_user'] = $input['karyawan_id']['id'];
		} else {
			$ht['status'] = 'READY_PO';
			$ht['last_approve_position'] = NULL;
			$ht['last_approve_user'] = NULL;
		}

		$ht['diubah_tanggal'] = date('Y-m-d H:i:s');
		$ht['diubah_oleh'] = $input['diubah_oleh'];
		$this->db->where('id', $id);
		$this->db->update('prc_pp_ht', $ht);
		$details = $input['details'];
		foreach ($details as $key => $value) {
			$field_qty = '';
			if ($status == '') {
				$field_qty = 'qty_pp';
			} else if ($status == 'PP1') {
				$field_qty = 'qty_pp1';
			} else if ($status == 'PP2') {
				$field_qty = 'qty_pp2';
			} else if ($status == 'PP3') {
				$field_qty = 'qty_pp3';
			} else if ($status == 'PP4') {
				$field_qty = 'qty_pp4';
			} else if ($status == 'PP5') {
				$field_qty = 'qty_pp5';
			}
			$dt = array(
				'qty' => $value['qty'],
				$field_qty => $value['qty']

			);
			$this->db->where('id', $value['id']);
			$this->db->update("prc_pp_dt", $dt);
		}

		return true;
	}
	public function reject($id, $input)
	{
		$id = (int)$id;
		$status = $input['status'];
		$is_ready_po = $input['is_ready_po'];
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
			$last_approve_position = 'PP1';
		} else if ($status == 'PP1') {
			$field_next_approve = 'user_approve2';
			$field_status_approve = 'status_approve1';
			$field_note_approve = 'note_approve1';
			$field_tanggal_approve = 'tgl_approve1';
			$last_approve_position = 'PP2';
		} else if ($status == 'PP2') {
			$field_next_approve = 'user_approve3';
			$field_status_approve = 'status_approve2';
			$field_note_approve = 'note_approve2';
			$field_tanggal_approve = 'tgl_approve2';
			$last_approve_position = 'PP3';
		} else if ($status == 'PP3') {
			$field_next_approve = 'user_approve4';
			$field_status_approve = 'status_approve3';
			$field_note_approve = 'note_approve3';
			$field_tanggal_approve = 'tgl_approve3';
			$last_approve_position = 'PP4';
		} else if ($status == 'PP4') {
			$field_next_approve = 'user_approve5';
			$field_status_approve = 'status_approve4';
			$field_note_approve = 'note_approve4';
			$field_tanggal_approve = 'tgl_approve4';
			$last_approve_position = 'PP5';
		} else if ($status == 'PP5') {
			$field_next_approve = 'user_approve6';
			$field_status_approve = 'status_approve5';
			$field_note_approve = 'note_approve5';
			$field_tanggal_approve = 'tgl_approve5';
			$last_approve_position = 'PP6';
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
		// if ($is_ready_po == 0) {
		// 	$ht[$field_next_approve] = $input['karyawan_id']['id'];
		// 	$ht['status'] = $status;
		// 	$ht['last_approve_position'] = $last_approve_position;
		// 	$ht['last_approve_user'] = $input['karyawan_id']['id'];
		// } else {
		// 	$ht['status'] = 'READY_PO';
		// 	$ht['last_approve_position'] = NULL;
		// 	$ht['last_approve_user'] = NULL;
		// }

		$ht['diubah_tanggal'] = date('Y-m-d H:i:s');
		$ht['diubah_oleh'] = $input['diubah_oleh'];
		$this->db->where('id', $id);
		$this->db->update('prc_pp_ht', $ht);
		// $details = $input['details'];
		// foreach ($details as $key => $value) {
		// 	$field_qty = '';
		// 	if ($status == '') {
		// 		$field_qty = 'qty_pp';
		// 	} else if ($status == 'PP1') {
		// 		$field_qty = 'qty_pp1';
		// 	} else if ($status == 'PP2') {
		// 		$field_qty = 'qty_pp2';
		// 	} else if ($status == 'PP3') {
		// 		$field_qty = 'qty_pp3';
		// 	} else if ($status == 'PP4') {
		// 		$field_qty = 'qty_pp4';
		// 	} else if ($status == 'PP5') {
		// 		$field_qty = 'qty_pp5';
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
	public function retrieve($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$result = $this->db->get('prc_pp_ht', 1);
		return $result->row_array();
	}

	public function retrieve_detail($hdid)
	{
		// $this->db->select('est_spat_dt.*,gbm_organisasi.kode as kode_blok,gbm_organisasi.nama as nama_blok');
		$this->db->select('*');
		$this->db->from('prc_pp_dt');
		// $this->db->join('gbm_organisasi', 'est_spat_dt.blok_id = gbm_organisasi.id');
		$this->db->where('pp_hd_id', $hdid);
		$res = $this->db->get();
		return $res->result_array();
	}

	public function retrieve_all_detail()
	{
			$this->db->order_by('id', 'ASC');
			$this->db->select('a.*, b.no_pp, c.nama AS item');
			$this->db->from('prc_pp_dt a');
			$this->db->join('prc_pp_ht b', 'a.pp_hd_id = b.id');
			$this->db->join('inv_item c', 'a.item_id = c.id');
			$result = $this->db->get();
			return $result->result_array();
	}

	public function retrieve_all_detail_by_status()
	{
			
			$result=	$this->db->query("Select f.nama as lokasi, a.tanggal,a.no_pp,a.catatan,b.id, b.item_id,c.kode as kode_item,c.nama as nama_item,d.kode as uom, b.qty,IFNULL(e.qty_po, 0)qty_sudah_po,b.qty-IFNULL(e.qty_po, 0)as qty_belum_po 
			from prc_pp_ht a INNER JOIN prc_pp_dt b ON a.id=b.pp_hd_id
			INNER JOIN inv_item c on b.item_id=c.id 
			INNER join gbm_uom d on c.uom_id=d.id
			LEFT join (
			 select pp_dt_id,sum(qty)as qty_po from prc_po_dt group by pp_dt_id)e 
			 on b.id=e.pp_dt_id
			 left join gbm_organisasi f on a.lokasi_id=f.id
			 where a.status='READY_PO'
			 and b.qty-IFNULL(e.qty_po, 0)>0
			 order by a.tanggal,a.no_pp"
			 )->result_array();
			return $result;
	}
	public function retrieve_all_detail_lokasi_by_status($lokasi_id)
	{
			
			$result=	$this->db->query("Select f.nama as lokasi, a.tanggal,a.no_pp,a.catatan,b.id, b.item_id,b.ket, c.kode as kode_item,c.nama as nama_item,d.kode as uom, b.qty,IFNULL(e.qty_po, 0)qty_sudah_po,b.qty-IFNULL(e.qty_po, 0)as qty_belum_po 
			from prc_pp_ht a INNER JOIN prc_pp_dt b ON a.id=b.pp_hd_id
			INNER JOIN inv_item c on b.item_id=c.id 
			INNER join gbm_uom d on c.uom_id=d.id
			LEFT join (
			 select pp_dt_id,sum(qty)as qty_po from prc_po_dt group by pp_dt_id)e 
			 on b.id=e.pp_dt_id
			 left join gbm_organisasi f on a.lokasi_id=f.id
			 where a.status='READY_PO'
			 and a.lokasi_id=".$lokasi_id."
			 and b.qty-IFNULL(e.qty_po, 0)>0
			 order by a.tanggal,a.no_pp"
			 )->result_array();
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
		FROM prc_pp_ht a 
        inner join pks_tanki b on a.tanki_id = b.id
        inner join inv_item c on a.produk_id = c.id
	    -- inner join inv_item c on b.item=c.id
	    -- inner join inv_gudang d on a.gudang_id=d.id
		
        where 1=1 and a.id=" . $id . ";";
		$data = $this->db->query($query)->row_array();
		return $data;
	}
}
