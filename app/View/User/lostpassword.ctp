<h3>Don't worry. I'm from the internet</h3>
<?php

echo $this->Form->create(null, array('type' => 'post','urk' => '/user/forgotpassword/','class' => 'stylish'));
echo $this->Form->input('email');
echo $this->Form->submit('Request new password',array('class' => 'button'));
echo $this->Form->end();
?>
<div class="notif">
<ol>
	<li>Enter your email in the email text field and click "send password reset"</li>
	<li>You will get an email containing a link that you need to click to generate a new password.</li>
	<li>A new password has been generated and the new password is sendt in an email to you.</li>
</ol>
</div>