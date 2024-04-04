PRAGMA synchronous = OFF;
PRAGMA journal_mode = MEMORY;
BEGIN TRANSACTION;
CREATE TABLE `ib_access` (
  `gid` integer NOT NULL
,  `fid` integer  NOT NULL
,  `view` text  NOT NULL DEFAULT '1'
,  `read` text  NOT NULL DEFAULT '1'
,  `post` text  NOT NULL DEFAULT '1'
,  `attach` text  NOT NULL DEFAULT '1'
,  `topic` text  NOT NULL DEFAULT '1'
,  `poll` text  NOT NULL DEFAULT '1'
,  `html` text  NOT NULL DEFAULT '0'
,  `vote` text  NOT NULL DEFAULT '0'
,  `rate` text  NOT NULL DEFAULT '0'
,  `edit` text  NOT NULL DEFAULT '1'
,  `nocaptcha` text  NOT NULL DEFAULT '1'
,  `nopremod` text  NOT NULL DEFAULT '1'
,  PRIMARY KEY (`gid`,`fid`)
);
INSERT INTO `ib_access` VALUES (0,0,'1','1','0','0','0','0','0','0','0','0','0','0'),(50,0,'1','1','1','0','1','0','0','0','0','0','1','1'),(100,0,'1','1','1','1','1','0','0','1','0','1','1','1'),(120,0,'1','1','1','1','1','0','0','1','0','1','1','1'),(140,0,'1','1','1','1','1','1','0','1','1','1','1','1'),(160,0,'1','1','1','1','1','1','0','1','1','1','1','1'),(180,0,'1','1','1','1','1','1','0','1','1','1','1','1'),(499,0,'1','1','1','1','1','1','0','1','1','1','1','1'),(500,0,'1','1','1','1','1','1','0','1','1','1','1','1'),(1000,0,'1','1','1','1','1','1','1','1','1','1','1','1'),(1024,0,'1','1','1','1','1','1','1','1','1','1','1','1');
CREATE TABLE `ib_banned_ip` (
  `start` integer  NOT NULL
,  `end` integer  NOT NULL
,  `till` integer  NOT NULL
,  PRIMARY KEY (`start`,`end`,`till`)
);
CREATE TABLE `ib_bots` (
  `id` integer NOT NULL PRIMARY KEY AUTOINCREMENT
,  `user_agent` varchar(255) NOT NULL
,  `bot_name` varchar(255) NOT NULL
,  `last_visit` integer  NOT NULL
);
INSERT INTO `ib_bots` VALUES (1,'YandexBot','Яндекс',0),(2,'Googlebot','Google',0),(3,'bingbot','Bing!',0),(5,'Yahoo! Slurp','Yahoo!',0),(6,'mail.ru','@Mail.Ru',0),(7,'W3C_Validator','W3C Validator',0);
CREATE TABLE `ib_captcha` (
  `hash` char(32) NOT NULL
,  `code` char(8) NOT NULL
,  `active` text  NOT NULL DEFAULT '1'
,  `lastmod` integer NOT NULL
,  `ip` integer NOT NULL
,  PRIMARY KEY (`hash`)
);
CREATE TABLE `ib_category` (
  `id` integer  NOT NULL PRIMARY KEY AUTOINCREMENT
,  `title` varchar(80) NOT NULL
,  `collapsed` text  NOT NULL DEFAULT '0'
,  `sortfield` integer  NOT NULL DEFAULT 0
);
CREATE TABLE `ib_complain` (
  `id` integer  NOT NULL PRIMARY KEY AUTOINCREMENT
,  `uid` integer  DEFAULT 0
,  `pid` integer  DEFAULT 0
,  `processed` text  DEFAULT '0'
,  `moderator` integer  DEFAULT 0
,  `text` varchar(255) DEFAULT NULL
,  `mod_comment` varchar(255) DEFAULT NULL
);
CREATE TABLE `ib_crontab` (
  `id` integer NOT NULL PRIMARY KEY AUTOINCREMENT
,  `library` varchar(24) NOT NULL
,  `proc` varchar(255) NOT NULL
,  `params` varchar(255) NOT NULL
,  `description` varchar(255) NOT NULL
,  `nextrun` integer  NOT NULL
,  `period` integer  NOT NULL
);
INSERT INTO `ib_crontab` VALUES (1,'antibot','captcha_clear','24','Очистка старых данных CAPTCHA',0,32768),(2,'maintain','log_rotate','5','Ротация логов в каталоге logs',0,1440),(3,'antibot','timeout_clear','24','Очистка старых данных о таймаутах',0,1441),(4,'maintain','search_results_clear','7','Очистка старых результатов поиска',0,1443),(5,'maintain','mod_logs_clear','90','Удаление старых данных о модераторских действиях',0,10079),(6,'maintain','light_optimize','','Малая оптимизация баз данных (только часто изменяемые таблицы)',0,4300),(7,'maintain','heavy_optimize','','Полная оптимизация базы данных (все таблицы)',0,44643),(8,'maintain','update_mark_all','90','Отметка прочитанными всех тем, которые обновились, но не были просмотрены в течение заданного количества дней',0,10081),(9,'delete','inactive_users_clear','30','Удаление пользователей, не активировавших свой профиль в течение указанного количества дней',0,4320),(10,'maintain','online_clear','3','Очистка списка последних действий пользователей',0,1440),(11,'instagram','getdata','вставьте свой token','Обновление списка фотографий из Instagram',0,60),(12,'sitemap','generate','','Генерация файла sitemap.xml',0,180),(13,'instagram','refresh','','Обновление access token для Instagram',0,10080);
CREATE TABLE `ib_file` (
  `fkey` char(16) NOT NULL DEFAULT ''
,  `oid` integer  NOT NULL
,  `is_main` text  NOT NULL DEFAULT '0'
,  `type` integer  NOT NULL
,  `filename` varchar(255) NOT NULL
,  `size` integer NOT NULL
,  `format` text  NOT NULL DEFAULT 'attach'
,  `extension` char(4) NOT NULL
,  `descr` text DEFAULT NULL
,  `exif` text DEFAULT NULL
,  `geo_latitude` float DEFAULT NULL
,  `geo_longtitude` float DEFAULT NULL
,  PRIMARY KEY (`fkey`)
);
CREATE TABLE `ib_forum` (
  `id` integer  NOT NULL PRIMARY KEY AUTOINCREMENT
,  `module` varchar(16) NOT NULL
,  `title` varchar(80) NOT NULL
,  `descr` varchar(255) NOT NULL DEFAULT ''
,  `hurl` varchar(255) NOT NULL
,  `category_id` integer  NOT NULL DEFAULT 0
,  `parent_id` integer  NOT NULL DEFAULT 0
,  `owner` integer  NOT NULL DEFAULT 0
,  `sortfield` integer  NOT NULL DEFAULT 0
,  `locked` text  NOT NULL DEFAULT '0'
,  `lastmod` integer  NOT NULL DEFAULT 0
,  `last_post_id` integer  NOT NULL DEFAULT 0
,  `template` varchar(32) NOT NULL DEFAULT ''
,  `template_override` text  NOT NULL DEFAULT '0'
,  `bcode` text  NOT NULL DEFAULT '1'
,  `max_smiles` integer  NOT NULL DEFAULT 16
,  `max_attach` integer NOT NULL DEFAULT 0
,  `attach_types` integer  NOT NULL DEFAULT 255
,  `topic_count` integer  NOT NULL DEFAULT 0
,  `post_count` integer  NOT NULL DEFAULT 0
,  `is_stats` text  NOT NULL DEFAULT '1'
,  `is_flood` text  NOT NULL DEFAULT '0'
,  `is_start` text  NOT NULL DEFAULT '1'
,  `icon_new` varchar(255) NOT NULL DEFAULT ''
,  `icon_nonew` varchar(255) NOT NULL DEFAULT ''
,  `sort_mode` text  NOT NULL DEFAULT 'DESC'
,  `sort_column` text  NOT NULL DEFAULT 'last_post_time'
,  `polls` text  NOT NULL DEFAULT '1'
,  `selfmod` integer NOT NULL DEFAULT 0
,  `sticky_post` text  NOT NULL DEFAULT '2'
,  `rate` text  NOT NULL DEFAULT '2'
,  `rate_value` float NOT NULL DEFAULT 0
,  `rate_flood` float NOT NULL DEFAULT 0
,  `tags` text  NOT NULL DEFAULT '0'
,  `webmention` text  NOT NULL DEFAULT '1'
,  `micropub` text  NOT NULL DEFAULT '0'
);
INSERT INTO `ib_forum` VALUES (1,'statpage','О проекте','Информация о нашем сайте','about',0,0,0,1,'0',0,0,'','0','1',16,0,255,0,0,'1','0','1','','','DESC','last_post_time','1',0,'2','2',0,0,'0','1','0');
CREATE TABLE `ib_forum_type` (
  `module` varchar(40) NOT NULL
,  `typename` varchar(255) NOT NULL
,  `has_rules` text  NOT NULL DEFAULT '1'
,  `has_foreword` text  NOT NULL DEFAULT '1'
,  `allow_mass` text  NOT NULL DEFAULT '1'
,  `allow_subforums` text  NOT NULL DEFAULT '0'
,  `allow_personal` text  NOT NULL DEFAULT '0'
,  `sortfield` integer  NOT NULL DEFAULT 1
,  `route` text DEFAULT NULL
,  `skip_sitemap` text  DEFAULT '0'
,  PRIMARY KEY (`module`)
);
INSERT INTO `ib_forum_type` VALUES ('anon','Анонимный форум','1','1','1','0','0',3,'^<<<hurl>>>/((\d+)\.htm)?$ anon.php?f=<<<id>>>&a=view_forum&page=$2
^<<<hurl>>>/((\w+)\.htm)?$ anon.php?f=<<<id>>>&a=$2
^<<<hurl>>>/([\w\-\d]+)/((\d+)\.htm)?$ anon.php?f=<<<id>>>&t=$1&a=view_topic&page=$3
^<<<hurl>>>/([\w\-\d]+)/(\w+)\.htm$ anon.php?f=<<<id>>>&t=$1&a=$2
^moderate/<<<hurl>>>/((\w+)/)?(\w+)\.htm$ moderate.php?f=<<<id>>>&a=$3&t=$2
','0'),('blog','Блог или новости','1','1','1','0','1',6,'^<<<hurl>>>/((\d+)\.htm)?$ blog.php?f=<<<id>>>&a=view_forum&page=$2
^<<<hurl>>>/((\w+)\.htm)?$ blog.php?f=<<<id>>>&a=$2
^<<<hurl>>>/([\w\-\d]+)/((\d+)\.htm)?$ blog.php?f=<<<id>>>&t=$1&a=view_topic&page=$3
^<<<hurl>>>/([\w\-\d]+)/(\w+)\.htm$ blog.php?f=<<<id>>>&t=$1&a=$2
^moderate/<<<hurl>>>/((\w+)/)?(\w+)\.htm$ moderate.php?f=<<<id>>>&a=$3&t=$2
','0'),('gallery','Фотогалерея','1','1','1','0','1',6,'^<<<hurl>>>/((\d+)\.htm)?$ gallery.php?f=<<<id>>>&a=view_forum&page=$2
^<<<hurl>>>/((\w+)\.htm)?$ gallery.php?f=<<<id>>>&a=$2
^<<<hurl>>>/([\w\-\d]+)/((\d+)\.htm)?$ gallery.php?f=<<<id>>>&t=$1&a=view_topic&page=$3
^<<<hurl>>>/([\w\-\d]+)/(\w+)\.htm$ gallery.php?f=<<<id>>>&t=$1&a=$2
^moderate/<<<hurl>>>/((\w+)/)?(\w+)\.htm$ moderate.php?f=<<<id>>>&a=$3&t=$2
','0'),('link','Ссылка на внешний ресурс','0','0','0','0','0',4,'^<<<hurl>>>/?$ link.php?f=<<<id>>>&a=view','1'),('micro','Микроблог','1','1','1','0','1',7,'^<<<hurl>>>/((\w+)\.htm)?$ micro.php?f=<<<id>>>&a=$2
^moderate/<<<hurl>>>/edit_foreword.htm$ moderate.php?f=<<<id>>>&a=edit_foreword
^moderate/<<<hurl>>>/((\w+)/)?(\w+)\.htm$ moderate.php?f=<<<id>>>&a=$3&t=$2
','1'),('statpage','Статическая страница','0','1','1','1','0',2,'^<<<hurl>>>/((\w+)\.htm)?$ statpage.php?f=<<<id>>>&a=$2
^moderate/<<<hurl>>>/edit_foreword.htm$ statpage.php?f=<<<id>>>&a=edit
','1'),('stdforum','Обычный форум','1','1','1','1','0',1,'^<<<hurl>>>/((\d+)\.htm)?$ stdforum.php?f=<<<id>>>&a=view_forum&page=$2
^<<<hurl>>>/((\w+)\.htm)?$ stdforum.php?f=<<<id>>>&a=$2
^<<<hurl>>>/([\w\-\d]+)/((\d+)\.htm)?$ stdforum.php?f=<<<id>>>&t=$1&a=view_topic&page=$3
^<<<hurl>>>/([\w\-\d]+)/(\w+)\.htm$ stdforum.php?f=<<<id>>>&t=$1&a=$2
^<<<hurl>>>/([\w\-\d]+)/(\w+)\.htm$ stdforum.php?f=<<<id>>>&t=$1&a=$2
^<<<hurl>>>/([\w\-\d]+)/post-(\d+)\.htm$ stdforum.php?f=<<<id>>>&t=$1&a=post&post=$2 
^moderate/<<<hurl>>>/(([\w\-\d]+)/)?(\w+)\.htm$ moderate.php?f=<<<id>>>&a=$3&t=$2','0');
CREATE TABLE `ib_group` (
  `level` integer NOT NULL
,  `name` varchar(32) NOT NULL
,  `special` text  NOT NULL DEFAULT '1'
,  `floodtime` integer  NOT NULL DEFAULT 60
,  `privmsg_hour` integer  NOT NULL DEFAULT 240
,  `max_attach` integer  NOT NULL
,  `min_posts` integer  NOT NULL DEFAULT 0
,  `custom_title` text  NOT NULL DEFAULT '0'
,  `admin` text  NOT NULL DEFAULT '0'
,  `team` text  NOT NULL DEFAULT '0'
,  `founder` text  NOT NULL DEFAULT '0'
,  `min_reg_time` integer  NOT NULL DEFAULT 0
,  `links_mode` text  NOT NULL DEFAULT 'allow'
,  PRIMARY KEY (`level`)
);
INSERT INTO `ib_group` VALUES (0,'Гость','1',90,0,2048,0,'0','0','0','0',0,'nofollow'),(50,'Сомнительный тип','1',90,3,0,0,'0','0','0','0',0,'none'),(100,'Новичок','0',30,6,1024,0,'0','0','0','0',0,'none'),(120,'Начинающий','0',30,10,1024,5,'0','0','0','0',2,'nofollow'),(140,'Участник','0',10,25,2048,25,'0','0','0','0',4,'nofollow'),(160,'Почетный участник','0',5,60,2048,100,'1','0','0','0',7,'allow'),(180,'Долгожитель форума','0',3,240,4096,500,'1','0','0','0',30,'allow'),(499,'Участник команды','1',0,0,4096,0,'1','0','1','0',0,'allow'),(500,'Модератор','1',0,0,4096,0,'1','0','1','0',0,'allow'),(1000,'Администратор','1',0,0,65535,0,'1','1','1','1',0,'allow'),(1024,'Создатель форума','1',0,0,65535,0,'1','1','1','1',0,'allow');
CREATE TABLE `ib_last_visit` (
  `oid` integer NOT NULL
,  `type` text  NOT NULL
,  `uid` integer NOT NULL
,  `visit1` integer NOT NULL
,  `visit2` integer DEFAULT NULL
,  `bookmark` text  NOT NULL DEFAULT '0'
,  `subscribe` integer NOT NULL DEFAULT 0
,  `lastmail` integer DEFAULT NULL
,  `posted` text  NOT NULL DEFAULT '0'
,  PRIMARY KEY (`oid`,`uid`,`type`)
);
CREATE TABLE `ib_log_action` (
  `id` integer  NOT NULL PRIMARY KEY AUTOINCREMENT
,  `pid` integer  NOT NULL DEFAULT 0
,  `tid` integer  NOT NULL DEFAULT 0
,  `fid` integer  NOT NULL DEFAULT 0
,  `type` integer  NOT NULL
,  `time` integer  NOT NULL
,  `uid` integer  NOT NULL
,  `data` text NOT NULL
);
CREATE TABLE `ib_mark_all` (
  `uid` integer  NOT NULL
,  `fid` integer  NOT NULL
,  `mark_time` integer  NOT NULL
,  PRIMARY KEY (`uid`,`fid`)
);
CREATE TABLE `ib_menu` (
  `id` integer  NOT NULL PRIMARY KEY AUTOINCREMENT
,  `descr` varchar(255) NOT NULL
,  `locked` text  NOT NULL
);
INSERT INTO `ib_menu` VALUES (1,'Главное меню','1'),(2,'Меню Центра Администрирования','1');
CREATE TABLE `ib_menu_item` (
  `id` integer  NOT NULL PRIMARY KEY AUTOINCREMENT
,  `mid` integer  NOT NULL
,  `title` varchar(255) NOT NULL
,  `url` varchar(255) NOT NULL
,  `sortfield` integer NOT NULL
,  `show_guests` text  NOT NULL DEFAULT '0'
,  `show_users` text  NOT NULL DEFAULT '0'
,  `show_admins` text  NOT NULL DEFAULT '0'
,  `hurl_mode` text  NOT NULL DEFAULT '0'
);
INSERT INTO `ib_menu_item` VALUES (1,1,'О проекте','about/',1,'1','1','1','1'),(2,1,'Правила','rules.htm',2,'1','1','1','1'),(3,1,'Участники','users/',5,'1','1','1','1'),(4,1,'Команда','team.htm',4,'1','1','1','1'),(5,1,'Последние сообщения','newtopics/',3,'1','1','1','1'),(6,1,'Поиск','search/',7,'1','1','1','1'),(7,1,'Справка','help/',8,'1','1','1','1'),(8,1,'Сейчас присутствуют','online/',6,'1','1','1','1');
CREATE TABLE `ib_moderator` (
  `fid` integer NOT NULL
,  `uid` integer NOT NULL
,  `role` text  NOT NULL
,  PRIMARY KEY (`fid`,`uid`,`role`)
);
CREATE TABLE `ib_oauth_code` (
  `code` char(64) NOT NULL
,  `uid` integer  NOT NULL
,  `client_id` varchar(255) NOT NULL
,  `redirect_uri` varchar(255) NOT NULL
,  `me` varchar(255) NOT NULL
,  `scope` varchar(255) DEFAULT NULL
,  `expires` integer  NOT NULL
,  PRIMARY KEY (`code`)
);
CREATE TABLE `ib_oauth_token` (
  `token` char(128) NOT NULL
,  `uid` integer  NOT NULL
,  `client_id` varchar(255) NOT NULL
,  `scope` varchar(255) DEFAULT NULL
,  `expires` integer  NOT NULL
);
CREATE TABLE `ib_online` (
  `uid` integer  NOT NULL
,  `hash` char(32) NOT NULL
,  `visittime` integer  NOT NULL
,  `type` integer NOT NULL
,  `fid` integer  NOT NULL
,  `tid` integer  NOT NULL
,  `text` varchar(255) NOT NULL
,  `ip` varchar(255) NOT NULL
,  PRIMARY KEY (`hash`,`uid`)
);
CREATE TABLE `ib_poll` (
  `id` integer  NOT NULL
,  `question` varchar(255) NOT NULL
,  `endtime` integer  NOT NULL
,  `mode` text  NOT NULL DEFAULT '0'
,  `max_variants` integer NOT NULL DEFAULT 1
,  PRIMARY KEY (`id`)
);
CREATE TABLE `ib_poll_variant` (
  `id` integer  NOT NULL PRIMARY KEY AUTOINCREMENT
,  `tid` integer  NOT NULL
,  `text` varchar(80) NOT NULL
,  `count` integer  NOT NULL DEFAULT 0
);
CREATE TABLE `ib_post` (
  `id` integer  NOT NULL PRIMARY KEY AUTOINCREMENT
,  `tid` integer  NOT NULL
,  `uid` integer  NOT NULL DEFAULT 1
,  `author` varchar(64) NOT NULL DEFAULT 'Guest'
,  `postdate` integer  NOT NULL DEFAULT 0
,  `editcount` integer  NOT NULL DEFAULT 0
,  `editor_id` integer  NOT NULL DEFAULT 0
,  `value` text  NOT NULL DEFAULT '0'
,  `status` text  NOT NULL DEFAULT '0'
,  `locked` text  NOT NULL DEFAULT '0'
,  `html` text  NOT NULL DEFAULT '0'
,  `bcode` text  NOT NULL DEFAULT '1'
,  `smiles` text  NOT NULL DEFAULT '1'
,  `links` text  NOT NULL DEFAULT '1'
,  `typograf` text  NOT NULL DEFAULT '1'
,  `rating` float NOT NULL DEFAULT 0
,  `ip` varchar(255) NOT NULL
,  `email` varchar(255) NOT NULL DEFAULT ''
);
CREATE TABLE `ib_privmsg_link` (
  `pm_id` integer NOT NULL
,  `uid` integer NOT NULL
,  PRIMARY KEY (`uid`,`pm_id`)
);
CREATE TABLE `ib_privmsg_post` (
  `id` integer NOT NULL PRIMARY KEY AUTOINCREMENT
,  `pm_thread` integer  NOT NULL
,  `subscribers` integer  DEFAULT 1
,  `uid` integer  NOT NULL
,  `text` text NOT NULL
,  `postdate` integer  NOT NULL
,  `html` text  NOT NULL DEFAULT '0'
,  `bcode` text  NOT NULL DEFAULT '1'
,  `smiles` text  NOT NULL DEFAULT '1'
,  `links` text  NOT NULL DEFAULT '1'
,  `typograf` text  NOT NULL DEFAULT '1'
);
CREATE TABLE `ib_privmsg_thread` (
  `id` integer  NOT NULL PRIMARY KEY AUTOINCREMENT
,  `title` varchar(255) NOT NULL
);
CREATE TABLE `ib_privmsg_thread_user` (
  `pm_thread` integer  NOT NULL
,  `uid` integer  NOT NULL
,  `total` integer  NOT NULL DEFAULT 0
,  `unread` integer  NOT NULL DEFAULT 0
,  `last_post_date` integer  NOT NULL DEFAULT 0
,  PRIMARY KEY (`uid`,`pm_thread`)
);
CREATE TABLE `ib_rating` (
  `id` integer  NOT NULL
,  `uid` integer  NOT NULL
,  `value` float NOT NULL
,  `time` integer  NOT NULL
,  `ip` varchar(255) NOT NULL
,  PRIMARY KEY (`id`,`uid`)
);
CREATE TABLE `ib_relation` (
  `from_` integer  NOT NULL PRIMARY KEY AUTOINCREMENT
,  `to_` integer  NOT NULL
,  `type` text  NOT NULL
);
CREATE TABLE `ib_search` (
  `id` integer  NOT NULL PRIMARY KEY AUTOINCREMENT
,  `owner` integer  DEFAULT 0
,  `output_mode` text  DEFAULT 'posts'
,  `search_type` integer DEFAULT 1
,  `query` varchar(255) DEFAULT '1'
,  `time` integer  DEFAULT NULL
,  `extdata` text DEFAULT NULL
);
CREATE TABLE `ib_search_result` (
  `sid` integer  NOT NULL
,  `oid` integer  NOT NULL
,  `relevancy` float  DEFAULT 0
,  PRIMARY KEY (`oid`,`sid`)
);
CREATE TABLE `ib_smile` (
  `code` varchar(16) NOT NULL
,  `file` varchar(255) NOT NULL
,  `descr` varchar(255) NOT NULL
,  `mode` text  NOT NULL DEFAULT 'dropdown'
,  `sortfield` integer  NOT NULL DEFAULT 0
,  PRIMARY KEY (`code`)
);
INSERT INTO `ib_smile` VALUES ('8-)','cool.png','','dropdown',4),(':''(','cwy.png','','dropdown',5),(':(','sad.png','','dropdown',9),(':)','smile.png','','dropdown',1),(':alien:','alien.png','','more',100),(':angel:','angel.png','','dropdown',2),(':angry:','angry.png','','dropdown',3),(':blink:','blink.png','','more',101),(':blush:','blush.png','','more',102),(':cheerful:','cheerful.png','','more',103),(':D','grin.png','','dropdown',7),(':devil:','devil.png','','more',104),(':dizzy:','dizzy.png','','more',105),(':ermm:','ermm.png','','dropdown',6),(':face:','face.png','','more',119),(':getlost:','getlost.png','','more',106),(':happy:','happy.png','','more',107),(':kissing:','kissing.png','','more',108),(':laughing:','laughing.png','','more',120),(':love:','wub.png','','hidden',501),(':ninja:','ninja.png','','more',109),(':O','shocked.png','','dropdown',10),(':P','tongue.png','','dropdown',11),(':pinch:','pinch.png','','more',110),(':pouty:','pouty.png','','more',111),(':sick:','sick.png','','more',112),(':sideways:','sideways.png','','more',113),(':silly:','silly.png','','more',114),(':sleeping:','sleeping.png','','more',115),(':unsure:','unsure.png','','more',116),(':wassat:','wassat.png','','more',118),(':whistling:','whistling.png','','hidden',500),(':woot:','w00t.png','','more',117),(';)','wink.png','','dropdown',12),('<3','heart.png','','dropdown',8);
CREATE TABLE `ib_subaction` (
  `id` integer  NOT NULL PRIMARY KEY AUTOINCREMENT
,  `name` varchar(128) NOT NULL
,  `module` varchar(32) NOT NULL
,  `action` varchar(32) NOT NULL
,  `fid` integer NOT NULL DEFAULT 0
,  `tid` integer NOT NULL DEFAULT 0
,  `library` varchar(32) NOT NULL
,  `proc` varchar(32) NOT NULL
,  `block` varchar(32) NOT NULL
,  `active` text  NOT NULL DEFAULT '1'
,  `params` varchar(255) NOT NULL
,  `priority` integer NOT NULL
);
INSERT INTO `ib_subaction` VALUES (1,'Блок тегов на обычном форуме','stdforum','view_forum',0,0,'blocks','block_tag_list','action_start','0','20',1),(2,'Блок «Сейчас присутствуют» на главной','mainpage','view',0,0,'online','get_online_users','page_bottom','1','2',10),(3,'Блок «Сейчас присутствуют» в разделах','*','view_forum',0,0,'online','get_online_users','page_bottom','0','2',10),(4,'Блок «Сейчас присутствуют» в темах','*','view_topic',0,0,'online','get_online_users','page_bottom','0','2',10),(5,'Блок объявлений','*','*',0,0,'blocks','block_announce','welcome_start','1','1',1),(6,'Блок с количеством личных сообщений','*','*',0,0,'blocks','block_pm_unread','pm_notify','1','',1),(7,'Блок фотографий из Instagram','statpage','view',0,0,'instagram','block_instagram','page_bottom','0','4,Добавьте свой Instagram token',20);
CREATE TABLE `ib_tagentry` (
  `tag_id` integer NOT NULL
,  `item_id` integer NOT NULL
,  PRIMARY KEY (`tag_id`,`item_id`)
);
CREATE TABLE `ib_tagname` (
  `id` integer NOT NULL PRIMARY KEY AUTOINCREMENT
,  `type` integer  NOT NULL
,  `tagname` varchar(32) NOT NULL
,  `count` integer NOT NULL DEFAULT 0
,  UNIQUE (`tagname`,`type`)
);
CREATE TABLE `ib_task` (
  `id` integer NOT NULL PRIMARY KEY AUTOINCREMENT
,  `library` varchar(24) NOT NULL
,  `proc` varchar(255) NOT NULL
,  `params` text NOT NULL
,  `nextrun` integer  NOT NULL
,  `errors` integer NOT NULL DEFAULT 0
);
CREATE TABLE `ib_text` (
  `id` integer  NOT NULL
,  `type` integer  NOT NULL
,  `data` mediumtext NOT NULL
,  `tx_lastmod` integer  NOT NULL DEFAULT 0
,  PRIMARY KEY (`id`,`type`)
);
INSERT INTO `ib_text` VALUES (0,0,'Правила форума разрабатываются. А пока просим придерживаться общих принципов вежливости и доброжелательности.',0),(1,2,'Если вы читаете этот текст, то установка Intellect Board прошла успешно. 
В дальнейшем его можно будет заменить на информацию о вашем проекте или просто удалить.
Этот раздел имеет тип "Статическая страница". Обычный раздел с темами и соощениями вы можете 
создать в Центре Администрирования.',0);
CREATE TABLE `ib_timeout` (
  `time` integer NOT NULL
,  `action` varchar(32) NOT NULL
,  `uid` integer NOT NULL
,  `ip` integer NOT NULL
);
CREATE TABLE `ib_topic` (
  `id` integer  NOT NULL PRIMARY KEY AUTOINCREMENT
,  `fid` integer  NOT NULL
,  `title` varchar(80) NOT NULL
,  `descr` varchar(255) NOT NULL
,  `status` text  NOT NULL DEFAULT '0'
,  `hurl` varchar(255) NOT NULL DEFAULT ''
,  `locked` text  NOT NULL DEFAULT '0'
,  `first_post_id` integer  NOT NULL DEFAULT 0
,  `last_post_id` integer  NOT NULL DEFAULT 0
,  `lastmod` integer  NOT NULL
,  `post_count` integer  NOT NULL DEFAULT 0
,  `flood_count` integer  NOT NULL DEFAULT 0
,  `valued_count` integer  NOT NULL DEFAULT 0
,  `owner` integer  NOT NULL DEFAULT 0
,  `sticky` text  NOT NULL DEFAULT '0'
,  `sticky_post` text  NOT NULL DEFAULT '0'
,  `favorites` text  NOT NULL DEFAULT '0'
,  `ext_status` integer  NOT NULL DEFAULT 0
,  `last_post_time` integer  NOT NULL DEFAULT 0
,  `rating` integer  NOT NULL DEFAULT 0
);
CREATE TABLE `ib_user` (
  `id` integer  NOT NULL PRIMARY KEY AUTOINCREMENT
,  `login` varchar(32) NOT NULL
,  `password` varchar(255) NOT NULL
,  `pass_crypt` integer  NOT NULL
,  `title` varchar(80) NOT NULL DEFAULT ''
,  `gender` text  NOT NULL DEFAULT 'U'
,  `birthdate` date DEFAULT NULL
,  `location` varchar(80) NOT NULL
,  `status` integer  NOT NULL DEFAULT 0
,  `canonical` varchar(255) NOT NULL
,  `signature` varchar(255) NOT NULL
,  `rnd` integer  NOT NULL
,  `display_name` varchar(32) NOT NULL
,  `avatar` text  NOT NULL DEFAULT 'none'
,  `photo` text  NOT NULL DEFAULT 'none'
,  `email` varchar(255) NOT NULL
,  `real_name` varchar(255) NOT NULL DEFAULT ''
,  UNIQUE (`login`)
,  UNIQUE (`display_name`)
);
INSERT INTO `ib_user` VALUES (1,'Guest','*',1,'','U',NULL,' ',0,'guest','',111,'Гость','none','none','null@intbpro.ru',''),(2,'System','*',1,'','U',NULL,'',0,'system','',222,'System','none','none','null@intbpro.ru',''),(3,'New User','*',5,'','U',NULL,'',0,'NewUser','',333,'New User','none','none','null2@intbpro.ru','');
CREATE TABLE `ib_user_award` (
  `id` integer  NOT NULL PRIMARY KEY AUTOINCREMENT
,  `uid` integer  NOT NULL
,  `file` varchar(255) NOT NULL
,  `descr` varchar(255) NOT NULL
,  `time` integer  NOT NULL
);
CREATE TABLE `ib_user_contact` (
  `uid` integer NOT NULL
,  `cid` integer NOT NULL
,  `value` varchar(80) NOT NULL
,  PRIMARY KEY (`uid`,`cid`)
);
CREATE TABLE `ib_user_contact_type` (
  `cid` integer  NOT NULL PRIMARY KEY AUTOINCREMENT
,  `c_title` varchar(80) NOT NULL
,  `icon` varchar(255) NOT NULL
,  `link` varchar(255) NOT NULL
,  `c_sort` integer NOT NULL
,  `c_name` varchar(32) NOT NULL
,  `c_permission` text  NOT NULL DEFAULT '0'
);
INSERT INTO `ib_user_contact_type` VALUES (2,'Skype','icons/c/skype.gif','skype:%s',50,'','0'),(3,'ВКонтакте','icons/c/vk.gif','http://vk.com/%s',30,'vkontakte','0'),(4,'ICQ','icons/c/icq.gif','',80,'','0'),(5,'Jabber/XMPP','icons/c/jabber.gif','xmpp:%s',100,'','0'),(6,'МойМир@Mail.Ru','icons/c/agent.gif','http://my.mail.ru/%s',60,'mailru','0'),(7,'LiveJournal','icons/c/lj.gif','http://%s.livejournal.com',70,'livejournal','0'),(8,'Telegram','icons/c/telegram.png','https://t-do.ru/%s',20,'telegram','0'),(9,'GTalk/GMail','icons/c/gtalk.gif','mailto:%s@gmail.com',40,'google','0'),(10,'Одноклассники','icons/c/odno.gif','http://www.odnoklassniki.ru/profile/%s',35,'odnoklassniki','0'),(11,'Facebook','icons/c/facebook.gif','https://www.facebook.com/profile.php?id=%s',37,'facebook','0'),(12,'Twitter','icons/c/twitter.gif','http://twitter.com/%s',90,'twitter','0'),(13,'Webmoney ID','icons/c/webmoney.gif','https://passport.webmoney.ru/asp/CertView.asp?wmid=%s',120,'webmoney','0'),(14,'OpenID','icons/c/openid.gif','%s',110,'openid','0'),(15,'Личный сайт','','%s',100,'','1'),(16,'Личный блог','','%s',100,'','1');
CREATE TABLE `ib_user_ext` (
  `id` integer  NOT NULL
,  `post_count` integer  NOT NULL DEFAULT 0
,  `rating` float NOT NULL DEFAULT 0
,  `warnings` integer NOT NULL DEFAULT 0
,  `balance` decimal(10,0) NOT NULL DEFAULT 0
,  `banned_till` integer  NOT NULL DEFAULT 0
,  `group_id` integer NOT NULL DEFAULT 0
,  `reg_date` integer  NOT NULL DEFAULT 0
,  `reg_ip` varchar(255) NOT NULL DEFAULT '0'
,  PRIMARY KEY (`id`)
);
INSERT INTO `ib_user_ext` VALUES (1,10,0,0,0,0,0,1411401372,'0'),(2,0,0,0,0,0,0,1411401372,'0'),(3,0,0,0,0,0,100,1411401372,'0');
CREATE TABLE `ib_user_field` (
  `id` integer  NOT NULL PRIMARY KEY AUTOINCREMENT
,  `title` varchar(60) NOT NULL
,  `type` text  NOT NULL DEFAULT 'text'
,  `values` text NOT NULL
,  `in_msg` text  NOT NULL DEFAULT '1'
,  `sortfield` integer  NOT NULL
);
CREATE TABLE `ib_user_settings` (
  `id` integer  NOT NULL
,  `topics_per_page` integer  NOT NULL DEFAULT 10
,  `posts_per_page` integer  NOT NULL DEFAULT 20
,  `template` varchar(20) NOT NULL DEFAULT ''
,  `msg_order` text  NOT NULL DEFAULT 'ASC'
,  `subscribe` text  NOT NULL DEFAULT 'None'
,  `timezone` integer NOT NULL DEFAULT 10800
,  `signatures` text  NOT NULL DEFAULT '1'
,  `avatars` text  NOT NULL DEFAULT '1'
,  `smiles` text  NOT NULL DEFAULT '1'
,  `pics` text  NOT NULL DEFAULT '1'
,  `longposts` text  NOT NULL DEFAULT '0'
,  `show_birthdate` text  NOT NULL DEFAULT '3'
,  `subscribe_mode` integer  NOT NULL DEFAULT 0
,  `email_fulltext` text  NOT NULL DEFAULT '1'
,  `email_pm` text  NOT NULL DEFAULT '1'
,  `email_message` text  NOT NULL DEFAULT '1'
,  `email_broadcasts` text  NOT NULL DEFAULT '1'
,  `flood_limit` integer  NOT NULL DEFAULT 50
,  `topics_period` integer  NOT NULL DEFAULT 0
,  `hidden` text  NOT NULL DEFAULT '0'
,  `wysiwyg` text  NOT NULL DEFAULT '1'
,  `goto` text  NOT NULL DEFAULT '0'
,  PRIMARY KEY (`id`)
);
INSERT INTO `ib_user_settings` VALUES (1,0,0,'','ASC','None',10800,'1','1','1','1','0','0',1,'1','1','1','1',50,0,'0','1','0'),(3,15,20,'','ASC','My',10800,'1','1','1','1','0','0',1,'1','1','1','1',50,0,'0','2','0');
CREATE TABLE `ib_user_value` (
  `uid` integer  NOT NULL
,  `fdid` integer  NOT NULL
,  `value` varchar(255) NOT NULL
,  PRIMARY KEY (`uid`,`fdid`)
);
CREATE TABLE `ib_user_warning` (
  `id` integer  NOT NULL PRIMARY KEY AUTOINCREMENT
,  `uid` integer  NOT NULL DEFAULT 0
,  `warntime` integer  NOT NULL DEFAULT 0
,  `moderator` integer  NOT NULL DEFAULT 0
,  `pid` integer  NOT NULL DEFAULT 0
,  `value` integer  NOT NULL DEFAULT 0
,  `warntill` integer  NOT NULL DEFAULT 0
,  `descr` text NOT NULL
);
CREATE TABLE `ib_views` (
  `oid` integer  NOT NULL
,  `type` text  NOT NULL DEFAULT 'topic'
,  `views` integer  NOT NULL DEFAULT 0
,  PRIMARY KEY (`oid`,`type`)
);
CREATE TABLE `ib_vote` (
  `tid` integer  NOT NULL PRIMARY KEY AUTOINCREMENT
,  `uid` integer  NOT NULL
,  `pvid` integer  NOT NULL
,  `time` integer  NOT NULL
,  `ip` varchar(255) NOT NULL
);
CREATE INDEX "idx_ib_post_topic" ON "ib_post" (`tid`,`postdate`);
CREATE INDEX "idx_ib_post_author_uid" ON "ib_post" (`uid`);
CREATE INDEX "idx_ib_user_award_uid" ON "ib_user_award" (`uid`);
CREATE INDEX "idx_ib_timeout_time" ON "ib_timeout" (`time`,`action`,`uid`);
CREATE INDEX "idx_ib_online_uid" ON "ib_online" (`uid`);
CREATE INDEX "idx_ib_text_search" ON "ib_text" (`data`);
CREATE INDEX "idx_ib_menu_item_mid" ON "ib_menu_item" (`mid`,`sortfield`);
CREATE INDEX "idx_ib_forum_sortkey" ON "ib_forum" (`sortfield`);
CREATE INDEX "idx_ib_forum_hurl" ON "ib_forum" (`hurl`);
CREATE INDEX "idx_ib_captcha_lastmod" ON "ib_captcha" (`lastmod`);
CREATE INDEX "idx_ib_user_ext_user_group" ON "ib_user_ext" (`group_id`);
CREATE INDEX "idx_ib_crontab_nextrun" ON "ib_crontab" (`nextrun`,`period`);
CREATE INDEX "idx_ib_user_location" ON "ib_user" (`location`);
CREATE INDEX "idx_ib_topic_forum" ON "ib_topic" (`fid`,`last_post_id`);
CREATE INDEX "idx_ib_topic_Fulltext_title" ON "ib_topic" (`title`,`descr`);
CREATE INDEX "idx_ib_oauth_code_uid" ON "ib_oauth_code" (`uid`);
CREATE INDEX "idx_ib_subaction_intb_subaction_module_IDX" ON "ib_subaction" (`module`,`action`);
CREATE INDEX "idx_ib_search_result_relevancy" ON "ib_search_result" (`sid`,`relevancy`);
CREATE INDEX "idx_ib_privmsg_post_thread" ON "ib_privmsg_post" (`pm_thread`,`postdate`);
CREATE INDEX "idx_ib_poll_variant_poll_tid" ON "ib_poll_variant" (`tid`);
CREATE INDEX "idx_ib_file_oid" ON "ib_file" (`oid`,`type`);

END TRANSACTION;
