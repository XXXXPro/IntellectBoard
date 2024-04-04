<?php
try {
define('BASEDIR','../');
if (!@include(BASEDIR.'etc/ib_config.php')) {
  header('Content-Type: text/html; charset=utf-8');
  echo 'Если вы только что установили Intellect Board, запустите <a href="install.php">инсталлятор</a>, чтобы произвести начальную настройку форума.<br /> Если форум уже был настроен, а вы все равно видите эту надпись, проверьте корректность файла etc/ib_config.php.';
  exit();
}
require(BASEDIR.'app/app.php');
$classname = Application::do_routing();
if (!$classname) {
  header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
  include BASEDIR.'www/404.htm';
  exit();
}
if ($classname==='admin')  {
  require(BASEDIR.'app/admin.php');
  $classname = $_REQUEST['m'];
  $filename = BASEDIR.'modules/admin/'.$classname.'.php';
}
else $filename = BASEDIR.'modules/'.$classname.'.php';
require($filename);
$app = new $classname();
$app->main();
}
catch (Exception $ex) {
  header($_SERVER['SERVER_PROTOCOL'].' 503 Temporary unavalaible');
  require BASEDIR.'www/503.htm';
}
