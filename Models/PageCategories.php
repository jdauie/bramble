<?php

namespace Jacere\Bramble\Models;

use Jacere\Bramble\Core\Database;

class PageCategories {

	public static function load($current, array $context = NULL) {
		$res = Database::query('
			SELECT t.name, t.slug FROM term_relationships r
			INNER JOIN term_taxonomy x ON r.taxonomy = x.id
			INNER JOIN terms t ON x.term = t.id
			WHERE x.taxonomy = :taxonomy
			AND r.object = :post
		', ['taxonomy' => 'category', 'post' => $context['id']]);
		
		return iterator_to_array($res->rows());
	}
}