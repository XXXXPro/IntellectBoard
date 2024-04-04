ALTER TABLE `ib_online` DROP PRIMARY KEY, ADD PRIMARY KEY ("hash","uid");

ALTER TABLE `ib_file` ADD is_main enum('0','1') NOT NULL DEFAULT '0' COMMENT 'Является ли приложенный файл главным (обложкой альбома)';
ALTER TABLE `ib_file` ADD descr TEXT NULL COMMENT 'Описание (подзаголовок) фотографии';
ALTER TABLE `ib_file` ADD exif TEXT NULL COMMENT 'Данные EXIF оригинала фотографии';
ALTER TABLE `ib_file` ADD geo_latitude FLOAT(9) NULL COMMENT 'Широта для геометок фото';
ALTER TABLE `ib_file` ADD geo_longtitude FLOAT(10) NULL COMMENT 'Долгота для геометок фото';

UPDATE `ib_file` SET is_main='1' WHERE fkey IN (SELECT fkey FROM (SELECT ROW_NUMBER() OVER (PARTITION BY oid) AS num, fkey FROM `ib_file`) AS tmpt WHERE tmpt.num=1);
ALTER TABLE `ib_forum_type` DROP COLUMN htaccess;
ALTER TABLE `ib_forum_type` ADD skip_sitemap enum('1','0') DEFAULT '0' COMMENT 'Если поле выставлено в 1, темы из данного вида разделов не будут включаться в sitemap.xml';

ALTER TABLE `ib_forum` MODIFY COLUMN selfmod TINYINT DEFAULT '0' NOT NULL COMMENT 'Режим кураторства: 0 - нет кураторов, 1 -- самомодерация (куратором сразу становится создатель темы), 2 -- кураторы вручную назначаются модераторами';
ALTER TABLE `ib_forum` ADD webmention enum('0','1','2') NOT NULL DEFAULT '1' COMMENT 'Использование WebMention: 0 - отключено, 1 - разрешено без премодерации, 2 - разрешено с премодерацией';
ALTER TABLE `ib_forum` ADD micropub enum('0','1','2') NOT NULL DEFAULT '0' COMMENT 'Использование Micropub и Microsub';
UPDATE `ib_forum` SET selfmod=selfmod-1 WHERE selfmod>0;
UPDATE `ib_topic` SET owner=0 WHERE fid IN (SELECT id FROM `ib_forum` WHERE selfmod!=1);

INSERT INTO `ib_forum_type` (module, typename, has_rules, has_foreword, allow_mass, allow_subforums, allow_personal, sortfield, route, skip_sitemap) VALUES('gallery', 'Фотогалерея', '1', '1', '1', '0', '1', 6, '^<<<hurl>>>/((\\d+)\\.htm)?$ gallery.php?f=<<<id>>>&a=view_forum&page=$2\n^<<<hurl>>>/((\\w+)\\.htm)?$ gallery.php?f=<<<id>>>&a=$2\n^<<<hurl>>>/([\\w\\-\\d]+)/((\\d+)\\.htm)?$ gallery.php?f=<<<id>>>&t=$1&a=view_topic&page=$3\n^<<<hurl>>>/([\\w\\-\\d]+)/(\\w+)\\.htm$ gallery.php?f=<<<id>>>&t=$1&a=$2\n^moderate/<<<hurl>>>/((\\w+)/)?(\\w+)\\.htm$ moderate.php?f=<<<id>>>&a=$3&t=$2\n', '0');

UPDATE `ib_forum_type` SET typename='Анонимный форум', has_rules='1', has_foreword='1', allow_mass='1', allow_subforums='0', allow_personal='0', sortfield=3, route='^<<<hurl>>>/((\\d+)\\.htm)?$ anon.php?f=<<<id>>>&a=view_forum&page=$2\n^<<<hurl>>>/((\\w+)\\.htm)?$ anon.php?f=<<<id>>>&a=$2\n^<<<hurl>>>/([\\w\\-\\d]+)/((\\d+)\\.htm)?$ anon.php?f=<<<id>>>&t=$1&a=view_topic&page=$3\n^<<<hurl>>>/([\\w\\-\\d]+)/(\\w+)\\.htm$ anon.php?f=<<<id>>>&t=$1&a=$2\n^<<<hurl>>>/([\\w\\-\\d]+)/post-(\\d+)\\.htm$ anon.php?f=<<<id>>>&t=$1&a=post&post=$2\n^moderate/<<<hurl>>>/((\\w+)/)?(\\w+)\\.htm$ moderate.php?f=<<<id>>>&a=$3&t=$2\n', skip_sitemap='0' WHERE module='anon';
UPDATE `ib_forum_type` SET typename='Блог или новости', has_rules='1', has_foreword='1', allow_mass='1', allow_subforums='0', allow_personal='1', sortfield=6, route='^<<<hurl>>>/((\\d+)\\.htm)?$ blog.php?f=<<<id>>>&a=view_forum&page=$2\n^<<<hurl>>>/((\\w+)\\.htm)?$ blog.php?f=<<<id>>>&a=$2\n^<<<hurl>>>/([\\w\\-\\d]+)/((\\d+)\\.htm)?$ blog.php?f=<<<id>>>&t=$1&a=view_topic&page=$3\n^<<<hurl>>>/([\\w\\-\\d]+)/(\\w+)\\.htm$ blog.php?f=<<<id>>>&t=$1&a=$2\n^<<<hurl>>>/([\\w\\-\\d]+)/post-(\\d+)\\.htm$ blog.php?f=<<<id>>>&t=$1&a=post&post=$2\n^moderate/<<<hurl>>>/((\\w+)/)?(\\w+)\\.htm$ moderate.php?f=<<<id>>>&a=$3&t=$2\n', skip_sitemap='0' WHERE module='blog';
