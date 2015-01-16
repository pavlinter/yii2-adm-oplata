<?php

/**
 * @package yii2-adm-oplata
 * @author Pavels Radajevs <pavlinter@gmail.com>
 * @copyright Copyright &copy; Pavels Radajevs <pavlinter@gmail.com>, 2015
 * @version 1.0.0
 */

namespace pavlinter\admoplata;

use Yii;

/**
 * PdfResponseFormatter formats the given HTML data into a PDF response content.
 */
class PdfResponseFormatter extends \robregonm\pdf\PdfResponseFormatter
{
	public $defaultFontSize = 10;

	public $defaultFont = 'DejaVuSansCondensed';

	public $marginLeft = 5;

	public $marginRight = 5;

	public $marginTop = 5;

	public $marginBottom = 5;

	public $marginHeader = 0;

	public $marginFooter = 0;
	/**
	 * @var string 'Landscape' or 'Portrait'
	 * Default to 'Portrait'
	 */
	public $orientation = 'Landscape';
}
