<?php

namespace Jacere\Bramble\Core\Database;

/**
 * Base statement result class.  At present, it is not intended to be used as an iterator, so it implements \Iterator
 * simply to throw a runtime exception if it is used as one.
 */
abstract class StatementResult implements \Iterator, \JsonSerializable {

	private $m_db;
	
	private $m_meta;
	private $m_anon;

	private $m_iterator_msg;

	/**
	 * @param IDatabase $db
	 */
	public function __construct(IDatabase $db) {
		$this->m_db = $db;
		$this->m_iterator_msg = sprintf('%s does not iterate over rows.  Use $res->rows() instead.', get_class($this));
	}
	
	protected abstract function fetch_array();

	protected abstract function get_field_metadata();

	public abstract function rows_affected();

	/**
	 * If there is exactly one row, return it, otherwise return false
	 * @param array $map
	 * @return array|false
	 */
	public function single_row(array $map = NULL) {
		$single_row = false;
		foreach ($this->rows($map) as $row) {
			if ($single_row) {
				$single_row = false;
				break;
			}
			$single_row = $row;
		}
		return $single_row;
	}

	/**
	 * If there is exactly one row and column, return the value, otherwise return false (or the specified default).
	 * This is useful for queries such as COUNT(*) or retrieving an ID for a single record.
	 * @param mixed|bool $default
	 * @return mixed|bool
	 */
	public function single_value($default = false) {
		if ($single_row = $this->single_row()) {
			if (count($single_row) === 1) {
				return array_pop($single_row);
			}
		}
		return $default;
	}

	/**
	 * Returns an iterator over the row arrays, where the fields in each row are keyed by field name,
	 * and values have been converted using optional function mappings
	 * @param array $map
	 * @return \Generator
	 */
	public function rows(array $map = NULL) {
		
		$this->load_field_metadata();
		
		$convertible_fields = [];
		foreach ($this->m_meta as $field) {
			if ($field_converter = $this->m_db->converter($field['type'])) {
				$convertible_fields[$field['name']] = $field_converter;
			}
		}

		while ($row = $this->fetch_array()) {
			if ($this->m_anon) {
				// this ungodly hack is to fix known PHP bug where numeric keys are actually strings (and as such are inaccessible)
				// https://bugs.php.net/bug.php?id=45959
				$new_row = [];
				if (count($row) === 1) {
					// this is a new behavior, where COUNT(*) returns an array like [45 => 45] instead of [0 => 45].
					// we will just have to use aliases for an anonymous field returned with any other fields
					$new_row[0] = reset($row);
				}
				else {
					foreach ($row as $key => $value) {
						$new_row[$key] = $value;
					}
				}
				$row = $new_row;
			}
			foreach ($convertible_fields as $name => $converter) {
				$row[$name] = $converter($row[$name]);
			}
			if ($map) {
				foreach ($map as $name => $converter) {
					$row[$name] = $converter($row[$name]);
				}
			}
			yield $row;
		}
	}

	/**
	 * Returns a keyed iterator over the row arrays, where the key is set from the specified field, and the field used
	 * as the key may be optionally removed from the row to simplify the result
	 * @param string $field
	 * @param bool $remove
	 * @param array $map
	 * @return \Generator
	 */
	public function rows_by_key($field, $remove = false, array $map = NULL) {
		foreach ($this->rows($map) as $row) {
			$key = $row[$field];
			if ($remove) {
				unset($row[$field]);
			}
			yield $key => $row;
		}
	}

	/**
	 * Returns a keyed iterator over a single field from each row, where the key is set from the specified field
	 * @param string $key_field
	 * @param string $val_field
	 * @return \Generator
	 */
	public function column_by_key($key_field, $val_field) {
		foreach ($this->rows() as $row) {
			yield $row[$key_field] => $row[$val_field];
		}
	}

	/**
	 * Returns an iterator over a single field from each row
	 * @param string $key
	 * @return \Generator
	 * @throws \InvalidArgumentException
	 */
	public function column($key = NULL) {
		$this->load_field_metadata();
		
		if ($key === NULL) {
			if (count($this->m_meta) !== 1) {
				throw new \InvalidArgumentException('The column key must be defined if there is more than one column');
			}
			else {
				$key = reset($this->m_meta)['name'];
			}
		}
		foreach ($this->rows() as $row) {
			yield $row[$key];
		}
	}
	
	private function load_field_metadata() {
		if (!$this->m_meta) {
			$this->m_meta = $this->get_field_metadata();
			foreach ($this->m_meta as $i => &$field) {
				if ($field['name'] === '') {
					// handle anonymous column like COUNT(*) without alias
					$field['name'] = $i;
					$this->m_anon = true;
				}
			}
		}
	}

	/**#@+
	 * \Iterator implementation intended to prevent mistaken iteration on this class
	 * @throws \LogicException
	 */
	public function rewind() {
		throw new \LogicException($this->m_iterator_msg);
	}

	public function current() {
		throw new \LogicException($this->m_iterator_msg);
	}

	public function key() {
		throw new \LogicException($this->m_iterator_msg);
	}

	public function next() {
		throw new \LogicException($this->m_iterator_msg);
	}

	public function valid() {
		throw new \LogicException($this->m_iterator_msg);
	}
	/**#@-*/

	/**
	 * \JsonSerializable implementation intended to prevent returning this object
	 * @throws \LogicException
	 */
	public function jsonSerialize() {
		throw new \LogicException($this->m_iterator_msg);
	}
}
