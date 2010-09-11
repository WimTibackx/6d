<?php
class_exists('AppResource') || require('resources/AppResource.php');
class_exists('AuthController') || require('controllers/AuthController.php');
class_exists('ProfileResource') || require('resources/ProfileResource.php');
class_exists('Member') || require('models/Member.php');
class_exists('ProfileController') || require('controllers/ProfileController.php');
class Application{
	public function __construct(){
		if (array_key_exists('PHP_AUTH_DIGEST', $_SERVER) && !AuthController::authKey()){
			$data = String::toArray($_SERVER['PHP_AUTH_DIGEST']);
			if(class_exists('AppConfiguration')){
				$config = new AppConfiguration();
			}else{
				$config = new Object();
			}
			
			/* My host runs PHP as a CGI and so I added:
				
				RewriteCond %{HTTP:Authorization} !^$
				RewriteRule .* - [E=PHP_AUTH_DIGEST:%{HTTP:Authorization},L]
				
				to the .htaccess file and when I did that, PHP_AUTH_DIGEST was set
				but the username key in the array was now "Digest username".
			*/
			if(array_key_exists('Digest username', $data)){
				$data['username'] = $data['Digest username'];
			}

			$data['username'] = str_replace('"', '', $data['username']);
			$data['response'] = str_replace('"', '', $data['response']);
			$data['realm'] = str_replace('"', '', $data['realm']);
			$data['nonce'] = str_replace('"', '', $data['nonce']);
			$data['uri'] = str_replace('"', '', $data['uri']);
			$data['opaque'] = str_replace('"', '', $data['opaque']);
			$data['cnonce'] = str_replace('"', '', $data['cnonce']);
			$data['nc'] = str_replace('"', '', $data['nc']);
			$data['qop'] = str_replace('"', '', $data['qop']);
			if(isset($data['username']) && $config->email === $data['username']){
				$a1 = md5($data['username'] . ':' . $data['realm'] . ':' . $config->site_password);
				$a2 = md5($_SERVER['REQUEST_METHOD'].':'.$data['uri']);
				$encrypted_response = md5($a1.':'.$data['nonce'].':'.$data['nc'].':'.$data['cnonce'].':'.$data['qop'].':'.$a2);
				if ($data['response'] === $encrypted_response){
					AuthController::setAuthKey($data['username']);
				}				
			}			
		}
		if(AuthController::authKey() !== null){
			self::$current_user = Member::findByEmail(AuthController::authKey());
		}
		
	}
	public function __destruct(){}
	public static $member;
	public static $current_user;
	public static function isPhotoPublic(){
		return true;
	}
	public function willSetUrlFor($resource){
		if(self::$member !== null && !self::$member->is_owner){
			$resource = self::$member->member_name . '/'. $resource;
		}
		return $resource;
	}
	public function exceptionHasOccured($sender, $args){
		$e = $args['exception'];
		$file_type = $args['file_type'];
		$resource = new AppResource(array('file_type'=>$file_type));
		if($e->getCode() == 401){
			if($file_type === 'html'){
				FrontController::redirectTo('login');
			}else{
				FrontController::send401Headers('Please login', 'sixd');
			}
		}elseif($e->getCode() == 404){
			$resource->output = $resource->renderView('error/404', array('message'=>$e->getMessage()));
			return $resource->renderView('layouts/default');
		}elseif(strpos($e->getMessage(), 'No database selected') !== false || get_class($e) == 'DSException'){
			Resource::setUserMessage($e->getMessage() . ' - You need to create the database first.');
			$resource->output = $resource->renderView('install/index', array('message'=>$e->getMessage()));
			return $resource->renderView('layouts/install');
		}else{
			Resource::setUserMessage('Exception has occured: ' . $e->getMessage());
			return $resource->renderView('layouts/default');
		}
	}
	public function unauthorizedRequestHasOccurred($sender, $args){
		FrontController::send401Headers('Please login', 'sixd');
		if($args['file_type'] === 'html'){
//			FrontController::redirectTo('login');			
		}else{
		}
	}
	public function willExecute($path_info){
		Photo::addObserver(new ProfileController(), 'willDeletePhoto', 'Photo');
		if(!class_exists('AppConfiguration')){
			return $path_info;
		}		
		if($path_info !== null){
			$path = explode('/', $path_info);			
			if(count($path) > 0){
				$member_name = array_shift($path);		
				self::$member = Member::findByMemberName($member_name);
				if(self::$member !== null){
					$path_info = implode('/', $path);					
				}
			}
		}
		if(self::$member === null){
			self::$member = Member::findOwner();
		}
		return $path_info;
	}
	public function errorDidHappen($message){
		console::log($message);
	}
	
	public function resourceOrMethodNotFoundDidOccur($sender, $args){
		$resource = new AppResource(array('file_type'=>$args['file_type'],'url_parts'=>$args['url_parts']));
		$method = array_key_exists('_method', $args['server']) ? $args['server']['_method'] : $args['server']['REQUEST_METHOD'];
		$page_name = $args['url_parts'][0];
		$view = $page_name . '_' . $resource->file_type . '.php';
		if(is_numeric($page_name)){
			require('resources/IndexResource.php');
			$index_resource = new IndexResource(array('file_type'=>'phtml'));
			$resource->output = $index_resource->get($page_name);
		}elseif(file_exists(FrontController::getThemePath() . '/views/index/' . $view)){
			$resource->output = $resource->renderView('index/' . $page_name);
		}elseif(file_exists('index/' . $view)){
			$resource->output = $resource->renderView('index/' . $page_name);
		}else{
			if(AuthController::isAuthorized()){
				$post = Post::findByAttribute('custom_url', $page_name, Application::$member->person_id);
			}else{
				$post = Post::findAllPublished($page_name, Application::$member->person_id);
			}
			if($post != null){
				$resource->output = $resource->renderView('post/show', array('post'=>$post));
				$resource->description = $post->title;
				$resource->keywords = implode(', ', String::getKeyWordsFromContent($post->body));		
				$resource->title = $post->title;
			}else{
				FrontController::send404Headers($page_name . ' was not found');
				$resource->output = $resource->renderView('error/404', array('message'=>$method . ' ' . $page_name . ' was not found.'));
			}				
		}
		if($resource->title === null){
			$resource->title = $resource->getTitleFromOutput($resource->output);				
		}		
		return $resource->renderView('layouts/default');
	}
}

class console{
	public static $messages = array();
	public static function log($obj){
		self::$messages[] = $obj;
	}
	public function __destruct(){
		self::$messages = array();
	}
	
}