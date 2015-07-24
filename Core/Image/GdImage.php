<?php

namespace Jacere\Bramble\Core\Image;

class GdImage {
	
	private static $c_types = [
		IMAGETYPE_GIF => 'gif',
		IMAGETYPE_JPEG => 'jpeg',
		IMAGETYPE_PNG => 'png',
	];

	private $m_image;
	private $m_width;
	private $m_height;
	private $m_type;

	private function __construct($image, $width, $height, $type) {
		$this->m_image = $image;
		$this->m_width = $width;
		$this->m_height = $height;
		$this->m_type = $type;
	}

	public static function load($path) {
		list($width, $height, $type) = getimagesize($path);
		if (!isset(self::$c_types[$type])) {
			throw new \Exception('unsupported image type');
		}
		$creator = 'imagecreatefrom'.self::$c_types[$type];
		$image = $creator($path);
		return new self($image, $width, $height, $type);
	}

	/**
	 * @param int $width
	 * @param int $height
	 * @return self
	 */
	public function resize($width, $height) {
		
		$src_aspect = $this->m_width / $this->m_height;

		if ($width === 0) {
			$width = (int)($height * $src_aspect);
		}
		else if ($height === 0) {
			$height = (int)($width / $src_aspect);
		}

		$image = imagecreatetruecolor($width, $height);
		imagecopyresampled($image, $this->m_image, 0, 0, 0, 0, $width, $height, $this->m_width, $this->m_height);
		
		return new self($image, $width, $height, $this->m_type);
	}

	/**
	 * @param string $path
	 * @return $this
	 */
	public function save($path) {
		// todo: add quality/speed options
		switch ($this->m_type) {
			case IMAGETYPE_GIF:
				imagegif($this->m_image, $path);
				break;
			case IMAGETYPE_JPEG:
				imagejpeg($this->m_image, $path);
				break;
			case IMAGETYPE_PNG:
				imagepng($this->m_image, $path);
				break;
		}
		return $this;
	}
	
	public function dispose() {
		if ($this->m_image) {
			imagedestroy($this->m_image);
			$this->m_image = NULL;
		}
	}
	
	public function __destruct() {
		$this->dispose();
	}
}
