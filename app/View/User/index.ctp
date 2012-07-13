<div class="row">
	<div class="span11">
<div id="anime-gallery">
<?php
foreach($users as $user)
{
	echo '
		<div class="anime-gallery-single">
			<div class="anime-gallery-single-inner">
				<a href="/user/view/'.$user['User']['id'].'/'.$user['User']['nick'].'">
					'.$this->Gravatar->image($user['User']['email'], array('size' => '75')).' 
				</a>
			</div>
			<p class="bold calendarinfo" style="max-width: 75px;overflow: hidden;">
				<a href="/user/view/'.$user['User']['id'].'/'.$user['User']['nick'].'">'.$user['User']['nick'].'</a>
			</p>
		</div>
	';
}
?>


</div>	
<br class="clear">	
	<div class="pagination pageepisodes">
		<?php echo $this->Paginator->prev('Previous', null, null, array('class' => 'disabled')); ?>
		<?php echo $this->Paginator->numbers(array('separator'=> '')); ?>
		<?php echo $this->Paginator->next('Next', null, null, array('class' => 'disabled')); ?>
	</div> 
	<br class="clear">	
	</div>
</div>
 
