<?php
class_exists("AppResource") || require("AppResource.php");
class_exists("AuthController") || require("controllers/AuthController.php");
class_exists("Post") || require("models/Post.php");
class PageResource extends AppResource{
	public function __construct(){
		parent::__construct();
		if(!AuthController::is_authed()){
			$this->set_unauthed("Unauthorized");
		}
	}
	public $state;
	public $post;
	public $legend;
	public function get($name){
		$this->title = $name === null ? "Add a post" : "Edit a post";
		$this->post = Post::find_public_page($name, AuthController::$current_user->id);
		if($this->post === null) $this->post = new Post();
		$this->legend = $this->title;
		$this->output = View::render("page/edit", $this);
		return View::render_layout("default", $this);			
	}
	public function post($state = "show", $name){
		$this->legend = "Create a new page";
		$name = preg_replace("/[^a-zA-Z0-9-]?/", "", $name);
		$this->title = "Create a new page called $name";
		$this->state = $state == "edit" ? "edit" : "show";
		$this->post = Post::find_page_by_name($name, AuthController::$current_user->id);
		if($this->post === null){
			$this->post = new Post(array("name"=>$name));
		}
		$this->output = View::render("page/{$this->state}", $this);
		return View::render_layout("default", $this);
	}
	public function put(Post $post){
		$post->id = (int)$post->id;
		$post->name = preg_replace("/[^a-zA-Z0-9-]?/", "", $post->name);
		$this->post = Post::find_by_id_and_owned_by($post->id, AuthController::$current_user->id);
		$same_name = Post::find_page_by_name($post->name, AuthController::$current_user->id);
		if($this->post === null){
			$this->set_unauthed("Unauthorized");
			return;
		}
		if($same_name !== null && $same_name->id !== $this->post->id){
			App::set_user_message("The name already exists for a page. Please enter a unique name.");
		}else{
			$this->post->title = $post->title;
			$this->post->body = $post->body;
			$this->post->status = $post->status;
			$this->post->name = $post->name;
			$this->post->post_date = strtotime($post->post_date);
			$errors = Post::can_save($this->post);
			if(count($errors) === 0){
				Post::save($this->post);
			}else{
				App::set_user_message(implode(",", $errors));
			}
			$this->set_redirect_to(AuthController::$current_user->name . '/page/' . $this->post->name);
		}		
		$this->output = View::render('page/edit', $this);
		return View::render_layout('default', $this);
	}
}