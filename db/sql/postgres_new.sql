

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;





CREATE TYPE ib_current.ib_access_attach AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_access_edit AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_access_html AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_access_nocaptcha AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_access_nopremod AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_access_poll AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_access_post AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_access_rate AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_access_read AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_access_topic AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_access_view AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_access_vote AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_captcha_active AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_category_collapsed AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_complain_processed AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_file_format AS ENUM (
    'attach',
    'image',
    'video',
    'audio',
    'text'
);



CREATE TYPE ib_current.ib_file_is_main AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_forum_bcode AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_forum_is_flood AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_forum_is_start AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_forum_is_stats AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_forum_locked AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_forum_micropub AS ENUM (
    '0',
    '1',
    '2'
);



CREATE TYPE ib_current.ib_forum_polls AS ENUM (
    '0',
    '1',
    '2'
);



CREATE TYPE ib_current.ib_forum_rate AS ENUM (
    '0',
    '1',
    '2'
);



CREATE TYPE ib_current.ib_forum_selfmod AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_forum_sort_column AS ENUM (
    'first_post_id',
    'last_post_time'
);



CREATE TYPE ib_current.ib_forum_sort_mode AS ENUM (
    'ASC',
    'DESC'
);



CREATE TYPE ib_current.ib_forum_sticky_post AS ENUM (
    '0',
    '1',
    '2',
    '3'
);



CREATE TYPE ib_current.ib_forum_tags AS ENUM (
    '0',
    '1',
    '2'
);



CREATE TYPE ib_current.ib_forum_template_override AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_forum_type_allow_mass AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_forum_type_allow_personal AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_forum_type_allow_subforums AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_forum_type_has_foreword AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_forum_type_has_rules AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_forum_type_skip_sitemap AS ENUM (
    '1',
    '0'
);



CREATE TYPE ib_current.ib_forum_webmention AS ENUM (
    '0',
    '1',
    '2'
);



CREATE TYPE ib_current.ib_group_admin AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_group_custom_title AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_group_founder AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_group_links_mode AS ENUM (
    'none',
    'premod',
    'nofollow',
    'allow'
);



CREATE TYPE ib_current.ib_group_special AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_group_team AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_last_visit_bookmark AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_last_visit_posted AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_last_visit_type AS ENUM (
    'forum',
    'topic'
);



CREATE TYPE ib_current.ib_menu_item_hurl_mode AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_menu_item_show_admins AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_menu_item_show_guests AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_menu_item_show_users AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_menu_locked AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_moderator_role AS ENUM (
    'moderator',
    'expert'
);



CREATE TYPE ib_current.ib_poll_mode AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_post_bcode AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_post_html AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_post_links AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_post_locked AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_post_smiles AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_post_status AS ENUM (
    '0',
    '1',
    '2'
);



CREATE TYPE ib_current.ib_post_typograf AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_post_value AS ENUM (
    '0',
    '1',
    '-1'
);



CREATE TYPE ib_current.ib_privmsg_post_bcode AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_privmsg_post_html AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_privmsg_post_links AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_privmsg_post_smiles AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_privmsg_post_typograf AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_relation_type AS ENUM (
    'friend',
    'ignore'
);



CREATE TYPE ib_current.ib_search_output_mode AS ENUM (
    'topics',
    'posts'
);



CREATE TYPE ib_current.ib_smile_mode AS ENUM (
    'more',
    'dropdown',
    'hidden'
);



CREATE TYPE ib_current.ib_subaction_active AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_subactions_active AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_tagname_type AS ENUM (
    'user',
    'topic'
);



CREATE TYPE ib_current.ib_topic_favorites AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_topic_locked AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_topic_status AS ENUM (
    '0',
    '1',
    '2'
);



CREATE TYPE ib_current.ib_topic_sticky AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_topic_sticky_post AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_user_avatar AS ENUM (
    'none',
    'gif',
    'jpg',
    'png'
);



CREATE TYPE ib_current.ib_user_contact_type_c_permission AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_user_field_in_msg AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_user_field_type AS ENUM (
    'text',
    'number',
    'select',
    'multiselect',
    'radio'
);



CREATE TYPE ib_current.ib_user_gender AS ENUM (
    'M',
    'F',
    'U'
);



CREATE TYPE ib_current.ib_user_photo AS ENUM (
    'none',
    'gif',
    'jpg',
    'png'
);



CREATE TYPE ib_current.ib_user_settings_avatars AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_user_settings_email_broadcasts AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_user_settings_email_fulltext AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_user_settings_email_message AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_user_settings_email_pm AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_user_settings_goto AS ENUM (
    '0',
    '1',
    '2',
    '3'
);



CREATE TYPE ib_current.ib_user_settings_hidden AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_user_settings_longposts AS ENUM (
    '0',
    '1',
    '2'
);



CREATE TYPE ib_current.ib_user_settings_msg_order AS ENUM (
    'ASC',
    'DESC'
);



CREATE TYPE ib_current.ib_user_settings_pics AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_user_settings_show_birthdate AS ENUM (
    '0',
    '1',
    '2',
    '3'
);



CREATE TYPE ib_current.ib_user_settings_signatures AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_user_settings_smiles AS ENUM (
    '0',
    '1'
);



CREATE TYPE ib_current.ib_user_settings_subscribe AS ENUM (
    'None',
    'My',
    'All'
);



CREATE TYPE ib_current.ib_user_settings_wysiwyg AS ENUM (
    '0',
    '1',
    '2'
);



CREATE TYPE ib_current.ib_views_type AS ENUM (
    'forum',
    'topic'
);


SET default_tablespace = '';

SET default_table_access_method = heap;


CREATE TABLE ib_current.ib_access (
    gid smallint NOT NULL,
    fid integer NOT NULL,
    view ib_current.ib_access_view DEFAULT '1'::ib_current.ib_access_view NOT NULL,
    read ib_current.ib_access_read DEFAULT '1'::ib_current.ib_access_read NOT NULL,
    post ib_current.ib_access_post DEFAULT '1'::ib_current.ib_access_post NOT NULL,
    attach ib_current.ib_access_attach DEFAULT '1'::ib_current.ib_access_attach NOT NULL,
    topic ib_current.ib_access_topic DEFAULT '1'::ib_current.ib_access_topic NOT NULL,
    poll ib_current.ib_access_poll DEFAULT '1'::ib_current.ib_access_poll NOT NULL,
    html ib_current.ib_access_html DEFAULT '0'::ib_current.ib_access_html NOT NULL,
    vote ib_current.ib_access_vote DEFAULT '0'::ib_current.ib_access_vote NOT NULL,
    rate ib_current.ib_access_rate DEFAULT '0'::ib_current.ib_access_rate NOT NULL,
    edit ib_current.ib_access_edit DEFAULT '1'::ib_current.ib_access_edit NOT NULL,
    nocaptcha ib_current.ib_access_nocaptcha DEFAULT '1'::ib_current.ib_access_nocaptcha NOT NULL,
    nopremod ib_current.ib_access_nopremod DEFAULT '1'::ib_current.ib_access_nopremod NOT NULL
);



COMMENT ON COLUMN ib_current.ib_access.gid IS 'Номер группы';



COMMENT ON COLUMN ib_current.ib_access.fid IS 'Раздел';



COMMENT ON COLUMN ib_current.ib_access.view IS 'Просмотр раздела, возможность видеть его в списке на главной';



COMMENT ON COLUMN ib_current.ib_access.read IS 'Чтение сообщений в разделе';



COMMENT ON COLUMN ib_current.ib_access.post IS 'Отправка сообщений';



COMMENT ON COLUMN ib_current.ib_access.attach IS 'Прикрепление файлов';



COMMENT ON COLUMN ib_current.ib_access.topic IS 'Создание тем';



COMMENT ON COLUMN ib_current.ib_access.poll IS 'Создание опросов';



COMMENT ON COLUMN ib_current.ib_access.html IS 'Использование HTML-кода без экранирования';



COMMENT ON COLUMN ib_current.ib_access.vote IS 'Разрешено ли голосовать в опросах в разделе';



COMMENT ON COLUMN ib_current.ib_access.rate IS 'Разрешено ли рейтинговать сообщения в разделе';



COMMENT ON COLUMN ib_current.ib_access.edit IS 'Возможность редактировать свои сообщения';



COMMENT ON COLUMN ib_current.ib_access.nocaptcha IS 'Возможность отправлять сообщения без ввода CAPTCHA';



COMMENT ON COLUMN ib_current.ib_access.nopremod IS 'Разрешено ли отправлять сообщения без премодерации';



CREATE TABLE ib_current.ib_banned_ip (
    start bigint NOT NULL,
    "end" bigint NOT NULL,
    till bigint NOT NULL
);



CREATE TABLE ib_current.ib_bots (
    id integer NOT NULL,
    user_agent character varying(255) NOT NULL,
    bot_name character varying(255) NOT NULL,
    last_visit bigint NOT NULL
);



COMMENT ON COLUMN ib_current.ib_bots.user_agent IS 'Часть строки User Agent, по которой определяется бот';



COMMENT ON COLUMN ib_current.ib_bots.bot_name IS 'Название бота (выводится в админке и списке присутствующих)';



COMMENT ON COLUMN ib_current.ib_bots.last_visit IS 'Время последнего визита';



CREATE SEQUENCE ib_current.ib_bots_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE ib_current.ib_bots_id_seq OWNED BY ib_current.ib_bots.id;



CREATE TABLE ib_current.ib_captcha (
    hash character(32) NOT NULL,
    code character(8) NOT NULL,
    active ib_current.ib_captcha_active DEFAULT '1'::ib_current.ib_captcha_active NOT NULL,
    lastmod bigint NOT NULL,
    ip bigint NOT NULL
);



COMMENT ON TABLE ib_current.ib_captcha IS 'Коды CAPTCHA для соответствующего модуля';



COMMENT ON COLUMN ib_current.ib_captcha.hash IS 'Хеш, по которому осуществляется проверка кода (он же совпадает с именем файла в files/captcha/хеш.jpg)';



COMMENT ON COLUMN ib_current.ib_captcha.code IS 'Код, который должен ввести пользователь';



COMMENT ON COLUMN ib_current.ib_captcha.active IS 'Признак того, что данный код еще не использован';



COMMENT ON COLUMN ib_current.ib_captcha.lastmod IS 'Время генерации пары хеш-код (нужно для удаления устаревших пар)';



COMMENT ON COLUMN ib_current.ib_captcha.ip IS 'IP адрес пользователя, запросившего хеш (возможно, будет использоваться для блокировки)';



CREATE TABLE ib_current.ib_category (
    id integer NOT NULL,
    title character varying(80) NOT NULL,
    collapsed ib_current.ib_category_collapsed DEFAULT '0'::ib_current.ib_category_collapsed NOT NULL,
    sortfield integer NOT NULL
);



COMMENT ON COLUMN ib_current.ib_category.title IS 'Название категории';



COMMENT ON COLUMN ib_current.ib_category.collapsed IS 'Если 1, то по умолчанию на главной выводится в свернутом виде';



CREATE SEQUENCE ib_current.ib_category_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE ib_current.ib_category_id_seq OWNED BY ib_current.ib_category.id;



CREATE TABLE ib_current.ib_complain (
    id bigint NOT NULL,
    uid integer DEFAULT 0,
    pid bigint DEFAULT '0'::bigint,
    processed ib_current.ib_complain_processed DEFAULT '0'::ib_current.ib_complain_processed,
    moderator integer DEFAULT 0,
    text character varying(255) DEFAULT NULL::character varying,
    mod_comment character varying(255) DEFAULT NULL::character varying
);



COMMENT ON TABLE ib_current.ib_complain IS 'Жалобы пользователей на сообщения';



COMMENT ON COLUMN ib_current.ib_complain.id IS 'Номер жалобы';



COMMENT ON COLUMN ib_current.ib_complain.uid IS 'Идентификатор пользователя, ее отправившего';



COMMENT ON COLUMN ib_current.ib_complain.pid IS 'Идентификатор сообщения, на которое подана жалоба';



COMMENT ON COLUMN ib_current.ib_complain.processed IS 'Жалоба обработана';



COMMENT ON COLUMN ib_current.ib_complain.moderator IS 'Модератор, выполнивший обработку';



COMMENT ON COLUMN ib_current.ib_complain.text IS 'Текст жалобы';



COMMENT ON COLUMN ib_current.ib_complain.mod_comment IS 'Комментарий модератора';



CREATE SEQUENCE ib_current.ib_complain_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE ib_current.ib_complain_id_seq OWNED BY ib_current.ib_complain.id;



CREATE TABLE ib_current.ib_crontab (
    id integer NOT NULL,
    library character varying(24) NOT NULL,
    proc character varying(255) NOT NULL,
    params character varying(255) NOT NULL,
    description character varying(255) NOT NULL,
    nextrun bigint NOT NULL,
    period integer NOT NULL
);



COMMENT ON TABLE ib_current.ib_crontab IS 'Список заданий, выполняемых по времени';



COMMENT ON COLUMN ib_current.ib_crontab.id IS 'Идентификатор задания';



COMMENT ON COLUMN ib_current.ib_crontab.library IS 'Библиотека, в которой находится выполняемая процедура';



COMMENT ON COLUMN ib_current.ib_crontab.proc IS 'Название процедуры';



COMMENT ON COLUMN ib_current.ib_crontab.params IS 'Параметры процедуры';



COMMENT ON COLUMN ib_current.ib_crontab.description IS 'Описание (показывается администраторам сайта)';



COMMENT ON COLUMN ib_current.ib_crontab.nextrun IS 'Время след. выполнения';



COMMENT ON COLUMN ib_current.ib_crontab.period IS 'Период выполнения (в минутах), если равен нулю, задание отключено';



CREATE SEQUENCE ib_current.ib_crontab_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE ib_current.ib_crontab_id_seq OWNED BY ib_current.ib_crontab.id;



CREATE TABLE ib_current.ib_file (
    fkey character(16) DEFAULT ''::bpchar NOT NULL,
    oid bigint NOT NULL,
    is_main ib_current.ib_file_is_main DEFAULT '0'::ib_current.ib_file_is_main NOT NULL,
    type smallint NOT NULL,
    filename character varying(255) NOT NULL,
    size bigint NOT NULL,
    format ib_current.ib_file_format DEFAULT 'attach'::ib_current.ib_file_format NOT NULL,
    extension character(4) NOT NULL,
    descr text,
    exif text,
    geo_latitude double precision,
    geo_longtitude double precision
);



COMMENT ON TABLE ib_current.ib_file IS 'Приложенные файлы';



