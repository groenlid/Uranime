<?php
	extract($user['User']);
	
?>
<div class="row">
<div class="span2">
	<div>
		<?= $this->Gravatar->image($email, array('class' => 'animeimage', 'size' => '150', 'rating' => 'r')) ?>
	</div>
<a href="/library/view/<?=$id.'/'.$nick?>" class="btn">View Anime-library</a>
</div>
<div class="span7">
	<h2><?= $nick ?></h2>
	<blockquote>
		<p class="animedesc">
			
		<?= $desc ?>
	</p>
	</blockquote>
<div id="newsfeed">
<?php
foreach($activity as $a)
	echo $this->element('activity', array(
    	"activity" => $a,
    	"kindOfObject" => "user"
    	));
//	echo print_activity($a);


/*function print_activity($activity)
{
	if(!isset($activity['object']['Anime']))
		return;
	$result = "";
	$result .= "<div class='row newsfeed'>";
	// The image
	$result .= "<div class='span1'>";
	switch($activity['Activity']['object_type']){
		case('episode'):
		case('reference'):
		case('image'):
		case('anime'):
		case('fanart'):
		
		$result .= "<a href='/anime/view/".$activity['object']['Anime']['id']."/".$activity['object']['Anime']['title']."'><img src='http://src.sencha.io/50/".SERVER_PATH . IMAGE_PATH. $activity['object']['Anime']['fanart']."'></a>";

		break;
	}
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
	return $result;
}*/
//debug($activity);
?>
</div>
</div>
</div>
