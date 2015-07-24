<?php

namespace Jacere\Bramble\Core;

use FilesystemIterator;
use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class Directory {
	
	const R_PHP = '/\.php$/i';
	const R_HIDDEN = '/^\./';
	const R_ANY = '/^/';
	
	public static function search($path, $pattern, array $exclude = NULL) {
		$path = is_array($path) ? $path : [$path];
		$exclude = $exclude ? $exclude : [];
		
		$files = [];
		
		foreach ($path as $p) {
			$directory = new RecursiveDirectoryIterator($p, FilesystemIterator::FOLLOW_SYMLINKS);
			$filter = new RecursiveCallbackFilterIterator($directory, function (SplFileInfo $current, $key, RecursiveIterator $iterator) use ($exclude, $pattern) {
                if ($iterator->hasChildren()) {
					foreach ($exclude as $s) {
						if (preg_match($s, $current->getFilename())) {
							return false;
						}
					}
					return true;
				}
				return $current->isFile() && preg_match($pattern, $current->getFilename());
			});
			
			$iterator = new RecursiveIteratorIterator($filter, RecursiveIteratorIterator::LEAVES_ONLY, RecursiveIteratorIterator::CATCH_GET_CHILD);
			
			foreach ($iterator as $info) {
				$files[] = $info->getPathname();
			}
		}
		
		return $files;
	}
}