COMMENT ON COLUMN ib_current.ib_file.fkey IS 'Ключ-идентификатор файла';



COMMENT ON COLUMN ib_current.ib_file.oid IS 'Номер объекта, к которому прикреплен файл';



COMMENT ON COLUMN ib_current.ib_file.is_main IS 'Является ли приложенный файл главным (обложкой альбома)';



COMMENT ON COLUMN ib_current.ib_file.type IS 'Тип прикрепления: 1 -- к форумному сообщению, 2 -- к ЛС (не используется)';



COMMENT ON COLUMN ib_current.ib_file.filename IS 'Имя файла при скачивании';



COMMENT ON COLUMN ib_current.ib_file.size IS 'Размер файла в байтах';



COMMENT ON COLUMN ib_current.ib_file.extension IS 'Расширение файла';



COMMENT ON COLUMN ib_current.ib_file.descr IS 'Описание (подзаголовок) фотографии';



COMMENT ON COLUMN ib_current.ib_file.exif IS 'Данные EXIF оригинала фотографии';



COMMENT ON COLUMN ib_current.ib_file.geo_latitude IS 'Широта для геометок фото';



COMMENT ON COLUMN ib_current.ib_file.geo_longtitude IS 'Долгота для геометок фото';



CREATE TABLE ib_current.ib_forum (
    id integer NOT NULL,
    module character varying(16) NOT NULL,
    title character varying(80) NOT NULL,
    descr character varying(255) DEFAULT ''::character varying NOT NULL,
    hurl character varying(255) NOT NULL,
    category_id integer DEFAULT 0 NOT NULL,
    parent_id integer DEFAULT 0 NOT NULL,
    owner integer DEFAULT 0 NOT NULL,
    sortfield integer DEFAULT 0 NOT NULL,
    locked ib_current.ib_forum_locked DEFAULT '0'::ib_current.ib_forum_locked NOT NULL,
    lastmod bigint DEFAULT '0'::bigint NOT NULL,
    last_post_id bigint DEFAULT '0'::bigint NOT NULL,
    template character varying(32) DEFAULT ''::character varying NOT NULL,
    template_override ib_current.ib_forum_template_override DEFAULT '0'::ib_current.ib_forum_template_override NOT NULL,
    bcode ib_current.ib_forum_bcode DEFAULT '1'::ib_current.ib_forum_bcode NOT NULL,
    max_smiles smallint DEFAULT '16'::smallint NOT NULL,
    max_attach smallint DEFAULT '0'::smallint NOT NULL,
    attach_types smallint DEFAULT '255'::smallint NOT NULL,
    topic_count integer DEFAULT 0 NOT NULL,
    post_count bigint DEFAULT '0'::bigint NOT NULL,
    is_stats ib_current.ib_forum_is_stats DEFAULT '1'::ib_current.ib_forum_is_stats NOT NULL,
    is_flood ib_current.ib_forum_is_flood DEFAULT '0'::ib_current.ib_forum_is_flood NOT NULL,
    is_start ib_current.ib_forum_is_start DEFAULT '1'::ib_current.ib_forum_is_start NOT NULL,
    icon_new character varying(255) DEFAULT ''::character varying NOT NULL,
    icon_nonew character varying(255) DEFAULT ''::character varying NOT NULL,
    sort_mode ib_current.ib_forum_sort_mode DEFAULT 'DESC'::ib_current.ib_forum_sort_mode NOT NULL,
    sort_column ib_current.ib_forum_sort_column DEFAULT 'last_post_time'::ib_current.ib_forum_sort_column NOT NULL,
    polls ib_current.ib_forum_polls DEFAULT '1'::ib_current.ib_forum_polls NOT NULL,
    selfmod smallint DEFAULT '0'::smallint NOT NULL,
    sticky_post ib_current.ib_forum_sticky_post DEFAULT '2'::ib_current.ib_forum_sticky_post NOT NULL,
    rate ib_current.ib_forum_rate DEFAULT '2'::ib_current.ib_forum_rate NOT NULL,
    rate_value double precision DEFAULT '0'::double precision NOT NULL,
    rate_flood double precision DEFAULT '0'::double precision NOT NULL,
    tags ib_current.ib_forum_tags DEFAULT '0'::ib_current.ib_forum_tags NOT NULL,
    webmention ib_current.ib_forum_webmention DEFAULT '1'::ib_current.ib_forum_webmention NOT NULL,
    micropub ib_current.ib_forum_micropub DEFAULT '0'::ib_current.ib_forum_micropub NOT NULL
);



COMMENT ON COLUMN ib_current.ib_forum.module IS 'Модуль, отвечающий за показ страницы';



COMMENT ON COLUMN ib_current.ib_forum.title IS 'Название раздела';



COMMENT ON COLUMN ib_current.ib_forum.descr IS 'Описание раздела';



COMMENT ON COLUMN ib_current.ib_forum.hurl IS 'Частичный URL';



COMMENT ON COLUMN ib_current.ib_forum.parent_id IS 'Родительский раздел';



COMMENT ON COLUMN ib_current.ib_forum.owner IS 'Владелец раздела (для личных блогов, галерей и т.п.)';



COMMENT ON COLUMN ib_current.ib_forum.sortfield IS 'Порядок сортировки';



COMMENT ON COLUMN ib_current.ib_forum.locked IS 'Форум закрыт для новых сообщений: 0 -- нет, 1 -- да';



COMMENT ON COLUMN ib_current.ib_forum.lastmod IS 'Дата последнего изменения';



COMMENT ON COLUMN ib_current.ib_forum.last_post_id IS 'Id последнего сообщения';



COMMENT ON COLUMN ib_current.ib_forum.template IS 'Шаблон для отображения раздела';



COMMENT ON COLUMN ib_current.ib_forum.template_override IS 'Приоритет шаблона форума над шаблоном пользователя';



COMMENT ON COLUMN ib_current.ib_forum.bcode IS 'Использование BBCode';



COMMENT ON COLUMN ib_current.ib_forum.max_smiles IS 'Максимальное количество смайликов в сообщении';



COMMENT ON COLUMN ib_current.ib_forum.max_attach IS 'Максимальное количество файлов, которое можно прикрепить к сообщению';



COMMENT ON COLUMN ib_current.ib_forum.attach_types IS 'Допустимые типы прикрепляемых файлов. Указываются как битовая маска: 255 -- все, 1 -- картинки, 2 -- видео, 4 -- аудио, 8 -- текст, 128 -- все остальное';



COMMENT ON COLUMN ib_current.ib_forum.topic_count IS 'Количество видимых тем (т.е. со статусом "0", без учета стоящих на премодерации и удаленных)';



COMMENT ON COLUMN ib_current.ib_forum.post_count IS 'Количество видимых сообщений';



COMMENT ON COLUMN ib_current.ib_forum.is_stats IS 'Является ли раздел статистически значимым';



COMMENT ON COLUMN ib_current.ib_forum.is_flood IS 'Является ли флуд-разделом (не показывать в "Обновившихся" и "Непрочитанных", а так же поиске, если он не включен явно)';



COMMENT ON COLUMN ib_current.ib_forum.is_start IS 'Показывать ли раздел на главной странице';



COMMENT ON COLUMN ib_current.ib_forum.icon_new IS 'Имя файла при наличии новых сообщений';



COMMENT ON COLUMN ib_current.ib_forum.icon_nonew IS 'Имя файла при отсутствии новых сообщений';



COMMENT ON COLUMN ib_current.ib_forum.sort_mode IS 'Порядок сортировки тем в разделе по умолчанию: по возрастанию или убыванию';



COMMENT ON COLUMN ib_current.ib_forum.sort_column IS 'Способ сортировки тем в форуме по умолчанию: по дате последнего сообщения или дате создания';



COMMENT ON COLUMN ib_current.ib_forum.polls IS 'Опросы в темах: 0 -- отключены вообще, 1 -- разрешены, 2 -- рарешены уже созданные, но запрещено создание новых';



COMMENT ON COLUMN ib_current.ib_forum.selfmod IS 'Режим кураторства: 0 - нет кураторов, 1 -- самомодерация (куратором сразу становится создатель темы), 2 -- кураторы вручную назначаются модераторами';



COMMENT ON COLUMN ib_current.ib_forum.sticky_post IS 'Первое сообщение является приклееным (выводится на всех страницах): 0 -- нет, 1 -- выставляется модераторами, 2 -- выставлеяется пользователем при создании темы, 3 -- есть всегда';



COMMENT ON COLUMN ib_current.ib_forum.rate IS 'Разрешены ли рейтинги на форуме: 0 - нет, 1 -- да, 2 -- только положительные';



COMMENT ON COLUMN ib_current.ib_forum.rate_value IS 'Количество голосов "за", после которых сообщение становится ценным';



COMMENT ON COLUMN ib_current.ib_forum.rate_flood IS 'Количество голосов против, после которых сообщение становится флудом';



COMMENT ON COLUMN ib_current.ib_forum.tags IS 'Использование тегов для тем: 0 -- нет, 1 -- да, 2 -- ставить теги могут только модераторы';



COMMENT ON COLUMN ib_current.ib_forum.webmention IS 'Использование WebMention: 0 - отключено, 1 - разрешено без премодерации, 2 - разрешено с премодерацией';



COMMENT ON COLUMN ib_current.ib_forum.micropub IS 'Использование Micropub и Microsub';



CREATE SEQUENCE ib_current.ib_forum_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE ib_current.ib_forum_id_seq OWNED BY ib_current.ib_forum.id;



CREATE TABLE ib_current.ib_forum_type (
    module character varying(40) NOT NULL,
    typename character varying(255) NOT NULL,
    has_rules ib_current.ib_forum_type_has_rules DEFAULT '1'::ib_current.ib_forum_type_has_rules NOT NULL,
    has_foreword ib_current.ib_forum_type_has_foreword DEFAULT '1'::ib_current.ib_forum_type_has_foreword NOT NULL,
    allow_mass ib_current.ib_forum_type_allow_mass DEFAULT '1'::ib_current.ib_forum_type_allow_mass NOT NULL,
    allow_subforums ib_current.ib_forum_type_allow_subforums DEFAULT '0'::ib_current.ib_forum_type_allow_subforums NOT NULL,
    allow_personal ib_current.ib_forum_type_allow_personal DEFAULT '0'::ib_current.ib_forum_type_allow_personal NOT NULL,
    sortfield integer DEFAULT 1 NOT NULL,
    route text,
    skip_sitemap ib_current.ib_forum_type_skip_sitemap DEFAULT '0'::ib_current.ib_forum_type_skip_sitemap
);



COMMENT ON TABLE ib_current.ib_forum_type IS 'Описание типов разделов';



COMMENT ON COLUMN ib_current.ib_forum_type.module IS 'Имя модуля, который отвечает за отображение раздела';



COMMENT ON COLUMN ib_current.ib_forum_type.typename IS 'Название раздела для вывода в админке';



COMMENT ON COLUMN ib_current.ib_forum_type.has_rules IS 'У раздела могут быть правила';



COMMENT ON COLUMN ib_current.ib_forum_type.has_foreword IS 'У раздела может быть вводное слово';



COMMENT ON COLUMN ib_current.ib_forum_type.allow_mass IS 'Над данным типом разделов разрешены групповые операции';



COMMENT ON COLUMN ib_current.ib_forum_type.allow_subforums IS 'У разделов данного типа могут быть вложенные подразделы';



COMMENT ON COLUMN ib_current.ib_forum_type.allow_personal IS 'Разделы данного типа могут быть личными';



COMMENT ON COLUMN ib_current.ib_forum_type.sortfield IS 'Поле для сортировки';



COMMENT ON COLUMN ib_current.ib_forum_type.route IS 'Правила для роутнга, если он осуществляется через текстовый файл';



COMMENT ON COLUMN ib_current.ib_forum_type.skip_sitemap IS 'Если поле выставлено в 1, темы из данного вида разделов не будут включаться в sitemap.xml';



CREATE TABLE ib_current.ib_group (
    level smallint NOT NULL,
    name character varying(32) NOT NULL,
    special ib_current.ib_group_special DEFAULT '1'::ib_current.ib_group_special NOT NULL,
    floodtime integer DEFAULT 60 NOT NULL,
    privmsg_hour smallint DEFAULT '240'::smallint NOT NULL,
    max_attach integer NOT NULL,
    min_posts integer DEFAULT 0 NOT NULL,
    custom_title ib_current.ib_group_custom_title DEFAULT '0'::ib_current.ib_group_custom_title NOT NULL,
    admin ib_current.ib_group_admin DEFAULT '0'::ib_current.ib_group_admin NOT NULL,
    team ib_current.ib_group_team DEFAULT '0'::ib_current.ib_group_team NOT NULL,
    founder ib_current.ib_group_founder DEFAULT '0'::ib_current.ib_group_founder NOT NULL,
    min_reg_time integer DEFAULT 0 NOT NULL,
    links_mode ib_current.ib_group_links_mode DEFAULT 'allow'::ib_current.ib_group_links_mode NOT NULL
);



COMMENT ON COLUMN ib_current.ib_group.level IS 'Номер группы, он же уровень доступа. В целом рекомендуется так: чем выше, тем статуснее группа';



COMMENT ON COLUMN ib_current.ib_group.special IS 'В особые группы пользователь может быть добавлен только явно, администратором';



COMMENT ON COLUMN ib_current.ib_group.max_attach IS 'Макс размер приложенного файла (в килобайтах)';



COMMENT ON COLUMN ib_current.ib_group.min_posts IS 'Минимальное число сообщений';



COMMENT ON COLUMN ib_current.ib_group.custom_title IS 'Разрешено пользователю ставить себе "особое звание"';



COMMENT ON COLUMN ib_current.ib_group.admin IS 'Является ли группа администраторами';



COMMENT ON COLUMN ib_current.ib_group.team IS 'Группа считается группой команды форума (из групп команды назначаются модераторы и эксперты форума, ее участники выводятся в списке "наша команда")';



COMMENT ON COLUMN ib_current.ib_group.founder IS 'Founder -- права суперпользователя форума (администратор с правом назначения/снятия других администраторов и сменой глобальных настроек)';



COMMENT ON COLUMN ib_current.ib_group.min_reg_time IS 'Количество дней, которое должно пройти с момента регистрации, для получения уровня (если уровень -- не "особый"';



COMMENT ON COLUMN ib_current.ib_group.links_mode IS 'Режим использования гиперссылок:  none -- запрещено отправлять сообщения со ссылками, premoderate -- сообщения со ссылками уходят на премодерацию, nofollow -- ссылкам ставится аттрибут nofollow, allow -- ссылки выводятся нормально';



