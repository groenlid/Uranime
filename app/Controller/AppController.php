<?php
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('Controller', 'Controller');
App::uses('AuthComponent', 'Controller/Component');
App::uses('SessionComponent', 'Controller/Component');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller {

    public $components = array(
        'Session',
        'Auth'
    );
    public $uses = array('Anime','Episode','User','AnimeRequest','UserEpisode');

	function beforeFilter(){
		parent::beforeFilter();
		
		/* Login specific configuration */
		
		//debug($this);

		$this->Auth->userModel = 'User';
		$this->Auth->fields = array('username' => 'email', 'password' => 'password');
		$this->Auth->allow('*');
		$this->Auth->loginAction = array('controller' => 'user', 'action' => 'login');
		$this->Auth->loginRedirect = array(true); // This is a temporary fix
		$this->Auth->logoutRedirect = array(Configure::read('Routing.admin') => false, 'controller' => 'front', 'action' => 'index');
     	

		$numberAnime = $this->Anime->find('count');
		$numberEpisodes = $this->Episode->find('count');
		$numberUsers = $this->User->find('count');
		$numberSeenEpisodes = $this->UserEpisode->find('count');
		
		$this->set('numberAnime',$numberAnime);
		$this->set('numberEpisodes', $numberEpisodes);
		$this->set('numberUsers', $numberUsers);
		$this->set('numberSeenEpisodes', $numberSeenEpisodes);
/*
		$this->set('numberAnime',0);
		$this->set('numberEpisodes', 0);
		$this->set('numberUsers', 0);*/
		if($this->isAdmin())
			$this->set('animerequestsCount',$this->AnimeRequest->find('count'));
		$this->set('isAdmin',$this->isAdmin());
	}
	
	private function isAdmin(){
		return ($this->Auth->User('id') == 1);
	}
	
}
