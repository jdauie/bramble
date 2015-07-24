<?php

namespace Jacere\Bramble\Core;

abstract class Model {
	
	public static function load() {
		$class = get_called_class();
		return new $class();
	}
}