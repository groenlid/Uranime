<div class="btn-toolbar pull-right">
<div class="btn-group">
	<button class="btn">Sort by</button>
	<a class="btn dropdown-toggle" href="#" data-toggle="dropdown">
		<span class="caret"></span>
	</a>
	<ul class="dropdown-menu">
		<li><a href="/anime/all/">Title</a></li>
		<li><a href="/anime/all/age">Entry Age</a></li>
	</ul>
</div>
</div>
<?php
	echo "<h2>View all anime</h2><hr>";

echo "<div id='anime-gallery'>";
foreach($animes as $anime)
{
	$fanart = $anime['Anime']['fanart'];
	if($fanart == null || $fanart == "")
		$fanart = "http://placehold.it/200x112";
	else
		$fanart = SERVER_PATH . IMAGE_PATH.$fanart;

	// Make it a proper size
	$fanart = "http://src.sencha.io/200/". $fanart;
	echo "
		<div class='anime-gallery-single'>
			<div class='anime-gallery-single-inner'>";
				echo $this->Html->link($this->Html->image($fanart,array('style' => 'height:112px;width:200px;')), '/anime/view/'.$anime['Anime']['id'], array('escape' => false));
				echo "<span class='anime-gallery-single-hover'>".$this->Html->link("View Anime",'/anime/view/'.$anime['Anime']['id'])."</span>
			</div>
			<span class='anime-gallery-single-name'>" . $anime['Anime']['title'] . "</span>
		</div>
	";
}
echo "</div>";
?> <br class="clear">

	<div class="pagination pageepisodes">
		<?php echo $this->Paginator->prev('Previous', null, null, array('class' => 'disabled')); ?>
		<?php echo $this->Paginator->numbers(array('separator'=> '')); ?>
		<?php echo $this->Paginator->next('Next', null, null, array('class' => 'disabled')); ?>
	</div> 
	
<br class="clear">
<div class="well">
<strong>WHAT?</strong> We don't have the anime you want? Why not <a href="/anime/add">add it</a> then?
</div>


