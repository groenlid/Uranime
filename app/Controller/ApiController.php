<?php
class ApiController extends AppController {
	var $uses = array('AnimeSynonym','AnimeRequest','User','ScrapeInfo','Anime','Episode','AnimelistEntry','Activity','UserEpisode','AnimeRatingBayes','UserWatchlist');
	var $paginate = array( 'Episode' => array(
						'limit' => 25,
						'order' => array(
							'Episode.aired' => 'desc'
						)
					)
				);
	var $helpers = array('Gravatar');
	var $components = array('Auth',

		'RequestHandler','Rest.Rest' => array(
		'catchredir' => true,
		'debug' => 0,
		'actions' => array(
				'newsfeed' => array(
					'extract' => array('data'),
				),
				'newsfeedAfter' => array(
					'extract' => array('data'),
				),
				'lastseen' => array(
					'extract' => array('data'),
				),
				'lastseenAfter' => array(
					'extract' => array('data'),
				),
				'animelist' => array(
					'extract' => array('animes.{n}' => 'animelist'),
				),
				'watchlist' => array(
					'extract' => array('animes.{n}' => 'animelist'),
				),
				'search' => array(
					'extract' => array('animes.{n}.Anime' => 'animelist'),
				),
				'episode' => array(
					'extract' => array('episode.Episode' => 'episode'),
				),
				'anime' => array(
					'extract' => array('anime.Anime' => 'anime'),				
				),
				'animeepisodes' => array(
					'extract' => array('episodes.{n}.Episode' => 'episodes'),
				),
				'userepisodes' => array(
					'extract' => array('UserEpisode.{n}.UserEpisode' => 'episodes'),
				),
				'checkCredentials' => array(
					'extract' => array('id')
				),
				'watchepisode' => array(
					'extract' => array('status')
				),
				'trendingAnime' => array(
					'extract' => array('anime.{n}' => 'animelist')
				),
				'latestAnime' => array(
					'extract' => array('anime.{n}.Anime' => 'animelist')
				),
				'setanimescrape' => array(
					'extract' => array('status')
				),
				'animeWatchlist' => array(
					'extract' => array('status')
				),
				'watchanime' => array(
					'extract' => array('status')
				)
			),
		'auth'=>array(
            'requireSecure'=>false,
            'keyword' => 'TRUEREST',
            'fields'=>array(
                'apikey'=>'apikey',
                'email'=>'email',
                'password' => 'password'
            ),
            'log' => array(
					'pretty' => true,
				),
            'meta' => array(
				'enable' => false,
			)
        )
		));

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

	/**
	 * Check user credentials.. Make session token that lasts for 15 minutes based on username and password.
	 * 
	 */
	public function checkCredentials(){
		//print_r($this->request);
		$cred = $this->Rest->credentials();
		$this->User->recursive = -1;
		$user = $this->User->find('first',array(
			'conditions' => array(
				'email' => $cred['email'],
				'password' => AuthComponent::password($cred['password'])
				)
			)
		);
		$this->set('id',$user['User']['id']);
			//$this->Auth->logout();
		/*if ($this->request->is('post')) {
			$this->set('status',$this->request->data);
			$username = $this->request->data['Post']['email'];
			$password = $this->request->data['Post']['password'];*/

			/*$data = array(
					$this->Auth->userModel => array(
						'username' => $username,
						'password' => $password,
					),
				);*/

			//$data = $this->Auth->hashPasswords($data);
			//$this->set('status',$this->Auth->login($data));
		//}
	}

	protected function login() {
		//return $this->Rest->abort(array('status' => '403', 'error' => "asd"));
		if (!$this->Auth->user()) {
			
			// Try to login user via REST
			if ($this->Rest->isActive()) {
				
				$credentials = $this->Rest->credentials();

				$this->Auth->autoRedirect = false;
				
				$username = $credentials['email'];
				$password = AuthComponent::password($credentials['password']);
				//$this->data['email'] = $username;
				//$this->data['password'] = AuthComponent::password($credentials['password']);
				$data = array(
					$this->Auth->userModel => array(
						'email' => $credentials['email'],
						'password' => AuthComponent::password($credentials['password']),
					),
				);
				
				//print_r($this->Auth);
				$count = $this->User->find('count',array('conditions' => array('email' => $username,'password' => $password)));
				if ($count != 1) {
					$msg = sprintf('Unable to log you in with the supplied credentials. ');
					return $this->Rest->abort(array('status' => '403', 'error' => $msg));
				}else{
					$this->Auth->login($data);
				}
			}
		}
	}

