<?php
class ScrapeShell extends AppShell {
	var $uses = array('AnimeSynonym', 'ScrapeInfo', 'Anime', 'AnimeGenre', 'Episode', 'Genre', 'AnimeRelationship');
	var $stats = array(
		'Anime_changed' => 0,
		'Episodes_changed' => 0,
		'Genre_linked' => 0,
		'Characters_changed' => 0,
		'Characters_added' => 0
		);
	function main(){
		App::uses('Sanitize', 'Utility');
		define('SCRAPEDEBUG',TRUE);

		// Variables to show at the end..

		// Fetch all the items in the queue

		$queue = $this->ScrapeInfo->find('all',array(
													'conditions' => array(
														'scrape_id !=' => 'NULL',
														'scrape_source !=' => 'NULL',
														'scrape_needed' => '1'
														)
													)
										);
		$lastAnidb = 0;
		foreach($queue as $itemOutdated)
		{
			//echo "id: '" . $itemOutdated['ScrapeInfo']['id'] . "'";
			// We need to use the updated item in case of editing
			$item = $this->ScrapeInfo->read(null,$itemOutdated['ScrapeInfo']['id']);
			//print_r($item);
			$source = $item['ScrapeInfo']['scrape_source'];
			$this->out('Fetching item: ' . $item['Anime']['title'] . " from source: " . $source);
			
			
			if($source == 'thetvdb')
			{
				$this->thetvdbScrape($item);
			} else if($source == 'mal')
			{
				$this->malScrape($item);
			} else if($source == 'anidb')
			{
				$now = time();
				if(($now - $lastAnidb) < 4)
					sleep($now - $lastAnidb);
				$this->anidbScrape($item);
				$lastAnidb = time();
			} else if($source == 'themoviedb')
			{
				$this->themoviedbScrape($item);
			}

			// Remove the scrape_needed on the queue
			//$item->set('scrape_needed', NULL);
			//$item->ScrapeInfo->save($item);
			$this->ScrapeInfo->id = $item['ScrapeInfo']['id'];
			$this->ScrapeInfo->read();
			$this->ScrapeInfo->set('scrape_needed',NULL);
			$this->ScrapeInfo->save();
			foreach($this->stats as $key => $value)
				$this->out("\t" . $key . " => " . $value);
			//print_r($this->stats);
			$this->stats = array();
			$this->out('Finished item..');
			$this->out('----------------');
			
		}
		
		$this->out('Finished parsing through queue');
	}
	
