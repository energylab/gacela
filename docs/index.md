# Mapping Data Structures to PHP Objects

Most useful applications interact with data in some form. There are multiple solutions for storing data and for each of 
those solutions, sometimes multiple formats in which the data can be stored.
When using object-oriented PHP, that same data is stored, modified and accessed in a class.

Let's assume that you were creating your own blogging system. We'll assume initially that you have need want to create
posts and you want to allow multiple users to author articles. 


Storing the data in a hierarchical format with XML is fairly straightforward. Each 'user' is represented by a node named 
'user' with a child 'contents' node to contain the user's blog posts.

```xml
<xml version="1.0>
	<user id="1" first="Bobby" last="Mcintire" email="bobby@kacela.com" />
	<user id="2" first="Frankfurt" last="McGee" email="sweetcheeks@kacela.com">
		<contents>
			<content id="id" title="Beginners Guide to ORMs">
                In order to start, you need to read the rest of this user's guide.
            </content>
		</contents>
	</user>
</xml>
```

With a relational database, we would create two tables, one to hold the basic information about each user, and a table 
to hold their posts.

'users' table

| id  | name           | email                    |
---------------------------------------------------
| 1  | Bobby Mcintire  | bobby@kacela.com         |
---------------------------------------------------
| 2  | Frankfurt McGee | sweetcheeks@kacela.com   |
---------------------------------------------------

'contents'

| id | userId | title                   | content                        |
-------------------------------------------------------------------------
| 1 |    2   | Beginners Guide to ORMs | Read the rest of the guide     |
-------------------------------------------------------------------------

The same data in PHP would be stored in classes like so:

```php
class User {

	protected $data = [
		'id' => 1,
		'name' => 'Bobby Mcintire',
		'email' => 'bobby@kacela.com'
	];

	protected $contents = [];

}

class User {

	protected $data = [
		'id' => 2,
		'name' => 'Frankfurt McGee',
		'email' => 'sweetcheeks@kacela.com',
		'phone' => '9876543214'
	];

	protected $contents = [
        [
            'id' => 1,
            'userId' => 2,
            'title' => 'Beginners Guide to ORMs',
            'content' => 'Read this guide all the way to the end'
        ]
	];

}
```

As you can see the way that data is stored can be vastly different from the way that we interact with data in our 
application code.

This is called the object-impedance mismatch. A common design pattern has arisen to hide the complexities of the 
differences between data in application code and data stores called Object-Relational Mapping.

This design pattern was developed specifically to deal with the complexities of mapping relational database records to 
objects in code, but many of the same principles apply when dealing with any form of raw data because there is almost 
always some mismatch.

# Common Solutions

The most common approach to Object-Relational Mapping, or ORM for short, is the Active Record pattern.

With Active Record, one class instance represents one Record from the Data Source.
With an Active Record instance, business logic and data access logic are contained in a single object.
A basic Active Record class would look like so:

```php
class Model_User extends ORM
{

}
```

And would be accessed like so:

```php
$user = ORM::find('User', 1);

// echo's Bobby Mcintire to the screen
echo $user->name;

$user->email = 'new.user@gacela.com'

$user->save();
```

# Gacela's Basic Philosophies

Working with a Data Mapper for the first time can be quite a bit more difficult than working with a more basic approach 
like Active Record, but Gacela offers large dividends if you tackle the complexity upfront. When developing Gacela, the 
following were just the top features we thought every ORM should have:

- Automatically discover relationships between classes and rules about the data contained within classes.
- Separate data store activities from business logic activities so that our classes have a single responsibility.
- Defaults that are easy to set up and use, but can handle complex mapping between business objects and the underlying 
data store.

# Installation and Configuration

## How to Install

Gacela can be installed with Composer.

Define the following requirement in your composer.json file:

```json
{
    "require": {
        "energylab/gacela": "dev-develop"
    }
}
```

## Configuration

```php
return array
(
	/**
	 * List of data sources and connection params
	 */
	'datasources' => array
	(
		/**
		 * db is the default data source for Mappers
		 * PDO is the data-access abstraction layer used by Gacela for all RDBMS connections.
		 */
		'db' => array
		(
			/**
			 * Valid types are: mysql (More will be added soon)
			 */
			'type' => 'mysql',
			/**
			 * Database name
			 */
			'schema' => 'kacela',
			/**
			 * Connection host
			 */
			'host' => 'localhost',
			/**
			 * Database username
			 */
			'user' => 'root',
			/**
			 * Database password
			 */
			'password' => ''
		),
		/**
		 *
		 */
		'sf' => array
		(
			'type' => 'salesforce',
			/**
			 * Gacela uses the Force.com toolkit for PHP.
			 * It can be found here: https://github.com/developerforce/Force.com-Toolkit-for-PHP
			 * Once installed in your application, Gacela needs to know the path to the soap client.
			 */
			'soapclient_path' => MODPATH.'sf/vendor/soapclient/',
			/**
			 * Specify the full path to your wsdl file for Salesforce
			 */
			'wsdl_path' => APPPATH.'config/sf.wsdl',
			'username' => 'salesforceuser@domain.com.sandbox',
			'password' => 'SecretPasswordWithSalesforceHash',
			/**
			 * Specifies which Salesforce objects are available for use in Gacela
			 */
			'objects' => array()
		)
	),
	/**
	 * Can specify TRUE to use default Cache::instance() otherwise, specify Cache group name
	 */
	'cache' => false,
	/**
	 * Set to TRUE causes the Kohana_Profiler to run for Gacela
	 */
	'profiling' => false
);
```

