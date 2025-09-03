<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
use Restserver\Libraries\REST_Controller;

class Asset extends Rest_Controller
{
    function __construct()
    {
        parent::__construct();
		$this->load->model('AssetModel');
		$this->load->model('M_DatatablesModel');
		
    }

	public function list_post()
	{
		$post = $this->post();

		$query  = "select a.*,b.nama as kategori,c.nama as tipe,d.nama as lokasi,e.nama as nama_pengguna,e.nip from asset a 
		inner join asset_kategori b on a.asset_kategori_id= b.id
		inner join asset_tipe c on a.asset_tipe_id=c.id
		inner join asset_lokasi d on a.asset_lokasi_id=d.id
		left join pengajar e on a.pengguna_id=e.id";
		$search = array('a.kode', 'b.nama','c.nama','d.nama','e.nama');
		$where  = null;
	   
		$isWhere = null;
		// $isWhere = 'artikel.deleted_at IS NULL';

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
    function index_get($segment_3 = '')
    {
		$id = $segment_3;
		$retrieve = $this->AssetModel->retrieve($id);
		
		if (!empty($retrieve)) {
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		} else {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		}
     }
	 function getAll_get()
	 {
		
		 $retrieve = $this->AssetModel->retrieve_all_asset();
				 
		 if (!empty($retrieve)) {
			 $this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);
		 } else {
			 $this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);
		 }
	  }
    function index_post()
    {
		

		$retrieve=  $this->AssetModel->create($this->post());
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);

	}

    function index_put($segment_3 = '')
    {
      

        $id = (int)$segment_3;
        $item = $this->AssetModel->retrieve( $id);
        if (empty($item)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);

        }

         $retrieve=   $this->AssetModel->update($item['id'], $this->put());
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);

          

      
    }

    function index_delete($segment_3 = '')
    {
      
		$id = (int)$segment_3;
        $item = $this->AssetModel->retrieve ($id);
        if (empty($item)) {
			$this->set_response(array("status" => "NOT OK", "data" => "Tidak ada Data"), REST_Controller::HTTP_NOT_FOUND);

        }

         $retrieve=  $this->AssetModel->delete($item['id']);
			$this->set_response(array("status" => "OK", "data" => $retrieve), REST_Controller::HTTP_OK);


    }

    private function formatData($val)
    {


        if (!empty($val['inv_kategori_id'])) {
            $inv_kategori = $this->invkategori_model->retrieve($val['inv_kategori_id']);
            $val['inv_kategori'] = $inv_kategori;
        }

        return $val;
    }

    
   

    function add($segment_3 = '')
    {
        # harus admin atau pengajar
        if (!is_admin() and !is_pengajar()) {
            $this->session->set_flashdata('inv', get_alert('warning', 'Akses ditolak.'));
            redirect('inv');
        }


         $data['inv_kategori']   = $this->invkategori_model->retrieve_all_invkategori();
        $data['comp_js'] = get_texteditor();


        # jika ada post filter
        if ($this->form_validation->run('invitem/add') == true) {

            $input = $this->input->post();

            $input['kode'] = $input['kode'];
            $input['nama'] = $input['nama'];
            $input['inv_kategori_id'] = $input['inv_kategori_id'];
            $input['satuan'] = $input['satuan'];
            $this->invitem_model->create($input);

            $this->session->set_flashdata(' Item', get_alert('success', 'inv berhasil disimpan.'));
            redirect('invitem');
        }


        $this->twig->display('tambah-invitem.html', $data);
    }

    function edit($segment_3 = '', $segment_4 = '')
    {


        $invitem_id = (int)$segment_3;
        $uri_back  = (string)$segment_4;

        if (empty($uri_back)) {
            $uri_back = site_url('invitem');
        } else {
            $uri_back = deurl_redirect($uri_back);
        }
        $data['uri_back'] = $uri_back;



        $invitem = $this->invitem_model->retrieve($invitem_id);
        if (empty($invitem)) {
            redirect($uri_back);
        }


        $data['inv_kategori']   = $this->invkategori_model->retrieve_all_invkategori();
        $data['invitem']   =$invitem;
        $data['comp_js'] = get_texteditor();

        # post action
        $success = false;

        if ($this->form_validation->run('invitem/add') == TRUE) {

            $input = $this->input->post();

            $input['kode'] = $input['kode'];
            $input['nama'] = $input['nama'];
            $input['inv_kategori_id'] = $input['inv_kategori_id'];
            $input['satuan'] = $input['satuan'];
            $input['id'] = $invitem_id;

            $this->invitem_model->update($input);
            $success = true;
        }

        if ($success) {
            $this->session->set_flashdata('inv', get_alert('success', 'inv berhasil diperbaharui.'));
            redirect($uri_back);
        }
        $this->twig->display('edit-invitem.html', $data);
    }




    function delete($segment_3 = '', $segment_4 = '')
    {
        # versi 1.2 siswa tidak bisa tambah,edit,hapus inv
        if (is_siswa()) {
            redirect('invitem');
        }

        $invitem_id = (int)$segment_3;
        $uri_back  = (string)$segment_4;

        if (empty($uri_back)) {
            $uri_back = site_url('invitem');
        } else {
            $uri_back = deurl_redirect($uri_back);
        }

        $invitem = $this->invitem_model->retrieve($invitem_id);
        if (empty($invitem)) {
            redirect($uri_back);
        }

        // # cek kepemilikan
        // if (is_pengajar() and $inv['pengajar_id'] != get_sess_data('user', 'id')) {
        //     redirect($uri_back);
        // }

        if (is_siswa()) {
            redirect($uri_back);
        }


        $this->invitem_model->delete($invitem['id']);

        $this->session->set_flashdata('inv', get_alert('warning', 'inv Item berhasil dihapus.'));
        redirect($uri_back);
    }

   
}
