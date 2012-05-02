<?php
class FrontController extends AppController {
	//var $helpers = array('Text','Time');
	var $uses = array('Anime');
	
	function index() {
		$this->set('page','home');
		/*$episodes = $this->Episode->find('all', array(
			'order' => 'aired ASC',
			'conditions' => array(
				'Episode.aired BETWEEN CURDATE() AND DATE_ADD(NOW(),INTERVAL 7 DAY)'
			)
		));*/

		//$this->set('episodes',$episodes);
		// Temporary show the cron queue.
		//$this->set('queue',$this->ScrapeInfo->find('all'));
		//$this->render('/pages/home');

		//$this->set('activities',$this->Activity->find('all'));
	}
	
	function searchlimit() {
		

		App::import('TextHelper','Helper'); 
		//debug($this);
		//$this->Text = new TextHelper();
		$search = $this->data['search'];//$_POST['search'];
		//debug($this->Text);
		die();
		$this->Anime->recursive = -1;
		$animes = $this->Anime->find('all', 
			array(
				'conditions' 	=> array('title LIKE' => "%$search%"),
				'table' => array('anime'),
				'fields' => array('title','id','image'),
				'limit' => 5
				)
			);

		echo '<div class="arrow"></div>';
		echo '<div class="inner">';
		echo '<h3 class="title">Results</h3>';
		echo '<div class="content">';
		if(count($animes) == 0)
		{
			echo "No results matches your criteria";
			die();
			return;
		}
		
	
		
		foreach($animes as $key => $anime)
		{
			/*echo '<div class="resultitem"><img src="'.$anime['Anime']['image'].'"><p><a href="/anime/view/'.$anime['Anime']['id'].'/'.$anime['Anime']['title'].'">'.$this->Text->highlight($this->Text->truncate($anime['Anime']['title'],50),$search).'</a></p></div>';*/
			echo '<div class="resultitem"><p><a href="/anime/view/'.$anime['Anime']['id'].'/'.$anime['Anime']['title'].'">'.$this->Text->highlight($this->Text->truncate($anime['Anime']['title'],50),$search).'</a></p></div>';
		}
		echo '</div>';
		echo '</div>';
		die();
	}
}
?>
