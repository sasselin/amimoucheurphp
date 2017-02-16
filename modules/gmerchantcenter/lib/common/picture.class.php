<?php
/**
 * picture.class.php file defines methods to manage images an dis an override of php_gd2
 */

class BT_Picture
{
	/**
	 * @var resource $_rId : store the current image resource
	 */
	private $_rId = null;

	/**
	 * @var int $_iFormat : set image format
	 */
	private $_iFormat = null;

	/**
	 * @var int $_iWidth : set image width
	 */
	private $_iWidth = null;

	/**
	 * @var int $_iHeight : set image height
	 */
	private $_iHeight = null;

	/**
	 * @var string $_sMime : set mime type
	 */
	private $_sMime = null;

	/**
	 * @var int $_iImageTransparent : set image transparent
	 */
	private $_iImageTransparent = null;

	/**
	 * __construct()
	 *
	 */
	public function __construct() {} // __construct()

	/**
	 * loadFile() method read specified file as parameter. If the file type is do not handle as available type as jpg, gif and png, exception is thrown
	 *
	 * @param string $sFilePath : file path
	 */
	public function loadFile($sFilePath)
	{
		// verify if file exist
		if (! file_exists($sFilePath)) {
			throw new Exception('File ' . $sFilePath . ' not found',301);
		}
		// load image infos
		$infos = @getimagesize ($sFilePath);

		// get image infos : size
		$this->_iWidth = $infos[0];
		$this->_iHeight = $infos[1];

		// get image infos : format
		$this->_sMime = $infos['mime'];
		$this->_iFormat = $infos[2];

		switch ($infos[2]) {
			case IMAGETYPE_GIF:
				$this->_iFormat = IMAGETYPE_GIF;
				$this->_rId = imagecreatefromgif($sFilePath);
				break;
			case IMAGETYPE_JPEG: // IMAGETYPE_JPEG2000
				$this->_iFormat = IMAGETYPE_JPEG;
				$this->_rId = imagecreatefromjpeg($sFilePath);
				break;
			case IMAGETYPE_PNG:
				$this->_iFormat = IMAGETYPE_PNG;
				$this->_rId = imagecreatefrompng($sFilePath);
				break;
			default:
				throw new Exception('Image type ' . $infos[2] . '(' . $infos['mime'] . ') not suported',302);
				break;
		}
		// create transparent ressource
		$this->_iImageTransparent = imagecolortransparent($this->_rId);
	}

	/**
	 * resourceId() method return current image resource
	 *
	 * @return resource
	 */
	public function resourceId()
	{
		return $this->_rId;
	}

	/**
	 * width() method returns current image's width
	 *
	 * @return int
	 */
	public function width() {
		return ($this->_iWidth);
	}

	/**
	 * height() method returns current image's height
	 *
	 * @return int
	 */
	public function height()
	{
		return $this->_iHeight;
	}

	/**
	 * setType() method allow to modify image type. If the file type is do not handle as available type as jpg, gif and png, exception is thrown
	 *
	 * @param int $iFormat
	 */
	public function setType($iFormat)
	{
		// set header
		switch ($iFormat) {
			case IMAGETYPE_GIF:
				$this->_iFormat = $iFormat;
				$this->_sMime = 'image/gif';
				break;
			case IMAGETYPE_JPEG:
				$this->_iFormat = $iFormat;
				$this->_sMime = 'image/jpeg';
				break;
			case IMAGETYPE_PNG:
				$this->_iFormat = $iFormat;
				$this->_sMime = 'image/png';
				break;
			default:
				throw new Exception('Image type ' . $iFormat . '(' . $this->_sMime . ') not suported',302);
				break;
		}
	}

