<?php

namespace Jacere\Bramble\Core\Routing;

use Jacere\Bramble\Core\Serialization\IPhpSerializable;
use Jacere\Bramble\Core\Serialization\PhpSerializationMap;

class CompiledRoute implements IPhpSerializable {
	
	private $m_regex;
	private $m_params;
	private $m_actions;
	private $m_methods;
	private $m_method;
	
	/**
	 * @param string $regex
	 * @param array $variables
	 * @param array $actions
	 * @param int $methods
	 * @param bool $method
	 */
	public function __construct($regex, array $variables, array $actions, $methods, $method) {
		$this->m_regex   = $regex;
		$this->m_params  = $variables;
		$this->m_actions = $actions;
		$this->m_methods = $methods;
		$this->m_method  = (bool)$method;
	}
	
	public function phpSerializable(PhpSerializationMap $map) {
		return $map->newObject($this, [
			$this->m_regex,
			$this->m_params,
			$this->m_actions,
			$this->m_methods,
			(int)$this->m_method
		]);
	}
	
	/**
	 * Gets whether mapping is enabled for action names based on HTTP method.
	 * @return bool
	 */
	public function method() {
		return $this->m_method;
	}
	
	/**
	 * Gets the controller name parameter.
	 */
	public function controller() {
		return $this->m_params['controller'];
	}
	
	/**
	 * Gets the action name parameter.
	 */
	public function action() {
		return $this->m_params['action'];
	}
	
	/**
	 * Gets the named parameter.
	 * @param string $key
	 * @return string|null
	 */
	public function param($key) {
		return isset($this->m_params[$key]) ? $this->m_params[$key] : NULL;
	}
	
	/**
	 * Evaluates the URI against the current pattern.
	 * @param string $uri
	 * @param int $method
	 * @return CompiledRoute
	 * @throws \LogicException
	 */
	public function match($uri, $method) {

		// check method
		if ($this->m_methods) {
			if (!($this->m_methods & $method)) {
				return NULL;
			}
		}

		// check pattern
		if (preg_match($this->m_regex, $uri, $matches)) {
			foreach ($matches as $name => $value) {
				if (is_string($name)) {
					$this->m_params[$name] = $value;
				}
			}
			
			foreach ($this->m_actions as $key => $value) {
				if (isset($matches[$key])) {
					$this->m_params['action'] = $value;
					break;
				}
			}
			
			if (!isset($this->m_params['controller']) || !isset($this->m_params['action'])) {
				throw new \LogicException('Matched route must define controller and action');
			}
			
			return $this;
		}
		return NULL;
	}
}
