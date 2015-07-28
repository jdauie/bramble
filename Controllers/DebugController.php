<?php

namespace Jacere\Bramble\Controllers;

use Jacere\Bramble\Core\Cache\Cache;
use Jacere\Bramble\Core\ClassLoader;
use Jacere\Bramble\Core\Controller;
use Jacere\Bramble\Core\Database;
use Jacere\Bramble\Core\Database\Builder\Builder;
use Jacere\Bramble\Core\Directory;
use Jacere\Bramble\Core\Image\GdImage;
use Jacere\Bramble\Core\Log;
use Jacere\Bramble\Core\Routing\Route;
use Jacere\Skhema\TemplateManager;
use Jacere\Subvert\Subvert;

final class DebugController extends Controller {

	public function routes() {
		return [
			Route::create('debug/<action>', [
				'action' => Route::R_SLUG,
			])->method(false, ['GET']),
		];
	}
	
	public function action__phpinfo() {
		ob_start();
		phpinfo();
		$info = ob_get_contents();
		ob_end_clean();
		return $info;
	}

	public function action__initialize() {
		self::action__clean();
		self::action__database();
		self::action__content();
		
		return NULL;
	}

	public function action__database() {
		Builder::build(BRAMBLE_DIR.'/.init/.data.yaml');
		return NULL;
	}

	public function action__templates() {
		TemplateManager::create(BRAMBLE_TEMPLATES, true);
		return NULL;
	}

	public function action__content() {

		// load posts/pages
		// parse and create thumbnails

		// in the future, I could alert about unused images/refs, dead links, etc.

		$images = [];

		$subvert_options = [
			//'root_url' => BRAMBLE_URL,
			'image_callback' => function(&$attributes) use (&$images) {
					$src = $attributes['src'];
					if (!isset($images[$src])) $images[$src] = [];
					$width = (isset($attributes['width']) && ctype_digit($attributes['width'])) ? (int)$attributes['width'] : 0;
					$height = (isset($attributes['height']) && ctype_digit($attributes['height'])) ? (int)$attributes['height'] : 0;
					$key = sprintf('%sx%s', $width, $height);
					if (!isset($images[$src][$key])) $images[$src][$key] = ['width' => $width, 'height' => $height];
				},
			'link_callback' => function(&$attributes) {},
		];

		$res = Database::query('
			SELECT Content FROM Objects
			WHERE Type IN (:types)
		', ['types' => ['post', 'page']]);

		foreach ($res->column() as $content) {
			Subvert::Parse($content, $subvert_options);
		}

		Log::time('load+subvert');

		ksort($images, SORT_NATURAL);
		foreach ($images as $image_path => $versions) {
			foreach ($versions as $version => $dimensions) {
				if ($version !== '0x0') {
					$parts = pathinfo($image_path);
					$thumbnail_path = sprintf('%s/%s-%s.%s', $parts['dirname'], $parts['filename'], $version, $parts['extension']);
					Log::debug("[resize] $thumbnail_path]");

					$src = BRAMBLE_DIR.$image_path;
					$dst = BRAMBLE_DIR.$thumbnail_path;
					if (file_exists($src)) {
						$image = GdImage::load($src);
						$image->resize($dimensions['width'], $dimensions['height'])
							->save($dst)
							->dispose();
						$image->dispose();
					}
				}
			}
		}

		Log::time('thumbnails');

		return NULL;
	}

	public function action__clean() {
		Cache::clear();
		return NULL;
	}

	public function action__controllers() {
		$output = [];
		foreach (self::get_reflection_controllers() as $controller) {
			$output[$controller->getName()] = array_values(array_map(
				function(\ReflectionMethod $a) {
					return $a->name;
				}, 
				array_filter($controller->getMethods(), function($a) {return strpos($a->name, 'action_') === 0;})
			));
		}
		return json_encode($output);
	}

	public function action__routes() {
		// list routes
	}

	public function action__autoload() {
		$map = ClassLoader::instance(true);
		
		return NULL;
	}

	private static function get_reflection_controllers() {
		foreach (Directory::search(BRAMBLE_DIR.'/Controllers', Directory::R_PHP) as $file) {
			$controller_name = sprintf('%s\%s', BRAMBLE_NS, substr(substr($file, strlen(BRAMBLE_DIR) + 1), 0, -4));
			try {
				$reflect = new \ReflectionClass($controller_name);
				if (!$reflect->isAbstract()) {
					yield $reflect;
				}
			}
			catch (\Exception $e) {
			}
		}
	}
}
