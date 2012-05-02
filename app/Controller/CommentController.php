<?php
class CommentController extends AppController {
	var $helpers = array('Text','Gravatar');
	var $uses = array('User','Anime','Comment');

	function add($anime_id = null){
		if($this->Auth->User('id') == NULL || empty($this->request->data))
			$this->redirect($this->referer());

		// Check if anime exists
		$anime = $this->Anime->read(null,$anime_id);
		if($anime == null)
			$this->redirect($this->referer());
		App::uses('Sanitize', 'Utility');

		$this->request->data = Sanitize::clean($this->request->data);
		
		//debug($this->request->data);
		$this->Comment->create();
		$this->Comment->set('anime_id',$anime_id);
		$this->Comment->set('user_id',$this->Auth->User('id'));
		$this->Comment->set('comment',$this->request->data['comment']);

		if($this->Comment->save())
			$this->Session->setFlash("Your comment has been posted",'flash_success');
		else
			$this->Session->setFlash("Your comment could not be posted!",'flash_error');
		$this->redirect($this->referer());
	}

}
?>
