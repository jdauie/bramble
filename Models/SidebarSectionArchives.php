<?php

namespace Jacere\Bramble\Models;

use Jacere\Bramble\Core\Database;

class SidebarSectionArchives {

	public static function load() {
		$res = Database::query('
			SELECT strftime(\'%Y-%m\',time) month, COUNT(*) count FROM objects
			WHERE type = :type
			GROUP BY month
			ORDER BY time DESC
		', ['type' => 'post']);
		
		return [
			'SidebarSectionArchives' => [
				'list' => iterator_to_array($res->rows([
					'month' => 'strtotime'
				])),
			]
		];
	}
}