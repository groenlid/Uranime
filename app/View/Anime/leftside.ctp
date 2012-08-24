<?php
extract($anime['Anime']);

if($image == null || $image == "")
	$image = "http://placehold.it/225x112";
else
	$image = IMAGE_PATH.$image;
?>
<!--<style type="text/css" rel="stylesheet">
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
-->
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
	'thetvdb' => 'http://thetvdb.com/?tab=series&id=',
	'themoviedb' => 'http://themoviedb.org/movie/'
);

foreach($anime['ScrapeInfo'] as $scrapeInfo)
	echo "	<li id='".$scrapeInfo['scrape_source']."'>".
			"<a href='".$links[$scrapeInfo['scrape_source']].$scrapeInfo['scrape_id']."'>".$scrapeInfo['scrape_source']."</a>".
		"</li>";
?>
	</ul>
<br class="clear">
<?php
$status_text = array(
	'currently' => 'Currently airing',
	'finished' => 'Finished airing',
	'unaired' => 'Unaired'
	);
?>
<table class="table table-striped table-condensed table-small">
	<tbody>
		<tr>
    		<td>Status</td>
    		<td><?=(isset($status))?$status_text[$status]:"N/A"?></td>
    	</tr>
    	<tr>
    		<td>Runtime</td>
    		<td><?=(isset($runtime))?$runtime . " min":"N/A"?></td>
    	</tr>
<?php
if($type != 'movie')
{

$reg_episodes = 0;
$special = 0;
foreach($anime['Episode'] as $episode){
	if($episode['special'] == null)
		$reg_episodes++;
	else
		$special++;
}
echo '
    	<tr>
    		<td>Episodes</td>
    		<td>'.((isset($anime['Episode'])) ? $reg_episodes . " (" . $special . " specials)" : "0" ).'</td>
    	</tr>
    	<tr>
    		<td>Time</td>
        )<td>'.((isset($anime['Episode']) && isset($runtime))? ($runtime * $reg_episodes. " min" ) :"N/A").'</td>
    	</tr>
    	';
}
?>

    	<tr>
    		<td>Aired</td>
    		<td><?=(isset($anime['Episode'][0])) ? $anime['Episode'][0]['aired'] . " - " : "N/A" ?>
    			<?=(isset($anime['Episode']) && $anime['Anime']['status'] == 'finished') ? $anime['Episode'][count($anime['Episode'])-1]['aired'] : ""; ?>
    	</tr>
    	<tr>
    		<td>Class</td>
    		<td><?=(isset($classification))? $classification: 'N/A' ?></td>
    	</tr>
    	<tr>
    		<td>Type</td>
    		<td><?=(isset($type))? strtoupper($type) : 'N/A' ?></td>
        </tr>
    </tbody>
</table>
<?php

// Add to watchlist
if($this->Session->check('Auth.User.id'))
{
	if($watchlist != null)
		echo $this->Html->link('Remove from watchlist','/watchlist/remove/'.$id,array('class' => 'span2 btn btn-warning'));
	else
		echo $this->Html->link('Add to watchlist','/watchlist/add/'.$id,array('class' => 'span2 btn btn-primary'));
}
if($isAdmin)
	echo $this->Html->link('Scrape', '/anime/setScrape/'.$id, array('class' => 'btn span2 btn-success'));
?>
<hr>
<!-- Place this tag where you want the +1 button to render. -->

<div class="g-plusone" data-annotation="inline" data-width="300"></div>



<!-- Place this tag after the last +1 button tag. -->

<script type="text/javascript">

  (function() {

          var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;

              po.src = 'https://apis.google.com/js/plusone.js';

              var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);

                })();

</script>
</div>
