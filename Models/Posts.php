<?php

namespace Jacere\Bramble\Models;

use Jacere\Bramble\Core\Database;
use DateTime;

class Posts {

	public static function page($slug) {
		$res = Database::query('
			SELECT p.title, p.slug, p.content FROM objects p
			WHERE p.type = :type
			AND p.slug = :slug
			LIMIT 0, 1
		', ['type' => 'page', 'slug' => $slug]);

		return iterator_to_array($res->rows());
	}

	public static function item(DateTime $start, DateTime $end, $slug) {
		$res = Database::query('
			SELECT p.id, p.time, p.title, p.slug, p.content, u.display FROM objects p
			INNER JOIN users u ON p.author = u.id
			WHERE p.type = :type
			AND p.slug = :slug
			AND :start <= p.time AND p.time < :end
			LIMIT 0, 1
		', ['type' => 'post', 'slug' => $slug, 'start' => $start->format('Y-m-d'), 'end' => $end->format('Y-m-d')]);

		return iterator_to_array($res->rows([
			'time' => 'strtotime'
		]));
	}

	public static function recent() {
		$res = Database::query('
			SELECT p.id, p.time, p.title, p.slug, p.content, u.display FROM objects p
			INNER JOIN users u ON p.author = u.id
			WHERE p.type = :type
			ORDER BY p.time DESC
			LIMIT :offset, :count
		', ['type' => 'post', 'offset' => 0, 'count' => 10]);

		return iterator_to_array($res->rows([
			'time' => 'strtotime'
		]));
	}

	public static function archive(DateTime $start, DateTime $end) {
		$res = Database::query('
			SELECT p.id, p.time, p.title, p.slug, p.content, u.display FROM objects p
			INNER JOIN users u ON p.author = u.id
			WHERE p.type = :type
			AND :start <= p.time AND p.time < :end
			ORDER BY p.time DESC
		', ['type' => 'post', 'start' => $start->format('Y-m-d'), 'end' => $end->format('Y-m-d')]);

		return iterator_to_array($res->rows([
			'time' => 'strtotime'
		]));
	}

	public static function category($slug) {
		$res = Database::query('
			SELECT p.id, p.time, p.title, p.slug, p.content, u.display FROM objects p
			INNER JOIN users u ON p.author = u.id
			INNER JOIN term_relationships r ON p.id = r.object
			INNER JOIN term_taxonomy x ON r.taxonomy = x.id
			INNER JOIN terms t ON x.term = t.id
			WHERE p.type = :type
			AND x.taxonomy = :taxonomy
			AND t.slug = :slug
			ORDER BY p.time DESC
		', ['type' => 'post', 'taxonomy' => 'category', 'slug' => $slug]);

		return iterator_to_array($res->rows([
			'time' => 'strtotime'
		]));
	}
}