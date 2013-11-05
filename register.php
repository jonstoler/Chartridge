<?php
	require_once('kirby/bootstrap.php');
	require_once('config.php');

	if(!r::has('game')){
		exit();
	} else {
		$out = '';

		$id = str::random(6, 'alphaLower');
		$out .= $id;
		
		$game = urldecode(r::get('game', ''));
		
		if(r::get('player', false)){ $player = r::get('player'); }
		else { $player = str::random(6, 'alphaLower'); $out .= ',' . $player; }
		
		db::insert('sessions', ['game' => $game, 'id' => $id, 'player' => $player, 'start' => date('Y-m-d G:i:s'), 'ip' => ip::country(r::ip()), 'location' => r::get('location', 'Unknown')]);
		$playerExists = db::one('players', '*', ['game' => $game, 'id' => $player]);
		if(!$playerExists){
			db::insert('players', ['game' => $game, 'id' => $player]);
		}

		if(c::get('prowl.enabled', false)){
			global $prowl;
			$n = $game;
			$g = db::one('games', 'name', ['id' => $game]);
			if($g){ $n = $g->name; }

			$url = uri::current()->baseurl() . DS . c::get('chartridge.root') . DS . 'game' . DS . $game;
			$playcount = db::count('players', ['game' => $game]);
			if($playcount && !$playerExists){
				$playcount = intval($playcount);
				$notify = false;
				if(c::get('prowl.type', 'milestone') == 'every'){
					$notify = true;
				}
				else if($playcount < 10){
					$notify = true;
				} else if($playcount < 50){
					$notify = ($playcount % 5 == 0);
				} else if($playcount < 200){
					$notify = ($playcount % 10 == 0);
				} else if($playcount < 300){
					$notify = ($playcount % 25 == 0);
				} else if($playcount < 500){
					$notify = ($playcount % 50 == 0);
				} else if($playcount % 100 == 0){
					$notify = true;
				}

				if($notify){
					if(intval($playcount) > 1){
						$prowl->notify(ucwords($n) . ' Playcount', $n . ' has been played by ' . $playcount . ' people.', $url);
					} else {
						$prowl->notify(ucwords($n) . ' Playcount', $n . ' has been played by ' . $playcount . ' person.', $url);
					}
				}
			}
		}
		
		echo $out;
	}
?>