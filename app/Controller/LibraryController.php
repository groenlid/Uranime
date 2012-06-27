<?php
class LibraryController extends AppController {
	var $uses = array('UserEpisode','User','Anime', 'Activity');	
	var $helpers = array('Time','Text');
	var $components = array(
		'Rest.Rest' => array(
			'catchredir' => false,
			'actions' =>array(
				'view' => array(
					'extract'	=> array('animelist' => 'animelist')
				)
			)
		));
	
	function index() {
		
	}
	
	function view($id = null,$sort = null){
		if($id == null || !is_numeric($id))
			return;
		
		$this->User->recursive = -1;
		$this->set('nick',$this->User->read(array('nick','id'),$id));
		$this->UserEpisode->recursive = 0;
		$userEpisodes = $this->UserEpisode->find('all', array(
			
			'fields' => 'DISTINCT Episode.anime_id, COUNT(*) as count, MAX(UserEpisode.timestamp) AS `when`',
			'order' => 'when DESC',
			'conditions' => array(
				'user_id' => $id,
				'Episode.special' => null
			),
			'group' => 'Episode.anime_id',
		));
		//debug($userEpisodes);
		
		$animes = array();
		$episodes = array();
		$this->Anime->recursive = -1;
		$this->Episode->recursive = -1;
		/*$this->Anime->unbindModel(
    		array(
    			'hasOne' => array(
    				'AnimeRatingBayes'
    				),
    			'hasMany' => array(
    				'AnimeGenre',
    				'AnimeSynonyms',
    				'ScrapeInfo',
    				'UserWatchlist'
    				)
    			)
    		);*/

		foreach($userEpisodes as $anime_id)
		{
			//array_push($animes,$tmp);
			/*array_push($animes, $this->Anime->find('first',array(
				'fields' => 'DISTINCT Episode.anime_id, Anime.image,Anime.title, COUNT(*) as episode_count',
				'conditions' => array(
					'Anime.id' => $anime_id['Episode']['anime_id'],
					'Episode.anime_id' => $anime_id['Episode']['anime_id']
					),
				'group' => 'Episode.anime_id'
				)
			));*/
			$episode = $this->Episode->find('count', array(
				'conditions' => array(
					'anime_id' => $anime_id['Episode']['anime_id'],
					'special IS NULL'
					)
				)
			);
			
			$anime = $this->Anime->read(null,$anime_id['Episode']['anime_id']);
			
			array_push($animes, $anime);
			array_push($episodes, array(
				'Anime' => array(
					'title' => $anime['Anime']['title']
					),
				'episodes' => array(
					$episode
					)
				)
			);
			/*array_push($animes, $this->Anime->find('first',array(
				'conditions' => array(
					'id' => $anime_id['Episode']['anime_id'],
					'Episode.special' => null
					)
				)
			)
			);*/
		}
		if($sort == 'title')
		{
			usort($animes, "cmpTitle");
			usort($userEpisodes, "cmpTitle");
			usort($episodes,"cmpTitle");
		}
		$this->set('anime',$animes);
		$this->set('stats',$userEpisodes);
		$this->set('episodes',$episodes);

		debug($episodes);
		debug($animes);
		debug($userEpisodes);
		//$this->set(compact('animes'));
	}


