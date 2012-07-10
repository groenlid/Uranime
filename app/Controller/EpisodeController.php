<?php
class EpisodeController extends AppController {
	var $helpers = array('Text','Gravatar');
	var $uses = array('User','Anime','Episode','Activity','AnimelistEntry','ScrapeInfo','AnimeGenre', 'AnimeRelationship','UserEpisode');

	function watch($id = null){
		$this->watchEpisode($id);
		$this->redirect($this->referer());
	}

	private function watchEpisode($id = null, $bulk = false){
		if($id == null || !is_numeric($id)){
			$this->Session->setFlash('Wrong episode number.','flash_error');
			return false;
		}

		if($this->Auth->User('id') == NULL)
		{
			$this->Session->setFlash('You are not logged in.','flash_error');
			return false;
		}	

		$count = $this->UserEpisode->find('count',array(
			'conditions' => array(
					'user_id' => $this->Auth->User('id'),
					'episode_id' => $id
				)
			));
		if($count != 0)
		{
				$this->Session->setFlash('You have already seen this episode.','flash_warning');
				return false;
		}
		else {
			// Check if this is a real episode
			$episode = $this->Episode->read(null,$id);
			if($episode == null)
				return false;

			$this->UserEpisode->create();
			$this->UserEpisode->set('user_id',$this->Auth->User('id'));
			$this->UserEpisode->set('episode_id',$id);
			$this->UserEpisode->set('timestamp',DboSource::expression('NOW()'));
			if($this->UserEpisode->save())
			{
				$this->Session->setFlash('<strong>Watched</strong> status is saved','flash_success');
								// ADDING ACTIVITY
				//$this->Episode->id = $id;
				$this->Episode->read(null, $id);
				if(!$bulk)
					$this->requestAction('/anime/setEpisodeScrape/'.$this->Episode->data['Anime']['id']);
				return true;
			}
			else{
				$this->Session->setFlash('Could not save watched status for episode.','flash_error');
				return false;
			}
		}
	}

	/**
	 * This is to be used on the mal import page
	 */
	function watchEpisodeFromTo($animeId = null,$from = null, $to = null)
	{
		if(!is_numeric($animeId) || !is_numeric($from) || !is_numeric($to))
			die();

		$anime = $this->Anime->find('first', array('conditions' => array('id' => $animeId)));
		if($anime == null)
			die();
		
		$added = 0;
		$rejected = 0;

		foreach($anime['Episode'] as $episode)
        {
            // Skip episode if it is a special.
            if($episode['special'] != null)
                continue;
			if($episode['number'] < $from || $episode['number'] > $to)
				continue;
			if($this->watchEpisode($episode['id'],true))
				$added++;
			else
				$rejected++;
			//if($)
		}
		if($added != 0)
			echo "<span class='label label-success'>".$added." added</span> ";
		if($rejected != 0)
			echo "<span class='label label-important'>".$rejected." rejected</span> ";
		if($rejected == 0 && $added == 0)
			echo "<span class='label label-warning'>Noting done</span> ";
		die();
	}

	function watchall($animeId = null){
		if($animeId == null || !is_numeric($animeId)){
			$this->Session->setFlash('Wrong anime id.','flash_error');
			$this->redirect($this->referer());
		}

		if($this->Auth->User('id') == NULL)
		{
			$this->Session->setFlash('You are not logged in.','flash_error');
			$this->redirect($this->referer());
		}
		$this->Episodes->recursive = -1;
		$episodes = $this->Episode->find('all',array(
			'conditions' => array(
					'anime_id' => $animeId
				)
			));

		foreach($episodes as $episode)
			if(strtotime($episode['Episode']['aired']) < time())
				$this->watchEpisode($episode['Episode']['id'], true);


		$this->requestAction('/anime/setEpisodeScrape/'.$animeId);
		$this->redirect($this->referer());
	}

	function unwatch($id = null){
		if($id == null || !is_numeric($id)){
			$this->Session->setFlash('Wrong episode number.','flash_error');
			$this->redirect($this->referer());
		}

		if($this->Auth->User('id') == NULL)
		{
			$this->Session->setFlash('You are not logged in.','flash_error');
			$this->redirect($this->referer());
		}	
		$count = $this->UserEpisode->find('first',array(
			'conditions' => array(
					'user_id' => $this->Auth->User('id'),
					'episode_id' => $id
				)
			));

		if(count($count) == 0)
		{
			$this->Session->setFlash('You have\'nt seen this episode yet.','flash_warning');
			$this->redirect($this->referer());
		}
		if($this->UserEpisode->delete($count['UserEpisode']['id'], false))
		{
			$this->Session->setFlash('Status has been updated','flash_success');
		}
		else{
			$this->Session->setFlash('Could not undo watched status','flash_error');
			
		}
		$this->redirect($this->referer());

		die();
	}

	function view($id = null){
		if($id == null || !is_numeric($id))
		{
			$this->Session->setFlash('Invalid input numner','flash_error');
			$this->redirect($this->referer());
		}
		$this->Episode->recursive = 2;
		$episode = $this->Episode->findById($id);

		if($episode == null)
		{
			$this->Session->setFlash('Invalid input numner','flash_error');
			$this->redirect($this->referer());
		}
		//debug($episode);
		$neighbors = $this->Episode->find('neighbors',array(
			'field' => 'number',
			'value' => $episode['Episode']['number'],
			'conditions' => array(
				'anime_id' => $episode['Episode']['anime_id'],
				'special' => $episode['Episode']['special']
				)
			)
		);
		
		$this->set('episode',$episode);
		$this->set('neighbors',$neighbors);

		//$this->set('userepisode',false);
		$userepisode = null;
		
		if($this->Auth->User('id') != null)
		{
			$userepisode = $this->UserEpisode->find('first',array(
				'conditions' => array(
						'user_id' => $this->Auth->User('id'),
						'episode_id' => $id
					)
			));	
		}
		$this->set('userepisode',$userepisode);

	}

	function delete($id = null){
		if($id == null || !is_numeric($id) || $this->Auth->User('id') == NULL|| $this->Auth->User('id') != 1)
		{
			$this->Session->setFlash('Could not delete this episode.. We\'re Sorry','flash_error');
			$this->redirect($this->referer());
			return;	
		}
		$uid = $this->Auth->User('id');
		
		$this->Episode->read(null,$id);
		$aid = $this->Episode->data['Episode']['anime_id'];
		//$this->Activity->deleteAll(array('Activity.object_type' => 'episode', 'Activity.object_id' => $id));
		//$this->UserEpisode->deleteAll(array('UserEpisode.episode_id' => $id));
		$this->Episode->deleteAll(array('Episode.id' =>$id), false, true);
		
		$this->Session->setFlash('The episode has been deleted','flash_success');
		$this->redirect('/anime/view/'.$aid);
		return;
	}

}
?>
