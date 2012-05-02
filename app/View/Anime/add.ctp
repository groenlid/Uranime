<ul class="progressionSteps">
</ul>
<?php
if($this->Session->check('Auth.User.id') && $this->Session->read('Auth.User.id') == '1')
{
	echo '<h1>Add a new anime</h1>';
	echo $this->Form->create(null, array('url' => '/anime/add/', 'class' => 'form-stacked'));
	//('cur', 'com', 'dro', 'pla', 'hol')

	echo $this->Form->input('Anime.title', array('label' => array('text' => 'Name of anime'),'class' => 'xlarge'));
	echo $this->Form->input('Anime.desc',  array('label' => array('text' => 'Description (optional)'),'class' => 'xxlarge'));

	$submitValue = "Add anime";
	echo "<div class='form-actions'>";
	echo $this->Form->submit($submitValue, array('div' => false, 'class' => 'btn primary')) . " ";
	echo $this->Html->link("Cancel","/anime", array('class' => 'btn'));
	echo "</div>";
	echo $this->Form->end();
} else if($this->Session->check('Auth.User.id')){
	echo '<h1>Request new anime</h1>';
	echo $this->Form->create(null, array('url' => '/anime/add/', 'class' => 'form-stacked'));
	//('cur', 'com', 'dro', 'pla', 'hol')

	echo $this->Form->input('AnimeRequest.title', array('label' => array('text' => 'Name of anime'),'class' => 'xlarge'));
	echo $this->Form->input('AnimeRequest.comment',  array('label' => array('text' => 'Additional comment (optional)'),'class' => 'xxlarge'));
	
	$submitValue = "Send Request";
	echo "<div class='form-actions'>";
	echo $this->Form->submit($submitValue, array('div' => false,'class' => 'btn btn-primary')) . " ";
	echo $this->Html->link("Cancel","/anime", array('class' => 'btn'));
	echo "</div>";
	echo $this->Form->end();
}
?>
