<?php

namespace Jacere\Bramble\Core\Database\Builder;

class Column {

	const R_NAME = '"[^"]++"|`[^`]++`|\[[^\]]++\]|[^]["`\s]++';

	private $m_name;
	private $m_type;
	private $m_size;
	private $m_null;
	private $m_default;

	private function __construct($str) {
		if (!preg_match('/^(?<column>'.self::R_NAME.')\s*(?<type>[^(\s]++)(?:\((?<size>\d++)\))?\s*(?<nn>\bNOT\s+NULL\b)?\s*(?:\bDEFAULT\s+(?<default>[^\s]++))?/i', $str, $m)) {
			throw new \Exception('Failed to parse column definition');
		}
		$this->m_name = trim($m['column'], '[]"`');
		$this->m_type = $m['type'];
		$this->m_size = isset($m['size']) ? $m['size'] : NULL;
		$this->m_null = !isset($m['nn']);
		$this->m_default = isset($m['default']) ? $m['default'] : NULL;
	}

	public function name() {
		return $this->m_name;
	}
	
	public function type_and_size() {
		$size = $this->m_size ? "({$this->m_size})" : '';
		// todo: convert from defined type to adapter type
		if (strpos($this->m_type, 'varchar') !== false) {
			return 'TEXT';
		}
		if ($this->m_type === 'int') {
			// sqlite is *very* specific about the type for auto incrementing primary key
			return 'INTEGER';
		}
		return $this->m_type.$size;
	}

	public static function parse($str) {
		return new self($str);
	}

	public function __toString() {
		$null = $this->m_null ? '' : ' NOT NULL';
		$default = ($this->m_default !== NULL) ? " DEFAULT {$this->m_default}" : '';
		return sprintf("`%s` {$this->type_and_size()}{$null}{$default}", $this->m_name);
	}
}
