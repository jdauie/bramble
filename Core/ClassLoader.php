<?php

namespace Jacere\Bramble\Core;

use Jacere\Bramble\Core\Cache\Cache;
use Jacere\Bramble\Core\Serialization\IPhpSerializable;
use Jacere\Bramble\Core\Serialization\PhpSerializationMap;

class ClassLoader implements IPhpSerializable {
	
	private static $c_locations;

	/** @var ClassLoader */
	private static $c_instance;
	
	private $m_map;
	
	public function __construct(array $map) {
		$this->m_map = $map;
	}

	public function phpSerializable(PhpSerializationMap $map) {
		return $map->newObject($this, [$this->m_map]);
	}
	
	public static function register($path) {

		if (self::$c_instance) {
			throw new \Exception('Late map registration');
		}

		// initialize and register handler
		if (!self::$c_locations) {
			self::$c_locations = [];
			spl_autoload_register([__CLASS__, 'autoload']);
		}

		self::$c_locations[] = $path;
	}

	public static function autoload($class) {
		if (self::$c_instance === NULL) {
			self::$c_instance = self::instance();
		}
		
		if (isset(self::$c_instance->m_map[$class])) {
			$path = self::$c_instance->m_map[$class];
			if (file_exists($path)) {
				require_once($path);
			}
		}
	}

	/**
	 * @param bool $rebuild
	 * @return ClassLoader
	 */
	public static function instance($rebuild = false) {
		return Cache::load('classmap', [self::class, 'immediate'], $rebuild);
	}

	/**
	 * @return ClassLoader
	 */
	public static function immediate() {
		function() {
			$files = [];
			if (count(self::$c_locations)) {
				foreach (self::$c_locations as $path) {
					if (is_file($path)) {
						$files[] = $path;
					}
					else {
						$files = array_merge($files, Directory::search(self::$c_locations, Directory::R_PHP, [Directory::R_HIDDEN]));
					}
				}
			}
			$map = self::get_class_map($files);
			return new self($map);
		}
	}

	private static function get_class_map($files, $ignore_duplicates = true) {
		$classes = [];
		$duplicates = [];
		
		foreach ($files as $file) {
			$current = self::get_classes($file);
			foreach ($current as $class) {
				if (isset($classes[$class])) {
					if ($ignore_duplicates) {
						// track duplicate classes for removal from the list, but do not throw
						$duplicates[$class] = true;
					}
					else {
						throw new \Exception(sprintf('Duplicate class "%s" in files "%s" and "%s"', $class, $classes[$class], $file));
					}
				}
				$classes[$class] = str_replace('\\', '/', $file);
			}
		}
		
		// remove invalid classes
		return array_diff_key($classes, $duplicates);
	}
	
	private static function get_classes($file) {
		$error_before = error_get_last();
		$tokens = token_get_all(file_get_contents($file));
		$error_after = error_get_last();
		// trap an E_COMPILE_WARNING
		if ($error_after && (!$error_before || $error_after['message'] !== $error_before['message'])) {
			// skip for now
		}
		
		$groups = self::get_token_groups($tokens, [
			T_NAMESPACE => [T_WHITESPACE, T_STRING, T_NS_SEPARATOR],
			T_INTERFACE => [T_WHITESPACE, T_STRING],
			T_CLASS     => [T_WHITESPACE, T_STRING],
		]);
		
		$classes = [];
		$namespace = NULL;
		
		foreach ($groups as $group) {
			if ($group['type'] === T_NAMESPACE) {
				$namespace = implode('', array_map(function($a){return $a[1];}, $group['tokens']));
			}
			else {// T_CLASS, T_INTERFACE
				$class = $group['tokens'][0][1];
				if ($namespace) {
					// just a heuristic; it could break on badly-organized code
					$class = $namespace.'\\'.$class;
				}
				$classes[] = $class;
			}
		}
		
		return $classes;
	}
	
	private static function get_token_groups($tokens, array $groups) {
		$matches = [];
		
		for ($i = 0,$count = count($tokens); $i < $count; ++$i) {
			$token = $tokens[$i];
			if (is_array($token)) {
				foreach ($groups as $key => $include) {
					if ($token[0] === $key) {
						// accumulate include types
						$match = [];
						while (++$i < $count && is_array($tokens[$i]) && array_search($tokens[$i][0], $include, true) !== false) {
							if ($tokens[$i][0] !== T_WHITESPACE) {
								$match[] = $tokens[$i];
							}
						}
						$matches[] = [
							'type' => $key,
							'tokens' => $match,
						];
						--$i;
					}
				}
			}
		}
		
		return $matches;
	}
}
