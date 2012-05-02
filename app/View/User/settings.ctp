<?php
	extract($user['User']);
	
?>
<div class="row">
<div class="span3">
	<div>
		<?= $this->Gravatar->image($email, array('size' => '150', 'rating' => 'r'),array('class' => 'animeimage')) ?>
	</div>
</div>
<div class="span8">
	<h2>Settings</h2>
	
		  <fieldset>
		    <legend>Change password</legend>
		    <div>
		    	<?php echo $this->Form->create(false,array('url' => '/user/settings'));
		    	echo $this->Form->input('current_password', array('type' => 'password'));
		    	echo $this->Form->input('new_password', array('type' => 'password'));
		    	echo $this->Form->input('confirm_password', array('type' => 'password'));
		    	echo $this->Form->submit('Change password',array('class'=>'btn'));
		      	echo $this->Form->end();?>
		    </div>
		  </fieldset>
	<fieldset>
		<legend>Import account from myanimelist</legend>
		<div>
			<?php 
				echo $this->Form->create(false,array('url' => '/mal/checkUser/'));
		    	echo $this->Form->input('Myanimelist_Username', array('type' => 'text'));
		    	echo $this->Form->submit('Check username',array('class'=>'btn'));
		      	echo $this->Form->end();?>
		</div>
	</fieldset>
</div>
</div>
