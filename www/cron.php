<?php
define('BASEDIR','../');
chdir(__DIR__);
require(BASEDIR.'app/app.php');
require(BASEDIR.'app/crontab.php');
require(BASEDIR.'etc/ib_config.php');
global $app;
$app = new Application_Crontab();
$app->main();
