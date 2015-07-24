<?php

namespace Jacere\Bramble\Models;

use Jacere\Bramble\Core\Database;

class SidebarSectionCategories {

	public static function load() {
		$res = Database::query('
			SELECT t.name text, t.slug, COUNT(r.object) count FROM terms t
			INNER JOIN term_taxonomy x ON t.id = x.term
			INNER JOIN term_relationships r ON r.taxonomy = x.id
			WHERE x.taxonomy = :taxonomy
			GROUP BY r.taxonomy
			ORDER BY t.name ASC
		', ['taxonomy' => 'category']);
		
		return [
			'SidebarSectionCategories' => [
				'list' => iterator_to_array($res->rows()),
			]
		];
	}
}