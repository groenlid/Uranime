<?php
class AnimeRequest extends AppModel {
	var $name = 'AnimeRequest';
	var $useTable = 'anime_request';
	
	public $validation = array(
			'title' => array(
					'Empty' => array(
							'check' => true
						)
				)
		);

	public $belongsTo = array( 
		'user' => array(
			'foreignKey' => 'user_id',
			'className'	=> 'User'
			)
		);
}
?>
