<?php
class UserEpisode extends AppModel
{
	public $belongsTo = array( 'Episode', 'User');
	//public $hasOne = 'User';
	var $useTable = 'user_episodes';
	
}
?>