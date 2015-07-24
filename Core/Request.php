<?php

namespace Jacere\Bramble\Core;

use Jacere\Bramble\Core\Routing\CompiledRoute;
use Jacere\Bramble\Core\Routing\RouteMap;
use Jacere\Bramble\Core\Exception\KnownException;

class Request {

	// change to spl enum?
	const HTTP_GET = 1;
	const HTTP_PUT = 2;
	const HTTP_POST = 4;
	const HTTP_PATCH = 8;
	const HTTP_DELETE = 16;
	
	private static $c_headers;
	
	private $m_uri;
	private $m_method;
	private $m_route;
	private $m_body;
	
	/** @var Request */
	private static $c_instance;
	
	private function __construct($uri) {
		$this->m_uri = $uri;
		$this->m_method = self::method_flag($_SERVER['REQUEST_METHOD']);
	}

	/**
	 * Gets the Request instance for the current URL.
	 * @return Request
	 */
	public static function get() {
		if (self::$c_instance === NULL) {
			$uri = self::detect();
			self::$c_instance = new self($uri);
		}
		return self::$c_instance;
	}
	
	public static function method_flag($method) {
		return constant(__CLASS__."::HTTP_$method");
	}

	/**
	 * Gets the named HTTP request header.
	 * @param $name
	 * @return string|null
	 */
	public static function header($name) {
		$name = strtolower($name);
		if (!self::$c_headers) {
			self::$c_headers = self::headers();
		}
		return (isset(self::$c_headers[$name]) ? self::$c_headers[$name] : NULL);
	}

	private static function detect() {
		// compare with PATH_INFO, PHP_SELF, REDIRECT_URL for greater reliability
		$uri = $_SERVER['REQUEST_URI'];
		$uri = substr($uri, strlen(BRAMBLE_BASE));
		return $uri;
	}
	
	private static function headers($manual = false) {
		$headers = [];
		if (!$manual && function_exists('getallheaders')) {
			$headers = apache_request_headers();
		}
		else {
			foreach ($_SERVER as $name => $value) {
				if (strncmp($name, 'HTTP_', 5) === 0) {
					$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
				}
			}
		}
		return array_change_key_case($headers);
	}

	/**
	 * Gets the URI.
	 * @return string
	 */
	public function uri() {
		return $this->m_uri;
	}
	
	/**
	 * Gets the HTTP method.
	 */
	public function method() {
		return $_SERVER['REQUEST_METHOD'];
	}

	/**
	 * Gets the matched Route.
	 * @return CompiledRoute
	 */
	public function route() {
		return $this->m_route;
	}

	/**
	 * Gets the body of the HTTP request, if it exists.
	 * @return string|null
	 */
	public function body() {
		if (!$this->m_body && $_SERVER['REQUEST_METHOD'] !== 'GET') {
			$this->m_body = file_get_contents('php://input');
			
			$content_type = strtok($this->header('Content-Type'), ';');
			if ($decoder = Application::content_type_decoder($content_type)) {
				$this->m_body = $decoder($this->m_body);
			}
		}
		return $this->m_body;
	}

	/**
	 * Execute the controller action.
	 */
	public static function execute() {
		
		$instance = self::get();
		
		// take over buffering
		if (ob_get_level() === 0) {
			ob_start();
		}
		
		Log::debug("[uri] {$instance->method()} /{$instance->m_uri}");
		
		try {
			$routes = RouteMap::instance();
			$instance->m_route = $routes->find($instance->m_uri, $instance->m_method);
			
			if (!$instance->m_route) {
				throw new KnownException('E_ROUTING_ROUTE_NOT_FOUND', [
					'uri' => $instance->uri(),
					'method' => $instance->method(),
				]);
			}
			
			$controller_class_name = $instance->m_route->controller();
			
			try {
				$controller = new $controller_class_name($instance);
			}
			catch (\Exception $e) {
				throw new KnownException('E_INTERNAL_CONTROLLER_FAILED');
			}
			
			if (!$controller instanceof Controller) {
				throw new KnownException('E_INTERNAL_CONTROLLER_INVALID');
			}
			
			$response = $controller->execute();
		}
		catch (KnownException $e) {
			$response = $e->response();
		}
		catch (\Exception $e) {
			$response = (new KnownException('E_INTERNAL', NULL, $e))->response();
		}

		/*$leaked = ob_get_contents();
		if ($leaked) {
			// ignore for now
		}*/
		ob_end_clean();

		// write instrumentation to log
		Log::time('~response');
		Stopwatch::instance()->stop();
		
		$response->body($response->body());
		
		$response->send();
	}
}
