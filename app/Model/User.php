<?php
class User extends AppModel {
	var $helpers = array('Gravatar');
	var $name = 'User';
	var $useTable = 'users';

	/*var $displayField = 'name';*/

	public $hasMany = array(
		'Activity' => array(
			'foreignKey' => 'subject_id',
			'dependent' => true
		)
	 );
	public $validate = array(
	    'password' => array(
		'rule'    => array('minLength', 8),
		'message' => 'Password must be at least 8 characters long'
	    ),
	    'email' => 'email',
	    'nick' => array(
	    	'rule' => 'isUnique',
	    	'message' => 'A user with that nick already exists'
	    )
	);
	//var $virtualFields = array('gravatar' => 'LOWER(MD5(User.email))');
}
?>
