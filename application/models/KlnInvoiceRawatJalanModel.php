<?php

class KlnInvoiceRawatJalanModel extends CI_Model
{


	public function retrieve_all()
	{
		// $this->db->where('aktif' , 1);
		$this->db->order_by('id', 'ASC');
		$result = $this->db->get('kln_invoice_rawat_jalan');
		return $result->result_array();
	}


	public function delete($id)
	{
		$id = (int)$id;
		$this->db->where('invoice_id', $id);
		$this->db->delete('kln_invoice_rawat_jalan_dt');
		$this->db->where('id', $id);
		$this->db->delete('kln_invoice_rawat_jalan_ht');
		return true;
	}


	public function update($id, $input)
	{
		$id = (int)$id;
		$ht['lokasi_id'] = $input['lokasi_id']['id'];
		// $ht['pasien_id'] = $input['pasien_id']['id'];
		// $ht['dokter_id'] = $input['dokter_id']['id'];
		// $ht['catatan'] = $input['catatan'];
		$ht['no_transaksi'] = $input['no_transaksi'];
		$ht['tanggal'] = $input['tanggal'];
		$ht['sub_total'] = $input['subtotal'];
		$ht['diubah_oleh'] = $input['diubah_oleh'];
		$ht['diubah_tanggal'] = date("Y-m-d H:i:s");


		$this->db->where('id', $id);
		$this->db->update('kln_invoice_rawat_jalan_ht', $ht);

		// hapus  detail
		$this->db->where('invoice_id', $id);
		$this->db->delete('kln_invoice_rawat_jalan_dt');

		$this->db->where('invoice_id', $id);
		$this->db->delete('kln_invoice_rawat_jalan_bayar');

		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("kln_invoice_rawat_jalan_dt", array(
				'invoice_id' => $id,
				'biaya_id' => $value['biaya_id']['id'],
				'harga' => $value['harga'],
				'ket' => $value['ket'],
				
			));
		}
		$detailsPembayaran = $input['details_pembayaran'];
		foreach ($detailsPembayaran as $key => $value) {
			$this->db->insert("kln_invoice_rawat_jalan_bayar", array(
				'invoice_id' => $id,
				'tipe_bayar_id' => $value['tipe_bayar_id']['id'],
				'jumlah' => $value['jumlah'],
				'ket' => $value['ket'],
				
			));
		}
		return true;
	}

	public function posting($id,	$input)
	{
		$id = (int)$id;
		$data['is_posting'] = 1;
		$data['diposting_tanggal'] = date('Y-m-d H:i:s');
		$data['diposting_oleh'] = $input['diposting_oleh'];
		$this->db->where('id', $id);
		$this->db->update('kln_invoice_rawat_jalan', $data);	

		return true;
	}
	public function retrieve($id)
	{
		$id = (int)$id;

		$this->db->where('id', $id);
		$result = $this->db->get('kln_invoice_rawat_jalan_ht', 1);
		return $result->row_array();
	}

	public function retrieve_detail($hdid)
	{
	
		$this->db->select('a.*,b.nama as nama_biaya');
		$this->db->from('kln_invoice_rawat_jalan_dt a');
		$this->db->join('acc_kegiatan b', 'a.biaya_id = b.id', 'Left');
		$this->db->where('a.invoice_id', $hdid);
		$res = $this->db->get();
		return $res->result_array();
	}
	public function retrieve_detail_pembayaran($hdid)
	{
	
		$this->db->select('a.*,b.nama as nama_pembayaran');
		$this->db->from('kln_invoice_rawat_jalan_bayar a');
		$this->db->join('kln_tipe_bayar b', 'a.tipe_bayar_id = b.id', 'Left');
		$this->db->where('a.invoice_id', $hdid);
		$res = $this->db->get();
		return $res->result_array();
	}

	public function create($input)
	{
		$ht['lokasi_id'] = $input['lokasi_id']['id'];
		$ht['rawat_jalan_id'] = $input['rawat_jalan_id']['id'];
		$ht['no_transaksi'] = $input['no_transaksi'];
		$ht['tanggal'] = $input['tanggal'];
		$ht['sub_total'] = $input['subtotal'];
		$ht['dibuat_oleh'] = $input['dibuat_oleh'];
		$ht['dibuat_tanggal'] = date("Y-m-d H:i:s");
		$ht['diubah_oleh'] = $input['diubah_oleh'];
		$ht['diubah_tanggal'] = date("Y-m-d H:i:s");

		$this->db->insert('kln_invoice_rawat_jalan_ht', $ht);
		$id = $this->db->insert_id();

		$details = $input['details'];
		foreach ($details as $key => $value) {
			$this->db->insert("kln_invoice_rawat_jalan_dt", array(
				'invoice_id' => $id,
				'biaya_id' => $value['biaya_id']['id'],
				'harga' => $value['harga'],
				'ket' => $value['ket'],
				
			));
		}
		$detailsPembayaran = $input['details_pembayaran'];
		foreach ($detailsPembayaran as $key => $value) {
			$this->db->insert("kln_invoice_rawat_jalan_bayar", array(
				'invoice_id' => $id,
				'tipe_bayar_id' => $value['tipe_bayar_id']['id'],
				'jumlah' => $value['jumlah'],
				'ket' => $value['ket'],
				
			));
		}
		return $id;
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
		FROM kln_invoice_rawat_jalan a 
        inner join pks_tanki b on a.tanki_id = b.id
        inner join kln_jenis_diagnosa c on a.produk_id = c.id
	    -- inner join kln_jenis_diagnosa c on b.jenis_diagnosa=c.id
	    -- inner join inv_gudang d on a.rawat_jalan_id=d.id
		
        where 1=1 and a.id=" . $id . ";";
		$data = $this->db->query($query)->row_array();
		return $data;
	}
}
