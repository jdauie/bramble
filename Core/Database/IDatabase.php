<?php

namespace Jacere\Bramble\Core\Database;

/**
 * Interface for database implementations
 */
interface IDatabase {

	/**
	 * @return string
	 */
	public function name();

	/**
	 * Get the converter to call for the specified field type, or NULL if no corresponding converter is found.
	 * @param $field_type
	 * @return callable
	 */
	public function converter($field_type);

	/**
	 * Retrieve query results using optional arguments to fill in values in the prepared SQL statement.  If there is a
	 * single optional array argument, then the values in the array will become the optional argument list.
	 * @param string $sql
	 * @return StatementResult
	 */
	public function query($sql);

	/**
	 * Execute query using optional arguments to fill in values in the prepared SQL statement.  If there is a single
	 * optional array argument, then the values in the array will become the optional argument list.
	 * @param string $sql
	 * @return int Number of rows affected
	 */
	public function execute($sql);

	/**
	 * @return bool
	 */
	public function begin_transaction();

	/**
	 * @return bool
	 */
	public function commit();

	/**
	 * @return bool
	 */
	public function rollback();

	/**
	 * Create a new database.
	 * @param $database
	 * @param $force
	 * @return bool
	 */
	public function create($database, $force);
}
