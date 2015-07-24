<?php

namespace Jacere\Bramble\Core\Exception;

class RuntimeInfoException extends \RuntimeException implements IAdditionalInfo {
	
	protected $m_info;
	
	public function __construct($message, $info = NULL) {
		parent::__construct($message);
		$this->m_info = $info;
	}

	public function getAdditionalInfo() {
		return $this->m_info;
	}
}
