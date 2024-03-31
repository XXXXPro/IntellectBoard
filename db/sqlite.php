<?php

/**
*  SQLite Database library for Intellect Board Platform
*  @package IntBoard
*  @author 4X_Pro <me@4xpro.ru>
*  @version 1.0
*  @copyright 2017, 4X_Pro
*  @url http://www.intbpro.ru
**/

/**  Class for dealing with SQLite database.
*    @package Database
*    @abstract
     **/

class Database_sqlite extends Database implements iDBDriver {
  private $link;
  private $errmsg;
  private $last_query;

/** Open a connection to a database file.
*  @return boolean
*  @param mixed $filename May be name of include-file or hash array with parameters of connection.
*  Parameters of connection are:<ul>
*  <li>DBname -- name of database file. Must be located in db/ subdirectory;
*  </ul>
*  In case of hash the parameters should be it's keys,
*  in case of include file the params must be defined as variables in the file.
**/
  function connect($params) {
    $this->link = new SQLite3(BASEDIR.'db/'.$params['DB_name'].'.db');
    if (!$this->link) trigger_error('Ошибка подключения к базе данных! '.$this->error_str(),E_USER_ERROR);
    $this->link->createFunction('CONCAT',array($this,'concat'),-1,SQLITE3_DETERMINISTIC);
    $this->link->createFunction('VERSION',array($this,'version'));
  }
  
  function concat() {
    return join('',func_get_args());
  }

  function version() {
    return "SQLite ".SQLite3::version();
  }
  

/** Closes a connection to database server, opened with {@see function connect()}
*  @return boolean
**/
  function close() {
    if ($this->link) $this->link->close();
    $this->link=NULL;
  }

/** Defines the character set for connection to database server.
*  @param string $charset Character set for
*  @return boolean
**/
  function set_charset($charset) {
  }

/** Gets database server software version
*   @return string Version number of RDBMS.
**/
  function get_version() {
    $tmp=$this->link->version();
    return $tmp['versionString'];
  }

/** Adds RDBMS-specific LIMIT clause for SQL operator.
*   @return string Modified SQL operator
*   @param string $sql SQL operator
*   @param $limit1 The first parameter of LIMIT
*   @param $limit2 The second parameter of LIMIT
**/
  function add_limiter($sql,$limit1=false,$limit2=false) {
    if ($limit1!==false && $limit2===false) {
      if (!preg_match('|LIMIT\s+\d+\s*$|is',$sql)) $sql.=' LIMIT '.$limit1;
    }
    elseif ($limit1!==false && $limit2!==false) {
      if (!preg_match('|LIMIT\s+\d+,\d+\s*$|is',$sql)) $sql.=' LIMIT '.$limit2.' OFFSET '.$limit1;
    }
    return $sql;
  }

/** Really executes query of any type except SELECT.
*  @return mixed FALSE if there were errors, number of affected rows -- if successful
*  @param string $sql SQL query to execute.
*  @param array|bool $params Parameters to substitute instead of ? symbols in query
**/
  function _query($sql,$params=false) {
    if (!empty($params)) {
      foreach ($params as $param) $sql = preg_replace('/\?/','\''.$this->slashes($param).'\'',$sql,1); 
    }
    return $this->link->query($sql);
  }

/** Return error code from last query.
*  @param only_errors TRUE if warnings and notices should not be returned
*  @return intger;
**/
  function error_num($only_errors=false) {
    return $this->link->lastErrorCode();
  }

/** Return error message from last query.
*  @return string;
**/
  function error_str() {
    return $this->link->lastErrorMsg();
  }

