<?php

namespace RectorPrefix20211020\TYPO3\CMS\Core\Database;

if (\class_exists('TYPO3\\CMS\\Core\\Database\\DatabaseConnection')) {
    return;
}
class DatabaseConnection
{
    /**
     * Creates and executes an INSERT SQL-statement for $table from the array with field/value pairs $fields_values.
     * Using this function specifically allows us to handle BLOB and CLOB fields depending on DB
     *
     * @param string $table Table name
     * @param array $fields_values Field values as key=>value pairs. Values will be escaped internally. Typically you would fill an array like "$insertFields" with 'fieldname'=>'value' and pass it to this function as argument.
     * @param bool|array|string $no_quote_fields See fullQuoteArray()
     *
     * @return bool|\mysqli_result|object MySQLi result object / DBAL object
     */
    public function exec_INSERTquery($table, $fields_values, $no_quote_fields = \false)
    {
        return \false;
    }
    /**
     * Creates and executes an INSERT SQL-statement for $table with multiple rows.
     *
     * @param string $table Table name
     * @param array $fields Field names
     * @param array $rows Table rows. Each row should be an array with field values mapping to $fields
     * @param bool|array|string $no_quote_fields See fullQuoteArray()
     *
     * @return bool|\mysqli_result|object MySQLi result object / DBAL object
     */
    public function exec_INSERTmultipleRows($table, $fields, $rows, $no_quote_fields = \false)
    {
        return \false;
    }
    /**
     * Creates and executes an UPDATE SQL-statement for $table where $where-clause (typ. 'uid=...') from the array with field/value pairs $fields_values.
     * Using this function specifically allow us to handle BLOB and CLOB fields depending on DB
     *
     * @param string $table Database tablename
     * @param string $where WHERE clause, eg. "uid=1". NOTICE: You must escape values in this argument with $this->fullQuoteStr() yourself!
     * @param array $fields_values Field values as key=>value pairs. Values will be escaped internally. Typically you would fill an array like "$updateFields" with 'fieldname'=>'value' and pass it to this function as argument.
     * @param bool|array|string $no_quote_fields See fullQuoteArray()
     *
     * @return bool|\mysqli_result|object MySQLi result object / DBAL object
     */
    public function exec_UPDATEquery($table, $where, $fields_values, $no_quote_fields = \false)
    {
        return \false;
    }
    /**
     * Creates and executes a DELETE SQL-statement for $table where $where-clause
     *
     * @param string $table Database tablename
     * @param string $where WHERE clause, eg. "uid=1". NOTICE: You must escape values in this argument with $this->fullQuoteStr() yourself!
     *
     * @return bool|\mysqli_result|object MySQLi result object / DBAL object
     */
    public function exec_DELETEquery($table, $where)
    {
        return \false;
    }
    /**
     * Creates and executes a SELECT SQL-statement
     * Using this function specifically allow us to handle the LIMIT feature independently of DB.
     *
     * @param string $select_fields List of fields to select from the table. This is what comes right after "SELECT ...". Required value.
     * @param string $from_table Table(s) from which to select. This is what comes right after "FROM ...". Required value.
     * @param string $where_clause Additional WHERE clauses put in the end of the query. NOTICE: You must escape values in this argument with $this->fullQuoteStr() yourself! DO NOT PUT IN GROUP BY, ORDER BY or LIMIT!
     * @param string $groupBy Optional GROUP BY field(s), if none, supply blank string.
     * @param string $orderBy Optional ORDER BY field(s), if none, supply blank string.
     * @param string $limit Optional LIMIT value ([begin,]max), if none, supply blank string.
     *
     * @return bool|\mysqli_result|object MySQLi result object / DBAL object
     */
    public function exec_SELECTquery($select_fields, $from_table, $where_clause, $groupBy = '', $orderBy = '', $limit = '')
    {
        return \false;
    }
    /**
     * Creates and executes a SELECT query, selecting fields ($select) from two/three tables joined
     * Use $mm_table together with $local_table or $foreign_table to select over two tables. Or use all three tables to select the full MM-relation.
     * The JOIN is done with [$local_table].uid <--> [$mm_table].uid_local  / [$mm_table].uid_foreign <--> [$foreign_table].uid
     * The function is very useful for selecting MM-relations between tables adhering to the MM-format used by TCE (TYPO3 Core Engine). See the section on $GLOBALS['TCA'] in Inside TYPO3 for more details.
     *
     * @param string $select Field list for SELECT
     * @param string $local_table Tablename, local table
     * @param string $mm_table Tablename, relation table
     * @param string $foreign_table Tablename, foreign table
     * @param string $whereClause Optional additional WHERE clauses put in the end of the query. NOTICE: You must escape values in this argument with $this->fullQuoteStr() yourself! DO NOT PUT IN GROUP BY, ORDER BY or LIMIT! You have to prepend 'AND ' to this parameter yourself!
     * @param string $groupBy Optional GROUP BY field(s), if none, supply blank string.
     * @param string $orderBy Optional ORDER BY field(s), if none, supply blank string.
     * @param string $limit Optional LIMIT value ([begin,]max), if none, supply blank string.
     *
     * @return bool|\mysqli_result|object MySQLi result object / DBAL object
     * @see exec_SELECTquery()
     */
    public function exec_SELECT_mm_query($select, $local_table, $mm_table, $foreign_table, $whereClause = '', $groupBy = '', $orderBy = '', $limit = '')
    {
        return \false;
    }
    /**
     * Executes a select based on input query parts array
     *
     * @param array $queryParts Query parts array
     *
     * @return bool|\mysqli_result|object MySQLi result object / DBAL object
     * @see exec_SELECTquery()
     */
    public function exec_SELECT_queryArray($queryParts)
    {
        return \false;
    }
    /**
     * Creates and executes a SELECT SQL-statement AND traverse result set and returns array with records in.
     *
     * @param string $select_fields List of fields to select from the table. This is what comes right after "SELECT ...". Required value.
     * @param string $from_table Table(s) from which to select. This is what comes right after "FROM ...". Required value.
     * @param string $where_clause Additional WHERE clauses put in the end of the query. NOTICE: You must escape values in this argument with $this->fullQuoteStr() yourself! DO NOT PUT IN GROUP BY, ORDER BY or LIMIT!
     * @param string $groupBy Optional GROUP BY field(s), if none, supply blank string.
     * @param string $orderBy Optional ORDER BY field(s), if none, supply blank string.
     * @param string $limit Optional LIMIT value ([begin,]max), if none, supply blank string.
     * @param string $uidIndexField If set, the result array will carry this field names value as index. Requires that field to be selected of course!
     *
     * @return array|NULL Array of rows, or NULL in case of SQL error
     * @see exec_SELECTquery()
     */
    public function exec_SELECTgetRows($select_fields, $from_table, $where_clause, $groupBy = '', $orderBy = '', $limit = '', $uidIndexField = '')
    {
        return null;
    }
    /**
     * Creates and executes a SELECT SQL-statement AND gets a result set and returns an array with a single record in.
     * LIMIT is automatically set to 1 and can not be overridden.
     *
     * @param string $select_fields List of fields to select from the table.
     * @param string $from_table Table(s) from which to select.
     * @param string $where_clause Optional additional WHERE clauses put in the end of the query. NOTICE: You must escape values in this argument with $this->fullQuoteStr() yourself!
     * @param string $groupBy Optional GROUP BY field(s), if none, supply blank string.
     * @param string $orderBy Optional ORDER BY field(s), if none, supply blank string.
     * @param bool $numIndex If set, the result will be fetched with sql_fetch_row, otherwise sql_fetch_assoc will be used.
     *
     * @return array|FALSE|NULL Single row, FALSE on empty result, NULL on error
     */
    public function exec_SELECTgetSingleRow($select_fields, $from_table, $where_clause, $groupBy = '', $orderBy = '', $numIndex = \false)
    {
        return null;
    }
    /**
     * Counts the number of rows in a table.
     *
     * @param string $field Name of the field to use in the COUNT() expression (e.g. '*')
     * @param string $table Name of the table to count rows for
     * @param string $where (optional) WHERE statement of the query
     *
     * @return mixed Number of rows counter (int) or FALSE if something went wrong (bool)
     */
    public function exec_SELECTcountRows($field, $table, $where = '1=1')
    {
        return \false;
    }
    /**
     * Truncates a table.
     *
     * @param string $table Database tablename
     *
     * @return mixed Result from handler
     */
    public function exec_TRUNCATEquery($table)
    {
        return \false;
    }
    /**************************************
     *
     * Query building
     *
     **************************************/
    /**
     * Creates an INSERT SQL-statement for $table from the array with field/value pairs $fields_values.
     *
     * @param string $table See exec_INSERTquery()
     * @param array $fields_values See exec_INSERTquery()
     * @param bool|array|string $no_quote_fields See fullQuoteArray()
     *
     * @return string|NULL Full SQL query for INSERT, NULL if $fields_values is empty
     */
    public function INSERTquery($table, $fields_values, $no_quote_fields = \false)
    {
        return null;
    }
    /**
     * Creates an INSERT SQL-statement for $table with multiple rows.
     *
     * @param string $table Table name
     * @param array $fields Field names
     * @param array $rows Table rows. Each row should be an array with field values mapping to $fields
     * @param bool|array|string $no_quote_fields See fullQuoteArray()
     *
     * @return string|NULL Full SQL query for INSERT, NULL if $rows is empty
     */
    public function INSERTmultipleRows($table, $fields, $rows, $no_quote_fields = \false)
    {
        return null;
    }
    /**
     * Creates an UPDATE SQL-statement for $table where $where-clause (typ. 'uid=...') from the array with field/value pairs $fields_values.
     *
     *
     * @param string $table See exec_UPDATEquery()
     * @param string $where See exec_UPDATEquery()
     * @param array $fields_values See exec_UPDATEquery()
     * @param bool|array|string $no_quote_fields See fullQuoteArray()
     *
     * @return string Full SQL query for UPDATE
     */
    public function UPDATEquery($table, $where, $fields_values, $no_quote_fields = \false)
    {
        return '';
    }
    /**
     * Creates a DELETE SQL-statement for $table where $where-clause
     *
     * @param string $table See exec_DELETEquery()
     * @param string $where See exec_DELETEquery()
     *
     * @return string Full SQL query for DELETE
     */
    public function DELETEquery($table, $where)
    {
        return '';
    }
    /**
     * Creates a SELECT SQL-statement
     *
     * @param string $select_fields See exec_SELECTquery()
     * @param string $from_table See exec_SELECTquery()
     * @param string $where_clause See exec_SELECTquery()
     * @param string $groupBy See exec_SELECTquery()
     * @param string $orderBy See exec_SELECTquery()
     * @param string $limit See exec_SELECTquery()
     *
     * @return string Full SQL query for SELECT
     */
    public function SELECTquery($select_fields, $from_table, $where_clause, $groupBy = '', $orderBy = '', $limit = '')
    {
        return '';
    }
    /**
     * Creates a SELECT SQL-statement to be used as subquery within another query.
     * BEWARE: This method should not be overridden within DBAL to prevent quoting from happening.
     *
     * @param string $select_fields List of fields to select from the table.
     * @param string $from_table Table from which to select.
     * @param string $where_clause Conditional WHERE statement
     *
     * @return string Full SQL query for SELECT
     */
    public function SELECTsubquery($select_fields, $from_table, $where_clause)
    {
        return '';
    }
    /**
     * Creates a SELECT query, selecting fields ($select) from two/three tables joined
     * Use $mm_table together with $local_table or $foreign_table to select over two tables. Or use all three tables to select the full MM-relation.
     * The JOIN is done with [$local_table].uid <--> [$mm_table].uid_local  / [$mm_table].uid_foreign <--> [$foreign_table].uid
     * The function is very useful for selecting MM-relations between tables adhering to the MM-format used by TCE (TYPO3 Core Engine). See the section on $GLOBALS['TCA'] in Inside TYPO3 for more details.
     *
     * @param string $select See exec_SELECT_mm_query()
     * @param string $local_table See exec_SELECT_mm_query()
     * @param string $mm_table See exec_SELECT_mm_query()
     * @param string $foreign_table See exec_SELECT_mm_query()
     * @param string $whereClause See exec_SELECT_mm_query()
     * @param string $groupBy See exec_SELECT_mm_query()
     * @param string $orderBy See exec_SELECT_mm_query()
     * @param string $limit See exec_SELECT_mm_query()
     *
     * @return string Full SQL query for SELECT
     * @see SELECTquery()
     */
    public function SELECT_mm_query($select, $local_table, $mm_table, $foreign_table, $whereClause = '', $groupBy = '', $orderBy = '', $limit = '')
    {
        return '';
    }
    /**
     * Creates a TRUNCATE TABLE SQL-statement
     *
     * @param string $table See exec_TRUNCATEquery()
     *
     * @return string Full SQL query for TRUNCATE TABLE
     */
    public function TRUNCATEquery($table)
    {
        return '';
    }
    /**
     * Returns a WHERE clause that can find a value ($value) in a list field ($field)
     * For instance a record in the database might contain a list of numbers,
     * "34,234,5" (with no spaces between). This query would be able to select that
     * record based on the value "34", "234" or "5" regardless of their position in
     * the list (left, middle or right).
     * The value must not contain a comma (,)
     * Is nice to look up list-relations to records or files in TYPO3 database tables.
     *
     * @param string $field Field name
     * @param string $value Value to find in list
     * @param string $table Table in which we are searching (for DBAL detection of quoteStr() method)
     *
     * @return string WHERE clause for a query
     */
    public function listQuery($field, $value, $table)
    {
        return '';
    }
    /**
     * Returns a WHERE clause which will make an AND or OR search for the words in the $searchWords array in any of the fields in array $fields.
     *
     * @param array $searchWords Array of search words
     * @param array $fields Array of fields
     * @param string $table Table in which we are searching (for DBAL detection of quoteStr() method)
     * @param string $constraint How multiple search words have to match ('AND' or 'OR')
     *
     * @return string WHERE clause for search
     */
    public function searchQuery($searchWords, $fields, $table, $constraint = self::AND_Constraint)
    {
        return '';
    }
    /**************************************
     *
     * Prepared Query Support
     *
     **************************************/
    /**
     * Creates a SELECT prepared SQL statement.
     *
     * @param string $select_fields See exec_SELECTquery()
     * @param string $from_table See exec_SELECTquery()
     * @param string $where_clause See exec_SELECTquery()
     * @param string $groupBy See exec_SELECTquery()
     * @param string $orderBy See exec_SELECTquery()
     * @param string $limit See exec_SELECTquery()
     * @param array $input_parameters An array of values with as many elements as there are bound parameters in the SQL statement being executed. All values are treated as \TYPO3\CMS\Typo3DbLegacy\Database\PreparedStatement::PARAM_AUTOTYPE.
     *
     * @return PreparedStatement Prepared statement
     */
    public function prepare_SELECTquery($select_fields, $from_table, $where_clause, $groupBy = '', $orderBy = '', $limit = '', $input_parameters = [])
    {
        return new \RectorPrefix20211020\TYPO3\CMS\Core\Database\PreparedStatement();
    }
    /**
     * Creates a SELECT prepared SQL statement based on input query parts array
     *
     * @param array $queryParts Query parts array
     * @param array $input_parameters An array of values with as many elements as there are bound parameters in the SQL statement being executed. All values are treated as \TYPO3\CMS\Typo3DbLegacy\Database\PreparedStatement::PARAM_AUTOTYPE.
     *
     * @return PreparedStatement Prepared statement
     */
    public function prepare_SELECTqueryArray($queryParts, $input_parameters = [])
    {
        return new \RectorPrefix20211020\TYPO3\CMS\Core\Database\PreparedStatement();
    }
    /**************************************
     *
     * Various helper functions
     *
     * Functions recommended to be used for
     * - escaping values,
     * - cleaning lists of values,
     * - stripping of excess ORDER BY/GROUP BY keywords
     *
     **************************************/
    /**
     * Escaping and quoting values for SQL statements.
     *
     * @param string $str Input string
     * @param string $table Table name for which to quote string. Just enter the table that the field-value is selected from (and any DBAL will look up which handler to use and then how to quote the string!).
     * @param bool $allowNull Whether to allow NULL values
     *
     * @return string Output string; Wrapped in single quotes and quotes in the string (" / ') and \ will be backslashed (or otherwise based on DBAL handler)
     * @see quoteStr()
     */
    public function fullQuoteStr($str, $table, $allowNull = \false)
    {
        return '';
    }
    /**
     * Will fullquote all values in the one-dimensional array so they are ready to "implode" for an sql query.
     *
     * @param array $arr Array with values (either associative or non-associative array)
     * @param string $table Table name for which to quote
     * @param bool|array|string $noQuote List/array of keys NOT to quote (eg. SQL functions) - ONLY for associative arrays
     * @param bool $allowNull Whether to allow NULL values
     *
     * @return array The input array with the values quoted
     * @see cleanIntArray()
     */
    public function fullQuoteArray($arr, $table, $noQuote = \false, $allowNull = \false)
    {
        return [];
    }
    /**
     * Substitution for PHP function "addslashes()"
     * Use this function instead of the PHP addslashes() function when you build queries - this will prepare your code for DBAL.
     * NOTICE: You must wrap the output of this function in SINGLE QUOTES to be DBAL compatible. Unless you have to apply the single quotes yourself you should rather use ->fullQuoteStr()!
     *
     * @param string $str Input string
     * @param string $table Table name for which to quote string. Just enter the table that the field-value is selected from (and any DBAL will look up which handler to use and then how to quote the string!).
     *
     * @return string Output string; Quotes (" / ') and \ will be backslashed (or otherwise based on DBAL handler)
     * @see quoteStr()
     */
    public function quoteStr($str, $table)
    {
        return '';
    }
    /**
     * Escaping values for SQL LIKE statements.
     *
     * @param string $str Input string
     * @param string $table Table name for which to escape string. Just enter the table that the field-value is selected from (and any DBAL will look up which handler to use and then how to quote the string!).
     *
     * @return string Output string; % and _ will be escaped with \ (or otherwise based on DBAL handler)
     * @see quoteStr()
     */
    public function escapeStrForLike($str, $table)
    {
        return '';
    }
    /**
     * Will convert all values in the one-dimensional array to integers.
     * Useful when you want to make sure an array contains only integers before imploding them in a select-list.
     *
     * @param array $arr Array with values
     *
     * @return array The input array with all values cast to (int)
     * @see cleanIntList()
     */
    public function cleanIntArray($arr)
    {
        return [];
    }
    /**
     * Will force all entries in the input comma list to integers
     * Useful when you want to make sure a commalist of supposed integers really contain only integers; You want to know that when you don't trust content that could go into an SQL statement.
     *
     * @param string $list List of comma-separated values which should be integers
     *
     * @return string The input list but with every value cast to (int)
     * @see cleanIntArray()
     */
    public function cleanIntList($list)
    {
        return '';
    }
    /**
     * Removes the prefix "ORDER BY" from the input string.
     * This function is used when you call the exec_SELECTquery() function and want to pass the ORDER BY parameter by can't guarantee that "ORDER BY" is not prefixed.
     * Generally; This function provides a work-around to the situation where you cannot pass only the fields by which to order the result.
     *
     * @param string $str eg. "ORDER BY title, uid
     *
     * @return string eg. "title, uid
     * @see exec_SELECTquery(), stripGroupBy()
     */
    public function stripOrderBy($str)
    {
        return '';
    }
    /**
     * Removes the prefix "GROUP BY" from the input string.
     * This function is used when you call the SELECTquery() function and want to pass the GROUP BY parameter by can't guarantee that "GROUP BY" is not prefixed.
     * Generally; This function provides a work-around to the situation where you cannot pass only the fields by which to order the result.
     *
     * @param string $str eg. "GROUP BY title, uid
     *
     * @return string eg. "title, uid
     * @see exec_SELECTquery(), stripOrderBy()
     */
    public function stripGroupBy($str)
    {
        return '';
    }
    /**
     * Returns the date and time formats compatible with the given database table.
     *
     * @param string $table Table name for which to return an empty date. Just enter the table that the field-value is selected from (and any DBAL will look up which handler to use and then how date and time should be formatted).
     *
     * @return array
     */
    public function getDateTimeFormats($table)
    {
        return [];
    }
    /**************************************
     *
     * MySQL(i) wrapper functions
     * (For use in your applications)
     *
     **************************************/
    /**
     * Executes query
     * MySQLi query() wrapper function
     * Beware: Use of this method should be avoided as it is experimentally supported by DBAL. You should consider
     * using exec_SELECTquery() and similar methods instead.
     *
     * @param string $query Query to execute
     *
     * @return bool|\mysqli_result|object MySQLi result object / DBAL object
     */
    public function sql_query($query)
    {
        return \false;
    }
    /**
     * Returns the error status on the last query() execution
     *
     * @return string MySQLi error string.
     */
    public function sql_error()
    {
        return '';
    }
    /**
     * Returns the error number on the last query() execution
     *
     * @return int MySQLi error number
     */
    public function sql_errno()
    {
        return 0;
    }
    /**
     * Returns the number of selected rows.
     *
     * @param bool|\mysqli_result|object $res MySQLi result object / DBAL object
     *
     * @return int Number of resulting rows
     */
    public function sql_num_rows($res)
    {
        return 0;
    }
    /**
     * Returns an associative array that corresponds to the fetched row, or FALSE if there are no more rows.
     * MySQLi fetch_assoc() wrapper function
     *
     * @param bool|\mysqli_result|object $res MySQLi result object / DBAL object
     *
     * @return array|bool Associative array of result row.
     */
    public function sql_fetch_assoc($res)
    {
        return [];
    }
    /**
     * Returns an array that corresponds to the fetched row, or FALSE if there are no more rows.
     * The array contains the values in numerical indices.
     * MySQLi fetch_row() wrapper function
     *
     * @param bool|\mysqli_result|object $res MySQLi result object / DBAL object
     *
     * @return array|bool Array with result rows.
     */
    public function sql_fetch_row($res)
    {
        return \false;
    }
    /**
     * Free result memory
     * free_result() wrapper function
     *
     * @param bool|\mysqli_result|object $res MySQLi result object / DBAL object
     *
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function sql_free_result($res)
    {
        return \false;
    }
    /**
     * Get the ID generated from the previous INSERT operation
     *
     * @return int The uid of the last inserted record.
     */
    public function sql_insert_id()
    {
        return 0;
    }
    /**
     * Returns the number of rows affected by the last INSERT, UPDATE or DELETE query
     *
     * @return int Number of rows affected by last query
     */
    public function sql_affected_rows()
    {
        return 0;
    }
    /**
     * Move internal result pointer
     *
     * @param bool|\mysqli_result|object $res MySQLi result object / DBAL object
     * @param int $seek Seek result number.
     *
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function sql_data_seek($res, $seek)
    {
        return \false;
    }
    /**
     * Get the type of the specified field in a result
     * mysql_field_type() wrapper function
     *
     * @param bool|\mysqli_result|object $res MySQLi result object / DBAL object
     * @param int $pointer Field index.
     *
     * @return string Returns the name of the specified field index, or FALSE on error
     */
    public function sql_field_type($res, $pointer)
    {
        return '';
    }
    /**
     * Open a (persistent) connection to a MySQL server
     *
     * @return bool|void
     */
    public function sql_pconnect()
    {
        return \false;
    }
    /**
     * Select a SQL database
     *
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function sql_select_db()
    {
        return \false;
    }
    /**************************************
     *
     * SQL admin functions
     * (For use in the Install Tool and Extension Manager)
     *
     **************************************/
    /**
     * Listing databases from current MySQL connection. NOTICE: It WILL try to select those databases and thus break selection of current database.
     * This is only used as a service function in the (1-2-3 process) of the Install Tool.
     * In any case a lookup should be done in the _DEFAULT handler DBMS then.
     * Use in Install Tool only!
     *
     * @return array Each entry represents a database name
     */
    public function admin_get_dbs()
    {
        return [];
    }
    /**
     * Returns the list of tables from the default database, TYPO3_db (quering the DBMS)
     * In a DBAL this method should 1) look up all tables from the DBMS  of
     * the _DEFAULT handler and then 2) add all tables *configured* to be managed by other handlers
     *
     * @return array Array with tablenames as key and arrays with status information as value
     */
    public function admin_get_tables()
    {
        return [];
    }
    /**
     * Returns information about each field in the $table (quering the DBMS)
     * In a DBAL this should look up the right handler for the table and return compatible information
     * This function is important not only for the Install Tool but probably for
     * DBALs as well since they might need to look up table specific information
     * in order to construct correct queries. In such cases this information should
     * probably be cached for quick delivery.
     *
     * @param string $tableName Table name
     *
     * @return array Field information in an associative array with fieldname => field row
     */
    public function admin_get_fields($tableName)
    {
        return [];
    }
    /**
     * Returns information about each index key in the $table (quering the DBMS)
     * In a DBAL this should look up the right handler for the table and return compatible information
     *
     * @param string $tableName Table name
     *
     * @return array Key information in a numeric array
     */
    public function admin_get_keys($tableName)
    {
        return [];
    }
    /**
     * Returns information about the character sets supported by the current DBM
     * This function is important not only for the Install Tool but probably for
     * DBALs as well since they might need to look up table specific information
     * in order to construct correct queries. In such cases this information should
     * probably be cached for quick delivery.
     *
     * This is used by the Install Tool to convert tables with non-UTF8 charsets
     * Use in Install Tool only!
     *
     * @return array Array with Charset as key and an array of "Charset", "Description", "Default collation", "Maxlen" as values
     */
    public function admin_get_charsets()
    {
        return [];
    }
    /**
     * mysqli() wrapper function, used by the Install Tool and EM for all queries regarding management of the database!
     *
     * @param string $query Query to execute
     *
     * @return bool|\mysqli_result|object MySQLi result object / DBAL object
     */
    public function admin_query($query)
    {
        return \false;
    }
    /******************************
     *
     * Debugging
     *
     ******************************/
    /**
     * Debug function: Outputs error if any
     *
     * @param string $func Function calling debug()
     * @param string $query Last query if not last built query
     */
    public function debug($func, $query = '')
    {
    }
    /**
     * Checks if record set is valid and writes debugging information into devLog if not.
     *
     * @param bool|\mysqli_result|object MySQLi result object / DBAL object
     *
     * @return bool TRUE if the  record set is valid, FALSE otherwise
     */
    public function debug_check_recordset($res)
    {
        return \false;
    }
}
