DROP DATABASE IF EXISTS gacela;

CREATE DATABASE gacela;

USE gacela;

CREATE TABLE addresses (
    addressId INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    locationName VARCHAR(255) NOT NULL
) ENGINE = Innodb;

INSERT INTO addresses VALUES
(1, 'Number 4, Privet Drive'),
(2, 'The Burrow'),
(3, 'Game Hut at Hogwarts'),
(4, 'Malfoy Manor');

CREATE TABLE houses (
    houseId INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    houseName VARCHAR(255) NOT NULL
) ENGINE=Innodb;

INSERT INTO houses VALUES
(1, 'Gryffindor'),
(2, 'Hufflepuff'),
(3, 'Ravenclaw'),
(4, 'Slytherin');

CREATE TABLE wizards (
    wizardId INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    fname VARCHAR(255) NOT NULL,
    lname VARCHAR(255) NOT NULL,
    role ENUM('teacher', 'student') NULL,
    addressId INT UNSIGNED NULL
) ENGINE = Innodb;

INSERT INTO wizards VALUES
(1, 'Harry', 'Potter', 'student', 1),
(2, 'Hermoine', 'Granger', 'student', NULL),
(3, 'Ron', 'Weasley', 'student', 2),
(4, 'Rubeus', 'Hagrid', 'teacher', 3),
(5, 'Minerva', 'McGonagall', 'teacher', NULL),
(6, 'Remus', 'Lupin', 'teacher', NULL),
(7, 'Severus', 'Snape', 'teacher', NULL),
(8, 'Charity', 'Burbage', 'teacher', NULL),
(9, 'Albus', 'Dumbledore', 'teacher', NULL),
(10, 'Filius', 'Flitwick', 'teacher', NULL),
(11, 'Aurora', 'Sinistra' , 'teacher', NULL),
(12, 'Pomona', 'Sprout', 'teacher', NULL),
(13, 'Sybill', 'Trelawney', 'teacher', NULL),
(14, 'Luna', 'Lovegood', 'student', NULL),
(15, 'Neville', 'Longbottom', 'student', NULL),
(16, 'Draco', 'Malfoy', 'student', 4),
(17, 'Justin', 'Finch-Fletchly', 'student', NULL),
(18, 'Cho', 'Chang', 'student', NULL),
(19, 'Hannah', 'Abbott', 'student', NULL),
(20, 'Vincent', 'Crabbe', 'student', NULL);

CREATE TABLE students (
    wizardId INT UNSIGNED NOT NULL PRIMARY KEY,
    houseId INT UNSIGNED NOT NULL,
    isDAMembmer BOOL NOT NULL DEFAULT 0
) ENGINE = Innodb;

INSERT INTO students VALUES
(1, 1, 1),
(2, 1, 1),
(3, 1, 1),
(14, 3, 1),
(15, 1, 1),
(16, 4, 0),
(17, 2, 1),
(18, 3, 1),
(19, 2, 1),
(20, 4, 0);

CREATE TABLE courses (
    courseId INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    wizardId INT UNSIGNED NOT NULL,
    subject VARCHAR(255) NOT NULL
) ENGINE = Innodb;

INSERT INTO courses VALUES
(1, 4, 'Care of Magical Teachers'),
(2, 5, 'Transfiguration'),
(3, 6, 'Defense Against the Dark Arts'),
(4, 7, 'Potions'),
(5, 8, 'Muggle Studies'),
(7, 10, 'Charms'),
(8, 11, 'Astronomy'),
(9, 12, 'Herbology'),
(10, 13, 'Divination');

CREATE TABLE enrollments (
    courseId INT UNSIGNED NOT NULL,
    wizardId INT UNSIGNED NOT NULL,
    PRIMARY KEY (courseId, wizardId)
) ENGINE = Innodb;

INSERT INTO enrollments
SELECT c.courseId, s.wizardId
FROM courses c, students s
WHERE courseId IN (2, 3, 4, 7, 9);

INSERT INTO enrollments VALUES
(10, 1),
(10, 3),
(5, 2),
(8, 1),
(8, 2),
(8, 3),
(1, 1),
(1, 2),
(1, 3),
(1, 16),
(1, 20);

DROP PROCEDURE IF EXISTS sp_belongs_to;
DROP PROCEDURE IF EXISTS sp_has_many;

DELIMITER //

CREATE PROCEDURE sp_belongs_to (IN schemaName VARCHAR(100), IN tableName VARCHAR(100))
	BEGIN
		SELECT TABLE_NAME as keyTable, GROUP_CONCAT(COLUMN_NAME) AS keyColumns, REFERENCED_TABLE_NAME AS refTable, GROUP_CONCAT(REFERENCED_COLUMN_NAME) AS refColumns, CONSTRAINT_NAME AS constraintName
		FROM INFORMATION_SCHEMA.key_column_usage
		WHERE TABLE_SCHEMA = schemaName  COLLATE utf8_general_ci
		AND TABLE_NAME = tableName  COLLATE utf8_general_ci
		AND REFERENCED_TABLE_NAME IS NOT NULL
		GROUP BY constraintName;
	END //

CREATE PROCEDURE sp_has_many (IN schemaName VARCHAR(100), IN tableName VARCHAR(100))
	BEGIN
	  SELECT REFERENCED_TABLE_NAME AS keyTable, GROUP_CONCAT(REFERENCED_COLUMN_NAME) AS keyColumns, TABLE_NAME AS refTable, GROUP_CONCAT(COLUMN_NAME) AS refColumns, CONSTRAINT_NAME AS constraintName
	  FROM INFORMATION_SCHEMA.key_column_usage
	  WHERE TABLE_SCHEMA = schemaName COLLATE utf8_general_ci
	  AND REFERENCED_TABLE_NAME = tableName COLLATE utf8_general_ci
	  GROUP BY constraintName;
	END //
