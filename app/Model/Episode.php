<?php
class Episode extends AppModel {
	var $name = 'Episode';
	var $useTable = 'episodes';
	public $belongsTo = array('Anime');
	public $hasMany = array('UserEpisode');
	public $order = 'number';
}
?>