	function malScrape($item){
		// Using mal-api.com for fetching
		// Used for genres.
		$url = "http://mal-api.com/anime/";
		$json = file_get_contents( $url . $item['ScrapeInfo']['scrape_id'] );
		
		$anime = json_decode($json, TRUE);
		
		if($item['ScrapeInfo']['fetch_information'] == '1')
		{

			//$this->Anime->id = $item['ScrapeInfo']['anime_id'];
			$dbAnime = $this->Anime->read(NULL, $item['ScrapeInfo']['anime_id']);
			
			/* CHECK IF THE ANIME GOT DESCRIPTION OR STATUS BEFOREHAND */
			if($dbAnime['Anime']['desc'] == '' || $dbAnime['Anime']['desc'] == NULL)
			{
				if(SCRAPEDEBUG)
					$this->out("\t" ."\t" . "\t" . 'Adding new synopsis/description to anime:' . $dbAnime['Anime']['title'] . ' Desc:' . $anime['synopsis']);
				$this->Anime->set('desc',$anime['synopsis']);
				if($this->Anime->save())
					$this->stats['Anime_changed']++;
			}
			$status = array(
				'finished airing' => 'finished',
				'currently airing' => 'currently',
				'not yet aired' => 'unaired'
			);
			if($dbAnime['Anime']['status'] != $status[$anime['status']] || $dbAnime['Anime']['status'] == NULL || $dbAnime['Anime']['status'] == '')
			{
				if(SCRAPEDEBUG)
					$this->out("\t" ."\t" . "\t" . 'Adding new status to anime:' . $dbAnime['Anime']['title'] .'.'. ' Status:' . $dbAnime['Anime']['status'] . '->' . $status[$anime['status']]);

				$this->Anime->set('status',$status[$anime['status']]);
				if($this->Anime->save())
					$this->stats['Anime_changed']++;
			}

			if($dbAnime['Anime']['runtime'] == '' || $dbAnime['Anime']['runtime'] == null)
			{
				/** TODO: Implement runtime fetching from mal **/
			}
			
			$type = array(
				'TV' => 'tv',
				'OVA' => 'ova',
				'ONA' => 'ona',
				'Special' => 'special',
				'Movie' => 'movie'
			);
			
			if($dbAnime['Anime']['type'] == '' || $dbAnime['Anime']['type'] == null || $dbAnime['Anime']['type'] != $type[$anime['type']])
			{
				if(SCRAPEDEBUG)
					$this->out("\t" ."\t" . "\t" . 'Adding new type to anime:' . $dbAnime['Anime']['title'] .'.'. ' Type:' . $dbAnime['Anime']['type'] . '->' . $type[$anime['type']]);
				$this->Anime->set('type',$type[$anime['type']]);
				if($this->Anime->save())	
					$this->stats['Anime_changed']++;
			}

			// Fetch anime rating PG-13 etc..
			$classification = array(
				'G - All Ages' => 'G',
				'PG - Children' => 'PG',
				'PG-13 - Teens 13 or older' => 'PG-13',
				'R - 17+ (violence & profanity)' => 'R',
				'R+ - Mild Nudity' => 'R+',
				'Rx - Hentai' => 'Rx'
				);
			if(($dbAnime['Anime']['classification'] == NULL || $dbAnime['Anime']['classification'] != $anime['classification'])
				&& isset($classification[$anime['classification']]))
			{
				$this->Anime->set('classification',$classification[$anime['classification']]);
				if($this->Anime->save())
					$this->stats['Anime_changed']++;
				if(SCRAPEDEBUG)
					$this->out("\t" . "\t" . 'Changing classification from: "'. $dbAnime['Anime']['classification'] .'" to "'. $classification[$anime['classification']].'"' );
			}
			


			if(SCRAPEDEBUG)
				$this->out("\t" . "\t" . 'Got genres: ' . implode(',',$anime['genres']));
			
			foreach($anime['genres'] as $genre)
			{
				$this->addGenre($item, $genre,'');
			}
			foreach($anime['tags'] as $tag)
			{
				$this->addGenre($item, $tag,'');
			}

			if(SCRAPEDEBUG)
				$this->out("\t" . "\t" . 'Fetching information about relationships');
			foreach($anime['prequels'] as $prequel)
			{
				// Fetch our animeid from mal id
				if(($animeid = $this->getAnimeId($prequel['anime_id'])) == null)
				{
					if(SCRAPEDEBUG)
						$this->out("\t" . "\t" . "\t" . 'The anime "' . $prequel['title'] . '" with myanimelist id "' .$prequel['anime_id']. '" does not exists in the db. Skipping...');
					continue;
				}
				
				$this->addRelationship($item['ScrapeInfo']['anime_id'],'sequel',$animeid);
				
			}
			foreach($anime['sequels'] as $sequel)
			{
				// Fetch our animeid from mal id
				if(($animeid = $this->getAnimeId($sequel['anime_id'])) == null)
				{
					if(SCRAPEDEBUG)
						$this->out("\t" . "\t" . "\t" . 'The anime "' . $sequel['title'] . '" with myanimelist id "' .$sequel['anime_id']. '" does not exists in the db. Skipping...');
					continue;
				}
				
				$this->addRelationship($animeid,'sequel',$item['ScrapeInfo']['anime_id']);

			}
			foreach($anime['side_stories'] as $sideStory)
			{
				// Fetch our animeid from mal id
				if(($animeid = $this->getAnimeId($sideStory['anime_id'])) == null)
				{
					if(SCRAPEDEBUG)
						$this->out("\t" . "\t" . "\t" . 'The anime "' . $sideStory['title'] . '" with myanimelist id "' .$sideStory['anime_id']. '" does not exists in the db. Skipping...');
					continue;
				}
				
				$this->addRelationship($animeid,'side-story',$item['ScrapeInfo']['anime_id']);
			}
			if(($parentStory = $anime['parent_story']) != null)
			{
				// Fetch our animeid from mal id
				if(($animeid = $this->getAnimeId($parentStory['anime_id'])) == null)
				{
					if(SCRAPEDEBUG)
						$this->out("\t" . "\t" . "\t" . 'The anime "' . $parentStory['title'] . '" with myanimelist id "' .$parentStory['anime_id']. '" does not exists in the db. Skipping...');
					continue;
				}
				
				$this->addRelationship($item['ScrapeInfo']['anime_id'],'side-story',$animeid);
			}
			if(SCRAPEDEBUG)
				$this->out("\t" . "\t" . 'Fetching synonyms');
			$languages = array(
				'english' => 'en'
				);
			foreach($anime['other_titles'] as $lang => $synonyms)
			{
				if(array_key_exists($lang,$languages))
					foreach($synonyms as $synonym)
						$this->addSynonym( $item, $synonym, $languages[$lang] );
			}
			$this->addSynonym( $item, $anime['title'] ,'x-jat');
			
			// Fetch chacters from myanimelist
			
		}
		
	}

