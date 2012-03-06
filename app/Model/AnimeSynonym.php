<?php
class AnimeSynonym extends AppModel
{
	public $belongsTo = array( 'Anime');
	var $useTable = 'anime_synonyms';
	
}
?>
