ALTER TABLE `ib_forum_type` ADD COLUMN route text NULL comment 'Шаблон для генерации правил модуля роутинга';
ALTER TABLE `ib_user` CHANGE COLUMN login login varchar(64) NOT NULL;
ALTER TABLE `ib_user` CHANGE COLUMN display_name display_name varchar(64) NOT NULL;
UPDATE `ib_forum_type` SET route='^<<<hurl>>>/((\\d+)\\.htm)?$ anon.php?f=<<<id>>>&a=view_forum&page=$2\n^<<<hurl>>>/((\\w+)\\.htm)?$ anon.php?f=<<<id>>>&a=$2\n^<<<hurl>>>/([\\w\\-\\d]+)/((\\d+)\\.htm)?$ anon.php?f=<<<id>>>&t=$1&a=view_topic&page=$3\n^<<<hurl>>>/([\\w\\-\\d]+)/(\\w+)\\.htm$ anon.php?f=<<<id>>>&t=$1&a=$2\n^moderate/<<<hurl>>>/((\\w+)/)?(\\w+)\\.htm$ moderate.php?f=<<<id>>>&a=$3&t=$2\n' WHERE module="anon";
UPDATE `ib_forum_type` SET route='^<<<hurl>>>/?$ link.php?f=<<<id>>>&a=view' WHERE module="link";
UPDATE `ib_forum_type` SET route='^<<<hurl>>>/((\\w+)\\.htm)?$ statpage.php?f=<<<id>>>&a=$2\n^moderate/<<<hurl>>>/edit_foreword.htm$ statpage.php?f=<<<id>>>&a=edit\n'  WHERE module="statpage";
UPDATE `ib_forum_type` SET route='^<<<hurl>>>/((\\d+)\\.htm)?$ stdforum.php?f=<<<id>>>&a=view_forum&page=$2\n^<<<hurl>>>/((\\w+)\\.htm)?$ stdforum.php?f=<<<id>>>&a=$2\n^<<<hurl>>>/([\\w\\-\\d]+)/((\\d+)\\.htm)?$ stdforum.php?f=<<<id>>>&t=$1&a=view_topic&page=$3\n^<<<hurl>>>/([\\w\\-\\d]+)/(\\w+)\\.htm$ stdforum.php?f=<<<id>>>&t=$1&a=$2\n^<<<hurl>>>/([\\w\\-\\d]+)/(\\w+)\\.htm$ stdforum.php?f=<<<id>>>&t=$1&a=$2\n^<<<hurl>>>/([\\w\\-\\d]+)/post-(\\d+)\\.htm$ stdforum.php?f=<<<id>>>&t=$1&a=post&post=$2 \n^moderate/<<<hurl>>>/(([\\w\\-\\d]+)/)?(\\w+)\\.htm$ moderate.php?f=<<<id>>>&a=$3&t=$2' WHERE module="stdforum";
DELETE FROM `ib_bots` WHERE bot_name="Sputnik";
