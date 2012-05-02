<h2><?=ucfirst($nick['User']['nick'])?>'s Library</h2>
<div class="btn-toolbar">
<div class="btn-group">
	<button class="btn">Sort by</button>
	<a class="btn dropdown-toggle" href="#" data-toggle="dropdown">
		<span class="caret"></span>
	</a>
	<ul class="dropdown-menu">
		<li><a href="/library/view/<?=$nick['User']['id']?>">Entry age</a></li>
		<li><a href="/library/view/<?=$nick['User']['id']?>/title">Title</a></li>
	</ul>
</div>
</div>
<hr>
<?php
if($animes == null || !isset($animes))
{
echo "<div class='alert alert-info'>This user does not have any anime in his/her's library</div>";
}
?>
<div class="anime-gallery-poster">
<?php
//debug($anime);
$i = 0;
foreach($anime as $animeSingle)
{
	$image = $animeSingle['Anime']['image'];
	if($image == null || $image == "")
		$image = "http://placehold.it/225x112";
	else
	 	$image = "http://src.sencha.io/200/".SERVER_PATH.IMAGE_PATH.$image;
	echo "
	<div class='anime-gallery-single'>
		<div class='anime-gallery-single-inner'>"
		.$this->Html->link(
			$this->Html->image($image),
			'/anime/view/'.$animeSingle['Anime']['id'].'/'.$animeSingle['Anime']['title'],
			array('escape' => false)
		)."
		</div>
		<span class='anime-gallery-single-name'>".$this->Text->truncate($animeSingle['Anime']['title'],20).' ('. $stats[$i][0]['count']. ")</span>
	</div>";
	$i++;
}
?>
<br class="clear">
</div>

<?php
/*
$all_status = array(
	'all' => 'All',
	'cur' => 'Currently Watching',
	'com' => 'Completed',
	'hol' => 'On-Hold',
	'dro' => 'Dropped',
	'pla' => 'Planned to Watch'
);
?>

<h2><?=$user['User']['nick']?>'s Library</h2>
<ul class="menu">
<?php
foreach($all_status as $singleStatus => $nice){
	$active = "";
	if($singleStatus == $status)
		$active = " class='active'";
	echo "<li".$active.">";
	echo $html->link($nice,'/library/view/'.$user['User']['id'].'/'.$singleStatus.'/'.$user['User']['nick']);
	echo "</li>";
}
?>
</ul>
<table id="searchTable" class="library">
<thead>
<?php
echo $html->tableHeaders(array('Title','Rating','Progress','Updated'));
?>
</thead>
<tbody>
<?php
foreach($animelist as $anime)
//if($anime['AnimelistEntry']['status'] == 'cur')
	echo $html->tableCells(
		array(
			$html->link($anime['Anime']['title'],'/anime/view/'.$anime['Anime']['id'].'/'.$anime['Anime']['title']),
			$this->Form->create(null, array('class' => 'libraryUpdate', 'url' => '/library/update/'.$anime['AnimelistEntry']['anime_id'])).$this->Form->input('AnimelistEntry.score', array(
				'default' => $anime['AnimelistEntry']['score'], 
				'label' => false, 
				'id' => $anime['Anime']['id'], 
				'class' => 'scoreinput')).
			"<span class='scorevalue' id='".$anime['Anime']['id']."'>".$anime['AnimelistEntry']['score']."</span>".$this->Form->end(),
			$this->Form->create(null, array('class' => 'libraryUpdate', 'url' => '/library/update/'.$anime['AnimelistEntry']['anime_id'])).$this->Form->input('AnimelistEntry.ep_seen', array(
				'default' => $anime['AnimelistEntry']['ep_seen'], 
				'label' => false, 
				'id' => $anime['Anime']['id'], 
				'class' => 'episodeinput')).
			"<span class='episodevalue' id='".$anime['Anime']['id']."'>".$anime['AnimelistEntry']['ep_seen']."</span>".$this->Form->submit('update', array('class' => 'hidden')).$this->Form->end(),
			$time->timeAgoInWords($anime['AnimelistEntry']['last_updated'])
			)
		);
?>
</tbody>
</table>
*/
?>