CREATE TABLE ib_current.ib_last_visit (
    oid integer NOT NULL,
    type ib_current.ib_last_visit_type NOT NULL,
    uid integer NOT NULL,
    visit1 bigint NOT NULL,
    visit2 bigint NOT NULL,
    bookmark ib_current.ib_last_visit_bookmark DEFAULT '0'::ib_current.ib_last_visit_bookmark NOT NULL,
    subscribe smallint DEFAULT '0'::smallint NOT NULL,
    lastmail bigint NOT NULL,
    posted ib_current.ib_last_visit_posted DEFAULT '0'::ib_current.ib_last_visit_posted NOT NULL
);



COMMENT ON TABLE ib_current.ib_last_visit IS 'Данные о посещении раздела или темы пользователем';



COMMENT ON COLUMN ib_current.ib_last_visit.oid IS 'Идентификатор раздела или темы';



COMMENT ON COLUMN ib_current.ib_last_visit.type IS 'Тип объекта: раздел или тема';



COMMENT ON COLUMN ib_current.ib_last_visit.uid IS 'Идентификатор пользователя';



COMMENT ON COLUMN ib_current.ib_last_visit.visit1 IS 'Время текущего посещения';



COMMENT ON COLUMN ib_current.ib_last_visit.visit2 IS 'Время предыдущего посещения';



COMMENT ON COLUMN ib_current.ib_last_visit.bookmark IS 'Находится ли тема в закладках';



COMMENT ON COLUMN ib_current.ib_last_visit.subscribe IS 'Подписан ли пользователь на тему (0 -- нет, 1 -- отправка уведомлений сразу, 2 -- уведомление только о первом новом сообщении, 3 и более -- периодическая высылка уведомлений)';



COMMENT ON COLUMN ib_current.ib_last_visit.lastmail IS 'Дата последней отправки сообщения';



COMMENT ON COLUMN ib_current.ib_last_visit.posted IS 'Если пользователь писал в эту тему';



CREATE TABLE ib_current.ib_log_action (
    id bigint NOT NULL,
    pid bigint DEFAULT '0'::bigint NOT NULL,
    tid integer DEFAULT 0 NOT NULL,
    fid integer DEFAULT 0 NOT NULL,
    type smallint NOT NULL,
    "time" bigint NOT NULL,
    uid integer NOT NULL,
    data text NOT NULL
);



COMMENT ON TABLE ib_current.ib_log_action IS 'Лог модераторских действий с возможностью отката измеений';



COMMENT ON COLUMN ib_current.ib_log_action.pid IS 'Номер сообщения, над которым совершалось действие';



COMMENT ON COLUMN ib_current.ib_log_action.tid IS 'Номер темы, над которой совершалось действие';



COMMENT ON COLUMN ib_current.ib_log_action.fid IS 'Номер раздела, над которым совершалось действие';



COMMENT ON COLUMN ib_current.ib_log_action.type IS 'Тип модераторского действия';



COMMENT ON COLUMN ib_current.ib_log_action."time" IS 'Время модераторского действия';



COMMENT ON COLUMN ib_current.ib_log_action.uid IS 'Модератор или пользователь, выполнивший действие';



COMMENT ON COLUMN ib_current.ib_log_action.data IS 'Данные для отката модераторского действия';



CREATE SEQUENCE ib_current.ib_log_action_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE ib_current.ib_log_action_id_seq OWNED BY ib_current.ib_log_action.id;



CREATE TABLE ib_current.ib_mark_all (
    uid integer NOT NULL,
    fid integer NOT NULL,
    mark_time bigint NOT NULL
);



COMMENT ON TABLE ib_current.ib_mark_all IS 'Данные об отметке раздела или всего форума как прочтенного';



COMMENT ON COLUMN ib_current.ib_mark_all.uid IS 'Идентификатор пользователя';



COMMENT ON COLUMN ib_current.ib_mark_all.fid IS 'Раздел, для которого выполняется действие "Отметить все" (0 -- весь форум)';



COMMENT ON COLUMN ib_current.ib_mark_all.mark_time IS 'Время выполнения действия';



CREATE TABLE ib_current.ib_menu (
    id integer NOT NULL,
    descr character varying(255) NOT NULL,
    locked ib_current.ib_menu_locked NOT NULL
);



COMMENT ON TABLE ib_current.ib_menu IS 'Список различных меню, используемых движком';



COMMENT ON COLUMN ib_current.ib_menu.id IS 'Идентификатор меню';



COMMENT ON COLUMN ib_current.ib_menu.descr IS 'Описание меню (показывается в админцентре)';



COMMENT ON COLUMN ib_current.ib_menu.locked IS 'Запрет на удаление меню (для наиболее важных системных меню)';



CREATE SEQUENCE ib_current.ib_menu_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE ib_current.ib_menu_id_seq OWNED BY ib_current.ib_menu.id;



CREATE TABLE ib_current.ib_menu_item (
    id integer NOT NULL,
    mid integer NOT NULL,
    title character varying(255) NOT NULL,
    url character varying(255) NOT NULL,
    sortfield smallint NOT NULL,
    show_guests ib_current.ib_menu_item_show_guests DEFAULT '0'::ib_current.ib_menu_item_show_guests NOT NULL,
    show_users ib_current.ib_menu_item_show_users DEFAULT '0'::ib_current.ib_menu_item_show_users NOT NULL,
    show_admins ib_current.ib_menu_item_show_admins DEFAULT '0'::ib_current.ib_menu_item_show_admins NOT NULL,
    hurl_mode ib_current.ib_menu_item_hurl_mode DEFAULT '0'::ib_current.ib_menu_item_hurl_mode NOT NULL
);



COMMENT ON TABLE ib_current.ib_menu_item IS 'Элементы меню';



COMMENT ON COLUMN ib_current.ib_menu_item.id IS 'Идентификатор пункта';



COMMENT ON COLUMN ib_current.ib_menu_item.mid IS 'Идентификатор меню, которому принадлежит пункт';



COMMENT ON COLUMN ib_current.ib_menu_item.title IS 'Заголовок пункта меню (выводится при выводе меню)';



COMMENT ON COLUMN ib_current.ib_menu_item.url IS 'URL пункта меню';



COMMENT ON COLUMN ib_current.ib_menu_item.sortfield IS 'Поле для определения порядка сортировки';



COMMENT ON COLUMN ib_current.ib_menu_item.show_guests IS 'Показывать пункт гостям';



COMMENT ON COLUMN ib_current.ib_menu_item.show_users IS 'Показывать пункт пользователям';



COMMENT ON COLUMN ib_current.ib_menu_item.show_admins IS 'Показывать пункт администраторам';



COMMENT ON COLUMN ib_current.ib_menu_item.hurl_mode IS 'Является ли ссылка адресом относительно корня форума (нужно ли применять функцию url)';



CREATE SEQUENCE ib_current.ib_menu_item_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE ib_current.ib_menu_item_id_seq OWNED BY ib_current.ib_menu_item.id;



CREATE TABLE ib_current.ib_moderator (
    fid integer NOT NULL,
    uid integer NOT NULL,
    role ib_current.ib_moderator_role NOT NULL
);



COMMENT ON TABLE ib_current.ib_moderator IS 'Данные о модераторах и экспертах форума';



COMMENT ON COLUMN ib_current.ib_moderator.fid IS 'Объект, для которого пользователь показывается как член команды';



COMMENT ON COLUMN ib_current.ib_moderator.uid IS 'Идентификатор пользователя';



COMMENT ON COLUMN ib_current.ib_moderator.role IS 'Роль (модератор или эксперт)';



CREATE TABLE ib_current.ib_oauth_code (
    code character(64) NOT NULL,
    uid integer NOT NULL,
    client_id character varying(255) NOT NULL,
    redirect_uri character varying(255) NOT NULL,
    me character varying(255) NOT NULL,
    scope character varying(255) DEFAULT NULL::character varying,
    expires bigint NOT NULL
);



COMMENT ON TABLE ib_current.ib_oauth_code IS 'Коды для авторизации по протоколу OAuth';



COMMENT ON COLUMN ib_current.ib_oauth_code.code IS 'Сам код';



COMMENT ON COLUMN ib_current.ib_oauth_code.uid IS 'ID пользователя, которому он выдан';



COMMENT ON COLUMN ib_current.ib_oauth_code.client_id IS 'ID клиента (для IndieWeb — сайт владельца)';



COMMENT ON COLUMN ib_current.ib_oauth_code.redirect_uri IS 'Поле state для проверки';



COMMENT ON COLUMN ib_current.ib_oauth_code.me IS 'URL, на который делается авторизация';



COMMENT ON COLUMN ib_current.ib_oauth_code.scope IS 'Список типов доступа, который был запрошен';



CREATE TABLE ib_current.ib_oauth_token (
    token character(128) NOT NULL,
    uid integer NOT NULL,
    client_id character varying(255) NOT NULL,
    scope character varying(255) DEFAULT NULL::character varying,
    expires bigint NOT NULL
);



CREATE TABLE ib_current.ib_online (
    uid integer NOT NULL,
    hash character(32) NOT NULL,
    visittime bigint NOT NULL,
    type smallint NOT NULL,
    fid integer NOT NULL,
    tid integer NOT NULL,
    text character varying(255) NOT NULL,
    ip character varying(255) NOT NULL
);



COMMENT ON COLUMN ib_current.ib_online.uid IS 'Идентификатор пользователя';



COMMENT ON COLUMN ib_current.ib_online.hash IS 'Хеш данных пользователя (IP-адрес, user agent и еще несколько полей заголовков HTTP)';



COMMENT ON COLUMN ib_current.ib_online.visittime IS 'Время последнего визита';



COMMENT ON COLUMN ib_current.ib_online.type IS 'Тип пользователя (0 -- гость, -1 -- зарегистрированный пользователь, -2 -- член команды, положительные значения -- номера ботов';



COMMENT ON COLUMN ib_current.ib_online.fid IS 'Номер раздела, над которым совершается действие';



COMMENT ON COLUMN ib_current.ib_online.tid IS 'Номер темы';



COMMENT ON COLUMN ib_current.ib_online.text IS 'Описание действия, обрабатыавется через sprintf с подстановкой ссылок на тему и форум';



COMMENT ON COLUMN ib_current.ib_online.ip IS 'IP пользователя (подумать, нужен ли вообще)';



CREATE TABLE ib_current.ib_poll (
    id integer NOT NULL,
    question character varying(255) NOT NULL,
    endtime bigint NOT NULL,
    mode ib_current.ib_poll_mode DEFAULT '0'::ib_current.ib_poll_mode NOT NULL,
    max_variants smallint DEFAULT '1'::smallint NOT NULL
);



COMMENT ON TABLE ib_current.ib_poll IS 'Таблица опросов';



COMMENT ON COLUMN ib_current.ib_poll.id IS 'Тема, к которой привязан опрос';



COMMENT ON COLUMN ib_current.ib_poll.question IS 'Текст вопроса';



COMMENT ON COLUMN ib_current.ib_poll.endtime IS 'Дата окончания опроса';



COMMENT ON COLUMN ib_current.ib_poll.mode IS 'Режим голосования: 0 -- анонимный, 1 -- с возможностью просмотра проголосовавших (пока не используется)';



COMMENT ON COLUMN ib_current.ib_poll.max_variants IS 'Количество вариантов ответов, которое можно выбрать (пока не используется)';



CREATE TABLE ib_current.ib_poll_variant (
    id integer NOT NULL,
    tid integer NOT NULL,
    text character varying(80) NOT NULL,
    count integer DEFAULT 0 NOT NULL
);



COMMENT ON COLUMN ib_current.ib_poll_variant.id IS 'Идентификатор варианта ответа';



COMMENT ON COLUMN ib_current.ib_poll_variant.tid IS 'Идентификатор темы с голосованием';



COMMENT ON COLUMN ib_current.ib_poll_variant.text IS 'Текст варианта ответа';



COMMENT ON COLUMN ib_current.ib_poll_variant.count IS 'Количество голосований за этот вариант';



CREATE SEQUENCE ib_current.ib_poll_variant_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE ib_current.ib_poll_variant_id_seq OWNED BY ib_current.ib_poll_variant.id;



CREATE TABLE ib_current.ib_post (
    id bigint NOT NULL,
    tid integer NOT NULL,
    uid integer DEFAULT 1 NOT NULL,
    author character varying(64) DEFAULT 'Guest'::character varying NOT NULL,
    postdate bigint DEFAULT '0'::bigint NOT NULL,
    editcount integer DEFAULT 0 NOT NULL,
    editor_id integer DEFAULT 0 NOT NULL,
    value ib_current.ib_post_value DEFAULT '0'::ib_current.ib_post_value NOT NULL,
    status ib_current.ib_post_status DEFAULT '0'::ib_current.ib_post_status NOT NULL,
    locked ib_current.ib_post_locked DEFAULT '0'::ib_current.ib_post_locked NOT NULL,
    html ib_current.ib_post_html DEFAULT '0'::ib_current.ib_post_html NOT NULL,
    bcode ib_current.ib_post_bcode DEFAULT '1'::ib_current.ib_post_bcode NOT NULL,
    smiles ib_current.ib_post_smiles DEFAULT '1'::ib_current.ib_post_smiles NOT NULL,
    links ib_current.ib_post_links DEFAULT '1'::ib_current.ib_post_links NOT NULL,
    typograf ib_current.ib_post_typograf DEFAULT '1'::ib_current.ib_post_typograf NOT NULL,
    rating double precision DEFAULT '0'::double precision NOT NULL,
    ip character varying(255) NOT NULL,
    email character varying(255) DEFAULT ''::character varying NOT NULL
);



COMMENT ON TABLE ib_current.ib_post IS 'Данные о сообщениях в темах и разделах';



COMMENT ON COLUMN ib_current.ib_post.tid IS 'Тема, к которой относится сообщение';



COMMENT ON COLUMN ib_current.ib_post.uid IS 'Номер владельца сообщения';



COMMENT ON COLUMN ib_current.ib_post.author IS 'Автор сообщения (NULL -- если зарегистрированный пользователь)';



COMMENT ON COLUMN ib_current.ib_post.postdate IS 'Дата отправки сообщения';



COMMENT ON COLUMN ib_current.ib_post.editcount IS 'Количество редактирований';



COMMENT ON COLUMN ib_current.ib_post.editor_id IS 'Идентификатор последнего отредактировавшего сообщение';



COMMENT ON COLUMN ib_current.ib_post.value IS 'Ценность сообщения: 0 -- обычное, 1 -- ценное, -1 -- флуд';



