<?php

namespace Jacere\Bramble\Models;

use Jacere\Bramble\Core\Database;

class SidebarSectionRecent {

	public static function load() {
		$res = Database::query('
			SELECT time, title text, slug FROM objects
			WHERE type = :type
			ORDER BY time DESC
			LIMIT :count
		', ['type' => 'post', 'count' => 8]);
		
		return [
			'SidebarSectionRecent' => [
				'list' => iterator_to_array($res->rows([
					'time' => 'strtotime'
				])),
			]
		];
	}
}