<?php
class AnimeController extends AppController {
	var $helpers = array('Text','Form','Html','Gravatar');
	var $uses = array('User','Anime','Episode','Activity',
					'ScrapeInfo','AnimeRequest','AnimeGenre', 'AnimeRelationship',
					'UserEpisode','AnimeRating','AnimeSynonym','AnimeRatingBayes',
					'UserWatchlist','Comment');
	var $paginate = array( 'Episode' => array(
						'limit' => 50,
						'order' => array(
							'Episode.number' => 'desc'
						),
						'Activity' => array(
							'limit' => 10
						)
					)
				);
	var $components = array(
		'Auth','Session','Attachment'
	);
	private function clean_url($text) 
	{ 
		$text=strtolower($text); 
		$code_entities_match = array(' ','--','&quot;','!','@','#','$','^','&','*','(',')','_','+','{','}','|',':','"','<','>','?','[',']','\\',';',"'",',','.','/','*','+','~','`','=');
		$code_entities_replace = array('%20','-','','','','','','','','','','','','','','','','','','','','','','','');
		$text = str_replace($code_entities_match, $code_entities_replace, $text); 
		return $text; 
	}
	
	function info()
	{
		phpinfo();
		die();
	}

	function useImage($id = null, $type = null)
	{

		if($this->Auth->User('id') == NULL)
			$this->redirect($this->referer());

		if($type == null || $id == null || !is_numeric($id))
		{
			$this->redirect($this->referer());
			return;
		}

		if($type != "image" && $type != "fanart"){
			$this->redirect($this->referer());
			return;
		}
		if(!$this->request->data)
			$this->redirect($this->referer());

		$requestData = $this->request->data;
		$url = $requestData['imageUrl'];
		//debug($this->request->data);
		//die();
		$file = fopen($url,"rb");
		if($file){
			
			$this->Anime->read(null,$id);
			
			if($type == 'image')
				$oldImage = $this->Anime->data['Anime']['image'];
			else if($type == 'fanart')
				$oldImage = $this->Anime->data['Anime']['fanart'];

			//debug($oldImage);
			//die();
			$valid_exts = array("jpg","jpeg","gif","png");
			$ext = end(explode(".",strtolower(basename($url))));
			if(in_array($ext,$valid_exts)){

				$filename = String::uuid() . '.' . $ext;

				$newfile = fopen(WWW_ROOT . IMAGE_PATH . $filename, "wb");

				if($newfile){

					while(!feof($file)){

						fwrite($newfile,fread($file,1024 * 8),1024 * 8); // write the file to the new directory at a rate of 8kb/sec. until we reach the end.
					
					}
					//$this->Anime->read(null,$id);
					
					if($type == 'image')
						$this->Anime->set('image',$filename);
					else if($type == 'fanart')
						$this->Anime->set('fanart',$filename);

					if($this->Anime->save())
					{
						if(file_exists(WWW_ROOT.IMAGE_PATH . $oldImage) && $oldImage != NULL)
						{
							if(unlink(WWW_ROOT.IMAGE_PATH . $oldImage))
								$this->Session->setFlash("The new image/poster has been uploaded + old one removed! Thank you for contributing",'flash_success');
						}
						else
							$this->Session->setFlash("The new image/poster has been uploaded! Thank you for contributing",'flash_success');
						$this->redirect($this->referer());
					}
				}
			}

		}

	}

