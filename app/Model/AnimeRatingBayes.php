<?php
class AnimeRatingBayes extends AppModel
{
	public $belongsTo = array( 'Anime' );
	var $useTable = 'anime_ratings_bayesian';
	
	public function getHighestRated($amount = 10, $offset = 0)
	{
		$options = array(
			'fields' => array(
				'anime_id, ((avg_num_votes * avg_rating) + (this_num_votes * this_rating)) / (avg_num_votes + this_num_votes) as real_rating'
			),
            'limit' => $amount,
            'offset' => $offset,
			'order' => 'real_rating DESC'
		);
		$anime = $this->find('all',$options);
		for($i = 0; $i < count($anime);$i++)
		{
			$this->Anime->recursive = -1;
			$addon = $this->Anime->find('first',array(
				'conditions' => array(
					'Anime.id' => $anime[$i]['AnimeRatingBayes']['anime_id']
					)
				)
			);
			$anime[$i]['Anime'] = $addon['Anime'];
		}
		return $anime;
	}
}
?>
