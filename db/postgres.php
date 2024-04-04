<?php

/**
*  PostgreSQL Database library for Intellect Board Platform
*  @package IntBoard
*  @author 4X_Pro <me@4xpro.ru>
*  @version 1.0
*  @copyright 2020 4X_Pro
*  @url http://www.intbpro.ru
**/

/**  Class for dealing with MySQL database.
*    @package Database
**/

class Database_postgres extends Database implements iDBDriver {
  private $link;
  private $last_result;

/** Open a connection to a database sever.
**/
  function connect($params) {
    $conn_string = "host=".$params['DB_host'];
    if (!empty($params['DB_port'])) $conn_string.=" port=".$params['DB_port'];
    if (!empty($params['DB_name'])) $conn_string.=" dbname=".$params['DB_name'];
    else $conn_string.= " dbname=template1";
    if (!empty($params['DB_username'])) $conn_string.=" user=".$params['DB_username'];
    if (!empty($params['DB_password'])) $conn_string.=" password=".$params['DB_password'];
    if (!empty($params['DB_charset'])) $conn_string.=" options='--client_encoding=UTF8'";
    if (!empty($params['DB_persist'])) $this->link=pg_pconnect($conn_string);
    else $this->link=pg_connect($conn_string);
    if (!$this->link) trigger_error('Ошибка подключения к базе данных! '.$this->error_str(),E_USER_ERROR);    
    if (!empty($params['DB_schema'])) {
      $sql = 'SET search_path TO '.$params['DB_schema'];
      $this->query($sql);
    }
  }
  
/** Closes a connection to database server, opened with {@see function connect()}
*  @return boolean
**/
  function close() {
    if ($this->link) pg_close($this->link);
    $this->link=NULL;
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
**/
  function _query($sql,$params=false) {
    try {
      if (!empty($params)) {
        foreach ($params as $param) $sql = preg_replace('/\?/','\''.$this->slashes($param).'\'',$sql,1);
      } 
      $this->last_result=pg_query($this->link,$sql);
      return $this->last_result;
    }
    catch (Exception $e) {
      trigger_error("SQL query $sql caused exception: ".$e->getFile().", line ".$e->getLine().': '.$e->getMessage(),E_USER_ERROR);
    }    

  }

/** Return error code from last query.
*  @param only_errors TRUE if warnings and notices should not be returned
*  @return intger; 
**/
  function error_num($only_errors=false) {
    return pg_last_error()!=='';
  }

/** Return error message from last query.
*  @return string;
**/
  function error_str() {
    return pg_last_error($this->link);
  }
  
/*  function explain($sql) {
    if (preg_match('/^\s*SELECT/is',$sql)) {
      $sql2 = "EXPLAIN $sql";
      $res2 = mysqli_query($sql2,$this->link);
      $buffer='<table style="width: 100%" style="border: #888 1px solid"><tr>';
      for ($i=0; $i<mysqli_num_fields($res2); $i++) $buffer.='<td><b>'.mysqli_field_name($res2,$i).'</b>';
      while ($row=mysqli_fetch_row($res2)) {
        $buffer.='<tr>';
        foreach ($row as $column) $buffer.='<td>'.$column.'</td>';
        $buffer.='</tr>';
      }
      $buffer.='</table>';
      mysqli_free_result($res2);
    }
    else $buffer='';
    return $buffer;
  }*/

/** Fetches associative array from query result.
*    @param resource $res Query result returned by query.
*    @return array Hash of fields from current row.
**/
  function fetch_array(&$res) {
    return pg_fetch_assoc($res);
  }

/** Fetches array from query result.
*    @param resource $res Query result returned by query.
*    @return array Array of fields from current row.
**/
  function fetch_row(&$res) {
    return pg_fetch_row($res);
  }

/** Frees the query result.
*    @param resource $res Query result returned by query.
**/
  function free_res(&$res) {
    return pg_free_result($res);
  }

  function insert($table,$data) {
    foreach ($data as $key=>$value) if (is_bool($data[$key])) $data[$key]=intval($value);
    $this->last_result=pg_insert($this->link,DB_schema.".".$table,$data);
    if ($this->last_result===false || (!is_resource($this->last_result) && !get_class($this->last_result)=='PgSql\\Result')) trigger_error('SQL Error in INSERT query to table '.$table.': '.$this->error_str(),E_USER_ERROR);
    return $this->last_result;
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
        $sqlarray1[]='"'.$key.'"';
        $sqlarray2[]='\''.$this->slashes($value).'\'';
    }
    if (count($sqlarray1)) {
      $sql = 'INSERT INTO '.$table.' ('.join(', ',$sqlarray1).') VALUES  ('.join(', ',$sqlarray2).') ON CONFLICT DO NOTHING';
      return $this->query($sql);
    }
    else trigger_error('Нет данных для записи!',E_USER_ERROR);
  }
  
/** There is no REPLACE in PostgreSQL, so we try to update table. If no rows changed, we try to insert data. **/  
  function replace($table,$data,$condition) {
    $res=$this->update($table,$data,$condition);
    if (pg_affected_rows($res)==0) $this->insert_ignore($table,$data);
  }
  

/** Return number of rows in last successful SELECT query.
* @return integer The number of rows.
**/
  function _num_rows(&$res) {
    return pg_num_rows($res);
  }

/** Return number of rows affected last query (except INSERT).
* @return integer The number of rows.
**/
  function affected_rows() {
    return pg_affected_rows($this->last_result);
  }

/** Return auto-incement value of the first row, inserted by last INSERT
* @return integer The last auto-increment value.
**/
  function insert_id() {
    return $this->select_int("SELECT lastval()");
  }
  
