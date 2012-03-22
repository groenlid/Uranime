<?php
extract($anime['Anime']);

if($image == null || $image == "")
	$image = "http://placehold.it/225x112";
else
	$image = IMAGE_PATH.$image;
?>
<style type="text/css" rel="stylesheet">
.fanart {
	width:958px;
	height:200px;
	margin:-20px 0 10px -20px;
	border-bottom:1px solid #eee;
	box-shadow:inset 0 -1px 0 rgba(0,0,0,0.1);
	display:none;
}
</style>

<div class="fanart" style="background:url(/api/imageresize/<?=$fanart?>/960/0)">

</div>

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
<br class="clear">̈́
<br>
<?php
$status_text = array(
	'currently' => 'Currently airing',
	'finished' => 'Finished airing',
	'unaired' => 'Unaired'
	);
?>
<table class="table table-striped table-condensed">
	<tbody>
		<tr>
    		<td>Status</td>
    		<td><?=(isset($status))?$status_text[$status]:"N/A"?></td>
    	</tr>
    	<tr>
    		<td>Runtime</td>
    		<td><?=(isset($runtime))?$runtime . " min":"N/A"?></td>
    	</tr>
    </tbody>
</table>


<?php


if($this->Session->check('Auth.User.id') && $this->Session->check('Auth.User.id') == 1)
{
	echo '<h3> Admin info </h3>';
	echo $this->Html->link('Scrape', '/anime/setScrape/'.$id, array('class' => 'btn success'));
	
}

?>

</div>
