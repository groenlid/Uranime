<?php
class AnimeRequestController extends AppController {
	var $uses = array('User','Activity','Anime','AnimeRequest','AnimeSynonym');
	var $helpers = array('Gravatar','html');
	
	private function isAdmin(){
		return ($this->Auth->User('id') == 1);
	}
	
	private function requireAdmin(){
		if($this->Auth->User('id') != 1)
		{
			$this->Session->setFlash("You are not allowed on this page.",'flash_error');
			$this->redirect($this->referer());
		}
	}
	
	public function view($id = null){
		if($id == null || !is_numeric($id))
		{
			$this->Session->setFlash("Wrong ID");
			$this->redirect($this->referer());
		}
		
		$this->set('request',$this->AnimeRequest->read(null,$id));
		
		if($this->isAdmin())
		{
			if (empty($this->request->data)) {
       			$this->request->data = $this->AnimeRequest->findById($id);
    		} else {
        		// Save logic goes here
        		if($this->AnimeRequest->save($this->request->data))
				{
					$this->Session->setFlash("Successfully edited the anime request.",'flash_success');
					$this->redirect($this->referer());
				}
    		}
		}
		
	}
	
	public function edit($id = null){
		
	}
	
	public function decide($id = null, $decision = null){
		$this->requireAdmin();
		if($id == null || $decision == null)
			$this->redirect($this->referer());

		// If decision is false -> delete request
		if($decision == "false")
		{
			if($this->AnimeRequest->delete($id))
			{
				$this->Session->setFlash("The request has been deleted.",'flash_success');
			}
		} else if($decision == "true")
		{
			// Fetch animerequest and insert it as an anime
			$this->AnimeRequest->read(NULL, $id);
			$this->Anime->create();
			$this->Anime->set('title',$this->AnimeRequest->data['AnimeRequest']['title']);
			if($this->Anime->save())
			{

				// Create new animesynonym with the title
				$this->AnimeSynonym->create();
				$this->AnimeSynonym->set('title',$this->AnimeRequest->data['AnimeRequest']['title']);
				$this->AnimeSynonym->set('anime_id',$this->Anime->id);
				$this->AnimeSynonym->save();
				
				$this->AnimeRequest->delete($id);
				// ADDING ACTIVITY
				$this->Activity->create();
				$this->Activity->set('subject_type','user');
				if($this->AnimeRequest->data['AnimeRequest']['user_id'] != null 
					&& isset($this->AnimeRequest->data['AnimeRequest']['user_id']))
					$this->Activity->set('subject_id',$this->AnimeRequest->data['AnimeRequest']['user_id']);
				else
					$this->Activity->set('subject_id',$this->Auth->User('id'));
				$this->Activity->set('verb','added');
				$this->Activity->set('object_type','anime');
				$this->Activity->set('object_id',$this->Anime->id);
				$this->Activity->save();

				$this->Session->setFlash("Anime Saved!",'flash_success');
				$this->redirect('/anime/view/'.$this->Anime->id.'/'.$this->Anime->title);
			}
		}
		$this->redirect($this->referer());
	}
}
?>