  function explain($sql) {
/*    if (preg_match('/^\s*SELECT/is',$sql)) {
      $sql2 = "EXPLAIN $sql";
      $res2 = mysql_query($sql2,$this->link);
      $buffer='<table style="width: 100%" style="border: #888 1px solid"><tr>';
      for ($i=0; $i<mysql_num_fields($res2); $i++) $buffer.='<td><b>'.mysql_field_name($res2,$i).'</b>';
      while ($row=mysql_fetch_row($res2)) {
        $buffer.='<tr>';
        foreach ($row as $column) $buffer.='<td>'.$column.'</td>';
        $buffer.='</tr>';
      }
      $buffer.='</table>';
      mysql_free_result($res2);
    }
    else $buffer='';
    return $buffer;*/
    _dbg('Explain is not implemented');
  }

/** Fetches associative array from query result.
*    @param SQLite3Result $res Query result returned by query.
*    @return array Hash of fields from current row.
**/
  function fetch_array(&$res) {
    return $res->fetchArray(SQLITE3_ASSOC);
  }

/** Fetches array from query result.
*    @param SQLite3Result $res Query result returned by query.
*    @return array Array of fields from current row.
**/
  function fetch_row(&$res) {
    return $res->fetchArray(SQLITE3_NUM);
  }

/** Frees the query result.
*    @param SQLite3Result $res Query result returned by query.
**/
  function free_res(&$res) {
    $res->finalize();
  }

/** Converts INSERT operator into INSERT IGNORE. Database-specific, should be overriden in descendants.
*  Called from store when $ignore is TRUE.
*  @return string SQL with INSERT IGNORE
*  @param string $data
**/
  function insert_ignore($table,$data) {
    $sqlarray1=array();
    $sqlarray2=array();
    foreach ($data as $key=>$value) {
        $sqlarray1[]='`'.$key.'`';
        $sqlarray2[]='\''.addslashes($value).'\'';
    }
    if (count($sqlarray1)) {
      $sql = "INSERT OR IGNORE INTO $table (".join(', ',$sqlarray1).') VALUES  ('.join(', ',$sqlarray2).')';
      $this->query($sql);
      return $this->insert_id();
    }
    else trigger_error('Нет данных для записи!',E_USER_ERROR);
  }

/** Выполнение запроса REPLACE **/
  function replace($table,$data,$condition) {
    $sqlarray1=array();
    $sqlarray2=array();
    foreach ($data as $key=>$value) {
        $sqlarray1[]='`'.$key.'`';
        $sqlarray2[]='\''.addslashes($value).'\'';
    }
    if (count($sqlarray1)) {
      $sql = "INSERT OR REPLACE INTO $table (".join(', ',$sqlarray1).') VALUES  ('.join(', ',$sqlarray2).')';
      $this->query($sql);
      return $this->insert_id();
    }
    else trigger_error('Нет данных для записи!',E_USER_ERROR);
  }


/** Return number of rows in last successful SELECT query.
* @return integer The number of rows.
**/
  function _num_rows(&$res) {
    trigger_error('Num_rows function is not implemented in SQLite!',E_USER_ERROR);
    //return 1;
  }

/** Return number of rows affected last query (except INSERT).
* @return integer The number of rows.
**/
  function affected_rows() {
    return $this->link->changes();
  }

/** Return auto-incement value of the first row, inserted by last INSERT
* @return integer The last auto-increment value.
**/
  function insert_id() {
    return $this->link->lastInsertRowID();
  }

/** Locks tables for write or for read
* @return boolean TRUE if successful
* @param mixed $tables String with single table name or array of table names to lock.
* @param integer $mode Mode for locking. May be one of this values: <ul>
* <li>DB_LOCK_READ -- locking for reading
* <li>DB_LOCK_WRITE -- locking for writing</ul>
**/
  function lock_tables($tables,$mode) {
  }

/** Unlocks previously locked tables
* @return boolean TRUE if successful
* @param mixed $tables String with single table name or array of table names to repair.
**/
  function unlock_tables($tables) {
  }

  /** Получение количественного показателя релевантности при полнотекстовом поиске **/
  function full_relevancy($column,$text) {
    return $column.' MATCH "'.$this->slashes($text).'" ';
  }

  /** Проверка наличия текста при полнетесктовом поиске **/
  function full_match($column,$text) {
    return $column.' MATCH "'.$this->slashes($text).'" ';
  }


/** Transaction start for RBDMS with transactional support
* @return boolean TRUE if successful
**/
  function begin() {}

/** Transaction commit for RBDMS with transactional support
* @return boolean TRUE if successful
**/
  function commit() {}

/** Transaction abort for RBDMS with transactional support
* @return boolean TRUE if successful
**/
  function rollback() {}
/** Checks whether RDBMS supports stored procedures
* @return boolean
**/
  function has_stored_proc() {
    return false;
  }

/** Checks whether RDBMS supports subqueries and UNION operator
* @return boolean
**/
  function has_subqueries() {
    return true;
  }

/** Checks whether RDBMS supports transactions
* @return boolean
**/
  function has_transactions() {
    return true;
  }

  function has_fulltext() {
    return false;
  }

  function slashes($text) {
    return SQLite3::escapeString($text);
  }

  
  function get_sql_file() {
    return 'sqlite_new.sql';
  }
}
