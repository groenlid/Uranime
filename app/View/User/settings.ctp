<?php
	extract($user['User']);
	
?>
<div class="row">
<div class="span2">
	<div>
		<?= $this->Gravatar->image($email, array('class' => 'animeimage', 'size' => '150', 'rating' => 'r')) ?>
	</div>
</div>
<div class="span7">
	<h2>Settings</h2>
	
		  <fieldset>
		    <legend>Change password</legend>
		    <div>
		    	<?php echo $this->Form->create(false,array('url' => '/user/settings'));
		    	echo $this->Form->input('current_password', array('type' => 'password'));
		    	echo $this->Form->input('new_password', array('type' => 'password'));
		    	echo $this->Form->input('confirm_password', array('type' => 'password'));
		    	echo $this->Form->submit();
		      	echo $this->Form->end();?>
		    </div>
		  </fieldset>
	
</div>
</div>