	public function restRatelimitMax ($Rest, $credentials = array()) { }
	public function beforeFilter () {
		//debug($this->_isRest());
		//$this->login();
		parent::beforeFilter();
	}

	function index() {

	}
	
	function requestAnime($title = null, $id = null & $id = null){
		$this->AnimeRequest->recursive = -1;

		
	}

	function userEpisodeGraph($id = null){
		$this->UserEpisode->recursive = -1;
		$res = $this->UserEpisode->find('all',array(
			'fields' => array(
				'count(*) as amount',
				'DATE_FORMAT(timestamp,"%Y-%m-%d") as day'
				),
			'conditions' => array(
				'user_id' => $id
				),
			'group' => array(
				'day'
				),
			'order' => array(
				'day ASC'
				)
			)
		);
		$results = array(array('color' => 'blue','data' => array()));


		foreach($res as $ue)
		{
			//print_r($ue);
			$results[0]['data'][] = array(
					'y' => (int)$ue[0]['amount'],
					'x' => strtotime($ue[0]['day']) 
				);
		}

		print json_encode($results);
		die();
	}
	
	function user($id = null){
		$this->login();
		$this->User->recursive = -1;
		$this->set('user', $this->User->find('first', array('conditions' => array('User.id' => $id), 
			'fields' => array('User.id','User.nick','User.joined','User.desc')
		)));
	}

	function watchepisode($userid = null, $id = null,$bulk = false)
	{
		$this->login();
		/*if($this->requestAction('/Episode/watchEpisode/'.$id))
			$this->set('status','Success');
		else
			$this->set('status','Fail');*/
		if($id == null || !is_numeric($id)){
			$this->set('status','Fail');
			return false;
		}

		if($userid == NULL)
		{
			$this->set('status','Fail what');
			return false;
		}	

		$count = $this->UserEpisode->find('count',array(
			'conditions' => array(
					'user_id' => $userid,
					'episode_id' => $id
				)
			));
		if($count != 0)
		{
				$this->set('status','Fail');
				return false;
		}
		else {
			// Check if this is a real episode
			$episode = $this->Episode->read(null,$id);
			if($episode == null)
				return false;

			$this->UserEpisode->create();
			$this->UserEpisode->set('user_id',$userid);
			$this->UserEpisode->set('episode_id',$id);
			$this->UserEpisode->set('timestamp',DboSource::expression('NOW()'));
			if($this->UserEpisode->save())
			{
				$this->set('status','Success');
				//$this->Episode->id = $id;
				$this->Episode->read(null, $id);
				if(!$bulk)
					$this->requestAction('/anime/setEpisodeScrape/'.$this->Episode->data['Anime']['id']."/true");
				return true;
			}
			else{
				$this->set('status','Fail');
				return false;
			}
		}

	}
	
	function watchanime($userid = null, $id = null,$bulk = false)
	{
		$this->login();
		/*if($this->requestAction('/Episode/watchEpisode/'.$id))
			$this->set('status','Success');
		else
			$this->set('status','Fail');*/
		if($id == null || !is_numeric($id)){
			$this->set('status','Fail');
			return false;
		}

		if($userid == NULL)
		{
			$this->set('status','Fail what');
			return false;
		}	

		$this->Episode->recursive = -1;
		$episodes = $this->Episode->find('all',array(
			'conditions' => array(
					'anime_id' => $id
				)
			));
		$amount = 0;
		foreach($episodes as $episode)
			if(strtotime($episode['Episode']['aired']) < time()){
				
				$count = $this->UserEpisode->find('count',array(
					'conditions' => array(
							'user_id' => $userid,
							'episode_id' => $episode['Episode']['id']
						)
					));
				if($count != 0)
				{
						$this->set('status','Fail');
						continue;
				}
				else {

					$this->UserEpisode->create();
					$this->UserEpisode->set('user_id',$userid);
					$this->UserEpisode->set('episode_id',$episode['Episode']['id']);
					$this->UserEpisode->set('timestamp',DboSource::expression('NOW()'));
					if($this->UserEpisode->save())
					{
						$this->set('status','Success');
						//$this->Episode->id = $id;
						$amount++;
						$continue;
					}
					else{
						
						continue;
					}
				}	
			}
			if($amount != 0)
				$this->requestAction('/api/setanimescrape/'.$id);
			else
				$this->set('status','Fail');

	}

	function episode($id = null) {
		$this->Episode->recursive = -1;
		$episode = $this->Episode->find('first', array('conditions' => array('id' => $id)));
		$this->set(compact('episode'));
		$this->set('_serialize',array('episode'));
	}
	
