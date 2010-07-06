<?php
	class_exists('Object') || require('lib/Object.php');
	class_exists('DataStorage') || require('lib/DataStorage/DataStorage.php');
	class_exists('Profile') || require('Profile.php');
	class Person extends Object{
		public function __construct($attributes = null){
			parent::__construct($attributes);
		}
		public function __destruct(){
			parent::__destruct();
		}
		
		private $relationships;
		public $confirmation_password;
		
		private $id;
		public function getId(){
			return (int)$this->id;
		}
		public function setId($val){
			$this->id = (int)$val;
		}
		
		private $uid;
		public function getUid(){
			return $this->uid;
		}
		public function setUid($val){
			$this->uid = $val;
		}
		
		private $session_id;
		public function getSession_id(){
			return $this->session_id;
		}
		public function setSession_id($val){
			$this->session_id = $val;
		}

		private $name;
		public function getName(){
			return $this->name;
		}
		public function setName($val){
			$this->name = $val;
		}
		
		private $email;
		public function getEmail(){
			return $this->email;
		}
		public function setEmail($val){
			$this->email = $val;
		}

		private $is_approved;
		public function getIs_approved(){
			return $this->is_approved;
		}
		public function setIs_approved($val){
			$this->is_approved = $val;
		}

		private $is_owner;
		public function getIs_owner(){
			return $this->is_owner;
		}
		public function setIs_owner($val){
			$this->is_owner = $val;
		}

		private $do_list_in_directory;
		public function getDo_list_in_directory(){
			return $this->do_list_in_directory;
		}
		public function setDo_list_in_directory($val){
			$this->do_list_in_directory = $val;
		}

		private $password;
		public function getPassword(){
			return $this->password;
		}
		public function setPassword($val){
			$this->password = $val;
		}

		private $url;
		public function getUrl(){
			return $this->url;
		}
		public function setUrl($val){
			$this->url = $val;
		}
		
		private $profile;
		public function getProfile(){
			return $this->profile;
		}
		public function setProfile($val){
			$this->profile = $val;
		}
		private $public_key;
		public function getPublic_key(){
			return $this->public_key;
		}
		public function setPublic_key($val){
			$this->public_key = $val;
		}
		
		private $owner_id;
		public function getOwner_id(){
			return $this->owner_id;
		}
		public function setOwner_id($val){
			$this->owner_id = $val;
		}
				
		public function getTableName($config = null){
			if($config == null){
				$config = new AppConfiguration();
			}
			return $config->prefix . 'people';
		}
		// I need a way to tell the data storage whether or not to add the id in the sql statement
		// when inserting a new record. This is it. The data storage should default it to false, so
		// if this method doesn't exist, it'll default to false.
		public function shouldInsertId(){
			return true;
		}
		public function willAddFieldToSaveList($name, $value){
			
			if($name === 'uid' && $this->uid !== null && strlen($this->uid) > 0){
				return uniqid(null, true);
			}
			return $value;			
		}
		public static function makeOwner($owner_id, $people){
			for($i = 0; $i < count($people); $i++){
				if(intval($people[$i]->id) == (int)$owner_id){
					$people[$i]->is_owner = true;
				}
			}
			return $people;
		}
		public static function findById($id){
			$config = new AppConfiguration();
			$db = Factory::get($config->db_type, $config);
			$person = $db->find(new ById($id), new Person(null));
			return $person;
		}
		public static function findByIdAndOwner($id, $owner_id){
			$config = new AppConfiguration();
			$db = Factory::get($config->db_type, $config);
			$id = (int)$id;
			$owner_id = (int)$owner_id;
			$person = $db->find(new ByClause("id={$id} and owner_id={$owner_id}"), new Person(null));
			return $person;
		}
		
		public static function findByTagText($text){
			$config = new AppConfiguration();
			$person = new Person(null);
			$db = Factory::get($config->db_type, $config);
			$tag = new Tag(null);
			$query = sprintf("select p.* from {$person->getTableName()} as p, {$tag->getTableName()} as t where t.type='group' and t.parent_id=p.id and t.text = '%s'", self::stringify($text));
			$list = $db->find(new All($query, null, 0, array('id'=>'asc')), $person);
			$list = ($list == null ? array() : $list);
			return $list;
		}
		
		public static function findByTagTextAndOwner($text, $owner_id){
			$config = new AppConfiguration();
			$person = new Person(null);
			$db = Factory::get($config->db_type, $config);
			$tag = new Tag(null);
			$owner_id = (int)$owner_id;
			$query = sprintf("select p.* from {$person->getTableName()} as p, {$tag->getTableName()} as t where t.type='group' and t.parent_id=p.id and t.text = '%s' and p.owner_id=%d", self::stringify($text), $owner_id);
			$list = $db->find(new All($query, null, 0, array('id'=>'asc')), $person);
			$list = ($list == null ? array() : $list);
			return $list;
		}
		public static function findAllByOwner($id){
			$config = new AppConfiguration();
			$db = Factory::get($config->db_type, $config);
			$id = (int)$id;
			$clause = new ByClause(sprintf("owner_id=%d or id=%d", $id, $id), null, 0, null);
			$list = $db->find($clause, new Person());
			return $list;
		}
		public static function findAll(){
			$config = new AppConfiguration();
			$db = Factory::get($config->db_type, $config);
			$list = $db->find(new All(null, null, 0, null), new Person());
			return $list;
		}
		public static function findByIds($ids = array()){
			$config = new AppConfiguration();
			$db = Factory::get($config->db_type, $config);
			$list = $db->find(new ByIds($ids), new Person());
			return $list;
		}

		public static function findByEmailAndPassword($email, $password){
			$config = new AppConfiguration();
			$db = Factory::get($config->db_type, $config);
			$person = $db->find(new ByClause(sprintf("email='%s' and password='%s'", urlencode($email), String::encrypt($password)), null, 1, null), new Person(null));
			return $person;
		}
		public static function findByPublicKey($public_key){
			$config = new AppConfiguration();
			$db = Factory::get($config->db_type, $config);
			$list = $db->find(new ByAttribute('public_key', $public_key, 1, null), new Person());
			return $list;
		}
		public static function findByEmail($email){
			$config = new AppConfiguration();
			$db = Factory::get($config->db_type, $config);
			$person = $db->find(new ByAttribute('email', $email, 1, null), new Person(null));
			return $person;
		}
		
		public static function findByUrl($url){
			$config = new AppConfiguration();
			$db = Factory::get($config->db_type, $config);
			$list = $db->find(new ByAttribute('url', $url, 1, null), new Person());
			return $list;
		}
		
		public static function stringify($text){
			return sprintf("%s", urlencode($text));
		}
		public static function delete_many($ids = array()){
			$config = new AppConfiguration();
			$db = Factory::get($config->db_type, $config);
			if($ids !== null){
				$clause = new ByClause(sprintf("id in (%s)", implode(',', $ids)), null, 0, null);
				return $db->delete($clause, new Person(null));
			}
			return 0;
		}
		
		public static function delete(Person $person){
			$config = new AppConfiguration();
			$db = Factory::get($config->db_type, $config);
			return $db->delete(null, $person);
		}
		public static function save(Person $person){
			$config = new AppConfiguration();
			$db = Factory::get($config->db_type, $config);
			/*if($person->password !== null && strlen($person->password) > 0){
				$person->password = String::encrypt($person->password);
				$person->confirmation_password = String::encrypt($person->confirmation_password);
			}
			*/
			$new_person = $db->save(null, $person);
			$person->id = $new_person->id;
			self::notify('didSavePerson', $person, $person);
			return $person;
		}

		public static function canSave(Person $person){
			$errors = array();
			$existing_person = null;
			if($person->email !== null){
				$existing_person = Person::findByEmail($person->email);
			}
			if($existing_person != null && ($person->id != $existing_person->id)){
				$errors['email'] = "Please enter a different email address.";
			}
			if($person->email == null || strlen($person->email) == 0){
				$errors['email'] = "Your email is required to identify your account.";
			}
			
			if($person->id !== null && strlen($person->id) > 0){
				if(($person->password == null || empty($person->password))){
					$errors['password'] = "You have to enter your password so you can use it to sign into the site.";
				}

				if($person->confirmation_password === null || strlen($person->confirmation_password) === 0 || $person->confirmation_password !== $person->password){
					$errors['confirmation_password'] = "The confirmation password that you entered doesn't match what you entered for your password. We check this just to make sure you're entering what you think since you can't see the password.";

					if(!array_key_exists('password', $errors)){
						$errors['password'] = "Please re-enter your password.";
					}
				}
			}
			
			if($person->name == null || strlen($person->name) === 0){
				$errors['name'] = "We need to know your name or at least what you want us to call you.";
			}
						
			if($person->uid === null || strlen($person->uid) === 0){
				$errors['uid'] = "The UID is required.";
			}

			return $errors;
		}
		
		public static function sort_by_name($a, $b){
			$a_name = strtolower($a->name);
			$b_name = strtolower($b->name);
			if($a_name == $b_name){
				return 0;
			}
			return ($a_name < $b_name) ? -1 : 1;
		}
		
		
		public function install(Configuration $config){
			$message = '';
			$db = Factory::get($config->db_type, $config);
			try{
				$table = new Table($this->getTableName($config), $db);
				$table->addColumn('id', 'biginteger', array('is_nullable'=>false, 'auto_increment'=>true));
				$table->addColumn('uid', 'string', array('is_nullable'=>false, 'size'=>255));
				$table->addColumn('url', 'string', array('is_nullable'=>true, 'default'=>null, 'size'=>255));
				$table->addColumn('session_id', 'string', array('is_nullable'=>false, 'size'=>255));
				$table->addColumn('public_key', 'string', array('is_nullable'=>true, 'size'=>255));
				$table->addColumn('name', 'string', array('is_nullable'=>false, 'size'=>255));
				$table->addColumn('email', 'string', array('is_nullable'=>true, 'size'=>255));
				$table->addColumn('password', 'string', array('is_nullable'=>true, 'size'=>255));
				$table->addColumn('is_approved', 'boolean', array('is_nullable'=>false, 'default'=>false));
				$table->addColumn('is_owner', 'boolean', array('is_nullable'=>false, 'default'=>false));
				$table->addColumn('do_list_in_directory', 'boolean', array('is_nullable'=>false, 'default'=>false));
				$table->addColumn('profile', 'text', array('is_nullable'=>true));
				$table->addColumn('owner_id', 'biginteger', array('is_nullable'=>false));
				
				
				$table->addKey('primary', 'id');
				$table->addKey('key', array('session_id_key'=>'session_id'));
				$table->addKey('key', array('owner_id_key'=>'owner_id'));
				$table->addKey('key', array('name_key'=>'name'));
				$table->addKey('key', array('email_key'=>'email'));
				$table->addKey('key', array('do_list_in_directory_key'=>'do_list_in_directory'));
				$table->addOption('ENGINE=MyISAM DEFAULT CHARSET=utf8');
				$errors = $table->save();
				if(count($errors) > 0){
					foreach($errors as $error){
						$message .= $error;
					}
					throw new Exception($message);
				}
			}catch(Exception $e){
				$db->deleteTable($this->getTableName($config));
				throw $e;
			}
		}
	}
?>