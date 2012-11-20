
<!DOCTYPE html>
<html>
<head>
	<title><?= $title_for_layout ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<?= $scripts_for_layout ?>
	<?/*=$this->Html->css('style.css');*/?>
	<?=$this->Html->css('bootstrap.min.css');?>
	<?=$this->Html->css('lightbox.css');?>
	<?=''/*$this->Html->css('bootstrap-responsive.css');*/?>
	<?=$this->Html->css('bootstrap-com.css');?>
	<?=$this->Html->css('rickshaw.css');?>

	<link href='http://fonts.googleapis.com/css?family=Droid+Sans:regular,bold' rel='stylesheet' type='text/css' />
	<link href='http://fonts.googleapis.com/css?family=Questrial' rel='stylesheet' type='text/css'>
	<link rel="search" type="application/opensearchdescription+xml" href="/xml/search.xml" title="AnimeSandbox" />
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>

	<?=$this->Html->script('bootstrap.js');?>
	<?=$this->Html->script('jquery-ui-1.8.12.custom.min.js');?>
	<?=$this->Html->script('jquery.tablesorter.min.js');?>
	<?=$this->Html->script('jquery.tablesorter.pager.js');?>
	<?=$this->Html->script('jquery.toggleEdit.min');?>
	<?=$this->Html->script('functions.js');?>
	<?=$this->Html->script('jquery.form.js');?>
	<?=$this->Html->script('d3.min.js');?>
	<?=$this->Html->script('d3.layout.min.js');?>
	<?=$this->Html->script('rickshaw.js');?>
	<?=$this->Html->script('moment.min.js');?>
	<?=$this->Html->script('jquery.smooth-scroll.min.js');?>
	<?=$this->Html->script('lightbox.js');?>
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-31318797-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
</head>
<body>
	<div class="navbar navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <a class="brand" href="/">Urani.me</a>
          
          <form action="/search/redir/" id="search" class="navbar-search pull-left" method="post">
			<input type="text" class="search-query" name="mainsearch" value="Search for anime" id="mainsearch" autocomplete="off" onkeyup="searchy(this.value)" onclick='this.value=""'>
			
			<div class="popover below" id="results"></div>
          </form>
          <ul class="nav">
          	<li class='<?=($this->request->params['controller'] == 'front') ? 'active' : '' ?>'><?=$this->Html->link('Home','/')?></li>
          	<li class='dropdown <?=($this->request->params['controller'] == 'anime') ? 'active' : '' ?>'>
          		<a href="#" class="dropdown-toggle" data-toggle="dropdown">Anime<b class="caret"></b></a>
          		<ul class="dropdown-menu">
          			<li><?=$this->Html->link('Anime','/anime/')?></li>
          			<li><?=$this->Html->link('View all','/anime/all')?></li>
          			<li class="divider"></li>
          			<?php
          			if($this->Session->read('Auth.User.id') == '1')
          				echo "<li>".$this->Html->link('Add anime','/anime/add')."</li>";
          			else 
          				echo "<li>".$this->Html->link('Request anime','/anime/add')."</li>";
          			?>
          			
          		</ul>
          	</li>
          	<li class='<?=($this->request->params['controller'] == 'user' && $this->request->params['action'] == 'index') ? 'active' : '' ?>'><?=$this->Html->link('Community','/user/')?></li>
          	<?php
				if($this->Session->check('Auth.User.id'))
				{
					echo "
						<li class='".(($this->request->params['controller'] == 'calendar') ? 'active' : '') ."'>".
							$this->Html->link('Calendar','/calendar/view/')."
						</li>";
				}
			?>
        </ul>
        <ul class="nav pull-right">
          	<?php
				if($this->Session->check('Auth.User.id'))
                {
					if(isset($isAdmin) && $isAdmin){
						echo '<li>';
							echo $this->Html->link('Admin <span class="label label-success">'.$animerequestsCount.'</span>','/admin/',array('escape' => false));
						echo '</li>';
					}
					
					echo "
				<li class='dropdown'><a href='#' class='dropdown-toggle' data-toggle='dropdown'>".$this->Session->read('Auth.User.nick')."<b class='caret'></b></a>
					<ul class='dropdown-menu'>
						<li class='".(($this->request->params['controller'] == 'user' && isset($this->request->params['pass'][0]) && $this->request->params['pass'][0] == $this->Session->read('Auth.User.id')) ? 'active' : '' )."'>".
							$this->Html->link('Profile','/user/view/'.$this->Session->read('Auth.User.id').'/'.$this->Session->read('Auth.User.nick'))."
						</li>
						<li class='".(($this->request->params['controller'] == 'library') ? 'active' : '' )."'>".
							$this->Html->link('Library','/library/view/'.$this->Session->read('Auth.User.id').'/'.$this->Session->read('Auth.User.nick'))."
						</li>
						<li class='".(($this->request->params['controller'] == 'watchlist') ? 'active' : '' )."'>".
							$this->Html->link('Watchlist','/watchlist/view/'.$this->Session->read('Auth.User.id').'/'.$this->Session->read('Auth.User.nick'))."
						</li>
						<li>
							".$this->Html->link('Settings','/user/settings/')."
						</li>
						<li class='divider'></li>
						<li>".
							$this->Html->link('Logout','/user/logout')."
						</li>";
				echo "
					</ul>
				</li>
					";
				}
				else{
					echo "
				<li>".$this->Html->link('Register','/user/register')."</li>
				<li>".$this->Html->link('Login','/user/login')."</li>
					";
				}
			?>
         </ul> 

          <?php
          
			/*if(!$this->Session->check('Auth.User.id')){
				echo $this->Form->create('User', array(
					'url' => array(
						'controller' => 'user', 
						'action' => 'login'
						), 
					'id'=>'loginForm',
					'class' => 'form-inline',
					'inputDefaults' => array(
						'div' => false
					),
					)
				);
				//echo "<a href='#' id='closelogin'>Reset Password</a>";
				echo $this->Form->text('User.email', array('placeholder' => 'Username','class' => 'input-small')) . " ";
				echo $this->Form->password('User.password', array('placeholder' => 'Password','class' => 'input-small')). " ";
				echo $this->Form->button('Login',array(
						'class' => 'btn',
						'div' => false
					));
				echo $this->Form->end();
			}*/
          ?>
        </div>
      </div>
    </div>
    <div class="container">
    	<div class="content">
    		<?= $this->Session->flash() ?>
			<?= $content_for_layout ?>
	    	<footer>
	    		<div class="center">
					<div class="numberbox">
						<h2><?= $numberAnime ?></h2>anime
					</div>
					<div class="numberbox">
						<h2><?= $numberEpisodes ?></h2>episodes
					</div>
					<div class="numberbox">
						<h2><?= $numberUsers ?></h2>users
					</div>
					<div class="numberbox">
						<h2><?= $numberSeenEpisodes ?></h2>seen episodes
					</div>
				</div>
	    	</footer>
    	</div>
    </div>
</body>
</html>
