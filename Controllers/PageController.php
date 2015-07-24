<?php

namespace Jacere\Bramble\Controllers;

use Jacere\Bramble\Core\Log;
use Jacere\Bramble\Core\Routing\Route;
use Jacere\Bramble\Models\Posts;
use Jacere\Bramble\Models\TemplateBase;

class PageController extends TemplateController {
	
	public function routes() {
		return [
			Route::create('<slug>', [
				'name' => Route::R_SLUG,
			])->defaults([
				'action' => 'page',
			]),
		];
	}
	
	public function action_get_page($slug) {
		return $this->create_page(Posts::page($slug));
	}

	private function create_page($rows) {
		$data = array_merge_recursive(TemplateBase::load(), [
			'Page' => reset($rows),
		]);

		Log::time('load');
		return $this->m_manager->evaluate('Page', $data);
	}
}