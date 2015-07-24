<?php

namespace Jacere\Bramble\Core;

class Autoloader {
	
	private static $c_lookup;
	
	public static function register($namespace, $path) {
		
		// initialize and register handler
		if (!self::$c_lookup) {
			self::$c_lookup = [];
			spl_autoload_register([__CLASS__, 'autoload']);
		}
		
		// add to lookup tree
		$current = &self::$c_lookup;
		$parts = explode('\\', $namespace);
		foreach ($parts as $part) {
			if (!isset($current[$part])) {
				$current[$part] = [];
			}
			$current = &$current[$part];
		}
		if (is_string($current)) {
			throw new \Exception('partial autoload path redefinition');
		}
		$current = $path;
	}
	
	public static function autoload($class) {
		$parts = explode('\\', $class);
		$count = count($parts);
		$current = self::$c_lookup;
		for ($i = 0; $i < $count; $i++) {
			$part = $parts[$i];
			if (!isset($current[$part])) {
				break;
			}
			$current = $current[$part];
		}
		if ($current && is_string($current)) {
			$path = $current;
			if ($i !== $count) {
				array_splice($parts, 0, $i);
				$path = sprintf('%s/%s.php', $path, implode('/', $parts));
			}
			if (file_exists($path)) {
				require_once($path);
			}
		}
	}
}
