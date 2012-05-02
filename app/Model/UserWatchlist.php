<?php
class UserWatchlist extends AppModel
{
	public $belongsTo = array( 'Anime', 'User');
	//public $hasOne = 'User';
	var $useTable = 'user_watchlist';
	
}
?>