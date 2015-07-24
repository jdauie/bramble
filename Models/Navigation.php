<?php

namespace Jacere\Bramble\Models;

use Jacere\Bramble\Core\Database;

class Navigation {

	public static function load() {
		$res = Database::query('
			SELECT title text, slug, menu_order FROM objects
			WHERE type = :type
			AND menu_order != 0
			ORDER BY menu_order ASC
		', ['type' => 'page']);

		$nav = [];
		$global = [];
		foreach($res->rows_by_key('menu_order', true) as $order => $row) {
			if ($order < 0) {
				$global[] = $row;
			}
			else {
				$nav[] = $row;
			}
		}
		
		return [
			'Navigation' => [
				'nav-list' => $nav,
				'nav-list-global' => $global,
			]
		];
	}
}