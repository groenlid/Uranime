<?php
class AnimeController extends AppController {
	var $helpers = array('Text','Form');
	var $uses = array('User','Anime','Episode','Activity','AnimelistEntry','ScrapeInfo','AnimeGenre', 'AnimeRelationship','UserEpisode','AnimeRating');
	var $paginate = array( 'Episode' => array(
						'limit' => 50,
						'order' => array(
							'Episode.number' => 'desc'
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
		echo "<ul>";
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
				$this->showReference($where, $anime['title'],$anime['id'], $id);
			
		}
		else if($where == "anidb"){
			$url = "http://anisearch.outrance.pl/index.php?task=search&query=".$this->clean_url($title);
			$response = new SimpleXMLElement(file_get_contents($url));

			foreach($response->anime as $anime)
			{
				$anidbId = $anime->attributes()->aid;
				$anidbtitle = '';
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
				//print_r($anime->lang);

				$this->showReference($where,$anidbtitle,$anidbId,$id);
				//print_r($anime);
			}
			//debug($response);
		}else if($where == "thetvdb"){

			App::import('Vendor','Thetvdb', array('file' => 'class.thetvdb.php'));
			$tvdbapi = new Thetvdb('992BDB755BA8805D');
			$serie_info = $tvdbapi->SearchSeries($title);
			
			foreach($serie_info as $serie)
			{
				$this->showReference($where,$serie->SeriesName,$serie->seriesid,$id);
			}
			//print_r($serie_info);
		}
		echo "</ul>";
		die();
	}

	private function showReference($where, $title, $id, $ourAnimeId){
		$thetvdbSelected = "";
		$anidbSelected = "";
		$malSelected = "";
		$episodes = '';
		$images = '';
		$information = '';
		$scrapeid = $id;
		echo '<li>';
		echo '<form action="/anime/addref/'.$ourAnimeId.'" method="post">';
		echo '<button type="submit">Use this</button>';
		if($where == "myanimelist"){
			echo '
				<a href="http://myanimelist.net/anime/'.$id.'">[MAL] ' . 
					$title . 
				'</a>
			 	-';
			$episodes = '';
			$images = '';
			$information = 'checked="checked"';
			$malSelected = 'selected="selected"';
		}
		else if($where == "anidb"){
			echo '
				<a href="http://anidb.net/perl-bin/animedb.pl?show=anime&aid='.$id.'">[ANIDB] ' . 
					$title . 
				'</a>
			 	-';
			$episodes = 'checked="checked"';
			$images = '';
			$information = 'checked="checked"';
			$anidbSelected = 'selected="selected"';
		}
		else if($where == "thetvdb"){
			echo '
				<a href="http://thetvdb.com/?tab=series&id='.$id.'">[THETVDB] ' . 
					$title . 
				'</a>
			 	-';
			$thetvdbSelected = 'selected="selected"';
		}

		 	echo '<select name="data[ScrapeInfo][scrape_source]" class="no-display" id="ScrapeInfoScrapeSource">
					<option '.$anidbSelected.' value="anidb">anidb</option>
					<option '.$thetvdbSelected.' value="thetvdb">thetvdb</option>
					<option '.$malSelected.' value="mal">mal</option>
				</select>';

		echo '<input name="data[ScrapeInfo][scrape_id]" value="'.$scrapeid.'" type="text" style="width:100px" class="no-display" id="ScrapeInfoScrapeId">';
		/*echo '<input name="data[ScrapeInfo][scrape_episodes]" type="text" style="width:100px" class="scrape_episodes" maxlength="20" id="ScrapeInfoScrapeEpisodes">';*/
		echo '<input type="checkbox" name="data[ScrapeInfo][fetch_episodes]" '.$episodes.' class="no-display" value="1" id="ScrapeInfoFetchEpisodes">';
		echo '<input type="checkbox" name="data[ScrapeInfo][fetch_images]" '.$images.' class="no-display" value="1">';
		echo '<input type="checkbox" name="data[ScrapeInfo][fetch_information]" '.$information.' class="no-display" value="1">';
		echo '</form>';
		echo '</li>';
		
	}

	function index() {
		$this->Anime->recursive = -1;
		$this->set('anime', $this->Anime->find('all'));
	}
	
	function add() {
		if($this->Auth->User('id') == NULL)
			$this->redirect($this->referer());
		if(!empty($this->request->data)){
			if($this->Anime->save($this->request->data)) {

				// ADDING ACTIVITY
				$this->Activity->create();
				$this->Activity->set('subject_type','user');
				$this->Activity->set('subject_id',$this->Auth->User('id'));
				$this->Activity->set('verb','added');
				$this->Activity->set('object_type','anime');
				$this->Activity->set('object_id',$this->Anime->id);
				$this->Activity->save();

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

		$this->set('anime', $this->Anime->find('first', array('conditions' => array('Anime.id' => $id))));

		$this->pageTitle = $this->Anime->data['Anime']['title'];
		$this->set('genres',$this->Anime->AnimeGenre->find('all',array('conditions' => array('anime_id' => $id))));
		$this->set('animeuser',NULL);
		$this->set('animeActivities',$this->Activity->find('all',array('conditions' => array('object_id' => $id))));
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
		$logfile = '/home/groenlid/Git/Sandbox_new/app/tmp/logs/scrape.log';
		passthru("/usr/bin/php /home/groenlid/Git/Sandbox_new/lib/Cake/Console/cake.php scrape -app /home/groenlid/Git/Sandbox_new/app > " . $logfile . " &");


		$this->set('logfile',$logfile);
	}

	function getlogfile() {
		$logfile = '/home/groenlid/Git/Sandbox_new/app/tmp/logs/scrape.log';
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
		$fetch_episodes = isset($this->request->data['ScrapeInfo']['fetch_episodes']) ? 1 : NULL;
		$fetch_images = isset($this->request->data['ScrapeInfo']['fetch_images']) ? 1 : NULL;
		$fetch_information = isset($this->request->data['ScrapeInfo']['fetch_information']) ? 1 : NULL;

		
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
		$this->ScrapeInfo->set('fetch_images', $fetch_images);
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
		$fetch_images = isset($this->request->data['ScrapeInfo']['fetch_images']) && $this->request->data['ScrapeInfo']['fetch_images'] == 1 ? 1 : NULL;
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
		$this->ScrapeInfo->set('fetch_images', $fetch_images);
		$this->ScrapeInfo->set('fetch_information', $fetch_information);
		$this->ScrapeInfo->set('scrape_needed',1);
		$this->ScrapeInfo->save();
		$this->Session->setFlash('The reference link has been updated.','flash_success');
		$this->redirect($this->referer());
		//var_dump($this->ScrapeInfo->fetch_episodes);
		return;
	}

	function setEpisodeScrape($id = null)
	{
		// This is an automatic scraper that triggers when user
		// updates their library.
		//
		if($id == null || !is_numeric($id) || $this->Auth->User('id') == NULL)
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

	function deleteEpisode($id = null){
		if($id == null || !is_numeric($id) || $this->Auth->User('id') == NULL)
		{
			$this->Session->setFlash('Could not delete this episode.. We\'re Sorry','flash_error');
			$this->redirect($this->referer());
			return;	
		}
		$uid = $this->Auth->User('id');

		if($uid == 1)
		{
			//$this->Episode->id = $id;
			$this->Episode->delete($id);
			$this->Session->setFlash('The episode has been deleted','flash_success');
			$this->redirect($this->referer());
		}
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
