<?php
class Activity extends AppModel
{
	var $useTable = 'activities';
	var $order = 'timestamp DESC';

	function beforeSave($options) {
		$this->data['Activity']['timestamp'] = date("Y-m-d H:i:s");
		return true;
	}
	public $belongsTo = array( 
		'subject' => array(
			'foreignKey' => 'subject_id',
			'className'	=> 'User',
			'fields' => array('subject.id','subject.nick','subject.email')
			)
		);
}
?>
