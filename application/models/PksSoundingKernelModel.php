<?php


class PksSoundingKernelModel extends CI_Model
{

   

    //  Method untuk mendapatkan semua data pks_sounding_kernel tanpa pagging

    public function retrieve_all_kategori()
    {
        $this->db->order_by('name', 'ASC');
        $result = $this->db->get('pks_sounding_kernel');
        return $result->result_array();
    }


    //   Method untuk menghapus record pks_sounding_kernel

    public function delete($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $this->db->delete('pks_sounding_kernel');
        return true;
    }


    public function update($id, $input)
    {
        $id = (int)$id;


        $data = array(
            
            'mill_id' => $input['mill_id']['id'],
			'tanki_id' => $input['tanki_id']['id'],
            'tanggal' => $input['tanggal'],
         
            'hasil_ukur_a' => $input['hasil_ukur_a'],
            'hasil_ukur_b' => $input['hasil_ukur_b'],
            'hasil_ukur_c' => $input['hasil_ukur_c'],
            'hasil_ukur_d' => $input['hasil_ukur_d'],
            
            'stok_a' => $input['stok_a'],
            'stok_b' => $input['stok_b'],
            'stok_c' => $input['stok_c'],
            'stok_d' => $input['stok_d'],
            
            'hasil_sounding' => $input['hasil_sounding'],

            'diubah_oleh'=> $input['diubah_oleh'],
            'diubah_tanggal'=> date('Y-m-d H:i:s'),
        );
        $this->db->where('id', $id);
        $this->db->update('pks_sounding_kernel', $data);
        return true;
    }


    //  Method untuk mengambil satu record pks_sounding_kernel

    public function retrieve($id)
    {
        $id = (int)$id;

        $this->db->where('id', $id);
        $result = $this->db->get('pks_sounding_kernel', 1);
        return $result->row_array();
    }


    //  Method untuk membuat data pks_sounding_kernel

    public function create($input)
    {
        $data = array(

            'tanki_id' => $input['tanki_id']['id'],			
            'mill_id' => $input['mill_id']['id'],
            'tanggal' => $input['tanggal'],
        
            'hasil_ukur_a' => $input['hasil_ukur_a'],
            'hasil_ukur_b' => $input['hasil_ukur_b'],
            'hasil_ukur_c' => $input['hasil_ukur_c'],
            'hasil_ukur_d' => $input['hasil_ukur_d'],
            
            'stok_a' => $input['stok_a'],
            'stok_b' => $input['stok_b'],
            'stok_c' => $input['stok_c'],
            'stok_d' => $input['stok_d'],
            
            'hasil_sounding' => $input['hasil_sounding'],

            'dibuat_oleh'=> $input['dibuat_oleh'],
            'dibuat_tanggal'=> date('Y-m-d H:i:s'),
        );
        $this->db->insert('pks_sounding_kernel', $data);
        return $this->db->insert_id();
    }
	public function process_sounding( $input )
	{
		// $tanki = $this->db->query("SELECT * FROM pks_tanki WHERE id=".$input['tanki_id'])->row();
		
		// $meja_ukur = $tanki->meja_ukur;
		// $tinggi = ($input['sounding'] * 10) + $meja_ukur;
		
		// $parse = (string) $tinggi;
		// $last_num = $parse[strlen($parse) - 1];
		// $norm_num = substr($parse, 0, strlen($parse)-1)."0";
		
		$area_A = $this->db->query("SELECT * FROM pks_tanki_formula_kernel WHERE area='A'")->row_array();
		$area_B = $this->db->query("SELECT * FROM pks_tanki_formula_kernel WHERE area='B'")->row_array();;
		$area_C = $this->db->query("SELECT * FROM pks_tanki_formula_kernel WHERE area='C'")->row_array();;
		$area_D = $this->db->query("SELECT * FROM pks_tanki_formula_kernel WHERE area='D'")->row_array();;
		
		$tinggi_kernel_A=$area_A['tinggi_kubus']-$input['hasil_ukur_A'];
		$tinggi_kernel_B=$area_B['tinggi_kubus']-$input['hasil_ukur_B'];
		$tinggi_kernel_C=$area_C['tinggi_kubus']-$input['hasil_ukur_C'];
		$tinggi_kernel_D=$area_D['tinggi_kubus']-$input['hasil_ukur_D'];
		$volume_A=	($tinggi_kernel_A*$area_A['panjang']*$area_A['lebar'])/1000000;
		$volume_B=	($tinggi_kernel_B*$area_B['panjang']*$area_B['lebar'])/1000000;
		$volume_C=	($tinggi_kernel_C*$area_A['panjang']*$area_C['lebar'])/1000000;
		$volume_D=	($tinggi_kernel_D*$area_D['panjang']*$area_D['lebar'])/1000000;
		$stok_kubus_A=	$volume_A*$area_A['berat_jenis']*1000;
		$stok_kubus_B=	$volume_B*$area_B['berat_jenis']*1000;
		$stok_kubus_C=	$volume_C*$area_C['berat_jenis']*1000;
		$stok_kubus_D=	$volume_D*$area_D['berat_jenis']*1000;
		$jumlah_stok_A=$stok_kubus_A +$area_A['stock_limas'];
		$jumlah_stok_B=$stok_kubus_B +$area_B['stock_limas'];
		$jumlah_stok_C=$stok_kubus_C +$area_C['stock_limas'];
		$jumlah_stok_D=$stok_kubus_D +$area_D['stock_limas'];
		$total_stok=	$jumlah_stok_A+	$jumlah_stok_B+	$jumlah_stok_C+	$jumlah_stok_D;

		$return = [
			'jumlah_stok_A'=>$jumlah_stok_A,
			'jumlah_stok_B'=>$jumlah_stok_B,
			'jumlah_stok_C'=> $jumlah_stok_C,
			'jumlah_stok_D'=>$jumlah_stok_D,
			'total_stok'=> $total_stok,
			'hasil_ukur_A'=> $input['hasil_ukur_A'],
			'hasil_ukur_B'=> $input['hasil_ukur_B'],
			'hasil_ukur_C'=> $input['hasil_ukur_C'],
			'hasil_ukur_D'=> $input['hasil_ukur_D']
			
		];
		return $return;
	}
}
