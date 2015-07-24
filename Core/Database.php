<?php

namespace Jacere\Bramble\Core;

use Exception;
use Jacere\Bramble\Core\Database\IDatabase;
use Jacere\Bramble\Core\Database\SQLite\SQLiteDatabase;
use Jacere\Bramble\Core\Database\StatementResult;

// todo: driver creates connection (wrapper around e.g. pdo_sqlite3), adapter

class Database {

	private static $c_databases;

	private static $c_adapters = [
		'sqlite' => SQLiteDatabase::class,
	];

	/**
	 * Connection factory
	 * @param \stdClass $options
	 * @return IDatabase
	 */
	public static function connect(\stdClass $options) {

		if (!self::$c_databases) {
			self::$c_databases = [];
		}
		
		$key = json_encode($options);

		if (!isset(self::$c_databases[$key])) {

			$adapter = $options->adapter;
			$class = isset(self::$c_adapters[$adapter]) ? self::$c_adapters[$adapter] : $adapter;

			//try {
				self::$c_databases[$key] = new $class($options);
			//} catch(\Exception $e) {
				//throw ServiceException::code(ServiceException::E_DB_CONNECT, NULL, $e);
			//}
		}

		Log::debug(sprintf('[database] %s(%s)', $options->adapter, $options->database));

		return self::$c_databases[$key];
	}

	/**
	 * Create a new database.
	 * @param \stdClass $options
	 * @param bool $force
	 * @return IDatabase
	 * @throws ServiceException
	 */
	public static function create(\stdClass $options, $force = false) {
		$database = $options->database;

		$connection = clone $options;
		$connection->database = NULL;

		$db = Database::connect($connection);
		$db->create($database, $force);

		return Database::connect($options);
	}

	/**
	 * @return IDatabase
	 * @throws ServiceException
	 */
	private static function core() {

		$options = (object)[
			'adapter' => 'sqlite',
			'database' => 'c:/tmp/db/bramble.sqlite3',
		];
		$key = json_encode($options);
		
		if (!isset(self::$c_databases[$key])) {
			return self::connect($options);
		}

		return self::$c_databases[$key];
	}

	/**
	 * Retrieve query results using optional arguments to fill in values in the prepared SQL statement.  If there is a
	 * single optional array argument, then the values in the array will become the optional argument list.
	 * @param string $sql
	 * @return StatementResult
	 */
	public static function query($sql) {
		return call_user_func_array([self::core(), 'query'], func_get_args());
	}

	/**
	 * Execute query using optional arguments to fill in values in the prepared SQL statement.  If there is a single
	 * optional array argument, then the values in the array will become the optional argument list.
	 * @param string $sql
	 * @return int Number of rows affected
	 */
	public static function execute($sql) {
		return call_user_func_array([self::core(), 'execute'], func_get_args());
	}

	/**
	 * @return bool
	 */
	public static function begin_transaction() {
		return self::core()->begin_transaction();
	}

	/**
	 * @return bool
	 */
	public static function commit() {
		return self::core()->commit();
	}

	/**
	 * @return bool
	 */
	public static function rollback() {
		return self::core()->rollback();
	}

	/**
	 * Get a new UUID
	 * @return string
	 */
	public static function new_id() {
		return self::new_v4_guid();
	}

	public static function new_v4_guid() {
		return strtoupper(sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

			// 32 bits for "time_low"
			mt_rand(0, 0xffff), mt_rand(0, 0xffff),

			// 16 bits for "time_mid"
			mt_rand(0, 0xffff),

			// 16 bits for "time_hi_and_version",
			// four most significant bits holds version number 4
			mt_rand(0, 0x0fff) | 0x4000,

			// 16 bits, 8 bits for "clk_seq_hi_res",
			// 8 bits for "clk_seq_low",
			// two most significant bits holds zero and one for variant DCE1.1
			mt_rand(0, 0x3fff) | 0x8000,

			// 48 bits for "node"
			mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
		));
	}
}