	/**
	 * display() method change the page's content type and display the current image
	 *
	 * @param int $iQuality = NULL
	 */
	public function display($iQuality = NULL)
	{
		// set header
		header('Content-type: ' . $this->_sMime);

		switch ($this->_iFormat) {
			case IMAGETYPE_GIF:
				imagegif($this->_rId);
				break;
			case IMAGETYPE_JPEG:
				if (is_null($iQuality)) {
					imagejpeg($this->_rId);
				}
				else {
					imagejpeg($this->_rId, null, $iQuality);
				}
				break;
			case IMAGETYPE_PNG:
				if (is_null($iQuality)) {
					imagepng($this->_rId);
				}
				else {
					imagepng($this->_rId, null, $iQuality);
				}
				break;
			default:
				throw new Exception('Image type ' . $this->_iFormat . '(' . $this->_sMime . ') not suported',302);
				break;
		}
	}

	/**
	 * saveTo() method register the current image into specified file as parameter
	 *
	 * @param string $sFileName
	 * @param int $iQuality = NULL
	 */
	public function saveTo($sFileName, $iQuality = NULL)
	{
		switch ($this->_iFormat) {
			case IMAGETYPE_GIF:
				imagegif($this->_rId, $sFileName);
				break;
			case IMAGETYPE_JPEG:
				if (is_null($iQuality)) {
					imagejpeg($this->_rId, $sFileName);
				}
				else {
					if ($iQuality == 'MAX') {
						$iQuality = 100;
					}
					imagejpeg($this->_rId, $sFileName, $iQuality);
				}
				break;
			case IMAGETYPE_PNG:
				if (is_null($iQuality)) {
					imagepng($this->_rId, $sFileName);
				}
				else {
					if ($iQuality == 'MAX') {
						$iQuality = 9;
					}
					imagepng($this->_rId, $sFileName, $iQuality);
				}
				break;
			default:
				throw new Exception('Image type ' . $this->_iFormat . '(' . $this->_sMime . ') not suported',302);
				break;
		}
	}

	/**
	 * resize() method resize the current image. If both width and height parameter is NULL, this one will be calculated by homothetic value
	 *
	 * @param int $iWidth
	 * @param int $iHeight
	 * @param bool $bTransparent = false
	 */
	public function resize($iWidth, $iHeight, $bTransparent = false)
	{
		// check size
		if (is_null($iHeight) && is_null($iWidth)) {
			throw new Exception('No size specified',303);
		}
		// calculate new height if not specified
		if (is_null($iHeight)) {
			$iHeight = round(($this->_iHeight * $iWidth) / $this->_iWidth);
		}
		// calculate new width if not specified
		if (is_null($iWidth)) {
			$iWidth = round(($this->_iWidth * $iHeight) / $this->_iHeight);
		}
		// create new image
		$newResId = imagecreatetruecolor($iWidth, $iHeight);
		if ($bTransparent) {
			imagecolortransparent($newResId, 0);
		}
		// resize image
		imagecopyresampled($newResId, $this->_rId, 0, 0, 0, 0, $iWidth, $iHeight, 
			$this->_iWidth, $this->_iHeight);
		// flip ressources link
		imagedestroy($this->_rId);
		$this->_rId = $newResId;
		// set new size
		$this->_iWidth = $iWidth;
		$this->_iHeight = $iHeight;
	}

	/**
	 * clip() method cut one part of the current image according to new coordinates
	 *
	 * @param int $iXpos : abscissa
	 * @param int $iYpos : ordinate
	 * @param int $iWidth : width
	 * @param int $iHeight : height
	 */
	public function clip($iXpos, $iYpos, $iWidth = null, $iHeight = null)
	{
		// calculate new width if not specified
		if (is_null($iWidth)) {
			$iWidth = $this->_iWidth - $iXpos;
		}
		// calculate new height if not specified
		if (is_null($iHeight)) {
			$iHeight = $this->_iHeight - $iYpos;
		}
		// create new image
		$newResId = imagecreatetruecolor($iWidth, $iHeight);
		imagecolortransparent($newResId, 0);
		// clip image
		imagecopy ($newResId, $this->_rId, 0, 0, $iXpos, $iYpos, $iWidth, $iHeight);
		// flip ressources link
		imagedestroy($this->_rId);
		$this->_rId = $newResId;
		// set new size
		$this->_iWidth = $iWidth;
		$this->_iHeight = $iHeight;
	}

