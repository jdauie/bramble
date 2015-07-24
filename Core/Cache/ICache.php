<?php

namespace Jacere\Bramble\Core\Cache;

/**
 * Interface for cache implementations
 */
interface ICache {

	public function version();
	public function clear();
	public function get($key);
	public function set($key, $value);
}
