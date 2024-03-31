<?php
define('BASEDIR','../');
require(BASEDIR.'app/app.php');
require(BASEDIR.'app/admin.php');
if (!empty($_REQUEST['m'])) $module = $_REQUEST['m'];
else $module='settings';
if (!preg_match('|^\w+$|', $module)) {
  header('HTTP/1.0 403 Forbidden');
  echo 'Invalid module name or hack attempt!';
  exit();
}
require(BASEDIR.'modules/admin/'.$module.'.php');
require(BASEDIR.'etc/ib_config.php');
global $app;
$app = new $module();
$app->main();
