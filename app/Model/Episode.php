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
		//$this->Anime = ClassRegistry::init('Anime');
		
		$this->Episode->recursive = 2;
		
		$episode = $this->Episode->find('first',array(
			'conditions' => array(
				'Episode.id' => $episode_id
				),
			'fields' => array(
				'Episode.*',
				'Anime.fanart',
				)
			)
		);

		$returnUrl = "";
		
		//print_r($episode);
		
		if($width != 0 && is_numeric($width) && $width > 0)
			$returnUrl .= "http://src.sencha.io/" . $width . "/";

		if(file_exists(WWW_ROOT . EPISODE_IMAGE_PATH . $episode['Episode']['anime_id'] . "/" . $episode['Episode']['image']) && $episode['Episode']['image'] != null)
			return $returnUrl . SERVER_PATH . EPISODE_IMAGE_PATH . $episode['Episode']['anime_id'] . "/" . $episode['Episode']['image'];
		else if(file_exists(WWW_ROOT . IMAGE_PATH . $episode['Anime']['fanart']) && $episode['Anime']['fanart'] != null)
			return $returnUrl . SERVER_PATH . IMAGE_PATH . $episode['Anime']['fanart'];
		else
			return $returnUrl . "http://placehold.it/200x112/";

    }

    /**
     * Make sure the episode image is also deleted..
     * + all user seen episodes of with this episode id..
     * $cascade: not used, but is for deleting dependent models.
     */
    public function beforeDelete(boolean $cascade){

        // Delete the episode image... 
        // We do not need it anymore..

        if($this->image != null && $this->image != '')
        {
            $image_path = WWW_ROOT . EPISODE_IMAGE_PATH . $this->anime_id . '/' . $this->image;
            if( file_exists( $image_path ) == TRUE )
                if( !unlink( $image_path ) ) // If it fails, we don't want to continue
                    return false;
        }


        // Delete user seen episodes
		$this->UserEpisode = ClassRegistry::init('UserEpisode');
        $this->UserEpisode->deleteAll(array('UserEpisode.episode_id' =>$this->id),false);
        
        return true;
    }
}
?>
