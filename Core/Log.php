<?php

namespace Jacere\Bramble\Core;

class Log {
	
	private static $c_debug = [];
	
	public static function get() {
		return implode("\n", array_merge(self::$c_debug, [Stopwatch::instance()->getString()]));
	}

	public static function debug($message) {
		self::$c_debug[] = $message;
	}

	public static function time($name) {
		if ($sw = Stopwatch::instance()) {
			$sw->save($name);
		}
	}
}