	function animeepisodes($anime_id = null){
		$this->Episode->recursive = -1;
		$episodes = $this->Episode->findAllByAnimeId($anime_id);
		$this->set(compact('episodes'));
	}

	function userepisodes($anime_id = null, $user_id = null){
		$this->login();
		$this->UserEpisode->recursive = -1;
		$UserEpisode = $this->UserEpisode->getByAnime($anime_id,$user_id);
		$this->set(compact('UserEpisode'));
	} 

	function anime($id = null) {
		$anime = $this->Anime->find('first', array('conditions' => array('Anime.id' => $id), 'fields' => array('Anime.*')));
		$this->set(compact('anime'));
		$this->set('_serialize',array('anime'));
	}

	function search($query){
		if(trim($query) == "" || $query == null)
			return false;
			
		$animes = $this->AnimeSynonym->find('all',
			array(
				'fields' => array(
					'DISTINCT(anime_id)',
					'Anime.*'),
				'limit' => 30,
				'conditions' => array(
						'LOWER(AnimeSynonym.title) LIKE' => "%".strtolower($query)."%"
					)
			)
		);

		$this->set(compact('animes'));
		$this->set('animes',$animes);
	}

	function animelist($userid = null)
	{
		$this->login();
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
		//$this->set('_serialize',array('animes'));
	}

	function watchlist($userid = null)
	{
		$this->login();
		if($userid == null)
			return false;
		$watchList = $this->UserWatchlist->find('all', array(
			'conditions' => array(
				'user_id' => $userid
			)
		));
		
		$animes = array();
		$this->Anime->recursive = -1;
		foreach($watchList as $watchItem)
		{
			$anime = $this->Anime->read(null,$watchItem['UserWatchlist']['anime_id']);
			array_push($animes, $anime['Anime']);
		}
		$this->set(compact('animes'));
		//$this->set('_serialize',array('animes'));
	}

	function newsfeed($limit = 10){
		$this->login();
		$results;

		if($limit > 50 || $limit < 0)
			$limit = 10;
		$data = $this->Activity->find('all', array('limit' => $limit));
		for($i = 0; $i < count($data);$i++)
			$data[$i]['Activity']['timestamp'] = strftime("%Y-%m-%dT%H:%M:%S%z",strtotime($data[$i]['Activity']['timestamp']));
		$this->set(compact('data'));
		//$this->set('_serialize',array('data'));
	}
	
	function newsfeedAfter($id = null){
		$this->login();
		if($id == null || !is_numeric($id))
			return;
		$data = $this->Activity->find('all', array('limit' => 50,'conditions' => array('Activity.id >' => $id)));
		for($i = 0; $i < count($data);$i++)
			$data[$i]['Activity']['timestamp'] = strftime("%Y-%m-%dT%H:%M:%S%z",strtotime($data[$i]['Activity']['timestamp']));
		//debug($data);
		$this->set(compact('data'));
		//$this->set('_serialize',array('data'));

	}
	
	function lastseen($limit = 10){
		$this->login();
		$results;

		if($limit > 50 || $limit < 0)
			$limit = 10;
		$data = $this->UserEpisode->find('all', array('limit' => $limit,'fields' => array('UserEpisode.*','User.nick','Episode.*,User.id,User.email'),'order' => 'UserEpisode.id DESC'));
		for($i = 0; $i < count($data);$i++)
			$data[$i]['UserEpisode']['timestamp'] = strftime("%Y-%m-%dT%H:%M:%S%z",strtotime($data[$i]['UserEpisode']['timestamp']));
		$this->set(compact('data'));
	}
	
