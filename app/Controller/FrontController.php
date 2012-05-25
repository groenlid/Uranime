<?php
class FrontController extends AppController {
	var $helpers = array('Text','Time');
	var $uses = array('Anime','Episode','UserEpisode','UserWatchlist');
	
	function index() {
		$this->set('page','home');
		
		if($this->Auth->User('id') != NULL){
			$uid = $this->Auth->User('id');
			$this->Episode->recursive = 0;
			/*$episodes = $this->Episode->find('all', array(
				'order' => 'aired ASC',
				'conditions' => array(
					'Episode.aired BETWEEN CURDATE() AND DATE_ADD(NOW(),INTERVAL 7 DAY) AND anime_id IN (
						SELECT anime_id FROM episodes WHERE id IN (
							SELECT episode_id FROM user_episodes WHERE user_id='.$uid.'
							)
						)'
				)
			));*/

			$episodes = $this->Episode->find('all', array(
				'order' => 'aired ASC',
				'conditions' => array(
					'Episode.aired = CURDATE()'
				)
			));

			$userEpisodes = $this->UserEpisode->find('all', array(
				'order' => 'aired ASC',
				'fields' => 'DISTINCT Episode.anime_id',
				'conditions' => array(
					'user_id' => $uid
				)
			));
			$this->UserWatchlist->recursive = -1;
			$userWatchList = $this->UserWatchlist->find('all', array(
				'conditions' => array(
					'user_id' => $uid
					)
				)
			);
			$i = 0;
			//debug($episodes);
			foreach($episodes as $episode)
			{
				$found = false;
				foreach($userEpisodes as $userEpisode)
					if($userEpisode['Episode']['anime_id'] == $episode['Episode']['anime_id']){
						$found = true;
						break;
					}
				foreach($userWatchList as $watchItem)
					if($watchItem['UserWatchlist']['anime_id'] == $episode['Episode']['anime_id']){
						$found = true;
						break;
					}
				if(!$found)
					unset($episodes[$i]);
				$i++;
			}
			$this->set('episodes',$episodes);
		}
		/*$episodes = $this->Episode->find('all', array(
			'order' => 'aired ASC',
			'conditions' => array(
				'Episode.aired BETWEEN CURDATE() AND DATE_ADD(NOW(),INTERVAL 7 DAY)'
			)
		));*/

		//$this->set('episodes',$episodes);
		// Temporary show the cron queue.
		//$this->set('queue',$this->ScrapeInfo->find('all'));
		//$this->render('/pages/home');

		//$this->set('activities',$this->Activity->find('all'));
	}
	
	function searchlimit() {
		

		App::import('TextHelper','Helper'); 
		//debug($this);
		//$this->Text = new TextHelper();
		$search = $this->data['search'];//$_POST['search'];
		//debug($this->Text);
		die();
		$this->Anime->recursive = -1;
		$animes = $this->Anime->find('all', 
			array(
				'conditions' 	=> array('title LIKE' => "%$search%"),
				'table' => array('anime'),
				'fields' => array('title','id','image'),
				'limit' => 5
				)
			);

		echo '<div class="arrow"></div>';
		echo '<div class="inner">';
		echo '<h3 class="title">Results</h3>';
		echo '<div class="content">';
		if(count($animes) == 0)
		{
			echo "No results matches your criteria";
			die();
			return;
		}
		
	
		
		foreach($animes as $key => $anime)
		{
			/*echo '<div class="resultitem"><img src="'.$anime['Anime']['image'].'"><p><a href="/anime/view/'.$anime['Anime']['id'].'/'.$anime['Anime']['title'].'">'.$this->Text->highlight($this->Text->truncate($anime['Anime']['title'],50),$search).'</a></p></div>';*/
			echo '<div class="resultitem"><p><a href="/anime/view/'.$anime['Anime']['id'].'/'.$anime['Anime']['title'].'">'.$this->Text->highlight($this->Text->truncate($anime['Anime']['title'],50),$search).'</a></p></div>';
		}
		echo '</div>';
		echo '</div>';
		die();
	}
}
?>
