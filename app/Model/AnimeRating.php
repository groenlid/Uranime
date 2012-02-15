<?php
class AnimeRating extends AppModel
{
	public $belongsTo = array( 'Anime', 'User');
	public $hasMany = array(
		'Anime' => array(
			'foreignKey' => 'id'
			),
		'User' => array(
			'foreignKey' => 'id'
			)
		);
	var $useTable = 'anime_ratings';
	
}
?>