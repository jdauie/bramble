<?php

namespace Jacere\Bramble\Core\Database\Builder;

use Jacere\Bramble\Core\Database;
use Jacere\Bramble\Core\Log;
use Jacere\Skhema\TemplateGenerator;
use Spyc;

class Builder {

	public static function tables() {
		$tables = [];
		foreach (glob(BRAMBLE_DIR.'/.init/.tables/*.sql') as $path) {
			$table = Table::parse(file_get_contents($path));
			$tables[$table->name()] = $table;
		}
		return TemplateGenerator::sort($tables);
	}

	public static function build($path) {
		$init = Spyc::YAMLLoad($path);
		self::create($init);
	}

	private static function create($init) {

		// todo: remove this!
		$path = 'c:/tmp/db/bramble.sqlite3';
		if (file_exists($path)) {
			unlink($path);
		}

		$tables = self::tables();

		Log::time('load');

		foreach ($tables as $table) {
			Database::execute((string)$table);
			Log::debug("[database] CREATE TABLE {$table->name()}");
		}

		Log::time('create');

		$saved = [];

		foreach ($init as $action) {

			$sql = $action['sql'];

			$fill = isset($action['fill']) ? $action['fill'] : [];
			$load = isset($action['load']) ? $action['load'] : [];
			$expand = isset($action['expand']) ? explode('/', $action['expand'], 2) : NULL;

			$variables = self::sql_vars($sql);

			if (isset($action['data'])) {
				$rows = $action['data'];

				if (is_string($rows)) {
					// load saved by name
					$rows = $saved[$rows];
				}
				else {
					$map = isset($action['map']) ? $action['map'] : $variables;

					// create associative arrays for data
					$rows = array_map(function($a) use($map) {return array_combine($map, $a);}, $rows);
				}

				foreach ($rows as $row) {
					foreach ($fill as $variable => $method) {
						/** @var callable $method */
						$method = [self::class, "_$method"];
						$row[$variable] = $method($row);
					}
					foreach ($load as $variable => $query) {
						$params2 = self::convert_keys(array_intersect_key($row, array_fill_keys(self::sql_vars($query), true)));
						$res = Database::query($query, $params2);
						$row[$variable] = $res->single_value();
					}

					if ($expand) {
						foreach ($row[$expand[0]] as $expanded) {
							$row[$expand[1]] = $expanded;
							self::execute_row($sql, $row, $variables);
						}
					}
					else {
						self::execute_row($sql, $row, $variables);
					}
				}

				if (isset($action['save'])) {
					$saved[$action['save']] = $rows;
				}
			}
			else {
				Database::execute($sql);
			}
		}

		Log::time('fill');
	}

	private static function execute_row($sql, array $row, array $variables) {
		$params = array_intersect_key($row, array_fill_keys($variables, true));
		if (count($params) != count($variables)) {
			throw new \Exception('missing variables: ' . implode(',', array_values(array_diff($variables, array_keys($row)))));
		}
		$params = self::convert_keys($params);
		Database::execute($sql, $params);
	}

	private static function convert_keys(array $map) {
		return array_combine(array_map(function ($a) {
			return ":$a";
		}, array_keys($map)), $map);
	}

	private static function sql_vars($sql) {
		preg_match_all('/(?<!:):(\w++)\b/', $sql, $m);
		return $m[1];
	}

	public static function _get_post_content($row) {
		$name = date('Y-m-', strtotime($row['time'])).$row['slug'];
		return file_get_contents(BRAMBLE_DIR."/.init/.posts/$name.md");
	}

	public static function _get_page_content($row) {
		$name = "page_{$row['slug']}";
		return file_get_contents(BRAMBLE_DIR."/.init/.posts/$name.md");
	}
}