	function getAnimeId($malId = null)
	{
		if($malId == null)
			return null;

		$result = $this->ScrapeInfo->find('first',array(
			'conditions' => array(
				'scrape_id' => $malId,
				'scrape_source' => 'mal'
			)
		));
		if(SCRAPEDEBUG && count($result['ScrapeInfo']) != 0)
			$this->out("\t" . "\t" . ' MAl-ID ' . $malId . ' equals UrAnimeID '. $result['ScrapeInfo']['anime_id']);
		if(SCRAPEDEBUG && count($result['ScrapeInfo']) == 0)	
			$this->out("\t" . "\t" . ' Could not find anime with MAL-ID ' . $malId) ;
			
		if(count($result['ScrapeInfo']) == 0)
			return null;
		return $result['ScrapeInfo']['anime_id'];

	}

	/***
	 * RETURN TRUE IF ADDED; FALSE OTHERWISE
	 * params
	 * 	anime1 = anime id for anime #1
	 * 	anime2 = anime id for anime #2
	 * 	type = type of relationship between anime1 and anime2
	 *
	 * Eg.
	 * 	animeid 15 is a sequel of animeid 10
	 * 	anime1 = 15, type = sequel, anime2 = 10
	 */
	function addRelationship($anime1 = null, $type = null, $anime2 = null)
	{
		if($anime1 == null || $anime2 == null || $type == null)
			return false;

		$this->out("\t" . "Checking if relationship already exists for anime " . $anime1 . " with type " . $type . " for anime " . $anime2);
		// Check if this relationship already exists;
		//
		$this->AnimeRelationship->recursive = -1;
		$exists = $this->AnimeRelationship->find('all', array(
				'conditions' => array(
					'anime1' => $anime1,
					'anime2' => $anime2,
					'type'	=> $type
				)
			)
		);

		if(count($exists) !== 0)
		{
			if(SCRAPEDEBUG)
				$this->out("\t" ."\t" . "\t" . 'Anime "'.$anime1.'" is already linked up with "'.$anime2.'" as "'.$type.'". Nothing done' );
			return false;
		}
		
		$this->AnimeRelationship->create();
		$this->AnimeRelationship->set('anime1',$anime1);
		$this->AnimeRelationship->set('type', $type);
		$this->AnimeRelationship->set('anime2',$anime2);
		$this->AnimeRelationship->save();
		$this->stats['Relationship_added']++;
		if(SCRAPEDEBUG)
			$this->out("\t" ."\t" . "\t" . 'Anime "'.$anime1.'" is now linked up with "'.$anime2.'" as "'.$type.'"' );

		return true;

	}

	private function addAnimeByMALid($id)
	{
		// Check if the anime already exists in the db
		
	}

	function in_array_r($needle, $keyinput, $haystack) {
		foreach ($haystack as $key => $item) {
			if ($item === $needle && $key==$keyinput || (is_array($item) && $this->in_array_r($needle, $keyinput, $item))) {
			return true;
			}
		}

		return false;
	}
	
	function addGenre($scrapeInfo, $genre, $description)
	{
		$genre = Sanitize::clean(trim($genre));
		$description = Sanitize::clean(trim($description));

		// Check if the genre is in the database.
		$this->Genre->recursive = -1;
		$ant = $this->Genre->find('first',array('conditions' => array('LOWER(name)' => strtolower($genre))));

		$genreid = $ant['Genre']['id'];
		
		if($ant == NULL){
			
			$this->Genre->create();
			$this->Genre->set('name',$genre);
			$this->Genre->set('description',$description);
			if($this->Genre->save())
			{
				$genreid = $this->Genre->getInsertID();
				$this->stats['Genre_added']++;
				if(SCRAPEDEBUG)
					$this->out("\t" . "\t" . 'Genre \''.$genre.'\' is added to the db....');
			}
			else{
				if(SCRAPEDEBUG)
					$this->out("\t" . "\t" . 'Could not add genre \''.$genre.'\' to the db....');
				return;
			}

		}
		else{
			$genreid = $ant['Genre']['id'];
		}
		
		if($ant['Genre']['description'] == '' && $description != '')
		{
			$this->Genre->read(NULL,$genreid);
			$this->Genre->set('description',$description);
			if($this->Genre->save()){
				$this->stats['Genre_changed']++;
				if(SCRAPEDEBUG)
					$this->out("\t" . "\t" . 'Added description to genre \''.$genre.'\'....');
			}
		}
		
		$anime_database = $this->Anime->read(NULL, $scrapeInfo['Anime']['id']);
		
		// Check if the genre is already connected to the anime
		if($this->in_array_r($genreid,'genre_id',$anime_database['AnimeGenre'])){
			if(SCRAPEDEBUG)
				$this->out("\t" . "\t" . 'Genre \''.$genre.'\' is already connected to anime in db. Skipping...');
			return;
		}
		//echo $genreid;
		//echo $scrapeInfo['Anime']['id'];
		// CHECK IF ANIME EXISTS!!
		// Connect the genre to the anime
		$tjos->AnimeGenre->recursive = -1;
		$this->AnimeGenre->create();
		$this->AnimeGenre->set('anime_id',$scrapeInfo['Anime']['id']);
		$this->AnimeGenre->set('genre_id',$genreid);
		$this->AnimeGenre->save();
		$this->stats['Genre_linked']++;
		if(SCRAPEDEBUG)
			$this->out("\t" . "\t" . 'Genre "'.$genre.'" is now linked up to the anime....');
		return;
	}

