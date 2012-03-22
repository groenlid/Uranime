<div class="row">
<?php
	extract($anime['Anime']);
?>

<div class="span8">
	<h2><?= $title ?><span class="pull-right"><small><?=round($calc_rating['avg_rate'],2)?> <span class="subtle">( <?=$calc_rating['amount']?> votes )</small></span></span></h2>
<!-- ANIME MENU -->
	<ul class="nav nav-tabs">
		<li><a href="/anime/view/<?=$id . '/' . $title?>">Summary</a></li>
		<li><a href="/anime/viewepisodes/<?=$id . '/' . $title?>">Episodes</a></li>
		<li><a href="/anime/viewref/<?=$id . '/' . $title?>">References</a></li>
		<li><a href="/anime/viewtags/<?=$id . '/' . $title?>">Tags/Genres</a></li>
	</ul>
	<div class="row">
		<div class="span4"> 
		<?php
		if($image == null || $image == "")
			$image = "http://placehold.it/287x450/";
		else 
			$image = SERVER_PATH . IMAGE_PATH . $image;
		?>
		<img class="thumbnail" src="<?=$image?>" style="height:112px">
		<?php 
			echo $this->Form->create('Anime', array('type' => 'file', 'url' => '/anime/editImage/'.$id)); 
			echo $this->Form->input('image', array('type' => 'file','div' => false, 'label' => false,'class' => ''));
			echo '<div class="actions" style="padding-left:15px">';
			echo $this->Form->submit('Upload new Poster', array('class' => 'btn btn-primary','label' => false));
			echo '</div>';
			echo $this->Form->end();
			?>
		</div>
		<div class="span4">
		
		<?php
		if($fanart == null || $fanart == "")
			$fanart = "http://placehold.it/200x112/";
		else 
			$fanart = SERVER_PATH . IMAGE_PATH . $fanart;
		?>
		<img class="thumbnail" src="<?=$fanart?>" style="height:112px">
		<?php 
			echo $this->Form->create('Anime', array('type' => 'file', 'url' => '/anime/editImage/'.$id)); 
			echo $this->Form->input('fanart', array('type' => 'file','div' => false, 'label' => false,'class' => ''));
			echo '<div class="actions" style="padding-left:15px">';
			echo $this->Form->submit('Upload new fanart', array('class' => 'btn btn-primary','label' => false,'div' => false));
			echo '</div>';
			echo $this->Form->end();
			?>
		</div>
	</div>
	<div class="notif">
		<p><strong>Resolutions:</strong></p>
		<p>Poster: 287 x 450 (Follow myanimelist, anidb guidelines)</p>
		<p>Fanart: 1920 x 1080, 1280 x 720 (Follow the thetvdb guidelines)</p>
	</div>
</div>

<?php
include('leftside.ctp');
?>
</div>
