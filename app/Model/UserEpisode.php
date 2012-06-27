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