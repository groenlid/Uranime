<?php
class Episode extends AppModel {
	var $name = 'Episode';
	var $useTable = 'episodes';
	public $belongsTo = array('Anime');
	public $hasMany = array('UserEpisode');
	public $order = array('aired','number');


	/**
	 * Returns the thumbnail of the episode image
	 * If $widht is 0, the full-size image is returned
	 */
	public function fetchImage($episode_id, $width = 0)
	{
		$this->Episode = ClassRegistry::init('Episode');
		$episode = $this->Episode->find('first',array(
			'conditions' => array(
				'id' => $episode_id
				)
			)
		);

		$returnUrl = "";

		if($width != 0 && is_numeric($width) && $width > 0)
			$returnUrl .= "http://src.sencha.io/" . $width . "/";

		if(file_exists(WWW_ROOT . EPISODE_IMAGE_PATH . $episode['Episode']['anime_id'] . "/" . $episode['Episode']['image']) && $episode['Episode']['image'] != null)
			return $returnUrl . SERVER_PATH . EPISODE_IMAGE_PATH . $episode['Episode']['anime_id'] . "/" . $episode['Episode']['image'];
		else
			return $returnUrl . SERVER_PATH . IMAGE_PATH . $episode['Anime']['fanart'];

	}
}
?>