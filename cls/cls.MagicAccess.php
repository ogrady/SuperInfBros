<?php
class MagicAccess {
	protected $attributes;
	
	public function __get($_key) {
		return is_array($this->attributes) && !empty($this->attributes) && array_key_exists($_key, $this->attributes) ? $this->attributes[$_key] : null; 
	}
	
	public function __set($_key, $_val) {
		if(!array_key_exists($_key, $this->attributes)) {
			throw new Exception("Invalid key for MagicAccess attribute: '$_key'");
		}
		$this->attributes[$_key] = $_val;
	}
	
	protected function init($_attributes) {
		$this->attributes = array_fill_keys($_attributes,'');	
	}
}
?>