COMMENT ON COLUMN ib_current.ib_post.status IS 'Статус сообщения: 0 -- нормальное сообщение, 1 -- на премодерации, 2 -- удалено';



COMMENT ON COLUMN ib_current.ib_post.locked IS 'Сообщение заблокировано от редактирования';



COMMENT ON COLUMN ib_current.ib_post.html IS 'В сообщении разрешено использование HTML';



COMMENT ON COLUMN ib_current.ib_post.bcode IS 'В сообщении разрешено использование BoardCode';



COMMENT ON COLUMN ib_current.ib_post.smiles IS 'В сообщении разрешены графические смайлики';



COMMENT ON COLUMN ib_current.ib_post.links IS 'Автоматически обрабатывать ссылки';



COMMENT ON COLUMN ib_current.ib_post.typograf IS 'Автоматически типографировать сообщение';



COMMENT ON COLUMN ib_current.ib_post.rating IS 'Рейтинг сообщения';



COMMENT ON COLUMN ib_current.ib_post.ip IS 'IP, с которого было отправлено сообщение';



COMMENT ON COLUMN ib_current.ib_post.email IS 'Email гостя, если он его указал. Для зарегистрированных — пустое значение';



CREATE SEQUENCE ib_current.ib_post_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE ib_current.ib_post_id_seq OWNED BY ib_current.ib_post.id;



CREATE TABLE ib_current.ib_privmsg_link (
    pm_id bigint NOT NULL,
    uid integer NOT NULL
);



COMMENT ON TABLE ib_current.ib_privmsg_link IS 'Связь сообщений в теме и пользователей, у которых их видно';



CREATE TABLE ib_current.ib_privmsg_post (
    id bigint NOT NULL,
    pm_thread bigint NOT NULL,
    subscribers smallint DEFAULT '1'::smallint,
    uid integer NOT NULL,
    text text NOT NULL,
    postdate bigint NOT NULL,
    html ib_current.ib_privmsg_post_html DEFAULT '0'::ib_current.ib_privmsg_post_html NOT NULL,
    bcode ib_current.ib_privmsg_post_bcode DEFAULT '1'::ib_current.ib_privmsg_post_bcode NOT NULL,
    smiles ib_current.ib_privmsg_post_smiles DEFAULT '1'::ib_current.ib_privmsg_post_smiles NOT NULL,
    links ib_current.ib_privmsg_post_links DEFAULT '1'::ib_current.ib_privmsg_post_links NOT NULL,
    typograf ib_current.ib_privmsg_post_typograf DEFAULT '1'::ib_current.ib_privmsg_post_typograf NOT NULL
);



COMMENT ON TABLE ib_current.ib_privmsg_post IS 'Личные сообщения ';



COMMENT ON COLUMN ib_current.ib_privmsg_post.subscribers IS 'Количество получателей сообщения';



CREATE SEQUENCE ib_current.ib_privmsg_post_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE ib_current.ib_privmsg_post_id_seq OWNED BY ib_current.ib_privmsg_post.id;



CREATE TABLE ib_current.ib_privmsg_thread (
    id bigint NOT NULL,
    title character varying(255) NOT NULL
);



COMMENT ON TABLE ib_current.ib_privmsg_thread IS 'Таблица тем ЛС.';



COMMENT ON COLUMN ib_current.ib_privmsg_thread.title IS 'Название темы ЛС';



CREATE SEQUENCE ib_current.ib_privmsg_thread_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE ib_current.ib_privmsg_thread_id_seq OWNED BY ib_current.ib_privmsg_thread.id;



CREATE TABLE ib_current.ib_privmsg_thread_user (
    pm_thread bigint NOT NULL,
    uid integer NOT NULL,
    total integer DEFAULT 0 NOT NULL,
    unread integer DEFAULT 0 NOT NULL,
    last_post_date bigint DEFAULT '0'::bigint NOT NULL
);



COMMENT ON TABLE ib_current.ib_privmsg_thread_user IS 'Данные о теме, специфичные для пользователя';



COMMENT ON COLUMN ib_current.ib_privmsg_thread_user.total IS 'Количество доступных сообщений';



COMMENT ON COLUMN ib_current.ib_privmsg_thread_user.unread IS 'Количество непрочитанных сообщений';



COMMENT ON COLUMN ib_current.ib_privmsg_thread_user.last_post_date IS 'Идентификатор последнего доступного пользователю сообщения';



CREATE TABLE ib_current.ib_rating (
    id bigint NOT NULL,
    uid integer NOT NULL,
    value double precision NOT NULL,
    "time" bigint NOT NULL,
    ip character varying(255) NOT NULL
);



COMMENT ON TABLE ib_current.ib_rating IS 'Информация о том, когда было отрейтинговано сообщение';



CREATE TABLE ib_current.ib_relation (
    from_ integer NOT NULL,
    "to" integer NOT NULL,
    type ib_current.ib_relation_type NOT NULL
);



CREATE SEQUENCE ib_current.ib_relation_from__seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE ib_current.ib_relation_from__seq OWNED BY ib_current.ib_relation.from_;



CREATE TABLE ib_current.ib_search (
    id bigint NOT NULL,
    owner integer DEFAULT 0,
    output_mode ib_current.ib_search_output_mode DEFAULT 'posts'::ib_current.ib_search_output_mode,
    search_type smallint DEFAULT '1'::smallint,
    query character varying(255) DEFAULT '1'::character varying,
    "time" bigint,
    extdata text
);



COMMENT ON TABLE ib_current.ib_search IS 'Таблица для хранения результатов поиска FULLTEXT или Sphynx';



COMMENT ON COLUMN ib_current.ib_search.id IS 'Номер поиска';



COMMENT ON COLUMN ib_current.ib_search.owner IS 'Id ищущего пользователя';



COMMENT ON COLUMN ib_current.ib_search.output_mode IS 'Режим вывода (темы или сообщения)';



COMMENT ON COLUMN ib_current.ib_search.search_type IS 'Режим поиска (для выдачи информации о запросе при его показе)';



COMMENT ON COLUMN ib_current.ib_search.query IS 'Текст запроса';



COMMENT ON COLUMN ib_current.ib_search."time" IS 'Время запроса';



COMMENT ON COLUMN ib_current.ib_search.extdata IS 'Расширенные данные поиска: дата, время, разделы';



CREATE SEQUENCE ib_current.ib_search_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE ib_current.ib_search_id_seq OWNED BY ib_current.ib_search.id;



CREATE TABLE ib_current.ib_search_result (
    sid bigint NOT NULL,
    oid bigint NOT NULL,
    relevancy double precision DEFAULT '0'::double precision
);



COMMENT ON TABLE ib_current.ib_search_result IS 'Таблица для хранения результатов поиска';



COMMENT ON COLUMN ib_current.ib_search_result.sid IS 'Номер поиска';



COMMENT ON COLUMN ib_current.ib_search_result.oid IS 'Номер найденного сообщения или темы';



COMMENT ON COLUMN ib_current.ib_search_result.relevancy IS 'Релевантность сообщения (или значение для сортировки)';



CREATE TABLE ib_current.ib_smile (
    code character varying(16) NOT NULL,
    file character varying(255) NOT NULL,
    descr character varying(255) NOT NULL,
    mode ib_current.ib_smile_mode DEFAULT 'dropdown'::ib_current.ib_smile_mode NOT NULL,
    sortfield integer DEFAULT 0 NOT NULL
);



COMMENT ON COLUMN ib_current.ib_smile.code IS 'Код смайлика';



COMMENT ON COLUMN ib_current.ib_smile.file IS 'Имя файла со смайликом';



COMMENT ON COLUMN ib_current.ib_smile.descr IS 'Описание того, что смайлик обозначает';



COMMENT ON COLUMN ib_current.ib_smile.mode IS 'Режим отображения смайлика в панели редактора: more -- обычный, dropdown -- в выпадающем списке, hidden -- не отображать';



COMMENT ON COLUMN ib_current.ib_smile.sortfield IS 'Поле для сортировки';



CREATE TABLE ib_current.ib_subaction (
    id integer NOT NULL,
    name character varying(128) NOT NULL,
    module character varying(32) NOT NULL,
    action character varying(32) NOT NULL,
    fid smallint DEFAULT '0'::smallint NOT NULL,
    tid smallint DEFAULT '0'::smallint NOT NULL,
    library character varying(32) NOT NULL,
    proc character varying(32) NOT NULL,
    block character varying(32) NOT NULL,
    active ib_current.ib_subaction_active DEFAULT '1'::ib_current.ib_subaction_active NOT NULL,
    params character varying(255) NOT NULL,
    priority smallint NOT NULL
);



COMMENT ON COLUMN ib_current.ib_subaction.name IS 'Название блока, видимое администратору';



COMMENT ON COLUMN ib_current.ib_subaction.fid IS 'Id раздела, в котором выводится блок, 0 -- во всех';



COMMENT ON COLUMN ib_current.ib_subaction.tid IS 'Id темы, в которой выводится блок, 0 -- во всех';



CREATE SEQUENCE ib_current.ib_subaction_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE ib_current.ib_subaction_id_seq OWNED BY ib_current.ib_subaction.id;



CREATE TABLE ib_current.ib_subactions (
    module bytea NOT NULL,
    action bytea NOT NULL,
    library bytea NOT NULL,
    function bytea NOT NULL,
    active ib_current.ib_subactions_active DEFAULT '1'::ib_current.ib_subactions_active NOT NULL,
    params character varying(255) NOT NULL,
    priority smallint DEFAULT '0'::smallint NOT NULL
);



COMMENT ON TABLE ib_current.ib_subactions IS 'Вспомогательные действия (пока не используется)';



COMMENT ON COLUMN ib_current.ib_subactions.module IS 'Имя модуля. Может содержать одно из зарезервированных ключевых слов: All (все модули), Forum (модуль форумного типа в режиме показа раздела), Topic (модуль форумного типа в режиме показа темы)';



COMMENT ON COLUMN ib_current.ib_subactions.action IS 'Имя основного действия, при котором выполняется данное вспомогательное. Может быть *, если нужно выполнить для всех действий.';



COMMENT ON COLUMN ib_current.ib_subactions.library IS 'Имя библиотеки, в которой лежит обработчик вспомогательного действия';



COMMENT ON COLUMN ib_current.ib_subactions.function IS 'Имя функции в библиотеке, реализующей данное действие';



COMMENT ON COLUMN ib_current.ib_subactions.active IS 'Признак того, что действие включено и должно выполняться';



COMMENT ON COLUMN ib_current.ib_subactions.params IS 'Параметры, передаваемые в функцию';



COMMENT ON COLUMN ib_current.ib_subactions.priority IS 'Приоритет по умолчанию (более высокие выполняются первыми)';



CREATE TABLE ib_current.ib_tagentry (
    tag_id smallint NOT NULL,
    item_id integer NOT NULL
);



CREATE TABLE ib_current.ib_tagname (
    id integer NOT NULL,
    type smallint NOT NULL,
    tagname character varying(32) NOT NULL,
    count integer DEFAULT 0 NOT NULL
);



CREATE SEQUENCE ib_current.ib_tagname_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE ib_current.ib_tagname_id_seq OWNED BY ib_current.ib_tagname.id;



CREATE TABLE ib_current.ib_task (
    id bigint NOT NULL,
    library character varying(24) NOT NULL,
    proc character varying(255) NOT NULL,
    params text NOT NULL,
    nextrun bigint NOT NULL,
    errors smallint DEFAULT '0'::smallint NOT NULL
);



COMMENT ON TABLE ib_current.ib_task IS 'Очередь из разово выполняемых задач';



COMMENT ON COLUMN ib_current.ib_task.id IS 'Номер задачи';



COMMENT ON COLUMN ib_current.ib_task.library IS 'Библиотека, в которой находится выполняемая процедура';



COMMENT ON COLUMN ib_current.ib_task.proc IS 'Название процедуры';



COMMENT ON COLUMN ib_current.ib_task.params IS 'Параметры в сериализованном виде';



COMMENT ON COLUMN ib_current.ib_task.nextrun IS 'Время следующей попытки выполнения';



COMMENT ON COLUMN ib_current.ib_task.errors IS 'Количество ошибок при предыдущих попытках';



CREATE SEQUENCE ib_current.ib_task_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE ib_current.ib_task_id_seq OWNED BY ib_current.ib_task.id;



CREATE TABLE ib_current.ib_text (
    id integer NOT NULL,
    type smallint NOT NULL,
    data text NOT NULL,
    tx_lastmod bigint DEFAULT '0'::bigint NOT NULL
);



COMMENT ON COLUMN ib_current.ib_text.id IS 'Номер раздела';



COMMENT ON COLUMN ib_current.ib_text.type IS 'Тип текста: 0 -- правила, 1 -- объявление, 2 -- текст статического раздела';



COMMENT ON COLUMN ib_current.ib_text.data IS 'Текст';



CREATE TABLE ib_current.ib_timeout (
    "time" bigint NOT NULL,
    action character varying(32) NOT NULL,
    uid integer NOT NULL,
    ip bigint NOT NULL
);



COMMENT ON TABLE ib_current.ib_timeout IS 'Таблица хранения таймаутов между действиями (типа регистраци';



COMMENT ON COLUMN ib_current.ib_timeout."time" IS 'Время последней попытки совершения действия';



COMMENT ON COLUMN ib_current.ib_timeout.action IS 'Условное название действия';



COMMENT ON COLUMN ib_current.ib_timeout.uid IS 'Идентификатор пользователя';



CREATE TABLE ib_current.ib_topic (
    id integer NOT NULL,
    fid integer NOT NULL,
    title character varying(80) NOT NULL,
    descr character varying(255) NOT NULL,
    status ib_current.ib_topic_status DEFAULT '0'::ib_current.ib_topic_status NOT NULL,
    hurl character varying(255) DEFAULT ''::character varying NOT NULL,
    locked ib_current.ib_topic_locked DEFAULT '0'::ib_current.ib_topic_locked NOT NULL,
    first_post_id bigint DEFAULT '0'::bigint NOT NULL,
    last_post_id bigint DEFAULT '0'::bigint NOT NULL,
    lastmod bigint NOT NULL,
    post_count integer DEFAULT 0 NOT NULL,
    flood_count integer DEFAULT 0 NOT NULL,
    valued_count integer DEFAULT 0 NOT NULL,
    owner integer DEFAULT 0 NOT NULL,
    sticky ib_current.ib_topic_sticky DEFAULT '0'::ib_current.ib_topic_sticky NOT NULL,
    sticky_post ib_current.ib_topic_sticky_post DEFAULT '0'::ib_current.ib_topic_sticky_post NOT NULL,
    favorites ib_current.ib_topic_favorites DEFAULT '0'::ib_current.ib_topic_favorites NOT NULL,
    ext_status bigint DEFAULT '0'::bigint NOT NULL,
    last_post_time bigint DEFAULT '0'::bigint NOT NULL,
    rating integer DEFAULT 0 NOT NULL
);



