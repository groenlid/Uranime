<?php
class Anime extends AppModel {
	var $name = 'Anime';
	var $useTable = 'anime';
	
	/*public $actAs = array(
		'MeioUpload' => array(
				'image' => array(
						'dir' => 'uploads{DS}{model}{DS}{field}',
						'create_directory' => true,
						'allowed_mime' => array('image/jpeg', 'image/pjpeg', 'image/png', 'image/gif'),
                        'allowed_ext' => array('.jpg', '.jpeg', '.png', 'gif')
					),
				'fanart' => array(
						'dir' => 'uploads{DS}{model}{DS}{field}',
						'create_directory' => true,
						'allowed_mime' => array('image/jpeg', 'image/pjpeg', 'image/png', 'image/gif'),
                        'allowed_ext' => array('.jpg', '.jpeg', '.png', 'gif')
					)
			)
		);*/
	public $hasOne = array( 'AnimeRatingBayes' => array(
		'associationForeignKey'  => 'anime_id',
		)
	);	
	public $hasMany = array( 
					'AnimeGenre',
					'Episode' => array(
						'className'	=> 'Episode',
						'foreignKey'	=> 'anime_id',
						'order'		=> 'Episode.number ASC',
						'dependent'	=> true
					),
					'AnimeSynonyms',
					'ScrapeInfo',
					'UserWatchlist'
				);
	public $validation = array(
			'image' => array(
					'Empty' => array(
							'check' => true
						)
				),
			'fanart' => array(
					'Empty' => array(
							'check' => true
						)
				)
		);

    /**
     * Gets the fanart of the given anime id.
     * If input id equals null, it checks if 
     * the model already is set
     */
    public function getFanart($id = null, $width = null)
    {
        $fanart = $this->read('fanart',$id);
        $pwidth = ($width == null) ? 100 : $width;
        $placeholder = "http://placehold.it/" . $pwidth;
        
       if(!file_exists(WWW_ROOT . IMAGE_PATH . $fanart['Anime']['fanart']))
           return $placeholder;
       else
           return  SERVER_PATH . "/api/imageresize/" . $fanart['Anime']['fanart']  . "/" . $width;
    }
    
    /**
     * DEPRECATED::: USE cmp function in UserController instead
     */ 
   
	public function getActivity($id = null){
		
		$this->Activity = ClassRegistry::init('Activity');
		$this->Comment = ClassRegistry::init('Comment');
		$this->User = ClassRegistry::init('User');
		$this->recursive = -1;
		$this->User->recursive = -1;

		$activities = $this->Activity->find('all', array('conditions' => array(
			"(object_id=".$id." AND object_type IN('fanart','image','anime','reference')) OR (object_type='episode' AND object_id IN (SELECT episodes.id FROM episodes WHERE episodes.anime_id=".$id."))"
			)));

		$this->Comment->recursive = -1;
		$comments = $this->Comment->findAllByAnime_id($id);
		$this->set("comments",$comments);

		$comments = array_reverse($comments);
		$pos = 0;
		$mergedActivities = array();
		$commentPos = 0;
		foreach($activities as $key => $activity)
		{
			// Check if there exists a comment with earlier timestamp
			while($commentPos < count($comments) && 
				strtotime($comments[$commentPos]['Comment']['timestamp']) > strtotime($activity['Activity']['timestamp']))
				{
					$u = $this->User->find('first',array('conditions' => array('id' => $comments[$commentPos]['Comment']['user_id'])));
					$mergedActivities[$pos] = array(
						'subject' => 
							$u['User'],
						'object' => 
							$comments[$commentPos],
						'Activity' => array(
							'verb' => 'comment'
							)
						); 
					//$comments[$commentPos];
					$commentPos++;
					$pos++;
				}
			$mergedActivities[$pos] = $activity;
			if($activity['Activity']['object_type'] == 'episode')
				$mergedActivities[$pos]['object'] = $this->Episode->findById($activity['Activity']['object_id']);
			else
				$mergedActivities[$pos]['object'] = Model::findById($activity['Activity']['object_id']);
			$pos++;
		}
		// Add the rest of the comments
		while($commentPos < count($comments))
		{
			$u = $this->User->find('first',array('conditions' => array('id' => $comments[$commentPos]['Comment']['user_id'])));

			$mergedActivities[$pos] = array(
						'subject' => 
							$u['User'],
						'object' => 
							$comments[$commentPos],
						'Activity' => array(
							'verb' => 'comment'
							)
						); 
			//$comments[$commentPos];
			$commentPos++;
			$pos++;
		}
		return $mergedActivities;
	}
	
}
?>
