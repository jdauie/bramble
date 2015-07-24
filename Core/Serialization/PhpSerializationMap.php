<?php

namespace Jacere\Bramble\Core\Serialization;

class PhpSerializationMap {
	
	private $m_map;
	
	public function __construct() {
		$this->m_map = [];
	}
	
	public function using() {
		return $this->m_map;
	}
	
	public function newObject($obj, $args) {
		$name = get_class($obj);
		if (!isset($this->m_map[$name])) {
			$this->m_map[$name] = 'A'.dechex(count($this->m_map));
		}
		$alias = $this->m_map[$name];
		$args = implode(',', array_map(function($a) {
			return PhpSerializationHelper::serialize_value($a, $this);
		}, $args));
		return "new $alias($args)";
	}
}
