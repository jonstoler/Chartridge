<?php
	require_once('kirby/bootstrap.php');
	require_once('config.php');

	if(r::has('game', 'id')){
		$game = r::get('game');
		$id   = r::get('id');
		$now  = date('Y-m-d G:i:s');

		if(r::has('checkpoint')){
			db::insert('checkpoints',
				[
					'session' => $id,
					'game'    => $game,
					'name'    => r::get('checkpoint'),
					'time'    => $now
				]
			);
		} else if(r::has('bonus')){
			db::insert('bonuses',
				[
					'session' => $id,
					'game'    => $game,
					'name'    => r::get('bonus'),
					'time'    => $now
				]
			);
		} else if(r::has('score', 'mode')){
			db::insert('scores',
				[
					'session' => $id,
					'game'    => $game,
					'mode'    => r::get('mode'),
					'score'   => r::get('score'),
					'time'    => $now
				]
			);
		} else if(r::has('increment')){
			$value = db::one('increments', 'value', ['game' => $game, 'session' => $id, 'name' => r::get('increment')]);
			$add = 1;
			if(r::has('set')){ $add = intval(r::get('set')); }
			else {
				if(r::has('by'))      { $add = intval(r::get('by')); }
				if(r::has('decrease')){ $add *= -1; }
			}
			if(!$value){
				db::insert('increments',
					[
						'session' => $id,
						'game'    => $game,
						'name'    => r::get('increment'),
						'value'   => $add
					]
				);
			} else {
				$val = intval($value->value) + $add;
				if(r::has('set')){ $val = intval(r::get('set')); }
				db::update('increments',
					[ 'value'   => $val ],
					[
						'session' => $id,
						'game'    => $game,
						'name'    => r::get('increment')
					]
				);
			}
		} else if(r::has('data', 'value')){
			$d = db::one('data', '*', ['session' => $id, 'game' => $game, 'name' => r::get('data')]);
			if(!$d){
				db::insert('data',
					[
						'session' => $id,
						'game'    => $game,
						'name'    => r::get('data'),
						'value'   => r::get('value')
					]
				);
			} else {
				db::update('data', ['value' => r::get('value')], ['session' => $id, 'game' => $game, 'name' => r::get('data')]);
			}
		}
	} else {
		exit();
	}
?>