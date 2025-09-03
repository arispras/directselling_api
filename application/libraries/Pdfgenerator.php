<?php
defined('BASEPATH') or exit('No direct script access allowed');

// require_once("./application/third_party/dompdf/autoload.inc.php");
//require_once dirname(__FILE__).'/dompdf/src/autoloader.php';
require 'vendor/autoload.php';
require_once 'vendor/dompdf/dompdf_config.inc.php';

use Dompdf\Adapter\CPDF;
use Dompdf\Dompdf;
use Dompdf\Exception;

class Pdfgenerator
{

	public function generate($html, $filename = '', $stream = TRUE, $paper = 'A4', $orientation = "portrait")
	{
		$dompdf = new DOMPDF;
		$dompdf->loadHtml($html);
		$dompdf->setPaper($paper, $orientation);
		$dompdf->render();
			// $x          = 750	;
			// $y          = 50;
			// $text       = "{PAGE_NUM} of {PAGE_COUNT}";
			// $font       =null;// $dompdf->getFontMetrics()->get_font('Helvetica', 'normal');
			// $size       = 10;
			// $color      = array(0, 0, 0);
			// $word_space = 0.0;
			// $char_space = 0.0;
			// $angle      = 0.0;

			// $dompdf->getCanvas()->page_text(
			// 	$x,
			// 	$y,
			// 	$text,
			// 	$font,
			// 	$size,
			// 	$color,
			// 	$word_space,
			// 	$char_space,
			// 	$angle
			// );
		if ($stream) {
			
			$dompdf->stream($filename . ".pdf", array("Attachment" => 0));
		} else {
			return $dompdf->output();
		}
		// print_r("here");
	}
}
