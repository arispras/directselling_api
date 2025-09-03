<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 class thermo{
	
	function __construct()
	{
		$this->load->library(array('form_validation', 'parser', 'image_lib', 'upload', 'user_agent', 'email'));
	}
	
 function create_img_thumb($source_path = '', $marker = '_thumb', $width = '90', $height = '90')
    {
        $config['image_library']  = 'gd2';
        $config['source_image']   = $source_path;
        $config['create_thumb']   = TRUE;
        $config['maintain_ratio'] = TRUE;
        $config['width']          = $width;
        $config['height']         = $height;
        $config['thumb_marker']   = $marker;

        $this->image_lib->initialize($config);
        $this->image_lib->resize();
        $this->image_lib->clear();
        unset($config);

        return true;
    }
 }