	function searchImage($where = null, $id = null){
		if($where == null || $id == null || !is_numeric($id) || trim($where) == "")
			die();
		if($this->Auth->User('id') == NULL)
			die();
		
		// Check if scrape info exists
		$scrapeInfo = $this->ScrapeInfo->find('first',array('conditions' => array(
			'anime_id' => $id,
			'scrape_source' => $where
			)));
		if($scrapeInfo == null)
		{

			echo "No link to " . $where . " is found";
			die();
		}
		$scrape_id = $scrapeInfo['ScrapeInfo']['scrape_id'];

		if($where == 'mal')
		{
			$url = "http://mal-api.com/anime/";
			$json = file_get_contents( $url . $scrape_id );
			
			$anime = json_decode($json, TRUE);

			echo '<form action="/anime/useImage/'.$id.'/image" method="post">';
			echo '<div class="thumbnail" style="float:left;">';
			echo '<input type="hidden" value="'.$anime['image_url'].'" name="imageUrl">';
			echo '<img src="http://src.sencha.io/0/150/'.$anime['image_url'].'">';
			echo '<input type="submit" class="btn btn-primary" value="Use as poster" name="submit">';
			echo '</div>';
			echo '</form>';

		}
		else if($where == 'thetvdb')
		{
			App::import('Vendor','Thetvdb', array('file' => 'class.thetvdb.php'));
			$tvdbapi = new Thetvdb('992BDB755BA8805D');

			$serieData = $tvdbapi->GetSerieFanart($scrape_id);
			
			
			$imgUrl = "http://thetvdb.com/banners/";
			foreach($serieData as $image){
				if($image['BannerType'] == "fanart")
				{
					
					echo '<form action="/anime/useImage/'.$id.'/fanart" method="post">';
					echo '<div class="thumbnail" style="float:left;">';
					echo '<input type="hidden" value="'.$imgUrl.$image['BannerPath'].'" name="imageUrl">';
					echo '<img src="http://src.sencha.io/0/150/'.$imgUrl.$image['BannerPath'].'">';
					echo '<input type="submit" class="btn btn-primary" value="Use as fanart" name="submit">';
					echo '</div>';
					echo '</form>';
				}
				else if($image['BannerType'] == "poster")
				{
					echo '<form action="/anime/useImage/'.$id.'/image" method="post">';
					echo '<div class="thumbnail" style="float:left;">';
					echo '<input type="hidden" value="http://src.sencha.io/225/'.$imgUrl.$image['BannerPath'].'" name="imageUrl">';
					echo '<img src="http://src.sencha.io/0/150/'.$imgUrl.$image['BannerPath'].'">';
					echo '<input type="submit" class="btn btn-primary" value="Use as poster" name="submit">';
					echo '</div>';
					echo '</form>';
				}
			}
		}
		else if($where == 'themoviedb'){
			App::import('Vendor','Themoviedb', array('file' => 'class.themoviedb.php'));
			$moviedb = new TMDBv3(THEMOVIEDB_APIKEY);
			$posters = array_merge($moviedb->moviePoster($scrape_id),$moviedb->movieBackdrops($scrape_id));
			$imgUrl = $moviedb->getImageURL();
			foreach($posters as $image)
			{
				// if it is a backdrop/fanart
				if($image['width'] > $image['height'])
				{
					echo '<form action="/anime/useImage/'.$id.'/fanart" method="post">';
					echo '<div class="thumbnail" style="float:left;">';
					echo '<input type="hidden" value="'.$imgUrl.$image['file_path'].'" name="imageUrl">';
					echo '<img src="http://src.sencha.io/0/150/'.$imgUrl.$image['file_path'].'">';
					echo '<input type="submit" class="btn btn-primary" value="Use as fanart" name="submit">';
					echo '</div>';
					echo '</form>';
				}
				else {
					echo '<form action="/anime/useImage/'.$id.'/image" method="post">';
					echo '<div class="thumbnail" style="float:left;">';
					echo '<input type="hidden" value="http://src.sencha.io/225/'.$imgUrl.$image['file_path'].'" name="imageUrl">';
					echo '<img src="http://src.sencha.io/0/150/'.$imgUrl.$image['file_path'].'">';
					echo '<input type="submit" class="btn btn-primary" value="Use as poster" name="submit">';
					echo '</div>';
					echo '</form>';
				}
			}
			
			//debug($posters);
		}
		else if($where == 'anidb'){

			$CLIENTNAME = 'calendar';
			$CLIENTVERSION = '1';
			//$port = 9001;
			
			//$anidbURL = "http://api.anidb.net/httpapi?client=".$CLIENTNAME."&clientver=".$CLIENTVERSION."&protover=1&request=anime&aid=".$scrape_id;
			$anidbURL = "http://158.39.171.120/anidb/anidb.php?aid=".$scrape_id."&client=".CLIENTNAME."&version=".CLIENTVERSION;
			$port = 80;
			// Blocked access for this---- hmmm
			//$response = file_get_contents($anidbURL);

			$crl = curl_init();
			$timeout = 20;
			curl_setopt($crl, CURLOPT_URL,$anidbURL);
			curl_setopt($crl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($crl, CURLOPT_ENCODING,'gzip');
			curl_setopt($crl, CURLOPT_HEADER,0);
			curl_setopt($crl, CURLOPT_FRESH_CONNECT, 1);
			curl_setopt($crl, CURLOPT_CONNECTTIMEOUT, $timeout);
			curl_setopt($crl, CURLOPT_PORT, $port);
			$response = curl_exec($crl) or die(curl_error($crl));

			$anime = new SimpleXMLElement($response);
			curl_close($crl);
			$imgUrl = "http://img7.anidb.net/pics/anime/";
			
			var_dump($anime);
			
			echo '<form action="/anime/useImage/'.$id.'/image" method="post">';
			echo '<div class="thumbnail" style="float:left;">';
			echo '<input type="hidden" value="'.$imgUrl.$anime->picture.'" name="imageUrl">';
			echo '<img src="http://src.sencha.io/0/150/'.$imgUrl.$anime->picture.'">';
			echo '<input type="submit" class="btn btn-primary" value="Use as poster" name="submit">';
			echo '</div>';
			echo '</form>';
		}
		die();
	}

	function searchReferences($where = null, $id = null){
		if($where == null || $id == null || !is_numeric($id) || trim($where) == "")
			die();

		// Check if the user is logged inn
		if($this->Auth->User('id') == NULL)
			die();
		// Check if anime exists
		$this->Anime->recursive = -1;
		$anime = $this->Anime->findById($id);
		//print_r($anime);
		if($anime == null){
			echo "This anime does not exist.";
			die();
		}
		
		$title = $anime['Anime']['title'];
		echo "<hr><ul>";
		// Fetch from myanimelist
		if($where == "myanimelist")
		{
			$url = "http://mal-api.com/anime/search?q=".$this->clean_url($title);
			//$url = "http://mal-api.com/anime/search?q=haruki";
			
			$json = json_decode(file_get_contents($url),true);

			if(count($json) == 0)
			{
				echo "Could not find any anime matching ". $title;
				die();
			}

			
			foreach($json as $anime)
				$this->showReference($where, $anime['title'],$anime['id'], $id, $anime['image_url']);
			
		}
		else if($where == "anidb"){
			$url = "http://anisearch.outrance.pl/index.php?task=search&query=".$this->clean_url($title);
			$response = new SimpleXMLElement(file_get_contents($url));

			foreach($response->anime as $anime)
			{
				$anidbId = $anime->attributes()->aid;
				$anidbtitle = '';
				$anidbImage = $anime->picture;
				foreach($anime->title as $xmltitle)
				{
					if($xmltitle->attributes()->lang == "x-jat" && $xmltitle->attributes()->type == "main")
					{
						$anidbtitle = $xmltitle;
						break;
					}
					if($xmltitle->attributes()->lang == "en" && $xmltitle->attributes()->type == "official")
					{
						$anidbtitle = $xmltitle;
						break;
					}
					else if($xmltitle->attributes()->lang == "en" && $xmltitle->attributes()->exact == "exact")
					{
						$anidbtitle = $xmltitle;
						continue;
					}
					else if($xmltitle->attributes()->lang == "en" && $anidbtitle == "")
					{
						$anidbtitle = $xmltitle;
						continue;
					} 
					
				}
				//print_r($anime);

				$this->showReference( $where, $anidbtitle,$anidbId, $id, $anidbImage );
				//print_r($anime);
			}
			//debug($response);
		}else if($where == "thetvdb"){

			App::import('Vendor','Thetvdb', array('file' => 'class.thetvdb.php'));
			$tvdbapi = new Thetvdb('992BDB755BA8805D');
			$serie_info = $tvdbapi->SearchSeries($title);
			
			foreach($serie_info as $serie)
			{
				//print_r($serie);
				$this->showReference($where,$serie->SeriesName,$serie->seriesid,$id, $serie->poster);
			}
			//print_r($serie_info);
		}else if($where == "themoviedb"){
			App::import('Vendor','Themoviedb', array('file' => 'class.themoviedb.php'));
			$moviedb = new TMDBv3(THEMOVIEDB_APIKEY);
			
			$serie_info = $moviedb->searchMovie($title);
			foreach($serie_info['results'] as $serie)
			{
				$this->showReference($where,$serie['title'],$serie['id'],$id);
				//debug($serie);
			}
		}
		echo "</ul>";
		die();
	}

	private function showReference($where, $title, $id, $ourAnimeId, $image = ""){
		$thetvdbSelected = "";
		$anidbSelected = "";
		$themoviedbSelected = "";
		$malSelected = "";
		$episodes = '';
		$information = '';
		$scrapeid = $id;
		echo '<li class="well" style="list-style-type:none;">';
		echo '<form action="/anime/addref/'.$ourAnimeId.'" method="post">';

		if($where == "myanimelist"){
			echo '<div class="span1"><img class="thumbnail" src="'.$this->addBeforeExtension("t",$image).'"></div><span class="span4">';
			echo '
				<a href="http://myanimelist.net/anime/'.$id.'">' . 
					$title . 
				'</a>
			 	';
			$episodes = '';
			$information = 'checked="checked"';
			$malSelected = 'selected="selected"';
		}
		else if($where == "anidb"){
			echo '<span class="span4">';
			echo '
				<a href="http://anidb.net/perl-bin/animedb.pl?show=anime&aid='.$id.'">' . 
					$title . 
				'</a>
			 	';
			$episodes = 'checked="checked"';
			$information = 'checked="checked"';
			$anidbSelected = 'selected="selected"';
		}
		else if($where == "thetvdb"){
			echo '<span class="span4">';
			echo '
				<a href="http://thetvdb.com/?tab=series&id='.$id.'">' . 
					$title . 
				'</a>
			 	';
			$thetvdbSelected = 'selected="selected"';
		}
		else if($where == 'themoviedb'){
			echo '<span class="span4">';
			echo 
				'<a href="http://themoviedb.org/movie/'.$id.'">
					'.$title.'
				</a>';
			$themoviedbSelected = ' selected="selected"';
		}

		 	echo '<select name="data[ScrapeInfo][scrape_source]" class="no-display" id="ScrapeInfoScrapeSource">
					<option '.$anidbSelected.' value="anidb">anidb</option>
					<option '.$thetvdbSelected.' value="thetvdb">thetvdb</option>
					<option '.$malSelected.' value="mal">mal</option>
					<option '.$themoviedbSelected.' value="themoviedb">themoviedb</option>
				</select>';

		echo '<input name="data[ScrapeInfo][scrape_id]" value="'.$scrapeid.'" type="text" style="width:100px" class="no-display" id="ScrapeInfoScrapeId">';
		/*echo '<input name="data[ScrapeInfo][scrape_episodes]" type="text" style="width:100px" class="scrape_episodes" maxlength="20" id="ScrapeInfoScrapeEpisodes">';*/
		echo '<input type="checkbox" name="data[ScrapeInfo][fetch_episodes]" '.$episodes.' class="no-display" value="1" id="ScrapeInfoFetchEpisodes">';
		echo '<input type="checkbox" name="data[ScrapeInfo][fetch_information]" '.$information.' class="no-display" value="1">';
		echo '<br><button type="submit">Use this</button></form></span><br class="clear">';
		echo '</li>';
		
	}

	private function addBeforeExtension($extend, $url){
		$pos = strrpos( $url, "." );
		$result = "";
		$result .= substr( $url, 0, $pos ) . $extend . substr( $url, $pos );
		return $result;
	}

	function index() {
		$this->Anime->recursive = -1;
		$this->set('anime', $this->Anime->find('all'));
		//$this->AnimeRatingBayes->recursive = 1;
		$this->set('animerating',$this->AnimeRatingBayes->getHighestRated(5));

	}
	
	function add() {
		if($this->Auth->User('id') == NULL)
			$this->redirect($this->referer());
		if($this->Auth->User('id') != 1)
		{
			// Add new request for anime
			if(!empty($this->request->data)){
				App::uses('Sanitize', 'Utility');
				$this->request->data = Sanitize::clean($this->request->data, array('encode' => false));
				// Check if the request for this anime has already been sendt.
				//print_r($this->request->clientIp);
				$request_exists = $this->AnimeRequest->find('count',array('conditions' =>
					array('LOWER(title)' => trim(strtolower($this->request->data['AnimeRequest']['title'])))));
				$anime_exists = $this->Anime->find('count',array('conditions' =>
					array('LOWER(title)' => trim(strtolower($this->request->data['AnimeRequest']['title'])))));

				/*print_r($request_exists);
				print_r($anime_exists);*/

				if($request_exists == 0 && $anime_exists == 0)
				{
					$this->AnimeRequest->create();
					$this->AnimeRequest->set('title',$this->request->data['AnimeRequest']['title']);
					$this->AnimeRequest->set('comment',$this->request->data['AnimeRequest']['comment']);
					$this->AnimeRequest->set('ip_adress',$_SERVER["REMOTE_ADDR"]);
					$this->AnimeRequest->set('user_id',$this->Auth->User('id'));
					if($this->AnimeRequest->save())
						$this->Session->setFlash("A request for this anime has been sendt. Thank you for contributing ;)",'flash_success');

				}
				else if($request_exists != 0){
					$this->Session->setFlash("A request for this anime has already been sendt!",'flash_error');
				}
				else if($anime_exists != 0){
					$this->Session->setFlash("Anime already exists in the database!",'flash_error');

				}
				$this->redirect($this->referer());
			}
			return;
		}
		else if(!empty($this->request->data)){
				App::uses('Sanitize', 'Utility');
				$this->request->data = Sanitize::clean($this->request->data, array('encode' => false));
			if($this->Anime->save($this->request->data)) {

				// ADDING ACTIVITY
				$this->Activity->create();
				$this->Activity->set('subject_type','user');
				$this->Activity->set('subject_id',$this->Auth->User('id'));
				$this->Activity->set('verb','added');
				$this->Activity->set('object_type','anime');
				$this->Activity->set('object_id',$this->Anime->id);
				$this->Activity->save();

				// Create new animesynonym with the title
				$this->AnimeSynonym->create();
				$this->AnimeSynonym->set('title',$this->request->data['Anime']['title']);
				$this->AnimeSynonym->set('anime_id',$this->Anime->id);
				$this->AnimeSynonym->save();

				$this->Session->setFlash("Anime Saved!",'flash_success');
				$this->redirect('/anime/view/'.$this->Anime->id.'/'.$this->Anime->title);
			}
		}
	}
	
	function editImage($id = null)
	{
		//App::uses('AttachmentComponent','Component');
		//debug($this);
		if($this->Auth->User('id') == NULL)
			$this->redirect($this->referer());
		if($id == null)
			$this->redirect($this->referer());
		$this->set('anime',$this->Anime->find('first', array('conditions' => array('Anime.id' => $id))));
		$this->getAnimeUser($id);	
		
		if(!empty($this->request->data))
		{
			App::uses('Sanitize', 'Utility');
			$this->request->data = Sanitize::clean($this->request->data, array('encode' => false));
			
			
			$this->Anime->read(null,$id);
			//debug($this->request->data);

			//Upload the new image
			if(!empty($this->request->data['Anime']['image']) && $this->Attachment->upload($this->request->data['Anime'],'image'))
			{
				//debug($this->request->data);
				// Delete the old image
				$oldImage = $this->Anime->read('image',$id);
				echo "delete ". $oldImage['Anime']['image'];
				$this->Attachment->delete_files($oldImage['Anime']['image']);

				$this->Anime->set('image',$this->request->data['Anime']['image_file_path']);
				if($this->Anime->save()){
					$this->Session->setFlash("Image is saved. Thank you for contributing :)",'flash_success');
					// ADDING ACTIVITY
					$this->Activity->create();
					$this->Activity->set('subject_type','user');
					$this->Activity->set('subject_id',$this->Auth->User('id'));
					$this->Activity->set('verb','added');
					$this->Activity->set('object_type','image');
					$this->Activity->set('object_id',$this->Anime->id);
					$this->Activity->save();
				}
				else
					$this->Session->setFlash("Could not save image.. Sorry",'flash_error');
				$this->redirect("/anime/editImage/".$id);
			}

			if(!empty($this->request->data['Anime']['fanart']) && $this->Attachment->upload($this->request->data['Anime'],'fanart'))
			{
				//debug($this->request->data);
				// Delete the old image
				$oldImage = $this->Anime->read('fanart',$id);
				echo "delete ". $oldImage['Anime']['fanart'];
				$this->Attachment->delete_files($oldImage['Anime']['fanart']);

				$this->Anime->set('fanart',$this->request->data['Anime']['fanart_file_path']);
				if($this->Anime->save()){
					$this->Session->setFlash("Fanart is saved. Thank you for contributing :)",'flash_success');
					// ADDING ACTIVITY
				$this->Activity->create();
				$this->Activity->set('subject_type','user');
				$this->Activity->set('subject_id',$this->Auth->User('id'));
				$this->Activity->set('verb','added');
				$this->Activity->set('object_type','fanart');
				$this->Activity->set('object_id',$this->Anime->id);
				$this->Activity->save();
				}
				else
					$this->Session->setFlash("Could not save fanart.. Sorry",'flash_error');
				$this->redirect("/anime/editImage/".$id);
			}
		}
	}
	
	private function getAnimeUser($id = null)
	{
		if(!is_numeric($id))
			$this->redirect($this->referer());
		
		$rate = $this->AnimeRating->find('first',array(
			'fields' => array(
				'anime_id',
				'AVG(rate) as avg_rate',
				'COUNT(*) as amount'
				),
			'conditions' => array(
					'anime_id' => $id
				)
			));
		$this->set('calc_rating',$rate[0]);

		//$this->set()
		if($this->Auth->User('id') != NULL)
		{
			$uid = $this->Auth->User('id');
			
			//pr($this->Anime->AnimelistEntry);
			$animeuser = $this->AnimeRating->find('first',array(
			'conditions' => array(
					'anime_id' => $id,
					'user_id' => $uid
				)
			));
			$this->UserWatchlist->recursive = -1;
			$this->set('watchlist',$this->UserWatchlist->find('first',array(
				'conditions' => array(
						'user_id' => $uid,
						'anime_id' => $id
					)
				)));
			$this->set('user_rate',$animeuser);
		}
	}

	function rate($id = null, $rate = null){
		if($id == null || $rate == null)
			$this->redirect($this->referer());

		if($rate > 5)
			$rate = 5;
		if($rate < 0)
			$rate = 1;

		if($this->Auth->User('id') == null)
			$this->redirect($this->referer());
		// Check if rating already exists
		$rateDB = $this->AnimeRating->find('first', array(
			'conditions' => array(
				'anime_id' => $id,
				'user_id' => $this->Auth->User('id')
				)
			)
		);
		//print_r($rateDB);
		if(count($rateDB) != 0 && $rateDB != null)
			$this->AnimeRating->read(null, $rateDB['AnimeRating']['id']);
		else{
			$this->AnimeRating->create();
			$this->AnimeRating->set('anime_id',$id);
			$this->AnimeRating->set('user_id',$this->Auth->User('id'));
		}
		$this->AnimeRating->set('rate',$rate);

		if($this->AnimeRating->save())
			$this->Session->setFlash('Rating saved.','flash_success');
		else
			$this->Session->setFlash('Could not save rating.','flash_error');
		$this->redirect($this->referer());

	}

	function view($id = null) {

		$this->Anime->id = $id;
		$this->Anime->read();
		$anime = $this->Anime->find('first', array('conditions' => array('Anime.id' => $id)));
		$this->set('anime', $anime );
		$this->set('title_for_layout',$anime['Anime']['title']);
		$this->pageTitle = $this->Anime->data['Anime']['title'];
		$this->set('genres',$this->Anime->AnimeGenre->find('all',array('conditions' => array('anime_id' => $id))));
		$this->set('animeuser',NULL);
		
		$this->set('user_email',$this->Auth->User('email'));
		$this->set('user_id',$this->Auth->User('id'));
		
		$this->set('prequels', $this->AnimeRelationship->find('all', array('conditions' => array('anime1' => $id))));
		$this->set('sequels', $this->AnimeRelationship->find('all', array('conditions' => array('anime2' => $id))));

		$this->getAnimeUser($id);

		if($this->Auth->User('id') != null)
		{
			$userepisodes = $this->UserEpisode->find('all',array(
				'conditions' => array(
						'user_id' => $this->Auth->User('id'),
						'anime_id' => $id
					)
			));	
			$this->set('userepisodes',$userepisodes);
		}

		$this->Activity->recursive = 0;
		//$animeActivities = $this->Activity->find('all',array('conditions' => array('object_id' => $id)));
		$animeActivities_episodes = $this->Activity->find('all', array('conditions' => array(
			"(object_id=".$id." AND object_type IN('fanart','image','anime','reference')) OR (object_type='episode' AND object_id IN (SELECT episodes.id FROM episodes WHERE episodes.anime_id=".$id."))"
			)));

		//$activities = $this->Anime->Episode->UserEpisode;
		
		$this->UserEpisode->recursive = 0;
		$ep_seen = $this->UserEpisode->find('all',array(
			'fields' => array('COUNT(*) as amount,User.email, UserEpisode.user_id'),
			'conditions' => array('episode_id IN (SELECT episodes.id FROM episodes WHERE episodes.anime_id='.$id.')'),
			'group' => array('UserEpisode.user_id'),
			'order' => array('UserEpisode.id DESC')
			));
		$this->set('ep_seen', $ep_seen);
		$activities = $this->Anime->getActivity($id);
		//debug($ep_seen);
		$this->Episode->recursive = -1;
		$this->Anime->recursive = -1;

		$this->Comment->recursive = -1;
		$comments = $this->Comment->findAllByAnime_id($id);
		$this->set("comments",$comments);

		;
		
		$this->set('activities',$activities);
	}
	function viewepisodes($id = null)
	{
		$this->Anime->id = $id;
		$anime = $this->Anime->read();
		$this->set('anime', $anime);
		$this->set('genres', $this->Anime->AnimeGenre->find('all', array('conditions' => array('anime_id' => $id))));
		$this->set('animeuser', NULL);
		$this->getAnimeUser($id);
		if($this->Auth->User('id') != NULL)
		{
			
			$uid = $this->Auth->User('id');
			$this->UserEpisode->recursive = 0;
			//pr($this->Anime->AnimelistEntry);
			$animeuser = $this->UserEpisode->find('all', 
				array('conditions' => 
					array(
						'user_id' => $this->Auth->User('id'),
						'anime_id' => $id
						)
					)
				);
			$this->set('animeuser',$animeuser);
		}
		$this->set('episodes', $this->paginate($this->Anime->Episode, array('Anime.id' => $id)));	
	}

	function scrape() {
		//$logfile = '/home/groenlid/Git/Uranime/app/tmp/logs/scrape.log';
		$logfile = '/home/groenlid/public_html/app/tmp/logs/scrape.log';
		//passthru("/usr/bin/php /home/groenlid/Git/Uranime/lib/Cake/Console/cake.php scrape -app /home/groenlid/Git/Uranime/app > " . $logfile . " &");
		//passthru('/home/content/00/8758600/html/app/Console/cake scrape > ' . $logfile . ' &');
		//passthru('which php5 > '. $logfile );
		passthru('php5 /home/groenlid/public_html/lib/Cake/Console/cake.php scrape -app /home/groenlid/public_html/app > '. $logfile . ' &'); 
		$this->set('logfile',$logfile);
	}

	function getlogfile() {
		//$logfile = '/home/groenlid/Git/Uranime/app/tmp/logs/scrape.log';
		$logfile = '/home/groenlid/public_html/app/tmp/logs/scrape.log';
		$content = file($logfile);
		for($i = count($content); $i > 0; $i--)
			echo $content[$i-1];	
		die();
	}

	function viewtags($id = null)
	{
		$this->Anime->id = $id;
		$this->Anime->read();

		$this->set('anime', $this->Anime->find('first', array('conditions' => array('Anime.id' => $id))));

		$this->pageTitle = $this->Anime->data['Anime']['title'];
		$this->set('genres',$this->Anime->AnimeGenre->find('all',array('conditions' => array('anime_id' => $id))));
		$this->set('animeuser',NULL);

		$this->getAnimeUser($id);
		
		if($this->Auth->User('id') != null)
		{
			$userepisodes = $this->UserEpisode->find('all',array(
				'conditions' => array(
						'user_id' => $this->Auth->User('id'),
						'anime_id' => $id
					)
			));	
			$this->set('userepisodes',$userepisodes);
		}
	}

	function viewref($animeid = null) {
		
		if($animeid == null || !is_numeric($animeid) || $this->Auth->User('id') == null)
		{
			$this->Session->setFlash('Please log inn to visit this site','flash_warning');
			$this->redirect($this->referer());
			return;
		}
		$this->Anime->id = $animeid;
		$anime = $this->Anime->read();
		$this->set('anime', $anime);
		$this->request->data = $this->ScrapeInfo->findAllByAnime_id($animeid);
		$this->set('info',$this->request->data);
		$this->set('siteid',$animeid);

		$this->set('animeuser', NULL);
	
		if($this->Auth->User('id') != NULL)
		{
			$uid = $this->Auth->User('id');
			
			$this->getAnimeUser($animeid);
		}



	}

	function addref($animeid = null){
		Configure::write('debug', 0);
		if(empty($this->request->data) || $animeid == null || !is_numeric($animeid) || $this->Auth->User('id') == null)
		{
			$this->Session->setFlash('Could not add reference link. Sorry','flash_error');
			$this->redirect($this->referer());
			return;
		}
		
		$scrape_id = $this->request->data['ScrapeInfo']['scrape_id'];
		$scrape_episodes = isset($this->request->data['ScrapeInfo']['scrape_episodes']) ? $this->request->data['ScrapeInfo']['scrape_episodes'] : NULL;
		$scrape_source = $this->request->data['ScrapeInfo']['scrape_source'];
		//$fetch_episodes = isset($this->request->data['ScrapeInfo']['fetch_episodes']) ? 1 : NULL;
		//$fetch_information = isset($this->request->data['ScrapeInfo']['fetch_information']) ? 1 : NULL;
		$fetch_episodes = isset($this->request->data['ScrapeInfo']['fetch_episodes']) && $this->request->data['ScrapeInfo']['fetch_episodes'] == 1? 1 : NULL;
		$fetch_information = isset($this->request->data['ScrapeInfo']['fetch_information']) && $this->request->data['ScrapeInfo']['fetch_information'] == 1 ? 1 : NULL;
		
		if($scrape_id == '' || !is_numeric($scrape_id))
		{
			$this->Session->setFlash('Invalid data','flash_error');
			$this->redirect($this->referer());
			return;
		}

		// Check if it already exists
		$count = $this->ScrapeInfo->find('count',array(
			'conditions' => array(
					'scrape_source' => $scrape_source,
					'scrape_id' => $scrape_id,
					'anime_id' => $animeid
				)
			));
		if($count != 0)
		{
			$this->Session->setFlash('This reference has already been added to this anime. Nothing done','flash_error');
			$this->redirect($this->referer());
			return;
		}

		// Check if anime exists
		$countAnime = $this->Anime->find('count',array(
			'conditions' => array(
					'id' => $animeid
				)
			));
		if($countAnime == 0)
		{
			$this->Session->setFlash('This anime does not exist','flash_error');
			$this->redirect($this->referer());
			return;
		}
		$this->ScrapeInfo->create();
		$this->ScrapeInfo->set('scrape_source',$scrape_source);
		$this->ScrapeInfo->set('scrape_episodes',$scrape_episodes);
		$this->ScrapeInfo->set('scrape_id',$scrape_id);
		$this->ScrapeInfo->set('fetch_episodes', $fetch_episodes);
		$this->ScrapeInfo->set('fetch_information',$fetch_information);
		$this->ScrapeInfo->set('anime_id',$animeid);
		$this->ScrapeInfo->set('scrape_needed',1);
		$this->ScrapeInfo->save();

		// ADDING ACTIVITY
		$this->Activity->create();
		$this->Activity->set('subject_type','user');
		$this->Activity->set('subject_id',$this->Auth->User('id'));
		$this->Activity->set('verb','added');
		$this->Activity->set('object_type','reference');
		$this->Activity->set('object_id',$animeid);
		$this->Activity->set('option',$scrape_source);
		$this->Activity->save();


		$this->Session->setFlash('Thank you for contributing. New reference link added','flash_success');
		//if($redir == null)
		$this->redirect("/anime/viewref/".$animeid);
		//return;
	}

	function editref($id){
		Configure::write('debug', 0);
		if($id == null || !is_numeric($id))
		{
			$this->redirect($this->referer());
			return;
		}
		// Check if the user is logged inn
		if($this->Auth->User('id') == NULL)
		{
			$this->Session->setFlash('You are not allowed on this page.','flash_warning');
			$this->redirect($this->referer());
			return;
		}

		// The id is ScrapeInfo.id
		if(empty($this->request->data))
		{
			$this->Session->setFlash('No data to update. Redirect to last page.','flash_warning');
			$this->redirect($this->referer());
			return;
		}
		//print_r($this->request->data);
		//return;
		
		$scrape_id = $this->request->data['ScrapeInfo']['scrape_id'];
		$scrape_episodes = isset($this->request->data['ScrapeInfo']['scrape_episodes']) ? $this->request->data['ScrapeInfo']['scrape_episodes'] : NULL;
		$scrape_source = $this->request->data['ScrapeInfo']['scrape_source'];
		$fetch_episodes = isset($this->request->data['ScrapeInfo']['fetch_episodes']) && $this->request->data['ScrapeInfo']['fetch_episodes'] == 1? 1 : NULL;
		$fetch_information = isset($this->request->data['ScrapeInfo']['fetch_information']) && $this->request->data['ScrapeInfo']['fetch_information'] == 1 ? 1 : NULL;

				// Check if it already exists
		$count = $this->ScrapeInfo->find('count',array(
			'conditions' => array(
					'scrape_source' => $scrape_source,
					'scrape_id' => $scrape_id,
					'anime_id' => $animeid
				)
			));
		if($count != 0)
		{
			$this->Session->setFlash('This reference has already been added to this anime. Nothing done','flash_warning');
			$this->redirect($this->referer());
			return;
		}

		$this->ScrapeInfo->read(NULL,$id);
		$this->ScrapeInfo->set('scrape_id',$scrape_id);
		$this->ScrapeInfo->set('scrape_source',$scrape_source);
		$this->ScrapeInfo->set('scrape_episodes',$scrape_episodes);
		$this->ScrapeInfo->set('fetch_episodes', $fetch_episodes);
		$this->ScrapeInfo->set('fetch_information', $fetch_information);
		$this->ScrapeInfo->set('scrape_needed',1);
		$this->ScrapeInfo->save();
		$this->Session->setFlash('The reference link has been updated.','flash_success');
		$this->redirect($this->referer());
		//var_dump($this->ScrapeInfo->fetch_episodes);
		return;
	}

	function setEpisodeScrape($id = null, $override = null)
	{
		// This is an automatic scraper that triggers when user
		// updates their library.
		//
		if($id == null || !is_numeric($id) || ($this->Auth->User('id') == NULL && $override != "true"))
		{
			$this->Session->setFlash('Could not update scrape for this anime.. We\'re Sorry','flash_error');
			$this->redirect($this->referer());
			return;	
		}

		$uid = $this->Auth->User('id');
		
		// Check if the anime is finished. If it is. DO NOT SCRAPE!
		$animetmp = $this->Anime->read(NULL,$id);
		if($this->Anime->data['Anime']['status'] == 'finished')
		{
			//$this->redirect($this->referer());
			return false;
		}
		foreach($this->Anime->data['ScrapeInfo'] as $scrape)
		{
			if($scrape['fetch_episodes'] != 1)
				continue;
			$this->ScrapeInfo->read('id',$scrape['id']);
			$this->ScrapeInfo->set('scrape_needed',1);
			$this->ScrapeInfo->save();
		}
		return true;

		//$this->redirect($this->referer());

	}

	function setScrape($id = null) {
		if($id == null || !is_numeric($id) || $this->Auth->User('id') == NULL)
		{
			$this->Session->setFlash('Could not update scrape for this anime.. We\'re Sorry','flash_error');
			$this->redirect($this->referer());
			return;	
		}

		$uid = $this->Auth->User('id');
		
			// This is a temporary hack. Sets the scrape_needed to 1 on all scrape_sites
			// for one anime.
		$this->Anime->read('id',$id);
		foreach($this->Anime->data['ScrapeInfo'] as $scrape)
		{
			$this->ScrapeInfo->read('id',$scrape['id']);
			$this->ScrapeInfo->set('scrape_needed',1);
			$this->ScrapeInfo->save();
		}

		$this->Session->setFlash('This anime is set in queue to be scraped ;)','flash_success');
		$this->redirect($this->referer());

	}

}

/*
require_once('class.thetvdbapi.php');

// create object
$tvapi = new Thetvdb('apikey');

// get serie id for 'fringe'
$serieid = $tvapi->GetSerieId('fringe');

// get episode id for fringe S01E01
$episodeid = $tvapi->GetEpisodeId($serieid,1,1);

// get information about the episode
$ep_info = $tvapi->GetEpisodeData($episodeid);

// get information about the serie, without the episodes
$serie_info = $tvapi->GetSerieData($serieid);

// get information about the serie, including the episodes
$serie_info = $tvapi->GetSerieData($serieid,true);
*/
?>
