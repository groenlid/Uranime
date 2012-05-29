<h2><?=$request['AnimeRequest']['title']?><span class="pull-right"><small>[request #<?=$request['AnimeRequest']['id']?> from <?=$request['user']['nick']?>]</small></span></h2>
<div class="row">
	<div class="span8">
		<div class="alert alert-info">
			<?=(empty($request['AnimeRequest']['comment'])?'No comment': $request['AnimeRequest']['comment'])?>
		</div>
		<?php
		if($isAdmin){
			echo $this->html->link(
				'<i class="icon-ok"></i> Accept',
				'/animeRequest/decide/'.$request['AnimeRequest']['id'].'/true',
				array('escape' => false,'class' => 'btn btn-primary span2')
				). '';
			echo $this->html->link(
				'<i class="icon-edit"></i> Show edit form',
				'#',
				array('id' => 'showHidden','escape' => false,'class' => 'btn btn-success span2')
				). ' ';
			echo $this->html->link(
				'<i class="icon-remove"></i> Remove',
				'/animeRequest/decide/'.$request['AnimeRequest']['id'].'/false',
				array('escape' => false, 'class' => 'btn btn-danger span2')
				);
		}
		?>
		<br class="clear">

<?php
if($isAdmin){
echo $this->Form->create("AnimeRequest",array('class' => 'hidden'));
echo $this->Form->input("AnimeRequest.title",array());
echo $this->Form->input("AnimeRequest.comment");
echo "<div class='form-actions'>";
echo $this->Form->submit("Edit request", array('div' => false,'class' => 'btn btn-primary'));
echo $this->html->link(
				'Cancel',
				'/animeRequest/view/'.$request['AnimeRequest']['id'],
				array('escape' => false, 'class' => 'btn btn-danger span2 pull-right')
				);
echo "</div>";
echo $this->Form->end(null);
}
?>

	</div>
	<div class="span3 pull-right">
		<div>
			<?= $this->html->link(
				$this->Gravatar->image($request['user']['email'], array('size' => '150', 'rating' => 'pg'),array('class' => 'animeimage')),
				'/user/view/'.$request['user']['id'].'/'.$request['user']['nick'],
				array('escape' => false,'class' => 'pull-right')
			) ?>
		</div>
		
		<br class="clear">
		<hr>	
		<div class="span3">

		</div>
	</div>
</div>