<?php
	if(!KIRBY){ die(); }

	define('KIRBY_TOOLKIT_ROOT_ADDONS', KIRBY_TOOLKIT_ROOT . DS . 'addons');

	// initialize the autoloader
	$addonloader = new Kirby\Toolkit\Autoloader();

	// set the base root where all addons are located
	$addonloader->root = KIRBY_TOOLKIT_ROOT_ADDONS;

	// set the global namespace for all addons
	$addonloader->namespace = 'Kirby\\Toolkit';

	// add all needed aliases
	$addonloader->aliases = array(
		'ip'    => 'Kirby\\Toolkit\\IP',
		'prowl' => 'Kirby\\Toolkit\\Prowl',
	);

	// start autoloading
	$addonloader->start();