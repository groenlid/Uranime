<?php
if(AuthComponent::user('id'))
	include('loggedin.ctp');
else
	include('frontpage.ctp');
?>