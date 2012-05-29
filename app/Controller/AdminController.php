<?php
class AdminController extends AppController {
	var $uses = array('User','Activity','Anime','AnimeRequest','AnimeSynonym');

	private function requireAdmin(){
		if($this->Auth->User('id') != 1)
		{
			$this->Session->setFlash("You are not allowed on this page.",'flash_error');
			$this->redirect($this->referer());
		}
	}

	public function index(){
		$this->requireAdmin();
		$this->set('animerequests',$this->AnimeRequest->find('all'));
	}

}
?>