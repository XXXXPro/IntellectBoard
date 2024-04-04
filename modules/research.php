<?php

require_once(BASEDIR.'modules/stdforum.php');

class research extends stdforum {

  function action_distribution() {
    if ($this->is_guest()) $this->output_403('Гостям нельзя смотреть расклад голосования по типам.');
    $fields = array('soc_type'=>'социотипу','py_type'=>'ПЙ-типу','temp_type'=>'темпористическому типу','amato_type'=>'аматотипу','psy_type='=>'типу в псикосмологии');
    $field = (!empty($_REQUEST['field'])) ? $this->db->slashes($_REQUEST['field']) : 'soc_type';
    if (!isset($fields[$field])) $this->output_404('Некорректно указано поле для получения распределения!');

    $sql = 'SELECT `values` FROM cms_users_fields WHERE name="'.$this->db->slashes($field).'"';
    $this->out->types = explode("\n",$this->db->select_str($sql));
    $this->out->fields = $fields;
    $this->out->field = $field;

    $tlib = $this->load_lib('topic');
    $tid = $this->topic['id'];
    $this->out->poll = $tlib->get_poll($tid);

    $sql = 'SELECT `'.$field.'` AS field, pv.id, COUNT(*) AS total FROM '.DB_prefix.'poll_variant pv, '.DB_prefix.'vote v, cms_users u WHERE pv.id=v.pvid AND v.uid=u.id AND pv.tid='.intval($tid).
         ' GROUP BY pv.id, u.`'.$field.'`';
    $this->out->poll_data = $this->db->select_super_hash($sql,'field');
    $this->out->max = 1;
    foreach ($this->out->poll_data as $items) {
      foreach ($items as $item) if ($this->out->max < $item['total']) $this->out->max=$item['total']+1;
    }
  }

  function set_title() {
    $result = parent::set_title();
    if ($this->action==='distribution') $result = 'Распределение результатов опроса по '.$this->out->fields[$this->out->field];
    return $result;
  }

  function set_location() {
    $result = parent::set_location();
    if ($this->action==='distribution') {
      array_push($result,array($this->topic['title'],$this->url($this->topic['full_hurl'])));
      array_push($result,array('Расклад результатов'));
    }
    return $result;
  }

}
