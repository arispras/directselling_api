<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
use Restserver\Libraries\REST_Controller;

class SendEmail extends Rest_Controller
{
    function __construct()
    {
        parent::__construct();
	
		$this->load->library('email');
	
		
    }

	public function test_get()
	{
		//echo " here";
		$email_subject = 'Info Notifikasi Approval PP';
		$email_body    = 'Hallo, ini test pengiriman PP';
		$email_server  = 'arispras@gmail.com';
		$nama_company  = 'Antech';
	
		// $CI =& get_instance();
		// var_dump($CI);
	// $this->email->clear(true);
	
		// $config['mailtype'] = 'html';
		# cek pakai smtp tidak
		// $smtp_host = get_pengaturan('smtp-host', 'value');
		// $smtp_user = get_pengaturan('smtp-username', 'value');
		// $smtp_pass = get_pengaturan('smtp-pass', 'value');
		// $smtp_port = get_pengaturan('smtp-port', 'value');
		// $smtp_host= 'ssl://smtp.googlemail.com';
		// $smtp_port = 465;
		// $smtp_user= 'arispras';
		// $smtp_pass = 'xxx';
		// if (!empty($smtp_host)) {
		// 	$config['protocol']  = 'smtp';
		// 	$config['smtp_host'] = $smtp_host;
		// 	$config['smtp_user'] = $smtp_user;
		// 	$config['smtp_pass'] = $smtp_pass;
	
		// 	# cek port
		// 	if (!empty($smtp_port)) {
		// 		$config['smtp_port'] = $smtp_port;
		// 	}
		// }
		// $config = Array(
		// 	'protocol' => 'smtp',
		// 	'smtp_host' => 'ssl://smtp.googlemail.com',
		// 	'smtp_port' => 465,
		// 	// 'smtp_timeout'=>30,
		// 	'smtp_user' => 'cs.antechindonesia@gmail.com',
		// 	'smtp_pass' => '4nt3ch2022',
		// 	'mailtype'  => 'html', 
		// 	'charset'   => 'utf-8',
		// 	// 'smtp_crypto' => 'tls', 
		// 	'wordwrap'  => TRUE
		// );

		// $config = Array(
		// 	'protocol' => 'smtp',
		// 	'smtp_host' => 'mail.antech-indonesia.com',
		// 	'smtp_port' => 465,
		// 	'smtp_user' => 'support@antech-indonesia.com',
		// 	'smtp_pass' => '4nt3chIndonesia',
		// 	'mailtype'  => 'html', 
		// 	// 'charset'   => 'utf-8',
		// 	 'smtp_crypto' => 'ssl', 
		// 	 'wordwrap'  => TRUE
		// );
		// // $this->load->library('email', $config);
		// $this->email->initialize($config);
		$this->email->set_newline("\r\n");
		$this->email->to('arispras@gmail.com');
		$this->email->from("support@antech-indonesia.com");
		$this->email->subject($email_subject);
		$this->email->message($email_body);
		if ( ! $this->email->send()) {
			show_error($this->email->print_debugger());
		} else{
			echo "email sent";
		}
		// $this->email->clear(true);
	
		//test_send_email();
	}
	public function list_post()
	{
		$post = $this->post();

		$query  = "SELECT * from acc_akun ";
		$search = array('kode', 'nama');
		$where  = null;
	   
		$isWhere = null;
		// $isWhere = 'artikel.deleted_at IS NULL';

		$data = $this->M_DatatablesModel->get_tables_query($query, $search, $where, $isWhere, $post);
		$this->set_response($data, REST_Controller::HTTP_OK);
	}
    function index_get($segment_3 = '')
    {
		
     }
	 function getAll_get()
	 {
		
		
	  }
	  function getAllDetail_get()
	 {
		
		
	  }
    function index_post()
    {


	}

    function index_put($segment_3 = '')
    {
      
    }

    function index_delete($segment_3 = '')
    {
    }

    function detail($segment_3 = '')
    {
       
    }
}
