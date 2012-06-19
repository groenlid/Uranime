	<h2><?= $title ?><span class="pull-right"><small><?=round($calc_rating['avg_rate'],2)?> <span class="subtle">( <?=$calc_rating['amount']?> votes )</small></span></span></h2>
	<!-- ANIME MENU -->
	<!--<div class="actions no-padding">-->
		<ul class="nav nav-tabs">
			<li class='<?=($this->request->params['action'] == 'view') ? 'active' : '' ?>'><a href="/anime/view/<?=$id . '/' . $title?>">Summary</a></li>
<?php
if($type != 'movie')			
	echo "<li class='".(($this->request->params['action'] == 'viewepisodes') ? 'active' : '')."'><a href='/anime/viewepisodes/".$id . "/" . $title."'>Episodes</a></li>";
if($isAdmin)
	echo "<li class='".(($this->request->params['action'] == 'viewref') ? 'active' : '')."'><a href='/anime/viewref/".$id . "/" . $title. "'>References</a></li>";
?>			
			<li class='<?=($this->request->params['action'] == 'viewtags') ? 'active' : '' ?>'><a href="/anime/viewtags/<?=$id . '/' . $title?>">Tags/Genres</a></li>
		</ul>
