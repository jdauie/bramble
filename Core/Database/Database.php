<?php

namespace Jacere\Bramble\Core\Database;

/**
 * Abstract database class which supports type conversion to PHP types
 */
abstract class Database implements IDatabase {

	private $m_options;
	private $m_types;

	/**
	 * @param \stdClass $options Connection options
	 */
	public function __construct(\stdClass $options) {

		$converters = [
			'bool'  => 'boolval',
			'int'   => 'intval',
			'float' => 'floatval',
			'date'  => function($a) {return new \DateTime($a);},
		];

		$this->m_types = self::get_type_converters($this->conversions(), $converters);

		$this->m_options = $options;
		
		$this->connect($options);
	}

	/**
	 * @param string $name
	 * @return string|null
	 */
	protected function option($name) {
		return (isset($this->m_options[$name]) ? $this->m_options[$name] : NULL);
	}

	/**
	 * @param \stdClass $dsn
	 * @return
	 */
	protected abstract function connect(\stdClass $dsn);

	/**
	 * @return array
	 */
	protected abstract function conversions();

	/**
	 * @param string $sql
	 * @param array $args
	 * @return StatementResult
	 */
	protected abstract function query_result($sql, $args);

	/**
	 * Get the database name
	 * @return string
	 */
	public abstract function name();

	/**
	 * @return bool
	 */
	public function begin_transaction() {
		return false;
	}

	/**
	 * @return bool
	 */
	public function commit() {
		return false;
	}

	/**
	 * @return bool
	 */
	public function rollback() {
		return false;
	}

	/**
	 * Get the converter to call for the specified field type, or NULL if no corresponding converter is found.
	 * @param $field_type
	 * @return callable
	 */
	public function converter($field_type) {
		return isset($this->m_types[$field_type]) ? $this->m_types[$field_type] : NULL;
	}

	/**
	 * Retrieve query results using optional arguments to fill in values in the prepared SQL statement.  If there is a
	 * single optional array argument, then the values in the array will become the optional argument list.
	 * @param string $sql
	 * @return StatementResult
	 */
	public function query($sql) {
		$args = self::get_var_args(func_get_args(), 1);
		self::convert_sql_named_parameters_to_placeholders($sql, $args);
		return $this->query_result($sql, $args);
	}

	/**
	 * Execute query using optional arguments to fill in values in the prepared SQL statement.  If there is a single
	 * optional array argument, then the values in the array will become the optional argument list.
	 * @param string $sql
	 * @return int Number of rows affected
	 */
	public function execute($sql) {
		$args = self::get_var_args(func_get_args(), 1);
		self::convert_sql_named_parameters_to_placeholders($sql, $args);
		return $this->query_result($sql, $args, 0, 0)->rows_affected();
	}
	
	public static function dsn_options($dsn) {
		$options = [];
		$parts = explode(';', $dsn);
		foreach ($parts as $part) {
			$pair = explode('=', $part);
			if (count($pair) === 2) {
				$options[strtolower($pair[0])] = $pair[1];
			}
		}
		return $options;
	}
	
	private static function convert_sql_named_parameters_to_placeholders(&$sql, array &$args) {
		
		if (!count($args)) {
			return;
		}
		
		// check for names?
		foreach ($args as $key => $value) {
			if (!is_string($key)) {
				return;
			}
			if ($key[0] === ':') {
				unset($args[$key]);
				$key = substr($key, 1);
				$args[$key] = $value;
			}
		}
		
		$names = [];
		$params = [];
		$sql = preg_replace_callback('/(?<![:\d])[:]([a-zA-Z0-9_]+)/', function($matches) use(&$names, &$params, $args) {
			$name = $matches[1];
			$names[$name] = true;

			if (!array_key_exists($name, $args)) {
				throw new \Exception('undefined named param');
			}
			
			$arg = $args[$name];
			if (is_array($arg)) {
				foreach ($arg as $val) {
					$params[] = $val;
				}
				return implode(',', array_fill(0, count($arg), '?'));
			}
			else {
				$params[] = $arg;
				return '?';
			}
		}, $sql);
		
		$diff = array_diff_key($args, $names);
		if (count($diff)) {
			throw new \Exception('too many parameters');
		}
		
		$args = $params;
	}

	private static function get_var_args($args, $skip = 0) {
		array_splice($args, 0, $skip);
		if (count($args) === 1 && is_array($args[0])) {
			return $args[0];
		}
		return $args;
	}

	private static function get_type_converters(array $conversions = NULL, $converters = NULL) {
		$types = [];
		if ($conversions && $converters) {
			foreach ($conversions as $key => $values) {
				if (isset($converters[$key])) {
					foreach ($values as $value) {
						$value = strtolower($value);
						if (isset($types[$value])) {
							throw new \RuntimeException("Multiple field conversion definitions for `$value`");
						}
						$types[$value] = $converters[$key];
					}
				}
			}
		}
		return $types;
	}
}
