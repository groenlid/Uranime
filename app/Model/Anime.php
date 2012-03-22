<?php
class Anime extends AppModel {
	var $name = 'Anime';
	var $useTable = 'anime';
	
	/*public $actAs = array(
		'MeioUpload' => array(
				'image' => array(
						'dir' => 'uploads{DS}{model}{DS}{field}',
						'create_directory' => true,
						'allowed_mime' => array('image/jpeg', 'image/pjpeg', 'image/png', 'image/gif'),
                        'allowed_ext' => array('.jpg', '.jpeg', '.png', 'gif')
					),
				'fanart' => array(
						'dir' => 'uploads{DS}{model}{DS}{field}',
						'create_directory' => true,
						'allowed_mime' => array('image/jpeg', 'image/pjpeg', 'image/png', 'image/gif'),
                        'allowed_ext' => array('.jpg', '.jpeg', '.png', 'gif')
					)
			)
		);*/
		
	public $hasMany = array( 
					'AnimeGenre',
					'Episode' => array(
						'className'	=> 'Episode',
						'foreignKey'	=> 'anime_id',
						'order'		=> 'Episode.number ASC',
						'dependent'	=> true
					),
					'AnimeSynonyms',
					'ScrapeInfo'
				);
	public $validation = array(
			'image' => array(
					'Empty' => array(
							'check' => true
						)
				),
			'fanart' => array(
					'Empty' => array(
							'check' => true
						)
				)
		);
	
}
?>
