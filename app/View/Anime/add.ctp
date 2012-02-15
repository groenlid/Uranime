<ul class="progressionSteps">
</ul>
<?php
if($this->Session->check('Auth.User.id') && $this->Session->check('Auth.User.id') == '1')
{
	echo '<h1>Add a new anime</h1>';
	echo $this->Form->create(null, array('url' => '/anime/add/', 'class' => 'form-stacked'));
	//('cur', 'com', 'dro', 'pla', 'hol')

	echo $this->Form->input('Anime.title', array('label' => array('text' => 'Name of anime'),'class' => 'xlarge'));
	echo $this->Form->input('Anime.desc',  array('label' => array('text' => 'Description (optional)'),'class' => 'xxlarge'));
	//echo $this->Form->input('Anime.image',  array('label' => array('class' => 'block', 'text' => 'Image URL')));
	//echo $this->Form->input('Anime.fanart',  array('label' => array('class' => 'block', 'text' => 'Fanart URL')));
	
	//echo $this->Form->input('image', array('type' => 'file'));
	//echo $this->Form->input('fanart', array('type' => 'file'));

	$submitValue = "Add anime";
	echo "<div class='actions'>";
	echo $this->Form->submit($submitValue, array('class' => 'btn primary')) . " ";
	echo $this->Html->link("Cancel","/anime", array('class' => 'btn'));
	echo "</div>";
	echo $this->Form->end();
}
?>
