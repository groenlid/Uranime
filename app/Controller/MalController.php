<?php
class MalController extends AppController {
	var $uses = array('User','Anime','Episode','Activity','AnimelistEntry','ScrapeInfo','AnimeGenre', 'AnimeRelationship','UserEpisode','AnimeRating','AnimeSynonym');
	
	public function import($username = ""){
		if(trim($username) == "")
			return;
		App::uses('Sanitize', 'Utility');
		$username = Sanitize::clean($username, array('encode' => false));
		
		$animelistUrl ="http://myanimelist.net/malappinfo.php?u=" . $username;

		$animelist = new SimpleXMLElement(file_get_contents($animelistUrl),true);
		$pretty_list = array();
		foreach($animelist->anime as $anime)
		{
			$mal_id = (int)$anime->series_animedb_id;
			$title = (String)$anime->series_title;
			$localAnime = $this->ScrapeInfo->find(
				'first',array(
					'conditions' => array(
						'scrape_source' => 'mal',
						'scrape_id' => $mal_id
						)
					)
				);
			$pretty_list[$title . $mal_id] = array(
				'id' => $mal_id, 
				'title' => $title, 
				'scrape_info' => $localAnime, 
				'epseen' => (int)$anime->my_watched_episodes,
				'eptotal' => (int)$anime->series_episodes,
				'score' => (int)$anime->my_score
			);
		}

		ksort($pretty_list);
		$this->set('animelist',$pretty_list);
		//debug($pretty_list);
	}

	public function checkUser(){
		if($this->data == null || !isset($this->data['Myanimelist_Username']) || $this->data['Myanimelist_Username'] == '' )
			$this->redirect($this->referer());
		App::uses('Sanitize', 'Utility');
		$this->request->data = Sanitize::clean($this->request->data, array('encode' => false));
		//debug($this->data);
		$uname = trim($this->data['Myanimelist_Username']);
		if($uname != "")
			$this->redirect("/mal/import/".$uname);
	}

}
?>