<?php

namespace Jacere\Bramble\Core;

use Jacere\Bramble\Core\Cache\Cache;
use Jacere\Bramble\Core\Exception\KnownException;

class Application {
	
	/**
	 * Application entry point.
	 */
	public static function start() {
		
		// hide errors
		//error_reporting(E_ALL);
		//ini_set('display_errors', 0);

		// use UTC on server
		date_default_timezone_set('UTC');
		
		// error/exception Handlers
		set_error_handler([__CLASS__, 'error']);
		set_exception_handler([__CLASS__, 'exception']);
		register_shutdown_function([__CLASS__, 'shutdown']);
		
		// configuration
		define('BRAMBLE_URL', self::get_app_url());

		Stopwatch::start()->register();
		Log::time('~init');
		
		// handle request
		Request::execute();
	}
	
	public static function cache($name, $options) {
		Cache::init($name, $options);
	}
	
	/**
	 * Gets the registered encoder for the specified content type.
	 * @param string $type
	 * @return callable|null
	 */
	public static function content_type_encoder($type) {
		switch ($type) {
			case Response::CONTENT_TYPE_JSON: return function ($content) {
				$options = JSON_UNESCAPED_SLASHES | (APP_DEBUG_SERVER ? JSON_PRETTY_PRINT : 0);
				return json_encode($content, $options);
			};
		}
		return NULL;
	}
	
	/**
	 * Gets the registered decoder for the specified content type.
	 * @param string $type
	 * @return null|string
	 */
	public static function content_type_decoder($type) {
		switch ($type) {
			case Response::CONTENT_TYPE_JSON: return 'json_decode';
		}
		return NULL;
	}
	
	private static function get_app_url() {
		$path = BRAMBLE_BASE;
		if ($path[0] !== '/' || substr($path, -1) !== '/') {
			throw new \Exception('invalid url base');
		}
		$domain = $_SERVER['HTTP_HOST'];
		$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
		$app_url = sprintf('%s://%s%s', $protocol, $domain, $path);
		return $app_url;
	}
	
	/**
	 * Global exception handler.
	 * @param \Exception $e
	 */
	public static function exception($e) {
		
		if (!($e instanceof KnownException)) {
			$e = new KnownException('E_INTERNAL', NULL, $e);
		}
		
		$e->response()->send();
		
		exit();
	}

	/**
	 * Global error handler.
	 * @param $err_severity
	 * @param $err_msg
	 * @param $err_file
	 * @param $err_line
	 * @param array $err_context
	 * @return bool
	 * @throws \ErrorException
	 */
	public static function error($err_severity, $err_msg, $err_file, $err_line, array $err_context) {
		// error was suppressed with the @-operator
		if (0 === error_reporting()) {
			return false;
		}

		// type hinting
		if ($err_severity === E_RECOVERABLE_ERROR) {
			// Should I make a more accurate regex?
			// '/^Argument \d+ passed to (?:\w+::)?\w+\(\) must be an instance of (\w+), (\w+) given/'
			/*if (preg_match('/ must be an instance of (\w+), (\w+) given/', $err_msg, $matches)) {
				if ($matches[1] === ($matches[2] === 'double' ? 'float' : $matches[2])) {
					return true;
				}
			}*/
		}
		else if ($err_severity === E_WARNING) {
			// ignore specific warnings
			//if ($err_msg === "ldap_search(): Partial search results returned: Sizelimit exceeded") {
			//	return true;
			//}
		}
		
		// convert error to exception
		throw new \ErrorException($err_msg, 0, $err_severity, $err_file, $err_line);
	}

	/**
	 * Global shutdown handler.
	 */
	public static function shutdown() {
		// check for fatal error vs normal shutdown
		if ($error = error_get_last()) {
			
			$unhandled_errors = [
				E_ERROR,
				E_PARSE,
				E_CORE_ERROR,
				E_CORE_WARNING,
				E_COMPILE_ERROR,
				E_COMPILE_WARNING,
				//E_STRICT,
			];
			
			// convert fatal error to unhandled exception.
			if (array_search($error['type'], $unhandled_errors, true) !== false) {
				// pass it to the exception handler
				self::exception(new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']));
				exit;
			}
		}

		if (!defined('RESPONSE_HEADERS_SENT')) {
			self::exception(new KnownException('E_UNKNOWN'));
			exit;
		}
	}
}