COMMENT ON COLUMN ib_current.ib_topic.fid IS 'Идентификатор раздела, в котором находится тема';



COMMENT ON COLUMN ib_current.ib_topic.title IS 'Название темы';



COMMENT ON COLUMN ib_current.ib_topic.descr IS 'Описание темы';



COMMENT ON COLUMN ib_current.ib_topic.status IS 'Статус темы: 0 -- нормальная, 1 -- на премодерации, 2 -- удалена';



COMMENT ON COLUMN ib_current.ib_topic.hurl IS 'HURL темы (без HURL раздела)';



COMMENT ON COLUMN ib_current.ib_topic.locked IS 'Тема закрыта';



COMMENT ON COLUMN ib_current.ib_topic.first_post_id IS 'Первое сообщение темы';



COMMENT ON COLUMN ib_current.ib_topic.last_post_id IS 'Второе сообщение темы';



COMMENT ON COLUMN ib_current.ib_topic.lastmod IS 'Время последнего изменения темы (отправки сообщения, редактирования или модерации)';



COMMENT ON COLUMN ib_current.ib_topic.post_count IS 'Отображаемое количество сообщений';



COMMENT ON COLUMN ib_current.ib_topic.flood_count IS 'Количество сообщений, помеченных как флуд';



COMMENT ON COLUMN ib_current.ib_topic.valued_count IS 'Количество ценных сообщений';



COMMENT ON COLUMN ib_current.ib_topic.owner IS 'Владелец темы (обычно автор первого сообщения)';



COMMENT ON COLUMN ib_current.ib_topic.sticky IS 'Тема является прикленной';



COMMENT ON COLUMN ib_current.ib_topic.sticky_post IS 'Показывать ли первое сообщение темы на каждой странице';



COMMENT ON COLUMN ib_current.ib_topic.favorites IS 'Тема есть в "лучших темах форума"';



COMMENT ON COLUMN ib_current.ib_topic.ext_status IS 'Расширенный статус (используется специализированными разделами)';



COMMENT ON COLUMN ib_current.ib_topic.last_post_time IS 'Время отправки последнего сообщения (сделано в целях оптимизации нагрузки на БД)';



COMMENT ON COLUMN ib_current.ib_topic.rating IS 'Суммарный рейтинг темы';



CREATE SEQUENCE ib_current.ib_topic_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE ib_current.ib_topic_id_seq OWNED BY ib_current.ib_topic.id;



CREATE TABLE ib_current.ib_user (
    id integer NOT NULL,
    login character varying(32) NOT NULL,
    password character varying(255) NOT NULL,
    pass_crypt smallint NOT NULL,
    title character varying(80) DEFAULT ''::character varying NOT NULL,
    gender ib_current.ib_user_gender DEFAULT 'U'::ib_current.ib_user_gender NOT NULL,
    birthdate date,
    location character varying(80) NOT NULL,
    status smallint DEFAULT '0'::smallint NOT NULL,
    canonical character varying(255) NOT NULL,
    signature character varying(255) NOT NULL,
    rnd bigint NOT NULL,
    display_name character varying(32) NOT NULL,
    avatar ib_current.ib_user_avatar DEFAULT 'none'::ib_current.ib_user_avatar NOT NULL,
    photo ib_current.ib_user_photo DEFAULT 'none'::ib_current.ib_user_photo NOT NULL,
    email character varying(255) NOT NULL,
    real_name character varying(255) DEFAULT ''::character varying NOT NULL
);



COMMENT ON COLUMN ib_current.ib_user.real_name IS 'Реальное имя пользователя, если он захочет его указать';



CREATE TABLE ib_current.ib_user_award (
    id integer NOT NULL,
    uid integer NOT NULL,
    file character varying(255) NOT NULL,
    descr character varying(255) NOT NULL,
    "time" bigint NOT NULL
);



COMMENT ON COLUMN ib_current.ib_user_award.id IS 'Идентификатор';



COMMENT ON COLUMN ib_current.ib_user_award.uid IS 'Идентификатор награжденного пользователя';



COMMENT ON COLUMN ib_current.ib_user_award.file IS 'Имя файла с изображением награды';



COMMENT ON COLUMN ib_current.ib_user_award.descr IS 'Описание причины для награждения';



COMMENT ON COLUMN ib_current.ib_user_award."time" IS 'Дата награждения';



CREATE SEQUENCE ib_current.ib_user_award_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE ib_current.ib_user_award_id_seq OWNED BY ib_current.ib_user_award.id;



CREATE TABLE ib_current.ib_user_contact (
    uid integer NOT NULL,
    cid smallint NOT NULL,
    value character varying(80) NOT NULL
);



CREATE TABLE ib_current.ib_user_contact_type (
    cid integer NOT NULL,
    c_title character varying(80) NOT NULL,
    icon character varying(255) NOT NULL,
    link character varying(255) NOT NULL,
    c_sort smallint NOT NULL,
    c_name character varying(32) NOT NULL,
    c_permission ib_current.ib_user_contact_type_c_permission DEFAULT '0'::ib_current.ib_user_contact_type_c_permission NOT NULL
);



COMMENT ON COLUMN ib_current.ib_user_contact_type.c_title IS 'Название контакта или социальной сети';



COMMENT ON COLUMN ib_current.ib_user_contact_type.icon IS 'URL значка контакта или социальной сети';



COMMENT ON COLUMN ib_current.ib_user_contact_type.link IS 'Ссылка на контакт или профиль соцсети';



COMMENT ON COLUMN ib_current.ib_user_contact_type.c_sort IS 'Поле для сортировки';



COMMENT ON COLUMN ib_current.ib_user_contact_type.c_name IS 'Идентификатор для библиотеки авторизации через соцсети';



COMMENT ON COLUMN ib_current.ib_user_contact_type.c_permission IS 'Нужна ли проверка на наличие прав размещать ссылки при выводе этого контакта';



CREATE SEQUENCE ib_current.ib_user_contact_type_cid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE ib_current.ib_user_contact_type_cid_seq OWNED BY ib_current.ib_user_contact_type.cid;



CREATE TABLE ib_current.ib_user_ext (
    id integer NOT NULL,
    post_count bigint DEFAULT '0'::bigint NOT NULL,
    rating double precision DEFAULT '0'::double precision NOT NULL,
    warnings smallint DEFAULT '0'::smallint NOT NULL,
    balance numeric(10,0) DEFAULT '0'::numeric NOT NULL,
    banned_till bigint DEFAULT '0'::bigint NOT NULL,
    group_id smallint DEFAULT '0'::smallint NOT NULL,
    reg_date bigint DEFAULT '0'::bigint NOT NULL,
    reg_ip character varying(255) DEFAULT '0'::character varying NOT NULL
);



COMMENT ON COLUMN ib_current.ib_user_ext.post_count IS 'Количество сообщений';



COMMENT ON COLUMN ib_current.ib_user_ext.rating IS 'Суммарный рейтинг';



COMMENT ON COLUMN ib_current.ib_user_ext.warnings IS 'Сумма баллов предупреждений';



COMMENT ON COLUMN ib_current.ib_user_ext.balance IS 'Баланс (сейчас не используется)';



COMMENT ON COLUMN ib_current.ib_user_ext.banned_till IS 'Если пользователь изгнан, дата окончания срока';



COMMENT ON COLUMN ib_current.ib_user_ext.group_id IS 'Группа прав доступа пользователя';



COMMENT ON COLUMN ib_current.ib_user_ext.reg_date IS 'Дата регистрации';



COMMENT ON COLUMN ib_current.ib_user_ext.reg_ip IS 'IP, с которого произведена регистрация';



CREATE TABLE ib_current.ib_user_field (
    id integer NOT NULL,
    title character varying(60) NOT NULL,
    type ib_current.ib_user_field_type DEFAULT 'text'::ib_current.ib_user_field_type NOT NULL,
    "values" text NOT NULL,
    in_msg ib_current.ib_user_field_in_msg DEFAULT '1'::ib_current.ib_user_field_in_msg NOT NULL,
    sortfield integer NOT NULL
);



COMMENT ON TABLE ib_current.ib_user_field IS 'Задаваемые поля для профиля пользователя';



COMMENT ON COLUMN ib_current.ib_user_field.id IS 'Идентификатор поля';



COMMENT ON COLUMN ib_current.ib_user_field.title IS 'Название поля';



COMMENT ON COLUMN ib_current.ib_user_field.type IS 'Тип поля: текст, числовое значение, выбор значения из списка типа Select или переключателей Radio';



COMMENT ON COLUMN ib_current.ib_user_field."values" IS 'Список возможных значений для select и radio, для text -- регулярное выражение для проверки корректности ввода';



COMMENT ON COLUMN ib_current.ib_user_field.in_msg IS 'Выводить значение поля при показе сообщений пользователя или только в его профиле';



COMMENT ON COLUMN ib_current.ib_user_field.sortfield IS 'Поле для сортировки';



CREATE SEQUENCE ib_current.ib_user_field_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE ib_current.ib_user_field_id_seq OWNED BY ib_current.ib_user_field.id;



CREATE SEQUENCE ib_current.ib_user_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE ib_current.ib_user_id_seq OWNED BY ib_current.ib_user.id;



CREATE TABLE ib_current.ib_user_settings (
    id integer NOT NULL,
    topics_per_page smallint DEFAULT '10'::smallint NOT NULL,
    posts_per_page smallint DEFAULT '20'::smallint NOT NULL,
    template character varying(20) DEFAULT ''::character varying NOT NULL,
    msg_order ib_current.ib_user_settings_msg_order DEFAULT 'ASC'::ib_current.ib_user_settings_msg_order NOT NULL,
    subscribe ib_current.ib_user_settings_subscribe DEFAULT 'None'::ib_current.ib_user_settings_subscribe NOT NULL,
    timezone smallint DEFAULT '10800'::smallint NOT NULL,
    signatures ib_current.ib_user_settings_signatures DEFAULT '1'::ib_current.ib_user_settings_signatures NOT NULL,
    avatars ib_current.ib_user_settings_avatars DEFAULT '1'::ib_current.ib_user_settings_avatars NOT NULL,
    smiles ib_current.ib_user_settings_smiles DEFAULT '1'::ib_current.ib_user_settings_smiles NOT NULL,
    pics ib_current.ib_user_settings_pics DEFAULT '1'::ib_current.ib_user_settings_pics NOT NULL,
    longposts ib_current.ib_user_settings_longposts DEFAULT '0'::ib_current.ib_user_settings_longposts NOT NULL,
    show_birthdate ib_current.ib_user_settings_show_birthdate DEFAULT '3'::ib_current.ib_user_settings_show_birthdate NOT NULL,
    subscribe_mode smallint DEFAULT '0'::smallint NOT NULL,
    email_fulltext ib_current.ib_user_settings_email_fulltext DEFAULT '1'::ib_current.ib_user_settings_email_fulltext NOT NULL,
    email_pm ib_current.ib_user_settings_email_pm DEFAULT '1'::ib_current.ib_user_settings_email_pm NOT NULL,
    email_message ib_current.ib_user_settings_email_message DEFAULT '1'::ib_current.ib_user_settings_email_message NOT NULL,
    email_broadcasts ib_current.ib_user_settings_email_broadcasts DEFAULT '1'::ib_current.ib_user_settings_email_broadcasts NOT NULL,
    flood_limit smallint DEFAULT '50'::smallint NOT NULL,
    topics_period integer DEFAULT 0 NOT NULL,
    hidden ib_current.ib_user_settings_hidden DEFAULT '0'::ib_current.ib_user_settings_hidden NOT NULL,
    wysiwyg ib_current.ib_user_settings_wysiwyg DEFAULT '1'::ib_current.ib_user_settings_wysiwyg NOT NULL,
    goto ib_current.ib_user_settings_goto DEFAULT '0'::ib_current.ib_user_settings_goto NOT NULL
);



COMMENT ON COLUMN ib_current.ib_user_settings.topics_per_page IS 'Тем на странице';



COMMENT ON COLUMN ib_current.ib_user_settings.posts_per_page IS 'Сообщений на странице';



COMMENT ON COLUMN ib_current.ib_user_settings.template IS 'Используемый шаблон';



COMMENT ON COLUMN ib_current.ib_user_settings.msg_order IS 'Порядок сортировки сообщений в теме';



COMMENT ON COLUMN ib_current.ib_user_settings.subscribe IS 'Подписка на обновления: нет, только на созданные темы, на все темы, в которых пользователь пишет ответ';



COMMENT ON COLUMN ib_current.ib_user_settings.timezone IS 'Часовой пояс участника (смещение в секундах)';



COMMENT ON COLUMN ib_current.ib_user_settings.signatures IS 'Показывать подписи';



COMMENT ON COLUMN ib_current.ib_user_settings.avatars IS 'Показывать аватары';



COMMENT ON COLUMN ib_current.ib_user_settings.smiles IS 'Показывать смайлики';



COMMENT ON COLUMN ib_current.ib_user_settings.pics IS 'Показывать прикрепленные и вставленные изображения';



COMMENT ON COLUMN ib_current.ib_user_settings.longposts IS 'Сворачивать длинные сообщения: 0 -- никогда, 1 -- да, 2 -- только  помеченные как флуд';



COMMENT ON COLUMN ib_current.ib_user_settings.show_birthdate IS 'Показывать дату рождения (0 -- нет, 1 -- да, 2 -- только дату, 3 -- только возраст)';



COMMENT ON COLUMN ib_current.ib_user_settings.subscribe_mode IS 'Режим рассылки уведомлений';



COMMENT ON COLUMN ib_current.ib_user_settings.email_fulltext IS 'Отправлять полный текст сообщения на почту';



COMMENT ON COLUMN ib_current.ib_user_settings.email_pm IS 'Отправлять увеедомления о новых личных сообщениях';



COMMENT ON COLUMN ib_current.ib_user_settings.email_message IS 'Разрешить отправку сообщений через форму на сайте';



COMMENT ON COLUMN ib_current.ib_user_settings.email_broadcasts IS 'Получать рассылки от администратора';



COMMENT ON COLUMN ib_current.ib_user_settings.flood_limit IS 'Порог (в процентах), после которого тема считается зафлуженной';



COMMENT ON COLUMN ib_current.ib_user_settings.topics_period IS 'Период (в часах) за который выводятся темы на форуме. 0 -- выдача за все время';



