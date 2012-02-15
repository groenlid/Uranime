<?php
	//echo $this->Session->flash('auth');
	echo $this->Form->create('User', array('class' => 'well','url' => array('controller' => 'user', 'action' => 'login')));
	echo $this->Form->input('User.email', array('class' => 'span3', 'placeholder' => 'Username'));
	echo $this->Form->input('User.password');

	echo $this->Form->end('Login');
	echo $this->Html->link('Forgot password','/user/lostpassword/',array('class' => 'red', 'id' => 'left'));
?>
