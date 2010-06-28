<?php
class_exists('AppResource') || require('AppResource.php');
class_exists('Post') || require('models/Post.php');
class_exists('Person') || require('models/Person.php');
class_exists('Profile') || require('models/Profile.php');
class_exists('LoginResource') || require('LoginResource.php');
class ProfileResource extends AppResource{
	public function __construct($attributes = null){
		parent::__construct($attributes);
	}
	public function __destruct(){
		parent::__destruct();
	}
	public $person;
	public function get($state = null, Person $person = null){
		if(count($this->url_parts) > 1){
			// Get the person's photo.
			$photo_file_type = '.png';
			$matches = String::find('/\.(.+$)/', $this->url_parts[1]);
			if(count($matches) > 0){
				$photo_file_type = $matches[1];
			}
			if(!Application::isPhotoPublic()){
				$person = Person::findByPublicKey(urldecode($person->public_key));
				if($person !== null && $person->is_approved){
					$this->person = $this->owner;
					$this->output = $this->renderView('profile/photo');
					return $this->renderView('layouts/default');
				}else{
					throw new Exception(FrontController::NOTFOUND, 404);
				}
			}else{
				$this->person = $this->owner;
				$this->output = $this->renderView('profile/photo');
				return $this->renderView('layouts/default');
			}
		}else{
			$this->person = $this->owner;
			if($this->person === null){
				$this->person = new Person();
			}
			if($this->person->profile !== null){
				$this->person->profile = unserialize($this->person->profile);
			}
			$this->title = $this->person->name . "'s profile.";
			if($state === 'modify'){
				if(!AuthController::isAuthorized() || $this->current_user->id !== $this->owner->person_id){
					throw new Exception(FrontController::UNAUTHORIZED, 401);
				}
				$this->output = $this->renderView('profile/edit', null);
				return $this->renderView('layouts/default', null);
			}else{
				if($this->person->profile === null || strlen($this->person->profile) === 0){
					$this->person->profile = serialize(new Profile(null));
				}
				$this->output = $this->renderView('profile/index', null);
				return $this->renderView('layouts/default', null);
			}
		}
	}
	public static function getPhotoUrl(Person $person, $photo_file_type = '.png'){
		if($person->profile === null || $person->profile->photo_url === null){
			return FrontController::urlFor('images') . 'mant-emo/grin.png';
		}else if(strpos($person->profile->photo_url, 'http') !== false){
			return $person->profile->photo_url;
		}else if($person->profile->photo_url !== null){
			return str_replace('index.php', '', FrontController::urlFor(null)) . $person->profile->photo_url;
		}
	}
	public function put(Person $person, Profile $profile){
		if(!AuthController::isAuthorized() || $this->current_user->id !== $this->owner->person_id){
			throw new Exception(FrontController::UNAUTHORIZED, 401);
		}
		$this->person = $person;
		$this->person->setSession_id(session_id());
		$existing_person = Person::findById($this->current_user->id);
		if($existing_person === null || $existing_person->id === 0){
			throw new Exception(FrontController::NOTFOUND, 404);
		}
		$this->person->setUid($existing_person->getUid());
		if($profile != null){
			$this->person->setProfile(serialize($profile));
		}
		$this->person->url = String::replace('/http[s]?\:\/\//', '', FrontController::$site_path);
		$this->person->url = String::replace('/\/$/', '', $this->person->url);
		$this->person->is_approved = true;
		$this->person = Person::save($this->person);
		if(count($errors) == 0){
			self::setUserMessage('Profile saved');
		}else{
			$message = $this->renderView('install/error', array('message'=>"The following errors occurred when saving your profile. Please resolve and try again.", 'errors'=>$errors));					
			self::setUserMessage($message);
		}
		$view = 'profile/index';
		$this->output = $this->renderView($view, array('errors'=>$errors));
		return $this->renderView('layouts/default');
	}
}

?>