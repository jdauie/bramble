<?php

namespace Jacere\Bramble\Core;

use Jacere\Bramble\Core\Routing\Route;
use Jacere\Bramble\Core\Exception\KnownException;

abstract class Controller {
	
	protected $m_request;
	protected $m_response;

	/**
	 * Creates new instance. Override before() method to add initialization logic, rather than constructor.
	 * @param Request $request
	 */
	public function __construct(Request $request) {
		$this->m_request = $request;
		$this->m_response = new Response();
	}

	public static function map(array $row, array $fields, array $map = []) {
		$out = [];

		foreach ($fields as $key => $value) {
			if (is_int($key)) {
				$key = $value;
			}
			$out[$value] = $row[$key];
		}

		foreach ($map as $key => $func) {
			$value = isset($out[$key]) ? $out[$key] : NULL;
			$value = $func($value, $row, $out);
			$out[$key] = $value;
		}

		return $out;
	}

	public static function map_each(array $rows, array $fields, array $map) {
		$out = [];
		foreach ($rows as $row) {
			$out[] = self::map($row, $fields, $map);
		}
		return $out;
	}

	/**
	 * Gets the method portion of the action name based on the current HTTP method.
	 * @return string
	 */
	protected final function method() {
		return strtolower($_SERVER['REQUEST_METHOD']);
	}

	/**
	 * Gets the list of controller parameters. Implementers must append to parent params.
	 * Closures are supported for late evaluation.
	 * @return array
	 */
	protected function params() {
		return [
			'request' => $this->m_request,
			'response' => $this->m_response,
			'body' => function() {return $this->m_request->body();},
		];
	}

	/**
	 * Gets the routes that map to this controller.
	 * @return Route[]
	 */
	public abstract function routes();

	/**
	 * Initialization
	 */
	public function before() {
		$this->m_response
			->status(200)
			->content_type(Response::CONTENT_TYPE_HTML)
			->cache_control(Response::CACHE_CONTROL_NOCACHE)
			->expires(Response::HTTP_DATE_ANCIENT);
	}

	/**
	 * Finalization
	 */
	public function after() {
		//
	}

    /**
     * Performs the specified action.
     * @return Response
     * @throws KnownException
     */
	public function execute() {
		
		$this->before();
		
		// map action to method
		// $this->action_[method]_name()
		
		$route = $this->m_request->route();
		$method = $route->method() ? $this->method() : '';
		$action = sprintf('action_%s_%s', $method, $this->m_request->route()->action());
		
		Log::debug(sprintf('[action] %s->%s()', get_class($this), $action));
		
		if (!method_exists($this, $action)) {
			throw new KnownException('E_ROUTING_ACTION_NOT_FOUND', [
				'uri' => $this->m_request->uri(),
				'method' => $this->m_request->method(),
			]);
		}
		
		// fill in parameters automatically
		$dynamic = array_change_key_case($this->params());
		$args = [];
		$reflect = new \ReflectionMethod($this, $action);
		
		foreach ($reflect->getParameters() as $param) {
			$name = $param->getName();
			$name_lower = strtolower($name);
			$value = NULL;
			
			if (array_key_exists($name_lower, $dynamic)) {
				$value = $dynamic[$name_lower];
				if ($value instanceof \Closure) {
					$value = $value();
				}
				Log::debug(sprintf('[param] $%s from dynamic param', $name));
				
				// warn about missing type hint if applicable
				if (is_object($value) && get_class($value) !== 'stdClass' && !($param_class = $param->getClass())) {
					Log::debug(sprintf('[param] $%s should have type hint %s', $name, get_class($value)));
				}
			}
			else if (($arg = $this->m_request->route()->param($name)) !== NULL) {
				$value = $arg;
				Log::debug(sprintf('[param] $%s from Route param', $name));
			}
			else if (isset($_REQUEST[$name])) {
				$value = $_REQUEST[$name];
				Log::debug(sprintf('[param] $%s from $_REQUEST', $name));
			}
			else if ($param->isDefaultValueAvailable()) {
				$value = $param->getDefaultValue();
				Log::debug(sprintf('[param] $%s from default', $name));
			}
			else {
				Log::debug(sprintf('[param] $%s unmatched', $name));
			}
			
			// pass unmatched params as NULL for now
			$args[] = $value;
		}

		Log::time('~before');
		
		$body = $reflect->invokeArgs($this, $args);

		$this->m_response->body($body, true);

		$this->after();
		
		return $this->m_response;
	}
}
