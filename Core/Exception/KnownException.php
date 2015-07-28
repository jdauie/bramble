<?php

namespace Jacere\Bramble\Core\Exception;

use Jacere\Bramble\Core\Response;
use Spyc;

class KnownException extends \Exception {
	
	private static $c_groups = [
		'INTERNAL' => 500,
		'ROUTING' => 404,
	];
	
	private $m_status;
	private $m_name;

    /**
     * @param string $name
     * @param array $args
     * @param \Exception $previous
     */
	public function __construct($name, $args = NULL, \Exception $previous = NULL) {
		
		$group = explode('_', $name, 3)[1];
		$status = 500;
		if (isset(self::$c_groups[$group])) {
			$status = self::$c_groups[$group];
		}
		
		$names = NULL;
		if (class_exists('Spyc')) {
			$names = Spyc::YAMLLoad(BRAMBLE_DIR . '/messages.yaml')['errors'];
		}
		
		if (isset($names[$name])) {
			$message = $names[$name];
			if ($args) {
				$keys = array_keys($args);
				foreach ($keys as &$key) {
					$key = ':'.$key;
				}
				$message = strtr($message, array_combine($keys, $args));
			}
		} else {
			$message = isset(Response::$c_messages[$status]) ? Response::$c_messages[$status] : '';
		}
		
		
		$this->m_status = (int)$status;

		$this->m_name = $name;
		
		parent::__construct($message, 0, $previous);
	}

	/**
	 * Gets the HTTP status code.
	 * @return int
	 */
	public function status() {
		return $this->m_status;
	}

	/**
	 * Gets the JSON object representation.
	 * @return object
	 */
	public function getResponseError() {
		return (object)[
			'name' => $this->m_name,
			'message' => $this->getMessage(),
		];
	}

	/**
	 * Gets the JSON object representation of the internal error.
	 * @return array
	 */
	public function getResponseInternal() {
		$errors = [];
		$e = $this;
		while ($e = $e->getPrevious()) {

			$trace = $e->getTrace();
			array_unshift($trace, [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);

			$trace = array_map(function ($a) {
				if (isset($a['class'])) {
					$func = sprintf('%s%s%s', str_replace('\\', '/', $a['class']), $a['type'], $a['function']);
				} else if (isset($a['function'])) {
					$func = str_replace('\\', '/', $a['function']);
				}
				if (isset($a['file'])) {
					$file = sprintf('%s[%d]', str_replace('\\', '/', $a['file']), $a['line']);
				} else {
					$file = '[internal function]';
				}
				if (isset($func)) {
					return sprintf('%s: %s()', $file, $func);
				}
				return $file;
			}, $trace);

			$error = [
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
				'trace' => $trace,
			];
			
			if ($e instanceof IAdditionalInfo) {
				$error['info'] = $e->getAdditionalInfo();
			}

			$errors[] = $error;
		}
		return $errors;
	}

	/**
	 * @return Response
	 */
	public function response() {
		$obj = $this->getResponseError();
		if ($this->getPrevious()) {
			$obj->internal = $this->getResponseInternal();
		}
		return (new Response())
			->body('<pre>'.json_encode($obj, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).'</pre>');
	}
}