  function slashes($text) {
    return pg_escape_string($this->link,$text);
  }

/** Locks tables for write or for read
* @return boolean TRUE if successful
* @param mixed $tables String with single table name or array of table names to lock.
* @param integer $mode Mode for locking. May be one of this values: <ul>
* <li>DB_LOCK_READ -- locking for reading
* <li>DB_LOCK_WRITE -- locking for writing</ul>
**/
  function lock_tables($tables,$mode) {
    if (!is_array($tables)) $tables = explode(',',$tables); // если параметр $tables не массив, то считаем, что это строка со списком таблиц через запятую
    $sql = 'LOCK TABLES ';
    for ($i=0,$count=count($tables);$i<$count;$i++) {
      if ($i>0) $sql.=', ';
      if ($mode) $sql.=$tables[$i].' WRITE';
      else $sql.=$tables[$i].' READ';
    }
    return $this->query($sql);
  }

/** Unlocks previously locked tables
* @return boolean TRUE if successful
* @param mixed $tables String with single table name or array of table names to repair.
**/
  function unlock_tables($tables) {
    $sql = 'UNLOCK TABLES ';    
    return $this->query($sql);
  }
  
  /** Получение количественного показателя релевантности при полнотекстовом поиске **/
  function full_relevancy($column,$text) {
    if (strpos($text,'&')!==false || strpos($text,'|')!==false) return ' ts_rank(to_tsvector(\'russian\','.$column.'),to_tsquery(\''.$this->slashes(preg_replace('/\s+/','|',trim($text))).'\')) '; 
    else return ' ts_rank(to_tsvector(\'russian\','.$column.'),plainto_tsquery(\'russian\',\''.$this->slashes($text).'\')) '; 
  }
  
  /** Проверка наличия текста при полнетесктовом поиске **/
  function full_match($column,$text) {
    if (strpos($text,'&')!==false || strpos($text,'|')!==false) return ' to_tsvector(\'russian\','.$column.') @@ to_tsquery(\'russian\',\''.$this->slashes(preg_replace('/\s+/','|',trim($text))).'\')'; 
    return ' to_tsvector(\'russian\','.$column.') @@ plainto_tsquery(\'russian\',\''.$this->slashes($text).'\')';  
  }  


/** Transaction start for RBDMS with transactional support
* @return boolean TRUE if successful
**/
  function begin() {
//    mysqli_begin_transaction($this->link);
  }

/** Transaction commit for RBDMS with transactional support
* @return boolean TRUE if successful
**/
  function commit() {
//    pg_commit($this->link);
  }

/** Transaction abort for RBDMS with transactional support
* @return boolean TRUE if successful
**/
  function rollback() {
    pg_rollback($this->link);
  }
/** Checks whether RDBMS supports stored procedures
* @return boolean
**/
  function has_stored_proc() {
    return true;
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
    return true;
  }

  function get_sql_file() {
    return 'postgres_new.sql';
  }
}
