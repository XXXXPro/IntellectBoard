<?php
/** ================================
 *  Intellect Board Pro
 *  http://intbpro.ru
 *  Модуль генерации RSS-потока
 *  ================================ */
 
 class Library_rss extends Library implements iParser {    
    /** пустая функция, т.к. RSS нигде не кешируется на данный момент **/
    function clear_cache() {}     
    
    /** Пустая функция, нужна только для единообразия интерфейса всех модулей вывода **/
    function set_style($stylename) {
    }
    
    /** Пустая функция, нужна только для единообразия интерфейса всех модулей вывода **/
    function set_template($tmpl) {
    }
    
    function generate_html($data) {    
      if (!isset($data->intb->link)) $data->intb->link=Library::$app->http(Library::$app->url('/'));
      if (!isset($data->intb->descr)) $data->intb->descr = Library::$app->get_opt('site_description');
        $buffer='<?xml version="1.0" encoding="utf-8"?>
  <rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:slash="http://purl.org/rss/1.0/modules/slash/" xmlns:dc="http://purl.org/dc/elements/1.1/">
  <channel>
  <title>'.htmlspecialchars($data->intb->title).'</title>
  <link>'.htmlspecialchars($data->intb->link).'</link>
  <description>'.htmlspecialchars($data->intb->descr).'</description>
  <lastBuildDate>'.date('r',Library::$app->lastmod).'</lastBuildDate>
  <atom:link href="'.Library::$app->http($_SERVER['REQUEST_URI']).'" rel="self" type="application/rss+xml" />
  <generator>Intellect Board 3 Pro</generator>';
      for ($i=0, $count=count($data->items); $i<$count; $i++) 
        if ($data->items[$i]['postdate']>Library::$app->if_modified_time) { // на всякий случай еще раз проверяем время, чтобы не отдавать старые записи, но все же учитывать start_time нужно сразу, еще на этапе вызова RSS-действия 
        $buffer.=' <item>
          <title>'.htmlspecialchars(trim($data->items[$i]['title'])).'</title>
          <link>'.htmlspecialchars($data->items[$i]['link']).'</link>
          <description><![CDATA['.htmlspecialchars_decode(trim($data->items[$i]['text'])).']]></description>
          <dc:creator>'.htmlspecialchars($data->items[$i]['author']).'</dc:creator>
          <pubDate>'.date('r',$data->items[$i]['postdate']).'</pubDate>';
          if (!empty($data->items[$i]['comments'])) $buffer.='<slash:comments>'.intval($data->items[$i]['comments']).'</slash:comments>';
          $buffer.='<guid>'.htmlspecialchars($data->items[$i]['link']).'</guid>';
          $buffer.='
          </item>';
        }
        $buffer.='</channel></rss>';
        Library::$app->checkpoint('RSS сгенерирован.');
        if (Library::$app->get_opt('debug') && !empty($GLOBALS['IntBF_debug'])) $buffer.='<!--'.htmlspecialchars($GLOBALS['IntBF_debug']).'-->';
        return $buffer;
    }
 }
