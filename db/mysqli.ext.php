<?php

/**
  MySQL Database library extension class for modifying database tables
  @package IntBoard
  @author 4X_Pro <admin@openproj.ru>
  @version 1.0
  @copyright 2007, 2009-2012 XXXX Pro
  @url http://intbpro.ru
**/


class mysqli_extender {
  private $db;
  
  function __construct(Database_mysqli $db) {
    $this->db=$db;
  }
  
  /** Gets database server software version
   *   @return string Version number of RDBMS.
   **/
  function get_version() {
    $sql = 'SELECT VERSION()';
    $this->db->select_str($sql);
  }  
  
/** Request to RDBMS to analyze table indexes for possible performance improvement
* @return boolean TRUE if successful
* @param mixed $tables String with single table name or array of table names to analyze.
**/
  function analyze($tables) {
    $sql = 'ANALYZE TABLE ';
    if (is_array($tables)) $sql.=join(',',$tables);
    else $sql.=$tables;
    $this->db->query($sql);
  }

/** Request to RDBMS to omptimize table and totally remove deleted rows
* @return boolean TRUE if successful
* @param mixed $tables String with single table name or array of table names to optimize.
**/
  function optimize($tables) {
    $sql = 'OPTIMIZE TABLE ';
    if (is_array($tables)) $sql.=join(',',$tables);
    else $sql.=$tables;
    $this->db->query($sql);
  }

/** Request to RDBMS to repair table and fix damaged indexes
* @return boolean TRUE if successful
* @param mixed $tables String with single table name or array of table names to repair.
**/
  function repair($tables) {
    $sql = 'REPAIR TABLE ';
    if (is_array($tables)) $sql.=join(',',$tables);
    else $sql.=$tables;
    $this->db->query($sql);
  }

/** Get list of all fields from specified table.
* @return array The list of all fields in table (only names).
* @param string $table Table name.
**/
  function get_fields($table) {
    $sql='SHOW COLUMNS FROM '.$table;
    $result=$this->db->select_all($sql);
    for ($i=0, $count=count($result); $i<$count; $i++) {
      $result[$i]['Type']=$this->convert_types_back($result[$i]['Type']);
    }   
  }
  
  function get_tables() {
    $sql = 'SHOW TABLES';
    $result=$this->db->select_all_strings($sql);
    return $result;
  }
  
  function convert_type($name) {
    $name=strtolower(trim($name));
    if ($name=='string') return 'VARCHAR(255)';
    elseif (strpos($name,'string')===0) return str_replace('string','VARCHAR',$name); // �� ������, ����
    elseif ($name=='text') return 'MEDUIMTEXT';
    elseif ($name=='int2') return 'SMALLINT';
    elseif ($name=='int4') return 'INTEGER';
    elseif ($name=='int3') return 'MEDIUMINT';
    elseif ($name=='byte') return 'TINYINT';
    elseif ($name=='blob') return 'BLOB';
    else return $name;
  }
  
  function convert_types_back($name) {
    $name=strtolower(trim($name));
    if ($name=='varchar') return 'string';
    elseif ($name=='text') return 'MEDUIMTEXT';
    elseif ($name=='int2') return 'SMALLINT';
    elseif ($name=='int4') return 'INTEGER';
    elseif ($name=='int3') return 'MEDIUMINT';
    elseif ($name=='byte') return 'TINYINT';
    elseif ($name=='blob') return 'BLOB';
    else return $name;    
  }  
}
