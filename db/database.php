<?php

/**
*  Database library for 4X_Pro's Intellect Framework
*  @package IntBPro
*  @author 4X_Pro <me@4xpro.ru>
*  @version 3.0
*  @copyright 2006-2013, 4X_Pro
*  @url http://www.intbpro.ru
**/

define('INTB_ERROR_SILENT',0);
define('INTB_ERROR_MASK',1);
define('INTB_ERROR_ALL',2);

define('INTB_LOCK_READ',1);
define('INTB_LOCK_WRITE',2);

/**  Common abstract class for dealing with database. All RDBMS-specific classes
*    should be this class descendants.
*    @package MySQL3
*    @abstract
     **/

class Database {
/**  @access private
*    @var resource
*    Link for database connection
**/
  private $link;

/** @access private
*    @var array
*    Connection parameters: login, password, host, etc. **/
  private $params;

/** @access private
*    @var integer
*    Number of executed queries. **/
  public $query_count=0;

/** @access private
*    @var float
*    Number of time elapsed for queries. **/
  public $query_time=0;

/** @var float
*    If query executed more than specified number of seconds, the notice will be generated and logged. **/
  private $query_long_time;
  
/** The number of rows in last successful SELECT query **/ 
  private $num_rows;

  function slashes($text) {
    return addslashes($text);
  }

/** Execute SELECT query and return all fetched rows as array, each row is hash indexed by field names.
*  @return array
*  @param string $sql SQL query to execute
*  @param integer $limit Start value for SQL LIMIT clause
*  @param integer $count Number of values to fetch in SQL LIMIT clause
**/
  function select_all($sql,$limit=false,$count=false,$params=false) {
    $sql = $this->add_limiter($sql,$limit,$count);
    $res = $this->query($sql,true,$params);
    //$this->num_rows=$this->_num_rows($res);
    $result = array();
    while ($data=$this->fetch_array($res)) {
      $result[]=$data;
    }
    $this->free_res($res);
    $this->num_rows = count($result);
    return $result;
  }
  
/** Execute SELECT query and return all fetched rows as array, each row is array 
* (the index of field is the number of its position in query).
*  @return array
*  @param string $sql SQL query to execute
*  @param integer $limit Start value for SQL LIMIT clause
*  @param integer $count Number of values to fetch in SQL LIMIT clause
**/
  function select_all_rows($sql,$limit=false,$count=false,$params=false) {
    $sql = $this->add_limiter($sql,$limit,$count);
    $res = $this->query($sql,true,$params);
    //$this->num_rows=$this->_num_rows($res);
    $result = array();
    while ($data=$this->fetch_row($res)) {
      $result[]=$data;
    }
    $this->free_res($res);
    $this->num_rows = count($result);
    return $result;
  }
  
/** Execute SELECT query and return all fetched rows as array, each row is array 
* (the index of field is the number of its position in query).
*  @return array
*  @param string $sql SQL query to execute
*  @param integer $limit Start value for SQL LIMIT clause
*  @param integer $count Number of values to fetch in SQL LIMIT clause
**/
  function select_all_numbers($sql,$limit=false,$count=false,$params=false) {
    $sql = $this->add_limiter($sql,$limit,$count);
    $res = $this->query($sql,true,$params);
    //$this->num_rows=$this->_num_rows($res);
    $result = array();
    while ($data=$this->fetch_row($res)) {
      $result[]=intval($data[0]);
    }
    $this->free_res($res);
    $this->num_rows = count($result);
    return $result;
  }
  
/** Execute SELECT query and return all fetched rows as array, each row is array 
* (the index of field is the number of its position in query).
*  @return array
*  @param string $sql SQL query to execute
*  @param integer $limit Start value for SQL LIMIT clause
*  @param integer $count Number of values to fetch in SQL LIMIT clause
**/
  function select_all_strings($sql,$limit=false,$count=false,$params=false) {
    $sql = $this->add_limiter($sql,$limit,$count);
    $res = $this->query($sql,true,$params);
    //$this->num_rows=$this->_num_rows($res);
    $result = array();
    while ($data=$this->fetch_row($res)) {
      $result[]=$data[0];
    }
    $this->free_res($res);
    $this->num_rows = count($result);
    return $result;
  }

/** Execute SELECT query and return all fetched rows as hash, and builds
*   a hash of hashes where indexes is specified in $key column and
*   other columns are array members of each hash
* (the index of field is the number of its position in query).
*  @return array
*  @param string $sql SQL query to execute
*  @param integer $limit Start value for SQL LIMIT clause
*  @param integer $count Number of values to fetch in SQL LIMIT clause
**/
  function select_hash($sql,$key,$limit=false,$count=false,$params=false) {
    $data=$this->select_all($sql,$limit,$count,$params);
    $result=array();
    if ($data) {
      for ($i=0, $count=count($data); $i<$count; $i++) $result[$data[$i][$key]]=$data[$i];
    }
    return $result;
  }
  
/** Execute SELECT query and return all fetched rows as hash, and builds
*   a hash of hashes where indexes is specified in $key column and
*   other columns are array members of each hash
* (the index of field is the number of its position in query).
*  @return array
*  @param string $sql SQL query to execute
*  @param integer $limit Start value for SQL LIMIT clause
*  @param integer $count Number of values to fetch in SQL LIMIT clause
**/
  function select_simple_hash($sql,$limit=false,$count=false,$params=false) {
    $data=$this->select_all_rows($sql,$limit,$count,$params);
    $result=array();
    if ($data) {
        for ($i=0, $count=count($data); $i<$count; $i++) $result[$data[$i][0]]=$data[$i][1];
    }
    return $result;
  }

/** Execute SELECT query and return hash, which contains arrays of rows with same key column value, while
* select_hash function returns only one row in hash key.
*  @return array
*  @param string $sql SQL query to execute
*  @param integer $limit Start value for SQL LIMIT clause
*  @param integer $count Number of values to fetch in SQL LIMIT clause
**/  
  function select_super_hash($sql,$key,$limit=false,$count=false,$params=false) {
    $data=$this->select_all($sql,$limit,$count,$params);
    $result=array();
    if ($data) {
      for ($i=0, $count=count($data); $i<$count; $i++) $result[$data[$i][$key]][]=$data[$i];
    }
    return $result;
  }


/** Execute SELECT query for fetching only one row and return hash indexed by field names.
*  @return array
*  @param string $sql SQL query to execute
**/
  function select_row($sql,$params=false) {
    $sql=$this->add_limiter($sql,1);
    $res=$this->query($sql,true,$params);
    $result=$this->fetch_array($res);
    $this->free_res($res);
    $this->num_rows=!empty($result) ? 1 : 0;
    return $result;
  }

/** Execute SELECT query for fetching integer value.
*  @return integer
*  @param string $sql SQL query to execute.
**/
  function select_int($sql,$params=false) {
    $sql=$this->add_limiter($sql,1);
    $res=$this->query($sql,true,$params);
    list($result)=$this->fetch_row($res);
    $this->num_rows=($res!==false) ? 1 : 0;
    $this->free_res($res);
    return intval($result);
 }

/** Execute SELECT query for fetching integer value.
*  @return array
*  @param string $sql SQL query to execute.
**/
  function select_str($sql,$params=false) {
    $sql=$this->add_limiter($sql,1);
    $res=$this->query($sql,true,$params);
    list($result)=$this->fetch_row($res);
    $this->num_rows=($res!==false) ? 1 : 0;
    $this->free_res($res);
    return $result;
  }

/** Execute query of any type except SELECT and mark
*  @return mixed FALSE if there were errors, number of affected rows -- if successful
*  @param string $sql SQL query to execute.
**/
  function query($sql,$fatal=true,$params=false) {
    $long_time=(defined('CONFIG_sql_longtime') && CONFIG_sql_longtime) ? CONFIG_sql_longtime : 30;
    $dbg=(defined('CONFIG_sql_debug')) ? CONFIG_sql_debug : 0;
//    if (!$this->link) $this->_connect();
    if (is_array($fatal)) trigger_error('Invalid argument: $fatal can not be array');
    $start_time=microtime(true);
    $res=$this->_query($sql,$params);
    if ($res===false) {
      _dbg($sql);
      if ($fatal) trigger_error('Error in SQL ('.$this->error_num().'): '.$this->error_str().'. Query: '.$sql,E_USER_ERROR);
    }
    $this->query_count++;
    $q_time=microtime(true)-$start_time;
    $this->query_time+=$q_time;
    if ($dbg>=1) {
      _dbg($sql.' Exec time:'.$q_time);
    }
    if ($q_time>$long_time && ($dbg>=2)) {
      _dbg($this->explain($sql));
    }
    return $res;
  }

/** Выполнение SQL-оператора INSERT над массивом с данными 
*  This function should not be called directly, use store instead.
*  @return boolean
*  @param string $table Table to store data.
**/
  function insert($table,$data) {
    $sqlarray1=array();
    $sqlarray2=array();
    foreach ($data as $key=>$value) {
        $sqlarray1[]='"'.$this->slashes($key).'"';
        $sqlarray2[]='\''.$this->slashes($value).'\'';
    }
    if (count($sqlarray1)) {
      $sql = "INSERT INTO $table (".join(', ',$sqlarray1).') VALUES  ('.join(', ',$sqlarray2).')';
      return $this->query($sql);
    }
    else trigger_error('Нет данных для записи!',E_USER_ERROR);
  }

/** Выполнение запроса UPDATE **/  
  function update($table,$data,$condition) {
    $sqlarray=array();
    $result=false;
    foreach ($data as $key=>$value) $sqlarray[]='"'.$this->slashes($key).'"'.'=\''.$this->slashes($value).'\'';
    if (!empty($sqlarray)) {
      $sql="UPDATE $table SET ".join(',',$sqlarray).' WHERE '.$condition;
      $result=$this->query($sql);
    }
    else trigger_error('Нет данных для записи!',E_USER_ERROR);
    return $result;
  }
  
/** Return number of rows in last successful SELECT query.
* @return integer The number of rows.
**/
  function num_rows() { return $this->num_rows; }
  
