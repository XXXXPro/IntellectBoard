<?php

/**
  MySQL Database library for Intellect Board Platform
  Backward compatible module for early MySQL 3.x versions
  @package IntBoard
  @author 4X_Pro <admin@openproj.ru>
  @version 1.0
  @copyright 2007, 2009-2012 XXXX Pro
  @url http://www.openproj.ru
**/

/**  Class for dealing with MySQL database.
*    @package Database
*    @abstract
     **/

require_once(BASEDIR.'db/mysql5.php');     

class Database_mysql3 extends Database_mysql5 implements iDBDriver {
    function set_charset($charset) {} // Early MySQL does not support charsets

    function has_stored_proc() {
      return false;
    }
  
  /** Checks whether RDBMS supports subqueries and UNION operator
  * @return boolean
  **/
    function has_subqueries() {
      return false;
    }
  
  /** Checks whether RDBMS supports transactions
  * @return boolean
  **/
    function has_transactions() {
      return false;
    }    
}

