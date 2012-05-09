<div class="row">
<?php
	extract($anime['Anime']);
?>

<div class="span8">
<?php
	include('sub_menu.php');
?>
		<h2>Automatic image retrieving</h2>
	<div class="actions"><center>
	<?php
	foreach($anime['ScrapeInfo'] as $scrapeInfo){
		echo $this->Html->link('<i class="icon-search"></i> Search '.$scrapeInfo['scrape_source'], $this->Html->url('/anime/searchImage/'.$scrapeInfo['scrape_source'].'/'.$anime['Anime']['id'], false), array('class' => 'ajax btn','escape' => false)) . " ";
	}
	?></center>
	</div>
	<hr>
	<div class="ajaxresults">
	</div>
	<br class="clear">
	<hr>
	<div class="row">
		<a href="#" id="showHidden" class="pull-right">Show the existing image/poster</a><br class="clear">
		<div class="span4 hidden"> 

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
		<div class="span4 hidden">
		
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
	<div class="notif hidden">
		<p><strong>Resolutions:</strong></p>
		<p>Poster: 287 x 450 (Follow myanimelist, anidb guidelines)</p>
		<p>Fanart: 1920 x 1080, 1280 x 720 (Follow the thetvdb guidelines)</p>
	</div>
	

</div>

<?php
include('leftside.ctp');
?>
</div>
