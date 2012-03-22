<?php
class UserEpisode extends AppModel
{
	public $belongsTo = array( 'Episode', 'User');
	//public $hasOne = 'User';
	var $useTable = 'user_episodes';
	
	public function getByAnime($aniId = null, $userId) {
        $conditions = array(
            'episode_id IN (SELECT episodes.id FROM episodes WHERE episodes.anime_id = '.$aniId.') AND user_id = '.$userId
        );
        return $this->find('all', compact('conditions'));
    }
}
?>