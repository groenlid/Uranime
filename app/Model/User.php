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

    /**
     * Checks whether user with id $userid is admin
     */
    public function gravatar($userid = null, $size = null){
        $email = $this->read('email',$userid);
        if($size != null)
            $size  = "?s=" . $size;
        return "http://gravatar.com/avatar/" . md5($email['User']['email']) . $size;
        return $this->Gravatar->image($email['User']['email'], array('size' => '150', 'rating' => 'pg'),array('class' => 'animeimage'));
    }

    public function isAdmin($userid = null){
        if($this->id != null && $userid == null)
            $userid = $this->id;
        // TODO: Make better admin function :P
        return $userid === 1;
		//return ($this->Auth->User('id') == 1);
	}
    
}
?>