	/**
	 * rotate() method rotate the current image
	 *
	 * @param float $fAngle : rotation angle
	 * @param array $aBgColor = null : background color
	 */
	public function rotate($fAngle, $aBgColor = null)
	{
		// calculate new width if not specified
		if (is_null($aBgColor)) {
			$this->_rId = imagerotate ($this->_rId, $fAngle, $this->_iImageTransparent);
		}
		else {
			$this->_rId = imagerotate ($this->_rId, $fAngle, 
				imagecolorallocate ($this->_rId, $aBgColor[0], $aBgColor[1], $aBgColor[2])
			);
		}
	}

	/**
	 * insertAt() method insert an image into the current image to the required position
	 *
	 * @param Picture $oPicture : picture to insert
	 * @param int $iXpos : X pos of insertion
	 * @param int $iYpos : Y pos of insertion
	 * @param int $iTranspPercent = 100 : Alpha transparency percentage
	 */
	public function insertAt(Picture $oPicture, $iXpos, $iYpos, $iTranspPercent = 100)
	{
		imagecopymerge ($this->_rId, $oPicture->resourceId(), $iXpos, $iYpos, 0, 0,
			$oPicture->width(), $oPicture->height(), $iTranspPercent);
	}

	/**
	 * insertTo() method is the same as insertAt() function. This one do not require by X and Y positions but a relative position : TOPLEFT, TOP CENTER, TOPRIGHT, MIDDLE, BOTTOMLEFT, BOTTOMCENTER ou BOTTOMRIGHT
	 *
	 * @param Picture $oPicture : picture to insert
	 * @param string $sPosition : including position
	 * @param int $iMargin = 0 : possible margin
	 * @param int $iTranspPercent = 100 :Alpha transparency percentage
	 */
	public function insertTo(Picture $oPicture, $sPosition, $iMargin = 0, $iTranspPercent = 100)
	{
		// calculate position
		$iXpos = 0;
		$iYpos = 0;
		$sPosition = strtoupper(str_replace('_','',$sPosition));

		switch ($sPosition) {
			case 'TOPLEFT' :
				$iXpos = $iXpos + $iMargin;
				$iYpos = $iYpos + $iMargin;
				break;
			case 'TOPCENTER' :
				$iXpos = round(($this->width() - $oPicture->width())/2);
				$iYpos = $iYpos + $iMargin;
				break;
			case 'TOPRIGHT' :
				$iXpos = $this->width() - $oPicture->width() - $iMargin;
				$iYpos = $iYpos + $iMargin;
				break;
			case 'MIDDLE' :
				$iXpos = round(($this->width() - $oPicture->width())/2);
				$iYpos = round(($this->height() - $oPicture->height())/2);
				break;
			case 'BOTTOMLEFT' :
				$iYpos = $this->height() - $oPicture->height() - $iMargin;
				$iXpos = $iXpos + $iMargin;
				break;
			case 'BOTTOMCENTER' :
				$iXpos = round(($this->width() - $oPicture->width())/2);
				$iYpos = $this->height() - $oPicture->height() - $iMargin;
				break;
			case 'BOTTOMRIGHT' :
				$iXpos = $this->width() - $oPicture->width() - $iMargin;
				$iYpos = $this->height() - $oPicture->height() - $iMargin;
				break;
		}
		$this->insertAt($oPicture, $iXpos, $iYpos, $iTranspPercent);
		
		imagecopymerge ($this->_rId, $oPicture->resourceId(), $iXpos, $iYpos, 0, 0,
			$oPicture->width(), $oPicture->height(), $iTranspPercent);
	}

	/**
	 * contract() method can delete the edges of an image of the number of pixels as parameters
	 *
	 * @param int $iPixel : number of pixels to delete
	 */
	public function contract($iPixel)
	{
		// calculate new size
		$this->_iWidth = $this->_iWidth - (2*$iPixel);
		$this->_iHeight = $this->_iHeight -(2*$iPixel);

		// create new image
		$newResId = imagecreatetruecolor($this->_iWidth, $this->_iHeight);

		// calculate image
		imagecopy ($newResId, $this->_rId, 0, 0, $iPixel, 
			$iPixel, $this->_iWidth, $this->_iHeight);

		// flip ressources link
		imagedestroy($this->_rId);

		$this->_rId = $newResId;
	}

