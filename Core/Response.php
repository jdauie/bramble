<?php

namespace Jacere\Bramble\Core;

class Response {

	const HTTP_DATA_CHUNK_SIZE = 8192;
	const HTTP_DATE_ANCIENT = 'Sat, 5 Sept 1977 05:00:00 GMT';

	const CONTENT_TYPE_JSON = 'application/json';
	const CONTENT_TYPE_TEXT = 'text/plain';
	const CONTENT_TYPE_HTML = 'text/html';
	
	const CACHE_CONTROL_NOCACHE = 'no-cache, must-revalidate';
	
	public static $c_messages = [
		// Informational 1xx
		100 => 'Continue',
		101 => 'Switching Protocols',

		// Success 2xx
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',

		// Redirection 3xx
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found', // 1.1
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		// 306 is deprecated but reserved
		307 => 'Temporary Redirect',

		// Client Error 4xx
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',

		// Server Error 5xx
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
		509 => 'Bandwidth Limit Exceeded'
	];

	private $m_status = 200;
	private $m_header;
	private $m_body = NULL;
	private $m_protocol = 'HTTP/1.1';

	public function __construct(array $config = []) {
		$this->m_header = $config;
	}

	/**
	 * @param null $content
	 * @param bool $force
	 * @return $this|string
	 */
	public function body($content = NULL, $force = false) {
		if (!$force && $content === NULL) {
			return $this->m_body;
		}
		
		// special error conditions
		if ($content instanceof \Generator) {
			throw new \LogicException('Generator cannot be converted to response');
		}
		
		if ($encoder = Application::content_type_encoder($this->content_type())) {
			$content = $encoder($content);
		}

		$this->m_body = (string)$content;
		return $this;
	}
	
	public function protocol($protocol = NULL) {
		if ($protocol === NULL) {
			return $this->m_protocol;
		}

		$this->m_protocol = strtoupper($protocol);
		return $this;
	}

	public function status($status = NULL) {
		if ($status === NULL) {
			return $this->m_status;
		}
		else if (isset(self::$c_messages[$status])) {
			$this->m_status = (int)$status;
			return $this;
		}
		
		throw ServiceException::create(500, 'Unknown status value');
	}

	public function content_type($value = NULL) {
		return $this->header_auto(__FUNCTION__, $value);
	}

	public function content_length($value = NULL) {
		return $this->header_auto(__FUNCTION__, $value);
	}

	public function cache_control($value = NULL) {
		return $this->header_auto(__FUNCTION__, $value);
	}

	public function content_disposition($value = NULL) {
		return $this->header_auto(__FUNCTION__, $value);
	}

	public function expires($value = NULL) {
		return $this->header_auto(__FUNCTION__, $value);
	}

	public function header($name, $value = NULL) {
		$name = strtolower($name);
		if ($value === NULL) {
			return isset($this->m_header[$name]) ? $this->m_header[$name] : NULL;
		}
		// these should probably be cleaned
		$this->m_header[$name] = $value;
		return $this;
	}
	
	private function header_auto($function_name, $value) {
		$name = str_replace(' ', '-', ucwords(str_replace('_', ' ', $function_name)));
		return $this->header($name, $value);
	}
	
	public function headers(array $headers = NULL) {
		if ($headers === NULL) {
			return $this->m_header;
		}
		else {
			foreach ($headers as $key => $value) {
				$this->m_header[$key] = $value;
			}
			return $this;
		}
	}

	/**
	 * Send data immediately with the specified content type.
	 * @param $data
	 * @param string $content_type
	 */
	public function passthru($data, $content_type) {
		ob_clean();
		ini_set('output_buffering', 0);
		ini_set('zlib.output_compression', 0);

		$this->content_type($content_type);
		$this->send_headers(true);
		
		echo $data;
		exit;
	}

	/**
	 * Send the contents of a file immediately with the specified content type, with chunking and filename support.
	 * @param string $path
	 * @param string $content_type
	 * @throws ServiceException
	 */
	public function fpassthru($path, $content_type = NULL) {
		
		if (!$content_type) {
			$content_type = self::get_content_type($path);
		}
		
		if (!$content_type) {
			throw ServiceException::create(404, 'Unknown response content type');
		}
		
		set_time_limit(0);
		ignore_user_abort(false);
		
		ob_clean();
		ini_set('output_buffering', 0);
		ini_set('zlib.output_compression', 0);
		
		$this->content_type($content_type);
		$this->content_length(filesize($path));
		$this->content_disposition("attachment; filename*=UTF-8''".rawurlencode(basename($path)));

		$this->send_headers(true);

		@ob_end_flush();
		flush();

		$fd = fopen($path, 'rb');

		while ($chunk = fread($fd, self::HTTP_DATA_CHUNK_SIZE)) {
			echo $chunk;
			@ob_end_flush();
			flush();
		}

		fclose($fd);
		
		exit;
	}
	
	private static function get_content_type($path) {
		$ext = pathinfo($path, PATHINFO_EXTENSION);
		if (!$ext) {
			return false;
		}
		$ext = strtolower($ext);
		switch ($ext) {
			case 'gif':
				return 'image/gif';
			case 'jpg':
			case 'jpeg':
				return 'image/jpeg';
			case 'png':
				return 'image/png';
			default:
				return false;
		}
	}

	private function send_headers($replace = false) {
		ob_end_clean();
		
		header(sprintf('%s %s %s', $this->protocol(), $this->status(), self::$c_messages[$this->status()]));
		
		$this->m_header['Date'] = gmdate('D, d M Y H:i:s T');
		
		foreach ($this->m_header as $key => $value) {
			header(sprintf('%s: %s', $key, $value), $replace);
		}
		flush();
		
		define('RESPONSE_HEADERS_SENT', true);
	}

	public function send() {
		$this->send_headers(true);
		echo $this->body();
		
		// todo: debug
		echo "\n<pre>\n".Log::get()."</pre>";
		
		flush();
	}
}
