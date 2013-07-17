<?php
namespace DB;

/**
 * Local extension of the fatfree framework SQL class PDO extended class
 *
 * @see vendor/fatfree/lib/db/sql.php
 * @see http://www.php.net/manual/en/class.pdo.php
 * @author Vijay Mahrra <vijay.mahrra@gmail.com>
 */

//! PDO wrapper adding new methods to \DB\SQL
class CustomSQL extends \DB\SQL {

	private
		//! Database engine
		$engine,
		//! Database name
		$dbname,
		//! Transaction flag
		$trans=FALSE,
		//! Number of rows affected by query
		$rows=0,
		//! SQL log
		$log;

	/**
	 * Execute a single read SQL statement like exec and
     * return the PDOStatement instance row pointer/cursor on a result set
     *
	 * @return PDOStatement|FALSE
	 * @param $cmd string
	 * @param $arg optional string|array
	 * @param $log bool
     *
     * @see vendor/fatfree/lib/db/sql.php
     * @see http://www.php.net/manual/en/class.pdo.php
     * @author Vijay Mahrra <vijay.mahrra@gmail.com>
     **/
	function get_cursor($cmd, $arg=NULL, $log=TRUE) {
		if (!is_string($cmd)) {
            return false;
		} elseif (!preg_match(
            '/\b(?:CALL|EXPLAIN|SELECT|PRAGMA|SHOW)\b/i',$cmd)) {
            return false;
        }
		if (empty($arg))
			$arg=array();
		elseif (is_scalar($arg))
			$arg=array(1=>$arg);

		$fw=\Base::instance();
        $now=microtime(TRUE);
        $keys=$vals=array();

        if (is_object($query=$this->prepare($cmd))) {
            foreach ($arg as $key=>$val) {
                if (is_array($val)) {
                    // User-specified data type
                    $query->bindvalue($key,$val[0],$val[1]);
                    $vals[]=$fw->stringify($val[0]);
                }
                else {
                    // Convert to PDO data type
                    $query->bindvalue($key,$val,$this->type($val));
                    $vals[]=$fw->stringify($val);
                }
                $keys[]='/'.(is_numeric($key)?'\?':preg_quote($key)).'/';
            }
            $query->execute();
            $error=$query->errorinfo();
            if ($error[0]!=\PDO::ERR_NONE) {
                // Statement-level error occurred
                if ($this->trans)
                    $this->rollback();
                user_error('PDOStatement: '.$error[2]);
            }

            $this->rows = $query->rowcount();
        }
        else {
            $error=$this->errorinfo();
            if ($error[0]!=\PDO::ERR_NONE) {
                // PDO-level error occurred
                if ($this->trans)
                    $this->rollback();
                user_error('PDO: '.$error[2]);
            }
        }
        if ($log)
            $this->log.=date('r').' ('.
                sprintf('%.1f',1e3*(microtime(TRUE)-$now)).'ms) '.
                preg_replace($keys,$vals,$cmd,1).PHP_EOL;

        return $query;
        // process the result above with either of:
        // while ($row = $stmt->fetch(\CustomSQL::FETCH_ASSOC))
        // foreach ($query as $index => $row) {
	}

	/**
	 * Process PDO Result Set into an array optionally using specified fields
     * If there is just one column it will return a hash array of key_field => column
	 * @return array|FALSE
     * @param $query PDOStatement
	 * @param optional string|array $columns fields from result set to use in results array
	 * @param optional string $key_field field from result to use as array key index OR empty to use resultset index
     * @author Vijay Mahrra <vijay.mahrra@gmail.com>
	 **/
    function process_cursor($query, $columns = NULL, $key_field = NULL) {
        // process the query results if columns were specified
        $rows = array();

        if (is_string($columns))
            $columns = array($columns);

        foreach ($query as $k => $row) {
            $data = array();
            // no columns specified, use everything for row data
            if (empty($columns)) {
                $data = $row;
            } else if (count($columns) == 1) {
                $data = $row[$columns[0]];
            } else {
                // only use specified columns
                foreach ($columns as $i => $c) {
                    $data[$c] = $row[$c];
                }
            }
            // use row index field if index not specified
            if (empty($key_field)) {
                $rows[$k] = $data;
            } else {
                $rows[$row[$key_field]] = $data;
            }
        }

		return empty($rows) ? false : $rows;
    }

}