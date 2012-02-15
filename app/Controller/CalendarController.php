<?php
class CalendarController extends AppController {
	var $helpers = array('Text','Time');
	var $components = array('RequestHandler','Auth');
	var $uses = array('Anime','Episode','User','ScrapeInfo','UserEpisode');
	

	
	function webcal($userid = null){
		if($userid == null)
			die();
		Configure::Write('debug',0);
		echo header( 'Content-Type: text/calendar; charset=utf-8' );  
		
		$this->layout = 'blank';
	}

	function view($param = "own") {
		$uid = $this->Auth->User('id');

		if($uid == NULL)
		{
			$this->redirect($this->referer());
			return;
		}

		if($param == "own"){
		$this->set('own',true);
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
					'Episode.aired BETWEEN CURDATE() AND DATE_ADD(NOW(),INTERVAL 7 DAY)'
				)
			));

			$userEpisodes = $this->UserEpisode->find('all', array(
				'order' => 'aired ASC',
				'fields' => 'DISTINCT Episode.anime_id',
				'conditions' => array(
					'user_id' => $uid
				)
			));
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
				if(!$found)
					unset($episodes[$i]);
				$i++;
			}

		}
		else if($param == "all")
			$episodes = $this->Episode->find('all', array(
				'order' => 'aired ASC',
				'conditions' => array(
					'Episode.aired BETWEEN CURDATE() AND DATE_ADD(NOW(),INTERVAL 7 DAY)'
				)
			));
		
		$this->set('episodes',$episodes);
		// Temporary show the cron queue.
		//$this->set('queue',$this->ScrapeInfo->find('all'));
		//$this->render('/pages/home');
	}
	
}
?>
