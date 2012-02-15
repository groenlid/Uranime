<?php
class UserController extends AppController {
	var $helpers = array('Gravatar','Time');
	var $uses = array('Activity');
	var $name = 'User';
	
	var $components = array(
		'Email',
		'Auth' => array(
        	'authenticate' => array(
            	'Form' => array(
                	'fields' => array('username' => 'email')
            	)
        	)
    	)
	);

	function index(){
		$this->User->recursive = -1;
		
		$this->set('users',$this->User->find('all'));
	}

	function view($id = null){
		$this->User->id = $id;
		$this->set('user', $this->User->read());
		$activities = $this->Activity->findAllBySubjectId($id);
		App::uses('Helper', 'Time');
		$this->Anime->recursive = -1;

		foreach($activities as $key => $activity)
		{
			if($activity['Activity']['object_type'] == 'episode')
				$activities[$key]['object'] = $this->Episode->findById($activity['Activity']['object_id']);
			else
				$activities[$key]['object'] = $this->Anime->findById($activity['Activity']['object_id']);
			
		}
		$this->set('activity',$activities);
		//debug($activities);
		//$this->set('activity',$this->Activity->findAllBySubjectId($id));	
	}
	
	function login($data = null){
		/*$this->autoRender = false;*/
		Configure::write('debug', 0);
		if ($this->request->is('post')) {
        	if ($this->Auth->login()) {
            	return $this->redirect($this->Auth->redirect());
        	} else {
            	$this->Session->setFlash(__('Username or password is incorrect'), 'default', array(), 'auth');
        	}
    	}
		/*if(!empty($this->request->data))	{
			$conditions = array(
								'conditions' => array(
									'email' => $this->request->data['User']['email'], 
									'password' => $this->request->data['User']['password']
								)
							);
			$found = $this->User->find(
									'count', 
									$conditions	
									);
			if($found == 1)	{
				$this->Session->setFlash(sprintf("Welcome %s!", $this->request->data['User']['email']));
				$this->set('loginMessage','Success');
				echo "Success";
			}	else	{
				$this->Session->setFlash('Wrong username or password. Please try again');
				$this->set('loginMessage','Error');
			}
		}*/
		
	}

	function lostpassword($user = null, $resetKey = null){
		if($user == null || $resetKey == null)
		{
			if(!empty($this->request->data))
			{
				$email = $this->request->data['User']['email'];
				if(trim($email) == "")
					return false;
				//print_r($this->request->data);
				// Find the user in question
				$this->sendResetEmail($email);
				return true;
			}
			else
				return false;
		}
		
		$user = $this->User->findByNick($user);
		if($user == null){
			$this->Session->setFlash('Wrong parameters. No such user.');
			return false;
		}
		
		$resetKeyCompare = $this->generateResetKey($user['User']['email'],$user['User']['password']);

		if($resetKey != $resetKeyCompare)
		{
			$this->Session->setFlash('Wrong parameters');
			return false;
		}
			
		

		// Generate new password and send it to the user..
		$newPassword = $this->genRandomString(10);

		$newPasswordHashed = $this->Auth->password($newPassword);

		// Send new email
		$this->sendEmail($user['User']['email'], $newPassword);

		// Save new password
		$this->User->recusive = -1;
		$this->User->read(null,$user['User']['id']);
		$this->User->set('password',$newPasswordHashed);
		if($this->User->save())
		{
			$this->Session->setFlash('Your password has been changed and sendt to your email.','flash_success');
		}

	}

	private function sendEmail($emailStr = null, $newPassword)
	{
		if($emailStr == null || trim($emailStr) == "" || trim($newPassword) == "" || $newPassword == null)
			return false;

		App::uses('CakeEmail', 'Network/Email');
		//$this->Email->delivery = 'debug';
		$email = new CakeEmail();
		$email->from(array('me@example.com' => 'Urani.me'));
		$email->to($emailStr);
		$email->subject('New password for user ' . $emailStr);
		$email->send('Your new password is "'.$newPassword.'".<br> Please change your password when logging in for the first time since the change.');

		//$this->Email->delivery = 'debug';
		/*$this->Email->to 		= $email;
		$this->Email->sendAs 	= 'html';
		$this->Email->subject 	= 'New password for user ' . $email;
		$this->Email->send('Your new password is "'.$newPassword.'".<br> Please change your password when logging in for the first time since the change.');*/
	}

	private function genRandomString($length = 10) {
	    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
	    $string = "";    
	    for ($p = 0; $p < $length; $p++) {
	        $string .= $characters[mt_rand(0, strlen($characters)-1)];
	    }
	    return $string;
	}

	private function generateResetKey($user = null, $password = null){
		if ($user == null || $password == null)
		{
			return null; 
		}
		$resetKeyUnHashed = $user . $password;
		return $this->Auth->password($resetKeyUnHashed);
	}

	private function sendResetEmail($email = null)
	{
		if($email == null || trim($email) == "")
			return false;

		// Find the user based on the email-adress
		$this->User->recursive = -1;
		$user = $this->User->findByEmail($email);
		//print_r($user);
		
		if(count($user) == 0)
			return false;

		$resetKey = $this->generateResetKey($user['User']['email'],$user['User']['password']);
		App::uses('CakeEmail', 'Network/Email');
		//$this->Email->delivery = 'debug';
		$email = new CakeEmail();
		$email->from(array('me@example.com' => 'Urani.me'));
		$email->to($user['User']['email']);
		$email->subject('Password reset for user ' . $user['User']['nick']);
		$email->send('You have requested a password reset. Please click this link if that is true. <a href="">http://158.39.171.120/user/lostpassword/'.$user['User']['nick'].'/'.$resetKey.'</a>');

		/*$this->Email->from  	= 'urani.me';
		$this->Email->to 		= $user['User']['email'];
		$this->Email->sendAs 	= 'html';
		$this->Email->subject 	= 'Password reset for user ' . $user['User']['nick'];
		$this->Email->send('You have requested a password reset. Please click this link if that is true. <a href="">http://158.39.171.120/user/lostpassword/'.$user['User']['nick'].'/'.$resetKey.'</a>');*/

		$this->Session->setFlash('An email has been sendt with a password reset link','flash_success');
	}
	
	function logout(){
		$this->Session->setFlash('Logout');
		$this->redirect($this->Auth->logout());
	}
}
?>
