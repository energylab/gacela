# Download Gacela

Gacela can be downloaded from [https://github.com/gabriel1836/gacela](https://github.com/gabriel1836/gacela).

Or you can download the github repository directly as a submodule into your project

1. At the command-line, browse to /your/project/root
2. Execute "git submodule https://github.com/gabriel1836/gacela.git /path/to/store/gacela"
3. Execute "git submodule init"
4. Execute "git submodule update"

# Initialize Gacela
3. [Register](gacela#registering-custom-application-namespaces) your application's custom namespace with the Gacela instance
4. [Register](gacela#registering-datasources) any [DataSources](gacela.datasources) with the Kacela instance


# Add Stored Procedures to your project database

In order to use Gacela's automatic relationship discovery tools, you will need to add the following stored procedures
to your project's database

~~~~

DROP PROCEDURE IF EXISTS sp_belongs_to;
DROP PROCEDURE IF EXISTS sp_has_many;

DELIMITER //

CREATE PROCEDURE sp_belongs_to (IN schemaName VARCHAR(100), IN tableName VARCHAR(100))
	BEGIN
		SELECT COLUMN_NAME AS keyColumn, REFERENCED_TABLE_NAME AS refTable, REFERENCED_COLUMN_NAME AS refColumn, CONSTRAINT_NAME AS constraintName
		FROM INFORMATION_SCHEMA.key_column_usage
		WHERE TABLE_SCHEMA = schemaName
		AND TABLE_NAME = tableName
		AND REFERENCED_TABLE_NAME IS NOT NULL;
	END //

CREATE PROCEDURE sp_has_many (IN schemaName VARCHAR(100), IN tableName VARCHAR(100))
	BEGIN
		SELECT REFERENCED_COLUMN_NAME AS keyColumn, TABLE_NAME AS refTable, COLUMN_NAME AS refColumn, CONSTRAINT_NAME AS constraintName
		FROM INFORMATION_SCHEMA.key_column_usage
		WHERE TABLE_SCHEMA = schemaName
		AND REFERENCED_TABLE_NAME = tableName;
	END //

DELIMITER ;

~~~~