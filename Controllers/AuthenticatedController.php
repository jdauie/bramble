<?php

namespace Jacere\Bramble\Controllers;

use Jacere\Bramble\Core\Controller;
use Jacere\Bramble\Core\Response;

abstract class AuthenticatedController extends Controller {
	
	protected function params() {
		return array_merge(parent::params(), [
			//
		]);
	}

	public function before() {
		parent::before();

		$this->m_response
			->status(200)
			->content_type(Response::CONTENT_TYPE_JSON)
			->cache_control(Response::CACHE_CONTROL_NOCACHE)
			->expires(Response::HTTP_DATE_ANCIENT);
	}
}
