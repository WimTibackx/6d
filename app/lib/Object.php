<?php
	class Object{
		public function __construct($attributes = null){
			if(self::$observers == null){
				self::$observers = array();
			}
			if($attributes !== null && is_array($attributes) && count($attributes) > 0){
				foreach($attributes as $key=>$value){
					$this->{$key} = $value;
				}
			}
		}
		public function __destruct(){}
		public $errors;
		private static $observers;
		public static function add_observer($observer, $notification, $publisher){
			self::$observers[] = new Observer($observer, $notification, $publisher);
		}
		public static function remove_observer($observer){
			$tmp = array();
			foreach(self::$observers as $o){
				if($o->obj !== $observer){
					$tmp[] = $o;
				}
			}
			self::$observers = $tmp;
		}

		public static function notify($notification, $sender, $info){
			$publisher = $sender;			
			if(is_object($sender)){
				$publisher = get_class($sender);
			}
			if(self::$observers != null){
				foreach(self::$observers as $observer){
					if(method_exists($observer->obj, $notification) && $observer->publisher === $publisher){						
						$observer->obj->{$notification}($sender, $info);
					}
				}
			}
		}

		public function __get($key){
			$val = null;
			if(method_exists($this, 'get' . ucwords($key))){
				$val = $this->{'get' . ucwords($key)}();				
			}
			if(count(self::$observers) > 0){
				foreach(self::$observers as $observer){
					if(method_exists($observer->obj, 'will_return_value_for_key') && $observer->publisher === $key){
						$val = $observer->obj->will_return_value_for_key($key, $this, $val);
					}
				}
			}
			return $val;
		}
		public function __set($key, $val){
			if(count(self::$observers) > 0){
				foreach(self::$observers as $observer){
					if(method_exists($observer->obj, 'observe_for_key_path') && ($observer->publisher === null || $observer->publisher === $key)){
						$val = $observer->obj->observe_for_key_path($key, $this, $val);
					}
				}
			}
			if(method_exists($this, 'set' . ucwords($key))){
				$this->{'set' . ucwords($key)}($val);
			}
		}
	}
	
	class Observer{
		public function __construct($obj, $notification, $publisher){
			$this->obj = $obj;
			$this->notification = $notification;
			if(is_object($publisher)){
				$publisher = get_class($publisher);
			}
			$this->publisher = $publisher;
		}
		
		public $obj;
		public $notification;
		public $publisher;
	}
?>