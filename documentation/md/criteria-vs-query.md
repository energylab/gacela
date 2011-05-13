# Criteria vs Query

## But aren't they the same?

The Criteria class is designed to be used at the domain level by controllers and models to pass query requirements
to the mappers. As such, it is designed to completely independent of the DataSource being used by any particular mapper.

The Query class functions specifically for the DataSource being used by the Mapper. This makes it possible to construct queries
specifically for MySQL, MSSQL, a web service or a raw xml file.

Example:

	class Users extends Controller {
		
			$criteria = new Gacela\Criteria();

			$criteria->like('email', '@domain.com')
					->equals('role', 'admin')
					->notIn('id', array(666));

			// $users is an instance of Gacela\Collection
			$users = Gacela::instance()->loadMapper('user')->findWithPrimaryAddress($criteria);

			foreach($users as $user)
			{
				// Emails will all contain the string @domain.com
				echo $user->email;
			}

	}

	class User extends Mapper {

		function findAllWithPrimaryAddress($criteria = null)
		{
			$query = $this->_source->getQuery($criteria);

			$query->from(array('u' => 'users'))
				->join(array('a' => 'addresses'), "u.id = a.user_id AND a.type = 'primary'");
				
			// Assembled query with $criteria argument might look like this
			/*
				SELECT *
				FROM users as u
				INNER JOIN addresses as a ON u.id = a.user_id AND a.type = 'primary'
				WHERE email LIKE '@domain.com'
				AND role = 'admin'
				AND id NOT IN (666)
			*/
		}

	}