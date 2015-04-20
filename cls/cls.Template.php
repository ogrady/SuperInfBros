<?php
/* Created on 27.10.2005 / 05.01.2006
*/
/**
* Aufgaben:
*<pre>
* - besorgt die template-Seite
* - bindet Content ein
* - liefert fertige Webseite
*</pre>
* @version "aktuellste Version, PHP 4"
* @package template, Template
*
*/
class Template {
	private $template;
	 
	function Template($template) {
		$this->settemplate($template);
	}

	function settemplate($tpl)	{
		if(file_exists($tpl)) {
			$fp = fopen($tpl, "r");
			$text = fread($fp, filesize($tpl));
			fclose($fp);
			$this->template = $text;
		} else {
			$this->template = $tpl;
		}
	}
	 
	/**
	* Mögliche Aufrufe:
	* <pre>
	* - String/String => needle, substitution
	* - Array/ - => Array(needle => substitution)
	* - String/Array => loopnName/2D-Array
	* </pre>
	* @internal Überladung simulieren: diese Methode entscheidet anhand der Parameter, an welche private Methode delegiert wird
	* @param String_oder_Array param1
	* @param String_oder_Array param2
	* @access public
	*/
	function setContent($param1, $param2="") {
		if(!is_array($param1) && isset($param2) && !is_array($param2)) {
			$this->setOne($param1, $param2);
		} elseif (is_array($param1) && !$param2){
			$this->setArray($param1);
		} elseif (!is_array($param1) && is_array($param2)) {
			$this->setLoop($param1, $param2);
		} else {
			die("Parameter in der Klasse template wurden falsch übergeben.");
		}
	}
	 
	function setOne($needle, $substitution) {
	$this->template = str_replace("{".$needle."}",
	$substitution,
	$this->template);
	}
	 
	/**
	* AufrufBeispiel:
	* <pre>
	* $template->setArray(array("MELDUNG" => $meldung,
	* "NAME" => $_POST['name'],
	* "EMAIL" => $_POST['eMail'],
	* "TEXT" => $_POST['text'],
	* "KOPIE" => $_POST['kopie']));
	* </pre>
	* @param mixed $Array enthält Variable/Substitution-Paare
	* @access private
	*/
	function setArray($Array) {
		foreach ($Array as $needle => $substitution) {
			$this->setOne($needle, $substitution);
		}
	}
	 
	/**
	* Aufrufbeispiel:
	* <pre>
	* $template->setLoop($nameDerloop, array(array("var1" => "konst1",
	* "var2" => "konst2"),
	* array("var1" => "konst3",
	* "var2" => "konst4")));
	* </pre>
	* @param String $loop Bezeichnung der loop
	* @param mixed $Array Array von assoziativen Arrays, die jeweils die Schlüssel/Werte enthalten (siehe Aufrufbeispiel)
	* @access private
	*/
	function setLoop($loop, $Array) {
		$str = explode("<!--begin:".$loop."!-->",
		str_replace("<!--end:".$loop."!-->",
		"<!--begin:".$loop."!-->",
		$this->template));
		$substr = "";
		foreach ($Array as $element)
		{
			$subtemplate = new Template($str[1]);
			$subtemplate->setArray($element);
			$substr .= $subtemplate->template;
		}
		$this->template = $str[0] . $substr . $str[2];
	}
	
	function __toString()
	{
		return $this->template;
	}
}
?>