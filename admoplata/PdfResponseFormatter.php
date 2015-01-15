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

	/**
	 * Formats response HTML in PDF
	 *
	 * @param Response $response
	 */
	protected function formatPdf($response)
	{
		$mpdf = new \mPDF($this->mode,
			$this->format,
			$this->defaultFontSize,
			$this->defaultFont,
			$this->marginLeft,
			$this->marginRight,
			$this->marginTop,
			$this->marginBottom,
			$this->marginHeader,
			$this->marginFooter,
			$this->orientation
		);

		foreach ($this->options as $key => $option) {
			$mpdf->$key = $option;
		}

		if ($this->beforeRender instanceof \Closure) {
			call_user_func($this->beforeRender, $mpdf, $response->data);
		}

		$mpdf->WriteHTML($response->data);
		return $mpdf->Output('', 'S');
	}
}
