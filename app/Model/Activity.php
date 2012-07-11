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
    /**
     * Compares different models based on their timestamp
     */
    static function cmp($a, $b){
        $aTime = Activity::getTime($a);
        $bTime = Activity::getTime($b);
        if($aTime == $bTime)
            return 0;
        else
            return ($aTime < $bTime) ? +1 : -1;   
    }

    static function getTime($modelData)
    {
        
        if(isset($modelData['UserEpisode']['timestamp']))
            return strtotime($modelData['UserEpisode']['timestamp']);
        else if(isset($modelData['Activity']['timestamp']))
            return strtotime($modelData['Activity']['timestamp']);
        else if(isset($modelData[0]['seenepisode_amount']))
            return strtotime($modelData[0]['timestamp']);
        else if(isset($modelData['Comment']))
            return strtotime($modelData['Comment']['timestamp']);
    }

}
?>
