<?php
	require_once('kirby/bootstrap.php');
	require_once('config.php');

	$con = mysql_connect(c::get('db.host'), c::get('db.user'), c::get('db.password'));
	if($con){
		$db = mysql_select_db(c::get('db.name'), $con);
		if(!$db){
			mysql_query('CREATE DATABASE ' . c::get('db.prefix').c::get('db.name'));
			mysql_close($con);
			db::connect();
			db::query(
				"CREATE TABLE `bonuses` (
				  `session` text NOT NULL,
				  `game` text NOT NULL,
				  `name` text NOT NULL,
				  `time` datetime NOT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=latin1;

				CREATE TABLE `checkpoints` (
				  `session` text NOT NULL,
				  `game` text NOT NULL,
				  `name` text NOT NULL,
				  `time` datetime NOT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=latin1;

				CREATE TABLE `data` (
				  `session` text NOT NULL,
				  `game` text NOT NULL,
				  `name` text NOT NULL,
				  `value` text NOT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=latin1;

				CREATE TABLE `games` (
				  `name` text NOT NULL,
				  `created` datetime NOT NULL,
				  `id` text NOT NULL,
				  `checkpoints` text NOT NULL,
				  `bonuses` text NOT NULL,
				  `disable_checkpoint_unit` int(11) NOT NULL DEFAULT '0',
				  `disable_bonus_unit` int(11) NOT NULL DEFAULT '0',
				  `disable_score_unit` int(11) NOT NULL DEFAULT '0',
				  `disable_increment_unit` int(11) NOT NULL DEFAULT '0',
				  `disable_data_unit` int(11) NOT NULL DEFAULT '0'
				) ENGINE=InnoDB DEFAULT CHARSET=latin1;

				CREATE TABLE `increments` (
				  `session` text NOT NULL,
				  `game` text NOT NULL,
				  `name` text NOT NULL,
				  `value` int(11) NOT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=latin1;

				CREATE TABLE `players` (
				  `id` text NOT NULL,
				  `game` text NOT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=latin1;

				CREATE TABLE `scores` (
				  `session` text NOT NULL,
				  `game` text NOT NULL,
				  `time` datetime NOT NULL,
				  `mode` text NOT NULL,
				  `score` float NOT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=latin1;

				CREATE TABLE `sessions` (
				  `id` text NOT NULL,
				  `player` text NOT NULL,
				  `game` text NOT NULL,
				  `start` datetime NOT NULL,
				  `ip` text NOT NULL,
				  `location` text NOT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=latin1;"
			);

			header('Location: ' . uri::current()->baseurl() . DS . c::get('chartridge.root'));
		}
	}
	
	header('Location: ' . uri::current()->baseurl() . DS . c::get('chartridge.root'));
?>