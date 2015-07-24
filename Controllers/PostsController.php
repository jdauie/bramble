<?php

namespace Jacere\Bramble\Controllers;

use DateTime;
use Jacere\Bramble\Core\Controller;
use Jacere\Bramble\Core\Log;
use Jacere\Bramble\Core\Routing\Route;
use Jacere\Bramble\Models\PageCategories;
use Jacere\Bramble\Models\Posts;
use Jacere\Bramble\Models\TemplateBase;

class PostsController extends TemplateController {
	
	public function routes() {
		return [
			Route::create('', [
			])->defaults([
				'action' => 'recent',
			]),
			Route::create('<year>(/<month>(/<item>))', [
				'year'  => Route::R_YEAR,
				'month' => Route::R_MONTH,
				'item'  => Route::R_SLUG,
			])->defaults([
				'action' => 'archive',
			])->actions([
				'item' => 'item',
			]),
			Route::create('category/<name>', [
				'name' => Route::R_SLUG,
			])->defaults([
				'action' => 'category',
			]),
		];
	}
	
	public function action_get_recent() {
		return $this->create_posts('recent', Posts::recent());
	}

	public function action_get_archive($year, $month = NULL) {

		if ($month) {
			$start = DateTime::createFromFormat('Y-m-d', sprintf('%s-%s-%s', $year, $month, 1));
			$end = (clone $start);
			$end->add(new \DateInterval('P1M'));
			$title = $start->format('Y-m');
		}
		else {
			$start = DateTime::createFromFormat('Y-m-d', sprintf('%s-%s-%s', $year, 1, 1));
			$end = (clone $start);
			$end->add(new \DateInterval('P1Y'));
			$title = $start->format('Y');
		}
		
		return self::create_posts("archive &raquo; $title", Posts::archive($start, $end));
	}
	
	public function action_get_item($year, $month, $item) {
		$start = DateTime::createFromFormat('Y-m-d', sprintf('%s-%s-%s', $year, $month, 1));
		$end = (clone $start);
		$end->add(new \DateInterval('P1M'));
		
		$posts = Posts::item($start, $end, $item);
		$title = reset($posts)['title'];
		
		return self::create_posts("item &raquo; $title", $posts);
	}
	
	public function action_get_category($name) {
		return self::create_posts("category &raquo; $name", Posts::category($name));
	}

	private function create_posts($title, $rows) {
		$data = array_merge_recursive(TemplateBase::load(), [
			'Posts' => [
				'title' => $title,
				'list' => self::create_posts_list($rows),
			],
		]);

		Log::time('load');
		return $this->m_manager->evaluate('Posts', $data);
	}

	private static function create_posts_list(array $rows) {
		return Controller::map_each(
			$rows,
			['time', 'title', 'slug', 'content', 'display' => 'author'],
			['categories' => [PageCategories::class, 'load']]
		);
	}
}