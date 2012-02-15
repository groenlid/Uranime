<?php
class SearchController extends AppController {
	var $helpers = array('Text');
	var $uses = array('Anime');
	var $components = array(
		'RequestHandler',
		'Rest.Rest' => array(
			'catchredir' => false,
			'actions' =>array(
				'basic' => array(
					'extract'	=> array('animes.{n}.Anime' => 'anime')
				)
			)
		)
	);
	
	function index() {
	}
	
	function redir() {
		$this->redirect(array('controller' => 'search', 'action' => 'basic/'.trim($_POST['mainsearch'])));
	}
	
	function basic($search = '') {
		//$this -> render('basic');
		App::uses('Helper','Text'); 
		//$this->Text = new TextHelper();
		$search = trim($search);
		$this->Anime->recursive = -1;
		$animes = $this->Anime->find('all', 
			array(
				'conditions' 	=> array('title LIKE' => "%$search%"),
				'table' => array('anime'),
				'fields' => array('title','id','image','desc','fanart'),
				'order' => array('title ASC')
				)
			);
		$this->set(compact('animes'));
		$this->set('animes',$animes);
		$this->set('query',$search);
		/*echo '<div id="arrow"></div>';
		
			echo "<pre>";
			print_r($animes);
			echo "</pre>";
		*/
	}
}
?>
