<?php

namespace Jacere\Bramble\Core\Database\Builder;

class ForeignKey {

	private $m_key;
	private $m_table;
	private $m_field;
	private $m_delete;

	private function __construct($key, $table, $field, $delete) {
		$this->m_key = $key;
		$this->m_table = $table;
		$this->m_field = $field;
		$this->m_delete = $delete;
	}

	public function localField() {
		return $this->m_key;
	}

	public function table() {
		return $this->m_table;
	}

	public static function parse($name, $def) {
		if (Table::match('FOREIGN KEY \((?<local>[^)]+)\) REFERENCES (?<table>[^\s]+) \((?<foreign>[^)]+)\) ON DELETE (?<delete>SET NULL|CASCADE|RESTRICT)', $def, $m)) {
			// foreign key (add fk clause)
			$localField = Table::trim($m['local']);
			$foreignTable = Table::trim($m['table']);
			$foreignField = Table::trim($m['foreign']);
			$foreignDelete = $m['delete'];

			return new self($localField, $foreignTable, $foreignField, $foreignDelete);
		}
		return NULL;
	}

	public function __toString() {
		return sprintf(
			'FOREIGN KEY (%s) REFERENCES %s (%s) ON DELETE %s',
			Table::quote_identifier($this->m_key),
			Table::quote_identifier($this->m_table),
			Table::quote_identifier($this->m_field),
			$this->m_delete
		);
	}
}
