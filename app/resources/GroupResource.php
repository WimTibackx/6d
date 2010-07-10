<?php
class_exists('AppResource') || require('AppResource.php');
class_exists('Tag') || require('models/Tag.php');
class_exists('Person') || require('models/Person.php');

class GroupResource extends AppResource{
	public function __construct($attributes = null){
		parent::__construct($attributes);
		if(! AuthController::isAuthorized()){
			FrontController::setRequestedUrl('addressbook');
			throw new Exception(FrontController::UNAUTHORIZED, 401);
		}
	}
	public function __destruct(){
		parent::__destruct();
	}
	public $groups;
	public $group;
	public $people;
	public function get($group_id){
		$this->title = 'Address Book';
		if(AuthController::isSuperAdmin()){
			$this->people = Person::findAll();
		}else{
			$this->people = Person::findAllByOwner($this->current_user->person_id);
		}
		if($this->people == null){
			$this->people = array();
		}
		$this->people = Person::makeOwner($this->current_user->id, $this->people);
		$all_contacts = new Tag(array('id'=>-1, 'type'=>'group', 'text'=>'All Contacts'));
		$this->groups = Tag::findAllTagsForGroups($this->current_user->person_id);
		if($this->groups === null){
			$this->groups = array();
		}
		$this->groups = array_merge(array($all_contacts), $this->groups);
		$view = 'addressbook/index';
		$this->output = $this->renderView($view);
		return $this->renderView('layouts/default');
	}

	public function delete(Tag $group = null){
		if($group != null && strlen($group->text) > 0){
			Tag::delete($group);
		}
		$all_contacts = new Tag(array('id'=>-1, 'type'=>'group', 'text'=>'All Contacts'));
		$this->groups = Tag::findAllTagsForGroups($this->current_user->person_id);
		if($this->groups === null){
			$this->groups = array();
		}
		$this->groups = array_merge(array($all_contacts), $this->groups);
		$view = 'addressbook/index';
		$this->output = $this->renderView($view);
		return $this->renderView('layouts/default');
	}
	
}