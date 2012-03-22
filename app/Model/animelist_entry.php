<?php
class AnimelistEntry extends AppModel
{
	public $belongsTo = array( 'Anime', 'User');
	var $useTable = 'animelist_entries';
	
}
?>
