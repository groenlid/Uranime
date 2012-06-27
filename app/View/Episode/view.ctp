<div class="row">
	<!--<div class="subnav">
		<ul class="nav">
			<li></li>
		</ul>
	</div>-->
<?php
	//debug($episode);
	//debug($neighbors);
	//extract($episode['Anime']);
?>
<?php
//include('../Anime/leftside.ctp');
?>
<div class="span8">
	<h2><?= $this->Html->link($episode['Anime']['title'] . " Episodes","/anime/viewepisodes/".$episode['Anime']['id']) ?> / Episode <?=$episode['Episode']['number']?></h2>
	<ul class="pager">
	<?php
	if(isset($neighbors['prev']))
	{
		echo '
			<li class="previous">
			    <a href="/episode/view/'.$neighbors['prev']['Episode']['id'].'">&larr; Episode '.$neighbors['prev']['Episode']['number'].'</a>
			</li>
		';
	}
	if(isset($neighbors['next']))
	{
		echo '
			<li class="next">
			    <a href="/episode/view/'.$neighbors['next']['Episode']['id'].'">&rarr; Episode '.$neighbors['next']['Episode']['number'].'</a>
			</li>
		';
	}
	?>
</ul>
<hr>
<h3><?=$episode['Episode']['name']?></h3>
<blockquote>
<?=($episode['Episode']['description'] == "") ? "No description available" : $episode['Episode']['description']?>
</blockquote>
<div style="height:40px"></div>
<h4>People who have seen this episode</h4><br>
<ul class="thumbnails">
<?php

foreach($episode['UserEpisode'] as $user)
	echo '
		<li class="span1">
			<a href="/user/view/'.$user['User']['id'].'" class="thumbnail">
				'.$this->Gravatar->image($user['User']['email'], array('size' => '100', 'rating' => 'r')).'
			</a>
		</li>
	';
?>
</ul>
<?php
if($this->Session->check('Auth.User.id') == 1)
{
	echo "<div class='well'>";
	echo $this->Html->link(
		'<i class="icon-trash icon-white"></i> Delete Episode',
		array('controller' => 'episode', 'action' => 'delete', $episode['Episode']['id']),
		array('class' => 'btn btn-danger','escape' => false),
		"Are you sure you wish to delete this episode?"
	);
	//echo '<a class="btn btn-danger"><i class="icon-trash icon-white"></i> Delete Episode</a>';
	echo '</div>';
}
?>

</div>
<div class="span3 pull-right">
<?php
if(file_exists(WWW_ROOT . EPISODE_IMAGE_PATH . $episode['Episode']['anime_id'] . "/" . $episode['Episode']['image']) && $episode['Episode']['image'] != null)
	echo '<img class="thumbnail" src="http://src.sencha.io/219/'.SERVER_PATH . EPISODE_IMAGE_PATH . $episode['Episode']['anime_id'] . "/" . $episode['Episode']['image'].'">';
else
	echo '<img class="thumbnail" src="http://src.sencha.io/219/'.SERVER_PATH . IMAGE_PATH . $episode['Anime']['fanart'].'">';
echo "<br>"; 
if($this->Session->check('Auth.User.id'))
{
	if(!$userepisode)
		echo '<a class="btn btn-primary btn-large span2" href="/episode/watch/'.$episode['Episode']['id'].'">Mark as seen</a>';
	else
		echo '<a class="btn btn-large btn-info span2" href="/episode/unwatch/'.$episode['Episode']['id'].'">Mark as unseen</a>';
}
?>
</div>
</div>
<div style="height:100px"></div>
