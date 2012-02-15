<?php
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
			<li class="active"><a href="/anime/view/<?=$id . '/' . $title?>">Summary</a></li>
			<li><a href="/anime/viewepisodes/<?=$id . '/' . $title?>">Episodes</a></li>
			<li><a href="/anime/viewref/<?=$id . '/' . $title?>">References</a></li>
			<li><a href="/anime/viewtags/<?=$id . '/' . $title?>">Tags/Genres</a></li>
		</ul>
	<!--</div>-->
	<blockquote>
		<p class="animedesc">
			<?= $desc ?>
		</p>
	</blockquote>

	
	<?php
	if($this->Session->check('Auth.User.id'))
	{
		echo '<p class="subtle big">Your watched progress</p>';
		// First find how many episodes is already out
		$out_now = 0;
		$user_seen = 0;
		$next_episode = null;
		foreach(array_reverse($anime['Episode']) as $episode){
			$found = false;
			if(strtotime($episode['aired']) < time())
			{
				$out_now++;
				// Find the next unseen user episode
				foreach($userepisodes as $uep)
					if($uep['UserEpisode']['episode_id'] == $episode['id'])
					{
						$user_seen++;
						$found = true;
						break;
					}
				if(!$found)
					$next_episode = $episode;
			}else{
				foreach($userepisodes as $uep)
					if($uep['UserEpisode']['episode_id'] == $episode['id'])
						{
							$found = true;
							break;
						}
				if(!$found)
					$next_episode = $episode;
			}
		}

		if($user_seen > $out_now)
			$user_seen = $out_now;
		
		if($out_now != 0)
			$per = $user_seen / $out_now * 100;
		else
			$per = 0;
		echo "<div class='progressbar'><div id='progress' style='width:".$per."%;'></div></div>";

		echo "<p>You have watched <strong>". $user_seen . "</strong> out of <strong>" . $out_now . "</strong> episodes.</p>";

		if($fanart == null || $fanart == "")
			$fanart = "http://placehold.it/117x66";
		else
			$fanart = SERVER_PATH . IMAGE_PATH . $fanart;
		
		if($next_episode != null)
			echo "<p class='subtle big'>Next unseen episode:</p><div class='episode'>
				<span class='episodeImage'>
					<img src='http://src.sencha.io/117/".$fanart."'>
				</span>
				<span class='episodeContent'>
					<span class='episodeName'>"
						.$next_episode['name']."
					</span>
					<span class='episodeTime'>
						Episode ".$next_episode['number']."
						".((strtotime($next_episode['aired']) < time()) ? 'aired ' : 'airs ') . $next_episode['aired'] .
					"</span>
				</span>
			
			</div><br class='clear'>";
	}
	?>

<p class="subtle big">Last 10 episodes</p>
	<table id="searchTable" class="table table-bordered table-striped table-condensed small-text">
	<thead>
	<tr>
		<td>#</td>
		<td>Episode Title</td>
		<td>Air-date</td>
	</tr>
	</thead>
	<tbody>
<?php
$i = 0;

	foreach(array_reverse($anime['Episode']) as $episode)
	{
	if($i >= 10)
		break;
		if(strtotime($episode['aired']) > time())
			continue;
			echo "
				<tr>
					<td class='episode-number'>".(($episode['special'] == '1') ? 'S' : $episode['number'])."</td>
					<td class='episode-name'><div style='position:relative'>".$episode['name'] . "</div></td>
					<td class='episode-aired'>".$episode['aired']."</td>
				</tr>
				";
		$i++;
	}
	?>
	</tbody>
	</table>
<?php
if(count($sequels) != 0 || count($prequels) != 0)
{
	echo '
	<p class="subtle big">Related Anime</p>
	<ul class="media-grid">
	';
}
?>

<?php
foreach($sequels as $sequel)
{
	$animeSeq = ($sequel['anime1']['id'] == $id) ? $sequel['anime2'] : $sequel['anime1'];

	$fanart = $animeSeq['fanart'];
	if($fanart == "" || $fanart == null)
		$fanart = "http://placehold.it/200x112/";
	else
		$fanart = SERVER_PATH . IMAGE_PATH . $fanart;

	echo '
		<li>
			<a href="/anime/view/'.$animeSeq['id'].'/'.$animeSeq['title'].'">
				<img src="http://src.sencha.io/200/'.$fanart.'">
				<span class="anime-gallery-single-name">
					'.$animeSeq['title'].'
				</span>
				</a>
		</li>';	
}
foreach($prequels as $prequel)
{
	$animePreq = ($prequel['anime1']['id'] == $id) ? $prequel['anime2'] : $prequel['anime1'];
	
	$fanart = $animePreq['fanart'];
	if($fanart == "" || $fanart == null)
		$fanart = "http://placehold.it/200x112/";
	else
		$fanart = SERVER_PATH . IMAGE_PATH . $fanart;

	echo '
		<li>
			<a href="/anime/view/'.$animePreq['id'].'/'.$animePreq['title'].'">
				<img src="http://src.sencha.io/200/'.$fanart.'">
				<span class="anime-gallery-single-name">
					'.$animePreq['title'].'
				</span>
			</a>
		</li>';	
}
if(count($sequels) != 0 || count($prequels) != 0)
{
	echo '
	<br class="clear">
	</ul>
	';
}
?>
<br class="clear">
<?php
	foreach($animeActivities as $activity)
	{
		
	//	print_r($activity);
	}
?>
</div>

</div>