<?php
define('BASEDIR','../');
require(BASEDIR.'etc/ib_config.php');
$LIMIT = 50000;

if (!is_readable(BASEDIR.'/tmp/sitemap.tmp') || !is_readable(BASEDIR.'/tmp/sitemap.txt')) {
  header($_SERVER['SERVER_PROTOCOL'].' 503 Service Unavailable');
  exit();
}

$total = intval(file_get_contents(BASEDIR.'/tmp/sitemap.txt'));

$url = empty($_SERVER['HTTPS']) ? 'http://' : 'https://';
$url.= str_replace('\\','/',$_SERVER['HTTP_HOST'].dirname($_SERVER['REQUEST_URI']));
if (substr($url,-1,1)!='/') $url.='/';
$pos = strpos($url,'?');
if ($pos!==false) $url =substr($url,0,$pos);
date_default_timezone_set('UTC'); // выставляем временную зону в UTC, чтобы для вывода даты было достаточно приплюсовать смещение, заданное в настройках пользователя

if (defined('CONFIG_gzip') && CONFIG_gzip && function_exists('ob_gzhandler')) ob_start('ob_gzhandler');
else ob_start(); // если выключена поддержка GZIP, используем простую буферизацию

ob_implicit_flush(false);

if (isset($_GET['file'])) {
  $file_id = intval($_GET['file']);
  if (!$file_id || ($file_id-1)*$LIMIT>$total) {
    header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
    echo 'File number is out of range!';
    exit();
  }
}

$mtime = filemtime(BASEDIR.'/tmp/sitemap.tmp');
if ($total<=$LIMIT) {
  output_map(0,$total,$url,$mtime);
}
elseif (!isset($_GET['file'])) {
  output_list(ceil($total/$LIMIT),$url,$mtime);
}
else {  
  output_map(($file_id-1)*$LIMIT,$LIMIT,$url,$mtime);
}
header('Content-Type: application/xml');
header('Last-Modified: '.gmdate('D, d M Y H:i:s \G\M\T', $mtime));
header('Content-Length: '.ob_get_length());
ob_end_flush();


function output_map($skip,$max,$url) {
  echo '<?xml version="1.0" encoding="UTF-8"?>';
  echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
  $fh = fopen(BASEDIR.'/tmp/sitemap.tmp','r');
  $i=0;
  while ($i<$skip && !feof($fh)) { fgets($fh); $i++; }
  $i=0;
  while ($i<$max && !feof($fh)) { echo str_replace('##DOMAIN##',$url,fgets($fh)); $i++; }
  fclose($fh);
  echo '</urlset>';
}

function output_list($files,$url,$mtime) {
  echo '<?xml version="1.0" encoding="UTF-8"?>';
  echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
  for ($i=1;$i<=$files;$i++) echo '<sitemap><loc>'.$url.'sitemap.'.$i.'.xml</loc><lastmod>'.date('c',$mtime).'</lastmod></sitemap>';
  echo '</sitemapindex>';
}