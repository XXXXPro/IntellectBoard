DROP TABLE IF EXISTS `ib_access`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ib_access` (
  `gid` smallint(6) NOT NULL COMMENT 'Номер группы',
  `fid` smallint(5) unsigned NOT NULL COMMENT 'Раздел',
  `view` enum('0','1') NOT NULL DEFAULT '1' COMMENT 'Просмотр раздела, возможность видеть его в списке на главной',
  `read` enum('0','1') NOT NULL DEFAULT '1' COMMENT 'Чтение сообщений в разделе',
  `post` enum('0','1') NOT NULL DEFAULT '1' COMMENT 'Отправка сообщений',
  `attach` enum('0','1') NOT NULL DEFAULT '1' COMMENT 'Прикрепление файлов',
  `topic` enum('0','1') NOT NULL DEFAULT '1' COMMENT 'Создание тем',
  `poll` enum('0','1') NOT NULL DEFAULT '1' COMMENT 'Создание опросов',
  `html` enum('0','1') NOT NULL DEFAULT '0' COMMENT 'Использование HTML-кода без экранирования',
  `vote` enum('0','1') NOT NULL DEFAULT '0' COMMENT 'Разрешено ли голосовать в опросах в разделе',
  `rate` enum('0','1') NOT NULL DEFAULT '0' COMMENT 'Разрешено ли рейтинговать сообщения в разделе',
  `edit` enum('0','1') NOT NULL DEFAULT '1' COMMENT 'Возможность редактировать свои сообщения',
  `nocaptcha` enum('0','1') NOT NULL DEFAULT '1' COMMENT 'Возможность отправлять сообщения без ввода CAPTCHA',
  `nopremod` enum('0','1') NOT NULL DEFAULT '1' COMMENT 'Разрешено ли отправлять сообщения без премодерации',
  PRIMARY KEY (`gid`,`fid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `ib_access` VALUES (0,0,'1','1','0','0','0','0','0','0','0','0','0','0'),(50,0,'1','1','1','0','1','0','0','0','0','0','1','1'),(100,0,'1','1','1','1','1','0','0','1','0','1','1','1'),(120,0,'1','1','1','1','1','0','0','1','0','1','1','1'),(140,0,'1','1','1','1','1','1','0','1','1','1','1','1'),(160,0,'1','1','1','1','1','1','0','1','1','1','1','1'),(180,0,'1','1','1','1','1','1','0','1','1','1','1','1'),(499,0,'1','1','1','1','1','1','0','1','1','1','1','1'),(500,0,'1','1','1','1','1','1','0','1','1','1','1','1'),(1000,0,'1','1','1','1','1','1','1','1','1','1','1','1'),(1024,0,'1','1','1','1','1','1','1','1','1','1','1','1');
