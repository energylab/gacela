<?php
/** 
 * @author Noah Goodrich
 * @date 10/8/11
 * @brief
 * 
 */

return array(
	'columns' => array(
		'houseId' => array(
			'type' => 'int',
			'length' => 10,
			'unsigned' => true,
			'primary' => true,
			'sequenced' => true,
			'default' => null
		),
		'houseName' => array(
			'length' => 255,
			'type' => 'string'
		)
	),
	'relations' => array(
		'students' => array(
			'keyTable' => 'houses',
			'refTable' => 'students',
			'constraintName' => 'fk_house_students',
			'type' => 'hasMany',
			'keys' => array(
				'houseId' => 'houseId'
			)
		)
	),
	'primary' => array('houseId')
);