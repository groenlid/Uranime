<?php
class AnimeRelationship extends AppModel
{
	public $belongsTo = array( 
		'anime1' => array(
			'className' => 'Anime',
			'foreignKey' => 'anime1'
		),
		'anime2' => array(
			'className' => 'Anime',
			'foreignKey' => 'anime2'
		));
	var $useTable = 'anime_relationship';
	
}
?>
