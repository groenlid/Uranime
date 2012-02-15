
<div id="left">
<?php
$episodeDate = array();
echo "<pre>";
print_r($episodes);
echo "</pre>";
foreach($episodes as $episode)
{
	$date = $episode['Episode']['aired'];
	if(!array_key_exists($date, $episodeDate))
		$episodeDate[$date] = array();
	array_push($episodeDate[$date],$episode);

}
foreach($episodeDate as $date => $episodes)
{
	if($this->Time->isToday(strtotime($date)))
		echo '<h2>Today</h2>';
	else if($this->Time->isTomorrow(strtotime($date)))
		echo '<h2>Tomorrow</h2>';
	else
		echo '<h2>' . date('l, d, M',strtotime($date)) . '</h2>';

	echo '<div id="anime-gallery">';
	foreach($episodes as $episode)
	{
		echo '<div class="anime-gallery-single">
			<div class="anime-gallery-single-inner">'.
		     	'<a href="/anime/view/'.$episode['Anime']['id'].'/'.$episode['Anime']['title'].'">';
		if($episode['Anime']['fanart'] != null)
			echo '<img src="http://src.sencha.io/200/'.$episode['Anime']['fanart'].'">';
		else
			echo '<img src="http://placehold.it/200x112">';
		echo '</a>'.
			'<span class="anime-gallery-single-hover"><a href="/anime/view/'.$episode['Anime']['id'].'/'.$episode['Anime']['title'].'">View Anime</a></span>'.
			'</div>
			<p class="bold calendarinfo">'.$episode['Anime']['title']. ' ' . $episode['Episode']['number'] . '</p>
			<p class="calendarinfo">'. $episode['Episode']['name'].'</p>
			</div>';	
	}
	echo "<br class='clear'>";
	echo '</div>';
}
?>
</div>
<?php

//print_r($episodeDate);
?>
	<div id="right">
<?php
if(isset($own))
	echo '<p class="strong"><a href="/calendar/view/all">Show all anime</a></p>';
else
	echo '<p class="strong"><a href="/calendar/view">Just show own anime</a></p>';
?>
</div>
