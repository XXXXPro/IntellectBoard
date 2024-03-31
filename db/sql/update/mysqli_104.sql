ALTER tABLE `ib_relation` RENAME COLUMN `from` TO `from_`;
ALTER tABLE `ib_relation` RENAME COLUMN `to` TO `to_`;

ALTER TABLE `ib_topic` DROP INDEX Fulltext_descr;
ALTER TABLE `ib_topic` DROP INDEX Fulltext_title;
CREATE FULLTEXT INDEX Fulltext_title ON `ib_topic` (title,descr);
