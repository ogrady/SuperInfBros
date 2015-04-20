<?php 
class Encapsulator {
	public static function toTextField($_text) {
		return "<textarea>$_text</textarea>";
	}	
	
	public static function toInputField($_text) {
		return "<input type='text' value='$_text'>";
	}
	
	public static function toDatePicker($_text) {
		return "<input type='date' value='$_text'>";
	} 
}
?>