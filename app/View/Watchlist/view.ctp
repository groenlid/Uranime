<h2><?=ucfirst($nick['User']['nick'])?>'s Watchlist</h2>
<div class="btn-toolbar">
<div class="btn-group">
	<button class="btn">Sort by</button>
	<a class="btn dropdown-toggle" href="#" data-toggle="dropdown">
		<span class="caret"></span>
	</a>
	<ul class="dropdown-menu">
		<li><a href="/watchlist/view/<?=$nick['User']['id']?>">Entry age</a></li>
		<li><a href="/watchlist/view/<?=$nick['User']['id']?>/title">Title</a></li>
	</ul>
</div>
</div>
<hr>
<?php
if($watchlist == null)
{
echo "<div class='alert alert-info'>This user does not have any anime in his/her's watchfolder</div>";
}
?>

<div class="anime-gallery-poster">
<?php
$i = 0;
foreach($watchlist as $animeSingle)
{
	$image = $animeSingle['Anime']['image'];
	if($image == null || $image == "")
		$image = "http://placehold.it/225x112";
	else
		$image = IMAGE_PATH.$image;

	echo "
	<div class='anime-gallery-single'>
		<div class='anime-gallery-single-inner'>"
		.$this->Html->link(
			$this->Html->image($image),
			'/anime/view/'.$animeSingle['Anime']['id'].'/'.$animeSingle['Anime']['title'],
			array('escape' => false)
		)."
		</div>
		<span class='anime-gallery-single-name'>".$this->Text->truncate($animeSingle['Anime']['title'],20)."</span>
	</div>";
	$i++;
}
?>
<br class="clear">
</div>
