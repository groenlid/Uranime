<?php
class Genre extends AppModel {
	var $name = 'Genre';
	var $useTable = 'genre';
	public $hasMany = array( 'AnimeGenre' );
}
?>
