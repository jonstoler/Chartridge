<?php
	/**
		* Include commonly used code in your templates
		*
		* @param string $name
		* @param array $data
		* @param array $params
		* @return string
		*/
	function snippet($name, $data = array(), $params = array()) {
		$root = template::$root;
		template::$root = null;
		$tpl = template::create(g::get('snippetRoot', '') . $name, $data, $params);
		echo $tpl;
		template::$root = $root;
	}

	/**
		* Shortcut for snippet()
		*
		* @see snippet()
		*/
	function s($name, $data = array(), $params = array()) {
		snippet($name, $data, $params);
	}

	/**
		* Take a chance!
		*
		* @param int odds
		* @return boolean
		*/
	function chance($odds = 2){
		return (rand() <= (1 / $odds));
	}
?>