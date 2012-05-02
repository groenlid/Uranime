<div class="row">
<?php
	extract($anime['Anime']);
?>
<?php
include('leftside.ctp');
?>
<div class="span8">
	<h2><?= $title ?><span class="pull-right"><small><?=round($calc_rating['avg_rate'],2)?> <span class="subtle">( <?=$calc_rating['amount']?> votes )</small></span></span></h2>
	<!-- ANIME MENU -->
	<ul class="nav nav-tabs">
		<li><a href="/anime/view/<?=$id . '/' . $title?>">Summary</a></li>
		<li class="active"><a href="/anime/viewepisodes/<?=$id . '/' . $title?>">Episodes</a></li>
		<li><a href="/anime/viewref/<?=$id . '/' . $title?>">References</a></li>
		<li><a href="/anime/viewtags/<?=$id . '/' . $title?>">Tags/Genres</a></li>
	</ul>
	<div class="actions">
		<a href="/episode/watchall/<?=$id?>" class="btn primary">Seen all up to date</a>
	</div>
	<div class="pagination pageepisodes">
		<?php echo $this->Paginator->prev('Previous', null, null, array('class' => 'disabled')); ?>
		<?php echo $this->Paginator->numbers(array('separator'=> '')); ?>
		<?php echo $this->Paginator->next('Next', null, null, array('class' => 'disabled')); ?>
	</div> 
	<br class="clear">
	<?php
	foreach($episodes as $episode)
	{
		$fanart = $episode['Anime']['fanart'];
		if($fanart == "" || $fanart == null)
			$fanart = "http://placehold.it/200x112/";
		else
			$fanart = SERVER_PATH . IMAGE_PATH . $fanart;
		

		/*if($animeuser['AnimelistEntry']['ep_seen'] >= $episode['Episode']['number'])
			echo "
				<tr>
					<td>".(($episode['Episode']['special'] == '1') ? 'S' : $episode['Episode']['number'])."</td>
					<td class='line-through'>".$episode['Episode']['name']."</td>
					<td>".$episode['Episode']['aired']."</td>
				</tr>
			";
		else*/
		$seen = null;
		if($this->Session->check('Auth.User.id'))
			foreach($animeuser as $entry){
				if($entry['UserEpisode']['episode_id'] == $episode['Episode']['id'])
					$seen = $entry['UserEpisode']['timestamp'];
			}
		echo "
			<div class='episode'>
				<span class='checkIt'></span>
				<span class='episodeNumber'><h2>".$episode['Episode']['number']."</h2></span>
				<span class='episodeContent'>
					<div><span class='episodeName'>".$this->Html->link($this->Text->truncate($episode['Episode']['name'],50),"/episode/view/".$episode['Episode']['id'])."</span><span class='episodeTime'>".date('l, d, M',strtotime($episode['Episode']['aired']))."</span></div>
					<p>".$this->Text->truncate($episode['Episode']['description'],200)."</p>
				</span>
				<span class='extra'>
				";
				
				// Checkbox
				if($this->Session->check('Auth.User.id'))
				{
					if($seen != null)
						echo "<a class='episodestatus' id='".$episode['Episode']['id']."' href='/episode/unwatch/".$episode['Episode']['id']."'><img id='".$episode['Episode']['id']."' src='/img/checked.png'></a><span class='tooltip'>Watched on ".date('d. M - Y',strtotime($seen))."</span>";
					else
						echo "<a class='episodestatus' id='".$episode['Episode']['id']."' href='/episode/watch/".$episode['Episode']['id']."'><img id='".$episode['Episode']['id']."' src='/img/unchecked.png'></a>";
				}
				echo "	
				</span>
			</div>
		";
		$episodeEdit 	= ($this->Session->check('Auth.User.id') && $this->Session->check('Auth.User.id') == 1) 
						? '<span class="episode-edit"><a href="">Edit</a> | '.
						$this->Html->link('Delete',
							array(
								"controller" => "anime", 
								"action" => "deleteEpisode/",
								$episode['Episode']['id']
								),
							array(),
							"Are you sure you want to delete this episode?"
							).'</span>' 
						: '';
			/*echo "
				<tr>
					<td class='episode-number'>".(($episode['Episode']['special'] == '1') ? 'S' : $episode['Episode']['number'])."</td>
					<td class='episode-name'><div style='position:relative'>".$episode['Episode']['name'] . $episodeEdit . "</div></td>
					<td class='episode-aired'>".$episode['Episode']['aired']."</td>
				</tr>
			";*/
	}
	?>

	<hr />
	<br class="clear">

</div>
</div>