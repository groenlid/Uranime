<?php
extract($anime['Anime']);

if($image == null || $image == "")
	$image = "http://placehold.it/225x112";
else
	$image = IMAGE_PATH.$image;
?>
<div class="span3 pull-right">
	<?php
	if($this->Session->check('Auth.User.id'))
	{
		echo '<div class="star-ratings">';
		for($i = 1; $i <= 5; $i++)
			if($user_rate['AnimeRating']['rate'] >= $i && $user_rate != null)
				echo '<a class="active" href="/anime/rate/'.$id.'/'.$i.'"><span class="no-display">'.$i.'</span></a>';
			else
				echo '<a href="/anime/rate/'.$id.'/'.$i.'"><span class="no-display">'.$i.'</span></a>';
		echo '</div>';
	}
	?>

	<div class="animeimagewrapper" style="position:relative">
		<a href="/anime/editImage/<?=$id?>">
			<img class="animeimage" src="<?= $image ?>">
		</a>
		<div id="transparrentLayer" class="animeimageLayer">
			Change poster/fanart
		</div>
	</div>
	<ul class="referencelinks">
<?php
$links = array(
	'mal' => 'http://myanimelist.net/anime/',
	'anidb' => 'http://anidb.net/perl-bin/animedb.pl?show=anime&aid=',
	'thetvdb' => 'http://thetvdb.com/?tab=series&id='
);

foreach($anime['ScrapeInfo'] as $scrapeInfo)
	echo "	<li id='".$scrapeInfo['scrape_source']."'>".
			"<a href='".$links[$scrapeInfo['scrape_source']].$scrapeInfo['scrape_id']."'>".$scrapeInfo['scrape_source']."</a>".
		"</li>";
?>
	</ul>
<br class="clear">
<?php


if($this->Session->check('Auth.User.id') && $this->Session->check('Auth.User.id') == 1)
{
	echo '<h3> Admin info </h3>';
	echo $this->Html->link('Scrape', '/anime/setScrape/'.$id, array('class' => 'btn success'));
	
}

?>

</div>
