<?php

/**
*  MySQL Database library for Intellect Board Platform
*  @package IntBoard
*  @author 4X_Pro <admin@openproj.ru>
*  @version 1.0
*  @copyright 2007, 2009-2012 4X_Pro
*  @url http://www.intbpro.ru
**/

/**  Class for dealing with MySQL database.
*    @package Database
**/

class Database_mysqli extends Database implements iDBDriver {
  private $link;
/** Open a connection to a database sever.
**/
  function connect($params) {
    $socket = NULL;
    if (!empty($params['DB_persist']) && version_compare(PHP_VERSION, '5.3.0','>=')) $host='p:'.$params['DB_host'];
    elseif (!empty($params['DB_socket'])) { $host=NULL; $socket = $params['DB_socket']; }
    else $host=$params['DB_host'];
    if (empty($params['DB_socket'])) $params['DB_socket']=NULL;
    if (empty($params['DB_port'])) $params['DB_port']=NULL;
    if (empty($params['DB_name'])) $params['DB_name']=NULL;
    $this->link=mysqli_connect($host,$params['DB_username'],$params['DB_password'],$params['DB_name'],$params['DB_port'],$params['DB_socket']);
    if (!empty($params['DB_charset'])) mysqli_query($this->link,'SET NAMES utf8');
    mysqli_query($this->link,"SET sql_mode = 'ANSI_QUOTES'");
    if (!$this->link) trigger_error('Ошибка подключения к базе данных! '.$this->error_str(),E_USER_ERROR);    
  }
  
/** Closes a connection to database server, opened with {@see function connect()}
*  @return boolean
**/
  function close() {
    if ($this->link) mysqli_close($this->link);
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
      if (!preg_match('|LIMIT\s+\d+,\d+\s*$|is',$sql)) $sql.=' LIMIT '.$limit1.','.$limit2;
    }
    return $sql;
  }

/** Really executes query
*  @return mixed FALSE if there were errors, number of affected rows -- if successful
*  @param string $sql SQL query to execute.
**/
  function _query($sql,$params=false) {
    $sql = preg_replace('|^(\s*SELECT\s+.*?\W)CAST\((.*?)\s+AS\s+integer\)(.*?\s+FROM)|i','$1CAST($s AS unsigned integer)$3',$sql);    
    if (!empty($params)) {
      foreach ($params as $param) $sql = preg_replace('/\?/','\''.$this->slashes($param).'\'',$sql,1);
    }
    return mysqli_query($this->link,$sql);
  }

/** Return error code from last query.
*  @param only_errors TRUE if warnings and notices should not be returned
*  @return intger; 
**/
  function error_num($only_errors=false) {
    return mysqli_errno($this->link);
  }

/** Return error message from last query.
*  @return string;
**/
  function error_str() {
    return mysqli_error($this->link);
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
    return mysqli_fetch_assoc($res);
  }

/** Fetches array from query result.
*    @param resource $res Query result returned by query.
*    @return array Array of fields from current row.
**/
  function fetch_row(&$res) {
    return mysqli_fetch_row($res);
  }

/** Frees the query result.
*    @param resource $res Query result returned by query.
**/
  function free_res(&$res) {
    return mysqli_free_result($res);
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
      $sqlarray1[]='"'.$this->slashes($key).'"';
      $sqlarray2[]='\''.$this->slashes($value).'\'';
    }
    if (count($sqlarray1)) {
      $sql = "INSERT IGNORE INTO $table (".join(', ',$sqlarray1).') VALUES  ('.join(', ',$sqlarray2).')';
      $this->query($sql);
      return $this->insert_id();
    }
    else trigger_error('Нет данных для записи!',E_USER_ERROR);
  }
  
/** Выполнение запроса REPLACE **/  
  function replace($table,$data,$condition) {
    $sqlarray=array();
    foreach ($data as $key=>$value) $sqlarray[]='`'.$this->slashes($key).'`'.'="'.$this->slashes($value).'"';
    if (!empty($sqlarray)) {
      $sql = "REPLACE $table SET ".join(',',$sqlarray);
      return $this->query($sql);
    }
    else trigger_error('Нет данных для записи!',E_USER_ERROR);    
  }

/** Return number of rows in last successful SELECT query.
* @return integer The number of rows.
**/
  function _num_rows(&$res) {
    return mysqli_num_rows($res);
  }

/** Return number of rows affected last query (except INSERT).
* @return integer The number of rows.
**/
  function affected_rows() {
    return mysqli_affected_rows($this->link);
  }

/** Return auto-incement value of the first row, inserted by last INSERT
* @return integer The last auto-increment value.
**/
  function insert_id() {
    return mysqli_insert_id($this->link);
  }
  
  function slashes($text) {
    return mysqli_escape_string($this->link,$text);
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
    return 'MATCH ('.$column.') AGAINST (\''.$this->slashes($text).'\') '; 
  }
  
  /** Проверка наличия текста при полнетесктовом поиске **/
  function full_match($column,$text) {
    return 'MATCH ('.$column.') AGAINST (\''.$this->slashes($text).'\' in boolean mode) '; 
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
    mysqli_commit($this->link);
  }

/** Transaction abort for RBDMS with transactional support
* @return boolean TRUE if successful
**/
  function rollback() {
    mysqli_rollback($this->link);
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
    return 'mysqli_new.sql';
  }
}