	/**
	 * expand() method can extend the current image
	 *
	 * @param int $iPixel : number of pixels to add
	 * @param array $aBgColor = null : edges color
	 */
	public function expand($iPixel, $aBgColor = null)
	{
		// create new image
		$newResId = imagecreatetruecolor($this->_iWidth + (2*$iPixel), $this->_iHeight + (2*$iPixel));

		if (is_null($aBgColor)) {
			imagefill ( $newResId, 0, 0, $this->_iImageTransparent);
		}
		else {
			imagefill ( $newResId, 0, 0, 
				imagecolorallocate ($this->_rId, $aBgColor[0], 
				$aBgColor[1], $aBgColor[2]));
		}
		imagecopy ($newResId, $this->_rId, $iPixel, $iPixel, 0, 0, $this->_iWidth, $this->_iHeight);

		// calculate new size
		$this->_iWidth = $this->_iWidth + (2*$iPixel);
		$this->_iHeight = $this->_iHeight +(2*$iPixel);
		// flip resource link
		imagedestroy($this->_rId);

		$this->_rId = $newResId;
	}

	/**
	 * addBorder() method can add edges to the current image
	 *
	 * @param int $iTop : number of pixels to add on top
	 * @param int $iLeft : number of pixels to add on left
	 * @param int $iBottom : number of pixels to add on bottom
	 * @param int $iRight : number of pixels to add on right
	 * @param array $aBgColor = null : edges color
	 */
	public function addBorder($iTop, $iLeft, $iBottom, $iRight, $aBgColor = null)
	{
		// create new image
		$newResId = imagecreatetruecolor($this->_iWidth + $iLeft + $iRight, $this->_iHeight + $iTop + $iBottom);

		if (is_null($aBgColor)) {
			imagefill ( $newResId, 0, 0, $this->_iImageTransparent);
		}
		else {
			imagefill ( $newResId, 0, 0, imagecolorallocate ($this->_rId, $aBgColor[0], $aBgColor[1], $aBgColor[2]));
		}
		imagecopy ($newResId, $this->_rId, $iLeft, $iTop, 0, 0, $this->_iWidth, $this->_iHeight);

		// calculate new size
		$this->_iWidth = $this->_iWidth + $iLeft + $iRight;
		$this->_iHeight = $this->_iHeight + $iTop + $iBottom;

		// flip resource link
		imagedestroy($this->_rId);

		$this->_rId = $newResId;
	}

	/**
	 * createImage() method can create an empty image with size and color as parameters
	 *
	 * @param int $iFormat
	 * @param int $iWidth
	 * @param int $iHeight
	 * @param array $aBgColor
	 */
	public function createImage($iFormat, $iWidth, $iHeight, $aBgColor = null)
	{
		$this->_iWidth = $iWidth;
		$this->_iHeight = $iHeight;

		switch ($iFormat) {
			case 'image/gif':
			case IMAGETYPE_GIF:
				$this->_iFormat = IMAGETYPE_GIF;
				$this->_sMime = 'image/gif';
				break;
			case 'image/jpeg':
			case 'image/jpg':
			case IMAGETYPE_JPEG:
				$this->_iFormat = IMAGETYPE_JPEG;
				$this->_sMime = 'image/jpeg';
				break;
			case 'image/png':
			case IMAGETYPE_PNG:
				$this->_iFormat = IMAGETYPE_PNG;
				$this->_sMime = 'image/png';
				break;
			default:
				throw new Exception('Image type ' . $sMimeType . ' not suported',302);
				break;
		}
		$this->_rId = imagecreate ($this->_iWidth, $this->_iHeight);
		if (is_null($aBgColor)) {
			imagefill ( $this->_rId, 0, 0, $this->_iImageTransparent);
		}
		else {
			imagefill ( $this->_rId, 0, 0, imagecolorallocate ($this->_rId, $aBgColor[0], $aBgColor[1], $aBgColor[2]));
		}
	}
}