	function addSynonym($scrapeInfo, $synonym, $lang){
		$synonym = trim($synonym);
		//$synonyms = $this->AnimeSynonym->findAllByAnime_id($scrapeInfo['Anime']['id']);
		$this->AnimeSynonym->recursive = -1;
		$ant = $this->AnimeSynonym->find('count',array('conditions' => array('LOWER(AnimeSynonym.title)' => strtolower($synonym),'AnimeSynonym.anime_id' => $scrapeInfo['Anime']['id'])));
		if($ant != 0 || strtolower(trim($scrapeInfo['Anime']['title']) == strtolower($synonym)))
		{
			if(SCRAPEDEBUG)
				$this->out("\t" . "\t" . 'Synonym "'.$synonym.'" is already linked to the anime.... Nothing done');
			return;
		} 

		// Create the synonym and link it to the anime
		$this->AnimeSynonym->create();

		$this->AnimeSynonym->set('anime_id', $scrapeInfo['Anime']['id']);
		$this->AnimeSynonym->set('title', $synonym);
		$this->AnimeSynonym->set('lang', $lang);

		if($this->AnimeSynonym->save()){
			$this->stats['Synonym_added']++;
			if(SCRAPEDEBUG)
				$this->out("\t" . "\t" . 'Synonym "'.$synonym.'" are created and linked up to the anime.');
		}
	}
	
