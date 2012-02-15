<?php
class ApiController extends AppController {
	var $uses = array('User','Anime','Episode','AnimelistEntry','Activity','UserEpisode');
	var $paginate = array( 'Episode' => array(
						'limit' => 25,
						'order' => array(
							'Episode.aired' => 'desc'
						)
					)
				);
	var $helpers = array('Gravatar');
	var $components = array('RequestHandler');

	/**
	 * Shortcut so you can check in your Controllers wether
	 * REST Component is currently active.
	 *
	 * Use it in your ->flash() methods
	 * to forward errors to REST with e.g. $this->Rest->error()
	 *
	 * @return boolean
	 */
	protected function _isRest() {
		return !empty($this->Rest) && is_object($this->Rest) && $this->Rest->isActive();
	}


	function index() {

	}
	
	function getuser($id = null){
		$this->User->recursive = -1;
		$this->set('user', $this->User->find('first', array('conditions' => array('User.id' => $id), 
			'fields' => array('User.id','User.nick','User.joined','User.desc')
		)));
	}

	function viewEpisode($id = null) {
		$this->Episode->recursive = -1;
		$episode = $this->Episode->find('first', array('conditions' => array('id' => $id)));
		$this->set(compact('episode'));
		$this->set('_serialize',array('episode'));
	}

	function viewAnime($id = null) {
		$anime = $this->Anime->find('first', array('conditions' => array('Anime.id' => $id), 'fields' => array('Anime.*')));
		$this->set(compact('anime'));
		$this->set('_serialize',array('anime'));
	}

	function viewAnimeList($userid = null)
	{
		if($userid == null)
			return false;
		$userEpisodes = $this->UserEpisode->find('all', array(
			'order' => 'aired ASC',
			'fields' => 'DISTINCT Episode.anime_id, COUNT(*) as count, MAX(UserEpisode.timestamp) AS `when`',
			'conditions' => array(
				'user_id' => $userid
			),
			'order' => 'when DESC',
			'group' => 'Episode.anime_id'
		));
		
		$animes = array();
		$this->Anime->recursive = -1;
		foreach($userEpisodes as $anime_id)
		{
			$anime = $this->Anime->read(null,$anime_id['Episode']['anime_id']);
			array_push($animes, $anime['Anime']);
		}
		$this->set(compact('animes'));
		$this->set('_serialize',array('animes'));
	}

	function newsfeed($limit = 10){
		
		$results;

		if($limit > 50 || $limit < 0)
			$limit = 10;
		$data = $this->Activity->find('all', array('limit' => $limit));
		$this->set(compact('data'));
		$this->set('_serialize',array('data'));
	}
	function newsfeedAfter($id = null){
		if($id == null || !is_numeric($id))
			return;
		$data = $this->Activity->find('all', array('limit' => 50,'conditions' => array('Activity.id >' => $id)));
		//debug($data);
		$this->set(compact('data'));
		$this->set('_serialize',array('data'));

	}

	function gravatar($userid = null)
	{
		if($userid == null || !is_numeric($userid))
			die();
		
		$this->User->recursive = -1;
		$email = $this->User->read('email',$userid);
		if($email == null)
			die();
		print file_get_contents($this->gravatar->image($email['User']['email'], array('class' => 'animeimage', 'size' => '150', 'rating' => 'r')));
		die();
	}

	function lastAnime($number = 5, $offset = 0)
	{
		if($number > 10)
			$number = 10;
		if($offset > 10)
			$offset = 10;

		$this->Anime->recursive = -1;
		$anime = $this->Anime->find('all', array('limit' => $number, 'offset' => $offset,'order' => array('Anime.id DESC')));

		$this->set(compact('anime'));
		$this->set('_serialize',array('anime'));
	}
}


?>
