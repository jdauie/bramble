<?php

namespace Jacere\Bramble\Core\Database\Builder;

class UniqueKey {

	private $m_fields;
	private $m_primary;

	private function __construct(array $fields, $primary) {
		$this->m_fields = $fields;
		$this->m_primary = $primary;
	}

	public static function parse($name, $def) {
		if (Table::match('(?<type>PRIMARY KEY|UNIQUE) \((?<columns>[^)]+)\)', $def, $m)) {
			// primary/unique key (add conflict clause)
			$constraintType = strtoupper($m['type']);
			$constraintColumns = array_map([Table::class, 'trim'], explode(',', $m['columns']));
			
			return new self($constraintColumns, ($constraintType === 'PRIMARY KEY'));
		}
		return NULL;
	}

	public function __toString() {
		return sprintf('%s (%s)', ($this->m_primary ? 'PRIMARY KEY' : 'UNIQUE'), implode(',', array_map([Table::class, 'quote_identifier'], $this->m_fields)));
	}
}
