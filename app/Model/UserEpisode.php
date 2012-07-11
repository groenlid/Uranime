<?php
class UserEpisode extends AppModel
{
	public $belongsTo = array( 'Episode', 'User');
	//public $hasOne = 'User';
	var $useTable = 'user_episodes';
	
	/**
	 * Returns the episodes the user has seen in a certain anime
	 */
	public function getByAnime($aniId = null, $userId) {
        $conditions = array(
            'episode_id IN (SELECT episodes.id FROM episodes WHERE episodes.anime_id = '.$aniId.') AND user_id = '.$userId
        );
        return $this->find('all', compact('conditions'));
    }

    /**
     * Returns the last seen episodes for the given user.
     * grouped by anime_id
     */
    public function getLastSeenEpisodes($userid = null, $count = 20, $distinct = true){
        if($userid == null)
            return null;

        if($count == null|| $count > 50 || $count < 0)
           $count = 20; 

        if(!$distinct)
            return $this->find('all', array(
                    'conditions' => array(
                            'user_id' => $userid
                        ),
                    'limit' => $count
                )
            );
        
        $seenEpisodes = $this->find('all', array(
                    'fields' => array('Episode.*','UserEpisode.*' ),
                    'conditions' => array(
                        'user_id' => $userid,
                    ),
                    'group' => 'Episode.anime_id',
                    'limit' => $count,
                    'order' => array('UserEpisode.id DESC'),
                ));

        $output = $this->addAnimeDetails($seenEpisodes,true);
        return $output;
    }

    /**
     * Takes UserEpisode and Episode details as input,
     * gives an array consisting of the original input + anime details
     * The input array needs to contain the Episode model.
     * @findAll = if cakephp's find all is used on the input. e.g. another outer layer.
     */
    public function addAnimeDetails($input, $findAll = false){
        if(!$findAll)
            return $this->addAnimeDetailsHelper($input);
        $output = array();
        for($i = 0; $i < count($input); $i++)
            $output[$i] = $this->addAnimeDetailsHelper($input[$i]);
        return $output;       
    }

    private function addAnimeDetailsHelper($input){
        if(!isset($input['Episode']['anime_id']))
            return null;

        $this->Episode->Anime->recursive = -1;
        $output = array_merge($input,$this->Episode->Anime->read(null,$input['Episode']['anime_id']));
        return $output;
    }



/*
    public function getUserAnimes($userId = null){
    	if($userId == null)
    		return;
    	
    	return $this->find('all',array(
    		''
    		)
    	);
    }*/
}
?>