	function lastseenAfter($id = null){
		$this->login();
		if($id == null || !is_numeric($id))
			return;
		$data = $this->UserEpisode->find('all', array(
			'limit' => 50,
			'conditions' => array(
				'UserEpisode.id >' => $id
				),
			'fields' => array('UserEpisode.*','User.nick','Episode.*,User.id,User.email'),
			'order' => 'UserEpisode.id DESC'
			)
		);
		//debug($data);
		for($i = 0; $i < count($data);$i++)
			$data[$i]['UserEpisode']['timestamp'] = strftime("%Y-%m-%dT%H:%M:%S%z",strtotime($data[$i]['UserEpisode']['timestamp']));
		$this->set(compact('data'));
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

	function latestAnime($number = 5, $offset = 0)
	{
		if($number > 10)
			$number = 10;
		if($offset > 10)
			$offset = 10;

		$this->Anime->recursive = -1;
		$anime = $this->Anime->find('all', array('limit' => $number, 'offset' => $offset,'order' => array('Anime.id DESC')));

		$this->set(compact('anime'));
		//$this->set('_serialize',array('anime'));
	}

	function trendingAnime($number = 5){
		$animeList = $this->AnimeRatingBayes->getHighestRated($number);
		$anime = array();
		foreach($animeList as $single)
		{
			$a = $single['Anime'];
			$a['bayes'] = $single[0]['real_rating'];
			array_push($anime,$a);
		}
		//debug($anime);
		$this->set(compact('anime'));
	}

	function imageresize($file = null, $newWidth = 0, $newHeight = 0){

		
		$imagick = new Imagick();
		$imagick->readImage(SERVER_PATH.IMAGE_PATH.$file);

		// Find the largest dimension to determine orientation
		$height = $imagick->getImageHeight();
		$width = $imagick->getImageWidth();
		$filepath = SERVER_PATH.IMAGE_PATH.$file;
		
		//list($width, $height) = getimagesize($filepath);
		
		if($newHeight > $height)
			$newHeight = $height;
		if($newWidth > $width)
			$newWidth = $width;

		if($newWidth <= 0 && $newHeight <= 0)
		{
			$newWidth = $width;
			$newHeight = $height;
		}

		if($newWidth <= 0 && $newHeight != 0){
			$scaleRatio = $height / $newHeight;
			$newWidth = intval($width/$scaleRatio);
		}
		if($newHeight <= 0 && $newWidth != 0){
			$scaleRatio = $width / $newWidth;
			$newHeight = intval($height/$scaleRatio);
		}

		
		//echo $newWidth . " " . $newHeight;
		$imagick->resizeImage($newWidth, $newHeight, Imagick::FILTER_LANCZOS, 1);
		/*$size = getimagesize($filepath);
		
		header('Content-Type:'.$size['mime']);
		
		$image = imagecreatefromstring(file_get_contents($filepath));
		$result = imagecreatetruecolor($newWidth, $newHeight);
		
		imagecopyresampled($result, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
		
		imagejpeg($result, null, 100);*/
		
		//$tmpPlace = '/home/content/00/8758600/html/app/tmp/imageresize/'.uniqid();
		//$shellTxt = 'convert '.SERVER_PATH.IMAGE_PATH.$file.' -resize '.$newWidth.'x'.$newHeight.' '.$tmpPlace;
		//echo $shellTxt;
		//shell_exec($shellTxt);
		
		//$fileStream = fopen($tmpPlace,'r');
		//echo $fileStream;
		//echo($imagick);
		header('Content-Type: image/'.$imagick->getImageFormat());

		echo($imagick);
		die();
	}

	// This is a public method meant to be used for android-app
	function setanimescrape($animeid = null)
	{
		$this->set('status','nothing done');
		if($animeid == null || !is_numeric($animeid))
			return false;
		// Check if anime exists 
		$anime = $this->Anime->findById($animeid);
		if($anime == null)
			return false;
		
		if($anime['Anime']['status'] != 'currently' && $anime['Anime']['status'] != NULL)
			return false;

		$this->ScrapeInfo->updateAll(
			array('ScrapeInfo.scrape_needed' => 1),
			array('ScrapeInfo.anime_id' => $animeid)
		);
		$this->set('status','changed scrapeinfo');
	}

	function animeWatchlist($userid = null, $animeid = null, $newValue = null)
	{
		$this->login();
		if($userid == null || $animeid == null || $newValue == null
		|| !is_numeric($userid) || !is_numeric($animeid))
			return false;
		$anime = $this->Anime->findById($animeid);
		if($anime == null)
			return false;
		$exists = $this->UserWatchlist->find('first',
			array('conditions' =>
				array(
					'user_id' => $userid,
					'anime_id' => $animeid
				)
			)
		);

		$this->set('status','nothing done');

		if($newValue == "true")
		{
			// Check if user already have it in watchlist
			if($exists != null)
				return;
			$this->UserWatchlist->create();
			$this->UserWatchlist->set('user_id',$userid);
			$this->UserWatchlist->set('anime_id',$animeid);
			$this->UserWatchlist->save();
			$this->set('status','added to watchlist');
		}
		else {
			// Delete already existing watchlistentry
			if($exists == null)
				return;
			$this->UserWatchlist->delete($exists['UserWatchlist']['id'],false);
			$this->set('status','removed from watchlist');	
		}
	}
}


?>
