<?php
define('BASEDIR','../');
chdir(__DIR__);
$_SERVER['REQUEST_URI']='cron.php';
$_SERVER['REMOTE_ADDR']='127.0.0.1';
$_SERVER['HTTP_USER_AGENT']='Crontab script';
require(BASEDIR.'app/app.php');
require(BASEDIR.'app/crontab.php');
require(BASEDIR.'etc/ib_config.php');
global $app;
$app = new Application_Crontab();
$app->main();
