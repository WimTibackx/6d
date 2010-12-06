<?php
	class_exists('AppResource') || require('AppResource.php');
	class VideoChannelResource extends AppResource{
		public function __construct($attributes = null){
			parent::__construct($attributes);
		}
		public function __destruct(){
			parent::__destruct();
		}
		
		public $photos;
		public $url;
		public function get_videochannel(){
			$this->title = "Video Channel";
			$this->output = $this->render('videochannel/index', null);
			return $this->render_layout('default', null);
		}
	}

?>