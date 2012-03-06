<?php
	$activity;
	$kindOfObject;
	
	/*echo "<pre>";
	print_r($activity);
	echo "</pre>";*/
	
	if(!isset($activity['object']['Anime']))
		return;
	$result = "";
	$result .= "<div class='row newsfeed'>";
	// The image
	$result .= "<div class='span1'>";
	
	switch($kindOfObject){
		case('anime'):
			$result .= $this->Html->link(
						$this->Gravatar->image(
							$activity['subject']['email'], 
							array('class' => 'animeimage', 'size' => '30', 'rating' => 'r')
						),
						'/user/view/'.$activity['subject']['id'].'/'.$activity['subject']['nick'],
						array('escape' => false)
					);
			break;	
		case('user'):
		default:
			$result .= "<a href='/anime/view/".$activity['object']['Anime']['id']."/".$activity['object']['Anime']['title']."'><img src='http://src.sencha.io/50/".SERVER_PATH . IMAGE_PATH. $activity['object']['Anime']['fanart']."'></a>";
			break;
	}
	/*switch($activity['Activity']['object_type']){
		case('episode'):
		case('reference'):
		case('image'):
		case('anime'):
		case('fanart'):
		
		$result .= "<a href='/anime/view/".$activity['object']['Anime']['id']."/".$activity['object']['Anime']['title']."'><img src='http://src.sencha.io/50/".SERVER_PATH . IMAGE_PATH. $activity['object']['Anime']['fanart']."'></a>";

		break;
	}*/
	$result .= "</div>";
	// The desc
	$result .= "<div class='span5'><p>";
	$result .= $activity['subject']['nick'];

	switch($activity['Activity']['verb']){

		case('added'):

			switch($activity['Activity']['object_type']){
				case('anime'):
					$result .= " added a new anime to the system named " . $activity['object']['Anime']['title'];
				break;
				case('fanart'):
					$result .= " added/changed fanart for " . $activity['Activity']['option'] . " for anime " . $activity['object']['Anime']['title'];
				break;
				case('image'):
					$result .= " added/changed image for " . $activity['Activity']['option'] . " for anime " . $activity['object']['Anime']['title'];
				break;
				case('reference'):
					$result .= " added a reference for " . $activity['Activity']['option'] . " for anime " . $activity['object']['Anime']['title'];
				break;

			}

		break;
		case('watched'):

			switch($activity['Activity']['object_type']){

				case('episode'):
					$result .= " watched <a href='/episode/view/".$activity['object']['Episode']['id']."'>episode " . $activity['object']['Episode']['number']. "</a> of anime <a href='/anime/view/".$activity['object']['Anime']['id']."'>" . $activity['object']['Anime']['title']."</a>";
				break;
			}

		break;

	}
		
	$result .= "";
	
	$result .= "</p><p class='subtle'>".$activity['Activity']['timestamp']."</p>";
	$result .= "</div>";	
	$result .= "</div>";
	echo $result;
?>