## Data Source Setup

### MySQL

The default for Gacela is to name the database tables in the plural form (users, posts)

# Basic Usage

With Gacela installed I would create the following files:i

APPATH/Classes/Mapper/User.php

```php
namespace Gacela\Mapper;

class Mapper_User extends Mapper {}
```

APPATH/classes/model/user.php

```php
namespace Gacela\Model;

class Model_User extends Model {}
```

Now, I can load and manipulate a basic model:

```php
$user = Gacela::factory('user', 1);

// echos Bobby Mcintire to the screen
echo $user->name;

$user->phone = '9875412356'

// Saves the updated record to the database
$user->save();
```

Right now you're probably thinking, "Wait! This looks EXACTLY like Kohana_ORM, where's the benefit in creating two files where I only created one before?" 

So far all we've looked at is the most basic scenario - one database table with a mapper that presents simple, default find() and findAll() methods with a Model that doesn't have any custom business logic.

We'll explore custom Mapper functions first.

# Fetching Data using Mappers

## To fetch a single record:

When fetching a single record, Gacela::factory() and Gacela::find() both return an instance of the Mapper::_modelName.

Mapper::_modelName defaults to Model_<name> where name is equal to Mapper_<name>.

```php
Gacela::factory($mapper_name, $id)

/*
 * For Example
*/

// Fetch a record with a simple (single field) primary key
Gacela::factory('user', 1);

// Fetch a record with a complex (multiple fields) primary key
Gacela::factory('survey_answer', array('question_id' => 500, 'user_id' => 23);
```

## To fetch multiple records with simple criteria:

```php
/*
 * Kohana_ORM
*/
ORM::factory('User')
	->where('last_login', '>=', '2012-12-31')
	->find_all();

/*
 * Gacela
 * The Gacela_Criteria object allows users to specify simple rules for filtering, sorting and limiting data without all of the complexity of
 * a full built-in query builder.
 * Gacela::find_all() returns an instance of Gacela_Collection_Arr
*/
Gacela::find_all('user', Gacela::criteria()->greater_than_or_equal_to('last_login', '2012-12-31');
```

## Fetching data using complex criteria

```php
/**
 * Gacela
*/
class Mapper_User extends Gacela_Mapper
{
	/**
	 * Encapsulates the above example in a simple method call.
	*/
	public function find_all_by_last_login($last_login)
	{
		return $this->find_all(Gacela::criteria()->greater_than_or_equal_to('last_login', $last_login);
	}

	/**
	 * Fetches a Collection of all users who have never logged in or have registered in the last ten minutes.
	*/
	public function find_new_or_inactive()
	{
		/**
		 * Gacela_Mapper::_getQuery() returns a Query instance specific to the Mapper's data source.
		 * As such, the methods available for each Query instance will vary.
		*/
		$query = $this->_getQuery()
			->from('users')
			->join(array('l' => 'logs'), "users.id = l.user_id AND l.type = 'login'", array(), 'left')
			->where('l.date IS NULL')
			->where('users.registration >= :date', array(':date' => date('Y-m-d H:i:s', strtotime('-10 minutes')));

		/**
		 * For the Database DataSource, returns an instance of PDOStatement.
		 * For all others, returns an array.
		*/
		$data = $this->_runQuery($query)->fetchAll();

		/**
		 * Creates a Gacela_Collection instance based on the internal type of the data passed to Gacela_Mapper::_collection()
		 * Currently, two types of Collections are supported, Arr (for arrays) and PDOStatement
		*/
		return $this->_collection($data);
	}
}
```

## Customizing the Data returned for the Model

Sometimes, it is desirable to pass data to the model that is not strictly represented in the underlying table for a specific model.

In this instance, lets assume that we want to return a comma-delimited list of aliases as part of the main user record.

```php

/**
 * Gacela
**/
class Mapper_User extends Gacela_Mapper
{
	public function find($id)
	{
		$criteria = Gacela::criteria()->equals('id', $id);

		$rs = $this->_run_query($this->_base($criteria))->fetch();

		if(!$rs)
		{
			$rs = new \stdClass;
		}

		return $this->_load($rs);
	}

	public function find_all(Gacela_Criteria $criteria = null)
	{
		/**
		 * Returns an instance Gacela_Collection_Statement
		**/
		return $this->_run_query($this->_base($criteria));
	}

	/**
	 * Allows for a unifying method of fetching the custom data set for find() and find_all()
	**/
	protected function _base(Gacela_Criteria $criteria = null)
	{
		$sub = $this->_get_query()
			->from('aliases', array('user_id' => 'id', 'aliases' => "GROUP_CONCAT(alias)")
			->groupBy('id');

		return $this->_get_query($criteria)
			->from('users')
			->join(array('a' => $sub), 'users.id = a.user_id', array('aliases));
	}
}
```
