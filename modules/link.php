<?php
/** ================================
 *  @package IntBPro
 *  @author 4X_Pro <admin@openproj.ru>
 *  @version 3.0
 *  @copyright 2007,2009-2011,2013 4X_Pro, INTBPRO.RU
 *  http://intbpro.ru
 *  Раздел типа "внешняя ссылка"
 *  ================================ */

require_once(BASEDIR . 'app/forum.php');

class link extends Application_Forum {
   function action_view() { // делаем редирект на случай, если каким-то образом все же зашли по внутренней ссылке (например, через выпадающий список разделов)  
     $extdata =$this->get_ext_data();
     if (empty($extdata['url'])) $this->output_404('Внешняя ссылка раздела настроена некорректно!');
     else $this->redirect($extdata['url'],true);
   }
}