  /** Преобразует PHP-массив в часть SQL-запроса, которая может быть безопасно помещена в условие WHERE
   *
   * @param mixed $data Массив параметров или строка со списком параметров, разделенных запятыми
   */
  function array_to_sql($data,$column) {
    $sqldata=$this->slashes($column).' IN (';
    if (!is_array($data)) $data=explode(',',$data); // если пришла строка, а не массив, разбиваем ее по запятым и превращаем в массив все равно
    $first=true;
    foreach ($data as $item) {
      if (!$first) $sqldata.=',';
      $first=false;
      $sqldata.='\''.$this->slashes($item).'\'';
    }
    if ($first) $sqldata=' 0=1'; // если массив пуст (цикл ни разу не выполнился), то возвращаем условие, которое всегда ложно
    else $sqldata.=')';
    return $sqldata;
  }
}

interface iDBDriver {
  function add_limiter($sql,$limit1=false,$limit2=false);
  function connect($params);
  function close();
  function _query($sql);
  function error_num($only_errors=false);
  function insert_ignore($table,$data);
  function error_str();
//  function explain($sql);
  function fetch_array(&$res);
  function fetch_row(&$res);
  function replace($table,$data,$condition);
  function _num_rows(&$res);
  function affected_rows();
  function insert_id();
  function begin();
  function commit();
  function rollback();
  function full_match($column,$text);
  function full_relevancy($column,$text);
  function has_stored_proc();
  function has_subqueries();
  function has_transactions();
  function has_fulltext();
  function get_sql_file();
}

/* Это будет интерфейс для редактирования таблиц при установке/удалении IntB
interface iDBExtender {
  function analyze($tables);
  function optimize($tables);
  function repair($tables);  
  function list_fields($table);
  function get_tables($prefix);
  function list_fields($prefix);
  function list_indexes($table);
  function get_db_size($prefix);  
  function db_create($params,$name);
  function check_access();
  function dump_tables($tables,$filename);
  function build_tables($tables);
}
*/
