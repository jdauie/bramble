<?php

namespace Jacere\Bramble\Core\Routing;

use Jacere\Bramble\Core\Request;

class Route {
	
	const REGEX_KEY_NAME = '[a-zA-Z-_]++';
	const REGEX_ESCAPE = '[.\+*?[^\]${}=!|]';

	const R_LITERAL = '';
	const R_ANYTHING = '[^/]++';

	const R_UUID = '[0-9a-fA-F]{8}-(?:[0-9a-fA-F]{4}-){3}[0-9a-fA-F]{12}';
	const R_NAMESPACE = '[a-zA-Z0-9\.]++';
	const R_NUM = '\d++';
	
	const R_SLUG = '[a-z0-9\-]++';
	const R_YEAR = '20\d{2}';
	const R_MONTH = '(1[0-2]|0[1-9])';

	const R_FILENAME = '[^/?%*:|"<> \\\\]+';
	
	// http://www.w3.org/TR/html5/forms.html#valid-e-mail-address
	const R_EMAIL = "[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*";
	
	
	private $m_uri;
	private $m_regexes;
	private $m_params;
	private $m_actions;
	private $m_method;
	private $m_methods;
	
	private function __construct($uri, array $regexes) {
		$this->m_uri     = $uri;
		$this->m_regexes = $regexes;
		$this->m_params  = [];
		$this->m_actions = [];
		$this->m_method  = true;
		$this->m_methods = [];
	}
	
	public function compile(array &$lookup) {
		// escape necessary characters other than :()<>
		$regex = preg_replace('`'.self::REGEX_ESCAPE.'`', '\\\\$0', $this->m_uri);
			
		// mark parentheses as non-capturing and optional
		if (strpos($regex, '(') !== false) {
			$regex = str_replace('(', '(?:', $regex);
			$regex = str_replace(')', ')?', $regex);
		}
			
		// match keys and replace with appropriate regexes
		if (preg_match_all(sprintf('/<(?<name>%s)>/', self::REGEX_KEY_NAME), $regex, $matches, PREG_SET_ORDER)) {
			foreach ($matches as $set) {
				$name = $set['name'];
				$key_regex = self::R_ANYTHING;
				if (isset($this->m_regexes[$name])) {
					$key_regex = $this->m_regexes[$name];
		
					if ($key_regex === self::R_LITERAL) {
						$key_regex = $name;
					}
				}
				$regex = str_replace($set[0], "(?<{$name}>{$key_regex})", $regex);
			}
		}
		
		// start with the lookup root
		$current = &$lookup;
		
		// find the starting literal components
		$position = strspn($this->m_uri ^ $regex, "\0");
		$similar = substr($this->m_uri, 0, $position);
		if (substr($similar, -1) === '(') {
			$similar = substr($similar, 0, -1);
		}
		if ($similar) {
			// add parts to the common lookup
			$similar_parts = array_filter(explode('/', $similar));
			foreach ($similar_parts as $part) {
				if (!isset($current[$part])) {
					$current[$part] = [];
				}
				$current = &$current[$part];
			}
		}
		
		// wrap with safe delimiter
		$delimiter = '`';
		if (strpos($regex, $delimiter)) {
			$delimiter = "\x02";
		}
		$regex = sprintf('%s^%s$%s', $delimiter, $regex, $delimiter);
		
		// build method mask
		$methods = 0;
		if ($this->m_methods) {
			foreach ($this->m_methods as $method) {
				$methods |= Request::method_flag($method);
			}
		}
		
		$compiled = new CompiledRoute($regex, $this->m_params, $this->m_actions, $methods, $this->m_method);
		
		// add compiled route to lookup
		$current[] = $compiled;
		
		return $compiled;
	}

	/**
	 * @param string $pattern
	 * @param string[] $regexes
	 * @return Route
	 */
	public static function create($pattern, array $regexes = NULL) {
		$regexes = $regexes ? $regexes : [];
		return new Route($pattern, $regexes);
	}

	/**
	 * Sets whether method mapping is enabled for action method names.
	 * @param bool $enabled
	 * @param string[] $allowed_methods
	 * @return Route
	 */
	public function method($enabled, array $allowed_methods = NULL) {
		$this->m_method = (bool)$enabled;
		$this->m_methods = $allowed_methods;
		return $this;
	}

	/**
	 * Sets the default values of the named parameters.
	 * @param string[] $params
	 * @return Route
	 */
	public function defaults(array $params) {
		foreach ($params as $key => $value) {
			$this->m_params[$key] = $value;
		}
		return $this;
	}

	/**
	 * Sets the action mappings for the named parameters.
	 * During evaluation, if the parameter identified by the current key exists, then the action is set to the current value.
	 * Parameters are evaluated in the order passed in to this method, so keys should be ordered from specific to general.
	 * @param string[] $actions
	 * @return Route
	 */
	public function actions(array $actions) {
		$this->m_actions = $actions;
		return $this;
	}
}
