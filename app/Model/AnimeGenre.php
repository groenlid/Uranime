<?php
class AnimeGenre extends AppModel
{
	public $belongsTo = array( 'Anime', 'Genre');
	public $hasMany = array(
		'Anime' => array(
			'foreignKey' => 'id'
			),
		'Genre' => array(
			'foreignKey' => 'id'
			)
		);
	var $useTable = 'anime_genre';
	
}
?>
