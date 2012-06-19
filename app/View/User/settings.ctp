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
		    	<?= 
					$this->Form->create(false,array('url' => '/user/settings')) .
					$this->Form->input('current_password', array('type' => 'password')) .
					$this->Form->input('new_password', array('type' => 'password')) .
					$this->Form->input('confirm_password', array('type' => 'password')) .
					$this->Form->submit('Change password',array('class'=>'btn','id'=> 'passwordchange')) .
					$this->Form->end();
		      	?>
		    </div>
		  </fieldset>
		  <fieldset>
			  <legend>Change profile description</legend>
			<div>
				<?=
					$this->Form->create(false, array('url' => '/user/settings')) .
					$this->Form->input('desc',array('default' => $desc)) .
					$this->Form->submit('Change description', array('class' => 'btn','id'=> 'descriptionchange')) .
					$this->Form->end();
				?>
			</div>
		  </fieldset>
	<fieldset>
		<legend>Import anime from myanimelist.net</legend>
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
