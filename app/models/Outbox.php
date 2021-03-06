<?php
class_exists("ModelFactory") || require("ModelFactory.php");
class Outbox extends ChinObject{
	public function __construct($values = array()){
		$this->id = 0;
		$this->owner_id = 0;
		$this->sent = time();
		parent::__construct($values);
	}
	public $id;
	public $owner_id;
	public $message;
	public $recipient;
	public $sent;
}