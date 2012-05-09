<?php
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
	<!--</div>-->
	<blockquote>
		<p class="animedesc">
			<?= $desc ?>
		</p>
	</blockquote>

	
	<?php
	if($this->Session->check('Auth.User.id'))
	{
		echo '<!--<p class="subtle big">Your watched progress</p>-->';
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
		
		$status = ($per < 30) 
			? "<span><strong>". $user_seen . "</strong> of <strong>" . $out_now . "</strong> seen</span>"
			: "<span>Watched <strong>". $user_seen . "</strong> out of <strong>" . $out_now . "</strong> episodes</span>";
		
		echo "<div class='progressbar'><div id='progress' style='width:".$per."%;'>".$status."</div></div>";

		//echo "<p>Watched <strong>". $user_seen . "</strong> out of <strong>" . $out_now . "</strong> episodes.</p>";

		if($fanart == null || $fanart == "")
			$fanart = "http://placehold.it/117x66";
		else
			$fanart = SERVER_PATH . IMAGE_PATH . $fanart;
		
		if($type == 'movie'){
			$episode = $anime['Episode'][0];
			echo "<div class='episode'>
				<span class='episodeImage'>
					".$this->Html->link("<img src='http://src.sencha.io/117/".$fanart."'>",'/episode/view/'.$episode['id'],array('escape' => false))."
				</span>
				<span class='episodeContent'>
					<span class='episodeName'>"
						.$this->Html->link($this->Text->truncate($next_episode['name'],45),'/episode/view/'.$next_episode['id'])."
					</span>";
			if($next_episode != null)
				echo "<span class='episodeTime'>
						<a href='/episode/watch/".$episode['id']."' class='btn btn-primary'>Mark as watched</a>	
						</span>";
			else {
				echo "<span class='episodeTime'>
						<a href='/episode/unwatch/".$episode['id']."' class='btn btn-warning'>Mark as unseen</a>	
						</span>";
			}
			echo "
				</span>
			</div><br class='clear'>";
		}
		else if($next_episode != null){
			echo "<!--<p class='subtle big'>Next unseen episode:</p>--><div class='episode'>
				<span class='episodeImage'>
					".$this->Html->link("<img src='http://src.sencha.io/117/".$fanart."'>",'/episode/view/'.$next_episode['id'],array('escape' => false))."
				</span>
				<span class='episodeContent'>
					<span class='episodeName'>"
						.$this->Html->link($this->Text->truncate($next_episode['name'],45),'/episode/view/'.$next_episode['id'])."
					</span>";
				echo "<span class='episodeTime'>
							Episode ".$next_episode['number']."
							".((strtotime($next_episode['aired']) < time()) ? 'aired ' : 'airs ') . $next_episode['aired'] .
						"</span>";
			echo "
				</span>
			
			</div><br class='clear'>";
		}
	}
	?>
<!--
<p class="subtle big">Last 10 episodes</p>
	<table id="searchTable" class="table table-bordered table-striped table-condensed small-text">
	<thead>
	<tr>
		<td>#</td>
		<td>Episode Title</td>
		<td>Air-date</td>
	</tr>
	</thead>
	<tbody>-->
<?php
/*
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
	}*/
	?><!--
	</tbody>
	</table>-->
<?php

if(isset($ep_seen) && count($ep_seen) != 0)
	echo '<hr>
	<p class="subtle big">Seen last by:</p>
	<ul class="thumbnails">';

foreach($ep_seen as $animeEp)
{
	//debug($animeEp);
	echo '
	<li class="span1">
		<a class="thumbnail" rel="tooltip" title="has seen '.$animeEp[0]['amount'].' episodes" href="/user/view/'.$animeEp['UserEpisode']['user_id'].'">
			'.$this->Gravatar->image($animeEp['User']['email'], array('size' => '100', 'rating' => 'r')).'
		</a>
	</li>
	';
}
if(isset($ep_seen) && count($ep_seen) != 0)
	echo '</ul>';
