<?php
class UserController extends AppController {
	var $helpers = array('Gravatar','Time');
	var $uses = array('Activity','UserEpisode');
	var $name = 'User';
	var $paginate = array( 'Activity' => array(
						'limit' => 20
					)
				);
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
		//echo $this->Auth->password('testing');
		$this->set('users',$this->User->find('all'));
	}

	function view($id = null){
		if($id == null || !is_numeric($id))
		{
			$id = $this->Auth->user('id');
			if($id != null)
				$this->redirect('/user/view/'.$this->Auth->user('id'));
			else
				$this->redirect('/front');
		}
		$this->User->id = $id;
		$this->set('user', $this->User->read());
		//$activities = $this->Activity->findAllBySubjectId($id);
		//debug($this->User);
		$activities = $this->paginate($this->User->Activity,array('Activity.subject_id' => $id));
		
		/*$episodes = $this->UserEpisode->find('all',array('conditions' => array('user_id' => $id)));
		echo "<pre>";
		print_r($episodes);
		echo "</pre>";*/
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

		// Variables for user graphs


		//debug($activities);
		//$this->set('activity',$this->Activity->findAllBySubjectId($id));	
	}

	function settings(){
		$id = $this->Auth->user('id');
		if($id == null)
			$this->redirect($this->referer());

		$this->User->recursive = -1;
		$this->User->id = $id;
		$this->set('user', $this->User->read(null,$id));
		//$activities = $this->Activity->findAllBySubjectId($id);
		$activities = $this->paginate($this->User->Activity,array('Activity.subject_id' => $id));
		//debug($this->User->Activity);
		App::uses('Helper', 'Time');
		//debug($this->request->data);
		if(!empty($this->request->data))
		{
			App::uses('Sanitize', 'Utility');

			//$this->Text = new TextHelper();
			$this->request->data = Sanitize::clean($this->request->data, array('encode' => false));
			//print_r($this->User->data);
			//echo AuthComponent::password($this->request->data['current_password']);
			if($this->User->data['User']['password'] == AuthComponent::password($this->request->data['current_password']))
			{
				if($this->request->data['new_password'] == $this->request->data['confirm_password'] && trim($this->request->data['current_password']) != ""){
					$this->User->set('password',AuthComponent::password($this->request->data['new_password']));
					if($this->User->save())
					{
						$this->Session->setFlash("Changed the password","flash_success");
						$this->redirect("/user/settings");
					}
					else{
						$this->Session->setFlash("Could not change the password","flash_error");
						$this->redirect("/user/settings");
					}
				}
			}
			else{
				$this->Session->setFlash("Could not change the password","flash_error");
				$this->redirect("/user/settings");
			}
			//$this->redirect($this->referer());
		}
	}
	
	function login($data = null){
		App::uses('Sanitize', 'Utility');

		$this->request->data = Sanitize::clean($this->request->data, array('encode' => false));
		$this->Auth->loginRedirect = array('controller' => 'user', 'action' => 'view');
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

	function register(){
		
		App::uses('Sanitize', 'Utility');
		$this->request->data = Sanitize::clean($this->request->data, array('encode' => false));
		
		if($this->request->data)
		{
			$email = $this->request->data['email'];
			if(trim($email) == "")
				return false;
				
			$user = $this->User->findByEmail($email);
			if($user != null)
			{
				$this->Session->setFlash('Could not create user. It already exists','flash_error');
				$this->redirect($this->referer());
				return; 
			}
			
			$this->User->create();
			$this->User->set('email',$email);
			$this->User->set('nick',$this->request->data['nickname']);
			
			$newPassword = $this->genRandomString(10);

			$newPasswordHashed = $this->Auth->password($newPassword);
			$this->User->set('joined', DboSource::expression('NOW()'));
			$this->User->set('password',$newPasswordHashed);
			
			
			if ($this->User->validates()) {
				if($this->User->save())
				{
					$this->sendResetEmail($email);
					return true;
				}
				else{
					$this->Session->setFlash('Could not create user','flash_error');
					$this->redirect($this->referer());
				}
			}
			else
			{
				$this->Session->setFlash('Could not validate input','flash_error');
				foreach($this->User->validationErrors as $error)
					$this->Session->setFlash($error[0],'flash_error');
				//print_r($this->User->validationErrors);
				$this->redirect($this->referer());
			}
			//print_r($this->request->data);
			// Find the user in question
			
		}
	}

	function lostpassword($user = null, $resetKey = null){
		if($user == null || $resetKey == null)
		{
			//print_r($this->request->data);
			if(isset($this->request->data['User']['email']))
			{
				App::uses('Sanitize', 'Utility');
				$this->request->data = Sanitize::clean($this->request->data, array('encode' => false));
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
			$this->Session->setFlash('Wrong parameters. No such user.','flash_error');
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
		$email->from(array('no-reply@urani.me' => 'Urani.me'));
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
		App::uses('Sanitize', 'Utility');
		$email = Sanitize::clean($email, array('encode' => false));
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
		$email->from(array('no-reply@urani.me' => 'Urani.me'));
		$email->to($user['User']['email']);
		$email->subject('Password reset for user ' . $user['User']['nick']);
		$email->send('You have requested a password reset. Please click this link if that is true. <a href="">http://urani.me/user/lostpassword/'.$user['User']['nick'].'/'.$resetKey.'</a>');

		//print_r($email);

		/*$this->Email->from  	= 'urani.me';
		$this->Email->to 		= $user['User']['email'];
		$this->Email->sendAs 	= 'html';
		$this->Email->subject 	= 'Password reset for user ' . $user['User']['nick'];
		$this->Email->send('You have requested a password reset. Please click this link if that is true. <a href="">http://158.39.171.120/user/lostpassword/'.$user['User']['nick'].'/'.$resetKey.'</a>');*/

		$this->Session->setFlash('An email has been sendt with a password reset link','flash_success');
	}
	
	function logout(){
		$this->Session->setFlash('Logout','flash_success');
		$this->redirect($this->Auth->logout());
	}
}
?>
