<?php
// Find the last 5 added animes
$anime;
$lastAnime = array();
$randomAnime;
for($i = 0; $i < 5; $i++)
{
	array_push($lastAnime,$anime[count($anime)-$i-1]['Anime']);
}
	$randomAnimeId = mt_rand(0,count($anime)-1);

$fanart = $anime[$randomAnimeId]['Anime']['fanart'];
if($fanart == null || $fanart == "")
		$fanart = "http://placehold.it/860x300";
	else
		$fanart = SERVER_PATH . IMAGE_PATH.$fanart;
?>
<div id="fanart">
	<div style="box-shadow:inset 0 0 5px rgba(0,0,0,0.3);border:1px solid #fff;background:url('http://src.sencha.io/860/<?= $fanart?>'); width:100%; height:100%"></div>
	<div id="transparrentLayer" style="display:block;">
		<a href="/anime/view/<?=$anime[$randomAnimeId]['Anime']['id']?>/<?=$anime[$randomAnimeId]['Anime']['title']?>">
			<?= $anime[$randomAnimeId]['Anime']['title']?>
		</a>
	</div>
</div>

<h3>Latest added anime</h3>
<div class="anime-gallery-poster">
<?php
foreach($lastAnime as $animeSingle)
{
	$image = $animeSingle['image'];
	if($image == null || $image == "")
		$image = "http://placehold.it/225x112";
	else
		$image = IMAGE_PATH.$image;

	echo "<div class='anime-gallery-single'>
		<div class='anime-gallery-single-inner'>"
		.$this->Html->link(
			$this->Html->image($image,array('style' => 'height:225px; width:150px;')),
			'/anime/view/'.$animeSingle['id'].'/'.$animeSingle['title'],
			array('escape' => false)
		)."
		</div>
		<span class='anime-gallery-single-name'>".$this->Text->truncate($animeSingle['title'],20)."</span>
	</div>";
}
?>
<br class="clear">
</div>

<h3>Top anime</h3>
<div class="anime-gallery-poster">
<?php
foreach($animerating as $anime)
{
	$animeSingle = $anime['Anime'];
	$image = $animeSingle['image'];
	if($image == null || $image == "")
		$image = "http://placehold.it/225x112";
	else
		$image = IMAGE_PATH.$image;

	echo "<div class='anime-gallery-single'>
		<div class='anime-gallery-single-inner'>"
		.$this->Html->link(
			$this->Html->image($image,array('style' => 'height:225px; width:150px;')),
			'/anime/view/'.$animeSingle['id'].'/'.$animeSingle['title'],
			array('escape' => false)
		)."
		</div>
		<span class='anime-gallery-single-name'>".$this->Text->truncate($animeSingle['title'],20)."</span>
	</div>";
}
?>
<br class="clear">
</div>
<br class="clear">

