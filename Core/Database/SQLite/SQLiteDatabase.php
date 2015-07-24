<?php

namespace Jacere\Bramble\Core\Database\SQLite;

use Jacere\Bramble\Core\Database\Database;
use Jacere\Bramble\Core\Exception\RuntimeInfoException;
use \PDO;

/**
 * SQLite PDO database class
 */
class SQLiteDatabase extends Database {

	/** @var PDO */
	private $m_pdo;

	protected function connect(\stdClass $options) {
		$dsn = "sqlite:{$options->database}";
		
		$this->m_pdo = new PDO($dsn);
		$this->m_pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$this->m_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->m_pdo->exec('PRAGMA synchronous=OFF');
	}
	
	protected function conversions() {
		return [
			'bool'  => ['bit'],
			'int'   => ['bigint', 'integer', 'int', 'mediumint', 'smallint', 'tinyint'],
			'float' => ['float', 'real'],
		];
	}

	public function name() {
		return $this->option('database');
	}

	protected function query_result($sql, $args) {

		$sql = self::convert_query_sql($sql);
		
		try {
			$sth = $this->m_pdo->prepare($sql, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);

			// I still haven't found a way to insert TRUE into BIT fields
			// "String data, right truncated: 1406 Data too long for column"
			foreach ($args as &$arg) {
				if (is_bool($arg)) {
					$arg = (int)$arg;
				}
			}
			
			$sth->execute($args);
		}
		catch(\Exception $e) {
			throw new RuntimeInfoException($e->getMessage(), ['sql' => $sql, 'args' => $args]);
		}

		return new SQLiteStatementResult($this, $sth);
	}

	public function begin_transaction() {
		return $this->m_pdo->beginTransaction();
	}

	public function commit() {
		$this->m_pdo->commit();
	}

	public function rollback() {
		$this->m_pdo->rollBack();
	}

	public function create($database, $force) {
		if ($force) {
			if ($path = realpath($database)) {
				unlink($path);
			}
		}
		return true;
	}
	
	public static function convert_query_sql($sql) {
		// replace [field] with `field`
		//$sql = preg_replace('/\[([a-z]\w+)\]/i', '"$1"', $sql);

		// handle paging (limited support)
		//$pattern = str_replace(' ', '\s+', 'OFFSET (\?|\d+) ROWS FETCH NEXT (\?|\d+) ROWS ONLY');
		//$sql = preg_replace("/$pattern/i", 'LIMIT $1, $2', $sql);

		return $sql;
	}
}
