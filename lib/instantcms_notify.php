<?php

require_once(BASEDIR.'lib/notify.php');

class Library_instantcms_notify extends Library_notify implements iNotifier {
  function new_topic($post,$topic,$forum,$parsed) {
    parent::new_topic($post,$topic,$forum,$parsed);
    $data['type_id']=30;
    $data['user_id']=$post['uid'];
    $data['subject_title']=$topic['title'];
    $data['subject_id']=$topic['id'];
    if (empty($topic['hurl'])) $topic['hurl']=$topic['id'];
    $data['subject_url']='forum/'.$forum['hurl'].'/'.$topic['hurl'].'/';
//    $data['reply_url']=$data['subject_url'].'#replyform';
    $data['date_pub']=gmdate('Y-m-d H:i:s',$post['postdate']+3*60*60);
    Library::$app->db->insert('cms_activity',$data);
  }

  function new_post($post,$topic,$forum,$parsed) {
    parent::new_post($post,$topic,$forum,$parsed);
    $data['type_id']=31;
    $data['user_id']=$post['uid'];
    $data['subject_title']=$topic['title'];
    $data['subject_id']=$topic['id'];
    if (empty($topic['hurl'])) $topic['hurl']=$topic['id'];
    $data['subject_url']='forum/'.$forum['hurl'].'/'.$topic['hurl'].'/new.htm';
//    $data['reply_url']=$data['subject_url'].'#replyform';
    $data['date_pub']=gmdate('Y-m-d H:i:s',$post['postdate']+3*60*60);
    $sql = 'DELETE FROM cms_activity WHERE (type_id=30 OR type_id=31) AND subject_id='.intval($topic['id']);
    Library::$app->db->query($sql);
    Library::$app->db->insert('cms_activity',$data);
  }

}
