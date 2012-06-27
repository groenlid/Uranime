<?php
//debug($anime);
extract($anime['Anime']);
?>
<?php
	include('leftside.ctp');
?>
<div class="row">
<div class="span8">
<?php
	include('sub_menu.php');
?>


	<ul class="genres">
	<?php
	foreach($genres as $genre)
	{
		$is_genre = ($genre['Genre']['is_genre'] != null) ? "genre" : "";
		echo '<li class="'.$is_genre.'" rel="tooltip" title="'.nl2br($genre['Genre']['description']).'"><a href="/genre/findAnime/'.$genre['Genre']['id'].'">'.ucfirst($genre['Genre']['name']).'</a></li>';
	}
	?>
	</ul>
<br class="clear">
</div>

</div>
