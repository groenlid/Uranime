<?php
class SearchController extends AppController {
	var $helpers = array('Text');
	var $uses = array('Anime','AnimeSynonym');
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
			);*/
		$animes = $this->AnimeSynonym->find('all',
			array(
				'fields' => array(
					'DISTINCT(anime_id)',
					'Anime.*'),
				'conditions' => array(
						'LOWER(AnimeSynonym.title) LIKE' => "%".strtolower($search)."%",
					),
				'order' => 'Anime.title ASC'
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
