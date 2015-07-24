<?php

namespace Jacere\Bramble\Core\Routing;

use Jacere\Bramble\Core\Request;
use Jacere\Bramble\Core\Directory;
use Jacere\Bramble\Core\Cache\Cache;
use Jacere\Bramble\Core\Serialization\IPhpSerializable;
use Jacere\Bramble\Core\Serialization\PhpSerializationMap;
use Jacere\Bramble\Core\Controller;

class RouteMap implements IPhpSerializable {
	
	private $m_lookup;
	
	public function __construct(array $lookup) {
		$this->m_lookup = $lookup;
	}
	
	public function phpSerializable(PhpSerializationMap $map) {
		return $map->newObject($this, [$this->m_lookup]);
	}

    /**
     * Finds the first Route that matches the specified URI.
     * @param string $uri
     * @param int $method
     * @return CompiledRoute
     * @throws \Exception
     */
	public function find($uri, $method) {
		// ignore query string for matching
		$uri_without_params = strtok($uri, '?');
		
		// shortcut to route candidates
		$parts = explode('/', $uri_without_params);
		$current = $this->m_lookup;
		foreach ($parts as $part) {
			if (isset($current[$part])) {
				$new = $current[$part];
				if (!is_array($new)) {
					break;
				}
				$current = $new;
			}
		}
        /** @var CompiledRoute[] $routes */
		$routes = array_filter($current, 'is_object');
		
		// test route candidates
		foreach ($routes as $route) {
			if ($match = $route->match($uri_without_params, $method)) {
				return $match;
			}
		}
		
		return NULL;
	}

	/**
	 * @param bool $rebuild
	 * @return RouteMap
	 */
	public static function instance($rebuild = false) {
		return Cache::load('routes', function() {
			$lookup = [];

			// todo: add support for optional centralized routes
			
			foreach (Directory::search(BRAMBLE_DIR.'/Controllers', Directory::R_PHP, [Directory::R_HIDDEN]) as $file) {
				$controller_name = sprintf('%s\%s', BRAMBLE_NS, substr(substr($file, strlen(BRAMBLE_DIR) + 1), 0, -4));
				$reflection_class = new \ReflectionClass($controller_name);
				if (!$reflection_class->isAbstract()) {
					$controller = new $controller_name(Request::get());
					if (!($controller instanceof Controller)) {
						throw new \Exception('Only controllers are allowed in this directory');
					}
					foreach ($controller->routes() as $route) {
						$route->defaults([
							'controller' => $controller_name,
						]);
						$route->compile($lookup);
					}
				}
			}
			
			return new self($lookup);
		}, $rebuild);
	}
}
