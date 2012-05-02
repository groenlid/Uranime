<?php
class WatchlistController extends AppController {
	var $helpers = array('Gravatar','Time','Text','Session');
	var $uses = array('Activity','UserWatchlist','User');
	var $name = 'Watchlist';

	function index(){
		//$this->UserWatchlist->recursive = -1;
		
		//$this->set('watchlist',$this->UserWatchlist->findAllbyUserId());
	}

	function view($id = null, $sort = 'age')
	{
		if($id == null || !is_numeric($id))
			$this->redirect($this->referer());

		$this->User->recursive = -1;
		$this->set('nick',$this->User->read(array('nick','id'),$id));
		
		$sortStatement = "";
		switch($sort){
			case('title'):
				$sortStatement = 'Anime.title ASC';
				break;
			case('age'):
			default:
				$sortStatement = 'UserWatchlist.id DESC';
				break;
		}
		$this->set('watchlist',
			$this->UserWatchlist->find('all',array(
				'conditions' => array(
					'user_id' => $id
				),
				'order' => array(
					$sortStatement
					)
				)
			)
		);

	}

	/**
	*	$id: anime id
	*/
	function add($id = null){
		if($id == null || !is_numeric($id))
			$this->redirect($this->referer());

		$uid = $this->Auth->User('id');
		if($uid == NULL)
			$this->redirect($this->referer());

		// check if this watchlist item already exists in db
		$exists = $this->UserWatchlist->find('first',array(
			'conditions' => array(
					'user_id' => $uid,
					'anime_id' => $id
				)
			));

		if($exists != null)
		{
			$this->Session->setFlash('You already have this anime in your watchlist.','flash_error');
			$this->redirect($this->referer());
		}
		$this->UserWatchlist->create();
		$this->UserWatchlist->set('anime_id',$id);
		$this->UserWatchlist->set('user_id',$uid);
		if($this->UserWatchlist->save())
		{
			$this->Session->setFlash('Successfully added the anime to your watchlist','flash_success');
		}else {
			$this->Session->setFlash('Could not add this anime to your watchlist. Sorry :(','flash_error');
		}
		$this->redirect($this->referer());
	}

	function remove($id = null){
		if($id == null || !is_numeric($id))
			$this->redirect($this->referer());

		$uid = $this->Auth->User('id');
		if($uid == NULL)
			$this->redirect($this->referer());

		$this->UserWatchlist->recursive = -1;
		// check if this watchlist item already exists in db
		$exists = $this->UserWatchlist->find('first',array(
			'conditions' => array(
					'user_id' => $uid,
					'anime_id' => $id
				)
			));

		if($exists == null)
		{
		        $this->Session->setFlash('You do not have this anime in your watchlist','flash_error');
		        $this->redirect($this->referer());
		}
		$wid = $exists['UserWatchlist']['id'];
		
		if($this->UserWatchlist->delete($wid,false))
		{
			$this->Session->setFlash('The anime has been removed from your watchlist','flash_success');
			$this->redirect($this->referer());
		}
		else{
			$this->Session->setFlash('Could not delete this entry. Sorry :(','flash_error');
			$this->redirect($this->referer());
		}

	}

}
?>
