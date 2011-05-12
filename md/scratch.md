For who skipped the basics, they will not be rehashed here. Please read [Relationships (basics)](/documentation/usage/relationships-basic/) first.

# Association Relationships

An Association (or Has and Belongs to Many) Relationship exists when two resources are linked through a middle (or join) resource that contains only the primary keys of the resources
on either side of the relationship. This makes it possible for many entities on the right side of the relationship to be linked to many entities on the left side of the relationship.  For example:

~~~~
CREATE TABLE students (
    wizardId INT UNSIGNED NOT NULL PRIMARY KEY,
    houseId INT UNSIGNED NOT NULL,
    isDAMembmer BOOL NOT NULL DEFAULT 0,
    CONSTRAINT fk_wizard_student
    	FOREIGN KEY (wizardId)
    	REFERENCES wizards(wizardId)
    	ON DELETE CASCADE,
    CONSTRAINT fk_house_students
    	FOREIGN KEY (houseId)
    	REFERENCES houses(houseId)
    	ON DELETE RESTRICT
) ENGINE = Innodb;

CREATE TABLE courses (
    courseId INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    wizardId INT UNSIGNED NOT NULL,
    subject VARCHAR(255) NULL,
    CONSTRAINT fk_teacher_courses
    	FOREIGN KEY (wizardId)
    	REFERENCES wizards(wizardId)
) ENGINE = Innodb;

CREATE TABLE enrollments (
    courseId INT UNSIGNED NOT NULL,
    wizardId INT UNSIGNED NOT NULL,
    PRIMARY KEY (courseId, wizardId),
    CONSTRAINT fk_course_students
    	FOREIGN KEY (courseId)
    	REFERENCES courses(courseId)
    	ON DELETE CASCADE,
    CONSTRAINT fk_student_courses
    	FOREIGN KEY (wizardId)
    	REFERENCES students(wizardId)
    	ON DELETE CASCADE
) ENGINE = Innodb;
~~~~

In the table creation statements above, the resources `Student` and `Course` are associated through a third (silent) resource `Enrollment`.

## Rules for Defining an Association Relationship

In order to have Gacela auto-discover an association relationship:
 
1. All fields in the Resource must be part of the defined Primary Key for the Resource.
2. All fields must be part of a belongsTo relationship to one of the parent resources involved in the relationship.

To manually specify an Association relationship, add the Resource name to the `Mapper::$_associations` array.

# Dependent Relationships

A Dependent Relationship exists when one Resource is only ever loaded within the context of another Resource and never on its own. For example:

~~~~
CREATE TABLE addresses (
    addressId INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    locationName VARCHAR(255) NOT NULL
) ENGINE = Innodb;

CREATE TABLE wizards (
    wizardId INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    fname VARCHAR(255) NOT NULL,
    lname VARCHAR(255) NOT NULL,
    role ENUM('teacher', 'student') NULL,
    addressId INT UNSIGNED NULL,
    CONSTRAINT fk_address_wizard
    	FOREIGN KEY (addressId)
    	REFERENCES addresses(addressId)
    	ON DELETE SET NULL
) ENGINE = Innodb;
~~~~

In the above example, 'A Wizard belongs to an Address'. Strictly speaking this is correct, but really this is more a case of 'A Wizard has an Address'. So we need to tell the Wizard Mapper that this is a dependent relationship that should always be loaded and saved within the context of the parent. As a result, no Mapper is needed for the Dependent Resource.

## Rules for Specifying a Dependent Relationship

1. Dependent Relationships can only be set on Belongs To relationships.
2. Add the Resource name to the $_dependents array

# Inheritance Relationships

An Inheritance Relationship exists when a Resource has a parent-child relationship with another Resource. For example:

~~~~
CREATE TABLE wizards (
    wizardId INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    fname VARCHAR(255) NOT NULL,
    lname VARCHAR(255) NOT NULL,
    role ENUM('teacher', 'student') NULL,
    addressId INT UNSIGNED NULL,
    CONSTRAINT fk_address_wizard
        FOREIGN KEY (addressId)
        REFERENCES addresses(addressId)
        ON DELETE SET NULL
) ENGINE = Innodb;

CREATE TABLE students (
    wizardId INT UNSIGNED NOT NULL PRIMARY KEY,
    houseId INT UNSIGNED NOT NULL,
    isDAMembmer BOOL NOT NULL DEFAULT 0,
    CONSTRAINT fk_wizard_student
        FOREIGN KEY (wizardId)
        REFERENCES wizards(wizardId)
        ON DELETE CASCADE,
    CONSTRAINT fk_house_students
        FOREIGN KEY (houseId)
        REFERENCES houses(houseId)
        ON DELETE RESTRICT
) ENGINE = Innodb;
~~~~

In the above example, a `Student` belongs to a `Wizard` and thus inherits the fields and relationships of its parent Resource.

## Rules for defining an Inheritance Relationship

1. The primary key of the child Resource must be the same as the primary key of the parent Resource
2. The child Resource must define a 'Belongs To' relationship with the parent Resource