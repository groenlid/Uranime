<?php
//debug($anime);
extract($anime['Anime']);
?>
<?php
	include('leftside.ctp');
?>
<div class="row">
<div class="span8">
	<h2><?= $title ?><span class="pull-right"><small><?=round($calc_rating['avg_rate'],2)?> <span class="subtle">( <?=$calc_rating['amount']?> votes )</small></span></span></h2>
	<!-- ANIME MENU -->
	<!--<div class="actions no-padding">-->
		<ul class="nav nav-tabs">
			<li><a href="/anime/view/<?=$id . '/' . $title?>">Summary</a></li>
			<li><a href="/anime/viewepisodes/<?=$id . '/' . $title?>">Episodes</a></li>
			<li><a href="/anime/viewref/<?=$id . '/' . $title?>">References</a></li>
			<li class="active"><a href="/anime/viewtags/<?=$id . '/' . $title?>">Tags/Genres</a></li>
		</ul>
	<!--</div>-->

	<ul class="genres">
	<?php
	foreach($genres as $genre)
		echo '<li rel="tooltip" title="'.$genre['Genre']['description'].'">'.ucfirst($genre['Genre']['name']).'</li>';
	?>
	</ul>
<br class="clear">
</div>

</div>