<?php

namespace Jacere\Bramble\Core\Database\Builder;

class Table {

	const FK_SET_NULL = 1;
	const FK_CASCADE = 2;
	const FK_RESTRICT = 3;

	private $m_name;
	private $m_columns;
	private $m_unique;
	private $m_foreign;

	private function __construct($sql) {
		preg_match('/^CREATE TABLE (?<name>[^\s\(]+) \((?<defs>.*)\)\s*$/is', $sql, $matches);
		$this->m_name = $matches['name'];
		$defs = array_map('trim', preg_split('/,[\t ]*[\r\n]/', $matches['defs']));

		$this->m_columns = [];
		$this->m_foreign = [];
		$this->m_unique = [];
		
		$constraints = [];
		foreach ($defs as $def) {
			if (count($constraints) || ($matched = self::match('(?:CONSTRAINT|PRIMARY|UNIQUE|FOREIGN|CHECK)\b', $def, $m))) {
				if (isset($matched) && !$matched) {
					throw new \Exception('Failed to parse start of constraint');
				}
				// table constraint
				// check for optional constraint name
				$constraintName = NULL;
				if (self::match('CONSTRAINT (?<name>'.Column::R_NAME.')', $def, $m)) {
					$constraintName = $m['name'];
					$def = ltrim(substr($def, strlen($m[0])));
				}

				if ($uniqueKey = UniqueKey::parse($constraintName, $def)) {
					$this->m_unique[] = $uniqueKey;
				}
				else if ($foreignKey = ForeignKey::parse($constraintName, $def)) {
					$this->m_foreign[$foreignKey->localField()] = $foreignKey;
				}
				else {
					throw new \Exception('Failed to parse constraint');
				}
			}
			else {
				$column = Column::parse($def);
				$this->m_columns[$column->name()] = $column;
			}
		}
	}
	
	public static function quote_identifier($identifier) {
		return "`$identifier`";
	}

	public static function parse($sql) {
		return new self($sql);
	}

	public function name() {
		return $this->m_name;
	}

	public function dependencies() {
		return array_diff(array_unique(array_map(function(ForeignKey $key) {
			return $key->table();
		}, $this->m_foreign)), [$this->name()]);
	}

	public function __toString() {
		return implode("\n", [
			sprintf("CREATE TABLE %s (", self::quote_identifier($this->m_name)),
			implode(",\n", array_map(function($a) {return "\t$a";}, array_merge(
				array_map('strval', array_values($this->m_columns)),
				array_map('strval', array_values($this->m_unique)),
				array_map('strval', array_values($this->m_foreign))
			))),
			")",
		]);
	}

	public static function match($pattern, $subject, array &$matches = NULL) {
		$pattern = trim($pattern);
		// \s* if there is an adjacent paren; otherwise \s+
		$pattern = preg_replace('/(?<=\\\\\))\s+|\s+(?=\\\\\()/', '\s*', $pattern);
		$pattern = preg_replace('/\s+/', '\s+', $pattern);
		return preg_match("/^$pattern/i", $subject, $matches);
	}

	public static function trim($str) {
		return trim(trim($str), '"[]`');
	}
}
