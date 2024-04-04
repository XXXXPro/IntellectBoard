<?php
/** ================================
*  @package IntBPro
*  @author 4X_Pro <admin@openproj.ru>
*  @version 3.0
*  @copyright 2018 4X_Pro, INTBPRO.RU
*  @url http://www.intbpro.ru
*  Генератор Sitemap.xml
*  ================================ */

class Library_sitemap extends Library {
  function cron_generate() {
    /** @var Library_forums **/
    $forumlib = Library::$app->load_lib('forums',true);
    Library::$app->set_user(Library::$app->load_guest());
    $fh = fopen(BASEDIR.'/tmp/sitemap.tmp','w');
    fputs($fh,"<url><loc>##DOMAIN##</loc><lastmod>".date('Y-m-d')."</lastmod><priority>1.0</priority><changefreq>always</changefreq></url>\n");
    fputs($fh,"<url><loc>##DOMAIN##online/</loc><lastmod>".date('Y-m-d')."</lastmod><priority>0.2</priority><changefreq>always</changefreq></url>\n");
    fputs($fh,"<url><loc>##DOMAIN##team.htm</loc><lastmod>".date('Y-m-d')."</lastmod><priority>0.2</priority><changefreq>daily</changefreq></url>\n");
    fputs($fh,"<url><loc>##DOMAIN##newtopics/</loc><lastmod>".date('Y-m-d')."</lastmod><priority>0.1</priority><changefreq>always</changefreq></url>\n");
    $counter=4;
    $cats = $forumlib->list_categories();
    foreach ($cats as $cat) {
      fputs($fh,"<url><loc>##DOMAIN##category/".$cat['id'].".htm</loc><priority>0.1</priority></url>\n");
      $counter++;
    }
    
    $forums = $forumlib->list_forums(array('all'=>true,'typeinfo'=>true));
    $tperpage = Library::$app->get_opt('topics_per_page','user'); // берем из настроек пользователя
    if (!$tperpage) $tperpage = Library::$app->get_opt('topics_per_page');  // берем из настроек сайта в целом
    if (!$tperpage) $tperpage = 10; // если ниоткуда не получилось взять кол-во тем на странице, берем жестко закодированное значение во избежание деления на ноль
    $fdata = array();
    $fvalues = array();
    $skip_forums = array();
    foreach ($forums as $forum) {
      if (Library::$app->check_access('view',$forum['id'])) {        
        if (empty($forum['hurl'])) $forum['hurl']=$forum['id'];
        $fdata[$forum['id']]=$forum['hurl'];
        $fvalue = 0.6;
        if ($forum['is_flood']) $fvalue-=0.1;
        if (!$forum['is_stats']) $fvalue-=0.1;
        if (!$forum['is_start']) $fvalue-=0.05;
        $fvalues[$forum['id']]=$fvalue;
        if (!empty($forum['skip_sitemap'])) $skip_forums[]=$forum['id'];
        fputs($fh,"<url><loc>##DOMAIN##".urlencode($forum['hurl'])."/</loc><lastmod>".$this->lastmod($forum['lastmod'])."</lastmod><changefreq>".$this->changefreq($forum['lastmod'])."</changefreq><priority>".$fvalue."</priority></url>\n");
        $counter++;
        for ($i=2;$i<=ceil($forum['topic_count']/$tperpage);$i++) {
          fputs($fh,"<url><loc>##DOMAIN##".$forum['hurl']."/".$i.".htm</loc><lastmod>".$this->lastmod($forum['lastmod'])."</lastmod><changefreq>".$this->changefreq($forum['lastmod'])."</changefreq><priority>".($fvalue-0.1)."</priority></url>\n");
          $counter++;
        }
      }
    }
   
    /** @var Library_topic **/
    $topiclib = Library::$app->load_lib('topic',false);
    $pperpage = Library::$app->get_opt('posts_per_page','user'); // берем из настроек пользователя
    if (!$pperpage) $pperpage = Library::$app->get_opt('posts_per_page');  // берем из настроек сайта в целом
    if (!$pperpage) $pperpage = 10; // если ниоткуда не получилось взять кол-во тем на странице, берем жестко закодированное значение во избежание деления на ноль    
    if ($topiclib) {
      $forum_ids = array_diff(array_keys($fdata),$skip_forums);
      $topics = $topiclib->list_topics(array('fid'=>$forum_ids));
      foreach ($topics as $topic) {
        if (isset($fdata[$topic['fid']])) {
           $tvalue = $fvalues[$topic['fid']]+0.2;
           if ($topic['post_count']>0) {
             $tvalue+=0.1*$topic['valued_count']/$topic['post_count'];
             $tvalue-=0.1*$topic['flood_count']/$topic['post_count'];
           }
           $tvalue=sprintf("%.3f",$tvalue);
           if (empty($topic['hurl'])) $topic['hurl']=$topic['id'];
           fputs($fh,"<url><loc>##DOMAIN##".urlencode($fdata[$topic['fid']])."/".urlencode($topic['hurl'])."/</loc><lastmod>".$this->lastmod($topic['lastmod'])."</lastmod><changefreq>".$this->changefreq($topic['lastmod'])."</changefreq><priority>".$tvalue."</priority></url>\n");
           $counter++;
           for ($i=2;$i<=ceil($topic['post_count']/$pperpage);$i++) {
             fputs($fh,"<url><loc>##DOMAIN##".urlencode($fdata[$topic['fid']])."/".urlencode($topic['hurl'])."/".$i.".htm</loc><lastmod>".$this->lastmod($topic['lastmod'])."</lastmod><changefreq>".$this->changefreq($topic['lastmod'])."</changefreq><priority>".($tvalue-0.1)."</priority></url>\n");
             $counter++;
           }           
        }
      }
    }
    fclose($fh);
    file_put_contents(BASEDIR.'/tmp/sitemap.txt',$counter);
  }
  
  function lastmod($date) {
    return date('c',$date);
  }
  
  function changefreq($date) {
    $curtime = Library::$app->time;
    if ($curtime-$date>2*30*24*60*60) return 'monthly';
    if ($curtime-$date>2*7*24*60*60) return 'weekly';
    return 'daily';
  }
}