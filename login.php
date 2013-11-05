<?php
	require_once('kirby/bootstrap.php');
	require_once('config.php');

	if(r::get('pwd', '') == c::get('chartridge.pwd')){
		if((md5(r::get('pwd', '').c::get('cookie.salt')) == c::get('cookie.value'))){
			if(cookie::exists('chartridge_auth')){ cookie::remove('chartridge_auth'); }
			setcookie('chartridge_auth', c::get('cookie.value'), time() + (365 * 24 * 60 * 60));
			$_COOKIE['chartridge_auth'] = c::get('cookie.value');
		}
	}

	header::redirect(DS . c::get('chartridge.root'));
?>