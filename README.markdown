# Gacela

Gacela is a robust Data Mapper framework written in PHP.

##How does Gacela as a Data Mapper framework differ from existing ORM solutions?

Traditional ORM solutions tend to implement only an Active Record approach to object-relational mapping while
ignoring the issues that inherently in applications as they grow.

Gacela provides all of the necessary defaults so that it requires basically the same amount of work to get the Data Mapper
setup and running and to start building your Model (or Domain) with it. But, Gacela also offers the necessary separation of
data storage and business logic in the form of Mappers and Models that are totally independent.

Gacela also aims to create an environment where many data sources can be used. Where Mappers can use databases, web services,
xml files, etc equally well.

# System Requirements

Gacela uses the Namepace features of PHP 5.3 and uses PDO exclusively for Database interaction at the present time.

* PHP 5.3
* PDO

# Installation

To install Gacela simply download the latest version of the unstable branch and make certain that
the library/ directory is in your include path.

Gacela uses the same autoloading model provided in Zend Framework and Kohana 3. A later release
will probably include an autoloading feature specifically for Gacela.

# Getting Started

For instructions on how to get started using Gacela, please see the wiki.