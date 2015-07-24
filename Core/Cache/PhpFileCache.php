<?php

namespace Jacere\Bramble\Core\Cache;

use Jacere\Bramble\Core\Directory;
use Jacere\Bramble\Core\Log;
use Jacere\Bramble\Core\Serialization\PhpSerializationHelper;

class PhpFileCache implements ICache {

	private $m_cache;
	
	public function __construct(array $options) {
		$this->m_cache = $options['path'];
	}

	public function version() {
		// TODO: Implement version() method.
	}

	public function clear() {
		$files = Directory::search($this->m_cache, Directory::R_ANY);
		foreach ($files as $file) {
			unlink($file);
			Log::debug(sprintf('[delete] %s', str_replace('\\', '/', $file)));
		}
		return $files;
	}

	public function get($key) {
		$path = self::path($key);
		if (file_exists($path)) {
			return require($path);
		}
		return NULL;
	}

	public function set($key, $value) {
		return file_put_contents(self::path($key), PhpSerializationHelper::serialize($value));
	}

	private function path($key) {
		return "{$this->m_cache}/.{$key}.php";
	}
}
