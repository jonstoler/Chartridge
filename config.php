<?php
	// debug mode?
	ini_set('display_errors', '0');

	// your password
	// do not share this with anyone!
	c::set('chartridge.pwd', 'password');

	// come up with a salt for the cookie!
	c::set('cookie.salt', 'mySalt');

	c::set('cookie.value', md5(c::get('chartridge.pwd').c::get('cookie.salt')));
	c::set('cookie.domain', preg_replace('#^www\.#', '', server::get('SERVER_NAME')));


	// set the directory where you store
	// chartridge, relative to the root
	// (no trailing slash!!!)
	c::set('chartridge.root', 'chartridge');


	// how games are sorted on the homepage
	// valid options: creation, lastplayed, mostpopular, leastpopular, alphabetical
	// default creation
	c::set('games.sort', 'creation');


	// enable Prowl support
	// http://prowlapp.com/
	// default false
	c::set('prowl.enabled', false);

	// your prowl api key
	// generate one at
	// http://prowlapp.com/
	// if this is not supplied,
	// Prowl will NOT work!
	c::set('prowl.apikey', '');

	// the name you want to display
	// on Prowl notifications
	// default 'chartridge'
	c::set('prowl.name', 'Chartridge');

	// when do you want to receive prowl alerts?
	// 'every' => every time someone starts playing your game
	// 'milestone' => when certain milestones of playcounts are reached
	// note that prowl has an API limit of 1000 requests per hour!!!
	// 'milestone' will help reduce the volume of your alerts
	// default 'milestone'
	c::set('prowl.type', 'milestone');


	// your database connection settings
	c::set('db.host', 'localhost');

	c::set('db.user', 'root');
	c::set('db.password', 'root');

	c::set('db.name', 'chartridge');
	c::set('db.prefix', '');


	// timezone to use for dates & times
	// (note that dates & times are not
	// stored in the database with this
	// timezone)
	c::set('timezone', 'PST');

	date_default_timezone_set(timezone_name_from_abbr(c::get('timezone')));

	$prowl = new prowl();
	$prowl->apiKey = c::get('prowl.apikey', null);
	$prowl->setApplication(c::get('prowl.name', 'Chartridge'));
?>