COMMENT ON COLUMN ib_current.ib_user_settings.hidden IS '"Скрытный пользователь" (не показывать в списке присутствующих)';



COMMENT ON COLUMN ib_current.ib_user_settings.wysiwyg IS 'Режим работы визуального редактора: 0 -- выключен, 1 -- вставка тегов без визуализации, 2 -- полностью визуальный (TinyMCE)';



COMMENT ON COLUMN ib_current.ib_user_settings.goto IS 'Переход после отправки сообщения: 0 -- в тему, 1 -- в раздел, 2 -- к "Обновившимся", 3 -- к "Непрочитанным"';



CREATE TABLE ib_current.ib_user_value (
    uid integer NOT NULL,
    fdid integer NOT NULL,
    value character varying(255) NOT NULL
);



COMMENT ON TABLE ib_current.ib_user_value IS 'Значения задаваемых полей профиля пользователя';



COMMENT ON COLUMN ib_current.ib_user_value.uid IS 'Идентификатор пользователя';



COMMENT ON COLUMN ib_current.ib_user_value.fdid IS 'Идентификатор задаваемого поля';



COMMENT ON COLUMN ib_current.ib_user_value.value IS 'Значение задаваемого поля';



CREATE TABLE ib_current.ib_user_warning (
    id integer NOT NULL,
    uid integer DEFAULT 0 NOT NULL,
    warntime bigint DEFAULT '0'::bigint NOT NULL,
    moderator integer DEFAULT 0 NOT NULL,
    pid bigint DEFAULT '0'::bigint NOT NULL,
    value smallint DEFAULT '0'::smallint NOT NULL,
    warntill bigint DEFAULT '0'::bigint NOT NULL,
    descr text NOT NULL
);



COMMENT ON TABLE ib_current.ib_user_warning IS 'Предупреждения и наказания пользователей';



COMMENT ON COLUMN ib_current.ib_user_warning.uid IS 'Идентификатор пользователя, которому вынесено предупреждение';



COMMENT ON COLUMN ib_current.ib_user_warning.warntime IS 'Время вынесения';



COMMENT ON COLUMN ib_current.ib_user_warning.moderator IS 'Идентификатор модератора, вынесшего предупреждение';



COMMENT ON COLUMN ib_current.ib_user_warning.pid IS 'Идентификатор сообщения, за которое вынесено предупреждение';



COMMENT ON COLUMN ib_current.ib_user_warning.value IS 'Количество штрафных баллов';



COMMENT ON COLUMN ib_current.ib_user_warning.warntill IS 'Дата окончания действия предупреждения';



COMMENT ON COLUMN ib_current.ib_user_warning.descr IS 'Комментарий модератора';



CREATE SEQUENCE ib_current.ib_user_warning_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE ib_current.ib_user_warning_id_seq OWNED BY ib_current.ib_user_warning.id;



CREATE TABLE ib_current.ib_views (
    oid integer NOT NULL,
    type ib_current.ib_views_type DEFAULT 'topic'::ib_current.ib_views_type NOT NULL,
    views bigint DEFAULT '0'::bigint NOT NULL
);



COMMENT ON COLUMN ib_current.ib_views.oid IS 'Номер объекта';



COMMENT ON COLUMN ib_current.ib_views.type IS 'Для какого объекта указаны просмотры: раздел или тема';



COMMENT ON COLUMN ib_current.ib_views.views IS 'Количество просмотров';



CREATE TABLE ib_current.ib_vote (
    tid integer NOT NULL,
    uid integer NOT NULL,
    pvid bigint NOT NULL,
    "time" bigint NOT NULL,
    ip character varying(255) NOT NULL
);



COMMENT ON TABLE ib_current.ib_vote IS 'Результаты голосования отдельных пользователей';



COMMENT ON COLUMN ib_current.ib_vote.tid IS 'Номер темы с опросом';



COMMENT ON COLUMN ib_current.ib_vote.uid IS 'Идентификатор пользователя';



COMMENT ON COLUMN ib_current.ib_vote.pvid IS 'Идентификатор варианта ответа';



COMMENT ON COLUMN ib_current.ib_vote."time" IS 'Время голосования';



COMMENT ON COLUMN ib_current.ib_vote.ip IS 'IP, с которого производилось голосование';



CREATE SEQUENCE ib_current.ib_vote_tid_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;



ALTER SEQUENCE ib_current.ib_vote_tid_seq OWNED BY ib_current.ib_vote.tid;



ALTER TABLE ONLY ib_current.ib_bots ALTER COLUMN id SET DEFAULT nextval('ib_current.ib_bots_id_seq'::regclass);



ALTER TABLE ONLY ib_current.ib_category ALTER COLUMN id SET DEFAULT nextval('ib_current.ib_category_id_seq'::regclass);



ALTER TABLE ONLY ib_current.ib_complain ALTER COLUMN id SET DEFAULT nextval('ib_current.ib_complain_id_seq'::regclass);



ALTER TABLE ONLY ib_current.ib_crontab ALTER COLUMN id SET DEFAULT nextval('ib_current.ib_crontab_id_seq'::regclass);



ALTER TABLE ONLY ib_current.ib_forum ALTER COLUMN id SET DEFAULT nextval('ib_current.ib_forum_id_seq'::regclass);



ALTER TABLE ONLY ib_current.ib_log_action ALTER COLUMN id SET DEFAULT nextval('ib_current.ib_log_action_id_seq'::regclass);



ALTER TABLE ONLY ib_current.ib_menu ALTER COLUMN id SET DEFAULT nextval('ib_current.ib_menu_id_seq'::regclass);



ALTER TABLE ONLY ib_current.ib_menu_item ALTER COLUMN id SET DEFAULT nextval('ib_current.ib_menu_item_id_seq'::regclass);



ALTER TABLE ONLY ib_current.ib_poll_variant ALTER COLUMN id SET DEFAULT nextval('ib_current.ib_poll_variant_id_seq'::regclass);



ALTER TABLE ONLY ib_current.ib_post ALTER COLUMN id SET DEFAULT nextval('ib_current.ib_post_id_seq'::regclass);



ALTER TABLE ONLY ib_current.ib_privmsg_post ALTER COLUMN id SET DEFAULT nextval('ib_current.ib_privmsg_post_id_seq'::regclass);



ALTER TABLE ONLY ib_current.ib_privmsg_thread ALTER COLUMN id SET DEFAULT nextval('ib_current.ib_privmsg_thread_id_seq'::regclass);



ALTER TABLE ONLY ib_current.ib_relation ALTER COLUMN from_ SET DEFAULT nextval('ib_current.ib_relation_from__seq'::regclass);



ALTER TABLE ONLY ib_current.ib_search ALTER COLUMN id SET DEFAULT nextval('ib_current.ib_search_id_seq'::regclass);



ALTER TABLE ONLY ib_current.ib_subaction ALTER COLUMN id SET DEFAULT nextval('ib_current.ib_subaction_id_seq'::regclass);



ALTER TABLE ONLY ib_current.ib_tagname ALTER COLUMN id SET DEFAULT nextval('ib_current.ib_tagname_id_seq'::regclass);



ALTER TABLE ONLY ib_current.ib_task ALTER COLUMN id SET DEFAULT nextval('ib_current.ib_task_id_seq'::regclass);



ALTER TABLE ONLY ib_current.ib_topic ALTER COLUMN id SET DEFAULT nextval('ib_current.ib_topic_id_seq'::regclass);



ALTER TABLE ONLY ib_current.ib_user ALTER COLUMN id SET DEFAULT nextval('ib_current.ib_user_id_seq'::regclass);



ALTER TABLE ONLY ib_current.ib_user_award ALTER COLUMN id SET DEFAULT nextval('ib_current.ib_user_award_id_seq'::regclass);



ALTER TABLE ONLY ib_current.ib_user_contact_type ALTER COLUMN cid SET DEFAULT nextval('ib_current.ib_user_contact_type_cid_seq'::regclass);



ALTER TABLE ONLY ib_current.ib_user_field ALTER COLUMN id SET DEFAULT nextval('ib_current.ib_user_field_id_seq'::regclass);



ALTER TABLE ONLY ib_current.ib_user_warning ALTER COLUMN id SET DEFAULT nextval('ib_current.ib_user_warning_id_seq'::regclass);



ALTER TABLE ONLY ib_current.ib_vote ALTER COLUMN tid SET DEFAULT nextval('ib_current.ib_vote_tid_seq'::regclass);