	function anidbScrape($item){
		
		// we try not to use the session client
		//$anidbSession = new anidb_Session($username, $password, $nat);
		//$port = 9001;
		$animeid = $item['ScrapeInfo']['scrape_id'];
		//$anidbURL = "http://api.anidb.net/httpapi?client=".CLIENTNAME."&clientver=".CLIENTVERSION."&protover=1&request=anime&aid=".$animeid;

		// Temporary server
		$anidbURL = ANIDB_APIPATH."?aid=".$animeid."&client=".CLIENTNAME."&version=".CLIENTVERSION;
		$port = 80;
		$sleepTime = 3;

		$crl = curl_init();
		$timeout = 20;
		curl_setopt($crl, CURLOPT_URL,$anidbURL);
		curl_setopt($crl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($crl, CURLOPT_ENCODING,'gzip');
		curl_setopt($crl, CURLOPT_HEADER,0);
		curl_setopt($crl, CURLOPT_FRESH_CONNECT, 1);
		curl_setopt($crl, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($crl, CURLOPT_PORT, $port);


		$response = curl_exec($crl) or die(curl_error());
		//$response = file_get_contents($anidbURL);
		$anime = new SimpleXMLElement($response);
		curl_close($crl);
		
		// CHECK WHAT WE WANT TO FETCH
		//$this->out($anidbURL);

		// Define what kind of language we are fetching synonyms for
		$language = array(
				'en' => 'en',
				'x-jat' => 'x-jat'
			);

		if($item['ScrapeInfo']['fetch_information'] == '1')
		{
			if(SCRAPEDEBUG)
				$this->out("\t".'AnidbScraper is set to scrape genres');
			//if($anime->count() !== 0){
			if(count($anime->children()) !== 0){
				foreach($anime->categories->category as $category)
					$this->addGenre($item, (string)$category->name, (string)$category->description);
				foreach($anime->tags->tag as $tag)
					$this->addGenre($item, (string)$tag->name, (string)$tag->description);
			}
			else
				if(SCRAPEDEBUG)
					$this->out("\t". 'Xml size is 0.. Sorry');

			if(SCRAPEDEBUG)
				$this->out("\t".'AnidbScraper is set to scrape synonyms');
			//if($anime->count() !== 0)
			if(count($anime->children()) !== 0)
			{
				foreach($language as $key => $value)
					foreach ($anime->titles->xpath('title[@xml:lang="'.$key.'"]') as $title)
				  		$this->addSynonym($item, (string)$title, $value);
			}
			else
				if(SCRAPEDEBUG)
					$this->out("\t". 'Xml size is 0.. Sorry');
			if(SCRAPEDEBUG)
				$this->out("\t"."Fetching type from anidb");
			
			$types = array(
				'TV Series' => 'tv',
				'OVA' => 'ova',
				'Movie' => 'movie',
				'TV Special' => 'special',
				'Other' => 'other'
			);
			$newType = (String)$anime->type;
			
			// Get anime type again in case the type has been set earlier in 
			
			if(($dbAnime['Anime']['type'] == '' || $dbAnime['Anime']['type'] == null) && !empty($newType))
			{
				if(SCRAPEDEBUG)
					$this->out("\t" ."\t" . "\t" . 'Adding new type to anime:' . $dbAnime['Anime']['title'] .'.'. ' Type:' . $dbAnime['Anime']['type'] . '->' . $types[$newType]);
				$this->Anime->set('type',$types[$newType]);
				if($this->Anime->save())	
					$this->stats['Anime_changed']++;
			}
		}

		if($item['ScrapeInfo']['fetch_episodes'] == '1')
		{
			// Ep type 1 = Regular Episode
			// Ep type 4 = Trailer/Promo/Ads
			if(SCRAPEDEBUG)
				$this->out("\t".'AnidbScraper is set to scrape episodes');
			// Parse through the episodes
			//if($anime->count() !== 0)
			if(count($anime->children()) !== 0) 
			foreach($anime->episodes->episode as $episode)
			{
				$special = NULL;
				//print_r($episode);
				// Check if right episode type
				if($item['ScrapeInfo']['fetch_specials'] == '1' && (int)$episode->epno['type'] == 2) // 2 means special
					$special = 1;
				else if((int)$episode->epno['type'] != 1) // 1 is a regular episode
					continue;

				// Fetch the episode name via namespace

				// Add the episode
				$path = $episode->xpath("title[@xml:lang='en']");
				$name = '';

				while(list( ,$node) = each($path)){
					$name = $node;
				}


				//print_r($path);
				//print_r($episode);
				//$this->out($item['Anime']['id'].' '.$episode->epno.' '. $episode->airdate . ' ' . $name. ' ' . $special);
				$this->addEpisode( $item, $item['Anime']['id'], (string)$episode->epno, (string)$episode->airdate, $name,'', $special);
				
			}
			else
				if(SCRAPEDEBUG)
					$this->out("\t".'Xml size is 0.. Sorry');
			
			if(SCRAPEDEBUG)
				$this->out("\t".'AnidbScraper is set to fetch episode runtime');
			foreach($anime->episodes->episode as $episode)
			{
				// Check if right episode type
				if((int)$episode->epno['type'] != 1)
					continue;
				// Always prefer information from anidb..
				if($item['Anime']['runtime'] != $episode->length)
				{
					$this->Anime->read(NULL, $item['ScrapeInfo']['anime_id']);
					$this->Anime->set('runtime',$episode->length);
					if($this->Anime->save())
					{
						if(SCRAPEDEBUG)
							$this->out("\t" . "\t" . 'Anime "'.$item['Anime']['title'].'" now have runtime "'.$episode->length.'".');
					}
				}
				break;
			}
			
		}
		
		//print_r($response);
	}
	
	function themoviedbScrape($item){
		// Should only be used when anime is a movie
		if($item['Anime']['type'] != 'movie' && $item['Anime']['type'] != null && $item['Anime']['type'] != ""){
			if(SCRAPEDEBUG)
					$this->out("\t".'Not classified as a movie. Quitting');
			return false;
		}
			
		App::import('Vendor','Themoviedb', array('file' => 'class.themoviedb.php'));
		$moviedb = new TMDBv3('ab6e5238babff326babdd34cc8060d4b');
		
		if($item['ScrapeInfo']['fetch_episodes'] == '1')
		{
			
			// Check if the only scraper used for episodes is themoviedb
			// If it is, it should remove all unessesary episodes
			$countScrapers = $this->ScrapeInfo->find('count',array(
				'conditions' => array(
						'anime_id' => $item['ScrapeInfo']['anime_id'],
						'fetch_episodes' => '1'
					)
				)
			);
				
			if($countScrapers > 1){
				return;
			}
			
			// Here we set movie runtime and insert the move as 1 episode.
			$animeinfo = $moviedb->movieDetail($item['ScrapeInfo']['scrape_id']);
			$dbAnime = $this->Anime->read(NULL, $item['ScrapeInfo']['anime_id']);
			
			if($dbAnime['Anime']['runtime'] != $animeinfo['runtime'])
			{
				if(SCRAPEDEBUG)
					$this->out("\t".'Changing anime movie runtime:"'.$dbAnime['Anime']['runtime'] . '" => "' . $animeinfo['runtime'] . '"');
				$this->Anime->set('runtime',$animeinfo['runtime']);
				$this->Anime->save();
			}
			
			// Check how many episodes exists for anime movie
			$this->Episode->recursive = -1;
			$epCount = $this->Episode->find('all',array('conditions' => array(
						'anime_id' => $dbAnime['Anime']['id']
					)
				)
			);
			
			if(count($epCount) != 1)
			{
				if(SCRAPEDEBUG)
					$this->out("\t". 'Deleting '.count($epCount).' episodes from anime');
				// remove all other episodes
				$this->Episode->deleteAll(array('Episode.anime_id' => $dbAnime['Anime']['id']),false);
				
				
				foreach($epCount as $tmpEpisode)
					$this->UserEpisode->deleteAll(array('UserEpisode.episode_id' => $tmpEpisode['Episode']['id']),false);
				if(SCRAPEDEBUG)
					$this->out("\t". 'Inserting the movie as one episodes');
				$this->addEpisode($item, $dbAnime['Anime']['id'],1,
									$animeinfo['release_date'],$dbAnime['Anime']['title']);
									
			}
			
			
			
		}
		if($item['ScrapeInfo']['fetch_information'] == '1')
		{
			if(SCRAPEDEBUG)
					$this->out("\t". '[Themoviedb] is not programmed to fetch information yet.');
		}
	}
	
	function thetvdbScrape($item){
		App::import('Vendor','Thetvdb', array('file' => 'class.thetvdb.php'));
		$tvdbapi = new Thetvdb('992BDB755BA8805D');

		// THIS IS THE NEW METHOD
		/**
		 * YOU SHOULD USE THE scrape_season field INSTEAD OF scrape_episodes
		 * Should be written as:
		 * 1 - 3, 5	 = fetches episodes from season 1, 2, 3, and 5
		 */
		
		    //   $episode['id'] = (int) $ep->id;
            //   $episode['season'] = (int) $ep->SeasonNumber;
            //   $episode['episode'] = (int) $ep->EpisodeNumber;
            //   $episode['airdate'] = (string) $ep->FirstAired;
            //   $episode['name'] = (string) $ep->EpisodeName;
            //   $episode['description'] = (string) $ep->Overview;
            //   $episode['absolute'] = (int) $ep->absolute_number;
            //   $episode['airsafter_season'] = (int) $ep->airsafter_season;
            //   $episode['airsbefore_season'] = (int) $ep->airsbefore_season;
            //   $episode['airsbefore_episode'] = (int) $ep->airsbefore_episode;

		$episodesInfo = ($item['ScrapeInfo']['scrape_episodes'] == NULL) ? "NULL": trim($item['ScrapeInfo']['scrape_episodes']);
		$seasonsInfo = ($item['ScrapeInfo']['scrape_seasons'] == NULL) ? "NULL": trim($item['ScrapeInfo']['scrape_seasons']);
		if(SCRAPEDEBUG)
			$this->out("\t".'Season information is set to:"'.$seasonsInfo . '" and episode info: "' . $episodeInfo.'"');
		
		// Fetching all the episodes
		if(SCRAPEDEBUG)
			$this->out("\t".'Fetching all episodes for series');
		$serie_info = $tvdbapi->GetSerieData($item['ScrapeInfo']['scrape_id'],true);
		

		// Start with the legacy code in the transission phase
		if($episodesInfo != null){
			$this->legacyThetvdb($item, $episodesInfo, $serie_info);
		}
		else //if($seasonsInfo != null){
			$this->fetchSeasonThetvdb($item,$seasonsInfo, $serie_info);
		return;
		//}
	}

	/**
	 * Fetches specified seasons from thetvdb and inserts the episodes in the db
	 */
	private function fetchSeasonThetvdb($item, $seasonsInfo, $serie_info){
		$info = $item['ScrapeInfo'];
		// Check if special flag is on
		$special = ($info['fetch_specials'] == 1);
		
		$this->buggy("Scraper is set to fetch specials.",1);


	}

	private function buggy($text = null, $indent = 0)
	{
		for($i = 0; $i < $indent; $i++)
			$text = "\t" . $text;
		if(SCRAPEDEBUG && $text != null)
			$this->out($text);
	}

	private function legacyThetvdb($item, $episodesInfo, $serie_info){

		// THIS IS DEPRECATED!
		// Fetch the episodes for the given series. It should allways be absolute_number. 
		/**
		 * The absolute numbering: 0 is always specials.
		 * 1 		= fetching episode 1
		 * 1 - 3 	= fetching episodes 1, 2, and 3
		 * 1 - 		= fetching all episodes from episode 1
		 * - 3 		= fetching episodes 1, 2, and 3
		 * NULL 	= fetching all seasons
		 *
		 * For specials you can use
		 * S1 - 3 will retrieve special episodes 1-3
		 * S1 - S3 gives the same result as the one above.
		 * 1 - S3 gives the same as the two previous
		 */
		$episodes = array();
		$start = 0;
		$end = count($serie_info['episodes']);
		$special_flag = false;
		// Filter out the ones we do not need
		if(strpos($episodesInfo,'-') !== FALSE)
		{
			$exploded = explode('-', $episodesInfo);
			if(!empty($exploded[0]))
				$start = strtolower($exploded[0]);
			if(!empty($exploded[1]))
				$end = strtolower($exploded[1]);
			
			// Check if the scraper should retrieve regular or special episodes
			if( ( strpos( $start,'s' ) !== FALSE ) ||
				( strpos( $end,'s' ) !== FALSE ) )
			{
				// the series is defined as special episodes at thetvdb
				if(SCRAPEDEBUG)
					$this->out("\t".'The anime is defined as special episodes at thetvdb');
				$special_flag = true;
				$start = str_replace('s','',$start);
				$end = str_replace('s','',$end);
			}else
				if(SCRAPEDEBUG)
					$this->out("\t".'The anime is defined as ordinary episodes at thetvdb');
		}
		
		
		/** DATE STUFF THAT IS NOT USED! **/
		$latest_date = null;
		$beginning_date = null;
		
		
		foreach($serie_info['episodes'] as $episode)
		{
			if((int)$episode['absolute'] == $start)
				$beginning_date = $episode['airdate'];
			if((int)$episode['absolute'] == $end)
				$latest_date = $episode['airdate'];
		}
		
		/** END OF DATA STUFF */

		foreach($serie_info['episodes'] as $episode)
		{
			$num = (int)$episode['absolute'];
			$aired_number = (int)$episode['episode']; // This is used in special episodes
			
			
			
			if($special_flag && $episode['season'] == 0)
			{
				 if($aired_number !== 0 && ($aired_number > $end || $aired_number < $start))
					continue;
				 // To make stuff simpler for now.. In episodes from thetvdb we skip the specials ;)
				if($start == 0)
					$episodeNumber = $aired_number;
				else
					$episodeNumber = ($aired_number - $start+1);
				
				$this->addEpisode( $item, $item['Anime']['id'], $episodeNumber, $episode['airdate'], $episode['name'], $episode['description'], NULL );	
				continue;
			}
			
			if($special_flag && $episode['season'] != 0)
				continue;
			
			// The special flag is not set and only regular episodes should be considered.
			if($num !== 0 && ($num > $end || $num < $start))
				continue;
				
			// if the episode is a special and no special flag is set.
			if((int)$episode['season'] == 0 || $episode['season'] == '' || $episode['season'] == NULL)
			{	
				if(SCRAPEDEBUG)
					$this->out("\t" . "\t" . 'The episode is a special; endDate0:' . $latest_date . '; beginDate:'.$beginning_date);
				/** DATE STUFF THAT IS NOT USED! **/
				
				if($latest_date != null && strtotime($episode['airdate']) > strtotime($latest_date))
				{
					if(SCRAPEDEBUG)
						$this->out("\t"."\t".'Skipping Ep:\'' . $episode['absolute'] . '\': \'' . $episode['name'].'\'. The airdate is after the last episode');
					continue;
				}
				if($start != 0)	
					if($beginning_date != null && strtotime($episode['airdate']) < strtotime($beginning_date))
					{
						if(SCRAPEDEBUG)
							$this->out("\t"."\t".'Skipping Ep:\'' . $episode['absolute'] . '\': \'' . $episode['name'].'\'. The airdate is before the first episode');
						continue;
					}
					/** END OF DATA STUFF */
			}
			
			// Add episode to db
			$special = $episode['season'] == 0 ? 1 :  NULL;
			
			// To make stuff simpler for now.. In episodes from thetvdb we skip the specials ;)
			if($start == 0)
				$episodeNumber = ((int)$episode['absolute']);
			else
				$episodeNumber = ((int)$episode['absolute'] - $start+1);
			
			return; /* TODO: REMOVE THIS*/
			if($special == NULL && $item['ScrapeInfo']['fetch_episodes'] == '1')
				$this->addEpisode( $item, $item['Anime']['id'], $episodeNumber, $episode['airdate'], $episode['name'], $episode['description'], $special );

		}
	}

	function addEpisode($scrapeInfo, $animeid, $number, $aired, $name, $description = '' , $special = NULL)
	{
		// We do not add episodes to items classified as movies
		/*if($scrapeInfo['Anime']['type'] == 'movie')
		{
			if(SCRAPEDEBUG)
				$this->out("\t"."\t".'Episode :\'' . $number . '\': \'' . $name.'\' was not added because the anime type is movie');
			return false;
		}*/
		
		if($number < 0){
			if(SCRAPEDEBUG)
				$this->out("\t"."\t".'Episode :\'' . $number . '\': \'' . $name.'\' was not added because the episode number is negative');
			return false;
		}
			
		// We do not add episodes with no name from scraper.
		if(strlen($name) == 0)
		{
			if(SCRAPEDEBUG)
				$this->out("\t"."\t".'Episode :\'' . $number . '\': \'' . $name.'\' was not added because it has no name');
			return false;
		}
		if($special == NULL && (int)$number == 0)
		{
			if(SCRAPEDEBUG)
				$this->out("\t"."\t".'Episode :\'' . $number . '\': \'' . $name.'\' was not added because it has no number');
			return false;
		}
		if((string)$aired == '')
		{
			if(SCRAPEDEBUG)
				$this->out("\t"."\t".'Episode :\'' . $number . '\': \'' . $name.'\' was not added because it has no date');
			return false;
		}
		
		// Check if the exact episodes exists in the database
		$exact = $this->Episode->find('first',array('conditions' => array(
			'anime_id' 	=> $scrapeInfo['Anime']['id'],
			'special' 	=> $special,
			'number'	=> $number
		)));

		if(count($exact['Episode']) != 0){
			
			$added = false;

			// Allways prefer thetvdb episode descriptions.
			if((strlen($exact['Episode']['description']) == 0 && strlen($description) != 0) 
				|| ($scrapeInfo['ScrapeInfo']['scrape_source'] == 'thetvdb' && $description != $exact['Episode']['description'])){
				$this->Episode->read(NULL,$exact['Episode']['id']);
				$this->Episode->set('description',$description);
				$this->Episode->save();
				$this->stats['Episodes_changed']++;
				if(SCRAPEDEBUG)
					$this->out("\t"."\t".'Added missing description to episode :\'' . $number . '\': \'' . $name.'\' ..');
				$added = true;
			}

			// Sometimes anidb labels episodes with :Episode ## when it does not have a name.
			if((strpos(strtolower($exact['Episode']['name']),'episode') !== FALSE 
				&& strlen($exact['Episode']['name']) < 25 
				&& $exact['Episode']['name'] != $name) || ($exact['Episode']['name'] != $name && $scrapeInfo['ScrapeInfo']['scrape_source'] == 'anidb')){
				// Use the new name instead of old one.
				$this->Episode->read(NULL,$exact['Episode']['id']);
				$this->Episode->set('name',$name);
				$this->Episode->save();
				$this->stats['Episodes_changed']++;
				if(SCRAPEDEBUG)
					$this->out("\t"."\t".'Added missing name to episode :\'' . $number . '\': \'' . $name.'\' ..');
				$added = true;
			}

			// Check if date is different... Prefer dates from anidb over thetvdb
			if($aired != $exact['Episode']['aired'] && $scrapeInfo['ScrapeInfo']['scrape_source'] == 'anidb'){
				$this->Episode->read(NULL,$exact['Episode']['id']);
				$this->Episode->set('aired',$aired);
				$this->Episode->save();
				$this->stats['Episodes_changed']++;
				if(SCRAPEDEBUG)
					$this->out("\t"."\t".'Changed aired date on episode [anidb] :\'' . $number . '\': \'' . $name.'\' ..');
				$added = true;
			}
			
			if($added)
				return false;
			if(SCRAPEDEBUG)
				$this->out("\t"."\t".'Episode :\'' . $number . '\': \'' . $name.'\' already exists..');
			return false;
		}

		// Create the episode
		$this->Episode->create();
		$this->Episode->set('anime_id', $animeid);
		$this->Episode->set('special', $special);
		$this->Episode->set('number', $number);
		$this->Episode->set('name', $name);
		$this->Episode->set('aired', $aired);
		$this->Episode->set('description', $description);
		$this->stats['Episodes_added']++;
		$this->Episode->save();
		if(SCRAPEDEBUG)
			$this->out("\t"."\t".'Added :\'' . $number . '\': \'' . $name.'\'');
	}

	

}
?>