?>
<hr>
<?php
if(count($relationships) != 0)
{
	echo '
	<p class="subtle big">Related Anime</p>
	<div id="anime-gallery">
	';
}
?>

<?php
//debug($relationships);
foreach($relationships as $relationship)
{
	$left = false;
	$animeCurrent = $relationship['anime1'];
	if($relationship['anime1']['id'] == $id)
	{
		$left = true;
		$animeCurrent = $relationship['anime2'];
	}
	
	$linkText = "";
	
	if($relationship['AnimeRelationship']['type'] == 'sequel')
	{
		if($left)
			$linkText = "View Prequel";
		else 
			$linkText = "View Sequel";
	}else if($relationship['AnimeRelationship']['type'] == 'side-story')
	{
		if($left)
			$linkText = "View Parent-Story";
		else
			$linkText = "View Side-Story";
	}
	
	$fanart = $animeCurrent['fanart'];
	if($fanart == "" || $fanart == null)
		$fanart = "http://placehold.it/200x112/";
	else
		$fanart = SERVER_PATH . IMAGE_PATH . $fanart;

	echo '
		<div class="anime-gallery-single">
			<div class="anime-gallery-single-inner">
				<a href="/anime/view/'.$animeCurrent['id'].'/'.$animeCurrent['title'].'" class="">
					<img src="http://src.sencha.io/200/'.$fanart.'">
				</a>
				<span class="anime-gallery-single-hover">
					<a href="/anime/view/'.$animeCurrent['id'].'/'.$animeCurrent['title'].'" class="">'.$linkText.'</a>
				</span>
			</div>
			<span class="anime-gallery-single-name">
			'.$animeCurrent['title'].'
			</span>
		</div>';	
}
/*foreach($relationship_anime2 as $prequel)
{
	$animePreq = ($prequel['anime1']['id'] == $id) ? $prequel['anime2'] : $prequel['anime1'];
	
	$fanart = $animePreq['fanart'];
	if($fanart == "" || $fanart == null)
		$fanart = "http://placehold.it/200x112/";
	else
		$fanart = SERVER_PATH . IMAGE_PATH . $fanart;

	echo '<div class="anime-gallery-single">
			<div class="anime-gallery-single-inner">
				<a href="/anime/view/'.$animePreq['id'].'/'.$animePreq['title'].'" class="">
					<img src="http://src.sencha.io/200/'.$fanart.'">
				</a>
				<span class="anime-gallery-single-hover">
					<a href="/anime/view/'.$animePreq['id'].'/'.$animePreq['title'].'" class="">View Prequel</a>
				</span>
			</div>
			<span class="anime-gallery-single-name">
			'.$animePreq['title'].'
			</span>
		</div>';	
}*/
if(count($relationships) != 0)
{
	echo '
	<br class="clear">
	</div>
	<hr>
	';
}
?>
<br class="clear">
<div id="newsfeed">

<?php

if($this->Session->check('Auth.User.id'))
{
	echo '
	<div class="comment-container">
		<div class="comment-avatar">
		'.
		$this->Html->link(
						$this->Gravatar->image(
							$user_email, 
							array('class' => 'animeimage', 'size' => '30', 'rating' => 'pg')
						),
						'/user/view/'.$user_id,
						array('escape' => false)
					)
		.'
		</div>
		<div class="comment"><form style="margin:0;padding:0" action="/comment/add/'.$id.'" method="post">
			<div class="comment-meta">Write a new comment <span class="comment-time"><input type="submit" class="btn" value="Comment"></span></div>
			<div class="comment-text"><textarea name="comment"></textarea></div>
		</div></form>
	</div>
	';
}
?>

<?php
	foreach($activities as $activity)
	{
			echo $this->element('activity', array(
    			"activity" => $activity,
    			"kindOfObject" => "anime"
    		));
		//print_r($activity);
	}
	
	
?>
</div>
</div>

</div>
