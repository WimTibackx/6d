<?php
	class_exists('Object') || require('lib/Object.php');
	class_exists('DataStorage') || require('lib/DataStorage/DataStorage.php');
	class_exists('Tag') || require('Tag.php');
	class Post extends Object{
		public function __construct($attributes = null){
			parent::__construct($attributes);
			$this->type = 'post';
			$this->is_published = false;
		}
		public function __destruct(){
			parent::__destruct();
		}
		public static $status = 'status';
		private $date;
		public function getDate(){
			return $this->date;
		}
		private $id;
		public function getId(){
			return $this->id;
		}
		public function setId($val){
			$this->id = $val;
		}

		private $person_post_id;
		public function getPerson_post_id(){
			return $this->person_post_id;
		}
		public function setPerson_post_id($val){
			$this->person_post_id = $val;
		}

		private $title;
		public function getTitle(){
			return $this->title;
		}
		public function setTitle($val){
			$this->title = $val;
		}

		private $type;
		public function getType(){
			return $this->type;
		}
		public function setType($val){
			$this->type = $val;
		}

		private $body;
		public function getBody(){
			return $this->body;
		}
		public function setBody($val){
			$this->body = $val;
		}

		private $source;
		public function getSource(){
			return $this->source;
		}
		public function setSource($val){
			$this->source = $val;
		}
		
		private $url;
		public function getUrl(){
			return $this->url;
		}
		public function setUrl($val){
			$this->url = $val;
		}

		private $description;
		public function getDescription(){
			return $this->description;
		}
		public function setDescription($val){
			$this->description = $val;
		}

		private $created;
		public function getCreated(){
			return $this->created;
		}
		public function setCreated($val){
			$this->created = $val;
		}
		private $post_date;
		public function getPost_date(){
			return $this->post_date;
		}
		public function setPost_date($val){
			$this->post_date = $val;
		}

		private $custom_url;
		public function getCustom_url(){
			return $this->custom_url;
		}
		public function setCustom_url($val){
			$this->custom_url = $val;
		}

		private $is_published;
		public function getIs_published(){
			return $this->is_published;
		}
		public function setIs_published($val){
			$this->is_published = $val;
		}

		private $tags;
		public function getTags(){
			return $this->tags;
		}
		private $owner_id;
		public function getOwner_id(){
			return $this->owner_id;
		}
		public function setOwner_id($val){
			$this->owner_id = $val;
		}
		
		public function setTags($val){
			$this->tags = $val;
		}
		
		public $password;
		public function getPassword(){
			return $this->password;
		}
		public function setPassword($val){
			if($val == null || strlen($val) == 0){
				$this->password = null;
			}else{
				$this->password = String::encrypt($val);
			}
		}
		
		public function getHowLongAgo(){
			return $this->post_date;
		}
		public function isHomePage($home_page_post_id){
			return $this->id > 0 && $this->id == $home_page_post_id;
		}
		// I need a way to tell the data storage whether or not to add the id in the sql statement
		// when inserting a new record. This is it. The data storage should default it to false, so
		// if this method doesn't exist, it'll default to false.
		public function shouldInsertId(){
			return true;
		}
		public function willAddFieldToSaveList($name, $value){
			
			if($name === 'id' && ($this->id === null || strlen($this->id) === 0)){
				$this->{$name} = uniqid(null, true);
				return $this->{$name};
			}
			return $value;			
		}
		
		public static function searchForPublished($q, $start = 0, $limit = 5, $sort_by = 'post_date', $sort_by_direction = 'desc', $owner_id){
			$config = new AppConfiguration();
			$post = new Post(null);
			$db = Factory::get($config->db_type, $config);
			if($sort_by === null || strlen($sort_by) === 0){
				$sort_by = $post->getTableName() . '.id';
			}
			$q = '%' . $q . '%';
			$query = sprintf("(title like '%s' or description like '%s' or body like '%s') and is_published=1 and owner_id=%d", $q, $q, $q, $owner_id);
			$list = $db->find(new ByClause($query, $post->relationships, array($start, $limit), array($sort_by=>$sort_by_direction)), $post);
			$list = ($list == null ? array() : (is_array($list) ? $list : array($list)));
			return $list;
		}
		public static function search($q, $start, $limit, $sort_by, $sort_by_direction = 'desc', $owner_id){
			$config = new AppConfiguration();
			$post = new Post(null);
			$db = Factory::get($config->db_type, $config);
			if($sort_by === null || strlen($sort_by) === 0){
				$sort_by = $post->getTableName() . '.id';
			}
			$q = '%' . $q . '%';
			$query = sprintf("(title like '%s' or description like '%s' or body like '%s') and owner_id=%d", $q, $q, $q, $owner_id);
			$list = $db->find(new ByClause($query, $post->relationsips, array($start, $limit), array($sort_by=>$sort_by_direction)), $post);
			$list = ($list == null ? array() : (is_array($list) ? $list : array($list)));
			return $list;
		}
		
		public static function findAll(){
			$config = new AppConfiguration();				
			$db = Factory::get($config->db_type, $config);
			$list = $db->find(new All(null, null, 0, null), new Post());
			$list = ($list == null ? array() : (is_array($list) ? $list : array($list)));
			return $list;
		}
		public static function findPublished($start, $limit, $sort_by, $sort_by_direction = 'desc', $owner_id){
			$config = new AppConfiguration();
			$post = new Post(null);
			$db = Factory::get($config->db_type, $config);
			if($sort_by === null || strlen($sort_by) === 0){
				$sort_by = $post->getTableName() . '.id';
			}
			$list = $db->find(new ByClause(sprintf("is_published=1 and owner_id=%d", $owner_id), null, array($start, $limit), array($sort_by=>$sort_by_direction)), $post);
			$list = ($list == null ? array() : (is_array($list) ? $list : array($list)));
			return $list;
		}
		public static function findPublishedPosts($start, $limit, $sort_by, $sort_by_direction = 'desc', $owner_id){
			$config = new AppConfiguration();
			$post = new Post(null);
			$db = Factory::get($config->db_type, $config);
			if($sort_by === null || strlen($sort_by) === 0){
				$sort_by = $post->getTableName() . '.id';
			}
			$list = $db->find(new ByClause(sprintf("is_published=1 and type != 'page' and owner_id=%d", $owner_id), null, array($start, $limit), array($sort_by=>$sort_by_direction, 'id'=>'desc')), $post);
			$list = ($list == null ? array() : (is_array($list) ? $list : array($list)));
			return $list;
		}
		public static function findByPerson(Person $person, $start, $limit, $sort_by, $sort_by_direction, $owner_id){
			$sort_by_direction = ($sort_by_direction !== null ? $sort_by_direction : 'desc');
			$config = new AppConfiguration();
			$post = new Post(null);
			$db = Factory::get($config->db_type, $config);
			if($sort_by === null || strlen($sort_by) === 0){
				$sort_by = $post->getTableName() . '.id';
			}
			$start_limit = null;
			if($limit > 0){
				$start_limit = array($start, $limit);
			}else{
				$start_limit = $limit;
			}
			$list = $db->find(new ByClause(sprintf("source = %s and owner_id=%s", ($person->url === null ? "''" : "'" . $person->url . "'"), $owner_id), null, $start_limit, array($sort_by=>$sort_by_direction)), $post);
			
			$list = ($list == null ? array() : $list);
			return $list;
		}
		public static function findByTag($tag, $start, $limit, $sort_by, $sort_by_direction = 'desc', $owner_id){
			$config = new AppConfiguration();
			$post = new Post(null);
			$db = Factory::get($config->db_type, $config);
			if($sort_by === null || strlen($sort_by) === 0){
				$sort_by = $post->getTableName() . '.id';
			}
			$tag->text = urlencode($tag->text);
			$owner_id = (int)$owner_id;
			$list = $db->find(new ByClause("tags like '%{$tag}%' and owner_id={$owner_id}", null, array($start, $limit), array($sort_by=>$sort_by_direction)), $post);
			$list = ($list == null ? array() : $list);
			return $list;
		}
		public static function findPublishedByTag($tag, $start, $limit, $sort_by, $sort_by_direction = 'desc', $owner_id){
			$config = new AppConfiguration();
			$post = new Post(null);
			$db = Factory::get($config->db_type, $config);
			if($sort_by === null || strlen($sort_by) === 0){
				$sort_by = $post->getTableName() . '.id';
			}
			$tag->text = urlencode($tag->text);
			$owner_id = (int)$owner_id;
			$clause = new ByClause("tags like '%{$tag->text}%' and is_published=1 and owner_id={$owner_id}", null, array($start, $limit), array($sort_by=>$sort_by_direction));
			$list = $db->find($clause, $post);
			$list = ($list == null ? array() : $list);
			return $list;
		}

		public static function find($start, $limit, $sort_by, $sort_by_direction = 'desc', $owner_id){
			$config = new AppConfiguration();
			$post = new Post(null);
			$db = Factory::get($config->db_type, $config);
			if($sort_by === null || strlen($sort_by) === 0){
				$sort_by = $post->getTableName() . '.id';
			}
			$start_limit = null;
			if($limit > 0){
				$start_limit = array($start, $limit);
			}else{
				$start_limit = $limit;
			}
			$owner_id = (int)$owner_id;
			$list = $db->find(new ByClause("owner_id={$owner_id}", null, $start_limit, array($sort_by=>$sort_by_direction)), $post);
			$list = ($list == null ? array() : (is_array($list) ? $list : array($list)));
			return $list;
		}
		public static function findPublishedPages($owner_id){
			$config = new AppConfiguration();
			$post = new Post(null);
			$db = Factory::get($config->db_type, $config);
			$owner_id = (int)$owner_id;
			$list = $db->find(new ByClause("type='page' and is_published=1 and owner_id={$owner_id}", null, 0, null), $post);
			return $list;
		}
		public static function findFriendsPublishedStatii($owner_id){
			$config = new AppConfiguration();
			$post = new Post(null);
			$db = Factory::get($config->db_type, $config);
			$owner_id = (int)$owner_id;
			$list = $db->find(new ByClause("type='status' and is_published=1 and owner_id={$owner_id} and person_post_id <> null", null, 0, null), $post);
			return $list;
		}
		
		public static function findAllPublished($custom_url, $owner_id){
			$config = new AppConfiguration();
			$db = Factory::get($config->db_type, $config);
			$cusomt_url = String::sanitize($custom_url);
			$post = $db->find(new ByClause("is_published=1 and custom_url='{$custom_url}' and owner_id={$owner_id}", null, 1, null), new Post(null));
			return $post;
		}
		
		public static function findByAttribute($name, $value, $owner_id){
			$config = new AppConfiguration();
			$db = Factory::get($config->db_type, $config);
			$owner_id = (int)$owner_id;
			$post = $db->find(new ByClause(sprintf("%s='%s' and owner_id=%d", $name, $value, $owner_id), null, 1, null), new Post(null));
			return $post;
		}
	
		public static function findById($id = null){
			$config = new AppConfiguration();				
			$db = Factory::get($config->db_type, $config);
			$post = $db->find(new ById($id), new Post(null));
			return $post;
		}
		public static function findHomePage($id = null, $owner_id){
			$config = new AppConfiguration();
			$db = Factory::get($config->db_type, $config);
			$owner_id = (int)$owner_id;
			$post = $db->find(new ByClause(sprintf("id='%s' and is_published=1 and owner_id=%d", $id, $owner_id), null, 1, null), new Post(null));
			return $post;
		}
		
		public static function findByPersonPostId($id = 0, $owner_id){
			$config = new AppConfiguration();
			$db = Factory::get($config->db_type, $config);
			$owner_id = (int)$owner_id;
			$post = $db->find(new ByClause("person_post_id = '{$id}' and owner_id={$owner_id}", null, 1, null), new Post(null));
			return $post;
		}
		public function canModify($user){
			return $this->owner_id === $user->person_id;
		}
		public function getTableName($config = null){
			if($config == null){
				$config = new AppConfiguration();
			}
			return $config->prefix . 'posts';
		}
		public static function delete(Post $post){
			$config = new AppConfiguration();
			$db = Factory::get($config->db_type, $config);
			return $db->delete(null, $post);
		}
		public static function save(Post $post){
			$errors = self::canSave($post);
			$config = new AppConfiguration();
			if(count($errors) == 0){
				$db = Factory::get($config->db_type, $config);
				$db->save(null, $post);
				$existing_tags = Tag::findAllForPost($post->id, $post->owner_id);
				if($existing_tags != null){
					foreach($existing_tags as $tag){
						Tag::delete($tag);
					}
				}

				foreach($post->tags as $tag_text){
					if($existing_tags == null || !in_array($tag_text, $existing_tags)){
						Tag::save(new Tag(array('parent_id'=>$post->id, 'type'=>'post', 'text'=>$tag_text, 'owner_id'=>$post->owner_id)));
					}
				}
				self::notify('didSavePost', $post, $post);
			}
			return array($post, $errors);
		}

		public static function canSave(Post $post){
			$errors = array();
			return $errors;
		}
		public function install(Configuration $config){
			$message = '';
			$db = Factory::get($config->db_type, $config);
			try{
				$table = new Table($this->getTableName($config), $db);
				$table->addColumn('id', 'string', array('is_nullable'=>false, 'size'=>255));
				$table->addColumn('person_post_id', 'string', array('is_nullable'=>true, 'size'=>255));
				$table->addColumn('title', 'string', array('is_nullable'=>true, 'default'=>'', 'size'=>255));
				$table->addColumn('type', 'string', array('is_nullable'=>true, 'default'=>'post', 'size'=>80));
				$table->addColumn('body', 'text', array('is_nullable'=>true, 'default'=>''));
				$table->addColumn('source', 'string', array('is_nullable'=>true, 'default'=>'', 'size'=>255));
				$table->addColumn('url', 'string', array('is_nullable'=>true, 'default'=>'', 'size'=>255));
				$table->addColumn('description', 'string', array('is_nullable'=>true, 'default'=>'', 'size'=>255));
				$table->addColumn('post_date', 'datetime', array('is_nullable'=>true, 'default'=>null));
				$table->addColumn('created', 'datetime', array('is_nullable'=>false));
				$table->addColumn('custom_url', 'string', array('is_nullable'=>true, 'default'=>'', 'size'=>255));
				$table->addColumn('tags', 'text', array('is_nullable'=>true));
				$table->addColumn('is_published', 'boolean', array('is_nullable'=>true, 'default'=>false));
				$table->addColumn('password', 'string', array('is_nullable'=>true, 'size'=>255));
				$table->addColumn('owner_id', 'biginteger', array('is_nullable'=>false));
				
				$table->addKey('primary', 'id');
				$table->addKey('key', array('owner_id_key'=>'owner_id'));
				$table->addKey('key', array('title_key'=>'title'));
				$table->addKey('key', array('custom_url_key'=>'custom_url'));
				$table->addKey('key', array('is_published_key'=>'is_published'));
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