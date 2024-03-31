DROP TABLE IF EXISTS `ib_subactions`;
CREATE TABLE IF NOT EXISTS `ib_subaction` (id smallint(5) unsigned NOT NULL auto_increment, name varchar(128) NOT NULL comment 'Название блока, видимое администратору',  module varchar(32) NOT NULL,  action varchar(32) NOT NULL,  fid smallint(6) NOT NULL DEFAULT 0 comment 'Id раздела, в котором выводится блок, 0 -- во всех',  tid smallint(6) NOT NULL DEFAULT 0 comment 'Id темы, в которой выводится блок, 0 -- во всех',  library varchar(32) NOT NULL,  proc varchar(32) NOT NULL,  block varchar(32) NOT NULL,  active enum('0', '1') NOT NULL DEFAULT '1',  params varchar(255) NOT NULL,  priority smallint(6) NOT NULL,  INDEX intb_subaction_module_IDX (module, action),  PRIMARY KEY (id)) ENGINE=InnoDB;

ALTER TABLE `ib_online` CHANGE COLUMN ip ip varchar(255) NOT NULL comment 'IP пользователя (подумать, нужен ли вообще)';
ALTER TABLE `ib_post` CHANGE COLUMN author author varchar(64) NOT NULL DEFAULT 'Guest' comment 'Автор сообщения (NULL -- если зарегистрированный пользователь)';
ALTER TABLE `ib_post` CHANGE COLUMN ip ip varchar(255) NOT NULL comment 'IP, с которого было отправлено сообщение';
ALTER TABLE `ib_tagname` CHANGE COLUMN type type tinyint(3) unsigned NOT NULL;
ALTER TABLE `ib_tagname` CHANGE COLUMN count count mediumint(9) NOT NULL DEFAULT 0;
ALTER TABLE `ib_timeout` CHANGE COLUMN action action varchar(32) NOT NULL comment 'Условное название действия';
ALTER TABLE `ib_user` CHANGE COLUMN login login varchar(32) NOT NULL;
ALTER TABLE `ib_user` CHANGE COLUMN display_name display_name varchar(32) NOT NULL;
ALTER TABLE `ib_user_ext` CHANGE COLUMN reg_ip reg_ip varchar(255) NOT NULL DEFAULT '0' comment 'IP, с которого произведена регистрация';
ALTER TABLE `ib_vote` CHANGE COLUMN ip ip varchar(255) NOT NULL comment 'IP, с которого производилось голосование';
ALTER TABLE `ib_user` DROP INDEX login;
ALTER TABLE `ib_user` DROP INDEX display_name;
ALTER TABLE `ib_user` ADD UNIQUE login (login);
ALTER TABLE `ib_user` ADD UNIQUE display_name (display_name);