	/*function view($id = null, $status = 'all') {
		if($id == null)
			return;

		$statuslist = array(
			'currently' => 'cur',
			'completed' => 'com',
			'dropped' => 'dro',
			'planned' => 'pla',
			'onhold' => 'hol',
			'all'	=> 'all'
		);

		if($status !== null && array_key_exists($status,$statuslist))
			$status = $statuslist[$status];

		$this->loadModel('User');
		$this->loadModel('Anime');
		$this->User->id = $id;
		$this->User->read();
		$list;

		if($status == null || $status == 'all')
			$list = $this->AnimelistEntry->find('all',array(
				'fields' => array('AnimelistEntry.*','Anime.*'),
				'conditions' => array('user_id' => $id)));
		else
			$list = $this->AnimelistEntry->find('all',array(
				'fields' => array('AnimelistEntry.*','Anime.*'),
				'conditions' => array('user_id' => $id, 'AnimelistEntry.status' => $status)));
		$this->set('animelist',$list);
		$this->set('user',$this->User->data);
		$this->set('status',$status);
	}

	function update($id = null) {
		if($id == null || empty($this->data)){
			$this->Session->setFlash('Could not update anime-stats');
			return false;
		}
		// Fetch the anime in question
		$uid = $this->Auth->User('id');
		$anime = $this->Anime->AnimelistEntry->find('first', array(
				'conditions' => array('user_id' => $uid, 'anime_id' => $id)	
			));

		$update = array();
		if(!empty($this->data['AnimelistEntry']['score']))
			$update['score'] = $this->data['AnimelistEntry']['score'];
		if(!empty($this->data['AnimelistEntry']['status']))
			$update['status'] = "'".$this->data['AnimelistEntry']['status']."'";
		if(!empty($this->data['AnimelistEntry']['ep_seen']))
			$update['ep_seen'] = $this->data['AnimelistEntry']['ep_seen'];

		$this->Anime->AnimelistEntry->updateAll(
			$update, array('user_id' => $uid, 'anime_id' => $id));

		$score = (empty($this->data['AnimelistEntry']['score'])) ? null : $this->data['AnimelistEntry']['score'];
		$status = (empty($this->data['AnimelistEntry']['status'])) ? 'cur' : $this->data['AnimelistEntry']['status'];
		$ep_seen = (empty($this->data['AnimelistEntry']['ep_seen'])) ? 0 : $this->data['AnimelistEntry']['ep_seen'];
		

		$newstatus = array(
			'cur' => 'watched',
			'pla' => 'plantowatch',
			'com' => 'completed',
			'dro' => 'dropped',
			'hol' => 'onhold' 
		);
		// NEED TO ADD ACTIVITY FOR THIS ACTION
		$this->Activity->create();
		$this->Activity->set('subject_type','user');
		$this->Activity->set('subject_id',$uid);
		$this->Activity->set('verb',$newstatus[$status]);

		$this->Activity->set('object_type','anime');
		$this->Activity->set('object_id',$id);
		$this->Activity->set('option',$ep_seen);
		$this->Activity->save();
		$this->Session->setFlash('Updated animelist entry for anime');
		$this->requestAction('/anime/setEpisodeScrape/'.$id);
		$this->redirect($this->referer());
	}

	function add($id = null) {
		if($id == null || empty($this->data)){
			$this->Session->setFlash('Could not update anime-stats');
			return false;
		}
		// Fetch the anime in question
		$uid = $this->Auth->User('id');
		$anime = $this->Anime->AnimelistEntry->find('first', array(
				'conditions' => array('user_id' => $uid, 'anime_id' => $id)	
			));
		
		$score = (empty($this->data['AnimelistEntry']['score'])) ? null : $this->data['AnimelistEntry']['score'];
		$status = (empty($this->data['AnimelistEntry']['status'])) ? 'cur' : $this->data['AnimelistEntry']['status'];
		$ep_seen = (empty($this->data['AnimelistEntry']['ep_seen'])) ? 0 : $this->data['AnimelistEntry']['ep_seen'];
		//print_r($status);
		
		$this->Anime->AnimelistEntry->create();
		
		$this->Anime->AnimelistEntry->saveAll(
			array(
				'ep_seen' => $ep_seen,
				'status' => $status,
				'score' => $score,
				'user_id' => $uid,
				'anime_id' => $id
			));
		$this->requestAction('/anime/setEpisodeScrape/'.$id);
		$this->Session->setFlash('Added animelist entry for anime');

		$newstatus = array(
			'cur' => 'watched',
			'pla' => 'plantowatch',
			'com' => 'completed',
			'dro' => 'dropped',
			'hol' => 'onhold' 
		);
		// NEED TO ADD ACTIVITY FOR THIS ACTION
		$this->Activity->create();
		$this->Activity->set('subject_type','user');
		$this->Activity->set('subject_id',$uid);
		$this->Activity->set('verb',$newstatus[$status]);

		$this->Activity->set('object_type','anime');
		$this->Activity->set('object_id',$id);
		$this->Activity->set('option',$ep_seen);
		$this->Activity->save();
		$this->redirect($this->referer());
	}*/
}
	function cmpTitle($a, $b)
	{
	    if ($a['Anime']['title'] == $b['Anime']['title']) {
	        return 0;
	    }
	    return ($a['Anime']['title'] < $b['Anime']['title']) ? -1 : 1;
	}
?>
