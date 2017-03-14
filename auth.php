<?php
	session_start();
	function auth($params) {
		foreach($params as $name=>$param) {
			$_SESSION[$name] = $param;
		}
	}
	function logout() {
		session_destroy();
	}
	function is_auth() {
		return (isset($_SESSION['username'])) ? TRUE : FALSE;
	}
	function user(){
		return [
			'username'=>(isset($_SESSION['username'])) ? $_SESSION['username'] : NULL,
			'IP'=>(isset($_SESSION['ip'])) ? $_SESSION['ip'] : NULL
		];
	}
?>