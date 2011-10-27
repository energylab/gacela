DROP TABLE IF EXISTS gacela.fields;

CREATE TABLE gacela.fields (
	`primary` INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
	`date` DATE NULL,
	`datetime` DATETIME NULL,
	`timestamp` TIMESTAMP NULL,
	`int` INT NOT NULL,
	`tinyint` TINYINT(4) UNSIGNED NULL,
	`smallint` SMALLINT,
	`mediumint` MEDIUMINT,
	`bigint` BIGINT,
	`decimal` DECIMAL(15),
	`float` FLOAT(12),
	`double` DOUBLE,
	`text` TEXT,
	`tinytext` TINYTEXT,
	`mediumtext` MEDIUMTEXT,
	`longtext` LONGTEXT,
	`enum` ENUM('one', 'two', 'three', 'four'),
	`char` CHAR(6),
	`varchar` VARCHAR(240),
	`bool` BOOL NOT NULL
) 