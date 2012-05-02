<div class="row">
<div class="span8">
<?php
$episodeDate = array();
$last_date = "";
function setDate($time)
{
	return date('l, d, M',$time);
}
$i = true;
foreach($episodes as $episode)
{

	$fanart = $episode['Anime']['fanart'];
	if($fanart == "" || $fanart == null)
		$fanart = "http://placehold.it/200x112/";
	else
		$fanart = SERVER_PATH . IMAGE_PATH . $fanart;

	$timeStr = strtotime($episode['Episode']['aired']);
	$date = setDate($timeStr);
	if($last_date != $date)
	{
		if(!$i)
		{
			echo '<br class="clear"></div>';
		}
		else
			$i = false;

		if($this->Time->isToday($timeStr))
			echo '<h2>Today</h2>';
		else if($this->Time->isTomorrow($timeStr))
			echo '<h2>Tomorrow</h2>';
		else
			echo '<h2>' . $date . '</h2>';
		
		echo '<div id="anime-gallery">';
	}

		echo '<div class="anime-gallery-single">
			<div class="anime-gallery-single-inner">'.
		     	'<a href="/anime/view/'.$episode['Anime']['id'].'/'.$episode['Anime']['title'].'">';
		if($episode['Anime']['fanart'] != null)
			echo '<img src="http://src.sencha.io/200/'.$fanart.'">';
		else
			echo '<img src="http://placehold.it/200x112">';
		echo '</a>'.
			'<span class="anime-gallery-single-hover"><a href="/episode/view/'.$episode['Episode']['id'].'/">View Episode</a></span>'.
			'</div>
			<p class="bold calendarinfo"><a href="/anime/view/'.$episode['Anime']['id'].'/'.$episode['Anime']['title'].'">'.$episode['Anime']['title']. '</a> ' . $episode['Episode']['number'] . '</p>
			<p class="calendarinfo">'. $this->Text->truncate($episode['Episode']['name'],35).'</p>
			</div>';	
	



	$last_date = $date;
}
if(count($episodes) != 0)
	echo '<br class="clear"></div>';
?>
</div>
<?php

//print_r($episodeDate);
?>
<div class="span3">
	<div class="well">
<?php
if(isset($own))
	echo '<p class="strong"><a href="/calendar/view/all" class="btn info">Show all anime</a></p>';
else
	echo '<p class="strong"><a href="/calendar/view" class="btn info">Just show own anime</a></p>';
?>
	</div>
</div>
</div>