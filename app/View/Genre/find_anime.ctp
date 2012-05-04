<h2>Anime containing tag/genre: <strong><?=$genre['Genre']['name']?></strong></h2>
<div class='alert alert-info'><?=$genre['Genre']['description']?></div>
<?php

echo "<div id='anime-gallery'>";
foreach($anime as $single)
{
	$fanart = $single['Anime']['fanart'];
	if($fanart == null || $fanart == "")
		$fanart = "http://placehold.it/200x112";
	else
		$fanart = SERVER_PATH . IMAGE_PATH.$fanart;

	// Make it a proper size
	$fanart = "http://src.sencha.io/200/". $fanart;
	echo "
		<div class='anime-gallery-single'>
			<div class='anime-gallery-single-inner'>";
				echo $this->Html->link($this->Html->image($fanart,array('style' => 'height:112px;width:200px;')), '/anime/view/'.$single['Anime']['id'], array('escape' => false));
				echo "<span class='anime-gallery-single-hover'>".$this->Html->link("View Anime",'/anime/view/'.$single['Anime']['id'])."</span>
			</div>
			<span class='anime-gallery-single-name'>" . $single['Anime']['title'] . "</span>
		</div>
	";
}
echo "</div><br class='clear'>";