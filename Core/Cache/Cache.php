<?php

namespace Jacere\Bramble\Core\Cache;

class Cache {

	/** @var ICache */
	private static $c_instance;

	private static $c_cache;
	
	public static function init($name, $options) {
		$type = sprintf('%s\%s', __NAMESPACE__, $name);
		self::$c_instance = new $type($options);
		self::$c_cache = [];
	}

	public static function version() {
		return self::$c_instance->version();
	}

	public static function clear() {
		self::$c_instance->clear();
	}

	public static function get($name) {
		$key = self::key($name);

		if (!isset(self::$c_cache[$key])) {
			self::$c_cache[$key] = self::$c_instance->get($key);
		}
		return self::$c_cache[$key];
	}

	public static function set($name, $value) {
		$key = self::key($name);
		self::$c_instance->set($key, $value);
		self::$c_cache[$key] = $value;
	}
	
	private static function key($name) {
		if (!is_string($name) || !ctype_alnum(str_replace('_', '', $name))) {
			throw new \Exception('Invalid cache key');
		}
		return $name;
	}

	/**
	 * Load or create the cached value.
	 * @param string $key
	 * @param callable $action Generator function for creating a new value if necessary
	 * @param bool $rebuild Force recreation of the value
	 * @return mixed
	 */
	public static function load($key, callable $action, $rebuild = false) {
		if ($rebuild || !($value = self::get($key))) {
			$value = $action();
			self::set($key, $value);
		}

		return $value;
	}
}