DROP TABLE IF EXISTS `ib_banned_ip`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ib_banned_ip` (
  `start` int(10) unsigned NOT NULL,
  `end` int(10) unsigned NOT NULL,
  `till` int(10) unsigned NOT NULL,
  PRIMARY KEY (`start`,`end`,`till`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ib_bots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ib_bots` (
  `id` smallint(5) NOT NULL AUTO_INCREMENT,
  `user_agent` varchar(255) NOT NULL COMMENT 'Часть строки User Agent, по которой определяется бот',
  `bot_name` varchar(255) NOT NULL COMMENT 'Название бота (выводится в админке и списке присутствующих)',
  `last_visit` int(10) unsigned NOT NULL COMMENT 'Время последнего визита',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `ib_bots` VALUES (1,'YandexBot','Яндекс',0),(2,'Googlebot','Google',0),(3,'bingbot','Bing!',0),(5,'Yahoo! Slurp','Yahoo!',0),(6,'mail.ru','@Mail.Ru',0),(7,'W3C_Validator','W3C Validator',0);
DROP TABLE IF EXISTS `ib_captcha`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ib_captcha` (
  `hash` char(32) NOT NULL COMMENT 'Хеш, по которому осуществляется проверка кода (он же совпадает с именем файла в files/captcha/хеш.jpg)',
  `code` char(8) NOT NULL COMMENT 'Код, который должен ввести пользователь',
  `active` enum('0','1') NOT NULL DEFAULT '1' COMMENT 'Признак того, что данный код еще не использован',
  `lastmod` int(11) NOT NULL COMMENT 'Время генерации пары хеш-код (нужно для удаления устаревших пар)',
  `ip` int(11) NOT NULL COMMENT 'IP адрес пользователя, запросившего хеш (возможно, будет использоваться для блокировки)',
  PRIMARY KEY (`hash`),
  KEY `lastmod` (`lastmod`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='Коды CAPTCHA для соответствующего модуля';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ib_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ib_category` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(80) NOT NULL COMMENT 'Название категории',
  `collapsed` enum('0','1') NOT NULL DEFAULT '0' COMMENT 'Если 1, то по умолчанию на главной выводится в свернутом виде',
  `sortfield` smallint(5) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ib_complain`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ib_complain` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Номер жалобы',
  `uid` mediumint(8) unsigned DEFAULT 0 COMMENT 'Идентификатор пользователя, ее отправившего',
  `pid` int(10) unsigned DEFAULT 0 COMMENT 'Идентификатор сообщения, на которое подана жалоба',
  `processed` enum('0','1') DEFAULT '0' COMMENT 'Жалоба обработана',
  `moderator` mediumint(8) unsigned DEFAULT 0 COMMENT 'Модератор, выполнивший обработку',
  `text` varchar(255) DEFAULT NULL COMMENT 'Текст жалобы',
  `mod_comment` varchar(255) DEFAULT NULL COMMENT 'Комментарий модератора',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='Жалобы пользователей на сообщения';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ib_crontab`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ib_crontab` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT COMMENT 'Идентификатор задания',
  `library` varchar(24) NOT NULL COMMENT 'Библиотека, в которой находится выполняемая процедура',
  `proc` varchar(255) NOT NULL COMMENT 'Название процедуры',
  `params` varchar(255) NOT NULL COMMENT 'Параметры процедуры',
  `description` varchar(255) NOT NULL COMMENT 'Описание (показывается администраторам сайта)',
  `nextrun` int(11) unsigned NOT NULL COMMENT 'Время след. выполнения',
  `period` smallint(6) unsigned NOT NULL COMMENT 'Период выполнения (в минутах), если равен нулю, задание отключено',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `nextrun` (`nextrun`,`period`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='Список заданий, выполняемых по времени';
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `ib_crontab` VALUES (1,'antibot','captcha_clear','24','Очистка старых данных CAPTCHA',0,32768),(2,'maintain','log_rotate','5','Ротация логов в каталоге logs',0,1440),(3,'antibot','timeout_clear','24','Очистка старых данных о таймаутах',0,1441),(4,'maintain','search_results_clear','7','Очистка старых результатов поиска',0,1443),(5,'maintain','mod_logs_clear','90','Удаление старых данных о модераторских действиях',0,10079),(6,'maintain','light_optimize','','Малая оптимизация баз данных (только часто изменяемые таблицы)',0,4300),(7,'maintain','heavy_optimize','','Полная оптимизация базы данных (все таблицы)',0,44643),(8,'maintain','update_mark_all','90','Отметка прочитанными всех тем, которые обновились, но не были просмотрены в течение заданного количества дней',0,10081),(9,'delete','inactive_users_clear','30','Удаление пользователей, не активировавших свой профиль в течение указанного количества дней',0,4320),(10,'maintain','online_clear','3','Очистка списка последних действий пользователей',0,1440),(11,'instagram','getdata','вставьте свой token','Обновление списка фотографий из Instagram',0,60),(12,'sitemap','generate','','Генерация файла sitemap.xml',0,180),(13,'instagram','refresh','','Обновление access token для Instagram',0,10080);
DROP TABLE IF EXISTS `ib_file`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ib_file` (
  `fkey` char(16) NOT NULL DEFAULT '' COMMENT 'Ключ-идентификатор файла',
  `oid` int(11) unsigned NOT NULL COMMENT 'Номер объекта, к которому прикреплен файл',
  `is_main` enum('0','1') NOT NULL DEFAULT '0' COMMENT 'Является ли приложенный файл главным (обложкой альбома)',
  `type` tinyint(16) unsigned NOT NULL COMMENT 'Тип прикрепления: 1 -- к форумному сообщению, 2 -- к ЛС (не используется)',
  `filename` varchar(255) NOT NULL COMMENT 'Имя файла при скачивании',
  `size` int(11) NOT NULL COMMENT 'Размер файла в байтах',
  `format` enum('attach','image','video','audio','text') NOT NULL DEFAULT 'attach',
  `extension` char(4) NOT NULL COMMENT 'Расширение файла',
  `descr` text DEFAULT NULL COMMENT 'Описание (подзаголовок) фотографии',
  `exif` text DEFAULT NULL COMMENT 'Данные EXIF оригинала фотографии',
  `geo_latitude` float DEFAULT NULL COMMENT 'Широта для геометок фото',
  `geo_longtitude` float DEFAULT NULL COMMENT 'Долгота для геометок фото',
  PRIMARY KEY (`fkey`),
  KEY `oid` (`oid`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='Приложенные файлы';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ib_forum`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ib_forum` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `module` varchar(16) NOT NULL COMMENT 'Модуль, отвечающий за показ страницы',
  `title` varchar(80) NOT NULL COMMENT 'Название раздела',
  `descr` varchar(255) NOT NULL DEFAULT '' COMMENT 'Описание раздела',
  `hurl` varchar(255) NOT NULL COMMENT 'Частичный URL',
  `category_id` smallint(5) unsigned NOT NULL DEFAULT 0,
  `parent_id` smallint(5) unsigned NOT NULL DEFAULT 0 COMMENT 'Родительский раздел',
  `owner` mediumint(9) unsigned NOT NULL DEFAULT 0 COMMENT 'Владелец раздела (для личных блогов, галерей и т.п.)',
  `sortfield` smallint(5) unsigned NOT NULL DEFAULT 0 COMMENT 'Порядок сортировки',
  `locked` enum('0','1') NOT NULL DEFAULT '0' COMMENT 'Форум закрыт для новых сообщений: 0 -- нет, 1 -- да',
  `lastmod` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Дата последнего изменения',
  `last_post_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Id последнего сообщения',
  `template` varchar(32) NOT NULL DEFAULT '' COMMENT 'Шаблон для отображения раздела',
  `template_override` enum('0','1') NOT NULL DEFAULT '0' COMMENT 'Приоритет шаблона форума над шаблоном пользователя',
  `bcode` enum('0','1') NOT NULL DEFAULT '1' COMMENT 'Использование BBCode',
  `max_smiles` tinyint(3) unsigned NOT NULL DEFAULT 16 COMMENT 'Максимальное количество смайликов в сообщении',
  `max_attach` tinyint(3) NOT NULL DEFAULT 0 COMMENT 'Максимальное количество файлов, которое можно прикрепить к сообщению',
  `attach_types` tinyint(3) unsigned NOT NULL DEFAULT 255 COMMENT 'Допустимые типы прикрепляемых файлов. Указываются как битовая маска: 255 -- все, 1 -- картинки, 2 -- видео, 4 -- аудио, 8 -- текст, 128 -- все остальное',
  `topic_count` mediumint(8) unsigned NOT NULL DEFAULT 0 COMMENT 'Количество видимых тем (т.е. со статусом "0", без учета стоящих на премодерации и удаленных)',
  `post_count` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Количество видимых сообщений',
  `is_stats` enum('0','1') NOT NULL DEFAULT '1' COMMENT 'Является ли раздел статистически значимым',
  `is_flood` enum('0','1') NOT NULL DEFAULT '0' COMMENT 'Является ли флуд-разделом (не показывать в "Обновившихся" и "Непрочитанных", а так же поиске, если он не включен явно)',
  `is_start` enum('0','1') NOT NULL DEFAULT '1' COMMENT 'Показывать ли раздел на главной странице',
  `icon_new` varchar(255) NOT NULL DEFAULT '' COMMENT 'Имя файла при наличии новых сообщений',
  `icon_nonew` varchar(255) NOT NULL DEFAULT '' COMMENT 'Имя файла при отсутствии новых сообщений',
  `sort_mode` enum('ASC','DESC') NOT NULL DEFAULT 'DESC' COMMENT 'Порядок сортировки тем в разделе по умолчанию: по возрастанию или убыванию',
  `sort_column` enum('first_post_id','last_post_time') NOT NULL DEFAULT 'last_post_time' COMMENT 'Способ сортировки тем в форуме по умолчанию: по дате последнего сообщения или дате создания',
  `polls` enum('0','1','2') NOT NULL DEFAULT '1' COMMENT 'Опросы в темах: 0 -- отключены вообще, 1 -- разрешены, 2 -- рарешены уже созданные, но запрещено создание новых',
  `selfmod` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'Режим кураторства: 0 - нет кураторов, 1 -- самомодерация (куратором сразу становится создатель темы), 2 -- кураторы вручную назначаются модераторами',
  `sticky_post` enum('0','1','2','3') NOT NULL DEFAULT '2' COMMENT 'Первое сообщение является приклееным (выводится на всех страницах): 0 -- нет, 1 -- выставляется модераторами, 2 -- выставлеяется пользователем при создании темы, 3 -- есть всегда',
  `rate` enum('0','1','2') NOT NULL DEFAULT '2' COMMENT 'Разрешены ли рейтинги на форуме: 0 - нет, 1 -- да, 2 -- только положительные',
  `rate_value` float NOT NULL DEFAULT 0 COMMENT 'Количество голосов "за", после которых сообщение становится ценным',
  `rate_flood` float NOT NULL DEFAULT 0 COMMENT 'Количество голосов против, после которых сообщение становится флудом',
  `tags` enum('0','1','2') NOT NULL DEFAULT '0' COMMENT 'Использование тегов для тем: 0 -- нет, 1 -- да, 2 -- ставить теги могут только модераторы',
  `webmention` enum('0','1','2') NOT NULL DEFAULT '1' COMMENT 'Использование WebMention: 0 - отключено, 1 - разрешено без премодерации, 2 - разрешено с премодерацией',
  `micropub` enum('0','1','2') NOT NULL DEFAULT '0' COMMENT 'Использование Micropub и Microsub',
  PRIMARY KEY (`id`),
  KEY `sortkey` (`sortfield`),
  KEY `hurl` (`hurl`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `ib_forum` VALUES (1,'statpage','О проекте','Информация о нашем сайте','about',0,0,0,1,'0',0,0,'','0','1',16,0,255,0,0,'1','0','1','','','DESC','last_post_time','1',0,'2','2',0,0,'0','1','0');
DROP TABLE IF EXISTS `ib_forum_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ib_forum_type` (
  `module` varchar(40) NOT NULL COMMENT 'Имя модуля, который отвечает за отображение раздела',
  `typename` varchar(255) NOT NULL COMMENT 'Название раздела для вывода в админке',
  `has_rules` enum('0','1') NOT NULL DEFAULT '1' COMMENT 'У раздела могут быть правила',
  `has_foreword` enum('0','1') NOT NULL DEFAULT '1' COMMENT 'У раздела может быть вводное слово',
  `allow_mass` enum('0','1') NOT NULL DEFAULT '1' COMMENT 'Над данным типом разделов разрешены групповые операции',
  `allow_subforums` enum('0','1') NOT NULL DEFAULT '0' COMMENT 'У разделов данного типа могут быть вложенные подразделы',
  `allow_personal` enum('0','1') NOT NULL DEFAULT '0' COMMENT 'Разделы данного типа могут быть личными',
  `sortfield` smallint(5) unsigned NOT NULL DEFAULT 1 COMMENT 'Поле для сортировки',
  `route` text DEFAULT NULL COMMENT 'Правила для роутнга, если он осуществляется через текстовый файл',
  `skip_sitemap` enum('1','0') DEFAULT '0' COMMENT 'Если поле выставлено в 1, темы из данного вида разделов не будут включаться в sitemap.xml',
  PRIMARY KEY (`module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='Описание типов разделов';
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `ib_forum_type` VALUES ('anon','Анонимный форум','1','1','1','0','0',3,'^<<<hurl>>>/((\\d+)\\.htm)?$ anon.php?f=<<<id>>>&a=view_forum&page=$2\n^<<<hurl>>>/((\\w+)\\.htm)?$ anon.php?f=<<<id>>>&a=$2\n^<<<hurl>>>/([\\w\\-\\d]+)/((\\d+)\\.htm)?$ anon.php?f=<<<id>>>&t=$1&a=view_topic&page=$3\n^<<<hurl>>>/([\\w\\-\\d]+)/(\\w+)\\.htm$ anon.php?f=<<<id>>>&t=$1&a=$2\n^moderate/<<<hurl>>>/((\\w+)/)?(\\w+)\\.htm$ moderate.php?f=<<<id>>>&a=$3&t=$2\n','0'),('blog','Блог или новости','1','1','1','0','1',6,'^<<<hurl>>>/((\\d+)\\.htm)?$ blog.php?f=<<<id>>>&a=view_forum&page=$2\n^<<<hurl>>>/((\\w+)\\.htm)?$ blog.php?f=<<<id>>>&a=$2\n^<<<hurl>>>/([\\w\\-\\d]+)/((\\d+)\\.htm)?$ blog.php?f=<<<id>>>&t=$1&a=view_topic&page=$3\n^<<<hurl>>>/([\\w\\-\\d]+)/(\\w+)\\.htm$ blog.php?f=<<<id>>>&t=$1&a=$2\n^moderate/<<<hurl>>>/((\\w+)/)?(\\w+)\\.htm$ moderate.php?f=<<<id>>>&a=$3&t=$2\n','0'),('gallery','Фотогалерея','1','1','1','0','1',6,'^<<<hurl>>>/((\\d+)\\.htm)?$ gallery.php?f=<<<id>>>&a=view_forum&page=$2\r\n^<<<hurl>>>/((\\w+)\\.htm)?$ gallery.php?f=<<<id>>>&a=$2\r\n^<<<hurl>>>/([\\w\\-\\d]+)/((\\d+)\\.htm)?$ gallery.php?f=<<<id>>>&t=$1&a=view_topic&page=$3\r\n^<<<hurl>>>/([\\w\\-\\d]+)/(\\w+)\\.htm$ gallery.php?f=<<<id>>>&t=$1&a=$2\r\n^moderate/<<<hurl>>>/((\\w+)/)?(\\w+)\\.htm$ moderate.php?f=<<<id>>>&a=$3&t=$2\r\n','0'),('link','Ссылка на внешний ресурс','0','0','0','0','0',4,'^<<<hurl>>>/?$ link.php?f=<<<id>>>&a=view','1'),('micro','Микроблог','1','1','1','0','1',7,'^<<<hurl>>>/((\\w+)\\.htm)?$ micro.php?f=<<<id>>>&a=$2\n^moderate/<<<hurl>>>/edit_foreword.htm$ moderate.php?f=<<<id>>>&a=edit_foreword\n^moderate/<<<hurl>>>/((\\w+)/)?(\\w+)\\.htm$ moderate.php?f=<<<id>>>&a=$3&t=$2\n','1'),('statpage','Статическая страница','0','1','1','1','0',2,'^<<<hurl>>>/((\\w+)\\.htm)?$ statpage.php?f=<<<id>>>&a=$2\n^moderate/<<<hurl>>>/edit_foreword.htm$ statpage.php?f=<<<id>>>&a=edit\n','1'),('stdforum','Обычный форум','1','1','1','1','0',1,'^<<<hurl>>>/((\\d+)\\.htm)?$ stdforum.php?f=<<<id>>>&a=view_forum&page=$2\n^<<<hurl>>>/((\\w+)\\.htm)?$ stdforum.php?f=<<<id>>>&a=$2\n^<<<hurl>>>/([\\w\\-\\d]+)/((\\d+)\\.htm)?$ stdforum.php?f=<<<id>>>&t=$1&a=view_topic&page=$3\n^<<<hurl>>>/([\\w\\-\\d]+)/(\\w+)\\.htm$ stdforum.php?f=<<<id>>>&t=$1&a=$2\n^<<<hurl>>>/([\\w\\-\\d]+)/(\\w+)\\.htm$ stdforum.php?f=<<<id>>>&t=$1&a=$2\n^<<<hurl>>>/([\\w\\-\\d]+)/post-(\\d+)\\.htm$ stdforum.php?f=<<<id>>>&t=$1&a=post&post=$2 \n^moderate/<<<hurl>>>/(([\\w\\-\\d]+)/)?(\\w+)\\.htm$ moderate.php?f=<<<id>>>&a=$3&t=$2','0');
DROP TABLE IF EXISTS `ib_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ib_group` (
  `level` smallint(6) NOT NULL COMMENT 'Номер группы, он же уровень доступа. В целом рекомендуется так: чем выше, тем статуснее группа',
  `name` varchar(32) NOT NULL,
  `special` enum('0','1') NOT NULL DEFAULT '1' COMMENT 'В особые группы пользователь может быть добавлен только явно, администратором',
  `floodtime` smallint(5) unsigned NOT NULL DEFAULT 60,
  `privmsg_hour` tinyint(3) unsigned NOT NULL DEFAULT 240,
  `max_attach` smallint(5) unsigned NOT NULL COMMENT 'Макс размер приложенного файла (в килобайтах)',
  `min_posts` smallint(5) unsigned NOT NULL DEFAULT 0 COMMENT 'Минимальное число сообщений',
  `custom_title` enum('0','1') NOT NULL DEFAULT '0' COMMENT 'Разрешено пользователю ставить себе "особое звание"',
  `admin` enum('0','1') NOT NULL DEFAULT '0' COMMENT 'Является ли группа администраторами',
  `team` enum('0','1') NOT NULL DEFAULT '0' COMMENT 'Группа считается группой команды форума (из групп команды назначаются модераторы и эксперты форума, ее участники выводятся в списке "наша команда")',
  `founder` enum('0','1') NOT NULL DEFAULT '0' COMMENT 'Founder -- права суперпользователя форума (администратор с правом назначения/снятия других администраторов и сменой глобальных настроек)',
  `min_reg_time` smallint(5) unsigned NOT NULL DEFAULT 0 COMMENT 'Количество дней, которое должно пройти с момента регистрации, для получения уровня (если уровень -- не "особый"',
  `links_mode` enum('none','premod','nofollow','allow') NOT NULL DEFAULT 'allow' COMMENT 'Режим использования гиперссылок:  none -- запрещено отправлять сообщения со ссылками, premoderate -- сообщения со ссылками уходят на премодерацию, nofollow -- ссылкам ставится аттрибут nofollow, allow -- ссылки выводятся нормально',
  PRIMARY KEY (`level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `ib_group` VALUES (0,'Гость','1',90,0,2048,0,'0','0','0','0',0,'nofollow'),(50,'Сомнительный тип','1',90,3,0,0,'0','0','0','0',0,'none'),(100,'Новичок','0',30,6,1024,0,'0','0','0','0',0,'none'),(120,'Начинающий','0',30,10,1024,5,'0','0','0','0',2,'nofollow'),(140,'Участник','0',10,25,2048,25,'0','0','0','0',4,'nofollow'),(160,'Почетный участник','0',5,60,2048,100,'1','0','0','0',7,'allow'),(180,'Долгожитель форума','0',3,240,4096,500,'1','0','0','0',30,'allow'),(499,'Участник команды','1',0,0,4096,0,'1','0','1','0',0,'allow'),(500,'Модератор','1',0,0,4096,0,'1','0','1','0',0,'allow'),(1000,'Администратор','1',0,0,65535,0,'1','1','1','1',0,'allow'),(1024,'Создатель форума','1',0,0,65535,0,'1','1','1','1',0,'allow');
DROP TABLE IF EXISTS `ib_last_visit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ib_last_visit` (
  `oid` mediumint(9) NOT NULL COMMENT 'Идентификатор раздела или темы',
  `type` enum('forum','topic') NOT NULL COMMENT 'Тип объекта: раздел или тема',
  `uid` mediumint(9) NOT NULL COMMENT 'Идентификатор пользователя',
  `visit1` int(11) NOT NULL COMMENT 'Время текущего посещения',
  `visit2` int(11) DEFAULT NULL COMMENT 'Время предыдущего посещения',
  `bookmark` enum('0','1') NOT NULL DEFAULT '0' COMMENT 'Находится ли тема в закладках',
  `subscribe` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'Подписан ли пользователь на тему (0 -- нет, 1 -- отправка уведомлений сразу, 2 -- уведомление только о первом новом сообщении, 3 и более -- периодическая высылка уведомлений)',
  `lastmail` int(11) DEFAULT NULL COMMENT 'Дата последней отправки сообщения',
  `posted` enum('0','1') NOT NULL DEFAULT '0' COMMENT 'Если пользователь писал в эту тему',
  PRIMARY KEY (`oid`,`uid`,`type`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='Данные о посещении раздела или темы пользователем';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ib_log_action`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ib_log_action` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Номер сообщения, над которым совершалось действие',
  `tid` mediumint(8) unsigned NOT NULL DEFAULT 0 COMMENT 'Номер темы, над которой совершалось действие',
  `fid` mediumint(8) unsigned NOT NULL DEFAULT 0 COMMENT 'Номер раздела, над которым совершалось действие',
  `type` tinyint(3) unsigned NOT NULL COMMENT 'Тип модераторского действия',
  `time` int(10) unsigned NOT NULL COMMENT 'Время модераторского действия',
  `uid` mediumint(8) unsigned NOT NULL COMMENT 'Модератор или пользователь, выполнивший действие',
  `data` text NOT NULL COMMENT 'Данные для отката модераторского действия',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='Лог модераторских действий с возможностью отката измеений';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ib_mark_all`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ib_mark_all` (
  `uid` mediumint(8) unsigned NOT NULL COMMENT 'Идентификатор пользователя',
  `fid` mediumint(8) unsigned NOT NULL COMMENT 'Раздел, для которого выполняется действие "Отметить все" (0 -- весь форум)',
  `mark_time` int(10) unsigned NOT NULL COMMENT 'Время выполнения действия',
  PRIMARY KEY (`uid`,`fid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='Данные об отметке раздела или всего форума как прочтенного';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ib_menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ib_menu` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Идентификатор меню',
  `descr` varchar(255) NOT NULL COMMENT 'Описание меню (показывается в админцентре)',
  `locked` enum('0','1') NOT NULL COMMENT 'Запрет на удаление меню (для наиболее важных системных меню)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='Список различных меню, используемых движком';
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `ib_menu` VALUES (1,'Главное меню','1'),(2,'Меню Центра Администрирования','1');
DROP TABLE IF EXISTS `ib_menu_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ib_menu_item` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Идентификатор пункта',
  `mid` smallint(6) unsigned NOT NULL COMMENT 'Идентификатор меню, которому принадлежит пункт',
  `title` varchar(255) NOT NULL COMMENT 'Заголовок пункта меню (выводится при выводе меню)',
  `url` varchar(255) NOT NULL COMMENT 'URL пункта меню',
  `sortfield` smallint(6) NOT NULL COMMENT 'Поле для определения порядка сортировки',
  `show_guests` enum('0','1') NOT NULL DEFAULT '0' COMMENT 'Показывать пункт гостям',
  `show_users` enum('0','1') NOT NULL DEFAULT '0' COMMENT 'Показывать пункт пользователям',
  `show_admins` enum('0','1') NOT NULL DEFAULT '0' COMMENT 'Показывать пункт администраторам',
  `hurl_mode` enum('0','1') NOT NULL DEFAULT '0' COMMENT 'Является ли ссылка адресом относительно корня форума (нужно ли применять функцию url)',
  PRIMARY KEY (`id`),
  KEY `mid` (`mid`,`sortfield`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='Элементы меню';
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `ib_menu_item` VALUES (1,1,'О проекте','about/',1,'1','1','1','1'),(2,1,'Правила','rules.htm',2,'1','1','1','1'),(3,1,'Участники','users/',5,'1','1','1','1'),(4,1,'Команда','team.htm',4,'1','1','1','1'),(5,1,'Последние сообщения','newtopics/',3,'1','1','1','1'),(6,1,'Поиск','search/',7,'1','1','1','1'),(7,1,'Справка','help/',8,'1','1','1','1'),(8,1,'Сейчас присутствуют','online/',6,'1','1','1','1');
DROP TABLE IF EXISTS `ib_moderator`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ib_moderator` (
  `fid` mediumint(9) NOT NULL COMMENT 'Объект, для которого пользователь показывается как член команды',
  `uid` mediumint(9) NOT NULL COMMENT 'Идентификатор пользователя',
  `role` enum('moderator','expert') NOT NULL COMMENT 'Роль (модератор или эксперт)',
  PRIMARY KEY (`fid`,`uid`,`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci ROW_FORMAT=DYNAMIC COMMENT='Данные о модераторах и экспертах форума';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ib_oauth_code`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ib_oauth_code` (
  `code` char(64) NOT NULL COMMENT 'Сам код',
  `uid` mediumint(8) unsigned NOT NULL COMMENT 'ID пользователя, которому он выдан',
  `client_id` varchar(255) NOT NULL COMMENT 'ID клиента (для IndieWeb — сайт владельца)',
  `redirect_uri` varchar(255) NOT NULL COMMENT 'Поле state для проверки',
  `me` varchar(255) NOT NULL COMMENT 'URL, на который делается авторизация',
  `scope` varchar(255) DEFAULT NULL COMMENT 'Список типов доступа, который был запрошен',
  `expires` int(10) unsigned NOT NULL,
  PRIMARY KEY (`code`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='Коды для авторизации по протоколу OAuth';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ib_oauth_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ib_oauth_token` (
  `token` char(128) NOT NULL,
  `uid` mediumint(8) unsigned NOT NULL,
  `client_id` varchar(255) NOT NULL,
  `scope` varchar(255) DEFAULT NULL,
  `expires` int(11) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ib_online`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ib_online` (
  `uid` mediumint(8) unsigned NOT NULL COMMENT 'Идентификатор пользователя',
  `hash` char(32) NOT NULL COMMENT 'Хеш данных пользователя (IP-адрес, user agent и еще несколько полей заголовков HTTP)',
  `visittime` int(10) unsigned NOT NULL COMMENT 'Время последнего визита',
  `type` smallint(6) NOT NULL COMMENT 'Тип пользователя (0 -- гость, -1 -- зарегистрированный пользователь, -2 -- член команды, положительные значения -- номера ботов',
  `fid` mediumint(8) unsigned NOT NULL COMMENT 'Номер раздела, над которым совершается действие',
  `tid` mediumint(8) unsigned NOT NULL COMMENT 'Номер темы',
  `text` varchar(255) NOT NULL COMMENT 'Описание действия, обрабатыавется через sprintf с подстановкой ссылок на тему и форум',
  `ip` varchar(255) NOT NULL COMMENT 'IP пользователя (подумать, нужен ли вообще)',
  PRIMARY KEY (`hash`,`uid`) USING HASH,
  KEY `uid` (`uid`) USING HASH
) ENGINE=MEMORY DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ib_poll`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ib_poll` (
  `id` mediumint(8) unsigned NOT NULL COMMENT 'Тема, к которой привязан опрос',
  `question` varchar(255) NOT NULL COMMENT 'Текст вопроса',
  `endtime` int(10) unsigned NOT NULL COMMENT 'Дата окончания опроса',
  `mode` enum('0','1') NOT NULL DEFAULT '0' COMMENT 'Режим голосования: 0 -- анонимный, 1 -- с возможностью просмотра проголосовавших (пока не используется)',
  `max_variants` tinyint(4) NOT NULL DEFAULT 1 COMMENT 'Количество вариантов ответов, которое можно выбрать (пока не используется)',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='Таблица опросов';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ib_poll_variant`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ib_poll_variant` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Идентификатор варианта ответа',
  `tid` mediumint(8) unsigned NOT NULL COMMENT 'Идентификатор темы с голосованием',
  `text` varchar(80) NOT NULL COMMENT 'Текст варианта ответа',
  `count` mediumint(8) unsigned NOT NULL DEFAULT 0 COMMENT 'Количество голосований за этот вариант',
  PRIMARY KEY (`id`),
  KEY `poll_tid` (`tid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ib_post`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ib_post` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `tid` mediumint(9) unsigned NOT NULL COMMENT 'Тема, к которой относится сообщение',
  `uid` mediumint(9) unsigned NOT NULL DEFAULT 1 COMMENT 'Номер владельца сообщения',
  `author` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL DEFAULT 'Guest' COMMENT 'Автор сообщения (NULL -- если зарегистрированный пользователь)',
  `postdate` int(11) unsigned NOT NULL DEFAULT 0 COMMENT 'Дата отправки сообщения',
  `editcount` smallint(6) unsigned NOT NULL DEFAULT 0 COMMENT 'Количество редактирований',
  `editor_id` mediumint(8) unsigned NOT NULL DEFAULT 0 COMMENT 'Идентификатор последнего отредактировавшего сообщение',
  `value` enum('0','1','-1') NOT NULL DEFAULT '0' COMMENT 'Ценность сообщения: 0 -- обычное, 1 -- ценное, -1 -- флуд',
  `status` enum('0','1','2') NOT NULL DEFAULT '0' COMMENT 'Статус сообщения: 0 -- нормальное сообщение, 1 -- на премодерации, 2 -- удалено',
  `locked` enum('0','1') NOT NULL DEFAULT '0' COMMENT 'Сообщение заблокировано от редактирования',
  `html` enum('0','1') NOT NULL DEFAULT '0' COMMENT 'В сообщении разрешено использование HTML',
  `bcode` enum('0','1') NOT NULL DEFAULT '1' COMMENT 'В сообщении разрешено использование BoardCode',
  `smiles` enum('0','1') NOT NULL DEFAULT '1' COMMENT 'В сообщении разрешены графические смайлики',
  `links` enum('0','1') NOT NULL DEFAULT '1' COMMENT 'Автоматически обрабатывать ссылки',
  `typograf` enum('0','1') NOT NULL DEFAULT '1' COMMENT 'Автоматически типографировать сообщение',
  `rating` float NOT NULL DEFAULT 0 COMMENT 'Рейтинг сообщения',
  `ip` varchar(255) NOT NULL COMMENT 'IP, с которого было отправлено сообщение',
  `email` varchar(255) NOT NULL DEFAULT '' COMMENT 'Email гостя, если он его указал. Для зарегистрированных — пустое значение',
  PRIMARY KEY (`id`),
  KEY `topic` (`tid`,`postdate`),
  KEY `author_uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='Данные о сообщениях в темах и разделах';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ib_privmsg_link`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ib_privmsg_link` (
  `pm_id` int(11) NOT NULL,
  `uid` mediumint(9) NOT NULL,
  PRIMARY KEY (`uid`,`pm_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='Связь сообщений в теме и пользователей, у которых их видно';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ib_privmsg_post`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ib_privmsg_post` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pm_thread` int(10) unsigned NOT NULL,
  `subscribers` tinyint(3) unsigned DEFAULT 1 COMMENT 'Количество получателей сообщения',
  `uid` mediumint(8) unsigned NOT NULL,
  `text` text NOT NULL,
  `postdate` int(10) unsigned NOT NULL,
  `html` enum('0','1') NOT NULL DEFAULT '0',
  `bcode` enum('0','1') NOT NULL DEFAULT '1',
  `smiles` enum('0','1') NOT NULL DEFAULT '1',
  `links` enum('0','1') NOT NULL DEFAULT '1',
  `typograf` enum('0','1') NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `thread` (`pm_thread`,`postdate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='Личные сообщения ';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ib_privmsg_thread`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ib_privmsg_thread` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL COMMENT 'Название темы ЛС',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='Таблица тем ЛС.';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ib_privmsg_thread_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ib_privmsg_thread_user` (
  `pm_thread` int(11) unsigned NOT NULL,
  `uid` mediumint(9) unsigned NOT NULL,
  `total` smallint(6) unsigned NOT NULL DEFAULT 0 COMMENT 'Количество доступных сообщений',
  `unread` smallint(6) unsigned NOT NULL DEFAULT 0 COMMENT 'Количество непрочитанных сообщений',
  `last_post_date` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Идентификатор последнего доступного пользователю сообщения',
  PRIMARY KEY (`uid`,`pm_thread`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='Данные о теме, специфичные для пользователя';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ib_rating`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ib_rating` (
  `id` int(10) unsigned NOT NULL,
  `uid` mediumint(8) unsigned NOT NULL,
  `value` float NOT NULL,
  `time` int(10) unsigned NOT NULL,
  `ip` varchar(255) NOT NULL,
  PRIMARY KEY (`id`,`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='Информация о том, когда было отрейтинговано сообщение';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ib_relation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ib_relation` (
  `from_` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `to_` mediumint(8) unsigned NOT NULL,
  `type` enum('friend','ignore') NOT NULL,
  PRIMARY KEY (`from_`,`to_`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ib_search`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ib_search` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Номер поиска',
  `owner` mediumint(8) unsigned DEFAULT 0 COMMENT 'Id ищущего пользователя',
  `output_mode` enum('topics','posts') DEFAULT 'posts' COMMENT 'Режим вывода (темы или сообщения)',
  `search_type` tinyint(4) DEFAULT 1 COMMENT 'Режим поиска (для выдачи информации о запросе при его показе)',
  `query` varchar(255) DEFAULT '1' COMMENT 'Текст запроса',
  `time` int(10) unsigned DEFAULT NULL COMMENT 'Время запроса',
  `extdata` text DEFAULT NULL COMMENT 'Расширенные данные поиска: дата, время, разделы',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='Таблица для хранения результатов поиска FULLTEXT или Sphynx';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ib_search_result`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ib_search_result` (
  `sid` int(10) unsigned NOT NULL COMMENT 'Номер поиска',
  `oid` int(10) unsigned NOT NULL COMMENT 'Номер найденного сообщения или темы',
  `relevancy` float unsigned DEFAULT 0 COMMENT 'Релевантность сообщения (или значение для сортировки)',
  PRIMARY KEY (`oid`,`sid`),
  KEY `relevancy` (`sid`,`relevancy`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='Таблица для хранения результатов поиска';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ib_smile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ib_smile` (
  `code` varchar(16) NOT NULL COMMENT 'Код смайлика',
  `file` varchar(255) NOT NULL COMMENT 'Имя файла со смайликом',
  `descr` varchar(255) NOT NULL COMMENT 'Описание того, что смайлик обозначает',
  `mode` enum('more','dropdown','hidden') NOT NULL DEFAULT 'dropdown' COMMENT 'Режим отображения смайлика в панели редактора: more -- обычный, dropdown -- в выпадающем списке, hidden -- не отображать',
  `sortfield` smallint(5) unsigned NOT NULL DEFAULT 0 COMMENT 'Поле для сортировки',
  PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `ib_smile` VALUES ('8-)','cool.png','','dropdown',4),(':\'(','cwy.png','','dropdown',5),(':(','sad.png','','dropdown',9),(':)','smile.png','','dropdown',1),(':alien:','alien.png','','more',100),(':angel:','angel.png','','dropdown',2),(':angry:','angry.png','','dropdown',3),(':blink:','blink.png','','more',101),(':blush:','blush.png','','more',102),(':cheerful:','cheerful.png','','more',103),(':D','grin.png','','dropdown',7),(':devil:','devil.png','','more',104),(':dizzy:','dizzy.png','','more',105),(':ermm:','ermm.png','','dropdown',6),(':face:','face.png','','more',119),(':getlost:','getlost.png','','more',106),(':happy:','happy.png','','more',107),(':kissing:','kissing.png','','more',108),(':laughing:','laughing.png','','more',120),(':love:','wub.png','','hidden',501),(':ninja:','ninja.png','','more',109),(':O','shocked.png','','dropdown',10),(':P','tongue.png','','dropdown',11),(':pinch:','pinch.png','','more',110),(':pouty:','pouty.png','','more',111),(':sick:','sick.png','','more',112),(':sideways:','sideways.png','','more',113),(':silly:','silly.png','','more',114),(':sleeping:','sleeping.png','','more',115),(':unsure:','unsure.png','','more',116),(':wassat:','wassat.png','','more',118),(':whistling:','whistling.png','','hidden',500),(':woot:','w00t.png','','more',117),(';)','wink.png','','dropdown',12),('<3','heart.png','','dropdown',8);
DROP TABLE IF EXISTS `ib_subaction`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ib_subaction` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL COMMENT 'Название блока, видимое администратору',
  `module` varchar(32) NOT NULL,
  `action` varchar(32) NOT NULL,
  `fid` smallint(6) NOT NULL DEFAULT 0 COMMENT 'Id раздела, в котором выводится блок, 0 -- во всех',
  `tid` smallint(6) NOT NULL DEFAULT 0 COMMENT 'Id темы, в которой выводится блок, 0 -- во всех',
  `library` varchar(32) NOT NULL,
  `proc` varchar(32) NOT NULL,
  `block` varchar(32) NOT NULL,
  `active` enum('0','1') NOT NULL DEFAULT '1',
  `params` varchar(255) NOT NULL,
  `priority` smallint(6) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `intb_subaction_module_IDX` (`module`,`action`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `ib_subaction` VALUES (1,'Блок тегов на обычном форуме','stdforum','view_forum',0,0,'blocks','block_tag_list','action_start','0','20',1),(2,'Блок «Сейчас присутствуют» на главной','mainpage','view',0,0,'online','get_online_users','page_bottom','1','2',10),(3,'Блок «Сейчас присутствуют» в разделах','*','view_forum',0,0,'online','get_online_users','page_bottom','0','2',10),(4,'Блок «Сейчас присутствуют» в темах','*','view_topic',0,0,'online','get_online_users','page_bottom','0','2',10),(5,'Блок объявлений','*','*',0,0,'blocks','block_announce','welcome_start','1','1',1),(6,'Блок с количеством личных сообщений','*','*',0,0,'blocks','block_pm_unread','pm_notify','1','',1),(7,'Блок фотографий из Instagram','statpage','view',0,0,'instagram','block_instagram','page_bottom','0','4,Добавьте свой Instagram token',20);
DROP TABLE IF EXISTS `ib_tagentry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ib_tagentry` (
  `tag_id` smallint(6) NOT NULL,
  `item_id` mediumint(9) NOT NULL,
  PRIMARY KEY (`tag_id`,`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ib_tagname`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ib_tagname` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `type` tinyint(3) unsigned NOT NULL,
  `tagname` varchar(32) NOT NULL,
  `count` mediumint(9) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tagname` (`tagname`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ib_task`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ib_task` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Номер задачи',
  `library` varchar(24) NOT NULL COMMENT 'Библиотека, в которой находится выполняемая процедура',
  `proc` varchar(255) NOT NULL COMMENT 'Название процедуры',
  `params` text NOT NULL COMMENT 'Параметры в сериализованном виде',
  `nextrun` int(10) unsigned NOT NULL COMMENT 'Время следующей попытки выполнения',
  `errors` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'Количество ошибок при предыдущих попытках',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='Очередь из разово выполняемых задач';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ib_text`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ib_text` (
  `id` mediumint(8) unsigned NOT NULL COMMENT 'Номер раздела',
  `type` tinyint(3) unsigned NOT NULL COMMENT 'Тип текста: 0 -- правила, 1 -- объявление, 2 -- текст статического раздела',
  `data` mediumtext NOT NULL COMMENT 'Текст',
  `tx_lastmod` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`,`type`) USING BTREE,
  FULLTEXT KEY `search` (`data`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `ib_text` VALUES (0,0,'Правила форума разрабатываются. А пока просим придерживаться общих принципов вежливости и доброжелательности.',0),(1,2,'Если вы читаете этот текст, то установка Intellect Board прошла успешно. \r\nВ дальнейшем его можно будет заменить на информацию о вашем проекте или просто удалить.\r\nЭтот раздел имеет тип \"Статическая страница\". Обычный раздел с темами и соощениями вы можете \r\nсоздать в Центре Администрирования.',0);
DROP TABLE IF EXISTS `ib_timeout`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ib_timeout` (
  `time` int(11) NOT NULL COMMENT 'Время последней попытки совершения действия',
  `action` varchar(32) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL COMMENT 'Условное название действия',
  `uid` mediumint(9) NOT NULL COMMENT 'Идентификатор пользователя',
  `ip` int(11) NOT NULL,
  KEY `time` (`time`,`action`,`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='Таблица хранения таймаутов между действиями (типа регистраци';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ib_topic`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ib_topic` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `fid` mediumint(8) unsigned NOT NULL COMMENT 'Идентификатор раздела, в котором находится тема',
  `title` varchar(80) NOT NULL COMMENT 'Название темы',
  `descr` varchar(255) NOT NULL COMMENT 'Описание темы',
  `status` enum('0','1','2') NOT NULL DEFAULT '0' COMMENT 'Статус темы: 0 -- нормальная, 1 -- на премодерации, 2 -- удалена',
  `hurl` varchar(255) NOT NULL DEFAULT '' COMMENT 'HURL темы (без HURL раздела)',
  `locked` enum('0','1') NOT NULL DEFAULT '0' COMMENT 'Тема закрыта',
  `first_post_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Первое сообщение темы',
  `last_post_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Второе сообщение темы',
  `lastmod` int(10) unsigned NOT NULL COMMENT 'Время последнего изменения темы (отправки сообщения, редактирования или модерации)',
  `post_count` smallint(5) unsigned NOT NULL DEFAULT 0 COMMENT 'Отображаемое количество сообщений',
  `flood_count` smallint(5) unsigned NOT NULL DEFAULT 0 COMMENT 'Количество сообщений, помеченных как флуд',
  `valued_count` smallint(5) unsigned NOT NULL DEFAULT 0 COMMENT 'Количество ценных сообщений',
  `owner` mediumint(8) unsigned NOT NULL DEFAULT 0 COMMENT 'Владелец темы (обычно автор первого сообщения)',
  `sticky` enum('0','1') NOT NULL DEFAULT '0' COMMENT 'Тема является прикленной',
  `sticky_post` enum('0','1') NOT NULL DEFAULT '0' COMMENT 'Показывать ли первое сообщение темы на каждой странице',
  `favorites` enum('0','1') NOT NULL DEFAULT '0' COMMENT 'Тема есть в "лучших темах форума"',
  `ext_status` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Расширенный статус (используется специализированными разделами)',
  `last_post_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Время отправки последнего сообщения (сделано в целях оптимизации нагрузки на БД)',
  `rating` mediumint(8) unsigned NOT NULL DEFAULT 0 COMMENT 'Суммарный рейтинг темы',
  PRIMARY KEY (`id`),
  KEY `forum` (`fid`,`last_post_id`),
  FULLTEXT KEY `Fulltext_title` (`title`,`descr`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ib_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ib_user` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `login` varchar(32) NOT NULL,
  `password` varchar(255) NOT NULL,
  `pass_crypt` tinyint(3) unsigned NOT NULL,
  `title` varchar(80) NOT NULL DEFAULT '',
  `gender` enum('M','F','U') NOT NULL DEFAULT 'U',
  `birthdate` date DEFAULT NULL,
  `location` varchar(80) NOT NULL,
  `status` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `canonical` varchar(255) NOT NULL,
  `signature` varchar(255) NOT NULL,
  `rnd` int(10) unsigned NOT NULL,
  `display_name` varchar(32) NOT NULL,
  `avatar` enum('none','gif','jpg','png') NOT NULL DEFAULT 'none',
  `photo` enum('none','gif','jpg','png') NOT NULL DEFAULT 'none',
  `email` varchar(255) NOT NULL,
  `real_name` varchar(255) NOT NULL DEFAULT '' COMMENT 'Реальное имя пользователя, если он захочет его указать',
  PRIMARY KEY (`id`),
  UNIQUE KEY `login` (`login`),
  UNIQUE KEY `display_name` (`display_name`),
  KEY `location` (`location`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `ib_user` VALUES (1,'Guest','*',1,'','U',NULL,' ',0,'guest','',111,'Гость','none','none','null@intbpro.ru',''),(2,'System','*',1,'','U',NULL,'',0,'system','',222,'System','none','none','null@intbpro.ru',''),(3,'New User','*',5,'','U',NULL,'',0,'NewUser','',333,'New User','none','none','null2@intbpro.ru','');
DROP TABLE IF EXISTS `ib_user_award`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ib_user_award` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Идентификатор',
  `uid` mediumint(8) unsigned NOT NULL COMMENT 'Идентификатор награжденного пользователя',
  `file` varchar(255) NOT NULL COMMENT 'Имя файла с изображением награды',
  `descr` varchar(255) NOT NULL COMMENT 'Описание причины для награждения',
  `time` int(10) unsigned NOT NULL COMMENT 'Дата награждения',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ib_user_contact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ib_user_contact` (
  `uid` mediumint(9) NOT NULL,
  `cid` smallint(6) NOT NULL,
  `value` varchar(80) NOT NULL,
  PRIMARY KEY (`uid`,`cid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ib_user_contact_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ib_user_contact_type` (
  `cid` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `c_title` varchar(80) NOT NULL COMMENT 'Название контакта или социальной сети',
  `icon` varchar(255) NOT NULL COMMENT 'URL значка контакта или социальной сети',
  `link` varchar(255) NOT NULL COMMENT 'Ссылка на контакт или профиль соцсети',
  `c_sort` smallint(6) NOT NULL COMMENT 'Поле для сортировки',
  `c_name` varchar(32) NOT NULL COMMENT 'Идентификатор для библиотеки авторизации через соцсети',
  `c_permission` enum('0','1') NOT NULL DEFAULT '0' COMMENT 'Нужна ли проверка на наличие прав размещать ссылки при выводе этого контакта',
  PRIMARY KEY (`cid`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `ib_user_contact_type` VALUES (2,'Skype','icons/c/skype.gif','skype:%s',50,'','0'),(3,'ВКонтакте','icons/c/vk.gif','http://vk.com/%s',30,'vkontakte','0'),(4,'ICQ','icons/c/icq.gif','',80,'','0'),(5,'Jabber/XMPP','icons/c/jabber.gif','xmpp:%s',100,'','0'),(6,'МойМир@Mail.Ru','icons/c/agent.gif','http://my.mail.ru/%s',60,'mailru','0'),(7,'LiveJournal','icons/c/lj.gif','http://%s.livejournal.com',70,'livejournal','0'),(8,'Telegram','icons/c/telegram.png','https://t-do.ru/%s',20,'telegram','0'),(9,'GTalk/GMail','icons/c/gtalk.gif','mailto:%s@gmail.com',40,'google','0'),(10,'Одноклассники','icons/c/odno.gif','http://www.odnoklassniki.ru/profile/%s',35,'odnoklassniki','0'),(11,'Facebook','icons/c/facebook.gif','https://www.facebook.com/profile.php?id=%s',37,'facebook','0'),(12,'Twitter','icons/c/twitter.gif','http://twitter.com/%s',90,'twitter','0'),(13,'Webmoney ID','icons/c/webmoney.gif','https://passport.webmoney.ru/asp/CertView.asp?wmid=%s',120,'webmoney','0'),(14,'OpenID','icons/c/openid.gif','%s',110,'openid','0'),(15,'Личный сайт','','%s',100,'','1'),(16,'Личный блог','','%s',100,'','1');
DROP TABLE IF EXISTS `ib_user_ext`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ib_user_ext` (
  `id` mediumint(8) unsigned NOT NULL,
  `post_count` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Количество сообщений',
  `rating` float NOT NULL DEFAULT 0 COMMENT 'Суммарный рейтинг',
  `warnings` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'Сумма баллов предупреждений',
  `balance` decimal(10,0) NOT NULL DEFAULT 0 COMMENT 'Баланс (сейчас не используется)',
  `banned_till` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Если пользователь изгнан, дата окончания срока',
  `group_id` smallint(6) NOT NULL DEFAULT 0 COMMENT 'Группа прав доступа пользователя',
  `reg_date` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Дата регистрации',
  `reg_ip` varchar(255) NOT NULL DEFAULT '0' COMMENT 'IP, с которого произведена регистрация',
  PRIMARY KEY (`id`),
  KEY `user_group` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `ib_user_ext` VALUES (1,10,0,0,0,0,0,1411401372,'0'),(2,0,0,0,0,0,0,1411401372,'0'),(3,0,0,0,0,0,100,1411401372,'0');
DROP TABLE IF EXISTS `ib_user_field`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ib_user_field` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Идентификатор поля',
  `title` varchar(60) NOT NULL COMMENT 'Название поля',
  `type` enum('text','number','select','multiselect','radio') NOT NULL DEFAULT 'text' COMMENT 'Тип поля: текст, числовое значение, выбор значения из списка типа Select или переключателей Radio',
  `values` text NOT NULL COMMENT 'Список возможных значений для select и radio, для text -- регулярное выражение для проверки корректности ввода',
  `in_msg` enum('0','1') NOT NULL DEFAULT '1' COMMENT 'Выводить значение поля при показе сообщений пользователя или только в его профиле',
  `sortfield` smallint(5) unsigned NOT NULL COMMENT 'Поле для сортировки',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='Задаваемые поля для профиля пользователя';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ib_user_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ib_user_settings` (
  `id` mediumint(8) unsigned NOT NULL,
  `topics_per_page` tinyint(3) unsigned NOT NULL DEFAULT 10 COMMENT 'Тем на странице',
  `posts_per_page` tinyint(3) unsigned NOT NULL DEFAULT 20 COMMENT 'Сообщений на странице',
  `template` varchar(20) NOT NULL DEFAULT '' COMMENT 'Используемый шаблон',
  `msg_order` enum('ASC','DESC') NOT NULL DEFAULT 'ASC' COMMENT 'Порядок сортировки сообщений в теме',
  `subscribe` enum('None','My','All') NOT NULL DEFAULT 'None' COMMENT 'Подписка на обновления: нет, только на созданные темы, на все темы, в которых пользователь пишет ответ',
  `timezone` smallint(5) NOT NULL DEFAULT 10800 COMMENT 'Часовой пояс участника (смещение в секундах)',
  `signatures` enum('0','1') NOT NULL DEFAULT '1' COMMENT 'Показывать подписи',
  `avatars` enum('0','1') NOT NULL DEFAULT '1' COMMENT 'Показывать аватары',
  `smiles` enum('0','1') NOT NULL DEFAULT '1' COMMENT 'Показывать смайлики',
  `pics` enum('0','1') NOT NULL DEFAULT '1' COMMENT 'Показывать прикрепленные и вставленные изображения',
  `longposts` enum('0','1','2') NOT NULL DEFAULT '0' COMMENT 'Сворачивать длинные сообщения: 0 -- никогда, 1 -- да, 2 -- только  помеченные как флуд',
  `show_birthdate` enum('0','1','2','3') NOT NULL DEFAULT '3' COMMENT 'Показывать дату рождения (0 -- нет, 1 -- да, 2 -- только дату, 3 -- только возраст)',
  `subscribe_mode` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT 'Режим рассылки уведомлений',
  `email_fulltext` enum('0','1') NOT NULL DEFAULT '1' COMMENT 'Отправлять полный текст сообщения на почту',
  `email_pm` enum('0','1') NOT NULL DEFAULT '1' COMMENT 'Отправлять увеедомления о новых личных сообщениях',
  `email_message` enum('0','1') NOT NULL DEFAULT '1' COMMENT 'Разрешить отправку сообщений через форму на сайте',
  `email_broadcasts` enum('0','1') NOT NULL DEFAULT '1' COMMENT 'Получать рассылки от администратора',
  `flood_limit` tinyint(3) unsigned NOT NULL DEFAULT 50 COMMENT 'Порог (в процентах), после которого тема считается зафлуженной',
  `topics_period` smallint(5) unsigned NOT NULL DEFAULT 0 COMMENT 'Период (в часах) за который выводятся темы на форуме. 0 -- выдача за все время',
  `hidden` enum('0','1') NOT NULL DEFAULT '0' COMMENT '"Скрытный пользователь" (не показывать в списке присутствующих)',
  `wysiwyg` enum('0','1','2') NOT NULL DEFAULT '1' COMMENT 'Режим работы визуального редактора: 0 -- выключен, 1 -- вставка тегов без визуализации, 2 -- полностью визуальный (TinyMCE)',
  `goto` enum('0','1','2','3') NOT NULL DEFAULT '0' COMMENT 'Переход после отправки сообщения: 0 -- в тему, 1 -- в раздел, 2 -- к "Обновившимся", 3 -- к "Непрочитанным"',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `ib_user_settings` VALUES (1,0,0,'','ASC','None',10800,'1','1','1','1','0','0',1,'1','1','1','1',50,0,'0','1','0'),(3,15,20,'','ASC','My',10800,'1','1','1','1','0','0',1,'1','1','1','1',50,0,'0','2','0');
DROP TABLE IF EXISTS `ib_user_value`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ib_user_value` (
  `uid` mediumint(8) unsigned NOT NULL COMMENT 'Идентификатор пользователя',
  `fdid` smallint(5) unsigned NOT NULL COMMENT 'Идентификатор задаваемого поля',
  `value` varchar(255) NOT NULL COMMENT 'Значение задаваемого поля',
  PRIMARY KEY (`uid`,`fdid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='Значения задаваемых полей профиля пользователя';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ib_user_warning`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ib_user_warning` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `uid` mediumint(8) unsigned NOT NULL DEFAULT 0 COMMENT 'Идентификатор пользователя, которому вынесено предупреждение',
  `warntime` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Время вынесения',
  `moderator` mediumint(8) unsigned NOT NULL DEFAULT 0 COMMENT 'Идентификатор модератора, вынесшего предупреждение',
  `pid` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Идентификатор сообщения, за которое вынесено предупреждение',
  `value` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT 'Количество штрафных баллов',
  `warntill` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Дата окончания действия предупреждения',
  `descr` text NOT NULL COMMENT 'Комментарий модератора',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='Предупреждения и наказания пользователей';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ib_views`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ib_views` (
  `oid` mediumint(8) unsigned NOT NULL COMMENT 'Номер объекта',
  `type` enum('forum','topic') NOT NULL DEFAULT 'topic' COMMENT 'Для какого объекта указаны просмотры: раздел или тема',
  `views` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Количество просмотров',
  PRIMARY KEY (`oid`,`type`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ib_vote`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ib_vote` (
  `tid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Номер темы с опросом',
  `uid` mediumint(8) unsigned NOT NULL COMMENT 'Идентификатор пользователя',
  `pvid` int(10) unsigned NOT NULL COMMENT 'Идентификатор варианта ответа',
  `time` int(10) unsigned NOT NULL COMMENT 'Время голосования',
  `ip` varchar(255) NOT NULL COMMENT 'IP, с которого производилось голосование',
  PRIMARY KEY (`tid`,`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='Результаты голосования отдельных пользователей';
/*!40101 SET character_set_client = @saved_cs_client */;
