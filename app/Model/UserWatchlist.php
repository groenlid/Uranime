<?php
class UserWatchlist extends AppModel
{
	public $belongsTo = array( 'Anime', 'User');
	//public $hasOne = 'User';
	var $useTable = 'user_watchlist';
	
	function beforeSave($options) {
		$this->data['UserWatchlist']['time'] = date("Y-m-d H:i:s");
		return true;
	}
}
?>