INSERT IGNORE INTO `ib_forum_type` (`module`, `typename`, `has_rules`, `has_foreword`, `allow_mass`, `allow_subforums`, `allow_personal`, `sortfield`, `htaccess`, `route`) VALUES ('blog',	'Блог или новости',	'1',	'1',	'1',	'0',	'1',	6,	'### Раздел <<<title>>> (тип blog)\nRewriteRule ^<<<hurl>>>/((\\d+)\\.htm)?$ blog.php?f=<<<id>>>&a=view_forum&page=$2 [L,QSA]\nRewriteRule ^<<<hurl>>>/((\\w+)\\.htm)?$ blog.php?f=<<<id>>>&a=$2 [L,QSA]\nRewriteRule ^<<<hurl>>>/([\\w\\-\\d]+)/((\\d+)\\.htm)?$ blog.php?f=<<<id>>>&t=$1&a=view_topic&page=$3 [L,QSA]\nRewriteRule ^<<<hurl>>>/([\\w\\-\\d]+)/(\\w+)\\.htm$ blog.php?f=<<<id>>>&t=$1&a=$2 [L,QSA]\nRewriteRule ^<<<hurl>>>/([\\w\\-\\d]+)/(\\w+)\\.htm$ blog.php?f=<<<id>>>&t=$1&a=$2 [L,QSA]\nRewriteRule ^<<<hurl>>>/([\\w\\-\\d]+)/post-(\\d+)\\.htm$ blog.php?f=<<<id>>>&t=$1&a=post&post=$2 [L,QSA]\nRewriteRule ^moderate/<<<hurl>>>/((\\w+)/)?(\\w+)\\.htm$ moderate.php?f=<<<id>>>&a=$3&t=$2 [L,QSA]\n',	'^<<<hurl>>>/((\\d+)\\.htm)?$ blog.php?f=<<<id>>>&a=view_forum&page=$2\n^<<<hurl>>>/((\\w+)\\.htm)?$ blog.php?f=<<<id>>>&a=$2\n^<<<hurl>>>/([\\w\\-\\d]+)/((\\d+)\\.htm)?$ blog.php?f=<<<id>>>&t=$1&a=view_topic&page=$3\n^<<<hurl>>>/([\\w\\-\\d]+)/(\\w+)\\.htm$ blog.php?f=<<<id>>>&t=$1&a=$2\n^moderate/<<<hurl>>>/((\\w+)/)?(\\w+)\\.htm$ moderate.php?f=<<<id>>>&a=$3&t=$2\n'), ('micro',	'Микроблог',	'1',	'1',	'1',	'0',	'1',	7,	'### Раздел <<<title>>> (тип micro)\nRewriteRule ^<<<hurl>>>/((\\w+)\\.htm)?$ micro.php?f=<<<id>>>&a=$2 [L,QSA]\nRewriteRule ^<<<hurl>>>/(\\w+)/((\\d+)\\.htm)?$ <<<hurl>>>/ [L,R=302]\nRewriteRule ^moderate/<<<hurl>>>/edit_foreword.htm$ moderate.php?f=<<<id>>>&a=edit_foreword [L,QSA]\nRewriteRule ^moderate/<<<hurl>>>/((\\w+)/)?(\\w+)\\.htm$ moderate.php?f=<<<id>>>&a=$3&t=$2 [L,QSA]\n',	'^<<<hurl>>>/((\\w+)\\.htm)?$ micro.php?f=<<<id>>>&a=$2\n^moderate/<<<hurl>>>/edit_foreword.htm$ moderate.php?f=<<<id>>>&a=edit_foreword\n^moderate/<<<hurl>>>/((\\w+)/)?(\\w+)\\.htm$ moderate.php?f=<<<id>>>&a=$3&t=$2\n');
INSERT INTO `ib_crontab` (id, library, proc, params, description, nextrun, period) VALUES(NULL, 'instagram', 'getdata', 'вставьте Instagram-токен здесь', 'Обновление списка фотографий из Instagram', 0, 3600);
INSERT INTO `ib_crontab` (id, library, proc, params, description, nextrun, period) VALUES(NULL, 'sitemap', 'generate', '', 'Генерация файла sitemap.xml', 0, 3600);

INSERT INTO `ib_subaction` (name,module,`action`,fid,tid,library,proc,block,active,params,priority) VALUES ('Блок тегов на обычном форуме','stdforum','view_forum',0,0,'blocks','block_tag_list','action_start','1','20',1);
INSERT INTO `ib_subaction` (name,module,`action`,fid,tid,library,proc,block,active,params,priority) VALUES ('Блок «Сейчас присутствуют» на главной','mainpage','view',0,0,'online','get_online_users','page_bottom','1','2',10);
INSERT INTO `ib_subaction` (name,module,`action`,fid,tid,library,proc,block,active,params,priority) VALUES ('Блок «Сейчас присутствуют» в разделах','*','view_forum',0,0,'online','get_online_users','page_bottom','0','2',10);
INSERT INTO `ib_subaction` (name,module,`action`,fid,tid,library,proc,block,active,params,priority) VALUES ('Блок «Сейчас присутствуют» в темах','*','view_topic',0,0,'online','get_online_users','page_bottom','0','2',10);
INSERT INTO `ib_subaction` (name,module,`action`,fid,tid,library,proc,block,active,params,priority) VALUES ('Блок объявлений','*','*',0,0,'blocks','block_announce','welcome_start','1','1',1);
INSERT INTO `ib_subaction` (name,module,`action`,fid,tid,library,proc,block,active,params,priority) VALUES ('Блок с количеством личных сообщений','*','*',0,0,'blocks','block_pm_unread','pm_notify','0','',1);

UPDATE `ib_post` SET ip=INET_NTOA(ip);
UPDATE `ib_user_ext` SET reg_ip=INET_NTOA(reg_ip);
UPDATE `ib_online` SET ip=INET_NTOA(ip);