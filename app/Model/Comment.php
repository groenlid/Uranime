<?php
class Comment extends AppModel
{
	public $belongsTo = array( 'Anime', 'User');
	var $useTable = 'comments';
	
	function beforeSave($options) {
		$this->data['Comment']['timestamp'] = date("Y-m-d H:i:s");
		return true;
	}
}
?>
