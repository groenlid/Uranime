<?php
class SearchController extends AppController {
	var $helpers = array('Text');
	var $uses = array('Anime','AnimeSynonym');
	var $components = array(
		'RequestHandler',
		'Paginator',
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
		App::uses('Sanitize', 'Utility');
		//$this -> render('basic');
		App::uses('Helper','Text'); 
		//$this->Text = new TextHelper();
		$search = Sanitize::clean($search, array('encode' => false));
		$search = trim($search);
		//$this->Anime->recursive = -1;


		/*$animes = $this->Anime->find('all', 
			array(
				'conditions' 	=> array("OR" => 
					array(
						'Anime.title LIKE' => "%$search%",
						'AnimeSynonym.title LIKE' => "%$search%"
						)
					),
				'table' => array('anime'),
				'fields' => array('Anime.title','Anime.id','image','desc','fanart'),
				'order' => array('title ASC'),
				'joins' => array(
					array(
							'table' => 'anime_synonyms',
							'alias'	=> 'AnimeSynonym',
							'type' => 'LEFT',
							'conditions' => array(
									'AnimeSynonym.anime_id = Anime.id'
								)
						)
					)
				)
			);*///$this->set('episodes', $this->paginate($this->Anime->Episode, array('Anime.id' => $id)));	
		/*$animes = $this->AnimeSynonym->find('all',
			array(
				'fields' => array(
					'DISTINCT(anime_id)',
					'Anime.*'),
				'conditions' => array(
						'LOWER(AnimeSynonym.title) LIKE' => "%".strtolower($search)."%",
					),
				'order' => 'Anime.title ASC'
			)
		);*/
		
		$this->paginate = array(
        		'fields' => array(
					/*'DISTINCT(anime_id)',*/
					'Anime.*'),
				'from' => array(
					'Anime',
					'AnimeSynonym'
				),
				'conditions' => array(
						'LOWER(AnimeSynonym.title) LIKE' => "%".strtolower($search)."%",
					),
				'order' => 'Anime.title ASC',
				'group' => 'Anime.id',
        		'limit' => 25
    	);

		//$this->set(compact('animes'));
		$this->set('animes',$this->paginate('AnimeSynonym'));
		
		$this->set('query',$search);
		/*echo '<div id="arrow"></div>';
		
			echo "<pre>";
			print_r($animes);
			echo "</pre>";
		*/
	}
}
?>
