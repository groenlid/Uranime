<?php
    //debug($activity);
	$time = strftime("%Y-%m-%dT%H:%M:%S%z",strtotime($activity['timestamp']));
	$result = "";
    $escape = (isset($activity['escapecomment'])) ? $activity['escapecomment'] : true;
    // New comment system
    
    $timeago = $this->Time->timeAgoInWords(strtotime($activity['timestamp']));
	$result .= "
	<div class='comment-container'>
		<div class='comment-avatar'>
			".$activity['thumbnail']."
		</div>
		<div class='comment'>
			<div class='comment-meta'>" . $activity['desc'] . "<span class='comment-time'><abbr class='timeago' title='".$time."'>".$timeago."</abbr></span></div>";

	if(isset($activity['comment']))
		$result .= "
	<div class='comment-text'>
		" . $this->Text->autoLink($activity['comment'], array('escape' => $escape)) . "
	</div>
	";

	$result .= "
		</div>
	</div>
	";
	echo $result;
?>
