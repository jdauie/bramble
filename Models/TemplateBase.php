<?php

namespace Jacere\Bramble\Models;

class TemplateBase {

	public static function load() {
		return array_merge([
			'TemplateBase' => [
				'title' => 'bramble',// default title
				'root-url' => BRAMBLE_URL,
			],
			'Header' => [
				'root-url' => BRAMBLE_URL
			],
		],
			Navigation::load(),
			SidebarSectionRecent::load(),
			SidebarSectionArchives::load(),
			SidebarSectionCategories::load()
		);
	}
}