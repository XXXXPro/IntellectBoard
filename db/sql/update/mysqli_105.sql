ALTER TABLE prefix_file ADD owner MEDIUMINT DEFAULT 2 NULL COMMENT 'Пользователь, загрузивший файл';
ALTER TABLE prefix_file CHANGE owner owner MEDIUMINT DEFAULT 0 NULL COMMENT 'Пользователь, загрузивший файл' AFTER is_main;
ALTER TABLE prefix_file ADD upload_date TIMESTAMP DEFAULT NOW() NULL COMMENT 'Время загрузки';
ALTER TABLE prefix_file CHANGE upload_date upload_date TIMESTAMP NULL COMMENT 'Время загрузки' AFTER extension;
ALTER TABLE prefix_file ADD views INTEGER UNSIGNED DEFAULT 0 NOT NULL;
ALTER TABLE prefix_file CHANGE views views INTEGER UNSIGNED DEFAULT 0 NOT NULL AFTER upload_date;

ALTER TABLE prefix_group ADD max_uploads MEDIUMINT UNSIGNED DEFAULT 0 NOT NULL COMMENT 'Максимальное количество загруженных файлов';
ALTER TABLE prefix_group ADD max_file_size SMALLINT UNSIGNED DEFAULT 0 NOT NULL COMMENT 'Максимальный размер загруженного файла в мегабайтах';
ALTER TABLE prefix_group ADD max_uploads_size INTEGER UNSIGNED DEFAULT 0 NOT NULL COMMENT 'Максимальный объём всех загруженных файлов';
