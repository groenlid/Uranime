<?php
class GenreController extends AppController {
	var $uses = array('Genre','User','Anime','AnimeGenre');	
	var $helpers = array('Time','Text');

	function index() {
		
	}
	
	/**
	 * Takes arraylist with tags/genres id as input
	 */
	private function findAnimeByTags($tags){
		
		
		$sqlStringFrom = "";
		$sqlStringWhere = "";
		
		$it = 0;
		foreach($tags as $tagid)
		{
			if(!is_numeric($tagid))
				continue;
			$it++;
			$sqlStringFrom .= "anime_genre AS genre" . $it . ", ";
			 
			$sqlStringWhere .= " genre".$it.".genre_id=".$tagid." AND";
			
			
		}
		$sqlStringWhere = substr($sqlStringWhere, 0,strlen($sqlStringWhere)-4);
		$sqlStringFrom = substr($sqlStringFrom, 0,strlen($sqlStringFrom)-2);
		// Find the animes from the pivot table
		$this->Anime->recursive = -1;
		$anime = $this->Anime->find('all',array(
				'conditions' => array(
					'Anime.id IN (
						SELECT DISTINCT(genre'.$it.'.anime_id) FROM '.$sqlStringFrom.' WHERE '.$sqlStringWhere.' 
					)'
				),
				'order' => array('Anime.title')
			));
		return $anime;
	}
	
	/**
	 * Finds anime based on specified tag
	 */
	function findAnime($tagid){
		if(!is_numeric($tagid))
			return false;
		$this->set('genre',$this->Genre->read(null,$tagid));
		$this->set('anime',$this->findAnimeByTags(array($tagid)));
		/*if($this->request->data)
		{
			$data = $this->request->data;
			print_r($data);
		}else{
			$this->Genre->recursive = -1;
			$this->set('genres',$this->Genre->find('all'));
		}*/
	}
	
}
	function cmpTitle($a, $b)
	{
	    if ($a['Anime']['title'] == $b['Anime']['title']) {
	        return 0;
	    }
	    return ($a['Anime']['title'] < $b['Anime']['title']) ? -1 : 1;
	}
?>
