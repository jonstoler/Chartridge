<?php
	setcookie('chartridge_auth', '', time() - (365 * 24 * 60 * 60));
	unset($_COOKIE['chartridge_auth']);

	header('Location: index.php');
?>