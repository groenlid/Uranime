<?php
class ScrapeInfo extends AppModel
{
	public $belongsTo = 'Anime';
	var $useTable = 'scrape_info';
	var $primaryKey = 'id';
}
?>