INSERT INTO ib_current.ib_access VALUES (0, 0, '1', '1', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');
INSERT INTO ib_current.ib_access VALUES (50, 0, '1', '1', '1', '0', '1', '0', '0', '0', '0', '0', '1', '1');
INSERT INTO ib_current.ib_access VALUES (100, 0, '1', '1', '1', '1', '1', '0', '0', '1', '0', '1', '1', '1');
INSERT INTO ib_current.ib_access VALUES (120, 0, '1', '1', '1', '1', '1', '0', '0', '1', '0', '1', '1', '1');
INSERT INTO ib_current.ib_access VALUES (140, 0, '1', '1', '1', '1', '1', '1', '0', '1', '1', '1', '1', '1');
INSERT INTO ib_current.ib_access VALUES (160, 0, '1', '1', '1', '1', '1', '1', '0', '1', '1', '1', '1', '1');
INSERT INTO ib_current.ib_access VALUES (180, 0, '1', '1', '1', '1', '1', '1', '0', '1', '1', '1', '1', '1');
INSERT INTO ib_current.ib_access VALUES (499, 0, '1', '1', '1', '1', '1', '1', '0', '1', '1', '1', '1', '1');
INSERT INTO ib_current.ib_access VALUES (500, 0, '1', '1', '1', '1', '1', '1', '0', '1', '1', '1', '1', '1');
INSERT INTO ib_current.ib_access VALUES (1000, 0, '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1');
INSERT INTO ib_current.ib_access VALUES (1024, 0, '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1');






INSERT INTO ib_current.ib_bots VALUES (1, 'YandexBot', 'Яндекс', 0);
INSERT INTO ib_current.ib_bots VALUES (2, 'Googlebot', 'Google', 0);
INSERT INTO ib_current.ib_bots VALUES (3, 'bingbot', 'Bing!', 0);
INSERT INTO ib_current.ib_bots VALUES (5, 'Yahoo! Slurp', 'Yahoo!', 0);
INSERT INTO ib_current.ib_bots VALUES (6, 'mail.ru', '@Mail.Ru', 0);
INSERT INTO ib_current.ib_bots VALUES (7, 'W3C_Validator', 'W3C Validator', 0);












INSERT INTO ib_current.ib_crontab VALUES (1, 'antibot', 'captcha_clear', '24', 'Очистка старых данных CAPTCHA', 0, 32768);
INSERT INTO ib_current.ib_crontab VALUES (2, 'maintain', 'log_rotate', '5', 'Ротация логов в каталоге logs', 0, 1440);
INSERT INTO ib_current.ib_crontab VALUES (3, 'antibot', 'timeout_clear', '24', 'Очистка старых данных о таймаутах', 0, 1441);
INSERT INTO ib_current.ib_crontab VALUES (4, 'maintain', 'search_results_clear', '7', 'Очистка старых результатов поиска', 0, 1443);
INSERT INTO ib_current.ib_crontab VALUES (5, 'maintain', 'mod_logs_clear', '90', 'Удаление старых данных о модераторских действиях', 0, 10079);
INSERT INTO ib_current.ib_crontab VALUES (6, 'maintain', 'light_optimize', '', 'Малая оптимизация баз данных (только часто изменяемые таблицы)', 0, 4300);
INSERT INTO ib_current.ib_crontab VALUES (7, 'maintain', 'heavy_optimize', '', 'Полная оптимизация базы данных (все таблицы)', 0, 44643);
INSERT INTO ib_current.ib_crontab VALUES (8, 'maintain', 'update_mark_all', '90', 'Отметка прочитанными всех тем, которые обновились, но не были просмотрены в течение заданного количества дней', 0, 10081);
INSERT INTO ib_current.ib_crontab VALUES (9, 'delete', 'inactive_users_clear', '30', 'Удаление пользователей, не активировавших свой профиль в течение указанного количества дней', 0, 4320);
INSERT INTO ib_current.ib_crontab VALUES (10, 'maintain', 'online_clear', '3', 'Очистка списка последних действий пользователей', 0, 1440);
INSERT INTO ib_current.ib_crontab VALUES (11, 'instagram', 'getdata', 'вставьте свой token', 'Обновление списка фотографий из Instagram', 0, 60);
INSERT INTO ib_current.ib_crontab VALUES (12, 'sitemap', 'generate', '', 'Генерация файла sitemap.xml', 0, 180);
INSERT INTO ib_current.ib_crontab VALUES (13, 'instagram', 'refresh', '', 'Обновление access token для Instagram', 0, 10080);






INSERT INTO ib_current.ib_forum VALUES (1, 'statpage', 'О проекте', 'Информация о нашем сайте', 'about', 0, 0, 0, 1, '0', 0, 0, '', '0', '1', 16, 0, 255, 0, 0, '1', '0', '1', '', '', 'DESC', 'last_post_time', '1', 0, '2', '2', 0, 0, '0', '1', '0');



INSERT INTO ib_current.ib_forum_type VALUES ('anon', 'Анонимный форум', '1', '1', '1', '0', '0', 3, '^<<<hurl>>>/((\d+)\.htm)?$ anon.php?f=<<<id>>>&a=view_forum&page=$2
^<<<hurl>>>/((\w+)\.htm)?$ anon.php?f=<<<id>>>&a=$2
^<<<hurl>>>/([\w\-\d]+)/((\d+)\.htm)?$ anon.php?f=<<<id>>>&t=$1&a=view_topic&page=$3
^<<<hurl>>>/([\w\-\d]+)/(\w+)\.htm$ anon.php?f=<<<id>>>&t=$1&a=$2
^moderate/<<<hurl>>>/((\w+)/)?(\w+)\.htm$ moderate.php?f=<<<id>>>&a=$3&t=$2
', '0');
INSERT INTO ib_current.ib_forum_type VALUES ('blog', 'Блог или новости', '1', '1', '1', '0', '1', 6, '^<<<hurl>>>/((\d+)\.htm)?$ blog.php?f=<<<id>>>&a=view_forum&page=$2
^<<<hurl>>>/((\w+)\.htm)?$ blog.php?f=<<<id>>>&a=$2
^<<<hurl>>>/([\w\-\d]+)/((\d+)\.htm)?$ blog.php?f=<<<id>>>&t=$1&a=view_topic&page=$3
^<<<hurl>>>/([\w\-\d]+)/(\w+)\.htm$ blog.php?f=<<<id>>>&t=$1&a=$2
^moderate/<<<hurl>>>/((\w+)/)?(\w+)\.htm$ moderate.php?f=<<<id>>>&a=$3&t=$2
', '0');
INSERT INTO ib_current.ib_forum_type VALUES ('gallery', 'Фотогалерея', '1', '1', '1', '0', '1', 6, '^<<<hurl>>>/((\d+)\.htm)?$ gallery.php?f=<<<id>>>&a=view_forum&page=$2
^<<<hurl>>>/((\w+)\.htm)?$ gallery.php?f=<<<id>>>&a=$2
^<<<hurl>>>/([\w\-\d]+)/((\d+)\.htm)?$ gallery.php?f=<<<id>>>&t=$1&a=view_topic&page=$3
^<<<hurl>>>/([\w\-\d]+)/(\w+)\.htm$ gallery.php?f=<<<id>>>&t=$1&a=$2
^moderate/<<<hurl>>>/((\w+)/)?(\w+)\.htm$ moderate.php?f=<<<id>>>&a=$3&t=$2
', '0');
INSERT INTO ib_current.ib_forum_type VALUES ('link', 'Ссылка на внешний ресурс', '0', '0', '0', '0', '0', 4, '^<<<hurl>>>/?$ link.php?f=<<<id>>>&a=view', '1');
INSERT INTO ib_current.ib_forum_type VALUES ('micro', 'Микроблог', '1', '1', '1', '0', '1', 7, '^<<<hurl>>>/((\w+)\.htm)?$ micro.php?f=<<<id>>>&a=$2
^moderate/<<<hurl>>>/edit_foreword.htm$ moderate.php?f=<<<id>>>&a=edit_foreword
^moderate/<<<hurl>>>/((\w+)/)?(\w+)\.htm$ moderate.php?f=<<<id>>>&a=$3&t=$2
', '1');
INSERT INTO ib_current.ib_forum_type VALUES ('statpage', 'Статическая страница', '0', '1', '1', '1', '0', 2, '^<<<hurl>>>/((\w+)\.htm)?$ statpage.php?f=<<<id>>>&a=$2
^moderate/<<<hurl>>>/edit_foreword.htm$ statpage.php?f=<<<id>>>&a=edit
', '1');
INSERT INTO ib_current.ib_forum_type VALUES ('stdforum', 'Обычный форум', '1', '1', '1', '1', '0', 1, '^<<<hurl>>>/((\d+)\.htm)?$ stdforum.php?f=<<<id>>>&a=view_forum&page=$2
^<<<hurl>>>/((\w+)\.htm)?$ stdforum.php?f=<<<id>>>&a=$2
^<<<hurl>>>/([\w\-\d]+)/((\d+)\.htm)?$ stdforum.php?f=<<<id>>>&t=$1&a=view_topic&page=$3
^<<<hurl>>>/([\w\-\d]+)/(\w+)\.htm$ stdforum.php?f=<<<id>>>&t=$1&a=$2
^<<<hurl>>>/([\w\-\d]+)/(\w+)\.htm$ stdforum.php?f=<<<id>>>&t=$1&a=$2
^<<<hurl>>>/([\w\-\d]+)/post-(\d+)\.htm$ stdforum.php?f=<<<id>>>&t=$1&a=post&post=$2 
^moderate/<<<hurl>>>/(([\w\-\d]+)/)?(\w+)\.htm$ moderate.php?f=<<<id>>>&a=$3&t=$2', '0');



INSERT INTO ib_current.ib_group VALUES (0, 'Гость', '1', 90, 0, 2048, 0, '0', '0', '0', '0', 0, 'nofollow');
INSERT INTO ib_current.ib_group VALUES (50, 'Сомнительный тип', '1', 90, 3, 0, 0, '0', '0', '0', '0', 0, 'none');
INSERT INTO ib_current.ib_group VALUES (100, 'Новичок', '0', 30, 6, 1024, 0, '0', '0', '0', '0', 0, 'none');
INSERT INTO ib_current.ib_group VALUES (120, 'Начинающий', '0', 30, 10, 1024, 5, '0', '0', '0', '0', 2, 'nofollow');
INSERT INTO ib_current.ib_group VALUES (140, 'Участник', '0', 10, 25, 2048, 25, '0', '0', '0', '0', 4, 'nofollow');
INSERT INTO ib_current.ib_group VALUES (160, 'Почетный участник', '0', 5, 60, 2048, 100, '1', '0', '0', '0', 7, 'allow');
INSERT INTO ib_current.ib_group VALUES (180, 'Долгожитель форума', '0', 3, 240, 4096, 500, '1', '0', '0', '0', 30, 'allow');
INSERT INTO ib_current.ib_group VALUES (499, 'Участник команды', '1', 0, 0, 4096, 0, '1', '0', '1', '0', 0, 'allow');
INSERT INTO ib_current.ib_group VALUES (500, 'Модератор', '1', 0, 0, 4096, 0, '1', '0', '1', '0', 0, 'allow');
INSERT INTO ib_current.ib_group VALUES (1000, 'Администратор', '1', 0, 0, 65535, 0, '1', '1', '1', '1', 0, 'allow');
INSERT INTO ib_current.ib_group VALUES (1024, 'Создатель форума', '1', 0, 0, 65535, 0, '1', '1', '1', '1', 0, 'allow');












INSERT INTO ib_current.ib_menu VALUES (1, 'Главное меню', '1');
INSERT INTO ib_current.ib_menu VALUES (2, 'Меню Центра Администрирования', '1');



INSERT INTO ib_current.ib_menu_item VALUES (1, 1, 'О проекте', 'about/', 1, '1', '1', '1', '1');
INSERT INTO ib_current.ib_menu_item VALUES (2, 1, 'Правила', 'rules.htm', 2, '1', '1', '1', '1');
INSERT INTO ib_current.ib_menu_item VALUES (3, 1, 'Участники', 'users/', 5, '1', '1', '1', '1');
INSERT INTO ib_current.ib_menu_item VALUES (4, 1, 'Команда', 'team.htm', 4, '1', '1', '1', '1');
INSERT INTO ib_current.ib_menu_item VALUES (5, 1, 'Последние сообщения', 'newtopics/', 3, '1', '1', '1', '1');
INSERT INTO ib_current.ib_menu_item VALUES (6, 1, 'Поиск', 'search/', 7, '1', '1', '1', '1');
INSERT INTO ib_current.ib_menu_item VALUES (7, 1, 'Справка', 'help/', 8, '1', '1', '1', '1');
INSERT INTO ib_current.ib_menu_item VALUES (8, 1, 'Сейчас присутствуют', 'online/', 6, '1', '1', '1', '1');
















































INSERT INTO ib_current.ib_smile VALUES ('8-)', 'cool.png', '', 'dropdown', 4);
INSERT INTO ib_current.ib_smile VALUES (':''(', 'cwy.png', '', 'dropdown', 5);
INSERT INTO ib_current.ib_smile VALUES (':(', 'sad.png', '', 'dropdown', 9);
INSERT INTO ib_current.ib_smile VALUES (':)', 'smile.png', '', 'dropdown', 1);
INSERT INTO ib_current.ib_smile VALUES (':alien:', 'alien.png', '', 'more', 100);
INSERT INTO ib_current.ib_smile VALUES (':angel:', 'angel.png', '', 'dropdown', 2);
INSERT INTO ib_current.ib_smile VALUES (':angry:', 'angry.png', '', 'dropdown', 3);
INSERT INTO ib_current.ib_smile VALUES (':blink:', 'blink.png', '', 'more', 101);
INSERT INTO ib_current.ib_smile VALUES (':blush:', 'blush.png', '', 'more', 102);
INSERT INTO ib_current.ib_smile VALUES (':cheerful:', 'cheerful.png', '', 'more', 103);
INSERT INTO ib_current.ib_smile VALUES (':D', 'grin.png', '', 'dropdown', 7);
INSERT INTO ib_current.ib_smile VALUES (':devil:', 'devil.png', '', 'more', 104);
INSERT INTO ib_current.ib_smile VALUES (':dizzy:', 'dizzy.png', '', 'more', 105);
INSERT INTO ib_current.ib_smile VALUES (':ermm:', 'ermm.png', '', 'dropdown', 6);
INSERT INTO ib_current.ib_smile VALUES (':face:', 'face.png', '', 'more', 119);
INSERT INTO ib_current.ib_smile VALUES (':getlost:', 'getlost.png', '', 'more', 106);
INSERT INTO ib_current.ib_smile VALUES (':happy:', 'happy.png', '', 'more', 107);
INSERT INTO ib_current.ib_smile VALUES (':kissing:', 'kissing.png', '', 'more', 108);
INSERT INTO ib_current.ib_smile VALUES (':laughing:', 'laughing.png', '', 'more', 120);
INSERT INTO ib_current.ib_smile VALUES (':love:', 'wub.png', '', 'hidden', 501);
INSERT INTO ib_current.ib_smile VALUES (':ninja:', 'ninja.png', '', 'more', 109);
INSERT INTO ib_current.ib_smile VALUES (':O', 'shocked.png', '', 'dropdown', 10);
INSERT INTO ib_current.ib_smile VALUES (':P', 'tongue.png', '', 'dropdown', 11);
INSERT INTO ib_current.ib_smile VALUES (':pinch:', 'pinch.png', '', 'more', 110);
INSERT INTO ib_current.ib_smile VALUES (':pouty:', 'pouty.png', '', 'more', 111);
INSERT INTO ib_current.ib_smile VALUES (':sick:', 'sick.png', '', 'more', 112);
INSERT INTO ib_current.ib_smile VALUES (':sideways:', 'sideways.png', '', 'more', 113);
INSERT INTO ib_current.ib_smile VALUES (':silly:', 'silly.png', '', 'more', 114);
INSERT INTO ib_current.ib_smile VALUES (':sleeping:', 'sleeping.png', '', 'more', 115);
INSERT INTO ib_current.ib_smile VALUES (':unsure:', 'unsure.png', '', 'more', 116);
INSERT INTO ib_current.ib_smile VALUES (':wassat:', 'wassat.png', '', 'more', 118);
INSERT INTO ib_current.ib_smile VALUES (':whistling:', 'whistling.png', '', 'hidden', 500);
INSERT INTO ib_current.ib_smile VALUES (':woot:', 'w00t.png', '', 'more', 117);
INSERT INTO ib_current.ib_smile VALUES (';)', 'wink.png', '', 'dropdown', 12);
INSERT INTO ib_current.ib_smile VALUES ('<3', 'heart.png', '', 'dropdown', 8);



INSERT INTO ib_current.ib_subaction VALUES (1, 'Блок тегов на обычном форуме', 'stdforum', 'view_forum', 0, 0, 'blocks', 'block_tag_list', 'action_start', '0', '20', 1);
INSERT INTO ib_current.ib_subaction VALUES (2, 'Блок «Сейчас присутствуют» на главной', 'mainpage', 'view', 0, 0, 'online', 'get_online_users', 'page_bottom', '1', '2', 10);
INSERT INTO ib_current.ib_subaction VALUES (3, 'Блок «Сейчас присутствуют» в разделах', '*', 'view_forum', 0, 0, 'online', 'get_online_users', 'page_bottom', '0', '2', 10);
INSERT INTO ib_current.ib_subaction VALUES (4, 'Блок «Сейчас присутствуют» в темах', '*', 'view_topic', 0, 0, 'online', 'get_online_users', 'page_bottom', '0', '2', 10);
INSERT INTO ib_current.ib_subaction VALUES (5, 'Блок объявлений', '*', '*', 0, 0, 'blocks', 'block_announce', 'welcome_start', '1', '1', 1);
INSERT INTO ib_current.ib_subaction VALUES (6, 'Блок с количеством личных сообщений', '*', '*', 0, 0, 'blocks', 'block_pm_unread', 'pm_notify', '1', '', 1);
INSERT INTO ib_current.ib_subaction VALUES (7, 'Блок фотографий из Instagram', 'statpage', 'view', 0, 0, 'instagram', 'block_instagram', 'page_bottom', '0', '4,Добавьте свой Instagram token', 20);















INSERT INTO ib_current.ib_text VALUES (0, 0, 'Правила форума разрабатываются. А пока просим придерживаться общих принципов вежливости и доброжелательности.', 0);
INSERT INTO ib_current.ib_text VALUES (1, 2, 'Если вы читаете этот текст, то установка Intellect Board прошла успешно. 
В дальнейшем его можно будет заменить на информацию о вашем проекте или просто удалить.
Этот раздел имеет тип "Статическая страница". Обычный раздел с темами и соощениями вы можете 
создать в Центре Администрирования.', 0);









INSERT INTO ib_current.ib_user VALUES (1, 'Guest', '*', 1, '', 'U', NULL, ' ', 0, 'guest', '', 111, 'Гость', 'none', 'none', 'null@intbpro.ru', '');
INSERT INTO ib_current.ib_user VALUES (2, 'System', '*', 1, '', 'U', NULL, '', 0, 'system', '', 222, 'System', 'none', 'none', 'null@intbpro.ru', '');
INSERT INTO ib_current.ib_user VALUES (3, 'New User', '*', 5, '', 'U', NULL, '', 0, 'NewUser', '', 333, 'New User', 'none', 'none', 'null2@intbpro.ru', '');









INSERT INTO ib_current.ib_user_contact_type VALUES (2, 'Skype', 'icons/c/skype.gif', 'skype:%s', 50, '', '0');
INSERT INTO ib_current.ib_user_contact_type VALUES (3, 'ВКонтакте', 'icons/c/vk.gif', 'http://vk.com/%s', 30, 'vkontakte', '0');
INSERT INTO ib_current.ib_user_contact_type VALUES (4, 'ICQ', 'icons/c/icq.gif', '', 80, '', '0');
INSERT INTO ib_current.ib_user_contact_type VALUES (5, 'Jabber/XMPP', 'icons/c/jabber.gif', 'xmpp:%s', 100, '', '0');
INSERT INTO ib_current.ib_user_contact_type VALUES (6, 'МойМир@Mail.Ru', 'icons/c/agent.gif', 'http://my.mail.ru/%s', 60, 'mailru', '0');
INSERT INTO ib_current.ib_user_contact_type VALUES (7, 'LiveJournal', 'icons/c/lj.gif', 'http://%s.livejournal.com', 70, 'livejournal', '0');
INSERT INTO ib_current.ib_user_contact_type VALUES (8, 'Telegram', 'icons/c/telegram.png', 'https://t-do.ru/%s', 20, 'telegram', '0');
INSERT INTO ib_current.ib_user_contact_type VALUES (9, 'GTalk/GMail', 'icons/c/gtalk.gif', 'mailto:%s@gmail.com', 40, 'google', '0');
INSERT INTO ib_current.ib_user_contact_type VALUES (10, 'Одноклассники', 'icons/c/odno.gif', 'http://www.odnoklassniki.ru/profile/%s', 35, 'odnoklassniki', '0');
INSERT INTO ib_current.ib_user_contact_type VALUES (11, 'Facebook', 'icons/c/facebook.gif', 'https://www.facebook.com/profile.php?id=%s', 37, 'facebook', '0');
INSERT INTO ib_current.ib_user_contact_type VALUES (12, 'Twitter', 'icons/c/twitter.gif', 'http://twitter.com/%s', 90, 'twitter', '0');
INSERT INTO ib_current.ib_user_contact_type VALUES (13, 'Webmoney ID', 'icons/c/webmoney.gif', 'https://passport.webmoney.ru/asp/CertView.asp?wmid=%s', 120, 'webmoney', '0');
INSERT INTO ib_current.ib_user_contact_type VALUES (14, 'OpenID', 'icons/c/openid.gif', '%s', 110, 'openid', '0');
INSERT INTO ib_current.ib_user_contact_type VALUES (15, 'Личный сайт', '', '%s', 100, '', '1');
INSERT INTO ib_current.ib_user_contact_type VALUES (16, 'Личный блог', '', '%s', 100, '', '1');



INSERT INTO ib_current.ib_user_ext VALUES (1, 10, 0, 0, 0, 0, 0, 1411401372, '0');
INSERT INTO ib_current.ib_user_ext VALUES (2, 0, 0, 0, 0, 0, 0, 1411401372, '0');
INSERT INTO ib_current.ib_user_ext VALUES (3, 0, 0, 0, 0, 0, 100, 1411401372, '0');






INSERT INTO ib_current.ib_user_settings VALUES (1, 0, 0, '', 'ASC', 'None', 10800, '1', '1', '1', '1', '0', '0', 1, '1', '1', '1', '1', 50, 0, '0', '1', '0');
INSERT INTO ib_current.ib_user_settings VALUES (3, 15, 20, '', 'ASC', 'My', 10800, '1', '1', '1', '1', '0', '0', 1, '1', '1', '1', '1', 50, 0, '0', '2', '0');















SELECT pg_catalog.setval('ib_current.ib_bots_id_seq', 7, true);



SELECT pg_catalog.setval('ib_current.ib_category_id_seq', 1, true);



SELECT pg_catalog.setval('ib_current.ib_complain_id_seq', 1, true);



SELECT pg_catalog.setval('ib_current.ib_crontab_id_seq', 13, true);



SELECT pg_catalog.setval('ib_current.ib_forum_id_seq', 1, true);



SELECT pg_catalog.setval('ib_current.ib_log_action_id_seq', 1, true);



SELECT pg_catalog.setval('ib_current.ib_menu_id_seq', 2, true);



SELECT pg_catalog.setval('ib_current.ib_menu_item_id_seq', 8, true);



SELECT pg_catalog.setval('ib_current.ib_poll_variant_id_seq', 1, true);



SELECT pg_catalog.setval('ib_current.ib_post_id_seq', 1, true);



SELECT pg_catalog.setval('ib_current.ib_privmsg_post_id_seq', 1, true);



SELECT pg_catalog.setval('ib_current.ib_privmsg_thread_id_seq', 1, true);



SELECT pg_catalog.setval('ib_current.ib_relation_from__seq', 1, true);



SELECT pg_catalog.setval('ib_current.ib_search_id_seq', 1, true);



SELECT pg_catalog.setval('ib_current.ib_subaction_id_seq', 7, true);



SELECT pg_catalog.setval('ib_current.ib_tagname_id_seq', 1, true);



SELECT pg_catalog.setval('ib_current.ib_task_id_seq', 1, true);



SELECT pg_catalog.setval('ib_current.ib_topic_id_seq', 1, true);



SELECT pg_catalog.setval('ib_current.ib_user_award_id_seq', 1, true);



SELECT pg_catalog.setval('ib_current.ib_user_contact_type_cid_seq', 16, true);



SELECT pg_catalog.setval('ib_current.ib_user_field_id_seq', 1, true);



SELECT pg_catalog.setval('ib_current.ib_user_id_seq', 3, true);



SELECT pg_catalog.setval('ib_current.ib_user_warning_id_seq', 1, true);



SELECT pg_catalog.setval('ib_current.ib_vote_tid_seq', 1, true);



ALTER TABLE ONLY ib_current.ib_subactions
    ADD CONSTRAINT idx_122274_primary PRIMARY KEY (module, action);



ALTER TABLE ONLY ib_current.ib_access
    ADD CONSTRAINT idx_140237_primary PRIMARY KEY (gid, fid);



ALTER TABLE ONLY ib_current.ib_banned_ip
    ADD CONSTRAINT idx_140252_primary PRIMARY KEY (start, "end", till);



ALTER TABLE ONLY ib_current.ib_bots
    ADD CONSTRAINT idx_140256_primary PRIMARY KEY (id);



ALTER TABLE ONLY ib_current.ib_captcha
    ADD CONSTRAINT idx_140262_primary PRIMARY KEY (hash);



ALTER TABLE ONLY ib_current.ib_category
    ADD CONSTRAINT idx_140267_primary PRIMARY KEY (id);



ALTER TABLE ONLY ib_current.ib_complain
    ADD CONSTRAINT idx_140273_primary PRIMARY KEY (id);



ALTER TABLE ONLY ib_current.ib_crontab
    ADD CONSTRAINT idx_140286_primary PRIMARY KEY (id);



ALTER TABLE ONLY ib_current.ib_file
    ADD CONSTRAINT idx_140292_primary PRIMARY KEY (fkey);



ALTER TABLE ONLY ib_current.ib_forum
    ADD CONSTRAINT idx_140301_primary PRIMARY KEY (id);



ALTER TABLE ONLY ib_current.ib_forum_type
    ADD CONSTRAINT idx_140339_primary PRIMARY KEY (module);



ALTER TABLE ONLY ib_current.ib_group
    ADD CONSTRAINT idx_140351_primary PRIMARY KEY (level);



ALTER TABLE ONLY ib_current.ib_last_visit
    ADD CONSTRAINT idx_140364_primary PRIMARY KEY (oid, uid, type);



ALTER TABLE ONLY ib_current.ib_log_action
    ADD CONSTRAINT idx_140371_primary PRIMARY KEY (id);



ALTER TABLE ONLY ib_current.ib_mark_all
    ADD CONSTRAINT idx_140380_primary PRIMARY KEY (uid, fid);



ALTER TABLE ONLY ib_current.ib_menu
    ADD CONSTRAINT idx_140384_primary PRIMARY KEY (id);



ALTER TABLE ONLY ib_current.ib_menu_item
    ADD CONSTRAINT idx_140389_primary PRIMARY KEY (id);



ALTER TABLE ONLY ib_current.ib_moderator
    ADD CONSTRAINT idx_140399_primary PRIMARY KEY (fid, uid, role);



ALTER TABLE ONLY ib_current.ib_oauth_code
    ADD CONSTRAINT idx_140402_primary PRIMARY KEY (code);



ALTER TABLE ONLY ib_current.ib_online
    ADD CONSTRAINT idx_140414_primary PRIMARY KEY (hash, uid);



ALTER TABLE ONLY ib_current.ib_poll
    ADD CONSTRAINT idx_140419_primary PRIMARY KEY (id);



ALTER TABLE ONLY ib_current.ib_poll_variant
    ADD CONSTRAINT idx_140425_primary PRIMARY KEY (id);



ALTER TABLE ONLY ib_current.ib_post
    ADD CONSTRAINT idx_140431_primary PRIMARY KEY (id);



ALTER TABLE ONLY ib_current.ib_privmsg_link
    ADD CONSTRAINT idx_140452_primary PRIMARY KEY (uid, pm_id);



ALTER TABLE ONLY ib_current.ib_privmsg_post
    ADD CONSTRAINT idx_140456_primary PRIMARY KEY (id);



ALTER TABLE ONLY ib_current.ib_privmsg_thread
    ADD CONSTRAINT idx_140469_primary PRIMARY KEY (id);



ALTER TABLE ONLY ib_current.ib_privmsg_thread_user
    ADD CONSTRAINT idx_140473_primary PRIMARY KEY (uid, pm_thread);



ALTER TABLE ONLY ib_current.ib_rating
    ADD CONSTRAINT idx_140479_primary PRIMARY KEY (id, uid);



ALTER TABLE ONLY ib_current.ib_relation
    ADD CONSTRAINT idx_140483_primary PRIMARY KEY (from_, "to");



ALTER TABLE ONLY ib_current.ib_search
    ADD CONSTRAINT idx_140488_primary PRIMARY KEY (id);



ALTER TABLE ONLY ib_current.ib_search_result
    ADD CONSTRAINT idx_140498_primary PRIMARY KEY (oid, sid);



ALTER TABLE ONLY ib_current.ib_smile
    ADD CONSTRAINT idx_140502_primary PRIMARY KEY (code);



ALTER TABLE ONLY ib_current.ib_subaction
    ADD CONSTRAINT idx_140510_primary PRIMARY KEY (id);



ALTER TABLE ONLY ib_current.ib_tagentry
    ADD CONSTRAINT idx_140519_primary PRIMARY KEY (tag_id, item_id);



ALTER TABLE ONLY ib_current.ib_tagname
    ADD CONSTRAINT idx_140523_primary PRIMARY KEY (id);



ALTER TABLE ONLY ib_current.ib_task
    ADD CONSTRAINT idx_140529_primary PRIMARY KEY (id);



ALTER TABLE ONLY ib_current.ib_text
    ADD CONSTRAINT idx_140536_primary PRIMARY KEY (id, type);



ALTER TABLE ONLY ib_current.ib_topic
    ADD CONSTRAINT idx_140546_primary PRIMARY KEY (id);



ALTER TABLE ONLY ib_current.ib_user
    ADD CONSTRAINT idx_140568_primary PRIMARY KEY (id);



ALTER TABLE ONLY ib_current.ib_user_award
    ADD CONSTRAINT idx_140581_primary PRIMARY KEY (id);



ALTER TABLE ONLY ib_current.ib_user_contact
    ADD CONSTRAINT idx_140587_primary PRIMARY KEY (uid, cid);



ALTER TABLE ONLY ib_current.ib_user_contact_type
    ADD CONSTRAINT idx_140591_primary PRIMARY KEY (cid);



ALTER TABLE ONLY ib_current.ib_user_ext
    ADD CONSTRAINT idx_140598_primary PRIMARY KEY (id);



ALTER TABLE ONLY ib_current.ib_user_field
    ADD CONSTRAINT idx_140610_primary PRIMARY KEY (id);



ALTER TABLE ONLY ib_current.ib_user_settings
    ADD CONSTRAINT idx_140618_primary PRIMARY KEY (id);



ALTER TABLE ONLY ib_current.ib_user_value
    ADD CONSTRAINT idx_140643_primary PRIMARY KEY (uid, fdid);



ALTER TABLE ONLY ib_current.ib_user_warning
    ADD CONSTRAINT idx_140647_primary PRIMARY KEY (id);



ALTER TABLE ONLY ib_current.ib_views
    ADD CONSTRAINT idx_140659_primary PRIMARY KEY (oid, type);



ALTER TABLE ONLY ib_current.ib_vote
    ADD CONSTRAINT idx_140665_primary PRIMARY KEY (tid, uid);



CREATE INDEX idx_140262_lastmod ON ib_current.ib_captcha USING btree (lastmod);



CREATE INDEX idx_140286_nextrun ON ib_current.ib_crontab USING btree (nextrun, period);



CREATE INDEX idx_140292_oid ON ib_current.ib_file USING btree (oid, type);



CREATE INDEX idx_140301_hurl ON ib_current.ib_forum USING btree (hurl);



CREATE INDEX idx_140301_sortkey ON ib_current.ib_forum USING btree (sortfield);



CREATE INDEX idx_140389_mid ON ib_current.ib_menu_item USING btree (mid, sortfield);



CREATE INDEX idx_140402_uid ON ib_current.ib_oauth_code USING btree (uid);



CREATE INDEX idx_140414_uid ON ib_current.ib_online USING btree (uid);



CREATE INDEX idx_140425_poll_tid ON ib_current.ib_poll_variant USING btree (tid);



CREATE INDEX idx_140431_author_uid ON ib_current.ib_post USING btree (uid);



CREATE INDEX idx_140431_topic ON ib_current.ib_post USING btree (tid, postdate);



CREATE INDEX idx_140456_thread ON ib_current.ib_privmsg_post USING btree (pm_thread, postdate);



CREATE INDEX idx_140498_relevancy ON ib_current.ib_search_result USING btree (sid, relevancy);



CREATE INDEX idx_140510_intb_subaction_module_idx ON ib_current.ib_subaction USING btree (module, action);



CREATE UNIQUE INDEX idx_140523_tagname ON ib_current.ib_tagname USING btree (tagname, type);



CREATE INDEX idx_140536_search ON ib_current.ib_text USING gin (to_tsvector('simple'::regconfig, data));



CREATE INDEX idx_140542_time ON ib_current.ib_timeout USING btree ("time", action, uid);



CREATE INDEX idx_140546_forum ON ib_current.ib_topic USING btree (fid, last_post_id);



CREATE INDEX idx_140546_fulltext_descr ON ib_current.ib_topic USING gin (to_tsvector('simple'::regconfig, (descr)::text));



CREATE INDEX idx_140546_fulltext_title ON ib_current.ib_topic USING gin (to_tsvector('simple'::regconfig, (title)::text));



CREATE UNIQUE INDEX idx_140568_display_name ON ib_current.ib_user USING btree (display_name);



CREATE INDEX idx_140568_location ON ib_current.ib_user USING btree (location);



CREATE UNIQUE INDEX idx_140568_login ON ib_current.ib_user USING btree (login);



CREATE INDEX idx_140581_uid ON ib_current.ib_user_award USING btree (uid);



CREATE INDEX idx_140598_user_group ON ib_current.ib_user_ext USING btree (group_id);



CREATE INDEX idx_gin_text ON ib_current.ib_text USING gin (to_tsvector( 'russian', "data")) WHERE "type"=16;
CREATE INDEX idx_gin_topic ON ib_current.ib_topic USING gin (to_tsvector( 'russian', title || descr)) WHERE "status"='0';
