<?php
	$activity;
	$kindOfObject;
	//debug($activity);
	switch($kindOfObject){
		case('anime'):
			$image = $this->Html->link(
						$this->Gravatar->image(
							$activity['subject']['email'], 
							array('size' => '30', 'rating' => 'pg'),
							array('class' => 'animeimage')
						),
						'/user/view/'.$activity['subject']['id'].'/'.$activity['subject']['nick'],
						array('escape' => false)
					);
			break;	
		case('user'):
		default:
			$image = "<a href='/anime/view/".$activity['object']['Anime']['id']."/".$activity['object']['Anime']['title']."'><img src='http://src.sencha.io/50/".SERVER_PATH . IMAGE_PATH. $activity['object']['Anime']['fanart']."'></a>";
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
	
	$nick = $activity['subject']['nick'];

	switch($activity['Activity']['verb']){

		case('added'):

			switch($activity['Activity']['object_type']){
				case('anime'):
					$action = " added a new anime to the system named " . $activity['object']['Anime']['title'];
				break;
				case('fanart'):
					$action = " added/changed fanart for " . $activity['Activity']['option'] . " for anime " . $activity['object']['Anime']['title'];
				break;
				case('image'):
					$action = " added/changed image for " . $activity['Activity']['option'] . " for anime " . $activity['object']['Anime']['title'];
				break;
				case('reference'):
					$action = " added a reference for " . $activity['Activity']['option'] . " for anime " . $activity['object']['Anime']['title'];
				break;

			}

		break;
		case('comment'):
			$action = " added a comment ";
		break;
		case('watched'):

			switch($activity['Activity']['object_type']){

				case('episode'):
					$action = " watched <a href='/episode/view/".$activity['Activity']['object_id']."'>episode " . $activity['object']['Episode']['number']. "</a>";
					if(isset($activity['object']['Anime']))
						$action = "  of anime <a href='/anime/view/".$activity['object']['Anime']['id']."'>" . $activity['object']['Anime']['title']."</a>";
				break;
			}

		break;

	}
	if($activity['Activity']['verb'] == 'comment'){
		$time = $activity['object']['Comment']['timestamp'];
		$commentText = $this->Text->autoLink($activity['object']['Comment']['comment']);
	}
	else
		$time = $activity['Activity']['timestamp'];
	
	$time = strftime("%Y-%m-%dT%H:%M:%S%z",strtotime($time));
	$result = "";
	/*$result .= "<div class='row newsfeed'>";
	// The image
	$result .= "<div class='span1'>";
	$result .= $image;
	$result .= "</div>";
	// The desc
	$result .= "<div class='span5'><p>";
	$result .= $user . ' ' . $action;
	
	$result .= "";
	
	$result .= "</p><p class='subtle'>".$time."</p>";
	$result .= "</div>";	
	$result .= "</div>";*/

	// New comment system
	$result .= "
	<div class='comment-container'>
		<div class='comment-avatar'>
			".$image."
		</div>
		<div class='comment'>
			<div class='comment-meta'>" . $nick . " " . $action . "<span class='comment-time'><abbr class='timeago' title='".$time."'></abbr></span></div>";

	if($activity['Activity']['verb'] == 'comment')
		$result .= "
	<div class='comment-text'>
		".$commentText."
	</div>
	";

	$result .= "
		</div>
	</div>
	";
	echo $result;
?>