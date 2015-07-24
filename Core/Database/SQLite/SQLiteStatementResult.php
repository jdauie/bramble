<?php

namespace Jacere\Bramble\Core\Database\SQLite;

use Jacere\Bramble\Core\Database\IDatabase;
use Jacere\Bramble\Core\Database\StatementResult;
use \PDO;

class SQLiteStatementResult extends StatementResult {

	private $m_statement;

	/**
	 * @param IDatabase $db
	 * @param \PDOStatement $statement
	 */
	public function __construct(IDatabase $db, $statement) {
		parent::__construct($db);
		$this->m_statement = $statement;
	}

	protected function fetch_array() {
		return $this->m_statement->fetch(PDO::FETCH_ASSOC);
	}

	public function rows_affected() {
		return $this->m_statement->rowCount();
	}

	protected function get_field_metadata() {
		$field_count = $this->m_statement->columnCount();

		$types = [
			PDO::PARAM_BOOL => 'bit',
			PDO::PARAM_INT	=> 'int',
			PDO::PARAM_STR	=> 'varchar',
			PDO::PARAM_LOB	=> 'blob'
		];
		
		// add more types
		$native_types = [
			'integer' => 'int',
			'string' => 'varchar',
		];
		
		$meta = [];
		for ($i = 0; $i < $field_count; $i++) {
			try {
				$current = $this->m_statement->getColumnMeta($i);
			}
			catch (\Exception $e) {
				// very old bug fails on empty result set
				// https://bugs.php.net/bug.php?id=57001
				break;
			}

			if (isset($current['sqlite:decl_type'])) {
				$type = $current['sqlite:decl_type'];
			}
			else {
				$type = strtolower($types[$current['pdo_type']]);
				
				if (isset($native_types[$current['native_type']])) {
					$type = $native_types[$current['native_type']];
				}
			}
			
			$meta[] = [
				'name' => $current['name'],
				'type' => $type,
			];
		}
		return $meta;
	}
}
